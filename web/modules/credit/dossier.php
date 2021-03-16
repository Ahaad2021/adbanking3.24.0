<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [105] Création dossier de crédit.
 *
 * Condition : Le client doit être actif et ordinaire
 *	       et ne posseder aucun dossier EAD, EA rééchelonnement/moratoire, accepté ou déboursé.
 *
 * Cette opération comprends les écrans :
 * - Ado-1 : sélection d'un dossier de crédit
 * - Ado-2 : saisie des infos sur le DCR
 * - Ado-3 : échéancier théorique
 * - Ado-4 : perception des frais de dossier
 * - Ado-5 : blocage des garanties
 * - Ado-6 : confirmation création du DCR
 * - Ado-7 : gestion des garanties mobilisées
 * - Ado-8 : ajout de garantie
 * - Ado-9 : confirmation ajout, modification ou suppression de garantie
 * - Ado-10 : modification de garantie
 * - Ado-11 : suppression de garantie
 * - Ado-12 : Choix des liste des membres d'un GS
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/html/HTML_champs_extras.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/misc/divers.php';
require_once('lib/misc/tableSys.php');

/*{{{ Ado-1 : Sélection d'un produit de crédit */
if ($global_nom_ecran == "Ado-1") {

  global $adsys;


  if (isMobileLending()){
      $SESSION_VARS['condition'] = "WHERE mode_calc_int!=5 AND is_mobile_lending_credit = 'f'";
  }else{
      $SESSION_VARS['condition'] = "WHERE mode_calc_int!=5";
  }
  unset($SESSION_VARS['produits_credit']);
  unset($SESSION_VARS['choix_produit']);
  unset($SESSION_VARS['mnt_frais']);

  // Initialisation des données
  if (!(strstr($global_nom_ecran_prec,"Ado"))) {
    // On vient du Menu de Crédits, on vide les données saisies sauf si on vient d'un écran de la même fonction
    unset($SESSION_VARS['def']);
    unset($SESSION_VARS['mnt_frais']);
    $SESSION_VARS['fictif'] = array(); // infos des dossiers fictifs des membres d'un groupe solidaire
    $SESSION_VARS['reel'] = array();  // infos des dossiers reels des membres d'un groupe solidaire
    $SESSION_VARS['liste_membres'] = array(); //liste des membres si GS sinon le client lui-même

    // Récupérations des utilisateurs. C'est à dire les agents gestionnaires
    $SESSION_VARS['utilisateurs'] = array();
    $utilisateurs = getUtilisateurs();
    foreach($utilisateurs as $id_uti=>$val_uti)
    $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom']." ".$val_uti['prenom'];
    //Tri par ordre alphabétique des utilisateurs
 	  natcasesort($SESSION_VARS['utilisateurs']);
    // On récupère les infos du client (ou du GS) et des produits qui lui sont octroyables
    $SESSION_VARS['infos_clients'][$global_id_client] = getClientDatas($global_id_client);
    // Récupération d'infos necessaire au crédit
    if ($SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) { // si Groupe solidaire (GS)
        if (isMobileLending()){
            $SESSION_VARS['condition'] = "WHERE mode_calc_int!=5 AND (gs_cat=1 OR gs_cat=2) AND is_mobile_lending_credit = 'f'";
        }else{
            $SESSION_VARS['condition'] = "WHERE mode_calc_int!=5 AND (gs_cat=1 OR gs_cat=2)"; // Si c'est un GS, récupérer que les produits de crédit destinés aux GS
        }
      // Récupération des membres du groupe
      $result = getListeMembresGrpSol($global_id_client);
      if (is_array($result->param))
        foreach($result->param as $key=>$id_cli) {
        $nom_client = getClientName($id_cli);
        $SESSION_VARS['liste_membres'][$id_cli] = $nom_client;
      }
    } else { // Personne physique, Personne morale ou  Groupe Informel
        if (isMobileLending()){
            $SESSION_VARS['condition'] = "WHERE mode_calc_int!=5 AND (gs_cat IS NULL OR (gs_cat!=1 AND gs_cat!=2)) AND is_mobile_lending_credit = 'f'";
        }else{
            $SESSION_VARS['condition'] = "WHERE mode_calc_int!=5 AND (gs_cat IS NULL OR (gs_cat!=1 AND gs_cat!=2))";// Ne pas récupérer les produits destinés aux GS
        }
      $nom_client = getClientName($global_id_client);
      $SESSION_VARS['liste_membres'][$global_id_client] = $globalid_client." ".$nom_client;
    }
  }

  if ($SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) {
    $prochain_ecran = "Ado-12";
  }
  else {
    $prochain_ecran = "Ado-2";
  }

  // Récupération des infos sur les produits de crédit octroyables au client

  //get produit credit (is_produit_actif) = true in adsys_produit_credit
  $PRODS = getProdInfo($SESSION_VARS['condition'], null, true);

  // Construction de la liste de choix et du Javascript pour la fonction de mise à jour des champs
  $js = "function updateForm(obj)\n{\n ";
  $js .= "\n var prodArray = []; ";

  $ChkJS = "\n prodArray = [\n";

  foreach($PRODS as $key=>$prod) {
    $SESSION_VARS['produits_credit'][$prod['id']] = $prod; // tableaux des produits
    $SESSION_VARS['choix_produit'][$prod['id']] = $prod['libel']; // liste de choix
    if ($prod["gs_cat"] != 0) {
      $js .= "if ((document.ADForm.HTML_GEN_LSB_id_prod.value == ".$prod["id"].")) {";
      $js .= "document.ADForm.HTML_GEN_LSB_periodicite.value = ".$prod["periodicite"].";";
      $js .= "document.ADForm.HTML_GEN_LSB_devise.value = '".$prod["devise"]."';";
      $js .= "document.ADForm.HTML_GEN_LSB_gs_cat.value = ".$prod["gs_cat"].";";
      $js .= "}";
    } else 	{
      $js .= "if ((document.ADForm.HTML_GEN_LSB_id_prod.value == ".$prod["id"].")) {";
      $js .= "document.ADForm.HTML_GEN_LSB_periodicite.value = ".$prod["periodicite"].";";
      $js .= "document.ADForm.HTML_GEN_LSB_devise.value = '".$prod["devise"]."';";
      $js .= "}";
    }
      $ChkJS .= "{ id_prod: ".$prod["id"].", is_flexible: ". (($prod["is_flexible"]=='t')?1:0) ." },\n";
  }

  $ChkJS .= "];\n ";

  // Allow modify produit crédit parameters if profil have access
  if (check_access(106)) {
    $js .= $ChkJS;
  }

  $js .= "if (obj != null && lookupProdArray( obj.value , prodArray) == 1) {";
  $js .= "if (document.ADForm.HTML_GEN_BOL_is_extended.parentNode.parentNode.style.display=='none'){";
  $js .= "document.ADForm.HTML_GEN_BOL_is_extended.parentNode.parentNode.style.display='table-row';";
  $js .= "}";
  $js .= "if (document.ADForm.HTML_GEN_BOL_is_extended.checked==true){";
  $js .= "document.ADForm.valider.onclick = assign('Ado-13');checkForm();";
  $js .= "}else{";
  $js .= "document.ADForm.valider.onclick = assign('$prochain_ecran');checkForm();";
  $js .= "}";
  $js .= "} else {";
  $js .= "document.ADForm.HTML_GEN_BOL_is_extended.checked=false;";
  $js .= "document.ADForm.HTML_GEN_BOL_is_extended.parentNode.parentNode.style.display='none';";
  $js .= "document.ADForm.valider.onclick = assign('$prochain_ecran');checkForm();";
  $js .= "} ";

  $js .= "};";
  $js .= "function lookupProdArray( index , arr)
                 {
                     for(var i = 0, len = arr.length; i < len; i++)
                     {
                         if( arr[ i ].id_prod == index )
                         {
                            return arr[ i ].is_flexible;
                         }
                     }
                     return false;
                 };\n ";
  $js .= "updateForm(null);";
  //Trier les produits par ordre alphabétique
 	natcasesort($SESSION_VARS['choix_produit']);

  // Création du formulaire
  $Myform = new HTML_GEN2(_("Choix du produit de crédit"));
  $Myform->addField("id_prod", _("Type de produit de crédit"), TYPC_LSB);
  $Myform->setFieldProperties("id_prod", FIELDP_ADD_CHOICES, $SESSION_VARS['choix_produit']);
  $Myform->addLink("id_prod", "produit",_("Détail produit"), "#"); // lien détail sur le produit sélectionné
  $Myform->setLinkProperties("produit",LINKP_JS_EVENT,array("onClick"=>"open_produit(document.ADForm.HTML_GEN_LSB_id_prod.value,0);"));
  $Myform->addTable("ad_dcr", OPER_INCLUDE,  array("num_cre"));
  $Myform->addTable("adsys_produit_credit", OPER_INCLUDE, array("periodicite", "devise", "gs_cat"));

  $Myform->setFieldProperties("periodicite",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("devise",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("gs_cat",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("num_cre",FIELDP_DEFAULT,  getNumCredit($global_id_client) + 1);
  $Myform->setFieldProperties("num_cre",FIELDP_IS_LABEL,true);

  $Myform->addField("is_extended", _("Modifier les paramètres du produit de crédit ?"), TYPC_BOL);
  $Myform->setFieldProperties("is_extended", FIELDP_DEFAULT, false);

  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, $prochain_ecran);
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->addJS(JSP_FORM,"JS",$js);
  $Myform->setFieldProperties("id_prod", FIELDP_JS_EVENT, array("onchange" => "updateForm(this);"));
  $Myform->setFieldProperties("is_extended", FIELDP_JS_EVENT, array("onclick" => "updateForm(document.ADForm.HTML_GEN_LSB_id_prod);"));

  $order = array("id_prod", "devise", "periodicite", "num_cre", "gs_cat", "is_extended");
  if (isset($SESSION_VARS["def"]["id_prod"])) {
    $PROD_DEF = getProdInfo(" WHERE id = ".$SESSION_VARS["def"]["id_prod"]);
    $Myform->setFieldProperties("id_prod", FIELDP_DEFAULT, $SESSION_VARS["def"]["id_prod"]);
    $jsdef = "document.ADForm.HTML_GEN_LSB_devise.value = '".$PROD_DEF[0]["devise"]."';";
    $jsdef .= "document.ADForm.HTML_GEN_LSB_periodicite.value = ".$PROD_DEF[0]["periodicite"].";";
    $Myform->addJS(JSP_FORM, "def", $jsdef);
  }

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Ado-12 : Choix des membres du GS pour le credit */
else if ($global_nom_ecran == "Ado-12")
{
  // Récupération du id du produit choisi
  if (isset($id_prod)) {
    $SESSION_VARS['id_prod'] = $id_prod;
  }

  // TODO : Recup Flex produit credit data
  if (strstr($global_nom_ecran_prec,"Ado-13")) {
    // Update produit credit session data
    updateProdCreditSessionData($tx_interet, $periodicite, $gs_cat, $prc_assurance, $mnt_assurance, $prc_frais, $mnt_frais, $prc_commission, $mnt_commission, $prc_gar_num);
  }

  // garde la liste recuperer
  $temp_liste_membres = $SESSION_VARS['liste_membres'];

  $Myform = new HTML_GEN2(_("Choix des membres du groupe solidaire"));

  $gs_nom = getClientName($global_id_client);
  $Myform->addHTMLExtraCode("GS", "<b>"._($gs_nom)."</b>");
  $Myform->setHTMLExtraCodeProperties("GS", HTMP_IN_TABLE, true);

  // si on vient de l'ecran detail, il faut r-a-z la liste des membres
  if (strstr($global_nom_ecran_prec,"Ado-2"))
  {
    if ($SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) { // si Groupe solidaire (GS)
      $SESSION_VARS['condition'] = "WHERE mode_calc_int!=5 AND (gs_cat=1 OR gs_cat=2)"; // Si c'est un GS, récupérer que les produits de crédit destinés aux GS
      // Récupération des membres du groupe
      $result = getListeMembresGrpSol($global_id_client);
      if (is_array($result->param))
        foreach($result->param as $key=>$id_cli) {
          $nom_client = getClientName($id_cli);
          $SESSION_VARS['liste_membres'][$id_cli] = $nom_client;
      }
    }
  }

  natcasesort($SESSION_VARS['liste_membres']);

  foreach($SESSION_VARS['liste_membres'] as $id_membre => $nom_membre) {
    $Myform->addField("membre_$id_membre", $nom_membre, TYPC_BOL);

    // si on vient de Ado-2, cocher seulement les membres choisis precedemment
    if((strstr($global_nom_ecran_prec,"Ado-2")) && is_array($temp_liste_membres) && array_key_exists($id_membre, $temp_liste_membres)){
      $Myform->setFieldProperties("membre_$id_membre", FIELDP_DEFAULT, true);
    }
    else if(!(strstr($global_nom_ecran_prec,"Ado-2"))) { // sinon tout cocher
      $Myform->setFieldProperties("membre_$id_membre", FIELDP_DEFAULT, true);
    }
  }

  $code_bol_js .="\t\t\t function checkBolFields() { \n";
  $code_bol_js .="\t\t\t var is_checked = false; \n";

  foreach($SESSION_VARS['liste_membres'] as $id_membre => $nom_membre) {
    $code_bol_js .="\t\t\t if(document.ADForm.HTML_GEN_BOL_membre_$id_membre.checked == true){ \n";
    $code_bol_js .="\t\t\t is_checked = true; \n";
    $code_bol_js .="\t\t\t} \n";
  }

  $code_bol_js .="\t\t\t if(is_checked == false){ \n";
  $code_bol_js .= "\t\t\t  msg += '- Veuillez choisir au moins un membre' ;\n";
  $code_bol_js .="\t\t\t ADFormValid=false; \n";
  $code_bol_js .="\t\t\t} \n";

  $code_bol_js .="\t\t\t} \n"; // end javascript function
  $code_bol_js .="\t\t\t checkBolFields(); \n"; // end javascript function

  $Myform->addJS(JSP_BEGIN_CHECK, "JS_BOL", $code_bol_js);

  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);
  $Myform->addFormButton(1,3, "coche_tout", _("Tout cocher/décocher"), TYPB_BUTTON);
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ado-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyjsCk = 'var checker = "false";
				function check(formulaire_mbr_gs) {
					if (checker == "false") {
  						for (i = 0; i < formulaire_mbr_gs.length; i++) {
  							formulaire_mbr_gs[i].checked = true;}
  							checker = "true";
  						 }
					else {
  						for (i = 0; i < formulaire_mbr_gs.length; i++) {
  							formulaire_mbr_gs[i].checked = false;}
  							checker = "false";
  						 }
				}';

  $Myform->addJS(JSP_FORM, "js_check", $MyjsCk);
  $Myform->setFormButtonProperties("coche_tout", BUTP_JS_EVENT, array("onclick" => "check(document.ADForm);"));

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Ado-13 : Modification des paramètres du produit de crédit */
else if ($global_nom_ecran == "Ado-13")
{
  global $global_id_client;

  // Récupération du id du produit choisi
  if (isset($id_prod)) {
    $SESSION_VARS['id_prod'] = $id_prod;
  }

  // Recup info produit credit
  $prodCredit = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']];

  $Myform = new HTML_GEN2(_("Modification des paramètres du produit de crédit"));

  // Libellé
  $Myform->addField("libel", _("Libellé produit de crédit"), TYPC_TXT);
  $Myform->setFieldProperties("libel",FIELDP_DEFAULT, trim($prodCredit['libel']));
  $Myform->setFieldProperties("libel",FIELDP_IS_LABEL,true);

  // Devise
  $Myform->addField("devise", _("Devise du crédit"), TYPC_TXT);
  $Myform->setFieldProperties("devise",FIELDP_DEFAULT, trim($prodCredit['devise']));
  $Myform->setFieldProperties("devise",FIELDP_IS_LABEL,true);

  /* Affiche des informations par section */
  $Myform->addHTMLExtraCode("infosintpen", "<b>Paramétrage des intérêts et des pénalités</b>");
  $Myform->setHTMLExtraCodeProperties("infosintpen",HTMP_IN_TABLE, true);

  // Taux d’intérêt
  $Myform->addField("tx_interet", _("Taux d’intérêt"), TYPC_PRC);
  $Myform->setFieldProperties("tx_interet",FIELDP_DEFAULT, trim($prodCredit['tx_interet'])*100);

  // Périodicité
  $Myform->addField("periodicite", _("Périodicité"), TYPC_LSB);
  $Myform->setFieldProperties("periodicite", FIELDP_ADD_CHOICES, $adsys["adsys_type_periodicite"]);
  $Myform->setFieldProperties("periodicite",FIELDP_DEFAULT, trim($prodCredit['periodicite']));

  /* Affiche des informations par section */
  $Myform->addHTMLExtraCode("infosgen", "<b>Paramétrage général</b>");
  $Myform->setHTMLExtraCodeProperties("infosgen",HTMP_IN_TABLE, true);

  // Catégorie de GS
  $Myform->addField("gs_cat", _("Catégorie de groupe solidaire"), TYPC_LSB);
  $Myform->setFieldProperties("gs_cat", FIELDP_ADD_CHOICES, $adsys["adsys_categorie_gs"]);
  $Myform->setFieldProperties("gs_cat",FIELDP_DEFAULT, trim($prodCredit['gs_cat']));
  if ($SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) {
    $Myform->setFieldProperties("gs_cat", FIELDP_HAS_CHOICE_AUCUN, false);
  } else {
    $Myform->setFieldProperties("gs_cat", FIELDP_IS_LABEL, true);
  }

  /* Affiche des informations par section */
  $Myform->addHTMLExtraCode("infosfin", "<b>Paramétrage financier</b>");
  $Myform->setHTMLExtraCodeProperties("infosfin",HTMP_IN_TABLE, true);

  // Montant des frais de dossier
  $Myform->addField("mnt_frais", _("Montant des frais de dossier"), TYPC_MNT);
  $Myform->setFieldProperties("mnt_frais",FIELDP_DEFAULT, trim($prodCredit['mnt_frais']));

  // Pourcentage des frais de dossier
  $Myform->addField("prc_frais", _("Pourcentage des frais de dossier"), TYPC_PRC);
  $Myform->setFieldProperties("prc_frais",FIELDP_DEFAULT, trim($prodCredit['prc_frais'])*100);

  // Montant de la commission
  $Myform->addField("mnt_commission", _("Montant de la commission"), TYPC_MNT);
  $Myform->setFieldProperties("mnt_commission",FIELDP_DEFAULT, trim($prodCredit['mnt_commission']));

  // Pourcentage de commission
  $Myform->addField("prc_commission", _("Pourcentage de commission"), TYPC_PRC);
  $Myform->setFieldProperties("prc_commission",FIELDP_DEFAULT, trim($prodCredit['prc_commission'])*100);

  /* Affiche des informations par section */
  $Myform->addHTMLExtraCode("infosgarass", "<b>Paramétrage des garanties et des assurances</b>");
  $Myform->setHTMLExtraCodeProperties("infosgarass",HTMP_IN_TABLE, true);

  // Garantie numéraire à bloquer au début
  $Myform->addField("prc_gar_num", _("Garantie numéraire à bloquer au début"), TYPC_PRC);
  $Myform->setFieldProperties("prc_gar_num",FIELDP_DEFAULT, trim($prodCredit['prc_gar_num'])*100);

  // Montant assurance
  $Myform->addField("mnt_assurance", _("Montant assurance"), TYPC_MNT);
  $Myform->setFieldProperties("mnt_assurance",FIELDP_DEFAULT, trim($prodCredit['mnt_assurance']));

  // Pourcentage d'assurance
  $Myform->addField("prc_assurance", _("Pourcentage d'assurance"), TYPC_PRC);
  $Myform->setFieldProperties("prc_assurance",FIELDP_DEFAULT, trim($prodCredit['prc_assurance'])*100);

  // Ordre d'affichage des champs
  $ordre = array("libel","devise","infosintpen","tx_interet","periodicite","infosgen","gs_cat","infosfin","mnt_frais","prc_frais","mnt_commission","prc_commission","infosgarass","prc_gar_num","mnt_assurance","prc_assurance");

  $Myform->setOrder(NULL, $ordre);

  if ($SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) {
    $prochain_ecran = "Ado-12";
  }
  else {
    $prochain_ecran = "Ado-2";
  }

  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, $prochain_ecran);
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, true);

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Ado-2 : Saisie des infos sur le DCR */
else if ($global_nom_ecran == "Ado-2") {
  global $adsys, $global_id_agence;

  $temp_liste_membres = $SESSION_VARS['liste_membres'];
  $dataAgence = getAgenceDatas($global_id_agence);
  $SESSION_VARS["is_cpte_remb"] =  'f';

  if (strstr($global_nom_ecran_prec,"Ado-12"))
  {
    if ($SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) { // si Groupe solidaire (GS)
      foreach($temp_liste_membres as $id_membre => $nom_membre) {
        if(!isset($_POST['membre_'.$id_membre])) {
          unset($SESSION_VARS['liste_membres'][$id_membre]);
        }
      }
    }
  }

  // Récupération du id du produit choisi
  if (isset($id_prod) && !isset($SESSION_VARS['id_prod'])) {
    $SESSION_VARS['id_prod'] = $id_prod;
  }

  // TODO : Recup Flex produit credit data
  if (strstr($global_nom_ecran_prec,"Ado-13")) {
    // Update produit credit session data
    updateProdCreditSessionData($tx_interet, $periodicite, $gs_cat, $prc_assurance, $mnt_assurance, $prc_frais, $mnt_frais, $prc_commission, $mnt_commission, $prc_gar_num);
  }
   // Devise du produit
  setMonnaieCourante($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]);
  // Client pouvant avoir un dossier de crédit réel
  $SESSION_VARS['clients_dcr'] = array(); // liste des membres pouvant avoir un dossier réel
  if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 1) // Si GS à dossier unique, seul le GS a un dossier réel
    $SESSION_VARS['clients_dcr'][$global_id_client] = $SESSION_VARS['liste_membres'][$global_id_client]." ".getClientName($global_id_client);
  elseif($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2) //GS avec doss multiples,chaque mbre a son dossier
    $SESSION_VARS['clients_dcr'] = $SESSION_VARS['liste_membres'];
  else // Crédit pour personne physique, Personne morale ou groupe informel : un seul dossier pour le client
    $SESSION_VARS['clients_dcr'][$global_id_client] = $SESSION_VARS['liste_membres'][$global_id_client];

  // Création du formulaire
  $Myform = new HTML_GEN2(_("Saisie des informations"));

  // Affichage des champs
  $JS_check = ""; // Javascript de validation de la saisie
  $js_copie_mnt_dem = ""; // sauvegare de mnt_dem si le champ est désactivé

  //Récuperation des Détails objets de crédits
  $det_dem = getDetailsObjCredit();
  $det_dem2 = getDetailsObjCredit2();

  foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom)
  {
    // Pour chaque client pouvant avoir un dossier reel
    $Myform->addHTMLExtraCode("espace".$id_cli,"<br /><b><p align=\"center\"><b>".sprintf(_("Saisie du dossier de %s"),$nom)." </b></p>");
    // Ajout de champs
    $Myform->addField("nom_client".$id_cli, _("Client"), TYPC_TXT);
    $Myform->addField("id_prod".$id_cli, _("Produit de crédit"), TYPC_TXT);
    $Myform->addField("devise".$id_cli, _("Devise"), TYPC_TXT);
    $Myform->addField("num_cre".$id_cli, _("Numéro crédit"), TYPC_TXT);
    $Myform->addField("date_dem".$id_cli, _("Date de la demande"), TYPC_DTE);
    $Myform->addField("cre_date_debloc".$id_cli, _("Date de déblocage"), TYPC_DTF);
    $Myform->setFieldProperties("cre_date_debloc".$id_cli,FIELDP_DEFAULT,$val_doss['cre_date_debloc']);
    $Myform->addField("id_bailleur".$id_cli, _("Source de financement"), TYPC_LSB);
    $list_bailleur = getListBailleur();
    $Myform->setFieldProperties("id_bailleur".$id_cli, FIELDP_ADD_CHOICES, $list_bailleur);
    $Myform->addField("obj_dem".$id_cli, _("Objet de la demande"), TYPC_LSB);
    $obj_dem = getObjetsCredit();
    $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_ADD_CHOICES, $obj_dem);

    if (isDcrDetailObjCreditLsb()) {
      if($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2){
        $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_JS_EVENT, array("onchange"=>"setDetailObj$id_cli();"));
      }
      else{
        $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_JS_EVENT, array("onchange"=>"setDetailObj();"));
      }

      $Myform->addField("detail_obj_dem_bis".$id_cli, _("Détail de la demande 1"), TYPC_LSB);
    }
    else
    {
      if($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2){ //AT-97 : on ajoute cette condition ici aussi meme si le champ detail objet demande n'est pas une liste deroulante
        $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_JS_EVENT, array("onchange"=>"setDetailObj$id_cli();"));
      }
      else{
        $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_JS_EVENT, array("onchange"=>"setDetailObj();"));
      }

      $Myform->addField("detail_obj_dem".$id_cli, _("Détail de la demande 1"), TYPC_TXT);
    }
    $Myform->addField("detail_obj_dem_2".$id_cli, _("Détail de la demande 2"), TYPC_LSB);

    $Myform->addField("mnt_dem".$id_cli, _("Montant de la demande"), TYPC_MNT);
    $Myform->addHiddenType("hid_mnt_dem".$id_cli, $SESSION_VARS['def'][$id_cli]['mnt_dem']);
    //type de durée : en mois ou en semaine
    $type_duree = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["type_duree_credit"];
    $libelle_duree = mb_strtolower(adb_gettext($adsys["adsys_type_duree_credit"][$type_duree])); // libellé type durée en minuscules
    $Myform->addField("duree_mois".$id_cli, _("Durée en ".$libelle_duree), TYPC_INT);

    $Myform->addField("differe_jours".$id_cli, _("Différé en jours"), TYPC_INN);
    $Myform->addField("differe_ech".$id_cli, _("Différé en échéances"), TYPC_INT);
    $Myform->addField("delai_grac".$id_cli, _("Délai de grace"), TYPC_INT);
    $Myform->addField("nb_jr_bloq_cre_avant_ech".$id_cli, _("Nombre de jours pour bloquer<br/>le crédit avant échéance"), TYPC_INT);
    $Myform->addField("etat".$id_cli, _("Etat du dossier"), TYPC_TXT);
    $Myform->addField("cpt_liaison".$id_cli, _("Compte de liaison"), TYPC_LSB);
    if($SESSION_VARS["is_cpte_remb"] == 't')
        $Myform->addField("cpt_remb".$id_cli, _("Compte de remboursement"), TYPC_LSB);
    $cptes_liaison = getComptesLiaison ($id_cli, $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]);
    $CPT_LIE = array(); // compte de liaison pour un client
    foreach($cptes_liaison as $id_cpte=>$compte) {
      $CPT_LIE[$id_cpte] = $compte["num_complet_cpte"]." ".$compte["intitule_compte"];
      $CPT_PRELEV_FRAIS[$id_cpte] =  $compte["num_complet_cpte"]." ".$compte["intitule_compte"];
    }
    $Myform->setFieldProperties("cpt_liaison".$id_cli, FIELDP_ADD_CHOICES, $CPT_LIE);
    if($SESSION_VARS["is_cpte_remb"] == 't')
        $Myform->setFieldProperties("cpt_remb".$id_cli, FIELDP_ADD_CHOICES, $CPT_LIE);
    $Myform->addField("id_agent_gest".$id_cli, _("Agent gestionnaire"), TYPC_LSB);
    $Myform->setFieldProperties("id_agent_gest".$id_cli, FIELDP_ADD_CHOICES, $SESSION_VARS['utilisateurs']);
    $Myform->addField("prelev_auto".$id_cli, _("Prélèvement automatique"), TYPC_BOL);
    $Myform->addField("mnt_commission".$id_cli, _("Commission"), TYPC_TXT);
    $Myform->addField("mnt_assurance".$id_cli, _("Assurance"), TYPC_TXT);
    if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_tot'] > 0 || $prc_gar_num >0 ||$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_num'] >0 ) {
      $Myform->addField("gar_num".$id_cli, _("Garantie numéraire attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num".$id_cli, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("gar_num".$id_cli,FIELDP_DEFAULT, $SESSION_VARS['def'][$id_cli]['gar_num']);
      $Myform->addField("gar_num_mob".$id_cli, _("Garantie numéraire mobilisée"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num_mob".$id_cli, FIELDP_IS_READONLY, true);
      $Myform->addField("gar_mat".$id_cli, _("Garantie matérielle attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_mat".$id_cli, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("gar_mat".$id_cli,FIELDP_DEFAULT, $SESSION_VARS['def'][$id_cli]['gar_mat']);
      $Myform->addField("gar_mat_mob".$id_cli, _("Garantie matérielle mobilisée"), TYPC_MNT);
      $Myform->setFieldProperties("gar_mat_mob".$id_cli, FIELDP_IS_READONLY, true);
      $Myform->addField("gar_tot".$id_cli, _("Garantie totale attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_tot".$id_cli, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("gar_tot".$id_cli,FIELDP_DEFAULT, $SESSION_VARS['def'][$id_cli]['gar_tot']);
    }
    if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_encours']>0) {
      $Myform->addField("gar_num_encours".$id_cli, _("Garantie encours attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num_encours".$id_cli, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("gar_num_encours".$id_cli,FIELDP_DEFAULT, $SESSION_VARS['def'][$id_cli]['gar_num_encours']);
    }

    $id_client=$id_cli;

    // Les champs obligatoires
    $Myform->setFieldProperties("id_prod".$id_cli,FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("devise".$id_cli,FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("mnt_dem".$id_cli,FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("obj_dem".$id_cli,FIELDP_IS_REQUIRED, true);
    if (isDcrDetailObjCreditLsb()) {
      $Myform->setFieldProperties("detail_obj_dem_bis".$id_cli,FIELDP_IS_REQUIRED, false);
    } else {
      $Myform->setFieldProperties("detail_obj_dem".$id_cli,FIELDP_IS_REQUIRED, true);
    }
    $Myform->setFieldProperties("detail_obj_dem_2".$id_cli,FIELDP_IS_REQUIRED, false);
    $Myform->setFieldProperties("date_dem".$id_cli,FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("duree_mois".$id_cli,FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("cpt_liaison".$id_cli,FIELDP_IS_REQUIRED, true);
    if($SESSION_VARS["is_cpte_remb"] == 't')
        $Myform->setFieldProperties("cpt_remb".$id_cli,FIELDP_IS_REQUIRED, true);

    // Les champs non modifiables
    $Myform->setFieldProperties("nom_client".$id_cli, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("id_prod".$id_cli, FIELDP_IS_LABEL, true);


    $Myform->setFieldProperties("mnt_assurance".$id_cli, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("mnt_commission".$id_cli, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("etat".$id_cli, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("num_cre".$id_cli, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("devise".$id_cli, FIELDP_IS_LABEL, true);

    // Les valeurs par défaut des champs
    $Myform->setFieldProperties("nom_client".$id_cli,FIELDP_DEFAULT,$nom);
    $produit = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["libel"];
    $Myform->setFieldProperties("id_prod".$id_cli,FIELDP_DEFAULT,$produit);
    $Myform->setFieldProperties("devise".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]);
    $Myform->setFieldProperties("mnt_dem".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['mnt_dem']);

    // Copie de de mnt dem au cas ou mnt_dem est désactivéut
    $js_copie_mnt_dem .= "document.ADForm.hid_mnt_dem".$id_cli.".value = recupMontant(document.ADForm.mnt_dem".$id_cli.".value);";
    $Myform->setFieldProperties("id_bailleur".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['id_bailleur']);
    $Myform->setFieldProperties("obj_dem".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['obj_dem']);
    if (isDcrDetailObjCreditLsb()) {
      $Myform->setFieldProperties("detail_obj_dem_bis" . $id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['detail_obj_dem_bis']);
    } else {
      $Myform->setFieldProperties("detail_obj_dem".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['detail_obj_dem']);
    }
    $Myform->setFieldProperties("detail_obj_dem_2" . $id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['detail_obj_dem_2']);
    $Myform->setFieldProperties("etat".$id_cli,FIELDP_DEFAULT, adb_gettext($adsys["adsys_etat_dossier_credit"][1]));
    $Myform->setFieldProperties("prelev_auto".$id_cli,FIELDP_DEFAULT, $SESSION_VARS['def'][$id_cli]['prelev_auto']);
    $Myform->setFieldProperties("cpt_liaison".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['cpt_liaison']);
    if($SESSION_VARS["is_cpte_remb"] == 't')
        $Myform->setFieldProperties("cpt_remb".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['cpt_remb']);

    if (!isset($SESSION_VARS['def'][$id_cli]['date_dem']))
      $SESSION_VARS['def'][$id_cli]['date_dem'] = date("d/m/Y");
    $Myform->setFieldProperties("date_dem".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['date_dem']);
    $Myform->setFieldProperties("cre_date_debloc".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['cre_date_debloc']);
    $Myform->setFieldProperties("duree_mois".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['duree_mois']);
    $Myform->setFieldProperties("differe_jours".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['differe_jours']);
    $Myform->setFieldProperties("differe_ech".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['differe_ech']);
    $Myform->setFieldProperties("id_agent_gest".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['id_agent_gest']);
    $Myform->setFieldProperties("delai_grac".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['delai_grac']);
    $Myform->setFieldProperties("nb_jr_bloq_cre_avant_ech".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['def'][$id_cli]['nb_jr_bloq_cre_avant_ech']);
    $Myform->setFieldProperties("mnt_assurance".$id_cli,FIELDP_DEFAULT,afficheMontant($SESSION_VARS['def'][$id_cli]['assurances_cre']));
    $Myform->setFieldProperties("mnt_commission".$id_cli,FIELDP_DEFAULT,afficheMontant($SESSION_VARS['def'][$id_cli]['mnt_commission']));


    $js_init = "initMnt($id_cli);"; // initialisation de champs, au chargement de la page, en fonction de mnt demandé
    $Myform->addJS(JSP_FORM, "js_int".$id_cli, $js_init);

    $SESSION_VARS['def'][$id_cli]['gar_num_mob'] = 0; // garanties numéraires totales mobilisées
    $SESSION_VARS['def'][$id_cli]['gar_mat_mob'] = 0; // garanties matérilles totales mobilisées
    if (is_array($SESSION_VARS['def'][$id_cli]['DATA_GAR'])) {
      foreach($SESSION_VARS['def'][$id_cli]['DATA_GAR'] as $key=>$value ) {
        if (($id_cli == $value['benef']) and ($value['type'] == 1))
          $SESSION_VARS['def'][$id_cli]['gar_num_mob'] += recupMontant($value['valeur']);
        elseif(($id_cli == $value['benef']) and ($value['type'] == 2))
        $SESSION_VARS['def'][$id_cli]['gar_mat_mob'] += recupMontant($value['valeur']);
      }
    }
      if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_tot"] > 0 || $prc_gar_num >0  ||$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_num'] >0 ) {
        $Myform->setFieldProperties("gar_num_mob".$id_cli,FIELDP_DEFAULT, $SESSION_VARS['def'][$id_cli]['gar_num_mob']);
        $Myform->setFieldProperties("gar_mat_mob".$id_cli,FIELDP_DEFAULT, $SESSION_VARS['def'][$id_cli]['gar_mat_mob']);
      }
      $num_cre = getNumCredit($id_cli) + 1;
      $Myform->setFieldProperties("num_cre".$id_cli,FIELDP_DEFAULT, $num_cre);

      // Evenement JavaScript des champs
      // GS avec dossiers reels multiples, calcul du montant total demandé
      if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2)
        $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_JS_EVENT, array("onchange"=>"setMontantDemande();"));

      $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_JS_EVENT, array("OnFocus"=>"resetMnt($id_cli);"));
      $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_JS_EVENT, array("OnChange"=>"initMnt($id_cli);"));

      // Vérifier que le montant demandé est correct
      $field = "document.ADForm.mnt_dem".$id_cli;
      $JS_check .= " if (!parseFloat(recupMontant($field.value)))
                 {
                 $field.value = '';
                 msg += '".sprintf(_("Le montant demandé par le client %s doit être correctement renseigné"),$id_cli)."\\n';
                 ADFormValid=false;
               }";
      // Vérifier que le montant demandé est entre le max et le min
      $min = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['mnt_min'];
      $max = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['mnt_max'];
      if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['mnt_max'] > 0)
        $JS_check .= "if ((parseFloat(recupMontant($field.value)) < parseFloat(".$min.")) || (parseFloat(recupMontant($field.value)) > parseFloat(".$max.")))
                   {
                   $field.value = '';
                   msg += '- ".sprintf(_("Le montant demandé par le client %s doit être compris entre %s et %s comme défini dans le produit"),$id_cli,afficheMontant($min),afficheMontant($max))."\\n';
                   ADFormValid=false;
                 }";
      else
        $JS_check .= "if (parseFloat(recupMontant($field.value)) < parseFloat(".$min."))
                   {
                   $field.value = '';
                   msg += '- Le montant demandé par le client $id_cli doit être au moins égal à ".afficheMontant($min)." comme défini dans le produit\\n'
                   ADFormValid=false;
                 }";

      // Vérifier que le montant totat mobilisé est supérieur ou égal au montant attendu
      if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_tot"] > 0 || $prc_gar_num > 0 ||$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_num'] >0 ) {
        $gar_num_mob = "document.ADForm.gar_num_mob".$id_cli;
        $gar_mat_mob = "document.ADForm.gar_mat_mob".$id_cli;
        $gar_tot = "document.ADForm.gar_tot".$id_cli;
        if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_num"] > 0) {
          $gar_num = "document.ADForm.gar_num".$id_cli;
          // Vérifer que les garanties numéraires mobilisées sont supérieues aux garanties numéraires attendues
          $JS_check .=  "
                if (recupMontant($gar_num.value) > recupMontant($gar_num_mob.value))
                   {
                        msg += '- ".sprintf(_("Les garanties numéraires mobilisées par le client %s sont insuffisantes"),$id_cli)."\\n';
                        ADFormValid = false;
                   }";
        }
        if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_mat"] > 0) {
          $gar_mat = "document.ADForm.gar_mat".$id_cli;
          // Vérifer que les garanties matérielles mobilisées sont supérieues aux garanties matérielles attendues
          $JS_check .=  "
                if (recupMontant($gar_mat.value) > recupMontant($gar_mat_mob.value))
                   {
                         msg += '- ".sprintf(_("Les garanties matérielles mobilisées par le client %s sont insuffisantes"),$id_cli)."\\n';
                         ADFormValid = false;
                   }";
        }
        $JS_check .=  "
                gar_tot_mob = recupMontant($gar_num_mob.value)+recupMontant($gar_mat_mob.value);
                if (recupMontant($gar_tot.value) > gar_tot_mob)
                   {
                       msg += '- ".sprintf(_("Le montant total des garanties numéraires et matérielles mobilisées par le client %s est insuffisant"),$id_cli)."\\n';
                       ADFormValid = false;
                   }
                ";
      }

    // Vérifier que la durée est comprise entre le max et le min
    $duree_max = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['duree_max_mois'];
    $duree_min = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['duree_min_mois'];
    if ($duree_max > 0)
      $JS_check .= "if ((parseInt(document.ADForm.duree_mois".$id_cli.".value) < parseInt(".$duree_min.")) || (parseInt(document.ADForm.duree_mois".$id_cli.".value) > parseInt(".$duree_max.")))
                   {
                   msg+=' - ".sprintf(_("La durée du crédit pour le client %s doit être comprise entre %s et %s comme définie dans le produit."),$id_cli,$duree_min,$duree_max)."\\n';
                   ADFormValid=false;
                 }";
    else
      $JS_check .= "if (parseInt(document.ADForm.duree_mois".$id_cli.".value) < parseInt(".$duree_min."))
                   {
                   msg+=' - ".sprintf(_("La durée du crédit pour le client %s doit être au moins égale à %s comme définie dans le produit."),$id_cli,$duree_min)."\\n';
                   ADFormValid=false;
                 }";

    // Vérifier que le différé en jour est inférieur au max
    $differe_max = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['differe_jours_max'];
    $differe_min = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['differe_jours_min'];
    $JS_check .= "if (parseInt(document.ADForm.differe_jours".$id_cli.".value) > parseInt(".$differe_max."))
                 {
                 msg +='- ".sprintf(_("Le differé pour le client %s doit être inférieur ou égal à %s jours"),$id_cli,$differe_max)."\\n';
                 ADFormValid=false;
               }";
    //Vérifier que le differé en échéances est inférieur au max
    $differe_max = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['differe_ech_max'];
    $JS_check .= "if (parseInt(document.ADForm.differe_ech".$id_cli.".value) > parseInt(".$differe_max."))
                 {
                 msg +='- ".sprintf(_("Le differé pour le client %s doit être inférieur ou égal à %s échéance(s)"),$id_cli,$differe_max)."\\n';
                 ADFormValid=false;
               }";
    //Vérifier que le delai de grâce est inférieur au max
    $delai_grac = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['delai_grace_jour'];
    $JS_check .= "if (parseInt(document.ADForm.delai_grac".$id_cli.".value) > parseInt(".$delai_grac."))
                 {
                 msg +='- ".sprintf(_("Le delai de grâce pour le client %s doit être inférieur ou égal à %s jours"),$id_cli,$delai_grac)." \\n';
                 ADFormValid=false;
               }";
    //Vérifier que le nombre de jours pour bloquer les credits avant echéance est inférieur au max
    $nb_jr_bloq_cre_avant_ech_max = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['nb_jr_bloq_cre_avant_ech_max'];
    $JS_check .= "if (parseInt(document.ADForm.nb_jr_bloq_cre_avant_ech".$id_cli.".value) > parseInt(".$nb_jr_bloq_cre_avant_ech_max."))
                 {
                 msg +='- ".sprintf(_("Le nombre de jours pour bloquer le crédit avant échéance doit être inférieur ou égal à %s jours"),$nb_jr_bloq_cre_avant_ech_max)." \\n';
                 ADFormValid=false;
               }";
    // Test de la durée demandée à faire uniquement si le nombre de mois de la période est > 1
    $periodicite = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['periodicite'];
    if ($adsys["adsys_duree_periodicite"][$periodicite] > 1) {
      $JS_check .= "
                   if(parseInt(document.ADForm.duree_mois".$id_cli.".value) % parseInt(".$adsys["adsys_duree_periodicite"][$periodicite].")!=0)
                 {
                   msg +='- ".sprintf(_("La durée pour le client %s doit être multiple de %s"),$id_cli,adb_gettext($adsys["adsys_duree_periodicite"][$periodicite]))."';
                   ADFormValid = false;
                 }";
    }
   //gestion des champs supplémentaire
    //foreach (array_keys($SESSION_VARS['clients_dcr']) as $id_cli) {
    	$objChampsExtras = new HTML_Champs_Extras ($Myform,'ad_dcr',$id_cli);
   		$objChampsExtras->buildChampsExtras($SESSION_VARS['def'][$id_cli]['champsExtrasValues']);
   		$SESSION_VARS['def'][$id_cli]['champsExtras']= $objChampsExtras-> getChampsExtras();
    //}
  }

  // Traitement particulier aux dossiers fictifs des GS
  if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 1) { // GS avec dossier unique
    $Myform->setFieldProperties("mnt_dem".$id_client, FIELDP_IS_LABEL, true);
    //Javascript :
    $js_code  = "function setMontantDemande(){\n"; // calcul le montant total demandé
    $js_code  .="document.ADForm.mnt_dem".$id_client.".value = formateMontant(";
    foreach($SESSION_VARS['liste_membres'] as $id_cli=>$val)
      $js_code  .="recupMontant(document.ADForm.mnt_dem".$id_cli.".value)+\n";

    $js_code=substr($js_code,0,strlen($js_code)-2);
    $js_code  .= ");}\n";
    $js_code  .= "setMontantDemande();\n";

    $Myform->addJS(JSP_FORM, "js1", $js_code);
    $champHidden="<input type=\"hidden\" name=\"nb_mem\" value=\"".sizeof($SESSION_VARS['liste_membres'])."\" />";
    $Myform->addHTMLExtraCode("champ_hidden_nb_mem",$champHidden);
    $Myform->addHTMLExtraCode("detail_credit","<br /><Table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Détails du crédit par membre")."</b></td></tr></table>\n");

    $JS_4 = "";

    foreach($SESSION_VARS['liste_membres'] as $id_cli=>$val) {
      $Myform->addField("membre".$id_cli, _("Membre"), TYPC_TXT);
      $Myform->setFieldProperties("membre".$id_cli, FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("membre".$id_cli, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("membre".$id_cli, FIELDP_DEFAULT,$id_cli." ".getClientName($id_cli));
      $Myform->addField("id_bailleur".$id_cli, _("Source de financement"), TYPC_LSB);
      $list_bailleur = getListBailleur();
      $Myform->setFieldProperties("id_bailleur".$id_cli, FIELDP_ADD_CHOICES, $list_bailleur);
      $Myform->setFieldProperties("id_bailleur".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['fictif'][$id_cli]['id_bailleur']);
      $Myform->addField("obj_dem".$id_cli, _("Objet demande"), TYPC_LSB);
      $obj_dem = getObjetsCredit();
      $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_ADD_CHOICES, $obj_dem);
      $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['fictif'][$id_cli]['obj_dem']);
      if (isDcrDetailObjCreditLsb()) {
        $Myform->setFieldProperties("obj_dem" . $id_cli, FIELDP_JS_EVENT, array("onchange" => "setDetailObj$id_cli();"));
        $Myform->addField("detail_obj_dem_bis" . $id_cli, _("Détail demande 1"), TYPC_LSB);
      } else {
        $Myform->addField("detail_obj_dem".$id_cli, _("Détail demande 1"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem".$id_cli, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("detail_obj_dem".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['fictif'][$id_cli]['detail_obj_dem']);
      }
      $Myform->setFieldProperties("obj_dem" . $id_cli, FIELDP_JS_EVENT, array("onchange" => "setDetailObj$id_cli();"));
      $Myform->addField("detail_obj_dem_2" . $id_cli, _("Détail demande 2 "), TYPC_LSB);


      $Myform->addField("mnt_dem".$id_cli, _("Montant demande"), TYPC_MNT);
      $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("mnt_dem".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['fictif'][$id_cli]['mnt_dem']);
       // Vérifier que le montant demandé est correct
      $field = "document.ADForm.mnt_dem".$id_cli;
      $JS_check .= " if (!parseFloat(recupMontant($field.value)))
                 {
                 $field.value = '';
                 msg += '".sprintf(_("Le montant demandé par le client %s doit être correctement renseigné"),$id_cli)."\\n';
                 ADFormValid=false;
               }";

      $Myform->setFieldProperties("mnt_dem".$id_cli,FIELDP_JS_EVENT,array("onchange"=>"setMontantDemande();initMnt($id_client)"));
      $js_init = "initMnt($id_client);"; // initialisation de champs, au chargement de la page, en fonction de mnt demandé
      $Myform->addJS(JSP_FORM, "js_int".$id_cli, $js_init);
      $Myform->addHTMLExtraCode("epace".$id_cli,"<BR>");

      //AT-97 : GS dossier unique - JS controle pour le champ detail objet demande 2 meme si le champ detail objet demande 1 n'est pas une liste deroulante
      $JS_4 .= "function setDetailObj$id_cli(){ \n  var myArray2 = [\n";


      foreach ($det_dem2 as $key2 => $value2) {

        $JS_4 .= "{ key2: $key2, value2: ['" . $value2['id_obj'] . "','" . $value2['libelle'] . "'] },";
      }

      $JS_4 .= "];\n ";
      // JS pour la "Details de la demande 2"
      $JS_4 .= " if( lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray2 ) != false ) { \n";
      $JS_4 .= " var select2 = document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli;
              select2.options.length=0;";
      $JS_4 .= "var det_cred2=(lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray2 ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred2){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(det_cred2[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
      $JS_4 .= "}\n";

      if (isDcrDetailObjCreditLsb()) {
        $JS_4 = "";

        $JS_4 .= "function setDetailObj$id_cli(){ \n var myArray = [\n";


        foreach ($det_dem as $key => $value) {

          $JS_4 .= "{ key: $key, value: ['" . $value['id_obj'] . "','" . $value['libel'] . "'] },";
        }

        $JS_4 .= "];\n ";

        $JS_4 .= " if( lookup(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray ) != false ) { \n";
        $JS_4 .= " var select = document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli;
              select.options.length=0;";
        $JS_4 .= "var det_cred=(lookup(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.length] = new Option(det_cred[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
        // Ticket AT-97 : "Detail de la demande 2"
        $JS_4 .="\n var myArray2 = [\n";


        foreach ($det_dem2 as $key2 => $value2) {

          $JS_4 .= "{ key2: $key2, value2: ['" . $value2['id_obj'] . "','" . $value2['libelle'] . "'] },";
        }

        $JS_4 .= "];\n ";
        // JS pour la "Details de la demande 2"
        $JS_4 .= " if( lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray2 ) != false ) { \n";
        $JS_4 .= " var select2 = document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli;
              select2.options.length=0;";
        $JS_4 .= "var det_cred2=(lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray2 ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred2){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(det_cred2[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
        $JS_4 .= "}\n";
      }
    }
    //if (isDcrDetailObjCreditLsb()) {
      $Myform->addJS(JSP_FORM, "det_obj_gs", $JS_4);
    //}

  }
  elseif($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2) { // GS avec dossiers multiples
    $Myform->addHTMLExtraCode("epace_".$id_cli,"<BR>");
    $Myform->addField("nom_client".$global_id_client, _("Nom du groupe"), TYPC_TXT);
    $Myform->addField("id_prod".$global_id_client, _("Produit de crédit"), TYPC_TXT);
    $Myform->addField("gs_cat".$global_id_client,"Catégorie dossier",TYPC_TXT);
    $Myform->addField("mnt_dem".$global_id_client,"Montant total demandé",TYPC_MNT);
    ///$Myform->addHiddenType("hid_mnt_dem".$global_id_client, $val_doss['cre_mnt_octr']);

    $Myform->setFieldProperties("nom_client".$global_id_client,FIELDP_DEFAULT,$global_id_client." ".getClientName($global_id_client));
    $Myform->setFieldProperties("id_prod".$global_id_client,FIELDP_DEFAULT, $SESSION_VARS['id_prod']." ".$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["libel"]);
    $Myform->setFieldProperties("gs_cat".$global_id_client,FIELDP_DEFAULT,adb_gettext($adsys["adsys_categorie_gs"][$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["gs_cat"]]));
    $Myform->setFieldProperties("nom_client".$global_id_client, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("id_prod".$global_id_client, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("gs_cat".$global_id_client, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("mnt_dem".$global_id_client, FIELDP_IS_LABEL, true);

    // ajout des comptes du groupe dans les comptes de prélèvement des frais de dossier
    $cptes_liaison = getComptesLiaison ($global_id_client, $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]);
    foreach($cptes_liaison as $id_cpte=>$compte)
      $CPT_PRELEV_FRAIS[$id_cpte] =  $compte["num_complet_cpte"]." ".$compte["intitule_compte"];

    // Javascript
    $js_code  = "function setMontantDemande(){\n"; // calcul le montant total demandé
    $js_code  .="document.ADForm.mnt_dem".$global_id_client.".value = formateMontant(";
    foreach($SESSION_VARS['liste_membres'] as $id_cli=>$val)
      $js_code  .="recupMontant(document.ADForm.mnt_dem".$id_cli.".value)+\n";

    $js_code=substr($js_code,0,strlen($js_code)-2);
    $js_code .= ");";
    $js_code .= "}\n";
    $js_code  .= "setMontantDemande();\n";
    $Myform->addJS(JSP_FORM, "js1", $js_code);

    $js_6 = " ";
    //AT-97 : GS dossier unique - JS controle pour le champ detail objet demande 2 meme si le champ detail objet demande 1 n'est pas une liste deroulante
    foreach ($SESSION_VARS['liste_membres'] as $id_cli => $val) {
      $js_6 .= "function setDetailObj$id_cli(){ \n var myArray2 = [\n";


      foreach ($det_dem2 as $key2 => $value2) {

        $js_6 .= "{ key2: $key2, value2: ['" . $value2['id_obj'] . "','" . $value2['libelle'] . "'] },";
      }

      $js_6 .= "];\n ";

      $js_6 .= " if( lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray2 ) != false ) { \n";
      $js_6 .= " var select2 = document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli;
                select2.options.length=0;";
      $js_6 .= "var det_cred2=(lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray2 ));\n
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
                for(var i in det_cred2){
                  document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(det_cred2[i] ,i, false, false);
               }
               }
               else{
               document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options.length=0;
               document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
               }";
      $js_6 .= "}\n";
    }

    if (isDcrDetailObjCreditLsb()) {
      $js_6 = " ";
      foreach ($SESSION_VARS['liste_membres'] as $id_cli => $val) {

        $js_6 .= "function setDetailObj$id_cli(){ \n var myArray = [\n";


        foreach ($det_dem as $key => $value) {

          $js_6 .= "{ key: $key, value: ['" . $value['id_obj'] . "','" . $value['libel'] . "'] },";
        }

        $js_6 .= "];\n ";

        $js_6 .= " if( lookup(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray ) != false ) { \n";
        $js_6 .= " var select = document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli;
              select.options.length=0;";
        $js_6 .= "var det_cred=(lookup(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.length] = new Option(det_cred[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";

        $js_6 .= "var myArray2 = [\n";


        foreach ($det_dem2 as $key2 => $value2) {

          $js_6 .= "{ key2: $key2, value2: ['" . $value2['id_obj'] . "','" . $value2['libelle'] . "'] },";
        }

        $js_6 .= "];\n ";

        $js_6 .= " if( lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray2 ) != false ) { \n";
        $js_6 .= " var select2 = document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli;
              select2.options.length=0;";
        $js_6 .= "var det_cred2=(lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_cli.value, myArray2 ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred2){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(det_cred2[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
        $js_6 .= "}\n";

      }
    }
    $Myform->addJS(JSP_FORM, "js_det_obf_gs2", $js_6);
  }

  // Ajout des frais et du compte de prélèvement
  $Myform->addField("mnt_frais", _("Montant des frais de dossier"), TYPC_MNT);
  $Myform->setFieldProperties("mnt_frais",FIELDP_CAN_MODIFY,true);
  $Myform->setFieldProperties("mnt_frais", FIELDP_IS_LABEL, true);

  $CPT_PRELEV_FRAIS1  = array(); // compte sur les quels on peut ptrélever les frais de dossier
  //$CPT_PRELEV_FRAIS1[-1] = "Le montant du crédit ";
  if($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2) {
    $CPT_PRELEV_FRAIS1[-2] = "Les comptes de liaison ";
  }
  $cptes_liaison = getComptesLiaison ($global_id_client, $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]);

  foreach($cptes_liaison as $id_cpte=>$compte) {
    $CPT_PRELEV_FRAIS1[$id_cpte] =  $compte["num_complet_cpte"]." ".$compte["intitule_compte"];
  }

  $Myform->addField("cpt_prelev_frais", _("Prélèvement des frais sur"), TYPC_LSB);
  $Myform->setFieldProperties("cpt_prelev_frais", FIELDP_ADD_CHOICES, $CPT_PRELEV_FRAIS1);

  if (isset($SESSION_VARS['cpt_prelev_frais'])){
    $Myform->setFieldProperties("cpt_prelev_frais", FIELDP_DEFAULT, $SESSION_VARS['cpt_prelev_frais']);
    // si le prélévement des frais doss se fait lors du déboursement et perception de frais comm se fait après débours. cpte_prelev_frais=cpt_liaison
    if($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prelev_frais_doss'] == 2
        && $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["percep_frais_com_ass"]==2)
    {
      $js_cpt_prelev = "function updateCptPrelevFrais()\n{\n ";

      if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2) {
        $js_cpt_prelev .= "document.ADForm.HTML_GEN_LSB_cpt_prelev_frais.value =-2;";
        $js_cpt_prelev .= "document.ADForm.HTML_GEN_LSB_cpt_prelev_frais.setAttribute('disabled', 'true')";
      } else {     ;
        $js_cpt_prelev .= "document.ADForm.HTML_GEN_LSB_cpt_prelev_frais.value = document.ADForm.HTML_GEN_LSB_cpt_liaison".$id_client.".value;";
        $js_cpt_prelev .= "document.ADForm.HTML_GEN_LSB_cpt_prelev_frais.setAttribute('disabled', 'true')";
        $Myform->setFieldProperties("cpt_liaison".$id_client, FIELDP_JS_EVENT, array("OnChange"=>"updateCptPrelevFrais()"));
      }
      $js_cpt_prelev .= "};";
      $js_cpt_prelev .= "updateCptPrelevFrais();";
    }
  }

  //les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);

  if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_tot"] > 0 || $prc_gar_num >0||$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_num'] >0) {
    $Myform->addFormButton(1,3,"mobiliser_gar", _("Mobilisation garanties"), TYPB_SUBMIT);
    $Myform->addFormButton(1,4,"annuler",_("Annuler"),TYPB_SUBMIT);
    $Myform->setFormButtonProperties("mobiliser_gar", BUTP_PROCHAIN_ECRAN, "Ado-7");
    $Myform->setFormButtonProperties("mobiliser_gar", BUTP_CHECK_FORM, false);
    $Myform->setFormButtonProperties("mobiliser_gar", BUTP_JS_EVENT, array("onclick" => $js_copie_mnt_dem));
  } else
    $Myform->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);

  if ($SESSION_VARS['infos_clients'][$global_id_client]['statut_juridique'] == 4) {
    $ecran_retour = "Ado-12";
  }
  else

    $ecran_retour = "Ado-1";

  //Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ado-3");
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  // JS des boutons
  $Myform->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => $js_copie_mnt_dem));
  // Calule du montant de l'assurance, de la commission et des garanties en fonction du montant à octoyer
  $prc_assurance = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_assurance"];
  $mnt_assurance = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["mnt_assurance"];debug($mnt_assurance);
  $prc_commission = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_commission"];
  $mnt_commission = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["mnt_commission"];
  $mnt_frais = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["mnt_frais"];
  $prc_frais = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_frais"];
  $prc_gar_num = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_num"];
  $prc_gar_mat = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_mat"];
  $prc_gar_tot = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_tot"];
  $prc_gar_encours = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_encours"];
  $devise_prod=$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['devise'];
  $DEV = getInfoDevise($devise_prod);// recuperation d'info sur la devise'
  $precision_devise=pow(10,$DEV["precision"]);

  // Initialisation de champs dèsque le champ mnt_octr est activé
  $js_mnt_reset = "function resetMnt(id_cli) { \n";
  $js_mnt_reset .= "var mnt_dem = 'mnt_dem'+id_cli;\n";
  $js_mnt_reset .= "var mnt_assurance = 'mnt_assurance'+id_cli;\n";
  $js_mnt_reset .= "var mnt_commission = 'mnt_commission'+id_cli;\n";
  $js_mnt_reset .= "var gar_num ='gar_num'+id_cli;\n";
  $js_mnt_reset .= "var gar_mat ='gar_mat'+id_cli;\n";
  $js_mnt_reset .= "var gar_tot ='gar_tot'+id_cli;\n";
  $js_mnt_reset .= "var gar_num_encours ='gar_num_encours'+id_cli;\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_dem).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_assurance).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_commission).value ='';\n";
  if ($prc_gar_tot > 0 || $prc_gar_num >0) {
    $js_mnt_reset .= "document.ADForm.eval(gar_num).value ='';\n";
    $js_mnt_reset .= "document.ADForm.eval(gar_mat).value ='';\n";
    $js_mnt_reset .= "document.ADForm.eval(gar_tot).value ='';\n";
  }
  if ($prc_gar_encours > 0)
    $js_mnt_reset .= "document.ADForm.eval(gar_num_encours).value ='';\n";
  $js_mnt_reset .= "}\n";
  $Myform->addJS(JSP_FORM,"js_mnt_reset",$js_mnt_reset);


  // Application des tarifications
  $tarifications = getListeTarification(true, false);

  $tarif_frais = getTarificationDatas("CRED_FRAIS");
  $frais_min = $tarifications['CRED_FRAIS']['valeur_min'];
  $frais_max = $tarifications['CRED_FRAIS']['valeur_max'];

  $tarif_comm = getTarificationDatas("CRED_COMMISSION");
  $comm_min = $tarifications['CRED_COMMISSION']['valeur_min'];
  $comm_max = $tarifications['CRED_COMMISSION']['valeur_max'];

  $tarif_assurance = getTarificationDatas("CRED_ASSURANCE");
  $assurance_min = $tarifications['CRED_ASSURANCE']['valeur_min'];
  $assurance_max = $tarifications['CRED_ASSURANCE']['valeur_max'];

    $js_mnt_init = "function initMnt(id_cli) { \n";
    $js_mnt_init .= "var mnt_dem = 'mnt_dem'+id_cli;\n";
    $js_mnt_init .= "var mnt_assurance = 'mnt_assurance'+id_cli;\n";
  	$js_mnt_init .= "var mnt_commission = 'mnt_commission'+id_cli;\n";
  	$js_mnt_init .= "var mnt_frais = 'mnt_frais';\n";
  	$js_mnt_init .= "var gar_num = 'gar_num'+id_cli;\n";
  	$js_mnt_init .= "var gar_mat = 'gar_mat'+id_cli;\n";
  	$js_mnt_init .= "var gar_tot = 'gar_tot'+id_cli;\n";
  	$js_mnt_init .= "var gar_num_encours ='gar_num_encours'+id_cli;\n";

    $js_mnt_init .="\t\t\t var mnt_demande_saisie = parseFloat(recupMontant(eval('document.ADForm.'+mnt_dem).value)); \n";

    $js_mnt_init .="\t\t\t if(mnt_demande_saisie > 0) { \n"; // si on a saisie un montant
        // assurances
        $js_mnt_init .="\t\t\tvar mnt_assurance_pour = ".$prc_assurance." * mnt_demande_saisie; \n";

        if(!is_null($tarif_assurance)) { // si on a une tarification valide
          // Les bornes min/max s'applique sur la valeur en pourcentage seulement
          $js_mnt_init .="\t\t\tif(mnt_assurance_pour < $assurance_min) mnt_assurance_pour = $assurance_min ;\n";
          $js_mnt_init .="\t\t\tif(mnt_assurance_pour > $assurance_max) mnt_assurance_pour = $assurance_max ;\n";
        }
        $js_mnt_init .="\t\t\tvar mnt_assurance_calc = formateMontant((Math.round(mnt_assurance_pour + ".$mnt_assurance.")*".$precision_devise.")/".$precision_devise.");\n";
        $js_mnt_init .="\t\t\teval('document.ADForm.'+mnt_assurance).value = mnt_assurance_calc; \n";

        // commissions
        $js_mnt_init .="\t\t\tvar mnt_comm_pour = " .$prc_commission. " * mnt_demande_saisie; \n";

        if(!is_null($tarif_comm)) {  // si on a une tarification valide
          // Les bornes min/max s'applique sur la valeur en pourcentage seulement
          $js_mnt_init .="\t\t\tif(mnt_comm_pour < $comm_min) mnt_comm_pour = $comm_min ;\n";
          $js_mnt_init .="\t\t\tif(mnt_comm_pour > $comm_max) mnt_comm_pour = $comm_max ;\n";
        }
        $js_mnt_init .="\t\t\tvar mnt_comm_calc = formateMontant((Math.round(mnt_comm_pour + ".$mnt_commission.")*".$precision_devise.")/".$precision_devise.");\n";
        $js_mnt_init .="\t\t\teval('document.ADForm.'+mnt_commission).value = mnt_comm_calc; \n";

        // Frais de dossier
        $js_mnt_init .="\t\t\tvar mnt_frais_pour = " .$prc_frais. " * mnt_demande_saisie; \n";

        if(!is_null($tarif_frais)) {  // si on a une tarification valide
          // Les bornes min/max s'applique sur la valeur en pourcentage seulement
          $js_mnt_init .="\t\t\tif(mnt_frais_pour < $frais_min) mnt_frais_pour = $frais_min ;\n";
          $js_mnt_init .="\t\t\tif(mnt_frais_pour > $frais_max) mnt_frais_pour = $frais_max ;\n";
        }

        $js_mnt_init .="\t\t\tvar mnt_frais_calc = formateMontant((Math.round(mnt_frais_pour + ".$mnt_frais.") * ".$precision_devise.")/".$precision_devise.");\n";

        $frais_recup = $SESSION_VARS['mnt_frais'];
        $mnt_dem_recup = $SESSION_VARS['def'][$id_client]['mnt_dem'];

        if(!empty($frais_recup)) {
          $js_mnt_init .="\t\t\tif(mnt_demande_saisie > 0 && mnt_demande_saisie == $mnt_dem_recup) { \n";
          $js_mnt_init .="\t\t\t mnt_frais_calc = $frais_recup; \n";
          $js_mnt_init .="\t\t\t } \n";
        }

        // assign the processed value
        $js_mnt_init .="\t\t\teval('document.ADForm.'+mnt_frais).value = mnt_frais_calc; \n";

    $js_mnt_init .="\t\t\t } else { \n"; // sinon prendre les montants fixe des frais si on n'as pas saisie le montant du credit
      $js_mnt_init .="\t\t\teval('document.ADForm.'+mnt_assurance).value = $mnt_assurance; \n";
      $js_mnt_init .="\t\t\teval('document.ADForm.'+mnt_commission).value = $mnt_commission; \n";
      $js_mnt_init .="\t\t\teval('document.ADForm.'+mnt_frais).value = $mnt_frais; \n";
    $js_mnt_init .="\t\t\t }  \n";


    // garanties
    if ($prc_gar_tot>0 || $prc_gar_num >0) {
		$js_mnt_init .="\t\t\teval('document.ADForm.'+gar_num).value = formateMontant(Math.round(".$prc_gar_num."* parseFloat(recupMontant(eval('document.ADForm.'+mnt_dem).value))*".$precision_devise.")/".$precision_devise.");\n";
		$js_mnt_init .="\t\t\teval('document.ADForm.'+gar_mat).value = formateMontant(Math.round(".$prc_gar_mat."* parseFloat(recupMontant(eval('document.ADForm.'+mnt_dem).value))*".$precision_devise.")/".$precision_devise.");\n";
      if ($prc_gar_tot > 0) {
        $js_mnt_init .= "\t\t\teval('document.ADForm.'+gar_tot).value = formateMontant(Math.round(" . $prc_gar_tot . "* parseFloat(recupMontant(eval('document.ADForm.'+mnt_dem).value))*" . $precision_devise . ")/" . $precision_devise . ");\n";
      }
      else {
        $js_mnt_init .= "\t\t\teval('document.ADForm.'+gar_tot).value = formateMontant(Math.round(parseFloat(recupMontant(eval('document.ADForm.'+gar_num).value))*" . $precision_devise . ")/" . $precision_devise . ");\n";
      }
	 }

	if ($prc_gar_encours>0) {
    $js_mnt_init .="\t\t\teval('document.ADForm.'+gar_num_encours).value = formateMontant(Math.round(".$prc_gar_encours."* parseFloat(recupMontant(eval('document.ADForm.'+mnt_dem).value))*".$precision_devise.")/".$precision_devise.");\n";
  }
	$js_mnt_init .= "}";
  $Myform->addJS(JSP_FORM,"js_mnt_init",$js_mnt_init);

  // Ajout des codes javascript
  $Myform->addJS(JSP_BEGIN_CHECK,"test",$JS_check);

  // Ajout du code JS
  $Myform->addJS(JSP_FORM,"rech",$JS_2);

  //AT-97 : JS controle pour le champ detail objet demande 2 si le champ detail objet demande 1 n'est pas une liste deroulante
  $JS_3 = " ";
  $JS_3 .= "function lookup2( name , arr)
            {
                var arrret=[]
                for(var i = 0, len = arr.length; i < len; i++)
                {

                    if( arr[ i ].value2[0] == name )
                    {
                    arrret[ arr[ i ].key2]=arr[ i ].value2[1];

                    }
                }
                if (arrret.length > 0){
                  return arrret;
                  }
                  else {
                return false;}

            };\n";

  $JS_3 .= "function setDetailObj(){ \n  var myArray2 = [\n";


  foreach ($det_dem2 as $key2 => $value2) {

    $JS_3 .= "{ key2: $key2, value2: ['" . $value2['id_obj'] . "','" . $value2['libelle'] . "'] },";
  }

  $JS_3 .= "];\n ";

  $JS_3 .= " if( lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_client.value, myArray2 ) != false ) { \n";
  $JS_3 .= " var select2 = document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client;
              select2.options.length=0;";
  $JS_3 .= "var det_cred2=(lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_client.value, myArray2 ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred2){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.length] = new Option(det_cred2[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
  $JS_3 .= "}\n";

  if (isDcrDetailObjCreditLsb()) {
    $JS_3 = " ";
    $JS_3 .= "function lookup( name , arr)
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
                return false;}

            };\n

            function lookup2( name , arr)
            {
                var arrret=[]
                for(var i = 0, len = arr.length; i < len; i++)
                {

                    if( arr[ i ].value2[0] == name )
                    {
                    arrret[ arr[ i ].key2]=arr[ i ].value2[1];

                    }
                }
                if (arrret.length > 0){
                  return arrret;
                  }
                  else {
                return false;}

            };\n";

    $JS_3 .= "function setDetailObj(){ \n var myArray = [\n";


    foreach ($det_dem as $key => $value) {

      $JS_3 .= "{ key: $key, value: ['" . $value['id_obj'] . "','" . $value['libel'] . "'] },";
    }

    $JS_3 .= "];\n ";

    $JS_3 .= " if( lookup(document.ADForm.HTML_GEN_LSB_obj_dem$id_client.value, myArray ) != false ) { \n";
    $JS_3 .= " var select = document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_client;
              select.options.length=0;";
    $JS_3 .= "var det_cred=(lookup(document.ADForm.HTML_GEN_LSB_obj_dem$id_client.value, myArray ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_client.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_client.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_client.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_client.length] = new Option(det_cred[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_client.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_client.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_client.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";

    $JS_3 .=" \n var myArray2 = [\n";


    foreach ($det_dem2 as $key2 => $value2) {

      $JS_3 .= "{ key2: $key2, value2: ['" . $value2['id_obj'] . "','" . $value2['libelle'] . "'] },";
    }

    $JS_3 .= "];\n ";

    $JS_3 .= " if( lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_client.value, myArray2 ) != false ) { \n";
    $JS_3 .= " var select2 = document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client;
              select2.options.length=0;";
    $JS_3 .= "var det_cred2=(lookup2(document.ADForm.HTML_GEN_LSB_obj_dem$id_client.value, myArray2 ));\n
              document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.length] = new Option(\"[Aucun]\" ,0, false, false);
              for(var i in det_cred2){
                document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.length] = new Option(det_cred2[i] ,i, false, false);
             }
             }
             else{
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.options.length=0;
             document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.options[document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.length] = new Option(\"[Aucun]\" ,0, false, false);
             }";
    $JS_3 .= "}\n";

    // Ajout du code JS
    //$Myform->addJS(JSP_FORM,"det_obj",$JS_3);

    // Insertion des données gardées dans les variables de sessions
    $js_page_init="";
    $js_page_init.= "\nwindow.onload = function() {";
    $js_page_init.= "\nsetDetailObj();";
    $cree_js_page_init=false;
    if($SESSION_VARS['def'][$id_client]['detail_obj_dem_bis'] != ''){
      //$js_page_init="";
      $det = $SESSION_VARS['def'][$id_client]['detail_obj_dem_bis'];
      $js_page_init.= "\ndocument.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_client.value = $det;";
      if($SESSION_VARS['fictif']!= null){
        foreach($SESSION_VARS['fictif'] as $id_cli => $value){
          if ($value['detail_obj_dem_bis']!=null){
            $deta=$value['detail_obj_dem_bis'];
            $js_page_init.=" \n setDetailObj$id_cli();";
            $js_page_init.=" document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.value = $deta;";
          }
        }
      }
      //$js_page_init.=" }; ";
      //$Myform->addJS(JSP_FORM,"js_page_init",$js_page_init);
      $cree_js_page_init=true;
    }
    //AT-97 : Insertion des données gardées dans les variables de sessions pour le champ detail de la demande 2
    if($SESSION_VARS['def'][$id_client]['detail_obj_dem_2'] != ''){
      //$js_detail_obj_dem_2="";
      $det = $SESSION_VARS['def'][$id_client]['detail_obj_dem_2'];
      $js_page_init.= "\ndocument.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.value = $det;";
      if($SESSION_VARS['fictif']!= null){
        foreach($SESSION_VARS['fictif'] as $id_cli => $value){
          if ($value['detail_obj_dem_2']!=null){
            $deta=$value['detail_obj_dem_2'];
            $js_page_init.=" \n setDetailObj$id_cli();";
            $js_page_init.=" document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.value = $deta;";
          }
        }
      }
      //$js_detail_obj_dem_2.=" }; ";
      //$Myform->addJS(JSP_FORM,"js_page_init_detail_demande2",$js_detail_obj_dem_2);
      $cree_js_page_init=true;
    }
    $js_page_init.=" }; ";
    if ($cree_js_page_init === true){
      $Myform->addJS(JSP_FORM,"js_page_init",$js_page_init);
    }

    $js_page_init2="";
    if($SESSION_VARS['def']!= null and $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2 ){
      $js_page_init2.= "\nwindow.onload = function() {";
      $setDetailObjCli = false;
      foreach($SESSION_VARS['def'] as $id_cli => $value){
        $js_page_init2.=" \n setDetailObj$id_cli();";
        if ($value['detail_obj_dem_bis'] != null){
          $setDetailObjCli = true;
        }
        if ($value['detail_obj_dem_2'] != null){
          $setDetailObjCli = true;
        }
        if ($setDetailObjCli === true){
          $js_page_init2.=" \n setDetailObj$id_cli();";
        }
        if ($value['detail_obj_dem_bis'] != null){
          $deta=$value['detail_obj_dem_bis'];
          //$js_page_init2.=" \n setDetailObj$id_cli();";
          $js_page_init2.=" document.ADForm.HTML_GEN_LSB_detail_obj_dem_bis$id_cli.value = $deta;";
        }
        if ($value['detail_obj_dem_2'] != null){
          $deta=$value['detail_obj_dem_2'];
          //$js_page_init2.=" \n setDetailObj$id_cli();";
          $js_page_init2.=" document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.value = $deta;";
        }
      }
      $js_page_init2.=" }; ";
      $Myform->addJS(JSP_FORM,"js_page_init2",$js_page_init2);
    }
  }
  else{//AT-97 : si le champ detail de la demande 1 n'est pas une liste deroulante
    // Insertion des données gardées dans les variables de sessions
    $js_page_init="";
    $js_page_init.= "\nwindow.onload = function() {";
    $js_page_init.= "\nsetDetailObj();";
    $cree_js_page_init=false;
    //AT-97 : Insertion des données gardées dans les variables de sessions pour le champ detail de la demande 2
    if($SESSION_VARS['def'][$id_client]['detail_obj_dem_2'] != ''){
      //$js_detail_obj_dem_2="";
      $det = $SESSION_VARS['def'][$id_client]['detail_obj_dem_2'];
      $js_page_init.= "\ndocument.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_client.value = $det;";
      if($SESSION_VARS['fictif']!= null){
        foreach($SESSION_VARS['fictif'] as $id_cli => $value){
          if ($value['detail_obj_dem_2']!=null){
            $deta=$value['detail_obj_dem_2'];
            $js_page_init.=" \n setDetailObj$id_cli();";
            $js_page_init.=" document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.value = $deta;";
          }
        }
      }
      //$js_detail_obj_dem_2.=" }; ";
      //$Myform->addJS(JSP_FORM,"js_page_init_detail_demande2",$js_detail_obj_dem_2);
      $cree_js_page_init=true;
    }
    $js_page_init.=" }; ";
    if ($cree_js_page_init === true){
      $Myform->addJS(JSP_FORM,"js_page_init",$js_page_init);
    }

    $js_page_init2="";
    if($SESSION_VARS['def']!= null and $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2 ){
      $js_page_init2.= "\nwindow.onload = function() {";
      foreach($SESSION_VARS['def'] as $id_cli => $value){
        $js_page_init2.=" \n setDetailObj$id_cli();";
        if ($value['detail_obj_dem_2'] != null){
          $deta=$value['detail_obj_dem_2'];
          $js_page_init2.=" \n setDetailObj$id_cli();";
          $setDetailObjCli = true;
          $js_page_init2.=" document.ADForm.HTML_GEN_LSB_detail_obj_dem_2$id_cli.value = $deta;";
        }
      }
      $js_page_init2.=" }; ";
      $Myform->addJS(JSP_FORM,"js_page_init2",$js_page_init2);
    }
  }

  // Ajout du code JS
  $Myform->addJS(JSP_FORM,"det_obj",$JS_3);

  $Myform->addJS(JSP_FORM,"js_cpt_prelev",$js_cpt_prelev);

  // enable the field so that it is posted
  $js_enable_fields = " if(ADFormValid == true) {\n\t\t document.ADForm.mnt_frais.removeAttribute('disabled'); document.ADForm.HTML_GEN_LSB_cpt_prelev_frais.removeAttribute('disabled'); }\n";
  $Myform->addJS(JSP_END_CHECK,"js_enable_fields",$js_enable_fields);

  /* Mémorisation des noms postés à l'écran suivant */
  $SESSION_VARS["POSTED_DATAS"] = $order;

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Ado-3 : Echéancier théorique */
else if ($global_nom_ecran == "Ado-3") {

  $id_prod = $SESSION_VARS['id_prod'];
  $num_client = $global_id_client;

  if(isset($_POST['mnt_frais']))
    $SESSION_VARS["mnt_frais"] = recupMontant($_POST['mnt_frais']);

  if (isset($HTML_GEN_LSB_cpt_prelev_frais))
    $SESSION_VARS['cpt_prelev_frais'] = $HTML_GEN_LSB_cpt_prelev_frais;

  // si aucun compte de prelevement de frais n'a été choisi, prendre le compte de liaison
  if(($SESSION_VARS['cpt_prelev_frais']==0)&& ($SESSION_VARS['produits_credit'][$id_prod]["gs_cat"] != 2))  {
  	$SESSION_VARS['cpt_prelev_frais']=${'cpt_liaison'.$global_id_client};//compte de liaison
  }elseif(($SESSION_VARS['cpt_prelev_frais']==0)&& ($SESSION_VARS['produits_credit'][$id_prod]["gs_cat"] == 2)){
  	$SESSION_VARS['cpt_prelev_frais']=-2;//compte de liaison
  }

  // Sauve les valeurs renseignées pour les réafficher en cas de retour arrière
  $tot_mnt_dem = 0;
  foreach($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {

    $SESSION_VARS['def'][$id_cli]['id_prod'] = $id_prod;
    $SESSION_VARS['def'][$id_cli]['cre_date_debloc'] = $ {'cre_date_debloc'.$id_cli};
    $SESSION_VARS['def'][$id_cli]['date_dem'] = $ {'date_dem'.$id_cli};
    $SESSION_VARS['def'][$id_cli]['duree_mois'] = $ {'duree_mois'.$id_cli};
    $SESSION_VARS['def'][$id_cli]['differe_jours'] = $ {'differe_jours'.$id_cli};
    $SESSION_VARS['def'][$id_cli]['differe_ech'] = $ {'differe_ech'.$id_cli};
    $SESSION_VARS['def'][$id_cli]['id_agent_gest'] = ($ {'id_agent_gest'.$id_cli} > 0 ? $ {'id_agent_gest'.$id_cli} : 0);
    $SESSION_VARS['def'][$id_cli]['delai_grac'] = $ {'delai_grac'.$id_cli};
    $SESSION_VARS['def'][$id_cli]['nb_jr_bloq_cre_avant_ech'] = $ {'nb_jr_bloq_cre_avant_ech'.$id_cli};
    $SESSION_VARS['def'][$id_cli]['id_bailleur'] = $ {'id_bailleur'.$id_cli};
    $SESSION_VARS['def'][$id_cli]['obj_dem'] = $ {'obj_dem'.$id_cli};
    if (isDcrDetailObjCreditLsb()) {
      $SESSION_VARS['def'][$id_cli]['detail_obj_dem_bis'] = ${'detail_obj_dem_bis' . $id_cli};
    } else {
      $SESSION_VARS['def'][$id_cli]['detail_obj_dem'] = $ {'detail_obj_dem'.$id_cli};
    }
    $SESSION_VARS['def'][$id_cli]['detail_obj_dem_2'] = ${'HTML_GEN_LSB_detail_obj_dem_2' . $id_cli};
    $SESSION_VARS['def'][$id_cli]['mnt_dem'] = arrondiMonnaie(recupMontant($ {'hid_mnt_dem'.$id_cli}),0); // champ caché
    $tot_mnt_dem +=$SESSION_VARS['def'][$id_cli]['mnt_dem'];
    $SESSION_VARS['def'][$id_cli]['prelev_auto'] = isset($ {'prelev_auto'.$id_cli});
    $SESSION_VARS['def'][$id_cli]['cpt_liaison'] = $ {'cpt_liaison'.$id_cli};
    if($SESSION_VARS["is_cpte_remb"] == 't'){
        $SESSION_VARS['def'][$id_cli]['cpt_remb'] = $ {'cpt_remb'.$id_cli};
    }else{
        $SESSION_VARS['def'][$id_cli]['cpt_remb'] = null;
    }

    $ass=recupMontant(round(arrondiMonnaie(recupMontant($ {'hid_mnt_dem'.$id_cli}),0)*$SESSION_VARS['produits_credit'][$id_prod]['prc_assurance'], $global_monnaie_prec));
    $SESSION_VARS['def'][$id_cli]['assurances_cre'] = 't';
    $SESSION_VARS['def'][$id_cli]['mnt_assurance'] = $ass;
    $SESSION_VARS['def'][$id_cli]['prelev_commission'] =  "f";
    if ($SESSION_VARS['produits_credit'][$id_prod]['prc_commission']<=0 || is_null($SESSION_VARS['produits_credit'][$id_prod]['prc_commission'])){ //ticket 763 : prendre en consideration commission fix
      $com=recupMontant(round($SESSION_VARS['produits_credit'][$id_prod]['mnt_commission'], $global_monnaie_prec));
    }
    else{
      $com=recupMontant(round(arrondiMonnaie(recupMontant($ {'mnt_dem'.$id_cli}),0)*$SESSION_VARS['produits_credit'][$id_prod]['prc_commission'], $global_monnaie_prec));
    }
    $SESSION_VARS['def'][$id_cli]['mnt_commission'] = $com;
    $SESSION_VARS['def'][$id_cli]["gar_num"] = recupMontant(round(arrondiMonnaie(recupMontant($ {'mnt_dem'.$id_cli}),0)*$SESSION_VARS['produits_credit'][$id_prod]['prc_gar_num'], $global_monnaie_prec));
    $SESSION_VARS['def'][$id_cli]["gar_mat"] = recupMontant(round(arrondiMonnaie(recupMontant($ {'mnt_dem'.$id_cli}),0)*$SESSION_VARS['produits_credit'][$id_prod]['prc_gar_mat'], $global_monnaie_prec));
    $SESSION_VARS['def'][$id_cli]["gar_tot"] = recupMontant(round(arrondiMonnaie(recupMontant($ {'mnt_dem'.$id_cli}),0)*$SESSION_VARS['produits_credit'][$id_prod]['prc_gar_tot'], $global_monnaie_prec));
    $SESSION_VARS['def'][$id_cli]["gar_num_encours"] = recupMontant(round(arrondiMonnaie(recupMontant($ {'mnt_dem'.$id_cli}),0)*$SESSION_VARS['produits_credit'][$id_prod]['prc_gar_encours'], $global_monnaie_prec));
    $SESSION_VARS['def'][$id_cli]['cpt_prelev_frais'] = $SESSION_VARS['cpt_prelev_frais'];
    if(sizeof($SESSION_VARS['def'][$id_cli]['champsExtras'])> 0 ) {
    	$SESSION_VARS['def'][$id_cli]['champsExtrasValues'] =HTML_Champs_Extras::buildDataChampsEXtrasValues($SESSION_VARS['def'][$id_cli]['champsExtras'],$_POST);

    }
  }

  // Dossiers fictifs si gs
  if ($SESSION_VARS['produits_credit'][$id_prod]["gs_cat"] == 1) {
    foreach($SESSION_VARS['liste_membres'] as $id_cli=>$nom_cli) {
      $SESSION_VARS['fictif'][$id_cli]['gs_cat'] = 1;
      $SESSION_VARS['fictif'][$id_cli]['id_membre'] = $id_cli;
      $SESSION_VARS['fictif'][$id_cli]['id_bailleur'] = $ {'id_bailleur'.$id_cli};
      $SESSION_VARS['fictif'][$id_cli]['obj_dem'] = $ {'obj_dem'.$id_cli};
      if (isDcrDetailObjCreditLsb()) {
        $SESSION_VARS['fictif'][$id_cli]['detail_obj_dem_bis'] = ${'detail_obj_dem_bis' . $id_cli};
      } else {
        $SESSION_VARS['fictif'][$id_cli]['detail_obj_dem'] = $ {'detail_obj_dem'.$id_cli};
      }
      $SESSION_VARS['fictif'][$id_cli]['detail_obj_dem_2'] = ${'HTML_GEN_LSB_detail_obj_dem_2' . $id_cli};
      $SESSION_VARS['fictif'][$id_cli]['mnt_dem'] = arrondiMonnaie(recupMontant($ {'mnt_dem'.$id_cli}),0);
    }
  }
  elseif($SESSION_VARS['produits_credit'][$id_prod]["gs_cat"] == 2) {
    $SESSION_VARS['fictif'][$global_id_client]['gs_cat'] = 2;
    $SESSION_VARS['fictif'][$global_id_client]['id_membre'] = $global_id_client;
    $SESSION_VARS['fictif'][$global_id_client]['mnt_dem'] = $tot_mnt_dem;
    $SESSION_VARS['fictif'][$global_id_client]['id_bailleur'] = NULL;
    $SESSION_VARS['fictif'][$global_id_client]['obj_dem'] = NULL;
    if (isDcrDetailObjCreditLsb()) {
      $SESSION_VARS['fictif'][$global_id_client]['detail_obj_dem_bis'] = NULL;
    } else {
      $SESSION_VARS['fictif'][$global_id_client]['detail_obj_dem'] = NULL;
    }
    $SESSION_VARS['fictif'][$global_id_client]['detail_obj_dem_2'] = NULL;
  }

  $HTML_code = "";

  foreach($SESSION_VARS['def'] as $id_cli => $val) {
    // Calcul des garanties
    $mnt_dem = arrondiMonnaie($val['mnt_dem'],0); // montant demandé
    $duree_mois = $val['duree_mois'];
    $differe_jours = $val['differe_jours'];
    $differe_ech = $val['differe_ech'];
    $tot_mnt_dem += $mnt_dem;
    if ($SESSION_VARS['produits_credit'][$id_prod]["prc_gar_num"] > 0) {
      $SESSION_VARS['def'][$id_cli]["gar_num"] = round(recupMontant($mnt_dem)*$SESSION_VARS['produits_credit'][$id_prod]["prc_gar_num"], $global_monnaie_courante_prec);
    } else {
      $SESSION_VARS['def'][$id_cli]["gar_num"] = $SESSION_VARS['def'][$id_cli]["gar_num_mob"];
    }
    if ($SESSION_VARS['produits_credit'][$id_prod]["prc_gar_mat"] > 0) {
      $SESSION_VARS['def'][$id_cli]["gar_mat"] = round(recupMontant($mnt_dem)*$SESSION_VARS['produits_credit'][$id_prod]["prc_gar_mat"], $global_monnaie_courante_prec);
    } else {
      $SESSION_VARS['def'][$id_cli]["gar_mat"] = $SESSION_VARS['def'][$id_cli]["gar_mat_mob"];
    }
    if ($SESSION_VARS['produits_credit'][$id_prod]["prc_gar_tot"] == $SESSION_VARS['produits_credit'][$id_prod]["prc_gar_num"] + $SESSION_VARS['produits_credit'][$id_prod]["prc_gar_mat"]) {
      $SESSION_VARS["gar_tot"] = round(recupMontant($mnt_dem)*$SESSION_VARS['produits_credit'][$id_prod]["prc_gar_tot"], $global_monnaie_courante_prec);
    } else {
      $SESSION_VARS["gar_tot"] = $SESSION_VARS['def'][$id_cli]["gar_num_mob"] + $SESSION_VARS['def'][$id_cli]["gar_mat_mob"];
    }
    $SESSION_VARS['def'][$id_cli]["gar_num_encours"] = round(recupMontant($mnt_dem)*$SESSION_VARS['produits_credit'][$id_prod]["prc_gar_encours"], $global_monnaie_courante_prec);     $id_cpte = $val['cpt_liaison']; // compte de liaison
    $id_cpte = $val['cpt_liaison']; // compte de liaison
    $SESSION_VARS["id_cpte"] = $id_cpte;

    $echeancier = calcul_echeancier_theorique($id_prod, $mnt_dem, $duree_mois, $differe_jours, $differe_ech, null, 1, null, $SESSION_VARS['produits_credit'][$id_prod]);

    // Appel de l'affichage de l'échéancier
    $garantie= $SESSION_VARS['def'][$id_cli]["gar_num"] + $SESSION_VARS['def'][$id_cli]["gar_mat"] + $SESSION_VARS['def'][$id_cli]["gar_num_encours"];
    $tot_garantie += $garantie;
    $parametre["id_client"] = $id_cli;
    $parametre["lib_date"]= "Date du jour:"; //Libellé de la date théorique de déboursement
    $parametre["index"]= 0;  //Index de début des n° d'echéance
    $parametre["titre"]= "Echéancier théorique de remboursement";
    $parametre["nbre_jour_mois"]= 30; //Durée théorique du mois en nombre de jour
    $parametre["montant"]= recupMontant($mnt_dem);
    $parametre["mnt_commission"]= $SESSION_VARS['def'][$id_cli]['mnt_commission'];
    $parametre["mnt_assurance"]= $SESSION_VARS['def'][$id_cli]['mnt_assurance'];
    $parametre["prelev_commission"]= $SESSION_VARS['def'][$id_cli]['prelev_commission'];
    $parametre["prelev_frais_doss"]= $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prelev_frais_doss'];
    $parametre["mnt_reech"]= 0;
    //$parametre["mnt_des_frais"]= $SESSION_VARS["mnt_des_frais"];
    $parametre["mnt_octr"]= recupMontant($mnt_dem);
    $parametre["garantie"]= $garantie;
    $parametre["duree"]= $duree_mois;
    $parametre["date"]= date("d/m/Y"); // Date théorique de déboursement
    $parametre["id_prod"]= $id_prod;
    $parametre["id_doss"]= -1;// Si id_doss=-1 alors l'echéancier n'est pas sauvegardé (sera enregistré lors de l'approbation)
    $parametre["differe_jours"]= $differe_jours;
    $parametre["differe_ech"]= $differe_ech;
    $parametre["EXIST"]=0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
    $parametre["perio"]= $SESSION_VARS['produits_credit'][$id_prod]['periodicite'];

    $HTML_code .= HTML_echeancier($parametre,$echeancier,null,$SESSION_VARS['produits_credit'][$id_prod]);
  }

  $formEcheancier = new HTML_GEN2();
  $JS="";
  if ( $SESSION_VARS["mnt_frais"] > 0 && $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prelev_frais_doss'] == 1 && $SESSION_VARS['def'][$id_cli]["cre_prelev_frais_doss"] != 't')
    $JS.="\t\tassign('Ado-4');\n";  // Perception frais de dossier lors de la mise en place du crédit
  elseif($SESSION_VARS['produits_credit'][$id_prod]["prc_gar_num"] > 0 or $SESSION_VARS['produits_credit'][$id_prod]["prc_gar_mat"] > 0) {
      $JS .= "\t\tassign('Ado-5');\n";  // Blocage des garanties;
  }
  else
    $JS.="\t\tassign('Ado-6')\n"; // Confirmation

  $formEcheancier->addJS(JSP_BEGIN_CHECK,"test",$JS);

  // les boutons ajoutés
  $formEcheancier->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,2,"retour",_("Précédent"),TYPB_SUBMIT);
  $formEcheancier->addFormButton(1,4,"annuler",_("Annuler"),TYPB_SUBMIT);

  // Propriétés des boutons
  $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Ado-2");
  $formEcheancier->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $formEcheancier->buildHTML();
  echo  $HTML_code;
  echo $formEcheancier->getHTML();
}
/*}}}*/

/*{{{ Ado-4 : Perception des frais de dossier */
else if ($global_nom_ecran == "Ado-4") {

  $id_prod = $SESSION_VARS['id_prod'];

  $formConf = new HTML_GEN2(_("Perception des frais de dossier"));

  $SESSION_VARS['cptes_prelev_frais'] = array();// tableau contenant ass numero cpte de prelevement de frais=>solde

  foreach ($SESSION_VARS['def'] as $id_cli => $val)
  {
    if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2 && $val["cpt_prelev_frais"] == -2) { // si on a choisi les comptes de liaison pour prélever les frais
      $cpte_prelev_frais = $val["cpt_liaison"];
    } else {
      $cpte_prelev_frais = $val['cpt_prelev_frais'];
    }
    if (!(array_key_exists($cpte_prelev_frais, $SESSION_VARS['cptes_prelev_frais']))) {
      $SESSION_VARS['cptes_prelev_frais'][$cpte_prelev_frais] = getSoldeDisponible($cpte_prelev_frais);
    }
  }

  foreach ($SESSION_VARS['def'] as $id_cli => $val)
  {
    if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2 && $val["cpt_prelev_frais"] == -2) { // si on a choisi les comptes de liaison pour prélever les frais
      $cpte_prelev_frais = $val["cpt_liaison"];
    } else {
      $cpte_prelev_frais = $val['cpt_prelev_frais'];
    }
    $soldeB = $SESSION_VARS['cptes_prelev_frais'][$cpte_prelev_frais];

    // Les frais
    $frais_values = getMntFraisDossierProd($SESSION_VARS['id_prod'], NULL, $val['mnt_dem']);

    if (isset($SESSION_VARS["mnt_frais"]) && ($SESSION_VARS["mnt_frais"] != $frais_values['mnt_frais']))
      $mnt_frais = $SESSION_VARS["mnt_frais"];
    else
      $mnt_frais = $frais_values['mnt_frais'];

    //calcul du montant des éventuelles taxes sur les frais
    if (($mnt_frais + $frais_values['mnt_tax_frais']) > $soldeB) {
      $erreur = new HTML_erreur(_("Perception des frais de dossier"));
      $erreur->setMessage(_("Impossible de continuer cette opération, le solde du compte lié au crédit est insuffisant pour la perception des frais de dossier.") . "<br /><ul><li>" . _("Montant des frais") . " : " . afficheMontant($SESSION_VARS["mnt_frais"], true) . "</li><li>" . _("Solde du compte lié") . " : " . afficheMontant($soldeB, true) . "</li></ul>");
      $erreur->addButton(BUTTON_OK, "Gen-11");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      exit();
    } else {
      // Ligne de séparation des frais
      $formConf->addHTMLExtraCode("frais" . $id_cli, "<br /><table align=\"center\" valign=\"middle\" bgcolor=\"" . $colb_tableau . "\"><tr><td><b>" . sprintf(_("Frais du dossier du client N° %s"), $id_cli) . "</b></td></tr></table>\n");

      // Diminuer le solde du compte de prélévement des frais du montant des frais
      $soldeF = $soldeB - ($mnt_frais + $frais_values['mnt_tax_frais']);
      $SESSION_VARS['cptes_prelev_frais'][$cpte_prelev_frais] = $soldeF;

      $formConf->addField("num_complet_cpte" . $id_cli, _("Compte de prélèvement"), TYPC_TXT);
      $formConf->addField("solde" . $id_cli, _("Solde du compte"), TYPC_MNT);
      $formConf->addField("mnt" . $id_cli, _("Montant des frais"), TYPC_MNT);
      if ($frais_values['mnt_tax_frais'] > 0) {
        $formConf->addField("mnt_tax_frais" . $id_cli, _("Montant tva sur frais"), TYPC_MNT);
        $formConf->setFieldProperties("mnt_tax_frais" . $id_cli, FIELDP_DEFAULT, $frais_values['mnt_tax_frais']);
        $formConf->setFieldProperties("mnt_tax_frais" . $id_cli, FIELDP_IS_LABEL, true);
      }
      $formConf->addField("soldeF" . $id_cli, _("Nouveau solde"), TYPC_MNT);

      // Récupération des infos sur le compte de liaison
      if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2 && $val['cpt_prelev_frais'] == -2) {
        $compte_prelev_frais = getAccountDatas($val['cpt_liaison']);
        $SESSION_VARS['cpt_prelev_frais' . $id_cli] = $val['cpt_liaison'];
      } else {
        $compte_prelev_frais = getAccountDatas($val['cpt_prelev_frais']);
        $SESSION_VARS['cpt_prelev_frais' . $id_cli] = $val['cpt_prelev_frais'];
      }
      // Les propriétés des champs
      $formConf->setFieldProperties("num_complet_cpte" . $id_cli, FIELDP_IS_REQUIRED, false);
      $formConf->setFieldProperties("num_complet_cpte" . $id_cli, FIELDP_DEFAULT, $compte_prelev_frais['num_complet_cpte']);
      $formConf->setFieldProperties("num_complet_cpte" . $id_cli, FIELDP_IS_LABEL, true);
      $formConf->setFieldProperties("solde" . $id_cli, FIELDP_IS_REQUIRED, false);
      $formConf->setFieldProperties("solde" . $id_cli, FIELDP_DEFAULT, $soldeB);
      $formConf->setFieldProperties("solde" . $id_cli, FIELDP_IS_LABEL, true);
      $formConf->setFieldProperties("mnt" . $id_cli, FIELDP_DEFAULT, $mnt_frais);
      $formConf->setFieldProperties("mnt" . $id_cli, FIELDP_IS_LABEL, true);
      $formConf->setFieldProperties("soldeF" . $id_cli, FIELDP_DEFAULT, $soldeF);
      $formConf->setFieldProperties("soldeF" . $id_cli, FIELDP_IS_LABEL, true);
    }
  }
  // les boutons ajoutés
  $formConf->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $formConf->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);

  // Propriétés des boutons
  if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_num"] > 0 or $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_mat"] > 0)
    $formConf->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ado-5");
  else
    $formConf->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ado-6");

  $formConf->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");

  $formConf->buildHTML();
  echo $formConf->getHTML();
}
/*}}}*/

