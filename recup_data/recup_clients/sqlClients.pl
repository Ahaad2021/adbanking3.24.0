#!/usr/bin/perl
# vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker:

######################################################################################
######################################################################################
#                      ----------   ADbanking v2.0 ----------                        #
#                      ---- Module de reprise des clients ---                        #
######################################################################################
######################################################################################

$traducFile = "../traduction.conf";	# Nom du fichier de configuration
$inputFile = "recup_clients.csv";	# Nom du fichier de données (format CSV)
$outputFile = "DATA.sql";		# Nom du fichier SQL à générer
$errorFile = "error.log";		# Nom du fichier de log
%comptes_substitut = ();		# Tableau contenant les comptes de substitut

#{{{ isDate : Fonction de vérification de la structure d'une date (JJ/MM/AAAA)
sub isDate
{
  if ($_[0] !~ /^[0-9]+\/[0-9]+\/[0-9]+$/)
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

              if ($section eq "produits")
              {
# Seulement pour la section produit
                $remp{$section}{$infos[0]}{"terme"} = $infos[2];
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
print OUTPUT "-- Fichier d'importation des clients généré automatiquement \n";
print OUTPUT "\\connect - adbanking\n";
print OUTPUT "BEGIN;\n";

# Ajout de la reprise dans l'historique
$now = gmtime;
print OUTPUT "\n-- Ajout de la reprise dans l'historique \n";
$sql = "INSERT INTO ad_his (id_ag, type_fonction,login,infos,date) VALUES (NumAgc(), 500, 'admin', 'Reprise des clients', '$now');";
print OUTPUT $sql."\n";

# Création de l'écriture
$sql = "INSERT INTO ad_ecriture (id_ag, id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture) VALUES (NumAgc(), (SELECT currval('ad_his_id_his_seq')), '$now', makeTraductionLangSyst('Reprise des clients'), 1, $exo_courant, makeNumEcriture(1, $exo_courant));";
print OUTPUT $sql."\n";

# Initialisation de la séquence des id_client
$sql = "SELECT setval('ad_cli_id_client_seq', (SELECT MAX(id_client) FROM ad_cli));";
print OUTPUT $sql."\n";

# Initialisation des variables
$i = 1;            # Numéro de ligne
$total_ps = 0;     # Nombre total de PS souscrites
$error = 0;        # Nombre d'erreurs trouvées
$errormsg = '';    # Message d'ereur à afficher
$CumulPS = 0;      # Total des PS
$solde_part_soc_restant = 0; #solde restant des parts sociales à payer
$infos[33] = 0;  #le capital social est par défaut égal à 0
%AncIdClis = ();   # Liste des anciens ID clients

while (<INPUT>)  # Pour chaque ligne du fichier INPUT (chaque client)
{
# Suppression des "" intempestifs
  $_ =~ s/\"//g;

# Récupération de la ligne dans un tableau, le séparateur est ';'
  @infos = split(/;/, $_);

### Etat client
# -- Construction de la regexp
  %tmp = %{$r->{ "etat_client" }};
  $regExp = makeRegExp(\%tmp);

# -- Validation du string
  if ($infos[0] !~ /$regExp/)
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : L'état du client n'est pas reconnu : ".$infos[0]."\n");
  }

# -- Remplacement de la valeur par une valeur postgres
  $etat_client = $r->{"etat_client"}{$infos[0]};

  if( $etat_client != 2 ) # client n'est pas Actif
  {
# On ne fait pas de reprise de compte ni de parts sociales
    if(($infos[28] != 0) && ($infos[28] != '') )
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Le solde ne doit pas être renseigné pour les clients qui ne sont pas actifs\n");
    }

# On reprend pas de parts sociales pour les clients qui ne sont pas actifs
    if(($infos[8] != 0) && ($infos[8] != '') )
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : On reprend des parts sociales que pour les clients actifs\n");
    }
  }

### Ancien ID client
  $infos[1] =~ s/\'//g;
  if ($use_anc_num == 1)
  {
    if (!(exists $infos[1]) || ($infos[1] eq ''))
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de client n'est pas renseigné\n");
    }
    elsif ($infos[1] !~ m/^[0-9]+$/ )
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de client n'est pas numérique\n");
    }
    $anc_id_client = $infos[1];
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
  }

### Numero matricule
  $infos[2] =~ s/\'//g;
   if (!(exists $infos[2]) || ($infos[2] eq ''))
    {
        $matricule = 'NULL';
    }
    else
    {        
      if (($infos[2] !~ /[0-9]+[a-zA-Z]+/))
       {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : ce matricule ".$infos[2]." n est pas au bon format\n");
       }

      $matricule = $infos[2];
      
      if (defined($MatClis{$matricule}))
       {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : ce matricule ".$matricule." existe déjà\n");
       }
       $MatClis{$matricule} = 1; 
     }
    
