<?php

namespace App\Http\Controllers\Api;

use App\Entity\Setting;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Class SettingController
 *
 * @package App\Http\Controllers\Api
 */
class SettingController extends BaseController
{
    public function index(Request $request)
    {
        $setting = Setting::setting();
        return $this->json(true, "Successful Fetched", $setting);
    }

    public function update(SettingRequest $request)
    {
        $setting = Setting::setting();

        if (isset($request->payment_multiple)) {
            $setting->payment_multiple = $request->payment_multiple;
        }
        if (isset($request->bank_user)) {
            $setting->bank_user = $request->bank_user;
        }
        if (isset($request->commission_user)) {
            $setting->commission_user = $request->commission_user;
        }
        if (isset($request->gaming_user)) {
            $setting->gaming_user = $request->gaming_user;
        }
        if (isset($request->event_game_commission)) {
            $setting->event_game_commission = $request->event_game_commission;
        }
        $setting->save();

        return $this->json(true, "Successful Fetched", $setting);
    }
}
