<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 30.01.19
 * Time: 14:01
 */
// Модуль работы с заявками

require_once 'mySecure.php';

// при доступе админа есть метка сессии
if (!isset($_SESSION['adm_on']) || $_SESSION['adm_on']!=1) {
    header('Location: index.php');
}
// сбрасываем номер заявки
$_SESSION['n_zay'] = 0;

// коннектимся к бд
$link = connect_to_db ();

///
//$sql = mysqli_query($link, "SELECT * from admin_db");
$sql = mysqli_query($link, "SELECT * from date_bd");
$result = mysqli_fetch_array($sql);
//$date_bd = $result['date_bd'];
$date_bd = $result['filedate'];
//$persons = $result['persons'];
$persons = $result['objectov'];
$priborov = $result['priborov'];
$priborov_nu = $result['priborov_nu'];

// обработка и сохранение занятой для заявок даты
$cheky = array();
if (isset($_POST['dt_bs_save'])) {  // нажата кнопка сохранить
//    echo  "<script>alert(\"кнопка нажата!\");</script>";
    $dt_bs_sv = $_POST['dat_busy'];
// пардонте. попутал с мускулом ))
    //    $dt_bs_sv = str_to_date($dt_bs_sv,'%d.%m.%Y,%H:%i');
//    $dt_bs_sv = strtotime($dt_bs_sv);
// так работает
    if (date("Y-m-d H:i:s") < $dt_bs_sv) {
        $sql = mysqli_query($link, "insert into date_busy (date_b) values (CONVERT('$dt_bs_sv',datetime) ) ");
// отключил алерт
        //        if ($sql) $_SESSION['dt_bs_saved'] = 1;
    }
if (isset($_POST['del_dat'])) {  // переданы на удаление
    $cheky = $_POST['del_dat'];
    for ($i = 0; $i < count($cheky); $i++) {
        if (!empty($cheky[$i])) {
            $sql = mysqli_query($link, "delete from date_busy where date_b like '{$cheky[$i]}' ");
        }
    }
}
}

// отключил алерт
//if (isset($_SESSION['dt_bs_saved']) and $_SESSION['dt_bs_saved'] == 1) {
//    print ("<div class=\"alert alert-success alert-dismissible\">
//                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
//                <strong>Готово!</strong> Дата занятости сохранена.
//                </div>");
//    $_SESSION['dt_bs_saved'] = 0;
//}

// получаем перечень занятых дат для заявок
$dtek = date("Y-m-d H:i:s");    //текущая дата. решил не использовать ее

// проверяем выбраный вариант просмотра занятых вручную дат (гет=1 все, гет=0 или не определен - только актуальные)
// проверяем метки гета
//$chkd = '';
$gt_chkal = isset($_GET['message']) ? $_GET['message'] : '0';
if ($gt_chkal == 'checked'){
    $chkd = 'checked';
    $sql = mysqli_query($link, "SELECT * from date_busy ");
}
else{
    $chkd = '';
    $sql = mysqli_query($link, "SELECT * from date_busy where date_b >= '$dtek' ");
}

$date_bus = array();
$ibs = mysqli_num_rows($sql);
if ($ibs > 0){
    while ($row_zb = mysqli_fetch_array($sql)){
        $date_bus[] = $row_zb['date_b'];
    }
}

// обработка вывода принятых заявок
$category = array();
$problem = array();
$date_time = array();
$na_datu = array();
$onwork = array();
$done = array();
$phone = array();
$nom_z = array();
$fio = array();
$adres = array();
$lc = array();
$i = 1;
$ye = false;
$sel_all = '';
$sel_inw = '';
$zag_tab1 = 'Заявки в работе';
$lc_get = '';
$dt_get = '';

// устанавливаем фильтры
$chek_cat = isset($_GET['message']) ? $_GET['message'] : '';

$sel_all = $chek_cat == 'all' ? 'selected' : '';
$sel_inw = $chek_cat == 'inwork' ? 'selected' : '';
$zag_tab1 = $chek_cat == 'all' ? 'Все заявки' : $zag_tab1;
$zag_tab1 = $chek_cat == 'inwork' ? 'Заявки в работе' : $zag_tab1;

