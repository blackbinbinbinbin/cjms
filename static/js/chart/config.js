define(function(require, exports, module) {
    var lib = require('lib');
    var form = require('form');
    require('bootstrap');

    require('js/libs/CodeMirror/lib/codemirror.js');
    require('js/libs/CodeMirror/mode/css/css.js');
    require('js/libs/CodeMirror/mode/javascript/javascript.js');
    require('js/libs/CodeMirror/mode/clike/clike.js');
    require('js/libs/CodeMirror/mode/php/php.js');
    require('js/libs/CodeMirror/mode/xml/xml.js');
    require('js/libs/CodeMirror/mode/htmlmixed/htmlmixed.js');

    var $dialog, customConfigEditor;

    var M = {
        saveConfig : function(saveas) {
            var url = '/chart/saveConfig';
            var flag = form.validateForm('#chartConfigModal');
            if (!flag) {
                return;
            }

            var data = form.getFormData('#chartConfigModal');
            data['needCustom'] = $('[name=needCustom]').prop('checked') ? 1 : 0,
            data['customConfig'] = customConfigEditor.getValue();
            if (saveas) {
                data['chartId'] = '';
            }

            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    lib.showTip(objResult.msg);
                    if (data.chartId) {
                        $(document).trigger('loadChart', data.chartId);
                    } else {
                        // 新增图表需要重新加载
                        require.async('js/chart/list.js', function(page) {
                            page.chartList();
                        });
                    }

                    // 坑爹，bootstramp的bug
                    $('body').removeClass('modal-open');
                    $dialog.modal('hide');
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            }, {
                loading : true
            });
        }
    };

    var C = {
        init : function() {
            C.initTextArea();
            C.toggleOption('[name=needCustom]', '#customConfigDiv');

            $('#btn_save').on(BDY.click,  function() {
                M.saveConfig(false);
            });

            $('#btn_saveas').on(BDY.click, function() {
                if (!confirm('确定要另存为新图形？')) {
                    return false;
                }

                M.saveConfig(true);
            });
        },
        initTextArea : function () {
            customConfigEditor = CodeMirror.fromTextArea(document.getElementById("customConfig"), {
                lineNumbers: true,
                mode: "text/html"
            });
            customConfigEditor.setSize($('#customConfig').width(), 500);
        },
        toggleOption : function(id, divId, callback) {
            $(id).on(BDY.click, _toggleEvent);

            function _toggleEvent() {
                if ($(id).get(0).checked) {
                    callback && callback();
                    $(divId).show();
                } else {
                    $(divId).hide();
                }
            }

            _toggleEvent();
        }
    };

    function init() {
        $dialog = $('#chartConfigModal').modal({
            'backdrop' : 'static',
            'show' : true
        });

        setTimeout(function() {
            C.init();

            $dialog.on('hide.bs.modal', function () {
                $dialog.remove();
                $dialog = null;
            });
        }, 300);
    }

    exports.init = init;
});

