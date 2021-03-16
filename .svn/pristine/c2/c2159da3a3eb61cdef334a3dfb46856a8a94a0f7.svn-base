<?php
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
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();

  $dbHandler->closeConnection(true);
  return ($row[0] > 0);
}
?>