<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

/**
 * change_montant_devise
 *
 * Les champs suivants doivent être postés depuis la fenêtre appelante :<ul>
 * <li> Noms des champs de la fenêtre appelante :<ul>
 * 	<li> $nomChampDevise : Montant
 * 	<li> $nomChampCV : Contre-Valeur du montant
 * 	<li> $comm_nette : Commission nette
 * 	<li> $taux: Taux utilisé pour l'opération de change
 * 	<li> $reste: Reste (différence entre la contre valeur et son arrondi)
 * 	<li> $dest_reste: Destination du reste
 * 	</ul>
 * <li> Valeurs provenant de la fenetre appelante et nécessaire au calcul de la C/V. Ces valeurs ne peuvent pas être récupérées par javascript car elles doivent faire l'objet d'un traitement en PHP :<ul>
 * 	<li> $montant : le montant à changer
 * 	<li> $contre_valeur : La C/V du montant à changer
 * 	<li> $devise : Devise de $montant
 * 	<li> $devise_contre_valeur : devise de $contrevaleur
 *	</ul>
 * <li> Valeurs fonction du contexte de l'appelant :<ul>
 * 	<li> $etape : toujours = 1 quand on vient de l'appelant. 2 si on doit y retourner
 * 	<li> $achat_vente : champ indiquant si l'oppération est un achat ou une vente
 *     Si achat : la montant vendu est $montant, sinon c'est $contrevaleur qui contient le montant vendu
 * 	<li> $type_change : Type de change (1 pour cash, 2 pour scriptural). Utilisé pour savoir quel taux de change récupérer dans la DB
 *	</ul>
 * </ul>
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/HtmlHeader.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/devise.php';
require_once 'lib/misc/divers.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Devise.php';
require_once 'ad_ma/app/models/Divers.php';

global $global_remote_id_agence;

/*{{{ Etape 1 */
if ($etape == 1) {   
  // Récupération du montant
  $montant = recupMontant($montant);
  $contre_valeur = recupMontant($contre_valeur);

  $SESSION_VARS["change"] = array();

  if (empty($reste)) {
    $SESSION_VARS["change"]["envoi_reste"]=0;
  } else {
    $SESSION_VARS["change"]["envoi_reste"]=1;
  }

  $SESSION_VARS["print_recu_change"]=0;

  // Fonctions java script permettant de calculer le taux ou l'inverse du taux si
  // la valeur de l'un de ces champs venait d'étre changéé.
  $js =  "function ftaux()\n";
  $js .= "{\n";
  $js .= "  if(document.ADForm.taux.value > 0)\n";
  $js .= "  {\n";
  $js .= "    document.ADForm.inv_taux.value=Math.round((1/document.ADForm.taux.value)*1000000000000)/1000000000000;\n";
  $js .= "  }\n";
  $js .= "  else\n";
  $js .= "  {\n";
  $js .= "    document.ADForm.inv_taux.value=0;\n";
  $js .= "    document.ADForm.taux.value=0;\n";
  $js .= "  }\n";
  $js .= "}\n";
  $js .= "function finvtaux()\n";
  $js .= "{\n";
  $js .= "  if(document.ADForm.inv_taux.value > 0)\n";
  $js .= "  {\n";
  $js .= "    document.ADForm.taux.value=Math.round((1/document.ADForm.inv_taux.value)*1000000000000)/1000000000000;\n";
  $js .= "  }\n";
  $js .= "  else\n";
  $js .= "  {\n";
  $js .= "    document.ADForm.inv_taux.value=0;\n";
  $js .= "    document.ADForm.taux.value=0;\n";
  $js .= "  }\n";
  $js .= "}\n";

  // On détermine le sens de l'opération : est-ce du champ spécifié à l'interface ==> sa C/V (appel à getChangeInfos) ou du champ C/V spécifié à l'interface vers l'orginne (appel à getChangeFinal)

  if ($achat_vente == _("vente")) { // On achète la devise fixe et on vend la devisee variable
    $SESSION_VARS["change"]["devise_vendue"] = $devise_contre_valeur;
    $SESSION_VARS["change"]["devise_achetee"] = $devise;
    if ($contre_valeur > 0) { // On a fourni la contrevaleur
      $sens_change = 2; // On va faire le change inversé
      $montant_depart = $contre_valeur;
    } else if ($montant > 0) {
      $sens_change = 1; // On va faire le change normal
      $montant_depart = $montant;
    } else {
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ni le montant, ni la C/V n'ont été renseignés"
    }
  } else if ($achat_vente == _("achat")) { // On achète la devise variable et on vend la devise fixe
    $SESSION_VARS["change"]["devise_vendue"]=$devise;
    $SESSION_VARS["change"]["devise_achetee"]=$devise_contre_valeur;
    if ($contre_valeur > 0) { // On a fourni la contrevaleur
      $sens_change = 1; // On va faire le change normal
      $montant_depart = recupMontant($contre_valeur);
    } else if ($montant > 0) {
      $sens_change = 2; // On va faire le change inversé
      $montant_depart = recupMontant($montant);
    } else {
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ni le montant, ni la C/V n'ont été renseignés"
    }
  } else {
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "l'oppération doit étre un achat ou une vente"
  }

  // Détermination de la devise à affecter au premier champ du formulaire
  if ($contre_valeur > 0) { // On a fourni la contrevaleur
    $devise_depart = $devise_contre_valeur;
    $devise_arrivee = $devise;
    $nom_champ_montant_destination = $nomChampDevise; // Permet de renvoyer le montant calculé vers l'appelant
  } else if ($montant > 0) { // On a fourni le montant
    $devise_depart = $devise;
    $devise_arrivee = $devise_contre_valeur;
    $nom_champ_montant_destination = $nomChampCV; // Permet de renvoyer le montant calculé vers l'appelant
  }

  if ($sens_change == 2) { // Change inversé
    $nom_champ_formulaire = _("Contrevaleur");
  } else {       // Change normal
    $nom_champ_formulaire = _("Montant");
  }

  $montant_depart = recupMontant($montant_depart);

  $SESSION_VARS["change"]["montant_depart"] = $montant_depart;
  $SESSION_VARS["change"]["nom_champ_montant_destination"] = $nom_champ_montant_destination;

  $SESSION_VARS["change"]["nom_comm_nette"] = $comm_nette;
  $SESSION_VARS["change"]["nom_taux"] = $taux;
  $SESSION_VARS["change"]["nom_reste"] = $reste;
  $SESSION_VARS["change"]["nom_dest_reste"] = $dest_reste;
  $SESSION_VARS["change"]["sens_change"] = $sens_change;

  $taux_indicatif = getTauxChange($SESSION_VARS["change"]["devise_achetee"], $SESSION_VARS["change"]["devise_vendue"], false);

  $isAllowed = 1;
  $remote_dbc = AgenceRemote::getRemoteAgenceConnection($global_remote_id_agence);
  $erreur = Divers::checkTauxDeviseForOperationDeplacer($SESSION_VARS["change"]["devise_achetee"], $SESSION_VARS["change"]["devise_vendue"], $remote_dbc);
    
  if ($erreur->errCode != NO_ERR) {
      $isAllowed = 2;
      $msgErr = _("Cette opération de change n\\'est pas permise car les taux de change sont différents dans les deux agences");
  }
    
  // Création du formulaire
  $MyPage = new HTML_GEN2(_("Assistant de change"));

  setMonnaieCourante($devise_depart);
  $MyPage->addField("montant_depart", $nom_champ_formulaire, TYPC_MNT);
  $MyPage->setFieldProperties("montant_depart", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("montant_depart", FIELDP_DEFAULT, $montant_depart);

  $MyPage->addField("devise", _("Devise"), TYPC_TXT);
  $MyPage->setFieldProperties("devise", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("devise", FIELDP_DEFAULT, $devise_arrivee);

  setMonnaieCourante($SESSION_VARS["change"]["devise_achetee"]);
  $MyPage->addField("commission", _("Commission nette"), TYPC_MNT);
  $MyPage->addField("taux", _("Taux"), TYPC_TXT);
  $MyPage->addField("inv_taux", _("1 / Taux"), TYPC_TXT);

  $MyPage->setFieldProperties("taux", FIELDP_JS_EVENT, array("onchange"=>"ftaux();"));
  $MyPage->setFieldProperties("inv_taux", FIELDP_JS_EVENT, array("onchange"=>"finvtaux();"));
  $MyPage->addJS(JSP_FORM, "modif", $js);

  $MyPage->addTableRefField("dest_reste", _("Destination du reste"), "adsys_change_dest_reste");

  // Si aucun client n'est sélectionné ou si le compte de base du client est fermé
  // if ($global_cpt_base_ouvert == false) ** Pour le moment exclus à cause des extraits de compte
  $MyPage->setFieldProperties("dest_reste", FIELDP_EXCLUDE_CHOICES, array(2));

  $MyPage->setFieldProperties("dest_reste", FIELDP_HAS_CHOICE_AUCUN, false);

  // champ hiden pour déterminer l'étape
  $MyPage->addHiddenType("etape");
  
  // Création d'une fonction JS qui avertit l'utilisateur qui a saisi un taux supérieur au taux indicatif et qui verifie les taux 
  // de change dans les 2 agences d'un operation en deplacé avant
  $js_warn =  "function checkTauxInd()\n";
  $js_warn .= "{\n";  
  $js_warn .= "  var isAllowed = $isAllowed; \n";
  $js_warn .= "  if (isAllowed != 1) { \n";
  $js_warn .= "    alert('$msgErr'); \n";
  $js_warn .= "    return false;\n";
  $js_warn .= "  }\n\n";  
  $js_warn .= "  if (document.ADForm.taux.value > $taux_indicatif)\n ";
  $js_warn .= "  {\n";
  $js_warn .= "    if (!confirm ('"._("Vous avez choisi un taux supérieur au taux indicatif")." ($taux_indicatif)\\n"._("Cette opération de change est une vente à perte.")."\\n"._("Cliquez sur OK pour confirmer ce taux")."')) \n";
  $js_warn .= "    {\n";
  $js_warn .= "      return false;\n";
  $js_warn .= "    }\n";
  $js_warn .= "    else\n";
  $js_warn .= "    {\n";
  $js_warn .= "      return true;\n";
  $js_warn .= "    }\n";
  $js_warn .= "  }\n";
  $js_warn .= "  else\n";
  $js_warn .= "  {\n";
  $js_warn .= "    return true;\n";
  $js_warn .= "  }\n";
  $js_warn .= "}\n";

  $MyPage->addJS(JSP_FORM, "js_warn", $js_warn);

  // les boutton
  $MyPage->addFormButton(1, 1, "ok", _("OK"),  TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "ann",_("Annuler"),  TYPB_BUTTON);
  $MyPage->setFormButtonProperties("ann", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  $MyPage->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.etape.value=2; return checkTauxInd();"));

  if ($montant != '' && $contre_valeur != '') {
    // On a déjà fait un calcul, on récupère donc les valeurs depuis l'appelant via jacascript
    $js .= "document.ADForm.taux.value=opener.document.ADForm.$taux.value; ftaux();\n";
    $js .= "document.ADForm.HTML_GEN_LSB_dest_reste.value=opener.document.ADForm.$dest_reste.value;\n";
    $js .= "document.ADForm.commission.value=formateMontant(opener.document.ADForm.$comm_nette.value);\n";
    $MyPage->addJS(JSP_FORM, "js", $js);
  } else {
    $taux = getTauxChange($SESSION_VARS["change"]["devise_achetee"], $SESSION_VARS["change"]["devise_vendue"], true, $type_change);
    if ($taux > 0) {
      $inv_taux = round(1/$taux, 6);
    } else {
      $inv_taux = 0;
    }

    if ($sens_change == 1) { // Change normal
      $tmp = getChangeInfos($SESSION_VARS["change"]["montant_depart"], $SESSION_VARS["change"]["devise_achetee"], $SESSION_VARS["change"]["devise_vendue"]);
    } else {
      $tmp = getChangeFinal($SESSION_VARS["change"]["montant_depart"], $SESSION_VARS["change"]["devise_achetee"], $SESSION_VARS["change"]["devise_vendue"]);
    }
        
    $commission = $tmp["commission"] + $tmp["taxe"];

    // Java script: pour la valeur par défaut de Taux, Destination du reste et Commission nette
    $MyPage->setFieldProperties("commission", FIELDP_DEFAULT, $commission);
    $MyPage->setFieldProperties("taux", FIELDP_DEFAULT, $taux);
    $MyPage->setFieldProperties("inv_taux", FIELDP_DEFAULT, $inv_taux);
  }

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}/* }}} */

/*{{{ Etape 2 */
else 
    if ($etape == 2) {
        
        global $global_remote_id_agence;
        
        // Récupération des valeurs postées de l'écran précédent : $commission et $taux
        $commission = recupMontant($commission);     
        
        if ($SESSION_VARS["change"]["sens_change"] == 1) { // Change normal
            $tmp = getChangeInfos($SESSION_VARS["change"]["montant_depart"], $SESSION_VARS["change"]["devise_achetee"], $SESSION_VARS["change"]["devise_vendue"], $commission, $taux);
            $ctrval = $tmp["cv_montant_reel_change"];
            $ctrval_arrondie = $tmp["cv_billet"];
            $reste = $tmp["diff_dev_ref"];
        } else {
            $tmp = getChangeFinal($SESSION_VARS["change"]["montant_depart"], $SESSION_VARS["change"]["devise_achetee"], $SESSION_VARS["change"]["devise_vendue"], $commission, $taux);
            $ctrval = $tmp["montant_debite"];
            $ctrval_arrondie = $ctrval;
        }
        
        if ($SESSION_VARS["change"]["envoi_reste"] == 0) { // Pas d'arrondi donc C/V arrondie = C/V
            $ctrval_arrondie = $ctrval;
        }
        
        // Si la C/V est négative, déclencher une erreur
        if ($ctrval < 0) {
            $Code_Js = "alert('" . _("Cette opération de change n\\'est pas permise car la C/V est négative") . "');window.close();";
        }
        
        if (($SESSION_VARS["change"]["envoi_reste"] == 1) && ($tmp["alert"] == true)) {
            // Ce cas se présente s'il y a un reste de change alors qu'on a défini la C/V.
            // On préfère empêcher ce cas pour éviter une erreur interne plus tard car le calcul est très complexe ...
            $Code_Js = "alert('" . _("Cette opération de change n\\'est pas permise car les billets et les unités des deux devises rendent le calcul impossible.") . " " . sprintf(_("Vous pouvez corriger ce problème en changeant directement le montant de %s en %s"), afficheMontant($ctrval, $SESSION_VARS["change"]["devise_achetee"]), $SESSION_VARS["change"]["devise_vendue"]) . "');window.close();";
        } else {
            
            $remote_dbc = AgenceRemote::getRemoteAgenceConnection($global_remote_id_agence);
            $erreur = Divers::checkTauxDeviseForOperationDeplacer($SESSION_VARS["change"]["devise_achetee"], $SESSION_VARS["change"]["devise_vendue"], $remote_dbc);
               
            if ($erreur->errCode != NO_ERR) {
                $Code_Js = "alert('" . _("Cette opération de change n\\'est pas permise car les taux de change sont différents dans les deux agences") . "'); window.close();";                
            } else {                
                // Renvoi des données à la fonction appelante
                // Commission
                $Code_Js = "opener.document.ADForm." . $SESSION_VARS["change"]["nom_comm_nette"] . ".value=$commission;\n";
                // Taux
                $Code_Js .= "opener.document.ADForm." . $SESSION_VARS["change"]["nom_taux"] . ".value=$taux;\n";
                // Destination du reste
                $Code_Js .= "opener.document.ADForm." . $SESSION_VARS["change"]["nom_dest_reste"] . ".value=$HTML_GEN_LSB_dest_reste;\n";
                // Reste s'il y a lieu
                if ($SESSION_VARS["change"]["envoi_reste"] == 1) {
                    $Code_Js .= "opener.document.ADForm." . $SESSION_VARS["change"]["nom_reste"] . ".value='$reste';\n";
                }
                // Montant final
                $Code_Js .= "opener.document.ADForm." . $SESSION_VARS["change"]["nom_champ_montant_destination"] . ".value=formateMontant('" . $ctrval_arrondie . "');window.close();";
            }
        }
        
        echo "<script type=\"text/javascript\">\n";
        echo "$Code_Js";
        echo "</script>";
        $SESSION_VARS["print_recu_change"] = 1;
        $SESSION_VARS["envoi_reste"] = $SESSION_VARS["change"]["envoi_reste"];
        unset($SESSION_VARS["change"]);
    }     /* }}} */
    
    else
        signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
 
?>