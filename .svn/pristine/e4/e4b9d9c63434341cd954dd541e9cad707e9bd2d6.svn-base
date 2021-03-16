<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2: */

/**
 * @package Epargne
 */

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/devise.php';
require_once 'modules/compta/xml_compta.php';
require_once 'batch/librairie.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/abonnement.php';

/**
 * Créer un compte d'épargne dans ad_cpt
 * 
 * @param Array $DATA
 *        	Tableau contenant les valeurs pour tous les champs de la table
 *        	NB Un champ type_cpt_vers_int peut y etre placé qui, s'il est à 1, indiquera que c'est le compte lui-meme sur lequel les intérets seront versés
 * @return integer Le numéro du compte créé.
 */
function creationCompte($DATA) {
	global $dbHandler, $global_id_agence;
	global $db;
	
	$type_cpt_vers_int = $DATA ["type_cpt_vers_int"];
	unset ( $DATA ["type_cpt_vers_int"] );
	$DATA ['id_ag'] = $global_id_agence;
	$sql = buildInsertQuery ( "ad_cpt", $DATA );
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__, $result->getMessage () );
	}
	
	$sql = "SELECT currval('ad_cpt_id_cpte_seq')";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__, $result->getMessage () );
	}
	$row = $result->fetchrow ();
	$id_cpte = $row [0];
		
	if ($type_cpt_vers_int == 1) {
		$sql = "UPDATE ad_cpt SET cpt_vers_int = id_cpte WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
		$result = $db->query ( $sql );
		if (DB::isError ( $result )) {
			$dbHandler->closeConnection ( false );
			signalErreur ( __FILE__, __LINE__, __FUNCTION__, $result->getMessage () );
		}
	}
		
	// #357 mis-a-jour champ compte comptable	
	$id_prod = $DATA['id_prod'];
	if(!empty($id_prod) && ($id_prod != 3 || $id_prod != 4)) { // pour les comptes a vue seulement
		$myErr = setNumCpteComptableForCompte($id_cpte, $db);
	}	
	
	$sql = "SELECT a.id_prod, b.statut_juridique, c.id_pers_ext FROM ad_cpt a, ad_cli b, ad_pers_ext c WHERE a.id_ag = $global_id_agence AND a.id_ag = b.id_ag AND a.id_ag = c.id_ag AND a.id_cpte = $id_cpte AND a.id_titulaire = b.id_client AND b.id_client = c.id_client;";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
	}
	$row = $result->fetchrow ();
	
	$id_prod = $row [0];
	$statut_juridique = $row [1];
	$id_pers_ext = $row [2];
	
	if (! in_array ( $id_prod, array (
			2,
			3,
			4 
	) ) && $statut_juridique == 1) {
		$MANDAT ['id_cpte'] = $id_cpte;
		$MANDAT ['id_pers_ext'] = $id_pers_ext;
		$MANDAT ['type_pouv_sign'] = 1;
		ajouterMandat ( $MANDAT );
	}
	
	return $id_cpte;
}

/**
 * Bloque un montant sur un compte (souvent il s'agit du blocage d'une garantie)
 * @param int $id_cpte Id du compte sur lequel le montant doit etre bloqué
 * @param float $mnt Montant à bloquer
 * @return bool 1
 * @since 1.0
 */
function bloqGarantie($id_cpte,$mnt) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  // Récupérer le montant actuellement bloqué sur le compte
  $sql = "SELECT mnt_bloq FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte=$id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
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
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return true;
}

/**
 * Débloque un montant sur un compte (souvent il s'agit du déblocage d'une garantie)
 * @param int $id_cpte Id du compte sur lequel le montant doit etre débloqué
 * @param float $mnt Montant à débloquer
 * @return bool 1
 * @since 1.0
 */
function debloqGarantie($id_cpte,$mnt) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  // Récupérer le montant actuellement bloqué sur le compte
  $sql = "SELECT mnt_bloq FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte=$id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
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
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return 1;
}

function getNewAccountID() {
  /* Renvoie le prochain ID de compte libre dans la base
   Valeurs de retour :
   id_compte si OK
   Die si refus de la base de données
  */
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT nextval('ad_cpt_id_cpte_seq');";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }
  $id_cpte_base = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $id_cpte_base[0];
}

/**
 * Renvoie l'ID du compte de base d'un client donné.
 * @param int $id_client L'identifiant du client.
 * @return int L'identifiant du compte de base du client ou NULL si le client n'existe pas ou ne possède pas de comtpe de base.
 */
function getBaseAccountID ($id_client) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_cpte_base FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = $id_client";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    return NULL;
  $tmpRow = $result->fetchrow();
  return $tmpRow[0];
}

/**
 * fonction getIdTitulaire(num_complet_cpte)
 * returns id_titulaire,id_cpte,num_complete_cpte
 * 
 * t361
 **/
function getIdtitulaire($num_complete_cpte) {

	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$sql = " SELECT id_cpte,id_titulaire,num_complet_cpte from ad_cpt where num_complet_cpte = '$num_complete_cpte' ; ";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
	}
	$dbHandler->closeConnection(true);
	if ($result->numRows() == 0)
		return NULL;
	$tmpRow = $result->fetchrow();

	return $tmpRow;
}

function getPSAccountID($id_client) {
  global $global_id_agence;
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $idProdPS = getPSProductID($global_id_agence);
  $sql = "SELECT id_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_titulaire = $id_client AND id_prod = $idProdPS AND etat_cpte = 1;";    
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  } else if ($result->numRows() >1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Zéro ou plusieurs comptes de parts sociales pour ce client"
  }
  $tmprow = $result->fetchrow();
  $idCptPS = $tmprow[0];
  $dbHandler->closeConnection(true);
  return $idCptPS;
}

function getCurrentAccountDatas($id_client) {
    global $global_id_agence, $erreur;

    if(($id_client == null) or ($id_client == '')){
        signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le numéro du client n'est pas renseigné")));
    }else {
        // Attention ! Laisser les tables dans cet ordre car devise apparait 2 fois et c'est celui de ad_cpt qui a précédence
        $sql = "SELECT * FROM ad_cpt WHERE id_titulaire = $id_client AND id_prod = 1 AND id_ag = $global_id_agence";
    }
    $result = executeDirectQuery($sql);
    if ($result->errCode != NO_ERR) {
        return $result;
    } else {
        if (empty($result->param)) {
            return NULL;
        } else {
            return $result->param[0];
        }
    }
}

/**
 * Renvoie un tableau associatif avec toutes les données du compte
 *
 * Les données retournées sont une synthèse cumulative de celles du produit et celles du compte lui-même,
 * en donnant la priorité aux données venant du produit.
 *
 * @param int $id_cpte L'identifiant du compte.
 * @return array NULL si le compte n'existe pas, le tableau des données sinon.
 */
function getAccountDatas($id_cpte) {
  global $global_id_agence, $erreur;

 	if(($id_cpte == null) or ($id_cpte == '')){
 	    signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le numéro du compte n'est pas renseigné")));
 	}else {
 	    // Attention ! Laisser les tables dans cet ordre car devise apparait 2 fois et c'est celui de ad_cpt qui a précédence
 	    $sql = "SELECT * FROM adsys_produit_epargne p, ad_cpt c WHERE c.id_ag = $global_id_agence AND c.id_ag = p.id_ag AND c.id_prod = p.id AND c.id_cpte = '$id_cpte'";
 	}
 	$result = executeDirectQuery($sql);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
  	if (empty($result->param)) {
  		return NULL;
  	} else {
      return $result->param[0];
    }
  }
}

/**
 * Renvoie un tableau associatif avec toutes les données du compte
 *
 * Les données retournées sont une synthèse cumulative de celles du produit et celles du compte lui-même,
 * en donnant la priorité aux données venant du produit.
 *
 * @param int $id_cpte L'identifiant du compte.
 * @return array NULL si le compte n'existe pas, le tableau des données sinon.
 */
function getMatriculeDatas($num_matricule) {
  global $dbHandler,$global_id_agence, $erreur;

  $db = $dbHandler->openConnection();

  if(($num_matricule == null) or ($num_matricule == '')){
    signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le matricule n'est pas renseigné")));
  }else {
    // Attention ! Laisser les tables dans cet ordre car devise apparait 2 fois et c'est celui de ad_cpt qui a précédence
    $sql = "SELECT * FROM ad_cli WHERE id_ag = $global_id_agence AND matricule = '$num_matricule'";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Le matricule n'existe pas. Matricule:''".$num_matricule."''"))); // "Aucun compte associé. Veuillez revoir le paramétrage"
  }
  $row = $result->fetchrow();
  $data_cli = $row[0];

  $dbHandler->closeConnection(true);
  return $data_cli;
}


/**
 * Renvoi les donnees du client pour la carte associe
 * @param int $numero_carte L'identifiant du compte.
 * @return array NULL si le compte n'existe pas, le tableau des données sinon.
 */
function getCarteUBADatas($num_carte) {
  global $dbHandler,$global_id_agence, $erreur;

  $db = $dbHandler->openConnection();

  if(($num_carte == null) or ($num_carte == '')){
    signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("La carte bancaire n'est pas renseigné")));
  }else {
    // Attention ! Laisser les tables dans cet ordre car devise apparait 2 fois et c'est celui de ad_cpt qui a précédence
    $sql = "SELECT * FROM ad_cli WHERE id_ag = $global_id_agence AND id_card = '$num_carte'";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("La carete bancaire n'existe pas. Carte:''".$num_carte."''"))); // "Aucune carte associé. Veuillez revoir le paramétrage"
  }
  $row = $result->fetchrow();
  $data_cli = $row[0];

  $dbHandler->closeConnection(true);
  return $data_cli;
}

function getCompteCptaGui($id_gui) {
  /*
    Renvoie le compte comptable associé à un guichet
  */

  global $dbHandler, $global_id_client,$global_id_agence, $erreur;
  $db = $dbHandler->openConnection();

  if(($id_gui == null) or ($id_gui == '')){
 	   erreur("getCompteCptaGui", sprintf(_("Le numéro du guichet n'est pas renseigné.")));
 	}else {
 	   $sql = "SELECT cpte_cpta_gui ";
 	   $sql .= "FROM ad_gui  ";
 	   $sql .= "WHERE id_ag = $global_id_agence AND id_gui = '$id_gui'";
 	}
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucun compte associé. Veuillez revoir le paramétrage"
  }
  $row = $result->fetchrow();
  $cpte_cpta = $row[0];

  $dbHandler->closeConnection(true);
  return $cpte_cpta;

}

/**
 * Renvoie le compte comptable associé à un compte client donné.
 *
 * Dans le cas d'un compte d'épargne nantie, on remonte jusqu'au produit de crédit.
 * @param int $id_cpte_cli Id du compte client associé
 * @return text Numéro du compte comptable associé
 */
function getCompteCptaProdEp($id_cpte_cli) {
  global $dbHandler, $global_id_agence, $erreur;

  $db = $dbHandler->openConnection();

  if(($id_cpte_cli == null) or ($id_cpte_cli == '')){
  	$dbHandler->closeConnection(false);
  	signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Le compte interne du client n'est pas renseigné.")));
  } else {
 	   $sql = "SELECT b.id, b.cpte_cpta_prod_ep ";
 	   $sql .= "FROM ad_cpt a, adsys_produit_epargne b  ";
 	   $sql .= "WHERE b.id_ag = $global_id_agence AND b.id_ag = a.id_ag AND a.id_prod = b.id AND a.id_cpte='$id_cpte_cli'";
 	}
 	$result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("DB").": ".$result->getMessage());
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Aucun compte associé. Veuillez revoir le paramétrage"));
  }

  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  if ($row['id'] == 4) { // Cas particulier du compte d'épargne nantie
    $sql = "SELECT cpte_cpta_prod_cr_gar from adsys_produit_credit a, ad_dcr b where b.id_ag = $global_id_agence AND b.id_ag = a.id_ag AND a.id = b.id_prod AND ";
    $sql .= "b.id_doss = (SELECT distinct(id_doss) FROM ad_gar WHERE id_ag = $global_id_agence AND gar_num_id_cpte_nantie = $id_cpte_cli)";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, "DB: ".$result->getMessage());
    }

    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);
    return $row[0];
  } else {
    $dbHandler->closeConnection(true);
    return $row["cpte_cpta_prod_ep"];
  }
}

/**
 * Renvoie le compte comptable associé aux intérêts payés sur un produit d'épargne grâce au compte client associé.
 * @param int $id_cpte_cli Identifiant du compte client associé
 * @return str Le compte comptable associé aux intérêts du produit d'épargne
 */
function getCompteCptaProdEpInt($id_cpte_cli) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT cpte_cpta_prod_ep_int ";
  $sql .= "FROM ad_cpt a, adsys_produit_epargne b  ";
  $sql .= "WHERE a.id_ag = $global_id_agence AND a.id_ag = b.id_ag AND a.id_prod = b. id AND a.id_cpte='$id_cpte_cli'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

	if ($result->numRows() == 0) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Aucun compte associé. Veuillez revoir le paramétrage"
	}
	$cpte_cpta = $result->fetchrow();
	$dbHandler->closeConnection(true);

  return $cpte_cpta[0];

}

function getCompteCptaDcr($id_doss) {

  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT cpte_cpta_prod_cr_int , cpte_cpta_prod_cr_gar, cpte_cpta_prod_cr_pen, cpte_cpta_prod_cr_frais";
  $sql .= " FROM  adsys_produit_credit b , ad_dcr a  ";
  $sql .= "WHERE b.id_ag = $global_id_agence AND b.id_ag = a.id_ag AND b.id = a.id_prod and a.id_doss = $id_doss";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucun compte associé. Veuillez revoir le paramétrage"
  }

  $cpte_cpta = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $cpte_cpta;
}

/**
 * Renvoie le compte comptable du coffre-fort avec son solde qui correspond au montant présent dans le coffre-fort
 * @author Hassan DIALLO
 * @param int $id_agc Id de l'agence
 * @since 1.0.8
 * @return array Renvoie un tableau de la forme ("CompteCoffreFort" => N° du compte, "Solde" => solde du compte)
 */
function getCompteCoffreFortInfos($id_agc) {

  global $dbHandler, $global_id_agence;
  global $global_multidevise;

  $db = $dbHandler->openConnection();

  $InfosCpteCoffre = array();

  //Chercher le n° de compte
  $infosagence = getAgenceDatas($global_id_agence);

  $CompteCoffreFort = $infosagence["cpte_cpta_coffre"];
  $CptCfreFor=$CompteCoffreFort;

  if ($CompteCoffreFort == "") {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // _("Aucun compte associé au coffre-fort. Veuillez revoir le paramétrage")
  }
  $DATA=get_table_devises();
  foreach ($DATA as $key => $value) {

    if ($global_multidevise)
      $CompteCoffreFort=$CptCfreFor.".".$key;
    //chercher le solde
    $critere = array();
    $critere["num_cpte_comptable"] = $CompteCoffreFort;
    $InfosCpte = getComptesComptables($critere);
    if (! isset($InfosCpte)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // _("Erreur lors de la recherche du solde")
    }


    $InfosCpteCoffre[$key]["CompteCoffreFort"] = $CompteCoffreFort;
    $InfosCpteCoffre[$key]["solde"] = abs($InfosCpte[$CompteCoffreFort]["solde"]);
  }
  $dbHandler->closeConnection(true);

  return $InfosCpteCoffre;
}

function getCompteBanque($id_agc) {
  /*
    Renvoie le compte comptable collectif associé aux banques dans la table agence
  */

  global $dbHandler;

  $db = $dbHandler->openConnection();

  $sql = "SELECT cpte_cpta_bqe ";
  $sql .= "FROM ad_agc ";
  $sql .= "WHERE id_ag = '$id_agc'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucun compte associé aux banques. Veuillez revoir le paramétrage"
  }
  $row = $result->fetchrow();
  $cpte_bqe = $row[0];

  $dbHandler->closeConnection(true);
  return $cpte_bqe;

}

/**
 * Renvoie les comptes comptables associés à un correspondant bancaire.
 * @author Bernard De Bois
 * @param int $idCorrespondant : ID du correspondant
 * @return array Renvoie un tableau de la forme ("compte" => Compte du correspondant, "debit" => Compte d'ordre débiteur, "credit" => Compte d'ordre créditeur)
 */
function getComptesCompensation($idCorrespondant) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT cpte_bqe, cpte_ordre_deb, cpte_ordre_cred FROM adsys_correspondant WHERE id_ag = $global_id_agence AND id = $idCorrespondant;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $cptes['compte'] = $row[0];
  $cptes['debit'] = $row[1];
  $cptes['credit'] = $row[2];

  $dbHandler->closeConnection(true);
  return $cptes;
}

