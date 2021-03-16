<?php

/**
 * Description of classe Divers
 *
 * @author BD0513
 */
require_once 'ad_ma/app/models/BaseModel.php';

class Divers extends BaseModel {

    public function __construct(&$dbc, $id_agence = NULL) {
        parent::__construct($dbc, $id_agence);
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Etablit la devise avec laquelle on travaille
     * Met ?à jour les variables $global_remote_monnaie_courante et $global_monnaie_courante_prec
     * 
     * @param $devise char(3) Code ISO de la devise
     * 
     * @return void
     */
    public static function setMonnaieCourante(&$dbc, $id_agence, $devise) {
        global $global_remote_monnaie_courante;
        global $global_monnaie_courante_prec;

        if ($devise == NULL) { // Utile pour des écrans dans lesquels la devise n'est pas fixe
            $global_remote_monnaie_courante = NULL;
            $global_monnaie_courante_prec = 0;
        } else {
            // Init class
            $DeviseObj = new Devise($dbc, $id_agence);

            $DEV = $DeviseObj->getInfoDevise($devise);

            // Destroy object
            unset($DeviseObj);

            $global_remote_monnaie_courante = $devise;
            $global_monnaie_courante_prec = $DEV["precision"];
        }
    }

    /**
     * Récupère la liste des pays
     * 
     * @return Liste de pays
     */
    public static function getListPays(&$dbc, $id_agence) {

        $sql = "SELECT * FROM adsys_pays WHERE id_ag = :id_agence ORDER BY libel_pays ASC";

        $param_arr = array(':id_agence' => $id_agence);

        $result = $dbc->prepareFetchAll($sql, $param_arr);

        if ($result === FALSE || count($result) < 0) {
            $dbc->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $tmp_arr = array();
        foreach ($result as $prod) {
            $tmp_arr[$prod["id_pays"]] = $prod;
        }

        return $tmp_arr;
    }

    /**
     * Récupère le libelle d'un pays
     *
     * @return libelle d'un pays
     */
    public static function getLibellePays(&$dbc, $id_agence, $id) {

        $sql = "SELECT libel_pays FROM adsys_pays WHERE id_ag = :id_agence and id_pays = :id_pays";

        $param_arr = array(':id_agence' => $id_agence, ':id_pays' => $id);

        $result = $dbc->prepareFetchColumn($sql, $param_arr);

        if ($result === FALSE || count($result) < 0) {
            $dbc->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Récupère le libelle d'une banque
     *
     * @return libelle banque
     */
    public static function getLibelleBanque(&$dbc, $id_agence, $id) {

        $sql = "SELECT nom_banque FROM adsys_banque WHERE id_ag = :id_agence and id_banque = :id_banque";

        $param_arr = array(':id_agence' => $id_agence, ':id_banque' => $id);

        $result = $dbc->prepareFetchColumn($sql, $param_arr);

        if ($result === FALSE || count($result) < 0) {
            $dbc->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Récupère la liste des devises
     * 
     * @return Liste de devises
     */
    public static function getListDevises(&$dbc, $id_agence) {

        $sql = "SELECT * FROM devise WHERE id_ag = :id_agence ORDER BY code_devise ASC";

        $param_arr = array(':id_agence' => $id_agence);

        $result = $dbc->prepareFetchAll($sql, $param_arr);

        if ($result === FALSE || count($result) < 0) {
            $dbc->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $tmp_arr = array();
        foreach ($result as $prod) {
            $tmp_arr[$prod["code_devise"]] = $prod;
        }

        return $tmp_arr;
    }

    /**
     * Récupère la liste des types pièce identité
     * 
     * @return Liste des types pièce identité
     */
    public static function getListTypePieceIdentite(&$dbc, $id_agence, $langue) {

        $sql = "SELECT tpi.*, t.traduction FROM adsys_type_piece_identite tpi, ad_traductions t WHERE tpi.libel=t.id_str AND tpi.id_ag = :id_agence AND langue = :langue ORDER BY tpi.libel ASC";

        $param_arr = array(':id_agence' => $id_agence, ':langue' => $langue);

        $result = $dbc->prepareFetchAll($sql, $param_arr);

        if ($result === FALSE || count($result) < 0) {
            $dbc->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $tmp_arr = array();
        foreach ($result as $prod) {
            $tmp_arr[$prod["id"]] = $prod;
        }

        return $tmp_arr;
    }

    /**
     * Fonction qui arrondit un montant selon la plus petite pièce existante.
     *
     * Le sens de l'arrondi dépend de $sens, la précision est prise comme le billet de plus petite valeur.
     * Ex: $mnt = 14 FCFA et $sens = -1 donne 10 si la plus petite pièce est 5 FCFA
     * 
     * @param float $mnt Montant à arrondir
     * @param int $sens : = 0 : Arrondi à l'unité la plus proche
     *                    < 0 : Arrondi à l'unité inférieure
     *                    > 0 : Arrondi à l'unité supérieure
     * 
     * @return float Montant arrondi
     */
    public static function arrondiMonnaie(&$dbc, $id_agence, $mnt, $sens, $devise = NULL) {

        global $global_remote_monnaie;

        if ($devise == NULL) {
            $devise = $global_remote_monnaie;
        }

        $sql = "SELECT MIN(valeur) FROM adsys_types_billets WHERE id_ag = $id_agence and devise = '$devise'";

        $min = $dbc->prepareFetchColumn($sql);

        if ($min === FALSE) {
            $dbc->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if ($min == 0) {
            echo "<BR><B><FONT COLOR=red> *** " . sprintf(_("Le billetage pour la devise %s n'a pas été renseigné"), $devise) . "<BR> *** " . _("On suppose la plus petite unité monétaire à 1") . "</FONT></B><BR>";
            $min = 1;
        }

        // Init class
        $DeviseObj = new Devise($dbc, $id_agence);

        $DEV = $DeviseObj->getInfoDevise($devise); // recuperation d'info sur la devise'
        // Destroy object
        unset($DeviseObj);

        $precision_devise = pow(10, $DEV["precision"]);
        $reste = fmod($mnt * $precision_devise, $min * $precision_devise) / $precision_devise;
        if ($reste == 0)
            return $mnt;

        if ($sens == 0)
            $sens = ((2 * $reste > $min) ? 1 : -1);

        if ($sens < 0)
            $arrondi = $mnt - ($reste);
        else if ($sens > 0)
            $arrondi = $mnt + $min - ($reste);

        return $arrondi;
    }

    /**
     * Formatte le montant avec le séparateur de milliers et la décimale propre à la devise courante
     *
     * @param str $montant Le montant à formater
     * @param bool $devise Si vrai alors ajoute le libellé de la devise à la chaine
     * 
     * @return str Une chaine contenant le montant formaté
     */
    public static function afficheMontant($montant, $devise = false, $typ_raport = false, $precision = NULL) 
    {
        global $global_remote_monnaie_courante;
        global $global_monnaie_courante_prec;
        global $mnt_sep_mil;
        global $mnt_sep_mil_csv;
        global $mnt_sep_dec;
        global $mnt_sep_dec_csv;        
        
        if (empty($montant))
            return "";

        // Conversion en string au cas où on passe un entier
        $montant = strval($montant);
        
        if(empty($precision)) $precision = $global_monnaie_courante_prec;   
        
        if(!ctype_alpha($devise)) {
             $devise = $global_remote_monnaie_courante;
        }       
        
        if($typ_raport) {
            $montant = number_format(doubleval($montant), $precision, $mnt_sep_dec_csv, $mnt_sep_mil_csv);
        }
        else {
            $montant = number_format(doubleval($montant), $precision, $mnt_sep_dec, $mnt_sep_mil);
        }
        
        if(!empty($devise)) $montant .= " " . $devise;       
        
        // Attention, le second " " est un blanc insécable !  Il est encodé en UTF-8 &#160;
        $montant = mb_ereg_replace(" ", " ", $montant);
              
        return $montant;
    }

    /**
     * Renvoie l'ID ecriture
     * 
     */
    public static function getIDEcritureByIDHis($id_his) {
        global $db;
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT id_ecriture from ad_ecriture where id_ag=" . $global_id_agence . " and id_his=" . $id_his . " order by id_ecriture asc limit 1;";

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(true);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $row = $result->fetchrow();

        $dbHandler->closeConnection(true);

        return $row[0];
    }

    /**
     * Renvoie le libellé de la transaction
     */
    public static function getTypeTransactionChoixLibel($type_transaction, $type_choix) {

        $transaction_libel = "";

        if ($type_transaction == "retrait") {
            switch ($type_choix) {
                case 1:
                    $transaction_libel = "Retrait Cash avec impression reçu";
                    break;
                case 4:
                    $transaction_libel = "Retrait Cash sur présentation dune autorisation de retrait sans livret/chèque";
                    break;
                case 15:
                    $transaction_libel = "Retrait Cash sur présentation dun chèque guichet";
                    break;
            }
        } elseif ($type_transaction == "depot") {
            switch ($type_choix) {
                case 1:
                    $transaction_libel = "Dépôt en espèce";
                    break;
                case 2:
                    $transaction_libel = "Dépôt par chèque";
                    break;
                case 3:
                    $transaction_libel = "Dépôt par ordre de paiement";
                    break;
            }
        }

        return $transaction_libel;
    }

    /**
     * Créer et renvoie un libellé local
     */
    public static function getLocalTextId($text, $langue = '') {

        require_once('lib/dbProcedures/multilingue.php');
        require_once('lib/multilingue/traductions.php');

        if (trim($langue) == '') {
            $langue = get_langue_systeme_par_defaut();
        }

        $libel_trad = new Trad();
        $libel_trad->set_traduction($langue, $text);
        $libel_trad->save();

        return $libel_trad->get_id_str();
    }

    /**
     * Créer et renvoie un libellé remote
     */
    public static function getRemoteTextId(&$dbc, $text) {

        $traduction = string_make_pgcompatible($text);

        $sql = "SELECT makeTraductionLangSyst('" . $traduction . "');";        

        $id_str = $dbc->prepareFetchColumn($sql);

        if ($id_str === FALSE) {
            $dbc->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $id_str;
    }   
  
    /**
     * 
     * Get remote text translation from text id
     * 
     * @param pdo $dbc
     * @param int $id_str
     * @return string
     */
    public static function getRemoteTradFromId(&$dbc, $id_str)
    {
        $langue = get_langue_systeme_par_defaut();
               
        $sql = "SELECT traduction FROM ad_traductions WHERE id_str=:id_str AND langue=:langue ;";
        $params = array(':id_str' => $id_str, ':langue' => $langue);
        
        $str = $dbc->prepareFetchColumn($sql, $params);
        
        if(empty($str)) {
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        return $str;
    }
    
    /**
     *
     * Get local text translation from text id
     *    
     * @param int $id_str
     * @return string
     */
    public static function getLocalTradFromId($id_str)
    {    
        global $dbHandler;
        
        $db = $dbHandler->openConnection();        
        $langue = get_langue_systeme_par_defaut();
         
        $sql = "SELECT traduction FROM ad_traductions WHERE id_str=$id_str AND langue='$langue' ;";       
        $result = $db->query($sql);
        
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(true);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }        
        $row = $result->fetchrow();        
        $dbHandler->closeConnection(true);
        return $row[0];       
    }
    
    /**
     * Renvoie le nom de l'operation en deplacé
     * 
     * @param string $fonction
     * @return string
     */
    public static function getLibelleFonctionDeplace($fonction)
    {
        global $adsys;
        
        $fonction = trim($fonction);
        
        if ($fonction == "depot") {
            $type_fonction = 93;
        }
        elseif($fonction == "retrait") {
            $type_fonction = 92;
        }
        else {
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        return adb_gettext($adsys["adsys_fonction_systeme"][$type_fonction]);
    }
    
    /**
     * Vérification des taux de change des devises lors d'un opération en déplacé multidevises.
     *  - Le taux doit être identique dans les 2 agences
     *  
     * @param string $devise1
     * @param string $devise2
     * @param object $dbc
     * @throws Exception
     * @return ErrorObj
     */
    public static function checkTauxDeviseForOperationDeplacer($devise1, $devise2, $dbc)
    {               
        global $global_remote_id_agence;
        
        if(empty($global_remote_id_agence)) {
            return new ErrorObj(ERR_TAUX_CHANGE_DIFF_MULTIAGENCE, _("Id agence de l'agence distante n'est pas défini"));
        }
        
        $deviseObj = new Devise($dbc, $global_remote_id_agence);        
        $tauxDeChangeRemote = $deviseObj->getTauxChange($devise1, $devise2, true, 1);      
        $tauxDeChangeLocale = getTauxChange($devise1, $devise2, true, 1);
        
        if(!empty($tauxDeChangeRemote) && !empty($tauxDeChangeLocale)) {
            $tauxDeChangeRemote = floatval($tauxDeChangeRemote);
            $tauxDeChangeLocale = floatval($tauxDeChangeLocale);
            
            if($tauxDeChangeRemote == $tauxDeChangeLocale) {
                return new ErrorObj(NO_ERR);
            }
            else{                
                return new ErrorObj(ERR_TAUX_CHANGE_DIFF_MULTIAGENCE, _("Taux de change différents dans les deux agences"));
            }
        }
        else {
            return new ErrorObj(ERR_TAUX_CHANGE_DIFF_MULTIAGENCE, _("Erreur : taux non défini"));
        }
    }

    /**
     * Fonction de vérification si le flag de prélèvement de commissions sont identiques dans les deux bases
     * lors d'un opération en déplacé en multidevises.
     *
     * @param object $dbc            
     * @throws Exception
     * @return boolean
     */
    public static function checkFlagPrelevementCommissionsForOperationDeplacer()
    {
        global $global_remote_id_agence;
        $isAllowed = false;
        
        if (! empty($global_remote_id_agence)) {
            
            $dbc = AgenceRemote::getRemoteAgenceConnection($global_remote_id_agence);
            
            $agenceObj = new Agence($dbc, $global_remote_id_agence);
            $statusRemote = $agenceObj->getWherePerceptionCommissionsMultiAgence();
            $statusLocal = getWherePerceptionCommissionsMultiAgence();          
            
            if ($statusRemote === $statusLocal) {
                $isAllowed = true;
            }
        }      
       
        return $isAllowed;
    }
    
    public static function getReferencedFields (&$dbc, $RefField, $RefValue=NULL) {
        global $adsys, $global_langue_utilisateur;
        
        $sql = "SELECT nchmpc, tablen FROM d_tableliste WHERE ident=:ref_field;";
        
        $param_arr = array(':ref_field' => $RefField);

        $temprow = $dbc->prepareFetchRow($sql, $param_arr, PDO::FETCH_NUM);

        if ($temprow === FALSE || count($temprow) == 0) {
            return NULL;
        }

        // REFIDENT is field name of the referenced field.
        $REFIDENT = $temprow[0];
        $REFTABLEID = $temprow[1];
        
        $sql = "SELECT nomc, is_table FROM tableliste WHERE ident=:ref_table_id;";
        
        $param_arr = array(':ref_table_id' => $REFTABLEID);

        $temprow = $dbc->prepareFetchRow($sql, $param_arr, PDO::FETCH_NUM);

        if ($temprow === FALSE || count($temprow) == 0) {
            return NULL;
        }

        $REFTABLE = $temprow[0];
        $is_table = $temprow[1];

        if ($is_table == 't') {
            // Get all the OnSelect fields of this table
            $sql = "SELECT nchmpc, traduit FROM d_tableliste WHERE onslct=true AND tablen=:ref_table_id ORDER BY ident";

            $param_arr = array(':ref_table_id' => $REFTABLEID);

            $results = $dbc->prepareFetchAll($sql, $param_arr, PDO::FETCH_NUM);

            if ($results === FALSE || count($results) < 0) {
                $dbc->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }

            $REFNAME = "";

            foreach ($results as $key=>$value) {
                if ($value[1] == TRUE) {
                    $REFNAME .= "traduction($value[0], '$global_langue_utilisateur'), ";
                } else {
                    $REFNAME .= $value[0].", ";
                }
            }

            $REFNAME = substr($REFNAME, 0, strlen($REFNAME)-2);
            
            // REFNAME is a list of OnSelcted fields for the referenced table
            
            /* Select all the $key=>$value pair for the HTML SELECT. */
            $sql = "SELECT $REFIDENT, $REFNAME FROM $REFTABLE";

            /*
            * La table en paramètre est-elle une table consolidé?
            */
            $a_sql = "SELECT count(c.relname) from pg_class c,pg_attribute a where a.attrelid=c.oid AND c.relkind='r' AND c.relname !~ '^pg_' AND relname !~ '^sql' AND a.attname='id_ag' AND c.relname = '$REFTABLE'";
            
            $consolide = $dbc->prepareFetchColumn($a_sql);

            if ($consolide) {
                global $global_remote_id_agence;
                $sql .= " WHERE id_ag = $global_remote_id_agence";
            }
            if (isset($RefValue)) {
                if ($consolide) {
                    $sql .= " AND";
                } else {
                    $sql .= " WHERE";
                }
                $sql .= " $REFIDENT = '$RefValue'";
            }
            $sql .= " ORDER BY $REFIDENT";

            $results = $dbc->prepareFetchAll($sql, "", PDO::FETCH_NUM);

            if ($results === FALSE || count($results) < 0) {
                $dbc->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }

            $temparray = array();
            foreach($results as $key=>$tmprow) {
                $Display = "";
                //$id = $tmprow[0];
                next($tmprow);
                while (list($key, $value) = each ($tmprow)) {
                    $Display .= $value." ";
                }
                $temparray[$tmprow[0]] = $Display;
            }
        } else {
            $temparray = array();
            if (isset($RefValue)) {
                $temparray[$RefValue] = adb_gettext($adsys[$REFTABLE][$RefValue]);
            } else {
                if (isset($adsys[$REFTABLE])) {
                    reset($adsys[$REFTABLE]);
                    while (list($key, $value) = each($adsys[$REFTABLE])) {
                        $temparray[$key] = _($value);
                    }
                }
            }
        }

        return $temparray;
    }
    
    /***
     * Renvoie un rowset contenant la liste de tous les champs d'une table donnée
     * 
     * @param type $Table
     * 
     * @return type
     */
    /*
      public static function getFieldsFromTable(&$dbc, $Table) {

      $sql = "SELECT d.* FROM d_tableliste d, tableliste t WHERE
      t.ident=d.tablen and t.nomc = '" . $Table . "'";

      $result = $dbc->prepareFetchAll($sql);

      if (!$result) {
      signalErreur(__FILE__, __LINE__, __FUNCTION__); //  $result->getMessage()
      }
      //FIXME: petite question: il manquerait pas un closeConnection ici? Il faut voir ce qu'on fait plus loin dans le code
      return $result;
      }
     */

    /**
     * Construit une URL pour affichage et chemin d'accès local pour une image
     * Utilisé dans le cadre de la gestion des photos et signatures
     * 
     * @param string $imagename Nom de l'image
     * 
     * @return Array Tableau "url" => URL de l'image, "localfilepath" => chemin d'accès local de l'image
     */
    public static function imageLocationClient($index) {
      global $global_remote_db_host, $global_remote_id_agence;
        
      $http_domain = "http://".$global_remote_db_host;
      $temp_img = "/images/travaux.gif";

      $imagename = strval($index);
      $photo = '';
      $signature = '';

      if(isMultiAgenceSameServer()) {
        $photo = "/adbanking/images_clients/clients/photos/".$imagename {0}."/".$global_remote_id_agence."_".$imagename;
        $signature = "/adbanking/images_clients/clients/signatures/".$imagename {0}."/".$global_remote_id_agence."_".$imagename;
      }
      else {
        $photo = "/adbanking/images_clients/clients/photos/".$imagename {0}."/".$imagename;
        $signature = "/adbanking/images_clients/clients/signatures/".$imagename {0}."/".$imagename;
      }

      $chemin['photo_chemin_web'] = $http_domain.$photo;
      $chemin['signature_chemin_web']= $http_domain.$signature;
        
      if (getimagesize($chemin['photo_chemin_web']) === false) {
        $chemin['photo_chemin_web'] = $http_domain.$temp_img;
      }

      if (getimagesize($chemin['signature_chemin_web']) === false) {
        $chemin['signature_chemin_web'] = $http_domain.$temp_img;
      }

      return $chemin;
    }

    /**
     * Construit une URL pour affichage et chemin d'accès local pour une image
     * Utilisé dans le cadre de la gestion des photos et signatures
     *
     * @param string $imagename Nom de l'image
     *
     * @return Array Tableau "url" => URL de l'image, "localfilepath" => chemin d'accès local de l'image
     */
    public static function bandeauImageLocationClient($index) {
        global $global_remote_db_host, $global_remote_id_agence;

        $http_domain = "http://".$global_remote_db_host;
        $temp_img = "/images/travaux.gif";

        $imagename = strval($index);
        $photo = '';
        $signature = '';

        if(isMultiAgenceSameServer()) {
            $photo = "/adbanking/images_clients/clients/photos/".$imagename {0}."/".$global_remote_id_agence."_".$imagename;
            $signature = "/adbanking/images_clients/clients/signatures/".$imagename {0}."/".$global_remote_id_agence."_".$imagename;
        }
        else {
            $photo = "/adbanking/images_clients/clients/photos/".$imagename {0}."/".$imagename;
            $signature = "/adbanking/images_clients/clients/signatures/".$imagename {0}."/".$imagename;
        }

        $chemin['photo_chemin_web'] = $http_domain.$photo;
        $chemin['signature_chemin_web']= $http_domain.$signature;

        if (getimagesize($chemin['photo_chemin_web']) === false) {
            $chemin['photo_chemin_web'] = null;
        }

        if (getimagesize($chemin['signature_chemin_web']) === false) {
            $chemin['signature_chemin_web'] = null;
        }

        return $chemin;
    }

    public static function imageLocationPersExt($index) {
      global $global_remote_db_host, $global_remote_id_agence;

      $http_domain = "http://".$global_remote_db_host;
      $temp_img = "/images/travaux.gif";

      $imagename = strval($index);
      $photo = '';
      $signature = '';

      if(isMultiAgenceSameServer()) {
        $photo = "/adbanking/images_clients/perso_ext/photos/".$imagename {0}."/".$global_remote_id_agence."_".$imagename;
        $signature = "/adbanking/images_clients/perso_ext/signatures/".$imagename {0}."/".$global_remote_id_agence."_".$imagename;
      }
      else {
        $photo = "/adbanking/images_clients/perso_ext/photos/".$imagename {0}."/".$imagename;
        $signature = "/adbanking/images_clients/perso_ext/signatures/".$imagename {0}."/".$imagename;
      }

      $chemin['photo_chemin_web'] = $http_domain.$photo;
      $chemin['signature_chemin_web']= $http_domain.$signature;

      if (getimagesize($chemin['photo_chemin_web']) === false) {
        $chemin['photo_chemin_web'] = $http_domain.$temp_img;
      }

      if (getimagesize($chemin['signature_chemin_web']) === false) {
        $chemin['signature_chemin_web'] = $http_domain.$temp_img;
      }

      return $chemin;
    }

    public function countBenefAvanceRapportSituation($id_saison,$province,$commune,$periode,$date_debut=null,$date_fin=null){

        $sql="select count( distinct c.id_benef) as nbre_agri
from ec_commande c
INNER JOIN ec_beneficiaire b on b.id_beneficiaire = c.id_benef
INNER JOIN ec_localisation loc on loc.id = b.id_commune
INNER JOIN ec_localisation loc1 on loc1.id = b.id_province";
if ($periode == 2){
$sql .=" INNER JOIN ec_paiement_commande p on p.id_commande = c.id_commande and p.etat_paye = 2";
    if (($date_debut != null) && ($date_fin != null)) {
        $sql .= " and p.date_creation >= date('$date_debut') and p.date_creation <= date('$date_fin')";
    }
}
 $sql .=" where loc1.libel = '$province' and loc.libel= '$commune' and c.etat_commande not in (7,5,6)";
if ($periode == 1){
    if (($date_debut != null) && ($date_fin != null)){
    $sql .= " and c.date_creation >= date('$date_debut') and c.date_creation <= date('$date_fin')";
    }
}
        $result = $this->getDbConn()->prepareFetchAll($sql);
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $data = array();
        foreach ($result as $row) {
            $data['nbre_agri'] = $row['nbre_agri'];
        }
        return $data;
    }

    public function countBenefRapportRepartitionZone($id_saison,$libel_zone){

        $sql="select count( distinct c.id_benef) as nbre_agri
from ec_commande c
INNER JOIN ec_beneficiaire b ON b.id_beneficiaire = c.id_benef
INNER JOIN ec_localisation l on l.id = b.id_zone
WHERE c.id_saison = $id_saison
and c.etat_commande not in (7,5,6)
and l.libel = '$libel_zone'";
        $result = $this->getDbConn()->prepareFetchAll($sql);
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $data = array();
        foreach ($result as $row) {
            $data['nbre_agri'] = $row['nbre_agri'];
        }
        return $data;
    }

    public static function getFilesRemote($dir, $pattern, $host, $password = 'public', $port = 22, $username = "root"){
        $connection = ssh2_connect($host, $port);
        ssh2_auth_password($connection, $username, $password);

        $stream = ssh2_exec($connection, "ls ".$dir);
        stream_set_blocking($stream, true);
        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);

        $files = explode("\n", stream_get_contents($stream_out));

        $files_metadata = array();
        foreach($files as $key => $file){
            preg_match($pattern, $file, $matches);
            if(!empty($matches)){
                $parts = explode("_", $file);
                $files_metadata[$parts[count($parts) -1]] = array(
                    "file_name" => $file,
                    "relative_path" => $dir.'/'.$file
                );
            }
        }

        ksort($files_metadata);
        return $files_metadata;
    }

}
