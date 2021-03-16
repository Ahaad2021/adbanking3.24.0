<?php

// On charge les variables globales
require_once '/usr/share/adbanking/web/services/misc_api.php';
require_once '/usr/share/adbanking/web/services/mobile_lending/function_algo.php';
require_once '/usr/share/adbanking/web/lib/misc/password_encrypt_decrypt.php';
//require_once '/usr/share/adbanking/web/ad_acu/app/erreur.php';


/***********************************************************************************************************/
global $DB_host, $DB_name, $DB_user, $DB_cluster,$DB_dsn,$dbHandler;
$dbHandler = new handleDB();
$ini_array = array();
$DB_host = "localhost";
$DB_name = $argv[1];
$DB_user = "adbanking";
$DB_pass = "public";
//AT-31 : securisé le mot de passe
//$password_converter = new Encryption;
//$decoded_password = $password_converter->decode($argv[2]);
//$DB_pass = $decoded_password;

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

/**********************************************************************************************************/

//recuperation des parametres pour le calculs des donnees clients abonnees
echo 'here ';
// Recuperation du pourcentage du montant max
$file_path = '/usr/share/adbanking/web/services/mobile_lending/IMF_RWANDA_ALL_test.csv';
$fichier_lot = $file_path;
$handle = fopen($fichier_lot, "r");
$count = 0;
$count_data = 0;
while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }
    $db = $dbHandler->openConnection();
    if ($data[20]==''){$data[20] = 0;}
    if ($data[22]==''){$data[22] = 0;}
    if ($data[13]==''){$data[13] = 0;}
    if ($data[11]==''){$data[11] = 0;}
    if ($data[25]==''){$data[25] = 0;}
    $sql = "INSERT INTO ml_statistique_client_all VALUES ('$data[0]',$data[1],$data[2],'$data[3]','$data[4]','$data[5]','$data[6]',$data[7],$data[8],$data[9],$data[10],$data[11],$data[12],$data[13],$data[14],$data[15],$data[16],$data[17],$data[18],$data[19],$data[20],$data[21],$data[22],$data[23],$data[24],$data[25])";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        echo "Failed";echo "\n";
        echo $sql."\n";
        $dbHandler->closeConnection(false);
    }else{
        echo "Succes";echo "\n";
        $dbHandler->closeConnection(true);
    }
    $count_data++;
    echo $count_data;
}
?>