/**
 * Retourne la date passée en paramètre augmentée ou diminuée d'un certain nombre de jours ouvrables,
 * en fonction des paramètres du produit épargne associé au compte.
 * @param int $compte Identifiant du compte épargne
 * @param string $sens Sens de l'opération 'd' pour débit, 'c' pour crédit, il déterminera si on retranche ou si l'on rajoute des jours.
 * @param string $date_compta La date de comptabilisation de l'opération, au format jj/mm/aaaa
 * @return string $date_valeur La date valeur calculée, au format jj/mm/aaaa
 */
function getDateValeur($a_compte, $a_sens, $a_date_compta) {
  global $global_id_agence;
  if (!isset($a_compte))
    return $a_date_compta;
  $info_compte = getAccountDatas($a_compte);
  $info_produit = getProdEpargne($info_compte["id_prod"]);

  $decalage_debit = $info_produit["nbre_jours_report_debit"];
  $decalage_credit = $info_produit["nbre_jours_report_credit"];

  $nombre_jours = 0;
  if ($a_sens=='c') $nombre_jours = $decalage_credit;
  if ($a_sens=='d') $nombre_jours = $decalage_debit * (-1);

  $annee = substr($a_date_compta,6,4);
  $mois = substr($a_date_compta,3,2);
  $jour = substr($a_date_compta,0,2);

  $date_valeur = jour_ouvrable($jour, $mois, $annee, $nombre_jours);
  return $date_valeur;

}

/**
 * Fonction qui permet de faire la comptabilisation des écritures de ADbanking, elle construit un tableau qu'on passera à ajout_historique
 * @author Hassan et Mouhamadou
 * @since 1.0.8
 * @param int $type_oper Numéro de l'opération, elle peut donnée directement ou déduite dans le schemas comptable
 * @param int $montant Montant de la transaction :Comme on a un seul débit et un seul crédit, le montant est le même des 2 côtés du mouvement
 * @param array $comptable_his tableau passé par référence, il va contenir l'historique des comptes à debiter et à crediter dans le cadre de l'appel
 * @param array $array_cptes tableau utilisé si on doit faire une substitution : (
 * - "cpta" => array("debit" => compte comptable à débiter,"credit" => compte comptable à  créditer)
 * - "int"  => array("debit" => compte interne à  débiter,"credit" => compte interne à  créditer) )
 * L'array "cpta" permet de passer les comptes comptables à subsituer au débit ou au crédit.
 * L'array "int" permet de passer le compte interne (ad_cpt) si la transaction implique un compte client.
 * Les 2 arrays sont indépendants
 * @return ErrorObj Objet erreur
 */

function passageEcrituresComptablesAuto($type_oper, $montant, &$comptable_his, $array_cptes=NULL, $devise=NULL, $date_compta=NULL,$info_ecriture=NULL,$infos_sup=NULL) {

  global $dbHandler;
  global $global_id_exo;
  global $global_multidevise;
  global $global_id_agence;

  $db = $dbHandler->openConnection();

  $mouvements = array();
  //verifier s'il faut substituer des comptes
  if (isset($array_cptes)) {
    //FIXME : verifier que le vecteur a au plus 2 lignes (1 debit et 1 credit)
    //lire chaque element du vecteur
    foreach ($array_cptes as $key=>$value) {
      //prendre les comptes a substituer
      if ($key == "cpta") { //il existe des comptes comptables a substituer
        foreach ($value as $key2=>$value2)
        if ($key2 == "debit")
          $cpte_debit_sub = $value2;
        elseif ($key2 == "credit")
        $cpte_credit_sub = $value2;
      }

      if ($key == "int") { //il existe des comptes internes a renseigner
        foreach ($value as $key2=>$value2)
        if ($key2 == "debit")
          $cpte_int_debit = $value2;
        elseif ($key2 == "credit")
        $cpte_int_credit = $value2;
      }
    }
  }

  //FIXME : gérer les frais en attente


  //Recuperer les infos sur l'operation

  $InfosOperation = array();
  $MyError = getOperations($type_oper);
  if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
    $dbHandler->closeConnection(false);
    return $MyError;
  } else {
    $InfosOperation = $MyError->param;
  }

  // comptes au débit et crédit
  $DetailsOperation = array();

  $MyError = getDetailsOperation($type_oper);
  if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
    $dbHandler->closeConnection(false);
    return $MyError;
  } else {
    $DetailsOperation = $MyError->param;
  }

  // Recherche du dernier élément du tableau

  end ($comptable_his);
  $tmparr = current($comptable_his);
  $last_libel_oper = $tmparr["libel"];

  if ($last_libel_oper == $type_oper) {
    if ($tmparr["type_operation"] != $type_oper ){
      $newID = getLastIdOperation($comptable_his) + 1;
    }else {
      $newID = getLastIdOperation($comptable_his);
    }
  }
  else {
    $newID = getLastIdOperation($comptable_his) + 1;
  }
    
   //Changer le libellé de l'opération, si autre libellé
  if ($infos_sup["autre_libel_ope"] != NULL)
  	$InfosOperation["libel"] = $infos_sup["autre_libel_ope"];

  //FIXME : ici ça marche parce qu'on a 1 débit et 1 crédit
  $comptable = array();

  // Choix du journal ,cela va dependre des comptes au débit et au crédit

  //Compte comptable à debiter

  if (isset($cpte_debit_sub))
    $cpte_debit = $cpte_debit_sub;
  else
    $cpte_debit = $DetailsOperation["debit"]["compte"];

  // Si on a pas de compte comptable, il y a eu un problème dans le paramétrage des opérations :
  if (!isset($cpte_debit)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_NO_ASSOCIATION, sprintf(_("Compte au débit de l'opération %s"), $type_oper));
  }

  //Compte comptable à crediter
  if (isset($cpte_credit_sub))
    $cpte_credit = $cpte_credit_sub;
  else
    $cpte_credit = $DetailsOperation["credit"]["compte"];

  // Si on a pas de compte comptable, il y a eu un problème dans le paramétrage des opérations :
  if (!isset($cpte_credit)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_NO_ASSOCIATION, sprintf(_("Compte au crédit de l'opération %s"), $type_oper));
  }

  // Si multidevise, vérifie que l'écriture peut avoir lieu
  if ($global_multidevise) {
    if ($devise == NULL) { // Par defaut la devise de reference est utilisee
      global $global_monnaie;
      $devise = $global_monnaie;
    }
    $cpte_debit_dev = checkCptDeviseOK($cpte_debit, $devise);
    if ($cpte_debit_dev == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DEVISE_CPT, _("Devise")." : $devise, "._("compte debit")." : $cpte_debit");
    }
    $cpte_credit_dev = checkCptDeviseOK($cpte_credit, $devise);
    if ($cpte_credit_dev == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_DEVISE_CPT, _("Devise")." : $devise, "._("compte")." : $cpte_credit");
    }

    $cpte_debit = $cpte_debit_dev;
    $cpte_credit = $cpte_credit_dev;

    // Vérifie également que les comptes internes associés s'ils existent sont dans la bonne devise
    if (isset($cpte_int_debit)) {
      $ACC = getAccountDatas($cpte_int_debit);
      if ($ACC["devise"] != $devise) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DEVISE_CPT_INT, _("Devise")." : $devise, "._("opération")." : $type_oper");
      }
    }
    if (isset($cpte_int_credit)) {
      $ACC = getAccountDatas($cpte_int_credit);
      if ($ACC["devise"] != $devise) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DEVISE_CPT_INT, _("Devise")." : $devise, "._("opération")." : $type_oper");
      }
    }
  } else { // En mode unidevise, la devise est toujours la devise de référence
    global $global_monnaie;
    $devise = $global_monnaie;
  }

  // On ne mouvemente pas un compte centralisateur
  if (isCentralisateur($cpte_debit)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPT_CENTRALISE, _("compte")." : $cpte_debit");
  }

  if (isCentralisateur($cpte_credit)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPT_CENTRALISE, _("compte")." : $cpte_credit");
  }

  $jou_cpte_debit = getJournalCpte($cpte_debit);
  $jou_cpte_credit = getJournalCpte($cpte_credit);


  $id_exo = "";

  if(is_array($infos_sup) && count($infos_sup) > 0 && array_key_exists('id_exo', $infos_sup)){
    $id_exo = $infos_sup['id_exo'];
  }

  $exo_encours = "";
  $date_fin = "";
  $date_debut = "";

  // la date comptable doit être dans la période de l'exercice en cours à cours
  if(empty($id_exo)) {
    $exo_encours = getExercicesComptables($global_id_exo);
    $date_debut = pg2phpDate($exo_encours[0]["date_deb_exo"]); // date debut exo ou max date prov
    $date_fin = pg2phpDate($exo_encours[0]["date_fin_exo"]);   // date hier
  }
  else { // ou dans les bornes fournis en parametres
    $exo_encours = $id_exo;
    $date_debut = $infos_sup['date_debut'];
    $date_fin = $infos_sup['date_fin'];
  }

  // date comptable
  if ($date_compta == NULL) {
    $date_comptable = date("d/m/Y"); // date du jour
    if (isAfter($date_comptable, $date_fin))
      $date_comptable = pg2phpDate(get_last_batch($global_id_agence));
  } else
    $date_comptable = $date_compta;

  //Cas exceptionel ou pour les declassements la date $date_compta n'est pas dans l'exercice actuelle
  if ($type_oper == 270 && isAfter($date_comptable, $date_fin)){
    $isDeclassement = 't';
  }
  //si c'est un declassement qui n'est plus dans l'exercice comptable, on laisse passer exceptionellement.
  if ($isDeclassement != 't') {
    if ((isAfter($date_debut, $date_comptable)) or (isAfter($date_comptable, $date_fin))) {
      $dbHandler->closeConnection(false);
      $msg = ". La date n'est pas dans la période de l'exercice.";
      if (!empty($id_exo)) {
        $msg = ". La date n'est pas valide.";
      }
      return new ErrorObj(ERR_DATE_NON_VALIDE, $msg);
    }
  }

  //echo "Journal au débit : $jou_cpte_debit et journal au crédit : $jou_cpte_credit<BR>";
  if (($jou_cpte_debit["id_jou"] != $jou_cpte_credit["id_jou"]) &&  ($jou_cpte_debit["id_jou"] > 1) && ($jou_cpte_credit["id_jou"] > 1) ) {
    //Utilisation d'un compte de liaison
    $InfosOperation["jou_debit"] = $jou_cpte_debit ["id_jou"];
    $InfosOperation["jou_credit"] = $jou_cpte_credit ["id_jou"];
    $temp1 = $jou_cpte_debit["id_jou"];
    $temp2 = $jou_cpte_credit["id_jou"];

    //Recuperation du compte de liaison

    $temp["id_jou1"] = $temp1;
    $temp["id_jou2"] = $temp2;

    $temp_liaison = getJournauxLiaison($temp);

    if (count($temp_liaison ) != 1 ) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_PAS_CPTE_LIAISON);
    }
    $cpte_liaison = $temp_liaison[0]["num_cpte_comptable"];

    // Passages écritures du compte debit au compte de liaison
    $comptable[0]["id"] = $newID;
    $comptable[0]["compte"] = $cpte_debit;
    if (isset($cpte_int_debit))
      $comptable[0]["cpte_interne_cli"] = $cpte_int_debit;
    else
      $comptable[0]["cpte_interne_cli"] = NULL;

    $comptable[0]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[0]["date_valeur"] = getDateValeur($cpte_int_debit,'d',$date_comptable);
    $comptable[0]["sens"] = SENS_DEBIT;
    $comptable[0]["montant"] = $montant;
    $comptable[0]["date_comptable"] = $date_comptable;
    $comptable[0]["libel"] = $InfosOperation["libel"];
    $comptable[0]["jou"] = $InfosOperation["jou_debit"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[0]["validation"] = 't';
    $comptable[0]["devise"] = $devise;
    $comptable[0]["info_ecriture"] = $info_ecriture;

    $comptable[1]["id"] = $newID;
    $comptable[1]["compte"] = $cpte_liaison;
    $comptable[1]["cpte_interne_cli"] = NULL;
    $comptable[1]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[1]["date_valeur"] = getDateValeur($cpte_int_credit,'c',$date_comptable);
    $comptable[1]["sens"] = SENS_CREDIT;
    $comptable[1]["montant"] = $montant;
    $comptable[1]["date_comptable"] = $date_comptable;
    $comptable[1]["libel"] = $InfosOperation["libel"];
    $comptable[1]["jou"] = $InfosOperation["jou_debit"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[1]["validation"] = 't';
    $comptable[1]["devise"] = $devise;
    $comptable[1]["info_ecriture"] = $info_ecriture;


    // Passages ecritures du compte credit au compte de liaison

    $newID++;
    $comptable[2]["id"] = $newID;
    $comptable[2]["compte"] = $cpte_liaison;
    $comptable[2]["cpte_interne_cli"] = NULL;
    $comptable[2]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[2]["date_valeur"] = getDateValeur($cpte_int_debit,'d',$date_comptable);
    $comptable[2]["sens"] = SENS_DEBIT;
    $comptable[2]["montant"] = $montant;
    $comptable[2]["date_comptable"] = $date_comptable;
    $comptable[2]["libel"] = $InfosOperation["libel"];
    $comptable[2]["jou"] = $InfosOperation["jou_credit"];

    if(!empty($id_exo)) $comptable[2]["exo"] = $id_exo;
    else $comptable[2]["exo"] = $global_id_exo;

    $comptable[2]["validation"] = 't';
    $comptable[2]["devise"] = $devise;
    $comptable[2]["info_ecriture"] = $info_ecriture;

    $comptable[3]["id"] = $newID;
    $comptable[3]["compte"] = $cpte_credit;
    if (isset($cpte_int_credit))
      $comptable[3]["cpte_interne_cli"] = $cpte_int_credit;
    else
      $comptable[3]["cpte_interne_cli"] = NULL;
    $comptable[3]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[3]["date_valeur"] = getDateValeur($cpte_int_credit,'c',$date_comptable);
    $comptable[3]["sens"] = SENS_CREDIT;
    $comptable[3]["montant"] = $montant;
    $comptable[3]["date_comptable"] = $date_comptable;
    $comptable[3]["libel"] = $InfosOperation["libel"];
    $comptable[3]["jou"] = $InfosOperation["jou_credit"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[3]["validation"] = 't';
    $comptable[3]["devise"] = $devise;
    $comptable[3]["info_ecriture"] = $info_ecriture;
  }

  else {//Ici, on choisit le journal dont l'id > journal principal si un des comptes est associé à ce journal
    $InfosOperation["jou"] = max($jou_cpte_debit ["id_jou"],$jou_cpte_credit ["id_jou"]);

    $comptable[0]["id"] = $newID;
    $comptable[0]["compte"] = $cpte_debit;
    if (isset($cpte_int_debit))
      $comptable[0]["cpte_interne_cli"] = $cpte_int_debit;
    else
      $comptable[0]["cpte_interne_cli"] = NULL;
    $comptable[0]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[0]["date_valeur"] = getDateValeur($cpte_int_debit,'d',$date_comptable);
    $comptable[0]["sens"] = SENS_DEBIT;
    $comptable[0]["montant"] = $montant;
    $comptable[0]["date_comptable"] = $date_comptable;
    $comptable[0]["libel"] = $InfosOperation["libel"];
    $comptable[0]["jou"] = $InfosOperation["jou"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[0]["validation"] = 't';
    $comptable[0]["devise"] = $devise;
    $comptable[0]["info_ecriture"] = $info_ecriture;

    $comptable[1]["id"] = $newID;
    $comptable[1]["compte"] = $cpte_credit;
    if (isset($cpte_int_credit))
      $comptable[1]["cpte_interne_cli"] = $cpte_int_credit;
    else
      $comptable[1]["cpte_interne_cli"] = NULL;
    $comptable[1]["type_operation"] = $InfosOperation["type_operation"];
    $comptable[1]["date_valeur"] = getDateValeur($cpte_int_credit,'c',$date_comptable);
    $comptable[1]["sens"] = SENS_CREDIT;
    $comptable[1]["montant"] = $montant;
    $comptable[1]["date_comptable"] = $date_comptable;
    $comptable[1]["libel"] = $InfosOperation["libel"];
    $comptable[1]["jou"] = $InfosOperation["jou"];

    if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
    else $comptable[0]["exo"] = $global_id_exo;

    $comptable[1]["validation"] = 't';
    $comptable[1]["devise"] = $devise;
    $comptable[1]["info_ecriture"] = $info_ecriture;
  }

  $comptable_his = array_merge($comptable_his, $comptable);

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);

}

function setSoldeComptable($cpte, $sens, $montant, $devise, $isOperationIAR=false) {
  /*
  Fonction qui met à jour le solde d'un compte de comptabilité.
  On devrait décider qu'on ne peut pas mouvementer un compte collectif mais plutôt un sous-ompte. Mais, pour le moment,
  on suppose que chaque compte a un solde indépendant et pour obtenir le solde d'un compte collectif, il faudra faire la somme  des sous-comptes.

  Au niveau de la DB, on devrait faire des CHECK CONSTRAINTS pour s'assurer en fonction du sens du compte que :
  - Un compte créditeur ne peut devenir négatif
  - Un compte débiteur ne peut devenir positif
  Il faudrait trouver un moyen de récupérer l'erreur interne générée par un trigger

  Pour l'instant, on passe par PHP pour implémenter les contraintes d'intégrité

  * cpte est le n° compte comptable
  * sens est SENS_DEBIT (signe -) ou SENS_CREDIT (signe +)
  * montant à mouvementer sur le compte c'est en valeur absolue
  * devise = Devise du mouvement


  FIXME : tester si le compte qu'on veut mettre à jour n'est pas fermé, bloqué, etc

  */

//  echo "On met à jour le compte comptable $cpte";

  global $dbHandler,$global_id_agence, $error;

  $db = $dbHandler->openConnection();

  //Quel est le solde du compte
  $sql = "SELECT solde, sens_cpte, cpte_centralise, devise ";
  $sql .= "FROM ad_cpt_comptable ";
  $sql .= "WHERE id_ag = $global_id_agence AND num_cpte_comptable = '$cpte' ";
  $sql .= "FOR UPDATE OF ad_cpt_comptable;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Le compte comptable lié n existe pas")); // "Compte inconnu"
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $solde = $row["solde"];
  $sens_cpte = $row["sens_cpte"];
  $cpte_centralise = $row["cpte_centralise"];
  $devise_cpte = $row["devise"];

  // #514 : Arrondir le montant a passer :
  $montant_nonArrondie = $montant;
  $soldeInit_cpte = $solde;
  //REL-101 : on verifie si le montant du compte client contient les deicmaux ex. 50000.536
  $hasDecimalSoldeCpte = hasDecimalMontant($soldeInit_cpte);
  $montant = arrondiMonnaiePrecision($montant, $devise);

  $cpte_int_couru = get_calcInt_cpteInt(false, true, null);

  // on garde le montant non arrondie uniquement si c'est le compte comptable des IAR + REL-101
  if((!is_null($cpte_int_couru) && ($cpte == $cpte_int_couru || $isOperationIAR === true)) || $hasDecimalSoldeCpte === true) {
    $montant = $montant_nonArrondie;
  }

  if ($sens == SENS_DEBIT) {
    $solde = $solde - $montant;
  } else if ($sens == SENS_CREDIT) {
    $solde = $solde + $montant;
  }

  // on garde le montant non arrondie uniquement si c'est le compte comptable des IAR + REL-101
  if((!is_null($cpte_int_couru) && ($cpte == $cpte_int_couru || $isOperationIAR === true)) || $hasDecimalSoldeCpte === true){
    $temp_solde = $solde;
    $abs_diff = abs($solde);
    if ($abs_diff < 1){
      $solde = arrondiMonnaiePrecision(abs($solde), $devise);//round($solde, EPSILON_PRECISION);
    }
    if ($abs_diff < 1 && $soldeInit_cpte < 0 && $temp_solde <0){
      $solde = -1 * $solde;
    }
  }

  if ($sens_cpte == 1) {
    //cas des comptes naturellement débiteurs : le solde ne peut pas devenir positif
    if ($solde > 0) {
      $dbHandler->closeConnection(false);
      return new  ErrorObj(ERR_CPTE_DEB_POS, $cpte); // "Compte $cpte debiteur va devenir positif !"
    }
  } else if ($sens_cpte == 2) {
    //cas des comptes naturellement créditeurs : le solde ne peut pas devenir négatif
    if ($solde < 0) {
      $dbHandler->closeConnection(false);
      return new  ErrorObj(ERR_CPTE_CRED_NEG, $cpte); // "Le compte $cpte crediteur va devenir negatif !"
    }
  }

  /* On ne mouvemente pas un compte centralisateur
  if (isCentralisateur($cpte))
    {
      $dbHandler->closeConnection(false);
      return new  ErrorObj(ERR_CPT_CENTRALISE, "Compte $cpte"); // "Tentative de mouvementer le compte centralisateur $cpte"
    }
  */

  // Vérifie que le mouvement a bien lieu dans la meme devise
  if ($devise_cpte != $devise) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Tentative de mouvementer le compte dans une autre devise"));
  }

  //mettre a jour solde courant et solde centralise
  $sql = "UPDATE ad_cpt_comptable ";
  $sql .= "SET solde = $solde ";
  $sql .= "WHERE id_ag=$global_id_agence AND num_cpte_comptable = '$cpte';";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

