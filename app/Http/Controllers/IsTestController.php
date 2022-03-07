<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZanySoft\Zip\Zip;

class IsTestController extends Controller
{
    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $users = User::cursor()->remember();
        $users->each(function($user,$i) use($sheet){
            $i=$i+1;
//            dump($user->name);
            $sheet->setCellValue("A$i",$user->name);
        });

        $writer = new Xlsx($spreadsheet);

//        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//        header('Content-Disposition: attachment; filename="'. urlencode('hello').'"');
//        return $writer->save('php://output');
    }

    public function step3()
    {
        $users = User::query()
            ->leftJoin('user_attributes as ua', 'ua.user_id', '=', 'users.id')
            ->where('users.name', 'like', 'Dr.%')
            ->where('ua.attributes', 'regexp', '\"mobile\"\:\"\+1[0-9-]+"')
            ->orderByRaw('substr(ua.attributes, 5, 10) desc');

        $sql = $users->toSql();

        $begin = microtime(true);
        $users->get();
        $duration = microtime(true) - $begin;

        return view('step3', compact('duration', 'sql'));
    }

    public function result()
    {
        File::delete(resource_path('result.zip'));
        $zip = Zip::create(resource_path('result.zip'), true);
        foreach (['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', '.env', 'composer.json', 'storage', 'tests'] as $add) $zip->add(base_path($add));
        $zip->close();
        return response()->download($zip->getZipFile());
    }
}
