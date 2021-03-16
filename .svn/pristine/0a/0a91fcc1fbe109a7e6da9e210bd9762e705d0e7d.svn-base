<?php

/**
 * [902] Dossiers Mobile Lending en attente
 *
 * Cette opération comprends les écrans :
 * - Mlt-1 : Liste demande autorisation retrait
 * - Adr-2 : Confirmation autorisation retrait
 *
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/access.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/dbProcedures/mobile_lending.php';

if ($global_nom_ecran == "Mlt-1") {
    global $global_id_client;

    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Dossiers de crédit en attente"));

    $dossier_attente= getCreditAttente("statut_demande = 4","id_client ASC");

    $xtHTML = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $xtHTML .= "<br /><table align=\"center\" cellpadding=\"5\" width=\"75% \" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding id='table_ml' >
    <tr align=\"center\" bgcolor=\"$colb_tableau\"><th>"._("Date demande")."</th><th>"._("N° client")."</th><th>"._("Nom client")."</th><th>"._("N° dossier")."</th><th>"._("Montant demandé")."</th><th>"._("Durée du crédit")."</th><th>"._("Épargne du client")."</th><th>"._("Approbation/Rejet du dossier crédit")."</th><th>"._("Déboursement du dossier crédit")."</th></tr>";

    while (list($key1, $DET) = each($dossier_attente)) {
        $date_demande = pg2phpDate($DET['date_creation']);
        $client = $DET['id_client'];
        $dossier = $DET['id_doss'];
        $info_doss = getDossierCrdtInfo($dossier);
        $etat_doss = $info_doss['etat'];
        $mnt_dem = $DET['mnt_dem'];
        $data_client = getClientDatas($client);
        $nom = $data_client['pp_nom']." ".$data_client['pp_prenom'];
        $duree = $DET['duree']. " mois";
        // donnees client pour le status frame
        //if (is_int($client)) {print_rn(test);
            $details = getClientDatas($client);
        //}
        $id_agc = getNumAgence();
        $AGD = getAgenceDatas($id_agc);
        $type_num_cpte = $AGD['type_numerotation_compte'];

        if ($type_num_cpte == 1) {
            $id_client_formate = sprintf("%06d", $details['id_client']);
        } else if ($type_num_cpte == 2) {
            $id_client_formate = sprintf("%05d", $details['id_client']);
        } else if ($type_num_cpte == 3) {
            $id_client_formate = sprintf("%07d", $details['id_client']);
        } else if ($type_num_cpte == 4) {
            $id_client_formate = makeNumClient($details['id_client']);
        }

        $nom_client = getClientName($client);
        $data_client = getClientDatas($client);
        $global_etat_client = $data_client['etat'];

        $global_id_client = $client;
        $prochain_ecran_epargne = "Vcp-1";
        $prochain_ecran_credit = "Gen-11";
        $lien_epargne = "<a href=".$PHP_SELF."?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$prochain_ecran_epargne."&num_client=".$client."&global_id_client_formate=".$id_client_formate."&nom_cli=".$nom_client.">Détail client</a>";
        $lien_credit_appro = "<a href=".$PHP_SELF."?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$prochain_ecran_credit."&num_client=".$client."&global_id_client_formate=".$id_client_formate."&nom_cli=".$nom_client." name='lien_approv' data-etat='$etat_doss'>Approbation/Rejet crédit</a>";
        $lien_credit_debours = "<a href=".$PHP_SELF."?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$prochain_ecran_credit."&num_client=".$client."&global_id_client_formate=".$id_client_formate."&nom_cli=".$nom_client." name='lien_debours' data-etat='$etat_doss'>Deboursement crédit</a>";


        $xtHTML .= "\n<tr bgcolor=\"$color\"><td>".$date_demande."</td><td>".$client."</td><td>".$nom."</td><td>".$dossier."</td><td>".afficheMontant($mnt_dem)."</td><td>".$duree."</td><td>".$lien_epargne."</td><td>".$lien_credit_appro."</td><td>".$lien_credit_debours."</td></tr>";


    }
    $xtHTML .= "</table><br /><br/><br />";
$acces_appro =  check_access(904)?'true':'false';
$acces_debours = check_access(905)?'true':'false';
    $js ="
    console.log(".$acces_debours.");
    $('#table_ml').find('[name*=lien_debours]').each(function(index, item){
        console.log($(item));
        if (".$acces_debours."){
            if($(item).attr('data-etat') == 1){
                 $(item).css({'text-decoration':'none', 'cursor':'not-allowed'});
                 $(item).click(function(event){
                 event.preventDefault();
            });
            }

	    }
	    else{
	        $(item).css({'text-decoration':'none', 'cursor':'not-allowed'});
	        $(item).click(function(event){
                event.preventDefault();
            });
	    }
    });

    $('#table_ml').find('[name*=lien_approv]').each(function(index, item){
    console.log($(item));
        if (".$acces_appro.") {
            if($(item).attr('data-etat') == 2){
                $(item).css({'text-decoration':'none', 'cursor':'not-allowed'});
                $(item).click(function(event){
                event.preventDefault();
        });
            }    

	    }
	    else{
	    	$(item).css({'text-decoration':'none', 'cursor':'not-allowed'});
	        $(item).click(function(event){
                event.preventDefault();
            });
	    }
	    
    });";
    $myForm->addJS(JSP_FORM, "lien_debours", $js);

    $myForm->addHTMLExtraCode("xtHTML", $xtHTML);

    $myForm->addFormButton(1, 2, "annuler", _("Retour"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-17");
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $myForm->buildHTML();
    echo $myForm->getHTML();
} else {
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
    // _("L'écran $global_nom_ecran n'existe pas")
}