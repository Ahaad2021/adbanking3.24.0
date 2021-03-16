<?php


require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/tireur_benef.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/net_bank.php';
require_once 'lib/dbProcedures/cheque_interne.php';
require_once 'lib/misc/tableSys.php';


function get_comptes_epargne_non_commander($id_client, $devise=NULL) {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "select c.*,e.* from ad_cpt c 
inner join adsys_produit_epargne e on e.id = c.id_prod
INNER JOIN ad_abonnement_atm b on b.id_cpte = c.id_cpte
LEFT JOIN ad_carte_atm m ON m.id_cpte = c.id_cpte
WHERE ((m.id_cpte is null or (m.id_cpte is not null and m.etat_carte IN (8,9))) 
or (m.id_cpte is not null and m.etat_carte = 5 and now() between m.date_carte_expiration - interval '30 days' and m.date_carte_expiration))
and b.id_client = $id_client ";
    // On ne prend pas les comptes bloqués
    if (!is_client_radie()){
        $sql .= " AND (c.etat_cpte <> 2)";
    }
    if ($devise != NULL)
        $sql .= " AND c.devise = '$devise'";

    $sql .= " ORDER BY c.num_complet_cpte";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) return NULL;

    $TMPARRAY = array();
    while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $TMPARRAY[$prod["id_cpte"]] = $prod;
        $TMPARRAY[$prod["id_cpte"]]["soldeDispo"] = getSoldeDisponible($prod["id_cpte"]);
    }

    return $TMPARRAY;
}

function getCarteATM($condition){
    global $dbHandler;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ad_carte_atm";

    if(!empty($condition)){
        $sql .= " WHERE $condition";
    }

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dataset = array();
    while($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        $dataset[] = $row;
    }

    $dbHandler->closeConnection(true);
    return $dataset;
}

function exportImpressionCarte($references, $dataset){
    global $dbHandler, $global_id_agence, $global_nom_login;

    $id = getNextSequence('ad_commande_carte_his_id_seq');
    $file_path = "/tmp/export_carte_atm/export_impression_carte_".date("dmY")."_".$id.".csv";

    //UPDATE ETAT
    $db = $dbHandler->openConnection();
    $DATA["etat_carte"] = 2;
    $DATA["date_envoi_impression"] = date("d/m/Y H:i:s");
    $sql = buildUpdateQuery("ad_carte_atm",$DATA, array("id_carte IN ($references)/*" => "*/--"));

    // Insertion dans la DB
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        return $result->errCode;
    }

    $dbHandler->closeConnection(true);
    $db = $dbHandler->openConnection();
    //LOG ad_his
    $ad_his_id = ajout_historique(808, NULL, NULL, $global_nom_login, date("r"), NULL);

    //LOGS export metadata
    $element= array_shift($dataset); //remove the header first
    $DATA_INSERT['date_traitement'] = date('d/m/Y H:i:s');
    $DATA_INSERT['nom_interne'] = basename($file_path);
    $DATA_INSERT['chemin_fichier'] = $file_path;
    $DATA_INSERT['nbre_cartes'] = count($dataset);
    $DATA_INSERT['id_ag'] = $global_id_agence;
    $DATA_INSERT['id_his'] = $ad_his_id->param;

    $sql = buildInsertQuery("ad_commande_carte_his", $DATA_INSERT);
    $result = executeQuery($db, $sql);

    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        return $result->errCode;
    }

    $dbHandler->closeConnection(true);

    //FILE CREATION
    array_unshift($dataset, $element);
    if(!file_exists('/tmp/export_carte_atm')) {
        mkdir('/tmp/export_carte_atm');
    }

    exec("chmod 777 -R /tmp/export_carte_atm");
    $file_handler = fopen($file_path, 'w');
    foreach($dataset as $fields){

        fputcsv($file_handler, $fields);
    }

    fclose($file_handler);

    return new ErrorObj(NO_ERR, "", null, $file_path);
}
function getCommandeCarteHistorique(){
    global $dbHandler, $global_id_agence;
    $dbHandler->closeConnection(true);
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ad_commande_carte_his WHERE id_ag = $global_id_agence";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dataset = array();
    while($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        $dataset[] = $row;
    }

    return $dataset;
}

