#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:

############################################################
# Variables utiles
############################################################
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

# Pour le moment, la langue système par défaut est toujours fr_BE
# Il faudrait que les traductions dans les autres langues soient fiables
# et disponibles de manière complète pour que l'on puisse proposer une autre langue
LANG_SYST_DFT="fr_BE"

# Choix des langues dans lesquelles ADbanking sera installé
#----------------------------------------------------------
LANGUES_POSSIBLES=""
echo `/bin/ls $ADB_INSTALL_DIR/web/locale`
for langue in `/bin/ls $ADB_INSTALL_DIR/web/locale`
do
  #pour chaque sous-répertoire de $ADB_INSTALL_DIR/web/locale
  if [[ -d $ADB_INSTALL_DIR/web/locale/$langue && $langue != $LANG_SYST_DFT ]];
  then
    get_nom_langue $langue
    LANGUES_POSSIBLES="$LANGUES_POSSIBLES $langue $nom_langue on"
  fi
done

LANG_INSTALLEES=`dialog --stdout --clear --backtitle "Ajout de nouvelles langues à la base de données ADbanking" \
    --title "Choix des langues" \
    --separate-output \
    --checklist "Veuillez cocher les codes langues des langues que vous voulez ajouter à ADbanking" 0 0 0 $LANGUES_POSSIBLES`
if [ ! "$LANG_INSTALLEES" ]; then
  terminer
fi

echo -e "\033[1m==========\033[0m Ajout des nouvelles langues"
for langue in $LANG_INSTALLEES
do
  get_nom_langue $langue
  SQL_TEST_LANGUE="SELECT COUNT(code) FROM adsys_langues_systeme WHERE code = '$langue';"
  LANGUE_PRESENTE=`return_psql "$SQL_TEST_LANGUE"`
  if [[ $LANGUE_PRESENTE == 0 ]]; then
    SQL_LANGUE="INSERT INTO ad_str DEFAULT VALUES;
                INSERT INTO adsys_langues_systeme(code, langue) SELECT '$langue', max(ad_str.id_str) FROM ad_str;
                INSERT INTO ad_traductions (id_str, langue, traduction) SELECT max(ad_str.id_str), '$LANG_SYST_DFT', '$nom_langue' FROM ad_str;"
    execute_psql 'cmd' "$SQL_LANGUE"
    execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/nom_langue.sql
    execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/tableliste.sql
    execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/menus.sql
  else
    echo -e "La langue \033[1m$langue\033[0m est déjà installée."
  fi
done
