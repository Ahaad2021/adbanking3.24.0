<?php
require_once 'lib/dbProcedures/agence.php';
/**
 * Fonction renvoyant les informations sur les messages SWIFT
 * @author Papa
 * @since 2.0
 * @param array $fields_values Array permettant de construire une clause WHERE pour le SELECT.
 * Si argument NULL, on renvoie tous les messages. L'array a la forme (fieldname=>value recherchée)
 * @return array On renvoie un tableau de la forme (index => infos compte)
 */
function getMessagesSwiftEtrangers($fields_values=NULL) {
  global $dbHandler,$global_id_agence;

  //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM swift_op_etrangers ";

  if (isset($fields_values)) {
    $sql .= "WHERE id_ag=$global_id_agence AND ";
    foreach ($fields_values as $key => $value)
    if (($value == '') or ($value == NULL))
      $sql .= "$key IS NULL AND ";
    else if ($key == 'DateDeb')
      $sql .= "date(date) >= '$value' AND ";
    else if ($key == 'DateFin')
      $sql .= "date(date) <= '$value' AND ";
    else
      $sql .= "$key = '$value' AND ";
    $sql = substr($sql, 0, -4);
  }

  $sql .= "ORDER BY id_message  ASC";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $messages = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $messages[$row["id_message"]]=$row;

  $dbHandler->closeConnection(true);

  return $messages;
}

/**
 * Fonction renvoyant les informations sur les messages SWIFT domestiques
 * @author Papa
 * @since 2.0
 * @param array $fields_values Array permettant de construire une clause WHERE pour le SELECT.
 * Si argument NULL, on renvoie tous les messages. L'array a la forme (fieldname=>value recherchée)
 * @return array On renvoie un tableau de la forme (index => infos compte)
 */
function getMessagesSwiftDomestiques($fields_values=NULL) {
  global $dbHandler,$global_id_agence;

  //vérifier qu'on reçoit bien un array
  if (($fields_values != NULL) && (! is_array($fields_values)))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Mauvais argument dans l'appel de la fonction"

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM swift_op_domestiques ";

  if (isset($fields_values)) {
    $sql .= "WHERE id_ag=$global_id_agence AND ";
    foreach ($fields_values as $key => $value)
    if (($value == '') or ($value == NULL))
      $sql .= "$key IS NULL AND ";
    else if ($key == 'DateDeb')
      $sql .= "date(date_memo) >= '$value' AND ";
    else if ($key == 'DateFin')
      $sql .= "date(date_memo) <= '$value' AND ";
    else
      $sql .= "$key = '$value' AND ";
    $sql = substr($sql, 0, -4);
  }

  $sql .= "ORDER BY id_message  ASC";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $messages = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $messages[$row["id_message"]]=$row;

  $dbHandler->closeConnection(true);

  return $messages;
}

/**
 * Fonction mettant à jour les messages SWIFT
 * @author Papa
 * @since 2.0
 * @param int $id_message l'identifiant du message
 * @param int $statut le nouveau statut du message
 * @param text $message_erreur le message de l'erreur
 * @param int $id_don l'identifiant du client donneur d'ordre
 * @param int $id_ben l'identifiant du bénéficiaire
 * @return objetErr
 */
