<?php
require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/parametrage.php');
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';
require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/dbProcedures/historique.php' ;


if ($global_nom_ecran == "Gca-1"){

    $MyMenu = new HTML_menu_gen(_("Gestions des cartes ATM"));
    $MyMenu->addItem(_("Listes des commandes à envoyer pour impression"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Lci-1", 808, "$http_prefix/images/menu_compta.gif","1");
    $MyMenu->addItem(_("Import des cartes imprimées"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ici-1", 809, "$http_prefix/images/rapport_credit.gif","2");
    $MyMenu->addItem(_("Historique des commandes envoyées"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Hic-1", 810, "$http_prefix/images/visualisation_toutes_trans.gif","3");
    $MyMenu->addItem(_("Liste de toutes les cartes"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ldc-1", 811, "$http_prefix/images/visu_dernier_rapport.gif","4");
    $MyMenu->addItem(_("Les rapports"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rat-1", 812, "$http_prefix/images/rapport.gif","5");
    $MyMenu->addItem(_("Retour Menu Principal"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-6", 0, "$http_prefix/images/back.gif","0");
    $MyMenu->buildHTML();
    echo $MyMenu->HTMLCode;

}

?>