### Statut juridique
# -- Construction de la regexp
  %tmp = %{$r->{ "stat_jur" }};
  $regExp = makeRegExp(\%tmp);

# -- Validation du string
  if ($infos[3] !~ /$regExp/)
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : Le statut juridique du client n'est pas reconnu : ".$infos[3]."\n");
  }

# -- Remplacement de la valeur par une valeur postgres
  $stat_jur = $r->{"stat_jur"}{$infos[3]};

### Catégorie de PM (uniquement pour les PM)
### Peut etre vide
  if ($stat_jur == 2) # PM
  {
    if (!(exists $infos[3]) || ($infos[3] eq ''))
    {
      $pm_categorie = 'NULL';  # Valeur par défaut
    }
    else
    {
# -- Construction de la regexp
      %tmp = %{$r->{ "nat_jur" }};
      $regExp = makeRegExp(\%tmp);

# -- Validation du string
      if ($infos[4] !~ /$regExp/)
      {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : La catégorie du client PM n'est pas reconnu : ".$infos[4]."\n");
      }

# -- Remplacement de la valeur par une valeur postgres
      $pm_categorie = $r->{"nat_jur"}{$infos[4]};
    }
  }

### Qualité
# -- Construction de la regexp
  %tmp = %{$r->{ "qualite" }};
  $regExp = makeRegExp(\%tmp);

# -- Validation du string
  if ($infos[5] !~ /$regExp/)
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : La qualité du client n'est pas reconnue : ".$infos[5]."\n");
  }

# -- Remplacement de la valeur par une valeur postgres
  $qualite = $r->{"qualite"}{$infos[5]};

### Nom

# Esapce des caractères '
  $infos[6] =~ s/\'/\\\'/g;

  if ($stat_jur == 1) # PP
  {
    $pp_nom = $infos[6];
  }
  elsif ($stat_jur == 2) # PM
  {
    $pm_raison_sociale = $infos[6];
  }
  elsif ($stat_jur == 3 || $stat_jur == 4) # GI et GS
  {
    $gi_nom = $infos[6];
  }

### Prénom

# Espace des caractères '
  $infos[7] =~ s/\'/\\\'/g;

  if ($stat_jur == 1) # PP
  {
    $pp_prenom = $infos[7];
  }

### Sexe uniquement pour les PP
  if ($stat_jur == 1)
  {
# -- Construction de la regexp
    %tmp = %{$r->{ "pp_sexe" }};
    $regExp = makeRegExp(\%tmp);

# -- Validation du string
    if ($infos[8] !~ /$regExp/)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Le sexe du client PP n'est pas reconnue : ".$infos[8]."\n");
    }

# -- Remplacement par une valeur postgreSQL
    $pp_sexe = $r->{"pp_sexe"}{$infos[8]};
  }
### Nombre de parts sociales uniquement pour qualité MO/ME/MD
  if ($qualite != 1)
  {
# -- Validation du string
    if ($infos[9] !~ /[0-9]+/)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Le nombre de parts sociales n'est pas numérique : ".$infos[9]."\n");
    }
    if ($infos[9] == 0)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Nombre de PS = 0 avec qualité MO/MD\n");
    }

# -- Assignation
    $nbre_parts = $infos[9];

# -- Calcul du solde du compte de PS
    $soldePS = $infos[33];
    $solde_ps = ($val_nominale_ps * $nbre_parts) - $infos[33];
    if ($solde_ps > 0)
    {
      $solde_part_soc_restant = $solde_ps;
    }

# -- Mise à jour du nombre total de PS souscrites
    $total_ps += $nbre_parts;

# -- Cumul des soldes PS souscrites
    $CumulPS += $soldePS;
}
  else
  {
    if ($infos[9] > 0)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Nombre de PS > 0 avec qualité MA\n");
    }
    $nbre_parts = 0;
}

### Date de naissance pour les PP
  if ($stat_jur == 1)
  {
    if ($infos[10] eq "")
    {
      $pp_date_naiss = '01/01/1900';          # Valeur par défaut
    }
    else
    {
# -- Validation du string
      if (!(&isDate($infos[10])))
      {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : La date de naissance n'est pas valide : ".$infos[10]."\n");
      }

# -- Assignation
      $pp_date_naiss = $infos[10];
      ($j,$m,$a) = split("/",$pp_date_naiss);
      $date_nais = $a."-".$m."-".$j;
      ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
      $date_jour =  (1900+$year)."-".($mon+1)."-".$mday;
      if ($date_nais > $date_jour){
         $error++;
         $errormsg .= ("ERREUR Ligne $i : La date de naissance est dans le futur : ".$infos[10]."\n");
      }
    }
  }

