<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 15.05.19
 * Time: 10:48
 */
// Модуль просмотра заявки

require_once 'mySecure.php';

// проверим есть ли гет и метку администратора
if (!isset($_SESSION['adm_on']) || $_SESSION['adm_on']!=1) {
    header('Location: index.php');
    exit;
}

// коннектимся к бд
$link = connect_to_db ();

$date_now = date("d.m.Y");
// если переменная передана  извне
$n_zay = isset($_POST['checked_z']) ? $_POST['checked_z'] : 0;
// сохраняем номер заявки в сессию. обнуляется в вызывающем файле
if ($n_zay != 0) $_SESSION['n_zay'] = $n_zay;

// обработка сабмита
if (isset($_POST['checked_done']) ) {  // нажата кнопка сохранить
//    записвываем закрытие заявки в бд
//        echo  "<script>alert(\"сохраняю!\");</script>";
    $n_zay = $_SESSION['n_zay'];
    $done_dat = $_POST['checked_done'] == 'yes' ? $date_now : $_POST['checked_done'];
    $comment = $_POST['comment'];

    $sql = mysqli_query($link, "update zayavki set done='$done_dat', comment='$comment' where nom_zay like $n_zay");
//    if ($sql) echo  "<script>alert(\"вроде сохранил!\");</script>";
//        очищаем переменные пост редиректом
    header("Location: ".$_SERVER['REQUEST_URI']);
}
// в случе сброса постов при сохранении, восстанавливаем номер заявки
$n_zay = $_SESSION['n_zay'];

$sql = mysqli_query($link, "SELECT zayavki.nom_zay, zayavki.lchet, zayavki.category, zayavki.problem, zayavki.date_time, 
    zayavki.na_datu, zayavki.onwork, zayavki.phone, zayavki.done, zayavki.isp_id, zayavki.comment, person.name, person.AddressCity, 
    person.AddressStreet, person.AddressHouse, person.AddressFlat 
      FROM zayavki, person WHERE zayavki.lchet = person.kod_ls and zayavki.nom_zay = $n_zay");

$result = mysqli_fetch_array($sql);
$fio = $result['name'];
$adres = $result['AddressCity'] . ' ' . $result['AddressStreet'] . ' ' . $result['AddressHouse'] . ' ' . $result['AddressFlat'];
$lc = $result['lchet'];
$nom_z = $result['nom_zay'];
$category = $result['category'];
$problem = $result['problem'];
$date_time = $result['date_time'];
$na_datu = $result['na_datu'];
$onwork = $result['onwork'];
$done = $result['done'];
$isp_id = $result['isp_id'];
$phone = $result['phone'];
$comment = $result['comment'];
$chk_done = $done == NULL ? 'checkbox' : 'text';
$chk_yno = $chk_done == 'checkbox' ? 'Отметить выполнение' : '';
$val_done = $chk_done == 'checkbox' ? 'yes' : $done;

// прикрутить таблицу исполнителей. пока так
//$maker =  'Иванов И.И.';
$id_mak = array();
$fio_mak = array();

$sql = mysqli_query($link, "SELECT * FROM ispolniteli ");
while ( $result = mysqli_fetch_array($sql)) {
    $id_mak[] = $result['id'];
    $fio_mak[] = $result['fio'];
}
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
</head>
<body>
<div class="container">
    <h3>Просмотр заявки</h3>
    <p>Управляющая компания "Хозяин дома"</p>

    <ul class="pagination">
        <li class="page-item active"><a class="page-link" href="#">Обработка заявки</a></li>
        <li class="page-item"><a class="page-link" href="ispolniteli.php">Исполнители</a></li>
<!--        <li class="page-item"><a class="page-link" href="wrts.html">Видеосвязь</a></li>-->
    </ul>
    <form id="editzay" method="POST" action="adm_vwzay.php" name="editzay">
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">Наименование</th>
                <th scope="col">Содержание</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">№ заявки</th>
                <td><input type="text" name="inom_zay" readonly value="<?php echo $nom_z ?>"> </td>
            </tr>
            <tr>
                <th scope="row">Л/счет</th>
                <td><?php echo $lc ?></td>
            </tr>
            <tr>
                <th scope="row">ФИО</th>
                <td><?php echo $fio ?></td>
            </tr>
            <tr>
                <th scope="row">Адрес</th>
                <td><?php echo $adres ?></td>
            </tr>
            <tr>
                <th scope="row">Контактный телефон</th>
                <td><?php echo $phone ?></td>
            </tr>
            <tr>
                <th scope="row">Категория</th>
                <td><input type="text" name="checked_cat" value="<?php echo $category ?>"></td>
            </tr>
            <tr>
                <th scope="row">Суть проблемы</th>
                <td><?php echo $problem ?></td>
            </tr>
            <tr>
                <th scope="row">Дата подачи заявки</th>
                <td><?php echo $date_time ?></td>
            </tr>
            <tr>
                <th scope="row">Запланированная дата работ</th>
                <td><input type="text" name="checked_dat" value="<?php echo $na_datu ?>"></td>
            </tr>
            <tr>
                <th scope="row">Исполнитель</th>
<!--                <td><input type="text" name="maker" value="--><?php //echo $maker ?><!--"></td>-->
                <td><select size="1" id="maker" name="maker" required >
                        <?php
                        $i = 0;
//                        $j = 1;
                        $max_i = sizeof($id_mak);
                        while ($i<$max_i){
                            $chk = $id_mak[$i] == $isp_id ? 'selected' : '';
                            print "<option value=\"$id_mak[$i]\" $chk > $fio_mak[$i]</option>";
                            $i++;
//                            $j++;
                        }
                        ?>
<!--                        <option value="$j"> $fio[$i]</option>-->
                    </select></td>
            </tr>
            <tr>
                <th scope="row">Дата исполнения</th>
                <td><input type="<?php echo $chk_done ?>" name="checked_done" value="<?php echo $val_done ?>" > <?php echo $chk_yno ?></td>
            </tr>
            <tr>
                <th scope="row">Комментарий</th>
                <td><textarea name="comment" cols="25" rows="4"><?php echo $comment ?></textarea> </td>
            </tr>
            </tbody>
        </table>
        <input type="submit" class="btn btn-primary" name="сv_zay" value="Сохранить изменения" title="Сохранить отредактированную заявки">
    </form>

</div>

<div class="container">
    <!--<div class="container">-->
    <!--    <p><h6>Выгрузка файла БД на FTP</h6></p>-->
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
    <a class="btn btn-secondary" href="adm_zay.php" role="button" title="Вернуться на главную страницу">Вернуться</a>
</div>
</div>
<p> </p>

</body>
</html>

