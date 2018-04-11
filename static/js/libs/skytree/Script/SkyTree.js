//////////////////////////////////////////////////////////////////////////////////////////////////
//      ______                                            _____                                 //
//      |__   _|            _    ____                    / ____| _               _  _           //
//         | |  _   _  ___ | |_ |  _ \  _   _  _ __     | (___  | |_  _   _   __| |(_)  ___     //
//     __  | | | | | |/ __|| __|| |_) || | | || '_ \      \___ \| __|| | | | / _` || | / _ \    //
//     \ \_/ / | |_| |\__ \| |_ |  _ < | |_| || | | |    ____) || |_ | |_| || (_| || || (_) |   //
//      \___/   \__,_||___/ \__||_| \_\ \__,_||_| |_|   |_____/  \__| \__,_| \__,_||_| \___/    //
//                                                                                              //
//                    ____   _         _     __                                                 //
//                   |  _ \ | |  __ _ | |_  / _|  ___   _ __  _ __ ___                          //
//                   | |_) || | / _` || __|| |_  / _ \ | '__|| '_ ` _ \                         //
//                   |  __/ | || (_| || |_ |  _|| (_) || |   | | | | | |                        //
//                   |_|    |_| \__,_| \__||_|   \___/ |_|   |_| |_| |_|     v2.0               //
//                                                                                              //
//                                                                                              //
//                                          Copyrights (c) 2005-2006 Powerd By JustRun Studio   //
//                                                        All Rights Reserved.                  //
//                                                                                              //
//                                                                                              //
//////////////////////////////////////////////////////////////////////////////////////////////////

/*------------ Version information ------------*/
ST_VER_EDITION = 'Interal';
ST_VER_NUMBER = '2.1';
ST_VER_BUILD = '1212';
ST_LISENCE_KEY = '';

/*------------ Constants define ------------*/
ST_DISPLAY_MODE_HTML = 0;  //show as HTML
ST_DISPLAY_MODE_SELECT = 1; //show as options

ST_SLIDE_MODE_DYNAMIC = 0; //dynamical sliding
ST_SLIDE_MODE_STATIC = 1; //static sliding

ST_DEFAULT_THEME = 'Default'; //default theme
ST_DEFAULT_BASE_PATH = ''; //default base path
ST_ABOUT_PATH = 'Script/Dialog/Dialog.About.html'; //about dialog path

ST_RESPONSE_TIMEOUT = 3000;//response timeout
ST_AUTO_EXPAND_DELAY = 500; //delay when drag auto expand node
ST_SCROLL_DELAY = 2;

ST_EDIT_TEXT_MIN_WIDTH = 50; //min width for editing text.

ST_DEFAULT_LANG = 'en'; //default language

ST_DEFAULT_NORMAL_CLASS = 'TCnt'; //default normal node class
ST_DEFAULT_FOCUS_CLASS = 'TCntFocus'; //default focused node class

ST_SEARCH_VALUE = 1; //search by value
ST_SEARCH_TEXT = 2; //search by text
ST_SEARCH_HINT = 4; //search by hint

//Icon list
ST_TREE_ICON = {
'TI_OPEN'  : 'FOpen.gif',
'TI_CLOSE' : 'FClose.gif',
'TI_LMINUS': 'Lminus.gif',
'TI_LPLUS' : 'Lplus.gif',
'TI_TMINUS': 'Tminus.gif',
'TI_TPLUS' : 'Tplus.gif',
'TI_FMINUS': 'Fminus.gif',
'TI_RPLUS' : 'Rplus.gif',
'TI_RMINUS': 'Rminus.gif',
'TI_FPLUS' : 'Fplus.gif',
'TI_BLANK' : 'Blank.gif',
'TI_LEAF'  : 'Leaf.gif',
'TI_F'     : 'F.gif',
'TI_I'     : 'I.gif',
'TI_L'     : 'L.gif',
'TI_T'     : 'T.gif',
'TI_ABOUT' : 'About.gif',
'I_INFO'   : 'Info.gif',
'I_WARNING': 'Warning.gif',
'I_ERROR'  : 'Error.gif',
'I_LOADING': 'Loading.gif',
'TAG_UP'   : 'DropUp.gif',
'TAG_DOWN' : 'DropDown.gif',
'TAG_SUB'  : 'Sub.gif',
'TAG_CHECK': 'Checked.gif',
'CM_NEWSIB': 'AddSibling.gif',
'CM_NEWCHD': 'AddChild.gif',
'CM_EDIT'  : 'Edit.gif',
'CM_DEL'   : 'Delete.gif',
'CM_EXEC'  : 'Execute.gif',
'CM_EXP'   : 'Expand.gif',
'CM_COL'   : 'Collapse.gif',
'CM_FEC'   : 'FullExpCol.gif',
/*-- Reserved --*/
'I_UNKNOWN': 'Unknown.gif'
}

/*------------ Global parameters ------------*/
var g_focusInstance = '';
var g_focused = false;
var g_langMapping = { //valid language mapping
'en'    : 'en',
'zh-cn' : 'cn',
'zh-tw' : 'tw',
'ja'    : 'ja'
};
var g_cursorSheet = null;
if(document.createStyleSheet){
    g_cursorSheet = document.createStyleSheet();
}


/*------------ Class SkyTree ------------*/
function SkyTree(iName,cId){
    if(!iName || !cId){
        alert('Parameters are not enough to initialize SkyTree.');
        return null;
    }
    this.id = '';
    this.instanceName = iName;
    this.basePath = ST_DEFAULT_BASE_PATH;
    this.checkName = '';
    this.isRadio = false;
    this.autoCheck = false;
    this.displayMode = ST_DISPLAY_MODE_HTML;
    this.slideMode = ST_SLIDE_MODE_DYNAMIC;
    this.theme = ST_DEFAULT_THEME;
    this.singleExpand = true; //set to 'false' if full expanding needed
    this.expandLevel = 0;
    this.hintBeforeDelete = true;

    this.defMethod = ''; //default method for new nodes
    this.focusExecute = false; //when focused or single click node to execute method

    this.readOnly = false;
    this.allowFocus = true;
    this.allowDragDrop = true;
    this.allowContextMenu = true;

    this.lang = (navigator.systemLanguage || navigator.language).toLowerCase();
    this.langLib = {};

    this.defIconExp = '';
    this.defIconCol = '';
    this.defIconLeaf = '';

    this.requestURL = '';

    this.initialized = false;
    this.loaded = false; //is it the first loading

    this.eleContainer = document.getElementById(cId);
    this.eleMain = null;
    this.eleIframe = null;
    this.eleText = null;
    this.eleInfo = null;
    this.eleAbout = null;
    this.contextMenu = null;

    this.infoTimer = null;
    this.scrollTimer = null;

    this.ds = null; //data structure
    this.rootNode = null;
    this.tempData = null;

    this.curNode = null;
    this.maxId = -1;

    this.countNode = 0;

    //customized events handler
    this.oninitialize = function(tree){};
    this.ondestroy = function(tree){};
    this.beforeinsert = function(tree,sender){};
    this.beforeedit = function(tree,sender){};
    this.beforedelete = function(tree,sender){};
    this.beforedrop = function(tree,senderSrc,senderDes,order){};
    this.afterinsert = function(tree,sender){};
    this.afteredit = function(tree,sender){};
    this.afterdelete = function(tree){};
    this.afterdrop = function(tree,sender){};
    this.beforeExpandNode = function(tree, sender){};

    this.eventCanceled = false;

    //for drag and drop
    this.inDrag = false;
    this.preDrag = false;
    this.curDragNode = null;
    this.orgX = 0;
    this.orgY = 0;
    this.curX = 0;
    this.curY = 0;
    this.curOrder = -1;
    this.curTgt = null;
    this.cloneDiv = null;
    //for search method
    this.curKey = '';
    this.curResult = new Array();
    this.curResultIdx = -1;
    //  this.initialize();
};