### Lieu de naissance uniquement PP
  if ($stat_jur == 1)
  {
    if ($infos[11] eq "")
    {
      $pp_lieu_naiss = 'N/A';          # Valeur par défaut
    }
    else
    {
# Esapce des caractères '
      $infos[11] =~ s/\'/\\\'/g;
      $infos[11] =~ s/\;/\\\;/g;

      $pp_lieu_naiss = $infos[11];
    }
  }

### Type de pièce d'identité - dépend du paramétrage de l'IMF
### Peut être vide
  if ($stat_jur == 1)
  {
    if (!(exists $infos[12]) || ($infos[12] eq ''))
    {
      $pp_type_piece_id = 'NULL';          # Valeur par défaut
    }
    else
    {
# -- Construction de la regexp
      %tmp = %{$r->{ "pp_type_piece_id" }};
      $regExp = makeRegExp(\%tmp);

# -- Validation du string
      if ($infos[12] !~ /$regExp/)
      {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : Le type de pièce d'identité n'est pas reconnu : ".$infos[12]."\n");
      }

# -- Remplacement par une valeur postgreSQL
      $pp_type_piece_id = $r->{"pp_type_piece_id"}{$infos[12]};
    }
  }

### Numéro de pièce d'identité
  if ($stat_jur == 1 && $pp_type_piece_id ne "NULL")
  {
    if (!(exists $infos[13]) || ($infos[13] eq ''))
    {
      $pp_num_piece_id = '999999';          # Valeur par défaut
    }
    else
    {
      $pp_num_piece_id = $infos[13];
    }
  }
  else
  {
    $pp_num_piece_id = '';
  }

### Lieu de délivrance de la pièce d'identité
  if ($stat_jur == 1)
  {
    $pp_lieu_delivrance_id = $infos[14];
  }
  else
  {
    $pp_lieu_delivrance_id = '';
  }

### Date d'expiration de la pièce d'identité
  if ($stat_jur == 1)
  {
    if (!(exists $infos[15]) || ($infos[15] eq ''))
    {
      $pp_date_exp_id = 'NULL';          # Valeur par défaut
    }
    else
    {
      $pp_date_exp_id = '\''.$infos[15].'\'';
    }
  }
  else
  {
    $pp_date_exp_id = '';
  }

### Adresse
  if (!(exists $infos[16]) || ($infos[16] eq ''))
  {
    $adresse = "N/A";
  }
  else
  {
# Escape des caractères '
    $infos[16] =~ s/\'/\'\'/g;
    $infos[16] =~ s/\;/\\\;/g;
    $adresse = $infos[16];
  }

### Code postal
  $code_postal = $infos[17];

### Ville
  $ville = $infos[18];

### Pays : dépend du paramétrage de l'IMF / banque
### Peut être vide
  if (!(exists $infos[19]) || ($infos[19] eq ''))
  {
    $pays = 'NULL';          # Valeur par défaut
  }
  else
  {
# -- Construction de la regexp
    %tmp = %{$r->{ "pays" }};
    $regExp = makeRegExp(\%tmp);

# -- Validation du string
   if ($infos[19] !~ /$regExp/)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Le pays n'est pas reconnu : ".$infos[19]."\n");
    }

# -- Remplacement par uen valeur postgreSQL
    $pays = $r->{"pays"}{$infos[19]};
  }

### Numéro de téléphone
  if ($infos[20] !~ /^[0-9\/\+\.\(\)\-\s]*$/)
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : Le numéro de téléphone du client n'est pas reconnu : ".$infos[19]."\n");
  }
  $num_tel = $infos[20];

### Secteurs d'activité - dépend du paramétrage de l'IMF
### Peut être vide
  if (!(exists $infos[21]) || ($infos[21] eq ''))
  {
    $sect_act = 'NULL';          # Valeur par défaut
  }
  else
  {
# -- Construction de la regexp
    %tmp = %{$r->{ "sect_act" }};
    $regExp = makeRegExp(\%tmp);

# -- Validation du string
   if ($infos[21] !~ /$regExp/)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Le secteur d'activité n'est pas reconnu : ".$infos[21]."\n");
    }

# -- Remplacement par uen valeur postgreSQL
    $sect_act = $r->{"sect_act"}{$infos[21]};
  }

### Activité professionnelle pour les PP et PM uniquement
  if ($stat_jur != 3)
  {
    $infos[22] =~ s/\'/\\\'/g;
    $infos[22] =~ s/\;/\\\;/g;
    $pp_pm_activite_prof = $infos[22];
  }

### Employeur uniquement PP (peut etre vide)
# Espace des caractères '
  $infos[23] =~ s/\'/\\\'/g;

  if ($stat_jur == 1) # PP
  {
    $pp_employeur = $infos[23];
  }

### Fonction uniquement PP (peut etre vide)
# Espace des caractères '
  $infos[24] =~ s/\'/\\\'/g;

  if ($stat_jur == 1) # PP
  {
    $pp_fonction = $infos[24];
  }

