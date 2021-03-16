<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2: */
/**
 * Procédures stockées relatives aux clients
 * @author Thomas Fastenakel
 * @since 10/12/2001
 * @package Clients
 **/

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/handleDB.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/epargne.php';

/**
 * Teste si la fiche d'un client a déjà été consultée
 * @param int $id_client L'identifiant du client
 * @return bool Vrai si le client a déjà été accédé par le système, faux sinon.
 */
function isAlreadyAccessed($id_client)
{
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT tmp_already_accessed FROM ad_cli WHERE id_ag=$global_id_agence AND id_client = '$id_client';";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  if ($result->numRows() == 0)
    return NULL;
  $tmprow = $result->fetchrow();
  $dbHandler->closeConnection(true);
  if ($tmprow[0] == 't')
    return true;
  else
    return false;
}

function markClientAccessed($id_client)
// FIXME - Fonction temporaire qui marque le client comme déjà accédé
{
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "UPDATE ad_cli SET tmp_already_accessed = 't' WHERE id_ag=$global_id_agence AND id_client = '$id_client';";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return true;
}

function client_exist($num_client) { //Renvoie true si le client existe
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT count(*) FROM ad_cli WHERE id_ag=$global_id_agence AND id_client = '$num_client'";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return ($row[0] == 1);
}

/**
 * Fonction qui renvoie le libellé de la localisation connaissant son id.
 *
 * @param Integer $id : identifiant la localisation
 * @return String : libellé de la localisation
 */
function getLocalisation($id) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT libel FROM adsys_localisation where id_ag=$global_id_agence AND id=$id ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row_libel = $result->fetchrow();
  return $row_libel[0];
}

/**
 * Fonction qui renvoie toutes les localisations
 * Les loc filles d'autres constituent un sous array de leur mère.
 * @param void
 * @return array Tableau associatif des localisation
 */
function getLocArray() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM adsys_localisation where id_ag=$global_id_agence ";
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

/**
 * Fonction qui renvoie toutes les localisations
 * Les loc filles d'autres constituent un sous array de leur mère.
 * @param void
 * @return array Tableau associatif des localisation
 */
function getLocRwandaArray() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id, libelle_localisation, type_localisation, parent  FROM adsys_localisation_rwanda where id_ag=$global_id_agence ";
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

/**
 * Fonction qui renvoie toutes les categorie employes
 * Les loc filles d'autres constituent un sous array de leur mère.
 * @param void
 * @return array Tableau associatif des categories employes
 */
function getCatEmpArray() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM adsys_categorie_emp where id_ag=$global_id_agence ";
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

/**
 * PS qui insère une nouvelle localisation dans la table adsys_localisation
 *
 * @param String $libel : Le libellé de la localisation
 * @param Integer $parent : L'ID du parent si niveau 2 (optionnel)
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function insertLocation($libel, $parent = NULL) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $insert = array (
              "libel" => $libel
            );
  if ($parent != NULL)
    $insert['parent'] = $parent;
  $insert['id_ag'] = $global_id_agence;
  $sql = buildInsertQuery("adsys_localisation", $insert);

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * PS qui insère une nouvelle categorie dans la table adsys_categorie employe
 *
 * @param String $libel : Le libellé de la la categorie
 * @param Integer $parent : L'ID du parent si niveau 2 (optionnel)
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function insertCategorieEmp($libel,$code, $parent = NULL) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $insert = array (
    "libel" => $libel,
    "code" => $code
  );
  if ($parent != NULL)
    $insert['parent'] = $parent;
  $insert['id_ag'] = $global_id_agence;
  $sql = buildInsertQuery("adsys_categorie_emp", $insert);

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}


/**
 * PS qui met à jour le libellé d'une localisation de la table adsys_localisation
 *
 * @param integer $id : L'ID de la localisation à modifier
 * @param String $libel : Le nouveau libellé
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function updateLocation($id, $libel) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $insert = array (
              "libel" => $libel
            );
  $where = array (
             "id" => $id,'id_ag'=> $global_id_agence
           );
  $sql = buildUpdateQuery("adsys_localisation", $insert, $where);

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * PS qui met à jour le libellé d'une categorie emp de la table adsys_categorie_emp
 *
 * @param integer $id : L'ID de la categorie à modifier
 * @param String $libel : Le nouveau libellé
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function updateCategorieEmp($id, $libel,$code) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $insert = array (
    "libel" => $libel,
    "code" => $code
  );
  $where = array (
    "id" => $id,'id_ag'=> $global_id_agence
  );
  $sql = buildUpdateQuery("adsys_categorie_emp", $insert, $where);

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * PS qui effectue la suppression d'une localisation de la table adsys_localisation
 *
 * @param Integer $id : est l'ID de la localisation à supprimer
 * @return ErrorObj
 * 							- NO_ERR si localisation supprimée
 * 							- ERR_LOC_EXIST_CHILD s'il y a des fils de cette localisation
 * 				      - ERR_LOC_EXIST_CLIENT si des clients sont répertoriés sous cette localisation
 */
function deleteLocation($id) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  // Tests
  $locArray = getLocArray();
  $err = NO_ERR;
  while ((list (, $value) = each($locArray)) && ($err == NO_ERR)) {
    // Si il existe des fils
    if ($value['parent'] == $id)
      $err = ERR_LOC_EXIST_CHILD;
  }
  // Si il existe des clients
  $sql = "SELECT id_client FROM ad_cli WHERE id_ag=$global_id_agence AND id_loc1 = $id OR id_loc2 = $id;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  if ($result->numRows() > 0)
    $err = ERR_LOC_EXIST_CLIENT;
  if ($err == NO_ERR) {
    $sql = buildDeleteQuery("adsys_localisation", array (
                              "id" => $id,"id_ag"=>$global_id_agence
                            ));
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj($err);
}


/**
 * PS qui effectue la suppression d'une categorieemploye de la table adsys_categorie_emp
 *
 * @param Integer $id : est l'ID de la categorie à supprimer
 * @return ErrorObj
 * 							- NO_ERR si categorie supprimée
 * 							- ERR_LOC_EXIST_CHILD s'il y a des fils de cette categorie
 * 				      - ERR_LOC_EXIST_CLIENT si des clients sont répertoriés sous cette categorie
 */
function deleteCategorieEmp($id) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  // Tests
  $locArray = getCatEmpArray();
  $err = NO_ERR;
  while ((list (, $value) = each($locArray)) && ($err == NO_ERR)) {
    // Si il existe des fils
    if ($value['parent'] == $id)
      $err = ERR_LOC_EXIST_CHILD;
  }
  // Si il existe des clients
  $sql = "SELECT id_client FROM ad_cli WHERE id_ag=$global_id_agence AND id_loc1 = $id OR id_loc2 = $id;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  if ($result->numRows() > 0)
    $err = ERR_LOC_EXIST_CLIENT;
  if ($err == NO_ERR) {
    $sql = buildDeleteQuery("adsys_localisation", array (
      "id" => $id,"id_ag"=>$global_id_agence
    ));
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj($err);
}

/**
 * Fonction renvoyant les informations des relations ayant-droit d'un client
 * @author Unknown
 * @since 1.0
 * @param int $id_client l'identifiant du client pour lequel on cherche les ayant-droits
 * @return array Tableau des information des relations ayant-droit si le client en possède sinon un tableau vide
 */
function existeAyantDroit($id_client) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  /* Récupération des relations ayant-droit */
  $AyantDroit = array ();
  $sql = "SELECT * FROM ad_rel WHERE id_ag=$global_id_agence AND id_client=$id_client AND typ_rel=3 AND valide='t';";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $AyantDroit[$row["id_rel"]] = $row;

  $dbHandler->closeConnection(true);
  return $AyantDroit;

}

/**
 * Insère un nouveau client dans la base de données
 * Créer le compte de base pour le client
 * @author Thomas Fastenakel
 * @param Array $CLI_DATA Données à insérer dans la table ad_cli (divisé en "TEXT" pour les champs textuels et "IMAGES" pour les champs image
 * @param Array $CPT_DATA Données concernant le compte de base
 * @return ErrorObj Objet Erreur avec l'ID du compte de base en paramètre
 */
function insere_client($CLI_DATA, $CPT_DATA, $ouvre_cpt_base) {
  global $db;
  global $dbHandler;
  global $global_id_agence;
  global $global_monnaie;

  $db = $dbHandler->openConnection();

  $CLI_DATA["tmp_already_accessed"] = "t";

  // Retrait des images de $CLI_DATAS
  $IMAGES = array (
              "photo" => $CLI_DATA["photo"],
              "signature" => $CLI_DATA["signature"]
            );
  unset ($CLI_DATA["photo"]);
  unset ($CLI_DATA["signature"]);
  
   //Récuperer les champs supplémentaire
   if( isset($CLI_DATA["champsExtras"]) ) {
  		$ChampsExtras = $CLI_DATA["champsExtras"];
    	unset ($CLI_DATA["champsExtras"]);   
   }
  $CLI_DATA['id_ag']= $global_id_agence;
  // Construction de la requete INSERT pour les champs texte
  $sql = buildInsertQuery("ad_cli", $CLI_DATA);
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  //insertion des champs supplémentaire
  if(is_array($ChampsExtras) and count($ChampsExtras) >0) {
  	$myErr = inseresClientChampsExtras($ChampsExtras,$CLI_DATA["id_client"]);
  	if ($myErr->errCode != NO_ERR) {
  		$dbHandler->closeConnection(false);
  		signalErreur(__FILE__, __LINE__, __FUNCTION__);
  	}
  }
  // Insertion d'image
  $PATHS = imageLocationClient($CLI_DATA["id_client"]);
  foreach ($IMAGES as $imagename => $imagepath) {
    $source = $IMAGES[$imagename];

    if ($imagename == 'photo')
      $destination = $PATHS["photo_chemin_local"];
    else
      if ($imagename == 'signature')
        $destination = $PATHS["signature_chemin_local"];

    if (($source == NULL) or ($source == "") or ($source == "/adbanking/images/travaux.gif"))
      exec("rm -f ".escapeshellarg($destination));
    else {
      rename($source, $destination);
      chmod($destination, 0777);
    }
  }

  // Insertion de la personne extérieure du client
  $sql = buildInsertQuery('ad_pers_ext', array (
                            'id_client' => $CLI_DATA['id_client'],'id_ag'=>$CLI_DATA['id_ag']
                          ));

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  // Construction du numéro de compte du client
  $rang = '00';
  $NumCompletCompte = makeNumCpte($CLI_DATA["id_client"], $rang);

  // Rapatriement du n° de produit du compte de base
  $id_prod = getBaseProductID($global_id_agence);
  $PROD = getProdEpargne($id_prod);

  // Construction de ACCOUNT, tableau associatif contenant les données sur le compte de base du client.
  $ACCOUNT = array ();
  $ACCOUNT["id_cpte"] = getNewAccountID();
  $ACCOUNT["id_titulaire"] = $CLI_DATA["id_client"];
  $ACCOUNT["date_ouvert"] = date("d/m/Y");
  $ACCOUNT["utilis_crea"] = $CLI_DATA["utilis_crea"];
  if ($ouvre_cpt_base == 1) {
    $ACCOUNT["etat_cpte"] = 1;
  } else {
    $ACCOUNT["etat_cpte"] = 3; // Le compte est bloqué si on ne veut pas l'utiliser
  }
  $ACCOUNT["solde"] = '0';
  $ACCOUNT["mnt_bloq"] = '0';
  $ACCOUNT["mnt_bloq_cre"] = '0';
  $ACCOUNT["num_cpte"] = '0'; // C'est le premier compte du client
  $ACCOUNT["num_complet_cpte"] = $NumCompletCompte;

  // Get chosen produit epargne
  if(null !== $CPT_DATA["id_prod_epg"] && trim($CPT_DATA["id_prod_epg"]) > 0) {
      $ACCOUNT["id_prod"] = trim($CPT_DATA["id_prod_epg"]);
  } else {
      $ACCOUNT["id_prod"] = $id_prod;
  }
  
  // Recup les details du produit epargne
  $PROD = getProdEpargne($ACCOUNT["id_prod"]);

  $ACCOUNT["intitule_compte"] = $CPT_DATA["intitule_compte"]; // Intitulé du compte
  $ACCOUNT["devise"] = $global_monnaie;
  $ACCOUNT["type_cpt_vers_int"] = 1; // Les intérets sont versés sur le compte lui-meme
  //  infos héritées du produit
  $ACCOUNT["tx_interet_cpte"] = $PROD["tx_interet"];
  $ACCOUNT["terme_cpte"] = $PROD["terme"];
  $ACCOUNT["mode_calcul_int_cpte"] = $PROD["mode_calcul_int"];
  $ACCOUNT["freq_calcul_int_cpte"] = $PROD["freq_calcul_int"];
  $ACCOUNT["mode_paiement_cpte"] = $PROD["mode_paiement"];
  $ACCOUNT["decouvert_max"] = $PROD["decouvert_max"];
  $ACCOUNT["mnt_min_cpte"] = $PROD["mnt_min"];

  if (!creationCompte($ACCOUNT)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Création du compte de base a échoué"
  }
  $sql = "UPDATE ad_cli SET id_cpte_base = " . $ACCOUNT["id_cpte"] . " WHERE id_ag=$global_id_agence AND id_client = " . $CLI_DATA["id_client"];
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $ACCOUNT["id_cpte"]);
}

/**
 * Recupère les numéro de pièce d'identité pour un type de pièce donnée
 * @author Djibril NIANG
 * @since 3.0
 * @param int $type_piece type de pièce d'identité
 * @return Array $Num_piece : un tableau contenant la liste de tous les numéros de pièce associés à ce type de pièce
 */
function getNumPieceId($type_piece) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
	$sql = "SELECT pp_nm_piece_id from ad_cli";
	$sql .= " WHERE id_ag = $global_id_agence AND pp_type_piece_id = $type_piece";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
	   $dbHandler->closeConnection(true);
	   signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	if ($result->numRows() == 0)
    return NULL;

	$Num_piece = array ();
	while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)){
		array_push($Num_piece, $tmprow);
	}
	$dbHandler->closeConnection(true);
	return $Num_piece;
}

/**
 * Recupère le numéro du client pour un type et numéro de piéce donné
 * @author Djibril NIANG
 * @since 3.0
 * @param text $num_piece type de pièce d'identité
 * @param int $type_piece numéro de pièce d'identité
 * @return Array $Num_client contenant le numéro du client
 */
function getNumPieceIdClient($type_piece, $num_piece) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
	$sql = "SELECT id_client from ad_cli";
	$sql .= " WHERE id_ag = $global_id_agence AND pp_type_piece_id = ".$type_piece." AND pp_nm_piece_id = '".$num_piece."'";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
	   $dbHandler->closeConnection(true);
	   signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	if ($result->numRows() == 0)
    return NULL;

	$Num_client = array ();
	while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)){
		array_push($Num_client, $tmprow);
		//$Num_client = $tmprow[0];
	}
	$dbHandler->closeConnection(true);
	return $Num_client;
}

/**
 * Recupère les groupes solidaires auxquels le membre est inscrit
 * @author Saourou MBODJ
 * @since 2.7
 * @param int $id_membre ID du client
 * @return ErrorObj Objet Erreur oubien un tableau contenant la liste des groupes solidaires
 */
function getGroupSol($id_membre, $id_group=null) {
  global $global_id_agence;
  $requete = "SELECT * from ad_grp_sol where id_ag=$global_id_agence AND id_membre='$id_membre' ";
  if(isset($id_group))
    $requete .= " AND id_grp_sol != '$id_group' ";
  $resultat = executeDirectQuery($requete);
  return $resultat;

}

/**
 * Recupère un groupe solidaire
 * @author Saourou MBODJ
 * @since 2.7
 * @param int $id_group ID du groupe solidaire
 * @return ErrorObj Objet Erreur oubien un tableau contenant un enregistrement du groupe solidaire
 */
function getNomGroup($id_group) {
  global $global_id_agence;
  $requete = "SELECT * from ad_cli where id_ag=$global_id_agence AND id_client='$id_group'";
  $resultat = executeDirectQuery($requete);
  return $resultat;

}

/**
 * Met à jour les données concernant un client dans la table des clients (ad_cli)
 * @author thomas FASTENAKEL
 * @since 1.0
 * @param int $id_client ID du client à modifier
 * @param Array $Fields Ensemble des champs à mettre à jour avec leur nouvelle valeur
 * @param Array $IMAGES Liste des images à mettre à jour (on transmet le nom et l'URL de l'image)
 * @param int $nbr_membres_gs Nombre de membres du groupe solidaire
 * @return ErrorObj Objet Erreur
 */
function updateClient($id_client, $Fields, $IMAGES = NULL, $nbr_membres_gs = 0) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  // Verification de l'existence des membres du groupe solidaire
  if ($nbr_membres_gs > 0) {
    // D'abord supprimer tous les membres du groupe pour nettoyer la base
    $result = executeQuery($db, buildDeleteQuery("ad_grp_sol", array (
                             "id_grp_sol" => $id_client,"id_ag"=>$global_id_agence
                           )));
    if ($result->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $result;
    }

    $membres_gs = array ();
    $nbr_membres_enregistres = 0;
    for ($i = 1; $i <= $nbr_membres_gs; ++ $i) {
      // Si nous créons un groupe solidaire, il faut vérifier l'existence des membres (num_client$i)
      $num_client = $Fields["num_client$i"];
      unset ($Fields["num_client$i"]);
      if ($num_client != "") {
        if (!client_exist($num_client)) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CLIENT_INEXISTANT, sprintf(_("Pour un des membres du groupe solidaire (id_client = %s)."), $num_client));
        }
        // et réintroduire les données nécessaire dans ad_grp_sol
        $membres_gs["id_grp_sol"] = $id_client;
        $membres_gs["id_membre"] = $num_client;
        $membres_gs["id_ag"] = $global_id_agence;
        $result = executeQuery($db, buildInsertQuery("ad_grp_sol", $membres_gs));
        if ($result->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $result;
        } else {
          $nbr_membres_enregistres++;
        }
      }
    }
    $Fields["gi_nbre_membr"] = $nbr_membres_enregistres;
  }

  //Récuperation des champs supplémentaire 
  if(isset($Fields['champsExtras'])) {
  	 $champsExtras = $Fields['champsExtras'];
  	 unset($Fields['champsExtras']);
  }
  // Insertion du client dans la table ad_cli, des relations et création du compte de base
  // Mise à jour des champs textuels
  $Where["id_client"] = $id_client;
  $Where["id_ag"] = $global_id_agence;
  $sql = buildUpdateQuery("ad_cli", $Fields, $Where);
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  //Mise à jour des champs supplémentaire
  if(count($champsExtras) > 0 ) {
  	$myError = updatesClientChampsExtras($champsExtras,$id_client);
  	if($myError->errCode != NO_ERR  ) {
  		$dbHandler->closeConnection(false);
    	return  $myError;
  	}
  }

  // Mise à jour des champs image
  if (sizeof($IMAGES) > 0) {
    $PATHS = imageLocationClient($id_client);
    foreach ($IMAGES as $imagename => $imagepath) {
        $source = $IMAGES[$imagename];

        if ($imagename == 'photo') {
          $destination_local = $PATHS["photo_chemin_local"];
          $path_meta = array("photo_path" => $PATHS["photo_chemin_web"]);
        }
        else if ($imagename == 'signature') {
           $destination_local = $PATHS["signature_chemin_local"];
            $path_meta = array("signature_path" =>$PATHS["signature_chemin_web"]);
        }

      if (($source == NULL) or ($source == "") or ($source == "/adbanking/images/travaux.gif"))
        exec("rm -f ".escapeshellarg($destination_local));
      else {
        if ($source != $PATHS[$imagename."_chemin_web"]) {
          file_add_timestamp(dirname($destination_local), "/".basename($destination_local)."/", $path_meta);
          rename($source, $destination_local);
          chmod($destination_local, 0777);
        }
      }
    }
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/**
 * Retourne le statut juridique du client.
 *
 * @param int $id_client L'identifiant du client.
 * @return string Le libellé du statut juridique du client.
 */
 function getStatutJuridique($id_client)
  {
   global $dbHandler;
   $db = $dbHandler->openConnection();
   $sql = "SELECT statut_juridique FROM ad_cli WHERE (id_client='$id_client')";
   $result=$db->query($sql);
   if (DB::isError($result))
     {
       $dbHandler->closeConnection(false);
       signalErreur(__FILE__,__LINE__,__FUNCTION__);
     }
   $rows = $result->fetchrow();
   $dbHandler->closeConnection(true);
   switch ($rows[0])
     {
     case 1:     // PP
       return "Personne physique ";
       case 2:     // PM
       return "Personne morale ";
       case 3:   // GI
       return "Groupe infomel ";
       case 4:  // GS
       return "Groupe solidaire ";
       default:
       return NULL;
     }
  }

/**
 * Retourne le statut juridique du client sous forme text.
 * @author Djibril NIANG
 * @param int $id_client L'identifiant du client.
 * @return string Le libellé du statut juridique du client.
*/
function getStatutJuridiqueClient($id_client)
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT statut_juridique FROM ad_cli WHERE (id_client='$id_client')";
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
 * Renvoie le nom complet d'un client personne physique
 * @author Djibril NIANG
 * @since 3.0.6
 * @param int $id_client identifiant du client
 * @return TEXT $nom : nom et prénom du client
**/
function getClientNamePP($id_client) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT pp_nom, pp_prenom FROM ad_cli WHERE id_client = $id_client AND id_ag = $global_id_agence ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
  }
  $ligne = $result->fetchrow();
  $nom = $ligne[0]." ".$ligne[1];

  $dbHandler->closeConnection(true);
  return $nom;
}

/**
 * Renvoie le nom complet d'un client personne morale
 * @author Djibril NIANG
 * @since 3.0.6
 * @param int $id_client identifiant du client
 * @return TEXT $nom : la raison sociale de la personne morale
**/
function getClientNamePM($id_client) {
 global $dbHandler,$global_id_agence;

 $db = $dbHandler->openConnection();
 $sql = "SELECT pm_raison_sociale FROM ad_cli WHERE id_client = $id_client AND id_ag = $global_id_agence ";
 $result = $db->query($sql);
 if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
 }
 $ligne = $result->fetchrow();
 $nom = $ligne[0];

 $dbHandler->closeConnection(true);
 return $nom;
}

