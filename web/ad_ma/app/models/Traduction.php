<?php

/**
 * Classe de gestion des strings traduits
 * Attention cette classe doit être chargée avant le démarrage de la session ???
 * @author Olivier Luyckx
 * @since janvier 2005
 * @package Multilingue
 */
require_once 'ad_ma/app/models/BaseModel.php';

class Traduction extends BaseModel {

    public function __construct(&$dbc, $id_agence = NULL) {
        parent::__construct($dbc, $id_agence);
    }

    public function __destruct() {
        parent::__destruct();
    }

    var $private_id_str;
    var $private_traductions;


    public function setTraduction($id_str='Not set')
        // Constructeur
    {
        if (isset($id_str) && is_numeric($id_str)) {
            $this->private_id_str = $id_str;
            $this->private_traductions = $this->db_get_traductions($id_str);
        }

    } // Fin constructeur Trad

    public function traduire($langue=NULL, $renvoie_null = false)
        // Renvoie la traduction dans la langue spécifiée (on passe le code langue)
        // Renvoie la traduction dans la langue de l'interface utilisateur si aucune langue n'est spécifiée
    {
        global $global_langue_utilisateur;
        if ($langue==NULL)
            $langue=$global_langue_utilisateur;
        return $this->private_get_traduction($langue, $renvoie_null);
    }

    function set_id_str($id_str) {
        if (isset($this->private_id_str))
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        $this->private_id_str = $id_str;
    }

    function get_id_str() {
        //if (!isset($this->private_id_str))
        //  signalErreur(__FILE__,__LINE__,__FUNCTION__);
        return $this->private_id_str;
    }

    ///////////////////
    // Partie PRIVEE //
    ///////////////////
    function private_get_traduction($langue, $renvoie_null = false)
        // SI renvoie_null est faux :
        // 	Renvoie la traduction (si elle existe) dans la langue spécifiée
        // 	Si elle n'existe pas, fournit la traduction dans la langue système par défaut
        // SI renvoie_null est vrai :
        // 	Renvoie la traduction (si elle existe) dans la langue spécifiée
        // 	Si elle n'existe pas, renvoie le string vide
    {
        $langue_systeme_par_defaut = $this->get_langue_systeme_par_defaut();
        if (isset($this->private_traductions[$langue]))
            return $this->private_traductions[$langue];
        else
            if ($renvoie_null == true)
                return '';
            else
                return $this->private_traductions[$langue_systeme_par_defaut];
    }

    public function db_get_traductions($id_str) {
        $retour = array();

        $sql = "SELECT langue,traduction FROM ad_traductions WHERE id_str= :id_str;";
        $param = array(":id_str" => $id_str);

        $results = $this->getDbConn()->prepareFetchAll($sql, $param);
        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        foreach($results as $row)
            $retour[$row["langue"]] = $row["traduction"];

        return $retour;
    }

    public function get_langue_systeme_par_defaut()
    // Renvoie le code langue de la langue système par défaut
    {
        global $global_langue_systeme_dft;

        // Optimisation
        if (isset($global_langue_systeme_dft))
            return $global_langue_systeme_dft;

        $sql = "SELECT langue_systeme_dft FROM ad_agc where id_ag= :id_agence;";
        $param = array(":id_agence" => $this->getIdAgence());

        $result = $results = $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__,__LINE__,__FUNCTION__);
        };
        if (count($result) != 1) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__,__LINE__,__FUNCTION__); // Il n'y a pas une et une seule agence
        };


        return $result["langue_systeme_dft"];
    }
}
?>