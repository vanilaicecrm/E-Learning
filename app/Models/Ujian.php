<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ujian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'duration',
        'started_at',
        'finished_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(related: Package::class);
    }

    public function ujianAnswers()
    {
        return $this->hasMany(UjianAnswer::class);
    }
}
