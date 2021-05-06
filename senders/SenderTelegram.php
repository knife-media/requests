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
        $curl = curl_init();

        if (empty($message['parse_mode'])) {
            $message['parse_mode'] = $mode;
        }

        // Make Telegram send message url
        $url = self::$api . $_ENV['TELEGRAM_TOKEN'] . "/sendMessage";

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $message);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}
