#!/usr/bin/perl
# vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker:

# Name: sqlBilan.pl
# Description: Script de reprise d'un bilan
# Author: Antoine Guyette <antoine.guyette@aquadev.org>


# Déclaration et initialisation des champs

$fichier_reprise = "recup_compta.csv";   # Nom du fichier de reprise

%tab_compte = ();	# Tableau contenant les informations pour un compte donné
# FIXME: à quoi sert cette variable ?
#%tab_solde = ();	# Tableau contenant, pour chaque devise, le solde total des comptes de l'Actif et du Passif


# Vérification et traitement du fichier de reprise

# Ouverture du fichier de reprise
open (REPRISE, $fichier_reprise) || die ("Le fichier de reprise $fichier_reprise n'a pas pu être trouvé !\n");

$compteur = 0;

print ("-- Attention : les apostrophes présentes dans les libellés des comptes ne seront pas prises en compte !");

while ($ligne = <REPRISE>)	# Pour chaque ligne du fichier de reprise
{
  if ($compteur) 	# Si ce n'est pas la première ligne
  {
# Nettoyage de la ligne
    chomp($ligne);		# Suppression du dernier caractère, seulement si c'est un délimitateur de fin de ligne
      $ligne =~ s/['"]//g;	# Suppression des (') et des (")
      $ligne =~ s/;*$//;	# Suppression des (;;) en fin de ligne

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

# Vérification et traitement du compte de provision
    if ($champs[5] and $champs[1] != 1)
    {
      die ("Erreur: Le compte $num_compte à la ligne $compteur n'est pas un compte AC (actif) et ne peut donc avoir de compte de provision\n");
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
  $num_compte_provision = $champs[5];
  if ($num_compte_provision ne "" and ! grep(/^$num_compte_provision$/, @tab_compte))
  {
    die ("Erreur: Le compte provision $num_compte_provision du compte $num_compte n'existe pas\n");
  }

}

# Création des requêtes SQL

# Début de la transaction
print("BEGIN;\n");

# Création des comptes comptables
foreach $num_compte (sort(keys(%tab_compte)))
{
  $libel_compte = $tab_compte{$num_compte}[0];
  $compart_compte = $tab_compte{$num_compte}[1];
  $sens_compte = $tab_compte{$num_compte}[2];
  $devise_compte = $tab_compte{$num_compte}[3];
  # FIXME: à quoi sert cette variable ?
  #$solde_compte = $tab_compte{$num_compte}[4];
  $classe_compte = $tab_compte{$num_compte}[6];
  $num_compte_central = $tab_compte{$num_compte}[7];

  $sql = "INSERT INTO ad_cpt_comptable (id_ag, num_cpte_comptable, libel_cpte_comptable, sens_cpte, classe_compta, compart_cpte, etat_cpte, date_ouvert, cpte_centralise, cpte_princ_jou, solde, devise) VALUES (NumAgc(), '$num_compte', '$libel_compte', $sens_compte, $classe_compte, $compart_compte, 1, now(), ";

  if ($num_compte_central eq "")
  {
    $sql .= "NULL, ";
  }
  else
  {
    $sql .= "'$num_compte_central', ";
  }

  $sql .= "'f', 0, ";

  if ($devise_compte eq "")
  {
    $sql .= "NULL);\n";
  }
  else
  {
    $sql .= "'$devise_compte');\n";
  }

  print($sql);
}

# Update du champ cpte_provision pour les tuples qui ont un tel champ
foreach $num_compte (keys(%tab_compte))
{
  if ($num_compte_provision = $tab_compte{$num_compte}[5])
  {
    print("UPDATE ad_cpt_comptable SET cpte_provision = '$num_compte_provision' WHERE id_ag = NumAgc() and num_cpte_comptable = '$num_compte';\n");
  }
}
# Update du champs niveau dans ad_cpt_comptable
  print("UPDATE ad_cpt_comptable SET niveau = getNiveau(num_cpte_comptable,NumAgc());\n");

# Fin de la transaction
print("COMMIT;\n");
