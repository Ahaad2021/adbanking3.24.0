<?php

function checkExistClientAbonnee($id_client)
{
    global $dbHandler;
    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM ad_data_client_mensuel_abo WHERE id_client = $id_client";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj (ERR_DB_SQL, _("function: checkExistClientAbonnee, SQL: $sql"));
//        echo "Erreur dans la fonction (checkExistClientAbonnee) instruction SQL --> $sql\n";
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return false;
    }
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);

    return $DATAS;
}

function getGarantieEnCours($id_doss){
    global $dbHandler;
    $db = $dbHandler->openConnection();
    $sql = "SELECT g.* FROM ad_gar g INNER JOIN ad_dcr d ON d.id_doss = g.id_doss INNER JOIN ad_cli c ON d.id_client = c.id_client WHERE g.id_doss = $id_doss AND g.etat_gar IN (1,2,3) ";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getGarantieEnCours) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("function: getGarantieEnCours, SQL: $sql"));
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return false;
    }
    $mnt_gar =0;
    while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
        $mnt_gar += $row['montant_vente'];
    }
    //$DATAS[$row["id_annee"]] = $row['libel'];

    $dbHandler->closeConnection(true);

    return $mnt_gar;
}

//----------------------Renvoi l'ensemble des dossiers de crédit du client---------------------------//

function getDossierClientMobile($id_client, $where=null) {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT ad_dcr.*,adsys_produit_credit.devise,adsys_produit_credit.libel as libelle FROM ad_dcr,adsys_produit_credit WHERE ad_dcr.id_ag=adsys_produit_credit.id_ag AND  ad_dcr.id_ag=$global_id_agence AND id_client = '$id_client' AND id_prod=id";

    if ($where != null) {
        $sql .= " AND ".$where;
    }

    $sql .=" ORDER BY id_doss";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getDossierClientMobile) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("function: getDossierClientMobile, SQL: $sql"));
    }
    if ($result->numrows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $i=1;
    $retour = array();
    while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $retour[$i]=$rows;
        $i++;
    }
    $dbHandler->closeConnection(true);
    return $retour;
}

function GetInfoProdMobLending($id_prod)
{

    global $dbHandler;
    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM adsys_produit_credit WHERE id = $id_prod";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (GetInfoProdMobLending) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("function: GetInfoProdMobLending, SQL: $sql"));
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return false;
    }
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);

    return $DATAS;
}


function getCreRetardMaxPredit($t_mnt_dem, $t_dure_remb, $t_lg_histo, $t_nbre_credit,$t_age,$sexe,$t_salaire = null,$t_regularite=null)
{
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM  ad_data_client_stat WHERE tranche_mnt_dem = $t_mnt_dem AND tranche_dure = $t_dure_remb AND tranche_long_hist = $t_lg_histo AND tranche_nbre_credit = $t_nbre_credit AND tranche_age = $t_age AND sexe = '$sexe' ";

    if ($t_salaire != null){
        $sql .= " AND tranche_mnt_sal = $t_salaire ";
    }
    if ($t_regularite != null){
        $sql .= " AND tranche_regularite = $t_regularite ";
    }

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getCreRetardMaxPredit) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("function: getCreRetardMaxPredit, SQL: $sql"));
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return false;
    }
    $count =0;
    $tot_cre_etat_max = 0;
    $cre_retard_max_predit = 0;
    while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
        $count++;
        $tot_cre_etat_max += $row['max_etat'];
    }

    $cre_retard_max_predit = $tot_cre_etat_max / $count;

    $dbHandler->closeConnection(true);

    return $cre_retard_max_predit;

}
function getClientDatas($id_client) {
    /* Renvoie un tableau associatif avec toutes les données du client dont l'ID est $id_client
       Valeurs de retour :
       Le tableau si OK
       NULL si le client n'existe pas
       Die si erreur de la base de données
    */
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM ad_cli WHERE id_ag=$global_id_agence AND id_client = '$id_client' ";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(true);
//        echo "Erreur dans la fonction (getClientDatas)\n";
        return new ErrorObj (ERR_DB_SQL, _("function: getClientDatas, SQL: $sql"));
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);
    // FIXME ** TF - 27/09/2002 -- Ce champs temporaire ne doit pas être visible par les modules
    unset ($DATAS["tmp_already_accessed"]);
    return $DATAS;
}
function getClientDatasMob($id_client) {

    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM ad_cli WHERE id_ag=$global_id_agence AND id_client = '$id_client' ";
    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(true);
//        echo "Erreur dans la fonction (getClientDatasMob)\n";
        return new ErrorObj (ERR_DB_SQL, _("function: getClientDatasMob, SQL: $sql"));
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);
    // FIXME ** TF - 27/09/2002 -- Ce champs temporaire ne doit pas être visible par les modules
    unset ($DATAS["tmp_already_accessed"]);
    return $DATAS;
}

function getSoldeInteretGarPenMob($id_doss){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();


    $sql = "SELECT * FROM ad_etr WHERE id_ag=$global_id_agence AND id_doss = $id_doss;";
    $result=$db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getSoldeInteretGarPenMob)\n";
        return new ErrorObj (ERR_DB_SQL, _("function: getSoldeInteretGarPenMob, SQL: $sql"));
    }

    $retour= array();
    $capital = 0;
    $interet = 0;
    $garantie = 0;
    $penalite = 0;

    while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
        $capital += $tmprow['solde_cap'];
        $interet += $tmprow['solde_int'];
        $garantie += $tmprow['solde_gar'];
        $penalite += $tmprow['solde_pen'];
    }

    $retour['solde_cap']=$capital;
    $retour['solde_int']=$interet;
    $retour['solde_gar']=$garantie;
    $retour['solde_pen']=$penalite;

    $dbHandler->closeConnection(true);
    return $retour;

}

function array_make_pgcompatible($ary) {
    if (! is_array($ary)){
//        echo "Fonction array_make_pgcompatible : L'argument attendu est un array \n";
//        exit();
        return new ErrorObj (ERR_GENERIQUE, _("Fonction array_make_pgcompatible : L'argument attendu est un array"));
    }
    foreach ($ary AS $key => $value)
        $ary[$key] = string_make_pgcompatible($ary[$key]);
    return $ary;
}
function string_make_pgcompatible($str) {
    return addslashes($str);
}
function buildUpdateQuery ($TableName, $Fields, $Where="") {

    if (sizeof($Fields) == 0) {
//        signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Aucun champ à mettre à jour"));
//        echo "Erreur dans la fonction (buildUpdateQuery) : Aucun champ à mettre à jour\n";
        return new ErrorObj (ERR_DB_SQL, _("fonction (buildUpdateQuery) : Aucun champ à mettre à jour"));
    }
    $Fields = array_make_pgcompatible($Fields);
    reset($Fields);
    $sql = "UPDATE $TableName SET ";
    while (list($key, $value) = each($Fields)) {
        if ("$value" == '')
            $sql .= $key." = NULL, ";
        else
            $sql .= $key." = '".$value."', ";
    }
    $sql = substr($sql, 0, strlen($sql) - 2);
    if (is_array($Where)) {
        $sql .= " WHERE  ";
        while (list($key, $value) = each($Where))
            $sql .= " $key = '$value' AND";
        $sql = substr($sql, 0, strlen($sql) - 3);
    }
    $sql .=";";
    return $sql;
}
function buildInsertQuery ($TableName, $Fields) {

    if (count($Fields) == 0){
//        echo "Fonction buildInsertQuery : Aucun champ à ajouter! \n";
//        exit();
        //signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Aucun champ à ajouter"));
        return new ErrorObj (ERR_GENERIQUE, _("Fonction buildInsertQuery : Aucun champ à ajouter"));
    }
    // On rend le tableau PG Compilant
//    $Fields = array_make_pgcompatible($Fields);
    $sql = "INSERT INTO $TableName (";
    reset($Fields);
    while (list($key, $value) = each($Fields))
        $sql .= "$key, ";
    $sql = substr($sql, 0, strlen($sql) - 2);
    $sql .=") VALUES (";
    reset($Fields);
    while (list($key, $value) = each($Fields)) {
        if ($value == "")
            $sql .= "NULL, ";
        else
            $sql .="'$value', ";
    };
    $sql = substr($sql, 0, strlen($sql) - 2);
    $sql .=");";

    return $sql;
}
function getNumCredit($id_client) {
    /*Renvoie le rang du dernier crédit d'un client
      INPUT : $id_client l'identifiant du client (exp 1000)
      Valeurs de retour :
      nbre si OK
      NULL si non renseigné
      Die si refus de la base de données
    */
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();
    $sql = "SELECT max(num_cre) FROM ad_dcr WHERE id_client ='$id_client' and id_ag=$global_id_agence ";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //signalErreur(__FILE__,__LINE__,__FUNCTION__, "DB: ".);
//        echo "Erreur dans la fonction (getNumCredit) DB: ".$result->getMessage()."\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getNumCredit : ".$result->getMessage()));
    }
    $dbHandler->closeConnection(true);
    $tmpRow = $result->fetchrow();
    $num = $tmpRow[0];
    return $num;
}
function getDataCombinaisonGlobale() {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ml_combinaison_global";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getDataCombinaisonGlobale) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getDataCombinaisonGlobale : $sql"));
    }
    if ($result->numrows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $i=1;
    $retour = array();
    while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $retour[$i]=$rows;
        $i++;
    }
    $dbHandler->closeConnection(true);
    return $retour;
}

function getDataClientAbonnee($client = NULL) {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ml_donnees_client_abonnees";

    if($client != NULL){
        $sql .= " WHERE client = $client";
    }
    $sql .=" ORDER BY client ASC";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //echo "Erreur dans la fonction (getDataClientAbonnee) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getDataClientAbonnee : $sql"));
    }
    if ($result->numrows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $i=1;
    $retour = array();
    while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $retour[$i]=$rows;
        $i++;
    }
    $dbHandler->closeConnection(true);
    return $retour;
}
function getProdInfo($whereCond, $id_doss = NULL ,$prod_is_actif = NULL) {
    global $dbHandler,$global_id_agence;
    $Produit = array();

    $db = $dbHandler->openConnection();

    if ($id_doss != NULL && $id_doss > 0) {
        $sql = "SELECT * FROM get_ad_dcr_ext_credit($id_doss, null, null, null, $global_id_agence)";
    } else {
        $sql = "SELECT * FROM adsys_produit_credit";
    }

    if (($whereCond == null) || ($whereCond == "")) {
        $sql .=	" WHERE ";
    } else {
        $sql .=	" $whereCond AND ";
    }
    //ticket_469: ajoute champ is_produit_actif in adsys_produit_credit
    if($prod_is_actif != NULL){
        $sql .=" is_produit_actif='".$prod_is_actif."'AND ";
    }
    $sql .="id_ag=$global_id_agence ORDER BY id";

    $result=$db->query($sql);
    if ($result->numRows() == 0)
        return new ErrorObj (ERR_GENERIQUE, _("Le produit de crédit n'existe pas, veillez contactez votre agence"));

    if (DB::isError($result))	{
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getProdInfo) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getProdInfo : $sql"));
    }

    while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        array_push($Produit,$rows);
    }
    $dbHandler->closeConnection(true);
    return $Produit;
}
function insereCredit($DATA, $id_utilisateur = NULL) {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();
    $DATA['id_ag']= $global_id_agence;

    $sql = buildInsertQuery ("ad_dcr", $DATA);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (insereCredit) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction insereCredit : $sql"));
    }

    //on récupère le id du chèque qu'on vient d'insérer pour le mettre dans l'historique
    $sql = "SELECT max(id_doss) from ad_dcr where id_ag=$global_id_agence ;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (insereCredit) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction insereCredit : $sql"));
    }
//    var_dump($result);
    $tmprow = $result->fetchrow();
    $id_doss = $tmprow[0];

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR, $id_doss);
}
function insereDossierUSSD($DATA, $func_sys_ins_doss = 105)
{
    global $dbHandler, $global_monnaie, $adsys, $global_id_client, $global_nom_login;
    $db = $dbHandler->openConnection();

    foreach ($DATA as $id_cli => $val) {
        $val = array_make_pgcompatible($val);
        // Insertion du dossier de crédit
        $id_prod = $val["id_prod"];
        $PRODS = getProdInfo(" WHERE id = " . $id_prod . " AND is_mobile_lending_credit = 't' ");
        if ($PRODS->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $PRODS;
        }
        $PROD = $PRODS[0];

        $myErr = insereCredit($val);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
        $id_prod = $val["id_prod"];
        $DOSS[$id_cli] = $myErr->param;


        $myErr = ajout_historique($func_sys_ins_doss, $id_cli, $val["id_prod"], $global_nom_login, date("r"), null, NULL);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR, $DOSS);
    }
}
/**
 * Renvoie l'identifiant de l'agence en utilisant la procédure stockée NumAgc()
 */
function getNumAgence() {
    // Fonction qui renvoie le numéro de l'agence, en fait le id_ag de la première entrée de l table ad_agc

    global $dbHandler;
    $db = $dbHandler->openConnection();
    $sql = "SELECT NumAgc()";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getNumAgence) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getNumAgence : $sql"));
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0)
        return new ErrorObj (ERR_DB_SQL, _("Fonction getNumAgence : $sql"));
    $tmprow = $result->fetchrow();
    if ($result->numRows() > 1) return 0;
    return $tmprow[0];

}
/**
 * Ticket 792
 * Fonction pour recuperer le compte comptable associer au function IAP : Interet a Payer
 * NO PARAM
 * Return string
 */
function getCompteIAP(){
    global $global_id_agence, $global_monnaie;
    global $dbHandler;

    $db = $dbHandler->openConnection();
    $global_id_agence = getNumAgence();

    // Recuperation du parametrage compte comptable des interets a payer
    $sql = "SELECT cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE id_ag = $global_id_agence;";
    $result=$db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        erreur("calcul_interets_a_payer()", $result->getUserInfo());
    }

    $tmprow = $result->fetchrow();
    $cpte_calc_IAP = $tmprow[0];

    $dbHandler->closeConnection(true);
    return $cpte_calc_IAP;
}
function insertHistoriqueExterieur($data_ext) {
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    //On commence par récupérer le numéro de lot
    $sql = "SELECT nextval('ad_his_ext_seq')";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (insertHistoriqueExterieur) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction insertHistoriqueExterieur : $sql"));
    }
    $row = $result->fetchrow();
    $id_his_ext = $row[0];

    $data_ext["id"] = $id_his_ext;
    $data_ext["id_ag"] = $global_id_agence;

    $sql = buildInsertQuery("ad_his_ext", $data_ext);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (insertHistoriqueExterieur) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction insertHistoriqueExterieur : $sql"));
    }

    $dbHandler->closeConnection(true);

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
function makeNumEcriture($id_jou, $id_exo) {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();

    // On prend tous les comptes à soldes négatifs sauf les comptes de crédit
    $sql = "SELECT last_ref_ecriture FROM ad_journaux WHERE id_ag=$global_id_agence AND id_jou = $id_jou FOR UPDATE";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(true);
//        echo "Erreur dans la fonction (makeNumEcriture) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction makeNumEcriture : $sql"));
    }
    $row = $result->fetchrow();
    $num_ecr = $row[0];
    $num_ecr++;

    $JOU = getInfosJournal($id_jou);
    $code_jou = $JOU[$id_jou]["code_jou"];

    $ref_ecriture = $code_jou."-".sprintf("%08d", $num_ecr)."-".sprintf("%02d", $id_exo);

    $sql = "UPDATE ad_journaux SET last_ref_ecriture = $num_ecr WHERE id_ag=$global_id_agence AND id_jou = $id_jou";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(true);
//        echo "Erreur dans la fonction (makeNumEcriture) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction makeNumEcriture : $sql"));
    }

    $dbHandler->closeConnection(true);

    return $ref_ecriture;
}
function getInfosJournal($id_jou = NULL) {
    /*
      Renvoie les infos des journaux
    */

    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql="SELECT *  FROM ad_journaux where id_ag=$global_id_agence ";

    if ($id_jou != NULL)
        $sql .= "AND id_jou=$id_jou";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getInfosJournal) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getInfosJournal : $sql"));
    }

    $jnl=array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $jnl[$row["id_jou"]]=$row;
    }

    $dbHandler->closeConnection(true);

    return $jnl;
}
/**
 * Fonction pour verifier si un montant est des decimaux ex. 100.15 ...
 * PARAM : mnt
 * RETURN : BOOLEAN $hasDecimal
 */
function hasDecimalMontant($mnt)
{
    $hasDecimal = false;

    $mntIAP_Arrondie = ROUND($mnt);
    $diff = abs($mnt - $mntIAP_Arrondie);
    if ($diff > 0){
        $hasDecimal = true;
    }

    return $hasDecimal;

}
/**
 *
 * Arrondi un montant selon la precision du devise
 * @author B&D
 * @param numeric $mnt
 * @param string $devise
 * @return numeric
 */
function arrondiMonnaiePrecision($mnt, $devise = NULL)
{
    global $global_monnaie,$global_id_agence;

    if (empty($devise)) {
        $devise = $global_monnaie;
    }

    $precisionDevise = getPrecisionDevise($devise);
    $mnt = round($mnt, $precisionDevise);

    return $mnt;
}
/**
 *
 * Retourne la precision d'un devise
 *
 * @param String $devise
 * @return integer $precision
 */
function getPrecisionDevise($devise)
{
    global $error;

    if(!empty ($devise)) {
        $infos_devise = getInfoDevise($devise);
        return $infos_devise['precision'];
    }
    else {
//        echo "Erreur dans la fonction (getInfosJournal) : La devise n'est pas renseigné\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction getPrecisionDevise : La devise n'est pas renseigné"));
    }
}
/**
 * Retourne tous les champs de la table devise pour une devise donnée ($dev)
 * @author Bernard DE BOIS
 * @param char(3) code de la devise
 * @return array
 */
function getInfoDevise($dev) {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $retour = NULL;

    $sql = "SELECT * FROM devise WHERE code_devise = '$dev' and id_ag=$global_id_agence ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getInfoDevise) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getInfoDevise : $sql"));
    }
    $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);
    return $retour;
}
/*---------------function pour recuperer soit le compte courus a recevoir ou le montant interet calculer------------------------------*/
function get_calcInt_cpteInt($montant=false, $compte=false, $id_doss = null, $id_ech = null){
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    if ($montant == true && $compte == false){
        $sql_recup_int_cal="select ((select sum(montant) from ad_calc_int_recevoir_his where id_doss = $id_doss and etat_int = 1";
        if($id_ech != null){
            $sql_recup_int_cal .= " and id_ech = $id_ech";
        }
        $sql_recup_int_cal .=") - coalesce((select sum(montant) from ad_calc_int_recevoir_his where id_doss = $id_doss and etat_int = 2";
        if ($id_ech != null){
            $sql_recup_int_cal .= "and id_ech = $id_ech";
        }
        $sql_recup_int_cal .= "),0)) as int_calc;";

        $result_recup_int_cal = $db->query($sql_recup_int_cal);

        if (DB::isError($result_recup_int_cal)) {
            $dbHandler->closeConnection(false);
//            echo "Erreur dans la fonction (get_calcInt_cpteInt) instruction SQL --> $sql_recup_int_cal\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction get_calcInt_cpteInt : $sql_recup_int_cal"));

        }
        $row_recup_int_cal = $result_recup_int_cal->fetchrow(DB_FETCHMODE_ASSOC);
        $resultat_int = $row_recup_int_cal['int_calc'];
    }
    if ($compte == true && $montant == false){
        //recuperation du compte interet couru a recevoir
        $sql_cpte_int_recevoir = "select cpte_cpta_int_recevoir from adsys_calc_int_recevoir";
        $result_cpte_int_recevoir = $db->query($sql_cpte_int_recevoir);
        if (DB::isError($result_cpte_int_recevoir)) {
            $dbHandler->closeConnection(false);
//            echo "Erreur dans la fonction (get_calcInt_cpteInt) instruction SQL --> $sql_cpte_int_recevoir\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction get_calcInt_cpteInt : $sql_cpte_int_recevoir"));
        }
        $row_cpte_int_recevoir = $result_cpte_int_recevoir->fetchrow();
        $resultat_int = $row_cpte_int_recevoir[0];
    }

    $dbHandler->closeConnection(true);

    return $resultat_int;

}
function setSoldeComptable($cpte, $sens, $montant, $devise, $isOperationIAR=false) {
    /*
    Fonction qui met à jour le solde d'un compte de comptabilité.
    On devrait décider qu'on ne peut pas mouvementer un compte collectif mais plutôt un sous-ompte. Mais, pour le moment,
    on suppose que chaque compte a un solde indépendant et pour obtenir le solde d'un compte collectif, il faudra faire la somme  des sous-comptes.

    Au niveau de la DB, on devrait faire des CHECK CONSTRAINTS pour s'assurer en fonction du sens du compte que :
    - Un compte créditeur ne peut devenir négatif
    - Un compte débiteur ne peut devenir positif
    Il faudrait trouver un moyen de récupérer l'erreur interne générée par un trigger

    Pour l'instant, on passe par PHP pour implémenter les contraintes d'intégrité

    * cpte est le n° compte comptable
    * sens est SENS_DEBIT (signe -) ou SENS_CREDIT (signe +)
    * montant à mouvementer sur le compte c'est en valeur absolue
    * devise = Devise du mouvement


    FIXME : tester si le compte qu'on veut mettre à jour n'est pas fermé, bloqué, etc

    */

//  echo "On met à jour le compte comptable $cpte";

    global $dbHandler,$global_id_agence, $error;

    $db = $dbHandler->openConnection();

    //Quel est le solde du compte
    $sql = "SELECT solde, sens_cpte, cpte_centralise, devise ";
    $sql .= "FROM ad_cpt_comptable ";
    $sql .= "WHERE id_ag = $global_id_agence AND num_cpte_comptable = '$cpte' ";
    $sql .= "FOR UPDATE OF ad_cpt_comptable;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (setSoldeComptable) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction setSoldeComptable : $sql"));
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (setSoldeComptable) : Le compte comptable lié n existe pas\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction setSoldeComptable : Le compte comptable lié n existe pas"));
    }
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $solde = $row["solde"];
    $sens_cpte = $row["sens_cpte"];
    $cpte_centralise = $row["cpte_centralise"];
    $devise_cpte = $row["devise"];

    // #514 : Arrondir le montant a passer :
    $montant_nonArrondie = $montant;
    $soldeInit_cpte = $solde;
    //REL-101 : on verifie si le montant du compte client contient les deicmaux ex. 50000.536
    $hasDecimalSoldeCpte = hasDecimalMontant($soldeInit_cpte);
    $montant = arrondiMonnaiePrecision($montant, $devise);

    $cpte_int_couru = get_calcInt_cpteInt(false, true, null);

    // on garde le montant non arrondie uniquement si c'est le compte comptable des IAR + REL-101
    if((!is_null($cpte_int_couru) && ($cpte == $cpte_int_couru || $isOperationIAR === true)) || $hasDecimalSoldeCpte === true) {
        $montant = $montant_nonArrondie;
    }

    if ($sens == SENS_DEBIT) {
        $solde = $solde - $montant;
    } else if ($sens == SENS_CREDIT) {
        $solde = $solde + $montant;
    }

    // on garde le montant non arrondie uniquement si c'est le compte comptable des IAR + REL-101
    if((!is_null($cpte_int_couru) && ($cpte == $cpte_int_couru || $isOperationIAR === true)) || $hasDecimalSoldeCpte === true){
        $temp_solde = $solde;
        $abs_diff = abs($solde);
        if ($abs_diff < 1){
            $solde = arrondiMonnaiePrecision(abs($solde), $devise);//round($solde, EPSILON_PRECISION);
        }
        if ($abs_diff < 1 && $soldeInit_cpte < 0 && $temp_solde <0){
            $solde = -1 * $solde;
        }
    }

    if ($sens_cpte == 1) {
        //cas des comptes naturellement débiteurs : le solde ne peut pas devenir positif
        if ($solde > 0) {
            $dbHandler->closeConnection(false);
            return new  ErrorObj(ERR_CPTE_DEB_POS, $cpte); // "Compte $cpte debiteur va devenir positif !"
        }
    } else if ($sens_cpte == 2) {
        //cas des comptes naturellement créditeurs : le solde ne peut pas devenir négatif
        if ($solde < 0) {
            $dbHandler->closeConnection(false);
            return new  ErrorObj(ERR_CPTE_CRED_NEG, $cpte); // "Le compte $cpte crediteur va devenir negatif !"
        }
    }

    /* On ne mouvemente pas un compte centralisateur
    if (isCentralisateur($cpte))
      {
        $dbHandler->closeConnection(false);
        return new  ErrorObj(ERR_CPT_CENTRALISE, "Compte $cpte"); // "Tentative de mouvementer le compte centralisateur $cpte"
      }
    */

    // Vérifie que le mouvement a bien lieu dans la meme devise
    if ($devise_cpte != $devise) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (setSoldeComptable) : Tentative de mouvementer le compte dans une autre devise\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction setSoldeComptable : Tentative de mouvementer le compte dans une autre devise"));
    }

    //mettre a jour solde courant et solde centralise
    $sql = "UPDATE ad_cpt_comptable ";
    $sql .= "SET solde = $solde ";
    $sql .= "WHERE id_ag=$global_id_agence AND num_cpte_comptable = '$cpte';";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (setSoldeComptable) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction setSoldeComptable : $sql"));
    }


    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}
function getCreditProductID ($id_agence)
// Renvoie le num de produit référençant les comptes de crédit
{
    global $dbHandler;
    $db = $dbHandler->openConnection();
    // Récupération du n° de produit d'épargne utilisé par l'agence pour les comptes de crédit
    $sql = "SELECT id_prod_cpte_credit FROM ad_agc WHERE id_ag = $id_agence;"; // Recherche l'état du client
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (setSoldeComptable) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getCreditProductID : $sql"));
    }
    $tmpRow = $result->fetchrow();
    $id_prod = $tmpRow[0];
    $dbHandler->closeConnection(true);
    return $id_prod;
}
/**
 * Retourner le solde du compte client
 * @param $id_cpte
 * @return mixed
 */
function getSoldeCpte($id_cpte)
{
    global $dbHandler;
    global $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT solde ";
    $sql .= "FROM ad_cpt ";
    $sql .= "WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte ";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getSoldeCpte) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getSoldeCpte : $sql"));
    }

    $row = $result->fetchrow();

    $solde = $row[0];

    $dbHandler->closeConnection(true);
    return $solde;
}
/**
 * recupMontant Renvoie en format numérique un string contenant un montant formaté (sans le libellé de la devise)
 *
 * @param str $montant La chaine contenant le montant
 * @access public
 * @return double Le montant sous format numérique
 */
function recupMontant($montant) {
    global $mnt_sep_mil;
    global $mnt_sep_dec;

    if ($montant == "")
        return NULL;

    // Il faut transformer les blancs insécables en blancs simples, pour retrouver les bon séparateurs.
    // C'est donc ici le premier " " qui est un blanc insécable !
    $montant = mb_ereg_replace(" ", " ", $montant);
    $montant = str_replace($mnt_sep_mil, "", $montant);
    $montant = str_replace($mnt_sep_dec, ".", $montant);
    return doubleval($montant);
}
/**
 * Fonction pour verifier si transaction/ mouvement est relié au IAP/IAR
 * PARAM : id_ecriture
 * RETURN : 1 si false aucun/ 2 true si IAR/ 3 true si IAP
 */
function is_Mouvement_IAR_IAP($id_ecriture)
{
    global $dbHandler, $global_id_agence;
    $isIAPIAR = 1;

    $db = $dbHandler->openConnection();

    $getCompteIAR = get_calcInt_cpteInt(false, true, null);
    $getCompteIAP = getCompteIAP();

    if ($getCompteIAR != '' || $getCompteIAR != null) { //IAR
        $sql_IAR="SELECT id FROM ad_calc_int_recevoir_his WHERE (id_ecriture_calc = $id_ecriture OR id_ecriture_reprise = $id_ecriture) AND  id_ag= numagc()";
        $result_IAR = $db->query($sql_IAR);
        if (DB::isError($result_IAR)) {
            $dbHandler->closeConnection(false);
//            echo "Erreur dans la fonction (is_Mouvement_IAR_IAP) instruction SQL --> $sql_IAR\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction is_Mouvement_IAR_IAP : $sql_IAR"));
        }
        if ($result_IAR->numRows() > 0) {
            $isIAPIAR = 2;
        }
    }

    if ($getCompteIAP != '' || $getCompteIAP != null) { //IAP
        $sql_IAP="SELECT id FROM ad_calc_int_paye_his WHERE (id_ecriture_calc = $id_ecriture OR id_ecriture_reprise = $id_ecriture) AND  id_ag= numagc()";
        $result_IAP = $db->query($sql_IAP);
        if (DB::isError($result_IAP)) {
            $dbHandler->closeConnection(false);
//            echo "Erreur dans la fonction (is_Mouvement_IAR_IAP) instruction SQL --> $sql_IAP\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction is_Mouvement_IAR_IAP : $sql_IAP"));
        }
        if ($result_IAP->numRows() > 0) {
            $isIAPIAR = 3;
        }
    }

    $dbHandler->closeConnection(true);

    return $isIAPIAR;

}
/**
 * Fonction pour verifier si le montant, relié a une reprise IAP, est non arrondie
 * PARAM : id_his
 * RETURN : BOOLEAN $hasDecimal
 */
