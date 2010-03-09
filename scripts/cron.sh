#!/bin/sh

# Directory where log files will be stored
DIR=/var/log/filez

# File containing URL output
FILE=filez-cron-`date +"%Y%m%d"`.log

# File containing wget errors
LOGFILE=wget.log

# Url of the CRON task
URL=http://localhost/fz/admin/checkFiles

cd $DIR
wget $URL -O $FILE -o $LOGFILE

