<?php

namespace App\Models;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class User extends EloquentUser
{
    use SoftDeletes;

    protected $dates = ['deleted_at', 'last_login'];

    /**
     * The connection name for the model
     * @var string
     */
    protected $connection = 'eglobalt_auth';

    /**
     * The atributes that are mass assignable
     * @var array
     */
    protected $fillable = [
        'description', 
        'username', 
        'email', 
        'password', 
        'owner_id', 
        'branch_id',
        'reset_password_code', 
        'reset_password', 
        'doc_number',
        'phone_number',
        'created_by'
    ];

     // Mutador (mutator) para establecer el campo `last_login` como instancia de Carbon
     public function setLastLoginAttribute($value)
     {
         $this->attributes['last_login'] = Carbon::parse($value);
     }
 
     // Accesor (accessor) para obtener el campo `last_login` como instancia de Carbon
     public function getLastLoginAttribute($value)
     {
         return Carbon::parse($value);
     }

    protected $loginNames = ['username'];
    /**
     * The atributes excluded from the model's JSON form
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The Eloquent Role model name
     * @var string
     */
    protected static $rolesModel = 'App\Models\Role';
    
    /**
     * The Eloquent persistence model name
     * @var string
     */
    protected static $persistencesModel = 'App\Models\Persistence';

    /**
     * The Eloquent activations model name
     * @var string
     */
    protected static $activationsModel = 'App\Models\Activation';

    /**
     * The Eloquent reminder model name
     * @var string 
     */
    protected static $remindersModel = 'App\Models\Reminder';
    
    /**
     * The Eloquent throttling model name
     * @var string
     */
    protected static $throttlingModel = 'App\Models\Throttle';
    
    /**
     * Get Owner Model for User
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\Owner', 'owner_id');
    }

    /**
     * Get Branch Model for User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo('App\Models\Branch', 'branch_id');
    }

    /**
     * Get activation Model for User
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activation()
    {
        return $this->hasOne('App\Models\Activation');
    }

    /**
     * Suspend the user associated with the throttle
     * @return void
     */
    public function suspend()
    {
        if ($this->suspended){
            $this->suspended = true;
            $this->suspended_at = $this->freshTimestamp();
            $this->save();
        }
    }

    /**
     * Unsuspend the user
     * @return void
     */
    public function unSuspend()
    {
        if ($this->suspended) {
            $this->suspended = false;
            $this->suspended_at = null;
            $this->save();
        }
    }

    /**
     * Check if the user is suspended.
     *
     * @return bool
     */
    public function isSuspended()
    {
        if ($this->suspended and $this->suspended_at) {
            $this->removeSuspensionIfAllowed();
            return (bool)$this->suspended;
        }
        return false;
    }

    /**
     * Ban the user.
     *
     * @return void
     */
    public function ban()
    {
        if (!$this->banned) {
            $this->banned = true;
            $this->banned_at = $this->freshTimestamp();

            $this->suspended = true;
            $this->suspended_at = $this->freshTimestamp();

            $this->save();
        }
    }

    /**
     * Unban the user.
     *
     * @return void
     */
    public function unBan()
    {
        if ($this->banned) {
            $this->banned = false;
            $this->banned_at = null;

            $this->suspended = false;
            $this->suspended_at = null;
            $this->save();
        }
    }

    /**
     * Check if user is banned
     *
     * @return bool
     */
    public function isBanned()
    {
        return $this->banned;
    }

    /**
    * Check user throttle status.
    * @return bool
    * @throws UserBannedException
    * @throws UserSuspendedException
    */
    public function check()
    {
        if ($this->isBanned()) {
            throw new UserBannedException(sprintf(
                'User [%s] has been banned.',
                $this->getUser()->getLogin()
            ));
        }
        if ($this->isSuspended()) {
            throw new UserSuspendedException(sprintf(
                'User [%s] has been suspended.',
                $this->getUser()->getLogin()
            ));
        }
        return true;
    }

    /**
     * Return true if the user was activated.
     * @return mixed
     */
    public function isActivated()
    {
        if (empty($this->activation))
            return false;

        return $this->activation->completed;
    }

