
function getElementsByClassName(node,classname) {
    alert('test');
    if (node.getElementsByClassName) { // use native implementation if available
        return node.getElementsByClassName(classname);
    } else {
        return (function getElementsByClass(searchClass,node) {
            if ( node == null )
                node = document;
            var classElements = [],
            els = node.getElementsByTagName("*"),
            elsLen = els.length,
            pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)"), i, j;

            for (i = 0, j = 0; i < elsLen; i++) {
                if ( pattern.test(els[i].className) ) {
                    classElements[j] = els[i];
                    j++;
                }
            }
            return classElements;
        })(classname, node);
    }
}

function selectAll (check_var) {
    for (var i=0; i<document.forms["list"].elements.length; i++) {
        var e=document.forms["list"].elements[i];
        if (e.type == "checkbox") e.checked = check_var;
    }
}

window.onload = function() {
    var eSelect = document.getElementById('fileActions');
    var eShowActions = document.getElementById('showActions');
    var eHideActions = document.getElementById('hideActions');
    var eDeleteActions = document.getElementById('deleteActions');
    var eDownloadActions = document.getElementById('downloadActions');
    var eTagActions = document.getElementById('tagActions');
    var eMoveActions = document.getElementById('moveActions');
    if (eSelect) {
        eSelect.onchange = function() {
            if(eSelect.value === "show") {
                eShowActions.style.display = 'block';
            } else {
                eShowActions.style.display = 'none';
            }
            if(eSelect.value === "hide") {
                eHideActions.style.display = 'block';
            } else {
                eHideActions.style.display = 'none';
            }
            if(eSelect.value === "delete") {
                eDeleteActions.style.display = 'block';
            } else {
                eDeleteActions.style.display = 'none';
            }
            // if ZipArchive is unavailable, this becomes null!
            if (eDownloadActions !== null) {
                if(eSelect.value === "download") {
                    eDownloadActions.style.display = 'block';
                } else {
                    eDownloadActions.style.display = 'none';
                }
            }
            if(eSelect.value === "tag") {
                eTagActions.style.display = 'block';
            } else {
                eTagActions.style.display = 'none';
            }
            if(eSelect.value === "move") {
                eMoveActions.style.display = 'block';
            } else {
                eMoveActions.style.display = 'none';
            }
        }
    }
}

function selectAllComments (check_var) {
    for (var i=0; i<document.forms["listComments"].elements.length; i++) {
        var e=document.forms["listComments"].elements[i];
        if (e.type == "checkbox") e.checked = check_var;
    }
}

function selectAllIgnoreIPs (check_var) {
    for (var i=0; i<document.forms["listIgnoreIPs"].elements.length; i++) {
        var e=document.forms["listIgnoreIPs"].elements[i];
        if (e.type == "checkbox") e.checked = check_var;
    }
}

function selectAllHiddenComments (check_var) {
    for (var i=0; i<document.forms["listHiddenComments"].elements.length; i++) {
        var e=document.forms["listHiddenComments"].elements[i];
        if (e.type == "checkbox") e.checked = check_var;
    }
}

function showAllImages() {
    var ele2 = document.getElementsByClassName("imPreview");
    var ele1 = document.getElementById("toggleText");
    var text = document.getElementById("displayText");
    if(ele1.style.display == "block") {
        ele1.style.display = "none";
        text.innerHTML = "<span style=\"float: left;width: 1.2em;\">+<\/span> Show all images";
        ele2.style.display = "none";
        for (var i=0; i<document.forms["list"].elements.length; i++) {

        }
    }
    else {
        ele1.style.display = "block";
        text.innerHTML = "<span style=\"float: left;width: 1.2em;\">-<\/span> Hide all images";
        ele2.style.display = "block";
    }
}