### Date d'adhésion
  if (!(&isDate($infos[25])))
  {
    $error++;
    $errormsg .= ("ERREUR Ligne $i : La date d'adhésion n'est pas valide : ".$infos[25]."\n");
  }
  $date_adh = $infos[25];
  ($j,$m,$a) = split("/",$date_adh);
  $date_adhesion = $a."-".$m."-".$j;
  ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
  $date_jour =  (1900+$year)."-".($mon+1)."-".$mday;
  if ($date_adhesion > $date_jour){
        $error++;
        $errormsg .= ("ERREUR Ligne $i : La date d'adhésion est dans le futur : ".$infos[25]."\n");
   }
### Gestionnaire
### A préciser lors du paramétrage
  if (!(defined($infos[26])) || ($infos[26] eq ''))
  {
    $gestionnaire = "NULL";
  }
  else
  {
# -- Construction de la regexp
    %tmp = %{$r->{ "gestionnaires"}};
    $regExp = makeRegExp(\%tmp);

# -- Validation du string
    if ($infos[26] !~ /$regExp/)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Le gestionnaire n'est pas reconnu : ".$infos[26]."\n");
    }

# -- Remplacement par une valeur postgreSQL
    $gestionnaire = $r->{"gestionnaires"}{$infos[26]};
  }

### Ancien compte de base
  $use_cpt_base = $infos[27];
  $anc_cpt_base = $infos[28];
  if ($use_cpt_base == '1' && $use_anc_num == '1' && $anc_cpt_base != '')
  {
# Numérotation des comptes standard
    if ($type_num_cpte == 1)
    {
# Vérification du numéro (syntaxe, check digit et id_client)
      if (! ($anc_cpt_base =~ /(\d{3})-(\d{6})-(\d{2})-(\d{2})/))
      {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de compte ".$anc_cpt_base." n'a pas le bon format\n");
      }
      else
      {
        $anc_cpt_base =~ /(\d{3})-(\d{6})-(\d{2})-(\d{2})/;
        $sans_check_digit = "$1$2$3";
        $num_client_cpte = $2;
        $rang_cpte = $3;
        $check_digit = $4;
        if ($sans_check_digit % 97 != $check_digit)
        {
          $error++;
          $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de compte ".$anc_cpt_base." n'a pas le bon check digit\n");
        }
        elsif ($anc_id_client != $num_client_cpte)
        {
          $error++;
          $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de compte ".$anc_cpt_base." n'a pas le bon numéro de client\n");
        }
        else
        {
          $num_cpte = $rang_cpte;
          $num_comp_cpte = $anc_cpt_base;
        }
      }
    }
# Numérotation des comptes RDC
    elsif ($type_num_cpte == 2)
    {
# Vérification du numéro (syntaxe, check digit et id_client)
      if (! ($anc_cpt_base =~ /(\d{4})-(\d{5})(\d{2})-(\d{2})/))
      {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de compte ".$anc_cpt_base." n'a pas le bon format\n");
      }
      else
      {
        $anc_cpt_base =~ /(\d{4})-(\d{5})(\d{2})-(\d{2})/;
        $sans_check_digit = "$1$2$3";
        $num_client_cpte = $2;
        $rang_cpte = $3;
        $check_digit = $4;
        if ($sans_check_digit % 97 != $check_digit)
        {
          $error++;
          $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de compte ".$anc_cpt_base." n'a pas le bon check digit\n");
        }
        elsif ($anc_id_client != $num_client_cpte)
        {
          $error++;
          $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de compte ".$anc_cpt_base." n'a pas le bon numéro de client\n");
        }
        else
        {
          $num_cpte = $rang_cpte;
          $num_comp_cpte = $anc_cpt_base;
        }
      }
    }
# Numérotation des comptes Rwanda
    elsif ($type_num_cpte == 3)
    {
# Vérification du numéro (syntaxe, check digit et id_client)
      if (! ($anc_cpt_base =~ /(\d{3})-(\d{7})-(\d{2})/))
      {
        $error++;
        $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de compte ".$anc_cpt_base." n'a pas le bon format\n");
      }
      else
      {
        $anc_cpt_base =~ /(\d{3})-(\d{7})-(\d{2})/;
        $num_client_cpte = $2;
        $rang_cpte = $3;
        if ($anc_id_client != $num_client_cpte)
        {
          $error++;
          $errormsg .= ("ERREUR Ligne $i : L'ancien numéro de compte ".$anc_cpt_base." n'a pas le bon numéro de client\n");
        }
        else
        {
          $num_cpte = $rang_cpte;
          $num_comp_cpte = $anc_cpt_base;
        }
      }
    }
  }

### Solde
  if ($infos[29] == '')
  {
    $solde = 0;         # Valeur par défaut
  }
  else
  {
    if ($infos[29] !~ /[0-9]+/)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Le solde du client n'est pas numérique : ".$infos[29]."\n");
    }

