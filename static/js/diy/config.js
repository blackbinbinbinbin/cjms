define(function(require, exports, module) {
	var lib = require('lib'), form = require('form'), searchPart = require('js/diy/config_table.js');
	var editPart = require('js/diy/config_edit.js');
	require('select2');
	var map = null, jsCssEditor = null, phpEditor = null, phpEditor2 = null;
	
	var M = {
        getDbIds : function() {
            var url = lib.url + "diyConfig/getDbIds";
            var data = M.getDbData();

            var $loadingDiv = lib.getLoadingDiv('dbId');
            lib.post(url, data, function(objResult) {
                $loadingDiv.end();
                if (objResult.result) {
                    V.buildSelectHtml('dbId', objResult.data);
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            });
        },
		getDbs : function() {
			var url = lib.url + "diyConfig/getDbs";
            var data = M.getDbData();
            
            var $loadingDiv = lib.getLoadingDiv('sourceDb');
            lib.post(url, data, function(objResult) {
            	$loadingDiv.end();
            	if (objResult.result) {
                    V.buildSelectHtml('sourceDb', objResult.data);
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            });
        },
        getTables : function() {
			var url = lib.url + "diyConfig/getTables";
            console.log('in');
            var data = M.getDbData();
            data.sourceDb = data.sourceDb || $('#sourceDb').attr('defaultValue');
            
            var $loadingDiv = lib.getLoadingDiv('sourceTable');
            lib.post(url, data, function(objResult) {
            	$loadingDiv.end();
            	if (objResult.result) {
                    V.buildSelectHtml('sourceTable', objResult.data);
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            });
        },
        getPriKey : function() {
            var url = lib.url + "diyConfig/getPriKeys";
            var data = M.getDbData();
            data.sourceDb = data.sourceDb || $('#sourceDb').attr('defaultValue');

            var $loadingDiv = lib.getLoadingDiv('redisKey');
            lib.post(url, data, function(objResult) {
                $loadingDiv.end();
                if (objResult.result) {
                    $('#redisKey').val(objResult.data.join(','));
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            });
        },
        testConnectRedis : function() {
            var url = lib.url + "diyConfig/testConnectRedis";
            var data = M.getDbData();

            var $loadingDiv = lib.getLoadingDiv('testConnectRedis');
            lib.post(url, data, function(objResult) {
                $loadingDiv.end();
                if (objResult.result) {
                    lib.showTip("连接成功");
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            });
        },
        getDbData : function() {
            alert('in');
            var data = {};
            data.tableId = $('#tableId').val() || lib.getParam('tableId');
            var args = ['nameDb', 'dbId', 'sourceHost', 'sourcePort', 'sourceUser', 'sourcePass', 'sourceDb', 'sourceTable', 'templateField'];
            for (var i in args) {
                data[args[i]] = $('#' + args[i]).val();
            }

            var args = ['pubRedis', 'pubKey', 'pubMsgCallback', 'nameRedis', 'nameRedisKey', 'redisHost', 'redisPort', 'redisPass', 'redisKey', 'redisTtl', 'redisDb'];
            for (var i in args) {
                data[args[i]] = $('#' + args[i]).val();
            }

            data['supportR2M'] = $('#supportR2M').prop('checked') ? 1 : 0;
            data['supportPub'] = $('#supportPub').prop('checked') ? 1 : 0;
            return data;
        },
        getReportData : function() {
        	var dbData = M.getDbData();
            dbData.tableType = lib.getParam('tableType');

        	var data = {
                'editFlag': $('#editFlag').prop('checked') ? 1 : 0,
                'safeEditFlag': $('#safeEditFlag').prop('checked') ? 1 : 0,
                'hideEditFlag': $('#hideEditFlag').prop('checked') ? 1 : 0,
                // 'bookFlag': $('#bookFlag').prop('checked') ? 1 : 0,
                'excelFlag': $('#excelFlag').prop('checked') ? 1 : 0,
                'groupFlag': $('#groupFlag').prop('checked') ? 1 : 0,
                'chartFlag': $('#chartFlag').prop('checked') ? 1 : 0,
                'exportTemplateFlag': $('#exportTemplateFlag').prop('checked') ? 1 : 0,
                'importTemplateFlag': $('#importTemplateFlag').prop('checked') ? 1 : 0,
                'addInPageFlag': $('#addInPageFlag').prop('checked') ? 1 : 0,
                'editInPageFlag': $('#editInPageFlag').prop('checked') ? 1 : 0,
                'pageStaticFlag': $('#pageStaticFlag').prop('checked') ? 1 : 0,
                'totalStaticFlag': $('#totalStaticFlag').prop('checked') ? 1 : 0,
            };
        	
        	var args = ['tableCName', 'admins', 'pagination', 'tableInfo', 'staticMode', 'editDialogWidth'];
            for (var i in args) {
                data[args[i]] = $('#' + args[i]).val();
            }
            data['extraJsCss'] = jsCssEditor.getValue();
            data['saveCallBack'] = phpEditor.getValue();
            data['sourceCallBack'] = phpEditor2.getValue();

        	return $.extend(data, dbData);
        },
        loadFieldTable : function(loadType) {
        	var url = lib.url + "diyConfig/loadFieldTable";
            var data = M.getDbData();
            data.loadType = loadType;
            data.tableType = lib.getParam('tableType');
            
            var $loadingDiv = lib.getLoadingDiv('searchPart');
            lib.get(url, data, function(html) {
            	$loadingDiv.end();
            	$('#searchPart .panel-body').html(html);
                lib.initSelect2();
            }, {
            	type : 'text'
            });
        },
        saveTable : function () {
            if (!form.validateForm('#tableForm') || !form.validateForm('#searchPart') || !confirm('确定要保存？')) {  return false;  }
            
            var data = M.getReportData();
            var fields = searchPart.getFieldsData();
            data['fields'] = JSON.stringify(fields);
            //var extraJsCss = $('#extraJsCss').val().replace(/\&/g,"%26").replace(/\+/g,"%2B");
            //var sourceCallBack = $('#sourceCallBack').val().replace(/\&/g,"%26").replace(/\+/g,"%2B");
            
            var url = lib.url + "diyConfig/saveTableAndFields";
            lib.post(url, data, function(objResult) {
            	if (objResult.result) {
            		var url = SITE_URL + 'DiyData/report?tableId=' + objResult.data;
            		$('#linkUrl').html(url).attr('href', url);
            		$('#tableId').val(objResult.data);
            	}
            	
            	lib.showTip(objResult.msg);
            }, {
            	loading : true
            });
        },
        loadEditFields : function(loadEditType) {
        	var url = lib.url + "diyConfig/loadEditFields";
            var data = M.getDbData();
            data.loadEditType = loadEditType;
            
            var $loadingDiv = lib.getLoadingDiv('editPart');
            lib.get(url, data, function(html) {
            	$loadingDiv.end();
            	$('#editPart .panel-body').html(html);
                lib.initSelect2();
            }, {
            	type : 'text'
            });
        },
        saveEditTable : function () {
            if (!form.validateForm('#tableForm') || !form.validateForm('#tableForm') || !confirm('确定要保存？')) {  return false;  }
            
            var data = M.getReportData();
            var fields = editPart.getFieldsData();
            data['fields'] = JSON.stringify(fields);
            //var extraJsCss = $('#extraJsCss').val().replace(/\&/g,"%26").replace(/\+/g,"%2B");
            //var sourceCallBack = $('#sourceCallBack').val().replace(/\&/g,"%26").replace(/\+/g,"%2B");
            
            var url = lib.url + "diyConfig/saveEditTable";
            lib.post(url, data, function(objResult) {
            	if (objResult.result) {
            		var url = SITE_URL + 'DiyData/report?tableId=' + objResult.data;
            		$('#linkUrl').html(url).attr('href', url);
            		$('#tableId').val(objResult.data);
            	}
            	
            	lib.showTip(objResult.msg);
            }, {
            	loading : true
            });
        }
	};
	
	var C = {
        init : function() {
            var tableType = lib.getParam('tableType');
            tableType || lib.setParam('tableType', 1);

            $('#loadDbId').click(M.getDbIds);
        	$('#loadDb').click(M.getDbs);
            $('#loadTable').click(M.getTables);
            C.bindAddOption();
            
            $('[loadType]').click(function() {
            	var loadType = $(this).attr('loadType');
                M.loadFieldTable(loadType);
            });
            
            $('[loadEditType]').click(function() {
            	var loadEditType = $(this).attr('loadEditType');
                M.loadEditFields(loadEditType);
            });

            $('#loadPriKey').click(M.getPriKey);
            $('#testConnectRedis').on(BDY.click, M.testConnectRedis);
            
            $('#addField').click(function() {
            	require.async('js/diy/config_table.js', function(page) {
            		page.addRow();
            	});
            });
            
            $('#addEditField').click(function() {
            	require.async('js/diy/config_edit.js', function(page) {
            		page.addRow();
            	});
            });
            
            // tab的事件
            $('[role=presentation] a').on(BDY.click, function() {
            	var hash = this.hash;
            	$(hash).siblings().hide();
            	$(hash).show();
            	
            	$(this).parent().siblings().removeClass('active');
            	$(this).parent().addClass('active');
            	
            	return false;
            });
            
            $('#saveTable').on(BDY.click, M.saveTable);
            $('#saveEditTable').on(BDY.click, M.saveEditTable);

            C.toggleOption('#supportR2M', '#supportR2MDiv', C.initRedisStatus);
            C.toggleOption('#supportPub', '#supportPubDiv', C.initPubStatus);
            C.toggleOption('#editFlag', '#editFlagDiv');
            C.toggleOption('#groupFlag', '#groupFlagDiv');

            $('#dbId, #nameDb').on("change", C.initDbStatus).change();

            $('#loadNameRedis, #loadNameRedisKey').on(BDY.click, C.initRedisStatus).change();

            $('#loadPubRedis').on(BDY.click, C.initPubStatus);

            jsCssEditor = CodeMirror.fromTextArea(document.getElementById("extraJsCss"), {
                lineNumbers: true,
                mode: "text/html"
            });
            jsCssEditor.setSize($('#extraJsCss').width(), 500);

            phpEditor = CodeMirror.fromTextArea(document.getElementById("saveCallBack"), {
                lineNumbers: true,
                mode: "text/x-php"
            });
            phpEditor.setSize($('#saveCallBack').width(), 500);

            phpEditor2 = CodeMirror.fromTextArea(document.getElementById("sourceCallBack"), {
                lineNumbers: true,
                mode: "text/x-php"
            });
            phpEditor2.setSize($('#sourceCallBack').width(), 400);


            $('.toggleDiy').on('click', function() {
                $(this).parent().parent().next().toggleClass('hide_jscss');
            });

            // 加载全部模板字段
            $('#loadAllTemplateField').click(function() {
                var $items = $('#searchTable .list-group-item');
                var values = [];
                $items.each(function() {
                    values.push($(this).find('[name=fieldName]').val());
                });
                $('#templateField').val(values.join(', '));
            });

            lib.initSelect2();

            // 加载数据
            if (lib.getParam('tableId')) {
            	$('[loadType=1]').click();
            	$('[loadEditType=1]').click();
            }
        },
        toggleOption : function(id, divId, callback) {
            $(id).on(BDY.click, _toggleEvent);

            function _toggleEvent() {
                var input = $(id).get(0);
                if (!input) {
                    return false;
                }

                if (input.checked) {
                    callback && callback();
                    $(divId).slideDown();
                } else {
                    $(divId).slideUp();
                }
            }

            _toggleEvent();
        },
        bindAddOption : function() {
        	$('.addOption').click(function() {
                var $span = $('#temp_addOption').clone();
                $span.show();
                
                var $parent = $(this).parent();
                $parent.hide();
                $parent.after($span);
                
                var $select = $parent.parent().find('select');
                $span.find('input').val($select.val());
                
                //取消按钮
                $span.find('#cancel').click(function() {
                    $span.prev().show();
                    $span.remove();
                });
                
               //添加按钮
                $span.find('#add').click(function() {
                    var val = $span.find('input').val();
                    var html = '<option value="' + val + '">' + val + '<option>';
                    var $option = $select.find('option[value="' + val + '"]');
                    $option.length || $select.append(html);
                    $select.val(val);
                    
                    $span.find('#cancel').click();
                });

                lib.initSelect2();
            });
        },
        initDbStatus : function() {
            var nameDb = $('#nameDb').val();
            if (nameDb) {
                $('#dbId').parents('.form-group').slideUp();
                $('.js-db').slideUp();
            } else {
                $('#dbId').parents('.form-group').slideDown();
                var db_id = $('#dbId').val();
                if (db_id) {
                    $('.js-db').slideUp();
                } else {
                    $('.js-db').slideDown();
                }
            }

        },
        initRedisStatus : function() {
            var nameDb = $('#nameDb').val();
            if (nameDb) {
                var $option = $("#nameRedis option[value='" + nameDb + "']");
                $option.length && $("#nameRedis").val(nameDb);

                var sourceTable = $('#sourceTable').val();
                var value = nameDb + ':' + sourceTable;
                $option = $("#nameRedisKey option[value='" + value + "']");
                $option.length && $("#nameRedisKey").val(value);
            }

            $('#nameRedis, #nameRedisKey').change();
        },
        initPubStatus : function() {
            var nameDb = $('#nameDb').val();
            if (nameDb) {
                var $option = $("#pubRedis option[value='" + nameDb + "']");
                $option.length && $("#pubRedis").val(nameDb);

                var text = $('#pubKey').val();
                if (!text) {
                    var sourceTable = $('#sourceTable').val();
                    var value = nameDb + ':' + sourceTable;
                    $('#pubKey').val('diy:' + value);
                }
            }
        }
	};
	
	var V = {
		buildSelectHtml : function(id, data) {
			var $select = $('#' + id);
	        var value = $select.val();
	        
	        var html = '<option value=""><=- 请选择 -=></option>';
	        for (var i in data) {
	            if (i == value) {
	            	html += '<option value="' + i + '" selected>' + data[i] + '</option>';
	            } else {
	            	html += '<option value="' + i + '">' + data[i] + '</option>';
	            }
	        }
	        
	        $select.html(html);
	    }
	};
	
	C.init();
	
	function init(data) {
		map = data;
	}
	
	exports.init = init;
	exports.getReportData = M.getReportData;
	
});

