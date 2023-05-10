<?php
require_once(__DIR__.'/../functions.php');
if (array_key_exists("rolTotal",request()->input('input'))) unset(request()->input('input')['rolTotal']);
$col = array();
$val = array();
if (isset(request()->input('input')['previous'])) unset(request()->input('input')['previous']);
foreach (request()->input('input') as $key => $value)
{
    if (strpos($key,"date") !== false) $value = DateTime::createFromFormat("d/m/Y H:i:s",$value)->format('Y-m-d H:i:s');
    $col[] = "`".$key."`";
    $val[] = "'".$mysqli->real_escape_string($value)."'";
}
$sql = "INSERT INTO finance_report_history (".implode(",",$col).") VALUES (".implode(",",array_fill(0,count($val),'?')).")";
$res = prepareExecuteQuery($sql,str_repeat('s',count($val)),$val);
?>