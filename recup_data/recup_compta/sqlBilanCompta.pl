#!/usr/bin/perl
# vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker:

# Name: sqlBilan.pl
# Description: Script de reprise d'un bilan
# Author: Antoine Guyette <antoine.guyette@aquadev.org>


# Déclaration et initialisation des champs
$fichier_ini = "../../adbanking.ini";   # Nom du fichier d'initialisation d'adbanking
$fichier_conf = "../traduction.conf";   # Nom du fichier de configuration
$fichier_reprise = "recup_compta.csv";   # Nom du fichier de reprise

%tab_compte = ();	# Tableau contenant les informations pour un compte donné
%tab_solde = ();	# Tableau contenant, pour chaque devise, le solde total des comptes Actif + Charge et Passif + Produit

# Récupération du Nom  et de l'utilisateur de la base de données dans le fichier adbanking.ini
# Ouverture du fichier d 'initialisation d'adbanking (adbanking.ini)
	open (INI, $fichier_ini) || die ("Le fichier d'initialiastion d'adbanking $fichier_ini n'a pas pu être trouvé!\n");

	$DB_name='';
	$DB_USER='';

	while (<INI>)  # Pour chaque ligne du fichier
	{
	  $i++;
	  if ($_ =~ /^\;/ )  # Commentaire
	  {
	    next;
	  }
	  elsif ($_ =~ /^\s/) # Ligne vide
	  {
	    next;
	  }
	  elsif ($_ =~ /^\[(\w)+\]$/) # Titre
	  {
	    next;
	  }
	  else
	  {
	    @infos = split(/\=/, $_);
	    $infos[0]=~ s/\s+//g; # supprimer les espaces
	    $infos[1]=~ s/\s+//g; # Supprimer les espaces
	    if ($infos[0] eq "DB_name")
	    {
	      $DB_name=$infos[1];
	    }
	    elsif ($infos[0] eq "DB_user")
	   {
	    $DB_user=$infos[1];
	   }
	  }
	}
# Fermeture du fichier d'initialisation adbanking.ini
	close(INI);
	if ($DB_name eq '' )
	{
	  die ("Le Paramètre DB_name (nom de la base de données) n'a pas été defini dans le fichier d'initialisation  $fichier_ini");
	}
	if ($DB_user eq '' )
	{
	  die ("Le Paramètre DB_user (utilisateur de la base de données)  n'a pas été defini dans le fichier d'initialisation  $fichier_ini");
	}
	# FIN Récupération du Nom  et de l'utilisateur de la base de données dans le fichier adbanking.ini

# Vérification et traitement du fichier d'entrée

# Ouverture du fichier de reprise
open (REPRISE, $fichier_reprise) || die ("Le fichier de reprise $fichier_reprise n'a pas pu être trouvé !\n");

$compteur = 0;