# -- Assignation
    $solde = $infos[29];
  }

### Découvert maximum autorisé
  if ($infos[30] == '')
  {
    $decouvert_max = "(SELECT DecouvertMax(1))";         # Valeur par défaut du produit d'épargne
  }
  else
  {
    if ($infos[30] !~ /[0-9]+/)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Le découvert du client n'est pas numérique : ".$infos[30]."\n");
    }

# -- Assignation
    chomp($infos[30]);
    $decouvert_max = $infos[30];

  }

### Responsable GS
  if ($stat_jur == 4)
  {
    $gs_responsable = $infos[31];
    if ($gs_responsable == '')
	  {
  	  $gs_responsable = NULL;
  	}
  }

### Membres GS
  if ($stat_jur == 4)
  {
    if ($infos[31] !~ /.*,.*/)
    {
      $error++;
      $errormsg .= ("ERREUR Ligne $i : Les membres d'un GS doivent être séparés par des virgules : ".$infos[32]."\n");
    }
    else
    {
      $gs_membres = $infos[32];
    }
  }

#---------------------------------------------------------------------------

# Variable contenant la date et l'heure
  $now = gmtime;
  print OUTPUT "\n-- Client $i\n";

# Construction de la requête d'insertion du client
  print OUTPUT "\n-- Insertion du Client $i\n";
  $sql = "INSERT INTO ad_cli(id_ag, id_client, matricule, tmp_already_accessed, langue_correspondance, date_crea, anc_id_client, etat, statut_juridique, qualite, nbre_parts, adresse, code_postal, ville, pays, num_tel, sect_act, gestionnaire, date_adh, ";
  if ($stat_jur == 1)
  {
    $sql .= "pp_nom, pp_prenom, pp_sexe, pp_date_naissance, pp_lieu_naissance, pp_type_piece_id, pp_nm_piece_id, pp_lieu_delivrance_id, pp_date_exp_id, pp_pm_activite_prof, pp_employeur, pp_fonction, pp_casier_judiciaire, ";
  }
  elsif ($stat_jur == 2)
  {
    $sql .= "pm_raison_sociale, pp_pm_activite_prof, pm_categorie, ";
  }
  elsif ($stat_jur == 3)
  {
    $sql .= "gi_nom, ";
  }
  elsif ($stat_jur == 4)
  {
    $sql .= "gi_nom, gs_responsable, ";
  }
  $sql = substr($sql, 0, -2);
  if ($use_anc_num == 1)
  {
    $sql .= ") VALUES (NumAgc(), $anc_id_client,'$matricule', 'f', '$langue_systeme_dft', '$now', '$anc_id_client', $etat_client, $stat_jur, $qualite, $nbre_parts, '$adresse', '$code_postal', '$ville', $pays, '$num_tel', $sect_act, $gestionnaire, '$date_adh', ";
  }
  else
  {
    $sql .= ") VALUES (NumAgc(), nextval('ad_cli_id_client_seq'),'$matricule', 'f', '$langue_systeme_dft', '$now', '$anc_id_client', $etat_client, $stat_jur, $qualite, $nbre_parts, '$adresse', '$code_postal', '$ville', $pays, '$num_tel', $sect_act, $gestionnaire, '$date_adh', ";
  }
  if ($stat_jur == 1)
  {
    $sql .= "'$pp_nom', '$pp_prenom', $pp_sexe, '$pp_date_naiss', '$pp_lieu_naiss', $pp_type_piece_id, '$pp_num_piece_id', '$pp_lieu_delivrance_id', $pp_date_exp_id, '$pp_pm_activite_prof', '$pp_employeur', '$pp_fonction', 'f', ";
  }
  elsif ($stat_jur == 2)
  {
    $sql .= "'$pm_raison_sociale', '$pp_pm_activite_prof', $pm_categorie, ";
  }
  elsif ($stat_jur == 3)
  {
    $sql .= "'$gi_nom', ";
  }
  elsif ($stat_jur == 4)
  {
    $sql .= "'$gi_nom', '$gs_responsable', ";
  }
  $sql = substr($sql, 0, -2);
  $sql .= ");";
  print OUTPUT $sql."\n";

# Récupération des images : déplacer les photos dans le répertoire qui convient et renommer avec le nouveau num client
	print("\n\nRécupération des images du client $anc_id_client\n");
	if ($use_anc_num == 1) {
		$photo_anc_path = "images/photo.".$anc_id_client.".jpg";
		$photo_new_path = "/var/lib/adbanking/backup/images_clients/clients/photos/".substr($anc_id_client,0,1)."/".$anc_id_client;
		if ( -e $photo_anc_path ) {
			print("\n\nRécupération de la photo\n");
	 		print("Déplacement photo $photo_anc_path vers $photo_new_path\n");
	 		`mv -f $photo_anc_path $photo_new_path`
	  }

		$signature_anc_path = "images/signature.".$anc_id_client.".jpg";
		$signature_new_path = "/var/lib/adbanking/backup/images_clients/clients/signatures/".substr($anc_id_client,0,1)."/".$anc_id_client;
		if ( -e $signature_anc_path ) {
			print("\n\nRécupération de la signature\n");
	 		print("Déplacement signature $signature_anc_path vers $signature_new_path\n");
	 		`mv -f $signature_anc_path $signature_new_path`
	  }
	}
