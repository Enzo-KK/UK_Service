<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 15.05.19
 * Time: 10:48
 */

require_once 'mySecure.php';

// проверим есть ли гет и метку администратора
if (!isset($_SESSION['adm_on']) || $_SESSION['adm_on']!=1) {
    header('Location: index.php');
    exit;
}
// сбрасываем ид исполнителя
$_SESSION['id_isp'] = 0;

// коннектимся к бд
$link = connect_to_db ();

$date_now = date("d.m.Y");

// обработка сабмита

// в случе сброса постов при сохранении, восстанавливаем номер заявки
//$n_zay = $_SESSION['n_zay'];

$sql = mysqli_query($link, "SELECT * FROM ispolniteli ");

// прикрутить таблицу исполнителей. пока так
$id = array();
$fio = array();
$kvalif = array();
$phone = array();
$prim = array();
$metka = array();
$elch = array();
$otch = array();
$vdch = array();
$knch = array();
$dmch = array();
$i = 1;
while ( $result = mysqli_fetch_array($sql)) {
    $id[$i] = $result['id'];
    $fio[$i] = $result['fio'];
    $kvalif[$i] = $result['kvalif'];
    $phone[$i] = $result['phone'];
    $prim[$i] = $result['prim'];

    $el_i[$i] = $result['el'];
    $elch[$i] = $el_i[$i] == 1 ? 'checked' : '';
    $ot_i[$i] = $result['ot'];
    $otch[$i] = $ot_i[$i] == 1 ? 'checked' : '';
    $vd_i[$i] = $result['vd'];
    $vdch[$i] = $vd_i[$i] == 1 ? 'checked' : '';
    $kn_i[$i] = $result['kn'];
    $knch[$i] = $kn_i[$i] == 1 ? 'checked' : '';
    $dm_i[$i] = $result['dm'];
    $dmch[$i] = $dm_i[$i] == 1 ? 'checked' : '';
    $pr_i[$i] = $result['pr'];
    $prch[$i] = $pr_i[$i] == 1 ? 'checked' : '';

    $i++;
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
    <h3>Исполнители</h3>
    <p>Управляющая компания "Хозяин дома"</p>

    <ul class="pagination">
        <li class="page-item "><a class="page-link" href="adm_zay.php">Заявки</a></li>
        <li class="page-item active" ><a class="page-link" href="#">Исполнители</a></li>
<!--        <li class="page-item"><a class="page-link" href="wrts.html">Видеосвязь</a></li>-->
    </ul>
    <form id="ispolniteli" method="POST" action="edit_ispoln.php" name="ispolniteli">
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">ФИО</th>
                <th scope="col">Квалификация</th>
                <th scope="col">Телефон</th>
                <th scope="col">Категория работ</th>
                <th scope="col">Примечание</th>
                <th scope="col">Выбрана запись</th>
            </tr>
            </thead>
            <tbody>
            <?php
            for ($j=1; $j < $i; $j++){
            print "
            <tr>
                <th scope=\"row\"><input type=\"text\" readonly name=\"isp_fio[]\" value=\" $fio[$j] \"></th>
                <td><input type=\"text\" size=\"8\" readonly name=\"isp_kv[]\" value=\" $kvalif[$j] \"></td>
                <td><input type=\"text\" size=\"8\" readonly name=\"isp_ph[]\" value=\" $phone[$j] \"></td>
                <td><label><input type=\"checkbox\" readonly id=\"isp_eln\" $elch[$j] name=\"isp_el\" value=\"1\"> Электроэнергия</label>
                    <label><input type=\"checkbox\" readonly id=\"isp_otn\" $otch[$j] name=\"isp_ot\" value=\"1\"> Отопление</label>
                    <label><input type=\"checkbox\" readonly id=\"isp_vdn\" $vdch[$j] name=\"isp_vd\" value=\"1\"> Вода</label>
                    <label><input type=\"checkbox\" readonly id=\"isp_knn\" $knch[$j] name=\"isp_kn\" value=\"1\"> Канализация</label>
                    <label><input type=\"checkbox\" readonly id=\"isp_dmn\" $dmch[$j] name=\"isp_dm\" value=\"1\"> Домофон</label>
                    <label><input type=\"checkbox\" readonly id=\"isp_prn\" $prch[$j] name=\"isp_pr\" value=\"1\"> Прочее</label></td>
                <td><input type=\"text\" name=\"isp_prim\" readonly value=\" $prim[$j] \"></td>
                <td><input type=\"radio\" name=\"check_isp[]\" required value=$id[$j]></td>
            </tr> ";
            }
            ?>
            <tr>
                <th scope="row">Добавить нового исполнителя</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input type="radio" name="check_isp[]" value="new_isp"></td>
            </tr>

            </tbody>
        </table>
        <input type="submit" class="btn btn-primary" name="ed_isp" value="Изменить" title="Отредактировать данные исполнителя или добавить нового">
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
    <a class="btn btn-secondary" href="adm_zay.php" role="button" title="Вернуться на страницу заявок">Вернуться</a>
<!--    <a class="btn btn-secondary" href="--><?php //echo $_SERVER['HTTP_REFERER'] ?><!--" role="button" title="Вернуться на главную страницу">Вернуться</a>-->
</div>
</div>
<p> </p>

</body>
</html>

