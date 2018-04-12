<?php


/**
 * 用户基础信息
 * @author benzhan
 */
class User extends R2MModel {
    
    protected $tableName = 'user';
    protected $dbKey = 'dw_task';
    protected $cacheKey = 'dw_task';     
    private static $userInfo = null;
    
    /**
     * 当前用户的user_id
     * @author benzhan
     * @param string $session_id
     * @return string
     */
    public static function getUserId() {
        return self::$userInfo['id'] ?: 0;
    }

    public static function getInfo() {
        return [
          'id' => '123',
          'username' => 'dw_zhanchaojiang'
        ];
    }

}
