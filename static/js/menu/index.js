define(function(require, exports, module) {
	var lib = require('js/libs/library.js');
    require('bootstrap');
	
	exports.init = init;
	
	var M = {
		getChildsByPId : function(tree, sender) {
			var url = lib.url + "menu/getChildsByPId";
		    var data = { nodeId : sender.data.nodeId, showHiddenNode : 1 };
		    lib.post(url, data, function(objResult) {
		    	if (objResult.result) {
	                tree.getNode(sender.index).eleSub.innerHTML = "";
	                tree.transDataing = true;
	                tree.transData(sender.index, objResult.data);
	                tree.transDataing = false;
	            } else {
	                lib.showTip(objResult.msg);
	            }
		    });
		},
		saveNode : function() {
			var url = lib.url + "menu/saveNode";
		    var data = {};
		    data.nodeId = $('#nodeId').val().trim();
		    data.oldNodeId = $('#oldNodeId').val().trim();
		    data.nodeName = $('#node_name').val().trim();
		    data.leftUrl = $('#leftUrl').val().trim();
		    data.rightUrl = $('#rightUrl').val().trim();
		    data.userIds = $('#userIds').val().trim();
		    data.appid = $('#appid').val().trim();
		    data.moduleName = $('#moduleName').val().trim();
            data.needAnotherPwd = $('[name=needAnotherPwd]:checked').val().trim();
            data.display = $('[name=display]:checked').val().trim();
            data.openNewWindow = $('[name=openNewWindow]:checked').val().trim();
		    data.domain = $('#domain').val().trim();

		    lib.post(url, data, function(objResult) {
	            if (objResult.result) {
	                objTree.curNode.data = data;
	                objTree.curNode.setText(data.nodeName);
	                objTree.curNode.setValue(data.nodeId);
	                lib.showTip(objResult.msg);
	            } else {
	                lib.showErrorTip(objResult.msg);
                }
		    }, {
		        loading : true
            });
		},
		addNode : function(tree, sender) {
			var url = lib.url + "menu/addNode";
		    var data = {};
		    data.parentNodeId = sender.parent.data && sender.parent.data.nodeId || 0;
		    data.nodeName = sender.text;
		    data.leftUrl = data.rightUrl = "";
		    
		    tree.showInfo('正在添加节点...', null, 5000);
		    // 把焦点放在新增的节点上
		    tree.focusNode(sender.index);
	        
		    lib.post(url, data, function(objResult) {
	            if (objResult.result) {
	                data.nodeId = objResult.data;
	                sender.data = data;
	                sender.value = data.nodeId;
	                // 为了赋值
	                sender.data.node_name = data.nodeName;
	                sender.data.oldNodeId = data.nodeId;
					sender.data.userIds = '';
	                lib.template(sender.data, '.panel-body form');
	            } else {
	            	tree.clientRemoveNode(sender.index, false);
	            }
	            
	            lib.showTip(objResult.msg);
		    });
		    
		},
		deleteNode : function(tree, sender) {
	        var url = lib.url + "menu/deleteNode";
	        var data = {};
	        data.nodeId = sender.data.nodeId;
			
	        lib.post(url, data, function(objResult) {
	            lib.showTip(objResult.msg);
	        });
		},
		syncPos : function(json) {
			var url = lib.url + "menu/syncPos";
	        var data = {};
	        data.json = json;
			
	        lib.post(url, data, function(objResult) {
	            lib.showTip(objResult.msg);
	        });
		},
        syncToProduct : function() {
            var url = lib.url + "menu/syncToProduct";
            var data = {};
            data.nodeId = $('#nodeId').val().trim();

            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    lib.showTip(objResult.msg);
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            });
        }
	};
	
	var C = {
        init : function() {
        	$('#saveNode').on(BDY.click, M.saveNode);
        	$('#syncPos').on(BDY.click, syncPos);
        	$('#syncToProduct').on(BDY.click, M.syncToProduct);
        }
	}
	
	var V = {
		init : function(tempData) {
			var treeCfg = {
			    basePath : SITE_URL + 'static/js/libs/skytree/',
			    singleExpand : false,
			    hintBeforeDelete : false,
			    lang : 'zh-cn',
			    expandLevel : 2,
			    theme : 'Default',
			    autoCheck : true,
			    allowContextMenu : true,
			    allowDragDrop : true,
			    allowFocus : true,
			    readOnly : false,
			    tempData : tempData
			};

		    objTree = new SkyTree('objTree', 'tree');
		    objTree.beforeExpandNode = expandNode;
		    objTree.beforeinsert = beforeinsert;
		    objTree.afterinsert = afterinsert;
		    objTree.beforedelete = beforedelete;
		    objTree.beforeedit = beforeedit;
		    
		    objTree.transDataing = true;
		    objTree.initialize(treeCfg);
		    objTree.transDataing = false;
		}
	}

	/** 展开右键节点*/
	function expandNode(tree, sender) {
	    if (tree.getNode(sender.index).firstChild) {
	        return false;
	    }
	    
	    M.getChildsByPId(tree, sender);
	}
	
    function beforeinsert(tree, sender) {
    	if (tree.transDataing) {
    		return;
    	}
    	
        if (sender.data && sender.data.childNum > 0
                && !sender.firstChild) {
            alert('添加子节点时，先展开该节点！');
            tree.eventCanceled = true;
            return false;
        }
    }

    function afterinsert(tree, sender) {
        sender.onfocus = function(sender) {
            if (!sender.data) { return; }
            
            sender.data.node_name = sender.data.nodeName;
            sender.data.oldNodeId = sender.data.nodeId;
            lib.template(sender.data, '.panel-body');
        };

        // 如果不是手动添加的节点，则无需向服务器发送添加节点的请求
        if (!tree.transDataing) {
            M.addNode(tree, sender);
        }
    }

    function beforedelete(tree, sender) {
        // 判断该节点是否存在兄弟节点
        if (sender.firstChild && !confirm('该节点下有子节点，确定都删除？') || !confirm('确定要删除节点？')) {
            tree.eventCanceled = true;
            return false;
        }
        
        M.deleteNode(tree, sender);
    }
    
    function beforeedit(tree, sender) {
        tree.editEventCanceled = true;
    }
    
    function syncPos() {
        var data = {};
        var child = objTree.rootNode.firstChild;
        var json = [];

        while (child) {
            while (child) {
            	var childJson = getChildJson(child);
            	childJson && json.push(childJson);
                if (child.firstChild) {
                    child = child.firstChild;
                } else {
                    break;
                }
            }

            if (child.nextSibling) {
                child = child.nextSibling;
            } else {
                while (child.parent) {
                    if (child.parent.nextSibling) {
                        child = child.parent.nextSibling;
                        break;
                    } else {
                        child = child.parent.value && child.parent;
                    }
                }
            }
        }

        json = '[' + json.join(',') + ']';
        M.syncPos(json);
    };

    function getChildJson(node) {
    	if (!node.value) {
    		return '';
    	}
    	
        var parent = node.parent || node;
        var json = '{';
        json += '"parentNodeId":' + (parent.value || 0) + ',';
        json += '"nodeId":' + node.value + ',';

        var pos = 1;
        while (node.previousSibling) {
            node = node.previousSibling;
            pos++;
        }
        json += '"sortPos":' + pos + '}';
        return json;
    }
    
	
	C.init();
	
	function init(tempData) {
		V.init(tempData);
	}
	
});