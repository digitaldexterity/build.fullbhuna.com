<?php // Copyright 2009 Paul Egan 
// examples
// $cal = new Calendar;
// echo $cal->getYearView(1999);
// echo $cal->getCurrentMonthView();
// echo $cal->getMonthView(12, 1964);
// 

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID): 1;

if(isset($aquiescedb)) { // get prefs

	$select = "SELECT * FROM eventprefs WHERE ID = ".$regionID;
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$eventPrefs = mysql_fetch_assoc($result);

}

class Calendar
{
    /*
        Constructor for the Calendar class
    */
    function Calendar()
    {
    }
    
    
    /*
        Get the array of strings used to label the days of the week. This array contains seven 
        elements, one for each day of the week. The first entry in this array represents Sunday. 
    */
    function getDayNames()
    {
        return $this->dayNames;
    }
    

    /*
        Set the array of strings used to label the days of the week. This array must contain seven 
        elements, one for each day of the week. The first entry in this array represents Sunday. 
    */
    function setDayNames($names)
    {
        $this->dayNames = $names;
    }
    
    /*
        Get the array of strings used to label the months of the year. This array contains twelve 
        elements, one for each month of the year. The first entry in this array represents January. 
    */
    function getMonthNames()
    {
        return $this->monthNames;
    }
    
    /*
        Set the array of strings used to label the months of the year. This array must contain twelve 
        elements, one for each month of the year. The first entry in this array represents January. 
    */
    function setMonthNames($names)
    {
        $this->monthNames = $names;
    }
    
    
    
    /* 
        Gets the start day of the week. This is the day that appears in the first column
        of the calendar. Sunday = 0.
    */
      function getStartDay()
    {
        return $this->startDay;
    }
    
    /* 
        Sets the start day of the week. This is the day that appears in the first column
        of the calendar. Sunday = 0.
    */
    function setStartDay($day)
    {
        $this->startDay = $day;
    }
    
    
    /* 
        Gets the start month of the year. This is the month that appears first in the year
        view. January = 1.
    */
    function getStartMonth()
    {
        return $this->startMonth;
    }
    
    /* 
        Sets the start month of the year. This is the month that appears first in the year
        view. January = 1.
    */
    function setStartMonth($month)
    {
        $this->startMonth = $month;
    }
    
    
    /*
        Return the URL to link to in order to display a calendar for a given month/year.
        You must override this method if you want to activate the "forward" and "back" 
        feature of the calendar.
        
        Note: If you return an empty string from this function, no navigation link will
        be displayed. This is the default behaviour.
        
        If the calendar is being displayed in "year" view, $month will be set to zero.
    */
    function getCalendarLink($month, $year)
    {
        return "";
    }
    
    /*
        Return the URL to link to  for a given date.
        You must override this method if you want to activate the date linking
        feature of the calendar.
        
        Note: If you return an empty string from this function, no navigation link will
        be displayed. This is the default behaviour.
    */
    function getDateLink($day, $month, $year)
    {
        return "";
    }


    /*
        Return the HTML for the current month
    */
    function getCurrentMonthView()
    {
        $d = getdate(time());
        return $this->getMonthView($d["mon"], $d["year"]);
    }
    

    /*
        Return the HTML for the current year
    */
    function getCurrentYearView()
    {
        $d = getdate(time());
        return $this->getYearView($d["year"]);
    }
    
    
    /*
        Return the HTML for a specified month
    */
    function getMonthView($month, $year)
    {
        return $this->getMonthHTML($month, $year);
    }
    

    /*
        Return the HTML for a specified year
    */
    function getYearView($year)
    {
        return $this->getYearHTML($year);
    }
    
    
    
    /********************************************************************************
    
        The rest are private methods. No user-servicable parts inside.
        
        You shouldn't need to call any of these functions directly.
        
    *********************************************************************************/


    /*
        Calculate the number of days in a month, taking into account leap years.
    */
    function getDaysInMonth($month, $year)
    {
        if ($month < 1 || $month > 12)
        {
            return 0;
        }
   
        $d = $this->daysInMonth[$month - 1];
   
        if ($month == 2)
        {
            // Check for leap year
            // Forget the 4000 rule, I doubt I'll be around then...
        
            if ($year%4 == 0)
            {
                if ($year%100 == 0)
                {
                    if ($year%400 == 0)
                    {
                        $d = 29;
                    }
                }
                else
                {
                    $d = 29;
                }
            }
        }
    
        return $d;
    }


