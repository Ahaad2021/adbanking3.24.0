<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * @package Credit
 */

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'lib/dbProcedures/extraits.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/handleDB.php';
require_once 'lib/misc/tableSys.php';


/**
 * Liste des produits des engrais chimique par agence
 * @param int $id_ag identifiant de l'agence
 * @return array tableau contenant la liste des produits de crédit
 */
function getListeSaisonPNSEB($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_saison_culturale WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_saison"]] = $row['nom_saison'];
  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getListeSaisonPNSEBlatest($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT id_saison,etat_saison, date(date_debut) as date_debut ,date(date_fin) as date_fin FROM ec_saison_culturale WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $sql .=" order by date_fin DESC limit 1";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getDetailSaisonCultu($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT id_saison,id_annee,nom_saison,etat_saison, date(date_debut) as date_debut ,date(date_fin) as date_fin, plafond_engrais, plafond_amendement FROM ec_saison_culturale WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}


function getDetailSaisonCultuAll($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT id_saison,id_annee,nom_saison,etat_saison, date(date_debut) as date_debut ,date(date_fin) as date_fin, plafond_engrais, plafond_amendement FROM ec_saison_culturale WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  while($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $DATAS[$row["id_saison"]] = $row;
  }
  //$DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function CheckAutreSaisonExist($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT id_saison,etat_saison, date(date_debut) as date_debut ,date(date_fin) as date_fin FROM ec_saison_culturale WHERE id_ag=$global_id_agence and etat_saison = 1 ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $sql .=" order by date_fin DESC limit 1";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}



/**
 * Liste des produits des engrais chimique par agence
 * @param int $id_ag identifiant de l'agence
 * @return array tableau contenant la liste des produits de crédit
 */
function getListeProduitPNSEB($whereCond=null,$tous=false) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_produit WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $sql .="order by id_produit";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    if ($tous === true){
      $DATAS[$row["id_produit"]] = $row;
    }
    else{
      $DATAS[$row["id_produit"]] = $row['libel'];
    }
  }

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getDetailsProduits($whereCond=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_produit WHERE id_ag = $global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getListelocalisationPNSEB($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_localisation WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id"]] = $row['libel'];

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getListeAnneeAgricolePNSEB($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_annee_agricole WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }


  if ($result->numRows() == 0){
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_annee"]] = $row['libel'];

  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getRangeDateAnneeAgri($whereCond=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_annee,libel, date(date_debut) as date_debut, date(date_fin) as date_fin from ec_annee_agricole WHERE id_ag = $global_id_agence AND etat = 1 ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getAnneeAgricoleActif($id_annee =null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_annee_agricole WHERE id_ag = $global_id_agence AND etat = 1 ";

  if ($id_annee != null){
    $sql .= " AND id_annee = ".$id_annee;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0) {

    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}
function getAnneeAgricole($whereCond =null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_annee_agricole WHERE id_ag = $global_id_agence AND etat = 1 ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0) {

    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}


function getDateAnneeAgricoleActif($id_annee =null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_annee, date(date_debut) as date_debut, date(date_fin) as date_fin from ec_annee_agricole WHERE id_ag = $global_id_agence AND etat = 1 ";

  if ($id_annee != null){
    $sql .= " AND id_annee = ".$id_annee;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getListeAnneeAgricoleActif($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_annee_agricole WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }


  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_annee"]] = $row['libel'];

  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getAnneeAgricoleFromSaison($id_saison =null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_saison_culturale WHERE id_ag = $global_id_agence ";

  if ($id_saison != null){
    $sql .= " AND id_saison = ".$id_saison;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function setPrixUnitaireModifiable() {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT s.* FROM ec_saison_culturale s INNER JOIN ec_annee_agricole an ON s.id_annee = an.id_annee AND s.id_ag = an.id_ag WHERE an.etat = 1 AND s.etat_saison = 1 AND date(now()) BETWEEN coalesce(date(s.date_fin_avance),date(now())) AND coalesce(date(s.date_debut_solde),date(now())) AND s.id_ag=$global_id_agence ";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  $dbHandler->closeConnection(true);

  $setPrixUnitaire = FALSE;

  if ($result->numRows() == 0) {
    $setPrixUnitaire = FALSE;
  }
  else{
    $setPrixUnitaire = TRUE;
  }

  return $setPrixUnitaire;
}

function getDerogationenCours($whereCond=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_derogation WHERE id_ag = $global_id_agence";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getCommande($whereCond=null,$order_by=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_commande WHERE id_ag = $global_id_agence";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  if ($order_by != null) {
    $sql .= " order by " . $order_by;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_commande"]] = $row;

  $dbHandler->closeConnection(true);
  return $DATAS;
}


function getCommandeDetail($whereCond=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_commande_detail WHERE id_ag = $global_id_agence";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["id_detail"]] = $row;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;

}


function getDetailsBeneficiaire($whereCond=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();


  $sql = "SELECT * from ec_beneficiaire WHERE id_ag = $global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function processAutorisationCommandeAttente($data = null, $id_beneficiaire = null)
{
  global $dbHandler, $global_id_agence, $global_nom_login;


  // Get liste demande de retrait
  $condi = "id_benef=".$id_beneficiaire;
  $listeDemandeCommande = getDerogationCommande($condi);

  $demande_count = 0;
  foreach ($listeDemandeCommande as $id => $demandeCommande) {

    $db = $dbHandler->openConnection();
    $isValidationOK = false;
    $isAutorisationOK = false;

    $id_demande = trim($demandeCommande["id_derogation"]);

    if (isset($data['btn_process_demande'])) {

      if (isset($data['check_valid_' . $id_demande])) {

        $fonction = 196; // Autorisation retrait

        $isValidationOK = true;
        $isAutorisationOK = true;

      } elseif (isset($data['check_rejet_' . $id_demande])) {

        $fonction = 197; // Refus retrait

        $isValidationOK = true;
      }
      if ($isValidationOK == true) {

        $myErr = ajout_historique($fonction, null, "Demande autorisation commande No. ".$id_demande, $global_nom_login, date("r"));

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        } else {
          // Mettre à jour le statut d'une demande de retrait à Autorisé / Refusé
          $erreur = updateDerogationCommandeAttenteEtat($id_demande, (($isAutorisationOK) ? 2 : 3), sprintf("Demande autorisation commande : %s", (($isAutorisationOK) ? "validé" : "rejeté")), $myErr->param);

          if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
          } else {

            if ($isAutorisationOK == false){
              $Fields_commande["etat_commande"] = 5;
              $Fields_commande["date_modif"] = date("Y-m-d");
              $Where_commande["id_commande"] = $demandeCommande['id_commande'] ;
              $update_etat_commande = buildUpdateQuery("ec_commande", $Fields_commande, $Where_commande);
              $result1 = $db->query($update_etat_commande);
              if (DB::isError($result1)) {
                $dbHandler->closeConnection(false);
                signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $result1 . "\n" . $result1->getMessage());
              }
            }
            // Commit
            $dbHandler->closeConnection(true);

            $demande_count++;
          }
        }
      } else {
        $dbHandler->closeConnection(false);
      }
    } else {
      $dbHandler->closeConnection(false);
    }
  }

  return new ErrorObj(NO_ERR, $demande_count);
}

function getDerogationCommande($whereCond=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_derogation WHERE id_ag = $global_id_agence";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0){
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_derogation"]] = $row;

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function updateDerogationCommandeAttenteEtat($id_demande, $etat_retrait, $comments = '', $id_his = null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $tableFields = array(
    "id_his" => $id_his,
    "etat" => $etat_retrait,
    "date_modif" => date("r"),
    "comment" => trim($comments)
  );

  $sql_update = buildUpdateQuery("ec_derogation", $tableFields, array('id_derogation' => $id_demande, 'id_ag' => $global_id_agence));

  $result = $db->query($sql_update);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

function getCompteCptaProdPnseb($id_prod){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql= "select compte_produit from ec_produit where id_produit = $id_prod";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0){
    $dbHandler->closeConnection(true);
    return NULL;
  }
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS = $row['compte_produit'];

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getPaiementDetail($whereCond=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_paiement_commande WHERE id_ag = $global_id_agence";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $resultPaiementDetail=$db->query($sql);
  if (DB::isError($resultPaiementDetail)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$resultPaiementDetail->getMessage());
  }
  if ($resultPaiementDetail->numRows() == 0){
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $resultPaiementDetail->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["id"]] = $row;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getPaiementMaxRemb($id_comamde){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT max(id_remb) from ec_paiement_commande WHERE id_ag = $global_id_agence and id_commande = $id_comamde";

  $resultPaiementMaxRemb=$db->query($sql);
  if (DB::isError($resultPaiementMaxRemb)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$resultPaiementMaxRemb->getMessage());
  }
  if ($resultPaiementMaxRemb->numRows() == 0){
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $resultPaiementMaxRemb->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function InsertPaiement($DATA=null,$counter=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  if ($DATA !=null) {
    for ($i = 1; $i <= $counter; $i++) {
      $result = executeQuery($db, buildInsertQuery("ec_paiement_commande", $DATA[$i]));
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
      }
    }
    $dbHandler->closeConnection(true);
    return true;
  }else {
    //$dbHandler->closeConnection(false);
    return false;
  }
}

function getNextValIdHis(){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT nextval('ad_his_id_his_seq')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $data_id_his = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $id_his = $data_id_his['nextval'];

  $dbHandler->closeConnection(true);
  return $id_his;

}

/**
 * Fonction qui compte le nombre de beneficiaires renvoyés par la fonction getMatchedBeneficiaire avec les mêmes paramètres
 */
function countMatchedBeneficiaire($Where)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $WhereClause = "";
  if (is_array($Where)) {
    $Where=array_make_pgcompatible($Where);
  }

  foreach($Where as $key => $value)
  {
    if(isset($value) && !empty($value))
    {
      switch ($key)
      {
        case 'nom_prenom' :
          $WhereClause .= " ( UPPER(nom_prenom) like UPPER('%$value%') OR LOWER(nom_prenom) like LOWER('%$value%') ) AND";
          break;
        case 'nic' :
          $WhereClause .= " nic like '%$value%' AND";
          break;
        case 'id_province' :
          $WhereClause .= " id_province = $value AND";
          break;
        case 'id_commune' :
          $WhereClause .= " id_commune = $value AND";
          break;
        case 'id_zone' :
          $WhereClause .= " id_zone = $value AND";
          break;
        case 'id_colline' :
          $WhereClause .= " id_colline = $value AND";
          break;
        default:
          $WhereClause .= "( $key = $value ) AND";
      }
    }
  }

  $WhereClause .= " id_ag = $global_id_agence AND";
  $WhereClause = substr($WhereClause, 0, strlen($WhereClause) - 3);
  $sql = "SELECT count(*) FROM ec_beneficiaire WHERE" . $WhereClause . ";";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row = $result->fetchrow();
  return $row[0];
}

/**
 * Renvoie un array contenant tous les Beneficiaires matchant la WhereClause
 * Chaque Beneficiaire est lui-même un tableau associatif avec toutes les données d'un Beneficiaire donné
 * $Where est un tableau associatif de type $Where[clé] = valeur;
 * Valeurs de retour :
 * Le tableau si OK
 * NULL si aucun Beneficiaire matchant ces critères n'a été trouvé.
 * Die si erreur de la DB
 */
function getMatchedBeneficiaire($Where)
{
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $WhereClause = "";
  if (is_array($Where)) {
    $Where=array_make_pgcompatible($Where);
  }

  foreach($Where as $key => $value)
  {
    if(isset($value) && !empty($value))
    {
      switch ($key)
      {
        case 'nom_prenom' :
          $WhereClause .= " ( UPPER(nom_prenom) like UPPER('%$value%') OR LOWER(nom_prenom) like LOWER('%$value%') ) AND";
          break;
        case 'nic' :
          $WhereClause .= " nic like '%$value%' AND";
          break;
        case 'id_province' :
          $WhereClause .= " id_province = $value AND";
          break;
        case 'id_commune' :
          $WhereClause .= " id_commune = $value AND";
          break;
        case 'id_zone' :
          $WhereClause .= " id_zone = $value AND";
          break;
        case 'id_colline' :
          $WhereClause .= " id_colline = $value AND";
          break;
        default:
          $WhereClause .= "( $key = $value ) AND";
      }
    }
  }

  $WhereClause .= "  id_ag = $global_id_agence AND";
  $WhereClause = substr($WhereClause, 0, strlen($WhereClause) - 3);

  $sql = "SELECT * FROM ec_beneficiaire WHERE" . $WhereClause . " order by id_beneficiaire asc;";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $DATAS = array ();

  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($DATAS, $tmprow);
  }

  $dbHandler->closeConnection(true);
  return $DATAS;
}
function getListeSaisonCultuDetails($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT id_saison,id_annee,nom_saison,etat_saison, date(date_debut) as date_debut ,date(date_fin) as date_fin, plafond_engrais, plafond_amendement, date(date_fin_avance) as date_fin_avance, date(date_debut_solde) as date_debut_solde, date(date_fin_solde) as date_fin_solde  FROM ec_saison_culturale WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $DATAS[$row["id_saison"]] = $row;
  }
  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getMontantPaiementCommande($whereCond=null,$choix=null,$date_debut=null,$date_fin=null) {
  global $dbHandler,$global_id_agence;


  if ($date_fin == null){
    $date_fin = date('d/m/y');
  }
  $db = $dbHandler->openConnection();
  //$sql = "SELECT id_saison,etat_saison, date(date_debut) as date_debut ,date(date_fin) as date_fin FROM ec_saison_culturale WHERE id_ag=$global_id_agence and etat_saison = 1 ";



  $sql="select";
  if ($choix == 1){
    $sql.=" sum(total_depot) as total_encaisse, ";
  } else if (($choix == 2) ||($choix == null) ){
    $sql .=" sum(total_depot) + sum(mnt_payer) as total_encaisse, sum(mnt_payer) as mnt_paid, ";
  }
  /* else if ($choix == null){
     $sql .=" sum(total_depot)+sum(total_paid) as total_encaisse, ";
   }*/
  $sql.="count(distinct numb_benef) as nb_agri from (
select
distinct c.id_commande ,c.montant_depose as total_depot,  sum(montant_paye) as total_paid, c.id_benef as numb_benef, sum(montant_paye) as mnt_payer
from ec_commande c
LEFT JOIN ec_paiement_commande p on c.id_commande = p.id_commande
where etat_commande not in (7,5,6) ";
  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  if (($choix ==2) || ($choix == 0)){
    $sql .= " and p.etat_paye = 2 ";
  }
  if ($choix == 1){
    $sql.=" and c.date_creation >= date('".$date_debut."') and c.date_creation <= date('".$date_fin."')";
  } else if (($choix == 2) ||($choix == 0) ){
    $sql.=" and p.date_creation >= date('".$date_debut."') and p.date_creation <= date('".$date_fin."')";
  }/* else if ($choix == 0){
    $sql.=" and c.date_creation >= date('".$date_debut."') and c.date_creation <= date('".$date_fin."') and p.date_creation >= date('".$date_debut."') and p.date_creation <= date('".$date_fin."') ";
  }*/
  $sql .=" group by c.id_commande, c.montant_depose, c.id_benef
order by c.id_commande
) z";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

/**
 *
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport engrais chimiques list beneficiaire payant
 *
 * @param number $annee
 * @param number $saison
 * @param number $periode
 * @param date $date debut
 * @param date $date fin
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */
function get_rapport_list_beneficiaire_payant_data($annee, $saison, $periode=0, $date_debut, $date_fin)
{
  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $db = $dbHandler->openConnection();

  if(empty($date_debut)) {
    $date_debut = date("Y")."-".date("m")."-".date("d");
  }
  elseif(strpos($date_debut, '/')){
    $date_debut = php2pg($date_debut);
  }

  if(empty($date_fin)) {
    $date_fin = date("Y")."-".date("m")."-".date("d");
  }
  elseif(strpos($date_fin, '/')){
    $date_fin = php2pg($date_fin);
  }
  if(empty($saison)) {
    $saison = 0;
  }

  $sql = "select * from getDataRapport($annee,$saison,'$date_debut','$date_fin') order by id_benef,id_prod";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }


  $produit_actif = getProduitActifSaison($saison);
  $DATA_PRODUIT = array();
  while (list($key_list, $COM_list) = each($produit_actif)) {
    $DATA_PRODUIT[$key_list]=$COM_list["libel"];
  }


  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$saison]["province"][$row["nom_province"]]["nom_province"]=$row["nom_province"];
    $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["nom_commune"] = $row["nom_commune"];
    $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["nom_coopec"] = $row["nom_coopec"];
    $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["nom_zone"]=$row["nom_zone"];
    $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]["nom_colline"]=$row["nom_colline"];
    $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["nom_prenom"]=$row["nom_prenom"];
    $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["id_card"]=$row["id_card"];
    //$DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["montant_avance"]=$row["montant_avance"];
    //$DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["montant_solde"]=$row["montant_solde"];
    $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["libel"] = $row["libel"];
    if ($periode == 1){
      $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["quantite"]= $row["quantite"];
    }else{
      $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["quantite"] += $row["qtite_paye"];
    }
    if ($periode == 1){
      $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["total"] = $row["montant_avance"];
    }else if ($periode == 2){
      $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["total"] = $row["montant_solde"];
    }else{
      $DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["total"] = $row["montant_avance"] + $row["montant_solde"];
    }

    $DATAS[$saison]["item_produit"] = $DATA_PRODUIT;
  }

  $dbHandler->closeConnection(true);
  return $DATAS;

}

function getDetailMontantProduitEncaisse($whereCond = null,$choix=null,$date_debut=null,$date_fin=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select mnt as mnt_depot, mnt_paye as mnt_paid, mnt+mnt_paye as montant_total , produit, id_prod from (
  select
  sum(d.montant_depose) as mnt, libel as produit,p.id_produit as id_prod, sum(montant_paye) as mnt_paye
  from ec_commande_detail d
  inner join ec_commande c on c.id_commande = d.id_commande
  inner join ec_produit p on p.id_produit = d.id_produit
  left join ec_paiement_commande pc on pc.id_detail_commande = d.id_detail
  where etat_commande not in (5)
  ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $sql .="  and c.date_creation >= date('".$date_debut."')  and c.date_creation <= date('".$date_fin."')
  or (pc.date_creation >= date('".$date_debut."')  and pc.date_creation <= date('".$date_fin."')) ";

  $sql .=" group by p.id_produit, libel
  )z";
  $resultMontantProduit=$db->query($sql);
  if (DB::isError($resultMontantProduit)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$resultMontantProduit->getMessage());
  }
  if ($resultMontantProduit->numRows() == 0){
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $resultMontantProduit->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["id_prod"]] = $row;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getListeBenefPlafond($id_annee,$id_saison=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql="SELECT  id_beneficiaire, nom_prenom,id_commande ,nbre_engrais, sum(total_engrais) as total_engrais,nbre_amendement, sum(total_amendement) as total_amendement, sum(total_engrais) + sum(total_amendement) as total_depassement from (
SELECT distinct d.id_commande,d.nbre_engrais, d.nbre_amendement,b.id_beneficiaire, b.nom_prenom, c.id_saison,
CASE WHEN p.type_produit = '1' THEN sum(cd.montant_depose) ELSE 0 END as total_engrais,
CASE WHEN p.type_produit = '2' THEN sum(cd.montant_depose) ELSE 0 END as total_amendement
FROM ec_commande c
INNER JOIN ec_derogation d on d.id_commande = c.id_commande
INNER JOIN ec_commande_detail cd on cd.id_commande = c.id_commande
INNER JOIN ec_produit p on cd.id_produit = p.id_produit
INNER JOIN ec_beneficiaire b on b.id_beneficiaire = c.id_benef
INNER JOIN ec_saison_culturale s on s.id_saison = c.id_saison
where s.id_annee = $id_annee and ";
  if($id_saison != null){
    $sql .= "c.id_saison = " .$id_saison. " " ;
  }
  $sql .= "group by d.id_commande,d.nbre_engrais, d.nbre_amendement,b.id_beneficiaire, b.nom_prenom, c.id_saison,p.type_produit
order by id_commande
) z
group by id_commande, id_beneficiaire,nom_prenom,nbre_engrais,nbre_amendement";

  $resultMontantProduit=$db->query($sql);
  if (DB::isError($resultMontantProduit)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$resultMontantProduit->getMessage());
  }
  if ($resultMontantProduit->numRows() == 0){
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $DATAS=array();
  $sous_total_engrais = 0;
  $sous_total_amendement = 0;
  $sous_total_montant = 0;
  while ( $row = $resultMontantProduit->fetchRow(DB_FETCHMODE_ASSOC) ) {
    //$DATAS[$row["id_commande"]] = $row;
    $DATAS[$id_saison][$row["id_commande"]]['id_benef']=$row['id_beneficiaire'];
    $DATAS[$id_saison][$row["id_commande"]]['nom_prenom']=$row['nom_prenom'];
    $DATAS[$id_saison][$row["id_commande"]]['id_commande']=$row['id_commande'];
    $DATAS[$id_saison][$row["id_commande"]]['nbre_engrais']=$row['nbre_engrais'];
    $DATAS[$id_saison][$row["id_commande"]]['total_engrais']=$row['total_engrais'];
    $DATAS[$id_saison][$row["id_commande"]]['nbre_amendement']=$row['nbre_amendement'];
    $DATAS[$id_saison][$row["id_commande"]]['total_amendement']=$row['total_amendement'];
    $DATAS[$id_saison][$row["id_commande"]]['total_depassement']=$row['total_depassement'];
    $sous_total_engrais += $row['total_engrais'];
    $sous_total_amendement += $row['total_amendement'];
    $sous_total_montant += $row['total_depassement'];

  }
  $DATAS[$id_saison]['sous_total_engrais']+=$sous_total_engrais;
  $DATAS[$id_saison]['sous_total_amendement']+=$sous_total_amendement;
  $DATAS[$id_saison]['sous_total_montant']+=$sous_total_montant;

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getRepartitionZone($id_annee,$id_saison,$date_debut,$date_fin,$localisation,$type_repartition){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  if(empty($date_debut)) {
    $date_debut = date("Y")."-".date("m")."-".date("d");
  }
  elseif(strpos($date_debut, '/')){
    $date_debut = php2pg($date_debut);
  }

  if(empty($date_fin)) {
    $date_fin = date("Y")."-".date("m")."-".date("d");
  }
  elseif(strpos($date_fin, '/')){
    $date_fin = php2pg($date_fin);
  }

  $sql = "SELECT id_commune,nom_commune, id_zone,nom_zone, id_produit,libel, sum(qtite) as qtite, sum(total) as total_mnt from(
SELECT b.id_commune, b.id_zone,p.id_produit,p.libel, sum(d.quantite) as qtite, sum(d.montant_depose)  as total,
(select libel from ec_localisation l where l.id=b.id_commune) as nom_commune,
(select libel from ec_localisation l where l.id=b.id_zone) as nom_zone
FROM ec_beneficiaire b
INNER JOIN ec_commande c on c.id_benef = b.id_beneficiaire
INNER JOIN ec_commande_detail d on d.id_commande = c.id_commande
INNER JOIN ec_produit p on p.id_produit = d.id_produit
where c.etat_commande in (1,2,3,4)
and c.date_creation >= date('$date_debut')  and c.date_creation <= date('$date_fin')
and b.id_commune = $localisation
  and c.id_saison = $id_saison
group by b.id_commune, b.id_zone, d.quantite,p.type_produit,p.id_produit,p.libel)
z
group by id_commune, id_zone, nom_commune,nom_zone,id_produit,libel
order by id_commune, id_zone,id_produit,libel" ;

  $resultListeBenefZone=$db->query($sql);
  if (DB::isError($resultListeBenefZone)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$resultListeBenefZone->getMessage());
  }
  if ($resultListeBenefZone->numRows() == 0){
    $dbHandler->closeConnection(true);
    return null;
  }
  $DATAS_repartition= array();
  $tot = 0;

  while ( $row = $resultListeBenefZone->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS_repartition[$row["nom_commune"]]["commune"] = $row["nom_commune"];
    $DATAS_repartition[$row["nom_commune"]]["zone"][$row["id_zone"]]["detail"] = $row["nom_zone"];
    /*---------------------------------------------------------------------------------*/
    $DATAS_repartition[$row["nom_commune"]]["zone"][$row["id_zone"]]["detail"] = $row["nom_zone"];
    $DATAS_repartition[$row["nom_commune"]]["zone"][$row["id_zone"]]["detail_zone"][$row["id_produit"]]["libel"] = $row["libel"];
    $DATAS_repartition[$row["nom_commune"]]["zone"][$row["id_zone"]]["detail_zone"][$row["id_produit"]]["quantite"] = $row["qtite"];
    $DATAS_repartition[$row["nom_commune"]]["zone"][$row["id_zone"]]["detail_zone"][$row["id_produit"]]["total"] = $row["total_mnt"];

  }

  $dbHandler->closeConnection(true);

  return $DATAS_repartition;

}

function getRepartitionCommune($id_annee,$id_saison,$date_debut,$date_fin,$localisation,$type_repartition){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  if(empty($date_debut)) {
    $date_debut = date("Y")."-".date("m")."-".date("d");
  }
  elseif(strpos($date_debut, '/')){
    $date_debut = php2pg($date_debut);
  }

  if(empty($date_fin)) {
    $date_fin = date("Y")."-".date("m")."-".date("d");
  }
  elseif(strpos($date_fin, '/')){
    $date_fin = php2pg($date_fin);
  }

  $sql = "SELECT  nom_commune,id_produit, libel, sum(qtite) as qtite,sum(total) as total_mnt from(
SELECT p.id_produit,p.libel, sum(d.quantite) as qtite,sum(d.montant_depose)  as total,
(select libel from ec_localisation l where l.id=b.id_commune) as nom_commune,
(select libel from ec_localisation l where l.id=b.id_zone) as nom_zone
FROM ec_beneficiaire b
INNER JOIN ec_commande c on c.id_benef = b.id_beneficiaire
INNER JOIN ec_commande_detail d on d.id_commande = c.id_commande
INNER JOIN ec_produit p on p.id_produit = d.id_produit
where c.etat_commande in (1,2,3,4)
and c.date_creation >= date('$date_debut')  and c.date_creation <= date('$date_fin')
and b.id_commune = $localisation
  and c.id_saison = $id_saison
group by b.id_commune, b.id_zone, d.quantite,p.type_produit,p.id_produit,p.libel)
z
group by nom_commune,id_produit, libel
order by nom_commune, id_produit,libel" ;

  $resultListeBenefZone=$db->query($sql);
  if (DB::isError($resultListeBenefZone)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$resultListeBenefZone->getMessage());
  }
  if ($resultListeBenefZone->numRows() == 0){
    $dbHandler->closeConnection(true);
    return null;
  }
  $DATAS_repartition= array();
  $tot = 0;

  while ( $row = $resultListeBenefZone->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS_repartition[$row["nom_commune"]]["commune"] = $row["nom_commune"];
    //$DATAS_repartition[$row["nom_commune"]]["zone"][$row["id_zone"]]["detail"] = $row["nom_zone"];
    /*---------------------------------------------------------------------------------*/
    //$DATAS_repartition[$row["nom_commune"]]["zone"][$row["id_zone"]]["detail"] = $row["nom_zone"];
    $DATAS_repartition[$row["nom_commune"]]["produit"][$row["id_produit"]]["libel"] = $row["libel"];
    $DATAS_repartition[$row["nom_commune"]]["produit"][$row["id_produit"]]["quantite"] = $row["qtite"];
    $DATAS_repartition[$row["nom_commune"]]["produit"][$row["id_produit"]]["total"] = $row["total_mnt"];

  }

  $dbHandler->closeConnection(true);

  return $DATAS_repartition;

}
function getNbreProduitCommande($id_saison=null,$benef=null){

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select sum(d.quantite) as quantite, p.type_produit from ec_commande_detail d
INNER JOIN ec_commande c on c.id_commande = d.id_commande
INNER JOIN ec_produit p on p.id_produit = d.id_produit
where c.id_benef = $benef
and c.id_saison = $id_saison
and c.etat_commande IN (1,7)
group by p.type_produit
";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0){
    $dbHandler->closeConnection(true);
    return null;
  }

  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["type_produit"]] = $row;

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getLocalisationDetails($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_localisation WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getProduitCommander($id_annee=null,$id_saison=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT distinct d.id_produit, p.libel FROM ec_commande_detail d
 INNER JOIN ec_produit p ON p.id_produit = d.id_produit
 INNER JOIN ec_commande c ON c.id_commande = d.id_commande
 INNER JOIN ec_saison_culturale s ON c.id_saison =s.id_saison
 WHERE s.id_annee= $id_annee";
  if ($id_saison != null || $id_saison != 0){
    $sql .= " AND s.id_saison = $id_saison";
  }
  $sql .= " order by d.id_produit";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $DATAS[$row["id_produit"]] = $row;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getProduitActif($where=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT id_produit, libel from ec_produit $where order by id_produit ";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $DATAS[$row["id_produit"]] = $row;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getProduitActifSaison($saison) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $saison_actif = getListeSaisonPNSEBlatest("etat_saison = 1");
  $saison_now = $saison_actif['id_saison'];

  if ($saison_now == $saison){
    $sql = "SELECT id_produit, libel FROM ec_produit where etat_produit = 1 order by id_produit ";
  }else{
    $sql = "SELECT p.libel as libel, h.id_produit as id_produit FROM ec_produit_hist h INNER JOIN ec_produit p on p.id_produit = h.id_produit where id_saison = $saison order by h.id_produit";
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $DATAS[$row["id_produit"]] = $row;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function DeleteViewRapportBenefPaye(){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "DROP VIEW IF EXISTS commande";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  else{
    $dbHandler->closeConnection(true);

    return true;
  }
}

function CreateViewRapportBenefPaye($annee=null, $saison=null, $periode=null, $date_debut=null, $date_fin=null){

  global $dbHandler,$global_id_agence;
  $groupBy = "";

  $db = $dbHandler->openConnection();
  if ($periode ==1){
    $sql= "CREATE VIEW commande AS ( SELECT DISTINCT data.id_benef, data.id_saison, data.id_produit, SUM(coalesce(data.qty,0)) AS qty, SUM(coalesce(data.qty_paye,0)) AS qty_paye, data.prix_total, SUM(coalesce(data.montant_avance,0)) AS montant_avance, data.montant_solde FROM (SELECT a.id_benef, a.id_saison, b.id_produit";
  }else{
    $sql= "CREATE VIEW commande AS ( SELECT DISTINCT data.id_benef, data.id_saison, data.id_remb, data.id_produit, SUM(coalesce(data.qty,0)) AS qty, SUM(coalesce(data.qty_paye,0)) AS qty_paye, data.prix_total,";
    if ($periode == null){
      $sql .="CASE WHEN data.id_saison <> $saison THEN 0 ELSE SUM(coalesce(data.montant_avance,0)) END AS montant_avance, SUM(coalesce(data.montant_solde,0)) AS montant_solde FROM (SELECT a.id_benef, a.id_saison, b.id_produit";
    }else{
      $sql .="SUM(coalesce(data.montant_avance,0))  AS montant_avance, SUM(coalesce(data.montant_solde,0)) AS montant_solde FROM (SELECT a.id_benef, a.id_saison, b.id_produit";
    }
  }
  $date_jour = date("d");
  $date_mois = date("m");
  $date_annee = date("Y");
  $date_total = $date_jour."/".$date_mois."/".$date_annee;
  $infoPeriode=getPeriodeEC($date_total,$saison);

  if ($saison != null) {
    if ($periode ==1){
      $sql .= ", CASE WHEN a.id_saison = $saison THEN coalesce(b.quantite,0) ELSE 0 END AS qty,0 AS qty_paye";
    }else {//($periode == null || $periode > 1) && ($infoPeriode == null || $infoPeriode != null)
      if ($periode > 1 && ($infoPeriode == null || $infoPeriode != null)){ //Saison anterieure et courante
        $sql .= " , p.id_remb, CASE WHEN a.id_saison <= $saison THEN coalesce(b.quantite,0) ELSE 0 END AS qty,CASE WHEN a.id_saison <= $saison THEN coalesce(p.qtite_paye,0) ELSE 0 END AS qty_paye";
      }
      if ($periode == null && $infoPeriode == null){ //Saison anterieure et courante
        $sql .= " , p.id_remb, CASE WHEN a.id_saison <= $saison THEN coalesce(b.quantite,0) ELSE 0 END AS qty,CASE WHEN a.id_saison <= $saison THEN coalesce(p.qtite_paye,0) ELSE 0 END AS qty_paye";
      }
      if ($periode == null && $infoPeriode != null && $infoPeriode['periode']==1){ //Saison courante periode avance
        $sql .= " , p.id_remb, CASE WHEN a.id_saison <= $saison THEN coalesce(b.quantite,0) ELSE 0 END AS qty, CASE WHEN a.id_saison <= $saison THEN coalesce(b.quantite,0) ELSE 0 END AS qty_paye";
      }
      if ($periode == null && $infoPeriode != null && $infoPeriode['periode']==2){ //Saison courante periode solde
        $sql .= " , p.id_remb, CASE WHEN a.id_saison <= $saison THEN coalesce(b.quantite,0) ELSE 0 END AS qty,CASE WHEN a.id_saison <= $saison THEN coalesce(p.qtite_paye,0) ELSE 0 END AS qty_paye";
      }
    }
  }
  else {
    $sql .= ", SUM(coalesce(b.quantite,0)) AS qty";
  }
  $sql.=", coalesce(b.prix_total,0) AS prix_total";
  if ($periode != null && $periode == 1){
    $sql.= " ,coalesce(b.montant_depose,0) AS montant_avance, 0 AS montant_solde, b.date_creation"; //(SUM(coalesce(b.montant_depose,0)) OVER (PARTITION BY a.id_benef ORDER BY b.id_produit)) AS montant_avance
    $groupBy = "a.id_saison, b.quantite, b.montant_depose, b.prix_total";
  }
  if ($periode != null && $periode == 2){
    $sql.= " ,CASE WHEN a.id_saison <= $saison THEN coalesce(b.montant_depose,0) ELSE 0 END AS montant_avance, coalesce(p.montant_paye,0)  AS montant_solde, b.date_creation";
    $groupBy = "a.id_saison,p.id_remb, b.montant_depose, b.quantite, p.qtite_paye, b.montant_depose, p.montant_paye, b.prix_total";
  }
  if ($saison != null && $saison > 0 && $periode == null){
    $sql.= " ,CASE WHEN a.id_saison <= $saison THEN coalesce(b.montant_depose,0) ELSE 0 END AS montant_avance, coalesce(p.montant_paye,0) AS montant_solde";
    $groupBy = "a.id_saison,p.id_remb, b.montant_depose, b.quantite, p.qtite_paye, b.montant_depose, p.montant_paye, b.prix_total";
  }
  if ($saison == null && $periode == null){
    $sql.= " ,coalesce(b.montant_depose,0) AS montant_avance, coalesce(p.montant_paye,0)  AS montant_solde";
    $groupBy = "a.id_saison, p.id_remb, b.montant_depose, b.quantite, p.qtite_paye, b.montant_depose, p.montant_paye, b.prix_total";
  }
  $sql.=" FROM ec_commande a
INNER JOIN ec_commande_detail b ON a.id_commande = b.id_commande "; //b.date_creation, b.date_modif,

  if ($saison != null && $saison > 0 && $date_debut != null && $date_fin != null){
    $where = " WHERE a.etat_commande in (1,2,3,4,8)";

    if ($periode != null && $periode == 1){
      $sql.= $where." AND a.id_saison = $saison AND date(b.date_creation) >= date('$date_debut') AND date(b.date_creation) <= date('$date_fin')";
    }
    else if ($periode != null && $periode == 2){
      $sql.= " LEFT JOIN ec_paiement_commande p ON p.id_detail_commande = b.id_detail AND date(p.date_creation) >= date('$date_debut') AND date(p.date_creation) <= date('$date_fin') and p.etat_paye = 2".  $where." AND a.id_saison <= $saison AND p.id_remb IS NOT NULL ";
    }
    else {
      $sql.= " LEFT JOIN ec_paiement_commande p ON p.id_detail_commande = b.id_detail AND date(p.date_creation) >= date('$date_debut') AND date(p.date_creation) <= date('$date_fin') and p.etat_paye = 2".  $where." AND a.id_saison <= $saison "; //(b.date_creation >= '$date_debut' AND b.date_creation <= '$date_fin') AND
      //if ($periode == null){
        $infoSaison = getDetailSaisonCultu("id_saison = $saison");
        if ($infoPeriode != null && $infoSaison['etat_saison'] == 1 && $infoPeriode['periode'] == 1){ //pour la saison courante
          $sql.= " AND COALESCE(b.prix_total,0) = 0";
        }
        else if ($infoPeriode == null){
          $sql.= " AND COALESCE(b.prix_total,0) >= 0 AND coalesce(p.qtite_paye,0) > 0";
        }
      //}
      else{
        $sql.= " AND p.id_remb IS NOT NULL ";
      }
    }
  }
  else {
    $sql.= " LEFT JOIN ec_paiement_commande p ON p.id_detail_commande = b.id_detail";
    $groupBy = "b.montant_depose,p.id_remb,   b.quantite,p.qtite_paye,b.montant_depose, p.montant_paye,b.prix_total";
  }
  $sql.= " ORDER BY a.id_benef ASC, b.id_produit ASC)  data"; //b.date_creation, b.date_modif //retire GROUP BY a.id_benef, b.id_produit, $groupBy
  if ($periode != null && $periode == 2){
    $positionSaison=checkPositionSaison($annee,$saison);
    if ($positionSaison==1){
      $sql.=" WHERE data.montant_avance > 0 AND data.montant_solde > 0";
    }
    else{
      $sql.=" WHERE data.montant_solde > 0";
    }
  }
  if ($saison != null && $saison > 0 && $periode == null){
    $positionSaison=checkPositionSaison($annee,$saison);
    if ($positionSaison==1){
      $sql.=" WHERE data.montant_avance > 0";
    }
    else{
      $sql.=" WHERE (data.montant_avance+data.montant_solde) > 0";
    }
  }

  if ($periode == 1 ){
    $sql .= " GROUP BY data.id_benef, data.id_saison, data.id_produit, data.prix_total, data.montant_solde"; //, data.qty, data.qty_paye,
  }
  else{
    $sql .= " GROUP BY data.id_benef, data.id_saison, data.id_remb, data.id_produit, data.qty, data.qty_paye, data.prix_total, data.montant_solde";
  }


  $sql.=")";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  else{
    $dbHandler->closeConnection(true);

    return true;
  }

}

//Fonctions pour Visualisation des transactions
function count_transactions($login, $fonction, $num_beneficiaire, $date_min, $date_max, $trans_min, $trans_max, $trans_fin) {
  // Fonction qui compte le nombre de transactions renvoyées par recherche_transactions
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT COUNT(DISTINCT h.id_his) FROM ad_his h LEFT JOIN ad_ecriture e ON h.id_his = e.id_his WHERE h.id_ag=$global_id_agence AND  ";
  if ($login != NULL) $sql .= "(h.login='$login') AND  ";
  if ($fonction != NULL) $sql .= "(h.type_fonction=$fonction) AND  ";
  if ($num_beneficiaire != NULL) $sql .= "(split_part(e.info_ecriture,'-',2)='$num_beneficiaire') AND   ";
  if ($date_min != NULL) $sql .= "(DATE(h.date)>=DATE('$date_min')) AND  ";
  if ($date_max != NULL) $sql .= "(DATE(h.date)<=DATE('$date_max')) AND  ";
  if ($trans_min != NULL) $sql .= "(h.id_his>=$trans_min) AND  ";
  if ($trans_max != NULL) $sql .= "(h.id_his<=$trans_max) AND  ";

  //remove multi agence elements
  //$sql .= "(type_fonction NOT IN (92,93, 193, 194)) AND  ";
  $sql .= "(h.type_fonction IN (172,173,174,175,176,177,178,293,294,295)) AND  ";
  $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  '

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  return $row[0];
}

function get_transactions($login, $fonction, $num_beneficiaire, $date_min, $date_max, $trans_min, $trans_max, $trans_fin) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT DISTINCT h.id_his as id, h.*, e.info_ecriture FROM ad_his h LEFT JOIN ad_ecriture e ON h.id_his = e.id_his WHERE h.id_ag=$global_id_agence AND  ";
  if ($login != NULL) $sql .= "(h.login='$login') AND  ";
  if ($fonction != NULL) $sql .= "(h.type_fonction=$fonction) AND  ";
  if ($num_beneficiaire != NULL) $sql .= "(split_part(e.info_ecriture,'-',2)='$num_beneficiaire') AND   ";
  if ($date_min != NULL) $sql .= "(DATE(h.date)>=DATE('$date_min')) AND  ";
  if ($date_max != NULL) $sql .= "(DATE(h.date)<=DATE('$date_max')) AND  ";
  if ($trans_min != NULL) $sql .= "(h.id_his>=$trans_min) AND  ";
  if ($trans_max != NULL) $sql .= "(h.id_his<=$trans_max) AND  ";

  //remove multi agence elements
  //$sql .= "(type_fonction NOT IN (92,93, 193, 194)) AND  ";
  $sql .= "(h.type_fonction IN (172,173,174,175,176,177,178,293,294,295)) AND  ";

  $sql = substr($sql, 0, strlen($sql) - 6); //Suppression du ' AND  ' ou du 'WHERE '
  $sql .= "ORDER BY h.id_his DESC";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = array();
  $i = 0;

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

    //Recup les opérations financières
    $sql = "SELECT count(*) from ad_ecriture WHERE id_ag=$global_id_agence AND id_his=".$row['id_his'];
    $result2 = $db->query($sql);
    if (DB::isError($result2)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // $result2->getMessage()
    }
    $row2 = $result2->fetchrow();

    if ((! $trans_fin) || ($row2[0] > 0)) {
      $retour[$i] = $row;
      $retour[$i]['trans_fin'] = ($row2[0] > 0);
      ++$i;
    }
  }

  $dbHandler->closeConnection(true);

  return $retour;
}

/**
 * Renvoie les détails financiers de plusieurs transactions pour le rapport visualisation des transactions
 * @param $login, $fonction, $num_client, $date_min, $date_max, $trans_min, $trans_max, $trans_fin
 *
 * @return array On renvoie un tableau de la forme array(id_his=>value, type_fonction=>value, id_client=>value, login=>value,
 *               infos=>value, date=>value,
 *               ecritures=><B>array</B>(id_ecriture=><B>array</B>(...détails écriture..., mouvements=><B>array</B>(... détails mouvements...))), ad_his_ext => array(* FROM ad_his_ext)
 */
function get_transactions_details($login, $fonction, $num_beneficiaire, $date_min, $date_max, $trans_min, $trans_max, $trans_fin) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT m.*, a.*, b.libel_jou,h.* FROM ad_his h, ad_ecriture a, ad_mouvement m, ad_journaux b WHERE h.id_ag=$global_id_agence   ";
  if ($login != NULL) $sql .= " AND (h.login='$login')  ";
  if ($fonction != NULL) $sql .= " AND (h.type_fonction=$fonction) ";
  if ($num_beneficiaire != NULL) $sql .= " AND (split_part(a.info_ecriture,'-',2)='$num_beneficiaire')  ";
  if ($date_min != NULL) $sql .= " AND (DATE(h.date)>=DATE('$date_min')) ";
  if ($date_max != NULL) $sql .= " AND (DATE(h.date)<=DATE('$date_max')) ";
  if ($trans_min != NULL) $sql .= " AND (h.id_his>=$trans_min) ";
  if ($trans_max != NULL) $sql .= " AND (h.id_his<=$trans_max) ";

  //remove multi agence elements
  $sql .= " AND (h.type_fonction IN (172,173,174,175,176,177,178,293,294,295)) ";

  $sql .= " AND h.id_his = a.id_his AND a.id_ecriture = m.id_ecriture AND a.id_jou = b.id_jou  ";
  $sql .= " AND h.id_ag = a.id_ag AND a.id_ag = m.id_ag AND m.id_ag = b.id_ag  ";
  $sql .= "ORDER BY h.id_his DESC";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($row['cpte_interne_cli'] != NULL) {
      $InfosCompte = getAccountDatas($row['cpte_interne_cli']);
      $row['cpte_interne_cli'] = $InfosCompte['num_complet_cpte'];
    }
    array_push($retour, $row);
  }
  $dbHandler->closeConnection(true);
  return $retour;
}

/**
 * Renvoie les détails financiers d'une transaction au niveau de la visualisation
 * @author Unknown
 * @since 1.0
 * @param int $id_trans Transaction dans l'historique pour laquelle on veut les détails
 * @return array On renvoie un tableau de la forme array(id_his=>value, type_fonction=>value, id_client=>value, login=>value,
 *               infos=>value, date=>value,
 *               ecritures=><B>array</B>(id_ecriture=><B>array</B>(...détails écriture..., mouvements=><B>array</B>(... détails mouvements...))), ad_his_ext => array(* FROM ad_his_ext)
 */
function getDetails_transaction($id_trans) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  // Récupère les infos sur la fonction dans l'historique
  $sql = "SELECT * FROM ad_his WHERE id_ag=$global_id_agence AND id_his=$id_trans";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // Aucune ou plusieurs occurences de la transaction
  }

  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);

  // Récupère l'en-tête de l'écriture dans ad_ecriture
  $sql = "SELECT a.*, b.libel_jou ";
  $sql .= "FROM ad_ecriture a, ad_journaux b ";
  $sql .= "WHERE a.id_ag = b.id_ag AND a.id_ag = $global_id_agence AND a.id_jou = b.id_jou and id_his = $id_trans ";
  $sql .= "ORDER BY a.id_ecriture;";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour['ecritures'] = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $retour['info_ecriture'] = $row['info_ecriture'];
    $retour['ecritures'][$row['id_ecriture']] = $row;
  }

  // Récupération du détail des mouvements comptables
  foreach ($retour['ecritures'] as $key => $value) {
    $sql = "SELECT * FROM ad_mouvement WHERE id_ag = $global_id_agence AND id_ecriture = $key ORDER BY sens DESC;";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $retour['ecritures'][$key]['mouvements'] = array();

    $count = 1; // Pour que les mvts comptables soient numérotés de 1 à n

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      if ($row['cpte_interne_cli'] != NULL) {
        $InfosCompte = getAccountDatas($row['cpte_interne_cli']);
        $row['num_complet_cpte'] = $InfosCompte['num_complet_cpte'];
      }
      $retour['ecritures'][$key]['mouvements'][$count] = $row;
      $count++;
    }
  }

  // Recherche des infos éventuelles dans ad_his_ext si appliquable
  if ($retour["id_his_ext"] != "") {
    $sql = "SELECT * FROM ad_his_ext WHERE id_ag = $global_id_agence AND id = ".$retour["id_his_ext"];

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $INFOS_EXT = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $retour["infos_ext"] = $INFOS_EXT;
  }

  $dbHandler->closeConnection(true);
  return $retour;
}
/**
 * Fonction pour recuperer l'id du libel d'un operation (Ajout commande, paiement des commandes...) PNSEB
 * PARAM : $type_oper - type operation PNSEB
 * Renvoie l'id libel de l'operation en parametre
 */
