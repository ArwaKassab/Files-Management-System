<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Group;
use App\Models\Group as ModelsGroup;
use App\Models\Group_user;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class Users extends Controller
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

/***
 * ------------------Get All User Groups-----------------
 */

    public function allUserGroups() {
        try {
            $userGroups = $this->userService->getAllUserGroups();
            return response()->json([$userGroups]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

//    public function allUserGroups()
//    {
//        $user = auth()->user();
//       // $userGroups = $user->groups;
//        $userGroups = $user->groups()->get();
//        if ($userGroups->isEmpty()) {
//            return response()->json(["no groups found"], 401);
//        }
//
//        return response()->json([$userGroups], 200);
//    }


/***
 * ------------------Get All User Owned Groups-----------------
 */

    public function allUserOwnedGroups() {
        try {
            $groups = $this->userService->getAllUserOwnedGroups();
            return response()->json([$groups], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
//
//    public function allUserOwnedGroups()
//    {
//        $user = auth()->user();
//        $groups = Group::where('owner', $user->id)->get();
//
//        if ($groups->isEmpty()) {
//            return response()->json(['no groups found'], 401);
//        }
//
//        return response()->json([$groups], 200);
//    }


/***
 * ------------------Get all User Files-----------------
 */
    public function allUserFiles() {
        try {
            $files = $this->userService->getAllUserFiles();
            return response()->json([$files], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }


//    public function allUserFiles()
//    {
//        $user= auth()->user();
//        $files = File::where('user_id',$user->id)->get();
//
//        if(sizeof($files)==0)
//            return response()->json(['no files found'], 401);
//        else
//            return response()->json([ $files], 200);
//    }


/***
 * ------------------Get Users Not In Group-----------------
 */

    public function getUsersNotInGroup($groupId) {
        $usersNotInGroup = $this->userService->getUsersNotInGroup($groupId);
//        return response()->json(['users_not_in_group' => $usersNotInGroup]);
        return response()->json( $usersNotInGroup);
    }

//    public function getUsersNotInGroup($groupId)
//    {
//        $usersNotInGroup = User::whereNotIn('id', function ($query) use ($groupId) {
//                $query->select('user_id')
//                    ->from('group_users')
//                    ->where('group_id', $groupId);
//            })
//            ->get();
//
//        return response()->json([
//            'users_not_in_group' => $usersNotInGroup,
//        ]);
//    }

}
