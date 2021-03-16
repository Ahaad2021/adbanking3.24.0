#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:

# Ce fichier extrait les chaines hors des appels à maketraductionlangsyst afin de les traduire
# Il compare ces chaines avec celles déjà présentes dans les fichiers correspondants des répertoires 'locale'
# Il ajoute les chaînes manquantes dans ces fichiers pour qu'elles soient traduites

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
  # La langue originale est fr_BE, on l'évite
  if [[ -d ${LOCALE_DIR}${LANG} && ${LANG} != 'fr_BE' ]] ; then
    echo -e "  Vérification pour \033[1m${LANG}\033[0m"
    # On vérifie si le fichier SQL pour la langue existe déjà
    TRAD_SQL_FILE=${LOCALE_DIR}${LANG}/${LANG}'.sql'
    TRAD_SQL_WITH_CSV_FILE=${LOCALE_DIR}${LANG}/${LANG}'.csv'
    if [[ ! -f ${TRAD_SQL_FILE} ]] ; then
        touch ${TRAD_SQL_FILE}
    fi
    for SQL_FILE in $SQL_FILES
    do
        echo -e "    Traitement de \033[1m${SQL_FILE}\033[0m"
        IN_FILE=${SQL_DIR}${SQL_FILE}
        COMMENT="\n-- Original strings coming from ${IN_FILE}\n"
        # On extrait toutes les chaînes à traduire
        IN_STRINGS=`awk 'BEGIN { FS="make[tT]raduction[lL]ang[sS]yst\\\(" } ; /make[tT]raduction[lL]ang[sS]yst/ {print $2}' $IN_FILE | 
        sed "s/')[;|,].*/'/" | sed "s/));//" | sed "s/^'//" | sed "s/'$//" | sed "s/^'//" | sed "s/'$//" | sort | uniq`
        # On change le délimiteur de mots à <newline> uniquement, pour faire une boucle sur les chaînes présentes dans IN_STRINGS
        IFS=$'\n'
        for IN_STRING in $IN_STRINGS
        do
            if [[ $SQL_FILE == 'PARMEC.sql' ]] ; then
               # Cas particulier pour PARMEC.sql, on le met dans un fichier séparé car il n'est pas automatiquement chargé comme les autres
               TRAD_SQL_FILE=${LOCALE_DIR}${LANG}/${SQL_FILE}
            fi
            # On cherche si la chaîne est présente dans le fichier traduit
            grep "'${IN_STRING/\*/\\*}'" ${TRAD_SQL_FILE} 2>&1 1>/dev/null
            if [[ $? == 1 ]]; then
                # Si elle n'est pas présente, on l'ajoute
                echo "    Ajout de '${IN_STRING}' pour ${LANG}"
                echo -e "${COMMENT}SELECT addTraduction('${IN_STRING}', '${LANG}', '');" >> ${TRAD_SQL_FILE}
                COMMENT=""
            fi
        done
    done
    echo
  fi
done
echo "Traitement terminé"
