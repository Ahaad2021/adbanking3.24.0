<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [115] Rejet dossier de crédit
 *
 * Cette opération comprends les écrans :
 * - Rfd-1 : sélection d'un dossier de crédit
 * - Rfd-2 : rejet d'un dossier de crédit
 * - Rfd-3 : confirmation du rejet
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/message_queue.php';

/*{{{ Rfd-1 : Sélection d'un dossier de crédit */

//*********************************************************

if ($global_nom_ecran == "Rfd-1") {
  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);
  unset($SESSION_VARS['infos_doss']);
  $dossiers = array(); // tableau contenant les infos sur les dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste box des dossiers à afficher
  $i = 1; // clé de la liste box

  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  // Récupération des dossiers individuels réels (dans ad_dcr) à l'état accepté
  $whereCl = " AND is_ligne_credit='f' AND mode_calc_int!=5 AND ((etat=1) OR ((etat=7 AND periodicite <> 6)) OR (etat=14))"; // Le dossier droit être en attente de décision ou en attente de Rééch/Moratoir
  $dossiers_reels = getIdDossier($global_id_client, $whereCl);
  if (is_array($dossiers_reels))
    foreach($dossiers_reels as $id_doss=>$value)
    if ($value['gs_cat'] != 2) { // les dossiers pris en groupe doivent être déboursés via le groupe
      $date = pg2phpDate($value["date_dem"]); //Fonction renvoie  des dates au format jj/mm/aaaa
      $liste[$i] ="n° $id_doss du $date"; //Construit la liste en affichant N° dossier + date
      $dossiers[$i] = $value;

      $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
      $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value["libelle"] . "\";";
      $codejs .= "}\n";
      $i++;
    }

  // SI GS, récupérer les dossiers des membres dans le cas de dossiers multiples
  if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) {
    // Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2
    $whereCl = " WHERE id_membre=$global_id_client and gs_cat=2";
    $dossiers_fictifs = getCreditFictif($whereCl);

    // Récupération des dossiers des membres
    $dossiers_membre = getDossiersMultiplesGS($global_id_client);

    // Ajout dans la liste box, pour chaque dossier fictif du GS, les dossiers réels des membres du GS
    foreach($dossiers_fictifs as $id=>$value) {
      // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
      $infos = '';
      foreach($dossiers_membre as $id_doss=>$val)
      if (($val['is_ligne_credit'] != 't') AND ($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 1 OR ($val['etat'] == 7 AND $val['periodicite'] != 6) OR $val['etat'] == 14)) {
        $date_dem = $date = pg2phpDate($val['date_dem']);
        $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
      }
      if ($infos != '') { // Si au moins on 1 dossier
        $infos .= "du $date_dem";
        $liste[$i] = $infos;
        $dossiers[$i] = $value; // on garde les infos du dossier fictif

        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
        $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $val["libelle"] . "\";";
        $codejs .= "}\n";
        $i++;
      }
    }
  }

  $SESSION_VARS['dossiers'] = $dossiers;
  $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value =='0') {";
  $codejs .= "\n\t\tdocument.ADForm.id_prod.value='';";
  $codejs .= "\n\t}\n";
  $codejs .= "}\ngetInfoDossier();";

  // Création du formulaire
  $Myform = new HTML_GEN2(_("Sélection d'un dossier de crédit"));
  $Myform->addField("id_doss",_("Dossier de crédit"), TYPC_LSB);
  $Myform->addField("id_prod",_("Type produit de crédit"), TYPC_TXT);

  $Myform->setFieldProperties("id_prod", FIELDP_IS_LABEL, true);
  $Myform->setFieldProperties("id_prod", FIELDP_IS_REQUIRED, false);
  $Myform->setFieldProperties("id_prod", FIELDP_WIDTH, 30);

  $Myform->setFieldProperties("id_doss",FIELDP_ADD_CHOICES,$liste);
  $Myform->setFieldProperties("id_doss", FIELDP_JS_EVENT, array("onChange"=>"getInfoDossier();"));
  $Myform->addJS(JSP_FORM, "JS3", $codejs);

  // Javascript : vérifie qu'un dossier est sélectionné
  $JS_1 = "";
  $JS_1.="\t\tif(document.ADForm.HTML_GEN_LSB_id_doss.options[document.ADForm.HTML_GEN_LSB_id_doss.selectedIndex].value==0){ msg+=' - "._("Aucun dossier sélectionné")." .\\n';ADFormValid=false;}\n";
  $Myform->addJS(JSP_BEGIN_CHECK,"testdos",$JS_1);

  // Ordre d'affichage des champs
  $order = array("id_doss","id_prod");

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Retour Menu"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rfd-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
  if (sizeof( $dossiers)<=0) {
    $msg = new HTML_message(_("Rejet dossier de crédit"));
    $msg->setMessage(_("Aucun dossier de crédit correspondant à un de ces états n'a été trouvé pour ce client !"));
    $msg->addButton(BUTTON_OK,"Gen-11");
    $msg->buildHTML();
    echo $msg->HTML_code;
  }
}

