<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('subjects')->upsert([
            ['name' => 'Physics'],
            ['name' => ' Geography'],
            ['name' => 'Algebra'],
            ['name' => 'Music'],
            ['name' => 'Science'],
            ['name' => 'Sport'],
            ['name' => 'Technology'],
            ['name' => 'History'],
            ['name' => 'Arabic'],
            ['name' => 'French'],
            ['name' => 'Arts'],
            ['name' => 'Islamic'],
            ['name' => 'Chemistry'],
            ['name' => 'English'],
            ['name' => 'Engineering'],
        ], ['name']);
    }
}
