<?php

/**
 * [60] Gestion des annulation retrait et dépôt
 *  *
 * @package Annulation Retrait et Dépôt
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/annulation_retrait_depot.php';
require_once 'lib/misc/divers.php';

require_once "lib/html/HTML_menu_gen.php";

/*{{{ Gae-1 : Gestion des annulations retrait et dépôt */
if ($global_nom_ecran == "Gae-1") {

    global $global_id_client;

    $MyMenu = new HTML_menu_gen("Gestion des annulations retrait et dépôt");

    $MyMenu->addItem(_("Demande d'annulation"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dae-1", 61, "$http_prefix/images/traitement_chq.gif", "1");

    $MyMenu->addItem(_("Approbation demande d'annulation"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Aae-1", 62, "$http_prefix/images/approb_dossier.gif", "2");

    $MyMenu->addItem(_("Effectuer l'annulation"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Eae-1", 63, "$http_prefix/images/annulation.gif", "3");

    $MyMenu->addItem(_("Retour Menu Epargne"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-10", 0, "$http_prefix/images/back.gif", "0");
    $MyMenu->buildHTML();

    echo $MyMenu->HTMLCode;

}
