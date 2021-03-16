<?php

require_once ('lib/misc/tableSys.php');

function getPostePrincipal($ref_budget) {
  // Fonction renvoyant l'ensemble des comptes clients associés

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_correspondance where poste_principal is not null and poste_niveau_1 is null and poste_niveau_2 is null and poste_niveau_2 is null and poste_niveau_3 is null and ref_budget = '$ref_budget' order by poste_principal";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}

function getSousPosteTableau($rang_poste_princ, $id_poste_princ, $ref_budget) {
  // Fonction renvoyant l'ensemble des comptes clients associés

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_correspondance where poste_principal = $rang_poste_princ and id !=$id_poste_princ and ref_budget = '$ref_budget' order by coalesce(poste_principal,0), coalesce(poste_niveau_1,0) ,coalesce(poste_niveau_2,0) ,coalesce(poste_niveau_3,0)";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}

function getDataPostePrincipal($id,$ref_budget = null) {
  // Fonction renvoyant l'ensemble des comptes clients associés
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_correspondance where id = $id ";

  if ($ref_budget != null){
    $sql .= " AND ref_budget= '$ref_budget'";
  }

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}

function getExoEnCours($id_exo = null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select *, extract(year from date_deb_exo) as debut_annee, extract(year from date_fin_exo) as fin_annee
from ad_exercices_compta where etat_exo = 1 ";
  if ($id_exo != null){
    $sql .= " AND id_exo_compta = $id_exo";
  }
  $sql .=" order by id_exo_compta desc limit 1";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}
function getExoEncoursIdExo(){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select id_exo_compta from ad_exercices_compta where etat_exo = 1 order by id_exo_compta desc ";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $CC[$row["id_exo_compta"]] = $row["id_exo_compta"];
  }
  $dbHandler->closeConnection(true);

  return $CC;

}

function getExoOuvert(){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select b.id_budget as id_budget_exo,b.ref_budget as ref_budget_exo,b.type_budget as type_budget_exo from ad_budget b INNER JOIN ad_exercices_compta c on b.exo_budget = c.id_exo_compta where c.etat_exo = 1";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $CC[$row["ref_budget_exo"]] = $row["ref_budget_exo"];
  }

  $dbHandler->closeConnection(true);
  return $CC;

}

function getExoEnCoursAll($getAllExo=null){//REL-104 - Ajout nouveau param
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  if ($getAllExo != null){
    $allExo = $getAllExo;
  }
  else{
    $allExo = 'etat_exo = 1';
  }

  $sql = "select *, extract(year from date_deb_exo) as debut_annee, extract(year from date_fin_exo) as fin_annee from ad_exercices_compta where $allExo order by id_exo_compta desc";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $CC[$row['id_exo_compta']] = $row;//array_push($CC, $row);

  return $CC;

}

function getExoDefini($id_exo){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_exercices_compta where id_exo_compta = $id_exo";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;
}

function getPosteBudget($Where){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_correspondance  ";

  if ($Where != ""){
    $sql .= " WHERE  ".$Where;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function InsertCorrespondance($DATA)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = buildInsertQuery ("ad_correspondance", $DATA);;
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  else {
    $sql = "SELECT max(id) from ad_correspondance";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $row = $result->fetchrow();
    $idcorrespondance = $row[0];
    $dbHandler->closeConnection(true);
    return $idcorrespondance;

  }
}

function InsertCpteCorrespondance($DATA)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = buildInsertQuery ("ad_budget_cpte_comptable", $DATA);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  else {

    $dbHandler->closeConnection(true);
    return true;

  }
}

