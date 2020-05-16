// Ticker startup



function startTicker()
{
	
 // Define run time values
 theCurrentStory	 = -1;
 theCurrentLength	= 0;
 // Locate base objects
 if (document.getElementById) { 
	  theAnchorObject	 = document.getElementById("tickerAnchor");
	  document.getElementById('ticker').style.display = 'block'; // added by me as ticker is hidden at start
	  document.getElementById('ticker').style.visibility = 'visible'; // later changed to visibility rather than display
if(theSummaries[0])   {
	runTheTicker();	
} 
   }
 else {
	 
			document.write("<style>#ticker{display:none;}.ticko{border:0px; padding:0px;}</style>");
			return true;
 }
}
// Ticker main run loop
function runTheTicker()
{
	
 var myTimeout;  
 // Go for the next story data block
 if(theCurrentLength == 0)
 {
  theCurrentStory++;
  theCurrentStory	  = theCurrentStory % theItemCount;
  theStorySummary	  = theSummaries[theCurrentStory].replace(/"/g,'"');  
  theTargetLink		= theSiteLinks[theCurrentStory];
  theAnchorObject.href = theTargetLink;
  thePrefix 	  = "<span class=\"tickls\">" + theLeadString + "</span>";
 }
 // Stuff the current ticker text into the anchor
 theAnchorObject.innerHTML = thePrefix + 
 theStorySummary.substring(0,theCurrentLength) + whatWidget();
 // Modify the length for the substring and define the timer
 if(theCurrentLength != theStorySummary.length)
 {
  theCurrentLength++;
  myTimeout = theCharacterTimeout;
 }
 else
 {
  theCurrentLength = 0;
  myTimeout = theStoryTimeout;
 }
 // Call up the next cycle of the ticker
 
 setTimeout(function() {
 	
	 runTheTicker();
 }, myTimeout);
}
// Widget generator
function whatWidget()
{
 if(theCurrentLength == theStorySummary.length)
 {
  return theWidgetNone;
 }

 if((theCurrentLength % 2) == 1)
 {
  return theWidgetOne;
 }
 else
 {
  return theWidgetTwo;
 }
}


// set up

 var theCharacterTimeout = 50;
 var theStoryTimeout	 = 5000;
 var theWidgetOne		= "_";
 var theWidgetTwo		= "-";
 var theWidgetNone	   = "";
 var theLeadString	   = "<strong>LATEST NEWS: </strong>";

 var theSummaries = new Array();
 var theSiteLinks = new Array();
 
 /* continue on the display page as follows....
 
<script language="JavaScript">
<!--

  theSummaries[0] = "China asks Taiwan to consider more direct commercial flights between the countries.";
  theSiteLinks[0] = "/1/hi/world/asia-pacific/4296507.stm";
 
  theSummaries[1] = "Ecuador's former defence minister is arrested for allegedly authorising an arms deal.";
  theSiteLinks[1] = "/1/hi/world/americas/4296201.stm";
 
  theSummaries[2] = "Vietnam reports a new bird flu case, as international conference discusses action.";
  theSiteLinks[2] = "/1/hi/world/asia-pacific/4296495.stm";
 
  theSummaries[3] = "US court dismisses bankruptcy case brought by Russian oil giant Yukos. ";
  theSiteLinks[3] = "/1/hi/business/4296425.stm";
 
var theItemCount = 4;
 addListener("load",startTicker);
 
  //-->
 </script>
 
 In the body....
 
 <div id="ticker"><a id="tickerAnchor" href="#" target="_blank" class="tickl"></a></div>
 
 */