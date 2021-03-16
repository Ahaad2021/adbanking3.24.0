<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2: */

/**
 * @package Extraits
 */

/**
 * Création des extraits de compte pour un compte donné
 * @author Antoine Guyette
 * @param integer $id_cpte Compte pour lequel on veut créer les extraits de compte
 * @return ErrorObj
 */
function creationExtraitCompteClient($id_cpte) {
  global $dbHandler;
  global $global_id_agence;
  global $adsys;

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_his, ad_ecriture, ad_mouvement WHERE ad_his.id_ag = $global_id_agence AND ad_ecriture.id_ag = $global_id_agence AND ad_mouvement.id_ag = $global_id_agence AND ad_mouvement.cpte_interne_cli = $id_cpte AND ad_his.id_his = ad_ecriture.id_his AND ad_ecriture.id_ecriture = ad_mouvement.id_ecriture AND ad_his.id_his NOT IN (SELECT id_his FROM ad_extrait_cpte WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte) ORDER BY ad_ecriture.id_ecriture";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $DATA = array();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $infos_extrait = array();
    $infos_extrait['id_cpte'] = $id_cpte;
    $infos_extrait['id_ag'] = $global_id_agence;

    if ($row['sens'] == 'c') {
      $infos_extrait['montant'] = $row['montant'];
    }
    elseif ($row['sens'] == 'd') {
      $infos_extrait['montant'] = - $row['montant'];
    }

    $infos_extrait['intitule'] = $adsys["adsys_intitule_extrait"][$row['type_fonction']];
    $infos_extrait['date_exec'] = substr($row['date'], 0, 10);
    $infos_extrait['date_valeur'] = $row['date_valeur'];
    $infos_extrait['information'] = $row['libel_ecriture'];
    $infos_extrait['id_his'] = $row['id_his'];
    creationExtraitCompte($infos_extrait);
  }

  $dbHandler->closeConnection(true);

  return NULL;
}

/**
 * Création d'un nouvel extrait de compte
 * @author Antoine Guyette
 * @param ARRAY $infos_extrait Tableau contenant toutes les informations d'un extrait de compte
 * @return ErrorObj
 */
function creationExtraitCompte($infos_extrait) {
  $id_cpte = $infos_extrait['id_cpte'];
  $date_extrait = $infos_extrait['date_exec'];
  $montant = $infos_extrait['montant'];

  $infos_extrait_eft = creationReferenceExtraitEFT($id_cpte, $date_extrait, $montant);
  $infos_extrait = array_merge($infos_extrait, $infos_extrait_eft);
  if($infos_extrait['intitule'] == NULL){
  	$infos_extrait['intitule'] = $infos_extrait['information'];
  }
  $sql = buildInsertQuery('ad_extrait_cpte', $infos_extrait);

  return (executeDirectQuery($sql));
}

/**
 * Get latest mouvement
 */
function get_last_mouvement() {
  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_mouvement FROM ad_mouvement WHERE id_ag=$global_id_agence ORDER BY id_mouvement DESC LIMIT 1";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);

  return $row[0];
}


/**
 * Création des références EFT d'un extrait de compte
 * @author Antoine Guyette
 * @param integer $id_cpte Identifiant du compte
 * @param date $date_extrait Date de l'extrait de compte
 * @return ARRAY Tableau contenant toutes les références EFT
 */
