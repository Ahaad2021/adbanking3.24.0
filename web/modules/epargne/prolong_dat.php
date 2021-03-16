<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [78] Permet de prolonger les DAT en instance de prolongation
 * Cette opération comprends les écrans :
 * - Pda-1 : Choix d'un compte à prolonger
 * - Pda-2 : Demande de confirmation de la prolongation
 * - Pda-3 : Confirmation de la prolongation
 * @package Epargne
 */

require_once 'lib/dbProcedures/epargne.php';

if ($global_nom_ecran == "Pda-1") {
  $html = new HTML_GEN2(_("Prolongation d'un compte à terme : choix du compte"));

  $listeDAT = clientHasCompteATerme($global_id_client);
  if (isset($listeDAT)) {
    foreach($listeDAT as $value)
    $choix[$value["id_cpte"]] = $value["num_complet_cpte"]." ".$value["intitule_compte"];
  };

  $html->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);
  $html->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);
  $html->setFieldProperties("NumCpte", FIELDP_IS_REQUIRED, true);

  $include_cpt = array("date_ouvert", "solde", "interet_annuel", "dat_date_fin","dat_num_certif", "dat_nb_prolong");
  $html->addTable("ad_cpt", OPER_INCLUDE, $include_cpt);
  $html->addTable("adsys_produit_epargne", OPER_INCLUDE, array("libel"));

  $html->setFieldProperties("libel", FIELDP_WIDTH, 40);

  //ordonner les champs
  $ordre = array("libel","solde","date_ouvert","interet_annuel","dat_date_fin","dat_nb_prolong","dat_num_certif");
  $html->setOrder("NumCpte", $ordre);
  //mettre les champs en label
  $fieldslabel = array_diff($ordre, array("NumCpte"));
  foreach($fieldslabel as $value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  };

  //en fonction du choix du compte, afficher les infos avec le onChange javascript

  $codejs = "function getInfoDAT()
          {";

  if (isset($listeDAT)) {
    foreach($listeDAT as $value) {
      $tmp_date1 = pg2phpDatebis($value["date_ouvert"]); //array(mm,dd,yyyy)
      $tmp_date2 = pg2phpDatebis($value["dat_date_fin"]); //array(mm,dd,yyyy)
      $decision = ($value["dat_prolongation"] == 'f' ) ? 2 : 1;
      $codejs .= "
                 if (document.ADForm.HTML_GEN_LSB_NumCpte.value == " . $value["id_cpte"] .	")
                 {
                 document.ADForm.libel.value = \"" . $value["libel"] . "\";
                 document.ADForm.solde.value = formateMontant(".$value["solde"].");
                 document.ADForm.HTML_GEN_date_date_ouvert.value = convert_js_date('".$tmp_date1[1]."/".$tmp_date1[0]."/".$tmp_date1[2]."');
                 document.ADForm.interet_annuel.value = formateMontant(".$value["interet_annuel"].");
                 document.ADForm.HTML_GEN_date_dat_date_fin.value = convert_js_date('".$tmp_date2[1]."/".$tmp_date2[0]."/".$tmp_date2[2]."');
                 document.ADForm.dat_nb_prolong.value = ".$value["dat_nb_prolong"].";
                 document.ADForm.dat_num_certif.value = '".$value["dat_num_certif"]."';
                 document.ADForm.HTML_GEN_LSB_decision_DAT.value = '".$decision."';
               };
                 ";
    };
    $codejs .= "
               if (document.ADForm.HTML_GEN_LSB_NumCpte.value == 0)
             {
               document.ADForm.libel.value = '';
               document.ADForm.solde.value = '';
               document.ADForm.HTML_GEN_date_date_ouvert.value='';
               document.ADForm.interet_annuel.value='';
               document.ADForm.HTML_GEN_date_dat_date_fin.value='';
               document.ADForm.dat_nb_prolong.value = '';
               document.ADForm.dat_num_certif.value = '';
               document.ADForm.HTML_GEN_LSB_decision_DAT.value = 0;
             }";
  };
  $codejs .= "
           }";

  $html->setFieldProperties("NumCpte", FIELDP_JS_EVENT, array("onChange"=>"getInfoDAT();"));
  $html->addJS(JSP_FORM, "JS1", $codejs);

  $html->addField("decision_DAT", _("Prolonger le Compte à terme"), TYPC_LSB);
  $choix = array(1=>"Oui", 2=>"Non");
  $html->setFieldProperties("decision_DAT", FIELDP_ADD_CHOICES, $choix);
  $html->setFieldProperties("decision_DAT", FIELDP_IS_REQUIRED, true);

