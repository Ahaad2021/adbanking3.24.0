<?php
/**
 * @package Systeme
 */
require_once 'lib/misc/VariablesGlobales.php';

/**
 * Permet de centraliser tous les accès à la base de données en un seul point du logiciel
 */
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
        signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Impossible d'établir la connexion avec la BD")." : ".$db->getmessage());
      }
      if ($DEBUG) {
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
      }
      $result = $db->query("BEGIN");
      if (DB::isError($result)) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Impossible de démarrer la transaction")." : ".$result->getMessage());
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

    if (DB::isError($result)) signalErreur(__FILE__,__LINE__,__FUNCTION__);
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
        echo "<br /><font color=\"red\">"._("Le commit ne peut avoir lieu car un ROLLBACK a déjà été demandé")."</font><br />";
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
?>