<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class PackageQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'package_id'
    ];

    public function packages(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