/**
 * Renvoie le nom complet d'un client groupe informel
 * @author Djibril NIANG
 * @since 3.0.6
 * @param int $id_client identifiant du client
 * @return TEXT $nom : le nom du groupe informel
**/
function getClientNameGI($id_client) {
 global $dbHandler,$global_id_agence;

 $db = $dbHandler->openConnection();
 $sql = "SELECT gi_nom FROM ad_cli WHERE id_client = $id_client AND id_ag = $global_id_agence ";
 $result = $db->query($sql);
 if (DB :: isError($result)) {
   $dbHandler->closeConnection(false);
   signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
 }
 $ligne = $result->fetchrow();
 $nom = $ligne[0];

 $dbHandler->closeConnection(true);
 return $nom;
}

function getClientDatas($id_client) {
  /* Renvoie un tableau associatif avec toutes les données du client dont l'ID est $id_client
     Valeurs de retour :
     Le tableau si OK
     NULL si le client n'existe pas
     Die si erreur de la base de données
  */
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_cli WHERE id_ag=$global_id_agence AND id_client = '$id_client' ";
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
 * Retourne l'état du client (voir adsys_etat_client)
 *
 * @param int $id_client L'identifiant du client.
 * @return int L'état du client.
 */
function getEtatClient($id_client) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT etat FROM ad_cli WHERE id_ag=$global_id_agence AND id_client = '$id_client';";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $etat_client = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $etat_client[0];
}

/**
 * Retourne la qualité du client (voir adsys_qualite_client).
 *
 * @param int $id_client L'identifiant du client.
 * @return int La qualité du client.
 */
function getQualiteClient($id_client) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT qualite FROM ad_cli WHERE (id_ag=$global_id_agence) AND (id_client='$id_client')";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $rows = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $rows[0];
}

/**
 * Extrait la photo et le spécimen de signature du client et renvoie un tableau avec les URLs donnant accès à ces deux fihiers
 * REM: Dans le cas où le client est une PM ou un GI, on prend la photo et la signature du premier responsable trouvé dans la DB ayant le pouvoir de signature
 *
 * @author Thomas FASTENAKEL
 * @author Stefano AMEKOUDI
 * @since 2.8
 * @param int $id_client ID du client
 * @return Array Tableau avec "photo" => nom du fichier de la photo et "signature" => nom du fichier contenan tla signature
 */
function getImagesClient($id_client) {

  $CLI = getClientDatas($id_client);
  if ($CLI["statut_juridique"] == 1) {
    $imagepath = imageLocationClient($id_client);

    if (is_file($imagepath['photo_chemin_local']))
      $PICPATHS = $imagepath['photo_chemin_web'];
    else
      $PICPATHS = NULL;

    if (is_file($imagepath['signature_chemin_local']))
      $SIGNPATHS = $imagepath['signature_chemin_web'];
    else
      $SIGNPATHS = NULL;

    return array ("signature" => $SIGNPATHS, "photo" => $PICPATHS);
  }

  $CPTS = get_comptes_epargne($id_client);
  if (is_array($CPTS)) {
    foreach ($CPTS as $id_cpte => $CPT) {
      $MANDATS = getListeMandatairesActifs($id_cpte);
      if (is_array($MANDATS)) {
        foreach ($MANDATS as $id_mandat => $MANDAT) {
          if ($id_mandat != 'CONJ') {
            $INFOS_MANDAT = getInfosMandat($id_mandat);
            if ($INFOS_MANDAT['photo'] != NULL || $INFOS_MANDAT['signature'] != NULL) {
              $imagepath = imageLocationPersExt($INFOS_MANDAT["id_pers_ext"]);

              if (is_file($imagepath['photo_chemin_local']))
                $PICPATHS = $imagepath['photo_chemin_web'];
              else
                $PICPATHS = NULL;

              if (is_file($imagepath['signature_chemin_web']))
                $SIGNPATHS = $imagepath['signature_chemin_web'];
              else
                $SIGNPATHS = NULL;
              return array ("signature" => $SIGNPATHS["url"], "photo" => $PICPATHS["url"]);
            }
          }
        }
      }
    }
  }
  return array ("signature" => NULL, "photo" => NULL);
}

function getPathPhotoClient($id_client) {
  $imagepath = imageLocationClient($id_client);
  if (is_file($imagepath['photo_chemin_local'])) {
    return $imagepath['photo_chemin_local'];
  } else {
    return NULL;
  }
}

/** 
 * Fonction qui compte le nombre de clients renvoyés par la fonction getMatchedClients avec les mêmes paramètres
 */
function countMatchedClients($Where, $type) 
{  
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $WhereClause = "";
  if (is_array($Where)) {
    $Where=array_make_pgcompatible($Where);
  }  
  
  foreach($Where as $key => $value)
  {     
      if(!empty($value))
      {
          switch ($key)
          {
              case 'pp_date_naissance' :
                  $WhereClause .= " substring(to_char(pp_date_naissance, 'YYYY-MM-DD'),1,10) = '$value' AND"; // '$value'"." 00:00:00"." AND";
                  break;
              case 'anc_id_client' :   
                  $WhereClause .= " anc_id_client = '$value' AND";
                  break;
              case 'id_client' :
                  $WhereClause .= " id_client = '$value' AND";
                  break;
              case 'n_agence' :
                  $WhereClause .= " n_agence = '$value' AND";
                  break;
              case 'n_guichet' :
                  $WhereClause .= " n_guichet = '$value' AND";
               break;
              default:                  
                  $WhereClause .= " upper($key) like '%' || upper('$value') || '%' AND";
          }
      }
  }
  
  if ($type == "pp") {
      $WhereClause .= " statut_juridique = 1 AND";
  }    
  elseif ($type == "gi") {
      $WhereClause .= " statut_juridique = 3 AND";
  }    

  $WhereClause .= " id_ag = $global_id_agence AND";
  $WhereClause = substr($WhereClause, 0, strlen($WhereClause) - 3);
  $sql = "SELECT count(*) FROM ad_cli WHERE" . $WhereClause . ";"; 
  
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row = $result->fetchrow();
  return $row[0];
}
/**
 * Fonction recupère le premier client  (id_client)
 * @return  Objet ObjError  :
 *                  				errCode: attribut code de l'erreur
 *                          param[0]:tableau assoc id_client=>données du client
 */

function getPremierClient(){
	global $global_id_agence;
  $requete = "SELECT * FROM ad_cli WHERE id_ag=$global_id_agence AND id_client=(SELECT MIN(id_client) FROM ad_cli)";
  $resultat = executeDirectQuery($requete);
  return $resultat;

}

/** 
 * Renvoie un array contenant tous les clients matchant la WhereClause
 * Chaque client est lui-même un tableau associatif avec toutes les données d'un client donné
 * $Where est un tableau associatif de type $Where[clé] = valeur;
 * $Type est un string indiquant quel type de client on désire ("pp" ou "gi" ou "*" pour tout type)
 * Valeurs de retour :
 * Le tableau si OK
 * NULL si aucun client matchant ces critères n'a été trouvé.
 * Die si erreur de la DB
 */
function getMatchedClients($Where, $type) 
{  
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $WhereClause = "";
  if (is_array($Where)) {
    $Where=array_make_pgcompatible($Where);
  }
 
  foreach($Where as $key => $value)
  {
      if(!empty($value))
      {
          switch ($key)
          {
              case 'pp_date_naissance' :
                  $WhereClause .= " substring(to_char(pp_date_naissance, 'YYYY-MM-DD'),1,10) = '$value' AND"; // '$value'"." 00:00:00"." AND";
                  break;
              case 'anc_id_client' :
                  $WhereClause .= " anc_id_client = '$value' AND";
                  break;
              case 'id_client' :
                  $WhereClause .= " id_client = '$value' AND";
                  break;
              case 'matricule' :
                $WhereClause .= " matricule = '$value' AND";
                break;
              case 'n_agence' :
                  $WhereClause .= " n_agence = '$value' AND";
                  break;
              case 'n_guichet' :
                  $WhereClause .= " n_guichet = '$value' AND";
                  break;
              default:
                  $WhereClause .= " upper($key) like '%' || upper('$value') || '%' AND";
          }
      }
  }

  if ($type == "pp") {
      $WhereClause .= " statut_juridique = 1 AND";
  }
  elseif ($type == "gi") {
      $WhereClause .= " statut_juridique = 3 AND";
  }
    
  $WhereClause .= "  id_ag = $global_id_agence AND";
  $WhereClause = substr($WhereClause, 0, strlen($WhereClause) - 3);
  
  $sql = "SELECT * FROM ad_cli WHERE" . $WhereClause . ";";
    
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  
  if ($result->numRows() == 0) return NULL;

  $DATAS = array ();
  
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($DATAS, $tmprow);
  }  

  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getNewClientID() {
  /* Renvoie le prochain ID de client libre dans la base
     Valeurs de retour :
     id_client si OK
     Die si refus de la base de données
  */
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT nextval('ad_cli_id_client_seq');";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $id_client = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $id_client[0];
}

/**
 * Renvoie les informations concernant une personne extérieure
 * Correspond aux infos présentes dans la table des personnes extérieures si celle-ci n'est pas cliente
 * Correspond aux infos extraites de ad_cli si celle-ci est cliente
 * @param int $id_pers_ext ID de la personne extérieure
 * @return Array(tous les champs de la table ad_pers_ext)
 * @author Thomas Fastenakel
 * @since 2.2
 */
function getPersExtDatas($id_pers_ext) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_pers_ext WHERE id_ag=$global_id_agence AND id_pers_ext = $id_pers_ext;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  if ($result->numRows() == 0)
    return NULL;

  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

  if ($DATAS["id_client"] != NULL) {
    // La personne extérieure est cliente, il faut aller récupérer ses infos dans ad_cli
    $id_client = $DATAS["id_client"];

    $DATAS["denomination"] = getClientName($id_client);

    $CLI = getClientDatas($id_client);
    $DATAS["adresse"] = $CLI["adresse"];
    $DATAS["code_postal"] = $CLI["code_postal"];
    $DATAS["ville"] = $CLI["ville"];
    $DATAS["pays"] = $CLI["pays"];
    $DATAS["num_tel"] = $CLI["num_tel"];
    $DATAS["date_naiss"] = $CLI["pp_date_naissance"];
    $DATAS["lieu_naiss"] = $CLI["pp_lieu_naissance"];
    $DATAS["type_piece_id"] = $CLI["pp_type_piece_id"];
    $DATAS["num_piece_id"] = $CLI["pp_nm_piece_id"];
    $DATAS["lieu_piece_id"] = $CLI["pp_lieu_delivrance_id"];
    $DATAS["date_piece_id"] = $CLI["pp_date_piece_id"];
    $DATAS["date_exp_piece_id"] = $CLI["pp_date_exp_id"];
    $DATAS["photo"] = $CLI["photo"];
    $DATAS["signature"] = $CLI["signature"];
  }

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getRelation($id_rel) {
  // PS qui renvoie un tableau associatif avec les infos sur la relation $id_rel
  // IN : ID de la relation
  // OUT: tableau si relation existe
  //      NULL si la relation n'existe pas
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_rel WHERE id_ag=$global_id_agence AND id_rel = '" . $id_rel . "'";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  if ($result->numRows() == 0)
    return NULL;
  $relation = $result->fetchrow(DB_FETCHMODE_ASSOC);
  return $relation;
}

/**
 * Renvoie un tableau associatif avec toutes les données concernant les relations d'un client
 * @since 1.0
 * @param int $id_client ID du client
 * @return Array Tableau avec $i => $REL où $REL = tableau avec le contenu d'une entrée de ad_rel + la dénomination de la personne extérieure ou du client avec lequel le client $id_client est en relation
 */
function getRelationsClient($id_client) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_rel WHERE id_ag=$global_id_agence AND valide = 't' AND id_client = '" . $id_client . "'";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  if ($result->numRows() == 0)
    return NULL;
  $i = 0;
  while ($TEMP = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $DATAS[$i] = $TEMP;
    $PE = getPersExtDatas($TEMP["id_pers_ext"]);
    $DATAS[$i]["denomination"] = $PE["denomination"];
    $i++;
  }
  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getNewRelationID() {
  /* Renvoie le prochain ID de relation libre dans la base
     Valeurs de retour :
     id_relation si OK
     Die si refus de la base de données
  */
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT nextval('ad_rel_id_rel_seq');";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $id_rel = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $id_rel[0];
}

/**
 * PS qui effectue le versement initial sur le compte de base.
 * La nuance par rapport à dépôt compte est qu'on ne prend pas les frais de dépôt et qu'on ne fait pas de check sur un éventuel solde minimum
 *
 * @param Integer $id_client : numéro client qui effectue le versement initial
 * @param Integer $id_guichet : numéro du guichet qui encaisse ou bien si transfert_client est renseigné transfert par la banque
 * @param Float $montant : Somme versée
 * @param Array $comptable_his : Eventuel tableau historique
 * @param unknown_type $transfert_client
 * @param unknown_type $banque
 * @return ObjError :
 *            Code erreur:
 *                    - {@see ERR_CPTE_NON_PARAM}
 *                    - {@see NO_ERROR}
 *            Erreur retournée par:
 *                    - {@see passageEcrituresComptablesAuto}
 *
 */
function versementInitial($id_client, $id_guichet, $montant, & $comptable_his, $transfert_client = NULL, $banque = NULL) {

  global $dbHandler;
  global $error;

  $db = $dbHandler->openConnection();

  $id_cpte_base = getBaseAccountID($id_client);
  $cptes_substitue = array ();
  $cptes_substitue["cpta"] = array ();
  $cptes_substitue["int"] = array ();

  if ($transfert_client == TRUE) {
    //on vire le client par la banque

    if ($banque == NULL)
      signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Erreur pas d'agence origine pour le transfert"
    //FIXME : on débite ou crédite ?
    $cptes_substitue["cpta"]["credit"] = $banque;
    $type_operation = 370;
  } else {
    //paiement au guichet
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
    $type_operation = 160;
    /* Arrondi du montant si paiement au guichet */
    $critere = array();
    $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
    $cpte_gui = getComptesComptables($critere);
    $montant = arrondiMonnaie( $montant, 0, $cpte_gui['devise'] );
  }

  //Mettre l'argent sur le sompte du client
  $cptes_substitue["int"]["credit"] = $id_cpte_base;
  //Produit du compte d'épargne associé
  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_base);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  //on récupère mouvements pour l'historique
  $myErr = passageEcrituresComptablesAuto($type_operation, $montant, $comptable_his, $cptes_substitue);

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $type_operation);
}

function getClientName($id_client) {
  // PS qui renvoie un string contenant le nom du client.
  // Celui-ci varie seln le statut juridique :
  //     PP => pp_nom + pp_prénom
  //     PM => pm_raison_sociale
  //     GI => gi_nom
  $CLI = getClientDatas($id_client);
  switch ($CLI['statut_juridique']) {
  case 1 : // PP
    return $CLI['pp_nom'] . " " . $CLI['pp_prenom'];
  case 2 :
    return $CLI['pm_raison_sociale'];
  case 3 :
  case 4 :
    return $CLI['gi_nom'];
  default :
  //  Solution temporaire au ticket:1325, TODO: voir ticket:1331.
    signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Statut juridique invalide pour le client %s"), $id_client));
   //return NULL;
  }
}

function getClientNameByArray ($CLI)
{
  // PS qui renvoie un string contenant le nom du client.
  // Celui-ci varie seln le statut juridique :
  //     PP => pp_nom + pp_prénom
  //     PM => pm_raison_sociale
  //     GI => gi_nom

  switch ($CLI['statut_juridique'])
    {
    case 1:     // PP
      return $CLI['pp_nom']." ".$CLI['pp_prenom'];
    case 2:
      return $CLI['pm_raison_sociale'];
    case 3:
    case 4:
      return $CLI['gi_nom'];
    default:
      return NULL;
    }
}
/**
 * @author Thomas Fastenakel
 * Fonction appelée depuis l'interface et réalisant tous les traitements liés à la défection par démission ou radiation
 * Préconditions :
 *  - Aucun compte bloqué ne doit subsister (sauf le compte de base qui peut etre désactivé). Il sera alors automatiquement réactivé
 *  - La balance en devise étrangère est nulle
 *  - La balance en devise de référence est positive
 * @param int $id_client ID du client
 * @param int $etat Etat du client après la défection (décédé, raidé etc. )
 * @param string $raison_defection String entré par l'utilisateur comme raison de la défection
 * @param int $id_guichet ID du guichet auquel a lieu la défection
 * @return ErrorObj Objet ErrorObj
 *
 * Traitements effectués
 *  - Vérifie que le compte de base est suffisemment alimenté pour qu'on puisse effectuer la défection
 *  - En cas de défection d'un client EAV, on appelle directement la fonction defectionClient
 *  - Dans les autres cas, s'il y a trop d'argent sur le compte de base du client, on effectue un retrait de ce montant
 *  - Restituer la garantie si elle a été donnée par un client autre que le cleint à qui le cxrédit a été octroyé
 *  - A noter ici qu'on arrondi le montant du compte en fonction de la plus petite pièce de monnaie
 *  - Le restant de l'arrondi est mis dans un compte de produit
 *  - On peut ensuite appeler la fonction de défection proprement dite
 */

function lanceDefectionClient($id_client, $etat, $raison_defection = 'N/A', $id_guichet) {
  global $dbHandler;
  global $global_id_agence, $global_monnaie;

  $db = $dbHandler->openConnection();

  // Si le compte de base du client était bloqué, on débloque celui-ci inconditionnellement
  $cptBase = getBaseAccountID($id_client);
  deblocageCompteInconditionnel($cptBase);

  $myErr = testDefection($id_client);
  if ($myErr->errCode == ERR_DEF_SLD_NON_NUL) {
    $balance = $myErr->param;
    if ($balance < 0) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Incohérence dans l'algo!! LA balance est négative"
    }
  } else
    if ($myErr->errCode == NO_ERR)
      $balance = 0;
    else {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Incohérence dans l'algo!! Erreur :".$myErr->errCode
    }

  // Le cas d'un client EAV est un peu particulier, il n'y a aucun opération financière à effectuer
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1) {
    // Défection client EAV
    // Rien à faire à ce niveau
  } else {
    // Mise à jour du solde_clot du compte de base
    $ACC = getAccountDatas($cptBase);
    $soldeCpt = $ACC["solde"];
    $sql = "UPDATE ad_cpt SET date_clot = '" . date("d/m/Y") . "', solde_clot = $soldeCpt WHERE id_ag=$global_id_agence AND id_cpte = $cptBase;";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $comptable_his = array ();
    $myErr = soldeTousComptes($id_client, $comptable_his);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    /* Si la balance est > 0, retrait du solde du compte de base arrondi à l'unité monétaire la plus petite */
    if ($balance > 0) {
      global $error;
      $baseAccountID = getBaseAccountID($id_client);
      $CPT = getAccountDatas($baseAccountID);

      $balanceArrondie = arrondiMonnaie($balance, -1);
      if ($balanceArrondie > 0) {
        // Débit du compte de base, crédit du compte guichet, c'est le même cptes_substitue pour l'arrondie
        $cptes_substitue = array ();
        $cptes_substitue["cpta"] = array ();
        $cptes_substitue["int"] = array ();

        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($baseAccountID);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        $cptes_substitue["int"]["debit"] = $baseAccountID;
        $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);

        $myErr = passageEcrituresComptablesAuto(140, $balanceArrondie, $comptable_his, $cptes_substitue);
        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(true);
          return $myErr;
        }
      }

      // Transfert du reste de l'arrondi vers le compte agence
      $reste = $balance - $balanceArrondie;
      if ($reste > 0) {
        // Débit du compte de base, crédit du compte paramétré au crédit de l'opération 321
        $cptes_substitue = array ();
        $cptes_substitue["cpta"] = array ();
        $cptes_substitue["int"] = array ();

        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($baseAccountID);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        $cptes_substitue["int"]["debit"] = $baseAccountID;

        $myErr = passageEcrituresComptablesAuto(321, $reste, $comptable_his, $cptes_substitue);
        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(true);
          return $myErr;
        }
      }

    } /* Fin if (balance > 0) */
  } /* Fin else  non client EAV */

  // Défection proprement dite
  $myErr = defectionClient($id_client, $etat, $raison_defection, $comptable_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  global $global_nom_login;
  $myErr = ajout_historique(15, $id_client, NULL, $global_nom_login, date("r"), $comptable_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $id_his = $myErr->param;
  //Solder le compte de base du client à la fin de la défection
 	$sql = "UPDATE ad_cpt SET solde = 0 WHERE id_ag = $global_id_agence AND id_cpte = $cptBase;";
 	$result = $db->query($sql);
 	if (DB :: isError($result)) {
 	    $dbHandler->closeConnection(false);
 	    signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
 	}

  //REL-101 : recuperation id_ecriture_reprise et id_cpte client pour la mise a jour correcte de la table ad_calc_int_paye_his
  $cpteIAP = getCompteIAP();
  if ($cpteIAP != null || $cpteIAP != ''){
    $id_ecri_reprise=recupIdEcritureRepriseIAP(null,$id_his);
    $id_cpte=recupIdCpteClientRepriseIAP($id_his);

    //REL-101 : mise a jour de la table ad_calc_int_paye_his apres reprise IAP
    $myErr2 = clotureIntCalcCpteEpargne($id_cpte, date("r"), $id_his, $id_ecri_reprise);
    if ($myErr2->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr2;
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $id_his);
}

function defectionClient($id_client, $etat, $raison_defection = "N/A", & $comptable_his)
// PS qui réalise la défection d'un client
// Précondition :
//   Le client ne possède que son compte de base, et celui-ci doit avoir été soldé au préalable
//   Il ne reste dessus que la somme nécessaire à la défection
// IN : L'ID du client dont on veut effectuer la défection
//      $etat = Etat du client après la défection (décédé, raidé etc. )
//      $raison_defection = String entré par l'utilisateur comme raison de la défection
// IN-OUT $comptable_his : Historique des précédents mouvements
// OUT: Objet ErrorObj
{
  global $global_id_agence;
  global $dbHandler;
  $db = $dbHandler->openConnection();
  // Vérifications
  $idCpteBase = getBaseAccountID($id_client);

  $ACC = getAccounts($id_client);
  //$ACC = get_comptes_epargne($id_client);

  if (sizeof($ACC) != 1) { // Il reste encore des comptes
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_DEF_CPT_EXIST);
  }
  if (!isset ($ACC[$idCpteBase])) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_DEF_CPT_EXIST);
  }

  $id_cpte = $idCpteBase; // Changement de nom pcq j'ai la flemme de le modifier dans tout le code
  $ACC_BASE = getAccountDatas($id_cpte);
  $PROD = getProdEpargne($ACC_BASE["id_prod"]);

  // Si le client est EAV, la balance est de toute manière à 0
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] == 1) {
    // La clôture du compte de base ne doit pas se faire
  } else {
    // Fermeture du compte de base
    // ************ Copie de la fonction clotureCompteEpargne avec quelques changements ***********
    global $dbHandler, $global_id_client, $global_nom_login;

    $InfoCpte = getAccountDatas($id_cpte);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

    $infos_simul = simulationArrete($id_cpte);
    $solde_cloture = $infos_simul["solde_cloture"];

    /* FIXME/ATTENTION : Le système actuel de passage des écritures en fin de transaction ne nous permùet pas de faire les vérifications quant au solde du compte.
       Rétablir ce check dès que ce système aura été mis à jour
       Le danger actuel étant qu'onne peut plus tre certain que le compte cloturé aura bien un solde nul
    if ($solde_cloture != 0)
    {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La fermeture du compte de base ne laisse pas le solde à 0, il reste $solde_cloture"
    } */

    blocageCompteInconditionnel($id_cpte);
    $dbHandler->closeConnection(true);
    // A partir de ce moment, nous sommes à l'intérieur d'une transaction, les autres utilisateurs voient le compte comme bloqué
    $db = $dbHandler->openConnection();

    deblocageCompteInconditionnel($id_cpte);

    //arrêté du compte
    $erreur = arreteCompteEpargne($ACC_BASE, $PROD, $comptable_his); //solde initial + intérêts
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    // Prélèvement des frais de tenue de compte
    $erreur = preleveFraisDeTenue($id_cpte, $comptable_his);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    $ACC = getAccountDatas($id_cpte);

    /* Prélèvement des frais de fermeture */
    $erreur = preleveFraisFermeture($id_cpte, $comptable_his);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
  }

  // On vérifie que le solde est bien nul

  /* FIXME/ATTENTION : Le système actuel de passage des écritures en fin de transaction ne nous permùet pas de faire les vérifications quant au solde du compte.
     Rétablir ce check dès que ce système aura été mis à jour
     Le danger actuel étant qu'on ne peut plus etre certain que le compte cloturé aura bien un solde nul

  $ACC = getAccountDatas($id_cpte);
  if ($ACC['solde'] != 0)
  {
  $dbHandler->closeConnection(false);
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Incohérence dans algo de fermeturen solde du compte à fermer n'a pas été annulé, il reste ".$ACC["solde"]
  } */

  // Fermeture du compte proprement dite
  $sql = "UPDATE ad_cpt SET etat_cpte = 2 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
  };

  // Invalidation des mandats liés au compte de base
  $MANDATS = getMandats($id_cpte);
  if ($MANDATS != NULL) {
    foreach ($MANDATS as $key => $value) {
      invaliderMandat($key);
    }
  }

  // mise à jour raison clôture
  $sql = "UPDATE ad_cpt SET raison_clot = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
  }

  // ********* Fin de la copie ***************
  // Défection du client
  $raison_defection = string_make_pgcompatible($raison_defection);
  $sql = "UPDATE ad_cli SET etat = $etat, nbre_parts = 0, raison_defection = '$raison_defection', date_defection = '" . date("d/m/Y") . "' WHERE id_ag=$global_id_agence AND id_client = $id_client;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  // Suppression des relations du client
  $DATA['valide'] = 'f';
  $WHERE['id_client'] = $id_client;
  $WHERE['id_ag'] = $global_id_agence;
  $sql = buildUpdateQuery('ad_rel', $DATA, $WHERE);
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new errorObj(NO_ERR);
}

