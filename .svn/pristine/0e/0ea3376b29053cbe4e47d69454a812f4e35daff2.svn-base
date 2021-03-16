#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:

# Ce fichier extrait les chaines dans les fichiers traduits csv {LANG}'.csv' des répertoires 'locale', , où {LANG} est le code de la langue, ex: 'en_GB'
# Il produit des scripts sql {LANG}'.sql' qui seront ensuite exécutées dans la base

# Variables
SQL_DIR='db/Dump/'
LOCALE_DIR='web/locale/'
TMP_FILE='/tmp/translate.tmp'

cd "`dirname $0`/.."
echo -e "\033[1mAjout des nouvelles chaînes à traduire des fichiers SQL\033[0m"
echo
# Pour chaque langue traduite
for LANG in `/bin/ls $LOCALE_DIR` ; do
  CSV_FILE=${LANG}'.csv'
  # La langue originale est fr_BE, on l'évite
  if [[ -d ${LOCALE_DIR}${LANG} && ${LANG} != 'fr_BE' ]] ; then
    echo -e "  Vérification pour \033[1m${LANG}\033[0m"
    # On vérifie si le fichier SQL pour la langue existe déjà
    TRAD_SQL_FILE=${LOCALE_DIR}${LANG}/'TRAD_'${LANG}'.sql'
    if [[ ! -f ${TRAD_SQL_FILE} ]] ; then
        touch ${TRAD_SQL_FILE}
    fi
        echo -e "    Traitement de \033[1m${SQL_FILE}\033[0m"
        IN_FILE=${LOCALE_DIR}${LANG}/${CSV_FILE}
        #COMMENT="\n-- Original strings coming from ${IN_FILE}\n"
      COMMENT="\n"
        # On extrait toutes les chaînes à traduire
       IN_STRINGS=`awk -F":" '{print $0}' $IN_FILE | 
         sort | uniq`
        # On change le délimiteur de mots à <newline> uniquement, pour faire une boucle sur les chaînes présentes dans IN_STRINGS
        
        IFS=$'\n'
        for IN_STRING in $IN_STRINGS
        do
          # Remplacer les ';' par les ','
          IN_STRING=${IN_STRING//"';"/"',"}
                echo "    Ajout de ${IN_STRING} pour ${LANG}"
                echo -e "${COMMENT}SELECT addTraduction(${IN_STRING});" >> ${TRAD_SQL_FILE}
                COMMENT=""
        done
    echo
  fi
done
echo "Traitement terminé"

