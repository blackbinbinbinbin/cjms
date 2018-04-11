define(function(require, exports, module) {
	var lib = require('lib'), form = require('form');
	exports.init = init;
	
	var M = {
		saveAnotherPwd : function() {
			if (form.validateForm('#dataForm')) {
				var url = lib.url + "user/saveAnotherPwd";
			    var data = {};
			    data.anotherPwd = $('#another_pwd').val();
			    lib.post(url, data, function(objResult) {
		            if (objResult.result) {
		            	lib.showTip('设置成功，正在返回...');
			            setTimeout(function() {
			            	top.location.reload();
	                    }, 1000);
		            } else {
		            	lib.showTip(objResult.msg);
		            }
			    });
			}
		},

		checkAnotherPwd : function() {
			if (form.validateForm('#dataForm')) {
				var url = lib.url + "user/verifyAnotherPwd";
			    var data = {};
			    data.anotherPwd = $('#another_pwd').val();
			    lib.post(url, data, function(objResult) {
		            if (objResult.result) {
		            	lib.showTip('检验成功，正在返回...');
			            setTimeout(function() {
			            	top.location.reload();
	                    }, 1000);
		            } else {
		            	lib.showTip(objResult.msg);
		            }
			    });
			}
		},

	};
	
	var C = {
        init : function() {
        	$('#saveAnotherPwd').on(BDY.click, M.saveAnotherPwd);
        	$('#checkAnotherPwd').on(BDY.click, M.checkAnotherPwd);
        }
	}
	
	function init() {
		C.init();
	}
	
});