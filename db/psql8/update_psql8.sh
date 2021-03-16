#!/bin/bash
############################################################
# Variables utiles
############################################################
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}

# Script de préparation de ADbanking pour PostgreSQL 8
# Ce script est transversal aux scripts de mise à jour d'ADbanking
# Vous pouvez donc l'utiliser sur un BD de n'importe quelle version d'ADbanking.
#
# Si vous êtes déjà passé par la v2.8.4, vous n'avez plus besoin de ce script.
# Il détectera automatiquement que la mise à jour a déjà été appliquée.
echo -e "Mise à jour ADbanking pour \033[1mPostgreSQL 8\033[0m"

psql7=`return_psql "SELECT COUNT(*) FROM pg_catalog.pg_trigger WHERE tgconstrname = (SELECT CHR(36)||'1') AND tgargs LIKE '%1%menus%ad_str%libel_menu%id_str%';"`
if [[ $psql7 == 0 ]]; then
  echo -e "Votre BD semble déjà être \033[1mcompatible avec PostgreSQL 8\033[0m !"
  exit
fi

# Relecture des triggers
execute_psql 0 ../Dump/triggers.sql

# Mise à jour structure
# On ignore les messages d'erreurs sur triggers.so et trig_suppr_trad car ils ne sont plus utilisés, voir #1056.
(execute_psql 0 updata-psql8.sql) 3>&1 1>&2 2>&3 | grep -v triggers.so | grep -v suppr_trad | grep -v del_dans_ad_trad

# Relecture des fonctions et des écrans
execute_psql 0  ../Dump/tableliste.sql
execute_psql 0  ../Dump/menus.sql
# Optimisation
execute_psql 'cmd' "VACUUM FULL ANALYZE"

