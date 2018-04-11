define('js/user/registersucc.js', function(require, exports, module) {
    var $ = require('jquery');
    var dialog = require('dialog');
    var store = require('store');

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

    var C = {
        init : function() {
            var uid = getParam("uid");
            var fromUrl = getParam("fromUrl") || location.origin;
            $("#uid").text(uid);
            $(".o-btn_submit").attr("href", fromUrl);

            var t = 5;
            var st = setInterval(function() {
                t -= 1;
                if(t == -1) {
                    clearInterval(st);
                    location.href = fromUrl;
                } else {
                    $("#count").text(t);
                }
                
            }, 1000);
        }
    }

    C.init();
});
