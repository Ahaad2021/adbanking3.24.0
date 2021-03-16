<?php

/**
 * Renvoie une liste de prestataires eWallet
 * 
 * @return array Tableau associatif avec les prestataires eWallet trouvés.
 */
function getListPrestataireEwallet() {
    global $dbHandler, $global_id_agence, $global_id_client;

    $db = $dbHandler->openConnection();
    $sql = sprintf("SELECT * FROM ad_ewallet WHERE id_ag=%d ORDER BY nom_prestataire ASC", $global_id_agence);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $tmp_arr = array();
    while ($prestataire = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

        $tmp_arr[$prestataire["id_prestataire"]] = trim($prestataire["nom_prestataire"]);
    }

    return $tmp_arr;
}

/*
 * Cette l'id_prestataire a partir du code_prestataire
 */
function getPrestataire($code_prestaire) {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = sprintf("SELECT p.* FROM adsys_prestataire p WHERE p.id_ag=%d AND code_prestataire = '%s' LIMIT 1", $global_id_agence, $code_prestaire);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return $DATAS;
}


/*
 * Cette fonction renvoie toutes les informations relatives à un client abonné
 */
function getClientAbonnementInfo($identifiant) {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = sprintf("SELECT a.*,e.* FROM ad_abonnement a LEFT JOIN ad_ewallet e ON a.id_prestataire=e.id_prestataire WHERE a.id_ag=%d AND a.deleted='f' AND a.identifiant LIKE '%s' ORDER BY a.date_creation DESC LIMIT 1", $global_id_agence, $identifiant);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return $DATAS;
}

/**
 * Renvoie toutes les abonnements d'un client
 * 
 * @return array Tableau associatif avec les abonnements trouvés.
 */
function getListAbonnement() {
    global $dbHandler, $global_id_agence, $global_id_client;

    $db = $dbHandler->openConnection();
//    $sql = sprintf("SELECT * FROM ad_abonnement WHERE id_ag=%d AND deleted='f' AND id_client=%d ORDER BY id_abonnement ASC ", $global_id_agence, $global_id_client);
    $sql = sprintf(
        "SELECT A.id_abonnement, CASE WHEN A.id_prestataire IS NOT null THEN M.libelle || ': ' || E.nom_prestataire ELSE M.libelle END AS libelle, E.nom_prestataire " .
        "FROM ad_abonnement A " .
        "INNER JOIN adsys_mobile_service M ON M.id_service = A.id_service " .
        "LEFT JOIN ad_ewallet E on E.id_prestataire = A.id_prestataire AND E.id_ag = A.id_ag " .
        "WHERE A.id_ag=%d " .
        "AND A.deleted='f' " .
        "AND A.id_client=%d " .
        "ORDER BY A.id_service ASC, A.id_prestataire ASC, A.id_abonnement ASC",
        $global_id_agence, $global_id_client
    );

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $tmp_arr = array();
    while ($abonnement = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

        $tmp_arr[$abonnement["id_abonnement"]] = $abonnement["libelle"]; //$abonnement;
    }

    return $tmp_arr;
}

function getListMobileService($includeEstatement = true) {
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = sprintf("SELECT * FROM adsys_mobile_service WHERE id_ag=%d ", $global_id_agence);
    $sql .= $includeEstatement === false ? "AND id_service NOT IN (SELECT id_service from adsys_mobile_service WHERE code = 'ESTATEMENT') " : "";
    $sql .= "ORDER BY id_service ASC ";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $tmp_arr = array();
    while ($abonnement = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

        $tmp_arr[$abonnement["id_service"]] = $abonnement["libelle"];
    }

    return $tmp_arr;
}

function getAvailablePrestataire($distinct = true) {
    global $dbHandler, $global_id_agence, $global_id_client;

    $db = $dbHandler->openConnection();
    $sql = "SELECT e.id_prestataire, e.nom_prestataire FROM ad_ewallet e ";
    if ($distinct) {
    $sql .= " WHERE e.id_prestataire NOT IN (SELECT COALESCE(A.id_prestataire,0) FROM ad_abonnement A WHERE A.id_ag=".$global_id_agence." AND A.deleted='f' AND A.id_client=".$global_id_client." ) ";
    }
    $sql .= " ORDER BY e.id_prestataire ASC";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $tmp_arr = array();
    while ($abonnement = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

        $tmp_arr[$abonnement["id_prestataire"]] = $abonnement["nom_prestataire"]; //$abonnement;
    }

    return $tmp_arr;
}

function getAvailableServices() {
    global $dbHandler, $global_id_agence, $global_id_client;

    $db = $dbHandler->openConnection();
    $sql = sprintf(
        "SELECT e.id_service from adsys_mobile_service e ".
        "WHERE e.id_service not in " .
        "(SELECT COALESCE(A.id_service,0) FROM ad_abonnement A WHERE A.id_ag=%d AND A.deleted='f' AND A.id_client=%d )",
        $global_id_agence, $global_id_client
    );

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $tmp_arr = array();
    while ($service = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

        $tmp_arr[$service["id_service"]] = $service["id_service"];
    }

    return $tmp_arr;
}

