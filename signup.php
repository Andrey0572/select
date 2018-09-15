<?php
require_once __DIR__ . '/functions.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth">
    <form method="post">
        <p>Регистрация</p>
        <p>Введите логин: <input type="text" name="login" value ="<? echo $_POST['login'] ?>"></p>
        <p>Введите пароль: <input type="password" name="password"></p>
        <p><button type="submit">Отправить</button></p>
    </form>  
</div>
</body>
</html>