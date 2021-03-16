<?php

/*require_once 'functions.php';
require_once 'Erreur.php';
require_once 'divers.php';
//require_once 'VariablesGlobales.php';*/
//require_once 'defection_client_par_lot.php';
//require_once 'lib/misc/VariablesGlobales.php';
//require_once 'Erreurs_defection.php';
//require_once 'DB.php';
require_once 'erreur.php';

global $global_id_exo,$global_multidevise,$global_id_agence,$dbHandler;
//$value = getGlobalDatas();
//$global_id_exo = $value['exercice'];
//$global_multidevise = $value['multidevise'];
//$global_id_agence = getNumAgence();

function buildUpdateQuery ($TableName, $Fields, $Where="") {

    if (sizeof($Fields) == 0){
        echo "Aucun champ à mettre à jour fonction buildUpdateQuery ! \n";
        exit();
        //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Aucun champ à mettre à jour"));
    }
    $Fields = array_make_pgcompatible($Fields);
    reset($Fields);
    $sql = "UPDATE $TableName SET ";
    while (list($key, $value) = each($Fields)) {
        if ("$value" == '')
            $sql .= $key." = NULL, ";
        else
            $sql .= $key." = ".$value.", ";
    }
    $sql = substr($sql, 0, strlen($sql) - 2);
    if (is_array($Where)) {
        $sql .= " WHERE  ";
        while (list($key, $value) = each($Where))
            $sql .= " $key = $value AND";
        $sql = substr($sql, 0, strlen($sql) - 3);
    }
    $sql .=";";
    return $sql;
}
function array_make_pgcompatible($ary) {
    if (! is_array($ary)){
        echo "Fonction array_make_pgcompatible : L'argument attendu est un array \n";
        exit();
        //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'argument attendu est un array"
    }
    foreach ($ary AS $key => $value)
        $ary[$key] = string_make_pgcompatible($ary[$key]);
    return $ary;
}
function string_make_pgcompatible($str) {
    return addslashes($str);
}

function buildInsertQuery ($TableName, $Fields) {

    if (count($Fields) == 0){
        echo "Fonction buildInsertQuery : Aucun champ à ajouter! \n";
        exit();
        //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Aucun champ à ajouter"));
    }
    // On rend le tableau PG Compilant
    $Fields = array_make_pgcompatible($Fields);
    $sql = "INSERT INTO $TableName (";
    reset($Fields);
    while (list($key, $value) = each($Fields))
        $sql .= "$key, ";
    $sql = substr($sql, 0, strlen($sql) - 2);
    $sql .=") VALUES (";
    reset($Fields);
    while (list($key, $value) = each($Fields)) {
        if ($value == "")
            $sql .= "NULL, ";
        else
            $sql .="'$value', ";
    };
    $sql = substr($sql, 0, strlen($sql) - 2);
    $sql .=");";

    return $sql;
}
?>