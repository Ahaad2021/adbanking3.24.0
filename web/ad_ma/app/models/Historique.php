<?php

/**
 * Description de la classe Historique
 *
 * @author danilo
 */

require_once 'ad_ma/app/models/BaseModel.php';
require_once 'ad_ma/app/models/MouvementPublisher.php';

class Historique extends BaseModel {

    public function __construct(&$dbc, $id_agence=NULL) {        
        parent::__construct($dbc, $id_agence); 
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * 
     * PRECONDITION :
     * Prend en argument un tableau d'écritures ocmptables pour l'historique et renvoie le dernier n° d'opération (débit/crédit)
     * 
     */
    public function getLastIdOperation($array_comptable) {

        if (!is_array($array_comptable))
            return 1;

        reset($array_comptable);
        $id_max = 0;
        while (list(, $tmp) = each($array_comptable)) {
            if ($id_max < $tmp["id"])
                $id_max = $tmp['id'];
        }
        return $id_max;
    }

    /**
     * Cette fonction remplit les champs nécessaires à l'écriture dans la table de l'historique des transferts avec l'extérieur.
     * 
     * @param array $data : toutes les informations utiles à la mise à jour de la table attentes_credit
     * 
     * @return array : un tableau avec tous les champs nécessaires à l'écriture dans la table ad_his_ext
     */
    public function creationHistoriqueExterieur($data) {
        $data_ext = array();

        $data_ext['type_piece'] = $data['type_piece'];
        $data_ext['remarque'] = $data['remarque'];
        $data_ext['sens'] = $data['sens'];
        $data_ext['num_piece'] = $data['num_piece'];
        $data_ext['date_piece'] = $data['date_piece'];
        $data_ext['communication'] = $data['communication'];
        $data_ext['id_pers_ext'] = $data['id_pers_ext'];
        switch ($data['sens']) {
            case 'in ' :
                $data_ext['id_tireur_benef'] = $data['id_ext_ordre'];
                break;
            case 'out' :
                $data_ext['id_tireur_benef'] = $data['id_ext_benef'];
                break;
            case '---' :
                $data_ext['id_tireur_benef'] = NULL;
                break;
            default :
                signalErreur(__LINE__, __FILE__, __FUNCTION__);
        }
        if ($data_ext['type_piece'] == 5)
            unset($data_ext['id_tireur_benef']); //dans le cas d'un Travelers cheque, pas de tireur/benef

        return $data_ext;
    }

    /**
     * Création de l'historique des transferts avec l'extérieur
     * 
     * @param type $data_ext
     * 
     * @return int $id_his_ext
     */
    public function insertHistoriqueExterieur($data_ext) {

        //On commence par récupérer le numéro de lot
        $sql = "SELECT nextval('ad_his_ext_seq')";

        $id_his_ext = $this->getDbConn()->prepareFetchColumn($sql);

        if ($id_his_ext===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $data_ext["id"] = $id_his_ext;
        $data_ext["id_ag"] = $this->getIdAgence();

        $sql = buildInsertQuery("ad_his_ext", $data_ext);

        $result = $this->getDbConn()->execute($sql);
        
        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $id_his_ext;
    }

    /**
     * Fabrique un numéro d'écriture comptable
     * Bloque la lign,e concernée de ad_journal pour éviter des conditions de course
     * @author Thomas Fastenakel
     * @param int $id_jou ID du journal
     * @param int $id_exo ID de l'exercice dans lequel l'écriture est passée
     * @return text Numéro d'écriture
     */
    public function makeNumEcriture($id_jou, $id_exo) {

        // On prend tous les comptes à soldes négatifs sauf les comptes de crédit
        $sql = "SELECT last_ref_ecriture FROM ad_journaux WHERE id_ag=".$this->getIdAgence()." AND id_jou = $id_jou FOR UPDATE";

        $num_ecr = $this->getDbConn()->prepareFetchColumn($sql);

        if ($num_ecr===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $num_ecr++;

        // Init class
        $ComptaObj = new Compta($this->getDbConn(), $this->getIdAgence());

        $JOU = $ComptaObj->getInfosJournal($id_jou);

        // Destroy object
        unset($ComptaObj);

        $code_jou = $JOU[$id_jou]["code_jou"];

        $ref_ecriture = $code_jou . "-" . sprintf("%08d", $num_ecr) . "-" . sprintf("%02d", $id_exo);

        $sql = "UPDATE ad_journaux SET last_ref_ecriture = $num_ecr WHERE id_ag=".$this->getIdAgence()." AND id_jou = $id_jou";

        $result = $this->getDbConn()->execute($sql);

        return $ref_ecriture;
    }

    /**
     * 
     * Cette f° se charge d'enregister dans l'historique aussi bien la f° (ad_his) que les opérations comptables associées (ad_ecriture qui contient les informations sur les ecritures et ad_mouvement qui donne les mouvement sur les comptes et ad_cpt_comptable)
     * 
     * Paramètres entrants :
     * - type d'opération (cf. n° table système)
     * - infos supplémentaires (cfr documentation/historique.txt)
     * - date
     * - login de l'utilisateur
     * 
     * - SI c'est une opération comptable, un tableau a 9 colonnes :
     * - 'id' : identifie l'écriture dans un lot d'écriture
     * - 'compte' : compte à mouvementer
     * - 'cpte_interne_cli' : si le mouvement concerne un compte client, on passe l'id (cf ad_cpt). Ce champ peut être NULL
     * - 'sens' : sens du mouvement ('c' ou 'd')
     * - 'montant' : montant du mouvement
     * - 'date_comptable' : date de valeur  du mouvement
     * - 'libel' : libellé de l'écriture
     * - 'jou' : identifiant du journal associé à l'opération. Ce champ peut être NULL
     * - 'exo' : identifiant de l'exercice comptable associé à la date de valeur
     * - 'devise' : Code de la devise du mouvement
     * 
     * FIXME
     * Cette procédure vérifie si la somme des montants débités est équivalante à la somme des montants crédités après quoi elle renseigne les tables concernées.
     * OUT bjet Error
     * Si OK, Renvoie l'id dans la table historique comme paramètre
     * 
     */
    public function ajoutHistorique($type_fonction, $id_client, $infos, $login, $date, $array_comptable = NULL, $data_ext = NULL, $idhis = NULL) {

        global $global_monnaie_courante_prec, $debug;

        // Init class
        $AgenceObj = new Agence($this->getDbConn());

        $id_agence_encours = $AgenceObj->getNumAgc();

        // Destroy object
        unset($AgenceObj);

        // S'il y a des données à insérer dans la table historique des transferts avec l'extérieur, on commence par cette insertion.
        if ($data_ext == NULL) {
            $id_his_ext = 'NULL';
        } else {
            $id_his_ext = self::insertHistoriqueExterieur($data_ext);
            if ($id_his_ext == NULL) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }
        }

        $infos = string_make_pgcompatible($infos);
        // Pour ne pas avoir une erreur de PSQL si pas de client associé.
        if ($id_client == '' || $id_client == NULL) {
            $id_client = 'NULL';
        }
        if ($idhis == NULL) {
            // On commence par récupérer le numéro de lot
            $sql = "SELECT nextval('ad_his_id_his_seq')";

            $idhis = $this->getDbConn()->prepareFetchColumn($sql);

            if ($idhis===FALSE) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }

            // On insère dans la table historique
            $sql = "INSERT INTO ad_his(id_his, id_ag, type_fonction, infos, id_client, login, date, id_his_ext) ";
            $sql .= "VALUES($idhis, $id_agence_encours, $type_fonction, '$infos', $id_client, '$login', '$date', $id_his_ext)";

            $result = $this->getDbConn()->execute($sql);

            if($result===FALSE) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }
        }

        // Si c'est une opération comptable
        if ($array_comptable != NULL) {
            // On vérifie si somme débit == somme crédit et on inscrit dans la base de données
            $equilibre = 0;

            reset($array_comptable);

            // Pour factoriser les lignes par id dans array_comptable pour faire un entête/détail (ad_ecriture/ad_mouvement)
            $tab_id = array();
            $tab_fact = array();
            foreach ($array_comptable as $key => $value) {
                // Verifier que l'operation a bien un libellé
                if (!isset($value['libel'])) {
                    echo "<p><font color=\"red\">" . sprintf(_("Erreur : l'écriture n'a pas de libellé pour la transaction %s, compte %s !"), $idhis, $value['compte']) . "</font></p>";
                    return;
                }

                // Pour chaque débit crédit
                if ($value['sens'] == SENS_CREDIT) {
                    $equilibre += $value['montant'];
                } elseif ($value['sens'] == SENS_DEBIT) {
                    $equilibre -= $value['montant'];
                }

                // Recherche de tous les id différents
                if (in_array($value['id'], $tab_id) == false) {
                    $temp = array();
                    array_push($tab_id, $value['id']);
                    $temp = array("libel" => $value["libel"], "type_operation" => $value["type_operation"], "date_comptable" => $value["date_comptable"], "id_jou" => $value["jou"], "id_exo" => $value["exo"], "info_ecriture" => $value['info_ecriture']);
                    $tab_fact[$value['id']] = $temp;
                }
            }
            if (round($equilibre, $global_monnaie_courante_prec) != 0) {
                //Si la somme débit != somme crédit
                // FIXME : renvoyer un objet Error à la place du signalErreur
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }
        }

        if ($tab_id != NULL) {
            foreach ($tab_id as $key => $value) { // Pour chaque écriture
                // Insertion dans ad_ecriture les infos factorisées
                // Construction de la requête d'insertion
                $DATA = array();
                $DATA["id_his"] = $idhis;
                $DATA["date_comptable"] = $tab_fact[$value]["date_comptable"];
                $DATA["libel_ecriture"] = $tab_fact[$value]["libel"];
                $DATA["type_operation"] = $tab_fact[$value]["type_operation"];
                $DATA["id_jou"] = $tab_fact[$value]["id_jou"];
                $DATA["id_ag"] = $this->getIdAgence();
                $DATA["id_exo"] = $tab_fact[$value]["id_exo"];
                $DATA["ref_ecriture"] = self::makeNumEcriture($DATA["id_jou"], $DATA["id_exo"]);
                $DATA["info_ecriture"] = $tab_fact[$value]["info_ecriture"];

                $sql = buildInsertQuery("ad_ecriture", $DATA);

                $result = $this->getDbConn()->execute($sql);

                if($result===FALSE) {
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__);
                }

                // Récupérer le numéro d'ecriture
                $sql = "SELECT max(id_ecriture) from ad_ecriture where id_ag=".$this->getIdAgence().";";

                $idecri = $this->getDbConn()->prepareFetchColumn($sql);

                if ($idecri===FALSE) {
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__);
                }
                
                // Init class
                $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());

