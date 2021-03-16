<?php
/**
 * User: rajeev
 */

require_once('/usr/share/adbanking/web/lib/vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Channel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Defini les modelisations des different types de publishers pour les envois de SMS mouvements
 * Class BaseMSQPublisher
 */
abstract class BaseMSQPublisher
{
    /**
     * RabbitMQ server host
     * @var
     */
    protected $host;
    
    /**
     * RabbitMQ server port
     * @var
     */
    protected $port;
    
    /**
     * RabbitMQ server username
     * @var
     */
    protected $username;
    
    /**
     * RabbitMQ server password
     * @var
     */
    protected $password;
    
    /**
     * @var
     */
    protected $connection;
    
    /**
     * RabbitMQ queue to publish to
     * @var
     */
    protected $queueName;
    
    /**
     * RabbitMQ routing key
     * @var
     */
    protected $routingKey;
    
    /**
     * RabbitMQ Message
     * @var BaseMSQMessage
     */
    protected $amqMessage;
    
    /**
     * RabbitMQ Channel
     * @var PhpAmqpLib\Channel;
     */
    protected $channel;
    
    /**
     * RabbitMQ exchange
     * @var
     */
    protected $exchangeName;
    
    /**
     * RabbitMQ Exchange type
     * Can be direct, topic, headers or fanout
     * @var
     */
    protected $exchangeType;
    
    /**
     * RabbitMQ virtualHost
     * @var
     */
    protected $virtualHost;
    
    
    /**
     * The default exchange type is 'topic'
     * BaseMSQPublisher constructor.
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     * @param $defaultQueueName
     * @param $defaultRoutingKey
     * @param $exchangeName
     * @param $virtualHost
     * @param null $exchangeType
     */
    public function __construct($host, $port, $username, $password, $defaultQueueName, $defaultRoutingKey, $exchangeName, $virtualHost, $exchangeType = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->queueName = $defaultQueueName;
        $this->routingKey = $defaultRoutingKey;
        $this->exchangeName = $exchangeName;
        $this->virtualHost = $virtualHost;
        empty($exchangeType) ? $this->exchangeType = 'topic' : $this->exchangeType = $exchangeType;
    }
    
    /************************* Method signatures for message queue ******************************/
    
    /**
     * Method signature to define the message body, depending on type of SMS (Mouvement, abonnement etc)
     * @param $data
     * @return mixed
     */
    abstract protected function generateMessageBody($data);
    
    /**
     * Helper function to publish a single message
     * @return mixed
     */
    abstract protected function publish();
    
    /**
     *  todo : implement if required
     * Helper function to batch publish messages
     * @return mixed
     */
    abstract protected function batchPublish();
    
    /**
     * Public function to process messages
     * @param $rawMessage
     * @return mixed
     */
    abstract public function process($rawMessage, $bindingKey = NULL);
    
    /**
     *  todo : implement if required
     * Public function to batch process messages
     * @param $listMessage
     * @return mixed
     */
    abstract public function processBatch($listMessage);
    
    /************************* Routine functions ******************************/

    /**
     * Initialises the MSQ publisher, sets up the connection / channel and exchange
     */
    public function init()
    {
        $connection = new AMQPStreamConnection($this->host, $this->port, $this->username, $this->password, $this->virtualHost);
        $this->setConnection($connection);
        $channel = $connection->channel();
        $this->setChannel($channel);
        $channel->exchange_declare($this->exchangeName, $this->exchangeType, false, true, false);
    }

    /**
     * Shutdown connection to server
     */
    public function shutdown()
    {
        $this->getChannel()->close();
        $this->getConnection()->close();
    }
    
    /**
     * Initialises a rabbitMQ queue and sets the binding
     * @param $routingKey
     * @param $queueName
     * @param bool $durable
     * @param bool $persistent
     * @param bool $autodelete
     */
    public function initQueue($routingKey, $queueName = null, $durable = true, $autodelete = false)
    {
        try {
            $channel = $this->getChannel();

            // Set the publisher's queue
            if (empty($queueName) && !empty($this->queueName)) {
                $queueName = $this->queueName;
            } else
                $this->setQueueName($queueName);
            
            // Sets the routing key for the queue
            $this->setRoutingKey($routingKey);
            //Create queue or use existing (define as persistent)
            $channel->queue_declare($queueName, false, $durable, false, $autodelete);
            // Do the binding of queue to exchange to route messages to correct queue
            $channel->queue_bind($queueName, $this->exchangeName, $routingKey);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /****************************** Common tasks ***********************************/
    
    /**
     * Create the MSQ message and set it to publisher
     * @param $rawMessage
     * @return mixed|void
     */
    protected function createMSQMessage($rawMessage)
    {
        if (is_array($rawMessage)) {
            $jsonMessage = json_encode($rawMessage);
        }

        $properties = array(
          'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
          'content_type' => 'application/json'
        );

        $message = new BaseMSQMessage($jsonMessage, $properties);
        $this->setAmqMessage($message);
    }

    /**
     * TODO complete phpdoc
     * Create the MSQ message from table ""
     * @param $encodedMessage
     */
    protected function createMSQMessageBatch($encodedMessage)
    {
        $properties = array(
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'application/json'
        );

        $message = new BaseMSQMessage($encodedMessage, $properties);
        $this->setAmqMessage($message);
    }

    /**
     * Insert unconfirmed message into ad_msq table
     *
     * @param $datas
     * @param int $statut
     * @param int $typeMSG
     */
    public function insertIntoFailoverTable($datas, $statut = BaseMSQMessage::STATUT_TRAITEMENT_A_TRAITER, $typeMSG = BaseMSQMessage::TYPE_MSG_MOUVEMENT)
    {
        //TODO : remove ' on each value from the value
        foreach ($datas as $key => &$value){
            $value = str_replace("'", "", $value);
        }

        $encodedMessageMSQ = json_encode($datas);

        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        // Ajouter une ligne dans la table ad_msq
        $sql_insert = "INSERT INTO ad_msq (encoded_message, date_creation, statut, type_msg, id_ag) VALUES ('" .$encodedMessageMSQ. "', now(), $statut, $typeMSG, $global_id_agence);";
        $result_insert = $db->query($sql_insert);
        if (DB::isError($result_insert)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
        $dbHandler->closeConnection(true);
    }
    
    
    /************************* Generic Getters and setters *****************************************/
    
    /**
     * @return BaseMSQMessage
     */
    public function getAmqMessage()
    {
        return $this->amqMessage;
    }
    
    /**
     * @param BaseMSQMessage $amqMessage
     */
    public function setAmqMessage($amqMessage)
    {
        $this->amqMessage = $amqMessage;
    }
    
    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }
    
    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }
    
    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
    
    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * @param mixed $connection
     */
    public function setConnection(&$connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * @return mixed
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
    
    /**
     * @param mixed $queueName
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }
    
    /**
     * @return mixed
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }
    
    /**
     * @param mixed $routingKey
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
    }
    
    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }
    
    /**
     * @param Channel $channel
     */
    public function setChannel(&$channel)
    {
        $this->channel = $channel;
    }
    
    /**
     * @return mixed
     */
    public function getExchangeName()
    {
        return $this->exchangeName;
    }
    
    /**
     * @param mixed $exchangeName
     */
    public function setExchangeName($exchangeName)
    {
        $this->exchangeName = $exchangeName;
    }
    
    /**
     * @return mixed
     */
    public function getExchangeType()
    {
        return $this->exchangeType;
    }
    
    /**
     * @param mixed $exchangeType
     */
    public function setExchangeType($exchangeType)
    {
        $this->exchangeType = $exchangeType;
    }
    
    /**
     * @return mixed
     */
    public function getVirtualHost()
    {
        return $this->virtualHost;
    }
    
    /**
     * @param mixed $virtualHost
     */
    public function setVirtualHost($virtualHost)
    {
        $this->virtualHost = $virtualHost;
    }
    
}