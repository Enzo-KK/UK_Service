<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 15.01.20
 * Time: 12:50
 */
// проверка логина, пароля
class LogPass {
    public $lchet;
    public $pass;
    public $date_bd;
    function __construct (){
        $this->lchet = isset($_POST['login']) ? $_POST['login'] : '';
        $this->pass = isset($_POST['password']) ? $_POST['password'] : '';

        if ($this->lchet == '') {
            $this->lchet = isset($_SESSION['lchet']) ? $_SESSION['lchet'] : '';
            $this->pass = isset($_SESSION['user']) ? $_SESSION['user'] : '';
            $this->date_bd = isset($_SESSION['date_bd']) ? $_SESSION['date_bd'] : '';

        }
    }
    function chkAdm (){
        if ($this->pass == ADM_PASS and ($this->lchet == ADM_LOG1 or $this->lchet == ADM_LOG2)){
            $_SESSION['adm_on']=1;
            header('Location: adm_zay.php');
            exit;
        }
        else{
            $_SESSION['adm_on']=0;
        }

    }
    function writeSess (){
        if (!isset($_SESSION['lchet'])) {
            $_SESSION['lchet'] = $this->lchet;
            $_SESSION['user'] = $this->pass;
        }

    }
}