function hasDecimalMntRepriseIAP($id_his)
{
    global $dbHandler, $global_id_agence;
    $hasDecimal = false;
    $mntIAP = -1;
    //$devise = '';

    $db = $dbHandler->openConnection();

    $get_CompteIAP = getCompteIAP();

    if ($get_CompteIAP != '' || $get_CompteIAP != null) {
        $sql_IAP="SELECT m.montant, m.devise FROM ad_ecriture e INNER JOIN ad_mouvement m ON e.id_ecriture = m.id_ecriture WHERE e.type_operation = 40 AND e.id_his = $id_his AND m.sens = 'd' AND m.compte IN (SELECT cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc())";

        $result_IAP = $db->query($sql_IAP);
        if (DB::isError($result_IAP)) {
            $dbHandler->closeConnection(false);
//            echo "Erreur dans la fonction (is_Mouvement_IAR_IAP) instruction SQL --> $sql_IAP\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction hasDecimalMntRepriseIAP : $result_IAP"));
        }
        if ($result_IAP->numRows() > 0) {
            $valIAP = $result_IAP->fetchrow();
            $mntIAP = $valIAP[0];
            //$devise = $valIAP[1];
            $mntIAP_Arrondie = ROUND($mntIAP);
            $diff = abs($mntIAP - $mntIAP_Arrondie);
            if ($diff > 0){
                $hasDecimal = true;
            }
        }
    }

    $dbHandler->closeConnection(true);

    return $hasDecimal;

}
function isMSQEnabled(){
    global $MSQ_ENABLED;

    is_null($MSQ_ENABLED) ? $conditionMsq = false : $conditionMsq = true;
    return $conditionMsq;
}
/**
 * Fonction qui renvoie les opérations comptables dont il faut prélever le frais forfaitaire transactionnel SMS
 * @param null $prelev_frais
 * @return array
 */
function getListeTypeOptPourPreleveFraisSMS($prelev_frais = null) {
    global $dbHandler;
    $db = $dbHandler->openConnection();

    $sql = "select type_opt from adsys_param_mouvement  where id_ag = numagc() and deleted = 'f' ";

    if ($prelev_frais != null){
        $sql .= " and preleve_frais = '$prelev_frais'";
    }


    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getListeTypeOptPourPreleveFraisSMS) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getListeTypeOptPourPreleveFraisSMS : $sql"));
    }
    $listeOpt = array ();
    while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC))
        array_push($listeOpt, $tmprow);
    $dbHandler->closeConnection(true);
    return $listeOpt;

}
/**
 * Récupère les infos d'une tarification
 *
 * @return array un tableau associatif avec les infos sur la tarification
 */
function getTarificationDatas($typeFrais) {
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM adsys_tarification WHERE id_ag=".$global_id_agence." AND statut='t' AND type_de_frais = '".$typeFrais."' AND ((date(date_debut_validite) IS NULL AND date(date_fin_validite) IS NULL) OR (date(date_debut_validite) <= date(NOW()) AND date(date_fin_validite) IS NULL) OR (date(date_debut_validite) IS NULL AND date(date_fin_validite) >= date(NOW())) OR (date(date_debut_validite) <= date(NOW()) AND date(date_fin_validite) >= date(NOW()))) ORDER BY date_creation DESC";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getTarificationDatas) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getTarificationDatas : $sql"));
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $datas = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return $datas;
}
/*
 * Check if the id_cpte is related to a client subscribed to SMS service
*/
function checkClientAbonnement($cpte_interne_cli){
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = sprintf(
        "SELECT cli.id_client
            FROM ad_cpt cpt
            JOIN ad_cli cli ON cpt.id_titulaire = cli.id_client
            JOIN ad_abonnement a ON cli.id_client = a.id_client
            WHERE a.id_ag = %d
            AND cpt.id_cpte = %s
            AND a.id_service = 1
            AND a.deleted = FALSE",
        $global_id_agence, $cpte_interne_cli
    );

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (checkClientAbonnement) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction checkClientAbonnement : $sql"));
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return $DATAS;
}
/**
 * Type opération frais forfaitaire du service SMS
 * Check if type opération 188 has a num_cpte on the credit side (sens 'c')
 */
function checkTypeOperationFraisSMSsensCredit($typeOperation)
{
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = $typeOperation AND id_ag = $global_id_agence AND sens = 'c'";
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (checkTypeOperationFraisSMSsensCredit) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction checkTypeOperationFraisSMSsensCredit : $sql"));
    }

    $rows = $result->fetchrow(DB_FETCHMODE_ASSOC);

    if(!isset($rows['num_cpte'])){
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (checkTypeOperationFraisSMSsensCredit) : L'opération $typeOperation n'a pas de compte associer paramétrer\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction checkTypeOperationFraisSMSsensCredit : L'opération $typeOperation n'a pas de compte associer paramétrer"));
    }
    else {
        $dbHandler->closeConnection(true);
//        echo "L'opération a réussi sans erreur";
        return new ErrorObj (NO_ERR, _("L'opération a réussi sans erreur"));
    }
}
/**
 * Renvoie un tableau associatif avec toutes les données du compte
 *
 * Les données retournées sont une synthèse cumulative de celles du produit et celles du compte lui-même,
 * en donnant la priorité aux données venant du produit.
 *
 * @param int $id_cpte L'identifiant du compte.
 * @return array NULL si le compte n'existe pas, le tableau des données sinon.
 */
function getAccountDatas($id_cpte) {
    global $global_id_agence, $erreur;

    if(($id_cpte == null) or ($id_cpte == '')){
//        echo "Erreur dans la fonction (getAccountDatas) : Le numéro du compte n'est pas renseigné\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction getAccountDatas : Le numéro du compte n'est pas renseigné"));
    }else {
        // Attention ! Laisser les tables dans cet ordre car devise apparait 2 fois et c'est celui de ad_cpt qui a précédence
        $sql = "SELECT * FROM adsys_produit_epargne p, ad_cpt c WHERE c.id_ag = $global_id_agence AND c.id_ag = p.id_ag AND c.id_prod = p.id AND c.id_cpte = '$id_cpte'";
    }
    $result = executeDirectQuery($sql);
    if ($result->errCode != NO_ERR) {
        return $result;
    } else {
        if (empty($result->param)) {
            return NULL;
        } else {
            return $result->param[0];
        }
    }
}
/**
 * Renvoie le solde disponible sur un compte client en tenant compte de
 *  - Compte bloqué => solde = 0
 *  - Retrait unique => solde = 0
 *  - Montant bloqué
 *  - Montant minimum
 *  - Découvert maximum autorisé
 *  - Si solde dispo négatif alors solde disponible = 0
 * @param int $id_cpte Numéro du compte
 * @return float Solde disponible
 */
function getSoldeDisponible($id_cpte) {
    // Remplir 2 tableaux avec toutes les infos sur le compte et le produit associé
    $InfoCpte = getAccountDatas($id_cpte);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

    if ($InfoProduit["retrait_unique"] == 't' || $InfoCpte["etat_cpte"] == 3)
        $solde_dispo = 0;
    else
        $solde_dispo = $InfoCpte["solde"] - $InfoCpte["mnt_bloq"] - $InfoCpte["mnt_min_cpte"] + $InfoCpte["decouvert_max"] - $InfoCpte["mnt_bloq_cre"];

    if ($solde_dispo < 0)
        $solde_dispo = 0;

    return $solde_dispo;
}
/**
 * Retourne les caractéristiques d'un produit d'épargne
 * @param int $a_id_produit L'identifiant du produit d'épargne
 * @return array Un tableau associatif avec les caractéristiques du produit, NULL si pas de produit trouvé.
 */
function getProdEpargne($a_id_produit) {
    global $global_id_agence;
    $sql = "SELECT * FROM adsys_produit_epargne WHERE id_ag = $global_id_agence AND id = '$a_id_produit'";
    $result = executeDirectQuery($sql, FALSE);
    if ($result->errCode != NO_ERR) {
        return $result;
    } else {
        if (empty($result->param)) {
            return NULL;
        } else {
            return $result->param[0];
        }
    }
}
/**
 * @description: Calcul le niveau d'un compte
 * @param text Numéro d'un Compte comptables
 * @return int le niveau du compte comptable
 */
function getNiveauCompte($compte) {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    //On commence par récupérer le numéro de lot
    $sql = "SELECT getNiveau('$compte',$global_id_agence) ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getNiveauCompte) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getNiveauCompte : ".$result->getMessage()));
    }
    $row = $result->fetchrow();
    $niveau = $row[0];
    $dbHandler->closeConnection(true);
    return $niveau;

}
function isEcritureAttente($num_cpte) {
    //Verifie s'il y des ecritures en attente sur le compte

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();
    $sql = "SELECT count(compte) FROM ad_brouillard where id_ag=$global_id_agence and compte ='$num_cpte' ";
    $result = $db->query($sql);
    $dbHandler->closeConnection(true);
    if (DB::isError($result)) {
//        echo "Erreur dans la fonction (isEcritureAttente) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction isEcritureAttente : $sql"));
    }

    $row = $result->fetchrow();

    if ($row[0] > 0)
        return true;
    else
        return false;
}
function getAgenceDatas($id_ag) {
    /* Cette fonction renvoie toutes les informations relatives à l'agence dont lID est $id_agence
     IN : l'ID de l'agence
     OUT: un tableau associatif avec les infos sur l'agence si tout va bien
          NULL si l'agence n'existe pas
          Die si erreur de la DB
    */
    global $dbHandler, $global_id_agence;

    if ($id_ag == NULL)
        $id_ag = $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ad_agc";
    if ($id_ag != NULL)
        $sql .= " WHERE id_ag = $id_ag";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getAgenceDatas) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getAgenceDatas : $sql"));
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0)
        return NULL;

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
    return $DATAS;
}
/* ************************** Gestion des Exercices ****************************************/
function getExercicesComptables($id_exo=NULL) {
    /*

     Fonction renvoyant l'ensemble des exercices comptables
     IN : <néant>
     OUT: array ( index => array(infos exercice))

    */

    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ad_exercices_compta where id_ag=$global_id_agence ";
    if ($id_exo)
        $sql .= " AND id_exo_compta=$id_exo ";
    $sql .= "ORDER BY id_exo_compta";

    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getExercicesComptables) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getExercicesComptables : $sql"));
    }

    $exos = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        array_push($exos, $row);

    $dbHandler->closeConnection(true);
    return $exos;
}
/**********
 * Fonction qui calcule pour un compte le solde des mouvements de l'exerciece en cours
 * utile pour la répartition des soldes des comptes de gestions centralisateurs lors de la création de sous-comptes
 * @author Papa
 * @since 2.2
 * @param txt $compte Le numéro du compte comptable
 * @return int Le solde des mouvements du compte dans l'exercice en cours
 */
function calculeSoldeCpteGestion($compte) {
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();
    $solde=0;

    /* Exercice en cours */
    $AG = getAgenceDatas($global_id_agence );
    $id_exo_encours = $AG["exercice"];

    $infos_exo_encours = getExercicesComptables($id_exo_encours);

    /* Mouvements au débit dans l'exercie en cours */
    $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE b.id_ag=$global_id_agence and a.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN date('".$infos_exo_encours[0]['date_deb_exo']."') AND date('".$infos_exo_encours[0]['date_fin_exo']."') AND sens = 'd' ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (calculeSoldeCpteGestion) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction calculeSoldeCpteGestion : $sql"));
    }
    $row = $result->fetchrow();
    $total_debit = $row[0];

    /* Mouvements au crédit dans l'exercie en cours */
    $sql="SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ag=$global_id_agence and b.id_ag=$global_id_agence and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN date('".$infos_exo_encours[0]['date_deb_exo']."') AND date('".$infos_exo_encours[0]['date_fin_exo']."') AND sens = 'c'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (calculeSoldeCpteGestion) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction calculeSoldeCpteGestion : $sql"));
    }
    $row = $result->fetchrow();
    $total_credit = $row[0];

    $solde = $total_credit - $total_debit;

    $dbHandler->closeConnection(true);
    return $solde;
}
function getNbreSousComptesComptables($num_cpte,$a_isActif=NULL) {
    /*

     Fonction renvoyant le nombre de sous comptes d'un compte principal définis dans le plan comptable
     IN : numero du compte

     OUT: nombre de sous compte   */

    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT count(num_cpte_comptable) FROM ad_cpt_comptable where id_ag=$global_id_agence and  num_cpte_comptable like '$num_cpte.%' ";
    if($a_isActif != NULL){
        $sql .=" AND is_actif='".$a_isActif."' ";

    }

    $result = $db->query($sql);
    $dbHandler->closeConnection(true);
    if (DB::isError($result)) {
//        echo "Erreur dans la fonction (getNbreSousComptesComptables) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getNbreSousComptesComptables : $sql"));
    }

    $row = $result->fetchrow();

    return $row[0];
}
function getInfosJournalCptie($id_jou=NULL,$num_cpte=NULL) {
    // renvoie les donnes de la table ad_journaux_cptie
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql="SELECT *  FROM ad_journaux_cptie where id_ag=$global_id_agence ";
    if ($id_jou != NULL) {
        $sql .= "AND id_jou=$id_jou";
        if ($num_cpte != NULL)
            $sql .= " and (num_cpte_comptable='$num_cpte' OR num_cpte_comptable like '$num_cpte.%')";
    } else
        if ($num_cpte != NULL)
            $sql .= "AND num_cpte_comptable='$num_cpte' OR num_cpte_comptable like '$num_cpte.%'";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getInfosJournalCptie) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getInfosJournalCptie : $sql"));
    }

    $cptie = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        array_push($cptie,$row);

    $dbHandler->closeConnection(true);
    return $cptie;
}
/**
 * Renvoie la liste des sous-comptes d'un compte comptable
 * @param text $compte Numéro du compte comptable
 * @param bool $recusrif true si on désire ontenir tous les sous comptes récursivement
 * @param text $whereSousCpte condition de selections des sous comptes
 * @return Array List edes sous comptes
 */
function getSousComptes($compte, $recursif=true,$whereSousCpte) { //$condSousComptes
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $liste_sous_comptes=array();

    $sql ="SELECT * FROM ad_cpt_comptable WHERE cpte_centralise ='".$compte."' AND id_ag = ".$global_id_agence;
    $sql.=$whereSousCpte;
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getSousComptes) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getSousComptes : $sql"));
    }

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        // ajoute le compte dans la liste
        $liste_sous_comptes[$row['num_cpte_comptable']] = $row;

        // ajouter les sous-comptes du sous-compte par récursivité si récursif
        if ($recursif)
            $liste_sous_comptes = array_merge($liste_sous_comptes,getSousComptes($row['num_cpte_comptable'], true,$whereSousCpte));
    }

    $dbHandler->closeConnection(true);
    return $liste_sous_comptes;
}
function supJournalCptie($id_jou,$id_compte) {
    // supprime des comptes de contrepartie d'un journal

    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    // le compte et ses sous-comptes qui sont de la contrepartie
    $cptie=getInfosJournalCptie($id_jou,$id_compte);
    if (isset($cptie))
        foreach($cptie as $row) {
            $id=$row["id_jou"];
            $num=$row["num_cpte_comptable"];

            $sql="delete from ad_journaux_cptie where id_ag=$global_id_agence and id_jou=$id and num_cpte_comptable='$num'";
            $result = $db->query($sql);
            if (DB::isError($result)) {
                $dbHandler->closeConnection(false);
//                echo "Erreur dans la fonction (supJournalCptie) instruction SQL --> $sql\n";
                return new ErrorObj (ERR_DB_SQL, _("Fonction supJournalCptie : $sql"));
            }
        }

    $dbHandler->closeConnection(true);
    return true;
}
function ajoutJournalCptie($id_jou,$compte) {
    // Ajout le compte $compte et ses sous-comptes dans la contrepartie du journal dont l'id est donné

    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    // si le compte ou les sous-comptes sont de la contrepartie, les supprimer d'abord
    $sup=supJournalCptie($id_jou,$compte);

    // Récupération de tous les comptes dérivés de ce compte
    $sous_comptes=getSousComptes($compte, true);

    // Ajout du compte dans la contrepartie du journal
    $sql="INSERT INTO ad_journaux_cptie Values($id_jou,'$compte',$global_id_agence)";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (ajoutJournalCptie) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction ajoutJournalCptie : $sql"));
    }

    // Ajout des sous-comptes dans la contrepartie du journal
    if (isset($sous_comptes))
        foreach($sous_comptes as $key=>$value) {
            // récupère informations du sous-compte
            $param["num_cpte_comptable"]=$key;
            $cpte=getComptesComptables($param);

            // vérifie si le sous-compte n'est pas compte principal d'un journal
            if ($cpte[$key]["cpte_princ_jou"]=='t') {
                $dbHandler->closeConnection(false);
                return new ErrorOBj(ERR_DEJA_PRINC_JOURNAL,$key);
            }

            // ajout du sous-compte dans la contrepartie
            $sql="INSERT INTO ad_journaux_cptie Values($id_jou,'$key',$global_id_agence)";
            $result = $db->query($sql);
            if (DB::isError($result)) {
                $dbHandler->closeConnection(false);
//                echo "Erreur dans la fonction (ajoutJournalCptie) instruction SQL --> $sql\n";
                return new ErrorObj (ERR_DB_SQL, _("Fonction ajoutJournalCptie : $sql"));
            }
        }

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);

}
function ajoutSousCompteComptable($compte_centralisateur,$liste_sous_comptes, $solde_reparti=NULL) {
    /*
       Fonction qui ajoute des sous-comptes à un compte comptable

       IN: - $compte_centralisateur = le numéro du compte auquel on veut ajouter des sous-comptes
           - $liste_sous_comptes = tableau contenant la liste des sous-comptes au format
             array (n° cpte => array(n° cpte, libel, solde de départ, devise))

       OUT : Objet ErrorObj
    */
    global $dbHandler, $global_nom_login,$global_id_agence;

    $db = $dbHandler->openConnection();
    $global_id_agence=getNumAgence();
    //Recupèration des infos du compte centralisateur
    $param["num_cpte_comptable"]=$compte_centralisateur;
    $infocptecentralise = getComptesComptables($param);

    // Verifier s'il n y a pas, pour le compte centralisateur, des ecritures en attente dans le brouillard
    $ecriture_attente = isEcritureAttente($compte_centralisateur);
    if ($ecriture_attente == true) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPT_ECRITURE_EXIST, $compte_centralisateur);
    }

    // Récupère le nombre de sous-comptes du compte centralisateur
    $nbre_souscompte = getNbreSousComptesComptables($compte_centralisateur) ;

    // Vérifie si c'est la première création de sous-comptes pour le compte centralisateur
    if ($nbre_souscompte == 0 ) {
        // première création, Vérifier alors que solde du compte centralisateur est complétement réparti entre les sous-comptes

        $soldesc=0; // la somme des soldes des sous-comptes
        if (isset($liste_sous_comptes))
            foreach($liste_sous_comptes as $key=>$value)
                $soldesc = $soldesc + abs($value["solde"]);
        if ($solde_reparti == NULL) {
            if ($infocptecentralise[$compte_centralisateur]['compart_cpte'] == 3 OR $infocptecentralise[$compte_centralisateur]['compart_cpte'] == 4) {
                $solde_reparti = calculeSoldeCpteGestion($compte_centralisateur);
            } else {
                $solde_reparti = $infocptecentralise[$compte_centralisateur]['solde'];
            }
        }

        //comparaison entre la sommme des soldes et le solde du compte centralisateur
        if ( abs($solde_reparti) != $soldesc) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_SOLDE_MAL_REPARTI, $compte_centralisateur);
        }
    }
    // Ajout des sous comptes
    if (isset($liste_sous_comptes)) // parcours de la liste des sous-comptes
        foreach($liste_sous_comptes as $key=>$value)
            if ($key!='') {
                // Vérifier que le sous-compte n'existe pas dans la DB
                $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag=$global_id_agence and num_cpte_comptable='$key';";
                // FIXME : Utiliser getComptesComptables ?
                $result = $db->query($sql);
                if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
//                    echo "Erreur dans la fonction (ajoutSousCompteComptable) instruction SQL --> $sql\n";
                    return new ErrorObj (ERR_DB_SQL, _("Fonction ajoutSousCompteComptable : $sql"));
                }

                //if compte exist deja on modifier le procedure
                /*
                  if ($result->numRows() > 0) {
                    $dbHandler->closeConnection(false);
                     return new ErrorObj(ERR_CPT_EXIST, $key);
                 }*/


                // Héritage automatique de la devise du compte centralisateur
                if (!isset($value["devise"]) && isset($infocptecentralise[$compte_centralisateur]["devise"]))
                    $value["devise"] = $infocptecentralise[$compte_centralisateur]["devise"];

                // Vérfieir si la devise du sous-compte n'est pas différente de la devise du compte centralisateur
                if ($infocptecentralise[$compte_centralisateur]["devise"] != NULL && $infocptecentralise[$compte_centralisateur]["devise"] != $value["devise"]) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj(ERR_DEV_DIFF_CPT_CENTR, $value["devise"]);
                }
                // Construction de la requête d'insertion de sous-compte
                $DATA = array();

                // Vérifier si la devise du sous-compte n'est pas différente de la devise du compte de provision
                if ( $value['cpte_provision'] != "[Aucun]" &&  $value["cpte_provision"] != NULL) {
                    $infoscpteprov=getComptesComptables(array("num_cpte_comptable"=>$value["cpte_provision"]));
                    if($infoscpteprov[$value["cpte_provision"] ]["devise"] != $value["devise"] ) {
                        $dbHandler->closeConnection(false);
                        return new ErrorObj(ERR_DEV_DIFF_CPT_PROV, $value["devise"]);
                    }
                    $DATA["cpte_provision"] =$value["cpte_provision"];
                } else {
                    $DATA["cpte_provision"] =NULL;
                }

                $DATA["num_cpte_comptable"] = $value["num_cpte_comptable"];
                $DATA["libel_cpte_comptable"] = $value["libel_cpte_comptable"];
                if ($value["compart_cpte"]!='') // si le compartiment n'edst pas renseigné alors il l'hérite du compte père
                    $DATA["compart_cpte"] = $value["compart_cpte"];
                else
                    $DATA["compart_cpte"] = $infocptecentralise[$compte_centralisateur]["compart_cpte"];

                if ($value["sens_cpte"]!='') // si le sens n'est pas renseigné alors il l'hérite du compte père
                    $DATA["sens_cpte"] = $value["sens_cpte"];
                else
                    $DATA["sens_cpte"] = $infocptecentralise[$compte_centralisateur]["sens_cpte"];

                $DATA["classe_compta"] = $infocptecentralise[$compte_centralisateur]["classe_compta"];
                //$DATA["cpte_centralise"] = $compte_centralisateur;

                if ($infocptecentralise[$compte_centralisateur]['cpte_princ_jou']=='t')
                    $DATA["cpte_princ_jou"] = 't';
                else
                    $DATA["cpte_princ_jou"] = 'f';

                $DATA["solde"] = 0;

                $now = date("Y-m-d");
                $DATA["date_ouvert"] = $now;
                $DATA["etat_cpte"] = 1;
                $DATA["id_ag"] = $global_id_agence;
                $DATA["devise"] = $value["devise"];



                // pour cas ou  le sous compte exist deja  on va faire un update
                $DATA["is_actif"] = TRUE;
                $Where = array("num_cpte_comptable" => $key,'id_ag'=> $global_id_agence,'is_actif'=>'FALSE');

                if ($result->numRows() > 0){
                    $sql = buildUpdateQuery("ad_cpt_comptable", $DATA, $Where);

                }
                // else insert normal
                else{

                    $sql = buildInsertQuery("ad_cpt_comptable",$DATA);

                }
                // Insertion dans la DB
                $result = $db->query($sql);
                if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
//                    echo "Erreur dans la fonction (ajoutSousCompteComptable) instruction SQL --> $sql\n";
                    return new ErrorObj (ERR_DB_SQL, _("Fonction ajoutSousCompteComptable : $sql"));
                }

                //Recherche des contrepartie pour le compte centralisateur
                $cpt_cptie=getInfosJournalCptie(NULL,$compte_centralisateur);
                if(is_array($cpt_cptie)){
                    foreach($cpt_cptie as $key1=>$DATA){
                        foreach($liste_sous_comptes as $key2=>$value2){
                            //ajout dans le journal des contreparties
                            // ajoutjournalCptie verifie si il y a une entre dans la table ad_cpt_comptable avec le nuvo num_cpte_comptable
                            $myErr=ajoutJournalCptie($DATA["id_jou"], $value2["num_cpte_comptable"]);
                            if ($myErr->errCode != NO_ERR) {
                                $html_err = new HTML_erreur(_("Echec création journal. "));
                                $html_err->setMessage(_("Erreur")." : ".$myErr->param);
                                $html_err->addButton("BUTTON_OK", 'Jou-6');
                                $html_err->buildHTML();
//                                echo $html_err->HTML_code;
                                return new ErrorObj (ERR_GENERIQUE, _("Fonction ajoutSousCompteComptable : ".$html_err->HTML_code));
                            }
                        }
                    }
                }
                // Insertion dans la DB
                /* $result = $db->query($sql);
                /* if (DB::isError($result)) {
                     $dbHandler->closeConnection(false);
                     signalErreur(__FILE__,__LINE__,__FUNCTION__);
                 }*/

                if ( abs($solde_reparti) != 0 && ($value['solde'] != 0)) {
                    // Passage des écritures comptables
                    $comptable = array();
                    $cptes_substitue = array();
                    $cptes_substitue["cpta"] = array();
                    if ($solde_reparti < 0 ) {
                        //crédit du compte centralisateur par le débit d'un sous-compte
                        $cptes_substitue["cpta"]["debit"] = $key;
                        $cptes_substitue["cpta"]["credit"] = $compte_centralisateur;
                    } else {
                        //débit d'un sous compte par le credit du compte centralisateur
                        $cptes_substitue["cpta"]["debit"] = $compte_centralisateur;
                        $cptes_substitue["cpta"]["credit"] = $key;
                    }
                    $myErr = passageEcrituresComptablesAuto(1003, abs($value["solde"]), $comptable, $cptes_substitue, $value["devise"]);
                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    }
                    $erreur=ajout_historique(410, NULL, _("Virement solde compte principal"), $global_nom_login, date("r"), $comptable);
                    if ($erreur->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $erreur;
                    }
                }
            }

    // Mise à jour du champs compte centralisateur des sous-compte
    if (isset($liste_sous_comptes)) // parcours de la liste des sous-comptes
        foreach($liste_sous_comptes as $key=>$value)
            if ($key!='') {
                $niveau = getNiveauCompte($compte_centralisateur) + 1;
                $sql = "UPDATE ad_cpt_comptable set cpte_centralise='$compte_centralisateur', niveau = $niveau WHERE id_ag=$global_id_agence AND num_cpte_comptable = '$key'";
                $result = $db->query($sql);
                if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
//                    echo "Erreur dans la fonction (ajoutSousCompteComptable) instruction SQL --> $sql\n";
                    return new ErrorObj (ERR_DB_SQL, _("Fonction ajoutSousCompteComptable : $sql"));
                }
            }

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);

}
/**
 * Fonction renvoyant les informations sur les comptes comptables définis dans le plan comptable
 * @since 1.0
 * @param array $fields_values Array permettant de construire une clause WHERE pour le SELECT.
 * Si argument est NULL, on renvoie tous les comptes. L'array a la forme (fieldname=>value recherchée).
 * @return array On renvoie un tableau de la forme (numéro compte => infos compte)
 */
