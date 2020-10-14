<?php
/**
 * Created by PhpStorm.
 * User: constantin
 * Date: 18.02.20
 * Time: 13:57
 */
session_start();

if ((!isset($_SESSION['payer'][0]))) {
    header('Location: user_lc.php');
    exit;
//    echo "нету  {$_SESSION['payer'][0]}";
}
//echo "{$_SESSION['payer'][1]}";
// берем исходную сумму к оплате
$new_sum=$_SESSION['payer'][3];
// проверяем гет
if (isset($_GET['message'])) {
//     если есть пишем новую сумму
    $new_sum=$_GET['message'];
    if (!isset($_SESSION['new_sum'])) $_SESSION['new_sum']=$_GET['message'];
        elseif ($_SESSION['new_sum']!=$_GET['message']) $_SESSION['new_sum']=$_GET['message'];
}
//$new_sum = isset($_GET['message']) ? $_GET['message'] : '0';

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
    <!--<div class="d-flex p-2 bd-highlight">Показания приборов учета</div>-->
    <!--    скрипт альфы -->
    <script>
        function ValChange() {
            var message = location.search;
            message = document.getElementById('summ').value;
            window.location.href = 'pay_page.php?message='+message;
        }
    </script>
    <script
        id="alfa-payment-script"
        type="text/javascript"
        src="https://testpay.alfabank.ru/assets/alfa-payment.js">
    </script>

</head>
<body>
<div class="container-fluid">
    <h3><strong> Оплата услуг банковской картой</strong></h3>
<!--    <hr>-->
    <!--    определяем переменные для классов платежных данных альфы -->
    <table  class="table">
        <tr>
            <td>Дата платежа: </td>
            <td ><input class='orderDate' type='text' size="40" readonly value=<?php echo "{$_SESSION['payer'][4]}" ?>></td>
        </tr>
        <tr>
            <td>Номер платежного документа: </td>
            <td ><input class='orderNumber' type='text' size="40" readonly value=<?php echo "{$_SESSION['new_num_pay']}" ?>></td>
        </tr>
        <tr>
            <td >Лицевой счет: </td>
            <td ><input class='clientLicSchet' type='text' size="40" readonly value=<?php echo "{$_SESSION['payer'][0]}" ?>></td>
        </tr>
        <tr>
            <td>ФИО: </td>
            <td> <input class='clientInfo' type='text' readonly size="40" value="<?php echo "{$_SESSION['payer'][1]}" ?>"></td>
        </tr>
        <tr>
            <td>Адрес: </td>
            <td> <input class='clientAdress' type='text' size="40" readonly value="<?php echo "{$_SESSION['payer'][2]}" ?>"></td>
        </tr>
        <tr>
            <td>Сумма к оплате: </td>
            <td> <input class='amount' id="summ" name="sum_pay" type='text' size="40" onchange="ValChange()" value=<?php echo "{$new_sum}" ?> ></td>
<!--            pattern="\d{3,6}\,\d{2}"-->
        </tr>
    </table>
<!--    показываем кнопку пока только ильину, больше никому -->
<!--<hr>-->
    <!--        оплата картой альфы -->
<div id="alfa-payment-button"
     data-token='ogko6ep0tt1tskjqhttu3k781n'
     data-client-info-selector='.clientInfo'
     data-amount-selector='.amount'
     data-add-clientLicSchet-selector='.clientLicSchet'
     data-add-clientAdress-selector='.clientAdress'
     data-version='1.0'
     data-order-number-selector='.orderNumber'
     data-language='ru'
     data-stages='1'
     data-amount-format='rubli'
     data-description='Оплата коммунальных услуг'
     data-return-url='http://host1697773.hostland.pro/pay_ok.php'
     data-fail-url='http://host1697773.hostland.pro/pay_no.php'>

<!--    data-amount-format='kopeyki'-->
</div>
    <a class="btn btn-secondary" href="user_lc.php" role="button" title="Вернуться в личный кабинет">Вернуться</a>
    <hr>
<h4 id="pravila_oplati_i_bezopasnost_platezhey_konfidentsialnost_informatsii" class="heading heading_size_m"><span> Правила оплаты и безопасность платежей, конфиденциальность информации</span></h4>

