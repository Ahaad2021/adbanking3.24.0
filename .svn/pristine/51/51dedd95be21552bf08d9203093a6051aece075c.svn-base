<?php

/**
 * Contient toutes les fonctionnalités générales nécessaires aux procédures stockées.
 *
 * @package Systeme
 * @author Fasty
 * @since 11/12/2001
 */

require_once 'lib/misc/Erreur.php';

/**
 * Rend une chaîne de caractères compatible pour être insérée dans une requête SQL.
 */
function string_make_pgcompatible($str) {
  return addslashes($str);
}

/**
 * Transforme les caractères préciaux d'un string en leurs quote HTML.
 *
 * Ex: Voici "le" thé => Voici &quot;le&quot th&eacute
 *
 * @param str $str Chaîne à transformer
 * @return str Chaîne transformée.
 */
function string_make_htmlcompatible($str)
{
  return htmlspecialchars($str);
};

function array_make_pgcompatible($ary) {
  if (! is_array($ary))
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'argument attendu est un array"
  foreach ($ary AS $key => $value)
  $ary[$key] = string_make_pgcompatible($ary[$key]);
  return $ary;
}

/**
 * Construction d'une Query SQL du type INSERT INTO ...
 *
 * @param str $TableName le nom de la table dans laquele l'insertion doit être effectuée
 * @param array $Fields tableau associatif de type $Fields[nom champs] = valeur
 * @return str Chaîne contenant la requête SQL
 */
