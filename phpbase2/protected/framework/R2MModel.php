<?php
class R2MModel extends Model {
    protected $cacheKey = 'default';
    
    /**
     * 数据库的表助手
     * @var Redis2Mysql
     */
    public $objR2m;
    // public $objRedis;

    public function __construct() {
        parent::__construct();
        $this->getHelper();
    }

    private function getHelper() {
        if (!$this->objR2m) {
            if ($GLOBALS['r2mMode'] == 'lib') {
                $this->objR2m = new Redis2Mysql($this->tableName, $this->dbKey, $this->cacheKey);
            } else {
                $this->objR2m = new R2m_Client($this->tableName, $this->dbKey, $this->cacheKey);
            }
        }
        
        return $this->objR2m;
    }

    public function getRedis() {
        return dwRedis::init($this->cacheKey);
    }

}