//Initialization of SkyTree object
SkyTree.prototype.initialize = function(cfg){
    var iName = this.instanceName;
    //set config if 'cfg' is supplied
    if(typeof(cfg)!='undefined'){
        if(cfg['id']){this.id = cfg['id'];}
        if(cfg['basePath']){this.basePath = cfg['basePath'];}
        if(cfg['lang']){this.lang = cfg['lang'];}
        if(cfg['checkName']){this.checkName = cfg['checkName'];}
        if(typeof(cfg['isRadio'])!='undefined'){this.isRadio = cfg['isRadio'];}
        if(typeof(cfg['autoCheck'])!='undefined'){this.autoCheck = cfg['autoCheck'];}
        if(cfg['displayMode']){this.displayMode = cfg['displayMode'];}
        if(cfg['slideMode']){this.slideMode = cfg['slideMode'];}
        if(cfg['theme']){this.theme = cfg['theme'];}
        if(typeof(cfg['singleExpand'])!='undefined'){this.singleExpand = cfg['singleExpand'];}
        if(cfg['expandLevel']){this.expandLevel = cfg['expandLevel'];}
        if(cfg['defMethod']){this.defMethod = cfg['defMethod'];}
        if(typeof(cfg['focusExecute'])!='undefined'){this.focusExecute = cfg['focusExecute'];}
        if(typeof(cfg['readOnly'])!='undefined'){this.readOnly = cfg['readOnly'];}
        if(typeof(cfg['allowFocus'])!='undefined'){this.allowFocus = cfg['allowFocus'];}
        if(typeof(cfg['allowDragDrop'])!='undefined'){this.allowDragDrop = cfg['allowDragDrop'];}
        if(typeof(cfg['allowContextMenu'])!='undefined'){this.allowContextMenu = cfg['allowContextMenu'];}
        if(typeof(cfg['hintBeforeDelete'])!='undefined'){this.hintBeforeDelete = cfg['hintBeforeDelete'];}
        if(cfg['defIconExp']){this.defIconExp = cfg['defIconExp'];}
        if(cfg['defIconCol']){this.defIconCol = cfg['defIconCol'];}
        if(cfg['defIconLeaf']){this.defIconLeaf = cfg['defIconLeaf'];}
        if(cfg['requestURL']){this.requestURL = cfg['requestURL'];}
        if(cfg['tempData']){this.tempData = cfg['tempData'];}
    }

    //create data structure
    this.ds = new Object();
    //create basic DOM elements.
    this.eleMain = document.createElement('div');
    this.eleMain.id = this.instanceName + 'Div';

    this.eleInfo = document.createElement('div');
    this.eleText = document.createElement('input');

    this.eleContainer.appendChild(this.eleMain);

    this.eleMain.className = this.theme;
    this.eleMain.appendChild(this.eleInfo);
    this.eleMain.appendChild(this.eleText);
    /*  this.eleMain.onmousemove = function(){
    if(eval(iName).curDragNode != 0){
    eval(iName).getNode(eval(iName).curDragNode).dragStart();
    }
    }*/
    this.eleMain.onmouseup = function(e){
        e = window.event || e;var key = e.keyCode || e.which;
        with(eval(iName)){
            if(inDrag && preDrag){
                //eleMain.releaseCapture();
                if(!curTgt){
                    //showInfo('Invalid place!','I_INFO',1000);
                }else if(curTgt.allowDrop){
                    //showInfo('Drop node ' + eval(iName).curDragNode.index + ' at node ' + curTgt.index,'I_INFO',1000);
                    if(curOrder == -1){
                        placeNode(curDragNode.index,curTgt.index);
                    }else{
                        placeNode(curDragNode.index,curTgt.parent.index,curOrder);
                    }
                }else{
                    showInfo(eval(iName).getStr('NoDrop'),'I_WARNING',1000);
                }
            }
            /*else if(preDrag){
            showInfo('No action!','I_INFO',1000);
            }*/
            orgX = 0;
            orgY = 0;
            curX = 0;
            curY = 0;
            curOrder = -1;
            if(inDrag){
                document.body.removeChild(eval(iName).cloneDiv);
                eval(iName).cloneDiv = null;
                if(document.createStyleSheet){
                    g_cursorSheet.cssText = '';
                }
                if(curTgt){
                    curTgt.eleNode.style.borderBottom = '';
                    curTgt.eleNode.style.borderTop = '';
                    curTgt.removeTag();
                    curTgt = null;
                }
            }
            curDragNode = null;
            preDrag = false;
            inDrag = false;
            document.onmousemove = null;
        }
    }
    this.eleInfo.className = 'Info';
    this.eleInfo.style.display = 'none';
    this.eleText.className = 'EditText';
    this.eleText.style.position = 'absolute';
    this.eleText.style.display = 'none';
    this.eleText.onkeydown = function(e){
        e = window.event || e;var key = e.keyCode || e.which;
        e.cancelBubble = true;
        switch(key){
            case 13 :
            eval(iName).setText(eval(iName).curNode.index);
            return false;
            case 27 :
            with(eval(iName).eleText){ //hide text editor and do nothing
                onblur = null;
                style.display = 'none';
            }
            return false;
        }
    }

    this.showInfo('Initializing [' + this.instanceName + ']...','I_LOADING',1000);

    //set shortcut for tree
    //被我注释掉了
    /*
    this.onkeydown = function(e){
        e = window.event || e; var key = e.keyCode || e.which;
        if(g_focusInstance == iName && g_focused){
            if(eval(iName).contextMenu){eval(iName).contextMenu.hide();}
            e.cancelBubble = true;
            if(e.shiftKey){
                switch(key){
                    case 45 : //Add subNode
                    if(eval(iName).curNode){
                        eval(iName).addNode(eval(iName).curNode.index,true,3);
                    }
                    return false;
                }
            }
            switch(key){
                case 13 :
                if(eval(iName).curNode){
                    eval(iName).getNode(eval(iName).curNode.index).execute();
                }
                return false;
                case 27 :
                eval(iName).releaseControl();  //Release control
                return false;

                case 32 :
                if(eval(iName).curNode){ //Check this node;
                    with(eval(iName).curNode){
                        if(eleInput){
                            eleInput.checked = !eleInput.checked;
                            eleInput.onclick();
                        }
                    }
                }
                return false;

                case 37 : eval(iName).upperNode(); return false;  //Arrow left, shrink child node
                case 38 : eval(iName).pervNode(); return false;  //Arrow up
                case 39 : eval(iName).lowerNode(); return false;  //Arrow right, expand child node
                case 40 : eval(iName).nextNode();  return false;  //Arrow down

                case 45 : eval(iName).addNode(eval(iName).curNode.index,false,3); return false;  //Add sibling node
                case 46 : eval(iName).removeNode(); return false; //Delete node

                case 107: //Full expand/collapse focused node
                if(eval(iName).curNode){
                    eval(iName).nodeFullExpCol(eval(iName).curNode.index);
                }
                return false;
                case 113: //Edit display text
                if(eval(iName).curNode){
                    eval(iName).curNode.editText();
                }
                return false;
                case 190: //Get next result
                if(eval(iName).curResult.length > 0){
                    eval(iName).searchKey(eval(iName).curKey,0);
                }
                return false;
                case 188: //Get previous result
                if(eval(iName).curResult.length > 0){
                    eval(iName).searchKey(eval(iName).curKey,1);
                }
                return false;
            }
        }else if(key == 27 && eval(iName).inDrag){
            eval(iName).preDrag = false;
            eval(iName).eleMain.onmouseup(e);
            eval(iName).showInfo(eval(iName).getStr('DragCanceled'),'I_INFO',1000);
        }
    };

    if (document.attachEvent){
        document.attachEvent('onkeydown', this.onkeydown);
    }else if (document.addEventListener){
        document.addEventListener('keydown', this.onkeydown, false);
    }
    */
    //create virtual root node
    this.rootNode = new SkyTreeNode(this,null,null);
    this.rootNode.eleNode = document.createElement('div'); //node element object
    this.rootNode.eleSub = document.createElement('div');
    this.rootNode.eleNode.appendChild(this.rootNode.eleSub);
    //rootNode.displayed = true;
    this.ds[this.rootNode.index] = this.rootNode;
    this.eleMain.appendChild(this.rootNode.eleNode);

    //set default theme.
    this.setTheme(this.theme);
    //set language package
    this.setLang(this.lang);

    if(typeof(this.oninitialize) == 'function'){this.oninitialize(this);}

    this.initialized = true;

    if(this.tempData != null){
        this.transData(0);
    }
};

//Set about layer
SkyTree.prototype.setAbout = function(){
    var icon = this.createIcon('TI_ABOUT',this.getStr('AboutButton'));
    icon.border = 0;
    icon.align = 'absmiddle';
    icon.style.verticalAlign = 'middle';
    var aLink = document.createElement('a');
    aLink.href = 'javascript:void(0)';
    aLink.onclick = Function(this.instanceName + '.showAbout()');
    aLink.appendChild(icon);
    var aCopyright = document.createElement('span');
    aCopyright.innerHTML = this.getStr('Copyright');

    if(this.eleAbout){
        this.eleAbout.parentNode.removeChild(this.eleAbout);
    }
    this.eleAbout = document.createElement('div');
    this.eleAbout.className = 'About';
    this.eleAbout.appendChild(aLink);
    this.eleAbout.appendChild(aCopyright);
    this.eleMain.appendChild(this.eleAbout);
};

//Show about dialog
SkyTree.prototype.showAbout = function(){
    var Win = window.open(this.basePath + ST_ABOUT_PATH + '?iname=' + this.instanceName,'About','width=595,height=320,'
    + 'top=' + parseInt((screen.height - 320)/2) + ',left=' + parseInt((screen.width - 600)/2)
    + ',resizable=0,scrollbars=no,menubar=no,status=no' );
};

//Import language
SkyTree.prototype.setLang = function(aLang){
    var i;
    //check if language is valid
    if(!g_langMapping[aLang]){
        this.lang = ST_DEFAULT_LANG;
        aLang = g_langMapping[ST_DEFAULT_LANG];
    }else{
        this.lang = aLang;
        aLang = g_langMapping[aLang];
    }
    var aPath = this.basePath + 'Script/Lang/' + aLang + '.js';
    importScript(aPath);
    setTimeout(this.instanceName + '.langLib=STL' + aLang,500);
    //3setTimeout(this.instanceName + '.setAbout()',500);
    //remove context menu
    if(this.contextMenu && !this.contextMenu.customized){
        this.contextMenu.hide();
        this.eleMain.removeChild(this.contextMenu.eleMenu);
        this.contextMenu = null;
    }
};

//Get string in local langauge
SkyTree.prototype.getStr = function(aKey){
    if(!this.langLib[aKey]){
        return '{$String:' + aKey + '}';
    }else{
        return this.langLib[aKey];
    }
};

//Destory elements and data structure.
SkyTree.prototype.destroy = function(){
    if(!this.initialized){return;}
    if(g_focusInstance == this.instanceName){
        this.releaseControl();
    }
    this.eleMain.parentNode.removeChild(this.eleMain);
    //  this.eleInfo.parentNode.removeChild(this.eleInfo);
    if(this.eleIframe){this.eleIframe.parentNode.removeChild(this.eleIframe);}

    if (document.detachEvent && this.onkeydown){
        document.detachEvent('onkeydown', this.onkeydown);
    }else if (document.removeEventListener){
        document.removeEventListener('keydown', this.onkeydown, false);
    }

    //this.eleContainer = null;
    this.ds = null;
    this.tempData = null;
    this.curNode = null;
    this.countNode = 0;
    this.maxId = -1;
    this.loaded = false;
    this.contextMenu = null;

    if(typeof(this.ondestroy) == 'function'){this.ondestroy(this);}

    this.initialized = false;
};

//Release control
SkyTree.prototype.releaseControl = function(){
    g_focusInstance = '';
    g_focused=false;
    if(this.contextMenu){
        this.contextMenu.hide();
    }
    this.curNode.eleContent.className = this.curNode.classNormal;
    this.showInfo(this.getStr('CtrlReleased'),'I_INFO',1000,this.curNode.index);
    this.curNode = null;
}

//Show information
SkyTree.prototype.showInfo = function(iText,ico,delay,pIdx){
    return;
    var objText,objIcon;
    var l,t;

    if(!ico){ico='I_INFO';}
    delay = parseInt(delay);
    objIcon = this.createIcon(ico);
    objText = document.createElement('span');
    objText.innerHTML = '&nbsp;' + iText;

    //clear original text
    this.eleInfo.innerHTML = '';
    //add new infomation
    this.eleInfo.appendChild(objIcon);
    this.eleInfo.appendChild(objText);
    //reset position
    this.eleInfo.style.display = '';
    this.fixInfoPos(pIdx);

    if(this.infoTimer){this.infoTimer = window.clearTimeout(this.infoTimer);}
    if(!isNaN(delay)){
        this.infoTimer = setTimeout(this.instanceName + '.hideInfo()',delay);
    }else{
        this.infoTimer = setTimeout(this.instanceName + '.showInfo("' + this.getStr('Timeout') + '","I_ERROR",1000)',ST_RESPONSE_TIMEOUT);
    }
};

//Hideinformation
SkyTree.prototype.hideInfo = function(){
    if(this.infoTimer)this.infoTimer = window.clearTimeout(this.infoTimer);
    this.eleInfo.style.display = "none";
};

//Fix the position of infomation layer.
SkyTree.prototype.fixInfoPos = function(pIdx){
    var rectTree,rectDiv;
    var l,t;
    var aNode,eleRect;
    pIdx = parseInt(pIdx);
    if(typeof(pIdx)=='undefined'){pIdx = 0;}
    aNode = this.getNode(pIdx);
    if(aNode){
        eleRect = aNode.eleIconGrp;
    }else{
        eleRect = this.eleMain;
    }
    if(eleRect){
        rectTree = getAbsoluteLocationEx(eleRect);
        rectDiv = getAbsoluteLocationEx(this.eleInfo);

        l = 10;//parseInt((rectTree.offsetWidth - rectDiv.offsetWidth)/2 + rectTree.absoluteLeft);
        t = parseInt((rectTree.offsetHeight - rectDiv.offsetHeight)/2 + rectTree.absoluteTop);

        this.eleInfo.style.left = l + 'px';
        this.eleInfo.style.top = t + 'px';
    }
};

