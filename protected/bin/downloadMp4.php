<?

set_time_limit(0);
require_once realpath(dirname(__FILE__)) . '/../common.php';

$GLOBALS['dbInfo']['o2o_store '] = array(
  'enable' => true,
  'dbType' => 'mysqli',
  'dbHost' => '221.228.83.154',
  'dbPort' => 3306,
  'dbName' => 'o2o_store ',
  'dbUser' => 'ojiatest',
  'dbPass' => 'ojia305',
);


$objTable = new TableHelper('bodybuilding', 'o2o_store');
$datas = $objTable->getAll();

// http://videocdn.bodybuilding.com/video/mp4/38000/38351m.mp4
foreach ($datas as $data) {
    $url = $data['mp4'];
    $info = parse_url($url);
    if ($info['host'] == 'videocdn.bodybuilding.com') {
        $newUrl = "http://test.www.ouj.com{$info['path']}";
        $newFile = "/data/webapps/test.www.ouj.com{$info['path']}";
        $newPath = dirname($newFile);

        mkdir($newPath, 0777, true);

        // $ret = file_put_contents($newFile, file_get_contents($url));
        $cmd = "cd $newPath; wget '$url';";
        exec($cmd, $output);

        $result = $objTable->updateObject(['mp4' => $newUrl], ['auto_id' => $data['auto_id']]);
        var_dump($result);
    }

}