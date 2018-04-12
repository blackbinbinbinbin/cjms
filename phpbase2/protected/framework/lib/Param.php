<?php

/**
 * 参数检查的类，用法见文件底部的测试用例 
 * @author benzhan
 */
class Param {
    private static $succ = array('result' => true);

    /**
     * 检查参数
     * @param array $rules <br>
array( <br>
    'appId' => 'int',  //int类型 <br>
	 'owners' => 'array',  //array类型 <br>
    'instanceIds' => 'intArr',  //array类型，元素为int类型 <br>
    'instanceTypes' => 'strArr',  //array类型，元素为string类型 <br>
    'deviceId' => 'int/array',  //int类型或者array类型，最后转化为元素为idArr类型 <br>
    'deviceClass' => 'string/array',  //string类型或者array类型，最后转化为strArr类型 <br>
    'blocks' => array('type' => 'int', 'range' => '(5,10)'), //int类型，> 5，< 10 <br>
    'blocks2' => array('type' => 'int', 'range' => '[5,10]'), //int类型，>= 5，<= 10 <br>
  	'percent' => array('type' => 'float', 'range' => '[5.1,10.9]'), //int类型，>= 5，<= 10 <br>
	'appName' => array('type' => 'string'),  //string类型 <br>
    'appName2' => array('type' => 'string', 'reg' => '[^0-9A-Za-z]', 'len' => '[1,10]', 'nullable' => true),  //string类型，支持正则表达式 <br>
    'default' => array('type' => 'string', 'nullable' => true, 'default' => 'test'),  //使用nullable可以设置默认值
); <br>
     * @param array $args 输入参数
     * @param boolean $exitError 遇到错误是否直接exit
     * @param array $newRule 解析通配符后的规则
     * @return array array("result" => true/false)
     */
    public static function checkParam(array $rules, array &$args, $exitError = true, array &$newRules = []) {
        self::getRule($rules, $args);

        $rules = self::makeRules($rules, array_keys($args));
	    $newRules = $rules;

	    foreach ($rules as $key => $rule) {
            $result = self::checkRule($rule, $args, $key);
            
            if (!$result['result']) {
                if ($exitError) {
                    $msg = $result["msg"] . "(url: {$_SERVER['REQUEST_URI']})";
                    Response::error(CODE_PARAM_ERROR, null, $msg);
                } else {
                    return $result;
                }
            }
        }
                
        return self::$succ;
    }
    
    /**
     * 效果同checkParam一样，但增加了删除多余字段
     * @author benzhan
     * @param array $rules
     * @param array $args
     * @param string $exitError
     * @return Ambigous <multitype:, multitype:boolean , unknown>
     */
    public static function checkParam2(array $rules, array &$args, $exitError = true) {
	    $newRules = [];
	    $flag = self::checkParam($rules, $args, $exitError, $newRules);
	    if (!empty($newRules)) {
		    $rules = $newRules;
	    }

	    // 删除多余字段
        foreach ($args as $key => $value) {
            if (!isset($rules[$key])) {
                unset($args[$key]);
            }
        }

        return $flag;
    }
    /**
     * rules支持*通配符key
     * @param  array $rules 原配置规则
     * @param  array $keys  用户传来的key
     * @return array        新的参数规则
     */
    private static function makeRules(array $rules, array $keys) {

        $ruleKeys = array_filter(array_keys($rules), function ($k) {
            return mb_strpos($k, '*') !== false;
        });

        foreach ($ruleKeys as $k) {
            $rule = $rules[$k];
            $pattern = '/' .str_replace('*', '\\S+', $k) . '/';

            $filtedKey = array_filter($keys, function ($k) use ($pattern){
                return preg_match($pattern, $k, $matche) === 1;
            });

            if (!empty($filtedKey)) {
                unset($rules[$k]);
                foreach ($filtedKey as $v) {
                    $rules[$v] = $rule;
                }
            }
        }

        return $rules;
    }
    
    public static function getRule(array $rules, array &$args) {
        if ($args["__getRules"]) {
            $GLOBALS['__getRules'] = false;
//            $params = $args['__params'];
            $params = array();
            
            $params['rules'] = $rules;
            foreach ($rules as $name => $rule) {
                $value = $params['params'][$name];
                if (is_array($rule)) {
                    $value['type'] = $rule['type'];
                    $value['type'] || $value['type'] = $rule[0];
                    $value['rule'] = self::genDoc($rule);
                } else {
                    $value['type'] = $rule;
                }
                $params['params'][$name] = $value;
            }

            $params['ret'] = DocController::getRetData();

            $response = array(
                'result' => 1,
                'code' => CODE_SUCCESS,
                'data' => $params
            );

            $json = json_encode($response);
            Response::exitMsg($json);
        }
    }
    
