define('js/user/editpass.js', function(require, exports, module) {
    var $ = require('jquery');
    var template = require('tpl');
    var dialog = require('dialog');
    var form = require('form');
    var store = require('store');
    var cookie = require('cookie');
    var config = require('js/config.js');
    var sdk = require('js/user/sdk.js');

    var appId = 4;

    exports.init = init;

    var M = {
        resetPass : function() {
            var api = BDY.userUrl + "account/resetPwd.do";

            var data = {
                uid : uid,
                token : token,
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
                    if(ret.code == 0) {

                    } else {
                        V.showTips(ret.msg);
                    }
                }
            });
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
    		var mobile = location.href.split("?mobile=")[1];
            $("#form-mobile .text-normal").text(mobile);

            //密码输入框失去焦点事件绑定
            $(".o-editPass").on("blur", "#pwd", function() {
                C.checkPwd();
            });

            //确认密码输入框失去焦点事件绑定
            $(".o-editPass").on("blur", "#pwd2", function() {
                C.checkPwd2();
            });

            $(".o-editPass").on("click", "#submitGetPass", function() {
                C.checkReset();
            });
    	},
        validateForm : function() {
            if(C.checkPwd() + C.checkPwd2() == 2) {
                return true;
            } else {
                return false;
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
        checkPwd2 : function(select) {
            var select = "pwd2";

            if(!form.validateLength($("#" + select))) {
                V.showError(select, "两次输入密码不相同");
                return 0;
            } else {
                V.hideError(select);
                return 1;
            }
        },
        checkReset : function() {

        }
    };
    
    function init() {
        C.init();
    }
});
