<?php
/**
 * Send messages service
 *
 * @version 1.1.1
 */

require_once(__DIR__ . '/vendor/autoload.php');


/**
 * Autoload actions and senders
 */
Flight::path(__DIR__ . '/actions/');
Flight::path(__DIR__ . '/senders/');


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
 * Send to telegram and email callback single field
 */
Flight::route('POST /callback', [
    'ActionCallback', 'start_action'
], true);


/**
 * Save list of brief fields to file and send its link
 */
Flight::route('POST /brief', [
    'ActionBrief', 'start_action'
], true);


/**
 * Save list of vacancy fields to file and send its link
 */
Flight::route('POST /vacancy', [
    'ActionVacancy', 'start_action'
], true);


/**
 * Send to telegram mistype error
 */
Flight::route('POST /mistype', [
    'ActionMistype', 'start_action'
], true);


/**
 * Send to telegram club form
 */
Flight::route('POST /club', [
    'ActionClub', 'start_action'
], true);


/**
 * Send to telegram Social Planner errors
 */
Flight::route('POST /planner', [
    'ActionPlanner', 'start_action'
], true);


/**
 * Start application with Flight
 */
Flight::start();