/**
 * Effectue la finalisation de la défection d'un client qui possède un ayant-droit
 * Prérequis : La défection du client pour raison de décès a été enregistrée
 * Tous les comptes en devise étrangère ont été soldés
 * @author TF & Papa ndiaye
 * @since 1.0
 * @param int $id_client ID du client
 * @param int $id_guichet ID du guichet effectuant l'opération
 */
function finalisationDefectionClientAvecAyantDroit($id_client, $id_guichet) {
  global $dbHandler;

  global $global_id_agence, $global_monnaie;

  $db = $dbHandler->openConnection();

  /* Balance du client dans la devise de référence */
  $balance = getBalance($id_client);

  /* On ne traite le cas ou seule la balance de devise de référence est présente */
  if ((sizeof($balance) > 1) or (sizeof($balance) == 1 and !array_key_exists($global_monnaie, $balance))) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); /* Il y a une balance non nulle dans une devise étrangère */
  }

  /* Balance dans la devise de référence */
  $balance = $balance[$global_monnaie];

  /* Déblocage de tous les comptes du client */
  $CPTS = getAccounts($id_client);
  reset($CPTS);
  while (list ($key,) = each($CPTS))
    deblocageCompteInconditionnel($key);

  /* Infos du compte de base */
  $idCpteBase = getBaseAccountID($id_client);
  $ACC = getAccountDatas($idCpteBase);

  /* Si le client doit à l'IMF, on reçoit le manque de l'ayant-droit via le guichet */
  if ($balance < 0) {
    /* Arrondir la balance */
    $balanceArrondie = arrondiMonnaie(abs($balance), 1);

    /* On débite le guichet et on crédite le compte de base par le montant donné par l'ayant-droit */
    $comptable = array ();
    $cptes_substitue = array ();
    $cptes_substitue["cpta"] = array ();
    $cptes_substitue["int"] = array ();
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au guichet"));
    }

    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($idCpteBase);
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["credit"] = $idCpteBase;

    $myErr = passageEcrituresComptablesAuto(160, $balanceArrondie, $comptable, $cptes_substitue);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
    $comptable_his = array_merge($comptable_his, $comptable);

    /* Passer en produit le reste de l'arrondi: débit du guichet par le crédit des produits sur arrondies */
    $type_oper = 320; /* Gains sur arrondis versés au guichet */
    $reste = $balanceArrondie -abs($balance);
    $comptable = array ();
    if ($reste > 0) {
      /* le compte au credit sera paramétré dans l'operation */
      unset ($cptes_substitue["cpta"]["credit"]);
      unset ($cptes_substitue["int"]["credit"]);

      $myErr = passageEcrituresComptablesAuto($type_oper, $reste, $comptable, $cptes_substitue);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
      $comptable_his = array_merge($comptable_his, $comptable);
    }

    /* On vire les comptes d'épargne dans le compte de base et on solde un éventuel crédit en cours */
    $myErr = soldeTousComptes($id_client, $comptable_his);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      global $error;
      return $myErr;
    }
  } /* Fin de if balance < 0 */
  elseif ($balance > 0) {
    $balanceArrondie = arrondiMonnaie($balance, -1);

    /* On vire les comptes d'épargne dans le compte de base et on solde l'éventuel crédit en cours */
    $comptable_his = array ();
    $myErr = soldeTousComptes($id_client, $comptable_his);

    /* On verse le trop-plein à l'ayant-droit s'il y a assez d'argent dans le guichet */
    $montantguichet = get_encaisse($id_guichet, $global_monnaie);
    if ($balanceArrondie > $montantguichet) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_GUI_POS);
    }

    /* Débit du compte de base, crédit du compte guichet */
    $cptes_substitue = array ();
    $cptes_substitue["cpta"] = array ();
    $cptes_substitue["int"] = array ();

    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($idCpteBase);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["debit"] = $idCpteBase;

    $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au guichet"));
    }

    /* l'Opération de retrait */
    $myErr = passageEcrituresComptablesAuto(140, $balanceArrondie, $comptable_his, $cptes_substitue);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(true);
      return $myErr;
    }

    /* Passer le reste de l'arrondi comme un produit : débit du guichet par le crédit des produits sur arrondies */
    $reste = $balance - $balanceArrondie;
    $comptable = array ();
    if ($reste > 0) {
      /* le compte de credit est le compte paramétré au crédit de l'operation */
      unset ($cptes_substitue["cpta"]["credit"]);
      $myErr = passageEcrituresComptablesAuto(321, $reste, $comptable, $cptes_substitue);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }

    $comptable_his = array_merge($comptable_his, $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  } /* Fin de balance > 0 */

  global $error;
  $myErr = defectionClient($id_client, 3, "N/A", $comptable_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  /* Ajout dans l'historique */
  global $global_nom_login;
  $myErr = ajout_historique(16, $id_client, NULL, $global_nom_login, date("r"), $comptable_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $id_his = $myErr->param;

  //REL-101 : recuperation id_ecriture_reprise et id_cpte client pour la mise a jour correcte de la table ad_calc_int_paye_his
  $cpteIAP = getCompteIAP();
  if ($cpteIAP != null || $cpteIAP != ''){
    $id_ecri_reprise=recupIdEcritureRepriseIAP(null,$id_his);
    $id_cpte=recupIdCpteClientRepriseIAP($id_his);

    //REL-101 : mise a jour de la table ad_calc_int_paye_his apres reprise IAP
    $myErr2 = clotureIntCalcCpteEpargne($id_cpte, date("r"), $id_his, $id_ecri_reprise);
    if ($myErr2->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr2;
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $id_his);
}

/**
 * PS appelée exceptionnellement lorsqu'un client est décédé et qu'il ne possèdait pas d'ayant-droit
 * Prérequis : La défection du client pour raison de décès a été enregistrée et qu'il n'y a pas d'ayant-droit
 * On verse toute l'épargne dans le compte de base
 * S'il y a un crédit en cours on transfert d'abord ce qu'on peut payer dans le compte de liaison et on rembourse ensuite
 * Si le crédit n'est pas solde, on passe en perte le capital restant dû
 * A la fin du remboursement des dettes, s'il reste de l'argent dans le compte de base alors le passer en profil
 * On effectue la défection proprement dite
 * @author TF & Papa ndiaye & Fatou
 * @since 1.0
 * @param int $id_client l'identifiant du client
 * @return errorObjet : NO_ERR si tout est ok sinon un code erreur
 */
function defectionClientDecedeSansAyantDroit($id_client) {
  global $global_id_agence;
  global $global_nom_login;
  global $dbHandler;
  global $global_monnaie;

  //$db = $dbHandler->openConnection();

  /* Récupération des infos du client */
  $CLI = getClientDatas($id_client);
  /* Récupération des éventuels listes de groupe solidaire auquel le client appartient */
  $listeGroupSol = getGroupSol($id_client);
  /* Vérifier que le client est en attente d'enregistrement décès */
  if ($CLI['etat'] != 7)
    return new ErrorObj(ERR_CLIENT_NON_EAED);

  /* Vérifie qu'Il n'existe pas d'ayant-droit */
  $AyantDroit = existeAyantDroit($id_client);
  if (count($AyantDroit))
    return new ErrorObj(ERR_CLIENT_EXIST_AD);

  /* bloquer tous les comptes du client pour qu'il n'y ait pas d'opérations financières dessus */
  $CPTS = getAccounts($id_client);
  foreach ($CPTS as $key => $value)
  blocageCompteInconditionnel($key);

  /* Ouverture d'une nouvelle transaction. Les comptes du client sont bloqués pour les autres utilisateurs */
  $db = $dbHandler->openConnection();

  /* Déblocage de tous les comptes pour l'utisateur ayant lancé la défection */
  foreach ($CPTS as $key => $value)
  deblocageCompteInconditionnel($key);

  /* Infos du compte de base */
  $idCpteBase = getBaseAccountID($id_client);

  $ACC = getAccountDatas($idCpteBase);
  $devise_base = $ACC['devise']; /* devise du compte de base */

  /* Mémorisation du solde de clôture du compte de base(comme il sera fermé sans passer par arretecompte()) */
  $soldeCpt = $ACC["solde"];
  $sql = "UPDATE ad_cpt SET date_clot = '" . date("d/m/Y") . "', solde_clot = $soldeCpt WHERE id_ag=$global_id_agence AND id_cpte = $idCpteBase;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
  }

  /* Tableau des mouvements comptables */
  $comptable_his = array ();

  /* Calcul des avoirs du client */
  $montant_disponible = 0;

  /* Récupération du solde du compte de base */
  //$montant_disponible += getSoldeDisponible($idCpteBase);
  $infos_simul = simulationArrete($idCpteBase);
  $montant_disponible = $infos_simul["solde_cloture"];

  /* Virement de tous les comptes d'épargne (sans distinction de devise) dans le compte de base */
  $CPTS = get_comptes_epargne($id_client); /* Récupération de tous les comptes d'épargne de service financier non fermés */
  unset ($CPTS[$idCpteBase]); /* Retrait du compte de base de la liste des comptes récupérés ci-dessus */

  $idCptPS = getPSAccountID($id_client); /* Récupération du compte de parts sociales (il n'est pas service financier) */
  if ($idCptPS != NULL) {
    $infos_ps = getAccountDatas($idCptPS);
    $CPTS[$idCptPS] = $infos_ps;
  }

  /* Tableau des comptes de liaison des dossiers de crédit */
  $cptes_liaison = array ();

  /* Récupération des éventuels comptes de garantie appartenant au client(ils ne sont pas service financier)
     Si un compte de garantie n'appartient pas au client alors il ne sera pris que si le client n'a pas assez d'argent pour rembourser
  */
  $whereCl = " AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
  $dossiers = getIdDossier($id_client, $whereCl);
  foreach ($dossiers as $id_doss => $value) {
    /* Récupération de l'épargne nantie numéraire du dossier */
    $liste_gar = getListeGaranties($id_doss);
    foreach ($liste_gar as $key => $val) {
      /* la garantie doit être numéraire, non restituée et non réalisée */
      if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5) {
        $nantie = $val['gar_num_id_cpte_nantie'];
        if ($nantie != NULL) { /* S'il y a un compte d'épargne nantie associé au dossier de crédit */
          $CPT_GAR = getAccountDatas($nantie);
          if ($CPT_GAR['id_titulaire'] == $id_client)
            $CPTS[$nantie] = $CPT_GAR;
        }
      }
    }

    /* Mémorisation des comptes de liaison des dossiers de crédit */
    $cptes_liaison[$value['cpt_liaison']]['id_cpte'] = $value['cpt_liaison'];
  }

  /* virer les soldes des comptes d'épargne dans le compte de base et les fermer. Les comptes de liaion seront virés mais pas fermés */
  foreach ($CPTS as $key => $cpt) {
    /* Infos du produit d'épargne auquel est associé le compte */
    $id_cpte = $key;
    $InfoCpte = getAccountDatas($id_cpte);
    $InfoProduit = getProdEpargne($cpt["id_prod"]);

    $erreur = checkCloture($id_cpte); /* Vérifier que le compte peut est être clôturer */
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    /* Calcule du solde de clôture du compte */
    $solde_cloture = 0;

    /* Si le compte était en attente de fermeture, on procède directement au virement du solde */
    if ($InfoCpte["etat_cpte"] == 5)
      $solde_cloture = $InfoCpte["solde"];
    else {
      /* On désigne le compte lui-même comme compte de versement des intérêts */
      $InfoCpte["cpt_vers_int"] = $InfoCpte["id_cpte"];
      $erreur = arreteCompteEpargne($InfoCpte, $InfoProduit, $comptable_his); /* calcul des intérêts en fonction du produit */
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
      $solde_cloture = $erreur->param["solde_cloture"];

      /* Si compte à terme, prélèvement des pénalités si rupture anticipée */
      if ($InfoCpte["terme_cpte"] > 0) {
        $erreur = prelevePenalitesEpargne($id_cpte, $comptable_his);
        if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
        }
        $solde_cloture -= $erreur->param;
      }

      // Prélèvement des frais de tenue de compte
      $erreur = preleveFraisDeTenue($id_cpte, $comptable_his);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
      $solde_cloture -= $erreur->param;

      /* Prélèvement des frais de fermeture */
      $erreur = preleveFraisFermeture($id_cpte, $comptable_his);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
      $solde_cloture -= $erreur->param;
    }

    /* Virement du solde du compte à clôturer dans le compte de base */
    if ($solde_cloture != 0) {
      /* Débit du compte à clôturer par le crédit du compte de base */
      $cptes_substitue = array ();
      $cptes_substitue["cpta"] = array ();
      $cptes_substitue["int"] = array ();

      if ($solde_cloture > 0) {
        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
        $cptes_substitue["int"]["debit"] = $id_cpte;

        $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($idCpteBase);
        $cptes_substitue["int"]["credit"] = $idCpteBase;
      } else {
        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($idCpteBase);
        $cptes_substitue["int"]["debit"] = $idCpteBase;

        $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
        $cptes_substitue["int"]["credit"] = $id_cpte;
      }

      if (($cptes_substitue["cpta"]["debit"] == NULL) or ($cptes_substitue["cpta"]["credit"] == NULL)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }

      $devise_cpte = $cpt['devise']; /* devise du compte à virer */

      $myErr = effectueChangePrivate($devise_cpte, $devise_base, abs($solde_cloture), 120, $cptes_substitue, $comptable_his);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }

      /* fermeture du compte si ce n'est pas un compte de liaison ,raison clôture= 1 => "Défection du client */
      if (!in_array($id_cpte, $cptes_liaison)) {
        $erreur = fermeCompte($id_cpte, 1, $solde_cloture);
        if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
        }
      } else /* c'est un compte de liaison */
        $cptes_liaison[$id_cpte]['solde_cloture'] = $solde_cloture;

      /* Ajouter du solde dans le montant disponible */
      if ($devise_cpte == $devise_base)
        $montant_disponible += $solde_cloture;
      else
        $montant_disponible += calculeCV($devise_cpte, $devise_base, $solde_cloture);

    }

  } /* End foreach comptes d'épargne */

  /* Rembourser tout ou partie des éventuels crédit en cours */
  foreach ($dossiers as $id_doss => $value) {
    /* Récupération du compte de liaison */
    $cpte_liaison = $value['cpt_liaison'];

    /* Infos du produit */
    $DOSS = getDossierCrdtInfo($id_doss);

    /* Récupération du solde restant dû du crédit : total solde_cap + total solde_int + total solde_pen */
    $solde_credit = 0;
    $myErr = simulationArreteCpteCredit($solde_credit, $id_doss);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    /* - On annule la garantie à constituer restant(car n'est prise en compte par simulationArreteCpteCredit ).
        On garde le compte de liaison pour le remboursement
        car la devise du compte de base peut être différente de celle du crédit */
    $myErr = supprimeReferenceCredit($id_client, $id_doss, true, false);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    /* Récupération du capital total restant dû */
    $solde_capital = getSoldeCapital($id_doss);

    /* Montant du crédit dans la devise du compte de base */
    $solde_cre_devise_base = calculeCV($DOSS["devise"], $devise_base, $solde_credit);

    /* Calcul du montant que peut remboursé le client */
    $mnt_remb = min($montant_disponible, $solde_cre_devise_base);

    /* Diminuner le montant disponible du montant du remboursement  */
    $montant_disponible -= $mnt_remb;

    /* Si le compte de liaison est différent du compte de base alors l'alimenter du montant du remboursement */
    if ($cpte_liaison != $idCpteBase) {
      /* Débit du compte de base par le crédit du compte de liaison */
      $cptes_substitue = array ();
      $cptes_substitue["cpta"] = array ();
      $cptes_substitue["int"] = array ();

      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($idCpteBase);
      $cptes_substitue["int"]["debit"] = $idCpteBase;

      $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($cpte_liaison);
      $cptes_substitue["int"]["credit"] = $cpte_liaison;

      $myErr = effectueChangePrivate($devise_base, $DOSS["devise"], $mnt_remb, 120, $cptes_substitue, $comptable_his);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }

    /* Montant du remboursement dans la devise du compte du crédit */
    $mnt_remb_cre = calculeCV($devise_base, $DOSS["devise"], $mnt_remb);

    /* Remboursement tout ou partie du crédit */
    $myErr = rembourse_montant($id_doss, $mnt_remb_cre, 2, $comptable_his);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    /* On récupérère le capital remboursé ci-dessus */
    $capital_rembourse = 0; /* le capital remboursé */
    $idCptCre = $DOSS["cre_id_cpte"]; /* le compte interne de crédit */
    /* On ne peut pas faire un select sur la base car le remboursement n'est pas encore effectif */
    foreach ($comptable_his as $key => $value) {
      if ($value['cpte_interne_cli'] == $idCptCre and $value['sens'] == SENS_CREDIT)
        $capital_rembourse += $value['montant'];
    }

    /* Calcule du capital restant dû : capital à passer en perte */
    $capital_restant_du = $solde_capital - $capital_rembourse;
    foreach ($dossiers as $id_doss=>$value){
      if ($value['gs_cat'] != 2){
        /* Passage en perte du capital restant dû, débit du compte de l'opération 280 et crédit du compte de crédit */
      if ($capital_restant_du > 0) {
      /* Id de l'état en perte */
      $id_etat_perte = getIDEtatPerte();

      $type_oper = 280; /* opération passage crédit en perte */
      $cptes_substitue = array ();
      $cptes_substitue["cpta"] = array ();
      $cptes_substitue["int"] = array ();

      /* Récupération des comptes comptables associés aux états du produit de crédit */
      $CPTS_ETAT = recup_compte_etat_credit($DOSS["id_prod"]);

      /* Compte au débit : en principe compte de charge */
      $cptes_substitue["cpta"]["debit"] = $CPTS_ETAT[$id_etat_perte];
      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé à l'état en perte du produit de crédit "));
      }

      $cptes_substitue["cpta"]["credit"] = $CPTS_ETAT[$DOSS["cre_etat"]];
      $cptes_substitue["int"]["credit"] = $DOSS["cre_id_cpte"];
      if ($cptes_substitue["cpta"]["credit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit de crédit"));
      }

      $myErr = passageEcrituresComptablesAuto($type_oper, $capital_restant_du, $comptable_his, $cptes_substitue);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(true);
        return $myErr;
      }
    }
  }
 }
    /* fermeture du compte de liaison du crédit si c'est pas le compte de base */
    if ($cpte_liaison != NULL and $cpte_liaison != $idCpteBase) {
      $erreur = fermeCompte($cpte_liaison, 1, $cptes_liaison[$cpte_liaison]['solde_cloture']);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
    }

    /* Fermeture du compte de crédit */
    $sql = "UPDATE ad_cpt SET etat_cpte = 2 WHERE id_ag=$global_id_agence AND id_cpte = $idCptCre;";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    foreach ($dossiers as $id_doss=>$value){
    if ($value['gs_cat'] != 2){ // Seuls les dossiers individuels doivent être soldés
     /* Passage du dossier de crédit à l'état "soldé */
     $Fields = array (
                "etat" => 6,
                "date_etat" => date("d/m/Y"
                                   ));
      updateCredit($id_doss, $Fields); //Mettre l'état du dossier de crédit à soldé
   }
  }
 } /* End Foreach de //Rembourser tout ou partie d'un éventuel crédit en cours /* End if ($id_client) */

  /* Si le solde final du compte de base est positif alors le passer en profil sinon en perte */
  $restant = $montant_disponible;
  if ($restant > 0) {
    /* Passage du restant des avoirs arrondie à l'unité monétaire la plus petite en produit exceptionnel */
    $type_oper = 350; /* Intégration exceptionnelle solde du compte d'epargne dans fonds propres */
    $restantArrondie = arrondiMonnaie($restant, -1);

    /* Débit du compte de base, crédit du compte au crédit de l'opération 350 */
    $cptes_substitue = array ();
    $cptes_substitue["cpta"] = array ();
    $cptes_substitue["int"] = array ();

    /* Compte comptable à débiter */
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($idCpteBase);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["debit"] = $idCpteBase;

    $myErr = passageEcrituresComptablesAuto($type_oper, $restantArrondie, $comptable_his, $cptes_substitue);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(true);
      return $myErr;
    }

    /* Transfert du reste de l'arrondi vers le compte agence */
    $reste = $restant - $restantArrondie;
    if ($reste > 0) {
      unset ($cptes_substitue["cpta"]["credit"]);
      $myErr = passageEcrituresComptablesAuto(321, $reste, $comptable_his, $cptes_substitue);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(true);
        return $myErr;
      }
    } //reste
  } // fin intégration except
  elseif ($restant < 0) {
    $restant = abs($restant);
    /* Passage dans un compte d'attente du solde débiteur */
    $type_oper = 354; /* Passage en perte solde compte épargne débiteur */

    /* Crédit du compte de base , débit compte d'attente ou compte de charge au débit de l'opération 354 */
    $cptes_substitue = array ();
    $cptes_substitue["cpta"] = array ();
    $cptes_substitue["int"] = array ();

    /* Compte comptable à créditer */
    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($idCpteBase);
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["credit"] = $idCpteBase;

    $myErr = passageEcrituresComptablesAuto($type_oper, $restant, $comptable_his, $cptes_substitue);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(true);
      return $myErr;
    }

  }

  /* Défection proprement dite du client */
  $myErr = defectionClient($id_client, 3, _("Décès"), $comptable_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  global $global_nom_login;
  $myErr = ajout_historique(16, $id_client, NULL, $global_nom_login, date("r"), $comptable_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $id_his = $myErr->param;

  //REL-101 : recuperation id_ecriture_reprise et id_cpte client pour la mise a jour correcte de la table ad_calc_int_paye_his
  $cpteIAP = getCompteIAP();
  if ($cpteIAP != null || $cpteIAP != ''){
    $id_ecri_reprise=recupIdEcritureRepriseIAP(null,$id_his);
    $id_cpte=recupIdCpteClientRepriseIAP($id_his);

    //REL-101 : mise a jour de la table ad_calc_int_paye_his apres reprise IAP
    $myErr2 = clotureIntCalcCpteEpargne($id_cpte, date("r"), $id_his, $id_ecri_reprise);
    if ($myErr2->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr2;
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $id_his);

}

/**
 * PS qui est apelée depuis l'interface.
 * Effectue un versement initial pour le client, suivi de la perception des frais d'adhésion.
 * Ajoute dans l'historique
 *
 * @param Integer $id_client : Numéro client
 * @param Integer $id_guichet : Numero Guichet
 * @param Montant $Montant : Somme versée
 * @param Binaire $ouvre_cpt_base : 0 => pas de compte de base, 1 => ouverture d'un compte de base
 * @param Montant $mnt_droits_adh
 * @return ErrorObj
 */
function perceptionFraisAdhesionInt($id_client, $id_guichet, $Montant, $ouvre_cpt_base, $mnt_droits_adh = NULL) {

  global $global_id_agence;
  global $dbHandler;

  $db = $dbHandler->openConnection();
  $comptable_his = array ();

  $CLI = getClientDatas($id_client);

  //montant droit adhesion agence
 	$mnt_droits_adh_agc = getMontantDroitsAdhesion($CLI["statut_juridique"]);
 	if ($mnt_droits_adh_agc == 0 ) {
 	   // Basculer l'état du client à "auxiliaire"
 	   $sql = "UPDATE ad_cli SET etat = 2 WHERE id_ag=$global_id_agence AND id_client = $id_client;";
 	   $result = $db->query($sql);
 	   if (DB :: isError($result)) {
 	      $dbHandler->closeConnection(false);
 	      signalErreur(__FILE__, __LINE__, __FUNCTION__);
 	   }

 	} else {
 	if ($ouvre_cpt_base == 1) {
    $idCpteBase = getBaseAccountID($id_client);
    deblocageCompteInconditionnel($idCpteBase);

    if ($Montant > 0) {
      // Versement initial
      $myErr = versementInitial($id_client, $id_guichet, $Montant, $comptable_his);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }

     if ($mnt_droits_adh == NULL )
    $mnt_droits_adh = getMontantDroitsAdhesion($CLI["statut_juridique"]);

  // Perception des frais d'adhésion
  $myErr = perceptionFraisAdhesion($id_client, $comptable_his, $mnt_droits_adh);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }


    }
  } else
    if ($Montant > 0) { // Pas normal qu'on ait q q ch à déposer alors que le compte de base ne sera pas utilisé
      $dbHandler->closeConnection(false);
       return new ErrorObj(ERR_GENERIQUE, _("Le compte de base du client n'existe pas'") );
    }
 	}
  // ajout historique
  global $global_nom_login;
  $myErr = ajout_historique(31, $id_client, $Montant, $global_nom_login, date("r"), $comptable_his, NULL);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $idHis = $myErr->param;

  // Fin de la procédure
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $idHis);
}

