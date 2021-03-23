<?php
/**
 * Send message to Telegram
 *
 * @since 1.0.0
 */

class SenderTelegram
{
    private static $api = 'https://api.telegram.org/bot';


    /**
     * Send message to Telegram with
     */
    public static function send_message($message, $mode = 'HTML')
    {
        if (empty($message['parse_mode'])) {
            $message['parse_mode'] = $mode;
        }

        // Make Telegram send message url
        $url = self::$api . $_ENV['TELEGRAM_TOKEN'] . "/sendMessage?";

        // Try to send message
        $result = @file_get_contents($url . http_build_query($message));

        return $result;
    }
}