/*
 * Cette fonction renvoie toutes les informations relatives à un abonnement
 */
function getAbonnementData($id_abonnement) {

    global $dbHandler, $global_id_agence, $global_id_client;

    $db = $dbHandler->openConnection();

    $sql = sprintf("SELECT * FROM ad_abonnement A " .
        "LEFT JOIN ad_ewallet E on E.id_prestataire = A.id_prestataire AND E.id_ag = A.id_ag " .
        "WHERE A.id_ag=%d AND A.deleted='f' AND A.id_client=%d AND A.id_abonnement=%d", $global_id_agence, $global_id_client, $id_abonnement);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return $DATAS;
}

/*
 * Vérifié si l'email existe
 */
function isEmailExist($email) { // Renvoie true si un numéro sms existe

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT count(*) FROM ad_abonnement WHERE id_ag=$global_id_agence AND deleted='f' AND estatement_email = '$email'";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);

    return ($row[0] > 0);
}

/*
 * Vérifié si le numéro sms existe
 */
function isNumSmsExist($num_sms, $id_client=null) { // Renvoie true si un numéro sms existe

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT count(*) FROM ad_abonnement WHERE id_ag=$global_id_agence AND deleted='f' AND num_sms = '$num_sms'";
    
    if ($id_client != null) {
        $sql .= " AND id_client<>".$id_client;
    }

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);

    return ($row[0] >= 1);
}

/*
 * Recup l'email d'un client
 */
function getEmailByClientId($id_client) {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT estatement_email FROM ad_abonnement WHERE id_ag=$global_id_agence AND deleted='f' AND id_client = $id_client AND estatement_email IS NOT NULL LIMIT 1";
    $result = $db->query($sql);

    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $row = $result->fetchrow();
    return $row[0];
}

/*
 * Recup le numéro sms d'un client
 */
function getNumSmsByClientId($id_client) { 

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT num_sms FROM ad_abonnement WHERE id_ag=$global_id_agence AND deleted='f' AND id_client = $id_client LIMIT 1";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $row = $result->fetchrow();

    return $row[0];
}

function validateNumSms($num_sms) {

    $isNumSmsValid = FALSE;

    $num_sms_test = preg_replace('/[\t\n\r\s\+\-]+/i', '', trim($num_sms));

    if (substr($num_sms_test, 0, 2) == '07') {
        $num_sms_test = '25'.$num_sms_test;

        $isNumSmsValid = TRUE;
    }

    if (strlen($num_sms_test) < 12 || strlen($num_sms_test) > 12) {
        $isNumSmsValid = FALSE;
    }

    return $isNumSmsValid;
}

function nullToFalse($val) {

    if($val === null || $val == '' || $val == '0') {
        return 'f';
    }
    return 't';
}

