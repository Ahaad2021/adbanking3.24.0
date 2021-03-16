<?php

// format  : php -f gen_licence2.php [licence_date] [mode_agence] [code_identifier] [agence_siege] [mode_compensation] [nombre_clients]
// exemple : php -f gen_licence2.php 2016-12-31 multi 602-2017 y interagence 1000

if(isset($argv[1]) && trim($argv[1])!='') {

  $expiration_date = trim($argv[1]);

  if(strlen($expiration_date) == 10 && $expiration_date[4] == '-' && $expiration_date[7] == '-') {
  
    $exp_date = substr($expiration_date, 0, 10);
    $day = substr($exp_date, 8, 2);
    $month = substr($exp_date, 5, 2);
    $year = substr($exp_date, 0, 4);
	
	if(checkdate($month, $day, $year)) {

		/**
		 * Permet de générer une clé de cryptage
		 */
		function GenerationCle($Texte,$CleDEncryptage) {
		  $CleDEncryptage = md5($CleDEncryptage);
		  $Compteur=0;
		  $VariableTemp = "";
		  for ($Ctr=0;$Ctr<strlen($Texte);$Ctr++) {
			if ($Compteur==strlen($CleDEncryptage))
			  $Compteur=0;
			$VariableTemp.= substr($Texte,$Ctr,1) ^ substr($CleDEncryptage,$Compteur,1);
			$Compteur++;
		  }
		  return $VariableTemp;
		}

		/**
		 * Permet de crypter un texte
		 */
		function Crypte($Texte,$Cle) {
		  srand((double)microtime()*1000000);
		  $CleDEncryptage = md5(rand(0,32000) );
		  $Compteur=0;
		  $VariableTemp = "";
		  for ($Ctr=0;$Ctr<strlen($Texte);$Ctr++) {
			if ($Compteur==strlen($CleDEncryptage))
			  $Compteur=0;
			$VariableTemp.= substr($CleDEncryptage,$Compteur,1).(substr($Texte,$Ctr,1) ^ substr($CleDEncryptage,$Compteur,1) );
			$Compteur++;
		  }
		  return base64_encode(GenerationCle($VariableTemp,$Cle) );
		}

		$crypte_key = "adbankingpublic";
		$current_date = date("Y-m-d");

		// Store current date and expiration date
		$param_arr = array($current_date, $expiration_date);
		
		// Store installation mode
		if(isset($argv[2])) {
			$app_mode = trim($argv[2]);
			
			if($app_mode=='mono' || $app_mode=='multi') {
				$param_arr[] = $app_mode;
			} else {
				$param_arr[] = "";
			}
		}
		
		// Store code identifier
		if(isset($argv[3])) {
			$code_identifier = trim($argv[3]);

			$param_arr[] = $code_identifier;
		}
		
		// Store activation dropdown list to access all branches
		if(isset($argv[4]) && trim($argv[4])!='') {
			$agence_siege = trim($argv[4]);
			
			$param_arr[] = $agence_siege;
		}

		// Store compensation mode
		if(isset($argv[5])) {
			$compensation_mode = trim($argv[5]);

			if($compensation_mode=='interagence' || $compensation_mode=='siege') {
				$param_arr[] = $compensation_mode;
			} else {
				$param_arr[] = "";
			}
		}

		// Store maximum number of allowed clients
		if(isset($argv[6])) {
			$number_of_clients = trim($argv[6]);

			if($number_of_clients > 0) {
				$param_arr[] = $number_of_clients;
			} else {
				$param_arr[] = 99999999;
			}
		}

		// Store number of client creation left to display alert message
		if(isset($argv[7])) {
			$count_client_alert = trim($argv[7]);

			if($count_client_alert > 0) {
				$param_arr[] = $count_client_alert;
			} else {
				$param_arr[] = 30;
			}
		}

		// Is Engrais Chimiques or not
		if(isset($argv[8])) {
			$engrais_chimiques = trim($argv[8]);

			if($engrais_chimiques == 'y') {
				$param_arr[] = $engrais_chimiques;
			} else {
				$param_arr[] = 'n';
			}
		}

		// Managing password for database and main database user
		if(isset($argv[9])) {
			$password = trim($argv[9]);

			if($password != null) {
				$param_arr[] = $password;
			} else {
				$param_arr[] = 'public'; // by default public
			}
		}

		// Is Agency Banking or not
		if(isset($argv[10])) {
			$agency_banking = trim($argv[10]);

			if($agency_banking == 'y') {
				$param_arr[] = $agency_banking;
			} else {
				$param_arr[] = 'n';
			}
		}

        // Is Mobile Lending or not
        if(isset($argv[11])) {
            $mobile_elending = trim($argv[11]);

            if($mobile_elending == 'y') {
                $param_arr[] = $mobile_elending;
            } else {
                $param_arr[] = 'n';
            }
        }

        // Is ATM module
        if(isset($argv[12])) {
            $atm = trim($argv[12]);

            if($atm == 'y') {
                $param_arr[] = $atm;
            } else {
                $param_arr[] = 'n';
            }
        }

        // Is FENACOBU FUSION module
        if(isset($argv[13])) {
            $fusion = trim($argv[13]);

            if($fusion == 'y') {
                $param_arr[] = $fusion;
            } else {
                $param_arr[] = 'n';
            }
        }




        // Serialize data
		$param_arr_serialized = serialize($param_arr);

		$crypte_str = Crypte($param_arr_serialized, $crypte_key);

		// Delete existing licence2.txt
		if(file_exists('licence2.txt')) {
		  @unlink('licence2.txt');
		}

		// Create new licence2.txt
		file_put_contents('licence2.txt', $crypte_str);
		
		if(file_exists('licence.txt') && file_exists('licence2.txt')) {
			echo utf8_decode("Les fichiers 'licence.txt' et 'licence2.txt' ont été générés !\n");
		}
		elseif(file_exists('licence.txt')) {
			echo utf8_decode("Le fichier 'licence.txt' a été généré !\n");
		}
		elseif(file_exists('licence2.txt')) {
		  echo utf8_decode("Le fichier 'licence2.txt' a été généré !\n");
		}
	  }
      else {
	    echo "La date d'expiration de la licence n'est pas valide !\n";
	  }
  }
  else {
	echo "Veuillez saisir une date valide !\n";
  }
}
else {
  echo "Veuillez saisir la date d'expiration de la licence !\n";
}
