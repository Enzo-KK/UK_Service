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

// письмо Колямбе
$sql = mysqli_query($link, "SELECT * from date_bd");
$result = mysqli_fetch_array($sql);
//$mail_to = 'khozyain-doma@mail.ru';
//$mail_to = 'in@vivaluks.ru';
$mail_to = strpos($result['email'],'@') !== false ? $result['email'] : '';
$mail_sub = 'Заявка с сайта';
$mail_mes = '';
// был адрес оналогичный ту. майл ру его банит
//$headers = "From: " . $mail_to . "\r\nContent-type: text/plain; charset=UTF-8 \r\n";
// сделал адрес от фонаря
$headers = "From: zayavka@hd.ru\r\nContent-type: text/plain; charset=UTF-8 \r\n";

// проверяем логин пароль
$chklp = new LogPass();

if ($chklp->lchet != '') {
    $lchet = htmlentities(mysqli_real_escape_string($link, $chklp->lchet));
    $pass = htmlentities(mysqli_real_escape_string($link, $chklp->pass));

    if ( $chklp->pass == '' ) {
        header('Location: index.php');
        exit;
    }

    //запрос информации по лицевому
//    $sql = mysqli_query($link, "SELECT name, commonarea, AddressCity, AddressStreet, AddressHouse,
//      AddressFlat FROM person WHERE person.kod_ls LIKE  '$lchet' ");
// добавил задолженность
    $sql = mysqli_query($link, "SELECT person.name, person.commonarea, person.AddressCity, person.AddressStreet, 
    person.AddressHouse, person.AddressFlat, person.debtprev
      FROM person, pribori WHERE person.kod_ls = pribori.kod_ls and person.name LIKE '$pass%' and person.kod_ls LIKE '$lchet' ");

// если результат пустой то на выход
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
    $fio = $result['name'];
    $ulica = $result['AddressStreet'];
    $dom = $result['AddressHouse'];
    $kvart = $result['AddressFlat'];
    $address = $result['AddressStreet'] . ' ' . $result['AddressHouse'] . ' ' . $result['AddressFlat'];
    $dolg = $result['debtprev'] == 'NULL' ? 0 : $result['debtprev'];
//    $mes = $result['Месяц'];
//    $god = $result['Год'];

//    сбрасываем алерт
//    $_SESSION['saved'] = 0;
/////
    // формируем возможные даты исполнения заявок
    $chek_cat = isset($_GET['message']) ? $_GET['message'] : '';

    // определяем selected
    $sel_el = $chek_cat == 'Электроэнергия' ? 'selected' : '';
//    if ($chek_cat == '') $sel_el = 'selected'; // если не определено

    $sel_vod = $chek_cat == 'Вода' ? 'selected' : '';
    $sel_otop = $chek_cat == 'Отопление' ? 'selected' : '';
    $sel_kan = $chek_cat == 'Канализация' ? 'selected' : '';
    $sel_pro = $chek_cat == 'Прочее' ? 'selected' : '';
    $sel_dom = $chek_cat == 'Домофон' ? 'selected' : '';

    $rab_dati = array();
    $rab_dati_uk = array();
    $occupy = true;
    $today = date('Y-m-d');
    //заполняем даты пяти ближайших рабочих дней
    // сначала +день от текущей даты
    $corr = 1;
    $rab_dati_uk[1] = date('Y-m-d', strtotime($today . '+'.$corr.' Weekday'));
//    сопоставляем с датами занятыми вручную админом
    $sql = mysqli_query($link, "select * from date_busy where date_b like '{$rab_dati_uk[1]}%' ");
    $how = mysqli_num_rows($sql);
//    if ($how > 0)
//    далее проверяем занятые даты до нахождения первой свободной
    while ($how > 0)
    {
//        берем следующую дату так как эта занята
        $corr ++;
//        а можно и от добавленной даты крутить вместо тодей
        $rab_dati_uk[1] = date('Y-m-d', strtotime($today . '+'.$corr.' Weekday'));
//        проверяем наличие новой даты
        $sql = mysqli_query($link, "select * from date_busy where date_b like '{$rab_dati_uk[1]}%' ");
        $how = mysqli_num_rows($sql);
    }
    // дата в русском формате
    $rab_dati[1] = date('d.m.y', strtotime($today . '+'.$corr.' Weekday'));
    // потом остальные дни от первого
    for ($i=2; $i<=5; $i++) {
//    for ($i=++$corr; $i<=$corr+3; $i++) {
//        $rab_dati_uk[$i] = date('Y-m-d', strtotime($today . '+'.$i.' Weekday'));
//        берем следующий день
        $rab_dati_uk[$i] = date('Y-m-d', strtotime($today . '+'.++$corr.' Weekday'));
//        тут тоже надо проверять даты

        $sql = mysqli_query($link, "select * from date_busy where date_b like '{$rab_dati_uk[$i]}%' ");
        $how = mysqli_num_rows($sql);
//        if ($how > 0)
        while ($how > 0){
            $corr ++;
            $rab_dati_uk[$i] = date('Y-m-d', strtotime($today . '+'.$corr.' Weekday'));
//        проверяем наличие новой даты
            $sql = mysqli_query($link, "select * from date_busy where date_b like '{$rab_dati_uk[$i]}%' ");
            $how = mysqli_num_rows($sql);
        }
//        конвертируем в русский вариант
        $rab_dati[$i] = date('d.m.y', strtotime($today . '+'.$corr.' Weekday'));
    }

    // категория выбрана
    if ($chek_cat != ''){
// проверяем уже поданые и не исполненные заявки. получаем ближайщие свободные пять дат
        $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like '{$rab_dati[1]}' and category like '{$chek_cat}' and done is null");
//         $xx = mysqli_num_rows($sql);
//        echo "получено записей: $xx по категории: $chek_cat в дату: $rab_dati[1]";
        while (mysqli_num_rows($sql) > 0){
            // берем следующую дату
            $rab_dati_uk[1] = date('Y-m-d', strtotime($rab_dati_uk[1] . '+1 Weekday'));
            $rab_dati[1] = date('d.m.y', strtotime($rab_dati_uk[1]));
            $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like  '{$rab_dati[1]}' and category like '{$chek_cat}' and done is null");
        }
        $rab_dati_uk[2] = date('Y-m-d', strtotime($rab_dati_uk[1] . '+1 Weekday'));
        $rab_dati[2] = date('d.m.y', strtotime($rab_dati_uk[1] . '+1 Weekday'));
        $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like  '{$rab_dati[2]}' and category like '{$chek_cat}' and done is null");
        while (mysqli_num_rows($sql) > 0){
            // берем следующую дату
            $rab_dati_uk[2] = date('Y-m-d', strtotime($rab_dati_uk[2] . '+1 Weekday'));
            $rab_dati[2] = date('d.m.y', strtotime($rab_dati_uk[2]));
            $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like  '{$rab_dati[2]}' and category like '{$chek_cat}' and done is null");
        }

        $rab_dati_uk[3] = date('Y-m-d', strtotime($rab_dati_uk[2] . '+1 Weekday'));
        $rab_dati[3] = date('d.m.y', strtotime($rab_dati_uk[2] . '+1 Weekday'));
        $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like  '{$rab_dati[3]}' and category like '{$chek_cat}' and done is null");
        while (mysqli_num_rows($sql) > 0){
            // берем следующую дату
            $rab_dati_uk[3] = date('Y-m-d', strtotime($rab_dati_uk[3] . '+1 Weekday'));
            $rab_dati[3] = date('d.m.y', strtotime($rab_dati_uk[3]));
            $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like  '{$rab_dati[3]}' and category like '{$chek_cat}' and done is null");
        }

        $rab_dati_uk[4] = date('Y-m-d', strtotime($rab_dati_uk[3] . '+1 Weekday'));
        $rab_dati[4] = date('d.m.y', strtotime($rab_dati_uk[3] . '+1 Weekday'));
        $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like  '{$rab_dati[4]}' and category like '{$chek_cat}' and done is null");
        while (mysqli_num_rows($sql) > 0){
            // берем следующую дату
            $rab_dati_uk[4] = date('Y-m-d', strtotime($rab_dati_uk[4] . '+1 Weekday'));
            $rab_dati[4] = date('d.m.y', strtotime($rab_dati_uk[4]));
            $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like  '{$rab_dati[4]}' and category like '{$chek_cat}' and done is null");
        }

        $rab_dati_uk[5] = date('Y-m-d', strtotime($rab_dati_uk[4] . '+1 Weekday'));
        $rab_dati[5] = date('d.m.y', strtotime($rab_dati_uk[4] . '+1 Weekday'));
        $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like  '{$rab_dati[5]}' and category like '{$chek_cat}' and done is null");
        while (mysqli_num_rows($sql) > 0){
            // берем следующую дату
            $rab_dati_uk[5] = date('Y-m-d', strtotime($rab_dati_uk[5] . '+1 Weekday'));
            $rab_dati[5] = date('d.m.y', strtotime($rab_dati_uk[5]));
            $sql = mysqli_query($link, "SELECT * from date_zayav where date_z like  '{$rab_dati[5]}' and category like '{$chek_cat}' and done is null");
        }

    }

///
    $act = '';
    if (isset($_POST['sbm_data'])) {  // нажата кнопка отправить
//    $sbm_data = $_POST['sbm_data']; // "Отправить данные"
//    записвываем заявку в бд
        if(isset($_POST['category'])) {
            $date_now = date('d.m.y');
            $category = $_POST['category'];
            $problem = $_POST['problem'];
            $phone = $_POST['phone'];
            $na_datu_ = $_POST['date_choice'];
            $spec_num = 0;

            switch ($category){
                case 'Электроэнергия':
                    $spec = 'Электрик';
                    break;
                case 'Вода':
                    $spec = 'Сантехник';
                    break;
                case 'Отопление':
                    $spec = 'Сантехник';
                    break;
                case 'Канализация':
                    $spec = 'Сантехник';
                    break;
                case 'Домофон':
                    $spec = 'Домофон';
                    break;
                default:
                    $spec = 'Прочее';
            }
//            ищем исполнителя
//            if (!is_numeric($spec)) {
              if ($spec != 'Прочее') {
                $sql = mysqli_query($link, "SELECT * FROM ispolniteli where kvalif like '$spec%' ");
//                echo  "<script>alert(\"после запроса!\");</script>";
                if (mysqli_num_rows($sql) > 0) {
                    $result = mysqli_fetch_array($sql);
//                    echo " ищем " . $spec;
                    $spec_num = $result['id'];
//                    $ff = $result['fio'];
//                    echo " fio $ff id $spec_num";
//                    echo  "<script>alert(\"смотри!\");</script>";
                }
            }
            // проверка даты и категории - только из списка
//            if (in_array($na_datu_, $rab_dati) and in_array($category, $cat_list))
//            {
//                сохраняем в базу заявок
// формируем уникальный номер заявки
            $sql = mysqli_query($link, "SELECT max(nom_zay) as max_zay  FROM zayavki");
            if (mysqli_num_rows($sql) == 0) $nom_z = 1;
            else
            {
                $result = mysqli_fetch_array($sql);
                $nom_z = $result['max_zay'] + 1;
//                $nom_z = $nom_z + 1;
            }
//            адрес и фио уже есть
//            $sql = mysqli_query($link, "SELECT *  FROM person where kod_ls like '$lchet' ");
//            $result = mysqli_fetch_array($sql);
//            $fio = $result['name'];
//            $address = $result['AddressStreet'] . ' ' . $result['AddressHouse'] . ' ' . $result['AddressFlat'];

            $sql = mysqli_query($link, "insert into zayavki (nom_zay, lchet, fio, address, category, 
            problem, date_time, na_datu, onwork, phone, isp_id)
            values ($nom_z, '$lchet', '$fio', '$address', '$category', '$problem', '$date_now', '$na_datu_', 'В работе', 
            '$phone', $spec_num)");

//            $sql = mysqli_query($link, "insert into zayavki (nom_zay, lchet, category, problem, date_time, na_datu, onwork, phone)
//            values ($nom_z, '$lchet', '$category', '$problem', '$date_now', '$na_datu_', 'В работе', '$phone')");
//                сохраняем в базу дат
            $sql = mysqli_query($link, "insert into date_zayav (lchet, date_z, category) 
            values ('{$lchet}', '{$na_datu_}', '{$category}' )");
            //    ставим метку для вывода сообщения о сохранении
                $_SESSION['saved'] = 1;

//             письмо Колямбе
//            если есть адрес в базе
            if ($mail_to != ''){
                $mail_mes = 'Номер заявки: ' . $nom_z . '; Л/счет: ' . $lchet . '; ФИО: ' . $fio .
                    '; Адрес: ' . $address . '; Тел.: ' . $phone . '; Категория: ' . $category . '; Проблема: ' .
                    $problem . '; На дату: ' . $na_datu_;
                mail($mail_to, $mail_sub, $mail_mes, $headers);
//                добавляю в бд для проверки
                $str_to = "mail to: " . $mail_to . "; mail sub: " . $mail_sub . "; headers: " . $headers . "; mail mes: " . $mail_mes;
                $sql = mysqli_query($link, "insert into orders (tovar) values ('{$str_to}')");
//
            }
//            }
//            else{
////                введена не верная дата выполнения заявки
//                $_SESSION['saved'] = 2;
//            }
        }

//        очищаем переменные пост редиректом
        header("Location: ".$_SERVER['REQUEST_URI']);
    }
//if(isset($sbm_data))
//{
////    echo  "<script>alert(\"Реагирую!\");</script>";
//    // если проверки прошли меняю на файл обработки сохранения
////    $act = 'php/save_lk.php';
//    $_SESSION['saved'] = 0;
////
//}

// обработка вывода принятых заявок
    $category = array();
    $problem = array();
    $date_time = array();
    $na_datu = array();
    $onwork = array();
    $done = array();
    $phone = array();
    $i = 1;
    $ye = false;

    //    mysql_free_result($sql);
//    получаем список поданых заявок в процессе работы
    $sql = mysqli_query($link, "SELECT * FROM zayavki WHERE lchet LIKE  '$lchet' and done is null ");

    while ( $result = mysqli_fetch_array($sql))
    {
        $ye = true;
        $category[$i] = $result['category'];
        $problem[$i] = $result['problem'];
        $date_time[$i] = $result['date_time'];
        $na_datu[$i] = $result['na_datu'];
        $onwork[$i] = $result['onwork'];
        $done[$i] = $result['done'];
        $phone[$i] = $result['phone'];
        $i++;
    };
}

else{
    header('Location: inqex.php');
//    header("Location: ".$_SERVER['REQUEST_URI']);
    exit("Отвалился лицевой!");
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

    <!--    для получения категории заявки, чтобы сделать запросы с учетом категории-->
    <script>
        function ValChange() {
            var message = location.search;
            message = document.getElementById('category_inp').value;
            window.location.href = 'user_zayav.php?message='+message;
        }
    </script>
</head>
<body>

<!--единица или двойка пишется в сессию при сохранении данных после нажатия сабмита-->
<?php if (isset($_SESSION['saved']) and $_SESSION['saved'] == 1): ?>
    <br>
    <br>
    <br>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Готово!</strong> Заявка успешно отправлена.
    </div>
<?php else: ?>
    <div >
        <br>
        <br>
        <br>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['saved']) and $_SESSION['saved'] == 2): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Ошибка!</strong> Вы можете выбрать значения только из списка!
    </div>
    <!--сбрасываем метку сохранения -->
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
                <a class="nav-link" href="user_indic.php">Подать показания</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Сделать заявку</a>
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

<div class="container-fluid">
    <h3>Личный кабинет</h3>
    <p>Управляющая компания "Хозяин дома"</p>
<!--    <ul class="pagination">-->
<!--        <li class="page-item"><a class="page-link" href="data_lc.php">Лицевой счет</a></li>-->
<!--        <li class="page-item"><a class="page-link" href="data_indic.php">Подать показания</a></li>-->
<!--        <li class="page-item active"><a class="page-link" href="#">Подать заявку</a></li>-->
<!--    </ul>-->
<form id="anonymousAddressForm" method="POST" action="<?php echo "$act" ?>" name="OneForm" >
<!-- информационная таблица   -->
    <table class="table">
        <thead class="thead-dark">
        <tr>
            <th scope="col">Абонент</th>
            <th scope="col">Лицевой счет</th>
            <th scope="col">Улица</th>
            <th scope="col">Дом</th>
            <th scope="col">Кв.</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row"><?php echo "$fio" ?></th>
            <td><?php echo "$lchet" ?></td>
            <td><?php echo "$ulica" ?></td>
            <td><?php echo "$dom" ?></td>
            <td><?php echo "$kvart" ?></td>
        </tr>

        </tbody>
    </table>

    <hr>

    <div class="row">

    <div class="col-md-4 col-lg-6">
    <h4>Подать заявку</h4>
    <table class="table table-striped">
        <thead>
        <tr>
<!--            <th scope="col">№</th>-->
            <th scope="col">Категория</th>
            <th scope="col">Суть проблемы</th>
        </tr>
        </thead>
        <tbody>
        <tr>
<!--            <th scope="row">1</th>-->
            <td><select size="1" id="category_inp" name="category" required onchange="ValChange()">
<!--                        <option disabled>Выберите тип</option>-->
                    <option value="Электроэнергия" <?php echo $sel_el ?> >Электроэнергия</option>
                    <option value="Отопление" <?php echo $sel_otop ?> >Отопление</option>
                    <option value="Вода" <?php echo $sel_vod ?> >Вода</option>
                    <option value="Канализация" <?php echo $sel_kan ?> >Канализация</option>
                    <option value="Домофон" <?php echo $sel_dom ?> >Домофон</option>
                    <option value="Прочее" <?php echo $sel_pro ?> >Прочее</option>
                    </select></td>
            <td><textarea required name="problem" placeholder="Кратко изложите суть проблемы"></textarea></td>
        </tr>
        <tr>
            <th scope=\"row\">Контактный номер телефона</th>
            <td><input required name="phone" type="text"  placeholder="Укажите номер для связи"></td>
            <!--                <td></td>-->
        </tr>
        <tr>
            <th scope=\"row\">Выберите дату</th>
            <td><select size="1" name="date_choice" required>
                    <!--                        <option disabled>Выберите дату</option>-->
                    <option value="<?php echo $rab_dati[1] ?>"><?php echo $rab_dati[1] ?></option>
                    <option value="<?php echo $rab_dati[2] ?>"><?php echo $rab_dati[2] ?></option>
                    <option value="<?php echo $rab_dati[3] ?>"><?php echo $rab_dati[3] ?></option>
                    <option value="<?php echo $rab_dati[4] ?>"><?php echo $rab_dati[4] ?></option>
                    <option value="<?php echo $rab_dati[5] ?>"><?php echo $rab_dati[5] ?></option>
                </select></td>
            <!--                <td></td>-->
        </tr>
        </tbody>
    </table>
        <a class="btn btn-secondary" href="/" role="button" title="Вернуться ко входу в личный кабинет">Вернуться</a>
        <input type="submit" class="btn btn-primary" name="sbm_data" value="Отправить заявку" title="Отправить заявку в УК">
        <p></p>
    </div>

    <div class="col-md-4 col-lg-6">
    <h4>Заявки в работе</h4>
    <!-- тут заявки в процессе выполнения -->
    <!--    выполненную заявку показывать только в день ее подачи-->
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">№</th>
            <th scope="col">Категория</th>
            <th scope="col">Проблема</th>
            <th scope="col">Подана</th>
            <th scope="col">На дату</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($ye){
//            for ($j=1; $j <= count($category); $j++)
//                или воспользуемся имеющейся переменной счетчика $i
            for ($j=1; $j < $i; $j++){
                print "        
                <tr>
                    <th scope=\"row\">$j</th>
                    <td>$category[$j]</td>
                    <td>$problem[$j]</td>
                    <td>$date_time[$j]</td>
                    <td>$na_datu[$j]</td>
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
                </tr> ";
        }
        ?>
        </tbody>
    </table>
    </div>

    </div>
</form>

<br>

<table class="table table-striped">
    <thead>
    <tr>
        <th scope="col">Контактные номера телефонов УО</th>
        <th scope="col">(3952) 404055</th>
        <th scope="col">+7 9025126055</th>
    </tr>
    </thead>
    <?php if ($dolg > 0): ?>
    <tbody>
    <tr>
        <th scope="col">За вами долг за предыдущий период</th>
        <th scope="col"><?php echo "$dolg" ?></th>
        <th scope="col">рублей</th>
    </tr>
    </tbody>
    <?php endif; ?>
</table>

    <p>* Обратите внимание: <br>Заявка принимается на ближайшее свободное время обслуживающего специалиста.</p>

</div>

</body>
</html>

