<?php

require_once '/usr/share/adbanking/web/ad_ma/app/controllers/misc/class.db.oo.php';


function getDataAgence($id_ag){
  global $dbHandler, $global_id_agence;

  if ($id_ag == NULL)
    $id_ag = $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_agc";
  if ($id_ag != NULL)
    $sql .= " WHERE id_ag = $id_ag";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "\n Erreur getDataAgence() ! \n";
    exit();
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    return NULL;

  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  return $DATAS;
}

/**
 * Description de la classe agence_Remote
 *
 * @author danilo
 */
class agence_Remote {

  public function __construct() {

  }

  /**
   * Renvoie toutes les agences
   *
   * @return array Tableau associatif avec les agences trouvés.
   */
  public static function getListAllAgence() {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM adsys_multi_agence ORDER BY id_agc ASC";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__);
      echo "\n Erreur getListAllAgence() ! \n";
      exit();
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
      return NULL;
    }

    $tmp_arr = array();
    while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      $plaintext = trim($prod['app_db_password']);
      $password = trim($prod['app_db_host']).'_'.trim($prod['app_db_name']);

      $prod['app_db_password'] = phpseclib_Decrypt_ACU($plaintext, $password);

      $tmp_arr[$prod["id_agc"]] = $prod;
    }

    return $tmp_arr;
  }

  /**
   * Renvoie toutes les agences excepté celle d'ou est fait l'opération
   * Si le parametre $isOnline = True, on retourne seulement les agences
   * qui sont en lignes
   *
   * @return array Tableau associatif avec les agences trouvés.
   */
  public static function getListRemoteAgenceComp($isOnline = FALSE) {
    global $dbHandler, $global_id_agence;
    $agence = 'numagc()';
    $data_agc =getDataAgence($agence);
    $id_ag = $data_agc['id_ag'];

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM adsys_multi_agence WHERE id_agc!=$id_ag AND is_agence_siege='f' ORDER BY id_agc ASC";
    //$sql = "SELECT * FROM adsys_multi_agence ORDER BY id_agc ASC";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__);
      echo "\n Erreur getListRemoteAgenceComp() ! \n";
      exit();
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
      return NULL;
    }

    $tmp_arr = array();

    while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC))
    {
      $plaintext = trim($prod['app_db_password']);
      $password = trim($prod['app_db_host']).'_'.trim($prod['app_db_name']);
      $prod['app_db_password'] = phpseclib_Decrypt_ACU($plaintext, $password);

      $to_include = false;

      if($isOnline){
        require_once 'DB.php';
        $agenceInfos = agence_Remote::getRemoteAgenceInfo($prod["id_agc"], DB_FETCHMODE_ASSOC);
        $pinged = DBC::pingConnection($agenceInfos);

        if($pinged)
          $to_include = true;
      }
      else
        $to_include = true;

      if($to_include)
        $tmp_arr[$prod["id_agc"]] = $prod;
    }

    return $tmp_arr;
  }

  /**
   * Renvoie les info de connexion pour l'agence en déplacé
   *
   * @param int $id_agence L'identifiant de l'agence en déplacé
   *
   * @return array Tableau associatif avec les info de connexion pour l'agence
   */
  public static function getRemoteAgenceInfo($id_agence, $fetch_mode = DB_FETCHMODE_OBJECT) {

    require_once 'DB.php';

    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM adsys_multi_agence WHERE id_agc=$id_agence";
    //$sql = "SELECT * FROM adsys_multi_agence WHERE id_agc=$id_agence order by compte_liaison desc limit 1";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__);
      echo "\n Erreur getRemoteAgenceInfo() ! \n";
      exit();
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
      return NULL;
    }

    $agc_info = $result->fetchrow($fetch_mode);

    switch($fetch_mode)
    {
      case DB_FETCHMODE_OBJECT:
        $plaintext = trim($agc_info->app_db_password);
        $password = trim($agc_info->app_db_host).'_'.trim($agc_info->app_db_name);

        $agc_info->app_db_password = phpseclib_Decrypt_ACU($plaintext, $password);
        break;
      case DB_FETCHMODE_ASSOC:
        $plaintext = trim($agc_info['app_db_password']);
        $password = trim($agc_info['app_db_host']).'_'.trim($agc_info['app_db_name']);

        $agc_info['app_db_password'] = phpseclib_Decrypt_ACU($plaintext, $password);
        break;
    }

    return $agc_info;
  }


  /**
   * Renvoie le nom de l'agence en déplacé
   *
   * @param int $id_agence L'identifiant de l'agence en déplacé
   *
   * @return string nom de l'agence
   */
  public static function getRemoteAgenceName($id_agence) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT app_db_description FROM adsys_multi_agence WHERE id_agc=$id_agence";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__);
      echo "\n Erreur getRemoteAgenceName() ! \n";
      exit();
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
      return NULL;
    }

    $nomAgence = $result->fetchrow();
    return $nomAgence[0];
  }


  /**
   * Renvoie le compte de liaison pour l'agence en déplacé
   *
   * @param int $id_agence L'identifiant de l'agence en déplacé
   *
   * @return string Le compte de liaison pour l'agence en déplacé
   */
  public static function getRemoteAgenceCompteLiaison($id_agence) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT compte_liaison FROM adsys_multi_agence WHERE id_agc=$id_agence";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__);
      echo "\n Erreur getRemoteAgenceCompteLiaison() ! \n";
      exit();
    }

    $tmprow = $result->fetchRow();
    $compte_liaison = $tmprow[0];

    $dbHandler->closeConnection(true);

    return $compte_liaison;
  }

  /**
   * Renvoie le compte avoir pour l'agence en déplacé
   *
   * @param int $id_agence L'identifiant de l'agence en déplacé
   *
   * @return string Le compte avoir pour l'agence en déplacé
   */
  public static function getRemoteAgenceCompteAvoir($id_agence) {
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = "SELECT compte_avoir FROM adsys_multi_agence WHERE id_agc=$id_agence";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      //signalErreur(__FILE__, __LINE__, __FUNCTION__);
      echo "\n Erreur getRemoteAgenceCompteAvoir() ! \n";
      exit();
    }

    $tmprow = $result->fetchRow();
    $compte_avoir = $tmprow[0];

    $dbHandler->closeConnection(true);

    return $compte_avoir;
  }

  /**
   * Return a connection to the remote database
   *
   * @param integer $id_agence
   * @return object DBC
   */
  public static function getRemoteAgenceConnection($id_agence)
  {
    require_once 'DB.php';

    $agenceInfos = self::getRemoteAgenceInfo($id_agence, DB_FETCHMODE_ASSOC);

    $dbc = NULL;
    $db_driver = "pgsql";
    $app_db_description = trim($agenceInfos['app_db_description']);
    $app_db_host = $agenceInfos['app_db_host'];
    $app_db_port = $agenceInfos['app_db_port'];
    $app_db_name = $agenceInfos['app_db_name'];
    $app_db_username = $agenceInfos['app_db_username'];
    $app_db_password = $agenceInfos['app_db_password'];

    $pinged = DBC::pingConnection($agenceInfos);
    // Ping test:
    if($pinged) {
      // Initialize database connection
      $dbc = new DBC($app_db_name, $app_db_username, $app_db_password, $app_db_host, $app_db_port, $db_driver);
    }
    /*else {
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }*/
    return $dbc;
  }

  /**
   * Kills the remote db connection
   * @param object $pdo_conn
   */
  public static function unsetRemoteAgenceConnection($dbc)
  {
    unset($dbc);
  }

}
