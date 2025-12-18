<?php
	session_start();
	include("./settings/connect_datebase.php");
	
	if (isset($_SESSION['user'])) {
		if($_SESSION['user'] != -1) {
			
			$user_query = $mysqli->query("SELECT * FROM `users` WHERE `id` = ".$_SESSION['user']);
			while($user_read = $user_query->fetch_row()) {
				if($user_read[3] == 0) header("Location: user.php");
				else if($user_read[3] == 1) header("Location: admin.php");
			}
		}
 	}
?>
<html>
	<head> 
		<meta charset="utf-8">
		<title> Авторизация </title>
		
		<script src="https://code.jquery.com/jquery-1.8.3.js"></script>
		<link rel="stylesheet" href="style.css">
		

		<script src="https://www.google.com/recaptcha/api.js?render=6LenZC8sAAAAAA-tARe4G4CapjV9-RWyJFT1md_s" async defer></script>
	</head>
	<body>
		<div class="top-menu">
			<a href=#><img src = "img/logo1.png"/></a>
			<div class="name">
				<a href="index.php">
					<div class="subname">БЗОПАСНОСТЬ  ВЕБ-ПРИЛОЖЕНИЙ</div>
					Пермский авиационный техникум им. А. Д. Швецова
				</a>
			</div>
		</div>
		<div class="space"> </div>
		<div class="main">
			<div class="content">
				<div class = "login">
					<div class="name">Авторизация</div>
				
					<div class = "sub-name">Логин:</div>
					<input name="_login" type="text" placeholder="" onkeypress="return PressToEnter(event)"/>
					<div class = "sub-name">Пароль:</div>
					<input name="_password" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>
					<br>
					<a href="regin.php">Регистрация</a>
					<br><a href="recovery.php">Забыли пароль?</a>
					<input type="button" class="button" value="Войти" onclick="LogIn()"/>
					<img src = "img/loading.gif" class="loading"/>
				</div>
				
				<div class="footer">
					© КГАПОУ "Авиатехникум", 2020
					<a href=#>Конфиденциальность</a>
					<a href=#>Условия</a>
				</div>
			</div>
		</div>
		
		<script>

			function getRecaptchaToken() {
				return new Promise((resolve) => {
					grecaptcha.ready(function() {
					grecaptcha.execute('6LenZC8sAAAAAA-tARe4G4CapjV9-RWyJFT1md_s', {action: 'login'}).then(function(token) {
						resolve(token);
					});
				});
			});
		}

			function LogIn() {
				var loading = document.getElementsByClassName("loading")[0];
				var button = document.getElementsByClassName("button")[0];
				
				var _login = document.getElementsByName("_login")[0].value.trim();
				var _password = document.getElementsByName("_password")[0].value;

				if (_login === "") {
					alert("Введите логин.");
					return;
				}
				if (_password === "") {
					alert("Введите пароль.");
					return;
				}

				loading.style.display = "block";
				button.className = "button_diactive";

				
				getRecaptchaToken().then(function(token_v3) {
					var data = new FormData();
					data.append("login", _login);
					data.append("password", _password);
					data.append("recaptcha_v3_token", token_v3);

					$.ajax({
						url         : 'ajax/login_user.php',
						type        : 'POST',
						data        : data,
						cache       : false,
						dataType    : 'html',
						processData : false,
						contentType : false, 
						success: function (_data) {
							if (_data.trim() === "" || _data.trim() === "-1") {
								alert("Логин или пароль неверный.");
							} else {
								localStorage.setItem("token", _data);
								location.reload();
							}
							loading.style.display = "none";
							button.className = "button";
						},
						error: function() {
							alert("Системная ошибка!");
							loading.style.display = "none";
							button.className = "button";
						}
					});
				});
			}
			
			function PressToEnter(e) {
				if (e.keyCode == 13) {
					LogIn();
				}
			}
		</script>
	</body>
</html>