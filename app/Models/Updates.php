<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Updates extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'app_updates';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['owner_id','version','user_id', 'file_path'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at']; 
}
