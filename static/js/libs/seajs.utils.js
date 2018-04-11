//+++++++++++++++++++++++++++++++++++++++ Sea.js ++++++++++++++++++++++++++++++++++++++++++++++++
/**
 * Sea.js 2.1.1 | seajs.org/LICENSE.md
 */
(function(global, undefined) {

    // Avoid conflicting when `sea.js` is loaded multiple times
    if (global.seajs) {
        return
    }

    var seajs = global.seajs = {
        // The current version of Sea.js being used
        version : "2.1.1"
    }

    var data = seajs.data = {}

    /**
	 * util-lang.js - The minimal language enhancement
	 */

    function isType(type) {
        return function(obj) {
            return Object.prototype.toString.call(obj) === "[object " + type + "]"
        }
    }

    var isObject = isType("Object")
    var isString = isType("String")
    var isArray = Array.isArray || isType("Array")
    var isFunction = isType("Function")

    var _cid = 0
    function cid() {
        return _cid++
    }

    /**
	 * util-events.js - The minimal events support
	 */

    var events = data.events = {}

    // Bind event
    seajs.on = function(name, callback) {
        var list = events[name] || (events[name] = [])
        list.push(callback)
        return seajs
    }
    // Remove event. If `callback` is undefined, remove all callbacks for the
    // event. If `event` and `callback` are both undefined, remove all callbacks
    // for all events
    seajs.off = function(name, callback) {
        // Remove *all* events
        if (!(name || callback)) {
            events = data.events = {}
            return seajs
        }

        var list = events[name]
        if (list) {
            if (callback) {
                for (var i = list.length - 1; i >= 0; i--) {
                    if (list[i] === callback) {
                        list.splice(i, 1)
                    }
                }
            } else {
                delete events[name]
            }
        }

        return seajs
    }
    // Emit event, firing all bound callbacks. Callbacks receive the same
    // arguments as `emit` does, apart from the event name
    var emit = seajs.emit = function(name, data) {
        var list = events[name], fn

        if (list) {
            // Copy callback lists to prevent modification
            list = list.slice()

            // Execute event callbacks
            while (( fn = list.shift())) {
                fn(data)
            }
        }

        return seajs
    }
    /**
	 * util-path.js - The utilities for operating path such as id, uri
	 */

    var DIRNAME_RE = /[^?#]*\//

    var DOT_RE = /\/\.\//g
    var DOUBLE_DOT_RE = /\/[^/]+\/\.\.\//

    // Extract the directory portion of a path
    // dirname("a/b/c.js?t=123#xx/zz") ==> "a/b/"
    // ref: http://jsperf.com/regex-vs-split/2
    function dirname(path) {
        return path.match(DIRNAME_RE)[0]
    }

    // Canonicalize a path
    // realpath("http://test.com/a//./b/../c") ==> "http://test.com/a/c"
    function realpath(path) {
        // /a/b/./c/./d ==> /a/b/c/d
        path = path.replace(DOT_RE, "/")

        // a/b/c/../../d ==> a/b/../d ==> a/d
        while (path.match(DOUBLE_DOT_RE)) {
            path = path.replace(DOUBLE_DOT_RE, "/")
        }

        return path
    }

    // Normalize an id
    // normalize("path/to/a") ==> "path/to/a.js"
    // NOTICE: substring is faster than negative slice and RegExp
    function normalize(path) {
        var last = path.length - 1
        var lastC = path.charAt(last)

        // If the uri ends with `#`, just return it without '#'
        if (lastC === "#") {
            return path.substring(0, last)
        }

        return (path.substring(last - 2) === ".js" || path.indexOf("?") > 0 || path.substring(last - 3) === ".css" || lastC === "/") ? path : path + ".js"
    }

    var PATHS_RE = /^([^/:]+)(\/.+)$/
    var VARS_RE = /{([^{]+)}/g

    function parseAlias(id) {
        var alias = data.alias
        return alias && isString(alias[id]) ? alias[id] : id
    }

    function parsePaths(id) {
        var paths = data.paths
        var m

        if (paths && ( m = id.match(PATHS_RE)) && isString(paths[m[1]])) {
            id = paths[m[1]] + m[2]
        }

        return id
    }

    function parseVars(id) {
        var vars = data.vars

        if (vars && id.indexOf("{") > -1) {
            id = id.replace(VARS_RE, function(m, key) {
                return isString(vars[key]) ? vars[key] : m
            })
        }

        return id
    }

    function parseMap(uri) {
        var map = data.map
        var ret = uri

        if (map) {
            for (var i = 0, len = map.length; i < len; i++) {
                var rule = map[i]

                ret = isFunction(rule) ? (rule(uri) || uri) : uri.replace(rule[0], rule[1])

                // Only apply the first matched rule
                if (ret !== uri)
                    break
            }
        }

        return ret
    }

    var ABSOLUTE_RE = /^\/\/.|:\//
    var ROOT_DIR_RE = /^.*?\/\/.*?\//

    function addBase(id, refUri) {
        var ret
        var first = id.charAt(0)

        // Absolute
        if (ABSOLUTE_RE.test(id)) {
            ret = id
        }
        // Relative
        else if (first === ".") {
            ret = realpath(( refUri ? dirname(refUri) : data.cwd) + id)
        }
        // Root
        else if (first === "/") {
            var m = data.cwd.match(ROOT_DIR_RE)
            ret = m ? m[0] + id.substring(1) : id
        }
        // Top-level
        else {
            ret = data.base + id
        }

        return ret
    }

    function id2Uri(id, refUri) {
        if (!id)
            return ""

        id = parseAlias(id)
        id = parsePaths(id)
        id = parseVars(id)
        id = normalize(id)

        var uri = addBase(id, refUri)
        uri = parseMap(uri)

        return uri
    }

    var doc = document
    var loc = location
    var cwd = dirname(loc.href)
    var scripts = doc.getElementsByTagName("script")

    // Recommend to add `seajsnode` id for the `sea.js` script element
    var loaderScript = doc.getElementById("seajsnode") || scripts[scripts.length - 1]

    // When `sea.js` is inline, set loaderDir to current working directory
    var loaderDir = dirname(getScriptAbsoluteSrc(loaderScript) || cwd)

    function getScriptAbsoluteSrc(node) {
        return node.hasAttribute ? // non-IE6/7
        node.src :
        // see http://msdn.microsoft.com/en-us/library/ms536429(VS.85).aspx
        node.getAttribute("src", 4)
    }

    /**
	 * util-request.js - The utilities for requesting script and style files
	 * ref: tests/research/load-js-css/test.html
	 */

    var head = doc.getElementsByTagName("head")[0] || doc.documentElement
    var baseElement = head.getElementsByTagName("base")[0]

    var IS_CSS_RE = /\.css(?:\?|$)/i
    var READY_STATE_RE = /^(?:loaded|complete|undefined)$/

    var currentlyAddingScript
    var interactiveScript

    // `onload` event is not supported in WebKit < 535.23 and Firefox < 9.0
    // ref:
    // - https://bugs.webkit.org/show_activity.cgi?id=38995
    // - https://bugzilla.mozilla.org/show_bug.cgi?id=185236
    // -
	// https://developer.mozilla.org/en/HTML/Element/link#Stylesheet_load_events
    var isOldWebKit = (navigator.userAgent.replace(/.*AppleWebKit\/(\d+)\..*/, "$1")) * 1 < 536

    function request(url, callback, charset) {
        var isCSS = IS_CSS_RE.test(url)
        var node = doc.createElement( isCSS ? "link" : "script")

        if (charset) {
            var cs = isFunction(charset) ? charset(url) : charset
            if (cs) {
                node.charset = cs
            }
        }

        addOnload(node, callback, isCSS)

        if (isCSS) {
            node.rel = "stylesheet"
            node.href = url
        } else {
            node.async = true
            node.src = url
        }

        // For some cache cases in IE 6-8, the script executes IMMEDIATELY after
        // the end of the insert execution, so use `currentlyAddingScript` to
        // hold current node, for deriving url in `define` call
        currentlyAddingScript = node

        // ref: #185 & http://dev.jquery.com/ticket/2709
        baseElement ? head.insertBefore(node, baseElement) : head.appendChild(node)

        currentlyAddingScript = null
    }

    function addOnload(node, callback, isCSS) {
        var missingOnload = isCSS && (isOldWebKit || !("onload" in node))

        // for Old WebKit and Old Firefox
        if (missingOnload) {
            setTimeout(function() {
                pollCss(node, callback)
            }, 1)// Begin after node insertion
            return
        }

        node.onload = node.onerror = node.onreadystatechange = function() {
            if (READY_STATE_RE.test(node.readyState)) {

                // Ensure only run once and handle memory leak in IE
                node.onload = node.onerror = node.onreadystatechange = null

                // Remove the script to reduce memory leak
                if (!isCSS && !data.debug) {
                    head.removeChild(node)
                }

                // Dereference the node
                node = null

                callback()
            }
        }
    }

    function pollCss(node, callback) {
        var sheet = node.sheet
        var isLoaded

        // for WebKit < 536
        if (isOldWebKit) {
            if (sheet) {
                isLoaded = true
            }
        }
        // for Firefox < 9.0
        else if (sheet) {
            try {
                if (sheet.cssRules) {
                    isLoaded = true
                }
            } catch (ex) {
                // The value of `ex.name` is changed from
				// "NS_ERROR_DOM_SECURITY_ERR"
                // to "SecurityError" since Firefox 13.0. But Firefox is less
				// than 9.0
                // in here, So it is ok to just rely on
				// "NS_ERROR_DOM_SECURITY_ERR"
                if (ex.name === "NS_ERROR_DOM_SECURITY_ERR") {
                    isLoaded = true
                }
            }
        }

        setTimeout(function() {
            if (isLoaded) {
                // Place callback here to give time for style rendering
                callback()
            } else {
                pollCss(node, callback)
            }
        }, 20)
    }

    function getCurrentScript() {
        if (currentlyAddingScript) {
            return currentlyAddingScript
        }

        // For IE6-9 browsers, the script onload event may not fire right
        // after the script is evaluated. Kris Zyp found that it
        // could query the script nodes and the one that is in "interactive"
        // mode indicates the current script
        // ref: http://goo.gl/JHfFW
        if (interactiveScript && interactiveScript.readyState === "interactive") {
            return interactiveScript
        }

        var scripts = head.getElementsByTagName("script")

        for (var i = scripts.length - 1; i >= 0; i--) {
            var script = scripts[i]
            if (script.readyState === "interactive") {
                interactiveScript = script
                return interactiveScript
            }
        }
    }

    /**
	 * util-deps.js - The parser for dependencies ref:
	 * tests/research/parse-dependencies/test.html
	 */

    var REQUIRE_RE = /"(?:\\"|[^"])*"|'(?:\\'|[^'])*'|\/\*[\S\s]*?\*\/|\/(?:\\\/|[^\/\r\n])+\/(?=[^\/])|\/\/.*|\.\s*require|(?:^|[^$])\brequire\s*\(\s*(["'])(.+?)\1\s*\)/g
    var SLASH_RE = /\\\\/g

    function parseDependencies(code) {
        var ret = []

        code.replace(SLASH_RE, "").replace(REQUIRE_RE, function(m, m1, m2) {
            if (m2) {
                ret.push(m2)
            }
        })

        return ret
    }

    /**
	 * module.js - The core of module loader
	 */

    var cachedMods = seajs.cache = {}
    var anonymousMeta

    var fetchingList = {}
    var fetchedList = {}
    var callbackList = {}

    var STATUS = Module.STATUS = {
        // 1 - The `module.uri` is being fetched
        FETCHING : 1,
        // 2 - The meta data has been saved to cachedMods
        SAVED : 2,
        // 3 - The `module.dependencies` are being loaded
        LOADING : 3,
        // 4 - The module are ready to execute
        LOADED : 4,
        // 5 - The module is being executed
        EXECUTING : 5,
        // 6 - The `module.exports` is available
        EXECUTED : 6
    }

    function Module(uri, deps) {
        this.uri = uri
        this.dependencies = deps || []
        this.exports = null
        this.status = 0

        // Who depends on me
        this._waitings = {}

        // The number of unloaded dependencies
        this._remain = 0
    }

    // Resolve module.dependencies
    Module.prototype.resolve = function() {
        var mod = this
        var ids = mod.dependencies
        var uris = []

        for (var i = 0, len = ids.length; i < len; i++) {
            uris[i] = Module.resolve(ids[i], mod.uri)
        }
        return uris
    }
    // Load module.dependencies and fire onload when all done
    Module.prototype.load = function() {
        var mod = this

        // If the module is being loaded, just wait it onload call
        if (mod.status >= STATUS.LOADING) {
            return
        }

        mod.status = STATUS.LOADING

        // Emit `load` event for plugins such as combo plugin
        var uris = mod.resolve()
        emit("load", uris)

        var len = mod._remain = uris.length
        var m

        // Initialize modules and register waitings
        for (var i = 0; i < len; i++) {
            m = Module.get(uris[i])

            if (m.status < STATUS.LOADED) {
                // Maybe duplicate
                m._waitings[mod.uri] = (m._waitings[mod.uri] || 0) + 1
            } else {
                mod._remain--
            }
        }

        if (mod._remain === 0) {
            mod.onload()
            return
        }

        // Begin parallel loading
        var requestCache = {}

        for ( i = 0; i < len; i++) {
            m = cachedMods[uris[i]]

            if (m.status < STATUS.FETCHING) {
                m.fetch(requestCache)
            } else if (m.status === STATUS.SAVED) {
                m.load()
            }
        }

        // Send all requests at last to avoid cache bug in IE6-9. Issues#808
        for (var requestUri in requestCache) {
            if (requestCache.hasOwnProperty(requestUri)) {
                requestCache[requestUri]()
            }
        }
    }
    // Call this method when module is loaded
    Module.prototype.onload = function() {
        var mod = this
        mod.status = STATUS.LOADED

        if (mod.callback) {
            mod.callback()
        }

        // Notify waiting modules to fire onload
        var waitings = mod._waitings
        var uri, m

        for (uri in waitings) {
            if (waitings.hasOwnProperty(uri)) {
                m = cachedMods[uri]
                m._remain -= waitings[uri]
                if (m._remain === 0) {
                    m.onload()
                }
            }
        }

        // Reduce memory taken
        delete mod._waitings
        delete mod._remain
    }
    // Fetch a module
    Module.prototype.fetch = function(requestCache) {
        var mod = this
        var uri = mod.uri

        mod.status = STATUS.FETCHING

        // Emit `fetch` event for plugins such as combo plugin
        var emitData = {
            uri : uri
        }
        emit("fetch", emitData)
        var requestUri = emitData.requestUri || uri

        // Empty uri or a non-CMD module
        if (!requestUri || fetchedList[requestUri]) {
            mod.load()
            return
        }

        if (fetchingList[requestUri]) {
            callbackList[requestUri].push(mod)
            return
        }

        fetchingList[requestUri] = true
        callbackList[requestUri] = [mod]

        // Emit `request` event for plugins such as text plugin
        emit("request", emitData = {
            uri : uri,
            requestUri : requestUri,
            onRequest : onRequest,
            charset : data.charset
        })

        if (!emitData.requested) {
            requestCache ? requestCache[emitData.requestUri] = sendRequest : sendRequest()
        }

        function sendRequest() {
            request(emitData.requestUri, emitData.onRequest, emitData.charset)
        }

        function onRequest() {
            delete fetchingList[requestUri]
            fetchedList[requestUri] = true

            // Save meta data of anonymous module
            if (anonymousMeta) {
                Module.save(uri, anonymousMeta)
                anonymousMeta = null
            }

            // Call callbacks
            var m, mods = callbackList[requestUri]
            delete callbackList[requestUri]
            while (( m = mods.shift()))
            m.load()
        }

    }
    // Execute a module
    Module.prototype.exec = function() {
        var mod = this

        // When module is executed, DO NOT execute it again. When module
        // is being executed, just return `module.exports` too, for avoiding
        // circularly calling
        if (mod.status >= STATUS.EXECUTING) {
            return mod.exports
        }

        mod.status = STATUS.EXECUTING

        // Create require
        var uri = mod.uri

        function require(id) {
            return Module.get(require.resolve(id)).exec()
        }


        require.resolve = function(id) {
            return Module.resolve(id, uri)
        }

        require.async = function(ids, callback) {
            Module.use(ids, callback, uri + "_async_" + cid())
            return require
        }
        // Exec factory
        var factory = mod.factory

        var exports = isFunction(factory) ? factory(require, mod.exports = {}, mod) : factory

        if (exports === undefined) {
            exports = mod.exports
        }

        // Emit `error` event
        if (exports === null && !IS_CSS_RE.test(uri)) {
            emit("error", mod)
        }

        // Reduce memory leak
        delete mod.factory

        mod.exports = exports
        mod.status = STATUS.EXECUTED

        // Emit `exec` event
        emit("exec", mod)

        return exports
    }
    // Resolve id to uri
    Module.resolve = function(id, refUri) {
        // Emit `resolve` event for plugins such as text plugin
        var emitData = {
            id : id,
            refUri : refUri
        }
        emit("resolve", emitData)

        return emitData.uri || id2Uri(emitData.id, refUri)
    }
    // Define a module
    Module.define = function(id, deps, factory) {
        var argsLen = arguments.length

        // define(factory)
        if (argsLen === 1) {
            factory = id
            id = undefined
        } else if (argsLen === 2) {
            factory = deps

            // define(deps, factory)
            if (isArray(id)) {
                deps = id
                id = undefined
            }
            // define(id, factory)
            else {
                deps = undefined
            }
        }

        // Parse dependencies according to the module factory code
        if (!isArray(deps) && isFunction(factory)) {
            deps = parseDependencies(factory.toString())
        }

        var meta = {
            id : id,
            uri : Module.resolve(id),
            deps : deps,
            factory : factory
        }

        // Try to derive uri in IE6-9 for anonymous modules
        if (!meta.uri && doc.attachEvent) {
            var script = getCurrentScript()

            if (script) {
                meta.uri = script.src
            }

            // NOTE: If the id-deriving methods above is failed, then falls back
            // to use onload event to get the uri
        }

        // Emit `define` event, used in nocache plugin, seajs node version etc
        emit("define", meta)

        meta.uri ? Module.save(meta.uri, meta) :
        // Save information for "saving" work in the script onload event
        anonymousMeta = meta
    }
    // Save meta data to cachedMods
    Module.save = function(uri, meta) {
        var mod = Module.get(uri)

        // Do NOT override already saved modules
        if (mod.status < STATUS.SAVED) {
            mod.id = meta.id || uri
            mod.dependencies = meta.deps || []
            mod.factory = meta.factory
            mod.status = STATUS.SAVED
        }
    }
    // Get an existed module or create a new one
    Module.get = function(uri, deps) {
        return cachedMods[uri] || (cachedMods[uri] = new Module(uri, deps))
    }
    // Use function is equal to load a anonymous module
    Module.use = function(ids, callback, uri) {
        var mod = Module.get(uri, isArray(ids) ? ids : [ids])

        mod.callback = function() {
            var exports = []
            var uris = mod.resolve()

            for (var i = 0, len = uris.length; i < len; i++) {
                exports[i] = cachedMods[uris[i]].exec()
            }

            if (callback) {
                callback.apply(global, exports)
            }
            delete mod.callback
        }

        mod.load()
    }
    // Load preload modules before all other modules
    Module.preload = function(callback) {
        var preloadMods = data.preload
        var len = preloadMods.length

        if (len) {
            Module.use(preloadMods, function() {
                // Remove the loaded preload modules
                preloadMods.splice(0, len)

                // Allow preload modules to add new preload modules
                Module.preload(callback)
            }, data.cwd + "_preload_" + cid())
        } else {
            callback()
        }
    }
    // Public API

    seajs.use = function(ids, callback) {
        Module.preload(function() {
            Module.use(ids, callback, data.cwd + "_use_" + cid())
        })
        return seajs
    }

    Module.define.cmd = {}
    global.define = Module.define

    // For Developers

    seajs.Module = Module
    data.fetchedList = fetchedList
    data.cid = cid

    seajs.resolve = id2Uri
    seajs.require = function(id) {
        return (cachedMods[Module.resolve(id)] || {}).exports
    }
    /**
	 * config.js - The configuration for the loader
	 */

    var BASE_RE = /^(.+?\/)(\?\?)?(seajs\/)+/

    // The root path to use for id2uri parsing
    // If loaderUri is `http://test.com/libs/seajs/[??][seajs/1.2.3/]sea.js`,
	// the
    // baseUri should be `http://test.com/libs/`
    data.base = (loaderDir.match(BASE_RE) || ["", loaderDir])[1]

    // The loader directory
    data.dir = loaderDir

    // The current working directory
    data.cwd = cwd

    // The charset for requesting files
    data.charset = "utf-8"

    // Modules that are needed to load before all other modules
    data.preload = (function() {
        var plugins = []

        // Convert `seajs-xxx` to `seajs-xxx=1`
        // NOTE: use `seajs-xxx=1` flag in uri or cookie to preload `seajs-xxx`
        var str = loc.search.replace(/(seajs-\w+)(&|$)/g, "$1=1$2")

        // Add cookie string
        str += " " + doc.cookie

        // Exclude seajs-xxx=0
        str.replace(/(seajs-\w+)=1/g, function(m, name) {
            plugins.push(name)
        })

        return plugins
    })()

    // data.alias - An object containing shorthands of module id
    // data.paths - An object containing path shorthands in module id
    // data.vars - The {xxx} variables in module id
    // data.map - An array containing rules to map module uri
    // data.debug - Debug mode. The default value is false

    seajs.config = function(configData) {

        for (var key in configData) {
            var curr = configData[key]
            var prev = data[key]

            // Merge object config such as alias, vars
            if (prev && isObject(prev)) {
                for (var k in curr) {
                    prev[k] = curr[k]
                }
            } else {
                // Concat array config such as map, preload
                if (isArray(prev)) {
                    curr = prev.concat(curr)
                }
                // Make sure that `data.base` is an absolute path
                else if (key === "base") {
                    (curr.slice(-1) === "/") || (curr += "/")
                    curr = addBase(curr)
                }

                // Set config
                data[key] = curr
            }
        }

        emit("config", configData)
        return seajs
    }
})(this);

// +++++++++++++++++++++++++++++++++++++++ JSON
// ++++++++++++++++++++++++++++++++++++++++++++++++

// Create a JSON object only if one does not already exist. We create the
// methods in a closure to avoid creating global variables.

if ( typeof JSON !== 'object') {
    JSON = {};
}( function() {'use strict';

        function f(n) {
            // Format integers to have at least two digits.
            return n < 10 ? '0' + n : n;
        }

        if ( typeof Date.prototype.toJSON !== 'function') {

            Date.prototype.toJSON = function() {

                return isFinite(this.valueOf()) ? this.getUTCFullYear() + '-' + f(this.getUTCMonth() + 1) + '-' + f(this.getUTCDate()) + 'T' + f(this.getUTCHours()) + ':' + f(this.getUTCMinutes()) + ':' + f(this.getUTCSeconds()) + 'Z' : null;
            };

            String.prototype.toJSON = Number.prototype.toJSON = Boolean.prototype.toJSON = function() {
                return this.valueOf();
            };
        }

        var cx, escapable, gap, indent, meta, rep;

        function quote(string) {

            // If the string contains no control characters, no quote
			// characters, and no
            // backslash characters, then we can safely slap some quotes around
			// it.
            // Otherwise we must also replace the offending characters with safe
			// escape
            // sequences.

            escapable.lastIndex = 0;
            return escapable.test(string) ? '"' + string.replace(escapable, function(a) {
                var c = meta[a];
                return typeof c === 'string' ? c : '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
            }) + '"' : '"' + string + '"';
        }

        function str(key, holder) {

            // Produce a string from holder[key].

            var i, // The loop counter.
            k, // The member key.
            v, // The member value.
            length, mind = gap, partial, value = holder[key];

            // If the value has a toJSON method, call it to obtain a replacement
			// value.

            if (value && typeof value === 'object' && typeof value.toJSON === 'function') {
                value = value.toJSON(key);
            }

            // If we were called with a replacer function, then call the
			// replacer to
            // obtain a replacement value.

            if ( typeof rep === 'function') {
                value = rep.call(holder, key, value);
            }

            // What happens next depends on the value's type.

            switch (typeof value) {
                case 'string':
                    return quote(value);

                case 'number':

                    // JSON numbers must be finite. Encode non-finite numbers as
					// null.

                    return isFinite(value) ? String(value) : 'null';

                case 'boolean':
                case 'null':

                    // If the value is a boolean or null, convert it to a
					// string. Note:
                    // typeof null does not produce 'null'. The case is included
					// here in
                    // the remote chance that this gets fixed someday.

                    return String(value);

                // If the type is 'object', we might be dealing with an object
				// or an array or
                // null.

                case 'object':

                    // Due to a specification blunder in ECMAScript, typeof null
					// is 'object',
                    // so watch out for that case.

                    if (!value) {
                        return 'null';
                    }

                    // Make an array to hold the partial results of stringifying
					// this object value.

                    gap += indent;
                    partial = [];

                    // Is the value an array?

                    if (Object.prototype.toString.apply(value) === '[object Array]') {

                        // The value is an array. Stringify every element. Use
						// null as a placeholder
                        // for non-JSON values.

                        length = value.length;
                        for ( i = 0; i < length; i += 1) {
                            partial[i] = str(i, value) || 'null';
                        }

                        // Join all of the elements together, separated with
						// commas, and wrap them in
                        // brackets.

                        v = partial.length === 0 ? '[]' : gap ? '[\n' + gap + partial.join(',\n' + gap) + '\n' + mind + ']' : '[' + partial.join(',') + ']';
                        gap = mind;
                        return v;
                    }

                    // If the replacer is an array, use it to select the members
					// to be stringified.

                    if (rep && typeof rep === 'object') {
                        length = rep.length;
                        for ( i = 0; i < length; i += 1) {
                            if ( typeof rep[i] === 'string') {
                                k = rep[i];
                                v = str(k, value);
                                if (v) {
                                    partial.push(quote(k) + ( gap ? ': ' : ':') + v);
                                }
                            }
                        }
                    } else {

                        // Otherwise, iterate through all of the keys in the
						// object.

                        for (k in value) {
                            if (Object.prototype.hasOwnProperty.call(value, k)) {
                                v = str(k, value);
                                if (v) {
                                    partial.push(quote(k) + ( gap ? ': ' : ':') + v);
                                }
                            }
                        }
                    }

                    // Join all of the member texts together, separated with
					// commas,
                    // and wrap them in braces.

                    v = partial.length === 0 ? '{}' : gap ? '{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}' : '{' + partial.join(',') + '}';
                    gap = mind;
                    return v;
            }
        }

        // If the JSON object does not yet have a stringify method, give it one.

        if ( typeof JSON.stringify !== 'function') {
            escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
            meta = {// table of character substitutions
                '\b' : '\\b',
                '\t' : '\\t',
                '\n' : '\\n',
                '\f' : '\\f',
                '\r' : '\\r',
                '"' : '\\"',
                '\\' : '\\\\'
            };
            JSON.stringify = function(value, replacer, space) {

                // The stringify method takes a value and an optional replacer,
				// and an optional
                // space parameter, and returns a JSON text. The replacer can be
				// a function
                // that can replace values, or an array of strings that will
				// select the keys.
                // A default replacer method can be provided. Use of the space
				// parameter can
                // produce text that is more easily readable.

                var i;
                gap = '';
                indent = '';

                // If the space parameter is a number, make an indent string
				// containing that
                // many spaces.

                if ( typeof space === 'number') {
                    for ( i = 0; i < space; i += 1) {
                        indent += ' ';
                    }

                    // If the space parameter is a string, it will be used as
					// the indent string.

                } else if ( typeof space === 'string') {
                    indent = space;
                }

                // If there is a replacer, it must be a function or an array.
                // Otherwise, throw an error.

                rep = replacer;
                if (replacer && typeof replacer !== 'function' && ( typeof replacer !== 'object' || typeof replacer.length !== 'number')) {
                    throw new Error('JSON.stringify');
                }

                // Make a fake root object containing our value under the key of
				// ''.
                // Return the result of stringifying the value.

                return str('', {
                    '' : value
                });
            };
        }

        // If the JSON object does not yet have a parse method, give it one.

        if ( typeof JSON.parse !== 'function') {
            cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
            JSON.parse = function(text, reviver) {

                // The parse method takes a text and an optional reviver
				// function, and returns
                // a JavaScript value if the text is a valid JSON text.

                var j;

                function walk(holder, key) {

                    // The walk method is used to recursively walk the resulting
					// structure so
                    // that modifications can be made.

                    var k, v, value = holder[key];
                    if (value && typeof value === 'object') {
                        for (k in value) {
                            if (Object.prototype.hasOwnProperty.call(value, k)) {
                                v = walk(value, k);
                                if (v !== undefined) {
                                    value[k] = v;
                                } else {
                                    delete value[k];
                                }
                            }
                        }
                    }
                    return reviver.call(holder, key, value);
                }

                // Parsing happens in four stages. In the first stage, we
				// replace certain
                // Unicode characters with escape sequences. JavaScript handles
				// many characters
                // incorrectly, either silently deleting them, or treating them
				// as line endings.

                text = String(text);
                cx.lastIndex = 0;
                if (cx.test(text)) {
                    text = text.replace(cx, function(a) {
                        return '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                    });
                }

                // In the second stage, we run the text against regular
				// expressions that look
                // for non-JSON patterns. We are especially concerned with '()'
				// and 'new'
                // because they can cause invocation, and '=' because it can
				// cause mutation.
                // But just to be safe, we want to reject all unexpected forms.

                // We split the second stage into 4 regexp operations in order
				// to work around
                // crippling inefficiencies in IE's and Safari's regexp engines.
				// First we
                // replace the JSON backslash pairs with '@' (a non-JSON
				// character). Second, we
                // replace all simple value tokens with ']' characters. Third,
				// we delete all
                // open brackets that follow a colon or comma or that begin the
				// text. Finally,
                // we look to see that the remaining characters are only
				// whitespace or ']' or
                // ',' or ':' or '{' or '}'. If that is so, then the text is
				// safe for eval.

                if (/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

                    // In the third stage we use the eval function to compile
					// the text into a
                    // JavaScript structure. The '{' operator is subject to a
					// syntactic ambiguity
                    // in JavaScript: it can begin a block or an object literal.
					// We wrap the text
                    // in parens to eliminate the ambiguity.

                    j = eval('(' + text + ')');

                    // In the optional fourth stage, we recursively walk the new
					// structure, passing
                    // each name/value pair to a reviver function for possible
					// transformation.

                    return typeof reviver === 'function' ? walk({
                        '' : j
                    }, '') : j;
                }

                // If the text is not JSON parseable, then a SyntaxError is
				// thrown.

                throw new SyntaxError('JSON.parse');
            };
        }
    }());

// +++++++++++++++++++++++++++++++++++++++ 项目配置
// ++++++++++++++++++++++++++++++++++++++++++

var SITE_URL = "/";
window.BDY = window.BDY || {};
if ('createTouch' in document) {
    BDY.click = 'click';
    BDY.touchstart = "touchstart";
    BDY.touchmove = "touchmove";
    BDY.touchend = "touchend";
    BDY.longTap = 'longTap';
} else {
    BDY.click = 'click';
    BDY.touchstart = "mousedown";
    BDY.touchmove = "mousemove";
    BDY.touchend = "mouseup";
    BDY.longTap = 'hover';
}

BDY.url = SITE_URL;
BDY.debug = true;
BDY.version = 1;

seajs.config({
    alias : {
        'jquery' : 'js/libs/jquery-1.10.2.js',
        'jquery-ui' : 'js/libs/jquery-ui.min.js',
        'bootstrap' : 'js/libs/bootstrap.min.js',
        'datetimepicker' : 'js/libs/bootstrap-datetimepicker.min.js',
        'select2' : 'js/libs/select2/js/select2.full.min.js',
        'submenu' : 'js/libs/bootstrap-submenu.js',
        'tpl' : 'js/libs/template.js',
        'lib' : 'js/libs/library.js',
        'treeTable' : 'js/libs/treeTable/jquery.treeTable.js',
        'form' : 'js/libs/form.js',
        'colortip' : 'js/libs/jquery.colortip.js',
        'jqext' : 'js/libs/jqext.js',
        'md5' : 'js/libs/md5.js',
        'clipboard' : 'js/libs/clipboard.min.js',
        'echarts' : 'js/libs/echarts.common.min.js',
        'map' : [
            [ /^(.*\.(?:css|js|html))$/i, '$1?' + BDY.version ],
            [ /^(.*\.(?:css|js|html)(.+))$/i, '$1&' + BDY.version ]
        ]
    },
    debug : BDY.debug,
    base : SITE_URL ? SITE_URL + 'static/' : './static/',
    charset : 'utf-8',
    timeout : 20000
});

BDY.params = [];
BDY.timeout = [];
BDY.interval = [];