    /**
     * Get a reset password code for the given user.
     *
     * @return string
     */
    public function getResetPasswordCode()
    {
        $this->reset_password_code = $resetCode = $this->getRandomString();
        $this->save();
        return $resetCode;
    }

    /**
     * Checks if the provided user reset password code is
     * valid without actually resetting the password.
     *
     * @param  string $resetCode
     * @return bool
     */
    public function checkResetPasswordCode($resetCode)
    {
        return ($this->reset_password_code == $resetCode);
    }

    /**
     * Attempts to reset a user's password by matching
     * the reset code generated with the user's.
     *
     * @param  string $resetCode
     * @param  string $newPassword
     * @return bool
     */
    public function attemptResetPassword($resetCode, $newPassword)
    {
        if ($this->checkResetPasswordCode($resetCode)) {

            $credentials = [
                'password'            => $newPassword,
                'reset_password_code' => null,
                'reset_password'      => false
            ];

            return \Sentinel::update($this, $credentials);
        }
        return false;
    }

    /**
     * Wipes out the data associated with resetting
     * a password.
     *
     * @return void
     */
    public function clearResetPassword()
    {
        if ($this->reset_password_code) {
            $this->reset_password_code = null;
            $this->save();
        }
    }

    /**
     * Generate a random string.
     *
     * @return string
     */
    public function getRandomString($length = 42)
    {
        // We'll check if the user has OpenSSL installed with PHP. If they do
        // we'll use a better method of getting a random string. Otherwise, we'll
        // fallback to a reasonably reliable method.

        if (function_exists('openssl_random_pseudo_bytes')) {

            // We generate twice as many bytes here because we want to ensure we have
            // enough after we base64 encode it to get the length we need because we
            // take out the "/", "+", and "=" characters.

            $bytes = openssl_random_pseudo_bytes($length * 2);
            // We want to stop execution if the key fails because, well, that is bad.
            if ($bytes === false) {
                throw new \RuntimeException('Unable to generate random string.');
            }
            return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
        }
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    /**
     * Return true if the user is a Super User
     *
     * @return bool
     */
    public function isSuperuser()
    {

        return $this->hasAccess('superuser');

    }

    /**
     * Check if the User has a Role
     *
     * @param null $slug
     * @return bool
     */
    public function hasRole($slug = null)
    {

        if (is_null($slug) or empty($slug)) return false;

        $roles = $this->roles;

        foreach ($roles as $role) {
            if ($role->slug == $slug) return true;
        }

        return false;
    }

    /**
     * Force User logout
     *
     */
    public function forceLogout()
    {
        foreach ($this->persistences as $persistence) {
            $persistence->delete();
        }
    }

    /**
     * Process all User's permissions and return it
     *
     * @return Collection
     */
    public function getProcessedPermissions()
    {

        // Get the User's roles permissions
        $rolesPermissions = [];
        $i = 0;
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $p => $s) {
                $rolesPermissions[$i][$p]['state'] = $s;
                $rolesPermissions[$i][$p]['inherited'] = true;
            }
            $i++;
        }

        // Merge the permissions with a denied permission taking priority
        $processedPermissions = Collection::make(array_shift($rolesPermissions));

        foreach ($rolesPermissions as $rolePermissions) {
            foreach ($rolePermissions as $permission => $info) {

                if (!$processedPermissions->has($permission) OR
                    ($processedPermissions->has($permission) AND !$info['state'])
                ) {
                    $processedPermissions->put($permission, $info);
                }
            }
        }

        // dd($this->permissions);

        // Merge the Users permissions, taking priority over roles
        foreach ($this->permissions as $permission => $state) {
            $processedPermissions->put($permission, ['state' => $state, 'inherited' => false]);
        }

        $sortedPermissions = $processedPermissions->all();

        ksort($sortedPermissions);

        return Collection::make($sortedPermissions);

    }

    /**
     * Filter by description
     */
    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('description', 'ILIKE', "%$description%");
        }
    }
    public static function filterAndPaginate($name)
    {
        return User::description($name)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}
