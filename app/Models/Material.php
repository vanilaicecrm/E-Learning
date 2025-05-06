<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'file', 'major_id', 'grade_id'];

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
