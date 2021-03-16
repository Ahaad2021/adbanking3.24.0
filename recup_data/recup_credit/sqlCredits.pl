#!/usr/bin/perl
# vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker:

######################################################################################
######################################################################################
#                      ----------   ADbanking v3.0 ----------                        #
#                      ---- Module de reprise des crédits par lot ---                #
######################################################################################
######################################################################################

$traducFile = "../traduction.conf";	# Nom du fichier de configuration
$inputFile = "recup_credit.csv";	# Nom du fichier de données (format CSV)
$outputFile = "DATA.sql";		# Nom du fichier SQL à générer
$errorFile = "error.log";		# Nom du fichier de log
%comptes_substitut = ();		# Tableau contenant les comptes de substitut

#{{{ isDate : Fonction de vérification de la structure d'une date (JJ/MM/AAAA)
sub isDate
{
  #if ($_[0] !~ /^[0-9]+\/[0-9]+\/[0-9]+$/)
 ($jour,$mois,$annee)=split("/", $_[0]);
  #if (($jour !~ /\b(0?[1-9]|[12][0-9]|3[01])/) || ($mois !~ /\b(0?[1-9]|1[0-2])/) || ($annee !~ /\b(\d{2}|\d{4}­)/))
  if (($jour !~ /\b(\d{2})\b/) || ($mois !~ /\b(\d{2})\b/) || ($annee !~ /\b(\d{2}|\d{4})\b/))
  {
    return 0;
  }
  else
  {
    return 1;
  }
}
#}}}

#{{{ Cmonth : Fonction de conversion mois lettre en mois chiffre
sub Cmonth
{
  if ($_[0] == "Jan")
  {
    $_[0] = 1;
  }
  elsif ($_[0] == "Feb")
  {
    $_[0] = 2;
  }
  elsif ($_[0] == "Mar")
  {
    $_[0] = 3;
  }
  elsif ($_[0] == "Apr")
  {
    $_[0] = 4;
  }
  elsif ($_[0] == "May")
  {
    $_[0] = 5;
  }
  elsif ($_[0] == "Jun")
  {
    $_[0] = 6;
  }
  elsif ($_[0] == "Jul")
  {
    $_[0] = 7;
  }
  elsif ($_[0] == "Aug")
  {
    $_[0] = 8;
  }
  elsif ($_[0] == "Sep")
  {
    $_[0] = 9;
  }
  elsif ($_[0] == "Oct")
  {
    $_[0] = 10;
  }
  elsif ($_[0] == "Nov")
  {
    $_[0] = 11;
  }
  elsif ($_[0] == "Dec")
  {
    $_[0] = 12;
  }
  return $_[0];
}
#}}}

