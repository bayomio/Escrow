<?php
namespace App\Entity;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventVoter extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'quantity',
        'position', //HOME, AWAY, DRAW
    ];

    public function user(){
        return $this->belongsTo(User::class, "user_id");
    }

}
