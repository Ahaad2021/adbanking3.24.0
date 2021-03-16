<?php

/**
 * Description de la classe Globalisation
 *
 * @author Roshan & Ahaad
 */

require_once '/usr/share/adbanking/web/ad_ma/app/models/BaseModel.php';

class Globalisation extends BaseModel {

  public function __construct(&$dbc, $id_agence=NULL) {
    parent::__construct($dbc, $id_agence);
  }

  public function __destruct() {
    parent::__destruct();
  }

  public function test(){

    $sql = "SELECT * FROM ad_agc where id_ag=".$this->getIdAgence();
    $result = $this->getDbConn()->prepareFetchAll($sql);

    if($result===FALSE || count($result)<0) {
      $this->getDbConn()->rollBack(); // Roll back
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $exos = array();
    foreach ($result as $row) {
      $id_agence = $row['code_institution'];
    }
    return $id_agence;
  }


  public function getPeriode($date_total){

    $sql = "select (case when date_debut_solde > date('$date_total') then 1 else 2 end) as period, id_saison,s.id_annee, (case when s.date_debut_solde > date('$date_total') then s.date_debut else date_debut_solde end) as date_debut, (case when date_debut_solde > date('$date_total') then date_debut_solde else date_fin_solde end) as date_fin  from ec_saison_culturale s INNER JOIN ec_annee_agricole a on a.id_annee = s.id_annee  where a.etat= 1 AND  s.id_ag=".$this->getIdAgence();
    $result = $this->getDbConn()->prepareFetchAll($sql);

    if($result===FALSE || count($result)<0) {
      $this->getDbConn()->rollBack(); // Roll back
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $exos = array();
    foreach ($result as $row) {
      $exos['periode'] = $row['period'];
      $exos['id_saison'] = $row['id_saison'];
      $exos['id_annee'] = $row['id_annee'];
      $exos['date_debut'] = $row['date_debut'];
      $exos['date_fin'] = $row['date_fin'];

    }
    return $exos;
  }

  public function getSituationPaiement($date_debut,$date_fin,$id_annee,$id_saison,$periode){
    $sql = "SELECT * FROM getdatarapportproduitglobal('$date_debut','$date_fin',$id_annee,$id_saison,$periode)";
    $result = $this->getDbConn()->prepareFetchAll($sql);
    if($result===FALSE || count($result)<0) {
      $this->getDbConn()->rollBack(); // Roll back
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $situation = array();
    $i =1;
    foreach ($result as $row) {
      $situation[$i]['nom_province'] = $row['nom_province'];
      $situation[$i]['nom_commune'] = $row['nom_commune'];
      $situation[$i]['libel_ag'] = $row['libel_ag'];
      $situation[$i]['id_produit'] = $row['id_produit'];
      $situation[$i]['libel_prod'] = $row['libel_prod'];
      $situation[$i]['qtite'] = $row['qtite'];
      $situation[$i]['qtite_paye'] = $row['qtite_paye'];
      $situation[$i]['mnt_avance'] = $row['mnt_avance'];
      $situation[$i]['mnt_solde'] = $row['mnt_solde'];
      $situation[$i]['id_annee'] = $row['id_annee'];
      $situation[$i]['id_saison'] = $row['id_saison'];
      $situation[$i]['period'] = $row['period'];
      $situation[$i]['id_ag'] = $this->getIdAgence();
      $i ++;
    }
    return $situation;
  }

  public function getRepartitionZone($id_annee,$id_saison,$date_debut,$date_fin){
    $sql = "select * from getdatarapportqtitezone($id_annee,$id_saison,'$date_debut','$date_fin') order by nom_province, nom_commune, nom_coopec, nom_zone, id_prod";
    $result = $this->getDbConn()->prepareFetchAll($sql);
    if($result===FALSE || count($result)<0) {
      $this->getDbConn()->rollBack(); // Roll back
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $repartition = array();
    $i =1;
    foreach ($result as $row) {
      $repartition[$i]['id_province'] = $row['id_province'];
      $repartition[$i]['nom_province'] = $row['nom_province'];
      $repartition[$i]['id_commune'] = $row['id_commune'];
      $repartition[$i]['nom_commune'] = $row['nom_commune'];
      $repartition[$i]['nom_coopec'] = $row['nom_coopec'];
      $repartition[$i]['id_zone'] = $row['id_zone'];
      $repartition[$i]['nom_zone'] = $row['nom_zone'];
      $repartition[$i]['id_prod'] = $row['id_prod'];
      $repartition[$i]['qtite'] = $row['qtite'];
      $repartition[$i]['montant'] = $row['montant'];
      $repartition[$i]['id_ag'] = $this->getIdAgence();
      $i ++;
    }
    return $repartition;
  }

  public function deleteView(){
    $sql = "DROP VIEW IF EXISTS commande";
    $result = $this->getDbConn()->execute($sql);
    if($result===FALSE || count($result)<0) {
      $this->getDbConn()->rollBack(); // Roll back
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    return true;
  }

  public function createViewPaye($annee=null, $saison=null, $periode=null, $date_debut=null, $date_fin=null){

    $groupBy = "";
    if ($periode ==1){
      $sql= "CREATE VIEW commande AS ( SELECT DISTINCT data.id_benef,data.id_saison, data.id_produit, SUM(coalesce(data.qty,0)) AS qty, SUM(coalesce(data.qty_paye,0)) AS qty_paye, data.prix_total, SUM(coalesce(data.montant_avance,0)) AS montant_avance, data.montant_solde FROM (SELECT a.id_benef, a.id_saison, b.id_produit";
    }else{
      $sql= "CREATE VIEW commande AS ( SELECT DISTINCT data.id_benef,data.id_saison, data.id_remb , data.id_produit, SUM(coalesce(data.qty,0)) AS qty, SUM(coalesce(data.qty_paye,0)) AS qty_paye, data.prix_total, SUM(coalesce(data.montant_avance,0))  AS montant_avance, SUM(coalesce(data.montant_solde,0)) AS montant_solde FROM (SELECT a.id_benef, a.id_saison, b.id_produit";
    }


    if ($saison != null) {
      if ($periode ==1){
        $sql .= ", CASE WHEN a.id_saison = $saison THEN coalesce(b.quantite,0) ELSE 0 END AS qty,0 AS qty_paye";
      }else {
        $sql .= " , p.id_remb, CASE WHEN a.id_saison <= $saison THEN coalesce(b.quantite,0) ELSE 0 END AS qty,CASE WHEN a.id_saison <= $saison THEN coalesce(p.qtite_paye,0) ELSE 0 END AS qty_paye";
      }
    }
    else {
      $sql .= ", SUM(coalesce(b.quantite,0)) AS qty";
    }
    $sql.=", coalesce(b.prix_total,0) AS prix_total";
    if ($periode != null && $periode == 1){
      $sql.= " ,coalesce(b.montant_depose,0) AS montant_avance, 0 AS montant_solde, b.date_creation"; //(SUM(coalesce(b.montant_depose,0)) OVER (PARTITION BY a.id_benef ORDER BY b.id_produit)) AS montant_avance
      $groupBy = "a.id_saison,  b.quantite,b.montant_depose,b.prix_total";
    }
    if ($periode != null && $periode == 2){
      $sql.= " ,CASE WHEN a.id_saison <= $saison THEN coalesce(b.montant_depose,0) ELSE 0 END AS montant_avance, coalesce(p.montant_paye,0)  AS montant_solde, b.date_creation";
      $groupBy = "a.id_saison,p.id_remb, b.montant_depose, b.quantite,p.qtite_paye,b.montant_depose, p.montant_paye,b.prix_total";
    }
    if ($saison != null && $saison > 0 && $periode == null){
      $sql.= " ,CASE WHEN a.id_saison <= $saison THEN coalesce(b.montant_depose,0) ELSE 0 END AS montant_avance, coalesce(p.montant_paye,0) AS montant_solde, b.date_creation";
      $groupBy = "a.id_saison,p.id_remb,  b.montant_depose,  b.quantite,p.qtite_paye,b.montant_depose, p.montant_paye,b.prix_total";
    }
    if ($saison == null && $periode == null){
      $sql.= " ,coalesce(b.montant_depose,0) AS montant_avance, coalesce(p.montant_paye,0)  AS montant_solde, b.date_creation";
      $groupBy = "a.id_saison,p.id_remb,  b.montant_depose,  b.quantite,p.qtite_paye,b.montant_depose, p.montant_paye,b.prix_total";
    }
    $sql.=" FROM ec_commande a
INNER JOIN ec_commande_detail b ON a.id_commande = b.id_commande "; //b.date_creation, b.date_modif,

    if ($saison != null && $saison > 0 && $date_debut != null && $date_fin != null){
      $where = " WHERE a.etat_commande in (1,2,3,4,8)";
      if ($periode != null && $periode == 1){
        $sql.= $where." AND a.id_saison = $saison AND date(b.date_creation) >= date('$date_debut') AND date(b.date_creation) <= date('$date_fin')";
      }
      else if ($periode != null && $periode == 2){
        $sql.= " LEFT JOIN ec_paiement_commande p ON p.id_detail_commande = b.id_detail AND date(p.date_creation) >= date('$date_debut') AND date(p.date_creation) <= date('$date_fin') and p.etat_paye = 2 ".  $where." AND a.id_saison <= $saison";
      }
      else {
        $sql.= " LEFT JOIN ec_paiement_commande p ON p.id_detail_commande = b.id_detail AND date(p.date_creation) >= date('$date_debut') AND date(p.date_creation) <= date('$date_fin') and p.etat_paye = 2 ".  $where."AND a.id_saison <= $saison"; //(b.date_creation >= '$date_debut' AND b.date_creation <= '$date_fin') AND
      }
    }
    else {
      $sql.= " LEFT JOIN ec_paiement_commande p ON p.id_detail_commande = b.id_detail";
      $groupBy = "b.montant_depose,p.id_remb,   b.quantite,p.qtite_paye,b.montant_depose, p.montant_paye,b.prix_total";
    }
    $sql.= " ORDER BY a.id_benef ASC, b.id_produit ASC)  data"; //b.date_creation, b.date_modif, //retire   GROUP BY a.id_benef, b.id_produit, $groupBy
    if ($periode != null && $periode == 2){
      $positionSaison=checkPositionSaison($annee,$saison);
      if ($positionSaison==1){
        $sql.=" WHERE data.montant_avance > 0 AND data.montant_solde > 0";
      }
      else{
        $sql.=" WHERE data.montant_solde > 0";
      }
    }
    if ($saison != null && $saison > 0 && $periode == null){
      $positionSaison=checkPositionSaison($annee,$saison);
      if ($positionSaison==1){
        $sql.=" WHERE data.montant_avance > 0";
      }
      else{
        $sql.=" WHERE (data.montant_avance+data.montant_solde) > 0";
      }
    }

    if ($periode == 1 ){
      $sql .= " GROUP BY data.id_benef,data.id_saison, data.id_produit, data.prix_total, data.montant_solde";
    }
    else{
      $sql .= " GROUP BY data.id_benef, data.id_saison, data.id_remb, data.id_produit, data.qty, data.qty_paye, data.prix_total, data.montant_solde";
    }
    $sql.=")";
    $result = $this->getDbConn()->execute($sql);
    if($result===FALSE || count($result)<0) {
      $this->getDbConn()->rollBack(); // Roll back
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    return true;
  }


  public function getBenefPaye ($annee, $saison, $periode=0, $date_debut, $date_fin){

    $sql = "select * from getDataRapport($annee,$saison,'$date_debut','$date_fin') order by id_benef,id_prod";
    $result = $this->getDbConn()->prepareFetchAll($sql);
    if($result===FALSE || count($result)<0) {
      $this->getDbConn()->rollBack(); // Roll back
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $benefPaye = array();
    $i =1;
    foreach ($result as $row) {
      $benefPaye[$i]['nom_province'] = $row['nom_province'];
      $benefPaye[$i]['nom_commune'] = $row['nom_commune'];
      $benefPaye[$i]['nom_zone'] = $row['nom_zone'];
      $benefPaye[$i]['nom_colline'] = $row['nom_colline'];
      $benefPaye[$i]['nom_coopec'] = $row['nom_coopec'];
      $benefPaye[$i]['id_benef'] = $row['id_benef'];
      $benefPaye[$i]['nom_prenom'] = $row['nom_prenom'];
      $benefPaye[$i]['id_card'] = $row['id_card'];
      $benefPaye[$i]['id_prod'] = $row['id_prod'];
      $benefPaye[$i]['libel'] = $row['libel'];
      $benefPaye[$i]['quantite'] = $row['quantite'];
      $benefPaye[$i]['qtite_paye'] = $row['qtite_paye'];
      $benefPaye[$i]['montant_paye'] = $row['montant_paye'];
      $benefPaye[$i]['montant_avance'] = $row['montant_avance'];
      $benefPaye[$i]['montant_solde'] = $row['montant_solde'];
      $benefPaye[$i]['id_ag'] = $this->getIdAgence();
      $i ++;
    }
    return $benefPaye;
  }
  public function getDate($id_saison){

    $sql = "SELECT * from ec_saison_culturale WHERE  id_ag=".$this->getIdAgence() ;
    $result = $this->getDbConn()->prepareFetchAll($sql);

    if($result===FALSE || count($result)<0) {
      $this->getDbConn()->rollBack(); // Roll back
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $exos = array();
    foreach ($result as $row) {
      $exos['date_debut'] = $row['date_debut'];
      $exos['date_fin'] = $row['date_fin_solde'];

    }
    return $exos;
  }


}