<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [68] Simulation échéancier DAT
 * Cette opération comprends les écrans :
 * - Spe-1 : paramètres de l'échéancier
 * - Spe-2 : échéancier théorique
 * - Spe-3 : impression échéancier
 * @package Epargne
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
//require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/misc/divers.php';
require_once 'modules/rapports/xml_echeancier.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/dbProcedures/interface.php';

if ($global_nom_ecran == "Spe-1" || $global_nom_ecran == "Spe-2" || $global_nom_ecran == 'Spe-3')
  $retour_ecran = "Gen-10";
else
  $retour_ecran = "Gen-13";

/*{{{ Spe-1 : Paramètres de l'échéancier ->  || ($global_nom_ecran == "Sta-1")*/
if (($global_nom_ecran == "Spe-1")) {

  // On vide le tableau de sauvegarde des données sauf si on vient d'un écran de la même fonction
  //  || strstr($global_nom_ecran_prec,"Sta")
  if (!(strstr($global_nom_ecran_prec,"Spe") ||strstr($global_nom_ecran_prec,"Spe-4") ))
    unset($SESSION_VARS['def']);
  unset($SESSION_VARS['liste_membres']);
  unset($SESSION_VARS['produit']);
  unset($SESSION_VARS['infos_doss']);
  unset($SESSION_VARS['infos_cpte']);
  setMonnaieCourante(NULL);
  if ($global_nom_ecran == "Spe-1") {
    // On récupère les infos du client (ou du GS) et des produits qui lui sont octroyables
    $SESSION_VARS['infos_clients'][$global_id_client] = getClientDatas($global_id_client);
    // Récupération d'infos necessaire epargne
    if ($SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) { // si Groupe solidaire (GS)
      // Récupération des membres du groupe
      $result = getListeMembresGrpSol($global_id_client);
      if (is_array($result->param))
        foreach($result->param as $key=>$id_cli) {
        $nom_client = getClientName($id_cli);
        $SESSION_VARS['liste_membres'][$id_cli] = $nom_client;
      }
    } else { // Personne physique, Personne morale ou  Groupe Informel
      $nom_client = getClientName($global_id_client);
      $SESSION_VARS['liste_membres'][$global_id_client] = $globalid_client." ".$nom_client;
    }
    // Récupération des infos sur le client

  } else {

    $CliParam=getPremierClient();
    $SESSION_VARS['liste_membres'][$CliParam->param[0]['id_client'] ]= " ";
  }
  $Prod  = getListProdEpargneDispo($global_id_client);
  // $Prod = getProdInfo("");  //Retourne les informations sur le produit
  $Myform = new HTML_GEN2(_("Choix du produit"));

  // Les champs ajoutés
  $Myform->addField("id", _("Type de produit d'epargne"), TYPC_LSB);
  // Ajout de liens
  $Myform->addLink("id", "produit",_("Détail produit"), "#");
  $Myform->setLinkProperties("produit",LINKP_JS_EVENT,array("onClick"=>"open_produit_epargne();return false;"));
  $codeJsProd="function open_produit_epargne()
        {
          id_prod = document.ADForm.HTML_GEN_LSB_id.value;
          if (id_prod > 0) {
          url='$http_prefix/lib/html/prodEpargne.php?m_agc=".$_REQUEST['m_agc']."&id=' + id_prod;
          EpargneWindow=window.open(url,'Produit Epargne','always Raised=1,dependant=1,scrollbars,resizable=0,width=550,height=600');
        }
          return false;
        }";
  $Myform->addJS(JSP_FORM, "JSProd", $codeJsProd);
  $Myform->setFieldProperties("id",FIELDP_IS_REQUIRED,true);

  //Remplissage de la liste Type de produit
  foreach( $Prod as $key => $Produit2) {
    $Myform->setFieldProperties("id",FIELDP_ADD_CHOICES,array($Produit2['id']=>$Produit2['libel']));
  }

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, $retour_ecran);
  if ($global_nom_ecran == "Spe-1"){
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Spe-4");
  }
  /*else
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Sta-4");*/
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Spe-4  : Echéancier théorique -> ||($global_nom_ecran == "Sta-4")*/
else if (($global_nom_ecran == "Spe-4")) {
  global $adsys;
  setMonnaieCourante(NULL);
  // setMonnaieCourante($global_monnaie);
  //Si écran précédent est Spe-2
  if ((!strstr($global_nom_ecran_prec,"Spe-2"))) { //&&(!strstr($global_nom_ecran_prec,"Sta-2"))
    unset($SESSION_VARS['id_type_prod']);
    $SESSION_VARS['id_type_prod']= $HTML_GEN_LSB_id;

  } else {
    $HTML_GEN_LSB_id= $SESSION_VARS['id_type_prod'];
  }

  // Récupération des infos sur les produits epargne octroyables au client
  $Prod1  = getListProdEpargneDispo($global_id_client);
  foreach( $Prod1 as $key => $Produit1) {
    if ($Produit1['id'] == $HTML_GEN_LSB_id){
      $Prod2 = $Produit1;
      $SESSION_VARS['produit'][$Produit1['id']]=$Prod2;
    }
  }

  if ($global_nom_ecran == "Spe-4") {
    // On récupère les infos du client (ou du GS) et des produits qui lui sont octroyables
    $SESSION_VARS['infos_clients'][$global_id_client] = getClientDatas($global_id_client);
    // Récupération d'infos necessaire
    if ( $SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) { // si Groupe solidaire(GS) produit cas 1

      unset($SESSION_VARS['liste_membres']);
      $nom_client = getClientName($global_id_client);
      $SESSION_VARS['liste_membres'][$global_id_client] = $globalid_client." ".$nom_client;
    }
  }

  $Myform = new HTML_GEN2(_("Simulation échéancier théorique de DAT"));

  $CHOICES = array();
  $CHOICES[1] = _("Compte lui-meme");
  if ($Prod2["classe_comptable"] != 6) // compte d'épargne à la source prend le compte lui même comme compte de versement des intérêts
    $CHOICES[2] = "Autre compte";

  $JS_ID="";
  foreach($SESSION_VARS['liste_membres'] as $id_client =>$nom_client) {

    $Myform->addField("libel$id_client", sprintf(_("Libellé Produit client n° %s"), $id_client), TYPC_TXT);
    $Myform->addField("devise$id_client", _("Devise"),TYPC_TXT);
    $Myform->addField("mnt_dem$id_client", sprintf(_("Montant de DAT client n° %s"), $id_client), TYPC_MNT);
    $Myform->addField("duree_mois$id_client", sprintf(_("Terme de DAT n° %s"), $id_client), TYPC_INT);
    $Myform->addField("tx_interet$id_client", _("Taux d'intérêt (en pourcentage)"), TYPC_TXT);
    $Myform->addField("date_debloc$id_client", _("Date théorique"), TYPC_DTG);
    $Myform->addField("mode_calc_int$id_client", _("Mode de calcul d' intérêts"), TYPC_TXT);
    $Myform->addField("periodicite$id_client", _("Fréquence de calcul des intérets"),TYPC_TXT);
    $Myform->addField("mode_calc_int_rup$id_client", _("Mode de calcul des intérêts a la rupture"), TYPC_TXT);
    $Myform->addField("mode_calc_pen_rup$id_client", _("Mode de calcul des penalités a la rupture"), TYPC_TXT);
    $Myform->addField("type_cpt_vers_int$id_client", _("Compte de versement des intérets"), TYPC_LSB);

    $Myform->setFieldProperties("mnt_dem$id_client",FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("duree_mois$id_client",FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("date_debloc$id_client",FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("date_debloc$id_client",FIELDP_DEFAULT,date("d/m/Y"));

    $Myform->setFieldProperties("periodicite$id_client",FIELDP_DEFAULT,adb_gettext($adsys["adsys_freq"][$Prod2['freq_calcul_int']]));
    $Myform->setFieldProperties("periodicite$id_client",FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("libel$id_client",FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("libel$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("libel$id_client",FIELDP_WIDTH,42);
    $Myform->setFieldProperties("devise$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("devise$id_client",FIELDP_WIDTH,3);
    $Myform->setFieldProperties("mode_calc_int$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mode_calc_int$id_client",FIELDP_DEFAULT,adb_gettext($adsys["adsys_mode_calcul_int_epargne"][$Prod2['mode_calcul_int']]));
    $Myform->setFieldProperties("periodicite$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mode_calc_int_rup$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mode_calc_int_rup$id_client",FIELDP_DEFAULT,adb_gettext($adsys["adsys_mode_calcul_int_rupt"][$Prod2['mode_calcul_int_rupt']]));
    $Myform->setFieldProperties("mode_calc_pen_rup$id_client",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mode_calc_pen_rup$id_client",FIELDP_DEFAULT,adb_gettext($adsys["adsys_mode_calcul_penal_rupt"][$Prod2['mode_calcul_penal_rupt']]));
    $Myform->setFieldProperties("type_cpt_vers_int$id_client", FIELDP_HAS_CHOICE_AUCUN, false);
    $Myform->setFieldProperties("type_cpt_vers_int$id_client", FIELDP_ADD_CHOICES, $CHOICES);

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

    foreach( $SESSION_VARS['produit'] as $key => $Produit) {

      $JScode .= "\tif (".$HTML_GEN_LSB_id." == ".$Produit['id'].")\n";
      $JScode .="\t{\n";
      $val_tx = 100 * $Produit['tx_interet'];
      $JScode .="\t\t document.ADForm.tx_interet".$id_client.".value = $val_tx;\n";
      $JScode .="\t\t document.ADForm.libel".$id_client.".value = \"".$Produit['libel']."\";\n";
      $JScode .="\t\t document.ADForm.devise".$id_client.".value = '".$Produit['devise']."';\n";
      $JScode .="\t\t document.ADForm.duree_mois".$id_client.".value = '".$Produit['terme']."';\n";
      $JScode .="}\n";

      $JScode_1 .= "\tif (".$HTML_GEN_LSB_id." == ".$Produit['id'].")\n";
      $JScode_1 .="\t{\n}";

      // Ajout de code JS à checkForm
      // Test de la durée du crédit demandé compris entre mnt min. et max.
      $JS_1 .= "\t\tif (".$HTML_GEN_LSB_id." == ".$Produit['id'].")\n";
      $JS_1 .="\t\t{\n";

      // Test du montant demandé
      $JS_1 .="\t\tif(parseFloat(".$Produit['mnt_max'].")>0){\n";
      $JS_1 .="\t\t\t if((parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)) < parseFloat(".$Produit['mnt_min'].")) || (parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)) > parseFloat(".$Produit['mnt_max']."))){ msg +='- Le montant demandé doit être compris entre ".afficheMontant($Produit['mnt_min'])." et ".afficheMontant($Produit['mnt_max'])." comme défini dans le produit\\n'; ADFormValid=false;}\n";
      $JS_1 .="\t\t}else\n";
      $JS_1 .="\t\t\tif(parseFloat(recupMontant(document.ADForm.mnt_dem".$id_client.".value)) < parseFloat(".$Produit['mnt_min'].")) { msg +='- ".sprintf(_("Le montant demandé doit être au moins égal à %s comme défini dans le produit"),afficheMontant($Produit['mnt_min']))."\\n'; ADFormValid=false;}\n";

      // Test de l'interet demandé
      $JS_1 .="\t\t\tif(parseFloat(recupMontant(document.ADForm.tx_interet".$id_client.".value)) > parseFloat(".($Produit['tx_interet_max']*100).")) { msg +='- ".sprintf(_("Interet doit être au moins égal à %s comme défini dans le produit"),($Produit['tx_interet_max']*100))."\\n'; ADFormValid=false;}\n";

      // Test de la durée demandée à faire uniquement si le nombre de mois de la période est > 1
      if ($Produit['freq_calcul_int'] > 1) {
        $terme = 1;
        if ($Produit['freq_calcul_int']==2){
          $terme = 3;
        }
        if ($Produit['freq_calcul_int']==3){
          $terme = 6;
        }
        if ($Produit['freq_calcul_int']==4){
          $terme = 12;
        }
        $JS_1 .= "\t\tif (parseInt(document.ADForm.duree_mois".$id_client.".value) % parseInt(".$terme.") != 0)
               {
                 msg += '- ".sprintf(_("La durée doit être multiple de %s comme défini dans le produit"),$terme)."';
                 ADFormValid = false;
               }\n";
      }
      $JS_1 .="\t\t}\n";
    }
    $JScode .="}\n";
    $JScode_1 .="\t}\n";

    $Myform->addJS(JSP_BEGIN_CHECK,"test".$id_client,$JS_1);
    $Myform->addJS(JSP_FORM,"fillF".$id_client,$JScode);
    $Myform->addJS(JSP_FORM,"comp".$id_client,$JScode_1);

    // Si on a déjà renseigné cet écran on recup les anciennes valeurs
    if (isset($SESSION_VARS['infos_epargne'])) {
      $JS .= "document.ADForm.mnt_dem".$id_client.".value = '". $SESSION_VARS['infos_epargne'][$id_client]['def']['mnt_dem']."';\n";
      $JS .= "document.ADForm.duree_mois".$id_client.".value = '". $SESSION_VARS['infos_epargne'][$id_client]['def']['duree_mois']."';\n";
      $JS .= "document.ADForm.tx_interet".$id_client.".value = '". $SESSION_VARS['infos_epargne'][$id_client]['def']['tx_interet']."';\n";
      $JS .= "document.ADForm.HTML_GEN_date_date_debloc".$id_client.".value = '". $SESSION_VARS['infos_epargne'][$id_client]['def']['date_debloc']."';\n";
      //$JS .= "fillFields".$id_client."();\n";
      //$JS .= "checkAndComput".$id_client."();\n";
      //$Myform->addJS(JSP_FORM, "js_def".$id_client, $JS);
    }

    // Réinitialise le champs montant demandé
    $JScode_2 ="";
    $JScode_2 .="\nfunction resetFields()\n";
    $JScode_2 .="{\n";
    $JScode_2 .="\t document.ADForm.mnt_dem".$id_client.".value =\"\";\n";
    $JScode_2 .="\t document.ADForm.duree_mois".$id_client.".value =\"\";\n";
    $JScode_2 .="}\n";
    $Myform->addJS(JSP_FORM,"resetF".$id_client,$JScode_2);
    $Myform->addHTMLExtraCode("espace".$id_client,"<BR>");
  }

  //Exécution des fonctions javascript qui préremplissent les champs
  $Myform->addJS(JSP_FORM,"Renseigne_valeurs",$JS_ID);

  // Si on a déjà renseigné cet écran on recup les anciennes valeurs
  if (isset($SESSION_VARS['infos_epargne'])) {
    $Myform->addJS(JSP_FORM, "js_def".$id_client, $JS);
  }

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  if ($global_nom_ecran == "Spe-4") {
    $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Spe-1");
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Spe-2");
  } /*else {
    $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Sta-1");
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Sta-2");
  }*/
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();


}

/*}}}*/

/*{{{ Spe-2 Sta-2 : Echéancier théorique */
else if (($global_nom_ecran == "Spe-2")) { // || ($global_nom_ecran == "Sta-2")
  $parametre_echeancier = array(); //les donnees importantes pour le produit : POST[]
  $nombre_ech = 0; //nombres des echeances pour echeancier
  $ech = 1; //debut echeance

  //Récupèration des données du produit
  // Récupération des infos sur les produits epargne octroyables au client
  $id= $SESSION_VARS['id_type_prod'];
  $Prod1  = getListProdEpargneDispo($global_id_client);
  foreach( $Prod1 as $key => $Produit1) {
    if ($Produit1['id'] == $id){
      $Prod2 = $Produit1;
      $SESSION_VARS['produit'][$Produit1['id']]=$Prod2;
    }
  }

  foreach($SESSION_VARS['liste_membres'] as $id_client =>$nom_client) {
    // Sauve les valeurs renseignées pour les réafficher en cas de retour arrière
    $SESSION_VARS['infos_epargne'][$id_client]['def']['id'] = $id;
    $SESSION_VARS['infos_epargne'][$id_client]['def']['mnt_dem'] = $ {'mnt_dem'.$id_client};
    $SESSION_VARS['infos_epargne'][$id_client]['def']['duree_mois'] = $ {'duree_mois'.$id_client};
    $SESSION_VARS['infos_epargne'][$id_client]['def']['tx_interet'] = $ {'tx_interet'.$id_client};
    $SESSION_VARS['infos_epargne'][$id_client]['def']['date_debloc'] = $ {'date_debloc'.$id_client};

    $parametre_echeancier["lib_date"] = _("Date du jour:");
    $parametre_echeancier["index"] = 0;
    $parametre_echeancier["titre"] = _("Echéancier théorique du produit d'epargne");
    $parametre_echeancier["montant"] = recupMontant(${'mnt_dem'.$id_client});
    $parametre_echeancier["duree"]= $ {'duree_mois'.$id_client};
    $parametre_echeancier["date"]= $ {'date_debloc'.$id_client};
    $parametre_echeancier["id_prod"]= $id;
    $parametre_echeancier["id_client"]=$id_client; // L'identifiant du client
    $parametre_echeancier["interet"]= $ {'tx_interet'.$id_client};
    $parametre_echeancier["type_cpt_vers_int"]= $ {'type_cpt_vers_int'.$id_client};

    // Tableau des détails du produit
    $table1 = new HTML_TABLE_table(4, TABLE_STYLE_CLASSIC);
    $table1->set_property("title",$parametre_echeancier["titre"]);
    $table1->add_cell(new TABLE_cell(_("N° client:")));
    $table1->set_cell_property("width","15%");
    $table1->add_cell(new TABLE_cell($parametre_echeancier["id_client"]));
    $table1->add_cell(new TABLE_cell(_("Nom client:")));
    $table1->add_cell(new TABLE_cell(_(getClientName($parametre_echeancier["id_client"]))));
    $table1->set_row_childs_property("align","left");

    $table1->add_cell(new TABLE_cell(_("Produit:")));
    $table1->set_cell_property("width","15%");
    $table1->add_cell(new TABLE_cell($Prod2["libel"]));
    $table1->set_cell_property("width","35%");
    $table1->add_cell(new TABLE_cell(_("Montant de DAT:")));
    $table1->set_cell_property("width","30%");
    $table1->add_cell(new TABLE_cell(afficheMontant ($parametre_echeancier["montant"],true)." ".$Prod2["devise"]));

    $table1->set_cell_property("width","20%");
    $table1->set_row_childs_property("align","left");
    $table1->set_row_property("class","");

    $table1->add_cell(new TABLE_cell(_("Terme en mois:")));
    $table1->add_cell(new TABLE_cell($parametre_echeancier["duree"]));
    $tx=$parametre_echeancier["interet"];
    $table1->add_cell(new TABLE_cell(_("Taux d'intérêt:")));
    $table1->add_cell(new TABLE_cell("$tx%"));
    $table1->set_row_childs_property("align","left");

    $table1->add_cell(new TABLE_cell(_("Fréquence calcule des interets:")));
    $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_freq"][$Prod2["freq_calcul_int"]])));
    $table1->add_cell(new TABLE_cell());
    $table1->add_cell(new TABLE_cell());
    $table1->set_row_childs_property("align","left");

    $table1->add_cell(new TABLE_cell(_("Mode calcul des intérêts:")));
    $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_mode_calcul_int_epargne"][$Prod2["mode_calcul_int"]])));
    $table1->add_cell(new TABLE_cell());
    $table1->add_cell(new TABLE_cell());
    $table1->set_row_childs_property("align","left");

    $table1->add_cell(new TABLE_cell(_("Compte de versement des interets:")));
    $table1->add_cell(new TABLE_cell(_($adsys["adsys_type_cpt_vers_int"][$parametre_echeancier["type_cpt_vers_int"]])));

    $table1->add_cell(new TABLE_cell());
    $table1->add_cell(new TABLE_cell());
    $table1->set_row_childs_property("align","left");

    if(isset($parametre_echeancier["lib_date"])) {
      $table1->add_cell(new TABLE_cell($parametre_echeancier["lib_date"]));
      $table1->add_cell(new TABLE_cell($parametre_echeancier["date"]));
    }
    $table1->set_row_childs_property("align","left");

    //recuperation nombre du duree : terme de DAT
    if (isset($parametre_echeancier["duree"]) && $parametre_echeancier["duree"]!=null){
      $parametre_echeancier["duree"] = $parametre_echeancier["duree"];
    }
    else{
      $parametre_echeancier["duree"] = $Prod2["terme"];
    }

    //*****************************Tableau des échéances*********************************************************
    // Affichage entête
    $table2 = new HTML_TABLE_table(5, TABLE_STYLE_ALTERN);
    $table2->add_cell(new TABLE_cell(_("N°")));
    $table2->add_cell(new TABLE_cell(_("Date")));
    $table2->add_cell(new TABLE_cell(_("Montant du capital")));
    $table2->add_cell(new TABLE_cell(_("Montant des intérêts")));
    $table2->add_cell(new TABLE_cell(_("Total de l'échéance")));

    // Affichage detailles echeances

    $cal_int = 0;
    $solde_DAT = $parametre_echeancier["montant"];

    //initialisation totaux
    $total_cap = 0;
    $total_int = 0;
    $total_ech = 0;

    //initialisation montant des echeances
    $mnt_cap = 0;
    $mnt_int = 0;
    $mnt_ech = 0;

    //date avant debut echeance
    $date_ech = $parametre_echeancier["date"];
    $date_ech_non_mensuelle = $parametre_echeancier["date"];

    // Récupération du nombre de jours par an
    $Agence = getAgenceDatas($global_id_agence);
    if ($Agence["base_taux"] == 1) // 360 jours
      $nbre_jours_an = 360;
    elseif ($Agence["base_taux"] == 2) // 365 jours
      $nbre_jours_an = 365;

    //calcule nombres echeances
    if ($Prod2["freq_calcul_int"] == 1) { //frequence mensuelle
      $frequence = 1;
    }
    if ($Prod2["freq_calcul_int"] == 2) { //frequence trimestrielle
      $frequence = 3;
    }
    if ($Prod2["freq_calcul_int"] == 3) { //frequence semestrielle
      $frequence = 6;
    }
    if ($Prod2["freq_calcul_int"] == 4) { //frequence annuelle
      $frequence = 12;
    }
    $nombre_ech = ($parametre_echeancier["duree"]/$frequence);
    if ($parametre_echeancier["duree"]==$frequence){
      $nombre_ech = 1;
    }

    //configuration date echeance : mensuelle
    if ($date_ech != "") { // Rappel : $date = Date du parametre
      $r = explode("/", $date_ech);
      $jj = (int)1 * $r[0];
      $jj_fix = (int)1 * $r[0];
      $mm = (int)1 * $r[1];
      $mm_fix = (int)1 * $r[1];
      $aa = (int)1 * $r[2];
    }

    //configuration date echeance : trimestrielle, semestrielle et annuelle
    if ($date_ech_non_mensuelle != "") { // Rappel : $date = Date du parametre
      $r = explode("/", $date_ech_non_mensuelle);
      $jj1 = (int)1 * $r[0];
      $jj1_fix = (int)1 * $r[0];
      $mm1 = (int)1 * $r[1];
      $mm1_fix = (int)1 * $r[1];
      $aa1 = (int)1 * $r[2];
    }

    if ($parametre_echeancier["type_cpt_vers_int"]!=1){ //Compte versement des interets : Autre Compte
      // Affichage échéances
      $DATAECH = array();
      $ech1 = $ech;
      for ($ech=$ech1;$ech<=$nombre_ech;$ech++){
        $table2->add_cell(new TABLE_cell($ech));
        $table2->set_cell_property("align","center");

        if ($Prod2["freq_calcul_int"] == 1) { //frequence mensuelle
          $date1 = date("Y-m-d", mktime(0,0,0,$mm_fix,$jj_fix,$aa));
          $date_avan_debut_ech = date_create($date1); //en type object date
          $date_ech = date("Y-m-d", mktime(0,0,0,$mm+$ech,$jj_fix,$aa));
          $date_ech1 = date_create($date_ech); //en type object date
          $ech_prec = $ech-1;
          $date2 = date("Y-m-d", mktime(0,0,0,$mm+$ech_prec,$jj_fix,$aa));
          $date_ech_prec = date_create($date2); //en type object date : date echeance precedant
          $table2->add_cell(new TABLE_cell_date(date("d/m/Y",strtotime($date_ech))));
        }

        if ($Prod2["freq_calcul_int"] == 2 || $Prod2["freq_calcul_int"] == 3 || $Prod2["freq_calcul_int"] == 4){ //frequence trimestrielle, semestrielle et annuelle
          //configuration date echeance
          $date_ech2 = date("d/m/Y",strtotime($date_ech));
          if ($ech==1){
            $date_ech2 = $date_ech;
          }
          if ($date_ech2 != "") { // Rappel : $date = Date du parametre
            $r = explode("/", $date_ech2);
            $jj = (int)1 * $r[0];
            $mm = (int)1 * $r[1];
            $aa = (int)1 * $r[2];
          }
          $addmois = 0;
          if ($Prod2["freq_calcul_int"] == 2) { //frequence trimestrielle
            $addmois = 4;
          }
          if ($Prod2["freq_calcul_int"] == 3) { //frequence semestrielle
            $addmois = 7;
          }
          if ($Prod2["freq_calcul_int"] == 4) { //frequence annuelle
            $addmois = 13;
          }
          if ($ech==1){
            $date_ech = date("Y-m-d", mktime(0,0,0,$mm+$addmois-1,$jj1_fix,$aa));
            $table2->add_cell(new TABLE_cell_date(date("d/m/Y",strtotime($date_ech))));
            $mm_prec = $mm1_fix;
          }
          else{
            $date_ech = date("Y-m-d", mktime(0,0,0,$mm+$addmois-1,$jj1_fix,$aa));
            $table2->add_cell(new TABLE_cell_date(date("d/m/Y",strtotime($date_ech))));
            $mm_prec = $mm;
          }
          $date1 = date("Y-m-d", mktime(0,0,0,$mm1_fix,$jj1_fix,$aa));
          $date_avan_debut_ech = date_create($date1); //en type object date
          $date_ech1 = date_create($date_ech); //en type object date
          $date2 = date("Y-m-d", mktime(0,0,0,$mm_prec,$jj1_fix,$aa));
          $date_ech_prec = date_create($date2); //en type object date : date echeance precedant
        }

        $table2->set_cell_property("align","left");

        //calcule nombre de jours echus entre echeance
        if ($ech==1){
          $nombre_jours_echu = date_diff($date_ech1,$date_avan_debut_ech);
          $nbre_jours_echus = (int)$nombre_jours_echu->days;
        }
        else{
          $nombre_jours_echu = date_diff($date_ech1,$date_ech_prec);
          $nbre_jours_echus = (int)$nombre_jours_echu->days;
        }

        $tx_interet = $parametre_echeancier["interet"]/100; //calcule taux interet
        $cal_int = ((int)round($tx_interet * $solde_DAT) * $nbre_jours_echus)/$nbre_jours_an; //calcule interet

        $mnt_DAT = 0;
        if ($ech == $nombre_ech){
          $mnt_DAT = $parametre_echeancier["montant"];
        }
        $mnt_cap = $mnt_DAT;
        $mnt_int = $cal_int;
        $mnt_ech = $mnt_cap + $mnt_int;
        $table2->add_cell(new TABLE_cell(afficheMontant ($mnt_cap, false)));
        $table2->add_cell(new TABLE_cell(afficheMontant ($mnt_int, false)));
        $table2->add_cell(new TABLE_cell(afficheMontant ($mnt_ech, false)));
        $table2->set_row_childs_property("align","right");
        $total_cap += $mnt_cap;
        $total_int += $mnt_int;
        $total_ech += $mnt_ech;
        $DATAECH[$ech]["ech"] = $ech;
        $DATAECH[$ech]["date_ech"] = date("d/m/Y",strtotime($date_ech));
        $DATAECH[$ech]["mnt_cap"] = $mnt_cap;
        $DATAECH[$ech]["mnt_int"] = $mnt_int;
        $DATAECH[$ech]["mnt_ech"] = $mnt_ech;
      }
    }
    else{ //Compte versement des interets : Compte lui-meme
      // Affichage échéances
      $DATAECH = array();
      $ech1 = $ech;
      $solde_DAT1 = $solde_DAT; //solde DAT initial
      for ($ech=$ech1;$ech<=$nombre_ech;$ech++){
        $table2->add_cell(new TABLE_cell($ech));
        $table2->set_cell_property("align","center");

        if ($Prod2["freq_calcul_int"] == 1) { //frequence mensuelle
          $date1 = date("Y-m-d", mktime(0,0,0,$mm_fix,$jj_fix,$aa));
          $date_avan_debut_ech = date_create($date1); //en type object date
          $date_ech = date("Y-m-d", mktime(0,0,0,$mm+$ech,$jj_fix,$aa));
          $date_ech1 = date_create($date_ech); //en type object date
          $date_ech2 = date("Y-m-d", mktime(0,0,0,$mm+$ech,$jj_fix,$aa));
          $date_ech3 = date_create($date_ech2); //en type object date
          $ech_prec = $ech-1;
          $date2 = date("Y-m-d", mktime(0,0,0,$mm+$ech_prec,$jj_fix,$aa));
          $date_ech_prec = date_create($date2); //en type object date : date echeance precedant
          $table2->add_cell(new TABLE_cell_date(date("d/m/Y",strtotime($date_ech))));
        }

        if ($Prod2["freq_calcul_int"] == 2 || $Prod2["freq_calcul_int"] == 3 || $Prod2["freq_calcul_int"] == 4){ //frequence trimestrielle, semestrielle et annuelle
          //configuration date echeance
          $date_ech2 = date("d/m/Y",strtotime($date_ech));
          if ($ech==1){
            $date_ech2 = $date_ech;
          }
          if ($date_ech2 != "") { // Rappel : $date = Date du parametre
            $r = explode("/", $date_ech2);
            $jj = (int)1 * $r[0];
            $mm = (int)1 * $r[1];
            $aa = (int)1 * $r[2];
          }
          $addmois = 0;
          if ($Prod2["freq_calcul_int"] == 2) { //frequence trimestrielle
            $addmois = 4;
          }
          if ($Prod2["freq_calcul_int"] == 3) { //frequence semestrielle
            $addmois = 7;
          }
          if ($Prod2["freq_calcul_int"] == 4) { //frequence annuelle
            $addmois = 13;
          }
          if ($ech==1){
            $date_ech = date("Y-m-d", mktime(0,0,0,$mm+$addmois-1,$jj,$aa));
            $table2->add_cell(new TABLE_cell_date(date("d/m/Y",strtotime($date_ech))));
            $mm_prec = $mm1_fix;
            if ($Prod2["freq_calcul_int"] == 3 || $Prod2["freq_calcul_int"] == 4){
              $mm_prec = $mm-1;
            }
          }
          else{
            $date_ech = date("Y-m-d", mktime(0,0,0,$mm+$addmois-1,$jj,$aa));
            $table2->add_cell(new TABLE_cell_date(date("d/m/Y",strtotime($date_ech))));
            $mm_prec = $mm;
          }
          $date1 = date("Y-m-d", mktime(0,0,0,$mm1_fix,$jj1_fix,$aa));
          $date_avan_debut_ech = date_create($date1); //en type object date
          $date_ech1 = date_create($date_ech); //en type object date
          $date_ech2 = date("Y-m-d", mktime(0,0,0,$mm+$addmois-1,$jj1_fix,$aa));
          $date2 = date("Y-m-d", mktime(0,0,0,$mm_prec,$jj1_fix,$aa));
          if ($ech==1){
            $date_ech2 = date("Y-m-d", mktime(0,0,0,$mm+$addmois-1,$jj1_fix,$aa));
            $date2 = date("Y-m-d", mktime(0,0,0,$mm_prec,$jj1_fix,$aa));
          }
          $date_ech3 = date_create($date_ech2); //en type object date
          $date_ech_prec = date_create($date2); //en type object date : date echeance precedant
        }

        $table2->set_cell_property("align","left");

        //calcule nombre de jours echus entre echeance
        if ($ech==1){
          $nombre_jours_echu = date_diff($date_ech1,$date_avan_debut_ech);
          $nbre_jours_echus = (int)$nombre_jours_echu->days;
        }
        else{
          $nombre_jours_echu = date_diff($date_ech3,$date_ech_prec);
          $nbre_jours_echus = (int)$nombre_jours_echu->days;
        }

        $tx_interet = $parametre_echeancier["interet"]/100; //calcule taux interet
        $cal_int = ((int)round($tx_interet * $solde_DAT) * $nbre_jours_echus)/$nbre_jours_an; //calcule interet

        if ($ech == 1){
          $mnt_cap = $solde_DAT;
        }
        //$nombre_jours_echu = date_diff($date_ech3,$date_ech_prec);
        //$nbre_jours_echus = (int)$nombre_jours_echu->days;
        $mnt_int = $cal_int;
        $mnt_ech = $mnt_cap + $mnt_int;
        $table2->add_cell(new TABLE_cell(afficheMontant ($mnt_cap, false)));
        $table2->add_cell(new TABLE_cell(afficheMontant ($mnt_int, false)));
        $table2->add_cell(new TABLE_cell(afficheMontant ($mnt_ech, false)));
        $table2->set_row_childs_property("align","right");
        $DATAECH[$ech]["ech"] = $ech;
        $DATAECH[$ech]["date_ech"] = date("d/m/Y",strtotime($date_ech));
        $DATAECH[$ech]["mnt_cap"] = $mnt_cap;
        $DATAECH[$ech]["mnt_int"] = $mnt_int;
        $DATAECH[$ech]["mnt_ech"] = $mnt_ech;
        $total_cap = $solde_DAT1;
        $total_int += $mnt_int;
        $total_ech = $mnt_ech;
        $mnt_cap += $cal_int;
        $solde_DAT = $mnt_cap; //nouveau solde DAT -> nouveau capital de l'echeance suivant
      }
    }

    //**********************************Affichage totaux***********************************************
    $table2->add_cell(new TABLE_cell(_("Total"),2));
    $table2->set_row_childs_property("align","center");
    $table2->add_cell(new TABLE_cell(afficheMontant ($total_cap,false)));
    $table2->add_cell(new TABLE_cell(afficheMontant ($total_int,false)));
    $table2->add_cell(new TABLE_cell(afficheMontant ($total_ech,false)));
    $table2->set_row_childs_property("align","right");
    $table2->set_row_childs_property("bold");

    //***********************************Donnees pour impression echeancier****************************
    // Collecte des informations pour le header contextuel
    global $adsys;
    $CRIT = array();
    $CRIT[_("Nom du Client")] = getClientName($parametre_echeancier["id_client"]);
    $CRIT[_("Produit Epargne")] =  $SESSION_VARS['produit'][$Prod2['id']]['libel'];
    $CRIT[_("Montant de DAT")] = afficheMontant($parametre_echeancier["montant"], true)." ".$Prod2["devise"];
    $CRIT[_("Terme en mois")] = $parametre_echeancier["duree"];
    $CRIT[_("Taux d'intérêt")] = $parametre_echeancier["interet"]."%"; //affichePourcentage( $parametre_echeancier["interet"]);
    $CRIT[_("Fréquence de calcule des interets")] = adb_gettext($adsys["adsys_freq"][$Prod2["freq_calcul_int"]]);
    $CRIT[_("Mode de calcul des intérêts")] = adb_gettext($adsys["adsys_mode_calcul_int_epargne"][$Prod2["mode_calcul_int"]]);
    $CRIT[_("Compte de versement des interets")] = $adsys["adsys_type_cpt_vers_int"][$parametre_echeancier["type_cpt_vers_int"]];
    $CRIT[_("Date du jour")] = $parametre_echeancier["date"];

    // Collecte des informations pour le body du rapport
    $SESSION_VARS['infos_epargne'][$id_client]["CRIT"] = $CRIT;
    $SESSION_VARS['infos_epargne'][$id_client]["echeances"] = $DATAECH;
    $SESSION_VARS['infos_epargne'][$id_client]["total_cap"] = $total_cap;
    $SESSION_VARS['infos_epargne'][$id_client]["total_int"] = $total_int;
    $SESSION_VARS['infos_epargne'][$id_client]["total_ech"] = $total_ech;
  }

  $formEcheancier = new HTML_GEN2();

  // les boutons ajoutés
  $formEcheancier->addFormButton(1,1,"retour",_("Précédent"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,2,"imprimer",_("Imprimer"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,3,"annuler",_("Retour Menu"),TYPB_SUBMIT);

  // Propriétés des boutons
  if ($global_nom_ecran == "Spe-2") {
    $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN,"Gen-10");
    $formEcheancier->setFormButtonProperties("imprimer", BUTP_PROCHAIN_ECRAN, 'Spe-3');
    $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Spe-4");
  } /*else {
    $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN,"Gen-13");
    $formEcheancier->setFormButtonProperties("imprimer", BUTP_PROCHAIN_ECRAN, 'Sta-3');
    $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Sta-4");
  }*/

  $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $formEcheancier->buildHTML();
  echo $table1->gen_HTML().$table2->gen_HTML();
  echo $formEcheancier->getHTML();

}
/*}}}*/

/*{{{ Spe-3 Sta-3 : Impression échéancier ->  || $prochain_ecran == 'Sta-3'*/
else if ($prochain_ecran == 'Spe-3') {

  if ($prochain_ecran == 'Spe-3')
    $retour_ecran="Gen-10";
  else
    $retour_ecran="Gen-13";

  setMonnaieCourante($SESSION_VARS["devise"]);

  $xml = xml_echeancier_theorique_DAT($SESSION_VARS["infos_epargne"]);

  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'simulation_echeancier_dat.xslt');

  echo get_show_pdf_html($retour_ecran, $fichier_pdf);

  ajout_historique(330, NULL, NULL, $global_nom_login, date("r"), NULL);

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>