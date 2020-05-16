<!-- Browser Check -->
iens6=document.all||document.getElementById
ns4=document.layers

<!--GLOBAL VARIABLES-->
var theTooltip
var timerID
var mouseX=0
var mouseY=0
var ttWidth=360
<!--END GLOBAL VARIABLES-->

if(iens6){
    theTooltip=document.getElementById ? document.getElementById("tooltip") : document.all["tooltip"]
    theTooltip.style.width=ttWidth
}
if(ns4) {
    document.captureEvents(Event.MOUSEMOVE)
    theTooltip = eval("document.nstooltip")
    theTooltip.visibility="hidden"
    theTooltip.width=ttWidth
    theTooltip.clip.width=ttWidth
}
document.onmousemove=getXY

<!--GLOBAL FUNCTIONS-->

function showTooltip(thetext) {

    var winHeight
    var bottomEdge = 0
    var rightEdge = 0

    clearTimeout(timerID)

    if(iens6){
        theTooltip.innerHTML = ""
        if (document.all) {
            theTooltip.insertAdjacentHTML("BeforeEnd",thetext)
            rightEdge = document.body.clientWidth
            bottomEdge = document.body.clientHeight
        } else {
            theTooltip.innerHTML=thetext
            rightEdge = window.innerWidth;
            bottomEdge = window.innerHeight;
        }
        winHeight=theTooltip.offsetHeight
    }

    if(ns4){
        winHeight=300
        theTooltip.height=winHeight
        theTooltip.document.write(thetext)
        theTooltip.document.close()
    }

    var x = mouseX + 20
    var y = mouseY - (winHeight / 2)
    if (x + ttWidth > rightEdge) {
        x = rightEdge - ttWidth
        y = mouseY + 20;
        if (y + winHeight > bottomEdge) {
            y = mouseY - winHeight - 20;
        }
        if (x < 0) {
            x = 0;
        }
    }
    if (y + winHeight > bottomEdge) {
        y = bottomEdge - winHeight
    }
    if (y < 0) {
        y = 0
    }

    if(iens6){
        if (document.all) {
            theTooltip.style.left=document.body.scrollLeft+x
            theTooltip.style.top=document.body.scrollTop+y
        } else {
            theTooltip.style.left=window.pageXOffset+x
            theTooltip.style.top=window.pageYOffset+y
        }
    }
    if(ns4){
        theTooltip.left=x
        theTooltip.top=y
    }
    timerID=setTimeout("startTooltip()", 1000)
}

function hideTooltip() {
    clearTimeout(timerID)
    timerID=setTimeout("stopTooltip()", 1000)
}

function startTooltip() {
    if(iens6){
        if (document.all) {
            var selects = document.all.tags('select');
            for (var i=0;i<selects.length;i++) {
                selects[i].style.visibility="hidden"
            }
        }
        theTooltip.style.visibility="visible"
    }
    if(ns4){
        theTooltip.visibility = "visible"
    }
}

function stopTooltip() {
    if(iens6) {
        theTooltip.innerHTML = ""
        theTooltip.style.visibility="hidden"
        if (document.all) {
            var selects = document.all.tags('select');
            for (var i=0;i<selects.length;i++) {
                selects[i].style.visibility="visible"
            }
        }
    }
    if(ns4) {
        theTooltip.document.write("")
        theTooltip.document.close()
        theTooltip.visibility="hidden"
    }
}

function getXY(e) {
    if (document.all) {
        mouseX=event.x
        mouseY=event.y
    } else {
        mouseX=e.pageX
        mouseY=e.pageY
    }
}
<!--END GLOBAL FUNCTIONS-->
