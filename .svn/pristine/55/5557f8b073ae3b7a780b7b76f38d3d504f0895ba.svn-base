#!/bin/bash
# vim: set expandtab softtabstop=2 shiftwidth=2:

############################################################
# Variables utiles
############################################################
let ${DBUSER:=adbanking}
let ${DBNAME:=test}
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
echo -e "Voulez-vous aussi traduire l'exemple de \033[1mplan PARMEC\033[0m ? (O pour Oui et \033[1mN pour Non\033[0m)"
read ${TEST:=N}
if [[ "$TEST" == "o" && "$TEST" == "O" && "$TEST" == "y" && "$TEST" == "Y" ]] ; then
  PARMEC=1
fi

echo -e "\033[1m==========\033[0m Ajout des nouvelles langues"
for langue in $LANG_INSTALLEES
do
  get_nom_langue $langue
  SQL_TEST_LANGUE="SELECT COUNT(code) FROM adsys_langues_systeme WHERE code = '$langue';"
  LANGUE_PRESENTE=`return_psql "$SQL_TEST_LANGUE"`
  if [[ $LANGUE_PRESENTE == 0 ]]; then
    echo -e "\033[1m==========\033[0m Ajout des traductions sql pour la langue $nom_langue"
    SQL_LANGUE="INSERT INTO adsys_langues_systeme(code, langue) SELECT '$langue', makeTraductionLangSyst('$nom_langue');"
    execute_psql 'cmd' "$SQL_LANGUE"
    execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/${langue}.sql
    if [[ $PARMEC -eq 1 ]]; then
      execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/PARMEC.sql
    fi    
  fi
done
