<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
    
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
}
