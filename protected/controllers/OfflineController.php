<?php


class OfflineController extends Controller {

    private $_insertTypes = [
        'update',
        'replace',
        'only_update',
        'only_update_multi',
        'only_insert_nx',
    ];

    private $_staticTypes = [
        1 => '递增方式(id:1)',
        2 => '覆盖方式(id:2)',
    ];

    private function displayView() {
        $this->tpl->assign('nameDbs', array_keys($GLOBALS['dbInfo']));
        $this->tpl->assign('insertTypes', $this->_insertTypes);
        $this->tpl->assign('staticTypes', $this->_staticTypes);
        $this->tpl->display('offline/editTaskView');
    }

    public function actionAddTaskView() {
        $this->displayView();
    }

    public function actionEditTaskView($args) {
        $rules = [
            'taskId' => 'int'
        ];
        Param::checkParam2($rules, $args);

        $objCmdb3Task = new TableHelper('Cmdb3Task', 'Report');
        $task = $objCmdb3Task->getRow($args);

        $this->tpl->assign('task', $task);
        $this->displayView();
    }

    /**
     * 添加离线任务
     * @author benzhan
     */
    public function actionSaveTask($args) {
        $rules = [
            'taskId' => ['int', 'nullable' => true],
            'taskName' => 'string',
            'sourceUrl' => 'string',
            'toNameDb' => 'string',
            'staticType' => 'int',
            'insertType' => 'string',
            'toTable' => 'string',
            'toTableCallback' => ['string', 'nullable' => true],
            'toTableSql' => ['string', 'nullable' => true],
            'timeField' => ['string', 'nullable' => true],
            'timeInterval' => 'int',
            'execInterval' => 'int',
            'execDelay' => 'int',
            'fieldMap' => 'json',
            'enable' => 'int',
            'redoConfig' => ['json', 'nullable' => true],
        ];

        Param::checkParam2($rules, $args);
        $sourceUrl = $args['sourceUrl'];
        $sourceArgs = str_replace('#!', '&', $sourceUrl);
        $parts = explode('?', $sourceArgs);
        $sourceArgs = end($parts);
        parse_str($sourceArgs, $parts);
        $relaTableId = $parts['tableId'];

        $args['authorName'] = $_COOKIE['username'];
        $args['lastModifyTime'] = date('Y-m-d H:i:s');

        $args += compact('sourceUrl', 'sourceArgs', 'relaTableId');
        $objCmdb3Task = new TableHelper('Cmdb3Task', 'Report');
        $taskId = arrayPop($args, 'taskId');
        if ($taskId) {
            $objCmdb3Task->updateObject($args, compact('taskId'));
        } else {
            $args['createTime'] = date('Y-m-d H:i:s');
            $objCmdb3Task->addObject($args);
        }
    }

}