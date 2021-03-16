<?php 
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * @author Nathan Meurrens <nathan(AT)meurrens(DOT)org> <http://www.nathan.meurrens.org>
 * @author Cassiopea ASBL <info(AT)cassiopea(DOT)org> <http://www.cassiopea.org>
 * @author eSHANGO <contact(AT)eshango(DOT)com> <http://www.eshango.com>
 * @since 06 mars 2009:17:38:37
 * @project ADBanking
 * @license GNU General Public License (GPL3) <http://www.gnu.org/copyleft/gpl.html>
 */

defined ('EXPRESSION') or define('EXPRESSION', '/(?<!color_def)\s*\[\s*(\"|\\\')(([A-Z][a-z]+)|(\w*\s+\w*)*|[^\1\]]*[ÀÂÇÈÉÊËÎÏÔÖÙÛàâçèéêëîïôöùû€\\\']+[^\1\]]*)\1\s*\]/');
defined ('EXPRESSION') or define('EXPRESSION','/\[\s*(\"|\\\')((([A-Z])([a-z]+))|(([^\]]*?)([ÀÂÇÈÉÊËÎÔÖÙÛàâçèéêëîôùû€\s\']+)([^\]]*?)))\1\s*\]/');
defined ('FILE_PATH') or define('FILE_PATH',realpath(dirname(__FILE__)."/../"));
defined ('FILE_TYPE') or define('FILE_TYPE','php');
$list = getFileFlatList(getFilesTree(FILE_PATH),FILE_TYPE);
$arrOccurences = compareFiles($list,EXPRESSION);
?>
Ce script essaye de détecter les utilisation de clés d'array suspectes.  Une clé d'array est considérée suspecte pour les raisons suivantes :
 - elle commence par une majuscule suivie de minuscules
 - elle contient un espace
 - elle contient un caractère accentué

Pour ce faire, l'expression réguliè!re suivante
<?= EXPRESSION ?>	
a été recherchée dans les <?= count($list) ?> fichiers <?= strtoupper(FILE_TYPE) ?> de <?= FILE_PATH ?>

<? print_r($arrOccurences) ; ?>

<?php 

function compareLine($line,$needle){
	if((isset($line))&&(is_string($line))&&isset($needle)&&(is_string($needle))) {
	return preg_match($needle,$line);
	}
}
function compareFile($file,$needle){
	if((isset($file))&&isset($needle)&&(is_string($needle))){
		$arr = array();
		$i = 0 ;
		while(!feof($file)) { 
			$i++;
			$line = fgets($file);
			if(compareLine($line,$needle)) {
				$arr[$i] = trim($line) ;
			}
		}
		return $arr;
	}
}
function compareFiles($list,$needle){
	if((isset($list))&&(is_array($list))&&isset($needle)&&(is_string($needle))) {
		$result = array();
		foreach($list as $value){
			if(is_readable($value)) {
				$file = fopen($value,"r") ;
				$arr=compareFile($file,$needle);
				if(count($arr)>0){
					$result [$value] = $arr ;
				}
				fclose($file);
			}
		}
		return $result;	
	}
}

function getFileFlatList($tree,$fileType=false){
	$list = array();
	if ((isset($tree))&&(is_array($tree))){
		foreach($tree as $value){
			if(is_array($value)) { 
				$list=getFileFlatList(array_merge($list,getFileFlatList($value,$fileType)) , $fileType );
			} else { 	
				if((!$fileType)||($fileType=="*")||(strtolower(substr($value,(-strlen($fileType)-1)))==(strtolower(".".$fileType)))) {
					$list[]=$value;
				}
			}
		}
	}
	return $list;
}


function getFilesTree($path,$recursive=true) {
	$arr = array();
	if((isset($path))&&(is_readable($path))&&(is_dir($path))){
		foreach (scandir($path) as $key => $value) {
			$ressource = realpath($path."/".$value);
			if (is_readable($ressource)) {
				if (is_dir($ressource)) {
					if (($value!='..')&&($value!=".")) {
						if($recursive)	{
							$arr[$value] = getFilesTree($ressource);
						}
					} 
				} else {
					$arr[$value] = $ressource;		
				}
			}
		}
	}
	return $arr;
}
?>