/*{{{ Ado-5 : Blocage des garanties */
else if ($global_nom_ecran == "Ado-5") {
  $formConf = new HTML_GEN2(_("Blocage des garanties"));
  $msg = ''; // message en cas d'erreur
  $order = array();

  $prelev_garantie = ($SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['prelev_garanti'] == '1')?true:false;
  if($SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['gs_cat'] == 2)
      $SESSION_VARS["id_cli_prelev"][$global_id_client] = $global_id_client;

//  $SESSION_VARS["id_cli_prelev"] = array_unique($SESSION_VARS["id_cli_prelev"]); //remove duplicates value
//  $SESSION_VARS["id_cli_prelev"] = array_filter($SESSION_VARS["id_cli_prelev"], function($value){
//      return !empty($value);
//  });

    /* Bloquer chaque garantie numéraire sur son compte de prélèvement */
  foreach($SESSION_VARS['def'] as $id_cli=>$val ) {
    // Vérifier que les garanties mobilisées par le client sont suffisantes
    if ($SESSION_VARS['def'][$id_cli]['gar_num'] > $SESSION_VARS['def'][$id_cli]['gar_num_mob'])
      $msg = sprintf(_("Impossible de continuer cette opération, les garanties numéraires du client %s sont insuffisantes."),$id_cli)."<BR><UL><LI>"._("Garantie attendue").": ".afficheMontant($SESSION_VARS['def'][$id_cli]['gar_num'], true)."</LI><LI>"._("Garantie mobilisée").": ".afficheMontant($SESSION_VARS['def'][$id_cli]['gar_num_mob'], true)."</LI></UL>";

    if ($SESSION_VARS['def'][$id_cli]['gar_mat'] > $SESSION_VARS['def'][$id_cli]['gar_mat_mob'])
      $msg = sprintf(_("Impossible de continuer cette opération, les garanties matérielles du client %s sont insuffisantes."),$id_cli)."<BR><UL><LI>"._("Garantie attendue").": ".afficheMontant($SESSION_VARS['def'][$id_cli]['gar_mat'], true)."</LI><LI>"._("Garantie mobilisée").": ".afficheMontant($SESSION_VARS['def'][$id_cli]['gar_mat_mob'], true)."</LI></UL>";

    $gar_tot = $SESSION_VARS['def'][$id_cli]['gar_num_mob'] + $SESSION_VARS['def'][$id_cli]['gar_mat_mob'];
    if ($SESSION_VARS['def'][$id_cli]['gar_tot'] > $gar_tot)
      $msg = sprintf(_("Impossible de continuer cette opération, le total des garanties du client %s est insuffisant."),$id_cli)."<BR><UL><LI>"._("Garantie attendue").": ".afficheMontant($SESSION_VARS['def'][$id_cli]['gar_tot'], true)."</LI><LI>"._("Garantie mobilisée").": ".afficheMontant($gar_tot, true)."</LI></UL>";

    if (is_array($SESSION_VARS['def'][$id_cli]['DATA_GAR']))
      foreach($SESSION_VARS['def'][$id_cli]['DATA_GAR'] as $key=>$value ) {
      $mnt_gar_mob = recupMontant($value['valeur']);

      if ($value['type'] == 1)
        // Garanties numéraires
      {
        $cpt_prelev_gar = $value['descr_ou_compte'];
        $cpteInfo = getAccountDatas($cpt_prelev_gar);
        $soldeB = getSoldeDisponible($cpt_prelev_gar);
        // déterminer les cptes de prélèvement de frais
        if($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2 && $val["cpt_prelev_frais"] == -2) { // si on a choisi les comptes de liaison pour prélever les frais
					$cpte_prelev_frais=$val["cpt_liaison"];
				}
				else {
					$cpte_prelev_frais=$val['cpt_prelev_frais'];
				}
        /* Si le cpte de prélèvement des garanties est aussi le compte de prélèvement des frais de dossier,
         * alors   le solde du compte est  $SESSION_VARS['cptes_prelev_frais'][$cpte_prelev_frais]*/
        if ($cpte_prelev_frais == $cpt_prelev_gar && isset($SESSION_VARS['cptes_prelev_frais']))
          $soldeB =$SESSION_VARS['cptes_prelev_frais'][$cpte_prelev_frais];

        $client_temp = $SESSION_VARS['liste_membres'];

        if($SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['gs_cat'] == 2)
            $client_temp[$global_id_client] = null;
        $client_temp = array_keys($client_temp);

        if (($mnt_gar_mob > $soldeB) && (!in_array($value["id_cli_prelev"], $client_temp) || $prelev_garantie)) {
            $msg = sprintf(_("Impossible de continuer, le solde du compte de prélèvement des garanties pour le client %s est insuffisant."), $id_cli) . "<BR><UL><LI>" . _("Montant de la garantie") . " : " . afficheMontant($mnt_gar_mob, true) . "</LI><LI>" . _("Solde du compte de prélèvement") . " : " . afficheMontant($soldeB, true) . "</LI><LI>" . _("Numéro de compte") . " : " . $cpteInfo['num_complet_cpte'] . "</LI></UL>";
        }

        /* Ligne de séparation des garanties numéraires */
        $formConf->addHTMLExtraCode("gar".$id_cli."_".$key,"<Table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Garanties numéraires")."</b></td></tr></Table>\n");

        /* Affichage */
        $formConf->addField("intitule_compte".$id_cli."_".$key,_("Intitule du compte"),TYPC_TXT);
        $formConf->addField("num_complet_cpte".$id_cli."_".$key,_("Numéro du compte"),TYPC_TXT);
        $formConf->addField("solde".$id_cli."_".$key,_("Solde du compte"),TYPC_MNT);
        $formConf->addField("mnt_gar".$id_cli."_".$key,_("Montant des garanties"),TYPC_MNT);

        /* Ordre d'affichage des champs */
        array_push($order,"gar".$id_cli."_".$key);
        array_push($order,"intitule_compte".$id_cli."_".$key);
        array_push($order,"num_complet_cpte".$id_cli."_".$key);
        array_push($order,"solde".$id_cli."_".$key);
        array_push($order,"mnt_gar".$id_cli."_".$key);

        /* Remplissage du formulaire */
        $formConf->setFieldProperties("intitule_compte".$id_cli."_".$key, FIELDP_DEFAULT, $cpteInfo["intitule_compte"]);
        $formConf->setFieldProperties("num_complet_cpte".$id_cli."_".$key, FIELDP_DEFAULT, $cpteInfo["num_complet_cpte"]);
        $formConf->setFieldProperties("solde".$id_cli."_".$key,FIELDP_DEFAULT,$soldeB);
        $formConf->setFieldProperties("mnt_gar".$id_cli."_".$key,FIELDP_DEFAULT, $mnt_gar_mob);

        /* Griser les champs */
        $formConf->setFieldProperties("mnt_gar".$id_cli."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("solde".$id_cli."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("num_complet_cpte".$id_cli."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("intitule_compte".$id_cli."_".$key,FIELDP_IS_LABEL,true);

      }
      elseif($value['type'] == 2)
      // Garanties matérielles
      {
        /* Ligne de séparation des garanties matérielles */
        $formConf->addHTMLExtraCode("gar".$id_cli."_".$key,"<Table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Garanties matérielles")."</b></td></tr></Table>\n");

        $formConf->addField("libel_gar".$id_cli."_".$key,_("Libellé des garanties"),TYPC_TXT);
        $formConf->setFieldProperties("libel_gar".$id_cli."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("libel_gar".$id_cli."_".$key, FIELDP_DEFAULT, $value['descr_ou_compte']);

        $formConf->addField("mnt_gar".$id_cli."_".$key,_("Valeur des garanties"),TYPC_MNT);
        $formConf->setFieldProperties("mnt_gar".$id_cli."_".$key,FIELDP_IS_LABEL,true);
        $formConf->setFieldProperties("mnt_gar".$id_cli."_".$key, FIELDP_DEFAULT, $mnt_gar_mob);

        /* Ordre d'affichage des champs */
        array_push($order,"gar".$id_cli."_".$key);
        array_push($order,"libel_gar".$id_cli."_".$key);
        array_push($order,"mnt_gar".$id_cli."_".$key);
      }
    }
  } /* Fin foreach */

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
  $formConf->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ado-6");
  $formConf->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");

  //$order = array("intitule_compte","num_complet_cpte","solde","mnt");
  $formConf->setOrder(NULL,$order);
  $formConf->buildHTML();
  echo $formConf->getHTML();
}
/*}}}*/

/*{{{ Ado-6 : Confirmation création du DCR */
else if ($global_nom_ecran == "Ado-6") {
	unset($SESSION_VARS['cptes_prelev_frais']);// on plus besoin de ce tableau
    $prelev_garantie = ($SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['prelev_garanti'] == '1')?true:false;

  // Les données de prélevement des frais de dossier
  foreach($SESSION_VARS['def'] as $id_cli=>$val) {
	if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2 && $val["cpt_prelev_frais"] == -2) { // si on a choisi les comptes de liaison pour prélever les frais
	  $FRAIS[$id_cli]["id_cpte_cli"] = $val["cpt_liaison"];
	  $FRAIS[$id_cli]["mnt_frais"] =  $val['mnt_dem'] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_frais'];//$SESSION_VARS["mnt_frais"];
	  $SESSION_VARS['cpt_prelev_frais'.$id_cli]= $val["cpt_liaison"];
	} else if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2 && $val['cpt_prelev_frais'] != -2) {
	    $FRAIS[$id_cli]["id_cpte_cli"] = $val["cpt_prelev_frais"];
	    $FRAIS[$id_cli]["mnt_frais"] =  $val['mnt_dem'] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_frais'];//$SESSION_VARS["mnt_frais"] ; //Montant des frais de dossiers
	} else {
		$FRAIS[$id_cli]["id_cpte_cli"] = $val["cpt_prelev_frais"];
	    $FRAIS[$id_cli]["mnt_frais"] =  $SESSION_VARS["mnt_frais"]; //Montant des frais
	}
    $fraisCalcule[$id_cli]['frais'] = $FRAIS[$id_cli]["mnt_frais"]; //ticket 788 frais calculé
    $FRAIS[$id_cli]["id_agence"] = $global_id_agence;
    if($SESSION_VARS['cpt_prelev_frais']!=-2){
    $SESSION_VARS['cpt_prelev_frais'.$id_cli]=$SESSION_VARS['cpt_prelev_frais'];
    }
    if(isset($SESSION_VARS['cre_prelev_frais_doss'])){
    $SESSION_VARS['cre_prelev_frais_doss'.$id_cli]=$SESSION_VARS['cre_prelev_frais_doss'];
    }
  }
  $DATA_GAR = array();
  if ($SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['gs_cat'] == 2) { //GS avec dossiers multiples
    $next_id_fictif = getNextDossierFictifID();
    $SESSION_VARS['fictif'][$global_id_client]['id'] = $next_id_fictif;
  } else
    $next_id_fictif = NULL;

  // Remplissage de $DATA avec les données postées au serveur.
  foreach($SESSION_VARS['def'] as $id_cli=>$val) {
    $DATA[$id_cli]["id_dcr_grp_sol"] = $next_id_fictif;
    $DATA[$id_cli]["id_prod"] = $SESSION_VARS["id_prod"]; //Identifiant du produit

    $DATA[$id_cli]["is_extended"] = 'f';
    if (isset($SESSION_VARS["is_extended"]) && $SESSION_VARS["is_extended"]=='t') {
      $DATA[$id_cli]["is_extended"] = 't';
      $DATA[$id_cli]["dcr_extended"] = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']];
    }
    $DATA[$id_cli]["differe_jours"] = $val["differe_jours"]; //Différe en jours
    $DATA[$id_cli]["differe_ech"] = $val["differe_ech"]; //Différe en échéances
    $DATA[$id_cli]["date_dem"] = $val["date_dem"]; //Date de demande
    $DATA[$id_cli]["cre_date_debloc"] = $val["cre_date_debloc"]; //Date de déboursement
    $DATA[$id_cli]["etat"] = 1; // Etat en attente de décision
    if ($val["id_agent_gest"] == '0') // Si le gestionnaire est 0, il ne faut rien mettre dans la BD
      $DATA[$id_cli]["id_agent_gest"] = '';
    else
      $DATA[$id_cli]["id_agent_gest"] = $val["id_agent_gest"];

    $DATA[$id_cli]["duree_mois"] = $val["duree_mois"];
    $DATA[$id_cli]["delai_grac"]= $val["delai_grac"];
    $DATA[$id_cli]["nb_jr_bloq_cre_avant_ech"]= $val["nb_jr_bloq_cre_avant_ech"];
    $DATA[$id_cli]["prelev_commission"]= $val["prelev_commission"];
    //if ($val["assurances_cre"] > 0)
      $DATA[$id_cli]["assurances_cre"] = 't';
    $DATA[$id_cli]["gar_num"] = $val["gar_num"];
    $DATA[$id_cli]["gar_num_encours"] = $val["gar_num_encours"];
    $DATA[$id_cli]["gar_mat"] = $val["gar_mat"];
    $DATA[$id_cli]["gar_tot"] = $val["gar_tot"];
    if (($val["duree_mois"] >= $adsys["adsys_termes_credit"][1]['mois_min'])
        && ($val["duree_mois"] <= $adsys["adsys_termes_credit"][1]['mois_max']))
      $DATA[$id_cli]["terme"] =1; // Court terme

    if (($val["duree_mois"] >= $adsys["adsys_termes_credit"][2]['mois_min'])
        && ($val["duree_mois"] <= $adsys["adsys_termes_credit"][2]['mois_max']))
      $DATA[$id_cli]["terme"] =2; // Moyen terme

    if ($val["duree_mois"] >= $adsys["adsys_termes_credit"][3]['mois_min'])
      $DATA[$id_cli]["terme"] =3; // Long terme

    $DATA[$id_cli]["id_client"] = $id_cli;
    $DATA[$id_cli]["cpt_liaison"] = $val["cpt_liaison"];
    $DATA[$id_cli]["cpt_remb"] = $val["cpt_remb"];
    $DATA[$id_cli]["mnt_dem"] = $val["mnt_dem"].''; //Montant demandé
    $DATA[$id_cli]["id_bailleur"] = $val["id_bailleur"];
    $DATA[$id_cli]["obj_dem"] = $val["obj_dem"];
    if (isDcrDetailObjCreditLsb()) {
      $DATA[$id_cli]["detail_obj_dem_bis"] = $val["detail_obj_dem_bis"];
    } else {
      $DATA[$id_cli]["detail_obj_dem"] = $val["detail_obj_dem"];
    }
    $DATA[$id_cli]["detail_obj_dem_2"] = $val["detail_obj_dem_2"];
    $DATA[$id_cli]["prelev_auto"] = $val["prelev_auto"];

    // S'il faut prelever les frais
    if($SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['prelev_frais_doss'] != 0) {
      $DATA[$id_cli]["mnt_frais_doss"] = $fraisCalcule[$id_cli]['frais']; //ticket 788 frais calculé //$SESSION_VARS["mnt_frais"];
    }

    if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_commission']<=0 || is_null($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_commission'])){ // Comission fix / Pourcentage | ticket 763 + 788
      $DATA[$id_cli]["mnt_commission"] = $val["mnt_commission"];
    }
    $DATA[$id_cli]["cre_nbre_reech"] = '0';
    $DATA[$id_cli]["num_cre"] = getNumCredit($id_cli) + 1;
    $DATA[$id_cli]["suspension_pen"] = 'f';
    $DATA[$id_cli]['gs_cat'] = $SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['gs_cat'];
    $DATA[$id_cli]['cpt_prelev_frais'] = $SESSION_VARS['cpt_prelev_frais'.$id_cli];
    if(isset($SESSION_VARS['cre_prelev_frais_doss'.$id_cli])){
		$DATA[$id_cli]['cre_prelev_frais_doss'] = $SESSION_VARS['cre_prelev_frais_doss'.$id_cli];
    }
    // prépare tableau garantie à donner à insereDossier et Vérif état des garanties pour l'approbation
    $gar_pretes = true;
    $pos = 0;
    if (is_array($SESSION_VARS['def'][$id_cli]['DATA_GAR'])) {
      foreach($SESSION_VARS['def'][$id_cli]['DATA_GAR'] as $cle=>$valeur) {
        $DATA_GAR[] = $valeur;
        if ($valeur['etat'] !=2  and $valeur['etat'] != 3 and $valeur['benef'] == $id_cli)
          $gar_pretes = false;
//        if(!$prelev_garantie)
//             $DATA_GAR[$pos]['etat'] = 6;
//        ($pos < count($DATA_GAR))?++$pos:($pos = 0);
      }
    }

    // Vérifier que le produit permet d'approuver dés la mise en place du dossier
    if ($SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['approbation_obli'] !='t') {
      if ($gar_pretes == true) { // On peut approuver le dossier
        $DATA[$id_cli]["cre_date_approb"] = date("d/m/Y"); // Date d'approbation
        $DATA[$id_cli]["etat"] = 2;  // Etat accepté
        $DATA[$id_cli]["date_etat"] = date("d/m/Y") ; // Date de passage à l'état accepté
        $DATA[$id_cli]["cre_date_etat"] = date("d/m/Y");
        $DATA[$id_cli]["cre_mnt_octr"]=$DATA[$id_cli]["mnt_dem"];
      }
    }
    //gestion champs supplémentaire
    $DATA[$id_cli]['champsExtras']=$SESSION_VARS['def'][$id_cli]['champsExtrasValues'];
  }
  // appel DB : Insertion du dossier de crédit
  $myErr = insereDossier ($DATA, $FRAIS, $DATA_GAR, $id_utilisateur, $SESSION_VARS['fictif'],105, $prelev_garantie);
  if ($myErr->errCode == NO_ERR) {
    $msg = new HTML_message(_("Confirmation ajout de dossier de crédit"));
    $message .= _("Le crédit a été mis en place avec succès !");
    //$message .= "<br /><br />"._("Numéro de transaction")." : <B><code>".sprintf("%09d", $myErr->param)."</code></B>";
    $msg->setMessage($message);
    $msg->addButton(BUTTON_OK,"Gen-11");
    $msg->buildHTML();
    echo $msg->HTML_code;

  } else {
    $html_err = new HTML_erreur(_("Echec de la création de dossier de crédit."));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]."<br />"._("Paramètre")." : ".$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-3');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
