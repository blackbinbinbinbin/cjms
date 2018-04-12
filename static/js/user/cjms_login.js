define(function(require, exports, module) {
	
	var $ = require('jquery');
	var lib = require('js/libs/library.js');
	exports.init = init;


	var M = {
    	login : function(mobile, pwd) {
    		var user = $('input[name=user]').val();
    		var password = $('input[name=password]').val();
    		var url = BDY.url + 'user/verifypwd';

    		var data = {
    			'user': user,
    			'password': password
    		}; 
			
    		lib.post(url, data, function (objResult) {
                if (objResult.result) {
                    lib.showTip('登录成功');
                    var login_url = location.search;
                    var request_params = new Object();   
					if (login_url.indexOf("?") != -1) {   
						var str = login_url.substr(1);   
						strs = str.split("&");   
						for(var i = 0; i < strs.length; i ++) {   
							request_params[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);   
						}   
					}   
                    setTimeout(function() {
                        if (request_params['refer']) {
							self.location.href = request_params['refer'];
						} else {
							self.location.href = url;
						}
                    }, 2000);
                } else {
                    lib.showErrorTip(objResult.msg);
                }
            }, {
                loading : true
            });
    	// 	var url = BDY.userUrl + "account/login.do";
    	// 	var data = {
    	// 		'mobile' : mobile,
    	// 		'pwd' : pwd,
     //            'appId' : appId
    	// 	};
    		
    	// 	$.get(url, data, function(objResult) {
    	// 		if (objResult.result == 1) {
    	// 	    	var time = new Date();
    	// 	    	time.setTime(time.getTime() + 1 * 864e+5);
    	// 			time = time.toUTCString();
    				
    	// 			if (/ouj.com/.test(document.location.href)) {
    	// 				var domain = 'domain=ouj.com;';
    	// 			} else {
    	// 				var domain = 'domain=' + document.domain;
    	// 			}
    				
    	// 			document.cookie = "otoken=" + encodeURIComponent(objResult.data.token) + ";expires=" + time + "; path=/;" + domain;
    	// 			document.cookie = "ouid=" + encodeURIComponent(objResult.data.uid) + ";expires=" + time + "; path=/;" + domain;
    	
					// //取消监听回车事件
					// $(document).off("keydown", C.keydownEnter);
					
					// sdk.redirect();
    	// 		} else {
     //                V.showError("ologin-mobile", objResult.msg);
    	// 		}
     //        }, "jsonp");
    	}
    }

    var V = {
    }

    var C = {
    	init : function() {
            //登录提交事件绑定
		    $('#btn_login_submit').click(function() {
                M.login();
            });
        },
    };
    
    function init() {
    	C.init();
    }
});