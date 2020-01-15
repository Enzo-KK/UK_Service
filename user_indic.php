<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 13.03.19
 * Time: 14:01
 */
// Модуль подачи показаний

require_once 'mySecure.php';
require_once 'myClass.php';

//$act = '';
// сразу с сохранением
$act = 'php/save_lk.php';

// показания сохранены в сессию в сейв_лк

// пересохраняю в переменные из сессии
$holodnoe = array();
$gorachee = array();

$holodnoe[1] = isset($_SESSION['hol1']) ? $_SESSION['hol1'] : 0; // показания холодной воды
$holodnoe[2] = isset($_SESSION['hol2']) ? $_SESSION['hol2'] : 0; // показания холодной воды2
$holodnoe[3] = isset($_SESSION['hol3']) ? $_SESSION['hol3'] : 0; // показания холодной воды3
$gorachee[1] = isset($_SESSION['gor1']) ? $_SESSION['gor1'] : 0; // показания горячей воды
$gorachee[2] = isset($_SESSION['gor2']) ? $_SESSION['gor2'] : 0; // показания горячей воды2
$gorachee[3] = isset($_SESSION['gor3']) ? $_SESSION['gor3'] : 0; // показания горячей воды3

// коннектимся к бд
$link = connect_to_db ();
$fio = '';

// проверяем логин пароль
$chklp = new LogPass();

