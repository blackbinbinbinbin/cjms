/*
 * directedGraph - 有向图库
 * @author benhzhan(詹潮江)
 * @update time: 2011-09-12
 */

var Raphael_DirectedGraph = 'V1.0';

/**
 * 画箭头
 * @author benzhan
 * @param (object) start 开始坐标 x, y
 * @param (object) end 结束坐标 x, y
 * @param (object) other 其他可选配置:
     color:颜色
     angle:箭头开口的角度(注意：是度数，不是弧度)，默认为50
     longAxis:箭头总长度，默认为12
     shortAxis: 箭头短轴长度(注意：若和size相等，则箭头就是一个三角形，底部没有凹陷形成尾翼；若为0，则箭头的尾翼就是两根直线)，默认为size/2
 */
Raphael.prototype.arrow = function(start, end, other) {
    var paper = this; 
    start.x = parseFloat(start.x) || parseFloat(start.left);
    start.y = parseFloat(start.y) || parseFloat(start.top);
    end.x = parseFloat(end.x) || parseFloat(end.left);
    end.y = parseFloat(end.y) || parseFloat(end.top);
    other = other || {};

    var y1 = end.y - Math.sqrt(Math.pow(start.x - end.x, 2) + Math.pow(start.y - end.y, 2)); 
        
    var longAxis = parseInt(other.longAxis) || 12;
    var angle = parseInt(other.angle) || 40;
    var shortAxis = parseInt(other.shortAxis) || longAxis / 2;
    var color = other.color || "#e29d36";

    //箭头尾部离左右节点的间隔
    var len = Math.tan(Raphael.rad(angle / 2)) * longAxis;

    //左边的节点x坐标
    var ltX = end.x - len;
    //右边的节点x坐标
    var rtX = end.x + len;
    //短轴节点位置
    var ctX = end.x;

    //左右节点的Y坐标相同
    if (end.y > start.y) {
        var tY = end.y - longAxis;
        var ctY = tY + shortAxis;
    } else {
        var tY = end.y + longAxis;
        var ctY = tY - shortAxis;
    }

    var arrowPath = ['M', end.x, end.y, 'L', ltX, tY, 'L', ctX, ctY, 'L', rtX, tY, 'L', end.x, end.y].join(","); 
	
    if (other['arrow']) {
        var arrow = other['arrow'];
        arrow.attr({'path' : arrowPath});
    } else {
        var strokeWidth = other['stroke-width'] || 1;
        var arrow = this.path(arrowPath).attr({ stroke: color , fill: color, 'stroke-width': strokeWidth});
        other['id'] && (arrow[0].id = other['id']);
    }
    
    if (arrow[0].id == '2-4-arrow')
    {
        console.log(arrow[0].id);
        console.log('start:' + start.x + ',' + start.y);
        console.log('start:' + end.x + ',' + end.y);
    }

    if (end.y - start.y === 0) {
        var angle = start.x >= end.x ? 270 : 90;
    } else {
        var angle = Math.atan((start.x - end.x) / (end.y - start.y)) / Math.PI * 180;
    }
    
    arrow.rotate(angle, end.x, end.y);

    $(arrow[0]).css('cursor', "pointer");
    return arrow;
};


