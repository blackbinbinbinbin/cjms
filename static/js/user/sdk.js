define(function(require, exports, module) {
	
	if (!window.BDY) {
		BDY = { 
			url : "http://www.ouj.com/",
			userUrl : "http://user.api.ouj.com/"
		};
	}
	
	var $ = window.jQuery;	
	var _fromUrl;
	
	
	exports.login = login;
	exports.loginwb = loginwb;
	exports.loadJs = loadJs;
	exports.register = register;
	exports.logout = logout;
	exports.redirect = redirect;
	exports.getParam = getParam;
	exports.getParam2 = getParam2;
	exports.init = init;
	
	function _loadJquery(callback) {
		if (!window.jQuery) {
			var url = BDY.url + 'static/js/libs/jquery-1.11.1.js';
			loadJs(url, function() {
				$ = jQuery;
				callback();
			});
		} else {
			$ = window.jQuery;
			callback();
		}
	}
	
	function loadJs(url, callback) {
        var script = document.createElement("script");
        script.src = url;
        script.charset = "utf-8";
        script.onload = callback;

        var header = document.getElementsByTagName("head")[0];
        header.insertBefore(script, header.firstChild);
	}
    
    function login(fromUrl) {
    	_fromUrl = fromUrl;
    	var url = BDY.url + 'user/login';
    	$.get(url, function(res) {
    		$('body').append(res);
    	}, 'jsonp');
    }
    
    function loginwb(fromUrl) {
    	_fromUrl = fromUrl;
    	
    	BDY.shopUrl = "http://shop.api.ouj.com/";
        var url = BDY.shopUrl + "weibo/login?url=" + fromUrl;
        $.get(url, function(objResult) {
            if (!objResult.result) {
                if (objResult.data) {
                    document.location.href = objResult.data;
                }
            } else {
            	redirect();
            }
        }, 'json');
    }
    
    function redirect() {
    	if (typeof(_fromUrl) == 'string') {
			document.location.href = _fromUrl;
		} else {
			location.reload();
		}
    }
    
    function register() {
    	var fromUrl = getParam('fromUrl') || document.location.href;
    	var url = BDY.url + 'user/register?fromUrl=' + encodeURIComponent(fromUrl) ;
    	document.location.href = url;
    }
    
    function logout(fromUrl) {
    	var time = new Date();
    	time.setTime(time.getTime() - 1 * 864e+5);
		time = time.toUTCString();
		
		var url = BDY.url + 'user/logout';
		$.getScript(url);
		
		if (/ouj.com/.test(document.location.href)) {
			var domain = 'domain=ouj.com;';
		} else {
			var domain = 'domain=' + document.domain;
		}
		
		document.cookie = "otoken=; expires=" + time + "; path=/;" + domain;
		document.cookie = "ouid=; expires=" + time + "; path=/;" + domain;
		document.cookie = "ouj_token=; expires=" + time + "; path=/;";
		
		_fromUrl = fromUrl;
		redirect();
    }
    
    function init() {
    	_loadJquery(initEvent);	
    }
 
	function initEvent() {
		$('#btn_ouj_login').click(function() {
			login();
		});
		
		$('#btn_ouj_register').click(register);
        $("#btn_ouj_logout").click(logout);
        $("#btn_ouj_loginwb").click(function() {
        	loginwb(document.location.href);
        });
	}
    
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
    
});
