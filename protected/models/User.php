<?php

@session_start();

/**
 * 用户基础信息
 * @author benzhan
 */
class User {
    private static $_extraInfo = null;

    /**
     * 当前用户的user_id
     * @author benzhan
     * @param string $session_id
     * @return Ambigous <>
     */
    public static function getUserId() {
        return $_SESSION['userId'];
    }

    public static function getUserName() {
        return $_SESSION['username'];
    }

    public static function getInfo() {
        return [
          'id' => $_SESSION['userId'],
          'username' => $_SESSION['username']
        ];
    }

    /**
     * 返回额外信息
     * @return array|mixed
     * @author benzhan
     */
    public static function getExtraInfo() {
        if (self::$_extraInfo === null) {
            if ($_SESSION['extraInfo']) {
                self::$_extraInfo = json_decode($_SESSION['extraInfo'], true);
            } else {
                $objUser = new TableHelper('cUser');
                $where = ['userName' => $_SESSION['username']];
                $keyWord = [
                  '_field' => 'extraInfo'
                ];

                $json = $objUser->getOne($where, $keyWord);
                if ($json) {
                    self::$_extraInfo = json_decode($json, true);
                } else {
                    self::$_extraInfo = [];
                }
            }
        }

        return self::$_extraInfo;
    }

}
