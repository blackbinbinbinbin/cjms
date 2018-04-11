define('js/user/getpass.js', function(require, exports, module) {
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
                    if(ret.code == 213) {
                        var obj = {
                            uid : ret.data.uid,
                            token : ret.data.token,
                            mobile : mobile,
                            vCode : vCode
                        }
                        V.showEditForm(obj);
                    } else {
                        V.showTips(ret.msg);
                    }
                }
            });
        },
        resetPass : function(uid, token, mobile, pwd, vCode) {
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
                        V.showTips("修改成功！");
                        setTimeout(function () {
                            location.href = location.origin;
                        }, 1500)
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
        },
        showEditForm : function(obj) {
            var tpl = template.render("tpl-editPass", {data:obj});
            $(".user-wrap").html(tpl);
        }
    }

    var C = {
        init : function() {
            //手机号输入框失去焦点事件绑定
            $(".user-wrap").on("blur", "#mobile", function() {
                C.checkTel();
            });

            //验证码输入框失去焦点事件绑定
            $(".user-wrap").on("blur", "#VCode", function() {
                C.checkVCode();
            });

            //获取验证码事件绑定
            $(".user-wrap").on("click", "#getVCode", function() {
                C.sendVCode();
            });

            $(".user-wrap").on("click", "#submitGetPass", function() {
                C.checkGetPass();
            });

            //密码输入框失去焦点事件绑定
            $(".user-wrap").on("blur", "#pwd", function() {
                C.checkPwd();
            });

            //确认密码输入框失去焦点事件绑定
            $(".user-wrap").on("blur", "#pwd2", function() {
                C.checkPwd2();
            });

            $(".user-wrap").on("click", "#submitEditPass", function() {
                C.checkEditPass();
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
        checkGetPass : function() {
            var data = form.getFormData("#formGetPass");
            
            if(C.checkTel() + C.checkVCode() == 2) {
                M.checkVCode(data.mobile, data.VCode);
            }
        },
        checkEditPass : function() {
            var data = form.getFormData("#formEditPass");
            console.log(data);
            if(C.checkPwd() + C.checkPwd2() == 2) {
                M.resetPass(data.uid, data.token, data.mobile, data.pwd, data.vCode);
            }
        }
    }

    function init() {
        C.init();
    }
});
