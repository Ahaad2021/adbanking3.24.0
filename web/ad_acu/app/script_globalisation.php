<?php

//echo "here \n";
//require_once 'DB.php';
//require_once '/usr/share/adbanking/web/script_compensation_auto/traitements_compensation.php';
require_once '/usr/share/adbanking/web/ad_acu/app/agence_Remote.php';
require_once '/usr/share/adbanking/web/ad_acu/app/functions.php';
require_once '/usr/share/adbanking/web/ad_acu/app/Globalisation.php';
require_once '/usr/share/adbanking/web/ad_ma/app/controllers/misc/class.db.oo.php';
require_once '/usr/share/adbanking/web/lib/misc/password_encrypt_decrypt.php';


// tous les fonctions de bases concernant la connection base de donnees
/**
 * Connexion à la base de donnée
 * @var handleDB $dbHandler l'objet de connexion à la base de données
 */
//echo $argv[1];
global $DB_host, $DB_name, $DB_user, $DB_cluster,$DB_dsn,$dbHandler;
$dbHandler = new handleDB();
$ini_array = array();
$DB_host = "localhost";
$DB_name = $argv[1];
$DB_user = "adbanking";
//AT-31 : securisé le mot de passe
$password_converter = new Encryption;
$decoded_password = $password_converter->decode($argv[2]);
$DB_pass = $decoded_password;

// Connexion par socket UNIX
$DB_dsn = sprintf("pgsql://%s:%s@/%s", $DB_user, $DB_pass, $DB_name);
// FIXME le DSN "unix()" n'est actuellement pas correctement reconnu par PEAR:DB, il faut donc utiliser la syntaxe ci-avant pour se connecter par le socket.
// voir http://pear.php.net/bugs/bug.php?id=339&edit=1
//$DB_dsn = sprintf("pgsql://%s:%s@unix(%s:%s)/%s", $ini_array["DB_user"], $ini_array["DB_pass"], $ini_array["DB_socket"], $ini_array["DB_port"], $DB_name);


class handleDB {

  /**
   * Nombre de connexions à la base de données
   * @var int
   */
  var $count;

  /**
   * Handler de connexion à la DB
   * @var handler
   */
  var $handle;

  /**
   * Indique si un ROLLBACK a été demandé par une fonction
   * @var bool
   */
  var $cancel;

  /**
   * Constructor
   * @return object
   */
  function handleDB() {
    $this->count = 0;
    $this->handle = NULL;
    $this->cancel = false;
    return $this;
  }

  /**
   * Renvoie un handler de connexion à la DB
   * Méthode invoquée par toute fonction qui désire effectuer des opérations sur la DB.
   * Si aucune connexion n'avait été précédemment effectuée, ouvre une nouvelle connexion.
   * @return handler Un handler de connexion
   */
  function openConnection() {
    global $DB_dsn, $DEBUG;

    if ($this->count == 0) {
      require_once 'DB.php';
      $db = DB::connect($DB_dsn, false);
      if (DB::isError($db)) {
        //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Impossible d'établir la connexion avec la BD")." : ".$db->getmessage());
        echo "Impossible d'établir la connexion avec la BD =>".$db->getMessage();
        exit();
      }
      /*if ($DEBUG) {
        $sqlLogActivate = array("SET log_statement = 'all';", "SET log_min_error_statement = 'WARNING';");
        foreach ($sqlLogActivate as $sql) {
          $result = $db->query($sql);
          if (DB::isError($result)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Problème à l'activation de la trace PSQL")." : ".$result->getMessage());
          }
        }
      } else {
        $sqlLogActivate = array("SET log_statement = 'none';", "SET log_min_error_statement = 'PANIC';");
        foreach ($sqlLogActivate as $sql) {
          $result = $db->query($sql);
          if (DB::isError($result)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Problème à la désactivation de la trace PSQL")." : ".$result->getMessage());
          }
        }
      }*/
      $result = $db->query("BEGIN");
      if (DB::isError($result)) {
        //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Impossible de démarrer la transaction")." : ".$result->getMessage());
        echo "Impossible d'établir la connexion avec la BD =>".$result->getMessage();
        exit();
      }
      $this->handle = $db;
    }
    ++$this->count;
    return $this->handle;
  }

  /**
   * Fonction privée effectuant le COMMIT ou le ROLLBACK lorsque le compteur d'accès à la DB passe à 0
   * @access private
   */
  function closeConnectionPrivate() {
    if ($this->cancel == true) $result = $this->handle->query("ROLLBACK");
    else $result = $this->handle->query("COMMIT");

    if (DB::isError($result)) echo "Error closeConnectionPrivate found! \n";
    $this->handle->disconnect();
  }

