<?php
Class HTML_Champs_Extras {
	const  CHAR_LIAISON = '__';
	const CHAMPS_EXTRAS_HTML_ESPACE = 'espace_champs_extras';
	const CHAMPS_EXTRAS_HTML_PREFIX_NAME = 'champs_extras';
	private  $Myform =null;
	private $tableName_id ="";
	private  $tableName = NULL;
	private $values_champs_extras = array();
	private $champsExtras = array();
	public function HTML_Champs_Extras (&$Myform,$tableName,$tableName_id) {
		$this->Myform = $Myform;
		$this->tableName = $tableName;
		$this->tableName_id = $tableName_id;
	}
	private function  addChamps($nameField ) {
		array_push($this->champsExtras,$nameField);
	}
	public function getChampsExtras() {
		return $this->champsExtras;
	}
	static  function buildDataChampsEXtrasValues($tabChampsExtras, $tabDataPost) {
		$tab = array();
		if (is_array($tabChampsExtras) && count($tabChampsExtras) > 0) {
			foreach ($tabChampsExtras as $ChampsName ) {
				$tabtemp = split(self:: CHAR_LIAISON, $ChampsName);
				$id_champs_extras = $tabtemp[1];
				//$tabableName_id = $tabtemp[2];
				$tab[$id_champs_extras] = $tabDataPost[$ChampsName];
			}
		 return  $tab;	
		}
		
	}
	public function buildChampsExtras ( $tabDefaultValues,$is_label = FALSE) {
		$champsExtras =array();
		$champsExtras = $this->getChampsExtrasTable($this->tableName);
		if(sizeof($champsExtras) > 0 ) {
			$this->Myform->addHTMLExtraCode(self::CHAMPS_EXTRAS_HTML_ESPACE.$this->tableName_id, "<BR>");
			$this->Myform->addHTMLExtraCode(self::CHAMPS_EXTRAS_HTML_PREFIX_NAME.$this->tableName_id,
    	 "<table align=\"center\" valign=\"middle\" bgcolor=\"" . $colb_tableau . "\"><tr><td><b>"._("Informations supplémentaires")."</b></td></tr></table>\n");
			
			foreach($champsExtras AS $key => $valeur) {
				$nameFields = self::CHAMPS_EXTRAS_HTML_PREFIX_NAME.self::CHAR_LIAISON.$valeur['id'].self::CHAR_LIAISON.$this->tableName_id;
				$value = '';
				$this->Myform->addField($nameFields,$valeur['libel'], trim($valeur['type']));
				if( isset($tabDefaultValues[$valeur['id']])) {
					$value = $tabDefaultValues[$valeur['id']];
					$this->Myform->setFieldProperties($nameFields, FIELDP_DEFAULT, $value);
				}
				if($is_label) {
					$this->Myform->setFieldProperties($nameFields,FIELDP_IS_LABEL, true);
				}
				$isRequis = ($valeur['isreq'] == 't' );
				$this->Myform->setFieldProperties($nameFields, FIELDP_IS_REQUIRED, $isRequis);
				$this->addChamps($nameFields);
			}
		}
	}
	public  function getChampsExtrasTable ( $table ) {
		require_once 'lib/dbProcedures/parametrage.php';
		return  getChampsExtras($table);
	}
}