//  $xtHTML = "Prolonger le DAT ?<input type=\"radio\"  name=\"prolongation\" value=\"Oui\"> Oui ";
//  $xtHTML .= "<input type=\"radio\" name=\"prolongation\" value=\"Non\"> Non ";
  /*
    $xtHTML = "<TABLE>";
    $xtHTML .= "<tr><td>Prolonger le DAT ?</td>";
    $xtHTML .= "<td><TABLE><tr><td width=\"50%\"><input type=\"radio\"  name=\"prolongation\" value=\"Oui\" Oui ></td>";
    $xtHTML .= "<td width=\"50%\"><input type=\"radio\" name=\"prolongation\" value=\"Non\" Non ></td></tr></TABLE></td>";
    $xtHTML .= "</tr></TABLE>";

    $html->addHTMLExtraCode("xtHTML", $xtHTML);
    $html->setHTMLExtraCodeProperties ("xtHTML", HTMP_IN_TABLE, true);
  */

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Pda-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();


} else if ($global_nom_ecran == "Pda-2") {
  if (isset($NumCpte))
    $SESSION_VARS["NumCpte"] = $NumCpte;
  if (isset($decision_DAT))
    $SESSION_VARS["decision_DAT"] = $decision_DAT;

  $InfoCpteDAT = getAccountDatas($SESSION_VARS["NumCpte"]);
  $InfoProduitDAT = getProdEpargne($InfoCpteDAT["id_prod"]);

  $html_msg =new HTML_message(_("Confirmation de l'opération sur le Compte à terme"));

  if ($SESSION_VARS["decision_DAT"] == 1) //prolonger
    $message = _("Vous allez procéder à la <b>prolongation</b> du Compte à terme suivant :")."<br />";
  else if ($SESSION_VARS["decision_DAT"] == 2) //ne pas prolonger
    $message = _("Vous avez décidé de <b>ne pas prolonger</b> le Compte à terme suivant :")."<br />";

  $tmp_date = pg2phpDatebis($InfoCpteDAT["dat_date_fin"]);
  $tmp_date1 = $tmp_date[1]."/".$tmp_date[0]."/".$tmp_date[2];

  $message .= sprintf(_("Compte n° : %s de type %s arrivant à échéance le %s"),"<b>".$InfoCpteDAT["num_complet_cpte"]."</b>","<b>".$InfoProduitDAT["libel"]."</b>",$tmp_date1);
  $message .= "<br />"._("Poursuivre l'opération ?");

  $html_msg->setMessage($message);
  $html_msg->addButton("BUTTON_OUI", 'Pda-3');
  $html_msg->addButton("BUTTON_NON", 'Pda-1');

  $html_msg->buildHTML();
  echo $html_msg->HTML_code;


} else if ($global_nom_ecran == "Pda-3") {

  if ($SESSION_VARS["decision_DAT"] == 1) {//appel DB
    decisionDAT($SESSION_VARS["NumCpte"], TRUE);
    $message = _("Le Compte à terme a été prolongé");
  } else if ($SESSION_VARS["decision_DAT"] == 2) {//appel DB
    decisionDAT($SESSION_VARS["NumCpte"], FALSE);
    $message = _("Le Compte à terme n'a pas été prolongé");
  }

  if (! alerteEcheanceDAT($global_id_client)) $global_alerte_DAT = FALSE;

  $html_msg =new HTML_message(_("Confirmation de l'opération sur le Compte à terme"));
  $html_msg->setMessage($message);
  $html_msg->addButton("BUTTON_OK", 'Gen-10');
  $html_msg->buildHTML();
  echo $html_msg->HTML_code;

} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'écran $global_nom_ecran n'a pas été trouvé"
?>