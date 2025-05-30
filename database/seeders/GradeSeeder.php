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
            ['name' => '10'],
            ['name' => '11 '],
            ['name' => '12 '],
        ], ['name']);
    }
}
