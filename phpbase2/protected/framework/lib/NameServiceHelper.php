<?php
/**
 * 配置生成类
 * @author solu
 */
define('KEYS_PREFIX', '_keys:');

class NameServiceHelper {

    private $_config = [];
    public $objRedis;

    public function __construct($conifg) {
        $this->_config = $conifg;
        dwRedis::cleanInstance();
        $this->objRedis = dwRedis::init('name_serv');
        $this->tpl = Template::init();
    }

    public function getKeys($topKey) {
        $keys = [];

        $key = KEYS_PREFIX . $topKey;
        $keyHash = $this->objRedis->hGetAll($key);
        $keyHash && $keys = array_keys($keyHash);

        return $keys;
    }

    private function getArrayKey($keyName) {
        $keyName = explode(':', $keyName);
        array_shift($keyName);
        $str =  "['" . join("']['", $keyName) . "']";
        return $str;
    }

    public function getConfig($keys) {
        $configs = [];
        $types = [
            Redis::REDIS_STRING => [
                'menthod' => 'get',
                'name' => 'string',
            ],
            Redis::REDIS_HASH  => [
                'menthod' => 'hGetAll',
                'name' => 'hash',
            ]
        ];
        foreach ($keys as $k) {
            $type = $types[$this->objRedis->type($k)];
            if (!$type) {
                continue;
            }

            list($top, $keyName, $subKeyName) = explode(':', $k);
            $value = call_user_func_array([$this->objRedis, $type['menthod']], [$k]);

            'hash' === $type['name'] && $keyName = $this->getArrayKey($k);
            $configs[$type['name']][$keyName] = $value;
        }

        return $configs;
    }

    public function makeConfig($topKey) {
        $keys = $this->getKeys($topKey);
        $configs = $this->getConfig($keys);

        $this->tpl->assign('configs', $configs);
        $tmpl = $this->_config['type'];
        $strConfig = $this->tpl->fetch("name_server/{$tmpl}");
        $strConfig = "<?php\n" . $strConfig;

        $now = date('Y-m-d H:i:s');
        $path = $this->_config['path'];
        if (file_put_contents($path, $strConfig)) {
            var_dump("[{$now}] path:{$path} 生成配置成功");
            ob_flush();
        } else {
            var_dump("[{$now}] path:{$path} 生成配置失败");
            ob_flush();
        }

    }

}
