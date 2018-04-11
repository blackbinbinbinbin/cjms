define(function(require, exports, module) {
    var lib = require('lib');
    require('bootstrap');

    var $dialog;

    function init() {
        $dialog = $('#import_report').modal({
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

