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
        save : function() {
            var flag = fileUpload.formatResult();
        	if (flag && form.validateForm('#form')) {
        		var url = lib.url + "diyEdit/save";
        		var data = form.getFormData('#form');
        		// data['tableId'] = lib.getParam('tableId');
        		
        		var where = form.getFormData('#priKey');
        		data['_where'] = JSON.stringify(where);

        		lib.post(url, data, function(objResult) {
        			if (objResult.result) {
                        if ($('#editModal').length) {
                            // 坑爹，bootstramp的bug
                            $('body').removeClass('modal-open');
                            $dialog.modal('hide');
                        } else {
                            setTimeout(function () {
                                history.back();
                            }, 1000);
                        }

						$(document).trigger('diy_edit_save', data);
                        console.log("$(document).trigger('diy_edit_save', data); data: ");
                        console.log(data);
        				lib.showTip(objResult.msg);
        			} else {
        				lib.showErrorTip(objResult.msg); 
        			}
        		}, {
                    loading : true
                });
        	}
        },
        add : function() {
            if (!confirm('确定要另存为新的数据吗？')) {
                return false;
            }

            var flag = fileUpload.formatResult();
        	if (flag && form.validateForm('#form')) {
        		var url = lib.url + "diyEdit/add";
        		var data = form.getFormData('#form');

        		lib.post(url, data, function(objResult) {
        			if (objResult.result) {
                        if ($('#editModal').length) {
                            // 坑爹，bootstramp的bug
                            $('body').removeClass('modal-open');
                            $('#editModal').modal('hide');
                        } else {
                            setTimeout(function () {
                                history.back();
                            }, 1000);
                        }

        				// require('js/diy/table.js').loadTable();
                        $(document).trigger('diy_edit_add', data);
                        console.log("$(document).trigger('diy_edit_add', data); data: ");
                        console.log(data);

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
    		    // 这里放在setTimeout是为了让外面的绑定事件先执行
    		    setTimeout(M.save);
            });

    		$('#btn_saveas').on(BDY.click, function() {
                // 这里放在setTimeout是为了让外面的绑定事件先执行
                setTimeout(M.add);
            });

			fileUpload.initDateTime();
            fileUpload.initUpload();
            fileUpload.initRich();
            fileUpload.initAutoComplete();

            lib.setTimeout(function() {
                fileUpload.initCode();
                lib.initSelect2();
            }, 200);
        }
    }
	
	function init() {
        if ($('#editModal').length) {
            $dialog = $('#editModal').modal({
                'backdrop' : 'static',
                'show' : true
            });

            $dialog.find('.modal-dialog').draggable({
                handle: ".modal-header"
            });
        }

		C.init();

        $(document).trigger('diy_edit_save_view');
        console.log("$(document).trigger('diy_edit_save_view'); ");
	}
	
	exports.init = init;
});

