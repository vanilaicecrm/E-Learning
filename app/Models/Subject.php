<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'major_id',
        'grade_id',
        'ai_summary_enabled',
    ];

    // Relasi ke jurusan
    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    // Relasi ke tingkat/kelas
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    // Relasi ke materi
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}
