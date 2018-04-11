define('js/user/register.js', function(require, exports, module) {
    var $ = require('jquery');
    var template = require('tpl');
    var dialog = require('dialog');
    var form = require('form');
    var store = require('store');
    var config = require('js/config.js');
    var sdk = require('js/user/sdk.js');

    var appId = 4;

    exports.init = init;
    
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

    var M = {
        /**
         * 获取短信验证码
         * @param  {string} mobile 手机号码
         */
    	sendVCode : function(mobile) {
    		var api = BDY.userUrl + "account/sendVCode.do";

    		var data = {
    			mobile : mobile,
    			type : 1,
    			appId : appId
    		}

    		$.ajax({
    			url : api,
    			data : data,
    			dataType : "jsonp",
    			success : function(ret) {
                    if(ret.code == 0) {

                    } else {
                        V.showTips(ret.msg);
                    }
    			}
    		});
    	},
        /**
         * 验证短信验证码
         * @param  {string} mobile 手机号码
         * @param  {string} vCode  验证码
         */
    	checkVCode : function(mobile, vCode) {
    		var api = BDY.userUrl + "account/checkVCode.do";

            var data = {
                mobile : mobile,
                vCode : vCode,
                appId : appId
            }

            $.ajax({
                url : api,
                data : data,
                dataType : "jsonp",
                success : function(ret) {
                    if(ret.code == 0) {

                    } else {
                        V.showTips(ret.msg);
                    }
                }
            });
    	},
        /**
         * 注册提交
         * @param  {string} nick   昵称
         * @param  {string} mobile 手机号码
         * @param  {string} pwd    密码
         * @param  {string} vCode  验证码
         */
    	submitRegist : function(nick, mobile, pwd, vCode) {
    		var api = BDY.userUrl + "account/regist.do";
            var fromUrl = getParam("fromUrl") || location.origin;

            var data = {
                nick : nick,
                mobile : mobile,
                pwd : pwd,
                vCode : vCode,
                appId : appId
            }

            $.ajax({
                url : api,
                data : data,
                dataType : "jsonp",
                success : function(ret) {
                	
                	if (ret.data) {
        		    	var time = new Date();
        		    	time.setTime(time.getTime() + 1 * 864e+5);
        				time = time.toUTCString();
        				
        				if (/ouj.com/.test(location.href)) {
        					var domain = 'domain=ouj.com;';
        				} else {
        					var domain = 'domain=' + document.domain;
        				}
        				
        				document.cookie = "otoken=" + encodeURIComponent(ret.data.token) + ";expires=" + time + "; path=/;" + domain;
        				document.cookie = "ouid=" + encodeURIComponent(ret.data.uid) + ";expires=" + time + "; path=/;" + domain;
                	}
                       
                    if(ret.code == 0) {
                        //注册成功页面跳转
                        location.href = "/user/registersucc?uid=" + ret.data.uid + "&fromUrl=" + encodeURIComponent(fromUrl);
                    } else if(ret.code == 213) {
                        location.href = fromUrl;
                    } else {
                        V.showTips(ret.msg);
                    }
                }
            });
    	},
    	checkNick : function(nick) {
    		var api = BDY.userUrl + "account/checkNick.do?nick=" + nick + "&appId=" + BDY.appId;
    		$.get(api, function(objResult) {
    			if (!objResult.result) {
    				V.showError('nick', objResult.msg);
    			}
    		}, 'jsonp');
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
        showProtocol : function() {
            var tpl = template.render("tpl-protocol", {});
            dialog({
                skin : "base-ui",
                title : "提示",
                content : tpl
            }).width(650).height(600).showModal();
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
    		var $registDom = $(".o-regist");

    		//点击登录事件绑定
    		$registDom.on("click", ".o-regist_login", function() {
    			sdk.login();
    		});

    		//获取验证码事件绑定
    		$registDom.on("click", "#getVCode", function() {
    			C.sendVCode();
    		});

            //昵称输入框失去焦点事件绑定
            $registDom.on("blur", "#nick", function() {
                C.checkNick();
            });

            //手机号输入框失去焦点事件绑定
            $registDom.on("blur", "#mobile", function() {
                C.checkTel();
            });

            //验证码输入框失去焦点事件绑定
            $registDom.on("blur", "#VCode", function() {
                C.checkVCode();
            });

            //密码输入框失去焦点事件绑定
            $registDom.on("blur", "#pwd", function() {
                C.checkPwd();
            });

            //注册提交事件绑定
            $registDom.on("click", "#submitRegist", function() {
                C.checkRegist();
            });

            //查看用户协议事件绑定
            $registDom.on("click", "#btn-Protocol", function(e) {
                e.preventDefault();
                V.showProtocol();
            });
    	},
    	sendVCode : function() {
    		var mobile = $("#mobile").val();
            if(C.checkTel() == 1 && $("#getVCode").hasClass("enable")){
                M.sendVCode(mobile);
                $("#getVCode").removeClass("enable");
                var t = 60;
                var st = setInterval(function() {
                    t -= 1;
                    if(t == -1){
                        $("#getVCode").text("获取验证码").addClass("enable");
                        clearInterval(st);
                    } else{
                        $("#getVCode").text("(" + t + ")重新发送");
                    }
                }, 1000);
            }
    	},
        validateForm : function() {
            if(C.checkNick() + C.checkTel() + C.checkVCode() + C.checkPwd() == 4) {
                return true;
            } else {
                return false;
            }
        },
        // checkRow : function(select) {
        //     if()
        // }
        checkTel : function() {
            var select = "mobile";

            if(!form.validateTel($("#" + select))) {
                V.showError(select, "请输入正确的手机号码！");
                return 0;
            } else {
                V.hideError(select);
                return 1;
            }
        },
        checkNick : function() {
            var select = "nick";
            M.checkNick($('#' + select).val());
            if(!form.validateLength($("#" + select))) {
                V.showError(select, "请输入长度为2-14个字符的昵称");
                return 0;
            } else {
                V.hideError(select);
                return 1;
            }
        },
        checkPwd : function(select) {
            var select = "pwd";

            if(!form.validateLength($("#" + select))) {
                V.showError(select, "请输入长度为6-20个字符的密码");
                return 0;
            } else {
                V.hideError(select);
                return 1;
            }
        },
        checkVCode : function(select) {
            var select = "VCode";

            if(!form.validateLength($("#" + select))) {
                V.showError(select, "请输入长度为6位的短信验证码");
                return 0;
            } else {
                V.hideError(select);
                return 1;
            }
        },
        checkRegist : function() {
            var data = form.getFormData("#formRegister");
            if(C.validateForm()) {
                M.submitRegist(data.nick, data.mobile, data.pwd, data.VCode);
            }
        }

    };
    
    function init() {
        C.init();
    }
    // dialog({
    //     skin : "base-ui",
    //     title : "提示",
    //     content : "ret.msg"
    // }).width(300).showModal();
});
