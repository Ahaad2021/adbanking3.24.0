<?php

require_once 'lib/misc/divers.php';
require_once 'services/misc_api.php';
include_once 'lib/misc/debug.php';
require_once 'lib/php-amqplib/MouvementMSQPublisher.php';
require_once 'lib/php-amqplib/MobLendingMSQPublisher.php'; 

/**
 * Envoi de message sur le broker si le array contienne des type operation fesant l'objet d'envoi de sms
 * @param $array_comptable
 */
function envoi_sms_mouvement($array_comptable)
{
    if (!empty($array_comptable)) {
        foreach ($array_comptable as $k => $val){
            if (isset($val['cpte_interne_cli'])){
                if (is_array($client = checkClientAbonnement($val['cpte_interne_cli'])) && $cpt_epargne = get_comptes_epargne($client["id_client"]) ) {

                    $listeOptEnvoiSMS = array();
                    $typeOptEnvoiSMS = getListeTypeOptPourPreleveFraisSMS();
                    foreach ($typeOptEnvoiSMS as $key => $value) {
                        foreach ($value as $item => $typeOpt) {
                            array_push($listeOptEnvoiSMS, $typeOpt);
                        }
                    }

                    if (in_array($val['type_operation'], $listeOptEnvoiSMS)) {

                        global $code_imf,$MSQ_HOST, $MSQ_PORT, $MSQ_USERNAME, $MSQ_PASSWORD, $MSQ_VHOST;
                        global $MSQ_EXCHANGE_NAME, $MSQ_EXCHANGE_TYPE, $MSQ_QUEUE_NAME_MOUVEMENT, $MSQ_ROUTING_KEY_MOUVEMENT;

                        // get the necessary data to send as message to the broker
                        $cpte_interne_cli = $val['cpte_interne_cli'];
                        $montant = $val['montant'];
                        $date_valeur = date('Y-m-d', strtotime(str_replace('/', '-', $val['date_valeur'])));

                        $datas = MouvementMSQPublisher::getMouvementData($cpte_interne_cli, $montant, $date_valeur, $val['solde_msq']);

                        $rawMessage = array(
                            'telephone' => $datas['telephone'],
                            'langue' =>$datas['langue'],
                            'date_transaction' => $datas['date_transaction'],
                            'type_opt' => $datas['type_opt'],
                            'sens' => $datas['sens'],
                            'num_complet_compte' => $datas['num_complet_cpte'],
                            'id_mouvement' => $datas['id_mouvement'],
                            'id_ag' => $datas['id_ag'],
                            'code_imf' => $code_imf,
                            'libelle_ecriture' => $datas['libelle_ecriture'],
                            'montant' => $datas['montant'],
                            'devise' => $datas['devise'],
                            'nom' => $datas['nom'],
                            'prenom' => $datas['prenom'],
                            'solde' => $datas['solde'],
                            'intitule_compte' => $datas['intitule_compte'],
                            'libelle_produit' => $datas['libelle_produit'],
                            'communication' => $datas['communication'],
                            'tireur' => $datas['tireur'],
                            'donneur' => $datas['donneur'],
                            'numero_cheque' => $datas['numero_cheque'],
                            'date_ouvert' => $datas['date_ouvert'],
                            'statut_juridique' => $datas['statut_juridique'],
                            'id_client' => $datas['id_client'],
                            'id_transaction' => $datas['id_transaction'],
                            'ref_ecriture' => $datas['ref_ecriture'],
                        );

                        //--------- Instantiates the publisher ---------------//
                        $mouvementPublisher = new MouvementMSQPublisher(
                            $MSQ_HOST,
                            (int)$MSQ_PORT,
                            $MSQ_USERNAME,
                            $MSQ_PASSWORD,
                            $MSQ_QUEUE_NAME_MOUVEMENT,
                            $MSQ_ROUTING_KEY_MOUVEMENT,
                            $MSQ_EXCHANGE_NAME,
                            $MSQ_VHOST
                        );

                        // Executes publish process
                        $mouvementPublisher->executePublisher($rawMessage);
                    }
                }
            }
        }
    }
}

/**
 * Verifier si le message queue system est active ou pas
 * @return bool
 */
function isMSQEnabled(){
    global $MSQ_ENABLED;

    is_null($MSQ_ENABLED) ? $conditionMsq = false : $conditionMsq = true;
    return $conditionMsq;
}

/**
 * Envoie de message sur le broker par rapport au deboursement de fond ou rejet du dossier de credit
 * 
 * @param $prenom
 * @param $id_client
 * @param $id_cpte_base
 * @param $id_doss
 * @param $mnt_dem
 * @param $taux_int
 * @param $statut
 * @param $premier_date_ech
 */
function envoi_sms_credit_mob_lending($prenom, $id_client, $id_cpte_base, $id_doss, $mnt_dem, $taux_int, $statut, $premier_date_ech = null)
{
    if(!empty($id_cpte_base)){
        if (is_array($client = checkClientAbonnement($id_cpte_base))) {
            
            $listeOptEnvoiSMS = array();
            $typeOptEnvoiSMS = getListeTypeOptPourPreleveFraisSMS();
            foreach ($typeOptEnvoiSMS as $key => $value) {
                foreach ($value as $item => $typeOpt) {
                    array_push($listeOptEnvoiSMS, $typeOpt);
                }
            }
           
            // 210 est le type_opt pour le deboursement de fond
            if (in_array(210, $listeOptEnvoiSMS)) {
                global $code_imf,$MSQ_HOST, $MSQ_PORT, $MSQ_USERNAME, $MSQ_PASSWORD, $MSQ_VHOST;
                global $MSQ_EXCHANGE_NAME, $MSQ_EXCHANGE_TYPE, $MSQ_QUEUE_NAME_MOB_LENDING, $MSQ_ROUTING_KEY_MOB_LENDING;

                // get the remaining data to send as message to the broker
                $datas = MobLendingMSQPublisher::getRemMobLendingData($id_client, $id_doss, $mnt_dem);

                $rawMessage = array(
                    'code_imf' => $code_imf,
                    'montant' => $mnt_dem,
                    'statut' => $statut,
                    'telephone' => $datas['num_sms'],
                    'taux_interet' => $taux_int,
                    'date_ech' => $premier_date_ech,
                    'id_doss' => $id_doss,
                    'id_trans_ext' => $datas['id_transaction'],
                    'langue_id' => $datas['langue'],
                    'prenom' => $prenom,
                    'num_imf' => $datas['num_imf']
                );

                //--------- Instantiates the publisher ---------------//
                $mlCreditPublisher = new MobLendingMSQPublisher(
                    $MSQ_HOST,
                    (int)$MSQ_PORT,
                    $MSQ_USERNAME,
                    $MSQ_PASSWORD,
                    $MSQ_QUEUE_NAME_MOB_LENDING,
                    $MSQ_ROUTING_KEY_MOB_LENDING,
                    $MSQ_EXCHANGE_NAME,
                    $MSQ_VHOST
                );

                // Executes publish process
                $mlCreditPublisher->executePublisher($rawMessage);
            }
        }
    }
}
?>