function parse_ajout_carte_atm_imprimer_fichier($fichier_lot) {

    global $global_id_agence, $dbHandler;
    $db = $dbHandler->openConnection();

    if (!file_exists($fichier_lot)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_FICHIER_DONNEES);
    }
    $handle = fopen($fichier_lot, 'r');
    //$AGC = getAgenceDatas($global_id_agence);
    //$devise = $AGC['code_devise_reference'];
    $count = 0;
    $count_failed = 0;
    $count_success = 0;
    $num_complet_cpte = "";
    $tabErreur =array();

    while (($data = fgetcsv($handle, 200, ',')) != false) {
        $passed = true;
        $count++;
        $num = count($data);
        if ($num != 12) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_NBR_COLONNES, array("ligne" => $num));
        }
        if ($count == 1) {
            $array_header = array();
            $array_header = $data;
            continue;
        }
        // Id de la carte
        $id_carte = $data[0];


        //date de la demande
        $date_dem = $data[1];

        //id client
        $id_cli = $data[2];

        //numero de compte
        $num_cpte = $data[3];

        //nom
        $nom = $data[4];

        //id
        $id = $data[5];

        //Prestataire
        $prestataire = $data[6];

        //motif de la demande
        $motif_dem = $data[7];

        //reference
        $reference = $data[8];

        //num carte
        $num_carte = $data[9];
        $data_carte_actif = getCarteATM("num_carte_atm = '" . $num_carte. "' ");
        $nbre_char = strlen(trim($data[9]));
        if (empty($num_carte) || $nbre_char != 16 || sizeof($data_carte_actif) > 0){
            $passed = false;
        }

        //date_deb carte
        $date_debut_carte = $data[10];
        if (empty($date_debut_carte)){
            $passed = false;
        }

        //date exipration carte
        $date_exp_carte = $data[11];
        if (empty($date_exp_carte)){
            $passed = false;
        } else if (isBefore($date_exp_carte,$date_debut_carte,true)){
            $passed = false;
        }


       /* if (isNumComplet($data[0])) {
            $id_cpte = get_id_compte($data[0]);
            if($id_cpte==NULL){
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_NUM_COMPLET_CPTE_NOT_EXIST, array("ligne" => $count));
            }
            $num_complet_cpte = $data[0];
        } else {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_NUM_COMPLET_CPTE, array("ligne" => $count));
        }*/
       if ($passed == true) {
           $count_success ++;
           $DATA[$count] = array(
               'id_carte' => $id_carte,
               'date_demande' => $date_dem,
               'id_client' => $id_cli,
               'num_cpte' => $num_cpte,
               'nom' => $nom,
               'id' => $id,
               'prestataire' => $prestataire,
               'motif_demande' => $motif_dem,
               'reference' => $reference,
               'num_carte_atm' => $num_carte,
               'date_carte_debut_validite' => $date_debut_carte,
               'date_carte_expiration' => $date_exp_carte,
               'passed' => $passed
           );
       } else {
           $count_failed ++;
           $DATA_failed[$count_failed] = array(
               'id_carte' => $id_carte,
               'date_demande' => $date_dem,
               'id_client' => $id_cli,
               'num_cpte' => $num_cpte,
               'nom' => $nom,
               'id' => $id,
               'prestataire' => $prestataire,
               'motif_demande' => $motif_dem,
               'reference' => $reference,
               'num_carte_atm' => $num_carte,
               'date_carte_debut_validite' => $date_debut_carte,
               'date_carte_expiration' => $date_exp_carte,
               'passed' => $passed
           );
       }
        //$total += $montant;
    }
    fclose($handle);

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR,"",null, array('header' => $array_header, 'data' => $DATA, 'data_failed' => $DATA_failed,'counter_succes' => $count_success, 'counter_failed' => $count_failed));
}

