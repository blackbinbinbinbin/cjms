define(function(require, exports, module) {
    var lib = require('lib');
    require('bootstrap');
    require('colortip');
    require('css/jquery.colortip.css');
    var form = require('form');
    
    // require('jquery');
    require('jquery-ui');
    var keys = ['_max', '_min', '_sum', '_count', '_distinctCount', '_distinct', '_avg', '_save'];
    
    var M = {
        buildDataParam : function(needPage) {
            var data = {};
            data.tableId = lib.getParam('tableId');
            var where = lib.getParam('where');
            where && (data.where = where);

            var keyWord = {};
            var showGroupBy = lib.getParam('_showGroupBy');

            var k = ['_sortKey', '_sortDir', '_showGroupBy', '_showChart'];
            if (needPage) {
                k = k.concat(['_page', '_pageSize']);
            }

            var k2 = ['_groupby', '_showGroupBy', '_hideNoGroupBy'];
            k2 = k2.concat(keys);
            if (showGroupBy) {
                k = k.concat(k2);
            }

            for (var i in k) {
                var val = lib.getParam(k[i]);
                val && (keyWord[k[i]] = val);
            }

            data.keyWord = JSON.stringify(keyWord);
            return data;
        },
        loadTable : function(event, rowNum) {
            var url = lib.url + "diyData/table";
            var data = M.buildDataParam(true);
            var $loadingDiv = lib.getLoadingDiv('table');
            lib.get(url, data, function(html) {
                $loadingDiv.end();
                $('#table').html(html);

                var prefix = '';
                if (/test\./.test(location.href)) {
                    prefix = 'Dev_';
                } else if (/new\./.test(location.href)) {
                    prefix = 'New_';
                }
                document.title = prefix + $("#reportTitle").text();
            }, {
                type : 'text'
            });

            // 同时进行加载分页
            M.loadPager(rowNum);
        },
        loadPager : function(rowNum) {
            var url = lib.url + "diyData/pager";
            var data = M.buildDataParam(true);
            if (rowNum && typeof rowNum == 'number') {
                data.rowNum = rowNum;
            }
            // var $loadingDiv = lib.getLoadingDiv('pagerDiv');
            lib.get(url, data, function(html) {
                // $loadingDiv.end();
                $('#pagerDiv').html(html);
            }, {
                type : 'text'
            });
        },
        exportCSV : function(exportTemplate) {
            var url = lib.url + "diyData/exportCSV";
            if (exportTemplate) {
                url = lib.url + "diyData/exportTemplate";
            }

            var data = M.buildDataParam(false);
            // 打开新页面下载csv
            window.open(url + '?' + $.param(data));
            //location.href = url + '?' + $.param(data);
        },
        addView : function() {
            var url = lib.url + "diyEdit/addView?tableId=" + lib.getParam('tableId');
            var where = lib.getParam('where');
            if (where) {
                where = JSON.parse(where);
                for (var i in where) {
                    var value = where[i];
                    if (value[1] == '=' && value[2]) {
                        url += "&" + value[0] + '=' + value[2];
                    }
                }
            }

            if ($('[data-addInPageFlag]').attr('data-addInPageFlag') == 0) {
                lib.get(url, function(html) {
                    $('#operDiv').html(html);
                }, {
                    type : 'text',
                    loading : true
                });
            } else {
                url += '&inPage=1';
                location.href = url;
            }
        },
        editView : function(data) {
            var url = lib.url + "diyEdit/editView?tableId=" + lib.getParam('tableId');
            if ($('[data-editInPageFlag]').attr('data-editInPageFlag') == 0) {
                lib.get(url, data, function(html) {
                    $('#operDiv').html(html);
                }, {
                    type : 'text',
                    loading : true
                });
            } else {
                url += '&inPage=1&' + $.param(data);
                location.href = url;
            }
        },
        importView : function() {
            var url = lib.url + "diyEdit/importView?tableId=" + lib.getParam('tableId');
            lib.get(url, function(html) {
                $('#operDiv').html(html);
            }, {
                type : 'text',
                loading : true
            });
        },
        del : function(data) {
            if (!confirm('确定要删除吗？')) { return; }
            
            var url = lib.url + "diyEdit/del";
            data['tableId'] = lib.getParam('tableId');
            
            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    lib.showTip(objResult.msg);
                    M.loadTable();
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            }, {
                loading : true
            });
        },
        delMulti : function(ids) {
            var url = lib.url + "diyEdit/delMulti";
            var data = {};
            data['tableId'] = lib.getParam('tableId');
            data['ids'] = JSON.stringify(ids);
            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    lib.showTip(objResult.msg);
                    M.loadTable();
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            }, {
                loading : true
            });
        },
        save : function(selector) {
            if (form.validateForm(selector)) {
                var data = form.getFormData(selector);
                var $inputs = $(selector).parents('tr').find('.js-pri');
                var where = {};
                for (var i = 0; i < $inputs.length; i++) {
                    var $input = $($inputs[i]);
                    where[$input.attr('name')] = $input.val();
                }

                M.saveData(data, where);
            }
        },
        saveData : function(data, where, callback) {
            var url = lib.url + "diyEdit/save";
            data['tableId'] = lib.getParam('tableId');
            data['_where'] = JSON.stringify(where);

            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    lib.showTip(objResult.msg);
                } else {
                    lib.showErrorTip(objResult.msg);
                }

                callback && callback();
            });
    	}
    };
    
    var C = {
        init : function() {
            $(document).on('pager_change', M.loadTable);
            $(document).on('diy_edit_save', M.loadTable);
            $(document).on('diy_edit_add', M.loadTable);

            $(document).on(BDY.click, '#oper [name=refresh]', M.loadTable);
            $(document).on(BDY.click, '#oper [name=export]', function() {
                M.exportCSV(false);
            });

            $(document).on(BDY.click, '#oper [name=cal]', C._showGroupBy);
            $(document).on(BDY.click, '#oper [name=hideNoGroupBy]', C._hideNoGroupBy);
            $(document).on(BDY.click, '#oper [name=chart]', C._showChart);

            // 添加按钮
            $(document).on(BDY.click, '#btn_add', M.addView);
            // 导入模板按钮
            $(document).on(BDY.click, '#btn_import_template', M.importView);

            // 导出模板按钮
            $(document).on(BDY.click, '#btn_export_template', function() {
                M.exportCSV(true);
            });

            // 编辑按钮
            $(document).on(BDY.click, '#btn_edit', function() {
                var $tr = $(this).parent().parent();
                var data = C.getTrPri($tr);
                M.editView(data);
            });

            // 删除按钮
            $(document).on(BDY.click, '#btn_del', function() {
                var $tr = $(this).parent().parent();
                var data = C.getTrPri($tr);
                M.del(data);
            });

            C.initCheckBox();
            C.initTableSort();
            C.initGroupBy();

            $(document).on("change", '[easyedit] :input', function(event) {
                M.save($(this).parents('td'));
            });

            if (lib.getParam('hideAdv')) {
                $("#switchToAdv").hide();
            }
        },
        _showChart : function() {
            var showChart = lib.getParam('_showChart');
            var $chartContainer = $('#chartContainer');
            if (showChart) {
                lib.removeParam('_showChart');
                $(this).removeClass('active');
                $chartContainer.hide();
            } else {
                lib.setParam('_showChart', 1);
                $(this).addClass('active');
                if ($chartContainer.length > 0) {
                    $chartContainer.show();
                } else {
                    require.async('js/chart/list.js', function(page) {
                        page.chartList();
                    });
                }
            }
        },
        _showGroupBy : function() {
            var showGroupBy = lib.getParam('_showGroupBy');
            if (showGroupBy) {
                lib.removeParam('_showGroupBy');
                $(this).removeClass('active');
                $('.icon').hide();
            } else {
                lib.setParam('_showGroupBy', 1);
                $(this).addClass('active');
                $('.icon').show();
            }
        },
        _hideNoGroupBy : function() {
            var flag = lib.getParam('_hideNoGroupBy');
            if (flag) {
                lib.removeParam('_hideNoGroupBy');
                $(this).removeClass('active');
            } else {
                lib.setParam('_hideNoGroupBy', 1);
                $(this).addClass('active');
            }
            M.loadTable();
        },
        getTrPri : function($tr) {
            var $inputs = $tr.find('.js-pri');
            var data = {};
            $inputs.each(function(i) {
                var key = $(this).attr('name');
                var val = $(this).val();
                data[key] = val;
            });
            return data;
        },
        initCheckBox : function() {
            // 删除按钮
            $(document).on(BDY.click, '#checkall', function() {
                var $checkbox = $('#table table [type=checkbox]');
                $checkbox.prop('checked', this.checked);
            });

            // 选择多条记录
            $(document).on(BDY.click, '#table tr', function(event) {
                if ($(event.target).attr('type') == 'checkbox') {
                    return;
                }

                var $checkbox = $(this).find('[type=checkbox]');
                $checkbox.prop('checked', !$checkbox.prop('checked'));
            });

            $(document).on(BDY.click, '#btn_del_select', function() {
                var $checkbox = $('#table table [type=checkbox]:checked');
                if (!confirm('确定删除这' + $checkbox.length + '项?')) {
                    return;
                }

                var ids = [];
                $checkbox.each(function() {
                    var $tr = $(this).parents('tr');
                    ids.push(C.getTrPri($tr));
                });

                M.delMulti(ids);
            });

        },
        initTableSort : function() {
            $(document).on(BDY.click, '[sortKey]', function() {
                var sortKey = $(this).attr('sortKey');
                var oldSortKey = lib.getParam('_sortKey');
                if (sortKey == oldSortKey) {
                    var oldSortDir = lib.getParam('_sortDir');
                    if (oldSortDir == 'DESC') {
                        lib.setParam('_sortDir', 'ASC');
                    } else {
                        lib.setParam('_sortDir', 'DESC');
                    }
                } else {
                    lib.setParam('_sortKey', sortKey);
                    lib.setParam('_sortDir', 'ASC');
                }
                
                M.loadTable();
            });
        },
        _resetUrlParam : function(key) {
            //赋值到url的hash中
            var fieldNames = [];
            $('th a._' + key).each(function(i) {
                fieldNames[i] = $(this).parent().attr('fieldName');
            });

            if (fieldNames.length) {
                lib.setParam('_' + key, fieldNames.join(','));
            } else {
                lib.removeParam('_' + key);
            }
        },
        initGroupBy : function() {
            //group by图标
            $(document).on(BDY.click, '[data-role=groupby] a.icon', function(event) {
                var className = $(event.target).attr('class');
                if (/noGroupby/.test(className)) {
                    $(this).attr('class', 'icon _groupby').attr('title', '分组计算');
                } else if (/groupby/.test(className)) {
                    $(this).attr('class', 'icon _save').attr('title', '保留字段');
                } else if (/save/.test(className)) {
                    $(this).attr('class', 'icon _noGroupby').attr('title', '不分组计算');
                }

                C._resetUrlParam('groupby');
                C._resetUrlParam('save');

                return false;
            });

            //group by图标
            $(document).on(BDY.click, '[data-role=value] a.icon', function(event) {
                //这里是计算列的逻辑
                var $select = $(this).find('select');
                var index = $select.find('option:selected').index();
                var len = $select.find('option').length;
                var newIndex = (index + 1) % len;
                //选择下一个
                $select.val($select.find('option:eq(' + newIndex + ')').val());

                return false;
            });
        },
        initCal : function() {
            // cal图标
            var $cal = $('.table').find('th a.cal, th a.noCal');
            $cal.colorTip && $cal.colorTip({
                'color' : 'blue',
                'timeout' : 100,
                'hideCallback' : function() {
                    var val = $(this).find('select').val();
                    
                    V.changeState($(this).parent().attr('fieldName'), val);
                    
                    var cals = {};
                    $('.cal').each(function() {
                        var val = $(this).find('select').val();
                        if (val) {
                            cals[val] = cals[val] || []; 
                            cals[val].push($(this).parent().attr('fieldName'));
                        }
                    });

                    lib.removeParam(keys);
                    for (var key in cals) {
                        lib.setParam(key, cals[key].join(','));
                    }

                    // _save特殊处理
                    C._resetUrlParam('save');
                }
           });
            
            // 防止冒泡
            $cal.find('select').click(function() {
                return false;
            });
            
               //从url参数中绑定计算图标
            if (!lib.getParam('_showGroupBy')) {
                $("th a.icon").hide();
            } else {
                $("th a.icon").show();
            }
            
        },
        initCopy : function() {
            $('#oper [name=copy]').copy({
                'getContent' : function(clip) {
                     lib.showTip("复制成功!");
                    //return parent.main.location.href;
                }
            });
        }
    };
    
    var V = {
        init : function() {
            var groupby = lib.getParam('_groupby');
            if (groupby) {
                groupby = groupby.split(',');
                for (var i in groupby) {
                    $("th[fieldName='" + groupby[i] + "'] a.icon").addClass('_groupby').attr('title', '分组计算');
                }
            }

            var save = lib.getParam('_save');
            if (save) {
                save = save.split(',');
                for (var i in save) {
                    $("th[fieldName='" + save[i] + "'] a.icon").addClass('_save').attr('title', '保留字段');
                }
            }

            for (var i in keys) {
                var key = keys[i];
                var val = lib.getParam(key);
                if (!val) { continue; }
                                
                val = val.split(',');
                for (var k in val) {
                    V.changeState(val[k], key);
                }
            }

        },
        changeState : function(fieldName, val) {
            $("th[fieldName='" + fieldName + "'] select").val(val);
            
            var $a = $("th[fieldName='" + fieldName + "'] a.icon");
            
            for (var j in keys) {
                $a.removeClass(keys[j]);
            }
            
            $a.addClass(val);
        }
    };
    
    C.init();
    
    function init(data) {
        // 复制main里面的url
        // setTimeout(C.initCopy, 100);
        C.initCal();
        V.init();

        $(document).trigger('diy_load_table');
        console.log("$(document).trigger('diy_load_table');");
    }
    
    exports.init = init;
    exports.loadTable = M.loadTable;
    exports.buildDataParam = M.buildDataParam;
    exports.saveData = M.saveData;

});

