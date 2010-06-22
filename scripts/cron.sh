#!/bin/sh
# -----------------------------------------------------------------------------
# Cron job used to delete expired files.
#
# This script needs to be executed by www user in order to append data to
# existing log files.
#
# IMPORTANT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
#
# Your CRON should execute a copy of this file in order to keep your 
# configuration unchanged after filez updates.
#
# -----------------------------------------------------------------------------

# Directory where log file will be stored. Could be the same as in filez.ini
# In this case, messages will be appended at the end of the file
LOG_DIR=/var/log/filez

# Url of filez web root
URL=http://filez-url.com

LANG=fr

url_output="`wget --header=\"Accept-Language: $LANG\"  -q -O - $URL/admin/checkFiles`"

if test -n "$url_output"; then
    cd $LOG_DIR
    echo "[`date +'%FT%T%:z'`] [CRON] wget output: "$url_output >> "$LOG_DIR/filez-cron-error.log"
fi
