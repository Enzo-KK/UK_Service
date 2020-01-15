<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 17.05.19
 * Time: 12:34
 */
// Редактор исполнителей работ в УК

require_once 'mySecure.php';
require_once 'myClass.php';

// проверим есть ли гет и метку администратора
if (!isset($_SESSION['adm_on']) || $_SESSION['adm_on']!=1) {
    header('Location: index.php');
}

// коннектимся к бд
$link = connect_to_db ();

$date_now = date("d.m.Y");

//if (isset($_POST['check_isp'])){

// передается из исполнители как массив
if (isset($_POST['check_isp'])) $inid = $_POST['check_isp'];

// определяем исполнителя или нового
if(isset($inid) && !empty($inid)) {
    $id = $inid[0];
}
//else{
//    echo("пустая передачка");
//}

// сохраняем номер заявки в сессию. обнуляется в вызывающем файле
//if ($id != 0) $_SESSION['id_isp'] = $id;
if (isset($id)) $_SESSION['id_isp'] = $id;


// обработка сабмита
if (isset($_POST['sv_isp1']) ) {  // нажата кнопка сохранить
//    записвываем исполнителя в бд
//        echo  "<script>alert(\"сохраняю!\");</script>";

// в случе сброса постов при сохранении, восстанавливаем
    $id = $_SESSION['id_isp'];

        $fio_isp = trim($_POST['isp_fio']);
        $kv_isp = trim($_POST['isp_kv']);
        $ph_isp = trim($_POST['isp_ph']);
        $pr_isp = trim($_POST['isp_prim']);
        $el_i = isset($_POST['isp_el']) && $_POST['isp_el'] == 1 ? 1 : 0;
        $ot_i = isset($_POST['isp_ot']) && $_POST['isp_ot'] == 1 ? 1 : 0;
        $vd_i = isset($_POST['isp_vd']) && $_POST['isp_vd'] == 1 ? 1 : 0;
        $kn_i = isset($_POST['isp_kn']) && $_POST['isp_kn'] == 1 ? 1 : 0;
        $dm_i = isset($_POST['isp_dm']) && $_POST['isp_dm'] == 1 ? 1 : 0;
        $pr_i = isset($_POST['isp_pr']) && $_POST['isp_pr'] == 1 ? 1 : 0;
//        $mt_isp = $_POST['isp_mtn'];
    if ($id == 'new_isp') { // если выбран новый исполнитель
        $sql = mysqli_query($link, "insert into ispolniteli(fio, kvalif, phone, prim, el, ot, vd, kn, dm, pr) 
        values('$fio_isp', '$kv_isp', '$ph_isp', '$pr_isp', $el_i, $ot_i, $vd_i, $kn_i, $dm_i, $pr_i) ");
//    if ($sql) echo  "<script>alert(\"вроде сохранил!\");</script>";
    }
    else{
        $sql = mysqli_query($link, "update ispolniteli set fio='$fio_isp', kvalif='$kv_isp', phone='$ph_isp', prim='$pr_isp', 
      el=$el_i, ot=$ot_i, vd=$vd_i, kn=$kn_i, dm=$dm_i, pr=$pr_i where id=$id ");
    }
if ($id == 'new_isp')    header("Location: ispolniteli.php");
}
// в случе сброса постов при сохранении, восстанавливаем номер заявки
//$n_zay = $_SESSION['n_zay'];


if ($id != 'new_isp'){

$sql = mysqli_query($link, "SELECT * FROM ispolniteli where id = $id");

$result = mysqli_fetch_array($sql);
    $id = $result['id'];
    $fio = $result['fio'];
    $kvalif = $result['kvalif'];
    $phone = $result['phone'];
    $prim = $result['prim'];

    $el_i = $result['el'];
    $elch = $el_i == 1 ? 'checked' : '';
    $ot_i = $result['ot'];
    $otch = $ot_i == 1 ? 'checked' : '';
    $vd_i = $result['vd'];
    $vdch = $vd_i == 1 ? 'checked' : '';
    $kn_i = $result['kn'];
    $knch = $kn_i == 1 ? 'checked' : '';
    $dm_i = $result['dm'];
    $dmch = $dm_i == 1 ? 'checked' : '';
    $pr_i = $result['pr'];
    $prch = $pr_i == 1 ? 'checked' : '';
}
else {
    $fio = '';
    $kvalif = '';
    $phone = '';
    $prim = '';
    $metka = '';
    $elch = '';
    $otch = '';
    $vdch = '';
    $knch = '';
    $dmch = '';
    $prch = '';
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
    <h3>Исполнители - изменение данных</h3>
    <p>Управляющая компания "Хозяин дома"</p>

    <ul class="pagination">
        <li class="page-item active"><a class="page-link" href="#">Редактирование</a></li>
        <li class="page-item"><a class="page-link" href="#">Исполнители</a></li>
        <!--        <li class="page-item"><a class="page-link" href="wrts.html">Видеосвязь</a></li>-->
    </ul>
    <form id="ispolnitel" method="POST" action="edit_ispoln.php" name="ispolnitel">
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">Исполнитель</th>
                <th scope="col">Данные</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">ФИО</th>
                <?php
                print "
                <td><input type=\"text\" name=\"isp_fio\" value=\" $fio \"></td>
            </tr>
            <tr>
                <th scope=\"row\">Квалификация</th>
                <td><input type=\"text\" size=\"8\" name=\"isp_kv\" value=\" $kvalif \"></td>
            </tr>
            <tr>
                <th scope=\"row\">Телефон</th>
                <td><input type=\"text\" size=\"8\" name=\"isp_ph\" value=\" $phone \"></td>
            </tr>
            <tr>
                <th scope=\"row\">Категория работ</th>
                <td><label><input type=\"checkbox\" id=\"isp_eln\" $elch name=\"isp_el\" value=\"1\"> Электроэнергия</label>
                    <label><input type=\"checkbox\" id=\"isp_otn\" $otch name=\"isp_ot\" value=\"1\"> Отопление</label>
                    <label><input type=\"checkbox\" id=\"isp_vdn\" $vdch name=\"isp_vd\" value=\"1\"> Вода</label>
                    <label><input type=\"checkbox\" id=\"isp_knn\" $knch name=\"isp_kn\" value=\"1\"> Канализация</label>
                    <label><input type=\"checkbox\" id=\"isp_dmn\" $dmch name=\"isp_dm\" value=\"1\"> Домофон</label>
                    <label><input type=\"checkbox\" id=\"isp_prn\" $prch name=\"isp_pr\" value=\"1\"> Прочее</label></td>
            </tr>
            <tr>
                <th scope=\"row\">Примечание</th>
                <td><input type=\"text\" name=\"isp_prim\" value=\" $prim \"></td>
            </tr>";
            ?>
            </tbody>
        </table>
        <input type="submit" class="btn btn-primary" name="sv_isp1" value="Сохранить изменения" title="Сохранить измененные данные">
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
    <a class="btn btn-secondary" href="ispolniteli.php" role="button" title="Вернуться к списку исполнителей">Вернуться</a>
</div>
<p> </p>

</body>
</html>