<p class="paragraph">
    <span>Оплата банковскими картами осуществляется через АО «АЛЬФА-БАНК».</span><br>
</p>
<p class="paragraph">
    <img src="images/AlfaBank.png" title=""/><span> </span>
    <br>
</p>
<p class="paragraph">
    <span>К оплате принимаются карты VISA, MasterCard, МИР.</span><br>
</p>
<table>
    <tbody>
    <colgroup width="33.333%"></colgroup>
    <tr>
        <td style="text-align:left"><img src="images/_Visa.png" title=""/><span>           </span>
        </td>
        <td style="text-align:left"><img src="images/Mastercard-logo.svg-8.png" title=""/><span>             </span>
        </td>
        <td style="text-align:left"><img src="images/logo_mir-50.png" title=""/><span>            </span>
        </td>
    </tr>
    </tbody>
</table>


<p class="paragraph">
    <span>Услуга оплаты через интернет осуществляется в соответствии с Правилами международных платежных систем Visa, MasterCard и Платежной системы МИР на принципах соблюдения конфиденциальности и безопасности совершения платежа, для чего используются самые современные методы проверки, шифрования и передачи данных по закрытым каналам связи. Ввод данных банковской карты осуществляется на защищенной платежной странице АО «АЛЬФА-БАНК».</span><br>
</p>
<p class="paragraph">
    <span>На странице для ввода данных банковской карты потребуется ввести </span><b><span>данные банковской карты</span></b><span>: номер карты, имя владельца карты, срок действия карты, трёхзначный код безопасности (CVV2 для VISA, CVC2 для MasterCard, Код Дополнительной Идентификации для МИР). Все необходимые данные пропечатаны на самой карте. Трёхзначный код безопасности — это три цифры, находящиеся на обратной стороне карты.</span>
    <br>
</p>
<p class="paragraph">
    <span>Далее вы будете перенаправлены на страницу Вашего банка для ввода кода безопасности, который придет к Вам в СМС. Если код безопасности к Вам не пришел, то следует обратиться в банк выдавший Вам карту.</span><br>
</p>
<table>
    <tbody>
    <colgroup width="33.333%"></colgroup>
    <tr>
        <td style="text-align:left"><img src="images/_verified-by-visa-40.png" title=""/><span>           </span>
        </td>
        <td style="text-align:left"><img src="images/_mastercard-securecode-50.png" title=""/><span>             </span>
        </td>
        <td style="text-align:left"><img src="images/MIRaccept-40.png" title=""/><span>            </span>
        </td>
    </tr>
    </tbody>
</table>


<p class="paragraph">
    <span>Случаи отказа в совершении платежа: </span><br>
</p>
<ul class="list">
    <li class="list__item"><span>банковская карта не предназначена для совершения платежей через интернет, о чем можно узнать, обратившись в Ваш Банк;</span></li>
    <li class="list__item"><span>недостаточно средств для оплаты на банковской карте. Подробнее о наличии средств на банковской карте Вы можете узнать, обратившись в банк, выпустивший банковскую карту;</span></li>
    <li class="list__item"><span>данные банковской карты введены неверно;</span></li>
    <li class="list__item"><span>истек срок действия банковской карты. Срок действия карты, как правило, указан на лицевой стороне карты (это месяц и год, до которого действительна карта). Подробнее о сроке действия карты Вы можете узнать, обратившись в банк, выпустивший банковскую карту;</span></li>
</ul>
<p class="paragraph">
    <span>По вопросам оплаты с помощью банковской карты и иным вопросам, связанным с работой сайта, Вы можете обращаться по следующим телефонам: </span><b><span>(3952) 40-40-55, +7 902 512 6055</span></b><span>.</span>
    <br>
</p>
<p class="paragraph">
    <span>Предоставляемая вами персональная информация (имя, адрес, телефон, e-mail, номер банковской карты) является конфиденциальной и не подлежит разглашению. Данные вашей кредитной карты передаются только в зашифрованном виде и не сохраняются на нашем Web-сервере.</span><br>
</p>
</div>
</body>
</html>