//Set theme of the tree.
SkyTree.prototype.setTheme = function(aTheme){
    var imported = false;
    var aHref,aCSSName;
    var arrImg;
    var i;
    //check if stylesheet is imported.
    for(i in document.styleSheets){
        aHref = document.styleSheets[i].href;
        //match the name
        if((/Theme\/(.+?)\/Tree.css/).exec(aHref) != null){
            aCSSName = RegExp.$1;
            if(aCSSName == this.theme){
                imported = true;
                break;
            }
        }
    }
    if(!imported){ //import new style sheet
        var aCSS = document.createElement('link');
        aCSS.rel = 'stylesheet';
        aCSS.type = 'text/css';
        aCSS.href = this.basePath + 'Theme/' + this.theme + '/Tree.css';
        document.getElementsByTagName('head')[0].appendChild(aCSS);
        //document.createStyleSheet(this.basePath + 'Theme/' + this.theme + '/Tree.css');
    }

    this.theme = aTheme;
    this.eleMain.className = this.theme;

    //set default node icon
    if(this.defIconExp==''){this.defIconExp = this.getIcon('TI_OPEN');}
    if(this.defIconCol==''){this.defIconCol = this.getIcon('TI_CLOSE');}
    if(this.defIconLeaf==''){this.defIconLeaf = this.getIcon('TI_LEAF');}

    //if(this.theme == aTheme){return;}

    //replace tree stucture icons.
    arrImg = this.eleMain.getElementsByTagName('img');
    for(i=0;i<arrImg.length;i++){
        if((arrImg[i].className == 'Ico') || (arrImg[i].className == 'IcoStatus')){
            arrImg[i].removeAttribute("width",0);
            arrImg[i].removeAttribute("height",0);
            arrImg[i].src = arrImg[i].src.replace(/Theme\/.+?\/Icons/,'Theme\/' + this.theme + '\/Icons');
        }
    }
};

//Get an icon url
SkyTree.prototype.getIcon = function(ico){
    if(ST_TREE_ICON[ico]){
        return this.basePath + 'Theme/' + this.theme + '/Icons/' + ST_TREE_ICON[ico];
    }else{
        return this.basePath + 'Theme/' + this.theme + '/Icons/' + ST_TREE_ICON['I_UNKNOWN'];
    }
};

//Create an icon.
SkyTree.prototype.createIcon = function(ico,alt){
    var objIcon = document.createElement('img');
    objIcon.align = 'absmiddle';
    objIcon.style.verticalAlign = 'middle';
    objIcon.src = this.getIcon(ico);
    objIcon.className = 'Ico';
    if(alt){objIcon.alt = alt;}
    return objIcon;
};

//Create an icon by src.
SkyTree.prototype.createIconBySrc = function(src,alt){
    var objIcon = document.createElement('img');
    objIcon.align = 'absmiddle';
    objIcon.style.verticalAlign = 'middle';
    objIcon.src = src;
    objIcon.className = 'Ico';
    if(alt){objIcon.alt = alt;}
    return objIcon;
}

//Get new node Id
SkyTree.prototype.getNewId = function(){
    return ++this.maxId;
};

//get a node by Id
SkyTree.prototype.getNode = function(idx){
    if(this.ds && this.ds[idx]){
        return this.ds[idx];
    }else{
        return null;
    }
};


SkyTree.valueMap = {};

//get a node by value
SkyTree.prototype.getIdxByValue = function(value){
    return SkyTree.valueMap[value];
};

//Transfer node data core
SkyTree.prototype.transDataCore = function(rootId,data){
    var aNode;
    var d,i,idx;
    if(!data){
        data = this.tempData;
    }else{
        this.tempData = data;
    }
    if(!data['items']){return;}
    try{
        for(i in data['items']){
            d = data['items'][i];
            aNode = this.createNode(rootId,0);
            idx = aNode.index;
            if(d['text']){aNode.setText(d['text']);}
            if(d['value']){
                aNode.setValue(d['value']);
                SkyTree.valueMap[d['value']] = idx;
            }
            if(d['hint']){aNode.setHint(d['hint']);}
            if(d['method']){aNode.method = d['method'];}
            if(d['clsN']){aNode.classNormal = d['clsN'];}
            if(d['clsF']){aNode.classFocus = d['clsF'];}
            if(d['iExp'] || d['iCol'] || d['iLeaf']){aNode.setIcons(d['iLeaf'],d['iExp'],d['iCol']);}
            if(d['data']){aNode.data = d['data'];}
            if(typeof(d['isChecked'])!='undefined'){aNode.isChecked = d['isChecked'];}
            if(typeof(d['drag'])!='undefined'){aNode.allowDrag = d['drag'];}
            if(typeof(d['drop'])!='undefined'){aNode.allowDrop = d['drop'];}
            if(this.loaded || aNode.depth <= this.expandLevel || this.expandLevel == 0){
                aNode.show();
            }
            //被我移动到这里
            if(typeof(this.afterinsert) == 'function'){this.afterinsert(this,aNode);}
            if(d['items']){
                this.transDataCore(idx,d);
                if(d['items'] == ''){ //reset icon for JSON expanding
                    aNode.expandTag = true;
                    aNode.resetIcons();
                }
            }
        }
        this.getNode(rootId).expandTag = false;
    }catch(ex){
        this.showInfo('Error in transferring:' + ex.message);
    }
};

//Transfer node data
SkyTree.prototype.transData = function(rootId,data){
    if(!this.initialized){return;}
    this.transDataCore(rootId,data);
    this.loaded = true;
};

//Search result
SkyTree.prototype.searchKey = function(key,dir,scope,rootId){
    this.allowFocus = true;
    if(key == ''){ //clear result and return nothing
        this.curKey = '';
        this.curResult = new Array();
        this.curResultIdx = -1;
        return;
    }
    if(typeof(dir) == 'undefined'){dir = 0;}
    key = key.toLowerCase();
    if(this.curKey == key && this.curResult.length > 0){ //just focused on next result
        if(dir == 0){
            this.curResultIdx = ++this.curResultIdx % this.curResult.length;
        }else{
            this.curResultIdx = (--this.curResultIdx + this.curResult.length) % this.curResult.length;
        }
        this.expandToNode(this.curResult[this.curResultIdx]);
    }else{ //start a new searching
        this.curKey = key;
        this.curResult = new Array();
        this.curResultIdx = -1;
        if(typeof(scope) == 'undefined'){scope = ST_SEARCH_VALUE + ST_SEARCH_TEXT;}
        if(typeof(rootId) == 'undefined'){rootId = 0;}
        this.searchCore(rootId,key,scope); //execute searching
        if(this.curResult.length > 0){ //focus first result
            this.curResultIdx = (dir==0)?0:this.curResult.length;
            this.expandToNode(this.curResult[this.curResultIdx]);
            this.showInfo(formatString(this.getStr('CountResult'),this.curResult.length.toString()),'I_INFO',2000);
        }else{
            this.showInfo(formatString(this.getStr('NoResult'),this.curKey),'I_WARNING',2000);
        }
    }
};

//Search recursion
SkyTree.prototype.searchCore = function(idx,key,scope){
    var aNode = this.getNode(idx);
    if(!aNode){return;}
    var aChild = aNode.firstChild;
    var matches;
    while(aChild){
        matches = false;
        if(scope & ST_SEARCH_VALUE){
            if(aChild.value.toLowerCase().indexOf(key) > -1){matches = true;}
        }
        if(scope & ST_SEARCH_TEXT){
            if(aChild.text.toLowerCase().indexOf(key) > -1){matches = true;}
        }
        if(scope & ST_SEARCH_HINT){
            if(aChild.hint.toLowerCase().indexOf(key) > -1){matches = true;}
        }
        if(matches){this.curResult[this.curResult.length] = aChild.index;}
        this.searchCore(aChild.index,key,scope);
        aChild = aChild.nextSibling;
    }
}

//Add new node
SkyTree.prototype.addNode = function(idx,bSub,fShow){
    var aNode = this.getNode(idx);
    if(!aNode){return;}
    if(this.requestURL != ''){
        if(!bSub){
            idx = aNode.parent.index;
        }
        this.serverAddNode(idx);
        return null;
    }else{
        return this.clientAddNode(idx,bSub,fShow);
    }
};

//Add new node on client
SkyTree.prototype.clientAddNode = function(idx,bSub,fShow){
    if(this.readOnly){return;}
    if(typeof(idx)=='undefined'){
        if(this.curNode){
            idx = this.curNode.index;
        }else{
            idx = 0;
            bSub = true;
        }
    }
    var aNode,aBaseNode;
    //expand to next level before create new node
    aBaseNode = this.getNode(idx);
    if(aBaseNode.firstChild && !aBaseNode.firstChild.displayed){
        this.expandNode(aBaseNode.index,true,ST_SLIDE_MODE_STATIC);
    }
    if(bSub){
        aNode = this.createNode(idx,fShow);
    }else{
        aNode = this.createNode(aBaseNode.parent.index,fShow);
    }
    if(!aNode){
        this.showInfo(this.getStr('ParentInvalid'),'I_ERROR',2000);
    }else if(fShow & 2){
        //focus the new node
        this.expandToNode(aNode.index);
        // aNode.editText();
    }
    //this.hideInfo();
    return aNode;
}

//Create a tree node
SkyTree.prototype.createNode = function(idx,fShow){
    if(!this.initialized){return;}
    var aNode,aParent;
    this.eventCanceled = false;
    if(!this.initialized){return;}
    if(typeof(fShow)=='undefined'){fShow = 1;}
    //get parent first
    aParent = this.getNode(idx);
    if(!aParent){return null;}
    if(typeof(this.beforeinsert) == 'function'){this.beforeinsert(this,aParent);}
    if(this.eventCanceled){return;}
    aNode = new SkyTreeNode(this,aParent);
    if(this.defMethod != ''){aNode.method = this.defMethod;}
    this.ds[aNode.index] = aNode; //store in ds
    if((fShow & 1) && (this.loaded || aNode.depth <= this.expandLevel || this.expandLevel == 0)){
        aNode.show();
    }
    this.countNode++;
    if(typeof(this.afterinsert) == 'function'){this.afterinsert(this,aNode);}
    return aNode;
};

//Edit display text
SkyTree.prototype.setText = function(idx){
    var aNode = this.getNode(idx);
    this.eleText.style.display = 'none';
    if(!aNode){return;}
    var aText = this.eleText.value;
    if(aText == aNode.text){return;}
    if(this.requestURL != ''){
        this.serverSetText(idx,aText);
    }else{
        aNode.setText(aText);
    }
};

//Remove single node
SkyTree.prototype.removeNode = function(idx){
    if(this.readOnly){return;}
    if(this.hintBeforeDelete){
        if(!confirm(this.getStr('DeleteHint'))){
            return;
        }
    }
    if(typeof(idx)=='undefined' && this.curNode){
        idx = this.curNode.index;
    }else if(idx == 0){
        this.showInfo(this.getStr('RootUnremovable'),'I_WARNING',1000);
        return;
    }
    if(this.requestURL != ''){
        //this.showInfo('Deleting','I_LOADING');
        this.serverDelete(idx);
    }else{
        this.clientRemoveNode(idx);
    }
};