function getComptesComptables($fields_values=NULL, $niveau=NULL,$date_modif=NULL) {
    global $dbHandler,$global_id_agence;

    //vérifier qu'on reçoit bien un array
    if (($fields_values != NULL) && (! is_array($fields_values)))
        return new ErrorObj (ERR_GENERIQUE, _("Fonction ajoutSousCompteComptable : Mauvais argument dans l'appel de la fonction"));
    $db = $dbHandler->openConnection();
    if($date_modif == NULL){
        $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag = $global_id_agence AND is_actif = 't' AND ";
    }else{
        $date_mod= php2pg($date_modif);
        $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag = $global_id_agence AND ((is_actif = 't') OR (is_actif = 'f' AND date_modif > '$date_mod')) AND ";
    }
    if (isset($fields_values)) {

        foreach ($fields_values as $key => $value)
            if (($value == '') or ($value == NULL))
                $sql .= "$key IS NULL AND ";
            else
                $sql .= "$key = '$value' AND ";

    }
    $sql = substr($sql, 0, -4);
    $sql .= "ORDER BY id_ag, num_cpte_comptable ASC";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getComptesComptables) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getComptesComptables : $sql"));
    }

    $cptes = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        if (getNiveauCompte($row["num_cpte_comptable"]) <= $niveau && $niveau != NULL) {
            $cptes[$row["num_cpte_comptable"]] = $row;
        }
        elseif($niveau == NULL) {
            $cptes[$row["num_cpte_comptable"]] = $row;
        }


    $dbHandler->closeConnection(true);

    return $cptes;
}
function getBaseAccountID ($id_client) {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();
    $sql = "SELECT id_cpte_base FROM ad_cli WHERE id_ag = $global_id_agence AND id_client = $id_client";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
//        echo "Erreur dans la fonction (getBaseAccountID) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getComptesComptables : ".$result->getMessage()));
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0)
        return NULL;
    $tmpRow = $result->fetchrow();
    return $tmpRow[0];
}
function preleveFraisAbonnement($type_frais, $id_client, $type_oper = 180, $montant_transaction = 0, $type_fonction = null, $id_doss = null, $source = 2) {
    global $dbHandler, $global_nom_login, $global_id_client, $global_id_agence, $global_monnaie;

    $comptable = array();
    if(is_null($type_fonction)) $type_fonction = 12;

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
            $id_cpte_source = getBaseAccountID($id_client);

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
                if ($type_frais == 'SMS_MTH' || $type_frais == 'ESTAT_MTH' || $type_frais == 'SMS_FRAIS') {
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
/**
 * Check if array_comptable contains transaction which concerns a client which has subscribed to Mobile Banking
 * Deduct frais sms transactionnel from base account
 * @param $array_comptable
 */
function preleveFraisTransactionnelSMS(&$array_comptable, $type_function, $id_doss = NULL, $source = 2)
{
    global $adsys;
    global $global_langue_rapport;

    $type_frais = 'SMS_FRAIS';
    $type_operation = 188;
    $montant_transaction = 0;
    $arr_type_operation = array();

    $type_opt = getListeTypeOptPourPreleveFraisSMS(true);
    foreach ($type_opt as $key => $value) {
        foreach ($value as $opt => $valeur) {
            $arr_type_operation[] = $valeur;
        }
    }

    $getFrais = getTarificationDatas($type_frais);
    if(empty($getFrais)){
        return true;
    }

    if(!empty($array_comptable)){
        foreach ($array_comptable as $k => $val){
            if (isset($val['cpte_interne_cli'])){
                if (in_array($val["type_operation"], $arr_type_operation) && is_array($client = checkClientAbonnement($val['cpte_interne_cli'])) && $cpt_epargne = get_comptes_epargne($client["id_client"]) ){
                    foreach ($cpt_epargne as $id_cpte => $values){
                        if ($val['cpte_interne_cli'] == $id_cpte) {

                            $myErr = checkTypeOperationFraisSMSsensCredit($type_operation);
                            if($myErr->errCode != NO_ERR){
                                return false;
                            }

                            preleveFraisAbonnement($type_frais, $client["id_client"], $type_operation, $montant_transaction,  $type_function, $id_doss, $source);

                            //No recu will be generated as discussed with Olivier.
                            /*$InfoCpte = getAccountDatas  ($val["cpte_interne_cli"]);//id_cpte as parameter
                            $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
                            $infos = get_compte_epargne_info($val["cpte_interne_cli"]);//id_cpte as parameter

                            print_recu_frais_transactionnel_SMS($client["id_client"], ++$id_his, $mnt, $global_langue_rapport, $InfoProduit, $infos);*/
                        }
                    }
                }
            }
        }
    }


    return true;
}
function setSoldeCpteCli($id_cpte, $sens, $montant, $devise, $isIAR=false) {
    /*

      Fonction qui met à jour le solde d'un compte client dans ad_cpt suite à une opération financière.
      Il faut vérifier que le solde ne peut pas être négatif sauf pour un compte dont le id produit est celui du type de compte de
      crédit.
      Important : on ne vérifie pes les soldes mini, c'est à l'appelant de le faire

       IN : $id_cpte = identifiant dans ad_cpt
            $sens = SENS_DEBIT => le compte interne est débité (signe de l'opération est -)
                    SENS_CREDIT => le compte interne est crédité (signe de l'opération est +)
            $montant = Montant du transfert sur le compte interne

       OUT : Objet Erreur

      */

    global $dbHandler;
    global $global_id_agence;

    $db = $dbHandler->openConnection();

    $id_prod_credit = getCreditProductID ($global_id_agence);

    $sql = "SELECT solde, id_prod, devise ,mnt_bloq,mnt_min_cpte,decouvert_max ";
    $sql .= "FROM ad_cpt ";
    $sql .= "WHERE id_ag = $global_id_agence AND id_cpte = $id_cpte ";
    $sql .= "FOR UPDATE OF ad_cpt;";


    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (setSoldeCpteCli) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction setSoldeCpteCli : $sql"));
    }
    //FIXME : vérifier si on a trouvé quelque chose

    $row = $result->fetchrow();

    $solde = $row[0];
    //REL-101 : on verifie si le montant du compte client contient les deicmaux ex. 50000.536
    $hasDecimalSoldeCpte = hasDecimalMontant($solde);

    $ProdCpte = $row[1];
    $devise_cpte = $row[2];

    // #514+PP165 : Arrondir le montant a passer :
    $montantIARnonArrondie = $montant; //Montant temporaire pour garder le montant non arrondie IAR si c'est une operation 375/20 il sera utile sinon servira a rien REL-80
    $soldeCompte_ini = $solde; //On garde le montant initial recuperé du compte client pour la difference entre ce montant et le montant mouvementé REL-80

    $montant = arrondiMonnaiePrecision($montant, $devise);

    $cpte_int_couru = get_calcInt_cpteInt(false, true, null);
    if ((!is_null($cpte_int_couru) && $isIAR === true) || $hasDecimalSoldeCpte === true){ //Exception pour operation Remboursement IAR depuis compte client REL-80 + REL-101
        $montant = $montantIARnonArrondie; //Recuperation du montant non arrondie IAR
    }

    if ($sens == SENS_DEBIT) {
        $solde = $solde - $montant;
    }
    elseif ($sens == SENS_CREDIT) {
        $solde = $solde + $montant;
    }

    if ((!is_null($cpte_int_couru) && $isIAR === true) || $hasDecimalSoldeCpte === true){ //Exception pour operation Remboursement IAR pour gerer proprement la difference si IAR est parametré REL-80 + REL-101
        $temp_solde = $solde;
        $abs_diff = abs($solde);
        if ($abs_diff < 1){
            $solde = arrondiMonnaiePrecision(abs($solde), $devise);
        }
        if ($abs_diff < 1 && $soldeCompte_ini <0 && $temp_solde <0){
            $solde = -1 * $solde;
        }
    }
    //$solde = round($solde, EPSILON_PRECISION);

    //verifier de quel type de compte client il s'agit : compte d'epargne ou de credit
    if ($ProdCpte ==  $id_prod_credit) {
        //Si compte de crédit, le solde doit être débiteur et ne peut devenir positif
        if ($solde > 0) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_DEB_POS, _("compte client")." $id_cpte");
        }
    } else {
        $mnt_bloq = $row[3];
        $mnt_min_cpte = $row[4];
        $decouvert_max = $row[5];
        $solde1 = $solde + $decouvert_max;
        if ($solde1 < 0) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_CRED_NEG, _("compte client")." $id_cpte");
        }
    }

    // Vérification sur la devise
    if ($devise_cpte != $devise) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (setSoldeCpteCli) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction setSoldeCpteCli : Tentative de mouvementer le compte client $id_cpte dans la devise $devise"));
    }

    //mettre à  jour le solde
    $sql = "UPDATE ad_cpt SET solde = $solde WHERE id_ag=$global_id_agence AND id_cpte=$id_cpte;";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (setSoldeCpteCli) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction setSoldeCpteCli : $sql"));
    }


    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);

}
/* ************************** Gestion des Operations ***********************************/

function getOperations($id_oper=0) {
    // Fonction renvoyant toutes les associations définies selon les opérations ou les informations concernant une opération particulière
    // IN : $id_oper = 0 ==> Renvoie toutes les opérations
    //               > 0 ==> Renvoie l'opération id_oper
    // OUT: Objet ErrorObj avec en param :
    //      Si $id_oper = 0 : array($key => array("type_operation", "libel", "cptes" = array ("sens" = array("categorie, "compte")))
    //                  > 0 : array("libel") = libellé de l'opération

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * FROM ad_cpt_ope ";
    if ($id_oper == 0)
        $sql .= "WHERE id_ag = $global_id_agence ORDER BY type_operation";
    else
        $sql .= "WHERE type_operation = $id_oper and id_ag = $global_id_agence ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
//        echo "Erreur dans la fonction (getOperations) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getOperations : $sql"));
    }

    if ($id_oper > 0) {
        if ($result->numRows() == 0) {
            // Il n'y a pas d'association pour cette opération
            $dbHandler->closeConnection(false);
            return new ErrorOBj(ERR_NO_ASSOCIATION, "L'opération $id_oper n'existe pas");
        } else {
            $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
            $dbHandler->closeConnection(true);
            return new ErrorObj(NO_ERR, array("libel" => $row["libel_ope"], "type_operation" => $row["type_operation"], "categorie_ope" => $row["categorie_ope"]));
        }
    } else {
        $OP= array();
        while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
            $sql = "SELECT * FROM ad_cpt_ope_cptes WHERE id_ag = $global_id_agence and type_operation = ".$rows["type_operation"];
            $result2 = $db->query($sql);
            if (DB::isError($result2)) {
                $dbHandler->closeConnection(false);
//                echo "Erreur dans la fonction (getOperations) instruction SQL --> $sql\n";
                return new ErrorObj (ERR_DB_SQL, _("Fonction getOperations : $sql"));
            }
            while ($row_cptes = $result2->fetchrow(DB_FETCHMODE_ASSOC)) {
                $rows["cptes"][$row_cptes["sens"]] = $row_cptes;
            }

            array_push($OP,$rows);
        }
        $dbHandler->closeConnection(true);
        return new ErrorObj(NO_ERR, $OP);
    }
}
function getLastIdOperation($array_comptable) {
    /*
      PRECONDITION :
      Prend en argument un tableau d'écritures ocmptables pour l'historique et renvoie le dernier n° d'opération (débit/crédit)

    */

    if (!is_array($array_comptable))
        return 1;

    reset($array_comptable);
    $id_max = 0;
    while (list(,$tmp) = each($array_comptable)) {
        if ($id_max < $tmp["id"])
            $id_max = $tmp['id'];
    }
    return $id_max;
}
/**
 * Fonction permettant d'afficher des informations de debugging
 *
 * Ne sera activée que si la variable globale $DEBUG est à true
 * Si le module Xdebug installé pour php, l'affichage des variables se fait avec {@see xdebug_var_dump}
 *
 * @param unknow $variable : variable à afficher
 * @param String $commentaire : Commentaire pour reconnaitre la variable affichée; Valeur par défaut NULL
 * @return null - Affiche la variable en entrée
 *
 * @author Bernard De Bois
 * @author Modifié par Stefano AMEKOUDI et Antoine Delvaux {@since version 3.0 - Sept 07}
 */
function debug($variable, $commentaire=null) {
    global $DEBUG;

    if ($DEBUG) {
        $output = "<pre>*****************************************************************************\n";
        if (function_exists('xdebug_enable')) {
            $output .= "<b>".xdebug_call_function()."</b> "._("à la ligne")." <b>".xdebug_call_line()."</b> "._("du fichier")." <b>".xdebug_call_file()."</b>\n";
        }
        if (isset($commentaire)) {
            $output .= $commentaire."\n";
        }
        echo $output."</pre>\n";
        if (function_exists('xdebug_enable')) {
            xdebug_var_dump($variable);
        } else {
            echo "<pre>\n";
            if (is_array($variable) || is_object($variable)) {
                print_r($variable);
            } else {
                var_dump($variable);
            }
            echo "\n</pre>\n";
        }
        echo "\n<pre>*****************************************************************************</pre>";
    }
}
/**
 * Vérifie si le compte $num_cpte peut être mouvementé dans la devise $devise
 * Impossibile si le compte possède déjà une devise différente de la devise $devise
 * Si le compte n'a pas de devise assignée,
 * création d'un sous-compte dans la devise désirée si ce dernier est inexistant
 * @author Thomas FASTENAKEL
 * @param text $num_cpte Numéro du compte
 * @param char(3) $devise Code ISO de la devise du mouvement
 * @return text Numéro du compte à mouvementer ou NULL si mouvement impossible
 */
function checkCptDeviseOK($num_cpte, $devise) {
    global $global_multidevise, $error;
    global $global_id_agence;
    if ($global_multidevise) {
        // Recherche des infos sur le compte
        $ACC = getComptesComptables(array("num_cpte_comptable" => $num_cpte));
        //debug($ACC,"acc");
        if (sizeof($ACC) != 1) {
//            echo "Erreur dans la fonction (checkCptDeviseOK) \n";
            return new ErrorObj (ERR_GENERIQUE, _("Fonction checkCptDeviseOK"));
        }
        $ACC = $ACC[$num_cpte];

        // Si le compte a une devise associée, alors vérifier que c'est la même que celle de l'opération
        if (isset($ACC["devise"])) {
            if ($ACC["devise"] == $devise)
                return $num_cpte;
            else {
                return NULL;
            }
        } else {
            // Chercher si le compte possède un sous-compte dans la devise renseignée
            $ACC2 = getComptesComptables(array("cpte_centralise" => $num_cpte, "devise" => $devise));
            if (count($ACC2) == 1) {
                $ACC  = array_pop($ACC2);
                return $ACC["num_cpte_comptable"];
            } else if (count($ACC2) == 0) {
                // Création du sous-compte dans la devise de l'écriture
                $sscomptes = array();
                $sscompte = array();
                $sscompte["num_cpte_comptable"] = $num_cpte.".$devise";
                $sscompte["libel_cpte_comptable"] = $ACC["libel_cpte_comptable"]."-$devise";
                $sscompte["solde"] = 0;
                $sscompte["devise"] = $devise;
                $sscomptes[$num_cpte.".$devise"] = $sscompte;

                $myErr = ajoutSousCompteComptable($num_cpte, $sscomptes);
                if ($myErr->errCode != NO_ERR) {
                    debug(sprintf(_("Problème lors de la création du sous-compte %s"),$num_cpte.$devise)." : ".$error[$myErr->errCode]);
//                    echo "Erreur dans la fonction (checkCptDeviseOK) \n";
                    return new ErrorObj (ERR_GENERIQUE, _("Fonction checkCptDeviseOK"));
                } else
                    return $num_cpte.".".$devise;
            } else
                return new ErrorObj (ERR_GENERIQUE, _("Fonction checkCptDeviseOK: ".sprintf(_("Au moins deux sous-comptes du compte %s existent dans la devise %s"),$num_cpte,$devise)));
            return $num_cpte;
        }
    } else
        return $num_cpte;
}
function is_ferie($date_jour, $date_mois, $date_annee) {
    //Le jour est-il ferié ?
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    //Recup infos
    $jour_semaine = date("w", gmmktime(0,0,0,$date_mois,$date_jour,$date_annee)); //0 = dimanche, 6 = samedi
    //Maintenant on passe au format de la table ad_fer : 1 = lundi, 7 = dimanche
    if ($jour_semaine == 0) $jour_semaine = 7;

    //SQL
    $sql = "SELECT count(*) FROM ad_fer WHERE id_ag=$global_id_agence AND ";
    //Jour semaine
    $sql .= "((jour_semaine = $jour_semaine) OR (jour_semaine = NULL) OR (jour_semaine = 0)) AND";
    //Date jour
    $sql .= "((date_jour = $date_jour) OR (date_jour = NULL) OR (date_jour = 0)) AND";
    //Date mois
    $sql .= "((date_mois = $date_mois) OR (date_mois = NULL) OR (date_mois = 0)) AND";
    //Date annee
    $sql .= "((date_annee = $date_annee) OR (date_annee = NULL) OR (date_annee = 0))";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (is_ferie) \n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction is_ferie: $sql"));

    }
    $row = $result->fetchrow();

    $dbHandler->closeConnection(true);
    return ($row[0] > 0);
}
function jour_ouvrable($date_jour, $date_mois, $date_annee, $nbre_jour) {

// Cette fonction renvoie la date du n ème jour ouvrable suivant la date $date_jour/$date_mois/$date_annee
// Si $nbre_jour est négatif, on remonte dans le temps
// IN  : $date_jour, $date_mois, $date_annee : La date de départ
//       $nbre_jours : Le nombre de jours à avancer / reculer
// OUT : La date demandée au format jj/mm/aaaa

    if ($nbre_jour > 0) $sens = 1;
    else $sens = -1;

    $dj = $date_jour;
    $dm = $date_mois;
    $da = $date_annee;
    for ($i = 0; $i < $nbre_jour*$sens; ) {
        $timestamp = mktime(0,0,0,$dm,$dj+$sens,$da); //Incrémente ou décrémente d'un jour
        $dj = date("d", $timestamp);
        $dm = date("m", $timestamp);
        $da = date("Y", $timestamp);
        if (! is_ferie($dj, $dm, $da)) ++$i;
    }
    $timestamp = gmmktime(0,0,0,$dm,$dj,$da);
    $dj = date("d", $timestamp);
    $dm = date("m", $timestamp);
    $da = date("Y", $timestamp);

    return $dj."/".$dm."/".$da;
}
/**
 * Retourne la date passée en paramètre augmentée ou diminuée d'un certain nombre de jours ouvrables,
 * en fonction des paramètres du produit épargne associé au compte.
 * @param int $compte Identifiant du compte épargne
 * @param string $sens Sens de l'opération 'd' pour débit, 'c' pour crédit, il déterminera si on retranche ou si l'on rajoute des jours.
 * @param string $date_compta La date de comptabilisation de l'opération, au format jj/mm/aaaa
 * @return string $date_valeur La date valeur calculée, au format jj/mm/aaaa
 */
function getDateValeur($a_compte, $a_sens, $a_date_compta) {
    global $global_id_agence;
    if (!isset($a_compte))
        return $a_date_compta;
    $info_compte = getAccountDatas($a_compte);
    $info_produit = getProdEpargne($info_compte["id_prod"]);

    $decalage_debit = $info_produit["nbre_jours_report_debit"];
    $decalage_credit = $info_produit["nbre_jours_report_credit"];

    $nombre_jours = 0;
    if ($a_sens=='c') $nombre_jours = $decalage_credit;
    if ($a_sens=='d') $nombre_jours = $decalage_debit * (-1);

    $annee = substr($a_date_compta,6,4);
    $mois = substr($a_date_compta,3,2);
    $jour = substr($a_date_compta,0,2);

    $date_valeur = jour_ouvrable($jour, $mois, $annee, $nombre_jours);
    return $date_valeur;

}

/**
 * Verifie si le compte est centralisateur, c'est à dire s'il a des sous-comptes.
 *
 * @param string $num_cpte Le numéro du compte comptable.
 * @return boolean True si compte possède des sous comptes, False sinon.
 */
function executeQuery(&$db, $a_sql, $a_flat = FALSE) {
    global $dbHandler;

    $result = $db->query($a_sql);
    if (DB::isError($result)) {
        // S'il y a une erreur, on retourne ERR_DB_SQL avec le code de la requête ayant posé problème
        return new ErrorObj(ERR_DB_SQL, $result->getUserinfo());
    } else if (DB::isManip($a_sql)) {
        // Si c'est un UPDATE, INSERT ou DELETE, on retourne alors le nombre de lignes affectées
        return new ErrorObj(NO_ERR, $db->affectedRows());
    } else {
        // On suppose que la requête était un SELECT, on retourne alors les lignes trouvées
        $rows = array();
        if ($a_flat) {
            // On concatène les lignes (et les colonnes) retournées
            while ($row = $result->fetchrow()) {
                foreach ($row as $col => $content)
                    array_push($rows, $content);
            }
        } else {
            // On retourne un tableau ($row) par ligne de résultats
            while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
                array_push($rows, $row);
        }
        $result->free();
        return new ErrorObj(NO_ERR, $rows);
    }
}
function executeDirectQuery($a_sql, $a_flat = FALSE) {
    global $dbHandler;
    $db = $dbHandler->openConnection();
    $result = executeQuery($db, $a_sql, $a_flat);
    if ($result->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $result;
    }
    $dbHandler->closeConnection(true);
    return($result);
}
function isCentralisateur($num_cpte) {
    global $global_id_agence;
    $sql = "SELECT COUNT(*) FROM ad_cpt_comptable where cpte_centralise ='$num_cpte'  ";
    $result = executeDirectQuery($sql, true);
    if ($result->errCode != NO_ERR) {
        return false;
    } else {
        return ($result->param[0] > 0);
    }
}

function getJournalCpte($num_cpte) {
    //renvoie les informations sur le Journal associé au compte comptable

    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();
    $infos = array();

    // Regarder si ce compte a un journal associé
    $sql="SELECT *  FROM ad_cpt_comptable where id_ag=$global_id_agence and num_cpte_comptable = '$num_cpte' and cpte_princ_jou = 't'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getJournalCpte) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getJournalCpte: $sql"));
    }

    if ($result->numrows()==0) { // Si pas de journal associé. Rem :pourquoi ne pas faire appel à getComptesComptes et vérifier que c'un compte principal
        //$dbHandler->closeConnection(true);
        $non_jou = true;
        //return NULL;
    }

    $sql="SELECT *  FROM ad_journaux  where id_ag=$global_id_agence and num_cpte_princ = '$num_cpte' ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        // $dbHandler->closeConnection(true);
        $non_jou = true; // $non_jou nous indique que c'est le journal 1 qui sera utilisé par défaut
        // return NULL;
    }

    if ($non_jou == false) { // Sinon pas la peine, on sait déjà qu'il n'y a pas de journal associé
        // Si on a de la chance, ce compte est directement associé à un journal
        $sql="SELECT *  FROM ad_journaux  where id_ag=$global_id_agence and num_cpte_princ = '$num_cpte' ";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
//            echo "Erreur dans la fonction (getJournalCpte) Instruction SQL --> $sql\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction getJournalCpte: $sql"));
        }

        if ($result->numrows()==1) {
            $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
            $infos = $row;
            //$dbHandler->closeConnection(true);
            //return($row);
        } else {
            // On regarde si le compte centralisateur n'est pas compte principal d'un journal
            // FIXME : INUTILE : On peut déjà faire l'appel récursif !
            /*
            $sql="SELECT *  FROM ad_journaux  where num_cpte_princ = (SELECT cpte_centralise  FROM ad_cpt_comptable where num_cpte_comptable = '$num_cpte') ";
            $result = $db->query($sql);
            if (DB::isError($result))
              {
                $dbHandler->closeConnection(false);
                signalErreur(__FILE__,__LINE__,__FUNCTION__);
              }

            if($result->numrows()==1)
              {
                $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
                $infos = $row;
                //  $dbHandler->closeConnection(true);
                // return($row);
              }
            else
            {*/
            $sql ="SELECT cpte_centralise  FROM ad_cpt_comptable where id_ag=$global_id_agence and num_cpte_comptable = '$num_cpte'";
            $result = $db->query($sql);
            if (DB::isError($result)) {
                $dbHandler->closeConnection(false);
//                echo "Erreur dans la fonction (getJournalCpte) Instruction SQL --> $sql\n";
                return new ErrorObj (ERR_DB_SQL, _("Fonction getJournalCpte: $sql"));
            }

            if ($result->numrows()==1) {
                $row = $result->fetchrow();
                $info_jou = getJournalCpte($row[0]); // Appel récursif avec le compte centralisateur
                $dbHandler->closeConnection(true);
                return $info_jou;
            } else {
                // On est arrivés à la racine du plan comptable, il y a donc une inconsistance dans la base de données
                $dbHandler->closeConnection(false);
                return new ErrorObj (ERR_GENERIQUE, _("Fonction getJournalCpte: Inconsistance dans la DB : le compte $num_cpte est censé etre compte principal et courant ..."));
            }

        }
    }
    $dbHandler->closeConnection(true);
    if ($non_jou == true) {
        $jou_princ = getInfosJournal(1);
        $infos = $jou_princ[1];
        return($infos);
    } else
        return($infos);

}
function get_last_batch($id_agence) {
    global $dbHandler;
    $db = $dbHandler->openConnection();

    $sql = "SELECT last_batch FROM ad_agc WHERE (id_ag=$id_agence)";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (get_last_batch) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction get_last_batch: $sql"));
    }
    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);

    return $row[0];
}
/**
 * Transforme une date venant de Postgres vers le format de PHP
/**
 * Transforme une date venant de Postgres vers le format de PHP
 * @param str $a_date Date au format aaaa-mm-jj
 * @return str Date au format jj/mm/aaaa
 */
function pg2phpDate($a_date) {
    if ($a_date == "") return "";
    // Ex : 2002-02-05
    $a_date = substr($a_date,0,10);
    $M = substr($a_date,5,2);
    $J = substr($a_date,8,2);
    $A = substr($a_date,0,4);
    return "$J/$M/$A";
}
function isAfter($date1, $date2, $equal = false) {
    // Fonction qui renvoie true si $date1 est postérieure à $date2
    // false si $date1 est antérieure ou égale à $date2
    // IN : $date1 au format jj/mm/aaaa
    //      $date2 au format jj/mm/aaaa
    // OUT: true ou false

    $j1 = substr($date1,0,2);
    $m1 = substr($date1,3,2);
    $a1 = substr($date1,6,4);

    $j2 = substr($date2,0,2);
    $m2 = substr($date2,3,2);
    $a2 = substr($date2,6,4);

    $time1 = mktime(0,0,0,$m1, $j1, $a1);
    $time2 = mktime(0,0,0,$m2, $j2, $a2);

    if($equal) {
        return ($time1 >= $time2);
    }
    else {
        return ($time1 > $time2);
    }
}
function getJournauxLiaison($fields_values=NULL) {
    /**
     *Fonction renvoyant l'ensemble des comptes de liaison et leurs journaux associés
     * @author Papa NDIAYE
     * @since 1.0.8
     * @param array $fields_values, on construit la clause WHERE ainsi : ... WHERE field = value ...
     * @return array ( index => infos)
     */

    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    //vérifier qu'on reçoit bien un array
    if (($fields_values != NULL) && (! is_array($fields_values)))
        return new ErrorObj (ERR_GENERIQUE, _("Fonction getJournauxLiaison: Mauvais argument dans l'appel de la fonction"));

    // construction de la requête
    $sql = "SELECT * FROM ad_journaux_liaison where id_ag=$global_id_agence";
    if (isset($fields_values)) {
        $sql .= " AND ";
        foreach ($fields_values as $key => $value)
            if ( $key == 'id_jou1' || $key == 'id_jou2')
                $sql .= "(id_jou1=$value OR id_jou2=$value ) AND "; // Soit il est à la première position soit il est la 2ème
            else
                $sql .= "$key = '$value' AND ";
        $sql = substr($sql, 0, -4);
    }
    $sql .= " ORDER BY id_jou1 ASC";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        return new ErrorObj (ERR_DB_SQL, _("Fonction getJournauxLiaison: $sql"));
    }

    // Liste des comptes de liaison
    $info = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        array_push($info,$row);

    $dbHandler->closeConnection(true);
    return $info;

}
function getDetailsOperation($type_oper) {
    global $dbHandler, $global_id_agence, $global_langue_systeme_dft;
    $db = $dbHandler->openConnection();

    define("SENS_CREDIT", "c");
    define("SENS_DEBIT", "d");

    // récupération du détail de l'opération
    $sql = "SELECT * FROM ad_cpt_ope_cptes WHERE id_ag=$global_id_agence and type_operation = $type_oper ORDER BY sens DESC;";
    $result = $db->query($sql);
    $dbHandler->closeConnection(true);

    if (DB::isError($result)) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    if ($result->numRows() == 0) // Il n'y a pas d'association pour cette opération
        return new ErrorOBj(ERR_NO_ASSOCIATION);

    $OP = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        if ($row["sens"] == SENS_DEBIT) // informations au débit de l'opération
            $OP["debit"] = array("compte"=>$row["num_cpte"], "sens"=>$row["sens"], "categorie"=>$row["categorie_cpte"]);
        elseif($row["sens"] == SENS_CREDIT)  // informations au crédit de l'opération
            $OP["credit"] = array("compte"=>$row["num_cpte"],"sens"=>$row["sens"],"categorie"=>$row["categorie_cpte"]);
    }

    return new ErrorObj(NO_ERR, $OP);
}
/**
 * Fonction qui permet de faire la comptabilisation des écritures de ADbanking, elle construit un tableau qu'on passera à ajout_historique
 * @author Hassan et Mouhamadou
 * @since 1.0.8
 * @param int $type_oper Numéro de l'opération, elle peut donnée directement ou déduite dans le schemas comptable
 * @param int $montant Montant de la transaction :Comme on a un seul débit et un seul crédit, le montant est le même des 2 côtés du mouvement
 * @param array $comptable_his tableau passé par référence, il va contenir l'historique des comptes à debiter et à crediter dans le cadre de l'appel
 * @param array $array_cptes tableau utilisé si on doit faire une substitution : (
 * - "cpta" => array("debit" => compte comptable à débiter,"credit" => compte comptable à  créditer)
 * - "int"  => array("debit" => compte interne à  débiter,"credit" => compte interne à  créditer) )
 * L'array "cpta" permet de passer les comptes comptables à subsituer au débit ou au crédit.
 * L'array "int" permet de passer le compte interne (ad_cpt) si la transaction implique un compte client.
 * Les 2 arrays sont indépendants
 * @return ErrorObj Objet erreur
 */

