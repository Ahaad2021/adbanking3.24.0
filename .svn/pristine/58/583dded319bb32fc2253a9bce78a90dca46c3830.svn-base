<?php

use PhpAmqpLib\Message\AMQPMessage;

require_once 'ad_ma/app/models/BaseModel.php';
require_once('/usr/share/adbanking/web/lib/vendor/autoload.php');

class MouvementPublisher extends BaseModel
{
    const STATUT_TRAITEMENT_A_TRAITER = 1;
    const TYPE_MSG_MOUVEMENT = 1;

    public function __construct(&$dbc, $id_agence = NULL)
    {
        parent::__construct($dbc, $id_agence);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Verifier si le message queue system est active ou pas
     * @return bool
     */
    public function isMSQEnabled()
    {
        global $MSQ_ENABLED;

        is_null($MSQ_ENABLED) ? $conditionMsq = false : $conditionMsq = true;
        return $conditionMsq;
    }

    /**
     * @param $cpteInterneCli
     * @param $montant
     * @param $dateValeur
     * @param $solde
     * @return |null
     */
    public function getMouvementData($cpteInterneCli, $montant, $dateValeur, $solde)
    {
        $sql = "SELECT * FROM f_getMouvementForProducer('" .$cpteInterneCli. "'," .$montant. ",'" .$dateValeur. "'," .$this->getIdAgence(). ",'" .$solde. "')";

        $result = $this->getDbConn()->prepareFetchRow($sql);

        if ($result === FALSE || count($result) == 0) {
            return NULL;
        }

        return $result;
    }

    /**
     * @param $datas
     * @param int $statut
     * @param int $typeMSG
     */
    public function insertIntoFailoverTable($datas, $statut = MouvementPublisher::STATUT_TRAITEMENT_A_TRAITER, $typeMSG = MouvementPublisher::TYPE_MSG_MOUVEMENT)
    {
        //TODO : remove ' on each value from the value
        foreach ($datas as $key => &$value){
            $value = str_replace("'", "", $value);
        }

        $encodedMessageMSQ = json_encode($datas);

        $idAgence = $this->getIdAgence();

        $sql = "INSERT INTO ad_msq (encoded_message, date_creation, statut, type_msg, id_ag) VALUES ('" .$encodedMessageMSQ. "', now(), $statut, $typeMSG, $idAgence);";

        $result = $this->getDbConn()->execute($sql);

        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
    }



    /**
     * Envoi de message sur le broker si le array contienne des type operation fesant l'objet d'envoi de sms
     *
     * @param $array_comptable
     */
    public function envoiSmsMouvement($array_comptable)
    {
        global $code_imf;

        $ClientObj = new Client($this->getDbConn(), $this->getIdAgence());
        $EpargneObj = new Epargne($this->getDbConn(), $this->getIdAgence());

        if (!empty($array_comptable)) {
            foreach ($array_comptable as $k => $val){
                if (isset($val['cpte_interne_cli'])){
                    if (is_array($client = $ClientObj->getClientAbnByIdCpte($val['cpte_interne_cli'])) && $cpt_epargne = $EpargneObj->getComptesEpargne($client["id_client"]) ) {

                        $listeTypeOpt = $EpargneObj->getListeTypeOptDepPourPreleveFraisSMS();

                        if (in_array($val['type_operation'], $listeTypeOpt)) {
                            // get the necessary data to send as message to the broker
                            $cpte_interne_cli = $val['cpte_interne_cli'];
                            $montant = $val['montant'];
                            $date_valeur = date('Y-m-d', strtotime(str_replace('/', '-', $val['date_valeur'])));

                            $datas = $this->getMouvementData($cpte_interne_cli, $montant, $date_valeur, $val['solde_msq']);

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

                            $this->producerSMS($rawMessage);
                        }
                    }
                }
            }
        }
    }

    /**
     * Publication du message sur le broker | Inserer dans la table ad_msq si on a un souci avec le broker
     *
     * @param $rawMessage
     */
    public function producerSMS($rawMessage)
    {
        global $code_imf, $MSQ_HOST, $MSQ_PORT, $MSQ_USERNAME, $MSQ_PASSWORD, $MSQ_VHOST;
        global $MSQ_EXCHANGE_NAME, $MSQ_EXCHANGE_TYPE, $MSQ_QUEUE_NAME_MOUVEMENT, $MSQ_ROUTING_KEY_MOUVEMENT;

        try {
            $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                $MSQ_HOST,
                $MSQ_PORT,
                $MSQ_USERNAME,
                $MSQ_PASSWORD,
                $MSQ_VHOST

            );

            $channel = $connection->channel();

            $channel->exchange_declare(
                $MSQ_EXCHANGE_NAME,
                $MSQ_EXCHANGE_TYPE,
                false,
                true,
                false
            );

            if (is_array($rawMessage)) {
                $jsonMessage = json_encode($rawMessage);
            }

            $properties = array(
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type' => 'application/json'
            );

            $msq = new \PhpAmqpLib\Message\AMQPMessage($jsonMessage, $properties);

            $data = $channel->basic_publish($msq, $MSQ_EXCHANGE_NAME, $MSQ_ROUTING_KEY_MOUVEMENT);

        } catch (Exception $e) {
            $this->insertIntoFailoverTable($rawMessage);
        }
    }
}