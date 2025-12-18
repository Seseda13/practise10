<?php
session_start();
require_once("../settings/connect_datebase.php");
require_once("../libs/autoload.php"); // Для ReCaptcha

$login = $_POST['login'] ?? '';

// === Проверка reCAPTCHA ===
if (!isset($_POST["g-recaptcha-response"]) || empty($_POST["g-recaptcha-response"])) {
    echo "-1";
    exit;
}

$secret = "6LfVXi8sAAAAAIFnr0jBaIqnFcQoVYNY1LST-my7";
$recaptcha = new \ReCaptcha\ReCaptcha($secret);
$response = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);

if (!$response->isSuccess()) {
    echo "-1";
    exit;
}

// === Если капча пройдена — ищем пользователя ===
$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login` = '".$mysqli->real_escape_string($login)."'");
$user_read = $query_user->fetch_row();

if (!$user_read) {
    echo "-1"; // Пользователь не найден
    exit;
}

$id = $user_read[0];

// === Генерация нового пароля ===
function PasswordGeneration() {
    $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
    $max = 10;
    $size = strlen($chars) - 1;
    $password = "";
    while ($max--) {
        $password .= $chars[rand(0, $size)];
    }
    return $password;
}

do {
    $new_password = PasswordGeneration();
    $hashed_password = md5($new_password);
    
    // Проверяем, не используется ли такой хэш (редкий случай)
    $check = $mysqli->query("SELECT 1 FROM `users` WHERE `password` = '$hashed_password'");
} while ($check->num_rows > 0);

// Обновляем пароль в БД
$mysqli->query("UPDATE `users` SET `password` = '$hashed_password' WHERE `id` = $id");

// Здесь можно раскомментировать отправку почты, когда почта будет настроена
// mail($login, 'Восстановление пароля', "Ваш новый пароль: $new_password");

echo $id; // Успешно — возвращаем ID пользователя
?>