/**
 * Effectue la perception d'une tranche des frais d'adhésion à partir du solde
 * du compte de base du client dans le cas d'un paiement par tranche des frais
 * @author Djibril NIANG
 * @since 2.9
 * @param int $id_client ID du client pour lequel on perçoit les frais
 * @param float $montant Montant de la tranche des droits d'adhésion
 * @return Objet Erreur
 */
function perceptionTrancheFraisAdhesion($id_client, $comptable_his, $montant) {
  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();
  $id_cpte_base = getBaseAccountID($id_client);
  $InfoCpte = getAccountDatas($id_cpte_base);
  $devise = $InfoCpte['devise'];

  $type_ope = 90;
  $subst = array();
  $subst["cpta"] = array();
  $subst["int"] = array();
  $subst["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_base);
  if ($subst["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé au produit d'épargne"));
  }
  $subst["int"]["debit"] = $id_cpte_base;

  $myErr = reglementTaxe($type_ope, $montant, SENS_CREDIT, $devise, $subst, $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  // Débit du compte de base, crédit du compte frais adhésion
  $cptes_substitue = array ();
  $cptes_substitue["cpta"] = array ();
  $cptes_substitue["int"] = array ();
  //on recupere l'id du compte de base du client


  $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_base);
  if ($cptes_substitue["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["debit"] = $id_cpte_base;
  /*$cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_base);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["credit"] = $id_cpte_base;*/

  //on récupère mouvements pour l'historique
  $myErr = passageEcrituresComptablesAuto(90, $montant, $comptable_his, $cptes_substitue);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(true);
    return $myErr;
  }

  // ajout historique
  global $global_nom_login;
  $myErr = ajout_historique(31, $id_client, $montant, $global_nom_login, date("r"), $comptable_his, NULL);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $idHis = $myErr->param;

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $idHis);
}

/**
 * Effectue la perception au guichet des frais d'adhésion
 * D guichet par C compte de produits pour frais d'adhésion
 * @author Thomas FASTENAKEL
 * @since 1.0
 * @param int $id_client ID du client pour lequel on perçoit les frais
 * @param float $montant Montant des droits d'adhésion (optionnel)
 * @return Objet Erreur
 */
function perceptionFraisAdhesion($id_client, & $comptable_his, $montant = NULL) {
  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();

  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] != 1 && $CLI["etat"] != 11 ) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_PFH_DEJA_PAYE, $id_client);
  }

  /*if ($montant == NULL) {
    $montant = getMontantDroitsAdhesion($CLI["statut_juridique"]);
  } else {
    // Arrondi du montant
    $critere = array();
    $critere['num_cpte_comptable'] = getCompteCptaGui($id_guichet);
    $cpte_gui = getComptesComptables($critere);
    $montant = arrondiMonnaie( $montant, 0, $cpte_gui['devise'] );
  }*/

  $id_cpte_base = getBaseAccountID($id_client);
  $InfoCpte = getAccountDatas($id_cpte_base);
  $devise = $InfoCpte['devise'];
  $type_ope = 90;
  $subst = array();
  $subst["cpta"] = array();
  $subst["int"] = array();
  $subst["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_base);
  if ($subst["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé au produit d'épargne"));
  }
  $subst["int"]["debit"] = $id_cpte_base;

  $myErr = reglementTaxe($type_ope, $montant, SENS_CREDIT, $devise, $subst, $comptable_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $cptes_substitue = array ();
  $cptes_substitue["cpta"] = array ();
  $cptes_substitue["int"] = array ();

  //on recupere l'id du compte de base du client
  $id_cpte_base = getBaseAccountID($id_client);

  $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_base);
  if ($cptes_substitue["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }
  $cptes_substitue["int"]["debit"] = $id_cpte_base;

  $myErr = passageEcrituresComptablesAuto(90, $montant, $comptable_his, $cptes_substitue);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  // Basculer l'état du client à "auxiliaire"
  $sql = "UPDATE ad_cli SET etat = 2 WHERE id_ag=$global_id_agence AND id_client = $id_client;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  // Fin de la procédure
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Souscription d'un nombre de parts sociales supplémentaires pour un client donné
 *
 * Fonction appelée depuis l'interface.
 * @param int $id_client L'identifiant du client souscrivant des parts sociales.
 * @param int $nbre_parts Le nombre de parts à souscrire.
 * @param int $id_utilisateur L'identifiant de l'utilisateur effectuant la transaction (FIXME, n'est-ce pas global_id_login ?)
 * @param int $versement Le montant versé en cas de souscription d'une tranche de part.
 * @return ErrorObj Avec en paramètre le montant souscrit.
 */
function souscriptionPartsSocialesInt($id_client, $nbre_parts, $id_utilisateur ) {
  global $global_nom_login;


  $myErr = souscriptionPartsSociales($id_client, $nbre_parts, $id_utilisateur);
  if ($myErr->errCode != NO_ERR)
    return $myErr;

  $mnt = $myErr->param;
  $id_cpte_base = getBaseAccountID($id_client);

  $myErr = ajout_historique(20, $id_client, $nbre_parts, $global_nom_login, date("r"), NULL, NULL);
  if ($myErr->errCode != NO_ERR)
    return $myErr;
 

  return $myErr;
}
/**
 * Liberation d'un nombre de parts sociales  par montant pour un client donné
 *
 * Fonction appelée depuis l'interface.
 * @param int $id_client L'identifiant du client souscrivant des parts sociales.
 * @param int $nbre_parts Le nombre de parts à souscrire.
 * @param int $id_utilisateur L'identifiant de l'utilisateur effectuant la transaction (FIXME, n'est-ce pas global_id_login ?)
 * @param int $versement Le montant versé en cas de souscription d'une tranche de part.
 * @return ErrorObj Avec en paramètre le montant souscrit.
 */
function liberationPartsSocialesInt($id_client, $nbrePSlib, $id_utilisateur, $versement = NULL) {
	
	global $global_nom_login;
	
	$comptable_his = array ();
	$myErr = liberationPartsSociales($id_client, $nbrePSlib, $id_utilisateur, $comptable_his, $versement);
	if ($myErr->errCode != NO_ERR)
		return $myErr;

	$mnt = $myErr->param;
	$id_cpte_base = getBaseAccountID($id_client);

	$myErr = ajout_historique(28, $id_client, $nbrePSlib, $global_nom_login, date("r"), $comptable_his, NULL);
	if ($myErr->errCode != NO_ERR)
		return $myErr;
	
	return $myErr;
}


/**
 * Transfert PS ver autre compte PS
 *
 * Fonction appelée depuis l'interface.
 * @param array DATA ,id_utilisateur.
 * @return ErrorObj Avec en paramètre le montant souscrit.
 */
function transfertPSPSInt($DATA, $id_utilisateur) {
	global $global_nom_login;
	
	$comptable_his = array ();
	$myErr = transfert_ps_ps ( $DATA, $id_utilisateur, $comptable_his );
	if ($myErr->errCode != NO_ERR)
		return $myErr;
	
	$mnt = $myErr->param;
	$myErr = ajout_historique ( 23, $DATA ['id_client_src'], $DATA ['nmbre_part_a_transferer'], $global_nom_login, date ( "r" ), $comptable_his, NULL );
	
	if ($myErr->errCode != NO_ERR) {
		return $myErr;
	} else {
		$id_his = $myErr->param;
		$err = post_update_psps ( $DATA, $id_his );
		if ($err->errCode != NO_ERR){
			return $err;
		}else{//historique ps
			$id_his = $myErr->param;
			$err_h =historique_mouvementPs( $DATA['id_client_src'], $id_his, 23);
			if ($err_h->errCode != NO_ERR){
				return $err_h;
			}
			$err_h_dest =historique_mouvementPs( $DATA['id_client_dest'], $id_his, 23);
			if ($err_h_dest->errCode != NO_ERR){
				return $err_h_dest;
			}
		}
	}
	
	return $myErr;
}
/**
 * MAJ base post transfert PS to pS terminé
 */
function post_update_psps($DATA, $id_his) {
	global $global_nom_login;
	global $global_id_agence;
	global $dbHandler;
	global $global_monnaie;
	global $db;
	
	// Ouverture de transaction
	$db = $dbHandler->openConnection ();
	
	// Augmenter le nbre de parts sociales de l'agence. ou nbre ps lib/souscrit sont enter_transferer
	// pas necessaire car ici les nbre de ps sont inter_transferer
	
	//Mise a jour libel operation
	
	if(is_trad(($DATA['libel_operation']))){
		$libel_operation_trad = ($DATA['libel_operation']);
		$libel_operation = $libel_operation_trad->save();//id_str
	}else{
	
		$libel_operation_trad = new Trad();
		$libel_operation_trad->set_traduction(get_langue_systeme_par_defaut(), ($DATA['libel_operation']));
	
		$libel_operation = $libel_operation_trad->save();//id_str
	}

	$sql = "UPDATE ad_transfert_ps_his SET libel_operation = $libel_operation WHERE id_ag = $global_id_agence AND id_client_src = '" . $DATA ['id_client_src'] . "' AND id = '" . $DATA ['id_demande'] . " '";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // UPDATE echoué", $result->getMessage()
	}
	
	// Mise à jour nbre de parts du client_src souscrit
	$sql = "UPDATE ad_cli SET nbre_parts = nbre_parts - '" . $DATA ['nmbre_part_a_transferer'] . "' WHERE id_ag = $global_id_agence AND id_client = '" . $DATA ['id_client_src'] . "' ";
	$result = $db->query ( $sql );
	
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // UPDATE echoué", $result->getMessage()
	}
	
	// Mise à jour nbre de parts du client_src liberer
	$sql = "UPDATE ad_cli SET nbre_parts_lib = nbre_parts_lib - '" . $DATA ['nmbre_part_a_transferer'] . "' WHERE id_ag = $global_id_agence AND id_client = '" . $DATA ['id_client_src'] . "' ";
	$result = $db->query ( $sql );
	
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // UPDATE echoué", $result->getMessage()
	}
	
	// Mise à jour nbre de parts du client_dest souscrit
	$sql = "UPDATE ad_cli SET nbre_parts = nbre_parts + '" . $DATA ['nmbre_part_a_transferer'] . "' WHERE id_ag = $global_id_agence AND id_client = '" . $DATA ['id_client_dest'] . "'";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // UPDATE echoué", $result->getMessage()
	}
	
	// Mise à jour nbre de parts du client_dest liberer
	$sql = "UPDATE ad_cli SET nbre_parts_lib = nbre_parts_lib + '" . $DATA ['nmbre_part_a_transferer'] . "' WHERE id_ag = $global_id_agence AND id_client = '" . $DATA ['id_client_dest'] . "'";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // UPDATE echoué", $result->getMessage()
	}
	
	// update ad_part_sociale_his
	$now = date ( "Y-m-d" );
	$sql = "UPDATE ad_transfert_ps_his SET etat_transfert = 2, id_his = $id_his ,date_approb = '$now',id_operation  = 82 WHERE id_ag = $global_id_agence AND id_client_src = '" . $DATA ['id_client_src'] . "' AND id = '" . $DATA ['id_demande'] . " '";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // UPDATE echoué", $result->getMessage()
	}
	
	// Recuperation montant a transferer pour set montant bloquer
	$sql = "SELECT val_nominale_part_sociale FROM ad_agc WHERE id_ag = $global_id_agence";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
	}
	$tmpRow = $result->fetchrow ();
	$val_nominale_part_sociale = $tmpRow [0];
	$Montant = $DATA ['nmbre_part_a_transferer'] * $val_nominale_part_sociale;
	
	 // Mise à jour du montant bloqué du compte de parts du client source
	$sql = "UPDATE ad_cpt SET mnt_bloq = mnt_bloq - $Montant WHERE id_ag=$global_id_agence AND id_cpte = ' ".$DATA ['id_cpte_src'] ."'";
	$result=$db->query($sql);
	if (DB::isError($result))
	{
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__); // UPDATE echoué", $result->getMessage()
	} 
	
	// Mise à jour du montant bloqué du compte de parts du client dest
	 $sql = "UPDATE ad_cpt SET mnt_bloq = mnt_bloq + $Montant WHERE id_ag=$global_id_agence AND id_cpte = ' ".$DATA ['id_cpte_dest'] ."'";
	 $result=$db->query($sql);
	if (DB::isError($result))
	{
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__); // UPDATE echoué", $result->getMessage()
	} 
	
	
	$dbHandler->closeConnection ( true );
	return new ErrorObj ( NO_ERR );
}

/**
 * Transfert part sociale a compte courant
 *
 * Fonction appelée depuis l'interface.
 */
function transfertPSCourantInt( $DATA, $id_utilisateur) {
	global $global_nom_login;
	$comptable_his = array ();
	
	$myErr = transfert_ps_courant ( $DATA, $id_utilisateur, $comptable_his );
	
	if ($myErr->errCode != NO_ERR)
		return $myErr;
	
	$mnt = $myErr->param;
	$id_cpte_base = getBaseAccountID ( $DATA ['id_client_src'] );
	
	$myErr = ajout_historique ( 23, $DATA ['id_client_src'], $DATA ['nmbre_part_a_transferer'], $global_nom_login, date ( "r" ), $comptable_his, NULL );
	
	if ($myErr->errCode != NO_ERR) {
		return $myErr;
	} else {//maj post operation
		$id_his = $myErr->param;
		$err = post_update_pscourant ( $DATA, $id_his );
		if ($err->errCode != NO_ERR){
			return $err;
		}else{//historique ps
			$id_his = $myErr->param;
			$err_h =historique_mouvementPs( $DATA ['id_client_src'], $id_his, 23);
			if ($err_h->errCode != NO_ERR){
				return $err_h;
			}
		}
	}
	
	return $myErr;
	
}
/**
 * MAJ base post transfert PS to compte courant
 */