function creationReferenceExtraitEFT($id_cpte, $date_extrait, $montant) {
  global $global_id_agence;

  $sql = "SELECT max(id_extrait_cpte) FROM ad_extrait_cpte WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte";
  $error = executeDirectQuery($sql);
  $result = $error->param;
  $row = $result[0];

  $idExtraitPrecedent = $row['max'];

  $sql = "SELECT id_titulaire, date_ouvert FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte";
  $error = executeDirectQuery($sql);
  $result = $error->param;
  $row = $result[0];

  $idTitulaire = $row['id_titulaire'];
  $dateOuverture = $row['date_ouvert'];

  if ($idExtraitPrecedent == NULL) {
    $infos_extrait_eft = array();
    $infos_extrait_eft['eft_id_extrait'] = 1;
    $infos_extrait_eft['eft_id_mvt'] = get_last_mouvement();
    $infos_extrait_eft['eft_id_client'] = $idTitulaire;
    $infos_extrait_eft['eft_annee_oper'] = substr($date_extrait, 0, 4);
    $infos_extrait_eft['eft_dern_solde'] = 0;
    $infos_extrait_eft['eft_dern_date'] = $dateOuverture;
    $infos_extrait_eft['eft_nouv_solde'] = $montant;
    $infos_extrait_eft['eft_sceau'] = $date_extrait;

    return $infos_extrait_eft;
  }

  $sql = "SELECT * FROM ad_extrait_cpte WHERE id_ag = $global_id_agence AND id_extrait_cpte = $idExtraitPrecedent";
  $error = executeDirectQuery($sql);
  $result = $error->param;
  $row = $result[0];

  $extraitPrecedent = $row;

  if ($extraitPrecedent['eft_annee_oper'] < substr($date_extrait, 0, 4)) {
    $infos_extrait_eft = array();
    $infos_extrait_eft['eft_id_extrait'] = 1;
    $infos_extrait_eft['eft_id_mvt'] = get_last_mouvement();
    $infos_extrait_eft['eft_id_client'] = $idTitulaire;
    $infos_extrait_eft['eft_annee_oper'] = substr($date_extrait, 0, 4);
    $infos_extrait_eft['eft_dern_solde'] = $extraitPrecedent['eft_nouv_solde'];
    $infos_extrait_eft['eft_dern_date'] = $extraitPrecedent['date_exec'];
    $infos_extrait_eft['eft_nouv_solde'] = $extraitPrecedent['eft_nouv_solde'] + $montant;
    $infos_extrait_eft['eft_sceau'] = $date_extrait;

    return $infos_extrait_eft;
  }

  if ($extraitPrecedent['date_exec'] == $date_extrait) {
    $infos_extrait_eft = array();
    $infos_extrait_eft['eft_id_extrait'] = $extraitPrecedent['eft_id_extrait'];
    $infos_extrait_eft['eft_id_mvt'] = get_last_mouvement(); //$extraitPrecedent['eft_id_mvt'] + 1;
    $infos_extrait_eft['eft_id_client'] = $idTitulaire;
    $infos_extrait_eft['eft_annee_oper'] = substr($date_extrait, 0, 4);
    $infos_extrait_eft['eft_dern_solde'] = $extraitPrecedent['eft_nouv_solde'];
    $infos_extrait_eft['eft_dern_date'] = $extraitPrecedent['date_exec'];
    $infos_extrait_eft['eft_nouv_solde'] = $extraitPrecedent['eft_nouv_solde'] + $montant;
    $infos_extrait_eft['eft_sceau'] = $date_extrait;

    return $infos_extrait_eft;
  }

  if ($extraitPrecedent['date_exec'] != $date_extrait) {
    $infos_extrait_eft = array();
    $infos_extrait_eft['eft_id_extrait'] = $extraitPrecedent['eft_id_extrait'] + 1;
    $infos_extrait_eft['eft_id_mvt'] = get_last_mouvement();
    $infos_extrait_eft['eft_id_client'] = $idTitulaire;
    $infos_extrait_eft['eft_annee_oper'] = substr($date_extrait, 0, 4);
    $infos_extrait_eft['eft_dern_solde'] = $extraitPrecedent['eft_nouv_solde'];
    $infos_extrait_eft['eft_dern_date'] = $extraitPrecedent['date_exec'];
    $infos_extrait_eft['eft_nouv_solde'] = $extraitPrecedent['eft_nouv_solde'] + $montant;
    $infos_extrait_eft['eft_sceau'] = $date_extrait;

    return $infos_extrait_eft;
  }
}