function getIdLibelOperationPNSEB($type_oper=0){
  global $dbHandler, $global_id_agence;


  $db = $dbHandler->openConnection();

  // Recupere l'id de l'operation
  $sql = "SELECT libel_ope FROM ad_cpt_ope WHERE type_operation = $type_oper AND id_ag=$global_id_agence";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($type_oper > 0) {
    if ($result->numRows() == 0) {
      // Il n'y a pas d'association pour cette opération
      $dbHandler->closeConnection(false);
      return new ErrorOBj(ERR_NO_ASSOCIATION, "L'opération $type_oper n'existe pas");
    } else {
      $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
      $dbHandler->closeConnection(true);
      return $row['libel_ope'];
    }
  }
}
/**
 * Fonction renvoie la date suivante sinon date du jour
 * PARAM : $dateAComparer type date
 * On renvoie la date suivante si $date1 < $date2 et meme mois sinon on renvoie la date du jour
 */
function ifSameMonthGetDate($dateAComparer){
  $dateDuJour = date("d")."/".date("m")."/".date("Y");
  //si meme mois et day < date du jour
  if ($dateAComparer[0]==date("m") && $dateAComparer[1]>date("d")){
    $dateDuJour = date("d/m/Y",mktime(0, 0, 0, (int)$dateAComparer[0], (double)$dateAComparer[1]+1, (double)$dateAComparer[2]));
  }
  return $dateDuJour;
}


