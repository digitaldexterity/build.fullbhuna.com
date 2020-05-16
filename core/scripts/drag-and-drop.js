// JavaScript Document
// these two functions find position of elements on screen - useful in centred layouts
function findPosX(obj)
  {
    var curleft = 0;
    if(obj.offsetParent)
        while(1) 
        {
          curleft += obj.offsetLeft;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.x)
        curleft += obj.x;
    return curleft;
  }

function findPosY(obj)
  {
    var curtop = 0;
    if(obj.offsetParent)
        while(1)
        {
          curtop += obj.offsetTop;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.y)
        curtop += obj.y;
    return curtop;
  }
  
  
function MouseEvent(e) // handles cross browser mouse events
{
	if(e) {
		this.e=e;//FF
	} else {
		this.e = window.event; //IE
	}
	
	if(e.pageX) {
		this.x=e.pageX; //FF
	} else {
		this.x=e.clientX;//IE
	}
	
	if(e.pageY) {
		this.y=e.pageY; //FF
	} else {
		this.y = e.clientY;//IE
	}
	
	if(e.target) {
		this.target=e.target; //FF
	} else {
		this.target = e.srcElement;
	}
}



function addListener(type,callback) // handles cross browser add listeners
{
	if(document.addEventListener) {
		document.addEventListener(type, callback, false); //FF
	} else if (document.attachEvent) {
		document.attachEvent("on" +type, callback,false); //IE
	}
}

function removeListener (type,callback) // handles cross browser remove listeners
{
	if(document.removeEventListener) {
		document.removeEventListener(type,callback,false);
	} else if (document.detachEvent) {
		document.detachEvent("on"+type,callback,false);
	}
}



function handleDown(e)
{
	var e = new MouseEvent(e);
	addListener("mousemove", handleMove);
	addListener("mouseup",handleUp);
	offsetX = e.x-parseInt(e.target.style.left);
	offsetY = e.y-parseInt(e.target.style.top);
	//document.getElementById("targetDiv").innerHTML="";
}

function handleMove(e)
{
	var e = new MouseEvent(e);
	var x = e.x - offsetX;
	e.target.style.left = x+"px";
	var y = e.y - offsetY;
	e.target.style.top = y+"px";
}

function handleUp(e)
{
	var e = new MouseEvent(e);
	removeListener("mousemove", handleMove);
	removeListener("mouseup", handleUp);
	// do whatever
	var target = document.getElementById("cart");
	var x = parseInt(findPosX(target));
	var y = parseInt(findPosY(target));
	var width = parseInt(target.style.width);
	var height = parseInt(target.style.height);
	alert(x+"-"+y+"-"+width+"-"+height+"\n"+e.x+"-"+e.y);
	if(e.x>x && e.x<x+width && e.y>y && e.y < y+height) {
		alert("Hooray!");
	}
}

