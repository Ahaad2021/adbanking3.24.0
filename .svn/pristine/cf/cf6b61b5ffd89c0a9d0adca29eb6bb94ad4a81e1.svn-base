<?php

/**
 * Permet de récupérer un rapport généré au format CSV
 * @package Rapports
 **/

/* La session doit être nommée avant toute utilisation ! */
session_name("ADbanking");
include 'lib/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
require_once 'lib/misc/VariablesGlobales.php';

$file_title = isset($_GET["file_title"])?$_GET["file_title"]:"export";
// Récupération du CSV
if (!isset($filename)) {
  $filename = "$csv_output.".session_id();
}

$fd = fopen($filename, 'r');
$rapport = fread($fd,filesize($filename));
fclose($fd);

$objReader = PHPExcel_IOFactory::createReader('CSV');
$objReader->setDelimiter(";");
$objReader->setInputEncoding('UTF-8');
$objPHPExcel = $objReader->load($filename);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$tempXls = '/tmp/'.$file_title.'.xls';
$objWriter->save($tempXls);

// Envoi
$filename = $tempXls;
$fd = fopen($filename, 'r');
$rapport = fread($fd,filesize($filename));
fclose($fd);
$len = strlen($rapport);
header("Content-type: application/vnd.ms-excel");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=$file_title.xls");
print $rapport;

?>
