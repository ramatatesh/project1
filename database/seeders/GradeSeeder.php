<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class GradeSeeder extends Seeder
{
    public function run()
    {

        DB::table('grades')->upsert([
            ['name' => 'العاشر'],
            ['name' => 'الحادي عشر'],
            ['name' => 'الثاني عشر'],
        ], ['name']);
    }
}
