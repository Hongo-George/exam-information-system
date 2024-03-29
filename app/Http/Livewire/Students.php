<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Level;
use App\Models\Hostel;
use App\Models\Stream;
use App\Models\Student;
use Livewire\Component;
use App\Models\Guardian;
use App\Models\LevelUnit;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use Illuminate\Validation\Rule;
use App\Rules\MustBeKenyanPhone;
use App\Settings\SystemSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use App\Notifications\SendPasswordNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Students extends Component
{
    use WithPagination, WithFileUploads, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['addStudentGuardiansFeedback'];

    public $studentId;

    public $adm_no;
    public $name;
    public $upi;
    public $kcpe_marks;
    public $kcpe_grade;
    public $gender;
    public $dob;
    public $admission_level_id;
    public $level_id;
    public $hostel_id;
    public $stream_id;
    public $description;

    public $student = array(
        'adm_no' => null,
        'name' => null,
        'upi' => null,
        'kcpe_marks' => null,
        'kcpe_grade' => null,
        'gender' => null,
        'dob' => null,
        'admission_level_id' => null,
        'level_id' => null,
        'hostel_id' => null,
        'stream_id' => null,
        'description' => null,
    );

    public $guardian = array(
        'name' => null,
        'email' => null,
        'phone' => null,
        'location' => null,
        'profession' => null
    );

    public $studentsFile;

    public $levels;
    public $streams;
    public $hostels;

    public $trashed = false;

    /**
     * Lifecycle methd that executes once when the component is mounting
     * 
     * @param string $trashed
     */
    public function mount(string $trashed = null)
    {
        $this->levels = $this->getAllLevels();
        $this->streams = $this->getAllStreams();
        $this->hostels = $this->getAllHostels();

        $this->trashed = boolval($trashed);
    }

    /**
     * Lifecycle method that reanders the component when the state of the component changes
     * 
     * @return View
     */
    public function render()
    {
        return view('livewire.students',[
            'students' => $this->getPaginatedStudents(),
            'genderOptions' => User::genderOptions(),
            'kcpeGradeOptions' => Student::kcpeGradeOptions()
        ]);
    }

    /**
     * Get paginated students from the database
     * 
     * @return Paginator
     */
    public function getPaginatedStudents()
    {
        $studentsQuery = Student::with(['level','levelUnit']);

        if($this->trashed) $studentsQuery->onlyTrashed();

        return  $studentsQuery->latest()->paginate(24)->withQueryString();
    }

    /**
     * Get all levels from the database
     * 
     * @return Collection
     */
    public function getAllLevels()
    {
        return Level::all(['id', 'name']);
    }

    /**
     * Get all streams from the database
     * 
     * @return Collection
     */
    public function getAllStreams()
    {
        return Stream::all(['id', 'name']);
    }

    /**
     * Get all hostels from the database
     * 
     * @return Collection
     * 
     */
    public function getAllHostels()
    {
        return Hostel::all(['id', 'name']);
    }

    /**
     * Validation of the original student fields
     * 
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['bail', 'required', 'string'],
            'adm_no' => ['bail', 'nullable', Rule::unique('students')->ignore($this->studentId)],
            'upi' => ['bail', 'nullable'],
            'gender' => ['bail', 'nullable', Rule::in(User::genderOptions())],
            'dob' => ['bail', 'nullable', 'string'],
            'admission_level_id' => ['bail', 'nullable', 'integer'],
            'level_id' => ['bail', 'nullable', 'integer'],
            'hostel_id' => ['bail', 'nullable', 'integer'],
            'stream_id' => ['bail', 'nullable', 'integer'],
            'description' => ['bail', 'nullable'],
            'kcpe_grade' => ['bail', 'nullable', Rule::in(Student::kcpeGradeOptions())],
            'kcpe_marks' => ['bail', 'nullable', 'integer', 'between:1,500']
        ];
    }

    /**
     * Hook to make sure that guardian contact is appropriate
     * 
     * @param mixed $value
     */
    public function updatedGuardian($value)
    {
        if (isset($this->guardian['phone']) && isset($value['phone'])) {
            $this->guardian['phone'] = Str::start($value['phone'], '254');
        }
    }

    /**
     * Persist a new student to the database
     */
    public function addStudent()
    {
        $data = $this->validate();

        $data = array_filter($data, fn($value, $key) => !empty($value), ARRAY_FILTER_USE_BOTH);

        try {

            $this->authorize('create', Student::class);

            /** @var SystemSettings */
            $systemSettings = app(SystemSettings::class);

            if ($systemSettings->school_has_streams) {

                $data['level_unit_id'] = LevelUnit::where([
                    'level_id' => $data['admission_level_id'],
                    'stream_id' => $data['stream_id']
                ])->firstOrFail()->id;
            }
            
            /** @var Student */
            $student = Student::create($data);

            if($student){

                $this->reset();

                $this->resetPage();

                $this->resetValidation();

                session()->flash('status', "Student, {$student->name}, has been successfully added");

                $this->emit('hide-upsert-student-modal');
            }

        } catch (\Exception $exception) {

            Log::error($exception->getMessage(), ['action' => __METHOD__]);
            
            $this->setError($exception, "Failed adding the student, consult the admin if this persists");

            $this->emit('hide-upsert-student-modal');
        }
    }

    /**
     * Launch a modal for updating student
     * 
     * @param Student $student
     */
    public function editStudent(Student $student)
    {
        $this->studentId = $student->id;

        $this->adm_no = $student->adm_no;
        $this->upi = $student->upi;
        $this->name = $student->name;
        $this->dob = optional($student->dob)->format('Y-m-d');
        $this->gender = $student->gender;
        $this->kcpe_marks = $student->kcpe_marks;
        $this->kcpe_grade = $student->kcpe_grade;
        $this->stream_id = $student->stream_id;
        $this->level_id = $student->level_id;
        $this->hostel_id = $student->hostel_id;
        $this->description = $student->description;

        $this->emit('show-upsert-student-modal');
    }


    /**
     * Set appropriate error based on the type of the erro and the environment
     * @param \Exception $exception
     * @param string $message
     */
    private function setError(\Exception $exception, string $message)
    {
        if($exception instanceof AuthorizationException) $message = $exception->getMessage();

        elseif($exception instanceof ModelNotFoundException) $message = 
            "You probably are using streams, and haven't generated the yet, navigate to the classes section and generate classes from streams and levels";

        else $message = App::environment('local') ? $exception->getMessage() : $message;

        session()->flash('error', $message);
    }    

    /**
     * Updated a database student record
     */
    public function updateStudent()
    {
        $data = $this->validate();

        $data = array_filter($data, fn($value, $key) => !empty($value), ARRAY_FILTER_USE_BOTH);

        try {

            /** @var Student */
            $student = Student::findOrFail($this->studentId);

            $this->authorize('update', $student);

            /** @var SystemSettings */
            $systemSettings = app(SystemSettings::class);
            
            if($systemSettings->school_has_streams){
                $data['level_unit_id'] = LevelUnit::where([
                    'level_id' => $data['level_id'],
                    'stream_id' => $data['stream_id']
                ])->firstOrFail()->id;
            }
                
            if($student->update($data)){

                $this->reset();

                $this->resetValidation();

                session()->flash('status', "Student, {$student->fresh()->name}, has been successfully updated");

                $this->emit('hide-upsert-student-modal');

            }

        } catch (\Exception $exception) {

            Log::error($exception->getMessage(), ['action' => __METHOD__]);
            
            $this->setError($exception, "Sorry! Updating student operation filed");

            $this->emit('hide-upsert-student-modal');
        }
        
    }

    /**
     * Add a new student with guardian at a one go
     */
    public function newAddStudent()
    {
        $data = $this->validate([
            'student.name' => ['bail', 'required', 'string'],
            'student.adm_no' => ['bail', 'nullable', Rule::unique('students', 'adm_no')],
            'student.upi' => ['bail', 'nullable'],
            'student.gender' => ['bail', Rule::in(User::genderOptions())],
            'student.dob' => ['bail', 'nullable', 'string'],
            'student.admission_level_id' => ['bail', 'nullable', 'integer'],
            'student.level_id' => ['bail', 'nullable', 'integer'],
            'student.hostel_id' => ['bail', 'nullable', 'integer'],
            'student.stream_id' => ['bail', 'nullable', 'integer'],
            'student.description' => ['bail', 'nullable'],
            'student.kcpe_grade' => ['bail', 'nullable', Rule::in(Student::kcpeGradeOptions())],
            'student.kcpe_marks' => ['bail', 'nullable', 'integer', 'between:1,500'],
            'guardian.name' => ['bail', 'required', 'string'],
            'guardian.email' => ['bail', 'nullable', 'string', 'email', Rule::unique('users', 'email')],
            'guardian.phone' => ['bail', 'required', Rule::unique('users', 'phone'), new MustBeKenyanPhone()],
            'guardian.profession' => ['bail', 'nullable'],
            'guardian.location' => ['bail', 'nullable']
        ]);

        try {

            $this->authorize('create', Student::class);
            $this->authorize('create', Guardian::class);
            $this->authorize('create', User::class);

            $dataStudent = array_filter($data['student'], fn($value, $key) => !empty($value), ARRAY_FILTER_USE_BOTH);

            DB::transaction(function() use($dataStudent, $data){

                /** @var SystemSettings */
                $systemSettings = app(SystemSettings::class);

                if ($systemSettings->school_has_streams) {
                    $dataStudent['level_unit_id'] = LevelUnit::where([
                        'level_id' => $dataStudent['admission_level_id'],
                        'stream_id' => $dataStudent['stream_id']
                    ])->firstOrFail()->id;
                }
                
                /** @var Student */
                $student = Student::create($dataStudent);
    
                /** @var Guardian */
                $guardian = Guardian::create($data['guardian']);

                /** @var User */
                $user = $guardian->auth()->create(array_merge($data['guardian'], ['password' => Hash::make($password = Str::random(6))]));

                // Sending email verification link to the user
                if(!empty($user->email)) $user->sendEmailVerificationNotification();

                // Send the guardian a password
                $user->notifyNow(new SendPasswordNotification($password));

                $student->guardians()->attach($guardian);

            });

            $this->reset(['student', 'guardian']);

            $this->resetPage();

            session()->flash('status', 'The student and guardian have successfully been added');

            $this->emit('hide-add-student-modal');
    
        } catch (\Exception $exception) {

            Log::error($exception->getMessage(), ['action' => __METHOD__]);
            
            $this->setError($exception, "Sorry! Adding student with guardian operation failed");

            $this->emit('hide-add-student-modal');
            
        }
    }

    /**
     * Show a modal for deleting a student
     * 
     * @param Student $student
     */
    public function showDeleteStudentModal(Student $student)
    {
        $this->studentId = $student->id;

        $this->name = $student->name;

        $this->emit('show-delete-student-modal');
    }

    /**
     * Trash a student
     */
    public function deleteStudent()
    {
        
        try {
            
            /** @var Student */
            $student = Student::findOrFail($this->studentId);

            $this->authorize('delete', $student);

            $student->delete();
    
            $this->reset();

            $this->resetPage();

            $this->resetValidation();

            session()->flash('status', 'Student has been successfully deleted');

            $this->emit('hide-delete-student-modal');

        } catch (\Exception $exception) {
         
            Log::error($exception->getMessage(), ['action' => __METHOD__]);
            
            $this->setError($exception, "Sorry! Deleting student operation failed, consult the admin if it persists");

            $this->emit('hide-delete-student-modal');
        }
    }

    /**
     * Attach quardians to student
     * 
     * @param Student $student
     */
    public function showAddStudentGuardiansModal(Student $student)
    {
        $this->emitTo('add-student-guardians', 'showAddStudentGuardiansModal', $student);
    }

    /**
     * Show feedback if a parent has been attach to a student
     * 
     * @param array $payload
     */
    public function addStudentGuardiansFeedback(array $payload)
    {
        session()->flash($payload['type'], $payload['message']);

        $this->emit('hide-add-student-guardians-modal');
        
    }

    /**
     * Archive student for the ones that have completed school
     * 
     * @param Student $student
     */
    public function archiveStudent(Student $student)
    {
        $student->update(["archived_at" => now()]);

        session()->flash('statue', "The student has been archived");
    }

    /**
     * Export students as an excel sheet
     * 
     * @return mixed
     */
    public function downloadSpreadSheet()
    {
        return Excel::download(new StudentsExport, 'students.xlsx');
    }

    /**
     * Download excel file for uploading students
     * 
     * @return mixed
     */
    public function downloadUploadStudentsExcelFile()
    {
        /** @var SystemSettings */
        $systemSettings = app(SystemSettings::class);

        $cols = [["NAME","ADMNO","LEVEL","STREAM","GENDER (Male, Female, Other)","DOB (YYYY-MM-DD)","UPI"]];

        if($systemSettings->school_level === 'secondary')
            $cols = [["NAME","ADMNO","LEVEL","STREAM","GENDER (Male, Female, Other)","DOB (YYYY-MM-DD)","UPI", "KCPEMARKS", "KCPEGRADE"]];
        
        $headers = collect($cols);

        return $headers->downloadExcel("new-students.xlsx");
        
    }

    /**
     * Importing students from an excel file to the database students table
     */
    public function importStudents()
    {
        $data = $this->validate(['studentsFile' => ['file', 'mimes:xlsx,csv,ods,xlsm,xltx,xltm,xls,xlt,xml']]);

        /** @var UploadedFile */
        $file = $data['studentsFile'];

        try {
            
            Excel::import(new StudentsImport, $file);
    
            session()->flash('status', 'Students Successfully imported');
            
            $this->emit('hide-import-student-spreadsheet-modal');

        } catch (\Exception $exception) {
         
            Log::error($exception->getMessage(), [
                'action' => __METHOD__
            ]);

            session()->flash('error', 'A fatal error occurred while trying to import students');
            
            $this->emit('hide-import-student-spreadsheet-modal');
            
        }

    }

    /**
     * Restore a trashed student
     * 
     * @param mixed $studentId
     */
    public function restoreStudent($studentId)
    {
        try {

            /** @var Student */
            $student = Student::where('id', $studentId)->withTrashed()->firstOrFail();

            $this->authorize('restore', $student);

            $student->restore();
            
            session()->flash('status', "Student, {$student->name}, has been successfully restored");

        } catch (\Exception $exception) {
         
            Log::error($exception->getMessage(), ['action' => __METHOD__]);
            
            $this->setError($exception, "Sorry! Restoring student operation failed, consult the admin if it persists");

        }
        
    }

    /**
     * 
     * Completely delete a student from the system
     * 
     * @param mixed $studentId
     * 
     */
    public function destroyStudent($studentId)
    {
        try {

            /** @var Student */
            $student = Student::where('id', $studentId)->withTrashed()->firstOrFail();

            $this->authorize('forceDelete', $student);

            $student->forceDelete();
            
            session()->flash('status', "Student, {$student->name}, has been completely deleted from the application");

        } catch (\Exception $exception) {
         
            Log::error($exception->getMessage(), ['action' => __METHOD__]);
            
            $this->setError($exception, "Sorry! Completely deleting student operation failed, consult the admin if it persists");

        }
        
    }
}
