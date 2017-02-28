<?php
/*
 * Назначение: Установка web-приложения Simple TODO Lists
 * */

$skin_header = '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Установка Simple TODO Lists</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <style type="text/css">
        body {
            background: url("/images/background.jpg") no-repeat center fixed;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            position: relative;
        }
        .container {
            max-width: 730px;
            min-width: 730px;
        }
        .header {
            padding-top: 60px;
            padding-bottom: 60px;
        }
        .header h2 {
            margin-top: 0;
            margin-bottom: 0;
            text-shadow: #fff 0px 1px 0, #000 0 -1px 0;
        }

        .header h4 {
            margin-top: 0;
            margin-bottom: 0;
            text-shadow: #fff 0px 1px 0, #000 0 -1px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header clearfix text-center">
            <h2><strong>SIMPLE TODO LISTS</strong></h2>
            <h4 class="text-muted">FROM RUBY GARAGE</h4>
        </div>
        <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Мастер установки скрипта</h3>
                </div>
                <div class="panel-body">
                    <p>Данные для доступа к MySQL-серверу</p>';

$skin_footer = '</div>
        </div>
    </div>
</body>
</html>';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST["host"]) and !empty($_POST["dbname"]) and !empty($_POST["username"]) and !empty($_POST["password"])) {
        $host = htmlspecialchars($_POST["host"]);
        $dbname = trim($_POST["dbname"]);
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        $dbconfig = "<?php
                return [
                    'host' => '$host',
                    'dbname' => '$dbname',        // Database name
                    'username' => '$username',           // Database username
                    'password' => '$password',           // Database password
                ];
            ";

        /* Создаем и записываем файл конфига */
        $con_file = fopen("config.php", "wb") or die("Извините, но невозможно создать файл <b>.config.php</b>.<br />Проверьте правильность проставленного CHMOD!");
        fwrite($con_file, $dbconfig);
        fclose($con_file);
        @chmod("config.php", 0666);

        /* Запросы на создание таблиц */
        $tableSchema = array();

        $tableSchema[] = "DROP TABLE IF EXISTS tasks;";
        $tableSchema[] = "DROP TABLE IF EXISTS projects;";
        $tableSchema[] = "CREATE TABLE projects (
                                          id int(11) NOT NULL auto_increment,
                                          name varchar(255) NOT NULL DEFAULT '',
                                          PRIMARY KEY (id),
                                          UNIQUE KEY name (name)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $tableSchema[] = "CREATE TABLE tasks (
                                          id int(11) NOT NULL auto_increment,
                                          project_id int(11) NOT NULL DEFAULT '0',
                                          date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                                          description text NOT NULL,
                                          priority int(11) NOT NULL DEFAULT '0',
                                          status BOOLEAN NOT NULL DEFAULT false,
                                          PRIMARY KEY (id),
                                          FOREIGN KEY (project_id)
                                              REFERENCES projects(id)
                                              ON DELETE CASCADE
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $mysqli = new mysqli($host, $username, $password, $dbname);
        if ($mysqli->connect_errno) {
            @unlink('config.php');
            msgbox("Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
        }

        foreach ($tableSchema as $table) {
            $query = $mysqli->query($table);
            if (!$query) {
                @unlink('config.php');
                msgbox("Не удалось создать таблицу: (" . $mysqli->errno . ") " . $mysqli->error);
            }
        }

        @unlink('install.php');
        header("Location: index.php");
        die();
    } else {
        msgbox('Заполните все поля!');
    }
}


function msgbox($error)
{
    global $skin_header, $skin_footer;
    echo $skin_header;
    echo '<div class="alert alert-danger" role="alert">' . $error . '</div>
            <form method="post" action="install.php" class="form-horizontal">
                <div class="form-group">
                    <label for="inputMySQL" class="col-sm-4 control-label">Сервер MySQL:</label>
                    <div class="col-sm-8">
                        <input type="text" name="host" class="form-control" id="inputMySQL" placeholder="Сервер MySQL" value="localhost">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputDbname" class="col-sm-4 control-label">Имя базы данных:</label>
                    <div class="col-sm-8">
                        <input type="text" name="dbname" class="form-control" id="inputDbname" placeholder="Имя базы данных">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputUsername" class="col-sm-4 control-label">Имя пользователя:</label>
                    <div class="col-sm-8">
                        <input type="text" name="username" class="form-control" id="inputUsername" placeholder="Имя пользователя">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputPassword" class="col-sm-4 control-label">Пароль:</label>
                    <div class="col-sm-8">
                        <input type="text" name="password" class="form-control" id="inputPassword" placeholder="Пароль">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary pull-right">Установить</button>
            </form>';
    echo $skin_footer;
    exit();
}


// ******************************
//  Вывод главной страницы - форма
// ******************************
echo $skin_header;
echo '<form method="post" action="install.php" class="form-horizontal">
        <div class="form-group">
            <label for="inputMySQL" class="col-sm-4 control-label">Сервер MySQL:</label>
            <div class="col-sm-8">
                <input type="text" name="host" class="form-control" id="inputMySQL" placeholder="Сервер MySQL" value="localhost">
            </div>
        </div>
        <div class="form-group">
            <label for="inputDbname" class="col-sm-4 control-label">Имя базы данных:</label>
            <div class="col-sm-8">
                <input type="text" name="dbname" class="form-control" id="inputDbname" placeholder="Имя базы данных">
            </div>
        </div>
        <div class="form-group">
            <label for="inputUsername" class="col-sm-4 control-label">Имя пользователя:</label>
            <div class="col-sm-8">
                <input type="text" name="username" class="form-control" id="inputUsername" placeholder="Имя пользователя">
            </div>
        </div>
        <div class="form-group">
            <label for="inputPassword" class="col-sm-4 control-label">Пароль:</label>
            <div class="col-sm-8">
                <input type="text" name="password" class="form-control" id="inputPassword" placeholder="Пароль">
            </div>
        </div>

        <button type="submit" class="btn btn-primary pull-right">Установить</button>
    </form>';
echo $skin_footer;