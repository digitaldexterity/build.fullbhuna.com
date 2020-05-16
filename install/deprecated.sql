-- --------------------------------------------------------

--
-- Table structure for table `advertisment`
--

DROP TABLE IF EXISTS `advertisment`;
CREATE TABLE IF NOT EXISTS `advertisment` (
  `ID` int(11) NOT NULL auto_increment,
  `adname` varchar(50) NOT NULL,
  `addescription` text,
  `startdatetime` datetime default NULL,
  `enddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `regionID` int(11) NOT NULL default '0',
  `directoryCatID` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `modifieddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  `imageURL` varchar(255) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


-- 
-- Table structure for table `blog`
-- 

DROP TABLE IF EXISTS `blog`;
CREATE TABLE IF NOT EXISTS `blog` (
  `ID` int(11) NOT NULL auto_increment,
  `longID` varchar(100)  default NULL,
  `regionID` int(11) NOT NULL default '1',
  `blogname` varchar(100) NOT NULL default '',
  `accesslevel` tinyint(4) NOT NULL default '0',
  `metadescription` text,
  `metakeywords` text,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `blog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `blogentry`
-- 

DROP TABLE IF EXISTS `blogentry`;
CREATE TABLE IF NOT EXISTS `blogentry` (
	`ID` int(11) NOT NULL auto_increment,
	`longID` varchar(100)  default NULL,
	`blogID` int(11) NOT NULL default '1',
	`regionID` int(11) NOT NULL default '1',
	`blogentrytitle` varchar(255) default NULL,
	`blogtext` mediumtext NOT NULL,
	`metadescription` text,
	`metakeywords` text,
	`imageURL` varchar(100) default NULL,
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`postdatetime` datetime default NULL,
	`groupemail` tinyint(4) NOT NULL default '0',
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `blogentry`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `blogcomments`
-- 


DROP TABLE IF EXISTS `blogcomments`;
CREATE TABLE IF NOT EXISTS `blogcomments` (
  `ID` int(11) NOT NULL auto_increment,
  `blogentryID` int(11) NOT NULL,
  `comment` text NOT NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `blogcomments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `blog`
-- 


DROP TABLE IF EXISTS `blogprefs`;
CREATE TABLE IF NOT EXISTS `blogprefs` (
  `ID` int(11) NOT NULL auto_increment,
  `blogentriesperpage` tinyint(4) NOT NULL default '20',
  `rssfooter` text,
  `memberblog` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;



-- 
-- Dumping data for table `blogprefs`
-- 

INSERT INTO `blogprefs` (ID) VALUES (1);

-- --------------------------------------------------------




--
-- Table structure for table `bookingcategory`
--

DROP TABLE IF EXISTS `bookingcategory`;
CREATE TABLE IF NOT EXISTS `bookingcategory` (
  `ID` int(11) NOT NULL auto_increment,
  `description` varchar(30)  default NULL,
  `createdbyID` int(11) default NULL,
  `createddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bookingfeature`
--

DROP TABLE IF EXISTS `bookingfeature`;
CREATE TABLE IF NOT EXISTS `bookingfeature` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `featurename` varchar(50)  default NULL,
  `featuredetails` mediumtext,
  `categoryID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bookinginstance`
--

DROP TABLE IF EXISTS `bookinginstance`;
CREATE TABLE IF NOT EXISTS `bookinginstance` (
	`ID` int(11) NOT NULL auto_increment,
	`resourceID` int(11) NOT NULL default '0',
	`multiple` int(11) NOT NULL default '1',
	`bookedfor` varchar(30)  default NULL,
	`confirmed` tinyint(4) NOT NULL default '1',
	`paymentrequired` tinyint(4) NOT NULL default '0',
	`depositrequired` tinyint(4) NOT NULL default '0',
	`currency` char(3)  default NULL,
	`price` double NOT NULL default '0',
	`pricepaid` tinyint(4) NOT NULL default '0',
	`deposit` double NOT NULL default '0',
	`depositpaid` tinyint(4) NOT NULL default '0',
	`startdatetime` datetime NOT NULL default '0000-00-00 00:00:00',
	`enddatetime` datetime NOT NULL default '0000-00-00 00:00:00',
	`recurring` tinyint(4) NOT NULL default '0',
	`recurringID` int(11) default NULL,
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL default '0000-00-00 00:00:00',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bookingpricing`
--

DROP TABLE IF EXISTS `bookingpricing`;
CREATE TABLE IF NOT EXISTS `bookingpricing` (
  `ID` int(11) NOT NULL auto_increment,
  `resourceID` int(11) NOT NULL default '0',
  `price` double NOT NULL default '0',
  `pricehours` int(11) NOT NULL default '24',
  `deposit` double NOT NULL default '0',
  `currency` char(3)  default NULL,
  `default` tinyint(4) NOT NULL default '1',
  `details` text,
  `datestart` date default '0000-00-00',
  `dateend` date default '0000-00-00',
  `everyyear` tinyint(4) NOT NULL default '1',
  `allday` tinyint(4) NOT NULL default '1',
  `timestart` time default '00:00:00',
  `timeend` time default '00:00:00',
  `daysofweek` varchar(7)  default NULL,
  `createddatetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `createdbyID` int(11) NOT NULL default '0',
  `modifeddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bookingresource`
--

DROP TABLE IF EXISTS `bookingresource`;
CREATE TABLE IF NOT EXISTS `bookingresource` (
	`ID` int(11) NOT NULL auto_increment,
	`title` varchar(30)  default NULL,
	`description` mediumtext,
	`availablestart` time NOT NULL default '00:00:00',
	`availableend` time NOT NULL default '23:59:59',
	`availabledow` varchar(7)  default NULL,
	`availabletoID` tinyint(4) NOT NULL default '1',
	`locationID` int(11) default NULL,
	`categoryID` int(11) default NULL,
	`maxHours` int(11) default '24',
	`minNotice` int(11) NOT NULL default '0',
	`recurringallow` tinyint(4) NOT NULL default '0',
	`interval` tinyint(4) NOT NULL default '1',
	`notifyemail` varchar(100)  default NULL,
	`paymentrequired` tinyint(4) NOT NULL default '0',
	`depositrequired` tinyint(4) NOT NULL default '0',
	`imageURL` varchar(50)  default NULL,
	`featurenotes` text,
	`createdbyID` int(11) default NULL,
	`createddatetime` datetime default NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	`capacitystanding` int(11) default NULL,
	`capacitytheatre` int(11) default NULL,
	`capacityclassroom` int(11) default NULL,
	`capacityboardroom` int(11) default NULL,
	`capacitybanquet` int(11) default NULL,
	`paymentnotes` mediumtext,
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bookingresourcefeature`
--

DROP TABLE IF EXISTS `bookingresourcefeature`;
CREATE TABLE IF NOT EXISTS `bookingresourcefeature` (
  `ID` int(11) NOT NULL auto_increment,
  `resourceID` int(11) NOT NULL default '0',
  `featureID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `bookingprefs`
-- 

DROP TABLE IF EXISTS `bookingprefs`;
CREATE TABLE IF NOT EXISTS `bookingprefs` (
  `ID` int(11) NOT NULL auto_increment,
  `tentativeoverlap` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `bookingprefs`
-- 


INSERT INTO `bookingprefs` (ID) VALUES (1);


-- --------------------------------------------------------

-- 
-- Table structure for table `changerequest`
-- 

DROP TABLE IF EXISTS `changerequest`;
CREATE TABLE IF NOT EXISTS `changerequest` (
	`ID` int(11) NOT NULL auto_increment,
	`requesttypeID` tinyint(4) NOT NULL,
	`URL` varchar(255) default NULL,
	`pagetitle` varchar(255) default NULL,
	`requestdetails` text,
	`developernotes` text,
	`ip4address` varchar(15) NOT NULL,
	`hostsystem` varchar(255) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`createdbyID` int(11) NOT NULL default '0',
	`modifeddatetime` datetime default NULL,
	`modifiedbyID` int(11) default NULL,
	`statusID` tinyint(4) NOT NULL default '0',	
	PRIMARY KEY  (`ID`),
	KEY `statusID` (`statusID`),
	KEY `createdbyID` (`createdbyID`),
	KEY `modifiedbyID` (`modifiedbyID`),
	KEY `createddatetime` (`createddatetime`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

-- 
-- Table structure for table `faq`
-- 

DROP TABLE IF EXISTS `faq`;
CREATE TABLE IF NOT EXISTS `faq` (
  `ID` int(11) NOT NULL auto_increment,
  `categoryID` tinyint(4) default NULL,
  `question` text NOT NULL,
  `answer` text default NULL,
  `emailed` tinyint(4) NOT NULL  default '0',
  `referanswerID` int(11) default NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `faq`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `faqcategory`
-- 

DROP TABLE IF EXISTS `faqcategory`;
CREATE TABLE IF NOT EXISTS `faqcategory` (
  `ID` int(11) NOT NULL auto_increment,
  `categoryname` varchar(50) NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `faqcategory`
-- 



-- --------------------------------------------------------

-- 
-- Table structure for table `salutation`
-- 

DROP TABLE IF EXISTS `salutation`;
CREATE TABLE IF NOT EXISTS `salutation` (
  `ID` tinyint(4) NOT NULL default '0',
  `description` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `salutation`
-- 



-- --------------------------------------------------------



-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE IF NOT EXISTS `testimonials` (
  `ID` int(11) NOT NULL auto_increment,
  `testimonialtext` text,
  `testimonialname` varchar(100) default NULL,
  `testimonialorganisation` varchar(100) default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `type` tinyint(4) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '1',
  `imageURL` varchar(50) default NULL,
  `createddatetime` datetime NOT NULL  default '0000-00-00 00:00:00',
  `regionID` int(11) NOT NULL default '1',
  `modifieddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

--
-- Table structure for table `wiki`
--

DROP TABLE IF EXISTS `wiki`;
CREATE TABLE IF NOT EXISTS `wiki` (
  `ID` int(11) NOT NULL auto_increment,
  `wikirefhost` varchar(255) NOT NULL,
  `wikirefURL` varchar(255) NOT NULL,
  `wikirefanchor` varchar(255) NOT NULL,  
  `wikititle` varchar(50) NOT NULL,
  `wikitext` text,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

--
-- Dumping data for table `wiki`
--

--
-- Table structure for table `wikiprefs`
--

DROP TABLE IF EXISTS `wikiprefs`;
CREATE TABLE IF NOT EXISTS `wikiprefs` (
  `ID` int(11) NOT NULL,
  `remoteURL` varchar(255) default 'www.digitaldexterity.net',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  ;

--
-- Dumping data for table `wikiprefs`
--