define(function(require, exports, module) {
    var tpl = require("tpl");
    var $ = require("jquery");
    require("treeTable");
    var $treeTable;
    var g_comments = {};

    var M = {
        getParam : function(funcName, pid) {
            var url = BDY.url + funcName + "?doc=func";
            $.get(url, function(objResult) {
                if (!objResult.result) {
                    alert(objResult.msg);
                } else {
                    objResult.data.pid = pid;
                    objResult.data.actionName = funcName;
                    var html = tpl.render("table_tr", objResult.data);
                    var $html = $(html);
                    $treeTable.addChilds($html);
                    $html.find('[role=presentation]:first').click();
                }
            }, 'json');
        },
        removeRet : function(funcName, index) {
            var url = BDY.url + "doc/removeRet?action=" + funcName + '&code=' + index;
            $.get(url, function(objResult) {
                if (objResult.result) {
                    $('[pid="' + funcName + '"]').find('div[id=' + index + ']').remove();
                } else {
                    alert(objResult.msg);
                }

            }, 'json');
        },
        saveComment : function(funcName, code, path, text) {
            var url = BDY.url + "doc/saveComment";
            var data = {
                action : funcName,
                code : code,
                path : path,
                text : text
            };

            $.post(url, data, function(objResult) {
                if (objResult.result) {
                    var $comments = $(".comment[data-path='" + path + "']");
                    $comments.find('.comment-text').text(text);
                    $comments.find('.comment-textarea').val(text);
                } else {
                    alert(objResult.msg);
                }
            }, 'json');
        }
    };

    var C = {
        init : function() {
            var options = {
                column : 0,
                expandLevel : 2
            };

            $treeTable = $('#class_list').treeTable(options);
            $treeTable.on(BDY.click, "[controller=true]", function(event) {
                var target = event.target;
                var $tr = $(target).parents("tr");
                var pid = $tr.attr("id");

                // 判断是否加载过
                if (!$("tr[pId='" + pid + "']").length) {
                    var funcName = $(target).text().trim() || $(target).next().text().trim();
                    M.getParam(funcName, pid);
                }
            });

            C.initNav();
        },
        initNav : function() {
            $(document).on(BDY.click, '[role=presentation]', function() {
                var $this = $(this);
                $this.siblings().removeClass('active');
                $this.addClass('active');

                var i = $this.text().trim();
                var $parentDiv = $this.parent().next();
                $parentDiv.find('div').hide();
                var $div = $parentDiv.find('div[id="' + i + '"]').show();

                var json = $div.find('[data-role=ret]').val();
                if (json) {
                    var $span = $div.find('span');
                    $span.html(formatJson(JSON.parse(json)));
                    $div.find('[data-role=ret]').remove();

                    $span.find('.json_key').each(function() {
                        var path = $(this).attr('data-path');
                        var $firstBr = $(this).next().next();
                        if ($firstBr[0].nodeName != 'BR') {
                            $firstBr = $firstBr.find('br:first');
                        }

                        var name = $(this).text().replace(/"/g, '');
                        var comment = g_comments[name] || name;
                        $firstBr.before('<span class="comment" data-path="' + path + '">' +
                            '<span class="comment-line"></span>' +
                            '<textarea class="comment-textarea">' + comment + '</textarea>' +
                            '<span class="comment-text">' + comment + '</span>' +
                            '</span>');
                    });

                    var comments = $div.find('[data-role=comments]').val();
                    $div.find('[data-role=comments]').remove();
                    if (comments) {
                        comments = JSON.parse(comments);
                        for (var path in comments) {
                            var $comments = $(".comment[data-path='" + path + "']");
                            $comments.find('.comment-text').text(comments[path]);
                            $comments.find('.comment-textarea').val(comments[path]);
                        }
                    }

                }
            });

            $(document).on(BDY.click, 'a[xIndex]', function() {
                var $this = $(this);
                var index = $this.attr('xIndex');
                var funcName = $this.parents('tr[pid]').attr('pid');
                // funcName = funcName.replace('_', '/');

                M.removeRet(funcName, index);
            });

            $(document).on(BDY.click, 'a[editIndex]', function() {
                var isEdit = $(this).parent().hasClass('edit');
                if (isEdit) {
                    $(this).parent().removeClass('edit');
                } else {
                    $(this).parent().addClass('edit');
                }
            });

            $(document).on('change', '.comment-textarea', function() {
                var $this = $(this);
                var code = $this.parents('[code_id]').attr('code_id');
                var funcName = $this.parents('tr[pid]').attr('pid');
                var path = $this.parent().attr('data-path');
                var text = $this.val();

                M.saveComment(funcName, code, path, text);
            });
        }
    };

    C.init();

    exports.init = function(data) {
        g_comments = data;
    }
});