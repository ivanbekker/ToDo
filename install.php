<?php
/*
* Назначение: Установка web-приложения Simple TODO Lists
* */

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

$url=parse_url(getenv("CLEARDB_DATABASE_URL"));
$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$dbname = substr($url["path"],1);

$mysqli = new mysqli($server, $username, $password, $dbname);

if ($mysqli->connect_errno) {
    msgbox("Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

foreach ($tableSchema as $table) {
    $query = $mysqli->query($table);
    if (!$query) {
        msgbox("Не удалось создать таблицу: (" . $mysqli->errno . ") " . $mysqli->error);
    }
}

@unlink('install.php');
header("Location: index.php");
die();


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
