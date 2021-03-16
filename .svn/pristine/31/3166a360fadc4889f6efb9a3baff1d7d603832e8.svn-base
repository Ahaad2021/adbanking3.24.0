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

$file_path = '/usr/share/adbanking/web/services/mobile_lending/parametrage_mobile_lending.csv';
$fichier_lot = $file_path;

if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
}


// Lecture des parametres d'age
$handle = fopen($fichier_lot, "r");
$columns_age = array(1,2,3);
$count = 0;
$array_age = array();
echo "Structuration array de lage";echo "\n";
while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }

    foreach ($data as $index => $val) {
        if (in_array($index + 1, $columns_age)) {
            ${"age" . $index} = $val;
        }
    }
    if (!empty($age0)) {
        $array_age[$age2]['age_deb']= $age0;
        $array_age[$age2]['age_fin'] =$age1;
        $array_age[$age2]['tranche_age']=$age2;
    }

}
fclose($handle);

//Lecture du salaire moyen
$handle = fopen($fichier_lot, "r");
$columns_salaire_moyen = array(21,22,23);
$count = 0;
$array_sal_moy = array();
echo "Structuration array du salaire moyen";echo "\n";
while (($data_salaire = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }

    foreach ($data_salaire as $index_salaire => $val_salaire) {
        if (in_array($index_salaire + 1, $columns_salaire_moyen)) {
            ${"sal_moy" . $index_salaire} = $val_salaire;
        }
    }
    if ($sal_moy22 != null && $sal_moy20 == NULL){
        $sal_moy20 = 0;
    }
    if (!empty($sal_moy22)) {
        $array_sal_moy[$sal_moy22]['sal_moy_deb']= $sal_moy20;
        $array_sal_moy[$sal_moy22]['sal_moy_fin'] =$sal_moy21;
        $array_sal_moy[$sal_moy22]['tranche_sal'] =$sal_moy22;
    }

}

