#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:
#
# Ce script compte divers indicateurs sur le code source.
# Les fichiers sont recherchés à partir du répertoire courant.
#
# Les fichiers et les lignes de code d'ADbanking pour les suffixes suivants :
#  - .PHP : fichiers PHP
#  - .SH  : fichiers BASH
#  - .PL  : fichiers PERL
#  - .SQL : fichiers SQL
#
# Les fonctions (PHP) en cherchant les déclarations de celles-ci,
# ou du moins les déclaration au début d'une ligne avec le mot clé 'function'
#
# De plus, si on se trouve à la racine d'un répertoire de code source
# le script compte aussi :
#  - les écrans présents dans menus.sql
#  - les fonctions systèmes (fonctionnalités) dans tableSys.php
#
# On recherche aussi les mots clés FIXME et TODO dans le code.

## Variables
# Types de fichiers
FILE_TYPES="PHP SH PL SQL"
# Nom du script courrant
SCRIPT_NAME=${0/\/*\///}

echo -e "\033[1mIndicateurs sur le code d'ADbanking\033[0m"
echo

for TYPE in $FILE_TYPES
do
  echo -ne "Fichiers \033[1m$TYPE\033[0m\t: "
  find . -type f -iname "*.${TYPE}" | wc -l 2>/dev/null
  echo -ne "\tlignes\t: "
  find . -type f -iname "*.${TYPE}" -exec cat {} \; | wc -l 2>/dev/null
done

# Fonctions : "^function"
echo
echo -ne "\033[1mFonctions PHP\033[0m\t: "
egrep -r "^[:space:]*function[:space:]*\(*" * | grep -v ".svn/" | wc -l 2>/dev/null

# Ecrans
if [[ -e db && -e web ]]; then
  echo
  echo -ne "\033[1mEcrans\033[0m dans menus.sql\t\t\t\t: "
  grep ".php" db/Dump/menus.sql | wc -l 2>/dev/null

  echo -ne "\033[1mFonctionnalités\033[0m dans adsys_fonction_systeme\t: "
  grep '"adsys_fonction_systeme"' web/lib/misc/tableSys.php | egrep -v "^//" | wc -l 2>/dev/null
fi

# FIXME et TODO
# Sauf ceux dans le fichier présent et ceux dans .svn
echo
echo -ne "\033[1mFIXME\033[0m\t: "
grep -r FIXME * | grep -v ".svn/" | grep -v $SCRIPT_NAME | wc -l 2>/dev/null
echo -ne "\033[1mTODO\033[0m\t: "
grep -r TODO * | grep -v ".svn/" | grep -v $SCRIPT_NAME | wc -l 2>/dev/null

