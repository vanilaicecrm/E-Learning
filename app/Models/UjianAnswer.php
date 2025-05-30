<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'ujian_id',
        'question_id',
        'option_id',
        'score',
    ];
}