//Remove single node on client
SkyTree.prototype.clientRemoveNode = function(idx){
    if(this.readOnly){return;}
    var aNode = this.getNode(idx);
    this.eventCanceled = false;
    if(!aNode){return;}
    if(typeof(this.beforedelete) == 'function'){this.beforedelete(this,aNode);}
    if(this.eventCanceled){return;}
    var aChild = aNode.firstChild;
    while(aChild){
        this.coreRemoveNode(aChild.index);
        aChild = aChild.nextSibling;
    }
    //Reset relationship
    if(aNode.previousSibling){
        aNode.previousSibling.nextSibling = aNode.nextSibling;
        if(!aNode.nextSibling){
            this.fixIcons(aNode.previousSibling,aNode.depth-1,false);
        }
    }else{
        aNode.parent.firstChild = aNode.nextSibling;
    }
    if(aNode.nextSibling){
        aNode.nextSibling.previousSibling = aNode.previousSibling;
    }
    if(aNode.parent.index != 0){
        aNode.parent.resetIcons();
    }
    //focus other node
    if(aNode.nextSibling){
        this.focusNode(aNode.nextSibling.index);
    }else if(aNode.previousSibling){
        this.focusNode(aNode.previousSibling.index);
    }else if(aNode.parent.index != 0){
        this.focusNode(aNode.parent.index);
    }
    /*if(aNode.previousSibling && aNode.nextSibling){
    aNode.previousSibling.nextSibling = aNode.nextSibling;
    aNode.nextSibling.previousSibling = aNode.previousSibling;
    this.focusNode(aNode.nextSibling.index);
    }else if(aNode.previousSibling){
    aNode.previousSibling.nextSibling = null;
    aNode.previousSibling.resetIcons();
    this.focusNode(aNode.previousSibling.index);
    }else if(aNode.nextSibling){
    aNode.nextSibling.previousSibling = null;
    aNode.parent.firstChild = aNode.nextSibling;
    this.focusNode(aNode.nextSibling.index);
    }else{
    aNode.parent.firstChild = null;
    //fix parent icons
    if(aNode.parent.index != 0){
    aNode.parent.resetIcons();
    this.focusNode(aNode.parent.index);
    }
    }*/
    //remove from DOM
    aNode.eleNode.parentNode.removeChild(aNode.eleNode);
    //remove this node
    delete this.ds[idx];
    this.countNode--;
    if(typeof(this.afterdelete) == 'function'){this.afterdelete(this);}
}

//Core remove node
SkyTree.prototype.coreRemoveNode = function(idx){
    var aParent = this.getNode(idx);
    var tmpNode;
    if(!aParent){return;}
    aNode = aParent.firstChild;
    while(aNode){
        tmpNode = aNode;
        this.coreRemoveNode(aNode.index);
        aNode = tmpNode.nextSibling;
    }
    delete this.ds[idx];
    this.countNode--;
}

//Auto check child and parent nodes
SkyTree.prototype.checkRelative = function(idx,bChk){
    //check all it's parents
    var aNode = this.getNode(idx);
    var pSibling = aNode;
    aNode.isChecked = bChk;
    if(!this.autoCheck){return;}
    if(bChk){
        while(aNode = aNode.parent){
            if(aNode.index != 0){
                aNode.eleInput.checked = true;
                aNode.isChecked = true;
            }
        }
    }else{
        this.checkParentNodes(aNode.parent.firstChild.index);
    }
    //check all it's child nodes
    aNode = pSibling.firstChild;
    while(aNode){
        this.checkChlidNodes(aNode.index,bChk);
        aNode = aNode.nextSibling;
    }
};

//Check parent nodes
SkyTree.prototype.checkParentNodes = function(idx){
    var aNode = this.getNode(idx);
    if(aNode.parent.index == 0){return;}
    var pSbiling = aNode;
    var aTag = false;
    while(aNode){
        if(aNode.eleInput.checked){
            aTag = true;
            break;
        }
        aNode = aNode.nextSibling;
    }
    if(!aTag){
        pSbiling.parent.eleInput.checked = false;
        pSbiling.parent.isChecked = false;
        this.checkParentNodes(pSbiling.parent.parent.firstChild.index);
    }
}

//Check child nodes
SkyTree.prototype.checkChlidNodes = function(idx,bChk){
    var aNode = this.getNode(idx);
    if(aNode){
        if(aNode.displayed){aNode.eleInput.checked = bChk;}
        aNode.isChecked = bChk;
        aNode = aNode.firstChild
        while(aNode){
            pSibling = aNode;
            this.checkChlidNodes(pSibling.index,bChk);
            aNode = aNode.nextSibling;
        }
    }
}

//Fix icons
SkyTree.prototype.fixIcons = function(aParent,icoIdx,fLine){
    var aNode,pSibling;
    if(!aParent.displayed){return;}
    if(typeof(fLine)=='undefined'){fLine = (aParent.nextSibling!=null);}
    if(fLine){
        if((aParent.depth-1 == icoIdx) && (aParent.firstChild || aParent.expandTag)){
            aParent.eleIconGrp.childNodes[icoIdx].src = this.getIcon(aParent.expanded?'TI_TMINUS':'TI_TPLUS');
        }else if(aParent.depth-1 == icoIdx){
            aParent.eleIconGrp.childNodes[icoIdx].src = this.getIcon('TI_T');
        }else{
            aParent.eleIconGrp.childNodes[icoIdx].src = this.getIcon('TI_I');
        }
    }else{
        if((aParent.depth-1 == icoIdx) && (aParent.firstChild || aParent.expandTag)){
            aParent.eleIconGrp.childNodes[icoIdx].src = this.getIcon(aParent.expanded?'TI_LMINUS':'TI_LPLUS');
        }else if(aParent.depth-1 == icoIdx){
            aParent.eleIconGrp.childNodes[icoIdx].src = this.getIcon('TI_L');
        }else{
            aParent.eleIconGrp.childNodes[icoIdx].src = this.getIcon('TI_BLANK');
        }
    }
    aNode = aParent.firstChild;
    while(aNode){
        pSibling = aNode;
        this.fixIcons(pSibling,icoIdx,fLine);
        aNode = aNode.nextSibling;
    }
};

//Expand sub-div
SkyTree.prototype.expandNode = function(idx,fExp,sMode){
    var aNode = this.getNode(idx);
    var aChild;
    //debugger;
    if(!aNode || !aNode.displayed){return;}
    if(typeof(fExp)=='undefined'){
        fExp = !aNode.expanded;
    }
    if(typeof(sMode)=='undefined'){
        sMode = this.slideMode;
    }
    if(fExp == aNode.expanded){return;}
    if(!fExp){ //hide action
        //dynamic hiding
        if(sMode == ST_SLIDE_MODE_DYNAMIC){
            aNode.eleSub.style.overflow = 'hidden';
            this.scrollTimer = window.setTimeout(this.instanceName +'.hideStep(' + aNode.eleSub.scrollHeight + ',' + aNode.index + ')', 5);
        }else{
            aNode.eleSub.style.overflow = '';
            aNode.eleSub.style.display = 'none';
        }

        aNode.expanded = false;
        aNode.eleBtn.src = this.getIcon('TI_' + (aNode.nextSibling?'TPLUS':'LPLUS'));
        aNode.eleIcon.src = (aNode.iconCol=='')?this.defIconCol:aNode.iconCol;
    }else{ //show action
        //check if need server request
        if(!aNode.firstChild && aNode.expandTag){
            if (this.requestURL != '') {
                var aIcon = this.createIcon('I_LOADING');
                var aSpan = document.createElement('span');
                aSpan.className = 'LoadTip';
                aSpan.appendChild(aIcon);
                aSpan.innerHTML += '&nbsp;' + this.getStr('Loading');
                aNode.eleSub.innerHTML = '';
                aNode.eleSub.appendChild(aSpan);
                aSpan.style.marginLeft = getAbsoluteLocationEx(aNode.eleContent).absoluteLeft + 'px';
                this.serverGetData(aNode.index,aNode.value);
            } else {
                if(typeof(this.beforeExpandNode) == 'function'){this.beforeExpandNode(this, aNode);}
            }
        }
        //show hild nodes that not shown
        var aChild = aNode.firstChild;
        while(aChild){
            aChild.show();
            aChild = aChild.nextSibling;
        }
        //hide other siblings if needed
        if(this.singleExpand){
            aChild = aNode.parent.firstChild;
            while(aChild){
                if(aChild.expanded){
                    this.nodeFullExpCol(aChild.index,false);
                }
                aChild = aChild.nextSibling;
            }
        }
        //dynamically showing
        if(sMode == ST_SLIDE_MODE_DYNAMIC){
            //aNode.eleSub.style.width = '100%';
            aNode.eleSub.style.overflow = 'hidden';
            aNode.eleSub.style.display = '';
            aNode.eleSub.style.height = '1px';
            this.scrollTimer = window.setTimeout(this.instanceName +'.showStep(1,' + aNode.index + ')', 5);
        }else{
            aNode.eleSub.style.height = '';
            aNode.eleSub.style.display = '';
        }

        aNode.expanded = true;
        aNode.eleBtn.src = this.getIcon('TI_' + (aNode.nextSibling?'TMINUS':'LMINUS'));
        aNode.eleIcon.src = (aNode.iconExp=='')?this.defIconExp:aNode.iconExp;
    }
};

//Action showing step
SkyTree.prototype.showStep = function(scroll, idx){
    var node = this.getNode(idx).eleSub;
    var height = node.scrollHeight;
    var step = (height - scroll)/ST_SCROLL_DELAY;
    if (step < 1 ) step = 1;
    scroll = scroll + step;
    if (scroll <= height)
    {
        node.style.height = scroll + 'px';
        this.scrollTimer = window.setTimeout(this.instanceName +'.showStep(' + scroll + ',' + idx + ')', 5);
    }else{
        node.style.height = '';
        node.style.overflow = '';
    }
};

//Action hiding step
SkyTree.prototype.hideStep = function (scroll, idx){
    var node = this.getNode(idx).eleSub;
    var height = node.scrollHeight;
    var step = (height - Math.abs(scroll - height))/ST_SCROLL_DELAY;
    if (step < 1 ) step = 1;
    scroll = scroll - step;
    if (scroll > 2 )
    {
        node.style.height = scroll + 'px';
        this.scrollTimer = window.setTimeout(this.instanceName +'.hideStep(' + scroll + ',' + idx + ')',5);
    }else{
        node.style.overflow = '';
        node.style.display = 'none';
    }
};

//Full expand/collapse a node.
SkyTree.prototype.nodeFullExpCol = function(idx,fExp){
    var aParent = this.getNode(idx);
    if(!aParent || (aParent.index == this.rootNode.index) || !aParent.firstChild){return;}
    if(typeof(fExp)=='undefined'){
        fExp = !aParent.expanded;
    }
    this.expandNode(aParent.index,fExp,ST_SLIDE_MODE_STATIC);
    var aNode = aParent.firstChild;
    while(aNode){
        pSibling = aNode;
        this.nodeFullExpCol(pSibling.index,fExp);
        aNode = aNode.nextSibling;
    }
};

//Full expand/collapse all tree nodes.
SkyTree.prototype.treeFullExpCol = function(fExp){
    var aNode = this.rootNode.firstChild;
    var pSibling;
    while(aNode){
        pSibling = aNode;
        this.nodeFullExpCol(pSibling.index,fExp);
        aNode = aNode.nextSibling;
    }
    this.hideInfo();
};

