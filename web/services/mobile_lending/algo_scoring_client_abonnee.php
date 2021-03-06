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
//$DB_pass = "public";
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

/**********************************************************************************************************/

//recuperation des parametres pour le calculs des donnees clients abonnees
echo 'here ';
// Recuperation du pourcentage du montant max
$file_path = '/usr/share/adbanking/web/services/mobile_lending/parametrage_mobile_lending.csv';
$fichier_lot = $file_path;
$handle = fopen($fichier_lot, "r");
$columns_mnt_max = array(41);
$count = 0;
while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }
    foreach ($data as $index => $val) {
        if (in_array($index + 1, $columns_mnt_max) && $val != null) {
            // echo$index; echo " <=> ";  echo $val; echo "\n";
            ${"prc_mnt_max" . $index} = $val;echo  $val;
        }
    }
}



// recuperation du alpha de coefficient present
$handle = fopen($fichier_lot, "r");
$columns_coeff_present= array(43);
$count = 0;
while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count == 0) {
        $count++;
        continue;
    }
    foreach ($data as $index => $val) {
        if (in_array($index + 1, $columns_coeff_present) && $val != null) {
            // echo$index; echo " <=> ";  echo $val; echo "\n";
            ${"coeff_present" . $index} = $val;echo  $val;
        }
    }
}

// montant max empruner
$handle = fopen($fichier_lot, "r");
echo "\n";echo "\n";
$columns_mnt_max_emprunt = array(35);
$count_coeff = 0;
echo "Data pour le mnt max a emprunter";echo "\n";
while (($data_mnt_emprunter = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_mnt_emprunter as $index_mnt_emprunter => $val_mnt_emprunter) {
        if (in_array($index_mnt_emprunter + 1, $columns_mnt_max_emprunt)) {
            if ($val_mnt_emprunter != null){
                $mnt_max_emprunter = $val_mnt_emprunter;
            }
        }
    }
}
//echo $mnt_max_emprunter."\n";
fclose($handle);

// montant max nouveau client
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_mnt_max_new_client = array(36);
$count_coeff = 0;
//echo "Data pour le mnt max new client";echo "\n";
while (($data_mnt_max_new_client = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_mnt_max_new_client as $index_mnt_max_new_client => $val_mnt_max_new_client) {
        if (in_array($index_mnt_max_new_client + 1, $columns_mnt_max_new_client)) {
            if ($val_mnt_max_new_client != null){
                $mnt_max_new_client = $val_mnt_max_new_client;
            }
        }
    }
}
//echo $mnt_max_new_client."\n";
fclose($handle);

// Lecture des coefficients def irregularite
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_coeff = array(45);
$count_coeff = 0;
//echo "Data pour les coefficients df irregularite";echo "\n";
while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_coeff as $index_coeff => $val_coeff) {
        if (in_array($index_coeff + 1, $columns_coeff)) {
            if ($val_coeff != null){
                $coeff_def_irregularite= $val_coeff;
            }
        }
    }
}

fclose($handle);


// Appelle de la fonction de mise a jour des donnees clients
$db = $dbHandler->openConnection();
$sql = "SELECT extraction_donnees_mobile_lending_v2();";
//echo $sql;
$result = $db->query($sql);
if (DB::isError($result)) {
    echo "Failed => Fonction mise a jour des donnees credits";echo "\n";
    $dbHandler->closeConnection(false);
}else{
    echo "Succes";echo "\n";
    $dbHandler->closeConnection(true);
}

// Appelle de la fonction de mise a jour des donnees clients
$db = $dbHandler->openConnection();
$sql = "SELECT mise_a_jour_donnee_abonnee(".$prc_mnt_max40.",".$coeff_present42.",".$mnt_max_emprunter.", ".$mnt_max_new_client.",".$coeff_def_irregularite.");";
//echo $sql;
$result = $db->query($sql);
if (DB::isError($result)) {
    echo "Failed => Fonction mise a jour des clients abonnees";echo "\n";
    $dbHandler->closeConnection(false);
}else{
    echo "Succes";echo "\n";
    $dbHandler->closeConnection(true);
}


