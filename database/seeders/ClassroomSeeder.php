<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Grade;
use App\Models\Classroom;


class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = Grade::all();

    foreach ($grades as $grade) {
        foreach (['A', 'B', 'C', 'D'] as $sectionName) {
            Classroom::updateOrCreate(
                ['grade_id' => $grade->id, 'name' => $sectionName],
                []
            );
        }
    }
    }
}
