<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Fonctions utilisées dans le batch
 * @package Systeme
 **/

require_once('lib/dbProcedures/systeme.php');
require_once('lib/dbProcedures/compte.php');
require_once('lib/dbProcedures/agence.php');
require_once('lib/misc/xml_lib.php');
require_once('batch/batch_declarations.php');

/**
 * Transforme une date de string en array
 * @param str $Date Date à convertir
 * @return array Date convertie en tableau : 0->jour 1->mois 2->année
 */
function getPhpDate($Date) {
  // Ex : 2002-02-05
  $Date = substr($Date,0,10);
  $M = substr($Date,5,2);
  $J = substr($Date,8,2);
  $A = substr($Date,0,4);
  return array(0=>$J, 1=>$M, 2=>$A);
}

/**
 * Cette fonction est appelé au lancement du batch
 * Elle vérifie le parametrage des données importantes pour l'éxécution sans erreur du batch
 * Un message d'avertissement s'affiche si une des données n'est pas bien paramétrée
 *
 * @access public
 * @return ErrorObj NO_ERR si tout Ok, sinon le code d'erreur indique l'erreur rencontrée
 *                  et le paramètre donne plus d'informations sur cette erreur.
 * @author aminata
 */
function verif_parametrage() {
  global $dbHandler;
  global $lib_path;
  affiche(_("Vérification du paramétrage des données importantes..."));
  incLevel();

  // Vérification du parametrage du compte comptable des interêts pour les produits d'épargne
  $produits = getListProdEpargne();
  foreach ($produits as $index=>$produit) {
    if ($produit['cpte_cpta_prod_ep_int'] == NULL && $produit['tx_interet'] != 0)
      return new ErrorObj(ERR_PARAM_CPT_INT, $produit['libel'], $produit['id']);
  }
  $id_agence=getNumAgence();
  // Vérification du paramétrage des comptes comptables associés aux états de crédit
  $etats = getTousEtatCredit();
  $nbr_etats = sizeof($etats);
  // Pour tous les produits de crédit dont il existe un DCR
  $produits = getProdInfo(" WHERE id IN (SELECT id_prod FROM ad_dcr GROUP BY id_prod)");
  foreach ($produits as $index=>$produit) {
    $comptes_etats_credit = recup_compte_etat_credit($produit['id']);
    if (sizeof($comptes_etats_credit) < $nbr_etats) {
      // Houston, we got a problem: pas assez de comptes !
      return new ErrorObj(ERR_PARAM_CPT_ASS, $produit['libel'], $produit['id']);
    }
  }

  // Vérification du paramétrage de certaines opérations financières
  // On définit un tableau avec les opérations à vérifier
  $operations_a_verifier = array(152, 410, 471, 505);

  //Vérication pour les opèrations des transactions FERLO
  //142 Retrait avec Carte FERLO
  //162 Dépôt/payement avec carte FERLO
  $echangeFerlo = $lib_path."/ferlo";
  $tab_files=listFiles_trans($echangeFerlo);
  if (sizeof($tab_files) >=1) {
    $operations_a_verifier = array(152, 410, 471, 505,142,162);
  }
  foreach ($operations_a_verifier as $operation) {
    $result = getDetailsOperation($operation);
    if ($result->errCode == NO_ERR) {
      // On vérifie les comptes comptables de l'opération
      $detail_operation = $result->param;

      if ($detail_operation['debit']['categorie'] == 0 AND $detail_operation['debit']['compte'] == NULL)
        // Houston, we got a problem: opération pas configurée !
        return new ErrorObj(ERR_PARAM_OPE, $operation);

      if ($detail_operation['credit']['categorie'] == 0 AND $detail_operation['credit']['compte'] == NULL)
        // Houston, we got a problem: opération pas configurée !
        return new ErrorObj(ERR_PARAM_OPE, $operation);
    }
  }

  global $global_id_agence;
  // Vérification du paramétrage de base_calcul_taux pour le taux d'interêt
  $donnees_ag = getAgenceDatas($global_id_agence);
  if ($donnees_ag["base_taux_epargne"] == NULL)
    return new ErrorObj(ERR_PARAM_AGC, $donnees_ag["base_taux_epargne"]);

  affiche("OK", true);
  decLevel();
  affiche(_("Vérification du parametrage des données terminée !"));
  return new ErrorObj(NO_ERR);
}