function getRetraitCarte($id_client){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ad_carte_atm WHERE id_ag = $global_id_agence and etat_carte = 3 and id_client = $id_client";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dataset = array();
    while($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        $dataset[] = $row;
    }

    $dbHandler->closeConnection(true);
    return $dataset;
}

function UpdateTable($table_name,$array_data, $array_condi){
    global $global_id_agence, $global_id_client, $dbHandler;
    $db = $dbHandler->openConnection();

    $sql = buildUpdateQuery($table_name, $array_data, $array_condi);

    $result = $db->query($sql);
    if ($result->errCode != NO_ERR){
        $dbHandler->closeConnection(false);
        return new ErrorObj($result->errCode);
    }
    $dbHandler->closeConnection(true);
    return true;
}

function preleveFraisAbonnementATM($type_frais, $id_client, $type_oper = 180, $montant_transaction = 0, $type_fonction = null, $id_doss = null, $source = 2) {
    global $dbHandler, $global_nom_login, $global_id_client, $global_id_agence, $global_monnaie;

    $comptable = array();
    if(is_null($type_fonction)) $type_fonction = 13;

    $type_frais_arr = array('SMS_REG' => 'Frais d\'activation du service', 'SMS_MTH' => 'Frais forfaitaires mensuels', 'SMS_TRC' => 'Frais transfert de compte à compte', 'SMS_EWT' => 'Frais transfert vers E-wallet', 'ESTAT_REG' => 'Frais d\'activation du service eStatement', 'ESTAT_MTH' => 'Frais forfaitaires mensuels eStatement');

    $myErr = new ErrorObj(NO_ERR);

    $tarif = getTarificationDatas($type_frais);

    // Prélèvement des frais d'abonnement
    if (is_array($tarif) && count($tarif) > 0) {

        //$compteCptaProdFrais = $tarif['compte_comptable'];
        $mode_frais = $tarif['mode_frais'];

        if ($mode_frais == 2 && $montant_transaction > 0) {
            $percentage = $tarif['valeur'];

            if ($percentage > 100) {
                $percentage = 100;
            } elseif ($percentage <= 0) {
                $percentage = 1;
            }

            $montant = ($montant_transaction * ($percentage / 100));
        } else {
            $montant = $tarif['valeur'];
        }

        if ($montant > 0) {

            // Get client compte de base
            $id_cpte_source = ($source == 3)?getRembAccountID($id_client, $id_doss):getBaseAccountID($id_client);

            // Aucun compte de base n'est associé à ce client
            if ($id_cpte_source == NULL) {
                //signalErreur(__FILE__, __LINE__, __FUNCTION__);
                $myErr = new ErrorObj(ERR_CPTE_INEXISTANT);
            }

            // Get compte comptable info
            //$InfoCpteCompta = getComptesComptables(array("num_cpte_comptable" => $compteCptaProdFrais));

            // Le compte comptable n'existe pas
            /*
            if (!is_array($InfoCpteCompta[$compteCptaProdFrais]) || (is_array($InfoCpteCompta[$compteCptaProdFrais]) && count($InfoCpteCompta[$compteCptaProdFrais])==0)) {
                //signalErreur(__FILE__, __LINE__, __FUNCTION__);
                $myErr = new ErrorObj(ERR_CPTE_NON_PARAM, $type_frais_arr[$tarif['type_de_frais']]);
            }
            */

            // Infos compte source
            $InfoCpteSource = getAccountDatas($id_cpte_source);

            // Le compte source n'existe pas
            if (!is_array($InfoCpteSource) || (is_array($InfoCpteSource) && count($InfoCpteSource)==0)) {
                $myErr = new ErrorObj(ERR_CPTE_CLI_NEG);
            }

            $soldeDispo = getSoldeDisponible($id_cpte_source);

            if ($soldeDispo < $montant) {
                if ($type_frais == 'ATM_REG') {
                    // Mise en attente : Prélèvement des frais d'abonnement
                    $date = DateTime::createFromFormat('U.u', microtime(true));
                    $sql = "INSERT INTO ad_frais_attente (id_cpte,id_ag, date_frais, type_frais, montant) VALUES (".$id_cpte_source.", ".$global_id_agence.", '".$date->format('Y-m-d H:i:s.u')."', ".$type_oper.", ".$montant.")";;
                    $result = executeDirectQuery($sql);

                    if ($result->errCode != NO_ERR) {
                        $myErr = new ErrorObj($result->errCode);
                    }
                } else {
                    $myErr = new ErrorObj(ERR_CPTE_CLI_NEG);
                }
            } else {
                // Passage des écritures comptables : débit client / crédit client
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();

                // Débit d'un compte client
                $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    $dbHandler->closeConnection(false);
                    $myErr = new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }
                $cptes_substitue["int"]["debit"] = $id_cpte_source;

                // Crédit d'un compte produit
                /*
                $cptes_substitue["cpta"]["credit"] = $compteCptaProdFrais;
                if ($cptes_substitue["cpta"]["credit"] == NULL) {
                  $dbHandler->closeConnection(false);
                  $myErr = new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé à l'abonnement"));
                }

                if ($InfoCpteSource['devise'] != $InfoCpteCompta[$compteCptaProdFrais]['devise']) {
                    $myErr = new ErrorObj(ERR_DEVISE_CPT_DIFF);
                } else {
                    $myErr = passageEcrituresComptablesAuto($type_oper, $montant, $comptable, $cptes_substitue, $InfoCpteSource['devise'], NULL, $id_cpte_source);
                }
                */

                $myErr = passageEcrituresComptablesAuto($type_oper, $montant, $comptable, $cptes_substitue, $InfoCpteSource['devise'], NULL, $id_cpte_source);
            }

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            } else {
                $myErr = ajout_historique($type_fonction, $InfoCpteSource["id_titulaire"], '', $global_nom_login, date("r"), $comptable);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
        }
    }
    /*else {
        if(function_exists('print_rn')) {
            print_rn($type_frais);echo $id_client;die;
        } else {
            var_dump($type_frais);echo $id_client;die;
        }

        //echo json_encode($type_frais, 1 | 4 | 2 | 8);
        //exit;
        //signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }*/