#{{{ expireDAT : Fonction de calcul de la date d'expiration d'un DAT
sub expireDAT
{
  my $tmp = $_[0];
  my $term = $_[1];

  @fin = split(/\//,$tmp);
  $fin[1] = $fin[1] + $term;
  if ($fin[1] > 12)
  {
    $fin[2] = $fin[2] + 1;
    $fin[1] = $fin[1] - 12;
  }
  return @fin;
}
#}}}

#{{{ CalculDate : Fonction de calcul de la date à partir d'une date et des différés jours et mois donnés
sub calculDateEch
{
	use POSIX;

	my $date = $_[0];
	my $nbr_jour = $_[1];
	my $nbr_mois = $_[2];
  my ($jour,$mois,$annee)=split("/", $date);
	if (length($annee) != 4){
		$error++;
    $errormsg .= ("ERREUR : La cellule de la date n'est pas au bon format : ".$date."\n");
	}

  $jour = $jour + $nbr_jour;
  $mois = $mois + $nbr_mois;
  $annee = $annee - 1900;
	my $time = POSIX::mktime(0,0,0,$jour,$mois-1,$annee);
	if ($time == -1){
		$error++;
    $errormsg .= ("ERREUR dans l'appel de la fonction 'calculDateEch' oubien la librairie POSIX n'est pas trouvée. \n");
	}
	my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = (localtime($time));
	$jour = $mday;
	$mois = $mon+1;
	$annee = 1900+$year;
	$date = "$jour/$mois/$annee";
	return $date;
}
 #}}}

#{{{ Insere les échéances et les éventuels remboursements
sub insereEchRemb
{
	 %args = @_;

		$num_ligne = $args{'num_ligne'}; $mnt_octr =$args{'mnt_octr'}; $int_tot = $args{'int_tot'}; $cap_rest = $args{'cap_rest'}; $int_rest = $args{'int_rest'};
		$duree = $args{'duree'}; $diff_ech = $args{'diff_ech'}; $diff_jour = $args{'diff_jour'}; $date_debloc = $args{'date_debloc'}; $date_dern_remb = $args{'date_dern_remb'};
		$periodicite = $args{'periodicite'}; $duree_periodicite = $args{'duree_periodicite'}; $base_taux = $args{'base_taux'}; $mode_calcul_int = $args{'mode_calcul_int'}; $id_prod_cre = $args{'id_prod'};

		# $nbre_jour_period nombre de jours entre deux échéances
		# $nbre_mois_period nombre de mois entre deux échéances

		if ($base_taux == 1){ # nombre de jours par mois = 30
			$nbr_jour_mois = 30;

			if ($periodicite == 6){ # remboursement en une fois
			$nbre_jour_period = $duree * $nbr_jour_mois;
			$nbre_mois_period = 0;
			}
			if ($periodicite == 8){ # hebdomadaire
				$nbre_jour_period = $duree_periodicite * 7;
				$nbre_mois_period = 0;
			}
			if ($periodicite == 2){ # quinzaine
				$nbre_jour_period = $duree_periodicite * $nbr_jour_mois;
				$nbre_mois_period = 0;
			}
			# mensuel, trimestriel, semestriel, annuel, bimestriel
			if (($periodicite == 1)||($periodicite == 3)||($periodicite == 4)||($periodicite == 5)||($periodicite == 7)){
				$nbre_jour_period = $duree_periodicite * $nbr_jour_mois;
				$nbre_mois_period = 0;
			}
		}
		if ($base_taux == 2){
			if ($periodicite == 6){ # une fois
				$nbre_jour_period = 0;
				$nbre_mois_period = $duree;
			}

			if ($periodicite == 8){ # hebdomadaire
				$nbre_jour_period = 7;
				$nbre_mois_period = 0;
			}

			if ($periodicite == 2){ # quinzaine
				$nbre_jour_period = 15;
				$nbre_mois_period = 0;
			}

			if (($periodicite == 1)||($periodicite == 3)||($periodicite == 4)||($periodicite == 5)||($periodicite == 7)){
				$nbre_jour_period = 0;
				$nbre_mois_period = $duree_periodicite;
			}

		}
		$cap_ech = int($mnt_octr / $duree);
		$int_ech = int($int_tot / ($duree + $diff_ech));
		$nbr_ech_cap = int($mnt_octr / $cap_ech) + $diff_ech;
		$nbr_ech_int = int($int_tot / $int_ech);
		if ($mode_calcul_int > 1 && $mode_calcul_int < 5){
		    $nbr_ech_int = $nbr_ech_cap;
		}
		# nombre total d'échéances ( le maximum entre nombre d'échéances en capital et nombre d'échéances in intérêt)
		 if ($nbr_ech_cap > $nbr_ech_int ){
	  	$nbr_tot_ech = $nbr_ech_cap;
	  }
	  else{
	  	$nbr_tot_ech =  $nbr_ech_int;
	  }

	  $tot_cap_pay = $mnt_octr - $cap_rest;
	  $tot_int_pay = $int_tot - $int_rest;
	  $nbr_ech_couv_cap = int($tot_cap_pay / $cap_ech);
	  $nbr_ech_couv_int = int($tot_int_pay / $int_ech);
	  if ($mode_calcul_int > 1 && $mode_calcul_int < 5){
      	  $nbr_ech_couv_int = $nbr_ech_couv_cap;
      }
		# la prochaine échéance à rembourser pour le capital
	  $proc_ech_cap = $nbr_ech_couv_cap + $diff_ech + 1;
	  # la prochaine échéance à rembourser pour les intérêts
	  $proc_ech_int = $nbr_ech_couv_int + 1;
	  # la prochaine échéance à rembourser (le minimum entre $proc_ech_cap et $proc_ech_int)
	  if ($proc_ech_cap > $proc_ech_int ){
	  	$proc_ech = $proc_ech_int;
	  }
	  else{
	  	$proc_ech =  $proc_ech_cap;
	  }
	  $cap_remb_proc_ech = $tot_cap_pay - ($nbr_ech_couv_cap * $cap_ech);
	  $int_remb_proc_ech = $tot_int_pay - ($nbr_ech_couv_int * $int_ech);

	  $date_ech_prec = $date_debloc;
	  $nbr_jour = $nbre_jour_period;
		$nbr_mois = $nbre_mois_period;
		$mnt_cap_cumule = 0;
		$mnt_int_cumule = 0;
		$mnt_proch_cap_cumule = 0;
		$mnt_echeance = 0;
		$int_ech_prorata = 0;
		$tot_cap_pay_degressif = $tot_cap_pay;
		$tot_int_pay_degressif = $tot_int_pay;
		$mnt_ech_tot_pay = $tot_cap_pay_degressif + $tot_int_pay_degressif;
	  # Pour chaque échéance
		for ($id_ech=1; $id_ech <= $nbr_tot_ech; $id_ech++) {
		# calcul date échéance
		if ($id_ech == 1){
		 $date_ech = calculDateEch($date_ech_prec, $nbr_jour + $diff_jour, $nbr_mois);
		}
		else {
		 $date_ech = calculDateEch($date_ech_prec, $nbr_jour, $nbr_mois);
		}
		# calcul des capitaux et intérêts attendus des échéances
		$mnt_cap = $cap_ech;
		$mnt_int = $int_ech;

		# Mode de calcul degressif (le capital augmente et l'interet degresse)
		if ($mode_calcul_int > 1 && $mode_calcul_int < 5){
            #use strict;
            use warnings;
            use POSIX qw(strftime);
            my $date_ech_courante = date_format($date_ech);
            my $date_prec_ech_courante = date_format($date_ech_prec);
            my $date_reprise = strftime "%d/%m/%Y", localtime; #localtime;#gmtime;
            my $dateNowYear = strftime "%Y", localtime;
            my $dateNowMon = strftime "%m", localtime;
            my $dateNowDay = strftime "%d", localtime;
            $date_reprise = date_format($date_reprise);
            my $ech_courante = date_to_seconds($date_ech_courante);
            my $prec_ech_courante = date_to_seconds($date_prec_ech_courante);
            my $reprise = date_to_seconds($date_reprise);

            use DateTime;
            if ($periodicite == 8){
                $date_jour_ech_courante = DateTime->new(year=>$dateNowYear,month=>$dateNowMon,day=>($dateNowDay+$periodicite*7),hour=>0,minute=>0,second=>0);
                $nombre_jour_ech_courante = $date_jour_ech_courante->day();
            }
            elsif ($base_taux == 1){
                $nombre_jour_ech_courante = 30*$periodicite;
            }
            else{
                $date_jour_ech_courante = DateTime->new(year=>$dateNowYear,month=>$dateNowMon+$periodicite,day=>$dateNowDay,hour=>0,minute=>0,second=>0);
                $nombre_jour_ech_courante = $date_jour_ech_courante->day();
            }

            #Recupere taux d'interet, frequence paiement capital et calcul interet differe pour le calcule interet degressif
            @taux_int_cre_cal_int_diff = `psql -A -d adbanking -U adbanking -c \"SELECT CASE WHEN calcul_interet_differe = 't' THEN 0 ELSE 1 END as cal_int_diff, tx_interet, freq_paiement_cap FROM adsys_produit_credit WHERE id = '$id_prod_cre'\"`;
            ($cal_int_diff,$taux_int_cre,$freq_paiement_cap) = split(/\|/,$taux_int_cre_cal_int_diff[1]);

            #Taux d'interet pour les echeances
            $tx_int_ech = $taux_int_cre * $periodicite / 12;
            if ($periodicite == 8){
                $tx_int_ech = $taux_int_cre * $periodicite;
            }

            #print "\nID ECH [$id_ech] => Taux Int = $taux_int_cre | Taux Int Ech = $tx_int_ech | Duree = $duree | Diff ech = $diff_ech | Periodicite = $periodicite | Freq Paiement Cap = $freq_paiement_cap\n";
            #Montant Ech -> le montant calculé est constant pour les echeances à venir
            $mnt_echeance = ($mnt_octr * $tx_int_ech/(1-(1/((1+$tx_int_ech)**($nbr_tot_ech/$freq_paiement_cap)))));
            $mnt_echeance = int($mnt_echeance+0.5);
            if ($id_ech == 1){ #Premiere echeance
                #Interets
                $int_ech_calc = ($mnt_octr * $tx_int_ech * 1.0); # /($duree+$diff_ech)
                $diff_jrs = $diff_jour;
                $true = 0;
                $result = $cal_int_diff cmp $true;
                if ($result == 0){ # && ($diff_jrs > 0 || $diff_jrs < 0)
                    $prorata_temp = $diff_jour/$nombre_jour_ech_courante;
                    $int_ech_prorata = ($mnt_octr * $tx_int_ech * $prorata_temp * 1.0);
                    $int_ech_calc = ($mnt_octr * $tx_int_ech * 1.0) + $int_ech_prorata;
                    $mnt_echeance = ($mnt_octr * $tx_int_ech/(1-(1/((1+$tx_int_ech)**($nbr_tot_ech/$freq_paiement_cap))))) + $int_ech_prorata;
                    $mnt_echeance = int($mnt_echeance+0.5);
                }
            }
            else{
                #Interets
                $int_ech_calc = (($mnt_octr - $mnt_cap_cumule) * $tx_int_ech * 1.0); # /($duree+$diff_ech)
            }
            $int_ech = int($int_ech_calc+0.5);
            #Capital
            $cap_ech = $mnt_echeance - $int_ech;
            $mnt_proch_cap_cumule += $cap_ech;
            #Capital et Interet prochaine echeance
            #$int_remb_proc_ech = int((($mnt_octr - $mnt_proch_cap_cumule) * $taux_int_cre * 1.0)+0.5);
            $int_remb_proc_ech = ($mnt_octr - $mnt_proch_cap_cumule) * $tx_int_ech * 1.0;
            $int_remb_proc_ech = int($int_remb_proc_ech+0.5);
            #$cap_remb_proc_ech = int(($mnt_echeance - $int_remb_proc_ech)+0.5);
            $cap_remb_proc_ech = $mnt_echeance - $int_remb_proc_ech;
            #print "\nId Ech = $id_ech | Int Ech = $int_ech | Cap Ech = $cap_ech\n";
            #print "\n\tMnt Echeance = $mnt_echeance | Int Remb Proc Ech = $int_remb_proc_ech | Cap Remb Proc Ech = $cap_remb_proc_ech\n";
            $mnt_int = int($int_ech+0.5);
            $mnt_cap = int($cap_ech+0.5);
		}

		if (($id_ech <= $diff_ech) || ($id_ech > $nbr_ech_cap)){ # échéance à différer ou dernières échéances en capital
			#$mnt_cap = 0;
			if ($mode_calcul_int > 1 && $mode_calcul_int < 5){
			    $mnt_cap = 0;
			}
			else{
			    $mnt_cap = 0;
			}
		}
		if ($id_ech > $nbr_ech_int){ # dernières échéances en intérêt
			$mnt_int = 0;
		}
		if ($id_ech == $nbr_ech_cap){# dernière  échéance à payer en capital
			#$mnt_cap = $cap_ech + $mnt_octr % $cap_ech;
			if ($mode_calcul_int > 1 && $mode_calcul_int < 5){
                   $mnt_cap = $mnt_octr - $mnt_cap_cumule;
            }
            else{
                   $mnt_cap = $cap_ech + $mnt_octr % $cap_ech;
            }
		}
		if ($id_ech == $nbr_ech_int){# dernière  échéance à payer en intérêt
			#$mnt_int = $int_ech + $int_tot % $int_ech;
			if ($mode_calcul_int > 1 && $mode_calcul_int < 5){
                  $mnt_int = $int_tot - $mnt_int_cumule + $int_ech_prorata;
            }
            else{
                $mnt_int = $int_ech + $int_tot % $int_ech;
            }
		}

		# calcul des soldes des échéances
		$solde_cap = $mnt_cap;
		$solde_int = $mnt_int;
		#print "Solde int = $solde_int\n";
		if ($id_ech < $proc_ech_cap){# échéance couverte en capital
			$solde_cap = 0;
		}
		if ($id_ech < $proc_ech_int){# échéance couverte en intérêt
			$solde_int = 0;
		}
		if ($id_ech == $proc_ech_cap){ # prochaine échéance à rembourser pour le capital
			$solde_cap = $mnt_cap - $cap_remb_proc_ech;
			#print "\t\tSolde Cap = $solde_cap | Mnt Cap = $mnt_cap | Cap Remb Proc Ech = $cap_remb_proc_ech\n";
		}
		if ($id_ech == $proc_ech_int){ # prochaine échéance à rembourser pour l'intérêt
			$solde_int = $mnt_int - $int_remb_proc_ech;
			#print "\t\tSolde Int = $solde_int | Mnt Int = $mnt_int | Int Remb Proc Ech = $int_remb_proc_ech\n";
		}
		$remb = 'f';
		if($id_ech < $proc_ech){
			$remb = 't';
		}
		# Remboursement pour le mode de calcul degressif
		if ($mode_calcul_int > 1 && $mode_calcul_int < 5){
		    $remb = 'f';

		    if ($mnt_ech_tot_pay >= $mnt_echeance){ # Remboursement complete de l'echeance
                $solde_cap = 0;
                $solde_int = 0;
                $mnt_ech_tot_pay -= $mnt_echeance;
                $tot_cap_pay_degressif -= $mnt_cap;
                $tot_int_pay_degressif -= $mnt_int;
                $remb = 't';
		    }
		    else{
		        if ($tot_cap_pay_degressif >= $mnt_cap){ #Reboursement partielle - remboursement complete capital
		            $solde_cap = 0;
		            $tot_cap_pay_degressif -= $mnt_cap;
		            $mnt_ech_tot_pay -= $mnt_cap;
		        }
		        else{ #remboursement partielle capital
		            $solde_cap = int(($mnt_cap - $tot_cap_pay_degressif)+0.5);
		            $tot_cap_pay_degressif -= $tot_cap_pay_degressif;
                    $mnt_ech_tot_pay -= $tot_cap_pay_degressif;
		        }
		        if ($tot_int_pay_degressif >= $mnt_int){ #Reboursement partielle - remboursement complete interet
                    $solde_int = 0;
                    $tot_int_pay_degressif -= $mnt_int;
                    $mnt_ech_tot_pay -= $mnt_int;
                }
                else{ #remboursement partielle interet
                    $solde_int = int(($mnt_int - $tot_int_pay_degressif)+0.5);
                    $tot_int_pay_degressif -= $tot_int_pay_degressif;
                    $mnt_ech_tot_pay -= $tot_int_pay_degressif;
                }
		    }
		}

		# Insertion des échéances
		$sql = "INSERT INTO ad_etr(id_doss, id_ech, date_ech, mnt_cap, mnt_int, remb, solde_cap, solde_int, id_ag)";
    $sql .= "VALUES (currval('ad_dcr_id_doss_seq'), $id_ech, '$date_ech', $mnt_cap, $mnt_int, '$remb', $solde_cap, $solde_int,  NumAgc());";
    print OUTPUT $sql."\n";

		# Insertion des remboursements
		$mnt_remb_cap = $mnt_cap - $solde_cap;
		$mnt_remb_int = $mnt_int - $solde_int;
		if (($mnt_remb_cap != 0) || ($mnt_remb_int != 0)){
		$sql = "INSERT INTO ad_sre(id_doss, id_ech, num_remb, date_remb, mnt_remb_cap, mnt_remb_int, id_ag)";
    $sql .= "VALUES (currval('ad_dcr_id_doss_seq'), $id_ech, 1, '$date_dern_remb', $mnt_remb_cap, $mnt_remb_int, NumAgc());";
    print OUTPUT $sql."\n";
		}
		$date_ech_prec = $date_ech;
		$mnt_cap_cumule += $cap_ech;
		$mnt_int_cumule += $int_ech;
	}
	#print "\n====================================================================\n";
}
#}}}

#{{{ isFieldBool : Fonction de vérification de la validité d'un champs booléen
sub isFieldBool
{
  my $val = $_[0];
  $val = uc($val);
	if (($val !~ /\b(OUI|NON)\b/)&&($val !~ /^$/)){
		return 0;
	}
  else
  {
    return 1;
  }
}
#}}}

#{{{ isFieldBoolOui : Fonction de vérification de la validité d'un champs booléen 'Oui'
sub isFieldBoolOui
{
  my $val = $_[0];
  $val = uc($val);
	if (($val !~ /\b(OUI)\b/)&&($val !~ /^$/)){
		return 'f';
	}
  else
  {
    return 't';
  }
}
#}}}

#{{{ valideDAT : Fonction de vérification de la validité d'un DAT
sub valideDAT
{
  my $tab1 = $_[0];
  my $tab2 = $_[1];
  my $ter = $_[2];
  @today = split(/\s/,$tab2);
  $today[1] = &Cmonth($today[1]);
  @datexpire = &expireDAT($tab1,$ter);
  if (($datexpire[2] < $today[5]) || (($datexpire[2] == $today[5]) && ($datexpire[1] < $today[1])) || (($datexpire[1] == $today[1]) && ($datexpire[2] == $today[5]) && ($datexpire[0] < $today[3])))
  {
    return 1;
  }
  else
  {
    return 0;
  }
}
#}}}

#{{{ print_r : Fonction d'affichage du contenu d'un hash de hash
sub print_r
{
  $r = $_[0];
  reset($r);
  for $k1 ( keys %$r )
  {
    print "k1: $k1\n";
    for $k2 ( keys %{$r->{ $k1 }} )
    {
      print "k2: $k2 $r->{ $k1 }{ $k2 }\n";
    }
  }
}
#}}}

#{{{ in_array : Clone de la fonction in_array de PHP
# Renvoie true si $val se trouve dans @list
sub in_array {
  my $val = $_[0];
  my @list = $_[1];

  foreach $elem(@list)
  {
    if($val == $elem)
    {
      return 1;
    }
  }
  return 0;
}
#}}}


#{{{ makeRegExp : Fabrique une regexp avec les différente valeurs correspondant aux clés du hash passé en paramètre
# Utilisé pour la validation des acronymes du fichier CSV
# IN : un hash avec les acronymes en tant que clé
# OUT: une expresion régulière du type ^(cle1|cle2|...|clen)$
sub makeRegExp
{
  local $r = $_[0];
  $regexp = "^(";
  for $key (keys %$r)
  {
    $regexp .= "$key|";
  }
  chop($regexp);
  $regexp .= ')$';
  return $regexp;
}
#}}}
#{{{ getRemp : Fonction permettant de parser le fichier de configuration
# Récupère dans $val_nominale_ps la valeur nominale d'une PS
# Créer un hash avec les acronymes possibles selon le type d'info ainsi que les valeurs
# à utiliser dans la base de données
sub getRemp
{
  open (TRAD, $traducFile) || die("Le fichier $traducFile n'a pas pu être trouvé !\n");

  print("Ouverture du fichier de configuration $traducFile\n");

# etat nous indique où nous sommes dans le fichier
# 0 = Hors d'une section
# 1 = Dans une section
# Si etat = 1, $section contient le nom de la section
  $etat = 0;

  $i = 0;  # Numéro de ligne, DEBUG seulement

    %remp = (); # Initialisation du hash : vide au départ

    while (<TRAD>)  # Pour chaque ligne du fichier
    {
      $i++;
      if ($_ =~ /^\#/ )  # Commentaire
      {
        next;
      }
      elsif ($_ =~ /^\s/) # Ligne vide
      {
        next;
      }
      else
      {
        if ($etat == 0) # On est hors d'une section
        {
          if ($_ !~ /^\[(\w)+\]$/) #La ligne n'est pas au format [xxx]
          {
            die("ERREUR ligne $i : Titre de section attendu\n");
          }
          else
          {
# Récupération du titre de section
            $section = s/(\[)(\w+)(\])/$2/g;
            $section = $2;
            print("\tSection $section trouvée\n");
            $etat = 1; # On est maintenant à l'intérieur d'une section
          }
        }
        elsif ($etat == 1) # On est dans une section, on cherche une correspondance
        {
          if ($section eq "general")  # Le cas de la section [general] est particulier
          {
            @infos = split(/\s/, $_); # On tente de récupérer les infos de la section general
              if ($infos[0] eq "exo_courant")
              {
                $exo_courant = $infos[1];
                print ("\t\tNuméro de l'exercice en cours = $exo_courant\n");
              }
            elsif ($infos[0] eq "val_nominale_ps")
            {
              $val_nominale_ps = $infos[1];
              print ("\t\tValeur nomainale PS = $val_nominale_ps\n");
            }
            elsif ($infos[0] eq "cpt_el")
            {
              $cpt_el = $infos[1];
              print ("\t\tCompte lié à l'épargne libre = $cpt_el\n");
            }
            elsif ($infos[0] eq "cpt_ps")
            {
              $cpt_ps = $infos[1];
              print ("\t\tCompte lié aux parts sociales = $cpt_ps\n");
            }
            elsif ($infos[0] eq "dev_el")
            {
              $dev_el = $infos[1];
              print ("\t\tDevise de l'épargne libre = $dev_el\n");
            }
            elsif ($infos[0] eq "dev_ps")
            {
              $dev_ps = $infos[1];
              print ("\t\tDevise des parts sociales = $dev_ps\n");
            }
            elsif ($infos[0] eq "langue_systeme_dft")
            {
              $langue_systeme_dft = $infos[1];
              print ("\t\tLangue système par défaut = $langue_systeme_dft\n");
            }
            elsif ($infos[0] eq "use_anc_num")
            {
              $use_anc_num = $infos[1];
              print ("\t\tUtilisation de l'ancien numéro = $use_anc_num\n");
            }
            elsif ($infos[0] eq "type_num_cpte")
            {
              $type_num_cpte = $infos[1];
              print ("\t\tType de numérotation des comptes = $type_num_cpte\n");
            }
          }
          else  # Autre section
          {
            if ($_ =~ /^\[(\w)+\]$/) # fin de section
            {
              $etat = 0; # On retourne à l'état hors d'une section
                redo;      # On réitère
            }
            else
            {
              @infos = split(/\s/, $_);
# Récupération de l'acronyme et de sa valeur de remplacement
              $remp{$section}{$infos[0]} = $infos[1];

              if ($section eq "produit_credit")
              {
# Seulement pour la section produit
								@infos = split(/\t|\n/, $_); # on elimine les tabulations et les retours chariots
                $remp{$section}{$infos[0]}{"id_prod"} = $infos[1];
                $remp{$section}{$infos[0]}{"devise"} = $infos[4];
                $remp{$section}{$infos[0]}{"duree_periodicite"} = $infos[5];
              }
              elsif ($section eq "sub_ep" or $section eq "sub_cr") # Substitution des comptes DAV et PS
              {
                if($infos[1]!=0)
                {
                  $ep = $infos[0];
                  $subs = $infos[1];
                  print ("\t\tCompte de substitut du compte $ep = $subs\n");
                  $comptes_substitut{$ep} = $infos[1];
                }
              }
            }
          }
        }
      }
    }
  close(TRAD);  # Fermeture du fichier de configuration
    print("Fermeture du fichier de configuration $traducFile\n");
  return \%remp; # Renvoie une référence vers le hash créé
}

sub date_to_seconds {

        use strict;
        use warnings;
        use POSIX qw/mktime/;
        # Array contenant les mois dans un an
        my %mon = (
               JAN => 0,
               FEB => 1,
               MAR => 2,
               APR => 3,
               MAY => 4,
               JUN => 5,
               JUL => 6,
               AUG => 7,
               SEP => 8,
               OCT => 9,
               NOV => 10,
               DEC => 11,
            );

        my $date = shift;
        my ($day, $month, $year) = split /-/, $date;

        $month = $mon{$month};
        if ($year < 50) { #or whatever your cutoff is
            $year += 100; #make it 20??
        }

        #return midnight on the day in question in
        #seconds since the epoch
        return mktime 0, 0, 0, $day, $month, $year;
}

sub date_format {

        #use strict;
        #use warnings;
        #use POSIX qw/mktime/;
        # Array contenant les mois dans un an
        my %mon = (
               1 => JAN,
               2 => FEB,
               3 => MAR,
               4 => APR,
               5 => MAY,
               6 => JUN,
               7 => JUL,
               8 => AUG,
               9 => SEP,
               10 => OCT,
               11 => NOV,
               12 => DEC,
            );

        my $date = shift;
        my ($day, $month, $year) = split /\//, $date;


        my ($firstpos, $secondpos) = split //, $month;
        $month = $mon{$month};
        if ($firstpos == 0){
            $month = $mon{$secondpos};
        }
        my ($yearfirst, $yearSecond, $yearThird, $yearFourth) = split //, $year;
        $dateValue = $day."-".$month."-".$yearThird.$yearFourth;

        #return midnight on the day in question in
        #seconds since the epoch
        #return mktime 0, 0, 0, $day, $month, $year;
        return $dateValue;

}

# Formattage du numéro de compte
sub formatCpteCmpltAgc {

        $clientId = $_[0];
        $Agc_id = $_[1];

        $new_id_cli = $clientId;

        @hasCpteCmpltAgc = `psql -A -d adbanking -U adbanking -c \"SELECT has_cpte_cmplt_agc FROM ad_agc where id_ag = $Agc_id\"`;
        $hasCpteCmpltAgc = $hasCpteCmpltAgc[1];

        if(hasCpteCmpltAgc == 't') {
          $new_id_cli = sprintf("%02d%08d", $Agc_id, $clientId);
        }

        return $new_id_cli;
}
sub makeNumCpte {

        use POSIX qw[fmod];

        $type_numerotation = $_[0];
        $rangCpte = $_[1];
        $clientId = $_[2];
        $Agc_id = $_[3];

        @infos_agc = `psql -A -d adbanking -U adbanking -c \"SELECT code_banque, code_ville, code_antenne FROM ad_agc where id_ag = $Agc_id\"`;
        #$infos_agc = $infos_agc[1];
        ($code_banque, $code_ville, $code_antenne) = split(/\|/, $infos_agc[1]);

        if ($type_numerotation == 1){
            # Crée un numéro de compte au format AA-CCCCCC-RR-DD à partir du rang (R) et de l'ID client (C)
            $NumCompletCompte = sprintf("%03d-%06d-%02d", $Agc_id, $clientId, $rangCpte);
            $Entier = sprintf("%03d%06d%02d", $Agc_id, $clientId, $rangCpte);
            $CheckDigit = fmod($Entier, 97);
            $NumCompletCompte .= sprintf("-%02d", $CheckDigit);
        }
        elsif ($type_numerotation == 2){
            # Crée un numéro de compte au format BBVV-CCCCCRR-DD à partir du rang (R) et de l'ID client (C) pour la RDC
            $NumCompletCompte = sprintf("%02d%02d-%05d%02d", $code_banque, $code_ville, $clientId, $rangCpte);
            $Entier = sprintf("%02d%02d%05d%02d", $code_banque, $code_ville, $clientId, $rangCpte);
            $CheckDigit = fmod($Entier, 97);
            $NumCompletCompte .= sprintf("-%02d", $CheckDigit);
        }
        elsif ($type_numerotation == 3){
            # Crée un numéro de compte au format BBB-CCCCCCCCCC-RR à partir du rang (R) et de l'ID client (C) pour le Rwanda
            $NumCompletCompte = sprintf("%03d-%010d-%02d", $code_banque, formatCpteCmpltAgc($clientId,$Agc_id), $rangCpte);
        }
        elsif ($type_numerotation == 4){
            # Crée un numéro de compte au format AA-CCCCCC-RR-DD à partir du rang (R) et de l'ID client (C)
            $numAntenne=$code_antenne;
            if ($numAntenne!= '0' && $numAntenne!= NULL) {
              $NumCompletCompte=$numAntenne.$Agc_id;
              $Entier =$numAntenne.$Agc_id;
            } else {
              $NumCompletCompte=$Agc_id;
              $Entier =$Agc_id;
            }
            $NumCompletCompte .= sprintf("-%06d-%02d", $clientId, $rangCpte);
            $Entier .= sprintf("%06d%02d", $clientId, $rangCpte);
            $CheckDigit = fmod($Entier, 97);
            $NumCompletCompte .= sprintf("-%02d", $CheckDigit);
        }
        else{
            $NumCompletCompte = "Error";
        }

        return $NumCompletCompte;

}
#}}}

#{{{ Programme principal



# Récupération du hash des remplacements
$r = getRemp();

# Ouverture du fichier CSV
open (INPUT, $inputFile) || die("Le fichier $inputFile n'a pas pu être trouvé !\n");
print("Ouverture du fichier de reprise $inputFile\n");

# Ouverture du fichier SQL
open (OUTPUT, ">$outputFile");

# On passe la première ligne qui contient la légende des colonnes
<INPUT>;

print("\tVérification du format des données\n");

# Lignes d'introduction dans le fichier OUTPUT
print OUTPUT "-- Fichier d'importation des crédits générés automatiquement \n";
print OUTPUT "\\connect - adbanking\n";
print OUTPUT "BEGIN;\n";

# Ajout de la reprise dans l'historique
$now = gmtime;
print OUTPUT "\n/* AJOUT DE LA REPRISE DANS L'HISTORIQUE */\n";
$sql = "INSERT INTO ad_his (id_ag, type_fonction,login,infos,date) VALUES (NumAgc(), 500, 'admin', 'Reprise des crédits', '$now');";
print OUTPUT $sql."\n";

# Création de l'écriture

$sql = "INSERT INTO ad_ecriture (id_ag, id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture) VALUES (NumAgc(), (SELECT currval('ad_his_id_his_seq')), '$now', makeTraductionLangSyst('Reprise des crédits'), 1,exoCourant(), makeNumEcriture(1, exoCourant()));";
print OUTPUT $sql."\n";

# Initialisation de la séquence des id_doss
$sql = "SELECT setval('ad_dcr_id_doss_seq', (SELECT MAX(id_doss) FROM ad_dcr));";
print OUTPUT $sql."\n";

# Initialisation des variables
$i = 2;            # Numéro de ligne
$error = 0;        # Nombre d'erreurs trouvées
$errormsg = '';    # Message d'ereur à afficher
%doss_multi_reel=(); # Tableau pour enregistrer les dossiers réels des crédits à dossiers multiples
%doss_multi_fictif=(); # Tableau pour enregistrer les dossiers fictifs des crédits à dossiers multiples
%doss_uni_reel=(); 	# Tableau pour enregistrer les dossiers réels des crédits à dossier unique
%doss_uni_fictif=(); 	# Tableau pour enregistrer les dossiers fictifs des crédits à dossier unique

print OUTPUT "\n/* TRAITEMENT DES CREDITS SIMPLES */\n";

while (<INPUT>)  # Pour chaque ligne du fichier INPUT (chaque crédit)
{
# Suppression des "" intempestifs
  $_ =~ s/\"//g;

# Récupération de la ligne dans un tableau, le séparateur est ';'
  @infos = split(/;/, $_);
  %tmp = %{$r->{ "produit_credit" }};
  $regExp = makeRegExp(\%tmp);


# -- Validation du string
  if ($infos[0] !~ /$regExp/)
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : Le produit doit être renseigné : ".$infos[0]."\n");
  }
  $id_prod = $r->{"produit_credit"}{$infos[0]}{"id_prod"};
  $devise_prod = $r->{"produit_credit"}{$infos[0]}{"devise"};
  $duree_periodicite = $r->{"produit_credit"}{$infos[0]}{"duree_periodicite"};

  # On récupère la périodicité du produit de crédit et l'id de l'agence
  @prod_cred = `psql -A -d adbanking -U adbanking -c \'SELECT periodicite, id_ag FROM adsys_produit_credit WHERE id = $id_prod\'`;
	($periodicite, $id_ag) = split(/\|/, $prod_cred[1]);

	# Récupération des parametres de l'agence : base de calcul des intérêts
  @base_taux = `psql -A -d adbanking -U adbanking -c \'SELECT base_taux FROM ad_agc where id_ag = $id_ag\'`;
  $base_taux = $base_taux[1];

  # date etat du credit doit etre la date du jour
  use POSIX qw(strftime);
  my $date_etat = strftime "%d/%m/%Y", localtime;

  ## Ancien ID client
  $infos[1] =~ s/\'//g;
  if ($use_anc_num == 1)
  {
    if (!(exists $infos[1]) || ($infos[1] eq ''))
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de client n'est pas renseigné\n");
    }
    elsif ($infos[1] !~ m/^[a-zA-Z0-9]+$/ )
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de client n'est pas alphanumérique\n");
    }
    $anc_id_client = $infos[1];

    @recup_id_client = `psql -A -d adbanking -U adbanking -c \"SELECT id_client FROM ad_cli where anc_id_client = '$anc_id_client'\"`;
  	# calcul le nombre de résultats renvoyés par la requête: si 0 le client ne se trouve pas dans la base de données
  	# on retranche 2 pour enlever les 2 lignes correpondantes resp aux libellés des champs et à celle qui permet de préciser le nombre de lignes renvoyées
    $taille = @recup_id_client - 2;
    if ($taille == 0){ # aucun resultat trouvé
			$error++;
      $errormsg .= ("ERREUR Ligne $i : Le client ne se trouve pas dans la base : ".$anc_id_client."\n");
    }
    $id_client = $recup_id_client[1];
  }
  else
  {
# Ajout de l'ID dans la liste
    $anc_id_client = $infos[1];
    if (defined($AncIdClis{$anc_id_client}))
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de client $anc_id_client existe déjà\n");
    }
    $AncIdClis{$anc_id_client} = 1;

    $id_client = $infos[2];
  }


  if ($use_anc_num != 1 && ($infos[2] == 0 || $infos[2] == ""))
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : Le client  doit être renseigné : ".$infos[0]."\n");
  }

  # -- Validation de la date de la demande
   if (!(&isDate($infos[3])))
   {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : La date demande n'est pas valide : ".$infos[3]."\n");
   }
  # -- Assignation
   $date_dem = $infos[3];

   $isdate = &isDate($infos[3]);
   if ($date_demande > $date_jour){
         $error++;
         $errormsg .= ("ERREUR Ligne $i : La date demande est dans le futur : ".$infos[3]."\n");
   }
   if ($infos[4] == 0 || $infos[4] == "")
   {
     $error++;
     $errormsg .= ("ERREUR Ligne $i : L'objet de la demande  doit être renseigné : ".$infos[4]."\n");
   }
  $objet_dem = $infos[4];

  # Espace des caractères '
  $infos[4] =~ s/\'/\\\'/g;
  $detail_dem = $infos[5];
  #Montant du crédit
  @mnt_min = `psql -A -d adbanking -U adbanking -c \"SELECT mnt_min FROM adsys_produit_credit where id = '$id_prod'\"`;
  $mnt_min = $mnt_min[1]; #recupere le montant minimum pour le credit pour verifier si on le respect?
  if ($infos[6] == 0 || $infos[6] == "")
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : Le montant  demandé doit être différent de zéro : ".$infos[6]."\n");
  }
  if ($infos[6] < $mnt_min)
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : Le montant  demandé doit être supérieure au montant minimum ($mnt_min) du produit : ".$infos[6]."\n");
  }
  $mnt_dem = $infos[6];
  # Durée du crédit
  @duree_min = `psql -A -d adbanking -U adbanking -c \"SELECT duree_min_mois FROM adsys_produit_credit where id = '$id_prod'\"`;
  $duree_min = $duree_min[1]; #recupere le montant minimum pour le credit pour verifier si on le respect?
  if ($infos[7] == 0 || $infos[7] == "")
   {
     $error++;
     $errormsg .= ("ERREUR Ligne $i : La durée du crédit  doit être renseignée : ".$infos[7]."\n");
   }
  if ($infos[7] < $duree_min)
   {
     $error++;
     $errormsg .= ("ERREUR Ligne $i : La durée du crédit  doit être supérieure au duree minimum mois ($duree_min) du produit : ".$infos[7]."\n");
   }
  $duree = $infos[7];
  # Diffèré en jours
   $diff_jour = $infos[8];
   if($diff_jour == ""){
     $diff_jour = 0;
   }
  # Diffèré en échéances
   $diff_ech = $infos[9];
   if($diff_ech == ""){
     $diff_ech = 0;
   }
   if($diff_ech >= $duree){
     $error++;
     $errormsg .= ("ERREUR Ligne $i : La durée du crédit  doit être strictement supérieure au différé échéance : ".$diff_ech."\n");
   }
  # Délai de grace
   $delai_grace = $infos[10];
   if($delai_grace == ""){
     $delai_grace = 0;
   }

  # Id agent gestionnaire
   $agent_gestionnaire = $infos[11];
   if($agent_gestionnaire == ""){
     $agent_gestionnaire = 1;
   }
   # prélèvement automatique
    if (!&isFieldBool($infos[12])){
			$error++;
      $errormsg .= ("ERREUR Ligne $i : La valeur du champs prélèvement automatique n'est pas valide(elle doit être 'oui', 'non' ou vide) : ".$infos[12]."\n");
   }
   	if (uc($infos[12]) eq "YES"  || uc($infos[12]) eq "OUI"){
   	   $prelev_auto = "t";
   	}else{
   	   $prelev_auto = "f";
   	}
   # -- Validation de la date d'approbation
   if (!(&isDate($infos[13])))
   {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : La date d'approbation n'est pas valide : ".$infos[13]."\n");
   }
  # -- Assignation
   $date_approb = $infos[13];
  ($j,$m,$a) = split("/",$date_approb);
   $date_approbation2 = $a."-".$m."-".$j;
  ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
   $date_jour =  (1900+$year)."-".($mon+1)."-".$mday;
   if ($date_approbation2 > $date_jour){
         $error++;
         $errormsg .= ("ERREUR Ligne $i : La date d'approbation est dans le futur : ".$infos[13]."\n");
   }
   # -- Validation de la date de déblocage
   if (!(&isDate($infos[14])))
   {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : La date de déblocage n'est pas valide : ".$infos[14]."\n");
   }
  # -- Assignation
   $date_debloc = $infos[14];
  ($j,$m,$a) = split("/",$date_debloc);
   $date_debloc2 = $a."-".$m."-".$j;
  ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
   $date_jour =  (1900+$year)."-".($mon+1)."-".$mday;
   if ($date_debloc2 > $date_jour){
         $error++;
         $errormsg .= ("ERREUR Ligne $i : La date de déblocage est dans le futur : ".$infos[14]."\n");
   }

	 # commission
  if (!&isFieldBool($infos[15])){
			$error++;
      $errormsg .= ("ERREUR Ligne $i : La valeur du champs commission n'est pas valide(elle doit être 'oui', 'non' ou vide) : ".$infos[15]."\n");
  }
  if (uc($infos[15]) eq "YES"  || uc($infos[15]) eq "OUI"){
    $commission ='t';
  }else{
    $commission ='f';
  }
  # Assurance
   if (!&isFieldBool($infos[16])){
			$error++;
      $errormsg .= ("ERREUR Ligne $i : La valeur du champs assurance n'est pas valide(elle doit être 'oui', 'non' ou vide) : ".$infos[16]."\n");
  }

  if(uc($infos[16]) eq "YES"  || uc($infos[16]) eq "OUI"){
    $assurance ='t';
  }else{
    $assurance ='f';
  }

  # Garanties numéraires
  $gar_num = $infos[17];
  if($gar_num == ""){
    $gar_num =0;
  }

  # Garanties matérielles
  $gar_mat = $infos[18];
  if($gar_mat == ""){
    $gar_mat =0;
  }
  else{
  	# Type du bien
  	$type_bien = $infos[19];
  	if($type_bien !~ m/^[0-9]+$/) {
    	$error++;
    	$errormsg .= ("ERREUR Ligne $i : Le type du bien materiel n'est pas renseigné ou sa valeur n'est pas numérique: ".$infos[19]."\n");
  	}
  	# Description du bien
  	$description = $infos[20];
  	if($description eq ""){
    	$error++;
    	$errormsg .= ("ERREUR Ligne $i : La description du bien materiel n'est pas renseignée : ".$infos[20]."\n");
  	}

  }

 # Id du groupe, si c'est un credit de groupe
  $id_group = $infos[21];

