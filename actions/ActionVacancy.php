<?php
/**
 * Vacancy request action
 *
 * @since 1.1.0
 */

class ActionVacancy
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
        $details = [];

        if (isset($data->heading)) {
            $details[] = sprintf('Вакансия: %s', htmlspecialchars($data->heading));
        }

        if (isset($data->name)) {
            $details[] = sprintf('Имя кандидата: %s', htmlspecialchars($data->name));
        }

        if (isset($data->mention)) {
            $details[] = sprintf('Получатель заявки: %s', htmlspecialchars($data->mention));
        }

        $content = sprintf("%s\n\n%s\n\n%s",
            sprintf('<strong>Добавлено новое резюме #%d:</strong>', $data->id),
            implode("\n", $details),
            $_ENV['REQUESTS_URL'] . $data->path
        );

        if (isset($_ENV['VACANCY_CHAT'])) {
            $message = [
                'chat_id' => $_ENV['VACANCY_CHAT'],
                'text' => $content
            ];

            SenderTelegram::send_message($message);
        }
    }


    /**
     * Update vacancy id in options
     */
    private static function update_id($root, $id = 1)
    {
        $redis = new Redis();

        if (!isset($_ENV['REDIS_HOST'], $_ENV['REDIS_PREFIX'])) {
            return $id;
        }

        $redis->connect($_ENV['REDIS_HOST']);

        // Redis name
        $name = $_ENV['REDIS_PREFIX'] . 'vacancy';

        // Get value
        $value = $redis->get($name);

        if ($value == false) {
            $value = $id;

            // Set new value if not exists
            $redis->set($name, $value);
        }

        // Increment option
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

        // Make storage vacancy file
        $data->path = sprintf("/storage/vacancy/%d-%s.html", $data->id, $secret);

        // Create directory if not exists
        if (!is_dir($root . '/storage/vacancy')) {
            mkdir($root . '/storage/vacancy');
        }

        ob_start();

        // Find vacancy template
        include_once($root . '/templates/vacancy.php');

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

        // Successfully exit
        Flight::output('Сообщение успешно отправлено', 200, true);
    }
}
