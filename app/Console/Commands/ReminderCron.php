<?php

namespace App\Console\Commands;

use App\Entity\Event;
use App\Entity\User;
use App\Mail\GameReminderFifteen;
use App\Mail\GameReminderOneHour;
use App\Mail\GameReminderVoters;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ReminderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gaming_reminder:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is a daily cron job to reminder users';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {

        $eventOurHours =Event::with("players")->where("sent_one_hour", false)
                ->get();

        foreach ($eventOurHours as $event){
            if(isset($event->start)) {
                $now = new DateTime();
                $start_time = new DateTime(date("Y-m-d", strtotime('-1 hours', strtotime($event->start))));
                if ($start_time > $now) {
                    foreach ($event->players as $player) {
                        $user = User::find($player->user_id);
                        Mail::to($user['email'])->send(new GameReminderOneHour($user->name, $event->start));
                    }
                }
                /*
                 * update record
                 */
                $event->sent_one_hour = true;
                $event->save();
            }
        }



        $eventFifteens =Event::with("players")->where("sent_fifteen", false)
            ->get();

        foreach ($eventFifteens as $event){
            if(isset($event->start)) {
                $now = new DateTime();
                $start_time = new DateTime(date("Y-m-d", strtotime('-15 minutes', strtotime($event->start))));
                if ($start_time > $now) {
                    foreach ($event->players as $player) {
                        $user = User::find($player->user_id);
                        Mail::to($user['email'])->send(new GameReminderFifteen($user->name, $event->start));
                    }
                }
                /*
                 * update record
                 */
                $event->sent_fifteen = true;
                $event->save();
            }
        }

        $eventVoters =Event::with("voters")->where("sent_voters", false)
            ->get();

        foreach ($eventVoters as $event){
            if(isset($event->start)) {
                $now = new DateTime();
                $start_time = new DateTime(date("Y-m-d", strtotime('-10 minutes', strtotime($event->start))));
                if ($start_time > $now) {
                    foreach ($event->voters as $player) {
                        $user = User::find($player->user_id);
                        Mail::to($user['email'])->send(new GameReminderVoters($user->name, $event->start));
                    }
                }
                /*
                 * update record
                 */
                $event->sent_voters = true;
                $event->save();
            }
        }

    }





}
