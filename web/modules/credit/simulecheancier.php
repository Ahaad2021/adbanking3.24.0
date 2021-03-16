<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [135] Simulation échéancier
 * Cette opération comprends les écrans :
 * - Sdo-1 Sra-1 : paramètres de l'échéancier
 * - Sdo-2 Sra-2 : échéancier théorique
 * - Sdo-3 Sra-3 : impression échéancier
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/misc/divers.php';
require_once 'modules/rapports/xml_echeancier.php';
require_once 'modules/rapports/xslt.php';

if ($global_nom_ecran == "Sdo-1" || $global_nom_ecran == "Sdo-2" || $global_nom_ecran == 'Sdo-3')
  $retour_ecran = "Gen-11";
else
  $retour_ecran = "Gen-13";

/*{{{ Sdo-1 Sra-1 : Paramètres de l'échéancier */
if (($global_nom_ecran == "Sdo-1") || ($global_nom_ecran == "Sra-1")) {

  // On vide le tableau de sauvegarde des données sauf si on vient d'un écran de la même fonction
  if (!(strstr($global_nom_ecran_prec,"Sdo") || strstr($global_nom_ecran_prec,"Sra") ||strstr($global_nom_ecran_prec,"Sdo-4") ))
    unset($SESSION_VARS['def']);
  unset($SESSION_VARS['liste_membres']);
  unset($SESSION_VARS['produit']);
  unset($SESSION_VARS['infos_doss']);
  setMonnaieCourante(NULL);
  if ($global_nom_ecran == "Sdo-1") {
    // On récupère les infos du client (ou du GS) et des produits qui lui sont octroyables
    $SESSION_VARS['infos_clients'][$global_id_client] = getClientDatas($global_id_client);
    // Récupération d'infos necessaire au crédit
    if ($SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) { // si Groupe solidaire (GS)
      $condition = "WHERE mode_calc_int!=5 AND (gs_cat=1 OR gs_cat=2)"; // Si c'est un GS, récupérer que les produits de crédit destinés aux GS
      // Récupération des membres du groupe
      $result = getListeMembresGrpSol($global_id_client);
      if (is_array($result->param))
        foreach($result->param as $key=>$id_cli) {
        $nom_client = getClientName($id_cli);
        $SESSION_VARS['liste_membres'][$id_cli] = $nom_client;
      }
    } else { // Personne physique, Personne morale ou  Groupe Informel
      $condition = "WHERE mode_calc_int!=5 AND (gs_cat IS NULL OR (gs_cat!=1 AND gs_cat!=2))";// Ne pas récupérer les produits destinés aux GS
      $nom_client = getClientName($global_id_client);
      $SESSION_VARS['liste_membres'][$global_id_client] = $globalid_client." ".$nom_client;
    }
    // Récupération des infos sur les produits de crédit octroyables au client

  } else {

    $condition = " WHERE mode_calc_int!=5";
    $CliParam=getPremierClient();
    $SESSION_VARS['liste_membres'][$CliParam->param[0]['id_client'] ]= " ";
  }
  $Prod  = getProdInfo($condition, null, true);
  // $Prod = getProdInfo("");  //Retourne les informations sur le produit
  $Myform = new HTML_GEN2(_("Choix du produit"));

  // Les champs ajoutés
  $Myform->addField("id", _("Type de produit de crédit"), TYPC_LSB);
  // Ajout de liens
  $Myform->addLink("id", "produit",_("Détail produit"), "#");
  $Myform->setLinkProperties("produit",LINKP_JS_EVENT,array("onClick"=>"open_produit(document.ADForm.HTML_GEN_LSB_id.value,0);"));
  $Myform->setFieldProperties("id",FIELDP_IS_REQUIRED,true);

  //Remplissage de la liste Type de produit
  foreach( $Prod as $key => $Produit) {
    $Myform->setFieldProperties("id",FIELDP_ADD_CHOICES,array($Produit['id']=>$Produit['libel']));
  }

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, $retour_ecran);
  if ($global_nom_ecran == "Sdo-1")
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Sdo-4");
  else
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Sra-4");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Sdo-4  : Echéancier théorique */
else if (($global_nom_ecran == "Sdo-4")||($global_nom_ecran == "Sra-4")) {
  global $adsys;
  setMonnaieCourante(NULL);
  // setMonnaieCourante($global_monnaie);
  //Si écran précédent est Sdo-2
  if ((!strstr($global_nom_ecran_prec,"Sdo-2"))&&(!strstr($global_nom_ecran_prec,"Sra-2"))) {
    unset($SESSION_VARS['id_type_prod']);
    $SESSION_VARS['id_type_prod']= $HTML_GEN_LSB_id;

  } else {
    $HTML_GEN_LSB_id= $SESSION_VARS['id_type_prod'];
  }
  // Récupération des infos sur les produits de crédit octroyables au client
  $condition="where id='$HTML_GEN_LSB_id'";
  // Récupération des infos sur les produits de crédit octroyables au client
  $Prod  = getProdInfo($condition);
  $SESSION_VARS['produit']=$Prod[0];
  if ($global_nom_ecran == "Sdo-4") {
    // On récupère les infos du client (ou du GS) et des produits qui lui sont octroyables
    $SESSION_VARS['infos_clients'][$global_id_client] = getClientDatas($global_id_client);
    // Récupération d'infos necessaire au crédit
    if ( $SESSION_VARS['produit']['gs_cat'] == 1) { // si Groupe solidaire(GS) produit cas 1

      unset($SESSION_VARS['liste_membres']);
      $nom_client = getClientName($global_id_client);
      $SESSION_VARS['liste_membres'][$global_id_client] = $globalid_client." ".$nom_client;
    }
  }

  $Myform = new HTML_GEN2(_("Simulation échéancier théorique"));

  $JS_ID="";
  foreach($SESSION_VARS['liste_membres'] as $id_client =>$nom_client) {


    $Myform->addField("libel$id_client", sprintf(_("Libellé Produit client n° %s"), $id_client), TYPC_TXT);
    $Myform->addField("devise$id_client", _("Devise"),TYPC_TXT);
    $Myform->addField("mnt_dem$id_client", sprintf(_("Montant demandé client n° %s"), $id_client), TYPC_MNT);
    $Myform->addField("duree_mois$id_client", sprintf(_("Durée du crédit  client n° %s"), $id_client), TYPC_INT);
    $Myform->addField("differe_jours$id_client", _("Différé (en jour)"), TYPC_INN);
    $Myform->addField("differe_ech$id_client", _("Différé (en échéances)"), TYPC_INT);
    $Myform->addField("date_debloc$id_client", _("Date théorique de déboursement "), TYPC_DTG);
    $Myform->addField("tx_interet$id_client", _("Taux d'intérêt (en pourcentage)"), TYPC_TXT);
    $Myform->addField("periodicite$id_client", _("Périodicité"),TYPC_TXT);
    $Myform->addField("mnt_commission$id_client", _("Montant de la commission"), TYPC_MNT);
    $Myform->addField("mode_perc_int$id_client", _("Mode de perception des intérêts"), TYPC_TXT);
    $Myform->addField("mode_calc_int$id_client", _("Mode de calcul d' intérêts"), TYPC_TXT);

    $Myform->setFieldProperties("mnt_dem$id_client",FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("duree_mois$id_client",FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("date_debloc$id_client",FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("date_debloc$id_client",FIELDP_DEFAULT,date("d/m/Y"));

    $Myform->addField("mnt_frais$id_client", _("Montant des frais de dossier"), TYPC_MNT);
    $Myform->addField("mnt_assurance$id_client", _("Montant des assurances"), TYPC_MNT);
    $Myform->addField("mnt_garantie$id_client", _("Montant des garanties numéraires au debut"), TYPC_MNT);
    $Myform->addField("mnt_garantie_mat$id_client", _("Montant des garanties matérielles au debut"), TYPC_MNT);
    $Myform->addField("mnt_garantie_encours$id_client", _("Montant des garanties en cours"), TYPC_MNT);
    $Myform->setFieldProperties("periodicite$id_client",FIELDP_DEFAULT,adb_gettext($adsys["adsys_type_periodicite"][$Prod[0]['periodicite']]));
    $Myform->setFieldProperties("periodicite$id_client",FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("libel$id_client",FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("libel$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("libel$id_client",FIELDP_WIDTH,42);
    $Myform->setFieldProperties("devise$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("devise$id_client",FIELDP_WIDTH,3);
    $Myform->setFieldProperties("mode_calc_int$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mode_calc_int$id_client",FIELDP_DEFAULT,adb_gettext($adsys["adsys_mode_calc_int_credit"][$Prod[0]['mode_calc_int']]));
    $Myform->setFieldProperties("periodicite$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("tx_interet$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mode_perc_int$id_client", FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mode_perc_int$id_client", FIELDP_DEFAULT,adb_gettext($adsys["adsys_mode_perception_int"][$Prod[0]['mode_perc_int']]));
    $Myform->setFieldProperties("mnt_frais$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_commission$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_assurance$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_garantie$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_garantie_mat$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_garantie_encours$id_client",FIELDP_IS_LABEL,true);
    $JS_ID .="fillFields".$id_client."();";

    $Myform->setFieldProperties("mnt_dem".$id_client,FIELDP_JS_EVENT,array("OnChange"=>"checkAndComput".$id_client."();"));

    //Génération du code javascript pour le remplissage des champs relatifs au produit sélectionné
    $JS="";
    $JScode="";
    $JScode_1="";
    $JScode .="\nfunction fillFields".$id_client."()\n";
    $JScode .="{\n\t";
    //  $JScode.="resetFields();\n";

    $JScode_1 .="\nfunction checkAndComput".$id_client."(){\n"; //Debut de comput
    $JScode_1 .="\tif (".$HTML_GEN_LSB_id." == 0) {alert('"._("Sélectionnez un produit SVP!")."'); return false;}\n";
    $JScode_1 .="\tif(!parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value))) {alert('"._("Le montant demandé doit être correctement renseigné")."'); return false;}\n";


    foreach( $Prod as $key => $Produit) {

      $JScode .= "\tif (".$HTML_GEN_LSB_id." == ".$Produit['id'].")\n";
      $JScode .="\t{\n";
      $val_tx = 100 * $Produit['tx_interet'];
      $JScode .="\t\t document.ADForm.tx_interet".$id_client.".value = $val_tx;\n";
      $JScode .="\t\t document.ADForm.libel".$id_client.".value = \"".$Produit['libel']."\";\n";
      $JScode .="\t\t document.ADForm.devise".$id_client.".value = '".$Produit['devise']."';\n";
      $JScode .="\t\t document.ADForm.mnt_frais".$id_client.".value = ".$Produit['mnt_frais'].";\n";
      if ($Produit['prc_commission'] > 0)
        $JScode .="\t\t document.ADForm.mnt_commission".$id_client.".value = ".$Produit['mnt_commission']."+".$Produit['prc_commission']."*parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value));\n";
      else
        $JScode .="\t\t document.ADForm.mnt_commission".$id_client.".value = ".$Produit['mnt_commission'].";\n";
      $JScode .="}\n";

      $JScode_1 .= "\tif (".$HTML_GEN_LSB_id." == ".$Produit['id'].")\n";
      $JScode_1 .="\t{\n";

      //Calule du montant de l'assurance
      if ($Produit['prc_gar_encours'] == "")
        $Produit['prc_gar_encours']=0;
      $JScode_1 .="\t\tdocument.ADForm.mnt_assurance".$id_client.".value =Math.round(".$Produit['prc_assurance']."* parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)));\n";
      $JScode_1 .="\t\tdocument.ADForm.mnt_assurance".$id_client.".value =formateMontant(document.ADForm.mnt_assurance".$id_client.".value);\n";
      // Calule du montant de la commission
      $JScode_1 .="\t\tdocument.ADForm.mnt_commission".$id_client.".value = Math.round(".$Produit['mnt_commission']."+".$Produit['prc_commission']."*parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)));\n";
      $JScode_1 .="\t\tdocument.ADForm.mnt_commission".$id_client.".value =formateMontant(document.ADForm.mnt_commission".$id_client.".value);\n";

      //Calule du montant des garanties numéraires
      $JScode_1 .="\t\t document.ADForm.mnt_garantie".$id_client.".value =Math.round(".$Produit['prc_gar_num']."* parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)));\n";
      $JScode_1 .="\t\tdocument.ADForm.mnt_garantie".$id_client.".value =formateMontant(document.ADForm.mnt_garantie".$id_client.".value);\n";
      $JScode_1 .="\t\t document.ADForm.mnt_garantie_mat".$id_client.".value =Math.round(".$Produit['prc_gar_mat']."* parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)));\n";
      $JScode_1 .="\t\tdocument.ADForm.mnt_garantie_mat".$id_client.".value =formateMontant(document.ADForm.mnt_garantie_mat".$id_client.".value);\n";
      $JScode_1 .="\t\t document.ADForm.mnt_garantie_encours".$id_client.".value =Math.round(".$Produit['prc_gar_encours']."* parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)));\n";
      $JScode_1 .="\t\tdocument.ADForm.mnt_garantie_encours".$id_client.".value =formateMontant(document.ADForm.mnt_garantie_encours".$id_client.".value);\n";
      $JScode_1 .="\t}\n";


      // Ajout de code JS à checkForm
      // Test de la durée du crédit demandé compris entre mnt min. et max.
      $JS_1 .= "\t\tif (".$HTML_GEN_LSB_id." == ".$Produit['id'].")\n";
      $JS_1 .="\t\t{\n";
      $JS_1.="\t\tif(parseInt(".$Produit['duree_max_mois'].")>0){\n";
      $JS_1.="\t\t\tif((parseInt(document.ADForm.duree_mois".$id_client.".value) < parseInt(".$Produit['duree_min_mois'].")) || (parseInt(document.ADForm.duree_mois".$id_client.".value) > parseInt(".$Produit['duree_max_mois']."))) { msg+=' - ".sprintf(_("La durée du crédit doit être comprise entre %s et %s comme définie dans le produit."),$Produit['duree_min_mois'],$Produit['duree_max_mois'])."\\n';ADFormValid=false;}\n";
      $JS_1 .="\t\t}else\n";
      $JS_1.="\t\t\tif(parseInt(document.ADForm.duree_mois".$id_client.".value) < parseInt(".$Produit['duree_min_mois'].")) { msg+=' - La durée du crédit doit être au moins égale à ".$Produit['duree_min_mois']." comme définie dans le produit.\\n';ADFormValid=false;}\n";

      //Test des différes
      $JS_1.="\t\tif(parseInt(document.ADForm.differe_jours".$id_client.".value) > ".$Produit['differe_jours_max']."){ msg+=' - ".sprintf(_("Le différe doit être au plus égal à %s jours."),$Produit['differe_jours_max'])."\\n';ADFormValid=false;}\n";
      $JS_1.="\t\tif(parseInt(document.ADForm.differe_ech".$id_client.".value) > ".$Produit['differe_ech_max']."){ msg+=' - ".sprintf(_("Le différe doit être au plus égal à %s échéances."),$Produit['differe_ech_max'])."\\n';ADFormValid=false;}\n";

      // Test du montant demandé
      $JS_1 .="\t\tif(parseFloat(".$Produit['mnt_max'].")>0){\n";
      $JS_1 .="\t\t\t if((parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)) < parseFloat(".$Produit['mnt_min'].")) || (parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)) > parseFloat(".$Produit['mnt_max']."))){ msg +='- Le montant demandé doit être compris entre ".afficheMontant($Produit['mnt_min'])." et ".afficheMontant($Produit['mnt_max'])." comme défini dans le produit\\n'; ADFormValid=false;}\n";
      $JS_1 .="\t\t}else\n";
      $JS_1 .="\t\t\tif(parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)) < parseFloat(".$Produit['mnt_min'].")) { msg +='- ".sprintf(_("Le montant demandé doit être au moins égal à %s comme défini dans le produit"),afficheMontant($Produit['mnt_max']))."\\n'; ADFormValid=false;}\n";

      // Test de la durée demandée à faire uniquement si le nombre de mois de la période est > 1
      if ($adsys["adsys_duree_periodicite"][$Produit['periodicite']] > 1) {
        $JS_1 .= "\t\tif (parseInt(document.ADForm.duree_mois".$id_client.".value) % parseInt(".$adsys["adsys_duree_periodicite"][$Produit['periodicite']].") != 0)
               {
                 msg += '- ".sprintf(_("La durée doit être multiple de %s"),$adsys["adsys_duree_periodicite"][$Produit['periodicite']])."';
                 ADFormValid = false;
               }\n";
      }
      $JS_1 .="\t\t}\n";
    }
    $JScode .="}\n";
    $JScode_1 .="\t}\n";

    // Si on a déjà renseigné cet écran on recup les anciennes valeurs
    if (isset($SESSION_VARS['infos_doss'])) {


      $JS .= "document.ADForm.mnt_dem".$id_client.".value = '". $SESSION_VARS['infos_doss'][$id_client]['def']['mnt_dem']."';\n";
      $JS .= "document.ADForm.duree_mois".$id_client.".value = '". $SESSION_VARS['infos_doss'][$id_client]['def']['duree_mois']."';\n";
      $JS .= "document.ADForm.differe_jours".$id_client.".value = '". $SESSION_VARS['infos_doss'][$id_client]['def']['differe_jours']."';\n";
      $JS .= "document.ADForm.differe_ech".$id_client.".value = '". $SESSION_VARS['infos_doss'][$id_client]['def']['differe_ech']."';\n";
      $JS .= "document.ADForm.HTML_GEN_date_date_debloc".$id_client.".value = '". $SESSION_VARS['infos_doss'][$id_client]['def']['date_debloc']."';\n";
      $JS .= "fillFields".$id_client."();\n";
      $JS .= "checkAndComput".$id_client."();\n";

      $Myform->addJS(JSP_FORM, "js_def".$id_client, $JS);
    }

    $Myform->addJS(JSP_BEGIN_CHECK,"test".$id_client,$JS_1);
    $Myform->addJS(JSP_FORM,"fillF".$id_client,$JScode);
    $Myform->addJS(JSP_FORM,"comp".$id_client,$JScode_1);

    // Réinitialise le champs montant demandé
    $JScode_2 ="";
    $JScode_2 .="\nfunction resetFields()\n";
    $JScode_2 .="{\n";
    $JScode_2 .="\t document.ADForm.mnt_dem".$id_client.".value =\"\";\n";
    $JScode_2 .="\t document.ADForm.duree_mois".$id_client.".value =\"\";\n";
    $JScode_2 .="\t document.ADForm.mnt_assurance".$id_client.".value =\"\";\n";
    $JScode_2 .="\t document.ADForm.mnt_garantie".$id_client.".value =\"\";\n";
    $JScode_2 .="\t document.ADForm.mnt_garantie_encours".$id_client.".value =\"\";\n";
    $JScode_2 .="}\n";
    $Myform->addJS(JSP_FORM,"resetF".$id_client,$JScode_2);
    $Myform->addHTMLExtraCode("espace".$id_client,"<BR>");
  }


  //Exécution des fonctions javascript qui préremplissent les champs
  $Myform->addJS(JSP_FORM,"Renseigne_valeurs",$JS_ID);
  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  if ($global_nom_ecran == "Sdo-4") {
    $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Sdo-1");
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Sdo-2");
  } else {
    $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Sra-1");
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Sra-2");
  }
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();


}

/*}}}*/

/*{{{ Sdo-2 Sra-2 : Echéancier théorique */
else if (($global_nom_ecran == "Sdo-2") || ($global_nom_ecran == "Sra-2")) {
  $HTML_code="";

  //Récupèration des données du produit
  $id= $SESSION_VARS['id_type_prod'];
  $whereCl=" where id='$id' " ;
  $Produit = getProdInfo($whereCl);// Tableau associatif de produit $Produit[$i]["champ"]
  $SESSION_VARS['produit']=$Produit[0];
  foreach($SESSION_VARS['liste_membres'] as $id_client =>$nom_client) {
    // Sauve les valeurs renseignées pour les réafficher en cas de retour arrière
    $SESSION_VARS['infos_doss'][$id_client]['def']['id'] = $id;
    $SESSION_VARS['infos_doss'][$id_client]['def']['mnt_dem'] = $ {'mnt_dem'.$id_client};
    $SESSION_VARS['infos_doss'][$id_client]['def']['duree_mois'] = $ {'duree_mois'.$id_client};
    $SESSION_VARS['infos_doss'][$id_client]['def']['differe_jours'] = $ {'differe_jours'.$id_client};
    $SESSION_VARS['infos_doss'][$id_client]['def']['differe_ech'] = $ {'differe_ech'.$id_client};
    $SESSION_VARS['infos_doss'][$id_client]['def']['date_debloc'] = $ {'date_debloc'.$id_client};

    // Devise courante = devise du crédit
    setMonnaieCourante( $SESSION_VARS['produit']["devise"]);
    $SESSION_VARS['infos_doss'][$id_client]["devise"] =  $SESSION_VARS['produit']["devise"];
    $SESSION_VARS['devise']= $SESSION_VARS['produit']["devise"];
    // Appel de la fonction echéancier théorique
    $echeancier = calcul_echeancier_theorique($id, recupMontant($ {'mnt_dem'.$id_client}), $ {'duree_mois'.$id_client}, $ {'differe_jours'.$id_client}, $ {'differe_ech'.$id_client});

    // Appel de l'affichage de l'échéancier
    $parametre["lib_date"]=_("Date du jour");
    $parametre["index"]= 0;//Index de début de l'échéancier
    $parametre["titre"]= _("Echéancier théorique de remboursement");
    $parametre["nbre_jour_mois"] = 30; // FIXME : En dur ?????
    $parametre["montant"]= recupMontant($ {'mnt_dem'.$id_client});
    $parametre["mnt_reech"]= 0; //Montant rééchelonnement
    $parametre["mnt_octr"]= $parametre["montant"]; //Montant octroyé
    $parametre["mnt_frais_doss"]= $SESSION_VARS['produit']["mnt_frais"];
    $parametre["mnt_commission"]=$parametre["montant"]*  $SESSION_VARS['produit']["prc_commission"];
    $parametre["mnt_assurance"]=$parametre["montant"]*  $SESSION_VARS['produit']["prc_assurance"];
    $parametre["garantie"]= $parametre["montant"]*  $SESSION_VARS['produit']["prc_gar_num"]+$parametre["montant"]*  $SESSION_VARS['produit']["prc_gar_encours"];
    $parametre["garantie_mat"]=$parametre["montant"]*  $SESSION_VARS['produit']["prc_gar_mat"];
    $parametre["duree"]= $ {'duree_mois'.$id_client}; //Nouvelle durée du crédit
    $parametre["date"]= $ {'date_debloc'.$id_client};
    $parametre["id_prod"]= $id;
    $parametre["id_doss"]= -1;//Aucun dossier lié à l'échéancier
    $parametre["differe_jours"]=$ {'differe_jours'.$id_client};
    $parametre["differe_ech"]=$ {'differe_ech'.$id_client};
    $parametre["EXIST"]=0; // Vaut 0 si l'échéancier n'est pas stocké dans la BD 1 sinon
    $parametre["id_client"]=$id_client; // L'identifiant du client
    // Génération de l'échéancier réel (avec date de déblocage fournie par l'utilisateur)
    $echeancierComplet = completeEcheancier($echeancier,$parametre);
    // Je dois calculer le montant total restant dû car ce n'est pas fait dans completeEcheancier mais dans HTML_echeancier (!)
    // Pas cool ...
    // Calcul du montant total à payer pour ce crédit
    reset($echeancierComplet);
    $total_cap = 0;
    $total_int = 0;
    $total_gar = 0;
    while (list(,$ech) = each($echeancierComplet)) {
      $total_cap += $ech["mnt_cap"];
      $total_int += $ech["mnt_int"];
      $total_gar += $ech["mnt_gar"];
    }
    // Remplissage du champs du montant total
    reset($echeancierComplet);
    $solde_cap = $total_cap;
    $solde_int = $total_int;
    $solde_gar = $total_gar;
    while (list($key,$ech) = each($echeancierComplet)) {
      $solde_cap -= $ech["mnt_cap"];
      $solde_int -= $ech["mnt_int"];
      $solde_gar -= $ech["mnt_gar"];
      $echeancierComplet[$key]["solde_cap"] = $solde_cap;
      $echeancierComplet[$key]["solde_int"] = $solde_int;
      $echeancierComplet[$key]["solde_gar"] = $solde_gar;
    }

    $SESSION_VARS['infos_doss'][$id_client]["ECH"] = $echeancierComplet;
    $SESSION_VARS['infos_doss'][$id_client]["echeances"]= $SESSION_VARS['infos_doss'][$id_client]["ECH"];
    // Collecte des informations pour le header contextuel
    global $adsys;
    $CRIT = array();
    $CRIT[_("Nom du Client")] = getClientName($parametre["id_client"]) ;//inclu nom du client_513
    $CRIT[_("Montant")] = afficheMontant($parametre["montant"], true);
    $CRIT[_("Durée du crédit")] = $parametre["duree"];
    $CRIT[_("Date de déboursement")] = $parametre["date"];
    $CRIT[_("Produit de crédit")] =  $SESSION_VARS['produit']["libel"];
    $CRIT[_("Montant des frais de dossier")] = afficheMontant($parametre["mnt_frais_doss"], true);
    $CRIT[_("Montant de la commission")] = afficheMontant($parametre["mnt_commission"], true);
    $CRIT[_("Montant des assurances")] = afficheMontant($parametre["mnt_assurance"], true);
    $CRIT[_("Montant de la garantie numéraire")] = afficheMontant($parametre["garantie"], true);
    $CRIT[_("Montant de la garantie matérielle")] = afficheMontant($parametre["garantie_mat"], true);
    $CRIT[_("Différé")] = str_affichage_diff($differe_jours, $differe_ech);
    $CRIT[_("Taux d'intérêt")] = affichePourcentage( $SESSION_VARS['produit']["tx_interet"]);
    $CRIT[_("Périodicité de remboursement")] = adb_gettext($adsys["adsys_type_periodicite"][ $SESSION_VARS['produit']["periodicite"]]);
    $CRIT[_("Mode de calcul des intérêts")] = adb_gettext($adsys["adsys_mode_calc_int_credit"][ $SESSION_VARS['produit']["mode_calc_int"]]);
    $CRIT[_("Délais de grâce")] = $SESSION_VARS['produit']["delai_grace_jour"]." "._("jours");
    $CRIT[_("Nombre de jours pour bloquer le crédit avant échéance")] = $SESSION_VARS['produit']["nb_jr_bloq_cre_avant_ech_max"]." "._("jours");
    $SESSION_VARS['infos_doss'][$id_client]["CRIT"] = $CRIT;
    $SESSION_VARS['infos_doss'][$id_client]["total_cap"] = $total_cap;
    $SESSION_VARS['infos_doss'][$id_client]["total_int"] = $total_int;
    $SESSION_VARS['infos_doss'][$id_client]["total_gar"] = $total_gar;

    $HTML_code .= HTML_echeancier($parametre,$echeancier);
  }

  $formEcheancier = new HTML_GEN2();

  // les boutons ajoutés
  $formEcheancier->addFormButton(1,1,"retour",_("Précédent"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,2,"imprimer",_("Imprimer"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,3,"annuler",_("Retour Menu"),TYPB_SUBMIT);


  // Propriétés des boutons
  if ($global_nom_ecran == "Sdo-2") {
    $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN,"Gen-11");
    $formEcheancier->setFormButtonProperties("imprimer", BUTP_PROCHAIN_ECRAN, 'Sdo-3');
    $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Sdo-4");
  } else {
    $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN,"Gen-13");
    $formEcheancier->setFormButtonProperties("imprimer", BUTP_PROCHAIN_ECRAN, 'Sra-3');
    $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Sra-4");
  }

  $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);



  $formEcheancier->buildHTML();
  echo  $HTML_code;
  echo $formEcheancier->getHTML();

}
/*}}}*/

/*{{{ Sdo-3 Sra-3 : Impression échéancier */
else if ($prochain_ecran == 'Sdo-3' || $prochain_ecran == 'Sra-3') {

  if ($prochain_ecran == 'Sdo-3')
    $retour_ecran="Gen-11";
  else
    $retour_ecran="Gen-13";

  setMonnaieCourante($SESSION_VARS["devise"]);

  $xml = xml_echeancier_theorique($SESSION_VARS["infos_doss"]);

  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'simulation_echeancier.xslt');

  echo get_show_pdf_html($retour_ecran, $fichier_pdf);

  ajout_historique(350, NULL, NULL, $global_nom_login, date("r"), NULL);

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>