Raphael.fn.connection = function (fromId, toId, other) {
    var pathId = fromId + '-' + toId;
    //过滤非法id
    if (!fromId || !toId) {
        return;
    }
    
    //设置默认线条
	other['style'] =  other['style'] || 'curve';
	if (other['style'] == 'line') {
		var pathInfo = getLinePathInfo(fromId, toId);
	} else if (other['style'] == 'curve') {
        var pathInfo = getCurvePathInfo(fromId, toId);
	} else {
		alert('style no found!');
	}
    
    var $line = $('#' + pathId);
    var valueId = pathId + "-value";
    var $value = $("#" + valueId);
    
    if ($line.length) {
        var path = other['path'];
        var arrow = other['arrow'];
		
        this.arrow(pathInfo['start'], pathInfo['end'], {'arrow' : arrow});
        //line.bg && line.bg.attr({path: path});
        path.attr({'path': pathInfo['pathString'], 'type' : other['type']});
    } else {
        var color = other['color'] || "#000";
        var strokeWidth = other["value"] / other['baseValue'];
        //最大粗度为20
        if (strokeWidth > 20) {
            strokeWidth = 20;
        }
        
        //画箭头
        var arrow = this.arrow(pathInfo['start'], pathInfo['end'], {'color' : color, 'stroke-width' : strokeWidth});
        arrow[0].id = pathId + '-arrow';
        var path = this.path(pathInfo['pathString']).attr({stroke: color, fill: "none", 'stroke-width' : strokeWidth});
        path[0].id = pathId;
        
        $value = $("<span id='" + valueId + "' class='value_span'>" + other["value"] + "</span>");
        $value.css("background-color", color);
        $('#' + fromId).after($value);
    }
    
    $line = $('#' + pathId);
    var info = $line[0].getBBox();
    $value.css("top", info.y + info.height / 2 - $value.height());
    $value.css("left", info.x + info.width / 2 - $value.width());
    
    other['arrow'] = arrow;
    other['path'] = path;
    return other;

    function getCurvePathInfo(fromId, toId) {
        var bb1 = getBox(fromId),
            bb2 = getBox(toId),
            p = [{x: bb1.x + bb1.width / 2, y: bb1.y - 1},
            {x: bb1.x + bb1.width / 2, y: bb1.y + bb1.height + 1},
            {x: bb1.x - 1, y: bb1.y + bb1.height / 2},
            {x: bb1.x + bb1.width + 1, y: bb1.y + bb1.height / 2},
            {x: bb2.x + bb2.width / 2, y: bb2.y - 1},
            {x: bb2.x + bb2.width / 2, y: bb2.y + bb2.height + 1},
            {x: bb2.x - 1, y: bb2.y + bb2.height / 2},
            {x: bb2.x + bb2.width + 1, y: bb2.y + bb2.height / 2}],
            d = {}, dis = [];
        for (var i = 0; i < 4; i++) {
            for (var j = 4; j < 8; j++) {
                var dx = Math.abs(p[i].x - p[j].x),
                    dy = Math.abs(p[i].y - p[j].y);
                if ((i == j - 4) || (((i != 3 && j != 6) || p[i].x < p[j].x) && ((i != 2 && j != 7) || p[i].x > p[j].x) && ((i != 0 && j != 5) || p[i].y > p[j].y) && ((i != 1 && j != 4) || p[i].y < p[j].y))) {
                    dis.push(dx + dy);
                    d[dis[dis.length - 1]] = [i, j];
                }
            }
        }
        if (dis.length == 0) {
            var res = [0, 4];
        } else {
            res = d[Math.min.apply(Math, dis)];
        }
        var x1 = p[res[0]].x,
            y1 = p[res[0]].y,
            x4 = p[res[1]].x,
            y4 = p[res[1]].y;
        dx = Math.max(Math.abs(x1 - x4) / 2, 10);
        dy = Math.max(Math.abs(y1 - y4) / 2, 10);
        var x2 = [x1, x1, x1 - dx, x1 + dx][res[0]].toFixed(3),
            y2 = [y1 - dy, y1 + dy, y1, y1][res[0]].toFixed(3),
            x3 = [0, 0, 0, 0, x4, x4, x4 - dx, x4 + dx][res[1]].toFixed(3),
            y3 = [0, 0, 0, 0, y1 + dy, y1 - dy, y4, y4][res[1]].toFixed(3);
        var pathString = ["M", x1.toFixed(3), y1.toFixed(3), "C", x2, y2, x3, y3, x4.toFixed(3), y4.toFixed(3)].join(",");
        var start = {'x' : x3, 'y' : y3};
		//var start = p[res[0]];
		//var end = {'x' : x4.toFixed(3), 'y' : y4.toFixed(3)};
		var end = p[res[1]];
        return {'pathString' : pathString, 'start' : start, 'end' : end, 'type' : 'curve'};
    }


	function getLinePathInfo(fromId, toId) {
        var bb1 = getBox(fromId),
            bb2 = getBox(toId),
            p = [{x: bb1.x + bb1.width / 2, y: bb1.y - 1},
            {x: bb1.x + bb1.width / 2, y: bb1.y + bb1.height + 1},
            {x: bb1.x - 1, y: bb1.y + bb1.height / 2},
            {x: bb1.x + bb1.width + 1, y: bb1.y + bb1.height / 2},
            {x: bb2.x + bb2.width / 2, y: bb2.y - 1},
            {x: bb2.x + bb2.width / 2, y: bb2.y + bb2.height + 1},
            {x: bb2.x - 1, y: bb2.y + bb2.height / 2},
            {x: bb2.x + bb2.width + 1, y: bb2.y + bb2.height / 2}],
            d = {}, dis = [];
        for (var i = 0; i < 4; i++) {
            for (var j = 4; j < 8; j++) {
                var dx = Math.abs(p[i].x - p[j].x),
                    dy = Math.abs(p[i].y - p[j].y);
                if ((i == j - 4) || (((i != 3 && j != 6) || p[i].x < p[j].x) && ((i != 2 && j != 7) || p[i].x > p[j].x) && ((i != 0 && j != 5) || p[i].y > p[j].y) && ((i != 1 && j != 4) || p[i].y < p[j].y))) {
                    dis.push(dx + dy);
                    d[dis[dis.length - 1]] = [i, j];
                }
            }
        }
        if (dis.length == 0) {
            var res = [0, 4];
        } else {
            res = d[Math.min.apply(Math, dis)];
        }
        var x1 = p[res[0]].x,
            y1 = p[res[0]].y,
            x4 = p[res[1]].x,
            y4 = p[res[1]].y;
        dx = Math.max(Math.abs(x1 - x4) / 2, 10);
        dy = Math.max(Math.abs(y1 - y4) / 2, 10);
        var x2 = [x1, x1, x1 - dx, x1 + dx][res[0]].toFixed(3),
            y2 = [y1 - dy, y1 + dy, y1, y1][res[0]].toFixed(3),
            x3 = [0, 0, 0, 0, x4, x4, x4 - dx, x4 + dx][res[1]].toFixed(3),
            y3 = [0, 0, 0, 0, y1 + dy, y1 - dy, y4, y4][res[1]].toFixed(3);
        var pathString = ["M", x1.toFixed(3), y1.toFixed(3), "L",  x4.toFixed(3), y4.toFixed(3)].join(",");

		var start = p[res[0]];
		var end = p[res[1]];
        return {'pathString' : pathString, 'start' : start, 'end' : end, 'type' : 'line'};
    }

    function getBox(id) {
        var $box = $('#' + id);
        var box = {};
        if (!$box.length) { return box; }
        box.width = $box[0].offsetWidth;
        box.height = $box[0].offsetHeight;
        //var offset = $box.offset();
        var offset = $box.position();
        box.x = offset.left;
        box.y = offset.top;
        return box;
    }
};

