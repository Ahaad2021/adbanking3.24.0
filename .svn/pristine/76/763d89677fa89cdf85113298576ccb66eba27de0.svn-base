<?php

/**
 * Description de la classe Client
 *
 * @author danilo
 */
require_once 'ad_ma/app/models/BaseModel.php';

class Client extends BaseModel {

    /** Properties */
    private $_id_client;

    public function __construct(&$dbc, $id_agence = NULL) {
        parent::__construct($dbc, $id_agence);
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Verifier si le client est abonneé au service SMS par le id_cpte
     *
     * @param $id_cpte
     * @return |null
     */
    public function getClientAbnByIdCpte($id_cpte){

        $sql = "SELECT cli.id_client FROM ad_cpt cpt JOIN ad_cli cli ON cpt.id_titulaire = cli.id_client JOIN ad_abonnement a ON cli.id_client = a.id_client WHERE a.id_ag = :id_agence AND cpt.id_cpte = :id_cpte AND a.id_service = 1 AND a.deleted = FALSE";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_cpte' => $id_cpte);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

        if ($result === FALSE || count($result) == 0) {
            return NULL;
        }

        return $result;

    }

  /**
   * Verifier si le client est abonné au service SMS
   *
   * @return boolean
   */
    public function checkIfClientAbonnerSMS($id_client){

      $sql = "SELECT * FROM ad_abonnement WHERE id_ag = :id_agence AND id_client = :id_client AND deleted = FALSE";

      $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);

      $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

      if ($result === FALSE || count($result) == 0) {
        return NULL;
      }

      return true;
    }

    /**
     * Renvoie un tableau associatif avec toutes les données du client dont l'ID est $id_client
     *
     * @param int $id_client
     * 
     * @return array L'identifiant du compte de base du client ou NULL si le client n'existe pas ou ne possède pas de comtpe de base.
     */
    public function getClientDatas($id_client) {

        $sql = "SELECT * FROM ad_cli WHERE id_ag = :id_agence AND id_client = :id_client";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

        if ($result === FALSE || count($result) == 0) {
            return NULL;
        }

        return $result;
    }

    /**
     * PS qui renvoie un string contenant le nom du client.
     * Celui-ci varie seln le statut juridique :
     * PP => pp_nom + pp_prénom
     * PM => pm_raison_sociale
     * GI => gi_nom
     *
     * @param int $id_client
     * 
     * @return string Nom du client
     */
    public function getClientName($id_client) {

        $client_arr = self::getClientDatas($id_client);

        switch ($client_arr['statut_juridique']) {
            case 1 : // PP
                return $client_arr['pp_nom'] . " " . $client_arr['pp_prenom'];
            case 2 :
                return $client_arr['pm_raison_sociale'];
            case 3 :
            case 4 :
                return $client_arr['gi_nom'];
            default :
                //  Solution temporaire au ticket:1325, TODO: voir ticket:1331.
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Statut juridique invalide pour le client %s"), $this->getIdClient()));
            //return NULL;
        }
    }

    /**
     * Retourne l'état du client (voir adsys_etat_client)
     * 
     * @param int $id_client
     * 
     * @return integer L'état du client.
     */
    public function getEtatClient($id_client) {

        $sql = "SELECT etat FROM ad_cli WHERE id_ag = :id_agence AND id_client = :id_client";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);

        $result = $this->getDbConn()->prepareFetchColumn($sql, $param_arr);

