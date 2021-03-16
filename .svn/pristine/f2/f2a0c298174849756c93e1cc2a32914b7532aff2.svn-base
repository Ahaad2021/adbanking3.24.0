<?php

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

/**
 * [102] Gestion de la ligne de crédit
 *  * 
 * @package Ligne de Credit
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

/*{{{ Lcr-1 : Gestion de la ligne de crédit */
if ($global_nom_ecran == "Lcr-1") {
    
    $MyMenu = new HTML_menu_gen("Gestion ligne de crédit"); // Menu Ligne de crédit
    
    $MyMenu->addItem(_("Mise en place dossier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LAdo-1", 600, "$http_prefix/images/nouveau_dossier.gif", "1");
    
    $MyMenu->addItem(_("Approbation dossier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LApd-1", 601, "$http_prefix/images/approb_dossier.gif", "2");
    
    $MyMenu->addItem(_("Déboursement des fonds"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LDbd-1", 604, "$http_prefix/images/deboursement.gif", "3");
    
    $MyMenu->addItem(_("Remboursement crédit"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LRcr-1", 607, "$http_prefix/images/remboursement.gif", "4");
    
    $MyMenu->addItem(_("Réalisation garanties"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LRga-1", 608, "$http_prefix/images/realisation_gar.gif", "5");
    
    $MyMenu->addItem(_("Consultation dossier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LCdo-1", 606, "$http_prefix/images/consult_dossier.gif", "6");
    
    $MyMenu->addItem(_("Modification dossier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LMdd-1", 605, "$http_prefix/images/modif_dossier.gif", "7");
    
    $MyMenu->addItem(_("Correction d'un dossier de crédit"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LCdd-1", 609, "$http_prefix/images/correct_dossier.gif","8");
    
    $MyMenu->addItem(_("Annulation d'un dossier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LAnd-1", 603, "$http_prefix/images/annul_dossier.gif", "9");
    
    $MyMenu->addItem(_("Rejet d'un dossier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LRfd-1", 602, "$http_prefix/images/refus_dossier.gif", "10");

    $MyMenu->addItem(_("Clôturer la ligne de crédit"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LCdr-1", 610, "$http_prefix/images/refus_dossier.gif", "11");
    
    $MyMenu->addItem(_("Retour Menu Crédit"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-11", 0, "$http_prefix/images/back.gif", "0");
    $MyMenu->buildHTML();

    echo $MyMenu->HTMLCode;
    
}
