<?php

class Doc {

    /**
     * 寻找类名
     * @author benzhan
     * @param string $filePath 文件路径
     */
    private function getClassName($filePath) {
        $handle = fopen($filePath, "r");
        if (!$handle) { return; }
        while (!feof($handle)) {
            $buffer = trim(fgets($handle, 1024));
            if (!$buffer || strpos($buffer, 'class ') === false) { continue; }
    
            $arr = explode(' ', $buffer);
            foreach ($arr as $i => $value) {
                if ($value != 'class') { contine; }
    
                fclose($handle);
                $className = $arr[$i + 1];
                $pos = strpos($className, '{');
                if ($pos) {
                    return substr($className, 0, $pos);
                } else {
                    return $className;
                }
            }
        }
    
        fclose($handle);
        return false;
    }
    
    /**
     * 获取类的信息
     * @author benzhan
     * @param sting $className 类名
     */
    public function getClassInfo($className) {
        // 包含类进来
        //$obj = new $className();
    
        $objClass = new ReflectionClass($className);
        $funcs = $objClass->getMethods(ReflectionMethod::IS_PUBLIC);
    
        $classComment = $objClass->getDocComment();
        $classInfo = array();
        $classInfo['desc'] = $this->getKeyValue('desc', $classComment);
        $classInfo['desc'] || $classInfo['desc'] = $this->getKeyValue('', $classComment);
        $classInfo['author'] = $this->getKeyValue('author', $classComment);

        if ($this->hasKey('ignore', $classComment)) {
            return false;
        }

        foreach ($funcs as $func) {
            $objFunc = new ReflectionMethod($func->class, $func->name);
            $funcComment = $objFunc->getDocComment();
            $parameter = $objFunc->getParameters();
            if (!$funcComment) { continue; }

            if ($this->hasKey('ignore', $funcComment)) {
                continue;
            }

            $author = $this->getKeyValue('author', $funcComment);
    
            $desc = $this->getKeyValue('desc', $funcComment);
            $desc || $desc = $this->getKeyValue('', $funcComment);
            $desc = str_replace("\n", '<br>', $desc);
    
            if (strpos($func->name, 'action') === 0) {
                $shortFuncName = str_replace('action', '', $func->name); 
                $shortFuncName = lcfirst($shortFuncName);
                $classInfo['funcs'][$shortFuncName] = compact('desc', 'parameter', 'author');
            }
        }
    
        return $classInfo;
    }
    
    public function getFuncInfo($className, $funcName) {
        $objClass = new ReflectionClass($className);
        $objFunc = $objClass->getMethod($funcName);
    
        //$objFunc = new ReflectionMethod($func->class, $func->name);
        $funcComment = $objFunc->getDocComment();
        if (!$funcComment) {
            return;
        }
    
        $author = $this->getKeyValue('author', $funcComment);
    
        $funcDesc = $this->getKeyValue('desc', $funcComment);
        $funcDesc || $funcDesc = $this->getKeyValue('', $funcComment);
        $funcDesc = str_replace("\n", '<>', $funcDesc);
    
        $params = $this->getKeyValue('param', $funcComment, true);
        $tData = array();
        foreach ($params as $i => $param) {
            $parts = explode(" ", $param);
            $datas = array();
            foreach ($parts as $part) {
                if ($part) {
                    $datas[] = $part;
                }
            }
            $parts = $datas;
            
            $type = array_shift($parts);
            $name = array_shift($parts);
            if (!$name) {
                $name = $type;
                $type = null;
            }
            $desc = join(" ", $parts);
    
            $tData[$name] = compact('type', 'name', 'desc');
        }
        $params = $tData;
    
        return compact('desc', 'params', 'author');
    }
    
    private function getKeyValue($key, $comment, $multi = false) {
        $comment = trim($comment);
        $key = $key ? "@$key" : $key;
        $pattern = "/{$key} ([^@\\/]+)/";
        $matches = array();
        if ($multi) {
            $flag = preg_match_all($pattern, $comment, $matches);
            return $this->formatComment($matches[1]);
        } else {
            $flag = preg_match($pattern, $comment, $matches);
            return $this->formatComment($matches[1]);
        }
    }

    private function hasKey($key, $comment) {
        $key = $key ? "@$key" : $key;
        $pattern = "/{$key}/";
        $matches = array();
        return preg_match_all($pattern, $comment, $matches);
    }
    
    private function formatComment($comments) {
        if (is_array($comments)) {
            foreach ($comments as $key => $comment) {
                $comments[$key] = trim(str_replace("*", "", $comment));
            }
            return $comments;
        } else {
            return trim(str_replace("*", "", $comments));
        }
    }
     
    /**
     * 生成类信息
     * @author benzhan
     * @param string $path 路径数组
     */
    public function getClassInfos($path) {
        $fileNames = scandir($path);
    
        $classInfos = array();
        foreach ($fileNames as $fileName) {
            if ($fileName == "." || $fileName == "..") {
                continue;
            }
    
            $filePath = $path . DIRECTORY_SEPARATOR . $fileName;
            if (is_dir($filePath)) {
                $classInfos += $this->getClassInfos($filePath);
            } else {
                if (strpos($fileName, '.php') === false) { continue; }
    
                $className = $this->getClassName($filePath);
                if (!$className) { continue; }
    
                $classInfo = $this->getClassInfo($className, $filePath);
                if (!$classInfo || !$classInfo['funcs']) { continue; }
    
                $api = str_replace("_", "/", $className);
                $api = str_replace('Controller', '', $api);
                $api = lcfirst($api);
                $classInfos[$api] = $classInfo;
            }
        }
    
        return $classInfos;
    }
    
}

