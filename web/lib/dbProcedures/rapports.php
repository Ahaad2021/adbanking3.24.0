<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2: */
/**
 * @package Rapports
 */
require_once 'lib/dbProcedures/interface.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/dbProcedures/login_func.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/utilisateurs.php';
require_once 'lib/dbProcedures/compte.php';

function recherche_clients($DATA, $indice,$limit = 0) { //Renvoie un array contenant les ID des clients correspondants aux critères de recherche
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_client FROM ad_cli WHERE id_ag = $global_id_agence AND ";

  if ($DATA['statut_jur'] > 0)
    $sql .= "(statut_juridique=" . $DATA['statut_jur'] . ") AND ";
  if ($DATA['qualite'] > 0)
    $sql .= "(qualite=" . $DATA['qualite'] . ") AND ";
  if ($DATA['id_loc1'] > 0)
    $sql .= "(id_loc1=" . $DATA['id_loc1'] . ") AND ";
  if ($DATA['id_loc2'] > 0)
    $sql .= "(id_loc2=" . $DATA['id_loc2'] . ") AND ";
  if ($DATA['id_loc3'] > 0)
    $sql .= "(id_loc3=" . $DATA['id_loc3'] . ") AND ";
  if ($DATA['id_loc4'] > 0)
    $sql .= "(id_loc4=" . $DATA['id_loc4'] . ") AND ";
  if ($DATA['date_adh_min'] != '')
    $sql .= "(date_adh>='" . $DATA['date_adh_min'] . "') AND ";
  if ($DATA['date_adh_max'] != '') {
    $date = splitEuropeanDate($DATA['date_adh_max']);
    $date2 = date("d/m/Y", mktime(0, 0, 0, $date[1], $date[0] + 1, $date[2]));
    $sql .= "(date_adh<'" . $date2 . "') AND ";
  }
  if ($DATA['date_rupt_min'] != '')
    $sql .= "(date_defection>='" . $DATA['date_rupt_min'] . "') AND ";
  if ($DATA['date_rupt_max'] != '') {
    $date = splitEuropeanDate($DATA['date_rupt_max']);
    $date2 = date("d/m/Y", mktime(0, 0, 0, $date[1], $date[0] + 1, $date[2]));
    $sql .= "(date_defection<'" . $date2 . "') AND ";
  }
  if ($DATA['sect_act'] > 0)
    $sql .= "(sect_act=" . $DATA['sect_act'] . ") AND ";
  if ($DATA['gest'] > 0)
    $sql .= "(gestionnaire=" . $DATA['gest'] . ") AND ";
  if ($DATA['lang'] > 0)
    $sql .= "(langue=" . $DATA['lang'] . ") AND ";
  if ($DATA['etat'] > 0)
    $sql .= "(etat=" . $DATA['etat'] . ") AND ";

  if ($DATA['n_guichet'] > 0)
      $sql .= "(n_guichet=" . $DATA['n_guichet'] . ") AND ";

  if ($DATA['n_agence'] > 0)
      $sql .= "(n_agence=" . $DATA['n_agence'] . ") AND ";


    //$sql .= " id_client > $indice AND ";
  //Enlève le 'AND '
  $sql = substr($sql, 0, strlen($sql) - 4);

  $sql .= "ORDER BY statut_juridique, id_client";

  if ($indice > 0) {
    $sql .= " offset $indice ";
  }
  if($limit > 0){
    $sql .= " limit ".$limit;
  }


  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $count = $result->numRows();
  $retour = array ();
  while ($row = $result->fetchrow()) {
    array_push($retour, $row[0]);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $retour);
}
function recherche_credits($DATA) { //Renvoie un array contenant les ID des crédits correspondants aux critères de recherche
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_doss FROM ad_dcr WHERE id_ag = $global_id_agence AND ";
  $len = strlen($sql);
  $sql1 = "SELECT id_doss FROM ad_etr WHERE id_ag = $global_id_agence AND (remb='f') AND";
  $len1 = strlen($sql1);
  if ($DATA['nbre_jour_retard'] > 0)
    $sql1 .= " (date_ech+" . $DATA['nbre_jour_retard'] . ">=" . date("d/m/Y") . ") AND";
  //Enlève le 'AND' de trop
  //if ($len1 == strlen($sql1))
  $sql1 = substr($sql1, 0, strlen($sql1) - 3); //Si on a rien ajouté
  if ($DATA['id_gestionnaire'] > 0)
    $sql .= "(id_agent_gest='" . $DATA['id_gestionnaire'] . "') AND ";
  if ($DATA['date_deb'] != "")
    $sql .= " (cre_date_debloc>=date(" . $DATA['date_deb'] . ")) AND ";
  if ($DATA['date_fin'] != "")
    $sql .= " (cre_date_debloc<=date(" . $DATA['date_fin'] . ")) AND ";

  $sql .= " (id_doss IN (" . $sql1 . ")) AND ";
  //Enlève le 'AND ' de trop
  $sql = substr($sql, 0, strlen($sql) - 4);

  $sql .= "ORDER BY id_doss";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $retour = array ();
  while ($row = $result->fetchrow()) {
    array_push($retour, $row[0]);
  }
  $dbHandler->closeConnection(true);
  return $retour;
}
function getLibel($table, $id) { //Renvoi un string concaténant les différents champs "onselect" de la table pour l'id demandé
  //Recup num de la table
  $valeurs = makeListFromTable($table);
  return $valeurs[$id];
}
function get_info_rapport_client($id) { //Renvoie différentes infos du client demandé
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = $id";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  } else
    if ($result->numrows() != 1) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); // "Retour inattendu"
    }
  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $retour;
}

function getSituationEpargneClient($id_client, $export_csv = false) {
  // PS qi renvoie un array avec les infos sur tous les comptes d'épargne d'un client, à l'exception du compte de PS et du compte de créfit
  global $global_id_agence;
  $id = getAgenceCpteIdProd($global_id_agence);
  $exclus = $id["id_prod_cpte_credit"] . "," . $id["id_prod_cpte_parts_sociales"];
  $cpteEpargne = getCptEpargne($id_client, $exclus); //Info sur tous les cptes d'épargne du client
  $DATA_EPARGNE = array ();
  while (list ($key, $value) = each($cpteEpargne)) {
    $data["num_complet_cpte"] = $value["num_complet_cpte"];
    $data["intitule_compte"] = $value["intitule_compte"];
    $data["id_titulaire"] = $value["id_titulaire"];
    $data["date_ouvert"] = pg2phpDate($value["date_ouvert"]);
    $data["solde_cpte"] = afficheMontant($value["solde"], false, $export_csv);
    $data["mnt_bloq"] = $value["retrait_unique"] == 't' ? "Retrait unique" : afficheMontant($value["mnt_bloq"] + $value["mnt_min_cpte"] + $value["mnt_bloq_cre"], false, $export_csv);
    $data["mnt_disp"] = $value["retrait_unique"] == 't' ? "Retrait unique" : afficheMontant(getSoldeDisponible($value["id_cpte"], false, $export_csv));
    $data["date_dernier_mvt"] = pg2phpDate(getLastMvtCpt($value["id_cpte"]));
    //      $data["date_dernier_mvt"] = pg2phpDate(getLastMvtCpt($value["num_complet_cpte"]));// pas bon
    $data["libel_prod"] = getLibelPrdt($value["id_prod"], "adsys_produit_epargne");
    $data["solde_calcul_interets"] = afficheMontant($value["solde_calcul_interets"], false, $export_csv);
    $data["devise"] = $value["devise"];
    array_push($DATA_EPARGNE, $data);
  }
  return $DATA_EPARGNE;
}

/**
 * Fonction utilisée par le rapport tableau de resulat trimestriel
 * Fonction : nombre d'epargnant à une date donnée(membre ayant au moins un compte d'épargne'), classé par leur status juridique et de leurs sexe
 * @param $date date à laquelle on veut connaitre le nombre d'epargnant
 * return
 */
function getNbreEpargnant($date){
	 global $global_id_agence;
	$sql="select count(id_client) as nbre_epargant,statut_juridique,pp_sexe FROM ad_cli where id_ag=$global_id_agence  and id_client in ";
  $sql.="     ( SELECT   d.id_titulaire FROM ad_cpt d, adsys_produit_epargne c ";
	$sql.="                   WHERE (c.classe_comptable=1 OR c.classe_comptable=2 OR c.classe_comptable=5) ";
	$sql.="                   AND d.id_prod = c.id AND d.etat_cpte=1 AND d.id_ag = c.id_ag AND d.id_ag = $global_id_agence ";
	$sql.="	                  AND date_ouvert<=date('$date')  ";
	$sql.="       ) ";
  $sql.=" group by statut_juridique,pp_sexe ";
  $sql.=" ORDER BY statut_juridique ;";

  $resultat = executeDirectQuery($sql);
  if( $resultat->errCode==NO_ERR ){
 	$epargnant=array();
 	$epargnant["g_mixte"]["nbre"]=0;
 	foreach($resultat->param as $valeur){

 		switch ($valeur["statut_juridique"]){
 			case 1:
 			      if($valeur["pp_sexe"]==1) {
 			      	$epargnant["homme"]["nbre"]=$valeur['nbre_epargant'];
 						}elseif($valeur["pp_sexe"]==2) {
 			      	$epargnant["femme"]["nbre"]=$valeur['nbre_epargant'];
 						}
 			      break;
 			case 2:
 			case 3:
 			case 4:
 			      $epargnant["g_mixte"]["nbre"]+=$valeur['nbre_epargant'];
 			       break;
 		}
 	}
 }
 return $epargnant;



}



function getSituationPartSocialeClient($id_client, $export_csv = false) {
  // PS qi renvoie un array avec les infos sur tous les comptes de part sociale d'un client
  global $global_id_agence;
  $id = getAgenceCpteIdProd($global_id_agence);
  $id_prod_ps = $id["id_prod_cpte_parts_sociales"];
  $cpte_ps = getCptPartSociale($id_client, $id_prod_ps); //Info sur tous les cptes de parts sociales du client
  $DATA_PS = array ();
  while (list ($key, $value) = each($cpte_ps)) {
    $data["num_complet_cpte"] = $value["num_complet_cpte"];
    $data["intitule_compte"] = $value["intitule_compte"];
    $data["id_titulaire"] = $value["id_titulaire"];
    $data["date_ouvert"] = pg2phpDate($value["date_ouvert"]);
    $data["solde_cpte"] = afficheMontant($value["solde"], false, $export_csv);
    $data["mnt_bloq"] = $value["retrait_unique"] == 't' ? "Retrait unique" : afficheMontant($value["mnt_bloq"] + $value["mnt_min_cpte"] + $value["mnt_bloq_cre"], false, $export_csv);
    $data["mnt_disp"] = $value["retrait_unique"] == 't' ? "Retrait unique" : afficheMontant(getSoldeDisponible($value["id_cpte"], false, $export_csv));
    $data["date_dernier_mvt"] = pg2phpDate(getLastMvtCpt($value["id_cpte"]));
    $data["libel_prod"] = getLibelPrdt($id_prod_ps, "adsys_produit_epargne");
    $data["solde_calcul_interets"] = afficheMontant($value["solde_calcul_interets"], false, $export_csv);
    $data["devise"] = $value["devise"];
    array_push($DATA_PS, $data);
  }
  return $DATA_PS;
}

function getDossierCreditsGarantis($id_client, $export_csv = false) {	// PS qui renvoie des informations sur tous les dossiers pour lesquels le client s'est porté garant
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  /* Récupération des garanties numéraires */
  $sql = "SELECT a.id_doss, a.etat, a.id_client, a.mnt_dem, a.gar_num, a.cre_mnt_octr, a.cre_etat,c.devise,c.solde,b.gar_num_id_cpte_prelev,b.gar_num_id_cpte_nantie,b.type_gar FROM ad_dcr a, ad_gar b, ad_cpt c WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag=$global_id_agence AND c.id_titulaire = $id_client AND b.gar_num_id_cpte_nantie = c.id_cpte AND a.id_doss=b.id_doss AND (a.etat <> 6 AND a.etat <> 8 AND a.etat <> 3 AND a.etat <> 4) ORDER BY a.id_doss";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  ///($result->numRows() == 0)
  ///turn NULL;
  $GAR = array ();
  while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
    $dcr = array ();
    $dcr['id_doss'] = $tmprow['id_doss'];
    $dcr['etat'] = $tmprow['etat'];
    $dcr["nomClient"] = getClientName($tmprow['id_client']);
    $dcr["id_client"] = $tmprow['id_client'];
    $dcr["mnt_dem"] = $tmprow["mnt_dem"];
    $dcr['gar_num'] = $tmprow['solde'];
    $dcr['cre_etat'] = $tmprow['cre_etat'];
    $dcr['devise'] = $tmprow['devise'];
    $dcr['cre_mnt_octr'] = $tmprow['cre_mnt_octr'];
    $dcr['type_gar'] = $tmprow['type_gar'];
    $dcr['gar_num_id_cpte_nantie'] = $tmprow['gar_num_id_cpte_nantie'];
    $dcr['num_cpte'] = getLibelCompte(2, $tmprow['gar_num_id_cpte_nantie']);
    /*
    if (($dcr['etat'] == 5 || $dcr['etat'] == 7) && (isset($dcr['gar_num_id_cpte_nantie']))) // La garantie est sur le compte d'épargne nantie
    $dcr['num_cpte'] = getLibelCompte(2, $tmprow['gar_num_id_cpte_nantie']);
    else
    $dcr['num_cpte'] = getLibelCompte(2, $tmprow['gar_num_id_cpte_prelev']);
    */
    array_push($GAR, $dcr);
  }
  /* Récupération des garanties matérielles */
  $sql = "SELECT a.id_doss, a.etat, a.id_client, a.mnt_dem, a.cre_mnt_octr, a.cre_etat,b.devise_vente,b.montant_vente,b.type_gar FROM ad_dcr a, ad_gar b, ad_biens c WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = $global_id_agence AND c.id_client = $id_client AND b.gar_mat_id_bien = c.id_bien AND a.id_doss=b.id_doss AND (a.etat <> 6 AND a.etat <> 8 AND a.etat <> 3 AND a.etat <> 4)";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
    $dcr = array ();
    $dcr['id_doss'] = $tmprow['id_doss'];
    $dcr['etat'] = $tmprow['etat'];
    $dcr["nomClient"] = getClientName($tmprow['id_client']);
    $dcr["id_client"] = $tmprow['id_client'];
    $dcr["mnt_dem"] = $tmprow["mnt_dem"];
    $dcr['gar_num'] = $tmprow["montant_vente"];
    //      $dcr["mnt_dem"] = afficheMontant($tmprow["mnt_dem"],false, $typ_raport=true);
    //      $dcr['gar_num'] = afficheMontant(recupMontant($tmprow['montant_vente']),false, $typ_raport=true);
    $dcr['cre_etat'] = $tmprow['cre_etat'];
    $dcr['devise'] = $tmprow['devise_vente'];
    $dcr['cre_mnt_octr'] = $tmprow['cre_mnt_octr'];
    $dcr['gar_num_id_cpte_nantie'] = NULL;
    $dcr['num_cpte'] = "Matérielle";
    $dcr['type_gar'] = $tmprow['type_gar'];
    array_push($GAR, $dcr);
  }
  return $GAR;
}
function getSituationCredits($id_client) { // PS qi renvoie un array avec les infos sur tous les crédits d'un client, en cours ou fermés
  $dossier = getDossierClient($id_client); //Info sur les dossiers de crédit du client
  if (!is_array($dossier))
    return NULL;
  $DATA_CREDIT = array ();
  while (list ($key, $value) = each($dossier)) {
    $data["id_doss"] = $value["id_doss"];
    $data["id_client"] = $value["id_client"];
    $data["etat"] = $value["etat"];
    $data["cre_etat"] = $value["cre_etat"];
    $data["id_prod"] = $value["id_prod"];
    $data["date_dem"] = pg2phpDate($value["date_dem"]);
    $data["mnt_dem"] = afficheMontant(recupMontant($value["mnt_dem"]), false, true);
    //$data["mnt_dem"] =  $value["mnt_dem"];
    $data["devise"] = $value["devise"];
    if ($value["cre_mnt_octr"])
      $data["cre_mnt_octr"] = $value["cre_mnt_octr"];
    if ($value["cre_date_approb"])
      $data["cre_date_approb"] = pg2phpDate($value["cre_date_approb"]);
    if ($value["cre_date_debloc"])
      $data["cre_date_debourse"] = pg2phpDate($value["cre_date_debloc"]);
    $data["libel_prod"] = getLibelPrdt($value["id_prod"], "adsys_produit_credit");
    $produit = getProdInfo("WHERE id = " . $value["id_prod"], $data["id_doss"]);
    //L'échéancier théorique
    if ($value["etat"] == 1) { //En attente de décision
      $echeancier = calcul_echeancier_theorique($data["id_prod"], $value["mnt_dem"], $value["duree_mois"], $value["differe_jours"], $value["differe_ech"], NULL, 1, $data["id_doss"]);
      // Appel de l'affichage de l'échéancier
      $parametre["index"] = '0';
      $parametre["nbre_jour_mois"] = 30;
      $parametre["montant"] = afficheMontant(recupMontant($value["mnt_dem"]), false, true); //$value["mnt_dem"];//Utilisé pour les calculs
      $parametre["mnt_reech"] = '0'; //Montant rééchelonnement
      $parametre["mnt_octr"] = $value["mnt_dem"]; //Montant octroyé
      $parametre["duree"] = $value["duree_mois"];
      $parametre["date"] = pg2phpDate($value["cre_date_approb"]);
      $parametre["id_prod"] = $value["id_prod"];
      $parametre["id_doss"] = -1; //-1 signifie aucun dossier n'est lié à l'échéancier ce qui évite de générer les données à sauvegarder
      $parametre["differe_jours"] = $value["differe_jours"];
      $parametre["differe_ech"] = $value["differe_ech"];
      $parametre["EXIST"] = 0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
      //Ajout la date d'échéance, les soldes de capitaux, intérêts et pénalités, le numéro de l'échéance et le booléen (remboursée ou non)
      $echeancier = completeEcheancier($echeancier, $parametre);
      $nbre_ech = 0;
      $nbre_ech_remb = 0;
      if (is_array($echeancier))
        reset($echeancier);
      while (list ($key, $echeance) = each($echeancier)) {
        $nbre_ech++;
        if ($echeance["remb"] == 't')
          $nbre_ech_remb++;
      }
    } else
      if ($value["etat"] == 2) { //Approuvé
        $echeancier = calcul_echeancier_theorique($data["id_prod"], $value["cre_mnt_octr"], $value["duree_mois"], $value["differe_jours"], $value["differe_ech"], NULL, 1, $data["id_doss"]);
        // Appel de l'affichage de l'échéancier
        $parametre["index"] = '0';
        $parametre["nbre_jour_mois"] = 30;
        $parametre["montant"] = $value["cre_mnt_octr"];
        $parametre["mnt_reech"] = '0'; //Montant rééchelonnement
        $parametre["mnt_octr"] = $value["cre_mnt_octr"]; //Montant octroyé
        $parametre["duree"] = $value["duree_mois"];
        $parametre["date"] = pg2phpDate($value["cre_date_approb"]);
        $parametre["id_prod"] = $value["id_prod"];
        $parametre["id_doss"] = -1; //-1 signifie aucun dossier n'est lié à l'échéancier ce qui évite de générer les données à sauvegarder
        $parametre["differe_jours"] = $value["differe_jours"];
        $parametre["EXIST"] = 0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
        //Ajout la date d'échéance, les soldes de capitaux, intérêts et pénalités, le numéro de l'échéance et le booléen (remboursée ou non)
        $echeancier = completeEcheancier($echeancier, $parametre);
        $nbre_ech = 0;
        $nbre_ech_remb = 0;
        if (is_array($echeancier))
          reset($echeancier);
        while (list ($key, $echeance) = each($echeancier)) {
          $nbre_ech++;
          if ($echeance["remb"] == 't')
            $nbre_ech_remb++;
        }
      } else
        if (($value["etat"] == 5) || ($value["etat"] == 6)) { // Fonds déboursés ou crédit soldé.
          // Appel de l'affichage de l'échéancier
          $parametre["index"] = '0';
          $parametre["nbre_jour_mois"] = 30;
          $parametre["mnt_reech"] = '0';
          if ($value["cre_nbre_reech"] <= 0) { //Pas eu de Rééch/Moratoire
            $parametre["montant"] = afficheMontant($value["cre_mnt_octr"], false, $typ_raport = true);
            $whereCond = "WHERE (id_doss='" . $value["id_doss"] . "')";
            $echeancier = getEcheancier($whereCond);
          } else { // Rééch/Moratoire
            $rows = getLastRechMorHistorique(145, $id_client); //Renvoie l'historique du dernier  Rééch/Moratoire
            $datereech = $rows["date"]; // Date de rééch/moratoire
            $parametre["montant"] = afficheMontant($rows["infos"], false, $typ_raport = true); //Montant rééchelonnement
            $parametre["mnt_reech"] = $rows["infos"]; //Montant rééchelonnement
            $whereCond = "WHERE (id_doss='" . $value["id_doss"] . "') AND (date(date_ech)>date('$datereech'))"; // Sélection les échéances après Rééch/Moratoire
            $echeancier = getEcheancier($whereCond);
          }
          $nbre_ech = 0;
          $nbre_ech_remb = 0;
          if (is_array($echeancier))
            reset($echeancier);
          while (list ($key, $echeance) = each($echeancier)) {
            $nbre_ech++;
            if ($echeance["remb"] == 't')
              $nbre_ech_remb++;
          }
        } else
          if ($value["etat"] == 7) { // ou  rééch/moratoire
            $differe_jours = 0;
            $differe_ech = 0;
            $echeancier = calcul_echeancier_theorique($data["id_prod"], $value["montant"], $value["nouv_duree_mois"], $differe_jours, $differe_ech, NULL, 1, $data["id_doss"]);
            $parametre["index"] = getRembPartiel($value["id_doss"]); // Renvoie l'id_ech de la dernière échéance remboursé partiellement
            $parametre["nbre_jour_mois"] = 30;
            $parametre["montant"] = $value["montant"];
            $parametre["mnt_reech"] = $value["montant"]; //Montant rééchelonnement
            $parametre["mnt_octr"] = $value["cre_mnt_octr"]; //Montant octroyé
            $parametre["duree"] = $value["nouv_duree_mois"]; //Nouvelle durée du crédit
            $parametre["date"] = pg2phpDate($value["date_etat"]); //Date de rééchelonnement
            $parametre["id_prod"] = $value["id_prod"];
            $parametre["id_doss"] = -1; // Si id_doss=-1 alors l'echéancier n'est pas sauvegardé
            $parametre["differe_jours"] = $differe_jours;
            $parametre["differe_ech"] = $differe_ech;
            $parametre["EXIST"] = 0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
            //Ajout la date d'échéance, les soldes de capitaux, intérêts et pénalités, le numéro de l'échéance et le booléen (remboursée ou non)
            $echeancier = completeEcheancier($echeancier, $parametre);
            $nbre_ech = 0;
            $nbre_ech_remb = 0;
            if (is_array($echeancier))
              reset($echeancier);
            while (list ($key, $echeance) = each($echeancier)) {
              $nbre_ech++;
              if ($echeance["remb"] == 't')
                $nbre_ech_remb++;
            }
          }
    $data["nbre_ech"] = $nbre_ech;
    $data["nbre_ech_remb"] = $nbre_ech_remb;
    array_push($DATA_CREDIT, $data);
  }
  return $DATA_CREDIT;
}
function getNomUtilisateur($id_utilis) {
  // PS qui renvoie un string "nom prénom" pour l'utilisateur id_uti
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT nom, prenom FROM ad_uti WHERE id_utilis = $id_utilis";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    $nom = "N/A";
  else
    if ($result->numRows() > 1)
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); // "0 ou plusieurs occurences de l'utilisateur $id_utilis dans la BD"
    else {
      $tmprow = $result->fetchRow();
      $nom = $tmprow[0] . " " . $tmprow[1];
    }
  return $nom;
}
function getCptEpargne($id_client, $exclus) { //Renvoie différentes infos des comptes d'épargne du client
  //$exclus: liste des produits de compte exclus (exple: 1,5,100)
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_cpte, num_complet_cpte, intitule_compte,id_titulaire ,date_ouvert, solde, mnt_bloq, id_prod, mnt_min_cpte, retrait_unique,solde_calcul_interets,ad_cpt.devise,mnt_bloq_cre FROM ad_cpt, adsys_produit_epargne WHERE ad_cpt.id_ag = adsys_produit_epargne.id_ag AND ad_cpt.id_ag = $global_id_agence AND id_prod = id AND id_titulaire='$id_client' AND etat_cpte <> 2 AND (id_prod NOT IN ($exclus)) ORDER BY num_complet_cpte";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $dbHandler->closeConnection(true);
  $retour = array ();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($retour, $row);
  return $retour;
}

function getCptPartSociale($id_client, $id_prod_ps) { //Renvoie différentes infos des comptes de part sociale du client
  //$exclus: liste des produits de compte exclus (exple: 1,5,100)
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_cpte, id_prod, libel, num_complet_cpte, ad_cpt.devise, intitule_compte, id_titulaire ,date_ouvert, solde, mnt_bloq, mnt_min_cpte, mnt_bloq_cre FROM ad_cpt, adsys_produit_epargne WHERE ad_cpt.id_ag = adsys_produit_epargne.id_ag AND ad_cpt.id_ag = $global_id_agence AND ad_cpt.id_prod = adsys_produit_epargne.id AND id_prod = '$id_prod_ps' AND id_titulaire = '$id_client' ORDER BY num_complet_cpte";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $dbHandler->closeConnection(true);
  $retour = array ();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($retour, $row);
  return $retour;
}

function getLibelPrdt($id_prod, $table) { //Renvoie le libellé du produit
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT libel FROM $table WHERE id_ag=$global_id_agence AND id='$id_prod'";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $dbHandler->closeConnection(true);
  $retour = $result->fetchrow();
  return $retour[0];
}
/*
 * get traduction/libel operation diverses from ad traduction
 * usage : pour le rapports des operations diverses
 * Kheshan.A.G
 */
function getLibelOperation($type_op) { //Renvoie le libellé dun operation
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	
	$sql="SELECT traduction from ad_cpt_ope a, ad_traductions b where a.libel_ope = b.id_str and categorie_ope in (2,3) and type_operation = $type_op  ;";
	
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	$dbHandler->closeConnection(true);
	$retour = $result->fetchrow();
	return $retour[0];
}

/*
 * get traduction/libel ecriture pour un id_str du libel from ad traduction
 * usage : pour le rapports des operations diverses
 * Kheshan.A.G
 */
function getLibellEcriture($id_libel_ecriture) { //Renvoie le libellé dun ecriture/op divers
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();

	$sql="SELECT traduction from ad_traductions where id_str = $id_libel_ecriture  ;";
	
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	$dbHandler->closeConnection(true);
	$retour = $result->fetchrow();
	return $retour[0];
}

function getLignesJournalCpt($journal = NULL, $date_debut, $date_fin) {
  /**
   * Renvoie les opérations comptables d'une période, sous forme d'un tableau associatif à partir de l'historique
   * @author
   * @since 1.0.8
   * @param int $journal identifiant de journal
   * @param date $date_debut date de début de la période
   * @param date $date_fin date de fin de la période
   * @return array Liste opérations comptables
   */
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT mv.id_jou, mv.ref_ecriture, mv.libel_ecriture, mv.date_comptable, mv.devise, mv.sens, mv.montant, mv.compte, cpte.num_cpte_comptable, cpte.libel_cpte_comptable, mv.type_fonction, mv.id_his,ade.type_operation,ade.info_ecriture ";
  $sql .= "FROM ad_flux_compta mv,ad_cpt_comptable cpte, ad_ecriture ade ";
  $sql .= "WHERE  mv.id_ag = cpte.id_ag AND cpte.id_ag = $global_id_agence AND ade.id_ecriture = mv.id_ecriture AND ";
  if($date_debut == NULL){
  	$sql .=" cpte.is_actif= 't' AND ";
  }else{
  	$date_deb= php2pg($date_debut);
  	$sql .=" ((cpte.is_actif = 't') OR (cpte.is_actif = 'f' AND date_modif > date('$date_debut'))) AND ";
  }
  if ($journal > 0)
    $sql .= " mv.id_jou=$journal AND ";
  $sql .= "( date(mv.date_comptable) BETWEEN date('$date_debut') AND date('$date_fin')) ";
  $sql .= "AND mv.compte = cpte.num_cpte_comptable ";
  $sql .= "ORDER BY mv.date_comptable, mv.id_ecriture, mv.sens DESC;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); // "DB: ".$result->getMessage()
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    return NULL;
  $TMPARRAY = array ();
  while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($TMPARRAY, $ligne);
  return $TMPARRAY;
}
function getLastMvtCpt($id_cpte) { //Renvoie le dernier mouvement du compte
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT MAX(date_comptable) FROM ad_mouvement m,ad_ecriture e WHERE e.id_ag = m.id_ag AND m.id_ag = $global_id_agence AND cpte_interne_cli='$id_cpte' AND m.id_ecriture=e.id_ecriture";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $dbHandler->closeConnection(true);
  $retour = $result->fetchrow();
  return $retour[0];
}
function getAgenceCpteIdProd($id_agce) {
  //Renvoie les identifiants des produits de compte pour une agence donnée
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_prod_cpte_base,id_prod_cpte_parts_sociales,id_prod_cpte_credit,id_prod_cpte_epargne_nantie FROM ad_agc WHERE id_ag='$id_agce'";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $dbHandler->closeConnection(true);
  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
  return $retour;
}
function get_next_rapport_id($login, $type) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT nextval('id_rapports')";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $retour = $result->fetchrow();
  $retour = $retour[0];
  $sql = "INSERT INTO ad_rapports(id, id_ag,login, date, type) VALUES($retour,$global_id_agence, '$login', '" . date("r") . "', $type) WHERE id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $dbHandler->closeConnection(true);
  return $retour;
}
function get_info_agence($id_ag) {
  echo "<FONT color=red>"._("Fonction get_info_agence deprecated.")."<br />"._("Utiliser getAgenceDatas à la place")."</FONT>";
  die();
}

function get_cpte_non_epargne($id_ag) {
  /* Renvoie les produits de comptes d'épargne qui ne sont pas de l'épargne
     ['ps'] : produit part sociale
     ['credit'] : produit compte crédit
  */
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_prod_cpte_parts_sociales, id_prod_cpte_credit FROM ad_agc WHERE id_ag=$id_ag";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $retour = $result->fetchrow();
  $retour = array (
              "ps" => $retour[0],
              "credit" => $retour[1]
            );

  $dbHandler->closeConnection(true);
  return $retour;
}
/**
 * fonction utilisée par le rapport tableau de resultat trimestriel
 * Fonction: Encours de l'epargne par statut juridique
 * @param date $date_calcul date de l'encours
 * @param array	$epargnant:
 *                       ["homme"]["montant"]
 *                       ["femme"]["montant"]
 *                       ["g_mixte"]["montant"]
 */
 function getMontantEncoursEpargne($date_calcul){
 	global $global_monnaie;
 	global $global_id_agence;
 	$sql="SELECT  sum(calculeCV(d.solde, d.devise, '$global_monnaie')) as montant_epargant,statut_juridique,pp_sexe ";
 	$sql.=" FROM ad_cpt d, adsys_produit_epargne c,ad_cli cli ";
	$sql.=" WHERE (c.classe_comptable=1 OR c.classe_comptable=2 OR c.classe_comptable=5) ";
	$sql.="  AND cli.id_client=d.id_titulaire AND cli.id_ag=d.id_ag AND d.id_prod = c.id  ";
	$sql.="  AND d.id_prod=1 AND d.etat_cpte=1 AND d.id_ag = c.id_ag AND d.id_ag = $global_id_agence ";
	$sql.="  AND date_ouvert <= date('$date_calcul') ";
	$sql.=" group by statut_juridique,pp_sexe ";
	$sql.=" ORDER BY statut_juridique ";

 	$resultat = executeDirectQuery($sql,false);
 	 if( $resultat->errCode==NO_ERR ){
 	$epargnant=array();
 	$epargnant["g_mixte"]["montant"]=0;
 	foreach($resultat->param as $valeur){

 		switch ($valeur["statut_juridique"]){
 			case 1:
 			      if($valeur["pp_sexe"]==1) {
 			      	$epargnant["homme"]["montant"]=$valeur['montant_epargant'];
 						}elseif($valeur["pp_sexe"]==2) {
 			      	$epargnant["femme"]["montant"]=$valeur['montant_epargant'];
 						}
 			      break;
 			case 2:
 			case 3:
 			case 4:
 			      $epargnant["g_mixte"]["montant"]+=$valeur['montant_epargant'];
 			       break;
 		}
 	}
 }

  return $epargnant;


 }


/**
 * Fonction utilisée par le rapport d'activité
 * Renvoie l'épargne encours à une date donnée
 * @author Djibril NIANG
 * @since 2.9
 * @param Date $date_calul date à laquelle on calcule l'épargne encours
 * @return float $epargne_encours
*/
function getEncoursEpargne($date_calcul) {
  global $dbHandler;
  global $global_id_agence;
  global $global_multidevise;

  $epargne_encours = 0;
	$solde_cpte = 0;
	//tester d'abord si la date de calcul n'est pas postérieure à la date du jour
  if(isBefore(date("d/m/Y"), $date_calcul)){
  	$epargne_encours = 0;
  } else {
  	$db = $dbHandler->openConnection();
	  //Recupération des comptes d'épargne des clients
	  $sql = "SELECT id_cpte from ad_cpt where (etat_cpte = 3 OR etat_cpte = 1) AND id_prod <> 2 AND id_prod <> 3 AND id_ag = $global_id_agence";
	  $result = $db->query($sql);
	  if (DB :: isError($result)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	  }
	  $comptes = array ();
	  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
	  	$comptes[$row["id_cpte"]] = $row;
	  }

	  foreach ($comptes as $key => $value) {
	    $compte = $value["id_cpte"];
	    //solde pour chaque compte à la date donnée
	    $solde_cpte = calculeSoldeCpteInterne($compte, $date_calcul);
	    $epargne_encours = $epargne_encours + $solde_cpte;
	  }
	  $dbHandler->closeConnection(true);
  }
    return $epargne_encours;
}

/**
 * Fonction utilisée par le rapport d'activité
 * Renvoie l'épargne collectée durant une période donnée d'une agence
 * @author Djibril NIANG
 * @since 2.9
 * @param Date $date_deb date de début de la période
 * @param Date $date_fin date de fin de la période
 * @return float $epargne_collecte
*/
function getEpargneCollectee($date_deb, $date_fin) {
  global $dbHandler;
  global $global_id_client;
  global $global_id_agence;
  global $global_multidevise;

  $db = $dbHandler->openConnection();

  $sql = " SELECT distinct cpte_cpta_prod_ep from adsys_produit_epargne where id_ag = $global_id_agence and cpte_cpta_prod_ep IS NOT NULL";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $compte = array ();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $compte[$row["cpte_cpta_prod_ep"]] = $row;

  $tot_credit = 0;
  $tot_debit = 0;
  foreach ($compte as $key => $value) {
    $compte = $value["cpte_cpta_prod_ep"];

    $sql1 = "SELECT sum(montant) FROM ad_mouvement WHERE id_ag = $global_id_agence AND compte = '$compte' AND sens = 'c' AND date_valeur BETWEEN date('$date_deb') AND date('$date_fin')";
    $result1 = $db->query($sql1);
    if (DB :: isError($result1)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    $row1 = $result1->fetchrow();
    $tot_credit = $tot_credit + $row1[0];

  }
  $epargne_collectee = $tot_credit;
  $dbHandler->closeConnection(true);
  return $epargne_collectee;
}
/**
 * Fonction utilisée par le rapport d'activité
 * Renvoie le nombre total clients actifs pour une date donnée d'une agence
 * @author Djibril NIANG
 * @since 2.9
 * @param Date $date date à laquelle on calcule les statistiques
 * @param bool $membreAuxiliaire vrai si on veut avoir que les clients membres ordinaires et auxiliaires
 * @return Array Tableau contenant les membres l'agence
*/
function get_Clients_Actifs($date,$membreAuxiliaire=false,$groupement=false) {
  global $dbHandler;
  global $global_id_client;
  global $global_id_agence;
  global $global_multidevise;
  $stat = array ();
  //  $stat['clients']['homme_deb'] = 0;
  //  $stat['clients']['femme_deb'] = 0;
  //  $stat['clients']['pm_deb'] = 0;
  $stat['clients']['homme'] = 0;
  $stat['clients']['femme'] = 0;
  if(!$groupement){
  	$stat['clients']['pm'] = 0;
  	$stat['clients']['gs'] = 0;
  	$stat['clients']['gi'] = 0;
  } else {
  	//$stat['clients']['g_homme'] = 0;
  //	$stat['clients']['g_femme'] = 0;
  	$stat['clients']['g_mixte'] = 0;
  }

  // Nombre clients
  $db = $dbHandler->openConnection();
  if ($membreAuxiliaire){
  	  $sql = "SELECT statut_juridique, pp_sexe, count(id_client) FROM ad_cli where etat=2 AND date_adh <= date('$date') AND (qualite=1 OR qualite=2) AND  id_ag=$global_id_agence GROUP BY statut_juridique, pp_sexe";
  }else{
  	  $sql = "SELECT statut_juridique, pp_sexe, count(id_client) FROM ad_cli where etat=2 AND date_adh <= date('$date') AND id_ag=$global_id_agence GROUP BY statut_juridique, pp_sexe";
  }

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($row["statut_juridique"] == 1){
    	if ($row["pp_sexe"] == 1)
        $stat["clients"]["homme"] = $row["count"];
      else
        if ($row["pp_sexe"] == 2)
          $stat["clients"]["femme"] = $row["count"];
    }
    if($row["statut_juridique"] >1){
    	if($groupement){
    		$stat['clients']['g_mixte'] += $row["count"];
    	}else {
    		if($row["statut_juridique"]==2){
          $stat["clients"]["pm"] = $row["count"];
    		}elseif($row["statut_juridique"]==3){
          $stat["clients"]["gi"] = $row["count"];
    		}elseif($row["statut_juridique"]==4){
    			$stat["clients"]["gs"] = $row["count"];
    		}
    	}
    }



  }
  $dbHandler->closeConnection(true);
  return $stat;
}

/**
 * Fonction utilisée par le rapport d'activité
 * Renvoie le nombre de credits et le montant total octroyé pour une période
 * donnée
 * @author Djibril NIANG
 * @since 2.9
 * @param Date $deb_mois debut de la période
 * @param Date $fin_mois fin de la période
 * @return Array $creditsMois
*/
function getNbreCreditsMois($deb_mois, $fin_mois) {
  global $dbHandler;
  global $global_id_client;
  global $global_id_agence;
  global $global_multidevise;
  $creditsMois = array ();
  $creditsMois["nbre_credit"] = 0;
  $creditsMois["montant_tot"] = 0;
  $db = $dbHandler->openConnection();
  $sql = "SELECT count(id_doss) As nbre_credit, sum(cre_mnt_octr) As montant_tot from ad_dcr where etat = 5 AND id_ag=$global_id_agence AND date_etat BETWEEN date('$deb_mois') AND date('$fin_mois') ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $creditsMois["nbre_credit"] = $row["nbre_credit"];
    $creditsMois["montant_tot"] = $row["montant_tot"];
  }
  $dbHandler->closeConnection(true);
  return $creditsMois;
}
/**
 * Fonction utilisée par le rapport Tableau de resultat trimestriel
 * Renvoie le nombre de credits et le montant total octroyé pour une période regroupé par statut juridique
 * donnée
 * @author ares
 * @since 3.0
 * @param Date $date_deb debut de la période
 * @param Date $date_fin fin de la période
 * @return Array $credits
*/
function getCreditsDebourseByStatutJuridique($date_deb, $date_fin) {
  global $global_id_agence;
  global $global_multidevise;

global $global_monnaie ;

 	if ( $global_multidevise) {
 	   $sql  = " SELECT count(id_doss) As nbre_credit,sum(calculecv(cre_mnt_octr,p.devise,'$global_monnaie'))  As montant_tot,statut_juridique,pp_sexe ";
 	   $sql .= " from ad_dcr d,ad_cli c, adsys_produit_credit p ";
 	   $sql .= "   WHERE c.id_client=d.id_client AND c.id_ag=d.id_ag AND  d.id_ag=$global_id_agence  AND   p.id_ag=d.id_ag AND  d.id_ag=p.id_ag AND p.id=d.id_prod ";
 	   $sql.="   AND cre_date_debloc BETWEEN date('$date_deb') AND date('$date_fin') " ;
 	   $sql.=" group by statut_juridique,pp_sexe;";

 	} else {
 	   $sql = " SELECT count(id_doss) As nbre_credit, sum(cre_mnt_octr) As montant_tot,statut_juridique,pp_sexe from ad_dcr d,ad_cli c ";
 	   $sql.="   WHERE c.id_client=d.id_client AND c.id_ag=d.id_ag AND  d.id_ag=$global_id_agence " ;
 	   $sql.="   AND cre_date_debloc BETWEEN date('$date_deb') AND date('$date_fin') " ;
 	   $sql.=" group by statut_juridique,pp_sexe;";
 	}

  $resultat = executeDirectQuery($sql);
 if( $resultat->errCode==NO_ERR ){
 	$credit=array();
 	 $credit["g_mixte"]["nbre"]=0;
 	 $credit["g_mixte"]["montant"]=0;
 	foreach($resultat->param as $valeur){

 		switch ($valeur["statut_juridique"]){
 			case 1:
 			      if($valeur["pp_sexe"]==1) {
 			      	$credit["homme"]["nbre"]=$valeur['nbre_credit'];
 							$credit["homme"]["montant"]=$valeur['montant_tot'];
 			      }elseif($valeur["pp_sexe"]==2) {
 			      	$credit["femme"]["nbre"]=$valeur['nbre_credit'];
 						  $credit["femme"]["montant"]=$valeur['montant_tot'];
 			      }
 			      break;
 			case 2:
 			case 3:
 			case 4:
 			      $credit["g_mixte"]["nbre"]+=$valeur['nbre_credit'];
 			      $credit["g_mixte"]["montant"]+=$valeur['montant_tot'];
 			       break;
 		}
 	}
 } else {
 	 signalErreur(__FILE__, __LINE__, __FUNCTION__);
 }

  return $credit;

}

function getRapportEcheancesCAT($limite_moisan, $gestionnaire = 0) {
  /*

  Détermine la répartition de l'épargne disponible en fonction des types de cptes suivants : 'Epargne nantie', 'DAT', 'Comptes à terme', 'Autres cpte'.
  Les données sont renvoyées dans un tableau; s'il n'y a rien, renvoi de NULL
  $limite_moisan spécifie jusqu'à quelle date il faut sélectionner les cptes arrivant à échéance
  FIXME : gérer $limite_moisan
  format du tableau :
  array( 'epargne_nantie' => array('total'=> array('nbre','montant'), 'détails'=>array('moisannée','totalmois', 'nbremoisannee')*)
         'DAT'                     =>array('total'=> array('nbre','montant'), 'détails'=>array('moisannée','totalmois', 'nbremoisannee')*)
         'cptes_a_terme'   =>array('total'=> array('nbre','montant'), 'détails'=>array('moisannée','totalmois', 'nbremoisannee')*)
         'cptes_a_vue'     =>array('total'=> array('nbre','montant'), 'détails'=>array('moisannée','totalmois')*)
  ) avec * indiquant qu'il y a autant d'entrées que de moisannée
  $gestionnaire L'utilisateur gestionnaire de l'épargne
  */
  //FIXME : dans les requêtes SQL tenir compte de l'état ouvert ou fermé du cpte
  global $global_id_agence;
  global $dbHandler;
  $work_array = array ();
  $db = $dbHandler->openConnection();
  $AG = getAgenceDatas($global_id_agence);

  $id_prod_epargne_nantie = $AG["id_prod_cpte_epargne_nantie"];
  //get regroupement des cptes d'épargne nantie par date d'échéance pour construire un tableau à mois m, m+1, m+2,etc
  $tmp_array = sqlEcheancesCAT(0, $limite_moisan, $gestionnaire);
  //
  $work_array[_("Epargne nantie")]["details"] = $tmp_array;
  $nbrTot = 0;
  $mntTot = 0;
  $i = 0;
  for ($i = 0; $i < $limite_moisan; $i++) {
    $current_mois_an = date("n", mktime(0, 0, 0, date("n") + $i, 1, date("Y"))) . date("Y");
    $nbrTot += $tmp_array[$current_mois_an]["nbre"];
    $mntTot += $tmp_array[$current_mois_an]["montant"];
  }
  $work_array[_("Epargne nantie")]["total"] = array (
        "nbre" => $nbrTot,
        "montant" => $mntTot
      );
  //get cptes DAT
  //get regroupement des DAT par date d'échéance pour construire un tableau à mois m, m+1, m+2,etc
  $tmp_array = sqlEcheancesCAT(1, $limite_moisan, $gestionnaire);
  $work_array[_("DAT")]["details"] = $tmp_array;
  $nbrTot = 0;
  $mntTot = 0;
  $i = 0;
  for ($i = 0; $i < $limite_moisan; $i++) {
    $current_mois_an = date("n", mktime(0, 0, 0, date("n") + $i, 1, date("Y"))) . date("Y");
    $nbrTot += $tmp_array[$current_mois_an]["nbre"];
    $mntTot += $tmp_array[$current_mois_an]["montant"];
  }
  $work_array[_("DAT")]["total"] = array (
                                  "nbre" => $nbrTot,
                                  "montant" => $mntTot
                                );
  //regroupement autres cptes à terme
  $tmp_array = sqlEcheancesCAT(2, $limite_moisan, $gestionnaire);
  $work_array[_("Comptes à terme")]["details"] = $tmp_array;
  $nbrTot = 0;
  $mntTot = 0;
  $i = 0;
  for ($i = 0; $i < $limite_moisan; $i++) {
    $current_mois_an = date("n", mktime(0, 0, 0, date("n") + $i, 1, date("Y"))) . date("Y");
    $nbrTot += $tmp_array[$current_mois_an]["nbre"];
    $mntTot += $tmp_array[$current_mois_an]["montant"];
  }
  $work_array[_("Comptes à terme")]["total"] = array (
        "nbre" => $nbrTot,
        "montant" => $mntTot
      );
  // Epargne nantie des crédits en retard/souffrance/perte
  $tmp_array = array ();
  reset($work_array[_("Epargne nantie")]["details"]);
  $mois_an_courant = mktime(0, 0, 0, date("m"), 1, date("Y"));
  $str_mois_an_courant = date("nY");
  while (list ($key, $value) = each($work_array[_("Epargne nantie")]["details"])) {
    $an = substr($key, -4);
    $mois = substr($key, 0, (strlen($key) == 5 ? 1 : 2));
    $mois_an = mktime(0, 0, 0, $mois, 1, $an);
    if ($mois_an < $mois_an_courant) {
      $tmp_array[$str_mois_an_courant]["montant"] += $value["montant"];
      $tmp_array[$str_mois_an_courant]["nbre"] += $value["nbre"];
    }
  }
  $work_array[_("Garanties de crédits en retard")]["details"] = $tmp_array;
  $work_array[_("Garanties de crédits en retard")]["total"] = array (
        "nbre" => $work_array[_("Garanties de crédits en retard")]["details"][$str_mois_an_courant]["nbre"],
        "montant" => $work_array[_("Garanties de crédits en retard")]["details"][$str_mois_an_courant]["montant"]
      );
  // Calcul du total
  $total_array = array ();
  reset($work_array);
  $total_array = array ();
  while (list ($key, $value) = each($work_array)) {
    for ($i = 0; $i < $limite_moisan; $i++) {
      $current_mois_an = date("n", mktime(0, 0, 0, date("n") + $i, 1, date("Y"))) . date("Y");
      $total_array[$current_mois_an]["nbre"] += $value["details"][$current_mois_an]["nbre"];
      $total_array[$current_mois_an]["montant"] += $value["details"][$current_mois_an]["montant"];
    }
  }
  $work_array[_("Total")]["details"] = $total_array;
  $nbrTot = 0;
  $mntTot = 0;
  $i = 0;
  for ($i = 0; $i < $limite_moisan; $i++) {
    $current_mois_an = date("n", mktime(0, 0, 0, date("n") + $i, 1, date("Y"))) . date("Y");
    $nbrTot += $work_array[_("Total")]["details"][$current_mois_an]["nbre"];
    $mntTot += $work_array[_("Total")]["details"][$current_mois_an]["montant"];
  }
  $work_array[_("Total")]["total"] = array (
                                    "nbre" => $nbrTot,
                                    "montant" => $mntTot
                                  );
  $dbHandler->closeConnection(true);
  if (is_array($work_array))
    return $work_array;
  else
    return NULL;
}
function sqlEcheancesCAT($type_cpte, $limite_moisan, $gestionnaire = 0) {
  /*
  Requête SQL permettant d'extraire le total des soldes par échéances de compte
  type_cpte 0 => épargne nantie
  type_cpte 1 => DAT
  type_cpte 2 => autres cptes à terme
  type_cpte 4 => cptes à vue
  $limite_moisan spécifie jusqu'à quelle date il faut sélectionner les cptes arrivant à échéance
  $gestionnaire L'utilisateur gestionnaire de l'épargne
    FIXME : gérer $limite_moisan
  */
  global $dbHandler, $global_monnaie, $global_id_agence;
  $dev_ref = $global_monnaie;
  $my_date = "dat_date_fin"; //nom du champ de la table ad_cpt utilisé pour connaître le terme d'un compte
  switch ($type_cpte) {
  case 0 :
    $my_date = "date_ech"; //pour l'épargne nantie, on utilise le champ date_ech de la table ad_etr
    $from_clause = " (select $my_date, id_cpte, solde, a.devise from ad_cpt a, ad_dcr b, ad_etr c, ad_gar d where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = d.id_ag and d.id_ag = $global_id_agence and id_cpte=d.gar_num_id_cpte_nantie and b.id_doss=d.id_doss and b.id_doss=c.id_doss and a.etat_cpte=1) ";
    if ($gestionnaire > 0)
      $from_clause = " (select $my_date, id_cpte, solde, a.devise from ad_cpt a, ad_dcr b, ad_etr c, ad_gar d where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = d.id_ag and d.id_ag = $global_id_agence and id_cpte=d.gar_num_id_cpte_nantie and b.id_doss=d.id_doss and b.id_doss=c.id_doss and a.etat_cpte=1 and b.id_agent_gest=$gestionnaire) ";
    break;
  case 1 :
    $from_clause = " (select $my_date, id_cpte, solde, a.devise  from ad_cpt a, adsys_produit_epargne b where a.id_ag = b.id_ag and b.id_ag = $global_id_agence and (a.id_prod=b.id) and (b.depot_unique='t') AND (b.retrait_unique='t') and a.etat_cpte ='1' AND (b.terme > 0)) ";
    if ($gestionnaire > 0)
      $from_clause = " (select $my_date, id_cpte, solde, a.devise  from ad_cli c,ad_cpt a, adsys_produit_epargne b where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence and (a.id_prod=b.id) and (b.depot_unique='t') AND (b.retrait_unique='t') and a.etat_cpte ='1' AND (b.terme > 0) and (c.id_client=a.id_titulaire and c.gestionnaire=$gestionnaire))";
    break;
  case 2 :
    $from_clause = " (select $my_date, id_cpte, solde, a.devise  from ad_cpt a, adsys_produit_epargne b where a.id_ag = b.id_ag and b.id_ag = $global_id_agence and (a.id_prod=b.id) and ((b.depot_unique='f') OR (b.retrait_unique='f')) AND (b.terme > 0) and (b.service_financier='t') and a.etat_cpte=1)  ";
    if ($gestionnaire > 0)
      $from_clause = " (select $my_date, id_cpte, solde, a.devise  from ad_cli c,ad_cpt a, adsys_produit_epargne b where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence and (a.id_prod=b.id) and ((b.depot_unique='f') OR (b.retrait_unique='f')) AND (b.terme > 0) and (b.service_financier='t') and (a.etat_cpte=1) and (c.id_client=a.id_titulaire and c.gestionnaire=$gestionnaire))";
    break;
  case 3 :
    $from_clause = "  (select $my_date, id_cpte, solde, a.devise  from ad_cpt a, adsys_produit_epargne b where a.id_ag = b.id_ag and b.id_ag = $global_id_agence and (a.id_prod=b.id) and ((b.depot_unique='f') and (b.retrait_unique='f')) AND (b.terme = 0) and (b.service_financier='t') and a.etat_cpte=1) ";
    if ($gestionnaire > 0)
      $from_clause = "  (select $my_date, id_cpte, solde, a.devise  from ad_cli c,ad_cpt a, adsys_produit_epargne b where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence(a.id_prod=b.id) and ((b.depot_unique='f') and (b.retrait_unique='f')) AND (b.terme = 0) and (b.service_financier='t') and (a.etat_cpte=1) and (c.id_client=a.id_titulaire and c.gestionnaire=$gestionnaire)) ";
    break; //pour les cptes à vue, la date de fin du cpte n'est pas pertinente
  }
  $db = $dbHandler->openConnection();
  $sql = "select (cast(mois_echeance as text) || cast(annee_echeance as text)) as moisannee, sum(calculeCV(solde_cpte, devise, '$dev_ref')) as totalmois, count(id_cpte) as nbre_cptes from ";
  $sql .= "(select extract(MONTH from max($my_date)) as mois_echeance, ";
  $sql .= "extract(YEAR from max($my_date)) as annee_echeance, ";
  $sql .= "solde as solde_cpte,id_cpte, devise ";
  $sql .= "from $from_clause as temp0 ";
  $sql .= "group by temp0.id_cpte,temp0.solde, temp0.devise ";
  $sql .= "order by annee_echeance, mois_echeance) as temp1 ";
  //  $sql .= " where (cast(mois_echeance as text) || cast(annee_echeance as text)) <= '$limite_moisan' ";
  $sql .= "group by mois_echeance,annee_echeance ";
  $sql .= "order by annee_echeance, mois_echeance;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); // "DB: ".$result->getMessage()
  }
  $tmp_array = array ();
  while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $tmp_array[$ligne["moisannee"]]['montant'] = $ligne["totalmois"];
    $tmp_array[$ligne["moisannee"]]['nbre'] = $ligne["nbre_cptes"];
  }
  $dbHandler->closeConnection(true);
  if (is_array($tmp_array))
    return $tmp_array;
  else
    return NULL;
}

function getLignesDATEcheance($periode, $exclusif, $gestionnaire = 0,$date_deb = null,$date_fin = null,$n_agence=null, $n_guichet=null) {
  /*
  Requête SQL qui renvoie dans un array les comptes DAT arrivant à échéance par groupes suivant la période spécifiée
    (1 => "Aujourd'hui", 2=>"Sur une semaine" , 3=>"Sur deux semaines" , 4 => "Sur trois semaines" , 5 => "Sur 1 mois", 6 => "Sur 3 mois", 7 => "Sur 6 mois", 8 => "Sur 12 mois")

  Renvoie un array sous la forme :
    array('Dat échus entre 1 et 7 jours'=>array('total'=>array('nbre','montant total'), 'détails"=>array(0=>dat1,...,n=>dat n))
          'Dat échus entre 8 et 15 jours'=>('total'=>array('nbre','montant total'), 'détails"=>array(0=>dat1,...,n=>dat n))
          ...
          'Dat échus entre x et y mois'=>('total'=>array('nbre','montant total'), 'détails"=>array(0=>dat1,...,n=>dat n))
         )
         $gestionnaire: L'identifiant du gestionnaire de l'épargne
  */
  global $dbHandler, $global_id_agence;

  $lignesDAT = array ();
  $tmp_array = array ();
  $date_inf = ""; //les dates qui vont servir de bornes inférieures et supérieures pour la sélection des comptes
  $date_sup = "";

  if(is_null($periode) || $periode ==''){
    $exclusif = true;
    $libelle = _(" la période du ". ($date_deb == ''?"-":$date_deb). " au ".  ($date_fin == ''?"-":$date_fin));
  }
  if ($exclusif)
    $i = $periode;
  else
    $i = 1;
  $db = $dbHandler->openConnection();
  do {
    //déterminer en fonction de la période sélectionnée les différents intervalles de temps pour la requête
    switch ($i) {
    case 1 : {
      $date_inf = date("d/m/Y"); //today
      $date_sup = date("d/m/Y");
      $indice = "Aujourdhui";
      $libelle = _("Aujourd'hui");
    }
    break;
    case 2 : {
      $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 1, date("Y"))); //j+1
      $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 7, date("Y"))); //j+7
      $indice = "entre_1_et_7_jours";
      $libelle = _("1 jour à 7 jours");
    }
    break;
    case 3 : {
      $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 8, date("Y"))); //j+1
      $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 14, date("Y"))); //j+7
      $indice = "entre_8_et_14_jours";
      $libelle = _("8 jours à 14 jours");
    }
    break;
    case 4 : {
      $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 15, date("Y")));
      $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 21, date("Y")));
      $indice = "entre_15_et_21_jours";
      $libelle = _("15 jours à 21 jours");
    }
    break;
    case 5 : {
      $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 22, date("Y")));
      $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n") + 1, date("d"), date("Y"))); //30 jours
      $indice = "entre_22_et_30_jours";
      $libelle = _("22 jours à 30 jours");
    }
    break;
    case 6 : {
      $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n") + 1, date("d") + 1, date("Y"))); //premier jour du mois suivant
      $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n") + 3, date("d"), date("Y"))); //dernier jour du 3ème mois suivant
      $indice = "entre_1_et_3_mois";
      $libelle = _("1 mois à 3 mois");
    }
    break;
    case 7 : {
      $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n") + 3, date("d") + 1, date("Y")));
      $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n") + 6, date("d"), date("Y"))); //
      $indice = "entre_4_et_6_mois";
      $libelle = _("4 mois à 6 mois");
    }
    break;
    case 8 : {
      $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n") + 6, date("d") + 1, date("Y")));
      $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n") + 12, date("d"), date("Y"))); //
      $indice = "entre_7_et_12_mois";
      $libelle = _("7 mois à 12 mois");
    }
    break;
    }

    $where_date = "";
    $isSearchByPeriod=false;
   // si le critère periode a été selectioné
   if(!is_null($periode) || $periode!='' )
    {
      $isSearchByPeriod=true;
      $where_date .= " and (date(a.dat_date_fin) >= date '$date_inf') and (date(a.dat_date_fin) <= date '$date_sup') ";
    }
   else if ($date_deb != NULL && $date_fin != NULL)
    {

        $where_date = " AND A.dat_date_fin BETWEEN  date('$date_deb') and date('$date_fin') ";
    }

    if($isSearchByPeriod)
    {
      //récupérer le nombre et le total du montant des DAT échus à la période donnée
      $sql = "select count(a.id_cpte) as nombre, sum(a.solde) as montant_total";
      $sql .= " from ad_cpt a, ad_cli b, adsys_produit_epargne c ";
      $sql .= " where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence and a.id_titulaire=b.id_client ";
      if ($gestionnaire > 0)
        $sql .= " and b.gestionnaire=$gestionnaire ";
      $sql .= $where_date;
      $sql .= " and a.id_prod=c.id and c.depot_unique='t' and c.retrait_unique='t'  ";
      $sql .= " and a.etat_cpte='1'";
      if (isFusion()){
          if ($n_agence!= null){
              $sql .= " and b.n_agence = $n_agence";
          }
          if ($n_guichet !=null){
            $sql .= " and b.n_guichet = $n_guichet";
          }
      }
      $sql .= " ;";
    }
    else
    {
      $sql = "select count(DISTINCT a.id_cpte) as nombre, sum(case WHEN ( date('$date_deb') <= d.date_action )then d.solde else A.solde END) as montant_total ";
      $sql .= " FROM
                  ad_cpt A
                  inner JOIN
                  ad_cpt_hist d  on a.id_ag = d.id_ag and a.id_cpte = d.id_cpte --and d.date_action >= date('20151210')
                  INNER JOIN
                  ad_cli b on b.id_client = A.id_titulaire and A.id_ag=b.id_ag
                  INNER JOIN  adsys_produit_epargne c on c.id_ag = b.id_ag and c.id = A.id_prod
                  WHERE ";
      $sql .= "  c.id_ag = $global_id_agence  and c.depot_unique='t' and c.retrait_unique='t'
                 AND (a.etat_cpte in(1) or d.id in (SELECT	id FROM	ad_cpt_hist WHERE	id_cpte = a.id_cpte AND date_action >= DATE ('$date_deb') and etat_cpte = 1 ORDER BY date_action,ID limit 1 )) ";
      if ($gestionnaire > 0)
        $sql .= " and b.gestionnaire=$gestionnaire ";
      $sql .= $where_date;
      if (isFusion()){
          if ($n_agence!= null){
              $sql .= " and b.n_agence = $n_agence";
          }
          if ($n_guichet !=null){
              $sql .= " and b.n_guichet = $n_guichet";
          }
      }

    }

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); //  $result->getMessage()
    }
    $lignesDAT["$indice"]["libelle_echeance"] = $libelle;
    $ligne = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $lignesDAT["$indice"]['total'] = $ligne;
    //récupérer le détail des comptes

    if($isSearchByPeriod) {

      $sql = "select a.num_complet_cpte, a.id_titulaire, a.solde, a.dat_date_fin as date_echeance, ";
      $sql .= " a.date_ouvert as date_ouverture, a.dat_decision_client,a.devise,a.dat_prolongation, ";
      $sql .= " b.pp_nom, b.pp_prenom, b.pm_raison_sociale, b.gi_nom, b.statut_juridique, c.tx_interet, c.terme, c.dat_prolongeable ";
      $sql .= " from ad_cpt a, ad_cli b, adsys_produit_epargne c ";
      $sql .= " where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence and a.id_titulaire=b.id_client and a.id_prod=c.id and c.depot_unique='t' and c.retrait_unique='t' ";
      if ($gestionnaire > 0)
        $sql .= " and b.gestionnaire=$gestionnaire ";
      $sql .= $where_date;
      if (isFusion()){
          if ($n_agence!= null){
              $sql .= " and b.n_agence = $n_agence";
          }
          if ($n_guichet !=null){
              $sql .= " and b.n_guichet = $n_guichet";
          }
      }
      $sql .= " and a.etat_cpte='1' ";
      $sql .= " order by a.dat_date_fin, a.num_complet_cpte;";

    }
    else
    {
      $sql = "select DISTINCT a.num_complet_cpte, a.id_titulaire, case WHEN ( date('$date_deb') <= d.date_action )then d.solde else A.solde END, a.dat_date_fin as date_echeance, ";
      $sql .= " a.date_ouvert as date_ouverture, a.dat_decision_client,a.devise,a.dat_prolongation, ";
      $sql .= " b.pp_nom, b.pp_prenom, b.pm_raison_sociale, b.gi_nom, b.statut_juridique, c.tx_interet, c.terme, c.dat_prolongeable ";
      $sql .= " FROM
                  ad_cpt A
                  inner JOIN
                  ad_cpt_hist d  on a.id_ag = d.id_ag and a.id_cpte = d.id_cpte --and d.date_action >= date('20151210')
                  INNER JOIN
                  ad_cli b on b.id_client = A.id_titulaire and A.id_ag=b.id_ag
                  INNER JOIN  adsys_produit_epargne c on c.id_ag = b.id_ag and c.id = A.id_prod
                  WHERE ";
      $sql .= "  c.id_ag = $global_id_agence  and c.depot_unique='t' and c.retrait_unique='t'
                  AND (a.etat_cpte in(1) or d.id in (SELECT	id FROM	ad_cpt_hist WHERE	id_cpte = a.id_cpte AND date_action >= DATE ('$date_deb') and etat_cpte = 1 ORDER BY date_action,ID limit 1 )) ";
      if ($gestionnaire > 0)
        $sql .= " and b.gestionnaire=$gestionnaire ";
      $sql .= $where_date;
      if (isFusion()){
          if ($n_agence!= null){
              $sql .= " and b.n_agence = $n_agence";
          }
          if ($n_guichet !=null){
              $sql .= " and b.n_guichet = $n_guichet";
          }
      }
      //$sql .= " and a.etat_cpte in (1,2) ";
      $sql .= " order by a.dat_date_fin, a.num_complet_cpte;";
    }

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); //  $result->getMessage()
    };
    while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC))
      $lignesDAT["$indice"]['details'][] = $ligne;
    $i++;
  } while ($i <= $periode);
  $dbHandler->closeConnection(true);
  if (is_array($lignesDAT))
    return $lignesDAT;
  else
    return NULL;
}

function getLignesCptesInactifs($nbre_jours, $produit, $gestionnaire = 0) {
  /*
  Requête SQL qui renvoie la liste des comptes inactifs depuis un certain nombre de jours
  $gestionnaire: utilisateur gestionnaire de l'épargne
  Retourne un tableau
  Exclure les produits épargne nantie, compte de parts sociales, compte de crédit
  */
  global $dbHandler, $global_monnaie, $global_id_agence;


  $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") - $nbre_jours, date("Y")));

  $lignesCptesInactifs = array ();
  $db = $dbHandler->openConnection();
  $sql = "select c.libel, c.id, a.id_cpte, a.num_complet_cpte, a.id_titulaire, a.solde, a.devise, calculeCV(a.solde, a.devise, '$global_monnaie') AS cv, b.pp_nom, b.pp_prenom,b.pm_raison_sociale, b.gi_nom, ";
  $sql .= " b.statut_juridique,t.last_date ";
  $sql .= " from ";
  $sql .= "   (select m.cpte_interne_cli, max(e.date_comptable) as last_date ";
  $sql .= "   from ad_mouvement m, ad_ecriture e ";
  $sql .= "   where e.id_ag = m.id_ag and m.id_ag = $global_id_agence and e.id_ecriture = m.id_ecriture ";
  $sql .= "   group by m.cpte_interne_cli) as t, ";
  $sql .= " ad_cpt a, ad_cli b, adsys_produit_epargne c ";
  $sql .= " where t.cpte_interne_cli::integer=a.id_cpte and a.id_titulaire=b.id_client and a.id_prod=c.id and c.service_financier='t' ";
  $sql .= " AND t.last_date::date <= '$date_sup'";
  $sql .= " and not (c.depot_unique='t' and c.retrait_unique='t')";
  
  if ($gestionnaire > 0) {
    $sql .= " and (b.gestionnaire=$gestionnaire ) ";
  }
  if ($produit != null) {
    $sql .= " and c.id = " . $produit['id'];
  }
  $sql .= " group by c.libel, c.id, a.id_cpte, a.num_complet_cpte, a.id_titulaire, a.solde, a.devise, b.pp_nom, b.pp_prenom,b.pm_raison_sociale, b.gi_nom, b.statut_juridique, t.last_date ";
  $sql .= " order by t.last_date;";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); //  $result->getMessage()
  }

  while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC)) {


    $idCpte  = $ligne['id_cpte'];
    $id_prod = $ligne['id'];
    $lignesCptesInactifs[$id_prod]["libel_prod_ep"] = $ligne['libel'];
    $lignesCptesInactifs[$id_prod]["comptes"][$idCpte]["id_cpte"] = $ligne['id_cpte'];
    switch ($ligne["statut_juridique"]) {
      case 1 :
        $nom_client = $ligne["pp_nom"]." ".$ligne["pp_prenom"];
        break;
      case 2 :
        $nom_client = $ligne["pm_raison_sociale"];
        break;
      default :
        $nom_client = $ligne["gi_nom"]; // 3 or 4
    }
    $lignesCptesInactifs[$id_prod]["comptes"][$idCpte]["nom_client"]       = $nom_client;
    $lignesCptesInactifs[$id_prod]["comptes"][$idCpte]["num_complet_cpte"] = $ligne['num_complet_cpte'];
    $lignesCptesInactifs[$id_prod]["comptes"][$idCpte]["id_titulaire"]     = $ligne['id_titulaire'];
    $lignesCptesInactifs[$id_prod]["comptes"][$idCpte]["solde"]            = $ligne['solde'];
    $lignesCptesInactifs[$id_prod]["comptes"][$idCpte]["cv"]               = $ligne['cv'];
    $lignesCptesInactifs[$id_prod]["comptes"][$idCpte]["last_date"]        = $ligne['last_date'];
    $lignesCptesInactifs[$id_prod]["comptes"][$idCpte]["devise"]           = $ligne['devise'];

  }

  $dbHandler->closeConnection(true);
  if (is_array($lignesCptesInactifs))
    return $lignesCptesInactifs;
  else
    return NULL;
}

/**
 * @desc : fonction qui renvoie le nombre de comptes financiers qui existaient il y a moins de n jours
 * @param int $nb_jours : le nombre de jours
 * @since 2.8  16/02/2007
 * @return int $nm_compte: le nombre de comptes d'épargne il y a n jours
 */
function getComptesFinanciers($filtres) {
  global $dbHandler, $global_monnaie, $global_id_agence;
//  if ($nb_jours == NULL)
//    $nb_jours = 0;
  // la date il y a moins de $nb_jours
  $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") - $filtres["nb_jours"], date("Y")));
  $db = $dbHandler->openConnection();
  $nb_comptes = 0;
  $sql = "SELECT COUNT(*) FROM ad_cpt, adsys_produit_epargne ";
  $sql .= " WHERE ad_cpt.id_ag = adsys_produit_epargne.id_ag and ad_cpt.id_ag = $global_id_agence and ad_cpt.id_prod = adsys_produit_epargne.id AND adsys_produit_epargne.service_financier = 't' ";
  $sql .= " AND ad_cpt.date_ouvert::date <= '$date_sup' ";
  $sql .= " AND NOT (adsys_produit_epargne.depot_unique = 't' AND adsys_produit_epargne.retrait_unique = 't')";
  if($filtres['id_prod'] != 0) {
    $sql .= " AND adsys_produit_epargne.id = " . $filtres['id_prod'];
  }

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $row = $result->fetchrow();
  $nb_comptes = $row[0];
  $dbHandler->closeConnection(true);
  return $nb_comptes;
}

function getNomClient($id_client) { //Renvoie le nom complet du client (que ce soit PP, PM, GI ou GS)
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  
  $sql = "SELECT statut_juridique, pp_nom, pp_prenom, pm_raison_sociale, gi_nom FROM ad_cli WHERE id_client=$id_client and id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Nombre d'occurences différent de 1 !"));
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  switch ($row['statut_juridique']) {
  case 1 : //PP
    $nom = $row['pp_prenom'] . " " . $row['pp_nom'];
    break;
  case 2 : //PM
    $nom = $row['pm_raison_sociale'];
    break;
  case 3 : //GI
    $nom = $row['gi_nom'];
  case 4 : //GS
    $nom = $row['gi_nom'];
    break;
  default : //Autre
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Statut juridique inconnu !"));
    break;
  }
  $dbHandler->closeConnection(true);
  return $nom;
}
function getNewAdhesions($id_ag, $date) { // PS qui renvoie un tableau avec les ID des clients dont la date d'adhésione st $date
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_client, id_ag, pp_nom, pm_raison_sociale, statut_juridique, gi_nom, sect_act, gestionnaire FROM ad_cli WHERE date_adh = '$date' AND id_ag = $id_ag order by statut_juridique";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newAdh = array ();
  while ($tmprow = $result->fetchrow()){
    $newAdh[$tmprow[0]]["id_client"] = $tmprow[0];
    $newAdh[$tmprow[0]]["id_ag"] = $tmprow[1];
    $newAdh[$tmprow[0]]["pp_nom"] = $tmprow[2];
    $newAdh[$tmprow[0]]["pm_raison_sociale"] = $tmprow[3];
    $newAdh[$tmprow[0]]["statut_juridique"] = $tmprow[4];
    $newAdh[$tmprow[0]]["gi_nom"] = $tmprow[5];
    $newAdh[$tmprow[0]]["sect_act"] = $tmprow[6];
    $newAdh[$tmprow[0]]["gestionnaire"] = $tmprow[7];
  }
  $dbHandler->closeConnection(true);
  return $newAdh;
}
function getNewDefections($id_ag, $date) { // PS qui renvoie un tableau associatif avec les ID des clients dont la date de défection st $date
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_client, id_ag, pp_nom, pm_raison_sociale, statut_juridique, gi_nom, sect_act, gestionnaire, date_adh, etat FROM ad_cli WHERE date_defection = '$date' AND id_ag = $id_ag order by statut_juridique";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newDef = array ();
  while ($tmprow = $result->fetchrow()){
    $newDef[$tmprow[0]]["id_client"] = $tmprow[0];
    $newDef[$tmprow[0]]["id_ag"] = $tmprow[1];
    $newDef[$tmprow[0]]["pp_nom"] = $tmprow[2];
    $newDef[$tmprow[0]]["pm_raison_sociale"] = $tmprow[3];
    $newDef[$tmprow[0]]["statut_juridique"] = $tmprow[4];
    $newDef[$tmprow[0]]["gi_nom"] = $tmprow[5];
    $newDef[$tmprow[0]]["sect_act"] = $tmprow[6];
    $newDef[$tmprow[0]]["gestionnaire"] = $tmprow[7];
    $newDef[$tmprow[0]]["date_adh"] = $tmprow[8];
    $newDef[$tmprow[0]]["etat"] = $tmprow[9];
  }
  $dbHandler->closeConnection(true);
  return $newDef;
}
function getNewOuvertures($id_ag, $date) { // PS qui renvoie un tableau avec les ID des comptes dont la date d'ouverture est $date
  global $dbHandler;
  global $global_id_agence;
  $creProd = getCreditProductID($global_id_agence);
  $AGC = getAgenceDatas($global_id_agence);
  $db = $dbHandler->openConnection();
  if ($AGC['type_structure'] == 3)
    $sql = "SELECT id_cpte, id_prod, devise, id_ag, num_complet_cpte, id_titulaire, solde  FROM ad_cpt WHERE date_ouvert = '$date' AND id_prod <> $creProd AND id_prod <> 1 AND id_ag = $id_ag";
  else
    $sql = "SELECT id_cpte, id_prod, devise, id_ag, num_complet_cpte, id_titulaire, solde  FROM ad_cpt WHERE date_ouvert = '$date' AND id_prod <> $creProd AND id_ag = $id_ag";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newOuv = array ();
  while ($tmprow = $result->fetchrow()){
    $newOuv[$tmprow[0]]["id_cpte"] = $tmprow[0];
    $newOuv[$tmprow[0]]["id_prod"] = $tmprow[1];
    $newOuv[$tmprow[0]]["devise"] = $tmprow[2];
    $newOuv[$tmprow[0]]["id_ag"] = $tmprow[3];
    $newOuv[$tmprow[0]]["num_complet_cpte"] = $tmprow[4];
    $newOuv[$tmprow[0]]["id_titulaire"] = $tmprow[5];
    $newOuv[$tmprow[0]]["solde"] = $tmprow[6];
  }
   $dbHandler->closeConnection(true);
  return $newOuv;
}


function getNewClotures($id_ag, $date) { // PS qui renvoie un tableau avec les ID des comptes dont la date de cloture est $date
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_cpte, id_prod, devise, id_ag, num_complet_cpte, id_titulaire, solde_clot, raison_clot, date_ouvert FROM ad_cpt WHERE date_clot = '$date' AND id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newClot = array ();
  while ($tmprow = $result->fetchrow()){
    $newClot[$tmprow[0]]["id_cpte"] = $tmprow[0];
    $newClot[$tmprow[0]]["id_prod"] = $tmprow[1];
    $newClot[$tmprow[0]]["devise"] = $tmprow[2];
    $newClot[$tmprow[0]]["id_ag"] = $tmprow[3];
    $newClot[$tmprow[0]]["num_complet_cpte"] = $tmprow[4];
    $newClot[$tmprow[0]]["id_titulaire"] = $tmprow[5];
    $newClot[$tmprow[0]]["solde_clot"] = $tmprow[6];
    $newClot[$tmprow[0]]["raison_clot"] = $tmprow[7];
    $newClot[$tmprow[0]]["date_ouvert"] = $tmprow[8];
  }
  $dbHandler->closeConnection(true);
  return $newClot;
}

function getNewDATDecisionPrise($id_ag, $date, $decision)// PS qui renvoie un tableau avec les ID des comptes dont la date d'ouverture est $date
// IN : $date = DAte à laquelle la décision a été prise
//    : $decision : true si décisiond e prolonger, false sinon
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $dec = ($decision) ? 't' : 'f';
  $sql = "SELECT id_cpte, c.id_prod, p.devise, c.id_ag, c.num_complet_cpte, c.id_titulaire, c.solde, c.dat_date_fin FROM ad_cpt c, adsys_produit_epargne p WHERE c.id_ag = p.id_ag AND c.id_ag = $id_ag AND c.dat_date_decision_client = '$date' AND c.dat_decision_client = 't' AND c.dat_prolongation = '$dec' AND p.dat_prolongeable = 't' AND c.id_prod = p.id";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newDAT = array ();
  while ($tmprow = $result->fetchrow()){
    $newDAT[$tmprow[0]]["id_cpte"] = $tmprow[0];
    $newDAT[$tmprow[0]]["id_prod"] = $tmprow[1];
    $newDAT[$tmprow[0]]["devise"] = $tmprow[2];
    $newDAT[$tmprow[0]]["id_ag"] = $tmprow[3];
    $newDAT[$tmprow[0]]["num_complet_cpte"] = $tmprow[4];
    $newDAT[$tmprow[0]]["id_titulaire"] = $tmprow[5];
    $newDAT[$tmprow[0]]["solde"] = $tmprow[6];
    $newDAT[$tmprow[0]]["dat_date_fin"] = $tmprow[7];
  }
  $dbHandler->closeConnection(true);
  return $newDAT;
}

function getNewDCR($id_ag, $date) { // PS qui renvoie un tableau avec les ID des dossiers de crédit dont la date de demande est $date
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_doss, id_ag, id_client, id_prod, mnt_dem, duree_mois, obj_dem, id_agent_gest FROM ad_dcr WHERE date_dem = '$date' AND id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newDCR = array ();
  while ($tmprow = $result->fetchrow()){
    $newDCR[$tmprow[0]]["id_doss"] = $tmprow[0];
    $newDCR[$tmprow[0]]["id_ag"] = $tmprow[1];
    $newDCR[$tmprow[0]]["id_client"] = $tmprow[2];
    $newDCR[$tmprow[0]]["id_prod"] = $tmprow[3];
    $newDCR[$tmprow[0]]["mnt_dem"] = $tmprow[4];
    $newDCR[$tmprow[0]]["duree_mois"] = $tmprow[5];
    $newDCR[$tmprow[0]]["obj_dem"] = $tmprow[6];
    $newDCR[$tmprow[0]]["id_agent_gest"] = $tmprow[7];
  }
  $dbHandler->closeConnection(true);
  return $newDCR;
}

function getNewApprobDCR($id_ag, $date) { // PS qui renvoie un tableau avec les ID des dossiers de crédit approuvés le $date
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT d.id_doss, d.id_ag, d.id_client, d.id_prod, d.mnt_dem, d.cre_mnt_octr, d.duree_mois, d.obj_dem, d.id_agent_gest FROM ad_his h, ad_dcr d WHERE h.infos = d.id_doss::text AND date(h.date) = '$date' AND h.type_fonction = 110 AND d.id_ag = h.id_ag AND h.id_ag = $id_ag ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newAppDCR = array ();
  while ($tmprow = $result->fetchrow()){
    $newAppDCR[$tmprow[0]]["id_doss"] = $tmprow[0];
    $newAppDCR[$tmprow[0]]["id_ag"] = $tmprow[1];
    $newAppDCR[$tmprow[0]]["id_client"] = $tmprow[2];
    $newAppDCR[$tmprow[0]]["id_prod"] = $tmprow[3];
    $newAppDCR[$tmprow[0]]["mnt_dem"] = $tmprow[4];
    $newAppDCR[$tmprow[0]]["cre_mnt_octr"] = $tmprow[5];
    $newAppDCR[$tmprow[0]]["duree_mois"] = $tmprow[6];
    $newAppDCR[$tmprow[0]]["obj_dem"] = $tmprow[7];
    $newAppDCR[$tmprow[0]]["id_agent_gest"] = $tmprow[8];
  }
  $dbHandler->closeConnection(true);
  return $newAppDCR;
}

function getNewRejetDCR($id_ag, $date) { // PS qui renvoie un tableau avec les ID des dossiers de crédit rejetés le $date
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT d.id_doss, d.id_ag, d.id_client, d.id_prod, d.mnt_dem, d.duree_mois, d.obj_dem, d.id_agent_gest FROM ad_his h, ad_dcr d WHERE h.infos = d.id_doss::text AND date(h.date) = '$date' AND h.type_fonction = 115 AND d.id_ag = h.id_ag AND h.id_ag = $id_ag ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newRejDCR = array ();
  while ($tmprow = $result->fetchrow()){
    $newRejDCR[$tmprow[0]]["id_doss"] = $tmprow[0];
    $newRejDCR[$tmprow[0]]["id_ag"] = $tmprow[1];
    $newRejDCR[$tmprow[0]]["id_client"] = $tmprow[2];
    $newRejDCR[$tmprow[0]]["id_prod"] = $tmprow[3];
    $newRejDCR[$tmprow[0]]["mnt_dem"] = $tmprow[4];
    $newRejDCR[$tmprow[0]]["duree_mois"] = $tmprow[5];
    $newRejDCR[$tmprow[0]]["obj_dem"] = $tmprow[6];
    $newRejDCR[$tmprow[0]]["id_agent_gest"] = $tmprow[7];
  }
  $dbHandler->closeConnection(true);
  return $newRejDCR;
}

function getNewAnnuleDCR($id_ag, $date) { // PS qui renvoie un tableau avec les ID des dossiers de crédit annulés le $date
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT d.id_doss, d.id_ag, d.id_client, d.id_prod, d.mnt_dem, d.duree_mois, d.obj_dem, d.id_agent_gest FROM ad_his h, ad_dcr d WHERE h.infos = d.id_doss::text AND date(h.date) = '$date' AND h.type_fonction = 120 AND d.id_ag = h.id_ag AND h.id_ag = $id_ag ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newAnnDCR = array ();
  while ($tmprow = $result->fetchrow()){
    $newAnnDCR[$tmprow[0]]["id_doss"] = $tmprow[0];
    $newAnnDCR[$tmprow[0]]["id_ag"] = $tmprow[1];
    $newAnnDCR[$tmprow[0]]["id_client"] = $tmprow[2];
    $newAnnDCR[$tmprow[0]]["id_prod"] = $tmprow[3];
    $newAnnDCR[$tmprow[0]]["mnt_dem"] = $tmprow[4];
    $newAnnDCR[$tmprow[0]]["duree_mois"] = $tmprow[5];
    $newAnnDCR[$tmprow[0]]["obj_dem"] = $tmprow[6];
    $newAnnDCR[$tmprow[0]]["id_agent_gest"] = $tmprow[7];
  }
  $dbHandler->closeConnection(true);
  return $newAnnDCR;
}

function getNewDebourseDCR($id_ag, $date) { // PS qui renvoie un tableau avec les ID des dossiers de crédit déboursés le $date
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT d.id_doss, d.id_ag, d.id_client, d.id_prod, d.mnt_dem, d.cre_mnt_octr, d.duree_mois, d.obj_dem, d.id_agent_gest FROM ad_his h, ad_dcr d WHERE h.infos = d.id_doss::text AND date(h.date) = '$date' AND h.type_fonction = 125 AND d.id_ag = h.id_ag AND h.id_ag = $id_ag ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newDebDCR = array ();
  while ($tmprow = $result->fetchrow()){
    $newDebDCR[$tmprow[0]]["id_doss"] = $tmprow[0];
    $newDebDCR[$tmprow[0]]["id_ag"] = $tmprow[1];
    $newDebDCR[$tmprow[0]]["id_client"] = $tmprow[2];
    $newDebDCR[$tmprow[0]]["id_prod"] = $tmprow[3];
    $newDebDCR[$tmprow[0]]["mnt_dem"] = $tmprow[4];
    $newDebDCR[$tmprow[0]]["cre_mnt_octr"] = $tmprow[5];
    $newDebDCR[$tmprow[0]]["duree_mois"] = $tmprow[6];
    $newDebDCR[$tmprow[0]]["obj_dem"] = $tmprow[7];
    $newDebDCR[$tmprow[0]]["id_agent_gest"] = $tmprow[8];
    }
  $dbHandler->closeConnection(true);
  return $newDebDCR;
}

function getNewCreditsRepris($id_ag, $date) {
  /*
   PS qui renvoie un tableau avec les ID des dossiers repris le jour $date
  */
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  //chercher les crédits repris aujourd'hui quel que soit l'état
  $sql = "SELECT b.id_client, b.id_doss, b.cre_etat, b.id_prod, b.cre_mnt_octr ";
  $sql .= "FROM ad_his a, ad_dcr b ";
  $sql .= "WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence AND a.id_client = b.id_client AND ";
  $sql .= "type_fonction = 503 AND date(date) = '$date' ;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newCrdRep = array ();
  while ($tmprow = $result->fetchrow())
    array_push($newCrdRep, $tmprow[0]);
  $dbHandler->closeConnection(true);
  return $newCrdRep;
}

function getNewPartsReprises($date) { // PS qui renvoie un tableau avec les ID des clients dont leurs parts sociales sont reprises le $date
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT distinct id_client FROM ad_his WHERE date(date) = '$date' AND type_fonction = 502 AND id_client IS NOT NULL AND id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newPS = array ();
  while ($tmprow = $result->fetchrow())
    array_push($newPS, $tmprow[0]);
  $dbHandler->closeConnection(true);
  return $newPS;
}

function getNewAjustementsSoldes($date) {
  // Fonction qui renvoie l'ensemble des ajustements sur des soldes pour une date donnée
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_his WHERE id_ag = $global_id_agence AND type_fonction = 235 AND date(date) = '$date'";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $DATAS = array ();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $INFO = array ();
    $INFO["id_client"] = $tmprow["id_client"];
    $INFO["nom_client"] = getClientName($INFO["id_client"]);
    // Récupération du nom de l'utilisateur associé
    $sql = "SELECT id_utilisateur FROM ad_log WHERE login='" . $tmprow["login"] . "'";
    $result2 = $db->query($sql);
    if (DB :: isError($result2)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result2->getMessage()); // $result2->getMessage()
    }
    $row2 = $result2->fetchrow();
    $nom_uti = getNomUtilisateur($row2[0]);
    $INFO["login"] = $tmprow["login"] . " ($nom_uti)";
    $tmp_dte1 = pg2phpDatebis($tmprow["date"]);
    $INFO["heure"] = $tmp_dte1[3] . ":" . $tmp_dte1[4];
    // Réxupération des données du champs infos
    $infos = $tmprow["infos"];
    list ($id_cpte, $anc_solde, $nouv_solde) = explode("|", $infos);
    $ACC = getAccountDatas($id_cpte);
    $INFO["num_cpte"] = $ACC["num_complet_cpte"];
    $INFO["anc_solde"] = afficheMontant($anc_solde, false);
    $INFO["nouv_solde"] = afficheMontant($nouv_solde, false);
    array_push($DATAS, $INFO);
  }
  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getNewDelestageGui($id_ag, $date) { // PS qui renvoie un tableau contenat les infos sur les délestages du jour $date
  global $dbHandler, $global_id_agence, $global_multidevise;
  $db = $dbHandler->openConnection();
  $sql = "select g.id_gui, g.libel_gui, m.montant, m.devise from ad_gui g, ad_mouvement m, ad_ecriture e, ad_his h ";
  if ($global_multidevise)
  	$sql .= " where m.compte = g.cpte_cpta_gui||'.'||m.devise and m.sens= 'c' and date(m.date_valeur) = '$date' and h.type_fonction= 156 ";
  else
  	$sql .= " where m.compte = g.cpte_cpta_gui and m.sens= 'c' and date(m.date_valeur) = '$date' and h.type_fonction= 156 ";
  $sql .= " and h.id_his = e.id_his and e.id_ecriture = m.id_ecriture and h.id_ag = e.id_ag and e.id_ag = m.id_ag and m.id_ag = g.id_ag and g.id_ag = $id_ag order by g.id_gui ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
   $newDelGui = array ();
  while ($tmprow = $result->fetchrow()){
    $newDelGui[$tmprow[0]][$tmprow[3]]["id_gui"] = $tmprow[0];
    $newDelGui[$tmprow[0]][$tmprow[3]]["libel_gui"] = $tmprow[1];
    $newDelGui[$tmprow[0]][$tmprow[3]]["montant"] += $tmprow[2];
    $newDelGui[$tmprow[0]][$tmprow[3]]["devise"] = $tmprow[3];
    }
  $dbHandler->closeConnection(true);
  return $newDelGui;
}

function getNewApprGui($id_ag, $date) { // PS qui renvoie un tableau contenat les infos sur les approvisionnements du jour $date
  global $dbHandler, $global_id_agence, $global_multidevise;
  $db = $dbHandler->openConnection();
  $sql = "select g.id_gui, g.libel_gui, m.montant, m.devise from ad_gui g, ad_mouvement m, ad_ecriture e, ad_his h ";
  if ($global_multidevise)
  	$sql .= " where m.compte = g.cpte_cpta_gui||'.'||m.devise and m.sens= 'd' and date(m.date_valeur) = '$date' and h.type_fonction= 155 ";
  else
  	$sql .= " where m.compte = g.cpte_cpta_gui and m.sens= 'd' and date(m.date_valeur) = '$date' and h.type_fonction= 155 ";
  $sql .= " and h.id_his = e.id_his and e.id_ecriture = m.id_ecriture and h.id_ag = e.id_ag and e.id_ag = m.id_ag and m.id_ag = g.id_ag and g.id_ag = $id_ag order by g.id_gui";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
   $newAppGui = array ();
  while ($tmprow = $result->fetchrow()){
    $newAppGui[$tmprow[0]][$tmprow[3]]["id_gui"] = $tmprow[0];
    $newAppGui[$tmprow[0]][$tmprow[3]]["libel_gui"] = $tmprow[1];
    $newAppGui[$tmprow[0]][$tmprow[3]]["montant"] += $tmprow[2];
    $newAppGui[$tmprow[0]][$tmprow[3]]["devise"] = $tmprow[3];
    }
  $dbHandler->closeConnection(true);
  return $newAppGui;
}

function getNewDep($id_ag, $date) { // PS qui renvoie un tableau contenat les infos sur les dépenses du jour $date
  global $dbHandler, $global_id_agence, $global_multidevise;
  $db = $dbHandler->openConnection();
  //On récupère toutes les écritures des opérations diverses dont le type de mouvement est mouvement de caisse et le sens est au débit
  $sql = "SELECT compte, montant, devise, libel_ecriture from ad_his h, ad_ecriture e , ad_mouvement m ";
 	$sql .= " where h.type_fonction = 189 and h.id_his=e.id_his and e.id_ecriture=m.id_ecriture and m.sens='d' and date(m.date_valeur) = '$date' " ;
 	$sql .= " and h.id_ag = e.id_ag and e.id_ag = m.id_ag and m.id_ag = $id_ag ";
 	$sql .= " and compte IN (SELECT b.num_cpte from ad_cpt_ope a, ad_cpt_ope_cptes b ";
 	$sql .= " where a.categorie_ope = '2' and b.sens = 'd' and a.type_operation = b.type_operation and b.num_cpte is NOT NULL and a.id_ag = b.id_ag and b.id_ag = $id_ag ) " ;
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
   $newDep = array ();
  while ($tmprow = $result->fetchrow()){
    $newDep[$tmprow[0]]["compte"] = $tmprow[0];
    $newDep[$tmprow[0]]["montant"] += $tmprow[1];
    $newDep[$tmprow[0]]["devise"] = $tmprow[2];
    $newDep[$tmprow[0]]["libel_ecriture"] = $tmprow[3];
    }
  $dbHandler->closeConnection(true);
  return $newDep;
}

function getSituationCoffr($id_ag, $date) { // PS qui renvoie un tableau contenat les infos sur la situation du coffre-fort du jour $date
  global $dbHandler, $global_id_agence, $global_multidevise;
  $db = $dbHandler->openConnection();

  $sql = "SELECT  m.devise, m.sens, m.montant, cpte_cpta_coffre from ad_agc a, ad_mouvement m ";
  if ($global_multidevise)
  	$sql .= " where m.compte = a.cpte_cpta_coffre||'.'||m.devise and date(m.date_valeur) = '$date' and m.id_ag = a.id_ag and a.id_ag = $id_ag ";
  else
  	$sql .= " where m.compte = a.cpte_cpta_coffre and date(m.date_valeur) = '$date' and m.id_ag = a.id_ag and a.id_ag = $id_ag ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $newSitCoff = array ();
  while ($tmprow = $result->fetchrow()){
  	 if ($global_multidevise)
			$compte = $tmprow[3].".".$tmprow[0];
		else
			$compte = $tmprow[3];
    if($tmprow[1] == 'c')
    	$newSitCoff[$compte]["montant_cred"] += $tmprow[2];
    else
    	$newSitCoff[$compte]["montant_deb"] += $tmprow[2];
		$newSitCoff[$compte]["compte"] = $compte;
    $newSitCoff[$compte]["devise"] = $tmprow[0];
  }
	 foreach($newSitCoff as $key_compte => $value){
		//récupérer le solde du coffre-fort dans chaque devise à la date donnée

		//récupérer le montant inscrit au crédit depuis la date donnée à la date d'aujourd'hui
		$sql = "SELECT  sum(m.montant) from ad_mouvement m where sens= 'c' and compte = '$key_compte' and date(m.date_valeur) > '$date' and date(m.date_valeur) <= date(now())";
		$result1 = $db->query($sql);
	  if (DB :: isError($result1)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result1->getMessage());
	  }
	  $row = $result1->fetchrow();
  	$mnt_credit = $row[0];
  	//récupérer le montant inscrit au débit depuis la date donnée à la date d'aujourd'hui
		$sql = "SELECT  sum(m.montant) from ad_mouvement m where sens= 'd' and compte = '$key_compte' and date(m.date_valeur) > '$date' and date(m.date_valeur) <= date(now())";
		$result1 = $db->query($sql);
	  if (DB :: isError($result1)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result1->getMessage());
	  }
	  $row = $result1->fetchrow();
  	$mnt_debit = $row[0];

  	//récupérer le solde du compte
		$sql = "SELECT  solde from ad_cpt_comptable m where num_cpte_comptable= '$key_compte'";
		$result1 = $db->query($sql);
	  if (DB :: isError($result1)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result1->getMessage());
	  }
	  $row = $result1->fetchrow();
  	$solde = (-1)*$row[0];
		$solde = $solde - $mnt_debit;
		$solde = $solde + $mnt_credit;
    $newSitCoff[$key_compte]["solde"] = $solde;
  }
	$dbHandler->closeConnection(true);
  return $newSitCoff;

}


function get_credits_retard($gestionnaire = 0, $etat = 0) {
  /* Renvoie différentes infos concernant les crédits en retard */
  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie;
  global $global_id_agence;

  $db = $dbHandler->openConnection();
  //Init
  $retour = array ();
  $tabGS = array();
  $retour['detail'] = array ();
  $retour['nbre_credits_retard'] = 0;
  $retour['total_solde_pen'] = 0;
  $retour['total_solde_int'] = 0;
  $retour['total_solde_cap'] = 0;
  $retour['total_solde_gar'] = 0;
  $retour['total_retard_int'] = 0;
  $retour['total_retard_cap'] = 0;
  $retour['total_retard_gar'] = 0;
  $retour['total_gar_num'] = 0;
  $retour['total_prov_mnt'] = 0;
  $date_str = date("d") . "/" . date("m") . "/" . date("Y"); //String pour la comparaison de date
  /* recupère l'id de l'état en perte */
   $idEtatPerte = getIDEtatPerte();

  //Récupère le nombre de crédits total
  if ($gestionnaire > 0 && $etat > 0)
    $sql = "SELECT count(*) FROM ad_dcr WHERE ((etat = 5) OR (etat = 7) OR (etat = 13) OR (etat = 14) OR (etat = 15)) and cre_etat = $etat and (id_agent_gest = $gestionnaire ) and (id_ag = $global_id_agence) ";
  elseif ($gestionnaire > 0 && $etat == 0)
  $sql = "SELECT count(*) FROM ad_dcr WHERE ((etat = 5) OR (etat = 7) OR (etat = 13) OR (etat = 14) OR (etat = 15)) and (id_agent_gest = $gestionnaire ) and (id_ag = $global_id_agence) ";
  elseif ($gestionnaire == 0 && $etat > 0)
  $sql = "SELECT count(*) FROM ad_dcr WHERE ((etat = 5) OR (etat = 7) OR (etat = 13) OR (etat = 14) OR (etat = 15)) and cre_etat = $etat and (id_ag = $global_id_agence) ";
  elseif ($gestionnaire == 0 && $etat == 0)
  $sql = "SELECT count(*) FROM ad_dcr WHERE ((etat = 5) OR (etat = 7) OR (etat = 13) OR (etat = 14) OR (etat = 15)) and (id_ag = $global_id_agence) ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $row = $result->fetchrow();
  $retour['nbre_credits'] = $row[0];
  // Récupère le volume du portefeuille de crédit
  if ($gestionnaire > 0 && $etat > 0) {
    $sql = "SELECT sum(calculeCV(sum, devise, '$global_monnaie')) FROM (SELECT sum(solde_cap), devise FROM ad_etr a, adsys_produit_credit b, ad_dcr c WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = $global_id_agence AND a.id_doss = c.id_doss AND c.id_agent_gest=$gestionnaire AND c.id_prod = b.id AND (c.etat = 5 OR c.etat = 7 OR c.etat = 13 OR c.etat = 14 OR c.etat = 15) AND c.cre_etat = $etat  GROUP BY devise) AS t;";
  }
  elseif ($gestionnaire == 0 && $etat > 0) {
    $sql = "SELECT sum(calculeCV(sum, devise, '$global_monnaie')) FROM (SELECT sum(solde_cap), devise FROM ad_etr a, adsys_produit_credit b, ad_dcr c WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = $global_id_agence AND a.id_doss = c.id_doss AND c.id_prod = b.id AND (c.etat = 5 OR c.etat = 7 OR c.etat = 13 OR c.etat = 14 OR c.etat = 15) AND c.cre_etat = $etat  GROUP BY devise) AS t;";
  }
  elseif ($gestionnaire > 0 && $etat == 0) {
    $sql = "SELECT sum(calculeCV(sum, devise, '$global_monnaie')) FROM (SELECT sum(solde_cap), devise FROM ad_etr a, adsys_produit_credit b, ad_dcr c WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = $global_id_agence AND a.id_doss = c.id_doss AND c.id_prod = b.id AND (c.etat = 5 OR c.etat = 7 OR c.etat = 13 OR c.etat = 14 OR c.etat = 15) AND c.cre_etat!=$idEtatPerte AND c.id_agent_gest=$gestionnaire  GROUP BY devise) AS t;";
  }
  elseif ($gestionnaire == 0 && $etat == 0) {
    $sql = "SELECT sum(calculeCV(sum, devise, '$global_monnaie')) FROM (SELECT sum(solde_cap), devise FROM ad_etr a, adsys_produit_credit b, ad_dcr c WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = $global_id_agence AND a.id_doss = c.id_doss AND c.id_prod = b.id AND (c.etat = 5 OR c.etat = 7 OR c.etat = 13 OR c.etat = 14 OR c.etat = 15) AND c.cre_etat!=$idEtatPerte GROUP BY devise) AS t;";
  }

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $row = $result->fetchrow();
  $retour['portefeuille'] = $row[0];
  //Récupère les n° de crédits qui sont en retard
  if ($gestionnaire > 0 && $etat > 0) {

    $sql = "select a.id_doss, a.id_client, a.devise, a.cre_etat, a.gs_cat, a.id_dcr_grp_sol, a.id from get_ad_dcr_ext_credit(null, null, null, $etat, $global_id_agence) a where a.cre_etat>=2 and a.cre_etat = $etat and (a.etat=5 OR a.etat=7 OR a.etat=13 OR a.etat=14 OR a.etat=15) AND a.id_agent_gest=$gestionnaire ORDER BY a.id_doss, a.id_client";
  } elseif ($gestionnaire == 0 && $etat > 0) {

    $sql = "select a.id_doss, a.id_client, a.devise, a.cre_etat, a.gs_cat, a.id_dcr_grp_sol, a.id from get_ad_dcr_ext_credit(null, null, null, $etat, $global_id_agence) a where a.cre_etat>=2 and a.cre_etat = $etat and (a.etat=5 OR a.etat=7 OR a.etat=13 OR a.etat=14 OR a.etat=15) ORDER BY a.id_doss, a.id_client";
  } elseif ($gestionnaire > 0 && $etat == 0) {

    $sql = "select a.id_doss, a.id_client, a.devise, a.cre_etat, a.gs_cat, a.id_dcr_grp_sol, a.id from get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) a where a.cre_etat>=2 and a.cre_etat != $idEtatPerte and a.id_agent_gest=$gestionnaire and (a.etat=5 OR a.etat=7 OR a.etat=13 OR a.etat=14 OR a.etat=15) ORDER BY a.id_doss, a.id_client"; // TODO : add param agent gest
  } elseif ($gestionnaire == 0 && $etat == 0) {

    $sql = "select a.id_doss, a.id_client, a.devise, a.cre_etat, a.gs_cat, a.id_dcr_grp_sol, a.id from get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) a where a.cre_etat>=2 and a.cre_etat != $idEtatPerte and (a.etat=5 OR a.etat=7 OR a.etat=13 OR a.etat=14 OR a.etat=15) ORDER BY a.id_doss, a.id_client";
  }
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $dossiers = array ();
  $k = 0;
  while ($row = $result->fetchrow()) {
  	// tableau $doss contient quelques infos des dossiers de crédits réels
  	$doss["id_doss"] 		= $row[0];
  	$doss["id_client"] 		= $row[1];
  	$doss["devise"] 		= $row[2];
  	$doss["gs_cat" ] 		= $row[4];
  	$doss["id_dcr_grp_sol"] = $row[5];
  	$doss["id_prod"] 		= $row[6];

    //recuperation du crédit solidaire à dossiers multiples
    $groupe = getCreditSolDetailRap($doss);
    if ((is_array($groupe["credit_gs"]))&&(!in_array($groupe["credit_gs"]["id_client"],$tabGS))){
    	$tabGS[]  = $groupe["credit_gs"]["id_client"];
    	$groupe["credit_gs"]["membre"] = 0;
    	array_push($dossiers,$groupe["credit_gs"]);
    }
    if (($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($doss["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"])) $doss["membre"] = 1;
    else $doss["membre"] = 0;
    array_push($dossiers,$doss);
   //récuperation des crédits des membres d'un groupe solidaire à dossier unique
    if (is_array($groupe[$doss["id_client"]])) {
    	$i = 0;
   		while($i < count($groupe[$doss["id_client"]])) {
   			$groupe[$doss["id_client"]][$i]["credit_doss_unique"] = $doss["id_client"];
   			array_push($dossiers,$groupe[$doss["id_client"]][$i]);
   			$i++;
    	}
    }
  }
  //On récupère les détails
  reset($dossiers);
  while (list (, $DOSS) = each($dossiers)) { // Pour chaque dossier qui est en retard
    $id = $DOSS["id_doss"];
    $devise = $DOSS["devise"];
    if (($id > 0) && (!isset($DOSS["credit_doss_unique"]))) {
    	$sql = "SELECT * FROM ad_etr WHERE (id_doss = $id) AND (remb = 'f') AND id_ag = $global_id_agence ORDER BY date_ech";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    $retour['detail'][$id]['retard_cap'] = 0;
    $retour['detail'][$id]['retard_int'] = 0;
    $retour['detail'][$id]['retard_gar'] = 0;
    $retour['detail'][$id]['solde_cap'] = 0;
    $retour['detail'][$id]['solde_int'] = 0;
    $retour['detail'][$id]['solde_gar'] = 0;
    $retour['detail'][$id]['solde_pen'] = 0;
    $retour['detail'][$id]['nbre_ech_retard'] = 0;
    ++ $retour['nbre_credits_retard'];
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) { //Pour chaque échéance non-remboursée du crédit en retard
      if (!isset ($retour['detail'][$id]['date_min']))
      $retour['detail'][$id]['date_min'] = $row['date_ech'];
      $retour['detail'][$id]['solde_cap'] += $row['solde_cap'];
      $retour['detail'][$id]['solde_int'] += $row['solde_int'];
      $retour['detail'][$id]['solde_gar'] += $row['solde_gar'];
      $retour['detail'][$id]['solde_pen'] += $row['solde_pen'];
      $retour['detail'][$id]['devise'] = $devise[$id]; //$dossiers['devise'];
      $cv_solde_pen = calculeCV($devise, $global_monnaie, $row['solde_pen']);
      $retour['total_solde_pen'] += $cv_solde_pen;
      $cv_solde_int = calculeCV($devise, $global_monnaie, $row['solde_int']);
      $retour['total_solde_int'] += $cv_solde_int;
      $cv_solde_gar = calculeCV($devise, $global_monnaie, $row['solde_gar']);
      $retour['total_solde_gar'] += $cv_solde_gar;
      $cv_solde_cap = calculeCV($devise, $global_monnaie, $row['solde_cap']);
      $retour['total_solde_cap'] += $cv_solde_cap;
      if (dateCompare($row['date_ech'], $date_str) == -1) { //Si cette échéance est en retard
        $retour['detail'][$id]['retard_cap'] += $row['solde_cap'];
        $retour['detail'][$id]['retard_int'] += $row['solde_int'];
        $retour['detail'][$id]['retard_gar'] += $row['solde_gar'];
        $retour['total_retard_pen'] += $cv_solde_pen;
        $retour['total_retard_int'] += $cv_solde_int;
        $retour['total_retard_gar'] += $cv_solde_gar;
        $retour['total_retard_cap'] += $cv_solde_cap;
        ++ $retour['detail'][$id]['nbre_ech_retard'];
      }
    }
  }
    //Récupère quelques infos du dossier
    if (($id > 0) && (!isset($DOSS["credit_doss_unique"]))) {
    $sql = "SELECT id_client, id_prod, cre_date_debloc, cre_mnt_octr, gar_num, cre_etat,prov_mnt, cre_mnt_deb, is_ligne_credit FROM ad_dcr WHERE id_doss = $id AND id_ag = $global_id_agence";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    } else
      if ($result->numrows() != 1) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); // "Retour DB incohérent !"
      }
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    //dossier simple;
    $retour['detail'][$id]['id_client'] = $row['id_client'];
    $retour['detail'][$id]['id_prod'] = $row['id_prod'];
    $retour['detail'][$id]['cre_date_debloc'] = $row['cre_date_debloc'];
    if ($row['is_ligne_credit'] == 't') {
        $retour['detail'][$id]['cre_mnt_octr'] = $row['cre_mnt_deb'];
    } else {
        $retour['detail'][$id]['cre_mnt_octr'] = $row['cre_mnt_octr'];
    }
    $retour['detail'][$id]['nom_client'] = getNomClient($row['id_client']);
    $retour['detail'][$id]['prov_mnt'] = $row['prov_mnt'];
    $cv_solde_prov_mnt = calculeCV($devise, $global_monnaie, $row['prov_mnt']);
    $retour['total_prov_mnt'] += $cv_solde_prov_mnt;
    $retour['detail'][$id]['cre_etat'] = $row['cre_etat'];
    $retour['detail'][$id]['gs_cat'] = $DOSS['gs_cat'];
    $retour['detail'][$id]['id_dcr_grp_sol'] = $DOSS['id_dcr_grp_sol'];
    if($DOSS["gs_cat"] == 2)
    	$retour['detail'][$id]['membre'] = 1;

    /* Récupération de l'épargne nantie numéraire du dossier appartenant au client lui-même */
    $liste_gar = getListeGaranties($id);
    foreach ($liste_gar as $key => $val) {
      /* la garantie doit être numéraire, non restituée et non réalisée */
      if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5) {
        $nantie = $val['gar_num_id_cpte_nantie'];
        if ($nantie != NULL) { /* S'il y a un compte d'épargne nantie associé au dossier de crédit */
          $CPT_GAR = getAccountDatas($nantie,$global_id_agence);
          $retour['detail'][$id]['gar_num'] += $CPT_GAR["solde"];
          $retour['total_gar_num'] += $CPT_GAR["solde"];
        }
      }
    }
    }
    // recuperation de quelques  informations sur les dossiers fictifs pour les credits solidaires
    else {
   		$id = $id."diff".$k;
    	$retour['detail'][$id]['id_client'] 	= $DOSS['id_client'];
    	$retour['detail'][$id]['id_prod'] 		= $DOSS['id_prod'];
    	$retour['detail'][$id]['membre'] 		= $DOSS['membre'];
    	$retour['detail'][$id]['gs_cat'] 		= $DOSS['gs_cat'];
   		$retour['detail'][$id]['nom_client'] 	= getNomClient($DOSS['id_client']);
   		$retour['detail'][$id]['gs_multiple'] 	= $DOSS['gs_multiple'];
   		$k++;
    }
  }
  $dbHandler->closeConnection(true);
  return $retour;

}

/**
 * Cette fonction renvoie les informations concernant les risques des crédits
 * @author Ibou Ndiaye
 * @since 2.8.10
 * @param : $gestionnaire, Gestionnaire dont on veut sélectionner les dossiers de crédit
 * @return : array ErrorObj(NO_ERR, $retour);
 *
 */
 function get_risques_credits($gestionnaire = 0, $export_date, $date_debloc_inf, $date_debloc_sup ,$id_prd, $zone,$n_agence=null,$n_guichet=null) {
 /* Renvoie différentes infos concernant les risques de crédits */
   global $dbHandler;
   global $global_multidevise;
   global $global_monnaie, $global_id_agence, $adsys, $error;
   $db = $dbHandler->openConnection();
   //Init
   $retour = array ();
   $retour['detail'] = array ();
   $retour['nbre_credits_retard'] = 0;
   $retour['total_solde_pen'] = 0;
   $retour['total_solde_int'] = 0;
   $retour['total_solde_cap'] = 0;
   $retour['total_solde_gar'] = 0;
   $retour['total_retard_int'] = 0;
   $retour['total_retard_cap'] = 0;
   $retour['total_retard_gar'] = 0;
   $retour['total_gar_num'] = 0;
   $retour['portefeuille'] = 0;
   
   /* Tous les états de crédit */
   $etats_credit = getTousEtatCredit($global_id_agence);
   
   $idEtatPerte = getIDEtatPerte();
   //Récupère les n° de crédits qui sont en retard
   $date = date('d/m/Y');
   if ($gestionnaire > 0){
   $sql = "SELECT g.*, ac.client_zone AS client_zone_id FROM getPortfeuilleView(date('".$export_date."'), $global_id_agence) g LEFT JOIN ad_cli ac ON ac.id_client = g.id_client AND ac.id_ag = g.id_ag WHERE id_etat_credit != $idEtatPerte AND id_agent_gest=$gestionnaire  ";

   // filtre sur date de deboursement(deblocage)
   if (isset ($date_debloc_inf))
   	$sql .= " AND cre_date_debloc >= date('" . $date_debloc_inf . "')";
   if (isset ($date_debloc_sup))
   	$sql .= " AND cre_date_debloc <= date('" . $date_debloc_sup . "')";
   
   //filtre par produit
   if (!empty($id_prd))
      $sql .= " AND id_prod=" . $id_prd;

    if($n_agence !=null){
        $sql .= " AND g.n_agence=" . $n_agence;
    }

     if($n_guichet !=null){
         $sql .= " AND g.n_guichet=" . $n_guichet;
     }

   
   $sql.= " ORDER BY nbr_jours_retard DESC ;";
   
   }else{
   
   $sql = "SELECT g.*, ac.client_zone FROM getPortfeuilleView(date('".$export_date."'), $global_id_agence) g INNER JOIN ad_cli ac ON ac.id_client = g.id_client AND ac.id_ag = g.id_ag WHERE id_etat_credit != $idEtatPerte ";
   // filtre sur date de deboursement(deblocage)
   if (isset ($date_debloc_inf))
   	$sql .= " AND cre_date_debloc >= date('" . $date_debloc_inf . "')";
   if (isset ($date_debloc_sup))
   	$sql .= " AND cre_date_debloc <= date('" . $date_debloc_sup . "')";
   
   //filtre par produit
   if (isset ($id_prd))
   	$sql .= " AND id_prod=" . $id_prd;

       if($n_agence !=null){
           $sql .= " AND g.n_agence=" . $n_agence;
       }

       if($n_guichet !=null){
           $sql .= " AND g.n_guichet=" . $n_guichet;
       }


       $zone_dataset = getZone();

   if(!empty($zone) && ($zone != -1)){
       $sql .= " AND client_zone IN ($zone)";
   }


      $sql.= " ORDER BY nbr_jours_retard DESC ;";
   
   }

   $result = $db->query($sql);
 	 if (DB :: isError($result)) {
 	 	$dbHandler->closeConnection(false);
 	 	signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
 	 }

 	 $dossiers = array ();
     $array_val = array();
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$id = $row["id_doss"];
		$devise = $row["devise"];
		$statut_juridique = getStatutJuridique($row['id_client']);
		$sexe = $adsys["adsys_sexe"][$row['pp_sexe']];
        $retour['detail'][$id]['id_client'] = $row['id_client'];
        $retour['detail'][$id]['nom_client'] = $row['nom_cli'];
        $retour['detail'][$id]['statut_jur'] = $statut_juridique;
        $retour['detail'][$id]['sexe'] = $sexe;
        $retour['detail'][$id]['id_prod'] = $row['id_prod'];
        $retour['detail'][$id]['duree'] = $row['duree_mois'];
        $retour['detail'][$id]['cre_date_debloc'] = $row['cre_date_debloc'];
        $retour['detail'][$id]['cre_mnt_octr'] = $row['cre_mnt_octr'];
        $retour['detail'][$id]['date_dernier_remb'] = $date_dernier_remb;
        $retour['detail'][$id]['date_dernier_ech_remb'] = $date_dernier_ech_remb;
        $retour['detail'][$id]['cre_etat'] = $row['id_etat_credit'];
        $retour['detail'][$id]['prov_mnt'] = $row['prov_mnt'];
                //$retour['detail'][$id]['prov_mnt'] = calculprovision($row["id_doss"], $row['id_etat_credit'], $etats_credit[$row['id_etat_credit']]["taux"], $export_date); // Added : Ticket #227

                if ($row["is_ligne_credit"] == 't') {
                    $retour['detail'][$id]['solde_cap'] = getCapitalRestantDuLcr($row["id_doss"], $export_date);
                } else {
                    $retour['detail'][$id]['solde_cap'] = $row["cre_mnt_octr"] - $row["mnt_cred_paye"];
                }

		$retour['detail'][$id]['solde_int'] = $row["mnt_int_att"] - $row["mnt_int_paye"];
		$retour['detail'][$id]['solde_gar'] = $row["mnt_gar_mob"];
		$retour['detail'][$id]['solde_pen'] = $row["mnt_pen_att"] - $row["mnt_pen_paye"];

		$retour['portefeuille'] += calculeCV($devise, $global_monnaie, $retour['detail'][$id]['solde_cap']);
		 

		$retour['detail'][$id]['nbr_jours_retard'] = $row["nbr_jours_retard"];
		$retour['detail'][$id]['nbre_ech_retard'] = $row["nbre_ech_retard"];
		$retour['detail'][$id]['retard_cap'] = $row["solde_retard"];
		$retour['detail'][$id]['retard_int'] = $row["int_retard"];
		$retour['detail'][$id]['retard_gar'] = $row["gar_retard"];
		if ($retour['detail'][$id]['cre_etat'] > 1){
			++ $retour['nbre_credits_retard'];
			$retour['portefeuille_retard'] += calculeCV($devise, $global_monnaie, $retour['detail'][$id]['solde_cap']);
			$retour['total_solde_int'] += calculeCV($devise, $global_monnaie, $retour['detail'][$id]['solde_int']);
			$retour['total_solde_gar'] += calculeCV($devise, $global_monnaie, $retour['detail'][$id]['solde_gar']);
			$retour['total_solde_pen'] += calculeCV($devise, $global_monnaie, $retour['detail'][$id]['solde_pen']);
			$retour['total_retard_cap'] += calculeCV($devise, $global_monnaie,$row["solde_retard"]);
			$retour['total_retard_int'] += calculeCV($devise, $global_monnaie,$row["int_retard"]);
			$retour['total_retard_gar'] += calculeCV($devise, $global_monnaie,$row["gar_retard"]);
		}
		//Récupère la date du dernier remboursement
		$sql1 = "select max(date_remb) from ad_sre where id_doss = $id AND date_remb <= '".$export_date."' AND id_ag = $global_id_agence ";
		$result1 = $db->query($sql1);
		if (DB :: isError($result1)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__, __LINE__, __FUNCTION__, $result1->getMessage());
		} else
		if ($result1->numrows() != 1) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__, __LINE__, __FUNCTION__, $result1->getMessage()); // "Retour DB incohérent !"
		}
		$row1 = $result1->fetchrow();
		$date_dernier_remb = $row1[0];

		//Récupère la date de la dernière échéanche remboursée
		$sql2 = "select max(date_ech) from (select * from ad_etr where id_doss = $id and remb = 't' AND date_ech <= '".$export_date."' AND id_ag = $global_id_agence ) as etr;";
		$result2 = $db->query($sql2);
 	    if (DB :: isError($result2)) {
 	      $dbHandler->closeConnection(false);
 	      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result2->getMessage());
 	    } else
 	    if ($result2->numrows() != 1) {
 	        $dbHandler->closeConnection(false);
 	        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result2->getMessage()); // "Retour DB incohérent !"
 	      }
 	    $row2 = $result2->fetchrow();
 	    $date_dernier_ech_remb = $row2[0];
 	    $retour['detail'][$id]['date_dernier_remb'] = $date_dernier_remb;
 	    $retour['detail'][$id]['date_dernier_ech_remb'] = $date_dernier_ech_remb;

 	    //$retour['portefeuille'] += $retour['detail'][$id]['solde_cap'];
 	    $retour['nbre_credits'] += 1;

        if(empty($zone)){          
            $libelle_zone = empty($row['client_zone'])?array("libelle_zone" => "non renseignée"):current(getZone(' id = ' . $row['client_zone']));
            $array_val[$libelle_zone["libelle_zone"]]["detail"][$id] = $retour['detail'][$id];
	    }
 	   }

 	   if(empty($zone)){
	       unset($retour['detail']);
           $retour["zone"] = $array_val;
       }

       $zone_keys_order = array_keys($retour["zone"]);
       if($zone_keys_order[0] == "non renseignée"){
         $shifted_element = array_shift($retour["zone"]);
         $retour["zone"][$zone_keys_order[0]] = $shifted_element;
       }
     $dbHandler->closeConnection(true);
 
  	return $retour;
 }


/**
 *
 * Renvoie l'historique des demandes de crédits introduites lors d'une certaine période
 *
 * @param array $DATA : Critère de recherche(
 * 								=> date_debut	: Date de déblocage Borne inférieure
 * 								=> date_fin		: Date de déblocage Borne supérieure
 * 								=> client			: ID Client
 * 								=> produit		: ID Produit de crédit
 * 								=> etat				: Etat dossier crédit)
 * @return array $lignes : Liste des dossier de crédit répondant aux paramètres
 * @package Rapports
 *
 */
function getHisDdeCrd($DATA, $a_gestionnaire=0, $i = 0) {
  global $dbHandler,$global_id_agence;
  $lignes = array ();
  $tabGS = array();
  $nombre = 0;
  $db = $dbHandler->openConnection();

  $etat = isset($DATA["etat"])?$DATA["etat"]:'null';

  $sql = "SELECT a.id_client,a.id_doss,a.id_prod,a.mnt_dem,a.date_dem, a.obj_dem, a.detail_obj_dem, a.detail_obj_dem_bis, a.cre_mnt_octr,a.devise, ";
  $sql .= " a.etat, a.cre_etat, a.cre_retard_etat_max, a.cre_date_approb, a.cre_date_debloc, a.duree_mois, id_agent_gest, a.terme,a.motif, a.gs_cat, a.id_dcr_grp_sol, ";
  $sql .= " b.pp_nom,b.pp_prenom,b.pm_raison_sociale,b.gi_nom,b.statut_juridique,";
  $sql .= " a.libel as libel_prd, ";
  $sql .= " d.nom, d.prenom, ";
  $sql .= " e.libel as libel_obj ";

  $sql .= " FROM get_ad_dcr_ext_credit(null, null, $etat, null, $global_id_agence) a, ad_cli b, adsys_objets_credits e, ad_uti d ";
  $sql .= " WHERE a.id_ag = b.id_ag and a.id_ag = d.id_ag and d.id_ag = $global_id_agence ";
  $sql .= " and a.id_client = b.id_client ";

  $sql .= " and a.id_agent_gest = d.id_utilis ";
 	$sql .= " and a.obj_dem = e.id ";

 	if (isset ($DATA["date_deb"]))
 	    $sql .= " AND date_dem >= date('" . $DATA["date_deb"] . "')";
  if (isset ($DATA["date_fin"]))
    $sql .= " AND date_dem <= date('" . $DATA["date_fin"] . "')";
 	if (isset ($DATA["num_client"]))
 	   $sql .= " and a.id_client= '" . $DATA["num_client"] . "' ";
  if (isset ($DATA["id_prod"]))
    $sql .= " and a.id_prod=" . $DATA["id_prod"];
  if (isset ($DATA["etat"]))
		$sql .= " and a.etat =". $DATA["etat"];
  if ( (isset($DATA["id_agent_gest"])) && ($DATA["id_agent_gest"] > 0))
    $sql .= " AND id_agent_gest=".$DATA["id_agent_gest"];

  //ici on optimise en ne générant que par palier de 4000 si le client non selectionné
 	$sql .= " AND a.id_client >= $i ";
 	$sql .= " order by a.id_dcr_grp_sol, a.id_client, a.id_doss, d.nom  ";
 	$sql .= " limit 4000;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); //  $result->getMessage()
  }
  $listEtatCredit = getTousEtatCredit(false);

  // Récuperation des détails objet demande
  $det_dem = getDetailsObjCredit();

  while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
  	// récupération du nombre d'échéances qui étaient dans l'état le plus avancé du crédit
  	if($ligne["cre_retard_etat_max"] > $ligne["cre_etat"])
 	 		$eta_plus_avance = $ligne["cre_retard_etat_max"];
 	 	else
 	 		$eta_plus_avance = $ligne["cre_etat"];
  	$ligne["nbr_ech_etat_plus_avance"] = 0;
  	$whereCond="WHERE id_doss='".$ligne["id_doss"]."' and id_ag = $global_id_agence ";
  	$echeance = getEcheancier($whereCond);
  	$nbr_ech = 0;
  	if($eta_plus_avance == 1){ // crédit est dans l'état sain
	  	$ligne["etat_plus_avance"] = $listEtatCredit[$eta_plus_avance]["libel"];
	  	$ligne["nbr_ech_etat_plus_avance"] = sizeof($echeance);
  	}
  	else{
  		$ligne["etat_plus_avance"] = $listEtatCredit[$eta_plus_avance]["libel"];
  		$nbr_ech = 0;
  		if(is_array($echeance))
	  	foreach($echeance as $cle=>$info) {
				$sql ="  SELECT max(date_remb) from ad_sre where id_doss= ".$ligne["id_doss"]." and id_ech= ".$info["id_ech"]." and id_ag = $global_id_agence ";
				$result_remb = $db->query($sql);
			  if (DB :: isError($result_remb)) {
			    $dbHandler->closeConnection(false);
			    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); //  $result->getMessage()
			  }
				$tmp_row = $result_remb->fetchrow();
			  if($info["remb"] == 't'){
			  	$tmp_date = pg2phpDatebis($tmp_row[0]);
			  	$date_dernier_remb = mktime(0,0,0, $tmp_date[0], $tmp_date[1], $tmp_date[2]);
			  }
			  else{
			  	$date_dernier_remb = mktime(0,0,0,date("m"), date("d"), date("y"));
			  }
			  $tmp_date_ech = pg2phpDatebis($info["date_ech"]);
			  $date_ech = mktime(0,0,0, $tmp_date_ech[0], $tmp_date_ech[1], $tmp_date_ech[2]);
			  if($date_dernier_remb > $date_ech){
			  	// nombre de jours écoulés entre date dernier remboursement échéance et date échéance
			  	$nbre_jours = ($date_dernier_remb - $date_ech) / (3600 * 24);
			  	// calculer le nombre de jours pour atteindre l'état le plus avancé
			  	$nbre_jours_eta_plus_avance = 0;
			  	$id_etat_prec = $listEtatCredit[$eta_plus_avance]["id_etat_prec"];
			  	while($id_etat_prec){
			  		$nbre_jours_eta_plus_avance += $listEtatCredit[$id_etat_prec]["nbre_jours"];
			  		$id_etat_prec = $listEtatCredit[$id_etat_prec]["id_etat_prec"];
			  	}
			  	if($nbre_jours >= $nbre_jours_eta_plus_avance)
			  		$nbr_ech += 1;
			  }

	  		}
  		$ligne["nbr_ech_etat_plus_avance"] = $nbr_ech;
  		if ($ligne["gs_cat"] == 2) {
	  		$ligne["membre_gs"]	= _("OUI");
	  	} elseif ($ligne["gs_cat"] == 1) {
	  		$ligne["groupe_solidaire"] = _("OUI");
	  	}

  	}
  	//
//  	if ($ligne["gs_cat"] == 2) {
//  		$ligne["membre_gs"]	= _("OUI");
//  	} elseif ($ligne["gs_cat"] == 1) {
//  		$ligne["groupe_solidaire"] = _("OUI");
//  	}


    //recuperation du crédit solidaire à dossiers multiples
    $groupe = getCreditSolDetailRap($ligne);
    if ((is_array($groupe["credit_gs"]))&&(!in_array($groupe["credit_gs"]["id_client"],$tabGS))){
    	$tabGS[]  = $groupe["credit_gs"]["id_client"];
    	$lignes[] = $groupe["credit_gs"];
    }
    if(($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($ligne["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"])) $ligne["membre"] = 1;
    else $ligne["membre"] = 0;

    if (isDcrDetailObjCreditLsb()) {
      $ligne['detail_obj_dem'] = $det_dem[$ligne['detail_obj_dem_bis']]['libel'];
    }

    $lignes[] = $ligne;
    //récuperation des crédits des membres d'un groupe solidaire à dossier unique
    if (is_array($groupe[$ligne["id_client"]])) {
    	$i = 0;
    	while($i < count($groupe[$ligne["id_client"]])) {
    		$lignes[]= $groupe[$ligne["id_client"]][$i];
    		$i++;
    	}
    }
  }
  $dbHandler->closeConnection(true);
  if (is_array($lignes)) {
    return $lignes;
  }
  else
    return NULL;
}

/**
 *Reworked by Kheshan A.G
 *BD-MU
 * Renvoie l'historique des crédits octroyés lors d'une certaine période
 *
 * @param array $criteres : Critère de recherche(
 * 								=> date_debut	: Date de déblocage Borne inférieure
 * 								=> date_fin		: Date de déblocage Borne supérieure
 * 								=> client			: ID Client
 * 								=> produit		: ID Produit de crédit
 * 								=> etat				: Etat dossier crédit)
 * $i = id client saisie
 * @return array $lignes : Liste des dossiers de crédit répondant aux paramètres
 * @package Rapports
 *
 */
function getDoneesRapOctr($criteres, $a_gestionnaire=0, $i = NULL,$n_agence = null, $n_guichet= null) {
	global $dbHandler, $global_id_agence;
	$db = $dbHandler->openConnection();

    $etat = isset($criteres["etat_dossier"])?$criteres["etat_dossier"]:'null';
	
	$sql ="SELECT a.id_doss, a.id_client, a.mnt_dem, a.cre_mnt_octr, a.cre_date_approb as date_oct, a.duree_mois, a.etat, a
.gs_cat, a.id_dcr_grp_sol, a.id_prod, a.id_agent_gest, b.pp_nom, b.pp_prenom, b.pm_raison_sociale, b.gi_nom, b
.statut_juridique, a.libel as libel_prod, a.devise, a.type_duree_credit as type_duree, d.nom, d.prenom FROM
get_ad_dcr_ext_credit(null, null, $etat, null, $global_id_agence) a, ad_cli b, ad_uti d WHERE a.id_ag = b.id_ag AND a.id_ag = d.id_ag AND d.id_ag = $global_id_agence and a.id_client=b.id_client AND a.id_agent_gest=d.id_utilis ";

	if (isset ($criteres["date_deb"]))
		$sql .= " AND cre_date_approb >= date('" . $criteres["date_deb"] . "')";
	if (isset ($criteres["date_fin"]))
		$sql .= " AND cre_date_approb <= date('" . $criteres["date_fin"] . "')";
	if (isset ($criteres["num_client"]))
		$sql .= " AND a.id_client= '" . $criteres["num_client"] . "' ";

	if (isset ($criteres["id_prod"]))
		$sql .= " AND a.id_prod=" . $criteres["id_prod"];

	if (isset ($criteres["etat_dossier"])) {
		$sql .= " AND a.etat =". $criteres["etat_dossier"];
    }
    else{ // AT-54 : Critrere "TOUS" les etats dossiers à ramener sont 5,6,7,9,10,11,12,13,14,15
      $sql .= " AND a.etat IN (5,6,7,9,10,11,12,13,14,15)";
    }

	if ( (isset($criteres["id_agent_gest"])) && ($criteres["id_agent_gest"] > 0))
		$sql .= " AND id_agent_gest=".$criteres["id_agent_gest"];

    if (isset ($i))
        $sql .= " AND a.id_client = $i ";

    if (isset($n_agence))
        $sql .= " AND b.n_agence = $n_agence";

    if (isset($n_guichet))
        $sql .= " AND b.n_guichet = $n_guichet";

	$sql .= " ORDER BY a.id_prod, a.id_doss, a.id_client ";

	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); //  $result->getMessage()
	}

    $dossiers = array();

	while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$dossiers [$tmprow['id_doss']] = $tmprow;

	}
	$dbHandler->closeConnection(true);
	if (is_array($dossiers)) {
		return $dossiers;
	}
	else
		return NULL;
}

/**
 *
 * Renvoie la liste des crédits réechelonnés avec montant et date du dernier réechelonnement
 *
 * @param array $DATA : Critère de recherche
 * 								=> client			: ID Client
 * 								=> produit		: ID Produit de crédit
 * @return array $lignes : Liste des dossiers de crédit répondant aux paramètres
 * @package Rapports
 *
 */
function getCrdReech($data_crit, $a_gestionnaire=0) {

    global $dbHandler,$global_id_agence;
    $lignes = array ();
    $tabGS = array();
    $nombre = 0;

    $db = $dbHandler->openConnection();

    $id_cli = isset($data_crit["client"])?$data_crit["client"]:'null';

    $sql = "SELECT a.id_doss, a.id_client, a.cre_mnt_octr, a.id_prod, a.gs_cat, sum(mnt_cap) as cap_att, sum(solde_cap) as cap_rest ,a.cre_nbre_reech, a.libel as lib_prod, a.devise, c.libel as lib_etat ";
    $sql .= " FROM get_ad_dcr_ext_credit(null, $id_cli, null, null, $global_id_agence) a, adsys_etat_credits c, ad_etr d ";
    $sql .= " where a.id_doss IN (select id_doss from ad_dcr where cre_nbre_reech > 0) ";
    $sql .= " and  a.id_doss=d.id_doss and a.cre_etat=c.id ";
    if (isset ($data_crit["client"]))
        $sql .= " and a.id_client= '" . $data_crit["client"] . "' ";
    if (isset ($data_crit["produit"]))
        $sql .= " and a.id_prod=" . $data_crit["produit"];
    if ( (isset($data_crit["id_agent_gest"])) && ($data_crit["id_agent_gest"] > 0))
        $sql .= " AND id_agent_gest=".$data_crit["id_agent_gest"];
    $sql .= " group by a.id_doss, a.cre_nbre_reech, a.id_client, a.cre_mnt_octr, a.gs_cat, a.id_prod, a.libel, a.devise, c.libel ";
    $sql .= " order by a.id_client;";
    
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); //  $result->getMessage()
    }

    $DATA = array();
    while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $DATA[$ligne["id_doss"]] = $ligne;
        if ($ligne["gs_cat"] == 2) {
            $DATA[$ligne["id_doss"]]["membre_gs"]	= "OUI";
        } elseif ($ligne["gs_cat"] == 1) {
            $DATA[$ligne["id_doss"]]["groupe_solidaire"] = "OUI";
        }
    }
    //récupération des rééchelonnements
    $sql = " select distinct(c.*) from ";
    $sql .= " (SELECT b.id_his, a.id_doss, b.date, d.montant from ad_dcr a, ad_his b, ad_ecriture c, ad_mouvement d ";
    $sql .= " where a.id_doss=b.infos::integer and b.id_his=c.id_his and c.id_ecriture=d.id_ecriture and b.type_fonction=146 " ;
    if (isset ($data_crit["client"]))
        $sql .= " and a.id_client= '" . $data_crit["client"] . "' ";
    if (isset ($data_crit["produit"]))
        $sql .= " and a.id_prod=" . $data_crit["produit"];
    if ( (isset($data_crit["id_agent_gest"])) && ($data_crit["id_agent_gest"] > 0))
        $sql .= " AND id_agent_gest=".$data_crit["id_agent_gest"];
    $sql .= 		") as c "; 

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); //  $result->getMessage()
    }

    //$data_reech = array();
    while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $DATA[$ligne["id_doss"]]["reech"][$ligne["id_his"]] = $ligne;
    }
    $dbHandler->closeConnection(true);
    return $DATA;
}
/**
 * récupère les infos à ajouter aux rapports crédits pour le détail des groupes solidaires
 * @param $valeur, tableau contenant les informations sur les dossiers
 * @author Evelyne Nshimirimana
 */
function getCreditSolDetailRap($valeur){

     // Récuperation des détails objet demande
     $det_dem = getDetailsObjCredit();

	 if($valeur["gs_cat"] == 1) {
	 	$wherecond = "WHERE id_dcr_grp_sol =". $valeur["id_doss"];
      	$dossiers_unique = getCreditFictif($wherecond);
      	$tab["id_doss"] = $valeur["id_doss"];
      	$tab["membre"] = 0;
      	$tab["gs_cat"] = $valeur["gs_cat"];
      	foreach($dossiers_unique as $key_uni => $value_uni) {
      		$tab["id_client"] 		=  $value_uni["id_membre"];
      		$tab["obj_dem"] 		=  $value_uni["obj_dem"];
      		$tab["mnt_dem"] 		=  $value_uni["mnt_dem"];
      		$tab["id_prod"] 		=  $valeur["id_prod"];
      		$tab["membre"] 			=  1;

            if (isDcrDetailObjCreditLsb()) {
              $tab['detail_obj_dem']    = $det_dem[$value_uni['detail_obj_dem_bis']]['libel'];
            } else {
              $tab["detail_obj_dem"] 	=  $value_uni["detail_obj_dem"];
            }

      		$tab["membre_gs"]			= "OUI";
      		$tabs[$valeur["id_client"]][]= $tab;
      	}
      	$tabs["credit_gs"] = $valeur["id_client"];
	 }
	 if($valeur["gs_cat"] == 2) {
	 	$wherecond = "WHERE id=".$valeur["id_dcr_grp_sol"];
		$dossiermultiple = getCreditFictif($wherecond);
		$idclient = $dossiermultiple[$valeur["id_dcr_grp_sol"]]["id_membre"];
		$tab["gs_multiple"] = "OK";
		$tab["groupe_solidaire"] = "OUI";
		$tab["id_doss"] 	= 0;
		$tab["id_dcr_grp_sol"] = $valeur["id_dcr_grp_sol"];
		$tab["id_client"] 	= $idclient;
		$tab["mnt_dem"]   	= $dossiermultiple[$valeur["id_dcr_grp_sol"]]["mnt_dem"];
		$tab["devise"]      = $valeur["devise"];
		$tab["id_prod"] 	= $valeur["id_prod"];
		$tab["id_agent_gest"] = $valeur["id_agent_gest"];
		$tabs["credit_gs"]  = $tab;
	 }
	 return $tabs;
}
function getHisCrdSolde($DATA) {
  /*
  Fonction qui indique pour chaque client les données sur son dernier prêt soldé (s'il en a eu un) et les infos sur sa demande de prêt en cours
  Reçoit en paramètre un array qui contient :
    - facultatif : la date de dernier solde d'un crédit (on renvoie tous les crédits soldés entre cette date et aujourd'hui)
    - facultatif : le numéro de client pour qui on veut l'historique
    - facultatif : le produit pour lequel on veut l'historique

  Renvoie un array

  */
  global $dbHandler,$global_id_agence;

  $lignes = array ();
  $today = date("d/m/Y");
  $db = $dbHandler->openConnection();

  $id_cli = isset($DATA["client"])?$DATA["client"]:'null';

  $sql = "select a.id_client,a.id_ag,a.id_doss,a.id_prod,a.cre_date_debloc,a.cre_mnt_octr,a.date_etat as date_solde_credit,a.cre_etat as etat_credit,devise,";
  $sql .= " b.pp_nom,b.pp_prenom,b.pm_raison_sociale,b.gi_nom,b.statut_juridique, a.libel,a.gs_cat,a.id_dcr_grp_sol,a.mnt_dem,";
  $sql .= "(select count(id_ech) from ad_etr where id_ag = $global_id_agence and id_doss=a.id_doss group by id_doss) as nbre_echeances_totales,";
  $sql .= "(select count(distinct(g.id_ech)) ";
  $sql .= "from ad_etr f, ad_sre g ";
  $sql .= "where f.id_ag = g.id_ag and g.id_ag = $global_id_agence and f.id_doss=g.id_doss and f.id_ech=g.id_ech ";
  $sql .= "and g.date_remb >= f.date_ech and f.id_doss=a.id_doss ";
  $sql .= "group by f.id_doss) as nbre_echeances_en_retard ";
  $sql .= "FROM get_ad_dcr_ext_credit(null, $id_cli, null, null, $global_id_agence) a, ad_cli b ";
  $sql .= "where a.id_ag = b.id_ag and a.id_ag = $global_id_agence and a.id_client=b.id_client ";
  $sql .= "and a.date_etat::date <= date('$today') ";
  $sql .= "and a.etat='6' ";
  if (isset ($DATA["date"]))
    $sql .= "and a.date_etat::date >= date('" . $DATA["date"] . "')";
  if (isset ($DATA["client"]))
    $sql .= " and a.id_client= '" . $DATA["client"] . "' ";
  if (isset ($DATA["produit"]))
    $sql .= " and a.id_prod=" . $DATA["produit"];
  $sql .= " order by a.date_etat,a.id_client,id_doss;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage()); //  $result->getMessage()
  };
  $tabGS = array();
  while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
  	//$lignes[] = $ligne;
  	//recuperation du crédit solidaire à dossiers multiples
    $groupe = getCreditSolDetailRap($ligne);
    if ((is_array($groupe["credit_gs"]))&&(!in_array($groupe["credit_gs"]["id_client"],$tabGS))){
    	$tabGS[]  = $groupe["credit_gs"]["id_client"];
    	$groupe["credit_gs"]["statut_juridique"] = 4;
    	$groupe["credit_gs"]["gi_nom"] 			 = mb_substr(getClientName($groupe["credit_gs"]["id_client"]), 0, 11, "UTF-8");
    	//TODO: le montant octroyé est la somme des montants octroyés aux membres du groupe, bien qu'il soit souvent egale au montant demandé
    	$groupe["credit_gs"]["cre_mnt_octr"] = $groupe["credit_gs"]["mnt_dem"];
    	$lignes[] = $groupe["credit_gs"];
    }
    if (($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($ligne["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"])) $ligne["membre"] = 1;
    else $ligne["membre"] = 0;
    $lignes[] = $ligne;
    //récuperation des crédits des membres d'un groupe solidaire  à dossier unique
    if(is_array($groupe[$ligne["id_client"]])) {
    	$i = 0;
    	while($i < count($groupe[$ligne["id_client"]])) {
    		//le montant octroyé à chacun des membres dépendra du montant octroyé au groupe par rapport au montant global demandé
    		$groupe[$ligne["id_client"]][$i]["cre_mnt_octr"] = ($groupe[$ligne["id_client"]][$i]["mnt_dem"] * $ligne["cre_mnt_octr"])/$ligne["mnt_dem"];
    		$lignes[] = $groupe[$ligne["id_client"]][$i];
    		$i++;
    	}
    }
  }
  $dbHandler->closeConnection(true);
  if (is_array($lignes))
    return $lignes;
  else
    return NULL;
}
/**
 * Fonction qui renvoie le nombre de clients/comptes respectant les critère
 * de sélection: les paramétres en entrées
 * @param string $a_clien_cpte détermine si liste par client ou par compte
 * @param string $a_debiteur détermine si débiteur par crédit ou par découvert
 * @param string $a_selection détermine si la sélection se fait par nombre ou par montant
 * @param int $a_palier1_nombre nombre de ligne minimum à renvoyer
 * @param int $a_palier2_nombre nombre de ligne maximum à renvoyer
 * @param int $a_palier1_mnt montant minimum du découvert
 * @param int $a_palier2_mnt montant maximum du découvert
 * @param int $a_gestionnaire Identifiant du gestionnaire de crédit
 * @return array Liste et détail des clients
 *
 *
 */
function getListeClientsDebiteursCredit($a_clien_cpte, $a_debiteur, $a_selection, $a_palier1_nombre, $a_palier1_mnt, $a_palier2_mnt, $a_gestionnaire, $devise) {
	global $dbHandler;
	global $global_multidevise, $global_id_agence;
	global $global_monnaie;

	$db = $dbHandler->openConnection(true);
	if ($a_debiteur == 'cre') {
		if ($a_debiteur == 'cpte') {
			$grp_by = " GROUP BY a.id_doss ";
			$id = 'a.id_doss';
		}
		elseif ($a_clien_cpte == 'cli') {
			$grp_by = " GROUP BY a.id_client ";
			$id = 'a.id_client';
		} else {
			$grp_by = " GROUP BY a.id_doss ";
			$id = 'a.id_doss';
		}
		if ($global_multidevise) {
 	     $sql = "SELECT  $id, SUM(solde_cap) AS solde, SUM(solde_pen) AS mnt_pen,devise ";
 	     $sql .= " FROM ad_dcr a, ad_etr b,adsys_produit_credit p WHERE a.id_ag = b.id_ag AND p.id_ag = a.id_ag AND a.id_prod = p.id AND b.id_ag = $global_id_agence AND a.id_doss = b.id_doss AND (a.etat = 5 OR a.etat = 7 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) AND p.devise ='$devise' ";
 	     $grp_by .= " ,devise ";
 	  } else {
 	     $sql = "SELECT  $id, SUM(solde_cap) AS solde, SUM(solde_pen) AS mnt_pen ";
 	     $sql .= " FROM ad_dcr a, ad_etr b WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence AND a.id_doss = b.id_doss AND (a.etat = 5 OR a.etat = 7 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) ";
 	  }		if ($a_gestionnaire != NULL){
			$sql .= " AND a.id_agent_gest=$a_gestionnaire";
		}
		$sql .= " " . $grp_by;

		if ($a_palier1_nombre != '') {
 	     $sql .= " ORDER BY solde DESC ";
 	     $sql .= " limit ".$a_palier1_nombre ;
 	  } elseif ( $a_palier1_mnt != '' && $a_palier2_mnt != '' ) {
 	     $sql .=" having SUM(solde_cap) >=$a_palier1_mnt and SUM(solde_cap) <= $a_palier2_mnt ";
 	     $sql .= " ORDER BY solde DESC ";
 	  }
		$result = $db->query($sql);
		if (DB :: isError($result)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
		}
		// Initialisation des infos globales
		$DATAS['DETAILS'] = array ();
		$GLOB = array ();
		$tabGS = array();
		$SOLIDAIRE = array();
		$GLOB["portefeuille"] = 0;
//		$GLOB["portefeuille_cli"] = 0;
//		$GLOB["portefeuille_retard_cli"] = 0;
		$i = 0;
		$total_credit=0;
		while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
			// infos du dossier
			$DATAS['DETAILS'][$i] = $row;
			if ($a_clien_cpte == 'cpte') {
				$infos_doss = getDossierCrdtInfo($row['id_doss']);
        $DATAS['DETAILS'][$i]['id_doss'] = $infos_doss["id_doss"];
 	      $DATAS['DETAILS'][$i]['id_client'] = $infos_doss["id_client"];
 	      $DATAS['DETAILS'][$i]["nom_utilisateur"] = getClientName($infos_doss["id_client"]);
 	      $DATAS['DETAILS'][$i]['cre_etat'] = $infos_doss["cre_etat"];
 	      //$DATAS['DETAILS'][$i]['gs_cat']= $infos_doss["gs_cat"];

 	    } else {
 	       $DATAS['DETAILS'][$i]["nom_utilisateur"] = getClientName($row["id_client"]);
			}
			$cli=getClientDatas($DATAS['DETAILS'][$i]['id_client']);
 	    if ($cli["statut_juridique"] == 4){
 	       $mbre_grp = getListeMembresGrpSol($DATAS['DETAILS'][$i]['id_client']);
 	       $membres_grp_sol = $mbre_grp->param;
 	       $DATAS["DETAILS"][$i]['groupe']=$membres_grp_sol;

 	    }

 	    $total_credit += $row['solde'];
 	    $GLOB["portefeuille"] += $row['solde'];
 	    $i++;
		}

	} elseif($a_debiteur == 'dec' ) {
		//Découvert compte
		if ($a_clien_cpte == 'cpte') {
			$grp_by = " GROUP BY id_cpte,id_client,solde, statut_juridique ";
			$id = ' id_client, abs(solde) as solde, statut_juridique, id_cpte ';
			$cond_solde = " abs(solde)";
		} elseif ($a_clien_cpte == 'cli') {
			$grp_by = " GROUP BY id_client , statut_juridique, id_cpte";
			$id = ' id_client, SUM(abs(solde)) as solde, statut_juridique, id_cpte ';
 	    $cond_solde ="SUM(abs(solde))";
		}

		$sql = "SELECT $id ";

		if ($global_multidevise) {
			$grp_by .=",devise  ";
			$sql .= ", devise";
			$cond_devise= " AND devise = '$devise'";
		}

		$sql .= " FROM ad_cli, ad_cpt WHERE ad_cli.id_ag = ad_cpt.id_ag AND ad_cpt.id_ag = $global_id_agence AND ad_cli.id_client = ad_cpt.id_titulaire  AND ad_cpt.id_prod <> 3 AND ad_cpt.id_prod <> 4 and solde < 0" ;

		if ($a_gestionnaire > 0) {
			$sql .= " AND ad_cli.gestionnaire = $a_gestionnaire";
		}
		$sql .= " ".$cond_devise;
		$sql .= " " . $grp_by;
		if ($a_palier1_nombre  != '') {
 	     $sql .= " ORDER BY solde DESC ";
 	     $sql .= " LIMIT ".$a_palier1_nombre. " ";
 	  } elseif ($a_palier1_mnt != '' && $a_palier2_mnt != '') {
 	     $sql .= " HAVING $cond_solde >= $a_palier1_mnt AND $cond_solde <= $a_palier2_mnt ";
 	     $sql .= " ORDER BY solde DESC ";
 	  }
 	  $result = $db->query($sql);
		if (DB :: isError($result)) {

			$dbHandler->closeConnection(false);
			signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
		}

		$DATAS["DETAILS"] = array ();
    $total_decouvert=0;
    $i=1;
		while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
			$DATAS["DETAILS"][$i]["id_client"] = $tmprow["id_client"];
 	    $DATAS["DETAILS"][$i]["nom_utilisateur"] = getClientName($DATAS["DETAILS"][$i]["id_client"]);
// 	    if ($a_clien_cpte == 'cpte') {
 	       $ACC = getAccountDatas($tmprow["id_cpte"]);
 	       $DATAS["DETAILS"][$i]["num_cpte"] = $ACC["num_complet_cpte"];
 	       $DATAS["DETAILS"][$i]["libel"] = $ACC["libel"];
// 	    } else {
// 	       $DATAS["DETAILS"][$i]["num_cpte"] = "-";
// 	    }
 	    if ($tmprow['statut_juridique'] == 4 ) {
 	       $mbre_grp = getListeMembresGrpSol($DATAS['DETAILS'][$i]['id_client']);

 	       $membres_grp_sol = $mbre_grp->param;
 	       $DATAS["DETAILS"][$i]['groupe']=$membres_grp_sol;
 	    }

 	    $DATAS["DETAILS"][$i]["solde"]  = $tmprow["solde"];
 	    $total_decouvert +=$tmprow["solde"];
 	    $GLOB["portefeuille"] += $tmprow["solde"];
 	    $i++;
		}
} elseif ($a_debiteur == 'credec' ) {
		//Découvert compte
		if ($a_clien_cpte == 'cpte') {
			$grp_by = " GROUP BY id_cpte,id_client,solde, statut_juridique ";
		  $id = ' id_client, id_cpte, abs(solde) as solde, statut_juridique ';
 	    $cond_solde = " abs(solde)";

 	  } elseif ($a_clien_cpte == 'cli') {
 	    $grp_by = " GROUP BY id_client , statut_juridique, id_cpte";
 	    $id = ' id_client, SUM(abs(solde)) as solde, statut_juridique, id_cpte ';
 	    $cond_solde ="SUM(abs(solde))";
		}
		$sql = "SELECT $id ";

		if ($global_multidevise) {
			$grp_by .=",devise  ";
			$sql .= ", devise";
			$cond_devise= " AND devise = '$devise'";
		}

		$sql .= " FROM ad_cli, ad_cpt WHERE ad_cli.id_ag = ad_cpt.id_ag AND ad_cpt.id_ag = $global_id_agence AND ad_cli.id_client = ad_cpt.id_titulaire   AND ad_cpt.id_prod <> 4 and solde < 0" ;

		if ($a_gestionnaire > 0) {
			$sql .= " AND ad_cli.gestionnaire = $a_gestionnaire";
		}
		$sql .= " ".$cond_devise;
		$sql .= " " . $grp_by;
		if ($a_palier1_nombre  != '') {
 	     $sql .= " ORDER BY solde DESC ";
 	     $sql .= " LIMIT ".$a_palier1_nombre. " ";
 	  } elseif ($a_palier1_mnt != '' && $a_palier2_mnt != '') {
 	     $sql .= " HAVING $cond_solde >= $a_palier1_mnt AND $cond_solde <= $a_palier2_mnt ";
 	     $sql .= " ORDER BY solde DESC ";
 	  }

		$result = $db->query($sql);
		if (DB :: isError($result)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
		}
    $DATAS["DETAILS"] = array ();
 	  $total_decouvert=0;
 	  $i=1;
		while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
			$DATAS["DETAILS"][$i]["id_client"] = $tmprow["id_client"];
 	    $DATAS["DETAILS"][$i]["nom_utilisateur"] = getClientName($DATAS["DETAILS"][$i]["id_client"]);
// 	    if ($a_clien_cpte == 'cpte') {
 	       $ACC = getAccountDatas($tmprow["id_cpte"]);
 	       $DATAS["DETAILS"][$i]["num_cpte"] = $ACC["num_complet_cpte"];
 	       $DATAS["DETAILS"][$i]["libel"] = $ACC["libel"];
// 	    } else {
// 	       $DATAS["DETAILS"][$i]["num_cpte"] = "-";
// 	    }
 	    if ($tmprow['statut_juridique'] == 4 ) {
 	       $mbre_grp = getListeMembresGrpSol($DATAS['DETAILS'][$i]['id_client']);

 	       $membres_grp_sol = $mbre_grp->param;
 	       $DATAS["DETAILS"][$i]['groupe']=$membres_grp_sol;
 	    }

 	    $DATAS["DETAILS"][$i]["solde"]  = $tmprow["solde"];
 	    $total_decouvert +=$tmprow["solde"];
 	    $GLOB["portefeuille"] += $tmprow["solde"];
 	    $i++;
		}

	}
  $GLOB["total_credit"]=$total_credit;
  $GLOB['total_decouvert'] = $total_decouvert;
  $DATAS["TOTAL" ] = $GLOB;

 return $DATAS;
}

function getListePlusGrandsEmp($a_limit, $a_gestionnaire, $date) {
	global $dbHandler;
	global $global_multidevise, $global_id_agence;
	global $global_monnaie;
	$a_limit = 10;
	$db = $dbHandler->openConnection(true);
	$sql = " SELECT  a.id_client, a.id_doss, a.cre_date_approb, a.cre_mnt_octr, COUNT(b.id_ech) AS nbr_ech, SUM(b.solde_cap) AS solde_cap ";
	$sql .= " FROM ad_dcr a, ad_etr b WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence AND a.id_doss = b.id_doss AND (a.etat = 5 OR a.etat = 7 OR a.etat = 8 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) ";
	if ($a_gestionnaire != NULL){
			$sql .= " AND a.id_agent_gest=$a_gestionnaire ";
	}
	if ($date != NULL){
			$sql .= " AND a.cre_date_approb < '".$date."' ";
	}
	$sql .= " GROUP BY a.id_doss, a.id_client, a.cre_date_approb, a.cre_mnt_octr ";
	$sql .= " ORDER BY a.cre_mnt_octr DESC";
	$sql .= " limit $a_limit ";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
		$DATA = array ();
		$tot_mnt_pret = 0;
		$tot_solde = 0;
		$tot_mnt_retard = 0;
		$tot_mnt_prov = 0;
		$i = 0;

			while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
			// infos du dossier
				$infos_doss = getDossierCrdtInfo($row['id_doss']);
			// calculer le montant en retard

			$mnt_retard = getMontantRetardDossier($row['id_doss'] , $date);

			// récupérer le type de garantie
			$sql_gar = " SELECT a.id_doss, b.gar_mat_id_bien, c.id, c.libel from ad_dcr a, ad_gar b, adsys_types_biens c  ";
			$sql_gar .= " WHERE a.id_doss=b.id_doss AND b.id_doss=".$row['id_doss']." AND b.gar_mat_id_bien=c.id AND b.type_gar = 2 ";
			$result_gar = $db->query($sql_gar);
			if (DB :: isError($result_gar)) {
				$dbHandler->closeConnection(false);
				signalErreur(__FILE__, __LINE__, __FUNCTION__, $result_gar->getMessage());
			}
			$row_gar = $result_gar->fetchrow(DB_FETCHMODE_ASSOC);
			$garantie = $row_gar["libel"];

			// calculer la contre valeur du montant octroyé et du solde si on est en multidevises
			if ($global_multidevise){
				$mnt_pret = calculeCV($infos_doss["devise"], $global_monnaie, $row["cre_mnt_octr"]);
				$solde = calculeCV($infos_doss["devise"], $global_monnaie, $row["solde_cap"]);
				$mnt_retard = calculeCV($infos_doss["devise"], $global_monnaie, $mnt_retard);
				$mnt_prov = 0; // A determiner si le calcul des provisions est effectif dans adbanking
			}
			else{
				$mnt_pret = $row["cre_mnt_octr"];
				$solde = $row["solde_cap"];
				$mnt_retard = $mnt_retard;
				$mnt_prov = 0; // A determiner si le calcul des provisions est effectif dans adbanking
			}
				$DATA[$i]["nom"] = getClientName($row["id_client"]);
				$DATA[$i]["date_pret"] = $row["cre_date_approb"];
				$DATA[$i]["mnt_pret"] = $mnt_pret;
				$DATA[$i]["echeance"] = $row["nbr_ech"];
				$DATA[$i]["solde"] = $solde;
				$DATA[$i]["mnt_retard"] = $mnt_retard;
				$DATA[$i]["garantie"] = $garantie;
				$DATA[$i]["mnt_prov"] = $mnt_prov;


				$tot_mnt_pret += $mnt_pret;
				$tot_solde += $solde;
				$tot_mnt_retard += $mnt_retard;
				$tot_mnt_prov += $mnt_prov;
				$i++;

		}
		$DATA["tot_mnt_pret"] = $tot_mnt_pret;
		$DATA["tot_solde"] = $tot_solde;
		$DATA["tot_mnt_retard"] = $tot_mnt_retard;
		$DATA["tot_mnt_prov"] = $tot_mnt_prov;
		$dbHandler->closeConnection(true);
		return $DATA;
}

function getRisqueCreditSecteur($a_gestionnaire, $date) {
	global $dbHandler;
	global $global_multidevise, $global_id_agence;
	global $global_monnaie;

		$db = $dbHandler->openConnection(true);
		$sql = " SELECT  a.id_client, a.id_doss, a.obj_dem, a.cre_date_approb, a.cre_mnt_octr, b.gi_nbre_membr, b.statut_juridique, c.devise ";
		$sql .= " FROM ad_dcr a, ad_cli b, adsys_produit_credit c  ";
		$sql .= " WHERE a.id_ag = b.id_ag AND b.id_ag=c.id_ag AND c.id_ag = $global_id_agence AND a.id_client = b.id_client AND a.id_prod=c.id AND (a.etat = 5 OR a.etat = 7 OR a.etat = 8 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) ";

		if ($date != NULL){
				$sql .= " AND a.cre_date_approb < '".$date."' ";
		}

		$result = $db->query($sql);
		if (DB :: isError($result)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
		}
		// récupération des objets de demande de crédit, création du tableau $DATA des données de retour
		$objet_credit = getListeObjetCredit();
		$DATA = array();
		foreach($objet_credit as $key=>$value){
			$DATA[$key]["libel_act"] = "$value";
			$DATA[$key]["mnt_cred"] = 0;
			$DATA[$key]["ind_deb"] = 0;
			$DATA[$key]["grp_deb"] = 0;
			$DATA[$key]["grp_benef_pret"] = 0;
		}
	  $DATA["tot_mnt_cred"] = 0;
	  $DATA["tot_ind_deb"] = 0;
	  $DATA["tot_grp_deb"] = 0;
	  $DATA["tot_grp_benef_pret"] = 0;

		$counted_client_act = array();// tableau mémorisant l'id des client déjà pris en compte pour le decompte du nombre d'individus ou groupe

	// Parcours des résultats de la requête et stockage dans le tableau $DATA des données de retour
			while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
			$key = $row["obj_dem"];
			// calculer la contre valeur du montant octroyé et du solde si on est en multidevises
			if ($global_multidevise){
				$mnt_pret = calculeCV($row["devise"], $global_monnaie, $row["cre_mnt_octr"]);
			}
			else{
				$mnt_pret = $row["cre_mnt_octr"];
			}
			$DATA[$key]["mnt_cred"] += $mnt_pret;
			$DATA["tot_mnt_cred"] += $mnt_pret;

			$id_client = $row["id_client"];
			if($counted_client_act[$key][$id_client]!= 't'){
			if($row["statut_juridique"] == 1){// personnes physiques
			 	$DATA[$key]["ind_deb"] += 1;
		 		$DATA["tot_ind_deb"] += 1;
			}
			 else { // groupe solidaire, groupe informel ou personne morale
			 	$DATA[$key]["grp_deb"] += 1;
			 	$DATA["tot_grp_deb"] += 1;
			 	if($row["gi_nbre_membr"] == NULL || $row["gi_nbre_membr"] == 0){// Personne morale
			 	 $DATA[$key]["grp_benef_pret"] += 1;
			   $DATA["tot_grp_benef_pret"] += 1;
			 	}
			  else{
			   $DATA[$key]["grp_benef_pret"] += $row["gi_nbre_membr"];
			   $DATA["tot_grp_benef_pret"] += $row["gi_nbre_membr"];
			  }

			 }
			 $counted_client_act[$key][$id_client] = 't';
			}
		}

		$dbHandler->closeConnection(true);
		return $DATA;
}

function getRecouvrementCredit($a_gestionnaire, $date) {

	global $dbHandler;
	global $global_multidevise, $global_id_agence;
	global $global_monnaie;

		$date_post = explode("/",$date);
		$annee = $date_post[2];
		//1 er trimestre
		$tabTrim[1]["date_deb"]="01/01/".$annee;
		$tabTrim[1]["date_fin"]="31/03/".$annee;
	  //2 eme trimestre
		$tabTrim[2]["date_deb"]="01/04/".$annee;
		$tabTrim[2]["date_fin"]="30/06/".$annee;
	  //3 eme trimestre
		$tabTrim[3]["date_deb"]="01/07/".$annee;
		$tabTrim[3]["date_fin"]="30/09/".$annee;
	  //4 eme trimestre
		$tabTrim[4]["date_deb"]="01/10/".$annee;
		$tabTrim[4]["date_fin"]="31/12/".$annee;
		$db = $dbHandler->openConnection(true);

			$DOSSIERS = array();
			$numEtatPerte = getIDEtatPerte();
			//Récupération des soldes des crédits octroyés avant le 31 decembre de l'année écoulée(pour le calcul des risques)
			$sql = " SELECT sum(solde_cap) as solde, cre_etat, a.id_doss, devise ";
			$sql .= " FROM ad_dcr a, ad_etr b, adsys_produit_credit c ";
			$sql .= " WHERE a.id_prod=c.id AND a.id_doss=b.id_doss AND (a.etat=5 OR a.etat=6 OR a.etat=7 OR a.etat=8 OR a.etat=13 OR a.etat=14 OR a.etat=15) AND cre_etat > 2 AND cre_etat != $numEtatPerte AND a.cre_date_approb < '".$tabTrim[1]["date_deb"]."' ";
			$sql .= " GROUP BY a.id_doss, cre_etat, devise order by id_doss ";
			$result = $db->query($sql);
			if (DB :: isError($result)) {
				$dbHandler->closeConnection(false);
				signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
			}
			while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
				$key = $row["id_doss"];
				$DOSSIERS[$key]["cre_etat"] = $row["cre_etat"];
				$DOSSIERS[$key]["devise"] = $row["devise"];
				$DOSSIERS[$key]["mnt_risque"] = $row["solde"];
			}

		//Récupération des montants de recouvrement des crédits par trimestre et calcul des risques au 31 decembre
		for ($i=1;$i<=4;$i++){
			$sql = " SELECT sum(mnt_remb_cap) as mnt_rec, cre_etat, a.id_doss, a.cre_date_approb, devise ";
			$sql .= " FROM ad_dcr a, ad_sre b, adsys_produit_credit c ";
			$sql .= " WHERE a.id_prod=c.id AND a.id_doss=b.id_doss AND (a.etat=5 OR a.etat=6 OR a.etat=7 OR a.etat=8 OR a.etat=13 OR a.etat=14 OR a.etat=15) AND cre_etat > 2 AND cre_etat != $numEtatPerte AND date_remb > '".$tabTrim[$i]["date_deb"]."' AND date_remb < '".$tabTrim[$i]["date_fin"]."' ";
			$sql .= " GROUP BY a.id_doss, cre_etat, a.cre_date_approb, devise order by id_doss ";
			$result = $db->query($sql);
			if (DB :: isError($result)) {
				$dbHandler->closeConnection(false);
				signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
			}
			$trim = "trim_".$i;
			while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
				$key = $row["id_doss"];
				if($DOSSIERS[$key] == NULL){
					$DOSSIERS[$key] = array();
					$DOSSIERS[$key]["cre_etat"] = $row["cre_etat"];
					$DOSSIERS[$key]["devise"] = $row["devise"];
				}
				$DOSSIERS[$key][$trim] = $row["mnt_rec"];
				if(isBefore(pg2phpDate($row["cre_date_approb"]), $tabTrim[1]["date_deb"]))// si la date d'aprobation est antérieure au 31 dec de l'année précédante, on ajoute le montant de recouvrement au risque
				$DOSSIERS[$key]["mnt_risque"] += $row["mnt_rec"];

			}
		}

		//Récupération des états de crédit (état en perte, premier état en retard et état sain exclus)
		$etats_credits = getTousEtatCredit(true);
		$etat_retard = $etats_credits[2];
		unset($etats_credits[2]); // on enlève le premier état en retard
		$nbr_jour_min = $etat_retard["nbre_jours"];
		//Initialisation du tableau des informations à retourner
		$DATA = array();
		foreach($etats_credits as $key=>$value){
			$nbr_jour_max = $nbr_jour_min+$value["nbre_jours"]-1;
			if($nbr_jour_min < 365)
			 $libel = $value["libel"]."(".$nbr_jour_min." to ".$nbr_jour_max." days)";
			else
			 $libel = $value["libel"]."(".$nbr_jour_min." days and more)";
			$DATA[$key]["libel_etat"] = $libel;
			$DATA[$key]["mnt_risque"] = 0;
			$DATA[$key]["trim_1"] = 0;
			$DATA[$key]["trim_2"] = 0;
			$DATA[$key]["trim_3"] = 0;
			$DATA[$key]["trim_4"] = 0;
			$DATA[$key]["tot_trim"] = 0;
			$nbr_jour_min = $nbr_jour_max + 1;
		}

		//Remplissage du tableau des informations à retourner
		foreach($DOSSIERS as $key=>$value){
			$cre_etat = $value["cre_etat"];
			$devise = $value["devise"];
			// calculer la contre valeur des montants de recouvrement si on est en multidevises
			if ($global_multidevise){
				$mnt_risque = calculeCV($devise, $global_monnaie, $value["mnt_risque"]);
				$mnt_rec_trim_1 = calculeCV($devise, $global_monnaie, $value["trim_1"]);
				$mnt_rec_trim_2 = calculeCV($devise, $global_monnaie, $value["trim_2"]);
				$mnt_rec_trim_3 = calculeCV($devise, $global_monnaie, $value["trim_3"]);
				$mnt_rec_trim_4 = calculeCV($devise, $global_monnaie, $value["trim_4"]);
			}
			else{
				$mnt_risque = $value["mnt_risque"];
				$mnt_rec_trim_1 = $value["trim_1"];
				$mnt_rec_trim_2 = $value["trim_2"];
				$mnt_rec_trim_3 = $value["trim_3"];
				$mnt_rec_trim_4 = $value["trim_4"];
			}
			$DATA[$cre_etat]["mnt_risque"] += $mnt_risque;
			$DATA[$cre_etat]["trim_1"] += $mnt_rec_trim_1;
			$DATA[$cre_etat]["trim_2"] += $mnt_rec_trim_2;
			$DATA[$cre_etat]["trim_3"] += $mnt_rec_trim_3;
			$DATA[$cre_etat]["trim_4"] += $mnt_rec_trim_4;
			$DATA[$cre_etat]["tot_trim"] += $mnt_rec_trim_1 + $mnt_rec_trim_2 + $mnt_rec_trim_3 + $mnt_rec_trim_4;
		}

		$dbHandler->closeConnection(true);
		return $DATA;
}


function get_repartition_credit($type_duree, $devise = NULL, $gestionnaire = 0, $Date,$n_agence=null, $n_guichet=null) {
  // Fonction utilisée pour le rapport de Concentration du portefeuille de crédit
  // On va récupérer diverses infos sur chaque dossier de crédit
  // IN : Néant
  // OUT: array ( 'id_doss' => array ( 'sect_act'         => Secteur d'activité,
  //                                   'id_loc1'          => Localisation 1
  //                                   'id_loc2'          => Localisation 2
  //                                   'statut_juridique' => Stat Jur du demandeur
  //                                   'pp_sexe'          => Sexe du demandeur (si PP)
  //                                   'solde_cap'        => Solde en capital
  //                                   'solde_int'        => Solde en intérêt )
  //                                   'id_prod'          => ID du produit de crédit) )
  global $dbHandler;
  global $global_multidevise,$global_id_agence, $error;
  $db = $dbHandler->openConnection();
  
  $idEtatPerte = getIDEtatPerte();
  //traite le cas de date export null: rapport plante si null pas de js
  if($Date == NULL){
  	$Date = date("Y")."-".date("m")."-".date("d");
  }
  
  
#Récupère tous les crédits en cours
//Ex-requete rapport Concentration p d credit
/*
  $sql = "SELECT d.id_doss, d.id_client,d.id_ag, d.cre_mnt_octr, d.cre_etat, d.duree_mois, d.id_prod";
  $sql .= " FROM ad_dcr d, adsys_produit_credit c ";
  if ($gestionnaire > 0)
    $sql .= " WHERE d.id_ag = c.id_ag and c.id_ag = $global_id_agence and ((d.etat=5) OR (d.etat=7) OR (d.etat=14) OR (d.etat=15)) and d.id_prod = c.id and d.id_agent_gest=$gestionnaire ";
  else
    $sql .= " WHERE d.id_ag = c.id_ag and c.id_ag = $global_id_agence and ((d.etat=5) OR (d.etat=7) OR (d.etat=14) OR (d.etat=15)) and d.id_prod = c.id ";
 
  if (($devise != NULL) && ($devise != '0') && ($global_multidevise))
    $sql .= " and c.devise ='" . $devise . "'";
    
  if (isset ($type_duree))
    $sql .= "and c.type_duree_credit = $type_duree";
  
  //filtre Date* pour le rapport
  if (isset ($Date))
  	$sql .= "and d.cre_date_etat  <= '$Date' ";
  
  
 
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }*/
  //Nouveau requete concentration:Migration sur getportfeuilleview

   $sql = "SELECT id_doss, id_client, id_ag, cre_mnt_octr, id_etat_credit as cre_etat, duree_mois, id_prod,devise, id_agent_gest, type_duree_credit, cre_nbre_reech, date_dem, mnt_cred_paye, cre_mnt_deb, is_ligne_credit
  FROM getPortfeuilleView('$Date',$global_id_agence) WHERE id_etat_credit != $idEtatPerte AND id_ag=$global_id_agence  "; 
   
   if ($gestionnaire > 0)
   	$sql .= " AND id_agent_gest=$gestionnaire ";

   if (($devise != NULL) && ($devise != '0') && ($global_multidevise))
   	$sql .= " AND devise ='" . $devise . "'";
   if (isset ($type_duree))
   	$sql .= " AND type_duree_credit = $type_duree";

   if ($n_agence !=null){
     $sql .= " AND n_agence = $n_agence";
   }

    if ($n_guichet !=null){
        $sql .= " AND n_guichet = $n_guichet";
    }
   
   $sql .= " ORDER BY id_doss ";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
  $credits = array ();
  $date_str = date("d/m/Y"); //String pour la comparaison de date d'aujourd'hui
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) { // Pour chaque crédit
    $credit[$row['id_ag']][$row['id_doss']] = $row;
  }

#Récupère des infos annexes pour chaque crédit
  if ($credit != array ()) {
    reset($credit);
    while (list ($id_ag, $values_agence) = each($credit)) {
      while (list ($id, $values) = each($values_agence)) {
        //Infos concernant le client
        // AT-33/AT-78
        $Data_agence = getAgenceDatas($global_id_agence);
        if ($Data_agence['identification_client'] == 1){
          $sql = "SELECT sect_act, id_loc1, id_loc2, statut_juridique, pp_sexe FROM ad_cli WHERE id_client = " . $values['id_client'];
        }
        else{// AT-33/AT-78
          $sql = "SELECT sect_act, id_loc1, id_loc2, province, district, secteur, cellule, village, statut_juridique, pp_sexe FROM ad_cli WHERE id_client = " . $values['id_client'];
        }
        $sql .= " AND id_ag = $global_id_agence";
        $result = $db->query($sql);
        if (DB :: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
        } else
          if ($result->numrows() != 1) {
            $dbHandler->closeConnection(false);
            if ($result->numrows() == 0)
            	return new ErrorObj(ERR_NO_RECORDS, $error[ERR_NO_RECORDS]);
            else
            	return new ErrorObj(ERR_TROP_RECORDS, $error[ERR_TROP_RECORDS]);
          }
        $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

        $credit[$id_ag][$id]['sect_act'] = $row['sect_act'];
        $credit[$id_ag][$id]['id_loc1'] = $row['id_loc1'];
        $credit[$id_ag][$id]['id_loc2'] = $row['id_loc2'];
        // AT-33/AT-78
        $Data_agence = getAgenceDatas($global_id_agence);
        if ($Data_agence['identification_client'] == 2){// AT-33/AT-78 - Localisation Rwanda
          $credit[$id_ag][$id]['province'] = $row['province'];
          $credit[$id_ag][$id]['district'] = $row['district'];
          $credit[$id_ag][$id]['secteur'] = $row['secteur'];
          $credit[$id_ag][$id]['cellule'] = $row['cellule'];
          $credit[$id_ag][$id]['village'] = $row['village'];
        }
        $credit[$id_ag][$id]['statut_juridique'] = $row['statut_juridique'];
        if ($row['statut_juridique'] == 1)
          $credit[$id_ag][$id]['pp_sexe'] = $row['pp_sexe'];
        //Infos concernant le capital et les intérêts en retard
        $sql = "SELECT SUM(solde_cap), SUM(solde_int) FROM ad_etr WHERE (id_doss = $id) AND (remb = 'f') AND id_ag = $global_id_agence";

        $result = $db->query($sql);
        if (DB :: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
        }

        $row1 = $result->fetchrow();

        // ligne de credit
        if($credit[$id_ag][$id]['is_ligne_credit'] == 't') {
          $credit[$id_ag][$id]['solde_cap'] = getCapitalRestantDuLcr($credit[$id_ag][$id]['id_doss'], $Date);
          $credit[$id_ag][$id]['solde_int'] = getCalculInteretsLcr($credit[$id_ag][$id]['id_doss'], $Date);
        }
        else {
          $credit[$id_ag][$id]['solde_cap'] = $row1[0];
          $credit[$id_ag][$id]['solde_int'] = $row1[1];
        }

      }
    }
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $credit);
}

/**
 * Extrait les données qui seront placée dans le rapport concentration de l'épargne
 * @return errorObj avec en paramètre un tableau associatif contenant les données pour le rapport :
 *  * 'id_compte' => array (
 *    * 'sect_act'         => Secteur d'activité,
 *    * 'id_loc1'          => Localisation 1
 *    * 'id_loc2'          => Localisation 2
 *    * 'statut_juridique' => Stat Jur du demandeur
 *    * 'pp_sexe'          => Sexe du demandeur (si PP)
 *    * 'solde_cap'        => Solde en capital
 *    * 'solde_int'        => Solde en intérêt
 *    * 'id_prod'          => ID du produit d'épargne
 *  )
 */
function get_repartition_epargne($date_rapport,$date_debut = null,$date_fin = null,$n_agence= null, $n_guichet= null) {
	global $dbHandler, $global_monnaie, $global_id_agence;
	$db = $dbHandler->openConnection();

	$epargnes = array ();
	$epargnes['totaux']['nbre'] = 0;
	$epargnes['totaux']['mnt'] = 0;
	$epargnes['totaux']['nbreclient'] = 0;
	$epargnes['totaux']['mntclient'] = 0;

	// Récupère tous les comptes d'épargne
	//$sql = "SELECT cli.sect_act, cli.id_loc1, cli.id_loc2, cli.statut_juridique, cli.qualite, cli.pp_sexe, d.id_cpte, d.id_titulaire, calculeCV(d.solde, d.devise, '$global_monnaie') AS solde, d.id_prod, c.classe_comptable";
	//$sql .= " FROM ad_cpt d, adsys_produit_epargne c, ad_cli cli ";
	//$sql .= " WHERE (c.classe_comptable=1 OR c.classe_comptable=2 OR c.classe_comptable=5 OR c.classe_comptable=6) AND d.id_titulaire = cli.id_client AND d.id_prod = c.id AND d.etat_cpte<>2";
	//$sql .= " AND d.id_ag = c.id_ag AND d.id_ag = $global_id_agence";
	// Il est important que la recherche soit triée par id_titulaire
	// De cette manière le tableau $epargnes[] le sera également
	// et grâce à cela nous pouvons optimiser la fonction get_tranche de xml_epargne.php, voir #1201.
	//$sql .= " ORDER BY d.id_titulaire, d.id_cpte";

  //ticket 659
  $v_date_debut = "date('{$date_debut}')";
  $v_date_fin = "date('{$date_fin}')";
  if(is_null($date_debut) && is_null($date_fin)){
    $v_date_debut = "NULL";
    $v_date_fin = "NULL";
  }
	$sql ="select  cli.sect_act, cli.id_loc1, cli.id_loc2, cli.statut_juridique, cli.qualite, cli.pp_sexe, a.id_cpte, a.id_client,  ";
	$sql .="calculeCV(a.solde, a.devise, '$global_monnaie') AS solde, a.id_prod, a.classe_comptable";
	$sql .=" from epargne_view (date('{$date_rapport}'),$global_id_agence,NULL,NULL,NULL,$v_date_debut,$v_date_fin)  as a"  ;
	$sql .= " left join ad_cli cli on ( a.id_ag=cli.id_ag and a.id_client = cli.id_client) ";
	if (isFusion()){
	  if ($n_agence!= null){
	    $sql .= " AND cli.n_agence = $n_agence";
      }
      if ($n_guichet!=null){
	    $sql .= " AND cli.n_guichet = $n_guichet";
      }
    }
	$sql .= " ORDER BY a.id_client, a.id_cpte";

	 
 
	$result = executeQuery($db, $sql);
	if ($result->errCode != NO_ERR) {
		$dbHandler->closeConnection(false);
		return $result;
	}

	$clientliste = array();
	foreach ($result->param as $infosCpte) {
		$idCpte = $infosCpte['id_cpte'];
	 if (!in_array($infosCpte['id_client'], $clientliste)) {
        // Le tableau $data doit être trié par id_client !!!
        // Ce postulat permet de réduire le tps d'exécution de cette fonction get_tranche, voir #1201.
        array_push($clientliste, $infosCpte['id_client']);
        $epargnes['totaux']['nbreclient']++;
      }
		
		$epargnes[$idCpte] = $infosCpte;
		$epargnes['totaux']['nbre']++;
		$epargnes['totaux']['mnt'] += $infosCpte['solde'];
		$epargnes['totaux']['mntclient'] += $infosCpte['solde'];
		$epargnes[$idCpte]['sect_act'] = $infosCpte['sect_act'];
		$epargnes[$idCpte]['id_loc1'] = $infosCpte['id_loc1'];
		$epargnes[$idCpte]['id_loc2'] = $infosCpte['id_loc2'];
		$epargnes[$idCpte]['statut_juridique'] = $infosCpte['statut_juridique'];
		$epargnes[$idCpte]['qualite'] = $infosCpte['qualite'];
		if ($infosCpte['statut_juridique'] == 1) {
			$epargnes[$idCpte]['pp_sexe'] = $infosCpte['pp_sexe'];
		}
	}
	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR, $epargnes);
}

/**
 	* Renvoie le libellé d'un produit d'épargne
 	* @author Djibril NIANG
 	* @since 3.0.6
 	* @param int $id_prod identifiant du produit d'épargne
 	* @return TEXT $libel : libellé du produit d'épargne
**/
function getLibelProdEp($id_prod){
	  global $dbHandler, $global_monnaie, $global_id_agence;
	  $db = $dbHandler->openConnection();
	  // Récupère tous les comptes d'épargne
    $sql = "SELECT libel";
	  $sql .= " FROM adsys_produit_epargne ";
	  $sql .= " WHERE id = $id_prod ";
	  $result = $db->query($sql);
	  if (DB :: isError($result)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	  }
	  $row = $result->fetchrow();
	  $libel = $row[0];
	  $dbHandler->closeConnection(true);
	  return $libel;
}

/**
 * Renvoie le libellé d'un produit d'épargne
 * @author Djibril NIANG
 * @since 3.0.6
 * @param int $id_prod identifiant du produit d'épargne
 * @return TEXT $libel : libellé du produit d'épargne
 **/
function getLibelProdcrdt($id_prod){
  global $dbHandler, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();
  // Récupère tous les comptes d'épargne
  $sql = "SELECT libel";
  $sql .= " FROM adsys_produit_credit ";
  $sql .= " WHERE id = $id_prod ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $row = $result->fetchrow();
  $libel = $row[0];
  $dbHandler->closeConnection(true);
  return $libel;
}

function getLibelProdCr($id_prod){
  global $dbHandler, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();
  // Récupère tous les comptes d'épargne
  $sql = "SELECT libel";
  $sql .= " FROM adsys_produit_credit ";
  $sql .= " WHERE id = $id_prod ";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $row = $result->fetchrow();
  $libel = $row[0];
  $dbHandler->closeConnection(true);
  return $libel;
}

/**
  * Renvoie le nombre de compte d'épargne pour d'un produit d'épargne
  * @author Djibril NIANG
  * @since 3.0.6
  * @param int $id_prod identifiant du produit d'épargne
  * @return TEXT $libel : libellé du produit d'épargne
**/
function getNbreComptesEpargne($id_prod, $type_epargne, $date_rapport,$n_agence=null, $n_guichet=null){
	global $dbHandler, $global_monnaie, $global_id_agence;
	$db = $dbHandler->openConnection();
	// Récupère tous les comptes d'épargne
	
	$sql = "	SELECT  count(id_cpte) as nbre_cli  ";
	$sql .= " 		from ad_cpt a inner join ad_cli c on c.id_client = a.id_titulaire left join  adsys_produit_epargne b on ( a.id_ag=b.id_ag and a.id_prod =b.id)";
	$sql .= " 		where (b.classe_comptable=1 OR b.classe_comptable=2 OR b.classe_comptable=5 OR b.classe_comptable=6)  and   ";
	$sql .= " 		date(date_ouvert)<= date('$date_rapport') and  a.id_ag =$global_id_agence and ";
	$sql .= " 		( etat_cpte <> 2 OR (etat_cpte = 2 and date(date_clot) >date('$date_rapport')))  ";
	if($id_prod != NULL){
		$sql .= " AND a.id_prod = $id_prod ";
	}
    if($type_epargne != NULL){
      $sql .= " AND b.classe_comptable = $type_epargne ";
    }
    if($n_agence != NULL){
        $sql .= " AND c.n_agence = $n_agence ";
    }
    if($n_guichet != NULL){
        $sql .= " AND c.n_guichet = $n_guichet ";
    }

	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	$row = $result->fetchrow();
	$nombre_cptes = $row[0];
	$dbHandler->closeConnection(true);
	return $nombre_cptes;
}
/**
 * Renvoie le nombre de clients demandeurs de crédits pour une période donnée
 * @author Djibril NIANG
 * @since 3.0.6
 * @param DATE $date_debut debut de la période
 * @param DATE $date_fin fin de la période
 * @param INT num_req : numéro de la requete à exécuter : 1 si crédits demandés, 2 pour credits octroyés, 3 pour ....
 * @return INT $nombre_cli : le nombre de clients
**/
function getNbreClients($date_debut, $date_fin, $num_req = 0){
  global $dbHandler, $global_monnaie, $global_id_agence, $adsys;
  $db = $dbHandler->openConnection();

  if($num_req == 1){
 	  $sql = " SELECT count(a.id_client) ";
 	  $sql .= " FROM ad_dcr a, ad_cli b, adsys_produit_credit c, adsys_objets_credits e ";
 	  $sql .= " WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = e.id_ag and e.id_ag = $global_id_agence ";
 	  $sql .= " AND a.id_client = b.id_client AND a.id_prod = c.id AND a.obj_dem = e.id AND date_dem >= date('$date_debut') AND date_dem <= date('$date_fin') ";
 	} else if($num_req == 2){
 	  $sql = " SELECT count(a.id_client) ";
 	  $sql .= " FROM ad_dcr a, ad_cli b, adsys_produit_credit c";
 	  $sql .= " WHERE a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence and a.id_client=b.id_client ";
 	  $sql .= " and a.id_prod = c.id AND cre_date_approb >= date('$date_debut') AND cre_date_approb <= date('$date_fin') ";
 	}
 	$result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  $row = $result->fetchrow();
  $nombre_cli = $row[0];
  $dbHandler->closeConnection(true);
  return $nombre_cli;
}

function get_liste_epargne($critere, $limit =NULL ,$offset=NULL, $date_rapport, $type_epargne=NULL, $zone = NULL,$n_agence = null, $n_guichet= null) {
	global $dbHandler, $global_monnaie, $global_id_agence, $adsys;
	$db = $dbHandler->openConnection();

	$epargnes = array ();
	$epargnes['totaux']['nbre'] = 0;
	$epargnes['totaux']['mnt'] = 0;
	$epargnes['totaux']['nbreclient'] = 0;
	$epargnes['totaux']['mntclient'] = 0;

	$id_prod_choisi  = $critere['id_prod'];
	$epargnes[$id_prod_choisi]["libel_prod_ep"] = $critere['libel'];;
	$epargnes[$id_prod_choisi]["nbr_titulaire"] = 0;

	//if(date('dd-MM-YYYY'))
	// Récupère tous les comptes d'épargne
	//$sql = "SELECT d.id_titulaire, d.id_cpte, d.num_complet_cpte, count(d.id_titulaire) as nbre_cli, d.solde, d.id_prod, c.libel, c.classe_comptable, d.devise,";
	//	$sql .= " cli.statut_juridique, cli.pp_nom, cli.pp_prenom, cli.pm_raison_sociale, cli.gi_nom ";
	//	$sql .= " FROM ad_cpt d, adsys_produit_epargne c, ad_cli cli ";
	// $sql .= " WHERE (c.classe_comptable=1 OR c.classe_comptable=2 OR c.classe_comptable=5 OR c.classe_comptable=6) AND d.id_prod = c.id AND d.etat_cpte<>2";
	// $sql .= " AND cli.id_client = d.id_titulaire ";
	//	$sql .= " AND d.id_ag = c.id_ag AND c.id_ag = cli.id_ag AND cli.id_ag = $global_id_agence ";
	//  if($id_prod_choisi != NULL){// un produit d'epargne n'est choisi,
	//	 $sql .= " AND d.id_prod = $id_prod_choisi ";
	//  }
	// $sql .= " AND d.id_titulaire > $indice ";
	if(is_null($limit)) $limit='NULL';
	if(is_null($offset)) $offset='NULL';
	if(is_null($id_prod_choisi)) $id_prod_choisi='NULL';
    $sql = "select  e.*, ac.client_zone AS client_zone_id from epargne_view (date('{$date_rapport}'),$global_id_agence,$id_prod_choisi,$limit,$offset,null,null) e INNER JOIN ad_cli ac ON ac.id_client = e.id_client AND ac.id_ag = e.id_ag WHERE classe_comptable NOT IN  (3, 8)  "  ;

    if($type_epargne != NULL) {
      $sql .= " AND classe_comptable = $type_epargne ";
    }

    $zone_dataset = getZone();

    if(!empty($zone) && ($zone != -1)) {
            $sql .= " AND client_zone IN ($zone)";
    }

    if(!empty($n_agence)) {
        $sql .= " AND n_agence = $n_agence";
    }

    if(!empty($n_guichet)) {
        $sql .= " AND n_guichet = $n_agence";
    }
    $sql .= " group by classe_comptable, id_prod, nom_cli, e.id_client, id_cpte, devise, date_ouverture, etat_cpte, solde, e.id_ag, num_complet_cpte, libel, ac.client_zone ORDER By ac.client_zone";
    // Il est important que la recherche soit triée par id_titulaire
	// De cette manière le tableau $epargnes[] le sera également
	// et grâce à cela nous pouvons optimiser la fonction get_tranche de xml_epargne.php, voir #1201.
	// $sql .= " GROUP BY d.id_titulaire, d.id_cpte, d.num_complet_cpte, d.solde, d.id_prod, c.libel, c.classe_comptable, d.devise,cli.statut_juridique, cli.pp_nom, cli.pp_prenom, cli.pm_raison_sociale, cli.gi_nom ";
	//	$sql .= " limit 4000";

	$result = executeQuery($db, $sql);
  //$data = $result->fetchrow();

  if (sizeof($result->param[0]) == 0){
    $erreur = new HTML_erreur(_("Rapport liste des epargnes"));
    $erreur->setMessage(_("Il n y a pas d' epargne correspondant à ces critères."));
    $erreur->addButton(BUTTON_OK, "Era-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }

    if ($result->errCode != NO_ERR) {
		$dbHandler->closeConnection(false);
		return $result;
	}

	$idTitulaire = 0;
	$id_prod = 0;

    foreach ($result->param as $infosCpte) {
        $classe_comptable = $infosCpte['classe_comptable'];
        $lib_type_ep = adb_gettext($adsys["adsys_type_cpte_comptable"][$classe_comptable]); //classe_comptable is id of  type epargne
        $idCpte = $infosCpte['id_cpte'];
        $idTitulaire = $infosCpte['id_client'];
        $id_prod = $infosCpte['id_prod'];
        $epargnes[$classe_comptable]["lib_type_ep"] = $lib_type_ep;
        $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["libel_prod_ep"] = $infosCpte['libel'];
        $epargnes['totaux']['nbreclient']++;
        $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["nbr_titulaire"]++;

        if(empty($zone)) {
            $libelle_zone = empty($infosCpte['client_zone_id'])?array("libelle_zone" => "non renseignée"):current(getZone(' id = ' . $infosCpte['client_zone_id']));
	    $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$libelle_zone["libelle_zone"]][$idTitulaire]["nom"] = $infosCpte['nom_cli'];
            $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$libelle_zone["libelle_zone"]][$idTitulaire]["id_titulaire"] = $idTitulaire;
            $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$libelle_zone["libelle_zone"]][$idTitulaire]["comptes"][$idCpte]["id_cpt"] = $infosCpte['id_cpte'];
            $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$libelle_zone["libelle_zone"]][$idTitulaire]["comptes"][$idCpte]["num_complet_cpte"] = $infosCpte['num_complet_cpte'];
            $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$libelle_zone["libelle_zone"]][$idTitulaire]["comptes"][$idCpte]["solde"] = $infosCpte['solde'];
        } else{
            $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$idTitulaire]["nom"] = $infosCpte['nom_cli'];
            $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$idTitulaire]["id_titulaire"] = $idTitulaire;
            $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$idTitulaire]["comptes"][$idCpte]["id_cpt"] = $infosCpte['id_cpte'];
            $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$idTitulaire]["comptes"][$idCpte]["num_complet_cpte"] = $infosCpte['num_complet_cpte'];
            $epargnes[$classe_comptable]["produit_epargnes"][$id_prod]["titulaires"][$idTitulaire]["comptes"][$idCpte]["solde"] = $infosCpte['solde'];
        }
    }
//	foreach ($result->param as $infosCpte) {
//		$idCpte = $infosCpte['id_cpte'];
//		$idTitulaire = $infosCpte['id_client'];
//		$id_prod = $infosCpte['id_prod'];
//		$epargnes[$id_prod]["libel_prod_ep"] = $infosCpte['libel'];
//		$epargnes['totaux']['nbreclient']++;
//		$epargnes[$id_prod]["nbr_titulaire"]++;
//
//		$epargnes[$id_prod]["titulaires"][$idTitulaire]["nom"] = $infosCpte['nom_cli'];
//		$epargnes[$id_prod]["titulaires"][$idTitulaire]["id_titulaire"] = $idTitulaire;
//		$epargnes[$id_prod]["titulaires"][$idTitulaire]["comptes"][$idCpte]["id_cpt"] = $infosCpte['id_cpte'];
//		$epargnes[$id_prod]["titulaires"][$idTitulaire]["comptes"][$idCpte]["num_complet_cpte"] = $infosCpte['num_complet_cpte'];
//		$epargnes[$id_prod]["titulaires"][$idTitulaire]["comptes"][$idCpte]["solde"] = $infosCpte['solde'];
//	}

	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR, $epargnes);
}

function get_impot_mobilier_collecte($critere, $date_debut, $date_fin, $limit, $offset)
{
  global $dbHandler, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();

  $epargnes = array ();

  $id_prod_choisi  = $critere['id_prod'];

  $sql = "
        SELECT cp.id_cpte, cp.cpte_virement_clot, c.id_cpte_base,
        cp.id_prod, pe.libel as libel, cp.date_clot, c.id_client,
        CASE WHEN c.statut_juridique = 1 then c.pp_prenom || ' ' || c.pp_nom
        WHEN c.statut_juridique = 2 THEN c.pm_raison_sociale ELSE c.gi_nom END AS nom_client,
        cp.interet_annuel,v.montant as montant
        FROM ad_cpt cp INNER JOIN ad_cli c ON c.id_client = cp.id_titulaire AND c.id_ag = cp.id_ag
        INNER JOIN adsys_produit_epargne pe ON pe.id = cp.id_prod
        INNER JOIN view_compta v on cp.id_ag = v.id_ag  and v.cpte_interne_cli = coalesce (cp.cpte_virement_clot, c.id_cpte_base) and  v.date_comptable = cp.date_clot
        and cp.id_cpte = v.info_ecriture::int
        WHERE cp.etat_cpte = 2 AND pe.service_financier=true AND pe.id > 5
        AND (pe.classe_comptable = 2 or pe.classe_comptable = 5)
        AND pe.tx_interet > 0 AND pe.retrait_unique = true
        and v.type_operation = 476
        and v.cpte_interne_cli is not null
  ";

  if(!is_null($id_prod_choisi)) {
    $sql .= " AND cp.id_prod = {$id_prod_choisi} ";
  }

  $sql .= "
        AND cp.date_clot BETWEEN date('{$date_debut}') and date('{$date_fin}')
        ORDER BY cp.id_prod, cp.date_clot;
  ";

  $result = executeQuery($db, $sql);

  if ($result->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $result;
  }

  foreach ($result->param as $infosCpte) {
    $idTitulaire = $infosCpte['id_client'];
    $id_prod = $infosCpte['id_prod'];
    $epargnes[$id_prod]["libel_prod_ep"] = $infosCpte['libel'];

    $epargnes[$id_prod]["titulaires"][$idTitulaire]["date_operation"] = pg2phpDate($infosCpte['date_clot']);
    $epargnes[$id_prod]["titulaires"][$idTitulaire]["nom"] = $infosCpte['nom_client'];
    $epargnes[$id_prod]["titulaires"][$idTitulaire]["id_titulaire"] = $idTitulaire;
    $epargnes[$id_prod]["titulaires"][$idTitulaire]["interet_annuel"] += $infosCpte['interet_annuel'];
    $epargnes[$id_prod]["titulaires"][$idTitulaire]["montant_impot"] += $infosCpte['montant'];
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $epargnes);
}

function getCliIntervalAge($ageSup,$ageInf) {
	/**
   	* Fonction utilisée pour le rapport de Concentration des adhérants
   	* @param int $ageSup détermine l'âge maximal de la tranche d'âge
   	* @param int $ageInf détermine l'âge minimal de la tranche d'âge
   	* recupère les clients dont l'age est compris entre $ageInf et $ageSup
   	* @return array : $retour
   	* @author Evelyne Nshimirimana
   	*/

	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$retour = array ();
	$sql = "SELECT id_client FROM ad_cli where id_ag = $global_id_agence and etat = 2";
	$sql .= " and (date_part('year',CURRENT_DATE) - date_part('year',pp_date_naissance)) between ".$ageInf;
	$sql .= " and ".$ageSup;
	$sql .= " and statut_juridique = 1";
	$result = $db->query($sql);
	if (DB::isError($result)) {
	$dbHandler->closeConnection(false);
	signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}

	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
	$retour[] = $row;
	}
	$dbHandler->closeConnection(true);
	if (is_array($retour))
		return $retour;
	else
		return NULL;
}
/**
 * Fonction getCli utilisée pour le rapport de Concentration des adhérants
 * @param int $ageSup détermine l'âge maximal de la tranche d'âge
 * @param int $ageInf détermine l'âge minimal de la tranche d'âge
 * recupère les clients dont l'age est compris entre $ageInf et $ageSup et sex
 * @return array : $retour
 * @author KG-modified frm Evelyne Nshimirimana
 */
function getCliIntervalAgeParSex($ageSup,$ageInf,$sex) {
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$retour = array ();
	$sql = "SELECT id_client FROM ad_cli where id_ag = $global_id_agence and etat = 2";
	$sql .= " and (date_part('year',CURRENT_DATE) - date_part('year',pp_date_naissance)) between ".$ageInf;
	$sql .= " and ".$ageSup;
	$sql .= " and statut_juridique = 1";
	if(isset($sex))
		$sql .= " and pp_sexe = $sex ";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}

	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$retour[] = $row;
	}
	$dbHandler->closeConnection(true);
	if (is_array($retour))
		return $retour;
	else
		return NULL;
}



function calculTauxCroissance($dateDebut,$dateFin) {
	/**
   	* Fonction utilisée pour le rapport de Concentration des adhérants
   	* @param date $dateDebut :  date de debut
   	* @param date $dateFin : date de fin
   	* @return float $taux : retourne le taux en pourcentage
   	* @author Evelyne Nshimirimana
   	*/

	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$nombre_avant = 0;
	$nombre_nouvo_adherant = 0;
	$sql = "select count(id_client) from ad_cli where date_adh <= '$dateDebut'";
	$result = $db->query($sql);
	if (DB::isError($result)) {
	$dbHandler->closeConnection(false);
	signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	$row = $result->fetchrow();
	$nombre_avant = $row[0];
	$sql = "select count(id_client) from ad_cli where date_adh between '$dateDebut' and  '$dateFin'";
	$result = $db->query($sql);
	if (DB::isError($result)) {
	$dbHandler->closeConnection(false);
	signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	$row = $result->fetchrow();
	$nombre_nouvo_adherant = $row[0];
	$dbHandler->closeConnection(true);
	$taux = affichePourcentage($nombre_nouvo_adherant/$nombre_avant,2);
	return $taux;
}


function getConcentrationClients($tranche_age, $statjuridik, $local, $secteur, $localisation= null, $localisation_main = null, $crit_loc = null,$n_agence=null, $n_guichet=null) {
    /**
     * Fonction utilisée pour le rapport de Concentration des adhérants
     *
     * @param bool $tranche_age :  booléen spécifiant si l'age fait partie des critères de répartitions
     * @param bool $local : booléen spécifiant si localisation fait partie des critères de répartitions
     * @param bool $secteur :  booléen spécifiant si le secteur d'activité fait partie des critères de répartitions
     * @param int $localisation : détermine s'il s'agit des localisations des 1er niveau($localisation = null)
     * ou 2ème niveau($localisation = identifiant de localisation de 1er niveau)
     * @return array $tabRes : $tabRes[0] tableau des resultats monocritère, $tabRes[1] pour multicritère
     * @author Evelyne Nshimirimana
     */
    global $global_id_agence, $dbHandler, $adsys;
    $db = $dbHandler->openConnection();
    $secteurs = get_secteurs_activite();
    $tabUnique = array();//contiendra le resultat d'une répartition monocritère
    $tabConcentre = array();//contiendra le résultat d'une répartition croisée

    $verif_tranche = 0;
    $sql = "select count(id_client) from ad_cli where etat = 2";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    $row = $result->fetchrow();
    $nombre_total_clients = $row[0];
    $dbHandler->closeConnection(true);


    // localisation
    if ($local) {
      if ($localisation_main > 0){
        $loc = get_localisation_rwanda_rapport($crit_loc,$localisation_main );

      }else {
        if ($localisation == null ) { // si choix = TOUS;
          $loc = get_localisation(1);
          $indice = 1;
        } elseif ($localisation > 0) { // localisation du deuxième niveau;
          $indice = 2;
          foreach (get_localisation(2) as $key => $value) {
            if (($value ['parent'] == $localisation)) {
              $loc [$key] ['id'] = $value ['id'];
              $loc [$key] ['libel'] = $value ['libel'];
            }
          }
        }
      }
      // part 7
      if ($statjuridik && $local && $tranche_age  && !$secteur) {
        foreach ( $loc as $key_loc => $value_loc ) {
          $tabConcentre [$key_loc] ['libel_stat_tableau'] = adb_gettext ( $adsys ["adsys_stat_jur"] [1] );
          $tabConcentre [$key_loc] ['libel_stat_loc_tranche'] = adb_gettext ( $adsys ["adsys_stat_jur"][1] );
          if ($localisation_main > 0){
            $data_clients = getClientStatLocRwanda( $value_loc ['id'], $localisation_main, 1 );
          }else{
            if (isFusion()){
                $data_clients = getClientsStatLoc( $value_loc ['id'], $indice, 1 ,$n_agence,$n_guichet);
            }else{
                $data_clients = getClientsStatLoc( $value_loc ['id'], $indice, 1 );
            }
          }
          $tranchesHomme = getRepartitionTrancheAgeParSex($data_clients,1);
          $tranchesFam  =  getRepartitionTrancheAgeParSex($data_clients,2);

          $tabConcentre [$key_loc]['homme'] = $tranchesHomme;
          $tabConcentre [$key_loc]['femme'] = $tranchesFam;
          $verif_tranche = 2;
          $tabConcentre [$key_loc] ['tranche'] = $verif_tranche;
        }
      }
      //localisation/statut juridique/secteur
      if (isFusion()){
          $listStatut = getClientsStatutJuridique (false, $n_agence, $n_guichet);
      } else{
          $listStatut = getClientsStatutJuridique ();
      } ;
      if ($local && $secteur && $statjuridik) {
        foreach ( $loc as $key_loc => $value_loc ) {
          $tabConcentre [$key_loc] ['stat_sect'] = 1;
          if ($localisation_main > 0){
            $tabConcentre [$key_loc] ['libel_loc'] = $value_loc ['libelle_localisation'];
          }else {
            $tabConcentre [$key_loc] ['libel_loc'] = $value_loc ['libel'];
          }
          $tabConcentre [$key_loc] ['Loc_Sect_Stat'] = _ ( "localSectStat" );

          foreach ( $secteurs as $key_sect => $value_sect ) {
            $tabConcentre [$key_loc] [$key_sect]['libel_sect'] = $value_sect ['libel'];
            foreach ( $listStatut as $key_stat => $value_stat ) {
              if ($statjuridik > 0) {
                if ($localisation_main > 0) {
                  $cli_sect_stat_loc = getClientsSectStatLocRwanda($value_loc ['id'],$localisation_main,$value_sect ['id'], $value_stat ['statut_juridique']);
                }else {
                  if (isFusion()){
                      $cli_sect_stat_loc = getClientsSectStatLoc($value_loc ['id'], $indice, $value_sect ['id'], $value_stat ['statut_juridique'],$n_agence,$n_guichet);
                  }else{
                      $cli_sect_stat_loc = getClientsSectStatLoc($value_loc ['id'], $indice, $value_sect ['id'], $value_stat ['statut_juridique']);
                  }
                }
                $tabConcentre [$key_loc][$key_sect] [$key_stat] ['libel_stat'] = adb_gettext ( $adsys ["adsys_stat_jur"] [$value_stat ['statut_juridique']] );


                $tabConcentre [$key_loc][$key_sect][$key_stat]['nbre']  = count($cli_sect_stat_loc);
                $tabConcentre [$key_loc][$key_sect][$key_stat]['prc']   = affichePourcentage($tabConcentre[$key_loc][$key_sect][$key_stat]['nbre']/$nombre_total_clients,2);

              }
            }
          }
        }
      }

      if(isFusion()){
          $listStatut = getClientsStatutJuridique (true,$n_agence, $n_guichet);
      }else{
          $listStatut = getClientsStatutJuridique (true);
      }
      // Part 2 : Localisation et statut juridique
      if ($statjuridik && $loc && !$secteur) {
        foreach ( $loc as $key_loc => $value_loc ) {
          foreach ( $listStatut as $key_stat => $value_stat ) {
            // get count client par statut juridique et localisation
            if ($localisation_main > 0){
              $data_clients = getClientStatLocRwanda($value_loc ['id'],$localisation_main,$value_stat ['statut_juridique'], $value_stat['pp_sexe']);
            }else {
              if (isFusion()){
                  $data_clients = getClientStatLoc($value_loc ['id'], $indice, $value_stat ['statut_juridique'], $value_stat['pp_sexe'],$n_agence,$n_guichet);
              }else{
                  $data_clients = getClientStatLoc($value_loc ['id'], $indice, $value_stat ['statut_juridique'], $value_stat['pp_sexe']);
              }
            }
            if (! $tranche_age) {
              if (! $secteur) {

                $libel = adb_gettext ( $adsys ["adsys_stat_jur"] [$value_stat ['statut_juridique']] );
                $libel .= ( isset($value_stat['pp_sexe'])?($value_stat['pp_sexe']==1?', Hommes':', Femmes'):'' );
                $tabConcentre [$key_loc] [$key_stat] ['libel_stat'] = $libel;

                //$tabConcentre [$key_loc] [$key_stat] ['libel_stat'] = adb_gettext ( $adsys ["adsys_stat_jur"] [$value_stat ['statut_juridique']] );
                $tabConcentre [$key_loc] [$key_stat] ['nbre'] = count ( $data_clients );
                $tabConcentre [$key_loc] [$key_stat] ['prc'] = affichePourcentage ( $tabConcentre [$key_loc] [$key_stat] ['nbre'] / $nombre_total_clients, 2 );
                $verif_tranche = 0;
              }
            }
          }
          if ($localisation_main > 0){
            $tabConcentre [$key_loc] ['libel_loc'] = $value_loc ['libelle_localisation'];
          }else {
            $tabConcentre [$key_loc] ['libel_loc'] = $value_loc ['libel'];
          }
          $tabConcentre [$key_loc] ['tranche'] = $verif_tranche;
        }
      }



      // localisation croisée au secteur d'activité
      //if ($secteur && $loc && $tranche_age ) {
		if ($secteur && $loc ) {
			foreach ( $loc as $key_loc => $value_loc ) {
				foreach ( $secteurs as $key_sect => $value_sect ) {
          if ($localisation_main > 0) {
            $data_clients = getClientsSectLocRwanda($value_loc ['id'],$localisation_main,$value_sect ['id']);
          }else{
            if(isFusion()){
                $data_clients = getClientsSectLoc ( $value_loc ['id'], $indice, $value_sect ['id'],$n_agence,$n_guichet);
            }else{
                $data_clients = getClientsSectLoc ( $value_loc ['id'], $indice, $value_sect ['id'] );
            }
          }
					if (! $tranche_age) {
						$tabConcentre [$key_loc] [$key_sect] ['nbre'] = count ( $data_clients );
						$tabConcentre [$key_loc] [$key_sect] ['prc'] = affichePourcentage ( $tabConcentre [$key_loc] [$key_sect] ['nbre'] / $nombre_total_clients, 2 );
						$verif_tranche = 2; //escape number not to clash against  tranche 1 et 2
					} 					// localisation croisée au secteur d'activité et tranche d'âge
					else {
						$tranches = getRepartitionTrancheAge ( $data_clients );
						$tabConcentre [$key_loc] [$key_sect] ['tranche'] = $tranches;
						$verif_tranche = 1;
					}
					$tabConcentre [$key_loc] [$key_sect] ['libel_sect'] = $value_sect ['libel'];
				}
        if ($localisation_main > 0) {
          $tabConcentre [$key_loc] ['libel_loc'] = $value_loc ['libelle_localisation'];
        }else {
          $tabConcentre [$key_loc] ['libel_loc'] = $value_loc ['libel'];
        }
				$tabConcentre [$key_loc] ['tranche'] = $verif_tranche;
			}
		} 		// sans choix secteur
		else {
			foreach ( $loc as $key_loc => $value_loc ) {
        if ($localisation_main > 0) {
          $data_clients = getClientSecLocRwanda($value_loc ['id'],$localisation_main,-1);
        }else {
          if(isFusion()){
              $data_clients = getClientsSectLoc($value_loc ['id'], $indice, -1,$n_agence,$n_guichet);
          }else {
              $data_clients = getClientsSectLoc($value_loc ['id'], $indice, -1);
          }
        }
				// exclude for statut juridique
				if (! $tranche_age && !$statjuridik) {
          if ($localisation_main > 0) {
            $tabUnique [$key_loc] ['libel'] = $value_loc ['libelle_localisation'];
          }else{
            $tabUnique [$key_loc] ['libel'] = $value_loc ['libel'];
          }
						$tabUnique [$key_loc] ['nbre'] = count ( $data_clients );
						$tabUnique [$key_loc] ['prc'] = affichePourcentage ( $tabUnique [$key_loc] ['nbre'] / $nombre_total_clients, 2 );
					
				} 				

				// localisation croisée aux tranches d'âges indépendamment des secteurs d'activité
				else if(!$statjuridik){
                    if(isFusion()){
                        $tabConcentre [$key_loc] ['tranche'] = getRepartitionTrancheAge ( $data_clients,$n_agence,$n_guichet );
                    }else{
                        $tabConcentre [$key_loc] ['tranche'] = getRepartitionTrancheAge ( $data_clients );
                    }
          if ($localisation_main > 0) {
            $tabConcentre [$key_loc] ['libel_loc'] = $value_loc ['libelle_localisation'];
          }else{
            $tabConcentre [$key_loc] ['libel_loc'] = $value_loc ['libel'];
          }
					$tabConcentre [$key_loc] ['libel_sectX'] = _ ( "tous les secteurs" );
				}
			}
		}
	} 	// fin if localisation
	  // indépendamment du localisation, secteurs d'activité croisés avec les tranches d'âges
	else {
			if($secteur && !$statjuridik) {
				foreach($secteurs as $key_sect => $value_sect) {
				    if(isFusion()){
                        $cli_sect = getClientsSectLoc(0, 0, $value_sect['id'],$n_agence,$n_guichet);
                    }else{
                        $cli_sect = getClientsSectLoc(0, 0, $value_sect['id']);
                    }
					if(!$tranche_age) {
					//on recupere le nombre par secteur indépendamment de l'âge
					$tabUnique[$key_sect]['nbre']  = count($cli_sect);
					$tabUnique[$key_sect]['prc']   = affichePourcentage($tabUnique[$key_sect]['nbre']/$nombre_total_clients,2);
					$tabUnique[$key_sect]['libel'] = $value_sect['libel'];
					}
					else {
						$tabConcentre[$key_sect]['libel_sect'] = $value_sect['libel'];
						$tabConcentre[$key_sect]['tranche'] = getRepartitionTrancheAge($cli_sect);//tablo contenant le nombre et le libelle par tranche;
					}
					$tabConcentre[$key_sect]['lib_locX'] = _("toutes les localites");
		
		     }
	    }
		//ena localisation si ladans
		else if ($secteur && $tranche_age && !$statjuridik) {
			foreach ( $secteurs as $key_sect => $value_sect ) {
			    if(isFusion()){
                    $cli_sect = getClientsSectLoc ( 0, 0, $value_sect ['id'],$n_agence, $n_guichet);
                }else{
                    $cli_sect = getClientsSectLoc ( 0, 0, $value_sect ['id'] );
                }
				if (! $tranche_age && ! $statjuridik) {
					// on recupere le nombre par secteur indépendamment de l'âge
					$tabUnique [$key_sect] ['nbre'] = count ( $cli_sect );
					$tabUnique [$key_sect] ['prc'] = affichePourcentage ( $tabUnique [$key_sect] ['nbre'] / $nombre_total_clients, 2 );
					$tabUnique [$key_sect] ['libel'] = $value_sect ['libel'];
				} 

				else if ($tranche_age && ! $statjuridik) { // $tranche d'age
					$tabConcentre [$key_sect] ['libel_sect'] = $value_sect ['libel'];
					$tabConcentre [$key_sect] ['tranche'] = getRepartitionTrancheAge ( $cli_sect ); // tablo contenant le nombre et le libelle par tranche;
				}
				$tabConcentre [$key_sect] ['lib_loc'] = _ ( "toutes les localites" );
			}
		} 		// tranches d'âge independamment des secteurs et localisations
		  
		// Part 4 : statut juridique et secteur
		else if ($secteur && $statjuridik && !$tranche_age) {
			if(isFusion()){
                $listStatut = getClientsStatutJuridique (true,$n_agence,$n_guichet);
            } else{
                $listStatut = getClientsStatutJuridique (true);
            }
			if ($statjuridik > 0) {
				foreach ( $secteurs as $key_sect => $value_sect ) {
					$tabConcentre [$key_sect] ['libel_sect'] = $value_sect ['libel'];
					$tabConcentre [$key_sect] ['libel_stat'] = "XXX";
					foreach ( $listStatut as $key_stat => $value_stat ) {
						// get les donnees nombre client par stat jur et secteur d'activité
						$clientSectStat = getClientsSectStat ( $value_sect ['id'], $value_stat ['statut_juridique'], $value_stat ['pp_sexe'] );
						$nbre = count ( $clientSectStat );
						$prc = affichePourcentage ( $nbre / $nombre_total_clients, 2 );
						$array2 [0] = $nbre;
						$array2 [1] = $prc;
						$tabConcentre [$key_sect] [$key_stat] = $array2;
					}
					$tabConcentre [$key_sect] ['stat_sect'] = 1;
				}
			}
		} 
	
		
		//PART 5 Retourcorrected
		elseif ($secteur && $tranche_age && $statjuridik) {
			if(isFusion()){
                $listStatut = getClientsStatutJuridique (false,$n_agence,$n_guichet); // a traiter comme dans localisation
            } else{
                $listStatut = getClientsStatutJuridique (); // a traiter comme dans localisation
            }
			//foreach ( $listStatut as $key_stat => $value_stat ) {
				
				//$tabConcentre [$key_stat] ['libel_stat_tableau'] = adb_gettext ( $adsys ["adsys_stat_jur"] [$value_stat ['statut_juridique']] );
				$tabConcentre ['$key_stat'] ['libel_stat_tableau'] = adb_gettext ( $adsys ["adsys_stat_jur"] [1] );
				$tabConcentre ['$key_stat'] ['libel_stat'] = adb_gettext ( $adsys ["adsys_stat_jur"][1] );
				foreach ( $secteurs as $key_sect => $value_sect ) {
					if(isFusion()){
                        $cli_sect_stat = getClientsSectStat ( $value_sect ['id'], 1 ,null,$n_agence,$n_guichet);
                    }else{
                        $cli_sect_stat = getClientsSectStat ( $value_sect ['id'], 1 );
                    }
					
					$tabConcentre ['$key_stat'] [$key_sect] ['libel_sect'] = $value_sect ['libel'];
					$tabConcentre ['$key_stat'] [$key_sect] ['tranche'] = getRepartitionTrancheAge ( $cli_sect_stat );
					
				}
			//}
		} 


		// Part 3 : tranche d'age /tranche d'age et statut juridique
		else if ($tranche_age) {
			if (! $statjuridik) // tranche d'age only
			    if (isFusion()){
                    $tabUnique = getRepartitionTrancheAge ($DATA = NULL,$n_agence,$n_guichet);
                }else{
                    $tabUnique = getRepartitionTrancheAge ();
                }
			else {
			    if (isFusion()){
                    $tranchesHomme = getRepartitionTrancheAgeParSex($DATA = NULL,1,$n_agence,$n_guichet);
                    $tranchesFam  =  getRepartitionTrancheAgeParSex($DATA = NULL,2,$n_agence,$n_guichet);
                }else{
                    $tranchesHomme = getRepartitionTrancheAgeParSex($DATA = NULL,1);
                    $tranchesFam  =  getRepartitionTrancheAgeParSex($DATA = NULL,2);
			    }

				$tabConcentre ['Personne Physique'] ['homme'] = $tranchesHomme;
				$tabConcentre ['Personne Physique'] ['femme'] = $tranchesFam;
				$tabConcentre ['Personne Physique'] ['Lib_statutjuridique'] = adb_gettext ( $adsys ["adsys_stat_jur"][1] );
				$verif_tranche =1;
				
			}
			
		} 		

		// Part 1 : statut juridique only
		else if ($statjuridik) {
			if ($statjuridik > 0) {
				// get count client par statut juridique into list
                if(isFusion()){
                    $listStatut = getClientsStatutJuridique (true,$n_agence,$n_guichet);
                }else{
                    $listStatut = getClientsStatutJuridique (true);
                }
              foreach ( $listStatut as $key => $value ) {
					$libel = adb_gettext ( $adsys ["adsys_stat_jur"] [$value ['statut_juridique']] );
                    $libel .= ( isset($value['pp_sexe'])?($value['pp_sexe']==1?', Hommes':', Femmes'):'' );
                    $tabUnique [$key] ['libel'] = $libel;
					$tabUnique [$key] ['statut_juridique'] = $value ['statut_juridique'];
					$tabUnique [$key] ['nbre'] = $value ['count'];
					$tabUnique [$key] ['prc'] = affichePourcentage ( $tabUnique [$key] ['nbre'] / $nombre_total_clients, 2 );
				}
			}
		}
	}

	$tabRes [0] = $tabUnique;
	$tabRes [1] = $tabConcentre;
	return $tabRes;
}



function getRepartitionTrancheAge($DATA = NULL, $n_agence=null, $n_guichet= null){
	/**
   	* Fonction utilisée pour le rapport de Concentration des adhérants
   	* @param  array $DATA :  ensemble des clients d'un secteur ou(ou logique) d'une localisation
   	* @return array $tabTranche : retourne un tableau contenant le nombre de clients pour chaque tranche
   	* @author Evelyne Nshimirimana
   	*/
	global $adsys, $dbHandler;
	$db = $dbHandler->openConnection();
	$sql = "select count(id_client) from ad_cli where etat = 2";
	if ($n_agence != null){
	  $sql .= " and n_agence = $n_agence";
    }
    if($n_guichet!=null){
        $sql .= " and n_guichet = $n_guichet";
    }
	$result = $db->query($sql);
	if (DB::isError($result)) {
	$dbHandler->closeConnection(false);
	signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	$row = $result->fetchrow();
	$nombre_total_clients = $row[0];
	$dbHandler->closeConnection(true);
	for($i = 1 ; $i <= count($adsys["adsys_tranche_age_client"]) ; $i++) {
		$inf = substr($adsys["adsys_tranche_age_client"][$i],0,2);
		$sup = substr($adsys["adsys_tranche_age_client"][$i],-2);
		if(!is_array($DATA)) {
			$nombre = count(getCliIntervalAge($sup,$inf));
			if($i == 1) $libel= _("Inférieur à 16 ans");
			elseif($i == count($adsys["adsys_tranche_age_client"])) $libel = _("Supérieur à 95 ans");
			else $libel = sprintf(_("De %s à %s ans"),$inf,$sup);
			$tabTranche[$i]['libel']  = $libel;
		}
		else {
			$nombre = 0;
			foreach($DATA as $key => $value) {
				if($value['pp_date_naissance']!= NULL) $age_cli = date('Y') - substr($value['pp_date_naissance'],0,4);
			  	else $age_cli = -1;//age inconnu
				if(($inf <= $age_cli) && ($sup >=$age_cli)){
				++$nombre;
				}//fin comparaison age
			}
		}
		$tabTranche[$i]['nbre'] = $nombre;
		$tabTranche[$i]['prc']  = affichePourcentage($nombre/$nombre_total_clients,2);
	}
	return $tabTranche;
}

/**
 * Fonction utilisée pour le rapport de Concentration des adhérants
 * récupere l'ensemble des clients particuliers dans un tableau
 * Tranche d'age  avec option de separé en homme et femme(pp.sex)
 * KG
 */
function getRepartitionTrancheAgeParSex($DATA = NULL,$sex = Null,$n_agence=null,$n_guichet=null){

	global $adsys, $dbHandler;
	$db = $dbHandler->openConnection();
	$sql = "select count(id_client) from ad_cli where etat = 2";
	if(isset($sex))
		$sql .= " AND pp_sexe = $sex ";
    if(isset($n_agence))
        $sql .= " AND n_agence = $n_agence ";
    if(isset($n_guichet))
        $sql .= " AND n_guichet = $n_guichet ";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	$row = $result->fetchrow();
	$nombre_total_clients = $row[0];
	$dbHandler->closeConnection(true);
	for($i = 1 ; $i <= count($adsys["adsys_tranche_age_client"]) ; $i++) {
		$inf = substr($adsys["adsys_tranche_age_client"][$i],0,2);
		$sup = substr($adsys["adsys_tranche_age_client"][$i],-2);
		if(!is_array($DATA)) {
			
			if(!isset($sex)){
			$nombre = count(getCliIntervalAge($sup,$inf));
			}
			else{
		    $nombre = count(getCliIntervalAgeParSex($sup,$inf,$sex));
			}
			if($i == 1) $libel= _("Inférieur à 16 ans");
			elseif($i == count($adsys["adsys_tranche_age_client"])) $libel = _("Supérieur à 95 ans");
			else $libel = sprintf(_("De %s à %s ans"),$inf,$sup);
			$tabTranche[$i]['libel']  = $libel;
		}
		else {
			
			$nombre = 0;
			
			foreach($DATA as $key => $value) {
				if($value['pp_date_naissance']!= NULL) 
					$age_cli = date('Y') - substr($value['pp_date_naissance'],0,4);
				  else $age_cli = -1;//age inconnu
				
				if(($inf <= $age_cli) && ($sup >=$age_cli) && ($value['pp_sexe']==$sex)){
					++$nombre;
				}//fin comparaison age	 
			}
		}
		$tabTranche[$i]['nbre'] = $nombre;
		$tabTranche[$i]['prc']  = affichePourcentage($nombre/$nombre_total_clients,2);
		//$tabTranche[$i]['prc']  = affichePourcentage($nombre/(count($DATA)),2);
	}
	return $tabTranche;
}


function getClientsSectLoc($idloc, $indice, $idsecteur,$n_agence=null, $n_guichet=null){
	/**
   	* Fonction utilisée pour le rapport de Concentration des adhérants
   	* récupere l'ensemble des clients particuliers dans un tableau
   	* @param int $idloc :  valeur de l'identifiant de localisation dans la table des clients'
   	* @param int $indice : détermine le niveau des localisations(actuellement, c'est 1 ou 2 )
   	* @param int $idsecteur : valeur de l'identifiant du secteur d'activité dans la table des clients
   	* @return array $retour
   	* @author Evelyne Nshimirimana
   	*/

	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
  	$retour = array ();
  	$sql = "select id_client,pp_date_naissance from ad_cli where id_ag = $global_id_agence and etat = 2 ";
  	if($indice > 0) {
  		if($idloc > 0) $sql .= " and id_loc".$indice ." = ". $idloc;
  		else $sql .= " and (id_loc$indice is null or id_loc$indice = 0)";
  	}
  	if($idsecteur > 0) $sql .= " and sect_act = $idsecteur";
  	elseif($idsecteur == 0) $sql .= " and (sect_act is null or sect_act = 0)";

    if ($n_agence !=null){
        $sql .= " and n_agence = $n_agence";
    }

    if ($n_guichet !=null){
        $sql .= " and n_guichet = $n_guichet";
    }
	$result = $db->query($sql);
  	if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  	}
  	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
  		$retour[] = $row;
  	}
  	$dbHandler->closeConnection(true);
  	if (is_array($retour))
    	return $retour;
  	else
    return NULL;
}

/**
 * Fonction utilisée pour le rapport de Concentration des adhérants
 * récupere l'ensemble des clients particuliers dans un tableau
 * KG
 */
function getClientsSectStatLoc($idloc, $indice, $idsecteur,$statut_juridique,$n_agence=null,$n_guichet=null){


	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$retour = array ();
	$sql = "select id_client,pp_date_naissance from ad_cli where id_ag = $global_id_agence and etat = 2 ";
	
	//"SELECT statut_juridique,sect_act, id_loc1,id_loc2,loc3 FROM ad_cli where id_ag = $global_id_agence group by statut_juridique,sect_act, id_loc1,id_loc2,loc3";
	if($indice > 0) {
		if($idloc > 0) $sql .= " and id_loc".$indice ." = ". $idloc;
		else $sql .= " and (id_loc$indice is null or id_loc$indice = 0)";
	}
	if($idsecteur > 0) $sql .= " and sect_act = $idsecteur";
	elseif($idsecteur == 0) $sql .= " and (sect_act is null or sect_act = 0)";
	
	if($statut_juridique > 0)
		$sql .= " and statut_juridique = $statut_juridique";
	elseif($idsecteur == 0)
	$sql .= " and (statut_juridique is null or statut_juridique = 0)";

    if ($n_agence !=null){
        $sql .= " and n_agence = $n_agence";
    }

    if ($n_guichet !=null){
        $sql .= " and n_guichet = $n_guichet";
    }
	
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$retour[] = $row;
	}
	$dbHandler->closeConnection(true);
	if (is_array($retour))
		return $retour;
	else
		return NULL;
}
/**
 * Fonction utilisée pour le rapport de Concentration des adhérants
 * Statut_jurididuque et localisation
 * récupere l'ensemble des clients particuliers dans un tableau
 * KG
 */
function getClientsStatLoc($idloc, $indice,$statut_juridique,$n_agence=null,$n_guichet=null){


	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$retour = array ();
	$sql = "select id_client,pp_date_naissance,pp_sexe from ad_cli where id_ag = $global_id_agence and etat = 2";

	//"SELECT statut_juridique,sect_act, id_loc1,id_loc2,loc3 FROM ad_cli where id_ag = $global_id_agence group by statut_juridique,sect_act, id_loc1,id_loc2,loc3";
	if($indice > 0) {
		if($idloc > 0) $sql .= " and id_loc".$indice ." = ". $idloc;
		else $sql .= " and (id_loc$indice is null or id_loc$indice = 0)";
	}
	if($statut_juridique > 0)
		$sql .= " and statut_juridique = $statut_juridique";
	elseif($statut_juridique == 0)
	$sql .= " and (statut_juridique is null or statut_juridique = 0)";

	if ($n_agence !=null){
	  $sql .= " and n_agence = $n_agence";
    }

    if ($n_guichet !=null){
        $sql .= " and n_guichet = $n_guichet";
    }
	
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$retour[] = $row;
	}
	$dbHandler->closeConnection(true);
	if (is_array($retour))
		return $retour;
	else
		return NULL;
}

/**
 * Fonction utilisée pour le rapport de Concentration des adhérants
 * get clientSectStat
 * @param
 * @param
 * @param
 * @return array $retour
 * @author KG
 */
/* 
function getClientsSectStat($idsecteur,$statut_juridique){
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$retour = array ();
	$sql = "select id_client, sect_act, statut_juridique ,pp_date_naissance from ad_cli where id_ag = $global_id_agence ";
	
	if($idsecteur > 0) 
		$sql .= " and sect_act = $idsecteur";
	elseif($idsecteur == 0) 
	    $sql .= " and (sect_act is null or sect_act = 0)";
	
	if($statut_juridique > 0) 
		$sql .= " and statut_juridique = $statut_juridique";
	elseif($idsecteur == 0) 
	    $sql .= " and (statut_juridique is null or statut_juridique = 0)";

	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$retour[] = $row;
	}
	$dbHandler->closeConnection(true);
	if (is_array($retour))
		return $retour;
	else
		return NULL;
}
 */
function getClientsSectStat($idsecteur,$statut_juridique,$pp_sexe=null,$n_agence=null,$n_guichet=null){
		global $dbHandler,$global_id_agence;
		$db = $dbHandler->openConnection();
		$retour = array ();
		$sql = "select id_client, sect_act, statut_juridique ,pp_date_naissance from ad_cli where id_ag = $global_id_agence and etat = 2";
	
		if($idsecteur > 0)
			$sql .= " and sect_act = $idsecteur";

		elseif($idsecteur == 0)
		    $sql .= " and (sect_act is null or sect_act = 0)";

		if($statut_juridique > 0)
			$sql .= " and statut_juridique = $statut_juridique";

        if($pp_sexe != null)
            $sql .= " and pp_sexe = $pp_sexe";

		elseif($idsecteur == 0)
		    $sql .= " and (statut_juridique is null or statut_juridique = 0)";

        if ($n_agence !=null){
            $sql .= " and n_agence = $n_agence";
        }

        if ($n_guichet !=null){
            $sql .= " and n_guichet = $n_guichet";
        }

		$result = $db->query($sql);
		if (DB :: isError($result)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
		}
		while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
			$retour[] = $row;
		}
		$dbHandler->closeConnection(true);
		if (is_array($retour))
			return $retour;
		else
			return NULL;
	}
	

/**
 * Fonction utilisée pour le rapport de Concentration des adhérants
 * get clientStatLoc
 * @param 
 * @param
 * @param
 * @return array $retour
 * @author KG
 */
function getClientStatLoc($idloc, $indice, $idstat, $pp_sexe=null,$n_agence=null,$n_guichet=null){

	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$retour = array ();
	$sql = "select id_client,pp_date_naissance from ad_cli where id_ag = $global_id_agence and etat = 2 ";
	if($indice > 0) {
		if($idloc > 0) $sql .= " and id_loc".$indice ." = ". $idloc;
		else $sql .= " and (id_loc$indice is null or id_loc$indice = 0)";
	}
	if($idstat > 0) $sql .= " and statut_juridique = $idstat";
	elseif($idstat == 0) $sql .= " and (statut_juridique is null or statut_juridique = 0)";

    if ($n_agence !=null){
        $sql .= " and n_agence = $n_agence";
    }

    if ($n_guichet !=null){
        $sql .= " and n_guichet = $n_guichet";
    }

    if($pp_sexe != null)
      $sql .= " and pp_sexe = $pp_sexe";

	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$retour[] = $row;
	}
	$dbHandler->closeConnection(true);
	if (is_array($retour))
		return $retour;
	else
		return NULL;
}


/**
 * Fonction utilisée pour le rapport de Concentration des adhérants
 * get clientStatutJuridique
 * @param
 * @param
 * @param
 * @return array $retour
 * @author KG
 */
//get client  pourcentage par statut juridique
 function getClientsStatutJuridique($sexe = false,$n_agence=null, $n_guichet=null){
	
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
  	$retour = array ();

    if ($sexe == true) {
      $sql = "SELECT statut_juridique,pp_sexe, count(id_client) FROM ad_cli where id_ag = $global_id_agence and etat = 2 AND statut_juridique = 1 AND pp_sexe IS NOT NULL";
      if (isFusion()){
        if($n_agence !=null){
          $sql .= " AND n_agence = $n_agence";
        }
        if ($n_guichet !=null){
          $sql .= " AND n_guichet = $n_guichet";
        }
      }
      $sql .=" group by statut_juridique, pp_sexe";
      $sql .=" UNION SELECT statut_juridique, pp_sexe,count(id_client) FROM ad_cli where id_ag = $global_id_agence and etat = 2 AND statut_juridique <> 1 ";
        if (isFusion()){
            if($n_agence !=null){
                $sql .= " AND n_agence = $n_agence";
            }
            if ($n_guichet !=null){
                $sql .= " AND n_guichet = $n_guichet";
            }
        }
      $sql .=" group by statut_juridique,pp_sexe order by statut_juridique ASC;";
    } else {
      $sql = "SELECT statut_juridique ,count(id_client) FROM ad_cli where id_ag = $global_id_agence and etat = 2 ";
        if (isFusion()){
            if($n_agence !=null){
                $sql .= " AND n_agence = $n_agence";
            }
            if ($n_guichet !=null){
                $sql .= " AND n_guichet = $n_guichet";
            }
        }
      $sql .= "group by statut_juridique order by statut_juridique ";
    }

	$result = $db->query($sql);
  	if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  	}
  	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
  		$retour[] = $row;
  	}
  	$dbHandler->closeConnection(true);
  	if (is_array($retour))
    	return $retour;
  	else
    return NULL;
}


function get_secteurs_activite() {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $retour = array ();
  $sql = "SELECT * FROM adsys_sect_activite where id_ag = $global_id_agence";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($retour, $row);
  }
  array_push($retour, array (
               "id" => 0,
               "libel" => _("Non renseigné")
             ));
  $dbHandler->closeConnection(true);
  return $retour;
}
function get_produits_credit($devise = NULL) {
  global $dbHandler;
  global $global_multidevise,$global_id_agence;
  $db = $dbHandler->openConnection();
  $retour = array ();
  $sql = "SELECT id, libel FROM adsys_produit_credit WHERE id_ag = $global_id_agence";
  if (($devise != NULL) && ($devise != '0') && ($global_multidevise)) {
    $sql .= " AND devise='" .$devise . "'";
  }

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($retour, $row);
  }
  $dbHandler->closeConnection(true);
  return $retour;
}
function get_produits_credit_balance_agee($devise = NULL) {
	global $dbHandler;
	global $global_multidevise,$global_id_agence;
	$db = $dbHandler->openConnection();
	$retour = array ();
	$sql = "SELECT id, libel FROM adsys_produit_credit WHERE id_ag = $global_id_agence";
	if (($devise != NULL) && ($devise != '0') && ($global_multidevise)) {
		$sql .= " AND devise='" .$devise . "'";
	}

	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
	}
	
	while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $retour[$rows["id"]]=$rows;
	}
	$dbHandler->closeConnection(true);
	return $retour;
}

function get_produits_epargne() {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $retour = array ();

  $sql = "SELECT id, libel FROM adsys_produit_epargne WHERE id_ag = $global_id_agence AND (classe_comptable=1 OR classe_comptable=2 OR classe_comptable=5  OR classe_comptable=6 OR classe_comptable=3 OR classe_comptable=8)";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($retour, $row);
  }
  $dbHandler->closeConnection(true);
  return $retour;
}

function get_localisation($niv) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $retour = array ();
  if ($niv == 1)
    $cond = "parent IS NULL";
  else
    $cond = "parent IS NOT NULL";
  $sql = "SELECT * FROM adsys_localisation WHERE id_ag = $global_id_agence AND $cond";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($retour, $row);
  }
  array_push($retour, array (
               "id" => 0,
               "libel" => _("Non renseigné")
             ));
  $dbHandler->closeConnection(true);
  return $retour;
}
function get_dates() {
  /* Renvoie les dates suivantes (dans un array) :
        'j' : date du jour
        's1' : date du jour + 1 semaine
        's2', 's3'
        'm1' : date du jour + 1 mois
        'm2', 'm3', 'm6', 'm9', 'm12'
  */
  $m = date('m');
  $d = date('d');
  $y = date('Y');
  $retour['j'] = date('d/m/Y', mktime(0, 0, 0, $m, $d, $y));
  $retour['s1'] = date('d/m/Y', mktime(0, 0, 0, $m, $d +7, $y));
  $retour['s2'] = date('d/m/Y', mktime(0, 0, 0, $m, $d +14, $y));
  $retour['s3'] = date('d/m/Y', mktime(0, 0, 0, $m, $d +21, $y));
  $retour['m1'] = date('d/m/Y', mktime(0, 0, 0, $m +1, $d, $y));
  $retour['m2'] = date('d/m/Y', mktime(0, 0, 0, $m +2, $d, $y));
  $retour['m3'] = date('d/m/Y', mktime(0, 0, 0, $m +3, $d, $y));
  $retour['m6'] = date('d/m/Y', mktime(0, 0, 0, $m +6, $d, $y));
  $retour['m9'] = date('d/m/Y', mktime(0, 0, 0, $m +9, $d, $y));
  $retour['m12'] = date('d/m/Y', mktime(0, 0, 0, $m, $d, $y +1));
  return $retour;
}
function get_prevision_credit($devise) {
  /* Va chercher les prévisions sur les crédits pour j, s1, s2, s3, m1, m2, m3, m6, m9, m12*/
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  //Récupère pour chaque tranche de date le capital attendu et les intérêts attendus
  $dates = get_dates();
  reset($dates);
  while (list ($key, $value) = each($dates)) {
    //Pour chaque tranche de dates
    $sql = "SELECT b.id_doss, sum(b.solde_cap), sum(b.solde_int) FROM ad_etr b  ";
    $sql .= " ,ad_dcr a,adsys_produit_credit c ";
    $sql .= " WHERE b.id_ag = a.id_ag and a.id_ag = c.id_ag and c.id_ag = $global_id_agence and (date_ech < '$value') AND (remb='f') ";
    $sql .= "AND c.devise='" . $devise . "' AND a.id_doss=b.id_doss AND c.id=a.id_prod ";
    $sql .= " GROUP BY b.id_doss";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    $retour['cap_attendu'][$key] = 0;
    $retour['int_attendu'][$key] = 0;
    while ($row = $result->fetchrow()) { //Pour chaque résultat
      $retour['cap_attendu'][$key] += $row[1];
      $retour['int_attendu'][$key] += $row[2];
    }
  }
  $dbHandler->closeConnection(true);
  return $retour;
}
function get_prevision_epargne($devise = NULL) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  // Epargne libre
  $sql = "SELECT sum(solde) FROM ad_cpt WHERE id_ag = $global_id_agence AND (id_prod = 1 OR id_prod > 4) AND (dat_date_fin IS NULL) ";
  $sql .= " AND devise='" . $devise . "'";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row = $result->fetchrow();
  if ($row[0] == '')
    $row[0] = 0;
  $retour['ep_libre']['j'] = $row[0];
  $last_key = 'j';
  //Récupère pour chaque tranche de date les épargnes
  $dates = get_dates();
  reset($dates);
  while (list ($key, $value) = each($dates)) {
    //Pour chaque tranche de dates
    //Epargne nantie
    $sql = " SELECT sum(a.solde) FROM ad_cpt a, ad_dcr b, ad_etr c, ad_gar d WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = d.id_ag AND d.id_ag = $global_id_agence AND a.devise = '$devise' AND (a.id_cpte = d.gar_num_id_cpte_nantie) AND (b.id_doss = d.id_doss) AND (b.id_doss = c.id_doss) AND (c.id_ech IN (SELECT max(id_ech) FROM ad_etr WHERE id_ag = $global_id_agence AND (date_ech > '$value') AND (id_doss = b.id_doss)))";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    if ($row[0] == '')
      $row[0] = 0;
    $retour["ep_nantie"][$key] = $row[0];
    //Epargne a terme
    $sql = "SELECT SUM(solde) FROM ad_cpt WHERE id_ag = $global_id_agence AND devise = '$devise' AND (dat_date_fin IS NOT NULL) AND (dat_date_fin > '$value')";
    $ep_terme = array ();
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    if ($row[0] == '')
      $row[0] = 0;
    $retour["ep_terme"][$key] = $row[0];
    //Epargne libre
    if ($key != 'j') {
      $retour["ep_libre"][$key] = $retour["ep_libre"][$last_key] + ($retour["ep_nantie"][$last_key] - $retour["ep_nantie"][$key]) + ($retour["ep_terme"][$last_key] - $retour["ep_terme"][$key]);
      $last_key = $key;
    }
  }
  $dbHandler->closeConnection(true);
  return $retour;
}

function getSoldesCptBase($idmin, $idmax)
// PS qui eznvoie la liste des soldes des comptes de base des clients, éventuellement filtrés par $idmin (id_client min) et $idmax (id_client max)
{
  global $dbHandler;
  global $global_id_agence;

  // Recherche du montant min sur le compte de base
  $idProdCptBase = getBaseProductID($global_id_agence);
  $PROD = getProdEpargne($idProdCptBase);
  $ACC = getAccountDatas($idProdCptBase);
  $mntMin = $ACC['mnt_min_cpte'];
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_client, anc_id_client, solde FROM ad_cli a, ad_cpt b WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence AND a.id_cpte_base = b.id_cpte AND b.solde > 0";
  if ($idmin)
    $sql .= " AND a.id_client >= $idmin";
  if ($idmax)
    $sql .= " AND a.id_client <= $idmax";
  $sql .= " ORDER BY a.id_client";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $DATA = array ();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $info["id_client"] = sprintf("%06d", $tmprow["id_client"]);
    $info["anc_id_client"] = $tmprow["anc_id_client"];
    $solde = $tmprow["solde"];
    $info["solde"] = recupMontant($solde);
    $info["nom"] = getClientName($info["id_client"]);
    array_push($DATA, $info);
  }
  $dbHandler->closeConnection(true);
  return $DATA;
}
function getSoldesCptBaseDate($date) { // PS qui envoie la liste des soldes des comptes de base des clients, dont le dernier mouvement a été effectué à la date $date
  global $dbHandler;
  global $global_id_agence;
  // Recherche du montant min sur le compte de base
  $idProdCptBase = getBaseProductID($global_id_agence);
  $PROD = getProdEpargne($idProdCptBase);
  $ACC = getAccountDatas($idProdCptBase);
  $mntMin = $ACC['mnt_min_cpte'];
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_client, anc_id_client, id_cpte, solde, mnt_bloq, mnt_bloq_cre FROM ad_cli a, ad_cpt b WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence AND a.id_cpte_base = b.id_cpte AND b.solde > 0 ORDER BY id_client";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $DATA = array ();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    // Recherche de la date du dernier mouvement
    $sql = "SELECT ecr.date_comptable FROM ad_ecriture ecr, ad_mouvement mvt WHERE ecr.id_ag = mvt.id_ag AND mvt.id_ag = $global_id_agence AND mvt.cpte_interne_cli = '" . $tmprow["id_cpte"] . "' AND ecr.id_ecriture = mvt.id_ecriture ORDER BY ecr.date_comptable DESC";
    $result2 = $db->query($sql);
    if (DB :: isError($result2)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__); // $result2->getMessage()
    }
    $tmprow2 = $result2->fetchrow();
    if (pg2phpDate($tmprow2[0]) == $date) { // Si cette date est celle du rapport
      $info["id_client"] = sprintf("%06d", $tmprow["id_client"]);
      $info["anc_id_client"] = $tmprow["anc_id_client"];
      $solde = $tmprow["solde"] - $tmprow["mnt_bloq"] - $tmprow["mnt_bloq_cre"] - $mntMin;
      if ($solde < 0)
        $solde = 0;
      $info["solde"] = afficheMontant($solde);
      $info["nom"] = getClientName($info["id_client"]);
      array_push($DATA, $info);
    }
  }
  $dbHandler->closeConnection(true);
  return $DATA;
}

function getSoldescptbasedatebis($date, $gestionnaire = 0) {

  global $dbHandler;
  global $global_id_agence;

  // Recherche du montant min sur le compte de base
  $db = $dbHandler->openConnection();
 	if ($gestionnaire > 0){
 	  $sql = "SELECT distinct (c.id_titulaire), c.num_complet_cpte, c.solde, c.mnt_bloq, c.mnt_min_cpte, c.mnt_bloq_cre ";
 	  $sql .= " FROM ad_cpt c, ad_mouvement m, ad_ecriture e, ad_cli cli ";
 	  $sql .= " WHERE c.id_prod <> 2 AND c.id_prod <> 3 AND c.id_cpte = m.cpte_interne_cli AND m.id_ecriture = e.id_ecriture ";
 	  $sql .= " AND date(e.date_comptable) = '$date' AND cli.id_client = c.id_titulaire AND cli.gestionnaire = $gestionnaire ";
 	  $sql .= " AND c.id_ag = m.id_ag and m.id_ag = e.id_ag and e.id_ag = cli.id_ag AND cli.id_ag = $global_id_agence ";
 	  $sql .= " ORDER BY c.id_titulaire ";
 	} else {
 	  $sql = "SELECT distinct (c.id_titulaire), c.num_complet_cpte, c.solde, c.mnt_bloq, c.mnt_min_cpte, c.mnt_bloq_cre ";
 	  $sql .= " FROM ad_cpt c, ad_mouvement m, ad_ecriture e, ad_cli cli ";
 	  $sql .= " WHERE c.id_prod <> 2 AND c.id_prod <> 3 AND c.id_cpte = m.cpte_interne_cli AND m.id_ecriture = e.id_ecriture ";
 	  $sql .= " AND date(e.date_comptable) = '$date' AND cli.id_client = c.id_titulaire "; //AND cli.gestionnaire = $gestionnaire ";
 	  $sql .= " AND c.id_ag = m.id_ag and m.id_ag = e.id_ag and e.id_ag = cli.id_ag AND cli.id_ag = $global_id_agence ";
 	  $sql .= " ORDER BY c.id_titulaire ";
 	}
 	$result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $DATA = array ();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $info["id_client"] = $tmprow['id_titulaire'];
 	  $info["num_complet_cpte"] = $tmprow['num_complet_cpte'];
 	  $solde = $tmprow['solde'] - $tmprow['mnt_bloq'] - $tmprow['mnt_min_cpte'] - $tmprow['mnt_bloq_cre'];
    if ($solde < 0)
      $solde = 0;
    $info["solde"] = $solde;
    $info["nom"] = getClientName($info["id_client"]);
    array_push($DATA, $info);
  }
  $dbHandler->closeConnection(true);
  return $DATA;
}
/**
 * getSoldeCpteEpargne renvoie la liste des soldes des comptes liés à un produit d'épargne entrée en paramètre des clients, éventuellement filtrés par $idmin (id_client min) et $idmax (id_client max)
 */
function getSoldeCpteEpargne($idProd, $idmin, $idmax,$date_deb,$n_agence = null,$n_guichet = null) {
  global $dbHandler;
  global $global_id_agence;

  // Recherche du montant min sur le compte de base
  $PROD = getProdEpargne($idProd);
  $ACC = getAccountDatas($idProd);
  $mntMin = $ACC['mnt_min_cpte'];
  $db = $dbHandler->openConnection();

  if($date_deb=='' or $date_deb ==null)
  {
    $sql = " SELECT id_client, anc_id_client, id_cpte, num_complet_cpte, solde FROM ad_cli a, ad_cpt b WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence AND a.id_client = b.id_titulaire AND b.id_prod = $idProd AND b.etat_cpte != 2 ";
  }
  else
  {
    $sql = "SELECT id_client, anc_id_client, b.id_cpte, num_complet_cpte, calculsoldecpte(b.id_cpte,null,date('$date_deb')) as solde FROM ad_cli a inner join ad_cpt b on a.id_ag = b.id_ag AND b.id_ag = $global_id_agence and a.id_client = b.id_titulaire where b.id_prod = $idProd and date_ouvert <= DATE ('$date_deb') and  ( calculetatcpte_epargne_hist(numagc(), b.id_cpte, date('$date_deb')))!=2";
    /*$sql = " SELECT DISTINCT id_client, anc_id_client, b.id_cpte, num_complet_cpte, calculsoldecpte(b.id_cpte,null,date('$date_deb')) as solde
              FROM ad_cli a, ad_cpt b, ad_cpt_hist c
              WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence
              AND b.id_cpte = c.id_cpte
              AND a.id_client = b.id_titulaire AND b.id_prod = $idProd
              AND (b.etat_cpte != 2 or (c.etat_cpte = 1 and c.date_action >= date('$date_deb') and b.date_clot >= date('$date_deb')))
              AND date_ouvert <= DATE ('$date_deb') ";*/
  }

  // Le solde est moins de 0 quand le sens du produit est débiteur #345
  if(strtolower(trim($PROD['sens'])) == 'd') {
      $sql .= " AND b.solde <= 0 ";
  }
  else {
      $sql .= " AND b.solde >= 0 ";
  }

  if (($idmin) && ($idmax)) {
    $sql .= " AND a.id_client BETWEEN $idmin AND $idmax";
  }
  if ($n_agence != null){
    $sql .= " AND a.n_agence = $n_agence";
  }
  if ($n_guichet != null){
    $sql .= " AND a.n_guichet = $n_guichet";
  }

  $sql .= " ORDER BY a.id_client";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $DATA = array ();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $info["id_client"] = sprintf("%06d", $tmprow["id_client"]);
    $info["anc_id_client"] = $tmprow["anc_id_client"];
    $info["num_complet_cpte"] = $tmprow["num_complet_cpte"];
    $info["id_cpte"] = $tmprow["id_cpte"];
    $solde = recupMontant(abs($tmprow["solde"]));
    $info["solde"] = $solde==null?"0":$solde;
    $info["nom"] = getClientName($info["id_client"]);
    array_push($DATA, $info);
  }
  $dbHandler->closeConnection(true);
  return $DATA;
}
/**
 * getBalanceComptable Renvoie les données sur les mouvements des comptes comptables pour une période donnée
 *
 * @param mixed $date_deb Date du début de période
 * @param mixed $date_fin Date de fin de période
 * @param mixed $devise La devise pour laquelle on fait la balance ou NULL pour faire une balance sur toutes les devises (également dans le cas mono-devise)
 * @param array $liste_ag  Liste des agences à imprimer les données
 * @param bool $consolide si on veut des etats consolidés
 * @access public
 * @return array array ("num_cpte" => array("libel", "solde_deb", "total_debits", "total_credits", "solde_fin"))
 * NOTE: Les soldes fournis sont les soldes début de journée pour $date_deb
 */


//function getBalanceComptable($date_deb, $date_fin, $devise = NULL, $liste_ag, $niveau = NULL,$consolide=NULL) {
//  global $dbHandler;
//  global $global_multidevise, $global_id_agence;
//  global $global_monnaie;
//  $db = $dbHandler->openConnection();
//  // Initialisation des tableaux
//  $DATA = array ();
//   $DATAD = array ();
//  $array_devise = array ();
//  if (($global_multidevise) && ($devise != NULL))
//    array_push($array_devise, $devise);
//  else
//    if (($global_multidevise) && ($devise == NULL)) {
//      $different_devise = get_table_devises();
//      foreach ($different_devise as $key => $value)
//      array_push($array_devise, $key);
//    } else
//      array_push($array_devise, $global_monnaie);
//
//  // Initialisation des totaux
//  foreach ($array_devise as $key => $la_devise) {
//
//    $total_mouvements_deb[$la_devise] = 0;
//    $total_mouvements_cre[$la_devise] = 0;
//    //parcours des agences
//    foreach($liste_ag as $id_agence=>$libel_agence) {
//    	 // on travaille avec cette agence
//    setGlobalIdAgence($id_agence);
//    $sql = "SELECT num_cpte_comptable,libel_cpte_comptable,is_hors_bilan,niveau FROM ad_cpt_comptable WHERE id_ag = $global_id_agence " ;
//      // calcul des soldes des comptes comptables
//    if($date_deb == NULL){
//      $condSousComptes=" and is_actif = 't'  ";
//    }else{
//     $date_debut= php2pg($date_deb);
//     $condSousComptes=" and (is_actif = 't' OR (is_actif = 'f' and date_modif > '$date_debut')) ";
//    }
//    $condSousComptes.= "AND( devise ='" .	$la_devise . "' OR devise IS NULL) ";
//    $sql .= $condSousComptes;
//    $sql .= " ORDER BY num_cpte_comptable ";
//    $result = $db->query($sql);
//    if (DB :: isError($result)) {
//      $dbHandler->closeConnection(false);
//      signalErreur(__FILE__, __LINE__, __FUNCTION__);
//    }
//
//    while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
//    	$isCpteBalance=false;//si c'est un compte qui doit etre present dans la balance'
//    	$isCalculNonRecursif=true;//si le solde du compte sera calculé non recursivement
//
//      if (($tmprow["niveau"] <= $niveau || $niveau == NULL) && ($tmprow["is_hors_bilan"]== 'f')) {
//        $num_cpte = $tmprow["num_cpte_comptable"];
//        // Calcul du solde du compte à la veille de la date de début
//        if(($tmprow["niveau"] == $niveau)&&  isCentralisateur($tmprow["num_cpte_comptable"])){//si c'est cpte centralisateur etle niveau du cpte  est egal au niveau specifié , calculer le solde et sommes des mvts recursivement(les sous comptes)
//        	$solde_debut = calculSoldeRecursif($num_cpte, hier($date_deb),$consolide,$condSousComptes);
//        	if((existeMouvementRecursif($num_cpte, $date_deb, $date_fin,$consolide,$condSousComptes) || $solde_debut <> 0)){
//        		 $isCpteBalance=true;
//        		 $isCalculNonRecursif=false;
//        	}
//        }else{
//        	$solde_debut = calculSoldeNonRecursif($num_cpte, hier($date_deb),$consolide);
//        	if((existeMouvement($num_cpte, $date_deb, $date_fin,$consolide) || $solde_debut <> 0))
//          $isCpteBalance=true;
//        }
//
//        if ( $isCpteBalance) {
//
//            if (!isset($DATA[$la_devise][$num_cpte])){
//          	  $DATA[$la_devise][$num_cpte]["libel"] = $tmprow["libel_cpte_comptable"]; // Ajout du libellé si ne l'est pas encore
//          		$DATA[$la_devise][$num_cpte]["solde_debut"] = 0;
//            	$DATA[$la_devise][$num_cpte]["solde_fin"] = 0;
//              $DATA[$la_devise][$num_cpte]["total_debits"] =0;
//              $DATA[$la_devise][$num_cpte]["total_credits"] = 0;
//            }
//            $DATA[$la_devise][$num_cpte]["solde_debut"]+= $solde_debut;
//        	// Calcul du solde du compte à la date de fin/calcul des mvts au debit et au crédit entre la date_deb et date_fin
//          	if( $isCalculNonRecursif){
//          		$solde_fin = calculSoldeNonRecursif($num_cpte, $date_fin,$consolide);
//          		$somMvtDeb=calculeSommeMvtCpte($num_cpte,$date_deb,$date_fin,"d",$consolide);
//          		$somMvtCre=calculeSommeMvtCpte($num_cpte,$date_deb,$date_fin,"c",$consolide);
//          	}else{
//              $solde_fin = calculSoldeRecursif($num_cpte, $date_fin,$consolide,$condSousComptes);
//              $somMvtDeb=calculeSommeMvtCpteRecursif($num_cpte,$date_deb,$date_fin,"d",$consolide,$condSousComptes);
//              $somMvtCre=calculeSommeMvtCpteRecursif($num_cpte,$date_deb,$date_fin,"c",$consolide,$condSousComptes);
//          	}
//          	$DATA[$la_devise][$num_cpte]["solde_fin"] += $solde_fin;
//            $DATA[$la_devise][$num_cpte]["total_debits"] += $somMvtDeb;
//            $DATA[$la_devise][$num_cpte]["total_credits"] += $somMvtCre;
//
//        } // fin si compte present dans la balance
//      }
//    } // fin parcours des comptes
//
//    resetGlobalIdAgence();
//
//    }//fin parcours agences
//   ksort($DATA[$la_devise]);
//  }// Parcours des devises
//
//  $dbHandler->closeConnection(true);
//  return $DATA;
//}

/**
 * getBalanceComptable Renvoie les données sur les mouvements des comptes comptables pour une période donnée
 *
 * @author Ibou Ndiaye
 * @version 3.2.2
 * @param mixed $date_deb Date du début de période
 * @param mixed $date_fin Date de fin de période
 * @param mixed $devise La devise pour laquelle on fait la balance ou NULL pour faire une balance sur toutes les devises (également dans le cas mono-devise)
 * @param array $liste_ag  Liste des agences à imprimer les données
 * @param bool $consolide si on veut des etats consolidés
 * @access public
 * @return array array ("num_cpte" => array("libel", "solde_deb", "total_debits", "total_credits", "solde_fin"))
 * NOTE: Les soldes fournis sont les soldes début de journée pour $date_deb
 */


function getBalanceComptable($date_deb, $date_fin, $devise = NULL, $liste_ag, $niveau = NULL,$consolide=NULL) {
	global $dbHandler;
	global $global_multidevise, $global_id_agence;
	global $global_monnaie;
	$db = $dbHandler->openConnection();
	// Initialisation des tableaux
	$DATA = array ();
	$DATAD = array ();
	$array_devise = array ();
	if (($global_multidevise) && ($devise != NULL))
	array_push($array_devise, $devise);
	else
	if (($global_multidevise) && ($devise == NULL)) {
		$different_devise = get_table_devises();
		foreach ($different_devise as $key => $value)
		array_push($array_devise, $key);
	} else
	array_push($array_devise, $global_monnaie);

	// Initialisation des totaux
	foreach ($array_devise as $key => $la_devise) {

		$total_mouvements_deb[$la_devise] = 0;
		$total_mouvements_cre[$la_devise] = 0;
		//parcours des agences
		foreach($liste_ag as $id_agence=>$libel_agence) {
			// on travaille avec cette agence
			setGlobalIdAgence($id_agence);
			$options =  $niveau ? ', '.$niveau: ', 0';
			$options .=  $consolide ? ', '.$consolide: ', false';
			$sql = "SELECT num_cpte_comptable,libel_cpte_comptable,solde_debut,som_mvt_debit,som_mvt_credit,solde_fin,is_hors_bilan,niveau FROM getBalanceView(date('$date_deb'), date('$date_fin'), $global_id_agence".$options.") WHERE id_ag = $global_id_agence " ;
			// calcul des soldes des comptes comptables
			if($date_deb == NULL){
				$condSousComptes=" and is_actif = 't'  ";
			}else{
				$date_debut= php2pg($date_deb);
				$condSousComptes=" and (is_actif = 't' OR (is_actif = 'f' and date_modif > '$date_debut')) ";
			}
			$condSousComptes.= "AND( devise ='" .	$la_devise . "' OR devise IS NULL) ";
			$sql .= $condSousComptes;
			$result = $db->query($sql);
			if (DB :: isError($result)) {
				$dbHandler->closeConnection(false);
				signalErreur(__FILE__, __LINE__, __FUNCTION__);
			}

			while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
				$isCpteBalance=false;//si c'est un compte qui doit etre present dans la balance'
				$isCalculNonRecursif=true;//si le solde du compte sera calculé non recursivement

				if (($tmprow["niveau"] <= $niveau || $niveau == NULL) && ($tmprow["is_hors_bilan"]== 'f')) {
					$num_cpte = $tmprow["num_cpte_comptable"];
					 
					$solde_debut = $tmprow["solde_debut"];
					// if ( $isCpteBalance) {

					if (!isset($DATA[$la_devise][$num_cpte])){
						$DATA[$la_devise][$num_cpte]["libel"] = $tmprow["libel_cpte_comptable"]; // Ajout du libellé si ne l'est pas encore
						$DATA[$la_devise][$num_cpte]["solde_debut"] = 0;
						$DATA[$la_devise][$num_cpte]["solde_fin"] = 0;
						$DATA[$la_devise][$num_cpte]["total_debits"] =0;
						$DATA[$la_devise][$num_cpte]["total_credits"] = 0;
					}
					$DATA[$la_devise][$num_cpte]["solde_debut"]+= $solde_debut;
					// Calcul du solde du compte à la date de fin/calcul des mvts au debit et au crédit entre la date_deb et date_fin
					//if( $isCalculNonRecursif){
					$solde_fin = $tmprow["solde_fin"];
					$somMvtDeb=$tmprow["som_mvt_debit"];
					$somMvtCre=$tmprow["som_mvt_credit"];
					//          	}else{
					//              $solde_fin = calculSoldeRecursif($num_cpte, $date_fin,$consolide,$condSousComptes);
					//              $somMvtDeb=calculeSommeMvtCpteRecursif($num_cpte,$date_deb,$date_fin,"d",$consolide,$condSousComptes);
					//              $somMvtCre=calculeSommeMvtCpteRecursif($num_cpte,$date_deb,$date_fin,"c",$consolide,$condSousComptes);
					//          	}
					$DATA[$la_devise][$num_cpte]["solde_fin"] += $solde_fin;
					$DATA[$la_devise][$num_cpte]["total_debits"] += $somMvtDeb;
					$DATA[$la_devise][$num_cpte]["total_credits"] += $somMvtCre;

					// } // fin si compte present dans la balance
					}
				} // fin parcours des comptes

				resetGlobalIdAgence();

			}//fin parcours agences
			ksort($DATA[$la_devise]);
		}// Parcours des devises

		$dbHandler->closeConnection(true);
		return $DATA;
	}


/**
 * Function getListeClientComptes permet de récupèrer les informations concernant
 * tous les client sousr forme de tableau indéxé par id_client
 * return array $DATA un tableau de clients
 */
function getListeClientComptes() {
  global $dbHandler;
  global $global_id_agence;
  global $global_monnaie;

  $sql = "SELECT cl.id_client, cl.ville, cl.adresse,cl. email,cl. pp_date_naissance, cl.pp_sexe, cl.num_tel, cl.num_fax, cl.num_port, cl.pp_nom, cl.pp_prenom, cpt.num_complet_cpte, py.libel_pays, tp.libel, cl.pp_nm_piece_id ";
  $sql .= " FROM ad_cpt cpt, (ad_cli cl LEFT OUTER JOIN adsys_pays py ON cl.pays = py.id_pays AND cl.id_ag = py.id_ag AND cl.id_ag = $global_id_agence) ";
  $sql .= " LEFT OUTER JOIN adsys_type_piece_identite tp ON cl.pp_type_piece_id = tp.id AND cl.id_ag = tp.id_ag AND cl.id_ag = $global_id_agence ";
  $sql .= " WHERE cl.id_client = cpt.id_titulaire AND cpt.id_prod = 1 AND cl.id_ag = $global_id_agence AND cl.id_ag = cpt.id_ag ORDER BY id_client;";

  return executeDirectQuery($sql);
}
/**
 * Rapport « Liste de sociètaires de l'insitution" partie 1 du rapports
 * @author Kheshan A.G
 * @return Array $DATAS Tableau de données à afficher sur la rapport liste de societaires de l'insitution
 */
function getListeSocietaires($statut_jur = 0, $export_date) {
  // Fonction renvoyant la liste de tous les sociétaires de l'institution
  // IN: Pas de paramètres
  // OUT: Tablea associatif : ["details"] => array("stat_jur" => array ("id_client", "nom", "nbre_parts"))
  //                          ["total_stat_jur"] => array ("stat_jur" => array("total_soc", "total_ps"))
  //                          ["total_ps"] => Nmbre total de parts
  //                          ["capital_social"] => Capital social
  //                          ["nbre_societaires"] => Nombre de sociétaires
  global $dbHandler;
  global $global_id_agence;
  global $global_monnaie;

  $db = $dbHandler->openConnection();
  // Récupère valeur nominale d'une part sociale
  $sql = "SELECT val_nominale_part_sociale FROM ad_agc WHERE id_ag=$global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmprow = $result->fetchrow();
  $val_nom_ps = $tmprow[0];
  $idProdPS = getPSProductID($global_id_agence);
 
 //recuperation des infos des societaire basant sur l'historique(ad_part_sociale_his)
    if ($statut_jur <= 0){// si statut juridique = tous
    $sql = "SELECT  test3.id_client as id_client,statut_juridique,pp_nom,pp_prenom,gi_nom, pm_raison_sociale,qualite,nbre_ps_souscrite as nbre_parts,nbre_ps_lib as nbre_parts_lib,solde_ps_lib as solde_ps,solde_ps_restant as solde_restant FROM ad_cli AS test2 inner join(
            
            SELECT  test1.id_client ,nbre_ps_souscrite,nbre_ps_lib,solde_ps_lib,solde_ps_restant FROM ad_part_sociale_his AS test inner join(
            SELECT a.id_client,MAX(a.date_his) As Maxdate_his 
            FROM ad_part_sociale_his a ,ad_cli b WHERE a.id_client =b.id_client and date(a.date_his) <= '$export_date' and b.etat = 2 and a.qualite>1 and b.qualite >1 
            GROUP BY a.id_client) 
            as test1 on test.id_client = test1.id_client and test.date_his = test1.Maxdate_his order by test1.Maxdate_his desc)

            as test3 on test2.id_client = test3.id_client and nbre_ps_lib > 0 and solde_ps_lib > 0 order by test3.id_client ;";
    }else{
    	$sql = "SELECT  test3.id_client as id_client,statut_juridique,pp_nom,pp_prenom,gi_nom, pm_raison_sociale,qualite,nbre_ps_souscrite as nbre_parts,nbre_ps_lib as nbre_parts_lib,solde_ps_lib as solde_ps,solde_ps_restant as solde_restant FROM ad_cli AS test2 inner join(
    	
    	SELECT  test1.id_client ,nbre_ps_souscrite,nbre_ps_lib,solde_ps_lib,solde_ps_restant FROM ad_part_sociale_his AS test inner join(
    	SELECT a.id_client,MAX(a.date_his) As Maxdate_his
    	FROM ad_part_sociale_his a ,ad_cli b WHERE a.id_client =b.id_client and b.statut_juridique=$statut_jur and date(a.date_his) <= '$export_date' and b.etat = 2 and a.qualite>1 and b.qualite >1 
    	GROUP BY a.id_client)
    	as test1 on test.id_client = test1.id_client and test.date_his = test1.Maxdate_his order by test1.Maxdate_his desc)
    	
    	as test3 on test2.id_client = test3.id_client and nbre_ps_lib > 0 and solde_ps_lib > 0 order by test3.id_client ;";
    	 
    }

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $DATA = array ();
  $DATA["details"] = array ("pp" => array (), "pm" => array (), "gi" => array (), "gs" => array ());
  $DATA["total_stat_jur"] = array ("pp" => array (), "pm" => array (), "gi" => array (), "gs" => array ());
  $solde_part_sociale = 0;

  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC))
  {
    if ($tmprow["statut_juridique"] == 1) {//personne physique
      $infos = array (
                 "id_client" => $tmprow["id_client"],
                 "nom" => $tmprow["pp_prenom"] . " " . $tmprow["pp_nom"],
                 "nbre_parts" => $tmprow["nbre_parts"],
      		     "nbre_parts_lib" => $tmprow["nbre_parts_lib"],
      		     "soldePSSouscrites" => ($tmprow["nbre_parts"] * $val_nom_ps),    
      		     "soldePSLib" => $tmprow["solde_ps"],      
      		     "soldePSRestant" => (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"])
               );
      $DATA["details"]["pp"][$tmprow["id_client"]] = $infos;
      $DATA["total_stat_jur"]["pp"]["total_soc"]++;
      $DATA["total_stat_jur"]["pp"]["total_ps"] += $tmprow["nbre_parts"];
      $DATA["total_stat_jur"]["pp"]["total_ps_lib"] += $tmprow["nbre_parts_lib"]; 
      $DATA["total_stat_jur"]["pp"]["total_soldePS_sous"] += ($tmprow["nbre_parts"] * $val_nom_ps);
      $DATA["total_stat_jur"]["pp"]["total_soldePS_lib"] += $tmprow["solde_ps"];
      $DATA["total_stat_jur"]["pp"]["total_soldePS_restant"] += (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"]);
      }
      else
      if ($tmprow["statut_juridique"] == 2) {//personne morale
        $infos = array (
                   "id_client" => $tmprow["id_client"],
                   "nom" => $tmprow["pm_raison_sociale"],
                   "nbre_parts" => $tmprow["nbre_parts"],
        		   "nbre_parts_lib" => $tmprow["nbre_parts_lib"],
        		   "soldePSSouscrites" => ($tmprow["nbre_parts"] * $val_nom_ps),
        		   "soldePSLib" => $tmprow["solde_ps"] ,
        		   "soldePSRestant" => (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"])
                 );
        $DATA["details"]["pm"][$tmprow["id_client"]] = $infos;
        $DATA["total_stat_jur"]["pm"]["total_soc"]++;
        $DATA["total_stat_jur"]["pm"]["total_ps"] += $tmprow["nbre_parts"];
        $DATA["total_stat_jur"]["pm"]["total_ps_lib"] += $tmprow["nbre_parts_lib"]; 
        $DATA["total_stat_jur"]["pm"]["total_soldePS_sous"] += ($tmprow["nbre_parts"] * $val_nom_ps);
        $DATA["total_stat_jur"]["pm"]["total_soldePS_lib"] += $tmprow["solde_ps"] ;
        $DATA["total_stat_jur"]["pm"]["total_soldePS_restant"] += (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"]);
      } else
        if ($tmprow["statut_juridique"] == 3) { //group informelle
          $infos = array (
                     "id_client" => $tmprow["id_client"],
                     "nom" => $tmprow["gi_nom"],
                     "nbre_parts" => $tmprow["nbre_parts"],
          		     "nbre_parts_lib" => $tmprow["nbre_parts_lib"], 
          		     "soldePSSouscrites" => ($tmprow["nbre_parts"] * $val_nom_ps),
          		     "soldePSLib" => $tmprow["solde_ps"],
          	         "soldePSRestant" => (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"])
                   );
          $DATA["details"]["gi"][$tmprow["id_client"]] = $infos;
          $DATA["total_stat_jur"]["gi"]["total_soc"]++;
          $DATA["total_stat_jur"]["gi"]["total_ps"] += $tmprow["nbre_parts"];
          $DATA["total_stat_jur"]["gi"]["total_ps_lib"] += $tmprow["nbre_parts_lib"]; 
          $DATA["total_stat_jur"]["gi"]["total_soldePS_sous"] += ($tmprow["nbre_parts"] * $val_nom_ps);
          $DATA["total_stat_jur"]["gi"]["total_soldePS_lib"] += $tmprow["solde_ps"] ;
          $DATA["total_stat_jur"]["gi"]["total_soldePS_restant"] += (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"]);
        } else
          if ($tmprow["statut_juridique"] == 4) {//group solidaire
            $infos = array (
                       "id_client" => $tmprow["id_client"],
                       "nom" => $tmprow["gi_nom"],
                       "nbre_parts" => $tmprow["nbre_parts"],
            		   "nbre_parts_lib" => $tmprow["nbre_parts_lib"], 
            		   "soldePSSouscrites" => ($tmprow["nbre_parts"] * $val_nom_ps),
            		   "soldePSLib" => $tmprow["solde_ps"], 
            		   "soldePSRestant" => (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"])
                     );
            $DATA["details"]["gs"][$tmprow["id_client"]] = $infos;
            $DATA["total_stat_jur"]["gs"]["total_soc"]++;
            $DATA["total_stat_jur"]["gs"]["total_ps"] += $tmprow["nbre_parts"];
            $DATA["total_stat_jur"]["gs"]["total_ps_lib"] += $tmprow["nbre_parts_lib"]; 
            $DATA["total_stat_jur"]["gs"]["total_soldePS_sous"] += ($tmprow["nbre_parts"] * $val_nom_ps);
            $DATA["total_stat_jur"]["gs"]["total_soldePS_lib"] += $tmprow["solde_ps"] ;
            $DATA["total_stat_jur"]["gs"]["total_soldePS_restant"] += (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"]);
          }
     $soldePartSoc = getSoldePartSoc($tmprow["id_client"]);
    $solde_part_sociale += $soldePartSoc->param[0]['solde'];
    $solde_part_soc_restant +=$soldePartSoc->param[0]['solde_part_soc_restant']; 
    
  }
//LES INFO GLOBALES
  $DATA["total_ps"] = $DATA["total_stat_jur"]["pm"]["total_ps"] + $DATA["total_stat_jur"]["pp"]["total_ps"] + $DATA["total_stat_jur"]["gi"]["total_ps"] + $DATA["total_stat_jur"]["gs"]["total_ps"];
  $DATA["total_ps_lib"] = $DATA["total_stat_jur"]["pm"]["total_ps_lib"] + $DATA["total_stat_jur"]["pp"]["total_ps_lib"] + $DATA["total_stat_jur"]["gi"]["total_ps_lib"] + $DATA["total_stat_jur"]["gs"]["total_ps_lib"];
  $DATA["nbre_societaires"] = $result->numrows();
  $DATA["capital_social"] = $solde_part_sociale;// pas utilisé. a eter remplacer par cap sociale_lib
  $DATA["capital_social_souscrites"] = $DATA["total_stat_jur"]["pp"]["total_soldePS_sous"] + $DATA["total_stat_jur"]["pm"]["total_soldePS_sous"] + $DATA["total_stat_jur"]["gi"]["total_soldePS_sous"] + $DATA["total_stat_jur"]["gs"]["total_soldePS_sous"] ;
  $DATA["capital_social_lib"] =  $DATA["total_stat_jur"]["pp"]["total_soldePS_lib"] + $DATA["total_stat_jur"]["pm"]["total_soldePS_lib"] + $DATA["total_stat_jur"]["gi"]["total_soldePS_lib"] + $DATA["total_stat_jur"]["gs"]["total_soldePS_lib"];
  $DATA["capital_social_restant"] =  $DATA["total_stat_jur"]["pp"]["total_soldePS_restant"] + $DATA["total_stat_jur"]["pm"]["total_soldePS_restant"] + $DATA["total_stat_jur"]["gi"]["total_soldePS_restant"] + $DATA["total_stat_jur"]["gs"]["total_soldePS_restant"];
  $DATA["valnomps"] = $val_nom_ps;
  
  
  return $DATA;
}
/**
 * Rapport « Complément liste des sociétaires " partie 2 du rapports
 * @author Kheshan A.G
 * @return Array $DATAS Tableau de données à afficher sur la rapport liste de societaires 
 */
 function getListeSocietaires_tranche($statut_jur = 0, $export_date) {
	// Fonction renvoyant la liste de tous les sociétaires de l'institution
	// IN: Pas de paramètres
	// OUT: Tablea associatif : ["details"] => array("stat_jur" => array ("id_client", "nom", "nbre_parts"))
	//                          ["total_stat_jur"] => array ("stat_jur" => array("total_soc", "total_ps"))
	//                          ["total_ps"] => Nmbre total de parts
	//                          ["capital_social"] => Capital social
	//                          ["nbre_societaires"] => Nombre de sociétaires
	global $dbHandler;
	global $global_id_agence;
	global $global_monnaie;
	

	$db = $dbHandler->openConnection();
	// Récupère valeur nominale d'une part sociale
	$sql = "SELECT val_nominale_part_sociale FROM ad_agc WHERE id_ag=$global_id_agence";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$tmprow = $result->fetchrow();
	$val_nom_ps = $tmprow[0];
	$idProdPS = getPSProductID($global_id_agence);

	//recuperation des infos des societaire basant sur l'historique(ad_part_sociale_his)
	if ($statut_jur <= 0){// si statut juridique = tous
		$sql = "SELECT  test3.date_his,test3.id_client as id_client,statut_juridique,pp_nom,pp_prenom,gi_nom, pm_raison_sociale,qualite,nbre_ps_souscrite as nbre_parts,nbre_ps_lib as nbre_parts_lib,solde_ps_lib as solde_ps,solde_ps_restant as solde_restant FROM ad_cli AS test2 inner join(

		SELECT  test1.id_client ,nbre_ps_souscrite,nbre_ps_lib,solde_ps_lib,solde_ps_restant,date_his FROM ad_part_sociale_his AS test inner join(
		SELECT a.id_client,MAX(a.date_his) As Maxdate_his
		FROM ad_part_sociale_his a ,ad_cli b WHERE a.id_client =b.id_client and date(a.date_his) <= '$export_date' and b.etat = 2 
		GROUP BY a.id_client)
		as test1 on test.id_client = test1.id_client and test.date_his = test1.Maxdate_his order by test1.Maxdate_his desc)

		as test3 on test2.id_client = test3.id_client and ((test3.solde_ps_lib /$val_nom_ps)>0 AND (test3.solde_ps_lib / $val_nom_ps)<1 )  order by test3.id_client ;";
	}else{
		$sql = "SELECT  test3.date_his,test3.id_client as id_client,statut_juridique,pp_nom,pp_prenom,gi_nom, pm_raison_sociale,qualite,nbre_ps_souscrite as nbre_parts,nbre_ps_lib as nbre_parts_lib,solde_ps_lib as solde_ps,solde_ps_restant as solde_restant FROM ad_cli AS test2 inner join(
		 
		SELECT  test1.id_client ,nbre_ps_souscrite,nbre_ps_lib,solde_ps_lib,solde_ps_restant,date_his FROM ad_part_sociale_his AS test inner join(
		SELECT a.id_client,MAX(a.date_his) As Maxdate_his
		FROM ad_part_sociale_his a ,ad_cli b WHERE a.id_client =b.id_client and b.statut_juridique=$statut_jur and date(a.date_his) <= '$export_date' and b.etat = 2 
		GROUP BY a.id_client)
		as test1 on test.id_client = test1.id_client and test.date_his = test1.Maxdate_his order by test1.Maxdate_his desc)
		 
		as test3 on test2.id_client = test3.id_client and ((test3.solde_ps_lib /$val_nom_ps)>0 AND (test3.solde_ps_lib / $val_nom_ps)<1 ) order by test3.id_client ;";

	}
	
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$DATA = array ();
	$DATA["details"] = array ("pp" => array (), "pm" => array (), "gi" => array (), "gs" => array ());
	$DATA["total_stat_jur"] = array ("pp" => array (), "pm" => array (), "gi" => array (), "gs" => array ());
	$solde_part_sociale = 0;
	while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		if ($tmprow["statut_juridique"] == 1) {//personne physique
			$infos = array (
					"id_client" => $tmprow["id_client"],
					"nom" => $tmprow["pp_prenom"] . " " . $tmprow["pp_nom"],
					"nbre_parts" => $tmprow["nbre_parts"],
					"nbre_parts_lib" => $tmprow["nbre_parts_lib"],
					"soldePSSouscrites" => ($tmprow["nbre_parts"] * $val_nom_ps),
					"soldePSLib" => $tmprow["solde_ps"],
					"soldePSRestant" => (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"])
			);
			$DATA["details"]["pp"][$tmprow["id_client"]] = $infos;
			$DATA["total_stat_jur"]["pp"]["total_soc"]++;
			$DATA["total_stat_jur"]["pp"]["total_ps"] += $tmprow["nbre_parts"];
			$DATA["total_stat_jur"]["pp"]["total_ps_lib"] += $tmprow["nbre_parts_lib"];
			$DATA["total_stat_jur"]["pp"]["total_soldePS_sous"] += ($tmprow["nbre_parts"] * $val_nom_ps);
			$DATA["total_stat_jur"]["pp"]["total_soldePS_lib"] += $tmprow["solde_ps"];
			$DATA["total_stat_jur"]["pp"]["total_soldePS_restant"] += (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"]);
		} else
			if ($tmprow["statut_juridique"] == 2) {//personne morale
			$infos = array (
					"id_client" => $tmprow["id_client"],
					"nom" => $tmprow["pm_raison_sociale"],
					"nbre_parts" => $tmprow["nbre_parts"],
					"nbre_parts_lib" => $tmprow["nbre_parts_lib"],
					"soldePSSouscrites" => ($tmprow["nbre_parts"] * $val_nom_ps),
					"soldePSLib" => $tmprow["solde_ps"] ,
					"soldePSRestant" => (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"])
			);
			$DATA["details"]["pm"][$tmprow["id_client"]] = $infos;
			$DATA["total_stat_jur"]["pm"]["total_soc"]++;
			$DATA["total_stat_jur"]["pm"]["total_ps"] += $tmprow["nbre_parts"];
			$DATA["total_stat_jur"]["pm"]["total_ps_lib"] += $tmprow["nbre_parts_lib"];
			$DATA["total_stat_jur"]["pm"]["total_soldePS_sous"] += ($tmprow["nbre_parts"] * $val_nom_ps);
			$DATA["total_stat_jur"]["pm"]["total_soldePS_lib"] += $tmprow["solde_ps"] ;
			$DATA["total_stat_jur"]["pm"]["total_soldePS_restant"] += (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"]);
		} else
			if ($tmprow["statut_juridique"] == 3) { //group informelle
			$infos = array (
					"id_client" => $tmprow["id_client"],
					"nom" => $tmprow["gi_nom"],
					"nbre_parts" => $tmprow["nbre_parts"],
					"nbre_parts_lib" => $tmprow["nbre_parts_lib"],
					"soldePSSouscrites" => ($tmprow["nbre_parts"] * $val_nom_ps),
					"soldePSLib" => $tmprow["solde_ps"],
					"soldePSRestant" => (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"])
			);
			$DATA["details"]["gi"][$tmprow["id_client"]] = $infos;
			$DATA["total_stat_jur"]["gi"]["total_soc"]++;
			$DATA["total_stat_jur"]["gi"]["total_ps"] += $tmprow["nbre_parts"];
			$DATA["total_stat_jur"]["gi"]["total_ps_lib"] += $tmprow["nbre_parts_lib"];
			$DATA["total_stat_jur"]["gi"]["total_soldePS_sous"] += ($tmprow["nbre_parts"] * $val_nom_ps);
			$DATA["total_stat_jur"]["gi"]["total_soldePS_lib"] += $tmprow["solde_ps"] ;
			$DATA["total_stat_jur"]["gi"]["total_soldePS_restant"] += (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"]);
		} else
			if ($tmprow["statut_juridique"] == 4) {//group solidaire
			$infos = array (
					"id_client" => $tmprow["id_client"],
					"nom" => $tmprow["gi_nom"],
					"nbre_parts" => $tmprow["nbre_parts"],
					"nbre_parts_lib" => $tmprow["nbre_parts_lib"],
					"soldePSSouscrites" => ($tmprow["nbre_parts"] * $val_nom_ps),
					"soldePSLib" => $tmprow["solde_ps"],
					"soldePSRestant" => (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"])
			);
			$DATA["details"]["gs"][$tmprow["id_client"]] = $infos;
			$DATA["total_stat_jur"]["gs"]["total_soc"]++;
			$DATA["total_stat_jur"]["gs"]["total_ps"] += $tmprow["nbre_parts"];
			$DATA["total_stat_jur"]["gs"]["total_ps_lib"] += $tmprow["nbre_parts_lib"];
			$DATA["total_stat_jur"]["gs"]["total_soldePS_sous"] += ($tmprow["nbre_parts"] * $val_nom_ps);
			$DATA["total_stat_jur"]["gs"]["total_soldePS_lib"] += $tmprow["solde_ps"] ;
			$DATA["total_stat_jur"]["gs"]["total_soldePS_restant"] += (($tmprow["nbre_parts"] * $val_nom_ps)- $tmprow["solde_ps"]);
		}
		$soldePartSoc = getSoldePartSoc($tmprow["id_client"]);
		$solde_part_sociale += $soldePartSoc->param[0]['solde'];
		$solde_part_soc_restant +=$soldePartSoc->param[0]['solde_part_soc_restant'];

	}
	//LES INFO GLOBALES
	$DATA["total_ps"] = $DATA["total_stat_jur"]["pm"]["total_ps"] + $DATA["total_stat_jur"]["pp"]["total_ps"] + $DATA["total_stat_jur"]["gi"]["total_ps"] + $DATA["total_stat_jur"]["gs"]["total_ps"];
	$DATA["total_ps_lib"] = $DATA["total_stat_jur"]["pm"]["total_ps_lib"] + $DATA["total_stat_jur"]["pp"]["total_ps_lib"] + $DATA["total_stat_jur"]["gi"]["total_ps_lib"] + $DATA["total_stat_jur"]["gs"]["total_ps_lib"];
	$DATA["nbre_societaires"] = $result->numrows();
	$DATA["capital_social"] = $solde_part_sociale;// pas utilisé. a eter remplacer par cap sociale_lib
	$DATA["capital_social_souscrites"] = $DATA["total_stat_jur"]["pp"]["total_soldePS_sous"] + $DATA["total_stat_jur"]["pm"]["total_soldePS_sous"] + $DATA["total_stat_jur"]["gi"]["total_soldePS_sous"] + $DATA["total_stat_jur"]["gs"]["total_soldePS_sous"] ;
	$DATA["capital_social_lib"] =  $DATA["total_stat_jur"]["pp"]["total_soldePS_lib"] + $DATA["total_stat_jur"]["pm"]["total_soldePS_lib"] + $DATA["total_stat_jur"]["gi"]["total_soldePS_lib"] + $DATA["total_stat_jur"]["gs"]["total_soldePS_lib"];
	$DATA["capital_social_restant"] =  $DATA["total_stat_jur"]["pp"]["total_soldePS_restant"] + $DATA["total_stat_jur"]["pm"]["total_soldePS_restant"] + $DATA["total_stat_jur"]["gi"]["total_soldePS_restant"] + $DATA["total_stat_jur"]["gs"]["total_soldePS_restant"];
	$DATA["valnomps"] = $val_nom_ps;


	return $DATA;
}

/**
 * Fonction qui renvoie l'ensemble des comptes dont le solde est supérieur à minimum
 *
 **/
function getListeCompteSupamin($minimum, $devise = NULL, $gestionnaire = 0,$n_agence = null, $n_guichet = null) {
  global $dbHandler;
  global $global_id_agence;
  global $global_multidevise;
  global $global_monnaie;

  $db = $dbHandler->openConnection();
  $min = recupMontant($minimum);

  $sql = "SELECT id_client, id_cpte, solde";

  if ($global_multidevise) {
    $sql .= ", devise";
  }

  $sql .= " FROM ad_cli, ad_cpt WHERE ad_cli.id_ag = ad_cpt.id_ag AND ad_cpt.id_ag = $global_id_agence AND ad_cli.id_client = ad_cpt.id_titulaire AND ad_cpt.solde >= $min AND ad_cpt.id_prod <> 3 AND ad_cpt.id_prod <> 4";

  if ($gestionnaire > 0) {
    $sql .= " AND ad_cli.gestionnaire = $gestionnaire";
  }

  if ($devise != NULL) {
    $sql .= " AND devise = '".$devise."'";
  }
  if ($n_agence != null){
    $sql .= " AND ad_cli.n_agence = $n_agence" ;
  }
  if ($n_guichet != null){
    $sql .= " AND ad_cli.n_guichet = $n_guichet";
  }

  $sql .= " ORDER BY solde DESC;";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $DATAS = array ();
  $DATAS["details"] = array ();

  if ($devise != NULL) {
    setMonnaieCourante($devise);
  }

  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $INFO = array ();
    $INFO["idclient"] = $tmprow["id_client"];
    $INFO["nom"] = getClientName($INFO["idclient"]);
    $ACC = getAccountDatas($tmprow["id_cpte"]);
    $INFO["numcpt"] = $ACC["num_complet_cpte"];
    $INFO["libel"] = $ACC["libel"];
    $mnt = $tmprow["solde"];
    $INFO["solde"] = $mnt;
    $INFO["devise"] = $tmprow["devise"];
    array_push($DATAS["details"], $INFO);
  }

  $DATAS["minimum"] = array ();
  $DATAS["minimum"] = afficheMontant($min, true);

  if ($devise != NULL) {
    setMonnaieCourante($global_monnaie);
  }
  $dbHandler->closeConnection(true);
  return $DATAS;
}

/**
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport balance agée du portefeuille à risque
 *
 * @param Integer $gestionnaire Identifiant du gestionnaire, 0 si tous
 *  @param Date $export_date , date d'édition du rapport
 *  @param Integer $type_affich Type affichage, 1 si affichage détaillé 2 si  affichage synthétisé
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */

/*  function balanceportefeuillerisque($gestionnaire = 0, $export_date, $type_affich, $date_debloc_inf, $date_debloc_sup ) {

	global $dbHandler;
	global $global_multidevise;
	global $global_monnaie,$global_id_agence;
	$db = $dbHandler->openConnection();
	if($export_date == NULL){
		$export_date = date("Y")."-".date("m")."-".date("d");
	}
	// Tous les états de crédit 
	$etats_credit = getTousEtatCredit($global_id_agence);
	// Initialisation des données 
	$DATAS = array ();
	$DATAS["pretsretard"] = array ();
	$tabGS = array();
	$totalenretard = 0;
	$totalprincipalretard = 0;
	$portefeuilletotal = 0;
	$portefeuillsain = 0;
	// le nombre et le montant des prêts par état de crédit 
	foreach ($etats_credit as $key => $value) {
		$DATAS[$global_id_agence]["nb"][$value['id']] = 0;
		$DATAS[$global_id_agence]["mnt"][$value['id']] = 0;
	}
	$idEtatPerte = getIDEtatPerte();
	
	if($type_affich == 2){//affichage synthetiques
		//pour les échéances :etr	
		$sql="SELECT sum(e.mnt_cap) AS mnt_cap, count(distinct d.id_doss) AS nbr_cred, (case WHEN date('$export_date') = date(now()) THEN d.cre_etat ELSE CalculEtatCredit(d.id_doss, '$export_date', $global_id_agence) END ) AS cre_etat,";
		//$sql .= "p.devise, p.id_ag from ad_etr e, ad_dcr d, adsys_produit_credit p  WHERE e.id_doss = d.id_doss AND d.id_prod = p.id AND d.cre_date_debloc <= '$export_date' AND ((d.etat IN (5,7,8,13,14,15)) OR (d.etat IN (6,9,11,12) AND d.date_etat > '$export_date')) ";
		$sql .= "p.devise, p.id_ag from ad_etr e, ad_dcr d, adsys_produit_credit p  WHERE e.id_doss = d.id_doss AND d.id_prod = p.id AND ((d.etat IN (5,7,8,13,14,15)) OR (d.etat IN (6,11,12) AND d.date_etat > '$export_date')) ";
		
		if ($gestionnaire > 0){
			$sql.= " AND d.id_agent_gest=$gestionnaire";
		}
		//filtre date debut/fin deboursement
		if (isset ($date_debloc_inf))
			$sql .= " AND d.cre_date_debloc >= date('" . $date_debloc_inf . "')";
		if (isset ($date_debloc_sup))
			$sql .= " AND d.cre_date_debloc <= date('" . $date_debloc_sup . "')";
		
		$sql.= " AND e.id_ag = d.id_ag AND d.id_ag = p.id_ag AND p.id_ag=$global_id_agence ";
		$sql.=" GROUP BY p.id_ag, cre_etat, (case WHEN date('$export_date') = date(now()) THEN d.cre_etat ELSE CalculEtatCredit(d.id_doss, '$export_date', $global_id_agence) END ), p.devise ";
		$sql.= " ORDER BY p.id_ag, cre_etat ";
		$result1 = $db->query($sql);
		if (DB::isError($result1)) {
			$dbHandler->closeConnection(false);
			Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result1->getMessage());
		}
		$ech = array(); 
		while ($values = $result1->fetchrow(DB_FETCHMODE_ASSOC)) {
			$ech[$values['id_ag']][$values['cre_etat']]['nbr'] += $values['nbr_cred'];
			$ech[$values['id_ag']][$values['cre_etat']]['mnt_cap'] += $values['mnt_cap'];
		}
		//pour les remboursements 
		$sql="SELECT sum(s.mnt_remb_cap) AS mnt_remb_cap, count(distinct d.id_doss) AS nbr_cred, (case WHEN date('$export_date') = date(now()) THEN d.cre_etat ELSE CalculEtatCredit(d.id_doss, '$export_date', $global_id_agence) END ) AS cre_etat,";
		$sql .= "p.devise, p.id_ag from ad_sre s, ad_dcr d, adsys_produit_credit p  WHERE s.id_doss = d.id_doss AND d.id_prod = p.id AND s.date_remb <= date('" . $date_debloc_sup . "') AND ((d.etat IN (5,7,8,13,14,15)) OR (d.etat IN (6,9,11,12) AND d.date_etat > '$export_date')) ";
		if ($gestionnaire > 0){
			$sql.= " AND d.id_agent_gest=$gestionnaire";
		}
		//filtre date debut/fin deboursement
		if (isset ($date_debloc_inf))
			$sql .= " AND d.cre_date_debloc >= date('" . $date_debloc_inf . "')";
		if (isset ($date_debloc_sup))
			$sql .= " AND d.cre_date_debloc <= date('" . $date_debloc_sup . "')";
		
		$sql.= " AND s.id_ag = d.id_ag AND d.id_ag = p.id_ag AND p.id_ag=$global_id_agence ";
		$sql.=" GROUP BY p.id_ag, cre_etat, (case WHEN date('$export_date') = date(now()) THEN d.cre_etat ELSE CalculEtatCredit(d.id_doss, '$export_date', $global_id_agence) END ), p.devise ";
		$sql.= " ORDER BY p.id_ag, cre_etat ";
		$result1 = $db->query($sql);
		if (DB::isError($result1)) {
			$dbHandler->closeConnection(false);
			Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result1->getMessage());
		}
		$remb = array();
		while ($values = $result1->fetchrow(DB_FETCHMODE_ASSOC)) {
			$remb[$values['id_ag']][$values['cre_etat']]['mnt_remb_cap'] += $values['mnt_remb_cap'];
		}

		// données
		foreach ($ech as $id_agence => $data_ag) {
			foreach ($data_ag as $cre_etat => $values) {
				$DATAS[$id_agence]["mnt"][$cre_etat] = $values['mnt_cap'] - (isset($remb[$id_agence][$cre_etat]['mnt_remb_cap'])?$remb[$id_agence][$cre_etat]['mnt_remb_cap']:0) ;
				$DATAS[$id_agence]["nb"][$cre_etat] = $values['nbr'];
				$portefeuilletotal += $DATAS[$id_agence]["mnt"][$cre_etat];
				if($cre_etat > 1)
				$totalenretard +=  $DATAS[$id_agence]["mnt"][$cre_etat];
				else
				$portefeuillsain += $DATAS[$id_agence]["mnt"][$cre_etat];
			}
		}
	}else{//affichage détaillé
		$sql="SELECT d.id_doss,d.id_client,d.id_ag,d.date_dem,d.cre_mnt_octr, d.id_agent_gest, d.gs_cat, d.id_dcr_grp_sol, cre_nbre_reech, (case WHEN date('$export_date') = date(now()) THEN d.cre_etat ELSE CalculEtatCredit(d.id_doss, '$export_date', $global_id_agence) END ) AS cre_etat,";
		
		//$sql .= "d.prov_mnt, p.devise from ad_dcr d, adsys_produit_credit p  WHERE d.id_prod = p.id AND d.cre_date_debloc <= '$export_date' AND ((d.etat IN (5,7,8,13,14,15)) OR (d.etat IN (6,9,11,12) AND d.date_etat > '$export_date'))";
		$sql .= "d.prov_mnt, p.devise from ad_dcr d, adsys_produit_credit p  WHERE d.id_prod = p.id AND ((d.etat IN (5,7,8,13,14,15)) OR (d.etat IN (6,9,11,12) AND d.date_etat > '$export_date'))";
		
		
		if ($gestionnaire > 0){
			$sql.= " AND d.id_agent_gest=$gestionnaire";
		}
		//filtre date debut/fin deboursement
		if (isset ($date_debloc_inf))
			$sql .= " AND d.cre_date_debloc >= date('" . $date_debloc_inf . "')";
		if (isset ($date_debloc_sup))
			$sql .= " AND d.cre_date_debloc <= date('" . $date_debloc_sup . "')";
		
		$sql.= " AND d.id_ag = p.id_ag AND p.id_ag=$global_id_agence ";
		$sql.=" ORDER BY d.id_doss ";
		$result1 = $db->query($sql);
		if (DB::isError($result1)) {
			$dbHandler->closeConnection(false);
			Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result1->getMessage());
		}
				
		while ($values = $result1->fetchrow(DB_FETCHMODE_ASSOC)) {
			$iddoss = $values['id_doss'];
			//$DATAS["pretsretard"][$iddoss] = array ();
			$details = array ();
			$details['cre_etat'] = $values['cre_etat'];
			$DATAS[$values['id_ag']]["nb"][$values['cre_etat']]++;
			$idclient = $values['id_client'];
			$nom = getClientName($idclient,$values['id_ag']);
			$solde_capital_brut = getSoldeCapital($iddoss,$export_date);
			$mnt_reech_ap_date_export = 0; // chercher le montant reechelonné aprs la date du rapport
			if ($values['cre_nbre_reech'] > 0) {
				$reechMorat = getRechMorHistorique (145,$values['id_client'],$values["date_dem"]); //Date demande car date rééch > date demande
				if (is_array($reechMorat)) {
					reset($reechMorat);
					foreach ($reechMorat as $id_his_reech => $reech) {
						if (isBefore($export_date, pg2phpDate($reech['date']))) {
							$mnt_reech_ap_date_export = $mnt_reech_ap_date_export + $reech['infos'];
						}
					}
				}
			}
			$solde_capital_brut = $solde_capital_brut - $mnt_reech_ap_date_export;
			if ($global_multidevise) {
				//$DOSS = getDossierCrdtInfo($iddoss,$values['id_ag']);
				//$devise = $DOSS["devise"];
				$solde_capital_brut = calculeCV($values["devise"], $global_monnaie, $solde_capital_brut,$values['id_ag']);
			}
			$portefeuilletotal += $solde_capital_brut;
			$DATAS[$values['id_ag']]["mnt"][$values['cre_etat']] += $solde_capital_brut;
			if($values['cre_etat']>=2){
				$totalenretard += $solde_capital_brut;
				$princ_int_gar_pen = getRetardPrincIntGarPen($iddoss, $export_date);
				$principal = $princ_int_gar_pen['solde_cap'];
				$totalprincipalretard += $principal;
				$interets = $princ_int_gar_pen['solde_int'];
				$garantie = $princ_int_gar_pen['solde_gar'];
				$penalite = $princ_int_gar_pen['solde_pen'];
			}
			elseif($values['cre_etat']==1)
			$portefeuillsain += $solde_capital_brut;
			if ($values['id_agent_gest'] != "")
			$gest = $values['id_agent_gest'] .				" (" . getNomUtilisateur($values['id_agent_gest']) . ")";
			else
			$gest = _("Pas de gestionnaire");
			$idclient = sprintf("%06d", $idclient);
			$details['nom'] = $nom;
			$details['id_doss'] = $iddoss;
			$details['montantpret'] = $values['cre_mnt_octr'];
			$details['solde'] = $solde_capital_brut;
			$details['principal'] = $principal;
			$details['interets'] = $interets;
			$details['garantie'] = $garantie;
			$details['penalite'] = $penalite;
			$details['gest'] = $gest;
			$details['id_doss'] = $iddoss;
			$details['gs_cat'] = $values['gs_cat'];
			$details['idclient'] = $idclient;
			$prcentage_impaye = ($solde_capital_brut/$values['cre_mnt_octr']);
			$details['impayes'] = $prcentage_impaye;
			$details['devise'] = $values["devise"];

			$details['prov_mnt'] = $values['prov_mnt'];
                        //$details['prov_mnt'] = calculprovision($iddoss, $values['cre_etat'], $etats_credit[$values["cre_etat"]]["taux"], $export_date); // Added : Ticket #227

			if ($details['gs_cat'] == 1)
			$details["membre"] = 0;

			//recuperation du crédit solidaire à dossiers multiples
			$groupe = getCreditSolDetailRap($values);
			if ((is_array($groupe["credit_gs"]))&&(!in_array($groupe["credit_gs"]["id_client"],$tabGS))){
				$tabGS[]  = $groupe["credit_gs"]["id_client"];
				$groupe["credit_gs"]["gest"] = $gest;
				$groupe["credit_gs"]["idclient"] = $groupe["credit_gs"]["id_client"];
				$groupe["credit_gs"]['cre_etat'] = $details['cre_etat'];
				$groupe["credit_gs"]["nom"] = getClientName($groupe["credit_gs"]["id_client"]);
				array_push($DATAS["pretsretard"],$groupe["credit_gs"]);
			}
			if (($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($values["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"]))
			$details["membre"] = 1;
			else
			$details["membre"] = 0;
			array_push($DATAS["pretsretard"],$details);
			//récuperation des crédits des membres d'un groupe solidaire  à dossier unique
			if(is_array($groupe[$values["id_client"]])) {
				$i = 0;
				while($i < count($groupe[$values["id_client"]])) {
					$groupe[$values["id_client"]][$i]["id_doss"] = 0;
					$groupe[$values["id_client"]][$i]["cre_etat"] = $details["cre_etat"];
					$groupe[$values["id_client"]][$i]["gest"] = $gest;
					$groupe[$values["id_client"]][$i]["idclient"] = $groupe[$values["id_client"]][$i]["id_client"];
					$groupe[$values["id_client"]][$i]["nom"] = getClientName($groupe[$values["id_client"]][$i]["id_client"]);
					array_push($DATAS["pretsretard"],$groupe[$values["id_client"]][$i]);
					$i++;
				}

			}
		}//fin parcours des crédits
	}
	$dbHandler->closeConnection(true);
	$DATAS["totaux"]['totalretard'] = $totalenretard;
	$DATAS["totaux"]['totalprincipalretard'] = $totalprincipalretard;
	if ($portefeuilletotal == 0) // Dans ce cas, rien à faire
	return NULL;
	$DATAS["totaux"]['portefeuilletotal'] = $portefeuilletotal;
	$DATAS["totaux"]['portefeuillsain'] = $portefeuillsain;
	$pourcentagerisque = ($totalenretard / $portefeuilletotal);
	$DATAS["pourcentage"]['pourcentagerisque'] = $pourcentagerisque;
	// Calcule des pourcentages à risque pour chaque état 
	if (is_array($etats_credit)) {
		foreach ($etats_credit as $key => $value) {
			$DATAS["pourcentage"]["prcentagerisque"][$value['id']] = $DATAS[$value['id_ag']]["mnt"][$value['id']] / $portefeuilletotal;
		}
	}
	return $DATAS;

}  */

function balanceportefeuillerisque($gestionnaire = 0, $export_date, $type_affich, $date_debloc_inf, $date_debloc_sup, $prd ,$n_agence= null, $n_guichet = null) {
			
				global $dbHandler;
				global $global_multidevise;
				global $global_monnaie,$global_id_agence;
				$db = $dbHandler->openConnection();
				if($export_date == NULL){
					$export_date = date("Y")."-".date("m")."-".date("d");
				}
				// Tous les états de crédit
				$etats_credit = getTousEtatCredit($global_id_agence);
				// Initialisation des données
				$DATAS = array ();
				$DATAS["pretsretard"] = array ();
				$tabGS = array();
				$totalenretard = 0;
				$totalprincipalretard = 0;
				$portefeuilletotal = 0;
				$portefeuillsain = 0;
				// le nombre et le montant des prêts par état de crédit
				foreach ($etats_credit as $key => $value) {
					$DATAS[$global_id_agence]["nb"][$value['id']] = 0;
					$DATAS[$global_id_agence]["mnt"][$value['id']] = 0;
				}
				$idEtatPerte = getIDEtatPerte();
			
	if ($type_affich == 2) { // affichage synthetiques                  
	                       
		// getportefeuilleview
		$sql = "SELECT id_doss,id_prod,id_client,id_ag,date_dem,cre_mnt_octr,id_agent_gest,gs_cat,id_dcr_grp_sol,cre_nbre_reech,id_etat_credit,prov_mnt,devise, mnt_cred_paye, is_ligne_credit FROM getPortfeuilleView('$export_date', $global_id_agence) WHERE id_etat_credit != $idEtatPerte AND id_ag=$global_id_agence  ";
		// filtre gestionnaire
		if ($gestionnaire > 0)
			$sql .= " AND id_agent_gest=$gestionnaire ";
			// filtre date debut/fin deboursement
		if (isset ( $date_debloc_inf ))
			$sql .= " AND cre_date_debloc >= date('" . $date_debloc_inf . "')";
		if (isset ( $date_debloc_sup ))
			$sql .= " AND cre_date_debloc <= date('" . $date_debloc_sup . "')";
		if (isset ( $prd ))
			$sql .= " AND id_prod = $prd ";
        if (isset ( $n_agence ))
            $sql .= " AND n_agence = $n_agence ";
        if (isset ( $n_guichet ))
            $sql .= " AND n_guichet = $n_guichet ";
		
		
		$sql .= " ORDER BY id_etat_credit,id_doss";

      $result1 = $db->query ( $sql );
		if (DB::isError ( $result1 )) {
			$dbHandler->closeConnection ( false );
			Signalerreur ( __FILE__, __LINE__, __FUNCTION__, _ ( "DB" ) . ": " . $result1->getMessage () );
		}
		
		while ( $values = $result1->fetchrow ( DB_FETCHMODE_ASSOC ) ) {
			$iddoss = $values ['id_doss'];
			$details = array ();
			$details ['cre_etat'] = $values ['id_etat_credit'];
			$DATAS [$values ['id_ag']] ["nb"] [$values ['id_etat_credit']] ++;
			$idclient = $values ['id_client'];
			$nom = getClientName ( $idclient, $values ['id_ag'] );
			
			//getsolde total restant
                        if ($values["is_ligne_credit"] == 't') {
                            $solde_capital_brut = getCapitalRestantDuLcr($iddoss, $export_date);
                        } else {
                            $solde_capital_brut = $values["cre_mnt_octr"] - $values["mnt_cred_paye"];
                        }
		    //traite le solde pour le cas multi devise
			$portefeuilletotal += calculeCV($values ["devise"], $global_monnaie, $solde_capital_brut, $values ['id_ag']);
			
			$DATAS [$values ['id_ag']] ["mnt"] [$values ['id_etat_credit']] += $solde_capital_brut;
			if ($values ['id_etat_credit'] >= 2) {
				$totalenretard += $solde_capital_brut;
				$princ_int_gar_pen = getRetardPrincIntGarPen ( $iddoss, $export_date );
				$principal = $princ_int_gar_pen ['solde_cap'];
				$totalprincipalretard += $principal;
				$interets = $princ_int_gar_pen ['solde_int'];
				$garantie = $princ_int_gar_pen ['solde_gar'];
				$penalite = $princ_int_gar_pen ['solde_pen'];
			} elseif ($values ['id_etat_credit'] == 1)
				$portefeuillsain += $solde_capital_brut;
			if ($values ['id_agent_gest'] != "")
				$gest = $values ['id_agent_gest'] . " (" . getNomUtilisateur ( $values ['id_agent_gest'] ) . ")";
			else
				$gest = _ ( "Pas de gestionnaire" );
			$idclient = sprintf ( "%06d", $idclient );
			$details ['nom'] = $nom;
			$details ['id_doss'] = $iddoss;
			$details ['montantpret'] = $values ['cre_mnt_octr'];
			$details ['solde'] = $solde_capital_brut;
			$details ['principal'] = $principal;
			$details ['interets'] = $interets;
			$details ['garantie'] = $garantie;
			$details ['penalite'] = $penalite;
			$details ['gest'] = $gest;
			$details ['id_doss'] = $iddoss;
			$details['id_prod'] = $values['id_prod'];
			$details ['gs_cat'] = $values ['gs_cat'];
			$details ['idclient'] = $idclient;
			$prcentage_impaye = ($solde_capital_brut / $values ['cre_mnt_octr']);
			$details ['impayes'] = $prcentage_impaye;
			$details ['devise'] = $values ["devise"];
			
			$details ['prov_mnt'] = $values ['prov_mnt'];
			// $details['prov_mnt'] = calculprovision($iddoss, $values['cre_etat'], $etats_credit[$values["cre_etat"]]["taux"], $export_date); // Added : Ticket #227
			
			if ($details ['gs_cat'] == 1)
				$details ["membre"] = 0;
				
				// recuperation du crédit solidaire à dossiers multiples
			$groupe = getCreditSolDetailRap ( $values );
			if ((is_array ( $groupe ["credit_gs"] )) && (! in_array ( $groupe ["credit_gs"] ["id_client"], $tabGS ))) {
				$tabGS [] = $groupe ["credit_gs"] ["id_client"];
				$groupe ["credit_gs"] ["gest"] = $gest;
				$groupe ["credit_gs"] ["idclient"] = $groupe ["credit_gs"] ["id_client"];
				$groupe ["credit_gs"] ['cre_etat'] = $details ['cre_etat'];
				$groupe ["credit_gs"] ["nom"] = getClientName ( $groupe ["credit_gs"] ["id_client"] );
				array_push ( $DATAS ["pretsretard"], $groupe ["credit_gs"] );
			}
			if (($groupe ["credit_gs"] ["id_dcr_grp_sol"] > 0) && ($values ["id_dcr_grp_sol"] == $groupe ["credit_gs"] ["id_dcr_grp_sol"]))
				$details ["membre"] = 1;
			else
				$details ["membre"] = 0;
			array_push ( $DATAS ["pretsretard"], $details );
			// récuperation des crédits des membres d'un groupe solidaire à dossier unique
			if (is_array ( $groupe [$values ["id_client"]] )) {
				$i = 0;
				while ( $i < count ( $groupe [$values ["id_client"]] ) ) {
					$groupe [$values ["id_client"]] [$i] ["id_doss"] = 0;
					$groupe [$values ["id_client"]] [$i] ["cre_etat"] = $details ["cre_etat"];
					$groupe [$values ["id_client"]] [$i] ["gest"] = $gest;
					$groupe [$values ["id_client"]] [$i] ["idclient"] = $groupe [$values ["id_client"]] [$i] ["id_client"];
					$groupe [$values ["id_client"]] [$i] ["nom"] = getClientName ( $groupe [$values ["id_client"]] [$i] ["id_client"] );
					array_push ( $DATAS ["pretsretard"], $groupe [$values ["id_client"]] [$i] );
					$i ++;
				}
			}
		} // fin parcours des crédits
		
	} else { // affichage détaillé
	       
		// getportefeuilleview
		$sql = "SELECT id_doss,id_prod,id_client,id_ag,date_dem,cre_mnt_octr,id_agent_gest,gs_cat,id_dcr_grp_sol,cre_nbre_reech,id_etat_credit,prov_mnt,devise, mnt_cred_paye, is_ligne_credit FROM getPortfeuilleView('$export_date', $global_id_agence) WHERE id_etat_credit != $idEtatPerte AND id_ag=$global_id_agence  ";
		// filtre gestionnaire
		if ($gestionnaire > 0)
			$sql .= " AND id_agent_gest=$gestionnaire ";
			// filtre date debut/fin deboursement
		if (isset ( $date_debloc_inf ))
			$sql .= " AND cre_date_debloc >= date('" . $date_debloc_inf . "')";
		if (isset ( $date_debloc_sup ))
			$sql .= " AND cre_date_debloc <= date('" . $date_debloc_sup . "')";
		if (isset ( $prd ))
			$sql .= " AND id_prod = $prd ";
        if (isset ( $n_agence ))
            $sql .= " AND n_agence = $n_agence ";
        if (isset ( $n_guichet ))
            $sql .= " AND n_guichet = $n_guichet ";


        $sql .= " ORDER BY id_etat_credit,id_doss";
        $result1 = $db->query ( $sql );
		if (DB::isError ( $result1 )) {
			$dbHandler->closeConnection ( false );
			Signalerreur ( __FILE__, __LINE__, __FUNCTION__, _ ( "DB" ) . ": " . $result1->getMessage () );
		}
		
		while ( $values = $result1->fetchrow ( DB_FETCHMODE_ASSOC ) ) {
			$iddoss = $values ['id_doss'];
			// $DATAS["pretsretard"][$iddoss] = array ();
			$details = array ();
			$details ['cre_etat'] = $values ['id_etat_credit'];
			$DATAS [$values ['id_ag']] ["nb"] [$values ['id_etat_credit']] ++;
			$idclient = $values ['id_client'];
			$nom = getClientName ( $idclient, $values ['id_ag'] );
			
			//getsolde total restant
                        if ($values["is_ligne_credit"] == 't') {
                            $solde_capital_brut = getCapitalRestantDuLcr($iddoss, $export_date);
                        } else {
                            $solde_capital_brut = $values["cre_mnt_octr"] - $values["mnt_cred_paye"];
                        }
			//traite le solde pour le cas multi devise
			$portefeuilletotal += calculeCV($values ["devise"], $global_monnaie, $solde_capital_brut, $values ['id_ag']);
			
			$DATAS [$values ['id_ag']] ["mnt"] [$values ['id_etat_credit']] += $solde_capital_brut;
			if ($values ['id_etat_credit'] >= 2) {
				$totalenretard += $solde_capital_brut;
				$princ_int_gar_pen = getRetardPrincIntGarPen ( $iddoss, $export_date );
				$principal = $princ_int_gar_pen ['solde_cap'];
				$totalprincipalretard += $principal;
				$interets = $princ_int_gar_pen ['solde_int'];
				$garantie = $princ_int_gar_pen ['solde_gar'];
				$penalite = $princ_int_gar_pen ['solde_pen'];
			} elseif ($values ['id_etat_credit'] == 1)
				$portefeuillsain += $solde_capital_brut;
			if ($values ['id_agent_gest'] != "")
				$gest = $values ['id_agent_gest'] . " (" . getNomUtilisateur ( $values ['id_agent_gest'] ) . ")";
			else
				$gest = _ ( "Pas de gestionnaire" );
			$idclient = sprintf ( "%06d", $idclient );
			$details ['nom'] = $nom;
			$details ['id_doss'] = $iddoss;
			$details ['montantpret'] = $values ['cre_mnt_octr'];
			$details ['solde'] = $solde_capital_brut;
			$details ['principal'] = $principal;
			$details ['interets'] = $interets;
			$details ['garantie'] = $garantie;
			$details ['penalite'] = $penalite;
			$details ['gest'] = $gest;
			$details ['id_doss'] = $iddoss;
			$details ['id_prod'] = $values ['id_prod'];
			$details ['gs_cat'] = $values ['gs_cat'];
			$details ['idclient'] = $idclient;
			$prcentage_impaye = ($solde_capital_brut / $values ['cre_mnt_octr']);
			$details ['impayes'] = $prcentage_impaye;
			$details ['devise'] = $values ["devise"];
			
			$details ['prov_mnt'] = $values ['prov_mnt'];

			
			if ($details ['gs_cat'] == 1)
				$details ["membre"] = 0;
				
				// recuperation du crédit solidaire à dossiers multiples
			$groupe = getCreditSolDetailRap ( $values );
			if ((is_array ( $groupe ["credit_gs"] )) && (! in_array ( $groupe ["credit_gs"] ["id_client"], $tabGS ))) {
				$tabGS [] = $groupe ["credit_gs"] ["id_client"];
				$groupe ["credit_gs"] ["gest"] = $gest;
				$groupe ["credit_gs"] ["idclient"] = $groupe ["credit_gs"] ["id_client"];
				$groupe ["credit_gs"] ['cre_etat'] = $details ['cre_etat'];
				$groupe ["credit_gs"] ["nom"] = getClientName ( $groupe ["credit_gs"] ["id_client"] );
				array_push ( $DATAS ["pretsretard"], $groupe ["credit_gs"] );
			}
			if (($groupe ["credit_gs"] ["id_dcr_grp_sol"] > 0) && ($values ["id_dcr_grp_sol"] == $groupe ["credit_gs"] ["id_dcr_grp_sol"]))
				$details ["membre"] = 1;
			else
				$details ["membre"] = 0;
			array_push ( $DATAS ["pretsretard"], $details );
			// récuperation des crédits des membres d'un groupe solidaire à dossier unique
			if (is_array ( $groupe [$values ["id_client"]] )) {
				$i = 0;
				while ( $i < count ( $groupe [$values ["id_client"]] ) ) {
					$groupe [$values ["id_client"]] [$i] ["id_doss"] = 0;
					$groupe [$values ["id_client"]] [$i] ["cre_etat"] = $details ["cre_etat"];
					$groupe [$values ["id_client"]] [$i] ["gest"] = $gest;
					$groupe [$values ["id_client"]] [$i] ["idclient"] = $groupe [$values ["id_client"]] [$i] ["id_client"];
					$groupe [$values ["id_client"]] [$i] ["nom"] = getClientName ( $groupe [$values ["id_client"]] [$i] ["id_client"] );
					array_push ( $DATAS ["pretsretard"], $groupe [$values ["id_client"]] [$i] );
					$i ++;
				}
			}
		} // fin parcours des crédits
	}
				$dbHandler->closeConnection(true);
				$DATAS["totaux"]['totalretard'] = $totalenretard;
				//total echeance en retard 
				$DATAS["totaux"]['totalprincipalretard'] = $totalprincipalretard;
				if ($portefeuilletotal == 0) // Dans ce cas, rien à faire
					return NULL;
				$DATAS["totaux"]['portefeuilletotal'] = $portefeuilletotal;
				$DATAS["totaux"]['portefeuillsain'] = $portefeuillsain;
				$pourcentagerisque = ($totalenretard / $portefeuilletotal);
				$DATAS["pourcentage"]['pourcentagerisque'] = $pourcentagerisque;
				// Calcule des pourcentages à risque pour chaque état
				if (is_array($etats_credit)) {
					foreach ($etats_credit as $key => $value) {
						$DATAS["pourcentage"]["prcentagerisque"][$value['id']] = $DATAS[$value['id_ag']]["mnt"][$value['id']] / $portefeuilletotal;
					}
				}
				return $DATAS;
					

}

/**
 * Cette fonction est un wrapper pour getBrouillardCaisseDevise
 * Si la devise n'est pas précisée, elle fait appel à cette dernière pour chaque devise et récupère le résultat dans un tableau indicé par devise
 * @author Thomas Fastenakel
 * @since 2.5
 * @param : cfr getBrouillardCaisseDevise
 */
function getBrouillardCaisse($guichet, $date, $details, $devise, $export_csv = false,$n_agence=null, $n_guichet=null) {
  $DATA = array ();
  if (isset ($devise)) {
    $DATA_DEV = getBrouillardCaisseDevise($guichet, $date, $details, $devise, $export_csv,$n_agence,$n_guichet);
    $DATA[$devise] = $DATA_DEV;
  } else {
    $DEVS = get_table_devises();
    foreach ($DEVS as $code_devise => $DEV) {
      $DATA_DEV = getBrouillardCaisseDevise($guichet, $date, $details, $code_devise, $export_csv,$n_agence,$n_guichet);
      $DATA[$code_devise] = $DATA_DEV;
    }
  }
  return $DATA;
}
function getBrouillardCaisseDevise($guichet, $date, $details, $devise, $export_csv = false, $n_agence=null, $n_guichet=null)	// Fonction renvoyant toutes les données utilse pour la génération du  rapport de brouillard de caisse
// IN : $guichet = Le numéro du guichet
//      $date = Date du brouillard
//      $details = true  ==> Récupérer le détail des transactions
//                 false ==> Ne pas récupérer le détail des transactions
//      $devise = Devise du brouillard
// OUT: $DATA contient tous les éléments dans deux parties
//        ["global"] pour les infos globales
//        ["details"] pour les infos détaillées si $details = true
{
  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();
  global $adsys;
  $DATA = array ();
  $infos_gui = array ();
  $infos_gui = get_guichet_infos($guichet);
  $DATA['libel_gui'] = $infos_gui["libel_gui"];
  $login = getLoginFromGuichet($guichet);
  $id_uti = get_login_utilisateur($login);
  $DATA['utilisateur'] = get_utilisateur_nom($id_uti);
  if ($global_multidevise)
    $cpte_associe = $infos_gui["cpte_cpta_gui"] . ".$devise";
  else
    $cpte_associe = $infos_gui["cpte_cpta_gui"];
  // Recherche des encaisses
  // Recherche de la date correspondant à 1 jour avant la date $date
  $hier = hier($date);
  setMonnaieCourante($devise);
  $encDeb = abs(calculSolde($cpte_associe, $hier, false));
  $encFin = abs(calculSolde($cpte_associe, $date, false));
  $DATA['encaisse_debut'] = $encDeb;
  $DATA['encaisse_fin'] = $encFin;
  // Recherches de la synthèse des opérations
  $sql = "SELECT distinct m.sens, e.libel_ecriture, count(e.id_his) AS nombre, sum(m.montant) AS montant FROM ad_mouvement m, ad_ecriture e WHERE e.id_ag = m.id_ag AND m.id_ag = $global_id_agence AND date(e.date_comptable) = '$date' AND m.id_ecriture = e.id_ecriture AND compte = '$cpte_associe' GROUP BY m.sens, e.libel_ecriture";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $GLOBAL_INFOS = array ();
  // Initialisation des totaux
  $total_nombre = 0;
  $total_debit = 0;
  $total_credit = 0;
  // Pour chaque type d'opération ...
  while ($infos = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $montant = $infos["montant"];
    $total_nombre += $infos["nombre"];
    if ($infos["sens"] == "d")
      $total_debit += $montant;
    else
      $total_credit += $montant;
    $infos["libel_operation"] = $infos["libel_ecriture"];
    $infos["montant"] = afficheMontant($infos["montant"], false, $export_csv);
    if ($infos["sens"] == "d") {
      $infos["montant_debit"] = afficheMontant($montant, false, $export_csv);
    } else {
      $infos["montant_credit"] = afficheMontant($montant, false, $export_csv);
    }
    array_push($GLOBAL_INFOS, $infos);
  }
  if (isFusion()) {
      if ($n_agence != null) {
        $n_agence_libel = getLibelLocalisationFenacobu($n_agence);
      }
      else{
          $n_agence_libel = "Tous";
      }
      if ($n_guichet != null) {
          $n_guichet_libel = getLibelLocalisationFenacobu($n_guichet);
      }
      else{
          $n_guichet_libel = "Tous";
      }
      $DATA["global"] = $GLOBAL_INFOS;
      // Ajout des totaux dans le tableau
      $total_infos = array (
          "libel_operation" => "TOTAL",
          "nombre" => $total_nombre,
          "montant_debit" => afficheMontant($total_debit,
              true
          ), "montant_credit" => afficheMontant($total_credit, true),
          "Niveau agence" => $n_agence_libel,
          "Niveau guichet" => $n_guichet_libel);
      $DATA["total"] = $total_infos;
  }else{
      $DATA["global"] = $GLOBAL_INFOS;
      // Ajout des totaux dans le tableau
      $total_infos = array (
          "libel_operation" => "TOTAL",
          "nombre" => $total_nombre,
          "montant_debit" => afficheMontant($total_debit,
              true
          ), "montant_credit" => afficheMontant($total_credit, true));
      $DATA["total"] = $total_infos;
  }

  // Récupérations des infos détaillées
  if ($details) {
    $DETAILS = array ();
    $DETAILS1 = array ();
//   $sql = "SELECT h.id_his, he.num_piece, h.id_client, e.libel_ecriture, h.date, m.sens, m.montant FROM ad_his h, ad_his_ext he, ad_ecriture e, ad_mouvement m WHERE h.id_ag = he.id_ag AND he.id_ag = e.id_ag AND e.id_ag = m.id_ag AND m.id_ag = $global_id_agence AND m.compte = '$cpte_associe' AND h.id_his=e.id_his AND he.id=h.id_his_ext AND date(e.date_comptable) = '$date' AND m.id_ecriture = e.id_ecriture ORDER BY h.date";
    $sql = "SELECT h.id_his, h.id_his_ext, h.id_client, e.libel_ecriture, h.date, m.sens, m.montant,e.type_operation,e.info_ecriture FROM ad_his h, ad_ecriture e, ad_mouvement m WHERE h.id_ag = e.id_ag AND e.id_ag = m.id_ag AND m.id_ag = $global_id_agence AND m.compte = '$cpte_associe' AND h.id_his=e.id_his AND date(e.date_comptable) = '$date' AND m.id_ecriture = e.id_ecriture ORDER BY h.date";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $encCour = $encDeb;
    while ($infos = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    	if(isset ($infos["id_his_ext"])){
    		// Recuperer le numéro de la pièce comptable
    		$WHERE['id'] = $infos["id_his_ext"];
    		$sql = buildSelectQuery('ad_his_ext', $WHERE);
			  $result_his_ext = $db->query($sql);
			  if (DB::isError($result_his_ext)) {
			    $dbHandler->closeConnection(false);
			    signalErreur(__FILE__,__LINE__,__FUNCTION__);
	  	  }
	  	  $row = $result_his_ext->fetchrow();
	  	  $infos["num_piece"] = $row[4];
    	}
      if (isset ($infos["id_client"]))
        $infos["client"] = getClientName($infos["id_client"]);
      $infos["id_his"] = sprintf("%09d", $infos["id_his"]);
      if ($infos["id_client"] != '')
        $infos["id_client"] = sprintf("%06d", $infos["id_client"]);
      $infos["libel_operation"] = $infos["libel_ecriture"];
      $infos["heure"] = pg2phpTime($infos["date"]);
      $montant = $infos["montant"];
      if ($infos["sens"] == "d") {
        $encCour += $montant;
        $infos["montant"] = afficheMontant($infos["montant"], false, $export_csv);
        $infos["montant_debit"] = $infos["montant"];
      } else {
        $encCour -= $montant;
        $infos["montant"] = afficheMontant($infos["montant"], false, $export_csv);
        $infos["montant_credit"] = $infos["montant"];
      }
      // Encaisse courante
      $infos["encaisse"] = afficheMontant($encCour, false, $export_csv);
      $DETAILS[] = $infos;
    }
    $DATA["details"] = $DETAILS;
  }
  $dbHandler->closeConnection(true);
  return $DATA;
}

/**
 * Cette fonction renvoie les informations concernant les ecritures libres passées à une date donnée.
 * @author Aminata
 * @since 2.9
 * @param $a_guichet : Le numéro du guichet
*  @param $a_date : Date de passage de des ecritures libres'
*  @param $details = true  ==> Récupérer le détail des transactions
*                 false ==> Ne pas récupérer le détail des transactions
*   @param $devise : Devise des ecritures
* OUT: $DATA contient tous les éléments dans deux parties
*        ["global"] pour les infos globales
*        ["details"] pour les infos détaillées si $details = true
*/
function getEcrituresLibresDevise($a_login, $a_date_debut, $a_date_fin, $a_details, $a_devise, $a_export_csv = false, $list_agence)
{
  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();

  global $adsys;

  $DATA = array ();
  $infos_gui = array ();

  foreach($list_agence as $id_agence=>$libel_agence) {
    set_time_limit(0); // Eviter la deconnexion du script pour depassement de max execution time
    setGlobalIdAgence($id_agence);
	  if (isset($a_login)){
	  //$infos_login = get_logins_utilisateur($a_login);
	  $DATA['login'] = $a_login;
	  $id_utilisateur = get_login_utilisateur($a_login);

	  //$login = $infos_login[0]["login"];
	  $DATA['utilisateur'] = get_utilisateur_nom($id_utilisateur);
	  $guichet = getGuichetFromLogin ($a_login);
	  if ($guichet != -1) {
	  $id_gui = getIdGuichet($guichet);
	  $infos_gui = get_guichet_infos($id_gui["id_gui"]);
	  if ($global_multidevise)
	    $cpte_associe = $infos_gui["cpte_cpta_gui"] . ".$devise";
	  else
	    $cpte_associe = $infos_gui["cpte_cpta_gui"];
	  // Recherche des encaisses
	  // Recherche de la date correspondant à 1 jour avant la date $date
	  $hier = hier($a_date_debut);

	  setMonnaieCourante($a_devise);

	  $encDeb = abs(calculSolde($cpte_associe, $hier));
	  $encFin = abs(calculSolde($cpte_associe, $a_date_fin));

	  $DATA['encaisse_debut'] = $encDeb;
	  $DATA['encaisse_fin'] = $encFin;
	  }
	  else {//agent sans guichet,
	  	$DATA['sans_guichet'] = 1;
	  }
	  $wherecond = " AND h.login = '$a_login'";
	}else {//agent sans guichet,
	  	$DATA['sans_guichet'] = 1;
	  }
    
	  // Recherches de la synthèse des opérations
	  $sql = "SELECT distinct m.sens, e.libel_ecriture, count(h.id_his) AS nombre, sum(m.montant) AS montant FROM ad_mouvement m, ad_ecriture e, ad_his h WHERE e.id_ag = m.id_ag AND m.id_ag = h.id_ag AND h.id_ag = $global_id_agence AND date(e.date_comptable) = date(h.date) AND date(h.date) >= '$a_date_debut' AND date(e.date_comptable) <= '$a_date_fin' AND m.id_ecriture = e.id_ecriture AND h.id_his = e.id_his AND h.type_fonction = 470 $wherecond AND m.devise = '$a_devise' AND m.sens = 'd' GROUP BY m.sens, e.libel_ecriture";
	  $result = $db->query($sql);
	  if (DB :: isError($result)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__, __LINE__, __FUNCTION__);
	  }
	  $GLOBAL_INFOS = array ();

	  // Initialisation des totaux
	  $total_nombre = 0;
	  $total_debit = 0;
	  $total_credit = 0;

	  // Pour chaque type d'opération ...
	  while ($infos = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
	    $montant = $infos["montant"];
	    $total_nombre ++;
	    if ($infos["sens"] == "d")
	      $total_debit += $montant;
	    else
	      $total_credit += $montant;

	    $infos["libel_operation"] = $infos["libel_ecriture"];
	    $infos["montant"] = afficheMontant($infos["montant"], false, $a_export_csv);
	    if ($infos["sens"] == "d") {
	      $infos["montant_debit"] = afficheMontant($montant, false, $a_export_csv);
	    } else {
	      $infos["montant_credit"] = afficheMontant($montant, false, $a_export_csv);
	    }
	    array_push($GLOBAL_INFOS, $infos);
	  }

	  $DATA["global"] = $GLOBAL_INFOS;

	  // Ajout des totaux dans le tableau
	  $total_infos = array (
	      "libel_operation" => "TOTAL",
	      "nombre" => $total_nombre,
	      "montant_debit" => afficheMontant($total_debit,
	        true
	        ), "montant_credit" => afficheMontant($total_credit, true));
	  $DATA["total"] = $total_infos;

	  // Récupérations des infos détaillées
	  if ($a_details) {
	    $DETAILS = array ();
	    $DETAILS1 = array ();
	    $sql = "SELECT h.id_his, h.date, e.libel_ecriture, m.sens, m.montant, m.compte, c.id_titulaire as id_client FROM ad_his h, ad_ecriture e, ad_mouvement m LEFT JOIN ad_cpt c ON (m.cpte_interne_cli = c.id_cpte AND m.id_ag = c.id_ag) WHERE h.id_ag = e.id_ag AND e.id_ag = m.id_ag AND m.id_ag = $global_id_agence AND h.id_his=e.id_his AND h.type_fonction = 470 $wherecond AND m.devise = '$a_devise' AND date(e.date_comptable) >= '$a_date_debut' AND date(e.date_comptable) <= '$a_date_fin' AND m.id_ecriture = e.id_ecriture ORDER BY h.date, m.sens";

	    $result = $db->query($sql);
	    if (DB :: isError($result)) {
	      $dbHandler->closeConnection(false);
	      signalErreur(__FILE__, __LINE__, __FUNCTION__);
	    }
	    while ($infos = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
	      $infos["id_his"] = sprintf("%09d", $infos["id_his"]);
	      $infos["client"] = $infos["id_client"];
	      $infos["libel_operation"] = $infos["libel_ecriture"];
	      $infos["heure"] = pg2phpTime($infos["date"]);
	     $infos["date"] = pg2phpDate($infos["date"]);
	      $montant = $infos["montant"];

	      if ($infos["sens"] == "d") {
	        $infos["montant_debit"] = afficheMontant($infos["montant"], false, $a_export_csv);
	        $infos["compte_debit"] = $infos["compte"];
	      } else {
	        $infos["montant_credit"] = afficheMontant($infos["montant"], false, $a_export_csv);
	        $infos["compte_credit"] = $infos["compte"];
	      }
	      $DETAILS[] = $infos;
	    }
	    $DATA["details"] = $DETAILS;
	  }
    resetGlobalIdAgence($id_agence);
  } // fin liste agence

    $dbHandler->closeConnection(true);
  return $DATA;
}

/**
 * Cette fonction est un wrapper pour getEcrituresLibresDevise
 * Si la devise n'est pas précisée, elle fait appel à cette dernière pour chaque devise et récupère le résultat dans un tableau indicé par devise
 * @author Aminata
 * @since 2.9
 * @param : cfr getEcrituresLibresDevise
 */
function getEcrituresLibres($a_login, $a_date_debut, $a_date_fin, $a_details, $a_devise, $a_export_csv = false, $list_agence) {
  $DATA = array ();
  if (isset ($a_devise)) {
    $DATA_DEV = getEcrituresLibresDevise($a_login, $a_date_debut, $a_date_fin, $a_details, $a_devise, $a_export_csv, $list_agence);
    $DATA[$a_devise] = $DATA_DEV;
  } else {
    $DEVS = get_table_devises();
    foreach ($DEVS as $code_devise => $DEV) {
      $DATA_DEV = getEcrituresLibresDevise($a_login, $a_date_debut, $a_date_fin, $a_details, $code_devise, $a_export_csv, $list_agence);
      $DATA[$code_devise] = $DATA_DEV;
    }
  }
  return $DATA;
}

/**
 * Fonction qui renvoie la liste des $nombre dossiers de crédit ayant les plus grands encours
 * Utilisé par le rapport 'Encours de crédit les plus importants'
 *
 * @param int $nombre Nombre de dossiers à renvoyer
 * @param real $mnt_min encours minimum
 * @param int $gestionnaire Identifiant du gestionnaire de crédit
 * @return array Liste et détail des clients
 * <ul><li> ["DETAILS"] => array (
 *   <ul><li> [index] => array (  <ul>
 *         <li>     "id_client" = ID du client
 *         <li>     "nom_client", = Nom du client
 *         <li>     "id_doss" = ID du dossier de crédit
 *         <li>     "solde_cap" = En-cours de crédit (solde en capital)
 *         <li>     "cre_etat" = Etat du crédit
 *         <li>     "devise" = la devise
 *         <li>     "contre_valeur" = contre valeur de solde en capital
 *         <li>     "mnt_pen" = Montant des pénalités attendues (si en retard ou en souffrance)))
 *       </ul>
 *     <li> ["TOTAL"] => array(
 *         <li>     "portefeuille" = Monant total du portefeuille de crédit
 *         <li>     "portefeuille_cli" = Partie de ce portefeuille constitué par les clients lités
 *         <li>     "portefeuile_retard_cli" = Partie en retard sur portefeuile_cli
 *       </ul>
 *   </ul></ul>
 */
function getListeClientsDebiteurs($nombre, $mnt_min, $gestionnaire) {
  global $dbHandler;
  global $global_multidevise,$global_id_agence;
  global $global_monnaie;

  $db = $dbHandler->openConnection(true);
  if ($mnt_min == NULL)
    $mnt_min = 0;
  $sql = "SELECT  a.id_doss,a.cre_etat, SUM(solde_cap) AS solde_cap, SUM(solde_pen) AS mnt_pen ";
  $sql .= " FROM ad_dcr a, ad_etr b WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence 
        AND a.id_doss = b.id_doss AND (a.etat = 5 OR a.etat = 7 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) ";
  if ($gestionnaire != NULL)
    $sql .= " AND a.id_agent_gest=$gestionnaire";
  $sql .= " GROUP BY a.id_doss, a.cre_etat ORDER BY solde_cap DESC, a.cre_etat ";
    
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  // Initialisation des infos globales
  $DETAILS = array ();
  $DATA = array ();
  $GLOB = array ();
  $TOTAL = array ();
  $GLOB["portefeuille"] = 0;
  $GLOB["portefeuille_cli"] = 0;
  $GLOB["portefeuille_retard_cli"] = 0;
  $TOTAL["total_sain"] = 0;
  $i = 0;
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    // infos du dossier
    $infos_doss = getDossierCrdtInfo($row['id_doss']);
    // calculer la contre valeur si on est en multidevises
    if ($global_multidevise)
      $contre_valeur = calculeCV($infos_doss["devise"], $global_monnaie, $row["solde_cap"]);
    else
      $contre_valeur = $row["solde_cap"];
    // vérifier que la contre valeur est > $mnt_min
    if ($contre_valeur >= $mnt_min) {
      $DATA[$i] = $row;
      $DATA[$i]['cre_etat'] = $infos_doss["cre_etat"];
      $DATA[$i]["contre_valeur"] = $contre_valeur;
      $DATA[$i]['id_client'] = $infos_doss["id_client"];
      $DATA[$i]["nom_utilisateur"] = getClientName($infos_doss["id_client"]);
      $DATA[$i]["gs_cat"] = $infos_doss["gs_cat"];
      $DATA[$i]["id_dcr_grp_sol"] = $infos_doss["id_dcr_grp_sol"];
      $GLOB["portefeuille"] += $DATA[$i]["contre_valeur"];
      $i++;
    }
  }

  $i = 1;
  $tabGS = array();
  $tab_premier_membre_GS_multi = array();
  while ((list ($key, $value) = each($DATA)) && ($i <= $nombre)) {
	 //récuperation du crédit solidaire à dossiers multiples
   	 $groupe = getCreditSolDetailRap($value);
     if((is_array($groupe["credit_gs"])) && (!in_array($groupe["credit_gs"]["id_client"],$tabGS))){
    	$tabGS[]  = $groupe["credit_gs"]["id_client"];
    	//TODO : l'etat d'un credit solidaire à dossier multiple est à préciser,
    	$groupe["credit_gs"]["cre_etat"] = $value["cre_etat"];
    	$groupe["credit_gs"]["premier_membre"] = $value["id_client"];
    	$credit_solidaire = $groupe["credit_gs"];
    	$DETAILS[] = $groupe["credit_gs"];
    }
    if (($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($value["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"])) {
    	$tab_premier_membre_GS_multi[] = $value["id_client"];
		$nbre_occurence_tab = array_count_values($tab_premier_membre_GS_multi);
		if (($nbre_occurence_tab[$value["id_client"]] > 1) && ($credit_solidaire["premier_membre"] == $value["id_client"])) {
			$credit_solidaire["cre_etat"] = $value["cre_etat"];
			$DETAILS[] = $credit_solidaire;
		}
		$value["membre"] = 1;
    }
    else $value["membre"] = 0;
    $DETAILS[] = $value;
    //récuperation des crédits des membres d'un groupe solidaire  à dossier unique
    if(is_array($groupe[$value["id_client"]])) {
    	$i = 0;
    	while($i < count($groupe[$value["id_client"]])) {
    		$groupe[$value["id_client"]][$i]["cre_etat"] = $value["cre_etat"];
    		$DETAILS[] = $groupe[$value["id_client"]][$i];
    		$i++;
    	}
    }
    $GLOB["portefeuille_cli"] += $value["contre_valeur"];
    if ($value["cre_etat"] > 1)
      $GLOB["portefeuille_retard_cli"] += $value["contre_valeur"];
    $i++;
  }
  $sql1 = " SELECT  a.id_doss,SUM(solde_cap) AS solde_cap ";
  $sql1 .= "FROM ad_dcr a, ad_etr b ";
  $sql1 .= "WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence AND a.id_doss = b.id_doss AND (a.etat = 5 OR a.etat = 7 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) AND cre_etat=1 group by a.id_doss;";
  $result1 = $db->query($sql1);
  if (DB :: isError($result1)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  while ($row1 = $result1->fetchrow(DB_FETCHMODE_ASSOC)) {
  	$TOTAL["total_sain"] += $row1["solde_cap"];
  }
 $value = array ("DETAILS" => $DETAILS,"TOTAL" => $GLOB,"TOTAL_SAIN" => $TOTAL);
 $dbHandler->closeConnection(true);
  return $value;
}
function getCreditsEmployesDirigeants($gestionnaire = 0, $date_edition = 0) {
  // Fonction qui renvoie le liste des clients dirigeants et employés possédans des crédits au sein de l'IMF
  // Utilisé par le rapport 'Crédits accordés aux employés et dirigeants'
  // IN : Néant
  // $gestionnaire identifiant du gestionnaire de crédit
  // OUT: ["DETAILS_DIR"] => array ([index]
  //         => array (
  //                 "id_client" = ID du client
  //                 "nom_client", = Nom du client
  //                 "id_doss" = ID du dossier de crédit
  //                 "solde_cap" = En-cours de crédit (solde en capital)
  //                 "cre_etat" = Etat du crédit
  //                 "mnt_pen" = Montant des pénalités attendues (si en retard ou en souffrance)))
  // OUT: ["DETAILS_EMP"] => array ([index]
  //         => array (
  //                 "id_client" = ID du client
  //                 "nom_client", = Nom du client
  //                 "id_doss" = ID du dossier de crédit
  //                 "solde_cap" = En-cours de crédit (solde en capital)
  //                 "cre_etat" = Etat du crédit
  //                 "mnt_pen" = Montant des pénalités attendues (si en retard ou en souffrance)))
  //    ["TOTAL"] => array(
  //                 "portefeuille" = Montant total du portefeuille de crédit
  //                 "portefeuille_emp" = Partie de ce portefeuille constitué par les employés
  //                 "portefeuille_dir" = Partie de ce portefeuille constitué par les dirigeants
  //                 "portefeuile_retard_emp" = Partie en retard sur portefeuile_emp
  //                 "portefeuile_retard_dir" = Partie en retard sur portefeuile_dir
  // ["EPARGNE"] => array(
  //                 "ratio_emp_epargne" = Encours prêts employés / encours éparne
  //                  "ratio_dir_epargne" = Encours prêts dirigeants / encours éparne
  global $dbHandler;
  global $global_multidevise,$global_id_agence;
  global $global_monnaie;

  $db = $dbHandler->openConnection(true);
  $sql = "SELECT a.id_doss, a.id_client, a.cre_etat, SUM(b.solde_cap) AS solde_cap, SUM(b.solde_pen) AS solde_pen, c.qualite, a.devise, a.gs_cat, a.id_dcr_grp_sol, a.date_dem, a.cre_mnt_octr, a.gar_tot, a.cre_retard_etat_max_jour, count(b.id_ech) AS nbre_ech ";
  $sql .= "  FROM get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) a, ad_etr b, ad_cli c";
  $sql .= " WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = $global_id_agence AND (a.id_doss = b.id_doss) AND a.id_client = c.id_client AND (a.etat = 5 OR a.etat = 7 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) AND (c.qualite = 3 OR c.qualite = 4) ";
  if ($date_edition){
  	$sql .= "and (a.date_dem <= date('$date_edition')) ";
  }
  if ($gestionnaire > 0)
    $sql .= "and (a.id_agent_gest = $gestionnaire) ";
  	$sql .= "GROUP BY a.id_doss, a.id_client, a.cre_etat, c.qualite, a.id_dcr_grp_sol, a.gs_cat, a.devise, a.date_dem, a.cre_mnt_octr, a.gar_tot, a.cre_retard_etat_max_jour ";
  	$sql .= "ORDER BY a.id_doss";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $DETAILS_DIR = array ();
  $DETAILS_EMP = array ();

  // Initialisation des infos globales
  $GLOB = array ();
  $tabGS = array();
  $GLOB["portefeuille_emp"] = 0;
  $GLOB["portefeuille_dir"] = 0;
  $GLOB["portefeuille_retard_emp"] = 0;
  $GLOB["portefeuille_retard_dir"] = 0;
  while ($row = $result->fetchrow()) {
    $infos = array ();
    $infos["id_client"] = $row[1];
    $infos["id_doss"] = $row[0];
    $infos["nom_utilisateur"] = getClientName($row[1]);
    $infos["solde_cap"] = $row[3];
    $infos["cre_etat"] = $row[2];
    $infos["date_dem"] = $row[9];
    $infos["cre_mnt_oct"] = $row[10];
    $infos["gar_tot"] = $row[11];
    $infos["cre_retard_etat_max_jour"] = $row[12];
    $infos["nbre_ech"] = $row[13];
    $infos["mnt_pen"] = $row[4];
    $infos["devise"] = $row[6];
    $infos["gs_cat"] = $row[7];
    $infos["id_dcr_grp_sol"] = $row[8];
    $cv_solde_cap = calculeCV($row[6], $global_monnaie, $infos["solde_cap"]);
    $infos["cv_solde_cap"] = $cv_solde_cap;

    if($infos["gs_cat"] == 2) {
    	$groupe = getCreditSolDetailRap($infos);
		$groupe["credit_gs"]["nom_utilisateur"]	 = mb_substr(getClientName($groupe["credit_gs"]["id_client"]), 0, 11, "UTF-8");
		if (($row[5] == 3) && (!in_array($groupe["credit_gs"]["id_client"],$tabGS["employe"]))) {
			$tabGS["employe"][]  = $groupe["credit_gs"]["id_client"];
			array_push($DETAILS_EMP, $groupe["credit_gs"]);
		}
		elseif (($row[5] == 4) && (!in_array($groupe["credit_gs"]["id_client"],$tabGS["dirigeant"]))){
			$tabGS["dirigeant"][]  = $groupe["credit_gs"]["id_client"];
			array_push($DETAILS_DIR, $groupe["credit_gs"]);
		}
	}
	//pour les membres du même groupe solidaire
    if (($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($infos["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"])) $infos["membre"] = 1;
    else//le groupe lui-même ne peut pas etre membre
    	$infos["membre"] = 0;

    if ($row[5] == 3) { // Qualité employé
      array_push($DETAILS_EMP, $infos);
      // Mise à jour des infos globales
      $GLOB["portefeuille_emp"] += $cv_solde_cap;
      if ($infos["cre_etat"] > 1)
        $GLOB["portefeuille_retard_emp"] += $cv_solde_cap;
    } else
      if ($row[5] == 4) { // Qualité dirigeant
        array_push($DETAILS_DIR, $infos);
        // Mise à jour des infos globales
        $GLOB["portefeuille_dir"] += $cv_solde_cap;
        if ($infos["cre_etat"] > 1)
          $GLOB["portefeuille_retard_dir"] += $cv_solde_cap;
      }
  }
  // Récupération du portefeuille de crédit
  $sql = "SELECT SUM(a.solde_cap)  ";
  if ($global_multidevise)
    $sql .= ",c.devise ";
  $sql .= "FROM ad_etr a";
  if ($global_multidevise) {
    if ($gestionnaire > 0)
      $sql .= " ,ad_dcr b, adsys_produit_credit c where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence and a.id_doss = b.id_doss and b.id_prod = c.id and b.id_agent_gest = $gestionnaire AND (b.etat = 5 OR b.etat = 7 OR b.etat = 13 OR b.etat = 14 OR b.etat = 15) group by(c.devise)";
    else
      $sql .= " ,ad_dcr b, adsys_produit_credit c where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence and a.id_doss=b.id_doss and b.id_prod=c.id AND (b.etat = 5 OR b.etat = 7 OR b.etat = 13 OR b.etat = 14 OR b.etat = 15) group by(c.devise)";
  } else {
  	if ($gestionnaire > 0)
      $sql .= " ,ad_dcr b, adsys_produit_credit c where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence and a.id_doss = b.id_doss and b.id_prod = c.id and b.id_agent_gest = $gestionnaire AND (b.etat = 5 OR b.etat = 7 OR b.etat = 13 OR b.etat = 14 OR b.etat = 15)";
    else
      $sql .= " ,ad_dcr b, adsys_produit_credit c where a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence and a.id_doss = b.id_doss and b.id_prod = c.id AND (b.etat = 5 OR b.etat = 7 OR b.etat = 13 OR b.etat = 14 OR b.etat = 15)";
  }

  $GLOB["portefeuille"] = 0;
  $portefeuille = 0;
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  while ($row = $result->fetchrow()) {
    if ($global_multidevise)
      $row[0] = calculeCV($row[1], $global_monnaie, $row[0]);
    $GLOB["portefeuille"] += $row[0];
  }
  $sql = "SELECT SUM(solde)";
  if ($global_multidevise)
    $sql .= " ,devise ";
  if ($gestionnaire > 0)
    $sql .= " FROM ad_cpt p,ad_cli c where p.id_ag = c.id_ag and c.id_ag = $global_id_agence and (p.id_prod <> 2 and p.id_prod <> 3) and (c.id_client=p.id_titulaire and c.gestionnaire=$gestionnaire) ";
  else
    $sql .= " FROM ad_cpt where id_ag = $global_id_agence and id_prod <> 2 and id_prod <> 3";
  if ($global_multidevise)
    $sql .= " group by(devise)";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $GLOB["epargne"] = 0;
  while ($row = $result->fetchrow()) {
    if ($global_multidevise)
      $row[0] = calculeCV($row[1], $global_monnaie, $row[0]);
    $GLOB["epargne"] += $row[0];
  }
  $RET = array ("DETAILS_EMP" => $DETAILS_EMP, "DETAILS_DIR" => $DETAILS_DIR, "TOTAL" => $GLOB);
  $dbHandler->closeConnection(true);
  return $RET;
}

/**
   * Renvoie les montant en retard d'un dossier de crédit
   * @author Djibril NIANG
   * @since 3.0.3
   * @param int $id_doss identifiant du dossier de crédit
   * @param date $date_calcul date de calcul du montant
   * @return MNT $montant_retard
**/
function getMontantRetardDossier($id_doss , $date_calcul) {

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT SUM(solde_cap) ";
  $sql .= " FROM ad_etr ";
  $sql .= " WHERE id_doss = $id_doss AND remb = 'f' AND date_ech < date('$date_calcul') AND id_ag = $global_id_agence";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "DB: ".$result->getMessage()
  }
  $row = $result->fetchrow();
  $montant_retard = $row[0];
  if($row[0] == NULL){
  	$montant_retard = 0;
  }
  $dbHandler->closeConnection(true);

  return $montant_retard;
}


function getCreditsPerte($date1, $date2, $gestionnaire = 0, $etat_dossier,$n_agence=null, $n_guichet=null)
{
  // Fonction qui renvoie des informations sur les crédits passés en perte
  // Infos utilisées pour la génération du rapport 'Crédits passés en perte'
  // IN : $date1 = Début de période
  //      $date2 = Fin de période
  //      $etat_dossier = etat filtre - ticket trac 682
  // OUT: array ("TOTAL" => Montant total passé en perte
  //             "DETAILS" = array ("id_doss" = ID du dossier
  //                                "id_client" = ID du client
  //                                "nom_client" = Nom du client
  //                                "id_prod" = ID du produit de crédit associé
  //                                "objet_dem" = Objet de la demande de crédit
  //                                "mnt_perte" = Montant de la perte liée à ce crédit
  //                                "date" = Date du passage en perte
  //                                "mnt_rec" = Montant d'évezntuels recouvrements
  //$gestionnaire:  utilisateur gestionnaire de crédit
  global $dbHandler;
  global $global_monnaie;
  global $global_multidevise, $global_id_agence;
  $tabGS = array();
  //ticket 682
  $AndOr = "OR"; //par defaut c'est OR pour etat dossier tous
  if ($etat_dossier > 0){ //si etat dossier est 6 ou 9
    $AndOr = "AND";
  }


  $db = $dbHandler->openConnection();
  $id_perte = getIDEtatPerte();
  $dataRecouvrement = array();
  // ticket 412
  $sql = " SELECT a.id_doss,sum(mnt_remb_cap) as solde_cap_rec,sum(mnt_remb_int) as solde_int_rec, a.etat,sum(mnt_remb_pen) as solde_pen_rec,a.id_dcr_grp_sol";
  $sql .= "  FROM ad_dcr a left join ad_sre b on ( b.id_ag = a.id_ag AND a.id_ag = $global_id_agence AND a.id_doss= b.id_doss)";
  $sql .= " where  ((a.cre_etat =$id_perte AND cre_date_etat BETWEEN '$date1' AND '$date2') ";
  $sql .= "".$AndOr." ((a.etat = ".$etat_dossier." AND cre_date_etat BETWEEN '$date1' AND '$date2')))  and (date(date_remb) > date (cre_date_etat)  AND date(date_remb)<= date('$date2') ) ";
  $sql .= "group by a.id_doss,a.id_dcr_grp_sol, a.etat  ORDER BY a.id_doss, a.etat ";

  $result2 = $db->query($sql);
  if (DB:: isError($result2)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  while ($tmprow1 = $result2->fetchrow(DB_FETCHMODE_ASSOC)) {
    $dataRecouvrement[$tmprow1['id_doss']] = $tmprow1;
  }

  if ($gestionnaire > 0) {
    $sql = "SELECT a.id_doss, a.etat, a.cre_etat, a.id_dcr_grp_sol, a.id_ag ,a.gs_cat,id_client,id_prod,a.libel,detail_obj_dem,
            detail_obj_dem_bis,perte_capital,date_etat,devise,prov_mnt,prov_date
            FROM get_ad_dcr_ext_credit(null, null, null, $id_perte, $global_id_agence) a, adsys_objets_credits b
            WHERE b.id_ag = a.id_ag AND a.id_ag = $global_id_agence AND a.obj_dem = b.id AND a.cre_etat = $id_perte
            AND a.id_agent_gest=$gestionnaire AND ((cre_date_etat BETWEEN '$date1' AND '$date2')
            ".$AndOr." (a.etat =".$etat_dossier." AND cre_date_etat BETWEEN '$date1' AND '$date2')) ";
  } else {
    $sql = "SELECT a.id_doss, a.etat, a.cre_etat, a.id_dcr_grp_sol, a.id_ag ,a.gs_cat,id_client,id_prod,a.libel,detail_obj_dem,
            detail_obj_dem_bis,perte_capital,date_etat,devise,prov_mnt,prov_date, cre_date_etat
            FROM get_ad_dcr_ext_credit(null, null, null, $id_perte, $global_id_agence) a,adsys_objets_credits b
            WHERE b.id_ag = a.id_ag AND a.id_ag = $global_id_agence AND a.obj_dem = b.id AND a.cre_etat = $id_perte
            AND ((cre_date_etat BETWEEN '$date1' AND '$date2') ".$AndOr." (a.etat =".$etat_dossier." AND cre_date_etat BETWEEN '$date1' AND '$date2')) ";
  }
    if($n_agence !=null){
    $sql .= " AND n_agence = $n_agence";
    }
    if ($n_guichet !=null){
        $sql .= " AND n_guichet = $n_guichet";
    }

  $sql .= " ORDER BY a.id_doss ,date_etat, a.etat, a.cre_etat ";

  $result = $db->query($sql);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $DETAILS = array();
  $total_perte = 0;
  $total_perte_recouvre = 0;
  $total_int_recouvre = 0;
  $total_prov_mnt = 0;
  //AT-51 - Evol ajout 3 totales (Capital, Interet et Penalite) recupere
  $total_cap_recupere = 0;
  $total_int_recupere = 0;
  $total_pen_recupere = 0;

  // Récuperation des détails objet demande
  $det_dem = getDetailsObjCredit();

  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC))
  {
    $dossier = array();
    $dossier["id_doss"] = $tmprow["id_doss"];
    $dossier["id_ag"] = $tmprow["id_ag"];
    $dossier["gs_cat"] = $tmprow["gs_cat"];
    $dossier["id_client"] = $tmprow["id_client"];
    $dossier["id_prod"] = $tmprow["id_prod"];
    $dossier["obj_dem"] = $tmprow["libel"];

    if (isDcrDetailObjCreditLsb()) {
      $dossier['detail_obj_dem'] = $det_dem[$tmprow['detail_obj_dem_bis']]['libel'];
    } else {
      $dossier["detail_obj_dem"] = $tmprow["detail_obj_dem"];
    }

    $dossier["etat"] = $tmprow["etat"];

    // AT-133
//    if ($dossier ["etat"] == 6) {
//      $dossier ["mnt_perte"] = afficheMontant(($dataRecouvrement [$tmprow ["id_doss"]] ['solde_cap_rec']), false, $typ_raport = true);
//    } else {
      $dossier ["mnt_perte"] = afficheMontant(recupMontant($tmprow ["perte_capital"]), false, $typ_raport = true);
//    }

    $dossier["date"] = pg2phpDate($tmprow["cre_date_etat"]);
    $dossier["devise"] = $tmprow["devise"];
    $dossier["prov_mnt"] = $tmprow["prov_mnt"];
    $dossier["prov_date"] = pg2phpDate($tmprow["prov_date"]);
    $dossier["id_dcr_grp_sol"] = $tmprow["id_dcr_grp_sol"];

    // Recherche d'éventuels recouvrements pour ce crédit
    //$sql = "SELECT sum(c.montant) FROM ad_his a, ad_ecriture b, ad_mouvement c, ad_cpt_ope d ";
    //$sql .= "WHERE a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = d.id_ag and d.id_ag = $global_id_agence ";
    //$sql .= "AND a.id_his = b.id_his AND b.id_ecriture = c.id_ecriture AND b.libel_ecriture = d.libel_ope ";
    //$sql .= "AND d.type_operation = 410 AND c.sens = 'd' AND a.infos = '" . $dossier["id_doss"] . "'";

    /* $result2 = $db->query($sql);
       if (DB :: isError($result2)) {
       $dbHandler->closeConnection(false);
       signalErreur(__FILE__, __LINE__, __FUNCTION__);
       }*/
    // $mnt_rec = $result2->fetchrow();

    $dossier["mnt_rec"] = $dataRecouvrement[$tmprow["id_doss"]]['solde_cap_rec'];
    $dossier["int_rec"] = $dataRecouvrement[$tmprow["id_doss"]]['solde_int_rec'];
    //ticket 412
    $dossier["pen_rec"] = $dataRecouvrement[$tmprow["id_doss"]]['solde_pen_rec'];

    if ($global_multidevise) {
      $total_perte += calculeCV($dossier["devise"], $global_monnaie, $dossier["mnt_perte"]);
      // ticket 412 :$total_perte_recouvre += calculeCV($dossier["devise"], $global_monnaie, $dossier["mnt_rec"]);
      $total_perte_recouvre += calculeCV($dossier["devise"], $global_monnaie, $dossier["mnt_rec"] + $dossier["int_rec"] + $dossier["pen_rec"]);
      $total_prov_mnt += calculeCV($dossier["devise"], $global_monnaie, $dossier["prov_mnt"]);
      //AT-51 : Total pour cepital. interet et penalite
      $total_cap_recupere += calculeCV($dossier["devise"], $global_monnaie, $dossier["mnt_rec"]);
      $total_int_recupere += calculeCV($dossier["devise"], $global_monnaie, $dossier["int_rec"]);
      $total_pen_recupere += calculeCV($dossier["devise"], $global_monnaie, $dossier["pen_rec"]);
    } else {
      $total_perte += $dossier["mnt_perte"];
      // ticket 412
      $total_perte_recouvre += ($dossier["mnt_rec"] + $dossier["int_rec"] + $dossier["pen_rec"]);

      $total_prov_mnt += $dossier["prov_mnt"];
      $total_int_recouvre += $dossier["int_rec"];
      //AT-51 : Total pour cepital. interet et penalite
      $total_cap_recupere += $dossier["mnt_rec"];
      $total_int_recupere += $dossier["int_rec"];
      $total_pen_recupere += $dossier["pen_rec"];
    }

    //array_push($DETAILS, $dossier);
    // recuperation du crédit solidaire à dossiers multiples

    $groupe = getCreditSolDetailRap($tmprow);

    if ((is_array($groupe["credit_gs"])) && (!in_array($groupe["credit_gs"]["id_client"], $tabGS))) {
      $tabGS[] = $groupe["credit_gs"]["id_client"];
      array_push($DETAILS, $groupe["credit_gs"]);
    }

    if (($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($tmprow["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"]))
      $dossier["membre"] = 1;
    else
      $dossier["membre"] = 0;

    array_push($DETAILS, $dossier);
    //récuperation des crédits des membres d'un groupe solidaire  à dossier unique
    if (is_array($groupe[$tmprow["id_client"]])) {
      $i = 0;
      while ($i < count($groupe[$tmprow["id_client"]])) {
        array_push($DETAILS, $groupe[$tmprow["id_client"]][$i]);
        $i++;
      }
    }
  }

  $INFOS["DETAILS"] = $DETAILS;
  $INFOS["TOTAL"] = $total_perte;
  $INFOS["TOTAL_rec"] = $total_perte_recouvre;
  $INFOS["TOTAL_int_rec"] = $total_int_recouvre;
  $INFOS["TOTAL_prov_mnt"] = $total_prov_mnt;
  //AT-51 : Total capital, interet et penalite
  $INFOS["TOTAL_cap_rec"] = $total_cap_recupere;
  $INFOS["TOTAL_int_rec"] = $total_int_recouvre;
  $INFOS["TOTAL_pen_rec"] = $total_pen_recupere;
  $dbHandler->closeConnection(true);

  return $INFOS;

}
/*
function getCreditsPerte($date1, $date2, $gestionnaire = 0) {
  // Fonction qui renvoie des informations sur les crédits passés en perte
  // Infos utilisées pour la génération du rapport 'Crédits passés en perte'
  // IN : $date1 = Début de période
  //      $date2 = Fin de période
  // OUT: array ("TOTAL" => Montant total passé en perte
  //             "DETAILS" = array ("id_doss" = ID du dossier
  //                                "id_client" = ID du client
  //                                "nom_client" = Nom du client
  //                                "id_prod" = ID du produit de crédit associé
  //                                "objet_dem" = Objet de la demande de crédit
  //                                "mnt_perte" = Montant de la perte liée à ce crédit
  //                                "date" = Date du passage en perte
  //                                "mnt_rec" = Montant d'évezntuels recouvrements
  //$gestionnaire:  utilisateur gestionnaire de crédit
  global $dbHandler;
  global $global_monnaie;
  global $global_multidevise,$global_id_agence;
  $tabGS = array();
  $db = $dbHandler->openConnection();
  if ($gestionnaire > 0)
    $sql = "SELECT * FROM ad_dcr a, adsys_objets_credits b, adsys_produit_credit c WHERE c.id_ag = b.id_ag AND b.id_ag = a.id_ag AND a.id_ag = $global_id_agence AND a.obj_dem = b.id AND a.etat = 9 AND a.id_prod = c.id AND a.id_agent_gest=$gestionnaire AND date_etat BETWEEN '$date1' AND '$date2' ";
  else
    $sql = "SELECT * FROM ad_dcr a,adsys_objets_credits b, adsys_produit_credit c WHERE c.id_ag = b.id_ag AND b.id_ag = a.id_ag AND a.id_ag = $global_id_agence AND a.obj_dem = b.id AND a.etat = 9 AND a.id_prod = c.id AND date_etat BETWEEN '$date1' AND '$date2' ";
  $sql.=" ORDER BY a.id_doss ,date_etat ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $DETAILS = array ();
  $total_perte = 0;
  $total_perte_recouvre = 0;
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $dossier = array ();
    $dossier["id_doss"] 		= $tmprow["id_doss"];
    $dossier["id_ag"] 			= $tmprow["id_ag"];
    $dossier["gs_cat"] 			= $tmprow["gs_cat"];
    $dossier["id_client"] 		= $tmprow["id_client"];
    $dossier["id_prod"] 		= $tmprow["id_prod"];
    $dossier["obj_dem"] 		= $tmprow["libel"];
    $dossier["detail_obj_dem"] 	= $tmprow["detail_obj_dem"];
    $dossier["detail_obj_dem_bis"] 	= $tmprow["detail_obj_dem_bis"];
    $dossier["mnt_perte"] 		= afficheMontant(recupMontant($tmprow["perte_capital"]), false, $typ_raport = true);
    $dossier["date"] 			= pg2phpDate($tmprow["date_etat"]);
    $dossier["devise"] 			= $tmprow["devise"];
    $dossier["prov_mnt"] 		= $tmprow["prov_mnt"];
    $dossier["prov_date"] 			= pg2phpDate($tmprow["prov_date"]);
    // Recherche d'éventuels recouvrements pour ce crédit
    $sql = "SELECT sum(c.montant) FROM ad_his a, ad_ecriture b, ad_mouvement c, ad_cpt_ope d ";
    $sql .= "WHERE a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = d.id_ag and d.id_ag = $global_id_agence ";
    $sql .= "AND a.id_his = b.id_his AND b.id_ecriture = c.id_ecriture AND b.libel_ecriture = d.libel_ope ";
    $sql .= "AND d.type_operation = 410 AND c.sens = 'd' AND a.infos = '" . $dossier["id_doss"] . "'";

    $result2 = $db->query($sql);
    if (DB :: isError($result2)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $mnt_rec = $result2->fetchrow();
    $dossier["mnt_rec"] = $mnt_rec[0];
    if ($global_multidevise)
      $total_perte += calculeCV($dossier["devise"], $global_monnaie, $dossier["mnt_perte"]);
    else
      $total_perte += $dossier["mnt_perte"];
    if ($global_multidevise)
      $total_perte_recouvre += calculeCV($dossier["devise"], $global_monnaie, $dossier["mnt_rec"]);
    else
      $total_perte_recouvre += $dossier["mnt_rec"];
    if ($global_multidevise)
      $total_prov_mnt += calculeCV($dossier["devise"], $global_monnaie, $dossier["prov_mnt"]);
    else
      $total_prov_mnt += $dossier["prov_mnt"];


    //array_push($DETAILS, $dossier);
    //recuperation du crédit solidaire à dossiers multiples
    $groupe = getCreditSolDetailRap($tmprow);
    if((is_array($groupe["credit_gs"]))&&(!in_array($groupe["credit_gs"]["id_client"],$tabGS))){
    	$tabGS[]  = $groupe["credit_gs"]["id_client"];
    	array_push($DETAILS,$groupe["credit_gs"]);
    }
    if(($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($tmprow["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"])) $dossier["membre"] = 1;
    else $dossier["membre"] = 0;
    array_push($DETAILS, $dossier);
    //récuperation des crédits des membres d'un groupe solidaire  à dossier unique
    if(is_array($groupe[$tmprow["id_client"]])) {
    	$i = 0;
    	while($i < count($groupe[$tmprow["id_client"]])) {
    		array_push($DETAILS,$groupe[$tmprow["id_client"]][$i]);
    		$i++;
    	}
    }
  }
  $INFOS["DETAILS"] = $DETAILS;
  $INFOS["TOTAL"] = $total_perte;
  $INFOS["TOTAL_rec"] = $total_perte_recouvre;
  $INFOS["TOTAL_prov_mnt"] = $total_prov_mnt;
  $dbHandler->closeConnection(true);
  return $INFOS;
}
*/
function getExtraitJournalComptable($CRIT) {
  // Fonction renvoyant un extrait de l'historique comptable selon les critères spécifiés
  // Utilisés pour le rapport 'Exportation du journal comptable'
  // IN : $CRIT = array("date_deb" = Date du début de l'extraction
  //                    "date_fin" = Date de fin de l'extraction
  //                    "type_operation" = Type d'opération à extraire
  //                    "compte" = Numéro de compte à extraire
  //                    "devise" = Devise du mouvement
  // OUT: $DATA = array( index => array(
  //                          "date_comptable" = Date et heure de l'opération
  //                          "libel_operation" = Libellé de l'opération
  //                          "compte" = Compte comptable mouvmementé
  //                          "sens" = Débit (d) ou crédit (c)
  //                          "montant" = Montant de l'opération
  //                          "devise" = Devise de l'opération
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  /*$sql = "SELECT a.id_ecriture, a.id_his, c.date, b.compte, b.sens, b.montant, c.id_client, b.devise, a.libel_ecriture,a.type_operation,a.info_ecriture FROM ad_ecriture a ,ad_mouvement b, ad_his c WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = $global_id_agence AND a.id_ecriture=b.id_ecriture AND a.id_his= c.id_his";
  if (isset ($CRIT["date_deb"]))
    $sql .= " AND date(date_comptable) >= '" . $CRIT["date_deb"] . "'";
  if (isset ($CRIT["date_fin"]))
    $sql .= " AND date(date_comptable) <= '" . $CRIT["date_fin"] . "'";
  if ($CRIT["type_operation"] > 0)
    $sql .= " AND libel_ecriture = (SELECT libel_ope FROM ad_cpt_ope WHERE type_operation = " . $CRIT["type_operation"] . ")";
  if ($CRIT["compte"] != '')
    $sql .= " AND (compte='" . $CRIT["compte"] . "' OR compte LIKE '" . $CRIT["compte"] . ".%')";
  if ($CRIT["devise"] != '')
    $sql .= " AND devise = '" . $CRIT["devise"] . "'";
    $sql.=" order by c.date,a.id_ecriture "; print_rn($sql);*/

  $compte = $CRIT["compte"];
  $type_ope = $CRIT["type_operation"];
  $date_deb =$CRIT["date_deb"] ;
  $date_fin = $CRIT["date_fin"];
  if ($compte == '' && $type_ope == ''){
    $sql = "select * from getdatajournalcomptable(NULL,NULL,'$date_deb','$date_fin',numagc()) WHERE id_ag = numagc() ";
  }else if ($compte != '' && $type_ope == ''){
    $sql = "select * from getdatajournalcomptable('$compte',NULL,'$date_deb','$date_fin',numagc()) WHERE id_ag = numagc() ";
  }else if ($compte == '' && $type_ope != ''){
    $sql = "select * from getdatajournalcomptable(NULL,$type_ope,'$date_deb','$date_fin',numagc()) WHERE id_ag = numagc() ";
  }
  else{
    $sql = "select * from getdatajournalcomptable('$compte',$type_ope,'$date_deb','$date_fin',numagc()) WHERE id_ag = numagc() ";
  }
  /*if ($CRIT["type_operation"] > 0){
    $sql .= " AND libel_ecriture = (SELECT libel_ope FROM ad_cpt_ope WHERE type_operation = " . $CRIT["type_operation"] . ")";
  }*/
  if ($CRIT["devise"] != ''){
    $sql .= " AND devise = '" . $CRIT["devise"] . "'";
  }

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $DATA = array ();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($DATA, $row);
  }
  $dbHandler->closeConnection(true);
  return $DATA;
}

/**
 * Renvoie le nombre de clients pour le registre de prêts sur une période donnée
 * @author Djibril NIANG
 * @since 3.0.6
 * @param date_debloc_inf borne inférieure
 * @param date_debloc_sup borne supérieure
 * @return INT $nombre_cli : le nombre de clients
**/
function getNbreClientsRegistrePret($date_debloc_inf, $date_debloc_sup){
	global $dbHandler, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();

	$sql = "SELECT count(d.id_client) ";
  $sql .= " FROM ad_dcr d, ad_cli c, adsys_produit_credit p ";
  $sql .= " WHERE d.id_ag = c.id_ag AND c.id_ag = p.id_ag AND p.id_ag = $global_id_agence ";
  $sql .= " AND d.id_prod = p.id AND d.id_client = c.id_client";
  $sql .= " AND d.cre_date_debloc >= date('$date_debloc_inf') AND d.cre_date_debloc <= date('$date_debloc_sup') ";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
	  signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$row = $result->fetchrow();
	$nombre_cli = $row[0];
	$dbHandler->closeConnection(true);
	return $nombre_cli;
}
/**
 * Recherche dans la BD les informations synthétiques pour le rapport Extrait du registre des crédits en cours
 *
 * @param array $CRITERE Les critères de sélection des crédits :<ul>
 *    <li>      "produit"          => produit de crédit
 *    <li>      "objet"            => objet de crédit
 *    <li>      "date_debloc_inf"  => Date inférieure pour l'octroi du crédit
 *    <li>      "date_debloc_sup"  => Date supérieure pour l'octroi du crédit
 *    <li>      "duree_mois"       => Durée  du crédit
 *    <li>      "type_duree"       => Type Durée  de crédit
 *    <li>      "cre_mnt_octr"     => Montant octroyé
 *    <li>      "localisation"     => ID de la localité du client bénéficiaire
 *    <li>      "statut "          => Satut juridique du client
 *    <li>      "pp_sexe"          => Le genre du membre bénéficiaire
 *    <li>      "sect_act"         => Secteur d'activité de bénéficiaire
 *    <li>      "nb_reech"         => Le nombre de rééchelonnement
 *    <li>      "etat_dossier"     => L'état du dossier
 * </ul>
 * @param int $devise La devise de recherche, NULL si en mono devise
 * @param int $gestionnaire: l'utilisateur gestionnaire de crédit
 * @access public
 * @param int $devise La devise de recherche, NULL si en mono devise
 * @param int $gestionnaire: l'utilisateur gestionnaire de crédit
 * @access public
 * @return array Un tableau associatifs des données utiles des crédits trouvés :<ul>
 *    <li>         "id_doss"        => ID du dossier de crédit
 *    <li>         "id_client"      => Id du client
 *    <li>         "pp_nom"         => Nom du client
 *    <li>         "pp_prenom"      => Prénom du client
 *    <li>         "prod"           => Libellé du produit de crédit
 *    <li>         "devise"         => Devise du produit de crédit
 *    <li>         "cre_mnt_octr"   => Montant octroyé
 *    <li>         "cre_mnt_deb"    => Montant déboursé
 *    <li>         "cre_date_debloc"=> Date de déboursement
 *    <li>         "duree_mois"     => Durée du crédit
 *    <li>         "cre_etat"       => Etat du crédit
 *    <li>         "cre_remb_cap"   => Montant du capital remboursé
 *    <li>         "cre_remb_int"   => Montant des intérêts remboursés
 *    <li>         "cre_remb_gar"   => Montant de la garantie remboursée
 *    <li>         "cre_remb_pen"   => Montant des pénalités remboursées
 * </ul>
 */
function getRegistreCreditInfoSynth($CRITERE, $devise = NULL, $gestionnaire = 0, $export_csv = false) {
  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();
  global $adsys;
  $data_agence = getAgenceDatas($global_id_agence);

  if ($CRITERE['statut'] == 4) { // specific uniquement pour les statut juridique : Groupe solidaire.
    //récupère les infos sur le nombre de credit, montant octroyé, capital restant dû
    $sql = "SELECT count(d1.id_doss) as nbr_cred, sum(calculeCV(d1.cre_mnt_octr, devise, '$global_monnaie')) as tot_cre_mnt_octr,sum(d1.perte_capital) as tot_perte_capital, ";
    $sql .= "sum(cap_rest) as tot_cap_rest, sum(pen_rest) as tot_pen_rest, sum(int_rest) as tot_int_rest, sum(gar_rest) as tot_gar_rest, sum(calculeCV(d1.cre_mnt_deb, devise, '$global_monnaie')) as tot_cre_mnt_deb, sum(calculeCV(d1.prov_mnt, devise, '$global_monnaie')) as tot_prov_mnt ";
    $sql .= "from ad_dcr as d1, (SELECT  ad_etr.id_doss, ad_etr.id_ag, p.devise, sum(calculeCV(solde_cap, devise, '$global_monnaie')) as cap_rest, ";
    $sql .= "sum(calculeCV(solde_pen, devise, '$global_monnaie')) as pen_rest,sum(calculeCV(solde_int, devise, '$global_monnaie')) as int_rest , ";
    $sql .= "sum(calculeCV(solde_gar, devise, '$global_monnaie')) as gar_rest FROM ad_cli c, ad_dcr d, adsys_produit_credit p, ad_etr, ad_grp_sol sol ";
    $sql .= "WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=d.id_ag AND d.id_ag=p.id_ag AND p.id_ag=c.id_ag AND ad_etr.id_doss = d.id_doss AND d.id_client = sol.id_membre AND d.is_ligne_credit='f' ";
    $sql .= "AND d.id_prod = p.id AND d.id_client = c.id_client ";
  }
  else {
    //récupère les infos sur le nombre de credit, montant octroyé, capital restant dû
    $sql = "SELECT count(d1.id_doss) as nbr_cred, sum(calculeCV(d1.cre_mnt_octr, devise, '$global_monnaie')) as tot_cre_mnt_octr,sum(d1.perte_capital) as tot_perte_capital, ";
    $sql .= "sum(cap_rest) as tot_cap_rest, sum(pen_rest) as tot_pen_rest, sum(int_rest) as tot_int_rest, sum(gar_rest) as tot_gar_rest, sum(calculeCV(d1.cre_mnt_deb, devise, '$global_monnaie')) as tot_cre_mnt_deb, sum(calculeCV(d1.prov_mnt, devise, '$global_monnaie')) as tot_prov_mnt ";
    $sql .= "from ad_dcr as d1, (SELECT  ad_etr.id_doss, ad_etr.id_ag, p.devise, sum(calculeCV(solde_cap, devise, '$global_monnaie')) as cap_rest, ";
    $sql .= "sum(calculeCV(solde_pen, devise, '$global_monnaie')) as pen_rest,sum(calculeCV(solde_int, devise, '$global_monnaie')) as int_rest , ";
    $sql .= "sum(calculeCV(solde_gar, devise, '$global_monnaie')) as gar_rest FROM ad_cli c, ad_dcr d, adsys_produit_credit p, ad_etr ";
    $sql .= "WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=d.id_ag AND d.id_ag=p.id_ag AND p.id_ag=c.id_ag AND ad_etr.id_doss = d.id_doss  AND d.is_ligne_credit='f' ";
    $sql .= "AND d.id_prod = p.id AND d.id_client = c.id_client ";
  }
  //crières de recherche
  $sql_crit = "";
  if (($global_multidevise) && ($devise != NULL))
    $sql_crit .= " AND p.devise= '" . $devise . "'  ";
  if ($CRITERE['statut'] != 4){
    if ($CRITERE['statut'] > 0)
      $sql_crit .= " AND (c.statut_juridique=" . $CRITERE['statut'] . ")  ";
  }
  // AT-79 : Evolution rapport Registre de prêts après AT-33
  if ($data_agence['identification_client'] == 2) {
    if ($CRITERE['localisation_main'] == 1) {
      if ($CRITERE['crit_loc'] != 0){
        $sql .= " AND c.province = " . $CRITERE['crit_loc'] . "  ";
      }else{
        $sql .= " AND c.province IS NOT NULL  ";
      }
    } elseif ($CRITERE['localisation_main'] == 2 ) {
      if ($CRITERE['crit_loc'] != 0){
        $sql .= " AND c.district =" . $CRITERE['crit_loc'] . "  ";
      }else{
        $sql .= " AND c.district IS NOT NULL  ";
      }
    } elseif ($CRITERE['localisation_main'] == 3) {
      if ($CRITERE['crit_loc'] != 0){
        $sql .= " AND  c.secteur =" . $CRITERE['crit_loc'] . "  ";
      }else{
        $sql .= " AND c.secteur IS NOT NULL  ";
      }
    } elseif ($CRITERE['localisation_main'] == 4) {
      if ($CRITERE['crit_loc'] != 0){
        $sql .= " AND c.cellule =" . $CRITERE['crit_loc'] . "  ";
      }else{
        $sql .= " AND c.cellule IS NOT NULL AND ";
      }
    } elseif ($CRITERE['localisation_main'] == 5 ) {
      if ($CRITERE['crit_loc'] != 0) {
        $sql .= " AND  c.village =" . $CRITERE['crit_loc'] . "  ";
      } else {
        $sql .= " AND c.village IS NOT NULL  ";
      }
    }
    else {
      $sql .= " AND c.province is null and c.district is null and c.secteur is null and c.cellule is null and c.village is null  ";
    }
  }else {
    if ($CRITERE['localisation'] > 0)
      $sql_crit .= " AND (c.id_loc1=" . $CRITERE['localisation'] . " or c.id_loc2=" . $CRITERE['localisation'] . ") ";
  }
  if ($CRITERE['pp_sexe'] > 0)
    $sql_crit .= " AND (c.pp_sexe=" . $CRITERE['pp_sexe'] . ")  ";
  if ($CRITERE['sect_act'] > 0)
    $sql_crit .= " AND (c.sect_act=" . $CRITERE['sect_act'] . ")  ";
  if ($CRITERE['produit'] > 0)
    $sql_crit .= " AND (d.id_prod=" . $CRITERE['produit'] . ")  ";
  if ($CRITERE['objet'] > 0)
    $sql_crit .= " AND (d.obj_dem=" . $CRITERE['objet'] . ")  ";
  //La date de déblocage n'est renseigné que si le crédit est déboursé donc etat >= 5
  if ($CRITERE['date_debloc_inf'] != ''){
  		$sql_crit .= " AND (d.cre_date_debloc >= date('" . $CRITERE['date_debloc_inf'] . "'))  ";
  }
	if ($CRITERE['date_debloc_sup'] != '') {
	  $date = splitEuropeanDate($CRITERE['date_debloc_sup']);
	  $date2 = date("d/m/Y", mktime(0, 0, 0, $date[1], $date[0] + 1, $date[2]));
	  $sql_crit .= " AND (d.cre_date_debloc < date('" . $date2 . "'))  ";
	}

  if ($CRITERE['type_duree'] > 0)
    $sql_crit .= " AND p.type_duree_credit = " . $CRITERE['type_duree'] . "  ";
  if ($CRITERE['duree_mois'] > 0)
    $sql_crit .= " AND d.duree_mois = " . $CRITERE['duree_mois'] . "  ";
  if ($CRITERE['cre_mnt_octr'] > 0)
    $sql_crit .= " AND d.cre_mnt_octr = " . $CRITERE['cre_mnt_octr'] . "  ";
  if ($CRITERE['nb_reech'] > 0)
    $sql_crit .= " AND d.cre_nbre_reech = " . $CRITERE['nb_reech'] . "  ";
  if ($CRITERE['etat_dossier'] != "")
    $sql_crit .= " AND d.etat IN (" . $CRITERE['etat_dossier'] . ")  ";


  if ($gestionnaire > 0)
    $sql_crit .= " AND d.id_agent_gest=$gestionnaire ";

	$sql .= $sql_crit;
  $sql .= " GROUP BY  ad_etr.id_doss, ad_etr.id_ag, p.devise) as ech ";
  $sql .= " WHERE d1.id_ag = ech.id_ag AND d1.id_doss = ech.id_doss ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $RESULTAT = array ();
  $RESULTAT["nbr_credit"] = $row["nbr_cred"];
  $RESULTAT["mnt_octr"] = $row["tot_cre_mnt_octr"];
  $RESULTAT["mnt_deb"] = $row["tot_cre_mnt_deb"];
  $RESULTAT["capital_rest_du"] = $row["tot_cap_rest"];
  $RESULTAT["capital_perte"] = $row["tot_perte_capital"];
  $RESULTAT["int_du"] = $row["tot_int_rest"];
  $RESULTAT["prov_mnt"] = $row["tot_prov_mnt"];

  //récupère les infos sur le nombre de credit, montant octroyé, capital restant dû - ligne de crédit
  $sql = "SELECT count(d1.id_doss) as nbr_cred, sum(calculeCV(d1.cre_mnt_octr, devise, '$global_monnaie')) as tot_cre_mnt_octr,sum(d1.perte_capital) as tot_perte_capital, ";
  $sql .= "sum(cap_rest) as tot_cap_rest, sum(pen_rest) as tot_pen_rest, sum(int_rest) as tot_int_rest, sum(gar_rest) as tot_gar_rest, sum(calculeCV(d1.cre_mnt_deb, devise, '$global_monnaie')) as tot_cre_mnt_deb, sum(calculeCV(d1.prov_mnt, devise, '$global_monnaie')) as tot_prov_mnt ";
  $sql .= "from ad_dcr as d1, (SELECT  ad_etr.id_doss, ad_etr.id_ag, p.devise, sum(calculeCV(solde_cap, devise, '$global_monnaie')) as cap_rest, ";
  $sql .= "sum(calculeCV(solde_pen, devise, '$global_monnaie')) as pen_rest,sum(calculeCV(solde_int, devise, '$global_monnaie')) as int_rest , ";
  $sql .= "sum(calculeCV(solde_gar, devise, '$global_monnaie')) as gar_rest FROM ad_cli c, ad_dcr d, adsys_produit_credit p, ad_etr ";
  $sql .= "WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=d.id_ag AND d.id_ag=p.id_ag AND p.id_ag=c.id_ag AND ad_etr.id_doss = d.id_doss AND d.is_ligne_credit='t' ";
  $sql .= "AND d.id_prod = p.id AND d.id_client = c.id_client ";

  $sql .= $sql_crit;
  $sql .= " GROUP BY  ad_etr.id_doss, ad_etr.id_ag, p.devise) as ech ";
  $sql .= " WHERE d1.id_ag = ech.id_ag AND d1.id_doss = ech.id_doss ";

  $result1 = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row1 = $result1->fetchrow(DB_FETCHMODE_ASSOC);

  $RESULTAT["nbr_credit"] += $row1["nbr_cred"];
  $RESULTAT["mnt_octr"] += $row1["tot_cre_mnt_octr"];
  $RESULTAT["mnt_deb"] += $row1["tot_cre_mnt_deb"];
  $RESULTAT["capital_rest_du"] += $row1["tot_cap_rest"];
  $RESULTAT["capital_perte"] += $row1["tot_perte_capital"];
  $RESULTAT["int_du"] += $row1["tot_int_rest"];
  $RESULTAT["prov_mnt"] += $row1["tot_prov_mnt"];
  
  //récupère les données de remboursement
  if ($CRITERE['statut'] == 4) {
    $sql = "SELECT count(d1.id_doss) as nbr_cred, sum(calculeCV(d1.cre_mnt_octr, devise, '$global_monnaie')) as tot_cre_mnt_octr, ";
    $sql .= "sum(mnt_remb_cap) as tot_mnt_remb_cap, sum(mnt_remb_pen) as tot_mnt_remb_pen, sum(mnt_remb_int) as tot_mnt_remb_int, sum(mnt_remb_gar) as tot_mnt_remb_gar, sum(calculeCV(d1.cre_mnt_deb, devise, '$global_monnaie')) as tot_cre_mnt_deb ";
    $sql .= "from ad_dcr as d1, (SELECT  ad_sre.id_doss, ad_sre.id_ag, p.devise, sum(calculeCV(mnt_remb_cap, devise, '$global_monnaie')) as mnt_remb_cap, ";
    $sql .= "sum(calculeCV(mnt_remb_pen, devise, '$global_monnaie')) as mnt_remb_pen,sum(calculeCV(mnt_remb_int, devise, '$global_monnaie')) as mnt_remb_int , ";
    $sql .= "sum(calculeCV(mnt_remb_gar, devise, '$global_monnaie')) as mnt_remb_gar FROM ad_cli c, ad_dcr d, adsys_produit_credit p, ad_sre, ad_grp_sol sol ";
    $sql .= "WHERE ad_sre.id_ag=$global_id_agence AND ad_sre.id_ag=d.id_ag AND d.id_ag=p.id_ag AND p.id_ag=c.id_ag AND ad_sre.id_doss = d.id_doss AND d.id_client = sol.id_membre ";
    $sql .= "AND d.id_prod = p.id AND d.id_client = c.id_client AND d.is_ligne_credit='f' ";

    $sql .= $sql_crit;
    $sql .= " GROUP BY  ad_sre.id_doss, ad_sre.id_ag, p.devise) as ech ";
    $sql .= " WHERE d1.id_ag = ech.id_ag AND d1.id_doss = ech.id_doss ";
  }
  else {
    $sql = "SELECT count(d1.id_doss) as nbr_cred, sum(calculeCV(d1.cre_mnt_octr, devise, '$global_monnaie')) as tot_cre_mnt_octr, ";
    $sql .= "sum(mnt_remb_cap) as tot_mnt_remb_cap, sum(mnt_remb_pen) as tot_mnt_remb_pen, sum(mnt_remb_int) as tot_mnt_remb_int, sum(mnt_remb_gar) as tot_mnt_remb_gar, sum(calculeCV(d1.cre_mnt_deb, devise, '$global_monnaie')) as tot_cre_mnt_deb ";
    $sql .= "from ad_dcr as d1, (SELECT  ad_sre.id_doss, ad_sre.id_ag, p.devise, sum(calculeCV(mnt_remb_cap, devise, '$global_monnaie')) as mnt_remb_cap, ";
    $sql .= "sum(calculeCV(mnt_remb_pen, devise, '$global_monnaie')) as mnt_remb_pen,sum(calculeCV(mnt_remb_int, devise, '$global_monnaie')) as mnt_remb_int , ";
    $sql .= "sum(calculeCV(mnt_remb_gar, devise, '$global_monnaie')) as mnt_remb_gar FROM ad_cli c, ad_dcr d, adsys_produit_credit p, ad_sre ";
    $sql .= "WHERE ad_sre.id_ag=$global_id_agence AND ad_sre.id_ag=d.id_ag AND d.id_ag=p.id_ag AND p.id_ag=c.id_ag AND ad_sre.id_doss = d.id_doss ";
    $sql .= "AND d.id_prod = p.id AND d.id_client = c.id_client AND d.is_ligne_credit='f' ";

    $sql .= $sql_crit;
    $sql .= " GROUP BY  ad_sre.id_doss, ad_sre.id_ag, p.devise) as ech ";
    $sql .= " WHERE d1.id_ag = ech.id_ag AND d1.id_doss = ech.id_doss ";
  }
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $RESULTAT["tot_mnt_remb_cap"] = $row["tot_mnt_remb_cap"];
  $RESULTAT["tot_mnt_remb_int"] = $row["tot_mnt_remb_int"];
  $RESULTAT["tot_mnt_remb_pen"] = $row["tot_mnt_remb_pen"];
  $RESULTAT["tot_mnt_remb_gar"] = $row["tot_mnt_remb_gar"];
  
  //récupère les données de remboursement - ligne de crédit
  $sql = "SELECT 0 as tot_mnt_remb_cap, sum(mnt_remb_pen) as tot_mnt_remb_pen, sum(mnt_remb_int) as tot_mnt_remb_int, sum(mnt_remb_gar) as tot_mnt_remb_gar ";
  $sql .= "from ad_dcr as d1, (SELECT  ad_sre.id_doss, ad_sre.id_ag, p.devise, sum(calculeCV(mnt_remb_cap, devise, '$global_monnaie')) as mnt_remb_cap, ";
  $sql .= "sum(calculeCV(mnt_remb_pen, devise, '$global_monnaie')) as mnt_remb_pen,sum(calculeCV(mnt_remb_int, devise, '$global_monnaie')) as mnt_remb_int , ";
	$sql .= "sum(calculeCV(mnt_remb_gar, devise, '$global_monnaie')) as mnt_remb_gar FROM ad_cli c, ad_dcr d, adsys_produit_credit p, ad_sre ";
	$sql .= "WHERE ad_sre.id_ag=$global_id_agence AND ad_sre.id_ag=d.id_ag AND d.id_ag=p.id_ag AND p.id_ag=c.id_ag AND ad_sre.id_doss = d.id_doss ";
	$sql .= "AND d.id_prod = p.id AND d.id_client = c.id_client AND d.is_ligne_credit='t' ";

  $sql .= $sql_crit;
  $sql .= " GROUP BY  ad_sre.id_doss, ad_sre.id_ag, p.devise) as ech ";
  $sql .= " WHERE d1.id_ag = ech.id_ag AND d1.id_doss = ech.id_doss ";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row2 = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $RESULTAT["tot_mnt_remb_cap"] += $row2["tot_mnt_remb_cap"];
  $RESULTAT["tot_mnt_remb_int"] += $row2["tot_mnt_remb_int"];
  $RESULTAT["tot_mnt_remb_pen"] += $row2["tot_mnt_remb_pen"];
  $RESULTAT["tot_mnt_remb_gar"] += $row2["tot_mnt_remb_gar"];

  $dbHandler->closeConnection(true);
  return $RESULTAT;

}
/**
 * Recherche dans la BD les données pour le rapport Extrait du registre des crédits en cours
 *
 * @param array $CRITERE Les critères de sélection des crédits :<ul>
 *    <li>      "produit"          => produit de crédit
 *    <li>      "objet"            => objet de crédit
 *    <li>      "date_debloc_inf"  => Date inférieure pour l'octroi du crédit
 *    <li>      "date_debloc_sup"  => Date supérieure pour l'octroi du crédit
 *    <li>      "duree_mois"       => Durée  du crédit
 *    <li>      "type_duree"       => Type Durée  de crédit
 *    <li>      "cre_mnt_octr"     => Montant octroyé
 *    <li>      "cre_mnt_deb"      => Montant déboursé
 *    <li>      "localisation"     => ID de la localité du client bénéficiaire
 *    <li>      "statut "          => Satut juridique du client
 *    <li>      "pp_sexe"          => Le genre du membre bénéficiaire
 *    <li>      "sect_act"         => Secteur d'activité de bénéficiaire
 *    <li>      "nb_reech"         => Le nombre de rééchelonnement
 *    <li>      "etat_dossier"     => L'état du dossier
 * </ul>
 * @param int $devise La devise de recherche, NULL si en mono devise
 * @param int $gestionnaire: l'utilisateur gestionnaire de crédit
 * @access public
 * @return array Un tableau associatifs des données utiles des crédits trouvés :<ul>
 *    <li>         "id_doss"        => ID du dossier de crédit
 *    <li>         "id_client"      => Id du client
 *    <li>         "pp_nom"         => Nom du client
 *    <li>         "pp_prenom"      => Prénom du client
 *    <li>         "prod"           => Libellé du produit de crédit
 *    <li>         "devise"         => Devise du produit de crédit
 *    <li>         "cre_mnt_octr"   => Montant octroyé
 *    <li>         "cre_mnt_deb"    => Montant déboursé
 *    <li>         "is_ligne_credit"=> Ligne de crédit?
 *    <li>         "cre_date_debloc"=> Date de déboursement
 *    <li>         "duree_mois"     => Durée du crédit
 *    <li>         "cre_etat"       => Etat du crédit
 *    <li>         "cre_remb_cap"   => Montant du capital remboursé
 *    <li>         "cre_remb_int"   => Montant des intérêts remboursés
 *    <li>         "cre_remb_gar"   => Montant de la garantie remboursée
 *    <li>         "cre_remb_pen"   => Montant des pénalités remboursées
 * </ul>
 *
 * Fonction evoluer par Kheshan : GS 09/2015 ticket 615
 */
function getRegistreCredit($CRITERE, $devise = NULL, $gestionnaire = 0, $export_csv = false) {
  global $dbHandler;
  global $global_multidevise,$global_id_agence;
  $data_agence = getAgenceDatas($global_id_agence);
  set_time_limit(0);
  $db = $dbHandler->openConnection();
  global $adsys;

  if ($CRITERE['statut'] == 4) { // specific uniquement pour les statut juridique : Groupe solidaire.
    $sql =  "select dcr.id_doss, dcr.cre_etat, dcr.id_client, prd.libel, dcr.cre_mnt_octr, dcr.cre_date_debloc, dcr.duree_mois, prd.type_duree_credit, dcr.gs_cat, dcr.id_dcr_grp_sol, dcr.mnt_dem, dcr.id_prod,prov_mnt,
    dcr.cre_mnt_deb, dcr.is_ligne_credit";

    if (($global_multidevise) && ($devise == NULL)) {
      $sql .= " ,prd.devise, dcr.id_ag ";
      $groupbydevise = " ,devise ";
      $groupbyid_agc = " ,d.id_ag ";
    }

    $sql .=" FROM ad_grp_sol sol, ad_dcr dcr, adsys_produit_credit prd, ad_cli cli";
    $sql .=" WHERE sol.id_membre = dcr.id_client and dcr.id_prod = prd.id and dcr.id_client = cli.id_client";
    $sql .=" AND prd.id_ag = $global_id_agence AND ";
    $len = strlen($sql);
    if (($global_multidevise) && ($devise != NULL))
      $sql .= " devise= '" . $devise . "' AND ";
    // AT-79 : Evolution rapport Registre de prêts après AT-33
    if ($data_agence['identification_client'] == 2) {
      if ($CRITERE['localisation_main'] != null) {
        if ($CRITERE['localisation_main'] == 1) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  cli.province = " . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  cli.province IS NOT NULL AND ";
          }
        } elseif ($CRITERE['localisation_main'] == 2) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  cli.district =" . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  cli.district IS NOT NULL AND ";
          }
        } elseif ($CRITERE['localisation_main'] == 3) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  cli.secteur =" . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  cli.secteur IS NOT NULL AND ";
          }
        } elseif ($CRITERE['localisation_main'] == 4) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  cli.cellule =" . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  cli.cellule IS NOT NULL AND ";
          }
        } elseif ($CRITERE['localisation_main'] == 5) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  cli.village =" . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  cli.village IS NOT NULL AND ";
          }

        } else {
          $sql .= "  cli.province is null and cli.district is null and cli.secteur is null and cli.cellule is null and cli.village is null AND";
        }
      }
    }else {
      if ($CRITERE['localisation'] > 0)
        $sql .= " (cli.id_loc1=" . $CRITERE['localisation'] . " or cli.id_loc2=" . $CRITERE['localisation'] . ") AND ";
    }
    if(isFusion()){
        if ($CRITERE['n_agence'] > 0)
            $sql .= " (cli.n_agence=" . $CRITERE['n_agence'] . ") AND ";

        if ($CRITERE['n_guichet'] > 0)
            $sql .= " (cli.n_guichet=" . $CRITERE['n_guichet'] . ") AND ";
    }
    if ($CRITERE['pp_sexe'] > 0)
      $sql .= " (cli.pp_sexe=" . $CRITERE['pp_sexe'] . ") AND ";
    if ($CRITERE['sect_act'] > 0)
      $sql .= " (cli.sect_act=" . $CRITERE['sect_act'] . ") AND ";
    if ($CRITERE['produit'] > 0)
      $sql .= " (dcr.id_prod=" . $CRITERE['produit'] . ") AND ";
    if ($CRITERE['objet'] > 0)
      $sql .= " (dcr.obj_dem=" . $CRITERE['objet'] . ") AND ";
    //La date de déblocage n'est renseigné que si le crédit est déboursé donc etat >= 5
    if ($CRITERE['date_debloc_inf'] != '') {
      $sql .= " (dcr.cre_date_debloc >= date('" . $CRITERE['date_debloc_inf'] . "')) AND ";
    }
    if ($CRITERE['date_debloc_sup'] != '') {
      $date = splitEuropeanDate($CRITERE['date_debloc_sup']);
      $date2 = date("d/m/Y", mktime(0, 0, 0, $date[1], $date[0] + 1, $date[2]));
      $sql .= " (dcr.cre_date_debloc < date('" . $date2 . "')) AND ";
    }
    if ($CRITERE['type_duree'] > 0)
      $sql .= " prd.type_duree_credit = " . $CRITERE['type_duree'] . " AND ";
    if ($CRITERE['duree_mois'] > 0)
      $sql .= " dcr.duree_mois = " . $CRITERE['duree_mois'] . " AND ";
    if ($CRITERE['cre_mnt_octr'] > 0)
      $sql .= " dcr.cre_mnt_octr = " . $CRITERE['cre_mnt_octr'] . " AND ";
    if ($CRITERE['nb_reech'] > 0)
      $sql .= " dcr.cre_nbre_reech = " . $CRITERE['nb_reech'] . " AND ";
    if ($CRITERE['etat_dossier'] != "")
      $sql .= " dcr.etat IN (" . $CRITERE['etat_dossier'] . ") AND ";

    //Enlève le 'AND' ou le 'WHERE'
    if ($len == strlen($sql))
      $sql = substr($sql, 0, strlen($sql) - 6); //Si on a rien ajouté
    else
      $sql = substr($sql, 0, strlen($sql) - 4);
    if ($gestionnaire > 0)
      $sql .= " AND dcr.id_agent_gest=$gestionnaire ";

    if ($CRITERE['etat_dossier'] >= 5) { //date déblocage renseigné
      // On trie par produit de crédit et par date de débloquage
      $sql .= " GROUP BY dcr.id_client, dcr.id_doss, dcr.cre_etat, dcr.id_prod, dcr.cre_date_debloc" . $groupbydevise . ",prd.libel,";
      $sql .= " dcr.cre_mnt_octr, dcr.duree_mois, prd.type_duree_credit, dcr.gs_cat, dcr.id_dcr_grp_sol, dcr.mnt_dem,dcr.prov_mnt, dcr.cre_mnt_deb, dcr.is_ligne_credit " . $groupbyid_agc;
    } else {
      // On trie par produit de crédit
      $sql .= " GROUP BY dcr.id_client, dcr.id_doss, dcr.cre_etat, dcr.id_prod,dcr.cre_date_debloc" . $groupbydevise . ", prd.libel,";
      $sql .= " dcr.cre_mnt_octr, dcr.duree_mois, prd.type_duree_credit, dcr.gs_cat, dcr.id_dcr_grp_sol, dcr.mnt_dem,dcr.prov_mnt, dcr.cre_mnt_deb, dcr.is_ligne_credit " . $groupbyid_agc . " ";
    }
    $sql .= " ORDER BY dcr.id_prod ASC, dcr.id_doss ASC "; // Added : Ticket #201
  }

  else {
    //$etat = isset($CRITERE['etat_dossier'])?$CRITERE['etat_dossier']:'null';

    $sql = "SELECT d.id_doss, d.cre_etat, ";//", sum(s.mnt_remb_cap), sum(s.mnt_remb_int),sum(s.mnt_remb_gar),sum(s.mnt_remb_pen), ";
    $sql .= " d.id_client, d.libel, d.cre_mnt_octr, d.cre_date_debloc, d.duree_mois, d.type_duree_credit, d.gs_cat, d.id_dcr_grp_sol, d.mnt_dem, d.id_prod,prov_mnt, d.cre_mnt_deb, d.is_ligne_credit ";

    if (($global_multidevise) && ($devise == NULL)) {
      $sql .= " ,d.devise, d.id_ag ";
      $groupbydevise=" ,d.devise ";
      $groupbyid_agc= " ,d.id_ag ";
    }

    $sql .= " FROM get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) d, ad_cli c ";//", ad_sre s ";
    $sql .= " WHERE d.id_ag = c.id_ag AND c.id_ag = d.id_ag AND d.id_ag = $global_id_agence ";
    $sql .= " AND d.id_client = c.id_client AND ";//AND d.id_doss = s.id_doss ";
    $len = strlen($sql);
    if (($global_multidevise) && ($devise != NULL))
      $sql .= " devise= '" . $devise . "' AND ";
    if ($CRITERE['statut'] > 0)
      $sql .= " (c.statut_juridique=" . $CRITERE['statut'] . ") AND ";
    // T-79 : Evolution rapport Registre de prêts après AT-33
    if ($data_agence['identification_client'] == 2) {
      if ($CRITERE['localisation_main'] != null) {
        if ($CRITERE['localisation_main'] == 1) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  c.province = " . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  c.province IS NOT NULL AND ";
          }
        } elseif ($CRITERE['localisation_main'] == 2) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  c.district =" . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  c.district IS NOT NULL AND ";
          }
        } elseif ($CRITERE['localisation_main'] == 3) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  c.secteur =" . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  c.secteur IS NOT NULL AND ";
          }
        } elseif ($CRITERE['localisation_main'] == 4) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  c.cellule =" . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  c.cellule IS NOT NULL AND ";
          }
        } elseif ($CRITERE['localisation_main'] == 5) {
          if ($CRITERE['crit_loc'] != 0) {
            $sql .= "  c.village =" . $CRITERE['crit_loc'] . " AND ";
          } else {
            $sql .= "  c.village IS NOT NULL AND ";
          }
        } else {
          $sql .= "  c.province is null and c.district is null and c.secteur is null and c.cellule is null and c.village is null AND ";
        }
      }
      }else {
        if ($CRITERE['localisation'] > 0)
        $sql .= " (c.id_loc1=" . $CRITERE['localisation'] . " or c.id_loc2=" . $CRITERE['localisation'] . ") AND ";
      }

      if(isFusion()){
          if ($CRITERE['n_agence'] > 0)
              $sql .= " (c.n_agence=" . $CRITERE['n_agence'] . ") AND ";

          if ($CRITERE['n_guichet'] > 0)
              $sql .= " (c.n_guichet=" . $CRITERE['n_guichet'] . ") AND ";
      }

    if ($CRITERE['pp_sexe'] > 0)
      $sql .= " (c.pp_sexe=" . $CRITERE['pp_sexe'] . ") AND ";
    if ($CRITERE['sect_act'] > 0)
      $sql .= " (c.sect_act=" . $CRITERE['sect_act'] . ") AND ";
    if ($CRITERE['produit'] > 0)
      $sql .= " (d.id_prod=" . $CRITERE['produit'] . ") AND ";
    if ($CRITERE['objet'] > 0)
      $sql .= " (d.obj_dem=" . $CRITERE['objet'] . ") AND ";
    //La date de déblocage n'est renseigné que si le crédit est déboursé donc etat >= 5
    if ($CRITERE['date_debloc_inf'] != ''){
      $sql .= " (d.cre_date_debloc >= date('" . $CRITERE['date_debloc_inf'] . "')) AND ";
    }
    if ($CRITERE['date_debloc_sup'] != '') {
      $date = splitEuropeanDate($CRITERE['date_debloc_sup']);
      $date2 = date("d/m/Y", mktime(0, 0, 0, $date[1], $date[0] + 1, $date[2]));
      $sql .= " (d.cre_date_debloc < date('" . $date2 . "')) AND ";
    }
    if ($CRITERE['type_duree'] > 0)
      $sql .= " d.type_duree_credit = " . $CRITERE['type_duree'] . " AND ";
    if ($CRITERE['duree_mois'] > 0)
      $sql .= " d.duree_mois = " . $CRITERE['duree_mois'] . " AND ";
    if ($CRITERE['cre_mnt_octr'] > 0)
      $sql .= " d.cre_mnt_octr = " . $CRITERE['cre_mnt_octr'] . " AND ";
    if ($CRITERE['nb_reech'] > 0)
      $sql .= " d.cre_nbre_reech = " . $CRITERE['nb_reech'] . " AND ";
    if ($CRITERE['etat_dossier'] != "")
      $sql .= " d.etat IN (" . $CRITERE['etat_dossier'] . ") AND ";

    //Enlève le 'AND' ou le 'WHERE'
    if ($len == strlen($sql))
      $sql = substr($sql, 0, strlen($sql) - 6); //Si on a rien ajouté
    else
      $sql = substr($sql, 0, strlen($sql) - 4);
    if ($gestionnaire > 0)
      $sql .= " AND d.id_agent_gest=$gestionnaire ";

    if($CRITERE['etat_dossier'] >= 5){ //date déblocage renseigné
      // On trie par produit de crédit et par date de débloquage
      $sql .= " GROUP BY d.id_client, d.id_doss, d.cre_etat, d.id_prod, d.cre_date_debloc". $groupbydevise.",d.libel,";
      $sql .= " d.cre_mnt_octr, d.duree_mois, d.type_duree_credit, d.gs_cat, d.id_dcr_grp_sol, d.mnt_dem,d.prov_mnt, d.cre_mnt_deb, d.is_ligne_credit ".$groupbyid_agc ;
    } else {
      // On trie par produit de crédit
      $sql .= " GROUP BY d.id_client, d.id_doss, cre_etat, d.id_prod,d.cre_date_debloc".$groupbydevise.", d.libel,";
      $sql .= " d.cre_mnt_octr, d.duree_mois, d.type_duree_credit, d.gs_cat, d.id_dcr_grp_sol, d.mnt_dem,d.prov_mnt, d.cre_mnt_deb, d.is_ligne_credit ".$groupbyid_agc." ";
    }
    $sql .= " ORDER BY d.id_prod ASC, d.id_dcr_grp_sol ASC "; // Added : Ticket #201
  }
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  //on recupère tous les états des crédits : pas besoin de faire l'appel dans la boucle while.
  $ET = getTousEtatCredit();

//Get all dossier de credits
  $dossiers = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $dossiers [$row['id_doss']] = $row;

  }
  /*
   * Recuperation des info concernant les remboursement pour chaque dossier de credits
   * Kheshan modified
   * @15092015
   */
  $INFO_DCR = array ();
  foreach ($dossiers as $id_doss => $details ){
    $sql1 = "select sum(mnt_remb_cap) as remb_cap, sum(mnt_remb_int) as remb_int ,sum(mnt_remb_gar) as remb_gar ,sum(mnt_remb_pen) as remb_pen from ad_sre where id_ag = $global_id_agence and id_doss= $id_doss";

    $result1 = $db->query($sql1);
    if (DB :: isError($result1)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__); // $result1->getMessage()
    }
    $row_remb = $result1->fetchrow(DB_FETCHMODE_ASSOC);
    $INFO_DCR [$id_doss]["id_doss"]= $id_doss;
    //get devise
    if ($global_multidevise) {
      if ($devise == NULL)
        $INFO_DCR [$id_doss] ["devise"] = $details[13];
      else
        $INFO_DCR [$id_doss] ["devise"] = $devise;
    }
    //recupe infor rembousement
    $INFO_DCR [$id_doss] ["mnt_remb_cap"] = $row_remb['remb_cap'];
    $INFO_DCR [$id_doss] ["mnt_remb_int"] = $row_remb["remb_int"];
    $INFO_DCR [$id_doss] ["mnt_remb_gar"] = $row_remb["remb_gar"];
    $INFO_DCR [$id_doss] ["mnt_remb_pen"] = $row_remb["remb_pen"];
    //recup info general du dossier
    $INFO_DCR [$id_doss] ["id_client"] = $details["id_client"];
    $INFO_DCR [$id_doss] ["nom"] = getClientName($details["id_client"]);
    $INFO_DCR [$id_doss] ["libel"] = $details["libel"];
    $INFO_DCR [$id_doss] ["cre_mnt_octr"] = $details["cre_mnt_octr"];
    $INFO_DCR [$id_doss] ["cre_date_debloc"] = $details["cre_date_debloc"];
    $INFO_DCR [$id_doss] ["cre_etat"] = $ET[$details["cre_etat"]]["libel"];
    $INFO_DCR [$id_doss] ["duree_mois"] = $details["duree_mois"];
    $INFO_DCR [$id_doss] ["gs_cat"] = $details["gs_cat"];
    $INFO_DCR [$id_doss] ["id_dcr_grp_sol"] = $details["id_dcr_grp_sol"];
    $INFO_DCR [$id_doss] ["mnt_dem"] = $details["mnt_dem"];
    $INFO_DCR [$id_doss] ["type_duree"] = $adsys["adsys_type_duree_credit"][$details["type_duree_credit"]];
    $INFO_DCR [$id_doss] ["id_prod"] = $details["id_prod"]; // Added : Ticket #201
    $INFO_DCR [$id_doss] ["prov_mnt"] = $details["prov_mnt"];
    $INFO_DCR [$id_doss] ["cre_mnt_deb"] = $details["cre_mnt_deb"];
    $INFO_DCR [$id_doss] ["is_ligne_credit"] = $details["is_ligne_credit"];

  }
  $dbHandler->closeConnection(true);

  return $INFO_DCR;
}
/**
  * Recherche dans la BD les données pour le rapport Extrait des crédits actifs
  *
  * @param array $CRITERE Les critères de sélection des crédits :<ul>
  *    <li>      "produit"          => produit de crédit
  *    <li>      "objet"            => objet de crédit
  *    <li>      "date_debloc_inf"  => Date inférieure pour l'octroi du crédit
  *    <li>      "date_debloc_sup"  => Date supérieure pour l'octroi du crédit
  *    <li>      "duree_mois"       => Durée  du crédit
  *    <li>      "type_duree"       => Type Durée  de crédit
  *    <li>      "cre_mnt_octr"     => Montant octroyé
  *    <li>      "localisation"     => ID de la localité du client bénéficiaire
  *    <li>      "statut "          => Satut juridique du client
  *    <li>      "pp_sexe"          => Le genre du membre bénéficiaire
  *    <li>      "sect_act"         => Secteur d'activité de bénéficiaire
  *    <li>      "nb_reech"         => Le nombre de rééchelonnement
  *    <li>      "etat_dossier"     => L'état du dossier
  * </ul>
  * @param int $devise La devise de recherche, NULL si en mono devise
  * @param int $gestionnaire: l'utilisateur gestionnaire de crédit
  * @access public
  * @return array Un tableau associatifs des données utiles des crédits trouvés :<ul>
  *    <li>         "id_doss"        => ID du dossier de crédit
  *    <li>         "id_client"      => Id du client
  *    <li>         "pp_nom"         => Nom du client
  *    <li>         "pp_prenom"      => Prénom du client
  *    <li>         "prod"           => Libellé du produit de crédit
  *    <li>         "devise"         => Devise du produit de crédit
  *    <li>         "cre_mnt_octr"   => Montant octroyé
  *    <li>         "cre_date_debloc"=> Date de déboursement
  *    <li>         "duree_mois"     => Durée du crédit
  *    <li>         "cre_etat"       => Etat du crédit
  *    <li>         "cre_remb_cap"   => Montant du capital remboursé
  *    <li>         "cre_remb_int"   => Montant des intérêts remboursés
  *    <li>         "cre_remb_gar"   => Montant de la garantie remboursée
  *    <li>         "cre_remb_pen"   => Montant des pénalités remboursées
  * </ul>
  */
function getCreditActif($CRITERE, $devise = NULL, $gestionnaire = 0, $i = 0) {
  global $dbHandler;
  global $global_multidevise,$global_id_agence;

  $db = $dbHandler->openConnection();
  global $adsys;
  set_time_limit(0);

  $etat = isset($CRITERE['etat_dossier'])?$CRITERE['etat_dossier']:'null';

  $sql = "SELECT d.id_doss, d.id_client, d.libel, d.cre_mnt_octr, d.cre_date_approb, d.cre_etat , ";
  $sql .= " c.loc3, c.adresse, d.mnt_dem, c.id_loc1, c.id_loc2, d.id_agent_gest, d.gs_cat, d.id_dcr_grp_sol, MAX(e.date_ech), c.statut_juridique, sum(e.mnt_int), d.is_ligne_credit ";
//  $sql .= " sum(s.mnt_remb_cap), sum(s.mnt_remb_int),sum(s.mnt_remb_gar),sum(s.mnt_remb_pen)  ";
  if (($global_multidevise) && ($devise == NULL))
    $sql .= " ,d.devise ";
  $sql .= " FROM get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) d, ad_cli c, ad_etr e ";//", ad_sre s ";
  $sql .= " WHERE c.id_client = d.id_client AND d.id_doss = e.id_doss ";
  $sql .= " AND d.id_ag = c.id_ag AND c.id_ag = d.id_ag AND d.id_ag = e.id_ag AND e.id_ag = $global_id_agence AND ";
  $len = strlen($sql);
  if (($global_multidevise) && ($devise != NULL))
    $sql .= " d.devise= '" . $devise . "' AND";
  if ($CRITERE['produit'] > 0)
    $sql .= " (d.id_prod=" . $CRITERE['produit'] . ") AND";
  if ($CRITERE['date_debloc_inf'] != '')
    $sql .= " (d.cre_date_debloc >= date('" . $CRITERE['date_debloc_inf'] . "')) AND";
  if ($CRITERE['date_debloc_sup'] != '') {
    $date = splitEuropeanDate($CRITERE['date_debloc_sup']);
    $date2 = date("d/m/Y", mktime(0, 0, 0, $date[1], $date[0] + 1, $date[2]));
    $sql .= " (d.cre_date_debloc < date('" . $date2 . "')) AND ";
  }
  if ($CRITERE['etat_dossier'] != "")
    $sql .= " d.etat IN (" . $CRITERE['etat_dossier'] . ") AND ";

  //Enlève le 'AND' ou le 'WHERE'
  if ($len == strlen($sql))
    $sql = substr($sql, 0, strlen($sql) - 6); //Si on a rien ajouté
  else
    $sql = substr($sql, 0, strlen($sql) - 4);
  if ($gestionnaire > 0)
    $sql .= " AND d.id_agent_gest = $gestionnaire ";

  $sql .= " AND d.id_client > $i ";

  // On trie par produit de crédit et par date de débloquage
  $sql .= " GROUP BY d.id_client, d.id_doss,  d.libel, d.cre_mnt_octr, d.cre_date_approb, d.cre_etat,";
  $sql .= " c.loc3, c.adresse, d.mnt_dem, c.id_loc1, c.id_loc2, d.id_agent_gest, d.gs_cat, d.id_dcr_grp_sol, c.statut_juridique, d.is_ligne_credit";
  if (($global_multidevise) && ($devise == NULL))
    $sql .= " ,d.devise ";
  //palier de 4000 lignes
  $sql .= " ORDER BY d.id_agent_gest ";

  //$sql .= " limit 2000";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $RESULTAT = array ();
  $tabGS = array();
  $numEtatPerte = getIDEtatPerte();
  $ET = getTousEtatCredit();
  while ($row = $result->fetchrow()) {
//    $id_ag=$row[1];
    $sql1 = "select sum(mnt_remb_cap), sum(mnt_remb_int),sum(mnt_remb_gar),sum(mnt_remb_pen) from ad_sre where id_doss= $row[0] and id_ag=$global_id_agence ";
    $result1 = $db->query($sql1);
    if (DB :: isError($result1)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__); // $result1->getMessage()
    }
    $row_remb = $result1->fetchrow();
    $INFO_DCR = array ();
    if ($global_multidevise) {
      if ($devise == NULL)
        $INFO_DCR["devise"] = $row[18];
      else
        $INFO_DCR["devise"] = $devise;
    }
    $INFO_DCR["id_doss"] = $row[0];
    $INFO_DCR["is_ligne_credit"] = $row[17];
    $INFO_DCR["mnt_remb_cap"] = $row_remb[0];
    $INFO_DCR["mnt_remb_int"] = $row_remb[1];
    $INFO_DCR["mnt_remb_gar"] = $row_remb[2];
    $INFO_DCR["mnt_remb_pen"] = $row_remb[3];
    //Récupèration des soldes restants capital et intérêt
    $sql3 = "select  sum(mnt_int) from ad_etr where id_doss= $row[0] and id_ag=$global_id_agence ";
    $result3 = $db->query($sql3);
    if (DB :: isError($result1)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__); // $result1->getMessage()
    }
    $row_remb = $result3->fetchrow();
    $INFO_DCR["mnt_int"] = $row_remb[0];
    //Récupèration de la date de la dernière échéance
    $sql5 = "select MAX(date_ech) from ad_etr where  id_doss= $row[0] and id_ag=$global_id_agence ";
    $result5 = $db->query($sql5);
    $row_remb = $result5->fetchrow();
    $INFO_DCR["delai"] = substr($row_remb[0], 8, 2) . "/" . substr($row_remb[0], 5, 2) . "/" . substr($row_remb[0], 0, 4);
//    $sql2 = "select d.id_client,p.libel,cre_mnt_octr,cre_date_approb,cre_etat , loc3, adresse,d.mnt_dem,id_loc1,id_loc2,id_agent_gest,d.id_ag, d.gs_cat,d.id_dcr_grp_sol ";
//    $sql2 .= " from ad_dcr d, ad_cli c, adsys_produit_credit p  where d.id_ag = c.id_ag AND c.id_ag = p.id_ag AND p.id_ag = $global_id_agence AND ";
//    $sql2 .= " id_doss= $row[0] and d.id_client=c.id_client and d.id_prod=p.id and d.id_ag=$id_ag ";
//    $result2 = $db->query($sql2);
//    if (DB :: isError($result2)) {
//      $dbHandler->closeConnection(false);
//      signalErreur(__FILE__, __LINE__, __FUNCTION__); // $result2->getMessage()
//    }
//    $row_dossier = $result2->fetchrow();
    $INFO_DCR["id_client"] = $row[1];
    $INFO_DCR["nom"] = getClientName($row[1]);
    $INFO_DCR["libel"] = $row[2];
    $INFO_DCR["cre_mnt_octr"] = $row[3];
    $INFO_DCR["cre_date_approb"] = $row[4];
    $INFO_DCR["cre_etat"] = $ET[$row[5]]["libel"];
    if ($row[5] == $numEtatPerte) {
      $INFO_DCR["is_en_perte"] = TRUE;
    } else {
      $INFO_DCR["is_en_perte"] = FALSE;
    }
    if ($row[9] != "")
      $loc1 = getLocalisation($row[9]);
    if ($row[10] != "")
      $loc2 = getLocalisation($row[10]);
    $INFO_DCR["localite"] = $loc1 . "  " . $loc2 . "  " . $row[6];
    $INFO_DCR["adresse"] = $row[7];
    $INFO_DCR["mnt_dem"] = $row[8];
    $INFO_DCR["gestionnaire"] = $row[11];
    if ($row[11] != "" && $row[11] > 0)
    $INFO_DCR["gestionnaire"] = get_gestionnaire($row[11]);
    $INFO_DCR["gs_cat"] = $row[12];
    $INFO_DCR["id_dcr_grp_sol"] = $row[13];
    //recuperation du crédit solidaire à dossiers multiples
    $groupe = getCreditSolDetailRap($INFO_DCR);
    if((is_array($groupe["credit_gs"]))&&(!in_array($groupe["credit_gs"]["id_client"],$tabGS))){
    	$tabGS[]  = $groupe["credit_gs"]["id_client"];
    	$groupe["credit_gs"]["libel"] = $row[1];
    	$groupe["credit_gs"]["gestionnaire"] = $INFO_DCR["gestionnaire"];
    	$groupe["credit_gs"]["nom"] = getClientName($groupe["credit_gs"]["id_client"]);
    	array_push($RESULTAT,$groupe["credit_gs"]);
    }
    if (($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($INFO_DCR["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"])) $INFO_DCR["membre"] = 1;
    else $INFO_DCR["membre"] = 0;
    array_push($RESULTAT, $INFO_DCR);
    //récuperation des crédits des membres d'un groupe solidaire  à dossier unique
    if(is_array($groupe[$INFO_DCR["id_client"]])) {
    	$i = 0;
    	while($i < count($groupe[$INFO_DCR["id_client"]])) {
    		$groupe[$INFO_DCR["id_client"]][$i]["gestionnaire"] =  $INFO_DCR["gestionnaire"];
    		$groupe[$INFO_DCR["id_client"]][$i]["id_doss"] = 0;
    		$groupe[$INFO_DCR["id_client"]][$i]["libel"] = $INFO_DCR["libel"];
    		$groupe[$INFO_DCR["id_client"]][$i]["nom"] = getClientName($groupe[$INFO_DCR["id_client"]][$i]["id_client"]);
    		//recherche de la localisation de chaque membre
//    		$sqloc = "select id_loc1, id_loc2, adresse from ad_cli where id_client=".$groupe[$INFO_DCR["id_client"]][$i]["id_client"];
//    		$resultloc = $db->query($sqloc);
//			if (DB::isError($resultloc )) {
//			$dbHandler->closeConnection(false);
//			signalErreur(__FILE__, __LINE__, __FUNCTION__);
//			}
//			$rowloc = $resultloc ->fetchrow();
//			if($rowloc[0] != "")
//				$loc1 = getLocalisation($rowloc[0]);
//			if($rowloc[1] !="")
//				$loc2 = getLocalisation($rowloc[1]);
			if(($loc1 != "") || ($loc2 != "")) $groupe[$INFO_DCR["id_client"]][$i]["localite"] = $loc1 . "  " . $loc2;
			else $groupe[$INFO_DCR["id_client"]][$i]["localite"] = "non précisée";
			$groupe[$INFO_DCR["id_client"]][$i]["adresse"] = $row[10];
    		//le montant octroyé à chacun des membres dépendra du montant octroyé au groupe par rapport au montant global demandé
    		$groupe[$INFO_DCR["id_client"]][$i]["cre_mnt_octr"] = ($groupe[$INFO_DCR["id_client"]][$i]["mnt_dem"] * $INFO_DCR["cre_mnt_octr"])/$INFO_DCR["mnt_dem"];
    		array_push($RESULTAT,$groupe[$INFO_DCR["id_client"]][$i]);
    		$i++;
    	}
    }
  }
  $dbHandler->closeConnection(true);
  return $RESULTAT;
}

function getIntervallePeriode($periode){
 	$intervalle = array();
 	$intervalle['date_inf'] = "";
 	$intervalle['date_sup'] = "";
 	$intervalle['indice'] = "";
 	$intervalle['libelle'] = "";
 	$date_inf = ""; // les dates qui vont servir de bornes inférieures et supérieures pour la sélection des comptes
 	$date_sup = "";
 	//déterminer en fonction de la période les différents intervalles de temps
 	if($periode == 1) {
 	  $date_inf = date("d/m/Y"); //today
 	  $date_sup = date("d/m/Y");
 	  $indice = "Aujourdhui";
 	  $libelle = "Aujourd'hui";
 	  $intervalle['date_inf'] = $date_inf;
 	  $intervalle['date_sup'] = $date_sup;
 	  $intervalle['indice'] = $indice;
 	  $intervalle['libelle'] = $libelle;
 	}
 	else if($periode == 2) {
 	  $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 1, date("Y"))); //j+1
 	  $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 7, date("Y"))); //j+7
 	  $indice = "entre_1_et_7_jours";
 	  $libelle = "1 jour à 7 jours";
 	  $intervalle['date_inf'] = $date_inf;
 	  $intervalle['date_sup'] = $date_sup;
 	  $intervalle['indice'] = $indice;
 	  $intervalle['libelle'] = $libelle;
 	}
 	else if($periode == 3) {
 	  $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 8, date("Y"))); //j+1
 	  $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 14, date("Y"))); //j+7
 	  $indice = "entre_8_et_14_jours";
 	  $libelle = "8 jours à 14 jours";
 	  $intervalle['date_inf'] = $date_inf;
 	  $intervalle['date_sup'] = $date_sup;
 	  $intervalle['indice'] = $indice;
 	  $intervalle['libelle'] = $libelle;
 	}
 	else if($periode == 4) {
 	  $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 15, date("Y")));
 	  $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 21, date("Y")));
 	  $indice = "entre_15_et_21_jours";
 	  $libelle = "15 jours à 21 jours";
 	  $intervalle['date_inf'] = $date_inf;
 	  $intervalle['date_sup'] = $date_sup;
 	  $intervalle['indice'] = $indice;
 	  $intervalle['libelle'] = $libelle;
 	}
 	else if($periode == 5) {
 	  $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n"), date("d") + 22, date("Y")));
 	  $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n") + 1, date("d"), date("Y"))); //30 jours
 	  $indice = "entre_22_et_30_jours";
 	  $libelle = "22 jours à 30 jours";
 	  $intervalle['date_inf'] = $date_inf;
 	  $intervalle['date_sup'] = $date_sup;
 	  $intervalle['indice'] = $indice;
 	  $intervalle['libelle'] = $libelle;
 	}
 	else if($periode == 6) {
 	  $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n") + 1, date("d") + 1, date("Y"))); //premier jour du mois suivant
 	  $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n") + 3, date("d"), date("Y"))); //dernier jour du 3ème mois suivant
 	  $indice = "entre_1_et_3_mois";
 	  $libelle = "1 mois à 3 mois";
 	  $intervalle['date_inf'] = $date_inf;
 	  $intervalle['date_sup'] = $date_sup;
 	  $intervalle['indice'] = $indice;
 	  $intervalle['libelle'] = $libelle;
 	}
 	else if($periode == 7) {
 	  $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n") + 3, date("d") + 1, date("Y")));
 	  $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n") + 6, date("d"), date("Y"))); //
 	  $indice = "entre_4_et_6_mois";
 	  $libelle = "4 mois à 6 mois";
 	  $intervalle['date_inf'] = $date_inf;
 	  $intervalle['date_sup'] = $date_sup;
 	  $intervalle['indice'] = $indice;
 	  $intervalle['libelle'] = $libelle;
 	}
 	else if($periode == 8) {
 	  $date_inf = date("d/m/Y", mktime(0, 0, 0, date("n") + 6, date("d") + 1, date("Y")));
 	  $date_sup = date("d/m/Y", mktime(0, 0, 0, date("n") + 12, date("d"), date("Y"))); //
 	  $indice = "entre_7_et_12_mois";
 	  $libelle = "7 mois à 12 mois";
 	  $intervalle['date_inf'] = $date_inf;
 	  $intervalle['date_sup'] = $date_sup;
 	  $intervalle['indice'] = $indice;
 	  $intervalle['libelle'] = $libelle;
 	}
    else{ //AT-55 : gestion entre la date inf et la date du jour
      $indice = "entre_dateinf_et_datedujour";
      $annee = false;
      $mois = false;
      $libelle = "plus ou moins ".$periode['en_jours']." jours";
      if ($periode['en_annee'] == 0 && $periode['en_mois'] == 0 && $periode['en_jours'] == 0 && $periode['en_semaine'] == 0){
        $libelle = $periode['intervalleMsgSup'];
      }
      if (isset($periode['en_annee']) && $periode['en_annee'] > 0){
        $libelle = $periode['intervalleMsgSup']."plus ou moins ".$periode['en_annee']."  annee(s) (".$periode['en_mois']." mois)".$periode['intervalleMsgInf'];
        $annee = true;
      }
      if ($annee === false && isset($periode['en_mois']) && $periode['en_mois'] > 0){
        $libelle = $periode['intervalleMsgSup']."plus ou moins ".$periode['en_mois']."  mois (en Jours : ".$periode['en_jours'].")".$periode['intervalleMsgInf'];
        $mois = true;
      }
      if ($annee === false && $mois === false && isset($periode['en_semaine']) && $periode['en_semaine'] > 0){
        $libelle = $periode['intervalleMsgSup']."plus ou moins ".$periode['en_semaine']."  semaine(s) (en Jours : ".$periode['en_jours'].")".$periode['intervalleMsgInf'];
      }
      $intervalle['date_inf'] = $periode['date_inf'];
      $intervalle['date_sup'] = $periode['date_sup'];
      $intervalle['indice'] = $indice;
      $intervalle['libelle'] = $libelle;
    }
 	return $intervalle;
}

/**
 * Renvoie le nombre de client ayant des crédits qui arrivent à échéance dans la période donnée
 * @author Djibril NIANG
 * @since 3.0.6
 * @param DATE $date_debut debut de la période
 * @param DATE $date_fin fin de la période
 * @param TEXT $devise : la devise du crédit
 * @return INT $nombre_cli : le nombre de crédits
**/
function getNbreClientsEcheance($date_debut, $date_fin, $devise){
  global $dbHandler, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();

  //récupérer les numeros de dossier de crédits concernés
  $sql = "SELECT count(b.id_client) ";
  $sql .= " FROM ad_etr a, ad_dcr b, adsys_produit_credit";
  $sql .= " WHERE a.id_ag = b.id_ag AND b.id_ag = adsys_produit_credit.id_ag AND b.id_ag = $global_id_agence ";
  $sql .= " AND remb = 'f' AND a.id_doss = b.id_doss";
  $sql .= " AND (date(a.date_ech) >= date('$date_debut') AND date(a.date_ech) <= date('$date_fin'))";
  $sql .= " AND id = id_prod AND devise = '$devise' ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
     $dbHandler->closeConnection(false);
     signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
 	$row = $result->fetchrow();
 	$nombre_cli = $row[0];
 	$dbHandler->closeConnection(true);
 	return $nombre_cli;
}

/**
 * Retourne les infos des crédits arrivant à échéance, groupés suivant la période spécifiée.
 *
 * @param int $periode La période à considérer (1 => "Aujourd'hui", 2=>"Sur une semaine" , 3=>"Sur deux semaines" , 4 => "Sur trois semaines" , 5 => "Sur 1 mois")
 * @param boolean $exclusif S'il ne faut considérer que les échéances de la période ou bien toutes celles entre maintenant et la période.
 * @param string $devise La devise dans laquelle il faut prendre les crédits.
 * @param int $gestionnaire L'id du gestionnaire des crédits (si 0, on considère tous les crédits).
 * @return array Un tableau contenant les informations :
 *  array('Crédits échus entre 1 et 7 jours'=>array('total'=>array('nbre','montant total','interêt total', 'montant réech', 'solde total'), 'Crédits"=>array(0=>Crédit1,...,n=>Crédit n))
 *        'Crédit échus entre 8 et 15 jours'=>('total'=>array('nbre','montant total','interêt total', 'montant réech', 'solde total'), 'détails"=>array(0=>Credit1,...,n=>Credit n))
 *        ...
 *        'Credits échus entre x et y mois'=>('total'=>array('nbre','montant total','interêt total', 'montant réech', 'solde total'), 'détails"=>array(0=>Credit1,...,n=>Credit n))
 *       )
 */
function getCreditsEcheance($periode, $exclusif, $devise, $gestionnaire = 0, $i = 0) {
  global $dbHandler;
  global $global_multidevise,$global_id_agence;
  global $global_monnaie;

  $credits = array ();
 	$tmp_array = array ();
 	$intervalle = getIntervallePeriode($periode);
 	$date_inf = $intervalle['date_inf'] ;
 	$date_sup = $intervalle['date_sup'];
 	$indice = $intervalle['indice'] ;
 	$libelle = $intervalle['libelle'] ;
 	$db = $dbHandler->openConnection();
 	//récupérer les numeros de dossier de crédits concernés
    $sql = "SELECT a.id_doss, b.id_client,b.gs_cat,b.id_dcr_grp_sol,date_ech, mnt_cap, mnt_int, mnt_gar, mnt_reech, solde_cap, devise";
    $sql .= ", (SELECT sum(solde_cap) FROM ad_etr WHERE b.id_doss = id_doss AND remb='f') AS capital_du";
    $sql .= " FROM ad_etr a, get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) b ";
    $sql .= " WHERE";
    $sql .= " a.id_ag = b.id_ag AND b.id_ag = $global_id_agence ";
    $sql .= " AND remb = 'f' AND a.id_doss = b.id_doss";
    $sql .= " AND (date(a.date_ech) >= date('$date_inf')) AND (date(a.date_ech) <= date('$date_sup'))";
    $sql .= " AND id = id_prod AND devise = '$devise'";
    if ($gestionnaire > 0)
      $sql .= " AND b.id_agent_gest=$gestionnaire ";
    $sql .= " AND b.id_client > $i ";
 	  $sql .= " order by  b.id_client, a.id_doss, date_ech ";
 	  $sql .= " limit 4000";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    $lignescredit["$indice"]["libelle_echeance"] = $libelle;
    $tabGS = array();
    while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        //recuperation du crédit solidaire à dossiers multiples
    	$groupe = getCreditSolDetailRap($ligne);
    	if((is_array($groupe["credit_gs"]))&&(!in_array($groupe["credit_gs"]["id_client"],$tabGS))){
    		$tabGS[]  = $groupe["credit_gs"]["id_client"];
    		$lignescredit["$indice"]['credit'][] = $groupe["credit_gs"];
    	}
    	if (($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($ligne["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"])) $ligne["membre"] = 1;
    	else $ligne["membre"] = 0;
    	$lignescredit["$indice"]['credit'][] = $ligne;
    	//récuperation des crédits des membres d'un groupe solidaire  à dossier unique
    	if(is_array($groupe[$ligne["id_client"]])) {
    		$k = 0;
    		while($k < count($groupe[$ligne["id_client"]])) {
    			$groupe[$ligne["id_client"]][$k]["id_doss"] = 0;
    			$lignescredit["$indice"]['credit'][] = $groupe[$ligne["id_client"]][$k];
    			$k++;
    		}
    	}
    }
    $lignescredit["$indice"]['total']["nombre"] = 0;
    $lignescredit["$indice"]['total']["tot_mnt"] = 0;
    $lignescredit["$indice"]['total']["tot_int"] = 0;
    $lignescredit["$indice"]['total']["tot_gar"] = 0;
    $lignescredit["$indice"]['total']["tot_reech"] = 0;
    $lignescredit["$indice"]['total']["tot_solde"] = 0;

    if (!empty ($lignescredit["$indice"]['credit'])) {
      while (list (, $value) = each($lignescredit["$indice"]['credit'])) {
        $lignescredit["$indice"]['total']["nombre"]++;
        if ((($value["gs_cat"] == 1) && $value["membre"] == 1) || ($value["gs_multiple"] == "OK"))
        	  --$lignescredit["$indice"]['total']["nombre"];
        $lignescredit["$indice"]['total']['tot_mnt'] += $value['mnt_cap'];
        $lignescredit["$indice"]['total']['tot_int'] += $value['mnt_int'];
        $lignescredit["$indice"]['total']['tot_gar'] += $value['mnt_gar'];
        $lignescredit["$indice"]['total']['tot_reech'] += $value['mnt_reech'];
        $lignescredit["$indice"]['total']['tot_solde'] += $value['solde_cap'];
        $lignescredit["$indice"]['total']['tot_capital_du'] += $value['capital_du'];
      }
    }

  $dbHandler->closeConnection(true);
  if (is_array($lignescredit))
    return $lignescredit;
  else
    return NULL;
}
/**
 * Fonction renvoyant les données des mouvements sur tous les comptes compris entre le compte inférieur et le compte supérieur (compris) et entre les dates de début et de fin (comprises).  Le tableau de retour est trié par compte et par date des mouvements.
 * @author Mouhamadou Diouf
 * @since 2.0
 * @param date $date_deb : Date de début de sélection des mouvements
 * @param date $date_fin : Date de fin de sélection des mouvements
 * @param char $num_cpte1 : Numéro de compte inférieur
 * @param char $num_cpte2 : Numéro de compte supérieur
 * @return array $DATA = array ("num_cpte" =>
                array("piece", "libel", "debit", "credit", "date_comptable" ,"total_debit", "total_credit", "devise"))

 */
//function getGrandLivre($date_deb, $date_fin, $num_cpte1, $num_cpte2,$type_affichage) {
//  global $dbHandler;
//  global $global_multidevise;
//  global $global_monnaie;
//  global $global_id_agence;
//  $db = $dbHandler->openConnection();
//  // Recherche tous les mouvements sur la période donnée
////------------- grand livre avec la table ad_fluc_compta---------------------------------------
// 	if($type_affichage == 1){//détaillé
//	  $sql = "SELECT a.compte,a.id_ecriture,a.sens,a.devise,a.montant, a.id_his, a.id_client, a.libel_ecriture,a.date_comptable ";
//	  $sql .= "from ad_flux_compta a ";
//	  $sql .= "where  a.id_ag = $global_id_agence and (date(a.date_comptable)>= '$date_deb') and (date(a.date_comptable) <= '$date_fin') ";
//	  	// Pour les comptes donnés
//	 	if (isset ($num_cpte1))
//	 	  $sql .= " and a.compte >= '$num_cpte1'  ";
//	 	if (isset ($num_cpte2))
//	 	  $sql .= " and a.compte <= '$num_cpte2'  ";
//	 	 // Et triée par compte puis par date
//	  $sql .= " order by a.compte, a.date_comptable, a.type_operation;";
//  }else{
//		$sql = "SELECT a.compte,a.sens,a.devise,sum(montant) as montant,a.date_comptable,a.libel_ecriture, a.type_operation FROM ad_flux_compta a ";
//	 	$sql .= "WHERE  a.id_ag = $global_id_agence and a.id_ecriture = a.id_ecriture and (date(a.date_comptable)>= '$date_deb') and (date(a.date_comptable) <= '$date_fin') ";
//	 	// Pour les comptes donnés
//	 	if (isset ($num_cpte1))
//	 	  $sql .= " and a.compte >= '$num_cpte1'  ";
//	 	if (isset ($num_cpte2))
//	 	  $sql .= " and a.compte <= '$num_cpte2'  ";
//
//	  // Et triée par compte puis par date
//	 	$sql .= "group by a.compte, a.devise, a.sens, date(a.date_comptable), a.type_operation, a.libel_ecriture ";
//	  // Et triée par compte puis par date
//	  $sql .= " order by a.compte, a.date_comptable, a.type_operation;";
//  }
//
//  $result = $db->query($sql);
//  if (DB :: isError($result)) {
//    $dbHandler->closeConnection(false);
//    signalErreur("rapports.php", "getGrandLivre", $result->getMessage());
//  }
//
//	$DATA = array ();
//	$index = 0;
//  while ($mvt = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
//    // Pour chaque écriture trouvée
//    $index++;
//    $infos = array ();
//    $tmp = array ();
//    if (!is_array($DATA[$mvt['compte']])) {
//      $DATA[$mvt['compte']] = array ();
//      $DATA[$mvt['compte']]['solde_debut'] = calculSoldeNonRecursif($mvt['compte'],hier($date_deb));
//		  $DATA[$mvt['compte']]['solde_fin'] = calculSoldeNonRecursif ($mvt['compte'],$date_fin);
//
//		  $infos_report=array();
//		  $infos_report['libel']=_("REPORT SOLDE COMPTE");
//		  if($DATA[$mvt['compte']]['solde_debut']>0){
//		    $infos_report['credit']=$DATA[$mvt['compte']]['solde_debut'];
//		  } else {
//		    $infos_report['debit']=abs($DATA[$mvt['compte']]['solde_debut']);
//		  }
//		  $infos_report['devise']  = $mvt['devise'];
//		  array_push($DATA[$mvt['compte']], $infos_report);
//    }
//    // Récupération des informations à imprimer
//     if($type_affichage == 1){//détaillé
//    	$infos['piece'] 	= $mvt['id_ecriture'];
//    	$infos['id_his'] 	= $mvt['id_his'];
//    	$infos['id_client'] = $mvt['id_client'];
//     }else{
//     	$infos['piece'] 	= $index;
//     }
//    $infos['libel'] 	= $mvt['libel_ecriture'];
//    if ($mvt['sens'] == 'd') {
//      $infos['debit'] 	= $mvt['montant'];
//      $DATA[$mvt['compte']]['total_debit'] += $mvt['montant'];
//    } else {
//      $infos['credit'] 	= $mvt['montant'];
//      $DATA[$mvt['compte']]['total_credit'] += $mvt['montant'];
//    }
//
//    $infos['date'] 		= $mvt['date_comptable'];
//    $infos['devise'] 	= $mvt['devise'];
//    array_push($DATA[$mvt['compte']], $infos);
//  }
//  $dbHandler->closeConnection(true);
//  return $DATA;
//
//}
function getGrandLivre($date_deb, $date_fin, $num_cpte1, $num_cpte2,$type_affichage, $id_jou) {
	global $dbHandler;
	global $global_multidevise;
	global $global_monnaie;
	global $global_id_agence;
	$db = $dbHandler->openConnection();
	// Recherche tous les mouvements sur la période donnée
	//------------- grand livre avec la table ad_flux_compta---------------------------------------

  /*Ajoute paramentre type_operation et pour les type operation 40 et 372 special recuperation des id client*/
	if($type_affichage == 1){//détaillé
		$sql = "SELECT compte,id_ecriture,sens,type_operation, devise,montant, id_his, CASE
WHEN type_operation = 40 THEN (select distinct id_titulaire from ad_calc_int_paye_his where id_ecriture_reprise = a.id_ecriture )
WHEN type_operation = 372 THEN (select distinct id_titulaire from ad_calc_int_paye_his where id_ecriture_calc = a.id_ecriture )
 ELSE id_client
 END  AS id_client2 ,libel_ecriture,date_comptable, id_jou ";
		$sql .= "from getGrandLivreView(date('".$date_deb."'), date('".$date_fin."'), $global_id_agence) a  ";
		$sql .= "where  id_ag = $global_id_agence  ";
		// Pour les comptes donnés
		if (isset ($num_cpte1))
		$sql .= " and compte >= '$num_cpte1'  ";
		if (isset ($num_cpte2))
		$sql .= " and compte <= '$num_cpte2'  ";
		//filtre journal
		if (isset ($id_jou))
			$sql .= " and id_jou = '$id_jou'  ";
		// Et triée par compte puis par date
		$sql .= " order by a.compte, a.date_comptable, a.type_operation;";

	}else{
		//$sql = "SELECT a.compte,a.sens,a.devise,sum(montant) as montant,a.date_comptable,a.libel_ecriture, a.type_operation FROM ad_flux_compta a ";
		//$sql .= "WHERE  a.id_ag = $global_id_agence and a.id_ecriture = a.id_ecriture and (date(a.date_comptable)>= '$date_deb') and (date(a.date_comptable) <= '$date_fin') ";
		$sql = "SELECT a.compte,a.sens,a.devise,sum(montant) as montant,a.date_comptable,a.libel_ecriture, a.type_operation ";
		$sql .= "from getGrandLivreView(date('".$date_deb."'), date('".$date_fin."'), $global_id_agence) a  ";
		$sql .= "where  id_ag = $global_id_agence  ";
		// Pour les comptes donnés
		if (isset ($num_cpte1))
		$sql .= " and a.compte >= '$num_cpte1'  ";
		if (isset ($num_cpte2))
		$sql .= " and a.compte <= '$num_cpte2'  ";
		//filtre journal
		if (isset ($id_jou))
			$sql .= " and id_jou = '$id_jou'  ";
		// Et triée par compte puis par date
		$sql .= "group by a.compte, a.devise, a.sens, date(a.date_comptable), a.type_operation, a.libel_ecriture ";
		// Et triée par compte puis par date
		$sql .= " order by a.compte, a.date_comptable, a.type_operation;";
	}

	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur("rapports.php", "getGrandLivre", $result->getMessage());
	}

	$DATA = array ();
	$index = 0;
	while ($mvt = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		// Pour chaque écriture trouvée
		$index++;
		$infos = array ();
		$tmp = array ();
		if (!is_array($DATA[$mvt['compte']])) {
			$DATA[$mvt['compte']] = array ();
			$DATA[$mvt['compte']]['solde_debut'] = calculSoldeNonRecursif($mvt['compte'],hier($date_deb));
			$DATA[$mvt['compte']]['solde_fin'] = calculSoldeNonRecursif ($mvt['compte'],$date_fin);
			//$DATA[$mvt['compte']]['solde_debut'] = 0;
			//$DATA[$mvt['compte']]['solde_fin'] = 0;
			$infos_report=array();
			$infos_report['libel']=_("REPORT SOLDE COMPTE");
			if($DATA[$mvt['compte']]['solde_debut']>0){
				$infos_report['credit']=$DATA[$mvt['compte']]['solde_debut'];
			} else {
				$infos_report['debit']=abs($DATA[$mvt['compte']]['solde_debut']);
			}
			$infos_report['devise']  = $mvt['devise'];
			array_push($DATA[$mvt['compte']], $infos_report);
		}
		// Récupération des informations à imprimer
		if($type_affichage == 1){//détaillé
			$infos['piece'] 	= $mvt['id_ecriture'];
			$infos['id_his'] 	= $mvt['id_his'];
			$infos['id_client'] = $mvt['id_client2'];
		}else{
			$infos['piece'] 	= $index;
		}
		$infos['libel'] 	= $mvt['libel_ecriture'];
		if ($mvt['sens'] == 'd') {
			$infos['debit'] 	= $mvt['montant'];
			$DATA[$mvt['compte']]['total_debit'] += $mvt['montant'];
		} else {
			$infos['credit'] 	= $mvt['montant'];
			$DATA[$mvt['compte']]['total_credit'] += $mvt['montant'];
		}

		$infos['date'] 		= $mvt['date_comptable'];
		$infos['devise'] 	= $mvt['devise'];
		array_push($DATA[$mvt['compte']], $infos);
	}
	$dbHandler->closeConnection(true);
	return $DATA;

}
/**
 * Fonction renvoyant l'état finanicer compte de résultat
 * @author Papa
 * @since 2.0
 * @param date $date_deb date début de la période
 * @param date $date_fin  date de fin de la période
 * @param array $liste_ag  liste des agences à imprimer
 * @return array $DATA = array ("cle" =>
 *               array("compte_charge,libel_charge", "solde_charge","compte_produit","libel_produit","solde_produit"))
 */
function getCompteDeResultat($date_deb, $date_fin, $liste_ag, $niveau = NULL) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  /* On imprime les soldes dans la devise de référence */
  $cv = true;

  $DATA_CH = array (); // les charges
  $DATA_PR = array (); // les produits

  // Initialisation des totaux
  $total_charge = 0;
  $total_produit = 0;

  foreach($liste_ag as $id_agence=>$libel_agence) {
    set_time_limit(0); // Eviter la deconnexion du script pour depassement de max execution time
    setGlobalIdAgence($id_agence);
    /* Vérifier que les deux dates sont dans le même exercice */
    $sql = "SELECT * FROM ad_exercices_compta WHERE id_ag = $global_id_agence AND date_deb_exo <= '$date_deb' AND date_fin_exo >= '$date_deb'";
    $sql .= "AND date_deb_exo <= '$date_fin' AND date_fin_exo >= '$date_fin' ;";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    if ($result->numrows() != 1) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DATE_NON_VALIDE, ". Les dates doivent se situer dans le même exercice.");
    }
    $exo = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $id_exo_compta = $exo["id_exo_compta"];
    $date_deb_exo = $exo["date_deb_exo"];
    $date_deb_exo = pg2phpDate($date_deb_exo); //AAAA-MM-JJ to JJ-MM-AAAA
    $date_fin_exo = $exo["date_fin_exo"];
    $date_fin_exo = pg2phpDate($date_fin_exo); //AAAA-MM-JJ to JJ-MM-AAAA

    /* Récupération de tous les comptes de charges */
    $param["compart_cpte"] = 3;
    $cptes_charge = getComptesComptables($param, $niveau,$date_deb);
    /* Récupération de tous les comptes de produit */
    $param["compart_cpte"] = 4;
    $cptes_produit = getComptesComptables($param, $niveau,$date_deb);
    /* Récupération de tous les comptes de gestion */
    $cptes_gestion = array_merge($cptes_charge, $cptes_produit);

    /* Le nombre de ligne du rapport */
    ////$nbligne = max(count($cptes_charge), count($cptes_produit));

    /* construction du compte de résultat: charges à gauche du tableau et produits à droite du tableau */
    ///$DATA = array ();

    /* Construction de la partie des charges */
    if (is_array($cptes_charge)) {
      //$i = 1;
      foreach ($cptes_charge as $key => $value) {
        $num_cpte = $value["num_cpte_comptable"];
        ///$DATA[$i]["compte_charge"] = $value["num_cpte_comptable"];
        ///$DATA[$i]["libel_charge"] = $value["libel_cpte_comptable"];
        if (!isset($DATA_CH[$num_cpte]))
          $DATA_CH[$num_cpte]['libel_cpte_comptable'] = $value["libel_cpte_comptable"];

        /* Solde du compte = son propre solde + soldes de ses sous-comptes */
        $solde = calculeSoldeCompteResultat($num_cpte, hier($date_deb), $date_fin, $date_fin_exo, $cv);
        $solde = (-1) * $solde;
        ///$DATA[$i]["solde_charge"] = (-1) * $solde;
        if (!isset($DATA_CH[$num_cpte]["solde_charge"]))
          $DATA_CH[$num_cpte]["solde_charge"] = $solde;
        else
          $DATA_CH[$num_cpte]["solde_charge"] += $solde;

        /* On somme sur les comptes principaux */
        if (isComptePrincipal($num_cpte))
          $total_charge += $solde;
        ////$i = $i +1;
      }
    }
    /* Construction de la partie des produits */
    if (is_array($cptes_produit)) {
      ///$i = 1;
      foreach ($cptes_produit as $key => $value) {
        $num_cpte = $value["num_cpte_comptable"];
        ///$DATA[$i]["compte_produit"] = $value["num_cpte_comptable"];
        ///$DATA[$i]["libel_produit"] = $value["libel_cpte_comptable"];
        if (!isset($DATA_PR[$num_cpte]))
          $DATA_PR[$num_cpte]['libel_cpte_comptable'] = $value["libel_cpte_comptable"];

        /* Solde du compte = son propre solde + soldes de ses sous-comptes */
        $solde = calculeSoldeCompteResultat($num_cpte, hier($date_deb), $date_fin, $date_fin_exo, $cv);
        ///$DATA[$i]["solde_produit"] = $solde;
        if (!isset($DATA_PR[$num_cpte]["solde_produit"]))
          $DATA_PR[$num_cpte]["solde_produit"] = $solde;
        else
          $DATA_PR[$num_cpte]["solde_produit"] += $solde;

        /* On somme sur les comptes principaux */
        if (isComptePrincipal($num_cpte))
          $total_produit += $solde;
        ///$i = $i +1;
      }
    }

    resetGlobalIdAgence($id_agence);
  } // fin liste agence

  // Le nombre de ligne du rapport
  $nbligne = max(count($DATA_CH), count($DATA_PR));

  // Tabeau contenant les infos du rapport
  $DATA = array ();

  //Tri des comptes
  ksort($DATA_CH);
  reset($DATA_CH);
  ksort($DATA_PR);
  reset($DATA_PR);

  // Construction des lignes du rapport
  for ($i = $nbligne; $i > 0; $i--) {
    // Partie charge
    list ($key1, $value1) = each($DATA_CH);
    $DATA[$i]["compte_charge"] = $key1;
    $DATA[$i]["libel_charge"] = $value1['libel_cpte_comptable'];
    $DATA[$i]["solde_charge"] = $value1['solde_charge'];

    // Partie produit
    list ($key2, $value2) = each($DATA_PR);
    $DATA[$i]["compte_produit"] = $key2;
    $DATA[$i]["libel_produit"] = $value2['libel_cpte_comptable'];
    $DATA[$i]["solde_produit"] = $value2['solde_produit'];
  }

  /* Ajout ligne pour les totaux avant le résultat */
  $j = $nbligne +1;
  $DATA[$j]["compte_charge"] = _("TOTAL");
  $DATA[$j]["libel_charge"] = _("TOTAL CHARGE");
  $DATA[$j]["solde_charge"] = $total_charge;
  $DATA[$j]["compte_produit"] = "";
  $DATA[$j]["libel_produit"] = _("TOTAL PRODUIT");
  $DATA[$j]["solde_produit"] = $total_produit;
  /* Mettre le résultat à la partie charge */
  $j = $j +1;
  $DATA[$j]["compte_charge"] = _("Résultat de la période");
  $DATA[$j]["libel_charge"] = _("Résultat de la période");
  $DATA[$j]["solde_charge"] = "";
  $DATA[$j]["compte_produit"] = "";
  $DATA[$j]["libel_produit"] = "";
  $DATA[$j]["solde_produit"] = "";
  $resultat = $total_produit - $total_charge;
  $total_charge += $resultat;
  $DATA[$j]["solde_charge"] = $resultat;
  if ($resultat < 0)
    $DATA[$j]["libel_charge"] = _("Déficit");
  else
    if ($resultat > 0)
      $DATA[$j]["libel_charge"] = _("Excédent");
  /* Ajout d'une ligne pour les totaux après le résultat */
  $j = $j +1;
  $DATA[$j]["compte_charge"] = _("TOTAL");
  $DATA[$j]["libel_charge"] = "";
  $DATA[$j]["solde_charge"] = $total_charge;
  $DATA[$j]["compte_produit"] = "";
  $DATA[$j]["libel_produit"] = "";
  $DATA[$j]["solde_produit"] = $total_produit;
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $DATA);
}
/**
 * fonction:permet de calculer les  soldes des comptes associés chaque poste pour le rapport compte de resultat
 * @param DATE $date_deb	date début de periode
 * @param DATE $date_fin	date fin de periode
 * @param Integer $type_etat	type etat (rapport)
 * @param array  $liste_ag 	liste des agences
 * @param boolean	$consolide	true si on veut editer les etats consolidés (multi-agences)
 * @return array	$tempData les soldes du rapport
 *                      $tempData[id_poste]=solde
 */
function getCompte_resultat_BNR($date_deb, $date_fin,$type_etat, $liste_ag){
	 global $dbHandler, $global_id_agence;
  global $global_multidevise;
  global $global_monnaie;
  $db = $dbHandler->openConnection();
  set_time_limit(0); // Eviter la deconnexion du script pour depassement de max execution time
   /* Vérifier que les deux dates sont dans le même exercice */
    $sql = "SELECT * FROM ad_exercices_compta WHERE id_ag = $global_id_agence AND date_deb_exo <= '$date_deb' AND date_fin_exo >= '$date_deb'";
    $sql .= "AND date_deb_exo <= '$date_fin' AND date_fin_exo >= '$date_fin' ;";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    if ($result->numrows() != 1) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DATE_NON_VALIDE, ". "._("Les dates doivent se situer dans le même exercice."));
    }
    $exo = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $id_exo_compta = $exo["id_exo_compta"];
    $date_deb_exo = $exo["date_deb_exo"];
    $date_deb_exo = pg2phpDate($date_deb_exo); //AAAA-MM-JJ to JJ-MM-AAAA
    $date_fin_exo = $exo["date_fin_exo"];
    $date_fin_exo = pg2phpDate($date_fin_exo); //AAAA-MM-JJ to JJ-MM-AAAA

  /* Le bilan est imprimé dans la devise de référence */
  $cv = true;
  $sql="select num_cpte_comptable,a.id_poste,compartiment from ad_poste a , ad_poste_compte b WHERE  a.id_poste=b.id_poste AND type_etat=".$type_etat;
  $result = $db->query($sql);
  if (DB :: isError($result)) {
  	 $dbHandler->closeConnection(false);
     signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

  	foreach($liste_ag as $id_agence=>$libel_agence) {
  		// on travaille avec cette agence
      setGlobalIdAgence($id_agence);

  	  $solde=calculeSoldeCompteResultat($row['num_cpte_comptable'], hier($date_deb), $date_fin, $date_fin_exo, $cv);
  	  if( $row['compartiment']== 3 ) { //charge
  	  	$tempData[$row['id_poste']]['solde']+=(-1)*$solde;
  	  }else{//produit
  	  	$tempData[$row['id_poste']]['solde']+=(1)*$solde;
  	  }

  	}
  	// reinitialisation de global_id_agence
    resetGlobalIdAgence();

  }

  $dbHandler->closeConnection(true);
  return $tempData;

}
/**
 * fonction:permet de calculer les  soldes des comptes associés chaque poste pour le rapport ratio de liquidité
 * @param DATE $date_deb	date début de periode
 * @param Integer $type_etat	type etat (rapport)
 * @param array  $liste_ag 	liste des agences
 * @param boolean	$consolide	true si on veut editer les etats consolidés (multi-agences)
 * @return array	$tempData les soldes du rapport
 *                      $tempData[id_poste]=solde
 */
function getRatio_liquidite_BNR($date_ratio, $type_etat, $liste_ag,$consolide){
	global $dbHandler, $global_id_agence;
  global $global_multidevise;
  global $global_monnaie;
  $db = $dbHandler->openConnection();


  $sql="select num_cpte_comptable,a.id_poste from ad_poste a , ad_poste_compte b WHERE  a.id_poste=b.id_poste AND type_etat=".$type_etat;
  $result = $db->query($sql);
  if (DB :: isError($result)) {
  	 $dbHandler->closeConnection(false);
     signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
  	foreach($liste_ag as $id_agence=>$libel_agence) {
  		// on travaille avec cette agence
      setGlobalIdAgence($id_agence);
  		/* Solde du compte au début de la période */
		  $solde = calculSoldeRecursif($row['num_cpte_comptable'], $date_ratio,$consolide);
		 	$tempData[$row['id_poste']]['solde']+=(1)*$solde;
  	}
  	// reinitialisation de global_id_agence
    resetGlobalIdAgence();
  }

  $dbHandler->closeConnection(true);
  return $tempData;

}

/**
 * Renvoie les données pour la formation du bilan à la date $date_bilan
 * NOTE: Les soldes fournis sont les soldes à la fin de la journée $date_bilan
 * NB Les soldes sont fournis dans la devise de référence
 * @param date $date_bilan : Date du bilan
 * @param array $liste_ag : liste des agences à imprimer
 * @param Int $niveau : le niveau des comptes comptables
 * @return Array $DATA Tableau contenant tous les comptes du bilan avec leur solde
 * @author ares
 */
function getBilan($date_bilan, $liste_ag, $niveau = NULL,$consolide=NULL,$solde_non_null=NULL) {
  global $dbHandler, $global_id_agence;
  global $global_multidevise;
  global $global_monnaie;
  $db = $dbHandler->openConnection();

  /* Le bilan est imprimé dans la devise de référence */
  $cv = "true";
  /* Tableaux des comptes à présenter dans le bilan */
  $cptes_actif = array ();
  $cptes_passif = array ();
  /* Initialisation des totaux */
  $total_actif = 0;
  $total_amortissement = 0;
  $total_net = 0;
  $total_passif = 0;
  $somme_debit = 0;
  $somme_credit = 0;
  $PASSIF=2;
  $ACTIF=1;

  // pour chaque agence calculer les soldes des comptes
  foreach($liste_ag as $id_agence=>$libel_agence) {
    set_time_limit(0); // Eviter la deconnexion du script pour depassement de max execution time
    // on travaille avec cette agence
    setGlobalIdAgence($id_agence);

    // Récupération de tous les comptes comptables de cette agence
    $comptesCompta = array ();
    if($consolide==NULL){
    	$consolide='false';
    }
    if($niveau != NULL ){
    	$cond_niveau=" AND niveau<=$niveau ";
    }
    if ($solde_non_null){
    	 $cond_solde_actif= " AND  isSoldebilanNotNul(num_cpte_comptable,'$date_bilan',$global_id_agence,$ACTIF,$cv,$consolide) ";
    	 $cond_solde_passif=" AND  isSoldebilanNotNul(num_cpte_comptable,'$date_bilan',$global_id_agence,$PASSIF,$cv,$consolide) ";
    }
    //$comptesCompta = getComptesComptables(NULL, $niveau,$date_bilan);
    $sql=" SELECT num_cpte_comptable,libel_cpte_comptable,cpte_centralise, $ACTIF as compart_cpte, niveau, calculeSoldeBilan(num_cpte_comptable,date('$date_bilan'),$global_id_agence,$ACTIF,$cv,$consolide) as solde,calculeSoldeBilanProv(num_cpte_comptable,'$date_bilan',$global_id_agence,$PASSIF,$cv,$consolide) as solde_provision";
    $sql.=" FROM  ad_cpt_comptable ";
    $sql.=" where num_cpte_comptable  not in (select a.num_cpte_comptable  from ad_cpt_comptable a,  ad_cpt_comptable b where a.num_cpte_comptable like b.cpte_provision||'%')"; //( select cpte_provision from ad_cpt_comptable where cpte_provision IS NOT NULL) ";
    $sql.=" AND (compart_cpte=$ACTIF  or compart_cpte=5) ";
    $sql .=" AND id_ag = $global_id_agence AND ((is_actif = 't') OR (is_actif = 'f' AND date_modif > '$date_bilan')) ";// AND getNiveau(num_cpte_comptable,$global_id_agence)<=2";
    $sql.=$cond_niveau;
     $sql.=$cond_solde_actif;
   // $sql .= " ORDER BY id_ag, num_cpte_comptable,compart_cpte ASC";
    $sql.= " UNION ";
    $sql.=" SELECT num_cpte_comptable,libel_cpte_comptable,cpte_centralise, $PASSIF as compart_cpte, niveau,calculeSoldeBilan(num_cpte_comptable,'$date_bilan',$global_id_agence,$PASSIF,$cv,$consolide) as solde,calculeSoldeBilanProv(num_cpte_comptable,'$date_bilan',$global_id_agence,$ACTIF,$cv,$consolide) as solde_provision";
    $sql.=" FROM  ad_cpt_comptable ";
    $sql.=" where num_cpte_comptable  not in (select a.num_cpte_comptable from ad_cpt_comptable a,  ad_cpt_comptable b where a.num_cpte_comptable like b.cpte_provision||'%')"; //( select cpte_provision from ad_cpt_comptable where cpte_provision IS NOT NULL) ";
    $sql.=" AND (compart_cpte=2 or compart_cpte=5) ";
    $sql .=" AND id_ag = $global_id_agence AND ((is_actif = 't') OR (is_actif = 'f' AND date_modif > '$date_bilan')) ";// AND getNiveau(num_cpte_comptable,$global_id_agence)<=2";
    $sql.=$cond_niveau;
    $sql.= $cond_solde_passif;
    $sql .= " ORDER BY  num_cpte_comptable,compart_cpte ASC";
    $result = $db->query($sql);

   if (DB::isError($result)) {
     signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    // Parcours de tous les comptes comptables
    while ($value = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

      // Construction du tableu des comptes de la partie actif et actif-passif
      if ($value['compart_cpte'] == 1) { // actif
        $num_cpte = $value['num_cpte_comptable'];
        $provision = 0;
        $net = 0;


          // récupération du numéro et du libellé du compte
          if (!isset($ligne_actif[$num_cpte]['compte'])) { // pour ne pas écraser le libellé. Priorité aux libellés du siège
            $ligne_actif[$num_cpte]['compte'] = $value['num_cpte_comptable'];
            $ligne_actif[$num_cpte]['libel'] = $value['libel_cpte_comptable'];
          }

          // Solde brut du compte à l'actif
          $solde = $value['solde'];//calculeSoldeBilan($num_cpte, $date_bilan, 1, $cv,$consolide);
          $provision = $value['solde_provision'];//;
          $net = $solde+$provision;
        // Solde du compte à l'actif
          if (!isset($ligne_actif[$num_cpte]['solde'])) { // si c'est la première fois
            $ligne_actif[$num_cpte]['solde'] = (-1) * $solde;
            $ligne_actif[$num_cpte]['amort'] = $provision;
            $ligne_actif[$num_cpte]['net'] = (-1) * $net;
          } else {
            $ligne_actif[$num_cpte]['solde'] += (-1) * $solde;
            $ligne_actif[$num_cpte]['amort'] += $provision;
            $ligne_actif[$num_cpte]['net'] += (-1) * $net;
          }
           if ($value['cpte_centralise']==NULL){
           	$total_actif += $solde;
            $total_net += $net;
            $total_amortissement += $provision;

             }

      }
      elseif ($value['compart_cpte'] == 2) { // passif
        // Construction du tableu des comptes de la partie passif
        $num_cpte = $value['num_cpte_comptable'];
        if (!isset($ligne_passif[$num_cpte]['compte'])) { // pour ne pas écraser. Priorité aux libellés du siège
          $ligne_passif[$num_cpte]['compte'] = $value['num_cpte_comptable'];
          $ligne_passif[$num_cpte]['libel'] = $value['libel_cpte_comptable'];
        }

        // Solde brut du compte au passif
        $solde = $value['solde'];//calculeSoldeBilan($num_cpte, $date_bilan, 2, $cv,$consolide);

        if (!isset($ligne_passif[$num_cpte]['solde'])) // premier calcul
          $ligne_passif[$num_cpte]['solde'] = $solde;
        else
          $ligne_passif[$num_cpte]['solde'] += $solde;

        /* On fait la sommation par les comptes principaux */
        if ($value['cpte_centralise']==NULL)
          $total_passif += $solde;
      }//fin si passif

    } // fin parcours des comptes
    //si bilan consolidé
    if($consolide){
    	$condition=" AND consolide is not  true ";
    }
    // Affichage des résultats provisoires des exercices ouverts
    $infos_exos = getExercicesComptables(); // Infos sur tous les exercices de l'agence
    foreach ($infos_exos as $key => $exo) {
      if ($exo['etat_exo'] != 3) {
        //pg2phpDateBis
        $id_exo = $exo['id_exo_compta'];
        $datedeb = $exo['date_deb_exo'];
        $datefin = $exo['date_fin_exo'];

        // Somme mouvementts au crédit des comptes de gestion
        $sql = "SELECT  SUM( CalculeCV(mv.montant,mv.devise,'$global_monnaie',$global_id_agence) ) FROM ad_mouvement mv, ad_ecriture ec, ad_cpt_comptable cpt WHERE ";
        $sql .= "mv.id_ag = ec.id_ag AND ec.id_ag = cpt.id_ag AND cpt.id_ag = $global_id_agence AND ec.date_comptable >= '$datedeb' AND ec.date_comptable <= '$datefin' AND ec.date_comptable <= '$date_bilan' AND mv.id_ecriture=ec.id_ecriture ";
        $sql .= "AND mv.sens='c' AND mv.compte=cpt.num_cpte_comptable AND (cpt.compart_cpte=3 OR cpt.compart_cpte=4)";
        $sql.=$condition;
        $result = $db->query($sql);
        if (DB :: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $row = $result->fetchrow();
        $somme_credit = $row[0];
        //if ($somme_credit > 0 and $cv)
        {
        	// Somme mouvements au débit des comptes de gestion
	        $sql = "SELECT SUM( CalculeCV(mv.montant,mv.devise,'$global_monnaie',$global_id_agence) ) FROM ad_mouvement mv, ad_ecriture ec, ad_cpt_comptable cpt WHERE ";
	        $sql .= "mv.id_ag = ec.id_ag AND ec.id_ag = cpt.id_ag AND cpt.id_ag = $global_id_agence AND ec.date_comptable >= '$datedeb' AND ec.date_comptable <= '$datefin' AND ec.date_comptable <= '$date_bilan' AND mv.id_ecriture=ec.id_ecriture ";
	        $sql .= "AND mv.sens='d' AND mv.compte=cpt.num_cpte_comptable AND (cpt.compart_cpte=3 OR cpt.compart_cpte=4)";
	        $sql .= $condition;
        }
        $result = $db->query($sql);
        if (DB :: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $row = $result->fetchrow();
        $somme_debit = $row[0];

        // Si le résultat est non null, l'afficher dans la partie du Passif
        $resultat_exo = (double)$somme_credit - (double)$somme_debit;

        if ($resultat_exo != 0 and $resultat_exo != NULL) {
          if (!isset($ligne_passif["exercie".$id_exo]['solde']))
            $ligne_passif["exercie".$id_exo]['solde'] = $resultat_exo;
          else
            $ligne_passif["exercie".$id_exo]['solde'] += $resultat_exo;

          $ligne_passif["exercie".$id_exo]['compte'] = "exercice" .$id_exo;
          $ligne_passif["exercie".$id_exo]['libel'] = "Resultat provisoire exercice " .$id_exo;

          $total_passif += $resultat_exo;
        }
      } // Fin si exercice non fermé et antérieur exo en cours
    } // Fin parcours des exercices

    // reinitialisation de global_id_agence
    resetGlobalIdAgence();

  } // fin parcours des agences

  /* Tabeau contenant les infos du rapport bilan */
  $DATA = array ();
  /* Le nombre de lignes du tableau */
  $nb_lignes = max(count($ligne_actif), count($ligne_passif));
  /* Tri des comptes à présenter dans le Bilan */
  ksort($ligne_actif);
  reset($ligne_actif);
  ksort($ligne_passif);
  reset($ligne_passif);
  /* Construction des lignes du bilan */
  for ($i = $nb_lignes; $i > 0; $i--) {
    /* Partie de l'Actif de la ligne */
    list ($key1, $value1) = each($ligne_actif);
    $DATA[$i]["compte_actif"] = $value1['compte'];
    $DATA[$i]["libel_actif"] = $value1['libel'];
    $DATA[$i]["solde_actif"] = $value1['solde'];
    $DATA[$i]["amort_actif"] = $value1['amort'];
    $DATA[$i]["net_actif"] = $value1['net'];
    /* Partie du Passif de la ligne */
    list ($key2, $value2) = each($ligne_passif);
    $DATA[$i]["compte_passif"] = $value2['compte'];
    $DATA[$i]["libel_passif"] = $value2['libel'];
    $DATA[$i]["solde_passif"] = $value2['solde'];
  }
  /* Ajout d'une ligne pour les totaux */
  $TOTAL = array ();
  $TOTAL["compte_actif"] = "TOTAL";
  $TOTAL["libel_actif"] = "";
  $TOTAL["solde_actif"] = $total_actif * (-1);
  $TOTAL["amort_actif"] = $total_amortissement;
  $TOTAL["net_actif"] = $total_net * (-1);
  $TOTAL["compte_passif"] = "";
  $TOTAL["libel_passif"] = "";
  $TOTAL["solde_passif"] = $total_passif;
  array_push($DATA, $TOTAL);
  $dbHandler->closeConnection(true);
  return $DATA;
}

/**
 * fonction:permet de calculer les  soldes des comptes associés chaque poste pour le rapport  bilan BNR
 * @param DATE $date_bilan	date du bialn
 * @param Integer $type_etat	type etat (rapport)
 * @param array  $liste_ag 	liste des agences
 * @param boolean	$consolide	true si on veut editer les etats consolidés (multi-agences)
 * @return array	$tempData les soldes du rapport
 *                      $tempData[id_poste]=solde
 */
function getBilan_BNR($date_bilan, $liste_ag,$consolide=NULL,$type_etat){
	global $dbHandler, $global_id_agence;
  global $global_multidevise;
  global $global_monnaie;
  $db = $dbHandler->openConnection();

  /* Le bilan est imprimé dans la devise de référence */
  $cv = true;
  //tab resultat exercice
  $ligne_exo=array();
  $sql="select num_cpte_comptable,a.id_poste from ad_poste a , ad_poste_compte b WHERE  a.id_poste=b.id_poste AND type_etat=".$type_etat." AND is_cpte_provision=false ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
  	 $dbHandler->closeConnection(false);
     signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  //traitement des comptes
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
  	foreach($liste_ag as $id_agence=>$libel_agence) {
  		// on travaille avec cette agence
      setGlobalIdAgence($id_agence);
      $where['num_cpte_comptable']=$row['num_cpte_comptable'];
		  $infos_cpte=getComptesComptables($where);
		  $solde=calculeSoldeBilan($row['num_cpte_comptable'],$date_bilan,$infos_cpte[$row['num_cpte_comptable']]['compart_cpte'],$cv ,$consolide);
		  $tempData[$row['id_poste']]['solde']+=(1)*$solde;
		  //net
		  $tempData[$row['id_poste']]['net']=$tempData[$row['id_poste']]['solde'];
  	}
  	resetGlobalIdAgence();
  }
  //recuperer les comptes de provisions
  $sql1="select num_cpte_comptable,a.id_poste from ad_poste a , ad_poste_compte b WHERE  a.id_poste=b.id_poste AND type_etat=".$type_etat." AND is_cpte_provision=true";
  $result_prov = $db->query($sql1);
  if (DB :: isError($result_prov)) {
  	 $dbHandler->closeConnection(false);
     signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  //calcul des soldes des comptes de provision pr chaque poste
  while ($row = $result_prov->fetchrow(DB_FETCHMODE_ASSOC)) {
  	foreach($liste_ag as $id_agence=>$libel_agence) {
  		// on travaille avec cette agence
      setGlobalIdAgence($id_agence);
 			$where['num_cpte_comptable']=$row['num_cpte_comptable'];
	  	$infos_cpte=getComptesComptables($where);
	  	$solde_prov=calculeSoldeBilan($where['num_cpte_comptable'],$date_bilan,$infos_cpte[$row['num_cpte_comptable']]['compart_cpte'],$cv ,$consolide);
	  	$tempData[$row['id_poste']]['amortissement']+=(1)*$solde_prov;
	  	//net
	    $tempData[$row['id_poste']]['net']+=(1)*$solde_prov;
  	}
  	resetGlobalIdAgence();
  }

  foreach($liste_ag as $id_agence=>$libel_agence) {
  	// on travaille avec cette agence
    setGlobalIdAgence($id_agence);
  	//si bilan consolidé
    if($consolide){
    	$condition=" AND consolide is not  true ";
    }
    // Affichage des résultats provisoires des exercices ouverts
    $infos_exos = getExercicesComptables(); // Infos sur tous les exercices de l'agence
    foreach ($infos_exos as $key => $exo) {
    	if ($exo['etat_exo'] != 3) {
    		//pg2phpDateBis
        $id_exo = $exo['id_exo_compta'];
        $datedeb = $exo['date_deb_exo'];
        $datefin = $exo['date_fin_exo'];

        // Somme mouvementts au crédit des comptes de gestion
        $sql = "SELECT SUM(mv.montant) FROM ad_mouvement mv, ad_ecriture ec, ad_cpt_comptable cpt WHERE ";
        $sql .= "mv.id_ag = ec.id_ag AND ec.id_ag = cpt.id_ag AND cpt.id_ag = $global_id_agence AND ec.date_comptable >= '$datedeb' AND ec.date_comptable <= '$datefin' AND ec.date_comptable <= '$date_bilan' AND mv.id_ecriture=ec.id_ecriture ";
        $sql .= "AND mv.sens='c' AND mv.compte=cpt.num_cpte_comptable AND (cpt.compart_cpte=3 OR cpt.compart_cpte=4)";
        $sql.=$condition;
        $result = $db->query($sql);
        if (DB :: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $row = $result->fetchrow();
        $somme_credit = $row[0];
        //if ($somme_credit > 0 and $cv)
        //  $somme_credit = calculeCV($value['devise'], $global_monnaie, $somme_credit);
        // Somme mouvements au débit des comptes de gestion
        $sql = "SELECT SUM(mv.montant) FROM ad_mouvement mv, ad_ecriture ec, ad_cpt_comptable cpt WHERE ";
        $sql .= "mv.id_ag = ec.id_ag AND ec.id_ag = cpt.id_ag AND cpt.id_ag = $global_id_agence AND ec.date_comptable >= '$datedeb' AND ec.date_comptable <= '$datefin' AND ec.date_comptable <= '$date_bilan' AND mv.id_ecriture=ec.id_ecriture ";
        $sql .= "AND mv.sens='d' AND mv.compte=cpt.num_cpte_comptable AND (cpt.compart_cpte=3 OR cpt.compart_cpte=4)";
        $sql.=$condition;
        $result = $db->query($sql);
        if (DB :: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $row = $result->fetchrow();
        $somme_debit = $row[0];
        //if ($somme_debit > 0 and $cv)
        //  $somme_debit = calculeCV($value['devise'], $global_monnaie, $somme_debit);
        // Si le résultat est non null, l'afficher dans la partie du Passif
        $resultat_exo = $somme_credit - $somme_debit;
        if ($resultat_exo != 0 and $resultat_exo != NULL) {
          if (!isset($ligne_exo["exercie".$id_exo]['solde']))
            $ligne_exo["exercie".$id_exo]['solde'] = $resultat_exo;
          else
            $ligne_exo["exercie".$id_exo]['solde'] += $resultat_exo;

          $ligne_exo["exercie".$id_exo]['compte'] = "exercice" .$id_exo;
          $ligne_exo["exercie".$id_exo]['libel'] = _(" Provisional result of fiscal year N°").$id_exo;

          $total_exos += $resultat_exo;
        }
    	} // Fin si exercice non fermé et antérieur exo en cours
    } // Fin parcours des exercices
  	// reinitialisation de global_id_agence
    resetGlobalIdAgence();
  }
  //mettre la somme total des resultats exo ds le tab ligne_exo
   //$ligne_exo[]
  //stocker les resultats des exercices ouverts
  $tempData['resultats_exo']=$ligne_exo ;

  $dbHandler->closeConnection(true);
  return $tempData;

}



function getschemas() {
	// renvoie les données pour l'edition des schémas comptables
	// IN : Neant
	// OUT: $DATA = array (type_operation,libel_operation,cpte_debit, cpte_credit)
	// Initialisation du tableau
	global $adsys, $global_id_agence;
	$DATA = array ();
	// récupération des opérations Adbanking
	$MyError = getOperations();
	if ($MyError->errCode != NO_ERR ) {
		return $MyError;
	} else {
		$schemas = $MyError->param;
	}

	while (list (, $operation) = each($schemas)) {
		$tmp = array ();
		$tmp["type_operation"] = $operation["type_operation"];
		$tmp["libel_ope"] = $operation["libel_ope"];
		//Compte au debit
		if ($operation["cptes"]["d"]["categorie_cpte"] != 0)
		$tmp["cpte_debit"] = $adsys["adsys_categorie_compte"][$operation["cptes"]["d"]["categorie_cpte"]];
		else {
			if ($operation["cptes"]["d"]["num_cpte"] == NULL)
			$tmp["cpte_debit"] = _("Non parametre");
			else {
				// Récupération du libellé du compte
				$temp = array ();
				$num_compte = $operation["cptes"]["d"]["num_cpte"];
				$temp['num_cpte_comptable'] = $num_compte;
				$compte = getComptesComptables($temp);
				$tmp["cpte_debit"] = $num_compte . " " . $compte[$num_compte]['libel_cpte_comptable'];
			}
		}
		//Compte au credit
		if ($operation["cptes"]["c"]["categorie_cpte"] != 0)
		$tmp["cpte_credit"] = $adsys["adsys_categorie_compte"][$operation["cptes"]["c"]["categorie_cpte"]];
		else {
			if ($operation["cptes"]["c"]["num_cpte"] == NULL)
			$tmp["cpte_credit"] = _("Non parametre");
			else {
				// Récupération du libellé du compte
				$temp = array ();
				$num_compte = $operation["cptes"]["c"]["num_cpte"];
				$temp['num_cpte_comptable'] = $num_compte;
				$compte = getComptesComptables($temp);
				$tmp["cpte_credit"] = $num_compte . " " . $compte[$num_compte]['libel_cpte_comptable'];
			}
		}
		array_push($DATA, $tmp);
	} // fin boucle
	return $DATA;
}
/**
 * Fonction renvoyant les ajustements de caisse
 * @author Antoine Guyette['resultats_exo']
 * @param date $date_debut début de l'intervalle de recherche
 * @param date $date_fin fin de l'intervalle de recherche
 * @return array $TMPARRAY liste des ajustements de caisse
 */
function getAjustementsCaisse($date_debut, $date_fin) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT c.sens, c.montant, c.devise, g.id_utilis, g.nom, g.prenom,a.date";
  $sql .= " FROM ad_his a, ad_ecriture b, ad_mouvement c, ad_cpt_comptable d, ad_gui e, ad_log f, ad_uti g";
  $sql .= " WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = d.id_ag AND d.id_ag = e.id_ag AND e.id_ag = $global_id_agence";
  $sql .= " AND a.type_fonction = 170";
  $sql .= " AND a.id_his = b.id_his";
  $sql .= " AND b.id_ecriture = c.id_ecriture";
  $sql .= " AND c.compte = d.num_cpte_comptable";
  $sql .= " AND (d.cpte_centralise = e.cpte_cpta_gui OR d.num_cpte_comptable = e.cpte_cpta_gui)";
  $sql .= " AND e.id_gui = f.guichet";
  $sql .= " AND f.id_utilisateur = g.id_utilis";
  // Date minimum
  if ($date_debut != '') {
    $sql .= " AND '$date_debut' <= date(a.date)";
  }
  // Date maximum
  if ($date_fin != '') {
    $sql .= " AND date(a.date) <= '$date_fin'";
  }
  $sql .= " ORDER BY g.id_utilis, c.devise";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $TMPARRAY = $TMPROW = array ();
  while ($ROW = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($ROW['id_utilis'] != $TMPROW['id_utilis'] || $ROW['devise'] != $TMPROW['devise']) {
      if (count($TMPROW) > 0) {
        array_push($TMPARRAY, $TMPROW);
      }
      $TMPROW['id_utilis'] = $ROW['id_utilis'];
      $TMPROW['utilisateur'] = $ROW['nom'] . " " . $ROW['prenom'];
      $TMPROW['devise'] = $ROW['devise'];
      $TMPROW['date_ajustement'] = $ROW['date'];
      $TMPROW['manquant'] = 0;
      $TMPROW['excedent'] = 0;
      $TMPROW['total'] = 0;
    }
    if ($ROW['sens'] == 'c') {
      $TMPROW['manquant'] += $ROW['montant'];
      $TMPROW['total'] -= $ROW['montant'];
    } else
      if ($ROW['sens'] == 'd') {
        $TMPROW['excedent'] += $ROW['montant'];
        $TMPROW['total'] += $ROW['montant'];
      }
  }
  if ($TMPROW != NULL) {
    array_push($TMPARRAY, $TMPROW);
  }
  $dbHandler->closeConnection(true);
  return $TMPARRAY;
}
/**
 * Renvoie les mouvements comptables reflexifs entre le siège et les agences à annuler à la consolidation
 * <li>
 *     <ul> mouvements passés par la fonction 473 (Gestion opérations siège/agence) sur les comptes reflets</ul>
 *     <ul> compte au débit de l'opération 600: Dépôt au siège</ul>
 *     <ul> compte au crédit de l'opération 601: Dépôt agence</ul>
 *     <ul> compte au crédit de l'opération 602: Emprunt auprès du siège</ul>
 *     <ul> compte au débit de l'opération 603: Prêts aux agences</ul>
 *     <ul> compte au débit de l'opération 604: Titres de participations</ul>
 *     <ul> compte au crédit de l'opération 605: Parts sociales agence</ul>
 *     <ul> compte au débit de l'opération 606: Participation aux charges du réseau</ul>
 *     <ul> compte au crédit de l'opération 607: Refacturatiion des charges du réseau</ul>

 *     <ul> compte au crédit de l'opération 608: Retrait au siège</ul>
 *     <ul> compte au débit de l'opération 609: Retrait des agences</ul>
 *     <ul> compte au débit de l'opération 610: Remboursement emprunt au siège</ul>
 *     <ul> compte au crédit l'opération 611: Remboursement prêts aux agences</ul>
 *     <ul> compte au crédit de l'opération 612: Récupération parts sociales</ul>
 *     <ul> compte au débit de l'opération 613: Remboursement parts sociales</ul>
 * </li>
 * @author Papa & Djibril
 * @since 2.9
 * @param date $date_debut date de début de la période
 * @param date $date_fin date de fin de la période
 * @param bool $consolide identificateur des mouvements déjà annulés
 * @param $liste_ag array liste des agences ($id_agence=>$libel_agence) pour lesquelles on veut afficher les mvts
 * @param $agence integer numero de l'agence :permet de selectionner les mvts passés entre $liste_ag et l'agence dont l'id = $agence
 * @param $type_oper integer numéro de l'opération
 * @return array Liste opérations comptables
 */
function getMouvementsReciproques($date_debut, $date_fin, $consolide, $liste_ag,$agence=NULL,$type_oper=NULL) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $TMPARRAY = array ();
  foreach($liste_ag as $id_agence=>$libel_agence) {
	    set_time_limit(0); // Eviter la deconnexion du script pour depassement de max execution time
	    setGlobalIdAgence($id_agence);
		  // mouvements entre le siège et les agences : fonction 473
		  $sql = "SELECT  mv.*, cpte.* FROM ad_flux_compta mv,ad_cpt_comptable cpte ";
		  $sql .= "WHERE ";
		  $sql .= " mv.id_ag = cpte.id_ag AND cpte.id_ag = $global_id_agence ";
		  $sql .= "AND mv.type_fonction = 473 ";
		  $sql .= "AND (date(mv.date_comptable) BETWEEN date('$date_debut') AND date('$date_fin')) ";
		  // mouvement sur les comptes reflet
		  $sql .= "AND ((mv.infos = '600' and mv.sens = 'd')
		          or (mv.infos = '601' and mv.sens = 'c')
		          or (mv.infos = '602' and mv.sens = 'c')
		          or (mv.infos = '603' and mv.sens = 'd')
		          or (mv.infos = '604' and mv.sens = 'd')
		          or (mv.infos = '605' and mv.sens = 'c')
		          or (mv.infos = '606' and mv.sens = 'd')
		          or (mv.infos = '607' and mv.sens = 'c')
		          or (mv.infos = '608' and mv.sens = 'c')
		          or (mv.infos = '609' and mv.sens = 'd')
		          or (mv.infos = '610' and mv.sens = 'd')
		          or (mv.infos = '611' and mv.sens = 'c')
		          or (mv.infos = '612' and mv.sens = 'c')
		          or (mv.infos = '613' and mv.sens = 'd')) ";
		  if ($consolide == 't')
		    $sql .= "AND mv.consolide = 't' "; // renvoie que les mouvements déjà consolidés (annulés)
		  elseif ($consolide == 'f')
		  $sql .= "AND (mv.consolide = 'f' or mv.consolide IS NULL) "; // renvoie que les mouvements qui ne sont pas encore consolidés
		  else {
		    // on renvoie tous les mouvement réciproques consolidés ou pas
		  }
		  if($agence!=NULL){
		  	$sql .=" AND mv.id_client= '$agence'";
		  }
		  if($type_oper!=NULL){
		  	$sql .=" AND mv.infos= '$type_oper' ";
		  }
		  $sql .= "AND mv.compte = cpte.num_cpte_comptable ";
		  $sql .= "ORDER BY mv.type_fonction DESC;";
		  $result = $db->query($sql);
		  if (DB :: isError($result)) {
		    $dbHandler->closeConnection(false);
		    signalErreur(__FILE__, __LINE__, __FUNCTION__);
		  }

		  while ($ligne = $result->fetchrow(DB_FETCHMODE_ASSOC))
		    array_push($TMPARRAY, $ligne);

		  resetGlobalIdAgence();

  } // fin parcours des agences
  $dbHandler->closeConnection(true);
  return $TMPARRAY;
}

/**
 * Renvoie les mouvements comptables reflexifs entre le siège et les agences à annuler à la consolidation

 * @author ares
 * @since 3.0
 * @param $compte $compte comptable
 * @param date $date_debut date de début de la période
 * @param date $date_fin date de fin de la période
 * @return bool ,true si on a passé des mouvements reflexifs entre le siège et les agences,false sinon
 */
function existeMouvementsReciproques($compte,$date_debut, $date_fin) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  // mouvements entre le siège et les agences : fonction 473
	$sql = "SELECT count(*) FROM ad_ecriture ecr,ad_mouvement mv,ad_cpt_comptable cpte, ad_his his ";
	$sql .= "WHERE ";
	$sql .= " ecr.id_ag = mv.id_ag AND mv.id_ag = cpte.id_ag AND cpte.id_ag = his.id_ag AND his.id_ag = $global_id_agence ";
	$sql .= "AND his.type_fonction = 473 ";
	$sql .= "AND mv.compte = '$compte' ";
	if($date_debut==NULL){
		  $sql .= "AND (date(ecr.date_comptable) <= date('$date_fin')) ";
	}elseif($date_fin==NULL){
			$sql .= "AND (date(ecr.date_comptable) <= date('$date_debut')) ";
	}else{
			$sql .= "AND (date(ecr.date_comptable) BETWEEN date('$date_debut') AND date('$date_fin')) ";
	}

  // mouvement sur les comptes reflet
  $sql .= "AND ((his.infos = '600' and mv.sens = 'd')
		          or (his.infos = '601' and mv.sens = 'c')
		          or (his.infos = '602' and mv.sens = 'c')
		          or (his.infos = '603' and mv.sens = 'd')
		          or (his.infos = '604' and mv.sens = 'd')
		          or (his.infos = '605' and mv.sens = 'c')
		          or (his.infos = '606' and mv.sens = 'd')
		          or (his.infos = '607' and mv.sens = 'c')
		          or (his.infos = '608' and mv.sens = 'c')
		          or (his.infos = '609' and mv.sens = 'd')
		          or (his.infos = '610' and mv.sens = 'd')
		          or (his.infos = '611' and mv.sens = 'c')
		          or (his.infos = '612' and mv.sens = 'c')
		          or (his.infos = '613' and mv.sens = 'd')) ";

	$sql .= "AND ecr.id_ecriture = mv.id_ecriture ";
	$sql .= "AND mv.compte = cpte.num_cpte_comptable ";
	$sql .= "AND ecr.id_his = his.id_his ";


	$result = $db->query($sql);
	if (DB :: isError($result)) {
		    $dbHandler->closeConnection(false);
		    signalErreur(__FILE__, __LINE__, __FUNCTION__);
		  }
 $row = $result->fetchrow();

  $dbHandler->closeConnection(true);
  return $row[0]>0;
}


/**
 * Recherche dans l'historique des libellés (Table: ad_libelle) le libellé valable pour un enregistrement pour une date donnée
 * ou renvoie le libellé courant dans la table de référence si il n'y a pas d'historique
 *
 * @param TEXT $ident_libelle : identifiant de l'enrégistrement dont on cherche le libellé valable à a date donné en paramètre
 * @param TEXT $type_libelle : nom de la table contenant ce enrégistrement
 * @param DATE $date_rapport : date pour laquelle on cherche le libellé valable
 * @return TEXT $libelle_valable : libellé trouvé
 * @author Stefano A.
 * @since Juin 2007
 * @version 2.10
 */
function getLibelleValable($ident_libelle,$type_libelle,$date_rapport) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT libelle FROM ad_libelle WHERE id_ag = $global_id_agence AND date_modification = (SELECT min(date_modification) FROM ad_libelle WHERE id_ag = $global_id_agence AND type_libelle='$type_libelle' AND ident='$ident_libelle' AND date_modification > '$date_rapport')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $sql = "SELECT libel_cpte_comptable FROM ad_cpt_comptable WHERE id_ag = $global_id_agence and num_cpte_comptable='$ident_libelle'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  }
  $dbHandler->closeConnection(true);
  $libelle_valable = $result->fetchrow();

  return $libelle_valable[0];
}
/**
 * Fonction renvoyant les indicateurs d'agence
 * @author Antoine Guyette
 */
function getIndicateursAgence($list_agence,$ratios_prudentiels, $qualite_portefeuille, $indices_couverture, $indices_productivite, $indices_impact) {
  global $global_monnaie,$global_id_agence;
  $DATA = array();

  // Ajout du nombre d'agence selectionné dans tableau contenant les statistiques de l'agence ou du réseau
  $DATA['a_nombreAgence'] = count($list_agence);

  if ($DATA['a_nombreAgence'] <= 1) {
    setGlobalIdAgence(key($list_agence));
    $DATA['a_infosGlobales'] = getAgenceDatas($global_id_agence);
    resetGlobalIdAgence();
  }

  if ($ratios_prudentiels) {
    $RatiosPrudentiels = getRatiosPrudentiels($list_agence);
    $DATA['ratios_prudentiels'] = $RatiosPrudentiels;
  }

  if ($qualite_portefeuille) {
    $QualitePortefeuille = getQualitePortefeuille($list_agence);
    $DATA['qualite_portefeuille'] = $QualitePortefeuille;
  }

  if ($indices_couverture) {
    $IndicesCouverture = getIndicesCouverture($list_agence);
    $DATA['indices_couverture'] = $IndicesCouverture;
  }

  if ($indices_productivite) {
    $IndicesProductivite = getIndicesProductivite($list_agence);
    $DATA['indices_productivite'] = $IndicesProductivite;
  }

  if ($indices_impact) {
    $IndicesImpact = getIndicesImpact($list_agence);
    $DATA['indices_impact'] = $IndicesImpact;
  }

  if ($DATA['a_nombreAgence'] > 1) {
    resetGlobalIdAgence();
  }

  return $DATA;
}

/**
 * Fonction renvoyant les ratios prudentiels
 * @author Antoine Guyette
 */
function getRatiosPrudentiels($list_agence) {

  global $dbHandler;
  global $global_monnaie,$global_id_agence;

  $db = $dbHandler->openConnection();
  $DATA['limitation_prets_dirigeants'] = 0;
  $DATA['limitation_risque_membre'] = 0;
  $DATA['taux_transformation'] = 0;
  $DATA['total_epargne'] = 0;
  foreach($list_agence as $key_id_ag =>$value) {
    // Parcours des agences
    setGlobalIdAgence($key_id_ag);
    // Encours brut
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15)";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_brut = $row[0];

    // Encours max
    $sql = "SELECT MAX(somme) FROM (SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) AS somme FROM ad_etr, ad_dcr, adsys_produit_credit WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) GROUP BY ad_etr.id_doss) AS encours";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_max = $row[0];

    // Encours dirigeants
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit, ad_cli WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND adsys_produit_credit.id_ag=ad_cli.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND ad_dcr.id_client = ad_cli.id_client AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND ad_cli.qualite = 4";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_dirigeants = $row[0];

    // Total épargne
    $sql = "SELECT SUM(calculeCV(solde, devise, '$global_monnaie')) FROM ad_cpt WHERE id_ag=$global_id_agence AND id_prod <> 2 AND id_prod <> 3";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $total_epargne = $row[0];

    // Tableau de données
    if ($total_epargne == NULL) {
      $DATA['limitation_prets_dirigeants'] = NULL;
      $DATA['limitation_risque_membre'] = NULL;
      $DATA['taux_transformation'] = NULL;
      $DATA['total_epargne'] = NULL;
    } else {
      $DATA['limitation_prets_dirigeants'] += $encours_dirigeants / $total_epargne;
      $DATA['limitation_risque_membre'] += $encours_max / $total_epargne;
      $DATA['taux_transformation'] += $encours_brut / $total_epargne;
      $DATA['total_epargne'] += $total_epargne;
    }
  }
  $dbHandler->closeConnection(true);

  return $DATA;
}

/**
 * Fonction renvoyant la qualité du portefeuille
 * @author Antoine Guyette
 */
function getQualitePortefeuille($list_agence) {

  global $dbHandler;
  global $global_monnaie,$global_id_agence;

  $db = $dbHandler->openConnection();
  $DATA['risque_1_ech'] = 0;
  $DATA['risque_2_ech'] = 0;
  $DATA['risque_3_ech'] = 0;
  $DATA['risque_30_jours'] = 0;
  $DATA['taux_provisions'] =  0;
  $DATA['taux_reech'] = 0;
  $DATA['taux_perte'] = 0;
  $DATA['encours_brut'] = 0;
  foreach($list_agence as $key_id_ag =>$value) {
    //Parcours des agences
    setGlobalIdAgence($key_id_ag);
    // Encours des crédits en retard d'au moins 1 échéance
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE ad_etr.id_ag= $global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND (SELECT COUNT(*) FROM ad_etr WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_doss = ad_dcr.id_doss AND remb = 'f' AND date_ech < current_date) >= 1";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_1_ech = $row[0];

    // Encours des crédits en retard d'au moins 2 échéances
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND (SELECT COUNT(*) FROM ad_etr WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_doss = ad_dcr.id_doss AND remb = 'f' AND date_ech < current_date) >= 2";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_2_ech = $row[0];

    // Encours des crédits en retard d'au moins 3 échéances
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND (SELECT COUNT(*) FROM ad_etr WHERE ad_etr.id_doss = ad_dcr.id_doss AND remb = 'f' AND date_ech < current_date) >= 3";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_3_ech = $row[0];

    // Encours des crédits en retard de plus de 30 jours
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND (SELECT COUNT(*) FROM ad_etr WHERE ad_etr.id_doss = ad_dcr.id_doss AND remb = 'f' AND date_ech < current_date - INTERVAL '30 days') >= 1";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_30_jours = $row[0];

    // Encours net
    $encours_net = 0;

    // Encours des crédits rééchelonnés
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND cre_nbre_reech > 1";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_reechelonnes = $row[0];

    // Encours des crédits passés en perte
    $sql = "SELECT SUM(calculeCV(perte_capital, devise, '$global_monnaie')) FROM ad_dcr, adsys_produit_credit WHERE ad_dcr.id_ag=$global_id_agence AND  ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_dcr.id_prod = adsys_produit_credit.id AND ad_dcr.etat = 9 AND ad_dcr.date_etat BETWEEN date_trunc('year', current_date) AND current_date";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_perte = $row[0];

    // Encours brut
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15)";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_brut = $row[0];

    // Tableau de données
    if ($encours_brut == NULL) {
      $DATA['risque_1_ech'] = NULL;
      $DATA['risque_2_ech'] = NULL;
      $DATA['risque_3_ech'] = NULL;
      $DATA['risque_30_jours'] = NULL;
      $DATA['taux_provisions'] =  NULL;
      $DATA['taux_reech'] = NULL;
      $DATA['taux_perte'] = NULL;
      $DATA['encours_brut'] = NULL;
    } else {
      $DATA['risque_1_ech'] += $encours_1_ech / $encours_brut;
      $DATA['risque_2_ech'] += $encours_2_ech / $encours_brut;
      $DATA['risque_3_ech'] += $encours_3_ech / $encours_brut;
      $DATA['risque_30_jours'] += $encours_30_jours / $encours_brut;
      $DATA['taux_provisions'] +=  $encours_net / $encours_brut;
      $DATA['taux_reech'] += $encours_reechelonnes / $encours_brut;
      $DATA['taux_perte'] += $encours_perte / $encours_brut;
      $DATA['encours_brut'] += $encours_brut;
    }
  }
  $dbHandler->closeConnection(true);

  return $DATA;
}

/**
 * Fonction renvoyant les indices de couverture
 * @author Antoine Guyette
 */
function getIndicesCouverture($list_agence) {

  global $dbHandler;
  global $global_monnaie,$global_id_agence;

  $db = $dbHandler->openConnection();
  $DATA['nombre_credits'] = 0;
  $DATA['encours_brut'] = 0;
  $DATA['nombre_epargne'] = 0;
  $DATA['total_epargne'] = 0;
  $DATA['taux_renouvellement_credits'] = 0;
  $DATA['first_credit_moyen'] = 0;
  $DATA['first_credit_median'] = 0;
  $DATA['credit_moyen'] = 0;
  $DATA['credit_median'] = 0;
  $DATA['epargne_moyen_cpte'] = 0;
  $DATA['epargne_median_cpte'] = 0;
  $DATA['epargne_moyen_client'] = 0;
  $DATA['epargne_median_client'] = 0;
  foreach($list_agence as $key_id_ag =>$value) {
    //Parcours des agences
    setGlobalIdAgence($key_id_ag);
    // Nombre de crédits actifs
    $sql = "SELECT COUNT(*) FROM ad_dcr WHERE id_ag=$global_id_agence AND etat = 5 OR etat = 7 OR etat = 13 OR etat = 14 OR etat = 15";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $nombre_credits = $row[0];

    // Encours brut
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15)";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_brut = $row[0];

    // Nombre de comptes d'épargne
    $sql = "SELECT COUNT(*) FROM ad_cpt WHERE id_ag=$global_id_agence AND id_prod <> 2 AND id_prod <> 3 AND etat_cpte <> 2 ";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $nombre_epargne = $row[0];

    // Volume total d'épargne
    $sql = "SELECT SUM(calculeCV(solde, devise, '$global_monnaie')) FROM ad_cpt WHERE id_ag=$global_id_agence AND id_prod <> 2 AND id_prod <> 3 AND etat_cpte <> 2 AND solde <> 0";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $total_epargne = $row[0];

    // Nombre de crédits relais consentis
    $sql = "SELECT count(id_doss) FROM ad_dcr a WHERE (a.id_ag=$global_id_agence) AND (a.etat = 5 OR a.etat = 7 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) AND a.date_etat BETWEEN date_trunc('year', current_date) AND current_date AND EXISTS (SELECT id_doss FROM ad_dcr b WHERE b.id_ag=$global_id_agence AND b.etat = 6 and b.date_etat < a.date_etat AND a.id_client = b.id_client)";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $credits_relais = $row[0];

    // Crédits remboursés
    $sql = "SELECT count(id_doss) FROM ad_dcr WHERE id_ag=$global_id_agence AND etat = 6 AND date_etat BETWEEN date_trunc('year', current_date) AND current_date";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $credits_rembourses = $row[0];

    // Calcul du taux
    if ($credits_rembourses > 0) {
      $taux_renouvellement_credits = $credits_relais / $credits_rembourses;
    } else {
      $taux_renouvellement_credits = NULL;
    }

    // Moyenne premiers crédits
    $sql = "SELECT AVG(calculeCV(cre_mnt_octr, devise, '$global_monnaie')) FROM ad_dcr a, adsys_produit_credit b WHERE a.id_ag=$global_id_agence AND a.id_ag=b.id_ag AND a.id_prod = b.id AND (a.etat = 5 OR a.etat = 7 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) AND cre_date_debloc BETWEEN date_trunc('year', current_date) AND current_date AND NOT EXISTS (SELECT id_doss FROM ad_dcr b WHERE b.id_ag=$global_id_agence AND b.etat = 6 AND b.date_etat < a.cre_date_debloc AND a.id_client = b.id_client)";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $first_credit_moyen = $row[0];

    // Médiane premiers crédits
    $sql = "SELECT calculeCV(cre_mnt_octr, devise, '$global_monnaie') FROM ad_dcr a, adsys_produit_credit b WHERE a.id_ag=$global_id_agence AND a.id_ag=b.id_ag AND a.id_prod = b.id AND (a.etat = 5 OR a.etat = 7 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) AND cre_date_debloc BETWEEN date_trunc('year', current_date) AND current_date AND NOT EXISTS (SELECT id_doss FROM ad_dcr b WHERE b.id_ag=$global_id_agence AND b.etat = 6 AND b.date_etat < a.cre_date_debloc AND a.id_client = b.id_client) ORDER BY calculeCV(cre_mnt_octr, devise, '$global_monnaie')";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $tableauCredit = array();
    while ($row = $result->fetchrow()) {
      array_push($tableauCredit, $row[0]);
    }

    $taille = sizeof($tableauCredit);
    if ($taille == 0) {
      $first_credit_median = NULL;
    }
    elseif ($taille % 2 == 1) {
      $first_credit_median = $tableauCredit[($taille -1) / 2];
    }
    else {
      $first_credit_median = ($tableauCredit[($taille) / 2] + $tableauCredit[($taille / 2) - 1]) / 2;
    }

    // Moyenne crédit
    $sql = "SELECT AVG(calculeCV(cre_mnt_octr, devise, '$global_monnaie')) FROM ad_dcr a, adsys_produit_credit b WHERE a.id_ag=$global_id_agence AND a.id_ag=b.id_ag AND a.id_prod = b.id AND (a.etat = 5 OR a.etat = 7 OR a.etat = 13 OR a.etat = 14 OR a.etat = 15) AND cre_date_debloc BETWEEN date_trunc('year', current_date) AND current_date";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $credit_moyen = $row[0];

    // Médiane crédit
    $sql = "SELECT calculeCV(cre_mnt_octr, devise, '$global_monnaie') FROM ad_dcr a, adsys_produit_credit b WHERE a.id_ag=$global_id_agence AND a.id_ag=b.id_ag AND a.id_prod = b.id AND (etat = 5 OR etat = 7 OR etat = 13 OR etat = 14 OR etat = 15)
           ORDER BY calculeCV(cre_mnt_octr, devise, '$global_monnaie')";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $tableauCredit = array();
    while ($row = $result->fetchrow()) {
      array_push($tableauCredit, $row[0]);
    }

    $taille = sizeof($tableauCredit);
    if ($taille == 0) {
      $credit_median = NULL;
    }
    elseif ($taille % 2 == 1) {
      $credit_median = $tableauCredit[($taille -1) / 2];
    }
    else {
      $credit_median = ($tableauCredit[($taille) / 2] + $tableauCredit[($taille / 2) - 1]) / 2;
    }

    // Moyenne épargne par compte
    $sql = "SELECT AVG(calculeCV(solde, devise, '$global_monnaie')) FROM ad_cpt WHERE id_ag=$global_id_agence AND id_prod <> 2 AND id_prod <> 3 AND etat_cpte <> 2 AND solde <> 0";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $epargne_moyen_cpte = $row[0];

    // Médiane épargne par compte
    $sql = "SELECT calculeCV(solde, devise, '$global_monnaie') FROM ad_cpt WHERE id_ag=$global_id_agence AND id_prod <> 2 AND id_prod <> 3 AND etat_cpte <> 2 AND solde <> 0 ORDER BY calculeCV(solde, devise, '$global_monnaie')";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $tableauCredits = array ();
    while ($row = $result->fetchrow()) {
      array_push($tableauCredits, $row[0]);
    }

    $taille = sizeof($tableauCredits);
    if ($taille == 0) {
      $epargne_median_cpte = NULL;
    }
    elseif ($taille % 2 == 1) {
      $epargne_median_cpte = $tableauCredits[($taille -1) / 2];
    }
    else {
      $epargne_median_cpte = ($tableauCredits[($taille) / 2] + $tableauCredits[($taille / 2) - 1]) / 2;
    }

    // Moyenne épargne par client
    $sql = "SELECT AVG(t.sum) FROM (SELECT SUM(calculeCV(solde, devise, '$global_monnaie')) FROM ad_cpt WHERE id_ag=$global_id_agence AND id_prod <> 2 AND id_prod <> 3 AND etat_cpte <> 2 AND solde <> 0 GROUP BY id_titulaire) AS t";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $epargne_moyen_client = $row[0];

    // Médiane épargne par client
    $sql = "SELECT SUM(calculeCV(solde, devise, '$global_monnaie')) AS solde, id_titulaire FROM ad_cpt WHERE id_ag=$global_id_agence AND id_prod <> 2 AND id_prod <> 3 AND solde <> 0 GROUP BY id_titulaire ORDER BY solde";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $tableauEpargne = array();
    while ($row = $result->fetchrow()) {
      array_push($tableauEpargne, $row[0]);
    }

    $taille = sizeof($tableauEpargne);
    if ($taille == 0) {
      $epargne_median_client = NULL;
    }
    elseif ($taille % 2 == 1) {
      $epargne_median_client = $tableauEpargne[($taille -1) / 2];
    }
    else {
      $epargne_median_client = ($tableauEpargne[($taille) / 2] + $tableauEpargne[($taille / 2) - 1]) / 2;
    }

    // Tableau de données
    $DATA['nombre_credits'] += $nombre_credits;
    $DATA['encours_brut'] += $encours_brut;
    $DATA['nombre_epargne'] += $nombre_epargne;
    $DATA['total_epargne'] += $total_epargne;
    $DATA['taux_renouvellement_credits'] += $taux_renouvellement_credits;
    $DATA['first_credit_moyen'] += $first_credit_moyen;
    $DATA['first_credit_median'] += $first_credit_median;
    $DATA['credit_moyen'] += $credit_moyen;
    $DATA['credit_median'] += $credit_median;
    $DATA['epargne_moyen_cpte'] += $epargne_moyen_cpte;
    $DATA['epargne_median_cpte'] += $epargne_median_cpte;
    $DATA['epargne_moyen_client'] += $epargne_moyen_client;
    $DATA['epargne_median_client'] += $epargne_median_client;
  }
  $dbHandler->closeConnection(true);

  return $DATA;
}

/**
 * Fonction renvoyant les indices de productivité
 * @author Antoine Guyette
 */
function getIndicesProductivite($list_agence) {

  global $dbHandler;
  global $global_monnaie,$global_id_agence;

  $db = $dbHandler->openConnection();
  $DATA['rendement_portefeuille'] = 0;
  $DATA['rendement_theorique'] = 0;
  $DATA['encours_net'] = 0;
  $DATA['ecart_rendement'] = 0;
  $DATA['remb_attendus'] = 0;
  foreach($list_agence as $key_id_ag =>$value) {
    //Parcours des agences
    setGlobalIdAgence($key_id_ag);
    // Remboursements en intérêts effectués
    $sql = "SELECT SUM(calculeCV(mnt_remb_int, devise, '$global_monnaie')) FROM ad_sre, ad_dcr, adsys_produit_credit WHERE ad_sre.id_ag=$global_id_agence AND ad_sre.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_sre.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND date_remb BETWEEN date_trunc('year', current_date) AND current_date";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $remb_effectues = $row[0];

    // Remboursements en intérêts attendus
    $sql = "SELECT SUM(calculeCV(mnt_int, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE  ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND date_ech BETWEEN date_trunc('year', current_date) AND current_date";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $remb_attendus = $row[0];

    // Encours brut
    $sql = "SELECT SUM(calculeCV(solde_cap, devise, '$global_monnaie')) FROM ad_etr, ad_dcr, adsys_produit_credit WHERE  ad_etr.id_ag=$global_id_agence AND ad_etr.id_ag=ad_dcr.id_ag AND ad_dcr.id_ag=adsys_produit_credit.id_ag AND ad_etr.id_doss = ad_dcr.id_doss AND ad_dcr.id_prod = adsys_produit_credit.id AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15)";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    $encours_brut = $row[0];

    // Calcul de l'indice
    if ($remb_attendus == 0 || $remb_attendus == '') {
      $stat['ecart_rendement'] = "Non disponible";
    } else {
      $stat['ecart_rendement'] = $remb_effectues / $remb_attendus - 1;
    }

    // Calcul du rendement theorique du portefeuille
    if ($stat['net'] > 0) {
      $stat['rendement_theorique'] = $remb_attendus / $stat['net'];
    } else {
      $stat['rendement_theorique'] = "Non disponible";
    }

    // Calcul du rendement du portefeuille
    if ($stat['net'] > 0) {
      $stat['rendement_portefeuille'] = $remb_effectues / $stat['net'];
    } else {
      $stat['rendement_portefeuille'] = "Non disponible";
    }

    $encours_net = $encours_brut;

    // Tableau de données
    if ($encours_net == NULL) {
      $DATA['rendement_portefeuille'] = NULL;
      $DATA['rendement_theorique'] = NULL;
      $DATA['encours_net'] = NULL;
    } else {
      $DATA['rendement_portefeuille'] += $remb_effectues / $encours_net;
      $DATA['rendement_theorique'] += $remb_attendus / $encours_net;
      $DATA['encours_net'] += $encours_net;
    }

    if ($remb_attendus == NULL) {
      $DATA['ecart_rendement'] = NULL;
      $DATA['remb_attendus'] = NULL;
    } else {
      $DATA['ecart_rendement'] += $remb_effectues / $remb_attendus - 1;
      $DATA['remb_attendus'] += $remb_attendus;
    }
  }
  $dbHandler->closeConnection(true);

  return $DATA;
}

/**
 * Fonction renvoyant les indices d'impact
 * @author Antoine Guyette
 */
function getIndicesImpact($list_agence) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $DATA['general']['nombre_moyen_gs']=0;
  $DATA['clients']['pp'] = 0;
  $DATA['clients']['homme'] = 0;
  $DATA['clients']['femme'] = 0;
  $DATA['clients']['pm'] = 0;
  $DATA['clients']['gi'] = 0;
  $DATA['clients']['gs'] = 0;
  $DATA['clients']['total']=0;
  $DATA['epargnants']['pp'] = 0;
  $DATA['epargnants']['homme'] = 0;
  $DATA['epargnants']['femme'] = 0;
  $DATA['epargnants']['pm'] = 0;
  $DATA['epargnants']['gi'] = 0;
  $DATA['epargnants']['gs'] = 0;
  $DATA['epargnants']['total']=0;
  $DATA['emprunteurs']['pp'] = 0;
  $DATA['emprunteurs']['homme'] = 0;
  $DATA['emprunteurs']['femme'] = 0;
  $DATA['emprunteurs']['pm'] = 0;
  $DATA['emprunteurs']['gi'] = 0;
  $DATA['emprunteurs']['gs'] = 0;
  $DATA['general']['total_membre_empr_gi'] = 0;
  $DATA['general']['total_membre_empr_gs'] = 0;
  $DATA['general']['nombre_moyen_gi'] =0;
  $DATA['general']['nombre_moyen_gs'] =0;
  $DATA['general']['gi_nbre_membre'] =0;
  $DATA['general']['gs_nbre_membre'] =0;
  $DATA['general']['gs_nbre_hommes'] =0;
  $DATA['general']['gs_nbre_femmes'] =0;
  $nb_agence=0;
  foreach($list_agence as $key_id_ag =>$value) {
    //Parcours des agences
    setGlobalIdAgence($key_id_ag);
    $nb_agence++;
    // Nombre de clients
    $sql = "SELECT statut_juridique, pp_sexe, count(id_client) FROM ad_cli WHERE id_ag=$global_id_agence AND etat = 2 GROUP BY statut_juridique, pp_sexe";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      if ($row['statut_juridique'] == 2) {
        $DATA['clients']['pm'] += $row['count'];
      }
      elseif ($row['statut_juridique'] == 3) {
        $DATA['clients']['gi'] += $row['count'];
      }
      elseif ($row['statut_juridique'] == 4) {
        $DATA['clients']['gs'] += $row['count'];
      }
      elseif ($row['pp_sexe'] == 1) {
        $DATA['clients']['homme'] += $row['count'];
      }
      elseif ($row['pp_sexe'] == 2) {
        $DATA['clients']['femme'] += $row['count'];
      }
    }

    $DATA['clients']['pp'] += $DATA['clients']['homme'] + $DATA['clients']['femme'];
    $DATA['clients']['total'] += $DATA['clients']['pp'] + $DATA['clients']['pm'] + $DATA['clients']['gi'] + $DATA['clients']['gs'];

    if ($DATA['clients']['pp'] == 0) {
      $DATA['clients']['pourcentage_homme'] = 0;
      $DATA['clients']['pourcentage_femme'] = 0;
    } else {
      $DATA['clients']['pourcentage_homme'] = $DATA['clients']['homme'] / $DATA['clients']['pp'];
      $DATA['clients']['pourcentage_femme'] = $DATA['clients']['femme'] / $DATA['clients']['pp'];
    }

    // Nombre d'épargnants
    $sql = "SELECT statut_juridique, pp_sexe, count(id_client) FROM ad_cli WHERE id_ag=$global_id_agence AND (SELECT count(*) FROM ad_cpt WHERE id_ag=$global_id_agence AND id_client = id_titulaire AND id_prod <> 2 AND id_prod <> 3 AND solde <> 0) > 0 GROUP BY statut_juridique, pp_sexe";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      if ($row['statut_juridique'] == 2) {
        $DATA['epargnants']['pm'] += $row['count'];
      }
      elseif ($row['statut_juridique'] == 3) {
        $DATA['epargnants']['gi'] += $row['count'];
      }
      elseif ($row['statut_juridique'] == 4) {
        $DATA['epargnants']['gs'] += $row['count'];
      }
      elseif ($row['pp_sexe'] == 1) {
        $DATA['epargnants']['homme'] += $row['count'];
      }
      elseif ($row['pp_sexe'] == 2) {
        $DATA['epargnants']['femme'] += $row['count'];
      }
    }

    $DATA['epargnants']['pp'] += $DATA['epargnants']['homme'] + $DATA['epargnants']['femme'];
    $DATA['epargnants']['total'] += $DATA['epargnants']['pp'] + $DATA['epargnants']['pm'] + $DATA['epargnants']['gi'] + $DATA['epargnants']['gs'];

    if ($DATA['epargnants']['pp'] == 0) {
      $DATA['epargnants']['pourcentage_homme'] = 0;
      $DATA['epargnants']['pourcentage_femme'] = 0;
    } else {
      $DATA['epargnants']['pourcentage_homme'] = $DATA['epargnants']['homme'] / $DATA['epargnants']['pp'];
      $DATA['epargnants']['pourcentage_femme'] = $DATA['epargnants']['femme'] / $DATA['epargnants']['pp'];
    }


    // Nombre d'emprunteurs
    $sql = "SELECT statut_juridique, pp_sexe, count(ad_cli.id_client) FROM ad_cli, ad_dcr WHERE ad_cli.id_ag=$global_id_agence AND ad_cli.id_ag=ad_dcr.id_ag AND ad_cli.id_client = ad_dcr.id_client AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) GROUP BY statut_juridique, pp_sexe";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      if ($row['statut_juridique'] == 2) {
        $DATA['emprunteurs']['pm'] += $row['count'];
      }
      elseif ($row['statut_juridique'] == 3) {
        $DATA['emprunteurs']['gi'] += $row['count'];
      }
      elseif ($row['statut_juridique'] == 4) {
        $DATA['emprunteurs']['gs'] += $row['count'];
      }
      elseif ($row['pp_sexe'] == 1) {
        $DATA['emprunteurs']['homme'] += $row['count'];
      }
      elseif ($row['pp_sexe'] == 2) {
        $DATA['emprunteurs']['femme'] += $row['count'];
      }
    }

    $DATA['emprunteurs']['pp'] += $DATA['emprunteurs']['homme'] + $DATA['emprunteurs']['femme'];
    $DATA['emprunteurs']['total'] += $DATA['emprunteurs']['pp'] + $DATA['emprunteurs']['pm'] + $DATA['emprunteurs']['gi'] + $DATA['emprunteurs']['gs'];

    if ($DATA['emprunteurs']['pp'] == 0) {
      $DATA['emprunteurs']['pourcentage_homme'] = 0;
      $DATA['emprunteurs']['pourcentage_femme'] = 0;
    } else {
      $DATA['emprunteurs']['pourcentage_homme'] = $DATA['emprunteurs']['homme'] / $DATA['emprunteurs']['pp'];
      $DATA['emprunteurs']['pourcentage_femme'] = $DATA['emprunteurs']['femme'] / $DATA['emprunteurs']['pp'];
    }

   // Nombre total de membres emprunteurs par groupe informel
    $sql = "SELECT SUM(ad_cli.gi_nbre_membr) FROM ad_cli, ad_dcr WHERE ad_cli.id_ag=$global_id_agence AND ad_cli.id_ag=ad_dcr.id_ag AND ad_cli.id_client = ad_dcr.id_client AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND ad_cli.statut_juridique = 3";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $DATA['general']['total_membre_empr_gi'] += round($row[0]);

    // Nombre total de membres emprunteurs par groupe solidaire
    $sql = "SELECT SUM(ad_cli.gi_nbre_membr) FROM ad_cli, ad_dcr WHERE ad_cli.id_ag=$global_id_agence AND ad_cli.id_ag=ad_dcr.id_ag AND ad_cli.id_client = ad_dcr.id_client AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND ad_cli.statut_juridique = 4";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $DATA['general']['total_membre_empr_gs'] += round($row[0]);

    // Nombre total d'hommes groupe solidaire
    $sql = "SELECT count(*) FROM ad_grp_sol,ad_cli where id_client=id_membre AND pp_sexe= 1 ";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $DATA['general']['total_homme_gs'] = round($row[0]);
    // Nombre total de femmes groupe solidaire
    $sql = "SELECT count(*) FROM ad_grp_sol,ad_cli where id_client=id_membre AND pp_sexe= 2 ";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $DATA['general']['total_femme_gs'] = round($row[0]);

    // Nombre moyen de membres par groupe informel
    $sql = "SELECT AVG(ad_cli.gi_nbre_membr) FROM ad_cli, ad_dcr WHERE ad_cli.id_ag=$global_id_agence AND ad_cli.id_ag=ad_dcr.id_ag AND ad_cli.id_client = ad_dcr.id_client AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND ad_cli.statut_juridique = 3";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $DATA['general']['nombre_moyen_gi'] += round($row[0]);

    // Nombre moyen de membres par groupe solidaire
    $sql = "SELECT AVG(ad_cli.gi_nbre_membr) FROM ad_cli, ad_dcr WHERE ad_cli.id_ag=$global_id_agence AND ad_cli.id_ag=ad_dcr.id_ag AND ad_cli.id_client = ad_dcr.id_client AND (ad_dcr.etat = 5 OR ad_dcr.etat = 7 OR ad_dcr.etat = 13 OR ad_dcr.etat = 14 OR ad_dcr.etat = 15) AND ad_cli.statut_juridique = 4";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $DATA['general']['nombre_moyen_gs'] += round($row[0]);
  }
  $DATA['general']['nombre_moyen_gi']=$DATA['general']['nombre_moyen_gi']/$nb_agence;
  $DATA['general']['nombre_moyen_gs']=$DATA['general']['nombre_moyen_gs']/$nb_agence;
  $dbHandler->closeConnection(true);
  return $DATA;
}
/**
 * Recherche ds l'historique tous les credits repris entre la dateDeb, et la dateFin'
 * @param DATE $dateDeb : date minimale pr les credits repris
 * @param DATE $date_rapport : date maximale pr les credits repris
 * @return ErrorObj Objet Erreur
 */
function getCreditRepris($dateDeb,$dateFin){
	global $global_id_agence;

  $sql =" SELECT m.date_valeur as date_reprise,c.pp_nom,c.pp_prenom,c.pm_raison_sociale,c.gi_nom,c.anc_id_client , c.statut_juridique,c.id_client,pc.libel , d.id_prod, d.id_doss , cast(m.montant as integer) as mnt_repris,d.cre_etat " ;
  $sql.=" FROM ad_mouvement m, ad_ecriture e, ad_his h,  ad_dcr d, ad_cli c ,adsys_produit_credit pc " ;
  $sql.=" WHERE m.id_ecriture = e.id_ecriture and e.id_his= h.id_his and d.id_prod=pc.id and h.type_fonction = 503  and d.cre_id_cpte = m.cpte_interne_cli and c.id_client = d.id_client " ;
  $sql.=" AND m.id_ag = e.id_ag AND e.id_ag = h.id_ag AND h.id_ag = d.id_ag AND d.id_ag = c.id_ag AND c.id_ag = pc.id_ag AND c.id_ag='$global_id_agence'";
  $sql.=" AND ( date(h.date) BETWEEN '$dateDeb' AND '$dateFin') ";
  $sql.=" ORDER BY m.date_valeur,pc.libel,id_client asc";

 $result=executeDirectQuery($sql);
 if ($result->errCode != NO_ERR)
 signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->param);
  return $result;

}

/**
 * Recherche ds l'historique tous les comptes d'epargne repris entre la dateDeb, et la dateFin'
 * @param DATE $dateDeb : date minimale pr les comptes d'epargne repris
 * @param DATE $date_rapport : date maximale pr les comptes d'epargne repris
 * @return ErrorObj Objet
 */
function getComptesEpargneRepris($dateDeb,$dateFin){
	global $global_id_agence;
  //Comptes d'epargne repris lors de la Reprise de comptes d'epargne existant (fonction 501) et lors de la reprise des comptes de base (fonction 500)
  $sql ="SELECT distinct cp.id_cpte,c.id_client,c.pp_nom,c.pp_prenom,c.pm_raison_sociale,c.gi_nom ,c.statut_juridique,cp.solde,cp.num_complet_cpte,cp.id_prod,p.libel,m.date_valeur as date_reprise,cast(m.montant as integer) as mnt_repris" ;
  $sql.=" FROM ad_mouvement m, ad_ecriture e, ad_his h,ad_cli c, ad_cpt cp,adsys_produit_epargne p  " ;
  $sql.=" WHERE m.id_ecriture = e.id_ecriture AND e.id_his= h.id_his  AND  cp.id_titulaire=c.id_client AND cp.id_prod=p.id AND (h.type_fonction = 500 or h.type_fonction = 501) AND cp.id_cpte = m.cpte_interne_cli AND service_financier=true " ;
  $sql.=" AND c.id_ag='$global_id_agence'";
  $sql.=" AND ( date(h.date) BETWEEN '$dateDeb' AND '$dateFin') ";
  $sql.=" ORDER BY p.libel, c.id_client";

 $result=executeDirectQuery($sql);

  return $result;

}
/**
 * Recherche ds l'historique tous les parts sociales reprises  entre la dateDeb, et la dateFin'
 * @param DATE $dateDeb : date minimale pr les comptes d'epargne repris
 * @param DATE $date_rapport : date maximale pr les comptes d'epargne repris
 * @return ErrorObj Objet
 */
function getPartSocialesReprises($dateDeb,$dateFin){
	global $global_id_agence;
	//Parts sociales reprises lors de la Reprise de comptes PS existants (fonction 502)
	$sql =" SELECT m.date_valeur as date_reprise,c.pp_nom,c.pp_prenom,c.pm_raison_sociale,gi_nom,c.anc_id_client , c.statut_juridique,c.id_client , cast(m.montant as integer) as mnt_repris";
  $sql.=" FROM ad_mouvement m, ad_ecriture e, ad_his h, ad_cli c " ;
  $sql.=" WHERE m.id_ecriture = e.id_ecriture and e.id_his= h.id_his and c.id_client=h.id_client  and type_fonction = 502 and m.cpte_interne_cli is not null  " ;
  $sql.=" AND ( date(date) BETWEEN '$dateDeb' AND '$dateFin') ";
  $sql.=" AND c.id_ag='$global_id_agence'";
  $sql.=" ORDER BY m.date_valeur,id_client asc";
  $result1=executeDirectQuery($sql);
  if ($result1->errCode!=NO_ERR){
  	return $result1;
  }

  //recuperation du numero du compte comptable  asoscié  aux parts sociales (operation 80)
  $sql = "SELECT cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE  id = 2 ORDER BY id";
  $result_cpte_ps=executeDirectQuery($sql,true);
  $cpte_ps=$result_cpte_ps->param[0];

  //Parts sociales reprises lors de la Reprise des données comptes de base (fonction 500)
  $sql =" SELECT m.date_valeur as date_reprise,c.pp_nom,c.pp_prenom,c.pm_raison_sociale,gi_nom,c.anc_id_client , c.statut_juridique,c.id_client , cast(m.montant as integer) as mnt_repris" ;
  $sql.=" FROM ad_mouvement m, ad_ecriture e, ad_his h,ad_cli c, ad_cpt cp ";
  $sql.=" WHERE m.id_ecriture = e.id_ecriture AND e.id_his= h.id_his  AND  cp.id_titulaire=c.id_client AND h.type_fonction = 500 AND cp.id_cpte = m.cpte_interne_cli and m.compte='$cpte_ps'";
  $sql.=" AND c.id_ag='$global_id_agence'";
  $sql.=" AND ( date(date) BETWEEN '$dateDeb' AND '$dateFin') ";
  $sql.=" ORDER BY m.date_valeur,id_client asc";
  $result=executeDirectQuery($sql);
  if ($result->errCode!=NO_ERR){
  	return $result;
  }
  //concatenation des resultats
  $result->param=array_merge($result->param,$result1->param);

   return $result;

}

function getTabResultatTrimestrielBCEAO($liste_reseau,$annee){
	global $global_id_agence;
	//1 er trimestre
	$tabTrim[1]["date_deb"]="01/01/".$annee;
	$tabTrim[1]["date_fin"]="31/03/".$annee;
  //2 eme trimestre
	$tabTrim[2]["date_deb"]="01/04/".$annee;
	$tabTrim[2]["date_fin"]="30/06/".$annee;
  //3 eme trimestre
	$tabTrim[3]["date_deb"]="01/07/".$annee;
	$tabTrim[3]["date_fin"]="30/09/".$annee;
  //4 eme trimestre
	$tabTrim[4]["date_deb"]="01/10/".$annee;
	$tabTrim[4]["date_fin"]="31/12/".$annee;

  $DATA=array();
  $liste_ag=$liste_reseau;
   if (isSiege()) {
   	resetGlobalIdAgence();
   	unset($liste_ag[$global_id_agence]);
    }


  foreach ($tabTrim as $key=>$valeur){
  	$DATA[$key]["exploitation"]=getCompteDeResultat($valeur["date_deb"],$valeur['date_fin'],$liste_reseau,1);
  	$DATA[$key]['usagers']['clients']['homme']=0;
  		$DATA[$key]['usagers']['clients']['femme']=0;
  		$DATA[$key]['usagers']['clients']['g_homme']=0;
  		$DATA[$key]['usagers']['clients']['g_femme']=0;
  		$DATA[$key]['usagers']['clients']['g_mixte']=0;
  		$DATA[$key]['usagers']['clients']['TOTAL']=0;

  		$DATA[$key]["credits_accordes"]['homme']['nbre']=0;
  		$DATA[$key]["credits_accordes"]['femme']['nbre']=0;
  		$DATA[$key]["credits_accordes"]['g_homme']['nbre']=0;
  		$DATA[$key]["credits_accordes"]['g_femme']['nbre']=0;
  		$DATA[$key]["credits_accordes"]['g_mixte']['nbre']=0;
  		$DATA[$key]["credits_accordes"]['TOTAL']['nbre']=0;

  		$DATA[$key]["credits_accordes"]['homme']['montant']=0;
  		$DATA[$key]["credits_accordes"]['femme']['montant']=0;
  		$DATA[$key]["credits_accordes"]['g_homme']['montant']=0;
  		$DATA[$key]["credits_accordes"]['g_femme']['montant']=0;
  		$DATA[$key]["credits_accordes"]['g_mixte']['montant']=0;
	    $DATA[$key]["credits_accordes"]['TOTAL']['montant']=0;

  		$DATA[$key]["epargnants"]['homme']['nbre']=0;
  		$DATA[$key]["epargnants"]['femme']['nbre']=0;
  		$DATA[$key]["epargnants"]['g_homme']['nbre']=0;
  		$DATA[$key]["epargnants"]['g_femme']['nbre']=0;
  		$DATA[$key]["epargnants"]['g_mixte']['nbre']=0;
  		$DATA[$key]["epargnants"]['TOTAL']['nbre']=0;

  		$DATA[$key]["encourepargants"]['homme']['montant']=0;
  		$DATA[$key]["encourepargants"]['femme']['montant']=0;
  		$DATA[$key]["encourepargants"]['g_homme']['montant']=0;
  		$DATA[$key]["encourepargants"]['g_femme']['montant']=0;
  		$DATA[$key]["encourepargants"]['g_mixte']['montant']=0;
  		$DATA[$key]["encourepargants"]['TOTAL']['montant']=0;

  	foreach ($liste_ag as $id_ag=>$agence){
  		setGlobalIdAgence($id_ag);
  		$DATAtmp["usagers"]=get_Clients_Actifs($valeur['date_fin'],false,true);
  		//Crédits accordés (cumul du 1er Janvier précédent à la date retenue)
  		$DATAtmp["credits_accordes"]=getCreditsDebourseByStatutJuridique($tabTrim[1]["date_deb"],$valeur['date_fin']);
  		//Epargnants (membres ayant au moins un compte d'épargne)
  		$DATAtmp["epargnants"]=getNbreEpargnant($valeur['date_fin']);
  		//Encours Epargne (compilation des fiches d'épargne)
  		$DATAtmp["encourepargants"]=getMontantEncoursEpargne($valeur['date_fin']);


  		$DATA[$key]['usagers']['clients']['homme']+=$DATAtmp['usagers']['clients']['homme'];
  		$DATA[$key]['usagers']['clients']['femme']+=$DATAtmp['usagers']['clients']['femme'];
  		$DATA[$key]['usagers']['clients']['g_homme']+=$DATAtmp['usagers']['clients']['g_homme'];
  		$DATA[$key]['usagers']['clients']['g_femme']+=$DATAtmp['usagers']['clients']['g_femme'];
  		$DATA[$key]['usagers']['clients']['g_mixte']+=$DATAtmp['usagers']['clients']['g_mixte'];
  		$DATA[$key]['usagers']['clients']['TOTAL']+=$DATAtmp['usagers']['clients']['homme']+$DATAtmp['usagers']['clients']['femme']+$DATAtmp['usagers']['clients']['g_homme']+$DATAtmp['usagers']['clients']['g_femme']+$DATAtmp['usagers']['clients']['g_mixte'];

  		$DATA[$key]["credits_accordes"]['homme']['nbre']+=$DATAtmp["credits_accordes"]['homme']['nbre'];
  		$DATA[$key]["credits_accordes"]['femme']['nbre']+=$DATAtmp["credits_accordes"]['femme']['nbre'];
  		$DATA[$key]["credits_accordes"]['g_homme']['nbre']+=$DATAtmp["credits_accordes"]['g_homme']['nbre'];
  		$DATA[$key]["credits_accordes"]['g_femme']['nbre']+=$DATAtmp["credits_accordes"]['g_femme']['nbre'];
  		$DATA[$key]["credits_accordes"]['g_mixte']['nbre']+=$DATAtmp["credits_accordes"]['g_mixte']['nbre'];
  		$DATA[$key]["credits_accordes"]['TOTAL']['nbre']+=$DATAtmp["credits_accordes"]['homme']['nbre']+$DATAtmp["credits_accordes"]['femme']['nbre']+$DATAtmp["credits_accordes"]['g_homme']['nbre']+$DATAtmp["credits_accordes"]['g_femme']['nbre']+$DATAtmp["credits_accordes"]['g_mixte']['nbre'];

  		$DATA[$key]["credits_accordes"]['homme']['montant']+=$DATAtmp["credits_accordes"]['homme']['montant'];
  		$DATA[$key]["credits_accordes"]['femme']['montant']+=$DATAtmp["credits_accordes"]['femme']['montant'];
  		$DATA[$key]["credits_accordes"]['g_homme']['montant']+=$DATAtmp["credits_accordes"]['g_homme']['montant'];
  		$DATA[$key]["credits_accordes"]['g_femme']['montant']+=$DATAtmp["credits_accordes"]['g_femme']['montant'];
  		$DATA[$key]["credits_accordes"]['g_mixte']['montant']+=$DATAtmp["credits_accordes"]['g_mixte']['montant'];
	    $DATA[$key]["credits_accordes"]['TOTAL']['montant']+=$DATAtmp["credits_accordes"]['homme']['montant']+$DATAtmp["credits_accordes"]['femme']['montant']+$DATAtmp["credits_accordes"]['g_homme']['montant']+$DATAtmp["credits_accordes"]['g_femme']['montant']+$DATAtmp["encourepargants"]['g_mixte']['montant'];

  		$DATA[$key]["epargnants"]['homme']['nbre']+=$DATAtmp["epargnants"]['homme']['nbre'];
  		$DATA[$key]["epargnants"]['femme']['nbre']+=$DATAtmp["epargnants"]['femme']['nbre'];
  		$DATA[$key]["epargnants"]['g_homme']['nbre']+=$DATAtmp["epargnants"]['g_homme']['nbre'];
  		$DATA[$key]["epargnants"]['g_femme']['nbre']+=$DATAtmp["epargnants"]['g_femme']['nbre'];
  		$DATA[$key]["epargnants"]['g_mixte']['nbre']+=$DATAtmp["epargnants"]['g_mixte']['nbre'];
  		$DATA[$key]["epargnants"]['TOTAL']['nbre']+=$DATAtmp["epargnants"]['homme']['nbre']+$DATAtmp["epargnants"]['femme']['nbre']+$DATAtmp["epargnants"]['g_homme']['nbre']+$DATAtmp["epargnants"]['g_femme']['nbre']+$DATAtmp["epargnants"]['g_mixte']['nbre'];

  		$DATA[$key]["encourepargants"]['homme']['montant']+=$DATAtmp["encourepargants"]['homme']['montant'];
  		$DATA[$key]["encourepargants"]['femme']['montant']+=$DATAtmp["encourepargants"]['femme']['montant'];
  		$DATA[$key]["encourepargants"]['g_homme']['montant']+=$DATAtmp["encourepargants"]['g_homme']['montant'];
  		$DATA[$key]["encourepargants"]['g_femme']['montant']+=$DATAtmp["encourepargants"]['g_femme']['montant'];
  		$DATA[$key]["encourepargants"]['g_mixte']['montant']+=$DATAtmp["encourepargants"]['g_mixte']['montant'];
  		$DATA[$key]["encourepargants"]['TOTAL']['montant']+=$DATAtmp["encourepargants"]['homme']['montant']+$DATAtmp["encourepargants"]['femme']['montant']+$DATAtmp["encourepargants"]['g_homme']['montant']+$DATAtmp["encourepargants"]['g_femme']['montant']+$DATAtmp["encourepargants"]['g_mixte']['montant'];

  	}
  	resetGlobalIdAgence();

  }
 return  $DATA;
}
/** Fonction: permet d'associer le libellé du poste et son solde ,le resultat sera mis dans  le tableau $tab,
 * si $recursif est à true,la fonction mettra dans tab, les postes et sous postes en calculant de manière recursif les soldes
 * @param array $tab tableau tableau où seront mis les données du poste
 * @param array $fields_values tableau contenant les condition de filtre des données
 * @param Integer $type_rapport type de rapport ou etat
 * @param boolean $recursif si true calcul de manière recursif les soldes des postes.
 * @param array	$tabSolde : tableau contenant les solde chaque poste
 * @param boolean $is_groupe si true les postes seront regroupés par compartiment
 */

function getPoste(&$tab, $fields_values=NULL,$type_rapport, $recursif=false,$tabSolde,$is_groupe=false) {
  global $dbHandler,$global_id_agence;

	//vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_poste  WHERE type_etat=".$type_rapport." AND ";
  if (isset($fields_values)) {

    foreach ($fields_values as $key => $value) {
    	if (($value == '') or ($value == NULL)) {
    		 $sql .= " $key AND ";
    	} else {
    		$sql .= " $key = '$value' AND ";
    	}
    }
  }
  $sql = substr($sql, 0, -4);
  $sql .= " ORDER BY id_poste,compartiment,niveau,id_poste_centralise ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
  	//calcul solde du poste
  	$solde=(1)*$tabSolde[$row['id_poste']]['solde'];
  	$solde_prov=(1)*$tabSolde[$row['id_poste']]['amortissement'];
  	$net=(1)*$tabSolde[$row['id_poste']]['net'];
  	//si on veut regrouper les postes
  	if($is_groupe){
  		if(!isset($tab[$row['compartiment']])){
  			$tab[$row['compartiment']]=array();
  		}
			$tab[$row['compartiment']][$row['id_poste']]=$row;
			$tab[$row['compartiment']][$row['id_poste']]['solde']=$solde;
			$tab[$row['compartiment']][$row['id_poste']]['amortissement']=$solde_prov;
			$tab[$row['compartiment']][$row['id_poste']]['net']=$net;
      if($recursif){
      	$temp=getSousPoste($row['id_poste']);
    	  foreach ( $temp as $key1=>$value1) {
    	  	$where['id_poste']=$key1;
	  		  getPoste($tab,$where,$type_rapport,$recursif,$tabSolde,$is_groupe);
	  		  $tab[$row['compartiment']][$row['id_poste']]['solde']+=$tab[$row['compartiment']][$key1]['solde'];
	  		  $tab[$row['compartiment']][$row['id_poste']]['amortissement']+=$tab[$row['compartiment']][$key1]['amortissement'];
	  		  $tab[$row['compartiment']][$row['id_poste']]['net']+=$tab[$row['compartiment']][$key1]['net'];
    	  }
      }
  	}else{
  		$tab[$row['id_poste']]=$row;
  		$tab[$row['id_poste']]['solde']=$solde;
  		$tab[$row['id_poste']]['amortissement']=$solde_prov;
			$tab[$row['id_poste']]['net']=$net;
  		if($recursif){
  			$temp=getSousPoste($row['id_poste']);
    	  foreach ( $temp as $key1=>$value1) {
    	  	$where['id_poste']=$key1;
	  		  getPoste($tab,$where,$type_rapport,$recursif,$tabSolde,$is_groupe);
	  		  $tab[$row['id_poste']]['solde']+=$tab[$key1]['solde'];
	  		  $tab[$row['id_poste']]['amortissement']+=$tab[$key1]['amortissement'];
	  		  $tab[$row['id_poste']]['net']+=$tab[$key1]['net'];
    	  }
  		}
  	}
  }//fin boucle while
  $dbHandler->closeConnection(true);


}

/**
 * Fonction utilisée pour les rapport de la BNR
 * Fonction: renvoie les sous poste d'un poste
 * @param integer $id_poste identifiant du poste
 * @return array	tableau des sous poste
 *                $liste_sous_rubriques['id_poste']=valeur
 *
 *
 */

function getSousPoste($id_poste) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $liste_sous_rubriques=array();

  $sql ="SELECT * FROM ad_poste WHERE id_poste_centralise =".$id_poste;
  $sql .= " ORDER BY id_poste,compartiment,niveau,id_poste_centralise ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    // ajoute le rubrique dans la liste
    $liste_sous_rubriques[$row['id_poste']] = $row;

  }

  $dbHandler->closeConnection(true);
  return $liste_sous_rubriques;
}
/**Fonction utilisée pr le bilan et compte de resultat de la BNR
 * Fonction permettant de recupérer les soldes des Postes et leurs soldes pour les etat de comptabilité paramètrés.
 * @param DATE $date_deb	date début de periode
 * @param DATE $date_fin	date fin de periode
 * @param Integer $type_etat	type etat (rapport)
 * @param array  $liste_ag 	liste des agences
 * @param boolean	$consolide	true si on veut editer les etats consolidés (multi-agences)
 * @return array $tab_donnee:les postes et les solde correspondants
 *
 */
function getPoste_solde($date_deb,$date_fin,$type_etat,$liste_ag,$consolide){
  global $dbHandler, $global_id_agence;
	$tab_donnee=array();
	if($type_etat==1){
		$tabSolde= getBilan_BNR($date_deb,$liste_ag,$consolide,$type_etat);
		//mettre le resultat des exo
		$tab_donnee['resultats_exo']=$tabSolde['resultats_exo'];
	} elseif($type_etat==2){
		$tabSolde= getCompte_resultat_BNR($date_deb,$date_fin,$type_etat, $liste_ag);
	}
  $db = $dbHandler->openConnection();

  $sql ="SELECT * FROM ad_poste WHERE type_etat=".$type_etat." AND id_poste_centralise IS NULL ";
  $sql .= " ORDER BY id_poste,compartiment,niveau,id_poste_centralise ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    // ajoute le rubrique dans la liste
    $where['id_poste']=$row['id_poste'];
    getPoste($tab_donnee, $where,$type_etat,true,$tabSolde,true);

  }

  $dbHandler->closeConnection(true);

	return $tab_donnee;
}
/**Fonction utilisée pr le bilan et compte de resultat de la BNR
 * Fonction permettant de recupérer les soldes des Postes et leurs soldes pour les etat de comptabilité paramètrés.
 * @param DATE $date_ratio	date de l'edition ration de liquidite
 * @param Integer $type_etat	type etat (rapport)
 * @param array  $liste_ag 	liste des agences
 * @param boolean	$consolide	true si on veut editer les etats consolidés (multi-agences)
 * @return array $tab_donnee:les postes et les soldes correspondants
 *
 */
function getPoste_solde_ratio_liquidite($date_ratio,$type_etat,$liste_ag,$consolide){

	global  $global_id_agence;
	$tab_donnee=array();
	$tabSolde=getRatio_liquidite_BNR($date_ratio,$type_etat,$liste_ag,$consolide);
  getPoste($tab_donnee, NULL,$type_etat,false,$tabSolde,true);

	return $tab_donnee;
}

/**
 * Fonction renvoyant les comptes d'épargnes cloturés à une periode donnée.
 * @author Arès Voukissi
 * @since 3.2
 * @param date $date_debut début de l'intervalle de recherche
 * @param date $date_fin fin de l'intervalle de recherche
 * @param array $where tableau des conditions de selection
 * @return array $cptes liste des comptes cloturés pendant la periode
 */
function getCpteEpargneCloture($list_agence, $date_debut, $date_fin, $where) {
	global $dbHandler, $global_id_agence;
   global $global_monnaie;
  $db = $dbHandler->openConnection();

	$array_devise = array();
	foreach($list_agence as $key_id_ag =>$value) {
    setGlobalIdAgence($key_id_ag);
    $devises = get_table_devises();
    array_merge($array_devise,$devises);
	  $sql = "SELECT a.id_ag, a.num_complet_cpte,b.devise,c.id_client,c.statut_juridique,pp_nom,pp_prenom,pm_raison_sociale,gi_nom,a.id_cpte,solde,solde_clot,CalculeCV(solde_clot,b.devise,'$global_monnaie') as solde_clot_cv,date_clot ,raison_clot,id_prod,libel,classe_comptable";
	  $sql .= "    FROM ad_cpt a, adsys_produit_epargne b , ad_cli c";
		$sql .= "	WHERE a.id_ag = b.id_ag and b.id_ag = c.id_ag and c.id_ag = $global_id_agence AND a.id_prod=b.id  AND a.id_titulaire=c.id_client 	" ;
		$sql .= " AND service_financier=true   AND a.etat_cpte=2  ";
		$sql .=" AND date_clot BETWEEN date('$date_debut') AND date('$date_fin') ";
		foreach( $where as $key=>$valeur ){
			$sql.=" AND $key='$valeur' ";
		}
		$sql .= " order by a.id_prod, date_clot,classe_comptable,b.id,c.id_client ";
	  $result = $db->query($sql);
	  if (DB::isError($result)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__,__LINE__,__FUNCTION__);
	  }

	  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
	  	$cptes[$row['id_cpte'].$row['id_ag']] = $row;
	  }
	}


  $dbHandler->closeConnection(true);
  return $cptes;
}

/**
   * Renvoie les détails sur la transaction concernant une fonction depuis le début de l'exercice jusqu'à la date précisée
   * @author Djibril NIANG
   * @since 3.1
   * @param INT $fonction numéro de la fonction associée à la transaction
   * @param DATE $date date d'édition du rapport
   * @return ARRAY $infos tableau contenant les infos sur la transaction concernant cette fonction
**/
function getInfosTransactionFonction($fonction, $date, $list_agence) {
  global $dbHandler,$global_id_agence, $global_id_exo;

  $db = $dbHandler->openConnection();
  //on construit la requête
  foreach($list_agence as $key_id_ag =>$value) {
    //Parcours des agences
    setGlobalIdAgence($key_id_ag);
	  $sql = "SELECT h.id_his, h.id_client, h.infos, m.cpte_interne_cli, m.montant, m.devise, m.date_valeur";
	  $sql .= " FROM ad_his h, ad_ecriture e, ad_mouvement m ";
	  $sql .= " WHERE e.id_his = h.id_his AND e.id_ecriture = m.id_ecriture AND m.sens = 'c' AND h.type_fonction = $fonction";
	  $sql .= " AND m.date_valeur <= date('$date')";
	  $sql .= " AND e.id_exo = $global_id_exo";
	  $sql .= " AND h.id_ag = e.id_ag AND e.id_ag = m.id_ag AND m.id_ag = $global_id_agence";
	  $sql .= " ORDER BY m.devise, h.id_his DESC";
	  $result = $db->query($sql);
	  if (DB::isError($result)) {
	    $dbHandler->closeConnection(false);
	    signalErreur(__FILE__,__LINE__,__FUNCTION__);
	  }
	  $details = array();
	  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
	    if($row['cpte_interne_cli']){
	    	$details[$row['devise']][$row['cpte_interne_cli']] = $row;
	    	//on recupère le libellé du produit d'épargne associé au numéro du compte d'épargne du client
		    $infosProdEpargne = getAccountDatas($row['cpte_interne_cli']);
		    $details[$row['devise']][$row['cpte_interne_cli']]['libel_prod_ep'] = $infosProdEpargne['libel'];
		    $details[$row['devise']][$row['cpte_interne_cli']]['etat_cpte'] = $infosProdEpargne['etat_cpte'];
		    //on recupère le nom du client
		    $details[$row['devise']][$row['cpte_interne_cli']]['nom_client'] = getNomClient($row['id_client']);
	    }

	  }
  }

  $dbHandler->closeConnection(true);
  return $details;

}

/**
 * Renvoie les données pour la concentration de l'épargne : une alternative à get_data_repartition_epargne voir #1758.
 * @author Djibril NIANG
 * @since 3.0.6
 * @param INT $palier1 solde inférieure
 * @param INT $palier2 solde supérieure
 * @return ARRAY $DATA : tableau contenant les données recherchées.
**/
function getConcentrationEpargne($palier1 = NULL, $palier2 = NULL) {
  global $dbHandler,$global_id_agence, $global_monnaie;
  $db = $dbHandler->openConnection();

  // Récupère tous les produits d'épargne
  $prod_ep = get_produits_epargne();
  foreach($prod_ep as $key=>$value){
    $id_prod = $value['id'];
    $libel_prod = $value['libel'];
    $DATA[$id_prod]['homme']['nbre'] = 0;
    $DATA[$id_prod]['homme']['nbre_prc'] = 0;
    $DATA[$id_prod]['homme']['solde'] = 0;
    $DATA[$id_prod]['homme']['solde_prc'] = 0;
    $DATA[$id_prod]['femme']['nbre'] = 0;
    $DATA[$id_prod]['femme']['nbre_prc'] = 0;
    $DATA[$id_prod]['femme']['solde'] = 0;
	    $DATA[$id_prod]['femme']['solde_prc'] = 0;
	    $DATA[$id_prod]['pm']['nbre'] = 0;
	    $DATA[$id_prod]['pm']['nbre_prc'] = 0;
	    $DATA[$id_prod]['pm']['solde'] = 0;
	    $DATA[$id_prod]['pm']['solde_prc'] = 0;
    $DATA[$id_prod]['gi']['nbre'] = 0;
	    $DATA[$id_prod]['gi']['nbre_prc'] = 0;
    $DATA[$id_prod]['gi']['solde'] = 0;
    $DATA[$id_prod]['gi']['solde_prc'] = 0;
	    $DATA[$id_prod]['gs']['nbre'] = 0;
	    $DATA[$id_prod]['gs']['nbre_prc'] = 0;
    $DATA[$id_prod]['gs']['solde'] = 0;
    $DATA[$id_prod]['gs']['solde_prc'] = 0;
    $DATA[$id_prod]['libel_prod'] = $libel_prod;
    $DATA[$id_prod]['Total']['total_cpte'] = 0;
    $DATA[$id_prod]['Total']['total_solde'] = 0;
    $DATA[$id_prod]['Total']['total_cpte_prc'] = 0;
    $DATA[$id_prod]['Total']['total_solde_prc'] = 0;
	    $DATA[$id_prod]['femme']['statut_juridique'] = "Femmes";
    $DATA[$id_prod]['homme']['statut_juridique'] = "Hommes";
    $DATA[$id_prod]['pm']['statut_juridique'] = "Personnes morales";
    $DATA[$id_prod]['gi']['statut_juridique'] = "Groupes Informels";
    $DATA[$id_prod]['gs']['statut_juridique'] = "Groupes Solidaires";
    $DATA[$id_prod]['libel_prod'] = $value['libel'];

    // Récupère tous les comptes d'épargne
    $sql = " SELECT cli.statut_juridique, cli.pp_sexe, SUM(c.solde) as somme_solde, COUNT(c.id_cpte) as nbre_cpte, c.devise";
    $sql .= " FROM ad_cpt c, ad_cli cli";
    $sql .= " WHERE cli.id_client = c.id_titulaire AND id_prod = $id_prod AND c.etat_cpte = 1 ";
    $sql .= " AND cli.id_ag = c.id_ag AND c.id_ag = $global_id_agence ";
    if($palier1 != NULL && $palier2 != NULL){
      $mnt_inf = recupMontant($palier1);
      $mnt_sup = recupMontant($palier2);
     $sql .= " AND c.solde >= $mnt_inf AND c.solde <= $mnt_sup ";
    }
    $sql .= " GROUP BY cli.statut_juridique, cli.pp_sexe, c.devise ";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      if ($row['statut_juridique'] == 2) {
	        $DATA[$id_prod]['pm']['nbre'] += $row['nbre_cpte'];
	        $DATA[$id_prod]['pm']['solde'] += $row['somme_solde'];
	      }
	      elseif ($row['statut_juridique'] == 3) {
	        $DATA[$id_prod]['gi']['nbre'] += $row['nbre_cpte'];
	        $DATA[$id_prod]['gi']['solde'] += $row['somme_solde'];
	      }
	      elseif ($row['statut_juridique'] == 4) {
	        $DATA[$id_prod]['gs']['nbre'] += $row['nbre_cpte'];
	        $DATA[$id_prod]['gs']['solde'] += $row['somme_solde'];
	      } if ($row['statut_juridique'] == 1) {
	        if ($row['pp_sexe'] == 1) {
	          $DATA[$id_prod]['homme']['nbre'] += $row['nbre_cpte'];
	          $DATA[$id_prod]['homme']['solde'] += $row['somme_solde'];
	        }
	        elseif ($row['pp_sexe'] == 2) {
	          $DATA[$id_prod]['femme']['nbre'] += $row['nbre_cpte'];
	          $DATA[$id_prod]['femme']['solde'] += $row['somme_solde'];
	        }
	      }
	      if($row['devise'] == NULL){
	        $DATA[$id_prod]['devise'] = $global_monnaie;
 } else {
 	        $DATA[$id_prod]['devise'] = $row['devise'];
 	}
}
	    $DATA[$id_prod]['Total']['libel'] = "Sous Total";
	    $DATA[$id_prod]['Total']['total_cpte'] += $DATA[$id_prod]['pm']['nbre'] + $DATA[$id_prod]['gi']['nbre'] + $DATA[$id_prod]['gs']['nbre'] + $DATA[$id_prod]['femme']['nbre'] + $DATA[$id_prod]['homme']['nbre'];
	    $DATA[$id_prod]['Total']['total_solde' ] += $DATA[$id_prod]['pm']['solde'] + $DATA[$id_prod]['gi']['solde'] + $DATA[$id_prod]['gs']['solde'] + $DATA[$id_prod]['homme']['solde'] + $DATA[$id_prod]['femme']['solde'];
	    //pourcentage des nbres
	    $DATA[$id_prod]['homme']['nbre_prc'] = $DATA[$id_prod]['homme']['nbre'] / max($DATA[$id_prod]['Total']['total_cpte'], 1);
	    $DATA[$id_prod]['femme']['nbre_prc'] = $DATA[$id_prod]['femme']['nbre'] / max($DATA[$id_prod]['Total']['total_cpte'], 1);
	    $DATA[$id_prod]['pm']['nbre_prc'] = $DATA[$id_prod]['pm']['nbre'] / max($DATA[$id_prod]['Total']['total_cpte'], 1);
	    $DATA[$id_prod]['gi']['nbre_prc'] = $DATA[$id_prod]['gi']['nbre'] / max($DATA[$id_prod]['Total']['total_cpte'], 1);
	    $DATA[$id_prod]['gs']['nbre_prc'] = $DATA[$id_prod]['gs']['nbre'] / max($DATA[$id_prod]['Total']['total_cpte'], 1);
	    //pourcentage sur les soldes
    $DATA[$id_prod]['homme']['solde_prc'] = $DATA[$id_prod]['homme']['solde'] / max($DATA[$id_prod]['Total']['total_solde'], 1);
	    $DATA[$id_prod]['femme']['solde_prc'] = $DATA[$id_prod]['femme']['solde'] / max($DATA[$id_prod]['Total']['total_solde'], 1);
	    $DATA[$id_prod]['pm']['solde_prc'] = $DATA[$id_prod]['pm']['solde'] / max($DATA[$id_prod]['Total']['total_solde'], 1);
	    $DATA[$id_prod]['gi']['solde_prc'] = $DATA[$id_prod]['gi']['solde'] / max($DATA[$id_prod]['Total']['total_solde'], 1);
	    $DATA[$id_prod]['gs']['solde_prc'] = $DATA[$id_prod]['gs']['solde'] / max($DATA[$id_prod]['Total']['total_solde'], 1);

	    $DATA[$id_prod]['Total']['total_cpte_prc'] += $DATA[$id_prod]['gs']['nbre_prc'] + $DATA[$id_prod]['pm']['nbre_prc'] + $DATA[$id_prod]['gi']['nbre_prc'] +$DATA[$id_prod]['femme']['nbre_prc'] +$DATA[$id_prod]['homme']['nbre_prc'];
	    $DATA[$id_prod]['Total']['total_solde_prc' ] += $DATA[$id_prod]['pm']['solde_prc'] + $DATA[$id_prod]['gi']['solde_prc'] + $DATA[$id_prod]['gs']['solde_prc'] + $DATA[$id_prod]['homme']['solde_prc'] + $DATA[$id_prod]['femme']['solde_prc'];

 		  }

 		  $dbHandler->closeConnection(true);
 		  return $DATA;
}


function getSoldeCpteComptable($num_cpte_comptable, $date_modif = NULL) {
  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;

  $db = $dbHandler->openConnection();

  if($date_modif == NULL){
  	$sql = " SELECT solde FROM ad_cpt_comptable WHERE num_cpte_comptable = '$num_cpte_comptable' AND id_ag = $global_id_agence ";
  } else {
  	$date_mod = php2pg($date_modif);
  	$sql = " SELECT solde FROM ad_cpt_comptable WHERE num_cpte_comptable = '$num_cpte_comptable' AND id_ag = $global_id_agence AND ((is_actif = 't') OR (is_actif = 'f' AND date_modif > '$date_mod'))";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
  }
  $row = $result->fetchrow();
  $solde = $row[0];

  $dbHandler->closeConnection(true);
  return $solde;

}


/**
 * Renvoie les dossiers des crédit provisionnés
 * @author Arès
 * @since 3.1
 * @param INT $etat_credit Id de l'etat du crédit
 * @param date $date_provision date à laquelle on a calculé la provision
 *  * @param BOOL $is_solde_not_null vrai on affiche que les crédit dont le solde de provision est non null
 * @return ARRAY $DATA : tableau contenant les données recherchées.
**/
function getDossierProvisionne($date_debut_provision=NULL,$date_fin_provision=NULL,$etat_credit=NULL,$is_solde_not_null=NULL) {
  global $dbHandler;
  global $global_multidevise, $global_monnaie, $global_id_agence;

  $db = $dbHandler->openConnection();
  
  // Get current date
  $export_date = php2pg(date('d/m/Y'));

  if(!is_null($date_fin_provision) && $date_fin_provision != '') {
    $export_date = php2pg($date_fin_provision);
  }

  $etat_credit = is_null($etat_credit)?'null':$etat_credit;

  $sql = "SELECT d.id_doss, d.id_client, d.gs_cat,
          (case WHEN date('".$export_date."') = date(now()) THEN d.cre_etat ELSE CalculEtatCredit(d.id_doss, '".$export_date."', $global_id_agence) END ) AS cre_etat,
          (case WHEN date('".$export_date."') = date(now())
            THEN d.prov_mnt
          ELSE
            (SELECT COALESCE(montant,0)
              FROM ad_provision
              WHERE id_doss = d.id_doss AND id_ag = $global_id_agence
              AND date_prov = (SELECT MAX(date_prov) FROM ad_provision WHERE date_prov <= '".$export_date."' AND id_doss = d.id_doss AND id_ag = $global_id_agence))
          END) AS prov_mnt,
          (case WHEN date('".$export_date."') = date(now())
            THEN d.prov_date
          ELSE
            (SELECT MAX(date_prov) FROM ad_provision WHERE date_prov <= '".$export_date."' AND id_doss = d.id_doss AND id_ag = $global_id_agence)
          END) AS prov_date,
          d.id_dcr_grp_sol, d.cre_date_etat, d.devise, d.libel, d.id_prod
          FROM get_ad_dcr_ext_credit(null, null, null, $etat_credit, $global_id_agence) d
          WHERE d.cre_date_debloc <= '".$export_date."'
          AND ((d.etat IN (5,7,8,13,14,15)) OR (d.etat IN (6,9,11,12) AND d.date_etat > '".$export_date."'))
          GROUP BY d.id_doss, d.id_client, d.prov_mnt, d.prov_date, d.gs_cat,
          (case WHEN date('".$export_date."') = date(now()) THEN d.cre_etat ELSE CalculEtatCredit(d.id_doss, '".$export_date."', $global_id_agence) END ),
          d.id_dcr_grp_sol, d.cre_date_etat, d.devise, d.libel, d.id_prod;";


  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
  }

  $RESULTAT = array ();
  $tabGS = array();//pour stoquer l'identifiant du groupe dont le credit solidaire est à dossiers multiples
  $tab_premier_membre_GS_multi = array();//pour stoquer chaque 1er membre du crédit solidaire à dossiers multiples
  $nbre_occurence_tab = 0;

  //on recupère tous les états des crédits : pas besoin de faire l'appel dans la boucle while.
  $ET = getTousEtatCredit();

  if(!is_null($etat_credit)) {
    $EtatProv[] = $etat_credit;
  }
  else {
    //on recupère tous les états des crédits avec provision
    $EtatProv = array_keys(getEtatCreditprovision());
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      
    if(in_array($row["cre_etat"], $EtatProv)) {

        $INFO_DCR["id_doss"] = $row['id_doss'];
        $INFO_DCR["id_client"] = $row["id_client"];
        $INFO_DCR["nom"] = getClientName($row["id_client"]);

        $INFO_DCR["capital_restant"]= getSoldeCapital($row["id_doss"], $export_date);
        $INFO_DCR["gar_num"]= getSoldeGarNumeraires($row["id_doss"]);
        $INFO_DCR["prov_mnt"] = $row["prov_mnt"];
        $INFO_DCR["prov_date"] = $row["prov_date"];
        $INFO_DCR["devise"] = $row["devise"];
        $INFO_DCR["libel"] = $row["libel"];
        $INFO_DCR["id_prod"] = $row["id_prod"];
        $INFO_DCR["is_ligne_credit"] = $row["is_ligne_credit"];

        $INFO_DCR["cre_date_etat"] = $export_date; // $row["cre_date_etat"];

        $INFO_DCR["cre_etat"] = $ET[$row["cre_etat"]]["libel"];
        $INFO_DCR["gs_cat"] = $row["gs_cat"];
        $INFO_DCR["id_dcr_grp_sol"] = $row["id_dcr_grp_sol"];

        //recuperation du crédit solidaire à dossiers multiples
        $groupe = getCreditSolDetailRap($INFO_DCR);

        if ((is_array($groupe["credit_gs"])) && (!in_array($groupe["credit_gs"]["id_client"],$tabGS))){
            $tabGS[]  = $groupe["credit_gs"]["id_client"];
            $groupe["credit_gs"]["libel"] = $row['libel'];
            //TODO: le montant octroyé est la somme des montants octroyés aux membres du groupe, bien qu'il soit souvent egale au montant demandé
            //$groupe["credit_gs"]["cre_mnt_octr"] = $groupe["credit_gs"]["mnt_dem"];
            $groupe["credit_gs"]["nom"] = getClientName($groupe["credit_gs"]["id_client"]);
            $groupe["credit_gs"]["premier_membre"] = $INFO_DCR["id_client"];
            $credit_solidaire = $groupe["credit_gs"];
            array_push($RESULTAT, $groupe["credit_gs"]);
        }
        if (($groupe["credit_gs"]["id_dcr_grp_sol"] > 0) && ($INFO_DCR["id_dcr_grp_sol"] == $groupe["credit_gs"]["id_dcr_grp_sol"])) {
                    $tab_premier_membre_GS_multi[] = $INFO_DCR["id_client"];
                    $nbre_occurence_tab = array_count_values($tab_premier_membre_GS_multi);
                    if (($nbre_occurence_tab[$INFO_DCR["id_client"]] > 1) && ($credit_solidaire["premier_membre"] == $INFO_DCR["id_client"]))
                            array_push($RESULTAT, $credit_solidaire);
                    $INFO_DCR["membre"] = 1;
        }
        else $INFO_DCR["membre"] = 0;
        array_push($RESULTAT, $INFO_DCR);
        //récuperation des crédits des membres d'un groupe solidaire  à dossier unique
        if (is_array($groupe[$INFO_DCR["id_client"]])) {
            $i = 0;
            while($i < count($groupe[$INFO_DCR["id_client"]])) {
                    $groupe[$INFO_DCR["id_client"]][$i]["libel"] = $row[7];
                    //le montant octroyé à chacun des membres dépendra du montant octroyé au groupe par rapport au montant global demandé
                    //$groupe[$INFO_DCR["id_client"]][$i]["cre_mnt_octr"] = ($groupe[$INFO_DCR["id_client"]][$i]["mnt_dem"] * $INFO_DCR["cre_mnt_octr"])/$INFO_DCR["mnt_dem"];
                    $groupe[$INFO_DCR["id_client"]][$i]["id_doss"] = 0;
                    $groupe[$INFO_DCR["id_client"]][$i]["nom"] = getClientName($groupe[$INFO_DCR["id_client"]][$i]["id_client"]);
                    array_push($RESULTAT,$groupe[$INFO_DCR["id_client"]][$i]);
                    $i++;
            }
        }
    }
  }

  $dbHandler->closeConnection(true);
  return $RESULTAT;

}


/**
 *
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport recouvrement
 *
 * @param number $gestionnaire $gestionnaire Identifiant du gestionnaire, 0 si tous
 * @param unknown $export_date date d'édition du rapport
 * @param Integer $etat Class du credit
 * @param Integer $type_affich Type affichage, 1 si affichage détaillé 2 si  affichage synthétisé
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */
function get_rapport_recouvrement_credit_data($gestionnaire = 0, $export_date, $etat = 0, $prd, $export_date_debut,$n_agence=null,$n_guichet=null)
{
    global $dbHandler;
    global $global_multidevise;
    global $global_monnaie,$global_id_agence;

    $db = $dbHandler->openConnection();

    if(empty($export_date)) {
        $export_date = date("Y")."-".date("m")."-".date("d");
    }
    elseif(strpos($export_date, '/')){
        $export_date = php2pg($export_date);
    }

    //Ticket 720 : date debut format
    if(strpos($export_date_debut, '/')){
      $export_date_debut = php2pg($export_date_debut);
    }

    //REL-30 : recupere etat perte - à utiliser pour les dossiers à état radié ou un dossier qui s'est radié soldé pendant la periode
    $etat_perte = getIDEtatPerte();
    $etat_perte = (int)$etat_perte;
    /*$sql = "SELECT a.* FROM (
                SELECT d.id_doss, d.id_client, d.id_prod, d.id_ag, d.date_dem, d.cre_mnt_deb, d.etat AS etat_dossier,
                d.id_agent_gest AS id_gestionnaire, cre_nbre_reech, p.devise,
                (case WHEN date('$export_date') = date(now()) THEN d.cre_etat WHEN d.etat = 6 AND d.cre_etat = $etat_perte AND date(d.cre_date_etat) >= date('$export_date_debut') THEN CalculEtatCredit(d.id_doss, date(d.date_etat)-1, $global_id_agence) ELSE CalculEtatCredit(d.id_doss, date('$export_date'), $global_id_agence) END )
                AS etat_credit, date(d.cre_date_etat) AS cre_date_etat, d.date_etat,  d.is_ligne_credit
                FROM ad_dcr d, adsys_produit_credit p
                WHERE d.id_prod = p.id AND date(d.cre_date_debloc) <= date('$export_date')
                AND (d.etat IN (5,6,7,8,9,11,12,13,14,15) OR (d.etat IN (6) AND date(d.date_etat) >= date('$export_date_debut')))
                AND d.id_ag = p.id_ag 
                AND p.id_ag=$global_id_agence                
            ) a             
        WHERE 1=1 ";*/
    //REL-30 : New SQL from existing one (Ramener les dossiers eligible en basant sur les criteres de recherche)
  $sql="SELECT a.* FROM (
  SELECT d.id_doss, d.id_client, d.id_prod, d.id_ag, d.date_dem, d.cre_mnt_deb, (case WHEN date('$export_date') = date(now()) THEN d.etat ELSE calculetatdossier_hist(numagc(),d.id_doss,date('$export_date')) END) AS etat_dossier,
        d.id_agent_gest AS id_gestionnaire, cre_nbre_reech, p.devise,
        (case WHEN date('$export_date') = date(now()) THEN d.cre_etat WHEN d.etat = 6 AND d.cre_etat = $etat_perte AND date(d.cre_date_etat) >= date('$export_date_debut') THEN CalculEtatCredit(d.id_doss, date(d.date_etat)-1, $global_id_agence) ELSE CalculEtatCredit(d.id_doss, date('$export_date'), $global_id_agence) END ) AS etat_credit, date(d.cre_date_etat) AS cre_date_etat, d.date_etat,  d.is_ligne_credit
  FROM ad_dcr d, adsys_produit_credit p, ad_cli x
  WHERE d.id_prod = p.id AND date(d.cre_date_debloc) <= date('$export_date')
  AND d.id_ag = p.id_ag
  AND x.id_client = d.id_client";
    if ($n_agence!=null){
        $sql .= " AND x.n_agence = $n_agence ";
    }
    if ($n_guichet!=null){
        $sql .= " AND x.n_guichet = $n_guichet ";
    }
  $sql .=" AND p.id_ag=$global_id_agence  
                        ) a
  WHERE 1=1 AND
  (
  (a.etat_dossier IN (5,7,8,11,13,14,15))
  OR (a.etat_dossier = 6 AND date(a.date_etat) >= date('$export_date_debut') AND date(a.date_etat) <= date('$export_date'))
  OR (a.etat_dossier IN (6,9) AND a.etat_credit = $etat_perte AND a.id_doss IN (SELECT DISTINCT sre.id_doss FROM ad_sre sre WHERE date(sre.date_remb) <= date('$export_date')))
  OR (a.etat_dossier = 9 AND a.etat_credit = $etat_perte AND date(a.date_etat) <= date('$export_date') AND date(a.cre_date_etat) <= date('$export_date'))
  )"; // OR AND a.etat_credit != $etat_perte  OR AND date(a.date_etat) <= date('$export_date') // date(a.cre_date_etat) <= date('$export_date_debut') AND
  //date(sre.date_remb) >= date('$export_date_debut') AND  // AND date(a.date_etat) >= date('$export_date_debut') AND date(a.date_etat) <= date('$export_date')

    
    if (!empty($gestionnaire)) {
        $sql.= " AND a.id_gestionnaire = $gestionnaire ";
    }
    if (!empty($etat)) {
      if ($etat=='CA'){ //trac#720 : Commentaire no.5
        //REL-30 filtrage par etat credit et non etat dossier - on exclut les dossiers qui ont passé à l'état radié
        $sql.= " AND a.etat_credit NOT IN ($etat_perte) ";
      }
      elseif ($etat=='SOLDE'){ //REL-30 : gestion etat soldé - 6
        $sql.= " AND a.etat_dossier = 6 AND date(a.date_etat) <= date('$export_date')";
      }
      else{
        $sql.= " AND a.etat_credit = $etat ";
      }
    }
    if (isset ( $prd )){
    	$sql .= " AND a.id_prod = $prd ";
    }

    $sql .= " ORDER BY a.etat_credit, a.id_doss ;";

    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
    }

    
    $produit_credits = get_produits_credit_balance_agee( $global_monnaie);
    
    // Regroupement totals
    $DATAS = array ();
    $grand_recap = array();
    $recap_par_classe = array();
    $details_recouvrement = array();
    
    // Grand totals
    $cap_restant_tot = 0;
    $cap_theorique_tot = 0;
    $interets_tot = 0;
    $penalites_tot = 0;
    $capital_total = 0;
    $montant_tot = 0;
    $coeff_tot = 0;
    
    // Recap par classe credit totals
    $cap_restant_recap = 0;
    $cap_theorique_recap = 0;
    $interets_recap = 0;
    $penalites_recap = 0;
    $capital_recap = 0;
    $montant_recap = 0;
    $coeff_recap = 0;
    // Ticket 720
    $cap_attendu = 0;
    $int_attendu = 0;
    $pen_attendu = 0;
    $cap_rembourse = 0;
    $int_rembourse = 0;
    $pen_rembourse = 0;

    
    while ($values = $result->fetchrow(DB_FETCHMODE_ASSOC)) 
    {         
        //Initialisation 
        $id_etat_credit = '';      
        $libel_etat_credit = '';
        $num_pret = '';
        $num_client = '';
        $id_prod = '';
        $nom_client = ' ';        
        $libel_gestionnaire = '';
        $date_fin = '';
        $date_dernier_remb = '';
        $montant_debourse = 0;        
        $capital_restant_du = 0;
        $capital_theorique = 0;
        $interets_impayes = 0;
        $penalites_impayes = 0;
        $penalites_attendu = 0;
        $capital_retard = 0;
        $capital_impayes = 0;
        //$montant_total_retard = 0;
        $montant_total_impayes = 0;
        $coeff = 0;
        
        // l'etat du credit
        $etat_credit = $values['etat_credit'];
        $whereCond = " id= $etat_credit";
        $liste_etat_credit = getListeEtatCredit($whereCond);
        $libel_etat_credit = $liste_etat_credit[$etat_credit];        
        
        // numero de pret + client
        $id_etat_credit = $values['etat_credit'];
        $num_pret = $values['id_doss'];        
        $num_client = sprintf("%06d", $values['id_client']);
        $nom_client = getClientName($values['id_client'], $values['id_ag']);   
        $num_client = sprintf("%06d", $values['id_client']);
        $id_prod =$values['id_prod'];
        // Gestionnaire
        $libel_gestionnaire = '';
        if(!empty($values['id_gestionnaire'])) {
            $libel_gestionnaire = $values['id_gestionnaire'] ." (" .getNomUtilisateur($values['id_gestionnaire']). ")";        
        }
        
        // dates
        $date_fin = getLastEcheanceDate($num_pret);
        $date_dernier_remb = getLastPaymentDossierDate($num_pret);        
        
        //Montant deboursee
        $montant_debourse = $values['cre_mnt_deb'];      
        
        if ($values['is_ligne_credit'] == 't') {
            // Le capital restant du
            $capital_restant_du = getCapitalRestantDuLcr($values['id_doss'], $export_date);
        } else {
            // Le capital restant du
            // REL-30 : Le calcule des capitaux restant du reste pareil pour tous les etats credits
            $capital_restant_du = getSoldeCapital($num_pret, $export_date);
        }

        $mnt_reech_apres_date_export = 0; 
        
        // chercher le montant reechelonné aprs la date de sorti du rapport
        if ($values['cre_nbre_reech'] > 0) 
        {
            $reechMorat = getRechMorHistorique (145, $num_client, $values["date_dem"]); //Date demande car date rééch > date demande
            
            if (is_array($reechMorat)) {
                reset($reechMorat);
                foreach ($reechMorat as $id_his_reech => $reech) {
                    if (isBefore(pg2phpDate($export_date), pg2phpDate($reech['date']))) {
                        $mnt_reech_apres_date_export = $mnt_reech_apres_date_export + $reech['infos'];
                    }
                }
            }
        }

        if ($values['is_ligne_credit'] == 'f') {
            // Montant final capital restant du
            $capital_restant_du = $capital_restant_du - $mnt_reech_apres_date_export;
            $capital_restant_du = check_null_numeric_value($capital_restant_du);
        }

        /*if ($values['etat_dossier'] == 6 && $values['etat_credit'] == $etat_perte){ // && isBefore($export_date_debut, pg2phpDate($values['cre_date_etat'])) //!empty($etat) && $etat=='SOLDE' &&
          $capital_restant_du_radie_solde = $capital_restant_du;
          $capital_restant_du = 0;
        }*/

        //Interets + penalites
        //$retards = getRetardPrincIntGarPen($num_pret, $export_date);
        //$retard_principal = $retards['solde_cap'];
        //$retard_garantie = $retards['solde_gar'];

        //Ticket 720 : new PHP functions pour ramener les montants attendus et remboursés pour la periode + REL-30 : Evolution
        ///$attendu = getPrincIntPenAttendu($num_pret, $values['etat_dossier'], $export_date_debut, $export_date, $values['etat_credit'], pg2phpDate($values['cre_date_etat']));
        // REL-30 : Nouveau fonction PHP pour ramener les montants attendus
        // REL-113 : Ajout nouveau parametre (Etat Credit) dans la fonction
        // AT-144 : utiliser la version 2 du fonction getCapIntPenAttendu
        $attendu = getCapIntPenAttendu_v2($num_pret, $export_date_debut, $export_date, $values['etat_credit']);

        // REL-30 : Modification faite - Ramener tous les remboursements dans la periode (Date debut et Date fin incluses)
        $remboursements_periodic = getPrincIntPenRembPeriode($num_pret, $export_date_debut, $export_date, $values['etat_dossier'], $values['etat_credit'], pg2phpDate($values['cre_date_etat']));
        
        //ticket 720 : les montants attendus et remboursés
        $cap_attendu = $attendu['cap_attendu'];
        $cap_attendu = check_null_numeric_value($cap_attendu);
        $int_attendu = $attendu['int_attendu'];
        $int_attendu = check_null_numeric_value($int_attendu);
        $pen_attendu = $attendu['pen_attendu'];
        $pen_attendu = check_null_numeric_value($pen_attendu);
        $cap_rembourse = $remboursements_periodic['remb_cap'];
        $cap_rembourse = check_null_numeric_value($cap_rembourse);
        $int_rembourse = $remboursements_periodic['remb_int'];
        $int_rembourse = check_null_numeric_value($int_rembourse);
        $pen_rembourse = $remboursements_periodic['remb_pen'];
        $pen_rembourse = check_null_numeric_value($pen_rembourse);
        $total_rembourse = $cap_rembourse + $int_rembourse + $pen_rembourse;
        $total_rembourse = check_null_numeric_value($total_rembourse);

        //Les montants impayes
        //$interets_impayes = $retards['solde_int'];
        $interets_impayes = $int_attendu - $int_rembourse;
        //$penalites_attendu = $retards['solde_pen'];
        $penalites_impayes = $pen_attendu - $pen_rembourse;
        $interets_impayes = check_null_numeric_value($interets_impayes);
        $penalites_impayes = check_null_numeric_value($penalites_impayes);

        // Ticket Trac 720 + REL-30 - le calcule du capital impayes reste pareil pour tous les etats credits
        $capital_retard = $cap_attendu - $cap_rembourse;
        //$capital_impayes = $cap_attendu - $cap_rembourse;

        //REL-30 : Gestion des montants remboursés anticipé - Logiquement il y aura des remboursés anticipé pour les credits sain
        if ($values['etat_credit'] == 1){
          if ($capital_retard < 0){
            $capital_retard = 0;
          }
          if ($interets_impayes < 0){
            $interets_impayes = 0;
          }
          if($penalites_impayes < 0){
            $penalites_impayes = 0;
          }
        }

        //Le montant totale des montants impayes
        $montant_total_impayes = $capital_retard + $interets_impayes + $penalites_impayes;

        $capital_retard = check_null_numeric_value($capital_retard);
        //$capital_impayes = check_null_numeric_value($capital_retard);
        $montant_total_impayes = check_null_numeric_value($montant_total_impayes);

        //REL-30 - Coefficient de recouvrement : (montant (capital, interet et penalite) attendu / montant (capital, interet, penalite) remboursé) X 100
        $coeff = (($cap_rembourse + $int_rembourse + $pen_rembourse)/($cap_attendu + $int_attendu + $pen_attendu)) * 100;
        $coeff = check_null_numeric_value($coeff);

        // Multidevise conversion
        if ($global_multidevise) {
            $capital_restant_du = calculeCV($values["devise"], $global_monnaie, $capital_restant_du, $values['id_ag']);
            $capital_theorique = calculeCV($values["devise"], $global_monnaie, $capital_theorique, $values['id_ag']);
            $montant_total_impayes = calculeCV($values["devise"], $global_monnaie, $montant_total_impayes, $values['id_ag']);
            $coeff = calculeCV($values["devise"], $global_monnaie, $coeff, $values['id_ag']);
            //ticket 720 : les montants attendus et remboursés et REL-30 : les montants impayes
            $cap_attendu = calculeCV($values["devise"], $global_monnaie, $cap_attendu, $values['id_ag']);
            $int_attendu = calculeCV($values["devise"], $global_monnaie, $int_attendu, $values['id_ag']);
            $pen_attendu = calculeCV($values["devise"], $global_monnaie, $pen_attendu, $values['id_ag']);
            $cap_rembourse = calculeCV($values["devise"], $global_monnaie, $cap_rembourse, $values['id_ag']);
            $int_rembourse = calculeCV($values["devise"], $global_monnaie, $int_rembourse, $values['id_ag']);
            $pen_rembourse = calculeCV($values["devise"], $global_monnaie, $pen_rembourse, $values['id_ag']);
            $total_rembourse = calculeCV($values["devise"], $global_monnaie, $total_rembourse, $values['id_ag']);
            $capital_retard = calculeCV($values["devise"], $global_monnaie, $capital_retard, $values['id_ag']);
            $interets_impayes = calculeCV($values["devise"], $global_monnaie, $interets_impayes, $values['id_ag']);
            $penalites_impayes = calculeCV($values["devise"], $global_monnaie, $penalites_impayes, $values['id_ag']);
        }
        
        // recap par classe de credit
        $recap_par_classe[$etat_credit]['entete_recap'] = $libel_etat_credit;
        $recap_par_classe[$etat_credit]['details_recap']['cap_restant_recap'] += $capital_restant_du;
        $recap_par_classe[$etat_credit]['details_recap']['cap_theorique_recap'] += $capital_theorique;
        $recap_par_classe[$etat_credit]['details_recap']['montant_recap'] += $montant_total_impayes;
        // Ticket 720 : Recapitulatif
        $recap_par_classe[$etat_credit]['details_recap']['capital_attendu_recap'] += $cap_attendu;
        $recap_par_classe[$etat_credit]['details_recap']['interet_attendu_recap'] += $int_attendu;
        $recap_par_classe[$etat_credit]['details_recap']['penalite_attendu_recap'] += $pen_attendu;
        $recap_par_classe[$etat_credit]['details_recap']['capital_rembourse_recap'] += $cap_rembourse;
        $recap_par_classe[$etat_credit]['details_recap']['interet_rembourse_recap'] += $int_rembourse;
        $recap_par_classe[$etat_credit]['details_recap']['penalite_rembourse_recap'] += $pen_rembourse;
        $recap_par_classe[$etat_credit]['details_recap']['total_rembourse_recap'] += $total_rembourse;
        // REL-30 : les montants impayes
        $recap_par_classe[$etat_credit]['details_recap']['capital_impaye_recap'] += $capital_retard;
        $recap_par_classe[$etat_credit]['details_recap']['interet_impaye_recap'] += $interets_impayes;
        $recap_par_classe[$etat_credit]['details_recap']['penalite_impaye_recap'] += $penalites_impayes;

        // Details des recouvrements regroupement par classe de credit

        $details_recouvrement[$etat_credit]['classe_credit'] = $libel_etat_credit;
        foreach ( $produit_credits as $key_prod => $value_prod ) {
        	if ($value_prod ["id"] == $id_prod ) {
        		$details_recouvrement[$etat_credit][$id_prod]['tot']['libel_prod'] = $value_prod['libel'];
        		//les totaux au niveau de produits
        		$details_recouvrement[$etat_credit][$id_prod]['tot']['capital_restant_du_tot'] += $capital_restant_du;
        		$details_recouvrement[$etat_credit][$id_prod]['tot']['montant_tot_retard_tot'] += $montant_total_impayes;
            //ticket 720 : totaux au niveau des produits
            $details_recouvrement[$etat_credit][$id_prod]['tot']['capital_attendu_tot'] += $cap_attendu;
            $details_recouvrement[$etat_credit][$id_prod]['tot']['interet_attendu_tot'] += $int_attendu;
            $details_recouvrement[$etat_credit][$id_prod]['tot']['penalite_attendu_tot'] += $pen_attendu;
            $details_recouvrement[$etat_credit][$id_prod]['tot']['capital_rembourse_tot'] += $cap_rembourse;
            $details_recouvrement[$etat_credit][$id_prod]['tot']['interet_rembourse_tot'] += $int_rembourse;
            $details_recouvrement[$etat_credit][$id_prod]['tot']['penalite_rembourse_tot'] += $pen_rembourse;
            // REL-30 : les montants impayes
            $details_recouvrement[$etat_credit][$id_prod]['tot']['capital_impaye_tot'] += $capital_impayes;
            $details_recouvrement[$etat_credit][$id_prod]['tot']['interet_impaye_tot'] += $interets_impayes;
            $details_recouvrement[$etat_credit][$id_prod]['tot']['penalite_impaye_tot'] += $penalites_impayes;
            $details_recouvrement[$etat_credit][$id_prod]['tot']['total_rembourse_tot'] += $total_rembourse;

        
        		$details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['num_pret'] = $num_pret;
        		$details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['num_client'] = $num_client;
        	    $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['id_prod'] = $id_prod;
        		$details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['nom_client'] = $nom_client;
        		$details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['gestionnaire'] = $libel_gestionnaire;
        		$details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['cap_restant'] = $capital_restant_du;
        		$details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['montant_retard'] = $montant_total_impayes;
            //ticket 720 : detail product wise
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['capital_attendu'] = $cap_attendu;
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['interet_attendu'] = $int_attendu;
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['penalite_attendu'] = $pen_attendu;
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['capital_rembourse'] = $cap_rembourse;
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['interet_rembourse'] = $int_rembourse;
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['penalite_rembourse'] = $pen_rembourse;
            // REL-30 : les montants impayes
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['capital_impaye'] = $capital_retard;
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['interet_impaye'] = $interets_impayes;
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['penalite_impaye'] = $penalites_impayes;
            $details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['total_rembourse'] = $total_rembourse;
            //ticket 720 : detail product wise
        		$details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['coeff'] = $coeff;
        		$details_recouvrement[$etat_credit][$id_prod]['detail'][$num_pret]['etat_credit'] = $libel_etat_credit;

        	}
        }       
    }

    // ticket 720 : initialization variables totaux
    $capital_attendu_total = 0;
    $interet_attendu_total = 0;
    $penalite_attendu_total = 0;
    $capital_rembourse_total = 0;
    $interet_rembourse_total = 0;
    $penalite_rembourse_total = 0;
    $total_rembourse_total = 0;
    $capital_impaye_total = 0;
    $interet_impaye_total = 0;
    $penalite_impaye_total = 0;


    // Calcule des grand totals
    foreach ($recap_par_classe as &$recap)
    {
        $cap_restant_recap = $recap['details_recap']['cap_restant_recap'];
        $cap_theorique_recap = $recap['details_recap']['cap_theorique_recap'];
        if (!empty($etat) && $etat=='SOLDE' && $recap['entete_recap']=='RADIE') { //trac#720 : Commentaire no.5
          //show nothing
        }
        else {
          $cap_restant_tot += $cap_restant_recap;
          $cap_theorique_tot += $cap_theorique_recap;
          $montant_tot += $recap['details_recap']['montant_recap'];
          //ticket 720 : calcule grand totals
          $capital_attendu_total += $recap['details_recap']['capital_attendu_recap'];
          $interet_attendu_total += $recap['details_recap']['interet_attendu_recap'];
          $penalite_attendu_total += $recap['details_recap']['penalite_attendu_recap'];
          $capital_rembourse_total += $recap['details_recap']['capital_rembourse_recap'];
          $interet_rembourse_total += $recap['details_recap']['interet_rembourse_recap'];
          $penalite_rembourse_total += $recap['details_recap']['penalite_rembourse_recap'];
          $total_rembourse_total += $recap['details_recap']['total_rembourse_recap'];
          //REL-30 : les montants impayes
          $capital_impaye_total += $recap['details_recap']['capital_impaye_recap'];
          $interet_impaye_total += $recap['details_recap']['interet_impaye_recap'];
          $penalite_impaye_total += $recap['details_recap']['penalite_impaye_recap'];
        }
    
        //REL-30 - Coefficient de recouvrement par classe de credit : (montant (capital, interet et penalite) attendu / montant (capital, interet, penalite) remboursé) X 100
        $coeff_recap = (($recap['details_recap']['capital_rembourse_recap'] + $recap['details_recap']['interet_rembourse_recap'] + $recap['details_recap']['penalite_rembourse_recap'])/($recap['details_recap']['capital_attendu_recap'] + $recap['details_recap']['interet_attendu_recap'] + $recap['details_recap']['penalite_attendu_recap']))*100;
        $recap['details_recap']['coeff_recap'] = $coeff_recap;
    }

    // Grand recap
    $grand_recap['cap_restant_tot'] = $cap_restant_tot;
    $grand_recap['cap_theorique_tot'] = $cap_theorique_tot;
    $grand_recap['montant_tot'] = $montant_tot;
    // ticket 720 : grand totals
    $grand_recap['capital_attendu_total'] = $capital_attendu_total;
    $grand_recap['interet_attendu_total'] = $interet_attendu_total;
    $grand_recap['penalite_attendu_total'] = $penalite_attendu_total;
    $grand_recap['capital_rembourse_total'] = $capital_rembourse_total;
    $grand_recap['interet_rembourse_total'] = $interet_rembourse_total;
    $grand_recap['penalite_rembourse_total'] = $penalite_rembourse_total;
    $grand_recap['total_rembourse_total'] = $total_rembourse_total;
    //REL-30 : les montants impayes
    $grand_recap['capital_impaye_total'] = $capital_impaye_total;
    $grand_recap['interet_impaye_total'] = $interet_impaye_total;
    $grand_recap['penalite_impaye_total'] = $penalite_impaye_total;

    //REL-30 - Coefficient de recouvrement Total : (montant (capital, interet et penalite) attendu / montant (capital, interet, penalite) remboursé) X 100
    $coeff_tot = (($capital_rembourse_total + $interet_rembourse_total + $penalite_rembourse_total)/($capital_attendu_total + $interet_attendu_total + $penalite_attendu_total))*100;
    $grand_recap['coeff_tot'] = $coeff_tot;
    
    // Assemblage array final
    $DATAS['grand_recap'] = $grand_recap;
    $DATAS['recap_par_classe'] = $recap_par_classe;
    $DATAS['details_recouvrement'] = $details_recouvrement;
  
    $dbHandler->closeConnection(true);    
    return $DATAS;
}

/**
 *
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport suivi ligne de crédit
 *
 * @param string $date_deb date de début crédit octroyé
 * @param string $date_fin date de fin crédit octroyé
 * @param number $gestionnaire Identifiant du gestionnaire, 0 si tous
 * @param number $num_client Identifiant du client, 0 si tous
 * @param number $prd Identifiant du produit crédit, 0 si tous
 *
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */
function get_rapport_suivi_ligne_credit_data($date_deb, $date_fin, $gestionnaire, $num_client, $prd)
{
    global $dbHandler;
    global $global_multidevise;
    global $global_monnaie, $global_id_agence;

    $db = $dbHandler->openConnection();

    $today = date("d")."/".date("m")."/".date("Y");
    //$yesterday = hier($today);

    $sql = "SELECT a.* FROM (
                SELECT d.id_doss, d.id_client, d.id_prod, d.id_ag, d.date_dem, d.cre_mnt_octr, d.cre_mnt_deb,
                d.etat AS etat_dossier, d.id_agent_gest AS id_gestionnaire, p.devise, d.cre_date_approb, d.duree_mois,
                (case WHEN date('$today') = date(now()) THEN d.cre_etat
                ELSE CalculEtatCredit(d.id_doss, '$today', $global_id_agence) END ) AS etat_credit
                FROM adsys_produit_credit p, ad_dcr d
                WHERE d.id_prod = p.id AND d.is_ligne_credit = 't'
                AND d.cre_date_debloc BETWEEN date('$date_deb') AND date('$date_fin')
                AND d.etat IN (5,6,9)
                AND d.id_ag = p.id_ag
                AND p.id_ag=$global_id_agence
            ) a WHERE 1=1 ";

    if (!empty($num_client)) {
        $sql.= " AND a.id_client = $num_client ";
    }
    if ($gestionnaire > 0) {
        $sql .= " AND a.id_gestionnaire = $gestionnaire ";
    }
    if (isset ( $prd )){
        $sql .= " AND a.id_prod = $prd ";
    }
    $sql .= " ORDER BY a.id_prod ASC, a.etat_dossier ASC, a.id_doss ASC;";

    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
    }

    $produit_credits_lcr = getListeProduitCredit('mode_calc_int=5');

    // Regroupement totals
    $DATAS = array ();
    $grand_recap = array();
    $details_suivi_credit = array();

    // Grand totals
    $montant_octoye_total = 0;
    $cap_debourse_total = 0;
    $cap_restant_du_total = 0;
    $montant_dispo_total = 0;
    $interets_restant_du_total = 0;
    $interets_payes_total = 0;
    $frais_restant_du_total = 0;
    $frais_payes_total = 0;
    //$penalites_du_total = 0;
    //$penalites_payes_total = 0;

    while ($values = $result->fetchrow(DB_FETCHMODE_ASSOC))
    {
        // Init.
        $id_etat_credit = '';
        $libel_etat_credit = '';
        $id_doss = 0;
        $id_prod = 0;
        $num_client = '';
        $nom_client = '';
        $libel_gestionnaire = '';
        $montant_octroye = 0;
        $devise = '';
        $date_octroi = '';
        $duree = '';
        $etat = '';
        $montant_dispo = 0;
        $capital_restant_du = 0;
        $interets_restant_du = 0;
        $interets_payes = 0;
        $frais_restant_du = 0;
        $frais_payes = 0;
        //$penalites_du = 0;
        //$penalites_payes = 0;
        $date_dernier_deb = '';
        $date_dernier_remb = '';
        $date_debut_nettoyage = '';
        $date_fin_echeance = '';

        // l'etat du credit
        $id_etat_credit = $values['etat_credit'];
        $whereCond = " id=$id_etat_credit";
        $liste_etat_credit = getListeEtatCredit($whereCond);
        $libel_etat_credit = $liste_etat_credit[$id_etat_credit];

        // numero de pret + client
        $id_doss = $values['id_doss'];
        $id_prod = $values['id_prod'];
        $num_client = sprintf("%06d", $values['id_client']);
        $nom_client = getClientName($values['id_client']);

        // Gestionnaire
        if(!empty($values['id_gestionnaire'])) {
            $libel_gestionnaire = getNomUtilisateur($values['id_gestionnaire']);
        }

        // Dates
        $date_dernier_deb = getDernierDateDebLcr($id_doss);
        $date_dernier_remb = getDernierDateRembLcr($id_doss);
        $date_fin_echeance = getDateFinEcheanceLcr($id_doss);

        //Montant deboursee
        $montant_octroye = $values['cre_mnt_octr'];
        //$montant_debourse = $values['cre_mnt_deb'];
        $date_octroi = $values['cre_date_approb'];
        $duree = $values['duree_mois'];
        $etat = $values['etat_dossier'];
        $devise = $values['devise'];
        $id_ag = $values['id_ag'];

        // Calculated data
        $montant_dispo = getMontantRestantADebourserLcr($id_doss, $today);
        $capital_restant_du = getCapitalRestantDuLcr($id_doss, $today);
        $interets_restant_du = check_null_numeric_value(getCalculInteretsLcr($id_doss, $today));
        $interets_payes = getCalculInteretsLcr($id_doss, $today, 0) - $interets_restant_du;
        $frais_restant_du = check_null_numeric_value(getCalculFraisLcr($id_doss, $today));
        $frais_payes = getCalculFraisLcr($id_doss, $today, 0) - $frais_restant_du;
        //$penalites_du = 0;
        //$penalites_payes = 0;

        // Multidevise conversion
        if ($global_multidevise) {
            $montant_octroye = calculeCV($devise, $global_monnaie, $montant_octroye, $id_ag);
            $montant_dispo = calculeCV($devise, $global_monnaie, $montant_dispo, $id_ag);
            $capital_restant_du = calculeCV($devise, $global_monnaie, $capital_restant_du, $id_ag);
            $interets_restant_du = calculeCV($devise, $global_monnaie, $interets_restant_du, $id_ag);
            $interets_payes = calculeCV($devise, $global_monnaie, $interets_payes, $id_ag);
            $frais_restant_du = calculeCV($devise, $global_monnaie, $frais_restant_du, $id_ag);
            $frais_payes = calculeCV($devise, $global_monnaie, $frais_payes, $id_ag);
        }

        $montant_octoye_total += $montant_octroye;
        $cap_debourse_total += $capital_restant_du;
        $cap_restant_du_total += $capital_restant_du;
        $montant_dispo_total += $montant_dispo;
        $interets_restant_du_total += $interets_restant_du;
        $interets_payes_total += $interets_payes;
        $frais_restant_du_total += $frais_restant_du;
        $frais_payes_total += $frais_payes;

        $details_suivi_credit[$id_doss]['id_doss'] = $id_doss;
        $details_suivi_credit[$id_doss]['id_prod'] = $id_prod;
        $details_suivi_credit[$id_doss]['num_client'] = $num_client;
        $details_suivi_credit[$id_doss]['nom_client'] = $nom_client;
        $details_suivi_credit[$id_doss]['libel_gestionnaire'] = $libel_gestionnaire;
        $details_suivi_credit[$id_doss]['montant_octroye'] = $montant_octroye;
        $details_suivi_credit[$id_doss]['devise'] = $devise;
        $details_suivi_credit[$id_doss]['date_octroi'] = $date_octroi;
        $details_suivi_credit[$id_doss]['duree'] = $duree;
        $details_suivi_credit[$id_doss]['etat'] = $etat;
        $details_suivi_credit[$id_doss]['montant_dispo'] = $montant_dispo;
        $details_suivi_credit[$id_doss]['capital_restant_du'] = $capital_restant_du;
        $details_suivi_credit[$id_doss]['interets_restant_du'] = $interets_restant_du;
        $details_suivi_credit[$id_doss]['interets_payes'] = $interets_payes;
        $details_suivi_credit[$id_doss]['frais_restant_du'] = $frais_restant_du;
        $details_suivi_credit[$id_doss]['frais_payes'] = $frais_payes;
        $details_suivi_credit[$id_doss]['date_dernier_deb'] = $date_dernier_deb;
        $details_suivi_credit[$id_doss]['date_dernier_remb'] = $date_dernier_remb;
        $details_suivi_credit[$id_doss]['date_fin_echeance'] = $date_fin_echeance;
    }

    // Grand recap
    $grand_recap['montant_octoye_total'] = $montant_octoye_total;
    $grand_recap['cap_debourse_total'] = $cap_debourse_total;
    $grand_recap['cap_restant_du_total'] = $cap_restant_du_total;
    $grand_recap['montant_dispo_total'] = $montant_dispo_total;
    $grand_recap['interets_restant_du_total'] = $interets_restant_du_total;
    $grand_recap['interets_payes_total'] = $interets_payes_total;
    $grand_recap['frais_restant_du_total'] = $frais_restant_du_total;
    $grand_recap['frais_payes_total'] = $frais_payes_total;

    // Assemblage array final
    $DATAS['grand_recap'] = $grand_recap;
    $DATAS['details_suivi_credit'] = $details_suivi_credit;

    $dbHandler->closeConnection(true);

    return $DATAS;
}

/**
 *
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport historisation ligne de crédit
 *
 * @param Array $infos_doss Info du dosssier crédit
 *
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */
function get_rapport_his_ligne_credit_data($infos_doss)
{
    global $dbHandler;
    global $global_multidevise;
    global $global_monnaie, $global_id_agence;
    global $adsys;

    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ad_lcr_his WHERE type_evnt NOT IN (1,5,6,7,8,9) AND id_ag = $global_id_agence AND id_doss = " . $infos_doss['id_doss'] . " ORDER BY id ASC;";

    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
    }

    $produit_credits_lcr = getListeProduitCredit('mode_calc_int=5');

    // Regroupement totals
    $DATAS = array ();
    $infos_syn = array();
    $details_his_credit = array();
    $total_result = array();

    // Informations synthétiques
    $infos_syn['num_client'] = $infos_doss['id_client'];
    $infos_syn['nom_client'] = getClientName($infos_doss['id_client']);
    $infos_syn['id_doss'] = $infos_doss['id_doss'];
    $infos_syn['etat'] = $adsys["adsys_etat_dossier_credit"][$infos_doss['etat']];
    $infos_syn['date_dem'] = pg2phpDate($infos_doss['date_dem']);
    $infos_syn['date_approb'] = pg2phpDate($infos_doss['cre_date_approb']);
    $infos_syn['libel_prod'] = getLibelPrdt($infos_doss['id_prod'], "adsys_produit_credit" );
    $infos_syn['montant_octroye'] = $infos_doss['cre_mnt_octr'];
    $infos_syn['devise'] = $infos_doss['devise'];
    $infos_syn['taux_interet'] = affichePourcentage($infos_doss['tx_interet_lcr']);
    $infos_syn['taux_frais'] = affichePourcentage($infos_doss['taux_frais_lcr']);
    $infos_syn['date_fin_ech'] = pg2phpDate(getDateFinEcheanceLcr($infos_doss['id_doss']));

    // Grand totals
    $mnt_deb_total = 0;
    $cap_remb_total = 0;
    $int_remb_total = 0;
    $frais_remb_total = 0;
    $pen_remb_total = 0;

    $count = 0;
    while ($values = $result->fetchrow(DB_FETCHMODE_ASSOC))
    {
        $his_credit = array();

        $date_evnt = $values['date_evnt'];
        $his_credit['date_evnt'] = $date_evnt;

        // Init. array
        if (!isset($details_his_credit[$count]['mnt_deb'])) {
            $his_credit['mnt_deb'] = 0;
        }
        if (!isset($details_his_credit[$count]['cap_remb'])) {
            $his_credit['cap_remb'] = 0;
        }
        if (!isset($details_his_credit[$count]['int_remb'])) {
            $his_credit['int_remb'] = 0;
        }
        if (!isset($details_his_credit[$count]['frais_remb'])) {
            $his_credit['frais_remb'] = 0;
        }

        if ($values['type_evnt'] == 2 && $values['nature_evnt'] == null) { // Montant déboursé
            $his_credit['mnt_deb'] = $values['valeur'];
            $mnt_deb_total += $values['valeur'];
        } elseif ($values['type_evnt'] == 3 && $values['nature_evnt'] == 1) { // Capital remboursé
            $his_credit['cap_remb'] = $values['valeur'];
            $cap_remb_total += $values['valeur'];
        } elseif ($values['type_evnt'] == 3 && $values['nature_evnt'] == 2) { // Intérêts remboursés
            $his_credit['int_remb'] = $values['valeur'];
            $int_remb_total += $values['valeur'];
        } elseif ($values['type_evnt'] == 4 && $values['nature_evnt'] == null) { // Frais remboursés
            $his_credit['frais_remb'] = $values['valeur'];
            $frais_remb_total += $values['valeur'];
        }

        // Pénalités remboursés
        $his_credit['pen_remb'] = 0;

        // Capital restant dû
        $his_credit['cap_restant_du'] = ($mnt_deb_total - $cap_remb_total);

        $details_his_credit[$date_evnt][] = $his_credit;

        $count++;
    }

    // Calc Pénalités
    $sql_pen = "SELECT * FROM ad_sre WHERE id_ag = $global_id_agence AND id_doss = " . $infos_doss['id_doss'] . " AND mnt_remb_pen > 0 ORDER BY date_remb ASC;";

    $result_pen = $db->query($sql_pen);

    if (DB::isError($result_pen)) {
        $dbHandler->closeConnection(false);
        Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result_pen->getMessage());
    }

    while ($values_pen = $result_pen->fetchrow(DB_FETCHMODE_ASSOC))
    {
        $his_credit = array();

        $date_remb = php2pg(pg2phpDate($values_pen['date_remb']));
        $his_credit['date_evnt'] = $date_remb;

        $his_credit['mnt_deb'] = $his_credit['cap_remb'] = $his_credit['int_remb'] = $his_credit['frais_remb'] = 0;

        // Pénalités remboursés
        $his_credit['pen_remb'] = check_null_numeric_value($values_pen['mnt_remb_pen']);
        $pen_remb_total += $his_credit['pen_remb'];

        $his_credit['cap_restant_du'] = -1;

        $details_his_credit[$date_remb][] = $his_credit;
    }

    $total_result['mnt_deb_total'] = $mnt_deb_total;
    $total_result['cap_remb_total'] = $cap_remb_total;
    $total_result['int_remb_total'] = $int_remb_total;
    $total_result['frais_remb_total'] = $frais_remb_total;
    $total_result['pen_remb_total'] = $pen_remb_total;

    // Order by date ascending
    ksort($details_his_credit);

    // Assemblage array final
    $DATAS['infos_syn'] = $infos_syn;
    $DATAS['details_his_credit'] = $details_his_credit;
    $DATAS['total_result'] = $total_result;

    $dbHandler->closeConnection(true);

    return $DATAS;
}

/**
 *
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport comptes dormants
 *
 * @param number $id_prod Identifiant du produit épargne, 0 si tous
 *
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */
function get_rapport_compte_dormant_data($id_prod, $date_rapport)
{
  global $dbHandler;
  global $global_monnaie, $global_id_agence;

  $db = $dbHandler->openConnection();

  /*$sql = "SELECT c.id_cpte, c.id_titulaire, c.num_complet_cpte, (CASE cli.statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom
  WHEN '2' THEN pm_raison_sociale WHEN '3' THEN gi_nom WHEN '4' THEN gi_nom END) as nom_client, c.solde, c
  .date_blocage, c.id_prod, p.libel as libel_prod FROM adsys_produit_epargne p, ad_cpt c, ad_cli cli WHERE p.id = c.id_prod AND c
  .id_titulaire = cli.id_client AND c.etat_cpte = 4 AND p.id_ag=$global_id_agence";*/

  /*$sql = "select A.* from
  (
  select id_cpte, id_titulaire, num_complet_cpte, (CASE cli.statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom
  WHEN '2' THEN pm_raison_sociale WHEN '3' THEN gi_nom WHEN '4' THEN gi_nom END) as nom_client,
  calculsoldecpte(cpt.id_cpte, NULL, date('$date_rapport')) as solde,
  cpt.date_blocage,cpt.id_prod, prd.libel as libel_prod ,
  case when date('$date_rapport') = date(now()) then cpt.etat_cpte else calculetatcpte_hist(numagc(),cpt.id_cpte,date('$date_rapport')) end as etat_cpte
  from ad_cpt cpt, ad_cli cli, adsys_produit_epargne prd
  where cpt.id_titulaire = cli.id_client
  and cpt.id_prod = prd.id
  ) A
  where etat_cpte = 4";*/
  /*$sql = "select A.* from (select distinct c.id_cpte as id_cpte, c.id_titulaire, c.num_complet_cpte, (CASE cli.statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom  WHEN '2' THEN pm_raison_sociale WHEN '3' THEN gi_nom WHEN '4' THEN gi_nom END) as nom_client,calculsoldecptedormant(c.id_cpte, NULL, date('$date_rapport')) as solde, c.date_blocage, c.id_prod, prod.libel
from ad_mouvement m, ad_ecriture e, ad_cpt c, adsys_produit_epargne prod, ad_cli cli
where m.id_ecriture = e.id_ecriture and to_number(e.info_ecriture,'9999999999') = c.id_cpte and c.id_prod = prod.id and c.id_titulaire = cli.id_client and m.compte = (select num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = e.type_operation AND sens = 'c' AND id_ag = numagc()) and date(m.date_valeur) <= date('$date_rapport') and date(e.date_comptable) <= date('$date_rapport') order by c.id_cpte) A
where A.solde <> 0 ";*/
  $sql = "select A.* from ( select c.id_cpte as id_cpte, c.id_titulaire, c.num_complet_cpte, (CASE cli.statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom  WHEN '2' THEN pm_raison_sociale WHEN '3' THEN gi_nom WHEN '4' THEN gi_nom END) as nom_client,
c.date_blocage, c.id_prod, prod.libel, sum(case when m.sens = 'c' then m.montant else -1*m.montant end) as solde from ad_mouvement m inner join ad_ecriture e on m.id_ecriture = e.id_ecriture and m.id_ag = e.id_ag inner join ad_cpt c on c.id_ag = e.id_ag and coalesce(to_number(e.info_ecriture,'999999999'),m.cpte_interne_cli) = c.id_cpte inner join adsys_produit_epargne prod on c.id_ag = prod.id_ag and c.id_prod = prod.id inner join ad_cli cli on c.id_titulaire = cli.id_client  and c.id_ag = cli.id_ag where  m.compte = (select num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = 170 AND sens = 'c' AND id_ag = numagc()) and e.date_comptable <= date('$date_rapport') group by c.id_cpte, c.id_titulaire, c.num_complet_cpte, (CASE cli.statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom  WHEN '2' THEN pm_raison_sociale WHEN '3' THEN gi_nom WHEN '4' THEN gi_nom END), c.date_blocage, c.id_prod, prod.libel ) A where A.solde <> 0";

  if (isset ( $id_prod )) {
    $sql .= " AND A.id_prod = $id_prod ";
  }

  /*if (!empty( $date_rapport )) {
    $sql .= " AND c.date_blocage <= '$date_rapport' ";
  }*/

  $sql .= " ORDER BY A.id_prod ASC, A.id_titulaire ASC; ";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  // Regroupement totals
  $DATAS = array ();
  $grand_recap = array();
  $details_compte_dormant = array();

  // Grand totals
  $nombre_comptes_dormants_total = 0;
  $solde_comptes_dormants_total = 0;

  while ($values = $result->fetchrow(DB_FETCHMODE_ASSOC))
  {
    // Init.
    $id_cpte = 0;
    $id_prod = 0;
    $libel_prod = '';
    $num_client = '';
    $num_compte = '';
    $nom_client = '';
    $solde_compte = 0;
    $date_blocage = '';

    // Client
    $id_cpte = trim($values['id_cpte']);
    $id_prod = trim($values['id_prod']);
    $libel_prod = trim($values['libel_prod']);
    $num_client = sprintf("%06d", $values['id_titulaire']);
    $num_compte = trim($values['num_complet_cpte']);
    $nom_client = trim($values['nom_client']);

    // Montant compte
    $solde_compte = $values['solde'];
    $date_blocage = $values['date_blocage'];

    $nombre_comptes_dormants_total += 1;
    $solde_comptes_dormants_total += $solde_compte;

    $details_compte_dormant[$id_cpte]['id_cpte'] = $id_cpte;
    $details_compte_dormant[$id_cpte]['id_prod'] = $id_prod;
    $details_compte_dormant[$id_cpte]['libel_prod'] = $libel_prod;
    $details_compte_dormant[$id_cpte]['num_client'] = $num_client;
    $details_compte_dormant[$id_cpte]['num_compte'] = $num_compte;
    $details_compte_dormant[$id_cpte]['nom_client'] = $nom_client;
    $details_compte_dormant[$id_cpte]['solde_compte'] = $solde_compte;
    $details_compte_dormant[$id_cpte]['date_blocage'] = $date_blocage;
  }

  // Grand recap
  $grand_recap['nombre_comptes_dormants_total'] = $nombre_comptes_dormants_total;
  $grand_recap['solde_comptes_dormants_total'] = $solde_comptes_dormants_total;

  // Assemblage array final
  $DATAS['grand_recap'] = $grand_recap;
  $DATAS['details_compte_dormant'] = $details_compte_dormant;

  $dbHandler->closeConnection(true);

  return $DATAS;
}

/**
 * 
 * Recupere les donnees pour le rapport d'equilibre/inventaire comptabilite
 * 
 * @param unknown $export_date
 * @param string $compte_comptable
 * @return array
 */
function get_rapport_equilibre_compta_data($export_date, $compte_comptable = NULL)
{
	global $dbHandler;
	global $global_multidevise;
	global $global_monnaie,$global_id_agence;
	
	$DATAS = array();	
	$db = $dbHandler->openConnection();
	
	if(empty($export_date)) {
		$export_date = date("Y")."-".date("m")."-".date("d");
	}
	elseif(strpos($export_date, '/')){
		$export_date = php2pg($export_date);
	}
	
	if(empty($compte_comptable)) {
		$compte_comptable = 'NULL';
		$sql = "SELECT * FROM get_rpt_ecart_compta('$export_date', $compte_comptable);";
	}
	else {
		$sql = "SELECT * FROM get_rpt_ecart_compta('$export_date', '$compte_comptable');";
	}
	
	$result = $db->query($sql);
	
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
	}
	
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) 
	{		 
		if(!empty($row['cre_etat'])) {			
			$etat_credit = $row['cre_etat'];			
			$info_etat = getListeEtatCredit(" id = $etat_credit ");
			$row['cre_etat'] = $info_etat[1];			
		}
				
		$row['date_ecart'] = pg2phpDate($row['date_ecart']);
		$DATAS[] = $row;	
	}	
	$dbHandler->closeConnection(true);
	return $DATAS;
}

/**
 *
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport inventaire des dépots
 *
 * @param array $where les filtres de la requete
 *
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */
function get_rapport_inventaire_depot($where,$date_deb,$date_fin,$type_loc,$n_agence = null, $n_guichet= null){

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_prod,id_cpte,num_complet_cpte, adcl.id_client,
    CASE statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom WHEN '2' THEN pm_raison_sociale WHEN '3'
    THEN gi_nom WHEN '4'  THEN gi_nom END as nom_complet,
    pp_etat_civil,";
  if ($type_loc == 2){
    $sql .= " adloc.libelle_localisation as sector, ";
  }else{
    $sql .= " adloc.libel as sector, ";
  }

    $sql .= "ville,num_tel,pp_nm_piece_id,pp_date_naissance ::date,loc3,
    m.solde_actuel,
    (m.solde_actuel - (m.montant_total_per + m.montant_total_apre)) as solde_debut,
    m.montant_depot_per as montant_depot,
    m.montant_retrait_per as montant_retrait,
    ((m.solde_actuel - (m.montant_total_per + m.montant_total_apre)) + m.montant_total_per)  as solde_fin
    from ad_cpt adcp
    LEFT JOIN  (
    select m1.cpte_interne_cli, m1.id_ag, cpt.solde as solde_actuel,
    sum(case when m1.sens = 'c' and e1.date_comptable >= date('$date_deb') and e1.date_comptable <= date('$date_fin') then COALESCE(m1.montant,0) else 0 end ) as montant_depot_per,
    sum(case when m1.sens = 'd' and e1.date_comptable >= date('$date_deb') and e1.date_comptable <= date('$date_fin') then COALESCE(m1.montant,0) else 0 end ) as montant_retrait_per,
    sum(case when m1.sens = 'c' and e1.date_comptable >= date('$date_deb') and e1.date_comptable <= date('$date_fin') then COALESCE(m1.montant,0) else 0 end ) -
    sum(case when m1.sens = 'd' and e1.date_comptable >= date('$date_deb') and e1.date_comptable <= date('$date_fin') then COALESCE(m1.montant,0) else 0 end ) as montant_total_per,
    sum(case when m1.sens = 'c' and e1.date_comptable > date('$date_fin') then COALESCE(m1.montant,0) else 0 end ) -
    sum(case when m1.sens = 'd' and e1.date_comptable > date('$date_fin') then COALESCE(m1.montant,0) else 0 end ) as montant_total_apre
    from ad_his h1 inner join ad_ecriture e1 on h1.id_his = e1.id_his
    inner join ad_mouvement m1 on e1.id_ecriture = m1.id_ecriture
    inner join ad_cpt cpt on m1.cpte_interne_cli = cpt.id_cpte and m1.id_ag = cpt.id_ag
    where e1.date_comptable >= date(cpt.date_ouvert) and e1.date_comptable <= date(now())
    group by m1.cpte_interne_cli, m1.id_ag, cpt.solde
    ) m

    on adcp.id_cpte  = m.cpte_interne_cli and m.id_ag=adcp.id_ag and adcp.id_ag=$global_id_agence
    left join ad_cli adcl on adcp.id_titulaire = adcl.id_client ";
  if ($type_loc == 2){
    $sql .= "LEFT JOIN adsys_localisation_rwanda adloc on adloc.id=adcl.secteur ";
  }
  else{
    $sql .= "LEFT JOIN adsys_localisation adloc on adloc.id=adcl.id_loc2 ";
  }
    $sql .= "Where adcp.etat_cpte in(1,2,3,4,5,6,7)";


  if(isset($where)) {
    foreach ($where as $key => $valeur) {
      $sql .= " AND $key='$valeur' ";
    }
  }
  else{
    $sql .= " and (adcp.id_prod =1 or adcp.id_prod > 5 ) ";
  }
  if (isset($n_agence)){
      $sql .= " and adcl.n_agence = $n_agence";
  }
  if (isset($n_guichet)){
      $sql .= " and adcl.n_guichet = $n_guichet";

  }
  if ($type_loc == 2){
    $sql .= "group by id_prod,id_cpte,num_complet_cpte,adcl.id_client,CASE statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom WHEN '2' THEN pm_raison_sociale WHEN '3'  THEN gi_nom WHEN '4'  THEN gi_nom END ,pp_etat_civil,adloc.libelle_localisation,ville,num_tel,pp_nm_piece_id,pp_date_naissance ::date,loc3,m.solde_actuel,solde_debut,montant_depot,montant_retrait,solde_fin,m.montant_total_per,m.montant_total_apre order by id_cpte";
  }else{
    $sql .= "group by id_prod,id_cpte,num_complet_cpte,adcl.id_client,CASE statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom WHEN '2' THEN pm_raison_sociale WHEN '3'  THEN gi_nom WHEN '4'  THEN gi_nom END ,pp_etat_civil,adloc.libel,ville,num_tel,pp_nm_piece_id,pp_date_naissance ::date,loc3,m.solde_actuel,solde_debut,montant_depot,montant_retrait,solde_fin,m.montant_total_per,m.montant_total_apre order by id_cpte";
  }

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__ .$sql);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $epargne[$row['id_prod']][$row['id_cpte']] = $row;
  }

  $dbHandler->closeConnection(true);
  return $epargne;
}

function getDateExerciseComptable() {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = " select max(date_deb_exo) as date_debut from ad_exercices_compta where etat_exo = 1 AND id_ag = $global_id_agence ";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
    }
    $row = $result->fetchrow();
    $date = $row[0];

    $dbHandler->closeConnection(true);
    return $date;

}


/**
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport sur les operations diverses
 * @param array $where les filtres de la requete
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 * T437
 * @author Kheshan.A.G
 */

function donnnes_oper_div( $date_debut ,$date_fin ,$type_operation, $login ) {

	global $dbHandler;
	global $global_multidevise;
	global $global_monnaie,$global_id_agence;
	$db = $dbHandler->openConnection();
	   
     if($date_debut == NULL){
		$date_debut = date("01/01/2000");	
	  }
	
	if($date_fin == NULL){
		$date_fin =  date("d/m/Y");
	 }
		// getGrandlivreView
		$sql ="SELECT * FROM (SELECT a.id_his,a.date_comptable, a.libel_ecriture,montant, a.type_operation, b.login ,b.id_client
		              from getGrandLivreView(date('$date_debut'), date('$date_fin'), $global_id_agence) AS a 
		       inner join ad_his b ON (a.id_his = b.id_his ) where  a.id_ag = $global_id_agence ) AS c inner join ad_cpt_ope d on  c.type_operation = d.type_operation where d.categorie_ope in(2,3)  ";
		
		//filtre login
		if ((isset($login) && $login != "0") )
			$sql .= " AND c.login = '$login' ";
		//filtre operation
		if ((isset ( $type_operation )&& $type_operation != 0))
			$sql .= " AND c.type_operation = $type_operation ";

		$sql .= " order by c.date_comptable ";
       
		$result1 = $db->query ( $sql );
		if (DB::isError ( $result1 )) {
			$dbHandler->closeConnection ( false );
			Signalerreur ( __FILE__, __LINE__, __FUNCTION__, _ ( "DB" ) . ": " . $result1->getMessage () );
		}
		
		$DATAS['total']= 0;

		while ( $values = $result1->fetchrow ( DB_FETCHMODE_ASSOC ) ) {
			$DATAS[$values['id_his']]['num_transaction'] =$values['id_his'];
			$DATAS[$values['id_his']]['login'] =$values['login'];
			$DATAS[$values['id_his']]['date'] =$values['date_comptable'];
			$DATAS[$values['id_his']]['id_operation'] =$values['id_operation'];
			$DATAS[$values['id_his']]['libel_ecriture'] =$values['libel_ecriture'];
			$DATAS[$values['id_his']]['num_client'] =$values['id_client'];
			$DATAS[$values['id_his']]['montant'] =$values['montant'];
			
			$DATAS['total'] = $DATAS['total']+ $values['montant'];
			
				};
	
	return $DATAS;	
}


/**
 * Récupère les données du rapport état chéquiers imprimés
 * @param $criteres_recherche
 * @return array
 */
function getRapportChequiersEnOppositionData($criteres_recherche)
{
  global $dbHandler, $adsys, $global_id_agence;
  $db = $dbHandler->openConnection();

  $date_debut = $criteres_recherche['date_debut'];
  $date_fin = $criteres_recherche['date_fin'];
  $num_client = $criteres_recherche['num_client'];
  if (isset($criteres_recherche['n_agence'])){
      $n_agence = $criteres_recherche['n_agence'];
  }
  if (isset($criteres_recherche['n_guichet'])){
      $n_guichet = $criteres_recherche['n_guichet'];
  }

  $sql = "SELECT cpt.id_titulaire AS id_client, cpt.num_complet_cpte,
            (SELECT CASE statut_juridique
            WHEN 1 THEN pp_nom||' '||pp_prenom
            WHEN 2 THEN pm_raison_sociale
            WHEN 3 THEN gi_nom
            WHEN 4 THEN gi_nom END) AS nom_client,
            ch.date_statut, ch.id_chequier, ch.num_first_cheque, ch.num_last_cheque, ch.etat_chequier, ch.description
            FROM ad_chequier ch
            INNER JOIN ad_cpt cpt ON ch.id_cpte = cpt.id_cpte
            INNER JOIN ad_cli cli ON cpt.id_titulaire = cli.id_client
            WHERE date_statut BETWEEN date('$date_debut') AND date('$date_fin')
            AND ch.id_ag = cpt.id_ag AND cpt.id_ag = cli.id_ag AND ch.id_ag = $global_id_agence
            AND ch.etat_chequier=5 ";


  if(!empty($num_client)) {
    $sql .= " AND id_client = $num_client ";
  }
  if (!empty($n_agence)){
      $sql .= " AND cli.n_agence = $n_agence ";
  }
  if (!empty($n_guichet)){
      $sql .= " AND cli.n_guichet = $n_guichet ";
  }

  $sql .= " ORDER BY  id_client, ch.id_chequier;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $DATAS = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $DATAS['chequiers_opposition_data'][] = $row;
  }
  if (isFusion()){
      $nbChequiersParEtat = getNombreChequiersParEtat(array('champ_date' => 'date_statut', 'date_debut' => $date_debut, 'date_fin' => $date_fin, 'etat_chequier' => 5),$n_agence,$n_guichet);
  }else{
      $nbChequiersParEtat = getNombreChequiersParEtat(array('champ_date' => 'date_statut', 'date_debut' => $date_debut, 'date_fin' => $date_fin, 'etat_chequier' => 5));
  }


  // Recap par etat chequier
  $DATAS['nb_chequiers_en_opposition'] = $nbChequiersParEtat[5]['nb_chequier'];






  $sql2 = "SELECT distinct cpt.id_titulaire AS id_client, cpt.num_complet_cpte,
            (SELECT CASE statut_juridique
            WHEN 1 THEN pp_nom||' '||pp_prenom
            WHEN 2 THEN pm_raison_sociale
            WHEN 3 THEN gi_nom
            WHEN 4 THEN gi_nom END) AS nom_client,
            ch.date_opposition, ch.id_cheque, ch.etat_cheque, ch.description
            FROM ad_cheque ch
            INNER JOIN ad_chequier chequier ON ch.id_chequier = chequier.id_chequier
            INNER JOIN ad_cpt cpt ON chequier.id_cpte = cpt.id_cpte
            INNER JOIN ad_cli cli ON cpt.id_titulaire = cli.id_client
            WHERE ch.id_ag = cpt.id_ag AND cpt.id_ag = cli.id_ag AND ch.id_ag = numagc()
            AND is_opposition = 't'
            AND date_opposition BETWEEN date('$date_debut') AND date('$date_fin') ";

  if(!empty($num_client)) {
    $sql2 .= " AND id_client = $num_client ";
  }
    if (!empty($n_agence)){
        $sql .= " AND cli.n_agence = $n_agence ";
    }
    if (!empty($n_guichet)){
        $sql .= " AND cli.n_guichet = $n_guichet ";
    }


    $sql2 .= " ORDER BY  id_client, ch.id_cheque;";

  $result2 = $db->query($sql2);

  if (DB::isError($result2)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }


  while ($row = $result2->fetchrow(DB_FETCHMODE_ASSOC)) {
    $row['libel_etat_cheque_ch'] = $adsys["adsys_etat_cheque"][$row['etat_cheque']];
    $DATAS['cheques_opposition_data'][] = $row;
  }
  $nbChequesParEtat = getNombreChequesEnOpposition(array('date_debut' => $date_debut, 'date_fin' => $date_fin));

  // Recap par etat chequier
  $DATAS['nb_cheques_en_opposition'] = $nbChequesParEtat['nb_cheques'];


  $dbHandler->closeConnection(true);
  return $DATAS;
}

/**
 * Récupère les données du rapport état chéquiers imprimés
 * @param $criteres_recherche
 * @return array
 */
function getRapportEtatChequiersImprimesData($criteres_recherche)
{
    global $dbHandler, $adsys, $global_id_agence;
    $db = $dbHandler->openConnection();

    $date_debut = $criteres_recherche['date_debut'];
    $date_fin = $criteres_recherche['date_fin'];
    $num_client = $criteres_recherche['num_client'];
    $etat_chequier = $criteres_recherche['etat_chequier'];
    if (isFusion()){
        if (isset($criteres_recherche['n_agence']))
            $n_agence = $criteres_recherche['n_agence'];

        if (isset($criteres_recherche['n_guichet']))
            $n_guichet = $criteres_recherche['n_guichet'];
    }

    $sql = "SELECT cpt.id_titulaire AS id_client, cpt.num_complet_cpte,
            (SELECT CASE statut_juridique
            WHEN 1 THEN pp_nom||' '||pp_prenom
            WHEN 2 THEN pm_raison_sociale
            WHEN 3 THEN gi_nom
            WHEN 4 THEN gi_nom END) AS nom_client,
            ch.date_livraison, ch.id_chequier, ch.num_first_cheque, ch.num_last_cheque, ch.etat_chequier,
            ((ch.num_last_cheque - ch.num_first_cheque)+1) as nb_cheque,
            ROW_NUMBER() OVER (ORDER BY ch.date_livraison) AS row_counter
            FROM ad_chequier ch
            INNER JOIN ad_cpt cpt ON ch.id_cpte = cpt.id_cpte
            INNER JOIN ad_cli cli ON cpt.id_titulaire = cli.id_client
            WHERE date_livraison BETWEEN date('$date_debut') AND date('$date_fin')
            AND ch.id_ag = cpt.id_ag AND cpt.id_ag = cli.id_ag AND ch.id_ag = $global_id_agence ";

    if($etat_chequier == 10) {
      $sql .= " AND ch.etat_chequier IN (0, 1) ";
    }
    else {
      $sql .= " AND ch.etat_chequier=$etat_chequier ";
    }

    if(!empty($num_client)) {
      $sql .= " AND id_client = $num_client ";
    }
    if(isFusion()) {
        if (!empty($n_agence)) {
            $sql .= " AND cli.n_agence  = $n_agence ";
        }
        if (!empty($n_guichet)) {
            $sql .= " AND cli.n_guichet = $n_guichet ";
        }
    }


    $sql .= " ORDER BY  row_counter, ch.etat_chequier, id_client, ch.id_chequier;";
    $result = $db->query($sql);

    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
    }

    $DATAS = array();

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      $row['date_livraison'] = pg2phpDate($row['date_livraison']);
      $row['etat_chequier'] = intval($row['etat_chequier']);
      $row['etat_chequier'] = $adsys["adsys_etat_chequier"][$row['etat_chequier']];
      $DATAS['etat_chequiers_data'][] = $row;
    }

    // Recap par etat chequier
    if (isFusion()){
        $DATAS['syntheses_par_etat'] = getNombreChequiersParEtat(array('champ_date' => 'date_livraison', 'date_debut' => $date_debut, 'date_fin' => $date_fin),$n_agence,$n_guichet);
    }else{
        $DATAS['syntheses_par_etat'] = getNombreChequiersParEtat(array('champ_date' => 'date_livraison', 'date_debut' => $date_debut, 'date_fin' => $date_fin));
    }


    $dbHandler->closeConnection(true);
    return $DATAS;
}

/**
 * Recupere les données du rapport Liste des chéquiers commandés
 * @param $criteres_recherche
 * @return array
 */
function getRapportCheqCommandeOrImpressionData($criteres_recherche, $isRapportCheqCmd = true)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $date_debut = $criteres_recherche['date_debut'];
  $date_fin = $criteres_recherche['date_fin'];
  $num_client = $criteres_recherche['num_client'];
  if (isFusion()){
    if (isset($criteres_recherche['n_agence']))
      $n_agence = $criteres_recherche['n_agence'];

      if (isset($criteres_recherche['n_guichet']))
      $n_guichet = $criteres_recherche['n_guichet'];
  }

  $sql = "SELECT cpt.id_titulaire AS id_client, cpt.num_complet_cpte,
          (SELECT CASE statut_juridique
          WHEN 1 THEN pp_nom||' '||pp_prenom
          WHEN 2 THEN pm_raison_sociale
          WHEN 3 THEN gi_nom
          WHEN 4 THEN gi_nom END) AS nom_client,
          ch.frais, ch.date_cmde, ch.date_envoi_impr,
          ROW_NUMBER() OVER (ORDER BY ch.date_cmde) AS row_counter
          FROM ad_commande_chequier ch
          INNER JOIN ad_cpt cpt ON ch.id_cpte = cpt.id_cpte
          INNER JOIN ad_cli cli ON cpt.id_titulaire = cli.id_client
          WHERE date_cmde BETWEEN date('$date_debut') AND date('$date_fin') ";

  if($isRapportCheqCmd) {
    $sql .= " AND ch.date_envoi_impr IS NULL ";
  }
  else {
    $sql .= " AND ch.etat = 2 "; // En attente d'impréssion
  }

  $sql .= "AND ch.id_ag = cpt.id_ag AND cpt.id_ag = cli.id_ag AND ch.id_ag = $global_id_agence ";

  if(!empty($num_client)) {
    $sql .= " AND id_client = $num_client ";
  }
  if(isFusion()) {
      if (!empty($n_agence)) {
          $sql .= " AND cli.n_agence  = $n_agence ";
      }
      if (!empty($n_guichet)) {
          $sql .= " AND cli.n_guichet = $n_guichet ";
      }
  }

  if($isRapportCheqCmd) {
    $sql .= " ORDER BY ch.date_cmde, ch.id ASC; ";
  }
  else {
    $sql .= " ORDER BY row_counter, ch.date_envoi_impr, ch.id ASC; ";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $DATAS = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $row['date_cmde'] = pg2phpDate($row['date_cmde']);
    $row['date_envoi_impr'] = pg2phpDate($row['date_envoi_impr']);
    $DATAS['cmd_chequiers_data'][] = $row;
  }

  // Recap par etat chequier
  $criteres = array('date_debut' => $date_debut, 'date_fin' => $date_fin);
  if(isFusion()){
      $DATAS['syntheses_par_etat_cmd'] = getNombreChquiersParEtatCommande($criteres,$n_agence,$n_guichet);
  }else{
      $DATAS['syntheses_par_etat_cmd'] = getNombreChquiersParEtatCommande($criteres);
  }


  $dbHandler->closeConnection(true);
  return $DATAS;
}


/**
 * Regroupe le nombre des chequiers par etat chequier. Utile pour les rapports chequiers
 * @return array
 */
function getNombreChequiersParEtat($criteres=null,$n_agence=null, $n_guichet=null)
{
  global $dbHandler, $adsys, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT c.etat_chequier, count(c.*) as nb_chequier FROM ad_chequier c INNER JOIN ad_cpt p on p.id_cpte = c.id_cpte INNER JOIN ad_cli i on i.id_client = p.id_titulaire WHERE c.id_ag = $global_id_agence ";

  if ($n_agence != null){
    $sql .= " AND i.n_agence = $n_agence";
  }
    if ($n_guichet != null){
        $sql .= " AND i.n_guichet = $n_guichet";
    }

  if($criteres !== null) {
    if(array_key_exists("champ_date", $criteres) && array_key_exists("date_debut", $criteres) && array_key_exists("date_fin", $criteres)) {
      $sql .= " AND " . $criteres['champ_date'] . " BETWEEN date('" . $criteres['date_debut'] . "') AND  date('" . $criteres['date_fin'] . "') ";
    }
    if(array_key_exists("etat_chequier", $criteres)) {
      $sql .= " AND c.etat_chequier = " . $criteres['etat_chequier'];
    }
  }

  $sql .= " GROUP BY c.etat_chequier ORDER BY c.etat_chequier;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $DATAS = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $row['libel_etat'] = $adsys['adsys_etat_chequier'][$row['etat_chequier']];
    $DATAS[$row['etat_chequier']] = $row;
  }

  $dbHandler->closeConnection(true);
  return $DATAS;
}


/**
 * Regroupe le nombre des chequiers par etat chequier. Utile pour les rapports chequiers
 * @return array
 */
function getNombreChequesEnOpposition($criteres = null)
{
  global $dbHandler, $adsys, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT count(*) as nb_cheques FROM ad_cheque WHERE id_ag = $global_id_agence AND is_opposition = 't'";

  if($criteres !== null && array_key_exists("date_debut", $criteres) && array_key_exists("date_fin", $criteres)) {
    $sql .= " AND date_opposition BETWEEN date('" . $criteres['date_debut'] . "') AND  date('" . $criteres['date_fin'] . "') ";
  }

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

//  $DATAS = array();
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
//  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
//    $DATAS[$row['etat_chequier']] = $row;
//  }

  $dbHandler->closeConnection(true);
  return $row;
}

/**
 * Regroupe le nombre des chequiers par etat du commande du chequier. Utile pour les rapports chequiers
 * @return array
 */
function getNombreChquiersParEtatCommande($criteres = null,$n_agence=null,$n_guichet=null)
{
  global $dbHandler, $adsys, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT r.etat, count(r.*) as nb_chequier FROM ad_commande_chequier r INNER JOIN ad_cpt t ON t.id_cpte = r.id_cpte INNER JOIN ad_cli c on c.id_client = t.id_titulaire WHERE r.id_ag = $global_id_agence";

  if ($n_agence !=null){
    $sql .= " AND c.n_agence = $n_agence";
  }
  if ($n_guichet !=null){
      $sql .= " AND c.n_guichet = $n_guichet";
  }

  if($criteres !== null && array_key_exists("date_debut", $criteres) && array_key_exists("date_fin", $criteres)) {
    $sql .= " AND r.date_cmde BETWEEN date('" . $criteres['date_debut'] . "') AND  date('" . $criteres['date_fin'] . "') ";
  }

  $sql .= " GROUP BY r.etat ORDER BY etat;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $DATAS = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $row['libel_etat_cmd'] = $adsys['adsys_etat_commande_chequier'][$row['etat']];
    $DATAS[$row['etat']] = $row;
  }

  $dbHandler->closeConnection(true);
  return $DATAS;
}

/**
 * Récupère les données du rapport des intérêts à payer
 * @param $criteres_recherche
 * @return array
 */
function getRapportCalcIntPayeData($criteres_recherche)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $id_prod_epg = $criteres_recherche['id_prod_epg'];
  $date_rapport = $criteres_recherche['date_rapport'];

  $sql = "SELECT h.id_cpte, c.id_prod, p.libel as prod_name, c.devise, c.id_titulaire,
          h.solde_compte AS solde, c.num_complet_cpte, date(c.date_ouvert) as date_ouvert,
          date(c.dat_date_fin) as dat_date_fin, h.max_nb_jours_echus,
          (SELECT CASE statut_juridique
          WHEN 1 THEN pp_nom||' '||pp_prenom
          WHEN 2 THEN pm_raison_sociale
          WHEN 3 THEN gi_nom
          WHEN 4 THEN gi_nom END) AS nom_client,
          h.max_nb_jours_echus,
          h.int_net_a_payer AS tot_montant_int
          FROM (
          SELECT
          a.id_ag,
          a.id_cpte,
          calculsoldecpte(a.id_cpte, NULL, date('$date_rapport')) as solde_compte,
          max(nb_jours_echus) as max_nb_jours_echus,
          sum(a.montant_int  - COALESCE(rep.int_repris,0)) as int_net_a_payer
          FROM ad_calc_int_paye_his  a
          LEFT JOIN (SELECT id_ag, id_cpte, COALESCE(montant_int,0) as int_repris FROM ad_calc_int_paye_his WHERE date_reprise <= date('$date_rapport')) rep
           ON a.id_cpte = rep.id_cpte AND a.id_ag = rep.id_ag
           WHERE a.date_calc <= date('$date_rapport')
           GROUP BY  a.id_ag, a.id_cpte
          )
          h
          INNER JOIN ad_cpt c ON h.id_cpte = c.id_cpte
          INNER JOIN ad_cli cli ON c.id_titulaire = cli.id_client
          INNER JOIN adsys_produit_epargne p ON c.id_prod = p.id
          AND h.int_net_a_payer > 0 ";

  if(!is_null($id_prod_epg) && $id_prod_epg > 0) {
    $sql .= " AND c.id_prod = $id_prod_epg ";
  }

  $sql .= " AND h.id_ag = c.id_ag AND c.id_ag = cli.id_ag AND p.id_ag = c.id_ag AND c.id_ag = numagc()
            ORDER BY id_prod, h.id_cpte;";
   $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $DATAS = array();
  $total_mnt_int_prod = 0;
  $total_int_paye = 0;
  $id_prod_current = 0;

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    if($id_prod_current == 0) {
      $id_prod_current = $row['id_prod'];
    }
    else
     if($id_prod_current != $row['id_prod']) {
       $id_prod_current = $row['id_prod'];
       $total_mnt_int_prod = 0;
     }

    $DATAS['details'][$row['id_prod']][$row['id_cpte']] = $row;
    $total_mnt_int_prod += $row['tot_montant_int'];
    $DATAS['details'][$row['id_prod']]['montant_int_prod'] = $total_mnt_int_prod;
    $DATAS['details'][$row['id_prod']]['prod_name'] = $row['prod_name'];
    $total_int_paye += $row['tot_montant_int'];
  }
  $DATAS['total_int_paye'] = $total_int_paye;
  $dbHandler->closeConnection(true);
  return $DATAS;

}


/**
 *
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport inventaire des dépots
 *
 * @param array $where les filtres de la requete
 *
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */
function get_rapport_inventaire_credit($where,$date_deb,$date_fin,$etat=NULL){

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $idEtatPerte = getIDEtatPerte();

  //données du fonction getinventairecredits pour ramener les dossiers credits encours, radiés au cours de la periode et certain soldés au cours de la periode
  //Parametre etat dossier credit : 1 ENCOURS, 2 RADIE, 3 SOLDE et 4 TOUS
  $etat_selecte = $etat;
  if ($etat == null){
    $etat_selecte = 4;
  }
  $sql = "SELECT A.* FROM getinventairecreditsprincipale(date('$date_deb'), date('$date_fin'), $idEtatPerte, $etat_selecte) A";

  //si etat encours est selecté
  if ($etat == 1){
    $sql .= " WHERE A.type_rapport = '1-ENCOURS'";
    if(isset($where)) {
      foreach ($where as $key => $valeur) {
        $sql .= " AND $key='$valeur' ";
      }
    }
  }
  //si etat soldé est selecté
  if ($etat == 3){
    //$sql = "SELECT A.* FROM (".$sql." UNION ".$sql_DossierSolde.") A";
    $sql .= " WHERE A.type_rapport = '2-SOLDE'";
    if(isset($where)) {
      foreach ($where as $key => $valeur) {
        $sql .= " AND $key='$valeur' ";
      }
    }
  }
  //si etat radié est selecté
  if ($etat == 2){
    $sql .= " WHERE A.type_rapport = '4-RADIE' OR (A.type_rapport = '2-SOLDE' AND date(A.cre_date_etat) >= date('$date_deb') AND A.perte_capital > 0)";
    if(isset($where)) {
      foreach ($where as $key => $valeur) {
        $sql .= " AND $key='$valeur' ";
      }
    }
  }
  //si etat tous est selecté
  if ($etat == null){
    //$sql = "SELECT A.* FROM (".$sql." UNION ".$sql_DossierSolde.") A";
    if(isset($where)) {
      foreach ($where as $key => $valeur) {
        $sql .= " WHERE $key='$valeur' ";
      }
    }
  }

  $sql .= " ORDER BY A.id_prod ASC, A.id_doss ASC";


  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__ .$sql);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $credit[$row['id_prod']][$row['id_doss']] = $row;
  }

  $dbHandler->closeConnection(true);
  return $credit;
}

/**
 * Récupère les données du rapport des intérêts à payer
 * @param $criteres_recherche
 * @return array
 */
function  getRapportCalcIntRecevoirData($criteres_recherche)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $id_prod_crdt = $criteres_recherche['id_prod_crdt'];
  $date_rapport = $criteres_recherche['date_rapport'];

  $sql = "select * from getiarview('$date_rapport', numagc())";
  if(!is_null($id_prod_crdt) && $id_prod_crdt > 0){
    $sql .= " where id_prod = $id_prod_crdt;";
  }


  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $DATAS = array();
  $id_prod_current = 0;
  $total_mnt_int_crdt = 0;
  $total_cap_restant_du = 0;
  $total_int_attendu_ech =0;
  $total_iar_echeance = 0;
  $total_int_non_paye = 0;
  $total_int_recevoir = 0;

  $count = 0;
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $count++;
    $id_prod_current = $row['id_prod'];

    if($id_prod_current == 0) {
      $id_prod_current = $row['id_prod'];
    }
    else {
      if ($id_prod_current != $row['id_prod']) {
        $id_prod_current = $row['id_prod'];
        $total_mnt_int_crdt = 0;
      }

    }
    //$DATAS['details'][$row['id_prod']][$row['id_doss']] = $row; //print_rn($DATAS['details'][$row['id_prod']][$row['id_doss']]);
    $DATAS['details'][$row['id_doss']][$row['id_client']] = $row;
    $DATAS['details'][$row['id_doss']]['montant'] = $total_mnt_int_crdt;
    $DATAS['details'][$row['id_doss']]['libel'] = $row['libel'];
    $total_mnt_int_crdt += $row['montant_cumul'];
    $total_cap_restant_du += $row['cap_restant_du'];
    $total_int_attendu_ech += $row['solde_int_ech'];
    $total_iar_echeance += $row['montant'];
    $total_int_non_paye += $row['montant_prec'];
    $total_int_recevoir += $row['montant_cumul'];

  }

  $DATAS['total_cap_restant_du'] = $total_cap_restant_du;
  $DATAS['total_int_attendu_ech'] = $total_int_attendu_ech;
  $DATAS['total_iar_echeance'] = $total_iar_echeance;
  $DATAS['total_int_non_paye'] = $total_int_non_paye;
  $DATAS['total_int_recevoir'] = $total_int_recevoir;
  $dbHandler->closeConnection(true);
  return $DATAS;

}

/**
 *
 * Genere le rapport BIC / BCEAO : trac #774
 * @param $date_rapport
 */
function generateRapportBCEAO($date_rapport) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $filepath = '/tmp/rapport_bic.xml';
  $user = 'apache';

  if(file_exists($filepath)) {
    file_put_contents($filepath, null);
  }
  else {
    touch($filepath);
  }

  chown($filepath, $user);
  chgrp($filepath, $user);
  chmod($filepath, 0777);

  $date_rapport = php2pg($date_rapport);

  $sql = "SELECT * FROM rapport_bic_file(date('$date_rapport'));";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $dbHandler->closeConnection(true);
  return true;

}


function get_rapport_statistique_operationnelle_data($info_ad = false, $info_ep = false ,$info_cr = false, $DATA_EMP,$date_deb,$date_fin)
{
  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();
$DATA_TOTAL = array();
  if (sizeof($DATA_EMP) == 1){
    while (list ($key, $value) = each($DATA_EMP)){
      $condi_emp = " cli.pp_partenaire = ".$DATA_EMP[$key]['id']." ";
      $condi_emp2 = $DATA_EMP[$key]['id'];
    }
  }

  if ($info_ad == true){
    $sql_ad = "select id_emp, employeur, cible, sum(nombre) as nombre, sum(Actif) as Actif from (
              SELECT
              emp.id as id_emp,
              emp.nom as employeur,
              emp.cible as cible,
              count(cli.id_client) as nombre,
              count(z.id_client) as Actif
              FROM adsys_employeur emp
              INNER JOIN ad_cli cli on cli.pp_partenaire = emp.id
              LEFT JOIN
              (select distinct id_titulaire as id_client
              from ad_cpt cpt
              INNER JOIN adsys_produit_epargne p ON p.id = cpt.id_prod
              WHERE  cpt.solde >0 AND cpt.etat_cpte <> 2 AND p.classe_comptable in (1,2,5)
              ) z ON z.id_client = cli.id_client ";
    if (sizeof($DATA_EMP) == 1){
        $sql_ad .= " WHERE $condi_emp ";
    }
    $sql_ad .="group by emp.id, emp.nom,cible, cli.etat) A
     group by id_emp, employeur, cible
     order by id_emp";
    $result_ad = $db->query($sql_ad);

    if (DB::isError($result_ad)) {
      $dbHandler->closeConnection(false);
      Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result_ad->getMessage());
    }

    if ($result_ad->numRows() == 0)
    {
      $dbHandler->closeConnection(true);
      return NULL;
    }
    $i=1;
    $DATAS_AD=array();
    while ( $row = $result_ad->fetchRow(DB_FETCHMODE_ASSOC) ) {
      $DATAS_AD[$i]["id_emp"] = $row["id_emp"];
      $DATAS_AD[$i]["employeur"] = $row['employeur'];
      $DATAS_AD[$i]['cible'] = $DATA_EMP[$row['id_emp']]['nouvelle_cible'];
      $DATAS_AD[$i]['nombre'] = $row['nombre'];
      $DATAS_AD[$i]['actif'] = $row['actif'];
      $DATAS_AD[$i]['prc_nbre'] = round($row['nombre'] /$DATA_EMP[$row['id_emp']]['nouvelle_cible']*100,2 );
      $DATAS_AD[$i]['prc_actif'] = round($row['actif'] / $row['nombre'] *100,2);
      $i++;
    }
  }
  if(sizeof($DATAS_AD)>0) {
    $DATA_TOTAL[]["adhesion"] = $DATAS_AD;
  }

  if ($info_cr == true){

    $sql_cr = "select id, employeur, sum(nombre_octroi) as nbre_octroi,sum(mnt_octroi) as mnt_octroi, sum(nombre_remb) as nbre_remb, sum(mnt_remb) as mnt_remb,sum(nombre_encours) as nbre_encours ,sum(mnt_encours) as mnt_encours from (
select id, employeur, case when cat = 'octroi' then sum(nbre) else 0 end as nombre_octroi, case when cat = 'octroi' then sum(mnt) else 0 end as mnt_octroi,
case when cat = 'remb' then sum(nbre) else 0 end as nombre_remb, case when cat = 'remb' then sum(mnt) else 0 end as mnt_remb,
case when cat = 'encours' then sum(nbre) else 0 end as nombre_encours, case when cat = 'encours' then sum(mnt) else 0 end as mnt_encours
from (
select 'octroi' as cat,emp.id, emp.nom as employeur, COUNT(dcr.id_client) as nbre, sum(dcr.cre_mnt_octr) as mnt
FROM adsys_employeur emp
INNER JOIN ad_cli cli ON cli.pp_partenaire = emp.id
INNER JOIN ad_dcr dcr ON dcr.id_client = cli.id_client
WHERE dcr.cre_date_debloc between date('$date_deb') and date('$date_fin')  ";
    if (sizeof($DATA_EMP) == 1){
        $sql_cr .= "and ".$condi_emp;

    }
$sql_cr .="group by emp.id, emp.nom

          union

          select 'remb' as cat,emp.id, emp.nom as employeur, count(distinct sre.id_doss) as nbre,
          sum(mnt_remb_cap) + sum(mnt_remb_int) + sum(mnt_remb_gar) + sum(mnt_remb_pen) AS mnt
          FROM adsys_employeur emp
          INNER JOIN ad_cli cli ON cli.pp_partenaire = emp.id
          INNER JOIN ad_dcr dcr ON dcr.id_client = cli.id_client
          INNER JOIN ad_sre sre ON sre.id_doss = dcr.id_doss
          WHERE
          sre.date_remb between date('$date_deb') and date('$date_fin')  ";
    if (sizeof($DATA_EMP) == 1){
        $sql_cr .= "and ".$condi_emp;

    }
$sql_cr .=" group by emp.id, emp.nom

            union


SELECT 'encours' as cat,emp.id, emp.nom as employeur,count(distinct etr.id_doss) as nbre,
sum(etr.solde_cap) AS mnt
from adsys_employeur emp
INNER JOIN ad_cli cli ON cli.pp_partenaire = emp.id
INNER JOIN ad_dcr dcr ON dcr.id_client = cli.id_client
INNER JOIN ad_etr etr ON etr.id_doss = dcr.id_doss
 ";
if (sizeof($DATA_EMP) == 1){
    $sql_cr .= "where ".$condi_emp;
}
    else {
      $sql_cr .= "where cli.pp_partenaire is not null";
    }
$sql_cr .= " group by emp.id, emp.nom

            ) A
            group by id,employeur, a.cat)
            B
            group by id,employeur
            order by id";

    $result_cr = $db->query($sql_cr);

    if (DB::isError($result_cr)) {
      $dbHandler->closeConnection(false);
      Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result_cr->getMessage());
    }

    if ($result_cr->numRows() == 0)
    {
      $dbHandler->closeConnection(true);
      return NULL;
    }

    $i=1;
    $DATAS_CR=array();
    while ( $row_cr = $result_cr->fetchRow(DB_FETCHMODE_ASSOC) ) {
      $DATAS_CR[$i]["id_emp"] = $row_cr["id"];
      $DATAS_CR[$i]["employeur"] = $row_cr['employeur'];
      $DATAS_CR[$i]['nbre_octroi'] = $row_cr['nbre_octroi'];
      $DATAS_CR[$i]['mnt_octroi'] = $row_cr['mnt_octroi'];
      $DATAS_CR[$i]['nbre_remb'] = $row_cr['nbre_remb'];
      $DATAS_CR[$i]['mnt_remb'] = $row_cr['mnt_remb'];
      $DATAS_CR[$i]['nbre_encours'] = $row_cr['nbre_encours'];
      $DATAS_CR[$i]['mnt_encours'] = $row_cr['mnt_encours'];
      $i++;
    }
  }
  if(sizeof($DATAS_CR)>0) {
    $DATA_TOTAL[]["credit"] = $DATAS_CR;
  }

  if ($info_ep == true){

    $sql_temp_depot = "    CREATE TEMP TABLE depots as SELECT count(cpte_interne_cli), sum(montant),pp_partenaire, libel_ecriture
from ad_mouvement a, ad_cpt , ad_cli, ad_ecriture b where a.id_ecriture = b.id_ecriture and cpte_interne_cli = id_cpte
and id_titulaire = id_client and sens = 'c' and date_comptable>= date('$date_deb') and date_comptable <= date('$date_fin')   and id_prod<> 3  and pp_partenaire is not null group by pp_partenaire, libel_ecriture;
";
    $result_temp_depot = $db->query($sql_temp_depot);

    if (DB::isError($result_temp_depot)) {
      $dbHandler->closeConnection(false);
      Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result_temp_depot->getMessage());
    }
      $sql_temp_retrait = "CREATE TEMP TABLE retraits as SELECT count(cpte_interne_cli), sum(montant),pp_partenaire, libel_ecriture
from ad_mouvement a, ad_cpt , ad_cli, ad_ecriture b where a.id_ecriture = b.id_ecriture and cpte_interne_cli = id_cpte
and id_titulaire = id_client and sens = 'd' and date_comptable >= date('$date_deb')  and date_comptable <= date('$date_fin')  and id_prod<> 3  and pp_partenaire is not null group by pp_partenaire, libel_ecriture;
";
    $result_temp_retrait = $db->query($sql_temp_retrait);

    if (DB::isError($result_temp_retrait)) {
      $dbHandler->closeConnection(false);
      Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result_temp_retrait->getMessage());
    }
    $sql_ep = "
select employeur, sum(nbre_cli_dep) as nb_depot,sum(mnt_depot) as mnt_depot, sum(nombre_cli_ret) as nb_retrait, sum(mnt_retrait) as mnt_retrait, sum(nombre_cli_enc) as nb_encours, sum(mnt_encours) as mnt_encours from (
select employeur, case when cat = 'Depots' then sum(nbr_cli) else 0 end as nbre_cli_dep, case when cat = 'Depots' then sum(montant) else 0 end as mnt_depot,
case when cat = 'Retraits' then sum(nbr_cli) else 0 end as nombre_cli_ret, case when cat = 'Retraits' then sum(montant) else 0 end as mnt_retrait,
case when cat = 'Encours' then sum(nbr_cli) else 0 end as nombre_cli_enc, case when cat = 'Encours' then sum(montant) else 0 end as mnt_encours
from (


SELECT   distinct 'Depots' as cat, sum(count) as nbr_cli, emp.nom as employeur,sum(sum) as montant
from  depots d
INNER JOIN adsys_employeur emp ON emp.id = d.pp_partenaire";

    if (sizeof($DATA_EMP) == 1){
      $sql_ep .= " and d.pp_partenaire = ".$condi_emp2;

    }
$sql_ep .= " group by emp.nom


union

SELECT  distinct 'Retraits' as cat, sum(count) as nbr_cli, emp.nom as employeur, sum(sum) as montant
from  retraits r
INNER JOIN adsys_employeur emp ON emp.id = r.pp_partenaire";
    if (sizeof($DATA_EMP) == 1){
      $sql_ep .= " and r.pp_partenaire = ".$condi_emp2;

    }
$sql_ep .= " group by nom

union

SELECT distinct 'Encours' as cat, count(cli.id_client) as nbr_cli,nom as employeur, sum(solde) as montant from adsys_employeur emp
INNER JOIN ad_cli cli ON cli.pp_partenaire = emp.id
INNER JOIN ad_cpt cpt ON cpt.id_titulaire = cli.id_client
where cpt.id_prod<> 3 ";
    if (sizeof($DATA_EMP) == 1){
      $sql_ep .= "and emp.id = ".$condi_emp2;

    }
$sql_ep .= " group by nom
) A
group by employeur,cat)
 B
group by employeur";

    $result_ep = $db->query($sql_ep);

    if (DB::isError($result_ep)) {
      $dbHandler->closeConnection(false);
      Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result_ep->getMessage());
    }

    if ($result_ep->numRows() == 0)
    {
      $dbHandler->closeConnection(true);
      return NULL;
    }
    $i=1;
    $DATAS_EP=array();
    while ( $row_ep = $result_ep->fetchRow(DB_FETCHMODE_ASSOC) ) {
      //$DATAS_EP[$i]["id_emp"] = $row["id"];
      $DATAS_EP[$i]["employeur"] = $row_ep['employeur'];
      $DATAS_EP[$i]['nbre_depot'] = $row_ep['nb_depot'];
      $DATAS_EP[$i]['mnt_depot'] = $row_ep['mnt_depot'];
      $DATAS_EP[$i]['nbre_retrait'] = $row_ep['nb_retrait'];
      $DATAS_EP[$i]['mnt_retrait'] = $row_ep['mnt_retrait'];
      $DATAS_EP[$i]['nbre_encours'] = $row_ep['nb_encours'];
      $DATAS_EP[$i]['mnt_encours'] = $row_ep['mnt_encours'];
      $i++;
    }
    if(sizeof($DATAS_EP)>0) {
      $DATA_TOTAL[]["epargne"] = $DATAS_EP;
    }
  }
  return $DATA_TOTAL;
}


/**
 * Fonction getDebutMois() pour recupere la date debut du mois courant
 * PARAM : $date date du jour
 * RETURN date
 */
function getDebutMois($dateDuJour){
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM getdebutmois(date('$dateDuJour'));";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $date_row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $dbHandler->closeConnection(true);
  return $date_row['getdebutmois'];
}

/**
 * Fonction getFinMois() pour recupere la date fin du mois courant
 * PARAM : $date date du jour
 * RETURN date
 */
function getFinMois($dateDuJour){
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM getfinmois(date('$dateDuJour'));";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $date_row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $dbHandler->closeConnection(true);
  return $date_row['getfinmois'];
}

/**
 * Fonction getListEmployeurs() pour recupere la liste des employeurs
 * PARAM : none
 * RETURN array of data : list employeur
 */
function getListEmployeurs(){
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id, sigle FROM adsys_employeur WHERE id_ag = numagc()";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $listEmp = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $listEmp[$row['id']]=$row['sigle'];
  }

  $dbHandler->closeConnection(true);
  return $listEmp;
}

/**
 * Fonction getListAdhesionsDuMois() pour recupere la Liste des adhésions du mois
 * PARAM : $id_emp default null, date debut et date fin
 * RETURN array of data : list employeur
 */
function getListAdhesionsDuMois($id_emp=null,$empSigle,$dateDebut,$dateFin){
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $date_debut = php2pg($dateDebut);
  $date_fin = php2pg($dateFin);

  if ($empSigle == 'SOD'){
    $sql = "SELECT '000000000'||SUBSTRING(b.sigle FROM 1 FOR 3)||rpad(a.matricule,7,' ') AS col1_value, '                         *FZYEL                       ' AS col2_value, '0001681C'||REPLACE(TO_CHAR(row_number() over (), '999999'),' ','0')||DATE('$date_debut')||DATE('$date_fin')  AS col3_value, ' M +000000000000      +'||REPLACE(TO_CHAR(CASE WHEN a.statut_juridique = 1 THEN COALESCE(ag.pp_montant_droits_adhesion,0) WHEN a.statut_juridique = 2 THEN COALESCE(ag.pm_montant_droits_adhesion,0) WHEN a.statut_juridique = 3 THEN COALESCE(ag.gi_montant_droits_adhesion,0) WHEN a.statut_juridique = 4 THEN COALESCE(ag.gs_montant_droits_adhesion,0) ELSE 0 END, '99999999'), ' ', '0')||'000      +000000000000      00                                                                                                                                       ' AS col4_value
    FROM ad_cli a
    INNER JOIN adsys_employeur b ON a.pp_partenaire = b.id
    INNER JOIN ad_cpt c ON a.id_client = c.id_titulaire
    INNER JOIN ad_agc ag ON c.id_ag = ag.id_ag
    WHERE a.etat = 1 AND c.etat_cpte = 1 AND c.id_prod = 1 AND DATE(a.date_adh) BETWEEN DATE('$date_debut') AND DATE('$date_fin')";
  }
  else{
    $sql = "SELECT '000000000'||SUBSTRING(b.sigle FROM 1 FOR 3)||rpad(a.matricule,7,' ') AS col1_value,'                         *FZY90                       ' AS col2_value,'0' AS col3_value,'                       75D' AS col4_value, '+'||'00000000001' AS col5_value, '        AM' AS col6_value, ' +000000000000' AS col7_value, '     X+'||REPLACE(TO_CHAR(CASE WHEN a.statut_juridique = 1 THEN COALESCE(ag.pp_montant_droits_adhesion,0) WHEN a.statut_juridique = 2 THEN COALESCE(ag.pm_montant_droits_adhesion,0) WHEN a.statut_juridique = 3 THEN COALESCE(ag.gi_montant_droits_adhesion,0) WHEN a.statut_juridique = 4 THEN COALESCE(ag.gs_montant_droits_adhesion,0) ELSE 0 END, '99999999'), ' ', '0')||'000' AS col8_value, '      +000000000000' AS col9_value, '                                                                                       00                                                                                                                  1' AS col10_value
    FROM ad_cli a
    INNER JOIN adsys_employeur b ON a.pp_partenaire = b.id
    INNER JOIN ad_cpt c ON a.id_client = c.id_titulaire
    INNER JOIN ad_agc ag ON c.id_ag = ag.id_ag
    WHERE a.etat = 1 AND c.etat_cpte = 1 AND c.id_prod = 1 AND DATE(a.date_adh) BETWEEN DATE('$date_debut') AND DATE('$date_fin')"; //TO_CHAR(DATE(a.date_adh),'YYYY')||
  }
  if ($id_emp != null){
    $sql .= " AND b.id = ".$id_emp;
  }
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $listAdh = array();
  $count = 0;
  $nombreCols = $result->numCols();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $count ++;
    $listAdh[$count]=$row;
  }
  $listAdh['nbrCols'] = $nombreCols;

  $dbHandler->closeConnection(true);
  return $listAdh;
}

/**
 * Fonction creation fichiers txt pour le rapport agence generation interface
 * PARAM : category txt(AD, PS, NE, NP), employeur id, sigle, date debut et date fin
 * session path : chemin pour les txt
 * RETURN : array of strings
 */
function createFichierTxt($cat_txt, $emp, $emp_sigle, $path_txt, $date_debut, $date_fin){
  $data = array();
  $txt =array();
  $no_data_msg = '';

  if ($cat_txt == 'AD'){
    $data = getListAdhesionsDuMois($emp,$emp_sigle,$date_debut,$date_fin);
    $no_data_msg = _('Liste des Adhesions du mois');
  }
  if ($cat_txt == 'PS'){
    $data = getListPSDuMois($emp,$emp_sigle,$date_debut,$date_fin);
    $no_data_msg = _('Liste des Parts Sociales du mois');
  }
  if ($cat_txt == 'NE'){
    $data = getListEpargneDuMois($emp,$emp_sigle,$date_debut,$date_fin);
    $no_data_msg = _('Liste des Epargnes du mois');
  }
  if ($cat_txt == 'NP'){
    $data = getListPretDuMois($emp,$emp_sigle,$date_debut,$date_fin);
    $no_data_msg = _('Liste des Prets du mois');
  }

  $nombre_colonnes = $data['nbrCols']+1;

  if(sizeof($data)>1){
    $file_handle = fopen($path_txt."/".$cat_txt."_".$emp_sigle."_".date('ymd').".txt","w");
    $txt_path = $path_txt."/".$cat_txt."_".$emp_sigle."_".date('ymd').".txt";
    $txt_name = $cat_txt."_".$emp_sigle."_".date('ymd').".txt";
    $txt['path'] = $txt_path;
    $txt['name'] = $txt_name;
    foreach($data as $key => $value){
      $content = '';
      //if($key != 'nbrCols'){
        //$content = $key;
      //}
      for($i=1;$i<=$nombre_colonnes;$i++){
        $content .= $value['col'.$i.'_value'];
      }
      fwrite($file_handle, $content."\r\n");
    }
    fclose($file_handle);
  }
  else{
    $txt['no_data'] = $no_data_msg;
  }

  return $txt;
}

/**
 * Fonction getListPSDuMois() pour recupere la Liste des part sociales du mois
 * PARAM : $id_emp default null, date debut et date fin
 * RETURN array of data : list PS
 */
function getListPSDuMois($id_emp=null,$empSigle,$dateDebut,$dateFin){
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $date_debut = php2pg($dateDebut);
  $date_fin = php2pg($dateFin);

  if ($empSigle != 'SOD'){
    $sql = "SELECT '000000000'||SUBSTRING(b.sigle FROM 1 FOR 3)||rpad(a.matricule,7,' ') AS col1_value,'                         *FZY90                       ' AS col2_value,'0' AS col3_value,'                       75E' AS col4_value, '+'||'00000000001' AS col5_value, '        AM' AS col6_value, ' +000000000000' AS col7_value, '     X+'||REPLACE(TO_CHAR(COALESCE(ag.val_nominale_part_sociale,0), '99999999'), ' ', '0')||'000' AS col8_value, '      +000000000000' AS col9_value, '                                                                                       00                                                                                                                  1' AS col10_value
FROM ad_cli a
INNER JOIN adsys_employeur b ON a.pp_partenaire = b.id
INNER JOIN ad_cpt c ON a.id_client = c.id_titulaire
INNER JOIN ad_agc ag ON ag.id_ag = a.id_ag
WHERE c.etat_cpte = 1 AND c.id_prod = 1 AND DATE(a.date_adh) BETWEEN DATE('$date_debut') AND DATE('$date_fin')";
  }
  else{
    $sql = "SELECT '000000000'||SUBSTRING(b.sigle FROM 1 FOR 3)||rpad(a.matricule,7,' ') AS col1_value, '                         *FZYEL                       ' AS col2_value, '0001680C'||REPLACE(TO_CHAR(row_number() over (), '999999'),' ','0')||DATE('$date_debut')||DATE('$date_fin')  AS col3_value, ' M +000000000000      +'||REPLACE(TO_CHAR(COALESCE(ag.val_nominale_part_sociale,0), '99999999'), ' ', '0')||'000      +000000000000      00                                                                                                                                       ' AS col4_value
FROM ad_cli a
INNER JOIN adsys_employeur b ON a.pp_partenaire = b.id
INNER JOIN ad_cpt c ON a.id_client = c.id_titulaire
INNER JOIN ad_agc ag ON ag.id_ag = a.id_ag
WHERE c.etat_cpte = 1 AND c.id_prod = 1 AND DATE(a.date_adh) BETWEEN DATE('$date_debut') AND DATE('$date_fin')";
  }
  if ($id_emp != null){
    $sql .= " AND b.id = ".$id_emp;
  }
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $listPS = array();
  $count = 0;
  $nombreCols = $result->numCols();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $count ++;
    $listPS[$count]=$row;
  }
  $listPS['nbrCols'] = $nombreCols;

  $dbHandler->closeConnection(true);
  return $listPS;
}

/**
 * Fonction getListEpargneDuMois() pour recupere la Liste des epargnes du mois
 * PARAM : $id_emp default null, date debut et date fin
 * RETURN array of data : list Epargne
 */
function getListEpargneDuMois($id_emp=null,$empSigle,$dateDebut,$dateFin){
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $date_debut = php2pg($dateDebut);
  $date_fin = php2pg($dateFin);

  if ($empSigle != 'SOD'){
    $sql = "SELECT '000000000'||SUBSTRING(b.sigle FROM 1 FOR 3)||rpad(a.matricule,7,' ') AS col1_value,'                         *FZYPR                       ' AS col2_value,'000174701EG'||REPLACE(TO_CHAR(c.cpt_from, '99999999'),' ','0') AS col3_value,'         ' AS col4_value, '+'||REPLACE(TO_CHAR(ROUND((c.montant*c.nb_periode)), '99999999'),' ','0')||'XOF0+000000000XOF0'||DATE(c.date_prem_exe)||DATE(c.date_fin) AS col5_value, '  00 00000           000000' AS col6_value, '             +'||lpad(cast(c.nb_periode as text),3, '0')||'+'||REPLACE(TO_CHAR(ROUND(c.montant), '999999'),' ','0')||'XOF0+'||REPLACE(TO_CHAR(ROUND((c.montant*c.nb_periode)), '99999999'),' ','0')||'XOF0' AS col7_value, '      +00000+000000000XOF0' AS col8_value, '                    +000000000XOF0+000000000XOF0' AS col9_value, '               +000' AS col10_value, '                                                       +000000000000000XOF0' AS col11_value, '                          +0000000XOF0                                                  ' AS col12_value
  FROM ad_cli a INNER JOIN adsys_employeur b ON a.pp_partenaire = b.id INNER JOIN ad_cpt d ON a.id_client  = d.id_titulaire INNER JOIN ad_ord_perm c ON c.cpt_from = d.id_cpte
  WHERE c.type_transfert IN (1,2) AND c.date_prem_exe BETWEEN DATE('$date_debut') AND  DATE('$date_fin')"; //TO_CHAR(DATE(c.date_prem_exe),'YYYY')||
  }
  else{
    $sql = "SELECT '000000000'||SUBSTRING(b.sigle FROM 1 FOR 3)||rpad(a.matricule,7,' ') AS col1_value,'                         *FZYEL                       ' AS col2_value, '0001606C'||REPLACE(TO_CHAR(row_number() over (), '999999'),' ','0')||DATE(c.date_prem_exe)||DATE(c.date_fin) AS col3_value, ' M +000000000000      +' AS col4_value, REPLACE(TO_CHAR(ROUND(c.montant), '99999999'),' ','0')||'000      +000000000000      00                                                                                                                                       ' AS col5_value
  FROM ad_cli a INNER JOIN adsys_employeur b ON a.pp_partenaire = b.id INNER JOIN ad_cpt d ON a.id_client  = d.id_titulaire INNER JOIN ad_ord_perm c ON c.cpt_from = d.id_cpte
  WHERE c.type_transfert IN (1,2) AND c.date_prem_exe BETWEEN DATE('$date_debut') AND  DATE('$date_fin')";
  }
  if ($id_emp != null){
    $sql .= " AND b.id = ".$id_emp;
  }
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $listEpargne = array();
  $count = 0;
  $nombreCols = $result->numCols();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $count ++;
    $listEpargne[$count]=$row;
  }
  $listEpargne['nbrCols'] = $nombreCols;

  $dbHandler->closeConnection(true);
  return $listEpargne;
}

/**
 * Fonction getListPretDuMois() pour recupere la Liste des prets du mois
 * PARAM : $id_emp default null, date debut et date fin
 * RETURN array of data : list Prets
 */
function getListPretDuMois($id_emp=null,$empSigle,$dateDebut,$dateFin){
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $date_debut = php2pg($dateDebut);
  $date_fin = php2pg($dateFin);

  if ($empSigle != 'SOD'){
    $sql = "SELECT '000000000'||SUBSTRING(b.sigle FROM 1 FOR 3)||rpad(a.matricule,7,' ') AS col1_value,'                         *FZYPR                       ' AS col2_value,'000175C01RT'||REPLACE(TO_CHAR(d.id_doss, '99999999'),' ','0') AS col3_value,'         ' AS col4_value, '+'||REPLACE(TO_CHAR(ROUND(SUM(d.mnt_cap)+SUM(d.mnt_int)), '99999999'),' ','0')||'XOF0+000000000XOF0'||MIN(DATE(d.date_ech))||MAX(DATE(d.date_ech)) AS col5_value, '  00 00000           000000' AS col6_value, '             +'||REPLACE(TO_CHAR(COUNT(d.id_ech), '99'),' ','0')||'+'||REPLACE(TO_CHAR(ROUND(AVG(d.mnt_cap)+AVG(d.mnt_int)), '999999'),' ','0')||'XOF0+'||REPLACE(TO_CHAR(ROUND((SUM(d.mnt_cap)+SUM(d.mnt_int))), '99999999'),' ','0')||'XOF0' AS col7_value, '      +00000+000000000XOF0' AS col8_value, '                    +000000000XOF0+000000000XOF0' AS col9_value, '               +000' AS col10_value, '                                                       +000000000000000XOF0' AS col11_value, '                          +0000000XOF0                                                  ' AS col12_value
  FROM ad_cli a INNER JOIN adsys_employeur b ON a.pp_partenaire = b.id INNER JOIN ad_dcr c ON a.id_client = c.id_client INNER JOIN ad_etr d ON c.id_doss = d.id_doss
  WHERE DATE(c.cre_date_debloc) <= DATE('$date_fin')"; //TO_CHAR(DATE(c.cre_date_debloc),'YYYY')||
  }
  else{
    $sql = "SELECT '000000000'||SUBSTRING(b.sigle FROM 1 FOR 3)||rpad(a.matricule,7,' ') AS col1_value,'                         *FZYEL                       ' AS col2_value, '0001605C'||REPLACE(TO_CHAR(row_number() over (), '999999'),' ','0')||MIN(DATE(d.date_ech))||MAX(DATE(d.date_ech)) AS col3_value, ' M +000000000000      +' AS col4_value, REPLACE(TO_CHAR(ROUND(AVG(d.mnt_cap)+AVG(d.mnt_int)), '99999999'),' ','0')||'000      +000000000000      00                                                                                                                                       ' AS col5_value
FROM ad_cli a INNER JOIN adsys_employeur b ON a.pp_partenaire = b.id INNER JOIN ad_dcr c ON a.id_client = c.id_client INNER JOIN ad_etr d ON c.id_doss = d.id_doss
WHERE DATE(c.cre_date_debloc) <= DATE('$date_fin')";
  }
  if ($id_emp != null){
    $sql .= " AND b.id = ".$id_emp;
  }
  $sql .= " GROUP BY d.id_doss, b.sigle, a.matricule, DATE(c.cre_date_debloc), c.duree_mois HAVING min(d.date_ech) BETWEEN DATE('$date_debut') AND DATE('$date_fin')";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  $listPret = array();
  $count = 0;
  $nombreCols = $result->numCols();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $count ++;
    $listPret[$count]=$row;
  }
  $listPret['nbrCols'] = $nombreCols;

  $dbHandler->closeConnection(true);
  return $listPret;
}

/**
 * Fonction qui renvoie toutes les localisations existantes dans la table ad_cli
 * Les loc filles d'autres constituent un sous array de leur mère.
 * @param void
 * @return array Tableau associatif des localisation
 */
function getLocRwandaSelectedArray() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select distinct l.* from adsys_localisation_rwanda l
INNER JOIN ad_cli c on c.province = l.id or c.district = l.id or secteur = l.id or cellule = l.id or village = l.id  and l.id_ag=$global_id_agence ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $locArray = array ();
  while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC))
    array_push($locArray, $tmprow);
  $dbHandler->closeConnection(true);
  return $locArray;
}

function get_localisation_rwanda_rapport($id_loc, $type_localisation) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $retour = array ();
  $sql = "SELECT distinct l.* FROM adsys_localisation_rwanda l INNER JOIN ad_cli c on";
  if ($type_localisation == 1){
    $sql .= " l.id  = c.province";
  }elseif ($type_localisation == 2){
    $sql .= " l.id  = c.district";
  }elseif ($type_localisation == 3) {
    $sql .= " l.id  = c.secteur";
  }elseif ($type_localisation == 4){
    $sql .= " l.id  = c.cellule ";
  }elseif ($type_localisation == 5){
    $sql .= " l.id  = c.village";
  }
 $sql .= " WHERE l.id_ag = $global_id_agence and l.type_localisation = $type_localisation";
  if ($id_loc > 0) {
   $sql .= " and l.id = $id_loc";
  }
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($retour, $row);
  }
  array_push($retour, array (
    "id" => 0,
    "libelle_localisation" => _("Non renseigné")
  ));
  $dbHandler->closeConnection(true);
  return $retour;
}

/**
 * AT-33/AT-77 - Fonction dupliqué de la fonction existante get_repartition_epargne
 * Extrait les données qui seront placée dans le rapport concentration de l'épargne
 * @return errorObj avec en paramètre un tableau associatif contenant les données pour le rapport :
 *  * 'id_compte' => array (
 *    * 'sect_act'         => Secteur d'activité,
 *    * 'id_loc1'          => Localisation 1
 *    * 'id_loc2'          => Localisation 2
 *    * 'statut_juridique' => Stat Jur du demandeur
 *    * 'pp_sexe'          => Sexe du demandeur (si PP)
 *    * 'solde_cap'        => Solde en capital
 *    * 'solde_int'        => Solde en intérêt
 *    * 'id_prod'          => ID du produit d'épargne
 *  )
 */
function get_repartition_epargne_rwanda($date_rapport,$date_debut = null,$date_fin = null,$niveau_localisation,$crit_loc=null) {
  global $dbHandler, $global_monnaie, $global_id_agence;
  $db = $dbHandler->openConnection();

  $epargnes = array ();
  $epargnes['totaux']['nbre'] = 0;
  $epargnes['totaux']['mnt'] = 0;
  $epargnes['totaux']['nbreclient'] = 0;
  $epargnes['totaux']['mntclient'] = 0;

  // Récupère tous les comptes d'épargne
  //$sql = "SELECT cli.sect_act, cli.id_loc1, cli.id_loc2, cli.statut_juridique, cli.qualite, cli.pp_sexe, d.id_cpte, d.id_titulaire, calculeCV(d.solde, d.devise, '$global_monnaie') AS solde, d.id_prod, c.classe_comptable";
  //$sql .= " FROM ad_cpt d, adsys_produit_epargne c, ad_cli cli ";
  //$sql .= " WHERE (c.classe_comptable=1 OR c.classe_comptable=2 OR c.classe_comptable=5 OR c.classe_comptable=6) AND d.id_titulaire = cli.id_client AND d.id_prod = c.id AND d.etat_cpte<>2";
  //$sql .= " AND d.id_ag = c.id_ag AND d.id_ag = $global_id_agence";
  // Il est important que la recherche soit triée par id_titulaire
  // De cette manière le tableau $epargnes[] le sera également
  // et grâce à cela nous pouvons optimiser la fonction get_tranche de xml_epargne.php, voir #1201.
  //$sql .= " ORDER BY d.id_titulaire, d.id_cpte";

  //ticket 659
  $v_date_debut = "date('{$date_debut}')";
  $v_date_fin = "date('{$date_fin}')";
  if(is_null($date_debut) || $date_debut == ""){
    $v_date_debut = "NULL";
  }
  if (is_null($date_fin) || $date_fin == ""){
    $v_date_fin = "NULL";
  }
  $sql ="select  cli.sect_act, cli.id_loc1, cli.id_loc2, cli.province, cli.district, cli.secteur, cli.cellule, cli.village, cli.statut_juridique, cli.qualite, cli.pp_sexe, a.id_cpte, a.id_client,  ";
  $sql .="calculeCV(a.solde, a.devise, '$global_monnaie') AS solde, a.id_prod, a.classe_comptable";
  $sql .=" from epargne_view (date('{$date_rapport}'),$global_id_agence,NULL,NULL,NULL,$v_date_debut,$v_date_fin)  as a"  ;
  $sql .= " left join ad_cli cli on ( a.id_ag=cli.id_ag and a.id_client = cli.id_client) ";
  $sql_crit_loc = "";
  if ($niveau_localisation == 1){
    $sql .= " and (cli.province is not null or cli.province > 0) ";
    if (($crit_loc != null || $crit_loc != 0) && $crit_loc > 0){
      $sql_crit_loc = " and cli.province = $crit_loc";
    }
  }
  if ($niveau_localisation == 2){
    $sql .= " and (cli.district is not null or cli.district > 0) ";
    if (($crit_loc != null || $crit_loc != 0) && $crit_loc > 0){
      $sql_crit_loc = " and cli.district = $crit_loc";
    }
  }
  if ($niveau_localisation == 3){
    $sql .= " and (cli.secteur is not null or cli.secteur > 0) ";
    if (($crit_loc != null || $crit_loc != 0) && $crit_loc > 0){
      $sql_crit_loc = " and cli.secteur = $crit_loc";
    }
  }
  if ($niveau_localisation == 4){
    $sql .= " and (cli.cellule is not null or cli.cellule > 0) ";
    if (($crit_loc != null || $crit_loc != 0) && $crit_loc > 0){
      $sql_crit_loc = " and cli.cellule = $crit_loc";
    }
  }
  if ($niveau_localisation == 5){
    $sql .= " and cli.village is not null or cli.village > 0 ";
    if (($crit_loc != null || $crit_loc != 0) && $crit_loc > 0){
      $sql_crit_loc = " and cli.village = $crit_loc";
    }
  }
  $sql .= "$sql_crit_loc ORDER BY a.id_client, a.id_cpte";



  $result = executeQuery($db, $sql);
  if ($result->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $result;
  }

  $clientliste = array();
  foreach ($result->param as $infosCpte) {
    $idCpte = $infosCpte['id_cpte'];
    if (!in_array($infosCpte['id_client'], $clientliste)) {
      // Le tableau $data doit être trié par id_client !!!
      // Ce postulat permet de réduire le tps d'exécution de cette fonction get_tranche, voir #1201.
      array_push($clientliste, $infosCpte['id_client']);
      $epargnes['totaux']['nbreclient']++;
    }

    $epargnes[$idCpte] = $infosCpte;
    $epargnes['totaux']['nbre']++;
    $epargnes['totaux']['mnt'] += $infosCpte['solde'];
    $epargnes['totaux']['mntclient'] += $infosCpte['solde'];
    $epargnes[$idCpte]['sect_act'] = $infosCpte['sect_act'];
    $epargnes[$idCpte]['id_loc1'] = $infosCpte['id_loc1'];
    $epargnes[$idCpte]['id_loc2'] = $infosCpte['id_loc2'];
    $epargnes[$idCpte]['province'] = $infosCpte['province'];
    $epargnes[$idCpte]['district'] = $infosCpte['district'];
    $epargnes[$idCpte]['secteur'] = $infosCpte['secteur'];
    $epargnes[$idCpte]['cellule'] = $infosCpte['cellule'];
    $epargnes[$idCpte]['village'] = $infosCpte['village'];
    $epargnes[$idCpte]['statut_juridique'] = $infosCpte['statut_juridique'];
    $epargnes[$idCpte]['qualite'] = $infosCpte['qualite'];
    if ($infosCpte['statut_juridique'] == 1) {
      $epargnes[$idCpte]['pp_sexe'] = $infosCpte['pp_sexe'];
    }
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $epargnes);
}

/**
 * AT-33/AT-77 - Fonction dupliqué de la fonction get_localisation
 * @param $niv - niveau/type de localisation (Province, District, Secteur, Cellule, Village)
 * @param null $loc - (Les localisations)
 * @return array
 */
function get_localisation_rwanda($niv,$loc=null) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $retour = array ();
  if ($niv == 1)
    $cond = "l.parent = 0 AND l.type_localisation = 1";
  else
    $cond = "l.parent > 0 AND l.type_localisation = $niv";
  $sql = "SELECT DISTINCT l.id, l.libelle_localisation as libel FROM adsys_localisation_rwanda l INNER JOIN ad_cli cli ON cli.province = l.id OR cli.district = l.id OR cli.secteur = l.id OR cli.cellule = l.id OR cli.village = l.id"; // INNER JOIN ad_cli cli ON cli.province = l.id OR cli.district = l.id OR cli.secteur = l.id OR cli.cellule = l.id OR cli.village = l.id
  $sql .= " WHERE";
  if ($niv == 1 && $loc != 0){
    $sql .= " cli.province = $loc AND";
  }
  if ($niv == 2 && $loc != 0){
    $sql .= " cli.district = $loc AND";
  }
  if ($niv == 3 && $loc != 0){
    $sql .= " cli.secteur = $loc AND";
  }
  if ($niv == 4 && $loc != 0){
    $sql .= " cli.cellule = $loc AND";
  }
  if ($niv == 5 && $loc != 0){
    $sql .= " cli.village = $loc AND";
  }
  $sql .= " l.id_ag = $global_id_agence AND $cond";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($retour, $row);
  }
  array_push($retour, array (
    "id" => 0,
    "libel" => _("Non renseigné")
  ));
  $dbHandler->closeConnection(true);
  return $retour;
}


/**
 * Fonction utilisée pour le rapport de Concentration des adhérants
 * get clientStatLoc
 * @param
 * @param
 * @param
 * @return array $retour
 * @author KG
 */
function getClientStatLocRwanda($idloc,$indice_type_loc, $idstat, $pp_sexe=null){

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $retour = array ();
  $sql = "select id_client,pp_date_naissance,pp_sexe from ad_cli where id_ag = $global_id_agence and etat = 2 ";
  if ($indice_type_loc == 1 && $idloc != 0){
    $sql .= " and province =  $idloc";
  }elseif ($indice_type_loc == 2 && $idloc != 0){
    $sql .= " and district = $idloc";
  }elseif ($indice_type_loc == 3 && $idloc != 0){
    $sql .= " and secteur = $idloc";
  }elseif ($indice_type_loc == 4 && $idloc != 0){
    $sql .= " and cellule = $idloc";
  }elseif ($indice_type_loc == 5 && $idloc != 0){
    $sql .= " and village = $idloc";
  }else {
    $sql .= " and province is null and district is null and secteur is null and cellule is null and village is null";
  }
  if($idstat > 0) $sql .= " and statut_juridique = $idstat";
  elseif($idstat == 0) $sql .= " and (statut_juridique is null or statut_juridique = 0)";

  if($pp_sexe != null)
    $sql .= " and pp_sexe = $pp_sexe";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $retour[] = $row;
  }
  $dbHandler->closeConnection(true);
  if (is_array($retour))
    return $retour;
  else
    return NULL;
}

/**
 * Fonction utilisée pour le rapport de Concentration des adhérants
 * get clientStatLoc
 * @param
 * @param
 * @param
 * @return array $retour
 * @author AM
 */
function getClientSecLocRwanda($idloc,$indice_type_loc, $idsecteur){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $retour = array ();
  $sql = "select id_client,pp_date_naissance from ad_cli where id_ag = $global_id_agence and etat = 2 ";
  if ($indice_type_loc == 1 && $idloc != 0){
    $sql .= " and province =  $idloc";
  }elseif ($indice_type_loc == 2 && $idloc != 0){
    $sql .= " and district = $idloc";
  }elseif ($indice_type_loc == 3 && $idloc != 0){
    $sql .= " and secteur = $idloc";
  }elseif ($indice_type_loc == 4 && $idloc != 0){
    $sql .= " and cellule = $idloc";
  }elseif ($indice_type_loc == 5 && $idloc != 0){
    $sql .= " and village = $idloc";
  }else {
    $sql .= " and province is null and district is null and secteur is null and cellule is null and village is null";
  }
  if($idsecteur > 0) $sql .= " and sect_act = $idsecteur";
  elseif($idsecteur == 0) $sql .= " and (sect_act is null or sect_act = 0)";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $retour[] = $row;
  }
  $dbHandler->closeConnection(true);
  if (is_array($retour))
    return $retour;
  else
    return NULL;
}

/**
 * Fonction utilisée pour le rapport de Concentration des adhérants
 * récupere l'ensemble des clients particuliers dans un tableau
 * AM
 */
function getClientsSectStatLocRwanda($idloc, $indice_type_loc, $idsecteur,$statut_juridique){


  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $retour = array ();
  $sql = "select id_client,pp_date_naissance from ad_cli where id_ag = $global_id_agence and etat = 2 ";

  //"SELECT statut_juridique,sect_act, id_loc1,id_loc2,loc3 FROM ad_cli where id_ag = $global_id_agence group by statut_juridique,sect_act, id_loc1,id_loc2,loc3";
  if ($indice_type_loc == 1 && $idloc != 0){
    $sql .= " and province =  $idloc";
  }elseif ($indice_type_loc == 2 && $idloc != 0){
    $sql .= " and district = $idloc";
  }elseif ($indice_type_loc == 3 && $idloc != 0){
    $sql .= " and secteur = $idloc";
  }elseif ($indice_type_loc == 4 && $idloc != 0){
    $sql .= " and cellule = $idloc";
  }elseif ($indice_type_loc == 5 && $idloc != 0){
    $sql .= " and village = $idloc";
  }else {
    $sql .= " and province is null and district is null and secteur is null and cellule is null and village is null";
  }
  if($idsecteur > 0) $sql .= " and sect_act = $idsecteur";
  elseif($idsecteur == 0) $sql .= " and (sect_act is null or sect_act = 0)";

  if($statut_juridique > 0)
    $sql .= " and statut_juridique = $statut_juridique";
  elseif($idsecteur == 0)
    $sql .= " and (statut_juridique is null or statut_juridique = 0)";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $retour[] = $row;
  }
  $dbHandler->closeConnection(true);
  if (is_array($retour))
    return $retour;
  else
    return NULL;
}

function getClientsSectLocRwanda($idloc, $indice_type_loc, $idsecteur){
  /**
   * Fonction utilisée pour le rapport de Concentration des adhérants
   * récupere l'ensemble des clients particuliers dans un tableau
   * @param int $idloc :  valeur de l'identifiant de localisation dans la table des clients'
   * @param int $indice : détermine le niveau des localisations(actuellement, c'est 1 ou 2 )
   * @param int $idsecteur : valeur de l'identifiant du secteur d'activité dans la table des clients
   * @return array $retour
   * @author AM
   */

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $retour = array ();
  $sql = "select id_client,pp_date_naissance from ad_cli where id_ag = $global_id_agence and etat = 2 ";
  if ($indice_type_loc == 1 && $idloc != 0){
    $sql .= " and province =  $idloc";
  }elseif ($indice_type_loc == 2 && $idloc != 0){
    $sql .= " and district = $idloc";
  }elseif ($indice_type_loc == 3 && $idloc != 0){
    $sql .= " and secteur = $idloc";
  }elseif ($indice_type_loc == 4 && $idloc != 0){
    $sql .= " and cellule = $idloc";
  }elseif ($indice_type_loc == 5 && $idloc != 0){
    $sql .= " and village = $idloc";
  }else {
    $sql .= " and province is null and district is null and secteur is null and cellule is null and village is null";
  }
  if($idsecteur > 0) $sql .= " and sect_act = $idsecteur";
  elseif($idsecteur == 0) $sql .= " and (sect_act is null or sect_act = 0)";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $retour[] = $row;
  }
  $dbHandler->closeConnection(true);
  if (is_array($retour))
    return $retour;
  else
    return NULL;
}
/**
* Renvoie les intervalle d'une periode entre deux dates
* @author Roshan Bolah
* @since 3.20-2.11 et 3.22
* @param DATE $date_debut debut de la période
* @param DATE $date_fin fin de la période
* @return ARRAY $intervalle : les intervalles de la periode
**/
function getIntervalleEntreDeuxDates($date1, $date2){
  $intervalle = array();
  $ts1 = strtotime($date1);
  $ts2 = strtotime($date2);
  $year1 = date('Y', $ts1);
  $year2 = date('Y', $ts2);
  $month1 = date('m', $ts1);
  $month2 = date('m', $ts2);
  $day1 = date('d', $ts1);
  $day2 = date('d', $ts2);
  $diff_annee = $year2 - $year1;
  if ($diff_annee == 0){ //meme annee
    $diff_mois = $month2 - $month1;
    if ($diff_mois == 0){ //meme mois
      $nbre_jours = $day2 - $day1;
    }
    if ($diff_mois > 0){ //different mois
      $diff_mois = $diff_mois - 1;
      $nbre_jours = (30.458 - $day1) + $day2;
    }
  }
  if ($diff_annee > 0){ //different annee
    $diff_annee = $diff_annee - 1;
    $diff_mois = ($diff_annee * 12) + (12 - $month1 + $month2) - 1;
    $nbre_jours = (30.458 - $day1) + $day2;
  }
  $diff_jours = (($diff_annee) * 12 * 30.458) + (($diff_mois) * 30.458) + $nbre_jours;
  $diff_semaine = $diff_jours / 7;
  if ($diff_semaine < 1){
    $diff_semaine = 0;
  }
  $intervalle['en_annee'] = $diff_annee;
  $intervalle['en_mois'] = $diff_mois;
  $intervalle['en_jours'] = round($diff_jours,0);
  $intervalle['en_semaine'] = round($diff_semaine,0);
  return $intervalle;
}

function getLibelUtilisateur($id_uti = NULL){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT id_utilis, nom, prenom FROM ad_uti WHERE is_agent_ag= 'f' ";

    if ($id_uti != NULL){
        $sql .= " AND id_uti = $id_uti";
    }
    $sql .= " ORDER BY nom";
    $array_uti = array();
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $array_uti[$row['id_utilis']] = $row['nom'] ." ".$row['prenom'];
    }
    $dbHandler->closeConnection(true);

    return $array_uti;

}

function getLogin($login = NULL){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT login FROM ad_log g INNER JOIN ad_uti u on u.id_utilis = g.id_utilisateur WHERE u.is_agent_ag= 'f' ";

  if ($login != NULL){
    $sql .= " AND login = $login";
  }
  $sql .=" ORDER BY login";
  $array_log = array();
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $array_log[$row['login']] = $row['login'] ;
  }
  $dbHandler->closeConnection(true);

  return $array_log;

}

function getRapports($dir, $pattern){
  $files = scandir($dir);

  $files_metadata = array();
  foreach($files as $key => $file){
    preg_match($pattern, $file, $matches);
    if(!empty($matches)){
        if(file_exists($dir.'/'.$file)){
            $files_metadata[] = array(
                "file_name" => $file,
                "relative_path" => $dir.'/'.$file,
                "modification_date" => date("F D Y H:i:s.", filectime($dir.'/'.$file))
            );
        }
    }
  }

  return $files_metadata;
}

function create_report_tag($root_key, $data){
    $file_name = "/tmp/report_tag.json";

    if(!file_exists($file_name)){
        $json_report_tag = fopen($file_name, "x+");
        $root = '{"report":{"epargne": {}}}';
        fwrite($json_report_tag, $root);
        fclose($json_report_tag);
    }

    $handler = fopen($file_name, "r+");
    $content = json_decode(fread($handler, filesize($file_name)), true);
    $json_path = explode(":", $root_key);
    fclose($handler);

    $handler = fopen($file_name, "w+");
    $content["report"][$json_path[0]] += $data;
    fwrite($handler, json_encode($content));
    fclose($handler);

}

function getReportTag(){
    $file_name = "/tmp/report_tag.json";

    $content = null;
    if(file_exists($file_name)){
        $handler = fopen($file_name, "r+");
        $content = json_decode(fread($handler, filesize($file_name)), true);
        fclose($handler);
    }

    return $content;
}

function isFileValid($filters, $values){
  $flag = true;

  foreach($values as $key => $value){
    if(empty($filters[$key])) continue;
    if($value != $filters[$key]){
      $flag = false;
    }
  }

  return $flag;
}

function unlink_report_tag($parent_key, $child_key){
    $file_name = "/tmp/report_tag.json";

    if(file_exists($file_name)){
        $handler = fopen($file_name, "r+");
        $content = json_decode(fread($handler, filesize($file_name)), true);
        fclose($handler);

        $handler = fopen($file_name, "w+");

        array_splice($content["report"][$parent_key], array_search($child_key, array_keys($content["report"][$parent_key])), 1);
        fwrite($handler, json_encode($content));
        fclose($handler);
    }
}
function file_delete($file_name){
  if(file_exists($file_name)){
    return unlink($file_name);
  }
}
?>
