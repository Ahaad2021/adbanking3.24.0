<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [145] Rééchelonement/moratoire
 * Cette opération comprends les écrans :
 * - Rdo-1 : sélection d'un dossier de crédit
 * - Rdo-4 : échéancier actuel à modifier
 * - Rdo-2 : nouvel échéancier
 * - Rdo-3 : confirmation enregistrement
 * - Rdo-5 : Blocage des garanties mobilisées
 * - Rdo-6 : Gestion des garanties mobilisées
 * - Rdo-7 : Ajout d'une garantie
 * - Rdo-8 : Modification d'un garantie
 * - Rdo-9 : Suppression d'un garantie
 * - Rdo-10 : Confirmation d'ajout, de modification ou de suppression de d'un garantie
 *
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';

/*{{{ Rdo-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "Rdo-1") {
  unset($SESSION_VARS["infos_doss"]);
  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);

  $dossiers = array(); // tableau contenant les infos sur les dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste box des dossiers à afficher
  $i = 1; // clé de la liste box

  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  // Récupération des dossiers individuels réels (dans ad_dcr) à l'état accepté
  $cre_etat_all = array_keys(getTousEtatCreditAll());
  $whereCl = " AND (etat=5 AND cre_etat IN (".implode(', ', $cre_etat_all).")) ";

  $dossiers_reels = getIdDossier($global_id_client, $whereCl);
  if (is_array($dossiers_reels))
    foreach($dossiers_reels as $id_doss=>$value)
    if ($value['gs_cat'] != 2) { // les dossiers pris en groupe doivent être déboursés via le groupe
      if (allowed2Reech_Moratoire($id_doss)) {
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
      if (($val['id_dcr_grp_sol'] == $id) AND $val['etat'] == 5) {
        if (allowed2Reech_Moratoire($id_doss)) {
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
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rdo-4");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Rdo-4 : échéancier courant à modifier */
else if ($global_nom_ecran == 'Rdo-4') {
  global $adsys;

  // Si on vient de Rdo-1, on récupère les infos de la BD
  if (strstr($global_nom_ecran_prec,"Rdo-1")) {
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
        if (($val['id_dcr_grp_sol'] == $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id']) and ($val['etat'] == 5 or $val['etat'] == 9)) {
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

  } //fin si on vient de Rdo-1

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


    $MNT_EXIG = getMontantExigible($id_doss);

    // Recupere le montant min/max pour le reechelonnement:
    // Recupere les duree minimum et maximum permissible pour le dossier
    $dureeReechData = getDureeMinMaxForReech($id_doss);

    $nbr_echeances_initial = $dureeReechData['nbr_echeances_initial'];
    $nbr_echeances_restant = $dureeReechData['nbr_echeances_restant'];
    $nbr_echeances_max = $dureeReechData['nbr_echeances_max'];
    $nbr_echeances_min = $dureeReechData['nbr_echeances_min'];
    $periodicite = $dureeReechData['periodicite'];
    $periodicite_mois = $dureeReechData['periodicite_mois'];

    /********************************* AT-187 *******************************************/

    $differe_jours = 0;
    $differe_ech = 0;

    $next_ech = getEcheancier("WHERE id_doss=$id_doss AND remb='f' AND date_ech >  DATE('".date("Y-m-d")."') ");
    $prev_ech = getEcheancier("WHERE id_doss=$id_doss AND id_ech=".($next_ech[0]['id_ech'] - 1));
    $prev_ech = (empty($prev_ech))?array(array('date_ech' => $SESSION_VARS['infos_doss'][$id_doss]['cre_date_debloc'])):$prev_ech;

    if(!empty($next_ech)){
        if(calcNmbreJr(date("Y/m/d"), $prev_ech[0]['date_ech'])){
            $differe_jours = calcNmbreJr(date("Y/m/d"), $prev_ech[0]['date_ech']);
            $prorata = getProrata($id_prod, $next_ech[0]['mnt_int'], $nbr_echeances_min, $differe_jours, $differe_ech, NULL, $id_doss);
        }
    }else{
        $prorata = 0;
    }


    $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'] = $MNT_EXIG["int"] + $MNT_EXIG["pen"] + $prorata;
    $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"] = $SESSION_VARS['infos_doss'][$id_doss]['cap'] + $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'];  // Nouveau capital = capital + montant rééchelonné
    $MNT_EXIG["int"] += $prorata;
    // Calcul de la nouvelle garantie attendue
    $SESSION_VARS['infos_doss'][$id_doss]["garantie"] = $SESSION_VARS['infos_prod']['prc_gar_tot'] * $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"];

    /********************************* FIN AT-187 **************************************/

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
    $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules

    // #433 : Le champ nbr_echeances_restant garde maintenant le nombre d’échéances restantes
    $Myform->addField("nbr_echeances_restant".$id_doss, _("Nombre d’échéances restantes"), TYPC_INT);
    $Myform->setFieldProperties("nbr_echeances_restant".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("nbr_echeances_restant".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("nbr_echeances_restant".$id_doss,FIELDP_DEFAULT, $nbr_echeances_restant);
    $SESSION_VARS['infos_doss'][$id_doss]["nbr_echeances_restant"] = intval($nbr_echeances_restant);

    // #433 : Le champ nbr_echeances_souhaite garde maintenant le nombre d’échéances souhaitees pour le racourcissement
    $Myform->addField("nbr_echeances_souhaite".$id_doss, _("Nombre d’échéances souhaitées"), TYPC_INT);
    $Myform->setFieldProperties("nbr_echeances_souhaite".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("nbr_echeances_souhaite".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("nbr_echeances_souhaite".$id_doss,FIELDP_DEFAULT, $nbr_echeances_min);

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
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_IS_REQUIRED,false);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_REQUIRED,false);
    //$Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);
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
    $msgControlDuree = _(" Le nombre d’échéances souhaité doit être entre $nbr_echeances_min et $nbr_echeances_max");

    $js_duree .="\t\t
      var selected_echeances = document.ADForm.nbr_echeances_souhaite$id_doss.value;
      \n \t\t selected_echeances = parseInt(selected_echeances); \n
      \n \t\t var min_echeances = parseInt($nbr_echeances_min); \n
      \n \t\t var max_echeances = parseInt($nbr_echeances_max); \n

      if(selected_echeances < 1 || selected_echeances < min_echeances || selected_echeances > max_echeances) {
          msg +='- ".$msgControlDuree."\\n';
          document.ADForm.nbr_echeances_souhaite$id_doss.value = min_echeances;
          ADFormValid=false;
      }
      ";
  } // Fin parcours dossiers

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  /* Remobilisation de la garantie a été inactivé car non nécessaire d'apres le #1525
  $Myform->addFormButton(1,2,"remobiliser_gar",_("Re-mobilisation garanties"),TYPB_SUBMIT);
  */
  $Myform->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  //$Myform->setFormButtonProperties("remobiliser_gar", BUTP_PROCHAIN_ECRAN, "Rdo-6");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rdo-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->addJS(JSP_BEGIN_CHECK,"datetest",$js_duree);
  $Myform->buildHTML();
  echo $Myform->getHTML();
  xdebug_dump_superglobals();
}
/*}}}*/

/*{{{ Rdo-2 : Nouvel échéancier */
else if ($global_nom_ecran == "Rdo-2")
{
  $id_prod = $SESSION_VARS['infos_prod']['id'];
  $HTML_code = '';
  $formEcheancier = new HTML_GEN2();

  foreach ($SESSION_VARS['infos_doss'] as $id_doss => $val_doss)
  {
    $curr_ech_row = getDernierEcheanceNonRemb($id_doss); // Récupéré les infos de la dernière échéance non remboursée
      // Remplissage de $SESSION_VARS avec les données postées au serveur, données qui seront ensuite transférées dans DATA
    $nbr_echeances_souhaite = ${'nbr_echeances_souhaite' . $id_doss}; //Duréé nouvelle de crédit
    $SESSION_VARS['infos_doss'][$id_doss]["nbr_echeances_souhaite"] = intval($nbr_echeances_souhaite);

    $duree = getDureeMinMaxForReech($id_doss);
    $nbr_echeances_initial = $duree['nbr_echeances_initial'];
    $nbr_echeances_restant = $duree['nbr_echeances_restant'];
    $SESSION_VARS['infos_doss'][$id_doss]["nbr_echeances_restant"] = $nbr_echeances_restant;

    $differe_jours = 0;
    $differe_ech = 0;

    $new_echeancier = calcul_echeancier_theorique_raccourci($id_prod, $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"], $SESSION_VARS['infos_doss'][$id_doss]['nbr_echeances_souhaite'], $differe_jours, $differe_ech, NULL, 1, $id_doss);
    $SESSION_VARS['infos_doss'][$id_doss]['echeancier'] = $new_echeancier;

    // Appel de l'affichage de l'échéancier
    $parametre["id_client"] = $SESSION_VARS['infos_doss'][$id_doss]['id_client'];
    $parametre["lib_date"] = _("Date de rééchelonnement");
    $parametre["index"] = getRembPartiel($id_doss); // Renvoie l'id_ech de la dernière échéance remboursé partiellement
    $parametre["titre"] = _("Echéancier théorique de remboursement");
    $parametre["nbre_jour_mois"] = 30;
    $parametre["montant"] = $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"];
    $parametre["mnt_reech"] = $SESSION_VARS['infos_doss'][$id_doss]["mnt_reech"]; //Montant rééchelonnement
    $parametre["mnt_octr"] = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"]; //Montant octroyé
    $parametre["garantie"] = $SESSION_VARS['infos_doss'][$id_doss]["garantie"];

    $parametre["duree"] = $SESSION_VARS['infos_doss'][$id_doss]['nbr_echeances_souhaite'];  //Nouvelle durée du crédit
    $parametre["nbr_echeances_restant"] = $SESSION_VARS['infos_doss'][$id_doss]['nbr_echeances_restant'];
    $parametre["nbr_echeances_souhaite"] = $SESSION_VARS['infos_doss'][$id_doss]['nbr_echeances_souhaite'];

    $parametre["date"] = date("d/m/Y");//Date de rééchelonnement
    $parametre["id_prod"] = $id_prod;
    $parametre["id_doss"] = -1;//$SESSION_VARS["id_doss"]; Si id_prod=-1 alors l'echéancier n'est pas sauvegardé
    $parametre["differe_jours"] = $differe_jours;
    $parametre["differe_ech"] = $differe_ech;
    $parametre["EXIST"] = 0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon

    $HTML_code .= HTML_echeancier_raccourci($parametre, $SESSION_VARS['infos_doss'][$id_doss]['echeancier'], $id_doss);

  }

  // les boutons ajoutés
  $formEcheancier->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $formEcheancier->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);

  // Propriétés des boutons
  $formEcheancier->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rdo-3");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  /* On ne passe plus par une remobilisation des garanties
  $formEcheancier->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $formEcheancier->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rdo-5");
  */
  $formEcheancier->buildHTML();
  echo $HTML_code;
  echo $formEcheancier->getHTML();
  xdebug_dump_superglobals();
}

/*}}}*/

/*{{{ Rdo-3 : Confirmation */
else if ($global_nom_ecran == "Rdo-3") {

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {

    // Remplissage de $DATA avec les données concernant la mise à jour du dossier de crédit
    $DATA [$id_doss] ["nbr_echeances_restant"] = $val_doss ["nbr_echeances_restant"];
    $DATA [$id_doss] ["nbr_echeances_souhaite"] = $val_doss ["nbr_echeances_souhaite"];
    $DATA[$id_doss]["etat"] = 7;
    $DATA [$id_doss] ["date_etat"] = date ( "d/m/Y" );
    $DATA [$id_doss] ['id_client'] = $val_doss ["id_client"];
    $DATA [$id_doss]['mnt_reech'] = $val_doss["mnt_reech"] ;
  }

  if (reechMoratoireAtomic($DATA)) {
    $msg = new HTML_message(_("Confirmation rééchelonnement du dossier de crédit"));
    $msg->setMessage(_("Dossier mis en attente de rééchelonnement!"));
    $msg->addButton(BUTTON_OK,"Gen-11");
    $msg->buildHTML();
    echo $msg->HTML_code;
  }

}
/*}}}*/

/*{{{ Rdo-6 : Affichage des garanties mobilisées */
else if ($global_nom_ecran == "Rdo-6") {
  // Affichage des garanties mobilisées pour ce dossier */
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Mobilisation des garanties"));

  // Creation d'un tableau contenant toutes les garanties des dossiers de crédit
  $xtHTML = "<br><TABLE align=\"center\">";

  // En-tête tableau : Bénéficiaire | Type | Description/compte de prélèvement | Valeur | Mod | Sup
  $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
  $xtHTML .= "<td><b>"._("Bénéficiaire")."</b></td>";
  $xtHTML .= "<td><b>"._("Type")."</b></td>";
  $xtHTML .= "<td><b>"._("Description/compte de prélèvement")."</b></td>";
  $xtHTML .= "<td><b>"._("Valeur")."</b></td>";
  $xtHTML .= "<td><b>"._("Etat")."</b></td>";
  $xtHTML .= "<td>&nbsp</td>";
  $xtHTML .= "<td>&nbsp</td></tr>";

  // Parcours des dossiers
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // Si on vient de Rdo-4 alors mémoriser les données modifiables
    if (strstr($global_nom_ecran_prec,"Rdo-4")) {
         $SESSION_VARS['infos_doss'][$id_doss]["nouv_duree_mois"] = $ {'nouv_duree_mois'.$id_doss};

        // Récupérations des dossiers fictifs dans le cas de GS avec dossier unique
        if ($SESSION_VARS['infos_doss'][$id_doss]["gs_cat"] == 1) {
          foreach($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic=>$val_fic) {
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['obj_dem'] = $ {'obj_dem_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['detail_obj_dem'] = $ {'detail_obj_dem_fic'.$id_fic};
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'][$id_fic]['mnt_dem'] = recupMontant($ {'mnt_dem_fic'.$id_fic});
          }
        }
    } // fin si on vient de Rdo-4

    // Contenu du tableau */
    debug($val_doss['DATA_GAR']);
    foreach($val_doss['DATA_GAR'] as $key=>$value ) {
      if ($value['type'] != '') { /* Si la garantie n'est pas supprimée */
        /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
        if ($value['type'] ==1) { /* Garantie numéraire */
          /* Infos du compte de prélèvement des garanties */
          $CPT_PRELEV_GAR = getAccountDatas($value['descr_ou_compte']);
          $origine  = $CPT_PRELEV_GAR["num_complet_cpte"]." ".$CPT_PRELEV_GAR['intitule_compte'] ;

        }
        elseif($value['type'] == 2) { /* garantie matérielle */
          $origine = $value['descr_ou_compte'];
        }

        $id_client = $val_doss['id_client'];
        $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
        $xtHTML .= "<td>".$id_client." ".getClientName($id_client)."</td>";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_type_garantie"][$value['type']])."</td>";
        $xtHTML .= "<td>".$origine."</td>";
        $xtHTML .= "<td>".afficheMontant($value['valeur'])."</td>";
        $xtHTML .= "<td>".adb_gettext($adsys["adsys_etat_gar"][$value['etat']])."</td>";
        $xtHTML .= "<td><a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rdo-8&benef=".$id_doss."&num_gar=".$key."\">"._("Mod")."</a></td>";
        $xtHTML .= "<td><a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rdo-9&benef=".$id_doss."&num_gar=".$key."\">"._("Sup")."</a></td></tr>";
      }
    }

    $xtHTML .= "</table><br><br>";
  } // Fin parcours dossiers

  $Myform->addHTMLExtraCode ("garanties", $xtHTML);
  $Myform->addFormButton(1,1,"ajout_gar", _("Nouvelle garantie"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Rdo-4');
  $Myform->setFormButtonProperties("ajout_gar", BUTP_PROCHAIN_ECRAN, 'Rdo-7');

  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Rdo-7 : Ajout d'une garantie */
else if ($global_nom_ecran == "Rdo-7") {
  // Devise du produit de crédit
  setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);

  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Ajout d'une garantie"));

  // Liste de choix du client (ou dossier) bénéficiare
  $choix = array();
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss)
  $choix[$id_doss] = $val_doss['id_client']." ".getClientName($val_doss['id_client']);

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
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Rdo-6');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Rdo-10');

  /* Contrôle des champs à renseigner selon le type de garantie  */
  $JS_valide ="";
  $JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1 && document.ADForm.gar_num_id_cpte_prelev.value =='')";
  $JS_valide .="\n\t{msg+='"._("Le compte de prélèvement des garanties doit être renseigné")."'; ADFormValid = false;}";

  $JS_valide .="\n\tif( (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.libel_gar_mat.value =='') ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.HTML_GEN_LSB_type_bien.value == 0) ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.num_client_rel.value == '')) ";
  $JS_valide .="\n\t{msg+='"._("La description, le type du matériel et le cleint garant doivent être renseignés")."'; ADFormValid = false;}";

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

/*{{{ Rdo-8 : Modification d'une garantie */
else if ($global_nom_ecran == "Rdo-8") {
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
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Rdo-6');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Rdo-10');

  /* Contrôle des champs à renseigner selon le type de garantie  */
  $JS_valide ="";
  $JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1 && document.ADForm.gar_num_id_cpte_prelev.value =='')";
  $JS_valide .="\n\t{msg+='Le compte de prélèvement des garanties doit être renseigné'; ADFormValid = false;}";

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
  $JS_active .="\ndocument.ADForm.etat_gar.selectedIndex=2;";
  $JS_active .="\ndocument.ADForm.etat_gar.disabled = true;";
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
  $JS_active .="\n\tdocument.ADForm.etat_gar.disabled = false;";
  $JS_active .="\ndocument.ADForm.etat_gar.selectedIndex=0;";
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

/*{{{ Rdo-9 : Suppression d'une garantie */
else if ($global_nom_ecran == "Rdo-9") {
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
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Rdo-6');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("supprimer", BUTP_PROCHAIN_ECRAN, 'Rdo-10');

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Rdo-10 : Confirmation d'ajout, de modification ou de suppression d'une garantie */
else if ($global_nom_ecran == "Rdo-10") {
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
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] = $num_id_cpte_prelev;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type_bien'] = NULL;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['num_client'] = NULL;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur'] = recupMontant($montant_vente);
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['etat'] = 2; /* Prête pour les numéraires car bloquées dans le compte */
    } else if ($type_gar == 2) {
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['benef'] = $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['id_client'];
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] = $libel_gar_mat;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['num_client'] = $num_client_rel;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type_bien'] = $HTML_GEN_LSB_type_bien;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['piece_just'] = $piece_just;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['remarq'] = $remarq;
      $SESSION_VARS['infos_doss'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur'] = recupMontant($montant_vente);
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

  $Myform->addButton(BUTTON_OK, 'Rdo-6');
  $Myform->buildHTML();
  echo $Myform->HTML_code;
}
/*}}}*/

/*{{{ Rdo-5 : Blocage des garanties */
else if ($global_nom_ecran == "Rdo-5") {

  $formConf = new HTML_GEN2(_("Blocage des garanties"));

  $msg = '';
  $order = array();

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $valeur_totale_bloquee = $val_doss['garantie']; // total à bloquer
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
          $msg = _("Impossible de continuer cette opération, le solde du compte de prélèvement des garanties est insuffisant.")."<br/><ul><li>"._("Montant de la garantie")." : ".afficheMontant($mnt_gar_mob, true)."</li><li>"._("Solde du compte de prélèvement")." : ".afficheMontant($soldeB, true)."</li></ul>";

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
      $msg = _("Impossible de continuer cette opération, le montant mobilisé est insuffisant pour le dossier")." $id_doss.<br /><ul><li>"._("Montant de la garantie")." : ".afficheMontant($valeur_totale_bloquee, true)."</li><li>"._("Valeur totale mobilisée")." : ".afficheMontant($valeur_totale_mobilisee, true)."</li></ul>";

  } // Foreach dossiers

  /* Si une erreur s'est produite */
  if ($msg != '') {
    $erreur = new HTML_erreur(_("Blocage des garanties"));

    $erreur->setMessage($msg);
    $erreur->addButton(BUTTON_OK,"Gen-11");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    exit();
  }

  // les boutons ajoutés
  $formConf->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $formConf->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  $formConf->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rdo-3");
  $formConf->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");

  //$order = array("num_complet_cpte","solde","mnt_bloq","mnt","soldeF");
  $formConf->setOrder(NULL,$order);
  $formConf->buildHTML();
  echo $formConf->getHTML();

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>