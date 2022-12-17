<?php
/**
 * News request action
 *
 * @since 1.1.2
 */

class ActionNews
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
     * Create message and send telegram message using helper
     */
    private static function send_telegram($data, $content = [])
    {
        $content = "<strong>Опубликована незапланированная новость:</strong>";

        if (isset($_ENV['NEWS_CHAT'])) {
            $message = [
                'chat_id' => $_ENV['NEWS_CHAT'],
                'text' => $content . "\n" . $data->link,
                'disable_web_page_preview' => true
            ];

            SenderTelegram::send_message($message);
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

         if (!isset($data->link)) {
            return Flight::output('Поле link не может быть пустым', 401);
        }

        // Send message to Telegram
        self::send_telegram($data);

        // Successfully exit
        Flight::output('Сообщение успешно отправлено', 200, true);
    }
}