    private static function genDoc($rule) {
        $str = '';

        if ($rule['nullable']) {
            $str .= '【可为null】';
        }
        
        if ($rule['emptyable']) {
            $str .= '【可为空值】';
        }
        
        if ($rule['len']) {
            $str .= "【长度范围：{$rule['len']}】";
        }
        
        if ($rule['range']) {
            $str .= "【取值范围：{$rule['range']}】";
        }
        
        if ($rule['reg']) {
            $str .= "【正则：{$rule['reg']}】";
        }
        
        if ($rule['enum']) {
            $str .= '【值枚举：' . join(",", $rule['enum']) . '】';
        }

        if ($rule['default']) {
            $str .= "【默认值：{$rule['default']}】";
        }
        
        return $str;
    }
    
    private static function checkRule($rule, &$args, $key) {
        if (!is_array($rule)) {
            $rule = array('type' => $rule);
        } 
        
        $type = $rule['type'];
        $type || $type = $rule[0];
        
        switch ($type) {
            case "int/array":
                if ($args[$key] && !is_array($args[$key])) {
                    $args[$key] = (array) $args[$key];
                }
                
                $result = self::checkIntArr($rule, $args, $key);
                break;
            case "string/array":
                if ($args[$key] && !is_array($args[$key])) {
                    $args[$key] = (array) $args[$key];
                }
                
                $result = self::checkStrArr($rule, $args, $key);
                break;
            default:
                $type || $type = 'default';
                $funcName = "check" . ucfirst($type);
                $result = self::$funcName($rule, $args, $key);
                break;
        }
        
        return $result;
    }
    
    private static function error($msg) {
        return array('result' => false, 'msg' => $msg);
    }
    
    /**
     * 检查nullable
     * @author benzhan
     */
    private static function checkBase($rule, &$args, $key) {
        $value = $args[$key];
        
        //判断是否可空
        if ($rule['nullable'] && !$value) {
            isset($rule['default']) && $args[$key] = $rule['default'];
            return self::$succ + array('nullable' => true);
        }

        //判断是否是可为0或空字符串
        if (($rule['emptyable']) && !$value) {
            return self::$succ + array('emptyable' => true);
        }
        
        //判断是否在enum中
        if ($rule['enum'] && !in_array($args[$key], $rule['enum'])) {
            return self::error("{$key}:{$args[$key]} is not in " . var_export($rule['enum'], true));
        }
        
        //判断是否为空
        if (!$value) {
            return self::error($key . ' is null or empty!');
        }
                
        return self::$succ;
    }
    
    private static function checkRange($rule, &$args, $key) {
        $range = $rule['range'];
        if ($range) {
            $range = trim($range);
            $ranges = explode(',', $range);
            
            $errMsg = "{$key} is not in range {$range}";
            $from = trim(substr($ranges[0], 1));
            
            if ($from !== '-' && $from !== '~') {
                $flag = $ranges[0][0];
                if ($flag == '[' && $from !== '-' && $args[$key] < $from) {
                    return self::error($errMsg);
                } else if ($flag === '(' && $args[$key] <= $from) {
                    return self::error($errMsg);
                }
            }

            $to = trim(substr($ranges[1], 0, -1));
            if ($to !== '+' && $to !== '~') {
                $flag = substr($ranges[1], -1);
                if ($flag === ']' && $args[$key] > $to) {
                    return self::error($errMsg);
                } else if ($flag === ')' && $args[$key] >= $to) {
                    return self::error($errMsg);
                }
            }

        }
        
        return self::$succ;
    }
    
    private static function checkDefault($rule, &$args, $key) {
        //int类型默认允许为0
        isset($rule['emptyable']) || $rule['emptyable'] = true;
        $result = self::checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        $result = self::checkRange($rule, $args, $key);
        if (!$result['result']) {
            return $result;
        }
        
        return self::$succ;
    }
        
    private static function checkInt($rule, &$args, $key) {
        //int类型默认运行为0
        isset($rule['emptyable']) || $rule['emptyable'] = true;
        $result = self::checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        $copyId = $args[$key];
        $id = (int) $args[$key];
        if ($copyId != $id || !is_numeric($copyId) || strlen($copyId) != strlen($id)) {
            return self::error("{$key}:{$args[$key]} is not int!");
        }

        $args[$key] = $id;
        $result = self::checkRange($rule, $args, $key);
        if (!$result['result']) {
            return $result;
        }
        
        return self::$succ;
    }
    