        if ($result === FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Renvoie true si le client est débiteur càd s'il possède un découvert sur au moins un de ses comptes
     * 
     * @param int $id_client
     * 
     * @return bool
     */
    public function isClientDebiteur($id_client) {

        $sql = "SELECT * FROM ad_cpt WHERE id_ag = :id_agence AND id_titulaire = :id_client AND solde < 0 AND id_prod != 3";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if (is_array($result) && count($result) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Fonction qui compte le nombre de clients
     * 
     * @param string $where
     * @param string $type
     * 
     * @return array Tableau associatif avec les info de connexion pour l'agence
     */
    public function countMatchedClients($where, $type) {
        $param_arr = array();

        $WhereClause = "";
        if (is_array($where)) {
            $where = array_make_pgcompatible($where);
        }

        //var_dump($where);
        //exit;

        while (list ($key, $value) = each($where)) {
            if ($value != "") {

                switch ($key) {
                    case 'pp_date_naissance':
                        $WhereClause .= " substring(to_char(pp_date_naissance, 'YYYY-MM-DD'),1,10) = :pp_date_naissance AND"; // '$value'"." 00:00:00"." AND";
                        $param_arr[':pp_date_naissance'] = $value;
                        break;
                    case 'anc_id_client':
                        $WhereClause .= " anc_id_client = :anc_id_client AND";
                        $param_arr[':anc_id_client'] = $value;
                        break;
                    case 'id_client':
                        $WhereClause .= " id_client = :id_client AND";
                        $param_arr[':id_client'] = $value;
                        break;
                    case 'id_ag':
                        $WhereClause .= " id_ag = :id_ag AND";
                        $param_arr[':id_ag'] = $value;
                        break;
                    default:
                        $WhereClause .= " upper($key) like '%' || upper('$value') || '%' AND";
                        break;
                }
            }
        }
        if ($type == "pp") {
            $WhereClause .= " statut_juridique = :statut_juridique AND";
            $param_arr[':statut_juridique'] = 1;
        } elseif ($type == "gi") {
            $WhereClause .= " statut_juridique = :statut_juridique AND";
            $param_arr[':statut_juridique'] = 3;
        }

        $WhereClauseCmd = substr($WhereClause, 0, strlen($WhereClause) - 3);
        $sql = "SELECT count(*) FROM ad_cli WHERE " . $WhereClauseCmd . ";";

//        var_dump($sql, $param_arr);
//        exit;

        $result = $this->getDbConn()->prepareFetchColumn($sql, $param_arr);

        if ($result === FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Fonction qui renvoie une liste de clients
     * 
     * @param string $where
     * @param string $type
     * 
     * @return array Tableau associatif avec les info de connexion pour l'agence
     */
    public function getMatchedClients($where, $type) {
        $param_arr = array();

        $WhereClause = "";
        if (is_array($where)) {
            $where = array_make_pgcompatible($where);
        }

        while (list ($key, $value) = each($where)) {
            if ($value != "") {

                switch ($key) {
                    case 'pp_date_naissance':
                        $WhereClause .= " substring(to_char(pp_date_naissance, 'YYYY-MM-DD'),1,10) = :pp_date_naissance AND";
                        $param_arr[':pp_date_naissance'] = $value;
                        break;
                    case 'anc_id_client':
                        $WhereClause .= " anc_id_client = :anc_id_client AND";
                        $param_arr[':anc_id_client'] = $value;
                        break;
                    case 'id_client':
                        $WhereClause .= " id_client = :id_client AND";
                        $param_arr[':id_client'] = $value;
                        break;
                    case 'id_ag':
                        $WhereClause .= " id_ag = :id_ag AND";
                        $param_arr[':id_ag'] = $value;
                        break;
                    default:
                        $WhereClause .= " upper($key) like '%' || upper('$value') || '%' AND";
                        break;
                }
            }
        }
        if ($type == "pp") {
            $WhereClause .= " statut_juridique = :statut_juridique AND";
            $param_arr[':statut_juridique'] = 1;
        } elseif ($type == "gi") {
            $WhereClause .= " statut_juridique = :statut_juridique AND";
            $param_arr[':statut_juridique'] = 3;
        }

        $WhereClauseCmd = substr($WhereClause, 0, strlen($WhereClause) - 3);
        $sql = "SELECT * FROM ad_cli WHERE" . $WhereClauseCmd . " ORDER BY id_client ASC;";

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($result === FALSE || count($result) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Nombre de personnes extérieures répondant à la clause where
     * 
     * @param Array $a_where conditions de recherche sur la table  ad_pers_ext
     * 
     * @return le nombre de personnes extérieures répondant aux clauses where
     */
    public function nombrePersonneExt($a_where) {

        // construction de la chaine de la requete pr cherche le personne ext ds la table ad_pers_ext
        $sql_pe = "SELECT count(id_pers_ext) FROM ad_pers_ext WHERE id_ag = " . $this->getIdAgence() . " AND id_client is null";

        // construction de la chaine de la req pr recherche  client pers_ext ds ad_cli
        $sql_cli = "SELECT count(cli.id_client) FROM ad_cli cli, ad_pers_ext pe WHERE cli.id_ag = " . $this->getIdAgence() . " AND cli.id_client = pe.id_client AND statut_juridique = 1";

        // contruction du critere de selection des client et non client pers_ext
        if (is_array($a_where)) {
            $a_where = array_make_pgcompatible($a_where);
            $sql_pe .= " AND ";
            $sql_cli .= " AND ";
            foreach ($a_where as $champ => $valeur) {
                if ($champ == 'denomination') {
                    $sql_cli .= "pp_nom || ' ' || pp_prenom LIKE '$valeur%' AND";
                    $sql_pe .= " $champ LIKE '$valeur%' AND";
                } elseif ($champ == 'id_client') {
                    $sql_cli .= " cli.$champ = '$valeur' AND"; //prefixé le champ par l'alias cli
                    $sql_pe .= " $champ = '$valeur' AND";
                } elseif ($champ == "lieu_naiss") {
                    $sql_cli .= " cli.pp_lieu_naissance = '$valeur' AND";
                    $sql_pe .= " $champ = '$valeur' AND";
                } elseif ($champ == "date_naiss") {
                    $sql_cli .= " cli.pp_date_naissance = '$valeur' AND";
                    $sql_pe .= " $champ = '$valeur' AND";
                } elseif ($champ == "id_pers_ext") {
                    $sql_pe .= " $champ = '$valeur' AND";
                    $sql_cli .= " $champ = '$valeur' AND";
                } else {
                    $sql_cli .= " cli.$champ = '$valeur' AND";
                    $sql_pe .= " $champ = '$valeur' AND";
                }
            }
            // On retire le dernier 'AND'
            $sql_pe = substr($sql_pe, 0, strlen($sql_pe) - 4);
            $sql_cli = substr($sql_cli, 0, strlen($sql_cli) - 4);
        }

        $nbre_pe = $this->getDbConn()->prepareFetchColumn($sql_pe);

        if ($nbre_pe == NULL || $nbre_pe == FALSE || $nbre_pe < 0) {
            $nbre_pe = 0;
        }

        // Recherche des personnes ext clientes
        $nbre_cli = $this->getDbConn()->prepareFetchColumn($sql_cli);

        if ($nbre_cli == NULL || $nbre_cli == FALSE || $nbre_cli < 0) {
            $nbre_cli = 0;
        }

        return ($nbre_pe + $nbre_cli);
    }

    /**
     * Personnes extérieures répondant à la clause where
     * 
     * @param Array $a_where conditions de recherche sur la table  ad_pers_ext
     * 
     * @returns Array $DATA informations sur les personnes extérieures répondant à la clause where
     */
    public function getPersonneExt($a_where) {

        // construction de la chaine de la requete pr cherche le personne ext ds la table ad_pers_ext
        $sql_pe = "SELECT id_pers_ext, id_client , ";
        $sql_pe .=" denomination, ";
        $sql_pe .="adresse, ";
        $sql_pe .="code_postal, ville, pays, ";
        $sql_pe .="num_tel, ";
        $sql_pe .=" date_naiss, ";
        $sql_pe .="lieu_naiss, ";
        $sql_pe .="type_piece_id, ";
        $sql_pe .=" num_piece_id, ";
        $sql_pe .=" lieu_piece_id, ";
        $sql_pe .=" date_piece_id, ";
        $sql_pe .="date_exp_piece_id ,";
        $sql_pe .="id_ag ";
        $sql_pe .=" FROM ad_pers_ext WHERE id_ag = :id_agence AND id_client is null ";

        // contruction de la chaine de la req pr recherche  client pers_ext ds ad_cli
        $sql_cli = "SELECT pe.id_pers_ext, cli.id_client , ";
        $sql_cli .="pp_nom || ' ' || pp_prenom as denomination, ";
        $sql_cli .="cli.adresse, ";
        $sql_cli .="cli.code_postal, cli.ville, cli.pays, ";
        $sql_cli .="cli.num_tel, ";
        $sql_cli .="cli.pp_date_naissance as date_naiss, ";
        $sql_cli .="cli.pp_lieu_naissance as lieu_naiss, ";
        $sql_cli .="cli.pp_type_piece_id as type_piece_id, ";
        $sql_cli .="cli.pp_nm_piece_id as num_piece_id, ";
        $sql_cli .="cli.pp_lieu_delivrance_id as lieu_piece_id, ";
        $sql_cli .="cli.pp_date_piece_id  as date_piece_id, ";
        $sql_cli .="cli.pp_date_exp_id as date_exp_piece_id ,";
        $sql_cli .="cli.id_ag ";
        $sql_cli .="FROM ad_cli cli, ad_pers_ext pe WHERE cli.id_ag = :id_agence AND cli.id_client = pe.id_client AND statut_juridique = 1";

        // contruction du critere de selection des client et non client pers_ext

        if (is_array($a_where)) {
            $a_where = array_make_pgcompatible($a_where);
            $sql_pe .= " AND ";
            $sql_cli .= " AND ";
            foreach ($a_where as $champ => $valeur) {
                if ($champ == "denomination") {
                    $sql_cli .= "pp_nom || ' ' || pp_prenom LIKE '$valeur%' AND";
                    $sql_pe .= " $champ LIKE '$valeur%' AND";
                } elseif ($champ == "id_client") {
                    $sql_cli .= " cli.$champ = '$valeur' AND"; //prefixé le champ par l'alias cli
                    $sql_pe .= " $champ = '$valeur' AND";
                } elseif ($champ == "lieu_naiss") {
                    $sql_cli .= " cli.pp_lieu_naissance = '$valeur' AND";
                    $sql_pe .= " $champ = '$valeur' AND";
                } elseif ($champ == "date_naiss") {
                    $sql_cli .= " cli.pp_date_naissance = '$valeur' AND";
                    $sql_pe .= " $champ = '$valeur' AND";
                } elseif ($champ == "id_pers_ext") {
                    $sql_pe .= " $champ = '$valeur' AND";
                    $sql_cli .= " $champ = '$valeur' AND";
                } else {
                    $sql_cli .= " cli.$champ = '$valeur' AND";
                    $sql_pe .= " $champ = '$valeur' AND";
                }
            }
            // On retire le dernier 'AND'
            $sql_pe = substr($sql_pe, 0, strlen($sql_pe) - 4);
            $sql_cli = substr($sql_cli, 0, strlen($sql_cli) - 4);
        }

        // concaténation des req pr unir le resultat
        // attention: le nbre de champ ds la requete sql_pe doit correspondre au de champ ds la requete sql_cli
        $sql = $sql_pe . " UNION " . $sql_cli . " ;";

        $param_arr = array(':id_agence' => $this->getIdAgence());

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($result === FALSE || count($result) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Ajouter une personne extérieure dans la base de donnée
     * 
     * @param Array $DATA données sur la personne extérieure
     * 
     * @return ErrorObj
     */
    public function ajouterPersonneExt($DATA) {
        //global $dbHandler, $global_id_agence;
        //$db = $dbHandler->openConnection();

        /*
          $IMAGES = array(
          'photo' => $DATA['photo'],
          'signature' => $DATA['signature'],
          'id_ag' => $this->getIdAgence()
          );
          unset($DATA['photo']);
          unset($DATA['signature']);
         */
        $DATA['id_ag'] = $this->getIdAgence();

        $sql = buildInsertQuery('ad_pers_ext', $DATA);

        $result = $this->getDbConn()->execute($sql);

        if ($result === FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $sql = "select currval('ad_pers_ext_id_pers_ext_seq')";

        $id_pers_ext = $this->getDbConn()->prepareFetchColumn($sql);

        if ($id_pers_ext === FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        /*
          $result = $db->query($sql);
          if (DB :: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
          }

          $row = $result->fetchrow();

          $id_pers_ext = $row[0];
         */

        // Insertion d'image
        /*
          $PATHS = imageLocationPersExt($id_pers_ext);
          foreach ($IMAGES as $imagename => $imagepath) {
          $source = $IMAGES[$imagename];

          if ($imagename == 'photo')
          $destination = $PATHS["photo_chemin_local"];
          else
          if ($imagename == 'signature')
          $destination = $PATHS["signature_chemin_local"];

          if (($source == NULL) or ($source == "") or ($source == "/adbanking/images/travaux.gif"))
          exec("rm -f " . escapeshellarg($destination));
          else {
          if (is_file($source)) {
          rename($source, $destination);
          chmod($destination, 0777);
          }
          }
          }
         */

        return new ErrorObj(NO_ERR, array('id_pers_ext' => $id_pers_ext));
    }

    /**
     * Modifier une personne extérieure dans la base de donnée
     * 
     * @param integer $id_pers_ext identifiant de la personne extérieure
     * @param Array $DATA données sur la personne extérieure
     * 
     * @return ErrorObj
     */
    public function modifierPersonneExt($id_pers_ext, $DATA) {
        //global $dbHandler, $global_id_agence;
        //$db = $dbHandler->openConnection();

        /*
          $IMAGES = array(
          'photo' => $DATA['photo'],
          'signature' => $DATA['signature']
          );
          unset($DATA['photo']);
          unset($DATA['signature']);
         */

        $WHERE['id_pers_ext'] = $id_pers_ext;
        $WHERE['id_ag'] = $this->getIdAgence();

        $sql = buildUpdateQuery('ad_pers_ext', $DATA, $WHERE);

        $result = $this->getDbConn()->execute($sql);

        if ($result === FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Insertion d'image
        /*
          $PATHS = imageLocationPersExt($id_pers_ext);
          foreach ($IMAGES as $imagename => $imagepath) {
          $source = $IMAGES[$imagename];

          if ($imagename == 'photo')
          $destination = $PATHS["photo_chemin_local"];
          else
          if ($imagename == 'signature')
          $destination = $PATHS["signature_chemin_local"];

          if (($source == NULL) or ($source == "") or ($source == "/adbanking/images/travaux.gif"))
          exec("rm -f " . escapeshellarg($destination));
          else {
          if ($source != $PATHS[$imagename . "_chemin_web"]) {
          rename($source, $destination);
          chmod($destination, 0777);
          }
          }
          }
         */

        return new ErrorObj(NO_ERR);
    }
    
    /**
    * Recupère les groupes solidaires auxquels le membre est inscrit
    * 
    * @param int $id_membre ID du client
    * @param int $id_group ID du group
    * 
    * @return ErrorObj Objet Erreur oubien un tableau contenant la liste des groupes solidaires
    */
    public function getGroupSol($id_membre, $id_group) {

        $sql = "SELECT * from ad_grp_sol where id_ag=:id_agence AND id_membre=:id_membre ";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_membre' => $id_membre);

        if (isset($id_group)) {
            $sql .= " AND id_grp_sol != :id_grp_sol ";

            $param_arr[':id_grp_sol'] = $id_group;
        }

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return new ErrorObj(NO_ERR, $results);
    }
    
    /**
    * Recupère un groupe solidaire
    * 
    * @param int $id_group ID du groupe solidaire
    * 
    * @return ErrorObj Objet Erreur oubien un tableau contenant un enregistrement du groupe solidaire
    */
    public function getNomGroup($id_group) {
        
        $sql = "SELECT * from ad_cli where id_ag=:id_agence AND id_client=:id_group ";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_group' => $id_group);

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return new ErrorObj(NO_ERR, $results);
    }
    
    /**
    * Donne la liste des membres d'un groupe solidaire.
    * 
    * @param int $a_id_client L'identifiant du client de type groupe solidaire
    * 
    * @return ErrorObj Un ErrorObj avec comme paramètre un array contenant la liste des identifiants client des membres du groupe solidaire.
    */
    public function getListeMembresGrpSol($a_id_client) {
        
        $sql = "SELECT id_membre FROM ad_grp_sol WHERE id_ag=:id_agence AND id_grp_sol=:a_id_client ";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':a_id_client' => $a_id_client);

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return new ErrorObj(NO_ERR, $results);
    }
    
    /**
     * Fonction qui renvoie les champs extras des tables
     * 
     * @param int $id_client
     * @param text $id
     * 
     * @return array Tableau des champs
     */
    public function getChampsExtrasCLIENTValues($id_client, $id = NULL) {
        
        $sql = "SELECT * FROM champs_extras_valeurs_ad_cli WHERE id_ag=:id_agence AND id_client=:id_client ";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);
        
        if (!is_null($id)) {
            $sql .= " AND id_champs_extras_table = :id_champs_extras_table  ";

            $param_arr['id_champs_extras_table'] = $id;
        }

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $champsExtrasValues = array();

        foreach ($results as $key => $value) {
            $champsExtrasValues[$value['id_champs_extras_table']] = $value['valeur'];
        }

        return $champsExtrasValues;
    }
    
    /**
     * Extrait la photo et le spécimen de signature du client et renvoie un tableau avec les URLs donnant accès à ces deux fihiers
     * REM: Dans le cas où le client est une PM ou un GI, on prend la photo et la signature du premier responsable trouvé dans la DB ayant le pouvoir de signature
     *
     * @param int $id_client ID du client
     * 
     * @return Array Tableau avec "photo" => nom du fichier de la photo et "signature" => nom du fichier contenan tla signature
     */
    public function getImagesClient($id_client) {

        $CLI = self::getClientDatas($id_client);

        if ($CLI["statut_juridique"] == 1) {
            $imagepath = Divers::imageLocationClient($id_client);

            $PICPATHS = $imagepath['photo_chemin_web'];

            $SIGNPATHS = $imagepath['signature_chemin_web'];

            return array ("signature" => $SIGNPATHS, "photo" => $PICPATHS);
        } else {

            return array ("signature" => NULL, "photo" => NULL);
        }
    }

    /**
     * Extrait la photo et le spécimen de signature du client et renvoie un tableau avec les URLs donnant accès à ces deux fihiers
     * REM: Dans le cas où le client est une PM ou un GI, on prend la photo et la signature du premier responsable trouvé dans la DB ayant le pouvoir de signature
     *
     * @param int $id_client ID du client
     *
     * @return Array Tableau avec "photo" => nom du fichier de la photo et "signature" => nom du fichier contenan tla signature
     */
    public function getBandeauImagesClient($id_client) {

        $CLI = self::getClientDatas($id_client);

        if ($CLI["statut_juridique"] == 1) {
            $imagepath = Divers::bandeauImageLocationClient($id_client);

            $PICPATHS = $imagepath['photo_chemin_web'];

            $SIGNPATHS = $imagepath['signature_chemin_web'];

            return array ("signature" => $SIGNPATHS, "photo" => $PICPATHS);
        } else {

            return array ("signature" => NULL, "photo" => NULL);
        }
    }

    /**
     * Extrait la photo et le spécimen de signature du client et renvoie un tableau avec les URLs donnant accès à ces deux fihiers
     * REM: Dans le cas où le client est une PM ou un GI, on prend la photo et la signature du premier responsable trouvé dans la DB ayant le pouvoir de signature
     *
     * @param int $id_pers_ext ID du personne extérieure
     *
     * @return Array Tableau avec "photo" => nom du fichier de la photo et "signature" => nom du fichier contenan tla signature
     */
    public function getImagesPersExt($id_pers_ext) {

      $imagepath = Divers::imageLocationPersExt($id_pers_ext);

      $PICPATHS = $imagepath['photo_chemin_web'];

      $SIGNPATHS = $imagepath['signature_chemin_web'];

      return array ("signature" => $SIGNPATHS, "photo" => $PICPATHS);

    }

    /** Getters & Setters */
    public function getIdClient() {
        return $this->_id_client;
    }

    public function setIdClient($value) {
        $this->_id_client = $value;
    }

    public function getClientAccountData($id_client){
        $sql = "select c.statut_juridique, c.pp_nom, c.pp_prenom,c. pm_raison_sociale, c.gi_nom, d.num_complet_cpte 
                FROM ad_cli c
                INNER JOIN ad_cpt d ON c.id_cpte_base = d.id_cpte
                WHERE c.id_client = :id_client AND c.id_ag = :id_agence";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($result === FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }
}
