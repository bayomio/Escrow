<?php

namespace App\Http\Controllers\Api;

use App\Entity\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStatusRequest;
use App\Http\Requests\VerifyStatusRequest;
use App\Http\Requests\NotificationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Api
 */
class ProfileController extends BaseController
{

    /*
     * The request update user profile records
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $users = User::where("email",'like', $request->name)
            ->orWhere("id", "like", $request->name)
            ->first();
        return $this->json(true, "Successful Fetch", $users);
    }

    /*
     * The request update user profile records
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers(Request $request)
    {
        $users = User::get();
        return $this->json(true, "Successful Fetch", $users);
    }

    /*
     * The request update user profile records
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        unset($user->activation_token);
        unset($user->send_notification);
        return $this->json(true, "Successful Fetch", $user);
    }

    /*
     * The request update user profile records
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile(Request $request)
    {
        $user = User::where("id", Auth::id())->first();


        if(isset($request->name))
            $user->name = $request->name;

        if(isset($request->send_notification))
            $user->send_notification = $request->send_notification;

        if(isset($request->account_number))
            $user->account_number = $request->account_number;

        if(isset($request->bank_code))
            $user->bank_code = $request->bank_code;

        if(isset($request->form_of_id))
            $user->form_of_id = $request->form_of_id;

        if(isset($request->profile_image))
            $user->profile_image = $request->profile_image;

        if (isset($request->status))
            $user->status = $request->status;

        $user->save();
        return $this->json(true, "Successful Fetch", $user);
    }

    /*
     * Grant user admin access or not
     * status can either be  YES or NO
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setAdminStatus(AdminStatusRequest $request)
    {
        User::where("id", Auth::id())
            ->update([
                "is_admin" => $request->status = "YES" ? true : false,
            ]);

        return $this->json(true, "User has been given admin privileges!", Response::HTTP_OK);

    }

    /*
     * Grant user admin access or not
     * status can either be  YES or NO
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setNotificationStatus(NotificationRequest $request)
    {

        User::where("id", Auth::id())
            ->update([
                "send_notification" => $request->status = "YES" ? true : false,
            ]);
        return $this->json(true, "It's been updated");
    }



    public function setUserStatus(VerifyStatusRequest $request)
    {
        /*
         * Check user have admin permission
         */

        // if (isset(Auth::user()->is_admin) && !Auth::user()->is_admin) {
        //     return response()->json(false, "You don't have required permission to update this request", Response::HTTP_UNPROCESSABLE_ENTITY);
        // }

        $user_ = User::where("id", $request->user_id)->first();

        // if(!isset($user)){
        //     return $this->json(false, "User account does not exist", Response::HTTP_UNPROCESSABLE_ENTITY);
        // }

        $user_->status = $request->status;
        $user_->save();

        return $this->json(true, "User account verification has been updated!", Response::HTTP_OK);
    }


//    public function postFormOfId(){
//
//        $user = Auth::user();
//        $image = request('form_of_id');
//        if(isset($image)){
//            $destinationPath = public_path('/form_of_id');
//            $fileName = time().'.'.$image->getClientOriginalExtension();
//            if($image->move($destinationPath, $fileName)) {
//                $user->form_of_id = $fileName;
//                $user->save();
//            }
//            return $this->json(true, "Successful Fetch", $user);
//        }else{
//            return $this->json(false, 'Image is required', $user);
//        }
//    }

}
