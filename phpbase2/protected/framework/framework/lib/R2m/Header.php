<?php

define('R2M_VERSION', 3);

// 请求的命令
define('CMD_AUTH', 1);
define('CMD_ADD_OBJECT', 2);
define('CMD_ADD_OBJECT_NX', 3);
define('CMD_GET_ROW', 4);
define('CMD_GET_ALL', 5);
define('CMD_REPLACE_OBJECT', 6);
define('CMD_UPDATE_OBJECT', 7);
define('CMD_UPDATE_OBJECTS', 8);
define('CMD_DEL_OBJECT', 9);
define('CMD_DEL_OBJECTS', 10);
define('CMD_DEL_ROW_CACHE', 11);
define('CMD_DEL_LIST_CACHE', 12);
define('CMD_SET_DEBUG', 13);
define('CMD_DEBUG_MSG', 14);
define('CMD_GET_INSERT_ID', 15);
define('CMD_ADD_OBJECTS', 16);
define('CMD_ADD_OBJECTS2', 17);
define('CMD_GET_ALL_SQL', 18);


// 响应的命令
define('RESULT_NO_AUTHOR', 10000);
define('RESULT_SUCCESS', 10001);
define('RESULT_ERROR', 10002);

// 命令类型
define('CMD_TYPE_REQUEST', 1);
define('CMD_TYPE_RESPONSE', 2);

define('CODE_TYPE_BINARY', 1);
define('CODE_TYPE_UTF8', 2);

define('CONTENT_TYPE_TEXT', 1);
define('CONTENT_TYPE_JSON', 2);


