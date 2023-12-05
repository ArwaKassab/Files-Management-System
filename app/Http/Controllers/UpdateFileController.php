<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthController;
use App\Models\File;
use App\Models\User;
use App\Models\Report;
use App\Models\Group_file;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateFileController extends Controller
{
    /**
     * ------------------UPDATE THE FILE-----------------
     */

    public function updateFile(Request $request){
        \Log::info('Attempting to reach updateFile method.');
        
        // Find the file by ID
        $fileId = $request->input('fileId');
        $file = File::findOrFail($fileId);
        $user = auth()->user();
        \Log::info('File ID: ' . $fileId);
    
        \Log::info('Request data before validation: ' . json_encode($request->all()));
        
        // Validate the updated file
        $request->validate([
            'updated_file' => 'required|mimes:txt,docx,pdf,jpg,png',
        ]);

        \Log::info('Validation passed.');
    
        if($file->state != $user->id){
            return response()->json([
                'message' => 'File is un available!',
            ], 401);
        }
    
        // Process the updated file
        if ($request->file('updated_file')->isValid()) {
            $updatedFile = $request->file('updated_file');
    
            // Store the updated file in the same path, overwriting the existing file
            $updatedFileName = $updatedFile->getClientOriginalName();
            $updatedFilePath = $updatedFile->storeAs('uploads', $updatedFileName);
    
            // For example, update the 'name' attribute to the new file name
            $file->name = $updatedFileName;
            $file->save();
    
                $date = Carbon::now();
                $report = new Report();
                $report->file_id = $file->id ;
                $report->operation_name = "Update" ;
                $report->operation_date =  $date ;
                $report->user_name =  $user->name  ;
                $report->save();
    
            return response()->json(['message' => 'File updated successfully', 'path' => $file->path]);
        }
    
        return response()->json(['message' => 'Invalid updated file'], 400);
        }
    
}
