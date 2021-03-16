#!/bin/bash
#to check whether directory exist and if so delete it :
if [ -d /tmp/MAE ]; then
	rm -rf /tmp/MAE/
elif [ ! -d /tmp/MAE ]; then
	mkdir /tmp/MAE
	chmod 777 /tmp/MAE
fi