function buildInsertQuery ($TableName, $Fields) {

  if (count($Fields) == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Aucun champ à ajouter"));
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

/**
 * Construction d'une requête SQL de type UPDATE.
 *
 * @param mixed $TableName Nom de la table dans laquelle la requête doit être faite
 * @param mixed $Fields Tableau associatif contenant les nouvelles valeurs à mettre à jour:<ul>
 *                      <li>nom d'un élément = nom de champ dans la table,
 *                      <li>valeur de cet élément = nouvelle valeur à mettre à jour dans la table.</ul>
 * @param string $Where Tableau associatif utilisé pour construire la clause de sélection (WHERE) des records à mettre à jour:<ul>
 *                      <li>nom d'un élément = nom de champ sur lequel la clause WHERE sera appliquée,
 *                      <li>valeur de cet élément = valeur que le champs doit avoir pour être sélectioné et donc mis à jour.</ul>
 * @access public
 * @return void Une chaîne de caractères contenant la requête SQL
 */
function buildUpdateQuery ($TableName, $Fields, $Where="") {

  if (sizeof($Fields) == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Aucun champ à mettre à jour"));
  $Fields = array_make_pgcompatible($Fields);
  reset($Fields);
  $sql = "UPDATE $TableName SET ";
  while (list($key, $value) = each($Fields)) {
    if ("$value" == '')
      $sql .= $key." = NULL, ";
    else
      $sql .= $key." = '".$value."', ";
  }
  $sql = substr($sql, 0, strlen($sql) - 2);
  if (is_array($Where)) {
    $sql .= " WHERE  ";
    while (list($key, $value) = each($Where))
      $sql .= " $key = '$value' AND";
    $sql = substr($sql, 0, strlen($sql) - 3);
  }
  $sql .=";";
  return $sql;
}

/**
 * Construction d'une Query SQL du type DELETE FROM ...
 *
 * @param string $TableName le nom de la table dans laquele la suppression doit être effectuée
 * @param array $Where  tableau associatif du type $Where|nom champs] = valeur. On fera un AND de tous ces champs pour construire une clause Where
 * @return string la requête SQL
 */
function buildDeleteQuery ($TableName, $Where) {

  $sql = "DELETE FROM $TableName";
  if (is_array($Where)) {
    $sql .= " WHERE ";
    while (list($key, $value) = each($Where))
      $sql .= " $key = '$value' AND";
    $sql = substr($sql, 0, strlen($sql) - 3);
  }
  $sql .= ";";
  return $sql;
}

/**
 * buildSelectQuery : Construction d'une requête SQL de type SELECT
 * @author Antoine Guyette
 * @param string $table Nom de la table dans laquelle la requête doit être faite
 * @param string $WHERE Tableau associatif utilisé pour construire la clause de sélection (WHERE)
 * @access public
 * @return Une chaîne de caractères contenant la requête SQL
 */
function buildSelectQuery ($table, $WHERE = NULL) {
  $sql = "SELECT * FROM $table";
  if (is_array($WHERE)) {
    $sql .= " WHERE ";
    while (list($key, $value) = each($WHERE)) {
      $sql .= " $key = '$value' AND";
    }
    $sql = substr($sql, 0, strlen($sql) - 4);
  }
  $sql .= ";";
  return $sql;
}

/**
 * buildCountQuery : Construction d'une requête SQL de type SELECT COUNT(*)
 * @author Antoine Delvaux
 * @version 2.8.6
 * @param string $a_table Nom de la table dans laquelle la requête doit être faite
 * @param string $a_champ Nom du champ sur lequel on lance le compte
 * @param string $a_where Tableau associatif utilisé pour construire la clause de sélection (WHERE)
 * @return Une chaîne de caractères contenant la requête SQL
 */
function buildCountQuery ($a_table, $a_champ = '*', $a_where = NULL)
{
   $sql = "SELECT COUNT($a_champ) FROM $a_table";
   if (is_array($a_where))
   {
      $sql .= " WHERE ";
      while (list($key, $value) = each($a_where))
      {
         $sql .= " $key = '$value' AND";
      }
      $sql = substr($sql, 0, strlen($sql) - 4);
   }
   $sql .= ";";
   return $sql;
}

/**
 * Wrapper autour de la fonction executeQuery {@see executeQuery} qui ne nécessite pas l'ouverture d'une connexion vers la DB.
 *
 * Cette fonction s'occupe de la gestion de la session vers la BD, aucune connexion ouvert n'est donc nécessaire préalablement
 * à son appel.  Si la requête SQL se passe bien, l'appel se terminera donc par un commit.
 *
 * @author Antoine Delvaux
 * @since 2.6
 * @param string $a_sql La requête SQL à exécuter.
 * @param bool $a_flat Flag indiquant si les résultats de la requête (SELECT) doivent être
 *  - concaténés dans un tableau unique
 *  - placés dans un tableau de tableaux (un tableau pour chaque ligne), surtout utile si la requête ne sélectionne qu'une seule colonne.
 * @return ErrorObj Avec en paramètre :
 *  - la liste des lignes retournées par la requête dans le cas d'un SELECT
 *  - le nombre de ligne affectées dans les autres cas.
 */
function executeDirectQuery($a_sql, $a_flat = FALSE) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $result = executeQuery($db, $a_sql, $a_flat);
  if ($result->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $result;
  }
  $dbHandler->closeConnection(true);
  return($result);
}

/**
 * Exécute une requête SQL (SELECT, UPDATE, INSERT ou DELETE) de manière standardisée.
 *
 * Cette fonction suppose qu'une connexion DB est déjà ouverte, il est dès lors nécessaire de passer par
 * référence l'objet $db déjà existant.  Si la requête se passe bien, la connexion est laissée dans son état (ie ouverte) afin que
 * d'autres requêtes puissent avoir lieu. Sinon elle s'occupe de fermer la connexion vers la DB et de signaler l'erreur.
 *
 * @author Antoine Delvaux
 * @since 2.6
 * @param handleDB &$db L'objet handleDB sur lequel on travaille, passé par référence.
 * @param string $a_sql La requête SQL à exécuter.
 * @param bool $a_flat Flag indiquant si les résultats de la requête (SELECT) doivent être
 *  - concaténés dans un tableau unique
 *  - placés dans un tableau de tableaux (un tableau pour chaque ligne), surtout utile si la requête ne sélectionne qu'une seule colonne.
 * @return ErrorObj Un ErrorObj avec en paramètre :
 *  - la liste des lignes retournées par la requête dans le cas d'un SELECT
 *  - le nombre de ligne affectées dans les autres cas.
 */
function executeQuery(&$db, $a_sql, $a_flat = FALSE) {
  global $dbHandler;

  $result = $db->query($a_sql);
  if (DB::isError($result)) {
    // S'il y a une erreur, on retourne ERR_DB_SQL avec le code de la requête ayant posé problème
    return new ErrorObj(ERR_DB_SQL, $result->getUserinfo());
  } else if (DB::isManip($a_sql)) {
    // Si c'est un UPDATE, INSERT ou DELETE, on retourne alors le nombre de lignes affectées
    return new ErrorObj(NO_ERR, $db->affectedRows());
  } else {
    // On suppose que la requête était un SELECT, on retourne alors les lignes trouvées
    $rows = array();
    if ($a_flat) {
      // On concatène les lignes (et les colonnes) retournées
      while ($row = $result->fetchrow()) {
        foreach ($row as $col => $content)
        array_push($rows, $content);
      }
    } else {
      // On retourne un tableau ($row) par ligne de résultats
      while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        array_push($rows, $row);
    }
    $result->free();
    return new ErrorObj(NO_ERR, $rows);
  }
}

/**
 * Fonction qui extrait une image de la base de données à partir de son Object ID
 * @author Thomas Fastenakel
 * @since 2.1
 * @param integer $OID Object Id de l'objet à extraire
 * @return string Le nom du fichier contenant l'image (ou NULL si aucune enregistrement trouvé ou si le fichier n'existe pas)
 */
function getImageFromOID($OID) {
  // Si OID = 0, le champ n'est pas renseigné, on renvoie donc un string vide
  if ($OID == NULL)
    return NULL;

  global $dbHandler, $global_nom_login;

  $db = $dbHandler->openConnection();

  $filename = $OID;

  $PATHS = makeImagePaths($filename);

  // Vérifie si le fichier n'est pas déjà présent dans la cache
  if (file_exists($PATHS["localfilepath"]))
    return $filename;
  else {
    // Récupération de la DB
    $sql = "SELECT lo_export('$OID', '".$PATHS["localfilepath"]."')";

    // Exécution de la requete
    $result = $db->query($sql);
    debug($result);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(true);
      //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numrows() == 1) {
      $row = $result->fetchrow();
      if ($row[0] == 1) // Le fichier a bien été exporté
        return $filename;
      else
        return NULL; // Le fichier était inexistant dans la DB
    } else
      return NULL; // On n'a pas trouvé l'image
  }
}

/**
 * Fonction qui importe une image présente sur le serveur vers la DB
 * @author Thomas Fastenakel
 * @since 2.1
 * @param string $tableName Le nom de la table qui contiendra l'image
 * @param string $fieldName Le nom du champ qui contiendra l'aimge (doit etre de type OID)
 * @param Array $WhereClause Un tableau associati reprenant la condition d'accès au champ (on fait un AND entre les éléments du tableau s'ils sont plusieurs
 * @return Objet ErrorObj
 */
function putImageIntoDB($imagepath, $tableName, $fieldName, $WhereClause=NULL) {
  global $dbHandler, $global_nom_login, $http_prefix, $global_id_agence;

  $db = $dbHandler->openConnection();

  if ($imagepath == "") // Dans ce cas effacer l'image
    $sql = "UPDATE $tableName SET $fieldName = NULL ";
  else
    $sql = "UPDATE $tableName SET $fieldName = lo_import('$imagepath') ";

  if (is_array($WhereClause)) {
    $sql .= "WHERE id_ag=$global_id_agence AND ";
    foreach ($WhereClause as $key => $value)
    $sql .= "$key = $value AND ";
    $sql = substr($sql, 0, strlen($sql) - 4);
  }

  // Exécution de la requete
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR); // On n'a pas trouvé l'image
}

