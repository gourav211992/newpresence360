<?php

namespace App\Services\Mailers;

use Error;
use Log;
use Mail;
use App\Models\MailBox;
use App\Services\LoggerFactory;
use Illuminate\Support\Facades\View;
use Swift_SmtpTransport;
use Swift_Mailer;

class Mailer
{
	/**
	 * Add an email to the queue to be sent
	 *
	 * @param  string $queue      Name of the queue to add the email on
	 * @param  string $email      Email address to send the email to
	 * @param  string $view       Laravel view to template the email
	 * @param  array  $data       Array of data members to pass to the laravel view
	 * @param  string $subject    Subject of the email
	 * @param  ?      $attachment [description]
	 * @return null
	 */
	public function emailTo($mailbox, $mailer = 'alerts_p360')
	{
		Log::info('Mailer::emailTo called', ['MAILBOX' => $mailbox?->toArray()]);

		if (!$mailbox) {
			return;
		}

		$view = $mailbox->layout ?: 'email.generic';

		try {
			Mail::send($view, ['body' => json_decode($mailbox->mail_body)], function ($message) use ($mailbox) {
				$message->subject($mailbox->subject);

				// Helper to clean email strings
				$parseEmails = function ($emails) {
					if (empty($emails)) return [];
					$list = is_array($emails) ? $emails : explode(',', $emails);
					return array_filter(array_map(function ($email) {
						$email = trim($email);
						return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
					}, $list));
				};

				$to = $parseEmails($mailbox->mail_to);
				if (!empty($to)) {
					$message->to($to);
				}

				$cc = $parseEmails($mailbox->mail_cc);
				if (!empty($cc)) {
					$message->cc($cc); // ✅ Correct usage
				}

				$bcc = $parseEmails($mailbox->mail_bcc);
				if (!empty($bcc)) {
					$message->bcc($bcc); // ✅ Correct usage
				}
				if (!empty($mailbox->attachment)) {
					// Try to decode it as JSON
					$decoded = json_decode($mailbox->attachment, true);

					// If it's a valid array (list of attachments with details)
					if (is_array($decoded) && isset($decoded[0])) {
						foreach ($decoded as $file) {
							$message->attachFromPath(
								$file['path'] ?? $file, // fallback in case path is directly string
								$file['as'] ?? null,
								$file['mime'] ?? 'application/octet-stream'
							);
						}
					}
					// If it's just a simple string path
					elseif (is_string($mailbox->attachment)) {
						$message->attachFromPath($mailbox->attachment);
					}
				}

			});

			$mailbox->status = MailBox::STATUS_COMPLETED;
			$mailbox->response = 'success';
			$mailbox->save();

			Log::info('Email sent successfully:', [
				'mailbox_id' => $mailbox->id,
				'mail_to' => $mailbox->mail_to,
				'subject' => $mailbox->subject,
			]);
		} catch (\Exception $e) {
			$mailbox->status = MailBox::STATUS_REJECTED;
			$mailbox->response = $e->getMessage();
			$mailbox->save();

			Log::info('Email not sent successfully:', [
				'error' => $e->getMessage(),
				'mailbox_id' => $mailbox->id,
				'mail_to' => $mailbox->mail_to,
				'mail_cc' => $mailbox->mail_cc,
				'mail_bcc' => $mailbox->mail_bcc,
			]);

			$mode = app()->runningInConsole() ? "cron" : "web";
		}
	}