function passageEcrituresComptablesAuto($type_oper, $montant, &$comptable_his, $array_cptes=NULL, $devise=NULL, $date_compta=NULL,$info_ecriture=NULL,$infos_sup=NULL) {

    global $dbHandler;
    global $global_id_exo;
    global $global_multidevise;
    global $global_id_agence;

    $db = $dbHandler->openConnection();

    $mouvements = array();
    //verifier s'il faut substituer des comptes
    if (isset($array_cptes)) {
        //FIXME : verifier que le vecteur a au plus 2 lignes (1 debit et 1 credit)
        //lire chaque element du vecteur
        foreach ($array_cptes as $key=>$value) {
            //prendre les comptes a substituer
            if ($key == "cpta") { //il existe des comptes comptables a substituer
                foreach ($value as $key2=>$value2)
                    if ($key2 == "debit")
                        $cpte_debit_sub = $value2;
                    elseif ($key2 == "credit")
                        $cpte_credit_sub = $value2;
            }

            if ($key == "int") { //il existe des comptes internes a renseigner
                foreach ($value as $key2=>$value2)
                    if ($key2 == "debit")
                        $cpte_int_debit = $value2;
                    elseif ($key2 == "credit")
                        $cpte_int_credit = $value2;
            }
        }
    }

    //FIXME : gérer les frais en attente


    //Recuperer les infos sur l'operation

    $InfosOperation = array();
    $MyError = getOperations($type_oper);
    if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
        $dbHandler->closeConnection(false);
        return $MyError;
    } else {
        $InfosOperation = $MyError->param;
    }

    // comptes au débit et crédit
    $DetailsOperation = array();

    $MyError = getDetailsOperation($type_oper);
    if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
        $dbHandler->closeConnection(false);
        return $MyError;
    } else {
        $DetailsOperation = $MyError->param;
    }

    // Recherche du dernier élément du tableau

    end ($comptable_his);
    $tmparr = current($comptable_his);
    $last_libel_oper = $tmparr["libel"];

    if ($last_libel_oper == $type_oper) {
        if ($tmparr["type_operation"] != $type_oper ){
            $newID = getLastIdOperation($comptable_his) + 1;
        }else {
            $newID = getLastIdOperation($comptable_his);
        }
    }
    else {
        $newID = getLastIdOperation($comptable_his) + 1;
    }

    //Changer le libellé de l'opération, si autre libellé
    if ($infos_sup["autre_libel_ope"] != NULL)
        $InfosOperation["libel"] = $infos_sup["autre_libel_ope"];

    //FIXME : ici ça marche parce qu'on a 1 débit et 1 crédit
    $comptable = array();

    // Choix du journal ,cela va dependre des comptes au débit et au crédit

    //Compte comptable à debiter

    if (isset($cpte_debit_sub))
        $cpte_debit = $cpte_debit_sub;
    else
        $cpte_debit = $DetailsOperation["debit"]["compte"];

    // Si on a pas de compte comptable, il y a eu un problème dans le paramétrage des opérations :
    if (!isset($cpte_debit)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_NO_ASSOCIATION, sprintf(_("Compte au débit de l'opération %s"), $type_oper));
    }

    //Compte comptable à crediter
    if (isset($cpte_credit_sub))
        $cpte_credit = $cpte_credit_sub;
    else
        $cpte_credit = $DetailsOperation["credit"]["compte"];

    // Si on a pas de compte comptable, il y a eu un problème dans le paramétrage des opérations :
    if (!isset($cpte_credit)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_NO_ASSOCIATION, sprintf(_("Compte au crédit de l'opération %s"), $type_oper));
    }

    // Si multidevise, vérifie que l'écriture peut avoir lieu
    if ($global_multidevise) {
        if ($devise == NULL) { // Par defaut la devise de reference est utilisee
            global $global_monnaie;
            $devise = $global_monnaie;
        }
        $cpte_debit_dev = checkCptDeviseOK($cpte_debit, $devise);
        if ($cpte_debit_dev == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_DEVISE_CPT, _("Devise")." : $devise, "._("compte debit")." : $cpte_debit");
        }
        $cpte_credit_dev = checkCptDeviseOK($cpte_credit, $devise);
        if ($cpte_credit_dev == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_DEVISE_CPT, _("Devise")." : $devise, "._("compte")." : $cpte_credit");
        }

        $cpte_debit = $cpte_debit_dev;
        $cpte_credit = $cpte_credit_dev;

        // Vérifie également que les comptes internes associés s'ils existent sont dans la bonne devise
        if (isset($cpte_int_debit)) {
            $ACC = getAccountDatas($cpte_int_debit);
            if ($ACC["devise"] != $devise) {
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_DEVISE_CPT_INT, _("Devise")." : $devise, "._("opération")." : $type_oper");
            }
        }
        if (isset($cpte_int_credit)) {
            $ACC = getAccountDatas($cpte_int_credit);
            if ($ACC["devise"] != $devise) {
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_DEVISE_CPT_INT, _("Devise")." : $devise, "._("opération")." : $type_oper");
            }
        }
    } else { // En mode unidevise, la devise est toujours la devise de référence
        global $global_monnaie;
        $devise = $global_monnaie;
    }

    // On ne mouvemente pas un compte centralisateur
    if (isCentralisateur($cpte_debit)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPT_CENTRALISE, _("compte")." : $cpte_debit");
    }

    if (isCentralisateur($cpte_credit)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPT_CENTRALISE, _("compte")." : $cpte_credit");
    }

    $jou_cpte_debit = getJournalCpte($cpte_debit);
    $jou_cpte_credit = getJournalCpte($cpte_credit);


    $id_exo = "";

    if(is_array($infos_sup) && count($infos_sup) > 0 && array_key_exists('id_exo', $infos_sup)){
        $id_exo = $infos_sup['id_exo'];
    }

    $exo_encours = "";
    $date_fin = "";
    $date_debut = "";

    // la date comptable doit être dans la période de l'exercice en cours à cours
    if(empty($id_exo)) {
        $exo_encours = getExercicesComptables($global_id_exo);
        $date_debut = pg2phpDate($exo_encours[0]["date_deb_exo"]); // date debut exo ou max date prov
        $date_fin = pg2phpDate($exo_encours[0]["date_fin_exo"]);   // date hier
    }
    else { // ou dans les bornes fournis en parametres
        $exo_encours = $id_exo;
        $date_debut = $infos_sup['date_debut'];
        $date_fin = $infos_sup['date_fin'];
    }

    // date comptable
    if ($date_compta == NULL) {
        $date_comptable = date("d/m/Y"); // date du jour
        if (isAfter($date_comptable, $date_fin))
            $date_comptable = pg2phpDate(get_last_batch($global_id_agence));
    } else
        $date_comptable = $date_compta;

    //Cas exceptionel ou pour les declassements la date $date_compta n'est pas dans l'exercice actuelle
    if ($type_oper == 270 && isAfter($date_comptable, $date_fin)){
        $isDeclassement = 't';
    }

    //si c'est un declassement qui n'est plus dans l'exercice comptable, on laisse passer exceptionellement.
    if ($isDeclassement != 't') {
        if ((isAfter($date_debut, $date_comptable)) or (isAfter($date_comptable, $date_fin))) {
            $dbHandler->closeConnection(false);
            $msg = ". La date n'est pas dans la période de l'exercice.";
            if (!empty($id_exo)) {
                $msg = ". La date n'est pas valide.";
            }
            return new ErrorObj(ERR_DATE_NON_VALIDE, $msg);
        }
    }

    //echo "Journal au débit : $jou_cpte_debit et journal au crédit : $jou_cpte_credit<BR>";
    if (($jou_cpte_debit["id_jou"] != $jou_cpte_credit["id_jou"]) &&  ($jou_cpte_debit["id_jou"] > 1) && ($jou_cpte_credit["id_jou"] > 1) ) {
        //Utilisation d'un compte de liaison
        $InfosOperation["jou_debit"] = $jou_cpte_debit ["id_jou"];
        $InfosOperation["jou_credit"] = $jou_cpte_credit ["id_jou"];
        $temp1 = $jou_cpte_debit["id_jou"];
        $temp2 = $jou_cpte_credit["id_jou"];

        //Recuperation du compte de liaison

        $temp["id_jou1"] = $temp1;
        $temp["id_jou2"] = $temp2;

        $temp_liaison = getJournauxLiaison($temp);

        if (count($temp_liaison ) != 1 ) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_PAS_CPTE_LIAISON);
        }
        $cpte_liaison = $temp_liaison[0]["num_cpte_comptable"];

        // Passages écritures du compte debit au compte de liaison
        $comptable[0]["id"] = $newID;
        $comptable[0]["compte"] = $cpte_debit;
        if (isset($cpte_int_debit))
            $comptable[0]["cpte_interne_cli"] = $cpte_int_debit;
        else
            $comptable[0]["cpte_interne_cli"] = NULL;

        $comptable[0]["type_operation"] = $InfosOperation["type_operation"];
        $comptable[0]["date_valeur"] = getDateValeur($cpte_int_debit,'d',$date_comptable);
        $comptable[0]["sens"] = SENS_DEBIT;
        $comptable[0]["montant"] = $montant;
        $comptable[0]["date_comptable"] = $date_comptable;
        $comptable[0]["libel"] = $InfosOperation["libel"];
        $comptable[0]["jou"] = $InfosOperation["jou_debit"];

        if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
        else $comptable[0]["exo"] = $global_id_exo;

        $comptable[0]["validation"] = 't';
        $comptable[0]["devise"] = $devise;
        $comptable[0]["info_ecriture"] = $info_ecriture;

        $comptable[1]["id"] = $newID;
        $comptable[1]["compte"] = $cpte_liaison;
        $comptable[1]["cpte_interne_cli"] = NULL;
        $comptable[1]["type_operation"] = $InfosOperation["type_operation"];
        $comptable[1]["date_valeur"] = getDateValeur($cpte_int_credit,'c',$date_comptable);
        $comptable[1]["sens"] = SENS_CREDIT;
        $comptable[1]["montant"] = $montant;
        $comptable[1]["date_comptable"] = $date_comptable;
        $comptable[1]["libel"] = $InfosOperation["libel"];
        $comptable[1]["jou"] = $InfosOperation["jou_debit"];

        if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
        else $comptable[0]["exo"] = $global_id_exo;

        $comptable[1]["validation"] = 't';
        $comptable[1]["devise"] = $devise;
        $comptable[1]["info_ecriture"] = $info_ecriture;


        // Passages ecritures du compte credit au compte de liaison

        $newID++;
        $comptable[2]["id"] = $newID;
        $comptable[2]["compte"] = $cpte_liaison;
        $comptable[2]["cpte_interne_cli"] = NULL;
        $comptable[2]["type_operation"] = $InfosOperation["type_operation"];
        $comptable[2]["date_valeur"] = getDateValeur($cpte_int_debit,'d',$date_comptable);
        $comptable[2]["sens"] = SENS_DEBIT;
        $comptable[2]["montant"] = $montant;
        $comptable[2]["date_comptable"] = $date_comptable;
        $comptable[2]["libel"] = $InfosOperation["libel"];
        $comptable[2]["jou"] = $InfosOperation["jou_credit"];

        if(!empty($id_exo)) $comptable[2]["exo"] = $id_exo;
        else $comptable[2]["exo"] = $global_id_exo;

        $comptable[2]["validation"] = 't';
        $comptable[2]["devise"] = $devise;
        $comptable[2]["info_ecriture"] = $info_ecriture;

        $comptable[3]["id"] = $newID;
        $comptable[3]["compte"] = $cpte_credit;
        if (isset($cpte_int_credit))
            $comptable[3]["cpte_interne_cli"] = $cpte_int_credit;
        else
            $comptable[3]["cpte_interne_cli"] = NULL;
        $comptable[3]["type_operation"] = $InfosOperation["type_operation"];
        $comptable[3]["date_valeur"] = getDateValeur($cpte_int_credit,'c',$date_comptable);
        $comptable[3]["sens"] = SENS_CREDIT;
        $comptable[3]["montant"] = $montant;
        $comptable[3]["date_comptable"] = $date_comptable;
        $comptable[3]["libel"] = $InfosOperation["libel"];
        $comptable[3]["jou"] = $InfosOperation["jou_credit"];

        if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
        else $comptable[0]["exo"] = $global_id_exo;

        $comptable[3]["validation"] = 't';
        $comptable[3]["devise"] = $devise;
        $comptable[3]["info_ecriture"] = $info_ecriture;
    }

    else {//Ici, on choisit le journal dont l'id > journal principal si un des comptes est associé à ce journal
        $InfosOperation["jou"] = max($jou_cpte_debit ["id_jou"],$jou_cpte_credit ["id_jou"]);

        $comptable[0]["id"] = $newID;
        $comptable[0]["compte"] = $cpte_debit;
        if (isset($cpte_int_debit))
            $comptable[0]["cpte_interne_cli"] = $cpte_int_debit;
        else
            $comptable[0]["cpte_interne_cli"] = NULL;
        $comptable[0]["type_operation"] = $InfosOperation["type_operation"];
        $comptable[0]["date_valeur"] = getDateValeur($cpte_int_debit,'d',$date_comptable);
        $comptable[0]["sens"] = SENS_DEBIT;
        $comptable[0]["montant"] = $montant;
        $comptable[0]["date_comptable"] = $date_comptable;
        $comptable[0]["libel"] = $InfosOperation["libel"];
        $comptable[0]["jou"] = $InfosOperation["jou"];

        if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
        else $comptable[0]["exo"] = $global_id_exo;

        $comptable[0]["validation"] = 't';
        $comptable[0]["devise"] = $devise;
        $comptable[0]["info_ecriture"] = $info_ecriture;

        $comptable[1]["id"] = $newID;
        $comptable[1]["compte"] = $cpte_credit;
        if (isset($cpte_int_credit))
            $comptable[1]["cpte_interne_cli"] = $cpte_int_credit;
        else
            $comptable[1]["cpte_interne_cli"] = NULL;
        $comptable[1]["type_operation"] = $InfosOperation["type_operation"];
        $comptable[1]["date_valeur"] = getDateValeur($cpte_int_credit,'c',$date_comptable);
        $comptable[1]["sens"] = SENS_CREDIT;
        $comptable[1]["montant"] = $montant;
        $comptable[1]["date_comptable"] = $date_comptable;
        $comptable[1]["libel"] = $InfosOperation["libel"];
        $comptable[1]["jou"] = $InfosOperation["jou"];

        if(!empty($id_exo)) $comptable[0]["exo"] = $id_exo;
        else $comptable[0]["exo"] = $global_id_exo;

        $comptable[1]["validation"] = 't';
        $comptable[1]["devise"] = $devise;
        $comptable[1]["info_ecriture"] = $info_ecriture;
    }

    $comptable_his = array_merge($comptable_his, $comptable);

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);

}
//Fonction qui determine si un client est radié
function is_client_radie(){
    global $global_etat_client;
    if(in_array($global_etat_client, array(3,5,6))) {
        return true;
    }
    else {
        return false;
    }
}
/**
 * Renvoie tous les comptes d'épargne d'un client qui sont services financiers
 * @param int $id_client L'identifiant du client
 * @param str $devise La devise dans laquelle on cherche les comptes
 * @return array Tableau associatif avec les comptes trouvés, indicé par les identifiants du compte.
 */
function get_comptes_epargne($id_client, $devise=NULL) {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT a.*, b.* FROM ad_cpt a,adsys_produit_epargne b WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence AND a.id_prod = b.id AND ";
    $sql .= "a.id_titulaire = '$id_client' and b.service_financier = true and b.classe_comptable <> 8";
    // On ne prend pas les comptes bloqués
    if (!is_client_radie()){
        $sql .= " AND (a.etat_cpte <> 2)";
    }
    if ($devise != NULL)
        $sql .= " AND a.devise = '$devise'";

    $sql .= " ORDER BY a.num_complet_cpte";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (get_comptes_epargne) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction get_comptes_epargne: $sql"));
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
/**
 * Envoi de message sur le broker si le array contienne des type operation fesant l'objet d'envoi de sms
 * @param $array_comptable
 */
function envoi_sms_mouvement($array_comptable)
{
    if (!empty($array_comptable)) {
        foreach ($array_comptable as $k => $val){
            if (isset($val['cpte_interne_cli'])){
                if (is_array($client = checkClientAbonnement($val['cpte_interne_cli'])) && $cpt_epargne = get_comptes_epargne($client["id_client"]) ) {

                    $listeOptEnvoiSMS = array();
                    $typeOptEnvoiSMS = getListeTypeOptPourPreleveFraisSMS();
                    foreach ($typeOptEnvoiSMS as $key => $value) {
                        foreach ($value as $item => $typeOpt) {
                            array_push($listeOptEnvoiSMS, $typeOpt);
                        }
                    }

                    if (in_array($val['type_operation'], $listeOptEnvoiSMS)) {

                        global $code_imf,$MSQ_HOST, $MSQ_PORT, $MSQ_USERNAME, $MSQ_PASSWORD, $MSQ_VHOST;
                        global $MSQ_EXCHANGE_NAME, $MSQ_EXCHANGE_TYPE, $MSQ_QUEUE_NAME_MOUVEMENT, $MSQ_ROUTING_KEY_MOUVEMENT;

                        // get the necessary data to send as message to the broker
                        $cpte_interne_cli = $val['cpte_interne_cli'];
                        $montant = $val['montant'];
                        $date_valeur = date('Y-m-d', strtotime(str_replace('/', '-', $val['date_valeur'])));

                        $datas = MouvementMSQPublisher::getMouvementData($cpte_interne_cli, $montant, $date_valeur, $val['solde_msq']);

                        $rawMessage = array(
                            'telephone' => $datas['telephone'],
                            'langue' =>$datas['langue'],
                            'date_transaction' => $datas['date_transaction'],
                            'type_opt' => $datas['type_opt'],
                            'sens' => $datas['sens'],
                            'num_complet_compte' => $datas['num_complet_cpte'],
                            'id_mouvement' => $datas['id_mouvement'],
                            'id_ag' => $datas['id_ag'],
                            'code_imf' => $code_imf,
                            'libelle_ecriture' => $datas['libelle_ecriture'],
                            'montant' => $datas['montant'],
                            'devise' => $datas['devise'],
                            'nom' => $datas['nom'],
                            'prenom' => $datas['prenom'],
                            'solde' => $datas['solde'],
                            'intitule_compte' => $datas['intitule_compte'],
                            'libelle_produit' => $datas['libelle_produit'],
                            'communication' => $datas['communication'],
                            'tireur' => $datas['tireur'],
                            'donneur' => $datas['donneur'],
                            'numero_cheque' => $datas['numero_cheque'],
                            'date_ouvert' => $datas['date_ouvert'],
                            'statut_juridique' => $datas['statut_juridique'],
                            'id_client' => $datas['id_client'],
                            'id_transaction' => $datas['id_transaction'],
                            'ref_ecriture' => $datas['ref_ecriture'],
                        );

                        //--------- Instantiates the publisher ---------------//
                        $mouvementPublisher = new MouvementMSQPublisher(
                            $MSQ_HOST,
                            (int)$MSQ_PORT,
                            $MSQ_USERNAME,
                            $MSQ_PASSWORD,
                            $MSQ_QUEUE_NAME_MOUVEMENT,
                            $MSQ_ROUTING_KEY_MOUVEMENT,
                            $MSQ_EXCHANGE_NAME,
                            $MSQ_VHOST
                        );

                        // Executes publish process
                        $mouvementPublisher->executePublisher($rawMessage);
                    }
                }
            }
        }
    }
}
function ajout_historique($type_fonction, $id_client, $infos, $login, $date, $array_comptable=NULL, $data_ext=NULL, $idhis=NULL, $id_doss = NULL, $source = 2) {
    /*
      Cette f° se charge d'enregister dans l'historique aussi bien la f° (ad_his) que les opérations comptables associées (ad_ecriture qui contient les informations sur les ecritures et ad_mouvement qui donne les mouvement sur les comptes
    et ad_cpt_comptable)

    Paramètres entrants :
      - type d'opération (cf. n° table système)
      - infos supplémentaires (cfr documentation/historique.txt)
      - date
      - login de l'utilisateur

      - SI c'est une opération comptable, un tableau a 9 colonnes :
        - 'id' : identifie l'écriture dans un lot d'écriture
        - 'compte' : compte à mouvementer
        - 'cpte_interne_cli' : si le mouvement concerne un compte client, on passe l'id (cf ad_cpt). Ce champ peut être NULL
        - 'sens' : sens du mouvement ('c' ou 'd')
        - 'montant' : montant du mouvement
        - 'date_comptable' : date de valeur  du mouvement
        - 'libel' : libellé de l'écriture
        - 'jou' : identifiant du journal associé à l'opération. Ce champ peut être NULL
        - 'exo' : identifiant de l'exercice comptable associé à la date de valeur
        - 'devise' : Code de la devise du mouvement

    FIXME
       Cette procédure vérifie si la somme des montants débités est équivalante à la somme des montants crédités après quoi elle renseigne les tables concernées.

    OUT
      objet Error
      Si OK, Renvoie l'id dans la table historique comme paramètre
    */

    global $global_monnaie_courante_prec;
    global $dbHandler, $global_id_agence, $debug, $appli;

    $db = $dbHandler->openConnection();
    $id_agence_encours = getNumAgence();

    // S'il y a des données à insérer dans la table historique des transferts avec l'extérieur, on commence par cette insertion.
    if ($data_ext == NULL) {
        $id_his_ext = 'NULL';
    } else {
        $id_his_ext = insertHistoriqueExterieur($data_ext);
        if ($id_his_ext == NULL) {
            $dbHandler->closeConnection(false);
//            echo "Erreur dans la fonction (ajout_historique)\n";
            return new ErrorObj (ERR_GENERIQUE, _("Fonction ajout_historique"));
        }
    }

    $infos = string_make_pgcompatible($infos);
    // Pour ne pas avoir une erreur de PSQL si pas de client associé.
    if ($id_client == '' || $id_client == NULL) {
        $id_client = 'NULL';
    }
    if ($idhis == NULL ) {
        // On commence par récupérer le numéro de lot
        $sql = "SELECT nextval('ad_his_id_his_seq')";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
//            echo "Erreur dans la fonction (ajout_historique) Instruction SQL --> $sql\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction ajout_historique: $sql"));
        }

        $row = $result->fetchrow();
        $idhis = $row[0];
        // On insère dans la table historique
        $sql = "INSERT INTO ad_his(id_his,id_ag, type_fonction, infos, id_client, login, date, id_his_ext) ";
        $sql .= "VALUES($idhis,$id_agence_encours, $type_fonction, '$infos', $id_client, '$login', '$date', $id_his_ext)";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
//            echo "Erreur dans la fonction (ajout_historique) Instruction SQL --> $sql\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction ajout_historique: $sql"));
        }
    }
    $tab_id = NULL; //Default to empty
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
            if (! isset($value['libel'])) {
//                echo "<p><font color=\"red\">".sprintf(_("Erreur : l'écriture n'a pas de libellé pour la transaction %s, compte %s !"),$idhis,$value['compte'])."</font></p>";
                return new ErrorObj (ERR_GENERIQUE, _("Fonction ajout_historique: "."<p><font color=\"red\">".sprintf(_("Erreur : l'écriture n'a pas de libellé pour la transaction %s, compte %s !"),$idhis,$value['compte'])."</font></p>"));
            }

            // Pour chaque débit crédit
            if ($value['sens'] == SENS_CREDIT) {
                $equilibre += $value['montant'];
            } elseif ($value['sens'] == SENS_DEBIT) {
                $equilibre -= $value['montant'];
            }

            // Recherche de tous les id différents
            if (in_array($value['id'],$tab_id) == false) {
                $temp = array();
                array_push($tab_id,$value['id']);
                $info_ecri = $value['info_ecriture'];
                if ($value["type_operation"] == 375 || $value["type_operation"] == 20) {
                    $info_ecri = explode('-',$value['info_ecriture']);
                    $info_ecri = $info_ecri[0];
                }
                $temp = array("libel" => $value["libel"], "type_operation" => $value["type_operation"], "date_comptable" => $value["date_comptable"], "id_jou" => $value["jou"], "id_exo" => $value["exo"],"info_ecriture"=>$info_ecri);
                $tab_fact[$value['id']] = $temp;
            }

        }
        if (round($equilibre, $global_monnaie_courante_prec) != 0) {
            //Si la somme débit != somme crédit
            $dbHandler->closeConnection(false);
            // FIXME : renvoyer un objet Error à la place du signalErreur
//            echo "Erreur dans la fonction (ajout_historique)\n";
            return new ErrorObj (ERR_GENERIQUE, _("Fonction ajout_historique: ligne ".__LINE__));
        }
    }

    // Garde la liste des comptes comptables qui vont etre impactés par des mouvements
    $liste_comptes_comptable = array();

    $IAR_INFO_temp = array();

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
            $DATA["id_ag"] = $global_id_agence;
            $DATA["id_exo"] = $tab_fact[$value]["id_exo"];
            $DATA["ref_ecriture"] = makeNumEcriture($DATA["id_jou"], $DATA["id_exo"]);
            $DATA["info_ecriture"] = $tab_fact[$value]["info_ecriture"];

            $sql = buildInsertQuery("ad_ecriture",$DATA);

            $result = $db->query($sql);
            if (DB::isError($result)) {
                $dbHandler->closeConnection(false);
//                echo "Erreur dans la fonction (ajout_historique) Instruction SQL --> $sql\n";
                return new ErrorObj (ERR_DB_SQL, _("Fonction ajout_historique:  $sql"));
            }

            // Récupérer le numéro d'ecriture
            $sql = "SELECT max(id_ecriture) from ad_ecriture where id_ag=$global_id_agence ";
            $result = $db->query($sql);
            if (DB::isError($result)) {
                $dbHandler->closeConnection(false);
//                echo "Erreur dans la fonction (ajout_historique) Instruction SQL --> $sql\n";
                return new ErrorObj (ERR_DB_SQL, _("Fonction ajout_historique:  $sql"));
            }

            $row = $result->fetchrow();
            $idecri = $row[0];

            // Insertion dans ad_mouvement les mouvements sur les comptes
            foreach ($array_comptable as $key1 => &$value1) { // Pour chaque mouvement
                if ($value1['id'] == $value ) { //mise à jour des soldes comptables

                    //REL-80 et REL-84: Gestion montant non arrondie par un flag $isOperationIAR, qui par défaut est false
                    //REL-80 et REL-84 $isOperationIAR is set true si c'est operations 374 et (375 et 20 relie)
                    //REL-80 fonction setSoldeCpteCli - 4eme parametre. C'est pour les operations 375 et 20 (Remboursement IAR et interet Credit)
                    //REL-84 fonction setSoldeComptable - 4eme parametre. C'est pour les operations 20 (Remboursement interet Credit associe a un IAR dans la foulee d'une operation 375)
                    /****************************************************************************************/
                    $isOperationIAR = false;
                    if ($value1['type_operation'] == 374){ //Calcule IAR
                        $isOperationIAR = true;
                    }
                    if ($value1['type_operation'] == 375){ //Reprise IAR
                        $isOperationIAR = true;
                        if (sizeof($IAR_INFO_temp) == 0){ //first time 375
                            $IAR_INFO_temp[0] = $value1['cpte_interne_cli'];
                            $IAR_INFO_temp[1] = $value1['info_ecriture'];
                        }
                        else{
                            if ($value1['cpte_interne_cli'] != $IAR_INFO_temp[0] && $value1['info_ecriture'] != $IAR_INFO_temp[1]){
                                unset($IAR_INFO_temp);
                                $IAR_INFO_temp[0] = $value1['cpte_interne_cli'];
                                $IAR_INFO_temp[1] = $value1['info_ecriture'];
                            }
                        }
                    }
                    if ($value1['type_operation'] == 20 && sizeof($IAR_INFO_temp) > 0 && ($value1['cpte_interne_cli'] == $IAR_INFO_temp[0] || $value1['info_ecriture'] == $IAR_INFO_temp[1])){ //Remboursement interet Credit associe a un IAR for setSoldeComptable
                        $isOperationIAR = true;
                        if ($value1['sens'] == 'c' ) {
                            unset($IAR_INFO_temp);
                        }
                    }
                    /****************************************************************************************/

                    //FIXME : il faut obliger à passer par les sous-comptes (ex : erreur de paramétrage)
                    //FIXME : le montant passé doit avoir été correctement récupéré au préalable par un recupMontant approprié
                    $MyError = setSoldeComptable($value1['compte'], $value1['sens'], $value1['montant'], $value1["devise"], $isOperationIAR);
                    if ($MyError->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $MyError;
                    }

                    // Mise à jour compte client interne
                    if ($value1['cpte_interne_cli'] != '' && $value1['type_operation'] != 270 && $value1['type_operation'] != 170) {
                        if ($value1['type_operation'] == 20 && sizeof($IAR_INFO_temp) > 0 && $value1['cpte_interne_cli'] == $IAR_INFO_temp[0] && $value1['info_ecriture'] == $IAR_INFO_temp[1]){ //Remboursement interet Credit associe a un IAR for setSoldeCpteCli
                            $isOperationIAR = true;
                        }
                        $MyError = setSoldeCpteCli($value1['cpte_interne_cli'], $value1['sens'], $value1['montant'], $value1['devise'], $isOperationIAR);
                        if ($MyError->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);

                            return $MyError;
                        }

                        $cpte_interne_cli = $value1['cpte_interne_cli'];
                    }

                    // Recuperer solde pour le message queue
                    if (!is_null($value1['cpte_interne_cli'])) {
                        $value1['solde_msq'] = getSoldeCpte($value1['cpte_interne_cli']);
                    }

                    // Fix montant si NULL ou vide
                    $ad_mouvement_montant = recupMontant($value1["montant"]);
                    if($ad_mouvement_montant==NULL || $ad_mouvement_montant=='') {
                        $ad_mouvement_montant = 0;
                    }
                    else { // #514: arrondir le montant + #356 arrondies
                        // #792 on verifie si IAR/IAP est parametré sinon on fait les arrondissement montant pour tous les operations comptables
                        $isMouvementIARIAP = is_Mouvement_IAR_IAP($idecri);
                        $getCompteIAP = getCompteIAP();
                        if ($isMouvementIARIAP == 2 || $isOperationIAR === true){ //IAR
                            if(($value1['type_operation'] != 374) && ($value1['type_operation'] != 20) && ($value1['type_operation'] != 375)){
                                $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']);
                            }
                        }
                        else if ($isMouvementIARIAP == 3 || $getCompteIAP != null || $getCompteIAP != ''){ //IAP
                            if(($value1['type_operation'] != 40) &&  ($value1['type_operation'] != 62) && ($value1['type_operation'] != 476)){
                                $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']);
                            }
                            else{ //si au cas ou le montant de l'operation 40 reprise IAP n'est pas arrondie alors on n'arrondie pas ceux pour les operations 62 et 476 - cas gere pour les anciennes ecritures sinon on fait les arrondissements
                                $hasDecimal=hasDecimalMntRepriseIAP($idhis);
                                if ($hasDecimal === false){
                                    $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']);
                                }
                            }
                        }
                        else{ // si $isMouvementIARIAP == 1
                            $ad_mouvement_montant = arrondiMonnaiePrecision($ad_mouvement_montant,$value1['devise']); //par defaut si IAR/IAP n'est pas parametré
                        }
                    }

                    // Alimenter la liste des comptes comptables qui sont impactés par des mouvements
                    if(!(in_array($value1["compte"], $liste_comptes_comptable, TRUE))) {
                        $liste_comptes_comptable[] = $value1["compte"];
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
                    $DATA["id_ag"] = $global_id_agence;

                    $sql = buildInsertQuery("ad_mouvement",$DATA);
                    $result = $db->query($sql);
                    if (DB::isError($result)) {
                        $dbHandler->closeConnection(false);
//                        echo "Erreur dans la fonction (ajout_historique) Instruction SQL --> $sql\n";
                        return new ErrorObj (ERR_DB_SQL, _("Fonction ajout_historique:  $sql"));
                    }
                }
            }
        }
    }

    // #357 - verification de l'equilibre comptable
    /**
     * @todo : decomment
     */
    /*
    foreach ($liste_comptes_comptable as $compte_comptable) {
      $MyError = verificationEquilibreComptable($compte_comptable, null, $idhis, $db);
    } */

    $dbHandler->closeConnection(true);

    //Frais transactionnel SMS sur ecran
    if($appli == 'main' && $array_comptable != NULL){
        $fraisReduced = preleveFraisTransactionnelSMS($array_comptable, $type_fonction, $id_doss, $source);
    }
    //Fin Frais transactionnel SMS

