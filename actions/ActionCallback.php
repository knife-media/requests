<?php
/**
 * Callback request action
 *
 * @since 1.0.0
 */

class ActionCallback
{
    /**
     * Check request nonce
     */
    private static function verify_nonce($timestamp, $nonce)
    {
        $secret = $_ENV['NONCE_SECRET'] ?? '';

        // Generate verification hash
        $verify = substr(sha1($secret . $timestamp), -12, 10);

        if ($verify === $nonce) {
            return true;
        }

        return false;
    }


    /**
     * Create message and send email using helper
     */
    private static function send_email($data)
    {
        $content = sprintf(
            '<b>Необходимо связаться с клиентом по адресу: </b><br><a href="mailto:%1$s">%1$s</a>',
            htmlspecialchars($data->email)
        );

        $emails = [];

        if (isset($_ENV['CALLBACK_EMAIL'])) {
            $emails = explode(',', $_ENV['CALLBACK_EMAIL']);
        }

        foreach ($emails as $email) {
            $message = [
                'to' => $email,
                'subject' => 'Запрос обратной связи',
                'html' => $content
            ];

            HelperMailgun::send_message($message);
        }
    }


    /**
     * Create message and send telegram message using helper
     */
    private static function send_telegram($data)
    {
        $content = sprintf(
            "<b>Запрос обратной связи: </b>\n%s", htmlspecialchars($data->email)
        );

        if (isset($_ENV['CALLBACK_CHAT'])) {
            $message = [
                'chat_id' => $_ENV['CALLBACK_CHAT'],
                'text' => $content
            ];

            HelperTelegram::send_message($message);
        }
    }


    /**
     * Entry point to this class
     */
    public static function start_action()
    {
        $data = Flight::request()->data;

        if (!isset($data->time, $data->nonce)) {
            return Flight::output('Ошибка проверки токена безопасности', 403);
        }

        if (!self::verify_nonce($data->time, $data->nonce)) {
            return Flight::output('Ошибка проверки токена безопасности', 403);
        }

        if (empty($data->email)) {
            return Flight::output('Поле email не может быть пустым', 401);
        }

        // Send message to Telegram
        self::send_telegram($data);

        // Send message to Email
        self::send_email($data);

        // Successfully exit
        Flight::output('Сообщение успешно отправлено', 200, true);
    }
}
