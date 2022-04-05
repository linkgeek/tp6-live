echo "loading..."
pid=`pidof tp6_live_master`
echo $pid
kill -USR1 $pid
echo "loading success"