// MSQ mouvement
    if(isMSQEnabled() && $appli == 'main' && $array_comptable != NULL){
        envoi_sms_mouvement($array_comptable);
    }
// Fin MSQ mouvement

    return new ErrorObj(NO_ERR, $idhis, null, $array_comptable);
}
/**
 *
 * @author b&d
 *
 * Renseigne le champ num_cpte_comptable pour le compte dans ad_cpt
 * Ce champ garde le compte comptable qui est associé au compte interne.
 * Le compte comptable differe selon le id_prod. voir cas dans le code.
 *
 *
 * trac #357 - équilibre inventaire - comptabilité
 *
 * @param int $id_cpte
 * @param $db
 * @return void|boolean|ErrorObj
 */
function setNumCpteComptableForCompte($id_cpte, &$db)
{
    global $error, $global_id_agence, $dbHandler;

    $num_cpte_comptable = NULL;

    // validation
    if (empty($id_cpte)) {
        return false; // le compte interne n'est pas defini, on ne fait rien
    }

    //verification de l'existance du compte interne:
    $sql = "SELECT count(*) FROM ad_cpt WHERE id_ag = $global_id_agence AND id_cpte = '$id_cpte'";
    $result = $db->query ($sql);

    if (DB::isError ($result)) {
        $dbHandler->closeConnection (false);
//        echo "Erreur dans la fonction (setNumCpteComptableForCompte) DB: ".$result->getMessage()."\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction setNumCpteComptableForCompte: ".$result->getMessage()));
//        signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
    }
    $count = $result->fetchrow();

    if ($count == 0) {
        return false; // le compte interne n'est pas defini en base, on ne fait rien
    }

    // recupere le id_prod l'etat compte et la devise du compte
    $sql = "SELECT id_prod, etat_cpte, devise FROM ad_cpt c WHERE c.id_ag = $global_id_agence AND c.id_cpte = '$id_cpte'";
    $result = $db->query ($sql);

    if (DB::isError ($result)) {
        $dbHandler->closeConnection (false);
//        echo "Erreur dans la fonction (setNumCpteComptableForCompte) DB: ".$result->getMessage()."\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction setNumCpteComptableForCompte: ".$result->getMessage()));
    }

    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $id_prod = $row['id_prod'];
    $etat_cpte = $row['etat_cpte'];
    $devise = $row['devise'];

    // If id_prod defined
    if(!empty($id_prod))
    {
        if($etat_cpte == 2) { // compte fermée, set num_cpte_comptable a NULL
            $num_cpte_comptable = NULL;
        }
        else if($id_prod == 1 || $id_prod > 5) // Depot à vue ou dépot / compte à terme :
        {
            // compte à l'état dormant (etat_cpte=4) qui sont déclassés sur le compte comptable de l'operation 170
            if($etat_cpte == 4) {
                $sql = "SELECT num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = 170 AND sens = 'c' AND id_ag = $global_id_agence;";
                $result = $db->query ($sql);
                if (DB::isError ($result)) {
                    $dbHandler->closeConnection (false);
//                    echo "Erreur dans la fonction (setNumCpteComptableForCompte) DB: ".$result->getMessage()."\n";
                    return new ErrorObj (ERR_DB_SQL, _("Fonction setNumCpteComptableForCompte: ".$result->getMessage()));
                }
                $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
                $num_cpte_comptable = $row['num_cpte'];
            }
            else { // Depot à vue ou dépot / compte à terme OUVERTS
                $sql = "SELECT cpte_cpta_prod_ep FROM adsys_produit_epargne WHERE id = $id_prod AND id_ag = $global_id_agence AND devise = '$devise';";

                $result = $db->query ($sql);
                if (DB::isError ($result)) {
                    $dbHandler->closeConnection (false);
//                    echo "Erreur dans la fonction (setNumCpteComptableForCompte) DB: ".$result->getMessage()."\n";
                    return new ErrorObj (ERR_DB_SQL, _("Fonction setNumCpteComptableForCompte: ".$result->getMessage()));
                }
                $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
                $num_cpte_comptable = $row['cpte_cpta_prod_ep'];
            }
        }
        elseif($id_prod == 3) // comptes de crédit
        {
            $sql = "SELECT etat_cpte.num_cpte_comptable
					FROM adsys_etat_credit_cptes etat_cpte, ad_dcr doss 
					WHERE doss.cre_id_cpte = $id_cpte
					AND etat_cpte.id_prod_cre = doss.id_prod 
					AND etat_cpte.id_etat_credit = doss.cre_etat
					AND etat_cpte.id_ag = $global_id_agence
					AND doss.id_ag = $global_id_agence
		    		AND doss.cre_etat IS NOT NULL;"; // Les fonds sont deboursés

            $result = $db->query ($sql);
            if (DB::isError ($result)) {
                $dbHandler->closeConnection (false);
//                echo "Erreur dans la fonction (setNumCpteComptableForCompte) DB: ".$result->getMessage()."\n";
                return new ErrorObj (ERR_DB_SQL, _("Fonction setNumCpteComptableForCompte: ".$result->getMessage()));
            }
            $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

            if (empty($row)) {
                return false;
            }

            $num_cpte_comptable = $row['num_cpte_comptable'];
        }
        elseif($id_prod == 4) // comptes de garantie
        {
            $sql = "SELECT prod.cpte_cpta_prod_cr_gar 
					FROM adsys_produit_credit prod, ad_dcr doss, ad_gar gar
					WHERE gar.gar_num_id_cpte_nantie = $id_cpte
					AND gar.type_gar = 1
					AND gar.id_doss = doss.id_doss
					AND doss.id_prod = prod.id
					AND prod.id_ag = $global_id_agence
					AND doss.id_ag = $global_id_agence
					AND gar.id_ag = $global_id_agence
					AND prod.cpte_cpta_prod_cr_gar IS NOT NULL 
					AND doss.cre_etat IS NOT NULL;"; // Les fonds sont deboursés

            $result = $db->query ($sql);
            if (DB::isError ($result)) {
                $dbHandler->closeConnection (false);
//                echo "Erreur dans la fonction (setNumCpteComptableForCompte) DB: ".$result->getMessage()."\n";
                return new ErrorObj (ERR_DB_SQL, _("Fonction setNumCpteComptableForCompte: ".$result->getMessage()));
            }
            $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

            if (empty($row)) {
//                return ; //le compte comptable n'est pas defini, on ne met pas a jour num_cpte_comptable
                return new ErrorObj (ERR_GENERIQUE, _("Fonction setNumCpteComptableForCompte: le compte comptable n'est pas defini, on ne met pas a jour num_cpte_comptable"));
            }
            $num_cpte_comptable = $row['cpte_cpta_prod_cr_gar'];
        }
    }

    // Update the num_cpte_comptable column in ad_cpt

    if(empty($num_cpte_comptable)) {
        $sql = "UPDATE ad_cpt SET num_cpte_comptable = NULL WHERE id_cpte = $id_cpte;";
    }
    else {
        $sql = "UPDATE ad_cpt SET num_cpte_comptable = '$num_cpte_comptable' WHERE id_cpte = $id_cpte;";
    }

    $result = $db->query ($sql);
    if (DB::isError ($result)) {
        $dbHandler->closeConnection (false);
//        echo "Erreur dans la fonction (setNumCpteComptableForCompte) DB: ".$result->getMessage()."\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction setNumCpteComptableForCompte: ".$result->getMessage()));
    }

    return new ErrorObj(NO_ERR, $num_cpte_comptable);
}

// -------------------------------Mise à jour d'un dossier de crédit--------------------------------------//
function updateCredit($id_doss, $Fields)
{
    /*
     * Met à jour le dossier de crédit référencé par $id_doss Les champs seront remplacés par ceux présents dans $Fields
     */
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection ();
    $Where ["id_doss"] = $id_doss;
    $Where ["id_ag"] = $global_id_agence;
    $sql = buildUpdateQuery ( "ad_dcr", $Fields, $Where );
    
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection ( false );
//        echo "Erreur dans la fonction (updateCredit) Instruction SQL --> $sql\n";
//        signalErreur( __FILE__, __LINE__, __FUNCTION__, _ ( "Erreur dans la requête SQL" ) . " : " . $sql );
        return new ErrorObj (ERR_DB_SQL, _("Fonction updateCredit: $sql"));
    }

    // #357 : équilibre inventaire - comptabilité
    // Update le num_cpt comptable pour le compte interne associe au produit de credit
    $sql = "SELECT cre_id_cpte FROM ad_dcr WHERE id_doss = $id_doss";

    $result = $db->query ( $sql );
    if (DB::isError ( $result )) {
        $dbHandler->closeConnection ( false );
//        echo "Erreur dans la fonction (updateCredit) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction updateCredit: ".$result->getMessage ()));
    }
    $row = $result->fetchrow (DB_FETCHMODE_ASSOC);

    $cre_id_cpte = $row['cre_id_cpte'];
    $myErr = setNumCpteComptableForCompte ($cre_id_cpte, $db);

    // Update le num_cpt comptable pour le compte interne associe au garantie
    $sql = "SELECT gar_num_id_cpte_nantie FROM ad_gar WHERE id_doss = $id_doss";
    $result = $db->query ( $sql );
    if (DB::isError ( $result )) {
        $dbHandler->closeConnection ( false );
//        echo "Erreur dans la fonction (updateCredit) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction updateCredit: ".$result->getMessage ()));
    }
    $row = $result->fetchrow (DB_FETCHMODE_ASSOC);

    $gar_num_id_cpte_nantie = $row['gar_num_id_cpte_nantie'];
    $myErr = setNumCpteComptableForCompte ($gar_num_id_cpte_nantie, $db);
    // #357 fin : équilibre inventaire - comptabilité

    $dbHandler->closeConnection ( true );
    return true;
}
/**
 * Rejette de dossiers de crédit en attente de décision ou en attente de rééchelonnement
 * @param array $DATA tableau sur les dossiers à rejeter
 * @param array $GARANTIE tableau contenant les garanties mobilisées par dossier
 */
function rejetDossierUSSD($DATA, $func_sys_rejet_doss = 115) {
    /* Met à jour le dossier de crédit (etat=rejeté)
       Toutes les informations nécessaires se trouvent dans DATA
       Déblocage des garanties - : Toutes les informations nécessaires se trouvent dans GARANTIE
       Valeurs de retour :
       1 si OK
       Die si refus de la base de données
    */

    global $dbHandler;
    $db = $dbHandler->openConnection();

    foreach($DATA as $id_doss => $valeur) {
        if ($valeur['last_etat'] == 1) {
            unset($valeur['last_etat']);
            // Mise à jour du dossier un crédit
            updateCredit($id_doss,$valeur);

            global  $global_nom_login;
            ajout_historique($func_sys_rejet_doss, $valeur['id_client'], $id_doss, $global_nom_login, date("r"),NULL);
        }
    }

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
}
/**
 * Etablit la devise avec laquelle on travaille
 * Met ?à jour les variables $global_monnaie_courante et $global_monnaie_courante_prec
 * @param $devise char(3) Code ISO de la devise
 * @return void
 */
function setMonnaieCourante($devise) {
    global $global_monnaie_courante;
    global $global_monnaie_courante_prec;

    if ($devise == NULL) { // Utile pour des écrans dans lesquels la devise n'est pas fixe
        $global_monnaie_courante = NULL;
        $global_monnaie_courante_prec = 0;
    } else {
        $DEV = getInfoDevise($devise);
        $global_monnaie_courante = $devise;
        $global_monnaie_courante_prec = $DEV["precision"];
    }
}
// Générer le numéro de compte avec Id agence
function hasCpteCmpltAgc($global_id_agence){

    global $dbHandler;

    $db = $dbHandler->openConnection();

    $sql = "SELECT has_cpte_cmplt_agc FROM ad_agc WHERE id_ag=$global_id_agence";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__);
//        echo "Erreur dans la fonction (hasCpteCmpltAgc) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction hasCpteCmpltAgc: $sql"));
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        return NULL;
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    return ($DATAS["has_cpte_cmplt_agc"]=='t'?TRUE:FALSE);
}
// Formattage du numéro de compte
function formatCpteCmpltAgc($id_cli) {

    global $global_id_agence;

    $new_id_cli = $id_cli;

    if(hasCpteCmpltAgc($global_id_agence)) {
        $new_id_cli = sprintf("%02d%08d", $global_id_agence, $id_cli);
    }

    return $new_id_cli;
}
/**
 * Fabrique un numéro de compte à partir du id client et éventullement du rang
 * @author Mamadou Mbaye
 * @param int $id_cli ID du client titulaire
 * @param int $rang Rang du compte (uniquement si type de numérotation = RDC)
 * @return text Numéro de compte
 */
function makeNumCpte($id_cli, $rang=NULL) {
    global $global_id_agence,$dbHandler,$db;
    $db = $dbHandler->openConnection();
    $DATA = getAgenceDatas($global_id_agence);
    if ($rang==NULL)
        $rang = getRangDisponible($id_cli);
    if ($DATA["type_numerotation_compte"] == 1) {
        // Crée un numéro de compte au format AA-CCCCCC-RR-DD à partir du rang (R) et de l'ID client (C)
        $NumCompletCompte = sprintf("%03d-%06d-%02d", $global_id_agence, $id_cli, $rang);
        $Entier = sprintf("%03d%06d%02d", $global_id_agence, $id_cli, $rang);
        $CheckDigit = fmod($Entier, 97);
        $NumCompletCompte .= sprintf("-%02d", $CheckDigit);
    } else if ($DATA["type_numerotation_compte"] == 2)
    {
        // Crée un numéro de compte au format BBVV-CCCCCRR-DD à partir du rang (R) et de l'ID client (C) pour la RDC
        $NumCompletCompte = sprintf("%02d%02d-%05d%02d", $DATA["code_banque"], $DATA["code_ville"], $id_cli, $rang);
        $Entier = sprintf("%02d%02d%05d%02d", $DATA["code_banque"], $DATA["code_ville"], $id_cli, $rang);
        $CheckDigit = fmod($Entier, 97);
        $NumCompletCompte .= sprintf("-%02d", $CheckDigit);
    } else if ($DATA["type_numerotation_compte"] == 3) {
        // Crée un numéro de compte au format BBB-CCCCCCCCCC-RR à partir du rang (R) et de l'ID client (C) pour le Rwanda
        $NumCompletCompte = sprintf("%03d-%010d-%02d", $DATA["code_banque"], formatCpteCmpltAgc($id_cli), $rang);
    } else if ($DATA["type_numerotation_compte"] == 4) {
        // Crée un numéro de compte au format AA-CCCCCC-RR-DD à partir du rang (R) et de l'ID client (C)
        $numAntenne=$DATA['code_antenne'];
        if ($numAntenne!= '0' && $numAntenne!= NULL) {
            $NumCompletCompte=$numAntenne.$global_id_agence;
            $Entier =$numAntenne.$global_id_agence;
        } else {
            $NumCompletCompte=$global_id_agence;
            $Entier =$global_id_agence;

        }
        $NumCompletCompte .= sprintf("-%06d-%02d", $id_cli, $rang);
        $Entier .= sprintf("%06d%02d", $id_cli, $rang);
        $CheckDigit = fmod($Entier, 97);
        $NumCompletCompte .= sprintf("-%02d", $CheckDigit);
    } else {
        $message=_("Rang non défini");
//        signalErreur(__FILE__,__LINE__,__FUNCTION__); // $message
//        echo "Erreur dans la fonction (hasCpteCmpltAgc) : $message\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction makeNumCpte: $message"));
    }

    $dbHandler->closeConnection(true);
    return $NumCompletCompte;
}
/**
 * Récupère le premier rang disponible pour un client donné
 * @author Mamadou Mbaye
 * @param int $id_client Numéro de client
 */
function getRangDisponible($id_client) {
    global $dbHandler, $global_id_agence;
    global $db;
    /* NB: Cette fonction ne doit pas ouvrir une connexion à la BD sinon ne marchera pas correctement dans le cas de la création de comptes imbriquées . Elle doit donc être appelée dans une autre qui a ouverte une connexion BD. */

    $sql = "SELECT num_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_titulaire = $id_client;";
    $result=$db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
//        echo "Erreur dans la fonction (getRangDisponible) : ".$result->getMessage()."\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getRangDisponible: ".$result->getMessage()));
    }

    // $RANGS va contenir tous les rangs déjà occupés
    $RANGS = array();
    while ($row = $result->fetchrow()) {
        array_push($RANGS, $row[0]);
    }

    for ($i = 0; $i < 1000; $i++) {
        if (in_array($i, $RANGS) == false) {
            return $i;
        }
    }

//    echo "Erreur dans la fonction (getRangDisponible) : Plus de rangs disponibles\n"; // "Plus de rangs disponibles"
    return new ErrorObj (ERR_GENERIQUE, _("Fonction getRangDisponible: Plus de rangs disponibles"));
}
/**
 * Ajouter un mandat dans la base de donnée
 * @author Antoine Guyette
 * @param Array $DATA données sur le mandat
 * @return ErrorObj
 */
function ajouterMandat($DATA) {
    global $dbHandler,$global_id_agence, $global_nom_login, $global_id_client;
    $db = $dbHandler->openConnection();

    $DATA['valide'] = 't';
    $DATA['id_ag'] = $global_id_agence;

    $sql = buildInsertQuery('ad_mandat', $DATA);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (ajouterMandat) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction ajouterMandat: $sql"));
    }

    // Enregistrement - Ajout d'un mandat
    ajout_historique(95, $global_id_client, 'Ajout d\'un mandat', $global_nom_login, date("r"), NULL);

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
}

/**
 * Créer un compte d'épargne dans ad_cpt
 *
 * @param Array $DATA
 *        	Tableau contenant les valeurs pour tous les champs de la table
 *        	NB Un champ type_cpt_vers_int peut y etre placé qui, s'il est à 1, indiquera que c'est le compte lui-meme sur lequel les intérets seront versés
 * @return integer Le numéro du compte créé.
 */
function creationCompte($DATA) {
    global $dbHandler, $global_id_agence;
    global $db;

    $type_cpt_vers_int = $DATA ["type_cpt_vers_int"];
    unset ( $DATA ["type_cpt_vers_int"] );
    $DATA ['id_ag'] = $global_id_agence;
    $sql = buildInsertQuery ( "ad_cpt", $DATA );

    $result = $db->query ( $sql );
    if (DB::isError ( $result )) {
        $dbHandler->closeConnection ( false );
//        signalErreur ( __FILE__, __LINE__, __FUNCTION__, $result->getMessage () );
//        echo "Erreur dans la fonction (creationCompte) : ".$result->getMessage()."\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction creationCompte: ".$result->getMessage ()));
    }

    $sql = "SELECT currval('ad_cpt_id_cpte_seq')";
    $result = $db->query ( $sql );
    if (DB::isError ( $result )) {
        $dbHandler->closeConnection ( false );
//        signalErreur ( __FILE__, __LINE__, __FUNCTION__, $result->getMessage () );
//        echo "Erreur dans la fonction (creationCompte) : ".$result->getMessage()."\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction creationCompte: ".$result->getMessage ()));
    }
    $row = $result->fetchrow ();
    $id_cpte = $row [0];

    if ($type_cpt_vers_int == 1) {
        $sql = "UPDATE ad_cpt SET cpt_vers_int = id_cpte WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
        $result = $db->query ( $sql );
        if (DB::isError ( $result )) {
            $dbHandler->closeConnection ( false );
//            signalErreur ( __FILE__, __LINE__, __FUNCTION__, $result->getMessage () );
//            echo "Erreur dans la fonction (creationCompte) : ".$result->getMessage()."\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction creationCompte: ".$result->getMessage ()));
        }
    }

    // #357 mis-a-jour champ compte comptable
    $id_prod = $DATA['id_prod'];
    if(!empty($id_prod) && ($id_prod != 3 || $id_prod != 4)) { // pour les comptes a vue seulement
        $myErr = setNumCpteComptableForCompte($id_cpte, $db);
    }

    $sql = "SELECT a.id_prod, b.statut_juridique, c.id_pers_ext FROM ad_cpt a, ad_cli b, ad_pers_ext c WHERE a.id_ag = $global_id_agence AND a.id_ag = b.id_ag AND a.id_ag = c.id_ag AND a.id_cpte = $id_cpte AND a.id_titulaire = b.id_client AND b.id_client = c.id_client;";
    $result = $db->query ( $sql );
    if (DB::isError ( $result )) {
        $dbHandler->closeConnection ( false );
//        signalErreur ( __FILE__, __LINE__, __FUNCTION__ );
//        echo "Erreur dans la fonction (creationCompte) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction creationCompte: ".$result->getMessage ()));
    }
    $row = $result->fetchrow ();

    $id_prod = $row [0];
    $statut_juridique = $row [1];
    $id_pers_ext = $row [2];

    if (! in_array ( $id_prod, array (
            2,
            3,
            4
        ) ) && $statut_juridique == 1) {
        $MANDAT ['id_cpte'] = $id_cpte;
        $MANDAT ['id_pers_ext'] = $id_pers_ext;
        $MANDAT ['type_pouv_sign'] = 1;
        ajouterMandat ( $MANDAT );
    }

    return $id_cpte;
}
function insereSre($DATA) {
    /* Insère un nouvel echancier dans la base de données.
       Toutes les informations nécessaires se trouvent dans DATA qui est un tableau associaltif
       Valeurs de retour :
       1 si OK
       Die si refus de la base de données
    */
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();
    $DATA['id_ag']= $global_id_agence;
    $sql = buildInsertQuery ("ad_sre", $DATA);
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
//        echo "Erreur dans la fonction (insereSre) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction insereSre: $sql"));
    }
    $dbHandler->closeConnection(true);
    return 1;
}
function insereEcheancier($DATA) {
    /* Insère un nouvel echancier dans la base de données.
       Toutes les informations nécessaires se trouvent dans DATA qui est un tableau associaltif
       Valeurs de retour :
       1 si OK
       Die si refus de la base de données
    */
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();
    $DATA['id_ag']= $global_id_agence;
    $sql = buildInsertQuery ("ad_etr", $DATA);
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
//        echo "Erreur dans la fonction (insereEcheancier) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction insereEcheancier: $sql"));
    }
    $dbHandler->closeConnection(true);
    return 1;
}
/**
 * Renvoie le compte comptable associé à un compte client donné.
 *
 * Dans le cas d'un compte d'épargne nantie, on remonte jusqu'au produit de crédit.
 * @param int $id_cpte_cli Id du compte client associé
 * @return text Numéro du compte comptable associé
 */
