<?php
namespace App\Entity;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPlayer extends Model
{

    protected $fillable = [
        'event_id',
        'user_id',
        'score',
        'position', //HOME ot AWAY
    ];

    public function user(){
        return $this->belongsTo(User::class, "user_id");
    }

}
