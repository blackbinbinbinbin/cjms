define(function(require, exports, module) {
	require('jquery');

    exports.getParam = getParam;
    exports.getParam2 = getParam2;
    exports.setParam = setParam;
    exports.setParam2 = setParam2;
    exports.removeParam = removeParam;
    exports.removeParam2 = removeParam2;
    
    exports.post = post;
    exports.get = get;
    exports.getLocalData = getLocalData;
    
    exports.setTimeout = _setTimeout;
    exports.setInterval = _setInterval;
    
    exports.redirect = redirect;
    exports.redirectClient = redirectClient;
    exports.onNavBtnClick = onNavBtnClick;
    exports.historyBack = historyBack;
    BDY.historyBack = historyBack;
    exports.openUrl = openUrl;
    
    exports.showTip = showTip;
    exports.showErrorTip = showErrorTip;
    exports.showLoading = showLoading;
    exports.hideLoading = hideLoading;
    exports.getLoadingDiv = getLoadingDiv;
    exports.showDialog = showDialog;
    exports.showSelectDialog = showSelectDialog;
    exports.confirm = _confirm;
    exports.prompt = _prompt;
    
    exports.openLogin = openLogin;
    exports.reportPv = reportPv;
    exports.template = template;
    exports.initSelect2 = initSelect2;
    
    exports.init = init;

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
        var reg = new RegExp("(^|!|&)" + name + "=([^&]*)(&|$)");

        //再获取?后面的参数
        r = str.match(reg);
        if (r != null) {
            try {
                return decodeURIComponent(r[2]);
            } catch (e) {
                console.log(e + "r[2]:" + r[2]);
                return null;
            }
        }
        return null;
    }

    /**
     * js设置url中hash参数的值, (函数内部encodeURIComponent传入的value参数)
     * @author benzhan
     * @param {string} name 参数名
     * @return {string} value 参数值
     */
    function setParam(name, value, causeHistory) {
        var hash = document.location.hash.substr(2);
        if ($.isPlainObject(name)) {
            // 支持 setParam(value, causeHistory)的写法
            causeHistory = value;
            value = name;
            
            for (var key in value) {
               hash = setParam2(key, value[key], hash);
            }
        } else {
            hash = setParam2(name, value, hash);
        }

        if (causeHistory) {
            document.location.hash = "!" + hash;
        } else {
            if (history.replaceState) {
                history.replaceState({}, null, "#!" + hash);
            } else {
                window.console && console.log("history.replaceState:" + history.replaceState);
            }
        }
    };
    
    function setParam2(name, value, str) {
        if ($.isPlainObject(name)) {
            // 支持 setParam(value, causeHistory)的写法
            str = value;
            value = name;
            for (var key in value) {
               str = setParam2(key, value[key], str);
            }
            return str;
        } else {
            var prefix = str ? "&" : "";
            var reg = new RegExp("(^|!|&)" + name + "=([^&]*)(&|$)");
            r = str.match(reg);
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
        var hash = document.location.hash.substr(2);
        hash = removeParam2(name, hash);
        
        if (causeHistory) {
            document.location.hash = "!" + hash;
        } else {
            if (history.replaceState) {
            	history.replaceState({}, null, "#!" + hash);
            } else {
                window.console && console.error("history.replaceState:" + history.replaceState);
            }
        }
    };
    
    /**
     * 删除锚点后的某个参数
     * @author benzhan
     * @param {string} name 参数名
     */
    function removeParam2(name, hash) {
        if (typeof name == 'object') {
            for (var i in name) {
                hash = removeParam2(name[i], hash);
            }
        } else {
            var reg = new RegExp("(^|!|#|&)" + name + "=([^&]*)(&|$)");
            r = hash.match(reg);
            if (r) {
                hash = hash.replace(r[0], r[1]);
            }
        }

        if (hash[hash.length - 1] == '&'){
            hash = hash.substr(0, hash.length - 1);
        }
        
        return hash;
    };
    
    function parseHash(strUrl) {
        strUrl = strUrl ? strUrl : document.location.hash.substr(2);
        
        //获取参数name的值
        var reg = new RegExp("(^|!|#|&)(\\w*)=([^&]*)", "g");
        
        //先获取#后面的参数
        var r = reg.exec(strUrl);
        var datas = {};
        
        var i = 0;
        while (r != null) {
            datas[r[2]] = decodeURIComponent(r[3]);
            r = reg.exec(strUrl);            
        }
        
        return datas;
    };
 // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


// +++++++++++++++++++++++++++ get 和 post +++++++++++++++++++++++++++++++++++++   
    function _getDefaultData(data) {
        data = data || {};
        
        data['g_appid'] = getParam('g_appid');
        
        data['username'] = getLocalData('username');
        data['password'] = getLocalData('password');
        data['osinfo'] = getLocalData('osinfo');
        data['oauthCookie'] = getLocalData('oauthCookie');
        
        // 获取验证码
        data.channel = getParam('channel');
        data.platform = getParam('platform');
        
        for (var k in data) {
            var v = data[k];
            if (v == null || typeof v === 'undefined') {
                delete data[k];
            } else {
                if (typeof v === 'string') {
                    data[k] = $.trim(v);
                }
            }
        }
        
        return data;
    }

    /**
     * option object {'cache', 'loading', 'onTimeout', 'type}
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

        // data = _getDefaultData(data);
        if (window.KLCommon && KLCommon.post) {
            KLCommon.post(url, data, function(objResult) {
                _handleResult(callback, objResult);
            }, option);
        } else {
            var xhr = new XMLHttpRequest();
            xhr.withCredentials = true;
            xhr.open("POST", url);
            
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            var type = option['type'] || 'json';
            xhr.setRequestHeader("Accept-Type", type);
            // 兼容ios6.0会缓存
            if (window.KalaGame) {
                xhr.setRequestHeader("Cache-Control", "no-cache");
            }

            var str = '';
            for (var key in data) {
                str += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            data = str.substr(1);

            //console.log('post: ' + url);
            //console.log(data);
            
            if (option['loading']) {
                xhr.timeout = 15000;
                showLoading();
            }
            
            xhr.send(data);
            if (option['onTimeout'] && xhr.ontimeout) {
                xhr.ontimeout = option['onTimeout'];
            }

            // 响应处理
            xhr.onload = function() {
                option['loading'] && hideLoading();
                _handleResult(callback, xhr.responseText, option);
            };
            
        }
    }

    function get(url, data, callback, option) {
        option = option || {};
        // 支持postCross(url, callback)的写法;
        if ( typeof data == 'function') {
            option = callback || {};
            callback = data;
        } else {
        	option = option || {};

            var str = '';
            for (var key in data) {
                str += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            str = str.substr(1);
        	
        	// 构造url
            if (url.indexOf('?') >= 0) {
            	url += '&' + str;
            } else {
            	url += '?' + str;
            }
        }
        
        if (window.KLCommon && KLCommon.get) {
            KLCommon.get(url, callback, option);
        } else {
            var xhr = new XMLHttpRequest();
            xhr.url = url;
            xhr.withCredentials = true;
            option['loading'] && showLoading();
            xhr.onreadystatechange = function() {
                if (xhr.readyState != 4) {
                    return;
                }
                if ((xhr.status >= 200 && xhr.status < 300) || xhr.status == 304 || (xhr.status == 0 && location.protocol == 'file:')) {
                    option['loading'] && hideLoading();
                    _handleResult(callback, xhr.responseText, option);
                }
            };
            xhr.open("GET", url);
            var type = option['type'] || 'json';
            xhr.setRequestHeader("Accept-Type", type);
            
            xhr.send();
        }
    }

    function _handleResult(callback, text, option) {
    	if (option['type'] == 'text') {
    		var objResult = text;
    	} else {
    		var objResult = _getAjaxResult(text);
    	}
    	
        callback && callback(objResult);
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

    
    function getLocalData(key) {
        return getParam(key) || window.localStorage && localStorage.getItem(key);
    }

    // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    
    
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
    var pages = {}, oldPage;;

    function init() {
        // 这里巨坑
        if (!document.body) {
            setTimeout(init, 10);
            console.warn("webview is slow, setTimeout(init, 10)");
            return;
        }
        
        _initEvent();
        _initUrl();
    }
       
    function _initEvent() {
        // 点击事件
        $('body').off(BDY.click + ".data-href").on(BDY.click + ".data-href", '[data-href]', function() {
            var $this = $(this);
            
            var href = $this.attr('data-href') || $this.attr('href');
            // 不合理的href，不特殊处理
            if (href && href.indexOf("#!") !== -1) {
                var clientParam = $this.attr('data-client');
                if (clientParam != null) {
                    redirectClient(href, clientParam);
                } else {
                    redirect(href);
                }
                return false;
            }
        });

        $('body').off(BDY.click + ".data-container").on(BDY.click + ".data-container", '[data-container]', function() {
            var $this = $(this);
            var container = $this.attr('data-container') || 'body';
            var href = $this.attr('href');
            get(href, function(html) {
                $(container).html(html);
            }, {
                type :'text',
                loading : true
            });

            return false;
        });

        // 点击上报
        $('body').off(BDY.click + ".data-report").on(BDY.click + ".data-report", '[data-report]', function() {
            var $this = $(this);
            var page = getParam('page');
            var event = 'click_' + $this.attr('id') || $this.attr('name');
            reportPv(page, event);
        });
        
    }
   
    function _initUrl() {
        var env = getLocalData("env") || "production";
        switch (env) {
            case "test":
            case "sandbox":
                exports.url = "http://test.mgame.mbox.duowan.com/";
                break;
            case "new":
                exports.url = "http://new.mgame.mbox.duowan.com/";
                break;
            default:
                exports.url = SITE_URL;
                break;
        }
        
        // exports.url = "http://test.m.pai.duowan.com/";
    }
    
    function historyBack() {
        var curPage = getParam("page");
        pages[curPage] = pages[curPage] || {};
        pages[curPage]['scrollTop'] = 0;
        if (window.Duowan && Duowan.historyBack) {
            Duowan.historyBack();
        } else {
            history.back();
        }
    }
    
    function redirect(param) {
        oldPage = getParam("page");
        pages[oldPage] = pages[oldPage] || {};
        pages[oldPage]['scrollTop'] = _getScrollTop();
        var values = parseHash(param);
        setParam(values, true);
    }
    
    function _getScrollTop() {
        return document.documentElement.scrollTop || document.body && document.body.scrollTop;        
    }
    
    function redirectClient(param, clientParam) {
        oldPage = getParam("page");
        
        var values = parseHash(param);
        if (window.DuowanUi && DuowanUi.openActivity) {
            var hash = document.location.hash;
            hash = setParam2(values, hash);
            if (clientParam) {
                hash += clientParam;
            }
            
            hash = hash.substr(2);
            DuowanUi.openActivity(hash);
        } else {
            pages[oldPage] = pages[oldPage] || {};
            pages[oldPage]['scrollTop'] = _getScrollTop();
            setParam(values, true); 
        }
    }

    function openUrl(url, title, needTopBar) {
        if (window.DuowanUi && DuowanUi.openUrl) {
            DuowanUi.openUrl(url, title, needTopBar);
        } else {
            window.open(url);
        }
    }
    

    function _redirectPage(_page) {
        pages[_page] = pages[_page] || {};
        if (!pages[_page]['dom']) {
            _getNewPage(_page);
        } else {
            _changePage(_page);
        }
    }
    
    function _getNewPage(_page, option) {
        var url = _page.replace('-', '/') + '.html';
        var path = document.location.href;

        var pos = path.indexOf("/main/");
        if (pos == -1) {
            pos = path.indexOf("/cacheMain/");
        }
        url = path.substr(0, pos) + "/" + url;
        
        get(url, function(responseText) {
            //如果有页面信息时才缓存
            pages[_page]['dom'] = responseText;
            _changePage(_page, option);
        });
    }

    function _changePage(_page) {
        var $pageswarp = $("#container");
        var $page = $pageswarp.children();
        if (oldPage && $page.length) {
            pages[oldPage] = pages[oldPage] || {};
            //保存滚动条位置$pageswarp
            // pages[oldPage]['scrollTop'] = document.body.scrollTop;
            //console.log("oldPage:" + oldPage + ", scrollTop:" + pages[oldPage]['scrollTop']);
            // pages[oldPage]['height'] = $pageswarp.height();
            pages[oldPage]['script'] = $page.filter("script");
            
            var $fragment = $(document.createDocumentFragment());
            pages[oldPage]['dom'] = $fragment.append($page.filter("div"));
            
            _clearTimer();
        }

        _renderPage(_page);
        reportPv(_page, 'open');
        
        $(document).trigger(BDY.pageChange, _page);
        console.log("$(document).trigger('" + BDY.pageChange + "', _page); _page:" + _page);
        oldPage = _page;
    }
    
    function _renderPage(_page) {
        var $pageswarp = $("#container");
        //回调放在页面js的init方法之前执行，不然会覆盖init里面的setParam
        try {
            var dom = pages[_page]['dom'];
            if ( typeof dom == 'string') {
                scroll(0, 0);
                $pageswarp.html(dom);
            } else {
                //还原高度、滚动条信息
                // $pageswarp.height(pages[_page]['height']);
                var scrollTop = pages[_page]['scrollTop'];
                scroll(0, scrollTop);
    
                //还原dom元素
                $pageswarp.html(dom);
                var script = pages[_page]['script'];
                script && $pageswarp.append(script);
            }
        } catch(e) {
            if (seajs.data.debug) {
                throw e;
            } else {
                console.error(e); 
            }
        }
    }
    
    function onNavBtnClick(callback) {
        $(document).on(BDY.navBtnClick + "_" + getParam("page"), callback);
    }
    
    /**
     * 建立一个覆盖在id加载层，15秒超时消失
     * （如果是在tab, div的id名为xx_tab, 则loading添加在div中）
     * @author benzhan
     * @param id 【可选】待覆盖的div的id
     * @param containerId 【可选】待覆盖的tab的id，或者为dom对象
     * @returns 覆盖div的jQuery对象
     */
    function getLoadingDiv(id, containerId, timeout) {
        timeout = timeout || 15000;
        var $div = $('#' + id);
        //删除之前的loading
        $('#_' + id + '_loading').remove();
        var $loadingDiv = $('<div id="_' + id + '_loading" style="background: #fff url(' + SITE_URL + 'static/images/loading.gif) no-repeat 50% 50%;z-index:200000;position: absolute;opacity: 0.6;filter:alpha(opacity=60);"></div>');
        
        if(typeof(id) == "undefined" || $div.length ==0 || $div.width() <=0 || $div.height() <= 0) {
            //全局loading
            if($('#' + id).length) {
                $loadingDiv.css('left', $div.offset().left);
                var marginTop = parseInt($div.css('margin-top'));
                var top = $div.offset().top;
                marginTop && (top += marginTop);
                $loadingDiv.css('top', top);
                $loadingDiv.width($div.width());
                $loadingDiv.height($(document).height() - $div.offset().top);
            } else {
                $loadingDiv.width('100%');
                $loadingDiv.height('100%');
                $loadingDiv.css('top', '0');
                $loadingDiv.css('left', '0');
                $loadingDiv.css('position', 'fixed');
            }
        } else {
            $loadingDiv.width($div.width());
            $loadingDiv.height($div.height());
            
            $loadingDiv.css('left', $div.offset().left);
            var marginTop = parseInt($div.css('margin-top'));
            var top = $div.offset().top;
            marginTop && (top += marginTop);
            $loadingDiv.css('top', top);
        }
        
        //判断是在tab页面中加还是在body加loading
        if (typeof(containerId) == "string" && $("#" + containerId).length > 0) {
            $("#" + containerId).append($loadingDiv);
        } else if (typeof(containerId) == "object" && containerId.document) {
            $(containerId.document.body).append($loadingDiv);
        } else {
            var $tab = $('.item_now');
            if($tab.length && $('#' + $tab[0].id + 'Tab').length) {
                $('#' + $tab[0].id + 'Tab').append($loadingDiv);
            } else {
                $('body').append($loadingDiv);
            }
        }
        
        //超过20秒，则删除loadingDiv
        var timeOutId = setTimeout(function() {
            //判断是否存在可见元素
            if($loadingDiv && $loadingDiv.is(':visible')) {
                showErrorTip('超时啦!');
                $loadingDiv.end();
            }
        }, timeout);
        
        $loadingDiv.end = function() {
            clearTimeout(timeOutId);
            $loadingDiv && $loadingDiv.remove();
            $loadingDiv = null;
            
            $('div[id$=\_loading]').each(function() {
                var id = this.id;
                id = id.split('_')[1];
                var $div = $('#' + id);
                if (!$div.length) { return; }
                
                $(this).css('left', $div.offset().left);
                var marginTop = parseInt($div.css('margin-top'));
                var top = $div.offset().top;
                marginTop && (top += marginTop);
                $(this).css('top', top);
            });
        };

        return $loadingDiv;
    };
    
    var $globalLoading = null;
    function showLoading(text, timeout, cancelable) {
        timeout = timeout || 15000;
        if (cancelable == null) {
            cancelable = true;
        } else {
            cancelable = !!cancelable;
        }
        
        if (window.DuowanUi && DuowanUi.showLoading) {
            DuowanUi.showLoading(text, timeout, cancelable);
        } else {
        	$globalLoading = getLoadingDiv();
        }
    }
    
    function hideLoading() {
        if (window.DuowanUi && DuowanUi.hideLoading) {
            DuowanUi.hideLoading();
        } else {
        	$globalLoading && $globalLoading.end();
        }
    }
    
    function _showMsg(type, msg, timeout) {
    	require.async(['js/libs/messenger.js', 'css/messenger.css', 'css/messenger-theme-future.css'], function() {
        	Messenger().post({
        		message: msg,
                type: type,
                showCloseButton: true,
                hideAfter: timeout / 1000,
        	});
        });
    }
    
    function showErrorTip(msg, timeout) {
    	if (!msg) { return; }
        timeout = timeout || 3000;
        if (window.DuowanUi && DuowanUi.showErrorTip) {
            DuowanUi.showErrorTip(msg, timeout);
        } else {
        	_showMsg('error', msg, timeout);
        }
    }
    
    function showTip(msg, timeout) {
    	if (!msg) { return; }
        timeout = timeout || 3000;
        if (window.DuowanUi && DuowanUi.showTip) {
            DuowanUi.showTip(msg, timeout);
        } else {
        	_showMsg('info', msg, timeout);
        }
    }

    function showDialog(text) {
        alert(text);
    }

    function showSelectDialog(option, callback) {
        if (window.DuowanUi && DuowanUi.showSelectDialog) {
            DuowanUi.showSelectDialog(option, callback);
        }
    }
    
    function _confirm(msg, confirmCallback, cancelCallback, title, buttonLabels) {
    	title = title || "提示";
    	buttonLabels = buttonLabels || "确定,取消";
        if (window.DuowanUi && DuowanUi.confirm) {
            DuowanUi.confirm(msg, confirmCallback, cancelCallback, title, buttonLabels);
        } else {
            if (confirm(msg)) {
                confirmCallback && confirmCallback(1);
            } else {
                cancelCallback && cancelCallback(2);
            }
        }
    }
    
    function _prompt(title, hint, confirmCallback, cancelCallback, buttonLabels) {
        title = title || "提示";
        buttonLabels = buttonLabels || "确定,取消";
        if (window.DuowanUi && DuowanUi.prompt) {
            DuowanUi.prompt(title, hint, confirmCallback, cancelCallback, buttonLabels);
        } else {
            var msg = prompt(title);
            if (msg) {
                confirmCallback(msg);
            } else {
                cancelCallback("cancel");
            }
        }
    }
    
    function openLogin(callback) {
        if (window.YB && YB.openLogin) {
            YB.openLogin(callback);
        } else {
            if (!$.cookie('oauthCookie') || !$.cookie('username')) {
                var js = "http://www.duowan.com/public/assets/sys/js/udb.v1.0.js";
                require(js, function() {
                    Navbar.login(document.location.href);
                });
            }
        }
    }
    
    function reportPv(page, event, content) {
	    if (window.Duowan && Duowan.reportPv) {
            Duowan.reportPv(page, event, content);
        } else {
            // do nothing
        }
    }
    
    
    
    /**
     * 简单的模板
     *
     * @param array
     *            data 有键值的数组
     * @param string/dom
     *            模板选择器
     */
    function template(data, selector) {
        selector = selector || document;
        var $doc = $(selector);
        for (var key in data) {
            var $item = $doc.find("[name='" + key + "']");
            if (!$item.length) {
                $item = $doc.find("#" + key);
                if (!$item.length) {
                    continue;
                }
            }
            
            $item.each(function() {
                var $this = $(this);
                switch ($this[0].nodeName) {
                    case 'INPUT':
                        var strType = $this.attr('type');
                        if (strType == 'checkbox') {
                            $this.prop("checked", data[key]);
                        } else if (strType == 'radio') {
                            var flag = ($this.val() == data[key]);
                            $this.prop('checked', flag);
                            if (flag) {
                                $this.parent().addClass('active');
                            } else {
                                $this.parent().removeClass('active');
                            }

                            // 处理bootstrap
                            break;
                        } else {
                            $this.val(data[key]);
                        }
                        break;
                    case 'TEXTAREA':
                    case 'SELECT':
                        $this.val(data[key]);
                        break;
                    case 'IMG':
                        // 设置默认图标
                        if ($this.attr('defaultIcon') == 'true') {
                            $this.attr('src', 'gc.defaultIcon');
                        }
                        if ($this.attr('data-original') != null) {
                            $this.attr('data-original', data[key]);
                        } else {
                            $this.attr('src', data[key]);
                        }
                        break;
                    case 'IFRAME':
                        $this.attr('src', data[key]);
                        break;
                    default:
                        $this.html(data[key]);
                }
            });
        }
    }

    function initSelect2() {
        $('select').not(".select2-hidden-accessible").each(function() {
            var $this = $(this);

            var $option = $this.find('[selected]');
            if ($option.length) {
                $option.removeAttr('selected');
                $this.val($option.val());
            }

            if ($this.find('option').length > 30) {
                $this.select2({
                    language: 'zh-CN'
                });

                var width = $this.data('width');
                if (width) {
                    $this.select2({
                        width: width
                    });
                }
            }
        });
    }
    
    init();
    
});

