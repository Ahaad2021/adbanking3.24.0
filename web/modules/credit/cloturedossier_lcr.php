<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [610] Clôturer dossier de crédit
 *
 * Cette opération comprends les écrans :
 * - LCdr-1 : Sélection d'un dossier de crédit
 * - LCdr-2 : Clôture dossier ligne de crédit
 * - LCdr-3 : Confirmation de la clôture
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'lib/misc/VariablesGlobales.php';

/*{{{ LCdr-1 : Sélection d'un dossier de crédit */

//*********************************************************

if ($global_nom_ecran == "LCdr-1") {
  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);
  unset($SESSION_VARS['infos_doss']);
  $dossiers = array(); // tableau contenant les infos sur les dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste box des dossiers à afficher
  $i = 1; // clé de la liste box

  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  // Récupération des dossiers individuels réels (dans ad_dcr) à l'état accepté
  $whereCl = " AND is_ligne_credit='t' AND mode_calc_int=5 AND etat=5"; // Le dossier droit être en attente de décision
  $dossiers_reels = getIdDossier($global_id_client, $whereCl);
  if (is_array($dossiers_reels)) {
    foreach($dossiers_reels as $id_doss=>$value) {
        if ($value['gs_cat'] != 2 && !isPeriodeNettoyageLcr($id_doss, $value["duree_nettoyage_lcr"])) { // les dossiers pris en groupe doivent être déboursés via le groupe
          $date = pg2phpDate($value["date_dem"]); //Fonction renvoie  des dates au format jj/mm/aaaa
          $liste[$i] ="n° $id_doss du $date"; //Construit la liste en affichant N° dossier + date
          $dossiers[$i] = $value;

          $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
          $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value["libelle"] . "\";";
          $codejs .= "}\n";
          $i++;
        }
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
      if (($val['is_ligne_credit'] == 't') AND ($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 5)) {
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
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "LCdr-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
  if (sizeof( $dossiers)<=0) {
    $msg = new HTML_message(_("Rejet dossier de crédit"));
    $msg->setMessage(_("Aucun dossier de crédit correspondant à un de ces états n'a été trouvé pour ce client !"));
    $msg->addButton(BUTTON_OK,"Lcr-1");
    $msg->buildHTML();
    echo $msg->HTML_code;
  }
}

/*}}}*/

/*{{{ LCdr-2 : Clôture dossier ligne de crédit */

else if ($global_nom_ecran == "LCdr-2") {
  // Si on vient de LCdr-1, on récupère les infos de la BD
  //if (strstr($global_nom_ecran_prec,"LCdr-1")) {
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
        if (($val['is_ligne_credit'] == 't') AND ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id']) AND ($val['etat']==1)) {
          $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
          $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
        }
      }
    }

      $MyPage = new HTML_GEN2(_("Clôture dossier ligne de crédit"));
      foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
          $mnt = 0;
          $whereCond="WHERE (id_doss='".$id_doss."')";
          $echeance = getEcheancier($whereCond);

          //Tableau des echéances
          $nom_cli = getClientName($val_doss['id_client']);
          $MyPage->addHTMLExtraCode("espace".$id_doss,"<b><p align=center><b>".sprintf(_("Clôture du dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");
          $color = $colb_tableau;
          $retour = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
          $retour .= "<TR bgcolor=\"$color\">\n";
          $retour .= "<TD colspan=8 align=\"left\"><b>"._("Echéances du crédit")."</b></TD>\n";
          $retour .= "</TR>\n";
          $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
          $retour .= "<TD align=\"center\">"._("Numéro")."</TD>\n";
          $retour .= "<TD align=\"center\">"._("Date")."</TD>\n";
          $retour .= "<TD align=\"center\">"._("Capital restant du")."</TD>\n";
          $retour .= "<TD align=\"center\">"._("Frais restants dus")."</TD>\n";
          $retour .= "<TD align=\"center\">"._("Intérêts restants dus")."</TD>\n";
          $retour .= "<TD align=\"center\">"._("Garantie restante due")."</TD>\n";
          $retour .= "<TD align=\"center\">"._("Pénalités dues")."</TD>\n";
          $retour .= "<TD align=\"center\">"._("Total du")."</TD>\n";
          $retour .= "</TR>\n";

          // Affichage
          $total_cap=0;
          $total_frais=0;
          $total_int=0;
          $total_pen=0;
          $total_gar=0;
          $infoEch=array();

          $today = date("d/m/Y");

          if (is_array($echeance)) {
              while (list($key,$info) = each($echeance)) {
                  $info["solde_frais"] = getCalculFraisLcr($id_doss, php2pg($today));
                  $info["solde_int"] = getCalculInteretsLcr($id_doss, php2pg($today));

                  $total_cap +=$info["solde_cap"]; //Somme du capital dû
                  $total_frais +=$info["solde_frais"]; //Somme des frais dûs
                  $total_int +=$info["solde_int"]; //Somme des intérêts dûs
                  $total_gar +=$info["solde_gar"]; //Somme de la garantie dûe
                  $total_pen +=$info["solde_pen"]; //Somme des pénalités dûes

                  array_push($infoEch,$info["solde_cap"]+$info["solde_frais"]+$info["solde_int"]+$info["solde_pen"]+$info["solde_gar"]); //Montant par échéance

                  $color = ($color == $colb_tableau? $colb_tableau_altern : $colb_tableau);
                  $retour .= "<TR bgcolor=\"$color\">\n";
                  $retour .= "<TD align=\"center\">".$info["id_ech"]."</TD>\n";
                  $retour .= "<TD align=\"left\">".pg2phpDate($info["date_ech"])."</TD>\n";
                  $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_cap"],false)."</TD>\n";
                  $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_frais"],false)."</TD>\n";
                  $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_int"],false)."</TD>\n";
                  $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_gar"],false)."</TD>\n";
                  $retour .= "<TD align=\"right\">".afficheMontant ($info["solde_pen"],false)."</TD>\n";
                  $retour .= "<TD align=\"right\">".afficheMontant (($info["solde_cap"]+$info["solde_pen"]+$info["solde_frais"]+$info["solde_int"]+$info["solde_gar"]),false)."</TD>\n";
                  $retour .= "</TR>\n";
              }
          }
          $mnt_total_du = $total_cap + $total_frais + $total_int + $total_pen + $total_gar;
          $SESSION_VARS["infoEch"][$id_doss] = $infoEch;

          $retour .= "</TABLE>\n";
          $MyPage->addHTMLExtraCode("ech1".$id_doss,$retour);
          $MyPage->setHTMLExtraCodeProperties("ech1".$id_doss, HTMP_IN_TABLE, true);
      }

      //Boutons
      $MyPage->addFormButton(1,1,"valid",_("Valider"),TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "LCdr-3");
      $MyPage->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "LCdr-1");
      $MyPage->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
      $MyPage->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Lcr-1");
      $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

      $MyPage->buildHTML();
      echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ LCdr-3 : Confirmation clôture */
else if ($global_nom_ecran == "LCdr-3") {

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
      // Clôture du dossier
      $myErr = clotureCredit($id_doss);
      if ($myErr->errCode == NO_ERR) {

          if ($myErr->param) {
              $msg = new HTML_message(_("Confirmation clôture du dossier de crédit"));
              $msg->setMessage(_("Le dossier de crédit est passé avec succès à l'état soldé !"));
              $msg->addButton(BUTTON_OK,"Lcr-1");
              $msg->buildHTML();
              echo $msg->HTML_code;
          } else {
              $erreur = new HTML_erreur(_("Clôture dossier de crédit"));
              $erreur->setMessage(_("Echec : la clôture du dossier n'est pas possible car le crédit n'a pas été soldé complétement !"));
              $erreur->addButton(BUTTON_OK,"Lcr-1");
              $erreur->buildHTML();
              echo $erreur->HTML_code;
          }
      } else {
        $erreur = new HTML_erreur(_("Clôture dossier de crédit"));
        $erreur->setMessage(_("Erreur : le dossier de crédit n'est pas passé à l'état soldé !"));
        $erreur->addButton(BUTTON_OK,"Lcr-1");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
      }
  }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>