/**
 * Fonction renvoyant les extraits de compte d'un client
 * @author Papa
 * @author Antoine Guyette
 * @since 2.0
 * @param integer $id_cpte identifiant d'un compte de client
 * @param boolean $dernier_extrait imprimer depuis le dernier extrait ?
 * @param date $date_debut début de l'intervalle de recherche
 * @param date $date_fin fin de l'intervalle de recherche
 * @param integer $num_debut numéro de début de l'intervalle de recherche
 * @param integer $num_fin numéro de fin de l'intervalle de recherche
 * @return array $TMPARRAY liste opération sur ce compte à cette période (index => infos opération)
 */
function getExtraitCompte($id_cpte, $date_debut, $date_fin, $dernier_extrait = NULL, $num_debut = NULL, $num_fin = NULL) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  creationExtraitCompteClient($id_cpte);

  // Récupération du id du dernier extrait imprimé
  if ($dernier_extrait == true) {
    $sql = "SELECT id_dern_extrait_imprime FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $id_dern_extrait_imprime = $row["id_dern_extrait_imprime"];
    if ($id_dern_extrait_imprime == NULL) {
      $id_dern_extrait_imprime = 0;
    }

    $sql = "SELECT DISTINCT e.*, c.num_complet_cpte, c.intitule_compte, c.id_titulaire, c.devise, ec.type_operation
            FROM ad_extrait_cpte e
            inner join ad_cpt c on e.id_cpte = c.id_cpte and e.id_ag = c.id_ag
            inner join ad_his his on e.id_his = his.id_his and e.id_ag = his.id_ag
            inner join ad_ecriture ec on his.id_his = ec.id_his and his.id_ag = ec.id_ag and e.information::int = ec.libel_ecriture
            WHERE e.id_cpte = $id_cpte
            AND e.id_ag = $global_id_agence
            AND id_extrait_cpte > $id_dern_extrait_imprime
            ORDER BY eft_annee_oper ASC, id_extrait_cpte ASC; ";

  }
  // Impression suivant les dates ou les numéros d'extraits
  else
  {
    $sql = "SELECT DISTINCT e.*, c.num_complet_cpte, c.intitule_compte, c.id_titulaire, c.devise, ec.type_operation
            FROM ad_extrait_cpte e
            inner join ad_cpt c on e.id_cpte = c.id_cpte and e.id_ag = c.id_ag
            inner join ad_his his on e.id_his = his.id_his and e.id_ag = his.id_ag
            inner join ad_ecriture ec on his.id_his = ec.id_his and his.id_ag = ec.id_ag and e.information::int = ec.libel_ecriture
            WHERE e.id_cpte = $id_cpte AND e.id_ag = $global_id_agence ";

    // Date minimum
    if ($date_debut != NULL) {
      $sql .= " AND '$date_debut' <= date(date_exec) ";
    }

    // Date maximum
    if ($date_fin != NULL) {
      $sql .= " AND date(date_exec) <= '$date_fin'";
    }
    // Numéro d'extrait minimum
    if ($num_debut != NULL) {
      $sql .= " AND '$num_debut' <= e.id_his";
    }
    // Numéro d'extrait maximum
    if ($num_fin != NULL) {
      $sql .= " AND e.id_his <= '$num_fin'";
    }

    $sql .=" ORDER BY eft_annee_oper ASC, id_extrait_cpte ASC; ";
  }

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  if ($result->numrows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $max = 0; // le max extrait
  $TMPARRAY = array ();

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($row["id_extrait_cpte"] > $max) {
      $max = $row["id_extrait_cpte"];
    }
    $id_pays = $row["cptie_pays"];
    if ($id_pays != NULL) {
      $sql = "select libel_pays from adsys_pays where id_ag = $global_id_agence and id_pays = '$id_pays'";
      $result_pays = $db->query($sql);
      if (DB :: isError($result_pays)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
      }
      $row_pays = $result_pays->fetchrow(DB_FETCHMODE_ASSOC);
      $row['cptie_pays'] = $row_pays['libel_pays'];
    }
    if ($row['id_his'] != NULL) {
      $sql = "SELECT a.login, b.id_tireur_benef, b.id_pers_ext, b.communication ";
      $sql .= " FROM ad_his a, ad_his_ext b ";
      $sql .= " WHERE a.id_ag = b.id_ag and b.id_ag = $global_id_agence and a.id_his = " . $row['id_his'] . " and a.id_his_ext = b.id";
      $result_his = $db->query($sql);
      if (DB :: isError($result_pays)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
      }
      $row_his = $result_his->fetchrow(DB_FETCHMODE_ASSOC);
      if ($row_his['id_tireur_benef'] != NULL) {
        $TIREUR_BENEF = getTireurBenefDatas($row_his['id_tireur_benef']);
        $row['tireur'] = $TIREUR_BENEF['denomination'];
      }
      if ($row_his['id_pers_ext'] != NULL) {
        $PERS_EXT = getPersonneExt(array ("id_pers_ext" => $row_his['id_pers_ext']));
        $row['donneur_ordre'] = $PERS_EXT[0]['denomination'];
      }
      if ($row_his['communication'] != NULL) {
        $row['communication'] = $row_his['communication'];
      }
      if ($row_his['login'] != NULL) { 
      	$row['login'] = $row_his['login'];
      } 	        
    }
    array_push($TMPARRAY, $row);
  }
  // Mise à jour du dernier extrait imprimé
  if (($dernier_extrait == true) and ($max > 0)) {
    $sql = "UPDATE ad_cpt SET id_dern_extrait_imprime = $max WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
  }
  $dbHandler->closeConnection(true);

  return $TMPARRAY;
}