  /**
   * Méthode invoquée lorsqu'une fonction a terminé ses traitements sur la DB
   * Si le compteur de connexion passe à 0, invoquer {@link #closeConnectionPrivate}
   * @param bool $commit indique si un COMMIT doit être effectué (1) ou un ROLLBACK(0)
   * @return bool true si la fermeture de connexion a pu avoir lieu
   */
  function closeConnection($commit) {
    if ($this->count > 0) { //S'il reste des connexions ouvertes
      --$this->count;
      if (($commit == true) && ($this->cancel == true)) {
        echo "<br /><font color=\"red\">"."Le commit ne peut avoir lieu car un ROLLBACK a déjà été demandé"."</font><br />";
      }
      if ((($this->count == 0) || ($commit == false)) && ($this->cancel == false)) {
        /*Si (on vient de fermer la dernière des  connexions ou qu'il s'agit d'un ROLLBACK  mais qu'il n'y a pas encore eu de ROLLBACK auparavant*/
        if ($commit == false)
          $this->cancel = true;
        $this->closeConnectionPrivate(); //Ferme la connexion (COMMIT ou ROLLBACK)
        $this->handle = NULL;
      }
      if ($this->count == 0) {
        $this->cancel = false;
      }
      return true;
    } else return false;
  }

}

// fin des fonctions de la base de donnees

global $dbHandler, $global_nom_login, $global_monnaie, $global_monnaie_courante;

$truncateTable = truncateTable("ec_situation_paiement");

$ListeAgences = agence_Remote::getListRemoteAgenceComp();

foreach ($ListeAgences as $key => $value){

  $agenceCo = agence_Remote::getRemoteAgenceConnection($value['id_agc']);
  if($agenceCo != null) {
    $agenceCo->beginTransaction();

    try {

      // Init class
      $GlobalisationObj = new Globalisation($agenceCo, $value['id_agc']);
      $date_jour = date("d");
      $date_mois = date("m");
      $date_annee = date("Y");
      $date_total = $date_jour."/".$date_mois."/".$date_annee;

      $Param = $GlobalisationObj->getPeriode($date_total);
      $DataSituation = $GlobalisationObj->getSituationPaiement($Param['date_debut'],$Param['date_fin'],$Param['id_annee'],$Param['id_saison'],$Param['periode']);
      $db = $dbHandler->openConnection();
      foreach($DataSituation as $keySituation => $valueSituation){
        $result = executeQuery($db, buildInsertQuery("ec_situation_paiement", $valueSituation));
      }
      $dbHandler->closeConnection(true);
      unset($GlobalisationObj);

      $agenceCo->commit();

    } catch (PDOException $e) {
      //$pdo_conn->rollBack(); // Roll back remote transaction
    }
  }

  unset($agenceCo);

}

$truncateTable = truncateTable("ec_repartition_zone");

foreach ($ListeAgences as $key => $value){
  $agenceCo = agence_Remote::getRemoteAgenceConnection($value['id_agc']);
  if($agenceCo != null) {
    $agenceCo->beginTransaction();

    try {

      // Init class
      $GlobalisationObj = new Globalisation($agenceCo, $value['id_agc']);
      $date_jour = date("d");
      $date_mois = date("m");
      $date_annee = date("Y");
      $date_total = $date_jour."/".$date_mois."/".$date_annee;

      $Param = $GlobalisationObj->getPeriode($date_total);
      $date_saison_selected = $GlobalisationObj-> getDate($Param['id_saison']);
      $date_debut = pg2phpDate($date_saison_selected['date_debut']);
      $date_fin = pg2phpDate($date_saison_selected['date_fin']);
      $DataRepartition= $GlobalisationObj->getRepartitionZone($Param['id_annee'],$Param['id_saison'],$date_debut,$date_fin);
      $db = $dbHandler->openConnection();
      foreach($DataRepartition as $keyRepartition => $valueRepartition){
        $result = executeQuery($db, buildInsertQuery("ec_repartition_zone", $valueRepartition));
      }
      $dbHandler->closeConnection(true);
      unset($GlobalisationObj);

      $agenceCo->commit();

    } catch (PDOException $e) {
      //$pdo_conn->rollBack(); // Roll back remote transaction
    }
  }
  unset($agenceCo);
}

$truncateTable = truncateTable("ec_benef_paye");

foreach ($ListeAgences as $key => $value){

  $agenceCo = agence_Remote::getRemoteAgenceConnection($value['id_agc']);
  if($agenceCo != null) {
    $agenceCo->beginTransaction();

    try {

      // Init class
      $GlobalisationObj = new Globalisation($agenceCo, $value['id_agc']);
      $date_jour = date("d");
      $date_mois = date("m");
      $date_annee = date("Y");
      $date_total = $date_jour."/".$date_mois."/".$date_annee;
      $Param = $GlobalisationObj->getPeriode($date_total);
      $delete_view = $GlobalisationObj->deleteView();
      $create_view = $GlobalisationObj->createViewPaye($Param['id_annee'], $Param['id_saison'], $Param['periode'], $Param['date_debut'],$Param['date_fin']);

      $DataBenefPaye= $GlobalisationObj->getBenefPaye($Param['id_annee'],$Param['id_saison'],$Param['periode'],$Param['date_debut'],$Param['date_fin']);
      $db = $dbHandler->openConnection();
      foreach($DataBenefPaye as $keyBenefPaye => $valueBenefPaye){
        $result = executeQuery($db, buildInsertQuery("ec_benef_paye", $valueBenefPaye));
      }
      $dbHandler->closeConnection(true);
      unset($GlobalisationObj);

      $agenceCo->commit();

    } catch (PDOException $e) {
      //$pdo_conn->rollBack(); // Roll back remote transaction
    }
  }
  unset($agenceCo);
}