    /*
        Generate the HTML for a given month
    */
    function getMonthHTML($m, $y, $showYear = 1)
    {
        $s = "";
        
        $a = $this->adjustDate($m, $y);
        $month = $a[0];
        $year = $a[1];        
        
    	$daysInMonth = $this->getDaysInMonth($month, $year);
    	$date = getdate(mktime(12, 0, 0, $month, 1, $year));
    	
    	$first = $date["wday"];
    	$monthName = $this->monthNames[$month - 1];
    	
		
    	$prev = $this->adjustDate($month - 1, $year);
    	$next = $this->adjustDate($month + 1, $year);
    	
    	if ($showYear == 1)
    	{
			// limited to 2 years either way to mitigate runaway bots
    	    $prevMonth = ($year> (date('Y')-2)) ? $this->getCalendarLink($prev[0], $prev[1]) : "";
    	    $nextMonth = ($year < (date('Y')+2)) ? $this->getCalendarLink($next[0], $next[1]) : "";
    	}
    	else
    	{
    	    $prevMonth = "";
    	    $nextMonth = "";
    	}
    	
    	$header = $monthName . (($showYear > 0) ? " " . $year : "");
    	
    	$s .= "<!-- ISEARCH_END_FOLLOW -->\n<table class=\"calendar\">\n";
    	$s .= "<tr class=\"monthNavigation\">\n";
    	$s .= " <thead><th>" . (($prevMonth == "") ? "<!-- blank-->" : "<a href=\"$prevMonth\" title = \"Click here to go to previous month\" rel=\"nofollow\">&#8592;</a>")  . "</th>\n";
    	$s .= "<th colspan=\"5\" class=\"monthname\">".$header."</th>\n"; 
    	$s .= "<th>" . (($nextMonth == "") ? "<!-- blank-->" : "<a href=\"$nextMonth\" title = \"Click here to go to following month\"  rel=\"nofollow\">&#8594;</a>")  . "</th>\n";
    	$s .= "</tr>\n";
    	
    	$s .= "<tr class=\"dayNames\">\n";
    	$s .= "<th>" . $this->dayNames[($this->startDay)%7] . "</th>\n";
    	$s .= "<th>" . $this->dayNames[($this->startDay+1)%7] . "</th>\n";
    	$s .= "<th>" . $this->dayNames[($this->startDay+2)%7] . "</th>\n";
    	$s .= "<th>" . $this->dayNames[($this->startDay+3)%7] . "</th>\n";
    	$s .= "<th>" . $this->dayNames[($this->startDay+4)%7] . "</th>\n";
    	$s .= "<th>" . $this->dayNames[($this->startDay+5)%7] . "</th>\n";
    	$s .= "<th>" . $this->dayNames[($this->startDay+6)%7] . "</th>\n";
    	$s .= "</tr></thead>\n";
    	
    	// We need to work out what date to start at so that the first appears in the correct column
    	$d = $this->startDay + 1 - $first;
    	while ($d > 1)
    	{
    	    $d -= 7;
    	}

        // Make sure we know when today is, so that we can use a different CSS style
        $today = getdate(time());
    	
    	while ($d <= $daysInMonth)
    	{
    	    $s .= "<tr>\n";       
    	    
    	    for ($i = 0; $i < 7; $i++)
    	    {
        	    $class = ($year == $today["year"] && $month == $today["mon"] && $d == $today["mday"]) ? " class=\"calendarToday\" " : "";
    	        $s .= "<td".$class.">";       
    	        if ($d > 0 && $d <= $daysInMonth)
    	        {
    	            $link = $this->getDateLink($d, $month, $year);
    	            $s .= $link; 
    	        }
    	        else
    	        {
    	            $s .= "<!-- blank-->";
    	        }
      	        $s .= "</td>\n";       
        	    $d++;
    	    }
    	    $s .= "</tr>\n";    
    	}
    	
    	$s .= "</table>\n<!-- ISEARCH_BEGIN_FOLLOW -->\n";
    	
    	return $s;  	
    }
    
    
    /*
        Generate the HTML for a given year
    */
    function getYearHTML($year)
    {
        $s = "";
    	$prev = $this->getCalendarLink(0, $year - 1);
    	$next = $this->getCalendarLink(0, $year + 1);
        
        $s .= "<table class=\"year\">\n";
        $s .= "<tr>";
    	$s .= "<td class=\"calendarHeader\" align=\"center\"  align=\"left\">" . (($prev == "") ? "<!-- blank-->" : "<a href=\"$prev\" rel=\"nofollow\">&laquo;</a>")  . "</td>\n";
        $s .= "<td class=\"calendarHeader\"  align=\"center\"><strong>" . (($this->startMonth > 1) ? $year . " - " . ($year + 1) : $year) ."</strong></td>\n";
    	$s .= "<td class=\"calendarHeader\" align=\"center\"  align=\"right\">" . (($next == "") ? "<!-- blank-->" : "<a href=\"$next\" rel=\"nofollow\">&raquo;</a>")  . "</td>\n";
        $s .= "</tr>\n";
        $s .= "<tr>";
        $s .= "<td class=\"month1\">" . $this->getMonthHTML(0 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"month2\">" . $this->getMonthHTML(1 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"month3\">" . $this->getMonthHTML(2 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "</tr>\n";
        $s .= "<tr>\n";
        $s .= "<td class=\"month4\">" . $this->getMonthHTML(3 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"month5\">" . $this->getMonthHTML(4 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"month6\">" . $this->getMonthHTML(5 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "</tr>\n";
        $s .= "<tr>\n";
        $s .= "<td class=\"month7\">" . $this->getMonthHTML(6 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"month8\">" . $this->getMonthHTML(7 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"month9\">" . $this->getMonthHTML(8 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "</tr>\n";
        $s .= "<tr>\n";
        $s .= "<td class=\"month10\">" . $this->getMonthHTML(9 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"month11\">" . $this->getMonthHTML(10 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "<td class=\"month12\">" . $this->getMonthHTML(11 + $this->startMonth, $year, 0) ."</td>\n";
        $s .= "</tr>\n";
        $s .= "</table>";
        
        return $s;
    }

    /*
        Adjust dates to allow months > 12 and < 0. Just adjust the years appropriately.
        e.g. Month 14 of the year 2001 is actually month 2 of year 2002.
    */
    function adjustDate($month, $year)
    {
        $a = array();  
        $a[0] = $month;
        $a[1] = $year;
        
        while ($a[0] > 12)
        {
            $a[0] -= 12;
            $a[1]++;
        }
        
        while ($a[0] <= 0)
        {
            $a[0] += 12;
            $a[1]--;
        }
        
        return $a;
    }

    /* 
        The start day of the week. This is the day that appears in the first column
        of the calendar. Sunday = 0.
    */
    var $startDay = 0;

    /* 
        The start month of the year. This is the month that appears in the first slot
        of the calendar in the year view. January = 1.
    */
    var $startMonth = 1;

    /*
        The labels to display for the days of the week. The first entry in this array
        represents Sunday.
    */
    var $dayNames = array("S", "M", "T", "W", "T", "F", "S");
    
    /*
        The labels to display for the months of the year. The first entry in this array
        represents January.
    */
    var $monthNames = array("January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December");
                            
                            
    /*
        The number of days in each month. You're unlikely to want to change this...
        The first entry in this array represents January.
    */
    var $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    
}
/* this stuff to go on actuall page and adjusted accordingly

class DynCalendar extends Calendar 
{
    function getCalendarLink($month, $year)
    {
        // Redisplay the current page, but with some parameters
        // to set the new month and year
        $s = getenv('SCRIPT_NAME');
        return "$s?month=$month&year=$year";
    }
	
	function getDateLink($day, $month, $year)
    {
        // Only link the first day of every month 
        $link = "";
        if ($day == 1)
        {
            $link = "first.php";
        }
        return $link;
    }
}
*/

if(!function_exists("showDuration")) {
	function showDuration($startdatetime, $enddatetime) {
	$duration = strtotime($enddatetime) - strtotime($startdatetime);
	$hrs = floor($duration/3600);
	$mins = (ceil($duration/3600)-(($duration/3600)))*60;
	$hrs .= $mins > 0 ? "hr ".$mins."mins"   : " hours";
	switch($duration) {
		case $duration>31449600 : return round($duration/31449600)." years";
		case $duration>604800 : return round($duration/604800)." weeks";
		case $duration>86400 : return round($duration/86400)." days";
		case $duration>3600 : return $hrs;
		case $duration>60 : return round($duration/60)." minutes";
		default : return "";
	}	
}
}

if(!function_exists("addEventGroup")) {
function addEventGroup($eventtitle="", $eventdetails = "", $categoryID = 0, $imageURL = "", $resourceID = "", $usertypeID = 0, $custom1 = "", $custom2 = "", $statusID = 1, $createdbyID = 0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if(strlen($eventtitle)>0) {
		$insert = "INSERT INTO eventgroup (eventtitle, eventdetails, categoryID, customvalue1, customvalue2, usertypeID, imageURL, createdbyID, createddatetime, statusID, resourceID) VALUES (".GetSQLValueString($eventtitle, "text").",".
						   GetSQLValueString($eventdetails, "text").",".
						   GetSQLValueString($categoryID, "int").",".
						   GetSQLValueString($custom1, "text").",".
						   GetSQLValueString($custom2, "text").",".
						   GetSQLValueString($usertypeID, "int").",".
						   GetSQLValueString($imageURL, "text").",".
						   GetSQLValueString($createdbyID, "int").",NOW(),".
						   GetSQLValueString($statusID, "int").",".
						   GetSQLValueString($resourceID, "int").")";
	
	  mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
	  return mysql_insert_id();
	} else {
		return false;
	}
}
}

if(!function_exists("addEvent")) {
	function addEvent($eventgroupID, $startdatetime="", $enddatetime="", $locationID = 0, $createdbyID=0, $allday = false,$recurringend="", $recurringinterval="WEEKS", $recurringmultiple=1,  $followonfromID="", $nthdow="", $registration = 0, $registrationURL = "", $registrationtext = "", $registrationmax=0,$registrationteam=0, $userID="",$params = array()) {
	if(isset($_SESSION['debug'])) $_SESSION['log'] .= "Function addEvent:<br>";
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$allday = $allday ? 1 : 0;
	$startdatetime = $startdatetime !="" ? $startdatetime : date('Y-m-d H:i:s');
	$enddatetime = ($enddatetime!="" && $enddatetime > $startdatetime)  ? $enddatetime : $startdatetime;
	$repeats  = ($recurringend!="") ? true : false;
	
	$recurringend = ($recurringend!="") ? date('Y-m-d 23:59:59',strtotime($recurringend)) : $startdatetime;
	$recurringinterval = trim($recurringinterval) !="" ?  strtoupper($recurringinterval) : "WEEKS";
	$recurringmultiple = $recurringmultiple>0  ? intval($recurringmultiple) : 1 ;
	$firsteventID = "";
	$max=0; //prevent runaway
	
	while($startdatetime<=$recurringend) { // loop
		$max++; if($max>500) {
			echo "Maximum reached: ".date('d M Y', strtotime($startdatetime)) ." - ".date('d M Y', strtotime($recurringend));
			die();
		}
		$insert = "INSERT INTO event (eventgroupID, startdatetime, enddatetime, statusID, eventlocationID, firsteventID, allday, followonfromID, registration, registrationURL, registrationtext, registrationmax,registrationteam,userID,createdbyID, createddatetime) VALUES (".GetSQLValueString($eventgroupID,"int").",".GetSQLValueString($startdatetime,"date").",".GetSQLValueString($enddatetime,"date").",1,".GetSQLValueString($locationID,"int").",".GetSQLValueString($firsteventID,"int").",".$allday.",".GetSQLValueString($followonfromID,"int").",".GetSQLValueString($registration,"int").",".GetSQLValueString($registrationURL,"text").",".GetSQLValueString($registrationtext,"text").",".GetSQLValueString($registrationmax,"int").",".GetSQLValueString($registrationteam,"int").",".GetSQLValueString($userID,"int").",".GetSQLValueString($createdbyID,"int").",NOW())";
		if(isset($_SESSION['debug'])) $_SESSION['log'] .= $insert."<br>";
		/* adding multiple of days does not add times, so these need to be appended later... */
		$starttime = date('H:i:s', strtotime($startdatetime));
		$endtime = date('H:i:s', strtotime($enddatetime));
		
		mysql_query($insert, $aquiescedb) or die(mysql_error());
		if($repeats && $firsteventID == "") {
			$firsteventID = mysql_insert_id();
			$update = "UPDATE event SET firsteventID = ".$firsteventID." WHERE ID = ".$firsteventID;
			mysql_query($update, $aquiescedb) or die(mysql_error());
		}
		
		if($recurringinterval=="NTHDOW") {
			$monthyear = date('F Y', strtotime($startdatetime." + ".$recurringmultiple." MONTHS"));
			if(phpversion()<5.3) {
				$words= explode(" ",strtoupper($nthdow));
				$datedescription = ordinalDate($words[0], $words[1], $monthyear);
			} else {
				$datedescription = $nthdow." of ".$monthyear;
			}
			$startdatetime=date('Y-m-d',strtotime($datedescription))." ".$starttime;
			$enddatetime=date('Y-m-d',strtotime($datedescription))." ".$endtime;
		} else {
			$startdatetime=date('Y-m-d',strtotime($startdatetime." + ".$recurringmultiple." ".$recurringinterval))." ".$starttime;
			$enddatetime=date('Y-m-d',strtotime($enddatetime." + ".$recurringmultiple." ".$recurringinterval))." ".$endtime;
		}		
	}
	return $firsteventID;
}	
}

if(!function_exists("attendEvent")) {
	function attendEvent($eventID = 0, $userID = 0, $statusID = 1, $createdbyID = 0) {
	if($eventID >0 && $userID >0) {
		global $database_aquiescedb, $aquiescedb;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$insert = "INSERT INTO eventattend (eventID, userID, statusID, createdbyID, createddatetime) VALUES (".intval($eventID).",".intval($userID).",".intval($statusID).",".intval($createdbyID).",NOW())";
		mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
	 	return mysql_insert_id();
	} else {
		return false;
	}
	}
}

if(!function_exists("findNextAvailable")) {
	function findNextAvailable($duration = "1 HOUR", $resourceID = 0, $startsearch = "", $endsearch = "") {
	$startsearch = ($startsearch == "") ? date('Y-m-d H:i:s') : $startsearch;
	$endsearch = ($endsearch == "") ? date('Y-m-d H:i:s', strtotime($startsearch ." + 1 MONTH")) : $endsearch;
	global $database_aquiescedb, $aquiescedb, $eventPrefs;
	$adjustedendtime = date('H:i:s',strtotime($eventPrefs['dayendtime']." - ".$duration));
	// first check free at start day time - to do
	
	// then check all event ends to see if time after
	
	$select = "SELECT a.enddatetime AS free_after
FROM event a LEFT JOIN eventgroup ag ON (a.eventgroupID = ag.ID) WHERE a.statusID = 1 AND DATE(a.enddatetime) > ".GetSQLValueString($startsearch, "date")." AND DATE(a.enddatetime) < ".GetSQLValueString($endsearch, "date")." AND TIME(a.enddatetime) <= '".$adjustedendtime."' AND (ag.resourceID = 0 OR ag.resourceID = ".intval($resourceID).") AND  NOT EXISTS (
  SELECT 1
  FROM event b LEFT JOIN eventgroup bg ON (b.eventgroupID = bg.ID)  
  WHERE b.statusID = 1 AND (bg.resourceID = 0 OR bg.resourceID = ".intval($resourceID).") AND (b.startdatetime BETWEEN a.enddatetime AND DATE_ADD(a.enddatetime , INTERVAL ".$duration.") OR  (b.startdatetime < a.enddatetime  AND b.enddatetime > a.enddatetime))
) 
 ORDER BY a.enddatetime LIMIT 1";

// OR (a.enddatetime BETWEEN b.startdatetime   AND b.enddatetime)
//die($select);

//AND a.enddatetime BETWEEN ".GetSQLValueString($startsearch, "date")." AND ".GetSQLValueString($endsearch, "date").";";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	if(mysql_num_rows($result)>0) {	
		$row = mysql_fetch_assoc($result); 
		mysql_free_result($result);
		return $row['free_after'];
	} else {
		mysql_free_result($result);
		return false;
	}
	}
}

if(!function_exists("checkOverlap")) {
function checkOverlap($startdatetime = "", $enddatetime = "", $resourceID = 0) {
	global $database_aquiescedb, $aquiescedb;
	$select = "SELECT event.ID FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) WHERE event.statusID = 1 AND  eventgroup.statusID = 1 AND 
	(eventgroup.resourceID IS NULL OR eventgroup.resourceID = 0 OR eventgroup.resourceID = ".intval($resourceID)." )  AND (
	(startdatetime >= ".GetSQLValueString($startdatetime, "date")." AND startdatetime < ".GetSQLValueString($enddatetime, "date").") OR
	(enddatetime > ".GetSQLValueString($startdatetime, "date")." AND enddatetime <= ".GetSQLValueString($enddatetime, "date").") OR
	(startdatetime < ".GetSQLValueString($startdatetime, "date")." AND enddatetime > ".GetSQLValueString($enddatetime, "date")."))";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	if(mysql_num_rows($result)>0) {
		return $row['ID'];
	} else {
		return false;
	}
}
}

if(!function_exists("deleteAllEvents")) {
	function deleteAllEvents() {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);	
	$delete = "DELETE FROM event";
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$delete = "DELETE FROM eventgroup";	
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$delete = "DELETE FROM eventattend";
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$delete = "DELETE FROM eventregistration";
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$delete = "DELETE FROM eventregstarttime";
	mysql_query($delete, $aquiescedb) or die(mysql_error());
	$delete = "DELETE FROM reminder WHERE eventID IS NOT NULL";
	mysql_query($delete, $aquiescedb) or die(mysql_error());
}
}

if(!function_exists("deleteEvent")) {
	function deleteEvent($eventID, $deletegroup = true) {
		if(isset($eventID) && intval($eventID)>0) {
			global $database_aquiescedb, $aquiescedb;
			mysql_select_db($database_aquiescedb, $aquiescedb);	
			$select = "SELECT relatedevents.ID FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN event AS relatedevents ON (relatedevents.eventgroupID = eventgroup.ID)  WHERE event.ID = ".intval($eventID);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
			if(mysql_num_rows($result)==1) { // only 1 event in group so delete group
				$deletegroup = true;
			}			
			$delete = "DELETE FROM eventattend WHERE eventID = ".intval($eventID);
			mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
			$delete = "DELETE FROM reminder WHERE eventID = ".intval($eventID);
			mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
			$delete = "DELETE FROM eventregistration WHERE eventID = ".intval($eventID);
			mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
			$delete = "DELETE FROM eventregstarttime WHERE eventID = ".intval($eventID);
			mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
			if($deletegroup) {	
				$delete = "DELETE event, eventgroup FROM event LEFT JOIN  eventgroup ON (event.eventgroupID = eventgroup.ID) WHERE event.ID = ".intval($eventID);
			} else {
				$delete = "DELETE  FROM event WHERE event.ID = ".intval($eventID);
			}
			mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);	
			return true;
		}
		return false;
}
}

if(!function_exists("ordinalDate")) {
	function ordinalDate($recurrOrdinal, $dayOfWeek, $monthYear)    {
	// e.g 3rd Tuesday of March 2015
	// for versions of PHP < 5.3
	// $recurrOrdinal must be uppercase
    $firstDate = date("j", strtotime($dayOfWeek . " " . $monthYear));
    if ($recurrOrdinal == "FIRST")
    	$computed = $firstDate; 
    elseif ($recurrOrdinal == "SECOND")
    	$computed = $firstDate + 7; 
    elseif ($recurrOrdinal == "THIRD")
    	$computed = $firstDate + 14; 
    elseif ($recurrOrdinal == "FOUTH")
    	$computed = $firstDate + 21; 
    elseif ($recurrOrdinal == "LAST")   {
    	if ( ($firstDate + 28) <= date("t", strtotime($monthYear)) )
        	$computed = $firstDate + 28; 
    	else
        	$computed = $firstDate + 21; 
    }
    return date("Y-m-d", strtotime($computed . " " . $monthYear) );
}
}


if(!function_exists("getDiaryDay")) {
	function getDiaryDay($date, $interval_time = 3600, $starttime = '08:00:00', $endtime = '19:00:00', $categoryID = 0, $resourceID = 0, $direction="vertical",$interval_pixels = 20) { // period in seconds default 1 hour
	
	
	global $database_aquiescedb, $aquiescedb, $eventselect, $nextavailable;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$resourceselect = "SELECT resourcename FROM eventresource  WHERE eventresource.ID = ".intval( $resourceID);
	$resourceresult = mysql_query($resourceselect, $aquiescedb) or die(mysql_error());
	$resource = mysql_fetch_assoc($resourceresult);
	
	$html = "<div class=\"diary-time-list resource".$resourceID." category".$categoryID."\">";
	$html .= "<div class=\"event-resource-name\">".$resource['resourcename']."</div>";
	for($time = strtotime($date." ".$starttime); $time <= strtotime($date." ".$endtime); $time += $interval_time) {
		$html .= "<div class=\"period minutes".date('i', $time);
		$html .= (isset($_GET['resourceID']) && $_GET['resourceID'] == $resourceID && date('H:i', strtotime($nextavailable)) == date('H:i', $time)) ? " nextavailable " : "";
		$style =  ($direction=="vertical") ? "height:".$interval_pixels."px; max-height:".$interval_pixels."px" : "width:".$interval_pixels."px; max-width:".$interval_pixels."px;";
		$html .= "\" style=\"".$style."\" data-resourceID=\"".$resourceID."\" data-categoryID=\"".$categoryID."\" data-hours=\"".date('H', $time)."\" data-minutes=\"".date('i', $time)."\"><span class=\"time\">".date('H:i', $time)."</span>";
		
		$eventselect = "SELECT eventgroup.eventtitle, eventgroup.eventdetails, eventgroup.categoryID, event.startdatetime, event.enddatetime, event.eventgroupID,eventcategory.title AS category,eventcategory.priority, eventcategory.colour,  event.ID AS thiseventID, reminder.firstsend, reminder.lastsent, followon.ID AS followonID, eventgroup.resourceID, eventresource.resourcename, (SELECT  GROUP_CONCAT(CONCAT('<span class=\'warning',warning,'\'>',firstname,' ',surname,'</span>') SEPARATOR ', ') FROM eventattend LEFT JOIN users ON (eventattend.userID = users.ID) WHERE eventattend.eventID = thiseventID GROUP BY eventattend.eventID) AS clients  FROM event LEFT JOIN event AS followon ON (followon.followonfromID = event.ID)  LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN eventcategory ON (eventgroup.categoryID = eventcategory.ID) LEFT JOIN reminder ON (reminder.eventID = event.ID) LEFT JOIN eventresource ON (eventgroup.resourceID = eventresource.ID) WHERE event.statusID = 1 AND (".intval($resourceID)." = 0  OR ".intval($resourceID)." = eventgroup.resourceID OR eventgroup.resourceID=0 OR eventgroup.resourceID IS NULL) AND event.startdatetime >= '".date('Y-m-d H:i:s', $time)."' AND event.startdatetime < '".date('Y-m-d H:i:s', ($time + $interval_time))."' GROUP BY event.ID";		
		$result = mysql_query($eventselect, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($result)>0) {
			$left = 0;			
		 	while($event = mysql_fetch_assoc($result)) {
				$duration = strtotime($event['enddatetime']) - strtotime($event['startdatetime']);
				
				if($direction=="vertical") {
					$height = (round($duration/$interval_time*$interval_pixels,1)-1)."px";
					$width ="auto";
				} else {
					$width = (round($duration/$interval_time*$interval_pixels,1)-1)."px";
					$height ="auto";
				}
				$html .= "<a  data-toggle=\"tooltip\" data-original-title=\"Click for more details or to edit\"  style=\"top:0; left: ".$left."em; height: ".$height."; min-height: ".$height."; width:".$width."; min-width:".$width."; z-index: ".$event['priority']."; ";
				if(isset($event['colour'])) {
					if(strpos($event['colour'], "#")!==false) {
						list($r, $g, $b) = sscanf($event['colour'], "#%02x%02x%02x");
						$event['colour'] = "rgba(".$r.",".$g.",".$b.",.7)";
						
					} else if(strpos($event['colour'] ,"rgb(")!==false) {
						$color = str_replace(array('rgb(', ')', ' '), '', $color);
						$arr = explode(',', $color);
						$event['colour'] = "rgba(".$color[0].",".$color[1].",".$color[2].",.7)";
					}
					$html .= " background-color: ".$event['colour']."; ";
					$html .= " border: 1px solid ".str_replace(".7","1",$event['colour'])."; ";
				}
				$html .= "  \"  data-eventID=\"".$event['thiseventID']."\"";
				$html .= "class=\"event category".$event['categoryID']." followon".$event['followonID'];
				$html .= ($event['categoryID']>0) ? " popup wow zoomIn\" href=\"/calendar/ajax/update.ajax.php?eventID=".$event['thiseventID']."&openerURL=".urlencode($_SERVER['REQUEST_URI'])."&token=".md5(PRIVATE_KEY.$event['thiseventID'])."\"" : "\" href=\"#\"";				
				$html .= ">";
				
				$html .= isset($event['category']) ? "<span class=\"categoryname\">".$event['category']." - </span>".$event['eventtitle'] : $event['eventtitle'];
				$html .= isset($event['resourcename']) ? " <span class=\"resourcename\">[".$event['resourcename']."]</span>" : "";
				//$html .= $duration.":".$interval_time.":".$interval_pixels.":".": ".$event['clients'];
				$html .= "<div class=\"eventdetails\">".$event['eventdetails']."</div>";
				$html .= (isset($_SESSION['debug'])) ? "ADMIN: eventID=".$event['thiseventID']." eventgroupID=".$event['eventgroupID']." resourceID = ".$event['resourceID']." followonID= ".$event['followonID'] : "";
				$class = isset($event['lastsent']) ? "reminder sent"  : "reminder";
				$html .= isset($event['firstsend']) ? "<span class=\"".$class."\">Reminder: ".$event['firstsend']."</span>" : "";
				$html .= "</a>";
				if($direction=="vertical") {
					$left ++;
				}
		 	}
		}		
		$html .="</div>";
		
		
	
		
	} // end for
	$html .="</div>";
	mysql_free_result($result);
	return $html;
}
}

if(!function_exists("sendEventEmails")) {
	function sendEventEmails($eventID) {
		global $database_aquiescedb, $aquiescedb, $eventPrefs;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT event.ID AS eventID, event.startdatetime, event.userID, event.eventlocationID, eventgroup.eventtitle, users.firstname, users.email, locationuser.ID AS locationuserID, location.locationname, location.locationemail, locationuser.email AS contactemail, locationuser.firstname AS locationfirstname FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN users ON (event.userID = users.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) LEFT JOIN users AS locationuser ON (locationuser.defaultaddressID = location.ID) WHERE event.ID = ".intval($eventID);
		$eventresult = mysql_query($select, $aquiescedb) or die(mysql_error());
		$event = mysql_fetch_assoc($eventresult);
		if(isset($eventPrefs['addeventuseremailtemplateID']) && $eventPrefs['addeventuseremailtemplateID']>0 && isset($event['userID']) && $event['userID']>0) {
			$to = isset($event['contactemail']) ? $event['contactemail'] : $event['locationemail'];	
			$merge = array("eventID" => $event['eventID'],"firstname" => $event['firstname'],"locationname"=>$event['locationname'], "eventname"=>$event['eventtitle'], "eventdate"=>date('l jS F Y', strtotime($event['startdatetime'])),  "eventtime"=>date('g:ia', strtotime($event['startdatetime'])));


			$result = sendMail($to,"","","","","","",true,"","","",$eventPrefs['addeventuseremailtemplateID'],false,$merge);
			
		}
		
		if(isset($eventPrefs['addeventlocationemailtemplateID']) && $eventPrefs['addeventlocationemailtemplateID']>0 && isset($event['locationuserID']) && $event['locationuserID']>0) {
			$to = isset($event['contactemail']) ? $event['contactemail'] : $event['locationemail'];	
			$merge = array("eventID" => $event['eventID'],"firstname" => $event['locationfirstname'],"eventname"=>$event['eventtitle'],"locationname"=>$event['locationname'],  "eventdate"=>date('l jS F Y', strtotime($event['startdatetime'])),  "eventtime"=>date('g:ia', strtotime($event['startdatetime'])));


			sendMail($to,"","","","","","",true,"","","",$eventPrefs['addeventuseremailtemplateID'],false,$merge);
		}
		
		//
	}
}

if(!function_exists("sendCalendarReminder")) {
function sendCalendarReminder() {
	
	global $database_aquiescedb, $aquiescedb, $eventPrefs;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	if($eventPrefs['remindereventuseremailtemplateID']>0 && $eventPrefs['remindereventuseremailhours']!=0) {
		// REMINDERS TO USERS
		$operator = $eventPrefs['remindereventuseremailhours']>0 ? "+" : "-";
		
		// if pre-reminder only do future events
		$where = $eventPrefs['remindereventuseremailhours']>0 ? " AND startdatetime >= NOW() " : "";
		
		
		$triggerdate = date('Y-m-d H:i:s', strtotime($operator." ".abs($eventPrefs['remindereventuseremailhours'])." HOURS"));
		$select = "SELECT event.ID AS eventID, event.startdatetime, event.userID, event.eventlocationID, eventgroup.eventtitle, users.firstname, users.email, locationuser.ID AS locationuserID, location.locationname, location.locationemail,locationuser.email AS contactemail, locationuser.firstname AS locationfirstname FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN users ON (event.userID = users.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) LEFT JOIN users AS locationuser ON (locationuser.defaultaddressID = location.ID) WHERE  startdatetime < '".$triggerdate."' AND event.userID >0  AND remindereventuseremailsent IS NULL ".$where." ORDER BY event.startdatetime  LIMIT 1";		
		$eventresult = mysql_query($select, $aquiescedb) or die(mysql_error());
		if(mysql_num_rows($eventresult)>0) {
			while($event = mysql_fetch_assoc($eventresult)) {
				$to = isset($event['contactemail']) ? $event['contactemail'] : $event['locationemail'];	
				$merge = array("eventID" => $event['eventID'],"firstname" => $event['firstname'],"locationname"=>$event['locationname'], "eventname"=>$event['eventtitle'], "eventdate"=>date('l jS F Y', strtotime($event['startdatetime'])),  "eventtime"=>date('g:ia', strtotime($event['startdatetime'])), "userID"=>$event['userID'], "usertoken"=>md5(PRIVATE_KEY.$event['userID']));
				$result = sendMail($to,"","","","","","",true,"","","",$eventPrefs['remindereventuseremailtemplateID'],false,$merge);
				$update = "UPDATE event SET remindereventuseremailsent = NOW() WHERE ID = ".intval($event['eventID']);
				mysql_query($update, $aquiescedb) or die(mysql_error());
			}// end while events		
		} // end events		
	}	// is user reminder
	
	
	if($eventPrefs['remindereventlocationemailtemplateID']>0 && $eventPrefs['remindereventlocationemailhours']!=0) {
	// REMINDERS TO LOCATIONS
	
		$operator = $eventPrefs['remindereventlocationemailhours']>0 ? "+" : "-";
		// if pre-reminder only do future events
		$where = $eventPrefs['remindereventlocationemailhours']>0 ? " AND startdatetime >= NOW() " : "";
		
		$triggerdate = date('Y-m-d H:i:s', strtotime($operator." ".abs($eventPrefs['remindereventlocationemailhours'])." HOURS"));
		$select = "SELECT event.ID AS eventID, event.startdatetime, event.userID, event.eventlocationID, eventgroup.eventtitle,eventgroup.eventfee,eventgroup.venuefee, users.firstname, users.email, locationuser.ID AS locationuserID, location.locationname, location.locationemail,locationuser.email AS contactemail, locationuser.firstname AS locationfirstname FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN users ON (event.userID = users.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) LEFT JOIN users AS locationuser ON (locationuser.defaultaddressID = location.ID) WHERE startdatetime < '".$triggerdate."' AND event.eventlocationID >0  AND remindereventlocationemailsent IS NULL ".$where." ORDER BY event.startdatetime LIMIT 1";		
		$eventresult = mysql_query($select, $aquiescedb) or die(mysql_error());
		
		if(mysql_num_rows($eventresult)>0) {
			while($event = mysql_fetch_assoc($eventresult)) {
				$to = isset($event['contactemail']) ? $event['contactemail'] : $event['locationemail'];							
				
				$merge = array("eventID" => $event['eventID'],"firstname" => $event['locationfirstname'],"locationname"=>$event['locationname'], "eventname"=>$event['eventtitle'],"eventfee"=>$event['eventfee'], "venuefee"=>$event['venuefee'], "eventdate"=>date('l jS F Y', strtotime($event['startdatetime'])),  "eventtime"=>date('g:ia', strtotime($event['startdatetime'])), "userID"=>$event['locationuserID'], "usertoken"=>md5(PRIVATE_KEY.$event['locationuserID']));
				$result = sendMail($to,"","","","","","",true,"","","",$eventPrefs['remindereventlocationemailtemplateID'],false,$merge);
				$update = "UPDATE event SET remindereventlocationemailsent = NOW() WHERE ID = ".intval($event['eventID']);
				mysql_query($update, $aquiescedb) or die(mysql_error());
			}// end while events		
		} // end events		
	}	// is location reminder
	
	
	
	
	if($eventPrefs['remindereventlocationemail2templateID']>0 && $eventPrefs['remindereventlocationemail2hours']!=0) {
	// REMINDERS TO LOCATIONS
	
		$operator = $eventPrefs['remindereventlocationemail2hours']>0 ? "+" : "-";
		// if pre-reminder only do future events
		$where = $eventPrefs['remindereventlocationemail2hours']>0 ? " AND startdatetime >= NOW() " : "";
		
		$triggerdate = date('Y-m-d H:i:s', strtotime($operator." ".abs($eventPrefs['remindereventlocationemail2hours'])." HOURS"));
		$select = "SELECT event.ID AS eventID, event.startdatetime, event.userID, event.eventlocationID, eventgroup.eventtitle,  eventgroup.eventfee,eventgroup.venuefee,users.firstname, users.email, locationuser.ID AS locationuserID, location.locationname, location.locationemail,locationuser.email AS contactemail, locationuser.firstname AS locationfirstname FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN users ON (event.userID = users.ID) LEFT JOIN location ON (event.eventlocationID = location.ID) LEFT JOIN users AS locationuser ON (locationuser.defaultaddressID = location.ID) WHERE startdatetime < '".$triggerdate."' AND event.eventlocationID >0  AND remindereventlocationemail2sent IS NULL ".$where." ORDER BY event.startdatetime LIMIT 1";		
		$eventresult = mysql_query($select, $aquiescedb) or die(mysql_error());
		
		if(mysql_num_rows($eventresult)>0) {
			while($event = mysql_fetch_assoc($eventresult)) {
				$to = isset($event['contactemail']) ? $event['contactemail'] : $event['locationemail'];	
				$bcc = "";			
				if(defined("IS_CONNECT_ENT")) {
					$bcc = "marcus.mcneilly@connect-ent.org,  accounts@connect-ct.org.uk";
				}
				$merge = array("eventID" => $event['eventID'],"firstname" => $event['locationfirstname'],"locationname"=>$event['locationname'], "eventname"=>$event['eventtitle'], "eventfee"=>$event['eventfee'], "venuefee"=>$event['venuefee'], "eventdate"=>date('l jS F Y', strtotime($event['startdatetime'])),  "eventtime"=>date('g:ia', strtotime($event['startdatetime'])), "userID"=>$event['locationuserID'], "usertoken"=>md5(PRIVATE_KEY.$event['locationuserID']));
				$result = sendMail($to,"","","","","","",true,"",$bcc,"",$eventPrefs['remindereventlocationemail2templateID'],false,$merge);
				$update = "UPDATE event SET remindereventlocationemail2sent = NOW() WHERE ID = ".intval($event['eventID']);
				mysql_query($update, $aquiescedb) or die(mysql_error());
			}// end while events		
		} // end events		
	}	// is location reminder 2
} // end func
}
?>