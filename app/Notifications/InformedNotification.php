<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InformedNotification extends Notification
{
	/**
	 * The message to send via Email.
	 *
	 * @var message
	 */
	public $message;

	/**
	 * The callback that should be used to build the mail message.
	 *
	 * @var \Closure|null
	 */
	public static $toMailCallback;

	/**
	 * Create a notification instance.
	 *
	 * @param  string $message
	 * @return void
	 */
	public function __construct($message)
	{
		$this->message = $message;
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
			return call_user_func(static::$toMailCallback, $notifiable, $this->message);
		}

		return (new MailMessage)->line($this->message);
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