/**
 * Insèrer/modifier un nouvel abonnement dans la table ad_abonnement
 *
 * @param String $mode : ajouter / modifier
 * @param String $num_sms : Le numéro sms d'un client
 * @param Integer $langue : Identifiant de la langue
 * @param Boolean $ewallet : Est abonné à eWallet? True/False
 * @param Integer $id_prestataire : Identifiant du prestataire
 * @param String $password : Le mot de passe
 * @param Integer $id_abonnement : Identifiant de d'abonnement
 * 
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function handleAbonnement($mode, $num_sms=null, $langue, $ewallet = null, $id_prestataire, $password=null, $id_abonnement=null, $id_service, $estatement_email=null, $estatement_journalier=null, $estatement_hebdo=null, $estatement_mensuel=null, $id_cpte=null) {

    global $dbHandler, $global_id_agence, $global_id_client;

    $db = $dbHandler->openConnection();

    // Build insert string
    $id_client = $global_id_client;
    $id_ag = $global_id_agence;
    
    $sql = null;
    $sql_update_ad_cli = null;

    $tableName = "ad_abonnement";
    if ($mode == "modifier" && $id_abonnement !== null && $id_abonnement > 0) {
        if($id_service === "1") {
            $tableFields = array("langue" => $langue, "ewallet" => nullToFalse($ewallet));
            if(!empty($id_prestataire))
                $tableFields["id_prestataire"] = $id_prestataire;
            if(!empty($id_cpte))
                $tableFields["id_cpte"] = $id_cpte;
            
            // Update field num_tel in table ad_cli
            //$sql_update_ad_cli = buildUpdateQuery("ad_cli", array("num_tel" => trim($num_sms)), array('id_client' => $id_client, 'id_ag' => $id_ag));

            if (trim($password) != '') {
                $salt = generateRandomString(generateIdentifiant());
                $motdepasse = encodePassword(trim($password), $salt);

                $tableFields["motdepasse"] = $motdepasse;
                $tableFields["salt"] = $salt;
            }
        } else {

            $tableFields = array(
                "langue" => $langue,
                //"id_service" => $id_service,
                //"estatement_email" => $estatement_email,
                "estatement_journalier" => nullToFalse($estatement_journalier),
                "estatement_hebdo" => nullToFalse($estatement_hebdo),
                "estatement_mensuel" => nullToFalse($estatement_mensuel),
            );
        }
        $sql = buildUpdateQuery($tableName, $tableFields, array('id_client' => $id_client, 'id_ag' => $id_ag, 'id_abonnement' => $id_abonnement));
    }
    elseif ($mode == "ajouter") {

        $identifiant = generateIdentifiant();

        if($id_service === "1") {
            //SMS_BANKING
            $tableFields = array(
                "id_client" => $id_client,
                "id_ag" => $id_ag,
                "identifiant" => $identifiant,
                "num_sms" => trim($num_sms),
                "langue" => $langue,
                "ewallet" => nullToFalse($ewallet),
                "id_prestataire" => $id_prestataire,
                "id_service" => $id_service,
                "id_cpte" => $id_cpte
            );
            
            // Update field num_tel in table ad_cli
            //$sql_update_ad_cli = buildUpdateQuery("ad_cli", array("num_tel" => trim($num_sms)), array('id_client' => $id_client, 'id_ag' => $id_ag));

            if (trim($password)!='') {
                $salt = generateRandomString(generateIdentifiant());
                $motdepasse = encodePassword(trim($password), $salt);

                $tableFields["motdepasse"] = $motdepasse;
                $tableFields["salt"] = $salt;
            }
        } else {
            //E-STATEMENT
            $tableFields = array(
                "id_client" => $id_client,
                "id_ag" => $id_ag,
                "langue" => $langue,
                "identifiant" => $identifiant,
                "id_service" => $id_service,
                "estatement_email" => $estatement_email,
                "estatement_journalier" => nullToFalse($estatement_journalier),
                "estatement_hebdo" => nullToFalse($estatement_hebdo),
                "estatement_mensuel" => nullToFalse($estatement_mensuel),
            );
            
            // Update field email in table ad_cli
            $sql_update_ad_cli = buildUpdateQuery("ad_cli", array("email" => trim($estatement_email)), array('id_client' => $id_client, 'id_ag' => $id_ag));
        }
        $sql = buildInsertQuery($tableName, $tableFields);
    }

    /*
    var_dump($sql);
    exit;
    */

    if ($sql != null) {
        $result = $db->query($sql);

        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        } else {
            if ($sql_update_ad_cli != null) {
                $result2 = $db->query($sql_update_ad_cli);

                if (DB :: isError($result2)) {
                    $dbHandler->closeConnection(false);
                    signalErreur(__FILE__, __LINE__, __FUNCTION__);
                }
            }            
        }
        //$dbHandler->closeConnection(true);
    }

    // ajouter dans la table ad_data_client_mensuel_abo && mettre à jour la table ad_abonnement
    if ($mode == "ajouter" && $id_service === "1") {
        insertStatCliAbn($id_client);
        check_client_salarie_ml($id_client);
    }

    return new ErrorObj(NO_ERR);
}

/**
 * Ré-initialiser un mot de passe
 *
 * @param Integer $id_abonnement : Identifiant de d'abonnement
 * 
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function resetMotDePasse($id_abonnement) {
    
    global $dbHandler, $global_id_agence, $global_id_client;

    $db = $dbHandler->openConnection();

    // Build insert string
    $id_client = $global_id_client;
    $id_ag = $global_id_agence;
    
    $sql = null;

    $tableName = "ad_abonnement";

    $tableFields = array(
        "date_mdp" => 'NOW()'
    );

    $sql = buildUpdateQuery($tableName, $tableFields, array('id_client' => $id_client, 'id_ag' => $id_ag, 'id_abonnement' => $id_abonnement));

    if ($sql != null) {
        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);
    }

    return new ErrorObj(NO_ERR);
}

/**
 * Delete an abonnement
 *
 * @param Integer $id_abonnement : Identifiant de d'abonnement
 * 
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function deleteAbonnement($id_abonnement) {
    
    global $dbHandler, $global_id_agence, $global_id_client;

    $db = $dbHandler->openConnection();

    // Build insert string
    $id_client = $global_id_client;
    $id_ag = $global_id_agence;
    
    $sql = null;

    $tableName = "ad_abonnement";

    $tableFields = array(
        "deleted" => 't'
    );

    $sql = buildUpdateQuery($tableName, $tableFields, array('id_client' => $id_client, 'id_ag' => $id_ag, 'id_abonnement' => $id_abonnement));

    if ($sql != null) {
        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);
    }

    return new ErrorObj(NO_ERR);
}

/* Générer Identifiant automatiquement à partir de l'id_agence et l'id_client de la base ADBanking */
function generateIdentifiant() {

    global $global_id_agence, $global_id_client;

    return sprintf("%s%s", trim($global_id_agence), str_pad(trim($global_id_client), 8, "0", STR_PAD_LEFT));
}

