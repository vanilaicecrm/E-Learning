<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{

    use HasFactory;

    protected $fillable = [
    'name',
    'duration'
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(PackageQuestion::class, foreignKey: 'package_id');
    }
}
