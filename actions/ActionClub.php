<?php
/**
 * Club request action
 *
 * @since 1.1.0
 */

class ActionClub
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
        $content = sprintf("%s\n\n%s \n%s \n\n%s",
            sprintf('<strong>В клуб добавлена новая заявка #%d</strong>', $data->id),
            sprintf('Автор: %s', htmlspecialchars($data->name)),
            sprintf('Тема: %s', htmlspecialchars($data->subject)),
            $_ENV['REQUESTS_URL'] . $data->path
        );

        if (isset($_ENV['CLUB_CHAT'])) {
            $message = [
                'chat_id' => $_ENV['CLUB_CHAT'],
                'text' => $content
            ];

            HelperTelegram::send_message($message);
        }
    }


    /**
     * Update club id in options
     */
    private static function update_id($root, $id = 1)
    {
        $redis = new Redis();

        if (!isset($_ENV['REDIS_HOST'], $_ENV['REDIS_PREFIX'])) {
            return $id;
        }

        $redis->connect($_ENV['REDIS_HOST']);

        // Club redis name
        $name = $_ENV['REDIS_PREFIX'] . 'club';

        // Get club value
        $value = $redis->get($name);

        if ($value == false) {
            $value = $id;

            // Set new club value if not exists
            $redis->set($name, $value);
        }

        // Increment club option
        $redis->incr($name);

        return $value;
    }


    /**
     * Create file from data
     */
    private static function save_request($data) {
        $root = Flight::get('config.root_path');

        // Get id from options
        $data->id = self::update_id($root);

        $secret = substr(md5(uniqid()), -8);

        // Make storage club file
        $data->path = sprintf("/storage/club/%d-%s.html", $data->id, $secret);

        // Create directory if not exists
        if (!is_dir($root . '/storage/club')) {
            mkdir($root . '/storage/club');
        }

        ob_start();

        // Find club template
        include_once($root . '/templates/club.php');

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

        if (empty($data->name)) {
            return Flight::output('Поле имени не может быть пустым', 400);
        }

        if (empty($data->email)) {
            return Flight::output('Поле почты не может быть пустым', 400);
        }

        if (empty($data->subject)) {
            return Flight::output('Поле темы не может быть пустым', 400);
        }

        $data = self::save_request($data);

        // Send message to Telegram
        self::send_telegram($data);

        // Successfully exit
        Flight::output('Сообщение успешно отправлено', 200, true);
    }
}
