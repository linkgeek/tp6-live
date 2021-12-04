echo "loading..."
pid=`pidof sports_live_master`
echo $pid
kill -USR1 $pid
echo "loading success"