/*}}}*/

/*{{{ Ado-7 : Gestion des garanties mobilisées */
else if ($global_nom_ecran == "Ado-7") {

  /* Mémorisation des données de l'écran précédent */
  $num_client = $global_id_client;
  $id_prod = $SESSION_VARS['id_prod'];

  // si l'utilisateur a le droit de modifier les frais
  if (check_access(299) && isset($mnt_frais))
    $SESSION_VARS["mnt_frais"] = recupMontant($mnt_frais);

  if (isset($cpt_prelev_frais))
    $SESSION_VARS['cpt_prelev_frais'] = $cpt_prelev_frais;

  if (strstr($global_nom_ecran_prec,"Ado-2")) {
    foreach($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
      // Sauve les valeurs renseignées pour les réafficher en cas de retour arrière
      $SESSION_VARS['def'][$id_cli]['id_prod'] = $id_prod;
      $SESSION_VARS['def'][$id_cli]['id_bailleur'] = $ {'id_bailleur'.$id_cli};
      $SESSION_VARS['def'][$id_cli]['obj_dem'] = $ {'obj_dem'.$id_cli};
      if (isDcrDetailObjCreditLsb()) {
        $SESSION_VARS['def'][$id_cli]['detail_obj_dem_bis'] = ${'detail_obj_dem_bis' . $id_cli};
      } else {
        $SESSION_VARS['def'][$id_cli]['detail_obj_dem'] = $ {'detail_obj_dem'.$id_cli};
      }
      $SESSION_VARS['def'][$id_cli]['detail_obj_dem_2'] = ${'detail_obj_dem_2' . $id_cli};
      $SESSION_VARS['def'][$id_cli]['date_dem'] = $ {'date_dem'.$id_cli};
      $SESSION_VARS['def'][$id_cli]['cre_date_debloc'] = $ {'cre_date_debloc'.$id_cli};
      $SESSION_VARS['def'][$id_cli]['mnt_dem'] = recupMontant($ {'hid_mnt_dem'.$id_cli}); // Champ caché
      $SESSION_VARS['def'][$id_cli]['duree_mois'] = $ {'duree_mois'.$id_cli};
      $SESSION_VARS['def'][$id_cli]['differe_jours'] = $ {'differe_jours'.$id_cli};
      $SESSION_VARS['def'][$id_cli]['differe_ech'] = $ {'differe_ech'.$id_cli};
      $SESSION_VARS['def'][$id_cli]['id_agent_gest'] = ($ {'id_agent_gest'.$id_cli} > 0 ? $ {'id_agent_gest'.$id_cli} : 0);
      $SESSION_VARS['def'][$id_cli]['delai_grac'] = $ {'delai_grac'.$id_cli};
      $SESSION_VARS['def'][$id_cli]['nb_jr_bloq_cre_avant_ech'] = $ {'nb_jr_bloq_cre_avant_ech'.$id_cli};
      $SESSION_VARS['def'][$id_cli]['prelev_auto'] = isset($ {'prelev_auto'.$id_cli});
      $SESSION_VARS['def'][$id_cli]['prelev_commission'] = isset($ {'prelev_commission'.$id_cli});
      $SESSION_VARS['def'][$id_cli]['cpt_liaison'] = $ {'cpt_liaison'.$id_cli};
      if($SESSION_VARS["is_cpte_remb"] == "t"){
          $SESSION_VARS['def'][$id_cli]['cpt_remb'] = $ {'cpt_remb'.$id_cli};
      }else{
          $SESSION_VARS['def'][$id_cli]['cpt_remb'] = null;
      }

      $ass =recupMontant(round(recupMontant($ {'mnt_dem'.$id_cli})*$SESSION_VARS['produits_credit'][$id_prod]['prc_assurance'],$global_monnaie_prec));
      $SESSION_VARS['def'][$id_cli]['assurances_cre'] = $ass;
      $com=recupMontant(round(recupMontant($ {'mnt_dem'.$id_cli})*$SESSION_VARS['produits_credit'][$id_prod]['prc_commission'],$global_monnaie_prec));
      $SESSION_VARS['def'][$id_cli]['mnt_commission'] = $com;
      $SESSION_VARS['def'][$id_cli]["gar_num"] = recupMontant(round(recupMontant($ {'mnt_dem'.$id_cli})*$SESSION_VARS['produits_credit'][$id_prod]['prc_gar_num'], $global_monnaie_prec));
      $SESSION_VARS['def'][$id_cli]["gar_mat"] = recupMontant(round(recupMontant($ {'mnt_dem'.$id_cli})*$SESSION_VARS['produits_credit'][$id_prod]['prc_gar_mat'], $global_monnaie_prec));
      $SESSION_VARS['def'][$id_cli]["gar_tot"] = recupMontant(round(recupMontant($ {'mnt_dem'.$id_cli})*$SESSION_VARS['produits_credit'][$id_prod]['prc_gar_tot'], $global_monnaie_prec));

    } // fin sauvegarde des dossiers reels

    // Dossiers fictifs si gs
    if ($SESSION_VARS['produits_credit'][$id_prod]["gs_cat"] == 1) { // GS avec dossier reel unique et plusieurs dossiers fictifs
      foreach($SESSION_VARS['liste_membres'] as $id_cli=>$nom_cli) {
        $SESSION_VARS['fictif'][$id_cli]['gs_cat'] = 1;
        $SESSION_VARS['fictif'][$id_cli]['id_membre'] = $id_cli;
        $SESSION_VARS['fictif'][$id_cli]['id_bailleur'] = $ {'id_bailleur'.$id_cli};
        $SESSION_VARS['fictif'][$id_cli]['obj_dem'] = $ {'obj_dem'.$id_cli};
        if (isDcrDetailObjCreditLsb()) {
          $SESSION_VARS['fictif'][$id_cli]['detail_obj_dem_bis'] = ${'detail_obj_dem_bis' . $id_cli};
        } else {
          $SESSION_VARS['fictif'][$id_cli]['detail_obj_dem'] = $ {'detail_obj_dem'.$id_cli};
        }
        $SESSION_VARS['fictif'][$id_cli]['detail_obj_dem_2'] = ${'detail_obj_dem_2' . $id_cli};
        $SESSION_VARS['fictif'][$id_cli]['mnt_dem'] = recupMontant($ {'mnt_dem'.$id_cli});
      }
    }
    elseif($SESSION_VARS['produits_credit'][$id_prod]["gs_cat"] == 2) { // GS avec plusieurs dosseirs reel et un dossier fictif
      $SESSION_VARS['fictif'][$global_id_client]['gs_cat'] = 2;
      $SESSION_VARS['fictif'][$global_id_client]['id_membre'] = $global_id_client;
      $SESSION_VARS['fictif'][$global_id_client]['mnt_dem'] = $tot_mnt_dem;
    }
  } // fin si ecran Ado-2

  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Mobilisation des garanties"));

  /* Creation d'un tableau contenant toutes les garanties d'un dossier de crédit */
  $xtHTML = "<br><TABLE align=\"center\">";

  /* En-tête tableau :  Type | Description/compte de prélèvement | Valeur | Mod | Sup  */
  $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
  if ($SESSION_VARS['gs_cat'] ==2 )
    $xtHTML .= "<td><b>"._("Bénéficiaire")."</b></td>";

  $xtHTML .= "<td><b>"._("Type")."</b></td><td><b>"._("Description/compte de prélèvement")." </b></td><td><b>"._("Valeur")."</b></td><td><b>"._("Etat")."</b></td><td>&nbsp</td><td>&nbsp</td></tr>";

  /* Contenu du tableau */
  foreach($SESSION_VARS['clients_dcr'] as $id_cli=>$nom)
  if (is_array($SESSION_VARS['def'][$id_cli]['DATA_GAR']))
    foreach($SESSION_VARS['def'][$id_cli]['DATA_GAR'] as $key=>$value ) {
    if ($value['type'] != '') {
      $origine = $value['descr_ou_compte'];
      /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
      if ($value['type'] ==1 and $value['descr_ou_compte'] != '') {
        $CPT_PRELEV_GAR = getAccountDatas($value['descr_ou_compte']);
        $origine  = $CPT_PRELEV_GAR["num_complet_cpte"]." ".$CPT_PRELEV_GAR['intitule_compte'] ;
      }

      $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
      //Membre du groupe solidaire avec plusieurs dossiers reels
      if ($SESSION_VARS['gs_cat'] ==2 )
        $xtHTML .= "<td>".$value['benef']." ". getClientName($value['benef'])."</td>";
      $xtHTML .= "<td>".adb_gettext($adsys["adsys_type_garantie"][$value['type']])."</td>";
      $xtHTML .= "<td>".$origine."</td>";
      $xtHTML .= "<td>".$value['valeur']."</td>";
      $xtHTML .= "<td>".adb_gettext($adsys["adsys_etat_gar"][$value['etat']])."</td>";
      $xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ado-10&benef=".$id_cli."&num_gar=".$key."\">"._("Mod")."</A></td>";
      $xtHTML .= "<td><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ado-11&benef=".$id_cli."&num_gar=".$key."\">"._("Sup")."</A></td></tr>";
    }
  }

  $xtHTML .= "</table><br><br>";
  $Myform->addHTMLExtraCode ("garanties", $xtHTML);
  $Myform->addFormButton(1,1,"ajout_gar", _("Nouvelle garantie"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ado-2');
  $Myform->setFormButtonProperties("ajout_gar", BUTP_PROCHAIN_ECRAN, 'Ado-8');

  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/

/*{{{ Ado-8 : Ajout de garantie */
else if ($global_nom_ecran == "Ado-8") {

  /* Devise du produit de crédit */
  setMonnaieCourante($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]);

  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Ajout d'une garantie"));

  $Myform->addField ("benef", _("Bénéficiaire"), TYPC_LSB);
  $Myform->setFieldProperties("benef", FIELDP_ADD_CHOICES, $SESSION_VARS['clients_dcr']);
  $Myform->setFieldProperties("benef",FIELDP_IS_REQUIRED, true);

  /* Ajout de certains champs de ad_gar */
  $exclude = array("devise_vente","gar_num_id_cpte_prelev","gar_mat_id_bien","gar_num_id_cpte_nantie","id_doss","id_gar","etat_gar");
  $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
  $Myform->setFieldProperties("type_gar", FIELDP_JS_EVENT, array("OnChange"=>"check_type_gar()"));

  /* Etat de la garantie */
  $etats_gar = array();
  $etats_gar[1] = adb_gettext($adsys["adsys_etat_gar"][1]);
  $etats_gar[2] = adb_gettext($adsys["adsys_etat_gar"][2]);
  $Myform->addField ("etat_gar", _("Etat de la garantie"), TYPC_LSB);
  $Myform->setFieldProperties("etat_gar", FIELDP_ADD_CHOICES, $etats_gar);
  $Myform->setFieldProperties("etat_gar",FIELDP_IS_REQUIRED, true);

  /* Numéro du compte de prélèvement si garantie numéraire */
  $Myform->addField ("gar_num_id_cpte_prelev", _("Compte de prélèvement"), TYPC_TXT);
  $Myform->addLink("gar_num_id_cpte_prelev","rech_cpt",_("Rechercher"), "#");
  $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_REQUIRED, true);
  $Myform->setLinkProperties("rech_cpt",LINKP_JS_EVENT,array("OnClick"=>"open_compte();return false;"));
  $Myform->addHiddenType("num_id_cpte_prelev", "");
  $Myform->addHiddenType("id_cli_prelev", "");
  $Myform->addHiddenType("devise_vente", $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]);

  /* Libellé de la garantie matérielle */
  $Myform->addField ("libel_gar_mat", _("Description du matériel"), TYPC_TXT);
  $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_REQUIRED, true);

  /* Types de bien */
  $types_biens = getTypesBiens();
  $Myform->addField ("type_bien", _("Type de bien"), TYPC_LSB);
  $Myform->setFieldProperties("type_bien", FIELDP_ADD_CHOICES, $types_biens);
  $Myform->setFieldProperties("type_bien",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("type_bien",FIELDP_IS_REQUIRED, true);

  /* Numéro du client si garantie du matérielle */
  $Myform->addField ("num_client", _("Client garant du matériel"), TYPC_TXT);
  $Myform->addHiddenType("num_client_rel");

  $Myform->addLink("num_client", "rech_client", _("Rechercher"), "#");
  $Myform->setLinkProperties("rech_client",LINKP_JS_EVENT,array("OnClick"=>"rech_client();"));
  $Myform->setFieldProperties("num_client",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("num_client",FIELDP_IS_REQUIRED, true);

  /* Pièce justificative si garanties matérielles */
  $Myform->addField ("piece_just", _("Pièce justificative"), TYPC_TXT);
  $Myform->setFieldProperties("piece_just",FIELDP_IS_LABEL,true);

  /* Remarque si garanties matérilles */
  $Myform->addField ("remarq", _("Remarque"), TYPC_TXT);
  $Myform->setFieldProperties("remarq",FIELDP_IS_LABEL,true);

  /* Traitement à effectuer : ajout, modification ou suppression de garantie */
  $Myform->addHiddenType("traitement", "ajout");

  /* Order d'affichage des champs */
  $order = array ("benef","type_gar","gar_num_id_cpte_prelev","libel_gar_mat","type_bien","num_client","piece_just","remarq","montant_vente","etat_gar");
  $Myform->setOrder(NULL, $order);

  $Myform->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ado-7');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Ado-9');

  /* Contrôle des champs à renseigner selon le type de garantie  */
  $JS_valide ="";
  $JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1 && document.ADForm.gar_num_id_cpte_prelev.value =='')";
  $JS_valide .="\n\t{msg+='"._("Le compte de prélèvement des garanties doit être renseigné")."'; ADFormValid = false;}";

  $JS_valide .= "\n\tif( (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.libel_gar_mat.value =='') ||
                (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.HTML_GEN_LSB_type_bien.value == 0) ||
                (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.num_client_rel.value == '')
                )";
  $JS_valide .="\n\t{msg+='"._("Le libellé, le type du matériel et le client garant doivent être renseignés")."'; ADFormValid = false;}";

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
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\tdocument.ADForm.num_client_rel.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = true;";
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
  $JS_active .="\n\tdocument.ADForm.num_client.value = '';";
  $JS_active .="\n\tdocument.ADForm.num_client_rel.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.value = '';";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = true;";
  $JS_active .="\n\tdocument.ADForm.remarq.value = '';";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = true;";
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
  $JS_prelev .="\n{url = '".$http_prefix."/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=gar_num_id_cpte_prelev&id_cpt_dest=num_id_cpte_prelev&devise=".$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]."';\n";
  $JS_prelev .="\t\tgarant = OpenBrwXY(url, 'Compte de prélèvement', 400, 500);\n";
  $JS_prelev .="\t}\n";
  $JS_prelev .="\telse return false;\n";
  $JS_prelev .="}\n";

  /* JS : recherche du client si garantie matérielle */
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

