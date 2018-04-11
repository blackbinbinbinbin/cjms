define(function(require, exports, module) {
    var lib = require('lib');
    require('bootstrap');

    var $dialog;

    var M = {
        importTemplate : function() {
            var url = lib.url + "diyEdit/import?tableId=" + lib.getParam('tableId');
            var oData = new FormData($( "#import_report_form" )[0]);
            var oReq = new XMLHttpRequest();
            oReq.open("POST", url , true);
            lib.showLoading();
            oReq.onload = function(oEvent) {
                lib.hideLoading();
                if (oReq.status == 200) {
                    var text = oReq.responseText;
                    var objResult = JSON.parse(text);
                    if (objResult && objResult.result) {
                        $dialog.modal('hide');
                        lib.showTip(objResult.msg);

                        setTimeout(function() {
                            $('#search').click();
                        }, 500);
                    } else {
                        lib.showErrorTip(objResult.msg);
                    }
                }
            };
            oReq.send(oData);
        }
    };

    function init() {
        $dialog = $('#import_report').modal({
            'backdrop' : 'static',
            'show' : true
        });

        $('#btn_submit').on(BDY.click, function() {
            M.importTemplate();
            return false;
        });
    }

    exports.init = init;
});