//Full expand
SkyTree.prototype.fullExpand = function(){
    this.showInfo(this.getStr('FullExpanding'),'I_LOADING');
    setTimeout(this.instanceName + '.treeFullExpCol(true)',100);
};

//Full collapse
SkyTree.prototype.fullCollapse = function(){
    this.showInfo(this.getStr('FullCollapsing'),'I_LOADING');
    setTimeout(this.instanceName + '.treeFullExpCol(false)',100);
};

//Focus node
SkyTree.prototype.focusNode = function(idx){
    if(!this.allowFocus){return;}
    var aNode = this.getNode(idx);
    if(!aNode){return;}
    if(this.instanceName != g_focusInstance){
        //this.showInfo('Use hoykeys to control.','I_INFO',1000,idx);
    }
    if(this.curNode){
        if(aNode.index == this.curNode.index){ //begin editing display text
            // this.curNode.editText();
            //这里被我注释掉了return
            //return;
        }else{
            this.eleText.style.display = 'none';
        }
        this.curNode.eleContent.className = this.curNode.classNormal;
    }
    //aNode.eleContent.focus();
    aNode.eleContent.className = aNode.classFocus;
    this.curNode = aNode;
    g_focusInstance = this.instanceName;
    g_focused = true;
    //auto execute method if needed
    if(this.focusExecute){
        aNode.execute();
    }
    if(typeof(aNode.onfocus) == 'function'){aNode.onfocus(aNode);}
};

//Expand to a specified node
SkyTree.prototype.expandToNode = function(idx){
    //get expand path
    var arrPath = new Array();
    var i = 0;
    var aNode = this.getNode(idx);
    if(!aNode){
        this.showInfo(this.getStr('NodeInvalid'),'I_WARNING',1000);
        return;
    }
    while(aNode){
        if(aNode.index != 0){
            arrPath[i++] = aNode.index;
        }
        aNode = aNode.parent
    }
    //expand all node on path one by one
    for(i=arrPath.length-1;i>0;i--){
        this.expandNode(arrPath[i],true,ST_SLIDE_MODE_STATIC);
    }
    //focus the node
	//这里被我注释掉了
    //this.focusNode(idx);
    //我增加了改变颜色
    if(this.curNode){
        this.curNode.eleContent.className = this.curNode.classNormal;
    }
    aNode = this.getNode(idx);
    aNode.eleContent.className = aNode.classFocus;
    this.curNode = aNode;
};

//Collapse current node or focus back parent node
SkyTree.prototype.upperNode = function(){
    if(!this.curNode){return;}
    if(this.curNode.firstChild && this.curNode.expanded){
        this.expandNode(this.curNode.index,false);
    }else if(this.curNode.parent.index != 0){
        this.focusNode(this.curNode.parent.index);
    }
};

//Expand current node or focus to the first child node
SkyTree.prototype.lowerNode = function(){
    if(!this.curNode){this.curNode = this.rootNode.firstChild;}
    if(!this.curNode){return;}//else{this.focusNode(this.curNode.index);}
    if(this.curNode.firstChild || this.curNode.expandTag){
        if(!this.curNode.expanded){
            this.expandNode(this.curNode.index,true);
        }else{
            this.focusNode(this.curNode.firstChild.index);
        }
    }
};

//Focus to previous node
SkyTree.prototype.pervNode = function(){
    var aNode,pSibling;
    if(!this.curNode){return;}
    if(this.curNode.previousSibling){
        //find previous availabe node
        pSibling = aNode = this.curNode.previousSibling;
        while(aNode.firstChild && aNode.expanded){
            aNode = aNode.firstChild;
            while(aNode){ //find last child
                pSibling = aNode;
                aNode = aNode.nextSibling;
            }
            aNode = pSibling;
        }
        this.focusNode(pSibling.index);
    }else if(this.curNode.parent.index != 0){
        this.focusNode(this.curNode.parent.index);
    }
    this.curNode.eleNode.scrollIntoView(true);
}

//Focus to next node
SkyTree.prototype.nextNode = function(){
    var aNode,pSibling;
    if(!this.curNode){return;}
    if(this.curNode.firstChild && this.curNode.expanded){
        this.focusNode(this.curNode.firstChild.index);
    }else if(this.curNode.nextSibling){
        this.focusNode(this.curNode.nextSibling.index);
    }else{
        //find next availabe node(until next sibling occurs)
        aNode = this.curNode;
        while(aNode.parent && aNode.parent.index != 0){
            pSibling = aNode;
            aNode = aNode.parent;
            if(aNode.nextSibling){
                this.focusNode(aNode.nextSibling.index);
                break;
            }
        }
    }
    this.curNode.eleNode.scrollIntoView(false);
}

//Drop node in new place
SkyTree.prototype.placeNode = function(srcIdx,pIdx,oIdx){
    var srcNode = this.getNode(srcIdx);
    var pNode = this.getNode(pIdx);
    if(!srcNode || !pNode){return;}
    if(this.requestURL != ''){
        this.serverPlaceNode(srcIdx,pIdx,oIdx);
    }else{
        this.clientPlaceNode(srcIdx,pIdx,oIdx);
    }
}

//Drop node in new place on client
SkyTree.prototype.clientPlaceNode = function(srcIdx,pIdx,oIdx){
    var srcNode = this.getNode(srcIdx);
    var pNode = this.getNode(pIdx);
    var i = 0;
    this.eventCanceled = false;
    //var tgt = e.srcElement || e.target;
    if(!srcNode || !pNode
    //|| (tgt.className != 'TCnt' && tgt.className != 'TCntFocus')
    ){return;}
    //check if new place is valid for source node
    i = pIdx;
    var tmpNode = pNode;
    while(i){
        if(tmpNode){
            i = tmpNode.index;
            if(i == srcIdx){
                this.showInfo(this.getStr('UndropablePath'),'I_WARNING',1000);
                return;
            }
        }
        tmpNode = tmpNode.parent;
    }

    //event handler
    if(typeof(this.beforedrop) == 'function'){this.beforedrop(this,srcNode,pNode,oIdx);}
    if(this.eventCanceled){return;}
    //----Reset relationship----
    var pSibling,nSibling;
    if(typeof(oIdx)=='undefined'){
        //find last order index to do
        oIdx = 0;
    }

    if(oIdx==0){
        pSibling = null;
        nSibling = pNode.firstChild;
    }else{
        pSibling = pNode.firstChild;
        nSibling = pSibling?pSibling.nextSibling:null;
        i = 1;
        while(i++<oIdx && nSibling){ //find position
            if(nSibling){
                pSibling = nSibling;
                nSibling = nSibling.nextSibling;
            }
        }
    }
    //exit if target is original position
    if((srcNode.previousSibling == pSibling && srcNode == nSibling)
    || (srcNode.nextSibling == nSibling && srcNode == pSibling)){
        return;
    }
    //break original relationship
    if(srcNode.previousSibling){
        srcNode.previousSibling.nextSibling = srcNode.nextSibling;
        if(!srcNode.nextSibling){
            this.fixIcons(srcNode.previousSibling,srcNode.depth-1,false);
        }
    }else{
        srcNode.parent.firstChild = srcNode.nextSibling;
    }
    if(srcNode.nextSibling){
        srcNode.nextSibling.previousSibling = srcNode.previousSibling;
    }
    if(srcNode.parent.index != 0){
        srcNode.parent.resetIcons();
    }

    //set new relationship
    srcNode.parent = pNode;
    srcNode.previousSibling = pSibling;
    srcNode.nextSibling = nSibling;
    if(pSibling){
        srcNode.previousSibling.nextSibling = srcNode;
    }else{
        srcNode.parent.firstChild = srcNode;
    }
    if(nSibling){
        srcNode.nextSibling.previousSibling = srcNode;
    }

    var aIdx = srcNode.index;
    this.resetDepth(aIdx);
    this.expandNode(pNode.index,true,ST_SLIDE_MODE_STATIC);
    //remove original node from DOM
    srcNode.eleNode.parentNode.removeChild(srcNode.eleNode);
    //put node in new place
    if(srcNode.nextSibling){
        pNode.eleSub.insertBefore(srcNode.eleNode,srcNode.nextSibling.eleNode);
    }else{
        pNode.eleSub.appendChild(srcNode.eleNode);
    }
    srcNode.resetIcons();
    //fix icons
    if(!srcNode.nextSibling && !srcNode.previousSibling){
        if(srcNode.parent.index != 0){
            srcNode.parent.resetIcons();
        }
    }else if(srcNode.previousSibling){
        srcNode.previousSibling.resetIcons();
        this.fixIcons(srcNode.previousSibling,srcNode.previousSibling.depth-1,true);
    }
    srcNode.removeTag();
    if(typeof(this.afterdrop) == 'function'){this.afterdrop(this,srcNode);}
    this.expandToNode(srcIdx);
}

//Reset node's and it's childs' depth
SkyTree.prototype.resetDepth = function(idx){
    var pNode = this.getNode(idx);
    var aIdx;
    if(!pNode || idx == 0){return;}
    pNode.depth = pNode.parent.depth + 1;
    pNode.resetIcons();
    var aNode = pNode.firstChild;
    while(aNode){
        aIdx = aNode.index;
        this.resetDepth(aIdx);
        aNode = aNode.nextSibling;
    }
}

//Get checked value,return a values string splits with ','
SkyTree.prototype.getCheckedValue = function(){
    var arrValue = new Array();
    var j = 0;
    if(this.checkName == ''){return null;}
    for(var i in this.ds){
        if(this.ds[i].isChecked){
            arrValue[j++] = this.ds[i].value;
        }
    }
    return arrValue.join(',');
}

//Get JSON data
SkyTree.prototype.getJSON = function(idx,compressed){
    var strJSON = '';
    var strSub = '';
    var pNode = this.getNode(idx);
    var aIdx;
    var itemTab,propTab,returnTag;
    if(!pNode){return strJSON;}
    else{
        var aNode = pNode.firstChild;
        if(!aNode){return strJSON;}
        if(typeof(compressed) == 'undefined'){compressed = true;}
        itemTab = compressed?'':repeatString('\t',aNode.depth);
        propTab = compressed?'':(itemTab + '\t');
        returnTag = compressed?'':'\n';
        while(aNode){
            strSub = this.getJSON(aNode.index,compressed);
            if(strJSON!=''){
                strJSON += compressed?',':('\n' + itemTab + ',\n');
            }
            strJSON += compressed?'{':(itemTab + '{\n');
            //Set properties for each node JSON data
            with(aNode){
                strJSON += propTab + 'text:\'' + aNode.text.replace(/\'/g,'\\\'') + '\',' + returnTag;
                if(aNode.hint != ''){strJSON += propTab + 'hint:\'' + aNode.hint.replace(/\'/g,'\\\'') + '\',' + returnTag;}
                if(aNode.method != this.defMethod){strJSON += propTab + 'method:\'' + aNode.method.replace(/\'/g,'\\\'') + '\',' + returnTag;}
                if(aNode.classNormal != ST_DEFAULT_NORMAL_CLASS){strJSON += propTab + 'clsN:\'' + aNode.classNormal + '\',' + returnTag;}
                if(aNode.classFocus != ST_DEFAULT_FOCUS_CLASS){strJSON += propTab + 'clsF:\'' + aNode.classFocus + '\',' + returnTag;}
                if(aNode.iconExp != this.defIconExp && aNode.iconExp != ''){strJSON += propTab + 'iExp:\'' + aNode.iconExp + '\',' + returnTag;}
                if(aNode.iconCol != this.defIconCol && aNode.iconCol != ''){strJSON += propTab + 'iCol:\'' + aNode.iconCol + '\',' + returnTag;}
                if(aNode.iconLeaf != this.defIconLeaf && aNode.iconLeaf != ''){strJSON += propTab + 'iLeaf:\'' + aNode.iconLeaf + '\',' + returnTag;}
                if(!aNode.allowDrag){strJSON += propTab + 'drag:flase,' + returnTag;}
                if(!aNode.allowDrop){strJSON += propTab + 'drop:flase,' + returnTag;}
                if(strSub != ''){strJSON += propTab + strSub + ',' + returnTag;}
                strJSON += propTab + 'value:\'' + aNode.value.replace(/\'/g,'\\\'') + '\'' + returnTag;
                strJSON += itemTab + '}';
            }
            aNode = aNode.nextSibling;
        }
        strJSON = compressed?('items:[' + strJSON + ']'):('items:[\n' + strJSON + ']');
    }
    return strJSON;
}

