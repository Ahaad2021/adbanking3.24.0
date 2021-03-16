<?php
require_once 'DB.php';

/*******************************************************************************/
/**
 * Connexion à la base de donnée
 * @var handleDB $dbHandler l'objet de connexion à la base de données
 */
global $DB_host, $DB_name, $DB_user, $DB_cluster,$DB_dsn,$dbHandler;
$dbHandler = new handleDB();
$ini_array = array();
$DB_host = "localhost";
//$DB_name = "fcb_gihosha";
$DB_name = "$argv[1]";
$DB_user = "adbanking";
$DB_pass = "$argv[4]";
$RPM_version = "$argv[2]";
$traduction_file = "$argv[3]";

//Parametres pour fonction postgres qui ramene les clients eligible a radier
#$id_prod_cpte = $argv[2];
#$jour_par_an = $argv[3];

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
        //signalErreur(__FILE__,__LINE__,__FUNCTION_, ("Impossible d'établir la connexion avec la BD")." : ".$db->getmessage());
        echo "Impossible d'établir la connexion avec la BD =>".$db->getMessage();
        exit();
      }
      /*if ($DEBUG) {
        $sqlLogActivate = array("SET log_statement = 'all';", "SET log_min_error_statement = 'WARNING';");
        foreach ($sqlLogActivate as $sql) {
          $result = $db->query($sql);
          if (DB::isError($result)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION_, ("Problème à l'activation de la trace PSQL")." : ".$result->getMessage());
          }
        }
      } else {
        $sqlLogActivate = array("SET log_statement = 'none';", "SET log_min_error_statement = 'PANIC';");
        foreach ($sqlLogActivate as $sql) {
          $result = $db->query($sql);
          if (DB::isError($result)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION_, ("Problème à la désactivation de la trace PSQL")." : ".$result->getMessage());
          }
        }
      }*/
      $result = $db->query("BEGIN");
      if (DB::isError($result)) {
        //signalErreur(__FILE__,__LINE__,__FUNCTION_, ("Impossible de démarrer la transaction")." : ".$result->getMessage());
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

/*******************************************************************************/

/*********************Recuperation des traductions dans les fichiers traduits*******************************************/

$fichier = '/usr/share/adbanking/db/update_traduction/'.$RPM_version.'/'.$traduction_file.'.csv';echo "/n $fichier /n";
if (!file_exists($fichier)) {
  echo "Le fichier traduit n existe pas! Veuillez verifier que ce ficher de traduction existe.";
  exit();
}
$handle = fopen($fichier, 'r');
$count = 0;
$count_traduit = 0;
while (($data = fgetcsv($handle, 1000, ';')) != false) {
  $count++;
  if ($count == 1) continue;
  $libel_fr = ltrim($data[1],"'");
  $libel_fr = rtrim($libel_fr,"'");

  $libel_traduit = ltrim($data[3],"'");
  $libel_traduit = rtrim($libel_traduit,"'");
  if (strpos($libel_fr, '\'')){
    $libel_fr = str_replace("'","''",$libel_fr);
  }
  //check if traduction en_GB exist
  $trad_exist = checkTraductionExist($libel_fr);
  if (strlen($libel_traduit) > 0 && $trad_exist[2] < 1){
    $count_traduit++;
    // insertion des traductions dans la table ad_traductions
    if ($trad_exist[1] != null) {
      $traduire = insertTraduction($trad_exist[1], $libel_traduit);
      echo "le libelle ".$libel_traduit."a été traduit\n";
    }
  }
}
echo "Il a eu ".$count_traduit." traductions qui ont été traité sur un total de ".$count." traductions\n";

function insertTraduction($id_str, $traduction)
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $langue = "en_GB";

  $sql = "INSERT INTO ad_traductions(id_str,langue,traduction) values ($id_str,'$langue','$traduction')";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    echo "DB: ".$result->getMessage()." et aussi id_str =>".$id_str;
  }
  $dbHandler->closeConnection(true);
  return true;
}

function checkTraductionExist($libel)
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $langue = "en_GB";

  $sql = "select count(id_str) as nb_traduit,id_str  from ad_traductions where traduction LIKE '%$libel%' group by id_str";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    echo "DB: ".$result->getMessage();
  }
  $traduit=array();
  $nb_traduit= $result->fetchrow();
  $traduit = $nb_traduit;

  if ($nb_traduit[1] != null) {
    $sql1 = "select count(id_str) as nb_traduit_traduction from ad_traductions where id_str = $nb_traduit[1] and langue = 'en_GB'";
    $result1 = $db->query($sql1);
    if (DB::isError($result1)) {
      $dbHandler->closeConnection(true);
      echo "DB: " . $result1->getMessage();
    }
    $nb_traduit_traduction = $result1->fetchrow();
    if ($nb_traduit_traduction[0] == 1) {
      $traduit[2] = $nb_traduit_traduction[0];
    } else {
      $traduit[2] = $nb_traduit_traduction[0];
    }
  }


  $dbHandler->closeConnection(true);
  return $traduit;
}

?>