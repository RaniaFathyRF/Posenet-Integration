<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class sizechart extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filePath = database_path('seeders/size_chart.csv');

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = null;
            $data = [];

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                    continue;
                }

                $data[] = array_combine($header, $row);
            }

            fclose($handle);

            foreach ($data as &$entry) {
                $entry = [
                    'gender' => $entry['gender'],
                    'height_min' => $entry['height_min'],
                    'height_max' => $entry['height_max'],
                    'weight_min' => $entry['weight_min'],
                    'weight_max' => $entry['weight_max'],
                    'size' => $entry['size'],
                ];
            }

            DB::table('sizes_gender')->insert($data);
        }
    }
}
