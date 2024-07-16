<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use App\Models\Group as ModelsGroup;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class GroupPolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether the user can delete the group.
     */
    public function deleteGroup(User $user, ModelsGroup $group): Response
    {
        return $user->id === $group->owner_id
            ? Response::allow()
            : Response::deny('You are not the owner of this group.');
    }


    /**
     * Determine whether the user can add a file to the group.
     */
    public function addFileToGroup(User $user, ModelsGroup $group): bool
    {
        // تحقق مما إذا كان المستخدم عضوًا في المجموعة
        return $group->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete a file from the group.
     */
        public function deleteFileFromGroup(Request $request, ModelsGroup $group): Response
    {

        // استخراج معرف الملف من الطلب
        $file = $request->file_id;

        $user = Auth::user(); // الحصول على بيانات المستخدم المسجل دخول

        $group = ModelsGroup::where('id', $group)->first();
        $file = File::where('id', $file)->first();
        // تحقق من وجود المجموعة والملف
        if (!$group || !$file) {
            return Response::deny('Group or file not found', 404);
        }

        // تحقق مما إذا كان الملف جزءًا من المجموعة
        if (!$group->files->contains($file)) {
            return Response::deny('File does not belong to the group', 403);
        }

        // التحقق من حالة الملف
        if ($file->state != 0) {
            return Response::deny('Cannot remove this file because it is being used', 401);
        }

        // التحقق من صلاحية المستخدم (مالك المجموعة أو مالك الملف)
        if ($user->id === $group->owner_id || $user->id === $file->user_id) {
            return Response::allow();
        } else {
            return Response::deny('You do not have permission to delete this file', 403);
        }
    }

    /**
     * Determine whether the user can add users to the group.
     */
    public function addUsersToGroup(User $user, ModelsGroup $group): bool
    {
        return $user->id === $group->owner;
    }

    /**
     * Determine whether the user can delete users from the group.
     */
    public function deleteUsersFromGroup(User $user, ModelsGroup $group): bool
    {
        return $user->id === $group->owner;
    }

    /**
     * Determine whether the user can view the group files.
     */
    public function viewGroupFiles(User $user, ModelsGroup $group): bool
    {
        return $group->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can view the group users.
     */
    public function viewGroupUsers(User $user, ModelsGroup $group): bool
    {
        return $group->users()->where('user_id', $user->id)->exists();
    }

    // أضف المزيد من التوابع حسب الحاجة...
}
