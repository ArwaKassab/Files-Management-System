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

class FilesController extends Controller
{

    /***
     * ------------------Get ALL FILES-----------------
     */
    public function getAllFiles(){

        $files= File::all();

        return response()->json(['files' => $files], 200);
    }

    /**
     * ------------------UPLOAD FILE WITH EXTENTIONS txt,docx,pdf,jpg,png-----------------
     */

    public function uploadFile(Request $request){
        $request->validate([
            'file' => 'required|mimes:txt,docx,pdf,jpg,png',
        ]);

        $user = auth()->user();

        // Process the file
        if ($request->file('file')->isValid()) {
            $file = $request->file('file');

            // Store the file in the storage/app/uploads directory
            $name = $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $file->getClientOriginalName());

            $file = new File();
            $file->path = $path;
            $file->name =$name;
            $file->state = "0";
            $file->user_id = $user->id ;
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


            return response()->json(['message' => 'File uploaded successfully', 'path' => $path]);
        }

        return response()->json(['message' => 'Invalid file'], 400);
    }

    /**
     * ------------------DOWNLOAD THE FILE-----------------
     */

    public function downloadFile($fileId){

        $file = File::findOrFail($fileId);
        if($file->state != 0){
            return response()->json([
                'message' => 'File is un available!',
            ], 401);
        }
        $filePath = Storage::path("uploads/{$file->name}");
    
        return response()->download($filePath, $file->name, [
            'Content-Type' => mime_content_type($filePath),
        ]);
    }

    
    public function deleteFile($id){

        $file =  File::find($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        if($file->state == "0"){
             // Delete related records in Group_file table

                $relatedFiles = Group_file::query()->where('file_id',$id)->get();
                foreach ($relatedFiles as $relatedFile) {
                    $relatedFile->delete();
                }

                 // Delete file from storage
                Storage::delete($file->path);

                $date = Carbon::now();
                $user = 1;

                $report = new Report();
                $report->file_id = $file->id ;
                $report->operation_name = "Delete" ;
                $report->operation_date =  $date ;
                $report->user_name =  'user-name' ;
                $report->save();
        
                // Delete file record from the database
                $file->delete();

                return response()->json(['message' => 'File deleted successfully'], 200);
            } 

            else {
                return response()->json(['message' => 'File is in use'], 401);
            }
        }

        /**
         * ------------------CHECK IN THE FILE-----------------
         * 
         */

        public function checkIN($id){

            $user = auth()->user();

            $file = File::query()->find($id) ;
            if($file->state != 0){
                return response()->json(['This file is already booked'], 401);
            }
    
            $file->state = $user->id ;
            $file->save();
    
            $date = Carbon::now();
            $report = new Report();
            $report->file_id = $file->id ;
            $report->operation_name = "Check in" ;
            $report->operation_date =  $date ;
            $report->user_name =  $user->name  ;
            $report->save();
    
            return response()->json([
                'The file is booked successfully!',
            ],200);
        }

        /***
         * ------------------CHECK OUT THE FILE-----------------
         */

        public function checkOUT(Request $request, $id){

            $user = auth()->user();

            $file = File::query()->find($id) ;
            if($file->state != $user->id){
                return response()->json(['This file is already booked'], 401);
            }
    
            $file->state = 0 ;
            $file->save();
    
            $date = Carbon::now();
            $report = new Report();
            $report->file_id = $file->id ;
            $report->operation_name = "Check in" ;
            $report->operation_date =  $date ;
            $report->user_name =  $user->name  ;
            $report->save();
    
            return response()->json([
                'The file is unbooked successfully!',
            ],200);
        }
        

        /***
         * ------------------CHECK IN MULT FILES-----------------
         */

        public function multiCheckIN(Request $request){
            $user = auth()->user();

            $fileIds = $request->input('fileIds', []); // to get the ids of the files as array
            //$fileIdsArray = explode(',', $fileIds);

            //\Log::info('File IDs from request: ' . json_encode($fileIds));

            if (empty($fileIds)) {
                return response()->json(['message' => 'No file IDs provided'], 400);
            }

            try {
                
                DB::beginTransaction();
        
                // Retrieve files based on the provided IDs and lock them for update
                $files = File::whereIn('id', $fileIds)->lockForUpdate()->get();
        
                // Loop through the files and perform check-in
                foreach ($files as $file) {
                    if ($file->state != 0) {
                        // Rollback the transaction if any file is already booked
                        DB::rollBack();
                        return response()->json(['message' => 'File ' . $file->id . ' is already booked'], 401);
                    }
        
                    $file->state = $user->id;
                    $file->save();
        
                    $date = Carbon::now();
                    $report = new Report();
                    $report->file_id = $file->id;
                    $report->operation_name = "Check in";
                    $report->operation_date = $date;
                    $report->user_name = $user->name;
                    $report->save();
                }
        
                // Commit the transaction if all files are checked in successfully
                DB::commit();
        
                return response()->json(['message' => 'Files checked in successfully'], 200);
            } catch (\Exception $e) {
                // Handle any exception and rollback the transaction
                DB::rollBack();
                return response()->json(['message' => 'Error during check-in process'], 500);
            }
        }

        /***
         * ------------------FILE STATE-----------------
         */

        public function fileState($id){
            
            $file = File::find($id);

            if (!$file) {
                return response()->json(['message' => 'File not found'], 404);
            }
        
            if ($file->state == 0) {
                return response()->json(['state' => 'free'], 200);
            } else {
                $user = $file->state;
                return response()->json(['state' => "Booked for user $user"], 200);
            }
        }

//         public function destroy($id){
//     $file = File::find($id);

//     if (!$file) {
//         return response()->json(['message' => 'File not found'], 404);
//     }

//     if ($file->state == "0") {
//         try {
//             DB::beginTransaction();
           

//             // Delete related records in Group_file table
//             Group_file::where('file_id', $id)->delete();

//             // Delete file from storage
//             Storage::delete($file->path);

//             $date = now();
//             $user = 1;

//             // Create a report record 
//             // $repoController = app(ReportController::class);
//             // $repoController->makeReport($date, "Delete", $file->id, 'user-name');

//             $report = new Report();
//             $report->file_id = $file->id ;
//             $report->operation_name = "Delete" ;
//             $report->operation_date =  $date ;
//             $report->user_name =  'user-name' ;
//             $report->save();

//               // Detach related records in reports table
//               $file->reports()->update(['file_id' => 0]);

//             // Delete file record from the database
//             $file->delete();

//             DB::commit();

//             return response()->json(['message' => 'File deleted successfully'], 200);
//         } catch (\Exception $e) {
//             DB::rollback();
//             \Log::error('Failed to delete file: ' . $e->getMessage());
//             return response()->json(['message' => 'Failed to delete file'], 500);
//         }
//     } else {
//         return response()->json(['message' => 'File is in use'], 401);
//     }
// }




}
