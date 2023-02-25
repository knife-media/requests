<?php
/**
 * Mistype request action
 *
 * @since 1.0.0
 */

class ActionMistype
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
        $content[] = "<strong>Сообщение об ошибкe</strong>\n" . $data->marked;

        // Add comment if not empty
        if (!empty($data->comment)) {
            $content[] = "<strong>Комментарий</strong>\n" . $data->comment;
        }

        // Add location
        if (!empty($data->location)) {
            $content[] = $data->location;
        }

        // Add ip address
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $content[] = '<em>Пользователь: ' . $_SERVER['HTTP_X_REAL_IP'] . '</em>';
        }

        if (isset($_ENV['MISTYPE_CHAT'])) {
            $message = [
                'chat_id' => $_ENV['MISTYPE_CHAT'],
                'text' => implode("\n\n", $content),
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

        if (empty($data->marked)) {
            return Flight::output('Поле marked не может быть пустым', 401);
        }

        // Send message to Telegram
        self::send_telegram($data);

        // Successfully exit
        Flight::output('Сообщение успешно отправлено', 200, true);
    }
}