/*------------ JSON Part ------------*/
//Get node data from server
SkyTree.prototype.serverGetData = function(rootId,rootValue){
    this.coreRequest(this.requestURL
    + '?action=getdata&iname=' + this.instanceName
    + '&rootid=' + rootId
    + '&rootvalue=' + escape(rootValue)
    + '&level=' + this.expandLevel
    + (this.loaded?'&intialized=true':''));
};

//Edit display text on server
SkyTree.prototype.serverSetText = function(idx,aText){
    var aNode = this.getNode(idx);
    if(!aNode){return;}
    aNode.setTag('I_LOADING',this.getStr('SettingText'));
    this.coreRequest(this.requestURL
    + '?action=edit&iname=' + this.instanceName
    + '&idx=' + idx
    + '&key=' + escape(aNode.value)
    + '&text=' + escape(aText));
};

//Delete node on server
SkyTree.prototype.serverDelete = function(idx){
    var aNode = this.getNode(idx);
    if(!aNode){return;}
    aNode.setTag('I_LOADING',this.getStr('Deleting'));
    this.coreRequest(this.requestURL
    + '?action=delete&iname=' + this.instanceName
    + '&idx=' + idx
    + '&key=' + escape(aNode.value));
};

//Add node on server
SkyTree.prototype.serverAddNode = function(idx){
    var aNode = this.getNode(idx);
    if(!aNode){return;}
    this.showInfo(this.getStr('Adding'),'I_LOADING');
    var key = aNode.value;
    if(!key){key = '';}
    this.coreRequest(this.requestURL
    + '?action=add&iname=' + this.instanceName
    + '&idx=' + idx
    + '&key=' + escape(key));
};

//Place node on server
SkyTree.prototype.serverPlaceNode = function(srcIdx,pIdx,oIdx){
    var srcNode = this.getNode(srcIdx);
    var pNode = this.getNode(pIdx);
    if(!srcNode || !pNode){return;}
    srcNode.setTag('I_LOADING',this.getStr('Moving'));
    //check if new place is valid for source node
    i = pIdx;
    var tmpNode = pNode;
    while(i){
        if(tmpNode){
            i = tmpNode.index;
            if(i == srcIdx){
                this.showInfo(this.getStr('UndropablePath'),'I_WARNING',1000);
                return;
            }
        }
        tmpNode = tmpNode.parent;
    }
    this.coreRequest(this.requestURL
    + '?action=move&iname=' + this.instanceName
    + '&srcidx=' + srcIdx + '&srcvalue=' + escape(srcNode.value)
    + '&pidx=' + pIdx + '&pvalue=' + escape(pNode.value)
    + '&oidx=' + oIdx);
};

//Core code to request from server
SkyTree.prototype.coreRequest = function(src){
    if(this.eleIframe){
        this.eleIframe.parentNode.removeChild(this.eleIframe);
        this.eleIframe = null;
    }
    this.eleIframe = document.createElement('iframe');
    this.eleIframe.id = this.instanceName + 'Frame';
    this.eleIframe.width = 0;
    this.eleIframe.height = 0;
    this.eleContainer.appendChild(this.eleIframe);
    if(this.id != ''){src += '&id=' + this.id;}
    setTimeout(this.instanceName + '.eleIframe.src="' + src + '"',10);
};

/*------------ Class SkyTreeNode ------------*/
function SkyTreeNode(aTree,aParent){
    this.tree = aTree;
    this.index = this.tree.getNewId(); //index of the node
    this.parent = aParent; //parent node
    this.firstChild = null; //first child node
    this.previousSibling = null; //previous sibling node
    this.nextSibling = null; //next sibling node

    this.text = 'Node ' + this.index; //display text
    this.value = ''; //value for checkbox
    this.hint = ''; //hint when mouse over
    this.method = ''; //tag {$NODE} and {$TREE} can be replaced with the node or tree object
    this.data = null;
    this.depth = 0;
    this.displayed = false;
    this.expanded = false;
    this.isChecked = false;
    this.allowDrag = true;
    this.allowDrop = true;

    //evnet handler
    this.onfocus = function(sender){};

    this.iconExp = '';
    this.iconCol = '';
    this.iconLeaf = '';

    this.classNormal = ST_DEFAULT_NORMAL_CLASS;
    this.classFocus = ST_DEFAULT_FOCUS_CLASS;

    this.expandTag = false;

    this.eleIconGrp = null;
    this.eleBtn = null;
    this.eleIcon = null;
    this.eleInput = null;
    this.eleContent = null;
    this.eleTag = null;
    this.eleSub = null;

    this.setRelation(this.parent);
};

//Set the relationship of a node.
SkyTreeNode.prototype.setRelation = function(aParent){
    var aNode,pSibling;
    this.parent = aParent;
    if(aParent){
        this.depth = this.parent.depth + 1;
        aNode = aParent.firstChild;
        if(!aNode){
            aParent.firstChild = this;
            pSibling = null;
            //reset button to parent for expanding childs
            if(aParent.displayed){
                aParent.getPreIcon();
            }
        }else{
            while(aNode){
                pSibling = aNode;
                aNode = aNode.nextSibling;
            }
        }
        this.previousSibling = pSibling;
        if(pSibling){
            this.previousSibling.nextSibling = this;
        }
    }
};

//get previous icons
SkyTreeNode.prototype.getPreIcon = function(){
    var arrIcon = new Array();
    var i = 0;
    var aPar,bExpanded;
    var hasEvent = false;
    var iExp,iCol,iLeaf;
    //get the first special icon,maybe as the expanding button.
    bExpanded = ((this.expanded && this.firstChild) || this.depth<this.tree.expandLevel || this.tree.expandLevel == 0);
    this.expanded = bExpanded;
    if(this.firstChild || this.expandTag){
        if(this.nextSibling){
            arrIcon[i++] = bExpanded?'TMINUS':'TPLUS';
        }else{
            arrIcon[i++] = bExpanded?'LMINUS':'LPLUS';
        }
        iExp = (this.iconExp=='')?this.tree.defIconExp:this.iconExp;
        iCol = (this.iconCol=='')?this.tree.defIconCol:this.iconCol;
        if(this.eleIcon){
            this.eleIcon.src = (bExpanded?iExp:iCol);
            this.eleIcon.removeAttribute("width",0);
            this.eleIcon.removeAttribute("height",0);
        }else{
            this.eleIcon = this.tree.createIconBySrc(bExpanded?iExp:iCol);
        }
        hasEvent = true;
    }else{
        iLeaf = (this.iconLeaf=='')?this.tree.defIconLeaf:this.iconLeaf;
        if(this.nextSibling){
            arrIcon[i++] = 'T';
        }else{
            arrIcon[i++] = 'L';
        }
        if(this.eleIcon){
            this.eleIcon.src = iLeaf;
        }else{
            this.eleIcon = this.tree.createIconBySrc(iLeaf);
        }
    }
    this.eleIcon.className = 'IcoStatus';
    aPar = this;
    while((aPar = aPar.parent) && aPar.index != 0){
        if(!aPar.nextSibling){
            arrIcon[i++] = 'BLANK';
        }else{
            arrIcon[i++] = 'I';
        }
    }
    //create element
    var eleIcon;
    this.eleIconGrp.innerHTML = '';
    for(i=arrIcon.length -1;i>-1;i--){
        eleIcon = this.tree.createIcon('TI_' + arrIcon[i]);
        eleIcon.className = 'Ico';
        this.eleIconGrp.appendChild(eleIcon);
        if(i==0){
            this.eleBtn = eleIcon;
            if(hasEvent){ //set event for the button
                this.eleBtn.style.cursor = 'pointer';
                this.eleBtn.onclick = this.eleIcon.onclick = Function(this.tree.instanceName + '.expandNode(' + this.index + ');');
                this.eleSub.style.display = bExpanded?'':'none';
            }else{
                this.eleSub.style.display = 'none';
            }
        }
    }
};

