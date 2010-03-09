#!/bin/sh
# -----------------------------------------------------------------------------
# Cron job used to delete expired files.
#
# This script needs to be executed by www user in order to append data to
# existing log files.
# -----------------------------------------------------------------------------

# Directory where log file will be stored. Could be the same as in filez.ini
# In this case, messages will be appended at the end of the file
LOG_DIR=/var/log/filez

# Url of the CRON web task
URL=http://filez-url/admin/checkFiles

url_output="`wget -q -O - $URL`"

if test -n "$url_output"; then
    cd $LOG_DIR
    echo "[`date +'%FT%T%:z'`] [CRON] wget output: "$url_output >> "$LOG_DIR/filez-cron-error.log"
fi
