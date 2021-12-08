<?php

namespace App\Http\Livewire;

use App\Models\Exam;
use App\Models\Grading;
use Livewire\Component;
use App\Models\LevelUnit;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LevelUnitExamScores extends Component
{
    public $exam;

    public $levelUnit;

    public $admno;

    public function mount(Exam $exam, LevelUnit $levelUnit)
    {
        $this->exam = $exam;
        $this->levelUnit = $levelUnit;
    }

    public function render()
    {
        return view('livewire.level-unit-exam-scores', [
            'data' => $this->getResults(),
            'cols' => $this->getColumns(),
            'subjectCols' => $this->getSubjectColumns()
        ]);
    }


    public function getResults()
    {
        $tblName = Str::slug($this->exam->shortname);

        /** @var array */
        $columns = $this->exam->subjects->pluck("shortname")->toArray();

        /** @var array */
        $aggregateCols = array("average", "total");

        return Schema::hasTable($tblName)
            ? DB::table($tblName)
                ->select(array_merge(["admno"], $columns, $aggregateCols))
                ->addSelect("students.name", "level_units.alias")
                ->join("students", "{$tblName}.admno", '=', 'students.adm_no')
                ->join("level_units", "{$tblName}.level_unit_id", '=', 'level_units.id')
                ->where("{$tblName}.level_unit_id", $this->levelUnit->id)
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
        $aggregateCols = array("average", "total");

        /** @var array */
        $studentLevelCols = array("name", "alias");

        return array_merge($studentLevelCols, $columns, $aggregateCols);;
    }

    public function generateBulkAggregates()
    {
        try {
            
            $cols = $this->getSubjectColumns();

            $tblName = Str::slug($this->exam->shortname);

            /** @var Collection */
            $data = DB::table($tblName)
                ->where("level_unit_id", $this->levelUnit->id)
                ->select(array_merge(["admno"], $cols))->get();

            $data->each(function($stuData) use($tblName, $cols){
                $totalScore = 0;
                $totalPoints = 0;
                $populatedCols = 0;

                foreach ($cols as $col) {

                    if(!is_null($stuData->$col)){
                        $populatedCols++;

                        $subData = json_decode($stuData->$col);

                        $totalScore += $subData->score ?? 0;
                        $totalPoints += $subData->points ?? 0;
                    }
                }

                $avgPoints = intval($totalPoints / $populatedCols);
                $avgScore = intval($totalScore / $populatedCols);

                $pgm = Grading::pointsGradeMap();

                $avgGrade = $pgm[$avgPoints];

                DB::table($tblName)
                ->updateOrInsert([
                    "admno" => $stuData->admno
                ], [
                    "average" => json_encode([
                        'score' => $avgScore,
                        'grade' => $avgGrade,
                        'points' => $avgPoints,
                    ]),
                    'total' => $totalScore
                ]);
            });

            session()->flash('status', 'Aggregates for the whole class successfully generated');

            $this->emit('hide-generate-scores-aggreagetes');

        } catch (\Exception $exception) {

            Log::error($exception->getMessage(), [
                'action' => __METHOD__
            ]);

            session()->flash('error', 'A fatal error occurred');

            $this->emit('hide-generate-scores-aggreagetes');
            
        }
        
    }

    public function showGenerateAggregatesModal(string $admno)
    {
        $this->admno = $admno;

        $this->emit('show-generate-scores-aggreagetes');
        
    }

    public function generateAggregates()
    {

        try {
            
            $cols = $this->getSubjectColumns();

            $tblName = Str::slug($this->exam->shortname);

            $stuData = DB::table($tblName)
                ->where("level_unit_id", $this->levelUnit->id)
                ->where('admno', $this->admno)
                ->select(array_merge(["admno"], $cols))->first();

            $totalScore = 0;
            $totalPoints = 0;
            $populatedCols = 0;

            foreach ($cols as $col) {

                if(!is_null($stuData->$col)){
                    $populatedCols++;

                    $subData = json_decode($stuData->$col);

                    $totalScore += $subData->score ?? 0;
                    $totalPoints += $subData->points ?? 0;
                }
            }

            $avgPoints = intval($totalPoints / $populatedCols);
            $avgScore = intval($totalScore / $populatedCols);

            $pgm = Grading::pointsGradeMap();

            $avgGrade = $pgm[$avgPoints];

            DB::table($tblName)
            ->updateOrInsert([
                "admno" => $stuData->admno
            ], [
                "average" => json_encode([
                    'score' => $avgScore,
                    'grade' => $avgGrade,
                    'points' => $avgPoints,
                ]),
                'total' => $totalScore
            ]);

            $this->reset('admno');

            session()->flash('status', "Aggregates for {$this->admno} class successfully generated");

            $this->emit('hide-generate-scores-aggreagetes');   

        } catch (\Exception $exception) {

            Log::error($exception->getMessage(), [
                'action' => __METHOD__
            ]);

            $this->reset('admno');

            session()->flash('error', 'A fatal error occurred');

            $this->emit('hide-generate-scores-aggreagetes');
            
        }
        
    }
        
}