<?php

require_once 'BaseMSQPublisher.php';
require_once 'BaseMSQMessage.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Channel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Modelise un publisher pour les SMS mouvements
 * Class MouvementMSQPublisher
 */
class MouvementMSQPublisher extends BaseMSQPublisher
{
    //----------------- IMPLEMENTATIONS ------------------------------//

    /**
     * @param $message
     * @return mixed|void
     */
    protected function publish($bindingKey = NULL)
    {
        if (!empty($this->amqMessage)) {
            $channel = $this->getChannel();
            if (empty($bindingKey)) {
                $bindingKey = $this->routingKey;
            }
            $channel->basic_publish($this->amqMessage, $this->exchangeName,$bindingKey);
        }
    }

    /**
     * todo : implement if required
     * @return mixed|void
     */
    protected function batchPublish()
    {
        // TODO: Implement batchPublish() method.
    }
    
    /**
     * Used to generate a message body for mouvement / abonnement etc
     * @param $data
     * @return array
     */
    protected function generateMessageBody($data)
    {
        $body = array();
        
        if (is_array($data))
        {
            if (array_key_exists('telephone', $data) && !empty($data['telephone']))
                $body['telephone'] = $data['telephone'];
            
            if (array_key_exists('langue', $data) && !empty($data['langue']))
                $body['langue'] = $data['langue'];
            
            if (array_key_exists('date_transaction', $data) && !empty($data['date_transaction']))
                $body['date_transaction'] = $data['date_transaction'];
            
            if (array_key_exists('type_opt', $data) && !empty($data['type_opt']))
                $body['type_opt'] = $data['type_opt'];
            
            if (array_key_exists('sens', $data) && !empty($data['sens']))
                $body['sens'] = $data['sens'];
            
            if (array_key_exists('num_complet_compte', $data) && !empty($data['num_complet_compte']))
                $body['num_complet_compte'] = $data['num_complet_compte'];
            
            if (array_key_exists('id_mouvement', $data) && !empty($data['id_mouvement']))
                $body['id_mouvement'] = $data['id_mouvement'];
            
            if (array_key_exists('id_ag', $data) && !empty($data['id_ag']))
                $body['id_ag'] = $data['id_ag'];
            
            // Todo : this one will be problematic
            if (array_key_exists('code_imf', $data) && !empty($data['code_imf']))
                $body['code_imf'] = $data['code_imf'];
            
            if (array_key_exists('libelle_ecriture', $data) && !empty($data['libelle_ecriture']))
                $body['libelle_ecriture'] = $data['libelle_ecriture'];
            
            if (array_key_exists('montant', $data) && !empty($data['montant']))
                $body['montant'] = $data['montant'];
            
            if (array_key_exists('devise', $data) && !empty($data['devise']))
                $body['devise'] = $data['devise'];
            
            if (array_key_exists('nom', $data) && !empty($data['nom']))
                $body['nom'] = $data['nom'];
            
            if (array_key_exists('prenom', $data) && !empty($data['prenom']))
                $body['prenom'] = $data['prenom'];
            
            if (array_key_exists('solde', $data) && !empty($data['solde']))
                $body['solde'] = $data['solde'];
            
            if (array_key_exists('intitule_compte', $data) && !empty($data['intitule_compte']))
                $body['intitule_compte'] = $data['intitule_compte'];
            
            if (array_key_exists('libelle_produit', $data) && !empty($data['libelle_produit']))
                $body['libelle_produit'] = $data['libelle_produit'];
            
            if (array_key_exists('communication', $data) && !empty($data['communication']))
                $body['communication'] = $data['communication'];
            
            if (array_key_exists('tireur', $data) && !empty($data['tireur']))
                $body['tireur'] = $data['tireur'];
            
            if (array_key_exists('donneur', $data) && !empty($data['donneur']))
                $body['donneur'] = $data['donneur'];
            
            if (array_key_exists('numero_cheque', $data) && !empty($data['numero_cheque']))
                $body['numero_cheque'] = $data['numero_cheque'];
            
            if (array_key_exists('date_ouvert', $data) && !empty($data['date_ouvert']))
                $body['date_ouvert'] = $data['date_ouvert'];
            
            if (array_key_exists('statut_juridique', $data) && !empty($data['statut_juridique']))
                $body['statut_juridique'] = $data['statut_juridique'];
            
            if (array_key_exists('id_client', $data) && !empty($data['id_client']))
                $body['id_client'] = $data['id_client'];
            
            if (array_key_exists('id_transaction', $data) && !empty($data['id_transaction']))
                $body['id_transaction'] = $data['id_transaction'];
            
            if (array_key_exists('ref_ecriture', $data) && !empty($data['ref_ecriture']))
                $body['ref_ecriture'] = $data['ref_ecriture'];
        }
        
        return $body;
    }
    
