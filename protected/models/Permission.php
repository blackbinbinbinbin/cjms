<?php

require_once ROOT_PATH . '/conf/diyConfig.inc.php';

class Permission {
	
	public static function isAdmin($tableId) {
	    $oConfig = new Diy_Table();
	    $tableInfo = $oConfig->getTableInfo($tableId);
	
	    return self::isAdmin2($tableInfo);
	}
	
	public static function isAdmin2($tableInfo) {
	    $admins = explode(',', $tableInfo['admins']);
	    $admins = array_merge($admins, $GLOBALS['diy']['whiteList']);
	    return in_array($_SESSION['username'], $admins);
	}
	
	public static function isAdmin3($userId = null) {
	    $userId || $userId = $_SESSION['username'];
	    
	    $admins = $GLOBALS['diy']['whiteList'];
	    return in_array($userId, $admins);
	}
	
	public static function checkTableRight($tableId, $userId = null) {
	    // 操作日志报表，不需要检查权限
        if ($tableId == 'a23a7321-60de-42be-c061-21d4ac081734') {
            return true;
        }

		$userId || $userId = $_SESSION['username'];
		$isAdmin = self::isAdmin($tableId);
		if (!$isAdmin) {
	         // 检查是否有权限
			$objMenuNode = new CMenuNode();

			$keyWord = array('_field' => 'nodeId');

			$where = array('leftTableId' => $tableId);
			$leftNodeIds = $objMenuNode->objTable->getCol($where, $keyWord);

			$where = array('rightTableId' => $tableId);
			$rightNodeIds = $objMenuNode->objTable->getCol($where, $keyWord);

			$nodeIds = array_merge($leftNodeIds, $rightNodeIds);
			$nodeIds = array_unique($nodeIds);
			$flag = self::checkRight($nodeIds, $userId);
			if (!$flag) {
				Response::error(CODE_NO_PERMITION, null, "nodeIds:" . join(',', $nodeIds) . ", userId:{$userId}");
			} else {
				self::checkAnotherPwd($nodeIds);
			}

		}
	}
	
	/**
	 * 检查nodeId是否有权限
	 * @author benzhan
	 * @param int $nodeId
	 * @param string $userId
	 * @return boolean
	 */
	public static function checkRight($nodeId, $userId = null) {
		return true;
	    $userId || $userId = $_SESSION['username'];
	    if (self::isAdmin3($userId)) {
	        return true;
	    }


	    $objUserNode = new UserNode();
	    $allUserIds = $objUserNode->getAllUserIds($nodeId);
//	    var_dump($userId, $allUserIds);exit;
	    $ret = in_array($userId, $allUserIds);
	    if (!$ret) {
	    	Tool::log('no Permission' . $userId . ' nodeId:' . json_encode($nodeId) . ', $allUserIds:' . json_encode($allUserIds));
	    	return false;
	    } else {
	    	return true;
	    }
	}

	/**
	 * 检查偶家url权限
	 * @author hawklim
	 */
	public static function checkOujUrlRight($url, $appid) {
		if (!$appid || !$url) {
            Response::error(CODE_PARAM_ERROR, null, 'url or appid is not valid');
		}

		$path = trim(parse_url(urldecode($url), PHP_URL_PATH), '/');
		$pathArr = explode('/', $path);
		$moduleName = $pathArr[0];
		if (!$moduleName) {
			Response::error(CODE_PARAM_ERROR, null, 'moduleName is not valid');
		}

        $objCMenuNode = new CMenuNode();
        $nodes = $objCMenuNode->objTable->getAll(compact('appid', 'moduleName'));
        if (!$nodes) {
        	Response::error(CODE_NOT_EXIST_NODE, null, "appid:{$appid}, moduleName:{$moduleName}: node is not exist");
        } else {
			foreach ($nodes as $node) {
				$flag = self::checkRight($node['nodeId']);
				if ($flag) {
					//二次密码验证
					return self::checkAnotherPwd($node['nodeId']);
				}
			}

			return false;
        }

	}
	
	/**
	 * 检查url是否有权限访问
	 * @author benzhan
	 * @param string $url
	 * @param string $userId
	 * @return boolean
	 */
	public static function checkUrlRight($url, $userId = null) {
	    $userId || $userId = $_SESSION['username'];
        $parts = explode('&_nodeId=', $url);
        if (count($parts) == 1) {
            $parts = explode('?_nodeId=', $url);
        }
        
        if (!$parts) {
            Response::error(CODE_PARAM_ERROR, null, 'url is not valid');
        }
        
        $orginUrl = $parts[0];
        $nodeId = $parts[1];
        
        $objUserNode = new UserNode();
        if (!self::checkRight($nodeId, $userId)) {
            Response::error(CODE_NO_PERMITION, null, "{$userId} is not permition to access nodeId:{$nodeId}");
        }
        
        $objCMenuNode = new CMenuNode();
        $node = $objCMenuNode->objTable->getRow(compact('nodeId'));
        
        $parts = explode('#', $node['leftUrl']);
        $leftUrl = $parts[0];
        
        $parts = explode('#', $node['rightUrl']);
        $rightUrl = $parts[0];
        
        $leftIndex = strpos($orginUrl, $leftUrl);
        $rightIndex = strpos($orginUrl, $rightUrl);
        
        if ($leftIndex !== false || $rightIndex !== false) {
        	//二次密码验证
        	self::checkAnotherPwd($node['nodeId']);
            return true;
        } else {
            $debugMsg = "left:{$leftUrl} and right:{$rightUrl} is not match {$parts[0]}; leftIndex:{$leftIndex}, rightIndex:{$rightIndex}";
            Response::error(CODE_NO_PERMITION, null, $debugMsg);
        }
	}

	/**
	 * 校验二次密码
	 * @author hawklim
	 */
	public static function checkAnotherPwd($nodeId, $userId = null) {
        // $objSessionRedis = dwRedis::init('php_session');
        // $sessionKey = "cjms_session:$session_id";
        // $loginInfo = $objSessionRedis->get($sessionKey);
            		
	    $userId || $userId = $_SESSION['username'];
        $objCMenuNode = new CMenuNode();
        $node = $objCMenuNode->objTable->getRow(compact('nodeId'));
        if ($node) {
        	// 无需验证二次密码或已经验证
        	if (!$node['needAnotherPwd'] || $_SESSION['verifyAnotherPwd']) {
        		return true;
        	}        	
        	$objUser = new TableHelper('cUser');
        	$where = array("userId" => $_SESSION['userId'], 'enable' => 1);        	
        	$userinfo = $objUser->getRow($where);
        	if (!$userinfo['anotherPwd']) {
        		Response::error(CODE_NEED_SET_ANOTHER_PWD);        		
        	} else {
        		Response::error(CODE_NEED_CHECK_ANOTHER_PWD);
        	}
        } else {
        	Response::error(CODE_NOT_EXIST_NODE, null, 'the node is not exist');
        }

	}
}


