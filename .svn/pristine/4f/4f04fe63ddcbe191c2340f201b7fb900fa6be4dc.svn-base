<?php
require_once 'MouvementMSQPublisher.php';
require_once 'functions.php';
require_once '/usr/share/adbanking/web/ad_ma/app/controllers/misc/class.db.oo.php';
require_once '/usr/share/adbanking/web/lib/misc/password_encrypt_decrypt.php';

// DB connection
/**
 * Connexion à la base de donnée
 * @var handleDB $dbHandler l'objet de connexion à la base de données
 */
global $DB_host, $DB_name, $DB_user, $DB_cluster,$DB_dsn,$dbHandler;
$dbHandler = new handleDB();
$ini_array = array();

$DB_host = "localhost";
$DB_name = $argv[1];
$DB_user = "adbanking";
//$DB_pass = $argv[2];
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


global $dbHandler, $global_nom_login;

// FIN DB connection

echo "Début de l'execution du fichier failOverMouvementMSQ.php \n";

define('MSQ_HOST', $argv[3]);
define('MSQ_PORT', $argv[4]);
define('MSQ_USERNAME', $argv[5]);
define('MSQ_PASSWORD', $argv[6]);
define('MSQ_VHOST', $argv[7]);

define('MSQ_EXCHANGE_NAME', $argv[8]);
define('MSQ_EXCHANGE_TYPE', $argv[9]);
define('MSQ_QUEUE_NAME_MOUVEMENT', $argv[10]);
define('MSQ_ROUTING_KEY_MOUVEMENT', $argv[11]);

$max_nb_heure = $argv[12];

$mouvementPublisher = new MouvementMSQPublisher(
    MSQ_HOST,
    MSQ_PORT,
    MSQ_USERNAME,
    MSQ_PASSWORD,
    MSQ_QUEUE_NAME_MOUVEMENT,
    MSQ_ROUTING_KEY_MOUVEMENT,
    MSQ_EXCHANGE_NAME,
    MSQ_VHOST
);

echo "Traitement des messages dans la table ad_msq en cours... \n";
$dbHandler = new handleDB();

global $dbHandler;

$typeMsg = BaseMSQMessage::TYPE_MSG_MOUVEMENT;

$statutATraiter = BaseMSQMessage::STATUT_TRAITEMENT_A_TRAITER;
$statutEnEchec = BaseMSQMessage::STATUT_TRAITEMENT_ECHEC;

$db = $dbHandler->openConnection();

$sql = "SELECT * FROM ad_msq WHERE type_msg = $typeMsg AND statut IN ($statutATraiter,$statutEnEchec) AND id_ag = numagc()";

$result = $db->query($sql);
if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    echo "Erreur du fonction getUnsuccessfulMsgFromFailoverTable ! \n";
}

$dbHandler->closeConnection(true);
if ($result->numRows() == 0) {
    echo "Aucunes données à traiter ! \n";
    echo "Fin de l'execution du fichier failOverMouvementMSQ.php \n";
    return NULL;
}

while($datas = $result->fetchrow(DB_FETCHMODE_ASSOC)){

//    $maxNbEssaie = 100;
    $rawMessage = json_decode($datas['encoded_message'], true);

    if (abs(calcDateDiffInHours($rawMessage['date_transaction'], date('Y-m-d H:i:s'))) >= $max_nb_heure) {
        echo "La date de transaction pour ce msg a dépassé $max_nb_heure heures. Le msg va être retirer du table \n";
        deleteMsgFromFailoverTable($datas['id']);
    }

//    if ($datas['nb_essaie'] > $maxNbEssaie) {
//        echo "Le nombre d'essaie pour ce msg a dépassé le maximum nombre d'essaie \n";
//        deleteMsgFromFailoverTable($datas['id']);
//    }
    else {
        try {
            $mouvementPublisher->init();

            $mouvementPublisher->process($rawMessage);

            $mouvementPublisher->shutdown();

            $statut = BaseMSQMessage::STATUT_TRAITEMENT_SUCCES;
            updateRowFailoverTable($datas['id'], $datas['id_ag'], $statut);

        } catch (Exception $e) {
            $statut = BaseMSQMessage::STATUT_TRAITEMENT_ECHEC;
            updateRowFailoverTable($datas['id'], $datas['id_ag'], $statut);
        }
    }
}

echo "Traitement des messages dans la table ad_msq terminé. \n";

echo "Purge des messages envoyé en succès chez le broker en cours... \n";
$deletePublishedMessages = deleteMsgFromFailoverTable(NULL, BaseMSQMessage::STATUT_TRAITEMENT_SUCCES, $typeMsg);
echo "Purge des messages envoyé en succès chez le broker terminé. \n";

echo "Fin de l'execution du fichier failOverMouvementMSQ.php \n";

/**
 * Update message into ad_msq table
 *
 * @param $datasId
 * @param $agenceId
 * @param $statut
 */
function updateRowFailoverTable($dataId, $agenceId, $statut)
{
    global $dbHandler;
    $db = $dbHandler->openConnection();

    $DATA['date_traitement'] = 'now()';
    $DATA['nb_essaie'] = 'nb_essaie + 1';
    $DATA['statut'] = $statut;
    $WHERE['id'] = $dataId;
    $WHERE['id_ag'] = $agenceId;

    $sql = buildUpdateQuery('ad_msq', $DATA, $WHERE);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        echo "Erreur du fonction updateRowFailoverTable ! \n";
        exit();
    }

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
}

/**
 * Delete msg from table
 */
function deleteMsgFromFailoverTable($dataId = NULL, $statut = NULL, $typeMsg = NULL)
{
    global $dbHandler;

    $db = $dbHandler->openConnection();

    $sql = "DELETE FROM ad_msq WHERE id_ag = numagc() ";
    if ($dataId != null){
        $sql .= " and id = $dataId";
    }
    if ($statut != null){
        $sql .= " and statut = $statut";
    }
    if ($typeMsg != null){
        $sql .= " and type_msg = $typeMsg";
    }

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        echo "Erreur du fonction deleteSuccessMsgFromFailoverTable ! \n";
    }

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}

/**
 * Calculate difference in hours between two dates
 * We assume here that the difference between the dates will be atmost 23 hours
 * @param $start_date
 * @param $end_date
 * @param string $format
 * @return string
 */
function calcDateDiff($start_date, $end_date, $format = '%h')
{
    $datetime1 = new \DateTime($start_date);
    $datetime2 = new \DateTime(($end_date?:$start_date));

    $interval = $datetime1->diff($datetime2);

    return $interval->format($format);
}

/**
 * Calculate difference in hours between two dates
 *
 * @param $start_date
 * @param $end_date
 * @return float|int
 */
function calcDateDiffInHours($start_date, $end_date)
{
    $difference = strtotime($start_date) - strtotime($end_date);

    $diffInHours = $difference/3600;

    return $diffInHours;
}