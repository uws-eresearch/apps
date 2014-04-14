var J5 = {
    width: 600,             // set slide width
    height: 450,            // set slide height
    keepSlides: false,      // keeps slides in the document (in addition to in the slideshow)
    notesEnabled: true,     // enables slide notes
    notesVisible: false,    // sets whether slide notes as visible by default
    notesHeight: 200        // sets height of the slide note tray
};

J5.loadIframe = function() {
    // create iframe and insert before first slide on page
    var frame = this.iframe = this.slides[0].parentNode.insertBefore(document.createElement("iframe"), this.slides[0]);
    frame.width = this.width;
    frame.height = this.height;
    frame.setAttribute("allowfullscreen", "");
    frame.style.cssText = "border: 3px solid black; display: block; margin-bottom: 10px; margin-top: 10px; box-sizing: content-box; -moz-box-sizing: content-box; -webkit-box-sizing: content-box;";
    
    // setup iframe document
    var doc = frame.contentDocument;
    doc.open();
    doc.write("<!DOCTYPE html><html><head></head><body></body></html>");
    doc.close();
    
    // define iframe content styles
    var styles = "html { height: 100%; }" +
                 "body { margin: 0; background-color: black; height: 100%; width: 100%; }" +
                 "iframe { border: none; display: block; background-color: white; width: 100%; height: " + this.height +  "px; }" +
                 "#controls { color: white; font-family: monospace; font-size: 13px; text-align: center; background-color: #444; }" +
                 "#controls { width: 100%; height: 30px; padding: 0px; box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; position: relative; }" +
                 "#controls path { fill: white; }" +
                 "button { background-color: transparent; border: none; cursor: pointer; outline: none; height: 30px; padding: 5px; margin: 0px 3px; position: relative; }" +
                 "button:active { top: 0px; left: 0px; }" +
                 "#controls button[disabled] svg path { fill: #888; }" +
                 "#leftcontrols { position: absolute; left: 6px; top: 6px; margin: 0px; }" +
                 "#slideidx { border: none; background-color: rgba(255, 255, 255, 0.2); color: white; text-align: center; width: 30px; height: 16px; }" +
                 "#rightcontrols { position: absolute; right: 0px; bottom: 0px; margin: 0px; }" +
                 "#notes { background-color: #CCC; display: none; height: " + this.notesHeight + "px; width: 100%; padding: 10px; box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; margin: 0px 0px 0px 0px; overflow: auto; }";

    // add iframe content styles
    var css = document.createElement("style");
    css.appendChild(document.createTextNode(styles));
    var head = doc.head || doc.getElementsByTagName("head")[0];
    head.appendChild(css);
    
    // add slide viewer UI controls
    frame.height = this.height = parseInt(this.height + 30);
    var noteButton = this.notesEnabled ? "<button id='togglenotes' title='Toggle Notes' onclick='parent.J5.displayNotes()'><svg viewBox='0 0 512 512' width='20px' height='20px'><path d='M444.125,135.765 L149.953,429.937 l-67.875-67.875 L376.219,67.859 L444.125,135.765 Z M444.125,0 l-45.281,45.234 l67.906,67.906 L512,67.859 L444.125,0 Z M66.063,391.312 L0,512 l120.703-66.063 L66.063,391.312 Z'/></svg></button>" : "";
    var controls = doc.body.appendChild(document.createElement("div"));
    controls.id = "controls";
    controls.innerHTML = "<button id='first' title='First Slide' onclick='parent.J5.goTo(1)'><svg viewBox='0 0 50 50' width='20px' height='20px'><path d='M0,3 H4 V47 H0 V3 Z M50,3 L27,25 L50,47 H30 L7,25 L30,3 H50 Z'></path></svg></button>" +
                         "<button id='prev' title='Previous Slide' onclick='parent.J5.goBack()'><svg viewBox='0 0 50 50' width='20px' height='20px'><path d='M47,3 L24,25 L47,47 H27 L4,25 L27,3 H47 Z'></path></svg></button>" +
                         "<button id='next' title='Next Slide' onclick='parent.J5.goForward()'><svg viewBox='0 0 50 50' width='20px' height='20px'><path d='M3,3 L26,25 L3,47 H23 L46,25 L23,3 H3 Z'></path></svg></button>" +
                         "<button id='last' title='Last Slide' onclick='parent.J5.goTo(this.parentNode.querySelector(\"#slidecount\").innerHTML)'><svg viewBox='0 0 50 50' width='20px' height='20px'><path d='M50,3 H46 V47 H50 V3 Z M0,3 L23,25 L0,47 H20 L43,25 L20,3 H0 Z'></path></svg></button>" +
                         "<p id='leftcontrols'><input onchange='parent.J5.goTo(this.value)' id='slideidx' maxlength='3' value='0'> /<span id='slidecount'>...</span></p>" +
                         "<p id='rightcontrols'>" + noteButton +
                         "<button id='fullscreen' title='Fullscreen' onclick='parent.J5.fullscreen()'><svg viewBox='0 0 512 512' width='20px' height='20px'><path d='M130,210 L40,120 L0,160 L0,0 L160,0 L120,40 L210,130 Z M382,210 L472,120 L512,160 L512,0 L352,0 L392,40 L302,130 Z M382,302 L472,392 L512,352 L512,512 L352,512 L392,472 L302,382 Z M210,382 L120,472 L160,512 L0,512 L0,352 L40,392 L130,302 Z'/></svg></button></p>";
    
    // add slide note box
    var notes = this.notes = doc.body.appendChild(document.createElement("div"));
    notes.id = "notes";
    
    // setup keyboard shortcuts
    frame.contentWindow.onkeydown = function(e) {
        // ignore modifier keys
        if (e.altKey || e.ctrlKey || e.metaKey || e.shiftKey) {
            return;
        }
        // right arrow, down arrow or page down
        if ( e.keyCode == 39 || e.keyCode == 40 || e.keyCode == 34) {
            e.preventDefault();
            J5.goForward();
        }
        // left arrow, up arrow or page up
        if (e.keyCode == 37 || e.keyCode == 38 || e.keyCode == 33) { 
            e.preventDefault();
            J5.goBack();
        }
    }
    
    // setup cross-window messaging
    frame.contentWindow.onmessage = function(e) {
        var args = e.data.split(" ");
        var argc = args.length;
        args.forEach(function(e, i, a) { a[i] = decodeURIComponent(e) });
        if (args[0] == "LOCATION" && argc == 2) {
            J5.idx = parseInt(args[1]);
            doc.getElementById("slideidx").value = J5.idx;
            doc.getElementById("first").disabled = doc.getElementById("prev").disabled = J5.idx == 1;
            doc.getElementById("last").disabled = doc.getElementById("next").disabled = J5.idx == J5.slides.length;
            doc.getElementById("notes").innerHTML = J5.getNotes(J5.idx);
        }
        if (args[0] == "REGISTERED") {
            doc.getElementById("slidecount").innerHTML = J5.slides.length;
        }
    }
    
    // create iframe for slides
    var targetElement = this.iframe.contentDocument.body;
    var slideFrame = targetElement.insertBefore(document.createElement("iframe"), targetElement.firstChild);
    var slideDoc = slideFrame.contentDocument;
    slideDoc.open();
    slideDoc.write("<!DOCTYPE html><html><head></head><body></body></html>");
    slideDoc.close();
    
    J5.displayNotes();
    J5.loadSlides(slideFrame.contentWindow);
    J5.view = slideFrame.contentWindow;
    J5.view.postMessage("REGISTER", "*");
}

