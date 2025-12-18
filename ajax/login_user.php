<?php
session_start();
require_once("../settings/connect_datebase.php");

$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

// === Проверка reCAPTCHA v3 ===
if (!isset($_POST['recaptcha_v3_token']) || empty($_POST['recaptcha_v3_token'])) {
    echo "-1";
    exit;
}

// ВАШ секретный ключ от reCAPTCHA v3
$secret_v3 = "6LenZC8sAAAAAOuof3xU1kAsdP9yfLiwnLfk5oxy"; 
$verifyURL = 'https://www.google.com/recaptcha/api/siteverify';

$post_data = http_build_query([
    'secret'   => $secret_v3,
    'response' => $_POST['recaptcha_v3_token'],
    'remoteip' => $_SERVER['REMOTE_ADDR']
]);

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => $post_data
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($verifyURL, false, $context);

if ($result === false) {
    echo "-1";
    exit;
}

$result_json = json_decode($result);

// Для отладки - можно посмотреть полный ответ
error_log("reCAPTCHA response: " . print_r($result_json, true));

if (!$result_json->success) {
    // Если не успех, выводим ошибки для отладки
    error_log("reCAPTCHA errors: " . print_r($result_json->{'error-codes'}, true));
    echo "-1";
    exit;
}

// Проверяем score (рекомендуется 0.5 и выше для логина)
if ($result_json->score < 0.5) {
    error_log("reCAPTCHA low score: " . $result_json->score);
    echo "-1";
    exit;
}

// === Защита от SQL инъекций ===
$login = $mysqli->real_escape_string($login);
$password = $mysqli->real_escape_string($password);

$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."' AND `password`= '".$password."'");
$id = -1;

while ($user_read = $query_user->fetch_row()) {
    $id = $user_read[0];
}

if ($id != -1) {
    $_SESSION['user'] = $id;
    echo md5(md5($id));
} else {
    echo "-1";
}
?>