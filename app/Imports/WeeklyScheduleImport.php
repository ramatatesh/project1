<?php

namespace App\Imports;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\WeeklySchedule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WeeklyScheduleImport implements ToModel, WithHeadingRow
{
    private $gradeName;
    private $classroomName;

    public function __construct($gradeName, $classroomName)
    {
        $this->gradeName = $gradeName;
        $this->classroomName = $classroomName;
    }

    public function model(array $row)
    {
        $grade = Grade::where('name', $this->gradeName)->first();
        $classroom = Classroom::where('grade_id', $grade->id)
                              ->where('name', $this->classroomName)
                              ->first();

        return new WeeklySchedule([
            'grade_id' => $grade->id,
            'classroom_id' => $classroom->id,
            'day' => $row['day'],
            'lesson_1' => $row['lesson_1'],
            'lesson_2' => $row['lesson_2'],
            'lesson_3' => $row['lesson_3'],
            'lesson_4' => $row['lesson_4'],
            'lesson_5' => $row['lesson_5'],
            'lesson_6' => $row['lesson_6'],
            'lesson_7' => $row['lesson_7'] ?? null,
        ]);
    }

}