                // Insertion dans ad_mouvement les mouvements sur les comptes
                foreach ($array_comptable as $key1 => &$value1) { // Pour chaque mouvement
                    if ($value1['id'] == $value) { //mise à jour des soldes comptables
                        //FIXME : il faut obliger à passer par les sous-comptes (ex : erreur de paramétrage)
                        //FIXME : le montant passé doit avoir été correctement récupéré au préalable par un recupMontant approprié
                        $MyError = $CompteObj->setSoldeComptable($value1['compte'], $value1['sens'], $value1['montant'], $value1["devise"]);
                        if ($MyError->errCode != NO_ERR) {
                            return $MyError;
                        }

                        // Mise à jour compte client interne
                        if ($value1['cpte_interne_cli'] != '') {

                            $MyError = $CompteObj->setSoldeCpteCli($value1['cpte_interne_cli'], $value1['sens'], $value1['montant'], $value1['devise']);

                            if ($MyError->errCode != NO_ERR) {
                                return $MyError;
                            }

                            $cpte_interne_cli = $value1['cpte_interne_cli'];

                            // Recuperer solde pour le message queue
                            $value1['solde_msq'] = $CompteObj->getSoldeCpteCli($value1['cpte_interne_cli']);
                        }

                        // Fix montant si NULL ou vide
                        $ad_mouvement_montant = recupMontant($value1["montant"]);
                        if($ad_mouvement_montant==NULL || $ad_mouvement_montant=='') {
                            $ad_mouvement_montant = 0;
                        }else { // #514: arrondir le montant              
                          $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']);            
                        } 

                        // Insertion dans ad_mouvements
                        $DATA = array();
                        $DATA["id_ecriture"] = $idecri;
                        $DATA["compte"] = $value1["compte"];
                        $DATA["cpte_interne_cli"] = $value1["cpte_interne_cli"];
                        $DATA["sens"] = $value1["sens"];
                        $DATA["montant"] = $ad_mouvement_montant;
                        $DATA["date_valeur"] = $value1["date_valeur"];
                        $DATA["devise"] = $value1["devise"];
                        $DATA["consolide"] = $value1["consolide"];
                        $DATA["id_ag"] = $this->getIdAgence();

                        $sql = buildInsertQuery("ad_mouvement", $DATA);

                        $result = $this->getDbConn()->execute($sql);

                        if($result===FALSE) {
                            $this->getDbConn()->rollBack(); // Roll back
                            signalErreur(__FILE__, __LINE__, __FUNCTION__);
                        }
                    }
                }
                
