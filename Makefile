# Define useful variables
SOURCE_DIR = `pwd`
INSTALL_DIR = /usr/share/adbanking
# Créer aussi le var/log/adbanking ou vérifier les droits
RSYNC_EXCLUDE = ".svn/"

all: adbanking-devel

# ADbanking developer install
adbanking-devel:
	@															\
	echo -e "\033[1mADbanking\033[0m developper install";									\
	echo "----------------------------";											\
	echo "From ${SOURCE_DIR} to ${INSTALL_DIR}";										\
	echo "----------------------------";											\
	echo;															\
	if [[ ! -e ${INSTALL_DIR} ]];												\
	then															\
		echo "We need the root password to create ${INSTALL_DIR}";							\
		su -c 'mkdir ${INSTALL_DIR}; chmod 777 ${INSTALL_DIR}';								\
	fi;															\
	if [[ ! -e ${INSTALL_DIR}/bin ]];											\
	then															\
		echo "Creating ${INSTALL_DIR}/bin";										\
		mkdir -p ${INSTALL_DIR}/bin;											\
	fi;															\
	echo "Copying utilities to ${INSTALL_DIR}/bin";										\
	rm -f ${INSTALL_DIR}/bin/*;												\
	rsync -a --delete --force --exclude ${RSYNC_EXCLUDE} utilities/devel/* ${INSTALL_DIR}/bin;				\
	rsync -a --delete --force --exclude ${RSYNC_EXCLUDE} utilities/main/* ${INSTALL_DIR}/bin;				\
	if [[ ! -e ${INSTALL_DIR}/db ]];											\
	then															\
		echo "Creating ${INSTALL_DIR}/db";										\
		mkdir -p ${INSTALL_DIR}/db;											\
	fi;															\
	echo "Copying db files to ${INSTALL_DIR}/db";										\
	rsync -a --delete --force --exclude ${RSYNC_EXCLUDE} db/ ${INSTALL_DIR}/db;						\
	if [[ ! -e ${INSTALL_DIR}/web ]]; 											\
	then 															\
		echo "Creating ${INSTALL_DIR}/web";										\
		mkdir -p ${INSTALL_DIR}/web;											\
	fi;															\
	echo "Copying source files to ${INSTALL_DIR}/web";									\
	rsync -a --delete --force --exclude ${RSYNC_EXCLUDE} web/ ${INSTALL_DIR}/web;						\
	rsync -a --delete --force --exclude ${RSYNC_EXCLUDE} multilingue ${INSTALL_DIR}/web;					\
	rsync -a --delete --force --exclude ${RSYNC_EXCLUDE} recup_data ${INSTALL_DIR}/web;					\
	rsync -a --delete --force --exclude ${RSYNC_EXCLUDE} web/rapports/xslt/ ${INSTALL_DIR}/web/rapports/xslt/fr_BE;		\
	echo;															\
	echo -e "\033[1mAll done!\033[0m";											\

# Fedora 7 specific part
fc7: /etc/httpd/conf.d/adbanking.conf /etc/logrotate.d/adbanking
VPATH = packaging/rpm/rpm-files/conf/etc/
/etc/httpd/conf.d/adbanking.conf: httpd/conf.d/adbanking.conf
	@echo "Root password is needed to copy apache conf file"
	@su -c 'cp -f $< $@'
	@echo "Apache config file for ADbanking copied."
/etc/logrotate.d/adbanking: logrotate.d/adbanking
	@echo "Root password is needed to copy logrotate conf file"
	@su -c 'cp -f $< $@'
	@echo "Logrotate config file for ADbanking copied."