//    $dbHandler->closeConnection(true);
    return $myErr;
}

function expirationCarteAtm($date_total){
    global $dbHandler, $global_nom_login, $global_id_client, $global_id_agence, $global_monnaie;

    affiche("Début de verification des dates des cartes pour expiration...");
    incLevel();

    $db = $dbHandler->openConnection();
    $sql = " SELECT id_carte from ad_carte_atm where date(date_carte_expiration) = date('$date_total') and etat_carte = 5";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dataset = array();
    while($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        $dataset[] = $row;
    }
    $dbHandler->closeConnection(true);
    $count_carte = 0;
    foreach ($dataset as $data){
        $count_carte ++;
        $db = $dbHandler->openConnection();
        $sql_update = "UPDATE ad_carte_atm set etat_carte = 8 where id_carte = ".$data['id_carte']." ;";
        $result = $db->query($sql);
        if ($result->errCode != NO_ERR){
            $dbHandler->closeConnection(false);
            return new ErrorObj($result->errCode);
        }
        $dbHandler->closeConnection(true);
    }
    affiche(sprintf(_("OK (%s cartes ont été expiré)"),$count_carte), true);
    decLevel();
    affiche(_("Expiration des cartes terminé !"));
}


function getCarteSuspenduDesactivee($id_client){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ad_carte_atm where id_client = $id_client and etat_carte = 6";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dataset = array();
    while($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        $dataset[] = $row;
    }

    return $dataset;
}

function getAbonnementCarteAtm($id_client,$id_carte){
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ad_abonnement_atm where id_carte = $id_carte and id_client = $id_client";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);
    return $DATAS;
}
function insertAbonnementAtm($DATA){
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = buildInsertQuery("ad_abonnement_atm", $DATA);

    $result1 = executeQuery($db, $sql);
    if (DB::isError($result1)) {
        $dbHandler->closeConnection(false);
        return $result1->errCode;
    }

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}

