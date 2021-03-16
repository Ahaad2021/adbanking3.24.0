#!/usr/bin/perl
# v1.00, olivier.luyckx@aquadev.org
# 16/12/2004
#
# Utilisation: cat fichier | parseMT103
# 		STDOUT = requêtes SQL
########################################

# Regular expression tutorial: http://gnosis.cx/publish/programming/regular_expressions.html
# Pour des infos plus "exhaustives": info perlre

# Etape 0: initialisation des variables
########################################
$nom_table_mt103 = "swift_op_domestiques";

# Etape 1: déclaration des structures MT103
############################################

# Types de base
#--------------
$num		= "(?:[0-9]| )";			# Un caractère numérique		(exemple :'9')
$anum_ext	= "(?: |[a-z]|[A-Z]|\\.|:|,|$num)";	# Un caractère alphanumérique "étendu"	(exemple :':')
$anum		= "(?: |[A-Z]|$num)";			# Un caractère alphanumérique 		(exemple :'B')
$char_num_cpte  = "(?:[0-9]|-| )";                        # Un caractère pouvant apparaitre dans un numéro de compte (O-9 ou -)
$float		= "(?:$num+(?:\\.$num+)?)";		# Un nombre à virgule:			(exemple :'12', '12.345')

# messages MT103 (dom)
#---------------------

# Header MT103
$code_swift_e	= "($anum\{11})";		# 1.Code SWIFT de la banque émettrice (alphanum longueur fixe:11)
$num_session	= "($num\{4})";			# 2.Numéro de session (numérique longueur fixe : 4)
$num_sequence	= "($num\{6})";			# 3.Numéro de séquence (numérique longueur fixe : 6)

$code_swift_r	= "($anum\{11})";		# 4.Code SWIFT de la banque réceptrice (alphanum longueur fixe:11)

$ref9		= "(?:($anum_ext\{12})?)";		# 5.Référence technique au payement

$balise1	= " *{1: ?F ?01 ?$code_swift_e ?$num_session ?$num_sequence ?}";		# Balise 1 du header MT103
$balise2	= "{2: ?I ?103 ?$code_swift_r ?N ?3 ?020 ?}"; 				# Balise 2 du header MT103
$balise3	= "{3: ?{113:XXXX} ?{118:$ref9} ?}";					# Balise 3 du header MT103

$headerMT103	= "$balise1 ?$balise2 ?$balise3";

# Body MT103
$ref10		= "(?:($anum\{8})?)";
$currenttime	= "($num\{4})";			   # 7.Date de création (numérique longueur fixe : 4)
$status		= "(RDY|SGN|PRT|SNT)";		   # 8.Statut fonctionnel (4 valeurs prédefinies possibles)
$ref1		= "(?:(UND|PEN|SAL|ALL|REM|PUB|".
 		"SSO|PRO|HOL|ZIC|EXP|TRE|SS2)?)";  # 9.Objet du payement (13 veleurs prédefinies possibles)
$ref3		= "($num\{8})";			   # 10.Date du payement (format CCYYMMDD)
$ref17		= "($anum\{3})";		   # 11.Devise du montant du payement (alphanumérique, longueur fixe:3)
$ref16		= "($float)";			# 12.Montant du payement

$pays		= "($anum\{2})";		# 13.Pays où est tenu le compte donneur d'ordre
$format		= "($anum\{3})";		# 14.Formattage du compte donneur d'ordre
$devisedebiteur	= "($anum\{3})";                # 15.Devise du numéro de compte
$ref4		= "($num\{13})";		# 16.Numéro du compte donneur d'ordre (=débiteur) 13 chiffres
$ref5		= "($anum_ext\{35})";		# 17.Nom du débiteur
$ref6		= "($anum_ext\{35})";		# 18.Adresse du débiteur
$ref6b		= "($anum_ext\{35})";		# 19.Adresse du débiteur (ligne 2)
$ref7		= "($num\{6})";			# 20.Code postal de l'adresse du débiteur
$ref8		= "($anum_ext\{24})";		# 21.Ville de l'adresse du débiteur

