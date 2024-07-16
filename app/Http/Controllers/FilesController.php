<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Models\File;
use App\Models\User;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Services\FileService;


class FilesController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

/***
 * ------------------Get ALL Reports-----------------
 */

    public function getAllReports()
    {
        $reports = $this->fileService->getAllReports();
        return response()->json(['files' => $reports], 200);
    }


/***
 * ------------------Get ALL FILES-----------------
 */
    public function getAllFiles()
    {
        $files = $this->fileService->getAllFiles();
        return response()->json(['files' => $files], 200);
    }


/**
 * ------------------UPLOAD FILE WITH EXTENTIONS txt,docx,pdf,jpg,png-----------------
 */

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:txt,docx,pdf,jpg,png',
        ]);

        try {
            $uploadedFile = $request->file('file');
            $file = $this->fileService->uploadFile($uploadedFile);

            return response()->json(['message' => 'File uploaded successfully', 'path' => $file->path]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

/**
 * ------------------DOWNLOAD THE FILE-----------------
 */
    public function downloadFile($fileId)
    {
        try {
            $filePath = $this->fileService->downloadFile($fileId);
            $fileName = basename($filePath);

            return response()->download($filePath, $fileName, [
                'Content-Type' => mime_content_type($filePath),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

/**
 * ------------------Delete THE FILE-----------------
 */


    public function deleteFile($id)
    {
        try {
            $this->fileService->deleteFile($id);
            return response()->json(['message' => 'File deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

/**
 * ------------------CHECK IN THE FILE-----------------
 *
 */

    public function checkIN($id)
    {
        try {
            $this->fileService->checkInFile($id);
            return response()->json(['message' => 'The file is booked successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

/***
 * ------------------CHECK OUT THE FILE-----------------
 */
    public function checkOUT($id)
    {
        try {
            $this->fileService->checkOutFile($id);
            return response()->json(['message' => 'The file is unbooked successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

/***
 * ------------------CHECK IN MULTI FILES-----------------
 */

    public function multiCheckIN(Request $request)
    {
        $fileIds = $request->input('fileIds', []);

        try {
            $this->fileService->multiCheckInFiles($fileIds);
            return response()->json(['message' => 'Files checked in successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

/***
 * ------------------FILE STATE-----------------
 */

    public function fileState($id) {
        try {
            $state = $this->fileService->getFileState($id);
            return response()->json(['state' => $state], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

}

//
///***
// * ------------------Get ALL Reports-----------------
// */
//public function getAllReports(){
//
//    $files= Report::all();
//
//    return response()->json(['files' => $files], 200);
//}

///***
// * ------------------Get ALL FILES-----------------
// */
//public function getAllFiles(){
//
//    $files= File::all();
//
//    return response()->json(['files' => $files], 200);
//}
//
///**
// * ------------------UPLOAD FILE WITH EXTENTIONS txt,docx,pdf,jpg,png-----------------
// */
//
//public function uploadFile(Request $request){
//    $request->validate([
//        'file' => 'required|mimes:txt,docx,pdf,jpg,png',
//    ]);
//
//    $user = auth()->user();
//
//    // Process the file
//    if ($request->file('file')->isValid()) {
//        $file = $request->file('file');
//
//        // Store the file in the storage/app/uploads directory
//        $name = $file->getClientOriginalName();
//        $path = $file->storeAs('uploads', $file->getClientOriginalName());
//
//        $file = new File();
//        $file->path = $path;
//        $file->name =$name;
//        $file->state = "0";
//        $file->user_id = $user->id ;
//        $file->save();
//
//        $date = Carbon::now();
//        $repoController = new ReportController();
//        $repoController->makeReprot($date, "Upload", $file->id, $user->name);
//
//        // $report = new Report();
//        // $report->file_id = $file->id ;
//        // $report->operation_name = "Upload" ;
//        // $report->operation_date =  $date ;
//        // $report->user_name =  '$user->name'  ;
//        // $report->save();
//
//
//        return response()->json(['message' => 'File uploaded successfully', 'path' => $path]);
//    }
//
//    return response()->json(['message' => 'Invalid file'], 400);
//}

/**
 * ------------------DOWNLOAD THE FILE-----------------
 */
//
//public function downloadFile($fileId){
//    $user = auth()->user();
//
//    $file = File::findOrFail($fileId);
//    if($file->state != $user->id){
//        return response()->json([
//            'message' => 'File is un available!',
//        ], 401);
//    }
//    $filePath = Storage::path("uploads/{$file->name}");
//
//    return response()->download($filePath, $file->name, [
//        'Content-Type' => mime_content_type($filePath),
//    ]);
//}

//
//public function deleteFile($id){
//
//    $user = auth()->user();
//
//    try {
//        DB::beginTransaction();
//
//        $file = File::find($id);
//        \Log::info('File ID: ' . $id);
//
//        if (!$file) {
//            DB::rollBack();
//            return response()->json(['message' => 'File not found'], 404);
//        }
//
//        if ($file->state != $user->id) {
//            DB::rollBack();
//            return response()->json(['message' => 'File is unavailable!'], 401);
//        }
//
//        // Delete related records in Group_file table
//        Group_file::where('file_id', $id)->delete();
//        \Log::info('delete from group' );
//
//        // Delete reports associated with the file
//        $file->reports()->delete();
//
//        // Delete file from storage
//        Storage::delete($file->path);
//
//        \Log::info('delete from storage' );
//
//        $date = now();
//        $report = new Report();
//        $report->file_id = $file->id;
//        $report->operation_name = "Delete";
//        $report->operation_date = $date;
//        $report->user_name = $user->name;
//        $report->save();
//
//        \Log::info('making report' );
//
//        // Delete file record from the database
//        $file->delete();
//
//        \Log::info('delete report' );
//
//
//        DB::commit();
//
//        return response()->json(['message' => 'File deleted successfully'], 200);
//    } catch (\Exception $e) {
//        DB::rollBack();
//        \Log::error('Exception during file deletion: ' . $e->getMessage());
//        return response()->json(['message' => 'Failed to delete file'], 500);
//    }
//}
//
///**
// * ------------------CHECK IN THE FILE-----------------
// *
// */
//
//public function checkIN($id){
//
//    $user = auth()->user();
//
//    return DB::transaction(function () use ($id, $user) {
//        // Acquire a write lock on the file record
//        $lockedFile = File::query()->where('id', $id)->lockForUpdate()->first();
//
//        if (!$lockedFile) {
//            return response()->json(['File not found'], 404);
//        }
//
//        if ($lockedFile->state != 0) {
//            return response()->json(['This file is already booked'], 401);
//        }
//
//        // Proceed with the check-in logic
//        $lockedFile->state = $user->id;
//        $lockedFile->save();
//
//        $date = Carbon::now();
//        $report = new Report();
//        $report->file_id = $lockedFile->id;
//        $report->operation_name = "Check in";
//        $report->operation_date = $date;
//        $report->user_name = $user->name;
//        $report->save();
//
//        return response()->json([
//            'The file is booked successfully!',
//        ], 200);
//    });
//}

///***
// * ------------------CHECK OUT THE FILE-----------------
// */


//public function checkOUT(Request $request, $id){
//
//    $user = auth()->user();
//
//    $file = File::query()->find($id) ;
//    if($file->state != $user->id){
//        return response()->json(['This file is already booked'], 401);
//    }
//
//    $file->state = 0 ;
//    $file->save();
//
//    $date = Carbon::now();
//    $report = new Report();
//    $report->file_id = $file->id ;
//    $report->operation_name = "Check out" ;
//    $report->operation_date =  $date ;
//    $report->user_name =  $user->name  ;
//    $report->save();
//
//    return response()->json([
//        'The file is unbooked successfully!',
//    ],200);
//}


///***
// * ------------------CHECK IN MULTI FILES-----------------
// */

//
//public function multiCheckIN(Request $request){
//    $user = auth()->user();
//
//    $fileIds = $request->input('fileIds', []);
//
//    if (empty($fileIds)) {
//        return response()->json(['message' => 'No file IDs provided'], 400);
//    }
//
//    try {
//        // Cache the file IDs and other necessary data
//        Cache::put('multiCheckInData', ['fileIds' => $fileIds, 'userId' => $user->id], now()->addMinutes(10));
//
//        DB::beginTransaction();
//
//        // Retrieve files based on the provided IDs and lock them for update
//        $files = File::whereIn('id', $fileIds)->lockForUpdate()->get();
//
//        // Loop through the files and perform check-in
//        foreach ($files as $file) {
//            if ($file->state != 0) {
//                // Rollback the transaction if any file is already booked
//                DB::rollBack();
//                Cache::forget('multiCheckInData'); // Remove cached data
//                return response()->json(['message' => 'File ' . $file->id . ' is already booked'], 401);
//            }
//
//            $file->state = $user->id;
//            $file->save();
//
//            $date = Carbon::now();
//            $report = new Report();
//            $report->file_id = $file->id;
//            $report->operation_name = "Check in";
//            $report->operation_date = $date;
//            $report->user_name = $user->name;
//            $report->save();
//        }
//
//        // Commit the transaction if all files are checked in successfully
//        DB::commit();
//
//        // Remove cached data after successful check-in
//        Cache::forget('multiCheckInData');
//
//        return response()->json(['message' => 'Files checked in successfully'], 200);
//    } catch (\Exception $e) {
//        // Handle any exception and rollback the transaction
//        DB::rollBack();
//        Cache::forget('multiCheckInData'); // Remove cached data
//        return response()->json(['message' => 'Error during check-in process'], 500);
//    }
//}
//
///***
// * ------------------FILE STATE-----------------
// */
//
//public function fileState($id){
//
//    $file = File::find($id);
//
//    if (!$file) {
//        return response()->json(['message' => 'File not found'], 404);
//    }
//
//    if ($file->state == 0) {
//        return response()->json(['state' => 'free'], 200);
//    } else {
//        $user = $file->state;
//        return response()->json(['state' => "Booked for user $user"], 200);
//    }

