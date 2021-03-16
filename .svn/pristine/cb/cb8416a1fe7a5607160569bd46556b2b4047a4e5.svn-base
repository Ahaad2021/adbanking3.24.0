<?php

require_once 'BaseMSQPublisher.php';
require_once 'BaseMSQMessage.php';

class MobLendingMSQPublisher extends BaseMSQPublisher 
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
    protected function generateMessageBody($data)
    {
        // TODO : Implement generateMessageBody() method
    }

    /**
     * todo : implement if required
     * @return mixed|void
     */
    protected function batchPublish()
    {
        // TODO: Implement batchPublish() method.
    }

    //----------------- PUBLIC EXPOSED FUNCTIONS ------------------------------//
    
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
            $this->insertIntoFailoverTable($rawMessage, BaseMSQMessage::STATUT_TRAITEMENT_A_TRAITER, BaseMSQMessage::TYPE_MSG_MOB_LENDING);

            return false;
        }
    }

    /**
     * Get remaining data to build message
     * 
     * @param $idClient
     * @param $idDoss
     * @param $mntDem
     * @return |null
     */
    static function getRemMobLendingData($idClient, $idDoss, $mntDem)
    {
        // sql query
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT * FROM f_getRemMobLendingDataForProducer('" .$idClient. "','" .$idDoss. "','" .$mntDem. "')";

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
?>