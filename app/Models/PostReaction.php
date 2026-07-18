<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReaction extends Model
{
    protected $fillable = ['post_id', 'character_id', 'type'];

    public function post()    { return $this->belongsTo(Post::class); }
    public function character() { return $this->belongsTo(Character::class); }
}
