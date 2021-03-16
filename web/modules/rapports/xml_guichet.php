<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2: */
/**
 * Génère le code XML pour les reçus guichet
 * @package Rapports
 */

require_once 'lib/misc/xml_lib.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'modules/rapports/xslt.php';

/**
 * Crée le xml pour la génération d'un reçu d'une opération diverse de caisse
 * @author Papa
 * @since 2.8
 * @param array $DATA : tableau contenant les informations de l'opération passée : ces informations sont :
 * <UL>
 *   <LI> date 'date_op': la date de l'opération </LI>
 *   <LI> text 'libelle_op : le libellé de l'opération </LI>
 *   <LI> text 'compte_debit' : le compte comptable mouvementé au débit </LI>
 *   <LI> real 'montant_debit' : le montant de l'opération au débit </LI>
 *   <LI> text 'devise_debit' : la devise du montant au débit </LI>
 *   <LI> text 'compte_credit' : le compte comptable mouvementé au crédit </LI>
 *   <LI> real 'montant_credit' : le montant de l'opération au crédit </LI>
 *   <LI> text 'devise_credit' : la devise du montant au crédit </LI>
 *   <LI> text 'type_piece' : le type de la pièce justificatife de l'opération </LI>
 *   <LI> text 'numero_piece' : le numéro de la pièce justificative </LI>
 *   <LI> date 'date_piece' : date de la pièce justificative </LI>
 *   <LI> text 'communication' : la communication sur l'opération </LI>
 *   <LI> text 'remarque' : la remarque sur l'opération </LI>
 * </UL>
 * @param integer $id__his : numéro de transaction
 * @return str Le document XML généré.
 */
function xml_recu_operation_diverse_caisse($DATA, $id_his) {
  $document = create_xml_doc("operation_diverse_caisse", "operation_diverse_caisse.dtd");

  //Element root
  $root = $document->root();

  //En-tête généraliste
  $ref = gen_header($root, $DATA['ref_doc']);

  //Corps
  $body = $root->new_child("body", "");
  $body->new_child("date_op", $DATA['date_op']);
  $body->new_child("libelle_op", $DATA['libelle_op']);
  $body->new_child("compte_debit", $DATA['compte_debit']);
  $body->new_child("montant_debit", afficheMontant($DATA['montant_debit']));
  $body->new_child("devise_debit", $DATA['devise_debit']);
  $body->new_child("montant_tax_debit", afficheMontant($DATA['montant_tax_debit']));
  $body->new_child("montant_ttc_debit", afficheMontant($DATA['montant_ttc_debit']));
  $body->new_child("compte_credit", $DATA['compte_credit']);
  $body->new_child("montant_credit", afficheMontant($DATA['montant_credit']));
  $body->new_child("devise_credit", $DATA['devise_credit']);
  $body->new_child("montant_tax_credit", afficheMontant($DATA['montant_tax_credit']));
  $body->new_child("montant_ttc_credit", afficheMontant($DATA['montant_ttc_credit']));
  $body->new_child("type_piece", $DATA['type_piece']);
  $body->new_child("numero_piece", $DATA['numero_piece']);
  $body->new_child("date_piece", $DATA['date_piece']);
  $body->new_child("communication", $DATA['communication']);
  $body->new_child("remarque", $DATA['remarque']);
  $body->new_child("num_trans", sprintf("%09d", $id_his));

  return($document->dump_mem(true));

}

