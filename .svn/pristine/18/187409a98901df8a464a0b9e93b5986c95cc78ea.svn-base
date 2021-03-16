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

for langue in `return_psql "SELECT code FROM adsys_langues_systeme WHERE code != '$LANG_SYST_DFT';"`
do
  echo -e "\033[1m==========\033[0m Rechargement \033[1m${langue}\033[0m"
  execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/${langue}.sql
  PARMEC=`return_psql "SELECT COUNT(id_str) FROM ad_traductions WHERE traduction = 'PARMEC' AND langue = '$LANG_SYST_DFT';"`
  if [[ "$PARMEC" != "0" ]]; then
    execute_psql 0 $ADB_INSTALL_DIR/web/locale/${langue}/PARMEC.sql
  fi    
done
