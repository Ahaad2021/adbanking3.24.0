<?php

/**
 * @package Ifutilisateur
 */
/* interface.php
   Contient toutes les procédures stockées nécessaires à HTML_GEN
   TF - 08 jan 2002 */

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/multilingue/utils.php';

function getFieldList($tableName) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT ident FROM tableliste WHERE nomc = '".$tableName."';";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }
  $tablen_row = $result->fetchRow();
  $tablen = $tablen_row[0];
  $sql = "SELECT nchmpc FROM d_tableliste WHERE tablen = ".$tablen.";";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }
  $fieldList = array();
  while ($c = $result->fetchRow())
    array_push($fieldList, $c[0]);
  $dbHandler->closeConnection(true);
  return $fieldList;
}

/**
 * Renvoie le résultat d'une requête SQL sur une table.
 * SELECT $fields FROM $table WHERE $condition
 * @param str $table Table dans laquelle on cherche
 * @param str $fields Champs que l'on veut retrouver
 * @param str $condition Clause where que l'on veut utiliser
 * @return array Tableau associatif des champs recherchés
 * 	signalErreur si on revoie plus d'une ligne
 */
function getDatas ($table, $fields, $conditions) {
  global $dbHandler;
  global $global_id_agence;
  $db = $dbHandler->openConnection();
  reset($fields);
  $fieldsstr = "";
  while (list($key, $ff) = each($fields)) {
    $fieldsstr .= $ff->ShortName.", ";
  }
  $fieldsstr = substr($fieldsstr, 0, strlen($fieldsstr) - 2);
  $wherestr = " WHERE ";
  reset($conditions);
  while (list($key,$cond) = each($conditions) ) {
    $wherestr .= $cond->ShortName." = '" . addslashes($cond->Value) . "' AND ";
  }
  $wherestr .=" id_ag=$global_id_agence"; 
  $sql = "SELECT ".$fieldsstr." FROM " . $table  . $wherestr;

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }
  if ($result->numRows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $sql); // "$sql a donné 0 ou plus d'1 résultat"
  }
  $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
  table_get_traductions($table,$row);
  $dbHandler->closeConnection(true);
  return $row;
}

function getFieldsFromTable ($Table)
/* Renvoie un rowset contenant la liste de tous les champs d'une table donnée */
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT d.* FROM d_tableliste d, tableliste t WHERE
         t.ident=d.tablen and t.nomc = '" . $Table . "'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }
  //FIXME: petite question: il manquerait pas un closeConnection ici? Il faut voir ce qu'on fait plus loin dans le code
  return $result;
}

function makeListFromTable ($tableName)
// Fonction qui crée un tableau avec toutes les valeurs d'une table donnée
// IN : $tableName : Nom de la table
// OUT: un array de type (clé => string)

{
  global $adsys;
  global $dbHandler;
  global $global_langue_utilisateur;
  $db = $dbHandler->openConnection();
  // Voir si c'est une table en dur ou soft
  $sql = "SELECT ident,nomc,is_table FROM tableliste WHERE nomc = '$tableName';";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }
  if ($result->numRows() <= 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "La table $tableName n'existe pas"
  }
  $tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC);
  if ($tmprow['is_table'] == 'f') {
    $temparray = array();
    reset($adsys[$tableName]);
    while (list($key, $value) = each($adsys[$tableName]))
      $temparray[$key] = _($value);
  } else {
    // Get all the OnSelect fields of this table
    $qrefname = "SELECT nchmpc,traduit FROM d_tableliste WHERE onslct=true AND tablen=".$tmprow["ident"].";";

    $arefname = $db->query($qrefname);
    if (DB::isError($arefname)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $arefname->getMessage()
    }
    $REFNAME = "";
    while ($temprow = $arefname->fetchRow(DB_FETCHMODE_ASSOC))
      if ($temprow["traduit"] == 't') // Si le champ est traduit dans la DB (cf table ad_traductions), on récupère la traduction
        $REFNAME .= "traduction(".$temprow["nchmpc"].",'$global_langue_utilisateur'), ";
      else
        $REFNAME .= $temprow["nchmpc"].", ";
    $REFNAME = substr($REFNAME, 0, strlen($REFNAME)-2);

    // REFNAME is a list of OnSelcted fields for the referenced table
    // Recherche de la clé primaire
    $sql = "SELECT nchmpc FROM d_tableliste WHERE tablen = '".$tmprow['ident']."' AND ispkey = 't'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
    }
    if ($result->numRows() == 0 || $result->numRows() > 1) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "La clé primaire est mal renseignée pour $tableName"
    }
    $tmprow = $result->fetchrow();
    $pkey = $tmprow[0];
    /* Select all the $key=>$value pair for the HTML SELECT. */
    $sql = "SELECT $pkey, $REFNAME FROM $tableName ORDER BY $pkey";

    $ref = $db->query($sql);
    if (DB::isError($ref)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $ref->getMessage()
    }
    $temparray = array();
    while ( $tmprow = $ref->fetchRow() ) {
      $Display = "";
      next($tmprow);
      while (list($key, $value) = each ($tmprow)) {
        $Display .= $value." ";
      }
      $temparray[$tmprow[0]] = $Display;
    }
  }
  $dbHandler->closeConnection(true);
  return $temparray;
}

