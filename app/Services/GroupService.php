<?php

namespace App\Services;
use App\Models\File;
use App\Models\Group;
use App\Models\Group as ModelsGroup;
use App\Models\Group_file;
use App\Models\Group_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GroupService
{


/***
 * ------------------Get ALL Group Files-----------------
 */

    public function allGroupFiles($groupId) {
        $filesIds = Cache::get($groupId);
        if ($filesIds == null) {
            // يمكن إرجاع null أو رمي استثناء، اعتمادًا على كيفية التعامل مع الأخطاء في تطبيقك
            return null;
        }

        return File::whereIn('id', $filesIds)->get();
    }

/***
 * ------------------Get ALLGroup Users-----------------
 */
    public function allGroupUsers($groupId) {
        $group = ModelsGroup::find($groupId);
        if (!$group) {
            return null; // أو يمكنك رمي استثناء
        }

        return $group->users()->get();
    }



/***
 * ------------------create Group-----------------DONE
 */
    public function createGroup($groupName) {
        $user = Auth::user();
        if (!$groupName) {
            return ['error' => 'No group name'];
        }

        $group = ModelsGroup::firstOrCreate(
            ['name' => $groupName],
            ['owner' => $user->id]
        );

        if (!$group->wasRecentlyCreated) {
            return ['error' => 'The group is already existed'];
        }

        $joingroup = new Group_user();
        $joingroup->user_id = $user->id;
        $joingroup->group_id = $group->id;
        $joingroup->save();

        return ['success' => 'The group is created successfully!'];
    }


/***
 * ------------------Delete Group-----------------DONE
 */
    public function deleteGroup($groupId) {
        $group = ModelsGroup::find($groupId);
        if (!$group) {
            return ['error' => 'Group not found'];
        }

        foreach ($group->files as $file) {
            if ($file->state != 0) {
                return ['error' => 'Delete failed because some files are being used'];
            }
        }

        DB::beginTransaction();

        try {
            Group_file::where('group_id', $groupId)->delete();
            Group_user::where('group_id', $groupId)->delete();

            $group->delete();
            DB::commit();
            return ['success' => 'Group deleted successfully'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['error' => 'Failed to delete group relations'];
        }
    }

/***
 * ------------------Add File To Group-----------------
 *
 *
 */

    public function addFileToGroup($fileId,$groupId) {
        $group = ModelsGroup::where('id', $groupId)->first();

        if (!$group) {
            return ['error' => 'Group not found', 'status' => 404];
        }

        if ($group->files()->where('file_id', $fileId)->exists()) {
            return ['error' => 'The file is already in this group', 'status' => 401];
        }

        $group_file = new Group_file();
        $group_file->group_id = $group->id;
        $group_file->file_id = $fileId;
        $group_file->save();

        // Update cache
        $groupFiles = Cache::get($group->id, []);
        $groupFiles[] = $fileId;
        Cache::forever($group->id, $groupFiles);




        return ['success' => 'The file is added successfully!'];
    }


/***
 * ------------------Delete File From Group-----------------
 */

//
//    public function deleteFileFromGroup($groupName, $fileId) {
//        $user = Auth::user();
//        $group = ModelsGroup::where('name', $groupName)->first();
//
//        if (!$group) {
//            return ['error' => 'Group not found', 'status' => 404];
//        }
//
//        $file = File::find($fileId);
//        if (!$file) {
//            return ['error' => 'File not found', 'status' => 404];
//        }
//
//        if ($file->state != 0) {
//            return ['error' => 'Cannot remove this file because it is being used', 'status' => 401];
//        }
//
//        $isOwner = $group->owner == $user->id;
//        $isUserFile = $file->user_id == $user->id;
//
//        if ($isOwner || $isUserFile) {
//            Group_file::where('group_id', $group->id)->where('file_id', $fileId)->delete();
//
//            // Update cache
//            $groupFiles = Cache::get($group->id, []);
//            if (($key = array_search($fileId, $groupFiles)) !== false) {
//                unset($groupFiles[$key]);
//            }
//            Cache::forever($group->id, $groupFiles);
//
//            return ['success' => 'The file is removed successfully!'];
//        } else {
//            return ['error' => 'You do not have permission to delete this file', 'status' => 403];
//        }
//    }
//


    public function deleteFileFromGroup($file_id,$group ) {
        $user = Auth::user();
        $group = ModelsGroup::where('id', $group)->first();
//            // Update cache
//            Group_file::where('group_id', $group)->where('file_id', $file_id)->delete();
////            $groupFiles = Cache::get($group->id, []);
////            if (($key = array_search($file_id, $groupFiles)) !== false) {
////                unset($groupFiles[$key]);
////            }
////            Cache::forever($group->id, $groupFiles);
//
//            return ['success' => 'The file is removed successfully!'];

//        $file = File::find($file_id);
//        $group = Group::find($group);
//        if ( $file->state != 0) {
//            return response()->json(['message' => 'Cannot remove this file because it is being used'], 401);
//        }



        $groupfiles = Cache::get($group->id);
        Cache::delete($group->id);
        $index = array_search($group, $groupfiles);
        unset($groupfiles[$index]);
        Cache::forever($group->id,$groupfiles);
        Group_file::where('group_id', $group->id)->where('file_id', $file_id)->delete();
        return ['success' => 'The file is removed successfully'];


    }



    /***
 * ------------------Add Users To Group-----------------
 */

    public function addUsersToGroup($group_id, $userIds) {
        $currentUser = Auth::user();
        $group = ModelsGroup::find($group_id);

        if (!$group) {
            return ['error' => 'Group not found', 'status' => 404];
        }

        if ($group->owner != $currentUser->id) {
            return ['error' => 'You do not have access to add users to this group', 'status' => 401];
        }

        $existingUsers = Group_user::where('group_id', $group->id)->whereIn('user_id', $userIds)->pluck('user_id')->toArray();
        $usersToAdd = array_diff($userIds, $existingUsers);

        if (empty($usersToAdd)) {
            return ['error' => 'Users are already in this group', 'status' => 401];
        }

        DB::beginTransaction();

        try {
            foreach ($usersToAdd as $userId) {
                $groupUser = new Group_user();
                $groupUser->group_id = $group->id;
                $groupUser->user_id = $userId;
                $groupUser->save();
            }

            DB::commit();
            return ['success' => 'Users added successfully!', 'added_users' => $usersToAdd];
        } catch (\Exception $e) {
            DB::rollback();
            return ['error' => 'Failed to add users to group', 'status' => 500];
        }
    }

/***
 * ------------------Delete Users From Group-----------------
 */

    public function deleteUsersFromGroup($groupId, $userIds) {
        $currentUser = Auth::user();
        $group = ModelsGroup::where('id', $groupId)->first();

//        if (!$group || $group->owner !== $currentUser->id) {
//            return ['error' => 'You do not have access to delete from this group', 'status' => 401];
//        }

        DB::beginTransaction();

        try {
            foreach ($userIds as $id) {
                if ($currentUser->id === $id) {
                    return ['error' => 'Wrong operation', 'status' => 401];
                }

                $groupUser = Group_user::where('group_id', $group->id)->where('user_id', $id)->first();
                if (!$groupUser) {
                    return ['error' => 'this user not in the group','status' => 401];
                    continue; // Skip if the user is not in the group
                }

                // Check if the user is using any files in the group
                $userFilesInGroup = File::where('state', $id)->get();
                foreach ($userFilesInGroup as $file) {
                    if (Group_file::where('group_id', $group->id)->where('file_id', $file->id)->exists()) {
                        return ['error' => 'This user is using some files in this group', 'status' => 401];
                    }
                }

                $groupUser->delete();
            }

            DB::commit();
            return ['success' => 'The users are removed successfully!'];
        } catch (\Exception $e) {
            DB::rollback();
            return ['error' => 'Failed to delete users from group', 'status' => 500];
        }
    }

/***
 * ------------------Get My Check In Files-----------------
 */

    public function getMyCheckInFiles($groupId) {
        $user = Auth::user();

        if (!$groupId) {
            return ['error' => 'Group ID is missing', 'status' => 400];
        }

        $userFilesInGroup = File::where('state', $user->id)
            ->whereHas('groups', function ($query) use ($groupId) {
                $query->where('group_id', $groupId);
            })
            ->get();

        return ['user_files_in_group' => $userFilesInGroup];
    }







}