# TODO : Suite récupération images if ($use_anc_num != 1), cas où on utilise pas l'ancien numéro client

# Construction de la requête d'insertion des membres d'un GS
  if ($stat_jur == 4)
  {
    print OUTPUT "\n-- Insertion des membres du GS $i\n";
    $sql = "";
    chomp($gs_membres);
    for $membre (split(/,/, $gs_membres))
    {
      $sql .= "INSERT INTO ad_grp_sol (id_ag, id_grp_sol, id_membre) VALUES (NumAgc(), currval('ad_cli_id_client_seq'), (SELECT id_client FROM ad_cli WHERE anc_id_client = '$membre'));\n";
    }
    print OUTPUT $sql;
  }

# Construction de la requête d'insertion de la personne extérieure du client
  print OUTPUT "\n-- Insertion de la personne extérieure du client $i\n";
  if ($use_anc_num == '1')
  {
    $sql = "INSERT INTO ad_pers_ext (id_ag, id_client) VALUES (NumAgc(), '$anc_id_client');";
  }
  else
  {
    $sql = "INSERT INTO ad_pers_ext (id_ag, id_client) VALUES (NumAgc(), currval('ad_cli_id_client_seq'));";
  }
  print OUTPUT $sql."\n";

# Si le client est actif et on utilise le compte de base, l'état est 1
  if($etat_client == 2 && $use_cpt_base == '1')
  {
    $etat_cpte = 1;
  }
  else
  {
	  # Sinon, on ouvre le compte de base en le mettant à l'état fermé (3)
    $etat_cpte = 3;
  }
  if($num_cpte=="")
  {
      $num_cpte="NULL";
  }
# Construction de la requête d'insertion du compte de base du client
  print OUTPUT "\n-- Insertion du compte de base du client $i\n";
  if ($use_anc_num == '1' && $use_cpt_base == '1' && $anc_cpt_base != '')
  {
    $sql = "INSERT INTO ad_cpt (id_ag, id_titulaire, intitule_compte, date_ouvert, utilis_crea, etat_cpte, solde, solde_calcul_interets, num_cpte, num_complet_cpte, id_prod, devise, decouvert_max, cpt_vers_int, mnt_min_cpte, tx_interet_cpte,mode_calcul_int_cpte,mode_paiement_cpte ) VALUES (NumAgc(), $anc_id_client, 'Compte de base', '$now', 1, $etat_cpte, $solde, $solde, $num_cpte, '$num_comp_cpte', 1, '$dev_el', $decouvert_max, currval('ad_cpt_id_cpte_seq'),(SELECT MontantMin(1)),(select tx_interet from adsys_produit_epargne where id=1),(select mode_calcul_int from adsys_produit_epargne where id=1),(select mode_paiement from adsys_produit_epargne where id=1));";
  }
  elsif ($use_anc_num == '1')
  {
    $sql = "INSERT INTO ad_cpt (id_ag, id_titulaire, intitule_compte, date_ouvert, utilis_crea, etat_cpte, solde, solde_calcul_interets, num_cpte, num_complet_cpte, id_prod, devise, decouvert_max, cpt_vers_int, mnt_min_cpte, tx_interet_cpte,mode_calcul_int_cpte,mode_paiement_cpte ) VALUES (NumAgc(), $anc_id_client, 'Compte de base', '$now', 1, $etat_cpte, $solde, $solde, 0, (SELECT makeNumCompletCpte($type_num_cpte, $anc_id_client, 0)), 1, '$dev_el', $decouvert_max, currval('ad_cpt_id_cpte_seq'),(SELECT MontantMin(1)),(select tx_interet from adsys_produit_epargne where id=1),(select mode_calcul_int from adsys_produit_epargne where id=1),(select mode_paiement from adsys_produit_epargne where id=1));";
  }
  else
  {
    $sql = "INSERT INTO ad_cpt (id_ag, id_titulaire, intitule_compte, date_ouvert, utilis_crea, etat_cpte, solde, solde_calcul_interets, num_cpte, num_complet_cpte, id_prod, devise, decouvert_max, cpt_vers_int, mnt_min_cpte, tx_interet_cpte,mode_calcul_int_cpte,mode_paiement_cpte ) VALUES (NumAgc(), (SELECT currval('ad_cli_id_client_seq')), 'Compte de base', '$now', 1, $etat_cpte, $solde, $solde, 0, (SELECT makeNumCompletCpte($type_num_cpte, currval('ad_cli_id_client_seq'), 0)), 1, '$dev_el', $decouvert_max, currval('ad_cpt_id_cpte_seq'),(SELECT MontantMin(1)),(select tx_interet from adsys_produit_epargne where id=1),(select mode_calcul_int from adsys_produit_epargne where id=1),(select mode_paiement from adsys_produit_epargne where id=1));";
  }
  print OUTPUT $sql."\n";