                // Destroy object
                unset($CompteObj);
            }
        }
        
        // Récupérer le premier numéro d'ecriture depuis d'ID historique
        if(!empty($array_comptable)) {
            $sql = "SELECT id_ecriture FROM ad_ecriture WHERE id_ag=" . $this->getIdAgence() . " AND id_his=" . $idhis . " ORDER BY id_ecriture ASC LIMIT 1;";

            $id_ecriture = $this->getDbConn()->prepareFetchColumn($sql);


            if ($id_ecriture === FALSE) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }
        }

        $MouvementPublisherObj = new MouvementPublisher($this->getDbConn(), $this->getIdAgence());
        if ($MouvementPublisherObj->isMSQEnabled()){
            $MouvementPublisherObj->envoiSmsMouvement($array_comptable);
        }
        unset($MouvementPublisherObj);

        return new ErrorObj(NO_ERR, array('id_his' => $idhis, 'id_ecriture' => $id_ecriture));
    }

    /**
     * Renseigne le numéro du reçu généré par ADbanking dans la table ad_his_ext
     * 
     * @param int $id_his ID de la transaction (dans ad_his)
     * @param text $ref_recu Numéro de reçu généré
     * 
     * @return ErrorObj Objet Erreur
     */
    public function confirmeGenerationRecu($id_his, $ref_recu) {

        // Recherche s'il existe déjà uen entrée correspondante à id_his dans ad_his_ext
        $sql = "SELECT * FROM ad_his a, ad_his_ext b WHERE a.id_ag=b.id_ag AND a.id_ag=".$this->getIdAgence()." AND a.id_his = $id_his AND a.id_his_ext = b.id";

        $result = $this->getDbConn()->prepareFetchAll($sql);

        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if (count($result) == 0) { // Il faut créer une nouvelle entrée
            //On commence par récupérer le numéro de lot
            $sql = "SELECT nextval('ad_his_ext_seq')";

            $id_his_ext = $this->getDbConn()->prepareFetchColumn($sql);

            if ($id_his_ext===FALSE) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__); // "DB: ".$result->getMessage()
            }

            $DATA = array();
            $DATA["id"] = $id_his_ext;
            $DATA["type_piece"] = 8; // Reçu ADbanking
            $DATA["num_piece"] = $ref_recu;
            $DATA["id_ag"] = $this->getIdAgence();

            $sql = buildInsertQuery("ad_his_ext", $DATA);

            $result = $this->getDbConn()->execute($sql);

            if($result===FALSE) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }

            // Mettre à jour le lien dans ad_his
            $UPDATE = array("id_his_ext" => $id_his_ext);
            $sql = buildUpdateQuery("ad_his", $UPDATE, array("id_his" => $id_his, 'id_ag' => $this->getIdAgence()));

            $result = $this->getDbConn()->execute($sql);

            if($result===FALSE) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }
        } else if (count($result) == 1) { // Une entrée existe déjà
            $INFOSEXT = $result[0]; // $result->fetchrow(DB_FETCHMODE_ASSOC);
            $UPDATE = array();
            $UPDATE["type_piece"] = 8;
            if ($INFOSEXT["num_piece"] != '')
                $UPDATE["num_piece"] = $INFOSEXT["num_piece"] . "/" . $ref_recu;
            else
                $UPDATE["num_piece"] = $ref_recu;
            $sql = buildUpdateQuery("ad_his_ext", $UPDATE, array("id" => $INFOSEXT["id"], 'id_ag' => $this->getIdAgence()));

            $result = $this->getDbConn()->execute($sql);

            if($result===FALSE) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }
        } else { // Impossible
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__); // Incohérence dans ad_his
        }

        return new ErrorObj(NO_ERR);
    }

}
