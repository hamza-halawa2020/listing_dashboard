<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitAttachment extends Model
{
    protected $fillable = [
        'visit_id',
        'file_path',
        'file_name',
        'mime_type',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
