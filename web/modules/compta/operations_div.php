<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Passage d'écritures libres
 *
 * Opd pour Opérations Diverses
 * Opa pour Opérations Auxiliaires (journal lié à un compte principal)
 *
 * Cette fonction comprend les écrans :
 * - Ecr-1 : choix de l'écriture libre
 * - Opd-1 : saisie de l'opération dans le journal principal ou des od
 * - Opd-3 Opa-3 : confirmation de l'ajout de l'écriture
 * - Opd-2 Opa-2 Opd-7 Opa-7 : demande de confirmation de la saisie de l'écriture
 * - Opd-6 : modification des opérations diverses
 * - Opd-5 Opa-5 : choix de l'écriture à modifier
 * - Opd-4 Opa-4 Opd-9 Opa-9 : validation de l'écriture
 * - Opd-8 Opa-8 : modification dans la base
 * - Opa-1 : saisie dans un journal auxiliaire
 * - Opa-6 : modification operations auxiliaires
 * - Opa-10 : confirmation de la suppression d'écritures
 * @package Compta
 */

require_once 'lib/dbProcedures/compta.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/misc/divers.php';
require_once 'modules/epargne/recu.php';

/*{{{ Ecr-1 : Choix de l'écriture libre */
if ($global_nom_ecran == "Ecr-1") {
  global $global_profil_axs;
  /* On affiche 6 lignes par défaut */
  $nbr_lignes_ecr = 6;

  /* Initialisation des variables de session */
  $i = 1;
  while (isset($SESSION_VARS[$i])) {
    unset($SESSION_VARS[$i]);
    $i++;
  }
  unset($SESSION_VARS["exercice"]);
  unset($SESSION_VARS["totaldeb"]);
  unset($SESSION_VARS["totalcred"]);
  unset($SESSION_VARS["libel_ope"]);
  unset($SESSION_VARS["type_operation"]);
  unset($SESSION_VARS["date_comptable"]);
  unset($SESSION_VARS["devise"]);
  unset($SESSION_VARS["nbr_lignes_ecr"]);

  //Menu principal des opérations diverses
  $MyPage = new HTML_GEN2(_("Ecritures libres"));
  $MyPage->addHiddenType("nbr_lignes_ecr", $nbr_lignes_ecr);

  // L'exercice ouvert ou les exercices en cours de clôture
  $liste=array();
  $exos = getExercicesComptables();
  if (isset($exos))
    foreach($exos as $key=>$value) {
    if ($value["etat_exo"]!=3) //etat_exo différent de 'Cloturé'
      $liste[$value["id_exo_compta"]]=$value["id_exo_compta"]; // parce que l'exercice n'a pas de libellé
  }
  $MyPage->addField("exercice",_("Exercice"), TYPC_LSB);
  $MyPage->setFieldProperties("exercice", FIELDP_ADD_CHOICES, $liste);
  $MyPage->setFieldProperties("exercice", FIELDP_HAS_CHOICE_AUCUN, true);
  $MyPage->setFieldProperties("exercice", FIELDP_IS_REQUIRED, true);

  // Liste des journaux
  $jnl=getInfosJournal();
  $choices = array();
  if (isset($jnl)) {
    foreach ($jnl as $key => $value)
    $choices[$key]=$value["libel_jou"];
  }

  $MyPage->addField("journal",_("Journal"), TYPC_LSB);
  $MyPage->setFieldProperties("journal", FIELDP_ADD_CHOICES_TRAD, $choices);
  $MyPage->setFieldProperties("journal", FIELDP_HAS_CHOICE_AUCUN, true);
  $MyPage->setFieldProperties("journal", FIELDP_IS_REQUIRED, true);

  //Gestionnaire- Tri par agent gestionnaire
  /*
  $list_users = array();
  $users = getEcritureLibreUtilisateurs();
  if (isset($users)) {
    foreach($users as $key=>$value) {
      $list_users[$value["login"]] = $value["fullname"];
    }
  }
  $MyPage->addField("gest",_("Gestionnaire"), TYPC_LSB);
  $MyPage->setFieldProperties("gest", FIELDP_ADD_CHOICES, $list_users);
  $MyPage->setFieldProperties("gest", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("gest", FIELDP_HAS_CHOICE_TOUS, true);
  */
  
  $MyPage->addHiddenType("gest", "");

  // La date de valeur
  $MyPage->addField("date_ope",_("Date opération"), TYPC_DTE);
  // $MyPage->setFieldProperties("date_ope", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("date_ope", FIELDP_DEFAULT, date("d/m/Y"));

  //Javascript
  $js="document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Opd-1';\n";
  $js2="document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Opd-5';\n";
  if (isset($jnl))
    foreach($jnl as $row) {
    $idjou=$row['id_jou'];
    if ($row['num_cpte_princ'])
      $cp=1;
    else
      $cp=0;
    $js.="if ( (document.ADForm.HTML_GEN_LSB_journal.value==$idjou) && ($cp==1) ){";

    $js.="document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Opa-1';}";

    $js2.="if ( (document.ADForm.HTML_GEN_LSB_journal.value==$idjou) && ($cp==1) ){";
    $js2.="document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Opa-5';}";

  }

  //Bouton Ajouter
  if (in_array(470,$global_profil_axs)){ //AT-125 : bouton acessible pour le fonction 470 seulement (saisie ecritures libres)
    $MyPage->addFormButton(1, 1, "ajouter", _("Ajouter"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("ajouter", BUTP_AXS, 470);
    //$MyPage->setFormButtonProperties("ajouter", BUTP_PROCHAIN_ECRAN, "Opd-1");
    $MyPage->setFormButtonProperties("ajouter", BUTP_CHECK_FORM, true);
    $MyPage->setFormButtonProperties("ajouter", BUTP_JS_EVENT, array("onclick" => $js));
  }

  //Bouton Modifier
  if (in_array(471,$global_profil_axs)){ //AT-125 : bouton acessible pour le droit (validation ecritures libres)
    $MyPage->addFormButton(1, 2, "modifier", _("Mod/Supp"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("modifier", BUTP_AXS, 471);
    //$MyPage->setFormButtonProperties("modifier", BUTP_PROCHAIN_ECRAN, "Opd-5");
    $MyPage->setFormButtonProperties("modifier", BUTP_CHECK_FORM, true);
    $MyPage->setFormButtonProperties("modifier", BUTP_JS_EVENT, array("onclick" => $js2));
  }

  //Bouton Annuler
  $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Opd-1 : Saisie opération dans le journal principal ou des od */
else if ($global_nom_ecran == "Opd-1") {	$SESSION_VARS['envoi'] = 0;
  $dateValide = true;

  if (isset($nbr_lignes_ecr))
    $SESSION_VARS["nbr_lignes_ecr"] = $nbr_lignes_ecr;
  if (!isset($SESSION_VARS["exercice"])) { //si on vient du premier ecran ( Ecr-1 )
    //Récupération de la saisie
    $SESSION_VARS["exercice"] = $exercice;
    $SESSION_VARS["journal"] = $journal;
    if ($date_ope!='')
      $SESSION_VARS["date_comptable"] = $date_ope;
    unset($SESSION_VARS["libel_ope"]);
    unset($SESSION_VARS["type_operation"]);
    unset($SESSION_VARS["id_taxe"]);
    unset($SESSION_VARS["taux_taxe"]);
    unset($SESSION_VARS["sens_oper_tax"]);

    //Récupération du libelle du journal
    $info=getInfosJournal($journal);
    $SESSION_VARS["libel_jou"] = serialize($info[$journal]['libel_jou']);

    // Récupération des comptes comptables qui peuvent être mouvementés dans le brouillard
    $SESSION_VARS["cptes_brouillard"] = array();
    $SESSION_VARS["cptes_brouillard"] = getComptesBrouillard();
  } else {
    // Récupération des données déjà encodées
    if (!isset($SESSION_VARS["libel_ope"])){
      $SESSION_VARS["libel_ope"] = serialize($libel_ope);
      $SESSION_VARS["type_operation"] = $HTML_GEN_LSB_libel_ope_def;
    }
      //$SESSION_VARS["id_taxe"] = $id_taxe;
      //$SESSION_VARS["taux_taxe"] = $taux_taxe;
      //$SESSION_VARS["sens_oper_tax"] = $sens_oper_tax;
    // récupération de la saisie : pour chaque ligne du tableau
    for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
      //if ($ {'cpte_comptable'.$i})
      {
          if ($ {'cpte_comptable'.$i}){
            $SESSION_VARS[$i]['num_cpte_comptable'] = $ {'cpte_comptable'.$i};
          }
        if ($ {'id_compte'.$i}) {
          $infoscompte=getAccountDatas($ {'id_compte'.$i});

          $CLI = getClientDatas($infoscompte["id_titulaire"]);
          if ($CLI["statut_juridique"] == 1) // Personne physique
            $nom_cli = STR_replace("'","",$CLI["pp_nom"])." ".STR_replace("'","",$CLI["pp_prenom"]);
          elseif($CLI["statut_juridique"] == 2) // Personne morale
          $nom_cli = STR_replace("'","",$CLI["pm_raison_sociale"]);
          elseif($CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) // Groupe informel ou solidaire
          $nom_cli = STR_replace("'","",$CLI["gi_nom"]);

          $SESSION_VARS[$i]['cpte_client']=$infoscompte['num_complet_cpte']." ".$nom_cli;
          $SESSION_VARS[$i]['id_compte'] = $ {'id_compte'.$i};
          $SESSION_VARS[$i]['id_client'] = $infoscompte["id_titulaire"];
          $SESSION_VARS[$i]['num_complet_cpte'] = $infoscompte['num_complet_cpte'];
        }
        if ($SESSION_VARS[$i]['mntdeb'] != "") {
          if($SESSION_VARS["id_taxe"]>0) {
            $taxesInfos = getTaxesInfos();
            
            $SESSION_VARS[$i]['mntdeb'] = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',$SESSION_VARS[$i]['mntdeb'])) * (1 + $taxesInfos[$SESSION_VARS['id_taxe']]["taux"]));
          }else{
            $SESSION_VARS[$i]['mntdeb'] = recupMontant($SESSION_VARS[$i]['mntdeb']);
          }

          $SESSION_VARS[$i]['mntcred'] = '';
        }
        else if ($SESSION_VARS[$i]['mntcred'] != "") {
          if($SESSION_VARS["id_taxe"]>0) {
            $taxesInfos = getTaxesInfos();
            
            $SESSION_VARS[$i]['mntcred'] = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',$SESSION_VARS[$i]['mntcred'])) * (1 + $taxesInfos[$SESSION_VARS['id_taxe']]["taux"]));
          }else{
            $SESSION_VARS[$i]['mntcred'] = recupMontant($SESSION_VARS[$i]['mntcred']);
          }

          $SESSION_VARS[$i]['mntdeb'] = '';
        }
      }
    }
  }

  // Récupération des infos de l'exercice
  $exos=getExercicesComptables($SESSION_VARS["exercice"]);

  // Contrôle de la date si elle est saisie
  if (isset($exos) && isset($SESSION_VARS["date_comptable"])) {
    // FIXME/TF : Il existe une fonction isBefore !!

    $dateope = php2pg($SESSION_VARS["date_comptable"]);
    $dateope=getPhpDateTimestamp($dateope);
    $datedeb=getPhpDateTimestamp($exos[0]['date_deb_exo']);
    $datefin=getPhpDateTimestamp($exos[0]['date_fin_exo']);
    if ( (date("y/m/d",$dateope) < date("y/m/d",$datedeb))
         || ( date("y/m/d",$dateope) > date("y/m/d",$datefin))
         || ( date("y/m/d",$dateope) > date("y/m/d"))) {
      $dateValide=false;
      $html_err = new HTML_erreur(_("Echec saisie écriture"));
      $html_err->setMessage(" ".sprintf(_("La date de l'écriture (%s) doit être postérieure à la date de la dernière cloture (%s)"), localiser_date($date_ope),localiser_date($date_clot)));
      $html_err->addButton("BUTTON_OK", 'Ecr-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }

  $CLOT = getCloturesPeriodiques(array("id_exo" => $SESSION_VARS["exercice"]));
  $CLOT = $CLOT["id_ag"];
  if (isset($date_ope) && sizeof($CLOT) > 0)
    foreach($CLOT as $key => $CL) {
    $date_clot = pg2phpDate($CL["date_clot_per"]);
    if (!isBefore($date_clot, $date_ope)) {
      $dateValide=false;
      $html_err = new HTML_erreur(_("Echec saisie écriture"));
      $html_err->setMessage(" ".sprintf(_("La date de l'écriture (%s) doit être postérieure à la date de la dernière cloture (%s)"), localiser_date($date_ope),localiser_date($date_clot)));
      $html_err->addButton("BUTTON_OK", 'Ecr-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }

  if ($dateValide) { // soit la date n'est pas renseignée soit elle l'est et est valide
    //Menu Saisie des opérations diverses
    $MyPage = new HTML_GEN2(_("Ajout opérations diverses"));

    // L'exercice choisi
    $MyPage->addField("exercice",_("Exercice"), TYPC_TXT);
    $MyPage->setFieldProperties("exercice", FIELDP_DEFAULT, $SESSION_VARS["exercice"]);
    $MyPage->setFieldProperties("exercice", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("exercice", FIELDP_IS_LABEL, true);

    // Le libellé du journal choisi
    $MyPage->addField("libel_jou",_("Journal"), TYPC_TTR);
    //$SESSION_VARS["libel_jou"] = unserialize($SESSION_VARS["libel_jou"]);
    if(!is_trad(unserialize($SESSION_VARS["libel_jou"]))){
    	$libel_jou = new Trad(unserialize($SESSION_VARS["libel_jou"]));
    	$SESSION_VARS["libel_jou"] = serialize($libel_jou);
    }else{
    	$libel_jou = unserialize($SESSION_VARS["libel_jou"]);
    }
    $MyPage->setFieldProperties("libel_jou", FIELDP_DEFAULT, $libel_jou);
    $MyPage->setFieldProperties("libel_jou", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("libel_jou", FIELDP_IS_LABEL, true);

    // La date de valeur saisie
    $MyPage->addField("date_ope",_("Date opération"), TYPC_DTE);
    $MyPage->setFieldProperties("date_ope", FIELDP_IS_REQUIRED, false);
    $MyPage->setFieldProperties("date_ope", FIELDP_DEFAULT, $SESSION_VARS["date_comptable"]);
    $MyPage->setFieldProperties("date_ope", FIELDP_IS_LABEL, true);

    // le libellé de l'écriture
   	$choices=array();
  	$list_libel = getLEL(); // Récupère de tous les libellés des écritures libres
  	$choices[0]=_("Autre libellé");
  	foreach ($list_libel as $key => $value){
  		$libel_ope = new Trad($value["libel_ope"]);
  		$choices[$value["type_operation"]] = $libel_ope->traduction();
  	}
   	$MyPage->addField("libel_ope_def",_("Liste libellé"), TYPC_LSB);
  	$MyPage->setFieldProperties("libel_ope_def", FIELDP_ADD_CHOICES, $choices);
  	$MyPage->setFieldProperties("libel_ope_def", FIELDP_HAS_CHOICE_AUCUN, false);
  	$MyPage->setFieldProperties("libel_ope_def", FIELDP_DEFAULT, $SESSION_VARS["type_operation"]);
  	$MyPage->setFieldProperties("libel_ope_def", FIELDP_JS_EVENT, array("onChange"=>"changeLibel();"));
  	      
    $MyPage->addField("libel_ope",_("Libellé opération"), TYPC_TTR);
    $SESSION_VARS["libel_ope"] = unserialize($SESSION_VARS["libel_ope"]);
    if(!is_trad($SESSION_VARS["libel_ope"])){
    	$SESSION_VARS["libel_ope"] = new Trad($SESSION_VARS["libel_ope"]);
    }
    if(isset($SESSION_VARS["libel_ope"]))
    	$MyPage->setFieldProperties("libel_ope", FIELDP_DEFAULT, $SESSION_VARS["libel_ope"]);
    $MyPage->setFieldProperties("libel_ope", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("libel_ope", FIELDP_WIDTH, 40);
		
    $codejs ="\n\nfunction changeLibel() {";
    $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==0)\n\t";
		$codejs .= "{\n\t\tdocument.ADForm.HTML_GEN_ttr_strid_libel_ope.value ='';";
		$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =false;";
		$codejs .= "}else{\n";
    foreach($choices as $type_operation=>$value) {
	  	$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==$type_operation)\n\t";
	  	$codejs .= "\n\t{";
	  	foreach (get_langues_installees() as $code => $langue){
	  		$libel_ope = new Trad($list_libel[$type_operation]['libel_ope']);
	  		$codejs .= "\n\t\tdocument.ADForm.HTML_GEN_ttr_libel_ope_".$code.".value =\"".$libel_ope->traduction($code)."\";";
	  	}
		//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =true;";
		$codejs .= "}\n";
    }
    $codejs .= "}}\n";
    $MyPage->addJS(JSP_FORM, "jslibel", $codejs);

    // TVA à appliquer
    $MyPage->addField("id_taxe",_("TVA à appliquer"), TYPC_LSB);
    $liste_taxe = getListeTaxes();
    $MyPage->setFieldProperties("id_taxe", FIELDP_ADD_CHOICES, $liste_taxe);
    $MyPage->setFieldProperties("id_taxe", FIELDP_DEFAULT, $SESSION_VARS["id_taxe"]);
    $MyPage->setFieldProperties("id_taxe", FIELDP_JS_EVENT, array("OnChange"=>"updateTaxe();"));
    $MyPage->setFieldProperties("id_taxe", FIELDP_IS_REQUIRED, false);
    $MyPage->addField("taux_taxe", _("Taux de la taxe"), TYPC_PRC);
    $MyPage->setFieldProperties("taux_taxe", FIELDP_DEFAULT, $SESSION_VARS["taux_taxe"]);
   	$MyPage->setFieldProperties("taux_taxe", FIELDP_IS_LABEL,true);
   	// Sens de l'opération
	  $sens=array("d"=>_("Paiement de tva déductible"),"c"=>_("Perception de tva collectée"));
	  $MyPage->addField("sens_oper_tax",_("Sens de la taxe"), TYPC_LSB);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_ADD_CHOICES, $sens);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_DEFAULT, $SESSION_VARS["sens_oper_tax"]);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_HAS_CHOICE_AUCUN, true);
   	$taxesInfos = getTaxesInfos();
   	$js = "function updateTaxe()\n{\n ";
   	foreach($taxesInfos as $key=>$value) {
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value == ".$value["id"].")) {";
     $js .= "document.ADForm.taux_taxe.value = '".$value["taux"]*(100)."';";
     $js .= "document.ADForm.HTML_GEN_LSB_sens_oper_tax.disabled = false;";
     $js .= "}";
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value == '0')){";
     $js .= "document.ADForm.taux_taxe.value = '';";
     $js .= "document.ADForm.HTML_GEN_LSB_sens_oper_tax.disabled = true;";
     $js .= "}";
   	}
	 	$js .= "};";
	 	$js .= "updateTaxe();";
	 	$MyPage->addJS(JSP_FORM, "jstest", $js);
		// Checkform
  	$jscheck = "if (document.ADForm.HTML_GEN_LSB_id_taxe.value != 0)
           {
             if (document.ADForm.HTML_GEN_LSB_sens_oper_tax.value == 0)
           {
             msg += '- "._("Le champ Sens de la taxe doit être renseigné")."\\n';
             ADFormValid = false;
           }
           }";

  	$MyPage->addJS(JSP_BEGIN_CHECK, "JS2", $jscheck);

    $MyPage->addHiddenType("bouton_clique");
    $MyPage->addHiddenType("nbr_lignes_ecr", $SESSION_VARS["nbr_lignes_ecr"]);

    //tableau des écritures diverses
    $html = "<link rel=\"stylesheet\" href=\"/lib/misc/js/chosen/css/chosen.css\">";
    $html .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $html .= "<script src=\"/lib/misc/js/chosen/chosen.jquery.js\" type=\"text/javascript\"></script>";

    $html .= "<br>";
    $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    //En-tête du tableau
    $html .= "<TR bgcolor=$colb_tableau>";
    $html.="<TD align=\"center\"><b>"._("Comptes comptables")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Compte client")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Débit")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Crédit")."</b></TD>";
    $html.="</TR>\n";

    for ($i=1; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
      //On alterne la couleur de fond
      if ($i%2)
        $color = $colb_tableau;
      else
        $color = $colb_tableau_altern;

      $html .= "<TR bgcolor=$color>\n";

      // Comptes comptables qui peuvent être mouvementés dans le brouillard
      $html .= "<TD>\n";
      $html .= "<select class=\"chosen-select\" NAME=\"cpte_comptable$i\" style=\"width:250px\" ";
      $html .= "onchange=\"document.ADForm.cpte_client$i.value ='';document.ADForm.id_compte$i.value ='';\">\n";
      $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
      if (isset($SESSION_VARS["cptes_brouillard"]))
        foreach($SESSION_VARS["cptes_brouillard"] as $key=>$value)
        if ($SESSION_VARS[$i]['num_cpte_comptable']==$key)
          $html .= "<option value=$key selected>".$key." ".$value."</option>\n";
        else
          $html .= "<option value=$key>".$key." ".$value."</option>\n";

      $html .= "</select>\n";
      $html .= "</TD>";

      // num complet du compte du client
      $html.="<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=32 value=\"".$SESSION_VARS[$i]['cpte_client']."\" disabled=true>\n";
      $html .="<FONT size=\"2\"><a href=# onclick=\"getCompte(document.ADForm.cpte_comptable$i.value,'cpte_client$i','id_compte$i');return false;\">"._("Recherche")."</a></FONT></TD>\n";

      // id compte du client
      $html.="<INPUT TYPE=\"hidden\" NAME=\"id_compte$i\" value=\"".$SESSION_VARS[$i]['id_compte']."\">\n";

      // id du client
 	    $html.="<INPUT TYPE=\"hidden\" NAME=\"id_client$i\" value=\"".$SESSION_VARS[$i]['id_client']."\">\n";

      //Montant débit
      $html .= "<TD><INPUT NAME=\"mntdeb$i\" TYPE=\"text\" size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntdeb'],false)."\"";
      $html .= "onchange=\"value = formateMontant(value);checkDebit();\"></TD>\n";

      //Montant crédit
      $html .="<TD><INPUT NAME=\"mntcred$i\" TYPE=\"text\" size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntcred'],false)."\"";
      $html .= "onchange=\"value = formateMontant(value);checkCredit();\"></TD>\n";

      $html .= "</TR>\n";
    }
    $html .= "</TABLE>\n";

    $jsPreLoadData = "";

    // Liste libellé
    if (isset($_POST['libel_ope_def']) && trim($_POST['libel_ope_def'])!='') {
      $jsPreLoadData .= "
                      document.ADForm.HTML_GEN_LSB_libel_ope_def.value = '".trim($_POST['libel_ope_def'])."';
                    ";
    }

    // Libellé opération
    if (is_trad($_POST['libel_ope'])) {
      $libel_ope = $_POST["libel_ope"];

      $libel_ope_txt = trim($libel_ope->traduction());

      $jsPreLoadData .= " document.ADForm.HTML_GEN_ttr_libel_ope_fr_BE.value = '".$libel_ope_txt."'; ";

    } else {

      $jsPreLoadData .= " changeLibel(); ";
    }

    // TVA à appliquer et Taux de la taxe
    if (isset($_POST['id_taxe']) && trim($_POST['id_taxe'])!='') {
      $jsPreLoadData .= "
                      document.ADForm.HTML_GEN_LSB_id_taxe.value = '".trim($_POST['id_taxe'])."';
                      updateTaxe();
                    ";
    }

    // Sens de la taxe
    if (isset($_POST['sens_oper_tax']) && trim($_POST['sens_oper_tax'])!='') {
      $jsPreLoadData .= "
                      document.ADForm.HTML_GEN_LSB_sens_oper_tax.value = '".trim($_POST['sens_oper_tax'])."';
                    ";
    }

    if (isset($_POST['nbr_lignes_ecr']) && trim($_POST['nbr_lignes_ecr'])!='') {
      $nbr_lignes_ecr = trim($_POST['nbr_lignes_ecr']);
    } else {
      $nbr_lignes_ecr = 6;
    }

    for($x=1;$x<=$nbr_lignes_ecr;$x++) {

      // Compte client
      if (isset($_POST['cpte_client'.$x]) && trim($_POST['cpte_client'.$x])!='') {
        $jsPreLoadData .= "
                            if (document.ADForm.cpte_client$x) {
                              document.ADForm.cpte_client$x.value = '".trim($_POST['cpte_client'.$x])."';
                            }
                    ";
      }
      // Id compte
      if (isset($_POST['id_compte'.$x]) && trim($_POST['id_compte'.$x])!='') {
        $jsPreLoadData .= "
                            if (document.ADForm.id_compte$x) {
                              document.ADForm.id_compte$x.value = '".trim($_POST['id_compte'.$x])."';
                            }
                    ";
      }
      // Id client
      if (isset($_POST['id_client'.$x]) && trim($_POST['id_client'.$x])!='') {
        $jsPreLoadData .= "
                            if (document.ADForm.id_client$x) {
                              document.ADForm.id_client$x.value = '".trim($_POST['id_client'.$x])."';
                            }
                    ";
      }
      // Montant Débit
      if (isset($_POST['mntdeb'.$x]) && trim($_POST['mntdeb'.$x])!='') {
        $jsPreLoadData .= "
                            if (document.ADForm.mntdeb$x) {
                              document.ADForm.mntdeb$x.value = '".trim($_POST['mntdeb'.$x])."';
                            }
                    ";
      }
      // Montant Crédit
      if (isset($_POST['mntcred'.$x]) && trim($_POST['mntcred'.$x])!='') {
        $jsPreLoadData .= "
                            if (document.ADForm.mntcred$x) {
                              document.ADForm.mntcred$x.value = '".trim($_POST['mntcred'.$x])."';
                            }
                    ";
      }
    }


    $MyPage->addJS(JSP_FORM, "JS_PRELOAD_DATA", $jsPreLoadData);

    //Bouton Ajout ligne
    $MyPage->addFormButton(1, 1, "ajout", _("Ajouter ligne"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("ajout", BUTP_AXS, 470);
    $MyPage->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, "Opd-1");
    $MyPage->setFormButtonProperties("ajout", BUTP_CHECK_FORM, false);
    $js = "document.ADForm.nbr_lignes_ecr.value = ".$SESSION_VARS['nbr_lignes_ecr']."+1;\n";
    $MyPage->setFormButtonProperties("ajout", BUTP_JS_EVENT, array("onclick" => $js));

    //Bouton Enregistrer
    $MyPage->addFormButton(1, 2, "enregistrer", _("Enregistrer"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("enregistrer", BUTP_AXS, 470);
    $MyPage->setFormButtonProperties("enregistrer", BUTP_PROCHAIN_ECRAN, "Opd-2");
    $MyPage->setFormButtonProperties("enregistrer", BUTP_CHECK_FORM, true);
    $MyPage->setFormButtonProperties("enregistrer", BUTP_JS_EVENT, array("onclick" => "document.ADForm.bouton_clique.value='enregistrer';"));

    //Bouton Valider
    $MyPage->addFormButton(1, 3, "valider", _("Valider"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valider", BUTP_AXS, 471);
    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Opd-2");
    $MyPage->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
    $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.bouton_clique.value='valider';"));

    //Bouton Annuler
    $MyPage->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    //$MyPage->setFormButtonProperties("annuler", BUTP_AXS, 470);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Ecr-1");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM,false);

    //JavaScript
    $html .= "<script type=\"text/javascript\">\n";
      $html .= "var config = { '.chosen-select' : {} }\n";
      $html .= "for (var selector in config) {\n";
      $html .= "$(selector).chosen(config[selector]); }\n";

    //function  getCompte : ouvre une fenêtre de recherche de compte de client
    $html .= "function getCompte(cpte_comptable,cpte_client,id_compte)\n{\n";
    $html.= "var ch;\n";
    $html.= "ch='../modules/compta/rech_compte_client.php?m_agc=".$_REQUEST['m_agc']."&field_name='+cpte_client+'&id_compte='+id_compte+'&field_cpte_comptable='+cpte_comptable;\n";
    $html .= "OpenBrw(ch, '"._("Recherche")."');\n";
    $html .= "}\n";

    // Fonction check crédit g
    $html .="function checkCredit()\n{\n";
    for ($i=1; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
      $html.="if(document.ADForm.mntcred$i.value)\n";
      $html.="\n\tdocument.ADForm.mntdeb$i.value = '';\n";
    }
    $html.="}\n";

    // Fonction check débit g
    $html .="function checkDebit()\n{\n";
    for ($i=1; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
      $html.="if(document.ADForm.mntdeb$i.value)\n";
      $html.="\n\tdocument.ADForm.mntcred$i.value = '';\n";
    }
    $html.="}\n";
    $html .= "</script>\n";

    $MyPage->addHTMLExtraCode("html",$html);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }
	}
/*}}}*/

/*{{{ Opd-3 Opa-3 : Confirmation de l'ajout de l'écriture */
else if ( ($global_nom_ecran == "Opd-3") || ($global_nom_ecran == "Opa-3") ) {
  $idecr=1;

  for ($num_op=1;$num_op<=$SESSION_VARS["nbr_lignes_ecr"];$num_op++)
    if ($SESSION_VARS[$num_op]['num_cpte_comptable']) {
      $DATA[$num_op]['id'] = $idecr;
      $DATA[$num_op]['compte'] = $SESSION_VARS[$num_op]['num_cpte_comptable'];
      $DATA[$num_op]['cpte_interne_cli'] = $SESSION_VARS[$num_op]['id_compte'];
      $DATA[$num_op]['devise'] = $SESSION_VARS[$num_op]['devise'];

      if ($SESSION_VARS[$num_op]['mntdeb']) {
        $DATA[$num_op]['sens'] = 'd';
        $mnt_debit = arrondiMonnaiePrecision($SESSION_VARS[$num_op]['mntdeb'], $global_monnaie);
        $DATA[$num_op]['montant'] = $mnt_debit;

      } else {
        $DATA[$num_op]['sens'] = 'c';
        $mnt_credit = arrondiMonnaiePrecision($SESSION_VARS[$num_op]['mntcred'], $global_monnaie);
        $DATA[$num_op]['montant'] = $mnt_credit;
      }

      $DATA[$num_op]['date_comptable'] = $SESSION_VARS[$num_op]['date_comptable'];
      $libel_ecriture = ($SESSION_VARS[$num_op]['libel_ecriture']);
      $DATA[$num_op]['libel_ecriture'] = $libel_ecriture;
      $DATA[$num_op]['type_operation'] = $SESSION_VARS[$num_op]['type_operation'];
      $DATA[$num_op]['id_jou'] = $SESSION_VARS[$num_op]['id_jou'];
      $DATA[$num_op]['id_exo'] = $SESSION_VARS[$num_op]['id_exo'];
      $DATA[$num_op]['id_taxe'] = $SESSION_VARS['id_taxe'];
      $DATA[$num_op]['sens_taxe'] = $SESSION_VARS['sens_oper_tax'];
    }

  $myErr = passageEcrituresBrouillard($DATA);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Passage écritures au brouillard.")." ");
    $html_err->setMessage(_("Echec")." : ".$error[$myErr->errCode].$myErr->param);

    $html_err->addButton("BUTTON_OK", 'Ecr-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    //HTML
    $MyPage = new HTML_message(_("Confirmation ajout"));
    $MyPage->setMessage(_("L'entrée de la table des écritures a été ajoutée avec succès !"));
    $MyPage->addButton(BUTTON_OK, "Ecr-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}
/*}}}*/

/*{{{ Opd-2 Opa-2 Opd-7 Opa-7 : Demande de confirmation de la saisie de l'écriture */
else if (($global_nom_ecran == "Opd-2") || ($global_nom_ecran == "Opa-2") || ($global_nom_ecran == "Opd-7") || ($global_nom_ecran == "Opa-7")) {
  for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++)
    unset($SESSION_VARS[$i]);
   $SESSION_VARS['envoi'] = 0;
  if (isset($date_ope))
    $SESSION_VARS["date_comptable"]=$date_ope; // en cas de modification

  $SESSION_VARS["libel_ope"] = serialize($libel_ope);
  $SESSION_VARS["type_operation"] = $HTML_GEN_LSB_libel_ope_def;

  $lignes_contenu = 0; // Comtpe le nombre de mouvements saisis
  $SESSION_VARS["comptes"]=getComptesComptables();

  // Initialisation des tableaux pour les totaux
  $TOTALDEB = array();
  $TOTALCRED = array();

  // récupération de la saisie : pour chaque ligne du tableau
  for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
    if ( ($ {'cpte_comptable'.$i}) || ($ {'id_mouvement'.$i}) ) {
      $lignes_contenu++;
      $SESSION_VARS[$i]['id_exo'] = $SESSION_VARS["exercice"];
      $SESSION_VARS[$i]['id_jou'] =  $SESSION_VARS["journal"];
      $SESSION_VARS[$i]['date_comptable'] = $SESSION_VARS["date_comptable"];
      $SESSION_VARS[$i]['libel_ecriture'] = $SESSION_VARS["libel_ope"];
      $SESSION_VARS[$i]['type_operation'] =$SESSION_VARS["type_operation"];

      $SESSION_VARS[$i]['id_mouvement'] = $ {'id_mouvement'.$i};
      $SESSION_VARS[$i]['id_his'] = $ {'id_his'.$i};
      $SESSION_VARS[$i]['id'] = $ {'id'.$i};
      $SESSION_VARS[$i]['num_cpte_comptable'] = $ {'cpte_comptable'.$i};
      $SESSION_VARS[$i]['libel_cpte_comptable'] =$ {'cpte_comptable'.$i}." ".$SESSION_VARS["comptes"][$ {'cpte_comptable'.$i}]["libel_cpte_comptable"];
      $SESSION_VARS[$i]['devise'] = $SESSION_VARS["comptes"][$ {'cpte_comptable'.$i}]["devise"];
	    if ($ {'id_compte'.$i}) {
        $infoscompte=getAccountDatas($ {'id_compte'.$i});

        $CLI = getClientDatas($infoscompte["id_titulaire"]);
        if ($CLI["statut_juridique"] == 1) // Personne physique
          $nom_cli = STR_replace("'","",$CLI["pp_nom"])." ".STR_replace("'","",$CLI["pp_prenom"]);
        elseif($CLI["statut_juridique"] == 2) // Personne morale
        $nom_cli = STR_replace("'","",$CLI["pm_raison_sociale"]);
        elseif($CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) // Groupe informel ou solidaire
        $nom_cli = STR_replace("'","",$CLI["gi_nom"]);

        $SESSION_VARS[$i]['cpte_client']=$infoscompte['num_complet_cpte']." ".$nom_cli;
        $SESSION_VARS[$i]['id_compte'] = $ {'id_compte'.$i};
        $SESSION_VARS[$i]['id_client'] = $infoscompte["id_titulaire"];
        $SESSION_VARS[$i]['num_complet_cpte'] = $infoscompte['num_complet_cpte'];
      }
      if ($ {'mntdeb'.$i} != "") {
          if(isset($HTML_GEN_LSB_id_taxe)&&($HTML_GEN_LSB_id_taxe != 0)){
            $taxesInfos = getTaxesInfos();
            $SESSION_VARS[$i]['mntdeb'] = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',${'mntdeb'.$i})) / (1 + $taxesInfos[$HTML_GEN_LSB_id_taxe]["taux"]));
          } else {
            $SESSION_VARS[$i]['mntdeb'] = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',$ {'mntdeb'.$i})));
          }
        $SESSION_VARS[$i]['mntcred'] = '';
        $TOTALDEB[$SESSION_VARS[$i]['devise']] += $SESSION_VARS[$i]['mntdeb'];
      }
      else if ($ {'mntcred'.$i} != "") {
          if(isset($HTML_GEN_LSB_id_taxe)&&($HTML_GEN_LSB_id_taxe != 0)){
            $taxesInfos = getTaxesInfos();
            $SESSION_VARS[$i]['mntcred'] = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',${'mntcred'.$i})) / (1 + $taxesInfos[$HTML_GEN_LSB_id_taxe]["taux"]));
          } else {
            $SESSION_VARS[$i]['mntcred'] = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',$ {'mntcred'.$i})));
          }
        $SESSION_VARS[$i]['mntdeb'] = '';
        $TOTALCRED[$SESSION_VARS[$i]['devise']] += $SESSION_VARS[$i]['mntcred'];
      }
      $last_line = $i;
    }
  }

  //Ecritures pour la perception des taxes
  $ecriture_taxe = array();
	$TOTALDEB_TAX = array();
	$TOTALCRED_TAX = array();
  if(isset($HTML_GEN_LSB_id_taxe)&&($HTML_GEN_LSB_id_taxe != 0)){
  	$taxesInfos = getTaxesInfos();
  	$SESSION_VARS["id_taxe"] = $id_taxe;
  	$SESSION_VARS["libel_taxe"] = $taxesInfos[$HTML_GEN_LSB_id_taxe]["libel"];
  	$SESSION_VARS["cpte_tax_ded"] = $taxesInfos[$HTML_GEN_LSB_id_taxe]["cpte_tax_ded"];
  	$SESSION_VARS["cpte_tax_col"] = $taxesInfos[$HTML_GEN_LSB_id_taxe]["cpte_tax_col"];
  	if($SESSION_VARS["cpte_tax_ded"] == NULL) {
    	$msgErr .= _("Compte lié à la taxe déductible n'est pas paramétré: ").$SESSION_VARS["libel_taxe"];
    }
    if($SESSION_VARS["cpte_tax_col"] == NULL) {
    	$msgErr .= _("Compte lié à la taxe collectée n'est pas paramétré: ").$SESSION_VARS["libel_taxe"];
    }
  	$SESSION_VARS["taux_taxe"] = $taxesInfos[$HTML_GEN_LSB_id_taxe]["taux"]*100;
  	$SESSION_VARS["sens_tax"] = $HTML_GEN_LSB_sens_oper_tax;
  	$SESSION_VARS["sens_oper_tax"] = $sens_oper_tax;

		$SESSION_VARS["libel_ope_tva"] = $SESSION_VARS["sens_oper_tax"];
		//$nbr_lignes_ecr = $SESSION_VARS["nbr_lignes_ecr"];
		$j = 0;
  	for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
    if ( ($ {'cpte_comptable'.$i}) || ($ {'id_mouvement'.$i}) ) {
      //$lignes_contenu++;
      if(!$SESSION_VARS[$i]['auto']){//on ne prend pas de taxes pour les écritures générées automatiquement
      $j++;
      $ecriture_taxe[$j]['id_exo'] = $SESSION_VARS["exercice"];
      $ecriture_taxe[$j]['id_jou'] =  $SESSION_VARS["journal"];
      $ecriture_taxe[$j]['date_comptable'] = $SESSION_VARS["date_comptable"];

      $ecriture_taxe[$j]['id_mouvement'] = $ {'id_mouvement'.$i}+$last_line;
      $ecriture_taxe[$j]['id_his'] = $ {'id_his'.$i};
      $ecriture_taxe[$j]['id'] = $ {'id'.$i};

      if ($ {'id_compte'.$i}) {
        $infoscompte=getAccountDatas($ {'id_compte'.$i});

        $CLI = getClientDatas($infoscompte["id_titulaire"]);
        if ($CLI["statut_juridique"] == 1) // Personne physique
          $nom_cli = STR_replace("'","",$CLI["pp_nom"])." ".STR_replace("'","",$CLI["pp_prenom"]);
        elseif($CLI["statut_juridique"] == 2) // Personne morale
        $nom_cli = STR_replace("'","",$CLI["pm_raison_sociale"]);
        elseif($CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) // Groupe informel ou solidaire
        $nom_cli = STR_replace("'","",$CLI["gi_nom"]);

        $ecriture_taxe[$j]['cpte_client']=$infoscompte['num_complet_cpte']." ".$nom_cli;
        $ecriture_taxe[$j]['id_compte'] = $ {'id_compte'.$i};
        $ecriture_taxe[$j]['id_client'] = $infoscompte["id_titulaire"];
        $ecriture_taxe[$j]['num_complet_cpte'] = $infoscompte['num_complet_cpte'];
      }
      if($SESSION_VARS["sens_oper_tax"] == 'd'){
      	if ($ {'mntcred'.$i} != "") {
      	}

      }else{
      	if ($ {'mntdeb'.$i} != "") {
      	}

      }
      if ($ {'mntdeb'.$i} != "") {
          
        if(isset($SESSION_VARS["taux_taxe"]) && ($SESSION_VARS["taux_taxe"] > 0)){
          $mntdeb = ((mb_ereg_replace(" ", "", preg_replace('/\s+/', '',${'mntdeb'.$i})) / ((100 + $SESSION_VARS["taux_taxe"]) / 100)));
        } else {
          $mntdeb = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',$ {'mntdeb'.$i})));
        }

      	$devise_cpt_tax = $SESSION_VARS["comptes"][$SESSION_VARS["cpte_tax_ded"]]["devise"];
        $devise_mntdeb = $SESSION_VARS["comptes"][$ {'cpte_comptable'.$i}]["devise"];
        if($SESSION_VARS["sens_oper_tax"] == 'd'){
        	$ecriture_taxe[$j]['libel_ecriture'] =trim(_("Paiement de tva déductible"));
					$ecriture_taxe[$j]['type_operation'] = 473;
					$ecriture_taxe[$j]['num_cpte_comptable'] = $SESSION_VARS["cpte_tax_ded"];
	      	$ecriture_taxe[$j]['libel_cpte_comptable'] = $SESSION_VARS["cpte_tax_ded"]." ".$SESSION_VARS["comptes"][$SESSION_VARS["cpte_tax_ded"]]["libel_cpte_comptable"];
	      	$ecriture_taxe[$j]['devise'] = $devise_cpt_tax;
	      	setMonnaieCourante($ecriture_taxe[$j]['devise']);
	      	if($devise_cpt_tax != $devise_mntdeb){
        		$mntdeb = calculeCV($devise_mntdeb, $devise_cpt_tax, $mntdeb);
        	}
                
                $mntdeb_tax =  (($mntdeb * ((100 + $SESSION_VARS["taux_taxe"])/100)) - $mntdeb);
                
        	$mntdeb_tax = round($mntdeb_tax, $global_monnaie_courante_prec);
        	$ecriture_taxe[$j]['mntdeb'] = $mntdeb_tax;
	  		}else{
	  			$ecriture_taxe[$j]['libel_ecriture'] =trim(_("Perception de tva collectée"));
					$ecriture_taxe[$j]['type_operation'] = 474;
					$ecriture_taxe[$j]['num_cpte_comptable'] = $ {'cpte_comptable'.$i};
	      	$ecriture_taxe[$j]['libel_cpte_comptable'] =$ {'cpte_comptable'.$i}." ".$SESSION_VARS["comptes"][$ {'cpte_comptable'.$i}]["libel_cpte_comptable"];
	      	$ecriture_taxe[$j]['devise'] = $devise_mntdeb;
	      	setMonnaieCourante($ecriture_taxe[$j]['devise']);

                $mntdeb_tax =  (($mntdeb * ((100 + $SESSION_VARS["taux_taxe"])/100)) - $mntdeb);
	      	$mntdeb_tax = round($mntdeb_tax, $global_monnaie_courante_prec);
	      	$ecriture_taxe[$j]['mntdeb'] = $mntdeb_tax;
	  		}
        $ecriture_taxe[$j]['mntcred'] = '';
        $TOTALDEB_TAX[$ecriture_taxe[$j]['devise']] += $mntdeb_tax;
      }
      else if ($ {'mntcred'.$i} != "") {
        
        if(isset($SESSION_VARS["taux_taxe"]) && ($SESSION_VARS["taux_taxe"] > 0)){
          $mntcred = ((mb_ereg_replace(" ", "", preg_replace('/\s+/', '',${'mntcred'.$i})) / ((100 + $SESSION_VARS["taux_taxe"]) / 100)));
        } else {
          $mntcred = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',$ {'mntcred'.$i})));
        }

        //$mntcred_tax = $mntcred * $SESSION_VARS["taux_taxe"]/100;
        $devise_cpt_tax = $SESSION_VARS["comptes"][$SESSION_VARS["cpte_tax_col"]]["devise"];
        $devise_mntcred = $SESSION_VARS["comptes"][$ {'cpte_comptable'.$i}]["devise"];
        if($SESSION_VARS["sens_oper_tax"] == 'd'){
	  			$ecriture_taxe[$j]['libel_ecriture'] =trim(_("Paiement de tva déductible"));
					$ecriture_taxe[$j]['type_operation'] = 473;
					$ecriture_taxe[$j]['num_cpte_comptable'] = $ {'cpte_comptable'.$i};
	      	$ecriture_taxe[$j]['libel_cpte_comptable'] =$ {'cpte_comptable'.$i}." ".$SESSION_VARS["comptes"][$ {'cpte_comptable'.$i}]["libel_cpte_comptable"];
	      	$ecriture_taxe[$j]['devise'] = $devise_mntcred;

                $mntcred_tax =  (($mntcred * ((100 + $SESSION_VARS["taux_taxe"])/100)) - $mntcred);
	      	setMonnaieCourante($ecriture_taxe[$j]['devise']);
	      	$mntcred_tax = round($mntcred_tax, $global_monnaie_courante_prec);
	  			$ecriture_taxe[$j]['mntcred'] = $mntcred_tax;
	  		}else{
        	$ecriture_taxe[$j]['libel_ecriture'] =trim(_("Perception de tva collectée"));
					$ecriture_taxe[$j]['type_operation'] = 474;
					$ecriture_taxe[$j]['num_cpte_comptable'] = $SESSION_VARS["cpte_tax_col"];
	      	$ecriture_taxe[$j]['libel_cpte_comptable'] = $SESSION_VARS["cpte_tax_col"]." ".$SESSION_VARS["comptes"][$SESSION_VARS["cpte_tax_col"]]["libel_cpte_comptable"];
	      	$ecriture_taxe[$j]['devise'] = $devise_cpt_tax;
	      	if($devise_cpt_tax != $devise_mntcred){
        		$mntcred = calculeCV($devise_mntcred, $devise_cpt_tax, $mntcred);
        	}
        	//$mntcred_tax = $mntcred * $SESSION_VARS["taux_taxe"]/100;
                $mntcred_tax =  (($mntcred * ((100 + $SESSION_VARS["taux_taxe"])/100)) - $mntcred);
        	setMonnaieCourante($ecriture_taxe[$j]['devise']);
        	$mntcred_tax = round($mntcred_tax, $global_monnaie_courante_prec);
        	$ecriture_taxe[$j]['mntcred'] = $mntcred_tax;
	  		}
        $ecriture_taxe[$j]['mntdeb'] = '';
        $TOTALCRED_TAX[$ecriture_taxe[$j]['devise']] += $mntcred_tax;
      }
     }
    }
   }
   $last_line_tax = $j;
   $SESSION_VARS["nbr_lignes_ecr_tax"] = $j;
   $SESSION_VARS["ecriture_taxe"] = array();
   $SESSION_VARS["ecriture_taxe"] = $ecriture_taxe;
   }
  /* *************************** Demande de confirmation s'il existe au moins un mouvement***********************************/

  // l'écran de retour si on annule l'opération
  if ($global_nom_ecran == "Opd-2")
    $retour='Opd-1';
  else if ($global_nom_ecran == "Opd-7") // Confirmation Modification OD
    $retour='Opd-6';
  else if ($global_nom_ecran == "Opa-2") // Confirmation ajout OA
    $retour='Opa-1';
  else if ($global_nom_ecran == "Opa-7") // Confirmation modification OD
    $retour='Opa-6';

  // le prochain écran en fonction de l'opération à réaliser et de l'écran précédent
  if ($bouton_clique == "enregistrer") { // enregistrement
    $operation="Enregistrer";
    if ($global_nom_ecran == "Opd-2")
      $prochain="Opd-3";
    else if ($global_nom_ecran == "Opa-2")
      $prochain="Opa-3";
    else if ($global_nom_ecran == "Opd-7")
      $prochain="Opd-8";
    else if ($global_nom_ecran == "Opa-7")
      $prochain="Opa-8";
  } else { // validation
    $operation="Valider";
    if ($global_nom_ecran == "Opd-2")
      $prochain="Opd-4";
    else if ($global_nom_ecran == "Opa-2")
      $prochain="Opa-4";
    else if ($global_nom_ecran == "Opd-7")
      $prochain="Opd-9";
    else if ($global_nom_ecran == "Opa-7")
      $prochain="Opa-9";
  }

  if ($lignes_contenu > 0) { // si au moins un mouvement est saisi
    $MyPage = new HTML_GEN2(_("Confirmation de la saisie"));

    // L'exercice choisi
    $MyPage->addField("exercice",_("Exercice"), TYPC_TXT);
    $MyPage->setFieldProperties("exercice", FIELDP_DEFAULT, $SESSION_VARS["exercice"]);
    $MyPage->setFieldProperties("exercice", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("exercice", FIELDP_IS_LABEL, true);

    // libellé du journal
    $MyPage->addField("libel_jou",_("Journal"), TYPC_TTR);
    if(!is_trad(unserialize($SESSION_VARS["libel_jou"]))){
    	$libel_jou = new Trad(unserialize($SESSION_VARS["libel_jou"]));
    }else{
    	$libel_jou = unserialize($SESSION_VARS["libel_jou"]);
    }
    $MyPage->setFieldProperties("libel_jou", FIELDP_DEFAULT, $libel_jou);
    $MyPage->setFieldProperties("libel_jou", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("libel_jou", FIELDP_IS_LABEL, true);

    // La date de valeur
    $MyPage->addField("date_ope",_("Date opération"), TYPC_TXT);
    $MyPage->setFieldProperties("date_ope", FIELDP_DEFAULT, $SESSION_VARS["date_comptable"]);
    $MyPage->setFieldProperties("date_ope", FIELDP_IS_REQUIRED, false);
    $MyPage->setFieldProperties("date_ope", FIELDP_IS_LABEL, true);
    // Libellé opération
    $MyPage->addField("libel_ope",_("Libellé opération"), TYPC_TTR);
  	if(!is_trad(unserialize($SESSION_VARS["libel_ope"]))){
    	$libel_ope = new Trad(unserialize($SESSION_VARS["libel_ope"]));
    }else{
    	$libel_ope = unserialize($SESSION_VARS["libel_ope"]);
    }
    $MyPage->setFieldProperties("libel_ope", FIELDP_DEFAULT, $libel_ope);
    $MyPage->setFieldProperties("libel_ope", FIELDP_WIDTH, 40);
    $MyPage->setFieldProperties("libel_ope", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("libel_ope", FIELDP_IS_LABEL, true);
	if(isset($HTML_GEN_LSB_id_taxe)&&($HTML_GEN_LSB_id_taxe != 0)){
			// TVA à appliquer
	    $MyPage->addField("id_taxe",_("TVA à appliquer"), TYPC_LSB);
	    $liste_taxe = getListeTaxes();
	    $MyPage->setFieldProperties("id_taxe", FIELDP_ADD_CHOICES, $liste_taxe);
	    $MyPage->setFieldProperties("id_taxe", FIELDP_DEFAULT, $SESSION_VARS["id_taxe"]);
	    $MyPage->setFieldProperties("id_taxe", FIELDP_IS_REQUIRED, false);
	    $MyPage->setFieldProperties("id_taxe", FIELDP_IS_LABEL,true);
	    $MyPage->addField("taux_taxe", _("Taux de la taxe"), TYPC_PRC);
	   	$MyPage->setFieldProperties("taux_taxe", FIELDP_DEFAULT, $SESSION_VARS["taux_taxe"]);
	   	$MyPage->setFieldProperties("taux_taxe", FIELDP_IS_LABEL,true);
	   	// Sens de l'opération
		  $sens=array("d"=>_("Débit"),"c"=>_("Crédit"));
		  $MyPage->addField("sens_oper_tax",_("Sens de la taxe"), TYPC_LSB);
		  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_ADD_CHOICES, $sens);
		  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_DEFAULT, $SESSION_VARS["sens_oper_tax"]);
		  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_IS_LABEL,true);
		}else{
			unset($SESSION_VARS["id_taxe"]);
			unset($SESSION_VARS["taux_taxe"]);
			unset($SESSION_VARS["sens_oper_tax"]);
		}
    if ($bouton_clique == "valider") { // Si l'utilisateur désire valider ses écritures
      // Saisie des informations concernant le type de pièce comptable et son numéro
      $MyPage->addTable("ad_his_ext", OPER_INCLUDE, array("type_piece", "num_piece", "date_piece", "remarque"));
//      $MyPage->setFieldProperties("type_piece", FIELDP_INCLUDE_CHOICES, array(3,9,10,11,12));
    }

    // infos sur toutes les devises
    $infos_devises = get_table_devises();
    // Pour chaque devise étrangère veiller à l'équilibre total débit = total crédit
    foreach ($infos_devises as $devise => $value) {
    	// Si pour devise étrangère mnt débit != de mnt crédit, mouvementer automatique sa position de change
      if ( ($devise != $global_monnaie) and (round($TOTALDEB[$devise] - $TOTALCRED[$devise], $global_monnaie_courante_prec) != 0) ) {
        // Comptes de position de la devise
        $cptes_devise = getCptesLies($devise);

        // Si total débit > total crédit : crédit position de change et débiter C/V position de change
        if ($TOTALDEB[$devise] > $TOTALCRED[$devise]) {
          $mnt = $TOTALDEB[$devise] - $TOTALCRED[$devise];
          $cpte_debit = $cptes_devise["cvPosition"];
          $montant_debit = calculeCV($devise, $global_monnaie, $mnt);

          $devise_debit = $global_monnaie;

          $cpte_credit = $cptes_devise["position"];
          $montant_credit = $mnt;
          $devise_credit = $devise;
        } // fin si total débit > total crédit
        elseif($TOTALDEB[$devise] < $TOTALCRED[$devise]) { // total crédit > total débit pour cette devise
          // on débite la position de change et on crédit la C/V position de change
          $mnt = $TOTALCRED[$devise] - $TOTALDEB[$devise];
          $cpte_debit = $cptes_devise["position"];
          $montant_debit = $mnt;
          $devise_debit = $devise;

          $cpte_credit = $cptes_devise["cvPosition"];
          $montant_credit = calculeCV($devise, $global_monnaie, $mnt);
          $devise_credit = $global_monnaie;
        } // fin si total débit < total crédit
		$libel_ope_trad = unserialize($SESSION_VARS["libel_ope"]);
		$libel_ope = $libel_ope_trad->traduction();
        $LIGNE_DEBIT = array('id_exo' => $SESSION_VARS["exercice"],
                             'id_jou' =>  $SESSION_VARS["journal"],
                             'date_comptable' => $SESSION_VARS["date_comptable"],
                             'libel_ecriture' => $libel_ope,
                             'num_cpte_comptable' => $cpte_debit,
                             'libel_cpte_comptable' => $cpte_debit." ".$SESSION_VARS["comptes"][$cpte_debit]["libel_cpte_comptable"],
                             'devise' => $devise_debit,
                             'mntdeb' => $montant_debit,
                             'mntcred' => '',
                             'auto' => true);
        $last_line++;
        $SESSION_VARS[$last_line] = $LIGNE_DEBIT;
        $TOTALDEB[$devise_debit] += $montant_debit;

        $LIGNE_CREDIT = array('id_exo' => $SESSION_VARS["exercice"],
                              'id_jou' =>  $SESSION_VARS["journal"],
                              'date_comptable' => $SESSION_VARS["date_comptable"],
                              'libel_ecriture' =>  $libel_ope,
                              'num_cpte_comptable' => $cpte_credit,
                              'libel_cpte_comptable' => $cpte_credit." ".$SESSION_VARS["comptes"][$cpte_credit]["libel_cpte_comptable"],
                              'devise' => $devise_credit,
                              'mntdeb' => '',
                              'mntcred' => $montant_credit,
                              'auto' => true);
        $last_line++;
        $SESSION_VARS[$last_line] = $LIGNE_CREDIT;
        $TOTALCRED[$devise_credit] += $montant_credit;

      } // fin si devise étrangère et total débit != total crédit
    } // mouvements automatiques

    $html ="<br>";
    $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    //En-tête du tableau
    $html .= "<TR bgcolor=$colb_tableau>";
    $html.="<TD><b>"._("n°")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Compte comptable")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Compte client")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Débit")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Crédit")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Libellé écriture")."</b></TD>";
    $html.="</TR>\n";

    // tableau des mouvements saisis
    $i=1;
    while (isset($SESSION_VARS[$i])) {
      if ($SESSION_VARS[$i]['num_cpte_comptable']) {

        if ($SESSION_VARS[$i]["auto"]) { // Ce mouvement a été généré automatiquement
          $beginColor = "<FONT color=blue>";
          $endColor = "</FONT>";
        } else {
          $beginColor = "";
          $endColor = "";
        }

        setMonnaieCourante($SESSION_VARS[$i]['devise']);

        //On alterne la couleur de fond du tableau
        if ($i%2)
          $color = $colb_tableau;
        else
          $color = $colb_tableau_altern;

        $mntdeb_affiche = arrondiMonnaiePrecision($SESSION_VARS[$i]['mntdeb'], $global_monnaie);
        $mntdeb_affiche = afficheMontant($mntdeb_affiche, true);
        $mntcred_affiche = arrondiMonnaiePrecision($SESSION_VARS[$i]['mntcred'], $global_monnaie);
        $mntcred_affiche = afficheMontant($mntcred_affiche, true);

        // Affichage des écritures
        $html .= "<TR bgcolor=$color >";
        $html .= "<TD>$beginColor<b>$i</b>$endColor</TD>"; // numéro de la ligne
        $html .= "<TD>$beginColor".$SESSION_VARS[$i]['libel_cpte_comptable']."$endColor</TD>";
        $html .= "<TD>$beginColor&nbsp ".$SESSION_VARS[$i]['cpte_client']."$endColor</TD>";
        $html .= "<TD>$beginColor&nbsp ".$mntdeb_affiche."$endColor</TD>";
        $html .= "<TD>$beginColor&nbsp ".$mntcred_affiche."$endColor</TD>";

        if(!is_trad(unserialize($SESSION_VARS[$i]['libel_ecriture']))){
            $libel_ecriture_trad = new Trad($SESSION_VARS[$i]['libel_ecriture']);
        }
        else{
            $libel_ecriture_trad = unserialize($SESSION_VARS[$i]['libel_ecriture']);
        } 
              
        $libel_ecriture = $libel_ecriture_trad->traduction();
        $html .= "<TD>$beginColor".$libel_ecriture."$endColor</TD>";
        $html .= "</TR>";
      }
      $i++;
    }
    // Pierre Ticket #682 : on réassigne à SESSION_VARS['nre_ligne_ecr'] la valeur correcte
    // tenant compte des mouvements automatiques éventuellement ajoutés
    $SESSION_VARS["nbr_lignes_ecr"] = $i-1;
    //Totaux
    $html .= "<TR bgcolor=$colb_tableau>";
    $html .=   "<TD colspan=6 align=\"center\">\n";
    $html .=     "<TABLE align=\"right\" border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>";
    foreach ($TOTALDEB as $devise => $totaldeb) {
      setMonnaieCourante($devise);
      $html .=       "<TR>";
      $html .=          "<TD><b>"._("Total")." $devise</b>&nbsp&nbsp</TD>";
      $html .=          "<TD><b>".afficheMontant($totaldeb,false)."</b>&nbsp&nbsp</TD>";
      $html .=          "<TD><b>".afficheMontant($TOTALCRED[$devise],false)."</b></TD>";
      $html .=       "</TR>";
    }
    $html .=     "</TABLE>\n";
    $html .=   "</TD>";
    $html .="</TR>\n";

    $html.="</TABLE>";

		// Affichage des écritures de perception de taxes
		if(isset($HTML_GEN_LSB_id_taxe)&&($HTML_GEN_LSB_id_taxe != 0)){
		// Pour chaque devise étrangère veiller à l'équilibre total débit = total crédit
    foreach ($infos_devises as $devise => $value) {
    	// Si pour devise étrangère mnt débit != de mnt crédit, mouvementer automatique sa position de change
      if ( ($devise != $global_monnaie) and (round($TOTALDEB_TAX[$devise] - $TOTALCRED_TAX[$devise], $global_monnaie_courante_prec) != 0) ) {
        // Comptes de position de la devise
        $cptes_devise = getCptesLies($devise);

        // Si total débit > total crédit : crédit position de change et débiter C/V position de change
        if ($TOTALDEB_TAX[$devise] > $TOTALCRED_TAX[$devise]) {
          $mnt = $TOTALDEB_TAX[$devise] - $TOTALCRED_TAX[$devise];
          $cpte_debit = $cptes_devise["cvPosition"];
          $montant_debit = calculeCV($devise, $global_monnaie, $mnt);

          $devise_debit = $global_monnaie;

          $cpte_credit = $cptes_devise["position"];
          $montant_credit = $mnt;
          $devise_credit = $devise;
        } // fin si total débit > total crédit
        elseif($TOTALDEB_TAX[$devise] < $TOTALCRED_TAX[$devise]) { // total crédit > total débit pour cette devise
          // on débite la position de change et on crédit la C/V position de change
          $mnt = $TOTALCRED_TAX[$devise] - $TOTALDEB_TAX[$devise];
          $cpte_debit = $cptes_devise["position"];
          $montant_debit = $mnt;
          $devise_debit = $devise;

          $cpte_credit = $cptes_devise["cvPosition"];
          $montant_credit = calculeCV($devise, $global_monnaie, $mnt);
          $devise_credit = $global_monnaie;
        } // fin si total débit < total crédit

        $LIGNE_DEBIT = array('id_exo' => $SESSION_VARS["exercice"],
                             'id_jou' =>  $SESSION_VARS["journal"],
                             'date_comptable' => $SESSION_VARS["date_comptable"],
                             'libel_ecriture' => trim($SESSION_VARS["ecriture_taxe"][$last_line_tax]["libel_ecriture"]),
                             'num_cpte_comptable' => $cpte_debit,
                             'libel_cpte_comptable' => $cpte_debit." ".$SESSION_VARS["comptes"][$cpte_debit]["libel_cpte_comptable"],
                             'devise' => $devise_debit,
                             'mntdeb' => $montant_debit,
                             'mntcred' => '',
                             'auto' => true);
        $last_line_tax++;
        $SESSION_VARS["ecriture_taxe"][$last_line_tax] = $LIGNE_DEBIT;
        $TOTALDEB_TAX[$devise_debit] += $montant_debit;

        $LIGNE_CREDIT = array('id_exo' => $SESSION_VARS["exercice"],
                              'id_jou' =>  $SESSION_VARS["journal"],
                              'date_comptable' => $SESSION_VARS["date_comptable"],
                              'libel_ecriture' => trim($SESSION_VARS["ecriture_taxe"][$last_line_tax]["libel_ecriture"]),
                              'num_cpte_comptable' => $cpte_credit,
                              'libel_cpte_comptable' => $cpte_credit." ".$SESSION_VARS["comptes"][$cpte_credit]["libel_cpte_comptable"],
                              'devise' => $devise_credit,
                              'mntdeb' => '',
                              'mntcred' => $montant_credit,
                              'auto' => true);
        $last_line_tax++;
        $SESSION_VARS["ecriture_taxe"][$last_line_tax] = $LIGNE_CREDIT;
        $TOTALCRED_TAX[$devise_credit] += $montant_credit;

      } // fin si devise étrangère et total débit != total crédit
    } // mouvements automatiques
		$html .="<br>";
		$html .="<br><p align=\"center\"><b>"._("Perception des taxes")."</p>";
    $html .="<br>";
    $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    //En-tête du tableau
    $html .= "<TR bgcolor=$colb_tableau>";
    $html.="<TD><b>"._("n°")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Compte comptable")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Compte client")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Débit")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Crédit")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Libellé écriture")."</b></TD>";
    $html.="</TR>\n";

    // tableau des mouvements saisis
    $i=1;
    while (isset($SESSION_VARS["ecriture_taxe"][$i])) {
      if ($SESSION_VARS["ecriture_taxe"][$i]['num_cpte_comptable']) {

        if ($SESSION_VARS["ecriture_taxe"][$i]["auto"]) { // Ce mouvement a été généré automatiquement
          $beginColor = "<FONT color=blue>";
          $endColor = "</FONT>";
        } else {
          $beginColor = "";
          $endColor = "";
        }

        setMonnaieCourante($SESSION_VARS["ecriture_taxe"][$i]['devise']);

        //On alterne la couleur de fond du tableau
        if ($i%2)
          $color = $colb_tableau;
        else
          $color = $colb_tableau_altern;

        // Affichage des écritures
        $html .= "<TR bgcolor=$color >";
        $html .= "<TD>$beginColor<b>$i</b>$endColor</TD>"; // numéro de la ligne
        $html .= "<TD>$beginColor".$SESSION_VARS["ecriture_taxe"][$i]['libel_cpte_comptable']."$endColor</TD>";
        $html .= "<TD>$beginColor&nbsp ".$SESSION_VARS["ecriture_taxe"][$i]['cpte_client']."$endColor</TD>";
        $html .= "<TD>$beginColor&nbsp ".afficheMontant($SESSION_VARS["ecriture_taxe"][$i]['mntdeb'],true)."$endColor</TD>";
        $html .= "<TD>$beginColor&nbsp ".afficheMontant($SESSION_VARS["ecriture_taxe"][$i]['mntcred'],true)."$endColor</TD>";
        $html .= "<TD>$beginColor".$SESSION_VARS["ecriture_taxe"][$i]['libel_ecriture']."$endColor</TD>";
        $html .= "</TR>";
      }
      $i++;
    }
    // Pierre Ticket #682 : on réassigne à SESSION_VARS['nre_ligne_ecr'] la valeur correcte
    // tenant compte des mouvements automatiques éventuellement ajoutés
    $SESSION_VARS["nbr_lignes_ecr_tax"] = $i-1;



    //Totaux
    $html .= "<TR bgcolor=$colb_tableau>";
    $html .=   "<TD colspan=6 align=\"center\">\n";
    $html .=     "<TABLE align=\"right\" border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>";
    foreach ($TOTALDEB_TAX as $devise => $totaldeb) {
      setMonnaieCourante($devise);
      $html .=       "<TR>";
      $html .=          "<TD><b>"._("Total")." $devise</b>&nbsp&nbsp</TD>";
      $html .=          "<TD><b>".afficheMontant($totaldeb,false)."</b>&nbsp&nbsp</TD>";
      $html .=          "<TD><b>".afficheMontant($TOTALCRED_TAX[$devise],false)."</b></TD>";
      $html .=       "</TR>";
    }
    $html .=     "</TABLE>\n";
    $html .=   "</TD>";
    $html .="</TR>\n";

    $html.="</TABLE>";
	}

    if (($global_nom_ecran == "Opd-2") || ($global_nom_ecran == "Opa-2")) { // Ajout de mouvements
      if ($global_nom_ecran == "Opd-2") {
        $ann="Opd-1";
        if ($bouton_clique=="enregistrer") {
          $prochain="Opd-3";
          $operation=_("Enregistrer");
        } else {
          $prochain="Opd-4";
          $operation=_("Valider");
        }
      } else {
        $ann="Opa-1";
        if ($bouton_clique=="enregistrer") {
          $prochain="Opa-3";
          $operation=_("Enregistrer");
        } else {
          $prochain="Opa-4";
          $operation=_("Valider");
        }
      }

      //Bouton Enregistrer ou Valider
      $MyPage->addFormButton(1, 1, "but", $operation, TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("but", BUTP_AXS, 0);
      $MyPage->setFormButtonProperties("but", BUTP_PROCHAIN_ECRAN, $prochain);
      $MyPage->setFormButtonProperties("but", BUTP_CHECK_FORM, true);

      //Bouton Annuler
      $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
      //$MyPage->setFormButtonProperties("annuler", BUTP_AXS, 470);
      $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, $ann);
      $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM,false);
    } else if (($global_nom_ecran == "Opd-7") || ($global_nom_ecran == "Opa-7")) { // Modification
      if ($global_nom_ecran == "Opd-7") {
        if ($bouton_clique=="enregistrer") {
          $operation=_("Enregistrer");
          $prochain="Opd-8";
        } else {
          $operation=_("Valider");
          $prochain="Opd-9";
        }
        $an='Opd-6';
        $SESSION_VARS["ecran_precedent"] = 7;
      } else {
        if ($bouton_clique=="enregistrer") {
          $operation=_("Enregistrer");
          $prochain="Opa-8";
        } else {
          $operation=_("Valider");
          $prochain="Opa-9";
        }
        $an='Opa-6';
      }

      //Bouton Enregistrer ou Valider
      $MyPage->addFormButton(1, 1, "but", $operation, TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("but", BUTP_AXS, 0);
      $MyPage->setFormButtonProperties("but", BUTP_PROCHAIN_ECRAN, $prochain);
      $MyPage->setFormButtonProperties("but", BUTP_CHECK_FORM, true);

      //Bouton Annuler
      $MyPage->addFormButton(1, 2, "annuler", _("Retour"), TYPB_SUBMIT);
      // $MyPage->setFormButtonProperties("annuler", BUTP_AXS, 470);
      $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, $an);
      $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM,false);
    }

    //   ********* vérification validité *******
    // Si une validation est nécessaire
    if ( ($prochain == "Opd-4") || ($prochain == "Opa-4") || ($prochain == "Opd-9")|| ($prochain == "Opa-9")) {

      $i=1;
      while (isset($SESSION_VARS[$i])) {
      	$cpte_interne=false;
      	$isCompteGuichet=false;
      	$ep=false;
      	$solde_insuffisant = false;
      	if ($SESSION_VARS[$i]['num_cpte_comptable']) {
      		if (isCompteEpargne($SESSION_VARS[$i]['num_cpte_comptable'])) {
      			if ($SESSION_VARS[$i]['cpte_client']=='' ) {
      				$ep=true;
      				$cpte_ep=$SESSION_VARS[$i]['num_cpte_comptable'];
      			} else {
      				$solde_dispo = getSoldeDisponible($SESSION_VARS[$i]['id_compte']);
      				if (($SESSION_VARS[$i]['mntdeb'] !='') && ($solde_dispo < $SESSION_VARS[$i]['mntdeb']) ) {
      					$solde_insuffisant = true;
      					$cpt_insuf = $SESSION_VARS[$i]['cpte_client'];
      				}
      			}
      		}

      		$dateope = php2pg($SESSION_VARS["date_comptable"]);
      		$dateope=getPhpDateTimestamp($dateope);
      		if ( hasCompteInterne($SESSION_VARS[$i]['num_cpte_comptable'])) {
      			$cpte_interne=true;
      			$cc=$SESSION_VARS[$i]['num_cpte_comptable'];
      		}
      		if(isCompteGuichet($SESSION_VARS[$i]['num_cpte_comptable'])){
      			$isCompteGuichet=true;
      			$cc=$SESSION_VARS[$i]['num_cpte_comptable'];
      		}
      		if(date("y/m/d",$dateope) != date("y/m/d")){
      			$is_not_ope_today=true;
      		}
      	}
      	//D'aprés DIOUF, il doit etre possible de mouvementer le compte à une date antérieure : voir #1721. il faut avoir le droit de le faire
      	if (($cpte_interne)&&($is_not_ope_today)) {
      		//si la date de passage d'ecriture a un  compte comptable lié à un compte interne n'est pas  non aujourd'hui
      		if (!check_access(479)) {
      			$msgErr .= _("Le compte $cc ne peut être mouvementé qu'aujourd'hui !");
      		}
      	}
      if (($isCompteGuichet)&&($is_not_ope_today)) {
      			$msgErr .= _("Le compte $cc ne peut être mouvementé qu'aujourd'hui ! Le compte est lié à un guichet");
      	}
       if(($ep)&&($SESSION_VARS[$i]['cpte_client']=='' )) { // si compte comptable lié à un produit d'épargne, de crédit ou de garantie et compte client non renseigné
        $msgErr .= _("Vous avez essayé de mouvementer le compte $cc sans lui associer un compte client !");
       } elseif($solde_insuffisant)
       $msgErr .= _("Le solde du compte $cpt_insuf est insuffisant !");
       $i++;
      }
 
      /* ticket #359
      foreach ($TOTALDEB as $devise => $totaldeb)
      if (round($totaldeb - $TOTALCRED[$devise], $global_monnaie_courante_prec) != 0)
        $msgErr .= sprintf(_("Le total du débit (%s %s) n'est pas égal au total du crédit (%s %s étaient attendus)."), $totaldeb, $devise, $TOTALCRED[$devise], $SESSION_VARS[$ligneCred]["devise"])."<br/>";
      */

      $dateValide = false;
      $dateope=php2pg($SESSION_VARS["date_comptable"]);
      $dateope=getPhpDateTimestamp($dateope);

      $exos=getExercicesComptables();
      if (isset($exos))
        foreach($exos as $row) {
        $datedeb=getPhpDateTimestamp($row['date_deb_exo']);
        $datefin=getPhpDateTimestamp($row['date_fin_exo']);
        if ( ($row['etat_exo']!=3)
             && ( date("y/m/d",$dateope) >= date("y/m/d",$datedeb))
             && ( date("y/m/d",$dateope) <= date("y/m/d",$datefin)) )
          $dateValide = true;
      }

      if (!$dateValide) {
        $msgErr .= _("La date comptable n'est pas valide!")."  ";
      }
    }

    if ($msgErr != "") {
      $html_err = new HTML_erreur(_("Echec validation."));
      $html_err->setMessage($msgErr);
      $html_err->addButton("BUTTON_OK", $retour);
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit;
    } else {
      $MyPage->addHTMLExtraCode("html",$html);
      $MyPage->buildHTML();
      echo $MyPage->getHTML();
    }

  } else { // if (!$contenu)
    $html_err = new HTML_erreur(_("Echec écriture."));
    $html_err->setMessage(" "._("Vous devez saisir au moins un mouvement!")."  ");
    $html_err->addButton("BUTTON_OK", $retour);
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
/*}}}*/

/*{{{ Opd-6 : Modification des opérations diverses */
else if ($global_nom_ecran=="Opd-6") {
	 $SESSION_VARS['envoi'] = 0;
  // Récupération des comptes comptables qui peuvent être mouvementés dans le brouillard
  //$cptes_non_princ = getComptesBrouillard();

  // Récupération des comptes comptables qui peuvent être mouvementés dans le brouillard
  $SESSION_VARS["cptes_brouillard"] = array();
  $SESSION_VARS["cptes_brouillard"] = getComptesBrouillard();

  // récup des infos d'une écriture
  if($SESSION_VARS["ecran_precedent"] == 5){// if (!isset($SESSION_VARS["libel_ope"]))  // On vient de Opd-5 et pas de Opd-7
    $SESSION_VARS["libel_ope"]=$libel_ope;
    $SESSION_VARS["type_operation"]=$type_operation;
    $SESSION_VARS["ecr"]["id_his"]=$id_his;
    if (!isset($SESSION_VARS["date_comptable"]))
      $SESSION_VARS["date_comptable"] =pg2phpDate($date_ope);

    // Recup infos
    $row=getInfosEcritures();
    $i=1;
    $total_debit=array();
    $total_credit=array();
    if (isset($row))
      foreach($row as $infos)
      if ($infos["id_his"] == $SESSION_VARS["ecr"]["id_his"] )
      {
        $SESSION_VARS[$i]['id_mouvement']=$infos['id_mouvement'];
        $SESSION_VARS[$i]['id_his']=$infos['id_his'];
        $SESSION_VARS[$i]['id']=$infos['id'];
        $SESSION_VARS[$i]['id_jou']=$infos['id_jou'];
        $SESSION_VARS[$i]['id_exo']=$infos['id_exo'];
        $SESSION_VARS[$i]['id_taxe']=$infos['id_taxe'];
        $SESSION_VARS[$i]['sens_taxe']=$infos['sens_taxe'];
        $SESSION_VARS[$i]['libel_ecriture']=$infos['libel_ecriture'];
        $SESSION_VARS[$i]['type_operation']=$infos['type_operation'];
        $SESSION_VARS[$i]['date_comptable']=$infos['date_comptable'];
        $SESSION_VARS[$i]['num_cpte_comptable']=$infos['compte'];
        $SESSION_VARS[$i]['devise']=$infos["devise"];
        $SESSION_VARS[$i]['libel_cpte_comptable'] = $infos['compte']." ".$SESSION_VARS["comptes"][$infos['compte']];
        if ($infos['cpte_interne_cli']) {
          $infoscompte=getAccountDatas($infos['cpte_interne_cli']);


          $CLI = getClientDatas($infoscompte["id_titulaire"]);
          if ($CLI["statut_juridique"] == 1) // Personne physique
            $nom_cli = STR_replace("'","",$CLI["pp_nom"])." ".STR_replace("'","",$CLI["pp_prenom"]);
          elseif($CLI["statut_juridique"] == 2) // Personne morale
          $nom_cli = STR_replace("'","",$CLI["pm_raison_sociale"]);
          elseif($CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) // Groupe informel ou solidaire
          $nom_cli = STR_replace("'","",$CLI["gi_nom"]);

          $SESSION_VARS[$i]['cpte_client'] = $infoscompte['num_complet_cpte']." ".$nom_cli;
          $SESSION_VARS[$i]['id_client'] = $infoscompte["id_titulaire"];
          $SESSION_VARS[$i]['num_complet_cpte'] = $infoscompte['num_complet_cpte'];
        }
        $SESSION_VARS[$i]['id_compte']=$infos['cpte_interne_cli'];
        $SESSION_VARS[$i]['sens']=$infos['sens'];
        
        if($infos['id_taxe']>0) {
          $taxesInfos = getTaxesInfos();
          $infos['montant'] = ($infos['montant'] * (1 + $taxesInfos[$infos['id_taxe']]["taux"]));
        }

        if ($infos['sens']=='d') {
          $total_debit[$infos["devise"]] += recupMontant($infos['montant']);
          $SESSION_VARS[$i]['mntdeb']=recupMontant($infos['montant']);
          $SESSION_VARS[$i]['mntcred']='';
        } else {
          $total_credit[$infos["devise"]] += recupMontant($infos['montant']);
          $SESSION_VARS[$i]['mntdeb']='';
          $SESSION_VARS[$i]['mntcred']= recupMontant($infos['montant']);
        }
        $i++;
      }

    $SESSION_VARS["totaldeb"]=$total_debit;
    $SESSION_VARS["totalcred"]=$total_credit;
    $SESSION_VARS["devise"]=$SESSION_VARS[1]["devise"]; // FIXME Pas très propre mais obligé vu que dans ad_brouillard on répète la devise
    $devise = $SESSION_VARS["devise"];
    setMonnaieCourante($devise);
    $SESSION_VARS["id_taxe"]=$SESSION_VARS[1]["id_taxe"]; // FIXME Pas très propre mais obligé vu que dans ad_brouillard on répète la devise
    $SESSION_VARS["sens_oper_tax"]=$SESSION_VARS[1]["sens_taxe"];
    $SESSION_VARS["nbr_lignes_ecr"] = $i;
  } elseif($SESSION_VARS["ecran_precedent"] == 6 || $SESSION_VARS["ecran_precedent"] == 7 || $SESSION_VARS["ecran_precedent"] == 9){// if (isset($libel_ope))  /* On vient de Opd-6 */echo "on vient de opd-6";
 	  if($SESSION_VARS["ecran_precedent"] == 6){
 	  	if(!is_trad($libel_ope)){
    		$libel_ope = new Trad($libel_ope);
    		$SESSION_VARS["libel_ope"] = $libel_ope->get_id_str();
    	}
 	  	$SESSION_VARS["type_operation"] = $HTML_GEN_LSB_libel_ope_def;
 	  }else{
 	  	$libel_ope_trad = unserialize($SESSION_VARS["libel_ope"]);
 	  	if(is_trad($libel_ope_trad))
 	  	$SESSION_VARS["libel_ope"] = $libel_ope_trad->get_id_str();
 	  }
 	  $SESSION_VARS["devise"]=$SESSION_VARS[1]["devise"]; // FIXME Pas très propre mais obligé vu que dans ad_brouillard on répète la devise
    $devise = $SESSION_VARS["devise"];
    setMonnaieCourante($devise);
    //initialisé les tableaux des totaux des ecritures
    $total_debit=NULL;
    $total_credit=NULL;
    // récupération de la saisie : pour chaque ligne du tableau
    for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
      // if ($ {'cpte_comptable'.$i})
      {
        if ($ {'cpte_comptable'.$i}) {
          $SESSION_VARS[$i]['num_cpte_comptable'] = $ {'cpte_comptable'.$i};
        }

        if ($ {'id_compte'.$i})
        {
          $infoscompte=getAccountDatas($ {'id_compte'.$i});

          $CLI = getClientDatas($infoscompte["id_titulaire"]);
          if ($CLI["statut_juridique"] == 1) // Personne physique
            $nom_cli = STR_replace("'","",$CLI["pp_nom"])." ".STR_replace("'","",$CLI["pp_prenom"]);
          elseif($CLI["statut_juridique"] == 2) // Personne morale
          $nom_cli = STR_replace("'","",$CLI["pm_raison_sociale"]);
          elseif($CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) // Groupe informel ou solidaire
          $nom_cli = STR_replace("'","",$CLI["gi_nom"]);

          $SESSION_VARS[$i]['cpte_client']=$infoscompte['num_complet_cpte']." ".$nom_cli;
          $SESSION_VARS[$i]['id_compte'] = $ {'id_compte'.$i};
          $SESSION_VARS[$i]['id_client'] = $infoscompte["id_titulaire"];
          $SESSION_VARS[$i]['num_complet_cpte'] = $infoscompte['num_complet_cpte'];
        }
        if ($SESSION_VARS[$i]['mntdeb'] != "") {
          
          if($SESSION_VARS[$i]['mntdeb']>0) {
            $taxesInfos = getTaxesInfos();
            $infos_mntdeb = ($SESSION_VARS[$i]['mntdeb'] * (1 + $taxesInfos[$SESSION_VARS["id_taxe"]]["taux"]));
            
            $total_debit[$SESSION_VARS[$i]["devise"]] += ($infos_mntdeb);
            $SESSION_VARS[$i]['mntdeb'] = ($infos_mntdeb);
          }else{
            $total_debit[$SESSION_VARS[$i]["devise"]] += recupMontant($SESSION_VARS[$i]['mntdeb']);
            $SESSION_VARS[$i]['mntdeb'] = recupMontant($SESSION_VARS[$i]['mntdeb']);  
          }

          $SESSION_VARS[$i]['mntcred'] = '';
        }
        else if ($SESSION_VARS[$i]['mntcred'] != "") {
            
          if($SESSION_VARS["id_taxe"]>0) {
            $taxesInfos = getTaxesInfos();
            $infos_mntcred = ($SESSION_VARS[$i]['mntcred'] * (1 + $taxesInfos[$SESSION_VARS["id_taxe"]]["taux"]));
            
            $total_credit[$SESSION_VARS[$i]["devise"]] += ($infos_mntcred);
            $SESSION_VARS[$i]['mntcred'] = ($infos_mntcred);
          }else{
            $total_credit[$SESSION_VARS[$i]["devise"]] += recupMontant($SESSION_VARS[$i]['mntcred']);
            $SESSION_VARS[$i]['mntcred'] = recupMontant($SESSION_VARS[$i]['mntcred']);
          }

          $SESSION_VARS[$i]['mntdeb'] = '';
        }
      }
    }// fin for
    if (!is_null($total_debit)) {
    	$SESSION_VARS["totaldeb"]=$total_debit;
        $SESSION_VARS["totalcred"]=$total_credit;
    }

  }else{
 	  	$libel_ope_trad = unserialize($SESSION_VARS["libel_ope"]);
 	  	if(is_trad($libel_ope_trad))
 	  	$SESSION_VARS["libel_ope"] = $libel_ope_trad->get_id_str();
 	  }
  if (isset($nbr_lignes_ecr))
    $SESSION_VARS["nbr_lignes_ecr"] = $nbr_lignes_ecr;

  // Modification opérations
  $MyPage = new HTML_GEN2(_("Modification opérations"));

  $MyPage->addField("exercice",_("Exercice"), TYPC_TXT);
  $MyPage->setFieldProperties("exercice", FIELDP_DEFAULT, $SESSION_VARS["exercice"]);
  $MyPage->setFieldProperties("exercice", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("exercice", FIELDP_IS_LABEL, true);

  // libellé du journal
  $MyPage->addField("libel_jou",_("Journal"), TYPC_TTR);
  if(!is_trad(unserialize($SESSION_VARS["libel_jou"]))){
    	$libel_jou = new Trad(unserialize($SESSION_VARS["libel_jou"]));	
    }else{
    	$libel_jou = unserialize($SESSION_VARS["libel_jou"]);
   }
  $MyPage->setFieldProperties("libel_jou", FIELDP_DEFAULT, $libel_jou);
  $MyPage->setFieldProperties("libel_jou", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("libel_jou", FIELDP_IS_LABEL, true);

  // La date de valeur
  $MyPage->addField("date_ope",_("Date opération"), TYPC_DTE);
  $MyPage->setFieldProperties("date_ope", FIELDP_DEFAULT, $SESSION_VARS["date_comptable"]);
  $MyPage->setFieldProperties("date_ope", FIELDP_IS_REQUIRED, false);
  // $MyPage->setFieldProperties("date_ope", FIELDP_IS_LABEL, true);

  // Libellé opération
  $choices=array();
  $list_libel = getLEL(); // Récupère de tous les libellés des écritures libres
  $choices[0]=_("Autre libellé");
  foreach ($list_libel as $key => $value){
  	$libel_ope = new Trad($value["libel_ope"]);
  	$choices[$value["type_operation"]] = $libel_ope->traduction();
  }
  $MyPage->addField("libel_ope_def",_("Liste libellé"), TYPC_LSB);
  $MyPage->setFieldProperties("libel_ope_def", FIELDP_ADD_CHOICES, $choices);
  $MyPage->setFieldProperties("libel_ope_def", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("libel_ope_def", FIELDP_DEFAULT, $SESSION_VARS["type_operation"]);
  $MyPage->setFieldProperties("libel_ope_def", FIELDP_JS_EVENT, array("onChange"=>"changeLibel();"));
  	
  $MyPage->addField("libel_ope",_("Libellé opération"), TYPC_TTR);
  $libel_ope = new Trad($SESSION_VARS["libel_ope"]);
  $MyPage->setFieldProperties("libel_ope", FIELDP_DEFAULT, $libel_ope);
  $MyPage->setFieldProperties("libel_ope", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("libel_ope", FIELDP_WIDTH, 40);

  $codejs ="\n\nfunction changeLibel() {";
  $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==0)\n\t";
	$codejs .= "{\n\t\tdocument.ADForm.libel_ope.value ='';";
	//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =false;";
	$codejs .= "}else{\n";
	foreach($choices as $type_operation=>$value) {
		$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==$type_operation)\n\t";
		$codejs .= "\n\t{";
		foreach (get_langues_installees() as $code => $langue){
			$libel_ope = new Trad($list_libel[$type_operation]['libel_ope']);
	  		$codejs .= "\n\t\tdocument.ADForm.HTML_GEN_ttr_libel_ope_".$code.".value =\"".$libel_ope->traduction($code)."\";";
		}
		//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =true;";
		$codejs .= "}\n";
  }
  $codejs .= "}}\n";
  $MyPage->addJS(JSP_FORM, "jslibel", $codejs);
  
    
  $MyPage->addTableRefField("devise", _("Devise de l'opération"), "devise");

  $MyPage->setFieldProperties("devise", FIELDP_DEFAULT, $devise);
  $MyPage->setFieldProperties("devise", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("devise", FIELDP_HAS_CHOICE_AUCUN, false);

  // TVA à appliquer
    $MyPage->addField("id_taxe",_("TVA à appliquer"), TYPC_LSB);
    $liste_taxe = getListeTaxes();
    $MyPage->setFieldProperties("id_taxe", FIELDP_ADD_CHOICES, $liste_taxe);
    $MyPage->setFieldProperties("id_taxe", FIELDP_DEFAULT, $SESSION_VARS["id_taxe"]);
    $MyPage->setFieldProperties("id_taxe", FIELDP_JS_EVENT, array("OnChange"=>"updateTaxe();"));
    $MyPage->setFieldProperties("id_taxe", FIELDP_IS_REQUIRED, false);
    $MyPage->addField("taux_taxe", _("Taux de la taxe"), TYPC_PRC);
    $MyPage->setFieldProperties("taux_taxe", FIELDP_DEFAULT, $SESSION_VARS["taux_taxe"]);
   	$MyPage->setFieldProperties("taux_taxe", FIELDP_IS_LABEL,true);
   	// Sens de l'opération
	  $sens=array("d"=>_("Paiement de tva déductible"),"c"=>_("Perception de tva collectée"));
	  $MyPage->addField("sens_oper_tax",_("Sens de la taxe"), TYPC_LSB);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_ADD_CHOICES, $sens);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_DEFAULT, $SESSION_VARS["sens_oper_tax"]);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_HAS_CHOICE_AUCUN, true);
   	$taxesInfos = getTaxesInfos();
   	$js = "function updateTaxe()\n{\n ";
   	foreach($taxesInfos as $key=>$value) {
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value == ".$value["id"].")) {";
     $js .= "document.ADForm.taux_taxe.value = '".$value["taux"]*(100)."';";
     $js .= "document.ADForm.HTML_GEN_LSB_sens_oper_tax.disabled = false;";
     $js .= "}";
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value == '0')){";
     $js .= "document.ADForm.taux_taxe.value = '';";
     $js .= "document.ADForm.HTML_GEN_LSB_sens_oper_tax.disabled = true;";
     $js .= "}";
   	}
	 	$js .= "};";
	 	$js .= "updateTaxe();";
	 	$MyPage->addJS(JSP_FORM, "jstest", $js);
		// Checkform
  	$jscheck = "if (document.ADForm.HTML_GEN_LSB_id_taxe.value != 0)
           {
             if (document.ADForm.HTML_GEN_LSB_sens_oper_tax.value == 0)
           {
             msg += '- "._("Le champ Sens de la taxe doit être renseigné")."\\n';
             ADFormValid = false;
           }
           }";

  	$MyPage->addJS(JSP_BEGIN_CHECK, "JS2", $jscheck);


  $MyPage->addHiddenType("bouton_clique");
  $MyPage->addHiddenType("nbr_lignes_ecr", $SESSION_VARS["nbr_lignes_ecr"]);

  $html = "<link rel=\"stylesheet\" href=\"/lib/misc/js/chosen/css/chosen.css\">";
  $html .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $html .= "<script src=\"/lib/misc/js/chosen/chosen.jquery.js\" type=\"text/javascript\"></script>";

  $html .= "<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

  //En-tête du tableau
  $html .="<TR bgcolor=$colb_tableau>";
  $html .="<TD align=\"center\"><b>"._("Compte comptable")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Compte client")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Débit")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Crédit")."</b></TD>";
  $html .="</TR>\n";

  // contenu du tableau
  for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
  	if(!$SESSION_VARS[$i]['auto']){
    //On alterne la couleur de fond du tableau
    if ($i%2)
      $color = $colb_tableau;
    else
      $color = $colb_tableau_altern;

    // Ligne de saisie
    $html .= "<TR bgcolor=$color>\n";

    // Comptes comptables qui peuvent être mouvements dans le brouillard
    $html .= "<TD><select class=\"chosen-select\" NAME=\"cpte_comptable$i\" style=\"width:250px\" ";
    $html .= "onchange=\"document.ADForm.cpte_client$i.value ='';document.ADForm.id_compte$i.value ='';\">\n";
    //$SESSION_VARS[$i]['id_compte'] = $ {'id_compte'.$i};
    $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
    if (isset($SESSION_VARS["cptes_brouillard"]))
      foreach($SESSION_VARS["cptes_brouillard"] as $key=>$value)
      if ($SESSION_VARS[$i]['num_cpte_comptable']==$key)
        $html .= "<option value=$key selected>".$key." ".$value."</option>\n";
      else
        $html .= "<option value=$key >".$key." ".$value."</option>\n";
    $html .= "</select></TD>\n";

    //num Compte complet du client
    $html.="<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=32 value=\"".$SESSION_VARS[$i]['cpte_client']."\" disabled=true>\n";
    $html .="<FONT size=\"2\"><A href=# onclick=\"getCompte(document.ADForm.cpte_comptable$i.value,'cpte_client$i','id_compte$i');return false;\">"._("Recherche")."</A></FONT></TD>\n";

    // id du compte du client
    $html.="<INPUT TYPE=\"hidden\" NAME=\"id_compte$i\" value=\"".$SESSION_VARS[$i]['id_compte']."\">\n";

    // debit
    setMonnaieCourante($SESSION_VARS[$i]['devise']);
    $html .= "<TD><INPUT NAME=\"mntdeb$i\" TYPE=\"text\" size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntdeb'],false)."\" onchange=\"checkDebit();CalculTotaux(); value = formateMontant(value);\"></TD>\n";

    // Crédit
    $html .="<TD><INPUT NAME=\"mntcred$i\" TYPE=\"text\" size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntcred'],false)."\" onchange=\"checkCredit();CalculTotaux(); value = formateMontant(value);\"></TD>\n";

    // id_mouvement
    $html .="<INPUT TYPE=\"hidden\" NAME=\"id_mouvement$i\" value=\"".$SESSION_VARS[$i]['id_mouvement']."\">\n";

    // id_his
    $html .="<INPUT TYPE=\"hidden\" NAME=\"id_his$i\" value=\"".$SESSION_VARS[$i]['id_his']."\">\n";

    // id_operation
    $html .="<INPUT TYPE=\"hidden\" NAME=\"id$i\" value=\"".$SESSION_VARS[$i]['id']."\">\n";

    $html .= "</TR>\n";
  	}
  }
  //Totaux
  $html .= "<TR bgcolor=$colb_tableau><TD colspan=6 align=\"center\">\n";
  $html .= "<TABLE align=\"right\">";
  foreach ($SESSION_VARS["totalcred"] as $dev_totaux => $totaux_cred ){
  	setMonnaieCourante($dev_totaux);
  	$html .=" <TR>";
  	$html .= "<TD><b>"._("Totaux")." ($dev_totaux) : </b></TD>";
    $html .= "<TD><INPUT TYPE=\"text\" NAME=\"tot_debit\" size=12 disabled=true VALUE='".afficheMontant($SESSION_VARS["totaldeb"][$dev_totaux],false)."'></TD>";
	$html .="<TD><INPUT TYPE=\"text\" NAME=\"tot_credit\" size=12 disabled=true VALUE='".afficheMontant($totaux_cred,false)."'></TD>";
	$html.="</TR>";
  }
  $html.="</TABLE>\n";
  $html .= "</TD> </TR>\n";
  $html.="</TABLE>";

  //Bouton Ajout ligne
  $MyPage->addFormButton(1, 1, "ajout", _("Ajouter ligne"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ajout", BUTP_AXS, 471);
  $MyPage->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, "Opd-6");
  $MyPage->setFormButtonProperties("ajout", BUTP_CHECK_FORM, false);
  $js = "document.ADForm.nbr_lignes_ecr.value = ".$SESSION_VARS['nbr_lignes_ecr']."+1;\n";
  $MyPage->setFormButtonProperties("ajout", BUTP_JS_EVENT, array("onclick" => $js));

  //Bouton Enregistrer
  $MyPage->addFormButton(1, 2, "enregistrer", _("Enregistrer"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("enregistrer", BUTP_AXS, 470);
  $MyPage->setFormButtonProperties("enregistrer", BUTP_PROCHAIN_ECRAN, "Opd-7");
  $MyPage->setFormButtonProperties("enregistrer", BUTP_CHECK_FORM, true);
  $MyPage->setFormButtonProperties("enregistrer", BUTP_JS_EVENT, array("onclick" => "document.ADForm.bouton_clique.value='enregistrer';"));

  //Bouton Valider
  $MyPage->addFormButton(1, 3, "valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_AXS, 471);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Opd-7");
  $MyPage->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.bouton_clique.value='valider';"));

  //Bouton Annuler
  $MyPage->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
  // $MyPage->setFormButtonProperties("annuler", BUTP_AXS, 470);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Opd-5");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM,false);

  //JavaScript
  $html .= "<script type=\"text/javascript\">\n";

  $html .= "var config = { '.chosen-select' : {} }\n";
  $html .= "for (var selector in config) {\n";
  $html .= "$(selector).chosen(config[selector]); }\n";

  //function  getCompte : ouvre une fenêtre de recherche de compte de client
  $html .= "function getCompte(cpte_comptable,cpte_client,id_compte)\n{\n";
  $html.= "var ch;\n";
  $html.= "ch='../modules/compta/rech_compte_client.php?m_agc=".$_REQUEST['m_agc']."&field_name='+cpte_client+'&id_compte='+id_compte+'&field_cpte_comptable='+cpte_comptable;\n";
  $html .= "OpenBrw(ch, '"._("Recherche")."');\n";
  $html .= "}\n";

  // Fonction check crédit g
  $html .="function checkCredit()\n{\n";
  for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
    $html.="if(document.ADForm.mntcred$i.value)\n";
    $html.="\n\tdocument.ADForm.mntdeb$i.value = '';\n";
  }
  $html.="}\n";

  // Fonction check débit  g
  $html .="function checkDebit()\n{\n";
  for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
    $html.="if(document.ADForm.mntdeb$i.value)\n";
    $html.="\n\tdocument.ADForm.mntcred$i.value = '';\n";
  }
  $html.="}\n";

  //Fonction calcul totaux
  $html .="function CalculTotaux()\n{\n";
  $html.="var debit =new Number(0);\n";
  $html.="var credit =new Number(0);\n";
  for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
    $html .="if(document.ADForm.mntdeb$i.value)\n{\n";
    $html .="debit +=recupMontant(document.ADForm.mntdeb$i.value);";
    $html .="\n}\n";

    $html .="if(document.ADForm.mntcred$i.value)\n{\n";
    $html .="credit +=recupMontant(document.ADForm.mntcred$i.value);";
    $html.="\n}\n";
  }
  $html.="\n\tdocument.ADForm.tot_debit.value = formateMontant(debit);\n";
  $html.="\n\tdocument.ADForm.tot_credit.value = formateMontant(credit);\n}\n";

  $html .= "</script>\n";
  $SESSION_VARS["ecran_precedent"] = 6;
  $MyPage->addHTMLExtraCode("html",$html);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Opd-5 Opa-5 : Choix de l'écriture à modifier */
else if ( ($global_nom_ecran=="Opd-5") || ($global_nom_ecran=="Opa-5") ) {
	$SESSION_VARS['envoi'] = 0;
  unset($SESSION_VARS["libel_ope"]);
  unset($SESSION_VARS["type_operation"]);
  unset($SESSION_VARS["totaldeb"]);
  unset($SESSION_VARS["totalcred"]);
  for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++)
    unset($SESSION_VARS[$i]);

  //unset($SESSION_VARS["date_comptable"]);
  if ($date_ope!='') {
      if($date_ope=='tous'){
          $date_ope = '';
      }
      $SESSION_VARS["date_comptable"] = $date_ope;
  }
  
  if(isset($gest)) {
    $SESSION_VARS["gest"] = trim($gest);
  }
  
  if(isset($prochain_ecran)) {
      $SESSION_VARS["prochain_ecran"] = $prochain_ecran;
  }
  
  if (!isset($SESSION_VARS["exercice"])) { //si on vient de l'ecran Ecr-1
    $SESSION_VARS["exercice"] = $exercice;
    $SESSION_VARS["journal"] = $journal;

    //Récupération du journal
    $info=getInfosJournal($journal);
    $SESSION_VARS["cpte_princ_jou"]=$info[$journal]['num_cpte_princ'];
    $SESSION_VARS["libel_jou"]=serialize($info[$journal]['libel_jou']);
    $SESSION_VARS["long_num_jou"]=strlen($SESSION_VARS["cpte_princ_jou"]);
  }

  // Le prochain écran selon que le journal est principal ou non
  if ($SESSION_VARS["cpte_princ_jou"])
    $prochainEcran='Opa-6';
  else
    $prochainEcran='Opd-6';

  $MyPage = new HTML_GEN2(_("Choix écriture à modifier"));

  $MyPage->addField("exercice_compta",_("Exercice"), TYPC_TXT);
  $MyPage->setFieldProperties("exercice_compta", FIELDP_DEFAULT, $SESSION_VARS["exercice"]);
  $MyPage->setFieldProperties("exercice_compta", FIELDP_IS_LABEL, true);

  // libellé du journal
  $MyPage->addField("libel_jou",_("Journal"), TYPC_TTR);
  if(!is_trad(unserialize($SESSION_VARS["libel_jou"]))){
    $libel_jou = new Trad(unserialize($SESSION_VARS["libel_jou"]));	
  }else{
    $libel_jou = unserialize($SESSION_VARS["libel_jou"]);
  }
  $MyPage->setFieldProperties("libel_jou", FIELDP_DEFAULT, $libel_jou);
  $MyPage->setFieldProperties("libel_jou", FIELDP_IS_LABEL, true);
    
  //Gestionnaire- Tri par agent gestionnaire
  $list_users = array();
  $users = getEcritureLibreUtilisateurs();
  if (isset($users)) {
    foreach($users as $key=>$value) {
      $list_users[$value["login"]] = $value["fullname"];
    }
  }
  $MyPage->addField("gest",_("Gestionnaire"), TYPC_LSB);
  $MyPage->setFieldProperties("gest", FIELDP_ADD_CHOICES, $list_users);
  $MyPage->setFieldProperties("gest", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("gest", FIELDP_HAS_CHOICE_TOUS, true);
  
  if($SESSION_VARS["gest"]!='') {
    $MyPage->setFieldProperties("gest", FIELDP_DEFAULT, $SESSION_VARS["gest"]);
  }
  
  // Liste date de valeur
  $list_dates = array();
  $dates = getEcritureLibreDates();
  
  if (isset($dates)) {
    foreach($dates as $key=>$value) {
      $list_dates[pg2phpDate($value['date_comptable'])] = pg2phpDate($value['date_comptable']);
    }
  }
  
  if (isset($SESSION_VARS["date_comptable"]) && trim($SESSION_VARS["date_comptable"])!='') {
      $list_dates[$SESSION_VARS["date_comptable"]] = $SESSION_VARS["date_comptable"];
  }
  
  ksort($list_dates);
  $list_dates = array_merge(array("tous" => "[Tous]"), array_unique($list_dates));
  
  $MyPage->addField("date_ope",_("Date opération"), TYPC_LSB);
  $MyPage->setFieldProperties("date_ope", FIELDP_ADD_CHOICES, $list_dates);
  $MyPage->setFieldProperties("date_ope", FIELDP_HAS_CHOICE_AUCUN, false);
  //$MyPage->setFieldProperties("date_ope", FIELDP_HAS_CHOICE_TOUS, true);
  
  if (isset($SESSION_VARS["date_comptable"])) {
    $MyPage->setFieldProperties("date_ope", FIELDP_DEFAULT, $SESSION_VARS["date_comptable"]);
  }
  
  $MyPage->addHiddenType("modifier", "Mod/Supp");
  $MyPage->addHiddenType("exercice", $SESSION_VARS["exercice"]);
  $MyPage->addHiddenType("journal", $SESSION_VARS["journal"]);
  $MyPage->addHiddenType("java_enabled", '1');
  
  //Bouton Rechercher
  $MyPage->addFormButton(1, 1, "search", _("Rechercher"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("search", BUTP_AXS, 470);
  $MyPage->setFormButtonProperties("search", BUTP_PROCHAIN_ECRAN, $SESSION_VARS["prochain_ecran"]);

  //Bouton Annuler
  $MyPage->addFormButton(1, 2, "annuler", _("Retour"), TYPB_SUBMIT);
  //$MyPage->setFormButtonProperties("annuler", BUTP_AXS, 470);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Ecr-1");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $SESSION_VARS["ecran_precedent"] = 5;

  // Tableau des écritures
  $html = "<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

  //En-tête du tableau
  $html .="<TR bgcolor=$colb_tableau>";
  $html .="<TD><b>"._("n°")."</b></TD>";
  $html .="<TD><b>"._("Gestionnaire")."</b></TD>";
  $html .="<TD><b>"._("N° transaction")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Libelle ecriture")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Date comptable")."</b></TD>";
  $html .="</TR>\n";

  $i=1;
  $row=getLibelEcritures($SESSION_VARS["gest"], $SESSION_VARS["date_comptable"]);

  if (isset($row))
    foreach($row as $infos)
    if ( ($infos["id_jou"]==$SESSION_VARS["journal"]) && ($infos["id_exo"]==$SESSION_VARS["exercice"]) ) {
      if (isset($SESSION_VARS["date_comptable"]))
        $date_compare=$SESSION_VARS["date_comptable"];
      else
        $date_compare=pg2phpDate($infos["date_comptable"]);

      //if ( pg2phpDate($infos["date_comptable"])==$date_compare)
      {
        //On alterne la couleur de fond du tableau
        if ($i%2)
          $color = $colb_tableau;
        else
          $color = $colb_tableau_altern;

        // Affichage des ecritures
        $html .= "<TR bgcolor=$color>\n";
        // Numero ecriture
        $html .= "<TD><b>".$i."</b></TD>";
        // Gestionnaire
        $html .= "<TD><b>".getUtilisateurFullNameByIdHis($infos['id_his'])."</b></TD>";
        // id_his
        $html .= "<TD><b>".$infos['id_his']."</b></TD>";
        // libelle ecriture
        if($infos['libel_ecriture'] != NULL){
        	$libel_ecriture_trad = new Trad($infos['libel_ecriture']);
        	$libel_ecriture = $libel_ecriture_trad->traduction();
        }
        $html .= "<TD>".$libel_ecriture."</TD>\n";
        // Date ecriture
        $html .= "<TD>".pg2phpDate($infos['date_comptable'])."</TD>\n";
        // Lien modifier
        $html.="<TD><FONT size=\"2\"><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$prochainEcran;
        $html.="&exercice=".$SESSION_VARS["exercice"]."&journal=".$SESSION_VARS["journal"];
        $libel_joue_trad = new Trad($SESSION_VARS["libel_jou"]);
        $libel_jou = $libel_joue_trad->traduction();
        $html.="&libel_jou=".$libel_jou."&date_ope=".$infos['date_comptable'];
        //$html.="&libel_ope=".$infos['libel_ecriture']."\">Modifier</A></FONT></TD>\n";
        $html.="&libel_ope=".$infos["libel_ecriture"]."&type_operation=".$infos["type_operation"]."&id_his=".$infos["id_his"]."\">"._("Modifier")."</A></FONT></TD>\n";
        // Lien Supprimer
        $html.="<TD><FONT size=\"2\"><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Opa-10";
        $html.="&id_his=".$infos["id_his"]."\" OnClick=\"return confirm('"._("Etes-vous sur de vouloir supprimer l\'ecriture")." \\'".$infos['libel_ecriture']."\\' ?');\">"._("Supprimer")."</A></FONT></TD>\n";
        $html .= "</TR>\n";
        $i++;
      }
    }
  $html.="</TABLE>";
  
  $MyPage->addHTMLExtraCode("html",$html);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Opd-4 Opa-4 Opd-9 Opa-9 : Validation de l'écriture */
else if (($global_nom_ecran == "Opd-4")|| ($global_nom_ecran == "Opa-4") || ($global_nom_ecran == "Opd-9") || ($global_nom_ecran == "Opa-9")) {
  global $global_monnaie, $global_id_agence, $global_nom_login;
  //controle d'envoie du formulaire
	$SESSION_VARS['envoi']++;
	if( $SESSION_VARS['envoi'] != 1 ) {
		$html_err = new HTML_erreur(_("Confirmation"));
	    $html_err->setMessage(_("Donnée dèjà envoyée"));
	    $html_err->addButton("BUTTON_OK", 'Gen-14');
	    $html_err->buildHTML();
	    echo $html_err->HTML_code;
	    exit();
	} 
	//fin contrôle
  $SESSION_VARS["ecran_precedent"] = 9;
  $idope=1;
  for ($num_op=1;$num_op<=$SESSION_VARS["nbr_lignes_ecr"];$num_op++)
    if ($SESSION_VARS[$num_op]['num_cpte_comptable']) {
      $DATA[$num_op]['id_mouvement'] = $SESSION_VARS[$num_op]['id_mouvement'];
      $DATA[$num_op]['id_his'] = $SESSION_VARS[$num_op]['id_his'];
      $DATA[$num_op]['id'] = $idope;
      $DATA[$num_op]['compte'] = $SESSION_VARS[$num_op]['num_cpte_comptable'];
      $DATA[$num_op]['cpte_interne_cli'] = $SESSION_VARS[$num_op]['id_compte'];
      if ($SESSION_VARS[$num_op]['mntdeb']) {
        $DATA[$num_op]['sens'] = 'd';
        $mnt_debit = arrondiMonnaiePrecision($SESSION_VARS[$num_op]['mntdeb']);
        $DATA[$num_op]['montant'] = $mnt_debit;
        $DATA[$num_op]['libel_cpte_comptable']['d'] = $SESSION_VARS[$num_op]['libel_cpte_comptable'];
      } else {
        $DATA[$num_op]['sens'] = 'c';
        $mnt_credit = arrondiMonnaiePrecision($SESSION_VARS[$num_op]['mntcred']);
        $DATA[$num_op]['montant'] = $mnt_credit;
        $DATA[$num_op]['libel_cpte_comptable']['c'] = $SESSION_VARS[$num_op]['libel_cpte_comptable'];
      }
      $DATA[$num_op]['date_comptable'] = $SESSION_VARS[$num_op]['date_comptable'];
      $DATA[$num_op]['libel_ecriture'] = ($SESSION_VARS[$num_op]['libel_ecriture']); // unserialize
      $DATA[$num_op]['type_operation'] = $SESSION_VARS[$num_op]['type_operation'];
      $DATA[$num_op]['id_jou'] = $SESSION_VARS[$num_op]['id_jou'];
      $DATA[$num_op]['id_exo'] = $SESSION_VARS[$num_op]['id_exo'];
      $DATA[$num_op]['validation'] = 't';
      $DATA[$num_op]['devise'] =  $SESSION_VARS[$num_op]["devise"];
      $DATA[$num_op]['id_ag'] =  $global_id_agence;
      $DATA[$num_op]['id_client'] =  $SESSION_VARS[$num_op]['num_complet_cpte'];
  }
	//Ajouter les écritures de taxes dans le tableau
  $nbr_tot_lignes_ecr = $SESSION_VARS["nbr_lignes_ecr"];
  if(isset($SESSION_VARS["nbr_lignes_ecr_tax"])){
  	$idope++;
		$nbr_tot_lignes_ecr += $SESSION_VARS["nbr_lignes_ecr_tax"];
		$proch_num_op = $SESSION_VARS["nbr_lignes_ecr"] + 1;
		$ligne_tax = 1;
		for ($num_op=$proch_num_op;$num_op<=$nbr_tot_lignes_ecr;$num_op++)
	    if ($SESSION_VARS["ecriture_taxe"][$ligne_tax]['num_cpte_comptable']) {
	      $DATA[$num_op]['id_mouvement'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['id_mouvement'];
	      $DATA[$num_op]['id_his'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['id_his'];
	      $DATA[$num_op]['id'] = $idope;
	      $DATA[$num_op]['compte'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['num_cpte_comptable'];
	      $DATA[$num_op]['cpte_interne_cli'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['id_compte'];
	      if ($SESSION_VARS["ecriture_taxe"][$ligne_tax]['mntdeb']) {
	        $DATA[$num_op]['sens'] = 'd';
            $mnt_debit = arrondiMonnaiePrecision($SESSION_VARS["ecriture_taxe"][$ligne_tax]['mntdeb']);
            $DATA[$num_op]['montant'] = $mnt_debit;
            $DATA[$num_op]['libel_cpte_comptable']['d'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['libel_cpte_comptable'];
	      } else {
	        $DATA[$num_op]['sens'] = 'c';
            $mnt_credit = arrondiMonnaiePrecision($SESSION_VARS["ecriture_taxe"][$ligne_tax]['mntcred']);
            $DATA[$num_op]['montant'] = $mnt_credit;
	        $DATA[$num_op]['libel_cpte_comptable']['c'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['libel_cpte_comptable'];
	      }
	      $DATA[$num_op]['date_comptable'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['date_comptable'];
	      $DATA[$num_op]['libel_ecriture'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['libel_ecriture'];
	      $DATA[$num_op]['type_operation'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['type_operation'];
	      $DATA[$num_op]['id_jou'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['id_jou'];
	      $DATA[$num_op]['id_exo'] = $SESSION_VARS["ecriture_taxe"][$ligne_tax]['id_exo'];
	      $DATA[$num_op]['validation'] = 't';
	      $DATA[$num_op]['devise'] =  $SESSION_VARS["ecriture_taxe"][$ligne_tax]["devise"];
	      $DATA[$num_op]['id_ag'] =  $global_id_agence;
	      $DATA[$num_op]['id_client'] =  $SESSION_VARS["ecriture_taxe"][$ligne_tax]['num_complet_cpte'];
	      $ligne_tax++;
	    }
  }
  $DATA_ECR[1]["date"] = $DATA[1]['date_comptable'];
  $libel_ecriture = $DATA[1]['libel_ecriture'];
  
  if(is_trad(unserialize($libel_ecriture))){

    $libel_ecriture_trad = unserialize($libel_ecriture);

    $DATA_ECR[1]["libelle_ecriture"] = $libel_ecriture_trad->traduction();
  }else{

    $libel_ecriture_trad = new Trad();
    $libel_ecriture_trad->set_traduction(get_langue_systeme_par_defaut(), ($libel_ecriture));
    $libel_ecriture_trad->save();

    $DATA_ECR[1]["libelle_ecriture"] = $libel_ecriture_trad->traduction();
  }

  $DATA_ECR[1]["type_operation"] = $DATA[1]['type_operation'];
  for ($num_op=1; $num_op <= $nbr_tot_lignes_ecr; $num_op++){
   if($DATA[$num_op]['libel_cpte_comptable']['d']){
 	 	$DATA_ECR[$num_op]["id_cpte_deb"] = $DATA[$num_op]["compte"];
   	$DATA_ECR[$num_op]["nom_cpte_deb"] = $DATA[$num_op]['libel_cpte_comptable']['d'];
   }
   if($DATA[$num_op]['libel_cpte_comptable']['c']){
   	$DATA_ECR[$num_op]["id_cpte_cre"] = $DATA[$num_op]["compte"];
   	$DATA_ECR[$num_op]["nom_cpte_cre"] = $DATA[$num_op]['libel_cpte_comptable']['c'];
   }
   $DATA_ECR[$num_op]["montant"] = $DATA[$num_op]['montant'];
   $DATA_ECR[$num_op]['devise'] = $DATA[$num_op]['devise'];
   $DATA_ECR[$num_op]['id_client'] = $DATA[$num_op]['id_client'];
  }
  // Récupération des références de la pièce justificative
  // Ces infos ont normalement été postées à partir de l'écran précédent
  $PIECE = array("type_piece" => $type_piece, "num_piece" => $num_piece, "date_piece" => $date_piece, "remarque" => $remarque);
  $fonction = 470; // passage écritures libres
  if ($DATA[1]['id_his'] != ''){
    $nom_initiateur = getUtilisateurFullNameByIdHis($DATA[1]['id_his']);
  }else{
    $nom_initiateur = null;
  }
  $erreur = validationEcrituresComptables($DATA, $PIECE, $fonction,$nom_initiateur);
  if ($erreur->errCode == NO_ERR) {
    // Génération d'une pièce comptable pour passage ecriture libres
    $userid = get_login_utilisateur($global_nom_login);
    $username = get_utilisateur_nom($userid);
    print_recu_ecriture($DATA_ECR, $username, $nbr_tot_lignes_ecr, $erreur->param,$nom_initiateur);

    //HTML
    $MyPage = new HTML_message(_("Confirmation validation écritures"));
    $MyPage->setMessage(_("Les écritures ont été validées avec succès !")."<br/><br/>"._("Numéro de transaction :")." <b><code>".$erreur->param."</code></b>");
    $MyPage->addButton(BUTTON_OK, "Ecr-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $html_err = new HTML_erreur(_("Validation écritures comptables"));
    $html_err->setMessage(_("Echec")." : ".$error[$erreur->errCode].$erreur->param);

    if (($global_nom_ecran == "Opa-4"))
      $retour = "Opa-1";
    else if (($global_nom_ecran == "Opa-9"))
      $retour = "Opa-6";
    else if (($global_nom_ecran == "Opd-4"))
      $retour = "Opd-1";
    else if (($global_nom_ecran == "Opd-9"))
      $retour = "Opd-6";

    $html_err->addButton("BUTTON_OK", $retour);
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
/*}}}*/

/*{{{ Opd-8 Opa-8 : Modification dans la base */
else if ( ($global_nom_ecran=="Opd-8") || ($global_nom_ecran=="Opa-8")) {
	$SESSION_VARS['envoi'] = 0;
  for ($num_op=1;$num_op<=$SESSION_VARS["nbr_lignes_ecr"];$num_op++)
    if (($SESSION_VARS[$num_op]['num_cpte_comptable']) || ($SESSION_VARS[$num_op]['id_mouvement'])) {
    	if(!$SESSION_VARS[$num_op]["auto"]){
	      $DATA[$num_op]['id_mouvement'] = $SESSION_VARS[$num_op]['id_mouvement'];
	      $DATA[$num_op]['id_his'] = $SESSION_VARS[$num_op]['id_his'];
	      $DATA[$num_op]['id'] = $SESSION_VARS[$num_op]['id'];
	      $DATA[$num_op]['compte'] = $SESSION_VARS[$num_op]['num_cpte_comptable'];
	      $DATA[$num_op]['devise'] = $SESSION_VARS[$num_op]['devise'];
	      $DATA[$num_op]['cpte_interne_cli'] = $SESSION_VARS[$num_op]['id_compte'];
	      if ($SESSION_VARS[$num_op]['mntdeb']) {
	        $DATA[$num_op]['sens'] = 'd';
	        $DATA[$num_op]['montant'] = $SESSION_VARS[$num_op]['mntdeb'];
	      } else {
	        $DATA[$num_op]['sens'] = 'c';
	        $DATA[$num_op]['montant'] = $SESSION_VARS[$num_op]['mntcred'];
	      }
	      $DATA[$num_op]['date_comptable'] = $SESSION_VARS[$num_op]['date_comptable'];
	      $libel_ecriture = unserialize($SESSION_VARS[$num_op]['libel_ecriture']);
	      $DATA[$num_op]['libel_ecriture'] = $libel_ecriture;
	      $DATA[$num_op]['type_operation'] = $SESSION_VARS[$num_op]['type_operation'];
	      $DATA[$num_op]['id_jou'] = $SESSION_VARS[$num_op]['id_jou'];
	      $DATA[$num_op]['id_exo'] = $SESSION_VARS[$num_op]['id_exo'];
	      $DATA[$num_op]['id_taxe'] = $SESSION_VARS['id_taxe'];
	      $DATA[$num_op]['sens_taxe'] = $SESSION_VARS['sens_oper_tax'];
    	}
    }
  modifEcrituresBrouillard($DATA);

  //HTML
  $MyPage = new HTML_message(_("Confirmation Modification"));
  $MyPage->setMessage(_("L'entrée de la table des écritures a été modifiée avec succès !"));
  $MyPage->addButton(BUTTON_OK, "Ecr-1");
  $MyPage->buildHTML();
  echo $MyPage->HTML_code;

}
/*}}}*/

/*{{{ Opa-1 : Saisie dans un journal auxiliaire */
else if ($global_nom_ecran == "Opa-1") {
  global $global_profil_axs;
	$SESSION_VARS['envoi'] = 0;
  global $global_id_agence;
  $errorFound = false;
  if (isset($nbr_lignes_ecr))
    $SESSION_VARS["nbr_lignes_ecr"] = $nbr_lignes_ecr;
  if (!isset($SESSION_VARS["exercice"])) { //si on vient du premier ecran ( Ecr-1 )
    $SESSION_VARS["exercice"] = $exercice;
    $SESSION_VARS["journal"] = $journal;
    if ($date_ope!='')
      $SESSION_VARS["date_comptable"] = $date_ope;
    unset($SESSION_VARS["libel_ope"]);
    unset($SESSION_VARS["type_operation"]);
    unset($SESSION_VARS["id_taxe"]);
    unset($SESSION_VARS["taux_taxe"]);
    unset($SESSION_VARS["sens_oper_tax"]);

    //Récupération du journal
    $info=getInfosJournal($journal);
    $SESSION_VARS["cpte_princ_jou"]= $info[$journal]['num_cpte_princ'];
    $SESSION_VARS["libel_jou"]= serialize($info[$journal]['libel_jou']);

    // Récupération des sous-comptes du compte principaux du journal
    $SESSION_VARS["cptes_princ"]=array();
    $SESSION_VARS["cptes_princ"] = getSousComptes($SESSION_VARS["cpte_princ_jou"], true, NULL, NULL);

    // Elimination de tous les sous comptes sans devise associée
    foreach ($SESSION_VARS["cptes_princ"] as $key => $CPT) {
      if ($CPT["devise"] == "")
        unset($SESSION_VARS["cptes_princ"][$key]);
    }
    //on trie le tableau par numéro de compte comptable
 	  ksort($SESSION_VARS["cptes_princ"]);
    if (count($SESSION_VARS["cptes_princ"]) == 0) { // si le compte pricipal du journal n'a pas de sous-compte alors l'afficher
      $param["num_cpte_comptable"] = $SESSION_VARS["cpte_princ_jou"];
      $CPTS = getComptesComptables($param);
      if ($CPTS[$SESSION_VARS["cpte_princ_jou"]]["devise"] == NULL) {
        $errorFound = true;
        $html_err = new HTML_erreur(_("Echec saisie écriture"));
        $html_err->setMessage(_("Aucun compte principal n'a été trouvé qui possède une devise.")."<br/>"._("Créez d'abord un tel compte et réessayez ensuite"));
        $html_err->addButton("BUTTON_OK", 'Ecr-1');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
      } else
        $SESSION_VARS["cptes_princ"] = $CPTS;
    }

    // récupération des comptes de contrepartie du journal
    $SESSION_VARS["contrepart"]=array();
    $SESSION_VARS["contrepart"]=getComptesContrepartie($journal);
    ksort($SESSION_VARS["contrepart"]);
  } else {
    // Récupération des données déjà encodées
	    if (!isset($SESSION_VARS["libel_ope"]))
	    {
	      $SESSION_VARS["libel_ope"] = $libel_ope;
	      $SESSION_VARS["type_operation"] = $HTML_GEN_LSB_libel_ope_def;
	    }
      //$SESSION_VARS["id_taxe"] = $id_taxe;
      //$SESSION_VARS["taux_taxe"] = $taux_taxe;
      //$SESSION_VARS["sens_oper_tax"] = $sens_oper_tax;
    // récupération de la saisie : pour chaque ligne du tableau
    for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
      //if ($ {'cpte_comptable'.$i})
      {
          if ($ {'cpte_comptable'.$i}) {
            $SESSION_VARS[$i]['num_cpte_comptable'] = $ {'cpte_comptable'.$i};
          }
        if ($ {'id_compte'.$i})
        {
          $infoscompte=getAccountDatas($ {'id_compte'.$i});

          $CLI = getClientDatas($infoscompte["id_titulaire"]);
          if ($CLI["statut_juridique"] == 1) // Personne physique
            $nom_cli = STR_replace("'","",$CLI["pp_nom"])." ".STR_replace("'","",$CLI["pp_prenom"]);
          elseif($CLI["statut_juridique"] == 2) // Personne morale
          $nom_cli = STR_replace("'","",$CLI["pm_raison_sociale"]);
          elseif($CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) // Groupe informel ou solidaire
          $nom_cli = STR_replace("'","",$CLI["gi_nom"]);

          $SESSION_VARS[$i]['cpte_client'] = $infoscompte['num_complet_cpte']." ".$nom_cli ;
          $SESSION_VARS[$i]['id_compte'] = $ {'id_compte'.$i};
        }
        if ($SESSION_VARS[$i]['mntdeb'] != "") {
          
          if($SESSION_VARS["id_taxe"]>0) {
            $taxesInfos = getTaxesInfos();
            
            $SESSION_VARS[$i]['mntdeb'] = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',$SESSION_VARS[$i]['mntdeb'])) * (1 + $taxesInfos[$SESSION_VARS['id_taxe']]["taux"]));
          }else{
            $SESSION_VARS[$i]['mntdeb'] = recupMontant($SESSION_VARS[$i]['mntdeb']);
          }
          $SESSION_VARS[$i]['mntcred'] = '';
        }
        else if ($SESSION_VARS[$i]['mntcred'] != "") {
            
          if($SESSION_VARS["id_taxe"]>0) {
            $taxesInfos = getTaxesInfos();
            
            $SESSION_VARS[$i]['mntcred'] = (mb_ereg_replace(" ", "", preg_replace('/\s+/', '',$SESSION_VARS[$i]['mntcred'])) * (1 + $taxesInfos[$SESSION_VARS['id_taxe']]["taux"]));
          }else{
            $SESSION_VARS[$i]['mntcred'] = recupMontant($SESSION_VARS[$i]['mntcred']);
          }
          $SESSION_VARS[$i]['mntdeb'] = '';
        }
      }
    }
  }


  // Récupération des exercices comptables
  $exos=getExercicesComptables($SESSION_VARS["exercice"]);

  // Vérification de la validité de la date
  if (isset($exos) && isset($SESSION_VARS["date_comptable"])) {
    $dateope = php2pg($SESSION_VARS["date_comptable"]);
    $dateope=getPhpDateTimestamp($dateope);
    $datedeb=getPhpDateTimestamp($exos[0]['date_deb_exo']);
    $datefin=getPhpDateTimestamp($exos[0]['date_fin_exo']);
    if ( (date("y/m/d",$dateope) < date("y/m/d",$datedeb))
         || ( date("y/m/d",$dateope) > date("y/m/d",$datefin))
         || ( date("y/m/d",$dateope) > date("y/m/d"))) {
      $errorFound = true;
      $html_err = new HTML_erreur(_("Echec saisie écriture"));
      $html_err->setMessage(" "._("La date doit être comprise entre la date de début et de fin de l'exercice et antérieure ou égale à la date d'aujourd'hui !")." ");
      $html_err->addButton("BUTTON_OK", 'Ecr-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }

  if (!$errorFound) { // S'il n'y a pas eu d'erreur
    //Menu Saisie des opérations auxiliaires
    $MyPage = new HTML_GEN2(_("Ajout opérations dans un journal auxiliaire"));

    // L'exercice choisi
    $MyPage->addField("exercice",_("Exercice"), TYPC_TXT);
    $MyPage->setFieldProperties("exercice", FIELDP_DEFAULT, $SESSION_VARS["exercice"]);
    $MyPage->setFieldProperties("exercice", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("exercice", FIELDP_IS_LABEL, true);

    // Le libellé du journal choisi
    $MyPage->addField("libel_jou",_("Journal"), TYPC_TTR);
    $libel_jou = new Trad($info[$journal]["libel_jou"]);
    $MyPage->setFieldProperties("libel_jou", FIELDP_DEFAULT,  $libel_jou);
    $MyPage->setFieldProperties("libel_jou", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("libel_jou", FIELDP_IS_LABEL, true);

    // La date de valeur saisie
    $MyPage->addField("date_ope",_("Date opération"), TYPC_DTE);
    $MyPage->setFieldProperties("date_ope", FIELDP_IS_REQUIRED, false);
    $MyPage->setFieldProperties("date_ope", FIELDP_DEFAULT,  $SESSION_VARS["date_comptable"]);
    $MyPage->setFieldProperties("date_ope", FIELDP_IS_LABEL, true);
    
    // le libellé de l'écriture
   	$choices=array();
  	$list_libel = getLEL(); // Récupère de tous les libellés des écritures libres
  	$choices[0]=_("Autre libellé");
  	foreach ($list_libel as $key => $value){
  		$libel_ope = new Trad($value["libel_ope"]);
  		$choices[$value["type_operation"]] = $libel_ope->traduction();
  	}
        
   	$MyPage->addField("libel_ope_def",_("Liste libellé"), TYPC_LSB);
  	$MyPage->setFieldProperties("libel_ope_def", FIELDP_ADD_CHOICES, $choices);
  	$MyPage->setFieldProperties("libel_ope_def", FIELDP_HAS_CHOICE_AUCUN, false);
  	$MyPage->setFieldProperties("libel_ope_def", FIELDP_DEFAULT, $SESSION_VARS["type_operation"]);
  	$MyPage->setFieldProperties("libel_ope_def", FIELDP_JS_EVENT, array("onChange"=>"changeLibel();"));
  	      
    $MyPage->addField("libel_ope",_("Libellé opération"), TYPC_TTR);
    
    $SESSION_VARS["libel_ope"] = unserialize($SESSION_VARS["libel_ope"]);
    if(!is_trad($SESSION_VARS["libel_ope"])){
    	$SESSION_VARS["libel_ope"] = new Trad($SESSION_VARS["libel_ope"]);
    }
    if(isset($SESSION_VARS["libel_ope"])) {
    	$MyPage->setFieldProperties("libel_ope", FIELDP_DEFAULT, $SESSION_VARS["libel_ope"]);
    }
    $MyPage->setFieldProperties("libel_ope", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("libel_ope", FIELDP_WIDTH, 40);
		
    $codejs ="\n\nfunction changeLibel() {";
    $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==0)\n\t";
		$codejs .= "{\n\t\tdocument.ADForm.libel_ope.value ='';";
		//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =false;";
		$codejs .= "}else{\n";
    foreach($choices as $type_operation=>$value) {
	  	$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==$type_operation)\n\t";
			$codejs .= "{\n\t\tdocument.ADForm.libel_ope.value =\"" . $value . "\";";
			//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =true;";
			$codejs .= "}\n";
    }
    $codejs .= "}}\n";
    $MyPage->addJS(JSP_FORM, "jslibel", $codejs);
    

    // TVA à appliquer
    $MyPage->addField("id_taxe",_("TVA à appliquer"), TYPC_LSB);
    $liste_taxe = getListeTaxes();
    $MyPage->setFieldProperties("id_taxe", FIELDP_ADD_CHOICES, $liste_taxe);
    $MyPage->setFieldProperties("id_taxe", FIELDP_DEFAULT, $SESSION_VARS["id_taxe"]);
    $MyPage->setFieldProperties("id_taxe", FIELDP_JS_EVENT, array("OnChange"=>"updateTaxe();"));
    $MyPage->setFieldProperties("id_taxe", FIELDP_IS_REQUIRED, false);
    $MyPage->addField("taux_taxe", _("Taux de la taxe"), TYPC_PRC);
    $MyPage->setFieldProperties("taux_taxe", FIELDP_DEFAULT, $SESSION_VARS["taux_taxe"]);
   	$MyPage->setFieldProperties("taux_taxe", FIELDP_IS_LABEL,true);
   	// Sens de l'opération
	  $sens=array("d"=>_("Paiement de tva déductible"),"c"=>_("Perception de tva collectée"));
	  $MyPage->addField("sens_oper_tax",_("Sens de la taxe"), TYPC_LSB);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_ADD_CHOICES, $sens);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_DEFAULT, $SESSION_VARS["sens_oper_tax"]);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_HAS_CHOICE_AUCUN, true);
   	$taxesInfos = getTaxesInfos();
   	$js = "function updateTaxe()\n{\n ";
   	foreach($taxesInfos as $key=>$value) {
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value == ".$value["id"].")) {";
     $js .= "document.ADForm.taux_taxe.value = '".$value["taux"]*(100)."';";
     $js .= "document.ADForm.HTML_GEN_LSB_sens_oper_tax.disabled = false;";
     $js .= "}";
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value == '0')){";
     $js .= "document.ADForm.taux_taxe.value = '';";
     $js .= "document.ADForm.HTML_GEN_LSB_sens_oper_tax.disabled = true;";
     $js .= "}";
   	}
	 	$js .= "};";
	 	$js .= "updateTaxe();";
	 	$MyPage->addJS(JSP_FORM, "jstest", $js);
		// Checkform
  	$jscheck = "if (document.ADForm.HTML_GEN_LSB_id_taxe.value != 0)
           {
             if (document.ADForm.HTML_GEN_LSB_sens_oper_tax.value == 0)
           {
             msg += '- "._("Le champ Sens de la taxe doit être renseigné")."\\n';
             ADFormValid = false;
           }
           }";

  	$MyPage->addJS(JSP_BEGIN_CHECK, "JS2", $jscheck);

    $MyPage->addHiddenType("bouton_clique");
    $MyPage->addHiddenType("nbr_lignes_ecr", $SESSION_VARS["nbr_lignes_ecr"]);

    //tableau pour le compte principal
    $html .= "<br>";
    $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
    //En-tête du tableau principal
    $html .= "<TR bgcolor=$colb_tableau>";
    $html.="<TD align=\"center\"><b>"._("Comptes principaux")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Client")."</TD>";
    $html.="<TD align=\"center\"><b>"._("Débit")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Crédit")."</b></TD>";
    $html.="</TR>\n";

    $i=1;
    $html .= "<TR bgcolor=$color>\n";

    // Compte principal du journal et ses sous-comptes qui ne sont pas centralisateurs
    $html .= "<TD>\n";
    $html .= "<select NAME=\"cpte_comptable$i\" style=\"width:250px\" onchange=\"document.ADForm.cpte_client$i.value ='';document.ADForm.id_compte$i.value ='';\">\n";
    // option aucun
    $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";

    // affiche le compte principal s'il n'a pas de sous-comptes sinon afficher les sous-comptes qui ne sont pas centralisateurs
    if (isset($SESSION_VARS["cptes_princ"]))
      foreach($SESSION_VARS["cptes_princ"] as $key=>$value)
      if (!isCentralisateur($value["num_cpte_comptable"])) { // afficher que les comptes non centralisateurs
        $num_compte = $value["num_cpte_comptable"];
        $libel_compte = $value["libel_cpte_comptable"];
        if ($SESSION_VARS[$i]['num_cpte_comptable'] == $num_compte)
          $html .= "<option value=$num_compte selected>".$num_compte." ".$libel_compte."</option>\n";
        else
          $html .= "<option value=$num_compte >".$num_compte." ".$libel_compte."</option>\n";
      }

    $html .= "</select>\n";
    $html .= "</TD>";

    //Comptes du client
    $html .= "<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=32 ";
    $html.= "value=\"".$SESSION_VARS[$i]['cpte_client']."\" disabled=true>\n";
    $html .="<FONT size=\"2\"><A href=# ";
    $html.="onclick=\"getCompte(document.ADForm.cpte_comptable$i.value,'cpte_client$i','id_compte$i');\">"._("Recherche")."</A></FONT></TD>\n";
    $html.="<INPUT TYPE=\"hidden\" NAME=\"id_compte$i\" value=\"".$SESSION_VARS[$i]['id_compte']."\">\n";

    //Montant débit
    $html .= "<TD><INPUT NAME=\"mntdeb$i\" TYPE=\"text\" ";
    $html.="onchange=\"value = formateMontant(value);if($i==1) checkDebit();checkDebit2();\" ";
    $html.="size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntdeb'],false)."\"></TD>\n";

    //Montant crédit
    $html .="<TD><INPUT NAME=\"mntcred$i\" TYPE=\"text\" ";
    $html.="onchange=\"value = formateMontant(value);if($i==1) checkCredit();checkCredit2();\" ";
    $html.="size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntcred'],false)."\"></TD>\n";
    $html .= "</TR>\n";
    $html .= "</TABLE>";

    $i++;

    //tableau contrepartie
    $html .= "<br><TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    $html .= "<TR bgcolor=$colb_tableau>";
    $html.="<TD align=\"center\"><b>"._("Comptes contre partie")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Client")."</TD>";
    $html.="<TD align=\"center\"><b>"._("Débit")."</b></TD>";
    $html.="<TD align=\"center\"><b>"._("Crédit")."</b></TD>";
    $html.="</TR>\n";

    for ($i=2; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
      //On alterne la couleur de fond
      if ($i%2)
        $color = $colb_tableau;
      else
        $color = $colb_tableau_altern;

      $html .= "<TR bgcolor=$color>\n";

      //Comptes comptables de la contrepartie qui ne sont pas centralisateurs
      $html .= "<TD>\n";
      $html .= "<select NAME=\"cpte_comptable$i\" style=\"width:250px\"";
      $html .= "onchange=\"document.ADForm.cpte_client$i.value ='';document.ADForm.id_compte$i.value ='';\">\n";
      $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
      if (isset($SESSION_VARS["contrepart"]))
        foreach($SESSION_VARS["contrepart"] as $key=>$value )
        if (!isCentralisateur($key))
          if ($SESSION_VARS[$i]['num_cpte_comptable'] == $key)
            $html .= "<option value=$key selected>".$key." ".$value["libel_cpte_comptable"]."</option>\n";
          else
            $html .= "<option value=$key>".$key." ".$value["libel_cpte_comptable"]."</option>\n";

      $html .= "</select>\n";
      $html .= "</TD>";

      //Comptes du client
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=32 ";
      $html.= "value=\"".$SESSION_VARS[$i]['cpte_client']."\" disabled=true>\n";
      $html .="<FONT size=\"2\"><A href=# ";
      $html.="onclick=\"getCompte(document.ADForm.cpte_comptable$i.value,'cpte_client$i','id_compte$i');\">"._("Recherche")."</A></FONT></TD>\n";
      $html.="<INPUT TYPE=\"hidden\" NAME=\"id_compte$i\" value=\"".$SESSION_VARS[$i]['id_compte']."\">\n";
      //Montant débit
      $html .= "<TD><INPUT NAME=\"mntdeb$i\" TYPE=\"text\" ";
      $html.="onchange=\"value = formateMontant(value);if($i==1) checkDebit();checkDebit2();\" ";
      $html.="size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntdeb'],false)."\"></TD>\n";

      //Montant crédit
      $html .="<TD><INPUT NAME=\"mntcred$i\" TYPE=\"text\" ";
      $html.="onchange=\"value = formateMontant(value);if($i==1) checkCredit();checkCredit2();\" ";
      $html.="size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntcred'],false)."\"></TD>\n";
      $html .= "</TR>\n";

    }
    $html .= "</TABLE>\n";

    if (in_array(470,$global_profil_axs)){ //AT-125 : boutons acessible pour le fonction 470 seulement (saisie ecritures libres)
      //Bouton Ajout ligne
      $MyPage->addFormButton(1, 1, "ajout", _("Ajouter lignes"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("ajout", BUTP_AXS, 470);
      $MyPage->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, "Opa-1");
      $MyPage->setFormButtonProperties("ajout", BUTP_CHECK_FORM, false);
      $js = "document.ADForm.nbr_lignes_ecr.value = ".$SESSION_VARS['nbr_lignes_ecr']."+1;\n";
      $MyPage->setFormButtonProperties("ajout", BUTP_JS_EVENT, array("onclick" => $js));

      //Bouton Enregistrer
      $MyPage->addFormButton(1, 2, "enregistrer", _("Enregistrer"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("enregistrer", BUTP_AXS, 470);
      $MyPage->setFormButtonProperties("enregistrer", BUTP_PROCHAIN_ECRAN, "Opa-2");
      $MyPage->setFormButtonProperties("enregistrer", BUTP_CHECK_FORM, true);
      $MyPage->setFormButtonProperties("enregistrer", BUTP_JS_EVENT, array("onclick" => "document.ADForm.bouton_clique.value='enregistrer';"));
    }

    //Bouton Valider
    $MyPage->addFormButton(1, 3, "valider", _("Valider"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valider", BUTP_AXS, 471);
    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Opa-2");
    $MyPage->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
    $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.bouton_clique.value='valider';"));

    //Bouton Annuler
    $MyPage->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    //$MyPage->setFormButtonProperties("annuler", BUTP_AXS, 470);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Ecr-1");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM,false);

    //JavaScript
    $html .= "<script type=\"text/javascript\">\n";

    //function  getCompte : ouvre une fenêtre de recherche de compte de client
    $html .= "function getCompte(cpte_comptable,cpte_client,id_compte)\n{\n";
    $html.= "var ch;\n";
    $html.= "ch='../modules/compta/rech_compte_client.php?m_agc=".$_REQUEST['m_agc']."&field_name='+cpte_client+'&id_compte='+id_compte+'&field_cpte_comptable='+cpte_comptable;\n";
    $html .= "OpenBrw(ch, '"._("Recherche")."');\n";
    $html .= "}\n";

    // Fonction check debit grise les champs de débit
    $html .="function checkDebit()\n{\n";
    $html.="if(document.ADForm.mntdeb1.value!='')\n{\n";
    for ($i=2; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
      $html .="document.ADForm.mntcred$i.disabled=false;\n";
      $html .="document.ADForm.mntdeb$i.value='';\n";
      $html .="document.ADForm.mntdeb$i.disabled=true;\n";
    }
    $html.="}\n";
    $html.="\n\tdocument.ADForm.mntcred1.value = '';\n}\n";

    // Fonction check crédit grise les champs de crédit
    $html .="function checkCredit()\n{\n";
    $html.="if(document.ADForm.mntcred1.value!='')\n{\n";
    for ($i=2; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
      $html .="document.ADForm.mntdeb$i.disabled=false;\n";
      $html .="document.ADForm.mntcred$i.value='';\n";
      $html .="document.ADForm.mntcred$i.disabled=true;\n";
    }
    $html.="}\n";
    $html.="\n\tdocument.ADForm.mntdeb1.value = '';\n}\n";

    // Fonction check crédit gen
    $html .="function checkCredit2()\n{\n";
    for ($i=1; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
      $html.="if(document.ADForm.mntcred$i.value)\n";
      $html.="\n\tdocument.ADForm.mntdeb$i.value = '';\n";
    }
    $html.="}\n";

    // Fonction check débit  gen
    $html .="function checkDebit2()\n{\n";
    for ($i=1; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
      $html.="if(document.ADForm.mntdeb$i.value)\n";
      $html.="\n\tdocument.ADForm.mntcred$i.value = '';\n";
    }
    $html.="}\n";
    $html .= "</script>\n";

    $MyPage->addHTMLExtraCode("html",$html);
    $MyPage->addJS(JSP_FORM, "js", $js);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }

}
/*}}}*/

/*{{{ Opa-6 : Modification opérations auxiliaire */
else if ($global_nom_ecran=="Opa-6") {
	$SESSION_VARS['envoi'] = 0;
  global $global_id_agence;
  // Récupération des sous-comptes du compte principaux du journal
  $SESSION_VARS["cptes_princ"] = array();
  $SESSION_VARS["cptes_princ"] = getSousComptes($SESSION_VARS["cpte_princ_jou"], true, NULL, NULL);

  if (count($SESSION_VARS["cptes_princ"])==0) { // si le compte pricipal du journal n'a pas de sous-compte alors l'affiché
    $param["num_cpte_comptable"] = $SESSION_VARS["cpte_princ_jou"];
    $SESSION_VARS["cptes_princ"]=getComptesComptables($param);
  }

  // répération des comptes de contrepartie du journal
  $SESSION_VARS["contrepart"]=array();
  $SESSION_VARS["contrepart"]=getComptesContrepartie($SESSION_VARS["journal"]);

  if (!isset($SESSION_VARS["libel_ope"])) { // On vient de Opa-5 et non de Opa-7
    $SESSION_VARS["libel_ope"] = new Trad($libel_ope);
    $SESSION_VARS["type_operation"]=$type_operation;
    $SESSION_VARS["ecr"]["id_his"]=$id_his;
    if (!isset($SESSION_VARS["date_comptable"]))
      $SESSION_VARS["date_comptable"]=pg2phpDate($date_ope);

    $row=getInfosEcritures();
    $i=1;
    $total_debit=0;
    $total_credit=0;
    
    $taxesInfos = getTaxesInfos();

    // Recup infos
    if (isset($row))
      foreach($row as $infos)
      if ($infos["id_his"] == $SESSION_VARS["ecr"]["id_his"] )
      {
        $SESSION_VARS[$i]['id_mouvement']=$infos['id_mouvement'];
        $SESSION_VARS[$i]['id_his']=$infos['id_his'];
        $SESSION_VARS[$i]['id']=$infos['id'];
        $SESSION_VARS[$i]['id_jou']=$infos['id_jou'];
        $SESSION_VARS[$i]['id_exo']=$infos['id_exo'];
        $SESSION_VARS[$i]['libel_ecriture']=$infos['libel_ecriture'];
        $SESSION_VARS[$i]['type_operation']=$infos['type_operation'];
        $SESSION_VARS[$i]['date_comptable']=$infos['date_comptable'];
        $SESSION_VARS[$i]['num_cpte_comptable']=$infos['compte'];
        $SESSION_VARS[$i]['libel_cpte_comptable'] = $infos['compte']." ".$SESSION_VARS["comptes"][$infos['compte']];
        $SESSION_VARS[$i]['devise'] = $infos['devise'];
        
        $SESSION_VARS["id_taxe"] = $infos['id_taxe'];
        $SESSION_VARS["taux_taxe"] = $taxesInfos[$infos['id_taxe']]["taux"]*100;
        $SESSION_VARS["sens_oper_tax"] = $infos['sens_taxe'];

        if (!isset($SESSION_VARS["devise"]))
          $SESSION_VARS["devise"] = $infos['devise']; // la devise est la même pour toute l'écriture

        if ($infos['cpte_interne_cli']) {
          $infoscompte=getAccountDatas($infos['cpte_interne_cli']);

          $CLI = getClientDatas($infoscompte["id_titulaire"]);
          if ($CLI["statut_juridique"] == 1) // Personne physique
            $nom_cli = STR_replace("'","",$CLI["pp_nom"])." ".STR_replace("'","",$CLI["pp_prenom"]);
          elseif($CLI["statut_juridique"] == 2) // Personne morale
          $nom_cli = STR_replace("'","",$CLI["pm_raison_sociale"]);
          elseif($CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) // Groupe informel ou solidaire
          $nom_cli = STR_replace("'","",$CLI["gi_nom"]);

          $SESSION_VARS[$i]['cpte_client'] = $infoscompte['num_complet_cpte']." ".$nom_cli;
        }
        $SESSION_VARS[$i]['id_compte']=$infos['cpte_interne_cli'];
        $SESSION_VARS[$i]['sens']=$infos['sens'];
        
        if($infos['id_taxe']>0) {
          $taxesInfos = getTaxesInfos();
          $infos['montant'] = ($infos['montant'] * (1 + $taxesInfos[$infos['id_taxe']]["taux"]));
        }

        if ($infos['sens']=='d') {
          $total_debit += recupMontant($infos['montant']);
          $SESSION_VARS[$i]['mntdeb']=recupMontant($infos['montant']);
          $SESSION_VARS[$i]['mntcred']='';
        } else {
          $total_credit += recupMontant($infos['montant']);
          $SESSION_VARS[$i]['mntdeb']='';
          $SESSION_VARS[$i]['mntcred']=recupMontant($infos['montant']);
        }
        $i++;
      }
    $SESSION_VARS["totaldeb"]=$total_debit;
    $SESSION_VARS["totalcred"]=$total_credit;
    $SESSION_VARS["nbr_lignes_ecr"] = $i;
  } else if (isset($SESSION_VARS["libel_ope"])) { /* On vient de Opa-6 */
    
    $SESSION_VARS["libel_ope"] = unserialize($SESSION_VARS["libel_ope"]);
    if(!is_trad($SESSION_VARS["libel_ope"])){
        $libel_ope = new Trad($SESSION_VARS["libel_ope"]);
        //$SESSION_VARS["libel_ope"] = $libel_ope->get_id_str();
    }
    
    $total_debit=0;
    $total_credit=0;

    // récupération de la saisie : pour chaque ligne du tableau
    for ($i=1;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) {
      //if ($ {'cpte_comptable'.$i})
      {
          if ($ {'cpte_comptable'.$i}) {
            $SESSION_VARS[$i]['num_cpte_comptable'] = $ {'cpte_comptable'.$i};
          }
        if ($ {'id_compte'.$i})
        {
          $infoscompte=getAccountDatas($ {'id_compte'.$i});

          $CLI = getClientDatas($infoscompte["id_titulaire"]);
          if ($CLI["statut_juridique"] == 1) // Personne physique
            $nom_cli = STR_replace("'","",$CLI["pp_nom"])." ".STR_replace("'","",$CLI["pp_prenom"]);
          elseif($CLI["statut_juridique"] == 2) // Personne morale
          $nom_cli = STR_replace("'","",$CLI["pm_raison_sociale"]);
          elseif($CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) // Groupe informel ou solidaire
          $nom_cli = STR_replace("'","",$CLI["gi_nom"]);

          $SESSION_VARS[$i]['cpte_client']=$infoscompte['num_complet_cpte']." ".$nom_cli;
          $SESSION_VARS[$i]['id_compte'] = $ {'id_compte'.$i};
        }

        if ($SESSION_VARS[$i]['mntdeb'] != "") {
          
          if($SESSION_VARS[$i]['mntdeb']>0) {
            $taxesInfos = getTaxesInfos();
            $infos_mntdeb = ($SESSION_VARS[$i]['mntdeb'] * (1 + $taxesInfos[$SESSION_VARS["id_taxe"]]["taux"]));

            $total_debit += ($infos_mntdeb);
            $SESSION_VARS[$i]['mntdeb'] = ($infos_mntdeb);
          }else{
            $total_debit += recupMontant($SESSION_VARS[$i]['mntdeb']);
            $SESSION_VARS[$i]['mntdeb'] = recupMontant($SESSION_VARS[$i]['mntdeb']);  
          }

          $SESSION_VARS[$i]['mntcred'] = '';
        }
        else if ($SESSION_VARS[$i]['mntcred'] != "") {
            
          if($SESSION_VARS["id_taxe"]>0) {
            $taxesInfos = getTaxesInfos();
            $infos_mntcred = ($SESSION_VARS[$i]['mntcred'] * (1 + $taxesInfos[$SESSION_VARS["id_taxe"]]["taux"]));
            
            $total_credit += ($infos_mntcred);
            $SESSION_VARS[$i]['mntcred'] = ($infos_mntcred);
          }else{
            $total_credit += recupMontant($SESSION_VARS[$i]['mntcred']);
            $SESSION_VARS[$i]['mntcred'] = recupMontant($SESSION_VARS[$i]['mntcred']);
          }

          $SESSION_VARS[$i]['mntdeb'] = '';
        }
      }
      
      $SESSION_VARS["totaldeb"]=$total_debit;
      $SESSION_VARS["totalcred"]=$total_credit;
    }
  } else{
  	$libel_ope = unserialize($SESSION_VARS["libel_ope"]);
        $SESSION_VARS["libel_ope"] = $libel_ope->get_id_str();
  }
  if (isset($nbr_lignes_ecr))
    $SESSION_VARS["nbr_lignes_ecr"] = $nbr_lignes_ecr;

  $MyPage = new HTML_GEN2(_("Modification opérations"));

  $MyPage->addField("exercice",_("Exercice"), TYPC_TXT);
  $MyPage->setFieldProperties("exercice", FIELDP_DEFAULT, $SESSION_VARS["exercice"]);
  $MyPage->setFieldProperties("exercice", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("exercice", FIELDP_IS_LABEL, true);

  // libellé du journal
  $MyPage->addField("libel_jou",_("Journal"), TYPC_TTR);
   if(!is_trad(unserialize($SESSION_VARS["libel_jou"]))){
    	$libel_jou = new Trad(unserialize($SESSION_VARS["libel_jou"]));	
    }else{
    	$libel_jou = unserialize($SESSION_VARS["libel_jou"]);
   }
  $MyPage->setFieldProperties("libel_jou", FIELDP_DEFAULT, $libel_jou);
  $MyPage->setFieldProperties("libel_jou", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("libel_jou", FIELDP_IS_LABEL, true);

  // La date de valeur
  $MyPage->addField("date_ope",_("Date opération"), TYPC_DTE);
  $MyPage->setFieldProperties("date_ope", FIELDP_DEFAULT, $SESSION_VARS["date_comptable"]);
  $MyPage->setFieldProperties("date_ope", FIELDP_IS_REQUIRED, false);
  
  // le libellé de l'écriture
  $choices=array();
  $list_libel = getLEL(); // Récupère de tous les libellés des écritures libres
  $choices[0]=_("Autre libellé");
  foreach ($list_libel as $key => $value) {
        $libel_ope = new Trad($value["libel_ope"]);
        $choices[$value["type_operation"]] = $libel_ope->traduction();
  }

  $MyPage->addField("libel_ope_def",_("Liste libellé"), TYPC_LSB);
  $MyPage->setFieldProperties("libel_ope_def", FIELDP_ADD_CHOICES, $choices);
  $MyPage->setFieldProperties("libel_ope_def", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("libel_ope_def", FIELDP_DEFAULT, $SESSION_VARS["type_operation"]);
  $MyPage->setFieldProperties("libel_ope_def", FIELDP_JS_EVENT, array("onChange"=>"changeLibel();"));
  
  $MyPage->addField("libel_ope",_("Libellé opération"), TYPC_TTR);
  //$libel_ope = new Trad($SESSION_VARS["libel_ope"]);
  $libel_ope = $SESSION_VARS["libel_ope"];
  $MyPage->setFieldProperties("libel_ope", FIELDP_DEFAULT, $libel_ope);
  $MyPage->setFieldProperties("libel_ope", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("libel_ope", FIELDP_WIDTH, 40);
  
  $codejs ="\n\nfunction changeLibel() {";
    $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==0)\n\t";
		$codejs .= "{\n\t\tdocument.ADForm.libel_ope.value ='';";
		//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =false;";
		$codejs .= "}else{\n";
    foreach($choices as $type_operation=>$value) {
	  	$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==$type_operation)\n\t";
			$codejs .= "{\n\t\tdocument.ADForm.libel_ope.value =\"" . $value . "\";";
			//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =true;";
			$codejs .= "}\n";
    }
    $codejs .= "}}\n";
    $MyPage->addJS(JSP_FORM, "jslibel", $codejs);
    

  // TVA à appliquer
    $MyPage->addField("id_taxe",_("TVA à appliquer"), TYPC_LSB);
    $liste_taxe = getListeTaxes();
    $MyPage->setFieldProperties("id_taxe", FIELDP_ADD_CHOICES, $liste_taxe);
    $MyPage->setFieldProperties("id_taxe", FIELDP_DEFAULT, $SESSION_VARS["id_taxe"]);
    $MyPage->setFieldProperties("id_taxe", FIELDP_JS_EVENT, array("OnChange"=>"updateTaxe();"));
    $MyPage->setFieldProperties("id_taxe", FIELDP_IS_REQUIRED, false);
    $MyPage->addField("taux_taxe", _("Taux de la taxe"), TYPC_PRC);
    $MyPage->setFieldProperties("taux_taxe", FIELDP_DEFAULT, $SESSION_VARS["taux_taxe"]);
   	$MyPage->setFieldProperties("taux_taxe", FIELDP_IS_LABEL,true);
   	// Sens de l'opération
	  $sens=array("d"=>_("Paiement de tva déductible"),"c"=>_("Perception de tva collectée"));
	  $MyPage->addField("sens_oper_tax",_("Sens de la taxe"), TYPC_LSB);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_ADD_CHOICES, $sens);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_DEFAULT, $SESSION_VARS["sens_oper_tax"]);
	  $MyPage->setFieldProperties("sens_oper_tax", FIELDP_HAS_CHOICE_AUCUN, true);
   	$taxesInfos = getTaxesInfos();
   	$js = "function updateTaxe()\n{\n ";
   	foreach($taxesInfos as $key=>$value) {
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value == ".$value["id"].")) {";
     $js .= "document.ADForm.taux_taxe.value = '".$value["taux"]*(100)."';";
     $js .= "document.ADForm.HTML_GEN_LSB_sens_oper_tax.disabled = false;";
     $js .= "}";
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value == '0')){";
     $js .= "document.ADForm.taux_taxe.value = '';";
     $js .= "document.ADForm.HTML_GEN_LSB_sens_oper_tax.disabled = true;";
     $js .= "}";
   	}
	 	$js .= "};";
	 	$js .= "updateTaxe();";
	 	$MyPage->addJS(JSP_FORM, "jstest", $js);
		// Checkform
  	$jscheck = "if (document.ADForm.HTML_GEN_LSB_id_taxe.value != 0)
           {
             if (document.ADForm.HTML_GEN_LSB_sens_oper_tax.value == 0)
           {
             msg += '- "._("Le champ Sens de la taxe doit être renseigné")."\\n';
             ADFormValid = false;
           }
           }";

  	$MyPage->addJS(JSP_BEGIN_CHECK, "JS2", $jscheck);


  $MyPage->addHiddenType("bouton_clique");
  $MyPage->addHiddenType("nbr_lignes_ecr", $SESSION_VARS["nbr_lignes_ecr"]);

  // tableau des comptes rincipaux
  $html = "<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $html .="<TR bgcolor=$colb_tableau>";
  $html .="<TD align=\"center\"><b>"._("Compte comptable")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Compte client")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Débit")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Crédit")."</b></TD>";
  $html .="</TR>\n";
  $i=1;
  $color = $colb_tableau_altern;

  // Ligne du compte principal du journal
  $html .= "<TR bgcolor=$color>\n";

  // le compte principal s'il n'a pas de sous-comptes sinon les sous-comptes qui ne sont pas centralisateurs
  $html .= "<TD><select NAME=\"cpte_comptable$i\" style=\"width:250px\" ";
  $html .= "onchange=\"document.ADForm.cpte_client$i.value ='';document.ADForm.id_compte$i.value ='';\">\n";
  $html .= "<option value=\"0\">[Aucun]</option>\n";
  if (isset($SESSION_VARS["cptes_princ"]))
    foreach($SESSION_VARS["cptes_princ"] as $key=>$value)
    if (!isCentralisateur($value["num_cpte_comptable"])) { // afficher que les comptes non centralisateurs
      $num_compte = $value["num_cpte_comptable"];
      $libel_compte = $value["libel_cpte_comptable"];
      if ($SESSION_VARS[$i]['num_cpte_comptable'] == $num_compte)
        $html .= "<option value=$num_compte selected>".$num_compte." ".$libel_compte."</option>\n";
      else
        $html .= "<option value=$num_compte >".$num_compte." ".$libel_compte."</option>\n";
    }
  $html .= "</select></TD>\n";

  //Compte du client
  $html.="<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=32 value=\"".$SESSION_VARS[$i]['cpte_client']."\" disabled=true>\n";
  $html .="<FONT size=\"2\"><A href=# onclick=\"getCompte(document.ADForm.cpte_comptable$i.value,'cpte_client$i','id_compte$i');return false;\">"._("Recherche")."</A></FONT></TD>\n";

  $html.="<INPUT TYPE=\"hidden\" NAME=\"id_compte$i\" value=\"".$SESSION_VARS[$i]['id_compte']."\">\n";
  // Débit
  setMonnaieCourante($SESSION_VARS[$i]['devise']);
  $html .= "<TD><INPUT NAME=\"mntdeb$i\" TYPE=\"text\" size=12 value=\"".AfficheMontant($SESSION_VARS[$i]['mntdeb'],false)."\" onchange=\"checkDebit();checkDebit2();CalculTotaux(); value = formateMontant(value);\"></TD>\n";

  //Crédit
  $html .="<TD><INPUT NAME=\"mntcred$i\" TYPE=\"text\" size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntcred'],false)."\" onchange=\"checkCredit();checkCredit2;CalculTotaux(); value = formateMontant(value); \"></TD>\n";

  // id_mouvement
  $html .="<INPUT TYPE=\"hidden\" NAME=\"id_mouvement$i\" size=14 value=\"".$SESSION_VARS[$i]['id_mouvement']."\">\n";
  // id_his
  $html .="<INPUT TYPE=\"hidden\" NAME=\"id_his$i\" value=\"".$SESSION_VARS[$i]['id_his']."\">\n";
  // id_operation
  $html .="<INPUT TYPE=\"hidden\" NAME=\"id$i\" value=\"".$SESSION_VARS[$i]['id']."\">\n";
  $html .= "</TR>\n";
  $html.="</TABLE>";

  // taleau des comptes de contrepartie
  $html .= "<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $html .="<TR bgcolor=$colb_tableau>";
  $html .="<TD align=\"center\"><b>"._("Compte comptable")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Compte client")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Débit")."</b></TD>";
  $html .="<TD align=\"center\"><b>"._("Crédit")."</b></TD>";
  $html .="</TR>\n";

  for ($i=2;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++) { // la ligne 1 est reservée à un compte principal
  	if(!$SESSION_VARS[$i]['auto']){
    //On alterne la couleur de fond du tableau
    if ($i%2)
      $color = $colb_tableau;
    else
      $color = $colb_tableau_altern;

    // Affichage des écritures
    $html .= "<TR bgcolor=$color>\n";

    // Comptes de contrepartie du journal qui ne sont pas centralisateurs
    $html .= "<TD><select NAME=\"cpte_comptable$i\" style=\"width:250px\" ";
    $html .= "onchange=\"document.ADForm.cpte_client$i.value ='';document.ADForm.id_compte$i.value ='';\">\n";
    $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
    if (isset($SESSION_VARS["contrepart"]))
      foreach($SESSION_VARS["contrepart"] as $key=>$value )
      if (!isCentralisateur($key))
        if ($SESSION_VARS[$i]['num_cpte_comptable'] == $key)
          $html .= "<option value=$key selected>".$key." ".$value["libel_cpte_comptable"]."</option>\n";
        else
          $html .= "<option value=$key>".$key." ".$value["libel_cpte_comptable"]."</option>\n";
    $html .= "</select></TD>\n";

    //Compte du client
    $html.="<TD><INPUT TYPE=\"text\" NAME=\"cpte_client$i\" size=32 value=\"".$SESSION_VARS[$i]['cpte_client']."\" disabled=true>\n";
    $html .="<FONT size=\"2\"><A href=# onclick=\"getCompte(document.ADForm.cpte_comptable$i.value,'cpte_client$i','id_compte$i');return false;\">"._("Recherche")."</A></FONT></TD>\n";

    $html.="<INPUT TYPE=\"hidden\" NAME=\"id_compte$i\" value=\"".$SESSION_VARS[$i]['id_compte']."\">\n";
    // Débit
    setMonnaieCourante($SESSION_VARS[$i]['devise']);
    $html .= "<TD><INPUT NAME=\"mntdeb$i\" TYPE=\"text\" size=12 value=\"".AfficheMontant($SESSION_VARS[$i]['mntdeb'],false)."\" onchange=\"checkDebit2();CalculTotaux(); value = formateMontant(value);\"></TD>\n";

    //Crédit
    $html .="<TD><INPUT NAME=\"mntcred$i\" TYPE=\"text\" size=12 value=\"".afficheMontant($SESSION_VARS[$i]['mntcred'],false)."\" onchange=\"checkCredit2();CalculTotaux(); value = formateMontant(value); \"></TD>\n";

    // id_mouvement
    $html .="<INPUT TYPE=\"hidden\" NAME=\"id_mouvement$i\" size=14 value=\"".$SESSION_VARS[$i]['id_mouvement']."\">\n";

    // id_his
    $html .="<INPUT TYPE=\"hidden\" NAME=\"id_his$i\" value=\"".$SESSION_VARS[$i]['id_his']."\">\n";

    // id_operation
    $html .="<INPUT TYPE=\"hidden\" NAME=\"id$i\" value=\"".$SESSION_VARS[$i]['id']."\">\n";

    $html .= "</TR>\n";
  	}
  }

  //Totaux
  $html .= "<TR bgcolor=$colb_tableau><TD colspan=6 align=\"center\">\n";
  $html .= "<TABLE align=\"right\">";
  $html .= "<TR>";
  $html .= "<TD>"._("Totaux").":<INPUT TYPE=\"text\" NAME=\"tot_debit\" size=12 disabled=true VALUE='".afficheMontant($SESSION_VARS["totaldeb"],false)."'></TD>";
  $html .= "<TD><INPUT TYPE=\"text\" NAME=\"tot_credit\" size=12 disabled=true VALUE='".afficheMontant($SESSION_VARS["totalcred"],false)."'></TD>";
  $html .= "</TR></TABLE>\n";
  $html .= "</TD></TR>\n";

  $html.="</TABLE>";

  //Bouton Ajout ligne
  $MyPage->addFormButton(1, 1, "ajout", _("Ajouter ligne"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ajout", BUTP_AXS, 470);
  $MyPage->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, "Opa-6");
  $MyPage->setFormButtonProperties("ajout", BUTP_CHECK_FORM, false);
  $js = "document.ADForm.nbr_lignes_ecr.value = ".$SESSION_VARS['nbr_lignes_ecr']."+1;\n";
  $MyPage->setFormButtonProperties("ajout", BUTP_JS_EVENT, array("onclick" => $js));

  //Bouton Enregistrer
  $MyPage->addFormButton(1, 2, "enregistrer", _("Enregistrer"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("enregistrer", BUTP_AXS, 470);
  $MyPage->setFormButtonProperties("enregistrer", BUTP_PROCHAIN_ECRAN, "Opa-7");
  $MyPage->setFormButtonProperties("enregistrer", BUTP_CHECK_FORM, true);
  $MyPage->setFormButtonProperties("enregistrer", BUTP_JS_EVENT, array("onclick" => "document.ADForm.bouton_clique.value='enregistrer';"));

  //Bouton Valider
  $MyPage->addFormButton(1, 3, "valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_AXS, 471);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Opa-7");
  $MyPage->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => "document.ADForm.bouton_clique.value='valider';"));

  //Bouton Annuler
  $MyPage->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
  //$MyPage->setFormButtonProperties("annuler", BUTP_AXS, 470);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Opa-5");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM,false);
  //JavaScript
  $html .= "<script type=\"text/javascript\">\n";

  //function  getCompte : ouvre une fenêtre de recherche de compte de client
  $html .= "function getCompte(cpte_comptable,cpte_client,id_compte)\n{\n";
  $html.= "var ch;\n";
  $html.= "ch='../modules/compta/rech_compte_client.php?m_agc=".$_REQUEST['m_agc']."&field_name='+cpte_client+'&id_compte='+id_compte+'&field_cpte_comptable='+cpte_comptable;\n";
  $html .= "OpenBrw(ch, '"._("Recherche")."');\n";
  $html .= "}\n";

  // grise la partie débit ou crédit de la contrepartie si le compte principal est débité ou crédit
  $JSInit = "";
  for ($i=2;$i<=$SESSION_VARS["nbr_lignes_ecr"];$i++)
    $JSInit .= "if(document.ADForm.mntdeb1.value !='') \n document.ADForm.mntdeb$i.disabled = true ;
               else if(document.ADForm.mntcred1.value !='') \n document.ADForm.mntcred$i.disabled = true;\n";
  $MyPage->addJS(JSP_FORM, "JSInit", $JSInit);

  // Vérifie si au moins,le compte principal a été mouvementé
  $JSCheck = "\n\tif(document.ADForm.cpte_comptable1.value == '' || document.ADForm.cpte_comptable1.value == 0)
           {
             msg += '- "._("Vous devez choisir le compte principal")."\\n';
             ADFormValid = false;
           }
             else if(document.ADForm.mntdeb1.value=='' && document.ADForm.mntcred1.value=='' )
           {
             msg += '- "._("Vous devez débiter ou créditer le compte principal")."\\n';
             ADFormValid = false;
           }";
  $MyPage->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

  // Fonction check crédit gen
  $html .="function checkCredit2()\n{\n";
  for ($i=1; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
    $html.="if(document.ADForm.mntcred$i.value)\n";
    $html.="\n\tdocument.ADForm.mntdeb$i.value = '';\n";
  }
  $html.="}\n";

  // Fonction check débit  gen
  $html .="function checkDebit2()\n{\n";
  for ($i=1; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
    $html.="if(document.ADForm.mntdeb$i.value)\n";
    $html.="\n\tdocument.ADForm.mntcred$i.value = '';\n";
  }
  $html.="}\n";

  // Fonction check debit grise les champs de débit
  $html .="function checkDebit()\n{\n";
  $html.="if(document.ADForm.mntdeb1.value!='')\n{\n";
  for ($i=2; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
    $html .="document.ADForm.mntcred$i.disabled=false;\n";
    $html .="document.ADForm.mntdeb$i.value='';\n";
    $html .="document.ADForm.mntdeb$i.disabled=true;\n";
  }
  $html.="}\n";
  $html.="\n\tdocument.ADForm.mntcred1.value = '';\n}\n";

  // Fonction check crédit grise les champs de crédit
  $html .="function checkCredit()\n{\n";
  $html.="if(document.ADForm.mntcred1.value!='')\n{\n";
  for ($i=2; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
    $html .="document.ADForm.mntdeb$i.disabled=false;\n";
    $html .="document.ADForm.mntcred$i.value='';\n";
    $html .="document.ADForm.mntcred$i.disabled=true;\n";
  }
  $html.="}\n";
  $html.="\n\tdocument.ADForm.mntdeb1.value = '';\n}\n";

  //Fonction calcul totaux
  $html .="function CalculTotaux()\n{\n";
  $html.="var debit =new Number(0);\n";
  $html.="var credit =new Number(0);\n";
  for ($i=1; $i<=$SESSION_VARS["nbr_lignes_ecr"]; ++$i) {
    $html .="if(document.ADForm.mntdeb$i.value)\n{\n";
    $html .="debit +=recupMontant(document.ADForm.mntdeb$i.value);";
    $html .="\n}\n";

    $html .="if(document.ADForm.mntcred$i.value)\n{\n";
    $html .="credit +=recupMontant(document.ADForm.mntcred$i.value);";
    $html.="\n}\n";
  }
  $html.="\n\tdocument.ADForm.tot_debit.value = formateMontant(debit);\n";
  $html.="\n\tdocument.ADForm.tot_credit.value = formateMontant(credit);\n}\n";
  $html .= "</SCRIPT>\n";

  $MyPage->addHTMLExtraCode("html",$html);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Opa-10 : Confirmation de la suppression d'écritures */
else if ($global_nom_ecran=="Opa-10") {

  $supprime = supEcritureBrouillard($id_his);

  if ($supprime == true) {
    //HTML
    $MyPage = new HTML_message(_("Confirmation suppression écritures"));
    $MyPage->setMessage(_("Les écritures ont été supprimées dans le brouillard avec succès !"));
    $MyPage->addButton("BUTTON_OK", "Ecr-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {

    $html_err = new HTML_erreur(_("Echec Suppression"));
    $html_err->setMessage(" "._("L'écriture n'a pas été supprimée")." ");
    $html_err->addButton("BUTTON_OK", "Ecr-1");
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>