function blocageCompteInconditionnel ($id_cpte) {
	/*
	 Cette PS bloque le compte $id_cpte
	 */

	global $dbHandler,$global_id_agence;

	$db = $dbHandler->openConnection();

	$sql = "SELECT etat_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte;";
	$result=$db->query($sql);
	if (DB::isError($result)){
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	$tmprow = $result->fetchrow();
	$etat = $tmprow[0];

	if ($etat == 2){  // Le compte est fermé
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de bloquer le compte $id_cpte qui est fermé"
	}


	//on change l'état du compte à  bloqué
	$sql = "UPDATE ad_cpt SET etat_cpte = 3 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
	$result=$db->query($sql);
	if (DB::isError($result)){
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	$dbHandler->closeConnection(true);

	return new ErrorObj(NO_ERR);
}
function deblocageCompteInconditionnel ($id_cpte) {
  /*
   Cette PS débloque le compte $id_cpte
  */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT etat_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte;";
  $result=$db->query($sql);
  if (DB::isError($result))
    signalErreur(__FILE__,__LINE__,__FUNCTION__);

  $tmprow = $result->fetchrow();
  $etat = $tmprow[0];

  if ($etat == 2)  // Le compte est fermé
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de débloquer le compte $id_cpte qui est fermé"

  //changer le compte à  ouvert
  $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result=$db->query($sql);
  if (DB::isError($result))
    signalErreur(__FILE__,__LINE__,__FUNCTION__);

  $dbHandler->closeConnection(true);

  //quel intérêt ?
  return new ErrorObj(NO_ERR);
}

/**
 * IDEM que la fonction {@link #getBalance}
 * Mis à part que la garantie mobilisée par un autre client peut tre intégrée
 * @author Thomas FASTENAKEL & Papa
 * @param int $id_client ID du client
 * @return Array Liste des balances indicées par code devise
 */
function getBalanceDeces ($id_client) {
  global $dbHandler, $global_id_agence;
  global $global_monnaie;
  global $global_monnaie_prec;

  $db = $dbHandler->openConnection();

  $dev_ref = $global_monnaie;
  $balance = array();
  $balance[$dev_ref] = 0;

  /* Si le client est EAV, la balance est de toute manière à 0 */
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1)
    return $balance;

  /* Récupération des soldes positifs des comptes services financiers */
  $CPTS = get_comptes_epargne($id_client);
  while (list($key, $cpt) = each($CPTS)) {
    $infos_simul = simulationArrete ($cpt['id_cpte']);
    $balance[$cpt["devise"]] += $infos_simul["solde_cloture"];
  }

  /* S'il le compte de parts sociales existe, l' inclure dans la la balance */
  $idCptPS = getPSAccountID($id_client);
  if ($idCptPS != NULL) {
    $cpte_ps = getAccountDatas($idCptPS);
    $infos_simul = simulationArrete ($idCptPS);
    $balance[$cpte_ps['devise']] += $infos_simul["solde_cloture"];
  }

  /* Récupération des dossiers de crédit à l'état 'Fonds déboursés' ou 'En attente de rééchel/Moratoire' ? */
  $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
  $dossiers = getIdDossier($id_client, $whereCl);

  /* Diminuer la balance du solde de chaque crédit en cours et l'augmenter du compte de garantie si elle appartient au client  */
  foreach($dossiers as $id_doss=>$value) {
    /* Diminuer la balance du solde du crédit en cours */
    $solde_credit = 0;
    $myErr = simulationArreteCpteCredit($solde_credit,$id_doss);
    if ($myErr->errCode != NO_ERR)
      signalErreur(__FILE__,__LINE__,__FUNCTION__);

    $balance[$myErr->param] -= $solde_credit;

    /* Récupération de l'épargne nantie numéraire du dossier appartenant au client lui-même */
    $liste_gar = getListeGaranties($id_doss);
    foreach($liste_gar as $key=>$val ) {
      /* la garantie doit être numéraire, non restituée et non réalisée */
      if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
        $nantie = $val['gar_num_id_cpte_nantie'];
        if ($nantie != NULL) { /* S'il y a un compte d'épargne nantie associé au dossier de crédit */
          $CPT_GAR = getAccountDatas($nantie);
          if ($CPT_GAR['id_titulaire'] == $id_client) {
            $infos_simul = simulationArrete ($nantie);
            $balance[$CPT_GAR['devise']] += $infos_simul["solde_cloture"];
          }
        }
      }
    }
  }

  $dbHandler->closeConnection(true);
  return $balance;
}

/**
 * Renvoie la somme des soldes de tous les comptes du client après avoir fait une simulation d'arreté.
 * On tient aussi compte d'un crédit éventuel
 * FIXME : tenir des compte des comptes débiteurs
 * On ne prend pas en compte les garanties déposées par d'autres cients.
 * @author Thomas FASTENAKEL $ Papa
 * @param int $id_client ID du client
 * @return Array Liste des balances indicées par code devise
 */
function getBalance($id_client) {
  global $error;
  global $dbHandler, $global_id_agence;
  global $global_monnaie_prec;
  global $global_monnaie;

  $dev_ref = $global_monnaie;

  $db = $dbHandler->openConnection();
  $balance = array();
  $balance[$dev_ref] = 0;

  // Si le client est EAV, la balance est de toute manière à  0
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1)
    return $balance;

  /* Récupération des soldes positifs des comptes services financiers */
  $CPTS = get_comptes_epargne($id_client);
  while (list($key, $cpt) = each($CPTS)) {
    $infos_simul = simulationArrete ($cpt['id_cpte']);
    $balance[$cpt["devise"]] += $infos_simul["solde_cloture"];
  }

  /* Récupération du compte de parts sociales comme il ne fait pas partie des comptes ci-dessus */
  $idCptPS = getPSAccountID($id_client);
  if ($idCptPS != NULL) {
    $infos_simul = simulationArrete ($idCptPS);
    $balance[$dev_ref] += $infos_simul["solde_cloture"];
  }

  /* Enlever le solde du crédit dans les avoirs du client */
  /* Le client a-t-il des crédits en cours ? */
  $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
  $dossiers = getIdDossier($id_client,$whereCl);
  foreach($dossiers as $id_doss=>$value) {

    $solde_credit = 0;
    $myErr = simulationArreteCpteCredit($solde_credit,$id_doss);
    if ($myErr->errCode != NO_ERR) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $balance[$myErr->param] -= $solde_credit;

    /* Récupération de l'épargne nantie numéraire du dossier appartenant au client */
    $liste_gar = getListeGaranties($id_doss);
    foreach($liste_gar as $key=>$val ) {
      /* la garantie doit être numéraire, non restituée et non réalisée */
      if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
        $nantie = $val['gar_num_id_cpte_nantie'];
        if ($nantie != NULL) { /* S'il y a un compte d'épargne nantie associé au dossier de crédit */
          $CPT_GAR = getAccountDatas($nantie);
          if ($CPT_GAR['id_titulaire'] == $id_client) {
            $infos_simul = simulationArrete($nantie);
            $balance[$CPT_GAR["devise"]] += $infos_simul["solde_cloture"];
          }
        }
      }
    }
  }

  $dbHandler->closeConnection(true);
  return $balance;
}

function getBalanceTransfert($id_client) {
  /*
  PS qui fait la balance d'un client faisant défection par transfert vers une autre agence
  du réseau
  */

  global $error;
  global $dbHandler, $global_id_agence;
  global $global_monnaie;
  global $global_monnaie_prec;

  $db = $dbHandler->openConnection();

  $dev_ref = $global_monnaie;
  $balance = array();

  // Si le client est EAV, la balance est de toute manière à 0
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1)
    return $balance;

  // Début du traitement des comptes services financiers
  $CPTS = get_comptes_epargne($id_client);

  while (list($key, $cpt) = each($CPTS))
    $balance[$cpt["devise"]] += $cpt['solde'];

  // S'il existe, il faut aussi inclure le compte de parts sociales dans la balance
  $idCptPS = getPSAccountID($id_client);
  if ($idCptPS != NULL) {
    $CPT_PS = get_compte_epargne_info($idCptPS);
    $balance[$dev_ref] += $CPT_PS["solde"];
  }

  /* Le client a-t-il des crédits en cours ? */
  $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
  $dossiers = getIdDossier($id_client,$whereCl);
  foreach($dossiers as $id_doss=>$value) {
    /* Diminution de la balance du solde du crédit */
    $solde_credit = 0;
    $myErr = simulationArreteCpteCredit($solde_credit,$id_doss);
    if ($myErr->errCode != NO_ERR)
      signalErreur(__FILE__,__LINE__,__FUNCTION__);

    $balance[$myErr->param] -= $solde_credit;

    /* Récupération de l'épargne nantie numéraire du client */
    $liste_gar = getListeGaranties($id_doss);
    foreach($liste_gar as $key=>$val) {
      /* la garantie doit être numéraire, non restituée et non réalisée  */
      if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
        /* Récupération des infos sur le compte nantie */
        $CPT_GAR = getAccountDatas($val['gar_num_id_cpte_nantie']);
        if ($CPT_GAR['id_titulaire'] == $id_client)
          $balance[$CPT_GAR["devise"]] += $CPT_GAR["solde"];
      }
    }
  }

  $dbHandler->closeConnection(true);
  return $balance;

}