function getIdSaison($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_saison_culturale WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_saison"]] = $row;
  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getTotalSituation($date_debut, $date_fin, $id_annee,$id_saison, $period){
  global $dbHandler,$global_id_agence;
  if ($date_fin == null){
    $date_fin = date('d/m/y');
  }
  $db = $dbHandler->openConnection();
  $sql = "SELECT (sum(mnt_avance) + sum(mnt_solde)) as total FROM getdatarapportproduit('$date_debut','$date_fin',$id_annee,$id_saison,$period)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $DATAS;

}

function getTotalSoldeSituation($date_debut, $date_fin, $id_annee,$id_saison, $period){
  global $dbHandler,$global_id_agence;
  if ($date_fin == null){
    $date_fin = date('d/m/y');
  }
  $db = $dbHandler->openConnection();
  $sql = "SELECT sum(mnt_solde) as total_solde FROM getdatarapportproduit('$date_debut','$date_fin',$id_annee,$id_saison,$period)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $DATAS;

}
function getDetailSituation($date_debut, $date_fin, $id_annee,$id_saison, $period){

  global $dbHandler,$global_id_agence;
  if ($date_fin == null){
    $date_fin = date('d/m/y');
  }
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM getdatarapportproduit('$date_debut','$date_fin',$id_annee,$id_saison,$period) order by id_produit";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  $produit = 0;
  $mnt_avance = 0;
  $mnt_solde = 0;
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    if ($produit != $row["id_produit"]){
      $mnt_avance = 0;
      $mnt_solde = 0;
      $mnt_avance = $row["mnt_avance"];
      $mnt_solde = $row["mnt_solde"];
      $produit = $row["id_produit"];
      $DATAS[$row["id_produit"]]["libel_produit"] = $row["libel_prod"];
      $DATAS[$row["id_produit"]]["mnt_avance"] = $mnt_avance;
      $DATAS[$row["id_produit"]]["mnt_solde"] = $mnt_solde;
    }else {
      $mnt_avance = 0;
      $mnt_solde = 0;
      $mnt_avance = $row["mnt_avance"];
      $mnt_solde = $row["mnt_solde"];
      $DATAS[$row["id_produit"]]["libel_produit"] = $row["libel_prod"];
      $DATAS[$row["id_produit"]]["mnt_avance"] += $mnt_avance;
      $DATAS[$row["id_produit"]]["mnt_solde"] += $mnt_solde;
    }
  $dbHandler->closeConnection(true);
  return $DATAS;
}
function getDetailSituationTest($date_debut, $date_fin, $id_annee,$id_saison, $period){

  global $dbHandler,$global_id_agence;
  if ($date_fin == null){
    $date_fin = date('d/m/y');
  }
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM getdatarapportproduit('$date_debut','$date_fin',$id_annee,$id_saison,$period)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  //$produit_actif = getListeProduitPNSEB(null,true); // " etat_produit = 1"
  //Recupere les produits par rapport au saison
  $produit_actif = getListeProduitSaison(null,true,$id_saison);
  $DATA_PRODUIT = array();
  while (list($key_list, $COM_list) = each($produit_actif)) {
    $DATA_PRODUIT[$key_list]=$COM_list["libel"];
  }
  $total = 0;
  $data_agence = getAgenceDatas($global_id_agence);

  $id_ag_temp_check = '';
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $id_ag_temp = $global_id_agence.$row["nom_province"].$row["nom_commune"];
    $DATAS[$id_saison]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["nom_province"]=  $row["nom_province"];
    $DATAS[$id_saison]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["nom_commune"]=  $row["nom_commune"];
    $DATAS[$id_saison]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["agence"]= $data_agence['libel_ag'];
    $DATAS[$id_saison]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["id"] = $row["id_produit"];
    $DATAS[$id_saison]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["libel"] = $row["libel_prod"];
    if ($period ==1){
      $DATAS[$id_saison]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["qtite"] = $row["qtite"];
    }else if ($period == 2){
      $DATAS[$id_saison]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["qtite"] = $row["qtite_paye"];
    }else{
      $DATAS[$id_saison]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["qtite"] = $row["qtite_paye"];
    }
    if ($id_ag_temp_check != $id_ag_temp){
      $id_ag_temp_check = $id_ag_temp;
      $total =0;
    }

    if ($period == 1){
      $total += $row["mnt_avance"];
     }
    else if ($period == 2){
      $total += $row["mnt_solde"];
    }
    else{
      $total += $row["mnt_avance"] +$row["mnt_solde"];
    }
    $DATAS[$id_saison]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["total"] = $total;
  }

  $DATAS[$id_saison]["item_produit"] = $DATA_PRODUIT;
  $dbHandler->closeConnection(true);
  return $DATAS;
}
function checkPositionSaison($annee,$saison){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "select id_saison from ec_saison_culturale where id_annee = $annee order by id_saison";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  $count = 0;

  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $count++;
    if ($saison == $row['id_saison']){
      $dbHandler->closeConnection(true);
      return $count;
    }
  }
}