    //----------------- PUBLIC EXPOSED FUNCTIONS ------------------------------//

    /**
     * Processing of MSQ message
     * @param $rawMessage
     * @return mixed|void
     * @throws Exception
     */
    public function process($rawMessage, $bindingKey = NULL){
        $this->createMSQMessage($rawMessage);
        $this->publish($bindingKey);
    }
    
    /**
     * todo : implement if required
     * @param $listMessage
     * @return mixed|void
     */
    public function processBatch($listMessage)
    {
        // TODO: Implement processBatch() method.
    }

    /**
     * @param $rawMessage
     * @param null $bindingKey
     * @throws Exception
     */
    public function executePublisher($rawMessage, $bindingKey = NULL)
    {
        try {
            $this->init();

            $this->process($rawMessage, $bindingKey);

            $this->shutdown();
            return true;
        } catch (Exception $e){
            // Failover Mechanism
            $this->insertIntoFailoverTable($rawMessage);

            return false;
        }
    }

    /**
     * Get the necessary data to build message to send to broker
     *
     * @param $cpteInterneCli
     * @param $montant
     * @param $dateComptable
     * @return |null
     */
    static function getMouvementData($cpteInterneCli, $montant, $dateValeur, $solde)
    {
        // sql query
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT * FROM f_getMouvementForProducer('" .$cpteInterneCli. "'," .$montant. ",'" .$dateValeur. "'," .$global_id_agence. ",'" .$solde. "')";

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__.' '.$sql);
        }

        $dbHandler->closeConnection(true);
        if ($result->numRows() == 0) {
            return NULL;
        }

        $datas = $result->fetchrow(DB_FETCHMODE_ASSOC);
        // FIN sql query

        return $datas;
    }

    /**
     * Get the necessary data to build message to send to broker
     *
     * @param $idMouvement
     * @param $solde
     * @param $dateTransaction
     * @return |null
     */
    static function getMouvementDataArreteCompte($idMouvement, $solde, $dateTransaction)
    {
        // sql query
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT * FROM f_getMouvementForProducerArreteCompteBatch(" .$idMouvement. "," .$global_id_agence. "," .$solde. ",'" .$dateTransaction. "')";

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__.' '.$sql);
        }

        $dbHandler->closeConnection(true);
        if ($result->numRows() == 0) {
            return NULL;
        }

        $datas = $result->fetchrow(DB_FETCHMODE_ASSOC);
        // FIN sql query

        return $datas;
    }

    /**
     * Get the necessary data to build message to send to broker
     *
     * @param $cpteInterneCli
     * @param $montant
     * @param $dateValeur
     * @return |null
     */
    static function getMouvementDataClotureCompte($cpteInterneCli, $montant, $dateValeur)
    {
        // sql query
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT * FROM f_getMouvementForProducerClotureCompteBatch('" .$cpteInterneCli. "'," .$montant. ",'" .$dateValeur. "'," .$global_id_agence. ")";

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__.' '.$sql);
        }

        $dbHandler->closeConnection(true);
        if ($result->numRows() == 0) {
            return NULL;
        }

        $datas = $result->fetchrow(DB_FETCHMODE_ASSOC);
        // FIN sql query
        return $datas;
    }
}