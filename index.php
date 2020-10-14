<?php
    ini_set('display_errors',1);
    error_reporting(E_ALL);
    session_start();
    session_destroy();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Constantin Krayushkin">
<!--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">

    <script>
        function ValChange(ent) {
            var entered = ent.value;
            // if (typeof parseInt(entered) !== "number")
                if (isNaN(entered))
                document.getElementById("pass_tp").type = "password";
            else
                document.getElementById("pass_tp").type = "text";
        }
    </script>

</head>
<body>
<div class="container-fluid">
    <h3>Вход в личный кабинет</h3>
    <p>Управляющая компания "Хозяин дома"</p>
<form action="user_lc.php" method="post" >
    <hr>
    <div class="form-group">
    <label for="log">Лицевой счет:</label>
    <input id="log"  class="form-control w-50" name="login" type="text" value="" onchange="ValChange(this)">
    </div>
<!--    <hr>-->
    <div class="form-group">
    <label>Пароль:</label>
<!--        <input class="form-control w-50"  name="password" type="--><?php //echo $gettype?><!--" >-->
        <input id="pass_tp" class="form-control w-50"  name="password" type="text" >
    </div>
    <a class="btn btn-secondary" href="http://khozyain-doma.ru" role="button" title="Вернуться на главную страницу">Вернуться</a>
    <button type="submit" class="btn btn-info" title="Перейти в личный кабинет">Войти</button>
</form>
</div>
</body>
</html>


