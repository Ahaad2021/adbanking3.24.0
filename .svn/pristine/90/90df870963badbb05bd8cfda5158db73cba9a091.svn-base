<?php

//require_once 'DB.php';
require_once '/usr/share/adbanking/web/ad_compensation_siege/app/traitements_compensation.php';
require_once '/usr/share/adbanking/web/ad_compensation_siege/app/agence_Remote.php';
require_once '/usr/share/adbanking/web/ad_compensation_siege/app/functions.php';
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
global $global_id_exo,$global_multidevise,$global_id_agence;
$value = getGlobalDatas();
$global_id_exo = $value['exercice'];
$global_multidevise = $value['multidevise'];
$global_id_agence = getNumAgence();
$global_monnaie = $value['code_devise_reference'];

$doc_prefix = "/usr/share/adbanking/web/multiagence/properties";


$statut_job = getStatutJobExterne();

if ($statut_job != 'ENCOURS') {
  // Read table adsys_multi_agence
  $ListeAgences = agence_Remote::getListRemoteAgenceComp(true);

  $file_path = $doc_prefix."/multiagence.csv";

  // Delete file
  if (file_exists($file_path)) {
    @unlink($file_path);
  }

  // Create file
  $test = @touch($file_path);

  // Add header to file
  file_put_contents($file_path, "app_db_host;app_db_name;id_agc;app_db_username;app_db_password\r\n", FILE_APPEND | LOCK_EX);

  $choix_agence = array();
  if (is_array($ListeAgences) && count($ListeAgences) > 0) {
    foreach ($ListeAgences as $key => $obj) {
      $line = $obj["app_db_host"]."||@||".$obj["app_db_name"]."||@||".$obj["id_agc"]."||@||".$obj["app_db_username"]."||@||".$obj["app_db_password"]."\r\n";
      // Append content to file
      file_put_contents($file_path, $line, FILE_APPEND | LOCK_EX);
    }
  }
  echo "Creation/mise à jour multiagence csv terminé!! | ";

  //Vidage partielle des tables logs
  $total_vidage = truncatelogmultiagence();
  echo "Nombre de logs vider à partir des tables logs = ".$total_vidage."\n";

  updateStatutJobExterne('ENCOURS');

  echo "Début JOB Talend - Alimentation de la base siege!! ===> ";

  $cmd_job = "sh /usr/share/adbanking/web/multiagence/batchs/ALIM_SIEGE.sh 2>&1";

  echo "Fin JOB Talend - Alimentation de la base siege!!\n";

  $result_job = exec($cmd_job, $output_job, $return_job);

  // Fin exécution du job
  updateStatutJobExterne('TERMINE');

  // Delete file
  //if (file_exists($file_path)) {
  //@unlink($file_path);
  //}
}


// Affichage de la confirmation


$error_arr = -1;
$error_arr = @getEtlLogError();
if($error_arr < 0) {
  echo "Erreur avec le Job Talend : ".$output_job[0]."<br /> ". $output_job[1];
}
else{
  if ($error_arr > 0){
    echo "Attention pas accès serveur pour certain agence(s)!";
    echo "Veuillez vérifier le rapport log multiagence pour plus de détailles!";
  }
}



$fonction = 214;
$operation = 614;
$id_his = NULL;

// Traiter les compensations
$ListeCompensations = getListeCompensations();
$statut_job_now = getStatutJobExterne();
echo "Nombre Compensations à traiter: ".count($ListeCompensations)." | ";

if (is_array($ListeCompensations) && count($ListeCompensations) > 0 && $statut_job_now['TERMINE']) {
  echo "Début Traitement écritures!! | ";

  for ($x = 0; $x < count($ListeCompensations); $x++) {

    // Build écritures
    // Passage de l'écriture de retrait
    $comptable = array();
    $ajout_historique = 'f';
    $msg_erreur = NULL;

    // Retrait / Depot
    $montant = $ListeCompensations[$x]['montant'];
    $devise = $ListeCompensations[$x]['code_devise_montant'];

    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();

    $cptes_substitue["cpta"]["debit"] = $ListeCompensations[$x]['compte_debit_siege'];
    $cptes_substitue["cpta"]["credit"] = $ListeCompensations[$x]['compte_credit_siege'];

    $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $devise, NULL, NULL, NULL);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    // Commission
    $commission = $ListeCompensations[$x]['commission'];
    $code_devise_commission = $global_monnaie_courante; //$ListeCompensations[$x]['code_devise_commission'];

    if ($commission > 0) {
      $cptes_substitue = array();
      $cptes_substitue["cpta"] = array();

      $cptes_substitue["cpta"]["debit"] = $ListeCompensations[$x]['compte_debit_siege'];
      $cptes_substitue["cpta"]["credit"] = $ListeCompensations[$x]['compte_credit_siege'];

      $myErr = passageEcrituresComptablesAuto($operation, $commission, $comptable, $cptes_substitue, $code_devise_commission, NULL, NULL, NULL);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }

    $myErr = ajout_historique($fonction, NULL, 'Traitement compensation au siège', $global_nom_login, date("r"), $comptable, NULL, $id_his);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      $msg_erreur = serialize($myErr);
    } else {
      $ajout_historique = 't';
    }

    $id_his = $myErr->param;
    $id_ecriture = getIDEcritureByIDHis($id_his);

    // Update current ad_multi_agence_compensation
    updateCompensation ($ListeCompensations[$x]['id'], $ListeCompensations[$x]['id_audit_agc'], $ListeCompensations[$x]['id_ag_local'], $ListeCompensations[$x]['id_ag_distant'], $id_his, $id_ecriture, $ajout_historique, $msg_erreur);

    $dbHandler->closeConnection(true); // TO UNCOMMENT - commit transaction
  }

  echo "Les écritures ont été effectuées avec succès.";
  echo "Traitement Terminé!!\n";
}
else {
  echo "Il y aucune écritures effectuées.";
  echo "Traitement Terminé!!\n";
}

?>
