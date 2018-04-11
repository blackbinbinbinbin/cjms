define(function(require, exports, module) {
    var lib = require('lib'), $ = require('jquery'), form = require('form'), tpl = require('tpl');
    require('bootstrap');
    require('select2');
    require('jquery-ui');
    require('jqext');

    var _task, _requireItems = {};
    var _items, _rule, _parent_items, _parent_rule;
    var iframeDoc = null;
    var iframeWin = null;
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
        editView : function(field_name) {
            var url = '/diyEdit/editView?tableId=4424e428-3ac7-e981-ec28-96592dbd5739';
            var data = {
                rule_id : lib.getParam('rule_id'),
                field_name : field_name
            };

            lib.post(url, data, function(html) {
                $('#oper_div').html(html);
            }, {
                type : 'text',
                loading : true
            });
        },
        addView : function(data) {
            var url = '/diyEdit/addView?tableId=4424e428-3ac7-e981-ec28-96592dbd5739';
            lib.post(url, data, function(html) {
                $('#oper_div').html(html);
            }, {
                type : 'text',
                loading : true
            });
        },
        editRule : function(rule_id) {
            var url = '/diyEdit/editView?tableId=4eecf423-631b-6210-78ee-8c2f45fa9d6d&rule_id=' + rule_id;
            lib.get(url, function(html) {
                $('#oper_div').html(html);
            }, {
                loading : true,
                type : 'text'
            });
        },
        del : function(field_name) {
            var url = '/diyEdit/del?tableId=4424e428-3ac7-e981-ec28-96592dbd5739';
            var data = {
                rule_id : lib.getParam('rule_id'),
                field_name : field_name
            };

            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    lib.showTip(objResult.msg);
                    $('[data-field_name="' + data.field_name + '"]').remove();
                    if (_parent_rule) {
                        M.refresh();
                    }
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            }, {
                loading : true
            });
        },
        refresh : function() {
            var rule_id = lib.getParam('rule_id');

            $('#rule_js').remove();
            $('body').append('<script src="/crawl/ruleJs?rule_id=' + rule_id + '&t=' + (new Date).getTime() + '" id="rule_js" type="text/javascript"></script>');

            var url = "/crawl/ruleItems?rule_id=" + rule_id;
            lib.get(url, function(objResult) {
                _items = objResult.data;
                V.init();
            }, {
                loading : true
            });
        },
        clearViewCache : function() {
            var url = "/crawl/clearViewCache";
            var data = {
                url : $('#btn_source').attr('href') || '',
                rule_id : lib.getParam('rule_id')
            };

            lib.post(url, data, function(objResult) {
                if (objResult.result) {
                    lib.showTip(objResult.msg);
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            }, {
                loading : true
            } );

            return false;
        }
    };

    var C = {
        init : function() {
            lib.showLoading();
            var timeoutHandler = setTimeout(_iframeInit, 2000);
            $("iframe.bottomEx").on('load', _iframeInit);

            function _iframeInit() {
                lib.hideLoading();
                if (timeoutHandler) {
                    iframeDoc = $("iframe.bottomEx")[0].contentDocument.documentElement;
                    iframeWin = $("iframe.bottomEx")[0].contentWindow;
                    // $(this).css('height', iframeDoc.scrollHeight);
                    C.initIframeEvent(iframeDoc);
                    V.init();

                    // 添加样式
                    var link1 = "<link href='/static/css/crawl/pageCrawlView-highlight.css' rel='stylesheet'>";
                    $(iframeDoc).find('head').append(link1);
                } else {
                    clearTimeout(timeoutHandler);
                    timeoutHandler = 0;
                }
            }

            $(".headLine").draggable({
                stop: function(event, ui) {
                    C._resize();
                },
                axis: 'y',
                distance:0,
                iframeFix:true,
                cancel:'a,span'
            });

            $('#btn_clear_cache').on(BDY.click, M.clearViewCache);

            $(document).on(BDY.click, '.column .glyphicon-edit', function() {
                var field_name = $(this).parent().find('span').attr('title');
                M.editView(field_name);
            });

            $(document).on(BDY.click, '.column .glyphicon-trash', function() {
                var field_name = $(this).parent().find('span').attr('title');
                if (!confirm("确定要删除" + field_name + "吗？")) {
                    return false;
                }

                M.del(field_name);
            });

            $(document).on(BDY.click, '#btn_edit_rule', function() {
                M.editRule(lib.getParam('rule_id'));
            });

            $(document).on(BDY.click, '#btn_add_item', function() {
                M.addView({
                    rule_id : _task.rule_id
                });
            });

            $(document).on(BDY.click, '.thead span', function() {
                var $el = $('[data-hicms_field_name="' + $(this).attr('title') +'"]', iframeDoc);
                $el.addClass('hicms_blink');
                setTimeout(function() {
                    $('.hicms_blink', iframeDoc).removeClass('hicms_blink');
                }, 6000);

                iframeDoc.scrollTo(0, $el.offset().top - 100);
            });

            $(window).on('resize', C._resize);
            $(document).on('diy_edit_save', M.refresh);
            $(document).on('diy_edit_add', M.refresh);
        },
        _resize : function() {
            var top = $(".headLine").position().top;
            var height = $('body').height() - top;
            $(".bottom.show, .tableContainer").css("height", height);
            $(".tableContainer .tbody").css("max-height", height - 90);
            $("iframe.bottomEx").css("padding-bottom", height);
        },
        initIframeEvent : function() {
            $(iframeDoc).on('mousemove', function(event) {
                $('.hicms_current', iframeDoc).removeClass('hicms_current');
                var el = event.target;
                $(el).addClass('hicms_current').off('click').on('click', function() {
                    $('.hicms_blink').removeClass('hicms_blink');
                    var $el = $(event.target);
                    var field_name = $el.attr('data-hicms_field_name');
                    var selector = $el.attr('data-hicms_selector');
                    if (field_name) {
                        $('[data-field_name="' + field_name + '"').addClass('hicms_blink');
                        setTimeout(function() {
                            $('.hicms_blink').removeClass('hicms_blink');
                        }, 3000);
                    }

                    var value = _getSelectorList($el);
                    var html = tpl.render('tip_tpl', value);
                    $('#oper_div').html(html);
                    C._initDialogEvent(selector);

                    return false;
                });
            });

            var map = {};
            $('img', iframeDoc).off('error').on('error', function() {
                map[this.src] = map[this.src] || 1;
                map[this.src]++;
                if (map[this.src] > 10) {
                    $(this).attr('old_err_src', this.src);
                    this.src = '/static/images/loading.gif';
                }
            });

            iframeDoc.top = null;
        },
        _initDialogEvent : function(selector) {
            $('#oper_div .modal').modal({
                'show' : true
            }).find('.modal-dialog').css('margin', '10px').draggable();
            if (selector) {
                $('#oper_div .modal').find('span[title="' + selector + '"]').addClass('selected');
            }

            $('.ul-selector>li>span').mouseenter(function() {
                $('.hicms_current', iframeDoc).removeClass('hicms_current');
                $($(this).text(), iframeDoc).addClass('hicms_current');
            }).click(function() {
                var $el = $($(this).text(), iframeDoc);
                var value = _getSelectorValue($el);

                var html = tpl.render('value_tpl', {value:value});
                $(this).parent().append(html);
            });

            $('.ul-selector').on(BDY.click, '.ul-value>li>span', function() {
                var $pLi = $(this).parent().parent().parent();
                var selector = $pLi.find('[data-selector]').text();
                var fetch_value = $(this).text();
                var is_multi = $pLi.attr('data-multi') ? 1 : 0;
                M.addView({
                    selector : selector,
                    fetch_value : fetch_value,
                    is_multi : is_multi,
                    rule_id : _task.rule_id
                });
            });
        },

    };

    var V = {
        init : function() {
            V._preprocess();

            var $el = null;
            if (_rule.data_type === 'json') {
                // $el = $(iframeDoc).text();
                $el = iframeWin.$el;
            }

            var diff = V.getDiff();

            _fetchArr($el, _items);
            _fetchArr($el, diff);

            V.initTips(diff);
            V.initItemData(diff);
        },
        getDiff : function() {
            if (!_parent_items || !_items) {
                return _parent_items;
            }

            var diff = {};
            for (var i in _parent_items) {
                var item = _parent_items[i];
                diff[item['field_name']] = item;
            }

            for (var i in _items) {
                delete diff[_items[i]['field_name']];
            }

            return diff;
        },
        initTips : function(diff) {
            var needAddItems = Object.assign({}, _requireItems);

            var arr = [_items, diff];
            for (var i in arr) {
                if (!arr[i]) continue;
                for (var j in arr[i]) {
                    var item = arr[i][j];
                    delete needAddItems[item['field_name']];
                }
            }

            var data = {'requireItems':[],'optionItems':[]};
            for (var key in needAddItems) {
                if (needAddItems[key]) {
                    data.requireItems.push(key);
                } else {
                    data.optionItems.push(key);
                }
            }

            var html = tpl.render('input_tips_tpl', data);
            $('#input_tips').html(html);
        },
        initItemData : function(diff) {
            var $table = $('.tableContainer .table');
            var html = tpl.render('column_tpl', {items:_items, parent_rule:null});
            $table.html(html);

            if (diff) {
                html = tpl.render('column_tpl', {items:diff, parent_rule:_parent_rule});
                $table.append(html);
            }
        },
        _preprocess : function() {
            if (_rule.preprocess) {
                var $html = $(iframeDoc).filter('html');
                if (_rule.data_type === 'json') {
                    $html = $html.text();
                }

                try {
                    // var func = new Function('$html', '$', 'page', _rule.preprocess);
                    __rulePreProcess($html, $, null);
                } catch (ex) {
                    console.error(ex);
                }
            }
        }
    };

    function _getSelectorList($el) {
        var $target = $el;
        var nodeName = $el[0].nodeName.toLowerCase();
        var value = {'single':[], 'multi':[nodeName]};
        _parseElem($el, value, '');
        value.multi.push();
        var i = 0;
        while ($el && $el.parent() && i++ < 8) {
            $el = $el.parent();
            var sLen = value.single.length;
            var mLen = value.multi.length;

            if (sLen + mLen > 15) {
                break;
            }

            // for (var j = 0; j < sLen; j++ ) {
            //     _parseElem($el.parent(), value, ' ' + value.single[j]);
            // }

            for (j = 0; j < mLen; j++ ) {
                _parseElem($el.parent(), value, ' ' + value.multi[j]);
            }
        }
        value.multi.shift();
        value.single = value.single.slice(0, 5);
        value.multi = value.multi.slice(0, 10);

        if (value.multi.length > 0) {
            var index = $(value.multi[0], iframeDoc).index($target[0]);
            value.single.push(value.multi[0] + ":nth(" + index + ")");
            var elText = $target.text();
            if (elText.length < 20) {
                value.single.push(value.multi[0] + ":contains('" + elText + "')");
            }
        }

        return value;
    }

    function _parseElem($el, value, postFix) {
        // var value = {'single':[], 'multi':[]};
        // 检查自身的属性
        var id = $el.attr('id');
        id && _parseSelector(value, '#' + id + postFix);

        // 检查name
        var selector = '';
        var name = $el.attr('name');
        if (name) {
            selector = '[name="' + name + '"]' + postFix;
            _parseSelector(value, selector);
        }

        // 检查data属性
        var data = $el.data();
        for (var key in data) {
            if (key.indexOf('hicms_') !== 0) {
                selector = '[data-' + key + ']' + postFix;
                _parseSelector(value, selector);
            }
        }

        // 检查class
        var className = $el.attr('class');
        if (className) {
            var classNames = className.split(' ');
            var parts = [];
            for (var i in classNames) {
                if (classNames[i] && classNames[i].indexOf('hicms_') !== 0) {
                    selector = '.' + classNames[i] + postFix;
                    _parseSelector(value, selector);
                    parts.push(classNames[i]);
                }
            }
        }

        return value;
    }

    function _parseSelector(value, selector) {
        if (!selector) { return }
        var len = $(selector, iframeDoc).length;
        if (len > 1) {
            value.multi.push(selector);
        } else if (len === 1) {
            value.single.push(selector);
        }
    }

    function _getSelectorValue($el) {
        $('.ul-value').remove();

        var value = [];
        var item = {};
        // 检查data属性
        var data = $el.data();
        for (var key in data) {
            if (key.indexOf('hicms_') !== 0) {
                item = { fetch_value : "return $el.attr('data-" + key + "').trim();" };
                value.push(_fetch($el, item));
            }
        }

        var attrs = ['href', 'src'];
        for (var i = 0; i < attrs.length; i++) {
            var attrValue = $el.attr(attrs[i]);
            if (attrValue) {
                item = { fetch_value : "return JTool.formatUrl($el.attr('" + attrs[i] + "'));" };
                value.push(_fetch($el, item));
            }
        }

        if ($el[0].nodeName === 'INPUT' || $el[0].nodeName === 'SELECT' || $el[0].nodeName === 'TEXTAREA') {
            item = { fetch_value : "return $el.val().trim();" };
            value.push(_fetch($el, item));
        }

        item = { fetch_value : "return $el.text().trim();" };
        value.push(_fetch($el, item));
        item = { fetch_value : "var html = $el.html();\nreturn JTool.formatRichText(html.trim());" };
        value.push(_fetch($el, item));

        var jsCode = "var $outHtml = $('<div><\/div>');\n" +
                   "return $outHtml.append($el.clone()).html();";
        item = { fetch_value : jsCode };
        value.push(_fetch($el, item));

        return value;
    }

    function _fetchArr($el, items) {
        // 标记items
        for (var i in items) {
            var item = items[i];
            item.is_multi = parseInt(item.is_multi);
            try {
                var $selector = $(item['selector'], iframeDoc);
                var j = i % 5;
                item['bg'] = 'hicms_bg' + j;
                $selector.addClass('hicms_edit ' + item['bg']);
                $selector.attr('data-hicms_field_name', item['field_name']);
                $selector.attr('data-hicms_selector', item['selector']);

                if (_rule.data_type === 'html') {
                    $el = $selector;
                }

                _fetch($el, item);
                items[i] = item;
            } catch (ex) {
                console.error(ex);
            }
        }
    }

    function _fetch($el, item) {
        try {
            // 获取值
            if (item.field_name) {
                item['value'] = _fetchVal2($el, item, false);
            } else {
                item['value'] = _fetchVal($el, item.fetch_value, item.is_multi);
            }


            if (item.new_task_key) {
                // item['task_key_value'] = _fetchVal($el, item.new_task_key, item.is_multi);
                item['task_key_value'] = _fetchVal2($el, item, true);
            }

            return item;
        } catch (ex) {
            console.error(ex);
            return ' 解析$el: 这里写错啦！error msg:' + ex.message;
        }

    }

    function _fetchVal2($el, item, iskey) {
        var key = iskey ? item.field_name + '-new_task_key' : item.field_name;
        var func = __crawPage[key];
        if (!func) {
            console.error('can not find:' + key);
        }

        if (item.is_multi && _rule.data_type === 'html') {
            var value = [];
            for (var i = 0; i < $el.length; i++) {
                value[i] = func($($el[i]), $, _task);
            }
            return value;
        } else {
            return func($el, $, _task);
        }
    }

    function _fetchVal($el, funcValue, is_multi) {
        var func = new Function("$el", '$', '_task', funcValue);
        if (is_multi && _rule.data_type === 'html') {
            var value = [];
            for (var i = 0; i < $el.length; i++) {
                value[i] = func($($el[i]), $, _task);
            }
            return value;
        } else {
            return func($el, $, _task);
        }
    }

    C.init();

    var detailConf = {
        'author' : 0,
        'content' : 0,
        'create_time' : 0,
        'game_id' : 1,
        'source_url' : 1,
        'title' : 1,
        'type' : 1
    };

    function init(task, items, rule, parent_items, parent_rule) {
        _task = task;
        _items = items;
        _rule = rule;
        _parent_items = parent_items;
        _parent_rule = parent_rule;

        JTool.initUrl(task.url);
        JTool.initJquery(jQuery);

        var type = '';
        var matches = rule.rule_id.match(/ka:\d+:([^:_\-]+_[^:_\-]+)/);
        if (matches) {
            type = matches[1];
        }

        var map = {
            'normal_list':{
                'tag' : 0,
                'source_url' : 0
            },
            'image_list': {
                'cover' : 1,
                'source_url' : 1,
                'tag' : 0
            },
            'video_list':{
                'cover' : 1,
                'source_url' : 1,
                'tag' : 0
            },
            'normal_detail': Object.assign({'content' : 1}, detailConf),
            'image_detail': Object.assign({'content' : 1}, detailConf),
            'video_detail': Object.assign({'video_html' : 1}, detailConf),
        };

        _requireItems = map[type];
    }

    exports.init = init;

});