while ($ligne = <REPRISE>)	# Pour chaque ligne du fichier d'entrée
{
  if ($compteur) 	# Si ce n'est pas la première ligne
  {
# Nettoyage de la ligne
    chomp($ligne);		# Suppression du dernier caractère, seulement si c'est un délimitateur de fin de ligne
      $ligne =~ s/['"]//g;	# Suppression des (') et des (")
      $ligne =~ s/;*$//;	# Suppression des (;) en fin de ligne

# Découpage de la ligne en champs
      @champs = split('[;]',$ligne);

# Vérification du nombre de champs
    if ($#champs < 7) # Trop peu de champs
    {
      die ("Erreur: La ligne $compteur n'a pas le bon nombre de champs\n");
    }
    elsif ($#champs eq 7) # Compte principal
    {
# Création du numéro de compte centralisateur
      $num_compte_central = "";
# Création du numéro de compte
      $num_compte = $champs[6].".".$champs[7];
    }
    elsif ($#champs > 7) # Compte non principal
    {
# Création du numéro de compte centralisateur
      $num_compte_central = "";
      for ($i = 6;  $i < $#champs; $i++)
      {
        $num_compte_central .= $champs[$i].".";
      }
      chop($num_compte_central);

# Création du numéro de compte
      $num_compte = $num_compte_central.".".$champs[$i];
    }

# Vérification de l'unicité du numéro de compte
    if (grep(/^$num_compte$/, keys %tab_compte))
    {
      die ("Erreur: Le compte $num_compte à la ligne $compteur existe déjà\n");
    }

# Vérification et traitement du compartiment comptable
    if ($champs[1] eq "AC")
    {
      $champs[1] = 1;
    }
    elsif ($champs[1] eq "PA")
    {
      $champs[1] = 2;
    }
    elsif ($champs[1] eq "CH")
    {
      $champs[1] = 3;
    }
    elsif ($champs[1] eq "PR")
    {
      $champs[1] = 4;
    }
    elsif ($champs[1] eq "AP")
    {
      $champs[1] = 5;
    }
    else
    {
      die ("Erreur: Le compartiment du compte $num_compte à la ligne $compteur doit être AC (actif), PA (passif), CH (charge), PR (produit) ou AP (actif-passif)\n");
    }

# Vérification et traitement du sens du compte
    if ($champs[2] eq "DE")
    {
      $champs[2] = 1;
    }
    elsif ($champs[2] eq "CR")
    {
      $champs[2] = 2;
    }
    elsif ($champs[2] eq "MI")
    {
      $champs[2] = 3;
    }
    else
    {
      die ("Erreur: Le sens du compte $num_compte à la ligne $compteur doit être DE (débiteur), CR (créditeur) ou MI (mixte)\n");
    }

# Vérification de la devise du compte
    if ($champs[3] and  ! $champs[3] =~ /[A-Z]{3}/)
    {
      die ("Erreur: La devise du compte $num_compte à la ligne $compteur est incorrecte ($champs[3])\n");
    }

# Vérification du solde du compte
    if (! $champs[4])
    {
      $champs[4] = 0;
    }
    elsif (! $champs[4] =~ /-?\d+(\.|,)?\d*/)
    {
      die ("Erreur: Le solde du compte $num_compte à la ligne $compteur est incorrect ($champs[4])\n");
    }
    elsif ($champs[4] < 0 and $champs[2] == 2)
    {
      die ("Erreur: Le solde du compte $num_compte à la ligne $compteur ne peut pas être négatif car le sens du compte est CR (créditeur)\n");
    }
    elsif ($champs[4] > 0 and $champs[2] == 1)
    {
      die ("Erreur: Le solde du compte $num_compte à la ligne $compteur ne peut pas être positif car le sens du compte est DE (débiteur)\n");
    }

# Remplacement de la virgule par un point dans le solde du compte
    $champs[4] =~ s/,/\./;

# Vérification équilibre de la balance
    if ($champs[4] < 0 )
    {
# Cumul des débits par devise
      $tab_solde{$champs[3]}{1} += $champs[4];
    }
    elsif ($champs[4] > 0)
    {
# Cumul des crédits par devise
      $tab_solde{$champs[3]}{2} += $champs[4];
    }

# Création des infos du compte
    $tab_compte{$num_compte}[0] = $champs[0];		# Libellé du compte
      $tab_compte{$num_compte}[1] = $champs[1];		# Compartiment du compte
      $tab_compte{$num_compte}[2] = $champs[2];		# Sens du compte
      $tab_compte{$num_compte}[3] = $champs[3];		# Devise du compte
      $tab_compte{$num_compte}[4] = $champs[4];		# Solde du compte
      $tab_compte{$num_compte}[5] = $champs[5];		# Compte de provision
      $tab_compte{$num_compte}[6] = $champs[6];		# Classe du compte
      $tab_compte{$num_compte}[7] = $num_compte_central;	# Compte centralisateur
      $tab_compte{$num_compte}[8] = $num_compte;		# Compte de substitution

  }
  $compteur++;
}

# Fermeture du fichier de reprise
close(REPRISE);


# Vérification de la hiérachie des comptes

foreach $num_compte (keys %tab_compte)
{
# Si le solde n'est pas null alors le compte doit avoir une devise
  if ($tab_compte{$num_compte}[4] != 0 and $tab_compte{$num_compte}[3] eq "")
  {
    die ("Erreur: Le compte $num_compte doit avoir une devise car il a un solde non nul\n");
  }


# si le solde n'est pas null , verifier que le compte existe dans la base de donnée
	  if ($tab_compte{$num_compte}[4] != 0)
	  {

	    @is_compte = `psql -A -d adbanking -U adbanking -c \"SELECT num_cpte_comptable FROM ad_cpt_comptable where  num_cpte_comptable = \'$num_compte\' AND id_ag = NumAgc()\"`;
	    $is_compte=$is_compte[1];
	    if ($is_compte != $num_compte )
	    {
	      die ("Erreur : Le compte  $num_compte n'existe pas, veuillez d'abord le créer  \n");
	    }
	  }

# Si le compte centralisateur est renseigné faire des vérifications
  $num_compte_central = $tab_compte{$num_compte}[7];
  if($num_compte_central ne "")
  {
# Si le compte centralisateur n'existe pas
    if (! grep(/^$num_compte_central$/, keys %tab_compte))
    {
      die ("Erreur: Le compte centralisateur $num_compte_central du compte $num_compte n'existe pas\n");
    }
# Si le solde du compte centralisateur n'est pas nul
    if ($tab_compte{$num_compte_central}[4] != 0)
    {
      die ("Erreur: Le solde du compte centralisateur $num_compte_central n'est pas nul\n");
    }

# Si la devise du compte centralisateur n'est pas égale à celle du compte
    if ($tab_compte{$num_compte_central}[3] ne "" and  $tab_compte{$num_compte_central}[3] ne $tab_compte{$num_compte}[3])
    {
      die ("Erreur: La devise du compartiment du compte centralisateur $num_compte_central n'est pas égale à celle du compte $num_compte\n");
    }
  }

# Si le compte provision est renseigné alors vérifier qu'il existe
  $num_compte_provision = $tab_compte{$num_compte}[5];
  if ($num_compte_provision ne "" and ! grep(/^$num_compte_provision$/, @tab_compte))
  {
    die ("Erreur: Le compte provision $num_compte_provision du compte $num_compte n'existe pas\n");
  }

}

# Vérification equilibre de la balance : voir si pour chaque devise total débit = total crédit
foreach $devise (keys %tab_solde)
{
  if (sprintf("%.4f", $tab_solde{$devise}{2}) != sprintf("%.4f", -$tab_solde{$devise}{1}))
  {
    die ("Le total débit n'est pas égal au total crédit pour la devise $devise\n");
  }
}

# Récupération des comptes de substitution

# Ouverture du fichier de configuration
open (CONF, $fichier_conf) || die ("Le fichier de configuration $fichier_conf n'a pas pu être trouvé!\n");

$etat = 0;
$i = 0;  # Numéro de ligne

%remp = (); # Initialisation du hash : vide au départ

while (<CONF>)  # Pour chaque ligne du fichier
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
      if ($_ !~ /^\[(\w)+\]$/) # La ligne n'est pas au format [xxx]
      {
        die ("Erreur : Titre de section attendu à la ligne $i\n");
      }
      else
      {
# Récupération du titre de section
        $section = s/(\[)(\w+)(\])/$2/g;
        $section = $2;
        $etat = 1; # On est maintenant à l'intérieur d'une section
      }
    }
    elsif ($etat == 1) # On est dans une section, on cherche une correspondance
    {
      if ($section eq "general")  # La section générale
      {
        @infos = split(/\s/, $_); # On tente de récupérer l'exercice en cours
          if ($infos[0] eq "exo_courant")
          {
            $exo_courant = $infos[1];
          }
      }
      else # autre session
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

# Comptes associés aux produits d'épargne et de crédit
          if( ($section eq "sub_ep") or ($section eq "sub_cr") or ($section eq "sub_gar"))
          {
            $num_compte = $infos[0];
            if (grep(/^$num_compte$/, keys %tab_compte))
            {
# Vérification du paramétrage d'un compte de substitution
              if($infos[1] != 0)
              {
                $tab_compte{$num_compte}[8] = $infos[1];
              }
            }
          }
        }
      }
    }
  }
}

