define(function(require, exports, module) {
    var lib = require('lib');
    require('jquery');
    require('js/libs/jqext.js');

    exports.validateForm = validateForm;
    exports.getFormData = getFormData;
   
   
    function initEvent() {
        $("body").on("click", ".focus", function() {
            $(this).removeClass("focus");
        });
    }
    
    initEvent();
   
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
            var key = $input.attr('data-name') || $input[i].name || $input[i].id;
            if (key) {
                var type = $this.attr("type");
                if (type == "checkbox") {
                    var vals = [];
                    var $cbs = $(selector).find('[name="' + $this.attr('name') + '"]:checked');
                    $cbs.each(function() {
                        vals.push($(this).val());
                    });
                    vals.length && (data[key] = vals);
                } else if (type == 'radio') {
                    data[key] = $(selector).find('[name="' + $this.attr('name') + '"]:checked').val();
                } else if (type == 'file') {
                    if (key == 'files[]' && $this.val().length == 0) {
                        continue;
                    }
                    data[key] = $.trim($this.val());
                } else if (type != 'submit' && type != 'button') {
                    var val = $this.val();
                    if ($this[0].nodeName != 'TEXTAREA') {
                        val = $.trim(val);
                    }
                    data[key] = val;
                }
            }
        }
        
        return data;
    }
    
    
    function validateForm(selector) {
        var $input = $(selector).find('input, select, textarea');
        $input.removeClass("hover");
        for (var i = 0; i < $input.length; i++) {
            var $this = $($input[i]);
            if (!_validate($this)) {
                return false;
            }
        }
        
        return true;
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
            	$.scrollTo($this, 500);
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
        	var title = $this.attr('title') || $this.attr('placeholder');
            lib.showTip("请输入" + title);
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
            	var title = $this.attr('title') || $this.attr('placeholder');
                lib.showTip(_format('{1}不能少于{0}', dataRange[1], title));
                $this.addClass("focus");
                return false;
            } else if (parseInt(dataRange[2]) && value > parseInt(dataRange[2])) {
            	var title = $this.attr('title') || $this.attr('placeholder');
                lib.showTip(_format("{1}不能大于{0}", title, dataRange[2]));
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
            	var title = $this.attr('title') || $this.attr('placeholder');
                lib.showTip(_format('请输入{0}个字以上的{1}', dataRange[1], title));
                $this.addClass("focus");
                return false;
            } else if (parseInt(dataRange[2]) && length > parseInt(dataRange[2])) {
            	var title = $this.attr('title') || $this.attr('placeholder');
                lib.showTip(_format("{0}不能超过{1}个字", title, dataRange[2]));
                $this.addClass("focus");
                return false;
            }
        } else if (length != dataLength) {
        	var title = $this.attr('title') || $this.attr('placeholder');
            lib.showTip(_format("{0}应该为{1}个字", title, dataLength));
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
            var title = $this.attr('title') || $this.attr('placeholder');
            lib.showTip(title + msg);
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
            case 'json':
                return _validateJson($this);
            default:
                return true;
        }
    }
    
    function _validateTel($this) {
        var value = $.trim($this.val());
        var reg = /^\d{11}$/;
        var strTip = "请输入正确的手机号码！";
        if (reg && !reg.test(value)) {
            lib.showTip(strTip);
            $this.addClass("focus");
            return false;
        } 
        
        return true;
    }

    function _validateJson($this) {
        var value = $.trim($this.val());
        var strTip = "请输入正确的JSON格式！";
        try {
            var obj = JSON.parse(value);
        } catch (ex) {
            strTip += ex.message;
        }

        if (!obj) {
            lib.showTip(strTip);
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
            lib.showTip(strTip);
            $this.addClass("focus");
            return false;
        } 
        
        return true;
    }
    
    function _validateMail($this) {
        var value = $.trim($this.val());
        var reg = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
        if (!reg.test(value)) {
            lib.showTip("请输入合法的Email地址！");
            $this.addClass("focus");
            return false;
        }
        
        return true;
    }
    
     // -----------------------  xbtn 的支持  begin -------------------------
    (function() {

        $(window).on('resize', function() {
            $('input[xbtn]').each(function() {
                var $this = $(this);
                var $img = $('#' + $this.attr('xbtn_id'));
                if ($img.length) {
                    _resizeXBtn($this, $img);
                }
            });
        });
        
        $("body").on('click', "input[xbtn]", function() {
            var $this = $(this);

            if ($this.attr('xbtn_id')) {
                $this.trigger('input');
                return;
            }
            
            _bulidXBtn($this);
        });
        
        function _bulidXBtn($this) {
            var id = "xbtn_" + (new Date).getTime();
            $this.attr("xbtn_id", id);
            
            var $img = $('<image class="_xbtn" id="' + id + '" src="../img/input_xbtn.png" style="cursor:pointer;"/>');
            
            $img.hide();
            $img.insertAfter($this);

            _resizeXBtn($this, $img);

            $this.trigger('input');
        }
        
        function _resizeXBtn($this, $img) {
             // 设置样式
            var oPos = {};
            // 设置位置
            oPos.position = 'absolute';
            var nBorderLeft = parseInt($this.css('border-left-width')) || 0;
            var nPaddingLeft = parseInt($this.css('padding-left')) || 0;
            var nMarginLeft = parseInt($this.css('margin-left')) || 0;
            
            var nPaddingTop = parseInt($this.css('padding-top')) || 0;
            var nBorderTop = parseInt($this.css('border-top-width')) || 0;
            var nMarginTop = parseInt($this.css('margin-top')) || 0;

                        
            var inputWidth = $this.width();
            var inputHeight = $this.height();
            oPos.left = $this.position().left + inputWidth - inputHeight * 7/8;
            
            var inputCss = {};
            inputCss["padding-right"] = inputHeight * 7/8;
            
            if (window.KLPlugin) {
                var nMarginRight = parseInt($this.css('margin-right')) || 0;
                var nBorderRight = parseInt($this.css('border-right-width')) || 0;
                oPos.top = $this.position().top + inputHeight * 1/8 + nBorderTop;
            } else {
                oPos.top = $this.position().top + inputHeight * 1/8;
            }
            
            var widthStyle = $this[0].style.width;
            if (widthStyle && widthStyle.indexOf("%") == -1) {
                 inputCss["width"] = inputWidth;                
            }
            
            $this.css(inputCss);
            $img.css(oPos).height(inputHeight * 3/4);
        }
        
        $("body").on('input', "input[xbtn]", function() {
            
            var $this = $(this);
            var id = $this.attr("xbtn_id");
            var $xbtn = $("#" + id);
                
            var strVal = $this.val();
            var isDisabled = $this.attr("readonly");
            if (strVal && (isDisabled == null)) {
                console.log("$xbtn.show();");
                $xbtn.show();
            } else {
                console.log("$xbtn.hide();");
                $xbtn.hide();
            }
        });
        
        $("body").on(BDY.click, "._xbtn", function() {
            var $input = $('[xbtn_id=' + $(this).attr('id') + ']');
            $input.val("");
            $(this).hide();
        });
        
        $(document).on(BDY.pageChange, function(event, _page) {
            var $xbtn = $("input[xbtn]");
            if (!$xbtn.length) {
                return;
            }
            
            setTimeout(function() {
                $xbtn.each(function() {
                    var $this = $(this);
                    
                    
                    if ($this.val()) {
                        if (!$this.attr('xbtn_id')) {
                            _bulidXBtn($this);;
                        }
                        
                        $this.trigger("input");
                    }
                });
            }, 100);
        });
        
        
    })();
    // -----------------------  xbtn 的支持  end -------------------------
    
});