# Categorie du groupe, si c'est un credit de groupe
  $gs_cat = $infos[22];

# Montant octroyé du crédit
  if ($infos[23] == 0 || $infos[23] == "")
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : Le montant octroyé doit être différent de zéro : ".$infos[23]."\n");
  }
 $mnt_octr = $infos[23];

# Intérêt total du crédit
  if ($infos[24] == "")
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : L'intérêt total doit être renseigné : ".$infos[24]."\n");
  }
  $int_tot = $infos[24];

# Capital restant du crédit
  if ($infos[25] == "")
  {
    $cap_rest = 0;
  }
  else{
   if ($infos[25] > $mnt_octr)
   {
  	$error++;
    $errormsg .= ("ERREUR Ligne $i : Le capilal restant doit être inférieur au montant octroyé : ".$infos[25]."\n");
   }
   $cap_rest = $infos[25];
  }
# Intérêt restant du crédit
  if ($infos[26] == "")
  {
    $int_rest= 0;
  }
  else{
  if ($infos[26] > $int_tot)
  {
  	$error++;
    $errormsg .= ("ERREUR Ligne $i : L'intérêt restant doit être inférieur à l'intérêt total : ".$infos[26]."\n");
  }
  $int_rest = $infos[26];
  }

# Date du dernier remboursement
	$date_dern_remb = $infos[27];

