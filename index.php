<?php

require_once('./smarty/Smarty.class.php');
session_start();
$smarty = new Smarty();
$db = new mysqli('localhost', 'root', '', 'restauracja');

$smarty->setTemplateDir('./templates');
$smarty->setCompileDir('./templates_c');
$smarty->setCacheDir('./cache');
$smarty->setConfigDir('./configs');


if(isset($_REQUEST['action'])) {
    switch($_REQUEST['action']) {
        case 'reservation':
            
            $smarty->display('reservation.tpl');
            
        break;
        case 'processReservation':
            $date =$_REQUEST['date'];
            $time =$_REQUEST['time'];
            $query = $db->prepare("
            WITH recursive Date_Ranges AS(
            SELECT CURDATE() as Date
            UNION ALL
            SELECT Date + INTERVAL 1 DAY
            FROM Date_Ranges
            WHERE Date <CURDATE() +INTERVAL 13 DAY)
            SELECT Date, id, max_liczba, rodzaj FROM Date_Ranges, tables
            WHERE (id, Date) NOT IN (SELECT id_table, date from reservation) AND Date = ? 
            
            ");
            $query->bind_param("s", $date);
            $query->execute();
            $result = $query->get_result();
            $tables = array();
            while ($row = $result->fetch_assoc()) {
                array_push($tables, $row);
            }
            $smarty->assign('tables', $tables);
            $smarty->assign('date', $date);
            $smarty->assign('time', $time);
       
            $smarty->display('reservation2.tpl');
        break;
        case 'accept':
            $six_digit_random = mt_rand(100000, 999999);
            $query = $db->prepare("INSERT INTO reservation(id, number_tel, date, time, id_table, random) 
                                    VALUES (NULL,?,?,?,?,?)");
            $query->bind_param("issii",$_REQUEST['tel'],$_REQUEST['date'], $_REQUEST['time'],$_REQUEST['tables'],$six_digit_random);
            $query->execute();
         header('Location:index.php');
        break;
        case 'back':
            $smarty->display('reservation.tpl');
        break;
        default:
        $smarty->display('index.tpl');
        break;
    }
} else {
    $smarty->display('index.tpl');
}

?>
