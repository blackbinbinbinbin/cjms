define(function(require, exports, module) {
    var lib = require('lib');
    var $ = require('jquery');

    require('datetimepicker');

    exports.initUpload = initUpload;
    exports.initRich = initRich;
    exports.initDateTime = initDateTime;
    exports.formatResult = formatResult;
    exports.initAutoComplete = initAutoComplete;
    exports.initCode = initCode;

    var _initDateTime = false;
    function initDateTime() {
        if (_initDateTime) {
            return;
        }

        _initDateTime = true;
        $(document).on(
            'focus.datetimepicker.data-api click.datetimepicker.data-api',
            '[fieldType="datetime"]',
            initDatetimePicker
        );

        $(document).on(
            'focus.datetimepicker.data-api click.datetimepicker.data-api',
            '[fieldType="date"]',
            initDatetimePicker
        );

        $(document).on(
            'focus.datetimepicker.data-api click.datetimepicker.data-api',
            '[fieldType="time"]',
            initDatetimePicker
        );
    }

    function initDatetimePicker(e) {
        var $this = $(this);
        if ($this.data('datetimepicker')) return;
        e.preventDefault();

        var option = {
            language: 'zh-CN',
            format: "yyyy-mm-dd hh:ii",
            autoclose: true,
            todayBtn: true,
            showMeridian : false
        };

        if ($(this).attr('fieldType') == 'date') {
            option['format'] = "yyyy-mm-dd";
            option['minView'] = 2;
        }

        if ($(this).attr('fieldType') == 'time') {
            option['format'] = "hh:ii";
            option['startView'] = 1;
        }

        // component click requires us to explicitly show it
        $this.datetimepicker(option);
        $this.datetimepicker('show');
    }

    function initUpload() {
        var $uploadImage = $('.js-upload-image');
        if (!$uploadImage.fileupload) {
            return;
        }

        $uploadImage.fileupload({
            url: '/service/uploadImg'
        });

        $('.js-upload-audio,.js-upload-video').fileupload({
            url: '/service/uploadAv'
        });

        $('.js-upload').each(function() {
            var result = $(this).attr('data-result');
            if (result) {
                result = JSON.parse(result);
                $(this).fileupload('option', 'done')
                       .call(this, $.Event('done'), {result: result});
            }
        });
    }

    function initRich() {
        if (!window.KindEditor) {
            return;
        }

        var descEditor;
        KindEditor.objects = KindEditor.objects || [];
        KindEditor.ready(function(K) {
            $('.js-rich-textarea,.js-rich_textarea').each(function() {
                var id = $(this).attr('id');
                var name = id || $(this).attr('name');
                var selector = id ? '#' + id : '[name="' + name +'"]';
                KindEditor.objects[name] = K.create(selector, {
                    allowFileManager: true,
                    uploadJson: '/service/upload'
                });
            });
        });
    }

    function formatResult() {
        var $images = $('.js-upload');
        if ($images.length) {
            // 初始化name
            for (var i = 0; i < $images.length; i++) {
                var $image = $($images[i]);
                var fieldName = $image.attr('name');
                var $inputs = $image.find('.file_url_from_jqupload');
                if ($inputs.length) {
                    $inputs.attr('name', fieldName);
                } else {
                    var required = $image.attr('required');
                    var title = $image.attr('title');
                    if (required) {
                        lib.showErrorTip(title + '不能为空');
                        return false;
                    } else {
                        var html = "<input name='" + fieldName + "' type='hidden' value=''>";
                        $image.find('.files').html(html);
                    }
                }
            }
        }

        // 富文本框初始化
        var $richTexts = $('.js-rich-textarea,.js-rich_textarea');
        if ($richTexts.length) {
            for (var i = 0; i < $richTexts.length; i++) {
                var $richText = $($richTexts[i]);
                var name = $richText.attr('id') || $richText.attr('name');
                var html = KindEditor.objects[name].html();

                //var required = $richText.attr('required');
                //if (required) {
                //    var text = $('<html>' + html + '</html>').text();
                //    var title = $richText.attr('title');
                //    if (!$.trim(text)) {
                //        lib.showErrorTip(title + '不能为空');
                //        return false;
                //    }
                //}

                $richText.text($.trim(html));
            }
        }

        var $codeInputs = $('.js-code_html,.js-code_js,.js-code_php');
        if ($codeInputs.length) {
            for (var i = 0; i < $codeInputs.length; i++) {
                var $codeInput = $($codeInputs[i]);
                var name = $codeInput.attr('id') || $codeInput.attr('name');
                var code = codeObjects[name].getValue();
                $codeInput.text($.trim(code));
            }
        }

        return true;
    }

    function initAutoComplete() {
        var $autoComplete = $('.js-auto_complete');
        if (!$autoComplete.autocomplete) {
            return;
        }

        $autoComplete.each(function() {
            var availableTags = $(this).attr('data-enum');
            this.availableTags = availableTags && JSON.parse(availableTags);
        });

        $autoComplete.on( "keydown", function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                $( this ).autocomplete( "instance" ).menu.active ) {
                event.preventDefault();
            }
        }).autocomplete({
            minLength: 0,
            source: function( request, response ) {
                // delegate back to autocomplete, but extract the last term
                response( $.ui.autocomplete.filter(this.element[0].availableTags, _extractLast( request.term ) ) );
            },
            focus: function() {
                // prevent value inserted on focus
                return false;
            },
            select: function( event, ui ) {
                var terms = _split( this.value );
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push( ui.item.value );
                // add placeholder to get the comma-and-space at the end
                // terms.push( "" );
                this.value = terms.join( ", " );
                return false;
            }
        });

        function _extractLast( term ) {
            return _split( term ).pop();
        }

        function _split( val ) {
            return val.split( /,\s*/ );
        }
    }

    var codeObjects = {};
    function initCode() {
        var map = {
            '.js-code_html' : 'text/html',
            '.js-code_js' : 'text/javascript',
            '.js-code_php' : 'text/x-php'
        };

        for (var key in map) {
            var $codeInputs = $(key);
            if ($codeInputs.length) {
                for (var i = 0; i < $codeInputs.length; i++) {
                    var value = map[key];
                    var $codeInput = $($codeInputs[i]);
                    var name = $codeInput.attr('id') || $codeInput.attr('name');

                    codeObjects[name] = CodeMirror.fromTextArea($codeInput[0], {
                        lineNumbers: true,
                        mode: value
                    });

                    var height = $codeInput.height() || parseInt($codeInput.css('height'));
                    codeObjects[name].setSize($codeInput.width(), height);
                }

            }
        }

    }
});