/**
 * Permet de connaître le dernier numéro d'extrait imprimé pour un compte (numéro d'extrait EFT)
 * @author Antoine Guyette
 * @since 2.6
 * @param integer $id_cpte identifiant du compte
 * @return integer numéro d'extrait
 */
function getNumDernierExtrait($id_cpte) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT a.eft_id_extrait FROM ad_extrait_cpte a, ad_cpt b WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence AND b.id_cpte = $id_cpte AND b.id_dern_extrait_imprime = a.id_extrait_cpte";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $eft_id_dern_extrait = $row["eft_id_extrait"];
  $dbHandler->closeConnection(true);
  return $eft_id_dern_extrait;
}
/**
 * Fonction renvoyant les numero de comptes dont l'export netbank est à true
 * @author Djibril NIANG
 * @since 3.2
 * @param date $date_debut début de l'intervalle de recherche
 * @param date $date_fin fin de l'intervalle de recherche
 * @return array $cpte_netbank liste des comptes pour export netbank
 */
function getCpteNetbank() {
	global $dbHandler, $global_id_agence;
  
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND export_netbank = true ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $cpte_netbank = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
  	$cpte_netbank[$row['id_cpte']] = $row[0];
  }    
    
  $dbHandler->closeConnection(true);
  return $cpte_netbank;
}
/**
 * Fonction renvoyant les extraits de comptes dont l'export netbank est à true
 * @author Djibril NIANG
 * @since 3.2
 * @param date $date_debut début de l'intervalle de recherche
 * @param date $date_fin fin de l'intervalle de recherche
 * @return array $extraits_netbank liste des Extraits de compte Netbank
 */
function getExtraitsCpteNetbank($date_debut, $date_fin) {
	global $dbHandler, $global_id_agence;
	
	//recupération des comptes netbank
	$cpte_netbank = getCpteNetbank();
	$extraits_netbank = array();
	foreach($cpte_netbank as $id_cpte =>$value) {
			//on recupere les extraits pour chaque compte
			$extrait_cpte = getExtraitCompte($id_cpte, $date_debut, $date_fin); debug($extrait_cpte,sprintf(_("extrait_cpte de %s"),$id_cpte));
			//si le tableau contient des données, on les recupere dans le tableau de retour
			if(is_array($extrait_cpte)){
				foreach($extrait_cpte as $key =>$value){
					$extraits_netbank[$value['id_extrait_cpte']] = $value;
				} 
			}			
					  
  }
  return $extraits_netbank;
}
?>