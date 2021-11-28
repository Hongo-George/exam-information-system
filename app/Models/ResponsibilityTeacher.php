<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ResponsibilityTeacher extends Pivot
{
    use HasFactory;

    public $incrementing = true;

    public function level()
    {
        return $this->belongsTo(Level::class)->withDefault([
            'name' => 'N/A'
        ]);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class)->withDefault([
            'name' => 'N/A'
        ]);
    }

    public function department()
    {
        return $this->belongsTo(Department::class)->withDefault([
            'name' => 'N/A'
        ]);
    }

    public function levelUnit()
    {
        return $this->belongsTo(LevelUnit::class)->withDefault([
            'alias' => 'N/A'
        ]);
    }

    public function responsibility()
    {
        return $this->belongsTo(Responsibility::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function scopeFor($query, Teacher $teacher)
    {
        return $query->where('teacher_id', $teacher->id);
    }

}
