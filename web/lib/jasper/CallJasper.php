<?php
require_once 'lib/misc/VariablesGlobales.php';
require_once 'modules/rapports/xslt.php';

class jasperReport {
	private $conn;
	private $reportsPath;
	private $reportsLibPath;
	private $DB_host='';
	private $DB_port='' ;
	private $url='' ;
	private $jaspert_output='';
	private $error = "";

	function jasperReport() {
		global $ini_array;
		global $DB_name;
		global $ini_array;
		$this->reportsLibPath= $ini_array['jasper_path'];
		//$this->reportsPath ="";
		debug($ini_array);
		$this->jaspert_output=$ini_array['jasper_output'];
		$this->dbDriver="org.postgresql.Driver";
			
	}



	public function initJasperReport($reportPath = '', $reportLibPath = '') {
		if ($reportPath != '')
		$this->setReportPath($reportPath);
		if ($reportLibPath != '')
		$this->setReportLib($reportLibPath);
	}

	public function buildReport($reportFile,$param,$format="pdf",$m_agc="") {
		global $doc_prefix;
		$param = $this->parametreformat($param);
		$jasper_output = $this->getJaspert_output() .".".session_id();
		if(!file_exists($reportFile)) {
                    $this->error .=sprintf("Le fichier {$reportFile} n'existe pas");
		}
                
                $filejasper = "{$this->reportsLibPath}/adbankingjasper.jar";

		if(!file_exists($filejasper)) {
			$this->error .=sprintf("\n Jasper report n'est pas configuré , Veuillez contacter votre administrateur");
		}
		//exec("java -jar {$this->reportsLibPath}/adbankingjasper.jar  -c {$fopcfg} -xml {$tmpxml} -xsl {$tmpxsl} -pdf {$tmppdf} 2>&1");
                
                if($m_agc>0) {
                    $ini_file_path = sprintf('%s/jasper_config/adbanking%s.ini', $doc_prefix, $m_agc);
                }else{
                    $ini_file_path = $doc_prefix."/adbanking.ini";
                }
		$reportFile = str_replace(" ", "\ ", $reportFile);

		$cmd = ("rm -f {$jasper_output}; java -jar {$this->reportsLibPath}/adbankingjasper.jar  {$ini_file_path} {$reportFile}  {$param}  {$format}  {$jasper_output} 2>&1");
		debug($cmd);

		debug("{$this->reportsLibPath}/adbankingjasper.jar");
		debug("{$this->reportsLibPath}");
		$arr_output = array();
		$returnvalue = null;
		exec($cmd,$arr_output,$returnvalue);
		debug($arr_output,$returnvalue);
		if ($returnvalue == 0 )
			return $jasper_output;
	}
	private function parametreformat ($param) {
		$str_format = NULL;
		foreach ($param as $paramName => $paramValues) {
			$str_format .=sprintf("%s=%s=%s:",$paramName,$paramValues[0],$paramValues[1]);
		}
		return $str_format;
	}


	public function setReportPath($path) {
		$this->reportsPath = $path;
	}

	public function getReportPath() {
		return $this->reportsPath;
	}

	public function setReportLib($path) {
		$this->reportsLibPath = $path;
	}

	public function getReportLib() {
		return $this->reportsLibPath;
	}
	public function setDB_host($DB_host) {
		$this->DB_host = $DB_host;
	}

	public function getDB_host() {
		if ( $this->DB_host == '' ) {
			if(!isset($ini_array["DB_host"])) {
				$this->DB_host="localhost";
			} else {
				$this->DB_host=$ini_array["DB_host"];
			}
		}
		return $this->DB_host;
	}
	public function setDB_port($DB_port) {

		$this->DB_port = $DB_port;
	}

	public function getDB_port() {
		global $ini_array;
		if ($this->DB_port == '' ) {
			if(!isset($ini_array["DB_port"])) {
				$this->DB_port="5432";
			} else {
				$this->DB_port=$ini_array["DB_port"];
			}
		}
		return $this->DB_port;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function getUrl() {
		global $ini_array;
		global $DB_name;
		// url de connexion
		if($this->url == '' ) {
			$this->url="jdbc:postgresql://".$this->getDB_host().":".$this->getDB_port()."/".$DB_name;
		}
		return $this->url;
	}
	public function getJaspert_output(){
		global $ini_array;
		global $DB_name;
		if($this->jaspert_output == "") {
			$this->jaspert_output=$ini_array['jasper_output'];
		}
		return  $this->jaspert_output;
	}
	public function isError() {
		return $this->error != "";
	}
	public function getError() {
		return $this->error;
	}
}
?>