
*/5 * * * * root echo "[`date +"\%F \%T"`] `/usr/local/php/bin/php /data/webapps/admin.ouj.com/protected/bin/monitor/monitor_code.php`"  >> /tmp/monitor_code.log &

*/5 * * * * root echo "[`date +"\%F \%T"`] `/usr/local/php/bin/php /data/webapps/admin.ouj.com/protected/bin/monitor/monitor_delay.php`"  >> /tmp/monitor_delay.log &

*/5 * * * * root echo "[`date +"\%F \%T"`] `/usr/local/php/bin/php /data/webapps/admin.ouj.com/protected/bin/monitor/monitor_server.php`"  >> /tmp/monitor_server.log &

*/1 * * * * root echo "[`date +"\%F \%T"`] `/usr/local/php/bin/php /data/webapps/admin.ouj.com/protected/bin/NameClient/pubEchoEvent.php`"  >> /tmp/pubEchoEvent.log &

*/1 * * * * root echo "[`date +"\%F \%T"`] `/usr/local/php/bin/php /data/webapps/admin.ouj.com/protected/bin/monitor/monitor_redis_list.php`"  >> /tmp/monitor_redis_list.log &


# 定时转存图片
#*/1 * * * * root echo "[`date +"\%F \%T"`] `/usr/local/php/bin/php /data/webapps/admin.ouj.com/protected/bin/spider/uploadRes2Bs2.php`"  >> /tmp/uploadRes2Bs2.log &
*/1 * * * * root echo "[`date +"\%F \%T"`] `/usr/local/php/bin/php /data/webapps/admin.ouj.com/protected/bin/spider/res2Bs2Master.php`"  >> /tmp/res2Bs2Master.log &