# Suspension pénalité pour le dossier de crédit
  if (!&isFieldBool($infos[28])){
    $error++;
    $errormsg .= ("ERREUR Ligne $i : La valeur du champ suspension pénalité n'est pas valide(elle doit être 'oui', 'non' ou vide) : ".$infos[28]."\n");
  }
  $suspension_penalite = 'f'; #par defaut
  $suspension_penalite = isFieldBoolOui($infos[28]);
  #print "\n\nTest suspension pen = '$suspension_penalite'\n";

# Variable contenant la date et l'heure
  $now = gmtime;

# Récuperation mode de calcul interets pour en prendre compte mode calcul 'dégressif'
  @mode_calcul_int = `psql -A -d adbanking -U adbanking -c \'SELECT mode_calc_int from adsys_produit_credit where id = $id_prod and id_ag = $id_ag\'`;
   $mode_calcul_int = $mode_calcul_int[1];

  if($gs_cat == 2){ # credit groupe solidaire à dossiers multiples, plusieurs dossiers réels et un seul dossier fictif
  	$num_ligne = $i;
   # Récupération du compte de liaison du credit, on prend le compte de base du client
   @cpt_liaison = `psql -A -d adbanking -U adbanking -c \'SELECT id_cpte from ad_cpt where id_titulaire = $id_client and id_prod = 1 and id_ag = $id_ag\'`;
   $cpt_liaison = $cpt_liaison[1];

   # Récupération numero de credit pour le client
   @num_cre = `psql -A -d adbanking -U adbanking -c \'SELECT COUNT(id_doss) from ad_dcr where id_client = $id_client and id_ag = $id_ag\'`;
   $num_cre = $num_cre[1] + 1;

  	# Pour le dossier fictif
		$group_prod = $id_group."_".$id_prod;
		$doss_multi_fictif{$group_prod}{"id_membre"} = $id_group;
		$doss_multi_fictif{$group_prod}{"id_prod"} = $id_prod;
		$doss_multi_fictif{$group_prod}{"obj_dem"} = $objet_dem;
		$doss_multi_fictif{$group_prod}{"detail_dem"} = $detail_dem;
		$doss_multi_fictif{$group_prod}{"gs_cat"} = 2;
		$doss_multi_fictif{$group_prod}{"mnt_dem"} = $doss_multi_fictif{$group_prod}{"mnt_dem"} + $mnt_dem;
		$doss_multi_fictif{$group_prod}{"clients"} = $doss_multi_fictif{$group_prod}{'clients'}."$id_client-";

		# Pour les dossiers réels
		$cle_client = $group_prod."_".$id_client;
		$doss_multi_reel{$cle_client}{"num_ligne"} = $num_ligne;
		$doss_multi_reel{$cle_client}{"id_group"} = $id_group;
		$doss_multi_reel{$cle_client}{"id_prod"} = $id_prod;
		$doss_multi_reel{$cle_client}{"date_dem"} = $date_dem;
		$doss_multi_reel{$cle_client}{"mnt_dem"} = $mnt_dem;
		$doss_multi_reel{$cle_client}{"objet_dem"} = $objet_dem;
		$doss_multi_reel{$cle_client}{"detail_dem"} = $detail_dem;
		$doss_multi_reel{$cle_client}{"duree"} = $duree;
		$doss_multi_reel{$cle_client}{"periodicite"} = $periodicite;
		$doss_multi_reel{$cle_client}{"duree_periodicite"} = $duree_periodicite;
		$doss_multi_reel{$cle_client}{"diff_jour"} = $diff_jour;
		$doss_multi_reel{$cle_client}{"diff_ech"} = $diff_ech;
		$doss_multi_reel{$cle_client}{"delai_grace"} = $delai_grace;
		$doss_multi_reel{$cle_client}{"etat"} = 10;
		$doss_multi_reel{$cle_client}{"date_etat"} = $date_etat;
		$doss_multi_reel{$cle_client}{"cpt_liaison"} = $cpt_liaison;
		$doss_multi_reel{$cle_client}{"agent_gestionnaire"} = $agent_gestionnaire;
		$doss_multi_reel{$cle_client}{"prelev_auto"} = $prelev_auto;
		$doss_multi_reel{$cle_client}{"date_approb"} = $date_approb;
		$doss_multi_reel{$cle_client}{"date_debloc"} = $date_debloc;
		$doss_multi_reel{$cle_client}{"date_dern_remb"} = $date_dern_remb;
		$doss_multi_reel{$cle_client}{"mnt_octr"} = $mnt_octr;
		$doss_multi_reel{$cle_client}{"cap_rest"} = $cap_rest;
		$doss_multi_reel{$cle_client}{"int_tot"} = $int_tot;
		$doss_multi_reel{$cle_client}{"int_rest"} = $int_rest;
		$doss_multi_reel{$cle_client}{"commission"} = $commission;
		$doss_multi_reel{$cle_client}{"assurance"} = $assurance;
		$doss_multi_reel{$cle_client}{"gar_num"} = $gar_num;
		$doss_multi_reel{$cle_client}{"gar_mat"} = $gar_mat;
		$doss_multi_reel{$cle_client}{"type_bien"} = $type_bien;
		$doss_multi_reel{$cle_client}{"description"} = $description;
		$doss_multi_reel{$cle_client}{"etat_gar"} = $etat_gar;
		$doss_multi_reel{$cle_client}{"gs_cat"} = 2;
		$doss_multi_reel{$cle_client}{"suspension_pen"} = $suspension_penalite;
		$doss_multi_reel{$cle_client}{"num_cre"} = $num_cre;


  }
  elsif($gs_cat == 1) { # credit groupe solidaire à dossier unique, un dossier reel et plusieurs dossiers fictifs
  	# Pour le dossier réel
  	$num_ligne = $i;
  	# Récupération du compte de liaison du credit, on prend le compte de base du client
    @cpt_liaison = `psql -A -d adbanking -U adbanking -c \'SELECT id_cpte from ad_cpt where id_titulaire = $id_group and id_prod = 1 and id_ag = $id_ag\'`;
    $cpt_liaison = $cpt_liaison[1];

    # Récupération numero de credit pour le client
    @num_cre = `psql -A -d adbanking -U adbanking -c \'SELECT COUNT(id_doss) from ad_dcr where id_client = $id_client and id_ag = $id_ag\'`;
    $num_cre = $num_cre[1] + 1;

  	$group_prod = $id_group."_".$id_prod;
  	$doss_uni_reel{$group_prod}{"id_group"} = $id_group;
		$doss_uni_reel{$group_prod}{"id_prod"} = $id_prod;
		$doss_uni_reel{$group_prod}{"date_dem"} = $date_dem;
		$doss_uni_reel{$group_prod}{"mnt_dem"} = $doss_uni_reel{$group_prod}{"mnt_dem"} + $mnt_dem;
		$doss_uni_reel{$group_prod}{"clients"} = $doss_uni_reel{$group_prod}{'clients'}."$id_client-";
		$doss_uni_reel{$group_prod}{"objet_dem"} = $objet_dem;
		$doss_uni_reel{$group_prod}{"detail_dem"} = $detail_dem;
		$doss_uni_reel{$group_prod}{"duree"} = $duree;
		$doss_uni_reel{$group_prod}{"periodicite"} = $periodicite;
		$doss_uni_reel{$group_prod}{"duree_periodicite"} = $duree_periodicite;
		$doss_uni_reel{$group_prod}{"diff_jour"} = $diff_jour;
		$doss_uni_reel{$group_prod}{"diff_ech"} = $diff_ech;
		$doss_uni_reel{$group_prod}{"delai_grace"} = $delai_grace;
		$doss_uni_reel{$group_prod}{"etat"} = 10;
		$doss_uni_reel{$group_prod}{"date_etat"} = $date_etat;
		$doss_uni_reel{$group_prod}{"cpt_liaison"} = $cpt_liaison;
		$doss_uni_reel{$group_prod}{"agent_gestionnaire"} = $agent_gestionnaire;
		$doss_uni_reel{$group_prod}{"prelev_auto"} = $prelev_auto;
		$doss_uni_reel{$group_prod}{"date_approb"} = $date_approb;
		$doss_uni_reel{$group_prod}{"date_debloc"} = $date_debloc;
		$doss_uni_reel{$group_prod}{"date_dern_remb"} = $date_dern_remb;
		$doss_uni_reel{$group_prod}{"mnt_octr"} = $mnt_octr;
		$doss_uni_reel{$group_prod}{"cap_rest"} = $cap_rest;
		$doss_uni_reel{$group_prod}{"int_tot"} = $int_tot;
		$doss_uni_reel{$group_prod}{"int_rest"} = $int_rest;
		$doss_uni_reel{$group_prod}{"commission"} = $commission;
		$doss_uni_reel{$group_prod}{"assurance"} = $assurance;
		$doss_uni_reel{$group_prod}{"gar_num"} = $gar_num;
		$doss_uni_reel{$group_prod}{"gar_mat"} = $gar_mat;
		$doss_uni_reel{$group_prod}{"type_bien"} = $type_bien;
		$doss_uni_reel{$group_prod}{"description"} = $description;
		$doss_uni_reel{$group_prod}{"etat_gar"} = $etat_gar;
		$doss_uni_reel{$group_prod}{"gs_cat"} = 1;
		$doss_uni_reel{$group_prod}{"suspension_pen"} = $suspension_penalite;
		$doss_uni_reel{$group_prod}{"num_cre"} = $num_cre;

		# Pour les dossiers fictifs
		$cle_client = $id_group."_".$id_prod."_".$id_client;
		$doss_uni_fictif{$cle_client}{"num_ligne"} = $num_ligne;
		$doss_uni_fictif{$cle_client}{"id_membre"} = $id_client;
		$doss_uni_fictif{$cle_client}{"obj_dem"} = $objet_dem;
		$doss_uni_fictif{$cle_client}{"detail_dem"} = $detail_dem;
		$doss_uni_fictif{$cle_client}{"mnt_dem"} = $mnt_dem;
		$doss_uni_fictif{$cle_client}{"mnt_octr"} = $mnt_octr;
		$doss_uni_fictif{$cle_client}{"cap_rest"} = $cap_rest;
		$doss_uni_fictif{$cle_client}{"int_tot"} = $int_tot;
		$doss_uni_fictif{$cle_client}{"int_rest"} = $int_rest;
		$doss_uni_fictif{$cle_client}{"date_debloc"} = $date_debloc;
		$doss_uni_fictif{$cle_client}{"duree"} = $duree;
		$doss_uni_fictif{$cle_client}{"diff_jour"} = $diff_jour;
		$doss_uni_fictif{$cle_client}{"diff_ech"} = $diff_ech;
		$doss_uni_fictif{$cle_client}{"delai_grace"} = $delai_grace;
		$doss_uni_fictif{$cle_client}{"gar_num"} = $gar_num;
		$doss_uni_fictif{$cle_client}{"gar_mat"} = $gar_mat;
		$doss_uni_fictif{$cle_client}{"gs_cat"} = 1;


  }
  else { # credit simple
  	$num_ligne = $i;
  	# Récupération du compte de liaison du credit, on prend le compte de base du client
    @cpt_liaison = `psql -A -d adbanking -U adbanking -c \'SELECT id_cpte from ad_cpt where id_titulaire = $id_client and id_prod = 1 and id_ag = $id_ag\'`;
    $cpt_liaison = $cpt_liaison[1];

    # Récupération numero de credit pour le client
    @num_cre = `psql -A -d adbanking -U adbanking -c \'SELECT COUNT(id_doss) from ad_dcr where id_client = $id_client and id_ag = $id_ag\'`;
    $num_cre = $num_cre[1] + 1;

  	print OUTPUT "\n-- CREDIT SIMPLE, ligne $num_ligne \n";

		# Insertion du dossier de crédit
  	print OUTPUT "\n--- Insertion du dossier de crédit, ligne $num_ligne\n";

  	$sql = "INSERT INTO ad_dcr(id_ag, id_prod, id_client, date_dem, mnt_dem, obj_dem, detail_obj_dem, duree_mois, differe_jours, differe_ech, delai_grac, etat, date_etat, cpt_liaison, id_agent_gest, prelev_auto, cre_date_approb, cre_date_debloc, cre_mnt_octr, prelev_commission, assurances_cre, doss_repris, suspension_pen, num_cre)";
    $sql .= " VALUES (NumAgc(), $id_prod, $id_client, '$date_dem', '$mnt_dem', '$objet_dem', '$detail_dem', $duree, $diff_jour, $diff_ech, $delai_grace, 10, '$date_etat', $cpt_liaison, $agent_gestionnaire, '$prelev_auto', '$date_approb', '$date_debloc', '$mnt_octr', '$commission', '$assurance', 't', '$suspension_penalite', $num_cre); ";
		print OUTPUT $sql."\n";

    # Insertion des garanties mobilisées
    if($gar_num != 0){# S'il existe des garanties numéraires mobilisées

        if($gar_num > 0){ # si on a une garantie numeraire alors creer un compte nantie avec la garantie
            #Recupere rang disponible 'ad_cpt' pour le client
            @rang_dispo = `psql -A -d adbanking -U adbanking -c \'SELECT MAX(num_cpte) from ad_cpt where id_titulaire = $id_client and id_ag = $id_ag\'`;
            $rang_dispo = $rang_dispo[1];
            $rang = $rang_dispo + 1;
            #Recupere le type de numerotation compte de l'agence
            @type_num_cpte = `psql -A -d adbanking -U adbanking -c \'SELECT type_numerotation_compte from ad_agc where id_ag = numagc()\'`;
            $type_num_cpte = $type_num_cpte[1];
            #Recupere le num compte complet de la nouvelle compte de garantie pour le client
            $num_complet_cpte = makeNumCpte($type_num_cpte,$rang,$id_client,$id_ag);
            #Recuperation des infos manquantes pour la creation compte
            @info_cpte = `psql -A -d adbanking -U adbanking -c \'SELECT utilis_crea, devise, num_last_cheque, etat_chequier, chequier_num_cheques from ad_cpt where id_titulaire = $id_client and id_prod = 1 and id_ag = $id_ag\'`;
            ($utils_crea, $devise, $num_last_cheque, $etat_chequier, $chequier_num_cheques) = split (/\|/, $info_cpte[1]);
            $date_ouvert = $date_etat; $id_titulaire = $id_client; $solde = $gar_num; $date_creation = $date_etat;
            #print "Num Complet Compte = $num_complet_cpte\n";
            # Creation compte de garantie pour le client
            print OUTPUT "\n--- Si on a une garantie numeraire alors creer un compte nantie avec la garantie, client $id_client\n";
            $sql = "INSERT INTO ad_cpt(id_titulaire, date_ouvert, utilis_crea, etat_cpte, solde, interet_annuel, interet_a_capitaliser, solde_calcul_interets, solde_clot, mnt_bloq, num_cpte, num_complet_cpte, id_prod, devise, num_last_cheque, etat_chequier, chequier_num_cheques, mnt_min_cpte, solde_part_soc_restant, id_ag, date_creation, mnt_bloq_cre)";
            $sql .= "VALUES ($id_titulaire, '$date_ouvert', $utils_crea, 3, $solde, 0, 0, 0, 0, 0, $rang, '$num_complet_cpte', 4, '$devise', $num_last_cheque, $etat_chequier, $chequier_num_cheques, 0, 0, $id_ag, '$date_creation', 0);\n";
            print OUTPUT $sql."\n";
        }

        print OUTPUT "\n--- Insertion des garanties numeraires pour le credit, ligne $num_ligne\n";
        $sql = "INSERT INTO ad_gar(id_doss, type_gar, gar_num_id_cpte_prelev, gar_num_id_cpte_nantie, etat_gar, montant_vente, devise_vente, id_ag)";
        $sql .= "VALUES (currval('ad_dcr_id_doss_seq'), 1, $id_client, currval('ad_cpt_id_cpte_seq'), 3, $gar_num, '$devise_prod', NumAgc());";
        print OUTPUT $sql."\n";
    }

		if($gar_mat != 0){# S'il existe des garanties materielles mobilisées
		print OUTPUT "\n--- Insertion des garanties materielles pour le credit, ligne $num_ligne\n";
    $sql = "INSERT INTO ad_biens(id_client, type_bien, description, valeur_estimee, devise_valeur, id_ag)";
    $sql .= "VALUES ($id_client, $type_bien, '$description', $gar_mat, '$devise_prod', NumAgc());";
    print OUTPUT $sql."\n";
    $sql = "INSERT INTO ad_gar(id_doss, type_gar, gar_mat_id_bien, etat_gar, montant_vente, devise_vente, id_ag)";
    $sql .= "VALUES (currval('ad_dcr_id_doss_seq'), 2, currval('ad_biens_id_bien_seq'), 3, $gar_mat, '$devise_prod', NumAgc());";
    print OUTPUT $sql."\n";
		}

		# Insertion des échéances et des remboursements
		print OUTPUT "\n--- Insertion des échéances et des remboursements pour le credit, ligne $num_ligne\n";

		%args = ( 'num_ligne'  => $num_ligne,
		          'mnt_octr' => $mnt_octr,
		          'int_tot' => $int_tot,
		          'cap_rest' => $cap_rest,
		          'int_rest' => $int_rest,
		          'duree' => $duree,
		          'diff_ech' => $diff_ech,
		          'diff_jour' => $diff_jour,
		          'date_debloc' => $date_debloc,
		          'date_dern_remb' => $date_dern_remb,
		          'periodicite' => $periodicite,
		          'duree_periodicite' => $duree_periodicite,
		          'base_taux' => $base_taux,
		          'mode_calcul_int' => $mode_calcul_int,
		          'id_prod' => $id_prod);
		insereEchRemb(%args);
  }
  $i++;

} # Fin du traitement du crédit, passage à la ligne suivante

