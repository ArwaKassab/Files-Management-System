<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
       'name',
       'owner'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class ,'group_users','group_id','user_id')->withTimestamps();
    }

    public function files()
    {
     return $this->belongsToMany(File::class ,'group_files','group_id' ,'file_id' )->withTimestamps();
    }
}