function testDefection($id_client) {
// Cette fonction vérifie
// - que le client est EAV ou que dans le cas contraire
//    - qu'aucun compte n'est bloqué
//    - que la balance est bien nulle pour la devise de référence. // Contradictoire avec la phrase suivante
// Renvoie un objet Erreur avec éventuellement en paramètre la balance dans le devise de référence si celle-ci n'est pas nulle

  global $global_id_agence, $global_monnaie;
  global $dbHandler;

  $db = $dbHandler->openConnection();

  // Si le client est EAV la défection est autorisée
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1) {
    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
  }

  $CPTS = get_comptes_epargne($id_client);

  // On vérifie si un des comptes est bloqué
  reset($CPTS);
  while (list($key, $cpt) = each($CPTS))
    if ($cpt['etat'] == 3) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPT_BLOQUE, $cpt['id_cpte']);
    }

  $balance = getBalance($id_client);

  // Vérifie qu'on a suffisemment d'argent pour effectuer la clô´ture
  if (!isArrayNull($balance)) {
    $dbHandler->closeConnection(true);
    return new ErrorObj(ERR_DEF_SLD_NON_NUL, $balance[$global_monnaie]);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Effectue la cloture de tous les comptes du client qui sont services financiers, sauf du compte de base.
 * En cas de cloture impossible, une erreur est renvoyée.
 * @param int $id_client ID du client dont on doit solder les comptes
 * @param Array $comptable_his Transactions éventuelles précédemment effectuées
 * @return ErrorObj Objet Erreur qui renvoie <ul>
 *   <li> NO_ERR : Tout est OK
 *   <li> ERR_CPTE_BLOQUE : Au moins un compte à cloturer est bloqué (param = id du compte)
 *   <li> ERR_SOLDE_INSUFFISANT : Solde insuffisant pour solder un compte (param = montant manquant (négatif)
 * </ul>
 * @author Thomas FASTENAKEL
 * @since 1.0
 */
function soldeTousComptes($id_client, &$comptable_his) {
  global $global_id_agence, $global_monnaie;
  global $dbHandler;
  $dummy=array(); // Array vide nécessaire à  la fonction clotureCompteEpargne.

  $db = $dbHandler->openConnection();

  $CPTS = get_comptes_epargne($id_client); // les comptes epargne qui sont services financiers

  // On vérifie si un des comptes est bloqué
  reset($CPTS);
  while (list($key, $cpt) = each($CPTS))
    if ($cpt['etat_cpte'] == 3) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Il reste des comptes bloqués."));
    }

  $balance = getBalance ($id_client);

  // Vérifie que seule une balance dans la devise de référence est présente
  if (sizeof($balance) > 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Il y a une balance non nulle dans une devise étrangère"));
  }
  if (!isset($balance["$global_monnaie"])) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Il y a une balance non nulle dans une devise étrangère"));
  }

  // Ici, on sait qu'on a suffisemment d'argent.
  $baseAccountID = getBaseAccountID($id_client);
  reset($CPTS);

  // Supprimer le compte de base de la lsite des comptes à clôturer
  unset($CPTS[$baseAccountID]);
  // Réaliser les clôtures pour de bon
  reset($CPTS);

  // clôture des comptes d'épargne de service financier sauf le compte de base
  while (list($key, $cpt) = each($CPTS)) {
    $myErr = clotureCompteEpargne($key, 1, 2, $baseAccountID, $comptable_his, $dummy);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }
  // Cloture du compte de parts sociales
  $idCptPS = getPSAccountID($id_client);
  if ($idCptPS != NULL) { // Dans le cas contraire, le client est EAV au auxiliaire et ne possède pas de compte PS
    $myErr = clotureCompteEpargne($idCptPS, 1, 2, $baseAccountID, $comptable_his, $dummy);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  // Traitements liés aux crédits
  /* Le client a-t-il des crédits en cours ? */

  $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
  $dossiers = getIdDossier($id_client,$whereCl);
  foreach($dossiers as $id_doss=>$value) {
    $solde_credit = 0;
    $DOSS = getDossierCrdtInfo($id_doss);
    $myErr = simulationArreteCpteCredit($solde_credit,$id_doss);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }


    /* - Annulation garantie à constituer restant due.Elle n'est comptabilisée ni dans les avoirs ni dans les dettes du client
         - Compte de liaison étant fermé (si c'est pas le compte de base), prendre alors compte de base comme compte de liaison .
    */
    $myErr = supprimeReferenceCredit($id_client,$id_doss);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    /* Récupération de l'épargne nantie numéraire du dossier */
    /* Le compte de garantie n'étant pas un service financier, il ne fait pas partie des comptes fermés ci-dessus */
    $liste_gar = getListeGaranties($id_doss);
    foreach($liste_gar as $key=>$val ) {
      /* la garantie doit être numéraire, non restituée et non réalisée */
      if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
        $nantie = $val['gar_num_id_cpte_nantie'];
        $CPT_GAR = getAccountDatas($nantie);
        if ($CPT_GAR['id_titulaire'] == $id_client) {
          /* A ce niveau le compte de liaison est peut être fermé. Effectuer donc le virement dans le compte de base */
          $myErr = clotureCompteEpargne($nantie, 1, 2, $baseAccountID, $comptable_his, $dummy);
          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          }
        } else { /* le compte de garantie appartient à un autre client, il faut restituer la garantie */
          /* id du compte de base du client garant */
          $id_cpt_base_gar = getBaseAccountID($CPT_GAR['id_titulaire']);

          /* Virement de la garantie dans le compte de base du cleint garant */
          $myErr = clotureCompteEpargne($nantie, 1, 2, $id_cpt_base_gar, $comptable_his,NULL);
          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          }
        }
      }
    } /* Fin foreach($liste_gar as $key=>$val ) */
  }
  foreach($dossiers as $id_doss=>$value) {
    // Apurement des crédits : on sait qu'on peut l'apurer
    $myErr = apurementCredit($id_doss,$comptable_his);

    if ($myErr->errCode != NO_ERR) {
      global $error;
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/* Renvoie le rang (les deux derniers chiffres) du numéro du dernier compte
   Valeurs de retour : le rang si OK
   signalErreur si problème */
function getLastAccountNumber($id_client) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT num_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_titulaire = $id_client ORDER BY num_cpte DESC;";
  $result=$db->query($sql);
  if (DB::isError($result))
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  $tmpRow = $result->fetchrow();
  $dbHandler->closeConnection(true);
  // foe me

  return $tmpRow[0];
}

/**
 * Récupère le premier rang disponible pour un client donné
 * @author Mamadou Mbaye
 * @param int $id_client Numéro de client
 */
function getRangDisponible($id_client) {
  global $dbHandler, $global_id_agence;
  global $db;
  /* NB: Cette fonction ne doit pas ouvrir une connexion à la BD sinon ne marchera pas correctement dans le cas de la création de comptes imbriquées . Elle doit donc être appelée dans une autre qui a ouverte une connexion BD. */

  $sql = "SELECT num_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_titulaire = $id_client;";
  $result=$db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
  }

  // $RANGS va contenir tous les rangs déjà occupés
  $RANGS = array();
  while ($row = $result->fetchrow()) {
    array_push($RANGS, $row[0]);
  }

  for ($i = 0; $i < 1000; $i++) {
    if (in_array($i, $RANGS) == false) {
      return $i;
    }
  }

  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Plus de rangs disponibles"
}

function getPSProductID ($id_agence)
// Renvoie le num de produit referençant les comptes de parts sociales
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  // Recuperation du n° de produit d'épargne utilisé par l'agence pour les comptes de parts sociales
  $sql = "SELECT id_prod_cpte_parts_sociales FROM ad_agc WHERE id_ag = $id_agence;"; // Recherche l'état du client
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // impossible de trouver le n° de produit", $result->getMessage()
  }
  $tmpRow = $result->fetchrow();
  $id_prod = $tmpRow[0];
  $dbHandler->closeConnection(true);
  return $id_prod;
}

function getBaseProductID ($id_agence)
// Renvoie le num de produit référençant les comptes de base
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  // Récupération du n° de produit d'épargne utilisé par l'agence pour les comptes de base
  $sql = "SELECT id_prod_cpte_base FROM ad_agc WHERE id_ag = $id_agence;"; // Recherche l'état du client
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $id_prod = $tmpRow[0];
  $dbHandler->closeConnection(true);
  return $id_prod;
}

/**
 * Renvoie un tableau associatif tous les produits d'épargne qui sont de type Dépot à vue
 *
 * @return array tableau associatif des produits d'épargne DAV ou NULL si aucun
 */
function getListProdEpargneDAV() {
  global $global_id_agence;
  //$sql = "SELECT * FROM adsys_produit_epargne WHERE id_ag = $global_id_agence AND classe_comptable=1";
  $sql = "SELECT * FROM adsys_produit_epargne WHERE id_ag = $global_id_agence AND classe_comptable=1 order by id "; 
  $result = executeDirectQuery($sql);
  if ($result->errCode == NO_ERR) {
    return $result->param;
  } else {
    return NULL;
  }
}

/**
 * Renvoie un tableau associatif tous les produits d'épargne qui sont de type services financiers=true
 *
 * @return array tableau associatif des produits d'épargne ou NULL si aucun
 */
function getListProdEpargne($isActif = true) {
  global $global_id_agence;

  $sql = "SELECT * FROM adsys_produit_epargne WHERE id_ag = $global_id_agence AND service_financier=true AND classe_comptable <> 8";

  if($isActif) {
    $sql .= " AND is_produit_actif = 'TRUE'";
  }
  else {
    $sql .= " AND is_produit_actif = 'FALSE'";
  }

  $result = executeDirectQuery($sql);
  if ($result->errCode == NO_ERR) {
    return $result->param;
  } else {
    return NULL;
  }
}


function getListProdEpargneDAT($isActif = true) {
  global $global_id_agence;

  $sql = "
      SELECT * FROM adsys_produit_epargne b
      WHERE b.id_ag = $global_id_agence
      AND b.service_financier=true
      AND b.id > 5
      AND b.classe_comptable = 2
      AND b.tx_interet > 0
      AND b.depot_unique = true
      AND b.retrait_unique = true
  ";

  if($isActif) {
    $sql .= " AND b.is_produit_actif = 'TRUE'";
  }
  else {
    $sql .= " AND b.is_produit_actif = 'FALSE'";
  }

  $result = executeDirectQuery($sql);
  if ($result->errCode == NO_ERR) {
    return $result->param;
  } else {
    return NULL;
  }
}

function getListProdCredits($isActif = true) {
    global $global_id_agence;

    $sql = "
      SELECT * FROM adsys_produit_credit b
      WHERE b.id_ag = $global_id_agence
  ";


    if($isActif) {
        $sql .= " AND b.is_produit_actif = 'TRUE'";
    }
    else {
        $sql .= " AND b.is_produit_actif = 'FALSE'";
    }
    $sql .= " ORDER by libel";

    $result = executeDirectQuery($sql);
    if ($result->errCode == NO_ERR) {
        return $result->param;
    } else {
        return NULL;
    }
}

function insereCompte($DATA) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $DATA['id_ag']= $global_id_agence;
  $sql = buildInsertQuery ("ad_cpt", $DATA);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return 1;

}

function getLastNumCpte($id_client) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT MAX(num_cpte) FROM ad_cpt WHERE id_ag = $global_id_agence AND id_titulaire = $id_client;";
  $result=$db->query($sql);
  if (DB::isError($result))
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  $Row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $Row["max"];
}

/**
 * Retourne les caractéristiques d'un produit d'épargne
 * @param int $a_id_produit L'identifiant du produit d'épargne
 * @return array Un tableau associatif avec les caractéristiques du produit, NULL si pas de produit trouvé.
 */
function getProdEpargne($a_id_produit) {
  global $global_id_agence;
  $sql = "SELECT * FROM adsys_produit_epargne WHERE id_ag = $global_id_agence AND id = '$a_id_produit'";
  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}

/**
 * Retourne les caractéristiques d'un produit d'épargne
 * @param int $a_id_produit L'identifiant du produit d'épargne
 * @return array Un tableau associatif avec les caractéristiques du produit, NULL si pas de produit trouvé.
 */
function getCommandeCarte($id_cpte) {
    global $global_id_agence;
    $sql = "SELECT * FROM ad_gest_carte WHERE id_ag = $global_id_agence AND id = '$id_cpte'";
    $result = executeDirectQuery($sql, FALSE);
    if ($result->errCode != NO_ERR) {
        return $result;
    } else {
        if (empty($result->param)) {
            return NULL;
        } else {
            return $result->param[0];
        }
    }
}

/**
 * Renvoie les infos sur tous les comptes client <B>ouverts</B> pour un client donné
 * @param $id_client int Numéro du client
 * @return Array Un Array indicé par le numéro du compte avec pour chaque compte una rray associatif avec toutes les infos
 */
function getAccounts ($id_client) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT b.*, a.* FROM adsys_produit_epargne b, ad_cpt a WHERE a.id_ag = $global_id_agence AND a.id_ag = b.id_ag AND a.id_prod = b.id AND ";
  $sql .= "a.id_titulaire = '$id_client'";
  $sql .= " AND NOT (a.etat_cpte = 2) ORDER BY a.num_complet_cpte";  //il se peut qu'on veuille avoir les comptes bloqués
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) return NULL;
  $TMPARRAY = array();
  while ($cpt = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $TMPARRAY[$cpt["id_cpte"]] = $cpt;
  }
  return $TMPARRAY;
}


/**
 * Renvoie, pour un client, les infos sur tous les comptes qui peuvent être liés à un crédit
 * Les comptes ayant les caractéristiques suivant
 * - avec retrait possible
 * - avec dépot possible
 * - service financiers = 't'
 * - sans terme
 * - meme devise que le crédit
 * @param $id_client int Numéro du client
 * @param $devise char(3) Devise du crédit
 * @return Array Un Array indicé par le numéro du compte avec pour chaque compte un array associatif avec toutes les infos
 */
function getComptesLiaison ($id_client, $devise) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $cptes_liaison = array();

  $sql = "SELECT b.*, a.* FROM adsys_produit_epargne b, ad_cpt a WHERE a.id_ag = $global_id_agence AND a.id_ag = b.id_ag AND a.id_prod=b.id";
  $sql .= " AND a.id_titulaire='$id_client'";
  $sql .= " AND a.devise='$devise'";
  $sql .= " AND a.etat_cpte=1";
  $sql .= " AND b.retrait_unique='f'";
  $sql .= " AND b.depot_unique='f'";
  $sql .= " AND b.service_financier='t'";
  $sql .= " AND b.terme=0";
  $sql .= " AND NOT (a.etat_cpte=2) ORDER BY a.num_complet_cpte";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  while ($cpt = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $cptes_liaison[$cpt["id_cpte"]] = $cpt;

  return $cptes_liaison;
}