function getCompteCptaProdEp($id_cpte_cli) {
    global $dbHandler, $global_id_agence, $erreur;

    $db = $dbHandler->openConnection();

    if(($id_cpte_cli == null) or ($id_cpte_cli == '')){
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Le compte interne du client n'est pas renseigné.")));
//        echo "Erreur dans la fonction (getCompteCptaProdEp) : Le compte interne du client n'est pas renseigné\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction getCompteCptaProdEp: Le compte interne du client n'est pas renseigné"));
    } else {
        $sql = "SELECT b.id, b.cpte_cpta_prod_ep ";
        $sql .= "FROM ad_cpt a, adsys_produit_epargne b  ";
        $sql .= "WHERE b.id_ag = $global_id_agence AND b.id_ag = a.id_ag AND a.id_prod = b.id AND a.id_cpte='$id_cpte_cli'";
    }
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, _("DB").": ".$result->getMessage());
//        echo "Erreur dans la fonction (getCompteCptaProdEp) : ".$result->getMessage()."\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getCompteCptaProdEp: ".$result->getMessage()));
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Aucun compte associé. Veuillez revoir le paramétrage"));
//        echo "Erreur dans la fonction (getCompteCptaProdEp) : Aucun compte associé. Veuillez revoir le paramétrage\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction getCompteCptaProdEp: Aucun compte associé. Veuillez revoir le paramétrage"));
    }

    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

    if ($row['id'] == 4) { // Cas particulier du compte d'épargne nantie
        $sql = "SELECT cpte_cpta_prod_cr_gar from adsys_produit_credit a, ad_dcr b where b.id_ag = $global_id_agence AND b.id_ag = a.id_ag AND a.id = b.id_prod AND ";
        $sql .= "b.id_doss = (SELECT distinct(id_doss) FROM ad_gar WHERE id_ag = $global_id_agence AND gar_num_id_cpte_nantie = $id_cpte_cli)";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
//            signalErreur(__FILE__, __LINE__, __FUNCTION__, "DB: ".$result->getMessage());
//            echo "Erreur dans la fonction (getCompteCptaProdEp) : ".$result->getMessage()."\n";
            return new ErrorObj (ERR_DB_SQL, _("Fonction getCompteCptaProdEp: ".$result->getMessage()));
        }

        $row = $result->fetchrow();
        $dbHandler->closeConnection(true);
        return $row[0];
    } else {
        $dbHandler->closeConnection(true);
        return $row["cpte_cpta_prod_ep"];
    }
}
function getTauxChange($devise1, $devise2, $commercial, $type=NULL) {
    // Recherche infos devise 1
    $DEV1 = getInfoDevise($devise1);
    if (!is_array($DEV1)) { // La devise 1 n'existe pas
        return NULL;
    }

    // Recherche infos devise 2
    $DEV2 = getInfoDevise($devise2);
    if (!is_array($DEV2)) { // La devise 2 n'existe pas
        return NULL;
    }

    if (!$commercial) { // C'est le taux indicatif qui dpoit etre utilisé
        $field_taux1 = "taux_indicatif";
        $field_taux2 = "taux_indicatif";
    } else { // On prend le taux achat de $devise1 et le taux vente de $devise2 pour maximiser le bénéfice
        if ($type == 1) { // CASH
            $field_taux1 = "taux_achat_cash";
            $field_taux2 = "taux_vente_cash";
        } else if ($type == 2) { // TRANSFERT
            $field_taux1 = "taux_achat_trf";
            $field_taux2 = "taux_vente_trf";
        }
    }

    // Calcul du taux réel
    $taux_change = round($DEV2[$field_taux2] / $DEV1[$field_taux1], 12);
    return $taux_change;
}
function calculeCV($devise1, $devise2, $montant) {

    if ($devise1 == $devise2)
        return $montant;

    $taux = getTauxChange($devise1, $devise2, false);
    $cv_montant = $montant * $taux;
    $DEV = getInfoDevise($devise2);
    $cv_montant = round($cv_montant, $DEV["precision"]);
    return $cv_montant;
}
function getCptesLies($devise) {
    global $global_id_agence;
    $comptes=array();
    $AG = getAgenceDatas($global_id_agence);
    $cpt_pos_ch = $AG["cpte_position_change"];
    $cpt_cv_pos_ch = $AG["cpte_contreval_position_change"];
    $cpt_credit = $AG["cpte_variation_taux_cred"];
    $cpt_debit = $AG["cpte_variation_taux_deb"];
    $comptes['position']=$cpt_pos_ch.".".$devise;
    $comptes['cvPosition']=$cpt_cv_pos_ch.".".$devise;
    $comptes['debit']=$cpt_debit.".".$devise;
    $comptes['credit']=$cpt_credit.".".$devise;
    return $comptes;
}
function effectueChangePrivate ($devise_achat, $devise_vente, $montant, $type_oper, $subst, &$comptable, $mnt_debit=true, $cv=NULL, $info_ecriture=NULL, $infos_sup=NULL, $date_comptable = NULL) {


    global $dbHandler;
    $db = $dbHandler->openConnection();
    // Vérifie que les devises sont renseignées
    if ($devise_achat == '' || $devise_vente == '') {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Devises non renseignées"));
//        echo "Erreur dans la fonction (effectueChangePrivate) : Devises non renseignées\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction effectueChangePrivate: Devises non renseignées"));
    }
    if ($devise_achat == $devise_vente) {
        // Pas d'opération de change à réaliser
        $result = passageEcrituresComptablesAuto($type_oper, $montant, $comptable, $subst, $devise_achat, $date_comptable, $info_ecriture, $infos_sup);
        $montant_debit = $montant;
        $montant_credit = $montant;
    } else {
        if ($mnt_debit == true) {
            // $montant représente un montant à débiter en $devise_achat
            $montant_debit = $montant;
            if ($cv == NULL)
                $montant_credit = calculeCV($devise_achat, $devise_vente, $montant);
            else
                $montant_credit = $cv;
        } else {
            // $montant représente un montant à créditer en $devise_vente
            $montant_credit = $montant;
            if ($cv == NULL)
                $montant_debit = calculeCV($devise_vente, $devise_achat, $montant);
            else
                $montant_debit = $cv;
        }

        // On récupère la devise de référence
        global $global_monnaie;
        $dev_ref = $global_monnaie;

        // Passage des écritures relatives à la devise d'achat
        $cptes = $subst;
        if ($devise_achat != $dev_ref) {
            $cpt_devise=getCptesLies($devise_achat);
            $cptes["cpta"]["credit"] = $cpt_devise['position'];
        } else {
            $cpt_devise=getCptesLies($devise_vente);
            $cptes["cpta"]["credit"] = $cpt_devise['cvPosition'];
        }

        $cptes["int"]["credit"] = NULL;
        $result = passageEcrituresComptablesAuto($type_oper, $montant_debit, $comptable, $cptes, $devise_achat,$date_comptable,$info_ecriture,$infos_sup);
        if ($result->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $result;
        }

        // Passage des écritures relatives à la devise de vente
        $cptes = $subst;
        if ($devise_vente != $dev_ref) {
            $cpt_devise=getCptesLies($devise_vente);
            $cptes["cpta"]["debit"] = $cpt_devise['position'];
        } else {
            $cpt_devise=getCptesLies($devise_achat);
            $cptes["cpta"]["debit"] = $cpt_devise['cvPosition'];
        }
        $cptes["int"]["debit"] = NULL;
        $result = passageEcrituresComptablesAuto($type_oper, $montant_credit, $comptable, $cptes, $devise_vente,$date_comptable,$info_ecriture,$infos_sup);
        if ($result->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $result;
        }

        // Passage des écritures relatives à la devise de référence (intermédiaire)
        if (($devise_achat != $dev_ref) && ($devise_vente != $dev_ref)) {
            // Recherche de la CV en devise de référence
            $cv_montant_dev_ref = calculeCV($devise_achat, $dev_ref, $montant_debit);
            $cptes = $subst;
            $cpt_devise=getCptesLies($devise_achat);
            $cptes["cpta"]["debit"] = $cpt_devise['cvPosition'];
            $cptes["int"]["debit"] = NULL;
            $cpt_devise=getCptesLies($devise_vente);
            $cptes["cpta"]["credit"] = $cpt_devise['cvPosition'];
            $cptes["int"]["credit"] = NULL;
            $result = passageEcrituresComptablesAuto($type_oper, $cv_montant_dev_ref, $comptable, $cptes, $dev_ref,$date_comptable,$info_ecriture,$infos_sup);
        }
    }

    if ($result->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $result;
    }

    // Préparation des valeurs de retour
    $param_result = array("montant_debit" => $montant_debit, "montant_credit" => $montant_credit);
    $result = new ErrorObj(NO_ERR, $param_result);
    $dbHandler->closeConnection(true);
    return $result;
}
function getTaxesOperation($type_oper=NULL) {
    global $dbHandler, $global_id_agence, $global_langue_systeme_dft;
    $db = $dbHandler->openConnection();

    // récupération des taxes appliquées à l'opération
    $sql = "SELECT a.type_taxe, a.id_taxe, a.type_oper, traduction(b.libel,  '$global_langue_systeme_dft') as libel_taxe, b.taux, b.cpte_tax_col, b.cpte_tax_ded from ad_oper_taxe a , adsys_taxes b where a.id_ag = b.id_ag and b.id_ag = $global_id_agence and a.id_taxe = b.id ";
    if ($type_oper != NULL)
        $sql .= "and a.type_oper = $type_oper ";
    $result = $db->query($sql);
    $dbHandler->closeConnection(true);

    if (DB::isError($result)) {
//        echo "Erreur dans la fonction (getCompteCptaProdEp) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getTaxesOperation: $sql"));
    }
    $taxes = array();
    if ($result->numRows() == 0) {
        return new ErrorObj(NO_ERR, $taxes);
    }
    else{
        while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
            $taxes[$row["type_taxe"]] = array("type_taxe"=>$row["type_taxe"], "id_taxe"=>$row["id_taxe"], "libel_taxe"=>$row["libel_taxe"],
                "taux"=>$row["taux"], "cpte_tax_col"=>$row["cpte_tax_col"], "cpte_tax_ded"=>$row["cpte_tax_ded"]);
        }
    }
    return new ErrorObj(NO_ERR, $taxes);
}
function reglementTaxe($type_operation, $montant, $sens, $devise, $cptes_substitue, &$comptable){
    global $dbHandler, $global_id_agence, $global_monnaie, $global_id_exo, $global_nom_login;
    $db = $dbHandler->openConnection();
    $taxesOperation = getTaxesOperation($type_operation);
    $details_taxesOperation = $taxesOperation->param;
    if (sizeof($details_taxesOperation) > 0){
        $subst_tva = array();
        $subst_tva["cpta"] = array();
        $subst_tva["int"] = array();
        if ($sens == SENS_DEBIT) {
            $devise_debit_tax = $global_monnaie;
            $devise_credit_tax = $devise;
            $mnt_debit = false;
            $type_oper_tax = 473;//paiement tva récupérable
            $subst_tva["cpta"]["debit"] = $details_taxesOperation[1]["cpte_tax_ded"];
            if ($subst_tva["cpta"]["debit"] == NULL){
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé à la taxe récupérable: ").$details_taxesOperation[1]["libel_taxe"]);
            }
            $subst_tva["cpta"]["credit"] = $cptes_substitue["cpta"]["credit"];
            $subst_tva["int"]["credit"] = $cptes_substitue["int"]["credit"];

        } else {
            $devise_debit_tax = $devise;
            $devise_credit_tax = $global_monnaie;
            $mnt_debit = true;
            $type_oper_tax = 474;//perception tva collectée
            $subst_tva["cpta"]["debit"] = $cptes_substitue["cpta"]["debit"];
            $subst_tva["int"]["debit"] = $cptes_substitue["int"]["debit"];
            $subst_tva["cpta"]["credit"] = $details_taxesOperation[1]["cpte_tax_col"];
            if ($subst_tva["cpta"]["credit"] == NULL){
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé à la taxe collectée: ").$details_taxesOperation[1]["libel_taxe"]);
            }
        }

        $mnt_tax = $montant * $details_taxesOperation[1]["taux"];
        $myErr = effectueChangePrivate($devise_debit_tax, $devise_credit_tax, $mnt_tax, $type_oper_tax, $subst_tva, $comptable, $mnt_debit);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

    }
    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR, $myErr->param);
}
function recup_compte_etat_credit($id_produit_credit) {
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();
    $retour=array();

    $sql ="SELECT *  FROM adsys_etat_credit_cptes WHERE id_ag=$global_id_agence and id_prod_cre ='".$id_produit_credit."';";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (recup_compte_etat_credit) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction recup_compte_etat_credit: $sql"));
    }

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        $retour[$row["id_etat_credit"]]=$row["num_cpte_comptable"];
    $dbHandler->closeConnection(true);
    return $retour;
}
/**
 * Transforme une date venant de PHP vers le format de Postgres
 * @param str $a_date Date au format jj/mm/aaaa
 * @return str Date au format aaaa-mm-jj
 */
function php2pg($a_date)
{
    if ($a_date == "") return "";
    $J = substr($a_date,0,2);
    $M = substr($a_date,3,2);
    $A = substr($a_date,6,4);
    return "$A-$M-$J";

}
function get_credit_type_oper($operation, $source=NULL) {

    if ($operation == 1) { //remb capital
        if ($source == 1)
            return 10;
        else if ($source == 2 || $source == 3)
            return 10;
    } else if ($operation == 2) { //remb interêt
        if ($source == 1)
            return 20;
        else if ($source == 2)
            return 20;
        else if ($source == 3)
            return 20;
        else if ($source == 4)
            return 375; //remb interêt a recevoir

    } else if ($operation == 3) { //remb penalité
        if ($source == 1)
            return 30;
        else if ($source == 2)
            return 30;
        else if ($source == 3)
            return 20;
    } else if ($operation == 4) {
        // OBSOLETE
    } else if ($operation == 5) {
        // OBSOLETE
    } else if ($operation == 6) {
        return 210;
    } else if ($operation == 7) {
        // OBSOLETE
    } else if (in_array($operation, array(8))) {
        return 390;
    } else if ($operation == 9){ //remb garantie
        return 220;
    } else if ($operation == 10) { //annuler remb capital
        if ($source == 1)
            return 11;
        else if ($source == 2)
            return 11;
    } else if ($operation == 11) { //annuler remb interêt
        if ($source == 1)
            return 21;
        else if ($source == 2 || $source == 3)
            return 21;
    } else if ($operation == 12) { //annuler remb penalité
        if ($source == 1)
            return 31;
        else if ($source == 2 || $source == 3)
            return 31;
    }else if ($operation == 13) { //annuler remb garantie
        if ($source == 1)
            return 221;
        else if ($source == 2 || $source == 3)
            return 221;
    }else if ($operation == 14){
        return 22;
    }


//    signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Paramètres incorrects : operation = %s, source = %s"), $operation, $source));
//    echo "Erreur dans la fonction (get_credit_type_oper) :".sprintf(_("Paramètres incorrects : operation = %s, source = %s"), $operation, $source)."\n";
    return new ErrorObj (ERR_GENERIQUE, _("Fonction get_credit_type_oper: ".sprintf(_("Paramètres incorrects : operation = %s, source = %s"), $operation, $source)));
}
function creationHistoriqueExterieur($data) {
    $data_ext=array();

    $data_ext['type_piece']	= $data['type_piece'];
    $data_ext['remarque']	= $data['remarque'];
    $data_ext['sens']		= $data['sens'];
    $data_ext['num_piece']	= $data['num_piece'];
    $data_ext['date_piece']	= $data['date_piece'];
    $data_ext['communication']	= $data['communication'];
    $data_ext['id_pers_ext']	= $data['id_pers_ext'];
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
            return new ErrorObj (ERR_GENERIQUE, _("Fonction creationHistoriqueExterieur: ligne ".__LINE__));

    }
    if ($data_ext['type_piece']==5) unset($data_ext['id_tireur_benef']);//dans le cas d'un Travelers cheque, pas de tireur/benef

    return $data_ext;
}
function getComptesCompensation($idCorrespondant) {
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();
    $sql = "SELECT cpte_bqe, cpte_ordre_deb, cpte_ordre_cred FROM adsys_correspondant WHERE id_ag = $global_id_agence AND id = $idCorrespondant;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (getComptesCompensation) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getComptesCompensation: $sql"));
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__);
        return new ErrorObj (ERR_DB_SQL, _("Fonction getComptesCompensation: $sql"));
    }
    $row = $result->fetchrow();
    $cptes['compte'] = $row[0];
    $cptes['debit'] = $row[1];
    $cptes['credit'] = $row[2];

    $dbHandler->closeConnection(true);
    return $cptes;
}
function fermeCompte ($id_cpte, $raison_cloture, $solde_cloture, $date_cloture=NULL) {
    /*  $ACC = getAccountDatas($id_cpte);
    if ($ACC["solde"] != $solde_cloture)
      return new ErrorObj(ERR_CPTE_SOLDE_NON_NUL, ($ACC["solde"] - $solde_cloture));
    */

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $fields_array = array();
    $fields_array["etat_cpte"] = 2; // Compte fermé
    $fields_array["raison_clot"] = $raison_cloture;
    if ($date_cloture == NULL)
        $fields_array["date_clot"] = date("d/m/Y");
    else
        $fields_array["date_clot"] = $date_cloture;

    $fields_array["solde_clot"] = $solde_cloture;

    $sql = buildUpdateQuery ("ad_cpt", $fields_array, array("id_cpte"=>$id_cpte,'id_ag'=>$global_id_agence));

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
//        echo "Erreur dans la fonction (fermeCompte) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction fermeCompte: $sql"));
    };

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}
function get_encaisse($id_guichet, $devise = NULL) {
    global $dbHandler, $global_id_agence;
    global $global_multidevise;
    $db = $dbHandler->openConnection();

    $sql = "SELECT cpte_cpta_gui FROM ad_gui WHERE id_ag = ".$global_id_agence." AND id_gui='".$id_guichet."'";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        echo "Erreur dans la fonction (get_encaisse) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction get_encaisse: $sql"));
    }
    $row = $result->fetchrow();
    $id_cpt = $row[0];
    if ($global_multidevise)
        if ($devise)
            $id_cpt.=".". $devise;
    $sql = "SELECT solde FROM ad_cpt_comptable WHERE id_ag = ".$global_id_agence." AND num_cpte_comptable = '".$id_cpt."'";
    if ($global_multidevise)
        if ($devise)
            $id_cpt.=".".$devise;
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__);
        return new ErrorObj (ERR_DB_SQL, _("Fonction get_encaisse: $sql"));
    }
    if ($result->numrows() > 1) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le nombre de guichets est différent de 1 !"
