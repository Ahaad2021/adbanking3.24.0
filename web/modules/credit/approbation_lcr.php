<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [601] Approbation dossier de crédit
 * Cette opération comprends les écrans :
 * - LApd-1 : sélection d'un dossier de crédit
 * - LApd-2 : approbation d'un dossier de crédit
 * - LApd-3 : affichage de l'échéancier
 * - LApd-4 : blocage des garanties numéraires
 * - LApd-5 : confirmation approbation d'un dossier de crédit
 * - LApd-6 : affichage des garanties
 * - LApd-7 : ajout de garanties
 * - LApd-8 : modification de garanties
 * - LApd-9 : suppression de garanties
 * - LApd-10 : confirmation ajout, modification ou suppression de garanties
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/misc/divers.php';

/*{{{ LApd-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "LApd-1") {
  unset($SESSION_VARS['infos_doss']);
  // Récupération des infos du client
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);

  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  $dossiers = array(); // tableau contenant les infos sur dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste des dossiers à afficher
  $i = 1;

  // Récupération des dossiers individuels dans ad_dcr en attente de décision ou en attente de Rééch/Moratoire
  $whereCl=" AND is_ligne_credit='t' AND mode_calc_int=5 AND (etat=1)";
  $dossiers_reels = getIdDossier($global_id_client,$whereCl);
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
      if (($val['is_ligne_credit'] == 't') AND ($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 1)) {
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
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "LApd-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ LApd-2 : Approbation d'un dossier de crédit */
else if ($global_nom_ecran == "LApd-2") {
  global $adsys;
  // Si on vient de LApd-1, on récupère les infos de la BD
  if (strstr($global_nom_ecran_prec,"LApd-1")) {

    // Récupération des dossiers à approuver
    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
      // Les informations sur le dossier
      $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
      $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
      $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
      $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
      $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
      $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
      // Infos dossiers fictifs dans le cas de GS avec dossier unique
      if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 1) {
        $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
        $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] = getCreditFictif($whereCond);
      }
    }
    elseif($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2 ) { // GS avec dossiers multiples
      // id du dossier fictif : id du dossier du groupe
      $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];

      // dossiers réels des membre du GS
      $dossiers_membre = getDossiersMultiplesGS($global_id_client);
      foreach($dossiers_membre as $id_doss=>$val) {
        if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'] and ($val['etat']==1 or $val['etat']==7)) {
          $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
          $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
          $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
        }
      }
    }

    /* Vérificateur de l'état des garanties  */
    $gar_pretes = true;

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

        /* Les garanties doivent être à l'état 'Prête' ou mobilisé  au moment de l'approbation  */
        if ($value['etat_gar'] !=2  and $value['etat_gar'] != 3)
          $gar_pretes = false;

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

    /* Toutes les garanties doivent être à l'état 'Prête' ou 'Mobilisé' au moment du déboursement  */
    if ( $gar_pretes == false) {
      $erreur = new HTML_erreur(_("Approbation de dossier de crédit"));
      $msg = _("Impossible d'approuver le dossier de crédit. Les garanties mobilisées ne sont pas toutes prêtes");
      $erreur->setMessage($msg);
      $erreur->addButton(BUTTON_OK,"Lcr-1");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      exit();
    }

    // Les informations sur le produit de crédit
    $Produit = getProdInfo(" where mode_calc_int=5 AND id =".$id_prod, $id_doss);
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
  } //fin si on vient de LApd-1

  //on revient de LApd-6 aprés consultation des garanties
  if (strstr($global_nom_ecran_prec,"LApd-6")) {
    $id_doss = $SESSION_VARS['id_doss'];
    debug($id_doss  ,"id_doss "._("venant de ecran 6"));
  }
  /* Récupération des garanties déjà mobilisées pour ce dossier */
  $SESSION_VARS['DATA_GAR'] = array();
  $liste_gar = getListeGaranties($id_doss);
  //recuperation de la precision de la devise du produit de credit
	$devise_prod=$SESSION_VARS['infos_prod']['devise'];
  $DEV = getInfoDevise($devise_prod);// recuperation d'info sur la devise'
  $precision_devise=pow(10,$DEV["precision"]);

  foreach($liste_gar as $key=>$value ) {
    /* Mémorisation des garanties */
    $num = count($SESSION_VARS['DATA_GAR']) +1;
    $SESSION_VARS['DATA_GAR'][$num]['id_gar'] = $value['id_gar'];
    $SESSION_VARS['DATA_GAR'][$num]['type'] = $value['type_gar'];
    $SESSION_VARS['DATA_GAR'][$num]['valeur'] = recupMontant($value['montant_vente']);
    $SESSION_VARS['DATA_GAR'][$num]['devise_vente'] = $value['devise_vente'];
    $SESSION_VARS['DATA_GAR'][$num]['etat'] = $value['etat_gar'];

    /* Les garanties doivent être à l'état 'Prête' ou mobilisé sauf les garanties numéraires encours  */
    if ($value['etat_gar'] !=2  and $value['etat_gar'] != 3 and $value['type_gar'] != 1 and $value['gar_num_id_cpte_prelev'] != NULL)
      $gar_pretes = false;

    /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
    if ($value['type_gar'] == 1) /* Garantie numéraire */
      $SESSION_VARS['DATA_GAR'][$num]['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];
    elseif($value['type_gar'] == 2 and isset($value['gar_mat_id_bien'])) { /* garantie matérielle */
      $id_bien = $value['gar_mat_id_bien'];
      $infos_bien = getInfosBien($id_bien);
      $SESSION_VARS['DATA_GAR'][$num]['descr_ou_compte'] = $infos_bien['description'];
      $SESSION_VARS['DATA_GAR'][$num]['type_bien'] = $infos_bien['type_bien'];
      $SESSION_VARS['DATA_GAR'][$num]['piece_just'] = $infos_bien['piece_just'];
      $SESSION_VARS['DATA_GAR'][$num]['num_client'] = $infos_bien['id_client'];
      $SESSION_VARS['DATA_GAR'][$num]['remarq'] = $infos_bien['remarque'];
      $SESSION_VARS['DATA_GAR'][$num]['gar_mat_id_bien'] = $id_bien;
    }
  } /* Fin foreach */

  // Gestion de la devise
  setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);
  $id_prod  = $SESSION_VARS['infos_prod']['id'];

  // Création du formulaire
  $js_date_approb = '';
  $js_gar = '';
  $js_check = ""; // Javascript de validation de la saisie
  $can_mob_gar = false ; // On ne peut mobiliser des garanties que si le dossier est en attente de décision
  $Myform = new HTML_GEN2(_("Approbation dossier de crédit"));

  // Récuperation des détails objet demande
  $det_dem = getDetailsObjCredit();

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $nom_cli = getClientName($val_doss['id_client']);

    if (isDcrDetailObjCreditLsb()) {
      $val_doss['detail_obj_dem_bis'] = $det_dem[$val_doss['detail_obj_dem_bis']]['libel'];
    }

    if ($val_doss['etat'] == 1) {
      $Myform->addHTMLExtraCode("espace".$id_doss,"<br/><b><p align=\"center\"><b> ".sprintf(_("Approbation du dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");
      if ($val_doss['gar_num'] > 0 or $val_doss['gar_mat'] > 0)
        $can_mob_gar = true;
    }

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
    /*
    $Myform->addField("periodicite".$id_doss, _("Périodicité"), TYPC_INT);
    $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_type_periodicite"][$SESSION_VARS['infos_prod']['periodicite']]));
    $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_IS_LABEL,true);
    */
    $Myform->addField("obj_dem".$id_doss, _("Objet de la demande"), TYPC_LSB);
    $Myform->setFieldProperties("obj_dem".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
    $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_DEFAULT,$val_doss['obj_dem']);
    if (isDcrDetailObjCreditLsb()) {
      $Myform->addField("detail_obj_dem_bis" . $id_doss, _("Détail objet demande"), TYPC_TXT);
      $Myform->setFieldProperties("detail_obj_dem_bis" . $id_doss, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("detail_obj_dem_bis" . $id_doss, FIELDP_DEFAULT, $val_doss['detail_obj_dem_bis']);
    } else {
      $Myform->addField("detail_obj_dem".$id_doss, _("Détail objet demande"), TYPC_TXT);
      $Myform->setFieldProperties("detail_obj_dem".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("detail_obj_dem".$id_doss,FIELDP_DEFAULT,$val_doss['detail_obj_dem']);
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
    $Myform->addField("num_cre".$id_doss, _("Numéro de crédit"), TYPC_INT);
    $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_DEFAULT,$val_doss['num_cre']);
    $Myform->addField("cpt_liaison".$id_doss, _("Compte de liaison"), TYPC_TXT);
    $Myform->setFieldProperties("cpt_liaison".$id_doss,FIELDP_IS_LABEL,true);
    $cpt_lie = getAccountDatas($val_doss['cpt_liaison']);
    $Myform->setFieldProperties("cpt_liaison".$id_doss,FIELDP_DEFAULT,$cpt_lie["num_complet_cpte"]." ".$cpt_lie['intitule_compte']);
    $Myform->addField("id_agent_gest".$id_doss, _("Agent gestionnaire"), TYPC_LSB);
    $Myform->setFieldProperties("id_agent_gest".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['utilisateurs']);
    $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_DEFAULT,$val_doss['id_agent_gest']);
    $Myform->addField("etat".$id_doss, _("Etat du dossier"), TYPC_TXT);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_etat_dossier_credit"][$val_doss['etat']]));
    $Myform->addField("date_etat".$id_doss, _("Date état du dossier"), TYPC_DTE);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_DEFAULT,$val_doss['date_etat']);
    $Myform->addField("cre_mnt_octr".$id_doss, _("Montant octroyé"), TYPC_MNT);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_DEFAULT,$val_doss['cre_mnt_octr']);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnFocus"=>"reset($id_doss);"));
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnChange"=>"init($id_doss);"));
    $Myform->addHiddenType("mnt_octr".$id_doss, $val_doss['cre_mnt_octr']);

    if ($SESSION_VARS['infos_doss'][$id_doss]["gs_cat"]==1)
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);
    //type de durée : en mois ou en semaine
    $type_duree = $SESSION_VARS['infos_prod']['type_duree_credit'];
    $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules

    $Myform->addField("duree_mois".$id_doss, sprintf(_("Durée en %s"),$libelle_duree), TYPC_INT);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_DEFAULT,$val_doss['duree_mois']);

    $Myform->addField("tx_interet_lcr".$id_doss, _("Taux d'intérêt"), TYPC_PRC);
    $Myform->setFieldProperties("tx_interet_lcr".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("tx_interet_lcr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("tx_interet_lcr".$id_doss,FIELDP_DEFAULT,($SESSION_VARS['infos_prod']['tx_interet']*100));

    $Myform->addField("taux_frais_lcr".$id_doss, _("Pourcentage taux de frais sur montant non-utilisé"), TYPC_PRC);
    $Myform->setFieldProperties("taux_frais_lcr".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("taux_frais_lcr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("taux_frais_lcr".$id_doss,FIELDP_DEFAULT,($SESSION_VARS['infos_prod']['taux_frais_lcr']*100));

    $Myform->addField("taux_min_frais_lcr".$id_doss, _("Frais minimum par jour pour montant non-utilisé"), TYPC_MNT);
    $Myform->setFieldProperties("taux_min_frais_lcr".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("taux_min_frais_lcr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("taux_min_frais_lcr".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_prod']['taux_min_frais_lcr']);

    $Myform->addField("taux_max_frais_lcr".$id_doss, _("Frais maximum par jour pour montant non-utilisé"), TYPC_MNT);
    $Myform->setFieldProperties("taux_max_frais_lcr".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("taux_max_frais_lcr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("taux_max_frais_lcr".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_prod']['taux_max_frais_lcr']);

    $Myform->addField("ordre_remb_lcr".$id_doss, _("Ordre de remboursement"), TYPC_LSB);
    $obj_dem = $adsys["adsys_ordre_remb_lcr"];
    $Myform->setFieldProperties("ordre_remb_lcr".$id_doss, FIELDP_ADD_CHOICES, $obj_dem);
    $Myform->setFieldProperties("ordre_remb_lcr".$id_doss,FIELDP_HAS_CHOICE_TOUS,false);
    $Myform->setFieldProperties("ordre_remb_lcr".$id_doss,FIELDP_HAS_CHOICE_AUCUN,false);
    $Myform->setFieldProperties("ordre_remb_lcr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("ordre_remb_lcr".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_prod']['ordre_remb_lcr']);

    $Myform->addField("duree_nettoyage_lcr".$id_doss, _("Durée période de nettoyage<br />avant date échéance (0 si aucun)"), TYPC_INT);
    $Myform->setFieldProperties("duree_nettoyage_lcr".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("duree_nettoyage_lcr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("duree_nettoyage_lcr".$id_doss,FIELDP_DEFAULT,$val_doss['duree_nettoyage_lcr']);

    $Myform->addField("deboursement_autorisee_lcr".$id_doss, _("Déboursement autorisée ?"), TYPC_BOL);
    $Myform->setFieldProperties("deboursement_autorisee_lcr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("deboursement_autorisee_lcr".$id_doss,FIELDP_DEFAULT,($val_doss['deboursement_autorisee_lcr']=="t"?true:false));

    $Myform->addField("remb_auto_lcr".$id_doss, _("Remboursement automatique avant échéance ?"), TYPC_BOL);
    $Myform->setFieldProperties("remb_auto_lcr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("remb_auto_lcr".$id_doss,FIELDP_DEFAULT,($val_doss['remb_auto_lcr']=="t"?true:false));
    
    $Myform->addField("cre_date_approb".$id_doss, _("Date approbation"), TYPC_DTE);
    $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_approb']);

    // Test de la date d'approbation
    $js_date_approb.= "\t\tif(isBefore(document.ADForm.HTML_GEN_date_cre_date_approb".$id_doss.".value, 'document.ADForm.HTML_GEN_date_date_dem".$id_doss."value')){ msg+=' - ".sprintf(_("La date d\'approbation pour le dossier %s ne peut être antérieure à la date de demande."),$id_doss)."\\n';ADFormValid=false;}\n";
    $Myform->addField("differe_jours".$id_doss, _("Différé en jours"), TYPC_INN);
    $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_DEFAULT,$val_doss['differe_jours']);
    $Myform->addField("differe_ech".$id_doss, _("Différé en échéance"), TYPC_INT);
    $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_DEFAULT,$val_doss['differe_ech']);
    $Myform->addField("delai_grac".$id_doss, _("Délai de grace"), TYPC_INT);
    $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_DEFAULT,$val_doss['delai_grac']);
    $Myform->addField("mnt_commission".$id_doss, _("Montant commission"), TYPC_MNT);
    //Kheshan ticket pp178
    $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_DEFAULT,$val_doss['mnt_commission']);
    $Myform->addField("mnt_assurance".$id_doss, _("Montant assurance"), TYPC_MNT);
    //Kheshan ticket pp178
    //$Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_DEFAULT,$val_doss["mnt_dem"]*$SESSION_VARS['infos_prod']['prc_assurance']);
    $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_DEFAULT,$val_doss["mnt_assurance"]);

    if(!empty($val_doss['cpt_prelev_frais'])) {
    	$Myform->addField("prelev_auto".$id_doss, _("Prélèvement automatique"), TYPC_BOL);
    	$Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_IS_LABEL,true);
    	$Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_DEFAULT,$val_doss['prelev_auto']);
    	$Myform->addField("cpt_prelev_frais".$id_doss, _("Compte de prélévement des frais"), TYPC_TXT);
    	$Myform->setFieldProperties("cpt_prelev_frais".$id_doss,FIELDP_IS_LABEL,true);
    	$cpt_frais = getAccountDatas($val_doss['cpt_prelev_frais']);
    	$Myform->setFieldProperties("cpt_prelev_frais".$id_doss,FIELDP_DEFAULT,$cpt_frais["num_complet_cpte"]." ".$cpt_frais['intitule_compte']);    	 
    }    
    
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
      $Myform->addField("gar_num".$id_doss, _("Garantie numéraire attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_DEFAULT,$val_doss['gar_num']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
      $Myform->addField("gar_num_encours".$id_doss, _("Garantie numéraire encours"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_DEFAULT,$val_doss['gar_num_encours']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
      $Myform->addField("gar_mat".$id_doss, _("Garantie matérielle attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_DEFAULT,$val_doss['gar_mat']);
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
      $Myform->addField("gar_tot".$id_doss, _("Garantie totale attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_DEFAULT,$val_doss['gar_tot']);
    }

    /* Initialisation des garanties numéraires et matérielles au début et les garanties à numéraires à constituer */
    $mnt_credit = $val_doss['cre_mnt_octr'];

    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
      $js_gar .="\n\tdocument.ADForm.gar_num".$id_doss.".value=Math.round(".$SESSION_VARS['infos_prod']['prc_gar_num']."*parseFloat(".$mnt_credit.")*".$precision_devise.")/".$precision_devise.";";
      $js_gar .="\n\tdocument.ADForm.gar_num".$id_doss.".value =formateMontant(document.ADForm.gar_num".$id_doss.".value);\n";
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
      $js_gar .="\n\tdocument.ADForm.gar_mat".$id_doss.".value=Math.round(".$SESSION_VARS['infos_prod']['prc_gar_mat']."*parseFloat(".$mnt_credit.")*".$precision_devise.")/".$precision_devise.";";
      $js_gar .="\n\tdocument.ADForm.gar_mat".$id_doss.".value =formateMontant(document.ADForm.gar_mat".$id_doss.".value);\n";
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
      $js_gar .="\n\tdocument.ADForm.gar_tot".$id_doss.".value=Math.round(".$SESSION_VARS['infos_prod']['prc_gar_tot']."*parseFloat(".$mnt_credit.")*".$precision_devise.")/".$precision_devise.";";
      $js_gar .="\n\tdocument.ADForm.gar_tot".$id_doss.".value =formateMontant(document.ADForm.gar_tot".$id_doss.".value);\n";
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
      $js_gar .="\n\tdocument.ADForm.gar_num_encours".$id_doss.".value=Math.round(".$SESSION_VARS['infos_prod']['prc_gar_encours']."*parseFloat(".$mnt_credit.")*".$precision_devise.")/".$precision_devise.";";
      $js_gar .="\n\tdocument.ADForm.gar_num_encours".$id_doss.".value =formateMontant(document.ADForm.gar_num_encours".$id_doss.".value);\n";
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

    /*
    if ($SESSION_VARS['infos_doss'][$id_doss]['etat'] == 7) { // Rééchelonnement/ Moratoire
      //type de durée : en mois ou en semaine
      $type_duree = $SESSION_VARS['infos_prod']['type_duree_credit'];
      $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules

      $Myform->addField("nouv_duree_mois".$id_doss, _("Nouvelle durée en ".$libelle_duree), TYPC_INT);
      $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_DEFAULT,$val_doss['nouv_duree_mois']);
      $Myform->addField("cre_id_cpte".$id_doss, _("Compte de crédit"), TYPC_TXT);
      $cpt_cr = getAccountDatas($val_doss['cre_id_cpte']);
      $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_DEFAULT,$cpt_cr["num_complet_cpte"]." ".$cpt_cr['intitule_compte']);
      $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->addField("cre_etat".$id_doss, _("Etat crédit"), TYPC_INT);
      $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_DEFAULT,getLibel("adsys_etat_credits",$val_doss['cre_etat']));
      $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->addField("cre_nbre_reech".$id_doss, _("Nombre de rééchelonnement"), TYPC_INT);
      $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_DEFAULT,$val_doss['cre_nbre_reech']);
      $Myform->addField("nbre_reechelon_auth".$id_doss, _("Nombre maximum de rééchelonnements"), TYPC_INT);
      $Myform->setFieldProperties("nbre_reechelon_auth".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_prod']['nbre_reechelon_auth']);
      $Myform->setFieldProperties("nbre_reechelon_auth".$id_doss,FIELDP_IS_LABEL,true);

      $SESSION_VARS['infos_doss'][$id_doss]['cap'] = 0;
      $SESSION_VARS['infos_doss'][$id_doss]['int'] = 0; //Somme des intérêts
      $SESSION_VARS['infos_doss'][$id_doss]['pen'] = 0; //Somme des pénalités
      $dateRechMor = date("d/m/Y");
      $whereCond = "WHERE (remb='f') AND (id_doss='".$id_doss."')";
      $lastEch = getEcheancier($whereCond);
      if (is_array($lastEch))
        while (list($key,$value)=each($lastEch)) {
          $SESSION_VARS['infos_doss'][$id_doss]['cap'] += $value["solde_cap"];
          $SESSION_VARS['infos_doss'][$id_doss]['int'] += $value["solde_int"];
          $SESSION_VARS['infos_doss'][$id_doss]['pen'] += $value["solde_pen"];
        }
      // FIXME : A-t-on besoin de cet appel à getLastRechMorHistorique si on appelle de nouveua getMontantExigible ?
      $reech_moratoire = getLastRechMorHistorique (145,$val_doss['id_client']);
      $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'] = $reech_moratoire['infos']; //Le montant rééchelonné
      $SESSION_VARS['infos_doss'][$id_doss]['date_reech']= $reech_moratoire["date"]; //La date de mise en attente de rééchélonnement
      $MNT_EXIG = getMontantExigible($id_doss);
      // Nouveau capital = capital + montant rééchelonné
      $SESSION_VARS['infos_doss'][$id_doss]['nouveau_cap'] =  $SESSION_VARS['infos_doss'][$id_doss]['cap'] + $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'];
      $Myform->addField("mnt_cap".$id_doss, _("Montant dû en capital"), TYPC_MNT);
      $Myform->addField("mnt_reech".$id_doss,_("Montant rééchelonné"), TYPC_MNT);
      $Myform->addField("nouveau_cap".$id_doss,_("Nouveau capital"), TYPC_MNT);
      $Myform->addField("date_reech".$id_doss, "Date de demande de Rééchelonnement/Moratoire", TYPC_DTE);
      $Myform->setFieldProperties("mnt_cap".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['cap']);
      $Myform->setFieldProperties("mnt_reech".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech']);
      $Myform->setFieldProperties("nouveau_cap".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['nouveau_cap']);
      $Myform->setFieldProperties("date_reech".$id_doss,FIELDP_DEFAULT,  $SESSION_VARS['infos_doss'][$id_doss]['date_etat']);
      $Myform->setFieldProperties("mnt_cap".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("mnt_reech".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("nouveau_cap".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("date_reech".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['cre_nbre_reech']+1);
      $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_IS_LABEL,true);


    } // fin si rééchelonnement
    */

    // Afficahge des dossiers fictifs dans le cas d'un GS avec dossier réel unique
    if ($SESSION_VARS['infos_doss'][$id_doss]['gs_cat'] == 1) {
      $js_mnt_octr = "function calculeMontant() {"; // function de calcule du montant octroyé
      $js_mnt_octr .= "var tot_mnt_octr = 0;\n";

      foreach($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic=>$val_fic) {
        $Myform->addHTMLExtraCode("espace_fic".$id_fic,"<BR>");
        $Myform->addField("membre".$id_fic, _("Membre"), TYPC_TXT);
        $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("membre".$id_fic,FIELDP_DEFAULT,$val_fic['id_membre']." ".getClientName($val_fic['id_membre']));
        $Myform->addField("obj_dem_fic".$id_fic, _("Objet demande"), TYPC_LSB);
        $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
        $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_DEFAULT, $val_fic['obj_dem']);
        if (isDcrDetailObjCreditLsb()) {
          $Myform->setFieldProperties("obj_dem_fic" . $id_fic, FIELDP_JS_EVENT, array("onchange" => "setDetailObj$id_fic();"));
          $Myform->addField("detail_obj_dem_bis_fic" . $id_fic, _("Détail demande"), TYPC_LSB);
          $Myform->setFieldProperties("detail_obj_dem_bis_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem_bis']);
        } else {
          $Myform->addField("detail_obj_dem_fic" . $id_fic, _("Détail demande"), TYPC_TXT);
          $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_IS_REQUIRED, true);
          $Myform->setFieldProperties("detail_obj_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem']);
        }
        $Myform->addField("mnt_dem_fic".$id_fic, _("Montant demande"), TYPC_MNT);
        $Myform->setFieldProperties("mnt_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_DEFAULT,$val_fic['mnt_dem']);
        $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_JS_EVENT,array("OnChange"=>"calculeMontant();"));

        $js_mnt_octr .= "tot_mnt_octr = tot_mnt_octr + recupMontant(document.ADForm.mnt_dem_fic".$id_fic.".value);\n";

        if (isDcrDetailObjCreditLsb()) {
          $js_2 .= "function lookup( name , arr)
            {
                var arrret=[]
                for(var i = 0, len = arr.length; i < len; i++)
                {

                    if( arr[ i ].value[0] == name )
                    {
                    arrret[ arr[ i ].key]=arr[ i ].value[1];

                    }
                }
                if (arrret.length > 0){
                  return arrret;
                  }
                  else {
                return false;} \n

            };\n ";

          $js_2 .= "function setDetailObj$id_fic(){ \n var myArray = [\n";


          foreach ($det_dem as $key => $value) {

            $js_2 .= "{ key: $key, value: ['" . $value['id_obj'] . "','" . $value['libel'] . "'] },";
          }

          $js_2 .= "];\n ";

          $js_2 .= " if( lookup(document.ADForm.HTML_GEN_LSB_obj_dem_fic$id_fic.value, myArray ) != false ) { \n";
          $js_2 .= "     var select = document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic;
                      select.options.length=0;";
          $js_2 .= "    var det_cred=(lookup(document.ADForm.HTML_GEN_LSB_obj_dem_fic$id_fic.value, myArray ));\n
                     document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.length] = new Option(\"[Aucun]\" ,0, false, false);
                     for(var i in det_cred)
                     {
                        document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.length] = new Option(det_cred[i] ,i, false, false);
                    }
                }
             else
             {
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.options.length=0;
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
          $js_2 .= "}\n";

          $js_onload .= "\n setDetailObj$id_fic();document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis_fic$id_fic.value = '".$val_fic['detail_obj_dem_bis']."';";
        }
      }

      if (isDcrDetailObjCreditLsb()) {
        $js_2 .= "\nwindow.onload = function() {".$js_onload."}\n";

        $Myform->addJS(JSP_FORM, "det_obj_gs", $js_2);
      }

      $js_mnt_octr .= "document.ADForm.cre_mnt_octr".$id_doss.".value = formateMontant(tot_mnt_octr);\n";
      $js_mnt_octr .= "document.ADForm.mnt_octr".$id_doss.".value = formateMontant(tot_mnt_octr);\n";
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
        $js_mnt_octr .="\n\tdocument.ADForm.gar_num".$id_doss.".value =formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_num']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
        $js_mnt_octr .="\n\tdocument.ADForm.gar_mat".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_mat']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
        $js_mnt_octr .="\n\tdocument.ADForm.gar_tot".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_tot']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
        $js_mnt_octr .="\n\tdocument.ADForm.gar_num_encours".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_encours']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
      }
      $js_mnt_octr .= "}\n";
    }

    // Contrôle Javascript
    // Vérifier que le montant totat mobilisé est supérieur ou égal au montant attendu
    if ($SESSION_VARS['infos_doss'][$id_doss]['gar_tot'] > 0) {
      $gar_num_mob = "document.ADForm.gar_num_mob".$id_doss;
      $gar_mat_mob = "document.ADForm.gar_mat_mob".$id_doss;
      $gar_tot = "document.ADForm.gar_tot".$id_doss;
      if ($SESSION_VARS['infos_doss'][$id_doss]['gar_num'] > 0) {
        $gar_num = "document.ADForm.gar_num".$id_doss;
        // Vérifer que les garanties numéraires mobilisées sont supérieues aux garanties numéraires attendues
        $js_check .= "if (recupMontant($gar_num.value) > recupMontant($gar_num_mob.value)) {\n";
        $js_check .= "\tmsg += '- ".sprintf(_("Les garanties matérielles mobilisées par le dossier %s sont insuffisantes"),$id_doss)."\\n';\n";
        $js_check .= "\tADFormValid = false;\n";
        $js_check .= "}\n";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]['gar_mat'] > 0) {
        $gar_mat = "document.ADForm.gar_mat".$id_doss;
        // Vérifer que les garanties matérielle mobilisées sont supérieues aux garanties matérielle attendues
        $js_check .= "if (recupMontant($gar_mat.value) > recupMontant($gar_mat_mob.value)) {\n";
        $js_check .= "\tmsg += '- ".sprintf(_("Les garanties matérielles mobilisées par le dossier %s sont insuffisantes"),$id_doss)."\\n';\n";
        $js_check .= "\tADFormValid = false;\n";
        $js_check .= "}\n";
      }
      $js_check .= "gar_tot_mob = 0;\n";
      if ($SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] > 0) {
        $js_check .= "gar_tot_mob += recupMontant($gar_num_mob.value);\n";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] > 0) {
        $js_check .= "gar_tot_mob += recupMontant($gar_mat_mob.value);\n";
      }
      $js_check .= "if (recupMontant($gar_tot.value) > gar_tot_mob) {\n";
      $js_check .= "\tmsg += '- ".sprintf(_("Le montant total des garanties numéraires et matérielles mobilisées par le dossier %s est insuffisant"),$id_doss)."\\n';\n";
      $js_check .= "\tADFormValid = false;\n";
      $js_check .= "}\n";
    }
    //Contrôle de la date de déblocage
    if($SESSION_VARS['infos_doss'][$id_doss]["cre_date_debloc"] != "" && $SESSION_VARS['infos_doss'][$id_doss]["cre_date_debloc"] != NULL && $SESSION_VARS['infos_doss'][$id_doss]['etat'] != 7){
      $js_check.="if(('".$SESSION_VARS['infos_doss'][$id_doss]["cre_date_debloc"]."') <  ('".php2pg(date("d/m/Y"))."'))
               {
                 msg += '- ".sprintf(_("La date de déboursement du dossier %s est dépassée, Veuillez Contacter l\'agent de crédit"),$id_doss)."\\n';
                 ADFormValid = false;
               }\t";
    }
    // Vérifier que le montant à octroyer est conforme aux paramètres du produit de crédit
    $js_check .="\tif(parseFloat(".$SESSION_VARS['infos_prod']['mnt_max'].")>0){ \n";
    $js_check .="\t\tif((parseFloat(recupMontant(document.ADForm.cre_mnt_octr".$id_doss.".value)) < parseFloat(".$SESSION_VARS['infos_prod']['mnt_min'].")) || (parseFloat(recupMontant(document.ADForm.cre_mnt_octr".$id_doss.".value)) > parseFloat(".$SESSION_VARS['infos_prod']['mnt_max']."))) {  msg+='- ".sprintf(_("Le montant demandé pour le dossier %s doit être compris entre %s et %s comme défini dans le produit."),$id_doss,afficheMontant($SESSION_VARS['infos_prod']['mnt_min']),afficheMontant($SESSION_VARS['infos_prod']['mnt_max']))."';ADFormValid=false;;}\n";
    $js_check .="\t}\n";

    $js_check .="\telse if( parseFloat(recupMontant(document.ADForm.cre_mnt_octr".$id_doss.".value)) < parseFloat(".$SESSION_VARS['infos_prod']['mnt_min'].")) { msg+='- ".sprintf(_("Le montant demandé doit être au moins égal à %s comme défini dans le produit"),afficheMontant($SESSION_VARS['infos_prod']['mnt_min']))."'; ADFormValid=false;;}\n";

    // validation montant approuvé
    $js_check .="\tif(parseFloat(recupMontant(document.ADForm.cre_mnt_octr".$id_doss.".value)) > parseInt(recupMontant(document.ADForm.mnt_dem".$id_doss.".value))) { msg+='- "._("Le montant approuvé doit être au plus égal au montant demandé")."'; ADFormValid=false;}\n";


    // Vérification de la durée en mois
    $js_check .="\tif(parseInt(".$SESSION_VARS['infos_prod']['duree_max_mois'].")>0){\n";
    $js_check .="\t\tif((parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$SESSION_VARS['infos_prod']['duree_min_mois'].")) || (parseInt(document.ADForm.duree_mois".$id_doss.".value) > parseInt(".$SESSION_VARS['infos_prod']['duree_max_mois']."))) { msg+=' - ".sprintf(_("La durée du crédit doit être comprise entre %s et %s"),$SESSION_VARS['infos_prod']['duree_min_mois'],$SESSION_VARS['infos_prod']['duree_max_mois'])." "._("Comme définie dans le produit.")."\\n';ADFormValid=false;}\n";
    $js_check .="\t}else\n";
    $JS_1.="\t\tif(parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$SESSION_VARS['infos_prod']['duree_min_mois'].")) { msg+=' - ".sprintf(_("La durée du crédit doit être au moins égale à %s"),$SESSION_VARS['infos_prod']['duree_min_mois'])." "._("Comme définie dans le produit.")."\\n';ADFormValid=false;}\n";

    // Test de la durée demandée à faire uniquement si le nombre de mois de la période est > 1

  } // fin parcours dossiers

  $Myform->addJS(JSP_BEGIN_CHECK,"testdateapprob",$js_date_approb);
  $Myform->addJS(JSP_FORM,"testgar",$js_gar);
  $Myform->addJS(JSP_BEGIN_CHECK,"js_check",$js_check);
  $Myform->addJS(JSP_FORM,"js_mnt_octr",$js_mnt_octr);

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  if ($can_mob_gar) {
    $Myform->addFormButton(1,2,"mobiliser_gar", _("Mobilisation garanties"), TYPB_SUBMIT);
    $Myform->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
    $Myform->setFormButtonProperties("mobiliser_gar", BUTP_PROCHAIN_ECRAN, "LApd-6");
    $Myform->setFormButtonProperties("mobiliser_gar", BUTP_CHECK_FORM, false);
  } else
    $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");

  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "LApd-3");
  //$Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "LApd-4");
  $Myform->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // JavaScript
  // Initialisation de champs dèsque le champ mnt_octr est activé
  $js_mnt_reset = "function reset(id_doss) { \n";
  $js_mnt_reset .= "var cre_mnt_octr = 'cre_mnt_octr'+id_doss;\n";
  $js_mnt_reset .= "var mnt_assurance = 'mnt_assurance'+id_doss;\n";
  $js_mnt_reset .= "var mnt_commission = 'mnt_commission'+id_doss;\n";
  $js_mnt_reset .= "var gar_num ='gar_num'+id_doss;\n";
  $js_mnt_reset .= "var gar_mat ='gar_mat'+id_doss;\n";
  $js_mnt_reset .= "var gar_tot ='gar_tot'+id_doss;\n";
  $js_mnt_reset .= "var gar_num_encours ='gar_num_encours'+id_doss;\n";
  $js_mnt_reset .= "document.ADForm.eval(cre_mnt_octr).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_assurance).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_commission).value ='';\n";
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
    $js_mnt_reset .= "document.ADForm.eval(gar_num).value ='';\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
    $js_mnt_reset .= "document.ADForm.eval(gar_mat).value ='';\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
    $js_mnt_reset .= "document.ADForm.eval(gar_tot).value ='';\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
    $js_mnt_reset .= "document.ADForm.eval(gar_num_encours).value ='';\n";
  }
  $js_mnt_reset .= "}\n";
  $Myform->addJS(JSP_FORM,"js_mnt_reset",$js_mnt_reset);

  // Calule du montant de l'assurance, de la commission et des garanties en fonction du montant à octoyer
  $js_mnt_init = "function init(id_doss) { \n";
  $js_mnt_init .= "var cre_mnt_octr = 'cre_mnt_octr'+id_doss;\n";
  $js_mnt_init .= "var mnt_octr = 'mnt_octr'+id_doss;\n";
  $js_mnt_init .= "var mnt_assurance = 'mnt_assurance'+id_doss;\n";
  $js_mnt_init .= "var mnt_commission = 'mnt_commission'+id_doss;\n";
  $js_mnt_init .= "var gar_num ='gar_num'+id_doss;\n";
  $js_mnt_init .= "var gar_mat ='gar_mat'+id_doss;\n";
  $js_mnt_init .= "var gar_tot ='gar_tot'+id_doss;\n";
  $js_mnt_init .= "var gar_num_encours ='gar_num_encours'+id_doss;\n";

  $js_mnt_init .="\t\t eval('document.ADForm.'+mnt_assurance).value = Math.round(".$SESSION_VARS['infos_prod']['prc_assurance']."*parseFloat(recupMontant(eval('document.ADForm.'+cre_mnt_octr).value))*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\teval('document.ADForm.'+mnt_assurance).value =formateMontant(eval('document.ADForm.'+mnt_assurance).value);\n";
  $js_mnt_init .="\t\t\t eval('document.ADForm.'+mnt_commission).value = Math.round((".$SESSION_VARS['infos_prod']['prc_commission']."* parseFloat(recupMontant(eval('document.ADForm.'+cre_mnt_octr).value))+ ".$SESSION_VARS['infos_prod']['mnt_commission'].")*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\t eval('document.ADForm.'+mnt_commission).value =formateMontant( eval ('document.ADForm.'+mnt_commission).value);\n";
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
    $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_num).value =Math.round(".$SESSION_VARS['infos_prod']['prc_gar_num']."* parseFloat(recupMontant( eval('document.ADForm.'+cre_mnt_octr).value))*".$precision_devise.")/".$precision_devise.";\n";
    // 2116
    $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_num).value =formateMontant( eval('document.ADForm.'+gar_num).value);\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
    $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_mat).value =Math.round(".$SESSION_VARS['infos_prod']['prc_gar_mat']."* parseFloat(recupMontant( eval('document.ADForm.'+cre_mnt_octr).value)));\n";
    $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_mat).value =formateMontant( eval('document.ADForm.'+gar_mat).value);\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
   // $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_tot).value = Math.round(".$SESSION_VARS['infos_prod']['prc_gar_tot']."* parseFloat(recupMontant(  eval('document.ADForm.'+cre_mnt_octr).value))*".$precision_devise.")/".$precision_devise.";\n";
   // $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_tot).value = formateMontant( eval('document.ADForm.'+gar_tot).value);\n";
  }
  if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
    $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_num_encours).value =Math.round(".$SESSION_VARS['infos_prod']['prc_gar_encours']."* parseFloat(recupMontant(eval('document.ADForm.'+cre_mnt_octr).value))*".$precision_devise.")/".$precision_devise.";\n";
    $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_num_encours).value = formateMontant( eval('document.ADForm.'+gar_num_encours).value);\n";
  }
  $js_mnt_init .="\t\t\t eval('document.ADForm.'+mnt_octr).value = recupMontant(eval('document.ADForm.'+cre_mnt_octr).value);\n";
  $js_mnt_init .= "}";
  $Myform->addJS(JSP_FORM,"js_mnt_init",$js_mnt_init);

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