/**
 * verif_conditions  Cette fonction est appelée au début de l'exécution dutch
 *                   Elle vérifie les conditions préalableà un lancement du batch.
 *                   Une erreur est déclenchée si au moins une des conditions n'est pas remplie
 *
 * @access public
 * @return bool FALSE si les conditions ne sont pas remplies, TRUE si tout est ok.
 */
function verif_conditions() {
  global $date_jour;
  global $date_mois;
  global $date_annee;
  global $dbHandler, $DB_user, $DB_name;
  global $global_id_agence;
  global $DB_user, $DB_name, $DB_cluster, $DB_pass;

  affiche(_("Vérification des conditions d'exécution ..."));
  incLevel();

  $ok = TRUE;

  $db = $dbHandler->openConnection();

  //Verif backup possible (sera nécessaire dans backup_db()
  // Commande simple qui permet de tester si on a accès à la BD
  $output = array ();
  $code_psql = 0;
  if (!empty($DB_cluster)) {
    putenv("PGCLUSTER=$DB_cluster");
  }
  $retour = exec(escapeshellcmd("PGPASSWORD=$DB_pass psql -U $DB_user -d $DB_name")." -c 'SHOW port;' > /dev/null", $output, $code_psql);
  if ($code_psql != 0)
    erreur("verif_conditions()", _("Le batch ne pourra pas faire une sauvegarde de la base, problème de droits d'accès."));
  //Verif statut agence
  $result = $db->query("SELECT statut FROM ad_agc WHERE id_ag=$global_id_agence");
  if (DB::isError($result))
    erreur("verif_conditions()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  $row = $result->fetchrow();

  if ($row[0] == 1) { //Si agence ouverte
    force_all_logout(); //on déconnecte tous les guichets d'office
  }

  //Verif date
  $result = $db->query("SELECT last_batch FROM ad_agc WHERE id_ag=$global_id_agence");
  if (DB::isError($result))
    erreur("verif_conditions()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  $row = $result->fetchrow();
  $last_batch = getPhpDate($row[0]);

  //si la date du jour est supérieure à la date du batch + 1 jour
  //i.e. last batch + 1 jour doit etre égal à date du jour si le batch a été exécuté la veille
  if ( date("Y/m/d", gmmktime(0,0,0,$date_mois,$date_jour,$date_annee) ) >
       date("Y/m/d", mktime(0,0,0,$last_batch[1],$last_batch[0]+1,$last_batch[2]) ) ) {
    //Si le batch ne s'est pas exécuté pour le jour précédent
    erreur("verif_conditions()", _("Le batch n'a pas été exécuté jusqu'à hier !"));
  } else if ( date("Y/m/d", gmmktime(0,0,0,$date_mois,$date_jour,$date_annee) ) <
              date("Y/m/d", mktime(0,0,0,$last_batch[1],$last_batch[0]+1,$last_batch[2]) ) ) {
    //Si le batch a déjà été exécuté pour aujourd'hui et que les frais ont été prélevés : double vérif
    //erreur("verif_conditions()", "Le batch a déjà été exécuté pour cette date !");
    $ok = FALSE;
  }

  $dbHandler->closeConnection(true);

  affiche("OK", true);

  decLevel();
  affiche(_("Vérification des conditions terminée !"));

  return $ok;

}

function update_conditions() {
  global $date_total;
  global $db;
  global $global_id_agence;
  global $dbHandler;

  affiche(_("Mise à jour des conditions d'exécution ..."));
  incLevel();

  $db = $dbHandler->openConnection();
  $global_id_agence = getNumAgence();
  //Met à jour last_batch
  $result = $db->query("UPDATE ad_agc SET last_batch='$date_total' WHERE id_ag=$global_id_agence");
  if (DB::isError($result))
    erreur("update_conditions()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  //Met à jour le statut de l'agence à fermé
  $result = $db->query("UPDATE ad_agc SET statut = 2 WHERE id_ag=$global_id_agence");
  if (DB::isError($result))
    erreur("update_conditions()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  affiche("OK", true);

  $dbHandler->closeConnection(true);

  decLevel();
  affiche(_("Mise à jour des conditions terminée !"));
  return true;
}

function update_conditions_frais_cpt() {
  //mettre à jour la dernière date de traitement des frais de tenue de compte

  global $date_total;
  global $db;
  global $global_id_agence;
  $global_id_agence = getNumAgence();
  affiche(_("Mise à jour des conditions d'exécution des frais de tenue ..."));
  incLevel();

  //Met à jour last prélève
  $sql = "UPDATE ad_agc SET last_prelev_frais_tenue='$date_total' WHERE id_ag=$global_id_agence;";
  $result = $db->query($sql);
  if (DB::isError($result))
    erreur("update_conditions_frais_cpt()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

  affiche("OK", true);

  decLevel();
  affiche(_("Mise à jour des conditions frais tenue terminée !"));
  return true;

}

function callback_overwrite_date_compta(&$value, $key)
{
  global $date_total, $is_type_opt,$date_total_type_opt;

  $date_total_local = $date_total;

  $dates_arr = array("date_comptable", "date_valeur");
  // la date valeur pour les reclassements / declassements = date batch.
  if ($key == "type_operation" && $value == "270") {
    //$date_total_type_opt =  demain($date_total_local);
    $is_type_opt = true; // identifier for 270
  }

  if (trim($date_total) != '') { // if date_batch set
    if (in_array($key, $dates_arr)) { // if its date_comptable or date_valeur
      if ($is_type_opt == true) { // if it was 270
        $value = $value; // -> Stays the same //$value = $date_total_type_opt; -> set to demain (if 270)
        if ($key == "date_comptable") {
          $is_type_opt = false;
        }
      } else { // if it wasnt type operation 270
        $value = $date_total_local; // set to date batch = J-1
      }
    }
  }

  return NULL;
}

// Remplace la date du jour par la date comptable
function overwrite_date_compta(&$mouvements_data) {
   // print_rn("BEFORE callback_overwrite_date_compta");
    //print_rn($mouvements_data);

    $is_type_opt = false;

    array_walk_recursive($mouvements_data, 'callback_overwrite_date_compta');

    //print_rn("AFTER callback_overwrite_date_compta");
    //print_rn($mouvements_data);

    return NULL;
}


function callback_overwrite_date_compta_old(&$value, $key)
{
  global $date_total;

  $dates_arr = array("date_comptable", "date_valeur");

  if(trim($date_total) != '') {
    if(in_array($key, $dates_arr)) {
      $value = $date_total;
    }
  }

  return NULL;
}

// Remplace la date du jour par la date comptable
function overwrite_date_compta_old(&$mouvements_data) {
  array_walk_recursive($mouvements_data, 'callback_overwrite_date_compta');
  return NULL;
}


/** 
 * Fonction qui met a jour le champ num_cpte_comptable dans ad_cpt en masse
 * en recuperant les bon numeros de compte
 * 
 * @return ErrorObj
 */
function update_num_cpte_comptable()
{
	global $dbHandler;
	$db = $dbHandler->openConnection();
	
	$sql ="SELECT recup_num_cpte_comptable_cpte_interne();";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL, $result->getUserinfo());
	}	
	$counter = $result->fetchrow();
	$dbHandler->closeConnection(TRUE);
	return new ErrorObj(NO_ERR, $counter);
}

?>