<?php

namespace App\Http\Livewire;

use App\Models\Department;
use App\Models\Level;
use App\Models\LevelUnit;
use App\Models\Responsibility;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class TeacherResponsibilities extends Component
{
    public Teacher $teacher;

    public $responsibility_id;
    public $level_unit_id;
    public $level_id;
    public $department_id;
    public $subject_id;

    public $type = null;

    public function mount(Teacher $teacher)
    {
        $this->teacher = $teacher;
    }

    public function render()
    {
        return view('livewire.teacher-responsibilities', [
            'responsibilities' => $this->getTeacherResponsibilities(),
            'responsibilityOptions' => $this->getResponsibilities(),
            'levels' => $this->getLevels(),
            'subjects' => $this->getSubjects(),
            'departments' => $this->getDepartments(),
            'levelUnits' => $this->getLevelUnits()
        ]);
    }

    public function getResponsibilities()
    {
        return Responsibility::all(['id', 'name']);
    }

    public function getLevels()
    {
        return Level::all(['id', 'name']);
    }

    public function getSubjects()
    {
        return $this->teacher->subjects;
    }

    public function getLevelUnits()
    {
        return LevelUnit::all(['id', 'alias']);
    }

    public function getDepartments()
    {
        return Department::all(['id', 'name']);
    }

    public function getTeacherResponsibilities()
    {
        return $this->teacher->fresh()->responsibilities;
    }

    public function rules()
    {
        return [
            'responsibility_id' => ['bail', 'required', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'level_id' => ['nullable', 'integer'],
            'level_unit_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
        ];
    }

    public function assignResponsibility()
    {
        $data = $this->validate();

        try {

            $id = $data['responsibility_id'];

            unset($data['responsibility_id']);
            
            $this->teacher->responsibilities()->attach($id, $data);

            session()->flash('status', "{$this->teacher->auth->name} has been assigned a new responsibility");

            $this->reset(['responsibility_id', 'level_unit_id', 'level_id', 'department_id', 'subject_id']);

            $this->emit('hide-assign-teacher-responsibility-modal');

        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), [
                'action' => __METHOD__,
                'teacher' => $this->teacher->id,
            ]);
        }
        
    }

    public function removeResponsibility(Responsibility $responsibility)
    {
        $this->teacher->responsibilities()->detach($responsibility);

        session()->flash('status', "{$this->teacher->auth->name} responsibility has been removed");
    }
}
