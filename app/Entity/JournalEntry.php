<?php
namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{

    protected $fillable = [
        'value_date',
        'narration',
        'credit',
        'debit',
        'transaction_id',
        'user_id',
    ];


    public function user(){
        return $this->belongsTo(User::class, "user_id");
    }

    public static function scopeBalance($query)
    {
        $balance = 0;
        foreach ($query->get() as $row){
            $balance += $row->credit - $row->debit;
        }
        return $balance;
    }
}