/*{{{ Ado-9 : Confirmation ajout, modification ou suppression de garantie */
else if ($global_nom_ecran == "Ado-9") {
  $Myform = new HTML_message(_("Confirmation"));
  $prelev_garantie = ($SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['prelev_garanti'] == '1')?true:false;

  if ($traitement == "ajout" or $traitement == "modification") {
    if ($type_gar == 2){
      /* Vérification de l'état du client garant*/
     $etat_client_rel = getEtatClient($num_client_rel);
     if($etat_client_rel != 2 ){
   	 	$html_err = new HTML_erreur(_("Client garant inactif "));
      	$html_err->setMessage(sprintf(_("Veuillez choisir un client actif comme garant")));
      	$html_err->addButton("BUTTON_OK", 'Ado-8');
      	$html_err->buildHTML();
      	echo $html_err->HTML_code;
      	die();
     }
    }

//    $SESSION_VARS['id_cli_prelev'][$benef] = $id_cli_prelev;
    /* Ajout ou modification de garantie */
    if ($traitement == "ajout") {
      $Myform->setMessage(_("La garantie a été ajoutée avec succès"));
      $num_gar = 1 + count($SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR']);
    } else {
      $HTML_GEN_LSB_benef=$SESSION_VARS["beneficaire"];
      unset($SESSION_VARS["beneficaire"]);
      $Myform->setMessage(_("La garantie a été modifiée avec succès"));
    }

    $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type'] = $type_gar ;
    $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['id_gar'] = NULL ;

    if ($type_gar == 1) {
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['benef'] = $HTML_GEN_LSB_benef;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] = $num_id_cpte_prelev;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type_bien'] = NULL;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['num_client'] = NULL;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['piece_just'] = NULL;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['remarq'] = NULL;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['etat'] = 2; /* Prête */
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur'] = $montant_vente;
    } else if ($type_gar == 2) {
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['benef'] = $HTML_GEN_LSB_benef;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] = $libel_gar_mat;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['num_client'] = $num_client_rel;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['type_bien'] = $HTML_GEN_LSB_type_bien;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['piece_just'] = $piece_just;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['remarq'] = $remarq;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['etat'] = $etat_gar;
      $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['valeur'] = $montant_vente;
    }

    if ($traitement == "ajout") {
        $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['devise_vente'] = $devise_vente;
    }
    //prevent id_cli_prelev from being overwritten with 'empty' values
    if(!empty($id_cli_prelev)){
        $SESSION_VARS['def'][$HTML_GEN_LSB_benef]['DATA_GAR'][$num_gar]['id_cli_prelev'] = $id_cli_prelev;
    }
  }
  elseif($traitement == "suppression") {
    /* Suppression de garantie */
    $Myform->setMessage(_("La garantie a été supprimée avec succès"));
    $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['type'] = '' ;
    $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['id_gar'] = NULL ;
    $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] ='';
    $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['valeur'] = '';
    $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['etat'] = '';
