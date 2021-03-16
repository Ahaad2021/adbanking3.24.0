#!/bin/bash
# Script to run before deploying to a developer's VM
# It takes care of setting up the correct permissions, flags and other stuff whenever needed.


# Test network to see what is the entrance gate to our VM
ping -c 1 -qn -t 1 ${vmname} > /dev/null
if [ $? != 0 ] ; then
  vmdeploy=${vmname2}
else
  vmdeploy=${vmname}
fi


# Deploy in VM
/usr/bin/rsync -av --delete  ./ root@${vmdeploy}:/usr/share/adbanking

												\
/usr/bin/rsync -av --delete utilities/devel/* root@${vmdeploy}:/usr/share/adbanking/bin
/usr/bin/rsync -av --delete utilities/main/* root@${vmdeploy}:/usr/share/adbanking/bin
/usr/bin/rsync -av --delete recup_data root@${vmdeploy}:/usr/share/adbanking/web