/* Encoder un mot de passe avec l'algorithme sha512 */
function encodePassword($plain_password, $salt) {
    
    $algorithm = 'sha512'; // Encryption algorithm
    $iterations = 5000; // Number of iterations to use to stretch the password hash
    
    $salted = $plain_password.'{'.$salt.'}';
    $digest = hash($algorithm, $salted, true);

    // "stretch" hash
    for ($i = 1; $i < $iterations; $i++) {
        $digest = hash($algorithm, $digest.$salted, true);
    }

    return base64_encode($digest);
}

/* Générer une chaine de texte aléatoirement */
function generateRandomString($str) {
    return md5(uniqid(mt_rand(0, 99999)) . $str);
}



/*
 * Check if the id_cpte is related to a client subscribed to SMS service
*/
function checkClientAbonnement($cpte_interne_cli){
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = sprintf(
    "SELECT cli.id_client
            FROM ad_cpt cpt
            JOIN ad_cli cli ON cpt.id_titulaire = cli.id_client
            JOIN ad_abonnement a ON cli.id_client = a.id_client
            WHERE a.id_ag = %d
            AND cpt.id_cpte = %s
            AND a.id_service = 1
            AND a.deleted = FALSE",
    $global_id_agence, $cpte_interne_cli
  );

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) {
    return NULL;
  }

  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

  return $DATAS;
}

/**
 * Retourne les infos du prestataire
 *
 * @param string $code_prestataire
 * @return |null
 */
function getPrestataireInfo($code_prestataire = null){
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT nom_prestataire, compte_comptable FROM ad_ewallet WHERE id_ag = numagc()";

  if ($code_prestataire != null) {
      $sql .= " and code_prestataire = '$code_prestataire'";
  } else {
      $sql .= " and code_prestataire = 'MTN_RW'";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) {
      return NULL;
  }

  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

  return $DATAS;
}

/**
 * Ajouter les stats du nouveau client abonné dans la table statistics mensuelle, ad_data_client_mensuel_abo
 * et table abonnement, ad_abonnement
 *
 * @param $id_client
 *
 * @return ErrorObj
 */
