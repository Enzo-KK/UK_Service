<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 08.05.19
 * Time: 12:01
 */
//Модуль сохранения показаний
//session_start();
require_once '../mySecure.php';

$data = date("YmdHis");
//$date_now = date("d.m.Y H:i:s");
// сократим. нафиг время
$date_now = date("d.m.Y");
$file = fopen($_SERVER['DOCUMENT_ROOT'].'/inSite/'.$data.'.txt', 'a');

if (isset($_POST['phone'])){
$str = preg_replace('#\(?(\w)\)?#s','$1',$_POST['phone']);
$str = preg_replace("#(?<=\d)[\s-]+(?=\d)#","",$str);
}

//fwrite($file, 'Улица:' 					. $_POST['street'] 				. ";");
//fwrite($file, 'Дом:' 						. $_POST['house'] 				. ";");
//fwrite($file, 'Квартира:' 					. $_POST['flat'] 				. ";");
//fwrite($file, 'ФИО:' 						. $_POST['fio'] 				. ";");
fwrite($file, 'Лицевой счет:' 				. $_POST['personal_account'] 	. ";");
fwrite($file, 'Дата:' 				. $date_now 	. ";");
//fwrite($file, 'Тип прибора:' 				. $_POST['counter_type_el'] 		. ";");
//fwrite($file, 'Тип прибора:' 				. 'Электро' 		. ";");
//
//fwrite($file, 'Серийный номер:' 			. $_POST['serial_number_el'] 		. ";");
//if($_POST['counter_place'] != ''){
//	fwrite($file, 'Месторасположение:' 	. $_POST['counter_place'] 		. ";");
//}
//if($_POST['another_place'] != ''){
//	fwrite($file, 'Другое:' 				. $_POST['another_place'] 		. ";");
//}
//fwrite($file, 'Показания:' 				. $_POST['counter_statement_el'] 	. ";");

//fwrite($file, 'Тип прибора:' 				. $_POST['counter_type_hv'] 		. ";");
fwrite($file, 'Тип прибора:' 				. 'ХВС' 		. ";");
//fwrite($file, 'Серийный номер:' 			. $_POST['serial_number_hv1'] 		. ";");
fwrite($file, 'Код прибора:' 			. $_POST['kod_hv1'] 		. ";");
fwrite($file, 'Показания:' 				. $_POST['counter_statement_hv1'] 	. ";");

if(isset($_POST['counter_statement_hv2']) && $_POST['counter_statement_hv2'] != 0) {
    fwrite($file, 'Тип прибора:' 				. 'ХВС' 		. ";");
//    fwrite($file, 'Серийный номер:' 			. $_POST['serial_number_hv2'] 		. ";");
    fwrite($file, 'Код прибора:' 			. $_POST['kod_hv2'] 		. ";");
    fwrite($file, 'Показания:' 				. $_POST['counter_statement_hv2'] 	. ";");
}

if(isset($_POST['counter_statement_hv3']) && $_POST['counter_statement_hv3'] != 0) {
    fwrite($file, 'Тип прибора:' 				. 'ХВС' 		. ";");
//    fwrite($file, 'Серийный номер:' 			. $_POST['serial_number_hv2'] 		. ";");
    fwrite($file, 'Код прибора:' 			. $_POST['kod_hv3'] 		. ";");
    fwrite($file, 'Показания:' 				. $_POST['counter_statement_hv3'] 	. ";");
}

//fwrite($file, 'Тип прибора:' 				. $_POST['counter_type_gv'] 		. ";");
fwrite($file, 'Тип прибора:' 				. 'ГВС' 		. ";");
//fwrite($file, 'Серийный номер:' 			. $_POST['serial_number_gv1'] 		. ";");
fwrite($file, 'Код прибора:' 			. $_POST['kod_gv1'] 		. ";");
fwrite($file, 'Показания:' 				. $_POST['counter_statement_gv1'] 	. ";");

if(isset($_POST['counter_statement_gv2']) && $_POST['counter_statement_gv2'] != 0) {
    fwrite($file, 'Тип прибора:' 				. 'ГВС' 		. ";");
//    fwrite($file, 'Серийный номер:' 			. $_POST['serial_number_gv2'] 		. ";");
    fwrite($file, 'Код прибора:' 			. $_POST['kod_gv2'] 		. ";");
    fwrite($file, 'Показания:' 				. $_POST['counter_statement_gv2'] 	. ";");
}

if(isset($_POST['counter_statement_hv3']) && $_POST['counter_statement_gv3'] != 0) {
    fwrite($file, 'Тип прибора:' 				. 'ГВС' 		. ";");
//    fwrite($file, 'Серийный номер:' 			. $_POST['serial_number_gv2'] 		. ";");
    fwrite($file, 'Код прибора:' 			. $_POST['kod_gv3'] 		. ";");
    fwrite($file, 'Показания:' 				. $_POST['counter_statement_gv3'] 	. ";");
}