function post_update_pscourant( $DATA, $id_his) {
	global $global_nom_login;
	global  $global_id_agence;
	global $dbHandler;
	global $global_monnaie;
	global $db;

	// Ouverture de transaction
	$db = $dbHandler->openConnection();


	//Mise a jour libel operation
	
	if(is_trad(($DATA['libel_operation']))){
		$libel_operation_trad = ($DATA['libel_operation']);
		$libel_operation = $libel_operation_trad->save();//id_str
	}else{
	
		$libel_operation_trad = new Trad();
		$libel_operation_trad->set_traduction(get_langue_systeme_par_defaut(), ($DATA['libel_operation']));
	
		$libel_operation = $libel_operation_trad->save();//id_str
	}
	
	$sql = "UPDATE ad_transfert_ps_his SET libel_operation = $libel_operation WHERE id_ag = $global_id_agence AND id_client_src = '" . $DATA ['id_client_src'] . "' AND id = '" . $DATA ['id_demande'] . " '";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // UPDATE echoué", $result->getMessage()
	}
	
	// Mise à jour nbre de parts souscrit du client_src 
	$sql = "UPDATE ad_cli SET nbre_parts = nbre_parts - '".$DATA['nmbre_part_a_transferer']."' WHERE id_ag = $global_id_agence AND id_client = '".$DATA['id_client_src']."' ";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
	}
	
	// Mise à jour nbre de parts du client_src liberer
	$sql = "UPDATE ad_cli SET nbre_parts_lib = nbre_parts_lib - '" . $DATA ['nmbre_part_a_transferer'] . "' WHERE id_ag = $global_id_agence AND id_client = '" . $DATA ['id_client_src'] . "' ";
	$result = $db->query ( $sql );
	
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // UPDATE echoué", $result->getMessage()
	}
	
	//getmontant
	$montant = ($DATA['nmbre_part_a_transferer'] * $DATA['valeur_nominale_ps']);
	
	//mise a jour capital_sociale_souscrit& capitale sociale_lib de l'agence
	$sql = "UPDATE ad_agc SET capital_sociale_souscrites = capital_sociale_souscrites - $montant , capital_sociale_lib = capital_sociale_lib - $montant  WHERE id_ag = $global_id_agence";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
	}
	
	// diminuer le nbre de parts sociales souscrit de l'agence.
	$sql = "UPDATE ad_agc SET nbre_part_sociale = nbre_part_sociale - '".$DATA['nmbre_part_a_transferer']."' WHERE id_ag = $global_id_agence";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
	}
	
	// diminuer le nbre de parts sociales liberer de l'agence.
	$sql = "UPDATE ad_agc SET nbre_part_sociale_lib = nbre_part_sociale_lib - '".$DATA['nmbre_part_a_transferer']."' WHERE id_ag = $global_id_agence";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
	}
	
	// Recuperation montant a transferer pour set montant bloquer
	$sql = "SELECT val_nominale_part_sociale FROM ad_agc WHERE id_ag = $global_id_agence";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
	}
	$tmpRow = $result->fetchrow ();
	$val_nominale_part_sociale = $tmpRow [0];
	$Montant = $DATA ['nmbre_part_a_transferer'] * $val_nominale_part_sociale;
	
	// Mise à jour du montant bloqué du compte de parts du client source
	$sql = "UPDATE ad_cpt SET mnt_bloq = mnt_bloq - $Montant WHERE id_ag=$global_id_agence AND id_cpte = ' ".$DATA ['id_cpte_src'] ."'";
	$result=$db->query($sql);
	if (DB::isError($result))
	{
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__); // UPDATE echoué", $result->getMessage()
	}
	
	
	// update ad_part_sociale_his
	$now = date("Y-m-d");
	$sql = "UPDATE ad_transfert_ps_his SET etat_transfert = 2, id_his = $id_his ,date_approb = '$now',id_operation  = 83 WHERE id_ag = $global_id_agence AND id_client_src = '".$DATA['id_client_src']."' AND id = '".$DATA['id_demande']." '";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
	}

	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);

}
/**
 * Historisation de mouvment concernant les parts sociales
 */
function historique_mouvementPs($id_client , $id_his, $type_fonc) {
	global $global_nom_login;
	global  $global_id_agence;
	global $dbHandler;
	global $global_monnaie;
	global $db;

	// Ouverture de transaction
	$db = $dbHandler->openConnection();

	$idProdPS = getPSProductID($global_id_agence);
	//recuperation des données apres les operations passée
	$sql = "SELECT a.id_client, a.qualite, a.nbre_parts, a.nbre_parts_lib,b.solde as solde_ps,b.solde_part_soc_restant as solde_restant FROM ad_cli a ,ad_cpt b
	WHERE a.id_ag = b.id_ag AND b.id_ag = $global_id_agence  AND (b.id_titulaire = a.id_client and a.id_client = $id_client and etat = 2 AND qualite >= 1 and b.id_prod = $idProdPS AND devise='$global_monnaie'
	)";     
	                                                                                                                                                                                                                                                                                                                                                 
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
	}
	$tmpRow = $result->fetchrow ();
	/**
     * *************************************************
	 * MAJ de qualité client Lors de Liberation/Souscription/Transfert PS
	 * aux-> ord
	 * ord-> aux
     * *************************************************
     */
	if (($type_fonc == 28) || ($type_fonc == 23) ||($type_fonc == 20)) {
		// check qualite_client == 1 && nbre_ps_lib >0 :-> on modifie la qualite de auxiliaire -> ordinaire
		if (($tmpRow [1] == 1) && ($tmpRow [3] > 0)) {
			$qualite = 2; // ordinaire
			//MAJ ad_cli
			$sql = "UPDATE  ad_cli SET  qualite = $qualite WHERE (id_ag=$global_id_agence) AND (id_client='$id_client')";
			$result = $db->query ( $sql );
			if (DB::isError ( $result )) {
				$dbHandler->closeConnection ( false );
				signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
			}
			
			// check qualite_client == 2 && nbre_ps_lib ==0 :-> on modifie la qualite de ordinaire -> auxiliaire
		} else if (($tmpRow [1] == 2) && ($tmpRow [3] == 0)) {
			$qualite = 1; // auxiliaire
			//MAJ ad_cli
			$sql = "UPDATE  ad_cli SET  qualite = $qualite WHERE (id_ag=$global_id_agence) AND (id_client='$id_client')";
			$result = $db->query ( $sql );
			if (DB::isError ( $result )) {
				$dbHandler->closeConnection ( false );
				signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
			}
	
		} else { //sinon garde la qualité tel quelle & donc pas de MAJ ad_cli
			$qualite = $tmpRow [1];
		}
	}

//fonction permise :23-transfert,20-souscription,28->liberation
	$INFO_HIS = array();
	//$now = date("Y-m-d");
	$INFO_HIS["date_his"] =  date ( "r" );
	$INFO_HIS["id_client"] = $tmpRow[0];
	$INFO_HIS["qualite"] =  $qualite;
	$INFO_HIS["type_fonc"] = $type_fonc;
	$INFO_HIS["id_his"] =  $id_his;
	$INFO_HIS["nbre_ps_souscrite"] = $tmpRow[2];
	$INFO_HIS["nbre_ps_lib"] = $tmpRow[3];
	$INFO_HIS["solde_ps_lib"] = $tmpRow[4];
	$INFO_HIS["solde_ps_restant"] = $tmpRow[5];
	$INFO_HIS["nom_login"] = $global_nom_login;
	$INFO_HIS["id_ag"] = $global_id_agence;
	
	// insert into ad_part_sociale_his
	$sql = buildInsertQuery("ad_part_sociale_his",$INFO_HIS);

	// Insertion dans la DB
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
	}

	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR);

}

/**
 * Mise en place demande transfert part sociale:
 *
 * Fonction appelée depuis l'interface.
 * @param int $id_client L'identifiant du client souscrivant des parts sociales.
 * @param int $nbre_parts Le nombre de parts à souscrire.
 * @param int $id_utilisateur L'identifiant de l'utilisateur effectuant la transaction (FIXME, n'est-ce pas global_id_login ?)
 * @param int $versement Le montant versé en cas de souscription d'une tranche de part.
 * @return ErrorObj Avec en paramètre le montant souscrit.
 */
function demande_transfert_ps($DATA_DEMANDE) {
	global $global_nom_login;
 global  $global_id_agence;
 // Variables globales
 global $dbHandler;
 global $global_id_agence;
 global $global_monnaie;
 global $db;

 
 // Ouverture de transaction
 $db = $dbHandler->openConnection();
 
	//gestion de transfert vers compte PS
	if($DATA_DEMANDE ["type_transfert"] == 1){
		// Construction de la requête d'insertion
		$DATA = array();
		$now = date("Y-m-d");
		$DATA["date_demande"] = $now;
		
		$DATA["id_client_src"] = $DATA_DEMANDE ["id_client_src"];
		$DATA["id_client_dest"] =$DATA_DEMANDE ["id_client_dest"];
		$DATA["id_cpt_src"] = $DATA_DEMANDE ["id_cpte_src"];
		$DATA["id_cpt_dest"] = $DATA_DEMANDE ["id_cpte_dest"];
		$DATA["num_cpte_src"] =  $DATA_DEMANDE ["num_cpte_src"];
		$DATA["num_cpte_dest"] =  $DATA_DEMANDE ["num_cpte_dest"];
		$DATA["type_transfert"] =  $DATA_DEMANDE ["type_transfert"];
		
		//souscrit/liberer init src
		$DATA["init_nbre_ps_sscrt_src"] = $DATA_DEMANDE ["init_nbre_part_src"];
		$DATA["init_nbre_ps_lib_src"] = $DATA_DEMANDE [ "nmbre_part_init_src_lib"];
		
		$DATA["init_solde_part_src"] = $DATA_DEMANDE [ "init_solde_part_src"];
		$DATA["nbre_part_a_trans"] =  $DATA_DEMANDE ["nmbre_part_a_transferer"];
		
		//souscrit/liberer init dest
		$DATA["init_nbre_ps_sscrt_dest"] = $DATA_DEMANDE ["init_nbr_part_dest"];
		$DATA["init_nbre_ps_lib_dest"] = $DATA_DEMANDE ["nmbre_part_init_dest_lib"];
		$DATA["init_solde_compte_dest"] =  $DATA_DEMANDE ["init_solde_part_dest"];

		//souscrit nouveau
		$DATA["nouv_nbre_ps_sscrt_src"] =  $DATA_DEMANDE ["nouveau_nmbre_part_src"];
		$DATA["nouv_nbre_ps_sscrt_dest"] =  $DATA_DEMANDE ["nouveau_nmbre_part_dest"];
		//liberer nouveau
		$DATA["nouv_nbre_ps_lib_src"] =  $DATA_DEMANDE ["nouveau_nmbre_part_lib_src"];
		$DATA["nouv_nbre_ps_lib_dest"] =  $DATA_DEMANDE ["nouveau_nmbre_part_lib_dest"];
		
		$DATA["nouv_solde_part_src"] =  $DATA_DEMANDE ["nouveau_solde_ps_src"];
		$DATA["nouv_solde_compte_dest"] =  $DATA_DEMANDE ["nouveau_solde_ps_dest"];
	}else{
		// Construction de la requête d'insertion
		$DATA = array();
		$now = date("Y-m-d");
		$DATA["date_demande"] = $now;
		
		$DATA["id_client_src"] = $DATA_DEMANDE ["id_client_src"];
		$DATA["id_client_dest"] =$DATA_DEMANDE ["id_client_dest"];
		$DATA["id_cpt_src"] = $DATA_DEMANDE ["id_cpte_src"];
		$DATA["id_cpt_dest"] = $DATA_DEMANDE ["id_cpte_dest"];
		$DATA["num_cpte_src"] =  $DATA_DEMANDE ["num_cpte_src"];
		$DATA["num_cpte_dest"] =  $DATA_DEMANDE ["num_cpte_dest"];
		$DATA["type_transfert"] =  $DATA_DEMANDE ["type_transfert"];
		
		//souscrit/liberer init src
		$DATA["init_nbre_ps_sscrt_src"] = $DATA_DEMANDE [ "init_nbre_part_src"];
		$DATA["init_nbre_ps_lib_src"] = $DATA_DEMANDE [ "nmbre_part_init_src_lib"];
		
		$DATA["init_solde_part_src"] = $DATA_DEMANDE [ "init_solde_part_src"];
		$DATA["nbre_part_a_trans"] =  $DATA_DEMANDE ["nmbre_part_a_transferer"];
		$DATA["init_solde_compte_dest"] =  $DATA_DEMANDE ["init_solde_part_dest"];

		//nouveau soucrit /liberer
		$DATA["nouv_nbre_ps_sscrt_src"] =  $DATA_DEMANDE ["nouveau_nmbre_part_src"];
		$DATA["nouv_nbre_ps_lib_src"] =  $DATA_DEMANDE ["nouveau_nmbre_part_lib_src"];
		
		$DATA["nouv_solde_part_src"] =  $DATA_DEMANDE ["nouveau_solde_ps_src"];
		$DATA["nouv_solde_compte_dest"] =  $DATA_DEMANDE ["nouveau_solde_ps_dest"];
		
	}
	
	//verification de la traduction
		if(is_trad(($DATA_DEMANDE ["libel_operation"]))){
			$libel_operation_trad = ($DATA_DEMANDE ["libel_operation"]);
			$libel_operation = $libel_operation_trad->save();
		}else{
			
			$libel_operation_trad = new Trad();
			$libel_operation_trad->set_traduction(get_langue_systeme_par_defaut(), ($DATA_DEMANDE ["libel_operation"]));
	
			$libel_operation = $libel_operation_trad->save();
		}
	
	$DATA["libel_operation"] = $libel_operation; // id_str de libel operation
	$DATA[" etat_transfert"] =  1;
	$DATA["id_ag"] = $global_id_agence;

	//$sql = buildInsertQuery("ad_part_sociale_his",$DATA);
	$sql = buildInsertQuery("ad_transfert_ps_his",$DATA);
	// Insertion dans la DB
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}
	
	 $erreur=ajout_historique(22, $DATA["id_client_src"], _("Demande transfert parts sociales "), $global_nom_login, date("r"), NULL);
	if ($erreur->errCode != NO_ERR) {
		$dbHandler->closeConnection(false);
		return $erreur;
	}
	
	$dbHandler->closeConnection(true);
	return $erreur;
	
}

/**
 * Souscription PS
 *
 * Fonction appelée soit par creationClient, soit par souscriptionPartsSocialesInt.
 * @param int $id_client L'identifiant du client souscrivant des parts sociales.
 */
function souscriptionPartsSociales($id_client, $nbre_parts, $id_utilisateur) {
  // Variables globales
  global $dbHandler;
  global $global_id_agence;
  global $global_monnaie;
  global $db;

  // Ouverture de transaction
  $db = $dbHandler->openConnection();

  // Vérification de l'état du client
  $sql = "SELECT etat FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = $id_client;"; // Recherche l'état du client
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $etat = $tmpRow[0];
  if ($etat == 1) {
    // Le client est en attente de validation et, à ce titre, ne peut pas souscrire de parts sociales
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_SPS_ETAT_EAV, $id_client);
  }

  // Récupération de la qualité du client
  $sql = "SELECT qualite FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = $id_client;"; // Recherche la qualité du client
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $qualite = $tmpRow[0];

  // Récupération du numéro de produit associé aux comptes de parts sociales
  $id_prod = getPSProductID($global_id_agence);
  // recuperation des données de l'agence
 	$AG_DATA = getAgenceDatas($global_id_agence);
 	 // verifier le nbre de part sociale max souscripte autorisé pour un client
 	  $nbre_part_sous_param=getNbrePartSoc($id_client);
    $nbre_part_sous=$nbre_part_sous_param->param[0]['nbre_parts']+$nbre_parts;
 	  $nbre_part_max=$AG_DATA['nbre_part_social_max_cli'];
 	  if(($nbre_part_max>0 ) && ($nbre_part_sous > $nbre_part_max) ) {
 	     $dbHandler->closeConnection(false);
 	    return new ErrorObj(ERR_NBRE_MAX_PS, array(_("Nbre de parts sociales max")=>$nbre_part_max));
 	  }

   //verif si client a deja une compte de PS_Evol lot 3
 	 $CheckHasComptePS = getPSAccountID($id_client);
 	 // Le client est auxiliaire et n'as pas un compte - pour effectuer un creation compte ps
 	if (($CheckHasComptePS == NULL)) { // ($qualite == 1) &&
      $id_cpte_base = getBaseAccountID($id_client);

      // Création du compte de parts sociales pour ce client
      // Construction du n° de compte

      $RangCompte = getRangDisponible($id_client);
      $NumCompletCompte = makeNumCpte($id_client, $RangCompte);

      // Construction du tableau utilisé pour la création du compte
      $ACCOUNT["id_cpte"] = getNewAccountID();
      $ACCOUNT["id_titulaire"] = $id_client;
      $ACCOUNT["date_ouvert"] = date("d/m/Y");
      $ACCOUNT["utilis_crea"] = $id_utilisateur;
      $ACCOUNT["etat_cpte"] = 1; // Etat "ouvert"
      $ACCOUNT["solde"] = '0';
      $ACCOUNT["mnt_bloq"] = '0';
      $ACCOUNT["mnt_bloq_cre"] = '0';
      $ACCOUNT["num_cpte"] = $RangCompte;
      $ACCOUNT["num_complet_cpte"] = $NumCompletCompte;
      $ACCOUNT["id_prod"] = $id_prod;
      $ACCOUNT["intitule_compte"] = _("Compte de parts sociales");
      $ACCOUNT["devise"] = $global_monnaie;
      $ACCOUNT["cpt_vers_int"] = $id_cpte_base;
      $ACCOUNT["mnt_min_cpte"] = '0';

      // Création du compte
      if (!creationCompte($ACCOUNT)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Insertion du compte dans la base de données a échoué"
      }

      if ($qualite == 1) {
        // Basculer la qualité à  "ordinaire"
        $sql = "UPDATE ad_cli SET qualite = 2 WHERE id_ag = $global_id_agence AND id_client = $id_client;";
        $result = $db->query($sql);
        if (DB:: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
      }
    }

  // Recherche de l'ID du compte de parts sociales ACTIF pour ce client.
  $sql = "SELECT id_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_titulaire = $id_client AND id_prod = $id_prod AND etat_cpte = 1;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $id_cpte_ps = $tmpRow[0];

  // Recherche de l'ID du compte de base de ce client
  $id_cpte_base = getBaseAccountID($id_client);

  // Recherche du montant à transférer
  $sql = "SELECT val_nominale_part_sociale FROM ad_agc WHERE id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $val_nominale_part_sociale = $tmpRow[0];
  $Montant = $nbre_parts * $val_nominale_part_sociale;
  
  // Augmenter le Capital sociale fictivement souscrite de lagence
  $sql = "UPDATE ad_agc SET capital_sociale_souscrites = capital_sociale_souscrites + $Montant WHERE id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
  	$dbHandler->closeConnection(false);
  	signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
  }

  // Augmenter le nbre de parts sociales de l'agence.
  $sql = "UPDATE ad_agc SET nbre_part_sociale = nbre_part_sociale + $nbre_parts WHERE id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
  }

  // Mise à jour nbre de parts du client
  $sql = "UPDATE ad_cli SET nbre_parts = nbre_parts + $nbre_parts WHERE id_ag = $global_id_agence AND id_client = $id_client";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $Montant);
}

/**
 * Liberation PS pour un client donné
 *
 * Fonction appelée soit par creationClient, soit par souscriptionPartsSocialesInt.
 * @param int $id_client L'identifiant du client souscrivant des parts sociales.
 * @param int $nbre_parts Le nombre de parts à souscrire.
 * @param int $id_utilisateur L'identifiant de l'utilisateur effectuant la transaction (FIXME, n'est-ce pas global_id_login ?)
 * @param array $comptable_his Le tableau des précédents mouvements comptables.
 * @param int $versement Le montant versé en cas de souscription d'une tranche de part.
 * @return ErrorObj Avec en paramètre le montant souscrit.
 */
function liberationPartsSociales($id_client, $nbre_part_liberer, $id_utilisateur, &$comptable_his, $versement = NULL) {
	// Variables globales
	global $dbHandler;
	global $global_id_agence;
	global $global_monnaie;
	global $db;

	// Ouverture de transaction
	$db = $dbHandler->openConnection();

	// Vérification de l'état du client
	$sql = "SELECT etat FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = $id_client;"; // Recherche l'état du client
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$tmpRow = $result->fetchrow();
	$etat = $tmpRow[0];
	if ($etat == 1) {
		// Le client est en attente de validation et, à ce titre, ne peut pas souscrire de parts sociales
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_SPS_ETAT_EAV, $id_client);
	}

	// Récupération de la qualité du client
	$sql = "SELECT qualite FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = $id_client;"; // Recherche la qualité du client
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$tmpRow = $result->fetchrow();
	$qualite = $tmpRow[0];

	// Récupération du numéro de produit associé aux comptes de parts sociales
	$id_prod = getPSProductID($global_id_agence);
	// recuperation des données de l'agence
	$AG_DATA = getAgenceDatas($global_id_agence);
	// verifier le nbre de part sociale max souscripte autorisé pour un client
	$nbre_part_sous_param=getNbrePartSoc($id_client);
	$nbre_part_sous=$nbre_part_sous_param->param[0]['nbre_parts'];
	$nbre_part_max=$AG_DATA['nbre_part_social_max_cli'];
	if(($nbre_part_max>0 ) && ($nbre_part_sous > $nbre_part_max) ) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_NBRE_MAX_PS, array(_("Nbre de parts sociales max")=>$nbre_part_max));
	}
	
	//control nombre part liberer
	$nbre_part_lib = getNbrePartSocLib($id_client);
	
	$nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib'];
	$nbrefinalPSlib = $nbrePSlib + $nbre_part_liberer;
	
	if(($nbrefinalPSlib > 0 ) && ($nbrefinalPSlib > $nbre_part_sous) ) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_NBRE_MAX_PS_LIBERER, array(_("Nombre de PS libérer ne peuvent pas depasser le nombre de PS souscrits ")=>$nbre_part_sous));
	}
	
	// Recherche de l'ID du compte de parts sociales ACTIF pour ce client.
	$sql = "SELECT id_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_titulaire = $id_client AND id_prod = $id_prod AND etat_cpte = 1;";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$tmpRow = $result->fetchrow();
	$id_cpte_ps = $tmpRow[0];

	// Recherche de l'ID du compte de base de ce client
	$id_cpte_base = getBaseAccountID($id_client);

	// Recherche du montant à transférer
	$sql = "SELECT val_nominale_part_sociale FROM ad_agc WHERE id_ag = $global_id_agence";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$tmpRow = $result->fetchrow();
	$val_nominale_part_sociale = $tmpRow[0];
	//verification  tranche part sociale
	if ($AG_DATA ['tranche_part_sociale'] == 't'){
		$Montant = $versement;
	}
	else{	
		$Montant = $nbre_part_liberer * $val_nominale_part_sociale;
	}
	// Débit du compte de base, crédit du compte parts sociales
	$cptes_substitue = array ();
	$cptes_substitue["cpta"] = array ();
	$cptes_substitue["int"] = array ();

	$cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_base);
	if ($cptes_substitue["cpta"]["debit"] == NULL) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
	}

	$cptes_substitue["int"]["debit"] = $id_cpte_base;
	$cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_ps);
	if ($cptes_substitue["cpta"]["credit"] == NULL) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
	}

	$cptes_substitue["int"]["credit"] = $id_cpte_ps;

	//on récupère mouvements pour l'historique
	$myErr = passageEcrituresComptablesAuto(80, $Montant, $comptable_his, $cptes_substitue);
	if ($myErr->errCode != NO_ERR) {
		$dbHandler->closeConnection(true);
		return $myErr;
	}

	// Augmenter le capital  parts sociales liberer de l'agence.
	$sql = "UPDATE ad_agc SET capital_sociale_lib = capital_sociale_lib + $Montant WHERE id_ag = $global_id_agence";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
	}
	// Augmenter le nbre de parts sociales  liberer de l'agence.
	$sql = "UPDATE ad_agc SET nbre_part_sociale_lib = nbre_part_sociale_lib + $nbre_part_liberer WHERE id_ag = $global_id_agence";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
	}

	// Mise à jour nbre de parts liberer du client
	$sql = "UPDATE ad_cli SET nbre_parts_lib = nbre_parts_lib + $nbre_part_liberer WHERE id_ag = $global_id_agence AND id_client = $id_client";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
	}

	// Mise à jour du montant bloqué du compte de parts du client
	$sql = "UPDATE ad_cpt SET mnt_bloq = mnt_bloq + $Montant WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte_ps";
	$result=$db->query($sql);
	if (DB::isError($result))
	{
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__); // UPDATE echoué", $result->getMessage()
	}

	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR, $Montant);
}


