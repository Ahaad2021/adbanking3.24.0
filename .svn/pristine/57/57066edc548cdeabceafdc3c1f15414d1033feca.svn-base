<?php
/* Défection d'un client.
   TF - 20/02/2002 */

require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/agence.php');
require_once('lib/dbProcedures/compte.php');
require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/guichet.php');
require_once('lib/dbProcedures/rapports.php');
require_once('lib/misc/divers.php');
require_once('lib/misc/VariablesSession.php');
require_once('lib/misc/VariablesGlobales.php');
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/handleDB.php';
require_once 'lib/html/HTML_erreur.php';

if ($global_nom_ecran == 'Sdc-1') {
	
  $balance = getBalance($global_id_client);
  //debug($balance);
  $Title = _("Situation finale du client");
  $myForm = new HTML_GEN2($Title);

  // Si le client est EAV, la balance est de toute manière à 0
  $CLI = getClientDatas($global_id_client);
  
  if ($CLI["etat"] != 1) {
    // Recherche des comptes services financiers
    $CPTS = get_comptes_epargne($global_id_client);
    // Ajout du compte de parts sociales si existant
    $idCptPS = getPSAccountID($global_id_client);
    if ($idCptPS != NULL) {
      $CPTS[$idCptPS] = getAccountDatas($idCptPS);
    }
    while (list($key, $value) = each($CPTS)) {
      setMonnaieCourante($value["devise"]);
      $infos_simul = simulationArrete($key);
      $soldeCloture = $infos_simul["solde_cloture"];

      $myForm->addField("num_cpte".$key, _("Numéro de compte"), TYPC_TXT);
      $myForm->addField("type_cpte".$key, _("Produit"), TYPC_TXT);
      $myForm->addField("solde".$key, _("Solde"), TYPC_MNT);
      $myForm->setFieldProperties("num_cpte".$key, FIELDP_DEFAULT, $value["num_complet_cpte"]);
      $myForm->setFieldProperties("type_cpte".$key, FIELDP_DEFAULT, $value["libel"]);
      $myForm->setFieldProperties("solde".$key, FIELDP_DEFAULT, $soldeCloture);
      $myForm->setFieldProperties("solde".$key, FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("num_cpte".$key, FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("type_cpte".$key, FIELDP_IS_LABEL, true);
      $myForm->addHTMLExtraCode("line".$key, "<BR>");
    }

    /* Récupération des dossiers de crédit à l'état 'Fonds déboursés' ou 'En attente de rééchel/Moratoire' ? */
    $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
    $dossiers = getIdDossier($global_id_client, $whereCl);
    foreach($dossiers as $id_doss=>$value) {
      $solde_credit = 0;
      $myErr = simulationArreteCpteCredit($solde_credit,$id_doss);
      if ($myErr->errCode == NO_ERR) {
        $devise_cre = $myErr->param; // Devise du crédit
        setMonnaieCourante($devise_cre);
        $myForm->addField("credit.$id_doss", _("Solde crédit N°$id_doss en cours"), TYPC_MNT);
        $myForm->setFieldProperties("credit.$id_doss", FIELDP_DEFAULT, ($solde_credit * -1));
        $myForm->setFieldProperties("credit.$id_doss", FIELDP_IS_LABEL, true);
        $myForm->addHTMLExtraCode("line_cre.$id_doss", "<br/>");
      }

      // Recherche d'éventuels comptes d'épargne nantie numéraires du client ni restitués ni réalisés
      $liste_gar = getListeGaranties($id_doss);
      foreach($liste_gar as $key=>$val ) {
        /* la garantie doit être numéraire, non restituée et non réalisée  */
        if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
          /* Récupération des infos sur le compte nantie */
          $nantie = $val['gar_num_id_cpte_nantie'];
          $infoNantie = getAccountDatas($nantie);
          $infos_simul = simulationArrete($nantie);
          $infoNantie["solde"] = $infos_simul["solde_cloture"];
          if ($infoNantie['id_titulaire'] == $global_id_client) {
            setMonnaieCourante($devise_cre);
            $myForm->addField("num_cpte".$nantie."".$id_doss, _("Numéro de compte dossier N°").$id_doss, TYPC_TXT);
            $myForm->addField("type_cpte".$nantie."".$id_doss, _("Produit"), TYPC_TXT);
            $myForm->addField("solde".$nantie."".$id_doss, _("Solde"), TYPC_MNT);
            $myForm->setFieldProperties("num_cpte".$nantie."".$id_doss, FIELDP_DEFAULT, $infoNantie["num_complet_cpte"]);
            $myForm->setFieldProperties("type_cpte".$nantie."".$id_doss, FIELDP_DEFAULT, $infoNantie["libel"]);
            $myForm->setFieldProperties("solde".$nantie."".$id_doss, FIELDP_DEFAULT, $infoNantie["solde"]);
            $myForm->setFieldProperties("solde".$nantie."".$id_doss, FIELDP_IS_LABEL, true);
            $myForm->setFieldProperties("num_cpte".$nantie."".$id_doss, FIELDP_IS_LABEL, true);
            $myForm->setFieldProperties("type_cpte".$nantie."".$id_doss, FIELDP_IS_LABEL, true);
            $myForm->addHTMLExtraCode("line".$nantie."".$id_doss, "<BR>");
          }
        }
      }
    }
  }

  foreach ($balance as $devise => $montant) {
    setMonnaieCourante($devise);
    $myForm->addField("balance.$devise", "Balance $devise", TYPC_MNT);
    $myForm->setFieldProperties("balance.$devise", FIELDP_DEFAULT, $montant);
    $myForm->setFieldProperties("balance.$devise", FIELDP_IS_LABEL, true);
  }

  // Comptes d'épargne nantie servant de garanties pour d'autres crédits
  // N'interviennent pas dans la balance
  $cptNantisBloqyes = array();
  $DCR = getDossierCreditsGarantis($global_id_client);
  if (is_array($DCR)) {
    while (list($key, $dcr) = each($DCR)) {
      if ($dcr["id_client"] != $global_id_client) {
        $cptNantisBloques[$dcr["gar_num_id_cpte_nantie"]] = getAccountDatas($dcr["gar_num_id_cpte_nantie"]);
        $cptNantisBloques[$dcr["gar_num_id_cpte_nantie"]]["id_client"] = $dcr["id_client"];
        $cptNantisBloques[$dcr["gar_num_id_cpte_nantie"]]["nomClient"] = $dcr["nomClient"];
      }
    }
    if (sizeof($cptNantisBloques) > 0) {
      $myForm->addHTMLExtraCode("epargne_nantie_bloquee", "<H5 align=\"center\"> "._("Comptes d'épargne bloqués comme garanties d'autres crédits")." </H5>");
      while (list($key, $value) = each($cptNantisBloques)) {
        setMonnaieCourante($value["devise"]);
        $infos_simul = simulationArrete($key);
        $soldeCloture = $infos_simul["solde_cloture"];
        $myForm->addField("num_cpte".$key, _("Numéro de compte"), TYPC_TXT);
        $myForm->addField("type_cpte".$key, _("Produit"), TYPC_TXT);
        $myForm->addField("solde".$key, _("Solde"), TYPC_MNT);
        $myForm->addField("id_client".$key, _("N° du client"), TYPC_INT);
        $myForm->addField("nom_client".$key, _("Nom du client"), TYPC_TXT);
        $myForm->setFieldProperties("num_cpte".$key, FIELDP_DEFAULT, $value["num_complet_cpte"]);
        $myForm->setFieldProperties("type_cpte".$key, FIELDP_DEFAULT, $value["libel"]);
        $myForm->setFieldProperties("solde".$key, FIELDP_DEFAULT, $soldeCloture);
        $myForm->setFieldProperties("id_client".$key, FIELDP_DEFAULT, $value["id_client"]);
        $myForm->setFieldProperties("nom_client".$key, FIELDP_DEFAULT, $value["nomClient"]);
        $myForm->setFieldProperties("solde".$key, FIELDP_IS_LABEL, true);
        $myForm->setFieldProperties("num_cpte".$key, FIELDP_IS_LABEL, true);
        $myForm->setFieldProperties("type_cpte".$key, FIELDP_IS_LABEL, true);
        $myForm->setFieldProperties("id_client".$key, FIELDP_IS_LABEL, true);
        $myForm->setFieldProperties("nom_client".$key, FIELDP_IS_LABEL, true);
        $myForm->addHTMLExtraCode("line".$key, "<BR>");
      }
    }
  }
  $myForm->addFormButton(1,1,"retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-9');

  // Si le client a un abonnement, il faut le siganler de désabonner de ce service
  if(count(getClientAbonnementInfo(generateIdentifiant()))>0){
      $myForm->addHTMLExtraCode("note", "<h4 style='color: red; text-align: center'>NOTE: Il faut désabonner le client avant de faire la défection</h4>");
  }

  $myForm->buildHTML();
  echo $myForm->getHTML();
  $dbHandler->closeConnection ( true );
  // Ajout dans l'historique
  $erreur = ajout_historique (17, $global_id_client, _ ( "Simulation défection client" ), $global_nom_login, date( "r" ), NULL);
  if ($erreur->errCode != NO_ERR) {
  	$dbHandler->closeConnection ( false );
  	return $erreur;
  }
  $dbHandler->closeConnection ( true );
  
  
} else{
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' non pris en charge"
}
?>
