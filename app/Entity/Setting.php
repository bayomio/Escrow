<?php
namespace App\Entity;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{

    protected $fillable = [
        'payment_multiple',
        'bank_user',
        'commission_user',
        'gaming_user',
        'event_game_commission',
    ];



    public static function scopeSetting($query)
    {
        $settings = $query->get();
        if(count($settings) > 0){
            return $settings[0];
        }
        return new Setting();
    }
}