/**
 * Crée le xml pour la génération du rapport visualisation des transactions
 * @author Ibou
 * @since 3.2.2
 * @param array $DATA : tableau contenant les informations de l'opération passée : ces informations sont :
 * <UL>
 *   <LI> date 'date_op': la date de l'opération </LI>
 *   <LI> text 'libelle_op : le libellé de l'opération </LI>
 *   <LI> text 'compte_debit' : le compte comptable mouvementé au débit </LI>
 *   <LI> real 'montant_debit' : le montant de l'opération au débit </LI>
 *   <LI> text 'devise_debit' : la devise du montant au débit </LI>
 *   <LI> text 'compte_credit' : le compte comptable mouvementé au crédit </LI>
 *   <LI> real 'montant_credit' : le montant de l'opération au crédit </LI>
 *   <LI> text 'devise_credit' : la devise du montant au crédit </LI>
 *   <LI> text 'type_piece' : le type de la pièce justificatife de l'opération </LI>
 *   <LI> text 'numero_piece' : le numéro de la pièce justificative </LI>
 *   <LI> date 'date_piece' : date de la pièce justificative </LI>
 *   <LI> text 'communication' : la communication sur l'opération </LI>
 *   <LI> text 'remarque' : la remarque sur l'opération </LI>
 * </UL>
 * @param integer $id__his : numéro de transaction
 * @return str Le document XML généré.
 */
function xml_detail_transactions($DATAS, $criteres) {
  global $adsys;
  $document = create_xml_doc("detail_transactions", "detail_transactions.dtd");

  //Element root
  $root = $document->root();
  //En-tête généraliste
  $ref = gen_header($root, 'GUI-TRA');
 //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $criteres);
  
  //Corps
  $transactions = $root->new_child("transactions", "");
  $his = array();
  	while (list ($cle, $details) = each($DATAS)) {
  		if(!isset($his[$details['id_his']])){
  			$his[$details['id_his']] = $transactions->new_child("his_data", "");
  		}
  		$num_trans = $his[$details['id_his']]->new_child("num_trans", $details['id_his']);
  		$date = $his[$details['id_his']]->new_child("date", $details['date']);
  		$fonction = $his[$details['id_his']]->new_child("fonction", adb_gettext($adsys["adsys_fonction_systeme"][$details['type_fonction']]));
  		$login = $his[$details['id_his']]->new_child("login", $details['login']);
  		$num_client = $his[$details['id_his']]->new_child("num_client", $details['id_client']);
  		if(!isset($ecriture[$details['id_ecriture']])){
  			$ecriture[$details['id_ecriture']] = $his[$details['id_his']]->new_child("ligne_ecritures", "");
  		}
  		$num_ecriture = $ecriture[$details['id_ecriture']]->new_child("num_ecriture", $details['ref_ecriture']);
  		$libel_ecriture = new Trad($details['libel_ecriture']);
  		$libel_ecriture = $ecriture[$details['id_ecriture']]->new_child("libel_ecriture", $libel_ecriture->traduction());
  		$mvmts[$details['id_mouvement']] = $ecriture[$details['id_ecriture']]->new_child("ligne_mouvements", "");
  		$mvmts[$details['id_mouvement']]->new_child("compte", $details['compte']);
  		$mvmts[$details['id_mouvement']]->new_child("compte_client", $details['cpte_interne_cli']);
  		if($details['sens'] == 'd'){
  			$mvmts[$details['id_mouvement']]->new_child("montant_debit", afficheMontant($details['montant']));
  		}else{
  			$mvmts[$details['id_mouvement']]->new_child("montant_credit", afficheMontant($details['montant']));
  		}
  	}

  return($document->dump_mem(true));

}

/**
 * Crée le xml pour la génération du rapport visualisation des transactions en deplace
 * @param array $DATA : tableau contenant les informations de l'opération passée
 * @return str Le document XML généré.
 */
