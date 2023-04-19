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

        $emails = [];

        if (isset($_ENV['BRIEF_EMAIL'])) {
            $emails = explode(',', $_ENV['BRIEF_EMAIL']);
        }

        foreach ($emails as $email) {
            $message = [
                'to' => trim($email),
                'subject' => 'Добавлена новая заявка #' . $data->id,
                'html' => $content
            ];

            SenderMailgun::send_message($message);
        }
    }


    /**
     * Create message and send telegram message using helper
     */
    private static function send_telegram($data)
    {
        $content = sprintf(
            "<b>В форму рекламы добавлена заявка #%d:</b>\n%s",
            $data->id, $_ENV['REQUESTS_URL'] . $data->path
        );

        if (isset($_ENV['BRIEF_CHAT'])) {
            $message = [
                'chat_id' => $_ENV['BRIEF_CHAT'],
                'text' => $content
            ];

            SenderTelegram::send_message($message);
        }
    }


    /**
     * Update brief id in options
     */
    private static function update_id($root, $meta = 'brief')
    {
        $db = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8", $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);

        $select = $db->prepare("SELECT value FROM options WHERE meta = '{$meta}'");
        $select->execute();

        $result = $select->fetch(PDO::FETCH_OBJ);

        if (empty($result)) {
            $insert = $db->prepare("INSERT INTO options (meta, value) VALUES ('{$meta}', 1)");
            $insert->execute();

            return 1;
        }

        $update = $db->prepare("UPDATE options SET value = value + 1 WHERE meta = '{$meta}'");
        $update->execute();

        return $result->value;
    }


    /**
     * Create file from data
     */
    private static function save_request($data) {
        $root = Flight::get('config.root_path');

        // Get id from options
        $data->id = self::update_id($root);

        // Make storage brief file
        $data->path = sprintf("/storage/brief/%d-%s.html", $data->id, substr(md5(uniqid()), -8));

        // Create directory if not exists
        if (!is_dir($root . '/storage/brief')) {
            mkdir($root . '/storage/brief');
        }

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
            return Flight::output('Поле fields не может быть пустым', 400);
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
