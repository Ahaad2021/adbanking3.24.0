<?php

/**
 * Liste des paramètres for a code_param
 * @param array $fields_array tableau contenant les nom des colonnes à selectionner
 * @return array Liste des param extras
 *
 */
function getJasperParamExtras($code_param) {
    
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = sprintf("SELECT * FROM ad_jasper_param_extras WHERE id_ag=%d AND code_param='%s' ORDER BY id DESC LIMIT 1", $global_id_agence, $code_param);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $datas = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return $datas;
}

/**
 * Insertion dans la table ad_jasper_param_extras
 *
 * @param String $code_param : 
 * @param String $type_lsb : 
 * @param Integer $table_name_param : 
 * @param Boolean $key_param : 
 * @param Integer $value_param : 
 * 
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function insertJasperParamExtras($code_param, $type_lsb, $table_name_param, $key_param, $value_param) {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = null;

    $tableFields = array(
        "code_param"        => trim($code_param),
        "type_lsb"          => trim($type_lsb),
        "table_name_param"  => trim($table_name_param),
        "key_param"         => trim($key_param),
        "value_param"       => trim($value_param),
        "id_ag"             => trim($global_id_agence),
    );
    $sql = buildInsertQuery("ad_jasper_param_extras", $tableFields);

    //var_dump($sql);
    //exit;

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
 * Modification dans la table ad_jasper_param_extras
 */
function updateJasperParamExtras($ancien_code_param, $code_param, $type_lsb, $table_name_param, $key_param, $value_param) {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $Fields = array();
    $Where = array();

    $Fields["type_lsb"]         = trim($type_lsb);
    $Fields["code_param"]       = trim($code_param);
    $Fields["table_name_param"] = trim($table_name_param);
    $Fields["key_param"]        = trim($key_param);
    $Fields["value_param"]      = trim($value_param);

    $Where["code_param"]    = trim($ancien_code_param);
    $Where["id_ag"]         = $global_id_agence;

    $sql = buildUpdateQuery("ad_jasper_param_extras", $Fields, $Where);

    //var_dump($sql);
    //exit;

    // Exécution de la requête
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n".$result->getMessage());
    }

    $dbHandler->closeConnection(true);

    return true;
}

 /**
 * Fonction : suppression du paramètre jasper extra
 * param $where array tableau des la condition du paramètre à supprimer
 * return errObj object
 */
function deleteJasperParamExtras($where) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = buildDeleteQuery ("ad_jasper_param_extras", $where);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);

        return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
    } else {
        $err = deleteJasperParamLsb($where);
    }

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}

/**
 * Liste des paramètres lsb pour un code_param
 * @param array $fields_array tableau contenant les nom des colonnes à selectionner
 * @return array Liste des param lsb
 *
 */
function getListeJasperParamLsb($code_param) {
    
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = sprintf("SELECT cle, valeur FROM ad_jasper_param_lsb WHERE id_ag=%d AND code_param='%s' ORDER BY id ASC", $global_id_agence, $code_param);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    
    $params = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $params[trim($row['cle'])]= trim($row['valeur']);
    }

    return $params;
}

/**
 * Paramètres lsb pour code_param et cle
 * @param array $fields_array tableau contenant les nom des colonnes à selectionner
 * @return array Liste des param lsb
 *
 */
function getJasperParamLsbByCle($code_param, $cle) {
    
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = sprintf("SELECT cle, valeur FROM ad_jasper_param_lsb WHERE id_ag=%d AND code_param='%s' AND cle='%s' ORDER BY id ASC LIMIT 1", $global_id_agence, $code_param, $cle);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    
    if ($result->numRows() == 0) {
        return NULL;
    }

    $datas = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return $datas;
}

/**
 * Insertion dans la table ad_jasper_param_lsb
 *
 * @param String $code_param
 * @param String $cle
 * @param String $valeur
 * 
 * @return ErrorObj = NO_ERR si tout s'est bien passé, SignalErreur si pb de la BD
 */
function insertJasperParamLsb($code_param, $cle, $valeur) {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = null;

    $tableFields = array(
        "code_param"    => $code_param,
        "cle"           => $cle,
        "valeur"        => $valeur,
        "id_ag"         => $global_id_agence,
    );
    $sql = buildInsertQuery("ad_jasper_param_lsb", $tableFields);

    //var_dump($sql);
    //exit;

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
 * Modification dans la table ad_jasper_param_lsb
 */
function updateJasperParamLsb($code_param, $ancien_cle, $cle, $valeur) {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $Fields = array();
    $Where = array();

    $Fields["cle"]      = $cle;
    $Fields["valeur"]   = $valeur;

    $Where["code_param"]    = $code_param;
    $Where["cle"]           = $ancien_cle;
    $Where["id_ag"]         = $global_id_agence;

    $sql = buildUpdateQuery("ad_jasper_param_lsb", $Fields, $Where);

    //var_dump($sql);
    //exit;

    // Exécution de la requête
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n".$result->getMessage());
    }

    $dbHandler->closeConnection(true);

    return true;
}

 /**
 * Fonction : suppression du paramètre jasper lsb
 * param $where array tableau des la condition du paramètre à supprimer
 * return errObj object
 */
function deleteJasperParamLsb($where) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = buildDeleteQuery ("ad_jasper_param_lsb", $where);

    //var_dump($sql);
    //exit;

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);

        return new ErrorObj(ERR_DB_SQL,array("Fichier"=>__FILE__,"ligne"=>__LINE__,"Fonction"=>__FUNCTION__,"INFO"=>$result->userinfo));
    }

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}

 /**
 * Liste des parametres du menu déroulant
 * @return array Liste des parametres (cle=>valeur)
 *
 */
function getJasperParamLsbOptions($code_param) {

    global $dbHandler, $global_id_agence;
    
    $db = $dbHandler->openConnection();

    $paramsExtras = getJasperParamExtras($code_param);

    $option_arr = array();

    if($paramsExtras['type_lsb'] == "dynamic") {

        $table_name = trim($paramsExtras["table_name_param"]);
        $key = trim($paramsExtras["key_param"]);
        $value = trim($paramsExtras["value_param"]);

        if ($table_name != '' && $key != '' && $value != '') {

            $sql = "SELECT $key, $value FROM $table_name";

            $result = $db->query($sql);
            if (DB::isError($result)) {
                $dbHandler->closeConnection(false);
                signalErreur(__FILE__,__LINE__,__FUNCTION__);
            }

            $dbHandler->closeConnection(true);

            while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
                $option_arr[trim($row[$key])]= trim($row[$value]);
            }
        }
        
    } elseif($paramsExtras['type_lsb'] == "static") {
        
        $option_arr = getListeJasperParamLsb($code_param);
    }

    return $option_arr;
}

 /**
 * Liste des parammetres jasper
 * @return array Liste des rapports jaspers array (code_param=>type_param)
 *
 */
function getJasperParamCodeByType() {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT ajp.code_param, ajp.type_param FROM ad_jasper_param ajp INNER JOIN ad_jasper_param_extras ajpe ON ajp.code_param=ajpe.code_param WHERE ajpe.type_lsb='static' AND ajpe.id_ag=$global_id_agence ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  $params = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      $params[trim($row['code_param'])]= trim($row['type_param']);
  }

  return  $params;
}
