<?php
/* Menu Transfert  parts sociales
    TF - 07/01/2015  T361          */

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

if ($global_nom_ecran == "Mps-1") {
	/* {{{ Mps-1 : Menu Transferts parts sociales */
	// check if demande exist then affiche lien approbation
	$demande_transfert = array ();
	$whereCl = " AND (etat_transfert=1)";
	$demande_transfert = getInfoDemandePS ( $global_id_client, $whereCl );
	//check if has ps liberer pour fr demande transfert
	$nbre_part_lib = getNbrePartSocLib($global_id_client);
	$nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib'];
	
	$MyMenu = new HTML_menu_gen ( "Transfert de parts sociales" ); // Menu gestion de parts sociales
	
	if(( empty ( $demande_transfert ) ) && $nbrePSlib > 0){
		$MyMenu->addItem ( _ ( "Demande de transfert parts sociales" ), "$PHP_SELF?m_agc=" . $_REQUEST ['m_agc'] . "&prochain_ecran=Dps-1", 22, "$http_prefix/images/reech_morat.gif", "1" );
	}
	if (! empty ( $demande_transfert )) {
		$MyMenu->addItem ( _ ( "Approbation / Rejet de transfert parts sociales" ), "$PHP_SELF?m_agc=" . $_REQUEST ['m_agc'] . "&prochain_ecran=Aps-1", 23, "$http_prefix/images/approb_dossier.gif", "2" );
	}
	$MyMenu->addItem ( _ ( "Retour menu clients" ), "$PHP_SELF?m_agc=" . $_REQUEST ['m_agc'] . "&prochain_ecran=Gen-9", 0, "$http_prefix/images/back.gif", "0" );
	$MyMenu->buildHTML ();
	
	echo $MyMenu->HTMLCode;
}
	
//else
  //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran non trouvé : " . $global_nom_ecran
?>