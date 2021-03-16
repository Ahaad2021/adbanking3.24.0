#!/bin/bash
# Name: build-rpm.sh
# Description: Script d'aide à la construction du RPM d'ADbanking
# Notes:
#	This script needs svn > 1.2 to work correctly

# User defined constants
#RED_HAT_DIR="/root/rpmbuild"
RED_HAT_DIR="/home/ovh/rpmbuild"
RPM_BUILD_DIR=$RED_HAT_DIR"/BUILD"
# If building on something else than i386, this needs to be changed
RPM_RPMS_DIR=$RED_HAT_DIR"/RPMS/i386"
RPM_SOURCE_DIR=$RED_HAT_DIR"/SOURCES"
RPM_SPECS_DIR=$RED_HAT_DIR"/SPECS"
RPM_SRPMS_DIR=$RED_HAT_DIR"/SRPMS"
SPEC_FILE=$RPM_SPECS_DIR"/adbanking.spec"
SPEC_FILE_SVN=`pwd`"/SPECS/adbanking.spec"
RPM_FILES_DIR=`pwd`"/rpm-files"
TMP_FILE="/tmp/adbanking.spec.tmp"
TMP_DIR="/tmp/ADbanking"
SVN_SERVER="https://adbanking@localhost/svn/adbanking/"
INSTALL_DIR="/usr/share/adbanking"
INSTALL_DIR_RE=${INSTALL_DIR//\//\\\/}
ENC_SUFFIX="enc"
#WEB_DIR="/var/home/releases/v"
#CLIENT_WEB_DIR="/var/home/clients/version"
WEB_DIR="/home/ovh/releases/v"
CLIENT_WEB_DIR="/home/ovh/clients/version"

#IONCUBE_ENCODER_PATH="/usr/local/ioncube/ioncube_encoder.sh"

echo
echo -e "\033[1mADbanking RPM\033[0m building process"
echo -e "\033[1m==============================\033[0m"
if [[ ! -e $RPM_FILES_DIR ]]; then
	echo
	echo "You must have a \$RPM_FILES_DIR directory available."
	echo "It is currently set to $RPM_FILES_DIR"
	echo "This directory should contain:"
	echo -e "\033[1mconf/\033[0m directory with apache, crontab, php, postgres and samba configuration files"
	echo -e "\033[1mlib/\033[0m directory with the ion-cube loader"
	echo -e "\033[1mMakefile\033[0m to build, intall and clean an adbanking application"
	exit
fi

touch ${RPM_SOURCE_DIR}ADB-test
if [[ $? > 0 ]] ; then
        echo
        echo -e "You must have write to all subdirectories in $RED_HAT_DIR to be able to build the RPM."
        echo -e "\033[1mPlease check access rights."
        exit
fi
rm ${RPM_SOURCE_DIR}ADB-test

# Checking ionCube encoder
# If not found, then we don't make encoded packaging and we don't publish on the web;

#IONCUBE_ENCODER=`which /usr/local/ioncube/ioncube_encoder.sh -5`
#IONCUBE_ENCODER=`which ioncube_encoder5` #ioncube_encoder5

IONCUBE_ENCODER='/usr/local/ioncube/ioncube_encoder.sh -53'

if [[ $? > 0 ]] ; then
        IONCUBE_ENCODER=""
        echo
        echo -e "\033[1mNo ionCube encoder could be found\033[0m, RPM with encoded sources will not be built."
        echo -e "(check that \033[1mioncube_encoder5\033[0m is in the PATH)"
else
        $IONCUBE_ENCODER -S ../../web/mainframe/mainframe.php
        #$IONCUBE_ENCODER -S ../../web/mainframe/mainframe.php -o ../../web/mainframe/mainframe_enc_test.php
        if [[ $? > 0 ]] ; then
                IONCUBE_ENCODER=""
                echo
                echo -e "\033[1mionCube encoder execution failed\033[0m, RPM with encoded sources will not be built."
                echo -e "(check above messages to correct the problem)"
        fi
fi

echo
echo "Copying $SPEC_FILE_SVN to $SPEC_FILE"
cp -f $SPEC_FILE_SVN $SPEC_FILE

# Version and revision number check
# TODO: version number should come from VariablesGlobales.php
echo
VERSION=`grep ^Version: $SPEC_FILE`
VERSION=${VERSION/Version: /}
echo -e "Current ADbanking \033[1mversion\033[0m number is \033[1m$VERSION\033[0m please type new version number:"
unset ANSWER
read -p "(empty entry will not change version number) " ANSWER
if [[ $ANSWER ]]; then
	if [[ "$ANSWER" == ".*-.*" ]]; then
		echo -e "Version number cannot contain \033[1m- (minus)\033[0m chars!"
		exit
	else
		VERSION=$ANSWER
	fi
fi
# We extract the major.intermediate version number
INT_VERSION=${VERSION%.*}
if [[ $INT_VERSION == ${INT_VERSION%.*} ]]; then
	INT_VERSION=$VERSION
fi
echo
RELEASE=`grep ^Release: $SPEC_FILE`
RELEASE=${RELEASE/Release: /}
RELEASE=${RELEASE//enc/}
echo -e "Current ADbanking RPM \033[1mrelease\033[0m number is \033[1m$RELEASE\033[0m please type new release number:"
unset ANSWER
read -p "(enter will not change release number) " ANSWER
if [[ $ANSWER ]]; then
	RELEASE=$ANSWER
fi

# SVN tag to use ?
echo
echo -e "Which \033[1mSVN tag\033[0m should I use to extract sources?"
echo -en "(enter will select tags/\033[1madbanking-${VERSION}\033[0m) "
read SVN_TAG
if [[ "$SVN_TAG" == "" ]]; then
        SVN_TAG="adbanking-${VERSION}"
fi
if [[ "$SVN_TAG" =~ "branches\/.*" ]]; then
  SVN_PATH="$SVN_TAG"
else
  SVN_PATH="tags/$SVN_TAG"
fi
echo $SVN_PATH

# Checking SVN access
echo
echo -e "\033[1m==============================================================================\033[0m"
echo -e "\033[1mRepository information\033[0m (checking access, please wait):"
echo
SVN_VERSION=`svn --version | head -1 | awk '{print $3}'`
if [[ $SVN_VERSION > 1.2 ]] ; then
  svn info ${SVN_SERVER}${SVN_PATH}
else
  echo "'svn info' command not available (v${SVN_VERSION}, need 1.2 at least), only checking access."
  echo
  svn list ${SVN_SERVER}${SVN_PATH} > /dev/null
fi
echo -e "\033[1m==============================================================================\033[0m"
# As of now, svn info return 0 even in case of bad url :(
# see http://svn.haxx.se/dev/archive-2005-10/0434.shtml
if [[ $? != 0 ]]; then
  echo
  echo -e "Access to the SVN server doesn't seem to be possible."
  echo -e "Check above message for error and check the SVN tag you gave."
  echo
  echo -e "If everything seems fine, then check that you have ssh access by running"
  echo -e "\033[1mssh adbanking@devel.adbanking.org\033[0m"
  echo
  exit
fi
echo -e "Access \033[1mOk\033[0m!"

# Confirmation
echo
echo -en "I'm about to build \033[1madbanking-${VERSION}-${RELEASE}\033[0m and "
if [[ "$IONCUBE_ENCODER" != "" ]]; then
        echo -e "\033[1madbanking-${VERSION}-${RELEASE}${ENC_SUFFIX}\033[0m"
else
        echo -e "\033[1mno ionCube encoded RPM\033[0m"
fi
echo -e "from \033[1m${SVN_SERVER}${SVN_PATH}\033[0m"
unset ANSWER
echo
echo -en "Is it \033[1mOK to proceed\033[0m?"
read -p " (Y/n) " -n 1 ANSWER
echo
if [[ "${ANSWER:=Y}" == "n" || "${ANSWER:=Y}" == "N" ]]; then
	exit
fi

# Do a checkout of the SVN repository
cd $RPM_SOURCE_DIR
echo -n "Removing a previously checked out tree..."
rm -rf $TMP_DIR
mkdir -p $TMP_DIR
cd $TMP_DIR
echo "   done."
echo -n "Extracting ADbanking sources from SVN repository in $RPM_SOURCE_DIR "
svn checkout -q ${SVN_SERVER}${SVN_PATH}/utilities/main bin
echo -n "."
svn checkout -q ${SVN_SERVER}${SVN_PATH}/db db
echo -n "."
svn checkout -q ${SVN_SERVER}${SVN_PATH}/web web
echo -n "."
svn checkout -q ${SVN_SERVER}${SVN_PATH}/jasper jasper
echo -n "."
svn checkout -q ${SVN_SERVER}${SVN_PATH}/recup_data web/recup_data
cd $RPM_SOURCE_DIR
rm -rf adbanking-$VERSION-*
mv $TMP_DIR adbanking-$VERSION-${RELEASE}
cd adbanking-${VERSION}-${RELEASE}
echo "   done."

# Adding RPM specific files (from current workdir)
cp -a ${RPM_FILES_DIR}/* .

# Cleaning up directory tree
echo -n "Cleaning up directory from SVN and temporary files..."
find . -name .svn -prune -exec rm -rf {} \;
find . -depth -name ~$ -exec rm -f {} \;
find . -depth -name \#$ -exec rm -f {} \;
echo "   done."

# ioncube encoding of PHP files
if [[ "$IONCUBE_ENCODER" != "" ]]; then
        cd $RPM_SOURCE_DIR
        echo -n "ionCube encoding of PHP files..."
        cp -a adbanking-${VERSION}-${RELEASE} adbanking-${VERSION}-${RELEASE}${ENC_SUFFIX}
        cd adbanking-${VERSION}-${RELEASE}${ENC_SUFFIX}
	$IONCUBE_ENCODER  --with-license licence.txt --passphrase adbankingpublic   web -o web.encoded
	rm -rf web
	mv web.encoded web
        echo "   done."
        RELEASES="${RELEASE} ${RELEASE}${ENC_SUFFIX}"
else
        RELEASES="${RELEASE}"
fi

for RELEASE_NAME in $RELEASES;
do
        echo -e "\033[1mBuilding adbanking-${VERSION}-${RELEASE_NAME}\033[0m"
        cd ${RPM_SOURCE_DIR}/adbanking-${VERSION}-${RELEASE_NAME}

        # Updating Version:, Release: and %files sections from adbanking.spec
        echo -n "Writing version and release number to spec file..."
        sed -i "s/^Version:\ .*/Version: $VERSION/" $SPEC_FILE
        sed -i "s/^Release:\ .*/Release: ${RELEASE_NAME}/" $SPEC_FILE
        echo "   done."

        # Create tar.gz archive
        echo -en "Creating \033[1mtar.gz archive\033[0m from source directory..."
        ARC_DIR=`pwd`
        ARC_DIR=${ARC_DIR/\/*\//}
        cd ..
        tar -zcf $ARC_DIR.tar.gz $ARC_DIR
        echo "   done."

        # Create RPM and SRPM
        echo -e "Creating \033[1mRPM\033[0m file..."
        cd ${RPM_BUILD_DIR}
        rm -rf ${ARC_DIR}
        mv -f ${RPM_SOURCE_DIR}/${ARC_DIR} .
        cd ${ARC_DIR}
        pwd
        #rpmbuild --quiet -ba ${SPEC_FILE} > /dev/null
		rpmbuild -vv -ba ${SPEC_FILE} > /dev/null

	# Copying to web directory for download
        if [[ "$IONCUBE_ENCODER" != "" ]]; then
                echo -n "Copying to web directory for download..."
                if [[ -d ${WEB_DIR}${INT_VERSION} ]]; then
                        cp ${RPM_RPMS_DIR}/adbanking-${VERSION}-${RELEASE_NAME}.i386.rpm ${WEB_DIR}${INT_VERSION}/
                        echo "done."
                else
                        echo -e "\033[1mWeb directory ${WEB_DIR}${INT_VERSION} doesn't exist!\033[0m"
                        echo -en "Should I create it?"
                        unset ANSWER
                        read -p " (Y/n)" -n 1 ANSWER
                        echo
                        if [[ "${ANSWER:=Y}" != "n" && "${ANSWER:=Y}" != "N" ]]; then
                                echo "Creating ${WEB_DIR}${INT_VERSION}"
                                mkdir -p ${WEB_DIR}${INT_VERSION}
                                echo "And copying RPM file"
                                cp ${RPM_RPMS_DIR}/adbanking-${VERSION}-${RELEASE_NAME}.i386.rpm ${WEB_DIR}${INT_VERSION}/
                        fi
                fi
        fi
done

# Creating link for client download, only for encoded version!
if [[ "$IONCUBE_ENCODER" != "" ]]; then
        echo -n "Linking encoded RPM file to client web directory for download..."
        if [[ -d ${CLIENT_WEB_DIR}${INT_VERSION} ]]; then
                ln -s ${WEB_DIR}${INT_VERSION}/adbanking-${VERSION}-${RELEASE}${ENC_SUFFIX}.i386.rpm ${CLIENT_WEB_DIR}${INT_VERSION}/
                echo "done."
        else
                echo -e "\033[1mWeb directory ${CLIENT_WEB_DIR}${INT_VERSION} doesn't exist!\033[0m"
                echo -en "Should I create it?"
                unset ANSWER
                read -p " (Y/n)" -n 1 ANSWER
                echo
                if [[ "${ANSWER:=Y}" != "n" && "${ANSWER:=Y}" != "N" ]]; then
                        echo "Creating ${CLIENT_WEB_DIR}${INT_VERSION}"
                        mkdir -p ${CLIENT_WEB_DIR}${INT_VERSION}
                        echo "And linking encoded RPM file"
                        ln -s ${WEB_DIR}${INT_VERSION}/adbanking-${VERSION}-${RELEASE}${ENC_SUFFIX}.i386.rpm ${CLIENT_WEB_DIR}${INT_VERSION}/
                fi
        fi
fi

echo
echo
echo -e "\033[1m======================================\033[0m"
echo -e "version: \033[1m${VERSION}\033[0m"
echo -e "release: \033[1m${RELEASE}\033[0m"
echo -e "from SVN \033[1m${SVN_SERVER}${SVN_PATH}\033[0m"
echo -e "\033[1m======================================\033[0m"
echo -e "Generated \033[1mADbanking RPM\033[0m should be in:"
for RELEASE_NAME in $RELEASES;
do
        echo -e "\033[1m${RPM_RPMS_DIR}/adbanking-${VERSION}-${RELEASE_NAME}.i386.rpm\033[0m"
done
echo -e "\033[1m======================================\033[0m"
echo -e "ADbanking \033[1mtar.gz archive\033[0m should be in:"
for RELEASE_NAME in $RELEASES;
do
        echo -e "\033[1m${RPM_SOURCE_DIR}/adbanking-${VERSION}-${RELEASE_NAME}.tar.gz\033[0m"
done
echo -e "\033[1m======================================\033[0m"
if [[ "$IONCUBE_ENCODER" != "" ]]; then
        if [[ -d ${WEB_DIR}${INT_VERSION} ]]; then
                echo -e "Available for \033[1mdownload\033[0m from"
                echo -e "\033[1m${WEB_DIR}${INT_VERSION}\033[0m"
                if [[ -d ${CLIENT_WEB_DIR}${INT_VERSION} ]]; then
                        echo -e "\033[1m${CLIENT_WEB_DIR}${INT_VERSION}\033[0m"
                fi
                echo -e "\033[1m======================================\033[0m"
        fi
fi
echo

