<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2: */
/**
 * Génère le code XML pour les recus de change
 * @package Rapports
 */

require_once 'modules/rapports/xslt.php';

/**
 * Fonction de création du document XML pour le reçu de change
 * @author Mamadou Mbaye
 * @param int $id_his Numéro de transaction
 * @param  array $DATA
 * @return code xml
**/
function xml_change_taux($id_his, $DATA) {
  global $global_monnaie;
  
  $document = create_xml_doc("changetaux", "change_taux.dtd");
  //Element root
  $root = $document->root();

  $set=0;

  if ($DATA["dest_reste"]=="VIDE")
    $set=0;
  else
    $set=$DATA["affiche_reste"];
  //En-tête généraliste

  $ref = gen_header($root,'REC-CHG');
  $body = $root->new_child("body", "");
  $body->set_attribute("affiche_reste",$set);

  $body->new_child("id_his", sprintf("%08d", $id_his));

  setMonnaieCourante($DATA["achat"]["devise"]);
  $body->new_child("mnt_achat",afficheMontant($DATA["achat"]["mnt"]));
  $body->new_child("devise_achat", $DATA["achat"]["devise"]);
  $body->new_child("source_achat", $DATA["achat"]["source_dest"]);

  setMonnaieCourante($DATA["vente"]["devise"]);
  $body->new_child("mnt_vente",afficheMontant($DATA["vente"]["mnt"]));
  $body->new_child("devise_vente", $DATA["vente"]["devise"]);
  $body->new_child("dest_vente", $DATA["vente"]["source_dest"]);
  $body->new_child("dest_reste",$DATA["dest_reste"]);

  setMonnaieCourante($global_monnaie);
  $body->new_child("devise_ref",$global_monnaie);
  $body->new_child("reste", $DATA["reste"]);
  $body->new_child("dest_reste", $DATA["dest_reste"]);
  $body->new_child("taux", $DATA["taux"]);
  
  setMonnaieCourante($DATA["achat"]["devise"]);
  $body->new_child("commission",afficheMontant($DATA["commission"]));
  
  $hasBilletageChange = $DATA["hasBilletageChange"];

  // Billetage
  if($hasBilletageChange) {

      $body->new_child("hasBilletage", true);
      
      $listTypesBilletArr = $DATA["listTypesBilletArr"];
      $valeurBilletArr = $DATA["valeurBilletArr"];
      $total_billetArr = $DATA["total_billetArr"];
      $global_langue_rapport = $DATA["global_langue_rapport"];
      
      for ($x = 0; $x < count($valeurBilletArr); $x ++) {
          if ($valeurBilletArr[$x] != 'XXXX') {
              $body->new_child("libel_billet_" . $x, afficheMontant($listTypesBilletArr[$x]['libel']));
              $body->new_child("valeur_billet_" . $x, $valeurBilletArr[$x]);
              $body->new_child("total_billet_" . $x, afficheMontant($total_billetArr[$x]));
          }
      }
  }
  
  $xml =  $document->dump_mem(true);  
  
  $RET = array("xml" => $xml, "ref" => $ref);
  return $RET;
}

/**
 * Fonction imprimant le recçu aprés une opération de change
 * @author Mamadou Mbaye
 * @param  int $id_his     Numéro de transaction
 * @param  $mnt_achat      Le montant de la devise achetée
 * @param  $devise_achat   La devise achetée
 * @param  $source_achat   Le compte où le montant en devise achetée sera prélevé
 * @param  $mnt_vente      Le montant de la devise vendue
 * @param  $devise_vente   La devise vendue
 * @param  $dest_vente    Le compte où le montant en devise vendue sera déposé
 * @param  $comm_nette     Le montant de la commission nette
 * @param  $taux           Le taux de change utilisé
 * @param  $reste          Le montant du reste
 * @param  $dest_reste     Le compte de destination du reste du change
 * @param  $affiche_reste  Champ indiquant si le reste doit etre affiché ou non
 */
function printRecuChange($id_his, $mnt_achat,$devise_achat,$source_achat,$mnt_vente,$devise_vente,$comm_nette,$taux,$reste,$dest_vente,$dest_reste=NULL,$affiche_reste=NULL, $listTypesBilletArr = NULL, $valeurBilletArr = NULL, $global_langue_rapport = NULL, $total_billetArr = NULL, $hasBilletageChange = false, $type_rapport = null)
{
  $DATA_RECU["achat"]["mnt"]=$mnt_achat;
  $DATA_RECU["achat"]["devise"]= $devise_achat;
  $DATA_RECU["achat"]["source_dest"]=$source_achat;
  $DATA_RECU["vente"]["mnt"]=$mnt_vente;
  $DATA_RECU["vente"]["devise"]=$devise_vente;
  $DATA_RECU["vente"]["source_dest"]=$dest_vente;
  $DATA_RECU["reste"]=$reste;

  //Billetage:
  if($hasBilletageChange) {
      $DATA_RECU["listTypesBilletArr"]=$listTypesBilletArr;
      $DATA_RECU["valeurBilletArr"]=$valeurBilletArr;
      $DATA_RECU["total_billetArr"]=$total_billetArr;
      $DATA_RECU["global_langue_rapport"]=$global_langue_rapport;
      $DATA_RECU["hasBilletageChange"]=$hasBilletageChange;
  }
  
  if ($dest_reste == "1")
    $nom_dest_reste=_("Au guichet");
  else if ($dest_reste == "2")
    $nom_dest_reste=_("Sur le compte de base");
  else if ($dest_reste == "3")
    $nom_dest_reste=_("VIDE");

  $DATA_RECU["dest_reste"]=$nom_dest_reste;
  $DATA_RECU["taux"]=$taux;
  $DATA_RECU["commission"]=$comm_nette;
  $DATA_RECU["affiche_reste"]=$affiche_reste;

  //debug($DATA_RECU,"DATA");
  $RET = xml_change_taux($id_his, $DATA_RECU);
  $xml = $RET["xml"];
  $ref = $RET["ref"];

  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'change_taux.xslt', true, "recu_".$type_rapport."_".rand());

  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
  get_show_pdf_html("Gen-10", $fichier_pdf);

  $myErr = confirmeGenerationRecu($id_his, $ref);
  if ($myErr->errCode != NO_ERR)
    signalErreur(__FILE__,__LINE__,__FUNCTION__);

}

/**
 * Fonction de création du doccument XML pour la position de change
 * @author Mamadou Mbaye
 * @param  array $DATA
 * @return code xml
**/
function xml_position_change($DATA) {
  reset($DATA);

  // Création racine
  $document = create_xml_doc("position_de_change", "position_de_change.dtd");
  //simulation_intermediaire
  //Element root
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'DEV-POS');

  while (list($key, $infos) = each($DATA)) {
    $devise = $root->new_child("devise","");
    $devise->new_child("code",$key);
    foreach($infos as $cle=>$value) {
      if (( $infos[$cle] =='')&&( $cle !='libel'))
        $infos[$cle]=0;
      $devise->new_child($cle, $infos[$cle]);
    }
  }
  return $document->dump_mem(true);
}
?>