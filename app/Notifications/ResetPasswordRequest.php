<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Http\Request;

class ResetPasswordRequest extends Notification
{
	/**
	 * The password reset token.
	 *
	 * @var string
	 */
	public $token;

	/**
	 * The callback that should be used to build the mail message.
	 *
	 * @var \Closure|null
	 */
	public static $toMailCallback;

	/**
	 * Create a notification instance.
	 *
	 * @param  string $token
	 * @return void
	 */
	public function __construct($token)
	{
		$this->token = $token;
	}

	/**
	 * Get the notification's channels.
	 *
	 * @param  mixed $notifiable
	 * @return array|string
	 */
	public function via($notifiable)
	{
		return ['mail'];
	}

	/**
	 * Build the mail representation of the notification.
	 *
	 * @param  mixed $notifiable
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail($notifiable)
	{
		if (static::$toMailCallback) {
			return call_user_func(static::$toMailCallback, $notifiable, $this->token);
		}
		$slug = explode('.', parse_url($_SERVER['HTTP_REFERER'])['host'])[0];
		return (new MailMessage)
			->line('You are receiving this email because we received a password reset request for your account.')
			->action('Reset Password', url('http://' . $slug . '.' . config('mail.fronturl') . '/password/reset/' . $this->token))
			->line('If you did not request a password reset, no further action is required.');
	}

	/**
	 * Set a callback that should be used when building the notification mail message.
	 *
	 * @param  \Closure $callback
	 * @return void
	 */
	public static function toMailUsing($callback)
	{
		static::$toMailCallback = $callback;
	}
}
