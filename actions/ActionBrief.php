<?php
/**
 * Brief request action
 *
 * @since 1.0.0
 */

class ActionBrief
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
            '<b>В форму рекламы добавлена заявка #%1$d:</b><br><a href="%2$s">%2$s</a>',
            $data->id, $_ENV['REQUESTS_URL'] . $data->path
        );

        $message = [
            'to' => $_ENV['FEEDBACK_EMAIL'],
            'subject' => 'Добавлена новая заявка',
            'html' => $content
        ];

        HelperMailgun::send_message($message);
    }


    /**
     * Create message and send telegram message using helper
     */
    private static function send_telegram($data)
    {
        $content = sprintf(
            "<b>В форму рекламы добавлена заявка #%d:</b>\n%s",
            $data->id, $_ENV['REQUESTS_UR'] . $data->path
        );

        $message = [
            'chat_id' => $_ENV['FEEDBACK_CHAT'],
            'text' => $content
        ];

        HelperTelegram::send_message($message);
    }


    /**
     * Update brief id in options
     */
    private static function update_id($root, $id = 1)
    {
        $args = new stdClass();

        // Get json from options
        $json = @file_get_contents($root . '/storage/options.json');

        if ($json !== false) {
            $args = json_decode($json, false);
        }

        if (isset($args->brief)) {
            $id = $args->brief + 1;
        }

        $args->brief = $id;

        // Make new json
        $json = json_encode($args);

        // Save updated options
        file_put_contents($root . '/storage/options.json', $json);

        return $id;
    }


    /**
     * Create file from data
     */
    private static function save_request($data) {
        $root = Flight::get('config.root_path');

        // Get id from options
        $data->id = self::update_id($root);

        $secret = substr(md5(uniqid()), -8);

        // Make storage brief file
        $data->path = sprintf("/storage/brief/%d-%s.html", $data->id, $secret);

        ob_start();

        // Find brief template
        include_once($root . '/templates/brief.php');

        $content = ob_get_clean();
        file_put_contents($root . $data->path, $content);

        return $data;
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

        if (empty($data->fields)) {
            return Flight::output('Поле fields не может быть пустым', 401);
        }

        $data = self::save_request($data);

        // Send message to Telegram
        self::send_telegram($data);

        // Send message to Email
        self::send_email($data);

        // Successfully exit
        Flight::output('Сообщение успешно отправлено', 200, true);
    }
}
