define(function(require, exports, module) {
    var lib = require('lib');
    var form = require('form');
    require('bootstrap');

    var $dialog;

    var M = {
        publish : function() {
            var url = '/nameService/publish';
            if (!form.validateForm('#publish_dialog')) {
                return false;
            }

            var data = form.getFormData('#publish_dialog');
            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    // lib.showTip(objResult.msg);
                    //
                    // $('body').removeClass('modal-open');
                    // $dialog.modal('hide');
                    M.checkPocess(objResult.data);
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            }, {
                loading : true
            });
        },
        checkPocess : function(data) {
            var url = "/nameService/process";
            lib.post(url, data, function(html) {
                $('#process_div').html(html);
                var $flag = $('[data-flag]');
                if ($flag.length && $flag.attr('data-flag') !== '1') {
                    setTimeout(function() {
                        M.checkPocess(data);
                    }, 1000);
                }
            }, {
                // loading : true,
                type : 'text'
            });
        },
        get_version : function (){
            var url = '/nameService/getVersion';
            var data = form.getFormData('#publish_dialog');
            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    V.addVersion(objResult.data); // 添加select选项 option
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            }, {
                loading : true
            });
        }
    };

    var V = {
        show_version : function () {
            var val = $('input[name="select_version"]:checked').val();
            $(".version_list").empty(); // 移除 select 的option
            if(val == 'yes'){
                $('#version_select').removeClass("hidden"); // 展示历史发布版本
                M.get_version();
            }else{
                $('#version_select').addClass("hidden");
            }
        },
        addVersion : function (data) {
            var text = "";
            var value = "";
            if(data.length == 0){
                $(".version_list").append("<option value='0'>没数据</option>");
            }else{
                for (var i in data) {
                    value = data[i]['version_id'];
                    text = data[i]['version_id']+"--"+data[i]['creator']+"--"+data[i]['log_time'];
                    $(".version_list").append("<option value='"+value+"'>"+text+"</option>");
                }
            }
            
        }
        
    };

    var C = {
        'init' : function() {
            $('#btn_submit').on(BDY.click, M.publish);

            // 版本显示与展示
            $("input[name='select_version']").on(BDY.click, V.show_version);
            $("input[name='env']").on(BDY.click, V.show_version);
            $("input[name='key']").on(BDY.click, V.show_version);
        }
    };

    function init() {
        C.init();

        $dialog = $('#publish_dialog').modal({
            'backdrop' : 'static',
            'show' : true
        });

        setTimeout(function() {
            $dialog.on('hide.bs.modal', function () {
                $dialog.remove();
                $dialog = null;
            });
        }, 1000);
    }

    exports.init = init;
});