//Lecture du taux irregularite
$handle = fopen($fichier_lot, "r");
$columns_tx_irreg = array(9,10,11);
$count = 0;
$array_tx_irreg = array();
echo "Structuration du taux irregularite";echo "\n";
while (($data_tx_irreg = fgetcsv($handle,2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }

    foreach ($data_tx_irreg as $index_tx_irreg => $val_tx_irreg) {
        if (in_array($index_tx_irreg + 1, $columns_tx_irreg)) {
            ${"tx_irreg" . $index_tx_irreg} = $val_tx_irreg;
        }
    }
    if ($tx_irreg8 != '') {
        $array_tx_irreg[$tx_irreg10]['tx_irreg_deb']= $tx_irreg8;
        $array_tx_irreg[$tx_irreg10]['tx_irreg_fin'] =$tx_irreg9;
        $array_tx_irreg[$tx_irreg10]['tranche_irregularite'] =$tx_irreg10;
    }

}

//Lecture du nombre de credits
$handle = fopen($fichier_lot, "r");
$columns_nbre_credit = array(17,18,19);
$count = 0;
$array_nbre_credit = array();
echo "Structuration array  monbre de credit";echo "\n";
while (($data_nbre_credit = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }

    foreach ($data_nbre_credit as $index_nbre_credit => $val_nbre_credit) {
        if (in_array($index_nbre_credit + 1, $columns_nbre_credit)) {
            ${"nbre_credit" . $index_nbre_credit} = $val_nbre_credit;
        }
    }
    if ($nbre_credit16 != '') {
        $array_nbre_credit[$nbre_credit18]['nbre_credit_deb']= $nbre_credit16;
        $array_nbre_credit[$nbre_credit18]['nbre_credit_fin'] =$nbre_credit17;
        $array_nbre_credit[$nbre_credit18]['tranche_nbre_credit'] =$nbre_credit18;
    }

}

//Lecture somme total enprunter
$handle = fopen($fichier_lot, "r");
$columns_somm_tot = array(5,6,7);
$count = 0;
$array_somm_tot = array();
echo "Structuration array somme total emprunter";echo "\n";
while (($data_somm_tot = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }

    foreach ($data_somm_tot as $index_somm_tot => $val_somm_tot) {
        if (in_array($index_somm_tot + 1, $columns_somm_tot)) {
            ${"somm_tot" . $index_somm_tot} = $val_somm_tot;
        }
    }
    if ($somm_tot4 != '') {
        $array_somm_tot[$somm_tot6]['somm_tot_deb']= $somm_tot4;
        $array_somm_tot[$somm_tot6]['somm_tot_fin'] =$somm_tot5;
        $array_somm_tot[$somm_tot6]['tranche_somme_tot_emprunter'] =$somm_tot6;
    }

}

//Lecture longueur historique
$handle = fopen($fichier_lot, "r");
$columns_lg_histo = array(13,14,15);
$count = 0;
$array_lg_histo = array();
echo "Structuration array longueur historique";echo "\n";
while (($data_lg_histo = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }

    foreach ($data_lg_histo as $index_lg_histo => $val_lg_histo) {
        if (in_array($index_lg_histo + 1, $columns_lg_histo)) {
            ${"lg_histo" . $index_lg_histo} = $val_lg_histo;
        }
    }
    if ($lg_histo12 != '') {
        $array_lg_histo[$lg_histo14]['lg_histo_deb']= $lg_histo12;
        $array_lg_histo[$lg_histo14]['lg_histo_fin'] =$lg_histo13;
        $array_lg_histo[$lg_histo14]['tranche_lg_histo'] =$lg_histo14;
    }

}
//Lecture du sexe
$handle = fopen($fichier_lot, "r");
$columns_sexe= array(25,26);
$count = 0;
$array_sexe = array();
echo "Structuration array sexe";echo "\n";
while (($data_sexe = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }

    foreach ($data_sexe as $index_sexe => $val_sexe) {
        if (in_array($index_sexe + 1, $columns_sexe)) {
            ${"sexe" . $index_sexe} = $val_sexe;
        }
    }
    if ($sexe25 != '') {
        $array_sexe[$sexe25]['sexe']= $sexe24;
        $array_sexe[$sexe25]['tranche_sexe']= $sexe25;
    }

}

//Lecture de la localisation
$handle = fopen($fichier_lot, "r");
$columns_loc= array(28,29);
$count = 0;
$array_loc = array();
echo "Structuration array localisation";echo "\n";
while (($data_loc = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }

    foreach ($data_loc as $index_loc => $val_loc) {
        if (in_array($index_loc + 1, $columns_loc)) {
            ${"loc" . $index_loc} = $val_loc;
        }
    }
    if ($loc28 != '') {
        $array_loc[$loc28]['loc']= $loc27;
        $array_loc[$loc28]['tranche_loc']= $loc28;
    }

}


fclose($handle);
/*echo "Array de lage";echo "\n";
var_dump($array_age);
echo "Array du salaire moyen";echo "\n";
var_dump($array_sal_moy);
echo "Array du taux irregularite";echo "\n";
var_dump($array_tx_irreg);
echo "Array du nombre de credit";echo "\n";
var_dump($array_nbre_credit);
echo "Array de la somme total emprunter";echo "\n";
var_dump($array_somm_tot);
echo "Array de la longueur historique";echo "\n";
var_dump($array_lg_histo);
echo "Array du sexe ";echo "\n";
var_dump($array_sexe);*/

//debut des combinaisons\
global $dbHandler, $global_id_agence;
$count_sql = 0;
    foreach ($array_sal_moy as $x1 => $y1) {
        $sql_sal_moy = " salaire_moyen >= ".$y1['sal_moy_deb']." AND salaire_moyen <= ".$y1['sal_moy_fin'];
        $tranche_moy = $y1['tranche_sal'];
        foreach($array_tx_irreg as $x2 => $y2){
            $sql_tx_irreg = "regularite >= ".$y2['tx_irreg_deb']." AND regularite <=".$y2['tx_irreg_fin'];
            $tranche_tx_irreg = $y2['tranche_irregularite'];
            foreach ($array_nbre_credit as $x3 => $y3){
                $sql_nbre_credit = " nbr_credit >= ".$y3['nbre_credit_deb']." AND nbr_credit <= ".$y3['nbre_credit_fin'];
                $tranche_nbre_credit = $y3['tranche_nbre_credit'];
                foreach ($array_somm_tot as $x4 => $y4){
                    $sql_somm_tot = " mnt_tot_emprunts >= ".$y4['somm_tot_deb']." AND mnt_tot_emprunts<= ".$y4['somm_tot_fin'];
                    $tranche_somm_tot = $y4['tranche_somme_tot_emprunter'];;
                            foreach ($array_loc as $x7 => $y7){
                                $count_sql++;
                                $sql_loc = " localisation = '".$y7['loc']."'";
                                $tranche_loc = $y7['tranche_loc'];
                                $db = $dbHandler->openConnection();
                                $sql_total = "select count(*) as nbre_element, sum(score_retard)/count(*) as score from ml_statistique_client_all WHERE $sql_sal_moy AND  $sql_tx_irreg AND $sql_nbre_credit AND $sql_somm_tot  AND $sql_loc";
                                $result = $db->query($sql_total);
                                if (DB::isError($result)) {
                                    $dbHandler->closeConnection(false);
                                    signalErreur(__FILE__, __LINE__, __FUNCTION__);
                                }
                                $row = $result->fetchrow();
                                $db = $dbHandler->closeConnection(true);
                                $nbre_element = $row[0];
                                if ($nbre_element == 0) {
                                    $score = 10000;
                                }else{
                                    $score = $row[1];
                                }
                                $db = $dbHandler->openConnection();
                                $data_combinaison['combinaison'] = $count_sql;
                                $data_combinaison['nbre_dossier'] = $nbre_element;
                                $data_combinaison['score_retard'] = $score;
                                $data_combinaison['tranche_sal_moyen'] = $tranche_moy;
                                $data_combinaison['tranche_irregularite'] = $tranche_tx_irreg;
                                $data_combinaison['tranche_nbre_credit'] = $tranche_nbre_credit;
                                $data_combinaison['tranche_tot_emprunter'] = $tranche_somm_tot;
                                $data_combinaison['tranche_localisation'] = $tranche_loc;
                                // Jason encode les parametres de la requete
                                $data_json = array();
                                $data_json['sal_moyen']= $sql_sal_moy;
                                $data_json['tx_irreg_deb']= $sql_tx_irreg;
                                $data_json['nbre_credit_deb']= $sql_nbre_credit;
                                $data_json['mnt_tot_emprunts']= $sql_somm_tot;
                                $data_json['loc']= str_replace("'", "''", $sql_loc);
                                $data_combinaison['data_combinaison'] = json_encode($data_json);
                                $sql_query = buildInsertQuery("ml_combinaison_global", $data_combinaison);

                                $result = $db->query($sql_query);
                                if (DB :: isError($result)) {
                                    echo $sql_query;
                                    $dbHandler->closeConnection(false);
                                    signalErreur(__FILE__, __LINE__, __FUNCTION__, $sql_query);
                                }
                                $dbHandler->closeConnection(true);
                                echo $count_sql."\n";
                                //

                                //$sql_total = "select * from ml_donnees_client_credit WHERE $sql_age AND $sql_sal_moy AND  $sql_tx_irreg AND $sql_nbre_credit AND $sql_somm_tot AND $sql_lg_histo AND $sql_sexe";
                                // echo $sql_total."\n";

                            }
                        }
                    }
                }


}

echo "le nombre de combinaison final est de ".$count_sql;


