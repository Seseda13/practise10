<?php
session_start();
require_once("./settings/connect_database.php");
require_once("./libs/autoload.php");

$login = $_POST['login'];
$password = $_POST['password'];

// ищем пользователя
$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login` = '".$login."'");
$id = $query_user->fetch_row();

if($user_read = $query_user->fetch_row()) {
    echo $id;
} else {
    if(isset($_POST["g-recaptcha-response"]) == false) {
        echo "Нет проверки 'Я не робот'";
    } exit;

    $secret = "6LfVXi8sAAAAAIFnr0jBaIqnFcQoVYNY1LST-my7";
    $recaptcha = new \ReCaptcha\ReCaptcha($secret);

    $response = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);

    if($response->isSuccess()) {
        $mysqli->query("INSERT INTO `users` (`login`, `password`, `roll`) VALUES ('".$login."', '".$password."', @)");
        
        $query_user = $mysqli->query("SELECT * FROM `users` WHERE `login` = '".$login."' AND `password` = '".$password."'");
        $user_new = $query_user->fetch_row();
        $id = $user_new[0];

        if($id != -1) $_SESSION['user'] = $id; // запоминаем пользователя
        echo $id;
    } else {
        echo "Пользователь не распознан.";
    } exit;
}
?>