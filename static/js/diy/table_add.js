define(function(require, exports, module) {
	var lib = require('lib');
	require('bootstrap');
    require('jquery');
	require('jquery-ui');
	require('datetimepicker');

    var fileUpload = require('js/libs/fileUpload.js');
    var form = require('form');
    var $dialog;
    
    var M = {
        add : function() {
            var flag = fileUpload.formatResult();
        	if (flag && form.validateForm('#form')) {
        		var url = lib.url + "diyEdit/add";
        		var data = form.getFormData('#form');
        		// data['tableId'] = lib.getParam('tableId');
        		lib.post(url, data, function(objResult) {
        			if (objResult.result) {

						// 触发table_add事件
						$(document).trigger('diy_edit_add');
						console.log("$(document).trigger('diy_edit_add');");
        				lib.showTip(objResult.msg);

                        if ($('#addModal').length) {
                            // 坑爹，bootstramp的bug
                            $('body').removeClass('modal-open');
                            $dialog.modal('hide');
                        } else {
                            setTimeout(function () {
                                history.back();
                            }, 1000);
                        }
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
    		$('#btn_save').on(BDY.click, M.add);

            fileUpload.initDateTime();
            fileUpload.initUpload();
            fileUpload.initRich();
            fileUpload.initAutoComplete();

            lib.setTimeout(function() {
                fileUpload.initCode();
                lib.initSelect2();
            }, 200);
    	},

    }
	
	function init() {
        if ($('#addModal').length) {
            $dialog = $('#addModal').modal({
                'backdrop' : 'static',
                'show' : true
            });

            $dialog.find('.modal-dialog').draggable({
                handle: ".modal-header"
            });
        }

		C.init();

        $(document).trigger('diy_edit_add_view');
        console.log("$(document).trigger('diy_edit_add_view');");
	}
	
	exports.init = init;
});