//    array_remove_element($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar], $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]["id_cli_prelev"]);
  }

  $Myform->addButton(BUTTON_OK, 'Ado-7');
  $Myform->buildHTML();
  echo $Myform->HTML_code;

}
/*}}}*/

/*{{{ Ado-10 : Modification de garantie */
else if ($global_nom_ecran == "Ado-10") {
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Modification d'une garantie"));

  $Myform->addField ("benef", _("Bénéficiaire"), TYPC_LSB);
  $Myform->setFieldProperties("benef", FIELDP_ADD_CHOICES, $SESSION_VARS['clients_dcr']);
  $Myform->setFieldProperties("benef",FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties("benef",FIELDP_DEFAULT, $benef);
  $SESSION_VARS["beneficaire"]=$benef;
  $exclude = array("devise_vente", "gar_num_id_cpte_prelev", "gar_mat_id_bien", "gar_num_id_cpte_nantie", "id_doss", "id_gar","etat_gar");
  $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
  $Myform->setFieldProperties("type_gar", FIELDP_JS_EVENT, array("OnChange"=>"check_type_gar()"));

  /* Etat de la garantie */
  $etats_gar = array();
  $etats_gar[1] = adb_gettext($adsys["adsys_etat_gar"][1]);
  $etats_gar[2] = adb_gettext($adsys["adsys_etat_gar"][2]);
  $Myform->addField ("etat_gar", _("Etat de la garantie"), TYPC_LSB);
  $Myform->setFieldProperties("etat_gar", FIELDP_ADD_CHOICES, $etats_gar);
  $Myform->setFieldProperties("etat_gar",FIELDP_IS_REQUIRED, true);

  /* Compte de prélèvement de la garantie numéraire */
  $Myform->addField ("gar_num_id_cpte_prelev", _("Compte de prélèvement"), TYPC_TXT);
  $Myform->addLink("gar_num_id_cpte_prelev","rechercher",_("Rechercher"), "#");
  $Myform->setLinkProperties("rechercher",LINKP_JS_EVENT,array("OnClick"=>"open_compte();return false;"));
  $Myform->addHiddenType("num_id_cpte_prelev", $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
  $Myform->addHiddenType("id_cli_prelev", "");
  /* Libellé du matériel */
  $Myform->addField ("libel_gar_mat", _("Garantie matérielle"), TYPC_TXT);

  /* Types de bien */
  $types_biens = getTypesBiens();
  $Myform->addField ("type_bien", _("Type de bien"), TYPC_LSB);
  $Myform->setFieldProperties("type_bien", FIELDP_ADD_CHOICES, $types_biens);

  /* Numéro du client si garantie du matérielle */
  $Myform->addField ("num_client", _("Client garant du matériel"), TYPC_TXT);
  $Myform->addHiddenType("num_client_rel", $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['num_client']);

  /* Pièce justificative si garanties matérielles */
  $Myform->addField ("piece_just", _("Pièce justificative"), TYPC_TXT);

  /* Remarque si garanties matérilles */
  $Myform->addField ("remarq", _("Remarque"), TYPC_TXT);

  /* Lien rechercher */
  $Myform->addLink("num_client", "rech_client", _("Rechercher"), "#");
  $Myform->setLinkProperties("rech_client",LINKP_JS_EVENT,array("OnClick"=>"rech_client();"));
  $Myform->setFieldProperties("num_client",FIELDP_IS_LABEL,true);

  $Myform->addHiddenType("traitement", "modification");
  $Myform->addHiddenType("num_gar", $num_gar);

  $Myform->setFieldProperties("benef", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['benef']);
  $Myform->setFieldProperties("type_gar", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['type']);
  $Myform->setFieldProperties("montant_vente", FIELDP_DEFAULT, recupMontant($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['valeur']));
  $Myform->setFieldProperties("etat_gar", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['etat']);
  $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_LABEL,true);

  if ($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['type'] ==1) { /* garanties numéraires */
    $Myform->setFieldProperties("etat_gar",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("type_bien",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("piece_just",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("remarq",FIELDP_IS_LABEL,true);

    if ($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] != '') {
      $CPT_PRELEV_GAR = getAccountDatas($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
      $Myform->setFieldProperties("gar_num_id_cpte_prelev", FIELDP_DEFAULT, $CPT_PRELEV_GAR["num_complet_cpte"]);
    }
  }
  elseif($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['type'] ==2) {
    $Myform->setFieldProperties("libel_gar_mat", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
    $Myform->setFieldProperties("type_bien", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['type_bien']);
    $Myform->setFieldProperties("num_client", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['num_client']);
    $Myform->setFieldProperties("piece_just", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['piece_just']);
    $Myform->setFieldProperties("remarq", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['remarq']);
  }

  $order = array ("benef","type_gar", "gar_num_id_cpte_prelev", "libel_gar_mat", "type_bien", "num_client","piece_just","remarq","montant_vente", "etat_gar");
  $Myform->setOrder(NULL, $order);

  $Myform->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ado-7');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Ado-9');

  /* Contrôle des champs à renseigner selon le type de garantie  */
  $JS_valide ="";
  $JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1 && document.ADForm.gar_num_id_cpte_prelev.value =='')";
  $JS_valide .="\n\t{msg+='"._("Le compte de prélèvement des garanties doit être renseigné")."'; ADFormValid = false;}";

  $JS_valide .="\n\tif( (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.libel_gar_mat.value =='') ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.HTML_GEN_LSB_type_bien.value == 0) ||
               (document.ADForm.HTML_GEN_LSB_type_gar.value == 2 && document.ADForm.num_client_rel.value == ''))
               ";
  $JS_valide .="\n\t{msg+='"._("Le libellé,le type du matériel et le client garant doivent être renseignés")."'; ADFormValid = false;}";

  $Myform->addJS(JSP_BEGIN_CHECK, "valJS", $JS_valide);

  /* JS : recherche du compte de prélèvement des garanties numéraires */
  $JS_prelev ="";
  $JS_prelev .="\nfunction open_compte()\n";
  $JS_prelev .="{\n";
  $JS_prelev .="\tif(document.ADForm.HTML_GEN_LSB_type_gar.value == 1){url = '".$http_prefix."/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=gar_num_id_cpte_prelev&id_cpt_dest=num_id_cpte_prelev&devise=".$PROD["devise"]."';\n";
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
  $JS_active .="\n\tdocument.ADForm.libel_gar_mat.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.piece_just.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.remarq.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = false;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=0;";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_type_bien.disabled = false;";
  $JS_active .="\n\tdocument.ADForm.gar_num_id_cpte_prelev.value = '';";
  $JS_active .="\n\t}";
  $JS_active .="\n\telse";
  $JS_active .="\n\t{";
  $JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_etat_gar.disabled = false;";
  $JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_gar.selectedIndex=0;";
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

/*{{{ Ado-11 : Suppression de garantie */
else if ($global_nom_ecran == "Ado-11") {
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Suppression d'une garantie"));

  if ($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['type'] == 1 ) {
    $exclude = array("gar_num_id_cpte_prelev", "gar_mat_id_bien","gar_num_id_cpte_nantie","id_doss","id_gar");
    $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
    $Myform->addField ("gar_num_id_cpte_prelev", _("Compte de prélèvement"), TYPC_TXT);

    /* Si garantie numéraire, afficher le numéro complet du compte de prélèvement */
    if ($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte'] != '') {
      $CPT_PRELEV_GAR = getAccountDatas($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
      $Myform->setFieldProperties("gar_num_id_cpte_prelev", FIELDP_DEFAULT, $CPT_PRELEV_GAR["num_complet_cpte"]);
      $Myform->setFieldProperties("gar_num_id_cpte_prelev",FIELDP_IS_LABEL,true);
    }

    $order = array ("type_gar", "gar_num_id_cpte_prelev","montant_vente", "devise_vente", "etat_gar");
  }
  elseif($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['type'] == 2 ) {
    /* Champs à exclure */
    $exclude = array("gar_num_id_cpte_prelev", "gar_mat_id_bien" , "gar_num_id_cpte_nantie", "id_doss", "id_gar");

    $Myform->addTable("ad_gar", OPER_EXCLUDE, $exclude);
    $Myform->addField ("libel_gar_mat", _("Garantie matérielle"), TYPC_TXT);
    $Myform->addField ("num_client", _("Client garant du matériel"), TYPC_TXT);

    $types_biens = getTypesBiens();
    $Myform->addField ("type_bien", _("Type de bien"), TYPC_TXT);

    $Myform->setFieldProperties("libel_gar_mat", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['descr_ou_compte']);
    $id_type_bien = $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['type_bien'];

    $Myform->setFieldProperties("type_bien", FIELDP_DEFAULT, $types_biens[$id_type_bien]);
    $Myform->setFieldProperties("num_client", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['num_client']);

    $Myform->setFieldProperties("libel_gar_mat",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("type_bien",FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("num_client",FIELDP_IS_LABEL,true);
    $order = array ("type_gar", "libel_gar_mat", "type_bien", "montant_vente", "etat_gar");
  }

  $Myform->addHiddenType("traitement", "suppression");
  $Myform->addHiddenType("num_gar", $num_gar);
  $Myform->addHiddenType("benef", $benef);

  /* Champs communs */
  $Myform->setFieldProperties("type_gar", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['type']);
  $Myform->setFieldProperties("montant_vente", FIELDP_DEFAULT, recupMontant($SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['valeur']));
  $Myform->setFieldProperties("etat_gar", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['etat']);
  $Myform->setFieldProperties("devise_vente", FIELDP_DEFAULT, $SESSION_VARS['def'][$benef]['DATA_GAR'][$num_gar]['devise_vente']);

  $Myform->setFieldProperties("type_gar",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("montant_vente",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("etat_gar",FIELDP_IS_LABEL,true);
  $Myform->setFieldProperties("devise_vente",FIELDP_IS_LABEL,true);

  $Myform->setOrder(NULL, $order);

  $Myform->addFormButton(1,1,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"supprimer", _("Supprimer"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ado-7');
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("supprimer", BUTP_PROCHAIN_ECRAN, 'Ado-9');

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>