function getComptesComptablesAssoc($id) {
  // Fonction renvoyant l'ensemble des comptes clients associés

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select b.id_ligne, array_to_string(array_agg(cpte_comptable),' - ') as comptable
  from ad_correspondance c
  INNER JOIN ad_budget_cpte_comptable b on b.id_ligne = c.id
  where b.id_ligne = $id
  group by b.id_ligne ";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}

function getCpteComptablesAssoc($id) {
  // Fonction renvoyant l'ensemble des comptes clients associés

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select b.id ,b.id_ligne, b.cpte_comptable, d.libel_cpte_comptable
  from ad_correspondance c
  INNER JOIN ad_budget_cpte_comptable b on b.id_ligne = c.id
  INNER JOIN ad_cpt_comptable d on d.num_cpte_comptable =  b.cpte_comptable
  where b.id_ligne = $id and d.cpte_centralise is not null
  order by cpte_comptable";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}

function checkCompteExist($Where){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_budget_cpte_comptable  ";

  if ($Where != ""){
    $sql .= " WHERE  ".$Where;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}
function checkCompte($Where){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_budget_cpte_comptable  ";

  if ($Where != ""){
    $sql .= " WHERE  ".$Where;
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

/**
 * //K
 * Fonction renvoyant les comptes comptables
 * @since
 * @param type budget / id de correspondance / compartiment
 * @return array of compte comptable
 */
function getComptesComptablesBudget($type_budget = null,$id_correspondance = null,$compartiment =null,$ref_budget=null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT distinct c.* FROM ad_cpt_comptable c INNER JOIN (select * from get_latest_cpte_comptables()) t on t.num_cpte_comptable = c.num_cpte_comptable  WHERE c.id_ag = $global_id_agence AND c.is_actif = 't' AND c.num_cpte_comptable not in (select distinct b.cpte_comptable from ad_budget_cpte_comptable b
INNER JOIN ad_correspondance p on p.id = b.id_ligne
WHERE p.poste_principal is not null
and p.poste_niveau_1 is null
and p.poste_niveau_2 is null
and p.poste_niveau_3 is null ";

  if ($type_budget != null) {

    $sql .= " and p.type_budget = ".$type_budget;
  }
  if ($id_correspondance != null){
    $sql .= " and b.id_ligne<> ".$id_correspondance;
  }
  if ($ref_budget != null){
    $sql .= " and p.ref_budget = '$ref_budget' ";
  }

  $sql .=" )";
  $sql .=" and c.num_cpte_comptable not in (select cpte_centralise from ad_cpt_comptable where cpte_centralise is not null)";
  if ($compartiment != null){
    $sql .= " and c.compart_cpte = ".$compartiment;
  }
  $sql .= " and c.cpte_centralise is not null";

  $sql .= " ORDER BY c.id_ag, c.num_cpte_comptable ASC";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $cptes = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $cptes[$row["num_cpte_comptable"]] = $row;
  }
  $dbHandler->closeConnection(true);

  return $cptes;

}

/**
 *
 * Fonction renvoyant les comptes comptables sans parametrage sur le perimetre Actif
 * @since
 * @param type budget / id de correspondance / compartiment
 * @return array of compte comptable
 */
function getComptesComptablesBudgetAll($type_budget = null,$id_correspondance = null,$compartiment =null,$ref_budget= null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT distinct c.* FROM ad_cpt_comptable c INNER JOIN (select * from get_latest_cpte_comptables()) t on t.num_cpte_comptable = c.num_cpte_comptable WHERE c.id_ag = $global_id_agence AND c.num_cpte_comptable not in (select distinct b.cpte_comptable from ad_budget_cpte_comptable b
INNER JOIN ad_correspondance p on p.id = b.id_ligne
INNER JOIN ad_budget u on u.ref_budget = p.ref_budget
WHERE p.poste_principal is not null
and p.poste_niveau_1 is null
and p.poste_niveau_2 is null
and p.poste_niveau_3 is null ";

  if ($type_budget != null) {

    $sql .= " and p.type_budget = ".$type_budget;
  }
  if ($id_correspondance != null){
    $sql .= " and b.id_ligne<> ".$id_correspondance;
  }
  if ($ref_budget != null){
    $sql .= " and u.ref_budget = '$ref_budget'";
  }

  $sql .=" )";
  $sql .=" and c.num_cpte_comptable not in (select cpte_centralise from ad_cpt_comptable where cpte_centralise is not null)";
  if ($compartiment != null){
    $sql .= " and c.compart_cpte = ".$compartiment;
  }
  $sql .= " and c.cpte_centralise is not null";

  $sql .= " ORDER BY c.id_ag, c.num_cpte_comptable ASC";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $cptes = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $cptes[$row["num_cpte_comptable"]] = $row;
  }
  $dbHandler->closeConnection(true);

  return $cptes;

}


/**
 * //K
 * Fonction renvoyant le poste superieur
 * @since
 * @param Where condition
 * @return array of compte comptable
 */
function getPosteSup($Where){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select id,
CASE WHEN poste_principal::text is not null and poste_niveau_1::text is null and poste_niveau_2::text is null and poste_niveau_3::text is null THEN poste_principal ::text ||' '|| description
WHEN poste_principal::text is not null and poste_niveau_1::text is not null and poste_niveau_2::text is null and poste_niveau_3::text is null THEN poste_principal::text ||'.'||poste_niveau_1::text ||' '|| description
WHEN poste_principal::text is not null and poste_niveau_1::text is not null and poste_niveau_2::text is not null and poste_niveau_3::text is null THEN poste_principal ::text||'.'|| poste_niveau_1::text ||'.'||poste_niveau_2::text ||' '|| description
WHEN poste_principal::text is not null and poste_niveau_1::text is not null and poste_niveau_2::text is not null and poste_niveau_3::text is not null THEN poste_principal::text ||'.'|| poste_niveau_1::text ||'.'|| poste_niveau_2::text||'.'|| poste_niveau_3::text ||' '|| description
END as post_sup
from ad_correspondance ";

  if ($Where != ""){
    $sql .= " WHERE  ".$Where;
  }
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $CC[$row["id"]] = $row['post_sup'];

  return $CC;

}


/**
 * //
 * Fonction renvoyant les rang du poste
 * @since
 * @param Where condition on existing poste or sous poste and Type budget
 * @return array of compte comptable
 */
function getRangPoste($type_budget,$where) {
  // Fonction renvoyant l'ensemble des comptes clients associés

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_correspondance where type_budget = $type_budget ";
  if ($where != ""){
    $sql .= " and  ".$where;
  }

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}



/**
 * //
 * Fonction renvoyant les id des sous comptes associe a une ligne budgetaire
 * @since
 * @param Where condition on existing poste or sous poste
 * @return array of compte comptable
 */
function getIdSousPoste($where) {
  // Fonction renvoyant l'ensemble des comptes clients associés

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select id from ad_correspondance ";
  if ($where != ""){
    $sql .= " WHERE ".$where;
  }

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}

function getSousComptePoste($where){

  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "select c.id_ligne, c.cpte_comptable , p.libel_cpte_comptable from ad_budget_cpte_comptable c INNER JOIN ad_cpt_comptable p on p.num_cpte_comptable = c.cpte_comptable INNER JOIN ad_correspondance o ON o.id = c.id_ligne";

  if ($where != null){
    $sql .= " where $where ";
  }
  $sql .= " ORDER BY  c.cpte_comptable";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $cptes = array();
  $count = 0;
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $count++;
    //$cptes[$row["id_ligne"]][$row["cpte_comptable"]] = $row["libel_cpte_comptable"];
    $cptes[$count] = $row["cpte_comptable"]."-".$row["libel_cpte_comptable"];
  }
  $dbHandler->closeConnection(true);

  return $cptes;


}

/**
 * Fonction qui retourne les types de budget dont une mise en place budget
 * a deja ete faite pour l'exercice courante
 * PARAM etat budget -> string (ex: < 1 or > 1 ....etc), is all exercice -> boolean default false
 * Return type : array of integers $DATAS
 */
function getTypBudgetFromTabBudget($etat_budget=null, $isAllExo=false){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $exo_courante = getExoEnCours();

  $sql = "select distinct type_budget from ad_budget where id_ag = $global_id_agence ";

  if ($etat_budget != null){
    $sql .= " and etat_budget ".$etat_budget;
  }

  if ($isAllExo === false){
    $sql .= " and exo_budget = ".$exo_courante['id_exo_compta'];
  }

  $sql .= " order by type_budget asc";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  //$DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $count = 0;
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)){
    $count++;
    $DATAS[$count] = $row;
  }
  $dbHandler->closeConnection(true);

  return $DATAS;
}

/**
 * Fonction pour recuperer la table de correspondance pour la mise en place du budget
 * PARAM : type de budget->integer
 * RETURN TYPE : array of data $DATAS
 */
function getTabCorrespondance($ref_budget){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select c.id, c.etat_correspondance, c.type_budget, c.poste_principal, c.poste_niveau_1, c.poste_niveau_2, c.poste_niveau_3, c.description, c.compartiment, array_to_string(array_agg(cpte.cpte_comptable),' - ') as cpte_correspondance from ad_correspondance c inner join ad_budget_cpte_comptable cpte on cpte.id_ligne = c.id where c.ref_budget = '$ref_budget' and c.etat_correspondance = 't' and c.dernier_niveau = 't' and c.id_ag = $global_id_agence group by c.id, c.etat_correspondance, c.type_budget, c.poste_principal, c.poste_niveau_1, c.poste_niveau_2, c.poste_niveau_3, c.description, c.compartiment order by coalesce(c.poste_principal,0),coalesce(c.poste_niveau_1,0),coalesce(c.poste_niveau_2,0),coalesce(c.poste_niveau_3,0)   ";  //and array_length(regexp_split_to_array(cpte_correspondance, '-'),1) = 1

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $arr_correspondance = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($arr_correspondance, $row);
  }

  $dbHandler->closeConnection(true);
  return $arr_correspondance;
}

/**
 * Fonction pour mettre en place le budget annuel dans la BDD
 * PARAM : Array of data -> $DATA, Integer -> $COUNTDATA
 * Return Type : Object -> ErrorObj
 */
function miseEnPlaceBudget($DATA,$COUNTLIGNE){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //recupere id exercice en cour et creation reference (format : id_agence - id_exo - date du jour - type_budget) pour le budget
  $id_exo = getExoEnCours();
  $ref_budget = sprintf("%03d",$DATA['id_ag'])."-".$id_exo['id_exo_compta']."-".date('d').date('m').date('Y')."-".$DATA['type_budget'];

  //Insertion dans la table ad_budget
  if ($COUNTLIGNE == 1){ //Une seule entree dans la table ad_budget pour le(s) ligne(s) budgetaire(s)
    //$ad_budget_data = array();
   // $ad_budget_data['exo_budget'] = intval($id_exo['id_exo_compta']);
    //$ad_budget_data['ref_budget'] = $DATA['ref_budget'];
    //$ad_budget_data['type_budget'] = intval($DATA['type_budget']);
    $ad_budget_data['etat_budget'] = 1;
    $ad_budget_data['date_modif'] = date('r');
    //$ad_budget_data['id_ag'] = intval($DATA['id_ag']);
    $ad_budget_where['ref_budget'] = $DATA['ref_budget'];
    $insert_data_ad_budget = buildUpdateQuery('ad_budget',$ad_budget_data,$ad_budget_where);
    $result_ad_budget = $db->query($insert_data_ad_budget);
    if (DB::isError($result_ad_budget)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }

  //Insertion dans la table ad_ligne_budgetaire
  $ad_ligne_budgetaire_data = array();
  $ad_ligne_budgetaire_data['id_correspondance'] = intval($DATA['id_correspondance']);
  $ad_ligne_budgetaire_data['ref_budget'] = $DATA['ref_budget'];
  $ad_ligne_budgetaire_data['poste_budget'] = $DATA['poste'];
  $ad_ligne_budgetaire_data['mnt_trim1'] = recupMontant($DATA['mnt_trim1']);
  $ad_ligne_budgetaire_data['mnt_trim2'] = recupMontant($DATA['mnt_trim2']);
  $ad_ligne_budgetaire_data['mnt_trim3'] = recupMontant($DATA['mnt_trim3']);
  $ad_ligne_budgetaire_data['mnt_trim4'] = recupMontant($DATA['mnt_trim4']);
  $ad_ligne_budgetaire_data['date_creation'] = date('r');
  $ad_ligne_budgetaire_data['id_ag'] = intval($DATA['id_ag']);
  $ad_ligne_budgetaire_data['etat_bloque'] = $DATA['etat_bloque'];
  $ad_ligne_budgetaire_data['etat_ligne'] = 1;
  $insert_data_ligne_budgetaire = buildInsertQuery('ad_ligne_budgetaire',$ad_ligne_budgetaire_data);
  $result_ad_ligne_budgetaire = $db->query($insert_data_ligne_budgetaire);
  if (DB::isError($result_ad_ligne_budgetaire)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array("type_budget" => $DATA['type_budget'], "ref_budget" => $DATA['ref_budget'], "annee" => date('Y')));
}

/**
 * Fonction pour mettre a jour le budget annuel dans la BDD
 * PARAM : Array of data -> $DATA, Integer -> $COUNTDATA
 * Return Type : Object -> ErrorObj
 */
function raffinerBudget($DATA,$COUNTLIGNE,$exo_budget = null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //recupere id exercice en cour
  $id_exo = getExoEnCours();

  //Mise a jour dans la table ad_budget
  if ($COUNTLIGNE == 1){ //Une seule fois mise a jour dans la table ad_budget pour le(s) ligne(s) budgetaire(s)
    //data
    $ad_budget_data = array();
    $ad_budget_data['etat_budget'] = 2;
    $ad_budget_data['date_modif'] = date('r');
    //where condition
    if ($exo_budget != null){
      $where_ad_budget["exo_budget"] = $exo_budget;
    }else{
      $where_ad_budget["exo_budget"] = $id_exo['id_exo_compta'];
    }
    $where_ad_budget["ref_budget"] = $DATA['ref_budget'];
    $where_ad_budget["type_budget"] = $DATA['type_budget'];
    $where_ad_budget["id_ag"] = $global_id_agence;
    $update_data_ad_budget = buildUpdateQuery('ad_budget',$ad_budget_data,$where_ad_budget);
    $result_ad_budget = $db->query($update_data_ad_budget);
    if (DB::isError($result_ad_budget)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }

  //Mise a jour dans la table ad_ligne_budgetaire
  //data
  $ad_ligne_budgetaire_data = array();
  $ad_ligne_budgetaire_data['mnt_trim1'] = recupMontant($DATA['mnt_trim1']);
  $ad_ligne_budgetaire_data['mnt_trim2'] = recupMontant($DATA['mnt_trim2']);
  $ad_ligne_budgetaire_data['mnt_trim3'] = recupMontant($DATA['mnt_trim3']);
  $ad_ligne_budgetaire_data['mnt_trim4'] = recupMontant($DATA['mnt_trim4']);
  $ad_ligne_budgetaire_data['date_modif'] = date('r');
  $ad_ligne_budgetaire_data['etat_bloque'] = $DATA['etat_bloque'];
  //condition
  $where_ligne_budgetaire['id_correspondance'] = intval($DATA['id_correspondance']);
  $where_ligne_budgetaire['ref_budget'] = $DATA['ref_budget'];
  $where_ligne_budgetaire['poste_budget'] = $DATA['poste'];
  $where_ligne_budgetaire['id_ag'] = intval($DATA['id_ag']);
  $update_data_ligne_budgetaire = buildUpdateQuery('ad_ligne_budgetaire',$ad_ligne_budgetaire_data,$where_ligne_budgetaire);
  $result_ad_ligne_budgetaire = $db->query($update_data_ligne_budgetaire);
  if (DB::isError($result_ad_ligne_budgetaire)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array("type_budget" => $DATA['type_budget'], "ref_budget" => $DATA['ref_budget'], "annee" => date('Y')));
}

/**
 * Fonction pour reviser le budget annuel dans la BDD
 * PARAM : Array of data -> $DATA, Integer -> $COUNTDATA
 * Return Type : Object -> ErrorObj
 */
function reviserBudget($DATA,$COUNTLIGNE){
  global $dbHandler, $global_id_agence, $global_id_utilisateur;
  $db = $dbHandler->openConnection();

  //recupere id exercice en cour
  $id_exo = getExoEnCours();

  //Mise a jour dans la table ad_budget
  if ($COUNTLIGNE == 1){ //Une seule fois mise a jour dans la table ad_budget pour le(s) ligne(s) budgetaire(s)
    //data
    $ad_budget_data = array();
    $ad_budget_data['etat_budget'] = 4;
    $ad_budget_data['date_modif'] = date('r');
    //where condition
    $where_ad_budget["exo_budget"] = $DATA['id_exo'];
    $where_ad_budget["ref_budget"] = $DATA['ref_budget'];
    $where_ad_budget["type_budget"] = $DATA['type_budget'];
    $where_ad_budget["id_ag"] = $global_id_agence;
    $update_data_ad_budget = buildUpdateQuery('ad_budget',$ad_budget_data,$where_ad_budget);
    $result_ad_budget = $db->query($update_data_ad_budget);
    if (DB::isError($result_ad_budget)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }

  //Mise a jour pour etat compte bloque dans la table ad_ligne_budgetaire
  //data
  $ad_ligne_budget_data = array();
  $ad_ligne_budget_data['etat_bloque'] = $DATA['etat_bloque'];
  $ad_ligne_budget_data['date_modif'] = date('r');
  //where condition
  $where_ad_ligne_budget["id_ligne"] = $DATA['ligne_budgetaire'];
  $where_ad_ligne_budget["ref_budget"] = $DATA['ref_budget'];
  $where_ad_ligne_budget["poste_budget"] = $DATA['poste'];
  $where_ad_ligne_budget["id_ag"] = $global_id_agence;
  $update_data_ad_ligne_budget = buildUpdateQuery('ad_ligne_budgetaire',$ad_ligne_budget_data,$where_ad_ligne_budget);
  $result_ad_ligne_budget = $db->query($update_data_ad_ligne_budget);
  if (DB::isError($result_ad_ligne_budget)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  //Mise en place/Mise a jour dans la table ad_revision_budgetaire
  for($i = 1;$i <= 4;$i++){ //loop 4 fois pour les trimestres
    $id_revision_trimestre = getIdRevisionTrimestre($DATA['id_exo'],$DATA['ref_budget'],$DATA['ligne_budgetaire'],$i);
    if ($DATA['hasRevisions']>=1 && $id_revision_trimestre != null){ //si la revision existe deja on fait une mise a jour
      if ($DATA['isTrimestre'.$i.'Open']=='t' && $DATA['mnt_trim'.$i] != null){
        $ad_ligne_revision_data['anc_montant'] = recupMontant($DATA['anc_mnt_trim'.$i]);
        $ad_ligne_revision_data['nouv_montant'] = recupMontant($DATA['mnt_trim'.$i]);
        $ad_ligne_revision_data['id_util_revise'] = $global_id_utilisateur;
        $ad_ligne_revision_data['date_modif'] = date('r');

        $where_ligne_revision['id_revision'] = $id_revision_trimestre;
        $where_ligne_revision['exo_budget'] = $DATA['id_exo'];
        $where_ligne_revision['ref_budget'] = $DATA['ref_budget'];
        $where_ligne_revision['id_ligne_budget'] = $DATA['ligne_budgetaire'];
        $where_ligne_revision['etat_revision'] = 1;
        $where_ligne_revision['id_trimestre'] = $i;
        $where_ligne_revision['id_ag'] = $global_id_agence;
        $update_data_ligne_revision = buildUpdateQuery('ad_revision_historique',$ad_ligne_revision_data,$where_ligne_revision);
        $result_ligne_revision = $db->query($update_data_ligne_revision);
        if (DB::isError($result_ligne_revision)) {
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
          $dbHandler->closeConnection(false);
        }
      }
    }
    else{ //si une nouvelle revision on fait une mise en place
      if ($DATA['isTrimestre'.$i.'Open']=='t' && $DATA['mnt_trim'.$i] != null){
        $ad_ligne_revision_data['exo_budget'] = $DATA['id_exo'];
        $ad_ligne_revision_data['ref_budget'] = $DATA['ref_budget'];
        $ad_ligne_revision_data['id_ligne_budget'] = $DATA['ligne_budgetaire'];
        $ad_ligne_revision_data['id_trimestre'] = $i;
        $ad_ligne_revision_data['anc_montant'] = recupMontant($DATA['anc_mnt_trim'.$i]);
        $ad_ligne_revision_data['nouv_montant'] = recupMontant($DATA['mnt_trim'.$i]);
        $ad_ligne_revision_data['id_util_revise'] = $global_id_utilisateur;
        $ad_ligne_revision_data['etat_revision'] = 1;
        $ad_ligne_revision_data['date_creation'] = date('r');
        $ad_ligne_revision_data['id_ag'] = $global_id_agence;
        $insert_data_ligne_revision = buildInsertQuery('ad_revision_historique',$ad_ligne_revision_data);
        $result_ligne_revision = $db->query($insert_data_ligne_revision);
        if (DB::isError($result_ligne_revision)) {
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
          $dbHandler->closeConnection(false);
        }
      }
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array("type_budget" => $DATA['type_budget'], "ref_budget" => $DATA['ref_budget'], "annee" => date('Y')));
}

/**
 * Fonction pour valider le budget annuel dans la BDD
 * PARAM : Array of data -> $DATA, Integer -> $COUNTDATA, Boolean -> isRevision default false
 * Return Type : Object -> ErrorObj
 */
function validerBudget($DATA,$COUNTLIGNE,$isRevision=false,$id_exo = false){
  global $dbHandler, $global_id_agence, $global_id_utilisateur;
  $db = $dbHandler->openConnection();

  //recupere id exercice en cour
  //$id_exo = getExoEnCours();

  //Mise a jour dans la table ad_budget pour raffinement
  if ($COUNTLIGNE == 1 && $isRevision === false){ //Une seule fois mise a jour dans la table ad_budget pour le(s) ligne(s) budgetaire(s)
    //data
    $ad_budget_data = array();
    $ad_budget_data['etat_budget'] = 3;
    $ad_budget_data['date_modif'] = date('r');
    //where condition
    $where_ad_budget["exo_budget"] = $id_exo;
    $where_ad_budget["ref_budget"] = $DATA['ref_budget'];
    $where_ad_budget["type_budget"] = $DATA['type_budget'];
    $where_ad_budget["id_ag"] = $global_id_agence;
    $update_data_ad_budget = buildUpdateQuery('ad_budget',$ad_budget_data,$where_ad_budget);
    $result_ad_budget = $db->query($update_data_ad_budget);
    if (DB::isError($result_ad_budget)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }

  if ($isRevision === true){ // Validation revision lignes budgetaires
    //Mise a jour dans la table ad_revision_budgetaire/ad_ligne_budgetaire
    for($i = 1;$i <= 4;$i++){ //loop 4 fois pour les trimestres
      $id_revision_trimestre = getIdRevisionTrimestre($id_exo['id_exo_compta'],$DATA['ref_budget'],$DATA['id_ligne_budgetaire'],$i);
      if ($id_revision_trimestre != null){ //si la revision existe deja on fait une mise a jour dans la table ad_revision_budgetaire
        if ($DATA['isTrimestre'.$i.'Open']=='t'){
          //Pour mise a jour ad_revision_historique
          //data
          $ad_ligne_revision_data['etat_revision'] = 2;
          $ad_ligne_revision_data['id_util_valide'] = $global_id_utilisateur;
          $ad_ligne_revision_data['date_modif'] = date('r');
          //condition
          $where_ligne_revision['id_revision'] = $id_revision_trimestre;
          $where_ligne_revision['exo_budget'] = $id_exo['id_exo_compta'];
          $where_ligne_revision['ref_budget'] = $DATA['ref_budget'];
          $where_ligne_revision['id_ligne_budget'] = $DATA['id_ligne_budgetaire'];
          $where_ligne_revision['id_trimestre'] = $i;
          $where_ligne_revision['id_ag'] = $global_id_agence;
          $update_data_ligne_revision = buildUpdateQuery('ad_revision_historique',$ad_ligne_revision_data,$where_ligne_revision);
          $result_ligne_revision = $db->query($update_data_ligne_revision);
          if (DB::isError($result_ligne_revision)) {
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
            $dbHandler->closeConnection(false);
          }
          //Pour mise a jour table ad_ligne_budgetaire chaque trimestre ouvert
          //data
          $ad_ligne_budget_data['mnt_trim'.$i] = $DATA['nouv_mnt_trim'.$i];
        }
      }
    }
    //Pour mise a jour table ad_ligne_budgetaire
    //data
    $ad_ligne_budget_data['etat_bloque'] = $DATA['etat_bloque'];
    $ad_ligne_budget_data['date_modif'] = date('r');
    //condition
    $where_ligne_budget['id_ligne'] = $DATA['id_ligne_budgetaire'];
    $where_ligne_budget['ref_budget'] = $DATA['ref_budget'];
    $where_ligne_budget['id_ag'] = $global_id_agence;
    $update_data_ligne_budget = buildUpdateQuery('ad_ligne_budgetaire',$ad_ligne_budget_data,$where_ligne_budget);
    $result_ligne_budget = $db->query($update_data_ligne_budget);
    if (DB::isError($result_ligne_budget)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }
  else{ // Validation raffinement lignes budgetaires
    //Mise a jour dans la table ad_ligne_budgetaire
    //data
    $ad_ligne_budgetaire_data = array();
    $ad_ligne_budgetaire_data['date_modif'] = date('r');
    $ad_ligne_budgetaire_data['etat_bloque'] = $DATA['etat_bloque'];
    $ad_ligne_budgetaire_data['etat_ligne'] = 2;
    //condition
    $where_ligne_budgetaire['id_correspondance'] = intval($DATA['id_correspondance']);
    $where_ligne_budgetaire['ref_budget'] = $DATA['ref_budget'];
    $where_ligne_budgetaire['poste_budget'] = $DATA['poste'];
    $where_ligne_budgetaire['id_ag'] = intval($DATA['id_ag']);
    $update_data_ligne_budgetaire = buildUpdateQuery('ad_ligne_budgetaire',$ad_ligne_budgetaire_data,$where_ligne_budgetaire);
    $result_ad_ligne_budgetaire = $db->query($update_data_ligne_budgetaire);
    if (DB::isError($result_ad_ligne_budgetaire)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array("type_budget" => $DATA['type_budget'], "ref_budget" => $DATA['ref_budget'], "annee" => date('Y')));
}

/**
 * Fonction pour recuperer les donnees des tables correspondance, budget et ligne budgetaire
 * PARAM : type de budget->integer, id exo->integer, etat budget->string (ex: < 1 or > 1...etc)
 * RETURN TYPE : array of data $DATAS
 */
function getTabBudget($type_budget,$id_exo,$etat_budget=null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select l.id_correspondance, l.poste_budget as poste, c.description, c.compartiment, array_to_string(array_agg(cpte.cpte_comptable),' - ') as cpte_correspondance, l.etat_bloque, l.mnt_trim1, l.mnt_trim2, l.mnt_trim3, l.mnt_trim4, (l.mnt_trim1+l.mnt_trim2+l.mnt_trim3+l.mnt_trim4) as mnt_annuel, b.ref_budget, b.etat_budget from ad_budget b inner join ad_ligne_budgetaire l on b.ref_budget = l.ref_budget inner join ad_correspondance c on l.id_correspondance = c.id inner join ad_budget_cpte_comptable cpte on cpte.id_ligne = c.id where b.type_budget = $type_budget and l.etat_ligne in (1,2) and b.exo_budget = $id_exo and b.id_ag = $global_id_agence";
  if ($etat_budget != null){
    $sql .= " and b.etat_budget ".$etat_budget;
  }
  $sql .= " group by l.id_correspondance, poste, c.description, c.compartiment, l.etat_bloque, l.mnt_trim1, l.mnt_trim2, l.mnt_trim3, l.mnt_trim4, b.ref_budget, b.etat_budget, coalesce(c.poste_principal,0),coalesce(c.poste_niveau_1,0),coalesce(c.poste_niveau_2,0),coalesce(c.poste_niveau_3,0) order by coalesce(c.poste_principal,0),coalesce(c.poste_niveau_1,0),coalesce(c.poste_niveau_2,0),coalesce(c.poste_niveau_3,0) ";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $arr_budget = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($arr_budget, $row);
  }

  $dbHandler->closeConnection(true);
  return $arr_budget;
}

/**
 * Fonction pour recuperer les donnees des tables correspondance, budget et ligne budgetaire pour la revision du budget
 * PARAM : type de budget->integer, id exo->integer, etat budget->string (ex: < 1 or > 1...etc)
 * RETURN TYPE : array of data $DATAS
 */
function getTabRevisionBudget($type_budget,$id_exo,$etat_budget=null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT c.id, c.type_budget, c.description, c.compartiment, array_to_string(array_agg(cpte.cpte_comptable),'-') AS cpte_comptable, b.id_budget, b.ref_budget, b.etat_budget, l.id_ligne, l.id_correspondance, l.poste_budget AS poste,
l.mnt_trim1 AS mnt_budget1, COALESCE(l.prc_utilisation_trim1,0) AS prc_utilisation_trim1, COALESCE((SELECT CASE WHEN id_trimestre = 1 THEN nouv_montant END AS new_mnt FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND id_trimestre = 1 AND etat_revision IN (1) ORDER BY id_revision DESC LIMIT 1),0) AS nouv_mnt_trim1,
l.mnt_trim2 AS mnt_budget2, COALESCE(l.prc_utilisation_trim2,0) AS prc_utilisation_trim2, COALESCE((SELECT CASE WHEN id_trimestre = 2 THEN nouv_montant END AS new_mnt FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND id_trimestre = 2 AND etat_revision IN (1) ORDER BY id_revision DESC LIMIT 1),0) AS nouv_mnt_trim2,
l.mnt_trim3 AS mnt_budget3, COALESCE(l.prc_utilisation_trim3,0) AS prc_utilisation_trim3, COALESCE((SELECT CASE WHEN id_trimestre = 3 THEN nouv_montant END AS new_mnt FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND id_trimestre = 3 AND etat_revision IN (1) ORDER BY id_revision DESC LIMIT 1),0) AS nouv_mnt_trim3,
l.mnt_trim4 AS mnt_budget4, COALESCE(l.prc_utilisation_trim4,0) AS prc_utilisation_trim4, COALESCE((SELECT CASE WHEN id_trimestre = 4 THEN nouv_montant END AS new_mnt FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND id_trimestre = 4 AND etat_revision IN (1) ORDER BY id_revision DESC LIMIT 1),0) AS nouv_mnt_trim4,
l.etat_bloque, (SELECT COUNT(id_revision) FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND etat_revision = 1) AS hasRevision FROM ad_correspondance c INNER JOIN ad_ligne_budgetaire l ON l.id_correspondance = c.id INNER JOIN ad_budget b ON b.ref_budget = l.ref_budget
INNER JOIN ad_budget_cpte_comptable cpte ON cpte.id_ligne = c.id WHERE b.type_budget = $type_budget AND b.exo_budget = $id_exo AND b.id_ag = numagc() AND l.etat_ligne = 2 AND (SELECT COUNT(id_revision) FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND etat_revision = 1) >= 0 AND coalesce((SELECT coalesce(etat_revision,0) FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne ORDER BY id_revision DESC LIMIT 1),0) <= 2";
  if ($etat_budget != null){
    $sql .= " AND b.etat_budget ".$etat_budget;
  }
  $sql .= " GROUP BY c.id, c.type_budget, c.description, c.compartiment, b.id_budget, b.ref_budget, b.etat_budget, l.id_ligne, l.id_correspondance, poste, mnt_budget1, mnt_budget2, mnt_budget3, mnt_budget4, l.etat_bloque, coalesce(c.poste_principal,0),coalesce(c.poste_niveau_1,0),coalesce(c.poste_niveau_2,0),coalesce(c.poste_niveau_3,0), prc_utilisation_trim1, prc_utilisation_trim2, prc_utilisation_trim3, prc_utilisation_trim4 ORDER BY coalesce(c.poste_principal,0),coalesce(c.poste_niveau_1,0),coalesce(c.poste_niveau_2,0),coalesce(c.poste_niveau_3,0) ";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $arr_revision = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($arr_revision, $row);
  }

  $dbHandler->closeConnection(true);
  return $arr_revision;
}

/**
 * Fonction pour recuperer les donnees des tables correspondance, budget et ligne budgetaire pour la validation revision du budget
 * PARAM : type de budget->integer, id exo->integer, etat budget->string (ex: < 1 or > 1...etc)
 * RETURN TYPE : array of data $DATAS
 */
function getValidationRevisionBudget($type_budget,$id_exo,$etat_budget=null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT c.id, c.type_budget, c.description, c.compartiment, array_to_string(array_agg(cpte.cpte_comptable),'-') AS cpte_correspondance, b.id_budget, b.ref_budget, b.etat_budget, l.id_ligne, l.id_correspondance, l.poste_budget AS poste,
l.mnt_trim1, COALESCE(l.prc_utilisation_trim1,0) AS prc_utilisation_trim1, COALESCE((SELECT CASE WHEN id_trimestre = 1 THEN nouv_montant END AS new_mnt FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND id_trimestre = 1 AND etat_revision = 1 ORDER BY id_revision DESC LIMIT 1),0) AS nouv_mnt_trim1,
l.mnt_trim2, COALESCE(l.prc_utilisation_trim2,0) AS prc_utilisation_trim2, COALESCE((SELECT CASE WHEN id_trimestre = 2 THEN nouv_montant END AS new_mnt FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND id_trimestre = 2 AND etat_revision = 1 ORDER BY id_revision DESC LIMIT 1),0) AS nouv_mnt_trim2,
l.mnt_trim3, COALESCE(l.prc_utilisation_trim3,0) AS prc_utilisation_trim3, COALESCE((SELECT CASE WHEN id_trimestre = 3 THEN nouv_montant END AS new_mnt FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND id_trimestre = 3 AND etat_revision = 1 ORDER BY id_revision DESC LIMIT 1),0) AS nouv_mnt_trim3,
l.mnt_trim4, COALESCE(l.prc_utilisation_trim4,0) AS prc_utilisation_trim4, COALESCE((SELECT CASE WHEN id_trimestre = 4 THEN nouv_montant END AS new_mnt FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND id_trimestre = 4 AND etat_revision = 1 ORDER BY id_revision DESC LIMIT 1),0) AS nouv_mnt_trim4,
l.etat_bloque FROM ad_correspondance c INNER JOIN ad_ligne_budgetaire l ON l.id_correspondance = c.id INNER JOIN ad_budget b ON b.ref_budget = l.ref_budget
INNER JOIN ad_budget_cpte_comptable cpte ON cpte.id_ligne = c.id WHERE b.type_budget = $type_budget AND b.exo_budget = $id_exo AND b.id_ag = numagc() AND l.etat_ligne =2 AND (SELECT COUNT(id_revision) FROM ad_revision_historique WHERE id_ligne_budget = l.id_ligne AND etat_revision = 1) >= 1";
  if ($etat_budget != null){
    $sql .= " AND b.etat_budget ".$etat_budget;
  }
  $sql .= " GROUP BY c.id, c.type_budget, c.description, c.compartiment, b.id_budget, b.ref_budget, b.etat_budget, l.id_ligne, l.id_correspondance, poste, mnt_trim1, mnt_trim2, mnt_trim3, mnt_trim4, l.etat_bloque, coalesce(c.poste_principal,0),coalesce(c.poste_niveau_1,0),coalesce(c.poste_niveau_2,0),coalesce(c.poste_niveau_3,0), prc_utilisation_trim1, prc_utilisation_trim2, prc_utilisation_trim3, prc_utilisation_trim4 ORDER BY coalesce(c.poste_principal,0),coalesce(c.poste_niveau_1,0),coalesce(c.poste_niveau_2,0),coalesce(c.poste_niveau_3,0) ";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $arr_revision = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($arr_revision, $row);
  }

  $dbHandler->closeConnection(true);
  return $arr_revision;
}

/**
 * Fonction pour recuperer les donnees exercices dont on avait enregistrer un budget
 * PARAM : Aucun
 * RETURN TYPE : array of data $DATAS
 */
function getAllExerciceBudget(){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select distinct b.exo_budget, case when to_char(e.date_deb_exo,'YYYY') = to_char(e.date_fin_exo,'YYYY') then to_char(e.date_deb_exo,'YYYY') else to_char(e.date_deb_exo,'YYYY')||'-'||to_char(e.date_fin_exo,'YYYY') end as annee from ad_budget b inner join ad_exercices_compta e on b.exo_budget = e.id_exo_compta and b.id_ag = $global_id_agence";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $arr_exercice = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $arr_exercice[$row['exo_budget']] = $row['annee'];
  }

  $dbHandler->closeConnection(true);
  return $arr_exercice;
}

function deleteCompteComptableAssocie($id_correspondance,$id_ligne = null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "DELETE FROM ad_budget_cpte_comptable WHERE id_ligne = $id_correspondance and id_ag=$global_id_agence ";
  if ($id_ligne != NULL)
    $sql .= " and id = $id_ligne ";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

function checkIfSousCompteAssocierExistSupression($id_ligne,$id) {

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_budget_cpte_comptable where id_ligne =  $id_ligne and cpte_comptable = (select cpte_comptable from ad_budget_cpte_comptable where id = $id)";
  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}

function deleteLigneBudgetaireNull($id){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "DELETE FROM ad_correspondance WHERE id = $id and id_ag=$global_id_agence ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/* Fonction pour renvoyer le trimestre courant
 * PARAM : Aucun
 * RETURN TYPE : integer
 */
function getTrimestre($id_exo = null){
  $trimestre = -1;

  //date du jour
  $dateDuJour = date('Y-m-d');

  //Recupere les trimestres
  $arrTrimestres = getPeriodeTrimestres($id_exo);
  foreach($arrTrimestres as $trim => $data){
    if ($dateDuJour >= $data['date_debut'] && $dateDuJour <= $data['date_fin']){
      $trimestre = $trim;
    }
  }

  return $trimestre;
}

/**
 * Fonction pour creer/renvoyer les periodes trimestres basant sur la date debut de l'exercice courante
 * PARAM : Aucun
 * RETURN TYPE : array of data $arrPeriodeTrimestres
 */
function getPeriodeTrimestres($id_exo){
  //Recupere exercice courante
  if($id_exo !=null){
    $exo_courante = getExoEnCours($id_exo);
  }else {
    $exo_courante = getExoEnCours();
  }

  //Recupere mois, jour et annee de la date debut de l'exercice courante
  $date_debut_exo = $exo_courante['date_deb_exo'];
  $date_debut_exo = explode('-',$date_debut_exo);

  $arrPeriodeTrimestres = array();
  //creation periode trimestre 1: date debut et date fin
  $date_deb_trim1 = $exo_courante['date_deb_exo'];
  $date_fin_trim1 = date("Y-m-d", mktime(0,0,0,$date_debut_exo[1]+3,$date_debut_exo[2]-1,$date_debut_exo[0]));
  $arrPeriodeTrimestres[1]['date_debut']=$date_deb_trim1;
  $arrPeriodeTrimestres[1]['date_fin']=$date_fin_trim1;

  //creation periode trimestre 2: date debut et date fin
  $date_deb_trim2 = date("Y-m-d", mktime(0,0,0,$date_debut_exo[1]+3,$date_debut_exo[2],$date_debut_exo[0]));
  $date_fin_trim2 = date("Y-m-d", mktime(0,0,0,$date_debut_exo[1]+6,$date_debut_exo[2]-1,$date_debut_exo[0]));
  $arrPeriodeTrimestres[2]['date_debut']=$date_deb_trim2;
  $arrPeriodeTrimestres[2]['date_fin']=$date_fin_trim2;

  //creation periode trimestre 3: date debut et date fin
  $date_deb_trim3 = date("Y-m-d", mktime(0,0,0,$date_debut_exo[1]+6,$date_debut_exo[2],$date_debut_exo[0]));
  $date_fin_trim3 = date("Y-m-d", mktime(0,0,0,$date_debut_exo[1]+9,$date_debut_exo[2]-1,$date_debut_exo[0]));
  $arrPeriodeTrimestres[3]['date_debut']=$date_deb_trim3;
  $arrPeriodeTrimestres[3]['date_fin']=$date_fin_trim3;

  //creation periode trimestre 4: date debut et date fin
  $date_deb_trim4 = date("Y-m-d", mktime(0,0,0,$date_debut_exo[1]+9,$date_debut_exo[2],$date_debut_exo[0]));
  $date_fin_trim4 = date("Y-m-d", mktime(0,0,0,$date_debut_exo[1]+12,$date_debut_exo[2]-1,$date_debut_exo[0]));
  $arrPeriodeTrimestres[4]['date_debut']=$date_deb_trim4;
  $arrPeriodeTrimestres[4]['date_fin']=$date_fin_trim4;

  return $arrPeriodeTrimestres;
}

/**
 * Fonction pour recuperer id revision si on a deja une entré pour ce trimestre dont etat revision est 1
 * PARAM : id exercice, la reference du budget, la ligne budgetaire et le trimestre en jeu
 * RETURN TYPE : integer
 */
function getIdRevisionTrimestre($exo_budget, $ref_budget, $id_ligne_budget, $id_trimestre){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $idRevision = null;

  $sql = "SELECT id_revision FROM ad_revision_historique WHERE id_trimestre = $id_trimestre AND ref_budget = '$ref_budget' AND id_ligne_budget = $id_ligne_budget AND exo_budget = $exo_budget AND etat_revision = 1 AND id_ag = $global_id_agence ORDER BY id_revision DESC LIMIT 1";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $idRevision = $row['id_revision'];
  }

  $dbHandler->closeConnection(true);
  return $idRevision;
}

/**
 * Fonction pour recuperer tous les ligne budgetaires bloquer
 * PARAM : type de budget
 * RETURN TYPE : array of ligne budgetaire
 */
function getCompteBlock($type) {
  // Fonction renvoyant l'ensemble des comptes bloqués
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "Select  cb.id_bloc, cb.ligne_budgetaire, c.description, cb.cpte_comptable from ad_budget_cpt_bloquer cb
INNER JOIN ad_ligne_budgetaire b on b.id_ligne = cb.ligne_budgetaire
INNER JOIN ad_correspondance c on c.id = b.id_correspondance
INNER JOIN ad_budget g on g.ref_budget = b.ref_budget
WHERE g.type_budget = $type and cpte_bloquer = 't'
ORDER BY cb.ligne_budgetaire";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;
}

/**
 * Fonction pour recuperer la ligne budgetaire bloquer
 * PARAM : id_bloc
 * RETURN TYPE : row
 */
function getLigneBudgetaireBlock($id_block){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql= "Select  cb.ligne_budgetaire, c.description, cb.cpte_comptable, b.mnt_trim1, b.mnt_trim2, b.mnt_trim3, b.mnt_trim4, (b.mnt_trim1+b.mnt_trim2+b.mnt_trim3+b.mnt_trim4) as total_annuel  from ad_budget_cpt_bloquer cb
INNER JOIN ad_ligne_budgetaire b on b.id_ligne = cb.ligne_budgetaire
INNER JOIN ad_correspondance c on c.id = b.id_correspondance
INNER JOIN ad_budget g on g.ref_budget = b.ref_budget
WHERE cb.id_bloc = $id_block";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

/**
 * Fonction pour recuperer id correspondance a partir de id_block comme parametre
 * PARAM : id_bloc
 * RETURN TYPE : array de comptes
 */
function getCompteComptableBlock($id_bloc){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select cpte.cpte_comptable,cp.etat_cpte from ad_budget_cpte_comptable cpte
INNER JOIN ad_ligne_budgetaire b on b.id_correspondance = cpte.id_ligne
INNER JOIN ad_budget_cpt_bloquer k on k.ligne_budgetaire = b.id_ligne
INNER JOIN ad_cpt_comptable cp on cp.num_cpte_comptable = cpte.cpte_comptable
WHERE id_bloc = $id_bloc  ";

  $result = $db->query($sql);
  $dbHandler->closeConnection(true);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($CC, $row);

  return $CC;

}

/**
 * Fonction pour verifier si on a toujour une revision pour un budget
 * PARAM : reference budget
 * RETURN TYPE : boolean -> true/false
 */
function chkExistRevision($ref_budget){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $isThereRevision = false;



  //Recupere exercice courante
  $exo_courante = getExoEnCours();

  $sql = "SELECT b.id_budget, b.ref_budget, count(r.id_revision) as Unvalidated_Revisions FROM ad_budget b INNER JOIN ad_revision_historique r ON r.ref_budget = b.ref_budget WHERE b.exo_budget = ".$exo_courante['id_exo_compta']." AND b.ref_budget = '$ref_budget' AND b.etat_budget = 4 AND r.etat_revision = 1 AND b.id_ag = $global_id_agence GROUP BY b.id_budget, b.ref_budget";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  if ($result->numRows() > 0) {
    $isThereRevision = true;
  }

  $dbHandler->closeConnection(true);
  return $isThereRevision;
}

/**
 * Fonction pour changer etat budget si tout les revisions pour ce dernier ont ete validés
 * PARAM : type budget et reference budget
 * Return Type : Object -> ErrorObj
 */
function changeEtatBudgetRevision($type_budget, $ref_budget,$id_exo = null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $chkExistRevision = true;

  //Recupere exercice courante
  //$exo_courante = getExoEnCours();

  $chkExistRevision = chkExistRevision($ref_budget);

  if (!$chkExistRevision){
    //data
    $ad_budget_data['etat_budget'] = 5;
    $ad_budget_data['date_modif'] = date('r');
    //condition
    $where_budget['type_budget'] = $type_budget;
    $where_budget['ref_budget'] = $ref_budget;
    $where_budget['exo_budget'] = $id_exo;
    $where_budget['id_ag'] = $global_id_agence;
    $update_data_budget = buildUpdateQuery('ad_budget',$ad_budget_data,$where_budget);
    $result_budget = $db->query($update_data_budget);
    if (DB::isError($result_budget)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Fonction pour recuperer les donnees pour le rapport etat d execution budgetaire
 * PARAM : type budget, exo budget, date debut trimestre et date fin
 * Return Type : array
 */
function getDataRapportEtatExecutionBudgetaire($type_budget, $exo_budget, $date_debut_trim, $date_fin){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();


  $sql = "select * from (
select * from etat_execution_budget($exo_budget,$type_budget, '$date_debut_trim', '$date_fin')
UNION
SELECT distinct * from etat_execution_budget_complet($exo_budget,$type_budget, '$date_debut_trim','$date_fin') ) z
order by
case when split_part(poste,'.',1) = '' then 0 else split_part(poste,'.',1)::int end ,
case when split_part(poste,'.',2) = '' then 0 else split_part(poste,'.',2)::int end ,
case when split_part(poste,'.',3) = '' then 0 else split_part(poste,'.',3)::int end ,
case when split_part(poste,'.',4) = '' then 0 else split_part(poste,'.',4)::int end ,
case when split_part(poste,'.',5) = '' then 0 else split_part(poste,'.',5)::int end ,
case when split_part(poste,'.',6) = '' then 0 else split_part(poste,'.',6)::int end ,
case when split_part(poste,'.',7) = '' then 0 else split_part(poste,'.',7)::int end ,
case when split_part(poste,'.',8) = '' then 0 else split_part(poste,'.',8)::int end ,
case when split_part(poste,'.',9) = '' then 0 else split_part(poste,'.',9)::int end ,
case when split_part(poste,'.',10) = '' then 0 else split_part(poste,'.',10)::int end";


  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$type_budget][$row["poste"]]['poste']=$row['poste'];
    $poste_explode = explode('.',$row['poste']);
    if ($poste_explode[0] != null && $poste_explode[1] == null && $poste_explode[2] == null && $poste_explode[3] == null){
      $DATAS[$type_budget][$row["poste"]]['niveau'] = 0;
    }else if($poste_explode[0] != null && $poste_explode[1] != null && $poste_explode[2] == null && $poste_explode[3] == null){
      $DATAS[$type_budget][$row["poste"]]['niveau'] = 1;
    }else if($poste_explode[0] != null && $poste_explode[1] != null && $poste_explode[2] != null && $poste_explode[3] == null){
      $DATAS[$type_budget][$row["poste"]]['niveau'] = 2;
    }else if($poste_explode[0] != null && $poste_explode[1] != null && $poste_explode[2] != null && $poste_explode[3] != null){
      $DATAS[$type_budget][$row["poste"]]['niveau'] = 3;
    }
    $DATAS[$type_budget][$row["poste"]]['description']=$row['description'];
    $DATAS[$type_budget][$row["poste"]]['budget_annuel']=$row['budget_annuel'];
    $DATAS[$type_budget][$row["poste"]]['budget_periode']=$row['budget_periode'];
    $DATAS[$type_budget][$row["poste"]]['realisation_period']=$row['realisation_period'];
    $DATAS[$type_budget][$row["poste"]]['performance_period']=$row['performance_period'];
    $DATAS[$type_budget][$row["poste"]]['performance_annuelle']=$row['performance_annuelle'];

  }
  $dbHandler->closeConnection(true);
  return $DATAS;

}

/**
 * Fonction pour recuperer les donnees pour le rapport revision historique budget
 * PARAM : type budget, exercice budget, date rapport, period
 * RETURN : array of data $DATA
 */
function getDataRevisionHistorique($type_budget, $exo_budget, $dateRapport, $period=null){
  global $dbHandler;
  global $global_multidevise;
  global $global_monnaie, $global_id_agence, $adsys;

  $dateRapport = php2pg($dateRapport);

  $DATAS = array();

  $db = $dbHandler->openConnection();

  $sql = "SELECT (SELECT DISTINCT type_budget FROM ad_budget WHERE ref_budget = h.ref_budget) AS type_budget, h.ref_budget, h.id_trimestre, COALESCE(h.date_modif,h.date_creation) AS date_revision, h.id_ligne_budget AS ligne_budget, (SELECT nom||' '||prenom FROM ad_uti WHERE id_utilis = h.id_util_revise) AS login_revise, (SELECT nom||' '||prenom FROM ad_uti WHERE id_utilis = h.id_util_valide) AS login_valide, h.anc_montant, h.nouv_montant, (h.nouv_montant-h.anc_montant) AS variation, (SELECT description FROM ad_correspondance c INNER JOIN ad_ligne_budgetaire l ON l.id_correspondance = c.id AND l.id_ligne = h.id_ligne_budget) AS description
FROM ad_revision_historique h
WHERE";
  if ($type_budget != 0){
    $sql .= " h.ref_budget = (SELECT DISTINCT ref_budget FROM ad_budget WHERE type_budget = $type_budget and exo_budget = $exo_budget) AND";
  }
  $sql .= " h.exo_budget = $exo_budget";
  if ($period != null){
    $sql .= " AND h.id_trimestre = $period";
  }
  $sql .= " AND h.etat_revision = 2 AND COALESCE(date(h.date_modif),date(h.date_creation)) <= date('$dateRapport') AND h.id_ag = $global_id_agence ORDER BY type_budget ASC, h.id_trimestre ASC, date_revision ASC, ligne_budget ASC;";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result->getMessage());
  }

  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $countData = 0;
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $countData++;
    $DATAS[$row['type_budget']][$row['id_trimestre']][$row['ligne_budget'].".".$countData] = $row;
  }

  $dbHandler->closeConnection(true);

  return $DATAS;
}
/**
 * Fonction pour recuperer les donnees pour le rapport etat d execution budgetaire
 * PARAM : type budget, exo budget, date debut trimestre et date fin
 * Return Type : array
 */
function getDataRapportBudget($type_budget, $exo_budget, $date_debut, $date_fin){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $get_ref_budget =getRefbudgetFromTypeExoBudget($exo_budget,$type_budget);
  $ref_budget = $get_ref_budget['ref_budget'];

  $sql = "select * from (
select * from get_budget($exo_budget,$type_budget, '$date_debut', '$date_fin')
UNION
select * from get_budget_complet($exo_budget,$type_budget,'$ref_budget', '$date_debut', '$date_fin')) z
order by
case when split_part(poste,'.',1) = '' then 0 else split_part(poste,'.',1)::int end ,
case when split_part(poste,'.',2) = '' then 0 else split_part(poste,'.',2)::int end ,
case when split_part(poste,'.',3) = '' then 0 else split_part(poste,'.',3)::int end ,
case when split_part(poste,'.',4) = '' then 0 else split_part(poste,'.',4)::int end ,
case when split_part(poste,'.',5) = '' then 0 else split_part(poste,'.',5)::int end ,
case when split_part(poste,'.',6) = '' then 0 else split_part(poste,'.',6)::int end ,
case when split_part(poste,'.',7) = '' then 0 else split_part(poste,'.',7)::int end ,
case when split_part(poste,'.',8) = '' then 0 else split_part(poste,'.',8)::int end ,
case when split_part(poste,'.',9) = '' then 0 else split_part(poste,'.',9)::int end ,
case when split_part(poste,'.',10) = '' then 0 else split_part(poste,'.',10)::int end;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[$type_budget][$row["poste"]]['poste']=$row['poste'];
    $poste_explode = explode('.',$row['poste']);
    if ($poste_explode[0] != null && $poste_explode[1] == null && $poste_explode[2] == null && $poste_explode[3] == null){
      $DATAS[$type_budget][$row["poste"]]['niveau'] = 0;
    }else if($poste_explode[0] != null && $poste_explode[1] != null && $poste_explode[2] == null && $poste_explode[3] == null){
      $DATAS[$type_budget][$row["poste"]]['niveau'] = 1;
    }else if($poste_explode[0] != null && $poste_explode[1] != null && $poste_explode[2] != null && $poste_explode[3] == null){
      $DATAS[$type_budget][$row["poste"]]['niveau'] = 2;
    }else if($poste_explode[0] != null && $poste_explode[1] != null && $poste_explode[2] != null && $poste_explode[3] != null){
      $DATAS[$type_budget][$row["poste"]]['niveau'] = 3;
    }
    $DATAS[$type_budget][$row["poste"]]['description']=$row['description'];
    $DATAS[$type_budget][$row["poste"]]['trim1']=$row['trim_1'];
    $DATAS[$type_budget][$row["poste"]]['trim2']=$row['trim_2'];
    $DATAS[$type_budget][$row["poste"]]['trim3']=$row['trim_3'];
    $DATAS[$type_budget][$row["poste"]]['trim4']=$row['trim_4'];
    $DATAS[$type_budget][$row["poste"]]['bud_annuel']=$row['trim_1']+$row['trim_2']+$row['trim_3']+$row['trim_4'];

  }
  $dbHandler->closeConnection(true);
  return $DATAS;

}
/**
 * Fonction pour mettre a jour ad_ligne_budgetaire et ad_revision_historique si le poste d'un dernier niveau change en
 * poste superieure (centralisateur)
 * PARAM : id correspondance
 * RETURN : none
 */
function miseAJourLigneEtRevision($id_corres){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //Recupere exercice courante
  $exo_courante = getExoEnCours();

  //Mettre a jour ligne budgetaire associe a ce poste -> etat bloque false si true
  $ad_ligne_data['etat_bloque'] = 'f';
  $ad_ligne_data['etat_ligne'] = 3;

  $where_ligne['id_correspondance'] = $id_corres;
  //$where_ligne['exo_budget'] = $exo_courante['id_exo_compta'];
  $where_ligne['id_ag'] = $global_id_agence;
  $update_data_ligne = buildUpdateQuery('ad_ligne_budgetaire',$ad_ligne_data,$where_ligne);
  $result_ligne = $db->query($update_data_ligne);
  if (DB::isError($result_ligne)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  //Mettre a jour tous les revisions associe a ce poste dont l'etat est 1 -> etat = 3
  $ad_revision_data['etat_revision'] = 3;

  $where_revision['id_ligne_budget'] = getIdLigneBudgetaire($id_corres);
  $where_revision['etat_revision'] = 1;
  $where_revision['exo_budget'] = $exo_courante['id_exo_compta'];
  $where_revision['id_ag'] = $global_id_agence;
  $update_data_revision = buildUpdateQuery('ad_revision_historique',$ad_revision_data,$where_revision);
  $result_revision = $db->query($update_data_revision);
  if (DB::isError($result_revision)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Fonction pour recuperer l'id du ligne budgetaire (ad_ligne_budgetaire)
 * PARAM : id correspondance
 * RETURN : id ligne budgetaire -> integer
 */
function getIdLigneBudgetaire($id_corres){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $id_ligne = -1;

  //Recupere exercice courante
  $exo_courante = getExoEnCours();

  $sql_id_ligne = "SELECT COALESCE(id_ligne,0) AS id_ligne FROM ad_ligne_budgetaire WHERE id_correspondance = ".$id_corres." AND id_ag = ".$global_id_agence;

  $result_id_ligne = $db->query($sql_id_ligne);

  if (DB::isError($result_id_ligne)) {
    $dbHandler->closeConnection(false);
    Signalerreur(__FILE__,__LINE__,__FUNCTION__,_("DB").": ".$result_id_ligne->getMessage());
  }

  if ($result_id_ligne->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return 0;
  }

  while ( $row = $result_id_ligne->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $id_ligne = $row['id_ligne'];
  }

  $dbHandler->closeConnection(true);
  return $id_ligne;
}

/**
 * Fonction qui compte le nombre de(s) nouvelle(s) ligne(s) budgetaire(s)
 * sil y en a pour la mise en place/validation
 * PARAM type : mise en place ou validation mise en place -> 1/2
 * Return count -> integer
 */
function countNouvelleLigneBudgetaire($type){
  global $dbHandler,$global_id_agence;

  $count_ligne = 0;

  $exo_courante = getExoEnCours();
  $BudgetExist = getTypBudgetFromTabBudget();

  if ($BudgetExist != null) {
    $db = $dbHandler->openConnection();
    if ($type == 1) { // mise en place
      $sql = "select count(c.id) as countligne from ad_correspondance c INNER JOIN ad_budget b ON b.ref_budget = c.ref_budget where c.id not in (select id_correspondance from ad_ligne_budgetaire) and c.etat_correspondance = 't' and c.dernier_niveau = 't' and b.etat_budget <>6 and c.id_ag = $global_id_agence ";
    }
    if ($type == 2) { // validation
      $sql = "select count(id_ligne) as countligne from ad_ligne_budgetaire where etat_ligne = 1 and ref_budget in (select ref_budget from ad_budget  where etat_budget >= 3  and id_ag = $global_id_agence) and id_ag = $global_id_agence ";
    }


    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    if ($result->numRows() == 0) {
      $dbHandler->closeConnection(true);
      return $count_ligne;
    }
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      $count_ligne = $row['countligne'];
    }
    $dbHandler->closeConnection(true);
  }
  return $count_ligne;

}

/**
 * Fonction pour recuperer la table des nouvelles lignes budgetaires a mettre en place
 * PARAM : type de budget->integer
 * RETURN TYPE : array of data $DATAS
 */
function getNouvelleLigneBudgetaire($type_budget,$ref_budget){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select c.id, c.etat_correspondance, c.type_budget, b.ref_budget, c.poste_principal, c.poste_niveau_1, c.poste_niveau_2, c.poste_niveau_3, c.description, c.compartiment, array_to_string(array_agg(cpte.cpte_comptable),' - ') as cpte_correspondance from ad_correspondance c inner join ad_budget b on b.type_budget = c.type_budget and b.ref_budget = c.ref_budget inner join ad_budget_cpte_comptable cpte on cpte.id_ligne = c.id where c.type_budget = $type_budget and c.etat_correspondance = 't' and c.dernier_niveau = 't' and c.id not in (select id_correspondance from ad_ligne_budgetaire where id_ag = $global_id_agence) and c.id_ag = $global_id_agence and b.ref_budget = '$ref_budget' group by c.id, c.etat_correspondance, c.type_budget, b.ref_budget, c.poste_principal, c.poste_niveau_1, c.poste_niveau_2, c.poste_niveau_3, c.description, c.compartiment order by coalesce(c.poste_principal,0),coalesce(c.poste_niveau_1,0),coalesce(c.poste_niveau_2,0),coalesce(c.poste_niveau_3,0)   ";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $arr_ligneBudgetaire = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($arr_ligneBudgetaire, $row);
  }

  $dbHandler->closeConnection(true);
  return $arr_ligneBudgetaire;
}

/**
 * Fonction pour recuperer les details du poste parent de la dernier niveau
 * PARAM : niveau principal, niveau 1, niveau 2, niveau 3
 * RETURN TYPE : array of data $DATAS *
 */
function getParentNouvelleLigneBudget($principal, $niveau1, $niveau2, $niveau3,$ref_budget,$id_exo=null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $exo_courante = getExoEnCours();

  if ($niveau3 != null && $niveau3 != ''){
    $poste_parent = $principal.".".$niveau1.".".$niveau2;
  }
  if ($niveau3 == null && $niveau2 != null && $niveau2 != ''){
    $poste_parent = $principal.".".$niveau1;
  }
  if ($niveau3 == null && $niveau2 == null && $niveau1 != null && $niveau1 != ''){
    $poste_parent = $principal;
  }

  $sql_parent = "SELECT l.* FROM ad_ligne_budgetaire l INNER JOIN ad_budget b ON l.ref_budget = b.ref_budget WHERE l.poste_budget = '$poste_parent' AND l.id_ag = $global_id_agence AND b.ref_budget = '$ref_budget'";

  $result_parent = $db->query($sql_parent);

  if (DB::isError($result_parent)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $arr_parent = array();
  while ($row = $result_parent->fetchrow(DB_FETCHMODE_ASSOC)) {
    $arr_parent = $row;
  }

  $dbHandler->closeConnection(true);
  return $arr_parent;
}

/**
 * Fonction pour mettre en place la nouvelle ligne budgetaire dans la BDD
 * PARAM : Array of data -> $DATA_NOUVELLELIGNE, $DATA_LIGNEPARENT
 * Return Type : Object -> ErrorObj
 */
function miseEnPlaceNouvelleLigne($DATA_NOUVELLELIGNE,$DATA_LIGNEPARENT){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //recupere id exercice en cour
  $id_exo = getExoEnCours();

  //Insertion nouvelle ligne budgetaire dans la table ad_ligne_budgetaire
  $nouv_ligne_budgetaire_data = array();
  $nouv_ligne_budgetaire_data['id_correspondance'] = intval($DATA_NOUVELLELIGNE['id_correspondance']);
  $nouv_ligne_budgetaire_data['ref_budget'] = $DATA_NOUVELLELIGNE['ref_budget'];
  $nouv_ligne_budgetaire_data['poste_budget'] = $DATA_NOUVELLELIGNE['poste'];
  $nouv_ligne_budgetaire_data['mnt_trim1'] = $DATA_NOUVELLELIGNE['mnt_trim1'];
  $nouv_ligne_budgetaire_data['mnt_trim2'] = $DATA_NOUVELLELIGNE['mnt_trim2'];
  $nouv_ligne_budgetaire_data['mnt_trim3'] = $DATA_NOUVELLELIGNE['mnt_trim3'];
  $nouv_ligne_budgetaire_data['mnt_trim4'] = $DATA_NOUVELLELIGNE['mnt_trim4'];
  $nouv_ligne_budgetaire_data['date_creation'] = date('r');
  $nouv_ligne_budgetaire_data['id_ag'] = intval($DATA_NOUVELLELIGNE['id_ag']);
  $nouv_ligne_budgetaire_data['etat_bloque'] = $DATA_NOUVELLELIGNE['etat_bloque'];
  $nouv_ligne_budgetaire_data['etat_ligne'] = 1;
  $insert_data_ligne_budgetaire = buildInsertQuery('ad_ligne_budgetaire',$nouv_ligne_budgetaire_data);
  $result_ad_ligne_budgetaire = $db->query($insert_data_ligne_budgetaire);
  if (DB::isError($result_ad_ligne_budgetaire)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  //Mise a jour montant trimestres pour la ligne parent dans la table ad_ligne_budgetaire
  if ($DATA_LIGNEPARENT['id'] != null){
    //data
    $ad_ligne_parent_data = array();
    $ad_ligne_parent_data['mnt_trim1'] = $DATA_LIGNEPARENT['mnt_trim1'];
    $ad_ligne_parent_data['mnt_trim2'] = $DATA_LIGNEPARENT['mnt_trim2'];
    $ad_ligne_parent_data['mnt_trim3'] = $DATA_LIGNEPARENT['mnt_trim3'];
    $ad_ligne_parent_data['mnt_trim4'] = $DATA_LIGNEPARENT['mnt_trim4'];
    $ad_ligne_parent_data['date_modif'] = date('r');
    //where condition
    $where_ad_ligne_parent["id_ligne"] = $DATA_LIGNEPARENT['id'];
    $where_ad_ligne_parent["ref_budget"] = $DATA_LIGNEPARENT['ref_budget'];
    $where_ad_ligne_parent["id_ag"] = $global_id_agence;
    $update_data_ad_ligne_budget = buildUpdateQuery('ad_ligne_budgetaire',$ad_ligne_parent_data,$where_ad_ligne_parent);
    $result_ad_ligne_budget = $db->query($update_data_ad_ligne_budget);
    if (DB::isError($result_ad_ligne_budget)) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
      $dbHandler->closeConnection(false);
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array("type_budget" => $DATA_NOUVELLELIGNE['type_budget'], "ref_budget" => $DATA_NOUVELLELIGNE['ref_budget'], "annee" => date('Y')));
}

/**
 * Fonction pour recuperer les donnees des tables correspondance, budget et ligne budgetaire pour la validation nouvelle ligne budgetaire
 * PARAM : type de budget->integer, id exo->integer, etat budget->string (ex: < 1 or > 1...etc)
 * RETURN TYPE : array of data $DATAS
 */
function getValidationLigneBudgetaire($type_budget,$id_exo,$etat_budget=null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT l.id_ligne, l.id_correspondance, l.ref_budget, l.poste_budget, l.mnt_trim1, l.mnt_trim2, l.mnt_trim3, l.mnt_trim4, l.etat_bloque, l.etat_ligne, c.description, c.compartiment, array_to_string(array_agg(cpte.cpte_comptable),'-') AS cpte_correspondance  FROM ad_budget b INNER JOIN ad_ligne_budgetaire l ON b.ref_budget = l.ref_budget INNER JOIN ad_correspondance c ON l.id_correspondance = c.id INNER JOIN ad_budget_cpte_comptable cpte ON cpte.id_ligne = c.id WHERE b.type_budget = $type_budget AND b.exo_budget = $id_exo AND l.etat_ligne = 1 AND l.id_ag = $global_id_agence";
  if ($etat_budget != null){
    $sql .= " AND b.etat_budget ".$etat_budget;
  }
  $sql .= " GROUP BY l.id_ligne, l.id_correspondance, l.ref_budget, l.poste_budget, c.poste_principal, c.poste_niveau_1, c.poste_niveau_2, c.poste_niveau_3, l.mnt_trim1, l.mnt_trim2, l.mnt_trim3, l.mnt_trim4, l.etat_bloque, l.etat_ligne,
c.description, c.compartiment ORDER BY coalesce(c.poste_principal,0),coalesce(c.poste_niveau_1,0),coalesce(c.poste_niveau_2,0),coalesce(c.poste_niveau_3,0) ";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $arr_ligne = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($arr_ligne, $row);
  }

  $dbHandler->closeConnection(true);
  return $arr_ligne;
}

/**
 * Fonction pour valider le(s) Nouvelle(s) Ligne(s) budgetaire(s) dans la BDD
 * PARAM : Array of data -> $DATA
 * Return Type : Object -> ErrorObj
 */
function validerLigneBudgetaire($DATA){
  global $dbHandler, $global_id_agence, $global_id_utilisateur;
  $db = $dbHandler->openConnection();

  //recupere id exercice en cour
  $id_exo = getExoEnCours();

  //Mise a jour dans la table ad_ligne_budgetaire
  //data
  $ad_ligne_budgetaire_data = array();
  $ad_ligne_budgetaire_data['date_modif'] = date('r');
  $ad_ligne_budgetaire_data['etat_bloque'] = $DATA['etat_bloque'];
  $ad_ligne_budgetaire_data['etat_ligne'] = 2;
  //condition
  $where_ligne_budgetaire['id_correspondance'] = intval($DATA['id_correspondance']);
  $where_ligne_budgetaire['ref_budget'] = $DATA['ref_budget'];
  $where_ligne_budgetaire['poste_budget'] = $DATA['poste'];
  $where_ligne_budgetaire['id_ag'] = intval($DATA['id_ag']);
  $update_data_ligne_budgetaire = buildUpdateQuery('ad_ligne_budgetaire',$ad_ligne_budgetaire_data,$where_ligne_budgetaire);
  $result_ad_ligne_budgetaire = $db->query($update_data_ligne_budgetaire);
  if (DB::isError($result_ad_ligne_budgetaire)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $donnees_poste = getDataPostePrincipal($DATA['id_correspondance'],$DATA['ref_budget']);
  $parent = getParentNouvelleLigneBudget($donnees_poste[0]['poste_principal'],$donnees_poste[0]['poste_niveau_1'],$donnees_poste[0]['poste_niveau_2'],$donnees_poste[0]['poste_niveau_3'],$DATA['ref_budget']);
  // Si nouvelle ligne budgetaire ne possede pas de parent c'est a dire qu'il ne decoule pas d'un poste de dernier niveau
  if (sizeof($parent)>0) {
    miseAJourLigneEtRevision($parent['id_correspondance']);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array("type_budget" => $DATA['type_budget'], "ref_budget" => $DATA['ref_budget'], "annee" => date('Y')));
}

/**
 * Fonction pour verifier s'il y a des comptes bloques pour la ligne budgetaire
 * PARAM : id correspondance
 * RETURN TYPE : boolean
 */
function verifCptesBloq($id_corres){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $hasCptesBloq = false;

  $exo_courante = getExoEnCours();

  $sql = "SELECT COUNT(id_bloc) AS count FROM ad_ligne_budgetaire l INNER JOIN ad_budget_cpt_bloquer cpt_bloq ON l.id_ligne = cpt_bloq.ligne_budgetaire WHERE l.id_correspondance = $id_corres AND cpt_bloq.cpte_bloquer = 't' AND l.id_ag = $global_id_agence";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    if ($row['count']>0){
      $hasCptesBloq = true;
    }
  }

  $dbHandler->closeConnection(true);
  return $hasCptesBloq;
}

function CreateTempTableEtat(){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "CREATE TABLE temp_table_etat_budget(
	 poste text,
	 description text,
	 budget_annuel numeric(30,6),
	 budget_periode numeric(30,6),
	 realisation_period numeric(30,6),
	 performance_period double precision,
	 performance_annuelle double precision
)";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }
  $dbHandler->closeConnection(true);
  return true;
}

function DropTempTableEtat(){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "DROP TABLE temp_table_etat_budget";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }
  $dbHandler->closeConnection(true);
  return true;
}

/**
 * Fonction qui recupere les donnees de la table ad_cpt_comptable pour une compte precis
 * PARAM : id_bloc
 * RETURN TYPE : row
 */
function getDataCpteComptable($num_cpte_compta = null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql= "Select * from ad_cpt_comptable where id_ag = numagc()";

  if ($num_cpte_compta != null){
    $sql .= " AND num_cpte_comptable = '$num_cpte_compta'";
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getTypeBudgetFromRefBudget($ref_budget){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select type_budget from ad_budget where ref_budget ='$ref_budget'";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function getRefbudgetFromTypeExoBudget($exo,$type_budget){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select ref_budget from ad_budget where exo_budget =$exo and type_budget = $type_budget";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);

  return $DATAS;

}

function checkIfBudgetExist($id_exo,$type_budget){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT count(*) as count_nbre FROM ad_budget WHERE exo_budget = $id_exo and type_budget = $type_budget ";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return FALSE;
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  if ($row['count_nbre'] >0){
    $dbHandler->closeConnection(true);
    return true;
  }else{
    $dbHandler->closeConnection(true);
    return FALSE;
  }

}

function getRefCorrespondanceExistant($ref_budget=null){
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT ref_budget FROM ad_budget ";
  if ($ref_budget !=null){
    $sql .= " WHERE ref_budget = $ref_budget";
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return FALSE;
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $CC[$row["ref_budget"]] = $row["ref_budget"];
  }
  $dbHandler->closeConnection(true);

  return $CC;
}

function insertBudgetAttente($exo_budget,$type_budget,$ref_budget=null)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

//recupere id exercice en cour et creation reference (format : id_agence - id_exo - date du jour - type_budget) pour le budget
  if ($ref_budget != null){
    $ref_budget = $ref_budget;
  }
  else{
    $ref_budget = sprintf("%03d", $global_id_agence) . "-" . $exo_budget . "-" . date('d') . date('m') . date('Y') . "-" . $type_budget;
  }

//Insertion dans la table ad_budget

  $ad_budget_data = array();
  $ad_budget_data['exo_budget'] = $exo_budget;
  $ad_budget_data['ref_budget'] = $ref_budget;
  $ad_budget_data['type_budget'] = $type_budget;
  $ad_budget_data['etat_budget'] = 6;
  $ad_budget_data['date_creation'] = date('r');
  $ad_budget_data['id_ag'] = $global_id_agence;
  $insert_data_ad_budget = buildInsertQuery('ad_budget', $ad_budget_data);
  $result_ad_budget = $db->query($insert_data_ad_budget);
  if (DB::isError($result_ad_budget)) {
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
    $dbHandler->closeConnection(false);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR,array("ref_budget" => $ref_budget));
}

function getAllExoOuvertWithBudgetAvailable($etat_budget = null){
  global $dbHandler,$global_id_agence,$adsys;
  $db = $dbHandler->openConnection();

  $sql = "SELECT b.id_budget AS id_budget_exo, b.ref_budget AS ref_budget_exo, b.type_budget AS type_budget_exo, b.exo_budget AS id_exo_budget FROM ad_budget b INNER JOIN ad_exercices_compta c ON b.exo_budget = c.id_exo_compta WHERE c.etat_exo = 1";
  if ($etat_budget != null) {
    $sql .= " AND b.etat_budget  $etat_budget";
  }
  $sql .=" ORDER BY b.exo_budget, b.type_budget";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return $adsys["adsys_type_budget"];//return NULL;
  }
  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $CC[$row["id_exo_budget"]][$row["type_budget_exo"]] = $row;
  }
  $budget_available = array();
  //$adsys_type_budget = $adsys["adsys_type_budget"];
  foreach($CC as $exo => $value_exo){
    $adsys_type_budget = $adsys["adsys_type_budget"];
    foreach($value_exo as $budget => $value_budget){
      if (($budget == 1) && $value_budget['type_budget_exo'] == '1') {
        unset($adsys_type_budget[1]);
      }
      if (($budget == 1 || $budget == 2) && $value_budget['type_budget_exo'] == '2') {
        unset($adsys_type_budget[2]);
      }
      if (($budget == 1 || $budget == 2 || $budget == 3) && $value_budget['type_budget_exo'] == '3') {
        unset($adsys_type_budget[3]);
      }
    }
    $budget_available[$exo] = $adsys_type_budget;
  }

  $dbHandler->closeConnection(true);
  return $budget_available;

}
function duplicationTableCorrespondanceExistant($ref_budget_old, $type_budget_old, $type_budget_new,$ref_budget_new,$exo_budget)
{
  global $dbHandler, $global_id_agence, $adsys;
  $db = $dbHandler->openConnection();
  $sql = "SELECT gettemplatecorrespondance('$ref_budget_old',$type_budget_old,$type_budget_new,'$ref_budget_new',$exo_budget)";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function getDataBudget($id_exo= null,$etat_budget = null,$type_budget = null){
  global $dbHandler,$global_id_agence,$adsys;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_budget WHERE id_ag = $global_id_agence ";
  if ($id_exo !=null){
    $sql .= " AND exo_budget = $id_exo";
  }
  if ($etat_budget !=null){
    $sql .= " AND etat_budget $etat_budget";
  }
  if ($type_budget !=null){
    $sql .= " AND type_budget = $type_budget";
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  if ($result->numRows() == 0)
  {
    $dbHandler->closeConnection(true);
    return FALSE;
  }

  $CC = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $CC[$row['type_budget']] = $adsys['adsys_type_budget'][$row['type_budget']];
  }

  $dbHandler->closeConnection(true);
  return $CC;
}
?>
