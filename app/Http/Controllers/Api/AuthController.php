<?php

namespace App\Http\Controllers\Api;

use App\Entity\User;
use App\Framework\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Redirector;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Api
 */
class AuthController extends BaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = User::find(Auth::id());

            if(!$user->active)
                return $this->json(false, 'Kindly activate your account');

            $token = $user->createToken('TokenCode')->accessToken;
            unset($user->activation_token);
            unset($user->send_notification);
            return $this->json(true, "Successful Login", [
                'user' => $user,
                'token' => $token,
            ]);
        }
        else{
            return $this->json(false, 'Invalid Login credentials');
        }
    }


    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'phone' => $request->phone,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'activation_token' => $this->generateActivateToken()
        ]);

        $token = $user->createToken('TokenCode')->accessToken;
        Mail::to($user['email'])->send(new WelcomeMail($user->name, $user->activation_token));
        unset($user->activation_token);
        unset($user->send_notification);
        return $this->json(true, "Successfully Created", [
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * @param Request $request
     *  @return View
     */

    public function confirmAccount(Request $request)
    {
        logger('Confirm Account ', ['request' => $request->all()]);

        $user = User::where('activation_token', request('token'))
            ->first();

        if(!isset($user)){
            // return $this->json(false, 'This activation token is invalid.');

            return view('invalid');
        }

        $user->active = true;
        $user->activation_token = '';
        $user->save();

        // return $this->json(true, "The account has been activated");

        return view('confirmed');

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        return response()->json([], Response::HTTP_OK);
    }


    private function ActivateTokenExists($number) {
        return User::where('activation_token', '=', $number)->exists();
    }

    private function generateActivateToken() {
        $number = $this->generateRandomNumber(20);
        if ($this->ActivateTokenExists($number)) {
            return $this->generateRandomNumber();
        }
        return $number;
    }

    function generateRandomNumber($len = 16) {
        $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $randomNumber = '';
        for ($i = 0; $i < $len; $i++) {
            $randomNumber .= $char[rand(0, $len - 1)];
        }
        return $randomNumber;
    }

}
