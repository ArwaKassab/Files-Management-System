<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'state',//0 if free// 1 if locked for user whos id=1 // 10 if locked for user whos id=10v ...
        'path',
        'name',
        'user_id'
    ];

    public function users()
    {
        return $this->belongsTo(User::class ,'user_id')->withTimestamps();
    }
    public function groups()
   {
    return $this->belongsToMany(Group::class ,'group_files','file_id','group_id')->withTimestamps();
   }
   public function reports()
    {
        return $this->hasMany(Report::class, 'file_id', 'id');
    }
}
