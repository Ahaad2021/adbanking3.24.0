#!/bin/bash
############################################################
# Variables utiles
############################################################
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh

# Script de mise à jour ADbanking  3.2.2 => 3.4
echo -e "Mise à jour ADbanking \033[1mv3.2.x -> v3.4\033[0m"

psql7=`return_psql "SELECT COUNT(*) FROM pg_catalog.pg_trigger WHERE tgconstrname = (SELECT CHR(36)||'1') AND tgargs LIKE '%1%menus%ad_str%libel_menu%id_str%';"`
if [[ $psql7 > 0 ]]; then
  # Mise à jour de préparation au passage vers PostgreSQL 8 (donc également compatible avec la 7.4)
  # On fait cette mise à jour uniquement si elle n'a pas déjà eu lieu dans le passé.
  # On ignore les messages d'erreurs sur triggers.so et trig_suppr_trad car ils ne sont plus utilisés, voir #1056.
  (execute_psql 0 ../psql8/updata-psql8.sql) 3>&1 1>&2 2>&3 | grep -v triggers.so | grep -v suppr_trad | grep -v del_dans_ad_trad
fi

# Relecture des fonctions et triggers
execute_psql 0 ../Dump/triggers.sql

# Mise à jour structure
execute_psql 0 updata3.0.sql

# Relecture des fonction de la 3.0
execute_psql 0 Dump3.0/fonctions.sql
# Relecture des fonction et des écrans
execute_psql 0 ../Dump/frais_tenue_cpt.sql
execute_psql 0 ../Dump/tableliste.sql
execute_psql 0 ../Dump/menus.sql
execute_psql 0 ../Dump/calcul_interets_debiteurs.sql
execute_psql 'cmd' "VACUUM FULL ANALYZE"