# Construction de la requête d'insertion du mandat pour le compte de base du client
  if ($stat_jur == 1)
  {
    print OUTPUT "\n-- Insertion du mandat du compte de base du client $i\n";
    $sql = "INSERT INTO ad_mandat (id_ag, id_cpte, id_pers_ext, type_pouv_sign, valide) VALUES (NumAgc(), (SELECT currval('ad_cpt_id_cpte_seq')), (SELECT currval('ad_pers_ext_id_pers_ext_seq')), 1, true);";
    print OUTPUT $sql."\n";
  }

# Passage des mouvements comptables pour la création du compte de base
  print OUTPUT "\n-- Passation des mouvements comptables pour la création du compte de base\n";

# Compte de substitution de l'épargne libre
  if (grep(/^$cpt_el$/, keys %comptes_substitut))
  {
    $cpt_substitut_el = $comptes_substitut{$cpt_el};
  }
  else
  {
    $cpt_substitut_el = $cpt_el;
  }

# Mouvement DAV
  if($solde > 0)
  {
    $sql = "INSERT INTO ad_mouvement (id_ag, id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur) VALUES (NumAgc(), (SELECT currval('ad_ecriture_seq')), '$cpt_substitut_el', NULL, 'd', $solde, '$dev_el', '$now');";
    print OUTPUT $sql."\n";
    $sql = "INSERT INTO ad_mouvement (id_ag, id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur) VALUES (NumAgc(), (SELECT currval('ad_ecriture_seq')), '$cpt_el', currval('ad_cpt_id_cpte_seq'), 'c', $solde, '$dev_el', '$now');";
    print OUTPUT $sql."\n";

# Requête de mise à jour du compte de substitution des DAV
    print OUTPUT "\n-- MAJ du compte de substitution des DAV\n";
    $sql = "UPDATE ad_cpt_comptable SET solde = solde - $solde WHERE id_ag = NumAgc() and num_cpte_comptable = '$cpt_substitut_el';";
    print OUTPUT $sql."\n";

# Requête de mise à jour du compte comptable associé au DAV
    $sql = "UPDATE ad_cpt_comptable SET solde = solde + $solde WHERE id_ag = NumAgc() and num_cpte_comptable = '$cpt_el';";
    print OUTPUT $sql."\n";
  }
  elsif($solde < 0)
  {
    $abs_solde = (-1) * $solde;
    $sql = "INSERT INTO ad_mouvement (id_ag, id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur) VALUES (NumAgc(), (SELECT currval('ad_ecriture_seq')),'$cpt_el', currval('ad_cpt_id_cpte_seq'), 'd', $abs_solde, '$dev_el', '$now');";
    print OUTPUT $sql."\n";
    $sql = "INSERT INTO ad_mouvement (id_ag, id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur) VALUES (NumAgc(), (SELECT currval('ad_ecriture_seq')), '$cpt_substitut_el', NULL, 'c', $abs_solde, '$dev_el', '$now');";
    print OUTPUT $sql."\n";

# Requête de mise à jour du compte de substitution des DAV
    print OUTPUT "\n-- MAJ du compte de substitution des DAV\n";
    $sql = "UPDATE ad_cpt_comptable SET solde = solde - $abs_solde WHERE id_ag = NumAgc() and num_cpte_comptable = '$cpt_el';";
    print OUTPUT $sql."\n";

# Requête de mise à jour du compte comptable associé au DAV
    $sql = "UPDATE ad_cpt_comptable SET solde = solde + $abs_solde WHERE id_ag = NumAgc() and num_cpte_comptable = '$cpt_substitut_el';";
    print OUTPUT $sql."\n";
  }

# Mise à jour du lien dans le compte de base du client
  print OUTPUT "\n-- Lien client $i <-> compte de base\n";
  if ($use_anc_num == '1')
  {
    $sql = "UPDATE ad_cli SET id_cpte_base = currval('ad_cpt_id_cpte_seq') WHERE id_ag = NumAgc() and id_client = '$anc_id_client';";
  }
  else
  {
    $sql = "UPDATE ad_cli SET id_cpte_base = currval('ad_cpt_id_cpte_seq') WHERE id_ag = NumAgc() and id_client = currval('ad_cli_id_client_seq');";
  }
  print OUTPUT $sql."\n";

