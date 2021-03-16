#!/usr/bin/perl
# v1.00, olivier.luyckx@aquadev.org
# 16/12/2004
#
# Utilisation: cat fichier | parseMT103
# 		STDOUT = requêtes SQL
########################################

# Regular expression tutorial: http://gnosis.cx/publish/programming/regular_expressions.html
# Pour des infos plus "exhaustives": info perlre

# Known buugs
#   Si le tag 26T est répété plus d'une fois. Seul le dernier est enregistré

# Etape 0: initialisation des variables
########################################
$nom_table_mt103 = "swift_op_etrangers";

# Etape 1: déclaration des structures MT103
############################################

# Types de base
#--------------
$num		= "(?:[0-9]| )";			# Un caractère numérique		(exemple :'9')
$anum_ext	= "(?: |[a-z]|[A-Z]|\\.|:|-|,|$num)";	# Un caractère alphanumérique "étendu"	(exemple :':')
$anum		= "(?: |[A-Z]|$num)";			# Un caractère alphanumérique 		(exemple :'B')
$float		= "(?:$num+(?:\\.$num+)?)";		# Un nombre à virgule:			(exemple :'12', '12.345')

# messages MT103 (dom)
#---------------------

# Header MT103
$code_swift_e	= "($anum\{11})";		# 1.Code SWIFT de la banque émettrice (alphanum longueur fixe:11)
$num_session	= "($num\{4})";			# 2.Numéro de session (numérique longueur fixe : 4)
$num_sequence	= "($num\{6})";			# 3.Numéro de séquence (numérique longueur fixe : 6)

$code_swift_r	= "($anum\{11})";		# 4.Code SWIFT de la banque réceptrice (alphanum longueur fixe:11)

$ref3		= "(?:($anum_ext\{12})?)";		# 5.Référence technique au payement

$balise1	= " *{1: ?F ?01 ?$code_swift_e ?$num_session ?$num_sequence ?}";		# Balise 1 du header MT103
$balise2	= "{2: ?I ?103 ?$code_swift_r ?N ?3 ?020 ?}"; 				# Balise 2 du header MT103
$balise3	= "{3: ?{113:XXXX} ?{118:$ref3} ?}";					# Balise 3 du header MT103

$headerMT103	= "$balise1 ?$balise2 ?$balise3";

# Body MT103
$ref6		= "(?:($anum_ext\{16})?)";         # 6.Références du client
$currenttime	= "($num\{4})";			   # 7.Date de création (numérique longueur fixe : 4)
$status		= "(RDY|SGN|PRT)";		   # 8.Statut fonctionnel (4 valeurs prédefinies possibles)
$ref33          = "(?:(PHOB|TELB|NOCH|    )?)";    # 9.Code d'instruction
$ref39          = "($num\{3})";                    # 10.Code IBLC
$ref7           = "($anum\{3})";                   # 11,15.Devise du paiement
$ref41          = "($float)";                      # 12. Montant de la justification IBLC
$ref4           = "(MEMDAT|DEBDAT|CREDAT)";        # 13.Type de date
$ref5		= "($num\{8})";			   # 14,17.Date du payement (format CCYYMMDD)
$ref8		= "($float)";			   # 16,18.Montant du payement
$exchange_rate  = "($float)";                      # 19.Taux de change vers l'EUR
$pays		= "($anum\{2})";		# 20.Pays où est tenu le compte donneur d'ordre
$format		= "($anum\{3})";		# 21.Formattage du compte donneur d'ordre
$devisedebiteur	= "($anum\{3})";                # 22.Devise du numéro de compte
$ref11		= "($num\{13})";		# 23.Numéro du compte donneur d'ordre (=débiteur) 13 chiffres
$ref12		= "($anum_ext\{35})";		# 24.Nom du débiteur
$ref13a		= "($anum_ext\{35})";		# 25.Adresse du débiteur
$ref13b		= "($anum_ext\{35})";		# 26.Adresse du débiteur (ligne 2)
$ref15		= "($anum\{6})";		# 27.Code postal de l'adresse du débiteur
$ref16		= "($anum_ext\{24})";		# 28.Ville de l'adresse du débiteur
$ref14          = "($anum\{2})";                # 29.Pays du débiteur
$ref24          = "($anum\{11})";               # 30.Code SWIFT de la banque intermédiaire
$ref17          = "($anum\{11})";               # 31.Code SWIFT de la banque destinataire
$ref18          = "($anum_ext\{25})";           # 24.Nom de la banque destinataire
$ref19a		= "($anum_ext\{35})";		# 25.Adresse de la banque destin.
$ref19b		= "($anum_ext\{35})";		# 26.Adresse de la banque destin.  (ligne 2)
$ref20		= "($anum\{6})";		# 27.Code postal de la banque destin.
$ref21		= "($anum_ext\{20})";		# 28.Ville de la banque destin.
$ref22          = "($anum\{2})";                # 29.Pays du débiteur

