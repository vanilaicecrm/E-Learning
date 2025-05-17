<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'major_id'];

    public function major()
{
    return $this->belongsTo(Major::class);
}

public function materials()
{
    return $this->hasMany(Material::class);
}

public function users()
{
    return $this->hasMany(User::class);
}

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

}
