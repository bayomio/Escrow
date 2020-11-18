<?php

namespace App\Http\Controllers\Api;

use App\Entity\JournalEntry;
use App\Entity\Setting;
use App\Entity\Transaction;
use App\Entity\User;
use App\Http\Controllers\Controller;
use App\Mail\FundWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Api
 */
class PaymentController extends BaseController
{

    /*
     * The account balance
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function balance(Request $request)
    {
        $setting = Setting::setting();
        $balance = JournalEntry::where("user_id", Auth::id())
            ->balance();

        $point = $setting->payment_multiple > 0 ? $balance / $setting->payment_multiple : $balance;

        return $this->json(true, "Successfully Fetch", $point);

    }

    /*
     * The account balance
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function banks(Request $request)
    {
        $response = Http::get("https://api.ravepay.co/v2/banks/NG");
        $json = json_decode($response->getBody()->getContents(), true);
        if ($json['status'] === "success") {
            return $this->json(true, "successful", $json['data']);
        } else {
            $error = $json['message'];
            return $this->json(false, $error);
        }
    }

    public function allBanks(Request $request)
    {
        $response = Http::withToken(env("SECRET_KEY"))->get("https://api.flutterwave.com/v3/banks/NG");
        $json = json_decode($response->getBody()->getContents(), true);
        if ($json['status'] === "success") {
            return $this->json(true, "successful", $json['data']);
        } else {
            $error = $json['message'];
            return $this->json(false, $error);
        }
    }

    public function accountResolve(Request $request)
    {
        $number = $request->number;
        $bank = $request->bank;

        $response = Http::withToken(env("SECRET_KEY"))->post("https://api.flutterwave.com/v3/accounts/resolve", [
            'account_number' => $number,
            'account_bank' => $bank
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        if ($json['status'] === "success") {
            return $this->json(true, "successful", $json['data']);
        } else {
            $error = $json['message'];
            return $this->json(false, $error);
        }
    }

    public function getAllTransactions(Request $request)
    {
        $transactions = Transaction::get();
        return $this->json(true, "Successful Fetch", $transactions);
    }

    /*
     * Grant user admin access or not
     * status can either be  YES or NO
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function depositFund(Request $request)
    {
        $txref = $request->reference;

        $response = Http::post("https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/v2/verify", [
            'txref' => $txref,
            'SECKEY' => env("SECRET_KEY")
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        //check the status is success
        if ($json['status'] === "success") {

            $transaction = Transaction::where("txref", $txref)->first();
            if ($transaction != null)
                return $this->json(false, "Transaction has already been processed");

            $amount = $json['data']['amount'];
            $setting = Setting::setting();
            $user_id = Auth::id();
            $transaction = Transaction::create([
                'user_id' => $user_id,
                'amount' => $amount,
                'request_type' => "DEPOSIT",
                'txref' => $txref,
                'value_date' => date("c"),
                'narration' => "Client Deposit"
            ]);

            JournalEntry::create([
                'narration' => "Client Deposit",
                'credit' => $amount,
                'debit' => 0,
                'transaction_id' => $transaction->id,
                'user_id' => $user_id,
            ]);

            JournalEntry::create([
                'narration' => "Client Deposit",
                'credit' => 0,
                'debit' => $amount,
                'transaction_id' => $transaction->id,
                'user_id' => $setting->bank_user,
            ]);

            return $this->json(true, "Successfully Fetch", $transaction);

        } else {
            $error = $json['message'];
            return $this->json(false, $error);
        }

    }

    /*
     * Grant user admin access or not
     * status can either be  YES or NO
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawFund(Request $request)
    {
        $this->validate($request, [
            'gamekrow' => 'required',
        ]);

        $setting = Setting::setting();
        $user = User::where("id", Auth::id())->first();
        $amount = $request->gamekrow * $setting->payment_multiple;

        $balance = JournalEntry::where("user_id", Auth::id())
            ->balance();

        if ($balance < $amount)
            return $this->json(false, 'You don\'t have sufficient balance');

        if ($user->bank_code == null || $user->bank_code == "") {
            return $this->json(false, 'Bank is required');
        }

        if ($user->account_number == null || $user->account_number == "") {
            return $this->json(false, 'Bank Account is required');
        }

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'request_type' => "WITHDRAW",
            'status' => 'new',
            'value_date' => date("c"),
            'narration' => "Client Withdrawal",
            'verified_id' => $request->verified_id
        ]);

        JournalEntry::create([
            'narration' => "Client Withdrawal",
            'credit' => $amount,
            'debit' => 0,
            'transaction_id' => $transaction->id,
            'user_id' => $setting->bank_user,
        ]);

        JournalEntry::create([
            'narration' => "Client Withdrawal",
            'credit' => 0,
            'debit' => $amount,
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
        ]);

        return $this->json(true, "Your withdrawal request has been submitted for review");

    }


    public function approveWithdrawFund(Request $request)
    {
        if (isset(Auth::user()->is_admin) && !Auth::user()->is_admin) {
            return response()->json("You don't have required permission to update this request", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $transaction = Transaction::find($request->transaction_id);
        // $transaction = Transaction::where("id", $request->transaction_id)->first();

        if (!isset($transaction)) {
            return $this->json(false, "Transaction does not exist");
        }

        if ($transaction->status != "new") {
            return $this->json(false, "Transaction has already been approved/rejected");
        }

        if ($request->status == "approved") {

            $response = Http::post("https://api.ravepay.co/v2/gpx/transfers/create", [
                'account_bank' => $transaction->user->bank_code,
                'account_number' => $transaction->user->account_number,
                'amount' => $transaction->amount,
                'narration' => "Fund Withdrawal",
                'currency' => "NGN",
                'reference' => "gaming" . date("YmdHis"),
                'seckey' => env("SECRET_KEY"),
            ]);

            $json = json_decode($response->getBody()->getContents(), true);

            //check the status is success
            if (isset($json) && isset($json['status']) && $json['status'] === "success") {
                $transaction->status = $request->status;
                $transaction->save();
                Mail::to($transaction->user->email)->send(new FundWithdrawal($transaction->amount, $transaction->user->name, $transaction->status));
                return $this->json(true, "Transaction has been approved");
            } else {
                $error = $json['message'];
                return $this->json(false, $error);
            }

        } elseif ($request->status == "rejected") {

            JournalEntry::where("transaction_id", $transaction->id)->delete();

            $transaction->status = $request->status;
            $transaction->save();

            Mail::to($transaction->user->email)->send(new FundWithdrawal($transaction->amount, $transaction->user->name, $transaction->status));

            return $this->json(true, "Transaction has been rejected");

        }else{

            return $this->json(false, "Transaction status does not exist");

        }

    }

}
