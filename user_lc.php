<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 13.03.19
 * Time: 14:01
 */
// Информация о лицевом счете пользователя


require_once 'mySecure.php';
require_once 'myClass.php';

// коннектимся к бд
$link = connect_to_db ();

// проверяем логин пароль
$chklp = new LogPass();

if ($chklp->lchet != '') {
    $lchet = htmlentities(mysqli_real_escape_string($link, $chklp->lchet));
    $pass = htmlentities(mysqli_real_escape_string($link, $chklp->pass));

    if ( $chklp->pass == '' ) {
        header('Location: index.php');
        exit;
    }

    /// проверяем не админ ли ломится
    $chklp->chkAdm();

    $sql = mysqli_query($link, "SELECT * FROM date_bd ");

    $row = mysqli_fetch_array($sql);
    $date_bd = $row['filedate'];
    // сохраним чтоб использовать в других вкладках
    $_SESSION['date_bd'] = $date_bd;

    $sql = mysqli_query($link, "SELECT person.name, person.commonarea, person.AddressCity, 
      person.AddressStreet, person.AddressHouse, person.AddressFlat, person.sumtopay, person.debtprev, person.kod_ls, person.summ, 
      pribori.name as name_us, pribori.ammount, pribori.edizm, pribori.sum 
      FROM person, pribori WHERE person.kod_ls = pribori.kod_ls and person.name LIKE '$pass%' and person.kod_ls LIKE '$lchet' ");

    if (mysqli_num_rows($sql) == 0)
    {
        header('Location: index.php');
        exit;
    }

    //    записываем в сессию логин и пароль чтоб не вываливаться
    $chklp->writeSess();

    /////
    $pokazania = array();

    // считываем первую запись
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

    $pokazania[] = $row;
// читаем остальные
    while($row = mysqli_fetch_array($sql))
    {
          $pokazania[] = $row;
    }
    ///////////////////
    ///  проверяем наличие активного опроса
$sql = mysqli_query($link, "SELECT * from opros where publ_op like 1");
$iop = mysqli_num_rows($sql);
// пока ставлю заглушку, а то клиенты разволнуются
//    $iop = 0;
//
if ($iop > 0) {
//        метка активного опроса сохраняется в сессию
//    $isact = $iop;
    $_SESSION['isactopr'] = 1;
}
// если нет активного опроса то сбрасываем метку
elseif (isset($_SESSION['isactopr']))  $_SESSION['isactopr'] = 0;
}
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
</head>
<body>

<nav class="navbar navbar-expand-md bg-dark navbar-dark fixed-top">
<!--<nav class="navbar navbar-expand-md bg-dark navbar-dark ">-->
    <a class="navbar-brand" href="http://khozyain-doma.ru">УО ООО "Хозяин дома"</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="collapsibleNavbar">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link active" href="#">Лицевой счет</a>
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
                <a class="nav-link" href="user_opr.php">Опрос</a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<br>
<br>
<br>

<div class="container-fluid">
    <h3>Личный кабинет</h3>
    <p>Управляющая компания "Хозяин дома"</p>
    <?php if (isset($_SESSION['isactopr'])  && $_SESSION['isactopr'] == 1): ?>
    <p><a href="user_opr.php">Предлагаем принять участие в опросе!</a></p>
    <?php endif; ?>
<!--    <ul class="pagination">-->
<!--        <li class="page-item active"><a class="page-link" href="#">Лицевой счет</a></li>-->
<!--        <li class="page-item"><a class="page-link" href="data_indic.php">Подать показания</a></li>-->
<!--        <li class="page-item"><a class="page-link" href="data_zayav.php">Подать заявку</a></li>-->
<!--    </ul>-->
    <table class="table table-striped">
        <thead class="thead-dark">
        <tr>
            <th scope="col">Сведения о получателе услуг</th>
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
            <td><?php echo "$date_bd" ?></td>
            <td> </td>
            <td> </td>
        </tr>

        </tbody>
    </table>

    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Вид услуги</th>
            <th scope="col">Объем</th>
            <th scope="col">Ед. измерения</th>
            <th scope="col">К оплате за месяц</th>
        </tr>
        </thead>
        <tbody>
        <?php

        // вывод показаний
        for ($i = 0; $i < count($pokazania); $i++)
        {
            print "<tr>
        <th scope=\"row\">".$pokazania[$i]['name_us']."</th>
        <td>".$pokazania[$i]['ammount']."</td>
        <td>".$pokazania[$i]['edizm']."</td>
        <td>".$pokazania[$i]['sum']."</td>
        </tr>";
        }
        ?>

        </tbody>
    </table>
    <a class="btn btn-secondary" href="/" role="button" title="Вернуться ко входу в личный кабинет">Вернуться</a>
    <a class="btn btn-success" href="#" role="button" title="Оплатить услуги">Оплатить</a>

    <p></p>

<table class="table table-striped">
    <thead>
    <tr>
        <th scope="col"></th>
        <th scope="col">Итого к оплате за расчетный период</th>
        <th scope="col"><?php echo "$koplate_mes" ?></th>
        <th scope="col">рублей</th>
    </tr>
    <tr>
        <th scope="col"></th>
        <th scope="col">К оплате всего</th>
        <th scope="col"><?php echo "$koplate" ?></th>
        <th scope="col">рублей</th>
    </tr>
    <tr>
        <th scope="col"></th>
        <th scope="col">Задолженность за предыдущий период</th>
        <th scope="col"><?php echo "$dolg" ?></th>
        <th scope="col">рублей</th>
    </tr>
    </thead>
</table>

<p>* Обратите внимание: <br>Отображено состояние лицевого счета на <?php echo "$date_bd" ?> г.</p>

</div>
</body>
</html>