if ($chklp->lchet != '') {
    $lchet = htmlentities(mysqli_real_escape_string($link, $chklp->lchet));
    $pass = htmlentities(mysqli_real_escape_string($link, $chklp->pass));

    if ( $chklp->pass == '' ) {
        header('Location: index.php');
        exit;
    }

    $sql = mysqli_query($link, "SELECT person.name, person.commonarea, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat, person.sumtopay, person.debtprev, person.kod_ls, person.summ, pribori.kod, 
    pribori.name as name_us, pribori.ammount, pribori.edizm 
      FROM person, pribori WHERE person.kod_ls = pribori.kod_ls and person.name LIKE '$pass%' and person.kod_ls LIKE '$lchet' and 
      (pribori.name like 'Хол/в%' or pribori.name like 'Гор.вода%') order by pribori.name DESC");

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

    $fio = $result['name'];
    $city = $result['AddressCity'];
    $ulica = $result['AddressStreet'];
    $dom = $result['AddressHouse'];
    $kvart = $result['AddressFlat'];
    $plosh = $result['commonarea'];
    $koplate = $result['summ'];
    $dolg = $result['debtprev'] == 'NULL' ? 0 : $result['debtprev'];

    $holod = array();
    $hol_n = array();
    $gorach = array();
    $gor_n = array();
    $max_hol = array();
    $max_gor = array();
    $nomer_gor1 = 0;
    $kod = array();
//    $holod[] = $result['ammount'];

    // записываем показания из таблицы приборы
    if (strpos($result['name_us'],'Хол/в') !== false)  {
//        $holod[1] = isset($holodnoe[1]) ? $holodnoe[1] : $result['ammount'];
        $holod[1] = $holodnoe[1]!=0 ? $holodnoe[1] : $result['ammount'];
//        $holod[1] = $result['ammount'];
        $hol_n[1] = '';
// пока не нужна проверка максималки. кстати ругается на несовместимость данных.
//        и правильно. ammount поле типа varchar. надо тогда преобразовывать значение в число.
        //        $max_hol[1] = $holod[1]+50;
        $nomer_gor1 = 2;
        $kod[1] = $result['kod'];
        }

        $result = mysqli_fetch_array($sql);

    if (strpos($result['name_us'],'Гор.') !== false)  {
//        $gorach[1] = isset($gorachee[1]) ? $gorachee[1] : $result['ammount'];
        $gorach[1] = ($gorachee[1] != 0) ? $gorachee[1] : $result['ammount'];
        $gor_n[1] = '';
//        $max_gor[1] = $gorach[1] + 50;
        $kod[3] = $result['kod'];
    }

    ///////// проверка наличия множ. приборов
    /// сначала заменяем если есть null на нули, иначе потом гемор. потом выбираем
    $sql = mysqli_query($link,"UPDATE moreone SET indiccur1=0 WHERE indiccur1 IS NULL");

    $sql = mysqli_query($link, "SELECT kod, name, service, indiccur1
      FROM moreone WHERE kod_ls like '$lchet' order by service DESC");

    if (mysqli_num_rows($sql) != 0)
    { // обновляем данные на данные таблицы мореван
        $result = mysqli_fetch_array($sql);
        $i = 1;
        $j = 1;

//        while ($result['service'] == 'Хол/в, канализация') {
        while (strpos($result['service'],'Хол') !== false) {
//            $holod[$i] = isset($holodnoe[$i]) ? $holodnoe[$i] : $result['indiccur1'];
            $holod[$i] = $holodnoe[$i]!=0 ? $holodnoe[$i] : $result['indiccur1'];
//            $holod[$i] = isset($_SESSION['hol2']) ? $_SESSION['hol2'] : $result['indiccur1'];
            $hol_n[$i] = strpos($result['name'], 'ХВС');
            $hol_n[$i] = substr($result['name'], $hol_n[$i]);
//            $max_hol[$i] = $holod[$i]+50;
            $i++ ;
            $kod[$j] = $result['kod'];
            $j++;
            $result = mysqli_fetch_array($sql);
            # нумерация горячей воды в таблице
            $nomer_gor1 = $i+1;
        }

        $i = 1;
//        while ($result['name_us'] == 'Гор.вода') {
        while (strpos($result['service'],'Гор') !== false) {
//            $gorach[$i] = isset($gorachee[$i]) ? $gorachee[$i] : $result['indiccur1'];
            $gorach[$i] = ($gorachee[$i] != 0) ? $gorachee[$i] : $result['indiccur1'];
//            были поля с показаниями null. сначала выправлял здесь но решил - лучше заменой в запосе заранее
//            $gorach[$i] = ($gorachee[$i] != 0) ? $gorachee[$i] : is_null($result['indiccur1']) ? 0 : $result['indiccur1'];
            $gor_n[$i] = strpos($result['name'], 'ГВС');
            $gor_n[$i]  = substr($result['name'], $gor_n[$i]);
//            $max_gor[$i] = $gorach[$i]+50;
            $i++ ;
            $kod[$j] = $result['kod'];
            $j++;
            $result = mysqli_fetch_array($sql);
        }
///////
    }

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
    <!--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script><!--<div class="d-flex p-2 bd-highlight">Показания приборов учета</div>-->

</head>
<body>

<!-- бреками сдвигаем ленту ниже навбара-->
<?php if (isset($_SESSION['saved']) and $_SESSION['saved'] == 1): ?>
    <br>
    <br>
    <br>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Готово!</strong> Показания успешно отправлены.
    </div>

<?php else: ?>
    <div >
        <br>
        <br>
        <br>
    </div>
<?php endif; ?>

<nav class="navbar navbar-expand-md bg-dark navbar-dark fixed-top">
    <a class="navbar-brand" href="http://khozyain-doma.ru">УО ООО "Хозяин дома"</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="collapsibleNavbar">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="user_lc.php">Лицевой счет</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Подать показания</a>
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
<!--<br>-->
<!--<br>-->
<!--<br>-->

<div class="container-fluid">
    <h3>Личный кабинет</h3>
    <p>Управляющая компания "Хозяин дома"</p>

    <form id="anonymousAddressForm" method="POST" action="<?php echo "$act" ?>" name="OneForm" >

        <table class="table table-striped">
            <thead class="thead-dark">
            <tr>
                <th scope="col">Показания приборов учета</th>
                <th scope="col">Лицевой счет</th>
                <!--            <th scope="col">ФИО</th>-->
                <th scope="col">Общая площадь</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row"><?php echo "$fio" ?></th>
<!--                <th scope="row"><input name="fio" type="text" readonly value="--><?php //echo "$fio" ?><!--"> </th>-->
<!--                <td>--><?php //echo "$lchet" ?><!--</td>-->
                <td><input name="personal_account" type="text" size="8" readonly value="<?php echo "$lchet" ?>"> </td>
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


    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Наименование услуги</th>
            <th scope="col">Прибор учета</th>
            <th scope="col">Показания предыдущие</th>
            <th scope="col">Показания текущие</th>
            <th scope="col"> </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Холодное водоснабжение</td>
            <td> <input name="serial_number_hv1" type="text" size="17" readonly value="<?php echo "$hol_n[1]" ?>"> </td>
            <td> <?php echo "$holod[1]" ?></td>
            <td> <input name="counter_statement_hv1" type="text" size="4" min="<?php echo "$holod[1]" ?>" max="<?php echo "$max_hol[1]" ?>" value="<?php echo "$holod[1]" ?>"> </td>
            <td><input name="kod_hv1" type="text" hidden readonly value="<?php echo "$kod[1]" ?>"></td>
        </tr>
        <?php if (isset($holod[2])): ?>
            <tr>
                <td>Холодное водоснабжение</td>
                <td> <input name="serial_number_hv2" type="text" size="17" readonly value="<?php echo "$hol_n[2]" ?>"> </td>
                <td> <?php echo "$holod[2]" ?> </td>
                <td> <input name="counter_statement_hv2" type="text" size="4" min="<?php echo "$holod[2]" ?>" max="<?php echo "$max_hol[2]" ?>" value="<?php echo "$holod[2]" ?>"> </td>
                <td><input name="kod_hv2" type="text"  hidden readonly value="<?php echo "$kod[2]" ?>"></td>
            </tr>
        <?php endif; ?>
        <?php if (isset($holod[3])): ?>
            <tr>
                <td>Холодное водоснабжение</td>
                <td> <input name="serial_number_hv3" type="text" size="17" readonly value="<?php echo "$hol_n[3]" ?>"> </td>
                <td> <?php echo "$holod[3]" ?> </td>
                <td> <input name="counter_statement_hv3" type="text" size="4" min="<?php echo "$holod[3]" ?>" max="<?php echo "$max_hol[3]" ?>" value="<?php echo "$holod[3]" ?>"> </td>
                <td><input name="kod_hv3" type="text"  hidden readonly value="<?php echo "$kod[3]" ?>"></td>
            </tr>
        <?php endif; ?>
        <tr>
            <!--            <th scope="row">--><?php //echo "$nomer_gor1" ?><!--</th>-->
            <td>Горячее водоснабжение</td>
            <td> <input name="serial_number_gv1" type="text" size="17" readonly value="<?php echo "$gor_n[1]" ?>"> </td>
            <td> <?php echo "$gorach[1]" ?> </td>
            <td> <input name="counter_statement_gv1" type="text" size="4" min="<?php echo "$gorach[1]" ?>" max="<?php echo "$max_gor[1]" ?>" value="<?php echo "$gorach[1]" ?>"> </td>
            <td><input name="kod_gv1" type="text" hidden readonly value="<?php echo isset($kod[5]) ? $kod[4] : $kod[3] ?>"></td>
        </tr>
        <?php if (isset($gorach[2])): ?>
            <tr>
                <td>Горячее водоснабжение</td>
                <td> <input name="serial_number_gv2" type="text" size="17" readonly value="<?php echo "$gor_n[2]" ?>"> </td>
                <td> <?php echo "$gorach[2]" ?> </td>
                <td> <input name="counter_statement_gv2" type="text" size="4" min="<?php echo "$gorach[2]" ?>" max="<?php echo "$max_gor[2]" ?>" value="<?php echo "$gorach[2]" ?>"> </td>
                <td><input name="kod_gv2" type="text" hidden readonly value="<?php echo  isset($kod[5]) ? $kod[5] : $kod[4] ?>"></td>
            </tr>
        <?php endif; ?>
        <?php if (isset($gorach[3])): ?>
            <tr>
                <td>Горячее водоснабжение</td>
                <td> <input name="serial_number_gv3" type="text" size="17" readonly value="<?php echo "$gor_n[3]" ?>"> </td>
                <td> <?php echo "$gorach[3]" ?> </td>
                <td> <input name="counter_statement_gv3" type="text" size="4" min="<?php echo "$gorach[3]" ?>" max="<?php echo "$max_gor[3]" ?>" value="<?php echo "$gorach[3]" ?>"> </td>
                <td><input name="kod_gv3" type="text" hidden readonly value="<?php echo "$kod[6]" ?>"></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <a class="btn btn-secondary" href="/" role="button" title="Вернуться ко входу в личный кабинет">Вернуться</a>
    <input type="submit" class="btn btn-primary" name="sbm_data" value="Отправить показания" title="Отправить обновленные показания в УК">
</form>

<br>

<table class="table table-striped">
    <thead>
    <tr>
        <th scope="col"></th>
        <th scope="col">К оплате за расчетный период</th>
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

<p>* Обратите внимание: <br>Показания, которые отображены в этом окне, являются учтенными на текущий момент и не будут изменены сразу после ввода новых значений.<br> Обновление показаний на новые произойдет в следующем месяце, после их обработки в УО.</p>

</div>

<?php
// сброс алерта
if (isset($_SESSION['saved']) && $_SESSION['saved'] = 1) $_SESSION['saved'] = 0;
?>
</body>
</html>