function getReferencedFields ($RefField, $RefValue=NULL) {
  global $adsys;
  global $dbHandler;
  global $global_langue_utilisateur;

  $db = $dbHandler->openConnection();
  // Get the field name and table ID of the referenced field
  $qrefident = "SELECT nchmpc, tablen FROM d_tableliste WHERE ident='$RefField';";

  $arefident = $db->query($qrefident);
  if (DB::isError($arefident)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $arefident->getMessage()
  }
  if ( ($arefident->numRows() <= 0) || ($arefident->numRows() > 1) ) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  "Zéro ou Plusieurs entrées portent le même ID dans d_tableliste pour $RefField"
  }
  $temprow = $arefident->fetchRow();
  // REFIDENT is field name of the referenced field.
  $REFIDENT = $temprow[0];
  $REFTABLEID = $temprow[1];
  // Get the name of the referenced table
  $qreftable = "SELECT nomc, is_table FROM tableliste WHERE ident=$REFTABLEID";

  $areftable = $db->query($qreftable);
  if (DB::isError($areftable)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $areftable->getMessage()
  }

  $temprow = $areftable->fetchRow();

  $REFTABLE = $temprow[0];
  $is_table = $temprow[1];
  if ($is_table == 't') {
    // Get all the OnSelect fields of this table
    $qrefname = "SELECT nchmpc,traduit FROM d_tableliste WHERE onslct=true AND tablen=$REFTABLEID ORDER BY ident";

    $arefname = $db->query($qrefname);
    if (DB::isError($arefname)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $arefname->getMessage()
    }
    $REFNAME = "";
    while ($temprow = $arefname->fetchRow())
      if ($temprow[1] == "t") // Si le champ est traduit dans la DB (cf table ad_traductions), on récupère la traduction
        $REFNAME .= "traduction($temprow[0],'$global_langue_utilisateur'), ";
      else
        $REFNAME .= $temprow[0].", ";
    $REFNAME = substr($REFNAME, 0, strlen($REFNAME)-2);
    // REFNAME is a list of OnSelcted fields for the referenced table
    /* Select all the $key=>$value pair for the HTML SELECT. */
    $sql = "SELECT $REFIDENT, $REFNAME FROM $REFTABLE";

    /*
     * La table en paramètre est-elle une table consolidé?
     */
    $a_sql = "SELECT count(c.relname) from pg_class c,pg_attribute a where a.attrelid=c.oid AND c.relkind='r' AND c.relname !~ '^pg_' AND relname !~ '^sql' AND a.attname='id_ag' AND c.relname = '$REFTABLE'";
    $a_result = $db->query($a_sql);
    $a_result = $a_result->fetchrow();
    if ($a_result[0]) {
      global $global_id_agence;
      $sql .= " WHERE id_ag = $global_id_agence";
    }
    if (isset($RefValue)) {
      if ($a_result[0]) {
        $sql .= " AND";
      } else {
        $sql .= " WHERE";
      }
      $sql .= " $REFIDENT = '$RefValue'";
    }
    $sql .= " ORDER BY $REFIDENT";
    $ref = $db->query($sql);
    if (DB::isError($ref)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $ref->getMessage()
    }
    $temparray = array();
    while ( $tmprow = $ref->fetchRow() ) {
      $Display = "";
      next($tmprow);
      while (list($key, $value) = each ($tmprow)) {
        $Display .= $value." ";
      }
      $temparray[$tmprow[0]] = $Display;
    }
  } else {
    $temparray = array();
    if (isset($RefValue)) {
      $temparray[$RefValue] = adb_gettext($adsys[$REFTABLE][$RefValue]);
    } else {
      if (isset($adsys[$REFTABLE])) {
        reset($adsys[$REFTABLE]);
        while (list($key, $value) = each($adsys[$REFTABLE])) {
          $temparray[$key] = _($value);
        }
      }
    }
  }
  $dbHandler->closeConnection(true);
  return $temparray;
}

function getFieldsLabel($table) {
  /*
    Retourne tous les champs d'une table : noms courts
  */
  global $dbHandler;

  $db = $dbHandler->openConnection();

  $sql = "SELECT d.nchmpc FROM d_tableliste d, tableliste t WHERE d.tablen = t.ident AND t.nomc = '" . $table . "'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  $dbHandler->closeConnection(true);

  $fields = array();
  while ($prod = $result->fetchrow()) {
    array_push($fields, $prod[0]);
  };

  return $fields;

}

?>