/*}}}*/

/*{{{ Rfd-2 : Rejet d'un dossier de crédit */

else if ($global_nom_ecran == "Rfd-2") {
  // Si on vient de Apd-1, on récupère les infos de la BD
  if (strstr($global_nom_ecran_prec,"Rfd-1")) {
    // Récupération des dossiers à approuver
    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
      // Les informations sur le dossier
      $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
      $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
      $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
      $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
      $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
      // Infos dossiers fictifs dans le cas de GS avec dossier unique
      if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 1) {
        $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
        $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);
      }
    }
    elseif($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2 ) { // GS avec dossiers multiples
      // infos dossier fictif
      $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];  // id du dossier fictif (dossier du groupe)
      $whereCond = " WHERE id = $id_doss_fic";
      $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);

      // dossiers réels des membre du GS
      $dossiers_membre = getDossiersMultiplesGS($global_id_client);
      foreach($dossiers_membre as $id_doss=>$val) {
        if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'] and ($val['etat']==1 or $val['etat']==7 or $val['etat']==14)) {
          $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
          $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
        }
      }
    }

    /* Récupération des garanties déjà mobilisées pour ce dossier */
    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$infos_doss) {
      $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] = array();
      $liste_gar = getListeGaranties($id_doss);
      foreach($liste_gar as $key=>$value ) {
        $num = count($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']) + 1; // indice du tableau
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['id_gar'] = $value['id_gar'];
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type'] = $value['type_gar'];
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['valeur'] = recupMontant($value['montant_vente']);
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['devise_vente'] = $value['devise_vente'];
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['etat'] = $value['etat_gar'];

        /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
        if ($value['type_gar'] == 1) /* Garantie numéraire */
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];
        elseif($value['type_gar'] == 2 and isset($value['gar_mat_id_bien'])) { /* garantie matérielle */
          $id_bien = $value['gar_mat_id_bien'];
          $infos_bien = getInfosBien($id_bien);
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $infos_bien['description'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type_bien'] = $infos_bien['type_bien'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['piece_just'] = $infos_bien['piece_just'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['num_client'] = $infos_bien['id_client'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['remarq'] = $infos_bien['remarque'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['gar_mat_id_bien'] = $id_bien;
        }
      } /* Fin foreach garantie */
    } /* Fin foreach infos dossiers */

    // Les informations sur le produit de crédit
    $Produit = getProdInfo(" where id =".$id_prod, $id_doss);
    $SESSION_VARS['infos_prod'] = $Produit[0];

    // Récupérations des utilisateurs. C'est à dire les agents gestionnaires
    $SESSION_VARS['utilisateurs'] = array();
    $utilisateurs = getUtilisateurs();
    foreach($utilisateurs as $id_uti=>$val_uti)
    $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom']." ".$val_uti['prenom'];
    //Tri par ordre alphabétique des utilisateurs
 	  natcasesort($SESSION_VARS['utilisateurs']);
    // Objet demande de crédit
    $SESSION_VARS['obj_dem'] = getObjetsCredit();
    // Source de financement
    $SESSION_VARS['id_bailleur'] = getListBailleur();
  } //fin si on vient de Apd-1

  // Gestion de la devise
  setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);
  $id_prod  = $SESSION_VARS['infos_prod']['id'];

  // Création du formulaire
  $Myform = new HTML_GEN2(_("Rejet d'un dossier de crédit"));

  global $adsys;
  // $includeFields = array("num_cre","id_prod","obj_dem","date_dem","mnt_dem","duree_mois","differe_jours","differe_ech","delai_grac","etat","id_agent_gest","prelev_auto","gar_num","gar_num_encours","gar_mat","date_etat");

  $det_dem = getDetailsObjCredit();

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $Myform->addField("id_doss".$id_doss, _("Numéro de dossier"), TYPC_TXT);
    $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_DEFAULT,$val_doss['id_doss']);

    $Myform->addField("num_cre".$id_doss, _("Numéro de crédit"), TYPC_INT);
    $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_DEFAULT,$val_doss['num_cre']);

    $Myform->addField("id_prod".$id_doss, _("Produit de crédit"), TYPC_LSB);
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_ADD_CHOICES, array("$id_prod"=>$SESSION_VARS['infos_prod']['libel']));
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_DEFAULT, $id_prod);

    $Myform->addField("periodicite".$id_doss, _("Périodicité"), TYPC_TXT);
    $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_type_periodicite"][$SESSION_VARS['infos_prod']['periodicite']]));
    $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_IS_LABEL,true);

    $Myform->addField("id_bailleur".$id_doss, _("Source de financement"), TYPC_LSB);
    $Myform->setFieldProperties("id_bailleur".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['id_bailleur']);
    $Myform->setFieldProperties("id_bailleur".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_bailleur".$id_doss,FIELDP_DEFAULT,$val_doss['id_bailleur']);

    $Myform->addField("obj_dem".$id_doss, _("Objet de la demande"), TYPC_LSB);
    $Myform->setFieldProperties("obj_dem".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
    $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_DEFAULT,$val_doss['obj_dem']);

    if (isDcrDetailObjCreditLsb()) {
      $val_doss['detail_obj_dem_bis'] = $det_dem[$val_doss['detail_obj_dem_bis']]['libel'];

      $Myform->addField("detail_obj_dem_bis" . $id_doss, _("Détail objet demande"), TYPC_TXT);
      $Myform->setFieldProperties("detail_obj_dem_bis" . $id_doss, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("detail_obj_dem_bis" . $id_doss, FIELDP_DEFAULT, $val_doss['detail_obj_dem_bis']);
    } else {
      $val_doss['detail_obj_dem'] = $val_doss['detail_obj_dem'];
      $Myform->addField("detail_obj_dem" . $id_doss, _("Détail objet demande"), TYPC_TXT);
      $Myform->setFieldProperties("detail_obj_dem" . $id_doss, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("detail_obj_dem" . $id_doss, FIELDP_DEFAULT, $val_doss['detail_obj_dem']);
    }

    $Myform->addField("date_dem".$id_doss, _("Date demande"), TYPC_DTE);
    $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_DEFAULT,$val_doss['date_dem']);

    $Myform->addField("cre_date_debloc".$id_doss, _("Date de déblocage"), TYPC_DTE);
    $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_debloc']);

    $Myform->addField("mnt_dem".$id_doss, _("Montant demandé"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_DEFAULT, $val_doss['mnt_dem']);

    //type de durée : en mois ou en semaine
    $type_duree = $SESSION_VARS['infos_prod']['type_duree_credit'];
    $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules
    $Myform->addField("duree_mois".$id_doss, _("Durée en ".$libelle_duree), TYPC_INT);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_DEFAULT,$val_doss['duree_mois']);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,true);

    $Myform->addField("differe_jours".$id_doss, _("Différé en jours"), TYPC_INN);
    $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_DEFAULT,$val_doss['differe_jours']);

    $Myform->addField("differe_ech".$id_doss, _("Différé en échéance"), TYPC_INT);
    $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_DEFAULT,$val_doss['differe_ech']);
    $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_IS_LABEL, true);

    $Myform->addField("delai_grac".$id_doss, _("Délai de grace"), TYPC_INT);
    $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_DEFAULT,$val_doss['delai_grac']);
    $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_IS_LABEL, true);

    $Myform->addField("nb_jr_bloq_cre_avant_ech".$id_doss, _("Nombre de jours pour bloquer le crédit avant échéance"), TYPC_INT);
    $Myform->setFieldProperties("nb_jr_bloq_cre_avant_ech".$id_doss,FIELDP_DEFAULT,$val_doss['nb_jr_bloq_cre_avant_ech']);
    $Myform->setFieldProperties("nb_jr_bloq_cre_avant_ech".$id_doss,FIELDP_IS_LABEL, true);

    $Myform->addField("etat".$id_doss, _("Etat du dossier"), TYPC_TXT);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_etat_dossier_credit"][$val_doss['etat']]));

    $Myform->addField("id_agent_gest".$id_doss, _("Agent gestionnaire"), TYPC_LSB);
    $Myform->setFieldProperties("id_agent_gest".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['utilisateurs']);
    $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_DEFAULT,$val_doss['id_agent_gest']);

    $Myform->addField("prelev_auto".$id_doss, _("Prélèvement automatique"), TYPC_BOL);
    $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_DEFAULT,$val_doss['prelev_auto']);

    $Myform->addField("date_etat".$id_doss, _("Date état du dossier"), TYPC_DTE);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_DEFAULT, date("d/m/Y"));

    if ($val_doss["etat"] == 1) { // en attente de décision
      $Myform->addField("motif".$id_doss, _("Motif"), TYPC_LSB);
      $Myform->setFieldProperties("motif".$id_doss,FIELDP_DEFAULT,$val_doss['motif']);
      $Myform->setFieldProperties("motif".$id_doss,FIELDP_IS_LABEL, true);

      $Myform->addField("details_motif".$id_doss, _("Détails motif"), TYPC_TXT);
      $Myform->setFieldProperties("details_motif".$id_doss,FIELDP_DEFAULT,$val_doss['details_motif']);
      $Myform->setFieldProperties("details_motif".$id_doss,FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("motif".$id_doss,FIELDP_IS_REQUIRED,true);
      $Myform->setFieldProperties("motif".$id_doss,FIELDP_IS_LABEL,false);
      $Myform->setFieldProperties("details_motif".$id_doss,FIELDP_IS_LABEL,false);
      $include = $adsys["adsys_lien_motif_etat_credit"][3];
      $tabMotif=array();
      foreach($include as $cle => $valeur)
      $tabMotif[$valeur]=adb_gettext($adsys["adsys_motif_etat_dossier_credit"][$valeur]);

      $Myform->setFieldProperties("motif".$id_doss,FIELDP_ADD_CHOICES,$tabMotif);
      $Myform->setFieldProperties("motif".$id_doss,FIELDP_DEFAULT,$val_doss['motif']);

        if (isMobileLending()){
            $info_prod = getProdInfo(" WHERE id = ".$SESSION_VARS['infos_doss'][$id_doss]['id_prod']);
            $flag_mob_lending = $info_prod[0]['is_mobile_lending_credit'];
            if ($flag_mob_lending == 't'){
                //ajout du champ du motif d 'approbation
                //$Myform->setFieldProperties("details_motif" . $id_doss, FIELDP_IS_REQUIRED, true);
                $jsMotif = "\nfunction checkMotif() {\n";
                $jsMotif .= "\tif (document.ADForm.details_motif" . $id_doss . ".value.length == 0){\n";
                $jsMotif .= "\t\tADFormValid = false;\n";
                $jsMotif .= "\t\talert('Le motif approbation doit être renseigné!!');\n";
                $jsMotif .= "\t\tdocument.ADForm.details_motif" . $id_doss . ".focus();return false;\n";
                $jsMotif .= "\t}\n";
                $jsMotif .= "\tif (document.ADForm.details_motif" . $id_doss . ".value.length != 0 && document.ADForm.details_motif" . $id_doss . ".value.length < 20 ){\n";
                $jsMotif .= "\t\tADFormValid = false;\n";
                $jsMotif .= "\t\talert('Le motif approbation doit être supérieur à 20 caractères!!');\n";
                $jsMotif .= "\t\tdocument.ADForm.details_motif" . $id_doss . ".focus();return false;\n";
                $jsMotif .= "\t}\n";
                $jsMotif .= "\telse{\n";
                $jsMotif .= "\t\tADFormValid = true;\n";
                $jsMotif .= "\t\tassign('Rfd-3');\n";
                $jsMotif .= "\t\tdocument.ADForm.submit();\n";
                $jsMotif .= "\t}\n";
                $jsMotif .= "\t}\n";
            }


        }

    }
    elseif(in_array($val_doss["etat"], array(7)) ) { // en attente de rééchelonnement
      $Myform->addField("nouv_duree_mois".$id_doss, _("Nouvelle durée"), TYPC_INT);
      $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_DEFAULT,$val_doss['nouv_duree_mois']);
      $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_IS_LABEL, true);
    }

    if (is_array($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']))
      foreach($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] as $key=>$value ) {
      if ($value['type'] == 1)
        $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] += recupMontant($value['valeur']);
      elseif($value['type'] == 2)
      $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] += recupMontant($value['valeur']);

    }

    $Myform->addField("mnt_assurance".$id_doss, _("Montant des assurances"), TYPC_MNT);
    $Myform->addField("mnt_commission".$id_doss, _("Montant commission"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_IS_LABEL,true);
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
      $Myform->addField("gar_num".$id_doss, _("Garantie numéraire attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_DEFAULT,$val_doss['gar_num']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
      $Myform->addField("gar_mat".$id_doss, _("Garantie matérielle attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_DEFAULT,$val_doss['gar_mat']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
      $Myform->addField("gar_tot".$id_doss, _("Garantie totale attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_DEFAULT,$val_doss['gar_mat']+$val_doss['gar_num']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_encours"] > 0) {
      $Myform->addField("gar_encours".$id_doss, _("Garantie numéraire encours"), TYPC_MNT);
      $Myform->setFieldProperties("gar_encours".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_encours".$id_doss,FIELDP_DEFAULT,$val_doss['gar_num_encours']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_mob"] > 0) {
      $Myform->addField("gar_num_mob".$id_doss, _("Garantie numéraire mobilisée"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat_mob"] > 0) {
      $Myform->addField("gar_mat_mob".$id_doss, _("Garantie matérielle mobilisée"), TYPC_MNT);
      $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob']);
    }
    // Date de l'état
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_LONG_NAME, "Date de décision");
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_DEFAULT,date("d/m/Y"));
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_HAS_CALEND,true);

    if ($val_doss["cre_mnt_octr"] > 0) {
      $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_DEFAULT,$val_doss["cre_mnt_octr"] * $SESSION_VARS['infos_prod']['prc_assurance']);
      $comm_values = calculCommissionDeboursement($val_doss["id_prod"],$val_doss["cre_mnt_octr"],$id_doss);
      $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_DEFAULT,$comm_values["mnt_comm"]);
    } else if ($val_doss["mnt_dem"]) {
      $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_DEFAULT,$val_doss["mnt_dem"] * $SESSION_VARS['infos_prod']['prc_assurance']);
      $comm_values = calculCommissionDeboursement($val_doss["id_prod"],$val_doss["mnt_dem"],$id_doss);
      $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_DEFAULT,$comm_values["mnt_comm"]);
    }

    // Ajout d'un lien sur le produit
    $Myform->addLink("id_prod".$id_doss, "produit".$id_doss,_("Détail produit"), "#");
    $Myform->setLinkProperties("produit".$id_doss,LINKP_JS_EVENT,array("onClick"=>"open_produit(".$val_doss["id_prod"].",".$id_doss.");"));
    $Myform->addHTMLExtraCode("espace".$id_doss,"<BR>");
  }

  // Afficahge des dossiers fictifs dans le cas d'un GS avec dossier réel unique et plusieurs dossiers fictifs
  if (is_array($SESSION_VARS['doss_fic']))
    foreach($SESSION_VARS['doss_fic'] as $id_fic=>$val_fic) {

    $val_fic['detail_obj_dem'] = $val_fic['detail_obj_dem'];

    $Myform->addField("membre".$id_fic, _("Membre"), TYPC_TXT);
    $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("membre".$id_fic,FIELDP_DEFAULT,$val_fic['id_membre']." ".getClientName($val_fic['id_membre']));
    $Myform->addField("id_bailleur_fic".$id_fic, _("Source de financement"), TYPC_LSB);
    $Myform->setFieldProperties("id_bailleur_fic".$id_fic, FIELDP_ADD_CHOICES, $SESSION_VARS['id_bailleur']);
    $Myform->setFieldProperties("id_bailleur_fic".$id_fic, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("id_bailleur_fic".$id_fic, FIELDP_DEFAULT, $val_fic['id_bailleur']);
    $Myform->addField("obj_dem_fic".$id_fic, _("Objet demande"), TYPC_LSB);
    $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
    $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_DEFAULT, $val_fic['obj_dem']);
      if (isDcrDetailObjCreditLsb()) {
        $val_fic['detail_obj_dem_bis'] = $det_dem[$val_fic['detail_obj_dem_bis']]['libel'];

        $Myform->addField("detail_obj_dem_bis_fic" . $id_fic, _("Détail demande"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem_bis_fic" . $id_fic, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("detail_obj_dem_bis_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem_bis']);
      } else {
        $Myform->addField("detail_obj_dem_fic" . $id_fic, _("Détail demande"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem']);
      }
    $Myform->addField("mnt_dem_fic".$id_fic, _("Montant demande"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_dem_fic".$id_fic, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_DEFAULT,$val_fic['mnt_dem']);
    $Myform->addHTMLExtraCode("espace_fic".$id_fic,"<BR>");
  }
// Test de la date d'annulation
  $JS_1="";
  $today = date("d/m/Y");
  $JS_1.="\t\tif(isBefore(document.ADForm.HTML_GEN_date_date_etat.value, document.ADForm.HTML_GEN_date_date_dem.value)){ msg+=' - "._("Le date d\'annulation doit être postérieure à la date de demande et antérieure ou égale à la date du jour.")."\\n';ADFormValid=false;}\n";
  $Myform->addJS(JSP_BEGIN_CHECK,"testdateapprob",$JS_1);

  // les boutons ajoutés
     if (isMobileLending()) {
        $info_prod = getProdInfo(" WHERE id = ".$SESSION_VARS['infos_doss'][$id_doss]['id_prod']);
        $flag_mob_lending = $info_prod[0]['is_mobile_lending_credit'];
        if ($flag_mob_lending == 't') {
            $Myform->addJS(JSP_FORM, "jsCheckMotif", $jsMotif);
            $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_BUTTON);
            $Myform->setFormButtonProperties("valider", BUTP_JS_EVENT, array("OnClick" => "checkMotif();"));
        }
        else{
            $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
            $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rfd-3");
        }
    }else{
        $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
        $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rfd-3");
    }
    $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);
  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  //$Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rfd-3");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);


  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Rfd-3 : Confirmation du rejet */
else if ($global_nom_ecran == "Rfd-3") {
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    if ($val_doss["etat"]==1) {
      //Remplissage de $DATA avec les données postées au serveur.
      $DATA[$id_doss]["etat"] = 3;// L'etat du dossier passe à Rejet
      $DATA[$id_doss]["date_etat"] = $_POST['date_etat'.$id_doss]; // Date de passage à l'état rejet
      $DATA[$id_doss]["motif"] = $_POST['motif'.$id_doss]; // Motif de rejet
      $DATA[$id_doss]["details_motif"] = $_POST['details_motif'.$id_doss]; // Détails du motif de rejet
      $DATA[$id_doss]["id_client"] = $val_doss['id_client'];
      $DATA[$id_doss]["last_etat"] = $val_doss['etat'];
      /* Suppression des garanties */
      $DATA_GAR[$id_doss]['DATA_GAR'] = array();
      foreach($val_doss['DATA_GAR'] as $key=>$value) {
        $value['type'] = '';
        array_push($DATA_GAR[$id_doss]['DATA_GAR'],$value);
      }
    }
    elseif(in_array($val_doss["etat"], array(7, 14))) {
      //Rejet d'un Rééch/moratoire
      //Remplissage de $DATA avec les données postées au serveur.
      if($val_doss["etat"]==7) {
        $DATA[$id_doss]["nouv_duree_mois"] = 0;// durée
      }
      $DATA[$id_doss]["etat"] = 5; //Etat du dossier : fonds déboursés
      $DATA[$id_doss]["id_client"] = $val_doss['id_client'];
      $DATA[$id_doss]["last_etat"] = $val_doss['etat'];
    }
  }

  // Rejet du dossier
  $myErr = rejetCredit($DATA, $DATA_GAR);
  if ($myErr->errCode == NO_ERR) {
      $info_prod = getProdInfo(" WHERE id = ".$SESSION_VARS['infos_prod']['id_prod']);
      $flag_mob_lending = $info_prod[0]['is_mobile_lending_credit'];
      $ml_demande_credit = current(getDemandeCredit($SESSION_VARS['infos_doss'][$id_doss]['id_client'], " id_doss = $id_doss"));

      if ($flag_mob_lending == 't'){
          global $dbHandler;
          $db = $dbHandler->openConnection();
          $array_update = array("statut_demande" => 6);
          $array_condi = array("id_client" => $SESSION_VARS['infos_prod']['id_client'], "id_transaction" => $ml_demande_credit['id_transaction']);
          $sql = buildUpdateQuery("ml_demande_credit", $array_update, $array_condi);
          $result = executeDirectQuery($sql);
          if ($result->errCode != NO_ERR){
              $dbHandler->closeConnection(false);
              return new ErrorObj($result->errCode);
          }
          $dbHandler->closeConnection(true);

          // MSQ mobile lending
	        if(isMSQEnabled() && $appli == 'main'){
            envoi_sms_credit_mob_lending($SESSION_VARS['infos_client']['pp_prenom'], $SESSION_VARS['infos_doss'][$id_doss]['id_client'], $SESSION_VARS['infos_client']['id_cpte_base'], $SESSION_VARS['infos_doss'][$id_doss]['id_doss'], $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'], $SESSION_VARS['infos_prod']['tx_interet'], 6);
          }
          // Fin MSQ mobile lending

      }


    $msg = new HTML_message(_("Confirmation rejet du dossier de crédit"));
    $msg->setMessage(_("Les dossiers de crédit sont passés avec succès à l'état rejeté !"));
    $msg->addButton(BUTTON_OK,"Gen-11");
    $msg->buildHTML();
    echo $msg->HTML_code;
  } else {
    $erreur = new HTML_erreur(_("Rejet dossier de crédit"));
    $erreur->setMessage(_("Echec : les dossiers de crédit ne sont pas passés avec succès à l'état rejeté !"));
    $erreur->addButton(BUTTON_OK,"Gen-11");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>