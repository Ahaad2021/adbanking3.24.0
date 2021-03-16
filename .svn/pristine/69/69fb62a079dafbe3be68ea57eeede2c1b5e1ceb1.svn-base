<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [140] Consultation dossier de crédit
 * Cette opération comprends les écrans :
 * - Cdo-1 : sélection d'un dossier de crédit
 * - Cdo-2 : affichage du dossier
 * - Cdo-3 : échéancier théorique
 * - Cdo-4 : suivi du dossier de crédit
 * - Cdo-5 : affichage des garanties mobilisées
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/html/HTML_champs_extras.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/html/suiviCredit.php';
require_once 'lib/misc/divers.php';
require_once 'modules/rapports/xml_echeancier.php';
require_once 'modules/rapports/xslt.php';

/*{{{ Cdo-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "Cdo-1") {

  // Récupération des infos du client
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);
  unset($SESSION_VARS['infos_doss']);
  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  $dossiers = array(); // tableau contenant les infos sur dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste des dossiers à afficher
  $i = 1;

  // Récupération des dossiers individuels dans ad_dcr
  $whereCl = " AND is_ligne_credit='f' AND mode_calc_int!=5";
  $dossiers_reels = getIdDossier($global_id_client, $whereCl);
  if (is_array($dossiers_reels))
    foreach($dossiers_reels as $id_doss=>$value)
    if ($value['gs_cat'] != 2) { // les dossiers pris en groupe doivent être approuvés via le groupe
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

    // Pour chaque dossier fictif du GS, récupération des dossiers réels des membres du GS
    $dossiers_membre = getDossiersMultiplesGS($global_id_client);

    foreach($dossiers_fictifs as $id=>$value) {
      // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
      $infos = '';
      foreach($dossiers_membre as $id_doss=>$val)
      if (($val['is_ligne_credit'] != 't') AND $val['id_dcr_grp_sol'] == $id) {
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
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Cdo-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Cdo-2 : Affichage du dossier */
else if ($global_nom_ecran == "Cdo-2") {

  global $adsys, $global_id_agence;

  $dataAgence = getAgenceDatas($global_id_agence);
  $SESSION_VARS["is_cpte_remb"] =  'f';

  // Si on vient de Apd-1, on récupère les infos de la BD
  if (strstr($global_nom_ecran_prec,"Cdo-1")) {

    // Récupération des dossiers à approuver
    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
      // Les informations sur le dossier
      $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
      $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];
      $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
      $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
      $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
      // Infos dossiers fictifs dans le cas de GS avec dossier unique
      if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 1) {
        $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
        $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);
      } else
        $SESSION_VARS['doss_fic'] = array();
    }
    elseif($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2 ) { // GS avec dossiers multiples
      // id du dossier fictif : id du dossier du groupe
      $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];
      $whereCond = " WHERE id = $id_doss_fic";
      $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);

      // dossiers réels des membre du GS
      $dossiers_membre = getDossiersMultiplesGS($global_id_client);
      foreach($dossiers_membre as $id_doss=>$val) {
        if (($val['is_ligne_credit'] != 't') AND $val['id_dcr_grp_sol'] == $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id']) {
          $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
          $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
        }
      }
    }

    // Récupération des garanties déjà mobilisées
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

        // Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement
        if ($value['type_gar'] == 1) // Garantie numéraire
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];
        elseif($value['type_gar'] == 2 and isset($value['gar_mat_id_bien'])) { // garantie matérielle
          $id_bien = $value['gar_mat_id_bien'];
          $infos_bien = getInfosBien($id_bien);
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $infos_bien['description'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type_bien'] = $infos_bien['type_bien'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['piece_just'] = $infos_bien['piece_just'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['num_client'] = $infos_bien['id_client'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['remarq'] = $infos_bien['remarque'];
          $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['gar_mat_id_bien'] = $id_bien;
        }
      } // Fin foreach garantie
      // recupération des valeurs des champs supplémentaire 
     // champsextras
      if ( !isset($SESSION_VARS['infos_doss'][$id_doss]['champsExtrasValues'])) {
      	  $SESSION_VARS['infos_doss'][$id_doss]['champsExtrasValues'] = getChampsExtrasDCRValues($id_doss);
      }
    } // Fin foreach infos dossiers

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

    $SESSION_VARS['id_prod'] = $id_prod;
  } // Fin si on vient de Cdo-1

  if (!isset($id_prod) && isset($SESSION_VARS['id_prod'])) {
    $id_prod = $SESSION_VARS['id_prod'];
  }

  setMonnaieCourante($SESSION_VARS['infos_prod']["devise"]);

  // Détermination du prochain écran
  $suivi_gar = false;
  $suivi_ech = false;
  $suivi_credit = false;

  // Création du formulaire
  $Myform = new HTML_GEN2(_("Consultation d'un dossier de crédit"));

  // Récuperation des détails objet demande
  $det_dem = getDetailsObjCredit();
  $det_dem2 = getDetailsObjCredit2();

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {

    if ($val_doss["etat"] == 10) { // Le crédit est actuellement en cours de reprise

      $msg = sprintf(_("Le dossier de crédit %s est actuellement en cours de reprise et ne peut être visualisé"),$id_doss);
      $Myform->addHTMLExtraCode("espace".$id_doss,"<b>$msg</b><BR>");

    } else {
    	//affichage de titre avec nom client
    	$nom_cli = getClientName($val_doss['id_client']);
    	$Myform->addHTMLExtraCode("espac_".$id_doss,"<br /><b><p align=center><b>".sprintf(_("Dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");

    	$Myform->addField("id_doss".$id_doss, _("Numéro de dossier"), TYPC_TXT);
      $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_DEFAULT,$val_doss['id_doss']);
      $Myform->addField("id_prod".$id_doss, _("Produit de crédit"), TYPC_LSB);
      $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_ADD_CHOICES, array("$id_prod"=>$SESSION_VARS['infos_prod']['libel']));
      $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_DEFAULT, $id_prod);
      
      // Ajout de liens
      $Myform->addLink("id_prod".$id_doss, "produit".$id_doss,_("Détail produit"), "#");
      $Myform->setLinkProperties("produit".$id_doss,LINKP_JS_EVENT,array("onClick"=>"open_produit(".$id_prod.",".$id_doss.");"));
      $Myform->addField("periodicite".$id_doss, _("Périodicité"), TYPC_INT);
      $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_type_periodicite"][$SESSION_VARS['infos_prod']['periodicite']]));
      $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->addField("etat".$id_doss, _("Etat du dossier"), TYPC_TXT);
      $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("etat".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_etat_dossier_credit"][$val_doss['etat']]));
      $Myform->addField("date_etat".$id_doss, _("Date état du dossier"), TYPC_DTE);
      $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_DEFAULT,$val_doss['date_etat']);
      $Myform->addField("num_cre".$id_doss, _("Numéro de crédit"), TYPC_INT);
      $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_DEFAULT,$val_doss['num_cre']);

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

        $Myform->addField("detail_obj_dem_bis" . $id_doss, _("Détail objet demande 1"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem_bis" . $id_doss, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("detail_obj_dem_bis" . $id_doss, FIELDP_DEFAULT, $val_doss['detail_obj_dem_bis']);
      } else {
        $Myform->addField("detail_obj_dem".$id_doss, _("Détail objet demande 1"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("detail_obj_dem".$id_doss,FIELDP_DEFAULT,$val_doss['detail_obj_dem']);
      }
      $val_doss['detail_obj_dem_2'] = $det_dem2[$val_doss['detail_obj_dem_2']]['libelle'];

      $Myform->addField("detail_obj_dem_2" . $id_doss, _("Détail objet demande 2"), TYPC_TXT);
      $Myform->setFieldProperties("detail_obj_dem_2" . $id_doss, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("detail_obj_dem_2" . $id_doss, FIELDP_DEFAULT, $val_doss['detail_obj_dem_2']);

      $Myform->addField("date_dem".$id_doss, _("Date demande"), TYPC_DTE);
      $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_DEFAULT,$val_doss['date_dem']);
      $Myform->addField("mnt_dem".$id_doss, _("Montant demandé"), TYPC_MNT);
      $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_DEFAULT, $val_doss['mnt_dem']);
      $Myform->addField("cre_mnt_octr".$id_doss, _("Montant octroyé"), TYPC_MNT);
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_DEFAULT,$val_doss['cre_mnt_octr']);
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnFocus"=>"reset($id_doss);"));
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnChange"=>"init($id_doss);"));
      $Myform->addField("cre_date_debloc".$id_doss, _("Date de déblocage"), TYPC_DTE);
      $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_debloc']);
      $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_IS_LABEL, true);
      // type durée : en mois ou en semaine
      $type_duree = $SESSION_VARS['infos_prod']['type_duree_credit'];
      $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules
      $Myform->addField("duree_mois".$id_doss, _("Durée en ".$libelle_duree), TYPC_INT);
      $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_DEFAULT,$val_doss['duree_mois']);
      $Myform->addField("cre_date_approb".$id_doss, _("Date approbation"), TYPC_DTE);
      $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_approb']);
      $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_LABEL, true);
      if (isMobileLending()) {
        $info_prod = getProdInfo(" WHERE id = " . $val_doss['id_prod']);
        $flag_mob_lending = $info_prod[0]['is_mobile_lending_credit'];
        if ($flag_mob_lending == 't') {
          $Myform->addField("details_motifs" . $id_doss, _("Details motif"), TYPC_TXT);
          $Myform->setFieldProperties("details_motifs" . $id_doss, FIELDP_DEFAULT, $val_doss['details_motif']);
          $Myform->setFieldProperties("details_motifs" . $id_doss, FIELDP_IS_LABEL, true);
        }
      }

      $Myform->addField("differe_jours".$id_doss, _("Différé en jours"), TYPC_INN);
      $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_DEFAULT,$val_doss['differe_jours']);

      $Myform->addField("differe_ech".$id_doss, _("Différé en échéance"), TYPC_INT);
        $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_DEFAULT,$val_doss['differe_ech']);
        $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_IS_LABEL, true);

        $Myform->addField("differe_ech_apres_deb".$id_doss, _("Différé en échéance apres déboursement"), TYPC_INT);
        $Myform->setFieldProperties("differe_ech_apres_deb".$id_doss,FIELDP_DEFAULT, $val_doss['diff_ech_apres_deb']);
        $Myform->setFieldProperties("differe_ech_apres_deb".$id_doss,FIELDP_IS_LABEL, true);


      $Myform->addField("delai_grac".$id_doss, _("Délai de grace"), TYPC_INT);
      $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_DEFAULT,$val_doss['delai_grac']);
      $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_IS_LABEL, true);
      $Myform->addField("nb_jr_bloq_cre_avant_ech".$id_doss, _("Nombre de jours pour bloquer le crédit avant échéance"), TYPC_INT);
      $Myform->setFieldProperties("nb_jr_bloq_cre_avant_ech".$id_doss,FIELDP_DEFAULT,$val_doss['nb_jr_bloq_cre_avant_ech']);
      $Myform->setFieldProperties("nb_jr_bloq_cre_avant_ech".$id_doss,FIELDP_IS_LABEL, true);
      $Myform->addField("prelev_auto".$id_doss, _("Prélèvement automatique"), TYPC_BOL);
      $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_DEFAULT,$val_doss['prelev_auto']);
      $Myform->addField("assurance_cre".$id_doss, _("Montant assurance"), TYPC_MNT);
      $Myform->setFieldProperties("assurance_cre".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("assurance_cre".$id_doss,FIELDP_DEFAULT,$val_doss["assurance_cre"]);
      $Myform->addField("id_agent_gest".$id_doss, _("Agent gestionnaire"), TYPC_LSB);
      $Myform->setFieldProperties("id_agent_gest".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['utilisateurs']);
      $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_DEFAULT,$val_doss['id_agent_gest']);
      $Myform->addField("prov_mnt".$id_doss, _("Montant provisionné"), TYPC_MNT);
      $Myform->setFieldProperties("prov_mnt".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("prov_mnt".$id_doss,FIELDP_DEFAULT,$val_doss["prov_mnt"]);
      $Myform->addField("prov_date".$id_doss, _("Date provision"), TYPC_DTE);
      $Myform->setFieldProperties("prov_date".$id_doss,FIELDP_DEFAULT,$val_doss['prov_date']);
      $Myform->setFieldProperties("prov_date".$id_doss,FIELDP_IS_LABEL, true);
      // 3 & 4 : Rejeté ou annulé
      if (($val_doss["etat"] == 3) || ($val_doss["etat"] == 4)) {
        $motif_rejet_annul = $adsys["adsys_lien_motif_etat_credit"][4];
        $Myform->addField("motif".$id_doss, _("Motif"), TYPC_LSB);
        $Myform->setFieldProperties("motif".$id_doss, FIELDP_ADD_CHOICES, $motif_rejet_annul);
        $Myform->setFieldProperties("motif".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("motif".$id_doss,FIELDP_DEFAULT,$val_doss['motif']);
        $Myform->addField("details_motif".$id_doss, _("Détails motif"), TYPC_TXT);
        $Myform->setFieldProperties("details_motif".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("details_motif".$id_doss,FIELDP_DEFAULT,$val_doss['details_motif']);
      }
      // 5 & 6 : Fonds déboursés ou crédit soldé
      if (($val_doss["etat"] == 5) || ($val_doss["etat"] == 6)) {
        $Myform->addField("cre_nbre_reech".$id_doss, _("Nombre de rééchelonnement"), TYPC_INT);
        $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_DEFAULT,$val_doss['cre_nbre_reech']);
        $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_IS_LABEL, true);
        $Myform->addField("cre_retard_etat_max".$id_doss, _("Etat le plus avancé"), TYPC_INT);
        $Myform->setFieldProperties("cre_retard_etat_max".$id_doss,FIELDP_DEFAULT, getLibel("adsys_etat_credits",$val_doss['cre_retard_etat_max']));
        $Myform->setFieldProperties("cre_retard_etat_max".$id_doss,FIELDP_IS_LABEL, true);
        $Myform->addField("cre_retard_etat_max_jour".$id_doss, _("Plus grand nombre jour de retard observé"), TYPC_INT);
        $Myform->setFieldProperties("cre_retard_etat_max_jour".$id_doss,FIELDP_DEFAULT,$val_doss['cre_retard_etat_max_jour']);
        $Myform->setFieldProperties("cre_retard_etat_max_jour".$id_doss,FIELDP_IS_LABEL, true);
      }
      // 7 : Rééchelonnement / Moratoire
      if ($val_doss["etat"] == 7) {
        $Myform->addField("nouv_duree_mois".$id_doss, _("Nouvelle durée"), TYPC_INT);
        $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_DEFAULT,$val_doss['nouv_duree_mois']);
        $Myform->addField("cre_nbre_reech".$id_doss, _("Nombre de rééchelonnement"), TYPC_INT);
        $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_DEFAULT,$val_doss['cre_nbre_reech']);
        $Myform->addField("cre_retard_etat_max".$id_doss, _("Etat le plus avancé"), TYPC_INT);
        $Myform->setFieldProperties("cre_retard_etat_max".$id_doss,FIELDP_DEFAULT,getLibel("adsys_etat_credits",$val_doss['cre_retard_etat_max']));
        $Myform->addField("cre_retard_etat_max_jour".$id_doss, _("Plus grand nombre jour de retard observé"), TYPC_INT);
        $Myform->setFieldProperties("cre_retard_etat_max_jour".$id_doss,FIELDP_DEFAULT,$val_doss['cre_retard_etat_max_jour']);
        $Myform->setFieldProperties("cre_retard_etat_max_jour".$id_doss,FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("cre_retard_etat_max".$id_doss,FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_IS_LABEL, true);
      }
      // 9 : En perte
      if ($val_doss["etat"] == 9) {
        $Myform->addField("cre_nbre_reech".$id_doss, _("Nombre de rééchelonnement"), TYPC_INT);
        $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_DEFAULT,$val_doss['cre_nbre_reech']);
        $Myform->addField("cre_retard_etat_max".$id_doss, _("Etat le plus avancé"), TYPC_INT);
        $Myform->setFieldProperties("cre_retard_etat_max".$id_doss,FIELDP_DEFAULT,getLibel("adsys_etat_credits",$val_doss['cre_retard_etat_max']));
        $Myform->addField("cre_retard_etat_max_jour".$id_doss, _("Plus grand nombre jour de retard observé"), TYPC_INT);
        $Myform->setFieldProperties("cre_retard_etat_max_jour".$id_doss,FIELDP_DEFAULT,$val_doss['cre_retard_etat_max_jour']);
        $Myform->setFieldProperties("cre_retard_etat_max_jour".$id_doss,FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("cre_retard_etat_max".$id_doss,FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_IS_LABEL, true);
      }
      // Fonds déboursés ou soldé ou en perte
      if (($val_doss["etat"] == 5) || ($val_doss["etat"] == 6) || ($val_doss["etat"] == 7) || ($val_doss["etat"] == 9)) {
        if ($val_doss["etat"] != 9) { // dossier pas en perte
          $cpte = getCptescredits($id_doss);
          $cptcredit = getnumcptecomplet($cpte['cre_id_cpte']);
          $Myform->addField("cre_id_cpte".$id_doss, _("compte de crédit"), TYPC_TXT);
          $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_DEFAULT,$cptcredit);
          $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_IS_LABEL,true);

          /********** AT-200 **********/
          if($SESSION_VARS["is_cpte_remb"] == 't') {
              if (!empty($val_doss['cpt_remb'])) {
                  $cpt_remboursement = getAccountDatas($val_doss['cpt_remb']);
                  $Myform->addField("cre_id_cpte_remb" . $id_doss, _("Compte de remboursement"), TYPC_TXT);
                  $Myform->setFieldProperties("cre_id_cpte_remb" . $id_doss, FIELDP_DEFAULT, $cpt_remboursement['num_complet_cpte']);
                  $Myform->setFieldProperties("cre_id_cpte_remb" . $id_doss, FIELDP_IS_LABEL, true);
              }
          }
          /********** AT-200 **********/

          // Récupération de l'épargne nantie du dossier de crédit
          $liste_gar = getListeGaranties($id_doss);
          foreach($liste_gar as $key=>$val ) {

            // la garantie ne doit être ni restituée ni réalisée
            if ($val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
              if ($val['type_gar']==1) { // Garantie numéraire
                $nantie = $val['gar_num_id_cpte_nantie'];
                $CPT_GAR = getAccountDatas($nantie);

                $Myform->addField("gar".$id_doss."_".$val['id_gar'], _("Compte de garantie numéraire"), TYPC_TXT);
                $Myform->setFieldProperties("gar".$id_doss."_".$val['id_gar'], FIELDP_IS_LABEL,true);
                debug($nantie, _("numero de compte"));
                $cptnantie = getnumcptecomplet($nantie);
                debug($nantie, _("numero de compte2"));
                $Myform->setFieldProperties("gar".$id_doss."_".$val['id_gar'],FIELDP_DEFAULT,$cptnantie);
              }
              elseif($val['type_gar'] ==2 ) { // Garantie matérielle
                $BIEN = getInfosBien($val['gar_mat_id_bien']);
                $Myform->addField("gar".$id_doss."_".$val['id_gar'], _("Garantie matérielle"), TYPC_TXT);
                $Myform->setFieldProperties("gar".$id_doss."_".$val['id_gar'], FIELDP_IS_LABEL,true);
                $Myform->setFieldProperties("gar".$id_doss."_".$val['id_gar'], FIELDP_DEFAULT, $BIEN['description']);
              }
            }
          }

          if ($val_doss["cre_nbre_reech"] > 0) {
            $reech_moratoire = getLastRechMorHistorique (145,$val_doss['id_client']);
            $SESSION_VARS['infos_doss'][$id_doss]['montant'] = $reech_moratoire["infos"];
            $Myform->addField("mnt_reech".$id_doss, _("Montant rééchelonné"), TYPC_MNT);
            $Myform->setFieldProperties("mnt_reech".$id_doss,FIELDP_DEFAULT, $reech_moratoire["infos"]);
            $Myform->setFieldProperties("mnt_reech".$id_doss,FIELDP_IS_LABEL,true);
          }
        }

        // Si le crédit est en retard
        if ($val_doss["etat"] == 5 && $val_doss["cre_etat"] > 1) {
          $Myform->addField("suspension_pen".$id_doss, _("Décompte des pénalités"), TYPC_TXT);
          $Myform->setFieldProperties("suspension_pen".$id_doss, FIELDP_IS_LABEL, true);
          if ($val_doss["suspension_pen"] == 't')
            $Myform->setFieldProperties("suspension_pen".$id_doss, FIELDP_DEFAULT, "Suspendu");
          else
            $Myform->setFieldProperties("suspension_pen".$id_doss, FIELDP_DEFAULT, "Actif");
        }
      }

      $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] = 0; // garanties numéraires totales mobilisées
      $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] = 0; // garanties matérilles totales mobilisées
      if (is_array($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']))
        foreach($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] as $key=>$value ) {
        if ($value['type'] == 1)
          $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] += recupMontant($value['valeur']);
        elseif($value['type'] == 2)
        $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] += recupMontant($value['valeur']);
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
        $Myform->addField("gar_num".$id_doss, _("Garantie numéraire attendue"), TYPC_MNT);
        $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['gar_num']);
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
        $Myform->addField("gar_num_encours".$id_doss, _("Garantie numéraire encours"), TYPC_MNT);
        $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['gar_num_encours']);
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
        $Myform->addField("gar_mat".$id_doss, _("Garantie matérielle attendue"), TYPC_MNT);
        $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['gar_mat']);
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
        $Myform->addField("gar_tot".$id_doss, _("Garantie totale attendue"), TYPC_MNT);
        $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['gar_tot']);
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_mob"] > 0) {
        $Myform->addField("gar_num_mob".$id_doss, _("Total garantie numéraire mobilisée"), TYPC_MNT);
        $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob']);
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat_mob"] > 0) {
        $Myform->addField("gar_mat_mob".$id_doss, _("Total garantie matérielle mobilisée"), TYPC_MNT);
        $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob']);
      }
      //Gestipn des champs supplémentaire
	   	$objChampsExtras = new HTML_Champs_Extras ($Myform,'ad_dcr',$id_doss); 
	   	$objChampsExtras->buildChampsExtras($SESSION_VARS['infos_doss'][$id_doss]['champsExtrasValues'],TRUE);
	   	$SESSION_VARS['infos_doss'][$id_doss]['champsExtras']= $objChampsExtras-> getChampsExtras();
	    //

      // Gestion des boutons
      if (in_array($val_doss["etat"], array(1,2,3,4,5,6,7,9,13)))
        $suivi_ech = true;
      if (in_array($val_doss["etat"], array(5,6,7,9,13)))
        $suivi_credit = true;
      if ($val_doss['gar_tot'] > 0 )
        $suivi_gar = true;

      $Myform->addHTMLExtraCode("espace".$id_doss,"<BR>");
 
    
      ajout_historique(140, $val_doss['id_client'], $id_doss, $global_nom_login, date("r"),NULL);
    }
  } // fin parcours des dossiers

  // Afficahge des dossiers fictifs dans le cas d'un GS avec dossier réel unique et plusieurs dossiers fictifs
  foreach($SESSION_VARS['doss_fic'] as $id_fic=>$val_fic) {
    $Myform->addHTMLExtraCode("espace_fic".$id_fic,"<BR>");
    $Myform->addField("membre".$id_fic, _("Membre"), TYPC_TXT);
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

      $Myform->addField("detail_obj_dem_bis_fic" . $id_fic, _("Détail demande 1"), TYPC_TXT);
      $Myform->setFieldProperties("detail_obj_dem_bis_fic" . $id_fic, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("detail_obj_dem_bis_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem_bis']);
    } else {
      $Myform->addField("detail_obj_dem_fic" . $id_fic, _("Détail demande 1"), TYPC_TXT);
      $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem']);
    }
    $val_fic['detail_obj_dem_2'] = $det_dem2[$val_fic['detail_obj_dem_2']]['libelle'];

    $Myform->addField("detail_obj_dem_2_fic" . $id_fic, _("Détail demande 2"), TYPC_TXT);
    $Myform->setFieldProperties("detail_obj_dem_2_fic" . $id_fic, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("detail_obj_dem_2_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem_2']);

    $Myform->addField("mnt_dem_fic".$id_fic, _("Montant demande"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_dem_fic".$id_fic, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_DEFAULT,$val_fic['mnt_dem']);
  }

  // Les boutons ajoutés
  $Myform->addFormButton(1,1,"retour",_("Précédent"),TYPB_SUBMIT);
  if ($suivi_ech) {
    $Myform->addFormButton(1,2,"echeancier",_("Echéancier"),TYPB_SUBMIT);
    $Myform->setFormButtonProperties("echeancier", BUTP_PROCHAIN_ECRAN, "Cdo-3");
  }
  if ($suivi_credit) {
    $Myform->addFormButton(1,3,"suivi",_("Suivi du crédit"),TYPB_SUBMIT);
    $Myform->setFormButtonProperties("suivi", BUTP_PROCHAIN_ECRAN, "Cdo-4");
  }
  if ($suivi_gar) {
    $Myform->addFormButton(1,4,"gar_mobilisees", _("Garanties mobilisées"), TYPB_SUBMIT);
    $Myform->setFormButtonProperties("gar_mobilisees", BUTP_CHECK_FORM, false);
    $Myform->setFormButtonProperties("gar_mobilisees", BUTP_PROCHAIN_ECRAN, "Cdo-5");
    $Myform->addFormButton(1,5,"annuler",_("Retour Menu"),TYPB_SUBMIT);
  } else
    $Myform->addFormButton(1,4,"annuler",_("Retour Menu"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cdo-1");
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cdo-1");
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Cdo-3 : Echéancier théorique */
else if ($global_nom_ecran == "Cdo-3") {
  // id du produit de crédit
  $id_prod = $SESSION_VARS['infos_prod']['id'];

  // Code HTML
  $HTML_code = '';
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    if (($val_doss["etat"] == 1) || ($val_doss["etat"] == 3) || ($val_doss["etat"] == 4)) {
      // Appel de la fonction echéancier théorique
      $echeancier = calcul_echeancier_theorique($id_prod, $val_doss['mnt_dem'], $val_doss['duree_mois'], $val_doss['differe_jours'], $val_doss['differe_ech'], NULL, 1, $id_doss);
      // Appel de l'affichage de l'échéancier
      $parametre["id_client"] = $val_doss["id_client"];
      $parametre["lib_date"] = _("Date de demande");
      $parametre["index"] = '0';
      $parametre["titre"] = _("Echéancier réel de remboursement");
      $parametre["nbre_jour_mois"] = 30;
      $parametre["montant"] = $val_doss["mnt_dem"]; //Utilisé pour les calculs
      $parametre["mnt_reech"] = '0'; //Montant rééchelonnement
      $parametre["mnt_octr"] = $val_doss["mnt_dem"]; //Montant octroyé
      $parametre["garantie"] = $val_doss["garantie"]+ $val_doss["garantie_encours"];
      $parametre["duree"] = $val_doss["duree_mois"];
      $parametre["date"] = pg2phpDate($val_doss["date_dem"]);
      $parametre["id_prod"]= $id_prod;
      //-1 signifie aucun dossier n'est lié à l'échéancier ce qui évite de générer les données à sauvegarder
      $parametre["id_doss"]= -1;
      $parametre["differe_jours"]= $val_doss["differe_jours"];
      $parametre["differe_ech"]= $val_doss["differe_ech"];
      $parametre["EXIST"]=0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
        $SESSION_VARS["CDOPARAM"] = $parametre;
        $SESSION_VARS["CDOECH"] = $echeancier;
        $SESSION_VARS["CDOID"] = $id_doss;
      $HTML_code .= HTML_echeancier($parametre,$echeancier,$id_doss);
    }
    elseif($val_doss["etat"] == 2) { // Etat approuvé
      $echeancier = calcul_echeancier_theorique ($val_doss["id_prod"], $val_doss["cre_mnt_octr"], $val_doss["duree_mois"], $val_doss["differe_jours"], $val_doss["differe_ech"], NULL, 1, $id_doss);
      // Appel de l'affichage de l'échéancier
      $parametre["id_client"] = $val_doss["id_client"];
      $parametre["lib_date"]=_("Date d'approbation");
      $parametre["index"]= '0';
      $parametre["titre"]= _("Echéancier réel de remboursement");
      $parametre["nbre_jour_mois"]= 30;
      $parametre["montant"]= $val_doss["cre_mnt_octr"];
      $parametre["mnt_reech"]= '0'; //Montant rééchelonnement
      $parametre["mnt_octr"]= $val_doss["cre_mnt_octr"]; //Montant octroyé
      $parametre["garantie"]= $val_doss["garantie"] + $val_doss["garantie_encours"];
      $parametre["duree"]= $val_doss["duree_mois"];
      $parametre["date"]= pg2phpDate($val_doss["cre_date_approb"]);
      $parametre["id_prod"]= $val_doss["id_prod"];
      //-1 signifie aucun dossier n'est lié à l'échéancier ce qui évite de générer les données à sauvegarder
      $parametre["id_doss"]= -1;
      $parametre["differe_jours"]= $val_doss["differe_jours"];
      $parametre["differe_ech"]= $val_doss["differe_ech"];
      $parametre["EXIST"]=0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
        $SESSION_VARS["CDOPARAM"] = $parametre;
        $SESSION_VARS["CDOECH"] = $echeancier;
        $SESSION_VARS["CDOID"] = $id_doss;
      $HTML_code .= HTML_echeancier($parametre,$echeancier,$id_doss);
    }
    elseif(($val_doss["etat"] == 5) || ($val_doss["etat"] == 13) || ($val_doss["etat"] == 6) || ($val_doss["etat"] == 9)) {
      // Appel de l'affichage de l'échéancier
      $parametre["id_client"] = $val_doss["id_client"];
      $parametre["lib_date"]=_("Date de déboursement");
      $parametre["index"]= '0';
      $parametre["titre"]= _("Echéancier réel de remboursement");
      $parametre["nbre_jour_mois"]= 30;
      $parametre["mnt_reech"]= '0';
      if ($val_doss["cre_nbre_reech"] <=0 ) { //Pas eu de Rééch/Moratoire
        $parametre["montant"]= $val_doss["cre_mnt_octr"];
        $whereCond = "WHERE (id_doss='$id_doss')";
        $echeancier = getEcheancier($whereCond);
        reset($echeancier);
      }
      if ($val_doss["cre_nbre_reech"]>0) { // Rééch/Moratoire
        //$cap_restant_du = getSoldeCapital($id_doss);
        $infos_reech = getLastRechMorHistorique(145,$val_doss['id_client']); //Renvoie l'historique du dernier  Rééch/Moratoire
        $datereech = $infos_reech["date"]; // Date de rééch/moratoire
        $parametre["montant"] = getSoldeCapital($id_doss);
        $parametre["mnt_reech"] = $infos_reech["infos"]; //Montant rééchelonnement
        $whereCond = "WHERE (id_doss='$id_doss') AND (date(date_ech)>date('$datereech'))"; // Sélection les échéances après Rééch/Moratoire
        $echeancier = getEcheancier($whereCond);
        reset($echeancier);
      }
      $parametre["mnt_octr"] = $val_doss["cre_mnt_octr"]; //Montant octroyé
      $parametre["garantie"] = $val_doss["garantie"] + $val_doss["garantie_encours"];
      $parametre["duree"] = $val_doss["duree_mois"];
      $parametre["date"] = pg2phpDate($val_doss["cre_date_debloc"]);
      $parametre["id_prod"] = $id_prod;
      $parametre["id_doss"] = -1;//$SESSION_VARS["id_doss"];
      $parametre["differe_jours"] = $val_doss["differe_jours"];
      $parametre["differe_ech"] = $val_doss["differe_ech"];
      $parametre["EXIST"]=1; // Vaut 0 si l'échéancier n'est pas stocké dans la BD 1 sinon
      $SESSION_VARS["CDOPARAM"] = $parametre;
      $SESSION_VARS["CDOECH"] = $echeancier;
      $SESSION_VARS["CDOID"] = $id_doss;
      $HTML_code .= HTML_echeancier($parametre,$echeancier,$id_doss);
    }
    elseif($val_doss["etat"] == 7) { // rééch/moratoire
      $differe_jours = 0;
      $differe_ech = 0;
      // Il faut calculer le nouveau montant pour le crédit, il n'est stocké nulle part
      $cap_restant_du = getSoldeCapital($id_doss);
      // Le montant rééchelonné est dans l'historique
      $infos_reech = getLastRechMorHistorique(145,$val_doss['id_client']);
      $mnt_reech = $infos_reech["infos"];

      $echeancier = calcul_echeancier_theorique($val_doss["id_prod"], $cap_restant_du + $mnt_reech, $val_doss["nouv_duree_mois"], $differe_jours, $differe_ech, NULL, 1, $id_doss);

      // Appel de l'affichage de l'échéancier
      if ($val_doss["cre_etat"]==1)
        $parametre["lib_date"]=_("Date de rééchelonnement");
      elseif($val_doss["cre_etat"]==2)
      $parametre["lib_date"]=_("Date de moratoire");

      $parametre["id_client"] = $val_doss["id_client"];
      $parametre["index"]= getRembPartiel($id_doss); // Renvoie l'id_ech de la dernière échéance remboursé partiellement
      $parametre["titre"]= _("Echéancier réel de remboursement");
      $parametre["nbre_jour_mois"]= 30;
      $parametre["montant"]= $val_doss["montant"] ;
      $parametre["montant"]= $cap_restant_du + $mnt_reech;
      $parametre["mnt_reech"]= $mnt_reech;
      $parametre["mnt_octr"]= $val_doss["cre_mnt_octr"]; //Montant octroyé
      $parametre["garantie"]= $val_doss["garantie"] + $val_doss["garantie_encours"];
      $parametre["duree"]= $val_doss["nouv_duree_mois"];  //Nouvelle durée du crédit
      $parametre["date"]= pg2phpDate($val_doss["date_etat"]); //Date de rééchelonnement
      $parametre["id_prod"]= $id_prod;
      $parametre["id_doss"]= -1; // Si id_doss=-1 alors l'echéancier n'est pas sauvegardé
      $parametre["differe_jours"]= $differe_jours;
      $parametre["differe_ech"]= $differe_ech;
      $parametre["EXIST"]=0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
        $SESSION_VARS["CDOPARAM"] = $parametre;
        $SESSION_VARS["CDOECH"] = $echeancier;
        $SESSION_VARS["CDOID"] = $id_doss;
      $HTML_code .= HTML_echeancier($parametre,$echeancier,$id_doss);

    }
  }

  $formEcheancier = new HTML_GEN2();

  // les boutons ajoutés
  $key = array_keys($SESSION_VARS['infos_doss']);
  $formEcheancier->addFormButton(1,1,"retour",_("Précédent"),TYPB_SUBMIT);
    if(intval($SESSION_VARS['infos_doss'][$key[0]]['etat']) > 4) {
        $formEcheancier->addFormButton(1, 2, "imprimmer", _("Impression"), TYPB_SUBMIT);
    }
  $formEcheancier->addFormButton(1,3,"annuler",_("Retour Menu"),TYPB_SUBMIT);

  // Propriétés des boutons
  $formEcheancier->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cdo-2");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    if(intval($SESSION_VARS['infos_doss'][$key[0]]['etat']) > 4){
      $formEcheancier->setFormButtonProperties("imprimmer", BUTP_CHECK_FORM, false);
      $formEcheancier->setFormButtonProperties("imprimmer", BUTP_PROCHAIN_ECRAN, "Cdo-8");
  }

  $formEcheancier->buildHTML();
  echo  $HTML_code;
  echo $formEcheancier->getHTML();
}

/*}}}*/

/*{{{ Cdo-4 : Suivi du dossier de crédit */
else if ($global_nom_ecran == "Cdo-4") {
  $HTML_code = '';
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
   
    $id_doss = $val_doss["id_doss"];
    $whereCond = "WHERE id_doss = $id_doss";
    $echeancier = getEcheancier($whereCond);// L'échéancier
    $reechMorat = getRechMorHistorique (145,$val_doss['id_client'],$val_doss["date_dem"]); //Date demande car date rééch > date demande
    $parametre=array ();
    $cap_du =0;  //Capital dû (Cap remb. + Cap restant dû)
    $int_du =0;  //Intérêt dû
    $gar_du =0;  //Garantie dûe
    $Nbre_Ech =0; //Nbre d'échéance

    $cap_rest =0;  //Capital restant
    $int_rest =0;  //Intérêt restant
    $gar_rest =0;  //Garantie restante
    $Nbre_rest =0; //Nbre d'échéance restant
    $i=0;


    //Echéancier de remboursement
    if (is_array($reechMorat)) {
      reset($reechMorat);
      list($key,$historique) = each($reechMorat);
    }
    while (list($key,$value)=each($echeancier)) {
      $AMJ_ech = pg2phpDatebis($value["date_ech"]);//Tableau aaaa/mm/jj de la date
      $sdEch = gmmktime(0,0,0,$AMJ_ech[0],$AMJ_ech[1],$AMJ_ech[2],1);  //0 mois 1 jour 2 année

      $AMJ_his = pg2phpDatebis($historique["date"]);
      $sdhis = gmmktime(0,0,0,$AMJ_his[0],$AMJ_his[1],$AMJ_his[2],1);  //0 mois 1 jour 2 année

      if (($sdEch > $sdhis) && ($sdhis > 0) && ($sdhis!=$lasthis) && ($val_doss["cre_nbre_reech"] > 0)) { //Réechelonnement /Moratoire
        $lasthis = $sdhis;
        list($key,$historique) = each($reechMorat);
      }


      //Calcul des pénalités attendues
      $pen_remb='0'; //Pénalité remboursé
      $cap_remb='0'; //Capital remboursé pour l'échéance i
      $int_remb='0'; //Intérêt remboursé pour l'échéance i
      $gar_remb='0'; //Garantie remboursée pour l'échéance i
      $som_cap_remb='0';//Capital remboursé
      $som_int_remb='0';//Intérêt remboursé

      $REMB = getRemboursement("WHERE id_doss = ".$id_doss." AND id_ech = ".$value["id_ech"]); //Les remboursements
      // Cas particlier des crédits repris
      if ((sizeof($REMB) == 0) && ($value["remb"] == 't')) {
        $cap_remb="N/A"; // Capital remboursé
        $int_remb="N/A"; // Intérêt remboursé
        $pen_remb="N/A"; // Pénalité remboursée
        $gar_remb="N/A"; // Garantie remboursée
      } else {
        while (list($num_remb, $remb) = each($REMB)) {
          $som_cap_remb += $remb["mnt_remb_cap"]; //Somme des capitaux remboursés
          $som_int_remb += $remb["mnt_remb_int"]; //Somme des intérêts remboursés
          $pen_remb += $remb["mnt_remb_pen"]; //Pénalités remboursées pour l'échéance i
          $cap_remb += $remb["mnt_remb_cap"]; //Capital remboursé pour l'échéance i
          $int_remb += $remb["mnt_remb_int"]; //Intérêts remboursés pour l'échéance i
          $gar_remb += $remb["mnt_remb_gar"]; //Intérêts remboursés pour l'échéance i
        }
      }

      if ($value["remb"]=='f') {
        $cap_rest += $value["solde_cap"];  //Capital restant à payer
        $int_rest += $value["solde_int"];  //Intérêt restant à payer
        $gar_rest += $value["solde_gar"];  //Garantie restant à payer
      }
      $cap_du = $cap_du + $value["mnt_cap"];
      $int_du = $int_du + $value["mnt_int"];
      $gar_du = $gar_du + $value["mnt_gar"];
      $Nbre_Ech = $value["id_ech"];
      if ($value["remb"] =='t')
        $i++;
    }

    $Nbre_rest= $Nbre_Ech-$i; //Nbre d'échéances non clôturé
    $nom_cli = getClientName($val_doss['id_client']);
    //$Myform->addHTMLExtraCode("espac_".$id_doss,"<br /><b><p align=center><b>".sprintf(_("Dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");
    
    $parametre["titre"]= sprintf(_("Suivi du crédit : Dossier N° %s de %s"),$id_doss,$nom_cli);
   // $parametre["titre"]= _("Suivi du crédit")." ".$id_doss;
    $parametre["id_doss"] = $id_doss;
    $parametre["cap_du"] = $cap_du; //$som_cap_remb+$cap_rest;
    $parametre["int_du"] = $int_du; //$som_int_remb+$int_rest;
    $parametre["gar_du"] = $gar_du; //$som_gar_remb+$gar_rest;
    $parametre["Nbre_Ech"] = $Nbre_Ech;
    $parametre["Nbre_rest"] = $Nbre_rest;
    $parametre["cap_rest"] = $cap_rest;
    $parametre["int_rest"] = $int_rest;
    $parametre["gar_rest"] = $gar_rest;
    $parametre["cre_retard_etat_max"] = getLibel("adsys_etat_credits", $val_dos['cre_retard_etat_max']);
    $parametre["cre_retard_etat_max_jour"] = $val_doss['cre_retard_etat_max_jour'];
    $parametre["prov_mnt"]=$val_doss["prov_mnt"];
    if ($val_doss["etat"] == 13 ) {
    	$parametre["cre_mnt_deb"]=$val_doss["cre_mnt_deb"];
    }
    $HTML_code .=  HTML_suiviCredit($parametre,null);
  }

  $formEcheancier = new HTML_GEN2();

  // Permet d'ouvrir la page de détail du remboursement
  $JScode="";
  $JScode .="\nfunction open_remb(id_doss,id_ech)";
  $JScode .="\t{\n";
  $JScode .="\t	// "._("Construction de l'URL : de type")." ./lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_ech=id\n";
  $JScode .="\t\turl = '../lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_doss='+id_doss+'&id_ech='+id_ech;\n";
  $JScode .="\t\tRembWindow = window.open(url, '"._("Produit sélectionné")."', 'alwaysRaised=1,dependent=1,scrollbars,resizable=0,width=650,height=400');\n";
  $JScode .="\t}\n";
  $formEcheancier->addJS(JSP_FORM,"prodF",$JScode);

  // les boutons ajoutés
  $formEcheancier->addFormButton(1,1,"retour",_("Précédent"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,2,"annuler",_("Retour Menu"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1, 3, "pdf", _("Rapport PDF"), TYPB_SUBMIT);
  $formEcheancier->addFormButton(1, 4, "csv", _("Export CSV"), TYPB_SUBMIT);

  // Propriétés des boutons
  $formEcheancier->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cdo-2");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $formEcheancier->setFormButtonProperties("pdf", BUTP_PROCHAIN_ECRAN, "Cdo-6");
  $formEcheancier->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Cdo-7");

  $formEcheancier->buildHTML();
  echo  $HTML_code;
  echo $formEcheancier->getHTML();
}
/*}}}*/

/*{{{ Cdo-5 : Affichage des garanties mobilisées */
else if ($global_nom_ecran == "Cdo-5") {
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Consultation des garanties mobilisées"));

  // Creation d'un tableau contenant toutes les garanties
  $xtHTML = "<br><TABLE align=\"center\">";

  // En-tête tableau :  Type | Description/compte de prélèvement | Valeur |
  $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("Type")."</b></td><td><b>"._("Description/compte de prélèvement")." </b></td><td><b>"._("Valeur")."</b></td><td><b>"._("Etat")."</b></td></tr>";

  // Contenu du tableau
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    foreach($val_doss['DATA_GAR'] as $key=>$value ) {
      if ($value['type'] != '') { /* Si la garantie n'est pas supprimée */
        /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
        if ($value['type'] ==1 and $value['descr_ou_compte'] != '') { /* Garantie numéraire */
          /* Infos du compte de prélèvement des garanties */
          $CPT_PRELEV_GAR = getAccountDatas($value['descr_ou_compte']);
          $origine  = $CPT_PRELEV_GAR["num_complet_cpte"]." ".$CPT_PRELEV_GAR['intitule_compte'] ;

          $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td>".adb_gettext($adsys["adsys_type_garantie"][$value['type']])."</td>";
          $xtHTML .= "<td>".$origine."</td>";
          $xtHTML .= "<td>".afficheMontant($value['valeur'])."</td>";
          $xtHTML .= "<td>".adb_gettext($adsys["adsys_etat_gar"][$value['etat']])."</td></tr>";

        }
        elseif($value['type'] == 2) { /* garantie matérielle */
          /* Description du bien */
          $origine = $value['descr_ou_compte'];

          $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td>".adb_gettext($adsys["adsys_type_garantie"][$value['type']])."</td>";
          $xtHTML .= "<td>".$origine."</td>";
          $xtHTML .= "<td>".afficheMontant($value['valeur'])."</td>";
          $xtHTML .= "<td>".adb_gettext($adsys["adsys_etat_gar"][$value['etat']])."</td></tr>";
        }
      }
    }
  }

  $xtHTML .= "</table><br><br>";
  $Myform->addHTMLExtraCode ("garanties", $xtHTML);
  $Myform->addFormButton(1,1,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Cdo-2');

  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Ech-4 : Impression du suivi de remboursement */
else if  ($global_nom_ecran == 'Cdo-6' || $global_nom_ecran == 'Cdo-7') {
  require_once 'modules/rapports/xml_echeancier.php';
  require_once 'modules/rapports/xslt.php';
  require_once 'lib/misc/csv.php';

  $xml = xml_echeancier($SESSION_VARS['infos_doss']);

  if ($global_nom_ecran == 'Cdo-6') {
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'echeancier.xslt');
    echo get_show_pdf_html("Gen-11", $fichier_pdf);
  } else if ($global_nom_ecran == 'Cdo-7') {
    //Génération du CSV grâce à XALAN
    //$xml = xml_echeancier($SESSION_VARS['infos_doss'], true);
    $csv_file = xml_2_csv($xml, 'echeancier.xslt');

    //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
    echo getShowCSVHTML("Gen-11", $csv_file);
  }

  ajout_historique(350, NULL, NULL, $global_nom_login, date("r"), NULL);
}
/*}}}*/
/*{{{ Cdo-9 : Impression échéancier*/
else if ($global_nom_ecran == "Cdo-8") {
    $id_doss = (($SESSION_VARS["CDOPARAM"]["id_doss"] == -1) ? $SESSION_VARS["CDOID"] : $SESSION_VARS["CDOPARAM"]["id_doss"]);
    $produit = getProdInfo(" where id =" . $SESSION_VARS["CDOPARAM"]["id_prod"], $id_doss);
    $arr = array("id_doss" => $id_doss, "param" =>$SESSION_VARS["CDOPARAM"], "echeance" => $SESSION_VARS["CDOECH"],
        "produit" => $produit);
    $xml = xml_credit_imprim($arr);
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'impression_echeance.xslt');
//    print_rn($xml);
    echo get_show_pdf_html("Cdo-1", $fichier_pdf);
}
else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>