# Fermeture du fichier de configuration
close(CONF);


# Création des requêtes SQL

# Début de la transaction
print("BEGIN;\n");

# Création de l'historique
print("INSERT INTO ad_his (id_ag, type_fonction, id_client, login, infos, date, id_his_ext) VALUES (NumAgc(), 504, NULL, 'admin', NULL, now(), NULL);\n");

# Création de l'écriture
print("INSERT INTO ad_ecriture (id_ag, id_his, date_comptable, libel_ecriture, id_jou,id_exo, ref_ecriture) VALUES (NumAgc(), (SELECT currval('ad_his_id_his_seq')), now(), makeTraductionLangSyst('Reprise du bilan'), 1, $exo_courant, makeNumEcriture(1, $exo_courant));\n");

# Insertion des tuples dans la table ad_cpt_comptable
foreach $num_compte (sort(keys(%tab_compte)))
{
	# FIXME: à quoi servent ces variables ?
  #$libel_compte = $tab_compte{$num_compte}[0];
  #$compart_compte = $tab_compte{$num_compte}[1];
  #$sens_compte = $tab_compte{$num_compte}[2];
  #$classe_compte = $tab_compte{$num_compte}[6];
  $devise_compte = $tab_compte{$num_compte}[3];
  $solde_compte = $tab_compte{$num_compte}[4];
  $num_compte_central = $tab_compte{$num_compte}[7];
  $cpte_substitue = $tab_compte{$num_compte}[8];

# Création des mouvements : uniquement pour les comptes ayant un solde
  if($solde_compte > 0)
  {
    print("INSERT INTO ad_mouvement (id_ag, id_ecriture, compte, cpte_interne_cli, sens, montant, date_valeur, devise) VALUES (NumAgc(), (SELECT currval('ad_ecriture_seq')), '$cpte_substitue', NULL, 'c', $solde_compte, now(), '$devise_compte');\n");
  }
  elsif ($solde_compte < 0)
  {
    print("INSERT INTO ad_mouvement (id_ag,id_ecriture, compte, cpte_interne_cli, sens, montant, date_valeur, devise) VALUES (NumAgc(),(SELECT currval('ad_ecriture_seq')), '$cpte_substitue', NULL, 'd', -1*$solde_compte, now(), '$devise_compte');\n");
  }

# Mise à jour des soldes des comptes
  if($solde_compte != 0)
  {
    print("UPDATE ad_cpt_comptable SET solde = solde + $solde_compte WHERE id_ag = NumAgc() and num_cpte_comptable = '$cpte_substitue';\n");
  }

}

# Fin de la transaction
print("COMMIT;\n");
