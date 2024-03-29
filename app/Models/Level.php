<?php

namespace App\Models;

use App\Http\Livewire\Exams;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Level extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'numeric',
        'description'
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;

        $this->attributes['slug'] = Str::slug($value);
        
    }

    /**
     * Level student relation declaration
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function exams()
    {
        return $this->belongsToMany(Exams::class)
            ->withTimestamps()
            ->withPivot(['points', 'grade', 'average']);
    }

    /**
     * Level - Level Unit relation
     */
    public function levelUnits()
    {
        return $this->hasMany(LevelUnit::class);
    }

    /**
     * Level Responsibility relation
     */
    public function responsibilities()
    {
        return $this->belongsToMany(Responsibility::class, 'responsibility_teacher')
            ->using(ResponsibilityTeacher::class)
            ->withTimestamps()
            ->withPivot(['teacher_id', 'subject_id']);
    }

    public function optionalSubjects()
    {
        return $this->belongsToMany(Subject::class)
            ->withTimestamps();
    }
}