$sel_el = $chek_cat == 'elec' ? 'selected' : '';
$sel_vod = $chek_cat == 'voda' ? 'selected' : '';
$sel_otop = $chek_cat == 'otop' ? 'selected' : '';
$sel_kan = $chek_cat == 'kanal' ? 'selected' : '';
$sel_pro = $chek_cat == 'proch' ? 'selected' : '';
$sel_domf = $chek_cat == 'domf' ? 'selected' : '';
$sel_no =  $chek_cat == 'selno' ? 'selected' : '';

// фильтр выбран
if ($chek_cat != ''){

    // если лицевой, забираем
    $lc_get = is_numeric($chek_cat) ? $chek_cat : '';
//  уточняем для свчича
    $chek_cat = is_numeric($chek_cat) ? 1 : $chek_cat;

    // если дата, забираем
    $dt_get = preg_match("/[0-3][0-9]\.[0-1][0-9]\.[1-2][0-9]/", $chek_cat) ? $chek_cat : '';
//  уточняем для свчича
    $chek_cat = preg_match("/[0-3][0-9]\.[0-1][0-9]\.[1-2][0-9]/", $chek_cat) ? 'dt' : $chek_cat;

    switch ($chek_cat) {
        case 'selno':
//    получаем список заявок в процессе работы
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.done is null ");
            break;
        case 'inwork':
//    получаем список заявок в процессе работы
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.done is null ");
            break;
        case 'elec':
            $zag_tab1 = 'Заявки по электроэнергии';
//    получаем список всех поданых заявок по электро
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.category like 'Электроэнергия' ");
        break;
        case 'voda':
            $zag_tab1 = 'Заявки по воде';
//    получаем список всех поданых заявок по воде
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.category like 'Вода' ");
            break;
        case 'otop':
            $zag_tab1 = 'Заявки по отоплению';
//    получаем список всех поданых заявок по отоп
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.category like 'Отопление' ");
            break;
        case 'kanal':
            $zag_tab1 = 'Заявки по канализации';
//    получаем список всех поданых заявок по канализ
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.category like 'Канализация' ");
            break;
        case 'proch':
            $zag_tab1 = 'Заявки по прочим вопросам';
//    получаем список всех поданых заявок по проч
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.category like 'Прочее' ");
            break;
        case 1:
            $zag_tab1 = 'Заявки по л/счету '.$lc_get;
//    получаем список всех поданых заявок
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.lchet = '{$lc_get}' ");
            break;
        case 'dt':
            $zag_tab1 = 'Заявки по дате '.$dt_get;
//    получаем список всех поданых заявок
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.na_datu = '{$dt_get}' ");
            break;
        case 'all':
            $zag_tab1 = 'Все заявки';
//    получаем список всех поданых заявок
            $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls");
            break;
    }

}
else {
//    получаем список заявок в процессе работы
    $sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, person.name, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.done is null");
}



//    mysql_free_result($sql);


while ( $result = mysqli_fetch_array($sql))
{
    $ye = true;
    $fio[$i] = $result['name'];
    $adres[$i] = $result['AddressCity'] . ' ' . $result['AddressStreet'] . ' ' . $result['AddressHouse'] . ' ' . $result['AddressFlat'];
    $lc[$i] = $result['lchet'];
    $nom_z[$i] = $result['nom_zay'];
    $category[$i] = $result['category'];
    $problem[$i] = $result['problem'];
    $date_time[$i] = $result['date_time'];
    $na_datu[$i] = $result['na_datu'];
    $onwork[$i] = $result['onwork'];
    $done[$i] = $result['done'];
    $phone[$i] = $result['phone'];
    $i++;
}

//        очищаем переменные пост редиректом
//header("Location: ".$_SERVER['REQUEST_URI']);

