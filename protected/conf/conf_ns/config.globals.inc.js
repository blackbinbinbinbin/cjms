// 常量
global["BS2_ACCESS_KEY"] = 'ak_uiz';
global["BS2_ACCESS_SECRET"] = 'fd855479b53b6632204c8d0f0b8ed47428265bda';
global["BS2_AUDIO_BUCKET"] = 'ojiastoreaudio';
global["BS2_DEL_HOST"] = 'bs2.yy.com';
global["BS2_DL_HOST"] = 'bs2dl.huanjuyun.com';
global["BS2_FILE_BUCKET"] = 'ojiastoreimage';
global["BS2_HOST"] = 'bs2ul.yy.com';
global["BS2_LARGE_FILE_BUCKET"] = 'ojiaauthorvideos';
global["BS2_SNS_BUCKET"] = 'ojiasnsimage';
global["BS2_VIDEO_BUCKET"] = 'ojiastorevideos';

// 数组
 
var dbInfo = {};
dbInfo["crawl"] = {"dbHost":"61.160.36.225","dbName":"crawl","dbPass":"ojia305","dbPort":"3306","dbType":"mysqli","dbUser":"ojiatest","enable":"true"}; 
dbInfo["ms"] = {"dbHost":"127.0.0.1","dbName":"ms","dbPass":"root","dbPort":"3306","dbType":"mysqli","dbUser":"root","enable":"1"}; 
dbInfo["Report"] = {"dbHost":"61.160.36.225","dbName":"Report","dbPass":"root","dbPort":"3306","dbType":"mysqli","dbUser":"root","enable":"true"}; 
dbInfo["Web"] = {"dbHost":"127.0.0.1","dbName":"Web","dbPass":"root","dbPort":"3306","dbType":"mysqli","dbUser":"root","enable":"true"};
exports["dbInfo"] = dbInfo;
