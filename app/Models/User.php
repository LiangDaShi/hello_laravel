<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name' , 'email' , 'password' ,
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password' , 'remember_token' ,
	];

	public static function boot ()
	{
		parent::boot();

		static::creating(function ($user) {
			$user->activation_token = str_random(30);
		});
	}

	protected $table = 'users';

	public function gravatar ($size = '100')
	{
		$hash = md5(strtolower(trim($this->attributes['email'])));
		return "http://www.gravatar.com/avatar/$hash?s=$size";
	}

	public function sendPasswordResetNotification ($token)
	{
		$this->notify(new ResetPassword($token));
	}

	public function statuses ()
	{
		return $this->hasMany(Status::class);
	}

	public function feed ()
	{
		return $this->statuses()
			->orderBy('created_at' , 'desc');
	}

	//获取粉丝关系列表
	public function followers ()
	{
		return $this->belongsToMany(User::Class , 'followers' , 'user_id' , 'follower_id');
	}

	//获取用户关注人列表
	public function followings ()
	{
		return $this->belongsToMany(User::Class , 'followers' , 'follower_id' , 'user_id');
	}

	//关注
	public function follow($user_ids)
	{
		if (!is_array($user_ids)) {
			$user_ids = compact('user_ids');
		}
		$this->followings()->sync($user_ids, false);
	}

	//取管
	public function unfollow($user_ids)
	{
		if (!is_array($user_ids)) {
			$user_ids = compact('user_ids');
		}
		$this->followings()->detach($user_ids);
	}

	//是否关注
	public function isFollowing($user_id)
	{
		return $this->followings->contains($user_id);
	}
}