?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <!--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script><!--<div class="d-flex p-2 bd-highlight">Показания приборов учета</div>-->

    <!--    для установки фильтра заявок -->
    <script>
        function ValChange() {
            var message = location.search;
            message = document.getElementById('is_done').value;
            window.location.href = 'adm_zay.php?message='+message;
        }
        function ValChangeChb() {
            var message = location.search ;
            var chbox;
            chbox=document.getElementById('ck_all');
            if (chbox.checked) {
                // alert('Выбран');
                message ='checked';
            }
            else {
                // alert ('Не выбран');
                message = 'nochecked';
            }
            // message = document.getElementById('ck_all').value;
            // alert(message);
            window.location.href = 'adm_zay.php?message='+message;
        }
        <!--    для установки фильтра заявок -->
        function ValChange_cat() {
            var message = location.search;
            message = document.getElementById('category_inp').value;
            window.location.href = 'adm_zay.php?message='+message;
        }
        function ValChange_lc() {
            var message = location.search;
            message = document.getElementById('lcet_in').value;
            window.location.href = 'adm_zay.php?message='+message;
        }
        function ValChange_dt() {
            var message = location.search;
            message = document.getElementById('date_in').value;
            window.location.href = 'adm_zay.php?message='+message;
        }

    </script>

</head>
<body>
<div class="container">
    <h3>Страница администратора</h3>
    <p>Управляющая компания "Хозяин дома"</p>

    <ul class="pagination">
        <li class="page-item active"><a class="page-link" href="#">Обработка заявок</a></li>
        <li class="page-item"><a class="page-link" href="ispolniteli.php">Исполнители</a></li>
        <li class="page-item"><a class="page-link" href="adm_page.php">Сервис</a></li>
        <li class="page-item"><a class="page-link" href="adm_opr.php">Опрос</a></li>
        <li class="page-item"><a class="page-link" href="#">Видеосвязь</a></li>
    </ul>

<!--    <form id="AdminForm" method="POST" action="" name="Adminka" >-->

    <table class="table table-dark table-striped">
        <thead>
        <tr>
            <th>Дата формирования БД</th>
            <th>Учтено объектов</th>
            <th>Приборов учета</th>
            <th>Нет № ПУ</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo "$date_bd" ?></td>
            <td><?php echo "$persons" ?></td>
            <td><?php echo "$priborov" ?></td>
            <td><?php echo "$priborov_nu" ?></td>
        </tr>
        </tbody>
    </table>

    <!-- таблица фильтров -->
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Отфильтровать по</th>
            <th scope="row"></th>
            <th scope="col">Готовности</th>
            <th scope="col">Категории</th>
            <th scope="col">Л/счету</th>
            <th scope="col">Дате заявки</th>
        </tr>
        <tr>
<!--    вроде и не нужна конопка. можно выбрать все        -->
            <th scope="row"><form action="adm_zay.php"><input type="submit" class="btn btn-warning" name="sbm_res" value="Сбросить" title="Сбросить все фильтры"></form></th>
            <th scope="col"></th>
            <th scope="col"><select size="1" id="is_done" name="ispolnenie" required onchange="ValChange()">
                    <option value="inwork" <?php echo $sel_inw ?>>В работе</option>
                    <option value="all" <?php echo $sel_all ?>>Все</option>
                </select></th scope="col">
            <th scope="col"><select size="1" id="category_inp" name="category" required onchange="ValChange_cat()">
                    <!--                        <option disabled>Выберите тип</option>-->
                    <option value="selno" <?php echo $sel_no ?> >Не выбрано</option>
                    <option value="elec" <?php echo $sel_el ?> >Электроэнергия</option>
                    <option value="otop" <?php echo $sel_otop ?> >Отопление</option>
                    <option value="voda" <?php echo $sel_vod ?> >Вода</option>
                    <option value="kanal" <?php echo $sel_kan ?> >Канализация</option>
                    <option value="domf" <?php echo $sel_domf ?> >Домофон</option>
                    <option value="proch" <?php echo $sel_pro ?> >Прочее</option>
                </select></th>
            <th scope="col"><input id="lcet_in" name="lchet_in" type="text" size="10" value="<?php echo $lc_get ?>" placeholder="Номер лицевого счета" onchange="ValChange_lc()"></th>
            <th scope="col"><input id="date_in" name="date_in" type="text" size="10" value="<?php echo $dt_get ?>" placeholder="Дата заявки" onchange="ValChange_dt()"></th>
        </tr>
        </thead>
    </table>

    <!-- информационная таблица   -->
    <h4><?php echo $zag_tab1?></h4>
    <!-- тут заявки в процессе выполнения -->
    <!--    выполненную заявку показывать только в день ее подачи-->
    <form id="checkzay" method="POST" action="adm_vwzay.php" name="checkzay">
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">№ заявки</th>
            <th scope="col">Л/счет</th>
            <th scope="col">ФИО</th>
            <th scope="col">Адрес</th>
            <th scope="col">Категория</th>
            <th scope="col">Суть проблемы</th>
            <th scope="col">Время подачи</th>
            <th scope="col">Дата работ</th>
            <th scope="col">Исполнение</th>
            <th scope="col">Контактный телефон</th>
            <th scope="col">Выбрать</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($ye){
//            for ($j=1; $j <= count($category); $j++)
//                или воспользуемся имеющейся переменной счетчика $i
            for ($j=1; $j < $i; $j++){
                $chk = $j==1 ? 'checked' : '';
                print "        
                <tr>
                    <th scope=\"row\">$nom_z[$j]</th>
                    <td>$lc[$j]</td>
                    <td>$fio[$j]</td>
                    <td>$adres[$j]</td>
                    <td>$category[$j]</td>
                    <td>$problem[$j]</td>
                    <td>$date_time[$j]</td>
                    <td>$na_datu[$j]</td>
                    <td>$done[$j]</td>
                    <td>$phone[$j]</td>
                    <td><input type=\"radio\" $chk required name=\"checked_z\" value=$nom_z[$j]></td>
                </tr> ";
            }
        }
        else
        {
            print "        
                <tr>
                    <th scope=\"row\"> </th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr> ";
        }
        ?>
        </tbody>
    </table>
        <input type="submit" class="btn btn-primary" name="viw_zay" value="Просмотреть заявку" title="Просмотр и редактирование заявки">
    </form>