function updateSwiftDomestique($id_message, $statut, $message_erreur, $cpte_don, $cpte_ben) {
  global $dbHandler,$global_id_agence;
  $global_id_agence=getNumAgence();
  $db = $dbHandler->openConnection();

  /* Vérifier que le compte du donneu d'ordre existe*/

  /*  $sql .= "SELECT * FROM ad_cpt WHERE replace(num_complet_cpte,'-','')='$cpte_don';";
  $result_don = $db->query($sql);
  if (DB::isError($result_don))
    {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

  if($result_don->numrows()== 0) // le compte du donneur d'ordre n'existe pas
    {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_SWIFT_NON_VALIDE, ". Le compte du donneur d'ordre n'existe pas");
    }

  */
  /* Vérifier que le compte du bénéficiaire est un compte client */
  /*$sql .= "SELECT * FROM ad_cpt WHERE replace(num_complet_cpte,'-','')='$cpte_ben';";
  $result = $db->query($sql);
  if (DB::isError($result))
    {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

  if($result->numrows()== 0) // le bénéficiaire n'est pas un client
    {
      // Vérifier que le compte du bénéficiaire est un compte tireur bénéficiaire
      $sql .= "SELECT * FROM tireur_benef WHERE trim(num_cpte)='$cpte_ben';";
      $result2 = $db->query($sql);
      if (DB::isError($result2))
  {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
    
      if($result2->numrows()== 0) // le compte n'est pas un compte d'un tireur bénéf
  {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_SWIFT_NON_VALIDE, ". Le compte du bénéficiaire n'existe pas");
  }
    }
  */

  $sql .= "UPDATE swift_op_domestiques SET statut =$statut,message_erreur='$message_erreur',num_cpte_do='$cpte_don',num_cpte_ben='$cpte_ben' WHERE id_ag=$global_id_agence AND id_message=$id_message";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Fonction mettant à jour les messages SWIFT
 * @author Papa
 * @since 2.0
 * @param int $id_message l'identifiant du message
 * @param int $statut le nouveau statut du message
 * @param text $message_erreur le message de l'erreur
 * @param int $id_don l'identifiant du client donneur d'ordre
 * @param int $id_ben l'identifiant du bénéficiaire
 * @return objetErr
 */
function updateSwiftEtranger($id_message, $statut, $message_erreur, $cpte_don, $cpte_ben) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  /* Vérifier que le compte du donneu d'ordre existe*/
  /*$sql .= "SELECT * FROM ad_cpt WHERE replace(num_complet_cpte,'-','')='$cpte_don';";
  $result_don = $db->query($sql);
  if (DB::isError($result_don))
    {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

  if($result_don->numrows()== 0) // le compte du donneur d'ordre n'existe pas
    {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_SWIFT_NON_VALIDE, ". Le compte du donneur d'ordre n'existe pas");
    }
  */
  /* Vérifier que le compte du bénéficiaire est un compte client */
  /*$sql .= "SELECT * FROM ad_cpt WHERE replace(num_complet_cpte,'-','')='$cpte_ben';";
  $result = $db->query($sql);
  if (DB::isError($result))
    {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

  if($result->numrows()== 0) // le bénéficiaire n'est pas un client
    {
      // Vérifier que le compte du bénéficiaire est un compte tireur bénéficiaire /
      $sql .= "SELECT * FROM tireur_benef WHERE trim(num_cpte)='$cpte_ben';";
      $result2 = $db->query($sql);
      if (DB::isError($result2))
  {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
    
      if($result2->numrows()== 0) // le compte n'est pas un compte d'un tireur bénéf
  {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_SWIFT_NON_VALIDE, ". Le compte du bénéficiaire n'existe pas");
  }
    }
  */

  $sql .= "UPDATE swift_op_etrangers SET statut =$statut,message_erreur='$message_erreur',num_cpte_do='$cpte_don',num_cpte_ben='$cpte_ben' WHERE id_message=$id_message";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Fonction vérifiant si le compte comlpet existe
 * @author Papa
 * @since 2.0
 *@param text $num_cpte le numéro du compte sans les '-'
 * @return bool true le compte existe sinon false
 */
function isBenefClient($num_cpte) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql .= "SELECT * FROM ad_cpt WHERE id_ag=$global_id_agence AND replace(num_complet_cpte,'-','')='$num_cpte'";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $nb = $result->numrows();

  $dbHandler->closeConnection(true);

  if ($nb ==1) {
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    return $row;
  } else
    return NULL;

}
/**
 * Liste des correspondants bancaire par agence
 * @param int  $id_ag identifiant de l'agence
 * @return array liste des correspondants bancvaires
 *
 */
function getCorrespondantBancaire() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql .= "SELECT * FROM adsys_correspondant WHERE id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  $CB = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $CB[$row['id']]= $row['numero_cpte'];

  return $CB;

}
/**
 * Fonction vérifiant si le compte est un comlpet d'un tireur bénéficiaire
 * @author Papa
 * @since 2.0
 *@param text $num_cpte le numéro du compte
 * @return array $DATA l'entrée de la table des tireurs bénéfciaire correspondand
 */
function isBenefTireur($num_cpte) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql .= "SELECT * FROM tireur_benef WHERE id_ag=$global_id_agence AND trim(num_cpte)='$num_cpte'";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $nb = $result->numrows();

  $dbHandler->closeConnection(true);

  if ($nb !=1)
    return NULL;
  else {
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    return $row;
  }

}


?>