$.fn.relate = function (option) {
    var defaults = {
        width : $(this).width(),
        height : 1000,
        itemXMargin : 200, //x轴的间隔
        itemYMargin : 100, //y轴的间隔
        itemXMaxCount : 8, //x轴最大容纳的个数
        lineStyle : "curve", //线条样式
        rMap : [],
        objs : []
    };

    option = $.extend(defaults, option);
    
    var rMap = option.rMap;
    var objs = option.objs;
    var $this = this;
    var raphael = Raphael(this.attr('id'), option.width, option.height);
    
    drawDiv(rMap, objs);
    var pathMap = drawPath(objs, rMap);
    bindEvent(pathMap);

    function getAvg(rMap) {
        //计算平均值
        var sum = 0;
        var count = 0;
        for (var fromId in rMap) {
            var values = rMap[fromId];
            for (var toId in values) {
                count++;
                sum += parseFloat(values[toId]);
            }
        }
        return sum / count;
    }
    
    function getLevelMap(rMap) {
        var level = 0, idMap = {};
        for (var fromId in rMap) {
             idMap[fromId] = idMap[fromId] || 0;
             level = idMap[fromId] + 1;
             for (var toId in rMap[fromId]) {
                 idMap[toId] = level;
             }
        }        
        
        //每层限制10，如果超过则往下推
        var addVar = 0;
        var levelMap = {};
        for (var id in idMap) {
            //新的层次
            level = idMap[id] + addVar;
            levelMap[level] = levelMap[level] || []; 
            
            if (levelMap[level].length >= option.itemXMaxCount) {
                addVar++;
            }
            
            levelMap[level].push(id);  
            idMap[id] = level;
        }
        
        return levelMap;
    }
    
    function drawDiv(rMap, map) {
        var marginRight = option.itemXMargin;
        var marginBottom = option.itemYMargin;
        var bodyMid = $(document).width() / 2;
        
        var levelMap = getLevelMap(rMap);
        var positionMap = {};
        for (var i in levelMap) {
            var count = levelMap[i].length;
            if (count == 1) {
                id = levelMap[i][0];
                if (i == 0) {
                    var tempMargin = 0;
                } else {
                    //var tempMargin = i % 2 ? -marginRight / 2 : marginRight / 2;
                    var tempMargin = 0;
                }
                
                positionMap[id] = {'top' : i * marginBottom, 'left' : bodyMid};
            } else {
                for (var j = 0; j < count; j++) {
                    id = levelMap[i][j];
                    positionMap[id] = {'top' : i * marginBottom, 'left' : bodyMid - ((count - 1)/2 - j ) * marginRight};
                }
            }
        }

        var html = '';
        for (var id in positionMap) {
            var attr = positionMap[id];
            html += '<div id="' + id + '" class="box_div" style="left:' + attr['left']  + 'px;top:' + attr['top'] + 'px;">' + map[id]['text'] + '</div>';
        }
        
        $this.append(html);
    }
    
    function drawPath(objs, rMap) {
        var avg = getAvg(rMap);
        //绘制线条
        var pathMap = {};
        for (var fromId in rMap) {
            var values = rMap[fromId];
            for (var toId in values) {            
                var value = values[toId];
                var other = {color : Raphael.getColor(), type : objs[fromId]['type'], value : value, style : option.lineStyle, baseValue : avg / 1.5};
                pathMap[fromId + '-' + toId] = raphael.connection(fromId, toId, other);
            }
        }
        
        return pathMap;
    }
    
    function bindEvent(pathMap) {
        // 数据节点绑定拖拽事件
        $this.find('div[id]').draggable({
            drag: function(event, ui) { 
                if (event.target.nodeName != "DIV") {
                    var id = $(event.target).closest("div").attr("id");
                } else {
                    var id = event.target.id;
                }
                
                $('[id^=' + id + '-],[id$=-' + id + ']').each(function() {
                    var ids = this.id.split('-');
                    var other = pathMap[ids[0] + '-' + ids[1]];
                    raphael.connection(ids[0], ids[1], other);
                });
                raphael.safari();
            },
            cancel: "span"
        });
        
        // 数据节点绑定事件
        $(".box_div").on("click", function(event) {
            if (event.ctrlKey) {
                $(this).toggleClass("box_selected");
            }
        });
        
        // 绑定鼠标右键事件
        $(document).off("contextmenu").on("contextmenu", ".box_selected", function() {
            if (confirm("需要分析?")) {
                var pages = [];
                $(".box_selected").each(function(i) {
                    pages[i] = $(this).text();
                });
                
                $('[field=parentPage][opt=in]').find("textarea").val(pages.join("\n"));
                //$('[field=page][opt=in]').find("textarea").val(pages.join("\n"));
                $("#btnSearch").click();
                
            }
            return false;
        });
    }
};