/******************************************UPDATE TRACNHE DATA**********************************************************/

$file_path = '/usr/share/adbanking/web/services/mobile_lending/parametrage_mobile_lending.csv';
$fichier_lot = $file_path;

if (!file_exists($fichier_lot)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_FICHIER_DONNEES);
}


// SAlaire moyen
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_sal_moy = array(21,22,23);
$count_sal_moy = 0;
//echo "Data pour le salaire moyen";echo "\n";
while (($data_sal_moy = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_sal_moy == 0) {
        $count_sal_moy++;
        continue;
    }
    foreach ($data_sal_moy as $index_sal_moy => $val_sal_moy) {
        if (in_array($index_sal_moy + 1, $columns_sal_moy)) {
            ${"sal_moy" . $index_sal_moy} = $val_sal_moy;
        }
    }

    if (!empty($sal_moy22)) {
        if ($sal_moy20 == NULL){
            $sal_moy20 = 0;
        }
        $db = $dbHandler->openConnection();
        $sql_sal_moy = "UPDATE ml_donnees_client_abonnees SET tranche_sal_moyen = $sal_moy22 WHERE salaire_moyen>= $sal_moy20 and salaire_moyen <= $sal_moy21"; echo $sql_sal_moy."\n";

        $result_mnt_sal = $db->query($sql_sal_moy);
        if (DB::isError($result_mnt_sal)) {
            echo "Failed";
            echo "\n";
            $dbHandler->closeConnection(false);
        } else {
            echo "Succes";
            echo "\n";
            $dbHandler->closeConnection(true);
        }
    }
}
fclose($handle);

