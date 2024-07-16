<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Group as ModelsGroup;
use App\Models\Group_file;
use App\Models\Group_user;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


use App\Services\GroupService;

class Groups extends Controller {
    protected $groupService;

    public function __construct(GroupService $groupService) {
        $this->groupService = $groupService;
    }

/***
 * ------------------Get ALL Group Files-----------------
 */
    public function allGroupFiles($id) {
        $files = $this->groupService->allGroupFiles($id);

        if ($files === null) {
            return response()->json(["no files"], 401);
        }

        return response()->json($files);
    }


/***
 * ------------------Get ALLGroup Users-----------------
 */
    public function allGroupUsers($id) {
        $users = $this->groupService->allGroupUsers($id);

        if ($users === null || $users->isEmpty()) {
            return response()->json(["no users"], 200);
        }
        return response()->json(['users_in_group' => $users]);
//        return response()->json($users);
    }

/***
 * ------------------create Group-----------------
 */


    public function createGroup(Request $request) {
        $result = $this->groupService->createGroup($request->group_name);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 401);
        }

        return response()->json(['message' => $result['success']], 200);
    }

/***
 * ------------------Delete Group-----------------
 */
    public function deleteGroup($id) {
        $result = $this->groupService->deleteGroup($id);

        if (isset($result['error'])) {
            $status = 400;
            if ($result['error'] == 'Group not found') {
                $status = 404;
            } elseif ($result['error'] == 'Delete failed because some files are being used') {
                $status = 401;
            }
            return response()->json(['message' => $result['error']], $status);
        }

        return response()->json(['message' => $result['success']], 200);
    }

/***
 * ------------------Add File To Group-----------------
 */

    public function addFileToGroup(Request $request, $id) {
        $result = $this->groupService->addFileToGroup($request->file_id, $id);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['status']);
        }

        return response()->json(['message' => $result['success']], 200);
    }

/***
 * ------------------Delete File From Group-----------------
 */
    public function deleteFileFromGroup(Request $request, $id) {
        $result = $this->groupService->deleteFileFromGroup($request->file_id, $id);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['status']);
        }

        return response()->json(['message' => $result['success']], 200);
    }

/***
 * ------------------Add Users To Group-----------------
 */

    public function addUsersToGroup(Request $request,$id) {
        $result = $this->groupService->addUsersToGroup($id, $request->user_ids);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['status']);
        }

        return response()->json(['message' => $result['success'], 'added_users' => $result['added_users']], 200);
    }

/***
 * ------------------Delete Users From Group-----------------
 */

    public function deleteUsersFromGroup(Request $request,$id) {
        $result = $this->groupService->deleteUsersFromGroup($id, $request->user_ids);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['status']);
        }

        return response()->json(['message' => $result['success']], 200);
    }

/***
 * ------------------Get My Check In Files-----------------
 */

    public function getMyCheckInFiles(Request $request) {
        $result = $this->groupService->getMyCheckInFiles($request->groupId);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['status']);
        }

        return response()->json($result);
    }

}