function transfertCaisseCentraleCptGuichet ($id_agence, $id_gui, $sens, $montant) {
  // Fonction qui transfère un montant entre la caisse centrale et un guichet
  // IN : $id_agence = ID de l'agence
  //      $id_gui = Numéro de guichet
  //      $sens = SENS_DEBIT => le compte client sera débité, le cpte guichet sera crédité
  //              SENS_CREDIT => le compte client sera crédité, le cpte guichet sera débité
  //      $montant = Montant du transfert
  // OUT : Objet Erreur

  // Les vérifications suviantes seront effectuées
  //  - Un compte créditeur ne peut devenir négatif
  //  - Un compte débiteur ne peut devenir positif

  global $dbHandler,$global_id_agence;
  global $adsys;

  $db = $dbHandler->openConnection();

  // Recherche des infos sur la caisse centrale
  $sql = "SELECT solde_cpt_caisse_centrale FROM ad_agc WHERE id_ag = $id_agence";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de trouver l'agence '$id_agence'"
  }
  $tmprow = $result->fetchrow();
  $solde_caisse_centrale = $tmprow[0];

  // Recherche des infos sur le compte guichet
  $sql = "SELECT encaisse FROM ad_gui WHERE id_ag = $id_agence AND id_gui = $id_gui;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le guichet $id_gui n'existe pas"
  }
  $tmprow = $result->fetchrow();
  $solde_gui = $tmprow[0];

  if ($sens == SENS_DEBIT) {
    // Vérification concernant le compte guichet
    if ($solde_gui > -$montant) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_DEB_POS, $id_gui);
    }
    $solde_caisse_centrale -= $montant;
    $solde_gui += $montant;

    //Met à  jour la DB
    $sql = "UPDATE ad_agc SET solde_cpt_caisse_centrale = solde_cpt_caisse_centrale - cast($montant as numeric) WHERE id_ag = $id_agence;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $sql = "UPDATE ad_gui SET encaisse = encaisse + cast($montant as numeric) WHERE id_ag = $id_agence AND id_gui = '$id_gui';";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  } else if ($sens == SENS_CREDIT) {
    // Vérification concernant le compte caisse centrale
    if ($solde_caisse_centrale > -$montant) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_DEB_POS, $id_agence);
    }
    $solde_caisse_centrale += $montant;
    $solde_gui -= $montant;

    //Met à  jour la DB
    $sql = "UPDATE ad_agc SET solde_cpt_caisse_centrale = solde_cpt_caisse_centrale + cast($montant as numeric) WHERE id_ag = $id_agence;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $sql = "UPDATE ad_gui SET encaisse = encaisse - cast($montant as numeric) WHERE id_ag = $id_agence AND id_gui = '$id_gui';";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  } else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Sens inconnu !"

  $sql = "UPDATE ad_gui SET date_enc = '".date("r")."' WHERE id_ag = $id_agence AND id_gui = '$id_gui';";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function transfertCaisseCentraleBanque ($id_agence, $id_banque, $sens, $montant) {
  // Fonction qui transfère un montant entre la caisse centrale et une banque
  // IN : $id_agence = ID de l'agence
  //      $id_gui = Numéro de guichet
  //      $sens = SENS_DEBIT => le compte client sera débité, le cpte guichet sera crédité
  //              SENS_CREDIT => le compte client sera crédité, le cpte guichet sera débité
  //      $montant = Montant du transfert
  // OUT : Objet Erreur

  // Les vérifications suviantes seront effectuées
  //  - Un compte créditeur ne peut devenir négatif
  //  - Un compte débiteur ne peut devenir positif

  global $dbHandler,$global_id_agence;
  global $adsys;

  $db = $dbHandler->openConnection();

  // Recherche des infos sur la caisse centrale
  $sql = "SELECT solde_cpt_caisse_centrale FROM ad_agc WHERE id_ag = $id_agence";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //
    //$result->getMessage();
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de trouver l'agence '$id_agence'"
  }
  $tmprow = $result->fetchrow();
  $solde_caisse_centrale = $tmprow[0];

  // Recherche des infos sur la banque
  $sql = "SELECT total_transfert FROM adsys_banques WHERE id_ag = $id_agence AND id = $id_banque";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //
    //$result->getMessage();
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La banque $id_banque n'existe pas"
  }
  $tmprow = $result->fetchrow();
  $solde_banque = $tmprow[0];

  if ($sens == SENS_DEBIT) {
    $solde_caisse_centrale -= $montant;
    $solde_banque += $montant;

    //Met à  jour la DB
    $sql = "UPDATE ad_agc SET solde_cpt_caisse_centrale = solde_cpt_caisse_centrale - cast($montant as numeric) WHERE id_ag = $id_agence;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //
      //$result->getMessage();
    }
    $sql = "UPDATE adsys_banques SET total_transfert = total_transfert + cast($montant as numeric) WHERE id_ag=$id_agence AND id = $id_banque";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // $result->getMessage()}
    } else if ($sens == SENS_CREDIT) {
      // Vérification concernant le compte caisse centrale
      if ($solde_caisse_centrale > -$montant) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_DEB_POS, $id_agence);
      }
      $solde_caisse_centrale += $montant;
      $solde_banque -= $montant;

      //Met à  jour la DB
      $sql = "UPDATE ad_agc SET solde_cpt_caisse_centrale = solde_cpt_caisse_centrale + cast($montant as numeric) WHERE id_ag = $id_agence;";
      $result=$db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
      $sql = "UPDATE adsys_banques SET total_transfert = total_transfert - cast($montant as numeric) WHERE id_ag=$id_agence AND id = $id_banque";
      $result=$db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // $result->getMessage()}
      }
    } else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Sens inconnu !"
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function transfertCpteAgenceCpteBanqueAgence ($id_agence, $id_banque, $sens, $montant) {

  // Fonction qui transfère un montant entre l'agence et une banque
  // IN : $id_agence = ID de l'agence
  //      $id_banque = Numéro de banque
  //      $sens = SENS_DEBIT => le compte client sera débité, le cpte guichet sera crédité
  //              SENS_CREDIT => le compte client sera crédité, le cpte guichet sera débité
  //      $montant = Montant du transfert
  // OUT : Objet Erreur

  // Les vérifications suviantes seront effectuées
  //  - Un compte créditeur ne peut devenir négatif
  //  - Un compte débiteur ne peut devenir positif

  global $dbHandler,$global_id_agence;
  global $adsys;

  $db = $dbHandler->openConnection();

  // Recherche des infos sur le compte agence
  $sql = "SELECT solde_cpt_agence FROM ad_agc WHERE id_ag = $id_agence;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'agence $id_a n'existe pas"
  }
  $tmprow = $result->fetchrow();
  $solde_ag = $tmprow[0];

  // Recherche des infos sur la banque
  $sql = "SELECT total_transfert FROM adsys_banques WHERE id_ag = $id_agence AND id = $id_banque";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La banque $id_banque n'existe pas"
  }
  $tmprow = $result->fetchrow();
  $solde_banque = $tmprow[0];

  if ($sens == SENS_DEBIT) {
    $solde_ag -= $montant;
    $solde_banque += $montant;

    //Met à jour la DB
    $sql = "UPDATE ad_agc SET solde_cpt_agence = solde_cpt_agence - cast($montant as numeric) WHERE id_ag = $id_agence;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $sql = "UPDATE adsys_banques SET total_transfert = total_transfert + cast($montant as numeric) WHERE id_ag=$global_id_agence AND id = $id_banque";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  } else if ($sens == SENS_CREDIT) {
    $solde_ag += $montant;
    $solde_banque -= $montant;

    //Met à  jour la DB
    $sql = "UPDATE ad_agc SET solde_cpt_agence = solde_cpt_agence + cast($montant as numeric) WHERE id_ag = $id_agence;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $sql = "UPDATE adsys_banques SET total_transfert = total_transfert - cast($montant as numeric) WHERE id_ag=$global_id_agence AND id = $id_banque";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  } else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Sens inconnu !"

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function transfertCptBanqueCptClient($id_banque, $id_cpte_cli, $sens, $montant) {
  // Fonction qui transfère un montant entre la banque et le compte d'un client
  // Arrive si un client reçoit de l'argent via le compte banque de l'IMF
  // IN : $id_banque = Numéro de banque
  //      $id_cpt_client = Numéro de compte du client.
  //      $sens = SENS_DEBIT => le compte banque sera débité, le cpte client sera crédité
  //              SENS_CREDIT => le compte banque sera crédité, le cpte client sera débité
  //      $montant = Montant du transfert
  // OUT : Objet Erreur

  // Les vérifications suviantes seront effectuées
  //  - Un compte créditeur ne peut devenir négatif
  //  - Un compte débiteur ne peut devenir positif

  global $dbHandler,$global_id_agence;
  global $adsys;

  $db = $dbHandler->openConnection();

  // Recherche des infos sur le compte client
  $sql = "SELECT id_cpte, solde, mnt_bloq, etat_cpte, id_prod,mnt_min_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = '$id_cpte_cli'";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de trouver le compte client $id_cpte_cli"
  }
  $tmprow = $result->fetchrow();
  $id_cpte_cli = $tmprow[0];
  $solde_cpte_cli = $tmprow[1];
  $mnt_bloq_cpte_cli = $tmprow[2];
  $etat_cpte_cli = $tmprow[3];
  $id_prod_cli = $tmprow[4];
  $mnt_min = $tmprow[5];
  // Recherche du sens du compte via le produit asocié au compte client
  $sql = "SELECT sens,mnt_max  FROM adsys_produit_epargne WHERE id_ag = $global_id_agence AND id = $id_prod_cli";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de trouver le produit n° $id_prod_cli"
  }
  $tmprow = $result->fetchrow();
  $sens_cpte_cli = $tmprow[0];

  $mnt_max = $tmprow[1];

  // Recherche des infos sur la banque
  $sql = "SELECT total_transfert FROM adsys_banques WHERE id_ag = $global_id_agence AND id = $id_banque";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La banque $id_banque n'existe pas"
  }
  $tmprow = $result->fetchrow();
  $solde_banque = $tmprow[0];

  if ($sens == SENS_DEBIT) {
    $solde_cpte_cli += $montant;
    $solde_banque -= $montant;
    // Pas de vérifications à faire au niveau de la banque , on ne gère pas la compta au niveau du compte banque
    // Vérifications compte client
    // Compte bloqué
    if ($etat_cpte_cli != '1') {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_BLOQUE, $id_cpte_cli);
    }
    // Montant maximum depasse ?
    if ($sens_cpte_cli == SENS_CREDIT) {
      if (($mnt_max > 0) && ($solde_cpte_cli > $mnt_max)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_MNT_MAX_DEPASSE, $id_cpte_cli);
      }
    }
    // Met aÂ  jour la DB
    $sql = "UPDATE ad_cpt SET solde = solde + cast($montant as numeric) WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte_cli;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $sql = "UPDATE adsys_banques SET total_transfert = total_transfert - cast($montant as numeric) WHERE id_ag=$global_id_agence AND id = $id_banque";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  } else if ($sens == SENS_CREDIT) {
    $solde_cpte_cli -= $montant;
    $solde_banque += $montant;

    // Vérification concernant le compte client
    if ($sens_cpte_cli == SENS_CREDIT) {// Si le compte est créditeur, il ne peut devenir négatif
      if ($solde_cpte_cli - $mnt_bloq_cpte_cli - $mnt_min < 0) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_CLI_NEG, $id_cpte_cli);
      }
    }

    if ($etat_cpte_cli != '1') {//Compte n'est pas ouvert
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_BLOQUE, $id_cpte_cli);
    }

    // Met à  jour la DB
    $sql = "UPDATE ad_cpt SET solde = solde - cast($montant as numeric) WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte_cli;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $sql = "UPDATE adsys_banques SET total_transfert = total_transfert + cast($montant as numeric) WHERE id_ag=$global_id_agence AND id = $id_banque";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
  } else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Sens inconnu !"


  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

function getLibelCompte($type_cpt, $num_cpt) {
  /* Renvoie le libellé d'un compte en fonction de son type.
     p.ex. si le type = guichet alors il renvoie le libellé du guichet
                      = client alors renvoie le n° complet */
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  switch ($type_cpt) {
  case 1 :
    $retour = $num_cpt;
    $sql = '';
    break; //Compte comptable
  case 2 :
    $retour = '';
    $sql = "SELECT num_complet_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte=$num_cpt";
    break; //Compte client
  case 3 :
    $retour = '';
    $sql = "SELECT libel_gui FROM ad_gui WHERE id_ag = $global_id_agence AND id_gui=$num_cpt";
    break; //Compte guichet
  case 4 :
    $retour = '';
    $sql = '';
    break; //Compte agence
  case 5 :
    $retour = '';
    $sql = '';
    break; //Compte chèque
  case 6 :
    $retour = '';
    $sql = '';
    break; //Compte caisse centrale
  case 7 :
    $retour = '';
    $sql = "SELECT libel FROM adsys_banques WHERE id_ag = $global_id_agence AND id=$num_cpt";
    break; //Compte banque
  case 8 :
    $retour = '';
    $sql = '';
    break; //Compte chèque agence
  default:
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Type compte inconnu!" break; //Autre
  }
  if ($sql != '') {
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $row = $result->fetchrow();
    $retour = $row[0];
  }
  $dbHandler->closeConnection(true);
  return $retour;
}

function mouvementeCptClientCptBanque($id_cpte_cli, $id_bqe, $sens, $montant) {
  // Fonction qui transfère $montant entre les comptes $id_cpt1 et le compte de la banque  $id_bqe
  // ATTENTION : Aucune vérification n'est faite à  ce niveau
  // Cette fonction ne peut êªtre appelée que pour des raisons exceptionnelles
  //      (clôture d'un compte, défection d'un client ...)
  // Pour un transfert "normal", utiliser la fonction transfertCptBanqueCptClient

  global $dbHandler,$global_id_agence;
  global $adsys;

  $db = $dbHandler->openConnection();

  // Recherche des infos sur le compte interne du client
  $sql = "SELECT solde FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = '$id_cpte_cli'";
  $result=$db->query($sql);
  if (DB::isError($result)) {

    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Impossible de trouver le compte client $id_cpt_cli"
  }

  $tmprow = $result->fetchrow();
  $solde_cpte_cli = $tmprow[0];

  // Recherche des infos sur le compte banque
  $sql = "SELECT total_transfert FROM adsys_banques WHERE id_ag = $global_id_agence AND id = $id_bqe";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La banque $id_bqe n'existe pas"
  }

  $tmprow = $result->fetchrow();
  $solde_banque = $tmprow[0];

  // Traitement proprement dit
  switch ($sens) {
  case SENS_DEBIT:
    $solde_cpte_cli -= $montant;
    $solde_banque += $montant;

    $sql = "UPDATE ad_cpt SET solde = solde - cast($montant as numeric) WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte_cli;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $sql = "UPDATE adsys_banques SET total_transfert = total_transfert + cast($montant as numeric) WHERE id_ag=$global_id_agence AND id = $id_bqe";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);

    break;
  case SENS_CREDIT:
    // Traitement proprment dit
    $solde_cpte_cli += $montant;
    $solde_banque -= $montant;

    $sql = "UPDATE ad_cpt SET solde = solde + cast($montant as numeric) WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte_cli;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $sql = "UPDATE adsys_banques SET total_transfert = total_transfert - cast($montant as numeric) WHERE id_ag=$global_id_agence AND id = $id_bqe";
    $result=$db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);

    break;
  default:
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le sens $sens n'est pas défini"
  }
}

/**
 * Met à jour le solde du compte de base d'un client
 * @param int $id_client N° du client concerné
 * @param $nouveau_solde c'est le nouveau solde à mettre à jour
 * @return ErrorObj Objet Erreur
 * @author Djibril NIANG
 * @since 2.9
 */
function updateSoldeCpteBaseCli($id_client, $nouveau_solde) {

  global $adsys,$global_id_agence;

  $requete = "UPDATE  ad_cpt SET solde = '$nouveau_solde'  where id_ag=$global_id_agence AND id_titulaire='$id_client' ";
  return executeDirectQuery($requete);

}


function setSoldeCpteCli($id_cpte, $sens, $montant, $devise, $isIAR=false) {
  /*

    Fonction qui met à jour le solde d'un compte client dans ad_cpt suite à une opération financière.
    Il faut vérifier que le solde ne peut pas être négatif sauf pour un compte dont le id produit est celui du type de compte de
    crédit.
    Important : on ne vérifie pes les soldes mini, c'est à l'appelant de le faire

     IN : $id_cpte = identifiant dans ad_cpt
          $sens = SENS_DEBIT => le compte interne est débité (signe de l'opération est -)
                  SENS_CREDIT => le compte interne est crédité (signe de l'opération est +)
          $montant = Montant du transfert sur le compte interne

     OUT : Objet Erreur

    */

  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();

  $id_prod_credit = getCreditProductID ($global_id_agence);

  $sql = "SELECT solde, id_prod, devise ,mnt_bloq,mnt_min_cpte,decouvert_max ";
  $sql .= "FROM ad_cpt ";
  $sql .= "WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte ";
  $sql .= "FOR UPDATE OF ad_cpt;";


  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  //FIXME : vérifier si on a trouvé quelque chose

  $row = $result->fetchrow();
 
  $solde = $row[0];
  //REL-101 : on verifie si le montant du compte client contient les deicmaux ex. 50000.536
  $hasDecimalSoldeCpte = hasDecimalMontant($solde);

  $ProdCpte = $row[1];
  $devise_cpte = $row[2];

  // #514+PP165 : Arrondir le montant a passer :
  $montantIARnonArrondie = $montant; //Montant temporaire pour garder le montant non arrondie IAR si c'est une operation 375/20 il sera utile sinon servira a rien REL-80
  $soldeCompte_ini = $solde; //On garde le montant initial recuperé du compte client pour la difference entre ce montant et le montant mouvementé REL-80

  $montant = arrondiMonnaiePrecision($montant, $devise);

  $cpte_int_couru = get_calcInt_cpteInt(false, true, null);
  if ((!is_null($cpte_int_couru) && $isIAR === true) || $hasDecimalSoldeCpte === true){ //Exception pour operation Remboursement IAR depuis compte client REL-80 + REL-101
    $montant = $montantIARnonArrondie; //Recuperation du montant non arrondie IAR
  }
  if ($sens == SENS_DEBIT) {
    $solde = $solde - $montant;
  }
  elseif ($sens == SENS_CREDIT) {
    $solde = $solde + $montant;
  }

  if ((!is_null($cpte_int_couru) && $isIAR === true) || $hasDecimalSoldeCpte === true){ //Exception pour operation Remboursement IAR pour gerer proprement la difference si IAR est parametré REL-80 + REL-101
    $temp_solde = $solde;
    $abs_diff = abs($solde);
    if ($abs_diff < 1){
      $solde = arrondiMonnaiePrecision(abs($solde), $devise);
    }
    if ($abs_diff < 1 && $soldeCompte_ini <0 && $temp_solde <0){
      $solde = -1 * $solde;
    }
  }
  //$solde = round($solde, EPSILON_PRECISION);

  //verifier de quel type de compte client il s'agit : compte d'epargne ou de credit
  if ($ProdCpte ==  $id_prod_credit) {
    //Si compte de crédit, le solde doit être débiteur et ne peut devenir positif
    if ($solde > 0) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_DEB_POS, _("compte client")." $id_cpte");
    }
  } else {
  	 $mnt_bloq = $row[3];
  	 $mnt_min_cpte = $row[4];
  	 $decouvert_max = $row[5];
  	$solde1 = $solde + $decouvert_max;
   if ($solde1 < 0) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_CRED_NEG, _("compte client")." $id_cpte");
    }
  }
  
  // Vérification sur la devise
  if ($devise_cpte != $devise) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Tentative de mouvementer le compte client $id_cpte dans la devise $devise"
  }

  //mettre à  jour le solde
  $sql = "UPDATE ad_cpt SET solde = $solde WHERE id_ag=$global_id_agence AND id_cpte=$id_cpte;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Retourne le solde d'un compte interne à la date demandée.
 *
 * @param int $id_cpte L'identifiant du compte interne
 * @param date $date La date à laquelle il faut calculer le solde
 * @return float Le solde du compte interne
 */
function calculeSoldeCpteInterne($id_cpte, $date) {
   global $global_id_agence, $dbHandler;
  // On a comme référence le solde à la date d'aujourd'hui
  $infos_cpte = getAccountDatas($id_cpte);
  $solde = $infos_cpte["solde"];

  // Il faut ensuite remonter jusqu'à la date demandée en annulant les opérations
  // D'abord ajouter le total des opérations au débit
  $sql = "SELECT sum(m.montant) FROM ad_ecriture e, ad_mouvement m, ad_his h
         WHERE e.id_ag = $global_id_agence AND e.id_ag = m.id_ag AND e.id_ag = h.id_ag
         AND e.id_ecriture = m.id_ecriture AND e.id_his = h.id_his
         AND date(h.date) BETWEEN date('$date') AND date(now())
         AND sens = 'd' AND cpte_interne_cli = '$id_cpte'";

  $result = executeDirectQuery($sql, TRUE);
  if ($result->errCode == NO_ERR) {
    $solde += $result->param[0];
  }

  // Ensuite soustraire le total des opérations au crédit
  $sql1 = "SELECT sum(m.montant) FROM ad_ecriture e, ad_mouvement m, ad_his h
         WHERE e.id_ag = $global_id_agence AND e.id_ag = m.id_ag AND e.id_ag = h.id_ag
         AND e.id_ecriture = m.id_ecriture AND e.id_his = h.id_his
         AND date(h.date) BETWEEN date('$date') AND date(now())
         AND sens = 'c' AND cpte_interne_cli = '$id_cpte'";

  $result1 = executeDirectQuery($sql1, TRUE);
  if ($result1->errCode == NO_ERR) {
    $solde -= $result1->param[0];
  }

  return $solde;
}
/**
 * Création d'une liste de banque par agence
 * @param int $id_ag identifiant de l'agence
 * @return array un tableau contenant la clé et le nom de la banque
 */
function getListeBanque() {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_banque where id_ag=$global_id_agence ";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_banque"]] = $row['nom_banque'];

  return $DATAS;

}

function getInfosBanque($id_bqe=NULL,$id_ag=NULL) {
  /*
  Renvoie les infos sur une banque.
  S'il n'y a rien on renvoie NULL
  */

  global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_banque";
  if ($id_bqe != NULL) {
    if ($id_ag == NULL)
      $sql .="  WHERE id_banque = $id_bqe";
    else
      $sql .="  WHERE id_banque = $id_bqe and id_ag = $id_ag ";
  }
  elseif ($id_ag != NULL) $sql .="  WHERE id_ag = $id_ag";
  $sql.= ";";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;

  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id_banque"]] = $row;

  return $DATAS;

}

