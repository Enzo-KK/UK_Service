<?php
/**
 * Created by PhpStorm.
 * User: Constantin Krayushkin
 * Date: 13.09.19
 * Time: 14:01
 */

require_once 'mySecure.php';
require_once 'myClass.php';

if (!isset($_SESSION['adm_on']) || $_SESSION['adm_on']!=1) {
    header('Location: index.php');
}

// коннектимся к бд
$link = connect_to_db ();

// получаем данные опросов
$id_op = array();
$nm_op = array();
$dt_op = array();
$tx_op = array();
$an1 = array();
$an2 = array();
$an3 = array();
$an4 = array();
$an5 = array();
$publ = array();

$opr_stat = 0;
$col_answ = 0;

// собираем инфу по всем опросам в бд
$sql = mysqli_query($link, "SELECT * from opros");
$iop = mysqli_num_rows($sql);
if ($iop > 0){
    $opr_stat = $iop;
    while ($row_op = mysqli_fetch_array($sql)){
        $id_op[] = $row_op['id'];
        $dt_op[] = $row_op['date_op'];
        $nm_op[] = $row_op['name_op'];
        $tx_op[] = $row_op['text_op'];
        $an1[] = $row_op['ans_1'];
        $an2[] = $row_op['ans_2'];
        $an3[] = $row_op['ans_3'];
        $an4[] = $row_op['ans_4'];
        $an5[] = $row_op['ans_5'];
        $publ[] = $row_op['publ_op'];
    }
}
// получаем инфу по выбранному опросу
// инициируем значения полей
$zag_opr = '';
$dat_op = '';
$op_opr = '';
$var_otv1 ='';
$var_otv2 ='';
$var_otv3 ='';
$var_otv4 ='';
$var_otv5 ='';
$chk = '';
$rdonly = '';
$opye = '';
//  надо через гет
if (isset($_GET['opros']) && $_GET['opros'] != 0){
    $op = $_GET['opros'];
// определяем индекс в массиве для данного ид
    $op = array_search($op, $id_op);
    $zag_opr = $nm_op[$op];
    $dat_op = $dt_op[$op];
    $op_opr = $tx_op[$op];
    $var_otv1 = $an1[$op];
    $var_otv2 = $an2[$op];
    $var_otv3 = $an3[$op];
    $var_otv4 = $an4[$op];
    $var_otv5 = $an5[$op];
    $chk = 'selected';
// не знаю использовать или нет..
    $rdonly = 'readonly';
    $pub = $publ[$op];
    $opye = $pub == 1 ? 'checked' : '';
}