//
//
//class Groups extends Controller
//{
//
//
//    /***
//     * ------------------Get ALL Group Files-----------------
//     */
//
//    public function allGroupFiles($id)
//    {
//        $filesIds = Cache::get($id);
//        if($filesIds==null)
//            return response()->json([
//                "no files"
//            ],401);
//
//        $files = File::whereIn('id', $filesIds)->get();
//        return response()->json([
//            $files
//        ]);
//
//    }
//
//    /***
//     * ------------------Get ALLGroup Users-----------------
//     */
//    public function allGroupUsers($id)
//    {
//        $group = ModelsGroup::find($id);
//        $users = $group->users()->get();
//
//        if ($users->isEmpty()) {
//            return response()->json(["no users"], 200);
//        }
//
//        return response()->json([$users]);
//return response()->json(['users_in_group' => $users]);
//
//    }
//
//
//
//    /***
//     * ------------------create Group-----------------
//     */
//    public function createGroup(Request $request)
//    {
//        $user= auth()->user();
//        if(!$request->group_name) {
//            return response()->json(['No group name'], 401);
//        }
//
//            $group = ModelsGroup::firstOrCreate(
//                ['name' => $request->group_name],
//                ['owner' => $user->id]
//            );
//
//            if (!$group->wasRecentlyCreated) {
//                return response()->json(['The group is already existed'], 401);
//            }
//
//        //////////////////////////
//
//        $joingroup = new Group_user();
//        $joingroup->user_id =  $user->id ;
//        $joingroup->group_id =  $group->id ;
//        $joingroup->save();
//
//        return response()->json([
//            'message' => 'The group is created successfully!',
//        ],200);
//    }
//
//
//    /***
//     * ------------------Delete Group-----------------
//     */
//    public function deleteGroup($id)
//    {
//        $group = ModelsGroup::find($id);
//        if (!$group) {
//            return response()->json(['message' => 'Group not found'], 404);
//        }
//
//        $filesInGroup = $group->files;
//        foreach ($filesInGroup as $file) {
//            if ($file->state != 0) {
//                return response()->json(['message' => 'Delete failed because some files are being used'], 401);
//            }
//        }
//
//        DB::beginTransaction();
//
//        try {
//
//    //        $filesInGroup = Group_file::query() ;
//    //        $filesInGroup->where('group_id',$id)->delete();
//    //        $usersInGroup = Group_user::query() ;
//    //        $usersInGroup->where('group_id',$id)->delete();
//
//            Group_file::where('group_id', $id)->delete();
//            Group_user::where('group_id', $id)->delete();
//
//            $group->delete();
//            DB::commit();
//            return response()->json(['message' => 'Group deleted successfully']);
//        } catch (\Exception $e) {
//            DB::rollBack();
//            return response()->json(['message' => 'Failed to delete group relations']);
//        }
//
//
//
//}
//
//    /***
//     * ------------------Add File To Group-----------------
//     */
//    public function addFileToGroup(Request $request, $id)
//    {
//        $group = ModelsGroup::query()->where('name', $request->group_name)->first();
//
//        if (!$group) {
//            return response()->json(['Group not found'], 404);
//        }
//
//        if ($group->files()->where('file_id', $id)->exists()) {
//            return response()->json(['The file is already in this group'], 401);
//        }
//
//        $group_file = new Group_file();
//        $group_file->group_id = $group->id;
//        $group_file->file_id = $id;
//        $group_file->save();
//
//
//
//        $groupfiles = Cache::get($group->id);
//        $new_array = array();
//        if($groupfiles!=null)
//        {
//            foreach($groupfiles as $groupfile)
//            {
//                array_push($new_array,$groupfile);
//            }
//            Cache::delete($group->id);
//        }
//
//        array_push($new_array,$id);
//
//        Cache::forever($group->id, $new_array);
//
//
//        return response()->json([
//            'message' => 'The file is added successfully!',
//        ], 200);
//    }
//
//    /***
//     * ------------------Delete File From Group-----------------
//     */
//
//    public function deleteFileFromGroup(Request $request,$id)
//    {
//
//        $group = ModelsGroup::query()->where('name', $request->group_name)->first();
//
//        if (!$group) {
//            return response()->json(['message' => 'Group not found'], 404);
//        }
//
//        $file = File::find($id);
//        if ( $file->state != 0) {
//            return response()->json(['message' => 'Cannot remove this file because it is being used'], 401);
//        }
//
//        Group_file::where('group_id', $group->id)->where('file_id', $id)->delete();
//
//
//
//        $groupfiles = Cache::get($group->id);
//        Cache::delete($group->id);
//        $index = array_search($id, $groupfiles);
//        unset($groupfiles[$index]);
//        Cache::forever($group->id,$groupfiles);
//
//        return response()->json([
//            'message' => 'The file is removed successfully!',
//        ],200);
//
//    }
//
//    /***
//     * ------------------Add Users To Group-----------------
//     */
//
//    public function addUsersToGroup(Request $request)
//    {
//        $userIds = $request->user_ids;
//        $currentUser = auth()->user();
//
//        if (empty($userIds)) {
//            return response()->json(['No user ids provided'], 401);
//        }
//
//        if (!$request->group_name) {
//            return response()->json(['No group name'], 401);
//        }

