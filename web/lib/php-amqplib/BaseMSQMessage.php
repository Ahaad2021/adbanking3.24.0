<?php
/**
 * User: rajeev
 */

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Modelise un message rabbitMQ pour les besoins d'envoi de SMS mouvement
 * Class BaseMSQMessage
 */
class BaseMSQMessage extends AMQPMessage
{
    /**
     * Les differents statut de traitement;
     */

    const STATUT_TRAITEMENT_A_TRAITER = 1;
    const STATUT_TRAITEMENT_EN_COURS = 2;
    const STATUT_TRAITEMENT_SUCCES = 3;
    const STATUT_TRAITEMENT_ECHEC = 4;

    const TYPE_MSG_MOUVEMENT = 1;
    const TYPE_MSG_MOB_LENDING = 2;
    
    /**
     * @var
     */
    protected $dateCreation;
    
    /**
     * @var
     */
    protected $dateTraitement;
    
    /**
     * @var
     */
    protected $statutTraitement;
    
    /**
     * @var integer
     */
    protected $nbRetry;
    
    
    /*************** Overrides ******************/
    
    /**
     * BaseMSQMessage constructor.
     * @param string $body
     * @param array $properties
     */
    public function __construct($body = '', array $properties = array())
    {
        parent::__construct($body, $properties);
    }
    
    
    /**
     * todo: implement correctly
     */
    protected function archiveMessage()
    {
        global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
        global $dbHandler, $global_multidevise;
        global $global_monnaie, $global_remote_monnaie;
        
        $body = $this->getBody();
    
        //pour pouvoir commit ou rollback toute la procédure
        $db = $dbHandler->openConnection();
        $sql = "INSERT "; // etc
        
        // Do save to database stuff here:
        // call adbanking function or create one
    
        // commit or rollback
        $dbHandler->closeConnection(true);
        
    }
    
    
    /*************** Getters and setters **********/
    
    /**
     * @return mixed
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }
    
    /**
     * @param mixed $dateCreation
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }
    
    /**
     * @return mixed
     */
    public function getDateTraitement()
    {
        return $this->dateTraitement;
    }
    
    /**
     * @param mixed $dateTraitement
     */
    public function setDateTraitement($dateTraitement)
    {
        $this->dateTraitement = $dateTraitement;
    }
    
    /**
     * @return mixed
     */
    public function getStatutTraitement()
    {
        return $this->statutTraitement;
    }
    
    /**
     * @param mixed $statutTraitement
     */
    public function setStatutTraitement($statutTraitement)
    {
        $this->statutTraitement = $statutTraitement;
    }
    
    /**
     * @return int
     */
    public function getNbRetry()
    {
        return $this->nbRetry;
    }
    
    /**
     * @param int $nbRetry
     */
    public function setNbRetry($nbRetry)
    {
        $this->nbRetry = $nbRetry;
    }
    
}