J5.loadSlides = function(win) {
    var doc = win.document;
    win.idx = 1;
    
    // define iframe content styles
    var styles = "html { background-color: black; }" +
                 "a { color: #0066FF; }" +
                 "a:hover { text-decoration: underline; }" +
                 "footer { position: absolute; bottom: 50px; right: 50px; }" +
                 "strong { color: #0066FF; }" +
                 "body { font-family: 'Oswald', arial, serif; background-color: #1C1C1C; color: white; font-size: 30px; line-height: 120%; }" +
                 "img { margin: 0 auto; display: block; }" +
                 "p>img { position: relative; left: -10px }" +
                 "section { -moz-transition: left 400ms linear 0s; -webkit-transition: left 400ms linear 0s; -ms-transition: left 400ms linear 0s; transition: left 400ms linear 0s; }" +
                 "section { background: #1C1C1C; }" +
                 "h1 { color: #FFA500; margin: 20px 0; font-size: 46px; text-align: center; padding: 0 10px; line-height: 100% }" +
                 "h2 { color: #FF0066; margin: 20px 0; font-size: 40px; text-align: center; padding: 0 10px; line-height: 100% }" +
                 "h3 { color: #FFD700; margin: 20px 0; font-size: 34px; text-align: center; padding: 0 10px; line-height: 100% }" +
                 "ul { margin: 10px 0 0 80px; font-size: 0.9em; }" +
                 "q, p { padding: 5px 40px; }" +
                 "q:after { content: ''; }" +
                 "q:before { content: ''; }" +
                 "q { display: block; margin-top: 140px; }" +
                 "object { display: block; }" +
                 "video { margin: 0 auto; display: block; width: 400px; height: 300px; }" +
                 "li { list-style-type: square; }" +
                 // important styles below - don't touch!
                 "* { margin: 0; padding: 0; }" +
                 "details {display: none; }" +
                 "body { width: 800px; height: 600px; margin-left: -400px; margin-top: -300px; position: absolute; top: 50%; left: 50%; overflow: hidden; }" +
                 "html { overflow: hidden; }" +
                 "section { position: absolute; pointer-events: none; width: 100%; height: 100%; overflow: hidden; left: -150%; }" +
                 "section[aria-selected] { pointer-events: auto; left: 0; }" +
                 "section[aria-selected] ~ section { left: +150%; }" +
                 "body { display: none }" +
                 "body.loaded { display: block }";

    // add iframe content styles
    var css = doc.createElement("style");
    css.appendChild(doc.createTextNode(styles));
    var head = doc.head || doc.getElementsByTagName("head")[0];
    head.appendChild(css);
    
    // scale slides to iframe window size
    win.onresize = function() {
        var body = this.document.body;
        var sx = body.clientWidth / this.innerWidth;
        var sy = body.clientHeight / this.innerHeight;
        var transform = "scale(" + (1/Math.max(sx, sy)) + ")";
        body.style.MozTransform = transform;
        body.style.WebkitTransform = transform;
        body.style.OTransform = transform;
        body.style.msTransform = transform;
        body.style.transform = transform;
    }
    
    // setup keyboard shortcuts
    win.onkeydown = function(e) {
        // ignore modifier keys
        if (e.altKey || e.ctrlKey || e.metaKey || e.shiftKey) {
            return;
        }
        // right arrow, down arrow or page down
        if ( e.keyCode == 39 || e.keyCode == 40 || e.keyCode == 34) {
            e.preventDefault();
            this.goForward();
        }
        // left arrow, up arrow or page up
        if (e.keyCode == 37 || e.keyCode == 38 || e.keyCode == 33) { 
            e.preventDefault();
            this.goBack();
        }
    }
    
    // setup cross-window messaging
    win.onmessage = function(e) {
        var args = e.data.split(" ");
        var argc = args.length;
        args.forEach(function(e, i, a) { a[i] = decodeURIComponent(e) });
        if (args[0] == "FORWARD") {
            this.goForward();
        }
        if (args[0] == "BACK") {
            this.goBack();
        }
        if (args[0] == "GOTO" && argc == 2) {
            this.goTo(parseInt(args[1]));
        }
        if (args[0] == "REGISTER") {
            this.postMsg(J5.iframe.contentWindow, "REGISTERED");
            this.postMsg(J5.iframe.contentWindow, "LOCATION", this.idx);
        }
    }
    
    // add slides to iframe
    for (var i = 0; i < J5.slides.length; i++) {
        if (J5.slides[i].parentNode && !J5.keepSlides) {
            J5.slides[i].parentNode.removeChild(J5.slides[i]);
        }
        var slide = doc.createElement("section");
        doc.body.appendChild(slide);
        slide.outerHTML = J5.slides[i].outerHTML;
    }
    
    // make image slides fill entire slide 
    var objects = doc.querySelectorAll("section[typeof='http://purl.org/ontology/bibo/Slide'] > object");
    for (var i = 0; i < objects.length; i++) {
        var parent = objects[i].parentNode;
        while (parent.firstChild && parent.firstChild != objects[i]) {
            parent.removeChild(parent.firstChild);
        }
    }

    // scale slide contents to fit slides
    scaleContents = function() {
        var s = doc.querySelectorAll("section[typeof='http://purl.org/ontology/bibo/Slide']")
        for (var i = 0; i < s.length; i++) {
            if (s[i].scrollHeight > 600 || s[i].scrollWidth > 800) {
                var images = s[i].getElementsByTagName("img");
                for (var j = 0; j < images.length; j++) {
                    if (images[j].height > 600 || images[j].width > 800) {
                        var sx = images[j].scrollWidth / 800;
                        var sy = images[j].scrollHeight / 600;
                        var scaleAmount = 1 / Math.max(sx, sy);
                        images[j].width = images[j].width * scaleAmount;
                        images[j].height = images[j].height * scaleAmount;
                    }
                }
                
                var wrapper = doc.createElement("div");
                for (var j = 0; j < s[i].children.length;) {
                    wrapper.appendChild(s[i].children[j]);
                }
                s[i].appendChild(wrapper);
                var sx = s[i].scrollWidth / 800;
                var sy = s[i].scrollHeight / 600;
                var scaleAmount = 1 / Math.max(sx, sy);
                if (scaleAmount < 0.5) scaleAmount = 0.5;
                var transform = "scale(" + scaleAmount + ")";
                wrapper.style.MozTransform = transform;
                wrapper.style.WebkitTransform = transform;
                wrapper.style.OTransform = transform;
                wrapper.style.msTransform = transform;
                wrapper.style.transform = transform;
                wrapper.style.MozTransformOrigin = "center top";
                wrapper.style.WebkitTransformOrigin = "center top";
                wrapper.style.OTransformOrigin = "center top";
                wrapper.style.msTransformOrigin = "center top";
                wrapper.style.TransformOrigin= "center top";
            }
        }
    }
    
    // set active slide
    win.setSlide = function(i) {
        if (!(i >= 1 && i <= J5.slides.length)) return;
        this.idx = i;
        var old = this.document.querySelector("section[aria-selected]");
        var next = this.document.querySelector("section[typeof='http://purl.org/ontology/bibo/Slide']:nth-of-type("+ this.idx +")");
        if (old) {
            old.removeAttribute("aria-selected");
            var video = old.querySelector("video");
            if (video) { video.pause(); }
        }
        if (next) {
            next.setAttribute("aria-selected", "true");
            var video = next.querySelector("video");
            if (video) { video.play(); }
        }
        this.postMsg(J5.iframe.contentWindow, "LOCATION", this.idx);
    }
    
    win.postMsg = function(win, msg) {
        msg = [msg];
        for (var i = 2; i < arguments.length; i++)
          msg.push(encodeURIComponent(arguments[i]));
        win.postMessage(msg.join(" "), "*");
    }
    
    win.goForward = function() {
        this.setSlide(this.idx + 1);
    }
    
    win.goBack = function() {
        this.setSlide(this.idx - 1);
    }
    
    win.goTo = function(s) {
        this.setSlide(s);
    }
    
    win.setSlide(win.idx);
    doc.body.className = "loaded";
    scaleContents();
    win.onresize();
}