//irregularite
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_irregularite= array(9,10,11);
$count_irregularite = 0;
//echo "Data pour irregularite";echo "\n";
while (($data_irregularite = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if($count_irregularite == 0){ $count_irregularite++; continue; }
    foreach ($data_irregularite as $index_irregularite => $val_irregularite) {
        if (in_array($index_irregularite + 1, $columns_irregularite)) {
            ${"irregularite" . $index_irregularite} = $val_irregularite;
        }
    }

    if (!empty($irregularite10)) {
        $db = $dbHandler->openConnection();
        $sql_irregularite= "UPDATE ml_donnees_client_abonnees SET tranche_irregularite = $irregularite10 WHERE tx_irregularite>= $irregularite8 and tx_irregularite <= $irregularite9";
        $result_mnt_dem = $db->query($sql_irregularite);
        if (DB::isError($result_mnt_dem)) {
            echo "Failed";echo "\n";
            $dbHandler->closeConnection(false);
        }else{echo "Succes";echo "\n";
            $dbHandler->closeConnection(true);
        }
        echo "mise a jour base de donnees \n";
    }
}
fclose($handle);

// Lecture de la tranche nbre de credit
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_nb_credit = array(17,18,19);
$count_nb_credit = 0;
//echo "Data pour les tranche nbre credit";echo "\n";
while (($data_nb_credit = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if($count_nb_credit == 0){ $count_nb_credit++; continue; }
    foreach ($data_nb_credit as $index_nb_credit => $val_nb_credit) {
        if (in_array($index_nb_credit + 1, $columns_nb_credit)) {
            ${"nb_credit" . $index_nb_credit} = $val_nb_credit;
        }
    }

    if (!empty($nb_credit18)) {
        $db = $dbHandler->openConnection();
        $sql_nbre_credit= "UPDATE ml_donnees_client_abonnees SET tranche_nbre_credit = $nb_credit18 WHERE nbre_credit>= $nb_credit16 and nbre_credit <= $nb_credit17";
        $result_nbre_credit= $db->query($sql_nbre_credit);
        if (DB::isError($result_nbre_credit)) {
            echo "Failed";echo "\n";
            $dbHandler->closeConnection(false);
        }else{echo "Succes";echo "\n";
            $dbHandler->closeConnection(true);
        }
    }
}
fclose($handle);

//Lecture mnt tot emprunter
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_salaire = array(5,6,7);
$count_salaire = 0;
//echo "Data pour les tranche Mnt demande";echo "\n";
while (($data_salaire = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if($count_salaire == 0){ $count_salaire++; continue; }
    foreach ($data_salaire as $index_salaire => $val_salaire) {
        if (in_array($index_salaire + 1, $columns_salaire)) {
            ${"salaire" . $index_salaire} = $val_salaire;
        }
    }
    if (!empty($salaire6)) {
        if ($salaire4 == NULL){
            $salaire4 = 0;
        }
        $db = $dbHandler->openConnection();
        $sql_mnt_dem= "UPDATE ml_donnees_client_abonnees SET tranche_tot_emprunter= $salaire6 WHERE mnt_tot_emprunter>= $salaire4 and mnt_tot_emprunter <= $salaire5";
        $result_mnt_dem = $db->query($sql_mnt_dem);
        if (DB::isError($result_mnt_dem)) {
            echo "Failed";echo "\n";
            $dbHandler->closeConnection(false);
        }else{echo "Succes";echo "\n";
            $dbHandler->closeConnection(true);
        }
        echo "mise a jour base de donnees \n";
    }
}
fclose($handle);

//Lecture tranche age
$handle = fopen($fichier_lot, "r");

$columns_age = array(1,2,3);
$count_age = 0;

while (($data_age = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if($count_age == 0){ $count_age++; continue; }
    foreach ($data_age as $index_age => $val_age) {
        if (in_array($index_age + 1, $columns_age)) {
            ${"age" . $index_age} = $val_age;
        }
    }
    if (!empty($age2)) {
        if ($age0 == NULL){
            $age0 = 0;
        }
        $db = $dbHandler->openConnection();
        $sql_age= "UPDATE ml_donnees_client_abonnees SET tranche_age= $age2 WHERE age>= $age0 and age <= $age1";
        $result_age = $db->query($sql_age);
        if (DB::isError($result_age)) {
            echo "Failed age";echo "\n";
            $dbHandler->closeConnection(false);
        }else{
            $dbHandler->closeConnection(true);
        }
    }
}
fclose($handle);

//Lecture tranche lg_histo
$handle = fopen($fichier_lot, "r");

$columns_lg_histo= array(13,14,15);
$count_lg_histo = 0;

while (($data_lg_histo = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if($count_lg_histo == 0){ $count_lg_histo++; continue; }
    foreach ($data_lg_histo as $index_lg_histo => $val_lg_histo) {
        if (in_array($index_lg_histo + 1, $columns_lg_histo)) {
            ${"lg_histo" . $index_lg_histo} = $val_lg_histo;
        }
    }
    if (!empty($lg_histo14)) {
        if ($lg_histo12 == NULL){
            $lg_histo12 = 0;
        }
        $db = $dbHandler->openConnection();
        $sql_lg_histo= "UPDATE ml_donnees_client_abonnees SET tranche_lg_histo= $lg_histo14 WHERE lg_histo>= $lg_histo12 and lg_histo <= $lg_histo13";
        $result_lg_histo = $db->query($sql_lg_histo);
        if (DB::isError($result_lg_histo)) {
            echo "Failed lg histo";echo "\n";
            $dbHandler->closeConnection(false);
        }else{
            $dbHandler->closeConnection(true);
        }
    }
}
fclose($handle);

// Lecture des coefficients passe
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_coeff = array(47);
$count_coeff = 0;
//echo "Data pour les coefficients de calcul de score";echo "\n";
while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_coeff as $index_coeff => $val_coeff) {
        if (in_array($index_coeff + 1, $columns_coeff)) {
            if ($val_coeff != null){
                $coeff_passe = $val_coeff;
            }
        }
    }
}

fclose($handle);

// Lecture des coefficients present
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_coeff = array(49);
$count_coeff = 0;
//echo "Data pour les coefficients de calcul de score";echo "\n";
while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_coeff as $index_coeff => $val_coeff) {
        if (in_array($index_coeff + 1, $columns_coeff)) {
            if ($val_coeff != null){
                $coeff_present = $val_coeff;
            }
        }
    }
}

fclose($handle);

// Lecture des coefficients futur
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_coeff = array(51);
$count_coeff = 0;
//echo "Data pour les coefficients de calcul de score";echo "\n";
while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_coeff as $index_coeff => $val_coeff) {
        if (in_array($index_coeff + 1, $columns_coeff)) {
            if ($val_coeff != null){
                $coeff_futur = $val_coeff;
            }
        }
    }
}

