<?php

namespace App\Http\Livewire;

use App\Models\Exam;
use App\Models\Level;
use App\Models\Grading;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LevelExamScores extends Component
{
    public Exam $exam;
    public Level $level;

    public function mount(Exam $exam, Level $level)
    {
        $this->exam = $exam;
        $this->level = $level;
    }

    public function render()
    {
        return view('livewire.level-exam-scores', [
            'data' => $this->getResults(),
            'cols' => $this->getColumns(),
            'subjectCols' => $this->getSubjectColumns()
        ]);
    }

    public function getResults()
    {
        $tblName = Str::slug($this->exam->shortname);

        /** @var array */
        $columns = $this->getSubjectColumns();

        /** @var array */
        $aggregateCols = array("average", "total", "grade", "points");

        return Schema::hasTable($tblName)
            ? DB::table($tblName)
                ->select(array_merge(["admno"], $columns, $aggregateCols))
                ->addSelect("students.name", "level_units.alias")
                ->join("students", "{$tblName}.admno", '=', 'students.adm_no')
                ->join("level_units", "{$tblName}.level_unit_id", '=', 'level_units.id')
                ->where("{$tblName}.level_id", $this->level->id)
                ->paginate(24)
            : collect([]);
    }

    public function getSubjectColumns()
    {
       return $this->exam->subjects->pluck("shortname")->toArray();
    }

    public function getColumns()
    {
        /** @var array */
        $columns = $this->exam->subjects->pluck("shortname")->toArray();

        /** @var array */
        $aggregateCols = array("average", "total", "grade", "points");

        /** @var array */
        $studentLevelCols = array("name", "alias");

        return array_merge($studentLevelCols, $columns, $aggregateCols);;
    }

    public function publishLevelScores()
    {
        try {

            $tblName = Str::slug($this->exam->shortname);

            $data = DB::table($tblName)
                ->where("level_id", $this->level->id)
                ->selectRaw("AVG(total) AS avg_total, AVG(points) avg_points")
                ->first();
            
            $avgTotal = number_format($data->avg_total, 2);
            $avgPoints = number_format($data->avg_points, 4);

            $pgm = Grading::pointsGradeMap();

            $avgGrade = $pgm[intval(round($avgPoints))];

            $this->exam->levels()->syncWithoutDetaching([
                $this->level->id => [
                    "points" => $avgPoints,
                    "grade" => $avgGrade,
                    "average" => $avgTotal
                ]
            ]);

            session()->flash('status', 'Your class scores have been successfully published, you can republish the scores incase of any changes');

            $this->emit('hide-publish-class-scores-modal');

        } catch (\Exception $exception) {

            Log::error($exception->getMessage(), [
                'action' => __METHOD__
            ]);

            session()->flash('error', 'A fatal error occurred');

            $this->emit('hide-publish-class-scores-modal');

        }
        
    }
}
