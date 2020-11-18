<?php

namespace App\Http\Controllers\Api;

use App\Entity\Event;
use App\Entity\EventPlayer;
use App\Entity\EventVoter;
use App\Entity\JournalEntry;
use App\Entity\Setting;
use App\Entity\Transaction;
use App\Entity\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Http\Requests\EventScoreRequest;
use App\Http\Requests\EventVoteRequest;
use App\Jobs\AddClientJob;
use App\Mail\EventInvitation;
use App\Mail\GameScorePlayers;
use App\Mail\GameScoreVoters;
use App\Mail\WelcomeMail;
use App\Models\Customer\Client;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PDF;


class EventController extends BaseController
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        return $this->json(true, "Successful Fetched", Event::with("players", "voters")->search($search)->sortByDesc('start'));

    }

    public function create()
    {

    }

    public function store(EventRequest $request)
    {

        $authUser = User::find($request->user_id);
//        if(!isset($user)){
//            return $this->json(false, "User does not exist");
//        }


        $event = Event::create([
            "name" => $request->name,
            "description" => $request->description,
            "start" => $request->start,
            "end" => $request->end,
            "streaming_link" => $request->streaming_link,
            "event_image" =>$request->event_image,
        ]);

        EventPlayer::create([
            "event_id" => $event->id,
            "user_id" => $authUser->id,
            "position" => "HOME",
        ]);

        $user = User::where("email", $request->invitee_email)->first();

        EventPlayer::create([
            "event_id" => $event->id,
            "user_id" => $user->id,
            "position" => "AWAY",
        ]);

        Mail::to($user['email'])->send(new EventInvitation($event->name, $authUser->name,
            $event->start, $event->streaming_link, $event->id));

        return $this->json(true, "Event Successfully Created", $event);

    }

    public function show($id)
    {
        $event = Event::with("players", "players.user", "voters", "voters.user")->findOrFail($id);
        $transactions = Transaction::with("journals", 'journals.user')->where("event_id", $id)->get();

        return $this->json(true, "Successful Fetched", [
            'data' => $event,
            'option' => [
                'transactions' =>$transactions
            ]
        ]);

    }

    public function edit($id)
    {
    }


    public function update($id, EventRequest $request)
    {

        $event = Event::findOrFail($id);

        $now_date = new DateTime("now");
        $start_date = new DateTime($event->start);
        if ($start_date < $now_date) {

            return $this->json(false, "You cannot edit a running event");
        }


        if (isset($request->name)) $event->name = $request->name;
        if (isset($request->description)) $event->description = $request->description;
        if (isset($request->start)) $event->start = $request->start;
        if (isset($request->end)) $event->end = $request->end;
        if (isset($request->streaming_link)) $event->streaming_link = $request->streaming_link;

        $event->save();

        return $this->json(true, "Successful Fetched", $event);
    }


    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        $now_date = new DateTime("now");
        $start_date = new DateTime($event->start);
        if ($start_date < $now_date) {
            return response()->json('You cannot edit a running event', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (isset($Client)) {
            $event->delete();
        }

        return $this->json(true, "It's been deleted");
    }


    public function setEventScore(EventScoreRequest $request)
    {
        /*
         * Check user have admin permission
         */

        if (isset(Auth::user()->is_admin) && !Auth::user()->is_admin) {
            return response()->json("You don't have required permission to update this request", Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $setting = Setting::setting();
        $event = Event::with("players", "voters", "transactions")->findOrFail($request->event_id);

        if($event->paid){
            return $this->json(false, "Have already paid user");
        }

        /*
         * Update Home score
         */
        $homePlayer = EventPlayer::where("event_id", $event->id)
            ->where("position", "HOME")
            ->first();
        if(isset($homePlayer)){
            $homePlayer->score = $request->home_player_score;
            $homePlayer->save();
        }

        /*
         * Update Home score
         */
        $awayPlayer = EventPlayer::where("event_id", $event->id)
            ->where("position", "AWAY")
            ->first();

        if(isset($awayPlayer)){
            $awayPlayer->score = $request->away_player_score;
            $awayPlayer->save();
        }

        /*
         * Update Event
         */

        $event->home_player_score = $request->home_player_score;
        $event->away_player_score = $request->away_player_score;

        if ($request->home_player_score > $request->away_player_score) {
            $event->result = "HOME";
        } elseif ($request->home_player_score < $request->away_player_score) {
            $event->result = "AWAY";
        } else {
            $event->result = "DRAW";
        }
        $event->save();
        $total_amount = 0;
        $transactions = $event->transactions;
        foreach ($transactions as $tran){
            $journals = JournalEntry::where("transaction_id", $tran->id)
                ->where("user_id", $setting->gaming_user)
                ->get();
            foreach ($journals as $journal){
                $total_amount += $journal->credit - $journal->debit;
            }
        }
        $number_of_winners = 0;
        $basic_winners = 0;
        foreach ($event->voters as $vote) {
            if($event->result == $vote->position){
                $number_of_winners++;
                $basic_winners = $basic_winners+$setting->payment_multiple;
            }
        }


        $dividence = 100 - $setting->event_game_commission;
        $sharable_amount = ($total_amount-$basic_winners);

        $dividend_amount = $dividence/100*$sharable_amount;


        /*
         * System commission
         */
        $sytem_commission_amount = $sharable_amount - $dividend_amount;
        $sytem_divience = $dividend_amount + $basic_winners;

        $system_transaction = Transaction::create([
            'user_id' => $setting->gaming_user,
            'amount' => $sytem_commission_amount,
            'event_id' => $request->event_id,
            'request_type' => "EVENT",
            'value_date' => new DateTime("now"),
        ]);
        JournalEntry::create([
            'narration' => "Bonus",
            'credit' => $sytem_commission_amount,
            'debit' => 0,
            'transaction_id' => $system_transaction->id,
            'user_id' => $setting->commission_user,
        ]);

        JournalEntry::create([
            'narration' => "Bonus",
            'credit' => 0,
            'debit' => $sytem_commission_amount,
            'transaction_id' => $system_transaction->id,
            'user_id' => $setting->gaming_user,
        ]);


        /*
         *
         * user Commission
         */

        $transaction = Transaction::create([
            'user_id' => $setting->gaming_user,
            'amount' => $sytem_divience,
            'event_id' => $request->event_id,
            'request_type' => "EVENT",
            'value_date' => new DateTime("now"),
        ]);

        foreach ($event->voters as $vote){
            $user_id = $vote->user_id;
            if($event->result == $vote->position){
                $commission = 1/$number_of_winners*$sytem_divience;
                JournalEntry::create([
                    'narration' => "Bonus",
                    'credit' => $commission,
                    'debit' => 0,
                    'transaction_id' => $transaction->id,
                    'user_id' => $user_id,
                ]);

                JournalEntry::create([
                    'narration' => "Bonus",
                    'credit' => 0,
                    'debit' => $commission,
                    'transaction_id' => $transaction->id,
                    'user_id' => $setting->gaming_user,
                ]);

            }

        }

        $event->paid = true;
        $event->save();


        foreach ($event->players as $player) {
            $user = User::find($player->user_id);
            Mail::to($user['email'])->send(new GameScorePlayers($user->name, $event->name, $event->result));
        }

        foreach ($event->voters as $player) {
            $user = User::find($player->user_id);
            Mail::to($user['email'])->send(new GameScoreVoters($user->name, $event->name, $event->result));
        }

        return $this->json(true, "Successful Fetched", $event);
    }


    public function eventVote(EventVoteRequest $request)
    {
        $this->validate($request, [
            'event_id' => 'required',
            'quantity' => 'required',
            'predict' => 'required',
        ]);

        $setting = Setting::setting();
        $user_id = Auth::id();

        $amount = $setting->payment_multiple * $request->quantity;

        $balance = JournalEntry::where("user_id", Auth::id())
            ->balance();

        if ($balance < $amount)
            return $this->json(false, "Insufficient Balance");


        //Check time too

        $transaction = Transaction::create([
            'user_id' => $user_id,
            'amount' => $amount,
            'event_id' => $request->event_id,
            'request_type' => "EVENT",
            'value_date' => new DateTime("now"),
        ]);

        JournalEntry::create([
            'narration' => "Client Deposit",
            'credit' => $amount,
            'debit' => 0,
            'transaction_id' => $transaction->id,
            'user_id' => $setting->gaming_user,
        ]);

        JournalEntry::create([
            'narration' => "Client Deposit",
            'credit' => 0,
            'debit' => $amount,
            'transaction_id' => $transaction->id,
            'user_id' => $user_id,
        ]);

        EventVoter::create([
            'event_id' => $request->event_id,
            'quantity' => $request->quantity,
            'user_id' => $user_id,
            'position'  => $request->predict, //HOME, AWAY, DRAW
        ]);

        return $this->json(true, "Vote has been submitted");

    }


    public function getEventVoteScore(Request $request)
    {

        return $this->json(true, "Successful", [
            'total_home' => EventVoter::where("event_id", $request->event_id)
                ->where("position", "HOME")
                ->count(),
            'total_away' => EventVoter::where("event_id", $request->event_id)
                ->where("position", "AWAY")
                ->count(),
            'total_draw' => EventVoter::where("event_id", $request->event_id)
                ->where("position", "DRAW")
                ->count()
        ]);
    }
}
