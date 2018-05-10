define('lib', function(require, exports, module) {
    var $ = require('jquery');
    var dialog = require('dialog');
    var NProgress = require('nprogress');

    exports.getParam = getParam;
    exports.getParam2 = getParam2;
    exports.setParam = setParam;
    exports.setParam2 = setParam2;
    exports.removeParam = removeParam;

    exports.getCookie = getCookie;

    exports.setTimeout = _setTimeout;
    exports.setInterval = _setInterval;
    exports.clearTimer = _clearTimer;

    exports.redirect = redirect;
    exports.redirect2 = redirect2;
    exports.historyBack = historyBack;
    exports.setNoCache = setNoCache;

    exports.showTip = showTip;
    exports.showErrorTip = showErrorTip;
    exports.showLoading = showLoading;
    exports.hideLoading = hideLoading;
    exports.confirm = _confirm;

    exports.bindEvent = bindEvent;

    exports.hmtTrackPage = hmtTrackPage;
    exports.hmtCustomVar = hmtCustomVar;
    exports.hmtTrackEvent = hmtTrackEvent;

    exports.post = post;
    exports.get = get;

    exports.init = init;

    var isForward = true;
    var loadDialog;

    //+++++++++++++++++++++++++++ hash 参数控制 +++++++++++++++++++++++++++++++++++++
    /**
     * js获取url参数的值，(函数内部decodeURIComponent值)
     * @author benzhan
     * @param {string} name 参数名
     * @return {string} 参数值
     */
    function getParam(name) {
        //先获取#后面的参数
        var str = document.location.hash.substr(2);
        var value = getParam2(name, str);
        if (value == null) {
            str = document.location.search.substr(1);
            value = getParam2(name, str);
        }

        return value;
    };

    function getParam2(name, str) {
        //获取参数name的值
        var reg = new RegExp("(^|!|&|\\?)" + name + "=([^&]*)(&|$)");

        //再获取?后面的参数
        r = str.match(reg);
        if (r != null) {
            try {
                return decodeURIComponent(r[2]);
            } catch (e) {
                // console.log(e + "r[2]:" + r[2]);
                return null;
            }
        }
        return null;
    }

    var paramTimeHandler;

    /**
     * js设置url中hash参数的值, (函数内部encodeURIComponent传入的value参数)
     * @author benzhan
     * @param {string} name 参数名
     * @return {string} value 参数值
     */
    function setParam(name, value, causeHistory) {
        var search = location['data-search'] || location.search;
        search = search.substr(1);
        if ($.type(name) === "object") {
            // 支持 setParam(value, causeHistory)的写法
            causeHistory = value;
            value = name;

            for (var key in value) {
            	search = setParam2(key, value[key], search);
            }
        } else {
        	search = setParam2(name, value, search);
        }

        location['data-search'] = search;
        if (causeHistory) {
        	if (history.pushState) {
        		history.pushState({}, null, "?" + search);
        	} else {
        		document.location.search = search;
        	}
        } else {
            if (history.replaceState) {
                history.replaceState({}, null, "?" + search);
            } else {
                paramTimeHandler && clearTimeout(paramTimeHandler);
                paramTimeHandler = setTimeout(function() {
                    location.search = location['data-search'];
                    paramTimeHandler = 0;
                }, 100);
            }
        }
    };

    function setParam2(name, value, str) {
        if ($.type(name) === "object") {
            // 支持 setParam(value, causeHistory)的写法
            str = value;
            value = name;
            for (var key in value) {
               str = setParam2(key, value[key], str);
            }
            return str;
        } else {
            var prefix = str ? "&" : "";
            var reg = new RegExp("(^|!|&|\\?)" + name + "=([^&]*)(&|$)");
            r = str.match(reg);
            value = encodeURIComponent(value);
            if (r) {
                if (r[2]) {
                    var newValue = r[0].replace(r[2], value);
                    str = str.replace(r[0], newValue);
                } else {
                    var newValue = prefix + name + "=" + value + "&";
                    str = str.replace(r[0], newValue);
                }
            } else {
                var newValue = prefix + name + "=" + value;
                str += newValue;
            }

            return str;
        }
    }

    /**
     * 删除锚点后的某个参数
     * @author benzhan
     * @param {string} name 参数名
     */
    function removeParam(name, causeHistory) {
        var search = location['data-search'] || location.search;
        search = search.substr(1);

        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        r = search.match(reg);
        if (r) {
        	if (r[1] && r[3]) {
        		search = search.replace(r[0], '&');
        	} else {
        		search = search.replace(r[0], '');
        	}
        }

        location['data-search'] = search;
        if (causeHistory) {
            location.search = search;
        } else {
            if (history.replaceState) {
                history.replaceState({}, null, "?" + search);
            } else {
                paramTimeHandler && clearTimeout(paramTimeHandler);
                paramTimeHandler = setTimeout(function() {
                    location.search = location['data-search'];
                    paramTimeHandler = 0;
                }, 100);
            }
        }
    };

 // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


    function getCookie(name) {
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
        if(arr=document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    }

    /**
     * 设置定时器
     * @param callback 定时器触发的函数
     * @param timeout 执行callback前的时间
     */
    function _setTimeout(callback, timeout) {
        var timer = setTimeout(callback, timeout);
        BDY.timeout.push(timer);
        return timer;
    }

    function _setInterval(callback, timeout) {
        var timer = setInterval(callback, timeout);
        BDY.interval.push(timer);
        return timer;
    }

    // 清除页面定时器
    function _clearTimer() {
        var len;
        var timeout = BDY.timeout;
        var interval = BDY.interval;
        len = timeout.length;
        for (var i = 0; i < len; i++) {
            clearTimeout(timeout[i]);
        }

        len = interval.length;
        for (var i = 0; i < len; i++) {
            clearInterval(interval[i]);
        }

        // 重置timer
        BDY.timeout = [];
        BDY.interval = [];
    }


    // ++++++++++++++++++++++++++++++++ 业务逻辑 +++++++++++++++++++++++++++++++++++++
    var pages = {}, oldPage, noCachePages = {};

    function init() {
        _initEvent();

        var page = location.href.replace('http://' + location.host, '');
        $("#container").attr('data-page', page);
    }

    function setNoCache(url) {
        url = url || location.href;
        url = url.replace('http://' + location.host, '');
        noCachePages[url] = 1;
    }

    function _initEvent() {
        // 点击事件
        $('body').off(BDY.click + ".data-href").on(BDY.click + ".data-href", '[data-href]', function() {
            var $this = $(this);
            var href = $this.attr('data-href') || $this.attr('href');
            var replace = $this.attr('data-replace');
            if (replace != null) {
                redirect2(href);
            } else {
                redirect(href);
            }

            return false;
        });

        // 点击上报
        $('body').off(BDY.click + ".data-report").on(BDY.click + ".data-report", '[data-report]', function() {
            var $this = $(this);
            var page = document.title;
            var event = 'click_' + $this.attr('report-name');
            hmtTrackEvent(page, event, "", "");
        });

        _initDataIframe();
        // iframe里面的跳转都仅更新iframe
        $('[data-iframe]').on('click', '[href],[data-href]', function() {
            var $this = $(this);
            var $parent = $this.parents('[data-iframe]');
            var url = $this.attr('data-href') || $this.attr('href');
            if (url) {
                get(url, function(html) {
                    $parent.html(html);
                }, {
                    type : 'text',
                    loading : true
                });
            }

            return false;
        });

        // 支持onpopstate
        window.onpopstate = function(event) {
    	    // alert("location: " + document.location + ", state: " + JSON.stringify(event.state));
    	    _redirectPage(document.location.href);
    	};

    }

    function historyBack() {
        var curPage = $("#container").attr('data-page');
        pages[curPage] = pages[curPage] || {};
        pages[curPage]['scrollTop'] = 0;
        history.back();
    }

    function redirect(url) {
    	oldPage = $("#container").attr('data-page');
        pages[oldPage] = pages[oldPage] || {};
        pages[oldPage]['scrollTop'] = _getScrollTop();
        pages[url] = pages[url] || {};
        pages[url]['scrollTop'] = 0;
        isForward = true;

        if (history.pushState) {
            if (oldPage != url) {
                history.pushState({}, '', url);
            }
            _redirectPage(url);
        } else {
            location.href  = url;
        }
    }

    function redirect2(url) {
    	oldPage = $("#container").attr('data-page');
        pages[oldPage] = pages[oldPage] || {};
        pages[oldPage]['scrollTop'] = _getScrollTop();
        pages[url] = pages[url] || {};
        pages[url]['scrollTop'] = 0;
        isForward = true;

        if(history.replaceState) {
            history.replaceState({}, '', url);
            _redirectPage(url);
        } else {
            location.href = url;
        }
    }

    function _getScrollTop() {
        return document.documentElement.scrollTop || document.body && document.body.scrollTop;
    }

    function bindEvent(callback) {
        var page = location.href.replace('http://' + location.host, '');
        pages[page] = pages[page] || {};

        var $child = $("#container>:first-child");
        if ($child.attr('_ouj_init')) { return }
        callback && callback();

        $child.attr('_ouj_init', true);
    }

    function _redirectPage(_page) {
        // 去掉域名前缀
        _page = _page.replace('http://' + location.host, '');
        pages[_page] = pages[_page] || {};

        NProgress.start();

        if (!pages[_page]['dom']) {
            _getNewPage(_page);
        } else {
            _changePage(_page);
        }
    }

    function _getNewPage(_page, option) {
        var connect = _page.indexOf('?') == -1 ? '?' : '&';
        var url = _page + connect + '_rand=' + Math.random();

        get(url, function(responseText) {
            //如果有页面信息时才缓存
            pages[_page]['dom'] = responseText;
            _changePage(_page, option);
            _initDataIframe();
        }, {
            'type' : 'text'
        });
    }

    function _initDataIframe() {
        // 第一次加载
        $('[data-iframe]').each(function() {
            var $this = $(this);
            var url = $this.attr('data-iframe');
            if (!$this.attr('init') && url) {
                get(url, function(html) {
                    $this.attr('init', true);
                    $this.html(html);
                }, {
                    type : 'text'
                });
            }
        });
    }

    function _changePage(_page) {
        var $pageswarp = $("#container");
        var $page = $pageswarp.children();
        if (oldPage && $page.length && !noCachePages[oldPage]) {
            pages[oldPage] = pages[oldPage] || {};
            //保存滚动条位置$pageswarp
            // pages[oldPage]['scrollTop'] = document.body.scrollTop;
            //console.log("oldPage:" + oldPage + ", scrollTop:" + pages[oldPage]['scrollTop']);
            // pages[oldPage]['height'] = $pageswarp.height();
            pages[oldPage]['script'] = $page.filter("script");

            var $fragment = $(document.createDocumentFragment());
            pages[oldPage]['dom'] = $fragment.append($page.filter("div"));

            _clearTimer();
            $(window).off("scroll");
        }

        _renderPage(_page);
        hmtTrackPage(document.location.href);

        NProgress.done();
        $(document).trigger(BDY.pageChange, _page);
        oldPage = _page;
    }

    function _renderPage(_page) {
        var $pageswarp = $("#container");
        //回调放在页面js的init方法之前执行，不然会覆盖init里面的setParam
        try {
            var dom = pages[_page]['dom'];
            if ( typeof dom == 'string') {
                scrollTo(0, 0);
                $pageswarp.html(dom);
                pages[_page]['dom'] = null;
            } else {
                //还原高度、滚动条信息
                // $pageswarp.height(pages[_page]['height']);
                var scrollTop = pages[_page]['scrollTop'];
                scrollTo(0, scrollTop);

                //还原dom元素
                $pageswarp.html(dom);
                var script = pages[_page]['script'];
                script && $pageswarp.append(script);
            }

            // 设置页面标识
            $pageswarp.attr('data-page', _page);
        } catch(e) {
            if (BDY.debug) {
                throw e;
            } else {
                console.error(e);
            }
        }
    }

    var loadingDelayHandler = 0;
    var loadingTimeoutHandler = 0;

    function showLoading(text, timeout, cancelable, delay) {
    	// 超时时间为15s
        timeout = timeout || 15000;
        // 0.5s后才显示loading
        delay = delay || 500;

        if (cancelable == null) {
            cancelable = true;
        } else {
            cancelable = !!cancelable;
        }

        if (loadingDelayHandler) {
        	return;
        }

        loadingDelayHandler = setTimeout(function() {
            loadDialog = dialog().showModal();
            loadingTimeoutHandler = setTimeout(function(){
                hideLoading();
                showTip("加载超时，请稍后再试");
            }, timeout);

            $(".ui-popup-backdrop").on(BDY.click, function(){
                loadDialog && loadDialog.close().remove();
                loadDialog = null;
            });

        }, delay);
    }

    function hideLoading() {
    	loadingDelayHandler && clearTimeout(loadingDelayHandler);
    	loadingDelayHandler = 0;
    	loadingTimeoutHandler && clearTimeout(loadingTimeoutHandler);
    	loadingTimeoutHandler = 0;
        loadDialog && loadDialog.close().remove();
        loadDialog = null;
    }

    function showErrorTip(msg, timeout) {
        if (!msg) { return; }
        timeout = timeout || 3000;
        var d = dialog({
            content : msg
        }).showModal();

        //2秒后自动关闭
        setTimeout(function(){
            d.close().remove();
        },timeout);
    }

    function showTip(msg, timeout) {
        if (!msg) { return; }
        timeout = timeout || 2000;

        var d = dialog({
            title : "提示",
            content : msg,
            skin : "base-ui"
        }).showModal();

        //2秒后自动关闭
        setTimeout(function(){
            d.close().remove();
        },timeout);

        $(".ui-popup-backdrop").on(BDY.click, function(){
            try{
                d.close().remove();
            }catch(e){}
        });

    }


    function _confirm(msg, confirmCallback, cancelCallback, title, buttonLabels) {
        title = title || "提示";
        buttonLabels = buttonLabels || "确定,取消";
        if (confirm(msg)) {
            confirmCallback && confirmCallback(1);
        } else {
            cancelCallback && cancelCallback(2);
        }
    }

    //设置签名
    function setSign(obj, md5) {
        var keyArr = new Array();
        var keySign = obj["app_id"];
        for(var a in obj) {
            keyArr.push(a);
        }
        keyArr.sort();
        for(var i = 0; i < keyArr.length; i++) {
            keySign += ( i == 0 ? "" : "&") + keyArr[i] + "=" + encodeURIComponent(obj[keyArr[i]])
        }
        return md5.hex_md5(keySign);
    }

    function reportPage() {
        var param = {
            app_id : 2,
            page : document.title,
            random : Math.random()
        }

        var user_id = getLocalData("user_id");
        if(user_id && user_id != "undefined") {
            param['user_id'] = user_id;
        }

        // 异步加载md5
        require.async('md5', function(md5) {
        	//签名
            param['sign'] = setSign(param, md5);
            var reportUrl = BDY.shopApiUrl + "data/reportPage?" + $.param(param);
            var rp = new Image(1, 1);
            rp.src = reportUrl;
        });
    }

    function reportEvent(category, action, content) {
        var param = {
            app_id : 2,
            category : category,
            action : action,
            content : content,
            random : Math.random()
        }

        var user_id = getLocalData("user_id");
        if(user_id && user_id != "undefined") {
            param['user_id'] = user_id;
        }

        require.async('md5', function(md5) {
            //签名
            param['sign'] = setSign(param, md5);

            var reportUrl = BDY.shopApiUrl + "data/reportEvent?" + $.param(param);

            var rp = new Image(1, 1);
            rp.src = reportUrl;
        });
    }

    function hmtTrackPage(pageURL) {
        try{
            _hmt.push(['_trackPageview', pageURL]);
        }catch(e) {

        }
    }

    function hmtCustomVar(index, name, value, opt_scope) {
        try{
            _hmt.push(['_setCustomVar', index, name, value, opt_scope]);
            reportPage()
        }catch(e) {

        }
    }

    function hmtTrackEvent(category, action, opt_label, opt_value) {
        try{
            _hmt.push(['_trackEvent', category, action, opt_label, opt_value]);
            reportEvent(category, action, opt_value);
        }catch(e) {

        }
    }

    function _reportError(msg, url, line) {
        var txt="Error: " + msg + "\n"
        txt+="Line: " + line + "\n\n"
        txt+="URL: " + url + "\n"
        hmtTrackEvent("error", getParam("page"), "", txt);
        console.error(txt);
        return true;
    }

    //测试环境捕捉全局异常错误并提示
    if (!BDY.debug) {
        window.onerror = _reportError;
    }

    // ------------------------ ajax相关 --------------------------

    function openLogin() {
        // location.href = "lolbox://LoginYY";
    }

    /**
     * option object {'cache', 'loading', 'onTimeout'}
     * cache为every、once
     */
    function post(url, data, callback, option) {
        option = option || {};
        // 支持postCross(url, callback)的写法;
        if ( typeof data == 'function') {
            option = callback || {};
            callback = data;
            data = {};
        }

        data['_from'] = 'ajax';

        //获取cache的key
        if (option['cache'] && window.localStorage) {
            var cacheKey = 'ajaxCache-' + url + '?' + $.param(data);
            //如果有cache则不出现loading
            var isCached = _loadAjaxCache(cacheKey, callback);
            if (isCached) {
                option['loading'] = false;
                if (option['cache'] == "must") {
                    return;
                }
            }
        }

        if (option['loading']) {
            showLoading();
        }

        $.post(url, data, function(text) {
            option['loading'] && hideLoading();
            if (option['type'] == 'text') {
                callback(text);
            } else {
                var objResult = _getAjaxResult(text);
                if (objResult['result'] && option['cache'] && window.localStorage) {
                    var cache = localStorage.getItem(cacheKey);
                    if (cache && cache == text) {
                        // 网络返回跟缓存一致
                        return;
                    } else {
                        localStorage.setItem(cacheKey, xhr.responseText);
                    }
                }

                _handleResult(callback, objResult);
            }
        }, 'text');

    }

    function get(url, data, callback, option) {
        option = option || {};

        // 支持postCross(url, callback)的写法;
        if (typeof data == 'function') {
            option = callback || {};
            callback = data;
            data = {};
        }

        option['loading'] && showLoading();
        if (/#|\?/.test(url)) {
            url += "&_from=ajax";
        } else {
            url += "?_from=ajax";
        }

        $.get(url, data, function(text) {
            option['loading'] && hideLoading();
            if (option['type'] == 'text') {
                callback(text);
            } else {
                var objResult = _getAjaxResult(text);
                callback(objResult);
            }
        }, 'text');

    }

    function _getAjaxResult(text) {
        var objResult = {};
        var objError = {
            result : 0,
            msg : "系统繁忙，请稍后再试！"
        };

        try {
            objResult = JSON.parse(text);
            if (typeof objResult !== 'object' || objResult === null) {
                objResult = objError;
            }
        } catch (ex) {
            //非json的处理
            objResult = objError;
        }

        return objResult;
    }

    function _loadAjaxCache(cacheKey, callback) {
        var cache = localStorage.getItem(cacheKey);
        if (cache) {
            var objResult = JSON.parse(cache);
            objResult._fromCache = true;
            _handleResult(callback, objResult);

            return true;
        } else {
            return false;
        }
    }

    function _handleResult(callback, objResult) {
        //session_id失效，重新验证登录
        if(!objResult.result && objResult.code == -5) {
            openLogin();
        } else {
            callback && callback(objResult);
        }
    }

    // 调用init
    init();
});

