define('form', function(require, exports, module) {
    var $ = require('jquery');

    exports.validateForm = validateForm;
    exports.validateForm2 = validateForm2;
    exports.getFormData = getFormData;
    
    exports.validateTel = _validateTel;
    exports.validateRequire = _validateRequire;
    exports.validateMail = _validateMail;
    exports.validateLength = _validateLength;
    exports.validateNum = _validateNum;

    // function initEvent() {
    //     $("body").on("click", ".focus", function() {
    //         $(this).removeClass("focus");
    //     });
    // }
    
    // initEvent();
    
    /**
     * 检查参数并获取表单数据
     * selector：表单选择器
     */
    function getFormData(selector) {
        var data = {};
        var $input = $(selector).find('input, select, textarea');
        $input.removeClass("hover");
        for (var i = 0; i < $input.length; i++) {
            var $this = $($input[i]);
            var key = $input[i].name || $input[i].id;
            if (key) {
                var type = $this.attr("type");
                if (type == "checkbox") {
                    data[key] = $this.prop("checked");
                } else if (type == 'radio') {
                    data[key] = $('[name="' + $this.attr('name') + '"]:checked').val();
                } else if (type != 'submit' && type != 'button') {
                    data[key] = $.trim($this.val());
                }
            }
        }
        
        return data;
    }
    
    
    function validateForm(selector, callback) {
        var $input = $(selector).find('input, select, textarea');
        $input.removeClass("hover");
        $flag = true;
        for (var i = 0; i < $input.length; i++) {
            var $this = $($input[i]);
            if (!_validate($this)) {
                return false;
            }
        }
        
        return true;
    }

    function validateForm2(selector) {
        var flag = validateForm(selector);
        if (!flag) {
            require.async('lib', function(lib) {
                var $input = $('[error_msg]');
                lib.showTip($input.attr('error_msg'));
                $input.attr('error_msg', '');
            });
        }

        return flag;
    }

    function _validate($this) {
        var funcList = [
            _validateRequire,
            _validateLength,
            _validateRange,
            _validateReg,
            _validateType
        ];
        
        for (var i = 0; i < funcList.length; i++) {
            if (!funcList[i]($this)) {
                return false;
            }
        }
        
        return true;
    }
    
    // 字符串格式化
    function _format(str) {
        if (arguments.length == 0) return null;
        var args = Array.prototype.slice.call(arguments, 1);
        return str.replace(/\{(\d+)\}/g, function(m, i){
            return args[i];
        });
    }

    function _validateRequire($this) {
        if (!$this.attr('required')) {
            return true;
        }
        
        var required = $this.attr('required');
        var inputVal = $.trim($this.val());
        if (required && !inputVal) {
            // lib.showTip("请输入" + $this.attr('title'));
        	$this.attr('error_msg', "请输入" + $this.attr('title'));
            $this.addClass("focus");
            return false;
        }
        
        return true;
    }
    
    function _validateRange($this) {
        var range = $this.attr('data-range');
        var value = $.trim($this.val());
        if (!range || !value) {
            return true;
        }
        
        var reg = /(\d+),\s*(\d+|\+)*/;
        if (reg.test(range)) {
            var dataRange = range.match(reg);
            if (value < parseInt(dataRange[1])) {
                // lib.showTip(_format('{1}不能少于{0}', dataRange[1], $this.attr('title')));
            	var msg = _format('{1}不能少于{0}', dataRange[1], $this.attr('title'));
            	$this.attr('error_msg', msg);
                $this.addClass("focus");
                return false;
            } else if (parseInt(dataRange[2]) && value > parseInt(dataRange[2])) {
                // lib.showTip(_format("{1}不能大于{0}", $this.attr('title'), dataRange[2]));
            	var msg = _format("{1}不能大于{0}", $this.attr('title'), dataRange[2]);
            	$this.attr('error_msg', msg);
                $this.addClass("focus");
                return false;
            }
        } 
        
        return true;
    }
    
    function _validateLength($this) {
        if (!$this.attr('data-length')) {
            return true;
        }
        
        var value = $.trim($this.val());
        // var length = strlen(value);
        var length = value.length;
        var reg = /(\d+),\s*(\d+|\+)*/;
        var dataLength = $this.attr('data-length');
        if (reg.test(dataLength)) {
            var dataRange = $this.attr('data-length').match(reg);
            if (length < parseInt(dataRange[1])) {
                // lib.showTip(_format('请输入{0}个字以上的{1}', dataRange[1], $this.attr('title')));
            	var msg = _format('请输入{0}个字以上的{1}', dataRange[1], $this.attr('title'));
            	$this.attr('error_msg', msg);
                $this.addClass("focus");
                return false;
            } else if (parseInt(dataRange[2]) && length > parseInt(dataRange[2])) {
                // lib.showTip(_format("{0}不能超过{1}个字", $this.attr('title'), dataRange[2]));
            	var msg = _format("{0}不能超过{1}个字", $this.attr('title'), dataRange[2]);
            	$this.attr('error_msg', msg);
                $this.addClass("focus");
                return false;
            }
        } else if (length != dataLength) {
            // lib.showTip(_format("{0}应该为{1}个字", $this.attr('title'), dataLength));
        	var msg = _format("{0}应该为{1}个字", $this.attr('title'), dataLength);
        	$this.attr('error_msg', msg);
            $this.addClass("focus");
            return false;
        }
        
        return true;
    }
    
    
    function _validateReg($this) {
        if (!$this.attr('data-reg')) {
            return true;
        }
        
        var reg = new RegExp($this.attr('data-reg'));
        var value = $.trim($this.val());
        if (reg.test(value)) {
            var msg = $this.attr('data-regMsg') || "不符合规范哦！";
            // lib.showTip($this.attr('title') + msg);
            var msg = _format("{0}应该为{1}个字", $this.attr('title'), dataLength);
        	$this.attr('error_msg', msg);
            $this.addClass("focus");
            return false;
        }
        
        return true;
    }

    function _validateType($this) {
        if (!$.trim($this.val())) {
            return true;
        }
        
        var dataType = $this.attr('data-type');
        var typeList = dataType ? dataType.split('|') : [$this.attr("type")];
        var result = true;
        for (var i = 0, len = typeList.length; i < len; i++) {
            var type = typeList[i];
            if (_validateType2($this, type)) {
                return true;
            }
        }
        
        return false;
    }
    
    function _validateType2($this, type) {
        switch (type) {
            case 'email':
                return _validateMail($this);
            case 'tel':
                return _validateTel($this);
            case 'number':
                return _validateNum($this);
            default:
                return true;
        }
    }
    
    function _validateTel($this) {
        var value = $.trim($this.val());
        var reg = /^\d{11}$/;
        var strTip = "请输入正确的手机号码！";
        if (reg && !reg.test(value)) {
            // lib.showTip(strTip);
            $this.addClass("focus");
            return false;
        } 
        
        return true;
    }
    
    function _validateNum($this) {
        var value = $.trim($this.val());
        var reg = /^\d+(\.\d+)*$/;
        var strTip = "请输入数字！";
        if (reg && !reg.test(value)) {
            // lib.showTip(strTip);
            $this.addClass("focus");
            return false;
        } 
        
        return true;
    }
    
    function _validateMail($this) {
        var value = $.trim($this.val());
        var reg = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
        if (!reg.test(value)) {
            // lib.showTip("请输入合法的Email地址！");
            $this.addClass("focus");
            return false;
        }
        
        return true;
    }
    
});


