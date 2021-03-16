# Name: adbanking.spec
# Description: fichier SPEC pour ADbanking
# Author: ADbanking <adbanking@adfinance.org>

Summary: ADbanking v3
Name: adbanking
Version: 3.22
Release: 5beta5
License: None
Distribution: Fedora Core 16
Vendor: ADFinance
Group: Applications/Productivity
Packager: ADbanking developpers <devel@adfinance.org>
Source: %{name}-%{version}-%{release}.tar.gz
Requires: dialog, fop, httpd, java-sun, mod_ssl, perl-gettext, php, php-mbstring, php-pear-DB, php-pgsql, php-xml, postgresql-server, samba, php-pear-Numbers-Words, php-bcmath
BuildRoot: /tmp/build-%{name}-%{version}-%{release}

%description
ADbanking est une application de gestion d'Institutions de Microfinance. Elle est développée en PHP et utilise une base de données PostgreSQL.

%install
cd %{name}-%{version}-%{release}
rm -rf $RPM_BUILD_ROOT
make INSTALL_ROOT=$RPM_BUILD_ROOT install

%clean
cd %{name}-%{version}-%{release}
make INSTALL_ROOT=$RPM_BUILD_ROOT clean
rm -rf $RPM_BUILD_ROOT

%pre
#!/bin/sh
# Vérification de l'initialisation de PostgreSQL, et initialisation si nécessaire
if [ ! -e /var/lib/pgsql/data/PG_VERSION ]
then
    service postgresql initdb
fi

%post
#!/bin/sh
# Redémarrage des services postgresql, httpd, nmb, smb et cron
chkconfig postgresql on
service postgresql restart
chkconfig httpd on
service httpd restart
chkconfig nmb on
service nmb restart
chkconfig smb on
service smb restart
chkconfig crond on
service crond restart

# Notice à l'utilisateur
# Installation interactive impossible avec Yum
echo
echo -e "Si vous avez déjà une BD ADbanking, vérifiez si elle est à jour et lancez les \033[1mscripts dans /usr/share/adbanking/db/\033[0m si nécessaire."
echo -e "Sinon, vous devez lancer le script \033[1m/usr/share/adbanking/db/new_db.sh\033[0m sous l'utilisateur root pour en créer une."
echo

chmod 777 /usr/share/adbanking/web
chmod 777 /usr/share/adbanking/web/jasper_config

# !!! Attention !!!
# Tous les répertoires extraits du dépôt SVN doivent avoir les permissions correctes   !
# Le flag d'exécution des scripts doit aussi être correctement placé dans le dépôt SVN !
# Ce qu'il n'est pas possible de règler dans le dépôt SVN doit alors être mis dans     !
# le script construisant le RPM                                                        !
# %attr(644,root,root) %config(noreplace) /etc/cron.d/adbanking
%files
%attr(644,root,root) %config(noreplace) /etc/cron.d/adbanking
%attr(644,root,root) %config(noreplace) /etc/httpd/conf.d/adbanking.conf
%attr(644,root,root) %config(noreplace) /etc/logrotate.d/adbanking
%attr(644,root,root) %config(noreplace) /etc/php.d/ioncube.ini
%attr(644,root,root) %config(noreplace) /etc/samba/adbanking.conf
%attr(644,root,root) %config(noreplace) /usr/share/adbanking/web/adbanking.ini
%attr(644,root,root) %config(noreplace) /usr/lib/php/modules/ioncube_loader_lin_5.3.so
%attr(755,root,root) /usr/share/adbanking
%attr(755,apache,apache) /usr/share/adbanking/jasper
%attr(755,apache,apache) /var/lib/adbanking
%attr(755,apache,apache) /var/lib/adbanking/rapports/jasper
%attr(755,apache,apache) /var/log/adbanking