/*}}}*/

/*{{{ LApd-3 : Affichage de l'échéancier */
else if ($global_nom_ecran == "LApd-3") {

  $HTML_code = '';
  //$JS = "\t\tassign('LApd-5');\n"; // Determination du prochain écran
  $prochain_ecran_lcr = "LApd-5";
  // Parcours des dossiers effectifs (dans ad_dcr)
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    if ($val_doss["last_etat"] == 1) { // L'état du dossier est En attente de décision
      // Les informations du produit
      $id_prod = $SESSION_VARS['infos_prod']['id_prod'];

      // Remplissage de $SESSION_VARS avec les données postées au serveur, données qui seront ensuite transférées dans DATA .
      $SESSION_VARS['infos_doss'][$id_doss]["duree_mois"] = (float)$ {'duree_mois'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["tx_interet_lcr"] = ($ {'tx_interet_lcr'.$id_doss}/100);
      $SESSION_VARS['infos_doss'][$id_doss]["taux_frais_lcr"] = ($ {'taux_frais_lcr'.$id_doss}/100);
      $SESSION_VARS['infos_doss'][$id_doss]["taux_min_frais_lcr"] = recupMontant($ {'taux_min_frais_lcr'.$id_doss});
      $SESSION_VARS['infos_doss'][$id_doss]["taux_max_frais_lcr"] = recupMontant($ {'taux_max_frais_lcr'.$id_doss});
      $SESSION_VARS['infos_doss'][$id_doss]["ordre_remb_lcr"] = $ {'ordre_remb_lcr'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["duree_nettoyage_lcr"] = $ {'duree_nettoyage_lcr'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["deboursement_autorisee_lcr"] = $ {'deboursement_autorisee_lcr'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["remb_auto_lcr"] = $ {'remb_auto_lcr'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["differe_jours"] = $ {'differe_jours'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["differe_ech"] = $ {'differe_ech'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["delai_grac"] = $ {'delai_grac'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["cre_date_approb"] = $ {'cre_date_approb'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"] = recupMontant($ {'mnt_octr'.$id_doss}); // il faut ajouter un champ caché
      $SESSION_VARS['infos_doss'][$id_doss]["id_agent_gest"] = $ {'id_agent_gest'.$id_doss};
      //Kheshan ticket pp178
      $SESSION_VARS['infos_doss'][$id_doss]["mnt_commission"] = recupMontant ($ {'mnt_commission'.$id_doss});
      $SESSION_VARS['infos_doss'][$id_doss]["mnt_assurance"] = recupMontant ($ {'mnt_assurance'.$id_doss});

      // Récupérations des dossiers fictifs dans le cas de GS avec dossier unique
      if ($SESSION_VARS['infos_doss'][$id_doss]["gs_cat"] == 1) {
        foreach($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic=>$val_fic) {
          $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['obj_dem'] = $ {'obj_dem_fic'.$id_fic};
          if (isDcrDetailObjCreditLsb()) {
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem_bis'] = ${'detail_obj_dem_bis_fic' . $id_fic};
          } else {
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem'] = $ {'detail_obj_dem_fic'.$id_fic};
          }
          $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['mnt_dem'] = recupMontant($ {'mnt_dem_fic'.$id_fic});
        }
      }

      /* Calcul des garanties selon le montant octroyé : les garanties arrondies à l'entier prêt */
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
        $SESSION_VARS['infos_doss'][$id_doss]["gar_num"] = round(recupMontant($SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"]) * $SESSION_VARS['infos_prod']["prc_gar_num"], $global_monnaie_courante_prec);
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
        $SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] = round(recupMontant($SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"]) * $SESSION_VARS['infos_prod']["prc_gar_mat"], $global_monnaie_courante_prec);
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
        $SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] = round(recupMontant($SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"]) * $SESSION_VARS['infos_prod']["prc_gar_tot"], $global_monnaie_courante_prec);
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
        $SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"]=round(recupMontant($SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"]) * $SESSION_VARS['infos_prod']["prc_gar_encours"],$global_monnaie_courante_prec);
      }

      // Calcul de l'échéancier théorique
      $echeancier = calcul_echeancier_theorique($SESSION_VARS['infos_prod']['id'], $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"], $SESSION_VARS['infos_doss'][$id_doss]["duree_mois"], $SESSION_VARS['infos_doss'][$id_doss]["differe_jours"], $SESSION_VARS['infos_doss'][$id_doss]["differe_ech"], NULL, 1, $id_doss);

      // Affichage de l'échéancier
      $parametre["id_client"] = $SESSION_VARS['infos_doss'][$id_doss]['id_client'];
      $parametre["lib_date"]=_("Date d'approbation");
      $parametre["index"] = 0;                            // Index de début des n° d'echéance
      $parametre["titre"] = _("Echéancier théorique de remboursement");
      $parametre["nbre_jour_mois"] = 30;
      $parametre["montant"] = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"];
      $parametre["mnt_reech"] = 0; //Montant rééchelonnement
      $parametre["mnt_octr"] = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"]; //Montant octroyé
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0 && $SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0)
        $parametre["garantie"] = $SESSION_VARS['infos_doss'][$id_doss]["gar_num"] +$SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"];
      else if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0)
        $parametre["garantie"] = $SESSION_VARS['infos_doss'][$id_doss]["gar_num"];
      else if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0)
        $parametre["garantie"] = $SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"];
      $parametre["duree"] = $SESSION_VARS['infos_doss'][$id_doss]["duree_mois"];  // Durée du crédit
      $parametre["date"] = $ {'cre_date_approb'.$id_doss};
      $parametre["id_prod"] = $SESSION_VARS['infos_doss'][$id_doss]["id_prod"];
      $parametre["id_doss"] = -1;//$SESSION_VARS["id_doss"]; Si id_prod=-1 alors l'echéancier n'est pas sauvegardé
      $parametre["differe_jours"] = $SESSION_VARS['infos_doss'][$id_doss]["differe_jours"];
      $parametre["differe_ech"] = $SESSION_VARS['infos_doss'][$id_doss]["differe_ech"];
      $parametre["EXIST"] = 0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon

      // S'il y a des garanties numéraires ou matérielles bloquées au début alors aller systématiquement à l'écran de blocage */
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0 or $SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0)
        //$JS.="\t\tassign('LApd-4');\n";  // Blocage des garanties
        $prochain_ecran_lcr = "LApd-4";
    }

    $echeancier = completeEcheancier($echeancier, $parametre);
    if ($parametre["id_doss"]>=0) {
      $SESSION_VARS["etr"] = $echeancier;
      $SESSION_VARS['infos_doss'][$parametre["id_doss"]]['etr'] = $echeancier;
    }

    // Génération de l'échéancier
    //$HTML_code .= HTML_echeancier($parametre,$echeancier);
  }

  // Création du formulaire
  $formEcheancier = new HTML_GEN2();
    
  $codeJs = "window.onload=function(){\n\n
        function submitform(){\n
          document.ADForm.prochain_ecran.value=\"$prochain_ecran_lcr\";\n
          document.ADForm.m_agc.value=\"".$_REQUEST['m_agc']."\";\n
          document.forms[0].submit();\n
        }\n\n
        var auto = setTimeout(function(){ submitform(); });\n
    }\n";
  $formEcheancier->addJS(JSP_FORM, "JS_post", $codeJs);
  
  $formEcheancier->buildHTML();
  
  echo $formEcheancier->getHTML();

}
/*}}}*/

/*{{{ LApd-4 : Blocage des garanties numéraires */
else if ($global_nom_ecran == "LApd-4") {
  $formConf = new HTML_GEN2(_("Blocage des garanties"));

  $msg = '';
  $order = array();

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $valeur_totale_bloquee = $val_doss["gar_num"] + $val_doss["gar_mat"]; // total à bloquer
    $valeur_totale_mobilisee = 0; /* la valeur totale mobilisée : garanties numéraires + garanties matérielless */
    
    /* Récupération des garanties qui étaient mobilisée lors de la mise en place du dossier de crédit */
    $liste_gar = getListeGaranties($id_doss);

    // Bloquer chaque garantie numéraire sur son compte de prélèvement
    foreach($val_doss['DATA_GAR'] as $key=>$value ) {
      if ($value['type'] == 1) { // Garanties numéraires
        $mnt_gar_mob = recupMontant($value['valeur']);
        $valeur_totale_mobilisee += recupMontant($value['valeur']);

        $cpt_prelev_gar = $value['descr_ou_compte'];
        $cpteInfo = getAccountDatas($cpt_prelev_gar);

        /* Solde disponible du compte de prélèvement de la garantie = solde dipso + ancienne garantie bloquée */
        $soldeB = getSoldeDisponible($cpt_prelev_gar);

        /* Si un montant avait été bloqué sur ce compte lors de la mise en place du dossier alors l'ajouter dans le dispo */
        if ($value['id_gar'] != NULL)
          $soldeB += $liste_gar[$value['id_gar']]['montant_vente'];

        /* Si le montant nouvellement mobilisé est > au solde disponible du compte de prélèvement */
        if ($mnt_gar_mob > $soldeB)
          $msg = sprintf(_("Le solde du compte de prélèvement des garanties du dossier %s est insuffisant."),$id_doss)."<br/><ul><li>"._("Montant de la garantie")." : ".afficheMontant($mnt_gar_mob, true)."</li><li>"._("Solde du compte de prélèvement")." : ".afficheMontant($soldeB, true)."</li></ul>";

        /* Ligne de séparation des garanties numéraires */
        $formConf->addHTMLExtraCode("gar".$id_doss."_".$key,"<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Garanties numéraires")."</b></td></tr></table>\n");

        /* Affichage */
        $formConf->addField("intitule_compte".$id_doss."_".$key,_("Intitule du compte"),TYPC_TXT);
        $formConf->addField("num_complet_cpte".$id_doss."_".$key,_("Numéro du compte"),TYPC_TXT);
        $formConf->addField("solde".$id_doss."_".$key,_("Solde du compte"),TYPC_MNT);
        $formConf->addField("mnt_gar".$id_doss."_".$key,_("Montant des garanties"),TYPC_MNT);

        /* Ordre d'affichage des champs */
        array_push($order,"gar".$id_doss."_".$key);
        array_push($order,"intitule_compte".$id_doss."_".$key);
        array_push($order,"num_complet_cpte".$id_doss."_".$key);
        array_push($order,"solde".$id_doss."_".$key);
        array_push($order,"mnt_gar".$id_doss."_".$key);

        /* Remplissage du formulaire */
        $formConf->setFieldProperties("intitule_compte".$id_doss."_".$key, FIELDP_DEFAULT, $cpteInfo["intitule_compte"]);
        $formConf->setFieldProperties("num_complet_cpte".$id_doss."_".$key, FIELDP_DEFAULT, $cpteInfo["num_complet_cpte"]);
        $formConf->setFieldProperties("solde".$id_doss."_".$key,FIELDP_DEFAULT,$soldeB);
        $formConf->setFieldProperties("mnt_gar".$id_doss."_".$key,FIELDP_DEFAULT, $mnt_gar_mob);

        /* Griser les champs */
        $formConf->setFieldProperties("mnt_gar".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("solde".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("num_complet_cpte".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("intitule_compte".$id_doss."_".$key,FIELDP_IS_LABEL,true);

      }
      elseif($value['type'] == 2)
      // Garanties matérielles
      {
        $mnt_gar_mob = recupMontant($value['valeur']);
        $valeur_totale_mobilisee += recupMontant($value['valeur']);

        /* Ligne de séparation des garanties */
        $formConf->addHTMLExtraCode("gar".$id_doss."_".$key,"<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Garanties matérielles")."</b></td></tr></table>\n");

        $formConf->addField("libel_gar".$id_doss."_".$key,_("Libellé des garanties"),TYPC_TXT);
        $formConf->setFieldProperties("libel_gar".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("libel_gar".$id_doss."_".$key, FIELDP_DEFAULT, $value['descr_ou_compte']);

        $formConf->addField("mnt_gar".$id_doss."_".$key,_("Valeur des garanties"),TYPC_MNT);
        $formConf->setFieldProperties("mnt_gar".$id_doss."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("mnt_gar".$id_doss."_".$key, FIELDP_DEFAULT, $mnt_gar_mob);

        /* Ordre d'affichage des champs */
        array_push($order,"gar".$id_doss."_".$key);
        array_push($order,"libel_gar".$id_doss."_".$key);
        array_push($order,"mnt_gar".$id_doss."_".$key);
      }
    } // Fin foreach garantie

    /* Vérifier que le solde total disponible n'est pas inférieur à la garantie numéraire à bloquer */
    if ($valeur_totale_bloquee > $valeur_totale_mobilisee)
      $msg = sprintf(_("Impossible de continuer cette opération, le montant mobilisé est insuffisant pour le dossier %s."),$id_doss)."<br /><ul><li>"._("Montant de la garantie")." : ".afficheMontant($valeur_totale_bloquee, true)."</li><li>"._("Valeur totale mobilisée")." : ".afficheMontant($valeur_totale_mobilisee, true)."</li></ul>";

  } // Foreach dossiers

  /* Si une erreur s'est produite */
  if ($msg != '') {
    $erreur = new HTML_erreur(_("Blocage des garanties"));

    $erreur->setMessage($msg);
    $erreur->addButton(BUTTON_OK,"Lcr-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    exit();
  }

  // les boutons ajoutés
  $formConf->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $formConf->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  $formConf->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "LApd-5");
  $formConf->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");

  //$order = array("num_complet_cpte","solde","mnt_bloq","mnt","soldeF");
  $formConf->setOrder(NULL,$order);
  $formConf->buildHTML();
  echo $formConf->getHTML();

}
/*}}}*/

/*{{{ LApd-5 : Confirmation approbation d'un dossier de crédit */
else if ($global_nom_ecran == "LApd-5") {
    
  global $global_nom_login;
  // Préparation des dossiers à approuver
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // Infos du dossier
    if ($val_doss['last_etat'] == 1) { // dossier en attente d'approbation
      $DATA[$id_doss]["duree_mois"] = $val_doss["duree_mois"];
      $DATA[$id_doss]["tx_interet_lcr"] = $val_doss["tx_interet_lcr"];
      $DATA[$id_doss]["taux_frais_lcr"] = $val_doss["taux_frais_lcr"];
      $DATA[$id_doss]["taux_min_frais_lcr"] = $val_doss["taux_min_frais_lcr"];
      $DATA[$id_doss]["taux_max_frais_lcr"] = $val_doss["taux_max_frais_lcr"];
      $DATA[$id_doss]["ordre_remb_lcr"] = $val_doss["ordre_remb_lcr"];
      $DATA[$id_doss]["duree_nettoyage_lcr"] = $val_doss["duree_nettoyage_lcr"];
      $DATA[$id_doss]["deboursement_autorisee_lcr"] = $val_doss["deboursement_autorisee_lcr"];
      $DATA[$id_doss]["remb_auto_lcr"] = $val_doss["remb_auto_lcr"];
      $DATA[$id_doss]["differe_jours"] = $val_doss["differe_jours"];
      $DATA[$id_doss]["differe_ech"] = $val_doss["differe_ech"];
      $DATA[$id_doss]["delai_grac"] = $val_doss["delai_grac"];
      $DATA[$id_doss]["cre_date_approb"] = $val_doss["cre_date_approb"];
      $DATA[$id_doss]["cre_mnt_octr"] = $val_doss["cre_mnt_octr"];
      $DATA[$id_doss]["etat"] = 2;  // Etat accepté
      $DATA[$id_doss]["date_etat"] = $val_doss["cre_date_approb"]; // Date de passage à l'état accepté
      $DATA[$id_doss]["id_agent_gest"] =  $val_doss["id_agent_gest"];
      $DATA[$id_doss]["cre_date_etat"] = $val_doss["cre_date_approb"];

      //Kheshan ticket pp178
      $DATA[$id_doss]["mnt_commission"] = $val_doss["mnt_commission"];
      $DATA[$id_doss]["mnt_assurance"] = $val_doss["mnt_assurance"];

      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
        $DATA[$id_doss]["gar_num"] = $val_doss["gar_num"];
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
        $DATA[$id_doss]["gar_mat"] = $val_doss["gar_mat"];
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
        $DATA[$id_doss]["gar_tot"] = $val_doss["gar_tot"];
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
        $DATA[$id_doss]["gar_num_encours"] = $val_doss["gar_num_encours"];
      }
      //$DATA[$id_doss]["duree_mois"] = $val_doss["duree_mois"];
      $DATA[$id_doss]["gs_cat"] = $val_doss["gs_cat"];
      $DATA[$id_doss]['DATA_GAR'] = $val_doss['DATA_GAR'];
      $DATA[$id_doss]['doss_fic'] = $val_doss['doss_fic'];
      $DATA[$id_doss]['last_etat'] = $val_doss['last_etat'];
    }
    $id_dossier = $id_doss;
    $valeur = $val_doss["cre_mnt_octr"];
  } // Fin parcours dossiers


  $myErr = approbationCredit($DATA, 601);
  if ($myErr->errCode == NO_ERR) {
      // Insert lcr event
      $date_evnt = php2pg(date("d/m/Y"));
      $type_evnt = 1; // Approbation
      $nature_evnt = NULL;
      $login = $global_nom_login;
      $id_his = $myErr->param;
      $comments = 'Approbation dossier. Plafond autorisé '.afficheMontant($valeur).' '.$SESSION_VARS['infos_prod']['devise'];
      
      $lcrErr = insertLcrHis($id_dossier, $date_evnt, $type_evnt, $nature_evnt, $login, $valeur, $id_his, $comments);
      
    $msg = new HTML_message(_("Confirmation approbation du dossier de crédit"));
    $message = _("Le dossier de crédit est passé avec succès à l'état accepté !");
    $message .= "<BR><BR>"._("N° de transaction")." : <B><code>".sprintf("%09d", $myErr->param)."</code></B>";
    $msg->setMessage($message);
    $msg->addButton(BUTTON_OK,"Lcr-1");
    $msg->buildHTML();
    echo $msg->HTML_code;
  } else {
    $html_err = new HTML_erreur(_("Echec de l'approbation du dossier de crédit."));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]."<br />"._("Paramètre")." : ".$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Lcr-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
/*}}}*/

/*{{{ LApd-6 : Affichage des garanties */
else if ($global_nom_ecran == "LApd-6") {
  // Affichage des garanties mobilisées
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Mobilisation des garanties"));

  // Creation d'un tableau contenant toutes les garanties d'un dossier de crédit
  $xtHTML = "<br><TABLE align=\"center\">";
  // En-tête tableau : Bénéficiaire | Type | Description/compte de prélèvement | Valeur | Mod | Sup
  $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
  $xtHTML .= "<td><b>Bénéficiaire</b></td>";
  $xtHTML .= "<td><b>Type</b></td>";
  $xtHTML .= "<td><b>"._("Description/compte de prélèvement")." </b></td>";
  $xtHTML .= "<td><b>"._("Valeur")."</b></td>";
  $xtHTML .= "<td><b>"._("Etat")."</b></td>";
  $xtHTML .= "<td>&nbsp</td><td>&nbsp</td>";
  $xtHTML .= "</tr>";

  // Affichage des garanties mobilisées pour chaque dossier
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // Si on vient de LApd-2 alors mémoriser les données modifiables de LApd-2
    if (strstr($global_nom_ecran_prec,"LApd-2")) {
      $SESSION_VARS['id_doss'] = $id_doss;
      $SESSION_VARS['infos_doss'][$id_doss]["duree_mois"] = ($ {'duree_mois'.$id_doss});
      $SESSION_VARS['infos_doss'][$id_doss]["tx_interet_lcr"] = ($ {'tx_interet_lcr'.$id_doss}/100);
      $SESSION_VARS['infos_doss'][$id_doss]["taux_frais_lcr"] = ($ {'taux_frais_lcr'.$id_doss}/100);
      $SESSION_VARS['infos_doss'][$id_doss]["taux_min_frais_lcr"] = recupMontant($ {'taux_min_frais_lcr'.$id_doss});
      $SESSION_VARS['infos_doss'][$id_doss]["taux_max_frais_lcr"] = recupMontant($ {'taux_max_frais_lcr'.$id_doss});
      $SESSION_VARS['infos_doss'][$id_doss]["ordre_remb_lcr"] = $ {'ordre_remb_lcr'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["duree_nettoyage_lcr"] = $ {'duree_nettoyage_lcr'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["differe_jours"] = $ {'differe_jours'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["differe_ech"] = $ {'differe_ech'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["delai_grac"] = $ {'delai_grac'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"] = recupMontant($ {'mnt_octr'.$id_doss});
      $SESSION_VARS['infos_doss'][$id_doss]["cre_date_approb"] = $ {'cre_date_approb'.$id_doss};
      $SESSION_VARS['infos_doss'][$id_doss]["id_agent_gest"] = $ {'id_agent_gest'.$id_doss};
      //Kheshan ticket pp178
      $SESSION_VARS['infos_doss'][$id_doss]["mnt_commission"] = recupMontant ($ {'mnt_commission'.$id_doss});
      $SESSION_VARS['infos_doss'][$id_doss]["mnt_assurance"] = recupMontant ($ {'mnt_assurance'.$id_doss});

      // Récupérations des dossiers fictifs dans le cas de GS avec dossier unique
      if ($SESSION_VARS['infos_doss'][$id_doss]["gs_cat"] == 1) {
        foreach($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic=>$val_fic) {
          $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['obj_dem'] = $ {'obj_dem_fic'.$id_fic};
          if (isDcrDetailObjCreditLsb()) {
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem_bis'] = ${'detail_obj_dem_bis_fic' . $id_fic};
          } else {
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem'] = $ {'detail_obj_dem_fic'.$id_fic};
          }
          $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['mnt_dem'] = recupMontant($ {'mnt_dem_fic'.$id_fic});
        }
      }
    }
    // Contenu du tableau : on affiche que les garanties des dossiers en attente de décision
    if ($val_doss['etat'] == 1)
      foreach($val_doss['DATA_GAR'] as $key=>$value ) {
      if ($value['type'] != '') { // Si la garantie n'est pas supprimée
        if ($value['type'] == 1) { // Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement
          $CPT_PRELEV_GAR = getAccountDatas($value['descr_ou_compte']); // Infos du compte de prélèvement des garanties
          $origine  = $CPT_PRELEV_GAR["num_complet_cpte"]." ".$CPT_PRELEV_GAR['intitule_compte'] ;
        }
        elseif($value['type'] == 2) // garantie matérielle
        $origine = $value['descr_ou_compte'];

        $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
        $id_client = $SESSION_VARS['infos_doss'][$id_doss]['id_client'];
        $xtHTML .= "<td>".$id_client." ".getClientName($id_client)."</td>";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_type_garantie"][$value['type']])."</td>";
        $xtHTML .= "<td>".$origine."</td>";
        $xtHTML .= "<td>".$value['valeur']."</td>";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_etat_gar"][$value['etat']])."</td>";
        $xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LApd-8&benef=".$id_doss."&num_gar=".$key."\">"._("Mod")."</A></td>";
        $xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=LApd-9&benef=".$id_doss."&num_gar=".$key."\">"._("Sup")."</A></td></tr>";
      }
    }
  }

  $xtHTML .= "</table><br><br>";
  $Myform->addHTMLExtraCode ("garanties", $xtHTML);
  $Myform->addFormButton(1,1,"ajout_gar", _("Nouvelle garantie"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'LApd-2');
  $Myform->setFormButtonProperties("ajout_gar", BUTP_PROCHAIN_ECRAN, 'LApd-7');

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ LApd-7 : Ajout de garanties */
else if ($global_nom_ecran == "LApd-7") {
  // Devise du produit de crédit
  setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);

  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Ajout d'une garantie"));

  // Liste de choix du client (ou dossier) bénéficiare
  $choix = array();
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // On ne mobilise des garanties que pour les dossiers en attente de décision
    if ($val_doss['etat'] == 1)
      $choix[$id_doss] = $val_doss['id_client']." ".getClientName($val_doss['id_client']);
  }

  $Myform->addField ("benef", _("Bénéficiaire"), TYPC_LSB);
  $Myform->setFieldProperties("benef", FIELDP_ADD_CHOICES, $choix);
  $Myform->setFieldProperties("benef",FIELDP_IS_REQUIRED, true);

  // Ajout de certains champs de ad_gar
  $exclude = array("devise_vente","gar_num_id_cpte_prelev","gar_mat_id_bien","gar_num_id_cpte_nantie","id_doss","id_gar","etat_gar");
  $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
  $Myform->setFieldProperties("type_gar", FIELDP_JS_EVENT, array("OnChange"=>"check_type_gar()"));

  // Numéro du compte de prélèvement si garantie numéraire
  $Myform->addField ("gar_num_id_cpte_prelev", _("Compte de prélèvement"), TYPC_TXT);
  $Myform->addLink("gar_num_id_cpte_prelev","rech_cpt",_("Rechercher"), "#");
  $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_LABEL,true);
  $Myform->setLinkProperties("rech_cpt",LINKP_JS_EVENT,array("OnClick"=>"open_compte();return false;"));
  $Myform->addHiddenType("num_id_cpte_prelev", "");
  $Myform->addHiddenType("devise_vente", $SESSION_VARS['infos_prod']['devise']);

  // Etat de la garantie
  $etats_gar = array();
  // $etats_gar[1] = $adsys["adsys_etat_gar"][1];
  $etats_gar[2] = adb_gettext($adsys["adsys_etat_gar"][2]);
  $etats_gar[3] = adb_gettext($adsys["adsys_etat_gar"][3]);
  $Myform->addField ("etat_gar", _("Etat de la garantie"), TYPC_LSB);
  $Myform->setFieldProperties("etat_gar", FIELDP_ADD_CHOICES, $etats_gar);
  $Myform->setFieldProperties("etat_gar",FIELDP_IS_REQUIRED, true);

  /* Libellé de la garantie matérielle */
  $Myform->addField ("libel_gar_mat", _("Garantie matérielle"), TYPC_TXT);
  $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_LABEL,true);

  /* Types de bien */
  $types_biens = getTypesBiens();
  $Myform->addField ("type_bien", _("Type de bien"), TYPC_LSB);
  $Myform->setFieldProperties("type_bien", FIELDP_ADD_CHOICES, $types_biens);
  $Myform->setFieldProperties("type_bien",FIELDP_IS_LABEL,true);

  /* Numéro du client si garantie du matérielle */
  $Myform->addField ("num_client", _("Client garant du matériel"), TYPC_TXT);
  $Myform->addHiddenType("num_client_rel");

  $Myform->addLink("num_client", "rech_client", _("Rechercher"), "#");
  $Myform->setLinkProperties("rech_client",LINKP_JS_EVENT,array("OnClick"=>"rech_client();"));
  $Myform->setFieldProperties("num_client",FIELDP_IS_LABEL,true);

  /* Pièce justificative si garanties matérielles */
  $Myform->addField ("piece_just", _("Pièce justificative"), TYPC_TXT);
  $Myform->setFieldProperties("piece_just",FIELDP_IS_LABEL,true);

  /* Remarque si garanties matérilles */
  $Myform->addField ("remarq", _("Remarque"), TYPC_TXT);
  $Myform->setFieldProperties("remarq",FIELDP_IS_LABEL,true);

  /* Traitement à effectuer : ajout, modification ou suppression de garantie */
  $Myform->addHiddenType("traitement", "ajout");

  /* Order d'affichage des champs */
  $order = array ("benef","type_gar","gar_num_id_cpte_prelev","libel_gar_mat","type_bien","piece_just","remarq","num_client","montant_vente", "etat_gar");
  $Myform->setOrder(NULL, $order);

  $Myform->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'LApd-6');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'LApd-10');

  /* Contrôle des champs à renseigner selon le type de garantie  */
  $JS_valide ="";
  $JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1 && document.ADForm.gar_num_id_cpte_prelev.value =='')";
  $JS_valide .="\n\t{msg+='"._("Le compte de prélèvement des garanties doit être renseigné")."'; ADFormValid = false;}";

  $JS_valide .="\n\tif( (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.libel_gar_mat.value =='') ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.HTML_GEN_LSB_type_bien.value == 0) ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.num_client_rel.value == '')) ";
  $JS_valide .="\n\t{msg+='"._("La description, le type du matériel et le client garant doivent être renseignés")."'; ADFormValid = false;}";

  $Myform->addJS(JSP_BEGIN_CHECK, "valJS", $JS_valide);

  /* JS : active ou désactive des champs selon le type de garantie */
  $JS_active = "";
  $JS_active .="\nfunction check_type_gar()";
  $JS_active .="\n{";
  $JS_active .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1)";
  $JS_active .="\n\t{";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=2;"; /* Garanties numéraires prêtes */
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.value = '';";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = true;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_type_bien.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\t}";
  $JS_active .="\n\telse if(document.ADForm.HTML_GEN_LSB_type_gar.value == 2)";
  $JS_active .="\n\t{";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.gar_num_id_cpte_prelev.value = '';";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=0;"; /* Garanties mat en cours de mobilisation ou prête */
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = false;";
  $JS_active .="\n\t}";
  $JS_active .="\n\telse";
  $JS_active .="\n\t{";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.value = '';";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = true;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_type_bien.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\tdocument.ADForm.gar_num_id_cpte_prelev.value = '';";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=0;"; /* Garanties mat en cours de mobilisation ou prête */
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = false;";
  $JS_active .="\n\t}";
  $JS_active .="\n}";

  /* JS : recherche du compte de prélèvement si garantie numéraire */
  $JS_prelev="";
  $JS_prelev .="\nfunction open_compte()\n";
  $JS_prelev .="{\n";
  $JS_prelev .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1)";
  $JS_prelev .="\n{url = '".$http_prefix."/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=gar_num_id_cpte_prelev&id_cpt_dest=num_id_cpte_prelev&devise=".$SESSION_VARS["devise"]."';\n";
  $JS_prelev .="\t\tgarant = OpenBrwXY(url, '"._("Compte de prélèvement")."', 400, 500);\n";
  $JS_prelev .="\t}\n";
  $JS_prelev .="\telse return false;\n";
  $JS_prelev .="}\n";

  /* JS : recherche du client si garantie numéraire */
  $JS_cli = "";
  $JS_cli .="\nfunction rech_client()\n";
  $JS_cli .="{\n";
  $JS_cli .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 2)";
  $JS_cli .="\n{OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client&num_client_dest=num_client_rel', '"._("Recherche")."');";
  $JS_cli .="\t}\n";
  $JS_cli .="\telse return false;\n";
  $JS_cli .="}\n";

  /* Ajout du code JavaScript  */
  $Myform->addJS(JSP_FORM,"check", $JS_active);
  $Myform->addJS(JSP_FORM,"rech", $JS_prelev);
  $Myform->addJS(JSP_FORM,"rech_clt", $JS_cli);

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ LApd-8 : Modification de garanties */
else if ($global_nom_ecran == "LApd-8") {
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Modification d'une garantie"));

  // Liste de choix du client (ou dossier) bénéficiare
  $choix = array();
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss)
  $choix[$id_doss] = $val_doss['id_client']." ".getClientName($val_doss['id_client']);

  $Myform->addField ("benef", _("Bénéficiaire"), TYPC_LSB);
  $Myform->setFieldProperties("benef", FIELDP_ADD_CHOICES, $choix);
  $Myform->setFieldProperties("benef",FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties("benef",FIELDP_DEFAULT, $benef);

  $exclude = array("devise_vente", "gar_num_id_cpte_prelev", "gar_mat_id_bien", "gar_num_id_cpte_nantie", "id_doss", "id_gar","etat_gar");
  $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
  $Myform->setFieldProperties("type_gar", FIELDP_JS_EVENT, array("OnChange"=>"check_type_gar()"));

  /* Compte de prélèvement de la garantie numéraire */
  $Myform->addField ("gar_num_id_cpte_prelev", _("Compte de prélèvement"), TYPC_TXT);
  $Myform->addLink("gar_num_id_cpte_prelev","rechercher",_("Rechercher"), "#");
  $Myform->setLinkProperties("rechercher",LINKP_JS_EVENT,array("OnClick"=>"open_compte();return false;"));
  $Myform->addHiddenType("num_id_cpte_prelev", $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
  // ticket #392
  $Myform->addHiddenType("num_id_cpte_prelev_ancien", $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);

  /* Etat de la garantie */
  $etats_gar = array();
  $etats_gar[2] = adb_gettext($adsys["adsys_etat_gar"][2]);
  $etats_gar[3] = adb_gettext($adsys["adsys_etat_gar"][3]);
  $Myform->addField ("etat_gar", _("Etat de la garantie"), TYPC_LSB);
  $Myform->setFieldProperties("etat_gar", FIELDP_ADD_CHOICES, $etats_gar);
  $Myform->setFieldProperties("etat_gar",FIELDP_IS_REQUIRED, true);

  /* Libellé du matériel */
  $Myform->addField ("libel_gar_mat", _("Garantie matérielle"), TYPC_TXT);

  /* Pièce justificative si garanties matérielles */
  $Myform->addField ("piece_just", _("Pièce justificative"), TYPC_TXT);

  /* Remarque si garanties matérilles */
  $Myform->addField ("remarq", _("Remarque"), TYPC_TXT);

  /* Types de bien */
  $types_biens = getTypesBiens();
  $Myform->addField ("type_bien", _("Type de bien"), TYPC_LSB);
  $Myform->setFieldProperties("type_bien", FIELDP_ADD_CHOICES, $types_biens);

  /* Numéro du client si garantie du matérielle */
  $Myform->addField ("num_client", _("Client garant du matériel"), TYPC_TXT);
  $Myform->addHiddenType("num_client_rel", $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['num_client']);

  /* Lien rechercher */
  $Myform->addLink("num_client", "rech_client", _("Rechercher"), "#");
  $Myform->setLinkProperties("rech_client",LINKP_JS_EVENT,array("OnClick"=>"rech_client();"));
  $Myform->setFieldProperties("num_client",FIELDP_IS_LABEL,true);

  $Myform->addHiddenType("traitement", "modification");
  $Myform->addHiddenType("num_gar", $num_gar);

  $Myform->setFieldProperties("type_gar", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type']);
  $Myform->setFieldProperties("montant_vente", FIELDP_DEFAULT, recupMontant($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['valeur']));
  
  // ticket #392
  $Myform->addHiddenType("montant_vente_ancien", $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['valeur']);
  
  $Myform->setFieldProperties("etat_gar", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['etat']);
  $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_LABEL,true);

  if ($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] ==1) { /* garanties numéraires */
    $Myform->setFieldProperties("etat_gar",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("type_bien",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("piece_just",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("remarq",FIELDP_IS_LABEL,true);

    if ($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] != '') {
      $CPT_PRELEV_GAR = getAccountDatas($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
      $Myform->setFieldProperties("gar_num_id_cpte_prelev", FIELDP_DEFAULT, $CPT_PRELEV_GAR["num_complet_cpte"]);
    }
  }
  elseif($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] ==2) {
    $Myform->setFieldProperties("libel_gar_mat", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
    $Myform->setFieldProperties("type_bien", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type_bien']);
    $Myform->setFieldProperties("piece_just", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['piece_just']);
    $Myform->setFieldProperties("remarq", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['remarq']);
    $Myform->setFieldProperties("num_client", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['num_client']);
  }

  $order = array ("benef","type_gar", "gar_num_id_cpte_prelev", "libel_gar_mat", "type_bien", "num_client","piece_just","remarq","montant_vente", "etat_gar");
  $Myform->setOrder(NULL, $order);

  $Myform->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'LApd-6');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'LApd-10');

  /* Contrôle des champs à renseigner selon le type de garantie  */
  $JS_valide ="";
  $JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1 && document.ADForm.gar_num_id_cpte_prelev.value =='')";
  $JS_valide .="\n\t{msg+='"._("Le compte de prélèvement des garanties doit être renseigné")."'; ADFormValid = false;}";

  $JS_valide .="\n\tif( (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.libel_gar_mat.value =='') ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.HTML_GEN_LSB_type_bien.value == 0) ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.num_client_rel.value == ''))";
  $JS_valide .="\n\t{msg+='"._("Le libellé et le type du matériel doivent être renseignés")."'; ADFormValid = false;}";

  $Myform->addJS(JSP_BEGIN_CHECK, "valJS", $JS_valide);

  /* JS : recherche du compte de prélèvement des garanties numéraires */
  $JS_prelev ="";
  $JS_prelev .="\nfunction open_compte()\n";
  $JS_prelev .="{\n";
  $JS_prelev .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1){url = '".$http_prefix."/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=gar_num_id_cpte_prelev&id_cpt_dest=num_id_cpte_prelev&devise=".$SESSION_VARS["devise"]."';\n";
  $JS_prelev .="\t\tgarant = OpenBrwXY(url, '"._("Autre personne garante")."', 400, 500);\n";
  $JS_prelev .="\t}\n";
  $JS_prelev .="\telse return false;\n";
  $JS_prelev .="}\n";

  /* JS : active ou désactive des champs selon le type de garantie */
  $JS_active = "";
  $JS_active .="\nfunction check_type_gar()";
  $JS_active .="\n{";
  $JS_active .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1)";
  $JS_active .="\n\t{";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=2;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.value = '';";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = true;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_type_bien.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\t}";
  $JS_active .="\n\telse if(document.ADForm.HTML_GEN_LSB_type_gar.value == 2)";
  $JS_active .="\n\t{";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = false;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.gar_num_id_cpte_prelev.value = '';";
  $JS_active .="\n\t}";
  $JS_active .="\n\telse";
  $JS_active .="\n\t{";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.value = '';";
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = true;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_type_bien.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\tdocument.ADForm.gar_num_id_cpte_prelev.value = '';";
  $JS_active .="\n\t}";
  $JS_active .="\n}";

  /* JS : recherche du client si garantie numéraire */
  $JS_cli = "";
  $JS_cli .="\nfunction rech_client()\n";
  $JS_cli .="{\n";
  $JS_cli .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 2)";
  $JS_cli .="\n{OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client&num_client_dest=num_client_rel', '"._("Recherche")."');";
  $JS_cli .="\t}\n";
  $JS_cli .="\telse return false;\n";
  $JS_cli .="}\n";

  /* Ajout du code JS  */
  $Myform->addJS(JSP_FORM,"prelev", $JS_prelev);
  $Myform->addJS(JSP_FORM,"actve", $JS_active);
  $Myform->addJS(JSP_FORM,"cli", $JS_cli);

  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ LApd-9 : Suppression de garanties */
else if ($global_nom_ecran == "LApd-9") {
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Suppression d'une garantie"));
  // Nom client bénéficiaire
  $nom_client = $SESSION_VARS['infos_doss'][$benef]['id_client']." ".getClientName($SESSION_VARS['infos_doss'][$benef]['id_client']);
  $Myform->addField ("beneficiaire", _("Bénéficiaire"), TYPC_TXT);
  $Myform->setFieldProperties("beneficiaire", FIELDP_DEFAULT, $nom_client);
  $Myform->setFieldProperties("beneficiaire",FIELDP_IS_LABEL,true);

  if ($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] == 1 ) {
    $exclude = array("gar_num_id_cpte_prelev", "gar_mat_id_bien","gar_num_id_cpte_nantie","id_doss","id_gar");
    $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
    $Myform->addField ("gar_num_id_cpte_prelev", _("Compte de prélèvement"), TYPC_TXT);

    /* Si garantie numéraire, afficher le numéro complet du compte de prélèvement */
    if ($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] != '') {
      $CPT_PRELEV_GAR = getAccountDatas($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
      $Myform->setFieldProperties("gar_num_id_cpte_prelev", FIELDP_DEFAULT, $CPT_PRELEV_GAR["num_complet_cpte"]);
      $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_LABEL,true);
    }

    $order = array ("beneficiaire","type_gar", "gar_num_id_cpte_prelev","montant_vente", "devise_vente", "etat_gar");
  }
  elseif($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] == 2 ) {
    /* Champs à exclure */
    $exclude = array("gar_num_id_cpte_prelev", "gar_mat_id_bien" , "gar_num_id_cpte_nantie", "id_doss", "id_gar");

    $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
    $Myform->addField ("libel_gar_mat", _("Garantie matérielle"), TYPC_TXT);
    $Myform->addField ("num_client", _("Client garant du matériel"), TYPC_TXT);

    $types_biens = getTypesBiens();
    $Myform->addField ("type_bien", _("Type de bien"), TYPC_TXT);

    $Myform->setFieldProperties("libel_gar_mat", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
    $id_type_bien = $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type_bien'];

    $Myform->setFieldProperties("type_bien", FIELDP_DEFAULT, $types_biens[$id_type_bien]);
    $Myform->setFieldProperties("num_client", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['num_client']);

    $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("type_bien",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("num_client",FIELDP_IS_LABEL,true);
    $order = array ("beneficiaire","type_gar", "libel_gar_mat", "type_bien", "montant_vente", "etat_gar");
  }

  $Myform->addHiddenType("traitement", "suppression");
  $Myform->addHiddenType("num_gar", $num_gar);
  $Myform->addHiddenType("benef", $benef);

  /* Champs communs */
  $Myform->setFieldProperties("type_gar", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type']);
  $Myform->setFieldProperties("montant_vente", FIELDP_DEFAULT, recupMontant($SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['valeur']));
  $Myform->setFieldProperties("etat_gar", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['etat']);
  $Myform->setFieldProperties("devise_vente", FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['devise_vente']);

  $Myform->setFieldProperties("type_gar",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("montant_vente",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("etat_gar",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("devise_vente",FIELDP_IS_LABEL,true);

  $Myform->setOrder(NULL, $order);

  $Myform->addFormButton(1,1,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"supprimer", _("Supprimer"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'LApd-6');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("supprimer", BUTP_PROCHAIN_ECRAN, 'LApd-10');

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ LApd-10 : Confirmation ajout, modification ou suppression de garanties */
else if ($global_nom_ecran == "LApd-10") {
  $Myform = new HTML_message(_("Confirmation"));

  // Ajout ou modification de garantie
  if ($traitement == "ajout" or $traitement == "modification" ) {
    if ($traitement == "ajout") { // Ajout de garantie
      $Myform->setMessage(_("La garantie a été ajoutée avec succès"));
      $num_gar = 1 + count($SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR']);
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['id_gar'] = NULL;
    } else // Modification de garantie
      $Myform->setMessage(_("La garantie a été modifiée avec succès"));

    $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type'] = $type_gar ;
    if ($type_gar == 1) {
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['benef'] = $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['id_client'];

      // Recup ancien compte prelev et montant vente ticket #392
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte_ancien'] = $num_id_cpte_prelev_ancien;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur_ancien'] = $montant_vente_ancien;

      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] = $num_id_cpte_prelev;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type_bien'] = NULL;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['num_client'] = NULL;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur'] = $montant_vente;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['etat'] = 2; /* Prête pour les numéraires car bloquées dans le compte */
    } else if ($type_gar == 2) {
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['benef'] = $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['id_client'];
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] = $libel_gar_mat;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['num_client'] = $num_client_rel;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type_bien'] = $HTML_GEN_LSB_type_bien;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['piece_just'] = $piece_just;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['remarq'] = $remarq;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur'] = $montant_vente;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['etat'] = $etat_gar;
    }

    if ($traitement == "ajout")
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['devise_vente'] = $devise_vente;

  }
  elseif($traitement == "suppression") {
    /* Suppression de garantie */
    $Myform->setMessage(_("La garantie a été supprimée avec succès"));
    $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['type'] = '' ;
    $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] ='';
    $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['valeur'] = '';
    $SESSION_VARS['infos_doss'][$benef]['DATA_GAR'][$num_gar]['etat'] = '';
  }

  $Myform->addButton(BUTTON_OK, 'LApd-6');
  $Myform->buildHTML();
  echo $Myform->HTML_code;

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>