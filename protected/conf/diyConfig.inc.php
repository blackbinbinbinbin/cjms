<?php

/** 操作符号 */
$GLOBALS['diy']['opts'] = array(
    "=" => "等于",
    "!=" => "不等于",
    ">" => "大于",
    ">=" => "大于等于",
    "<" => "小于",
    "<=" => "小于等于",
    "like" => "类似于",
    "like .%" => "开头类似于",
    "like %." => "结尾类似于",
    "in" => "包含于",
    "not in" => "不包含于",
    ":" => "区间于",
);

/** 字段的数据类型 */
$GLOBALS['diy']['fieldTypes'] = array(
    "int" => "int",
    "float" => "float",
    "string" => "string",
    "enum" => "enum",
    "date" => "date",
    "datetime" => "datetime",
    "time" => "time",
    "text" => "text",
    // 自定义类型
    "ip" => "ip",
    "json" => "json",
);

/** 字段的数据类型 */
$GLOBALS['diy']['inputTypes'] = array(
    "text" => "普通输入",
    "textarea" => "文本框",
    "number" => "数字",
    "date" => "日期",
    "datetime" => "时间",
    "time" => "时间(时分)",
    "select" => "下拉框单选",
    "auto_complete" => "自动提示输入",
    "radio" => "单选Radio",
    "checkbox" => "复选框",
    "image" => "图片",
    "audio" => "音频",
    "video" => "视频",
    "rich_textarea" => "富文本框",
    "json" => "json文本框",
    "code_js" => "JS代码文本框",
    "code_php" => "PHP代码文本框",
    "code_html" => "Html代码文本框",
);

/** 字段的数据类型 */
$GLOBALS['diy']['fieldTypeMap'] = array(
    "tinyint" => "int",
    "smallint" => "int",
    "mediumint" => "int",
    "int" => "int",
    'bigint' => "int",

    "decimal" => "float",
    "float" => "float",
    "double" => "float",

    "varchar" => "string",
    "char" => "string",
    
    "date" => "date",
    "time" => "time",
    "datetime" => "datetime",
    "timestamp" => "datetime",
    
    'tinytext' => 'text',
    "text" => "text",
    'mediumtext' => 'text',
    'longtext' => 'text',
    
    'tinyblob' => 'text',
    'mediumblob' => 'text',
    'blob' => 'text',
    'longblob' => 'text',
    
    "enum" => "enum",
    'set' => 'enum'
);


/** 字段的数据类型 */
$GLOBALS['diy']['pageSizes'] = array(
    10,
    20,
    50, 
    100
);
//
//$GLOBALS['diy']['map'] = array(
//    array('desc' => '无字典', 'func' => ''),
//
//    array('desc' => '作者ID->作者名称', 'func' => '{"name":"getAuthorName","keyField":"id","valField":"name"}'),
//    array('desc' => '书ID->书名称', 'func' => '{"name":"getBookName","keyField":"id","valField":"name"}'),
//    array('desc' => '店铺ID->店铺名称', 'func' => '{"name":"getShopName","keyField":"shop_id","valField":"shop_name"}'),
//    array('desc' => '吧ID->吧名称', 'func' => '{"name":"getBarName","keyField":"id","valField":"name"}'),
//
//    array('desc' => '数据库ID->数据库名称', 'func' => '{"name":"getDbName","keyField":"dbId","valField":"sourceDb"}'),
//
//    array('desc' => '应用ID->应用名称', 'func' => '{"name":"getAppName","keyField":"appId","valField":"cName"}'),
//    array('desc' => '国家ID->国家名称', 'func' => '{"name":"getCountry","keyField":"countryId","valField":"countryName"}'),
//    array('desc' => '省份ID->省份名称', 'func' => '{"name":"getProv","keyField":"provinceId","valField":"provinceName"}'),
//    array('desc' => '城市ID->城市名称', 'func' => '{"name":"getCity","keyField":"cityId","valField":"cityName"}'),
//    array('desc' => '运营商ID->运营商名称', 'func' => '{"name":"getIsp","keyField":"ispId","valField":"ispName"}'),
//    array('desc' => 'page->页面名称', 'func' => '{"name":"getPage","keyField":"page","valField":"pageName"}'),
//    array('desc' => 'action->动作名称', 'func' => '{"name":"getAction","keyField":"action","valField":"actionName"}'),
//);

//白名单
$GLOBALS['diy']['whiteList'] = array(
    'admin',
);

//end of script