	public function emailToNotification($mailbox)
	{
		$view = $mailbox->layout ? $mailbox->layout : 'generic';
		try {
			Mail::send('email.' . $view, json_decode($mailbox->mail_body, true), function ($message) use ($mailbox) {
				$message->subject($mailbox->subject);
				$message->to(explode(',', $mailbox->mail_to));
				if ($mailbox->mail_cc) {
					$message->cc(explode(',', $mailbox->mail_cc));
				}
				if ($mailbox->mail_bcc) {
					$message->bcc(explode(',', $mailbox->mail_bcc));
				}

				if ($mailbox->attachment) {
					$message->attach($mailbox->attachment);
				}
				// $message->from('FROM_EMAIL_ADDRESS','Artisans Web');
			});

			if ($mailbox) {
				// $mailbox->status = MailBox::STATUS_COMPLETED;
				$mailbox->response = 'success';
				$mailbox->save();
			}
		} catch (\Exception $e) {
			$errorMessage = array();
			$errorMessage['message'] = $e->getMessage();
			$errorMessage['url'] = request()->url();

			if ($mailbox) {
				// $mailbox->status = MailBox::STATUS_REJECTED;
				$mailbox->response = $e->getMessage();
				$mailbox->save();
			}

			$mode = "web";
			if (app()->runningInConsole()) {
				$mode = "cron";
			}

			$this->log = (new LoggerFactory)->setPath('logs/emails')->createLogger($mode . '-email-sending-issue');
			$this->log->info('Issue in email sending:', $errorMessage);
		}
	}

	public function sendEmailLater($mailbox)
	{
		$view = $mailbox->layout ?: 'email.generic';
		try {
			Mail::send($view, ['body' => json_decode($mailbox->mail_body)], function ($message) use ($mailbox) {
				$message->subject($mailbox->subject);
				$message->to(explode(',', $mailbox->mail_to));
				if ($mailbox->mail_cc) {
					$message->cc(explode(',', $mailbox->mail_cc));
				}
				if ($mailbox->mail_bcc) {
					$message->bcc(explode(',', $mailbox->mail_bcc));
				}
				if ($mailbox->attachment) {
					$message->attach($mailbox->attachment);
				}
				// $message->from('FROM_EMAIL_ADDRESS','Artisans Web');
			});

			if ($mailbox) {
				$mailbox->status = MailBox::STATUS_COMPLETED;
				$mailbox->response = 'success';
				$mailbox->save();
			}
		} catch (\Exception $e) {
			$errorMessage = array();
			$errorMessage['message'] = $e->getMessage();
			$errorMessage['url'] = request()->url();

			if ($mailbox) {
				$mailbox->status = MailBox::STATUS_REJECTED;
				$mailbox->response = $e->getMessage();
				$mailbox->save();
			}

			$mode = "web";
			if (app()->runningInConsole()) {
				$mode = "cron";
			}

			$this->log = (new LoggerFactory)->setPath('logs/emails')->createLogger($mode . '-email-sending-issue');
			$this->log->info('Issue in email sending:', $errorMessage);
		}
	}

	public function queueTo($queue, $email, $view, $data, $subject, $attachment = null, $attachment_options = null)
	{
		if (is_string($email)) {
			$email = trim($email);
		}

		if (is_array($email)) {
			$email = array_map(
				function ($e) {
					return trim($e);
				},
				$email
			);
		}
		$view_text = $view . '-plain-text';
		if (!View::exists($view_text)) {
			$view_text = $view;
		}
		try {

			\Mail::queue(
				['text' => $view_text, 'html' => $view],
				$data,
				function ($message) use ($email, $subject, $attachment, $attachment_options) {
					$message->to($email)->subject($subject);
					$message->from(env('MAIL_DEFAULT_FROM'));

					if ($attachment != null) {
						$message->attachData($attachment, "parking-coupon.jpg");
					}
				}
			);
		} catch (\Exception $e) {
			$errorMessage = array();
			$errorMessage['message'] = $e->getMessage();
			$errorMessage['url'] = request()->url();

			$mode = "web";
			if (app()->runningInConsole()) {
				$mode = "cron";
			}

			$this->log = (new LoggerFactory)->setPath('logs/emails')->createLogger($mode . '-email-sending-issue');
			$this->log->info('Issue in email sending:', $errorMessage);
		}
	}
}