#Traitement des credits à dossiers multiples des groupes solidaires
print OUTPUT "\n/* TRAITEMENT DES CREDITS A DOSSIERS MULTIPLES DES GROUPES SOLIDAIRES*/\n";
$doss = 1;
@cle = keys(%doss_multi_fictif);
 foreach $group_prod (@cle) {

		#Insertion du dossier fictif
		print OUTPUT "\n-- TRAITEMENT DU DOSSIER $doss\n";
  	print OUTPUT "\n-- Insertion du dossier fictif $doss\n";
		$id_group = $doss_multi_fictif{$group_prod}{"id_membre"};
		$obj_dem = $doss_multi_fictif{$group_prod}{"obj_dem"};
		$detail_obj_dem = $doss_multi_fictif{$group_prod}{"detail_dem"};
		$mnt_dem = $doss_multi_fictif{$group_prod}{"mnt_dem"};
		$gs_cat = $doss_multi_fictif{$group_prod}{"gs_cat"};

		$sql = "INSERT INTO ad_dcr_grp_sol(id_ag, id_membre, obj_dem, detail_obj_dem, mnt_dem, gs_cat)";
		$sql .= " VALUES (NumAgc(), $id_group, '$obj_dem','$detail_obj_dem', '$mnt_dem', '$gs_cat'); ";
		print OUTPUT $sql."\n";

  	print OUTPUT "\n-- Insertion des dossiers réels\n";
			#Insertion des dossiers réels
		@clients = split(/-/, $doss_multi_fictif{$group_prod}{"clients"});
		for ($k=0; $k <= $#clients; $k++) {
			$id_client = $clients[$k];
			$cle_client = $group_prod."_".$id_client;
			$num_ligne = $doss_multi_reel{$cle_client}{"num_ligne"};
			$id_prod = $doss_multi_reel{$cle_client}{"id_prod"};
			$date_dem = $doss_multi_reel{$cle_client}{"date_dem"};
			$mnt_dem = $doss_multi_reel{$cle_client}{"mnt_dem"};
			$objet_dem = $doss_multi_reel{$cle_client}{"objet_dem"};
			$detail_dem = $doss_multi_reel{$cle_client}{"detail_dem"};
			$duree = $doss_multi_reel{$cle_client}{"duree"};
			$periodicite = $doss_multi_reel{$cle_client}{"periodicite"};
			$duree_periodicite = $doss_multi_reel{$cle_client}{"duree_periodicite"};
			$diff_jour = $doss_multi_reel{$cle_client}{"diff_jour"};
			$diff_ech = $doss_multi_reel{$cle_client}{"diff_ech"};
			$delai_grace = $doss_multi_reel{$cle_client}{"delai_grace"};
			$etat = $doss_multi_reel{$cle_client}{"etat"};
			$date_etat = $doss_multi_reel{$cle_client}{"date_etat"};
			$cpt_liaison = $doss_multi_reel{$cle_client}{"cpt_liaison"};
			$agent_gestionnaire = $doss_multi_reel{$cle_client}{"agent_gestionnaire"};
			$prelev_auto = $doss_multi_reel{$cle_client}{"prelev_auto"};
			$date_approb = $doss_multi_reel{$cle_client}{"date_approb"};
			$date_debloc = $doss_multi_reel{$cle_client}{"date_debloc"};
			$date_dern_remb = $doss_multi_reel{$cle_client}{"date_dern_remb"};
			$mnt_octr = $doss_multi_reel{$cle_client}{"mnt_octr"};
			$cap_rest = $doss_multi_reel{$cle_client}{"cap_rest"};
			$int_tot = $doss_multi_reel{$cle_client}{"int_tot"};
			$int_rest = $doss_multi_reel{$cle_client}{"int_rest"};
			$commission = $doss_multi_reel{$cle_client}{"commission"};
			$assurance = $doss_multi_reel{$cle_client}{"assurance"};
			$gar_num = $doss_multi_reel{$cle_client}{"gar_num"};
			$gar_mat = $doss_multi_reel{$cle_client}{"gar_mat"};
			$type_bien = $doss_multi_reel{$cle_client}{"type_bien"};
			$description = $doss_multi_reel{$cle_client}{"description"};
			$etat_gar = $doss_multi_reel{$cle_client}{"etat_gar"};
			$gs_cat = $doss_multi_reel{$cle_client}{"gs_cat"};
			$suspension_pen = $doss_multi_reel{$cle_client}{"suspension_pen"};
			$num_cre = $doss_multi_reel{$cle_client}{"num_cre"};

			print OUTPUT "\n--- Insertion du dossier pour le client $id_client\n";
			$sql = "INSERT INTO ad_dcr(id_ag, id_prod, id_client, date_dem, mnt_dem, obj_dem, detail_obj_dem, duree_mois, differe_jours, differe_ech, delai_grac, etat, date_etat, cpt_liaison, id_agent_gest, prelev_auto, cre_date_approb, cre_date_debloc, cre_mnt_octr, prelev_commission, assurances_cre, gs_cat, id_dcr_grp_sol, doss_repris, suspension_pen, num_cre)";
   		$sql .= " VALUES (NumAgc(), $id_prod, $id_client, '$date_dem', '$mnt_dem', '$objet_dem', '$detail_dem', $duree, $diff_jour, $diff_ech, $delai_grace, 10, '$date_etat', $cpt_liaison, $agent_gestionnaire, '$prelev_auto', '$date_approb', '$date_debloc', '$mnt_octr', '$commission', '$assurance', '$gs_cat', currval('ad_dcr_grp_sol_id_seq'), 't', '$suspension_penalite', $num_cre); ";
			print OUTPUT $sql."\n";

   			# Insertion des garanties mobilisées
   		if($gar_num != 0){# S'il existe des garanties numéraires mobilisées

   		    if($gar_num > 0){ # si on a une garantie numeraire alors creer un compte nantie avec la garantie
                #Recupere rang disponible 'ad_cpt' pour le client
                @rang_dispo = `psql -A -d adbanking -U adbanking -c \'SELECT MAX(num_cpte) from ad_cpt where id_titulaire = $id_client and id_ag = $id_ag\'`;
                $rang_dispo = $rang_dispo[1];
                $rang = $rang_dispo + 1;
                #Recupere le type de numerotation compte de l'agence
                @type_num_cpte = `psql -A -d adbanking -U adbanking -c \'SELECT type_numerotation_compte from ad_agc where id_ag = numagc()\'`;
                $type_num_cpte = $type_num_cpte[1];
                #Recupere le num compte complet de la nouvelle compte de garantie pour le client
                $num_complet_cpte = makeNumCpte($type_num_cpte,$rang,$id_client,$id_ag);
                #Recuperation des infos manquantes pour la creation compte
                @info_cpte = `psql -A -d adbanking -U adbanking -c \'SELECT utilis_crea, devise, num_last_cheque, etat_chequier, chequier_num_cheques from ad_cpt where id_titulaire = $id_client and id_prod = 1 and id_ag = $id_ag\'`;
                ($utils_crea, $devise, $num_last_cheque, $etat_chequier, $chequier_num_cheques) = split (/\|/, $info_cpte[1]);
                $date_ouvert = $date_etat; $id_titulaire = $id_client; $solde = $gar_num; $date_creation = $date_etat;
                #print "Num Complet Compte = $num_complet_cpte\n";
                # Creation compte de garantie pour le client
                print OUTPUT "\n--- Si on a une garantie numeraire alors creer un compte nantie avec la garantie, client $id_client\n";
                $sql = "INSERT INTO ad_cpt(id_titulaire, date_ouvert, utilis_crea, etat_cpte, solde, interet_annuel, interet_a_capitaliser, solde_calcul_interets, solde_clot, mnt_bloq, num_cpte, num_complet_cpte, id_prod, devise, num_last_cheque, etat_chequier, chequier_num_cheques, mnt_min_cpte, solde_part_soc_restant, id_ag, date_creation, mnt_bloq_cre)";
                $sql .= "VALUES ($id_titulaire, '$date_ouvert', $utils_crea, 3, $solde, 0, 0, 0, 0, 0, $rang, '$num_complet_cpte', 4, '$devise', $num_last_cheque, $etat_chequier, $chequier_num_cheques, 0, 0, $id_ag, '$date_creation', 0);\n";
                print OUTPUT $sql."\n";
            }

   			print OUTPUT "\n--- Insertion des garanties numeraires mobilisées par le client $id_client\n";
    		$sql = "INSERT INTO ad_gar(id_doss, type_gar, gar_num_id_cpte_prelev, gar_num_id_cpte_nantie, etat_gar, montant_vente, devise_vente, id_ag)";
    		$sql .= "VALUES (currval('ad_dcr_id_doss_seq'), 1, $id_client, currval('ad_cpt_id_cpte_seq'), 3, $gar_num, '$devise_prod', NumAgc());";
    		print OUTPUT $sql."\n";
   		}
			if($gar_mat != 0){# S'il existe des garanties materielles mobilisées
				print OUTPUT "\n--- Insertion des garanties materielles mobilisées par le client $id_client\n";
    		$sql = "INSERT INTO ad_biens(id_client, type_bien, description, valeur_estimee, devise_valeur, id_ag)";
    		$sql .= "VALUES ($id_client, $type_bien, '$description', $gar_mat, '$devise_prod', NumAgc());";
    		print OUTPUT $sql."\n";
    		$sql = "INSERT INTO ad_gar(id_doss, type_gar, gar_mat_id_bien, etat_gar, montant_vente, devise_vente, id_ag)";
    		$sql .= "VALUES (currval('ad_dcr_id_doss_seq'), 2, currval('ad_biens_id_bien_seq'), 3, $gar_mat, '$devise_prod', NumAgc());";
    		print OUTPUT $sql."\n";
			}
			# Insertion des échéances et des remboursements
		print OUTPUT "\n--- Insertion des échéances et des remboursements pour le credit du client $id_client\n";

		%args = ( 'num_ligne'  => $num_ligne,
		          'mnt_octr' => $mnt_octr,
		          'int_tot' => $int_tot,
		          'cap_rest' => $cap_rest,
		          'int_rest' => $int_rest,
		          'duree' => $duree,
		          'diff_ech' => $diff_ech,
		          'diff_jour' => $diff_jour,
		          'date_debloc' => $date_debloc,
		          'date_dern_remb' => $date_dern_remb,
		          'periodicite' => $periodicite,
		          'duree_periodicite' => $duree_periodicite,
		          'base_taux' => $base_taux,
		          'mode_calcul_int' => $mode_calcul_int,
                  'id_prod' => $id_prod);
		insereEchRemb(%args);
	}
			$doss++;

}

