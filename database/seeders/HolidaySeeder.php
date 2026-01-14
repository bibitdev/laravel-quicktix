<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            // Januari 2026
            ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026', 'type' => 'national', 'is_long_weekend' => true],

            // Februari 2026
            ['date' => '2026-02-05', 'name' => 'Isra Miraj Nabi Muhammad SAW', 'type' => 'religious', 'is_long_weekend' => false],
            ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577', 'type' => 'religious', 'is_long_weekend' => false],

            // Maret 2026
            ['date' => '2026-03-14', 'name' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)', 'type' => 'religious', 'is_long_weekend' => false],
            ['date' => '2026-03-27', 'name' => 'Wafat Yesus Kristus', 'type' => 'religious', 'is_long_weekend' => false],
            ['date' => '2026-03-29', 'name' => 'Paskah', 'type' => 'religious', 'is_long_weekend' => true],

            // April 2026
            ['date' => '2026-04-01', 'name' => 'Idul Fitri 1447 H (Hari Pertama)', 'type' => 'religious', 'is_long_weekend' => true],
            ['date' => '2026-04-02', 'name' => 'Idul Fitri 1447 H (Hari Kedua)', 'type' => 'religious', 'is_long_weekend' => true],
            ['date' => '2026-04-03', 'name' => 'Cuti Bersama Idul Fitri', 'type' => 'cuti_bersama', 'is_long_weekend' => true],
            ['date' => '2026-04-06', 'name' => 'Cuti Bersama Idul Fitri', 'type' => 'cuti_bersama', 'is_long_weekend' => true],

            // Mei 2026
            ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional', 'type' => 'national', 'is_long_weekend' => false],
            ['date' => '2026-05-07', 'name' => 'Kenaikan Yesus Kristus', 'type' => 'religious', 'is_long_weekend' => false],
            ['date' => '2026-05-26', 'name' => 'Hari Raya Waisak 2570', 'type' => 'religious', 'is_long_weekend' => false],

            // Juni 2026
            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila', 'type' => 'national', 'is_long_weekend' => false],
            ['date' => '2026-06-08', 'name' => 'Idul Adha 1447 H', 'type' => 'religious', 'is_long_weekend' => false],
            ['date' => '2026-06-27', 'name' => 'Tahun Baru Islam 1448 H', 'type' => 'religious', 'is_long_weekend' => true],

            // Agustus 2026
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan RI', 'type' => 'national', 'is_long_weekend' => false],

            // September 2026
            ['date' => '2026-09-05', 'name' => 'Maulid Nabi Muhammad SAW', 'type' => 'religious', 'is_long_weekend' => true],

            // Desember 2026
            ['date' => '2026-12-24', 'name' => 'Cuti Bersama Natal', 'type' => 'cuti_bersama', 'is_long_weekend' => true],
            ['date' => '2026-12-25', 'name' => 'Hari Raya Natal', 'type' => 'religious', 'is_long_weekend' => true],
        ];

        foreach ($holidays as $holiday) {
            DB::table('holidays')->insert([
                'date' => $holiday['date'],
                'name' => $holiday['name'],
                'type' => $holiday['type'],
                'is_long_weekend' => $holiday['is_long_weekend'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