//Show self on tree view
SkyTreeNode.prototype.show = function(){
    if(this.displayed){return;}
    var idx = this.index;
    var iName = this.tree.instanceName;
    this.eleNode = document.createElement('div'); //node element object
    this.eleNode.className = 'TNode';
    this.eleNode.onmousemove = function(e){
        eval(iName).getNode(idx).dragOver(e);
        return false;
    }
    this.eleNode.onmouseout = function(e){
        if(eval(iName).inDrag){
            with(eval(iName).getNode(idx).eleNode.style){
                borderBottom = '';
                borderTop = '';
            }
            eval(iName).getNode(idx).removeTag();
        }
    }
    this.eleIconGrp = document.createElement('span');
    this.eleContent = document.createElement('a');
    this.eleContent.href = 'javascript:void(0)';
    this.eleContent.className = this.classNormal;
    this.eleContent.innerHTML = this.text; //set display text
    if(this.hint!=''){
        this.eleContent.title = this.hint;
    }
    //this.eleContent.onclick = Function(this.tree.instanceName + '.focusNode(' + this.index + ')');
    this.eleContent.onmousedown = function(e){
        var o_X,o_Y;
        var oBody = document.compatMode=="BackCompat"?document.body: (document.documentElement || document.body);
        e = window.event || e;
        if(e.button == 1 || e.button == 0){
            if(!eval(iName).allowDragDrop){return;}
            eval(iName).getNode(idx).dragNode(e);
        }else if(e.button == 2){
            document.oncontextmenu = function(e){
                return false;
            }
            if(!eval(iName).allowContextMenu){return false;}
            if(!eval(iName).contextMenu){
                eval(iName).contextMenu = new ContextMenu(eval(iName));
                eval(iName).eleMain.appendChild(eval(iName).contextMenu.eleMenu);
                eval(iName).contextMenu.addDefItems();
            }
            document.onmouseup = function(e){
                e = window.event || e;
                if(e.button == 1 || e.button == 0){
                    eval(iName).contextMenu.hide();
                    document.onmouseup = null;
                    document.oncontextmenu = null;
                }
            }
            if(window.event){
                o_X = e.x + oBody.scrollLeft;
                o_Y = e.y + oBody.scrollTop;
            }else{
                o_X = e.pageX;
                o_Y = e.pageY;
            }
            //hide original menu if belongs to other tree object
            if(g_focusInstance != '' && g_focusInstance != iName){
                if(eval(g_focusInstance).contextMenu){
                    eval(g_focusInstance).contextMenu.hide();
                }
            }
            eval(iName).contextMenu.show(eval(iName).getNode(idx),o_X,o_Y);
        }
        return false;
    }
    this.eleContent.onclick = function(){
        eval(iName).focusNode(idx);
        //我添加了return false,防止Aborted /skchen@2008.01.07
        return false;
    }
    this.eleTag = document.createElement('span');
    this.eleTag.className = 'Tag';
    this.eleSub = document.createElement('div');
    this.getPreIcon(); //icon group
    this.eleNode.appendChild(this.eleIconGrp);
    if(this.tree.checkName != ''){ //check if insert checkbox
        if(navigator.appName.indexOf('Microsoft') != -1){ //fix IE name property problem
            this.eleInput = document.createElement('<input name="' + this.tree.checkName + '">');
        }else{
            this.eleInput = document.createElement('input');
            this.eleInput.setAttribute('name',this.tree.checkName);
        }
        this.eleInput.type = (this.isRadio?'radio':'checkbox');
        this.eleInput.className = 'Check';
        this.eleInput.value = this.value;
        if(!this.tree.isRadio){
            this.eleInput.onclick = Function(this.tree.instanceName + '.checkRelative(' + this.index + ',this.checked);');
        }
        this.eleNode.appendChild(this.eleInput);
    }
    this.eleNode.appendChild(this.eleIcon);
    this.eleNode.appendChild(this.eleContent);
    this.eleNode.appendChild(this.eleTag);
    this.eleNode.appendChild(this.eleSub);

    this.parent.eleSub.appendChild(this.eleNode);
    if(this.eleInput){
        this.eleInput.checked = this.isChecked;
    }

    if(this.previousSibling){
        this.tree.fixIcons(this.previousSibling,this.depth-1,true);
    }

    this.displayed = true;
}

//Execute method
SkyTreeNode.prototype.execute = function(){
    var eCode;
    if(this.method != ''){
        eCode = this.method;
        eCode = eCode.replace('{$NODE}',this.tree.instanceName + '.getNode(' + this.index + ')');
        eCode = eCode.replace('{$TREE}',this.tree.instanceName);
        try{
            eval(eCode);
        }catch(e){
            this.tree.showInfo(formatString(this.tree.getStr('ExecutingFailed'),e.message),'I_ERROR',2000);
        }
    }
};

//Edit display text;
SkyTreeNode.prototype.editText = function(){
    this.tree.eventCanceled = false;
    if(!this.displayed || this.tree.readOnly){return;}
    if(typeof(this.tree.beforeedit) == 'function'){this.tree.beforeedit(this.tree,this);}
    if(this.tree.eventCanceled){return;}
    //fix editor size and value
    var rectText;
    var l,t,w,h;
    rectText = getAbsoluteLocationEx(this.eleContent);

    l = rectText.absoluteLeft;
    t = rectText.absoluteTop;
    w = rectText.offsetWidth;

    with(this.tree.eleText){
        value = htmlDecoder(this.text);
        onblur = Function(this.tree.instanceName + '.setText(' + this.index + ')');
        style.left = l + 'px';
        style.top = t + 'px';
        style.width = Math.max(w,ST_EDIT_TEXT_MIN_WIDTH) + 'px';
        style.display = '';
        try{
            focus();
            select();
        }catch(e){}
    }
};

//Set display text
SkyTreeNode.prototype.setText = function(aText){
    this.text = aText;
    if(this.displayed){
        this.eleContent.innerHTML = htmlEncoder(aText);
        this.removeTag();
    }
    this.tree.eleText.style.display = 'none';
    if(typeof(this.tree.afteredit) == 'function'){this.tree.afteredit(this.tree,this);}
}

//Set hint text
SkyTreeNode.prototype.setHint = function(aHint){
    this.hint = aHint;
    if(this.displayed){this.eleContent.title = aHint;}
}

//Set value
SkyTreeNode.prototype.setValue = function(aValue){
    this.value = aValue;
    if(this.eleInput){
        this.eleInput.value = aValue;
    }
}

//Set icons
SkyTreeNode.prototype.setIcons = function(iLeaf,iExp,iCol){
    if(typeof(iExp)=='undefined'){iExp = iLeaf;}
    if(typeof(iCol)=='undefined'){iCol = iExp;}
    if(iLeaf !== false){this.iconLeaf = iLeaf;}
    if(iExp !== false){this.iconExp = iExp;}
    if(iCol !== false){this.iconCol = iCol;}
    if(this.displayed){this.getPreIcon();}
}

//Reset icon group
SkyTreeNode.prototype.resetIcons = function(){
    if(this.displayed){
        this.getPreIcon();
        this.eleNode.removeChild(this.eleContent.previousSibling);
        this.eleNode.insertBefore(this.eleIcon,this.eleContent);
    }
}

/*------------ Drag and Drop ------------*/
//check drag
SkyTreeNode.prototype.dragNode = function(e){
    if(!this.allowDrag){return;}
    e = window.event || e; var key = e.keyCode || e.which;
    e.cancelBubble = true;
    this.tree.preDrag = false;
    this.tree.curDragNode = this;
    return false;
}

//Event: on drag
SkyTreeNode.prototype.dragStart = function(e){
    e = window.event || e; var key = e.keyCode || e.which;
    if(e.button != 1 && e.button != 0){return;}
    //e.cancelBubble = true;
    //this.tree.eleMain.setCapture();
    var tgt = e.srcElement || e.target;
    var iName = this.tree.instanceName;
    var idx = this.index;
    with(this.tree){
        if(!inDrag && !preDrag){
            orgX = e.x?e.x:e.pageX;
            orgY = e.y?e.y:e.pageY;
            preDrag = true;
            document.onmousemove = function(e){
                eval(iName).getNode(idx).dragStart(e);
                return false;
            }
            return false;
        }else if(preDrag){
            curX = e.x?e.x:e.pageX;
            curY = e.y?e.y:e.pageY;
        }else{return false;}
        if ((Math.abs(curX-orgX)>15) || (Math.abs(curY-orgY)>15)){
            //showInfo(('Node ' + this.index + ' - Moving to x:' + curX + ';y:' + curY + ' - ' + tgt.tagName + ':' + tgt.className),'I_INFO',1000);
            if(!this.tree.cloneDiv){ //clone div
                var cNode = this.eleNode.cloneNode(true);
                //fix look
                var iCount = cNode.firstChild.childNodes.length;
                var oDiv = cNode.lastChild.getElementsByTagName('div');
                for(var i=0;i<oDiv.length;i++){
                    if(oDiv[i].className == 'TNode'){
                        for(var j=0;j<iCount;j++){
                            oDiv[i].firstChild.removeChild(oDiv[i].firstChild.firstChild);
                        }
                    }
                }
                cNode.removeChild(cNode.firstChild);
                this.tree.cloneDiv = document.createElement('div');
                this.tree.cloneDiv.className = this.tree.theme;
                this.tree.cloneDiv.appendChild(cNode);
                document.body.appendChild(this.tree.cloneDiv);
                //show cloned div
                with(this.tree.cloneDiv.style){
                    position='absolute';
                    zIndex='0';
                    filter="Alpha(Opacity='70',FinishOpacity='0',Style='1',StartX='0',StartY='0',FinishX='100',FinishY='100')";
                    try{
                        MozOpacity=0.7;
                        opacity=0.7;
                    }catch(ex){}
                }
            }
            //reset position
            if(this.tree.cloneDiv){
                var oBody=document.compatMode=="BackCompat"?document.body: (document.documentElement || document.body);
                var o_X,o_Y;
                if (window.event){
                    o_X=oBody.scrollLeft;
                    o_Y=oBody.scrollTop;
                }
                else{
                    o_X=e.pageX;
                    o_Y=e.pageY;
                }
                with(this.tree.cloneDiv.style){
                    if (window.event){
                        left = (curX + o_X + 5) + 'px';
                        top = (curY + o_Y + 5) + 'px';
                    }else{
                        left = (o_X + 5) + 'px';
                        top = (o_Y + 5) + 'px';
                    }
                }
            }
            //let lost focus before drag
            if(g_focusInstance!=''){
                g_focusInstance = '';
                g_focused=false;
                if(curNode){
                    curNode.eleContent.className = curNode.classNormal;
                    curNode = null;
                }
            }
        }else{
            return false;
        }
        inDrag = true;
    }
    return false;
}

//Event: drag over
SkyTreeNode.prototype.dragOver = function(e){
    e = window.event || e; var key = e.keyCode || e.which;
    e.cancelBubble = true;
    if(this.tree.inDrag){
        //determine target and order index;
        var rectNode,curY,bSibling;
        rectNode = getAbsoluteLocationEx(this.eleContent);
        curY = window.event?(e.y + Math.max(document.documentElement.scrollTop,document.body.scrollTop)):e.pageY;
        if(!this.allowDrop){
            this.setTag('I_WARNING',this.tree.getStr('Undropable'));
        }else if(rectNode.absoluteTop + rectNode.offsetHeight - curY < 5){
            //at bottom
            this.eleNode.style.borderTop = '';
            this.eleNode.style.borderBottom = '1px black dotted';
            this.setTag('TAG_DOWN',this.tree.getStr('DropBelow'));
            this.tree.curOrder = this.getOrderIndex() + 1;
        }else if(curY - rectNode.absoluteTop < 5){
            //at top
            this.eleNode.style.borderBottom = '';
            this.eleNode.style.borderTop = '1px black dotted';
            this.setTag('TAG_UP',this.tree.getStr('DropAbove'));
            this.tree.curOrder = this.getOrderIndex();
        }else{
            //at body
            this.eleNode.style.borderBottom = '';
            this.eleNode.style.borderTop = '';
            this.setTag('TAG_SUB',this.tree.getStr('DropAsChild'));
            //  		this.showInfo
            this.tree.curOrder = -1;
        }

        this.tree.curTgt = this;
        /*this.tree.showInfo('It\'s node ' + this.index
        + ' top:' + (curY - rectNode.absoluteTop)
        + ' bottom:' + (rectNode.absoluteTop + rectNode.offsetHeight - curY)
        ,'I_INFO',10000);*/
        if(document.createStyleSheet){
            g_cursorSheet.cssText = this.allowDrop?'*{cursor:move!important}':'*{cursor:no-drop!important}';
        }
        if((this.firstChild || this.expandTag) && !this.expanded){
            setTimeout(this.tree.instanceName + '.getNode(' + this.index + ').autoExpand()',ST_AUTO_EXPAND_DELAY);
        }
        document.onmousemove(e);
        //return false;
    }
    if(this.tree.curDragNode){
        this.tree.curDragNode.dragStart(e);
    }
    //this.tree.eleMain.onmousemove();
    return false;
}

