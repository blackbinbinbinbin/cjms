define(function(require, exports, module) {
    var lib = require('lib');
    var table = require('js/diy/table.js');
    require('bootstrap');

    var M = {
        chartList : function() {
            var url = '/chart/list';
            var data = table.buildDataParam(false);
            lib.get(url, data, function(html) {
                $('#chart').html(html);
            }, {
                type : 'text'
            });
        },
        loadChart : function(chartId) {
            var data = table.buildDataParam(false);
            data.chartId = chartId;

            var url = "/chart/item";

            var $overlay = $('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');

            var $chart = $("[data-chartId='" + data.chartId + "']");
            var $box = $chart.parents('.box');
            $overlay.appendTo($box);

            lib.get(url, data, function(html) {
                $chart.html(html);
                $overlay.remove();
            }, {
                type : 'text'
            });
        },
        editConfigDialog : function(chartId) {
            var url = "/chart/editConfigDialog";
            var data = {};
            data.tableId = lib.getParam('tableId');
            if (chartId) {
                data.chartId = chartId;
            }

            lib.get(url, data, function(html) {
                $('#chartOperDiv').html(html);
            }, {
                type : 'text',
                loading : true
            });
        },
        del : function(chartId) {
            var data = {chartId : chartId};
            var url = "/chart/del";
            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    // 前端删除图表
                    var $chart = $("[data-chartId='" + data.chartId + "']");
                    $chart.parents('.box').parent().remove();

                    lib.showTip(objResult.msg);
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
            var $box = $('.box');
            $('#btn_add_chart').on(BDY.click, M.editConfigDialog);

            $box.on(BDY.click, '[data-widget="refresh"]', function() {
                var chartId = C.getChartId(this);
                M.loadChart(chartId);
            });

            $box.on(BDY.click, '[data-widget="edit"]', function() {
                var chartId = C.getChartId(this);
                M.editConfigDialog(chartId);
            });

            $box.on(BDY.click, '[data-widget="remove"]', function() {
                if (!confirm('确定要删除这个图形？')) return false;
                var chartId = C.getChartId(this);
                M.del(chartId);
            });
        },
        initChartData : function() {
            $('[data-chartId]').each(function() {
                var chartId = $(this).attr('data-chartId');
                M.loadChart(chartId);
            });
        },
        getChartId : function(elem) {
            var $box = $(elem).parents('.box');
            return $box.find('.chart').attr('data-chartId');
        },
        initGlobalEvent : function() {
            $(window).resize(function() {
                var echartsIns = window.echartsIns || {};
                for (var chartId in echartsIns) {
                    echartsIns[chartId].resize();
                }
            });

            $(document).on('loadChart', function(event, chartId) {
                $("[data-chartId^='" + chartId + "']").each(function() {
                    var chartId2 = $(this).attr('data-chartId');
                    M.loadChart(chartId2);
                });
            });
        }
    }

    C.initGlobalEvent();

    function init() {
        setTimeout(function() {
            C.init();
            C.initChartData();
        }, 300);
    }

    exports.init = init;
    exports.chartList = M.chartList;
});

