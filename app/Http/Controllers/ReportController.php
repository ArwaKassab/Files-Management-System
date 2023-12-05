<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\User;
use App\Models\Report;
use App\Models\Group_file;

class ReportController extends Controller
{
    public function makeReprot($date, $operation, $fileID, $userName){
        $report = new Report();
        $report->file_id = $fileID ;
        $report->operation_name = $operation ;
        $report->operation_date =  $date ;
        $report->user_name =  $userName ;
        $report->save();
    }
}