//        echo "Erreur dans la fonction (get_encaisse) : Le nombre de guichets est différent de 1 !\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction get_encaisse: Le nombre de guichets est différent de 1 !"));
    }
    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);

    return (-1 * $row[0]); //compte débiteur => * -1
}
function getCompteCptaGui($id_gui) {
    /*
      Renvoie le compte comptable associé à un guichet
    */

    global $dbHandler, $global_id_client,$global_id_agence, $erreur;
    $db = $dbHandler->openConnection();

    if(($id_gui == null) or ($id_gui == '')){
        erreur("getCompteCptaGui", sprintf(_("Le numéro du guichet n'est pas renseigné.")));
    }else {
        $sql = "SELECT cpte_cpta_gui ";
        $sql .= "FROM ad_gui  ";
        $sql .= "WHERE id_ag = $global_id_agence AND id_gui = '$id_gui'";
    }
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
//        echo "Erreur dans la fonction (getCompteCptaGui) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getCompteCptaGui: $sql"));
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucun compte associé. Veuillez revoir le paramétrage"
//        echo "Erreur dans la fonction (getCompteCptaGui) : Aucun compte associé. Veuillez revoir le paramétrage\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction getCompteCptaGui: Aucun compte associé. Veuillez revoir le paramétrage"));
    }
    $row = $result->fetchrow();
    $cpte_cpta = $row[0];

    $dbHandler->closeConnection(true);
    return $cpte_cpta;

}
function deboursementCreditUSSD($infos_doss, $mode_debour, $dest_debour, $id_guichet, $func_sys_deb_doss = 125)
{
    global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_id_utilisateur;
    global $error;
    global $db;
    // Pour gèrer les garanties de dossier de crédit par un autre client
    $clientTraite = array();
    $arrayNbGarant = array();

    $db = $dbHandler->openConnection();

    $array_his = array(); // Pour enregistrer l'id des historiques
    // Déboursement des dossiers
    foreach ($infos_doss as $id_doss => $val_doss) {
        $comptable = array(); // Mouvements comptable
        $comptableFrais = array();
        $comptablemntCrt = array();

        //init
        $comptableAssurance = array();
        $comptableTaxe1 = array();
        $comptableFrais1 = array();
        $comptableTaxe2 = array();
        $comptableFrais2 = array();

        $DATA = array();
        $is_assurance = false;
        $is_frais_doss = false;
        $is_commission = false;
        // Récupération infos sur le produit de crédit
        $PROD = getProdInfo(" WHERE id = " . $val_doss ['id_prod'], $id_doss);
        $devise_credit = $PROD [0] ["devise"];
        setMonnaieCourante($devise_credit);
        // Mise à jour du montant déboursé
        /*
         * if($val_doss['prelev_commission'] == 't' && $PROD[0]['prelev_frais_doss'] == 2){ $val_doss['transfert_fond']['montant']=$val_doss['transfert_fond']['montant']-$val_doss['mnt_commission']-$val_doss['mnt_assurance']; updateCredit($id_doss,array('cre_mnt_octr'=>$val_doss['transfert_fond']['montant'])); }
         */

        if ($val_doss ['etat'] == 2) { // si c'est le premier déboursement du crédit
            // Création du compte de crédit
            $val_doss ['data_cpt_cre'] ['num_cpte'] = getRangDisponible($val_doss ['id_client']); // récupére un rang disponible
            $val_doss ['data_cpt_cre'] ['num_complet_cpte'] = makeNumCpte($val_doss ['id_client'], $val_doss ['data_cpt_cre'] ['num_cpte']); // numéro complet du compte de crédit
            $id_cpte_cre = creationCompte($val_doss ['data_cpt_cre']);

            /* Création des comptes nanties s'il y a des garanties numéraire pour ce crédit */
            foreach ($val_doss ['DATA_GAR'] as $key => $value) {
                if ($value ['type'] == 1) { // garantie numéraire
                    $cpt_garant = $value ['descr_ou_compte'];
                    $mnt_preleve = $value ["mnt_preleve"];
                    $id_gar = $value ['id_gar'];
                    // Préparation des données à passer à creationCompte()
                    $DATA_CPT_GAR = array();
                    $DATA_CPT_GAR ['devise'] = $value ['devise'];
                    $DATA_CPT_GAR ['utilis_crea'] = $value ['utilis_crea'];
                    $DATA_CPT_GAR ['etat_cpte'] = $value ['etat_cpte'];
                    $DATA_CPT_GAR ['id_titulaire'] = $value ['id_titulaire'];
                    $DATA_CPT_GAR ['date_ouvert'] = $value ['date_ouvert'];
                    $DATA_CPT_GAR ['mnt_bloq'] = $value ['mnt_bloq'];
                    $DATA_CPT_GAR ['id_prod'] = $value ['id_prod'];
                    $DATA_CPT_GAR ['type_cpt_vers_int'] = $value ['type_cpt_vers_int'];
                    $DATA_CPT_GAR ['intitule_compte'] = $value ['intitule_compte'];
                    // Infos du compte de prélèvement
                    $compte_prelev = getAccountDatas($cpt_garant);
                    $DATA_CPT_GAR ['id_titulaire'] = $compte_prelev ['id_titulaire'];
                    // Si les garanties sont prélevés sur un compte d'une autre personne
                    $rang = getRangDisponible($compte_prelev ['id_titulaire']);
                    $DATA_CPT_GAR ['num_cpte'] = $rang;
                    $DATA_CPT_GAR ['num_complet_cpte'] = makeNumCpte($compte_prelev ['id_titulaire'], $rang);
                    // Création du compte d'épargne nantie
                    $id_cpte_en = creationCompte($DATA_CPT_GAR);

                    // Renseigner le compte de garantie dans ad_gar
                    $sql = "UPDATE ad_gar SET gar_num_id_cpte_nantie = $id_cpte_en WHERE id_ag=$global_id_agence AND id_gar = $id_gar";
                    $result = $db->query($sql);
                    if (DB::isError($result)) {
                        $dbHandler->closeConnection(false);
//                        signalErreur(__FILE__, __LINE__, __FUNCTION__);
//                        echo "Erreur dans la fonction (deboursementCreditUSSD) Instruction SQL --> $sql\n";
                        return new ErrorObj (ERR_DB_SQL, _("Fonction deboursementCreditUSSD: $sql"));
                    }

                    // Si garanties bloquées au début alors débloquer le cpte de prélevement et le transferer dans le compte nantie
                    if ($mnt_preleve > 0) {
                        debloqGarantie($cpt_garant, $mnt_preleve);

                        // Tranfert des garanties du compte de prélèvement vers le compte d'épargne nantie
                        $cptes_substitue = array();
                        $cptes_substitue ["cpta"] = array();
                        $cptes_substitue ["int"] = array();

                        // débit compte de prélèvement / crédit compte nantie
                        $cptes_substitue ["cpta"] ["debit"] = getCompteCptaProdEp($cpt_garant);
                        if ($cptes_substitue ["cpta"] ["debit"] == NULL) {
                            $dbHandler->closeConnection(false);
                            return new ErrorObj (ERR_CPTE_NON_PARAM, _("compte comptable associé à la garantie"));
                        }

                        $cptes_substitue ["int"] ["debit"] = $cpt_garant;

                        $cptes_substitue ["cpta"] ["credit"] = $PROD [0] ["cpte_cpta_prod_cr_gar"];
                        $cptes_substitue ["int"] ["credit"] = $id_cpte_en;

                        $myErr = passageEcrituresComptablesAuto(220, $mnt_preleve, $comptable, $cptes_substitue, $devise_credit);
                        if ($myErr->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $myErr;
                        }
                    } // End if ($mnt_preleve >
                } // fin si gar numéraire
            } // fin parcours DATA_GAR

            // Ajout de la garantie encours
            if ($val_doss ['gar_num_encours'] > 0) {
                // Préparation des dosnnées à passer à creationCompte()
                $DATA_CPT_GAR = array();
                $DATA_CPT_GAR ['devise'] = $val_doss ['data_gar_encours'] ['devise'];
                $DATA_CPT_GAR ['utilis_crea'] = $val_doss ['data_gar_encours'] ['utilis_crea'];
                $DATA_CPT_GAR ['etat_cpte'] = $val_doss ['data_gar_encours'] ['etat_cpte'];
                $DATA_CPT_GAR ['id_titulaire'] = $val_doss ['data_gar_encours'] ['id_titulaire'];
                $DATA_CPT_GAR ['date_ouvert'] = $val_doss ['data_gar_encours'] ['date_ouvert'];
                $DATA_CPT_GAR ['mnt_bloq'] = $val_doss ['data_gar_encours'] ['mnt_bloq'];
                $DATA_CPT_GAR ['id_prod'] = $val_doss ['data_gar_encours'] ['id_prod'];
                $DATA_CPT_GAR ['type_cpt_vers_int'] = $val_doss ['data_gar_encours'] ['type_cpt_vers_int'];
                $DATA_CPT_GAR ['intitule_compte'] = $val_doss ['data_gar_encours'] ['intitule_compte'];
                $rang = getRangDisponible($val_doss ['data_gar_encours'] ['id_titulaire']);
                $DATA_CPT_GAR ['num_cpte'] = $rang;
                $DATA_CPT_GAR ['num_complet_cpte'] = makeNumCpte($val_doss ['data_gar_encours'] ['id_titulaire'], $rang);
                // Création du compte d'épargne nantie
                $id_cpte_en = creationCompte($DATA_CPT_GAR);
                $cpt_gar_encours = $id_cpte_en;

                // Insertion de la garantie numéraire à constituer dans la tables des garanties
                $GAR_ENCOURS = array();
                $GAR_ENCOURS ['type_gar'] = 1;
                $GAR_ENCOURS ['id_doss'] = $id_doss;
                $GAR_ENCOURS ['gar_num_id_cpte_prelev'] = NULL;
                $GAR_ENCOURS ['gar_num_id_cpte_nantie'] = $id_cpte_en;
                $GAR_ENCOURS ['etat_gar'] = 1; // En cours de mobilisation
                $GAR_ENCOURS ['montant_vente'] = 0;
                $GAR_ENCOURS ['devise_vente'] = $devise_credit;
                $GAR_ENCOURS ['id_ag'] = $global_id_agence;

                $sql = buildInsertQuery("ad_gar", $GAR_ENCOURS);
                $result = $db->query($sql);
                if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
//                    echo "Erreur dans la fonction (deboursementCreditUSSD) Instruction SQL --> $sql\n";
                    return new ErrorObj (ERR_DB_SQL, _("Fonction deboursementCreditUSSD: $sql"));
                }
            }

            /* Toutes les garanties doivent être à l'état 'Mobilisé' sauf la garanties numéraire à constituer au fil des remboursements */
//            if ($cpt_gar_encours != '')
//                $sql = "UPDATE ad_gar SET etat_gar = 3 WHERE id_ag=$global_id_agence AND id_doss = $id_doss AND gar_num_id_cpte_nantie != $cpt_gar_encours OR type_gar=2";
//            else
//                $sql = "UPDATE ad_gar SET etat_gar = 3 WHERE id_ag=$global_id_agence AND id_doss = $id_doss";
//
//            $result = $db->query($sql);
//            if (DB::isError($result)) {
//                $dbHandler->closeConnection(false);
//                echo "Erreur dans la fonction (deboursementCreditUSSD) Instruction SQL --> $sql\n";
//            }

            // Mise à jour du dossier
            $cre_mnt_deb = $val_doss ['cre_mnt_deb'] + $val_doss ['cre_mnt_a_deb'];
            if (($mode_debour == 2) && ($cre_mnt_deb < $val_doss ['cre_mnt_octr'])) {

                if ($PROD[0]["mode_calc_int"] == 5) {
                    $DATA ['etat'] = 5; // Fonds déboursés
                } else {
                    $DATA ['etat'] = 13; // En déboursement progressif
                }
                $DATA ['cre_mnt_deb'] = $cre_mnt_deb;
            } else {
                $DATA ['etat'] = 5; // Fonds déboursés
                $DATA ['cre_mnt_deb'] = $val_doss ['cre_mnt_octr'];
            }
            $DATA ['date_etat'] = date("d/m/Y"); // Date de passage à l'état déboursé
            $DATA ['cre_date_debloc'] = $val_doss ['cre_date_debloc']; // Date de déblocage des fonds
            $DATA ['cre_etat'] = 1; // Etat du crédit = sain
            $DATA ['cre_date_etat'] = $val_doss ['cre_date_debloc'];
            $DATA ['cre_retard_etat_max'] = 1;
            $DATA ['cre_retard_etat_max_jour'] = 0;
            $DATA ['cre_id_cpte'] = $id_cpte_cre;
            $DATA ['cre_date_approb'] = $val_doss ['cre_date_approb'];
            $DATA ['cre_mnt_octr'] = $val_doss ['cre_mnt_octr'];

            // Récupération du compte des garanties numéraires à constituer en cours
            if (isset ($cpt_gar_encours))
                $DATA ['cpt_gar_encours'] = $cpt_gar_encours;
            // s'il ya assurance a payé'
            if (is_array($val_doss ['transfert_ass']) && $val_doss ["assurances_cre"] == 't') {
                $DATA ['assurances_cre'] = 'f';
                $is_assurance = true;
            }
            // s'il ya commission a payé'
            if (is_array($val_doss ['transfert_com']) && $val_doss ['prelev_commission'] != 't') {
                $DATA ['prelev_commission'] = 't';
                $is_commission = true;
            }
            // s'il ya frais de dossiers a payé'
            if (is_array($val_doss ['transfert_frais']) && $val_doss ['cre_prelev_frais_doss'] != 't') {
                $DATA ['cre_prelev_frais_doss'] = 't';
                $is_frais_doss = true;
            }

            /* Insertion de l'echéancier réel */
            $count_differe_ech = 1;
            while (list ($key, $value) = each($val_doss ['etr'])) {
                if ($count_differe_ech <= $val_doss['differe_ech']) {
                    if ($PROD[0]['calcul_interet_differe'] == 'f') {
                        $value['remb'] = 't';

                        // Faire une insertion dans la table ad_sre, quand le remb est 't'.
                        $date_jour = date("d");
                        $date_mois = date("m");
                        $date_annee = date("Y");
                        $date_jour = $date_jour."/".$date_mois."/".$date_annee;

                        $data_sre = array();
                        $data_sre["id_doss"] = $value['id_doss'];
                        $data_sre["num_remb"] = 1;
                        $data_sre["date_remb"] = $date_jour;
                        $data_sre["id_ech"] = $value['id_ech'];
                        $data_sre["mnt_remb_cap"] = $value['solde_cap'];
                        $data_sre["mnt_remb_int"] = $value['solde_gar'];
                        $data_sre["mnt_remb_pen"] = $value['solde_int'];
                        $data_sre["mnt_remb_pen"] = $value['solde_pen'];
                        insereSre($data_sre);
                    }
                }
                insereEcheancier($value);
                $count_differe_ech++;
            }


            /* Incrémentation du cumul des crédits octroyés au client */
            $sql = "UPDATE ad_cli SET nbre_credits = nbre_credits + 1 WHERE id_ag=$global_id_agence AND id_client=$global_id_client";
            $result = $db->query($sql);
            if (DB::isError($result)) {
                $dbHandler->closeConnection(false);
//                signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
//                echo "Erreur dans la fonction (deboursementCreditUSSD) Instruction SQL --> $sql\n";
                return new ErrorObj (ERR_DB_SQL, _("Fonction deboursementCreditUSSD: $sql"));
            }


            // Transfert du Montant des assurances
            if ($is_assurance) {
                // FIXME : Nous faisons actuellement l'hypothèse que l'assurance se comptabilise dans la devise du crédit

                // Passage des écritures comptables
                $cptes_substitue = array();
                $cptes_substitue ["cpta"] = array();
                $cptes_substitue ["int"] = array();

                // débit compte client / crédit compte d'assurance
                $cptes_substitue ["cpta"] ["debit"] = getCompteCptaProdEp($val_doss ['transfert_ass'] ['id_cpte_cli']);
                if ($cptes_substitue ["cpta"] ["debit"] == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj (ERR_CPTE_NON_PARAM, _("compte comptable du transfert de l'assurance"));
                }

                $cptes_substitue ["int"] ["debit"] = $val_doss ['transfert_ass'] ['id_cpte_cli'];

                global $global_monnaie;
                // Si la devise du crédit n'est pas la devise de référence, mouvementer la position de change
                if ($devise_credit != $global_monnaie) {
                    $myErr = effectueChangePrivate($devise_credit, $global_monnaie, $val_doss ['transfert_ass'] ['mnt_assurance'], 230, $cptes_substitue, $comptableAssurance);
                } else
                    $myErr = passageEcrituresComptablesAuto(230, $val_doss ['transfert_ass'] ['mnt_assurance'], $comptableAssurance, $cptes_substitue, $devise_credit);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                // Donner la possibilité de faire jouer l'assurance
                $sql = "UPDATE ad_dcr SET assurances_cre = 'f' WHERE id_ag=$global_id_agence AND id_doss = $id_doss";
                $result = $db->query($sql);
                if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
//                    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
//                    echo "Erreur dans la fonction (deboursementCreditUSSD) Instruction SQL --> $sql\n";
                    return new ErrorObj (ERR_DB_SQL, _("Fonction deboursementCreditUSSD: $sql"));
                }
            }

            // Transfert éventuel des commissions
            if ($is_commission) {

                // Passage des écritures comptables
                $cptes_substitue = array();
                $cptes_substitue ["cpta"] = array();
                $cptes_substitue ["int"] = array();

                // débit compte client / crédit compte de produit
                $cptes_substitue ["cpta"] ["debit"] = getCompteCptaProdEp($val_doss ['transfert_com'] ['id_cpte_cli']);
                if ($cptes_substitue ["cpta"] ["debit"] == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj (ERR_CPTE_NON_PARAM, _("compte comptable des commissions"));
                }

                $cptes_substitue ["int"] ["debit"] = $val_doss ['transfert_com'] ['id_cpte_cli'];

                // perception des éventuelles taxes sur les commissions
                $myErr = reglementTaxe(360, $val_doss ['transfert_com'] ['mnt_commission'], SENS_CREDIT, $devise_credit, $cptes_substitue, $comptableTaxe1);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                global $global_monnaie;
                // Si la devise du crédit n'est pas la devise de référence, mouvementer la position de change
                if ($devise_credit != $global_monnaie) {
                    //$myErr = effectueChangePrivate($devise_credit, $global_monnaie, $val_doss ['transfert_com'] ['mnt_commission'], 360, $cptes_substitue, $comptableFrais);
                    $myErr = effectueChangePrivate($devise_credit, $global_monnaie, $val_doss ['transfert_com'] ['mnt_commission'], 360, $cptes_substitue, $comptableFrais1);
                } else
                    //$myErr = passageEcrituresComptablesAuto(360, $val_doss ['transfert_com'] ['mnt_commission'], $comptableFrais, $cptes_substitue, $devise_credit);
                    $myErr = passageEcrituresComptablesAuto(360, $val_doss ['transfert_com'] ['mnt_commission'], $comptableFrais1, $cptes_substitue, $devise_credit);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }

            // Transfert des frais de dossier
            if ($is_frais_doss) {

                // Passage des écritures comptables
                $cptes_substitue = array();
                $cptes_substitue ["cpta"] = array();
                $cptes_substitue ["int"] = array();

                // débit compte client / crédit compte de produit
                $cptes_substitue ["cpta"] ["debit"] = getCompteCptaProdEp($val_doss ['transfert_frais'] ['id_cpte_cli']);
                if ($cptes_substitue ["cpta"] ["debit"] == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj (ERR_CPTE_NON_PARAM, _("compte comptable des frais de dossier"));
                }

                $cptes_substitue ["int"] ["debit"] = $val_doss ['transfert_frais'] ['id_cpte_cli'];

                global $global_monnaie;
                $type_oper = 200;

                // perception des éventuelles taxes sur les frais
                //$myErr = reglementTaxe(200, $val_doss ['transfert_frais'] ['mnt_frais'], SENS_CREDIT, $devise_credit, $cptes_substitue, $comptable);
                $myErr = reglementTaxe(200, $val_doss ['transfert_frais'] ['mnt_frais'], SENS_CREDIT, $devise_credit, $cptes_substitue, $comptableTaxe2);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                // Si la devise du crédit n'est pas la devise de référence, mouvementer la position de change
                if ($devise_credit != $global_monnaie) {
                    //$myErr = effectueChangePrivate($devise_credit, $global_monnaie, $val_doss ['transfert_frais'] ['mnt_frais'], 200, $cptes_substitue, $comptableFrais);
                    $myErr = effectueChangePrivate($devise_credit, $global_monnaie, $val_doss ['transfert_frais'] ['mnt_frais'], 200, $cptes_substitue, $comptableFrais2);
                } else
                    //$myErr = passageEcrituresComptablesAuto(200, $val_doss ['transfert_frais'] ['mnt_frais'], $comptableFrais, $cptes_substitue, $devise_credit);
                    $myErr = passageEcrituresComptablesAuto(200, $val_doss ['transfert_frais'] ['mnt_frais'], $comptableFrais2, $cptes_substitue, $devise_credit);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }

            $CPT_ETATS = recup_compte_etat_credit($val_doss ['id_prod']);
            $cpt_comptable_cap = $CPT_ETATS [1]; // 1 = Etat Sain
            if ($cpt_comptable_cap == NULL) {
                $dbHandler->closeConnection(false);
//                echo "Erreur dans la fonction(deboursementCreditUSSD) :".$error[ERR_CPTE_ETAT_CRE_NON_PARAMETRE]." (id_prod: ".$val_doss ['id_prod'].", prod_libel: ".$PROD [0] ['libel'].") \n";
                return new ErrorObj (ERR_CPTE_ETAT_CRE_NON_PARAMETRE, $val_doss ['id_prod'] . ' => ' . $PROD [0] ['libel']);
            }
            $cpte_compta_debit_deb = $cpt_comptable_cap;
            $cpte_interne_debit_deb = $id_cpte_cre;

            if ($mode_debour == 2 && $PROD[0]["mode_calc_int"] != 5) { // En déboursement progressif
                // Création du compte d'attente de déboursement
                $val_doss ['data_cpt_att_deb'] ['utilis_crea'] = $global_id_utilisateur;
                $val_doss ['data_cpt_att_deb'] ['etat_cpte'] = 1;
                $val_doss ['data_cpt_att_deb'] ['id_titulaire'] = $val_doss ['id_client'];
                $val_doss ['data_cpt_att_deb'] ['date_ouvert'] = php2pg(date("d/m/Y"));
                $val_doss ['data_cpt_att_deb'] ['num_cpte'] = getRangDisponible($val_doss ['id_client']); // récupére un rang disponible
                $val_doss ['data_cpt_att_deb'] ['num_complet_cpte'] = makeNumCpte($val_doss ['id_client'], $val_doss ['data_cpt_att_deb'] ['num_cpte']); // numéro complet du compte de crédit
                $val_doss ['data_cpt_att_deb'] ['id_prod'] = 5;
                $val_doss ['data_cpt_att_deb'] ['devise'] = $devise_credit;
                $id_cpte_att_deb = creationCompte($val_doss ['data_cpt_att_deb']);
                $DATA ['cre_cpt_att_deb'] = $id_cpte_att_deb;
                // Passage des écritures comptables de mise en attente de déboursement si c'est un déboursement progressif
                $cptes_substitue = array();
                $cptes_substitue ["cpta"] = array();
                $cptes_substitue ["int"] = array();
                $cptes_substitue ["cpta"] ["debit"] = $cpte_compta_debit_deb;
                $cptes_substitue ["int"] ["debit"] = $cpte_interne_debit_deb;
                $cpt_compta_att_deb = $PROD [0] ["cpte_cpta_att_deb"];
                if ($cpt_compta_att_deb == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj (ERR_CPTE_CRE_ATT_DEB_NON_PARAMETRE, $val_doss ['id_prod'] . ' => ' . $PROD [0] ['libel']);
                }
                $cptes_substitue ["cpta"] ["credit"] = $cpt_compta_att_deb;
                $cptes_substitue ["int"] ["credit"] = $id_cpte_att_deb;

                $myErr = passageEcrituresComptablesAuto(212, $val_doss ['cre_mnt_octr'] - $val_doss ['transfert_fond'] ['montant'], $comptablemntCrt, $cptes_substitue, $devise_credit);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
            //pp178
            $DATA ['mnt_commission'] = $val_doss ['mnt_commission'];
            $DATA ['mnt_assurance'] = $val_doss ['mnt_assurance'];

            /* Mise à jour du dossier de crédit */
            updateCredit($id_doss, $DATA);
        }    // Fin, si c'est le premier déboursement
        else {
            $id_cpte_cre = $val_doss ['cre_id_cpte'];
            $DATA ['cre_mnt_deb'] = $val_doss ['cre_mnt_deb'] + $val_doss ['cre_mnt_a_deb'];
            //pp178
            $DATA ['mnt_commission'] = $val_doss ['mnt_commission'];
            $DATA ['mnt_assurance'] = $val_doss ['mnt_assurance'];

            $fermeCpteAttente = false;
            if ($DATA ['cre_mnt_deb'] < $val_doss ['cre_mnt_octr']) {
                if ($PROD[0]["mode_calc_int"] == 5) {
                    $DATA ['etat'] = 5; // Fonds déboursés
                } else {
                    $DATA ['etat'] = 13; // En déboursement progressif
                }
            } elseif ($DATA ['cre_mnt_deb'] == $val_doss ['cre_mnt_octr']) {
                $DATA ['etat'] = 5; // Fonds déboursés
                if ($func_sys_deb_doss == 125 && $val_doss['etat'] == 13) {
                    $fermeCpteAttente = true;
                    $cre_cpt_att_deb = $val_doss['cre_cpt_att_deb'];
                }
            } else {
                $dbHandler->closeConnection(false);
                return new ErrorObj (ERR_CRE_MNT_DEB_TROP_ELEVE, _("Le montant déboursé est trop élevé"));
                //echo "Erreur dans la fonction(deboursementCreditUSSD) :".$error[ERR_CPTE_ETAT_CRE_NON_PARAMETRE]." (Le montant déboursé est trop élevé) \n";
            }

            if ($val_doss['is_ligne_credit'] != 'f' && $func_sys_deb_doss == 604) {
                $CPT_ETATS = recup_compte_etat_credit($val_doss ['id_prod']);
                $cpt_comptable_cap = $CPT_ETATS [1]; // 1 = Etat Sain
                if ($cpt_comptable_cap == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj (ERR_CPTE_ETAT_CRE_NON_PARAMETRE, $val_doss ['id_prod'] . ' => ' . $PROD [0] ['libel']);
                    //echo "Erreur dans la fonction(deboursementCreditUSSD) :".$error[ERR_CPTE_ETAT_CRE_NON_PARAMETRE]." (id_prod: ".$val_doss ['id_prod'].", prod_libel: ".$PROD [0] ['libel'].") \n";
                }
                $cpte_compta_debit_deb = $cpt_comptable_cap;
                $cpte_interne_debit_deb = $id_cpte_cre;
            } else {
                // Préparation des comptes à mouvementer pour le déboursement suivant
                $cpt_compta_att_deb = $PROD [0] ["cpte_cpta_att_deb"];
                if ($cpt_compta_att_deb == NULL) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj (ERR_CPTE_CRE_ATT_DEB_NON_PARAMETRE, $val_doss ['id_prod'] . ' => ' . $PROD [0] ['libel']);
                    //echo "Erreur dans la fonction(deboursementCreditUSSD) :".$error[ERR_CPTE_ETAT_CRE_NON_PARAMETRE]." (id_prod: ".$val_doss ['id_prod'].", prod_libel: ".$PROD [0] ['libel'].") \n";
                }
                $cpte_compta_debit_deb = $cpt_compta_att_deb;
                $cpte_interne_debit_deb = $val_doss ['cre_cpt_att_deb'];
            }

            /* Mise à jour du dossier de crédit */
            updateCredit($id_doss, $DATA);
        }

        // Recherche du type d'opération
        $type_oper = get_credit_type_oper(6);

        // débit du cpte de crédit, crédite le cpte de base du client

        // Passage des écritures comptables
        $cptes_substitue = array();
        $cptes_substitue ["cpta"] = array();
        $cptes_substitue ["int"] = array();

        // débit compte de crédit / crédit compte client
        $cptes_substitue ["cpta"] ["debit"] = $cpte_compta_debit_deb;
        $cptes_substitue ["int"] ["debit"] = $cpte_interne_debit_deb;
        if (($dest_debour == 1) || ($dest_debour == 2)) { // destination = Compte lié
            $devise_compt_compta_cred = $devise_credit; // si compte lié, devise crédit = devise compte lié
            $compt_compta_cred = getCompteCptaProdEp($val_doss ['transfert_fond'] ['id_cpte_cli']);
            $compt_int_cred = $val_doss ['transfert_fond'] ['id_cpte_cli'];
        } elseif ($dest_debour == 3) { // destination = Par chèque
            if ($val_doss ['data_chq'] != NULL) {
                $data_his_ext = creationHistoriqueExterieur($val_doss ['data_chq']);
            } else {
                $data_his_ext = NULL;
            }
            $devise_compt_compta_cred = $devise_credit;
            $comptesCompensation = getComptesCompensation($val_doss ['data_chq'] ['id_correspondant']);
            $compt_compta_cred = $comptesCompensation ['compte'];
        } else {
            $dbHandler->closeConnection(false);
            return new ErrorObj (ERR_CRE_DEST_DEB_INCONNU, _("compte de destination des fonds inconnu"));
            //echo "Erreur dans la fonction(deboursementCreditUSSD) :".$error[ERR_CRE_DEST_DEB_INCONNU]." (compte de destination des fonds inconnu) \n";
        }

        $cptes_substitue ["cpta"] ["credit"] = $compt_compta_cred;
        if ($cptes_substitue ["cpta"] ["credit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj (ERR_CPTE_NON_PARAM, _("compte comptable du transfert des fonds"));
            //echo "Erreur dans la fonction(deboursementCreditUSSD) :".$error[ERR_CPTE_NON_PARAM]." (compte comptable du transfert des fonds) \n";
        }

        $cptes_substitue ["int"] ["credit"] = $compt_int_cred;

        // Si la devise du crédit n'est pas la devise du compte de transfert des fonds
        if ($devise_credit != $devise_compt_compta_cred) {
            $myErr = effectueChangePrivate($devise_credit, $devise_compt_compta_cred, $val_doss ['transfert_fond'] ['montant'], $type_oper, $cptes_substitue, $comptablemntCrt);
        } else {
            $myErr = passageEcrituresComptablesAuto($type_oper, $val_doss ['transfert_fond'] ['montant'], $comptablemntCrt, $cptes_substitue, $devise_credit);
        }
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        // deboursement au guichet, débit compte de liaison / crédit compte du guichet
        if ($dest_debour == 1) {
            // verifier que l' utilisateur à un guichet
            if ($id_guichet == NULL) {
                $dbHandler->closeConnection(false);
                return new ErrorObj (ERR_CRE_DEST_DEB_INCONNU, _("l utilisateur ne possède pas de guichet"));
                //echo "Erreur dans la fonction(deboursementCreditUSSD) :".$error[ERR_CRE_DEST_DEB_INCONNU]." (l utilisateur ne possède pas de guichet) \n";
            }
            $cptes_substitue_gui ["cpta"] ["debit"] = $compt_compta_cred;
            $cptes_substitue_gui ["int"] ["debit"] = $compt_int_cred;

            /* si destination = guichet */
            $cptes_substitue_gui ["cpta"] ["credit"] = getCompteCptaGui($id_guichet);
            if ($cptes_substitue_gui ["cpta"] ["credit"] != NULL) {
                /* On vérifie s'il y a assez d'argent dans le guichet */
                $montantguichet = get_encaisse($id_guichet, $devise_credit);
                if ($val_doss ['transfert_fond'] ['montant'] > $montantguichet) {
                    $dbHandler->closeConnection(false);
                    return new ErrorObj (ERR_CPTE_GUI_POS, sprintf(_("compte guichet: %s en devise %s"), $cptes_substitue_gui ["cpta"] ["credit"], $devise_credit));
                    //echo "Erreur dans la fonction(deboursementCreditUSSD) :".$error[ERR_CPTE_GUI_POS]." (".sprintf(_("compte guichet: %s en devise %s"), $cptes_substitue_gui ["cpta"] ["credit"], $devise_credit).") \n";
                }
                $myErr = passageEcrituresComptablesAuto(140, $val_doss ['transfert_fond'] ['montant'], $comptablemntCrt, $cptes_substitue_gui, $devise_credit);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            } else {
                $dbHandler->closeConnection(false);
                return new ErrorObj (ERR_CRE_DEST_DEB_INCONNU, _("le compte guichet n est pas paramétré"));
                //echo "Erreur dans la fonction(deboursementCreditUSSD) :".$error[ERR_CRE_DEST_DEB_INCONNU]." (le compte guichet n est pas paramétré) \n";

            }
        }

        // bien transcrire les mouvements selon que le credit a été debour avant ou apres
        if ($PROD [0] ["percep_frais_com_ass"] == 2) { // Perception des frais, commissions et assurances : APRES deboursement
            if ($comptablemntCrt != NULL) {
                $comptable = array_merge($comptable, $comptablemntCrt);
            }
            if ($comptableAssurance != NULL) {
                $comptable = array_merge($comptable, $comptableAssurance);
            }
            if ($comptableFrais2 != NULL) {
                $comptable = array_merge($comptable, $comptableFrais2);
            }
            if ($comptableTaxe2 != NULL) {
                $comptable = array_merge($comptable, $comptableTaxe2);
            }
            if ($comptableFrais1 != NULL) {
                $comptable = array_merge($comptable, $comptableFrais1);
            }
            if ($comptableTaxe1 != NULL) {
                $comptable = array_merge($comptable, $comptableTaxe1);
            }
        }
        else // Perception des frais, commissions et assurances : AVANT deboursement
        {
            if ($comptableAssurance != NULL) {
                $comptable = array_merge_recursive($comptable, $comptableAssurance);
            }
            if ($comptableFrais2 != NULL) {
                $comptable = array_merge_recursive($comptable, $comptableFrais2);
            }
            if ($comptableTaxe2 != NULL) {
                $comptable = array_merge_recursive($comptable, $comptableTaxe2);
            }
            if ($comptableFrais1 != NULL) {
                $comptable = array_merge_recursive($comptable, $comptableFrais1);
            }
            if ($comptableTaxe1 != NULL) {
                $comptable = array_merge_recursive($comptable, $comptableTaxe1);
            }
            if ($comptablemntCrt != NULL) {
                $comptable = array_merge_recursive($comptable, $comptablemntCrt);
            }
        }

        // reecrire les id du tableau comptable_historique après la fusion
        $newid = 1;
        for ($i = 0; $i < count($comptable); $i = $i + 2) {

            $comptable [$i] ["id"] = $newid;
            $comptable [$i + 1] ["id"] = $newid;
            $newid++;
        }

        // Construction du tableau global des extraits
        $myErr = ajout_historique($func_sys_deb_doss, $val_doss ["id_client"], $id_doss, $global_nom_login, date("r"), $comptable, $data_his_ext);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        } else {
            if ($fermeCpteAttente == true) {
                // Fermeture des comptes d'attente déboursement
                $erreur = fermeCompte($cre_cpt_att_deb, 4, $val_doss['transfert_fond']['montant']);
                if ($erreur->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $erreur;
                }
            }
        }

        $array_his [$id_doss] = $myErr->param;
    } // fin parcours dossiers


    $dbHandler->closeConnection(true);
    return new ErrorObj (NO_ERR, $array_his);
}
function updateDemandeCredit($id_doss, $id_client, $statut, $id_transaction){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $fields = array('id_doss' => $id_doss, "statut_demande" => $statut, "date_modif" => date("d/m/Y"));
    $where = array('id_client' => $id_client, 'id_doss' => -1, 'id_transaction' => $id_transaction);
    $sql = buildUpdateQuery ("ml_demande_credit", $fields, $where);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
//        echo "Erreur dans la fonction (updateDemandeCredit) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction updateDemandeCredit: $sql"));
    };

    $dbHandler->closeConnection(true);

    return new ErrorObj(NO_ERR);
}
function calcul_echeancier_theorique($id_prod, $capital, $duree, $differe_jours, $differe_ech, $periodicite = NULL, $echeance_index = 1, $id_doss = NULL, $produits_credit = NULL, $premiere_credit) {
    global $global_id_agence;
    global $global_monnaie_courante, $global_monnaie_courante_prec;

    $adsys["adsys_duree_periodicite"][1] = 1;
    $adsys["adsys_duree_periodicite"][2] = 0.5;
    $adsys["adsys_duree_periodicite"][3] = 3;
    $adsys["adsys_duree_periodicite"][4] = 6;
    $adsys["adsys_duree_periodicite"][5] = 12;
    $adsys["adsys_duree_periodicite"][6] = 0;
    $adsys["adsys_duree_periodicite"][7] = 2;
    $adsys["adsys_duree_periodicite"][8] = 1;

    // {{{ Initialisations
    $retour = array ();

    // Récupération des infos via le produit de crédit
    if ($produits_credit != NULL && is_array($produits_credit)) {
        $Produit = $produits_credit;
    } else {
        $Produits = getProdInfo("WHERE id = $id_prod", $id_doss);
        $Produit = $Produits[0];
    }

    if($premiere_credit == 't'){
        $Produit['tx_interet'] = 0;
    }

    if ($periodicite == NULL) {
        $periodicite = $Produit["periodicite"];
    }

    $mode_calc_int = $Produit["mode_calc_int"];
    $mode_perc_int = $Produit["mode_perc_int"];
    $tx_interet = $Produit["tx_interet"];
    $freq_paiement_cap = $Produit["freq_paiement_cap"];
    $prc_gar_encours = $Produit["prc_gar_encours"];
    $gar_encours = $prc_gar_encours * $capital;
    // Récupération du nombre de jours par an
    $Agence = getAgenceDatas($global_id_agence);
    if ($Agence["base_taux"] == 1) // 360 jours
        $nbre_jours_an = 360;
    elseif ($Agence["base_taux"] == 2) // 365 jours
        $nbre_jours_an = 365;

    // Vérifie que la durée est bien un multiple du nombre de mois que constitue une période
    // Ceci ne devrait pas arriver si le javascript est activé
    if ($adsys["adsys_duree_periodicite"][$periodicite] > 1)
        if ($duree % $adsys["adsys_duree_periodicite"][$periodicite] != 0) {
//            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("La durée n'est pas un multiple de la périodicité."));
            //echo "Erreur dans la fonction (calcul_echeancier_theorique) : La durée n'est pas un multiple de la périodicité.\n";
            return new ErrorObj (ERR_GENERIQUE, _("Fonction calcul_echeancier_theorique: La durée n'est pas un multiple de la périodicité"));
        }
    // Calcule la durée d'une période (échéance)
    // $period contient la durée d'une échéance exprimée en type de durée de crédit : mois ou semaine
    if ($periodicite != 6)
        $period = $adsys["adsys_duree_periodicite"][$periodicite];
    else
        $period = $duree; // Remboursement en une fois

    // Calcul du nombre d'échéances de remboursement (hors différé)
    $nbr_ech_remb = $duree / $period;
    $nbr_ech_total = $nbr_ech_remb + $differe_ech;

    // Calcul du prorata temporis pour le différé en jours
    if ($periodicite == 8)
        // Périodicité hebdomadaire
        $nbr_jours_ech_courante = date("d", mktime(0, 0, 0, 0, date("d") + $period * 7, date("Y")));
    elseif ($Agence["base_taux"] == 1)
        $nbr_jours_ech_courante = 30 * $period;
    else
        $nbr_jours_ech_courante = date("d", mktime(0, 0, 0, date("m") + $period, 0, date("Y")));
    if (($differe_jours > 0) || ($differe_jours < 0)) {
        $prorata_temp = $differe_jours / $nbr_jours_ech_courante;
        debug(	$prorata_temp,"temp");
        /*  Code à activer si on veut créer une échéance supplémentaire pour le différé en jours
            $premiere_echeance = 2;
            $echeancier[1]['mnt_cap'] = 0;
            $echeancier[1]['mnt_int'] = 0;
            $echeancier[1]['mnt_gar'] = 0; */
    } else {
        $prorata_temp = 1;
        //    $premiere_echeance = 1;
    }
    $premiere_echeance = 1;

    // Taux d'intérêts ramené sur une période (pas utile pour le mode constant)
    if ($periodicite == 8)
        $tx_int_ech = $tx_interet * $period;
    else
        $tx_int_ech = $tx_interet * $period / 12;

    // Les montants cumulés, pour vérifier les arrondis sur la dernière échéance
    $mnt_cap = 0;
    $mnt_gar = 0;
    $mnt_int = 0;

    // Le flag pour la perception des intérêts au debut de l'échéancier
    $int_percus = false;

    // }}}
    // {{{ Mode Constant
    if ($mode_calc_int == 1) {


        // Montant total des intérêts à payer
        if ($periodicite == 8)
            // Périodicité hebdomadaire
            $int = $capital * ($duree + $differe_ech * $period) * $tx_interet;
        else
            $int = $capital * ($duree + $differe_ech * $period) * $tx_interet / 12;


        for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
            // Pour chaque échéance
            $echeancier[$i]['mnt_int'] = 0;

            if ($i <= $differe_ech) {
                // On est dans le différé, on ne rembourse que les intérêts
                $echeancier[$i]['mnt_cap'] = 0;
                $echeancier[$i]['mnt_gar'] = 0;
                if ($mode_perc_int == 2) {
                    // Intérêts inclus dans les remboursements
                    if($Produit["calcul_interet_differe"] == 't'){
                        $echeancier[$i]['mnt_int'] = round($int / $nbr_ech_total, $global_monnaie_courante_prec);

                    }
                }
                if ($Produit['differe_epargne_nantie'] == 'f') {
                    $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
                }
            } elseif ($i < $nbr_ech_total) {
                // On rembourse les intérêts et le capital et on constitue la garantie
                if (($i - $differe_ech) % $freq_paiement_cap == 0)
                    // L'échéance, hors différé, est un multiple de la fréquence de paiement
                    $echeancier[$i]['mnt_cap'] = round($capital * $freq_paiement_cap / $nbr_ech_remb, $global_monnaie_courante_prec);
                else
                    $echeancier[$i]['mnt_cap'] = 0;
                if ($Produit['differe_epargne_nantie'] == 'f') {
                    $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
                }else{
                    $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_remb, $global_monnaie_courante_prec);
                }
                if ($mode_perc_int == 1 && !$int_percus) {
                    // Perception des intérêts au début
                    $echeancier[$i]['mnt_int'] = round($int, $global_monnaie_courante_prec);
                    $int_percus = true;
                }
                if ($mode_perc_int == 2) {
                    // Intérêts inclus dans les remboursements
                    if($Produit["calcul_interet_differe"] == 't'){
                        $echeancier[$i]['mnt_int'] = round($int / $nbr_ech_total, $global_monnaie_courante_prec);
                    }else{
                        $echeancier[$i]['mnt_int'] = round($int / $nbr_ech_remb, $global_monnaie_courante_prec);
                    }
                }

            } else {
                // On est à la dernière échéance
                // On reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
                $echeancier[$i]['mnt_cap'] = round($capital - $mnt_cap, $global_monnaie_courante_prec);
                $echeancier[$i]['mnt_gar'] = round($gar_encours - $mnt_gar, $global_monnaie_courante_prec);
                if ($mode_perc_int == 2) {
                    // Intérêts inclus dans les remboursements
                    $echeancier[$i]['mnt_int'] = round($int - $mnt_int, $global_monnaie_courante_prec);
                }
                if ($mode_perc_int == 3) {
                    // Perception des intérêts à la fin
                    $echeancier[$i]['mnt_int'] = round($int, $global_monnaie_courante_prec);
                }
            }

            // On calcule les sommes pour tomber juste à la fin
            $mnt_cap += $echeancier[$i]['mnt_cap'];
            $mnt_gar += $echeancier[$i]['mnt_gar'];
            $mnt_int += $echeancier[$i]['mnt_int'];
        }

        if (($differe_jours > 0) || ($differe_jours < 0)) {
            // On ajoute les intérêts sur le différé en jours à la première échéance
            if($Produit["calcul_interet_differe"] == 't'){


                $mnt_int_diff_jour= round($int / $nbr_ech_total * $prorata_temp, $global_monnaie_courante_prec);

                /* On ajoute les intérêts sur le différé en jours à la première  ou a la derniere   échéance
        selon le report choisi .les montants entre la première échéance et la dernière seront échanger
       */
                if ($Produit['report_arrondi'] =='t') {
                    $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour; // ca sera la premiere écheance

                }
                else
                {
                    if($echeance_index!=1) {
                        $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour;
                    }
                    else {
                        $echeancier[$nbr_ech_total]['mnt_int'] +=$mnt_int_diff_jour;//sera la premiere
                    }
                }

            }
        }
    }

    // }}}
    // {{{ Mode dégressif prédéfini ou mode dégressif variable
    // Ils sont identiques au niveau de l'échéancier théorique

    elseif ($mode_calc_int == 2 || $mode_calc_int == 3) {
        // Calcul de la somme à payer à chaque échéance
        $mnt_ech = $capital * $tx_int_ech / (1 - 1 / pow(1 + $tx_int_ech, $nbr_ech_remb / $freq_paiement_cap));

        // Cas particuliers de perception des intérêts au début ou à la fin
        if ($mode_perc_int == 1) {
            // Perception des intérêts au début
            $ech_de_remboursement = $differe_ech +1;
        } elseif ($mode_perc_int == 3) {
            // Perception des intérêts à la fin
            $ech_de_remboursement = $nbr_ech_total;
        }

        for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
            // Pour chaque échéance
            $echeancier[$i]['mnt_int'] = 0;

            if ($i <= $differe_ech) {
                // On est dans le différé, on ne rembourse que les intérêts
                $echeancier[$i]['mnt_cap'] = 0;
                $echeancier[$i]['mnt_int'] = 0;
                $echeancier[$i]['mnt_gar'] = 0;
                if ($mode_perc_int == 1 || $mode_perc_int == 3) {
                    // Perception des intérêts au début ou à la fin
                    $echeancier[$ech_de_remboursement]['mnt_int'] += round($capital * $tx_int_ech, $global_monnaie_courante_prec);
                } else {
                    if($Produit["calcul_interet_differe"] == 't') {
                        // Intérêts inclus dans les remboursements
                        $echeancier[$i]['mnt_int'] = round($capital * $tx_int_ech, $global_monnaie_courante_prec);
                    }
                }
                if ($Produit['differe_epargne_nantie'] == 'f') {
                    $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
                }
            } elseif ($i < $nbr_ech_total) {
                // On rembourse les intérêts et le capital et on constitue la garantie
                if ($mode_perc_int == 1 || $mode_perc_int == 3) {
                    // Perception des intérêts au début ou à la fin
                    $echeancier[$ech_de_remboursement]['mnt_int'] += round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
                } else {
                    // Intérêts inclus dans les remboursements
                    $echeancier[$i]['mnt_int'] = round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
                }
                if (($i - $differe_ech) % $freq_paiement_cap == 0)
                    // L'échéance, hors différé, est un multiple de la fréquence de paiement
                    $echeancier[$i]['mnt_cap'] = round($mnt_ech - $echeancier[$i]['mnt_int'], $global_monnaie_courante_prec);
                else
                    $echeancier[$i]['mnt_cap'] = 0;
                if ($Produit['differe_epargne_nantie'] == 'f') {
                    $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
                }else{
                    $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_remb, $global_monnaie_courante_prec);
                }

            } else {
                // On est à la dernière échéance, on reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
                $echeancier[$i]['mnt_cap'] = $capital - $mnt_cap;
                $echeancier[$i]['mnt_gar'] = $gar_encours - $mnt_gar;
                if ($mode_perc_int == 1 || $mode_perc_int == 3) {
                    // Perception des intérêts au début ou à la fin
                    $echeancier[$ech_de_remboursement]['mnt_int'] += round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
                } else {
                    // Intérêts inclus dans les remboursements
                    $echeancier[$i]['mnt_int'] = round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
                }
            }

            // On calcule les sommes pour tomber juste à la fin
            $mnt_cap += $echeancier[$i]['mnt_cap'];
            $mnt_gar += $echeancier[$i]['mnt_gar'];
            $mnt_int += $echeancier[$i]['mnt_int'];
        }

        if (($differe_jours > 0) || ($differe_jours < 0)) {
            // On ajoute les intérêts sur le différé en jours à la première échéance
            if($Produit["calcul_interet_differe"] == 't') { //si le calcule d'interêt diff'

                $mnt_int_diff_jour= round($capital * $tx_int_ech * $prorata_temp, $global_monnaie_courante_prec);

                /* On ajoute les intérêts sur le différé en jours à la première  ou a la derniere   échéance
               selon le report choisi .les montants entre la première échéance et la dernière seront échanger
              */
                if ($Produit['report_arrondi'] =='t') {
                    $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour; //ca sera la pemiere écheance

                }
                else
                {
                    if($echeance_index!=1) {
                        $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour;
                    }
                    else {
                        $echeancier[$nbr_ech_total]['mnt_int'] +=$mnt_int_diff_jour; //sera la premiere écheance
                    }
                }
            }

        }

    }

    // }}}
    // {{{ Mode dégressif capital constant

    elseif ($mode_calc_int == 4) {
        // Calcul de la somme à payer à chaque échéance
        $cap_ech = $capital * $freq_paiement_cap / $nbr_ech_remb;

        // Cas particuliers de perception des intérêts au début ou à la fin
        if ($mode_perc_int == 1) {
            // Perception des intérêts au début
            $ech_de_remboursement = $differe_ech +1;
        } elseif ($mode_perc_int == 3) {
            // Perception des intérêts à la fin
            $ech_de_remboursement = $nbr_ech_total;
        }

        for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
            // Pour chaque échéance
            $echeancier[$i]['mnt_int'] = 0;

            if ($i <= $differe_ech) {
                // On est dans le différé, on ne rembourse que les intérêts
                $echeancier[$i]['mnt_cap'] = 0;
                $echeancier[$i]['mnt_int'] = 0;
                $echeancier[$i]['mnt_gar'] = 0;
                if ($mode_perc_int == 1 || $mode_perc_int == 3) {
                    // Perception des intérêts au début ou à la fin
                    $echeancier[$ech_de_remboursement]['mnt_int'] += round($capital * $tx_int_ech, $global_monnaie_courante_prec);
                } else {
                    if($Produit["calcul_interet_differe"] == 't') {
                        // Intérêts inclus dans les remboursements
                        $echeancier[$i]['mnt_int'] = round($capital * $tx_int_ech, $global_monnaie_courante_prec);
                    }
                }
                if ($Produit['differe_epargne_nantie'] == 'f') {
                    $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
                }
            } elseif ($i < $nbr_ech_total) {
                // On rembourse les intérêts et le capital et on constitue la garantie
                if (($i - $differe_ech) % $freq_paiement_cap == 0)
                    // L'échéance, hors différé, est un multiple de la fréquence de paiement
                    $echeancier[$i]['mnt_cap'] = round($cap_ech, $global_monnaie_courante_prec);
                else
                    $echeancier[$i]['mnt_cap'] = 0;
                if ($mode_perc_int == 1 || $mode_perc_int == 3) {
                    // Perception des intérêts au début ou à la fin
                    $echeancier[$ech_de_remboursement]['mnt_int'] += round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
                } else {
                    // Intérêts inclus dans les remboursements
                    $echeancier[$i]['mnt_int'] = round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
                }
                if ($Produit['differe_epargne_nantie'] == 'f') {
                    $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
                }else{
                    $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_remb, $global_monnaie_courante_prec);
                }

            } else {
                // On est à la dernière échéance, on reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
                $echeancier[$i]['mnt_cap'] = $capital - $mnt_cap;
                $echeancier[$i]['mnt_gar'] = $gar_encours - $mnt_gar;
                if ($mode_perc_int == 1 || $mode_perc_int == 3) {
                    // Perception des intérêts au début ou à la fin
                    $echeancier[$ech_de_remboursement]['mnt_int'] += round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
                } else {
                    // Intérêts inclus dans les remboursements
                    $echeancier[$i]['mnt_int'] = round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
                }
            }

            // On calcule les sommes pour tomber juste à la fin
            $mnt_cap += $echeancier[$i]['mnt_cap'];
            $mnt_gar += $echeancier[$i]['mnt_gar'];
            $mnt_int += $echeancier[$i]['mnt_int'];
        }

        if (($differe_jours > 0) || ($differe_jours < 0)) {
            // On ajoute les intérêts sur le différé en jours à la première échéance

            if($Produit["calcul_interet_differe"] == 't') {

                $mnt_int_diff_jour= round($capital * $tx_int_ech * $prorata_temp, $global_monnaie_courante_prec);

                /* On ajoute les intérêts sur le différé en jours à la première  ou a la derniere   échéance
                          selon le report choisi .les montants entre la première échéance et la dernière seront échanger
                     */
                if ($Produit['report_arrondi'] =='t') {

                    $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour; //sera la primiere écheance

                }
                else
                {
                    if($echeance_index != 1) {
                        $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour;
                    }
                    else {
                        $echeancier[$nbr_ech_total]['mnt_int'] +=$mnt_int_diff_jour;// sera la premiere echeance
                    }
                }

            }
        }
    }
    // {{{ Mode Ligne de crédit

    elseif ($mode_calc_int == 5) {

        // Montant total des intérêts à payer
        for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
            // Pour chaque échéance
            $echeancier[$i]['mnt_int'] = 0;
            $echeancier[$i]['mnt_gar'] = 0;

            if ($i < $nbr_ech_total) {

            } else {
                // On est à la dernière échéance
                // On reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
                $echeancier[$i]['mnt_cap'] = round($capital - $mnt_cap, $global_monnaie_courante_prec);

            }

            // On calcule les sommes pour tomber juste à la fin
            $mnt_cap += $echeancier[$i]['mnt_cap'];
            $mnt_gar += $echeancier[$i]['mnt_gar'];
            $mnt_int += $echeancier[$i]['mnt_int'];
        }
    }

    // }}}
    // {{{ Autre

    else {
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Mode de calcul des intérêts inconnu !"));
//        echo "Erreur dans la fonction (calcul_echeancier_theorique) : Mode de calcul des intérêts inconnu !\n";
        return new ErrorObj (ERR_GENERIQUE, _("Fonction calcul_echeancier_theorique: Mode de calcul des intérêts inconnu !"));
    }

    //Si Report arrondi première échéance
    if($Produit["report_arrondi"] == 'f'){
        //Echanger les montants entre la première échéance et la dernière
        //Transfert du capital
        $tmp_cap=$echeancier[$premiere_echeance]['mnt_cap'];
        $echeancier[$premiere_echeance]['mnt_cap']=$echeancier[$nbr_ech_total]['mnt_cap'];
        $echeancier[$nbr_ech_total]['mnt_cap']=$tmp_cap;
        //Transfert des intérêts
        $tmp_int=$echeancier[$premiere_echeance]['mnt_int'];
        $echeancier[$premiere_echeance]['mnt_int']=$echeancier[$nbr_ech_total]['mnt_int'];
        $echeancier[$nbr_ech_total]['mnt_int']=$tmp_int;
        //Transfert des garanties
        $tmp_gar=$echeancier[$premiere_echeance]['mnt_gar'];
        $echeancier[$premiere_echeance]['mnt_gar']=$echeancier[$nbr_ech_total]['mnt_gar'];
        $echeancier[$nbr_ech_total]['mnt_gar']=$tmp_gar;
        //Transfert des soldes
        //du capital
        $tmp_solde_cap=$echeancier[$premiere_echeance]['solde_cap'];
        $echeancier[$premiere_echeance]['solde_cap']=$echeancier[$nbr_ech_total]['solde_cap'];
        $echeancier[$nbr_ech_total]['solde_cap']=$tmp_solde_cap;
        //des intérêts
        $tmp_solde_int=$echeancier[$premiere_echeance]['solde_int'];
        $echeancier[$premiere_echeance]['solde_int']=$echeancier[$nbr_ech_total]['solde_int'];
        $echeancier[$nbr_ech_total]['solde_int']=$tmp_solde_int;
        //des garanties
        $tmp_solde_gar=$echeancier[$premiere_echeance]['solde_gar'];
        $echeancier[$premiere_echeance]['solde_gar']=$echeancier[$nbr_ech_total]['solde_gar'];
        $echeancier[$nbr_ech_total]['solde_gar']=$tmp_solde_gar;
        //des pénalités
        $tmp_solde_pen=$echeancier[$premiere_echeance]['solde_pen'];
        $echeancier[$premiere_echeance]['solde_pen']=$echeancier[$nbr_ech_total]['solde_pen'];
        $echeancier[$nbr_ech_total]['solde_pen']=$tmp_solde_pen;


    }

    return $echeancier;
}
function completeEcheancier($echeancier,$parametre) {

    global $global_id_agence;

    $adsys["adsys_duree_periodicite"][1] = 1;
    $adsys["adsys_duree_periodicite"][2] = 0.5;
    $adsys["adsys_duree_periodicite"][3] = 3;
    $adsys["adsys_duree_periodicite"][4] = 6;
    $adsys["adsys_duree_periodicite"][5] = 12;
    $adsys["adsys_duree_periodicite"][6] = 0;
    $adsys["adsys_duree_periodicite"][7] = 2;
    $adsys["adsys_duree_periodicite"][8] = 1;

    //Types de périodicités de remboursement
    $adsys["adsys_type_periodicite"][1] = _("Mensuelle");
    $adsys["adsys_type_periodicite"][2] = _("Quinzaine");
    $adsys["adsys_type_periodicite"][3] = _("Trimestrielle");
    $adsys["adsys_type_periodicite"][4] = _("Semestrielle");
    $adsys["adsys_type_periodicite"][5] = _("Annuelle");
    $adsys["adsys_type_periodicite"][6] = _("En une fois");
    $adsys["adsys_type_periodicite"][7] = _("Tous les 2 mois");
    $adsys["adsys_type_periodicite"][8] = _("Hebdomadaire");

    $Produit = getProdInfo(" where id =".$parametre["id_prod"], $parametre["id_doss"]);  //Info du produit de crédit
    $index=$Produit[0]["periodicite"];

    if($parametre["perio"]){
        $Produit[0]["periodicite"] = $parametre["perio"];
        $index = $parametre["perio"];
    }

    // Récupération de la base pour le calcul des intérpets
    // 1 => 360 jours => Mois de 30 jours
    // 2 => 365 jours => Mois correspondent au calendrier
    $AG = getAgenceDatas($global_id_agence);
    $base_taux = $AG["base_taux"];

    // Echéancier non stocké dans la base de données
    // D'où la nécessité de le générer entièrement
    $total_cap = 0;
    $total_int = 0;
    $period = $adsys["adsys_duree_periodicite"][$index];
    reset($echeancier); // Réinitialise le pointeur de tableau des écheances

    // Calcul de la périodicité en jour
    if ($Produit[0]["periodicite"] == 6) // Si on doit tout rembourser en une fois
        $duree_Periode = $parametre["duree"] * $parametre["nbre_jour_mois"];
    elseif ($adsys["adsys_type_periodicite"][$index]=="Hebdomadaire")
        $duree_Periode= $adsys["adsys_duree_periodicite"][$Produit[0]["periodicite"]]*7;
    else
        //FIXME nbre_jours_mois est toujours 30 et ne sert à rien en fait. A supprimer
        $duree_Periode = $adsys["adsys_duree_periodicite"][$Produit[0]["periodicite"]]*$parametre["nbre_jour_mois"];

    $diff = $parametre["differe_jours"];
    $date = $parametre["date"]; //Date de déboursement ou rééchelonnement
    $retour = array();
    $dern_jour = false; // Variable utilisée pour le cas particulier du dernier jour du mois
    $jj_save = $mm_save = 0;

    while (list($key,$echanc) = each($echeancier))
    {
        $i = $key + $parametre["index"];

        $DATAECH = array();
        // Remplissage de $DATAECH avec les données retournées par l'échéancier.
        $DATAECH["id_doss"] =  $parametre["id_doss"];
        $DATAECH["id_ech"] = $i;
        $DATAECH["mnt_cap"] = $echanc["mnt_cap"].'';
        $DATAECH["mnt_int"] = $echanc["mnt_int"].'';
        $DATAECH["mnt_gar"] = $echanc["mnt_gar"].'';
        $DATAECH["remb"] ='f';
        $DATAECH["solde_cap"] = $echanc["mnt_cap"].'';
        $DATAECH["solde_gar"] = $echanc["mnt_gar"].'';

        // Dans le cas d'un mode de calcul des intérêts de type 'dégressif KAANI',
        // les intérêts seront comptabilisés dynamiquement au jour le jour
        if ($Produit[0]["mode_calc_int"] == 3) // Type dégressif KAANI
            $DATAECH["solde_int"] = 0;
        else
            $DATAECH["solde_int"] = $echanc["mnt_int"].'';

        $DATAECH["solde_pen"] ='0';
        $DATAECH["mnt_reech"] ='0';

        // Calcul des dates d'échéance la date doit être au format jj/mm/aaaa
        $periodicite = $Produit[0]["periodicite"];

        if ($date != "") { // Rappel : $date = Date du déboursement / rééchelonnement
            $r = explode("/", $date);
            $jj = (int) 1*$r[0];
            $mm = (int) 1*$r[1];
            $aa = (int) 1*$r[2];

            // Init :
            if(empty($jj_save)) $jj_save = $jj;
            if(empty($mm_save)) $mm_save = $mm;
            if(empty($aa_savex)) $aa_savex = $aa;

            if ($base_taux == 1) // 360 jours
                $date = date("d/m/Y",mktime(0,0,0,$mm,$jj + $duree_Periode + $diff,$aa,0));
            else if ($base_taux == 2) {
                // Périodicité hebdomadaire
                if (in_array($periodicite, array(8))) $date=date("d/m/Y",mktime(0,0,0,$mm,$jj + $duree_Periode + $diff,$aa,0));

                // Périodes de mois entiers
                else if (in_array($periodicite, array(1,3,4,5,7))) {
                    $nbre_mois_periode = $adsys["adsys_duree_periodicite"][$periodicite];
                    if ($dern_jour)
                        $date = date("d/m/Y", mktime(0,0,0,$mm+$nbre_mois_periode+1,0,$aa));
                    else
                        $date = date("d/m/Y", mktime(0,0,0,$mm+$nbre_mois_periode,$jj+$diff,$aa));
                }

                // Périodicité 2 fois par mois
                else if ($periodicite == 2)
                {
                    if ($i%2 == 1) { // Impair ==> d(j) = d(j-1) + 15 jours.
                        if ($dern_jour)
                            $date = date("d/m/Y", mktime(0,0,0,$mm+1,15,$aa));
                        else {
                            $date = date("d/m/Y", mktime(0,0,0,$mm,$jj+$diff+15,$aa));
                            /*echo 'ech='.$i.' - $mm='.$mm.', $jj='.$jj.' + $diff='.$diff.' + 15 , $aa='.$aa.'<br />';
                            echo '$date='.$date.'<br /><br />';*/
                        }
                        // On enregistre le jour et le mois de d(j-1)
                        $aa_savex = $aa; // Year fix
                        $mm_save = $mm;
                        $jj_save = $jj+$diff;
                    }
                    else // Pair ==> d(j) = d(j-2) + 1 mois.
                    {
                        // Year fix
                        if($mm_save==12 && ($jj_save+15)>31) {
                            if ($dern_jour) {
                                $date = date("d/m/Y", mktime(0,0,0,$mm_save+2,0,$aa_savex));
                            } else {
                                $date = date("d/m/Y", mktime(0,0,0,$mm_save+1,$jj_save,$aa_savex));
                                /*echo 'ech='.$i.' - $mm_save='.$mm_save.' + 1, $jj_save='.$jj_save.' , $aa='.$aa_savex.'<br />';
                                echo '$date='.$date.'<br /><br />';*/
                            }
                        }
                        else {
                            if ($dern_jour) {
                                $date = date("d/m/Y", mktime(0,0,0,$mm_save+2,0,$aa));
                            } else {
                                $date = date("d/m/Y", mktime(0,0,0,$mm_save+1,$jj_save,$aa));
                                /*echo 'ech='.$i.' - $mm_save='.$mm_save.' + 1, $jj_save='.$jj_save.' , $aa='.$aa.'<br />';
                                echo '$date='.$date.'<br /><br />';*/
                            }
                        }
                    }
                } // end :  Périodicité 2 fois par mois

                // Remboursement en une fois
                else if ($periodicite == 6) {
                    $date = date("d/m/Y", mktime(0,0,0,$mm+$parametre["duree"],$jj+$diff, $aa));
                }

                if ($i == 1) { // On est à la première échéance
                    // On va rechercher si cette première échéance correspond à la fin d'un mois
                    // Dans ce cas, on considère que l'utilisateur
                    // désire que toutes les échéances tombent à la fin du mois
                    $r = explode("/", $date);
                    $jj = (int) 1*$r[0];
                    $mm = (int) 1*$r[1];
                    $aa = (int) 1*$r[2];
                    if (mktime(0,0,0,$mm,$jj,$aa) == mktime(0,0,0,$mm+1,0,$aa))
                        $dern_jour = true; // On est au dernier jour du mois
                    else
                        $dern_jour = false;
                }

            } else {
//                signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Type de base de calcul inconnue: [$base_taux]"
                //echo "Erreur dans la fonction (completeEcheancier) : Type de base de calcul inconnue !\n";
                return new ErrorObj (ERR_GENERIQUE, _("Fonction completeEcheancier: Type de base de calcul inconnue !"));
            }

            $diff=0;
            $DATAECH["date_ech"] = $date;
        }
        //if($parametre["id_doss"]>=0) $SESSION_VARS["etr"][$key] = $DATAECH;
        $retour[$key] = $DATAECH;
    }

    return $retour;
}


