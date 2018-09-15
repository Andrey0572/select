<?php
session_start();

 // подключение к БД
    $host = 'localhost';
    $dbname = 'select';
    $dbuser = 'admin';
    $dbpassword = '123';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            echo "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
 // логинимся
if (!empty($_POST)) {
    $errors = array();
    $sql = "SELECT * FROM user WHERE `login` = '{$_POST['login']}'";
    $result = $pdo->prepare($sql);
    $result->execute();
    $loginArr = $result->fetchALL(PDO::FETCH_ASSOC);

    if (!empty($loginArr)) {
        if (password_verify($_POST['password'], $loginArr['0']['password'])) {
            $id = $loginArr['0']['id'];
            $_SESSION['login_user'] = $loginArr['0'];
            header('LOCATION: task.php');
            die;
        }
        $errors[] = 'Пароль введен не верно!';
    } else {
        $errors[] = 'Пользователь не найден!';
    }
    if (!empty($errors)) {
        echo '<div style="color: red; text-align: center;">' . array_shift($errors) . '</div><hr>';
    }
}

// переход к задачам
if (isset($_SESSION['login_user'])) {
    header('location: task.php');
    die;
}

// проверить логин на уникальность
function uniqueLogin($login, $pdo)
{
    $sql = "SELECT id FROM user WHERE `login` = '$login'";
    $result = $pdo->prepare($sql);
    $result->execute();
    if ($result->fetchALL(PDO::FETCH_ASSOC)) {
        return true;
    } else {
        return false;
    }
}

// внесение пользователя в БД
function userData($login, $password, $pdo)
{
    $password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO user(login, password) VALUE ('$login', '$password')";
    $result = $pdo->prepare($sql);
    $result->execute();
}

// Проверяем введеные данные в форму или она пустая
if (!empty($_POST)) {
    $errors = array();

//Проверка на не пустой логин
    if (trim($_POST['login']) == '') {
        $errors[] = 'Введите логин!';
    } 

//Проверка на не пустой пароль
    if ($_POST['password'] == '') {
        $errors[] = 'Введите пароль!';
    } 

//Проверка логина на уникальность
    if (uniqueLogin($_POST['login'], $pdo)) {
        $errors[] = 'Пользователь с таким логином уже существует';
    }

// нет ошибок добавляем в БД пользователя.
    if (empty($errors)) {
        userData($_POST['login'], $_POST['password'], $pdo);
        header('location: login.php');
        die;
    } else {
        echo '<div style="color: red; text-align: center;">' . array_shift($errors) . '</div><hr>';
    }
}

/*if (empty($_SESSION['login_user'])) {
    http_response_code(403);
    die;
} else {  */  
 function logout(){
    if (isset($_SESSION['login_user'])) {
        session_destroy();
        return ["location" => "login.php"];
    } else {
        http_response_code(403);
    }
/* }*/

//  изменить задачу
 function changeTask($condition, $goalId, $user_id,$pdo){
    $sql = "UPDATE task SET is_done = $condition WHERE `id` = $goalId AND `assigned_user_id` = $user_id LIMIT 1";
    $result = $pdo->prepare($sql);
    $result->execute();
 }

 // изменить задачу у пользователя
function changeDetermineUserNamme($task_user, $goalId, $pdo)
{
    $sql = "UPDATE task SET assigned_user_id = $task_user WHERE id = $goalId";
    $result = $pdo->prepare($sql);
    $result->execute();
}


function idName($id, $pdo)
{
    $sql = "SELECT user.login FROM user  WHERE id = $id";
    $result = $pdo->prepare($sql);
    $result->execute();
    $name = $result->fetchALL(PDO::FETCH_ASSOC);
    $name = $name['0']['login'];
    return $name;
}

//счечик заданий
function countTask($id, $pdo)
{
    $sql = "SELECT count(*) FROM task t WHERE t.user_id = $id OR t.assigned_user_id = $id";
    $result = $db->prepare($sql);
    $result->execute();
    $count = $result->fetchALL(PDO::FETCH_ASSOC);
    $count = $count['0']['count(*)'];
    return $count;
}

//  списка заданий
function listTask($id, $pdo)
{
    $sql = "SELECT t.id, t.description, t.date_added, t.user_id, t.assigned_user_id, t.is_done 
        FROM task t INNER JOIN user u ON u.id = t.user_id WHERE
        t.user_id = $id OR t.assigned_user_id = $id ORDER BY `date_added`";
    $result = $pdo->prepare($sql);
    $result->execute();
    return $result->fetchALL(PDO::FETCH_ASSOC);
}

function tableMassif($row, $pdo)
{
    $condition = ($row['is_done'] == 1) ? 'Выполнено' : 'Невыполненно'; ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td><?php echo $row['date_added']; ?></td>
            <td><?php echo idName($row['user_id'], $pdo); ?></td>
            <td>        
                <form action="task.php" method="post">
                    <select name="task_user">
                        <option value="<? echo $row['assigned_user_id'] ?>"><?php echo idName($row['assigned_user_id'], $pdo); ?></option>
                        <?php
                        $sql = "SELECT u.id, u.login FROM user u WHERE NOT u.id = '{$row['assigned_user_id']}'";
                        $result = $pdo->prepare($sql);
                        $result->execute();
                        $userMas = $result->fetchALL(PDO::FETCH_ASSOC);
                        foreach ($userMas as $user) { ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo $user['login']; ?></option>
                            <?php 
                        } ?>    
                    </select>
                    <button type="submit" name="assign" value="<? echo $row['id'] ?>">Делегировать</button>
                </form>
            
            </td>
            <td><?php echo $condition; ?></td>
            <td><?php if ($row['is_done'] == 1) { ?> <a href="task.php?condition=0&task=<? echo $row['id'] ?>&user_id=<? echo $_SESSION['login_user']['id'] ?>">Возобновить</a> <?php 
                                                 } elseif ($row['is_done'] == 0) { ?> <a href="task.php?state=1&task=<? echo $row['id'] ?>&user_id=<? echo $_SESSION['login_user']['id'] ?>">Выполнить</a> <?php 
                                                 } ?></td>
            <td><a href="task.php?del=<?php echo $row['id'] ?>">Удалить</a></td>
        </tr>
    <?php 
}

//добавить задания 
function addTask($task, $id, $pdo)
{
    $date = date('Y-m-d h:m:s');
    echo $date;
    $sql = "INSERT INTO task(description, date_added, user_id, assigned_user_id, is_done) 
        VALUE ('$task', '$date', '$id', '$id', '0')";
    $result = $pdo->prepare($sql);
    $result->execute();
}

// удаление заданий
function deleteTask($id, $idTask, $pdo)
{
    $sql = "DELETE FROM task WHERE user_id='$id' AND id='$idTask' LIMIT 1";
    $result = $pdo->prepare($sql);
    $result->execute();
}
}

/*if (empty($_SESSION['login_user'])) {
    http_response_code(403);
    die;
} else {*/
    if (!empty($_GET || !empty($_POST))) {
        if ($_GET['add'] == 1) {
            addTask($_GET['task'], $_SESSION['login_user']['id'], $pdo);
        }
        if (!empty($_GET['del'])) {
            deleteTask($_SESSION['login_user']['id'], $_GET['del'], $pdo);
        }
        if ($_POST['assign'] > 0) {
            changeDetermineUserNamme($_POST['task_user'], $_POST['assign'], $pdo);
        }
        if (isset($_GET['condition'])) {
            changeTask($_GET['condition'], $_GET['task'], $_GET['user_id'], $db);
        }
    }
    require 'task.php';
/*} */
?>