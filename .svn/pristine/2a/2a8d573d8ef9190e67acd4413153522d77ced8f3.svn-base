<?Php

/**
 * prodEpargne
 * @package Epargne
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/html/HtmlHeader.php';
require_once 'lib/dbProcedures/credit.php';

function affiche_HTML_produit($id) {
  /* Renvoie le code html pour le produit sélectionné */

  global $global_monnaie_courante;

  $PROD = getProdEpargne($id);
  setMonnaieCourante($PROD["devise"]);

  $Myform = new HTML_GEN2(_("Détail du produit sélectionné"));
  $exclude_produits=array('id','sens');
  $Myform->addTable("adsys_produit_epargne", OPER_EXCLUDE,$exclude_produits);

  //LABEL pour adsys_produit_epargne
  $fields = getFieldsLabel("adsys_produit_epargne");
  $info = get_tablefield_info("adsys_produit_epargne",$id);
  $fieldslabel = array_diff($fields, $exclude_produits);
  foreach($fieldslabel as $value) {
    $Myform->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  };
  $MyData=new FILL_HTML_GEN2();
  $MyData->addFillClause('TableEp','adsys_produit_epargne');
  $MyData->addCondition('TableEp','id',$id);
  $MyData->addManyFillFields('TableEp',OPER_EXCLUDE,$exclude_produits);
  $MyData->fill($Myform);
  // les boutons ajoutés
  $Myform->addFormButton(1,1,"ok",_("Ok"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onClick"=>"window.close();"));

  $order = array("libel","classe_comptable","devise","terme", "ep_source_date_fin", "tx_interet", "tx_interet_max", "freq_calcul_int","mode_calcul_int",  "mnt_min", "mnt_max", "frais_tenue_cpt", "frequence_tenue_cpt", "frais_retrait_cpt","frais_depot_cpt", "frais_ouverture_cpt", "frais_fermeture_cpt", "frais_transfert","penalite_const", "penalite_prop", "dat_prolongeable", "certif");
  $Myform->setOrder(NULL,$order);

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

if (isset($id))
  affiche_HTML_produit($id);


?>