    private static function checkIp($rule, &$args, $key) {
        $result = self::checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
                
        $ipName = $args[$key];
        $pattern = '/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])'
             . '\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)'
             . '\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)'
             . '\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/';
        if (!preg_match($pattern, $ipName)) {
            return self::error("{$key}:{$ipName} is not valid ip format!");
        }
        
        return self::$succ;
    }
    
    private static function checkFloat($rule, &$args, $key) {
        $result = self::checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        // $copyId = $args[$key];
        $id = (float) $args[$key];

        if (!is_float($id)) {
            return self::error("{$key}:{$args[$key]} is not float!");
        }

        // if (strlen($copyId) != strlen($id) && strlen($id) < 8) {
        //     return self::error("{$key}:{$args[$key]} is not float!");
        // }

        $args[$key] = $id;
        $result = self::checkRange($rule, $args, $key);
        if (!$result['result']) {
            return $result;
        }
        
        return self::$succ;
    }
    
    private static function checkLen($rule, $args, $key) {
        $len = $rule['len'];
        if ($len) {
            $len = trim($len);
            $ranges = explode(',', $len);
        
            $errMsg = "{$key} is not valid. len must in {$len}";
            $from = (float) trim(substr($ranges[0], 1));
        
            $strLen = strlen($args[$key]);
            if ($from != '-' && $from != '~') {
                $flag = $ranges[0][0];
                if ($flag == '[' && $from != '-' && $strLen < $from) {
                    return self::error($errMsg);
                } else if ($flag == '(' && $strLen <= $from) {
                    return self::error($errMsg);
                }
            }
        
            $to = (float) trim(substr($ranges[1], 0, -1));
            if ($to != '+' && $to != '~') {
                $flag = substr($ranges[1], -1);
                if ($flag == ']' && $strLen > $to) {
                    return self::error($errMsg);
                } else if ($flag == ')' && $strLen >= $to) {
                    return self::error($errMsg);
                }
            }
        }
        
        return self::$succ;
    }
    
    private static function checkString($rule, &$args, $key) {    
        if ($args[$key] !== null) {
            $args[$key] = (string) $args[$key];
            $args[$key] = trim($args[$key]);
        }

        $result = self::checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        $result = self::checkLen($rule, $args, $key);
        if (!$result['result']) {
            return $result;
        }
        
        if ($rule['reg']) {
            if ($rule['reg'][0] != '/') {
                $rule['reg'] = '/' . $rule['reg'] . '/';
            } 

            if (!preg_match($rule['reg'], $args[$key])) {
                return self::error($key . ' preg_match error! The reg rule is:' . $rule['reg']);
            }
        }
        
        return self::$succ;
    }
    
    private static function checkArray($rule, &$args, $key) {
        $result = self::checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        if (!is_array($args[$key])) {
            return self::error($key . ' is not array!');
        } 
        
        if ($rule['elem']) {
            foreach ($args[$key] as $i => $value) {
                $result = self::checkRule($rule['elem'], $args[$key], $i);
                if (!$result['result']) { 
                    $result['msg'] .= " => [parent:{$key}]"; 
                    return $result; 
                }
            } 
        }
        
        return self::$succ;
    }
    
    private static function checkIntArr($rule, &$args, $key) {
        return self::_checkRuleArr($rule, $args, $key, 'int');
    }
    
    private static function checkStrArr($rule, &$args, $key) {
        return self::_checkRuleArr($rule, $args, $key, 'string');
    }
    
    private static function checkIpArr($rule, &$args, $key) {
        return self::_checkRuleArr($rule, $args, $key, 'ip');
    }
    
    private static function _checkRuleArr($rule, &$args, $key, $type = 'int') {
        $result = self::checkArray($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) {
            return $result;
        }
        
        foreach ($args[$key] as $i => $str) {
            $result = self::checkRule($type, $args[$key], $i);
            if (!$result['result']) { 
                $result['msg'] .= " => [parent:{$key}]"; 
                return $result; 
            }
        }
                
        return self::$succ;
    }
    
    
    private static function checkObject($rule, &$args, $key) {
        $result = self::checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) { return $result; }
        if (!$rule['items']) { return self::$succ; } 
        
        foreach ($rule['items'] as $k => $r) {
            $result = self::checkRule($r, $args[$key], $k);
            if (!$result['result']) { 
                $result['msg'] .= " => [parent:{$key}]"; 
                return $result; 
            }
        }
                
        return self::$succ;
    }
    
    private static function checkJson($rule, &$args, $key) {
        $result = self::checkBase($rule, $args, $key);
        if (!$result['result'] || $result['nullable']) { return $result; }

        $value = json_decode($args[$key], true);
        if (json_last_error() != JSON_ERROR_NONE) {
            return self::error("{$key}:{$args[$key]} is not json!");
        }

        return self::$succ;
    }
}



//end of script
