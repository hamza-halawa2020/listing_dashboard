<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Contact extends Model
{
    protected $table = 'contacts';

    protected $fillable = [
        'name',
        'phone',
        'message',
        'source',
        'comment_count',
        'last_commented_at',
    ];

    protected function casts(): array
    {
        return [
            'last_commented_at' => 'datetime',
        ];
    }

    public function scopeContactMessages(Builder $query): Builder
    {
        return $query->where('source', 'contact_form');
    }

    public function scopeGuestCommenters(Builder $query): Builder
    {
        return $query->where('source', 'guest_comment');
    }
}