function insertStatCliAbn($id_client){
    global $dbHandler, $global_id_agence, $global_id_client;

    // $db = $dbHandler->openConnection();

    // $sql = "SELECT * FROM f_getDataClientAbo(" .$id_client.")";

    // $result = $db->query($sql);
    // if (DB::isError($result)) {
    //     $dbHandler->closeConnection(false);
    //     signalErreur(__FILE__,__LINE__,__FUNCTION__.' '.$sql);
    // }

    // $dbHandler->closeConnection(true);

    // return new ErrorObj(NO_ERR);


    // Recuperation du pourcentage du montant max
    $file_path = '/usr/share/adbanking/web/services/mobile_lending/parametrage_mobile_lending.csv';
    $fichier_lot = $file_path;

    if (!file_exists($fichier_lot)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_FICHIER_DONNEES);
    }


    $handle = fopen($fichier_lot, "r");
    $columns_mnt_max = array(41);
    $count = 0;
    while (($data_mnt_max = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count == 0) {
            $count++;
            continue;
        }
        foreach ($data_mnt_max as $index_mnt_max => $val_mnt_max) {
            if (in_array($index_mnt_max + 1, $columns_mnt_max) && $val_mnt_max != null) {
                ${"prc_mnt_max" . $index_mnt_max} = $val_mnt_max;
            }
        }
    }



    // recuperation du alpha de coefficient present
    $handle = fopen($fichier_lot, "r");
    $columns_coeff_present= array(43);
    $count = 0;
    while (($data_coeff_present = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count == 0) {
            $count++;
            continue;
        }
        foreach ($data_coeff_present as $index_coeff_present => $val_coeff_present) {
            if (in_array($index_coeff_present + 1, $columns_coeff_present) && $val_coeff_present != null) {
                ${"coeff_present" . $index_coeff_present} = $val_coeff_present;
            }
        }
    }

    // montant max empruner
    $handle = fopen($fichier_lot, "r");
    $columns_mnt_max_emprunt = array(35);
    $count_coeff = 0;
    while (($data_mnt_emprunter = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_mnt_emprunter as $index_mnt_emprunter => $val_mnt_emprunter) {
            if (in_array($index_mnt_emprunter + 1, $columns_mnt_max_emprunt)) {
                if ($val_mnt_emprunter != null){
                    $mnt_max_emprunter = $val_mnt_emprunter;
                }
            }
        }
    }
    fclose($handle);

    // montant max nouveau client
    $handle = fopen($fichier_lot, "r");
    $columns_mnt_max_new_client = array(36);
    $count_coeff = 0;
    while (($data_mnt_max_new_client = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_mnt_max_new_client as $index_mnt_max_new_client => $val_mnt_max_new_client) {
            if (in_array($index_mnt_max_new_client + 1, $columns_mnt_max_new_client)) {
                if ($val_mnt_max_new_client != null){
                    $mnt_max_new_client = $val_mnt_max_new_client;
                }
            }
        }
    }
    fclose($handle);

    // Lecture des coefficients def irregularite
    $handle = fopen($fichier_lot, "r");
    $columns_coeff = array(45);
    $count_coeff = 0;
    while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_coeff as $index_coeff => $val_coeff) {
            if (in_array($index_coeff + 1, $columns_coeff)) {
                if ($val_coeff != null){
                    $coeff_def_irregularite= $val_coeff;
                }
            }
        }
    }

    fclose($handle);

    // Appelle de la fonction de mise a jour des donnees clients
    $db = $dbHandler->openConnection();
    $sql = "SELECT mise_a_jour_donnee_abonnee_alone(".$prc_mnt_max40.",".$coeff_present42.",".$mnt_max_emprunter.", ".$mnt_max_new_client.",".$coeff_def_irregularite.",".$id_client.");";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        echo "Failed => Fonction mise a jour des clients abonnees";echo "\n";
        $dbHandler->closeConnection(false);
    }else{
        $dbHandler->closeConnection(true);
    }


    /******************************************UPDATE TRACNHE DATA**********************************************************/


    // SAlaire moyen
    $handle = fopen($fichier_lot, "r");

    $columns_sal_moy = array(21,22,23);
    $count_sal_moy = 0;

    while (($data_sal_moy = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_sal_moy == 0) {
            $count_sal_moy++;
            continue;
        }
        foreach ($data_sal_moy as $index_sal_moy => $val_sal_moy) {
            if (in_array($index_sal_moy + 1, $columns_sal_moy)) {
                ${"sal_moy" . $index_sal_moy} = $val_sal_moy;
            }
        }
        if (!empty($sal_moy22)) {
            if ($sal_moy20 == NULL){
                $sal_moy20 = 0;
            }
            $db = $dbHandler->openConnection();
            $sql_sal_moy = "UPDATE ml_donnees_client_abonnees SET tranche_sal_moyen = $sal_moy22 WHERE salaire_moyen>= $sal_moy20 and salaire_moyen <= $sal_moy21";

            $result_mnt_sal = $db->query($sql_sal_moy);
            if (DB::isError($result_mnt_sal)) {
                echo "Failed";
                echo "\n";
                $dbHandler->closeConnection(false);
            } else {
                $dbHandler->closeConnection(true);
            }
        }
    }
    fclose($handle);


    //irregularite
    $handle = fopen($fichier_lot, "r");

    $columns_irregularite= array(9,10,11);
    $count_irregularite = 0;

    while (($data_irregularite = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if($count_irregularite == 0){ $count_irregularite++; continue; }
        foreach ($data_irregularite as $index_irregularite => $val_irregularite) {
            if (in_array($index_irregularite + 1, $columns_irregularite)) {
                ${"irregularite" . $index_irregularite} = $val_irregularite;
            }
        }

        if (!empty($irregularite10)) {
            $db = $dbHandler->openConnection();
            $sql_irregularite= "UPDATE ml_donnees_client_abonnees SET tranche_irregularite = $irregularite10 WHERE tx_irregularite>= $irregularite8 and tx_irregularite <= $irregularite9";
            $result_mnt_dem = $db->query($sql_irregularite);
            if (DB::isError($result_mnt_dem)) {
                echo "Failed";echo "\n";
                $dbHandler->closeConnection(false);
            }else{
                $dbHandler->closeConnection(true);
            }
        }
    }
    fclose($handle);

    // Lecture de la tranche nbre de credit
    $handle = fopen($fichier_lot, "r");

    $columns_nb_credit = array(17,18,19);
    $count_nb_credit = 0;

    while (($data_nb_credit = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if($count_nb_credit == 0){ $count_nb_credit++; continue; }
        foreach ($data_nb_credit as $index_nb_credit => $val_nb_credit) {
            if (in_array($index_nb_credit + 1, $columns_nb_credit)) {
                ${"nb_credit" . $index_nb_credit} = $val_nb_credit;
            }
        }

        if (!empty($nb_credit18)) {
            $db = $dbHandler->openConnection();
            $sql_nbre_credit= "UPDATE ml_donnees_client_abonnees SET tranche_nbre_credit = $nb_credit18 WHERE nbre_credit>= $nb_credit16 and nbre_credit <= $nb_credit17";
            $result_nbre_credit= $db->query($sql_nbre_credit);
            if (DB::isError($result_nbre_credit)) {
                echo "Failed";echo "\n";
                $dbHandler->closeConnection(false);
            }else{
                $dbHandler->closeConnection(true);
            }
        }
    }
    fclose($handle);

    //Lecture mnt tot emprunter
    $handle = fopen($fichier_lot, "r");

    $columns_salaire = array(5,6,7);
    $count_salaire = 0;

    while (($data_salaire = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if($count_salaire == 0){ $count_salaire++; continue; }
        foreach ($data_salaire as $index_salaire => $val_salaire) {
            if (in_array($index_salaire + 1, $columns_salaire)) {
                ${"salaire" . $index_salaire} = $val_salaire;
            }
        }
        if (!empty($salaire6)) {
            if ($salaire4 == NULL){
                $salaire4 = 0;
            }
            $db = $dbHandler->openConnection();
            $sql_mnt_dem= "UPDATE ml_donnees_client_abonnees SET tranche_tot_emprunter= $salaire6 WHERE mnt_tot_emprunter>= $salaire4 and mnt_tot_emprunter <= $salaire5";
            $result_mnt_dem = $db->query($sql_mnt_dem);
            if (DB::isError($result_mnt_dem)) {
                echo "Failed";echo "\n";
                $dbHandler->closeConnection(false);
            }else{
                $dbHandler->closeConnection(true);
            }
        }
    }
    fclose($handle);

    //Lecture tranche age
    $handle = fopen($fichier_lot, "r");

    $columns_age = array(1,2,3);
    $count_age = 0;

    while (($data_age = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if($count_age == 0){ $count_age++; continue; }
        foreach ($data_age as $index_age => $val_age) {
            if (in_array($index_age + 1, $columns_age)) {
                ${"age" . $index_age} = $val_age;
            }
        }
        if (!empty($age2)) {
            if ($age0 == NULL){
                $age0 = 0;
            }
            $db = $dbHandler->openConnection();
            $sql_age= "UPDATE ml_donnees_client_abonnees SET tranche_age= $age2 WHERE age>= $age0 and age <= $age1";
            $result_age = $db->query($sql_age);
            if (DB::isError($result_age)) {
                echo "Failed age";echo "\n";
                $dbHandler->closeConnection(false);
            }else{
                $dbHandler->closeConnection(true);
            }
        }
    }
    fclose($handle);

//Lecture tranche lg_histo
    $handle = fopen($fichier_lot, "r");

    $columns_lg_histo= array(13,14,15);
    $count_lg_histo = 0;

    while (($data_lg_histo = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if($count_lg_histo == 0){ $count_lg_histo++; continue; }
        foreach ($data_lg_histo as $index_lg_histo => $val_lg_histo) {
            if (in_array($index_lg_histo + 1, $columns_lg_histo)) {
                ${"lg_histo" . $index_lg_histo} = $val_lg_histo;
            }
        }
        if (!empty($lg_histo14)) {
            if ($lg_histo12 == NULL){
                $lg_histo12 = 0;
            }
            $db = $dbHandler->openConnection();
            $sql_lg_histo= "UPDATE ml_donnees_client_abonnees SET tranche_lg_histo= $lg_histo14 WHERE lg_histo>= $lg_histo12 and lg_histo <= $lg_histo13";
            $result_lg_histo = $db->query($sql_lg_histo);
            if (DB::isError($result_lg_histo)) {
                echo "Failed lg histo";echo "\n";
                $dbHandler->closeConnection(false);
            }else{
                $dbHandler->closeConnection(true);
            }
        }
    }
    fclose($handle);

    // Lecture des coefficients passe
    $handle = fopen($fichier_lot, "r");

    $columns_coeff = array(47);
    $count_coeff = 0;

    while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_coeff as $index_coeff => $val_coeff) {
            if (in_array($index_coeff + 1, $columns_coeff)) {
                if ($val_coeff != null){
                    $coeff_passe = $val_coeff;
                }
            }
        }
    }

    fclose($handle);

    // Lecture des coefficients present
    $handle = fopen($fichier_lot, "r");

    $columns_coeff = array(49);
    $count_coeff = 0;

    while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_coeff as $index_coeff => $val_coeff) {
            if (in_array($index_coeff + 1, $columns_coeff)) {
                if ($val_coeff != null){
                    $coeff_present = $val_coeff;
                }
            }
        }
    }

    fclose($handle);

    // Lecture des coefficients futur
    $handle = fopen($fichier_lot, "r");

    $columns_coeff = array(51);
    $count_coeff = 0;

    while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_coeff as $index_coeff => $val_coeff) {
            if (in_array($index_coeff + 1, $columns_coeff)) {
                if ($val_coeff != null){
                    $coeff_futur = $val_coeff;
                }
            }
        }
    }

    fclose($handle);

    // Lecture des coefficients present sans credit
    $handle = fopen($fichier_lot, "r");

    $columns_coeff = array(53);
    $count_coeff = 0;

    while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_coeff as $index_coeff => $val_coeff) {
            if (in_array($index_coeff + 1, $columns_coeff)) {
                if ($val_coeff != null){
                    $coeff_present_new_client = $val_coeff;
                }
            }
        }
    }

    fclose($handle);

    // Lecture des coefficients futur sans credit
    $handle = fopen($fichier_lot, "r");

    $columns_coeff = array(55);
    $count_coeff = 0;

    while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_coeff as $index_coeff => $val_coeff) {
            if (in_array($index_coeff + 1, $columns_coeff)) {
                if ($val_coeff != null){
                    $coeff_futur_new_client = $val_coeff;
                }
            }
        }
    }

    fclose($handle);

    // Lecture des coefficients passe sans combinaison avec credit
    $handle = fopen($fichier_lot, "r");

    $columns_coeff = array(57);
    $count_coeff = 0;

    while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_coeff as $index_coeff => $val_coeff) {
            if (in_array($index_coeff + 1, $columns_coeff)) {
                if ($val_coeff != null){
                    $coeff_passe_sans_combi_avec_credit= $val_coeff;
                }
            }
        }
    }

    fclose($handle);

    // Lecture des coefficients present sans combinaison avec credit
    $handle = fopen($fichier_lot, "r");

    $columns_coeff = array(59);
    $count_coeff = 0;

    while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_coeff as $index_coeff => $val_coeff) {
            if (in_array($index_coeff + 1, $columns_coeff)) {
                if ($val_coeff != null){
                    $coeff_present_sans_combi_avec_credit= $val_coeff;
                }
            }
        }
    }

    fclose($handle);

    // Lecture des coefficients present sans combinaison sans credit
    $handle = fopen($fichier_lot, "r");

    $columns_coeff = array(61);
    $count_coeff = 0;

    while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_coeff == 0) {
            $count_coeff++;
            continue;
        }
        foreach ($data_coeff as $index_coeff => $val_coeff) {
            if (in_array($index_coeff + 1, $columns_coeff)) {
                if ($val_coeff != null){
                    $coeff_present_sans_combi_sans_credit= $val_coeff;
                }
            }
        }
    }

    fclose($handle);

    /*****************************FIN UPDATE TRANCHE DATA CLIENT***********************************************/
    // Recuperation score futur dans la table des combinaisons globales
    $data_client_abonnee = getDataClientAbonnee($id_client);
    //var_dump($data_client_abonnee);
    // fonction getLocalisationIMF
    $localisation_imf = getLocalisationIMF();
    $loc_imf = $localisation_imf['ml_localisation'];
    foreach ($data_client_abonnee as $index => $value) {
        $combinaison_pleine = 'f';
        $score = 0;
        $db = $dbHandler->openConnection();
        $sql = "SELECT score_retard, nbre_dossier FROM ml_combinaison_global WHERE  tranche_sal_moyen = " . $value['tranche_sal_moyen'] . " AND  tranche_irregularite= " . $value['tranche_irregularite'] . " AND tranche_nbre_credit= " . $value['tranche_nbre_credit'] . " AND tranche_tot_emprunter= " . $value['tranche_tot_emprunter']." AND tranche_localisation = ".$loc_imf;

        $result = $db->query($sql);
        if (DB::isError($result)) {
            echo "Failed => " . $sql;
            echo "\n";
            $dbHandler->closeConnection(false);
        }
        $row = $result->fetchrow();
        $db = $dbHandler->closeConnection(true);
        $score = $row[0];
        $nbre_doss = $row[1];
        if ($nbre_doss >= 3){
            $combinaison_pleine = 't';
        }


        /******************Recuperation des Bonus garantit****************/
        $ratio_bonus_gar = 0;
        $bonus_gar_calculer = 0;
        $garantit_en_cours = getGarantiEnCours($value['client']);
        if ($garantit_en_cours > 0) {
            $ratio_bonus_gar = ($garantit_en_cours / $value['mnt_restant_du']) * 100;

            // Lecture des bonus de garanti
            $handle = fopen($fichier_lot, "r");
            echo "\n";
            echo "\n";
            $columns_bonus = array(31,32,33);
            $count_bonus = 0;

            while (($data_bonus = fgetcsv($handle, 2000, ",")) !== FALSE) {
                if ($count_bonus == 0) {
                    $count_bonus++;
                    continue;
                }
                foreach ($data_bonus as $index_bonus => $val_bonus) {
                    if (in_array($index_bonus + 1, $columns_bonus)) {
                        ${"bonus_gar" . $index_bonus} = $val_bonus;
                    }
                }

                if (!empty($bonus_gar32)) {
                    if (($ratio_bonus_gar > $bonus_gar30) && ($ratio_bonus_gar <= $bonus_gar31)) {
                        $bonus_gar_calculer = $bonus_gar32;
                    }
                }
            }
            fclose($handle);
        }
        /*****************************************************/

        // Lecture de la constante 1
        $handle = fopen($fichier_lot, "r");
        echo "\n";echo "\n";
        $columns_c1 = array(63);
        $count_c1 = 0;
        while (($data_c1 = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_c1 == 0) {
                $count_c1++;
                continue;
            }
            foreach ($data_c1 as $index_c1 => $val_c1) {
                if (in_array($index_c1 + 1, $columns_c1)) {
                    if ($val_c1 != null){
                        $coeff_c1= $val_c1;
                    }
                }
            }
        }

        fclose($handle);

        // Lecture de la constante 2
        $handle = fopen($fichier_lot, "r");
        echo "\n";echo "\n";
        $columns_c2 = array(65);
        $count_c2 = 0;
        while (($data_c2 = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_c2 == 0) {
                $count_c2++;
                continue;
            }
            foreach ($data_c2 as $index_c2 => $val_c2) {
                if (in_array($index_c2 + 1, $columns_c2)) {
                    if ($val_c2 != null){
                        $coeff_c2= $val_c2;
                    }
                }
            }
        }

        fclose($handle);

        // Lecture de la constante 3
        $handle = fopen($fichier_lot, "r");
        echo "\n";echo "\n";
        $columns_c3 = array(67);
        $count_c3 = 0;
        while (($data_c3 = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_c3 == 0) {
                $count_c3++;
                continue;
            }
            foreach ($data_c3 as $index_c3 => $val_c3) {
                if (in_array($index_c3 + 1, $columns_c3)) {
                    if ($val_c3 != null){
                        $coeff_c3= $val_c3;
                    }
                }
            }
        }

        fclose($handle);

        // Lecture de la constante 4
        $handle = fopen($fichier_lot, "r");
        echo "\n";echo "\n";
        $columns_c4 = array(69);
        $count_c4 = 0;
        while (($data_c4 = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_c4 == 0) {
                $count_c4++;
                continue;
            }
            foreach ($data_c4 as $index_c4 => $val_c4) {
                if (in_array($index_c4 + 1, $columns_c4)) {
                    if ($val_c4 != null){
                        $coeff_c4= $val_c4;
                    }
                }
            }
        }

        fclose($handle);


        //calcul du score finale
        if ($value['nbre_credit'] > 0){
            if ($score == 10000){
                $score_final = $coeff_passe_sans_combi_avec_credit * $value['score_passe'] + $coeff_present_sans_combi_avec_credit * $value['score_present'] + $bonus_gar_calculer +$coeff_c3;
                $score_final = round($score_final, 2);
            }else {
                $score_final = $coeff_passe * $value['score_passe'] + $coeff_present * $value['score_present'] + $coeff_futur * $score + $bonus_gar_calculer+$coeff_c1;
                $score_final = round($score_final, 2);
            }
        }
        else{
            if ($score == 10000){
                $score_final =  $coeff_present_sans_combi_sans_credit * $value['score_present'] +$bonus_gar_calculer+ $coeff_c4;
                $score_final = round($score_final, 2);
            }else {
                $score_final = $coeff_present_new_client * $value['score_present'] + $coeff_futur_new_client * $score +$bonus_gar_calculer + $coeff_c2;
                $score_final = round($score_final, 2);
            }
        }
        //Malus Actif moins de 3 mois
        if ($value['actif_3_mois'] == 'f'){
            $score_final = $score_final - 80;
        }
        if ($value['salaire_moyen_non_nul'] == 'f'){
            $score_final = $score_final - 80;
        }


        $db = $dbHandler->openConnection();
        $sql_update_score_futur = "UPDATE ml_donnees_client_abonnees SET score_futur = $score, score_final = $score_final, bonus_gar = $bonus_gar_calculer WHERE client =" . $value['client'];

        $result_score_futur = $db->query($sql_update_score_futur);
        if (DB::isError($result_score_futur)) {
            echo "Failed => ";
            echo "\n";
            $dbHandler->closeConnection(false);
        } else {
            //        echo "Succes";
            //        echo "\n";
            $dbHandler->closeConnection(true);
        }

        $db = $dbHandler->openConnection();
        $sql_update_abo = "UPDATE ad_abonnement SET ml_score = $score_final WHERE id_client =" . $value['client']." AND deleted = 'f' AND id_service = 1";
        $result_abo = $db->query($sql_update_abo);
        if (DB::isError($result_abo)) {
            echo "Failed";
            echo "\n";
            $dbHandler->closeConnection(false);
        } else {
            //        echo "Succes";
            //        echo "\n";
            $dbHandler->closeConnection(true);
        }
    }
    // $html_msg = new HTML_message("Confirmation de la mise à jour des prix des produits");

    $demande_msg = "Votre automatisme de mise à jour est reussi!";


    // $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

    // $html_msg->addButton("BUTTON_OK", 'Gfp-1');
    // $html_msg->buildHTML();
    // echo $html_msg->HTML_code;

    return new ErrorObj(NO_ERR, $demande_msg);
}

/*
 * Cette fonction renvoie toutes les informations relatives à un client abonné au service Mobile
 */
function getOneOrNullClientAbonnementInfo($identifiant) {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = sprintf("SELECT a.*,e.* FROM ad_abonnement a LEFT JOIN ad_ewallet e ON a.id_prestataire=e.id_prestataire WHERE a.id_ag=%d AND a.deleted='f' AND a.identifiant LIKE '%s' AND a.id_service=%d ORDER BY a.date_creation DESC LIMIT 1", $global_id_agence, $identifiant, 1);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return $DATAS;
}

function check_client_salarie_ml($id_client){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();
    $sql="select recup_salarie_defini($id_client);";
    $result= $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    return true;

}

function getDataClientAbonneeActif($id_client){
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ad_abonnement where id_client = $id_client AND deleted = 'f' AND id_service = 1 " ;

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return $DATAS;
}