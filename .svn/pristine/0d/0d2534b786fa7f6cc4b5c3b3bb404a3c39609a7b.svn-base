<?php
/*
  Visualisation et traitement des attentes de crédits suivant plusieurs critères

Ecrans :
//------------- ECRAN Att-1 : sélection des critères de recherche ------------------------
//------------- ECRAN Att-2 : affichage du résultat de la recherche ----------------------
//------------- ECRAN Att-3 : Confirmation de l'operation d'encaissement -----------------
//------------- ECRAN Att-4 : Confirmation de l'operation de rejet -----------------------
//------------- ECRAN Att-5 : Opérations sur les attentes acceptée/envoyée ---------------
//------------- ECRAN Att-6 : Opérations sur les chèques à refuser -----------------------
//------------- ECRAN Att-7 : Introduction manuelle d'une attente  -----------------------
//------------- ECRAN Att-8 : Introduction du montant de l'attente manuelle --------------
//------------- ECRAN Att-9 : Confirmation de l'introduction de l'attente manuelle ------
*/

require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/parametrage.php');

//Si la variable Att4 est alimentée, on vient de sélectionner une attente à rejeter dans l'écran Att-2.
if (isset($Att4) && $Att4!='') $global_nom_ecran = "Att-4";
//----------------------------------------------------------------------------------------
//------------- ECRAN Att-1 : sélection des critères de recherche ------------------------
//----------------------------------------------------------------------------------------
if ($global_nom_ecran == "Att-1") {
  $html = new HTML_GEN2(_("Critères de recherche"));

  //Popup Client
  $html->addField("num_client", _("N° de client"), TYPC_INT);
  $html->addLink("num_client", "rechercher", _("Rechercher"), "#");
  $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."', '"._("Recherche")."');return false;"));

  //Champs banque
  $libels=getLibelBanque();
  $html->addField("banque",_("Banques"), TYPC_LSB);
  $html->setFieldProperties("banque", FIELDP_ADD_CHOICES, $libels);
  $html->setFieldProperties("banque", FIELDP_HAS_CHOICE_AUCUN, false);
  $html->setFieldProperties("banque", FIELDP_HAS_CHOICE_TOUS, true);

  //Date minimale
  $html->addField("dateDeb", _("Date de dépôt début :"), TYPC_DTE);
  $html->setFieldProperties("dateDeb",  FIELDP_HAS_CALEND, false);
  $html->addLink("dateDeb", "calendrier1", _("Calendrier"), "#");
  $codejs = "if (!isDate(document.ADForm.HTML_GEN_date_dateDeb.value)) ";
  $codejs .= "document.ADForm.dateDeb.value='';open_calendrier(getMonth(document.ADForm.HTML_GEN_date_dateDeb.value), getYear(document.ADForm.HTML_GEN_date_dateDeb.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_dateDeb');return false;";
  $html->setLinkProperties("calendrier1", LINKP_JS_EVENT, array("onclick" => $codejs));

  //Date maximale
  $html->addField("dateFin", _("Date de dépôt fin :"), TYPC_DTE);
  $html->setFieldProperties("dateFin",  FIELDP_HAS_CALEND, false);
  $html->addLink("dateFin", "calendrier2", _("Calendrier"), "#");
  $codejs = "if (!isDate(document.ADForm.HTML_GEN_date_dateFin.value)) ";
  $codejs .= "document.ADForm.HTML_GEN_date_dateFin.value='';open_calendrier(getMonth(document.ADForm.HTML_GEN_date_dateFin.value), getYear(document.ADForm.HTML_GEN_date_dateFin.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_dateFin');return false;";
  $html->setLinkProperties("calendrier2", LINKP_JS_EVENT, array("onclick" => $codejs));

  //Informations bénéficiaire (seulement dans le cas du chèque)
  $html->addField("nom_ben", _("Nom du tireur"), TYPC_TXT);
  $html->addHiddenType("id_ben","");

  $html->addLink("nom_ben", "rechercher2", _("Rechercher"), "#");
  $html->setLinkProperties("rechercher2", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=a', '"._("Recherche")."');return false;"));

  //Correspondant bancaire (seulement dans le cas du chèque)
  $libel_correspondant=getLibelCorrespondant();
  $html->addField("correspondant", _("Correspondant bancaire"), TYPC_LSB);
  $html->setFieldProperties("correspondant", FIELDP_ADD_CHOICES, $libel_correspondant);
  $html->setFieldProperties("correspondant", FIELDP_HAS_CHOICE_AUCUN, false);
  $html->setFieldProperties("correspondant", FIELDP_HAS_CHOICE_TOUS, true);

  // TF - Génération d'une listBox contenant les différents états du chèque
  $html->addField("etat_chq", _("Etat"), TYPC_LSB);
  $html->setFieldProperties("etat_chq", FIELDP_ADD_CHOICES, array(1 => _("En attente"), 2 => _("Envoyée"), 3 => _("Acceptée"), 4 => _("Refusée"), 5 => _("A rembourser"), 6=> _("Remboursé"), 99=> _("En attente/envoyée/A rembourser")));
  $html->setFieldProperties("etat_chq", FIELDP_DEFAULT, 1);//Par défaut, on n'affiche que le statut = 'en attente'
  $html->setFieldProperties("etat_chq", FIELDP_IS_REQUIRED, false);
  $html->setFieldProperties("etat_chq", FIELDP_HAS_CHOICE_AUCUN, false);
  $html->setFieldProperties("etat_chq", FIELDP_HAS_CHOICE_TOUS, true);

  $html->addField("sens", _("Sens"), TYPC_LSB);
  $html->setFieldProperties("sens", FIELDP_ADD_CHOICES, array(1 => _("In (client bénéficiaire)"), 2 => _("Out (client débiteur)")));
  $html->setFieldProperties("sens", FIELDP_HAS_CHOICE_AUCUN, false);
  $html->setFieldProperties("sens", FIELDP_HAS_CHOICE_TOUS, true);

  $html->addField("type_piece", _("Type de pièce"), TYPC_LSB);
  $liste_type_paiement = getListeTypePieceComptables();
  foreach ($liste_type_paiement as $key=>$value) {
    //Attentes possibles : 2:chèque, 3:ordre de paiement, 5:Travelers Cheque, 6:mise à disposition, 7:envoi d'argent
    if ($key!=2 and $key!=3 and $key!=5 and $key!=6 and $key!=7) {
      unset($liste_type_paiement[$key]);
    }
  }
  $html->setFieldProperties("type_piece", FIELDP_ADD_CHOICES, $liste_type_paiement);
  $html->setFieldProperties("type_piece", FIELDP_HAS_CHOICE_AUCUN, false);
  $html->setFieldProperties("type_piece", FIELDP_HAS_CHOICE_TOUS, true);

  //Choix des lignes d'informations à afficher
  $html->addField("affDeb", _("Afficher le débiteur"), TYPC_BOL);
  $html->setFieldProperties("affDeb", FIELDP_DEFAULT, true);
  $html->addField("affDest", _("Afficher le bénéficiaire"), TYPC_BOL);
  $html->setFieldProperties("affDest", FIELDP_DEFAULT, true);
  $html->addField("affComm", _("Afficher la communication"), TYPC_BOL);
  $html->addField("affRem", _("Afficher la remarque"), TYPC_BOL);
  $html->addField("affCorr", _("Afficher le Correspondant"), TYPC_BOL);


  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Retour Menu"), TYPB_SUBMIT);
  $html->addFormButton(1,3, "nouveau", _("Attente manuelle"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Att-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-6');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("nouveau", BUTP_PROCHAIN_ECRAN, 'Att-7');

  $html->buildHTML();
  echo $html->getHTML();
}
//----------------------------------------------------------------------------------------
//------------- ECRAN Att-2 : affichage du résultat de la recherche ----------------------
//----------------------------------------------------------------------------------------
else if ($global_nom_ecran == "Att-2") {
  $criteres=array();
  $ok = TRUE;
  if (isset($SESSION_VARS['enAttente'])) unset ($SESSION_VARS['enAttente']);
  //vérifier que le numéro de client est correct
  if (! empty($num_client)) {
    $details = getClientDatas($num_client);
    if ($details == NULL) { //Si le client n'existe pas
      $erreur = new HTML_erreur(_("Client inexistant"));
      $erreur->setMessage(_("Le numéro de client entré ne correspond à aucun client valide"));
      $erreur->addButton(BUTTON_OK, "Gen-6");
      $erreur->addCustomButton("nouv1", _("Nouvelle recherche"), "Att-1");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = FALSE;
    } else { //Si le client existe
      $criteres['num_client']=$num_client;
    }
  }

  if ($ok) {
    if (check_access(188)) $traitementPossible = true;
    else $traitementPossible = false;

    //générer la requête SQL en fonction des critères saisis
    if ($banque!=0)        $criteres['id_banque']     = $banque;
    if ($correspondant!=0) $criteres['correspondant'] = $correspondant;
    if ($etat_chq!=0)      $criteres['etat_chq']      = $etat_chq;
    if ($id_ben!='')       $criteres['id_ben']        = $id_ben;
    if ($dateDeb!='')      $criteres['dateDeb']       = $dateDeb;
    if ($dateFin!='')      $criteres['dateFin']       = $dateFin;
    switch ($sens) {
    case 1:
      $criteres['sens']          = "in ";
      break;
    case 2:
      $criteres['sens']          = "out";
      break;
    }
    if ($type_piece!=0)    $criteres['type_piece']    = $type_piece;
    $listeAttentes=getListeAttentes($criteres);
    $nombreAttentes = count($listeAttentes);
    if (empty($listeAttentes)) {
      $html_msg = new HTML_message(_("Résultats de la recherche"));
      $html_msg->setMessage(_("Aucune attente n'a été trouvée"));
      $html_msg->addButton(BUTTON_OK, 'Att-1');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    } else {
      //On sauve le résultat de la requête pour procéder aux opérations d'acceptation ou de rejet par la suite.
      $SESSION_VARS["listeAttentes"] = $listeAttentes;

      //Génération du titre de l'écran de résultats en fonciton du critère : encaissé, rejeté ou en attente
      if (isset($criteres['etat_chq']))
        switch ($criteres['etat_chq']) {
        case 1  :
          $titre = _("Attentes en attente");
          break;
        case 2  :
          $titre = _("Attentes envoyées");
          break;
        case 3  :
          $titre = _("Attentes acceptées");
          break;
        case 4  :
          $titre = _("Attentes rejetées");
          break;
        case 5  :
          $titre = _("Attentes à rembourser");
          break;
        case 6  :
          $titre = _("Attentes remboursées");
          break;
        case 99  :
          $titre = _("Attentes en attente/envoyées/à rembourser");
          break;
        }
      else $titre = _("Liste des attentes ");
      $titre .= " (".$nombreAttentes.")";

      $html = new HTML_GEN2($titre);

      //paramètres pour l'affichage de certaines lignes (numéro, débiteur, destinataires, communication, remarque)
      $nbLigne=1;
      if (isset($affDeb))  $nbLigne++;
      if (isset($affDest)) $nbLigne++;
      if (isset($affComm)) $nbLigne++;
      if (isset($affRem))  $nbLigne++;
      if (isset($affCorr))  $nbLigne++;
      $libel_correspondant=getLibelCorrespondant();

      //tableau HTML contenant les résultats de la recherche
      $extraHTML = "
                   <br />
                   <table width=\"95%\">";

      $codeModif="";
      if ($traitementPossible) {
        $codeModif="<td></td>";
        $i=0;
      }
      $extraHTML .= "
                    <tr bgcolor=\"$colb_tableau\" align=\"center\">
                    $codeModif
                    <td><b>"._("Etat")."</b></td>
                    <td colspan=\"3\"><b>"._("Informations")."</b></td>
                    </tr>";

      $color = $colb_tableau;
      //récupérer la liste des pièces comptables
      $liste_type_piece = getListeTypePieceComptables();
      foreach ($listeAttentes as $key=>$value) {
        $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);
        $Tmp_dte1 = pg2phpDatebis($value["date_piece"]);
        $Tmp_dte2 = $Tmp_dte1[1]."/".$Tmp_dte1[0]."/".$Tmp_dte1[2];
        setMonnaieCourante($value['devise']);
        $tabMontant=afficheMontant($value['montant'],true);
        $tabDe   = $value['donneurOrdre'];
        $tabVers = $value['beneficiaire'];
        $tabEtat = adb_gettext($adsys['adsys_etat_ac'][$value['etat']]);
        $tabType = $liste_type_piece[$value['type_piece']];
        $tabSens = $value['sens'];
        $tabNum  = $value['num_piece'];
        $tabRem  = $value['remarque'];
        $tabComm = $value['communication'];
        if ($value['id_correspondant'] > 0) $tabCorr = $libel_correspondant[$value['id_correspondant']];
        else $tabCorr = "";
        $codeModif = "";
        if ($traitementPossible) {
          $codeModif = "
                       <td></td>";//première ligne de l'attente
          $codeModif2 = "
                        <td rowspan=\"$nbLigne\"></td>";//lignes suivantes de l'attente
          $codeRejet ="";
          if ($value['etat']==1 || $value['etat']==2) { //envoyée ou en attente
            if ($value['type_piece']!=6) { //dans le cas des mise à dispo, pas de case à cocher (les cas sont à traiter individuellement)
              $codeModif = "
                           <td><input type=\"checkbox\" name=\"valide".$i."\"></td>";
            }
            //La variable suivante stocke les ID des attentes "traitables" et l'associe à l'ID de la checkbox
            $SESSION_VARS['enAttente'][$i]=$value['id'];
            $i++;
            if ($value['type_piece']==6 || $value['etat']==2) {
              $codeRejet = "<a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Att-4&id=".$value['id']."\">"._("Rejet")."</a>";
              if ($value['type_piece']==6) {
                $codeRejet .= "<br /><a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Att-4&id=".$value['id']."&action=encaisse\">"._("Encaissement")."</a>";
              }
            }
          }
          if ($value['etat']==5) { //A rembourser
            $codeRejet = "<a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Att-4&id=".$value['id']."&action=encaisse\">"._("Rembourser")."</a>";
            //La variable suivante stocke les ID des attentes "traitables" et l'associe à l'ID de la checkbox
            $SESSION_VARS['enAttente'][$i]=$value['id'];
            $i++;
          }
        }
        $extraHTML .= "
                      <tr bgcolor=\"$color\" align=\"center\">$codeModif
                      <td>$tabEtat</td>
                      <td colspan=\"3\">$tabType du $Tmp_dte2 de <b>$tabMontant</b> ($tabSens)</td>
                      <tr bgcolor=\"$color\" align=\"left\">$codeModif2
                      <td rowspan=\"$nbLigne\">$codeRejet</td>
                      <td colspan=\"3\"><b>"._("Numéro")." :</b> $tabNum</td>";
        if (isset($affDeb)) $extraHTML.= "
                                           <tr bgcolor=\"$color\" align=\"left\">
                                           <td><b>"._("de")."</b></td>
                                           <td colspan=\"2\">$tabDe</td></tr>";
        if (isset($affDest)) $extraHTML.="
                                           <tr bgcolor=\"$color\" align=\"left\">
                                           <td><b>"._("vers")."</b></td>
                                           <td colspan=\"2\">$tabVers</td></tr>";
        if (isset($affComm)) $extraHTML.="
                                           <tr bgcolor=\"$color\" align=\"left\">
                                           <td colspan=\"3\"><b>"._("Communication")."</b> : $tabComm</td></tr>";
        if (isset($affRem)) $extraHTML.="
                                          <tr bgcolor=\"$color\" align=\"left\">
                                          <td colspan=\"3\"><b>"._("Remarque")."</b> : $tabRem</td></tr>";
        if (isset($affCorr)) $extraHTML.="
                                           <tr bgcolor=\"$color\" align=\"left\">
                                           <td colspan=\"3\"><b>"._("Correspondant")."</b> : $tabCorr</td></tr>";
      }

      if (isset($i)) $SESSION_VARS["i"] = $i;

      $extraHTML .= "
                    </table>";

      $html->addHTMLextraCode("tableau", $extraHTML);

      if ($traitementPossible) {
        $codejs ="
                 function clickSelectTous()
               {
                 var MyCheckBox;
                 for (var i=0; i < ".$i.";i++)
               {
                 MyCheckBox = 'valide'+i;
                 eval('document.ADForm.'+MyCheckBox+'.checked=true');
               }
               }";
        $html->addJS(JSP_FORM, "JS6", $codejs);

        $codejs ="
                 function clickDeselectTous()
               {
                 var MyCheckBox;
                 for (var i=0; i < ".$i.";i++)
               {
                 MyCheckBox = 'valide'+i;
                 eval('document.ADForm.'+MyCheckBox+'.checked=false');
               }
               }";
        $html->addJS(JSP_FORM, "JS7", $codejs);

        $Xtrahtml1 = "
                     <input type=\"button\" name=\"btn_select\" value=\"Sélectionner tous\" onclick=\"clickSelectTous()\">
                     <input type=\"button\" name=\"btn_deselect\" value=\"Désélectionner tous\" onclick=\"clickDeselectTous()\">";
        $html->addHTMLExtraCode ("htm1", $Xtrahtml1);

        // Variable utilisée pour éviter que HTML_GEN2 ne génère pas les boutons de la deuxième ligne
        $num_ligne = 1;

        // On n'affiche le bouton Envoi que si on a recherché parmi les attentes 'en attente'
        if ($criteres["etat_chq"] == 1) {
          $html->addFormButton(1, 1, "btn_envoi", _("Envoi"), TYPB_SUBMIT);
          $html->setFormButtonProperties("btn_envoi", BUTP_PROCHAIN_ECRAN, 'Att-3');
          $num_ligne = 2;
        }

        // On n'affiche le bouton Encaissement que si on a recherché les attentes envoyées
        if ($criteres["etat_chq"] == 2) {
          $html->addFormButton(1, 2, "btn_encaisse", _("Encaissement"), TYPB_SUBMIT);
          $html->setFormButtonProperties("btn_encaisse", BUTP_PROCHAIN_ECRAN, 'Att-3');
          $num_ligne = 2;
        }

        $html->addFormButton($num_ligne, 1, "retour", "     "._("Précédent")."     ", TYPB_SUBMIT);
        $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Att-1');
        $html->addFormButton($num_ligne, 2, "cancel", "   "._("Annuler")."   ", TYPB_SUBMIT);
        $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-6');

      } else {
        $html->addFormButton(1, 1, "retour", "     "._("Précédent")."     ", TYPB_SUBMIT);
        $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Att-1');
        $html->addFormButton(1, 2, "cancel", " "._("Retour Menu")." ", TYPB_SUBMIT);
        $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Att-1');
      }

      $html->buildHTML();
      echo $html->getHTML();
    }
  }
}
//----------------------------------------------------------------------------------------
//------------- ECRAN Att-3 : Confirmation de l'operation d'encaissement -----------------
//----------------------------------------------------------------------------------------
else if ($global_nom_ecran == "Att-3") {
  $valide = array();

  for ($j=0; $j <= $SESSION_VARS["i"]; $j++) {
    $a = "valide".$j;
    if (isset($$a))
      array_push($valide, $j);
  }
  if (empty($valide)) {
    $html_msg =new HTML_message(_("Traitement des attentes"));
    $html_msg->setMessage(_("Aucune attente n'a été sélectionnée"));
    $html_msg->addButton("BUTTON_OK", 'Att-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    if (isset($btn_envoi)) {
      $message = _("Vous allez procéder à l'envoi des attentes")."<br />" ;
      $SESSION_VARS['operationAttente']='envoi';
    }
    if (isset($btn_encaisse)) {
      $message = _("Vous allez procéder à l'encaissement des attentes")." :<br />";
      $SESSION_VARS['operationAttente']='encaisse';
    }
    foreach($valide as $key=>$value) {
      $SESSION_VARS["attentes_checkees"][$key] = $SESSION_VARS['enAttente'][$value];
      $donneesAttente = $SESSION_VARS['listeAttentes'][$SESSION_VARS['enAttente'][$value]];
      setMonnaieCourante($donneesAttente['devise']);
      $ligneMontant=afficheMontant($donneesAttente['montant'],true);
      $message.="<br /> - ".$donneesAttente['nom_banque']." - ".$donneesAttente['num_complet']." (".$ligneMontant.")";
    }
    $html_msg =new HTML_message(_("Confirmation de l'opération sur attente"));
    $message .= "<br /><br />"._("Poursuivre l'opération ?");
    $html_msg->setMessage($message);
    $html_msg->addButton("BUTTON_OUI", 'Att-5');
    $html_msg->addButton("BUTTON_NON", 'Att-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}
//----------------------------------------------------------------------------------------
//------------- ECRAN Att-4 : Confirmation de l'operation de rejet ou de paiement --------
//----------------------------------------------------------------------------------------
else if ($global_nom_ecran == "Att-4") {
  if ($SESSION_VARS['listeAttentes'][$_GET['id']] == null ) {
    $html_msg =new HTML_message(_("Traitement des attentes"));
    $html_msg->setMessage(_("Aucune attente n'a été sélectionnée"));
    $html_msg->addButton("BUTTON_OK", 'Att-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    $html =new HTML_GEN2(_("Confirmation de l'opération sur attentes"));

    //tableau HTML contenant les résultats de la recherche
    $value=$SESSION_VARS['listeAttentes'][$_GET['id']];
    if (isset($action)) {
      $SESSION_VARS['encaisse']=$_GET['id'];
    } else {
      $SESSION_VARS['rejet']=$_GET['id'];
    }
    $tabDe   = $value['donneurOrdre'];
    $tabVers = $value['beneficiaire'];
    $liste_type_piece = getListeTypePieceComptables();
    $tabEtat = adb_gettext($adsys['adsys_etat_ac'][$value['etat']]);
    $tabType = $liste_type_piece[$value['type_piece']];
    $tabSens = $value['sens'];
    $tabNum  = $value['num_piece'];
    $tabRem  = $value['remarque'];
    $tabComm = $value['communication'];
    $libel_correspondant=getLibelCorrespondant();
    $devise_correspondant = NULL;
    if ($value['id_correspondant'] > 0) {
      $tabCorr = $libel_correspondant[$value['id_correspondant']];
      $datasCorrespondant = getInfosCorrespondant($value['id_correspondant']);
      $devise_correspondant = $datasCorrespondant['devise'];
    } else {
      $tabCorr = "";
    }

    $html->addField("type", _("Type"), TYPC_TXT);
    $html->setFieldProperties("type", FIELDP_DEFAULT, $tabType);
    $html->setFieldProperties("type", FIELDP_IS_LABEL, true);

    $html->addField("corr", _("Correspondant"), TYPC_TXT);
    $html->setFieldProperties("corr", FIELDP_DEFAULT, $tabCorr);
    $html->setFieldProperties("corr", FIELDP_IS_LABEL, true);

    $html->addField("date_piece", _("Date pièce"), TYPC_DTE);
    $html->setFieldProperties("date_piece", FIELDP_DEFAULT, $value['date_piece']);
    $html->setFieldProperties("date_piece", FIELDP_IS_LABEL, true);

    $html->addField("date_encod", _("Date encodage"), TYPC_DTE);
    $html->setFieldProperties("date_encod", FIELDP_DEFAULT, $value['date']);
    $html->setFieldProperties("date_encod", FIELDP_IS_LABEL, true);

    setMonnaieCourante($value['devise']);
    $html->addField("montant", _("Montant"), TYPC_MNT);
    $html->setFieldProperties("montant", FIELDP_DEFAULT, $value['montant']);
    $html->setFieldProperties("montant", FIELDP_IS_LABEL, true);

    $html->addField("sens", _("Sens"), TYPC_TXT);
    $html->setFieldProperties("sens", FIELDP_DEFAULT, $tabSens);
    $html->setFieldProperties("sens", FIELDP_IS_LABEL, true);

    $html->addField("numero", _("Numéro"), TYPC_TXT);
    $html->setFieldProperties("numero", FIELDP_DEFAULT, $tabNum);
    $html->setFieldProperties("numero", FIELDP_IS_LABEL, true);

    $html->addField("de", _("De"), TYPC_TXT);
    $html->setFieldProperties("de", FIELDP_DEFAULT, $tabDe);
    $html->setFieldProperties("de", FIELDP_IS_LABEL, true);

    $html->addField("vers", _("Vers"), TYPC_TXT);
    $html->setFieldProperties("vers", FIELDP_DEFAULT, $tabVers);
    $html->setFieldProperties("vers", FIELDP_IS_LABEL, true);

    $html->addField("communication", _("Communication"), TYPC_TXT);
    $html->setFieldProperties("communication", FIELDP_DEFAULT, $tabComm);
    $html->setFieldProperties("communication", FIELDP_IS_LABEL, true);

    $html->addField("remarque", _("Remarque"), TYPC_TXT);
    $html->setFieldProperties("remarque", FIELDP_DEFAULT, $tabRem);
    $html->setFieldProperties("remarque", FIELDP_IS_LABEL, true);

    if ($value['type_piece']!=6 && $value['type_piece']!=7) {
      if ($value['sens']=='in ') $compteClient = $value['id_cpt_benef'];
      if ($value['sens']=='out') $compteClient = $value['id_cpt_ordre'];
      $dataCompte=getAccountDatas($compteClient);
      setMonnaieCourante($dataCompte['devise']);
      $html->addField("frais_rejet", _("Frais de rejet (client)"), TYPC_MNT);
    }

    if ($devise_correspondant != null) {
      setMonnaieCourante($value['devise']);
      if (isset($action)) {
        $html->addField("frais_operation", _("Frais d'operation"), TYPC_MNT);
        $html->addField("total_guichet", _("A remettre au client"), TYPC_MNT);
        $html->setFieldProperties("total_guichet", FIELDP_DEFAULT, $value['montant']);
        $html->setFieldProperties("total_guichet", FIELDP_IS_LABEL, true);
        $html->setFieldProperties("frais_operation", FIELDP_JS_EVENT, array("onChange"=>"calculeTotal();"));
        $calcJS="
                function calculeTotal()
              {
                document.ADForm.total_guichet.value = formateMontant(recupMontant(document.ADForm.montant.value) - recupMontant(document.ADForm.frais_operation.value));
              }
                ";
        // Confirmation du montant décaissé
        $html->addField("mnt_decaisse", _("Montant décaissé"), TYPC_MNT);
        $html->setFieldProperties("mnt_decaisse", FIELDP_HAS_BILLET, true);

        $jsCheck = "if (recupMontant(document.ADForm.total_guichet.value) != recupMontant(document.ADForm.mnt_decaisse.value))
                 {
                   ADFormValid = false;
                   msg += '"._("Le montant décaissé ne correspond pas au montant total")."\\n';
                 }";
        $html->addJS(JSP_FORM, "JS2", $calcJS);
        $html->addJS(JSP_BEGIN_CHECK, "jsCheck", $jsCheck);
      } else {
        $html->addField("frais_correspondant", _("Frais de rejet (correspondant)"), TYPC_MNT);
      }
    }

    $html->addFormButton(1, 1, "valider", "     "._("Valider")."     ", TYPB_SUBMIT);
    $html->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Att-6');
    $html->addFormButton(1, 2, "cancel", "   "._("Annuler")."   ", TYPB_SUBMIT);
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Att-1');
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $html->buildHTML();
    echo $html->getHTML();
  }
}
//----------------------------------------------------------------------------------------
//------------- ECRAN Att-5 : Opérations sur les attentes acceptée/envoyée ---------------
//----------------------------------------------------------------------------------------
else if ($global_nom_ecran == "Att-5") {
  unset($error_message);
  if (isset($SESSION_VARS["attentes_checkees"])) {
    foreach($SESSION_VARS["attentes_checkees"] as $value) {
      $donneesAttente = $SESSION_VARS['listeAttentes'][$value];
      if ($SESSION_VARS['operationAttente']=='encaisse') {
        if ($donneesAttente['sens']=='in ' && ($donneesAttente['type_piece']==2 || $donneesAttente['type_piece']==5)) $erreur = accepteAttente($value);
        if ($donneesAttente['type_piece']==7) $erreur = accepteEnvoiArgent($value);
        if ($donneesAttente['sens']=='out' && $donneesAttente['type_piece']==3)
          $erreur = updateEtatAttente($value,3); // On accepte l'attente dans le cas de l'ordre de paiement (il n'y a aucune opération comptable à faire).
      }
      if ($SESSION_VARS['operationAttente']=='envoi')
        $erreur = envoiAttente($value);
      if ($erreur->errCode != NO_ERR) {
        global $error;
        setMonnaieCourante($donneesAttente['devise']);
        $ligneMontant=afficheMontant($donneesAttente['montant'],true);
        $error_message = _("Erreur sur l'attente suivante")." : ";
        $error_message.="<br /> - ".$donneesAttente['nom_banque']." - ".$donneesAttente['num_complet']." (".$ligneMontant.")";
        $error_message .= "<br />"._("Nature de l'erreur")." : ".$error[$erreur->errCode]."<br />"._("Paramètre")." : ".$erreur->param;
        break;
      }
    }
    unset($SESSION_VARS['attentes_checkees']);
  }

  if (isset($error_message)) {
    $html_err = new HTML_erreur(_("Echec du traitement de l'attente"));
    $html_err->setMessage($error_message);
    $html_err->addButton("BUTTON_OK", 'Att-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $html_msg =new HTML_message(_("Confirmation"));
    if (count($SESSION_VARS["attentes_checkees"]) > 1)
      $msg = _("Attentes validées");
    else
      $msg = _("Attente validée");
    if (isset($erreur->param))
      $msg .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param)."</code></B>";
    $html_msg->setMessage($msg);
    $html_msg->addButton("BUTTON_OK", 'Att-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}
//----------------------------------------------------------------------------------------
//------------- ECRAN Att-6 : Opérations sur les chèques à refuser -----------------------
// ---------------------------------------------------------------------------------------
else if ($global_nom_ecran == "Att-6") {
  if (isset($SESSION_VARS["rejet"])) {
    $value=$SESSION_VARS['listeAttentes'][$SESSION_VARS['rejet']];
    if ($value['type_piece']==7) { //envoi d'argent par un non-client
      $frais_correspondant = recupMontant($frais_correspondant);
      $erreur = rejetEnvoi($SESSION_VARS['rejet'], $frais_correspondant);
    } else {
      $frais_correspondant = recupMontant($frais_correspondant);
      $frais_rejet = recupMontant($frais_rejet);
      $erreur = rejetAttente($SESSION_VARS['rejet'], $frais_rejet, $frais_correspondant);
    }
    $messageErreur = _("Echec du rejet de l'attente");
    $messageConfirmation = _("Attente rejetée");
    unset($SESSION_VARS['rejet']);
  }
  if (isset($SESSION_VARS['encaisse'])) {
    $frais_operation = recupMontant($frais_operation);
    $erreur = paiementGuichet($SESSION_VARS['encaisse'], $frais_operation);
    $messageErreur = _("Echec de l'encaissement de l'attente");
    $messageConfirmation = _("Attente payée");
    unset($SESSION_VARS['encaisse']);
  }

  if ($erreur->errCode != NO_ERR) {
    $html_err = new HTML_erreur($messageErreur);
    $html_err->setMessage($error[$erreur->errCode]."<BR/>".$erreur->param);
    $html_err->addButton("BUTTON_OK", 'Att-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $html_msg =new HTML_message(_("Confirmation"));
    $messageConfirmation .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param)."</code></B>";
    $html_msg->setMessage($messageConfirmation);
    $html_msg->addButton("BUTTON_OK", 'Att-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}
//----------------------------------------------------------------------------------------
//------------- ECRAN Att-7 : Introduction manuelle d'une attente  -----------------------
//----------------------------------------------------------------------------------------
else if ($global_nom_ecran == "Att-7") {
  $html = new HTML_GEN2(_("Introduction d'une attente"));

  //Informations donneur d'ordre
  $html->addField("nom_ordre", _("Donneur d'ordre"), TYPC_TXT);
  $html->setFieldProperties("nom_ordre", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("nom_ordre", FIELDP_WIDTH, 40);
  $html->addHiddenType("id_ordre","");
  $html->addLink("nom_ordre", "rechercher2", _("Rechercher"), "#");
  $html->setLinkProperties("rechercher2", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ordre&field_id=id_ordre&type=t', '"._("Recherche")."');return false;"));

  //Informations bénéficiaire
  $html->addField("nom_ben", _("Bénéficiaire"), TYPC_TXT);
  $html->setFieldProperties("nom_ben", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("nom_ben", FIELDP_WIDTH, 40);
  $html->addHiddenType("id_ben","");
  $html->addLink("nom_ben", "rechercher", _("Rechercher"), "#");
  $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=b', '"._("Recherche")."');return false;"));

  //Correspondant bancaire
  $libel_correspondant=getLibelCorrespondant();
  $html->addField("correspondant", _("Correspondant bancaire"), TYPC_LSB);
  $html->setFieldProperties("correspondant", FIELDP_ADD_CHOICES, $libel_correspondant);
  $html->setFieldProperties("correspondant",  FIELDP_IS_REQUIRED, true);

  //Champs banque
  $libels=getLibelBanque();
  $html->addField("banque",_("Banques"), TYPC_LSB);
  $html->setFieldProperties("banque", FIELDP_ADD_CHOICES, $libels);
  $html->setFieldProperties("banque",  FIELDP_IS_REQUIRED, true);

  //Sens
  $html->addField("sens", _("Sens"), TYPC_LSB);
  $html->setFieldProperties("sens", FIELDP_ADD_CHOICES, array(1 => _("In (mise à disposition)"), 2 => _("Out (envoi argent)")));
  $html->setFieldProperties("sens",  FIELDP_IS_REQUIRED, true);

  //Montant à retirer
  setMonnaieCourante($global_monnaie);
  $html->addField("frais",_("Frais transfert"),TYPC_MNT);
  $ordre = array("sens", "nom_ordre", "nom_ben", "correspondant", "banque", "frais");

  $html->addField("num_piece", _("Numéro"), TYPC_TXT);
  $html->setFieldProperties("num_piece",  FIELDP_IS_REQUIRED, true);

  $html->addField("remarque", _("Remarque"), TYPC_ARE);

  $html->addField("communication", _("Communication"), TYPC_TXT);

  array_push($ordre, "num_piece", "communication", "remarque");
  $html->setOrder(NULL, $ordre);

  $ChkJS ="
          if (document.ADForm.id_ben.value == '')
        {
          msg += ' - "._("Vous devez choisir un bénéficiaire")."\\n';
          ADFormValid=false;
        };
          if (document.ADForm.id_ordre.value == '')
        {
          msg += ' - "._("Vous devez choisir un donneur d\'ordre")."\\n';
          ADFormValid=false;
        };
          ";

  $html->addJS(JSP_BEGIN_CHECK, "JS1",$ChkJS);

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Att-8');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Att-1');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();

}
//----------------------------------------------------------------------------------------
//------------- ECRAN Att-8 : Introduction du montant de l'attente manuelle --------------
//----------------------------------------------------------------------------------------
else if ($global_nom_ecran == "Att-8") {
  $html = new HTML_GEN2(_("Introduction d'une attente (suite)"));

  //sauvegarde des données
  if (isset($correspondant)) $SESSION_VARS['dataAttente']['id_correspondant'] = $correspondant;
  if (isset($id_ben))        $SESSION_VARS['dataAttente']['id_ext_benef']     = $id_ben;
  if (isset($id_ordre))      $SESSION_VARS['dataAttente']['id_ext_ordre']     = $id_ordre;
  if (isset($sens)) {
    if ($sens==1)           $SESSION_VARS['dataAttente']['sens']             = 'in ';
    else                    $SESSION_VARS['dataAttente']['sens']             = 'out';
  }
  if (isset($num_piece))     $SESSION_VARS['dataAttente']['num_piece']        = $num_piece;
  if (isset($remarque))      $SESSION_VARS['dataAttente']['remarque']         = $remarque;
  if (isset($communication)) $SESSION_VARS['dataAttente']['communication']    = $communication;
  if (isset($banque))        $SESSION_VARS['dataAttente']['id_banque']        = $banque;
  if (isset($frais))         $frais = recupMontant($frais);

  //récupération des données
  $dataCorrespondant = getInfosCorrespondant($correspondant);
  $dataBeneficiaire  = getTireurBenefDatas($id_ben);
  $dataDonneur       = getTireurBenefDatas($id_ordre);
  $dataBanque        = getInfosBanque($banque);
  $dataBanque        = $dataBanque[$banque];

  //données encodées
  $html->addField("nom_ben", _("Bénéficiaire"), TYPC_TXT);
  $html->addField("nom_ordre", _("Donneur d'ordre"), TYPC_TXT);
  $html->addField("correspondant", _("Correspondant bancaire"), TYPC_TXT);
  $html->addField("banque",_("Banque"), TYPC_TXT);
  $html->addField("sens", _("Sens"), TYPC_TXT);
  $html->addField("num_piece", _("Numéro"), TYPC_TXT);
  $html->addField("communication", _("Communication"), TYPC_TXT);
  $html->addField("remarque", _("Remarque"), TYPC_ARE);

  //valeurs des champs
  if ($sens == 1)
    $html->setFieldProperties("sens", FIELDP_DEFAULT, _("In (mise à disposition)"));
  else
    $html->setFieldProperties("sens", FIELDP_DEFAULT, _("Out (envoi argent)"));
  $html->setFieldProperties("nom_ben",       FIELDP_DEFAULT, $dataBeneficiaire['denomination']);
  $html->setFieldProperties("nom_ordre",     FIELDP_DEFAULT, $dataDonneur['denomination']);
  $html->setFieldProperties("banque",        FIELDP_DEFAULT, $dataBanque['nom_banque']);
  $html->setFieldProperties("correspondant", FIELDP_DEFAULT, $dataCorrespondant['nom_banque']." - ".$dataCorrespondant['numero_cpte']);
  $html->setFieldProperties("num_piece",     FIELDP_DEFAULT, $num_piece);
  $html->setFieldProperties("communication", FIELDP_DEFAULT, $communication);
  $html->setFieldProperties("remarque",      FIELDP_DEFAULT, $remarque);


  $fieldsLabel = array("nom_ben", "nom_ordre", "correspondant", "banque", "sens", "num_piece", "communication", "remarque");
  $ordre = array("nom_ben", "nom_ordre", "correspondant", "banque");

  //données à encoder
  setMonnaieCourante($dataCorrespondant['devise']);
  $SESSION_VARS['dataAttente']['devise']        = $dataCorrespondant['devise'];
  $html->addField("frais_transfert", _("Frais de transfert réel"), TYPC_MNT);
  if ($sens == 2) { // s'il s'agit d'un envoi d'argent
    $html->addField("mnt", _("Montant à envoyer"), TYPC_MNT);
    $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);
    $html->addField("mnt_total", _("Montant à verser au guichet"), TYPC_MNT);
    $html->setFieldProperties("frais_transfert", FIELDP_JS_EVENT, array("onChange"=>"calculeTotal();"));
    $html->setFieldProperties("mnt", FIELDP_JS_EVENT, array("onChange"=>"calculeTotal();"));
    array_push($fieldsLabel, "mnt_total");

    // Ajout du champ de confirmation du montant
    $html->addField("mnt_encaisse", _("Montant encaissé"), TYPC_MNT);
    $html->setFieldProperties("mnt_encaisse", FIELDP_HAS_BILLET, true);
    $html->setFieldProperties("mnt_encaisse", FIELDP_IS_REQUIRED, true);
    $jsCheck = "if (recupMontant(document.ADForm.mnt_total.value) != recupMontant(document.ADForm.mnt_encaisse.value))
             {
               ADFormValid = false;
               msg += ' - "._("Le montant encaissé ne correspond pas au montant total")."\\n';
             }";
  } else {
    $html->addField("mnt", _("Montant de mise à disposition"), TYPC_MNT);
    $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);
  }

  if ($dataCorrespondant['devise'] != $global_monnaie) {
    $html->addField("cv_frais_transfert", _("C/V des frais de transfert"), TYPC_MNT);
    setMonnaieCourante($global_monnaie);
    $html->addField("frais_transfert_ref", _("Frais de transfert"), TYPC_MNT);
    $html->setFieldProperties("frais_transfert_ref", FIELDP_DEFAULT, $frais);
    $CVfrais = calculeCV($global_monnaie, $dataCorrespondant['devise'], $frais);
    $html->setFieldProperties("cv_frais_transfert", FIELDP_DEFAULT, $CVfrais);
    $html->setFieldProperties("frais_transfert", FIELDP_DEFAULT, $CVfrais);
    array_push($fieldsLabel, "frais_transfert_ref", "cv_frais_transfert");
    array_push($ordre, "frais_transfert_ref", "cv_frais_transfert");
  } else {
    $html->setFieldProperties("frais_transfert", FIELDP_DEFAULT, $frais);
  }

  if ($sens == 2) {
    array_push($ordre, "frais_transfert", "mnt", "mnt_total", "mnt_encaisse", "sens", "num_piece", "communication", "remarque");
  } else {
    array_push($ordre, "frais_transfert", "mnt", "sens", "num_piece", "communication", "remarque");
  }
  foreach($fieldsLabel as $value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
  }

  $calcJS="
          function calculeTotal()
        {
          document.ADForm.mnt_total.value = recupMontant(document.ADForm.mnt.value) + recupMontant(document.ADForm.frais_transfert.value);
        }
          ";
  $html->addJS(JSP_FORM, "JS2", $calcJS);
  $html->addJS(JSP_BEGIN_CHECK, "jsCheck", $jsCheck);

  $html->setOrder(NULL, $ordre);

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Att-9');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Att-1');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();
}
//----------------------------------------------------------------------------------------
//------------- ECRAN Att-9 : Confirmation de l'introduction de l''attente manuelle ------
//----------------------------------------------------------------------------------------
else if ($global_nom_ecran == "Att-9") {
  $data=$SESSION_VARS['dataAttente'];
  if ($data['sens']=='in ') $data['type_piece']=6;//in  : mise à disposition
  else                      $data['type_piece']=7;//out : envoi d'argent
  $data['date_piece'] = date("d/m/Y");
  $data['date']       = date("d/m/Y");
  $data['montant']    = arrondiMonnaie(recupMontant($mnt),0,$global_monnaie);
  $data['etat']       = 1;//en attente
  $frais_transfert = arrondiMonnaie(recupMontant($frais_transfert),0,$global_monnaie);
  $erreur = attenteManuelle($data, $frais_transfert);

  unset($SESSION_VARS['dataAttente']);

  if ($erreur->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec de l'enregistrement de l'attente"));
    $html_err->setMessage($error[$erreur->errCode]."<BR/>".$erreur->param);
    $html_err->addButton("BUTTON_OK", 'Att-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $html_msg =new HTML_message(_("Confirmation"));
    $html_msg->setMessage(_("Une attente a été créée avec succès")."<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param)."</code></B>");
    $html_msg->addButton("BUTTON_OK", 'Att-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'écran $global_nom_ecran n'a pas été trouvé"
?>