fclose($handle);

// Lecture des coefficients present sans credit
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_coeff = array(53);
$count_coeff = 0;
//echo "Data pour les coefficients de calcul de score present sans credit";echo "\n";
while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_coeff as $index_coeff => $val_coeff) {
        if (in_array($index_coeff + 1, $columns_coeff)) {
            if ($val_coeff != null){
                $coeff_present_new_client = $val_coeff;
            }
        }
    }
}

fclose($handle);

// Lecture des coefficients futur sans credit
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_coeff = array(55);
$count_coeff = 0;
//echo "Data pour les coefficients de calcul de score futur sans credit";echo "\n";
while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_coeff as $index_coeff => $val_coeff) {
        if (in_array($index_coeff + 1, $columns_coeff)) {
            if ($val_coeff != null){
                $coeff_futur_new_client = $val_coeff;
            }
        }
    }
}

fclose($handle);
//echo $coeff_passe."\n".$coeff_present."\n".$coeff_futur."\n".$coeff_present_new_client."\n".$coeff_futur_new_client;

// Lecture des coefficients passe sans combinaison avec credit
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_coeff = array(57);
$count_coeff = 0;
//echo "Data pour les coefficients de calcul de score passe sans combinaison avec credit";echo "\n";
while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_coeff as $index_coeff => $val_coeff) {
        if (in_array($index_coeff + 1, $columns_coeff)) {
            if ($val_coeff != null){
                $coeff_passe_sans_combi_avec_credit= $val_coeff;
            }
        }
    }
}

fclose($handle);

// Lecture des coefficients present sans combinaison avec credit
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_coeff = array(59);
$count_coeff = 0;
//echo "Data pour les coefficients de calcul de score present sans combinaison avec credit";echo "\n";
while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_coeff as $index_coeff => $val_coeff) {
        if (in_array($index_coeff + 1, $columns_coeff)) {
            if ($val_coeff != null){
                $coeff_present_sans_combi_avec_credit= $val_coeff;
            }
        }
    }
}

fclose($handle);

// Lecture des coefficients present sans combinaison sans credit
$handle = fopen($fichier_lot, "r");
//echo "\n";echo "\n";
$columns_coeff = array(61);
$count_coeff = 0;
//echo "Data pour les coefficients de calcul de score present sans combinaison sans credit";echo "\n";
while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if ($count_coeff == 0) {
        $count_coeff++;
        continue;
    }
    foreach ($data_coeff as $index_coeff => $val_coeff) {
        if (in_array($index_coeff + 1, $columns_coeff)) {
            if ($val_coeff != null){
                $coeff_present_sans_combi_sans_credit= $val_coeff;
            }
        }
    }
}

fclose($handle);
/*echo "\n".$coeff_passe_sans_combi_avec_credit;
echo "\n".$coeff_present_sans_combi_avec_credit;
echo "\n".$coeff_present_sans_combi_sans_credit;*/

    /*****************************FIN UPDATE TRANCHE DATA CLIENT***********************************************/
