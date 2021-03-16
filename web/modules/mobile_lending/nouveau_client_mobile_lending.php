<?php


require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/html/suiviCredit.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/mobile_lending.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/misc/excel.php';
require_once 'lib/misc/csv.php';

require_once "lib/html/HTML_menu_gen.php";

if ($global_nom_ecran == "Mle-1") {
    global $global_nom_utilisateur, $global_id_utilisateur;

    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Nouveaux clients Mobile Lending"));

    $data_uti = getDataUtilisateurMobileLending($global_id_utilisateur);
    $client_eligible = getDataDemandeClientEligible(" m.code_agent = '".$data_uti['ml_code_agent']."' AND m.statut_demande IN (2, 5)");
    $DATA_RAPPORT= array();

    //$dossier_attente = getCreditAttente("statut_demande = 4", "id_client ASC");

    $xtHTML = "<i>".$global_nom_utilisateur." ,voici les clients qui ont utilisé votre code ".$data_uti['ml_code_agent']." pour demander un crédit Mobile Lending. Veuillez en assurer le suivi : </i></br>


<h3>"._("Crédits en cours"). "</h3><br /><table align=\"center\" cellpadding=\"5\" width=\"85% \" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
<tr align=\"center\" bgcolor=\"$colb_tableau\"><th>" . _("N° client") . "</th><th>" . _("Nom client") . "</th><th>" . _("Numéro de téléphone") . "</th><th>" . _("N° dossier") . "</th><th>" . _("Montant crédit") . "</th><th>" . _("Durée crédit") . "</th><th>" . _("Etat crédit") . "</th><th>" . _("Montant échéance") . "</th><th>" . _("Date échéance") . "</th></tr>";

while (list($key0, $DET0) = each($client_eligible)) {
    $Clients = getDataDemandeMobileLending(" id_transaction = '".$DET0['transaction']."' ");
    while (list($key1, $DET) = each($Clients)) {
            $no_client = $DET['id_client'];
            $data_client = getClientDatas($no_client);
            $nom = $data_client['pp_nom'] . " " . $data_client['pp_prenom'];
            $numero = $DET['telephone'];
            $dossier = $DET['id_doss'];
            $mnt_dem = $DET['mnt_dem'];
            $duree = $DET['duree'] . " mois";
            $data_credit = getDataCreditMobileLendingEnCours($dossier);
            $etat_credit = adb_gettext($adsys["adsys_etat_dossier_credit"][$data_credit[$dossier]['etat']]);
            $mnt_eche = $data_credit[$dossier]['solde_cap']+$data_credit[$dossier]['solde_int']+$data_credit[$dossier]['solde_gar']+$data_credit[$dossier]['solde_pen'];
            $date_eche = pg2phpDate($data_credit[$dossier]['date_ech']);

            $xtHTML .= "\n<tr bgcolor=\"$color\"><td>".$no_client."</td><td>".$nom."</td><td>".$numero."</td><td>".$dossier."</td><td>".afficheMontant($mnt_dem)."</td><td>".$duree."</td><td>".$etat_credit."</td><td>".afficheMontant($mnt_eche)."</td><td>".$date_eche."</td></tr>";

            $DATA_RAPPORT['credit_encours'][$dossier]['no_client'] = $no_client;
            $DATA_RAPPORT['credit_encours'][$dossier]['nom'] = $nom;
            $DATA_RAPPORT['credit_encours'][$dossier]['telephone'] = $numero;
            $DATA_RAPPORT['credit_encours'][$dossier]['id_doss'] = $dossier;
            $DATA_RAPPORT['credit_encours'][$dossier]['mnt_dem'] = $mnt_dem;
            $DATA_RAPPORT['credit_encours'][$dossier]['duree'] = $duree;
            $DATA_RAPPORT['credit_encours'][$dossier]['etat_credit'] = $etat_credit;
            $DATA_RAPPORT['credit_encours'][$dossier]['mnt_eche'] = $mnt_eche;
            $DATA_RAPPORT['credit_encours'][$dossier]['date_eche'] = $date_eche;

        }
    }
    $xtHTML .= "</table><br /><br/><br />";
    $myForm->addHTMLExtraCode("xtHTML", $xtHTML);


    $client_eligible1 = getDataDemandeClientEligible(" m.code_agent = '".$data_uti['ml_code_agent']."' AND m.statut_demande IN (3)");
    $xtHTML = "<h3>"._("Crédits soldés"). "</h3><br /><table align=\"center\" cellpadding=\"5\" width=\"85% \" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
<tr align=\"center\" bgcolor=\"$colb_tableau\"><th>" . _("N° client") . "</th><th>" . _("Nom client") . "</th><th>" . _("Numéro de téléphone") . "</th><th>" . _("N° dossier") . "</th><th>" . _("Montant crédit") . "</th><th>" . _("Durée crédit") . "</th><th>" . _("Etat crédit") . "</th><th>" . _("Montant échéance") . "</th><th>" . _("Date échéance") . "</th></tr>";

    while (list($key00, $DET00) = each($client_eligible1)) {
        $Clients1 = getDataDemandeMobileLending(" id_transaction = '".$DET00['transaction']."' ");
        while (list($key2, $DET2) = each($Clients1)) {
            if ($DET2['statut_demande'] == 3) {
                $no_client = $DET2['id_client'];
                $data_client = getClientDatas($no_client);
                $nom = $data_client['pp_nom'] . " " . $data_client['pp_prenom'];
                $numero = $DET2['telephone'];
                $dossier = $DET2['id_doss'];
                $mnt_dem = $DET2['mnt_dem'];
                $duree = $DET2['duree'] . " mois";
                $data_credit = getDataCreditMobileLendingSolde($dossier);
                $etat_credit = adb_gettext($adsys["adsys_etat_dossier_credit"][$data_credit[$dossier]['etat']]);
                $mnt_eche = $data_credit[$dossier]['mnt_cap'] + $data_credit[$dossier]['mnt_int'] + $data_credit[$dossier]['mnt_gar'] + $data_credit[$dossier]['mnt_reech'];
                $date_eche = pg2phpDate($data_credit[$dossier]['date_ech']);

                $xtHTML .= "\n<tr bgcolor=\"$color\"><td>" . $no_client . "</td><td>" . $nom . "</td><td>" . $numero . "</td><td>" . $dossier . "</td><td>" . afficheMontant($mnt_dem) . "</td><td>" . $duree . "</td><td>" . $etat_credit . "</td><td>" . afficheMontant($mnt_eche) . "</td><td>" . $date_eche . "</td></tr>";

                $DATA_RAPPORT['credit_solde'][$dossier]['no_client'] = $no_client;
                $DATA_RAPPORT['credit_solde'][$dossier]['nom'] = $nom;
                $DATA_RAPPORT['credit_solde'][$dossier]['telephone'] = $numero;
                $DATA_RAPPORT['credit_solde'][$dossier]['id_doss'] = $dossier;
                $DATA_RAPPORT['credit_solde'][$dossier]['mnt_dem'] = $mnt_dem;
                $DATA_RAPPORT['credit_solde'][$dossier]['duree'] = $duree;
                $DATA_RAPPORT['credit_solde'][$dossier]['etat_credit'] = $etat_credit;
                $DATA_RAPPORT['credit_solde'][$dossier]['mnt_eche'] = $mnt_eche;
                $DATA_RAPPORT['credit_solde'][$dossier]['date_eche'] = $date_eche;
            }
        }
    }

    $xtHTML .= "</table><br /><br/><br />";
    $SESSION_VARS['DATA_RAPPORT'] = $DATA_RAPPORT;

    $myForm->addHTMLExtraCode("xtHTML1", $xtHTML);

    $myForm->addFormButton(1, 1, "pdf", _("Rapport PDF"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("pdf", BUTP_PROCHAIN_ECRAN, "Mle-2");

    $myForm->addFormButton(1, 2, "annuler", _("Retour"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-17");
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $myForm->buildHTML();
    echo $myForm->getHTML();
}


elseif ($global_nom_ecran == "Mle-2"){
    global $global_nom_utilisateur, $global_monnaie_courante;

    $liste_criteres = array (
        _("Date") => date('d-m-y'),
        _("Utilisateur ") => $global_nom_utilisateur,
        _("Devise ") => $global_monnaie_courante

    );

    $xml = xmlNouveauClient($SESSION_VARS['DATA_RAPPORT'],$liste_criteres);
    //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'rapport_nouveaux_client_mobile_lending.xslt');
    //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
    echo get_show_pdf_html("Gen-17", $fichier_pdf);

}

?>