/**
 * Transfert part sociale vers part sociale
 *
 * Fonction appelée lor de l'approbation d'un transfert de parts sociale, 
 * @param int $id_client L'identifiant du client souscrivant des parts sociales.
 * @param int $nbre_parts Le nombre de parts à souscrire.
 * @param int $id_utilisateur L'identifiant de l'utilisateur effectuant la transaction (FIXME, n'est-ce pas global_id_login ?)
 * @param array $comptable_his Le tableau des précédents mouvements comptables.
 * @param int $versement Le montant versé en cas de souscription d'une tranche de part.
 * @return ErrorObj Avec en paramètre le montant souscrit.
 */
function transfert_ps_ps($DATA, $id_utilisateur , &$comptable_his) {
	global $dbHandler;
	global $global_id_agence;
	global $global_monnaie;
	global $db;
	
	// Ouverture de transaction
	$db = $dbHandler->openConnection ();
	
	// Vérification de l'état du client_src
	$sql = "SELECT etat FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = '" . $DATA ['id_client_src'] . "';"; // Recherche l'état du client
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
	}
	$tmpRow = $result->fetchrow ();
	$etat = $tmpRow [0];
	if ($etat == 1) {
		// Le client est en attente de validation et, à ce titre, ne peut pas transfert de PS
		$dbHandler->closeConnection ( false );
		return new ErrorObj ( ERR_SPS_ETAT_EAV, $DATA ['id_client_src'] );
	}
	
	// Vérification de l'état du client_dest
	$sql = "SELECT etat FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = '" . $DATA ['id_client_dest'] . "';"; // Recherche l'état du client
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
	}
	$tmpRow = $result->fetchrow ();
	$etat = $tmpRow [0];
	
	if ($etat == 1) { // etat dois normalement etre 2
	                  // Le client est en attente de validation et, à ce titre, ne peut pas transfert de PS
		$dbHandler->closeConnection ( false );
		return new ErrorObj ( ERR_SPS_ETAT_EAV, $DATA ['id_client_dest'] );
	}
	
	
	// Récupération du numéro de produit associé aux comptes de parts sociales
	$id_prod = getPSProductID ( $global_id_agence );
	// recuperation des données de l'agence
	$AG_DATA = getAgenceDatas ( $global_id_agence );
	
	
	// verifier le nbre de part sociale max souscripte autorisé pour le client dest
	$nbre_part_sous_param=getNbrePartSoc($DATA['id_client_dest']);
	$nbre_part_sous=$nbre_part_sous_param->param[0]['nbre_parts']+$DATA['nmbre_part_a_transferer']  ;
	$nbre_part_max=$AG_DATA['nbre_part_social_max_cli'];
	
	if(($nbre_part_max>0 ) && ($nbre_part_sous > $nbre_part_max) ) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_NBRE_MAX_PS, array(_("Nbre de parts sociales max")=>$nbre_part_max));
	}
		
		// Recherche du montant à transférer
	$sql = "SELECT val_nominale_part_sociale FROM ad_agc WHERE id_ag = $global_id_agence";
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
	}
	$tmpRow = $result->fetchrow ();
	$val_nominale_part_sociale = $tmpRow [0];
	$Montant = $DATA ['nmbre_part_a_transferer'] * $val_nominale_part_sociale;
	
	/**
	 * ******************** logique de transfert PS to PS******************
	 * retire le montant du compte comptable associe au compte PS src
	 * ---- retirer Montant de compte de PS client src
	 * transferer le montant du compte comptable associe au compte PS dest
	 * ---- transferer Montant dans le compte PS de client dest
	 */
	
	// Le transfert de part sociale PS- PS
	$cptes_substitue = array ();
	$cptes_substitue ["cpta"] = array ();
	$cptes_substitue ["int"] = array ();
	
	// debiter le compte comptable associé pour le compte pS source
	$cptes_substitue ["cpta"] ["debit"] = getCompteCptaProdEp ( $DATA ['id_cpte_src'] );
	
	if ($cptes_substitue ["cpta"] ["debit"] == NULL) {
		$dbHandler->closeConnection ( false );
		return new ErrorObj ( ERR_CPTE_NON_PARAM, _ ( "compte comptable associé au produit d'épargne" ) );
	}
	// debiter le compte part sociale du client source
	$cptes_substitue ["int"] ["debit"] = $DATA ['id_cpte_src'];
	
	// crediter le compte comptable associé pour le compte pS destinataire
	$cptes_substitue ["cpta"] ["credit"] = getCompteCptaProdEp ( $DATA ['id_cpte_dest'] );
	
	if ($cptes_substitue ["cpta"] ["credit"] == NULL) {
		$dbHandler->closeConnection ( false );
		return new ErrorObj ( ERR_CPTE_NON_PARAM, _ ( "compte comptable associé au produit d'épargne " ) );
	}
	// crediter le compte part sociale du client dest
	$cptes_substitue ["int"] ["credit"] = $DATA ['id_cpte_dest'];
	
	// on récupère mouvements pour l'historique
	$myErr = passageEcrituresComptablesAuto ( 82, $Montant, $comptable_his, $cptes_substitue );
	
	if ($myErr->errCode != NO_ERR) {
		$dbHandler->closeConnection ( false );
		return $myErr;
	}
	
	$dbHandler->closeConnection ( true );
	return new ErrorObj ( NO_ERR, $Montant );
}



/**
 * Transfert part sociale vers courant
 *
 * Fonction appelée lor de l'approbation d'un transfert de parts sociale,
 * @param int $id_client L'identifiant du client souscrivant des parts sociales.
 * @param int $nbre_parts Le nombre de parts à souscrire.
 * @param int $id_utilisateur L'identifiant de l'utilisateur effectuant la transaction (FIXME, n'est-ce pas global_id_login ?)
 * @param array $comptable_his Le tableau des précédents mouvements comptables.
 * @param int $versement Le montant versé en cas de souscription d'une tranche de part.
 * @return ErrorObj Avec en paramètre le montant souscrit.
 */
function transfert_ps_courant($DATA, $id_utilisateur , &$comptable_his) {
	// Variables globales
	global $dbHandler;
	global $global_id_agence;
	global $global_monnaie;
	global $db;

	// Ouverture de transaction
	$db = $dbHandler->openConnection();

	// Vérification de l'état du client_src
	$sql = "SELECT etat FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = '".$DATA['id_client_src']."';"; // Recherche l'état du client
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$tmpRow = $result->fetchrow();
	$etat = $tmpRow[0];
	if ($etat == 1) {
		// Le client est en attente de validation et, à ce titre, ne peut pas transfert de PS
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_SPS_ETAT_EAV, $DATA['id_client_src']);
	}

	// Vérification de l'état du client_dest
	$sql = "SELECT etat FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = '".$DATA['id_client_dest']."';"; // Recherche l'état du client
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$tmpRow = $result->fetchrow();
	$etat = $tmpRow[0];

	if ($etat == 1) {//etat dois normalement etre 2
		// Le client est en attente de validation et, à ce titre, ne peut pas transfert de PS
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_SPS_ETAT_EAV, $DATA['id_client_dest']);
	}

	// Récupération du numéro de produit associé aux comptes de parts sociales
	$id_prod = getPSProductID($global_id_agence);
	// recuperation des données de l'agence
	$AG_DATA = getAgenceDatas($global_id_agence);

	// verifier le nbre de part sociale max souscripte autorisé pour le client dest
	$nbre_part_max=$AG_DATA['nbre_part_social_max_cli'];

	// Recherche du montant à transférer
	$sql = "SELECT val_nominale_part_sociale FROM ad_agc WHERE id_ag = $global_id_agence";
	$result = $db->query($sql);
	if (DB :: isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$tmpRow = $result->fetchrow();
	$val_nominale_part_sociale = $tmpRow[0];
	$Montant = $DATA['nmbre_part_a_transferer'] * $val_nominale_part_sociale;



	/**********************  logique de transfert PS to courant******************
	 * retirer le montant du compte comptable associe au compte PS src
	 *  ---- retirer Montant de compte de PS client src
	 *  transferer le montant du compte comptable associe au compte PS dest
	 *  ---- transferer Montant dans le compte PS de client dest
	*/

	//Le transfert de part sociale PS- PS
	$cptes_substitue = array ();
	$cptes_substitue["cpta"] = array ();
	$cptes_substitue["int"] = array ();
	 
	//debiter le compte comptable associé pour le compte pS source
	$cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($DATA['id_cpte_src']);
	if ($cptes_substitue["cpta"]["debit"] == NULL) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
	}
	//debiter le compte part sociale du client source
	$cptes_substitue["int"]["debit"] = $DATA['id_cpte_src'];
	
	//crediter le compte comptable associé pour le compte pS destinataire
	$cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($DATA['id_cpte_dest']);
	
	if ($cptes_substitue["cpta"]["credit"] == NULL) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne "));
	}
	// crediter le compte part sociale du client dest
	$cptes_substitue["int"]["credit"] = $DATA['id_cpte_dest'];

	//on récupère mouvements pour l'historique 
	$myErr = passageEcrituresComptablesAuto(83, $Montant, $comptable_his, $cptes_substitue);
	if ($myErr->errCode != NO_ERR) {
		$dbHandler->closeConnection(false);
		return $myErr;
	}

	$dbHandler->closeConnection(true);
	return new ErrorObj(NO_ERR, $Montant);
}


/**
 *  * Souscription d'un nombre de parts sociales  pour un client donné, en pricisant la source des fonds
 *
 * Fonction appelée .
 * @param int $id_client L'identifiant du client souscrivant des parts sociales.
 * @param int $nbre_parts Le nombre de parts à souscrire.
 * @param int $id_utilisateur L'identifiant de l'utilisateur effectuant la transaction (FIXME, n'est-ce pas global_id_login ?)
 * @param array $comptable_his Le tableau des précédents mouvements comptables.
 * @param double $versement Le montant versé en cas de souscription d'une tranche de part.
 * @param int $source  la source de fonds pour la souscription des parts sociales.
 *                   	1=la banque
 *                  	2=le guichet
 *                  	3=le compte de base
 * @param int $id_source  l'identifiant de la source de fonds.
 * @return ErrorObj Avec en paramètre le montant souscrit.
 */
function souscriptionPartSocialeSource($id_client, $nbre_parts, $id_utilisateur, &$comptable_his, $versement = NULL,$source,$id_source) {
  // Variables globales
  global $dbHandler;
  global $global_id_agence;
  global $global_monnaie;
  global $db;

  // Ouverture de transaction
  $db = $dbHandler->openConnection();
  // Vérification de l'état du client
  $sql = "SELECT etat FROM ad_cli WHERE  id_client = $id_client;"; // Recherche l'état du client
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $etat = $tmpRow[0];
  if ($etat == 1) {
      // Le client est en attente de validation et, à ce titre, ne peut pas souscrire de parts sociales
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_SPS_ETAT_EAV,array("ID client"=> $id_client));
  }

  // Récupération de la qualité du client
  $sql = "SELECT qualite FROM ad_cli WHERE  id_client = $id_client;"; // Recherche la qualité du client
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $qualite = $tmpRow[0];

  // Récupération du numéro de produit associé aux comptes de parts sociales
  $id_prod = getPSProductID($global_id_agence);
   // recuperation des données de l'agence
 	$AG_DATA = getAgenceDatas($global_id_agence);
 	// verifier le nbre de part sociale max souscripte autorisé pour un client
 	  $nbre_part_sous_param=getNbrePartSoc($id_client);
    $nbre_part_sous=$nbre_part_sous_param->param[0]['nbre_parts']+$nbre_parts;
 	  $nbre_part_max=$AG_DATA['nbre_part_social_max_cli'];
 	  if(($nbre_part_max>0 ) && ($nbre_part_sous > $nbre_part_max) ) {
 	     $dbHandler->closeConnection(false);
 	    return new ErrorObj(ERR_NBRE_MAX_PS, array(_("Nbre de parts sociales max")=>$nbre_part_max));
 	  }

  if ($qualite == 1) { // Le client est auxiliaire
    $id_cpte_base = getBaseAccountID($id_client);

    // Création du compte de parts sociales pour ce client
    // Construction du n° de compte

    $RangCompte = getRangDisponible($id_client);
    $NumCompletCompte = makeNumCpte($id_client, $RangCompte);

    // Construction du tableau utilisé pour la création du compte
    $ACCOUNT["id_cpte"] = getNewAccountID();
    $ACCOUNT["id_titulaire"] = $id_client;
    $ACCOUNT["date_ouvert"] = date("d/m/Y");
    $ACCOUNT["utilis_crea"] = $id_utilisateur;
    $ACCOUNT["etat_cpte"] = 1; // Etat "ouvert"
    $ACCOUNT["solde"] = '0';
    $ACCOUNT["mnt_bloq"] = '0';
    $ACCOUNT["mnt_bloq_cre"] = '0';
    $ACCOUNT["num_cpte"] = $RangCompte;
    $ACCOUNT["num_complet_cpte"] = $NumCompletCompte;
    $ACCOUNT["id_prod"] = $id_prod;
    $ACCOUNT["intitule_compte"] = _("Compte de parts sociales");
    $ACCOUNT["devise"] = $global_monnaie;
    $ACCOUNT["cpt_vers_int"] = $id_cpte_base;
    $ACCOUNT["mnt_min_cpte"] = '0';

    // Création du compte
    if (!creationCompte($ACCOUNT)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Insertion du compte dans la base de données a échoué"
    }

    // Basculer la qualité à  "ordinaire"
    $sql = "UPDATE ad_cli SET qualite = 2 WHERE id_client = $id_client;";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
  }

  // Recherche de l'ID du compte de parts sociales pour ce client, recuperation du compte PS actif seulement.
  $sql = "SELECT id_cpte FROM ad_cpt WHERE id_titulaire = $id_client AND id_prod = $id_prod AND etat_cpte = 1;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $id_cpte_ps = $tmpRow[0];



  // Recherche du montant à transférer
  $sql = "SELECT val_nominale_part_sociale FROM ad_agc WHERE id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();
  $val_nominale_part_sociale = $tmpRow[0];
  $Montant = $nbre_parts * $val_nominale_part_sociale;

  if ($versement > 0) {
    $Montant = $versement;
  }
  // Débit du compte de la source, crédit du compte parts sociales
  $cptes_substitue = array ();
  $cptes_substitue["cpta"] = array ();
  $cptes_substitue["int"] = array ();

  if ($source==2){
  $comptesCompensation = getComptesCompensation($id_source);
  $cptes_substitue["cpta"]["debit"] = $comptesCompensation['compte'];
  }
  elseif($source==1) {
  	//débit du compte guichet , crédit du compte parts sociales
  $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_source);

  /* Arrondi du montant si paiement au guichet*/
  $critere = array();
  $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
  $cpte_gui = getComptesComptables($critere);
  $Montant = arrondiMonnaie( $Montant, 0, $cpte_gui['devise'] );

  }elseif($source==3){
  	// Recherche de l'ID du compte de base de ce client
  	$id_cpte_base = getBaseAccountID($id_client);
  	$cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_base);
	  if ($cptes_substitue["cpta"]["debit"] == NULL) {
	    $dbHandler->closeConnection(false);
	    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
	  }

  	$cptes_substitue["int"]["debit"] = $id_cpte_base;

  }else{
      $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé à la source de fond"));
  }

  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_ps);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["credit"] = $id_cpte_ps;

  //on récupère mouvements pour l'historique
  $myErr = passageEcrituresComptablesAuto(80, $Montant, $comptable_his, $cptes_substitue);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(true);
    return $myErr;
  }

  // Augmenter le nbre de parts sociales du client et de l'agence.
  $sql = "UPDATE ad_agc SET nbre_part_sociale = nbre_part_sociale + $nbre_parts WHERE id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
  }

  // Mise à jour nbre de parts du client
  $sql = "UPDATE ad_cli SET nbre_parts = nbre_parts + $nbre_parts WHERE id_client = $id_client";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__); // UPDATE echoué", $result->getMessage()
  }

  // Mise à jour du montant bloqué du compte de parts du client
  $sql = "UPDATE ad_cpt SET mnt_bloq = mnt_bloq + $nbre_parts*$val_nominale_part_sociale WHERE  id_cpte = $id_cpte_ps";
  $result=$db->query($sql);
  if (DB::isError($result))
    {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // UPDATE echoué", $result->getMessage()
    }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $Montant);
}

/**
 * Récupère le solde du compte des parts sociales d'un client
 * @param int $id_client N° du client concerné
 * @return ErrorObj Objet Erreur
 * @author Saourou MBODJ
 * @since 2.7
 */
function getSoldePartSoc($id_client) {
  global $global_id_agence;
  //Compte de parts sociales
  $requete="SELECT solde,solde_part_soc_restant from ad_cpt where id_ag=$global_id_agence AND id_titulaire='$id_client' and id_prod = 2 AND etat_cpte = 1;";
  
  return executeDirectQuery($requete);

}
/**
 * Met à jour le solde restant à payer du compte des parts sociales d'un client
 * @param int $id_client N° du client concerné
 * @param $nouveau_solde c'est le nouveau solde à mettre à jour
 * @return ErrorObj Objet Erreur
 * @author Saourou MBODJ
 * @since 2.7
 */
function updateSodeRestantPartSoc($id_client, $nouveau_solde) {

  global $global_id_agence;
  //Compte de parts sociales // Compte actif seulement
  $requete="UPDATE  ad_cpt SET solde_part_soc_restant='$nouveau_solde'  where id_ag=$global_id_agence AND id_titulaire='$id_client' and id_prod = 2 AND etat_cpte = 1;";
  return executeDirectQuery($requete);

}

/**
 * Récupère le nombre de parts sociales souscrites d'un client
 * @param int $id_client N° du client concerné
 * @return ErrorObj Objet Erreur
 * @author Djibril NIANG
 * @since 3.0
 */
function getNbrePartSoc($id_client) {
  global $global_id_agence;

  $requete = "SELECT nbre_parts from ad_cli where id_ag = $global_id_agence AND id_client = '$id_client' ";
  return executeDirectQuery($requete);

}
/**
 * Récupère le nombre de parts sociales liberer
 * @param int $id_client N° du client concerné
 * @return ErrorObj Objet Erreur
 * @author 
 */
function getNbrePartSocLib($id_client) {
	global $global_id_agence;

	$requete = "SELECT nbre_parts_lib from ad_cli where id_ag = $global_id_agence AND id_client = '$id_client' ";
	return executeDirectQuery($requete);

}
/**
 * controle souscription au niveau de l'agence
 * @param 
 * @return y for souscription illimité/ x for non-autorise / integer > 0 for souscription limité
 * @author 361
 * @since 2015
 */
function checkSouscription() {
	global $global_id_agence;

	$AGC = getAgenceDatas ( $global_id_agence );
	$val_nominalePS = $AGC ["val_nominale_part_sociale"];
	$cap_autorise = $AGC ["capital_sociale_autorise"];
	$cap_souscrite = $AGC ["capital_sociale_souscrites"];

	$nbre_ps_autorise = floor($cap_autorise / $val_nominalePS);
	$nbre_ps_souscrite_actuellement = floor($cap_souscrite / $val_nominalePS);
	
	$restant_a_souscrire = $nbre_ps_autorise -$nbre_ps_souscrite_actuellement;
	 
	if ($cap_autorise == 0) {
		return y; // souscription illimité
	} 
	else if (($cap_autorise > 0) && ($nbre_ps_souscrite_actuellement >= $nbre_ps_autorise)) {
		return x; // souscription non-autorisé
	} else {
		return $restant_a_souscrire; // souscription limité
	}	
		
}

/**
 * determine le nbre de part transferable basant sur le solde ps et la valeur nominale de ps (agence)
 * @param int $id_client N° du client concerné
 * @return nmbre part transferable ou 0 if pas de part transferable
 * @author 361
 * @since 2015
 * FOnction non utilisé- mais reutilisable
 */
function determineNbrePsTransferable($id_client) {
	global $global_id_agence;
	
	$nbre_part = getNbrePartSoc ( $id_client );
	$nbrePS = $nbre_part->param [0] ['nbre_parts'];
	$SOLDE_PART_SOC = getSoldePartSoc ( $id_client ); // returns an object
	$soldePS = $SOLDE_PART_SOC->param [0] ['solde']; // object passed to variables
	$AGC = getAgenceDatas ( $global_id_agence );
	$val_nominalePS = $AGC ["val_nominale_part_sociale"];
	
	$nbre_ps_transferable_division = ($soldePS / $val_nominalePS);
	$nbre_ps_transferable = floor ( $nbre_ps_transferable_division );
	
	if (($nbre_ps_transferable >= 1) && ($nbre_ps_transferable <= $nbrePS)) {
		return $nbre_ps_transferable;
	} else {
		return 0;
	}
}



/**
 * Récupère le solde restant des frais d'adhésion d'un client
 * @param int $id_client N° du client concerné
 * @return ErrorObj Objet Erreur
 * @author Djibril NIANG
 * @since 2.9
 */
