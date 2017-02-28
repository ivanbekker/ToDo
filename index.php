<?php
if (file_exists('config.php')) {
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Simple TODO Lists</title>

    <script type="text/javascript" src="js/jquery-1.11.2.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="js/jquery.blockUI.js"></script>
    <script type="text/javascript" src="js/jquery-todo.js"></script>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/jquery-ui.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <script type="text/javascript">
        $(document).ready(function () {
            $("#todo-lists").TODO({
                add_project_btn_name: "Add TODO List" // Надпись на  кнопке добавления проекта
            });
        });
    </script>
</head>

<body>

<div class="container">
    <div class="header clearfix text-center">
        <h2><strong>SIMPLE TODO LISTS</strong></h2>
        <h4 class="text-muted">FROM RUBY GARAGE</h4>
    </div>

    <div id="todo-lists"></div>

    <footer class="footer">
        <p class="copyright text-center"><span class="fa fa-copyright"></span> Ruby Garage</p>
    </footer>
</div>

</body>
</html>';

} else {
    header("Location: install.php");
    die();
}