// Recuperation score futur dans la table des combinaisons globales
$data_client_abonnee = getDataClientAbonnee();
// fonction getLocalisationIMF
$localisation_imf = getLocalisationIMF();
$loc_imf = $localisation_imf['ml_localisation'];

//var_dump($data_client_abonnee);
foreach ($data_client_abonnee as $index => $value){
    $combinaison_pleine = 'f';
    $score= 0;
    $db = $dbHandler->openConnection();
    $sql = "SELECT score_retard,nbre_dossier FROM ml_combinaison_global WHERE  tranche_sal_moyen = ".$value['tranche_sal_moyen']." AND  tranche_irregularite= ".$value['tranche_irregularite']." AND tranche_nbre_credit= ".$value['tranche_nbre_credit']." AND tranche_tot_emprunter= ".$value['tranche_tot_emprunter']." AND tranche_localisation = ".$loc_imf;
    //echo $sql."\n";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        echo "Failed => ".$sql;
        echo "\n";
        $dbHandler->closeConnection(false);
    }
    $row = $result->fetchrow();
    $db = $dbHandler->closeConnection(true);
    $score = $row[0];
    $nbre_doss = $row[1];
    if ($nbre_doss >= 3){
        $combinaison_pleine = 't';
    }
    //echo "\n le score est de => ".$score."\n";

    /******************Recuperation des Bonus garantit****************/
    $ratio_bonus_gar = 0;
    $bonus_gar_calculer = 0;
    $garantit_en_cours = getGarantiEnCours($value['client']);
    if ($garantit_en_cours > 0) {
        $ratio_bonus_gar = ($garantit_en_cours / $value['mnt_restant_du']) * 100;

        // Lecture des bonus de garanti
        $handle = fopen($fichier_lot, "r");
//        echo "\n";
//        echo "\n";
        $columns_bonus = array(31,32,33);
        $count_bonus = 0;
//        echo "Data pour les Bonus de garantit";
//        echo "\n";
//        echo $value['client'];
//        echo "\n";
        while (($data_bonus = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($count_bonus == 0) {
                $count_bonus++;
                continue;
            }
            foreach ($data_bonus as $index_bonus => $val_bonus) {
                if (in_array($index_bonus + 1, $columns_bonus)) {
                    ${"bonus_gar" . $index_bonus} = $val_bonus;
                }
            }

            if (!empty($bonus_gar32)) {
                if (($ratio_bonus_gar > $bonus_gar30) && ($ratio_bonus_gar <= $bonus_gar31)) {
                    $bonus_gar_calculer = $bonus_gar32;
                }
            }
        }
        fclose($handle);
    }
    /*****************************************************/

    // Lecture de la constante 1
    $handle = fopen($fichier_lot, "r");
    //echo "\n";echo "\n";
    $columns_c1 = array(63);
    $count_c1 = 0;
    while (($data_c1 = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_c1 == 0) {
            $count_c1++;
            continue;
        }
        foreach ($data_c1 as $index_c1 => $val_c1) {
            if (in_array($index_c1 + 1, $columns_c1)) {
                if ($val_c1 != null){
                    $coeff_c1= $val_c1;
                }
            }
        }
    }

    fclose($handle);

    // Lecture de la constante 2
    $handle = fopen($fichier_lot, "r");
    //echo "\n";echo "\n";
    $columns_c2 = array(65);
    $count_c2 = 0;
    while (($data_c2 = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_c2 == 0) {
            $count_c2++;
            continue;
        }
        foreach ($data_c2 as $index_c2 => $val_c2) {
            if (in_array($index_c2 + 1, $columns_c2)) {
                if ($val_c2 != null){
                    $coeff_c2= $val_c2;
                }
            }
        }
    }

    fclose($handle);

    // Lecture de la constante 3
    $handle = fopen($fichier_lot, "r");
    //echo "\n";echo "\n";
    $columns_c3 = array(67);
    $count_c3 = 0;
    while (($data_c3 = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_c3 == 0) {
            $count_c3++;
            continue;
        }
        foreach ($data_c3 as $index_c3 => $val_c3) {
            if (in_array($index_c3 + 1, $columns_c3)) {
                if ($val_c3 != null){
                    $coeff_c3= $val_c3;
                }
            }
        }
    }

    fclose($handle);

    // Lecture de la constante 4
    $handle = fopen($fichier_lot, "r");
    //echo "\n";echo "\n";
    $columns_c4 = array(69);
    $count_c4 = 0;
    while (($data_c4 = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if ($count_c4 == 0) {
            $count_c4++;
            continue;
        }
        foreach ($data_c4 as $index_c4 => $val_c4) {
            if (in_array($index_c4 + 1, $columns_c4)) {
                if ($val_c4 != null){
                    $coeff_c4= $val_c4;
                }
            }
        }
    }

    fclose($handle);




    //calcul du score finale
    if ($value['nbre_credit'] > 0){
        if ($score == 10000){
            $score_final = $coeff_passe_sans_combi_avec_credit * $value['score_passe'] + $coeff_present_sans_combi_avec_credit * $value['score_present'] + $bonus_gar_calculer +$coeff_c3;
            $score_final = round($score_final, 2);
        }else {
            $score_final = $coeff_passe * $value['score_passe'] + $coeff_present * $value['score_present'] + $coeff_futur * $score + $bonus_gar_calculer+$coeff_c1;
            $score_final = round($score_final, 2);
        }
    }
    else{
        if ($score == 10000){
            $score_final =  $coeff_present_sans_combi_sans_credit * $value['score_present'] +$bonus_gar_calculer+ $coeff_c4;
            $score_final = round($score_final, 2);
        }else {
            $score_final = $coeff_present_new_client * $value['score_present'] + $coeff_futur_new_client * $score +$bonus_gar_calculer + $coeff_c2;
            $score_final = round($score_final, 2);
        }
    }
    //Malus Actif moins de 3 mois
    if ($value['actif_3_mois'] == 'f'){
        $score_final = $score_final - 80;
    }
    if ($value['salaire_moyen_non_nul'] == 'f'){
        $score_final = $score_final - 80;
    }


 //echo "\n Le score du client".$value['client']." est de ". $score_final;

    $db = $dbHandler->openConnection();
    $sql_update_score_futur = "UPDATE ml_donnees_client_abonnees SET score_futur = $score, score_final = $score_final, bonus_gar = $bonus_gar_calculer, combinaison_pleine = '".$combinaison_pleine."' WHERE client =". $value['client'];
    //echo $sql_update_score_futur."\n";
    $result_score_futur = $db->query($sql_update_score_futur);
    if (DB::isError($result_score_futur)) {
        echo "Failed => ".$sql_update_score_futur;
        echo "\n";
        $dbHandler->closeConnection(false);
    } else {
//        echo "Succes";
//        echo "\n";
        $dbHandler->closeConnection(true);
    }

    $db = $dbHandler->openConnection();
    $sql_update_abo = "UPDATE ad_abonnement SET ml_score = $score_final WHERE id_client =". $value['client']." AND deleted = 'f' AND id_service = 1";
    $result_abo = $db->query($sql_update_abo);
    if (DB::isError($result_abo)) {
        echo "Failed";
        echo "\n";
        $dbHandler->closeConnection(false);
    } else {
//        echo "Succes";
//        echo "\n";
        $dbHandler->closeConnection(true);
    }

}

// Script de categorisation des clients salaries
$db = $dbHandler->openConnection();
$sql_salarie = "select recup_salarie();";
$result_salarie = $db->query($sql_salarie);
if (DB::isError($result_salarie)) {
    echo "Failed";
    echo "\n";
    $dbHandler->closeConnection(false);
} else {
//      echo "Succes recup_salarie()";
//        echo "\n";
    $dbHandler->closeConnection(true);
}

?>