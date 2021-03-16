<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

/**
 * [136] Modification de l'échéancier de crédit
 * 
 * Cette opération comprends les écrans :
 * - Mdr-1 : Demande modification de la date de remboursement
 * - Rdc-1 : Demande raccourcissement de la durée du crédit
 * - Adc-1 : Demande rééchelonnement du crédit
 * 
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/html/suiviCredit.php';
require_once 'lib/misc/divers.php';

require_once "lib/html/HTML_menu_gen.php";

/*{{{ Mec-1 : Modification de l'échéancier de crédit */
if ($global_nom_ecran == "Mec-1") {
    
    $MyMenu = new HTML_menu_gen("Modification de l'échéancier de crédit"); // Menu Crédit , Modification de l'échéancier de crédit
    
    $MyMenu->addItem(_("Demande modification de la date de remboursement"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mdr-1", 141, "$http_prefix/images/reech_morat.gif", "1");
    
    $MyMenu->addItem(_("Approbation modification de la date de remboursement"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Amd-1", 142, "$http_prefix/images/approb_dossier.gif", "2");
    $WhereC= " and etat = 14";
    $id_dossier = getIdDossier($global_id_client,$WhereC);
    if ($id_dossier != null) {
        $MyMenu->addItem(_("Annulation modification date de remboursement"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=And-1",120, "$http_prefix/images/annul_dossier.gif","10");
        $MyMenu->addItem(_("Rejet modification date de remboursement"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Rfd-1", 115, "$http_prefix/images/refus_dossier.gif", "13");
    }

    $MyMenu->addItem(_("Demande raccourcissement de la durée du crédit"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rdc-1", 143, "$http_prefix/images/reech_morat.gif", "3");
    
    $MyMenu->addItem(_("Approbation raccourcissement de la durée du crédit"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ard-1", 144, "$http_prefix/images/approb_dossier.gif", "4");
    
    $MyMenu->addItem(_("Annulation raccourcissement de la durée du crédit"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ald-1", 150, "$http_prefix/images/annul_dossier.gif", "5");
        
    $MyMenu->addItem(_("Demande rééchelonnement du crédit"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rdo-1", 145, "$http_prefix/images/reech_morat.gif", "6");
    
    $MyMenu->addItem(_("Approbation rééchelonnement du crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Apd-1", 110, "$http_prefix/images/approb_dossier.gif","7");
    if (hasCreditAttReechMor($global_id_client)) {

        $MyMenu->addItem(_("Annulation du rééchelonnement / moratoire"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=And-1", 120, "$http_prefix/images/annul_dossier.gif", "11");
        $MyMenu->addItem(_("Rejet du rééchelonnement / moratoire"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Rfd-1", 115, "$http_prefix/images/refus_dossier.gif", "12");
    }

    $MyMenu->addItem(_("Demande rééchelonnement crédits 'En une fois'"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Muf-1", 137, "$http_prefix/images/reech_morat.gif", "8");

    $MyMenu->addItem(_("Approbation rééchelonnement crédits 'En une fois'"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Auf-1", 138, "$http_prefix/images/approb_dossier.gif", "9");

    $MyMenu->addItem(_("Annulation rééchelonnement crédits 'En une fois'"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ruf-1", 139, "$http_prefix/images/annul_dossier.gif", "10");

    $MyMenu->addItem(_("Demande de differé en échéances"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dec-1", 141, "$http_prefix/images/reech_morat.gif", "14");
    $MyMenu->addItem(_("Approbation differé en échéances"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ade-1", 104, "$http_prefix/images/approb_dossier.gif", "15");
    $MyMenu->addItem(_("Annulation differé en échéances"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cde-1", 104, "$http_prefix/images/annul_dossier.gif", "16");
    $MyMenu->addItem(_("Rejet differé en échéances"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Rde-1", 104, "$http_prefix/images/refus_dossier.gif", "13");

    $MyMenu->addItem(_("Retour menu crédit"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-11", 0, "$http_prefix/images/back.gif", "0");
    $MyMenu->buildHTML();

    echo $MyMenu->HTMLCode;
    
}