$pays2		= "($anum\{2})";		# 22.Pays où est tenu le compte du bénéficiaire
$format2	= "(FREE|STRU|IBAN)";		# 23.Format du compte bénéficiaire
$ref11		= "($char_num_cpte\{36})";	# 24.Numéro de compte du bénéficiaire (36)
$ref12		= "(?:($anum_ext\{35})?)";		# 25.Nom du bénéficiaire
$ref13		= "(?:($anum_ext\{35})?)";		# 26.Adresse du bénéficiaire
$ref13b		= "(?:($anum_ext\{35})?)";		# 27.Adresse du bénéficiaire (ligne 2)
$ref14		= "(?:($num\{6})?)";			# 28.Code postal de l'adresse du bénéficiaire
$ref15		= "(?:($anum_ext\{24})?)";		# 29.Ville de l'adresse du bénéficiaire

$ref21		= "(FREE|STRU)";		# 30.Type de communication
$ref18		= "(?:($anum_ext\{53})?)";		# 31.Communication (première ligne)
$ref19		= "(?:($anum\{53})?)";		#   Communication (deuxiemme ligne)

$balise20	= ":20:$ref10";
$balise13C	= ":13C:/SNDTIME/$currenttime\\+0100";
$balise23B	= ":23B:CRED";
$balise23C	= ":23C:$status";
$balise26T	= ":26T:$ref1";
$balise32A	= ":32A:MEMDAT$ref3 ?$ref17 ?$ref16";
$balise50K	= ":50K:/$pays/$format/$devisedebiteur$ref4$ref5$ref6$ref6b$ref7$ref8"."CD";
$balise59	= ":59:/$pays2/$format2/$ref11$ref12$ref13$ref13b$ref14$ref15"."CD";
$balise72B	= ":72B:/$ref21/$ref18$ref19";

$bodyMT103	= "{4: ?$balise20 ?$balise13C$balise23B$balise23C$balise26T$balise32A$balise50K {0,3}$balise59$balise72B}";

# Tailer MT103
$ref9		= "($anum_ext\{9})";		# 32.Référence technique du payement

$tailerMT103	= "{5:{MAC:TMB8950220}{PAC:$ref9}}";

$MT103		= "$headerMT103 ?$bodyMT103 ?$tailerMT103";

# Etape 2: Déclaration des fonctions
#####################################

sub skipMT103
{
	if ($input =~ /$MT103/)
	{	#Message valide
		$input =~ s/$MT103//;
	}
	else
	{	#Message invalide
		print STDERR "Message invalide détecté, ce message invalide a été ignoré !\n";
		$input =~ s/^{1//;

		if ($input =~ /{1:.*?}/)
		{	#Si ce n'est pas le dernier message, on supprime le message invalide
			$input =~ s/.*?({1:.*?})/$1/;
		}
		else
		{
			$input = "";
		}
	};
};

sub verfifChampsObligatoiresMT103
{
	$result = $_[0];
	for($i=1;$i<=32;$i++)
	{	# Suppression des espaces en début et en fin de chaîne
		$result[$i] =~ s/^ *//;
		$result[$i] =~ s/ *$//;
	};
	return TRUE;
};

sub outputMT103
{
	$result = $_[1];
	print "INSERT INTO $nom_table_mt103 VALUES (DEFAULT, '', '".$_[0]."', 0, '', ";
	for($i=1;$i<=33;$i++)
	{
		print "'$result[$i]'";
		if ($i < 33)
		{
			print ",";
		}
	}
	print ");\n";
};

sub parseMT103
{
	if ($input =~ /($MT103)/)
	{	# Message MT103 valide:
		$mess = $1;
		#-Enregistrement des valeurs parsées
		for($i=2;$i<=34;$i++)
		{
			$result[$i-1] = $$i;
		};
		#-Output des valeurs sous forme de requêtes SQL
		if (&verfifChampsObligatoiresMT103($result))
		{
			&outputMT103($mess, $result);
		}
	}
	
	# Passage au message suivant
	&skipMT103;
}

# Etape 3: Lecture de STDIN et traitements
###########################################

#Lecture de stdin
open (SWIFT,'-'); 
@stdin = <SWIFT>;
close(SWIFT);

#Mise en forme de stdin
$input = join ("" , @stdin);
$input =~ s/\r\n//g;            #supression des sauts de ligne (version WINDOWS)
$input =~ s/\n//g;              #supression des sauts de ligne (version UNIX)

while (length $input > 0)
{
	&parseMT103;
};
exit;
