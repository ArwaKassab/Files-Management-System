<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, File $file): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, File $file): bool
    {
        //
    }


    /**
     * Determine if the given file can be viewed by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\File $file
     * @return bool
     * use it in routes like: ->middlware('can:view,file') and use model binding
     */
    public function delete(User $user, $fileId)
    {
        $user = auth()->user();
        $file = File::find($fileId);


        if ($file) {

            return $user->id == $file->user_id;
        }


        return false;
    }




    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, File $file): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, File $file): bool
    {
        //
    }
}
