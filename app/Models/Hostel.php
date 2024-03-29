<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hostel extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        
        $this->attributes['slug'] = Str::slug($value);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

}