function xml_operations_deplace($DATAS, $criteres)
{
    // Multi_agence includes
    require_once 'ad_ma/app/models/Divers.php';
    require_once 'ad_ma/app/models/AgenceRemote.php';
    require_once 'ad_ma/app/models/AuditVisualisation.php';
       
    global $adsys, $global_id_agence;
  
    $loginDistant = AuditVisualisation::LOGIN_DISTANT;
    $nom_agence_local = AgenceRemote::getRemoteAgenceName($global_id_agence);
    
    // flag for report type
    $isRapportClientsInterne = false;
  
    // check if local or remote access
    if(!empty($criteres['Login']) && $criteres['Login'] == $loginDistant) {
        $isRapportClientsInterne = true;
    }
    
    // generate reporting for clients internes, login = distant / ancien rapport distant
    if($isRapportClientsInterne) {
        $document = create_xml_doc("operations_deplace_clients_interne", "operations_deplace_clients_interne.dtd");
        $code_rapport = 'GUI-OPD-INT';
    }
    else { // generate reporting for clients externes, login = tous/specifique // ancien rapport local
        $document = create_xml_doc("operations_deplace_clients_externe", "operations_deplace_clients_externe.dtd");
        $code_rapport = 'GUI-OPD-EXT';
    }    
   
    //Element root
    $root = $document->root();
    //En-tête généraliste
    $header_nom_agence = " " . $nom_agence_local;
    $ref = gen_header($root, $code_rapport, $header_nom_agence);
    
    //En-tête contextuel
    $header_contextuel = $root->new_child("header_contextuel", "");
    $criteres_header = $criteres;  
    unset($criteres_header['criteres_recherche']);
    gen_criteres_recherche($header_contextuel, $criteres_header);
   
    //Corps   
    $transactions = $root->new_child("transactions", "");
    $summary = $root->new_child("summary", "");
    
    // Infos agences
    $infos_agences = $transactions->new_child("infos_agences", "");    
    $nom_agence_local = $infos_agences->new_child("nom_agence_locale", $nom_agence_local);
    
    $criteres_recherche = $criteres['criteres_recherche'];
    
    if(!empty($criteres['Agence'])) {
        $nom_agence_distante = $criteres['Agence'];
        $nom_agence_distante = $infos_agences->new_child("nom_agence_distante", $nom_agence_distante);
    }
   
    if($isRapportClientsInterne) { // get reporting data for clients internes
        get_operations_deplace_clients_interne($DATAS, $transactions);  
        get_summary_clients_interne($criteres_recherche, $summary);
    }
    else {  // get reporting data for clients externes        
        get_operations_deplace_clients_externe($DATAS, $transactions);
        get_summary_clients_externe($criteres_recherche, $summary);
    }
    
    $output = $document->dump_mem(true);      
    return($output);
}


/**
 * Crée le xml pour la génération du rapport visualisation des transactions en deplace, rapport local, clients distant
 * @return str Le document XML généré.
 */
