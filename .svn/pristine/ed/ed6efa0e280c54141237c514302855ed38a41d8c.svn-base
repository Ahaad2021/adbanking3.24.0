<?php
/* Défection d'un client.
   TF - 20/02/2002 */

require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/agence.php');
require_once('lib/dbProcedures/compte.php');
require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/guichet.php');
require_once('lib/dbProcedures/rapports.php');
require_once 'lib/dbProcedures/carte_atm.php';
require_once('lib/misc/divers.php');
require_once('lib/misc/VariablesSession.php');
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once 'modules/epargne/recu.php';


if ($global_nom_ecran == 'Dcl-1') {

  $CLI = getClientDatas($global_id_client);

  $statut_juridique = $CLI['statut_juridique'];
  $SESSION_VARS['statut_juridique'] = $statut_juridique;

  $Title = _("Défection d'un client");
  $myForm = new HTML_GEN2($Title);

  $myForm->addTable("ad_cli", OPER_INCLUDE, array("etat", "raison_defection"));
  $exclude = array(1,2,4,7,9);

  if ($CLI['etat'] == 1) {
    // Le client est en attente de validation; n'a pas payé adhésion et parts sociales
    $SESSION_VARS["is_EAV"] = true;
    array_push($exclude, 3,4,5,6);
  } else {
    $SESSION_VARS["is_EAV"] = false;//client inscrit
    array_push($exclude, 8);

    if ($statut_juridique != 1) // Le client n'est pas PP
      array_push($exclude, 3);
  }

  $myForm->setFieldProperties("etat", FIELDP_EXCLUDE_CHOICES, $exclude);

  $js = "\n\tif (document.ADForm.HTML_GEN_LSB_etat.value == 3){\n";
  $js .= "\t\tdocument.ADForm.raison_defection.value = '';\n";
  $js .= "\t\tdocument.ADForm.raison_defection.disabled = true;\n";
  $js .= "\t}\n";
  $js .= "\telse\n";
  $js .= "\t{\n";
  $js .= "\t\tdocument.ADForm.raison_defection.value = '';\n";
  $js .= "\t\tdocument.ADForm.raison_defection.disabled = false;\n";
  $js .= "\t}";

  $myForm->setFieldProperties("etat", FIELDP_JS_EVENT, array("onchange" => $js));

  $myForm->addFormButton(1, 1, "ok", _("Continuer"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dcl-2');
  $myForm->setFieldProperties("etat", FIELDP_LONG_NAME, _("Raison de la défection"));
  $myForm->buildHTML();
  echo $myForm->getHTML();
} else if ($global_nom_ecran == 'Dcl-2') {
  //Situation financière du client
  //le client est-il garant pour un autre client ayant un crédit ?
  $dcr = isGarantAutreClient($global_id_client);
  $nbre_elt = count($dcr);
  //calcul de la balance
  if ($etat == 3) // En cas de décès
    $balance = getBalanceDeces($global_id_client);
  else //cas général
    $balance = getBalance($global_id_client);

  // montant des encaisses
  foreach ($balance as $devise => $montant)
  $encaisse[$devise] = get_encaisse($global_id_guichet, $devise);

  //  TF - 26/09/2002 - Pour le moment, on ne gère pas la défection d'un client garant d'un autre crédit,
  //  même en cas de décès. En cours
  $probleme = false; // Passe à true si un fait empèche la défection
  if ($nbre_elt>0) {
    // Ce client s'est porté garant pour un autre client, et ne peut donc pas subir de défection
    // A moins qu'on soit dans le cas d'un décès. Alors appel d'une procédure exceptionnelle
    $probleme = true;
    $msg .= _("Ce client s'est porté garant concernant le ou les crédits d'autres clients ( dossier(s) de crédit n° : ");
    foreach($dcr as $id_doss=>$value)
    $msg .="<br/>$id_doss";

    $msg .="<br/>)";
    $msg .=" <br/><br/>"._("La défection est impossible tant que le ou les crédits n'auront pas été soldés")."<br/>";
  }

  foreach($balance as $devise => $montant) {
    // La balance en devise étrangère doit être nulle sauf dans le cas d'une défection d'un client décédé sans ayant-droit
    if ($devise != $global_monnaie && $montant != 0) {
      $AyantDroit = existeAyantDroit($global_id_client);
      if ($etat !=3 or count($AyantDroit) > 0) {
        $probleme = true;
        setMonnaieCourante($devise);
        $msg .= _("Le client possède une balance non nulle dans la devise "). $devise." (".afficheMontant($montant, true).")<br/>"._("La défection ne peut avoir lieu que si tous les avoirs et dettes du client en devise étrangère ont été annulés")."<br/>";
      }
    }
  }
  reset($balance);

  if (($etat != 3) && ($etat != 4) && ($probleme == false)) {
    foreach ($balance as $devise => $montant) {
      if ($encaisse[$devise] < $montant) {
        $probleme = true; // Il y a un pb sur au moins une caisse
        setMonnaieCourante($devise);
        $msg .= "<br/><br/>".sprintf(_("Le montant de la caisse %s est insuffisant pour poursuivre cette opération. "),$devise)."<br/> "._("Disponible")." : ".afficheMontant($encaisse[$devise], true).", "._("nécessaire")." : ".afficheMontant($montant, true);
      }
    }
  }

  // Si le client a un abonnement, il doit se désabonner avant de continuer
  if (count(getClientAbonnementInfo(generateIdentifiant())) > 0) {
      $probleme = true;
      $msg .= "<br/><br/>"._("Le client a au moins un abonnement. Il faut le désabonner avant de continuer avec la défection !");
  }

  //Si le client possede un abonnement ATM, il faut le desabonner
    $abonnement = getAbonnementATM();
  if (sizeof($abonnement) > 0){
      $probleme = true;
      $msg .= "<br/><br/>"._("Le client possède un abonnement au service ATM. Il faut le désabonner avant de continuer avec la defection!");
  }

  if ($probleme) { // Encaisse insuffisante ou balance devise étrangère non null dans le cas d'une défection autre que par décès
    $erreur = new HTML_erreur(_("Défection client"));
    $erreur->setMessage($msg);
    $erreur->addButton(BUTTON_OK,"Gen-9");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    die();
  }
  // traitement proprement dit de la défection

  if ($etat == 4)
    $SESSION_VARS["transfert_client"] = TRUE;

  $SESSION_VARS["etat"] = $etat;      // On garde le nouveau statut du client pour usage futur.
  $SESSION_VARS["raison_defection"] = $raison_defection;      // Idem pour la raison de la défection

  $Title = "Situation globale du client";

  $myForm = new HTML_GEN2($Title);

  /* Construction de la balance du client */
  if ($SESSION_VARS["is_EAV"] == false) { /* client actif */
    /* Recherche des comptes d'épargne services financiers non fermés */
    $CPTS = get_comptes_epargne($global_id_client);

    /* Ajout de la liste le compte de parts sociales si existant */
    $idCptPS = getPSAccountID($global_id_client);
    if ($idCptPS != NULL)
      $CPTS[$idCptPS] = getAccountDatas($idCptPS);


    /* Récupération des dossiers de crédit à l'état 'Fonds déboursés' ou 'En attente de rééchel/Moratoire' ? */
    $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
    $dossiers = getIdDossier($global_id_client, $whereCl);

    /* On affiche les comptes nanties numéraires s'ils appartiennentt au client ou si c'est une défection par dècès */
    foreach($dossiers as $id_doss=>$value) {
      /* Récupération de l'épargne nantie numéraire du dossier */
      $liste_gar = getListeGaranties($id_doss);
      foreach($liste_gar as $key=>$val) {
        /* la garantie doit être numéraire, non restituée et non réalisée  */
        if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
          $nantie = $val['gar_num_id_cpte_nantie'];

          /* si le compte appartient au client,on l'incorpore dans ses avoirs quelle que soit la nature de la défection */
          $CPT_NANTIE = getAccountDatas($nantie);
          if ($CPT_NANTIE["id_titulaire"] == $global_id_client)
            $CPTS[$nantie] = $CPT_NANTIE;
          elseif ($etat == 3) { /* la garantie n'appartient pas au client et c'est une défection par décès */
            /* Si le client est débiteur, tout ou une partie de cette garantie servira à rembourser
                         tout ou partie d'un événtuel crédit en cours.
                      On affiche le solde entier du compte mais pas le montant qui sera prélevè lors de la finalisation de la défection
            */
            $CPTS[$nantie] = $CPT_NANTIE;
          }
        }
      }

    }


    /* Affichage des Comptes */
    $myForm->addHTMLExtraCode("titre1","<h4  align=\"center\">Situation des comptes</h4>");
    while (list($key, $value) = each($CPTS)) {
      //si on transfère le client, on prend les vrais soldes des comptes
      //FIXME : pas besoin de faire isset ?

      if ($SESSION_VARS["transfert_client"] != TRUE) {
        /* Récupération des infos de simulation arrêter compte */
        $infos_simul = simulationArrete($key);
        $CPTS[$key]["solde"] = $infos_simul["solde_cloture"];
      }

      setMonnaieCourante($value["devise"]);

      $myForm->addField("num_cpte".$key, _("Numéro de compte"), TYPC_TXT);
      $myForm->addField("type_cpte".$key, _("Produit"), TYPC_TXT);
      $myForm->addField("solde".$key, _("Solde"), TYPC_MNT);

      $myForm->setFieldProperties("num_cpte".$key, FIELDP_DEFAULT, $value["num_complet_cpte"]);
      $myForm->setFieldProperties("type_cpte".$key, FIELDP_DEFAULT, $value["libel"]);
      $myForm->setFieldProperties("solde".$key, FIELDP_DEFAULT, $CPTS[$key]["solde"]);

      $myForm->setFieldProperties("solde".$key, FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("num_cpte".$key, FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("type_cpte".$key, FIELDP_IS_LABEL, true);
      $myForm->addHTMLExtraCode("line".$key, "<BR>");

    }

    //si on transfère un client, on ne prend en compte que le capital restant du : compta de caisse

    /* Récupération des dossiers de crédit à l'état 'Fonds déboursés' ou 'En attente de rééchel/Moratoire' ? */
    $myForm->addHTMLExtraCode("titre2","<h4  align=\"center\">Simulation arrêté de compte crédit</h4>");
    $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
    $dossiers = getIdDossier($global_id_client, $whereCl);
    /* Affichage des soldes des crédits */
    foreach($dossiers as $id_doss=>$value) {
      $solde_credit = 0;
      $myErr = simulationArreteCpteCredit($solde_credit, $id_doss);
      if ($myErr->errCode == NO_ERR) {
        $dev_cre = $myErr->param;
        setMonnaieCourante($dev_cre);

        $myForm->addField("credit".$id_doss, _("Solde crédit dossier N°$id_doss en cours"), TYPC_MNT);
        $myForm->setFieldProperties("credit".$id_doss, FIELDP_DEFAULT, ($solde_credit * -1));
        $myForm->setFieldProperties("credit".$id_doss, FIELDP_IS_LABEL, true);
        $myForm->addHTMLExtraCode("line_cre".$id_doss, "<BR>");
      } else {
        // "Retour inattendu desimulationArreteCpteCredit : ".$myErr->errCode
        $html_err = new HTML_erreur(_("Echec lors de la simulation de l'arrêté du compte de crédit."));
        $html_err->setMessage("Erreur : ".$error[$myErr->errCode].$myErr->param);
        $html_err->addButton("BUTTON_OK", 'Gen-9');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit();
      }
    }
  }

  foreach ($balance as $devise => $montant) {
    setMonnaieCourante($devise);
    $myForm->addField("balance.$devise", "Balance $devise", TYPC_MNT);
    $myForm->setFieldProperties("balance.$devise", FIELDP_DEFAULT, $montant);
    $myForm->setFieldProperties("balance.$devise", FIELDP_IS_LABEL, true);
  }

  //recherche des ayant droit
  if ($SESSION_VARS["transfert_client"] != TRUE) {
    $lidclient = $global_id_client;
    $AyantDroit = existeAyantDroit($lidclient);
    if (count($AyantDroit) > 0 ) {
      $myForm->addHTMLExtraCode("saut","<BR>");
      $myForm->addHTMLExtraCode("sousTitre","<H4 align=\"center\">Informations sur les ayant droit</h4>");

      $table =& $myForm->addHTMLTable('tablerels', /*nbre colonnes*/ 4, TABLE_STYLE_ALTERN);
      $table->add_cell(new TABLE_cell(_("Dénomination"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
      $table->add_cell(new TABLE_cell(_("Adresse"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
      $table->add_cell(new TABLE_cell(_("Date de naissance"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
      $table->add_cell(new TABLE_cell(_("Lieu de naissance"), 	/*colspan*/ 1, 	/*rowspan*/ 1));

      // Construction de la table
      foreach($AyantDroit as $key=>$REL) {
        $infos_rel = getPersExtDatas($REL["id_pers_ext"]);
        $table->add_cell(new TABLE_cell($infos_rel["denomination"]));
        $table->add_cell(new TABLE_cell($infos_rel["adresss"]));
        $table->add_cell(new TABLE_cell($infos_rel["date_naiss"]));
        $table->add_cell(new TABLE_cell($infos_rel["lieu_naiss"]));
      }
    }
  }//fin ayant droit

  //Choix d'une agence-banque destination du transfert client
  if ($SESSION_VARS["transfert_client"]  == TRUE) {
    $myForm->addHTMLExtraCode("line_bqe", "<BR>");
    $myForm->addTableRefField("bqe", "Banque", "adsys_banques");
    $myForm->setFieldProperties("bqe", FIELDP_HAS_CHOICE_AUCUN, true);
    $myForm->setFieldProperties("bqe", FIELDP_IS_REQUIRED, true);
  }


  $myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);

  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Dcl-3');

  $myForm->buildHTML();
  echo $myForm->getHTML();

  $SESSION_VARS['balance'] = $balance[$global_monnaie];
  $SESSION_VARS['retraitOK'] = false;
  $SESSION_VARS['depotOK'] = false;

}

else if ($global_nom_ecran == 'Dcl-3') {
  if (($SESSION_VARS['etat'] == 3)) { //défection client décédé
    /* En cas de décès, on passe par un état intermédiaire 'en attente enregistrement décès' */
    if ($SESSION_VARS['statut_juridique'] != 1)  /* vérifie que le client est bien personne physique */
      signalErreur(__FILE__,__LINE__,__FUNCTION__);

    $myErr = clientDecede($global_id_client);
    if ($myErr->errCode != NO_ERR) {
      $html_err = new HTML_erreur(_("Echec du traitement."));
      $html_err->setMessage("Erreur : ".$error[$myErr->errCode].$myErr->param);
      $html_err->addButton("BUTTON_OK", 'Gen-3');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }

    $global_etat_client = 7;

    $myMsg = new HTML_message(_("Confirmation de la défection"));
    $myMsg->setMessage(_("Procéder à la finalisation de la défection lorsque les ayant-droits se présenteront et après que l'assurance ait joué s'il y a lieu."));

    $myMsg->addButton(BUTTON_OK, 'Gen-3');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;

  } else { /* Défection par démission ou radiation */
    $myErr = testDefection($global_id_client);
    if ($myErr->errCode == ERR_CPTE_BLOQUE) {
      // GESTION DE L'ERREUR
      $erreur = new HTML_erreur(_("Tentative de défection d'un client"));
      $erreur->setMessage(_("Ce client possède actuellement un ou plusieurs comptes bloqués. Débloquer ces comptes avant de continuer."));
      $erreur->addButton(BUTTON_OK,"Gen-9");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
    } else if ($SESSION_VARS["etat"] == 4) {//défection par transfert, pas de retrait des fonds

      //Récupération de la banque dans le cas d'un transfert client
      if (isset($bqe)) $SESSION_VARS["banque"] = $bqe;

      if ($SESSION_VARS['balance'] < 0) {
        $erreur = new HTML_erreur(_("Tentative de défection d'un client"));
        $erreur->setMessage(_("Ce client a une balance négative. Défection non autorisée."));
        $erreur->addButton(BUTTON_OK,"Gen-9");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
      } else {
        /* Récupération des dossiers de crédit à l'état 'Fonds déboursés' ou 'En attente de rééchel/Moratoire' ? */
        $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
        $dossiers = getIdDossier($global_id_client, $whereCl);
        if (count($dossiers) > 0) {
          //pas de crédit en cours ?
          $erreur = new HTML_erreur(_("Tentative de défection d'un client"));
          $erreur->setMessage(_("Ce client a un crédit en cours. Défection non autorisée."));
          $erreur->addButton(BUTTON_OK,"Gen-9");
          $erreur->buildHTML();
          echo $erreur->HTML_code;
        } else {
          //fermer les comptes d'épargne
          $myErr = lanceDefectionTransfert($global_id_client, $SESSION_VARS["banque"], $SESSION_VARS['etat'], $SESSION_VARS["raison_defection"]);
          if ($myErr->errCode != NO_ERR) {
            //FIXME : revoir cet appel

            $erreur = new HTML_erreur(_("Tentative de défection d'un client"));

            $erreur->setMessage(_("Une erreur s'est produite pendant le traitement.."));
            $erreur->addButton(BUTTON_OK,"Gen-9");
            $erreur->buildHTML();
            echo $erreur->HTML_code;
            exit();
          } else {
            //FIXME : pas d'impression de reçu
            $myMsg = new HTML_message(_("Défection terminée"));
            $myMsg->setMessage(_("L'opération de défection s'est terminée avec succès"));
            $myMsg->addButton(BUTTON_OK, 'Gen-3');
            $myMsg->buildHTML();
            echo $myMsg->HTML_code;
          }
        }
      }
    } else if ($myErr->errCode == ERR_DEF_SLD_NON_NUL && $SESSION_VARS['retraitOK'] == false && $SESSION_VARS["depotOK"] == false) {
      $balance = $myErr->param;

      if ($balance < 0) {
        $SESSION_VARS['balance'] = $balance;
        $SESSION_VARS['balanceArrondie'] = - (arrondiMonnaie(($balance*-1), 1, $global_monnaie));
        // Il faut approvisionner le compte de base avant de pouvoir effectuer la clôture des comptes
        $myForm = new HTML_GEN2(_("Un versement est nécessaire pour que la défection soit menée à bien"));
        $myForm->addField("apayer" , _("Somme à payer"), TYPC_MNT);
        $myForm->addField("paye", _("Somme encaissée"), TYPC_MNT);
        $myForm->setFieldProperties("paye", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("paye", FIELDP_HAS_BILLET, true);
        $myForm->setFieldProperties("apayer", FIELDP_DEFAULT, arrondiMonnaie(($balance*-1), 1));
        $myForm->setFieldProperties("apayer", FIELDP_IS_LABEL, true);
        $myForm->addFormButton(1,1, "valider",_("Valider"), TYPB_SUBMIT);
        $myForm->addFormButton(1,2, "annuler",_("Annuler"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Dcl-4');
        $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
        $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
        $myForm->addJS(JSP_BEGIN_CHECK, "checkMoney", "if (recupMontant(document.ADForm.paye.value) != ".arrondiMonnaie(($balance*-1),1).") {msg += 'La somme encaissée doit correspondre au montant du dépôt';ADFormValid = false;}");
        $myForm->buildHTML();
        echo $myForm->getHTML();
      } else if ($balance > 0) { // La balance est > 0, il faut faire un retrait.
        $SESSION_VARS["balanceArrondie"] = arrondiMonnaie($balance, -1);
        $Title = "Retrait du solde du compte de base";
        $myForm = new HTML_GEN2($Title);
        $myForm->addTable("ad_cpt", OPER_INCLUDE, array("solde"));
        $myForm->setFieldProperties("solde", FIELDP_DEFAULT, arrondiMonnaie($balance, -1));
        $myForm->setFieldProperties("solde", FIELDP_IS_LABEL, true);
        $myForm->addField("decaisse", _("Somme décaissée"), TYPC_MNT);
        $myForm->setFieldProperties("decaisse", FIELDP_IS_REQUIRED, true);

        global $global_billet_req;
        if ($global_billet_req) {
          $myForm->setFieldProperties("decaisse", FIELDP_HAS_BILLET, true);
          $myForm->setFieldProperties("decaisse", FIELDP_SENS_BIL, SENS_BIL_OUT);
        }
        $myForm->addFormButton(1,1, "valider",_("Valider"), TYPB_SUBMIT);
        $myForm->addFormButton(1,2, "annuler",_("Annuler"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Dcl-3');
        $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
        $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
        $mntCheck = "if (recupMontant(document.ADForm.decaisse.value ) != ".arrondiMonnaie($balance, -1).") {msg += 'Le montant décaissé doit être égal au solde du compte';ADFormValid = false;}";
        $myForm->addJS(JSP_BEGIN_CHECK, "mntCheck", $mntCheck);
        $myForm->buildHTML();
        echo $myForm->getHTML();
        $SESSION_VARS['retraitOK'] = true; // Indique que le retrait a bien été réalisé physiquement.
        // A noter qu'on ne va pas encore enregistrer réellement ce retrait car on ne sait pas sur quel compte aller prendre l'argent que l'on retire.
        // D'où l'intérêt de la variable retraitOK qui est à true une fois qu'on est passé par cet écran.
      }
    } else if ($myErr->errCode == NO_ERR || $SESSION_VARS['retraitOK'] == true || $SESSION_VARS['depotOK'] == true) {
      $myMsg = new HTML_message(_("Confirmation de la défection"));
      $myMsg->setMessage(_("Tous les comptes du client sont à présent soldés.<br> Cliquez sur OK pour procéder à la défection"));
      $myMsg->addButton(BUTTON_OK, 'Dcl-5');
      $myMsg->addCustomButton("annuler", "Annuler", "Gen-9");
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec lors de la déféctioon. "));
      $html_err->setMessage("La déféction n'a pas été effectuée. ".$error[$myErr->errCode].$myErr->param);
      $html_err->addButton("BUTTON_OK", 'Gen-9');
      $html_err->buildHTML();
      echo $html_err->HTML_code;

    }
  }
} else if ($global_nom_ecran == 'Dcl-4') {
  // Confirmation du versement.
  $idCpteBase = getBaseAccountID($global_id_client);
  // Le client était dans l'état EAV, il faut débloquer exceptionnellement son compte de base
  deblocageCompteInconditionnel($idCpteBase);

  $InfoCpte = getAccountDatas($idCpteBase);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  $myErr = depot_cpte($global_id_guichet, $idCpteBase, arrondiMonnaie(abs($SESSION_VARS['balance']),1), $InfoProduit, $InfoCpte);
  if ($myErr->errCode != NO_ERR)        // Ne devrait pas arriver
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Incohérence dans la défection, erreur ".$myErr->errCode
  $myMsg = new HTML_message(_("Confirmation"));
  $myMsg->setMessage(_("Le versement a été effectué avec succès"));
  $myMsg->addButton(BUTTON_OK, 'Dcl-3');
  $myMsg->buildHTML();
  echo $myMsg->HTML_code;
  $SESSION_VARS["depotOK"] = true;

} else if ($global_nom_ecran == 'Dcl-5') {
  $myErr = lanceDefectionClient($global_id_client, $SESSION_VARS['etat'], $SESSION_VARS["raison_defection"], $global_id_guichet);
  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Défection du client."));
    $html_err->setMessage("Echec : ".$error[$myErr->errCode]."<BR>Paramètre : ".$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-9');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $id_his = $myErr->param;

    // Impression du reçu lors de la défection
    if ($SESSION_VARS["etat"] != 3 && !$SESSION_VARS["is_EAV"]) // En cas de décès pas de reçu pour le moment
      print_recu_defection($global_id_client, $SESSION_VARS["etat"], $SESSION_VARS['balanceArrondie'], $id_his);

    $global_etat_client = $SESSION_VARS['etat'];
    // Ajout historique
    // ajout_historique(15, $global_id_client, 2, $global_nom_login, date("r"), NULL);
    $myMsg = new HTML_message(_("Défection terminée"));
    $msg = _("L'opération de défection s'est terminée avec succès");
    $msg .= "<br /><br />"._("Numéro de transaction")." : <B><code>".sprintf("%09d", $id_his)."</code></B>";
    $myMsg->setMessage($msg);

    // Set session nb_clients_actifs
    $_SESSION['nb_clients_actifs'] = updateNbClientActif(true);

    $myMsg->addButton(BUTTON_OK, 'Gen-3');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }
} else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' non pris en charge"

?>
