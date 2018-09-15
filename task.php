<?php
require_once __DIR__ . '/functions.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>task</title>
</head>
<body>
    <h1>Привет <?php echo $_SESSION['login_user']['login']; ?></h1>
    <h2>Колличество текущих дел: <?php echo countTask($_SESSION['login_user']['id'], $pdo); ?></h2>
    <a href="logout.php">Выйти</a>
    <form action="task.php" method="get">
        <input type="text" placeholder="Описание задачи" name="task">
        <button type="submit" name="add" value="1">Добавить</button>
    </form>
    <table>
            <thead>
                <tr>
                    <th>id</th>
                    <th>Описание задачи</th>
                    <th>Дата добавления</th>
                    <th>Автор</th>
                    <th>Ответственный</th>
                    <th>Статус</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $list = listTask($_SESSION['login_user']['id'], $pdo);
                foreach ($list as $row) {
                    tableMassif($row, $db);
                }
                ?>
            </tbody>
    </table>
</body>
</html>