function get_operations_deplace_clients_externe($DATAS, &$transactionsNode)
{   
    $loginDistant = AuditVisualisation::LOGIN_DISTANT;
    
    foreach ($DATAS as $details)
    {
        $transaction = $transactionsNode->new_child("transaction", '');
    
        // La date de la transaction
        $date = trim($details['date_maj']);
        $his_details_date_transac = $transaction->new_child("date_transac", $date);
    
        // les nodes local et distant
        $his_details_local = $transaction->new_child("his_data_local", "");
        $his_details_distant = $transaction->new_child("his_data_distant", "");
    
        // START : les infos local ////    
        $trans_local = trim($details['id_his_distant']);
        $agence_local = AgenceRemote::getRemoteAgenceName($details['id_ag_distant']);
        $login_local =  $loginDistant;
        $client_local = trim($details['id_client_distant']);        
            
        // xml nodes local :
        $trans_local = $his_details_local->new_child("trans_local", $trans_local);
        $login_local = $his_details_local->new_child("login_local", $login_local);
        $agence_local = $his_details_local->new_child("agence_local", $agence_local);     
        $client_local = $his_details_local->new_child("client_local", $client_local);
    
        // les infos ecritures local
        $ligne_ecritures_local = $his_details_local->new_child("ligne_ecritures_local", '');
    
        foreach ($details['ecritures_local'] as $key => $detail)
        {
            $ecriture_local = $ligne_ecritures_local->new_child("ecriture_local", "");
    
            if(!empty($detail['libel'])) {
                $libel_ecriture_local = trim($detail['libel']);
                $libel_ecriture_local = $ecriture_local->new_child("libel_ecriture_local", $libel_ecriture_local);
            }
            else {
                $libel_ecriture_local = trim($details['type_choix_libel']);
                $libel_ecriture_local = $ecriture_local->new_child("libel_ecriture_local", $libel_ecriture_local);
            }
             
            $ligne_mouvements_local = $ecriture_local->new_child("ligne_mouvements_local", "");
    
            foreach ($detail['mouvements'] as $mouvement)
            {
                $mouvement_local = $ligne_mouvements_local->new_child("mouvement_local", "");
                $compte_local = $mouvement_local->new_child("compte_local", $mouvement['compte']);
                $compte_client_local = $mouvement_local->new_child("compte_client_local", $mouvement['num_complet_cpte']);
                
                $montant = Divers::afficheMontant($mouvement['montant'], $mouvement["devise"], false, 2);               
                
                if($mouvement['sens'] == 'd') {
                    $montant_debit_local = $mouvement_local->new_child("montant_debit_local", $montant);
                    $montant_credit_local = $mouvement_local->new_child("montant_credit_local", '');
                }else{
                    $montant_debit_local =  $mouvement_local->new_child("montant_debit_local", '');
                    $montant_credit_local = $mouvement_local->new_child("montant_credit_local", $montant);
                }
    
            }
        }    
        /// END : les infos local /////////////////////
    
    
        /// START : les infos distant  ///////////////
    
        $trans_distant = trim($details['id_his_local']);
        //$fonction_distant = $type_transaction = Divers::getLibelleFonctionDeplace($details['type_transaction']);
        $agence_distant = AgenceRemote::getRemoteAgenceName($details['id_ag_local']);
        $login_distant =  $details['nom_login'];
        $login_distant = trim($login_distant);
        $client_distant = trim($details['id_client_distant']);
        
        // xml nodes distant :
        $trans_distant = $his_details_distant->new_child("trans_distant", $trans_distant);       
        $login_distant = $his_details_distant->new_child("login_distant", $login_distant);
        $agence_distant = $his_details_distant->new_child("agence_distant", $agence_distant);
            
        // les infos ecritures distant
        $ligne_ecritures_distant = $his_details_distant->new_child("ligne_ecritures_distant", '');        
                
        foreach ($details['ecritures_distant']['ecritures'] as $key => $detail)
        {
            $ecriture_distant = $ligne_ecritures_distant->new_child("ecriture_distant", "");
            $libel_ecriture_distant = $detail['libel_ecriture'];
            $libel_ecriture_distant = trim($libel_ecriture_distant);
            $libel_ecriture_distant = $ecriture_distant->new_child("libel_ecriture_distant",$libel_ecriture_distant);
             
            $ligne_mouvements_distant = $ecriture_distant->new_child("ligne_mouvements_distant", "");

            foreach ($detail['mouvements'] as $mouvement)
            {
                $mouvement_distant = $ligne_mouvements_distant->new_child("mouvement_distant", "");
                $compte_distant = $mouvement_distant->new_child("compte_distant", $mouvement['compte']);                
                $compte_client_distant = $mouvement_distant->new_child("compte_client_distant", $mouvement['num_complet_cpte']);
                               
                $montant = Divers::afficheMontant($mouvement['montant'], $mouvement["devise"], false, 2);
                
                if($mouvement['sens'] == 'd') {
                    $montant_debit_distant = $mouvement_distant->new_child("montant_debit_distant", $montant);
                    $montant_credit_distant = $mouvement_distant->new_child("montant_credit_distant", '');
                }else{
                    $montant_debit_distant =  $mouvement_distant->new_child("montant_debit_distant", '');
                    $montant_credit_distant = $mouvement_distant->new_child("montant_credit_distant", $montant);
                }
            }
        }
        /// END : les infos distant /////////////////////
    }
}

