<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Tạo URL xác minh không phụ thuộc session.
     * Embed user_id + email vào signed URL → mở được trên bất kỳ thiết bị nào.
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify.direct',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Xác Minh Địa Chỉ Email')
            ->greeting('Xin chào!')
            ->line('Vui lòng nhấn vào nút bên dưới để xác minh địa chỉ email của bạn.')
            ->action('Xác Minh Email', $verificationUrl)
            ->line('Nếu bạn không tạo tài khoản, bạn không cần thực hiện thêm bất kỳ thao tác nào.')
            ->salutation('Trân trọng,');
    }
}
