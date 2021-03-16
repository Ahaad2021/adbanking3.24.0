#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:

# Ce fichier extrait les chaines dans les fichiers {LANG}'.sql' , où {LANG} est le code de la langue, ex: 'en_GB'
# Il enregistre ces chaines dans les fichiers csv {LANG}'.csv' des répertoires 'locale'
# Ensuite ces fichiers seront passés aux traducteurs pour qu'ils traduissent ces chaines

# Variables
SQL_DIR='db/Dump/'
# PARMEC.sql est traité un peu différement, il doit donc resté le dernier de la liste
SQL_FILES='arrete_compte.sql calcul_interets_debiteurs.sql frais_tenue_cpt.sql menus.sql start.sql tableliste.sql PARMEC.sql'
LOCALE_DIR='web/locale/'
TMP_FILE='/tmp/translate.tmp'

cd "`dirname $0`/.."
echo -e "\033[1mAjout des nouvelles chaînes à traduire des fichiers SQL\033[0m"
echo
# Pour chaque langue traduite
for LANG in `/bin/ls $LOCALE_DIR` ; do
  SQL_FILE=${LANG}'.sql'
  # La langue originale est fr_BE, on l'évite
  if [[ -d ${LOCALE_DIR}${LANG} && ${LANG} != 'fr_BE' ]] ; then
    echo -e "  Vérification pour \033[1m${LANG}\033[0m"
    # On vérifie si le fichier CSV pour la langue existe déjà
    TRAD_SQL_WITH_CSV_FILE=${LOCALE_DIR}${LANG}/${LANG}'.csv'
    if [[ ! -f ${TRAD_SQL_WITH_CSV_FILE} ]] ; then
        touch ${TRAD_SQL_WITH_CSV_FILE}
    fi
        echo -e "    Traitement de \033[1m${SQL_FILE}\033[0m"
        IN_FILE=${LOCALE_DIR}${LANG}/${SQL_FILE}
        #COMMENT="\n-- Original strings coming from ${IN_FILE}\n"
      COMMENT="\n"
        # On extrait toutes les chaînes à traduire
       IN_STRINGS=`awk 'BEGIN { FS="add[tT]raduction\\\(" } ; /add[tT]raduction/ {print $2}' $IN_FILE | 
        sed "s/')[;|,].*/'/" | sed "s/));//" | sort | uniq`
        # On change le délimiteur de mots à <newline> uniquement, pour faire une boucle sur les chaînes présentes dans IN_STRINGS
        
        IFS=$'\n'
        for IN_STRING in $IN_STRINGS
        do
          # Remplacer les ',' par les ';'
          IN_STRING=${IN_STRING//"',"/"';"}
                echo "    Ajout de ${IN_STRING} pour ${LANG}"
                echo -e "${COMMENT}${IN_STRING}" >> ${TRAD_SQL_WITH_CSV_FILE}
                COMMENT=""
        done
    echo
  fi
done
echo "Traitement terminé"