/**
 * Crée le xml des details de transactions pour la génération du rapport visualisation des transactions en deplace, 
 * rapport distante / clients internes
 * @param array $DATA : tableau contenant les informations de l'opération passée
 * @return Le document XML généré.
 */
function get_operations_deplace_clients_interne($DATAS, &$transactionsNode)
{   
    foreach ($DATAS as $details)
    {
        $transaction = $transactionsNode->new_child("transaction", '');
    
        // La date de la transaction
        $date = trim($details['date_maj']);
        $his_details_date_transac = $transaction->new_child("date_transac", $date);
    
        // les nodes local et distant
        $his_details_local = $transaction->new_child("his_data_local", "");
        $his_details_distant = $transaction->new_child("his_data_distant", "");
    
        $loginDistant = AuditVisualisation::LOGIN_DISTANT;
        
        // START : les infos local ////
    
        $trans_local = trim($details['id_his_distant']);
        $login_local =  $loginDistant;
        $agence_local = AgenceRemote::getRemoteAgenceName($details['id_ag_distant']);       
        $client_local = trim($details['id_client_distant']);
    
        // xml nodes local :
        $trans_local = $his_details_local->new_child("trans_local", $trans_local);
        $login_local = $his_details_local->new_child("login_local", $login_local);
        $agence_local = $his_details_local->new_child("agence_local", $agence_local);
        $client_local = $his_details_local->new_child("client_local", $client_local);
    
        // les infos ecritures local
        $ligne_ecritures_local = $his_details_local->new_child("ligne_ecritures_local", '');
    
        foreach ($details['ecritures_local'] as $key => $detail)
        {
            $ecriture_local = $ligne_ecritures_local->new_child("ecriture_local", "");
    
            if(!empty($detail['libel'])) {
                $libel_ecriture_local = trim($detail['libel']);
                $libel_ecriture_local = $ecriture_local->new_child("libel_ecriture_local", $libel_ecriture_local);
            }
            else {
                $libel_ecriture_local = trim($details['type_choix_libel']);
                $libel_ecriture_local = $ecriture_local->new_child("libel_ecriture_local", $libel_ecriture_local);
            }
             
            $ligne_mouvements_local = $ecriture_local->new_child("ligne_mouvements_local", "");
    
            foreach ($detail['mouvements'] as $mouvement)
            {
                $mouvement_local = $ligne_mouvements_local->new_child("mouvement_local", "");
                $compte_local = $mouvement_local->new_child("compte_local", $mouvement['compte']);
                $compte_client_local = $mouvement_local->new_child("compte_client_local", $mouvement['num_complet_cpte']);
                       
                $montant = Divers::afficheMontant($mouvement['montant'], $mouvement["devise"], false, 2);
                
                if($mouvement['sens'] == 'd') {
                    $montant_debit_local = $mouvement_local->new_child("montant_debit_local", $montant);
                    $montant_credit_local = $mouvement_local->new_child("montant_credit_local", '');
                }else{
                    $montant_debit_local =  $mouvement_local->new_child("montant_debit_local", '');
                    $montant_credit_local = $mouvement_local->new_child("montant_credit_local", $montant);
                }
    
            }
        }
    
        /// END : les infos local /////////////////////
    
    
        /// START : les infos distant  ///////////////
    
        $trans_distant = trim($details['id_his_local']); 
        $login_distant =  $details['nom_login'];
        $login_distant = trim($login_distant);
        $agence_distant = AgenceRemote::getRemoteAgenceName($details['id_ag_local']);             
         
        // xml nodes distant :
        $trans_distant = $his_details_distant->new_child("trans_distant", $trans_distant);       
        $login_distant = $his_details_distant->new_child("login_distant", $login_distant);
        $agence_distant = $his_details_distant->new_child("agence_distant", $agence_distant);       
    
        // les infos ecritures distant
        $ligne_ecritures_distant = $his_details_distant->new_child("ligne_ecritures_distant", '');
               
        foreach ($details['ecritures_distant'] as $key => $detail)
        {
            $ecriture_distant = $ligne_ecritures_distant->new_child("ecriture_distant", "");
             
            $libel_ecriture_distant = $detail['libel_ecriture'];
            $libel_ecriture_distant = trim($libel_ecriture_distant);
            $libel_ecriture_distant = $ecriture_distant->new_child("libel_ecriture_distant", $libel_ecriture_distant);
             
            $ligne_mouvements_distant = $ecriture_distant->new_child("ligne_mouvements_distant", "");
             
            foreach ($detail['mouvements'] as $mouvement)
            {
                $mouvement_distant = $ligne_mouvements_distant->new_child("mouvement_distant", "");
                $compte_distant = $mouvement_distant->new_child("compte_distant", $mouvement['compte']);               
                                 
                $montant = Divers::afficheMontant($mouvement['montant'], $mouvement["devise"], false, 2);
                
                if($mouvement['sens'] == 'd') {
                    $montant_debit_distant = $mouvement_distant->new_child("montant_debit_distant", $montant);
                    $montant_credit_distant = $mouvement_distant->new_child("montant_credit_distant", '');
                }else{
                    $montant_debit_distant =  $mouvement_distant->new_child("montant_debit_distant", '');
                    $montant_credit_distant = $mouvement_distant->new_child("montant_credit_distant", $montant);
                }
            }
        }    
    
        // END : les infos distant       
    }
}