function getAbonnementATM(){
    global $global_id_client, $global_id_agence, $dbHandler;

    $dbHandler->closeConnection(true);
    $db = $dbHandler->openConnection();

    $sql = "SELECT id_cpte, id_client FROM ad_abonnement_atm WHERE id_client = $global_id_client AND id_ag = $global_id_agence";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dataset = array();
    while($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        $dataset[$row['id_client']][] = $row['id_cpte'];
    }

    return $dataset;
}

function updateAbonnementATM($Fields, $Where){
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = buildUpdateQuery("ad_abonnement_atm", $Fields, $Where);

    // Exécution de la requête
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        return $result->errCode;
    }

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
}


function updateCarteATM($Fields, $Where){
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $sql = buildUpdateQuery("ad_carte_atm", $Fields, $Where);

    // Exécution de la requête
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        return $result->errCode;
    }

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
}

/**
 * Renvoie toutes les abonnements d'un client
 *
 * @return array Tableau associatif avec les abonnements trouvés.
 */
function getListAbonnementATM() {
    global $dbHandler, $global_id_agence, $global_id_client;

    $db = $dbHandler->openConnection();
//    $sql = sprintf("SELECT * FROM ad_abonnement WHERE id_ag=%d AND deleted='f' AND id_client=%d ORDER BY id_abonnement ASC ", $global_id_agence, $global_id_client);
    $sql = sprintf(
        " SELECT a.id_abonnement, a.id_cpte, a.id_client, a.statut, e.num_complet_cpte, e.intitule_compte, a.id_carte, a.num_carte_atm, c.motif_suspension FROM ad_abonnement_atm a INNER JOIN ad_cpt e on a.id_cpte = e.id_cpte INNER JOIN ad_carte_atm c ON c.id_carte = a.id_carte where a.id_client = $global_id_client ; "
    );

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $tmp_arr = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $tmp_arr[$row["id_cpte"]] = $row; //$abonnement;
    }
    return $tmp_arr;
}

/**
 * Cette fonction renvoie toutes les informations relatives à un client abonné au service ATM
 * 
 * @param $identifiant_client
 * @param $id_carte
 * @param $id_compte
 * @param $statut_abn
 * @param $etat_carte
 * @return null|array
 */
function getOneOrNullClientATMInfo($identifiant_client, $id_carte, $id_compte, $statut_abn = 2, $etat_carte = 5){
    global $dbHandler;

    $db = $dbHandler->openConnection();

    $sql = "SELECT a.id_client, e.compte_comptable, cpt.devise";
    $sql .= " FROM ad_abonnement_atm a";
    $sql .= " INNER JOIN ad_carte_atm c ON a.id_carte = c.id_carte AND a.id_ag = c.id_ag";
    $sql .= " INNER JOIN ad_ewallet e ON c.id_prestataire = e.id_prestataire AND c.id_ag = e.id_ag";
    $sql .= " INNER JOIN ad_cpt cpt ON c.id_cpte = cpt.id_cpte AND c.id_ag = cpt.id_ag";
    $sql .= " WHERE a.identifiant_client = '$identifiant_client'";
    $sql .= " AND a.id_carte = $id_carte";
    $sql .= " AND cpt.id_cpte = $id_compte";
    $sql .= " AND a.statut = $statut_abn";
    $sql .= " AND c.etat_carte = $etat_carte";

    $result = $db->query($sql);
  
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
  
    if ($result->numRows() == 0) {
      $dbHandler->closeConnection(true);
      return NULL;
    }
    $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
  
    $dbHandler->closeConnection(true);
  
    return $retour;
}

function getPrestataireATM($id_prestataire){
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ad_ewallet where id_prestataire = $id_prestataire";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);
    return $DATAS;
}

