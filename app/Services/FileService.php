<?php

namespace App\Services;
use App\Http\Controllers\ReportController;
use App\Models\File;
use App\Models\Group_file;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FileService
{


/***
 * ------------------Get ALL Reports-----------------
 */
    public function getAllReports() {
        return Report::all();
    }

/***
 * ------------------Get ALL FILES-----------------
 */
    public function getAllFiles() {
        return File::all();
    }



/**
 * ------------------UPLOAD FILE WITH EXTENTIONS txt,docx,pdf,jpg,png-----------------
 */


    public function uploadFile( $uploadedFile) {
        $user = Auth::user();

        if (!$uploadedFile->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        $name = $uploadedFile->getClientOriginalName();
        $path = $uploadedFile->storeAs('uploads', $name);

        $file = new File();
        $file->path = $path;
        $file->name = $name;
        $file->state = "0";
        $file->user_id = $user->id;
        $file->save();

        $date = Carbon::now();
        $repoController = new ReportController();
        $repoController->makeReprot($date, "Upload", $file->id, $user->name);

        // $report = new Report();
        // $report->file_id = $file->id ;
        // $report->operation_name = "Upload" ;
        // $report->operation_date =  $date ;
        // $report->user_name =  '$user->name'  ;
        // $report->save();

        return $file;
    }

/**
 * ------------------DOWNLOAD THE FILE-----------------
 */

    public function downloadFile($fileId) {
        $user = Auth::user();

        $file = File::findOrFail($fileId);
        if ($file->state != $user->id) {
            throw new \Exception('File is unavailable', 401);
        }

        $filePath = Storage::path("uploads/{$file->name}");

        return $filePath;
    }

/**
 * ------------------Delete THE FILE-----------------
 */



    public function deleteFile($fileId) {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $file = File::find($fileId);

            if (!$file) {
                throw new \Exception('File not found', 404);
            }

            if ($file->state != $user->id) {
                throw new \Exception('File is unavailable!', 401);
            }

            Group_file::where('file_id', $fileId)->delete();
            $file->reports()->delete();
            Storage::delete($file->path);

            $date = now();
            $report = new Report();
            $report->file_id = $file->id;
            $report->operation_name = "Delete";
            $report->operation_date = $date;
            $report->user_name = $user->name;
            $report->save();
            $file->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
//
//    public function deleteFile($id){
//
//        $user = auth()->user();
//
//        try {
//            DB::beginTransaction();
//
//            $file = File::find($id);
//            \Log::info('File ID: ' . $id);
//
//            if (!$file) {
//                DB::rollBack();
//                return response()->json(['message' => 'File not found'], 404);
//            }
//
//            if ($file->state != $user->id) {
//                DB::rollBack();
//                return response()->json(['message' => 'File is unavailable!'], 401);
//            }
//
//            // Delete related records in Group_file table
//            Group_file::where('file_id', $id)->delete();
//            \Log::info('delete from group' );
//
//            // Delete reports associated with the file
//            $file->reports()->delete();
//
//            // Delete file from storage
//            Storage::delete($file->path);
//
//            \Log::info('delete from storage' );
//
//            $date = now();
//            $report = new Report();
//            $report->file_id = $file->id;
//            $report->operation_name = "Delete";
//            $report->operation_date = $date;
//            $report->user_name = $user->name;
//            $report->save();
//
//            \Log::info('making report' );
//
//            // Delete file record from the database
//            $file->delete();
//
//            \Log::info('delete report' );
//
//
//            DB::commit();
//
//            return response()->json(['message' => 'File deleted successfully'], 200);
//        } catch (\Exception $e) {
//            DB::rollBack();
//            \Log::error('Exception during file deletion: ' . $e->getMessage());
//            return response()->json(['message' => 'Failed to delete file'], 500);
//        }
//    }

/**
 * ------------------CHECK IN THE FILE-----------------
 *
 */

    public function checkInFile($fileId) {
        $user = Auth::user();

        return DB::transaction(function () use ($fileId, $user) {
            $lockedFile = File::query()->where('id', $fileId)->lockForUpdate()->first();

            if (!$lockedFile) {
                throw new \Exception('File not found', 404);
            }

            if ($lockedFile->state != 0) {
                throw new \Exception('This file is already booked', 401);
            }

            $lockedFile->state = $user->id;
            $lockedFile->save();

            $date = Carbon::now();
            $report = new Report();
            $report->file_id = $lockedFile->id;
            $report->operation_name = "Check in";
            $report->operation_date = $date;
            $report->user_name = $user->name;
            $report->save();

            return $lockedFile;
        });
    }




/***
 * ------------------CHECK OUT THE FILE-----------------
 */

    public function checkOutFile($fileId) {
        $user = Auth::user();

        $file = File::findOrFail($fileId);
        if ($file->state != $user->id) {
            throw new \Exception('This file is already booked', 401);
        }

        $file->state = 0;
        $file->save();

        $date = Carbon::now();
        $report = new Report();
        $report->file_id = $file->id ;
        $report->operation_name = "Check out" ;
        $report->operation_date =  $date ;
        $report->user_name =  $user->name  ;
        $report->save();


        return $file;
    }


/***
 * ------------------CHECK IN MULTI FILES-----------------
 */

    public function multiCheckInFiles($fileIds) {
        $user = Auth::user();

        if (empty($fileIds)) {
            throw new \Exception('No file IDs provided', 400);
        }

        Cache::put('multiCheckInData', ['fileIds' => $fileIds, 'userId' => $user->id], now()->addMinutes(10));

        DB::beginTransaction();

        try {
            $files = File::whereIn('id', $fileIds)->lockForUpdate()->get();

            foreach ($files as $file) {
                if ($file->state != 0) {
                    throw new \Exception('File ' . $file->id . ' is already booked', 401);
                }

                $file->state = $user->id;
                $file->save();

                $report = new Report();
                $report->file_id = $file->id;
                $report->operation_name = "Check in";
                $report->operation_date = Carbon::now();
                $report->user_name = $user->name;
                $report->save();
            }

            DB::commit();
            Cache::forget('multiCheckInData');

            return $files;
        } catch (\Exception $e) {
            DB::rollBack();
            Cache::forget('multiCheckInData');
            throw $e;
        }
    }



/***
 * ------------------FILE STATE-----------------
 */

    public function getFileState($fileId) {
        $file = File::find($fileId);

        if (!$file) {
            throw new \Exception('File not found', 404);
        }

        if ($file->state == 0) {
            return 'free';
        } else {
            return "Booked for user {$file->state}";
        }
    }

}