//fwrite($file, 'Контактный телефон:' 		. $str 				. ";");
//fwrite($file, 'E-mail:' 					. $_POST['email'] 				. ";");

fclose($file);

// сохраняем показания в сессию
$_SESSION['saved'] = 1; // метка алерта
$_SESSION['hol1'] = $_POST['counter_statement_hv1']; // показания холодной воды
if (isset($_POST['counter_statement_hv2'])) $_SESSION['hol2'] = $_POST['counter_statement_hv2']; // показания холодной воды2
if (isset($_POST['counter_statement_hv3'])) $_SESSION['hol3'] = $_POST['counter_statement_hv3']; // показания холодной воды3
$_SESSION['gor1'] = $_POST['counter_statement_gv1']; // показания горячей воды
if (isset($_POST['counter_statement_gv2'])) $_SESSION['gor2'] = $_POST['counter_statement_gv2']; // показания горячей воды2
if (isset($_POST['counter_statement_gv3'])) $_SESSION['gor3'] = $_POST['counter_statement_gv3']; // показания горячей воды3

// коннектимся к бд
$link = connect_to_db ();

$lc = $_POST['personal_account'];
//$date_now = date('y-m-d H:i:s');
$hvn = $_POST['kod_hv1'];
$hv_new = $_POST['counter_statement_hv1'];
$gvn = $_POST['kod_gv1'];
$gv_new = $_POST['counter_statement_gv1'];
//echo "lc:$lc eln:$eln el_new:$el_new";

//$sql = mysqli_query($link, "insert into polucheno(lchet, kod, service, indiccur1, date) values('{$lc}', 'Хол/в', '{$hvn}', '{$hv_new}', '{$date_now}')");
// сделал уникальный индекс lchet+kod, при отсутствии записи добавляется, при присктствии апдейт
$sql = mysqli_query($link, "INSERT INTO polucheno (lchet, kod, service, indiccur1, date) values('{$lc}', '{$hvn}', 'Хол/в', '{$hv_new}', '{$date_now}') 
    ON DUPLICATE KEY UPDATE service='Хол/в', indiccur1='{$hv_new}', date='{$date_now}' ");

//$sql = mysqli_query($link, "insert into polucheno(lchet, service, kod, indiccur1, date) values('{$lc}', 'Гор.вода', '{$gvn}', '{$gv_new}', '{$date_now}')");
$sql = mysqli_query($link, "INSERT INTO polucheno (lchet, kod, service, indiccur1, date) values('{$lc}', '{$gvn}', 'Гор.вода', '{$gv_new}', '{$date_now}') 
    ON DUPLICATE KEY UPDATE service='Гор.вода', indiccur1='{$gv_new}', date='{$date_now}' ");

// если есть показания второго прибора
if(isset($_POST['counter_statement_hv2']) && $_POST['counter_statement_hv2'] != 0) {
    $hvn2 = $_POST['kod_hv2'];
    $hv_new2 = $_POST['counter_statement_hv2'];
//    $sql = mysqli_query($link, "insert into polucheno(lchet, service, kod, indiccur1, date) values('{$lc}', 'Хол/в', '{$hvn2}', '{$hv_new2}', '{$date_now}')");
    $sql = mysqli_query($link, "INSERT INTO polucheno (lchet, kod, service, indiccur1, date) values('{$lc}', '{$hvn2}', 'Хол/в', '{$hv_new2}', '{$date_now}') 
    ON DUPLICATE KEY UPDATE service='Хол/в', indiccur1='{$hv_new2}', date='{$date_now}' ");
}
if(isset($_POST['counter_statement_gv2']) && $_POST['counter_statement_gv2'] != 0) {
    $gvn2 = $_POST['kod_gv2'];
    $gv_new2 = $_POST['counter_statement_gv2'];
//    $sql = mysqli_query($link, "insert into polucheno(lchet, service, kod, indiccur1, date) values('{$lc}', 'Гор.вода', '{$gvn2}', '{$gv_new2}', '{$date_now}')");
    $sql = mysqli_query($link, "INSERT INTO polucheno (lchet, kod, service, indiccur1, date) values('{$lc}', '{$gvn2}', 'Гор.вода', '{$gv_new2}', '{$date_now}') 
    ON DUPLICATE KEY UPDATE service='Гор.вода', indiccur1='{$gv_new2}', date='{$date_now}' ");
}

//header('Location: http://березовый38.рф/in_lk');
header('Location: ../user_indic.php');
?>