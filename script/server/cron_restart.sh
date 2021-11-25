#!/bin/bash
count=`ps -fe |grep "tp6_live_master" | grep -v "grep" | wc -l`
#count=`netstat -ln | grep 4074| wc -l`
echo $count
if [ $count -lt 1 ]; then
ps -eaf |grep "tp6_live_master" | grep -v "grep"| awk '{print $2}'|xargs kill -9
sleep 2

# /usr/local/php7/bin/php /data/www/shoxot-api/mqtt/socket.php
sh /data/www/mooc/tp6-live/script/server/reload.sh
echo "restart";
echo $(date +%Y-%m-%d_%H:%M:%S) >/data/www/mooc/tp6-live/runtime/log/ws-restart.log
fi