//auto expand
SkyTreeNode.prototype.autoExpand = function(){
    if(this.tree.inDrag && (this.tree.curTgt.index == this.index)){
        this.tree.expandNode(this.index,true,this.tree.slideMode);
    }
}

//get order index
SkyTreeNode.prototype.getOrderIndex = function(){
    var aNode,aIdx;
    aIdx = -1;
    aNode = this;
    while(aNode){
        aIdx++;
        aNode = aNode.previousSibling;
    }
    return aIdx;
}

//set tag icon
SkyTreeNode.prototype.setTag = function(ico,alt,timeout){
    var aTag = this.tree.createIcon(ico,alt);
    this.eleTag.innerHTML = '';
    this.eleTag.appendChild(aTag);
    if(alt){
        this.eleTag.innerHTML += '&nbsp;' + alt;
    }
    if(typeof(timeout)!='undefined'){
        setTimeout(this.tree.instanceName + '.getNode(' + this.index + ').removeTag()',timeout);
    }
}

//remove tag icon
SkyTreeNode.prototype.removeTag = function(){
    this.eleTag.innerHTML = '';
}

/*------------ Class Context Menu ------------*/
function ContextMenu(aParent){
    this.parent = aParent;
    this.node = null;
    this.customized = false;
    this.cancelPopup = false;
    this.countItems = 0;
    this.eleMenu = document.createElement('div');
    this.eleMenu.className = 'ContextMenu';
    this.eleMenu.style.display = 'none';
}

ContextMenu.prototype.onpopup = function(aNode){}

ContextMenu.prototype.addItemCore = function(text,ico,act){
    var eleItem = document.createElement('div');
    var eleIcon = this.parent.createIcon(ico);
    var eleText = document.createElement('span');
    var iName = this.parent.instanceName;
    eleItem.className = 'ContextMenuItem';
    eleItem.onmousemove = function(){
        this.className="ContextMenuItemOver";
    }
    eleItem.onmouseout = function(){
        this.className="ContextMenuItem";
    }
    eleText.innerHTML = text;
    eleItem.appendChild(eleIcon);
    eleItem.appendChild(eleText);
    eleItem.innerHTML += '&nbsp;';
    if(typeof act == 'function'){
        eleItem.onclick = function(){
            act(eval(iName + '.curNode'));
        }
    }

    this.eleMenu.appendChild(eleItem);
    this.countItems++;
    return eleItem;
}

ContextMenu.prototype.clear = function(){
    this.eleMenu.innerHTML = '';
    this.countItems = 0;
}

ContextMenu.prototype.addDefItems = function(){
    //add items
    this.actAddSibling = this.addItem('AddSibling','CM_NEWSIB','AS');
    this.actAddChild = this.addItem('AddChild','CM_NEWCHD','AC');
    this.actEdit = this.addItem('Edit','CM_EDIT','ED');
    this.actDelete = this.addItem('Delete','CM_DEL','DL');
    this.sepD = this.addSeparator();
    this.actExecute = this.addItem('Execute','CM_EXEC','EC');
    this.sepE = this.addSeparator();
    this.actExpand = this.addItem('Expand','CM_EXP','EX');
    this.actCollapse = this.addItem('Collapse','CM_COL','CO');
    this.actFullExpCol = this.addItem('FullExpCol','CM_FEC','FEC');
    this.sepA = this.addSeparator();
    this.actAbout = this.addItem('AboutButton','TI_ABOUT','AB');
}

ContextMenu.prototype.addItem = function(text,ico,act){
    var eleItem = document.createElement('div');
    var eleIcon = this.parent.createIcon(ico);
    var eleText = document.createElement('span');
    var iName = this.parent.instanceName;
    eleItem.className = 'ContextMenuItem';
    eleItem.onmousemove = function(){
        this.className="ContextMenuItemOver";
    }
    eleItem.onmouseout = function(){
        this.className="ContextMenuItem";
    }
    eleText.innerHTML = this.parent.getStr(text);
    eleItem.appendChild(eleIcon);
    eleItem.appendChild(eleText);
    eleItem.innerHTML += '&nbsp;';
    switch(act){
        case 'AS':
        eleItem.onclick = Function(iName + '.addNode(' + iName + '.curNode.index,false,3)');
        eleItem.innerHTML += 'Ins';
        break;
        case 'AC':
        eleItem.onclick = Function(iName + '.addNode(' + iName + '.curNode.index,true,3)');
        eleItem.innerHTML += 'Shift+Ins';
        break;
        case 'ED':
        eleItem.onclick = Function(iName + '.curNode.editText()');
        eleItem.innerHTML += 'F2';
        break;
        case 'DL':
        eleItem.onclick = Function(iName + '.removeNode()');
        eleItem.innerHTML += 'Del';
        break;
        case 'EC':
        eleItem.onclick = Function(iName + '.curNode.execute()');
        eleItem.innerHTML += 'Enter';
        break;
        case 'EX':
        eleItem.onclick = Function(iName + '.expandNode(' + iName + '.curNode.index,true)');
        eleItem.innerHTML += '→';
        break;
        case 'CO':
        eleItem.onclick = Function(iName + '.expandNode(' + iName + '.curNode.index,false)');
        eleItem.innerHTML += '←';
        break;
        case 'FEC':
        eleItem.onclick = Function(iName + '.nodeFullExpCol(' + iName + '.curNode.index)');
        eleItem.innerHTML += '+';
        break;
        case 'AB':
        eleItem.onclick = Function(iName + '.showAbout()');
    }
    this.eleMenu.appendChild(eleItem);
    this.countItems++;
    return eleItem;
}

ContextMenu.prototype.addSeparator = function(){
    var eleSep = document.createElement('div');
    eleSep.className = 'ContextMenuSeparator';
    this.eleMenu.appendChild(eleSep);
    this.countItems++;
    return eleSep;
}

ContextMenu.prototype.show = function(aNode,x,y){
    if(this.customized){
        if(this.cancelPopup || this.count == 0)return;
        if(this.parent.allowFocus && (this.parent.curNode != aNode)){
            this.parent.focusNode(aNode.index);
        }
        if((typeof this.onpopup) == 'function')this.onpopup(aNode);
    }else{
        if(this.parent.readOnly && this.parent.defMethod == '' && aNode.method == '' && !aNode.firstChild && !aNode.expandTag){
            return;
        }
        if(this.parent.allowFocus && (this.parent.curNode != aNode)){
            this.parent.focusNode(aNode.index);
        }
        this.parent.curNode = aNode;
        this.fixAction(aNode);
    }

    this.eleMenu.style.left = x + 'px';
    this.eleMenu.style.top = y + 'px';
    this.eleMenu.style.display = '';
}

ContextMenu.prototype.hide = function(){
    this.eleMenu.style.display = 'none';
}

ContextMenu.prototype.fixAction = function(aNode){
    var showSep;
    if(this.parent.readOnly){
        this.actAddSibling.style.display = 'none';
        this.actAddChild.style.display = 'none';
        this.actEdit.style.display = 'none';
        this.actDelete.style.display = 'none';
        showSep = false;
    }else{
        this.actAddSibling.style.display = '';
        this.actAddChild.style.display = '';
        this.actEdit.style.display = '';
        this.actDelete.style.display = '';
        showSep = true;
    }

    if(this.parent.defMethod == '' && aNode.method == ''){
        this.actExecute.style.display = 'none';
        this.sepD.style.display = 'none';
        showSep = showSep || false;
    }else{
        this.actExecute.style.display = '';
        this.sepD.style.display = showSep?'':'none';
        showSep = true;
    }

    if(aNode.firstChild || aNode.expandTag){
        this.actExpand.style.display = aNode.expanded?'none':'';
        this.actCollapse.style.display = aNode.expanded?'':'none';
        this.actFullExpCol.style.display = '';
        this.sepE.style.display = showSep?'':'none';
        showSep = true;
    }else{
        this.actExpand.style.display = 'none';
        this.actCollapse.style.display = 'none';
        this.actFullExpCol.style.display = 'none';
        this.sepE.style.display = 'none';
        showSep = showSep || false;
    }

}

/*------------ Other Tools ------------*/
//Get absolute location.
function getAbsoluteLocationEx(element)
{
    if ( arguments.length != 1 || element == null )
    {
        return null;
    }
    var elmt = element;
    var offsetTop = elmt.offsetTop;
    var offsetLeft = elmt.offsetLeft;
    var offsetWidth = elmt.offsetWidth;
    var offsetHeight = elmt.offsetHeight;
    while( elmt = elmt.offsetParent )
    {
        // add this judge
        if ( elmt.style.position == 'absolute' || elmt.style.position == 'relative'
        || ( elmt.style.overflow != 'visible' && elmt.style.overflow != '' ) )
        {
            break;
        }
        offsetTop += elmt.offsetTop;
        offsetLeft += elmt.offsetLeft;
    }
    return { absoluteTop: offsetTop, absoluteLeft: offsetLeft,
    offsetWidth: offsetWidth, offsetHeight: offsetHeight };
};

//Format string: 'arrValue' replace '%s' tag in 'aString'
function formatString(aString,arrValue){
    var i;
    var newString;
    newString = aString;
    try{
        if(typeof(arrValue)!="object"){
            arrValue = new Array(arrValue);
        }
        for(i = 0;i<arrValue.length;i++){
            if(newString.indexOf("%s") > -1){
                newString = newString.replace(/\%s/,arrValue[i]);
            }
        }
        return newString;
    }
    catch(e){
        return "Erroneous array input!";
    }
};

//Import a script to document
function importScript(aPath){
    var scripts = document.getElementsByTagName('script');
    var found = false;
    var aScript;
    for(i in scripts){
        if(scripts[i].src == aPath){
            aScript = scripts[i];
            found = true;
            break;
        }
    }
    if(!found){
        aScript = document.createElement('script');
        aScript.language = "JavaScript"
        aScript.type = 'text/javascript';
        aScript.src = aPath;
        scripts[0].parentNode.insertBefore(aScript,scripts[0]);
    }
    return aScript;
}

//Open indecated URL in target
function openURL(aURL,aTgt){
    if(aURL==''){return;}
    if(typeof(aTgt)=='undefined'){aTgt='';}
    if(aTgt==''){
        window.open(aURL);
    }else{
        var t = document.getElementById(aTgt);
        if(!t){
            t = document.getElementsByName(aTgt);
            if(t){
                t = t[0];
            }
        }
        if(!t){
            window.open(aURL,aTgt);
        }else{
            if(t.tagName.toLowerCase() == 'iframe'){
                t.src = aURL;
            }else{
                t.location = aURL;
            }
        }
    }
};

//Repeat string
function repeatString(str,times){
    var newStr = '';
    if(typeof(times) == 'undefined'){times = 1;}
    for(var i=0;i<times;i++){
        newStr += str;
    }
    return newStr;
}

//htmlEncoder
function htmlEncoder(fString){
    fString = fString.replace(/>/g,'&gt;');
    fString = fString.replace(/</g,'&lt;');
    fString = fString.replace(/&/g,'&amp;');
    return fString;
};

//htmlDecoder
function htmlDecoder(fString){
    fString = fString.replace('&gt;','>');
    fString = fString.replace('&lt;','<');
    fString = fString.replace('&amp;','&');
    return fString;
};