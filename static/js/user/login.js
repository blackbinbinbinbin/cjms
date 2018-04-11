define(function(require, exports, module) {
	
	var $ = require('jquery');
    var form = require('ouj_form');
    var dialog = require('oug_dialog');
    var sdk = require('ouj_sdk');

    exports.init = init;
    var appId = 4;
    
    var M = {
    	login : function(mobile, pwd) {
    		var url = BDY.userUrl + "account/login.do";
    		var data = {
    			'mobile' : mobile,
    			'pwd' : pwd,
                'appId' : appId
    		};
    		
    		$.get(url, data, function(objResult) {
    			if (objResult.result == 1) {
    		    	var time = new Date();
    		    	time.setTime(time.getTime() + 1 * 864e+5);
    				time = time.toUTCString();
    				
    				if (/ouj.com/.test(document.location.href)) {
    					var domain = 'domain=ouj.com;';
    				} else {
    					var domain = 'domain=' + document.domain;
    				}
    				
    				document.cookie = "otoken=" + encodeURIComponent(objResult.data.token) + ";expires=" + time + "; path=/;" + domain;
    				document.cookie = "ouid=" + encodeURIComponent(objResult.data.uid) + ";expires=" + time + "; path=/;" + domain;
    	
					//取消监听回车事件
					$(document).off("keydown", C.keydownEnter);
					
					sdk.redirect();
    			} else {
                    V.showError("ologin-mobile", objResult.msg);
    			}
            }, "jsonp");
    	}
    }

    var V = {
        /**
         * 显示错误提示
         * @param  {string} select 选择器
         * @param  {string} text   提示内容
         * @return {boolean}       
         */
        showError : function(select, text) {
            $("#form-" + select).addClass("row-error").find(".text-notice").text(text);
        },
        /**
         * 隐藏错误提示
         * @param  {string} select 选择器
         * @return {boolean}       
         */
        hideError : function(select) {
            $("#form-" + select).removeClass("row-error");
        },
        showTips : function(text) {
            dialog({
                skin : "base-ui",
                title : "提示",
                content : text
            }).width(300).showModal();
        }
    }

    var C = {
    	init : function() {
		    var $dialog = dialog({
                title : "用户登录",
                skin : "base-ui",
		    	content : $('#ouj_login'),
                onclose : function(){
                    setTimeout(function(){
                        $("#ouj_login").remove();
                    },20)
                }
		    }).showModal();
		      
            //自动聚焦输入框
            $("#ologin-mobile").focus();

            //手机号输入框失去焦点事件绑定
            $("#ouj_login").on("blur", "#ologin-mobile", function() {
                C.checkTel();
            });

            //密码输入框失去焦点事件绑定
            $("#ouj_login").on("blur", "#ologin-pwd", function() {
                C.checkPwd();
            });

            //登录提交事件绑定
		    $('#btn_login_submit').click(function() {
                if($(this).hasClass("submit-active")) {
                    C.checkSubmit();
                }
            });

            //监听回车事件
            $(document).on("keydown", C.keydownEnter);

            var url = BDY.url + 'user/register?fromUrl=' + encodeURIComponent(document.location.href);
            $(".o-login_register").attr("href", url);
        },
        keydownEnter : function(e) {
            if(e.keyCode == 13) {
                C.checkSubmit();
            }
        },
        validateForm : function() {
            if(C.checkTel() + C.checkPwd() == 2) {
                return true;
            } else {
                return false;
            }

        },
        checkTel : function() {
            var select = "ologin-mobile";

            if(!form.validateTel($("#" + select))) {
                V.showError(select, "请输入11个数字的手机号");
                return 0;
            } else {
                V.hideError(select);
                return 1;
            }
        },
        checkPwd : function(select) {
            var select = "ologin-pwd";

            if(!form.validateLength($("#" + select))) {
                V.showError(select, "请输入长度为6-20个字符的密码");
                return 0;
            } else {
                V.hideError(select);
                return 1;
            }
        },
        checkSubmit : function() {
            var data = form.getFormData("#formLogin");
            if(C.validateForm()) {
                M.login(data['ologin-mobile'], data['ologin-pwd']);
            }
        }
    };
    
    function init() {
    	C.init();
    }
    
});