/**
 * Genere le xml pour le sommaire des transactions pour clients interne a l'agence
 * 
 * @param array $criteres
 * @param $summaryNode
 */
function get_summary_clients_interne($criteres, &$summaryNode)
{   
    $id_agence_ext = $criteres['IdAgence'];
        
    if(!empty($id_agence_ext))   
    {
        $summary = AuditVisualisation::getSummaryForVisualisationClientsInterne($criteres);
        $agence_locale = $summaryNode->new_child("agence_locale", $summary['agence_locale']);
        $agence_externe = $summaryNode->new_child("agence_externe", $summary['agence_externe']);
        $total_depot = $summaryNode->new_child("total_depot", $summary['total_depot']);
        $total_retrait = $summaryNode->new_child("total_retrait", $summary['total_retrait']);
    }    
}


/**
 * Genere le xml pour le sommaire des transactions pour clients externe a l'agence
 *
 * @param array $criteres
 * @param $summaryNode
 * 
 */
function get_summary_clients_externe($criteres, &$summaryNode)
{    
    $summary = AuditVisualisation::getSummaryForVisualisationClientsExterne($criteres);

    $rows_summary_node = $summaryNode->new_child("rows_summary", "");
    $grand_summary_node = $summaryNode->new_child("grand_summary", "");
        
    foreach ($summary['rows_summary'] as $row_summary) {
        $row = $rows_summary_node->new_child("row", "");
        $agence_locale = $row->new_child("agence_locale", $row_summary['agence_locale']);
        $agence_externe = $row->new_child("agence_externe", $row_summary['agence_externe']);
        $total_depot = $row->new_child("total_depot", $row_summary['total_depot']);
        $total_retrait = $row->new_child("total_retrait", $row_summary['total_retrait']);
    }
    
    $agence = $grand_summary_node->new_child("agence", $summary['grand_summary']['agence']);    
    $grand_total_depot = $grand_summary_node->new_child("grand_total_depot", $summary['grand_summary']['grand_total_depot']);
    $grand_total_retrait = $grand_summary_node->new_child("grand_total_retrait", $summary['grand_summary']['grand_total_retrait']);       
}