#Traitement des credits à dossier unique des groupes solidaires
print OUTPUT "\n/* TRAITEMENT DES CREDITS A DOSSIER UNIQUE DES GROUPES SOLIDAIRES */\n";
$doss = 1;
@cle = keys(%doss_uni_reel);
 foreach $group_prod (@cle) {

		#Insertion du dossier réel
		print OUTPUT "\n-- TRAITEMENT DU DOSSIER $doss\n";
  	print OUTPUT "\n-- Insertion du dosier réel $doss\n";
  	$id_group = $doss_uni_reel{$group_prod}{"id_group"};
  	$id_prod = $doss_uni_reel{$group_prod}{"id_prod"};
		$date_dem = $doss_uni_reel{$group_prod}{"date_dem"};
		$mnt_dem = $doss_uni_reel{$group_prod}{"mnt_dem"};
		$objet_dem = $doss_uni_reel{$group_prod}{"objet_dem"};
		$detail_dem = $doss_uni_reel{$group_prod}{"detail_dem"};
		$duree = $doss_uni_reel{$group_prod}{"duree"};
		$periodicite = $doss_uni_reel{$group_prod}{"periodicite"};
		$duree_periodicite = $doss_uni_reel{$group_prod}{"duree_periodicite"};
		$diff_jour = $doss_uni_reel{$group_prod}{"diff_jour"};
		$diff_ech = $doss_uni_reel{$group_prod}{"diff_ech"};
		$delai_grace = $doss_uni_reel{$group_prod}{"delai_grace"};
		$etat = $doss_uni_reel{$group_prod}{"etat"};
		$date_etat = $doss_uni_reel{$group_prod}{"date_etat"};
		$cpt_liaison = $doss_uni_reel{$group_prod}{"cpt_liaison"};
		$agent_gestionnaire = $doss_uni_reel{$group_prod}{"agent_gestionnaire"};
		$prelev_auto = $doss_uni_reel{$group_prod}{"prelev_auto"};
		$date_approb = $doss_uni_reel{$group_prod}{"date_approb"};
		$date_debloc = $doss_uni_reel{$group_prod}{"date_debloc"};
		$date_dern_remb = $doss_uni_reel{$group_prod}{"date_dern_remb"};
		$mnt_octr = $doss_uni_reel{$group_prod}{"mnt_octr"};
		$cap_rest = $doss_uni_reel{$group_prod}{"cap_rest"};
		$int_tot = $doss_uni_reel{$group_prod}{"int_tot"};
		$int_rest = $doss_uni_reel{$group_prod}{"int_rest"};
		$commission = $doss_uni_reel{$group_prod}{"commission"};
		$assurance = $doss_uni_reel{$group_prod}{"assurance"};
		$gar_num = $doss_uni_reel{$group_prod}{"gar_num"};
		$gar_mat = $doss_uni_reel{$group_prod}{"gar_mat"};
		$type_bien = $doss_uni_reel{$group_prod}{"type_bien"};
		$description = $doss_uni_reel{$group_prod}{"description"};
		$etat_gar = $doss_uni_reel{$group_prod}{"etat_gar"};
		$gs_cat = $doss_uni_reel{$group_prod}{"gs_cat"};
		$suspension_pen = $doss_uni_reel{$group_prod}{"suspension_pen"};
		$num_cre = $doss_uni_reel{$group_prod}{"num_cre"};

		$sql = "INSERT INTO ad_dcr(id_ag, id_prod, id_client, date_dem, mnt_dem, obj_dem, detail_obj_dem, duree_mois, differe_jours, differe_ech, delai_grac, etat, date_etat, cpt_liaison, id_agent_gest, prelev_auto, cre_date_approb, cre_date_debloc, cre_mnt_octr, prelev_commission, assurances_cre, gs_cat, doss_repris, suspension_pen, num_cre)";
    $sql .= " VALUES (NumAgc(), $id_prod, $id_group, '$date_dem', '$mnt_dem', '$objet_dem', '$detail_dem', $duree, $diff_jour, $diff_ech, $delai_grace, 10, '$date_etat', $cpt_liaison, $agent_gestionnaire, '$prelev_auto', '$date_approb', '$date_debloc', '$mnt_octr', '$commission', '$assurance', '$gs_cat', 't', '$suspension_penalite', $num_cre); ";
		print OUTPUT $sql."\n";

	  print OUTPUT "\n--- Insertion des dossiers fictifs\n";
		# Insertion des dossiers fictifs
			@clients = split(/-/, $doss_uni_reel{$group_prod}{"clients"});
			for ($k=0; $k <= $#clients; $k++) {
				$id_client = $clients[$k];
				$cle_client = $group_prod."_".$id_client;
				if ($k == 0){
					$cle_client_ref = $cle_client;
				}
				# verifier si les données saisies dans le fichier csv sont cohérentes pour le crédit à dossier unique
				$num_ligne = $doss_uni_fictif{$cle_client}{"num_ligne"};

				if ($doss_uni_fictif{$cle_client_ref}{"mnt_octr"} != $doss_uni_fictif{$cle_client}{"mnt_octr"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : Le montant octroyé et le montant octroyé à la Ligne  ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être égaux\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"cap_rest"} != $doss_uni_fictif{$cle_client}{"cap_rest"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : Le capital restant et le capital restant à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être égaux\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"int_tot"} != $doss_uni_fictif{$cle_client}{"int_tot"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : L'intérêt total et l'intérêt total à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être égaux\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"int_rest"} != $doss_uni_fictif{$cle_client}{"int_rest"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : L'intérêt restant et l'intérêt restant à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être égaux\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"gar_num"} != $doss_uni_fictif{$cle_client}{"gar_num"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : La garantie numéraire et la garantie numéraire à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être égaux\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"gar_mat"} != $doss_uni_fictif{$cle_client}{"gar_mat"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : La garantie matérielle et la garantie matérielle à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être égaux\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"date_debloc"} != $doss_uni_fictif{$cle_client}{"date_debloc"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : La date de déboursement et la date de déboursement à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être les mêmes\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"duree"} != $doss_uni_fictif{$cle_client}{"duree"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : La durée et la durée à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être les mêmes\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"diff_jour"}!= $doss_uni_fictif{$cle_client}{"diff_jour"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : Le différé jour et le différé jour à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être les mêmes\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"diff_ech"} != $doss_uni_fictif{$cle_client}{"diff_ech"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : Le différé échéance et le différé échéance à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être les mêmes\n");
				}
				if ($doss_uni_fictif{$cle_client_ref}{"delai_grace"} != $doss_uni_fictif{$cle_client}{"delai_grace"}){
					$error++;
    			$errormsg .= ("ERREUR credit groupe à dossier unique, Ligne $num_ligne : Le délai grace et le délai grace à la Ligne ".$doss_uni_fictif{$cle_client_ref}{"num_ligne"}." doivent être les mêmes\n");
				}
				$id_group = $doss_uni_fictif{$cle_client}{"id_membre"};
				$obj_dem = $doss_uni_fictif{$cle_client}{"obj_dem"};
				$detail_obj_dem = $doss_uni_fictif{$cle_client}{"detail_dem"};
				$mnt_dem = $doss_uni_fictif{$cle_client}{"mnt_dem"};
				$gs_cat = $doss_uni_fictif{$cle_client}{"gs_cat"};

				$sql = "INSERT INTO ad_dcr_grp_sol(id_ag, id_membre, obj_dem, detail_obj_dem, mnt_dem, gs_cat, id_dcr_grp_sol)";
 				$sql .= " VALUES (NumAgc(), $id_client, '$obj_dem','$detail_obj_dem', '$mnt_dem', '$gs_cat', currval('ad_dcr_id_doss_seq')); ";
 				print OUTPUT $sql."\n";
			}

    # Insertion des garanties mobilisées
    if($gar_num != 0){# S'il existe des garanties numéraires mobilisées

        if($gar_num > 0){ # si on a une garantie numeraire alors creer un compte nantie avec la garantie
            #Recupere rang disponible 'ad_cpt' pour le client
            @rang_dispo = `psql -A -d adbanking -U adbanking -c \'SELECT MAX(num_cpte) from ad_cpt where id_titulaire = $id_group and id_ag = $id_ag\'`;
            $rang_dispo = $rang_dispo[1];
            $rang = $rang_dispo + 1;
            #Recupere le type de numerotation compte de l'agence
            @type_num_cpte = `psql -A -d adbanking -U adbanking -c \'SELECT type_numerotation_compte from ad_agc where id_ag = numagc()\'`;
            $type_num_cpte = $type_num_cpte[1];
            #Recupere le num compte complet de la nouvelle compte de garantie pour le client
            $num_complet_cpte = makeNumCpte($type_num_cpte,$rang,$id_group,$id_ag);
            #Recuperation des infos manquantes pour la creation compte
            @info_cpte = `psql -A -d adbanking -U adbanking -c \'SELECT utilis_crea, devise, num_last_cheque, etat_chequier, chequier_num_cheques from ad_cpt where id_titulaire = $id_group and id_prod = 1 and id_ag = $id_ag\'`;
            ($utils_crea, $devise, $num_last_cheque, $etat_chequier, $chequier_num_cheques) = split (/\|/, $info_cpte[1]);
            $date_ouvert = $date_etat; $id_titulaire = $id_group; $solde = $gar_num; $date_creation = $date_etat;
            #print "Num Complet Compte = $num_complet_cpte\n";
            # Creation compte de garantie pour le client
            print OUTPUT "\n--- Si on a une garantie numeraire alors creer un compte nantie avec la garantie, client $id_client\n";
            $sql = "INSERT INTO ad_cpt(id_titulaire, date_ouvert, utilis_crea, etat_cpte, solde, interet_annuel, interet_a_capitaliser, solde_calcul_interets, solde_clot, mnt_bloq, num_cpte, num_complet_cpte, id_prod, devise, num_last_cheque, etat_chequier, chequier_num_cheques, mnt_min_cpte, solde_part_soc_restant, id_ag, date_creation, mnt_bloq_cre)";
            $sql .= "VALUES ($id_titulaire, '$date_ouvert', $utils_crea, 3, $solde, 0, 0, 0, 0, 0, $rang, '$num_complet_cpte', 4, '$devise', $num_last_cheque, $etat_chequier, $chequier_num_cheques, 0, 0, $id_ag, '$date_creation', 0);\n";
            print OUTPUT $sql."\n";
        }

    	print OUTPUT "\n--- Insertion des garanties numeraires pour le dossier $doss\n";
    	$sql = "INSERT INTO ad_gar(id_doss, type_gar, gar_num_id_cpte_prelev, gar_num_id_cpte_nantie, etat_gar, montant_vente, devise_vente, id_ag)";
    	$sql .= "VALUES (currval('ad_dcr_id_doss_seq'), 1, $id_group, currval('ad_cpt_id_cpte_seq'), 2, $gar_num, '$devise_prod', NumAgc());";
    	print OUTPUT $sql."\n";
    }
		if($gar_mat != 0){# S'il existe des garanties materielles mobilisées
			print OUTPUT "\n--- Insertion des garanties materielles pour le dossier $doss\n";
    	$sql = "INSERT INTO ad_biens(id_client, type_bien, description, valeur_estimee, devise_valeur, id_ag)";
    	$sql .= "VALUES ($id_group, $type_bien, '$description', $gar_mat, '$devise_prod', NumAgc());";
    	print OUTPUT $sql."\n";
    	$sql = "INSERT INTO ad_gar(id_doss, type_gar, gar_mat_id_bien, etat_gar, montant_vente, devise_vente, id_ag)";
    	$sql .= "VALUES (currval('ad_dcr_id_doss_seq'), 2, currval('ad_biens_id_bien_seq'), 3, $gar_mat, '$devise_prod', NumAgc());";
    	print OUTPUT $sql."\n";
		}

		# Insertion des échéances et des remboursements
		print OUTPUT "\n--- Insertion des échéances et des remboursements pour le crédit, ligne $i\n";

		%args = ( 'num_ligne'  => $num_ligne,
		          'mnt_octr' => $mnt_octr,
		          'int_tot' => $int_tot,
		          'cap_rest' => $cap_rest,
		          'int_rest' => $int_rest,
		          'duree' => $duree,
		          'diff_ech' => $diff_ech,
		          'diff_jour' => $diff_jour,
		          'date_debloc' => $date_debloc,
		          'date_dern_remb' => $date_dern_remb,
		          'periodicite' => $periodicite,
		          'duree_periodicite' => $duree_periodicite,
		          'base_taux' => $base_taux,
		          'mode_calcul_int' => $mode_calcul_int,
                  'id_prod' => $id_prod);
		insereEchRemb(%args);


			$doss++;

}


#--
#-- Requête SQL de mise à jour de la séquence sur id_doss en réinitialisant LAST_VALUE
#--
print OUTPUT "\n--- Mise à jour de de la dernière valeur de la séquence sur ID des crédits\n";
$sql = "SELECT setval('ad_dcr_id_doss_seq',(SELECT MAX(id_doss) FROM ad_dcr));\n";
print OUTPUT $sql;

#-------------------------------------------------------------------------------

print("Fermeture du fichier de reprise $inputFile\n");

print OUTPUT "\nCOMMIT;\n";

# Si aucune erreur
if ($error == 0)
{
# Fermeture des fichiers
  close(INPUT);
  close(OUTPUT);

  $i--; # On a compté une ligne de trop
    print("\n $i crédits ont été traités\n\n");
}
else
{
# Fermeture des fichiers
  close(INPUT);
  close(OUTPUT);

# Vide le fichier DATA.sql
  open(ERASE, ">DATA.sql");
  close(ERASE);

# Affiche les erreurs
  print("\n *** $error erreurs ont été rencontrées dans le fichier\n *** Le fichier n'a pas été généré\nLes erreurs ont été écrites dans le fichier $errorFile\n");
  open(ERRORFILE, ">$errorFile");
  print ERRORFILE $errormsg;
  close(ERRORFILE);
}
#}}}
