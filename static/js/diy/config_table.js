define(function(require, exports, module) {
	var lib = require('lib'), tpl = require('tpl');
	
	require('jquery');
	require('jquery-ui');
	require('colortip');
	require('css/jquery.colortip.css');
	
	var oldData;
	
	var M = {
	   getFieldsData : function() {
            var fields = [];
            $('#searchTable').find('.list-group-item:visible').each(function(i) {
                // var fieldMap = oldData.map[$(this).find('select[name=fieldMap]').val()]['func'];
                
                var fieldDisplay1 = $(this).find('input[name=fieldDisplay1]').prop('checked') ? 1 : 0;
                var fieldDisplay2 = $(this).find('input[name=fieldDisplay2]').prop('checked') ? 1 : 0;
                var fieldDisplay3 = $(this).find('input[name=fieldDisplay3]').prop('checked') ? 1 : 0;
                var showInCondition = $(this).find('input[name=showInCondition]').prop('checked') ? 1 : 0;
                var isPrimaryKey = $(this).find('input[name=isPrimaryKey]').prop('checked') ? 1 : 0;
                var needMerge = $(this).find('input[name=needMerge]').prop('checked') ? 1 : 0;
                var needMap2 = $(this).find('input[name=needMap2]').prop('checked') ? 1 : 0;

                fields.push({
                    'fieldId': $(this).attr('fieldId'),
                    'fieldName': $(this).find('input[name=fieldName]').val(),
                    'fieldCName': $(this).find('input[name=fieldCName]').val(),
                    'fieldSortName': $(this).find('input[name=fieldSortName]').val(),
                    'defaultSortOrder': $(this).find('select[name=defaultSortOrder]').val(),
                    'fieldType': $(this).find('select[name=fieldType]').val(),
                    'inputType': $(this).find('select[name=inputType]').val(),
                    'fieldLength': $(this).find('textarea[name=fieldLength]').val(),
                    'callBack': $(this).find('textarea[name=callBack]').val(),
                    'fieldDisplay': (fieldDisplay3 << 2) + (fieldDisplay2 << 1) + fieldDisplay1,
                    'showInCondition' : showInCondition,
                    'needMerge': needMerge,
                    'isPrimaryKey': isPrimaryKey,
                    'fieldVirtualValue': $(this).find('[name=fieldVirtualValue]').val(),
                    'defaultValue': $(this).find('[name=defaultValue]').val(),
                    // 'fieldMap': oldData.fieldMap,
                    'mapKey': $(this).find('select[name=mapKey]').val(),
                    'enumMapKey': $(this).find('select[name=enumMapKey]').val(),
					'needMap2' : needMap2,
                    'fieldPosition' : i
                });
            });
            
            return fields;
        }
	};
	
	var C = {
        init : function() {
        	$('#searchTable').sortable({revert:true, cancel:':input,.colorTip'});
        	
        	$('#searchTable').on(BDY.click, '[name=advOption]', function() {
        		var $item = $(this).parents('.list-group-item');
        		$item.find('.js-adv').slideToggle();
        	});
        	
        	$('#searchTable').on(BDY.click, '[name=delete]', function() {
        		var $item = $(this).parents('.list-group-item');
        		lib.confirm("确定要删除这个字段吗？", function() {
        			$item.slideUp(function() {
        				$item.remove();
        			});
        		});
        	});
        	
        	$('#searchTable :input[title]').colorTip({
                'color' : 'blue',
                'timeout' : 500
            });
        },
        addRow : function() {
        	var html = tpl.render('temp_list_item', oldData);
			$('#searchTable').append(html);
        }
	};
	
	var V = {
		init : function(data) {
			var html = tpl.render('temp_list', data);
			$('#searchTable').html(html);
		}
	};
	
	function init(data) {
		// 排序自定义条件
		if (data) {
			V.init(data);
			oldData = data;
		}
		
    	C.init();
	}
	
	exports.init = init;
	exports.addRow = C.addRow;
	exports.getFieldsData = M.getFieldsData;
	
});

