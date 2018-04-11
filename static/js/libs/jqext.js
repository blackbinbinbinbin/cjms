define(function(require, exports, module) {
    require('jquery');
    require('clipboard');
    var $ = jQuery;

    //复制的jquery插件，用法见index.html 202行
    $.fn.copy = function(opts) {
        var defaults = {
            //'trigger' : 'mousedown', //触发事件，可为mousedown、mouseover、mouseout、mouseup
            'getContent' : function() { return '复制的内容！'; },
            'appendElem' : undefined //默认把flash加在body中
        };
        opts = $.extend(defaults, opts);

        var Clipboard = window.Clipboard || parent.Clipboard;
        new Clipboard(this.selector, {
            text: function() {
                return opts.getContent();
            }
        });
        
        return this;
    };
    
    
    /**
     * ajax表格排序
     * @param string containerId 表格id或父div的id
     * @param function callback 排序事件的回调函数 
     * @author benzhan
     */
    $.fn.ajaxSortTable = function(option) {
        var defaults = {
            'callback' : function(){},
            'attrName' : 'sortKey'
        };
        
        option = $.extend(defaults, option);
        
        var ascPng = "<span id='arrow' style='color:red;'>↑</span>";
        var descPng = "<span id='arrow' style='color:red;'>↓</span>";

        $this = this;
        var $ths = $this.find("th[" + option.attrName + "]");
        $ths.css('cursor', 'pointer');
        $ths.attr('title', '点击排序');
               
        //鼠标点击事件   
        $ths.click(function() {
            var $arrow = $(this).find('#arrow');
            if ($arrow.length) {
                if ($.getParam('_sortDir') == 'DESC') {
                    $.setParam('_sortDir', 'ASC');
                } else {
                    $.setParam('_sortDir', 'DESC');
                }
            } else {
                $.setParam('_sortDir', 'DESC');
            }
            $.setParam('_sortKey', $(this).attr(option.attrName));
            option.callback();
        });
        
        //可能有预先排序
        if ($.getParam('_sortKey')) {
            var $sortField = $this.find("th[" + option.attrName + "='" +  $.getParam('_sortKey') + "']");
            if ($sortField.find('#arrow').length == 0) {
                if ($.getParam('_sortDir') == 'ASC') {
                    $sortField.append(ascPng);
                } else {
                    $sortField.append(descPng);
                }
            }
        }
        
        return $this;
    };
    
    /**
     * $.scrollTo('#pro',500);
     * Copyright (c) 2007-2014 Ariel Flesler - aflesler<a>gmail<d>com | http://flesler.blogspot.com
     * Licensed under MIT
     * @author Ariel Flesler
     * @version 1.4.11
     */
    ;(function(a){if(typeof define==='function'&&define.amd){define(['jquery'],a)}else{a(jQuery)}}(function($){var j=$.scrollTo=function(a,b,c){return $(window).scrollTo(a,b,c)};j.defaults={axis:'xy',duration:parseFloat($.fn.jquery)>=1.3?0:1,limit:true};j.window=function(a){return $(window)._scrollable()};$.fn._scrollable=function(){return this.map(function(){var a=this,isWin=!a.nodeName||$.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!isWin)return a;var b=(a.contentWindow||a).document||a.ownerDocument||a;return/webkit/i.test(navigator.userAgent)||b.compatMode=='BackCompat'?b.body:b.documentElement})};$.fn.scrollTo=function(f,g,h){if(typeof g=='object'){h=g;g=0}if(typeof h=='function')h={onAfter:h};if(f=='max')f=9e9;h=$.extend({},j.defaults,h);g=g||h.duration;h.queue=h.queue&&h.axis.length>1;if(h.queue)g/=2;h.offset=both(h.offset);h.over=both(h.over);return this._scrollable().each(function(){if(f==null)return;var d=this,$elem=$(d),targ=f,toff,attr={},win=$elem.is('html,body');switch(typeof targ){case'number':case'string':if(/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(targ)){targ=both(targ);break}targ=$(targ,this);if(!targ.length)return;case'object':if(targ.is||targ.style)toff=(targ=$(targ)).offset()}var e=$.isFunction(h.offset)&&h.offset(d,targ)||h.offset;$.each(h.axis.split(''),function(i,a){var b=a=='x'?'Left':'Top',pos=b.toLowerCase(),key='scroll'+b,old=d[key],max=j.max(d,a);if(toff){attr[key]=toff[pos]+(win?0:old-$elem.offset()[pos]);if(h.margin){attr[key]-=parseInt(targ.css('margin'+b))||0;attr[key]-=parseInt(targ.css('border'+b+'Width'))||0}attr[key]+=e[pos]||0;if(h.over[pos])attr[key]+=targ[a=='x'?'width':'height']()*h.over[pos]}else{var c=targ[pos];attr[key]=c.slice&&c.slice(-1)=='%'?parseFloat(c)/100*max:c}if(h.limit&&/^\d+$/.test(attr[key]))attr[key]=attr[key]<=0?0:Math.min(attr[key],max);if(!i&&h.queue){if(old!=attr[key])animate(h.onAfterFirst);delete attr[key]}});animate(h.onAfter);function animate(a){$elem.animate(attr,g,h.easing,a&&function(){a.call(this,targ,h)})}}).end()};j.max=function(a,b){var c=b=='x'?'Width':'Height',scroll='scroll'+c;if(!$(a).is('html,body'))return a[scroll]-$(a)[c.toLowerCase()]();var d='client'+c,html=a.ownerDocument.documentElement,body=a.ownerDocument.body;return Math.max(html[scroll],body[scroll])-Math.min(html[d],body[d])};function both(a){return $.isFunction(a)||typeof a=='object'?a:{top:a,left:a}};return j}));

});