function xml_atm_carte_a_imprimer($criteres, $date_debut, $date_fin, $isCsv=false){

    $document = create_xml_doc("atm_carte_a_imprimer", "atm_carte_a_imprimer.dtd");
    $root = $document->root();

    //En-tête généraliste
    gen_header($root, 'ATM-CAI');
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $criteres);

    $info = $root->new_child("infos_synthetiques");

    $condition = " etat_carte = 1 AND date(date_demande) >= '$date_debut' AND date(date_demande) <= '$date_fin'";
    $DATA = get_data_ATM_all($condition);
    $carte_imprimer = $root->new_child("carte_imprimer", "");
    foreach ($DATA as $data_atm) {
            $detail_carte = $carte_imprimer->new_child("details", "");
            $no_client = $detail_carte->new_child("no_client", $data_atm['id_client']);
            $num_cpte = getAccountDatas($data_atm['id_cpte']);
            $niveau = $detail_carte->new_child("compte", $num_cpte['num_complet_cpte']);
            $data_client = getClientDatas($data_atm['id_client']);
            $nom = $data_client['pp_nom']." ".$data_client['pp_prenom'];
            $description = $detail_carte->new_child("nom", $nom);
            $budget_annuel = $detail_carte->new_child("date_demande", $data_atm['date_demande']);
        }


    return $document->dump_mem(true);
}


function get_data_ATM_all($condition){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = " SELECT * from ad_carte_atm";

    if (isset($condition) && !empty($condition)){
        $sql .= " WHERE ".$condition;
    }

    $result = $db->query($sql);

    if (DB::isError($result)) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
        $dbHandler->closeConnection(false);
    }

    $DATAS=array();
    while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
        $DATAS[$row['id_carte']] = $row;
    }

    $dbHandler->closeConnection(true);
    return $DATAS;
}

function xml_atm_carte_a_activer($criteres, $date_debut, $date_fin, $isCsv=false){

    $document = create_xml_doc("atm_carte_a_activer", "atm_carte_a_activer.dtd");
    $root = $document->root();

    //En-tête généraliste
    gen_header($root, 'ATM-CAA');
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $criteres);

    $info = $root->new_child("infos_synthetiques");

    $condition = " etat_carte = 3 AND date(date_livraison) >= '$date_debut' AND date(date_livraison) <= '$date_fin'";
    $DATA = get_data_ATM_all($condition);
    $carte_imprimer = $root->new_child("carte_activer", "");
    foreach ($DATA as $data_atm) {
        $detail_carte = $carte_imprimer->new_child("details", "");
        $no_client = $detail_carte->new_child("no_client", $data_atm['id_client']);
        $num_cpte = getAccountDatas($data_atm['id_cpte']);
        $niveau = $detail_carte->new_child("compte", $num_cpte['num_complet_cpte']);
        $data_client = getClientDatas($data_atm['id_client']);
        $nom = $data_client['pp_nom']." ".$data_client['pp_prenom'];
        $nom_client = $detail_carte->new_child("nom", $nom);
        $num_carte_atm = $detail_carte->new_child("num_carte", $data_atm['num_carte_atm']);
        $date_livraison = $detail_carte->new_child("date_livraison", $data_atm['date_livraison']);
        $date_expiration = $detail_carte->new_child("date_expiration", $data_atm['date_carte_expiration']);
    }


    return $document->dump_mem(true);
}

