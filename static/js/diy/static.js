define(function(require, exports, module) {
	var lib = require('lib');
	var cacheHtml;

	var M = {
        loadStatic: function () {
			var url = lib.url + "diyData/static";
            var data = {};
            data.tableId = lib.getParam('tableId');
            var where = lib.getParam('where');
            where && (data.where = where);

            var keyWord = {};
            var showGroupBy = lib.getParam('_showGroupBy');

            var keys = ['_sortKey', '_sortDir', '_page', '_pageSize', '_showGroupBy'];
            var keys2 = ['_groupby', '_save', '_max', '_min', '_count', '_sum', '_avg', '_distinctCount', '_distinct', '_showGroupBy', '_hideNoGroupBy'];
            showGroupBy && (keys = keys.concat(keys2));

            for (var i in keys) {
                var val = lib.getParam(keys[i]);
                val && (keyWord[keys[i]] = val);
            }

            data.keyWord = JSON.stringify(keyWord);
            lib.get(url, data, function (html) {
                cacheHtml = html;
                $('#staticDiv').html(html);
            }, {
                type: 'text'
            });
        }
    };

	var C = {
		init: function() {
            $(document).on('diy_load_table', function() {
                $('#staticDiv').html(cacheHtml);
			});
		}
	};

	C.init();

	function init() {
	}
	
	exports.init = init;
	exports.loadStatic = M.loadStatic;

});

