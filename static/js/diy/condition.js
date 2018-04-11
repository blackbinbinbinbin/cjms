define(function(require, exports, module) {
	var lib = require('lib'), tpl = require('tpl');
	
	// require('jquery');
	require('jquery-ui');
	require('datetimepicker');
    require('select2');

	var fileUpload = require('js/libs/fileUpload.js');
	
	var M = {
		setDefaultView : function() {
            var url = lib.url + 'diyConfig/setDefaultView';
            var data = {}; 
	    	data.tableId = lib.getParam('tableId');
            data.metaValue = lib.removeParam2('where', document.location.hash.substr(2));
            
            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    lib.showTip(objResult.msg);
                } else {
                	lib.showErrorTip(objResult.msg);
                }
            });
        }, 
	    setDefaultCondition : function() {
            var url = lib.url + 'diyConfig/setDefaultCondition';
            var data = {}; 
	    	data.tableId = lib.getParam('tableId');
            data.metaValue = C.getWhere(true);
            
            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    lib.showTip(objResult.msg);
                } else {
                	lib.showErrorTip(objResult.msg);
                }
            });
        }, 
        getDefaultCondition : function() {
        	var url = lib.url + 'diyConfig/getDefaultCondition';
            var data = {}; 
            data.tableId = lib.getParam('tableId');
            
            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                	var defaultCondition = JSON.parse(objResult.data.metaValue);
                    if (!defaultCondition) { return; }
                    
                    for (var i = 0; i < defaultCondition.length; i++) {
                        var value = defaultCondition[i];
                        var field = value[0];
                        var opt = value[1];
                        var val = value[2];
                        var val2 = value[3];
                        
                        var $group = $("#condition .form-group[fieldName=" + field + "][opt='" + opt + "']");
                        $group.find(':input').each(function(i) {
                        	$(this).val(value[i + 2]);
                        });
                    }
                } else {
                	lib.showErrorTip(objResult.msg);
                }
            });
        },
        getCondition : function() {
        	var url = lib.url + 'diyCondition/index';
			var data = {};
			data.tableId = lib.getParam('tableId');
			data.where = lib.getParam('where');
			
			lib.get(url, data, function(html) {
				$('#condition').html(html);
			}, {
				type : 'text'
			});
        }
	};
	
	var C = {
        init : function() {
        	C.initCustomCondition();
        	C.initDocumentEvent();
        	
        	$(document).on(BDY.click, '#search', function() {
				lib.setParam('where', C.getWhere(false));
				require.async('js/diy/table.js', function(page) {
					page.loadTable();
				});

				if ($('#condition').attr('data-static')) {
                    require.async('js/diy/static.js', function(page) {
                        page.loadStatic();
                    });
				}

                if (lib.getParam('_showChart')) {
                    require.async('js/chart/list.js', function(page) {
                        page.chartList();
                    });
                }
			});
        	
        	$(document).on(BDY.click, '#setDefaultCondition', M.setDefaultCondition);
        	$(document).on(BDY.click, '#getDefaultCondition', M.getDefaultCondition);
			$(document).on(BDY.click, '#setDefaultView', M.setDefaultView);

            // 设置面包屑
            var nodeId = lib.getParam('_nodeId');
            if (nodeId) {
                parent.seajs.use('js/index.js', function(page) {
                    page.setNodeId(nodeId);
                });
            }

            $(document).on('submit', '#condition #advConditionForm, #condition #normalCondition', function() {
            	$('#search').trigger(BDY.click);
            	return false;
			});

            $(document).on('keydown', '#condition input', function(event) {
                if(event.keyCode == "13") {
                    $('#search').trigger(BDY.click);
                }
            });

        },
        getWhere : function(allowEmpty) {
        	var where = [];
        	var $groups = $('#condition').find('.form-group');
			for (var i = 0; i < $groups.length; i++) {
				var $group = $($groups[i]);
				var fieldName = $group.attr('fieldName');
				var opt = $group.attr('opt');
				var $controls = $group.find(':input');
				var value = [fieldName, opt];
				
				var isValid = false;
				for (var j = 0; j < $controls.length; j++) {
					var $control = $($controls[j]);
					var type = $control.attr('type');
					if (type == 'radio' || type == 'checkbox') {
						var flag = $control.prop('checked');
					} else {
						var flag = true;
					}

					if (flag) {
						var v = $control.val();
						value.push(v);
					}

					if (v) {
						isValid = true;
					}
				}
				
				if (allowEmpty || isValid) {
					where.push(value);
				}
			}
			
			return JSON.stringify(where);
        },
        initCustomCondition : function() {
        	// 添加自定义条件
        	$(document).on(BDY.click, '#addCondition', function() {
        		var data = {};
        		data['fieldName'] = $('#fieldName').val();
        		var $option = $('#fieldName > [value="' + data['fieldName'] + '"]');
        		data['fieldCName'] = $option.text();
        		data['fieldType'] = $option.attr('fieldType');
        		
        		data['opt'] = $('#opt').val();
        	    $option = $('#opt > [value="' + data['opt'] + '"]');
        	    data['optCName'] = $option.text();
        	    
        	    var $field = $('[fieldName="' + data['fieldName'] + '"][opt="' + data['opt'] + '"]');
        	    if ($field.length) {
        	    	lib.showTip('已经存在相同的条件了!');
        	    	return;
        	    }
        	    
        	    data['value1'] = $('[name=value1]').val();
        	    var $value2 = $('[name=value2]');
        	    if (data['opt'] == ':') {
					data['value1'] = [data['value1'], $value2.val()];
        	    }
        	    
        	    var html = tpl.render('temp_form_group', data);
        	    var $last = $('#advConditionForm .form-group:last');
        	    if ($last.length) {
        	    	$last.after(html);
        	    } else {
        	    	$('#advConditionForm').prepend(html);
        	    }
        	});
        	
        	$(document).on('change', '#opt', function() {
        		var value = $(this).val();
        		$('#customCondition .input-group > *').hide();
        		if (value == ':') {
        			$('#customCondition .input-group > *').show();
        		} else {
        			$('[name=value1]').show();
        		}
        	});
        	
        	// 删除自定义条件
        	$(document).on(BDY.click, '#advConditionForm .glyphicon-remove', function() {
        		$(this).parents('.form-group').remove();
        	});
        },
        initDocumentEvent : function() {
			fileUpload.initDateTime();

			$(document).on(BDY.click, '#switchToNormal', function() {
				$('#normalCondition').html($('#advConditionForm > *'));
				$('#normalCondition').show();
				$('#advCondition').hide();
			}).on(BDY.click, '#switchToAdv', function() {
				$('#advConditionForm').html($('#normalCondition > *'));
				$('#normalCondition').hide();
				$('#advCondition').show();
			});

        }
	}
	
	var V = {
		init : function(data) {
			var tempData = [];
			for (var key in data.where) {
			    var value = data.where[key]; 
			    
			    var tData = {};
			    tData['fieldName'] = value[0]; 
			    var field = data.fields[value[0]];
			    if (field) {
                    tData['fieldCName'] = field['fieldCName'];
				    tData['fieldType'] = field['fieldType'];
					tData['inputType'] = field['inputType'];
				    tData['opt'] = value[1]; 
				    tData['value1'] = value[2];

				    tData['optCName'] = data.opts[tData['opt']];
                    tData['enum1'] = field['enum'];

				    tempData[key] = tData;
			    }
			}
			data.where = tempData;
			var html = tpl.render('temp_form_inline', data);
			$('#normalCondition').html(html);
			
			var where = lib.getParam('where');
			if (where) {
				where = JSON.parse(where);
				for (var i in where) {
					var value = where[i];
					var $group = $('[fieldName="' + value[0] + '"][opt="' + value[1] + '"]');
					$group.find('.form-control').each(function(index) {
						$(this).val(value[index + 2]);
					});
				}
			}

			lib.initSelect2();
			
			setTimeout(function() {
				$('#search').trigger(BDY.click);
			});
		}
	}
	
	C.init();
	
	function init(data) {
	    V.init(data);
	    // 排序自定义条件s
    	$('#advConditionForm').sortable({ cancel: "a,button,:input" });

        $(document).trigger('diy_load_condition');
        console.log("$(document).trigger('diy_load_condition');");
	}
	
	exports.init = init;
	exports.getCondition = M.getCondition;

});