//
//        $group = ModelsGroup::query()->where('name', $request->group_name)->first();
//            $group = ModelsGroup::find($request->group_id);
//
//        if (!$group) {
//            return response()->json(['message' => 'Group not found'], 404);
//        }
//
//        if ($group->owner != $currentUser->id) {
//            return response()->json(['You do not have access to add users to this group'], 401);
//        }
//
//        $existingUsers = Group_user::where('group_id', $group->id)->whereIn('user_id', $userIds)->pluck('user_id')->toArray();
//
//        $usersToAdd = array_diff($userIds, $existingUsers);
//
//        if (empty($usersToAdd)) {
//            return response()->json(['Users are already in this group'], 401);
//        }
//
//        DB::beginTransaction();
//
//        try {
//            $groupUsers = [];
//            foreach ($usersToAdd as $userId) {
//                $groupUser = new Group_user();
//                $groupUser->group_id = $group->id;
//                $groupUser->user_id = $userId;
//                $groupUser->save();
//                $groupUsers[] = $groupUser;
//            }
//
//            // اذا وصلنا الى هنا دون أي استثناء، نقوم بتنفيذ العملية
//            DB::commit();
//
//            return response()->json([
//                'message' => 'Users added successfully!',
//                'added_users' => $groupUsers,
//            ], 200);
//        } catch (\Exception $e) {
//            // في حالة حدوث أي خطأ، نقوم بإلغاء المعاملة
//            DB::rollback();
//            return response()->json(['message' => 'Failed to add users to group'], 500);
//        }
//    }
//
//    /***
//     * ------------------Delete Users From Group-----------------
//     */
//
//    public function deleteUsersFromGroup(Request $request)
//    {
//        $user = auth()->user();
//        $group = ModelsGroup::query()->where('name', $request->group_name)->first();
//
//        if (!$group || $group->owner !== $user->id) {
//            return response()->json(['You dont have access to delete from this group'], 401);
//        }
//
//        DB::beginTransaction();
//
//        try {
//            foreach ($request->user_ids as $id) {
//                if ($user->id === $id) {
//                    return response()->json(['wrong operation'], 401);
//                }
//
//                $groupUser = Group_user::where('group_id', $group->id)->where('user_id', $id)->first();
//
//                if (!$groupUser) {
//                    return response()->json(['The user is not in this group'], 401);
//                }
//
//                $userFilesInGroup = File::query()->where('state', $id)->get();
//
//                foreach ($userFilesInGroup as $file) {
//                    $groupFile = Group_file::where('group_id', $group->id)->where('file_id', $file->id)->first();
//                    if ($groupFile) {
//                        return response()->json(['This user is using some files in this group'], 401);
//                    }
//                }
//
//                $groupUser->delete();
//            }
//
//            DB::commit();
//        } catch (\Exception $e) {
//            DB::rollback();
//            // something went wrong
//        }
//
//        return response()->json(['message' => 'The users are removed successfully!'], 200);
//    }
//
//    /***
//     * ------------------Get My Check In Files-----------------
//     */
//
//    public function getMyCheckInFiles(Request $request)
//    {
//        $user = auth()->user();
//        $groupId = $request->groupId;
//
//        if (!$groupId) {
//            return response()->json(['message' => 'Group ID is missing'], 400);
//        }
//
//        $userFilesInGroup = File::where('state', $user->id)
//            ->whereHas('groups', function ($query) use ($groupId) {
//                $query->where('group_id', $groupId);
//            })
//            ->get();
//
//        return response()->json([
//            'user_files_in_group' => $userFilesInGroup,
//        ]);
//    }
//
//
//
//
//
//
//
//}
