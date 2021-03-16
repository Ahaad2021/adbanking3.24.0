<?php
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/html/HtmlHeader.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/dbProcedures/jasper_report.php';

function html_jasper_param ( $code_rapport,& $MyPage){
	$rapports= getJasperRapportsCodeByLibel($code_rapport );
	$param=getJasperParamsRapports($code_rapport);
	$param=$param->param;
	$MyPage->setTitle ($rapports[$code_rapport]);
	$tabParam =array();
	foreach($param AS $key => $valeur) {
		$MyPage->addField(trim($key), $valeur['libel'],trim( $valeur['type_param']));
		
                $is_required = true;
		if(trim( $valeur['type_param']) == "dtg") {
                    $tabParam[$key]= array("HTML_GEN_date_".$key,trim( $valeur['type_param']));
                } elseif (trim( $valeur['type_param']) == "lsb") {

                    $dataType = 'int'; //trim( $valeur['type_param'])

                    $tabParam[$key]= array("HTML_GEN_LSB_".$key, $dataType);

                    $MyPage->setFieldProperties(trim($key), FIELDP_HAS_CHOICE_AUCUN, false);
                    $MyPage->setFieldProperties(trim($key), FIELDP_HAS_CHOICE_TOUS, true);

                    // Populate lsb
                    $liste_options = getJasperParamLsbOptions(trim($key));
                    $MyPage->setFieldProperties(trim($key), FIELDP_ADD_CHOICES, $liste_options);

                    $is_required = false;
		} else {
		    $tabParam[$key]=array($key,trim( $valeur['type_param']));
		}

                $MyPage->setFieldProperties(trim($key), FIELDP_IS_REQUIRED, $is_required);
	}
	return $tabParam;
}
/**
 * Fonction  : qui renvoie l'extension du fichier
 * @param $filename
 * @return String ext renvoie l'extension du fichier
 */

function renvoiExtFile ($filename)
{
	$filename = strtolower($filename) ;
	$exts = split("[/\\.]", $filename) ;
	$n = count($exts)-1;
	$exts = $exts[$n];
	return $exts;
}

?>