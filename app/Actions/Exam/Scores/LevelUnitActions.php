<?php

namespace App\Actions\Exam\Scores;

use App\Exceptions\InvalidConnectionDriverException;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Grading;
use App\Models\LevelUnit;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LevelUnitActions
{
    /**
     * Generate exam aggregates for a level unit
     * 
     * @param Exam $exam
     * @param LevelUnit $levelUnit
     * 
     */
    public static function generateAggregates(Exam $exam, LevelUnit $levelUnit)
    {
        if ($exam->deviationExam) {

            self::generateAggregatesWithDeviations($exam, $levelUnit);

        }else{
            try {
                
                $cols = $exam->subjects->pluck("shortname")->toArray();
    
                $tblName = Str::slug($exam->shortname);
    
                /** @var Collection */
                $data = DB::table($tblName)
                    ->where("level_unit_id", $levelUnit->id)
                    ->select(array_merge(["student_id"], $cols))->get();
    
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
    
                    $avgPoints = round($totalPoints / $populatedCols);
                    $avgScore = round($totalScore / $populatedCols);
    
                    $pgm = Grade::all(['points', 'grade'])->pluck('grade', 'points');
    
                    $avgGrade = $pgm[$avgPoints];
    
                    DB::table($tblName)
                    ->updateOrInsert([
                        "student_id" => $stuData->student_id
                    ], [
                        "mm" => $avgScore,
                        "mg" => $avgGrade,
                        'mp' => $avgPoints,
                        'tp' => $totalPoints,
                        'tm' => $totalScore
                    ]);
                });
    
            } catch (\Exception $exception) {
                throw $exception;
            }        
        }
    }
    /**
     * Generate exam aggregates for a level unit (With deviations)
     * 
     * @param Exam $exam
     * @param LevelUnit $levelUnit
     */
    public static function generateAggregatesWithDeviations(Exam $exam, LevelUnit $levelUnit)
    {
        try {
            $subjectCols = $exam->subjects->pluck("shortname")->toArray();
            
            $examTblName = Str::slug($exam->shortname);

            $cols = array_map(fn($col) => "{$examTblName}.{$col}", $subjectCols);
            
            $devExamTblName = Str::slug($exam->deviationExam->shortname);
    
            /** @var Collection */
            $data = DB::table($examTblName)
                ->leftJoin($devExamTblName, "{$examTblName}.student_id", "=", "{$devExamTblName}.student_id")
                ->where("$examTblName.level_unit_id", $levelUnit->id)
                ->select(array_merge(["$examTblName.student_id"], $cols, ["$devExamTblName.mm AS prev_mm", "$devExamTblName.tm AS prev_tm", "$devExamTblName.tp AS prev_tp", "$devExamTblName.mp AS prev_mp"]))->get();
    
            $data->each(function($stuData) use($examTblName, $subjectCols){
                $prevTm = $stuData->prev_tm;
                $prevTp = $stuData->prev_tp;
                $prevMp = $stuData->prev_mp;
                $prevMm = $stuData->prev_mm;
                $totalScore = 0;
                $totalPoints = 0;
                $populatedCols = 0;
    
                foreach ($subjectCols as $col) {
    
                    if(!is_null($stuData->$col)){
                        $populatedCols++;
    
                        $subData = json_decode($stuData->$col);
    
                        $totalScore += $subData->score ?? 0;
                        $totalPoints += $subData->points ?? 0;
                    }
                }
    
                $avgPoints = round($totalPoints / $populatedCols);
                $avgScore = round($totalScore / $populatedCols);
    
                $pgm = Grade::all(['points', 'grade'])->pluck('grade', 'points');
    
                $avgGrade = $pgm[$avgPoints];
    
                DB::table($examTblName)
                ->updateOrInsert([
                    "student_id" => $stuData->student_id
                ], [
                    "mm" => $avgScore,
                    "mg" => $avgGrade,
                    'mp' => $avgPoints,
                    'tp' => $totalPoints,
                    'tm' => $totalScore,
                    'mmd' => is_null($prevMm) ? 0 : ($avgScore - $prevMm),
                    'tmd' => is_null($prevTm) ? 0 : ($totalScore - $prevTm),
                    'tpd' => is_null($prevTp) ? 0 : ($totalPoints - $prevTp),
                    'mpd' => is_null($prevMp) ? 0 : ($avgPoints - $prevMp),
                    'mpd' => is_null($prevMp) ? 0 : ($avgPoints - $prevMp)
                ]);
            });

        } catch (\Exception $exception) {
            
            throw $exception;
            
        }
        
    }

    /**
     * Generate ranks for a level-unit
     * 
     * @param Exam $exam
     * @param LevelUnit $levelUnit
     * 
     */
    public static function generateRanks(Exam $exam, LevelUnit $levelUnit, string $col = 'tm')
    {
        try {

            $tblName = Str::slug($exam->shortname);

            // Get order records from the database with the admno number as the primary key
            /** @var Collection */
            $data = DB::table($tblName)
                ->select(['student_id', $col])
                ->where('level_unit_id', $levelUnit->id)
                ->orderBy($col, 'desc')
                ->get();

            $data->each(function($item, $key) use($tblName){

                $rank = $key + 1;

                DB::table($tblName)->updateOrInsert(['student_id' => $item->student_id],['sp' => $rank]);
                
            });

        } catch (\Exception $exception) {

            throw $exception;

        }
        
    }

    /**
     * Publish grade distribution got the specified level unit in the specified exam
     * 
     * @param Exam $exam
     * @param LevelUnit $levelUnit
     * 
     */
    public static function publishGradeDistribution(Exam $exam, LevelUnit $levelUnit)
    {
        
        try {
            
            $tblName = Str::slug($exam->shortname);

            /** @var Collection */
            $data = DB::table($tblName)
                ->where('level_unit_id', $levelUnit->id)
                ->selectRaw("mg, COUNT(mg) AS grade_count")
                ->distinct("mg")
                ->groupBy('mg')
                ->get()
                ->pluck('grade_count', 'mg');

            if ($data->count()) {

                DB::beginTransaction();
    
                foreach (Grading::gradeOptions() as $grade) {

                    DB::table('exam_level_unit_grade_distribution')
                        ->updateOrInsert([
                            'exam_id' => $exam->id,
                            'level_unit_id' => $levelUnit->id,
                            'grade' => $grade,
                        ],['grade_count' => $data[$grade] ?? 0]);
                }

                DB::commit();
    
            }

        } catch (\Exception $exception) {

            DB::rollBack();

            throw $exception;
        }
    }

    /**
     * Publishing subject performance for the specified level unit in the specified exam
     * 
     * @param Exam $exam
     * @param LevelUnit $levelUnit
     * 
     * @return bool
     */
    public static function publishSubjectPerformance(Exam $exam, LevelUnit $levelUnit) : bool
    {

        try {

            $subjectsWithPreviousScores = collect([]);

            /** @var Exam */
            $deviationExam = $exam->deviationExam;

            if ($deviationExam) {
                $subjectsWithPreviousScores = $deviationExam->levelUnitSubjectPerformance()
                    ->wherePivot('level_unit_id', $levelUnit->id)
                    ->get();
            }

            $driverName = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

            if(strtolower($driverName) == 'mysql'){
                DB::transaction(function()use($exam, $levelUnit, $subjectsWithPreviousScores){
    
                    $tblName = Str::slug($exam->shortname);
    
                    $atLeastASubjectPublished = false;
        
                    foreach ($exam->subjects as $subject) {
        
                        $col = $subject->shortname;
        
                        $data = DB::table($tblName)
                            ->selectRaw("AVG(JSON_UNQUOTE(JSON_EXTRACT($col, \"$.points\"))) AS avg_points, AVG(JSON_UNQUOTE(JSON_EXTRACT($col, \"$.score\"))) AS avg_score")
                            ->where('level_unit_id', $levelUnit->id)
                            ->whereNotNull($col)
                            ->first();
        
                        if (!is_null($data->avg_points) && !is_null($data->avg_score)) {
        
                            $atLeastASubjectPublished = true;
                            
                            $avgTotal = number_format($data->avg_score, 2);
                            $avgPoints = number_format($data->avg_points, 4);
                            $prevAvgTotal = optional(optional($subjectsWithPreviousScores->where('id', $subject->id)->first())->pivot)->average;
                            $prevAvgPoints = optional(optional($subjectsWithPreviousScores->where('id', $subject->id)->first())->pivot)->points;
            
                            $pgm = Grade::all(['points', 'grade'])->pluck('grade', 'points');
            
                            $avgGrade = $pgm[intval(round($avgPoints))];
            
                            DB::table('exam_level_unit_subject_performance')
                                ->updateOrInsert([
                                    'exam_id' => $exam->id,
                                    'level_unit_id' => $levelUnit->id,
                                    'subject_id' => $subject->id
                                ], [
                                    'average' => $avgTotal,
                                    'points' => $avgPoints,
                                    'grade' => $avgGrade,
                                    'average_deviation' => !empty($prevAvgTotal) ? ($avgTotal - $prevAvgTotal) : null,
                                    'points_deviation' => !empty($prevAvgPoints) ? ($avgPoints - $prevAvgPoints) : null,
                                ]);
                        }
        
                    }
    
                    return $atLeastASubjectPublished;
    
                });

            }else{

                throw new InvalidConnectionDriverException(
                    "Not sure whether your db driver connection can handle this"
                );
                
            }
            
        } catch (\Exception $exception) {

            throw $exception;

        }
        
        return false;
    }

    /**
     * Publish LevelUnit Exam Scores (With deviations included)
     * 
     * @param Exam $exam
     * @param LevelUnit $levelUnit
     */
    public static function publishScores(Exam $exam, LevelUnit $levelUnit)
    {
        /** @var Exam */
        $deviationExam = $exam->deviationExam;

        $levelUnitWithPreviousScores = null;

        if($deviationExam){
            $levelUnitWithPreviousScores = $deviationExam->levelUnits()
                ->where('level_units.id', $levelUnit->id)
                ->first();
        }

        try {

            $tblName = Str::slug($exam->shortname);

            $data = DB::table($tblName)
                ->where("level_unit_id", $levelUnit->id)
                ->selectRaw("AVG(tm) AS avg_total, AVG(mp) avg_points")
                ->first();
            
            $avgTotal = number_format($data->avg_total, 2);
            $avgPoints = number_format($data->avg_points, 4);

            $previousAvgTotal = optional(optional($levelUnitWithPreviousScores)->pivot)->average;
            $previousAvgPoints = optional(optional($levelUnitWithPreviousScores)->pivot)->points;

            $pgm = Grade::all(['points', 'grade'])->pluck('grade', 'points');

            $avgGrade = $pgm[intval(round($avgPoints))];

            $exam->levelUnits()->syncWithoutDetaching([
                $levelUnit->id => [
                    "points" => $avgPoints,
                    "grade" => $avgGrade,
                    "average" => $avgTotal,
                    "points_deviation" => !is_null($previousAvgPoints) ? ($avgPoints - $previousAvgPoints) : 0,
                    "average_deviation" => !is_null($previousAvgTotal) ? ($avgTotal - $previousAvgTotal) : 0
                ]
            ]);

        } catch (\Exception $exception) {
            
            throw $exception;

        }
        
    }

    /**
     * Publish student results at level unit group (Deviations included)
     * 
     * @param Exam $exam
     * @param LevelUnit $level
     */
    public static function publishStudentResults(Exam $exam, LevelUnit $levelUnit)
    {
        
        try {
            
            $tblName = Str::slug($exam->shortname);

            $aggregateColums = ["mm", "tm", "op", "mg", "mp", "tp", "sp", "mmd", "tmd", "tpd", "mpd"];

            /** @var Collection */
            $data = DB::table($tblName)->select(array_merge(["students.id"], $aggregateColums))
                ->join("students", "{$tblName}.student_id", '=', 'students.id')
                ->where("{$tblName}.level_unit_id", $levelUnit->id)
                ->get();

            if ($data->count()) {
                
                $data->each(function($item) use($exam){
    
                    DB::table('exam_student')
                        ->updateOrInsert(['exam_id' => $exam->id,'student_id' => $item->id], [
                            'mm' => $item->mm,
                            'tm' => $item->tm,
                            'mp' => $item->mp,
                            'tp' => $item->tp,
                            'mg' => $item->mg,
                            'sp' => $item->sp ?? null,
                            'op' => $item->op,
                            'mmd' => $item->mmd,
                            'tmd' => $item->tmd,
                            'tpd' => $item->tpd,
                            'mpd' => $item->mpd
                        ]);
    
                });
                
            }

        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Publishing exam top students per subject
     * 
     * @param Exam $exam - the exam to publish
     * @param LevelUnit $levelUnit
     * @param int $howMany
     */
    public static function publishExamTopStudentsPerSubject(Exam $exam, LevelUnit $levelUnit, int $howMany = 3)
    {
        // Check the database driver
        $dbDriver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if(strtolower($dbDriver) == 'mysql'){
            
            DB::transaction(function() use($exam, $levelUnit, $howMany){

                $examTblName = Str::slug($exam->shortname);

                $exam->subjects->each(function($subject) use($exam, $examTblName, $howMany, $levelUnit){
    
                    $subjectCol = $subject->shortname;
                    $subjectId = $subject->id;

                    DB::table('exam_level_unit_top_students_per_subject')
                        ->where([
                            ['exam_id' , $exam->id],
                            ['level_unit_id' , $levelUnit->id],
                            ['subject_id' , $subjectId]
                        ])->delete();

    
                    /** @var Collection */
                    $data = DB::table($examTblName)
                        ->selectRaw("student_id, CAST(JSON_UNQUOTE(JSON_EXTRACT({$subjectCol}, \"$.score\")) AS UNSIGNED) AS score, JSON_UNQUOTE(JSON_EXTRACT({$subjectCol}, \"$.grade\")) AS grade")
                        ->where('level_unit_id', $levelUnit->id)
                        ->orderBy("score", 'desc')
                        ->limit($howMany)
                        ->get();
    
                    $data->each(function($item) use($exam, $levelUnit, $subjectId){
                        DB::table('exam_level_unit_top_students_per_subject')
                            ->updateOrInsert([
                                'exam_id' => $exam->id,
                                'level_unit_id' => $levelUnit->id,
                                'subject_id' => $subjectId,
                                'student_id' => $item->student_id
                            ],[
                                'score' => $item->score,
                                'grade' => $item->grade
                            ]);
                    });
                });

            });

            
        }else{

            throw new InvalidConnectionDriverException("MySQL, will the best companion in this");

        }
        
    }

}