/**
 * Prend un identificateur et va chercher la référence en fonction du ref_field de la table d_tableliste.
 *
 * @param int $numero ident que l'on veut "traduire"
 * @param string $nchmpc le nom court du champs qui le contient
 * @return string chaîne qui sera affiché à la place de $numero
 */
function getNameFromIdent($numero, $nchmpc) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  // Trouver l'ID du champs à partir de son nom court
  $sql = "SELECT ident FROM d_tableliste WHERE nchmpc = '$nchmpc'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le champs $nchmpc n'est pas renseigné dans la table"
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $num_champs = $row["ident"];
  // num_champ est l'ID du champs en question dans d_tableliste
  // Trouver le nom du champs référencé
  $sql = "SELECT ref_field FROM d_tableliste WHERE ident = '$num_champs'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $ref_field = $row["ref_field"];
  // ref_field est l'ID du champs référencé
  // Trouver le nom court et le n° de la table qui contient le champs référencé
  $sql = "SELECT nchmpc, tablen FROM d_tableliste WHERE ident = '$ref_field'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  // tablen est le numéro de la table contenant le champs référencé
  // FieldName est le nom u champs référencé.
  $tablen = $row["tablen"];
  $FieldName = $row["nchmpc"];
  // Construire la liste des champs représentatifs de la table contenant le champs référencé
  $sql = "SELECT * FROM d_tableliste WHERE tablen = '$tablen' AND onslct = 't'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $OnSelectFields = "";
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $OnSelectFields .= $row["nchmpc"].", ";
  $OnSelectFields = substr($OnSelectFields, 0, strlen($OnSelectFields)-2);
  // Trouver le nom de la table qui conteint les champs référencé
  $sql = "SELECT nomc FROM tableliste WHERE ident = '$tablen'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $tablename = $row["nomc"];
  // Retrouver les valeurs des champs représentatifs pour le numéro fourni.
  $sql = "SELECT $OnSelectFields  FROM $tablename WHERE $FieldName = '$numero'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $Display = "";
  while (list($key, $value) = each ($row))
    $Display .= $value." ";
  $dbHandler->closeConnection(true);
  return $Display;
}

?>