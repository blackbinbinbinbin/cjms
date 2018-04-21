<?php
/**
 * Created by PhpStorm.
 * @author benzhan
 * Date: 15/11/11
 * Time: 下午4:30
 */
require_once realpath(dirname(__FILE__)) . '/../common.php';
require_once realpath(dirname(__FILE__)) . '/common_script.php';

$objCmsTable = new TableHelper('Cmdb3Table', 'Report');
$objCmsEdit = new TableHelper('Cmdb3Edit', 'Report');
$objCmsField = new TableHelper('Cmdb3Field', 'Report');
$objCmsMap = new TableHelper('Cmdb3Map', 'Report');
$objTableMeta = new TableHelper('Cmdb3TableMeta', 'Report');
// $allDelTable = $objCmsTable->getAll([], ['_where' => '`nameDb` not in ("Report", "Web", "ms")']);
// foreach ($allDelTable as $key => $table) {
//     $tableId = $table['tableId'];
//     $sourceTable = $table['sourceTable'];
//     $objCmsTable->delObject(['tableId' => $tableId]);
//     $objCmsEdit->delObject(['tableId' => $tableId]);
//     $objCmsField->delObject(['tableId' => $tableId]);
//     $objTableMeta->delObject(['tableId' => $tableId]);
//     $objCmsMap->delObject(['sourceTable' => $sourceTable]);
//     echo "del: {$sourceTable}\r\n";
// }