/**
 * Renvoie les informations concernant un correspondant déterminé : les champs de la table + la devise des comptes (qui doit être la même pour les trois comptes) + le nom de la banque associée.
 * @author Bernard De Bois
 * @param int $id_cor Identifiant du correspondant (clé primaire de la table adsys_correspondant)
 * @return array Renvoie un tableau contant les champs de la table adsys_correspondant un champs "nom_banque" et un champs "devise".  Si les devises des trois comptes sont différentes, le champs "devise" retourné est NULL.
 */
function getInfosCorrespondant($id_cor) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT a.*, b.nom_banque
         FROM adsys_correspondant a, adsys_banque b
         WHERE a.id_ag = $global_id_agence AND a.id_ag = b.id_ag AND a.id=$id_cor AND a.id_banque=b.id_banque; ";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  //recherche de la devise des comptes du correspondant.
  $param = array();
  $param['num_cpte_comptable'] = $row['cpte_bqe'];
  $cpte_bqe = getComptesComptables($param);
  $cpte_bqe = $cpte_bqe[$row['cpte_bqe']];

  $param = array();
  $param['num_cpte_comptable'] = $row['cpte_ordre_deb'];
  $cpte_ordre_deb = getComptesComptables($param);
  $cpte_ordre_deb = $cpte_ordre_deb[$row['cpte_ordre_deb']];

  $param = array();
  $param['num_cpte_comptable'] = $row['cpte_ordre_cred'];
  $cpte_ordre_cred = getComptesComptables($param);
  $cpte_ordre_cred = $cpte_ordre_cred[$row['cpte_ordre_cred']];

  if ($cpte_bqe['devise']==$cpte_ordre_deb['devise'] && $cpte_bqe['devise']==$cpte_ordre_cred['devise']) {
    $row['devise']=$cpte_bqe['devise'];
  } else {
    $row['devise']=NULL;
  }

  $dbHandler->closeConnection(true);
  return $row;
}

function getComptesClients($Where) {
  // Fonction renvoyant l'ensemble des comptes clients associés

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  if ($Where == '')
    $sql = "SELECT * FROM ad_cpt ORDER BY id_titulaire DESC";
  else
    $sql = "SELECT * FROM ad_cpt $Where ORDER BY id_titulaire DESC";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}

/**
 * Fonction donnant les états MNME présentent séparément les montants en devise nationale et en devise étrangère.
 * @author Mamadou mbaye
 * @return array Les données des différents états.
 */
function calculeSoldesMNME1($DATA,&$temp) {
  global $global_multidevise;
  global $global_monnaie;

  $num_cpte_comptable=$DATA["num_cpte_comptable"];
  $solde=abs($DATA["solde"]);

  if (!isCentralisateur($num_cpte_comptable)) {
    if ($DATA["devise"] ==$global_monnaie ) {
      $DATA_CPTES[$num_cpte_comptable]["mn"]=$solde;
      $DATA_CPTES[$num_cpte_comptable]["me"]=0;
      $DATA_CPTES[$num_cpte_comptable]["tot"]=$solde;
    } else if ($DATA["devise"] !=$global_monnaie ) {
      $solde=calculeCV($DATA["devise"], $global_monnaie, $solde);

      $DATA_CPTES[$num_cpte_comptable]["mn"]=0;
      $DATA_CPTES[$num_cpte_comptable]["me"]=$solde;
      $DATA_CPTES[$num_cpte_comptable]["tot"]=$solde;
    }
  } else {
    $mn=0;
    $me=0;
    $sous_comptes= getSousComptesDirecte($num_cpte_comptable);
    foreach($sous_comptes as $key=>$value) {
      $montant= calculeSoldesMNME1($value,$temp);
      $mn+= $montant[$key]["mn"];
      $me+=$montant[$key]["me"];
    }
    $DATA_CPTES[$num_cpte_comptable]["mn"]=$mn;
    $DATA_CPTES[$num_cpte_comptable]["me"]=$me;
    $DATA_CPTES[$num_cpte_comptable]["tot"]=$me+$mn;
  }
  $temp=array_merge($temp, $DATA_CPTES);
  return $DATA_CPTES;
}

/**
 * Renvoie les soldes en MN et en ME du compte $compte
 * @author Mamadou Mbaye
 * @param  array $CPT Infos sur le compte à traiter (un array est utilisé plutt qu'un numéro à des fins d'optimisation)
 * @return array
 */
function calculeSoldeMNME($CPT) {
  global $global_multidevise;
  global $global_monnaie;
  global $global_id_agence;

  $RES = array();

  $num_cpte = $CPT["num_cpte_comptable"];
  $solde = $CPT["solde"];
  $devise = $CPT["devise"];

  if (!isCentralisateur($num_cpte)) {
    if ($devise == $global_monnaie) {
      $RES["mn"] = $solde;
      $RES["me"] = 0;
      $RES["tot"] = $solde;
    } else if ($devise != $global_monnaie ) {
      $solde = calculeCV($devise, $global_monnaie, $solde);

      $RES["mn"]=0;
      $RES["me"]=$solde;
      $RES["tot"]=$solde;
    }
  } else {
    $mn=0;
    $me=0;
    $sous_comptes = getSousComptes($num_cpte, false);
    foreach($sous_comptes as $key=>$value) {
      $SUBRES = calculeSoldeMNME($value);
      $mn += $SUBRES["mn"];
      $me += $SUBRES["me"];
    }

    $RES["mn"] = $mn;
    $RES["me"] = $me;
    $RES["tot"] = $me + $mn;
  }

  return $RES;
}

/**
 * Récupère les soldes en Monnaie Nationale et en Monnaie Etrangère pour tous les comptes
 * @author Mamadou Mbaye
 * @return Array
**/
function getSituationMNME() {

  $tmp=array();
  global $global_multidevise;
  global $global_monnaie;
  global $global_id_agence;
  $tmp=array();
  $DATA=array();

  // Récupération de tous les compte qui n'ont pas de compte parent
  $cptes = getComptesComptables();

  if (isset($cptes))
    foreach($cptes as $key => $value) {
    $RES[$key] = calculeSoldeMNME($value);
    $RES[$key]["libel"] = $value["libel_cpte_comptable"];
  }

  return $RES;
}

/**
 * Vérifie la validité d'un numéro complet de compte en vérifiant son pattern (suivant le type de numérotation de l'agence)et le check digit.
 * @author Antoine Guyette
 * @param  string $num_complet_cpte Numéro complet d'un compte
 * @return boolean Renvoie true si le compte est correct
 */
function isNumComplet ($num_complet_cpte) {
  $id_agc = getNumAgence();
  $AGD = getAgenceDatas($id_agc);
  $type_num_cpte = $AGD['type_numerotation_compte'];
  // Numérotation "Standard"
  if ($type_num_cpte == 1) {
    if (strlen($id_agc) <= 3){
      $v_id_agc = 3; //prendre en consideration des ids agence dont le nombre des chiffres est moins ou egal 3
    }
    else {
      $v_id_agc = strlen($id_agc); //prendre en consideration des ids agence dont le nombre des chiffres est superieur de 3
    }
    if (ereg("([[:digit:]]{".$v_id_agc."})-([[:digit:]]{6})-([[:digit:]]{2})-([[:digit:]]{2})", $num_complet_cpte)) {
      $num_sans_check = ereg_replace("([[:digit:]]{".$v_id_agc."})-([[:digit:]]{6})-([[:digit:]]{2})-([[:digit:]]{2})", "\\1\\2\\3", $num_complet_cpte);
      $num_check = ereg_replace("([[:digit:]]{".$v_id_agc."})-([[:digit:]]{6})-([[:digit:]]{2})-([[:digit:]]{2})", "\\4", $num_complet_cpte);
      if (fmod($num_sans_check, 97) == $num_check) {
        return true;
      }
    }
  }
  // Numérotation "RDC"
  else if ($type_num_cpte == 2) {
    if (ereg("([[:digit:]]{4})-([[:digit:]]{5})([[:digit:]]{2})-([[:digit:]]{2})", $num_complet_cpte)) {
      $num_sans_check = ereg_replace("([[:digit:]]{4})-([[:digit:]]{5})([[:digit:]]{2})-([[:digit:]]{2})", "\\1\\2\\3", $num_complet_cpte);
      $num_check = ereg_replace("([[:digit:]]{4})-([[:digit:]]{5})([[:digit:]]{2})-([[:digit:]]{2})", "\\4", $num_complet_cpte);
      if (fmod($num_sans_check, 97) == $num_check) {
        return true;
      }
    }
  }

  // Numérotation "Rwanda"
  else if ($type_num_cpte == 3) {
    if (ereg("([[:digit:]]{3})-([[:digit:]]{7,10})-([[:digit:]]{2})", $num_complet_cpte)) {
      return true;
    }
  }

  // Numérotation "Avec code antenne et numéro bureau"
  else if ($type_num_cpte == 4) {
    if (ereg("([[:digit:]]{2})-([[:digit:]]{6})([[:digit:]]{2})-([[:digit:]]{2})", $num_complet_cpte)) {
      $num_sans_check = ereg_replace("([[:digit:]]{4})-([[:digit:]]{6})([[:digit:]]{2})-([[:digit:]]{2})", "\\2\\3", $num_complet_cpte);
      $num_check = ereg_replace("([[:digit:]]{4})-([[:digit:]]{6})([[:digit:]]{2})-([[:digit:]]{2})", "\\4", $num_complet_cpte);
      if (fmod($num_sans_check, 97) == $num_check) {
        return true;
      }
    }
  }
  return false;
}


function generateRequestRefNum() {
    return 'ATM' . md5(uniqid(mt_rand(0, 99999)));
}

/**
 * Traite et enregistre une commande de cartes ATM.
 *
 * @param int $a_id_cpte L'identifiant du compte pour lequel un chèquier est demandé.
 * @param string $nom_carte Le nom qui apparait sur la carte .
 * @param string $titre Mr, Madame, Mamzel, etc...
 * @param string $num_identite_passeport
 * @param string $resident Si resident ou pas
 * @param string $reason_for_issue le motif de la delivrence
 * @param string $priorite Haute Basse Moyen
 * @param string $frais Les frais de commande
 * @return ErrorObj
 */
function doCommandeCarte($branch_code, $first_name, $middle_name, $last_name, $id_cpte, $id_client, $nom_carte, $titre, $num_identite_passeport, $type_client, $resident, $reason_for_issue, $type_compte, $devise, $priorite, $guichet, $frais) {
    //TODO : GET FRAIS COMMANDE
    global $dbHandler, $global_nom_login, $global_id_agence;
    $db = $dbHandler->openConnection();
    $cpte = getAccountDatas($id_cpte);

    // On vérifie d'abord s'il n'y pas une demande déjà en cours
    $result = isNotCarteActive($id_cpte);
    if ($result->errCode != NO_ERR) {
        return $result;
    }

    $request_ref_num = generateRequestRefNum();
    if ($frais > 0) {
        // S'il y a des frais de commande : préparer les écritures comptables de perception des frais
        // Débit du compte d'épargne par le crédit d'un compte de produit
        global $global_monnaie;
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
        //verifier le solde disponible
        $solde_disponible = getSoldeDisponible($id_cpte);
        if ( ($solde_disponible - $frais) < 0){
            return  new ErrorObj(ERR_MNT_MIN_DEPASSE, $id_cpte);
        }
        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
//        if ($cptes_substitue["cpta"]["debit"] == NULL) {
//            $dbHandler->closeConnection(false);
//            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
//        }
        $cptes_substitue["int"]["debit"] = $id_cpte;
//        $err = effectueChangePrivate($cpte["devise"], $global_monnaie, $frais, 472, $cptes_substitue, $comptable);
//        if ($err->errCode != NO_ERR) {
//            $dbHandler->closeConnection(false);
//            return $err;
//        }

        $result = ajout_historique(41, $cpte["id_titulaire"], $request_ref_num, $global_nom_login, date("r"), $comptable, NULL);
        if ($result->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $result;
        }
    }
    $data = array();
    $data['id_client']              = $id_client;
    $data['request_ref_num']        = generateRequestRefNum();
    $data['branch_code']            = $branch_code;
    $data['first_name']             = $first_name;
    $data['middle_name']            = $middle_name;
    $data['last_name']              = $last_name;
    $data['date_cmde']              =  date("d/m/Y");
    $data['id_ag']                  =  $global_id_agence;
    $data['frais']                  = $frais;
    $prestataire                    = getPrestataire('RSWITCH_RW');
    $data['id_prestataire']         = $prestataire['id_prestataire'];
    $data['nom_carte']              = $nom_carte;
    $data['titre']                  = $titre;
    $data['num_identite_passeport'] = $num_identite_passeport;
    $data['type_client']            = $type_client;
    $data['resident']               = $resident;
    $data['reason_for_issue']       = $reason_for_issue;
    $data['id_cpte']                = $id_cpte;
    $data['type_compte']            = $type_compte;
    $data['devise']                 = $devise;
    $data['priorite']               = $priorite;
    $data['guichet']                = $guichet;
    $data['frais']                  = is_null($frais) ? 0 : $frais;
    $result                         = executeDirectQuery(buildInsertQuery('ad_gest_carte',$data));
    if ($result->errCode == NO_ERR) {
        $dbHandler->closeConnection(true);
    }
    return $result;
}


/**
 * Recuperation des commande de carte ATM a envoyer a RSwitch.
 *
 * @return ErrorObj
 */
function getCommandesCartes($etat='null'){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    // Demandes de cartes ATM
    $sql = "SELECT * FROM ad_gest_carte WHERE id_ag = $global_id_agence AND etat is $etat order by id ";

    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $retour = array();

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        array_push($retour, $row);
    }

    $db = $dbHandler->closeConnection(true);
    return $retour;
}


/**
 * Traite et enregistre une commande de chèquier.
 *
 * @author Antoine Delvaux
 * @since 2.6
 * @param int $a_id_cpte L'identifiant du compte pour lequel un chèquier est demandé.
 * @param int $a_nbre_carnets Le nombre de chèquier commandés .
 * @param int $a_frais Les frais afférents à la commande.
 * @return ErrorObj
 */
function doCommandeChequier($a_id_cpte, $a_nbre_carnets, $a_frais) {
  global $dbHandler, $global_nom_login, $global_id_agence;
  $db = $dbHandler->openConnection();
  $cpte = getAccountDatas($a_id_cpte);

  // On vérifie d'abord s'il n'y pas une demande déjà en cours
  $result = isNotDemandeChequier($a_id_cpte);
  if ($result->errCode != NO_ERR) {
    return $result;
  } 
  


  if ($a_frais > 0) {
    // S'il y a des frais de commande : préparer les écritures comptables de perception des frais
    // Débit du compte d'épargne par le crédit d'un compte de produit
    global $global_monnaie;
    $comptable = array();
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    //verifier le solde disponible
    $solde_disponible = getSoldeDisponible($a_id_cpte);
  	if ( ($solde_disponible - $a_frais) < 0){
 	   return  new ErrorObj(ERR_MNT_MIN_DEPASSE, $a_id_cpte);
 	}
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($a_id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["debit"] = $a_id_cpte;
    $err = effectueChangePrivate($cpte["devise"], $global_monnaie, $a_frais, 472, $cptes_substitue, $comptable);
    if ($err->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $err;
    }

    $result = ajout_historique(41, $cpte["id_titulaire"], $a_nbre_carnets, $global_nom_login, date("r"), $comptable, NULL);
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $result;
    }
  }
  $data=array();
  $data['id_cpte']= $a_id_cpte;
  $data['date_cmde'] =  date("d/m/Y");
  $data['id_ag'] =  $global_id_agence;
  $data['nbre_carnets'] =  $a_nbre_carnets;
  $data['etat'] = 1;
  $data['frais'] = is_null($a_frais) ? 0 : $a_frais;
  $result = executeDirectQuery(buildInsertQuery('ad_commande_chequier',$data));
  if ($result->errCode == NO_ERR) {
    $dbHandler->closeConnection(true);
  }
  return($result);
}
/**
 * Ajout d'un chequier
 *
 * @author Arès
 * @since 3.4
 * @param array a_data tableau contenant les informations du cheque à ajouter
 * @return ErrorObj Un ErrorObj.
 */
function insertChequier($a_data) {
  global $global_id_agence;
  $a_data["id_ag"]=$global_id_agence;
  //$a_data["etat_chequier"] = 1;
   $a_data["etat_chequier"] = 0;
  $a_data["date_livraison"] = date('d/m/Y');
  // vérifier si les numero de series n'existe pas dans la base
  $result = existeValeursSerieChequiers($a_data["num_first_cheque"],$a_data["num_last_cheque"]);
  if ($result->errCode != NO_ERR) {
    return $result;
  } 
  $sql=buildInsertQuery("ad_chequier",$a_data);
  return(executeDirectQuery( $sql, TRUE));
}
/**
 * Retourne l'état de la commande d'un chèquier pour un compte donné ainsi que la date de commande.
 *
 * @author Antoine Delvaux
 * @since 2.6
 * @param int $a_id_cpte Le numéro de compte pour lequel on veut connaître l'état de commande.
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant l'état de la commande ainsi que la date de la commande (le cas échéant).
 */
