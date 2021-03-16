<?php
require_once 'lib/misc/xml_lib.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'lib/misc/divers.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/devise.php';


// recu sur demande de derogation engrais chimiques
function print_recu_demande_autorisation_commande($id_beneficiaire, $nom_beneficiaire,$num_commande,$qtite_dep_engrais=0,$qtite_dep_amendement=0, $date_demande,$utilisateur) {
  global $global_id_agence, $global_id_profil;
  $format_A5 = false;

  $document = create_xml_doc("recu", "recu_demande_autorisation_commande.dtd");

  //Element root
  $root = $document->root();
  $root->set_attribute("type", 22);

  //En-tête généraliste
  gen_header($root, 'REC-PND');

  //Corps
  $body = $root->new_child("body", "");
  $body->new_child("num_beneficiaire", $id_beneficiaire);
  $body->new_child("nom_beneficiaire",$nom_beneficiaire);
  $body->new_child("num_commande",$num_commande);
  $body->new_child("qtite_dep_engrais",  $qtite_dep_engrais);
  $body->new_child("qtite_dep_amendement", $qtite_dep_amendement);
  $body->new_child("date_demande", $date_demande);
  $body->new_child("utilisateur_demande", $utilisateur);

  $xml = $document->dump_mem(true);

  //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
  if($format_A5){
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_demande_autorisation_commande.xslt');
  } else {
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_demande_autorisation_commande.xslt');
  }

  // Affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html(NULL, $fichier_pdf);

}

// recu sur demande de derogation engrais chimiques
function  print_recu_commande($id_beneficiaire, $nom_beneficiaire,$num_commande,$qtite_dep_engrais=0,$qtite_dep_amendement=0,$montant_cmd=0, $date_demande,$utilisateur) {
  global $global_id_agence, $global_id_profil;
  $format_A5 = false;

  $document = create_xml_doc("recu", "recu_ec_commande.dtd");

  //Element root
  $root = $document->root();
  $root->set_attribute("type", 22);

  //En-tête généraliste
  gen_header($root, 'REC-PNA');

  //Corps
  $body = $root->new_child("body", "");
  $body->new_child("num_beneficiaire", $id_beneficiaire);
  $body->new_child("nom_beneficiaire",$nom_beneficiaire);
  $body->new_child("num_commande",$num_commande);
  $body->new_child("qtite_dep_engrais",  $qtite_dep_engrais);
  $body->new_child("qtite_dep_amendement", $qtite_dep_amendement);
  $body->new_child("mnt_commande", $montant_cmd);
  $body->new_child("date_demande", $date_demande);
  $body->new_child("utilisateur_demande", $utilisateur);

  $xml = $document->dump_mem(true);

  //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
  if($format_A5){
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_ec_commande.xslt');
  } else {
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_ec_commande.xslt');
  }

  // Affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html(NULL, $fichier_pdf);

}


function print_recu_paiement_commande($id_beneficiaire, $nom_beneficiaire,$num_commande,$montant_payer,$montant_restant, $date_demande,$utilisateur) {
  global $global_id_agence, $global_id_profil;
  $format_A5 = false;

  $document = create_xml_doc("recu", "recu_ec_paiement_commande.dtd");

  //Element root
  $root = $document->root();
  $root->set_attribute("type", 22);

  //En-tête généraliste
  gen_header($root, 'REC-PNP');

  //Corps
  $body = $root->new_child("body", "");
  $body->new_child("num_beneficiaire", $id_beneficiaire);
  $body->new_child("nom_beneficiaire",$nom_beneficiaire);
  $body->new_child("num_commande",$num_commande);
  $body->new_child("montant_payer",  $montant_payer);
  $body->new_child("montant_restant", $montant_restant);
  $body->new_child("date_demande", $date_demande);
  $body->new_child("utilisateur_demande", $utilisateur);
  $xml = $document->dump_mem(true);

  //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
  if($format_A5){
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_ec_paiement_commande.xslt');
  } else {
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'recu_ec_paiement_commande.xslt');
  }

  // Affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html(NULL, $fichier_pdf);

}

?>