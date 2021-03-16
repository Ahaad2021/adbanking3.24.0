<?php

/**
 * Renvoie une liste des transfert eBanking
 * 
 * @return array Tableau associatif avec les prestataires eWallet trouvés.
 */
function getListEbankingTransfert() {
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = sprintf("SELECT * FROM ad_ebanking_transfert WHERE id_ag=%d ORDER BY service ASC, action ASC", $global_id_agence);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__, __LINE__, __FUNCTION__);
        return NULL;
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $tmp_arr = array();
    $include = array('TRANSFERT_CPTE' => 'Transfert compte à compte', 'TRANSFERT_EWALLET' => 'Transfert eWallet', 'TRANSFERT_EWALLET_DEPOT' => 'Transfert eWallet depot', 'TRANSFERT_EWALLET_RETRAIT' => 'Transfert eWallet retrait');
    while ($ebanking_transfert = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

        $tmp_arr[$ebanking_transfert["id_ebanking_transfert"]] = sprintf('%s - %s (%s)', ucfirst(trim($ebanking_transfert['service'])), $include[$ebanking_transfert['action']], strtoupper($ebanking_transfert['devise']));
    }

    return $tmp_arr;
}

/**
 * Renvoie toutes les devises
 * 
 * @return array Tableau associatif avec les devises trouvés.
 */
function getListDevises() {
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = sprintf("SELECT * FROM devise WHERE id_ag=%d ORDER BY devise ASC", $global_id_agence);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__, __LINE__, __FUNCTION__);
        return NULL;
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $tmp_arr = array();
    while ($devise = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $tmp_arr[$devise["code_devise"]] = sprintf("%s (%s)", $devise["libel_devise"], $devise["code_devise"]);
    }

    return $tmp_arr;
}

/**
 * Bloque un montant sur un compte (souvent il s'agit du blocage d'une garantie)
 * @param int $id_cpte Id du compte sur lequel le montant doit etre bloqué
 * @param float $mnt Montant à bloquer
 * @return bool 1
 * @since 1.0
 */
function bloqMontantCpte($id_cpte, $mnt) {

  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  // Récupérer le montant actuellement bloqué sur le compte
  $sql = "SELECT mnt_bloq FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte=$id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    return new ErrorObj(ERR_GENERIQUE, $result);
  }
  $tmprow = $result->fetchrow();
  $old_mnt_bloq = $tmprow[0];

  // Ajout du nouveau montant
  $new_mnt_bloq = $old_mnt_bloq + $mnt;

  // Mise à  jour de la DB
  $sql = "UPDATE ad_cpt set mnt_bloq=$new_mnt_bloq where id_ag=$global_id_agence AND id_cpte=$id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    return new ErrorObj(ERR_GENERIQUE, $result);
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/**
 * Débloque un montant sur un compte (souvent il s'agit du déblocage d'une garantie)
 * @param int $id_cpte Id du compte sur lequel le montant doit etre débloqué
 * @param float $mnt Montant à débloquer
 * @return bool 1
 * @since 1.0
 */
function debloqMontantCpte($id_cpte, $mnt) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  // Récupérer le montant actuellement bloqué sur le compte
  $sql = "SELECT mnt_bloq FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte=$id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    return new ErrorObj(ERR_GENERIQUE, $result);
  }

  $tmprow = $result->fetchrow();
  $old_mnt_bloq = $tmprow[0];
  // Retire le montant à  débloquer
  $new_mnt_bloq = $old_mnt_bloq - $mnt;
  // Mise à jour de la DB
  $sql = "UPDATE ad_cpt set mnt_bloq=$new_mnt_bloq where id_ag=$global_id_agence AND id_cpte=$id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    return new ErrorObj(ERR_GENERIQUE, $result);
  }
  
  $dbHandler->closeConnection(true);
  
  return new ErrorObj(NO_ERR);
}

/**
 * Vérifie si le montant est bloqué
 * @param $id_cpte
 * @param $mnt
 * @return ErrorObj
 */
function checkBloqMontantCpte($id_cpte, $mnt, $id_agence) {
  global $dbHandler;

  $db = $dbHandler->openConnection();

  // Récupérer le montant actuellement bloqué sur le compte
  $sql = "SELECT mnt_bloq FROM ad_cpt WHERE id_ag = $id_agence AND id_cpte=$id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__);
      return new ErrorObj(ERR_GENERIQUE, $result);
  }
  $tmprow = $result->fetchrow();
  $mnt_bloq = $tmprow[0];

  $dbHandler->closeConnection(true);

  // Il est possible que d'autre operations sont venus ajouté un montant sur le mnt_bloq
  if ($mnt_bloq >= $mnt){
      return new ErrorObj(NO_ERR);
  } else {
      return new ErrorObj(ERR_GENERIQUE, $result);
  }
}

?>