function getSoldeFraisAdhesion($id_client) {
  global $adsys,$global_id_agence;
  //Compte de parts sociales
  //$intitule=$adsys["adsys_categorie_compte"][9];

  $requete = "SELECT solde_frais_adhesion_restant from ad_cli where id_ag=$global_id_agence AND id_client='$id_client' "; //and intitule_compte LIKE '$intitule'";
  return executeDirectQuery($requete);

}
/**
 * Met à jour le solde restant à payer des frais d'adhésion d'un client
 * @param int $id_client N° du client concerné
 * @param $nouveau_solde c'est le nouveau solde à mettre à jour
 * @return ErrorObj Objet Erreur
 * @author Djibril NIANG
 * @since 2.9
 */
function updateSodeRestantFraisAdhesion($id_client, $nouveau_solde) {

  global $adsys,$global_id_agence;
  //Compte de parts sociales
  //$intitule=$adsys["adsys_categorie_compte"][9];

  $requete = "UPDATE  ad_cli SET solde_frais_adhesion_restant='$nouveau_solde'  where id_ag=$global_id_agence AND id_client='$id_client' "; //and intitule_compte LIKE '$intitule'";
  return executeDirectQuery($requete);

}

/**
 * Enregistre le décès d'un client
 *  - Passage du client à l'état "En attente enregistrement décès"
 *  - Blocage de tous les comptes
 * @param int $id_client N° du client qui décède
 * @return ErrorObj Objet Erreur
 * @author Thomas Fastenakel
 * @since 1.0
 */
function clientDecede($id_client) {
  //$CPTS = getAccounts($id_client);
  // Mise à jour de l'état du client à 'en attente enregistrement décès'
  $Field = array (
             "etat" => 7
           );
  updateClient($id_client, $Field);
  //On ne bloque pas les comptes d'un client décédé, car d'aprés le ticket 383
  //while (list ($key,) = each($CPTS))
    //blocageCompteInconditionnel($key);
  global $global_nom_login;
  $myErr = ajout_historique(15, $id_client, NULL, $global_nom_login, date("r"), NULL);
  if ($myErr->errCode != NO_ERR)
    return $myErr;

  return new ErrorObj(NO_ERR);
}

/**
 * Crée un nouveau client dans la DB
 * Appelée à partir de l'interface de création d'un nouveau client
 * FIXME : revoir les arguments de cette fonction, il y en a trop et qui sont contingents
 * Les taches sont
 *  - insertion du client dans la DB
 *  - insertion de ses relations
 *  - création du compte de base
 *  - versement initial
 *  - perception des frais d'adhésion (si le client paye tout de suite et si pas de transfert depuis une autre agence)
 *  - souscription des parts sociales (si applicable)
 * @param Array $DATA_CLI Données sur le client
 * @param Array $DATA_CPT Informations sur le compte de base (à supprimer)
 * @param int $paye  0 => Le client payera plus tard, son compte de base est bloqué
 *                   1 => Le client paye uniquement les frais d'adhésion, il devient auxiliaire
 *                   2 => Le client paye les frais d'adhésion et souscrit au parts sociales
 * @param int $ouvre_cpt_base : 1 si le client utilise le compte de base, 0 sinon
 * @param float $versement Montant du versement initial (en devise de référence)
 * @param int $nbre_parts Nombre de PS souscrites
 * @param int $id_guihet ID du guichet de l'utilisateur ayant encaissé l'argent
 * @param int $transfert_client Deprecated
 * @param int $banque Deprecated
 * @param float $mnt_droits_adhesion Montant des droits d'adhésion (si ceux-ci ont été modifiés par l'utilisateur)
 * @param int $nbr_membres_gs Nombre de membres du groupe solidaire
 * @return ErrorObj Objet rreur
 */

function creationClient($DATA_CLI, $DATA_CPT, $paye, $ouvre_cpt_base, $versement, $nbre_parts, $nbre_parts_lib, $somme, $id_guichet, $transfert_client, $data_ext, $banque = NULL, $mnt_droits_adhesion = NULL, $nbr_membres_gs = 0, $etat_cli_bloc = null) {
  global $global_id_agence;
  global $global_id_client;
  global $global_etat_client;
  global $global_id_utilisateur;
  global $dbHandler;

  $db = $dbHandler->openConnection();

  $comptable_his = array ();

  $AG_DATA = getAgenceDatas($global_id_agence);
  // Verification de l'existence des membres du groupe solidaire
  if(empty($etat_cli_bloc) && !$etat_cli_bloc) {
      if ($DATA_CLI["statut_juridique"] == 4 && $nbr_membres_gs > 0) {
          $membres_gs = array ();
          $nbr_membres_enregistres = 0;
          for ($i = 1; $i <= $nbr_membres_gs; ++ $i) {
              // Si nous créons un groupe solidaire, il faut vérifier l'existence des membres (num_client$i)
              $num_client = $DATA_CLI["num_client$i"];
              unset ($DATA_CLI["num_client$i"]);
              if ($num_client != "") {
                  if (!client_exist($num_client)) {
                      $dbHandler->closeConnection(false);
                      return new ErrorObj(ERR_CLIENT_INEXISTANT, sprintf(_("Pour un des membres du groupe solidaire (id_client = %s)."), $num_client));
                  }
                  // et introduire les données nécessaire dans ad_grp_sol
                  $membres_gs["id_grp_sol"] = $DATA_CLI["id_client"];
                  $membres_gs["id_membre"] = $num_client;
                  $membres_gs["id_ag"] = $global_id_agence;
                  $result = executeQuery($db, buildInsertQuery("ad_grp_sol", $membres_gs));
                  if ($result->errCode != NO_ERR) {
                      $dbHandler->closeConnection(false);
                      return $result;
                  } else {
                      $nbr_membres_enregistres++;
                  }
              }
          }
          $DATA_CLI["gi_nbre_membr"] = $nbr_membres_enregistres;
      }
  }

  // Insertion du client dans la table ad_cli, des relations et création du compte de base
  if(!empty($etat_cli_bloc) && $etat_cli_bloc){
      $myErr = updateCliBloc($DATA_CLI, $DATA_CPT, $ouvre_cpt_base);
      if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
      }
  }else {
      $myErr = insere_client($DATA_CLI, $DATA_CPT, $ouvre_cpt_base);
      if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
      }
  }
  
  $id_cpte_base = $myErr->param;
  $DATA_CLI["id_cpte_base"] = $id_cpte_base;
  $numcpt = getBaseAccountID($DATA_CLI["id_client"]);
  //FIXME : changer l'appel de f°
  $PROD = getProdEpargne(getBaseProductID($global_id_agence));
  $mnt_min_cpt_base = $PROD['mnt_min'];
	
	
  if($mnt_droits_adhesion==NULL){
  $montant_frais_adhesion = getMontantDroitsAdhesion($DATA_CLI["statut_juridique"]);
  }else{
  	$montant_frais_adhesion=$mnt_droits_adhesion;
  }
  
  /**
   * *************************************************
   * EVOLUTION SOUSCRIPTION & LIBERATION
   * Option :Paye frais adhesion et PS
   * Tranche parts sociale TRUE NON utiliser ici
   * *************************************************
   */
  if ($ouvre_cpt_base == 1) {
  	if ($paye == 2) {
  		if ($AG_DATA ["tranche_part_sociale"] == "t") {
  			$mnt_droits_adhesion = $montant_frais_adhesion ;
  			
  			$nbre_ps_sous =$nbre_parts ;
  			$montant_souscription = $nbre_parts * $AG_DATA["val_nominale_part_sociale"];
  			$nbre_ps_lib =$nbre_parts_lib ;
  			$montant_liberation_tranche = $somme; //montant par tranche
  			$montant_part_soc_restant = $montant_souscription - $montant_liberation_tranche;
  			
  			$versement_min= $mnt_min_cpt_base + $montant_frais_adhesion + $montant_liberation_tranche;
  			
  		}else{
  			$mnt_droits_adhesion = $montant_frais_adhesion ;
  		
  			$nbre_ps_sous =$nbre_parts ;
  			$montant_souscription = $nbre_parts * $AG_DATA["val_nominale_part_sociale"];
  			$nbre_ps_lib =$nbre_parts_lib ;
  			$montant_liberation = $somme; //montant complete
  			$montant_part_soc_restant = $montant_souscription - $montant_liberation;
  			
  			$versement_min= $mnt_min_cpt_base + $montant_frais_adhesion + $montant_liberation;
  		}
  	}else  if ($paye == 1) {
  		$mnt_droits_adhesion = $montant_frais_adhesion ;
  		$versement_min = $mnt_min_cpt_base + $montant_frais_adhesion ;
  	}
  		
  }//fin ouvre compte de base =1
  

  if ($paye != 0) { // Il y a quelque chose à payer
    //Versement initial
    if ($versement > 0) {
      $myErr = versementInitial($DATA_CLI["id_client"], $id_guichet, $versement, $comptable_his, $transfert_client, $banque);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }

    //perception de frais d'adhesion'
    if ($transfert_client == false && $mnt_droits_adhesion > 0) { // frais d'adhésion pour les clients non transférés
      $myErr = perceptionFraisAdhesion($DATA_CLI["id_client"], $comptable_his, $mnt_droits_adhesion);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    } else {
      // Basculer l'état du client à "auxiliaire" sans perception des frais pour le transfert client
      $sql = "UPDATE ad_cli SET etat = 2 WHERE id_ag=$global_id_agence AND id_client = " . $DATA_CLI["id_client"] . ";";
      $result = $db->query($sql);
      if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
      }
    }
		
		
	} //fin paye 2
    
 //fin il y a quelque chose à payer
  else {
    // Le client est en cours de validation, il n'a pas le droit d'accéder à son compte de base.
    $num_cpte_base = getBaseAccountID($DATA_CLI['id_client']);
    blocageCompteInconditionnel($num_cpte_base);
  }

  if ($data_ext != NULL) {
    $data_his_ext = creationHistoriqueExterieur($data_ext);
  } else {
    $data_his_ext = NULL;
  }
  
  // Ajout dans l'historique
  global $global_nom_login;
 
  $myErr = ajout_historique(30, $DATA_CLI["id_client"], "", $global_nom_login, date("r"), $comptable_his, $data_his_ext);

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  
  $id_his = $myErr->param;

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $id_his);
}

/**
 * 	PS permettant de réaliser le transfer d'un client entre agence sans que des frais soit pris
 * 	On ne fait aucune vérification spéciale puisqu'il s'agit de fermer les comptes du client et de passer une écriture de régul
 *
 * @param Integer $id_client :
 * @param Integer $id_bqe
 * @param Integer $etat
 * @param String $raison_defection : Texte descriptive de ma raison dela déféction
 * @return ErrorObj
 * 						- NO_ERR et numéro d'historique si tous se passe bien
 * 						- ERR_CPTE_NON_PARAM si compte comptable associé au produit d'épargne"
 * 						- erreur retournée par {@see passageEcrituresComptablesAuto}
 * 						- erreur retournée par {@see ajout_historique}
 * 						- SignalErreur si problème SQL
 *
 * @todo FIXME : faire le contrôle pour les comptes bloqués
 */
function lanceDefectionTransfert($id_client, $id_bqe, $etat, $raison_defection = 'N/A') {
  global $dbHandler;
  global $global_id_agence;
  global $global_nom_login;

  $db = $dbHandler->openConnection();

  // Le cas d'un client EAV est un peu particulier, il n'y a aucune opération financière à effectuer
  $CLI = getClientDatas($id_client);
  if ($CLI["etat"] != 1) {
    // Défection client EAV : pas de fermeture des comptes

    //fermeture des comptes

    $comptable_his = array ();

    $myErr = fermeComptesEP($id_client, $comptable_his);
    if ($myErr->errCode != NO_ERR)
      return $myErr;

    //fermer le  compte de base
    $id_cpte_base = getBaseAccountID($id_client);
    $InfosCpteBase = getAccountDatas($id_cpte_base);

    if ($InfosCpteBase["solde"] > 0) {
      // Passage des écritures comptables
      $cptes_substitue = array ();
      $cptes_substitue["cpta"] = array ();
      $cptes_substitue["int"] = array ();

      //Compte au debit
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_base);
      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }

      $cptes_substitue["int"]["debit"] = $id_cpte_base;

      //Compte au credit
      $Infobanque = getInfosBanque($id_bqe);
      $cpte_banque = $Infobanque[$id_bqe]["cpte_cpta_bqe"];
      $cptes_substitue["cpta"]["credit"] = $cpte_banque;

      $myErr = passageEcrituresComptablesAuto(421, $InfosCpteBase["solde"], $comptable_his, $cptes_substitue);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(true);
        return $myErr;
      }
    }

    //màj solde cloture, date cloture, etat compte, raison cloture
    $sql = "UPDATE ad_cpt SET date_clot = '" . date("d/m/Y") . "', solde_clot = " . $InfosCpteBase["solde"] . ", ";
    $sql .= " etat_cpte=2, raison_clot=1 ";
    $sql .= "WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte_base;";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
    }

  } //end fermeture des comptes

  // Défection du client
  $sql = "UPDATE ad_cli SET etat = $etat, nbre_parts = 0, raison_defection = '$raison_defection', date_defection = '" . date("d/m/Y") . "' WHERE id_ag=$global_id_agence AND id_client = $id_client;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  global $global_nom_login;

  $myErr = ajout_historique(15, $id_client, NULL, $global_nom_login, date("r"), $comptable_his);

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(true);
    return $myErr;
  }

  $id_his = $myErr->param;
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $id_his);

}

/**
 * Ajouter une personne extérieure dans la base de donnée
 * @author Antoine Guyette
 * @param Array $DATA données sur la personne extérieure
 * @return ErrorObj
 */
function ajouterPersonneExt($DATA) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $IMAGES = array (
              'photo' => $DATA['photo'],
              'signature' => $DATA['signature'],
              'id_ag' => $global_id_agence
            );
  unset ($DATA['photo']);
  unset ($DATA['signature']);
  $DATA['id_ag']=$global_id_agence;
  $sql = buildInsertQuery('ad_pers_ext', $DATA);

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $sql = "select currval('ad_pers_ext_id_pers_ext_seq')";

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $row = $result->fetchrow();

  $id_pers_ext = $row[0];

  // Insertion d'image
  $PATHS = imageLocationPersExt($id_pers_ext);
  foreach ($IMAGES as $imagename => $imagepath) {
    $source = $IMAGES[$imagename];

    if ($imagename == 'photo')
      $destination = $PATHS["photo_chemin_local"];
    else
      if ($imagename == 'signature')
        $destination = $PATHS["signature_chemin_local"];

    if (($source == NULL) or ($source == "") or ($source == "/adbanking/images/travaux.gif"))
      exec("rm -f ".escapeshellarg($destination));
    else {
      if (is_file($source)) {
        rename($source, $destination);
        chmod($destination, 0777);
      }
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array (
                        'id_pers_ext' => $id_pers_ext
                      ));
}

/**
 * Modifier une personne extérieure dans la base de donnée
 * @author Antoine Guyette
 * @param integer $id_pers_ext identifiant de la personne extérieure
 * @param Array $DATA données sur la personne extérieure
 * @return ErrorObj
 */
