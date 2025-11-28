<?php

namespace App\Models;

use App\Policies\TaskPolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    //
    use HasFactory ,SoftDeletes ;
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $policies =[
        Task::class => TaskPolicy::class
    ];
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

}
