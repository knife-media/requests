<?php
/**
 * Send messages service
 *
 * @version 1.0.0
 */

require_once(__DIR__ . '/vendor/autoload.php');


/**
 * Autoload actions and helpers
 */
Flight::path(__DIR__ . '/actions/');
Flight::path(__DIR__ . '/helpers/');


/**
 * Set root path variable
 */
Flight::set('config.root_path', __DIR__);


/**
 * Try to load dotenv config
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


/**
 * Remap default errors
 */
Flight::map('error', function (Exception $e) {
    Flight::json(['success' => false, 'message' => 'Неизвестная ошибка сервера'], 500);
    exit;
});

Flight::map('notFound', function () {
    Flight::json(['success' => false, 'message' => 'Метод не найден'], 404);
    exit;
});

Flight::map('output', function($message, $code = 500, $success = false) {
    Flight::json(['success' => $success, 'message' => $message], $code);
    exit;
});


/**
 * Send to telegram and email feedback single field
 */
Flight::route('POST /feedback', [
    'ActionFeedback', 'start_action'
], true);


/**
 * Save list of brief fields to file and send its link
 */
Flight::route('POST /brief', [
    'ActionBrief', 'start_action'
], true);


/**
 * Start application with Flight
 */
Flight::start();

