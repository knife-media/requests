<?php
/**
 * Send message to Email using Mailgun API
 *
 * @since 1.0.0
 */

class SenderMailgun
{
    private static $api = 'https://api.eu.mailgun.net/v3/';


    /**
     * Send message to Telegram with
     */
    public static function send_message($message)
    {
        $curl = curl_init();

        // Make Mailgun API url
        $url = self::$api . $_ENV['MAILGUN_DOMAIN'];

        // Append from field
        $message['from'] = $_ENV['MAILGUN_FROM'];

        curl_setopt($curl, CURLOPT_USERPWD, 'api:' . $_ENV['MAILGUN_TOKEN']);
        curl_setopt($curl, CURLOPT_URL, $url . '/messages');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $message);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}