function getEtatCommandeChequier($a_id_cpte) {
  global $global_id_agence;
  return(executeDirectQuery("SELECT etat, date_cmde FROM ad_commande_chequier WHERE id_ag = $global_id_agence AND id_cpte = $a_id_cpte;", TRUE));
}

/**
 * fonction qui vérifie  s'il n'y a pas  une demande de chèquier en cours
 *
 * @author Arès
 * @since 3.4
 * @param int $a_id_cpte Le numéro de compte pour lequel on veut verifier  s'il y a   une demande  en cours.
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant true s'il n'ya pas de demande, sinon le nombre de chèquier en cours de demande.
 */
function isNotDemandeChequier($a_id_cpte) {
  global $global_id_agence;
  $sql="SELECT COUNT (*) FROM ad_cpt a, ad_commande_chequier b WHERE a.id_ag=b.id_ag AND  a.id_ag = $global_id_agence AND a.id_cpte=b.id_cpte AND b.id_cpte=$a_id_cpte AND b.etat IN (0,1,2) ";
  $result=executeDirectQuery($sql, TRUE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else if ($result->param[0] > 0) {
    // Une demande de chèquier est déjà en cours !
    return new ErrorObj(ERR_CMD_CHEQUIER_ENCOURS, $result->param[0]);
  }
  return new ErrorObj(NO_ERR, true);
}

/**
 * fonction qui vérifie  s'il n'y a pas  une carte est active
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant true s'il n'ya pas de demande, sinon le nombre de de demandes de cartes.
 */
function isNotCarteActive($a_id_cpte) {
    global $global_id_agence;
    $sql="SELECT COUNT (*) FROM ad_cpt a, ad_gest_carte b WHERE a.id_ag=b.id_ag AND  a.id_ag = $global_id_agence AND a.id_cpte=b.id_cpte AND b.id_cpte=$a_id_cpte AND (b.etat = null OR b.etat NOT IN (1,2)) ";
    $result=executeDirectQuery($sql, TRUE);
    if ($result->errCode != NO_ERR) {
        return $result;
    } else if ($result->param[0] > 0) {
        // Une carte active est déjà associée à ce numéro de compte !
        return new ErrorObj(ERR_CARTE_ACTIVE, $result->param[0]);
    }
    return new ErrorObj(NO_ERR, true);
}

/**
 * Retourne l'état du chèquier pour un chèquier donné 
 *
 * @author Arès
 * @since 3.4
 * @param int $a_id_chequier L'identifaint du chèquier pour lequel on veut connaître l'état .
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant l'état de la commande ainsi que la date de la commande (le cas échéant).
 */
function getEtatChequier($a_id_chequier) {
  global $global_id_agence;
  return(executeDirectQuery("SELECT etat_chequier FROM ad_chequier WHERE id_ag = $global_id_agence AND id_chequier = $a_id_chequier;", TRUE));
}

/**
  * Donne la liste de tous les comptes pour lesquels un chèquier est à imprimer (etat = 1).
  *
  * @author Antoine Delvaux
  * @since 2.6
  * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant la liste des identifiants de compte pour lesquels un chèquier est à imprimer.
  */
function getListChequiersPrint() {
  global $global_id_agence;
  $sql="SELECT b.*,num_complet_cpte,id_titulaire FROM ad_cpt a, ad_commande_chequier b WHERE a.id_ag=b.id_ag AND  a.id_ag = $global_id_agence AND a.id_cpte=b.id_cpte AND b.etat = 1";
  return(executeDirectQuery($sql));
}

/**
 * Donne la liste des chèquiers pour un client donné 
 * @author Antoine Delvaux
 * @since 2.6
 * @param int $a_id_client L'identifiant du client
 * @param int $a_etat_chequier L'etat du chèquier
 * @param int $a_statut statut du chèquier
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant la liste des chèquier du client pour lequel l'état du chèquier est specifié.
 */
function getListChequiers($a_id_client,$a_etat_chequier=NULL,$a_statut=NULL) {
  global $global_id_agence;
  $sql="SELECT b.* FROM ad_cpt a, ad_chequier b WHERE a.id_ag=b.id_ag AND  a.id_ag = $global_id_agence AND a.id_cpte=b.id_cpte AND id_titulaire = $a_id_client ";
  if(!is_null($a_etat_chequier)) $sql.=" AND b.etat_chequier =$a_etat_chequier ";
  if(!is_null($a_statut)) $sql.=" AND b.statut =$a_statut ";
  return(executeDirectQuery($sql));
}

/**
 * Confirmation de l'impression des chèquiers.  etat_chequier passe alors à 2
 *
 * @author Antoine Delvaux
 * @since 2.6
 * @param array $a_id_cptes Identifiant des comptes pour lequels un chèquier a été imprimé.
 * @return ErrorObj
 */
function setChequiersPrinted($a_id_cmde_chequiers) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  foreach ($a_id_cmde_chequiers as $id => $id_chequier) {
    // On vérifie d'abord si une demande de chèquier a bien été introduite pour ce chequier
    $result = getEtatChequier($id_chequier); 
    if ($result->errCode != NO_ERR) {
      return $result;
    } else if ($result->param[0] != 1) {
      // Une demande de chèquier n'existe pas !
      return new ErrorObj(ERR_NO_CMD_CHEQUIER, $result->param);
    }
    
    $new_values = array();
    $new_values["etat"] = 2;
    $new_values["date_impr"]= date("d/m/Y");
    $sql = buildUpdateQuery("ad_commande_chequier", $new_values, array("id_chequier"=>$id_chequier,'id_ag'=>$global_id_agence));
    $result = executeQuery($db, $sql);
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $result;
    }
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Confirmation de la remise du chèquier d'un compte au client. etat_chequier passe alors à 0 et la date_demande_chequier à NULL.
 *
 * @author Antoine Delvaux
 * @since 2.6
 * @param int $a_id_cpte Identifiant du compte pour lequel un chèquier a été remis.
 * @return ErrorObj
 */
function setChequierRemis($a_id_chequier) {
  global $global_id_agence;
  // On vérifie d'abord si un chèquier a bien été imprimé pour ce compte
  $result = getEtatChequier($a_id_chequier);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else if ($result->param[0] != 0) {
    // Un chèquier imprimé n'existe pas !
    return new ErrorObj(ERR_NO_CHEQUIER_IMPR, $result->param);
  }

  $new_values = array();
  $new_values["etat_chequier"] = 1;
  $new_values["statut"] = 1;
  $new_values["date_livraison"] = date("d/m/Y");
  $sql = buildUpdateQuery("ad_chequier", $new_values, array("id_chequier"=>$a_id_chequier,'id_ag'=>$global_id_agence));
  return(executeDirectQuery($sql));
}

/**
 * Renvoie le dernier numéro du chèque utilisé pour le compte passé en paramètre.
 *
 * @author Arès
 * @since 3.4
 * @param int $a_id_cpte L'identifiant du compte
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant le dernier numéro du chéque .
 */
function getNumLastChequiers($a_id_cpte) {
  global $global_id_agence;
  return(executeDirectQuery("SELECT num_last_cheque from ad_chequier WHERE id_ag = $global_id_agence AND id_cpte =$a_id_cpte AND id_chequier = ( SELECT MAX(id_chequier) FROM ad_chequier WHERE id_cpte=$a_id_cpte)  ", TRUE));
}
/**
 * Liste des chequiers en donnant l'identifant du compte d'epargne.
 *
 * @author Arès
 * @since 3.4
 * @param int $a_id_cpte L'identifiant du compte
 * @param	int $a_etat_chequier L'etat du chequier
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant la liste des chequiers .
 */
function getChequiersByCpte($a_id_cpte=NULL,$a_etat_chequier=NULL) {
  global $global_id_agence;
  $sql="SELECT * from ad_chequier WHERE id_ag = $global_id_agence ";
  if(! is_null($a_id_cpte)) $sql.=" AND id_cpte =$a_id_cpte ";
  if(!is_null($a_etat_chequier)) $sql.=" AND etat_chequier =$a_etat_chequier ";
  return(executeDirectQuery($sql));
}

/**
 * Renvoie les informations d'un chequiers .
 *
 * @author Arès
 * @since 3.4
 * @param int $a_id_cpte L'identifiant du compte
 * @param	int $a_etat_chequier L'etat du chequier
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant la liste des chequiers .
 */
function getChequiers($a_id_chequier,$a_statut=Null,$a_etat=NULL) {
  global $global_id_agence;
  $sql="SELECT * from ad_chequier WHERE id_ag = $global_id_agence ";
  if(! is_null($a_id_chequier)) $sql.=" AND id_chequier =$a_id_chequier ";
  if(! is_null($a_statut)) $sql.=" AND statut =$a_statut ";
  if(! is_null($a_etat)) $sql.=" AND etat_chequier =$a_etat ";
  
  return(executeDirectQuery($sql));
}


/**
 * Renvoie les informations d'un chequier en donnant le numero du chèque .
 *
 * @author Arès
 * @since 3.4
 * @param int $a_id_cpte L'identifiant du compte
 * @param	int $a_etat_chequier L'etat du chequier
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant la liste des chequiers .
 */
function getChequierByNumCheque($a_num_cheque,$a_num_cpte=NULL) {
  global $global_id_agence;
  $sql="SELECT * from ad_chequier WHERE id_ag = $global_id_agence AND (num_first_cheque <= $a_num_cheque AND num_last_cheque>=$a_num_cheque) ";
  if(! is_null($a_num_cpte)) $sql.=" AND id_cpte =$a_num_cpte ";
  
  return(executeDirectQuery($sql));
}

/**
 * Verifier si les valeurs comprise dans l'intervalle [num_first_cheque  num_last_cheque] existe dans la base.
 *
 * @author Arès
 * @since 3.4
 * @param int $a_id_cpte L'identifiant du compte
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant le dernier numéro du chéque .
 */
function existeValeursSerieChequiers($num_first_cheque,$num_last_cheque) {
  global $global_id_agence;
  $sql=" SELECT count(*) from ad_chequier where (num_first_cheque <= $num_first_cheque AND num_last_cheque>=$num_first_cheque) OR (num_first_cheque <=$num_last_cheque AND num_last_cheque>=$num_last_cheque)";
  $sql .=" AND id_ag = $global_id_agence ";
  $result=executeDirectQuery( $sql, TRUE);
  if ($result->errCode != NO_ERR) {
  	return $result;
  } else if ($result->param[0] > 0) {
  	// Une valeur au moins de la serie est deja utilisée dans la base !
  	$msg=sprintf (_("Au moins une valeur comprise dans l'intervalle [%s-%s] est dèjà utilisé comme numero de chèque "),$num_first_cheque,$num_last_cheque);
  	$msg.= _("Veuillez changer l'intervalle de valeurs svp!");
    return new ErrorObj(ERR_CHEQUIER_SERIE_EXIST, $msg);
  }
  return new ErrorObj(NO_ERR, false);
}

/**
 * Verifie si le cheque n'est pas mis en opposition ou déjà encaissé.
 *
 * @author Arès
 * @since 3.4
 * @param int $a_num_cheque L'identifiant du cheque
 * @param	int $a_num_cpte L'identifiant du compte
 * @return ErrorObj Un ErrorObj avec comme paramètre true si tout c'est bien passé sinon  un array contenant la raison .
 */
function valideCheque($a_num_cheque,$a_num_cpte=NULL) {
  global $global_id_agence;
  global $adsys;
  $result=getChequierByNumCheque($a_num_cheque,$a_num_cpte);
  $msg_numCheque = _(" numéro chèque ");
  $msg_etatCheque = _(" Etat du chèque ");
  
  if ($result->errCode != NO_ERR) {
  	return $result;
  } elseif (count($result->param)== 1) {
  	$chequier=$result->param[0];
  	//verifier que le chéquier est actif (remis au client)
  	if ($chequier['statut'] != 1) {
  		$param=array();
  		$param[$msg_etatCheque]= $adsys['adsys_etat_chequier'][$chequier['etat_chequier']];
  		return new ErrorObj(ERR_CHEQUIER_INACTIF, $param);
  	}
  	//verifier que le cheque n'est pas utilisé
  	$result=getCheque($a_num_cheque);
  	if($result->errCode!= NO_ERR) {
  		return $result;
  	} elseif (count($result->param)>0) {
  		$param=array();
  		$param[$msg_etatCheque] = $adsys["adsys_etat_cheque"][$result->param[0]['etat_cheque']];
  		$param[$msg_numCheque] = $result->param[0]['id_cheque'];
  		if ($result->param[0]['is_opposition'] == 't') {
  			return new ErrorObj(ERR_CHEQUE_OPPOSITION, $param);
  		}else {
  			return new ErrorObj(ERR_CHEQUE_USE, $param);
  		}
  	}
  	//Fin verifier que le cheque n'est pas utilisé
  } else {
  	$result->param[$msg_numCheque]=$a_num_cheque;
  	return new ErrorObj(ERR_NO_CHEQUE, $result->param);
  }
 return  new ErrorObj(NO_ERR, $chequier['id_chequier']);
}

/**
 * Renvoie les informations d'un chèque dont l'idenfiant est  passé en paramètre.
 *
 * @author Arès
 * @since 3.4
 * @param int $a_num_cheque L'identifiant du cheque
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant les informations du chéque .
 */
function getCheque($a_num_cheque) {
  global $global_id_agence;
  return(executeDirectQuery("SELECT * from ad_cheque WHERE id_ag = $global_id_agence AND id_cheque =$a_num_cheque  "));
}

/**
 * Ajout d'un cheque encaissé, volé ou perdu.
 *
 * @author Arès
 * @since 3.4
 * @param array a_data tableau contenant les informations du cheque à ajouter
 * @return ErrorObj Un ErrorObj.
 */
function insertCheque($a_data,$id_cpte = NULL) {
  global $global_id_agence;
  $a_data["id_ag"]=$global_id_agence;
  $result = valideCheque($a_data['id_cheque'],$id_cpte);
  if ($result->errCode != NO_ERR) {
  	return $result;
  }
    $a_data['id_chequier']=$result->param;
  
  $sql=buildInsertQuery("ad_cheque",$a_data);
  return(executeDirectQuery( $sql, TRUE));
}
/**
 * Mise en opposition d'un chéquier ou  d'un chèque .
 *
 * @author Arès
 * @since 3.4
 * @param array a_data tableau contenant les informations du cheque à ajouter
 * @return ErrorObj Un ErrorObj.
 */
function opposeChequier($opposition,$a_data,&$comptable) {
  global $global_id_agence;
  global $adsys;
  $a_data["id_ag"]=$global_id_agence;
  if ( $opposition == 1 ) { // misee  en opposition d'un chèque
    //verifier que le cheque n'est pas utilisé
  	$result=getCheque( $a_data['id_cheque']);
  	if($result->errCode!= NO_ERR) {
  		return $result;
  	} elseif (count($result->param)>0) {
  		$param=array();
  		$param=$result->param[0];
  		if ($param['is_opposition'] == 't')
  			$msg=sprintf(_("Le chèque est déjà mise en opposition "));
  		else
  			$msg=sprintf(_("Etat du chèque:%s "),$adsys["adsys_etat_cheque"][$param['etat_cheque']]);
  		return new ErrorObj(ERR_CHEQUE_USE, $msg);
  	}

    // Vérifier si c'est un chèque certifié
    $isChequecertifie = ChequeCertifie::isChequeCertifie($a_data['id_cheque'], ChequeCertifie::ETAT_CHEQUE_CERTIFIE_NON_TRAITE);
    $num_cpte_client = $_POST['num_cpte'];

    //Si chèque certifié procéder a l'opposition du chèque certifié avec les mouvements concernés
    if($isChequecertifie)
    {
      $myErr = ChequeCertifie::oppositionChequeCertifie($a_data['id_cheque'],$num_cpte_client,$comptable);

      if ($myErr->errCode != NO_ERR)
      {
        return $myErr;
      }
    }

  	$a_data['id_ag']=$global_id_agence;
  	$a_data['is_opposition']=true;
  	$a_data['date_opposition']=date ('d/m/Y');
  	
  	// mettre en opposition
  	$rep=insertCheque($a_data);
  	return $rep;
  	
  } elseif ( $opposition == 2 ) { // mise en opposition d'un chequier
  $a_data["date_statut"] = date("d/m/Y");
  $a_data["etat_chequier"] = 5; // mise en opposition
  $a_data["statut"] = 0; //rendre le chéquier inactif
  $id_chequier=$a_data['id_chequier'];
  $sql = buildUpdateQuery("ad_chequier", $a_data, array("id_chequier"=>$id_chequier,'id_ag'=>$global_id_agence));
  return(executeDirectQuery($sql));
  }
  
}
/**
 * Ajout d'une commande de chequier 
 *
 * @author Arès
 * @since 3.4
 * @param array a_data tableau contenant les informations de la commande du chequier à ajouter
 * @return ErrorObj Un ErrorObj.
 */
function insertCommandeChequier($a_data) {
  global $global_id_agence;
  $a_data["id_ag"]=$global_id_agence;
  $sql=buildInsertQuery("ad_commande_chequier",$a_data);
  return(executeDirectQuery( $sql, TRUE));
}

/**
 * Mise à jour  d'une commande de chequier 
 *
 * @author Arès
 * @since 3.4
 * @param array a_data tableau contenant les informations de la commande du chequier à mettre à jour
 * @param integer $id_cde_chequier l'identifiant de la commande
 * @return ErrorObj Un ErrorObj.
 */
function updateCommandeChequier($a_data,$id_cde_chequier) {
  global $global_id_agence;
  $where["id_ag"]=$global_id_agence;
  $where["id"]=$id_cde_chequier;
  $sql=buildUpdateQuery("ad_commande_chequier",$a_data,$where);
  return(executeDirectQuery( $sql, TRUE));
}
/**
 * liste des commandes de chequier 
 *
 * @author Arès
 * @since 3.4
 * @param integer $id_cde_chequier l'identifiant de la commande
 * @param integer $id_cpte  l'identifiant du compte d'épargne
 * @return ErrorObj Un ErrorObj.
 */
function getCommandeChequier($id_cde_chequier = NULL ,$id_cpte = NULL,$etat = NULL) {
  global $global_id_agence;
  $where["id_ag"]=$global_id_agence;
  if ( !is_null($id_cde_chequier)) {
  	$where["id"]=$id_cde_chequier;
  }
  if ( !is_null($id_cpte)) {
  	$where["id_cpte"]=$id_cpte;
  }
  if ( !is_null($etat)) {
  	$where["etat"]=$etat;
  }
  
  $sql=buildSelectQuery("ad_commande_chequier",$where);
  return(executeDirectQuery( $sql));
}
/**
 * Mettre les comandes à l'etat En attente d'impréssion
 * @param array $data tableau contenant les identifiant des commande  des chéquiers à mette en attente d'impression
 */
function setAttenteImpressionChequier($data) {
	global $global_id_agence;
	foreach( $data as $id_commande_chequier ) {
		$_data['date_envoi_impr'] = date('d/m/Y');
		$_data['etat'] = 2;
		$err = updateCommandeChequier($_data,$id_commande_chequier);
		if ($err->errCode != NO_ERR) {
			return $err;
		}
	}
	return new ErrorObj(NO_ERR);
}
/**
 * Mettre les comandes à l'etat En attente d'impréssion
 * @param integer $id_cmde_chequier  identifiant de la commande  du chéquier à mettre à l'etat imprimer
 */
function setImpressionChequier($id_cmde_chequier) {
	global $global_id_agence;
    $cmdeupadate ['date_impr'] = date('d/m/Y');
    $cmdeupadate ['etat'] = 3;
    $err1 = updateCommandeChequier($cmdeupadate,$id_cmde_chequier);
	if ($err1->errCode != NO_ERR) {
		return $err1;
	}
	return new ErrorObj(NO_ERR);
}
/**
 * liste des commandes de chequier en attente d'impression
 *
 * @author Arès
 * @since 3.4
 * @param integer $id_cpte  l'identifiant du compte d'épargne
 * @return ErrorObj Un ErrorObj.
 */
function getAttenteImpressionChequier($id_cpte = NULL) {
  global $global_id_agence;
  $where["id_ag"]=$global_id_agence;
  $where["etat"] = 2 ;
  if ( !is_null($id_cpte)) {
  	$where["id_cpte"]=$id_cpte;
  }
  
  $sql=buildSelectQuery("ad_commande_chequier",$where);
  return(executeDirectQuery( $sql));
}

/**
 *  
 * @author b&d
 * 
 * Renseigne le champ num_cpte_comptable pour le compte dans ad_cpt
 * Ce champ garde le compte comptable qui est associé au compte interne.
 * Le compte comptable differe selon le id_prod. voir cas dans le code.
 * 
 * 
 * trac #357 - équilibre inventaire - comptabilité
 * 
 * @param int $id_cpte
 * @param $db
 * @return void|boolean|ErrorObj
 */
function setNumCpteComptableForCompte($id_cpte, &$db)
{	
	global $error, $global_id_agence, $dbHandler;
	
	$num_cpte_comptable = NULL;	
		
	// validation
	if (empty($id_cpte)) {
		return false; // le compte interne n'est pas defini, on ne fait rien		
	}
	
	//verification de l'existance du compte interne:
	$sql = "SELECT count(*) FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = '$id_cpte'";
	$result = $db->query ($sql);
	
	if (DB::isError ($result)) {
		$dbHandler->closeConnection (false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
	}	
	$count = $result->fetchrow();	

	if ($count == 0) {		
		return false; // le compte interne n'est pas defini en base, on ne fait rien
	}	
	
	// recupere le id_prod l'etat compte et la devise du compte
	$sql = "SELECT id_prod, etat_cpte, devise FROM ad_cpt c WHERE c.id_ag = $global_id_agence AND c.id_cpte = '$id_cpte'";
	$result = $db->query ($sql);

	if (DB::isError ($result)) {
		$dbHandler->closeConnection (false);						
		signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
	}		
	
	$row = $result->fetchrow(DB_FETCHMODE_ASSOC);	
	$id_prod = $row['id_prod'];
	$etat_cpte = $row['etat_cpte'];
	$devise = $row['devise'];	

	// If id_prod defined
	if(!empty($id_prod))
	{		
		if($etat_cpte == 2) { // compte fermée, set num_cpte_comptable a NULL		
			$num_cpte_comptable = NULL;
		}		
		else if($id_prod == 1 || $id_prod > 5) // Depot à vue ou dépot / compte à terme :
		{
			// compte à l'état dormant (etat_cpte=4) qui sont déclassés sur le compte comptable de l'operation 170
			if($etat_cpte == 4) {
				$sql = "SELECT num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = 170 AND sens = 'c' AND id_ag = $global_id_agence;";								
				$result = $db->query ($sql);				
				if (DB::isError ($result)) {
					$dbHandler->closeConnection (false);						
					signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
				}				
				$row = $result->fetchrow(DB_FETCHMODE_ASSOC);				
				$num_cpte_comptable = $row['num_cpte'];
			}
			else { // Depot à vue ou dépot / compte à terme OUVERTS				
				$sql = "SELECT cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE id = $id_prod AND id_ag = $global_id_agence AND devise = '$devise';";
								
				$result = $db->query ($sql);				
				if (DB::isError ($result)) {
					$dbHandler->closeConnection (false);						
					signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
				}				
				$row = $result->fetchrow(DB_FETCHMODE_ASSOC);				
				$num_cpte_comptable = $row['cpte_cpta_prod_ep'];
			}
		}
		elseif($id_prod == 3) // comptes de crédit
		{
		    $sql = "SELECT etat_cpte.num_cpte_comptable
					FROM adsys_etat_credit_cptes etat_cpte, ad_dcr doss 
					WHERE doss.cre_id_cpte = $id_cpte
					AND etat_cpte.id_prod_cre = doss.id_prod 
					AND etat_cpte.id_etat_credit = doss.cre_etat
					AND etat_cpte.id_ag = $global_id_agence
					AND doss.id_ag = $global_id_agence
		    		AND doss.cre_etat IS NOT NULL;"; // Les fonds sont deboursés
		    		    
		    $result = $db->query ($sql);		    
		    if (DB::isError ($result)) {
		    	$dbHandler->closeConnection (false);
		    	signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
		    }		    
		    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);	
		    
		    if (empty($row)) {
		    	return false;
		    }
		    
		    $num_cpte_comptable = $row['num_cpte_comptable'];
		}
		elseif($id_prod == 4) // comptes de garantie
		{
			$sql = "SELECT prod.cpte_cpta_prod_cr_gar 
					FROM adsys_produit_credit prod, ad_dcr doss, ad_gar gar
					WHERE gar.gar_num_id_cpte_nantie = $id_cpte
					AND gar.type_gar = 1
					AND gar.id_doss = doss.id_doss
					AND doss.id_prod = prod.id
					AND prod.id_ag = $global_id_agence
					AND doss.id_ag = $global_id_agence
					AND gar.id_ag = $global_id_agence
					AND prod.cpte_cpta_prod_cr_gar IS NOT NULL 
					AND doss.cre_etat IS NOT NULL;"; // Les fonds sont deboursés			
			
			$result = $db->query ($sql);			
			if (DB::isError ($result)) {
				$dbHandler->closeConnection (false);
				signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
			}
			$row = $result->fetchrow(DB_FETCHMODE_ASSOC);	
			
			if (empty($row)) {				
				return ; //le compte comptable n'est pas defini, on ne met pas a jour num_cpte_comptable
			}			
			$num_cpte_comptable = $row['cpte_cpta_prod_cr_gar'];			
		}
	}

	// Update the num_cpte_comptable column in ad_cpt

	if(empty($num_cpte_comptable)) {
		$sql = "UPDATE ad_cpt SET num_cpte_comptable = NULL WHERE id_cpte = $id_cpte;";
	}
	else {
		$sql = "UPDATE ad_cpt SET num_cpte_comptable = '$num_cpte_comptable' WHERE id_cpte = $id_cpte;";
	}
		
	$result = $db->query ($sql);	
	if (DB::isError ($result)) {
		$dbHandler->closeConnection (false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
	}
	
	return new ErrorObj(NO_ERR, $num_cpte_comptable);
}

/**
 *  
 * @author b&d
 * @param string $compte_comptable
 * @param string $id_cpte
 * @param string $id_his
 * @param db $db
 * @return ErrorObj
 * 
 * Trac #357 - équilibre inventaire - comptabilité
 * 
 * Fonction qui verifie et logue des écarts d'inventaire/comptablité
 * 
 */
function verificationEquilibreComptable($compte_comptable = NULL, $id_cpte = NULL, $id_his = NULL, &$db)
{
	global $dbHandler, $error;
	global $global_id_agence, $global_nom_login;	
	
	// Le compte interne est renseigné, il faut recuperer le compte comptable a partir du champ num_cpte_comptable
	if(!empty($id_cpte)) {
		$sql = "SELECT num_cpte_comptable FROM ad_cpt WHERE id_cpte = $id_cpte;";		
		$result = $db->query ($sql);		
		if (DB::isError ($result)) {
			$dbHandler->closeConnection (false);
			signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
		}
		$row = $result->fetchrow(DB_FETCHMODE_ASSOC);
		$compte_comptable = $row['num_cpte_comptable'];		
	}
	
	// Verification si on a bien ce compte comptable renseigné pour ce compte interne	
	$count_compte = 0;
	
	if(!empty($compte_comptable)) {
		$sql = "SELECT COUNT(*) FROM ad_cpt WHERE num_cpte_comptable = '$compte_comptable' and etat_cpte != 4;";		
		$result = $db->query ($sql);
		if (DB::isError ($result)) {
			$dbHandler->closeConnection (false);
			signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
		}
		$row = $result->fetchrow();		
		$count_compte = $row[0];
	}	
	
	// Ne rien faire si le compte n'est pas renseigne dans num_cpte_comptable dans ad_cpt
	if(empty($compte_comptable) || $count_compte < 1) { 		
		//return 0; // ?
	}
	else 
	{		
		if(empty($id_cpte)) $id_cpte = 'NULL';	
		if(empty($id_his)) $id_his = 'NULL';				
		$sql = "SELECT verification_equilibre_comptable($id_cpte, '$compte_comptable', '$global_nom_login', $id_his, $global_id_agence);";
		$result = $db->query ($sql);
		if (DB::isError ($result)) {
			$dbHandler->closeConnection (false);
			signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
		}
		$row = $result->fetchrow();
		$hasEcart = $row[0];
			
		return new ErrorObj(NO_ERR, $hasEcart);		
	}	
}

function getCompteData($id_client,$id_prod = null) {
  /* Renvoie un tableau associatif avec toutes les données du compte dont l'id_titulaire est $id_client
     Valeurs de retour :
     Le tableau si OK
     NULL si le client n'existe pas
     Die si erreur de la base de données
  */
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_cpt WHERE id_ag=$global_id_agence AND id_titulaire = '$id_client' ";
  if ($id_prod != null){
    $sql .=" AND id_prod = $id_prod";
  }
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  // FIXME ** TF - 27/09/2002 -- Ce champs temporaire ne doit pas être visible par les modules
  unset ($DATAS["tmp_already_accessed"]);
  return $DATAS;
}

/**
 * Retourne le statut juridique du client sous forme text.
 * @author Djibril NIANG
 * @param int $id_client L'identifiant du client.
 * @return string Le libellé du statut juridique du client.
 */
function getDateFinMois($date)
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM getfinmois(date('$date'));";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $rows = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $rows[0];
}

/**
 * Renvoie les infos sur tous les comptes client <B>ouverts</B> pour un client donné pour infomations financiers client
 * @param $id_client int Numéro du client
 * @return Array Un Array indicé par le numéro du compte avec pour chaque compte una rray associatif avec toutes les infos
 */
function getAccountsInfoFinancier ($id_client) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT b.*, a.* FROM adsys_produit_epargne b, ad_cpt a WHERE a.id_ag = $global_id_agence AND a.id_ag = b.id_ag AND a.id_prod = b.id AND ";
  $sql .= "a.id_titulaire = '$id_client'";
  $sql .= " AND NOT (a.etat_cpte = 2) ORDER BY a.num_complet_cpte";  // AND NOT (a.etat_cpte = 2) il se peut qu'on veuille avoir les comptes bloqués
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) return NULL;
  $TMPARRAY = array();
  while ($cpt = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $TMPARRAY[$cpt["id_cpte"]] = $cpt;
  }
  return $TMPARRAY;
}

