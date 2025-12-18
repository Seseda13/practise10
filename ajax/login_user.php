<?php
session_start();
require_once("../settings/connect_datebase.php");
require_once("../libs/autoload.php");

$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

// Проверка reCAPTCHA
if (!isset($_POST["g-recaptcha-response"]) || empty($_POST["g-recaptcha-response"])) {
    echo "-1";
    exit;
}

$secret = "6LfVXi8sAAAAAIFnr0jBaIqnFcQoVYNY1LST-my7"; 
$recaptcha = new \ReCaptcha\ReCaptcha($secret);
$response = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);

if (!$response->isSuccess()) {
    echo "-1"; // Капча не пройдена
    exit;
}

// Если капча пройдена — проверяем логин и пароль
$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."' AND `password`= '".$password."'");
$id = -1;

while ($user_read = $query_user->fetch_row()) {
    $id = $user_read[0];
}

if ($id != -1) {
    $_SESSION['user'] = $id;
    echo md5(md5($id)); // Возвращаем токен
} else {
    echo "-1"; // Неверный логин/пароль
}
?>