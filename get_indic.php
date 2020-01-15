<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 04.06.19
 * Time: 16:40
 */
// Модуль загрузки показаий с фтп

require_once 'mySecure.php';

// коннектимся к бд
$link = connect_to_db ();

// запись показаний в файл
//$data = 'Показания_' . date("YmdHis");
// ограничился датой
$data = 'pokazania_' . date("Ymd");
//$date_now = date("d.m.Y H:i:s");
// сократим. нафиг время
$date_now = date("d.m.Y");
// для записи в поле дата бд
//$date_now_s = CURRENT_DATE();

// чтобы потом использовать в нашем формате надо преобразовывать дату
//DATE_FORMAT(`date`,'%d.%m.%Y');

// запросом выбираем все записи заявок и запускаем циклом запись в файл построчно

// выбираем все лицевые
//$sql = mysqli_query($link, "SELECT DISTINCT lchet from polucheno");
$sql = mysqli_query($link, "SELECT * from polucheno  
    WHERE str_to_date(date, '%d.%m.%Y') > str_to_date('01.07.2019','%d.%m.%Y') group by lchet");
if (mysqli_num_rows($sql) == 0)
{
    header('Location: adm_page.php?message=dwldno');
    exit;
}
else{
    $file = fopen($_SERVER['DOCUMENT_ROOT'].'/inSite/'.$data.'.txt', 'w');
    while($row_lc = mysqli_fetch_array($sql)){
        $lc = $row_lc['lchet'];
        fwrite($file, 'Лицевой счет:' 				. $lc 	. ";");
        fwrite($file, 'Дата:' 				. $row_lc['date'] 	. ";");
        $sql2 = mysqli_query($link, "SELECT * from polucheno where lchet='$lc' ");
        while($row = mysqli_fetch_array($sql2))
        {
            fwrite($file, 'Тип прибора:' 				. $row['service'] 		. ";");
            fwrite($file, 'Код прибора:' 			. $row['kod'] 		. ";");
            fwrite($file, 'Показания:' 				. $row['indiccur1'] . ";");
        }
        fwrite($file, PHP_EOL  );
    }
    fclose($file);
}
// сохраним дату забора показаний. надо подумать как исключать повтор забора прошломесячных
//$sql = mysqli_query($link, "update date_bd set dat_get_ind = '$date_now' ");
// может чек поставить - только новые..

// простейшее решение - получение ссылки для загрузки

//header('Location: adm_page.php?message=dwldok');
header('Location: adm_page.php?message=' . $data);
?>