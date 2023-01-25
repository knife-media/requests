<?php
/**
 * Russia request action
 *
 * @since 1.0.0
 */

class ActionRussia
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
    private static function send_telegram($data)
    {
        $data->title = strip_tags($data->title);

        $content = "<strong>{$data->title}</strong>\n";
        $content = $content . $data->link . "\n\n";
        $content = $content . "Ссылки на посты в соцсетях:\n";

        if (isset($data->external)) {
            $external = (array) json_decode($data->external);

            foreach ($external as $link) {
                $content = $content . $link . "\n";
            }
        }

        if (isset($_ENV['RUSSIA_CHAT'])) {
            $message = [
                'chat_id' => $_ENV['RUSSIA_CHAT'],
                'text' => $content,
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

        if (!isset($data->link, $data->title)) {
            return Flight::output('Не заполнены необходимые поля', 400);
        }

        // Send message to Telegram
        self::send_telegram($data);

        // Successfully exit
        Flight::output('Сообщение успешно отправлено', 200, true);
    }
}