function getNbreProduitActif(){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT count(*) FROM ec_produit WHERE etat_produit = 1;";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row = $result->fetchrow();
  return $row[0];
}
function getListeStockBa($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_stock_ba WHERE id_ag=$global_id_agence ";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $sql .="order by id_produit";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    /*if ($tous === true){
      $DATAS[$row["id_produit"]] = $row;
    }
    else{*/
      $DATAS[$row["id_produit"]] = $row;
    //}
  }

  $dbHandler->closeConnection(true);

  return $DATAS;
}


function getSpecificStock($id_prod,$where =null){

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ec_stock_ba WHERE id_ag=$global_id_agence AND id_produit = $id_prod";

  if ($where != null) {
    $sql .= " AND " . $where;
  }

  $sql .= " ORDER BY id";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_produit"]] = $row['qtite_ba'];
  $dbHandler->closeConnection(true);
  return $DATAS;

}
function getNumLivraison($id_annee,$id_saison,$condi = null){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT  distinct numero_livraison, date_livraison from ec_livraison_ba where id_annee = $id_annee AND id_saison = $id_saison";
  if ($condi != null){
    $sql .= " AND ".$condi;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["numero_livraison"]] = $row;
  $dbHandler->closeConnection(true);
  return $DATAS;
}
function getDetailLivraison($id_annee,$id_saison,$num_livraison,$condi = null){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_produit, qtite_ba from ec_livraison_ba where id_annee = $id_annee AND id_saison = $id_saison AND numero_livraison = '$num_livraison'";
  if ($condi != null){
    $sql .= " AND ".$condi;
  }
  $sql .= " ORDER BY id_produit";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_produit"]] = $row["qtite_ba"];
  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getLivraison($id_annee,$id_saison,$condi = null){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ec_livraison_ba where id_annee = $id_annee AND id_saison = $id_saison";
  if ($condi != null){
    $sql .= " AND ".$condi;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["numero_livraison"]] = $row['qtite_ba'];
  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getAgentStock($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db11 = $dbHandler->openConnection();
  $sql_stock = "SELECT * from ec_agent_ba WHERE id_ag=$global_id_agence";

  if ($whereCond != null) {
    $sql_stock .= " AND ".$whereCond;
  }
  //$sql .=" order by date_fin DESC limit 1";
  $result_stock=$db11->query($sql_stock);
  if (DB::isError($result_stock)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result_stock->getMessage());
  }
  if ($result_stock->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result_stock->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_agent"]] = $row;
  $dbHandler->closeConnection(true);

  return $DATAS;
}


function getAgentStockSpecific($id_agent, $id_prod) {

  global $dbHandler,$global_id_agence;

  $db11 = $dbHandler->openConnection();
  $sql_stock = "SELECT * from ec_agent_ba WHERE id_ag=$global_id_agence AND id_agent = '$id_agent' AND id_produit = $id_prod";

  //$sql .=" order by date_fin DESC limit 1";
  $result_stock=$db11->query($sql_stock);
  if (DB::isError($result_stock)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result_stock->getMessage());
  }
  if ($result_stock->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result_stock->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getInfoUtilisateur($id_util) {

  global $dbHandler,$global_id_agence;

  $db11 = $dbHandler->openConnection();
  $sql_stock = "SELECT * from ad_uti WHERE id_ag=$global_id_agence AND id_utilis = $id_util";

  //$sql .=" order by date_fin DESC limit 1";
  $result_stock=$db11->query($sql_stock);
  if (DB::isError($result_stock)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result_stock->getMessage());
  }
  if ($result_stock->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result_stock->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function CheckCountAgentStock(){

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql="select count(*) from ec_agent_ba where qtite_ba > 0";
  $result= $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getLocRapportSituation($id_saison){

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql="select distinct b.id_province, l1.libel as libel_province, b.id_commune, l2.libel as libel_commune
from ec_beneficiaire b
INNER JOIN ec_localisation l1 on l1.id = b.id_province
INNER JOIN ec_localisation l2 on l2.id = b.id_commune
INNER JOIN ec_commande c on c.id_benef = b.id_beneficiaire
where c.id_saison = $id_saison";
  $result= $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}


function countBenefSoldeRapportSituation($id_saison,$province,$commune,$criteres){

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql="select count( distinct c.id_benef) as nbre_agri
from ec_paiement_commande p
INNER JOIN ec_commande_detail d ON d.id_detail = p.id_detail_commande
INNER JOIN ec_commande c ON c.id_commande = d.id_commande
INNER JOIN ec_beneficiaire b on b.id_beneficiaire = c.id_benef
INNER JOIN ec_localisation loc on loc.id = b.id_commune
INNER JOIN ec_localisation loc1 on loc1.id = b.id_province
 where p.date_creation >= date('".$criteres['Date debut']."')  and p.date_creation <= date('".$criteres['Date fin']."') and  loc1.libel ='$province' and loc.libel= '$commune' and p.etat_paye = 2";
  $result= $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}
function countBenefAvanceRapportSituation($id_saison,$province,$commune){

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql="select count( distinct c.id_benef) as nbre_agri
from ec_commande c
INNER JOIN ec_beneficiaire b on b.id_beneficiaire = c.id_benef
INNER JOIN ec_localisation loc on loc.id = b.id_commune
INNER JOIN ec_localisation loc1 on loc1.id = b.id_province
 where c.id_saison = $id_saison and loc1.libel ='$province' and loc.libel= '$commune' and c.etat_commande not in (7,5,6)";
  $result= $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}
function getLoginAll($whereCond=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * from ad_log WHERE id_ag = $global_id_agence";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["login"]] = $row;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getLoginDelestage($whereCond=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT distinct id_agent from ec_agent_ba WHERE id_ag = $global_id_agence";

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["id_agent"]] = $row;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getRepartitionQtiteZone($annee,$saison,$date_debut, $date_fin)
{
  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $db = $dbHandler->openConnection();

  if (empty($date_debut)) {
    $date_debut = date("Y") . "-" . date("m") . "-" . date("d");
  } elseif (strpos($date_debut, '/')) {
    $date_debut = php2pg($date_debut);
  }

  if (empty($date_fin)) {
    $date_fin = date("Y") . "-" . date("m") . "-" . date("d");
  } elseif (strpos($date_fin, '/')) {
    $date_fin = php2pg($date_fin);
  }
  if (empty($saison)) {
    $saison = 0;
  }

  $sql = "select * from getdatarapportqtitezone($annee,$saison,'$date_debut','$date_fin') order by nom_province, nom_commune, nom_coopec, nom_zone, id_prod";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__, __LINE__, __FUNCTION__, _("DB") . ": " . $result->getMessage());
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }


  $produit_actif = getProduitActifSaison($saison);
  $DATA_PRODUIT = array();
  while (list($key_list, $COM_list) = each($produit_actif)) {
    $DATA_PRODUIT[$key_list] = $COM_list["libel"];
  }


  $DATAS = array();
  while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
    $DATAS[$saison]["province"][$row["id_province"]]["nom_province"] = $row["nom_province"];
    $DATAS[$saison]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["nom_commune"] = $row["nom_commune"];
    $DATAS[$saison]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["nom_coopec"] = $row["nom_coopec"];
    $DATAS[$saison]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["nom_zone"] = $row["nom_zone"];
    $DATAS[$saison]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["id_prod"][$row["id_prod"]]["id_prod"] = $row["id_prod"];
    $DATAS[$saison]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["id_prod"][$row["id_prod"]]['qtite'] = $row["qtite"];
    $DATAS[$saison]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["id_prod"][$row["id_prod"]]['montant'] = $row["montant"];
    $DATAS[$saison]["item_produit"] = $DATA_PRODUIT;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;

}

function calculBeneficiaireCommande($id_saison, $libel_zone){
  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $db = $dbHandler->openConnection();

  $sql = "select distinct  count(c.id_benef) as nb_agri from ec_beneficiaire b
INNER JOIN ec_commande c on c.id_benef = b.id_beneficiaire
INNER JOIN ec_localisation l on l.id = b.id_zone
where c.id_saison = $id_saison and l.libel = '$libel_zone'";

  $result= $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function countBenefRapportRepartitionZone($id_saison,$libel_zone){

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql="select count( distinct c.id_benef) as nbre_agri
from ec_commande c
INNER JOIN ec_beneficiaire b ON b.id_beneficiaire = c.id_benef
INNER JOIN ec_localisation l on l.id = b.id_zone
WHERE c.id_saison = $id_saison
and c.etat_commande not in (7,5,6)
and l.libel = '$libel_zone'";
  $result= $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $DATAS;
}

 function getPeriodeEC($date_total,$saison = null){
   global $dbHandler,$global_id_agence;

   $db = $dbHandler->openConnection();
   $sql = "select (case when date(date_debut_solde) > date('$date_total') then 1 else 2 end) as period, id_saison,id_annee, (case when date(date_debut_solde) > date('$date_total') then date(date_debut) else date(date_debut_solde) end) as date_debut, (case when date(date_debut_solde) > date('$date_total') then date(date_debut_solde) else date(date_fin_solde) end) as date_fin  from ec_saison_culturale where id_ag= numagc() AND date_debut <= date('$date_total') AND date_fin >= date('$date_total')";
   if ($saison != null){
   	$sql .= " AND id_saison = ".$saison;
   }

   $result= $db->query($sql);

   if (DB::isError($result)) {
     $dbHandler->closeConnection(false);
     signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
   }
   if ($result->numRows() == 0)
   {
     $dbHandler->closeConnection(true);
     return NULL;
   }
  $exos = array();
   while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
    $exos['periode'] = $row['period'];
    $exos['id_saison'] = $row['id_saison'];
    $exos['id_annee'] = $row['id_annee'];
    $exos['date_debut'] = $row['date_debut'];
    $exos['date_fin'] = $row['date_fin'];

  }
  return $exos;
}

/**
 *
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport engrais chimiques list beneficiaire payant globale
 *
 * @param number $annee
 * @param number $saison
 * @param number $periode
 * @param date $date debut
 * @param date $date fin
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */
function get_rapport_list_beneficiaire_payant_data_globale($saison, $periode)
{
  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $db = $dbHandler->openConnection();

  $sql_benef = "select * from ec_benef_paye order by id_ag,id_benef,id_prod";
  $result_benef = $db->query($sql_benef);

  if (DB::isError($result_benef)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result_benef->getMessage());
  }

  if ($result_benef->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }


  $produit_actif = getProduitActif();
  $DATA_PRODUIT = array();
  while (list($key_list, $COM_list) = each($produit_actif)) {
    $DATA_PRODUIT[$key_list]=$COM_list["libel"];
  }

  $DATAS=array();
  while ( $row = $result_benef->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["nom_province"]=$row["nom_province"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["nom_commune"] = $row["nom_commune"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["nom_coopec"] = $row["nom_coopec"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["nom_zone"]=$row["nom_zone"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]["nom_colline"]=$row["nom_colline"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["nom_prenom"]=$row["nom_prenom"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["id_card"]=$row["id_card"];
    //$DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["montant_avance"]=$row["montant_avance"];
    //$DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["montant_solde"]=$row["montant_solde"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["libel"] = $row["libel"];
    if ($periode == 1){
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["quantite"]= $row["quantite"];
    }else{
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["quantite"] += $row["qtite_paye"];
    }
    if ($periode == 1){
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["total"] = $row["montant_avance"];
    }else if ($periode == 2){
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["total"] = $row["montant_solde"];
    }else{
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["total"] = $row["montant_avance"] + $row["montant_solde"];
    }

    $DATAS[$saison]["item_produit"] = $DATA_PRODUIT;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getRepartitionQtiteZoneGlobal($annee,$saison,$date_debut, $date_fin){

  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $db = $dbHandler->openConnection();

  if (empty($date_debut)) {
    $date_debut = date("Y") . "-" . date("m") . "-" . date("d");
  } elseif (strpos($date_debut, '/')) {
    $date_debut = php2pg($date_debut);
  }

  if (empty($date_fin)) {
    $date_fin = date("Y") . "-" . date("m") . "-" . date("d");
  } elseif (strpos($date_fin, '/')) {
    $date_fin = php2pg($date_fin);
  }
  if (empty($saison)) {
    $saison = 0;
  }

  $sql = "select * from ec_repartition_zone order by nom_province, nom_commune, nom_coopec, nom_zone, id_prod";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__, __LINE__, __FUNCTION__, _("DB") . ": " . $result->getMessage());
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }


  $produit_actif = getProduitActif();
  $DATA_PRODUIT = array();
  while (list($key_list, $COM_list) = each($produit_actif)) {
    $DATA_PRODUIT[$key_list] = $COM_list["libel"];
  }


  $DATAS = array();
  while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["nom_province"] = $row["nom_province"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["nom_commune"] = $row["nom_commune"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["nom_coopec"] = $row["nom_coopec"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["nom_zone"] = $row["nom_zone"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["id_prod"][$row["id_prod"]]["id_prod"] = $row["id_prod"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["id_prod"][$row["id_prod"]]['qtite'] = $row["qtite"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["id_prod"][$row["id_prod"]]['montant'] = $row["montant"];
    $DATAS[$saison][$row["id_ag"]]["item_produit"] = $DATA_PRODUIT;
  }

  $dbHandler->closeConnection(true);
  return $DATAS;

}

function getDetailSituationGlobal($date_debut, $date_fin, $id_annee,$id_saison, $period){

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_situation_paiement";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $produit_actif = getListeProduitPNSEB(" etat_produit = 1",true);
  $DATA_PRODUIT = array();
  while (list($key_list, $COM_list) = each($produit_actif)) {
    $DATA_PRODUIT[$key_list]=$COM_list["libel"];
  }
  $total = 0;
  $data_agence = getAgenceDatas($global_id_agence);

  $DATAS=array();
  //$id_ag_temp= 0;
  $id_ag_temp_check = '';
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $id_ag_temp = $row["id_ag"].$row["nom_province"].$row["nom_commune"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["nom_province"]=  $row["nom_province"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["nom_commune"]=  $row["nom_commune"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["agence"]=  $row["libel_ag"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["id"] = $row["id_produit"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["libel"] = $row["libel_prod"];
    if ($period ==1){
      $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["qtite"] = $row["qtite"];
    }else if ($period == 2){
      $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["qtite"] = $row["qtite_paye"];
    }else{
      $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["qtite"] = $row["qtite_paye"];
    }

    if ($id_ag_temp_check != $id_ag_temp){
      $id_ag_temp_check = $id_ag_temp;
      $total =0;
    }

    if ($period == 1){
      $total += $row["mnt_avance"];
    }
    else if ($period == 2){
      $total += $row["mnt_solde"];
    }
    else{
      $total += $row["mnt_avance"] +$row["mnt_solde"];
    }

    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["total"] = $total;

  }
  $DATAS[$id_saison]["item_produit"] = $DATA_PRODUIT;

  $dbHandler->closeConnection(true);
  return $DATAS;
}

function truncateTable($table_name){
  global $dbHandler,$global_monnaie,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "TRUNCATE TABLE $table_name ";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  else{
    $dbHandler->closeConnection(true);

    return true;
  }
}

function getDetailSituationHist($date_debut, $date_fin, $id_annee,$id_saison, $period){

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ec_situation_paiement_historique";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  //$produit_actif = getListeProduitPNSEB(" etat_produit = 1",true);
  //Recupere les produits par rapport au saison
  $produit_actif = getListeProduitSaison(null,true,$id_saison);
  $DATA_PRODUIT = array();
  while (list($key_list, $COM_list) = each($produit_actif)) {
    $DATA_PRODUIT[$key_list]=$COM_list["libel"];
  }
  $total = 0;
  $data_agence = getAgenceDatas($global_id_agence);

  $DATAS=array();

  $id_ag_temp_check ='';
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $id_ag_temp = $row["id_ag"].$row["nom_province"].$row["nom_commune"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["nom_province"]=  $row["nom_province"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["nom_commune"]=  $row["nom_commune"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["agence"]=  $row["libel_ag"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["id"] = $row["id_produit"];
    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["libel"] = $row["libel_prod"];
    if ($period ==1){
      $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["qtite"] = $row["qtite"];
    }else if ($period == 2){
      $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["qtite"] = $row["qtite_paye"];
    }else{
      $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["produit"][$row["id_produit"]]["qtite"] = $row["qtite_paye"];
    }

    if ($id_ag_temp_check != $id_ag_temp){
      $id_ag_temp_check = $id_ag_temp;
      $total =0;
    }

    if ($period == 1){
      $total += $row["mnt_avance"];
    }
    else if ($period == 2){
      $total += $row["mnt_solde"];
    }
    else{
      $total += $row["mnt_avance"] +$row["mnt_solde"];
    }

    $DATAS[$id_saison][$row["id_ag"]]["agence"][$global_id_agence.$row["nom_province"]][$global_id_agence.$row["nom_commune"]]["total"] = $total;

  }
  $DATAS[$id_saison]["item_produit"] = $DATA_PRODUIT;

  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getRepartitionQtiteZoneHist($annee,$saison,$date_debut, $date_fin){

  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $db = $dbHandler->openConnection();

  if (empty($date_debut)) {
    $date_debut = date("Y") . "-" . date("m") . "-" . date("d");
  } elseif (strpos($date_debut, '/')) {
    $date_debut = php2pg($date_debut);
  }

  if (empty($date_fin)) {
    $date_fin = date("Y") . "-" . date("m") . "-" . date("d");
  } elseif (strpos($date_fin, '/')) {
    $date_fin = php2pg($date_fin);
  }
  if (empty($saison)) {
    $saison = 0;
  }

  $sql = "select * from ec_repartition_zone_historique order by nom_province, nom_commune, nom_coopec, nom_zone, id_prod";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__, __LINE__, __FUNCTION__, _("DB") . ": " . $result->getMessage());
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }


  $produit_actif = getProduitActif();
  $DATA_PRODUIT = array();
  while (list($key_list, $COM_list) = each($produit_actif)) {
    $DATA_PRODUIT[$key_list] = $COM_list["libel"];
  }


  $DATAS = array();
  while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["nom_province"] = $row["nom_province"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["nom_commune"] = $row["nom_commune"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["nom_coopec"] = $row["nom_coopec"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["nom_zone"] = $row["nom_zone"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["id_prod"][$row["id_prod"]]["id_prod"] = $row["id_prod"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["id_prod"][$row["id_prod"]]['qtite'] = $row["qtite"];
    $DATAS[$saison][$row["id_ag"]]["province"][$row["id_province"]]["commune"][$row["id_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["id_zone"]]["id_prod"][$row["id_prod"]]['montant'] = $row["montant"];
    $DATAS[$saison][$row["id_ag"]]["item_produit"] = $DATA_PRODUIT;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;

}

/**
 *
 * Fonction qui renvoie l'ensemble des informations dont on a besoin dans le rapport engrais chimiques list beneficiaire payant globale historique
 *
 * @param number $annee
 * @param number $saison
 * @param number $periode
 * @param date $date debut
 * @param date $date fin
 * @return Array $DATAS Tableau de données à afficher sur la rapport
 */
function get_rapport_list_beneficiaire_payant_data_globale_his($saison, $periode)
{
  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $db = $dbHandler->openConnection();

  $sql = "select * from ec_benef_paye_historique order by id_ag,id_benef,id_prod";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }


  $produit_actif = getProduitActif();
  $DATA_PRODUIT = array();
  while (list($key_list, $COM_list) = each($produit_actif)) {
    $DATA_PRODUIT[$key_list]=$COM_list["libel"];
  }


  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["nom_province"]=$row["nom_province"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["nom_commune"] = $row["nom_commune"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["nom_coopec"] = $row["nom_coopec"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["nom_zone"]=$row["nom_zone"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]["nom_colline"]=$row["nom_colline"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["nom_prenom"]=$row["nom_prenom"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["id_card"]=$row["id_card"];
    //$DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["montant_avance"]=$row["montant_avance"];
    //$DATAS[$saison]["province"][$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["montant_solde"]=$row["montant_solde"];
    $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["libel"] = $row["libel"];
    if ($periode == 1){
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["quantite"]= $row["quantite"];
    }else{
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["quantite"] += $row["qtite_paye"];
    }
    if ($periode == 1){
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["total"] = $row["montant_avance"];
    }else if ($periode == 2){
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["total"] = $row["montant_solde"];
    }else{
      $DATAS[$saison]["province"][$row["id_ag"].$row["nom_province"]]["commune"][$row["nom_commune"]]["coopec"][$row["nom_coopec"]]["zone"][$row["nom_zone"]]["colline"][$row["nom_colline"]]['benef'][$row["id_benef"]]["produit"][$row["id_prod"]]["total"] = $row["montant_avance"] + $row["montant_solde"];
    }

    $DATAS[$saison]["item_produit"] = $DATA_PRODUIT;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;

}

/**
 * Liste des produits des engrais chimique par saison
 * @param : where condition, ramener tous les detailes ou seulement le libel et pour quelle saison
 * @return array tableau contenant la liste des produits Angrais Chimiques
 */
function getListeProduitSaison($whereCond=null,$tous=false,$idSaison=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  //Verifie si la saison est une saison anterieure ou une saison actuelle ouverte
  $saison = getListeSaisonPNSEB("id_saison=".$idSaison." AND etat_saison = 2");
  $isSaisonAnterieure = false;
  if ($saison != null){
    $isSaisonAnterieure = true;
  }

  $sql = "SELECT * FROM ec_produit WHERE etat_produit = 1 AND id_ag=$global_id_agence ";
  if ($isSaisonAnterieure === true && $idSaison != null){
    $sql = "SELECT * FROM ec_produit e INNER JOIN ec_produit_hist eh ON e.id_produit = eh.id_produit WHERE eh.id_saison = $idSaison AND e.id_ag=$global_id_agence";
  }

  if ($whereCond != null) {
    $sql .= " AND " . $whereCond;
  }
  if ($isSaisonAnterieure === true && $idSaison != null){
    $sql .="order by e.id_produit";
  }
  else{
    $sql .="order by id_produit";
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    if ($tous === true){
      $DATAS[$row["id_produit"]] = $row;
    }
    else{
      $DATAS[$row["id_produit"]] = $row['libel'];
    }
  }

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function checkStockAgentParFlux($whereCond=null,$id_saison) {

  global $dbHandler,$global_id_agence;

  $db11 = $dbHandler->openConnection();
  $sql_stock = "SELECT a.* from ec_agent_ba a INNER JOIN ec_flux_ba b on b.id_agent = a.id_agent WHERE a.id_ag=$global_id_agence AND b.id_saison = $id_saison";

  if ($whereCond != null) {
    $sql_stock .= " AND b.".$whereCond;
  }
  //$sql .=" order by date_fin DESC limit 1";
  $result_stock=$db11->query($sql_stock);
  if (DB::isError($result_stock)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result_stock->getMessage());
  }
  if ($result_stock->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS=array();
  while ( $row = $result_stock->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_agent"]] = $row;
  $dbHandler->closeConnection(true);

  return $DATAS;
}
?>