function xml_atm_liste_carte($criteres, $etat_carte= null, $numero_client= null, $motif_demande=null,$date_debut, $date_fin, $isCsv=false){
    global $adsys;

    $document = create_xml_doc("atm_liste_carte", "atm_liste_carte.dtd");
    $root = $document->root();

    //En-tête généraliste
    gen_header($root, 'ATM-LCA');
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $criteres);

    $info = $root->new_child("infos_synthetiques");

    $condition = "date(date_carte_expiration) >= '$date_debut' AND date(date_carte_expiration) <= '$date_fin'";

    if ($etat_carte != null){
        $condition .= " AND etat_carte = ".$etat_carte." ";
    }
    if ($numero_client != null){
        $condition .=" AND id_client = ".$numero_client." ";
    }
    if ($motif_demande != null){
        $condition .= " AND motif_demande = ".$motif_demande." ";
    }
    $DATA = get_data_ATM_all($condition);
    $carte_liste = $root->new_child("carte_liste", "");
    foreach ($DATA as $data_atm) {
        $detail_carte = $carte_liste->new_child("details", "");
        $no_client = $detail_carte->new_child("no_client", $data_atm['id_client']);
        $num_cpte = getAccountDatas($data_atm['id_cpte']);
        $niveau = $detail_carte->new_child("compte", $num_cpte['num_complet_cpte']);
        $data_client = getClientDatas($data_atm['id_client']);
        $nom = $data_client['pp_nom']." ".$data_client['pp_prenom'];
        $nom_client = $detail_carte->new_child("nom", $nom);
        $array_split = str_split($data_atm['num_carte_atm'],4);
        $num_atm = implode(" ",$array_split);
        $num_carte_atm = $detail_carte->new_child("num_carte", $num_atm);
        $convert_etat_int = (int)$data_atm['etat_carte'];
        $libel_etat = adb_gettext($adsys['etat_carte_atm'][$convert_etat_int]);
        $etat_carte_x = $detail_carte->new_child("etat_carte", $libel_etat);
        $date_expiration = $detail_carte->new_child("date_expiration", $data_atm['date_carte_expiration']);
    }

    return $document->dump_mem(true);
}

function xml_atm_transaction($criteres, $num_carte= null, $numero_client= null,$date_debut, $date_fin, $isCsv=false){
    global $adsys;

    $document = create_xml_doc("atm_transaction", "atm_transaction.dtd");
    $root = $document->root();

    //En-tête généraliste
    gen_header($root, 'ATM-TRA');
    $header_contextuel = $root->new_child("header_contextuel", "");
    gen_criteres_recherche($header_contextuel, $criteres);

    $info = $root->new_child("infos_synthetiques");

    $condition = "date(date_comptable) >= '$date_debut' AND date(date_comptable) <= '$date_fin'";

    if ($num_carte != null){
        $condition .= " AND a.num_carte_atm = '".$num_carte."' ";
    }
    if ($numero_client != null){
        $condition .=" AND a.id_client = ".$numero_client." ";
    }

    $DATA = get_transaction_ATM($condition);
    $transaction = $root->new_child("transaction", "");
    foreach ($DATA as $data_atm) {
        $detail_trans = $transaction->new_child("details", "");
        $no_client = $detail_trans->new_child("id_his", $data_atm['id_his']);
        $date_compta = $detail_trans->new_child("date_transaction", $data_atm['date_comptable']);
        $id_client = $detail_trans->new_child("no_client", $data_atm['id_client']);
        $num_cpte = getAccountDatas($data_atm['cpte_interne_cli']);
        $compte = $detail_trans->new_child("compte", $num_cpte['num_complet_cpte']);
        $array_split = str_split($data_atm['num_carte_atm'],4);
        $num_atm = implode(" ",$array_split);
        $num_carte_atm = $detail_trans->new_child("num_carte", $num_atm);
        $libelOperation= getLibelOperationTransaction($data_atm['type_operation']);
        $type_ope = $detail_trans->new_child("type_operation", $libelOperation);
        $montant = $detail_trans->new_child("montant", afficheMontant($data_atm['montant']));
    }

    return $document->dump_mem(true);
}

function get_transaction_ATM($condition){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = " SELECT h.id_his, e.date_comptable, h.id_client, m.cpte_interne_cli,a.num_carte_atm ,e.type_operation, m.montant
FROM ad_his h
INNER JOIN ad_ecriture e ON h.id_his = e.id_his
INNER JOIN ad_mouvement m ON e.id_ecriture = m.id_ecriture 
INNER JOIN ad_carte_atm a ON a.id_client = h.id_client
WHERE e.type_operation IN (190,191) and m.sens = 'd' ";

    if (isset($condition) && !empty($condition)){
        $sql .= " AND ".$condition;
    }

    $result = $db->query($sql);

    if (DB::isError($result)) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
        $dbHandler->closeConnection(false);
    }

    $DATAS=array();
    while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
        $DATAS[$row['id_his']] = $row;
    }

    $dbHandler->closeConnection(true);
    return $DATAS;
}
?>