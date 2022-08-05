<?php
require_once('../functions.php');
if (array_key_exists("rolTotal",$_POST['input'])) unset($_POST['input']['rolTotal']);
$col = array();
$val = array();
if (isset($_POST['input']['previous'])) unset($_POST['input']['previous']);
foreach ($_POST['input'] as $key => $value)
{
    if (strpos($key,"date") !== false) $value = DateTime::createFromFormat("d/m/Y H:i:s",$value)->format('Y-m-d H:i:s');
    $col[] = "`".$key."`";
    $val[] = "'".mysqli_real_escape_string($mysqli,$value)."'";
}
$sql = "INSERT INTO finance_report_history (".implode(",",$col).") VALUES (".implode(",",$val).")";
$res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);
?>