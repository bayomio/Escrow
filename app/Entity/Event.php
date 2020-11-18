<?php


namespace App\Entity;


use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

    protected $fillable = [
        'name',
        'description',
        'start',
        'end',
        'streaming_link',
        'event_image',
        'paid',
        'sent',
        'sent_one_hour',
        'sent_fifteen',
        'sent_voters',
        'home_player_score',
        'away_player_score',
        'result', // HOME WIN, DRAW ​& A​WAY WIN​.
    ];

    protected $appends = ['event_image_url'];

    public function getEventImageUrlAttribute()
    {
        if(isset($this->event_image)){
            return asset('event_image/'.$this->event_image);
        }
    }

    public static function scopeSearch($query, $searchTerm)
    {
        if(isset($searchTerm)){
            $query = $query->where('name', 'LIKE', "%$searchTerm%");
        }

        return $query->paginate();
    }

    public function players(){
        return $this->hasMany(EventPlayer::class, "event_id");
    }

    public function voters(){
        return $this->hasMany(EventVoter::class, "event_id");
    }

    public function transactions(){
        return $this->hasMany(Transaction::class, "event_id");
    }
}