// обработка нового опроса или внесение изменений
//
if (isset($_POST['save_op'])) {  // нажата кнопка сохранить
    $dat_op = date("Y-m-d");
//    проверка на 3,4,5 варианты ответа
    $an_3 = $_POST['var_otv3']!='' ? $_POST['var_otv3'] : '';
    $an_4 = $_POST['var_otv4']!='' ? $_POST['var_otv4'] : '';
    $an_5 = $_POST['var_otv5']!='' ? $_POST['var_otv5'] : '';
//    проверяем это меняются сохраненные данные или новый опрос
    if (isset($pub) ) { // опрос выбран из сохраненных
        switch ($pub){ // опрос сохранен но не опубликован.
            case 0:
// делаем апдейт записи.
//         проверяем введен ли новый текст. если да то его, если нет то прежний
                $tmptxt = iconv_strlen($_POST['op_opr']) > 5 ? $_POST['op_opr'] : $op_opr;

                $sql = mysqli_query($link, "update opros set name_op='{$_POST['zag_opr']}', text_op='$tmptxt', 
      ans_1='{$_POST['var_otv1']}', ans_2='{$_POST['var_otv2']}', ans_3='$an_3', ans_4='$an_4', ans_5='$an_5', 
      publ_op='{$_POST['pub_opr']}' ");
                break;
            case 1:
// если чек снят
                if ($_POST['pub_opr'] == 0) {
// делаем снятие с публикации
                    $sql = mysqli_query($link, "update opros set  publ_op=2 ");
                }
                break;
//        если стоит 2 значит снят с публикации. ничего не делаем
        }
    }
    else {
//    новый опрос. ставим метку 0
    $akt = isset($_POST['pub_opr']) && $_POST['pub_opr'] == 1 ? 1 : 0;
    $sql = mysqli_query($link, "insert into opros (name_op, date_op, text_op, ans_1, ans_2, ans_3, ans_4, ans_5, publ_op) 
      values ('{$_POST['zag_opr']}', CONVERT('$dat_op',date), '{$_POST['op_opr']}', '{$_POST['var_otv1']}', '{$_POST['var_otv2']}', '$an_3', '$an_4', '$an_5', '$akt') ");
    }
}
//// конец блока записи
///
// проверяем активный опрос или выбраный архивный
$activ = false;
$arhiv = false;
//    метки ответов более двух
$ye3 = false;
$ye4 = false;
$ye5 = false;

$sql = mysqli_query($link, "SELECT * from opros where publ_op like 1");
$iop = mysqli_num_rows($sql);
if ($iop > 0){ // есть ли активный опрос
    $activ = true;
    }
    elseif (isset($_GET['opros']) && $_GET['opros'] != 0) { // выбран архивный опрос
        $sql = mysqli_query($link, "SELECT * from opros where id like '{$_GET['opros']}'");
        $iop = mysqli_num_rows($sql);
        $arhiv = true;
        }
        if ($activ || $arhiv){
    $row_op = mysqli_fetch_array($sql);
        $id_ak = $row_op['id'];
        $dt_ak = $row_op['date_op'];
        $nm_ak = $row_op['name_op'];
        $tx_ak = $row_op['text_op'];
        $an1_ak = $row_op['ans_1'];
        $an2_ak = $row_op['ans_2'];
        $an3_ak = $row_op['ans_3'];
        $an4_ak = $row_op['ans_4'];
        $an5_ak = $row_op['ans_5'];
//        метка активного или выбраного архивного опроса
        $isact = $iop;
//        не мудрствуя лукаво ))
//    все уже проработано на основе гета по выбору опроса из селекта. и чтоб не переписывать код сделал костыль
    if (!isset($_GET['opros'])) {
        print "<script>window.location.href = 'adm_opr.php?opros='+$id_ak;</script>";
    }
// собираем статистику
//    начальные значения, пока нет ответов
    $col_answ = 0;
    $ans1 = 0;
    $ans2 = 0;
    $ans3 = 0;
    $ans4 = 0;
    $ans5 = 0;
    $sql = mysqli_query($link, "SELECT * from opros_answ ");
    $iop = mysqli_num_rows($sql);
    if ($iop > 0) {
        $col_answ = $iop;
//        получаем список ответов
        while ($result = mysqli_fetch_array($sql)){
            $op_lc[] = $result['lchet'];
            $op_ans[] = $result['answ'];
        }

        $sql = mysqli_query($link, "SELECT * from opros_answ where answ like '$an1_ak' ");
        $ans1 = mysqli_num_rows($sql);
        $sql = mysqli_query($link, "SELECT * from opros_answ where answ like '$an2_ak' ");
        $ans2 = mysqli_num_rows($sql);
//        проверяем есть ли еще варианты ответов
        if (iconv_strlen($an3_ak)>1){
            $ye3 = true;
            $sql = mysqli_query($link, "SELECT * from opros_answ where answ like '$an3_ak' ");
            $ans3 = mysqli_num_rows($sql);
            if (iconv_strlen($an4_ak)>1) {
                $ye4 = true;
                $sql = mysqli_query($link, "SELECT * from opros_answ where answ like '$an4_ak' ");
                $ans4 = mysqli_num_rows($sql);
                if (iconv_strlen($an5_ak) > 1) {
                    $ye5 = true;
                    $sql = mysqli_query($link, "SELECT * from opros_answ where answ like '$an5_ak' ");
                    $ans5 = mysqli_num_rows($sql);
                }
            }
        }
    }
}
$nam_zag = $activ ? 'Дата активного опроса:' : ($arhiv ? 'Дата архивного опроса:' : '');
//// конец обработки статистики опроса

//        очищаем переменные пост редиректом
if (isset($_POST['save_op'])) {  // была нажата кнопка сохранить
    header("Location: " . $_SERVER['REQUEST_URI']);
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

    <script>
        var i = 4;
        var elem = '';
        // показывать или нет варианты ответа более двух
        function addansw(){
            if (i==6){
                elem = 'ans5';
                // alert(elem);
                document.getElementById(elem).style = "display: none";
                i = 5;
            }
            else {
            elem = 'ans'+i;
            // alert(elem);
            document.getElementById(elem).style = "";
            i++;
            }
            }
        //    не хочет сволочь работать. прописал в селекте
        function fresh() {
            alert('fresh');
            window.location.href = 'adm_opr.php;
        }
    </script>
</head>
<body>
<div class="container">
    <h3>Страница администратора</h3>
    <p>Управляющая компания "Хозяин дома"</p>
    <ul class="pagination">
        <li class="page-item"><a class="page-link" href="adm_zay.php">Обработка заявок</a></li>
        <li class="page-item"><a class="page-link" href="ispolniteli.php">Исполнители</a></li>
        <li class="page-item "><a class="page-link" href="adm_page.php">Сервис</a></li>
        <li class="page-item active"><a class="page-link" href="#">Опрос</a></li>
        <li class="page-item"><a class="page-link" href="#">Видеосвязь</a></li>
    </ul>

<!--</div>-->

    <table class="table table-dark table-striped">
        <thead>
<!--        если есть активный или выбран архинвй опрос, показываем статистику. иначе говорим: ничего нет -->
<!--        --><?php //if ($col_answ > 0): ?>
        <?php if ($activ || $arhiv ): ?>
        <tr>
            <th><?php echo "$nam_zag" ?></th>
            <th>О чем опрос:</th>
            <th>Получено ответов:</th>
<!--            <th>Нет № ПУ</th>-->
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo "$dt_ak" ?></td>
            <td><?php echo "$nm_ak" ?></td>
            <td><?php echo "$col_answ" ?></td>
        </tr>
        <tr>
            <td>Из них: </td>
            <td><?php echo $an1_ak ?> </td>
            <td><?php echo $ans1 ?> </td>
        </tr>
        <tr>
            <td> </td>
            <td><?php echo $an2_ak ?> </td>
            <td><?php echo $ans2 ?> </td>
        </tr>
        <!--        если вариантов ответов боее двух -->
        <?php
        if ($ye3) {
            print "<tr>
            <td> </td>
            <td>$an3_ak </td>
            <td>$ans3 </td>
        </tr>";
        }
        if ($ye4) {
            print "<tr>
            <td> </td>
            <td>$an4_ak </td>
            <td>$ans4 </td>
        </tr>";
        }
        if ($ye5) {
            print "<tr>
            <td> </td>
            <td>$an5_ak </td>
            <td>$ans5 </td>
        </tr>";
        }
        ?>
        <?php else: ?>
        <tr>
            <th>Опрос не выбран</th>
            <th> </th>
            <th> </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td> </td>
            <td> </td>
            <td> </td>
        </tr>
        <?php endif; ?>

        <tr>
            <td>Выбрать сохраненный опрос</td>
            <td><select size="1" id="opros" name="oprosi" onchange="window.location.href = 'adm_opr.php?opros='+document.getElementById('opros').value;">
                    <option value="0" > ---</option>
                    <?php
                    if ($opr_stat > 0){
                    $i = 0;
                    //  можно не считать, а взять кол. зап. из запроса
//                    $max_i = sizeof($nm_op);
                    $max_i = $opr_stat;
                    while ($i<$max_i){
//                        $chk = $nm_op[$i] == $isp_id ? 'selected' : '';
                        print "<option $chk value=\"$id_op[$i]\"  > $nm_op[$i]</option>";
                        $i++;
//                            $j++;
                    }
                    }
//                    else{
//                        print "<option value=\"\"  > </option>";
//                    }
                    ?>
                </select></td>
            <td><select size="1" id="spisok" name="spisok" >
                <option value="0" >Список ответов</option>
                <?php
                if ($col_answ > 0){
                    $i = 0;
                    //  можно не считать, а взять кол. зап. из запроса
//                    $max_i = sizeof($nm_op);
                    $max_i = $col_answ;
                    while ($i<$max_i){
//                        $chk = $nm_op[$i] == $isp_id ? 'selected' : '';
                        print "<option value=\"\"  >" . $op_lc[$i] . " " . $op_ans[$i] . "</option>";
                        $i++;
                   }
                }
                ?>
                </select> </td>
        </tr>
        </tbody>
    </table>

    <form action="" method="POST" >

    <table class="table table-striped">
    <thead>
    <tr>
        <th scope="col">Формирование опросного списка</th>
<!--    прячем инпут с нулевым значением    -->
        <th scope="col"><input type="hidden" name="pub_opr" value="0" />
            <label><input type="checkbox" <?php echo $opye ?> id="pub_op" name="pub_opr" value="1"> Опубликовать</label></th>
        <th scope="col">Дата опроса</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th scope="row">Заголовок опроса</th>
        <td><input id="zag_op"  name="zag_opr" type="text" size="30" value="<?php echo $zag_opr ?>"></td>
        <td><?php echo $dat_op ?></td>
    </tr>
    <tr>
        <th scope="row">Описание опроса</th>
        <td><textarea d="op_op" rows="4" name="op_opr" placeholder="<?php echo $op_opr ?>"></textarea></td>
        <td></td>
    </tr>
    <tr>
        <th scope="row">Варианты ответа</th>
        <td><input id="var_ot1"  name="var_otv1" type="text" value="<?php echo $var_otv1 ?>"></td>
        <td><label><input type="checkbox" checked id="var_ot_ck1" name="var_otv_ck1" > Выбрать</label></td>
    </tr>
    <tr>
        <th scope="row"> </th>
        <td><input id="var_ot2"  name="var_otv2" type="text" value="<?php echo $var_otv2 ?>"></td>
        <td><label><input type="checkbox" checked id="var_ot_ck2" name="var_otv_ck2" > Выбрать</label></td>
    </tr>
    <tr>
        <th scope="row"> </th>
        <td><input id="var_ot3"  name="var_otv3" type="text" value="<?php echo $var_otv3; ?>"></td>
        <td><label><input type="checkbox" id="var_ot_ck3" name="var_otv_ck3" onchange="addansw();"> Добавить</label></td>
    </tr>
    <tr id="ans4" style="display: none">
        <th scope="row"> </th>
        <td><input id="var_ot4"  name="var_otv4" type="text" value="<?php echo $var_otv4; ?>"></td>
        <td><label><input type="checkbox" id="var_ot_ck" name="var_otv_ck4" onchange="addansw();"> Добавить</label></td>
    </tr>
    <tr id="ans5" style="display: none">
        <th scope="row"> </th>
        <td><input id="var_ot5"  name="var_otv5" type="text" value="<?php echo $var_otv5; ?>"></td>
        <td> </label></td>
    </tr>
    <tr >
        <th scope="row"><a class="btn btn-secondary" href="/" role="button" title="Вернуться на главную страницу">Вернуться</a></th>
        <td><button id="sv_opr" type="submit" name="save_op" class="btn btn-info" title="Сохранить введенную информацию">Сохранить</button></td>
        <td></td>
    </tr>
    </tbody>
    </table>
</form>


</div>

<div class="container">
<!--<div class="container">-->
<!--        <a class="btn btn-secondary" href="/" role="button" title="Вернуться на главную страницу">Вернуться</a>-->
<!--    </div>-->
</div>


</body>
</html>