function getGarantiEnCours($id_client){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "Select sum(montant_vente) as mnt_gar_tot from ad_gar g INNER JOIN ad_dcr d On d.id_doss = g.id_doss WHERE d.id_client = $id_client AND d.etat IN (5,6,7,9,13,14,15) AND g.etat_gar IN (2,3)";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //echo "Erreur dans la fonction (getGarantiEnCours) Instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getGarantiEnCours: $sql"));
    }
    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return false;
    }
    $dbHandler->closeConnection(true);
    $tmpRow = $result->fetchrow();
    $mnt = $tmpRow[0];
    return $mnt;

}
function getAgentMobileLending($condition = NULL) {
    global $dbHandler;

    $db = $dbHandler->openConnection(true);
    $sql = "SELECT id_utilis, nom,prenom FROM ad_uti WHERE is_agent_ml = 't' LIMIT 1";
    $result = $db->query($sql); //Va chercher dans la table des sessions
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj (ERR_DB_SQL, _("Fonction getGarantiEnCours: $sql"));
    }
    $row = $result->fetchrow();

    $dbHandler->closeConnection(true);
    return  $row[0];
}
function getDemandeCredit($id_client, $condition = null){
    global $dbHandler;

    $db = $dbHandler->openConnection(true);
    $sql = "SELECT * FROM ml_demande_credit WHERE id_client = $id_client";
    $dataset = array();

    if(!empty($condition))
        $sql .= " AND ".$condition;

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj (ERR_DB_SQL, _("Fonction ml_demande_credit: $sql"));
    }


    while($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
        $dataset[] = $row;
    }

    $dbHandler->closeConnection(true);
    return $dataset;
}
function updateAbonnement($DATA){
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $fields = array("signature_contrat" => $DATA['signature_contrat']);
    if(array_key_exists('premier_credit', $DATA)){
        $fields['premier_credit'] = $DATA['premier_credit'];
    }
    $where = array('id_client' => $DATA['id_client'], 'deleted' => 'f');

    $sql = buildUpdateQuery ("ad_abonnement", $fields, $where);

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj (ERR_DB_SQL, _("Fonction ad_abonnement: $sql"));
    };

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR);
}

function isPremierCreditMobileLending($id_client){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT premier_credit FROM ad_abonnement a INNER JOIN ml_demande_credit m on m.id_client = a.id_client WHERE m.id_client = $id_client AND a.deleted = 'f'";
    //$sql = "SELECT * FROM ml_demande_credit WHERE id_client = 2040";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
        return new ErrorObj (ERR_DB_SQL, _("Fonction isPremierCreditMobileLending: ".$result->getMessage()));
    }
    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
    $dbHandler->closeConnection(true);
    return $row;
}

function getDataClientAbonneeSpecifique($client = NULL) {

    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ml_donnees_client_abonnees_specifique";

    if($client != NULL){
        $sql .= " WHERE client = $client";
    }
    $sql .=" ORDER BY client ASC";
    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        //echo "Erreur dans la fonction (getDataClientAbonnee) instruction SQL --> $sql\n";
        return new ErrorObj (ERR_DB_SQL, _("Fonction getDataClientAbonnee : $sql"));
    }
    if ($result->numrows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $i=1;
    $retour = array();
    while ($rows = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $retour[$i]=$rows;
        $i++;
    }
    $dbHandler->closeConnection(true);
    return $retour;
}

function getNbreDossierMobileLending($where = null){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT count(*) as nbre from ml_demande_credit";

    if ($where != null){
        $sql .=  $where;
    }echo $sql;
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
        return new ErrorObj (ERR_DB_SQL, _("Fonction getNbreDossierMobileLending : ". $result->getMessage()));
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    $dbHandler->closeConnection(true);
    return $DATAS;
}

function getLocalisationIMF(){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT ml_localisation from ad_agc";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
        return new ErrorObj (ERR_DB_SQL, _("Fonction getLocalisationIMF : ". $result->getMessage()));
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    $dbHandler->closeConnection(true);
    return $DATAS;
}
function getClientAbonnement($whereCondition){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT * from ad_abonnement";

    if(!empty($whereCondition)){
        $sql .= $whereCondition;
    }

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
        return new ErrorObj (ERR_DB_SQL, _("Fonction getClientAbonnement : ". $result->getMessage()));
    }

    if ($result->numRows() == 0) {
        $dbHandler->closeConnection(true);
        return NULL;
    }

    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    $dbHandler->closeConnection(true);
    return $DATAS;
}
function getObjetsCredit($code) {
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $obj_credit = array();

    $sql = "SELECT * FROM adsys_objets_credits WHERE id_ag=$global_id_agence AND code LIKE '%".$code."%' ORDER BY id";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__);
        return new ErrorObj (ERR_DB_SQL, _("Fonction getObjetsCredit , SQL: $sql"));
    }

    $dbHandler->closeConnection(true);

    while ($obj = $result->fetchrow(DB_FETCHMODE_ASSOC))
        $obj_credit[$obj["id"]] = $obj["libel"];

    return $obj_credit;
}
function getDetailsObjCredit($code) {
    global $dbHandler,$global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "select * from adsys_detail_objet where id_ag=$global_id_agence AND code LIKE '%".$code."%' ORDER BY libel ASC ";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__);
        return new ErrorObj (ERR_DB_SQL, _("Fonction getDetailsObjCredit , SQL: $sql"));
    }

    $dbHandler->closeConnection(true);

    while ($obj = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

        $obj['id_obj'] = $obj['id_obj'];
        $obj['libel'] = trim(str_replace("'", "\'", str_replace(array("\\\\'","\\\'","\\'","\'"), "'", str_replace(array('\\\\\\\\','\\\\\\','\\\\',), '\\', stripslashes(trim($obj['libel']))))));
        if (!isDcrDetailObjCreditLsb()) {
            $obj['libel'] = ucfirst(strtolower($obj['libel']));
        }

        $det_credit[$obj["id"]] = $obj;
    }

    return $det_credit;
}
function isDcrDetailObjCreditLsb() {
    global $dbHandler,$global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "SELECT dcr_lsb_detail_obj_credit FROM ad_agc WHERE id_ag=$global_id_agence";

    $result = $db->query($sql);
    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__, __LINE__, __FUNCTION__);
        return new ErrorObj (ERR_DB_SQL, _("Fonction isDcrDetailObjCreditLsb , SQL: $sql"));
    }
    $row = $result->fetchrow();
    $dbHandler->closeConnection(true);

    return ($row[0]=='t'?true:false);
}
function getUtilisateurs($code_agent) {
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $utilisateur = array();
    $sql = "SELECT * FROM ad_uti WHERE ml_code_agent = '".$code_agent."'";


    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
//        signalErreur(__FILE__,__LINE__,__FUNCTION__);
        return new ErrorObj (ERR_DB_SQL, _("Fonction getUtilisateurs , SQL: $sql"));
    }

    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        $utilisateur[$row['id_utilis']] = $row;

    $db = $dbHandler->closeConnection(true);
    return $utilisateur;
}

?>


