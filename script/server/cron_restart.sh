#!/bin/bash
count=`ps -fe |grep "live_master" | grep -v "grep" | wc -l`
#count=`netstat -ln | grep 4076| wc -l`
echo $count
if [ $count -lt 1 ]; then
ps -eaf |grep "live_master" | grep -v "grep"| awk '{print $2}'|xargs kill -9
sleep 2

# /usr/local/php7/bin/php /data/www/shoxot-api/mqtt/socket.php
sh /data/www/pro/tp5-live/script/bin/server/reload.sh
echo "restart";
echo $(date +%Y-%m-%d_%H:%M:%S) >/data/www/pro/tp5-live/runtime/log/ws-restart.log
fi