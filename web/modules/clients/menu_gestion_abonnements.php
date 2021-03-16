<?php
/* Menu gestion parts sociales
     - 04/02/2015   MAJ361        */

require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/compte.php');
require_once('lib/misc/tableSys.php');
require_once('lib/dbProcedures/historique.php');
require_once('lib/dbProcedures/agence.php');
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once('modules/epargne/recu.php');
require_once 'lib/misc/VariablesGlobales.php';
require_once "lib/html/HTML_menu_gen.php";

if ($global_nom_ecran == "Abn-1") {
	/* {{{ Abn-7 : Menu Gestion des Abonnements */

	$MyMenu = new HTML_menu_gen ( "Menu gestion des abonnements" ); // Menu gestion de parts sociales

	//Consultation compte PS
	$MyMenu->addItem(_("Commande de cartes"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Crt-2", 12, "$http_prefix/images/consultation_client.gif","1");
	$MyMenu->addItem(_("Liste des abonnements"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Abn-7", 12, "$http_prefix/images/modif_client.gif","2");


	$MyMenu->addItem ( _ ( "Retour menu clients" ), "$PHP_SELF?m_agc=" . $_REQUEST ['m_agc'] . "&prochain_ecran=Gen-9", 0, "$http_prefix/images/back.gif", "0" );
	$MyMenu->buildHTML ();
	
	echo $MyMenu->HTMLCode;
}


//else
  //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran non trouvé : " . $global_nom_ecran
?>