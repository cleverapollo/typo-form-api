<?php

namespace App;

use App\Models\ApplicationUser;
use App\Models\OrganisationUser;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordRequest as ResetPasswordNotification;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
	use Authenticatable, Authorizable;
	use CanResetPassword;
	use Notifiable;
	use HasRolesAndAbilities;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'first_name',
		'last_name',
		'email',
		'password',
		'role_id',
		'api_token',
		'expire_date',
		'status',
		'workflow_delay'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
	];

	/**
	 * Get the organisations for the User.
	 */
	public function organisations()
	{
		return $this->belongsToMany('App\Models\Organisation', 'organisation_users')
			->using(OrganisationUser::class)
			->withPivot('role_id')
			->withTimestamps();
	}

	/**
	 * Get the applications for the User.
	 */
	public function applications()
	{
		return $this->belongsToMany('App\Models\Application', 'application_users')
			->using(ApplicationUser::class)
			->withPivot('role_id')
			->withTimestamps();
	}

	/**
	 * Get the forms for the User.
	 */
	public function forms()
	{
		return $this->hasMany('App\Models\Form');
	}

	/**
	 * Get user role
	 */
	public function role()
	{
		return $this->belongsTo('App\Models\Role');
	}

    /**
     * Get the logs for the User.
     */
    public function logs()
    {
        return $this->hasMany('App\Models\Log');
    }

    /**
     * Get all of the User's notes.
     */
    public function notes()
    {
        return $this->morphMany('App\Models\Note', 'recordable');
    }

	/**
	 * Send the password reset notification.
	 *
	 * @param  string  $token
	 * @return void
	 */
	public function sendPasswordResetNotification($token)
	{
		$this->notify(new ResetPasswordNotification($token));
	}

    /**
     * Set Email Attribute
     *
     * @param  string  $value
     * @return void
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Get Email Attribute
     *
     * @param  string  $value
     * @return string
     */
    public function getEmailAttribute($value)
    {
        return strtolower($value);
    }
}
