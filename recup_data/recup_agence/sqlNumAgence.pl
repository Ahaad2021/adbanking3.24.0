#!/usr/bin/perl
$inputFile = "recup_num_agence.csv";      # Nom du fichier de données (format CSV)
$outputFile = "DATA.sql";       # Nom du fichier SQL à générer
# Ouverture du fichier CSV
open (INPUT, $inputFile) || die("Le fichier $inputFile n'a pas pu être trouvé !\n");
print("Ouverture du fichier des agences $inputFile\n");

# Ouverture du fichier SQL
open (OUTPUT, ">$outputFile");

<INPUT>;

  print OUTPUT "BEGIN;\n";
while (<INPUT>)  # Pour chaque ligne du fichier INPUT (chaque agence)
{
# Suppression des "" intempestifs
  $_ =~ s/\"//g;

# Récupération de la ligne dans un tableau, le séparateur est ';'
  @donnes = split(/;/, $_);
  $num_agence = $donnes[0];
  $nom_agence = $donnes[1];
  print("\tVérification du format des données\n");

# Insertion de la liste des agences
  print OUTPUT "\n-- Insertion de la liste des agences \n";
  $sql = "INSERT INTO ad_agence_conso(num_agence,nom_agence) VALUES ($num_agence,'$nom_agence');";
  print OUTPUT $sql."\n";
}
 print OUTPUT "\nCOMMIT;\n";

# Fermeture des fichiers
  close(INPUT);
  close(OUTPUT);

