<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Exam;
use App\Models\Role;
use App\Models\Level;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Str;
use App\Models\Responsibility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Actions\Exam\CreateScoresTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UploadingExamsScoresManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private Teacher $teacher;

    private Role $role;

    public function setUp(): void
    {
        parent::setUp();

        $this->role = Role::factory()->create();

        $this->teacher = Teacher::factory()->create();

        /** @var Authenticatable */
        $user = $this->teacher->auth()->create([
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->randomElement(['1', '7']) . $this->faker->numberBetween(10000000, 99999999),
            'role_id' => $this->role->id,
            'password' => Hash::make('password')
        ]);
        
        $this->actingAs($user);
        
    }

    /** @group exam-scores */
    public function testAuthorizedTeacherCanVisitPageToUploadLevelSubjectScoresForStudentsWithoutAdmNo()
    {
        $this->withoutExceptionHandling();

        $this->artisan('db:seed --class=SubjectsSeeder');
    
        /** @var Level */
        $level = Level::factory()->create();

        Student::factory(2)->create([
            'adm_no' => null,
            'kcpe_marks' => null,
            'kcpe_grade' => null,
            'stream_id' => null,
            'admission_level_id' => $level->id,
        ]);

        /** @var Subject */
        $subject = Subject::first();

        // Create Responsibility for the current teacher
        $responsibility = Responsibility::firstOrCreate(['name' => 'Subject Teacher']);

        // Associate Teacher and Responsibility
        $this->teacher->responsibilities()->attach($responsibility, [
            'level_id' => $level->id,
            'subject_id' => $subject->id
        ]);

        /** @var Exam */
        $exam = Exam::factory()->create();

        $exam->levels()->attach($level);

        $subjects = Subject::limit(2)->get();

        $exam->subjects()->attach($subjects);

        CreateScoresTable::invoke($exam);

        $response = $this->get(route('exams.scores.upload', [
            'exam' => $exam,
            'subject' => $subject->id,
            'level' => $level->id
        ]));

        $response->assertOk();

        $response->assertViewIs('exams.scores.upload');

        $response->assertViewHasAll(['exam', 'subject', 'segments', 'level', 'gradings', 'data', 'title']);
        
    }

    /** @group exam-scores */
    public function testAuthorizedUserCanUploadLevelSubjectScores()
    {
        $this->withoutExceptionHandling();

        $this->artisan('db:seed --class=SubjectsSeeder');
        $this->artisan('db:seed --class=GradingSeeder');
    
        /** @var Level */
        $level = Level::factory()->create();

        $students = Student::factory(2)->create([
            'kcpe_marks' => null,
            'kcpe_grade' => null,
            'stream_id' => null,
            'admission_level_id' => $level->id,
        ]);

        /** @var Subject */
        $subject = Subject::first();

        // Create Responsibility for the current teacher
        $responsibility = Responsibility::firstOrCreate(['name' => 'Subject Teacher']);

        // Associate Teacher and Responsibility
        $this->teacher->responsibilities()->attach($responsibility, [
            'level_id' => $level->id,
            'subject_id' => $subject->id
        ]);

        /** @var Exam */
        $exam = Exam::factory()->create();

        $exam->levels()->attach($level);

        $subjects = Subject::limit(2)->get();

        $exam->subjects()->attach($subjects);

        // Create Subject Scores
        $scores = array();

        foreach ($students as $student) {
            $scores[$student->id]['score'] = $this->faker->numberBetween(50, 100);
            $scores[$student->id]['extra'] = null;
        }

        CreateScoresTable::invoke($exam);

        $response = $this->put(route('exams.scores.upload', [
            'exam' => $exam,
            'subject' => $subject->id,
            'level' => $level->id
        ]), [
            'scores' => $scores
        ]);

        $tblName = Str::slug($exam->shortname);
        $subCol = $subject->shortname;

        /** @var Collection */
        $data = DB::table($tblName)
            ->select($subCol)
            ->where('level_id', $level->id)
            ->get();

        $this->assertEquals($level->students()->count(), $data->count());

        $data->each(function($item) use($subCol){
            $this->assertNotNull($item->$subCol);
        });

        $dbDriver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($dbDriver == 'mysql') {            
            $response->assertRedirect(route('exams.scores.manage', [
                'exam' => $exam,
                'subject' => $subject->id,
                'level' => $level->id
            ]));
        }

        
    }

    /** @group exam-scores */
    public function testAuthorizedUserCanUploadLevelSegmentSubjectScores()
    {
        $this->withoutExceptionHandling();

        $this->artisan('db:seed --class=GradingSeeder');
    
        /** @var Level */
        $level = Level::factory()->create();

        $students = Student::factory(2)->create([
            'kcpe_marks' => null,
            'kcpe_grade' => null,
            'stream_id' => null,
            'admission_level_id' => $level->id,
        ]);

        /** @var Subject */
        $subject = Subject::factory()->create([
            'name' => 'English',
            'shortname' => 'eng',
            'segments' => [
                $level->id => [ 'outOf60' => 60, 'comp' => 40]
            ]
        ]);

        // Create Responsibility for the current teacher
        $responsibility = Responsibility::firstOrCreate(['name' => 'Subject Teacher']);

        // Associate Teacher and Responsibility
        $this->teacher->responsibilities()->attach($responsibility, [
            'level_id' => $level->id,
            'subject_id' => $subject->id
        ]);

        /** @var Exam */
        $exam = Exam::factory()->create();

        $exam->levels()->attach($level);

        $exam->subjects()->attach($subject);

        // Create Subject Scores
        $scores = array();

        foreach ($students as $student) {
            $scores[$student->id]['outOf60'] = $this->faker->numberBetween(0, 60);
            $scores[$student->id]['comp'] = $this->faker->numberBetween(0, 40);
        }

        CreateScoresTable::invoke($exam);

        $response = $this->put(route('exams.scores.upload', [
            'exam' => $exam,
            'subject' => $subject->id,
            'level' => $level->id
        ]), [
            'scores' => $scores
        ]);

        $tblName = Str::slug($exam->shortname);
        $subCol = $subject->shortname;

        /** @var Collection */
        $data = DB::table($tblName)
            ->select($subCol)
            ->where('level_id', $level->id)
            ->get();

        $this->assertEquals($level->students()->count(), $data->count());

        $data->each(function($item) use($subCol){
            $this->assertNotNull($item->$subCol);
        });

        $dbDriver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if($dbDriver == 'mysql'){
            $response->assertRedirect(route('exams.scores.manage', [
                'exam' => $exam,
                'subject' => $subject->id,
                'level' => $level->id
            ]));
        }

        
    }
    
}