$ref23		= "($anum\{2})";		# 30.Pays où est tenu le compte du bénéficiaire
$ref25	        = "(FREE|STRU|IBAN)";	        # 31.Format du compte bénéficiaire
$ref26		= "($anum\{36})";	        # 32.Numéro de compte du bénéficiaire (36)

$ref27		= "(?:($anum_ext\{35})?)";	# 33.Nom du bénéficiaire
$ref28a		= "(?:($anum_ext\{35})?)";	# 34.Adresse du bénéficiaire
$ref28b		= "(?:($anum_ext\{35})?)";	# 35.Adresse du bénéficiaire (ligne 2)
$ref29		= "(?:($anum\{6})?)";		# 36.Code postal de l'adresse du bénéficiaire
$ref30		= "(?:($anum_ext\{24})?)";	# 37.Ville de l'adresse du bénéficiaire
$ref31          = "($anum\{2})";                # 38.Pays du bénéficiaire
$ref35          = "(CHC|CDC|CHD|CDD|CHA|CDA|ZIC|MAN)"; #39.Mode de paiement
$ref36          = "(BEN|OUR|SHA|NOR)";          # 39.Code charges
$devisefrais	= "($anum\{3})";                # 40.Devise du numéro de compte des frais
$ref37		= "($num\{13})";	        # 41.Numéro de compte des frais (36)
$ref34          = "(?:($anum_ext\{70})?)";      # 43. Comm à la banque du DO

$ref32		= "(?:($anum_ext\{140})?)";	# 44.Communication do => ben

$balise20	= ":20:$ref6";
$balise13C	= ":13C:/SNDTIME/$currenttime\\+0100";
$balise23B	= ":23B:CRED";
$balise23C	= ":23C:$status";
$balise23E      = ":23E:$ref33";
$balise26T      = ":26T:$ref39$ref7$ref41";
$balise32A	= ":32A:$ref4$ref5 ?$ref7 ?$ref8";
$balise33B      = ":33B:$ref7$ref8";
$balise36       = ":36:$exchange_rate";
$balise50K	= ":50K:/$pays/$format/$devisedebiteur$ref11$ref12$ref13a$ref13b$ref15$ref16$ref14";
$balise51       = ":51:$ref24";
$balise57A      = ":57A:$ref17";
$balise57C      = ":57C:$ref20$ref21$ref22";
$balise57D      = ":57D:$ref18$ref19a$ref19b";
$infosbanquedest= "(?:$balise57A|$balise57C$balise57D)";
$balise59A      = ":59A:/$ref23/$ref25/$ref26$ref17";
$balise59	= ":59:/$ref23/$ref25/$ref26$ref27$ref28a$ref28b$ref29$ref30$ref31";
$infosclientdest= "(?:$balise59|$balise59A)";
$balise71A      = ":71A:$ref35$ref36/$pays/$format/$devisefrais$ref37";
$balise72       = ":72:/$ref33/$ref34";
$balise72B	= ":72B:/$ref32";

# $bodyMT103	= "{4: ?$balise20 ?$balise13C$balise23B$balise23C$balise26T$balise32A$balise50K {0,3}$balise59$balise72B}";
$bodyMT103	= "{4: ?$balise20 ?$balise13C$balise23B$balise23C$balise23E(?:$balise26T)*$balise32A$balise33B$balise36$balise50K {0,3}(?:$balise51)?$infosbanquedest$infosclientdest$balise71A$balise72$balise72B}";
# Tailer MT103
$code_auth_part		= "($anum_ext\{9})";   # 45.Code d'authentification partic.

$trailerMT103	= "{5:{MAC:TMB0489863}{PAC:$code_auth_part}}";

$MT103		= "$headerMT103 ?$bodyMT103 ?$trailerMT103";

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
        # REM : Pas vraiment utile car souvent le message n'est pas validé par la regexp s'il manque quelque chose.
	$result = $_[0];
	@IDChampsO = (15,16,23,24,25,27,28,29,38,39,40,41,42,43,44,45,46);
	for($i=1;$i<=60;$i++)
	{	# Suppression des espaces en début et en fin de chaîne
		$result[$i] =~ s/^ *//;
		$result[$i] =~ s/ *$//;
	}
	$taille = scalar(@IDChampsO);
	for ($i=1;$i<=$taille;$i++)
	  {
	    if ($result[$IDChampsO[$i]] == '')
	      {
		return FALSE,
	      }
	  }
	return TRUE;
};

sub outputMT103
{
	$result = $_[1];
	print "INSERT INTO $nom_table_mt103 VALUES (DEFAULT, '', '".$_[0]."', 0, '', ";
	for($i=1;$i<=60;$i++)
	{
		print "'$result[$i]'";
		if ($i < 60)
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
		for($i=2;$i<=61;$i++)
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