</div>

<p> </p>

<div class="container">

<!--    <hr>-->
<!--    <p> </p>-->

    <!--<div class="container">-->
<!--    <p><h5>Занятые даты</h5></p>-->

<form action="" method="POST" >
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Занятые даты</th>
            <td><label><input type="checkbox" id="ck_all" name="chk_all" <?php echo "$chkd"?> onchange="ValChangeChb()" > Показать все</label></td>
<!--            <td><label><input type="checkbox" id="ck_all" name="chk_all" onchange="ValChangeChb()" > Показать все</label></td>-->
        </tr>
        </thead>
        <tbody>

<?php
    if ($ibs > 0){
    for ($jzb=0; $jzb < $ibs; $jzb++){
//            в чек отправляем удаляемую дату
        print "
        <tr>
            <td>$date_bus[$jzb]</td>
            <td><label><input type=\"checkbox\" readonly id=\"del_dt\" name=\"del_dat[]\" value=\"$date_bus[$jzb]\"> Удалить</label></td>
        </tr> 
        ";
        }
    }
//else
//    сбрасываем посты
//    не катит. ругается. уже был вывод в 18 строке
//    header('Location: adm_zay.php')
?>
        <tr>
<!--            <th scope="row"> </th>-->
            <td><input name="dat_busy" type="datetime-local" title="Ввод даты занятой для обслуживания"></td>
<!--            <td><button type="submit" class="btn btn-primary" name="dt_bs_save" title="Сохранить дату">Сохранить</button></td>-->
            <td><input type="submit" class="btn btn-info" name="dt_bs_save" value="Сохранить" title="Сохранить дату"> </td>
        </tr>
        </tbody>
    </table>
</form>
    <!--</div>-->
<!---->
<!--<div class="container">-->
<!--<!--    <button type="button" class="btn btn-info" >Вернуться</button>-->
<!--    <form action="open_fl.php" method="POST" enctype="multipart/form-data">-->
<!--        <input name="userfile" type="file" size="50">-->
<!--<!--    <button type="button" class="btn btn-secondary" onclick="upload_fl()">Загрузить файл</button>-->
<!--<!--        <button type="button" class="btn btn-secondary" >Загрузить файл</button>-->
<!--        <button type="submit" class="btn btn-secondary" title="Загрузить файл на FTP сервер">Загрузить файл</button>-->
<!--    </form>-->
    <p> </p>
    <hr>
    <p> </p>

</div>
    <div class="container">
        <a class="btn btn-secondary" href="/" role="button" title="Вернуться на главную страницу">Вернуться</a>
    </div>
</div>
<p> </p>

</body>
</html>

