<?php

namespace App\Http\Controllers;

use App\Models\CsvData;
use Illuminate\Http\Request;
use App\Imports\HoroscopesImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\CsvImportRequest;
use Maatwebsite\Excel\HeadingRowImport;

class ImportController extends Controller
{
    public function parseImport(CsvImportRequest $request)
    {
        if ($request->has('header')) {
            $headings = (new HeadingRowImport)->toArray($request->file('csv_file'));
            $data = Excel::toArray(new HoroscopesImport, $request->file('csv_file'))[0];
        } else {
            $file = $request->file('csv_file');
            $csvData = file_get_contents($file);
            $delimiter = '|';
            $data = array_map(function ($d) use ($delimiter) {
                return str_getcsv($d, $delimiter);
            }, explode("\n", $csvData));
        }

        if (count($data) > 0) {
            $csv_data = array_slice($data, 0, 2);

            $csv_data_file = CsvData::create([
                'csv_filename' => $request->file('csv_file')->getClientOriginalName(),
                'csv_header' => $request->has('header'),
                'csv_data' => json_encode($data)
            ]);
        } else {
            return redirect()->back();
        }

        return view('import_fields', [
            'headings' => $headings ?? null,
            'csv_data' => $csv_data,
            'csv_data_file' => $csv_data_file
        ]);
    }

    public function processImport(Request $request)
    {
        $data = CsvData::find($request->csv_data_file_id);
        $csv_data = json_decode($data->csv_data, true);
        $horoscopes = [];

        foreach ($csv_data as $row) {

            $row = $this->removeEmptyArrays($row);
            if (!empty($row)) {
                $horoscope = [];

                foreach (config('app.db_fields') as $index => $field) {

                    if ($data->csv_header) {
                        $fields = array_flip($request->fields);
                        $key = $fields[$field];
                        $value = $row[$key];

                        if ($this->checkIsAValidDate($value)) {
                            $horoscope[$field] = date('Y-m-d', strtotime($value));
                        } else {
                            $horoscope[$field] = $value;
                        }

                    } else {
                        $value = $row[$request->fields[$index]] ?? null;
                        if ($this->checkIsAValidDate($value)) {
                            $value = date('Y-m-d', strtotime($value));
                        }
                        $horoscope[$field] = $value;
                    }
                }
                $horoscopes[] = $horoscope;
            }
        }
        DB::table('horoscopes')->insertOrIgnore($horoscopes);
        return redirect()->route('horoscopes.index')->with('success', 'Import finished.');
    }


    private function checkIsAValidDate($str)
    {
        return (bool)\DateTime::createFromFormat('d-m-Y', $str) !== false;
    }

    private function removeEmptyArrays($array)
    {
        // Filtra l'array interno per rimuovere solo quelli con valori non vuoti o nulli
        $filtered = array_filter($array, function ($innerArray) {
            return !empty(array_filter([$innerArray], function ($value) {
                return !is_null(trim($value)) && trim($value) !== "";
            }));
        });

        // Reindirizza gli indici dell'array
        return $filtered;
    }

}