function modifierPersonneExt($id_pers_ext, $DATA) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $IMAGES = array (
              'photo' => $DATA['photo'],
              'signature' => $DATA['signature']
            );
  unset ($DATA['photo']);
  unset ($DATA['signature']);

  $WHERE['id_pers_ext'] = $id_pers_ext;
  $WHERE['id_ag'] = $global_id_agence;
  $sql = buildUpdateQuery('ad_pers_ext', $DATA, $WHERE);

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  // Insertion d'image
  $PATHS = imageLocationPersExt($id_pers_ext);
  foreach ($IMAGES as $imagename => $imagepath) {
    $source = $IMAGES[$imagename];

    if ($imagename == 'photo')
      $destination = $PATHS["photo_chemin_local"];
    else
      if ($imagename == 'signature')
        $destination = $PATHS["signature_chemin_local"];

    if (($source == NULL) or ($source == "") or ($source == "/adbanking/images/travaux.gif"))
      exec("rm -f ".escapeshellarg($destination));
    else {
      if ($source != $PATHS[$imagename."_chemin_web"]) {
        rename($source, $destination);
        chmod($destination, 0777);
      }
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Nombre de personnes extérieures répondant à la clause where
 * @author Antoine Guyette
 * @param Array $a_where conditions de recherche sur la table  ad_pers_ext
 * @return errorObj Avec comme paramètre le nombre de personnes extérieures répondant aux clauses where
 */
function nombrePersonneExt($a_where)
{
	global $global_id_agence;
   // construction de la chaine de la requete pr cherche le personne ext ds la table ad_pers_ext
  $sql_pe ="SELECT count(id_pers_ext) FROM ad_pers_ext WHERE id_ag = $global_id_agence AND id_client is null";

  // construction de la chaine de la req pr recherche  client pers_ext ds ad_cli
  $sql_cli="SELECT count(cli.id_client) FROM ad_cli cli, ad_pers_ext pe WHERE cli.id_ag = $global_id_agence AND cli.id_client = pe.id_client AND statut_juridique = 1";

  // contruction du critere de selection des client et non client pers_ext

  if (is_array($a_where)) {
    $a_where=array_make_pgcompatible($a_where);
    $sql_pe  .= " AND ";
    $sql_cli .= " AND ";
    foreach ($a_where as $champ => $valeur) {
      if ($champ == 'denomination') {
        $sql_cli .= "pp_nom || ' ' || pp_prenom LIKE '$valeur%' AND";
        $sql_pe .= " $champ LIKE '$valeur%' AND";

      } elseif ($champ=='id_client')  {
        $sql_cli .= " cli.$champ = '$valeur' AND"; //prefixé le champ par l'alias cli
        $sql_pe .= " $champ = '$valeur' AND";
      }
      elseif ($champ=="lieu_naiss") {
        $sql_cli .= " cli.pp_lieu_naissance = '$valeur' AND";
        $sql_pe .= " $champ = '$valeur' AND";
      }
      elseif ($champ=="date_naiss"){
        $sql_cli .= " cli.pp_date_naissance = '$valeur' AND";
        $sql_pe .= " $champ = '$valeur' AND";
      }
      elseif ($champ=="id_pers_ext") {
        $sql_pe .= " $champ = '$valeur' AND";
        $sql_cli .= " $champ = '$valeur' AND";
      }
      else {
        $sql_cli .= " cli.$champ = '$valeur' AND";
        $sql_pe .= " $champ = '$valeur' AND";
      }
    }
    // On retire le dernier 'AND'
    $sql_pe = substr($sql_pe, 0, strlen($sql_pe) - 4);
    $sql_cli = substr($sql_cli, 0, strlen($sql_cli) - 4);
  }

  // recherche des personnes ext non cleintes
  $result = executeDirectQuery($sql_pe, TRUE);
  if($result->errCode!=NO_ERR){
    return $result;
  }
  $nbre_pe = $result->param[0];

  // recherche des personnes ext clientes
  $result = executeDirectQuery($sql_cli, TRUE);
  if($result->errCode==NO_ERR){
    $result->param[0] += $nbre_pe;
  }

  return $result;
}

/**
 * Personnes extérieures répondant à la clause where
 * @author Antoine Guyette
 * @param Array $a_where conditions de recherche sur la table  ad_pers_ext
 * @returns Array $DATA informations sur les personnes extérieures répondant à la clause where
 */
function getPersonneExt($a_where)
{
	global $global_id_agence;

  // construction de la chaine de la requete pr cherche le personne ext ds la table ad_pers_ext
  $sql_pe ="SELECT id_pers_ext, id_client , ";
  $sql_pe .=" denomination, ";
  $sql_pe .="adresse, " ;
  $sql_pe .="code_postal, ville, pays, ";
  $sql_pe .="num_tel, ";
  $sql_pe .=" date_naiss, ";
  $sql_pe .="lieu_naiss, ";
  $sql_pe .="type_piece_id, " ;
  $sql_pe .=" num_piece_id, ";
  $sql_pe .=" lieu_piece_id, " ;
  $sql_pe .=" date_piece_id, " ;
  $sql_pe .="date_exp_piece_id ," ;
  $sql_pe .="id_ag ";
  $sql_pe .=" FROM ad_pers_ext WHERE id_ag = $global_id_agence AND id_client is null ";

  // contruction de la chaine de la req pr recherche  client pers_ext ds ad_cli
  $sql_cli ="SELECT pe.id_pers_ext, cli.id_client , ";
  $sql_cli .="pp_nom || ' ' || pp_prenom as denomination, ";
  $sql_cli .="cli.adresse, " ;
  $sql_cli .="cli.code_postal, cli.ville, cli.pays, ";
  $sql_cli .="cli.num_tel, ";
  $sql_cli .="cli.pp_date_naissance as date_naiss, ";
  $sql_cli .="cli.pp_lieu_naissance as lieu_naiss, ";
  $sql_cli .="cli.pp_type_piece_id as type_piece_id, " ;
  $sql_cli .="cli.pp_nm_piece_id as num_piece_id, ";
  $sql_cli .="cli.pp_lieu_delivrance_id as lieu_piece_id, " ;
  $sql_cli .="cli.pp_date_piece_id  as date_piece_id, " ;
  $sql_cli .="cli.pp_date_exp_id as date_exp_piece_id ," ;
  $sql_cli .="cli.id_ag ";
  $sql_cli .="FROM ad_cli cli, ad_pers_ext pe WHERE cli.id_ag = $global_id_agence AND cli.id_client = pe.id_client AND statut_juridique = 1";

  // contruction du critere de selection des client et non client pers_ext

  if (is_array($a_where)) {
    $a_where = array_make_pgcompatible($a_where);
    $sql_pe  .= " AND ";
    $sql_cli .= " AND ";
    foreach ($a_where as $champ => $valeur) {
      if ($champ == "denomination") {
        $sql_cli .= "pp_nom || ' ' || pp_prenom LIKE '$valeur%' AND";
        $sql_pe .= " $champ LIKE '$valeur%' AND";

      } elseif ($champ=="id_client")  {
       $sql_cli .= " cli.$champ = '$valeur' AND"; //prefixé le champ par l'alias cli
       $sql_pe .= " $champ = '$valeur' AND";
      }
      elseif ($champ=="lieu_naiss") {
        $sql_cli .= " cli.pp_lieu_naissance = '$valeur' AND";
        $sql_pe .= " $champ = '$valeur' AND";
      }
      elseif ($champ=="date_naiss") {
        $sql_cli .= " cli.pp_date_naissance = '$valeur' AND";
        $sql_pe .= " $champ = '$valeur' AND";
      }
      elseif ($champ=="id_pers_ext") {
        $sql_pe .= " $champ = '$valeur' AND";
        $sql_cli .= " $champ = '$valeur' AND";
      }
      else {
        $sql_cli .= " cli.$champ = '$valeur' AND";
        $sql_pe .= " $champ = '$valeur' AND";
      }
    }
    // On retire le dernier 'AND'
    $sql_pe = substr($sql_pe, 0, strlen($sql_pe) - 4);
    $sql_cli = substr($sql_cli, 0, strlen($sql_cli) - 4);
  }

  // concaténation des req pr unir le resultat
  // attention: le nbre de champ ds la requete sql_pe doit correspondre au de champ ds la requete sql_cli
  $sql = $sql_pe." UNION ".$sql_cli." ;";
  $result = executeDirectQuery($sql, false);
  if ($result->errCode != NO_ERR) {
    return array();
  }
  return $result->param;
}

/**
 * Ajouter une relation dans la base de donnée
 * @author Antoine Guyette
 * @param Array $DATA données sur la relation
 * @return ErrorObj
 */
function ajouterRelation($DATA) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $DATA['valide'] = 't';
  $DATA['id_ag'] = $global_id_agence;
  $sql = buildInsertQuery('ad_rel', $DATA);

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Modifier une relation dans la base de donnée
 * @author Antoine Guyette
 * @param integer $id_rel identifiant de la relation
 * @param Array $DATA données sur la relation
 * @return ErrorObj
 */
function modifierRelation($id_rel, $DATA) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $WHERE['id_rel'] = $id_rel;
  $WHERE['id_ag'] = $global_id_agence;

  $sql = buildUpdateQuery('ad_rel', $DATA, $WHERE);

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Supprimer une relation dans la base de donnée
 * @author Antoine Guyette
 * @param integer $id_rel identifiant de la relation
 * @return ErrorObj
 */
function supprimerRelation($id_rel) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $DATA['valide'] = 'f';
  $WHERE['id_rel'] = $id_rel;
  $WHERE['id_ag'] = $global_id_agence;

  $sql = buildUpdateQuery('ad_rel', $DATA, $WHERE);

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Donne la liste des membres d'un groupe solidaire.
 *
 * @author Antoine Delvaux
 * @since 2.7
 * @param int $a_id_client L'identifiant du client de type groupe solidaire
 * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant la liste des identifiants client des membres du groupe solidaire.
 */
function getListeMembresGrpSol($a_id_client) {
  global $global_id_agence;
  return (executeDirectQuery("SELECT id_membre FROM ad_grp_sol WHERE id_ag=$global_id_agence AND id_grp_sol = $a_id_client;", TRUE));
}

/**
 * Dit si un membre appartient à un groupe solidaire.
 *
 * @author Antoine Delvaux
 * @since 2.7
 * @param int $a_id_client L'identifiant du membre du groupe solidaire
 * @param int $a_id_grp_sol L'identifiant du groupe solidaire
 * @return ErrorObj Un ErrorObj avec comme paramètre true ou false suivant que le membre appartient au groupe ou non.
 */
function IsMembreGrpSol($a_id_client, $a_id_grp_sol) {
  global $global_id_agence;
  return (executeDirectQuery("SELECT COUNT (*) FROM ad_grp_sol WHERE id_ag=$global_id_agence AND id_membre = $a_id_client AND id_grp_sol = $a_id_grp_sol;", TRUE));
}

function getClientEtat($i = 0){
	global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

	$sql = " SELECT id_client, etat, statut_juridique, pp_nom, pp_prenom, pm_raison_sociale, gi_nom, pp_sexe, date_adh, date_defection FROM ad_cli ";
	$sql .= " WHERE id_ag = $global_id_agence AND id_client > $i ORDER BY id_client, etat ";
	$sql .= " limit 4000 ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  if ($result->numRows() == 0)
    return NULL;

  $DATAS = array ();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($DATAS, $tmprow);

  $dbHandler->closeConnection(true);
  return $DATAS;
}

/**
 * Insertions des champs supplémentaire pour le crédit s dans champs_extras_valeurs_ad_dcr pour les chmaps supplementaire  du crédit
 * @param Array $DATA Toutes les données des  dossier de crédit
 * @param $id_client ID du dossier de crédit 
 * @return ErrorObj Objet Erreur
 */
function inseresClientChampsExtras(array $DATAChamps , $id_client) {
	global $global_id_agence;
 
	foreach ($DATAChamps as $id_champs => $valeurChamps) {
		if($valeurChamps!=NULL && trim($valeurChamps)!='') { // Fix : ticket #272
			$DATA['id_ag']= $global_id_agence;
			$DATA['id_client'] = $id_client;
			$DATA['id_champs_extras_table'] =$id_champs;
			$DATA['valeur']= $valeurChamps ;
			$myError =insereClientChampsExtras($DATA);
			if($myError->errCode != NO_ERR) {
				 return $myError ;
			}
		}
	}
	return new ErrorObj(NO_ERR);
}
 /**
 * Crée une nouvelle entrée dans champs_extras_valeurs_ad_dcr pour les chmaps supplementaire  du crédit
 * @param Array $DATA Toutes les données des  dossier de crédit
  * @return ErrorObj Objet Erreur
 */
function insereClientChampsExtras(array $DATA) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  //$DATA['id_ag']= $global_id_agence;

  $sql = buildInsertQuery ("champs_extras_valeurs_ad_cli", $DATA);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}
/**
 * MAJ des champs supplémentaire pour le crédit s dans champs_extras_valeurs_ad_dcr pour les chmaps supplementaire  du crédit
 * @param Array $DATA Toutes les données des  dossier de crédit
 * @param $id_client ID du dossier de crédit 
 * @return ErrorObj Objet Erreur
 */
function updatesClientChampsExtras(array $DATAChamps , $id_client) {
	global $global_id_agence;
 
	foreach ($DATAChamps as $id_champs => $valeurChamps) {

		if(count(getChampsExtrasCLIENTValues($id_client,$id_champs)) > 0 ) {
			$WHERE['id_ag']= $global_id_agence;
			$WHERE['id_client'] = $id_client;
			$WHERE['id_champs_extras_table'] =$id_champs;

			// Fix : ticket #272
			if($valeurChamps==NULL || trim($valeurChamps)=='') {
				$valeurChamps = ' ';
			}

			$field['valeur']= $valeurChamps ;
			$myError =updateClientChampsExtras($field,$WHERE);
			if($myError->errCode != NO_ERR) {
				 return $myError ;
			}
		}else {
			if($valeurChamps!=NULL && trim($valeurChamps)!='') { // Fix : ticket #272
				$DATA['id_ag']= $global_id_agence;
				$DATA['id_client'] = $id_client;
				$DATA['id_champs_extras_table'] =$id_champs;
				$DATA['valeur']= $valeurChamps ;
				$myError =insereClientChampsExtras($DATA);
				if($myError->errCode != NO_ERR) {
					 return $myError ;
				}
			}
		}
	}
	return new ErrorObj(NO_ERR);
}

/**
 * Mettre à jour   un  chmaps supplementaire  du crédit  dans  la table  champs_extras_valeurs_ad_dcr 
 * @param Array $DATA Toutes les données des  dossier de crédit
 * @param Array $Where tableau des condition de mise à jour 
  * @return ErrorObj Objet Erreur
 */
function updateClientChampsExtras(array $DATA, $Where) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = buildUpdateQuery("champs_extras_valeurs_ad_cli", $DATA, $Where);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}
/**
 * Fonction qui renvoie les champs extras des tables
 * @param text $id
 * @param int $id_client
 * @return array Tableau des champs
 */
function getChampsExtrasCLIENTValues($id_client,$id = NULL) {
  global $dbHandler,$global_id_agence,$global_langue_systeme_dft;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM champs_extras_valeurs_ad_cli where  id_client = $id_client AND id_ag=$global_id_agence ";
  if (!is_null($id)) {
  	$sql .= " AND id_champs_extras_table = $id  ";
  }
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $champsExtrasValues = array ();
  while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
    $champsExtrasValues[$tmprow['id_champs_extras_table']] = $tmprow['valeur'];
  }
  $dbHandler->closeConnection(true);
  return $champsExtrasValues;
}

function getClientGender($id_client) {
    // PS qui renvoie un string contenant le sexe du client.
    $CLI = getClientDatas($id_client);
    switch ($CLI['pp_sexe']) {
        case 1 : // PP
            return 'M';
        case 2 :
            return 'F';
        default :
            return '';
    }
}

/**
 * Fonction pour verifier la validité du numero telephone client -> si c'est vide ou depasse le nombre de chiffres permi
 * pour le module SMS Banking
 * PARAM : id client
 * RETURN boolean (true/false)
 */
function isValidNumTelClient($id_client){
  global $dbHandler,$global_id_agence,$global_langue_systeme_dft;
  $isValid = true;

  $db = $dbHandler->openConnection();

  //Recupere numero telephone client
  $sql_numtel = "SELECT num_tel FROM ad_cli where id_ag=$global_id_agence ";
  if (!is_null($id_client)) {
    $sql_numtel .= " AND id_client = $id_client  ";
  }
  $result_numtel = $db->query($sql_numtel);
  if (DB :: isError($result_numtel)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $numTel = '';
  while ($tmprow = $result_numtel->fetchRow(DB_FETCHMODE_ASSOC)) {
    $numTel = $tmprow['num_tel'];
  }

  //Recupere nombre chiffre parametré pour sms banking
  $sql_chiffre = "SELECT valeur FROM adsys_param_abonnement where id_ag=$global_id_agence AND cle = 'NB_CARACTERES_TELEPHONE'";
  $result_chiffre = $db->query($sql_chiffre);
  if (DB :: isError($result_chiffre)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $nombreChiffre = '';
  while ($tmprow = $result_chiffre->fetchRow(DB_FETCHMODE_ASSOC)) {
    $nombreChiffre = $tmprow['valeur'];
  }

  if (!is_null($numTel)) { // not null numero telephone est renseigné ou vide
    if (!is_null($nombreChiffre) && $numTel != "" && (strlen($numTel) > intval($nombreChiffre) || strlen($numTel) < intval($nombreChiffre))) { // depasse ou moins que le nombre de chiffres permi
      $isValid = false;
    }
    if ($numTel == "") { // numero telephone est vide
      $isValid = false;
    }
  }
  else{ // null
    $isValid = false;
  }

  $dbHandler->closeConnection(true);
  return $isValid;
}

/**
 * Fonction pour recuperer les infos de la table adsys_param_abonnement
 * PARAM : aucun
 * RETURN array of data
 */
function getInfoParamAbonnement($cle = null){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  //Recupere les infos
  $sql = "SELECT * FROM adsys_param_abonnement where id_ag=$global_id_agence ";

  if (isset($cle)) {
    $sql .= "AND cle = '" .$cle. "';";
  }

  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $infoParamAbonnement = array();
  while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
    $infoParamAbonnement = $tmprow;
  }

  $dbHandler->closeConnection(true);
  return $infoParamAbonnement;
}

/**
 * Liste des des employeurs de l'agence
 * @param int $id_ag identifiant de l'agence
 * @return array tableau contenant la liste des employeurs
 */

function getListeEmployeur($condition = null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_employeur where id_ag=$global_id_agence ";

  if ($condition != null) {
    $sql .= " AND ".$condition;
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id"]] = $row['nom'];

  return $DATAS;
}

/**
 * Liste des des employeurs de l'agence
 * @param int $id_ag identifiant de l'agence / Case = si on retoune le nom ou la ligne
 *
 * @return array tableau contenant la liste des employeurs
 */

function getListeEmployeurComplet($condition = null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_employeur where id_ag=$global_id_agence ";

  if ($condition != null) {
    $sql .= " AND ".$condition;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }


  if ($result->numRows() == 0) return NULL;

  $DATAS = array();
  $i = 1;
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $DATAS[$i] = $row;
    $i++;
  }
  $dbHandler->closeConnection(true);
  return $DATAS;
}


/**
 * Mise a jour quotite dans la table ad_cli
 * @param array de l'update
 * @return return bool
 */

function update_quotite_client($DATA_update,$DATA_where) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $update_quotite_cli = buildUpdateQuery('ad_cli',$DATA_update,$DATA_where);
  $result_quotite_cli = $db->query($update_quotite_cli);
  if (DB::isError($result_quotite_cli)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }
  else {
    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
  }
}

function getListelocalisationRwanda($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_localisation_rwanda WHERE id_ag=$global_id_agence ";

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
    $DATAS[$row["id"]] = $row['libelle_localisation'];

  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getLocalisationRwandaDetails($whereCond=null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_localisation_rwanda WHERE id_ag=$global_id_agence ";

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

/**
 * Fonction qui parse et vérifie un fichier de données pour le chargement des localisations du rwanda
 * @author Ahaad
 * @param $fichier_lot emplacement du fichier de données
 * @return ErrorObj
 */
function parse_fichier_chargement_loc_rwanda($fichier_lot)
{

  global $global_id_agence, $dbHandler;

  $db = $dbHandler->openConnection();
  $total = array();
  $total_com = array();


  if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
  }

  $handle = fopen($fichier_lot, 'r');

  $count = 0;

  $currentLocId = 0;

  $code_province = '';
  $libel_province = '';
  $id_province = 0;

  $code_district = '';
  $libel_district = '';
  $id_district = 0;

  $code_secteur = '';
  $libel_secteur = '';
  $id_secteur = 0;

  $code_cellule = '';
  $libel_cellule = '';
  $id_cellule = 0;

  $code_village = '';
  $libel_village = '';
  $id_village = 0;


  //debut id localisation
  $sql = "SELECT nextval('adsys_localisation_rwanda_id_seq')";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
  }
  if ($result->numRows() == 0) return NULL;
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $currentLocId = $DATAS['nextval'];


  while (($data = fgetcsv($handle, 200, ';')) != false) {
    if($count == 0){ $count++; continue; }
    $count++;

    $num = count($data);
    if ($num != 10) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $count));
    }

   /* $code_village = $data[0];
    $libel_village = $data[1];
    $code_cellule = $data[2];
    $libel_cellule = $data[3];
    $code_secteur = $data[4];
    $libel_secteur = $data[5];
    $code_district = $data[6];
    $libel_district = $data[7];
    $code_province = $data[8];
    $libel_province = $data[9];*/

      if ($code_province != $data[8]){
        $code_province = $data[8];
        $libel_province = str_replace("'"," ",$data[9]);
        $id_province = $currentLocId;
        $currentLocId++;
        $count++;

        $insertProvince = "INSERT INTO adsys_localisation_rwanda (id, code_localisation, libelle_localisation, parent, id_ag,type_localisation) VALUES (".$id_province.", '".$code_province."','".$libel_province."', 0, numagc(),1)";
        $result_insertProvince = $db->query($insertProvince);
        if (DB::isError($result_insertProvince)) {
          $dbHandler->closeConnection(false);
        }
        //echo "Province = ".$province." inserted with id = ".$id_province."\n";
        echo " .";
      }
      if ($code_district != $data[6]){
        $code_district = $data[6];
        $libel_district = str_replace("'"," ",$data[7]);
        $id_district = $currentLocId;
        $currentLocId++;
        $count++;

        $insertDistrict = "INSERT INTO adsys_localisation_rwanda (id, code_localisation, libelle_localisation, parent, id_ag,type_localisation) VALUES (".$id_district.", '".$code_district."','".$libel_district."', ".$id_province.", numagc(),2)";
        $result_insertDistrict = $db->query($insertDistrict);
        if (DB::isError($result_insertDistrict)) {
          $dbHandler->closeConnection(false);
        }
        //echo "\t Commune = ".$commune." inserted with id = ".$id_commune." with parent name = ".$province." and parent id = ".$id_province."\n";
        echo " .";
      }
      if ($code_secteur != $data[4]){
        $code_secteur = $data[4];
        $libel_secteur = str_replace("'"," ",$data[5]);
        $id_secteur = $currentLocId;
        $currentLocId++;
        $count++;

        $insertSecteur = "INSERT INTO adsys_localisation_rwanda (id, code_localisation, libelle_localisation, parent, id_ag,type_localisation) VALUES (".$id_secteur.", '".$code_secteur."', '".$libel_secteur."', ".$id_district.", numagc(),3)";
        $result_insertSecteur= $db->query($insertSecteur);
        if (DB::isError($result_insertSecteur)) {
          $dbHandler->closeConnection(false);
        }
        //echo "\t\t Zone = ".$zone." inserted with id = ".$id_zone." with parent name = ".$commune." and parent id = ".$id_commune."\n";
        echo " .";
      }
      if ($code_cellule != $data[2]){
        $code_cellule = $data[2];
        $libel_cellule = str_replace("'"," ",$data[3]);
        $id_cellule = $currentLocId;
        $currentLocId++;
        $count++;

        $insertColline = "INSERT INTO adsys_localisation_rwanda (id, code_localisation, libelle_localisation, parent, id_ag,type_localisation) VALUES (".$id_cellule.", '".$code_cellule."', '".$libel_cellule."', ".$id_secteur.", numagc(),4)";
        $result_insertColline = $db->query($insertColline);
        if (DB::isError($result_insertColline)) {
          $dbHandler->closeConnection(false);
        }
        //echo "\t\t\t colline = ".$colline." inserted with id = ".$id_colline." with parent name = ".$zone." and parent id = ".$id_zone."\n";
        echo " .";
      }

      if ($code_village != $data[0]){
        $code_village = $data[0];
        $libel_village = str_replace("'"," ",$data[1]);
        $id_village = $currentLocId;
        $currentLocId++;
        $count++;

        $insertColline = "INSERT INTO adsys_localisation_rwanda (id, code_localisation, libelle_localisation, parent, id_ag,type_localisation) VALUES (".$id_village.", '".$code_village."','".$libel_village."', ".$id_cellule.", numagc(),5)";
        $result_insertColline = $db->query($insertColline);
        if (DB::isError($result_insertColline)) {
          $dbHandler->closeConnection(false);
        }
        //echo "\t\t\t colline = ".$colline." inserted with id = ".$id_colline." with parent name = ".$zone." and parent id = ".$id_zone."\n";
        echo " .";
      }

    }

  fclose($handle);
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR,$count);
}

function updateMntLimiteEwallet($id_client,$montant_limit, $date, $type_transaction){

  global $global_id_agence, $dbHandler;

  $db = $dbHandler->openConnection();
  $type_transaction = ($type_transaction == 1)?'retrait':'depot';
  $sql = buildUpdateQuery ("ad_cli", array("mnt_".$type_transaction."_limit_ewallet_jounalier "=>$montant_limit,'date_'.$type_transaction.'_limit_ewallet_jounalier'=>$date),
      array("id_client"=>$id_client));
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj('ERR_MISE_A_JOUR_MNT_LIMIT');
  }

  $dbHandler->closeConnection(true);
  return  new ErrorObj(NO_ERR);

}
function update_d_tableliste($data, $where){
    global $global_id_agence, $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = buildUpdateQuery ("d_tableliste", $data, $where);
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DB_SQL, "fonction update_d_tableliste, SQL: $sql");
    }

    $dbHandler->closeConnection(true);
    return  new ErrorObj(NO_ERR);
}

function audit_client($DATA){
    global $dbHandler, $global_id_agence, $global_id_client;
    $db = $dbHandler->openConnection();

    $DATA["id_ag"] = $global_id_agence;
    $sql = buildUpdateQuery("ad_cli_hist",$DATA, array("id_client_hist = (SELECT id_client_hist FROM ad_cli_hist WHERE id_client = $global_id_client ORDER BY id_client_hist DESC LIMIT 1)/*" => "*/--"));

    // Insertion dans la DB
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }


    $id_cli_hist = getCurrSequence('ad_cli_hist_seq');

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR, $id_cli_hist);
}

function getClientHistoric($id_cli_hist)
{
    global $dbHandler;
    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM ad_cli_hist WHERE id_client_hist=$id_cli_hist";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $rows = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);

    return $rows;
}

function getCurrSequence($sequence){
    global $dbHandler;
    $db = $dbHandler->openConnection();

    $sql = "SELECT last_value FROM $sequence";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);
    return $row[0];
}

function getNextSequence($sequence){
    global $dbHandler;

    $db = $dbHandler->openConnection();

    $sql = "SELECT nextval('$sequence')";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $row = $result->fetchrow();

    $dbHandler->closeConnection(true);
    return $row[0];
}

function getListelocalisationFenacobu($whereCond=null) {

    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM adsys_niveau_fenacobu WHERE id_ag=$global_id_agence ";

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
        $DATAS[$row["id"]] = $row['libelle_niveau'];

    $dbHandler->closeConnection(true);

    return $DATAS;
}
function getLocalisationFENACOBUDetails($whereCond=null) {

    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM adsys_niveau_fenacobu WHERE id_ag=$global_id_agence ";

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
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        $DATAS[$row["id"]] = $row;
    }
    $dbHandler->closeConnection(true);

    return $DATAS;
}

function getClientFENACOBUDetails($whereCond=null) {

    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM ad_cli WHERE id_ag=$global_id_agence ";

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
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        $DATAS[$row["id"]] = $row;
    }
    $dbHandler->closeConnection(true);

    return $DATAS;
}

function deleteData($table_name, $array_condi){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();
    $sql = buildDeleteQuery($table_name, $array_condi);
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $dbHandler->closeConnection(true);
}

function getNivFenacobuArray($condi=null) {
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT id, libelle_niveau, type_niveau, parent  FROM adsys_niveau_fenacobu where id_ag=$global_id_agence ";
    if ($condi != null){
        $sql .= " AND $condi ";
    }
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

function getNiVFenacobuNom($id){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT libelle_niveau from adsys_niveau_fenacobu where id = $id";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    return $row[0];
}

function getLibelLocalisationFenacobu($id){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT libelle_niveau FROM adsys_niveau_fenacobu where id = $id";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();
    return $row[0];
}
?>
