define(function(require, exports, module) {
	var lib = require('lib'), tpl = require('tpl');
	
	require('jquery');
	require('jquery-ui');
	require('colortip');
	require('css/jquery.colortip.css');
	
	var map, inputTypes;
	
	var M = {
	   getFieldsData : function() {
            var fields = [];
            $('#editTable').find('.list-group-item:visible').each(function(i) {
                
                var required = $(this).find('[name=required]').prop('checked') ? 1 : 0;
                var showInAdd = $(this).find('[name=showInAdd]').prop('checked') ? 1 : 0;
                var showInEdit = $(this).find('[name=showInEdit]').prop('checked') ? 1 : 0;
                var easyEdit = $(this).find('[name=easyEdit]').prop('checked') ? 1 : 0;
				var isPrimaryKey = $(this).find('input[name=isPrimaryKey]').prop('checked') ? 1 : 0;
                
                fields.push({
                    'fieldId': $(this).attr('fieldId'),
                    'fieldName': $(this).find('[name=fieldName]').val(),
                    'fieldCName': $(this).find('[name=fieldCName]').val(),
                    'orginType': $(this).find('[name=orginType]').val(),
                    'inputType': $(this).find('[name=inputType]').val(),
                    'labelColSpan': $(this).find('[name=labelColSpan]').val(),
                    'inputColSpan': $(this).find('[name=inputColSpan]').val(),
                    'postfixColSpan': $(this).find('[name=postfixColSpan]').val(),
                    'inputHeight': $(this).find('[name=inputHeight]').val(),
                    'placeholder': $(this).find('[name=placeholder]').val(),
                    'inputTip': $(this).find('[name=inputTip]').val(),
                    'postfixTip' : $(this).find('[name=postfixTip]').val(),
                    'newlineTip' : $(this).find('[name=newlineTip]').val(),
                    'editDefaultValue' : $(this).find('[name=editDefaultValue]').val(),
                    // 'fieldLength': $(this).find('[name=fieldLength]').val(),
					'isPrimaryKey': isPrimaryKey,
                    'required' : required,
                    'showInAdd' : showInAdd,
                    'showInEdit' : showInEdit,
                    'easyEdit' : easyEdit,
                    'fieldPosition' : i
                });
            });
            
            return fields;
        }
	};
	
	var C = {
        init : function() {
        	$('#editTable').sortable({revert:true, cancel:':input,.colorTip'});
        	
        	$('#editTable').on(BDY.click, '[name=advOption]', function() {
        		var $item = $(this).parents('.list-group-item');
        		$item.find('.js-adv').slideToggle();
        	});
        	
        	$('#editTable').on(BDY.click, '[name=delete]', function() {
        		var $item = $(this).parents('.list-group-item');
        		lib.confirm("确定要删除这个字段吗？", function() {
        			$item.slideUp(function() {
        				$item.remove();
        			});
        		});
        	});
        	
        	$('#editTable :input[title]').colorTip({
                'color' : 'blue',
                'timeout' : 500
            });

        },
        addRow : function() {
        	var data = { map:map, inputTypes:inputTypes, labelColSpan : 3, inputColSpan : 8, postfixColSpan : 0};
        	var html = tpl.render('temp_editlist_item', data);
			$('#editTable').append(html);
        }
	};
	
	var V = {
		init : function(data) {
			var html = tpl.render('temp_editlist', data);
			$('#editTable').html(html);
		}
	};
	
	function init(data) {
		// 排序自定义条件
		if (data) {
			V.init(data);
	        map = data.map;
	        inputTypes = data.inputTypes;
		}
		
    	C.init();
	}
	
	exports.init = init;
	exports.addRow = C.addRow;
	exports.getFieldsData = M.getFieldsData;
});

