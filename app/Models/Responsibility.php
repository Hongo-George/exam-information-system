<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Responsibility extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        
        $this->attributes['slug'] = Str::slug($value);
    }

    public function teacher()
    {
        return $this->belongsToMany(Teacher::class)
            ->using(ResponsibilityTeacher::class)
            ->withTimestamps()
            ->withPivot(['level_id', 'level_unit_id', 'subject_id', 'department_id']);
    }
}
