<?php

/**
 * recherche_compte Interface de choix d'un compte comptable
 * la variable $shortname soit avoir été postée et contient le nom du champ attaché au lien Rechercher
 * @package Ifutilisateur
 */

require_once 'lib/html/FILL_HTML_GEN2.php';

$JScode_1 .="if (document.ADForm.HTML_GEN_LSB_cpte_centralise.value=='0')
            return false;\n";
$JScode_1 .= " opener.document.ADForm.".$shortName.".focus();opener.document.ADForm.".$shortName.".value =document.ADForm.HTML_GEN_LSB_cpte_centralise.value ;opener.document.ADForm.".$shortName.".blur();window.close();";


$MyPage = new HTML_GEN2(_("Recherche de compte"));
//Recupération des comptes comptables actifs
$cptes_actifs = getComptesActifs();

$MyPage->addField("cpte_centralise",_("Compte"), TYPC_LSB);
$MyPage->setFieldProperties("cpte_centralise", FIELDP_ADD_CHOICES, $cptes_actifs);
// $MyPage->addTable("ad_cpt_comptable", OPER_INCLUDE, array("cpte_centralise"));
// $MyPage->setFieldProperties("cpte_centralise", FIELDP_LONG_NAME, "Compte");
$MyPage->addFormButton(1,1, "ok", _("Valider"), TYPB_SUBMIT);
$MyPage->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>$JScode_1));
$MyPage->addFormButton(1,2, "butret", _("Annuler"), TYPB_SUBMIT);
$MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
$MyPage->setFormButtonProperties("butret", BUTP_JS_EVENT, array("onclick" => "window.close();"));

$MyPage->buildHTML();
echo $MyPage->getHTML();

?>