<?php
namespace App\Http\Controllers\Api;
use App\Entity\PasswordReset;
use App\Entity\User;
use App\Mail\PasswordResetRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends BaseController
{


    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user)
            return $this->json(false, 'We can\'t find a user with that e-mail address.');

        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => rand(100000, 999999)
            ]
        );
        if (isset($user) && isset($passwordReset)){
            Mail::to($passwordReset->email)->send(new PasswordResetRequest($user->name, $passwordReset->token));
        }

        return $this->json(true, 'We have e-mailed the password reset code!');

    }



    public function find()
    {
        $token = request('token');

        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset){
            return $this->json(false, 'This password reset token is invalid.');
        }

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return $this->json(false, 'This password reset token is invalid.');
        }

        return $this->json(true, 'Successful', $passwordReset);
    }


    public function reset(Request $request)
    {

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset)
            return $this->json(false, 'This password reset token is invalid.');

        $user = User::where('email', $passwordReset->email)->first();
        if (!$user)
            return $this->json(false, 'We can\'t find a user with that e-mail address.');


        $user->password = bcrypt($request->password);
        $user->save();

        $passwordReset->delete();

        return $this->json(true, "Successful Login", [
            'user' => $user,
            'token' =>  $user->createToken('TokenCode')->accessToken,
        ]);


    }
}
