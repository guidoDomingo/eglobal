<?php

namespace App\Models;

use Cartalyst\Sentinel\Roles\EloquentRole;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends EloquentRole
{
    use SoftDeletes;
    /**
     * The connection name for the model
     * @var string
     */
    protected $connection = 'eglobalt_auth';

    /**
     * {@inheritDoc}
     */
    protected $fillable = ['name', 'description', 'slug', 'permissions'];

    /**
     * Kill all the Users session assigned to this role
     */
    public function killUsersSession()
    {
        foreach ($this->users as $user) {
            if (!($user->persistences->isEmpty())) {
                foreach ($user->persistences as $persistence) {
                    $persistence->delete();
                }
            }
        }
    }

    /**
     * Filter by description
     */
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('name', 'ILIKE', "%$description%");

        }
    }
    public static function filterAndPaginate($name)
    {
        return Role::description($name)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}