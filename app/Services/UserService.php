<?php

namespace App\Services;

use App\Models\File;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserService
{

/***
 * ------------------Get All User Groups-----------------
 */
//    public function getAllUserGroups() {
//        $user = Auth::user();
//        $userGroups = $user->groups()->get();
//
//        if ($userGroups->isEmpty()) {
//            throw new \Exception('No groups found', 401);
//        }
//
//        return $userGroups;
//    }
    public function getAllUserGroups() {
        $user = Auth::user();

        $userGroups = $user->groups()->where('owner', '!=', $user->id)->get();

        if ($userGroups->isEmpty()) {
            throw new \Exception('No groups found', 401);
        }

        return $userGroups;
    }


/***
 * ------------------All User Owned Groups-----------------
 */

    public function getAllUserOwnedGroups() {
        $user = Auth::user();
        $groups = Group::where('owner', $user->id)->get();

        if ($groups->isEmpty()) {
            throw new \Exception('No groups found', 401);
        }

        return $groups;
    }
/***
 * ------------------Get all User Files-----------------
 */

    public function getAllUserFiles() {
        $user = Auth::user();
        $files = File::where('user_id', $user->id)->get();

        if ($files->isEmpty()) {
            throw new \Exception('No files found', 401);
        }

        return $files;
    }

/***
 * ------------------Get Users Not In Group-----------------
 */

    public function getUsersNotInGroup($groupId) {
        $usersNotInGroup = User::whereNotIn('id', function ($query) use ($groupId) {
            $query->select('user_id')
                ->from('group_users')
                ->where('group_id', $groupId);
        })
            ->get();

        return $usersNotInGroup;
    }


}
