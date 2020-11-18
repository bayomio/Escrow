<?php
namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $fillable = [
        'txref',
        'user_id',
        'event_id',
        'amount',
        'narration',
        'status',
        'request_type', //DEPOSIT or WITHDRAW or EVENT
    ];

    public function user(){
        return $this->belongsTo(User::class, "user_id");
    }

    public function journals(){
        return $this->hasMany(JournalEntry::class, "transaction_id");
    }

}
