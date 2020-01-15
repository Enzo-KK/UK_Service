<?php
/**
 * Created by PhpStorm.
 * User: constantin krayushkin
 * Date: 05.02.19
 * Time: 10:28
 */

require_once 'mySecure.php';

$filep = $_FILES['userfile']['tmp_name'];
$ftp_server = FTP_SRV;
$ftp_user_name = FTP_USER;
$ftp_user_pass = FTP_PASS;
$paths = FTP_PATH;
$name = $_FILES['userfile']['name'];
$res = 'no';

$conn_id = ftp_connect($ftp_server) or die("Не удалось установить соединение с $ftp_server");

$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
if ((!$conn_id) || (!$login_result)) {
    echo "FTP connection has failed!";
    echo "Attempted to connect to $ftp_server for user: $ftp_user_name";
    exit;
} else {
}

// сначала пробуем удлалить
//ftp_delete($conn_id, '/'.$paths.'/'.$name);
// выгружает только с правами на корневую папку
$upload = ftp_put($conn_id, '/'.$paths.'/'.$name, $filep, FTP_BINARY);
if (!$upload) {
    echo "Error: FTP upload has failed!";
    $_SESSION['ftp_uploaded'] = 2;
//    ftp_delete($conn_id, '/'.$paths.'/'.$name);
//    header('Location: open_fl.php');
} else {
//    $res = 'ok';
}

ftp_close($conn_id);

set_time_limit(3000);

$_SESSION['ftp_uploaded'] = 1;

header('Location: adm_page.php?message=' . $name);

?>