//Ticket Jira MB-153
function getTypeOperation($cat_ope = nulll){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT o.type_operation,t.traduction FROM ad_cpt_ope o INNER JOIN ad_traductions t on t.id_str = o.libel_ope where o.id_ag = $global_id_agence ";
  if ($cat_ope != null) {
    $sql .= " and o.categorie_ope = $cat_ope";
  }
  $sql .= " and o.type_operation not in (SELECT type_opt from adsys_param_mouvement where deleted = 'f')";

  $sql .= "order by type_operation asc";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) return NULL;
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["type_operation"]]=$row["type_operation"];

  return $DATAS;
}

//Ticket Jira MB-153
function getTypeOperationAll($cat_ope = null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT o.type_operation,t.traduction FROM ad_cpt_ope o INNER JOIN ad_traductions t on t.id_str = o.libel_ope where o.id_ag = $global_id_agence ";
  if ($cat_ope != null) {
    $sql .= " and o.categorie_ope = $cat_ope";
  }
  $sql .= "order by type_operation asc";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) return NULL;
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$row["type_operation"]] = $row["traduction"];
  }
  return $DATAS;
}

/**
 * Fonction pour verifier si un montant est des decimaux ex. 100.15 ...
 * PARAM : mnt
 * RETURN : BOOLEAN $hasDecimal
 */
function hasDecimalMontant($mnt)
{
  $hasDecimal = false;

  $mntIAP_Arrondie = ROUND($mnt);
  $diff = abs($mnt - $mntIAP_Arrondie);
  if ($diff > 0){
    $hasDecimal = true;
  }

  return $hasDecimal;

}

/**
 * Retourner le solde du compte client
 * @param $id_cpte
 * @return mixed
 */
function getSoldeCpte($id_cpte)
{
    global $dbHandler;
    global $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT solde ";
    $sql .= "FROM ad_cpt ";
    $sql .= "WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte ";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $row = $result->fetchrow();

    $solde = $row[0];

    $dbHandler->closeConnection(true);
    return $solde;
}
function getRembAccountID ($id_client, $id_doss) {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();
    $sql = "SELECT cpt_remb FROM ad_dcr WHERE id_ag = $global_id_agence AND id_client = $id_client AND id_doss = $id_doss";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0)
        return NULL;
    $tmpRow = $result->fetchrow();
    return $tmpRow[0];
}

function getListChequiersPrintParNiveau($niv_agence = null,$niv_guichet=null) {
    global $global_id_agence;
    $sql="SELECT b.*,num_complet_cpte,id_titulaire,c.n_agence,c.n_guichet FROM ad_cpt a, ad_commande_chequier b,ad_cli c  WHERE a.id_ag=b.id_ag AND  a.id_ag = $global_id_agence AND a.id_cpte=b.id_cpte AND b.etat = 1 AND a.id_titulaire = c.id_client";
    if ($niv_agence != null){
        $sql .= " AND c.n_agence = $niv_agence";
    }
    if ($niv_guichet != null){
        $sql .= " AND n_guichet = $niv_guichet";
    }
    return(executeDirectQuery($sql));
}
?>
