<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 30.01.19
 * Time: 14:01
 */

require_once 'mySecure.php';
require_once 'myClass.php';

// проверка административного входв
if (!isset($_SESSION['adm_on']) || $_SESSION['adm_on']!=1) {
    header('Location: index.php');
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

<!-- передаем гетом метку обработку   -->
<script>
    function ValChange() {
        var message = location.search;
        message = document.getElementById('CountDolzh').value;
        window.location.href = 'adm_page.php?message='+message;
    }
    function ValChange_b() {
        var message = location.search;
        message = document.getElementById('ChkBal').id;
        window.location.href = 'adm_page.php?message='+message;
    }
    function ValChange_snd() {
        var message = location.search;
        message = document.getElementById('SendSMS').id;
        window.location.href = 'adm_page.php?message='+message;
    }
    function ValChange_sv_txt() {
        var message = location.search;
        message = document.getElementById('SaveTxt').id;
        window.location.href = 'adm_page.php?message='+message;
    }
    function upload_fl() {
        document.location.href = 'open_fl.php';

    }
</script>

<?php
// коннектимся к бд
$link = connect_to_db ();

$sql = mysqli_query($link, "SELECT * from date_bd");
$result = mysqli_fetch_array($sql);
//$date_bd = $result['date_bd'];
$date_bd = $result['filedate'];
//$persons = $result['persons'];
$persons = $result['objectov'];
$priborov = $result['priborov'];
$priborov_nu = $result['priborov_nu'];
$email_sv = $result['email'];

// смотрим метки
//$zap_bal = $result['zapros_bal'];
//$zap_rass = $result['zapros_rass'];

// проверяем метку сессии, которая устанвливаетсе в open_fl, показываем сообщение и сбрасываем метку
if (isset($_SESSION['ftp_uploaded']) and $_SESSION['ftp_uploaded'] == 1) {
    print ("<div class=\"alert alert-success alert-dismissible\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                <strong>Готово!</strong> Файл отправлен на сервер. Можно загрузить данные в базу.
                </div>");
                $_SESSION['ftp_uploaded'] = 0;
}

if (isset($_SESSION['ftp_uploaded']) and $_SESSION['ftp_uploaded'] == 2) {
    print ("<div class=\"alert alert-warning alert-dismissible\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                <strong>Готово!</strong> Файл не отправлен на FTP сервер! Все пропало!.
                </div>");
    $_SESSION['ftp_uploaded'] = 0;
}

////////

// проверяем метки гета
$got = isset($_GET['message']) ? $_GET['message'] : '0';

//if ($got != '0' and $got == 'dwldok') {
// получаем имя файла с показаниями
if (strpos($got,'pokazania') !== false) {
    $got_pok = $got;
                    print ("<div class=\"alert alert-info alert-dismissible\">
                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                    <strong>Готово!</strong> Файл с  показаниями сформирован.
                    </div>" );
}
if ($got != '0' and $got == 'dwldno') {
    print ("<div class=\"alert alert-danger alert-dismissible\">
                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                    <strong>Внимание!</strong> Файлы с  показаниями загрузить не удалось.
                    </div>" );
}
// если был выгружен файл, берем его имя
// реализовал скриптом
    //    принимаем базу из хмл
if (strpos($got,'bdok') !== false) {
    $got_dwld = $got;
    print ("<div class=\"alert alert-info alert-dismissible\">
                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                    <strong>Готово!</strong> База данных обновлена.
                    </div>" );
}

// сохранение адреса мыла
if (isset($_POST['email_save'])) {  // нажата кнопка сохранить
    $email_sv = $_POST['email_svd'];
    $sql = mysqli_query($link, "update date_bd set email='$email_sv' ");
    if ($sql) $_SESSION['email_saved'] = 1;
}

if (isset($_SESSION['email_saved']) and $_SESSION['email_saved'] == 1) {
    print ("<div class=\"alert alert-success alert-dismissible\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                <strong>Готово!</strong> Адрес e-mail сохранен.
                </div>");
    $_SESSION['email_saved'] = 0;
}
?>

</head>
<body>
<div class="container">
    <h3>Страница администратора</h3>
    <p>Управляющая компания "Хозяин дома"</p>
    <ul class="pagination">
        <li class="page-item"><a class="page-link" href="adm_zay.php">Обработка заявок</a></li>
        <li class="page-item"><a class="page-link" href="ispolniteli.php">Исполнители</a></li>
        <li class="page-item active"><a class="page-link" href="#">Сервис</a></li>
        <li class="page-item"><a class="page-link" href="adm_opr.php">Опрос</a></li>
        <li class="page-item"><a class="page-link" href="#">Видеосвязь</a></li>
    </ul>

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

<form action="get_indic.php" method="POST" >

    <table class="table table-striped">
    <thead>
    <tr>
        <th scope="col">Загрузка файла с показаниями</th>
        <th scope="col"></th>
        <th scope="col"></th>
        <th scope="col"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th scope="row">После загрузки будет доступна ссылка</th>
        <td><?php
            if (isset($got_pok))  {
                print ("<a href=\"http://host1697773.hostland.pro/inSite/$got_pok.txt\" title=\"Ссылка для сохранения файла на диск\">$got_dwld.txt</a> ");
            }?></td>
        <td><button id="ChkBal" type="submit" class="btn btn-info" title="Загрузка файла с показаниями">Загрузить</button></td>
        <td> </td>
    </tr>
    <tr>
        <th scope="row"></th>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    </tbody>
    </table>
</form>

<form action="open_fl.php" method="POST" enctype="multipart/form-data">
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Выгрузка файла XML на сервер</th>
            <th scope="col"></th>
            <th scope="col"></th>
            <th scope="col"><div id="ldxmllab"></div></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row"><input required name="userfile" type="file" onChange="javascript:if(userfile.value.substring(userfile.value.lastIndexOf('.')+1,userfile.value.length).toLowerCase()!='xml')
            {alert('Необходимо выбрать XML файл для загрузки!'); form.reset(); return;};"></th>
            <td></td>
            <td><button type="submit" class="btn btn-info" title="Выгрузить файл с данными на сервер">Выгрузить</button></td>
            <td><div id="ldxml"></div> </td>
        </tr>
        <tr>
            <th scope="row"></th>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
    </table>
</form>

    <form action="" method="POST" >

        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">Адрес электронной почты для отправки копии заявки</th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row"><input name="email_svd" type="email" value="<?php echo $email_sv ?>"></th>
                <td><input type="submit" class="btn btn-info" name="email_save" value="Сохранить" title="Сохранить адрес электронной почты"> </td>
                <td></td>
                <td> </td>
            </tr>
            <tr>
                <th scope="row"></th>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>
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
</div>
<p> </p>
    <div class="container">
        <a class="btn btn-secondary" href="/" role="button" title="Вернуться на главную страницу">Вернуться</a>
    </div>
</div>

<script>
    var message = location.search;
    // проверим корректность имени файла хмл
    if (message.indexOf('xml') > -1) {
        if (message.indexOf('vigruzka') > -1) {
            message = message.substring(9);
            document.getElementById("ldxmllab").innerHTML = 'Будет загружен файл: ' + message;
            document.getElementById("ldxml").innerHTML = '<button type="button" class="btn btn-primary" onclick="loadxml()" title="Загрузить файл с данными в базу на сервер">Загрузить</button>';
        }
        else {
            alert('Имя файла должно быть: vigruzka****.xml');
        }
    }
    function loadxml() {
        var message = location.search;
        if (message.indexOf('vigruzka') > -1) {
            window.location.href = 'load_xml.php'+message;
        }
    }

</script>

</body>
</html>

