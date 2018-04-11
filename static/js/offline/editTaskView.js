define(function(require, exports, module) {
	var lib = require('lib');
	require('bootstrap');
    require('jquery');

    var form = require('form');

    var M = {
        saveTask : function(saveAs) {
        	if (form.validateForm('#form')) {
        		var url = lib.url + "offline/saveTask";
        		var data = form.getFormData('#form');
        		if (saveAs) {
        			data.taskId = 0;
				}

        		lib.post(url, data, function(objResult) {
        			if (objResult.result) {
        				lib.showTip(objResult.msg);
        			} else {
        				lib.showErrorTip(objResult.msg); 
        			}
        		}, {
                    loading : true
                });
        	}
        }
    };
    
    var C = {
    	init : function() {
    		$('#btn_save').on(BDY.click, function() {
                M.saveTask(false);
			});

    		$('#btn_saveas').on(BDY.click, function() {
    			if (confirm('确认要另存为数据吗？')) {
                    M.saveTask(true);
				}
			});
    	}
    };
	
	function init() {
		C.init();
	}
	
	exports.init = init;
});

