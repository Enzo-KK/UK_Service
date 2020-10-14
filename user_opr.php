<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 13.03.19
 * Time: 14:01
 */


require_once 'mySecure.php';
require_once 'myClass.php';

// коннектимся к бд
$link = connect_to_db ();

$act = '';
$fio = '';
$adres = '';
$mes = '';
$god = '';

// проверяем логин пароль
$chklp = new LogPass();


if ($chklp->lchet != '') {
    $lchet = htmlentities(mysqli_real_escape_string($link, $chklp->lchet));
    $pass = htmlentities(mysqli_real_escape_string($link, $chklp->pass));

    if ( $chklp->pass == '' ) {
//        echo  "<script>alert(\"пароль пустой!\");</script>";
        header('Location: index.php');
        exit;
    }

    $sql = mysqli_query($link, "SELECT person.name, person.commonarea, person.AddressCity, 
      person.AddressStreet, person.AddressHouse, person.AddressFlat, person.sumtopay, person.debtprev, person.kod_ls, person.summ, 
      pribori.name as name_us, pribori.ammount, pribori.edizm, pribori.sum 
      FROM person, pribori WHERE person.kod_ls = pribori.kod_ls and person.name LIKE '$pass%' and person.kod_ls LIKE '$lchet' ");

    if (mysqli_num_rows($sql) == 0)
    {
        header('Location: index.php');
        exit;
    }
    $result = mysqli_fetch_array($sql);

    if (!isset($_SESSION['lchet'])) {
        $_SESSION['lchet'] = $lchet;
        $_SESSION['user'] = $pass;
    }
/////
    // считываем статистические данные
    $row = mysqli_fetch_array($sql);
    $fio = $row['name'];
//    $adres = $row['Адрес'];
    $city = $row['AddressCity'];
    $ulica = $row['AddressStreet'];
    $dom = $row['AddressHouse'];
    $kvart = $row['AddressFlat'];
    $plosh = $row['commonarea'];
//    $mes = $row['Месяц'];
//    $god = $row['Год'];
//    $koplate_mes = $row['sumtopay']-$row['debtprev'] ;
    $koplate_mes = $row['summ'];
    $koplate = $row['sumtopay'];
    $dolg = $row['debtprev'] == 'NULL' ? 0 : $row['debtprev'];

    ///////////////////
    ///  проверяем наличие активного опроса
$sql = mysqli_query($link, "SELECT * from opros where publ_op like 1");
$iop = mysqli_num_rows($sql);
if ($iop > 0) {
    $row_op = mysqli_fetch_array($sql);
    $id_ak = $row_op['id'];
    $dt_ak = $row_op['date_op'];
    $nm_ak = $row_op['name_op'];
    $tx_ak = $row_op['text_op'];
    $an1_ak = $row_op['ans_1'];
    $an2_ak = $row_op['ans_2'];
    $an3_ak = $row_op['ans_3'];
    $an4_ak = $row_op['ans_4'];
    $an5_ak = $row_op['ans_5'];
//        метка активного опроса сохраняется в сессию
//    отключил метки на время обкатки
//    $_SESSION['isactopr'] = 1;
}
//elseif (isset($_SESSION['isactopr']))  $_SESSION['isactopr'] = 0;

//    проверяем не ответил ли чел уже на опрос
    $my_answ = '';
    $answ_snd = false;
    $sql = mysqli_query($link, "SELECT * from opros_answ where lchet like '$lchet' and id like '$id_ak' ");
    $iop = mysqli_num_rows($sql);
    if ($iop > 0) {
        $answ_snd = true;
//        получаем сохраненный ответ для подстановки в форму
        $row_op = mysqli_fetch_array($sql);
        $my_answ = $row_op['answ'];
//        ответы сопоставил прямо в инпутах
//        switch ($my_answ){
//            case
//        }
    }
//    обработка сохранения результата опроса
//    функция на нажатие сохранить
    $setfunc = '';
    if (isset($_POST['sbm_data'])) {  // нажата кнопка отправить
        if ($answ_snd) {
//     ответ уже был  получен
            $setfunc = 'nzz()';
        }
        else {
//    записвываем ответ в бд
            $dat_op = date("Y-m-d");
            $sql = mysqli_query($link, "insert into opros_answ set id='$id_ak', answ='{$_POST['chk_answ']}', lchet='$lchet', date_op=CONVERT('$dat_op', date) ");
// делаем метку отправки голоса. алерт - ответ принят (в хеде)
            if ($sql) $_SESSION['answ_send'] = 1;

        }
    }
}
//        очищаем переменные пост редиректом
// была нажата кнопка сохранить
//if (isset($_POST['sbm_data'])) header("Location: " . $_SERVER['REQUEST_URI']);

else{
    header('Location: index.php');
    exit;
}

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script><!--<div class="d-flex p-2 bd-highlight">Показания приборов учета</div>-->

    <?php
//    if ($sql) echo "<meta http-equiv='refresh' content='0'>";
    ?>
</head>
<body>
<script>
    function nzz() {
        alert('Вы уже ответили на этот опрос!');
    }
</script>

<?php
// чо то не хочет алерт ниже менюшки двигаться падла
if (isset($_SESSION['answ_send']) and $_SESSION['answ_send'] == 1) {
    print ("<br><br><br><div class=\"alert alert-success alert-dismissible\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                <strong>Спасибо!</strong> Ваш голос принят.
                </div>");
}
?>

<nav class="navbar navbar-expand-md bg-dark navbar-dark fixed-top">
    <a class="navbar-brand" href="http://khozyain-doma.ru">УО ООО "Хозяин дома"</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="collapsibleNavbar">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link " href="user_lc.php">Лицевой счет</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="user_indic.php">Подать показания</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="user_zayav.php">Сделать заявку</a>
            </li>
<!--            если есть активный опрос добавляем пункт -->
            <?php if (isset($_SESSION['isactopr'])  && $_SESSION['isactopr'] == 1): ?>
            <li class="nav-item">
                <a class="nav-link active" href="#">Опрос</a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<?php
// отключаем бреки при алерте
if (!isset($_SESSION['answ_send']) || $_SESSION['answ_send'] == 0) print "<br><br><br>";
//       и сбрасываем метку алерта
$_SESSION['answ_send'] = 0;
?>
<div class="container-fluid">
    <h3>Личный кабинет</h3>
    <p>Управляющая компания "Хозяин дома"</p>
<!--    <ul class="pagination">-->
<!--        <li class="page-item active"><a class="page-link" href="#">Лицевой счет</a></li>-->
<!--        <li class="page-item"><a class="page-link" href="data_indic.php">Подать показания</a></li>-->
<!--        <li class="page-item"><a class="page-link" href="data_zayav.php">Подать заявку</a></li>-->
<!--    </ul>-->
    <table class="table table-striped">
        <thead class="thead-dark">
        <tr>
            <th scope="col">Сведения о собственнике</th>
            <th scope="col">Лицевой счет</th>
            <!--            <th scope="col">ФИО</th>-->
            <th scope="col">Общая площадь</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row"><?php echo "$fio" ?></th>
            <td><?php echo "$lchet" ?></td>
            <td><?php echo "$plosh" ?></td>
            <td>кв.м</td>
        </tr>
        <tr>
            <th scope="row">Адрес</th>
            <td><?php echo "$ulica" ?></td>
            <td><?php echo "$dom" ?></td>
            <td><?php echo "$kvart" ?></td>
        </tr>
        <tr>
            <td>Дата учета данных</td>
            <td><?php echo "$chklp->date_bd" ?></td>
            <td> </td>
            <td> </td>
        </tr>

        </tbody>
    </table>

    <form id="otvet" method="POST" action="" name="otvet">
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Просим принять участие в опросе:</th>
            <th scope="col"><?php echo "$nm_ak" ?></th>
            <th scope="col"></th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope=\"row\"></th>
            <td><?php echo "$tx_ak" ?></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th scope=\"row\">Варианты ответа</th>
            <td><input type="radio" <?php if ($my_answ == $an1_ak) echo "checked" ?> required name="chk_answ" value="<?php echo "$an1_ak" ?>"> <?php echo "$an1_ak" ?></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th scope=\"row\"> </th>
            <td><input type="radio" <?php if ($my_answ == $an2_ak) echo "checked" ?> required name="chk_answ" value="<?php echo "$an2_ak" ?>"> <?php echo "$an2_ak" ?></td>
            <td></td>
            <td></td>
        </tr>
        <?php

        // еще варианты ответов опроса, если есть
        if (iconv_strlen($an3_ak >= 2))
        {
            if ($my_answ == $an3_ak) $chk3 = "checked" ;
            print "<tr>
        <th scope=\"row\"></th>
        <td>$an3_ak</td>
        <td><input type=\"radio\" $chk3 required name=\"chk_answ\" value=$an3_ak></td>
        <td></td>
        </tr>";
            if (iconv_strlen($an4_ak >= 2)) {
                if ($my_answ == $an4_ak) $chk4 = "checked" ;
            print "<tr>
        <th scope=\"row\"></th>
        <td>$an4_ak</td>
        <td><input type=\"radio\" $chk4 required name=\"chk_answ\" value=$an4_ak></td>
        <td></td>
        </tr>";
        }
            if (iconv_strlen($an5_ak >= 2)) {
                if ($my_answ == $an5_ak) $chk5 = "checked" ;
                print "<tr>
        <th scope=\"row\"></th>
        <td>$an5_ak</td>
        <td><input type=\"radio\" $chk5 required name=\"chk_answ\" value=$an5_ak></td>
        <td></td>
        </tr>";
            }

        }
        ?>

        </tbody>
    </table>
    <a class="btn btn-secondary" href="/" role="button" title="Вернуться ко входу в личный кабинет">Вернуться</a>
<!--    <a class="btn btn-success" href="#" role="button" title="Отправить ответ">Отправить</a>-->
    <input type="submit" class="btn btn-primary" name="sbm_data" onclick="<?php echo "$setfunc" ?>"  value="Отправить" title="Отправить ответ">
    </form>

    <p></p>

<table class="table table-striped">
    <thead>
    <tr>
        <th scope="col"></th>
        <th scope="col">У вас имеется задолженность за предыдущий период</th>
        <th scope="col"><?php echo "$dolg" ?></th>
        <th scope="col">рублей</th>
    </tr>
    </thead>
</table>

<p>* Дата публикации опроса: <?php $dt_ak = date("d.m.Y", strtotime($dt_ak)); echo "$dt_ak"; ?> г.</p>

</div>
</body>
</html>

