<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [137] Rééchelonnement/moratoire des credits en une fois
 * Cette opération comprends les écrans :
 * - Muf-1 : sélection d'un dossier de crédit
 * - Muf-4 : échéancier actuel à modifier
 * - Muf-2 : nouvel échéancier
 * - Muf-3 : confirmation enregistrement
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';

/*{{{ Muf-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "Muf-1") {
  unset($SESSION_VARS["infos_doss"]);
  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);

  $dossiers = array(); // tableau contenant les infos sur les dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste box des dossiers à afficher
  $i = 1; // clé de la liste box

  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  // Récupération des dossiers individuels réels (dans ad_dcr) à l'état accepté
  $whereCl = " AND (etat=5) AND (periodicite=6)";
  $dossiers_reels = getIdDossier($global_id_client, $whereCl);
  if (is_array($dossiers_reels))
    foreach($dossiers_reels as $id_doss=>$value)
      if ($value['gs_cat'] != 2) { // les dossiers pris en groupe doivent être déboursés via le groupe
        if (allowed2Reech_Moratoire($id_doss, true)) {
          $date = pg2phpDate($value["date_dem"]); //Fonction renvoie  des dates au format jj/mm/aaaa
          $liste[$i] ="n° $id_doss du $date"; //Construit la liste en affichant N° dossier + date
          $dossiers[$i] = $value;

          $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
          $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value["libelle"] . "\";";
          $codejs .= "}\n";
          $i++;
        }
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
      if (($val['id_dcr_grp_sol'] == $id) AND $val['etat'] == 5 AND $val['periodicite'] == 6) {
        if (allowed2Reech_Moratoire($id_doss, true)) {
          $date_dem = $date = pg2phpDate($val['date_dem']);
          $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
        }
      }
      if ($infos != '') { // Si au moins on 1 dossier
        $infos .= "du $date_dem";
        $liste[$i] = $infos;
        $dossiers[$i] = $value; // on garde les infos du dossier fictif

        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
        $codejs .= "{\n\t\tdocument.ADForm.id_prod.value ='" . $val["libelle"] . "';";
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
  $Myform->addFormButton(1,3,"annuler",_("Retour Menu"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Muf-4");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Muf-4 : échéancier courant à modifier */
