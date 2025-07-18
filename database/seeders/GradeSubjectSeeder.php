<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Subject;

class GradeSubjectSeeder extends Seeder
{
    public function run(): void
    {
        // استبعاد المواد غير المطلوبة
        $excludedSubjects = ['الرياضة', 'الرسم', 'الموسيقا'];

        // جلب كل المواد باستثناء المستثناة
        $subjects = Subject::whereNotIn('name', $excludedSubjects)->pluck('id');

        // ربط جميع الصفوف بهذه المواد
        Grade::all()->each(function ($grade) use ($subjects) {
            $grade->subjects()->sync($subjects);
        });

        echo "تم ربط المواد بالصفوف بنجاح.\n";
    }
}