J5.goForward = function() {
    this.view.postMessage("FORWARD", "*");
}

J5.goBack = function() {
    this.view.postMessage("BACK", "*");
}

J5.goTo = function(s) {
    this.view.postMessage("GOTO " + s, "*");
}

J5.getNotes = function(n) {
    var slide = this.slides[n - 1];
    return slide.querySelector("details") ? slide.querySelector("details").innerHTML : "";
}

J5.displayNotes = function() {
    if (!this.notesEnabled) return;
    if (this.notesVisible) {
        this.notes.style.display = "block";
        this.iframe.height = this.height = parseInt(this.height) + this.notes.clientHeight;
    }
    else {
        this.iframe.height = this.height = parseInt(this.height) - this.notes.clientHeight;
        this.notes.style.display = "none";
    }
    this.notesVisible = !this.notesVisible;
}

J5.fullscreen = function() {
    var e = this.view.frameElement;
    var fullscreenAllowed = e.requestFullscreen || e.requestFullScreen || e.mozRequestFullScreen || e.webkitRequestFullScreen || e.oRequestFullScreen || e.msRequestFullScreen;
    if (fullscreenAllowed) {
        fullscreenAllowed.apply(e);
    }
    else {
        //alert("Fullscreen is not supported in this browser.");
        var win = window.open("","","width=800,height=600,menubar=0,toolbar=0,scrollbars=0,resizable=1");
        win.document.open();
        win.document.write("<!DOCTYPE html><html><head></head><body onload='window.opener.J5.loadSlides(window)'></body></html>");
        win.document.close();
    }
}

J5.init = function() {
    var transformSupport = 'WebkitTransform' in document.body.style || 'MozTransform' in document.body.style || 'msTransform' in document.body.style || 'OTransform' in document.body.style || 'transform' in document.body.style;
    if (!transformSupport) return;

    J5.slides = document.querySelectorAll("section[typeof='http://purl.org/ontology/bibo/Slide']");
    if (J5.slides.length > 0) {
        J5.loadIframe();
    }
}

window.addEventListener("DOMContentLoaded", J5.init);