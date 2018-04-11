define(function(require, exports, module) {
	var lib = require('js/libs/library.js');
	
	exports.init = init;
	
	var M = {
		getChildsByPId : function(tree, sender) {
			var url = lib.url + "menu/getChildsByPId";
		    var data = { nodeId : sender.data.nodeId };
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

        checkAnotherPwd : function(nodeId) {
            var url = lib.url + "default/checkAnotherPwd";
            var data = {};
            data.nodeId = nodeId;
            lib.post(url, data, function(objResult) {
                if (objResult.code == -1000) {
                   $('#main', parent.document).attr('src', '/user/setAnotherPwd');
                } else if (objResult.code == -1001) {
                   $('#main', parent.document).attr('src', '/user/checkAnotherPwd');
                } else if(objResult.code != 0) {
                    lib.showTip(objResult.msg);
                }
            });

        }		
	};
	
	var C = {
        init : function() {

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
			    allowContextMenu : false,
			    readOnly : true,
			    allowDragDrop : false,
			    allowFocus : true,
			    tempData : tempData
			};

		    objTree1 = new SkyTree('objTree1', 'tree');
		    objTree1.afterinsert = setOnFocus;
		    objTree1.beforeExpandNode = expandNode;
		    objTree1.initialize(treeCfg);

			var nodeId = lib.getParam('nodeId');
			if (nodeId) {
				var idx = objTree1.getIdxByValue(nodeId);
				objTree1.expandToNode(idx);
			}
		}
	}
	
	function formatUrl(url, nodeId) {
		if (url) {
        	var postfix = url.indexOf('?') >= 0 ? '&' : '?';
        	var index = url.indexOf('#');
        	if (index >= 0) {
        		var part1 = url.substr(0, index);
        		var part2 = url.substr(index + 1);
        		url = part1 + postfix + "_nodeId=" + nodeId + '#' + part2;
        	} else {
        		url += postfix + "_nodeId=" + nodeId;
        	}
        }
		
		return url;
	}
	
	function setOnFocus(tree, sender) {
	    sender.onfocus = function(sender){
	    	if (!sender.data) {
	    		return;
	    	}
	    	
	        var leftUrl = formatUrl(sender.data.leftUrl, sender.data.nodeId);
	        if (leftUrl) {
	        	$('#tree', parent.document).attr('src', leftUrl);
	        }
	        
            // 校验登录、二次密码
            M.checkAnotherPwd(sender.data.nodeId);	        

	        var rightUrl = formatUrl(sender.data.rightUrl, sender.data.nodeId);
            rightUrl = rightUrl || '/default/menuList?nodeId=' + sender.data.nodeId;
	        if (parseInt(sender.data.openNewWindow)) {
	        	window.open(rightUrl);
			} else {
                $('#main', parent.document).attr('src', rightUrl);
			}

            parent.seajs.use('js/index.js', function(page) {
	        	page.setNodeId(sender.data.nodeId);
	        });
	    }
	}

	/** 展开右键节点*/
	function expandNode(tree, sender) {
	    if (tree.getNode(sender.index).firstChild) {
	        return false;
	    }
	    
	    M.getChildsByPId(tree, sender);
	}
	
	C.init();
	
	function init(tempData) {
		V.init(tempData);
	}
	
});