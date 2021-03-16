#!/bin/bash
############################################################
# Variables utiles
############################################################
OLD_VERSION="3.10.3"
VERSION="3.12.1"
let ${DBUSER:=adbanking}
let ${DBNAME:=$DBUSER}
source ${ADB_INSTALL_DIR:="/usr/share/adbanking"}/web/lib/bash/misc.sh 

echo -e "Mise à jour ADbanking \033[1mv${OLD_VERSION} -> v${VERSION}\033[0m"

# Est-ce toujours utile ? -- antoine
psql7=`return_psql "SELECT COUNT(*) FROM pg_catalog.pg_trigger WHERE tgconstrname = (SELECT CHR(36)||'1') AND tgargs LIKE '%1%menus%ad_str%libel_menu%id_str%';"`
if [[ $psql7 > 0 ]]; then
  # Mise à jour de préparation au passage vers PostgreSQL 8 (donc également compatible avec la 7.4)
  # On fait cette mise à jour uniquement si elle n'a pas déjà eu lieu dans le passé.
  # On ignore les messages d'erreurs sur triggers.so et trig_suppr_trad car ils ne sont plus utilisés, voir #1056.
  (execute_psql 0 ../psql8/updata-psql8.sql) 3>&1 1>&2 2>&3 | grep -v triggers.so | grep -v suppr_trad | grep -v del_dans_ad_trad
fi

# Relecture des fonctions et triggers
execute_psql 0 ../Dump/triggers.sql

# Relecture des fonction et des écrans
#execute_psql 0 ../Dump/empty_tables.sql
execute_psql 0 ../Dump/fonction_comptable.sql
execute_psql 0 ../Dump/fonctions.sql
execute_psql 0 ../Dump/frais_tenue_cpt.sql
execute_psql 0 ../Dump/arrete_compte.sql
execute_psql 0 ../Dump/tableliste.sql
execute_psql 0 ../Dump/menus.sql
execute_psql 0 ../Dump/calcul_interets_debiteurs.sql
execute_psql 0 ../Dump/report_fonctions.sql

# Correction #505 : A executer seulement dans le script maj v3.12 et ne pas inclure dans les prochains releases
execute_psql 0 ../Dump/traitement_comptes_dormants.sql

# Mise à jour structure
execute_psql 0 updata3.12.sql

# Mise-a-jour duterimbere : nouveau etats credits
execute_psql 0 fix_etats_credit.sql

# Rechargement des traductions
source $ADB_INSTALL_DIR/bin/reloadlangues.sh

execute_psql 'cmd' "VACUUM FULL ANALYZE"