# Construction de la requête d'insertion du compte de PS du client (si nécessaire)
  if ($nbre_parts > 0)
  {
    print OUTPUT "\n-- Insertion compte de parts sociales du client $i\n";
    if ($use_anc_num == 1)
    {
      $sql = "INSERT INTO ad_cpt (id_ag, id_titulaire, date_ouvert, utilis_crea, etat_cpte, solde, solde_calcul_interets, num_cpte, num_complet_cpte, id_prod,devise,mnt_min_cpte) VALUES (NumAgc(), $anc_id_client, '$now', 1, 1, '$soldePS', '$soldePS', 1, (SELECT makeNumCompletCpte($type_num_cpte, $anc_id_client, MaxIdCpte($anc_id_client))), 2, '$dev_ps',(SELECT MontantMin(2)));";
    }
    else
    {
      $sql = "INSERT INTO ad_cpt (id_ag, id_titulaire, date_ouvert, utilis_crea, etat_cpte, solde, solde_calcul_interets, num_cpte, num_complet_cpte, id_prod,devise, mnt_min_cpte) VALUES (NumAgc(), (SELECT currval('ad_cli_id_client_seq')), '$now', 1, 1, '$soldePS', '$soldePS', 1,(SELECT makeNumCompletCpte($type_num_cpte, currval('ad_cli_id_client_seq'), MaxIdCpte(currval('ad_cli_id_client_seq')))), 2, '$dev_ps',(SELECT MontantMin(2)));";
    }
    print OUTPUT $sql."\n";

# Passage des mouvements comptables pour la création du compte de parts sociales
    print OUTPUT "\n-- Passation des mouvements comptables pour la création du compte de parts sociales\n";

# Compte de substitut des parts sociales
    if (grep(/^$cpt_ps$/, keys %comptes_substitut))
    {
      $cpt_substitut_ps = $comptes_substitut{$cpt_ps};
    }
    else
    {
      $cpt_substitut_ps = $cpt_ps;
    }

    $sql = "INSERT INTO ad_mouvement (id_ag, id_ecriture,compte,cpte_interne_cli,sens,montant,devise,date_valeur) VALUES (NumAgc(), (SELECT currval('ad_ecriture_seq')),'$cpt_substitut_ps',NULL,'d','$soldePS','$dev_ps','$now');";
    print OUTPUT $sql."\n";

    $sql = "INSERT INTO ad_mouvement (id_ag, id_ecriture,compte,cpte_interne_cli,sens,montant,devise,date_valeur) VALUES (NumAgc(), (SELECT currval('ad_ecriture_seq')),'$cpt_ps',currval('ad_cpt_id_cpte_seq'),'c','$soldePS','$dev_ps','$now');";
    print OUTPUT $sql."\n";

# Requête de mise à jour du compte de Capital social
    print OUTPUT "\n-- MAJ-PS\n";
    $sql = "UPDATE ad_cpt_comptable SET solde = solde + '$soldePS' WHERE id_ag = NumAgc() and num_cpte_comptable = '$cpt_ps';";
    print OUTPUT $sql."\n";

# Requête de mise à jour du compte de substitut des parts sociales
    $sql = "UPDATE ad_cpt_comptable SET solde = solde - '$soldePS' WHERE id_ag = NumAgc() and num_cpte_comptable = '$cpt_substitut_ps';";
    print OUTPUT $sql."\n";
  }

# Création de la requête SQL de mise à jour du montant restant des parts sociales souscrites
print OUTPUT "\n-- Mise à jour du montant restant des parts sociales des clients\n";
if ($use_anc_num == 1) {
    $sql = "UPDATE ad_cpt SET solde_part_soc_restant = '$solde_part_soc_restant' where id_ag = NumAgc() and id_titulaire = $anc_id_client ;\n";
} else {
    $sql = "UPDATE ad_cpt SET solde_part_soc_restant = '$solde_part_soc_restant' where id_ag = NumAgc() and id_titulaire = currval('ad_cli_id_client_seq') ;\n";
}
print OUTPUT $sql;

  $i++;

} # Fin du traitement du client, passage à la ligne suivante

#--
#-- Requête SQL de mise à jour de la séquence sur id_client en réinitialisant LAST_VALUE
#--
print OUTPUT "\n-- Mise à jour de de la dernière valeur de la séquence sur ID des clients\n";
$sql = "SELECT setval('ad_cli_id_client_seq',(SELECT MAX(id_client) FROM ad_cli));\n";
print OUTPUT $sql;

#-------------------------------------------------------------------------------

# Création de la requête SQL de mise à jour du nombre total de parts souscrites
print OUTPUT "\n-- Mise à jour du nombre de PS des clients\n";
$sql = "UPDATE ad_agc SET nbre_part_sociale = nbre_part_sociale + $total_ps WHERE id_ag = NumAgc() ;\n";
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
    print("\n $i clients ont été traités\n\n");
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