else if ($global_nom_ecran == 'Muf-4') {
  global $adsys;

  // Si on vient de Muf-1, on récupère les infos de la BD
  if (strstr($global_nom_ecran_prec,"Muf-1")) {
    // Récupération des dossiers à approuver
    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
      // Les informations sur le dossier
      $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
      $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
      $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
      $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
      $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
      $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
      $SESSION_VARS['infos_doss'][$id_doss]['infos_credit'] = get_info_credit($id_doss);

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
        if (($val['id_dcr_grp_sol'] == $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id']) and ($val['periodicite'] == 6) and ($val['etat'] == 5 or $val['etat'] == 9)) {
          $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
          $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
          $SESSION_VARS['infos_doss'][$id_doss]['infos_credit'] = get_info_credit($id_doss);
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

  } //fin si on vient de Muf-1

  // Gestion de la devise
  setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);
  $id_prod  = $SESSION_VARS['infos_prod']['id'];

  // Création du formulaire
  $Myform = new HTML_GEN2(_("Rééchelonnement d'un crédit"));
  $js_duree = '';

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss)
  {
    $SESSION_VARS["id_prod"] = $val_doss["id_prod"];
    $SESSION_VARS["cre_etat"] = $val_doss["cre_etat"];
    $SESSION_VARS["garantie"] = $val_doss["gar_num"];
    $SESSION_VARS["last_duree_mois"] = $val_doss["duree_mois"];
    $SESSION_VARS["cre_mnt_octr"] = $val_doss["cre_mnt_octr"];
    $SESSION_VARS["cre_date_debloc"] = $val_doss["cre_date_debloc"];
    $SESSION_VARS["cpte_credit"] = $val_doss["cre_id_cpte"];

    // Retourne les informations sur l'échéancier passé et non remboursé
    $SESSION_VARS['infos_doss'][$id_doss]['cap'] = 0;
    $SESSION_VARS['infos_doss'][$id_doss]['int'] = 0;
    $SESSION_VARS['infos_doss'][$id_doss]['pen'] = 0;

    // Retourne les informations sur l'échéancier passé sous réserve de la date du jour de rééchelonnement
    $dateRechMor = date("d/m/Y");
    $whereCond="WHERE (remb='f') AND (id_doss='".$id_doss."')";
    $lastEch = getEcheancier($whereCond);

    if (is_array($lastEch))
      while (list($key,$value)=each($lastEch)) {
        $SESSION_VARS['infos_doss'][$id_doss]['cap'] += $value["solde_cap"];
        $SESSION_VARS['infos_doss'][$id_doss]['int'] += $value["solde_int"];  //Somme des intérêts
        $SESSION_VARS['infos_doss'][$id_doss]['pen'] += $value["solde_pen"];  //Somme des pénalités
      }

    // Recherche du montant rééchelonné (= intérêts exigibles + pénalités)
    $MNT_EXIG = getMontantExigible($id_doss);
    $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'] = $MNT_EXIG["int"] + $MNT_EXIG["pen"];

    $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"] = $SESSION_VARS['infos_doss'][$id_doss]['cap'] + $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'];  // Nouveau capital = capital + montant rééchelonné

    // Calcul de la nouvelle garantie attendue
    $SESSION_VARS['infos_doss'][$id_doss]["garantie"] = $SESSION_VARS['infos_prod']['prc_gar_tot'] * $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"];

    // Ajout des champs
    $nom_cli = getClientName($val_doss['id_client']);
    $Myform->addHTMLExtraCode("espace".$id_doss,"<br /><b><p align=center><b> ".sprintf(_("Rééchelonnement du dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");
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
 	  $Myform->setFieldProperties("etat".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_etat_dossier_credit"][$val_doss['etat']]));
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->addField("date_etat".$id_doss, _("Date état du dossier"), TYPC_DTE);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_DEFAULT,$val_doss['date_etat']);
    $Myform->addField("cre_id_cpte".$id_doss, _("Compte de crédit"), TYPC_TXT);
    $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_DEFAULT,$val_doss['cre_id_cpte']);
    $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->addField("cre_etat".$id_doss, _("Etat crédit"), TYPC_INT);
    $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_DEFAULT,getlibel("adsys_etat_credits",$val_doss['cre_etat']));
    $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->addField("cre_nbre_reech".$id_doss, _("Nombre de rééchelonnement"), TYPC_INT);
    $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_DEFAULT,$val_doss['cre_nbre_reech']);
    $Myform->addField("nbre_reechelon_auth".$id_doss, _("Nombre maximum de rééchelonnements"), TYPC_INT);
    $Myform->setFieldProperties("nbre_reechelon_auth".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_prod']['nbre_reechelon_auth']);
    $Myform->setFieldProperties("nbre_reechelon_auth".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->addField("cre_mnt_octr".$id_doss, _("Montant octroyé"), TYPC_MNT);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_DEFAULT,$val_doss['cre_mnt_octr']);

    //type de durée : en mois ou en semaine
    $type_duree = $SESSION_VARS['infos_prod']['type_duree_credit'];
    $duree_mois = $val_doss['duree_mois'];
    $nouv_duree_mois = $val_doss['nouv_duree_mois'];
    $duree_min = $duree_mois + 1;
    if(!empty($nouv_duree_mois)) $duree_min = $nouv_duree_mois + 1;

    $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules
    $Myform->addField("duree_mois".$id_doss, _("Durée en ".$libelle_duree. " initiale"), TYPC_INT);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_DEFAULT,$duree_mois);

    $Myform->addField("duree_mois_reech".$id_doss, _("Durée en $libelle_duree rééchelonnée"), TYPC_INT);
    $Myform->setFieldProperties("duree_mois_reech".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("duree_mois_reech".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("duree_mois_reech".$id_doss,FIELDP_DEFAULT, $nouv_duree_mois);

    $Myform->addField("nouv_duree_mois".$id_doss, _("Durée en $libelle_duree souhaitée"), TYPC_INT);
    $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_DEFAULT, $duree_min);

    $Myform->addField("mnt_cap".$id_doss, _("Montant dû en capital"), TYPC_MNT);
    $Myform->addField("mnt_exig_int".$id_doss,_("Montant exigible en intérêts"), TYPC_MNT);
    $Myform->addField("mnt_pen".$id_doss,_("Montant dû en pénalités"), TYPC_MNT);
    $Myform->addField("mnt_reech".$id_doss,_("Montant rééchelonné"), TYPC_MNT);
    $Myform->addField("nouveau_cap".$id_doss,_("Nouveau capital"), TYPC_MNT);

    // Verification si le dossier de credit a ete 1 cree/2 debourse/rechelonnee/approbationReech le meme jour.
    $today = date("Y-m-d 00:00:00");

    if (($val_doss ['cre_date_debloc'] == $SESSION_VARS ['infos_doss'] [$id_doss] ['date_etat']) AND ($SESSION_VARS ['infos_doss'] [$id_doss] ['date_etat'] == $today)) {
      $SESSION_VARS ['infos_doss'] [$id_doss] ['mnt_reech'] = 0;
      $MNT_EXIG ["int"] = 0;
      $SESSION_VARS ['infos_doss'] [$id_doss] ["nouveau_cap"] = $SESSION_VARS ['infos_doss'] [$id_doss] ['cap'];
    }


    $Myform->setFieldProperties("mnt_cap".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['cap']);
    $Myform->setFieldProperties("mnt_exig_int".$id_doss,FIELDP_DEFAULT,$MNT_EXIG["int"]);
    $Myform->setFieldProperties("mnt_pen".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['pen']);
    $Myform->setFieldProperties("nouveau_cap".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"]);
    $Myform->setFieldProperties("mnt_reech".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech']);

    //Les champs obligatoires
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);

    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_cap".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_pen".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("nouveau_cap".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_reech".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_exig_int".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("nbre_reechelon_auth".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);

    // Les controls de duree :
    $duree_min_affiche = $duree_min - 1;
    $msgControlDuree = _(" La nouvelle durée en mois du dossier $id_doss doit être supérieure à $duree_min_affiche");

    $js_duree .="\t\t
      var nouv_duree_mois = document.ADForm.nouv_duree_mois$id_doss.value;
      \n \t\t nouv_duree_mois = parseInt(nouv_duree_mois);
      \n \t\t if(nouv_duree_mois < $duree_min) {
      \n \t\t   msg +='- $msgControlDuree\\n';
      \n \t\t   document.ADForm.nouv_duree_mois$id_doss.value = $duree_min;
      \n \t\t   ADFormValid=false;
      \n \t\t }
      ";

  } // Fin parcours dossiers

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Muf-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $Myform->addJS(JSP_BEGIN_CHECK,"datetest",$js_duree);
  $Myform->buildHTML();
  echo $Myform->getHTML();
  xdebug_dump_superglobals();
}
/*}}}*/

/*{{{ Muf-2 : Nouvel échéancier */
else if ($global_nom_ecran == "Muf-2") {
  $id_prod = $SESSION_VARS['infos_prod']['id'];
  $HTML_code = '';
  $formEcheancier = new HTML_GEN2();
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // Remplissage de $SESSION_VARS avec les données postées au serveur, données qui seront ensuite transférées dans DATA .
    $SESSION_VARS['infos_doss'][$id_doss]["nouv_duree_mois"] = $ {'nouv_duree_mois'.$id_doss}; //Duréé nouvelle de crédit

    $differe_jours = 0;
    $differe_ech = 0;
    $SESSION_VARS['infos_doss'][$id_doss]['echeancier'] = calcul_echeancier_theorique($id_prod, $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"], $SESSION_VARS['infos_doss'][$id_doss]['nouv_duree_mois'], $differe_jours, $differe_ech, NULL, 1, $id_doss);

    // Appel de l'affichage de l'échéancier
    $parametre["id_client"] = $SESSION_VARS['infos_doss'][$id_doss]['id_client'];
    $parametre["lib_date"]= _("Date de rééchelonnement");
    $parametre["index"] = getRembPartiel($id_doss); // Renvoie l'id_ech de la dernière échéance remboursé partiellement
    $parametre["titre"] = _("Echéancier théorique de remboursement du dossier $id_doss");
    $parametre["nbre_jour_mois"] = 30;
    $parametre["montant"] = $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"];
    $parametre["mnt_reech"] = $SESSION_VARS['infos_doss'][$id_doss]["mnt_reech"]; //Montant rééchelonnement
    $parametre["mnt_octr"] = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"]; //Montant octroyé
    $parametre["garantie"] = $SESSION_VARS['infos_doss'][$id_doss]["garantie"];
    $parametre["duree"] = $SESSION_VARS['infos_doss'][$id_doss]['nouv_duree_mois'];  //Nouvelle durée du crédit
    $parametre["date"] = date("d/m/Y");//Date de rééchelonnement
    $parametre["id_prod"]= $id_prod;
    $parametre["id_doss"]= -1;//$SESSION_VARS["id_doss"]; Si id_prod=-1 alors l'echéancier n'est pas sauvegardé
    $parametre["differe_jours"] = $differe_jours;
    $parametre["differe_ech"] = $differe_ech;
    $parametre["EXIST"] = 0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon

    $HTML_code .= HTML_echeancier($parametre, $SESSION_VARS['infos_doss'][$id_doss]['echeancier'], $id_doss);
  }

  // les boutons ajoutés
  $formEcheancier->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  $formEcheancier->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Muf-3");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $formEcheancier->buildHTML();

  echo  $HTML_code;
  echo $formEcheancier->getHTML();
  xdebug_dump_superglobals();
}
/*}}}*/

/*{{{ Muf-3 : Confirmation */
else if ($global_nom_ecran == "Muf-3") {
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    //Remplissage de $DATA avec les données concernant la mise à jour du dossier de crédit
    $DATA[$id_doss]["nouv_duree_mois"] = $val_doss["nouv_duree_mois"];
    $DATA[$id_doss]["mnt_reech"] = $val_doss["mnt_reech"];
    $DATA[$id_doss]["etat"] = 7;
    $DATA[$id_doss]["date_etat"] = date("d/m/Y");
    $DATA[$id_doss]['id_client'] = $val_doss["id_client"];
  }

  if (reechMoratoireAtomic($DATA, true)) {

    $msg = new HTML_message(_("Confirmation rééchelonnement du dossier de crédit"));
    $msg->setMessage(_("Dossier mis en attente de rééchelonnement!"));
    $msg->addButton(BUTTON_OK,"Gen-11");
    $msg->buildHTML();
    echo $msg->HTML_code;
  }

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>