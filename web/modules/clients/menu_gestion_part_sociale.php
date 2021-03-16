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

if ($global_nom_ecran == "Mgp-1") {
	/* {{{ Mps-1 : Menu Gestion parts sociales */
	
	$AGC = getAgenceDatas ( $global_id_agence );
	$nbre_part_max = $AGC ['nbre_part_social_max_cli'];
	//check souscrit
	$nbre_part = getNbrePartSoc ( $global_id_client ); // returns an object
	$nbrePS_souscrit = $nbre_part->param [0] ['nbre_parts'];

	//check if has ps liberer pour fr demande transfert
	$nbre_part_lib = getNbrePartSocLib($global_id_client);
	$nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib'];
	
	//souscription permissible de l'agence
	$souscription_ouvert = checkSouscription();
	
	$MyMenu = new HTML_menu_gen ( "Menu gestion parts sociales" ); // Menu gestion de parts sociales

	//Consultation compte PS
	$MyMenu->addItem(_("Consultation compte de parts sociales"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Cps-1",26, "$http_prefix/images/consultation_client.gif","1");

	if(($nbrePS_souscrit < $nbre_part_max)||( $nbre_part_max == 0)){
	    $MyMenu->addItem(_("Souscription parts sociales"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Sps-1", 20, "$http_prefix/images/ajout_client.gif","2");
	}

	// Doit avoir au moins une part liberee pour un transfert
	if($nbrePSlib != 0) {
		$MyMenu->addItem(_("Transfert parts sociales"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mps-1",21, "$http_prefix/images/gestion_relations.gif","3");
	}

	if(($nbrePS_souscrit > 0)&&($nbrePSlib != $nbrePS_souscrit) ){
		$MyMenu->addItem ( _ ( "Libération parts sociales" ), "$PHP_SELF?m_agc=" . $_REQUEST ['m_agc'] . "&prochain_ecran=Lps-1", 28, "$http_prefix/images/retrait.gif", "4" );
	}


	$MyMenu->addItem ( _ ( "Retour menu clients" ), "$PHP_SELF?m_agc=" . $_REQUEST ['m_agc'] . "&prochain_ecran=Gen-9", 0, "$http_prefix/images/back.gif", "0" );
	$MyMenu->buildHTML ();
	
	echo $MyMenu->HTMLCode;
}


//else
  //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran non trouvé : " . $global_nom_ecran
?>