-- 
-- Database: `aquiescedb`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `preferences`
-- 

DROP TABLE IF EXISTS `preferences`;
CREATE TABLE IF NOT EXISTS `preferences` (
	`ID` int(11) NOT NULL auto_increment,
	`license_key` varchar(12) default NULL,
	`encrypted_fields` text,
	`orgname` varchar(100) default NULL,
	`orgaddress` text,
	`orgphone` varchar(50) default NULL,
	`orgfax` varchar(20) default NULL,
	`orgskype` varchar(50) default NULL, 
	`userscansignup` tinyint(4) NOT NULL default '0',
	`userscanlogin` tinyint(4) NOT NULL default '1',
	`stayloggedin` tinyint(4) NOT NULL default '0',
	`passwordmulticase` tinyint(4) NOT NULL default '0',
	`passwordnumber` tinyint(4) NOT NULL default '1',
	`passwordspecialchar` tinyint(4) NOT NULL default '0',
	`userpostalias` tinyint(4) NOT NULL default '0',
	`multisitesignup` tinyint(4) NOT NULL default '0',
	`addressrequired` tinyint(4) NOT NULL default '0',
	`usernamerequired` tinyint(4) NOT NULL default '1',
	`passwordrequired` tinyint(4) NOT NULL default '1',
	`passwordencrypted` tinyint(4) NOT NULL default '1',
	`defaultcrypttype` tinyint(4) NOT NULL default '1',
	`emailasusername` tinyint(4) NOT NULL default '0',
	`askdateofbirth` tinyint(4) NOT NULL default '0',
	`minimumage` tinyint(4) NOT NULL default '0',
	`emailverify` tinyint(4) NOT NULL default '0',
	`emailoptintype` tinyint(4) NOT NULL default '1',
	`emailoptinset` tinyint(4) NOT NULL default '0',
	`emailoptintext` varchar(255) default 'I would like to receive news updates from this site',
	`partneremailoptintype` tinyint(4) NOT NULL default '0',
	`partneremailoptinset` tinyint(4) NOT NULL default '0',
	`partneremailoptintext` varchar(255) default 'I would like to receive news updates from carefully selected partners',
	`groupoptintext` varchar(255) default 'You can opt in to any of the following:',
	`deletedatausertypeID` tinyint(4)  default NULL,
	`deletedataperiod` int(11) NOT NULL default '31449600', # in seconds 31449600 = a year
	`manualverify` tinyint(4) NOT NULL default '0',
	`securityletters` tinyint(4) NOT NULL default '0',
	`captcha_login` tinyint(4) NOT NULL default '0',
	`captcha_type` tinyint(4) NOT NULL default '1',
	`recaptcha_site_key` varchar(50) default NULL,
	`recaptcha_secret_key` varchar(50) default NULL,
	`memberdirectory` tinyint(4) NOT NULL default '0',
	`memberdirectoryemail` tinyint(4) NOT NULL default '0',
	`memberdirectoryname` varchar(50) default 'Member Directory',
	`registertext` varchar(50) default 'Register',
	`logintext` varchar(50) default 'Log in',
	`logouttext` varchar(50) default 'Log out',
	`membernetwork` tinyint(4) NOT NULL default '0',
	`memberpubliclocation` tinyint(4) NOT NULL default '0',
	`memberjobtitle` tinyint(4) NOT NULL default '0',
	`newuseralert` tinyint(4) NOT NULL default '0',
	`userupdatealert` tinyint(4) NOT NULL default '0',
	`welcomeemailID` tinyint(4) NOT NULL default '0',
	`contactemail` varchar(100) default "hello@digdex.co.uk",
	`forumsections` tinyint(4) NOT NULL default '0',
	`forummoderatorID` int(11) NOT NULL default '0', 
	`forumintrotext` mediumtext,
	`head` mediumtext,
	`header` mediumtext,
	`footer` mediumtext,
	`mapURL` varchar(255) default NULL,
	`maplat` double default NULL,
	`maplong` double default NULL,
	`googlemapsAPI` varchar(255) default 'AIzaSyAzcR-c4oEroPYGojnw-HdnwJhGzEprr-w',
	`googlesearchAPI` varchar(255) default NULL,
	`openspaceAPI` varchar(255) default NULL,
	`streetview` tinyint(4) NOT NULL default '1',
	`defaultzoom` tinyint(4) NOT NULL default '1',
	`defaultlongitude` double NOT NULL default '0',
	`defaultlatitude` double NOT NULL default '0',
	`paymentsystemID` varchar(255) default NULL,
	`openinghours` text,
	`termsagreetext` mediumtext,
	`termsconditions` mediumtext,
	`privacypolicy` mediumtext,
	`refundpolicy` mediumtext,
	`communityguidelines` tinyint(4) NOT NULL default '0',
	`termsarticleID` int(11)  default NULL, 
	`privacyarticleID` int(11) default NULL,  
	`approveforumposts` tinyint(4) NOT NULL default '0',
	`allowforumjpeg` tinyint(4) NOT NULL default '0',
	`allowforumHTML` tinyint(4) NOT NULL default '0',
	`allowforumflagreview` tinyint(4) NOT NULL default '0',
	`forumpublic` tinyint(4) NOT NULL default '0',
	`uselocations` tinyint(4) NOT NULL default '0',
	`uselocationcategory` tinyint(4) NOT NULL default '0',
	`useregions` tinyint(4) NOT NULL default '0',
	`usesections` tinyint(4) NOT NULL default '1',
	`userdirectory` tinyint(4) NOT NULL default '0',  
	`jobsexternal` tinyint(4) NOT NULL default '0',
	`askmiddlename` tinyint(4) NOT NULL default '0',
	`askmiddleprofile` tinyint(4) NOT NULL default '0',
	`askhowdiscovered` tinyint(4) NOT NULL default '0',
	`askhowdiscoveredother` tinyint(4) NOT NULL default '0',
	`askagerangetext` varchar(100) NOT NULL default 'Your age group',
	`askethnicity` tinyint(4) NOT NULL default '0',
	`askethnicitytext` varchar(100) NOT NULL default 'Your ethnicity',	
	`askgender` tinyint(4) NOT NULL default '0',
	`askgenderother` tinyint(4) NOT NULL default '0',
	`askgenderrathernotsay` tinyint(4) NOT NULL default '0',
	`askgendertext` varchar(100) NOT NULL default 'Your gender',
	`askdisability` tinyint(4) NOT NULL default '0',
	`askdisabilitytext` varchar(100) NOT NULL default 'Do you have any disabilities?',
	`asktelephone` tinyint(4) NOT NULL default '0',
	`askmobile` tinyint(4) NOT NULL default '0',
	`askjobtitle` tinyint(4) NOT NULL default '0',
	`asktwitter` tinyint(4) NOT NULL default '0',
	`askfacebook` tinyint(4) NOT NULL default '0',	
	`askwebsiteURL` tinyint(4) NOT NULL default '0',	
	`askhowdiscoveredprofile` tinyint(4) NOT NULL default '0',
	`askethnicityprofile` tinyint(4) NOT NULL default '0',
	`askgenderprofile` tinyint(4) NOT NULL default '0',
	`askdisabilityprofile` tinyint(4) NOT NULL default '0',
	`asktelephoneprofile` tinyint(4) NOT NULL default '0',
	`askmobileprofile` tinyint(4) NOT NULL default '0',
	`askjobtitleprofile` tinyint(4) NOT NULL default '0',
	`asktwitterprofile` tinyint(4) NOT NULL default '0',
	`askfacebookprofile` tinyint(4) NOT NULL default '0',
	`askwebsiteURLprofile` tinyint(4) NOT NULL default '0',	
	`askhowdiscoveredcompulsary` tinyint(4) NOT NULL default '0',
	`askethnicitycompulsary` tinyint(4) NOT NULL default '0',
	`askgendercompulsary` tinyint(4) NOT NULL default '0',
	`askdisabilitycompulsary` tinyint(4) NOT NULL default '0',
	`asktelephonecompulsary` tinyint(4) NOT NULL default '0',	
	`askmobilecompulsary` tinyint(4) NOT NULL default '0',	
	`askjobtitlecompulsary` tinyint(4) NOT NULL default '0',
	`asktwittercompulsary` tinyint(4) NOT NULL default '0',
	`askfacebookcompulsary` tinyint(4) NOT NULL default '0',
	`askwebsiteURLcompulsary` tinyint(4) NOT NULL default '0',
	`askaboutmeprofile` tinyint(4) NOT NULL default '1',
	`askaboutmecompulsary` tinyint(4) NOT NULL default '0',
	`askphotoprofile` tinyint(4) NOT NULL default '1',
	`askphotocompulsary` tinyint(4) NOT NULL default '0',
	`askcompanydetails` tinyint(4) NOT NULL default '0',
	`usesalutation` tinyint(4) NOT NULL default '0',	
	`videoupload` tinyint(4) NOT NULL default '0', 
	`loginattempts` tinyint(4) NOT NULL default '10', 
	`autolinks` tinyint(4) NOT NULL default '1', 
	`autousername` tinyint(4) NOT NULL default '0',
	`maxclassifiedsperuser` tinyint(4) NOT NULL default '1', 
	`memberspageURL` varchar(50) default NULL, 
	`controlpanelURL` varchar(50) default NULL, 
	`usercontactform` tinyint(4) NOT NULL default '0',	
	`micrositelevel` tinyint(4) NOT NULL default '0',
	`enablewidgets` tinyint(4) NOT NULL default '0',
	`googlemeta` varchar(255) default NULL,
	`bingmeta` varchar(255) default NULL,
	`alexameta` varchar(255) default NULL,
	`googleanalytics` text,
	`googleanalyticsecommerce` tinyint(4) NOT NULL default '0', 
	`googleconversions` text,
	`googleconversionsall` tinyint(4) NOT NULL default '0', 
	`googletagmanager` varchar(50) default NULL, 
	`addthiscode` text,	
	`facebookconversions` text,	
	`text_existingusers` varchar(50) default 'Existing Users',
	`text_loggedout` varchar(255) default 'You have successfully logged out. Log in again below.',
	`text_loginnow` varchar(50) default 'Log in to continue:',
	`text_newpassword` varchar(255) default 'You have successfully changed your password.<br>Please log in again with your new password.',
	`text_emailverified` varchar(255) default 'You have succesfully verified your email address. Now log in to continue with your chosen username and password.',	
	`text_username` varchar(50) default 'Username',
	`text_password` varchar(50) default 'Password',
	`text_middlename` varchar(50) default 'Middle Name',
	`text_retypepassword` varchar(50) default 'Retype Password',
	`text_choosepassword` varchar(255) default 'Please choose your own password to log in to this site with:',
	`text_stayloggedin` varchar(50) default 'Keep me logged in*',
	`text_forgotpass` varchar(50) default 'Forgotten password?',
	`text_logintips` mediumtext,
	`text_loginfail` mediumtext,
	`text_registerinfo` mediumtext,
	`text_signupinfo` mediumtext,
	`text_signup1` mediumtext ,
	`text_signup2` mediumtext,
	`text_salutation` varchar(50) default 'Title',
	`text_firstname` varchar(50) default 'First name',
	`text_surname` varchar(50) default 'Surname',
	`text_email` varchar(50) default 'Email',
	`text_role` varchar(50) default 'Role',
	`text_address_book` varchar(255) default 'Address Book',
	`user_list_email` tinyint(4) NOT NULL default '1',
	`user_list_phone` tinyint(4) NOT NULL default '0',
	`user_list_mobile` tinyint(4) NOT NULL default '0',
	`user_page_tabs` tinyint(4) NOT NULL default '1',
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`installdatetime` datetime default NULL,	
	`updateddatetime`  datetime default NULL,	
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `preferences`
-- 

INSERT INTO `preferences` (`ID`) VALUES (1);








-- --------------------------------------------------------

--
-- Table structure for table `agerange`
--

DROP TABLE IF EXISTS `agerange`;
CREATE TABLE IF NOT EXISTS `agerange` (
  `ID` int(11) NOT NULL auto_increment,
  `agerange` varchar(50) NOT NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `ordernum` int(11) NOT NULL default '0',
  KEY `statusID` (`statusID`),
	KEY `ordernum` (`ordernum`),
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;



INSERT INTO `agerange` (ID, `agerange`, `statusID`, `ordernum`) VALUES (1, 'Under 18' ,1,1);
INSERT INTO `agerange` (ID, `agerange`, `statusID`, `ordernum`)  VALUES (2, '18-25' ,1,2);
INSERT INTO `agerange` (ID, `agerange`, `statusID`, `ordernum`)  VALUES (3, '26-45',1,3);
INSERT INTO `agerange` (ID, `agerange`, `statusID`, `ordernum`)  VALUES (4, '46-65',1,4);
INSERT INTO `agerange` (ID, `agerange`, `statusID`, `ordernum`)  VALUES (5, 'over 65',1,5);





-- --------------------------------------------------------

-- 
-- Table structure for table `article`
-- 

-- regionID here is used ONLY for Home Page

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
	`ID` int(11) NOT NULL auto_increment,
	`articletype` tinyint(4) NOT NULL default '1', # 1=article, 2=section, 3=url redirect
	`longID` varchar(255)  default NULL,
	`oldlongID` varchar(255)  default NULL,
	`ordernum` int(11) NOT NULL default '0',
	`regionID` int(11) default 1,
	`versionofID` int(11) default NULL,
	`robots` tinyint(4) default 1,
	`history` tinyint(4) default 1,
	`title` varchar(100) NOT NULL default '',
	`seotitle` varchar(100) NOT NULL default '',
	`body` mediumtext,
	`notes` text,
	`metakeywords` text,
	`metadescription` text,
	`googleconversions` text,
	`showlink` tinyint(4) NOT NULL default '1',
	`newWindow` tinyint(4) NOT NULL default '0',
	`statusID` tinyint(4) NOT NULL default '1',
	`draft` tinyint(4) NOT NULL default '0',
	`redirectURL` varchar(255) default NULL,  
	`redirecttype` int(11) default 301,  
	`sectionID` int(11) NOT NULL default '1',
	`photogalleryID` int(11) default NULL,
	`ogimageURL` varchar(255) default NULL,  
	`allowcomments` tinyint(4) NOT NULL default '0',
	`headHTML` text,
	`headHTMLineditor` tinyint(4) NOT NULL default '0',
	`class` varchar(50) default NULL,
	`linktitle` varchar(50) default NULL,
	`accesslevel` tinyint(4) NOT NULL default '0',
	`editedbyID` int(11) default NULL,
	`editeddatetime` datetime default NULL,
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `ordernum` (`ordernum`),
	KEY `showlink` (`showlink`),
	KEY `articletype` (`articletype`),
	KEY `regionID` (`regionID`),
	KEY `statusID` (`statusID`),
	KEY `sectionID` (`sectionID`),
	KEY `versionofID` (`versionofID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


-- 
-- Table structure for table `articleprefs`
-- 

DROP TABLE IF EXISTS `articleprefs`;
CREATE TABLE IF NOT EXISTS `articleprefs` (
	`ID` tinyint(4) NOT NULL default '1',
	`titleheading` tinyint(4) NOT NULL default '0',
	`addtitle` tinyint(4) NOT NULL default '1',
	`containerclass` varchar(255)  default NULL,
	`pageclass` varchar(255)  default NULL,
	`indextitle` varchar(255)  default 'Index',
	`indexmetadescription` text,
	`indexshowsearch` tinyint(4) NOT NULL default '0',
	`productlinks` tinyint(4) NOT NULL default '0',
	`documentlinks` tinyint(4) NOT NULL default '0',
	`membersubmit` tinyint(4) NOT NULL default '0',
	`safeemail` tinyint(4) NOT NULL default '1',
	`articlesectionorder` tinyint(4) NOT NULL default '1',
	`notfoundURL` varchar(255)  default NULL,	
	`text_siteindex` varchar(255)  default 'Site Index',
	`articleshare` tinyint(4) NOT NULL default '0',	
	
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1;

-- 
-- Dumping data for table `articleprefs`
-- 

INSERT INTO `articleprefs` (`ID`) VALUES (1);


-- --------------------------------------------------------

-- 
-- Table structure for table `articlesection`
-- 

DROP TABLE IF EXISTS `articlesection`;
CREATE TABLE IF NOT EXISTS `articlesection` (
	`ID` int(11) NOT NULL auto_increment,
	`longID` varchar(255)  default NULL,
	`showlink` tinyint(4) NOT NULL default '1',
	`articleID` int(11) default NULL, #represented by article (so we can sort)
	`metakeywords` varchar(255) default NULL,
	`metadescription` varchar(255) default NULL,
	`ordernum` int(11) NOT NULL default '0',
	`subsectionofID` int(11) NOT NULL default '0',
	`description` varchar(255) NOT NULL default '',
	`accesslevel` tinyint(4) NOT NULL default '0',
	`groupreadID` tinyint(4) NOT NULL default '0',
	`writerankID` tinyint(4) NOT NULL default '7',
	`approverankID` tinyint(4) NOT NULL default '0',
	`groupwriteID` tinyint(4) NOT NULL default '0',
	`regionID` tinyint(4) NOT NULL default '1',
	`linkaction` tinyint(4) NOT NULL default '1',
	`newWindow` tinyint(4) NOT NULL default '0',
	`class` varchar(50) default NULL,
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `ordernum` (`ordernum`),
	KEY `showlink` (`showlink`),
	KEY `regionID` (`regionID`),
	KEY `articleID` (`articleID`),
	KEY `subsectionofID` (`subsectionofID`),
	KEY `accesslevel` (`accesslevel`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;






-- --------------------------------------------------------


-- 
-- Table structure for table `backup`
-- 
-- autobackuptype 0 -manual  1-CRON 2- Page access 
-- backupcontenttype 1 - database;  2- uploaded file;  3 - filemanager file

DROP TABLE IF EXISTS `backup`;
CREATE TABLE IF NOT EXISTS `backup` (
  `ID` int(11) NOT NULL auto_increment, 
  `backupfilename` varchar(255) NOT NULL,
  `remotefilename` varchar(255) NOT NULL,
  `autobackuptype` tinyint(4) default '0',
  `backupcontenttype` tinyint(4) default '1',
  `statusID` tinyint(4) default '0',
  `createdbyID` tinyint(4) default '0',
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`)
  ) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

--
-- Dumping data for table `backup`
--


-- --------------------------------------------------------


-- 
-- Table structure for table `backupprefs`
-- 

DROP TABLE IF EXISTS `backupprefs`;
CREATE TABLE IF NOT EXISTS `backupprefs` (
  `ID` int(11) NOT NULL auto_increment, 
  `autobackup` tinyint(4) default '0',
  `autobackupdestination` tinyint(4) default '0',
  `backupftpserver` varchar(100) default NULL,
  `backupftpuser` varchar(50) default NULL,
  `backupftppassword` varchar(255) default NULL,
  `backupftppath` varchar(255) default '/Backups/',
  `backupstart` datetime default NULL,
  `backupfrequency` int(11) default NULL,
  `backupemail` varchar(100) default NULL,
  `backupzip`  tinyint(4) default '0',
  `backupfiles`  tinyint(4) default '0',
  `backupnotes`  text,
  `ftptype` tinyint(4) default '1',
  `remoteclientURL` varchar(255) default 'http://www.yourdomain.com/documents/admin/backup/backupclient.php',
  PRIMARY KEY  (`ID`)
  ) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

--
-- Dumping data for table `backupprefs`
--

-- --------------------------------------------------------


-- 
-- Table structure for table `bannedwords`
-- 

DROP TABLE IF EXISTS `bannedwords`;
CREATE TABLE IF NOT EXISTS `bannedwords` (
  `ID` int(11) NOT NULL auto_increment,
  `word` varchar(20) NOT NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `bannedwords`
-- 





-- --------------------------------------------------------

-- 
-- Table structure for table `comments`
-- 

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `ID` int(11) NOT NULL auto_increment,
  `newsID` int(11) default NULL,
  `articleID` int(11) default NULL,
  `photoID` int(11) default NULL,
  `commenttext` text NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `newsID` (`newsID`),
  KEY `photoID` (`photoID`),
  KEY `createdbyID` (`createdbyID`),
  KEY `createddatetime` (`createddatetime`),
  KEY `statusID` (`statusID`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `communication`
--

DROP TABLE IF EXISTS `communication`;
CREATE TABLE IF NOT EXISTS `communication` (
  `ID` int(11) NOT NULL auto_increment,
  `regionID` int(11) NOT NULL default '1',
  `commtypeID` tinyint(4) default NULL,
  `commcatID` tinyint(4) default NULL,
  `incoming` tinyint(4) default NULL,
  `userID` int(11) default NULL,
  `clientID` int(11) default NULL,
  `locationID` int(11) default NULL,
  `directoryID` int(11) default NULL,
  `orderID` int(11) default NULL,
  `notes` text NOT NULL,
  `thiscommdatetime` datetime default NULL,
  `nextcommdatetime` datetime default NULL,
  `nextcommID` int(11) default NULL,
  `followupuserID` int(11) default NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`),
  KEY `modifiedbyID` (`modifiedbyID`),
  KEY `orderID` (`orderID`),
  KEY `regionID` (`regionID`),
  KEY `locationID` (`locationID`),
  KEY `commcatID` (`commcatID`),
  KEY `directoryID` (`directoryID`),
  KEY `userID` (`userID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `communicationtype`
--

DROP TABLE IF EXISTS `communicationtype`;
CREATE TABLE IF NOT EXISTS `communicationtype` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `typename` varchar(50) NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `ordernum` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

--
-- Dumping data for table `communicationtype`
--

INSERT INTO `communicationtype` (`ID`, `typename`, `createdbyID`, `createddatetime`, `modifiedbyID`, `modifieddatetime`, `statusID`) VALUES
(1, 'Telephone', 0, '2010-08-02 10:15:09', NULL, NULL, 1),
(2, 'email', 0, '2010-08-02 10:15:13', NULL, NULL, 1);



-- --------------------------------------------------------

--
-- Table structure for table `communicationcategory`
--

DROP TABLE IF EXISTS `communicationcategory`;
CREATE TABLE IF NOT EXISTS `communicationcategory` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `categoryname` varchar(20) NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `contactsubject`
-- 

DROP TABLE IF EXISTS `contactsubject`;
CREATE TABLE IF NOT EXISTS `contactsubject` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
   `statusID` tinyint(4) NOT NULL default '1',
   `ordernum` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `contactsubject`
-- 

INSERT INTO `contactsubject` (`ID`, `description`) VALUES
(1, 'General Enquiry');





-- --------------------------------------------------------

-- 
-- Table structure for table `correspondence`
-- 

DROP TABLE IF EXISTS `correspondence`;
CREATE TABLE IF NOT EXISTS `correspondence` (
	`ID` int(11) NOT NULL auto_increment,
	`accountID` tinyint(4) NOT NULL default '0',
	`messageID` varchar(100) default NULL,
	`sessionID` varchar(32) default NULL,
	`recipient` varchar(100)  default NULL,
	`recipientID` int(11) default NULL,
	`subject` varchar(100)  default NULL,
	`message` mediumtext,
	`rawemail` tinyint(4) NOT NULL default '0',
	`sentdatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	`mailfolderID` tinyint(4) NOT NULL default '1',
	`autoreply` tinyint(4) NOT NULL default '0',
	`responsetoID` int(11) default '0',
	`regionID` int(11) default '1',
	`sender` varchar(100) default NULL,
	`sendername` varchar(50) default NULL,
	`reply_using` tinyint(4) default NULL,
	`telephone` varchar(10) default NULL,
	`address` text,
	`directoryID` int(11) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `mailfolderID` (`mailfolderID`),
	KEY `messageID` (`messageID`),
	KEY `directoryID` (`directoryID`),
	KEY `regionID` (`regionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `correspondence`
-- 

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
CREATE TABLE IF NOT EXISTS `countries` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `cc1` varchar(10) DEFAULT NULL,
  `fullname` varchar(100) NOT NULL DEFAULT '',
  `iso2` varchar(2) DEFAULT NULL,
  `iso3` varchar(50) DEFAULT NULL,
  `num_code` int(11) DEFAULT NULL,
  `currency_code` varchar(10) DEFAULT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `tld` varchar(2) DEFAULT NULL,
  `ordernum` int(11) NOT NULL DEFAULT '0',
  `statusID` tinyint(4) NOT NULL DEFAULT '1',
  `regionID` int(11) DEFAULT NULL,
  `local_shipping` varchar(10) DEFAULT NULL,
  `int_shipping` varchar(10) DEFAULT NULL,
  `shippingamount1` varchar(10) DEFAULT NULL,
  `shippingamount2` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `statusID` (`statusID`),
  KEY `regionID` (`regionID`),
  KEY `iso2` (`iso2`),
  KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`ID`, `cc1`, `fullname`, `iso2`, `iso3`, `num_code`, `currency_code`, `nationality`, `tld`, `ordernum`, `statusID`, `regionID`, `local_shipping`, `int_shipping`, `shippingamount1`, `shippingamount2`) VALUES
(1, 'AA', 'Aruba', 'AW', 'ABW', 533, 'AWG', 'Aruban', 'aw', 0, 1, NULL, NULL, NULL, NULL, NULL),
(2, 'AC', 'Antigua and Barbuda', 'AG', 'ATG', 28, 'XCD', 'Antiguan or Barbudan', 'ag', 0, 1, NULL, NULL, NULL, NULL, NULL),
(3, 'AE', 'United Arab Emirates', 'AE', 'ARE', 784, 'AED', 'Emirati, Emirian, Emiri', 'ae', 0, 1, NULL, NULL, NULL, NULL, NULL),
(4, 'AF', 'Afghanistan', 'AF', 'AFG', 4, 'AFN', 'Afghan', 'af', 0, 1, NULL, NULL, NULL, NULL, NULL),
(5, 'AG', 'Algeria', 'DZ', 'DZA', 12, 'DZD', 'Algerian', 'dz', 0, 1, NULL, NULL, NULL, NULL, NULL),
(6, 'AJ', 'Azerbaijan', 'AZ', 'AZE', 31, 'AZN', 'Azerbaijani, Azeri', 'az', 0, 1, NULL, NULL, NULL, NULL, NULL),
(7, 'AL', 'Albania', 'AL', 'ALB', 8, 'ALL', 'Albanian', 'al', 0, 1, NULL, NULL, NULL, NULL, NULL),
(8, 'AM', 'Armenia', 'AM', 'ARM', 51, 'AMD', 'Armenian', 'am', 0, 1, NULL, NULL, NULL, NULL, NULL),
(9, 'AN', 'Andorra', 'AD', 'AND', 20, 'EUR', 'Andorran', 'ad', 0, 1, NULL, NULL, NULL, NULL, NULL),
(10, 'AO', 'Angola', NULL, 'AGO', 24, 'AOA', 'Angolan', 'ao', 0, 1, NULL, NULL, NULL, NULL, NULL),
(11, 'AQ', 'American Samoa', 'AS', 'ASM', 16, 'USD', 'American Samoan', 'as', 0, 1, NULL, NULL, NULL, NULL, NULL),
(12, 'AR', 'Argentina', 'AR', 'ARG', 32, 'ARS', 'Argentine', 'ar', 0, 1, NULL, NULL, NULL, NULL, NULL),
(13, 'AS', 'Australia', 'AU', 'AUS', 36, 'AUD', 'Australian', 'au', 0, 1, NULL, NULL, NULL, NULL, NULL),
(15, 'AU', 'Austria', 'AT', 'AUT', 40, 'EUR', 'Austrian', 'at', 0, 1, 9, NULL, NULL, NULL, NULL),
(16, 'AV', 'Anguilla', 'AI', 'AIA', 660, 'XCD', 'Anguillan', 'ai', 0, 1, NULL, NULL, NULL, NULL, NULL),
(17, 'AY', 'Antarctica', 'AQ', 'ATA', 10, '', 'Antarctic', 'aq', 0, 1, NULL, NULL, NULL, NULL, NULL),
(18, 'BA', 'Bahrain', 'BH', 'BHR', 48, 'BHD', 'Bahraini', 'bh', 0, 1, NULL, NULL, NULL, NULL, NULL),
(19, 'BB', 'Barbados', 'BB', 'BRB', 52, 'BBD', 'Barbadian', 'bb', 0, 1, NULL, NULL, NULL, NULL, NULL),
(20, 'BC', 'Botswana', 'BW', 'BWA', 72, 'BWP', 'Motswana, Botswanan', 'bw', 0, 1, NULL, NULL, NULL, NULL, NULL),
(21, 'BD', 'Bermuda', 'BM', 'BMU', 60, 'BMD', 'Bermudian, Bermudan', 'bm', 0, 1, NULL, NULL, NULL, NULL, NULL),
(22, 'BE', 'Belgium', 'BE', 'BEL', 56, 'EUR', 'Belgian', 'be', 0, 1, NULL, NULL, NULL, NULL, NULL),
(23, 'BF', 'Bahamas', 'BS', 'BHS', 44, 'BSD', 'Bahamian', 'bs', 0, 1, NULL, NULL, NULL, NULL, NULL),
(24, 'BG', 'Bangladesh', 'BD', 'BGD', 50, 'BDT', 'Bangladeshi', 'bd', 0, 1, NULL, NULL, NULL, NULL, NULL),
(25, 'BH', 'Belize', 'BZ', 'BLZ', 84, 'BZD', 'Belizean', 'bz', 0, 1, NULL, NULL, NULL, NULL, NULL),
(26, 'BK', 'Bosnia and Herzegovina', 'BA', 'BIH', 70, 'BAM', 'Bosnian or Herzegovinian', 'ba', 0, 1, NULL, NULL, NULL, NULL, NULL),
(27, 'BL', 'Bolivia', 'BO', 'BOL', 68, 'BOB', 'Bolivian', 'bo', 0, 1, NULL, NULL, NULL, NULL, NULL),
(28, 'BM', 'Burma', 'MM', 'MMR', 104, 'MMK', 'Burmese', 'mm', 0, 1, NULL, NULL, NULL, NULL, NULL),
(29, 'BN', 'Benin', 'BJ', 'BEN', 204, 'XOF', 'Beninese, Beninois', 'bj', 0, 1, NULL, NULL, NULL, NULL, NULL),
(30, 'BO', 'Belarus', NULL, 'BLR', 112, 'BYN', 'Belarusian', 'by', 0, 1, NULL, NULL, NULL, NULL, NULL),
(31, 'BP', 'Solomon Islands', 'SB', 'SLB', 90, 'SBD', 'Solomon Island', 'sb', 0, 1, NULL, NULL, NULL, NULL, NULL),
(32, 'BQ', 'Navassa Island', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(33, 'BR', 'Brazil', 'BR', 'BRA', 76, 'BRL', 'Brazilian', 'br', 0, 1, NULL, NULL, NULL, NULL, NULL),
(34, 'BS', 'Bassas da India', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(35, 'BT', 'Bhutan', 'BT', 'BTN', 64, 'INR,BTN', 'Bhutanese', 'bt', 0, 1, NULL, NULL, NULL, NULL, NULL),
(36, 'BU', 'Bulgaria', 'BG', 'BGR', 100, 'BGN', 'Bulgarian', 'bg', 0, 1, NULL, NULL, NULL, NULL, NULL),
(37, 'BV', 'Bouvet Island', 'BV', 'BVT', 74, 'NOK', 'Bouvet Island', 'bv', 0, 1, NULL, NULL, NULL, NULL, NULL),
(38, 'BX', 'Brunei', 'BN', 'BRN', 96, 'BND', 'Bruneian', 'bn', 0, 1, NULL, NULL, NULL, NULL, NULL),
(39, 'BY', 'Burundi', 'BI', 'BDI', 108, 'BIF', 'Burundian', 'bi', 0, 1, NULL, NULL, NULL, NULL, NULL),
(40, 'CA', 'Canada', 'CA', 'CAN', 124, 'CAD', 'Canadian', 'ca', -2, 1, NULL, NULL, NULL, NULL, NULL),
(41, 'CB', 'Cambodia', 'KH', 'KHM', 116, 'KHR', 'Cambodian', 'kh', 0, 1, NULL, NULL, NULL, NULL, NULL),
(42, 'CD', 'Chad', 'TD', 'TCD', 148, 'XAF', 'Chadian', 'td', 0, 1, NULL, NULL, NULL, NULL, NULL),
(43, 'CE', 'Sri Lanka', 'LK', 'LKA', 144, 'LKR', 'Sri Lankan', 'lk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(44, 'CF', 'Congo', 'CG', 'COG', 178, 'XAF', 'Congolese', 'cg', 0, 1, NULL, NULL, NULL, NULL, NULL),
(46, 'CH', 'China', 'CN', 'CHN', 156, 'CNY', 'Chinese', 'cn', 0, 1, NULL, NULL, NULL, NULL, NULL),
(47, 'CI', 'Chile', 'CL', 'CHL', 152, 'CLP', 'Chilean', 'cl', 0, 1, NULL, NULL, NULL, NULL, NULL),
(48, 'CJ', 'Cayman Islands', 'KY', 'CYM', 136, 'KYD', 'Caymanian', 'ky', 0, 1, NULL, NULL, NULL, NULL, NULL),
(49, 'CK', 'Cocos (Keeling) Islands', 'CC', 'CCK', 166, 'AUD', 'Cocos Island', 'cc', 0, 1, NULL, NULL, NULL, NULL, NULL),
(50, 'CM', 'Cameroon', 'CM', 'CMR', 120, 'XAF', 'Cameroonian', 'cm', 0, 1, NULL, NULL, NULL, NULL, NULL),
(51, 'CN', 'Comoros', 'KM', 'COM', 174, 'KMF', 'Comoran, Comorian', 'km', 0, 1, NULL, NULL, NULL, NULL, NULL),
(52, 'CO', 'Colombia', 'CO', 'COL', 170, 'COP', 'Colombian', 'co', 0, 1, NULL, NULL, NULL, NULL, NULL),
(53, 'CQ', 'Northern Mariana Islands', 'MP', 'MNP', 580, 'USD', 'Northern Marianan', 'mp', 0, 1, NULL, NULL, NULL, NULL, NULL),
(54, 'CR', 'Coral Sea Islands', 'AU', 'AUS', 36, 'AUD', 'Australian', 'au', 0, 1, NULL, NULL, NULL, NULL, NULL),
(55, 'CS', 'Costa Rica', 'CR', 'CRI', 188, 'CRC', 'Costa Rican', 'cr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(56, 'CT', 'Central African Republic', 'CF', 'CAF', 140, 'XAF', 'Central African', 'cf', 0, 1, NULL, NULL, NULL, NULL, NULL),
(57, 'CU', 'Cuba', 'CU', 'CUB', 192, 'CUP,CUC', 'Cuban', 'cu', 0, 1, NULL, NULL, NULL, NULL, NULL),
(58, 'CV', 'Cape Verde', 'CV', 'CPV', 132, 'CVE', 'Cabo Verdean', 'cv', 0, 1, NULL, NULL, NULL, NULL, NULL),
(59, 'CW', 'Cook Islands', 'CK', 'COK', 184, 'NZD', 'Cook Island', 'ck', 0, 1, NULL, NULL, NULL, NULL, NULL),
(60, 'CY', 'Cyprus', 'CY', 'CYP', 196, 'EUR', 'Cypriot', 'cy', 0, 1, NULL, NULL, NULL, NULL, NULL),
(61, 'DA', 'Denmark', 'DK', 'DNK', 208, 'DKK', 'Danish', 'dk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(62, 'DJ', 'Djibouti', 'DJ', 'DJI', 262, 'DJF', 'Djiboutian', 'dj', 0, 1, NULL, NULL, NULL, NULL, NULL),
(63, 'DO', 'Dominica', 'DM', 'DMA', 212, 'XCD', 'Dominican', 'dm', 0, 1, NULL, NULL, NULL, NULL, NULL),
(64, 'DQ', 'Jarvis Island', 'US', 'USA', 840, 'USD', 'American', 'us', 0, 1, NULL, NULL, NULL, NULL, NULL),
(65, 'DR', 'Dominican Republic', 'DO', 'DOM', 214, 'DOP', 'Dominican', 'do', 0, 1, NULL, NULL, NULL, NULL, NULL),
(66, 'EC', 'Ecuador', 'EC', 'ECU', 218, 'USD', 'Ecuadorian', 'ec', 0, 1, NULL, NULL, NULL, NULL, NULL),
(67, 'EG', 'Egypt', 'EG', 'EGY', 818, 'EGP', 'Egyptian', 'eg', 0, 1, NULL, NULL, NULL, NULL, NULL),
(68, 'EI', 'Ireland', 'IE', 'IRL', 372, 'EUR', 'Irish', 'ie', 0, 1, 3, NULL, NULL, NULL, NULL),
(69, 'EK', 'Equatorial Guinea', 'GQ', 'GNQ', 226, 'XAF', 'Equatorial Guinean, Equatoguinean', 'gq', 0, 1, NULL, NULL, NULL, NULL, NULL),
(70, 'EN', 'Estonia', 'EE', 'EST', 233, 'EUR', 'Estonian', 'ee', 0, 1, NULL, NULL, NULL, NULL, NULL),
(71, 'ER', 'Eritrea', 'ER', 'ERI', 232, 'ERN', 'Eritrean', 'er', 0, 1, NULL, NULL, NULL, NULL, NULL),
(72, 'ES', 'El Salvador', 'SV', 'SLV', 222, 'SVC,USD', 'Salvadoran', 'sv', 0, 1, NULL, NULL, NULL, NULL, NULL),
(73, 'ET', 'Ethiopia', 'ET', 'ETH', 231, 'ETB', 'Ethiopian', 'et', 0, 1, NULL, NULL, NULL, NULL, NULL),
(74, 'EU', 'Europa Island', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(75, 'EZ', 'Czech Republic', 'CZ', 'CZE', 203, 'CZK', 'Czech', 'cz', 0, 1, NULL, NULL, NULL, NULL, NULL),
(76, 'FA', 'Falklands', 'FK', 'FLK', 238, '', 'Falkland Island', 'fk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(77, 'FG', 'French Guiana', 'GF', 'GUF', 254, 'EUR', 'French Guianese', 'gf', 0, 1, NULL, NULL, NULL, NULL, NULL),
(78, 'FI', 'Finland', 'FI', 'FIN', 246, 'EUR', 'Finnish', 'fi', 0, 1, NULL, NULL, NULL, NULL, NULL),
(79, 'FJ', 'Fiji', 'FJ', 'FJI', 242, 'FJD', 'Fijian', 'fj', 0, 1, NULL, NULL, NULL, NULL, NULL),
(81, 'FO', 'Faroe Islands', 'FO', 'FRO', 234, 'DKK', 'Faroese', 'fo', 0, 1, NULL, NULL, NULL, NULL, NULL),
(82, 'FP', 'French Polynesia', 'PF', 'PYF', 258, 'XPF', 'French Polynesian', 'pf', 0, 1, NULL, NULL, NULL, NULL, NULL),
(83, 'FQ', 'Baker Island', 'US', 'USA', 840, 'USD', 'American', 'us', 0, 1, NULL, NULL, NULL, NULL, NULL),
(84, 'FR', 'France', 'FR', 'FRA', 250, 'EUR', 'French', 'fr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(86, 'GA', 'Gambia (The)', 'GM', 'GMB', 270, 'GMD', 'Gambian', 'gm', 0, 1, NULL, NULL, NULL, NULL, NULL),
(87, 'GB', 'Gabon', 'GA', 'GAB', 266, 'XAF', 'Gabonese', 'ga', 0, 1, NULL, NULL, NULL, NULL, NULL),
(88, 'GG', 'Georgia', 'GE', 'GEO', 268, 'GEL', 'Georgian', 'ge', 0, 1, NULL, NULL, NULL, NULL, NULL),
(89, 'GH', 'Ghana', 'GH', 'GHA', 288, 'GHS', 'Ghanaian', 'gh', 0, 1, NULL, NULL, NULL, NULL, NULL),
(90, 'GI', 'Gibraltar', 'GI', 'GIB', 292, 'GIP', 'Gibraltar', 'gi', 0, 1, NULL, NULL, NULL, NULL, NULL),
(91, 'GJ', 'Grenada', 'GD', 'GRD', 308, 'XCD', 'Grenadian', 'gd', 0, 1, NULL, NULL, NULL, NULL, NULL),
(92, 'GK', 'Guernsey', '', 'GBR', 826, 'GBP', 'British, UK', 'uk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(93, 'GL', 'Greenland', 'GL', 'GRL', 304, 'DKK', 'Greenlandic', 'gl', 0, 1, NULL, NULL, NULL, NULL, NULL),
(94, 'GM', 'Germany', 'DE', 'DEU', 276, 'EUR', 'German', 'de', 0, 1, 9, NULL, NULL, NULL, NULL),
(95, 'GO', 'Glorioso Islands', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(96, 'GP', 'Guadeloupe', 'GP', 'GLP', 312, 'EUR', 'Guadeloupe', 'gp', 0, 1, NULL, NULL, NULL, NULL, NULL),
(97, 'GQ', 'Guam', 'GU', 'GUM', 316, 'USD', 'Guamanian, Guambat', 'gu', 0, 1, NULL, NULL, NULL, NULL, NULL),
(98, 'GR', 'Greece', 'GR', 'GRC', 300, 'EUR', 'Greek, Hellenic', 'gr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(99, 'GT', 'Guatemala', 'GT', 'GTM', 320, 'GTQ', 'Guatemalan', 'gt', 0, 1, NULL, NULL, NULL, NULL, NULL),
(100, 'GV', 'Guinea', 'GN', 'GIN', 324, 'GNF', 'Guinean', 'gn', 0, 1, NULL, NULL, NULL, NULL, NULL),
(101, 'GY', 'Guyana', 'GY', 'GUY', 328, 'GYD', 'Guyanese', 'gy', 0, 1, NULL, NULL, NULL, NULL, NULL),
(102, 'GZ', 'Gaza Strip', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(103, 'HA', 'Haiti', 'HT', 'HTI', 332, 'HTG,USD', 'Haitian', 'ht', 0, 1, NULL, NULL, NULL, NULL, NULL),
(104, 'HK', 'Hong Kong', 'HK', 'HKG', 344, 'HKD', 'Hong Kong, Hong Kongese', 'hk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(106, 'HO', 'Honduras', 'HN', 'HND', 340, 'HNL', 'Honduran', 'hn', 0, 1, NULL, NULL, NULL, NULL, NULL),
(107, 'HQ', 'Howland Island', 'US', 'USA', 840, 'USD', 'American', 'us', 0, 1, NULL, NULL, NULL, NULL, NULL),
(108, 'HR', 'Croatia', NULL, 'HRV', 191, 'HRK', 'Croatian', 'hr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(109, 'HU', 'Hungary', 'HU', 'HUN', 348, 'HUF', 'Hungarian, Magyar', 'hu', 0, 1, NULL, NULL, NULL, NULL, NULL),
(110, 'IC', 'Iceland', 'IS', 'ISL', 352, 'ISK', 'Icelandic', 'is', 0, 1, NULL, NULL, NULL, NULL, NULL),
(111, 'ID', 'Indonesia', NULL, 'IDN', 360, 'IDR', 'Indonesian', 'id', 0, 1, NULL, NULL, NULL, NULL, NULL),
(112, 'IM', 'Man (Isle of)', '', 'GBR', 826, 'GBP', 'British, UK', 'uk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(113, 'IN', 'India', 'IN', 'IND', 356, 'INR', 'Indian', 'in', 0, 1, NULL, NULL, NULL, NULL, NULL),
(115, 'IP', 'Clipperton Island', 'PF', 'PYF', 258, 'XPF', 'French Polynesian', 'pf', 0, 1, NULL, NULL, NULL, NULL, NULL),
(116, 'IR', 'Iran', 'IR', 'IRN', 364, 'IRR', 'Iranian, Persian', 'ir', 0, 1, NULL, NULL, NULL, NULL, NULL),
(117, 'IS', 'Israel', 'IL', 'ISR', 376, 'ILS', 'Israeli', 'il', 0, 1, NULL, NULL, NULL, NULL, NULL),
(118, 'IT', 'Italy', 'IT', 'ITA', 380, 'EUR', 'Italian', 'it', 0, 1, NULL, NULL, NULL, NULL, NULL),
(119, 'IV', 'Cote d''Ivoire', 'CI', 'CIV', 384, 'XOF', 'Ivorian', 'ci', 0, 1, NULL, NULL, NULL, NULL, NULL),
(120, 'IZ', 'Iraq', 'IQ', 'IRQ', 368, 'IQD', 'Iraqi', 'iq', 0, 1, NULL, NULL, NULL, NULL, NULL),
(121, 'JA', 'Japan', 'JP', 'JPN', 392, 'JPY', 'Japanese', 'jp', 0, 1, NULL, NULL, NULL, NULL, NULL),
(122, 'JE', 'Jersey', '', 'GBR', 826, 'GBP', 'British, UK', 'uk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(123, 'JM', 'Jamaica', 'JM', 'JAM', 388, 'JMD', 'Jamaican', 'jm', 0, 1, NULL, NULL, NULL, NULL, NULL),
(124, 'JN', 'Jan Mayen', 'SJ', 'SJM', 744, 'NOK', 'Svalbard', 'sj', 0, 1, NULL, NULL, NULL, NULL, NULL),
(125, 'JO', 'Jordan', 'JO', 'JOR', 400, 'JOD', 'Jordanian', 'jo', 0, 1, NULL, NULL, NULL, NULL, NULL),
(126, 'JQ', 'Johnston Atoll', 'US', 'USA', 840, 'USD', 'American', 'us', 0, 1, NULL, NULL, NULL, NULL, NULL),
(127, 'JU', 'Juan de Nova Island', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(128, 'KE', 'Kenya', 'KE', 'KEN', 404, 'KES', 'Kenyan', 'ke', 0, 1, NULL, NULL, NULL, NULL, NULL),
(129, 'KG', 'Kyrgyzstan', NULL, 'KGZ', 417, 'KGS', 'Kyrgyzstani, Kyrgyz, Kirgiz, Kirghiz', 'kg', 0, 1, NULL, NULL, NULL, NULL, NULL),
(130, 'KN', 'Korea (North)', 'KR', 'PRK', 408, 'KPW', 'North Korean', 'kp', 0, 1, NULL, NULL, NULL, NULL, NULL),
(131, 'KQ', 'Kingman Reef', 'US', 'USA', 840, 'USD', 'American', 'us', 0, 1, NULL, NULL, NULL, NULL, NULL),
(132, 'KR', 'Kiribati', 'KI', 'KIR', 296, 'AUD', 'I-Kiribati', 'ki', 0, 1, NULL, NULL, NULL, NULL, NULL),
(133, 'KS', 'Korea (South)', 'KP', 'KOR', 410, 'KRW', 'South Korean', 'kr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(134, 'KT', 'Christmas Island', 'CX', 'CXR', 162, 'AUD', 'Christmas Island', 'cx', 0, 1, NULL, NULL, NULL, NULL, NULL),
(135, 'KU', 'Kuwait', 'KW', 'KWT', 414, 'KWD', 'Kuwaiti', 'kw', 0, 1, NULL, NULL, NULL, NULL, NULL),
(136, 'KZ', 'Kazakhstan', 'KZ', 'KAZ', 398, 'KZT', 'Kazakhstani, Kazakh', 'kz', 0, 1, NULL, NULL, NULL, NULL, NULL),
(137, 'LA', 'Laos', 'LA', 'LAO', 418, 'LAK', 'Lao, Laotian', 'la', 0, 1, NULL, NULL, NULL, NULL, NULL),
(138, 'LE', 'Lebanon', 'LB', 'LBN', 422, 'LBP', 'Lebanese', 'lb', 0, 1, NULL, NULL, NULL, NULL, NULL),
(139, 'LG', 'Latvia', 'LV', 'LVA', 428, 'EUR', 'Latvian', 'lv', 0, 1, NULL, NULL, NULL, NULL, NULL),
(140, 'LH', 'Lithuania', 'LT', 'LTU', 440, 'EUR', 'Lithuanian', 'lt', 0, 1, NULL, NULL, NULL, NULL, NULL),
(141, 'LI', 'Liberia', 'LR', 'LBR', 430, 'LRD', 'Liberian', 'lr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(142, 'LO', 'Slovakia', 'SK', 'SVK', 703, 'EUR', 'Slovak', 'sk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(143, 'LQ', 'Palmyra Atoll', 'US', 'USA', 840, 'USD', 'American', 'us', 0, 1, NULL, NULL, NULL, NULL, NULL),
(144, 'LS', 'Liechtenstein', 'LI', 'LIE', 438, 'CHF', 'Liechtenstein', 'li', 0, 1, NULL, NULL, NULL, NULL, NULL),
(145, 'LT', 'Lesotho', 'LS', 'LSO', 426, 'LSL,ZAR', 'Basotho', 'ls', 0, 1, NULL, NULL, NULL, NULL, NULL),
(146, 'LU', 'Luxembourg', 'LU', 'LUX', 442, 'EUR', 'Luxembourg, Luxembourgish', 'lu', 0, 1, NULL, NULL, NULL, NULL, NULL),
(147, 'LY', 'Libya', 'LY', 'LBY', 434, 'LYD', 'Libyan', 'ly', 0, 1, NULL, NULL, NULL, NULL, NULL),
(148, 'MA', 'Madagascar', 'MG', 'MDG', 450, 'MGA', 'Malagasy', 'mg', 0, 1, NULL, NULL, NULL, NULL, NULL),
(149, 'MB', 'Martinique', 'MQ', 'MTQ', 474, 'EUR', 'Martiniquais, Martinican', 'mq', 0, 1, NULL, NULL, NULL, NULL, NULL),
(150, 'MC', 'Macau', 'MO', 'MAC', 446, 'MOP', 'Macanese, Chinese', 'mo', 0, 1, NULL, NULL, NULL, NULL, NULL),
(151, 'MD', 'Moldova', 'MD', 'MDA', 498, 'MDL', 'Moldovan', 'md', 0, 1, NULL, NULL, NULL, NULL, NULL),
(152, 'MF', 'Mayotte', NULL, 'MYT', 175, 'EUR', 'Mahoran', 'yt', 0, 1, NULL, NULL, NULL, NULL, NULL),
(153, 'MG', 'Mongolia', 'MN', 'MNG', 496, 'MNT', 'Mongolian', 'mn', 0, 1, NULL, NULL, NULL, NULL, NULL),
(154, 'MH', 'Montserrat', 'MS', 'MSR', 500, 'XCD', 'Montserratian', 'ms', 0, 1, NULL, NULL, NULL, NULL, NULL),
(155, 'MI', 'Malawi', 'MW', 'MWI', 454, 'MWK', 'Malawian', 'mw', 0, 1, NULL, NULL, NULL, NULL, NULL),
(156, 'MK', 'Macedonia', 'MK', 'MKD', 807, 'MKD', 'Macedonian', 'mk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(157, 'ML', 'Mali', 'ML', 'MLI', 466, 'XOF', 'Malian, Malinese', 'ml', 0, 1, NULL, NULL, NULL, NULL, NULL),
(158, 'MN', 'Monaco', 'MC', 'MCO', 492, 'EUR', 'MonÃ©gasque, Monacan', 'mc', 0, 1, NULL, NULL, NULL, NULL, NULL),
(159, 'MO', 'Morocco', 'MA', 'MAR', 504, 'MAD', 'Moroccan', 'ma', 0, 1, NULL, NULL, NULL, NULL, NULL),
(160, 'MP', 'Mauritius', 'MU', 'MUS', 480, 'MUR', 'Mauritian', 'mu', 0, 1, NULL, NULL, NULL, NULL, NULL),
(161, 'MQ', 'Midway Islands', 'US', 'USA', 840, 'USD', 'American', 'us', 0, 1, NULL, NULL, NULL, NULL, NULL),
(162, 'MR', 'Mauritania', 'MR', 'MRT', 478, 'MRO', 'Mauritanian', 'mr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(163, 'MT', 'Malta', 'MT', 'MLT', 470, 'EUR', 'Maltese', 'mt', 0, 1, NULL, NULL, NULL, NULL, NULL),
(164, 'MU', 'Oman', 'OM', 'OMN', 512, 'OMR', 'Omani', 'om', 0, 1, NULL, NULL, NULL, NULL, NULL),
(165, 'MV', 'Maldives', 'MV', 'MDV', 462, 'MVR', 'Maldivian', 'mv', 0, 1, NULL, NULL, NULL, NULL, NULL),
(166, 'MX', 'Mexico', 'MX', 'MEX', 484, 'MXN', 'Mexican', 'mx', 0, 1, NULL, NULL, NULL, NULL, NULL),
(167, 'MY', 'Malaysia', 'MY', 'MYS', 458, 'MYR', 'Malaysian', 'my', 0, 1, NULL, NULL, NULL, NULL, NULL),
(168, 'MZ', 'Mozambique', 'MZ', 'MOZ', 508, 'MZN', 'Mozambican', 'mz', 0, 1, NULL, NULL, NULL, NULL, NULL),
(169, 'NC', 'New Caledonia', 'NC', 'NCL', 540, 'XPF', 'New Caledonian', 'nc', 0, 1, NULL, NULL, NULL, NULL, NULL),
(170, 'NE', 'Niue', 'NU', 'NIU', 570, 'NZD', 'Niuean', 'nu', 0, 1, NULL, NULL, NULL, NULL, NULL),
(171, 'NF', 'Norfolk Island', 'NF', 'NFK', 574, 'AUD', 'Norfolk Island', 'nf', 0, 1, NULL, NULL, NULL, NULL, NULL),
(172, 'NG', 'Niger', 'NE', 'NER', 562, 'XOF', 'Nigerien', 'ne', 0, 1, NULL, NULL, NULL, NULL, NULL),
(173, 'NH', 'Vanuatu', 'VU', 'VUT', 548, 'VUV', 'Ni-Vanuatu, Vanuatuan', 'vu', 0, 1, NULL, NULL, NULL, NULL, NULL),
(174, 'NI', 'Nigeria', 'NG', 'NGA', 566, 'NGN', 'Nigerian', 'ng', 0, 1, NULL, NULL, NULL, NULL, NULL),
(175, 'NL', 'Netherlands', 'NL', 'NLD', 528, 'EUR', 'Dutch, Netherlandic', 'nl', 0, 1, NULL, NULL, NULL, NULL, NULL),
(176, 'NO', 'Norway', 'NO', 'NOR', 578, 'NOK', 'Norwegian', 'no', 0, 1, NULL, NULL, NULL, NULL, NULL),
(177, 'NP', 'Nepal', 'NP', 'NPL', 524, 'NPR', 'Nepali, Nepalese', 'np', 0, 1, NULL, NULL, NULL, NULL, NULL),
(178, 'NR', 'Nauru', 'NR', 'NRU', 520, 'AUD', 'Nauruan', 'nr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(179, 'NS', 'Suriname', 'SR', 'SUR', 740, 'SRD', 'Surinamese', 'sr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(180, 'NT', 'Netherlands Antilles', 'AN', 'ANT', NULL, NULL, NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(181, 'NU', 'Nicaragua', 'NI', 'NIC', 558, 'NIO', 'Nicaraguan', 'ni', 0, 1, NULL, NULL, NULL, NULL, NULL),
(182, 'NZ', 'New Zealand', 'NZ', 'NZL', 554, 'NZD', 'New Zealand, NZ', 'nz', 0, 1, NULL, NULL, NULL, NULL, NULL),
(183, 'PA', 'Paraguay', 'PY', 'PRY', 600, 'PYG', 'Paraguayan', 'py', 0, 1, NULL, NULL, NULL, NULL, NULL),
(184, 'PC', 'Pitcairn Islands', NULL, 'PCN', 612, 'NZD', 'Pitcairn Island', 'pn', 0, 1, NULL, NULL, NULL, NULL, NULL),
(185, 'PE', 'Peru', 'PE', 'PER', 604, 'PEN', 'Peruvian', 'pe', 0, 1, NULL, NULL, NULL, NULL, NULL),
(186, 'PF', 'Paracel Islands', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(187, 'PG', 'Spratly Islands', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(188, 'PK', 'Pakistan', 'PK', 'PAK', 586, 'PKR', 'Pakistani', 'pk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(189, 'PL', 'Poland', 'PL', 'POL', 616, 'PLN', 'Polish', 'pl', 0, 1, NULL, NULL, NULL, NULL, NULL),
(190, 'PM', 'Panama', 'PA', 'PAN', 591, 'PAB,USD', 'Panamanian', 'pa', 0, 1, NULL, NULL, NULL, NULL, NULL),
(191, 'PO', 'Portugal', 'PT', 'PRT', 620, 'EUR', 'Portuguese', 'pt', 0, 1, NULL, NULL, NULL, NULL, NULL),
(192, 'PP', 'Papua New Guinea', 'PG', 'PNG', 598, 'PGK', 'Papua New Guinean, Papuan', 'pg', 0, 1, NULL, NULL, NULL, NULL, NULL),
(193, 'PS', 'Palau', 'PW', 'PLW', 585, 'USD', 'Palauan', 'pw', 0, 1, NULL, NULL, NULL, NULL, NULL),
(194, 'PU', 'Guinea-Bissau', 'GW', 'GNB', 624, 'XOF', 'Bissau-Guinean', 'gw', 0, 1, NULL, NULL, NULL, NULL, NULL),
(195, 'QA', 'Qatar', 'QA', 'QAT', 634, 'QAR', 'Qatari', 'qa', 0, 1, NULL, NULL, NULL, NULL, NULL),
(196, 'RE', 'Reunion', NULL, 'REU', 638, 'EUR', 'RÃ©unionese, RÃ©unionnais', 're', 0, 1, NULL, NULL, NULL, NULL, NULL),
(197, 'RM', 'Marshall Islands', 'MH', 'MHL', 584, 'USD', 'Marshallese', 'mh', 0, 1, NULL, NULL, NULL, NULL, NULL),
(198, 'RO', 'Romania', NULL, 'ROM', NULL, NULL, 'Romanian', NULL, 0, 1, NULL, NULL, NULL, NULL, NULL),
(199, 'RP', 'Philippines', 'PH', 'PHL', 608, 'PHP', 'Philippine, Filipino', 'ph', 0, 1, NULL, NULL, NULL, NULL, NULL),
(200, 'RQ', 'Puerto Rico', 'PR', 'PRI', 630, 'USD', 'Puerto Rican', 'pr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(201, 'RS', 'Russia', 'RU', 'RUS', 643, 'RUB', 'Russian', 'ru', 0, 1, NULL, NULL, NULL, NULL, NULL),
(202, 'RW', 'Rwanda', 'RW', 'RWA', 646, 'RWF', 'Rwandan', 'rw', 0, 1, NULL, NULL, NULL, NULL, NULL),
(203, 'SA', 'Saudi Arabia', 'SA', 'SAU', 682, 'SAR', 'Saudi, Saudi Arabian', 'sa', 0, 1, NULL, NULL, NULL, NULL, NULL),
(206, 'SE', 'Seychelles', 'SC', 'SYC', 690, 'SCR', 'Seychellois', 'sc', 0, 1, NULL, NULL, NULL, NULL, NULL),
(207, 'SF', 'South Africa', 'ZA', 'ZAF', 710, 'ZAR', 'South African', 'za', 0, 1, NULL, NULL, NULL, NULL, NULL),
(208, 'SG', 'Senegal', 'SN', 'SEN', 686, 'XOF', 'Senegalese', 'sn', 0, 1, NULL, NULL, NULL, NULL, NULL),
(209, 'SH', 'Saint Helena', 'SH', 'SHN', 654, 'SHP', 'Saint Helenian', 'sh', 0, 1, NULL, NULL, NULL, NULL, NULL),
(210, 'SI', 'Slovenia', 'SI', 'SVN', 705, 'EUR', 'Slovenian, Slovene', 'si', 0, 1, NULL, NULL, NULL, NULL, NULL),
(211, 'SL', 'Sierra Leone', 'SL', 'SLE', 694, 'SLL', 'Sierra Leonean', 'sl', 0, 1, NULL, NULL, NULL, NULL, NULL),
(212, 'SM', 'San Marino', 'SM', 'SMR', 674, 'EUR', 'Sammarinese', 'sm', 0, 1, NULL, NULL, NULL, NULL, NULL),
(213, 'SN', 'Singapore', 'SG', 'SGP', 702, 'SGD', 'Singaporean', 'sg', 0, 1, NULL, NULL, NULL, NULL, NULL),
(214, 'SO', 'Somalia', 'SO', 'SOM', 706, 'SOS', 'Somali, Somalian', 'so', 0, 1, NULL, NULL, NULL, NULL, NULL),
(215, 'SP', 'Spain', 'ES', 'ESP', 724, 'EUR', 'Spanish', 'es', 0, 1, NULL, NULL, NULL, NULL, NULL),
(216, 'ST', 'Saint Lucia', 'LC', 'LCA', 662, 'XCD', 'Saint Lucian', 'lc', 0, 1, NULL, NULL, NULL, NULL, NULL),
(217, 'SU', 'Sudan', 'SD', 'SDN', 729, 'SDG', 'Sudanese', 'sd', 0, 1, NULL, NULL, NULL, NULL, NULL),
(218, 'SV', 'Svalbard', NULL, 'SJR', NULL, NULL, NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(219, 'SW', 'Sweden', 'SE', 'SWE', 752, 'SEK', 'Swedish', 'se', 0, 1, NULL, NULL, NULL, NULL, NULL),
(221, 'SY', 'Syria', 'SY', 'SYR', 760, 'SYP', 'Syrian', 'sy', 0, 1, NULL, NULL, NULL, NULL, NULL),
(222, 'SZ', 'Switzerland', 'CH', 'CHE', 756, 'CHF', 'Swiss', 'ch', 0, 1, NULL, NULL, NULL, NULL, NULL),
(223, 'TD', 'Trinidad and Tobago', 'TT', 'TTO', 780, 'TTD', 'Trinidadian or Tobagonian', 'tt', 0, 1, NULL, NULL, NULL, NULL, NULL),
(224, 'TE', 'Tromelin Island', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(225, 'TH', 'Thailand', 'TH', 'THA', 764, 'THB', 'Thai', 'th', 0, 1, NULL, NULL, NULL, NULL, NULL),
(226, 'TI', 'Tajikistan', 'TJ', 'TJK', 762, 'TJS', 'Tajikistani', 'tj', 0, 1, NULL, NULL, NULL, NULL, NULL),
(227, 'TK', 'Turks and Caicos Islands', 'TC', 'TCA', 796, 'USD', 'Turks and Caicos Island', 'tc', 0, 1, NULL, NULL, NULL, NULL, NULL),
(228, 'TL', 'Tokelau', 'TK', 'TKL', 772, 'NZD', 'Tokelauan', 'tk', 0, 1, NULL, NULL, NULL, NULL, NULL),
(229, 'TN', 'Tonga', 'TO', 'TON', 776, 'TOP', 'Tongan', 'to', 0, 1, NULL, NULL, NULL, NULL, NULL),
(230, 'TO', 'Togo', 'TG', 'TGO', 768, 'XOF', 'Togolese', 'tg', 0, 1, NULL, NULL, NULL, NULL, NULL),
(231, 'TP', 'Sao Tome and Principe', 'ST', 'STP', 678, 'STD', 'SÃ£o TomÃ©an', 'st', 0, 1, NULL, NULL, NULL, NULL, NULL),
(232, 'TS', 'Tunisia', 'TN', 'TUN', 788, 'TND', 'Tunisian', 'tn', 0, 1, NULL, NULL, NULL, NULL, NULL),
(233, 'TT', 'East Timor', NULL, 'TMP', NULL, NULL, NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(234, 'TU', 'Turkey', 'TR', 'TUR', 792, 'TRY', 'Turkish', 'tr', 0, 1, NULL, NULL, NULL, NULL, NULL),
(235, 'TV', 'Tuvalu', 'TV', 'TUV', 798, 'AUD', 'Tuvaluan', 'tv', 0, 1, NULL, NULL, NULL, NULL, NULL),
(236, 'TW', 'Taiwan', 'TW', 'TWN', 158, '', 'Chinese, Taiwanese', 'tw', 0, 1, NULL, NULL, NULL, NULL, NULL),
(237, 'TX', 'Turkmenistan', 'TM', 'TKM', 795, 'TMT', 'Turkmen', 'tm', 0, 1, NULL, NULL, NULL, NULL, NULL),
(238, 'TZ', 'Tanzania', 'TZ', 'TZA', 834, 'TZS', 'Tanzanian', 'tz', 0, 1, NULL, NULL, NULL, NULL, NULL),
(239, 'UG', 'Uganda', 'UG', 'UGA', 800, 'UGX', 'Ugandan', 'ug', 0, 1, NULL, NULL, NULL, NULL, NULL),
(240, 'UK', 'United Kingdom', 'GB', 'GBR', 826, 'GBP', 'British, UK', 'uk', -3, 1, 1, NULL, NULL, NULL, NULL),
(241, 'UP', 'Ukraine', 'UA', 'UKR', 804, 'UAH', 'Ukrainian', 'ua', 0, 1, NULL, NULL, NULL, NULL, NULL),
(242, 'US', 'United States', 'US', 'USA', 840, 'USD', 'American', 'us', -1, 1, NULL, NULL, NULL, NULL, NULL),
(243, 'UV', 'Burkina Faso', 'BF', 'BFA', 854, 'XOF', 'BurkinabÃ©', 'bf', 0, 1, NULL, NULL, NULL, NULL, NULL),
(244, 'UY', 'Uruguay', 'UY', 'URY', 858, 'UYU', 'Uruguayan', 'uy', 0, 1, NULL, NULL, NULL, NULL, NULL),
(245, 'UZ', 'Uzbekistan', 'UZ', 'UZB', 860, 'UZS', 'Uzbekistani, Uzbek', 'uz', 0, 1, NULL, NULL, NULL, NULL, NULL),
(247, 'VE', 'Venezuela', 'VE', 'VEN', 862, 'VEF', 'Venezuelan', 've', 0, 1, NULL, NULL, NULL, NULL, NULL),
(248, 'VI', 'British Virgin Islands', 'VG', 'VGB', 92, 'USD', 'British Virgin Island', 'vg', 0, 1, NULL, NULL, NULL, NULL, NULL),
(249, 'VM', 'Vietnam', 'VN', 'VNM', 704, 'VND', 'Vietnamese', 'vn', 0, 1, NULL, NULL, NULL, NULL, NULL),
(250, 'VQ', 'Virgin Islands', 'VI', 'VIR', 850, 'USD', 'U.S. Virgin Island', 'vi', 0, 1, NULL, NULL, NULL, NULL, NULL),
(251, 'VT', 'Holy See (Vatican City)', 'VA', 'VAT', 336, 'EUR', 'Vatican', 'va', 0, 1, NULL, NULL, NULL, NULL, NULL),
(252, 'WA', 'Namibia', 'NA', 'NAM', 516, 'NAD,ZAR', 'Namibian', 'na', 0, 1, NULL, NULL, NULL, NULL, NULL),
(253, 'WE', 'West Bank', '', '', NULL, '', NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(254, 'WF', 'Wallis and Futuna', 'WF', 'WLF', 876, 'XPF', 'Wallis and Futuna, Wallisian or Futunan', 'wf', 0, 1, NULL, NULL, NULL, NULL, NULL),
(255, 'WI', 'Western Sahara', 'EH', 'ESH', 732, 'MAD', 'Sahrawi, Sahrawian, Sahraouian', 'eh', 0, 1, NULL, NULL, NULL, NULL, NULL),
(256, 'WQ', 'Wake Island', 'US', 'USA', 840, 'USD', 'American', 'us', 0, 1, NULL, NULL, NULL, NULL, NULL),
(257, 'WS', 'Samoa', NULL, 'WSM', 882, 'WST', 'Samoan', 'ws', 0, 1, NULL, NULL, NULL, NULL, NULL),
(258, 'WZ', 'Swaziland', 'SZ', 'SWZ', 748, 'SZL', 'Swazi', 'sz', 0, 1, NULL, NULL, NULL, NULL, NULL),
(259, 'YI', 'Yugoslavia', NULL, 'YUG', NULL, NULL, NULL, '', 0, 1, NULL, NULL, NULL, NULL, NULL),
(260, 'YM', 'Yemen', 'YE', 'YEM', 887, 'YER', 'Yemeni', 'ye', 0, 1, NULL, NULL, NULL, NULL, NULL),
(261, 'ZA', 'Zambia', NULL, 'ZWB', NULL, NULL, 'Zambian', NULL, 0, 1, NULL, NULL, NULL, NULL, NULL),
(262, 'ZI', 'Zimbabwe', 'ZW', 'ZWE', 716, 'ZWL', 'Zimbabwean', 'zw', 0, 1, NULL, NULL, NULL, NULL, NULL),
(263, 'RI', 'Serbia', 'RS', 'SRB', 688, 'RSD', 'Serbian', 'rs', 0, 1, NULL, NULL, NULL, NULL, NULL),
(264, 'MJ', 'Montenegro', 'ME', 'MNE', 499, 'EUR', 'Montenegrin', 'me', 0, 1, NULL, NULL, NULL, NULL, NULL);


-- --------------------------------------------------------

--
-- Table structure for table `directory`
--

DROP TABLE IF EXISTS `directory`;
CREATE TABLE IF NOT EXISTS `directory` (
  `ID` int(11) NOT NULL auto_increment,
  `categoryID` int(11) default NULL,
  `ordernum` int(11) default '0',
  `directorytype` tinyint(4) default NULL,
  `mainlocationID` int(11) default NULL,
  `registeredaddressID` int(11) default NULL,
  `isparent` tinyint(4) NOT NULL default '0',
  `parentID` int(11) default NULL,
  `userID` int(11) default NULL,
  `name` varchar(255) default NULL,
  `individual` tinyint(4) NOT NULL default '0',
    `public` tinyint(4) NOT NULL default '1',
  `description` text,
  `address1` varchar(50) default NULL,
  `address2` varchar(50) default NULL,
  `address3` varchar(50) default NULL,
  `address4` varchar(50) default NULL,
  `address5` varchar(50) default NULL,
  `postcode` varchar(10) default NULL,
  `countryID` int(11) default NULL,
  `telephone` varchar(20) default NULL,
  `fax` varchar(20) default NULL,
  `mobile` varchar(20) default NULL,
  `imageURL` varchar(100) default NULL,
  `mapurl` varchar(100) default NULL,
  `latitude` double default NULL,
  `longitude` double default NULL,
  `streetview` tinyint(4) NOT NULL default '1',
  `email` varchar(100) default NULL,
  `url` varchar(100) default NULL,
  `localwebpage` tinyint(4) NOT NULL default '0',
  `localweburl` varchar(50) default NULL,
  `statusID` tinyint(4) NOT NULL default '0',
  `favourite` tinyint(4) NOT NULL default '0',
  `locationcategoryID` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `establisheddate` date default NULL,
  `occupierofID` int(11) default NULL,
  `notes` text,
  `bankname` varchar(50) default NULL,
  `bankaccountname` varchar(50) default NULL,
  `bankaddress` text,
  `bankpostcode` varchar(10) default NULL,
  `banksortcode` varchar(10) default NULL,
  `bankaccountnumber` varchar(20) default NULL,
  `companynumber` varchar(20) default NULL,
  `vatnumber` varchar(20) default NULL,
  `charitynumber` varchar(20) default NULL,
  PRIMARY KEY  (`ID`),
   KEY `categoryID` (`categoryID`),
   KEY `mainlocationID` (`mainlocationID`),
    KEY `userID` (`userID`),
	 KEY `statusID` (`statusID`),
	 KEY `favourite` (`favourite`),
	  KEY `parentID` (`parentID`),
	   KEY `public` (`public`),
	    KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `directoryarea`
-- 

DROP TABLE IF EXISTS `directoryarea`;
CREATE TABLE IF NOT EXISTS `directoryarea` (
  `ID` int(11) NOT NULL auto_increment,
  `areaname` varchar(50) NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `directoryinarea`
-- 

DROP TABLE IF EXISTS `directoryinarea`;
CREATE TABLE IF NOT EXISTS `directoryinarea` (
  `ID` int(11) NOT NULL auto_increment,
  `directoryID` int(11) NOT NULL,
  `directoryareaID` int(11) NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `directoryID` (`directoryID`),
  KEY `directoryareaID` (`directoryareaID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `directorylocation`
-- 

DROP TABLE IF EXISTS `directorylocation`;
CREATE TABLE IF NOT EXISTS `directorylocation` (
  `ID` int(11) NOT NULL auto_increment,
  `directoryID` int(11) NOT NULL,
  `locationID` int(11) NOT NULL,
  `entrydate` date default NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `directoryID` (`directoryID`),
  KEY `locationID` (`locationID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `directorycategory`
--

DROP TABLE IF EXISTS `directorycategory`;
CREATE TABLE IF NOT EXISTS `directorycategory` (
  `ID` int(11) NOT NULL auto_increment,
  `subcatofID` int(11) NOT NULL default '0',
  `description` varchar(100) default NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `regionID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `subcatofID` (`subcatofID`),
  KEY `statusID` (`statusID`),
  KEY `regionID` (`regionID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `directoryincategory`
-- 

DROP TABLE IF EXISTS `directoryincategory`;
CREATE TABLE IF NOT EXISTS `directoryincategory` (
  `ID` int(11) NOT NULL auto_increment,
  `directoryID` int(11) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `directoryID` (`directoryID`),
  KEY `categoryID` (`categoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `directoryincategory`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `directorygallery`
-- 

DROP TABLE IF EXISTS `directorygallery`;
CREATE TABLE IF NOT EXISTS `directorygallery` (
  `ID` int(11) NOT NULL auto_increment,
  `directoryID` int(11) NOT NULL,
  `galleryID` int(11) NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `directoryID` (`directoryID`),
  KEY `galleryID` (`galleryID`)
) ENGINE=MyISAM AUTO_INCREMENT=1;


-- --------------------------------------------------------

--
-- Table structure for table `directoryprefs`
--

DROP TABLE IF EXISTS `directoryprefs`;
CREATE TABLE IF NOT EXISTS `directoryprefs` (
  `ID` tinyint(4) NOT NULL default '1',
  `approveupdates` tinyint(4) NOT NULL default '0',
  `allowsuggestions` tinyint(4) NOT NULL default '0',
  `showcontacts` tinyint(4) NOT NULL default '1',
  `contactform` tinyint(4) NOT NULL default '0',
  `directoryname` varchar(50) default 'Directory',
  `managedirectoryURL` varchar(50) default NULL,
  `accesslevel` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM;

INSERT INTO `directoryprefs` (`ID`, `approveupdates`) VALUES
(1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `directoryuser`
--

DROP TABLE IF EXISTS `directoryuser`;
CREATE TABLE IF NOT EXISTS `directoryuser` (
  `ID` int(11) NOT NULL auto_increment,
  `userID` int(11) NOT NULL default '0',
  `directoryID` int(11) NOT NULL default '0',
  `relationshiptype` tinyint(4) NOT NULL default '1',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `enddate` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `userID` (`userID`),
  KEY `directoryID` (`directoryID`),
  KEY `relationshiptype` (`relationshiptype`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `directoryrelationship`
--

DROP TABLE IF EXISTS `directoryrelationship`;
CREATE TABLE IF NOT EXISTS `directoryrelationship` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `relationshipname` varchar(50) NOT NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

--
-- Dumping data for table `directoryrelationship`
--



-- --------------------------------------------------------

--
-- Table structure for table `disability`
--

DROP TABLE IF EXISTS `disability`;
CREATE TABLE IF NOT EXISTS `disability` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `disabilityname` varchar(50) NOT NULL,
   `statusID` tinyint(4) default 1,
   `ordernum` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `statusID` (`statusID`),
  KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM  AUTO_INCREMENT=1;

--
-- Dumping data for table `disability`
--

INSERT INTO `disability` (`ID`, `disabilityname`) VALUES (1, 'Physical impairment');
INSERT INTO `disability` (`ID`, `disabilityname`) VALUES (2, 'Sensory impairment');
INSERT INTO `disability` (`ID`, `disabilityname`) VALUES (3, 'Learning impairment');
INSERT INTO `disability` (`ID`, `disabilityname`) VALUES (4, 'A mental health condition');
INSERT INTO `disability` (`ID`, `disabilityname`) VALUES (5, 'Any other disability or impairment');
INSERT INTO `disability` (`ID`, `disabilityname`) VALUES (6, 'Multiple disability or impairment');
INSERT INTO `disability` (`ID`, `disabilityname`) VALUES (7, 'Prefer not to say');
INSERT INTO `disability` (`ID`, `disabilityname`) VALUES (8, 'None');




-- --------------------------------------------------------

-- 
-- Table structure for table `discovered`
-- 

DROP TABLE IF EXISTS `discovered`;
CREATE TABLE IF NOT EXISTS `discovered` (
  `ID` int(11)  NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
   `statusID` tinyint(4) NOT NULL default '1',
   `ordernum` int(11)  NOT NULL default '0',
   `regionID` int(11) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `discovered`
-- 

INSERT INTO `discovered` (`ID`, `description`) VALUES
(1, 'Searching the web'),
(2, 'Recommendation'),
(3, 'Newspaper ad'),
(4, 'Magazine ad'),
(5, 'Other (not listed)'),
(6, 'Rather not say');





-- --------------------------------------------------------

-- 
-- Table structure for table `documents`
-- 

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
	`ID` int(11) NOT NULL auto_increment,
	`documentname` varchar(255) NOT NULL default '',
	`documentcategoryID` int(11) default NULL,
	`description` text,
	`active` tinyint(4) NOT NULL default '1',
	`filename` varchar(255) NOT NULL default '',
	`uploaddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`userID` int(11) NOT NULL default '0',
	`uploadID` int(11) default NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`type` varchar(100) default NULL,
	`lock` tinyint(4) NOT NULL default '0',
	`directoryID` int(11) default NULL,
	`ordernum` int(11) NOT NULL default '0',
	`left` int(11) NOT NULL default '0',
	`top` int(11) NOT NULL default '0',	
	PRIMARY KEY  (`ID`),
	KEY `documentcategoryID` (`documentcategoryID`),
	KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `documents`
-- 



-- --------------------------------------------------------

-- 
-- Table structure for table `documentcategory`
-- 

DROP TABLE IF EXISTS `documentcategory`;
CREATE TABLE IF NOT EXISTS `documentcategory` (
	`ID` int(11) NOT NULL auto_increment,
	`categoryname` varchar(50) NOT NULL default 'Home',
	`description` text,
	`subcatofID` int(11) default NULL,
	`addedbyID` int(11) default 0,
	`accessID` tinyint(4) default 0,
	`writeaccess` tinyint(4) default 7,
	`groupreadID` int(11) NOT NULL default  '0',
	`groupwriteID` int(11) NOT NULL default  '0',
	`regionID` int(11) NOT NULL default  '1',
	`active` tinyint(4) NOT NULL default '1',
	`ordernum` int(11) NOT NULL default '0',
	`left` int(11) NOT NULL default '0',
	`top` int(11) NOT NULL default '0',	
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `regionID` (`regionID`),
	KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `documentcategory`
-- 


INSERT INTO `documentcategory` (ID) VALUES (1);


-- 
-- Table structure for table `documentincategory`
-- 


DROP TABLE IF EXISTS `documentincategory`;
CREATE TABLE IF NOT EXISTS `documentincategory` (
	`ID` int(11) NOT NULL auto_increment,
	`documentID` int(11) default NULL,
	`categoryID` int(11) default NULL,
	`userID` int(11) default NULL, -- userID is for MyDocuments
	`ordernum` int(11) NOT NULL default '0',
	`left` int(11) NOT NULL default '0',
	`top` int(11) NOT NULL default '0',	
	`createdbyID` int(11) default 0,
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	PRIMARY KEY  (`ID`),
	KEY `documentID` (`documentID`),
	KEY `categoryID` (`categoryID`),
	KEY `userID` (`userID`),
	KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `documentincategory`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `documentshortcut`
-- 

DROP TABLE IF EXISTS `documentshortcut`;
CREATE TABLE IF NOT EXISTS `documentshortcut` (
	`ID` int(11) NOT NULL auto_increment,
	`shortcuttype` tinyint(4) NOT NULL default '1',
	`categoryID` int(11) NOT NULL,
	`shortcuttoID` int(11) default NULL,
	`shortcutname` varchar(255) default NULL,
	`shortcutURL` varchar(255) default NULL,
	`ordernum` int(11) NOT NULL default '0',
	`left` int(11) NOT NULL default '0',
	`top` int(11) NOT NULL default '0',	
	`createdbyID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `categoryID` (`categoryID`),
	KEY `shortcuttype` (`shortcuttype`),
	KEY `ordernum` (`ordernum`),
	KEY `shortcuttoID` (`shortcuttoID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

-- 
-- Table structure for table `documentprefs`
-- 

DROP TABLE IF EXISTS `documentprefs`;
CREATE TABLE IF NOT EXISTS `documentprefs` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `opennewwindow` tinyint(4) NOT NULL default '1',
  `additionalfolders` tinyint(4) NOT NULL default '0',
  `mydocuments` tinyint(4) NOT NULL default '1',
  `versioncontrol` tinyint(4) NOT NULL default '0',
  `defaultview` tinyint(4) NOT NULL default '0',
  `showsearch` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1;

-- 
-- Dumping data for table `documentprefs`
-- 

INSERT INTO `documentprefs` VALUES (1,1,0,1,0,0,0);






-- --------------------------------------------------------

-- 
-- Table structure for table `documentversion`
-- 

DROP TABLE IF EXISTS `documentversion`;
CREATE TABLE IF NOT EXISTS `documentversion` (
  `ID` int(11) NOT NULL auto_increment,
  `documentID` int(11) NOT NULL,
  `uploadID` int(11) NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,

  PRIMARY KEY  (`ID`),
  KEY `documentID` (`documentID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `documentversion`
-- 





-- --------------------------------------------------------

--
-- Table structure for table `ethnicity`
--


DROP TABLE IF EXISTS `ethnicity`;
CREATE TABLE IF NOT EXISTS `ethnicity` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `ethnicityname` varchar(50) NOT NULL,
  `statusID` tinyint(4) default 1,
   `ordernum` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `statusID` (`statusID`),
  KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM  AUTO_INCREMENT=1;

--
-- Dumping data for table `ethnicity`
--

INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (1, 'White Scottish');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (2, 'Other White British');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (3, 'White Irish');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (4, 'Other White');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (5, 'Indian');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (6, 'Pakistani');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (7, 'Other (South) Asian');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (8, 'Chinese');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (9, 'Caribbean');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (10, 'African');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (11, 'Black Scottish and other Black');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (12, 'Mixed');
INSERT INTO `ethnicity` (`ID`, `ethnicityname`) VALUES (13, 'Other');




-- --------------------------------------------------------

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
CREATE TABLE IF NOT EXISTS `event` (
  `ID` int(11) NOT NULL auto_increment,
  `eventlocationID` int(11) NOT NULL default '0',
  `userID` int(11)  default NULL,
  `allday` tinyint(4) NOT NULL default '0',
  `imageURL` varchar(100) default NULL,
  `startdatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `enddatetime` datetime default '2000-01-01 00:00:00',
  `firsteventID` int(11) default NULL,
  `recurringweekly` tinyint(4) NOT NULL default '0',
  `recurringend` datetime default NULL,
  `registration` tinyint(4) NOT NULL default '0',
  `registrationtext` text,
  `registrationURL` varchar(255) default NULL,
  `registrationmulti` tinyint(4) NOT NULL default '0',
  `registrationstart` datetime default NULL,
  `registrationend` datetime default NULL,
  `registrationmax` int(11) NOT NULL default '0',
  `registrationdob` tinyint(4) NOT NULL default '0',
  `registrationteam` tinyint(4) NOT NULL default '0',
  `registrationteamname` tinyint(4) NOT NULL default '0',
  `registrationmedical` tinyint(4) NOT NULL default '0',
  `registrationinfo` tinyint(4) NOT NULL default '0',
  `registrationpayment` tinyint(4) NOT NULL default '0',
  `registrationinvoice` tinyint(4) NOT NULL default '0',
  `registrationcost` decimal(10,2) NOT NULL default '0',
  `registrationconcession` decimal(10,2) NOT NULL default '0',
  `registrationsequential` tinyint(4) NOT NULL default '0',
  `over65` tinyint(4) NOT NULL default '0',
  `teamdiscountamount` decimal(10,2) NOT NULL default '0',
  `teamdiscounttype` tinyint(4) NOT NULL default '1',
  `teamdiscountnumber` tinyint(4) NOT NULL default '0',
  `memberdiscountamount` decimal(10,2) NOT NULL default '0',
  `memberdiscounttype` tinyint(4) NOT NULL default '1',
  `memberdiscountrank` tinyint(4) NOT NULL default '0',
  `memberdiscountgroup` tinyint(4) NOT NULL default '0',  
  `familydiscountamount` decimal(10,2) NOT NULL default '0',
  `familydiscountamounttype` tinyint(4) NOT NULL default '1',
  `familydiscountadults` tinyint(4) NOT NULL default '0',
  `familydiscountchildren` tinyint(4) NOT NULL default '0',
  `paymentinstructions` text,
  `registrationconfirmationURL` varchar(100) default NULL,
  `registrationaskjobtitle` tinyint(4) NOT NULL default '0',
  `registrationaskaddress` tinyint(4) NOT NULL default '0',
  `registrationasktelephone` tinyint(4) NOT NULL default '0',
  `registrationaskcompany` tinyint(4) NOT NULL default '0',
  `registrationtshirt` tinyint(4) NOT NULL default '0',
  `registrationtime` tinyint(4) NOT NULL default '0',
  `registrationchoosestarttime` tinyint(4) NOT NULL default '0',  
  `registrationwheelchair` tinyint(4) NOT NULL default '0',
  `registrationdiscovered` tinyint(4) NOT NULL default '0',
  `registrationalertemail` tinyint(4) NOT NULL default '1',
  `registrationfullemail` tinyint(4) NOT NULL default '0',
  `registrationemailtemplateID` int(11) NOT NULL default '0',
  `registrationdietryreq` tinyint(4) NOT NULL default '0',
  `registrationspecialreq` tinyint(4) NOT NULL default '0',
  `registrationextraquestion` text,
  `registrationextraquestion2` text,
  `registrationextraquestion3` text,
  `registrationextracompulsary`  tinyint(4) NOT NULL default '0',
  `registrationextracompulsary2`  tinyint(4) NOT NULL default '0',
  `registrationextracompulsary3`  tinyint(4) NOT NULL default '0',
  `registrationadminemail` varchar(50) default NULL,
  `registrationemail` tinyint(4) NOT NULL default '1',
  `registrationemailnumbers` tinyint(4) NOT NULL default '0',
  `registrationemailmessage` text,
  `registrationautoaccept` tinyint(4) NOT NULL default '0',
  `registrationgroupID` int(11) default NULL,
  `registrationmarketingtext` text,
  `registrationtermstext` text,
  `registrationmarketingtextshow` tinyint(4) NOT NULL default '0',
  `registrationtermstextshow` tinyint(4) NOT NULL default '0',
  `takenpartbefore` tinyint(4) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '0',
  `eventgroupID` int(11) NOT NULL default '0',
  `surveyID` int(11) NOT NULL default '0',
  `eventdetails` text,
  `rsvp` tinyint(4) NOT NULL default '0',
  `rsvpdatetime` datetime default NULL,
  `followonfromID` int(11) default NULL,
  `remindereventuseremailsent` datetime default NULL,
  `remindereventlocationemailsent` datetime default NULL,
  `remindereventlocationemail2sent` datetime default NULL,
   KEY `followonfromID` (`followonfromID`),
    KEY `surveyID` (`surveyID`),
	 KEY `eventgroupID` (`eventgroupID`),
	  KEY `statusID` (`statusID`),
	    KEY `eventlocationID` (`eventlocationID`),
		 KEY `startdatetime` (`startdatetime`),
		 KEY `enddatetime` (`enddatetime`),
		  KEY `firsteventID` (`firsteventID`),
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `eventattend`
-- 

DROP TABLE IF EXISTS `eventattend`;
CREATE TABLE IF NOT EXISTS `eventattend` (
  `ID` int(11) NOT NULL auto_increment,
  `eventID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `statusID` tinyint(4) NOT NULL default '0',
  `checkedoutdatetime` datetime default NULL,
  `checkedindatetime` datetime default NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `eventID` (`eventID`),
  KEY `userID` (`userID`),
  KEY `statusID` (`statusID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;




--
-- Table structure for table `eventcategory`
--

DROP TABLE IF EXISTS `eventcategory`;
CREATE TABLE IF NOT EXISTS `eventcategory` (
  `ID` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL default '',
  `colour` varchar(25) default NULL,
  `description` text,
  `groupID` tinyint(4) NOT NULL default '1',
  `priority` tinyint(4) NOT NULL default '1',
  `active` tinyint(4) NOT NULL default '1',
  KEY `groupID` (`groupID`),
  KEY `active` (`active`),
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `eventcategorygroup`
--

DROP TABLE IF EXISTS `eventcategorygroup`;
CREATE TABLE IF NOT EXISTS `eventcategorygroup` (
  `ID` int(11) NOT NULL auto_increment,
  `groupname` varchar(50) NOT NULL,
  `groupdescription` text,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM   AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `eventgroup`
--

DROP TABLE IF EXISTS `eventgroup`;
CREATE TABLE IF NOT EXISTS `eventgroup` (
  `ID` int(11) NOT NULL auto_increment,
  `eventtitle` varchar(255) NOT NULL,
  `eventdetails` mediumtext,
  `eventfee` decimal(10,2) NOT NULL default '0',
`venuefee` decimal(10,2) NOT NULL default '0',
  `categoryID` int(11) default NULL,
  `resourceID` int(11) default NULL,
  `directoryID` int(11) default NULL,
  `customvalue1` varchar(100) default NULL,
  `customvalue2` varchar(100) default NULL,
  `usertypeID` tinyint(4) default 0,
  `imageURL` varchar(255) default NULL,
  `attachment1` varchar(255) default NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifieddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `featured` tinyint(4) NOT NULL default '0',
  KEY `featured` (`featured`),
  KEY `statusID` (`statusID`),
  KEY `categoryID` (`categoryID`),
  KEY `resourceID` (`resourceID`),
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `eventprefs`
-- 

DROP TABLE IF EXISTS `eventprefs`;
CREATE TABLE IF NOT EXISTS `eventprefs` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `eventname` varchar(50) default 'event',
  `allowcancelregistration` tinyint(4) NOT NULL default '1',
  `accesslevel` tinyint(4) NOT NULL default '5',
  `writeaccess` tinyint(4) NOT NULL default '8',
  `registrationaccesslevel` tinyint(4) NOT NULL default '1',
  `defaultregistrationmax` int(11) NOT NULL default '0',
  `defaultregistrationteam` tinyint(4) NOT NULL default '0',
  `defaultrepeatperiod` varchar(10) NOT NULL default 'weeks',
  `daystarttime` time NOT NULL default '00:00:00',
  `dayendtime` time NOT NULL default '23:59:59',
  `customfield1` varchar(50) default NULL,
  `customfield2` varchar(50) default NULL,
  `userlistgroupID` int(11) NOT NULL default '0',
  `registrationalertemail` varchar(255) default NULL,
  `registrationalertincludelink` tinyint(4) NOT NULL default '1',
  `addeventuseremailtemplateID` int(11) NOT NULL default '0',
  `addeventlocationemailtemplateID` int(11) NOT NULL default '0',
  `canceleventuseremailtemplateID` int(11) NOT NULL default '0',
  `canceleventlocationemailtemplateID` int(11) NOT NULL default '0',
  `remindereventuseremailtemplateID` int(11) NOT NULL default '0',
  `remindereventlocationemailtemplateID` int(11) NOT NULL default '0',
  `remindereventuseremailhours` int(11) NOT NULL default '0',
  `remindereventlocationemailhours` int(11) NOT NULL default '0',
  `remindereventlocationemail2templateID` int(11) NOT NULL default '0',
  `remindereventlocationemail2hours` int(11) NOT NULL default '0',
  `text_register` varchar(255) default 'Register',
  `text_received` varchar(255) default 'Your registration for this event has been received.',
  
  
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `eventprefs`
-- 

INSERT INTO `eventprefs` (ID) VALUES (1);


-- --------------------------------------------------------

-- --------------------------------------------------------

-- 
-- Table structure for table `eventregistration`
-- 

DROP TABLE IF EXISTS `eventregistration`;
CREATE TABLE IF NOT EXISTS `eventregistration` (
  `ID` int(11) NOT NULL auto_increment,
  `eventID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `registrationnumber` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '0',
  `withregistrationID` int(11) default NULL,
  `takenpartbefore` tinyint(4) default NULL,
  `registrationteamname` varchar(50) default NULL,
  `registrationmedical` text,
  `registrationinfo` text,
  `registrationinfo2` text,
  `registrationinfo3` text,
  `registrationtshirt` tinyint(4) default NULL,
  `registrationtime` varchar(20) default NULL,
  `registrationwheelchair` tinyint(4) default NULL,
  `registrationjobtitle` varchar(50) default NULL,
  `registrationcompany` varchar(50) default NULL,
  `registrationdietryreq` text,
  `registrationspecialreq` text,
  `registrationdiscovered` tinyint(4) default NULL,
   `registrationstarttime` tinyint(4) default NULL,
   `registrationmarketing` tinyint(4) NOT NULL default '0',
  `registrationterms` tinyint(4) NOT NULL default '0',
  `paymentamount` decimal(10,2) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `eventID` (`eventID`),
  KEY `registrationnumber` (`registrationnumber`),
  KEY `userID` (`userID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `eventregstarttime`
-- 

DROP TABLE IF EXISTS `eventregstarttime`;
CREATE TABLE IF NOT EXISTS `eventregstarttime` (
  `ID` int(11) NOT NULL auto_increment,
  `eventID` int(11) NOT NULL,
  `starttime` varchar(10) NOT NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `eventID` (`eventID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


--
-- Table structure for table `eventresource`
--

DROP TABLE IF EXISTS `eventresource`;
CREATE TABLE IF NOT EXISTS `eventresource` (
  `ID` int(11) NOT NULL auto_increment,
  `categoryID` int(11) default NULL,
  `resourcename` varchar(50) NOT NULL default '',
  `description` mediumtext,
  `statusID` tinyint(4) NOT NULL default '1',
  `ordernum` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `statusID` (`statusID`),
  KEY `categoryID` (`categoryID`),
  KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;







-- 
-- Table structure for table `favourites`
-- 

DROP TABLE IF EXISTS `favourites`;
CREATE TABLE IF NOT EXISTS `favourites` (
  `ID` int(11) NOT NULL auto_increment,
  `url` varchar(255) NOT NULL,
  `pagetitle` varchar(100) NOT NULL,
  `userID` int(11) NOT NULL,
  `newwindow` tinyint(4) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `regionID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `userID` (`userID`),
  KEY `statusID` (`statusID`),
  KEY `regionID` (`regionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `favourites`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `flipbook`
-- 

DROP TABLE IF EXISTS `flipbook`;
CREATE TABLE IF NOT EXISTS `flipbook` (
	`ID` int(11) NOT NULL auto_increment,
	`flipbookname` varchar(50) NOT NULL,
	`galleryID` int(11) NOT NULL,
	`categoryID` int(11) default NULL,
	`downloadURL` varchar(255) default NULL,
	`ordernum` int(11) NOT NULL default '0',
	`left` int(11) NOT NULL default '0',
	`top` int(11) NOT NULL default '0',	
	`createdbyID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`),
	KEY `galleryID` (`galleryID`),
	KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `flipbook`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `font`
-- 

DROP TABLE IF EXISTS `font`;
CREATE TABLE IF NOT EXISTS `font` (
	`ID` int(11) NOT NULL auto_increment,
	`fontname` varchar(150) NOT NULL,
	`fontcssURL` varchar(255) NOT NULL,
	`createdbyID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `font`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `form`
-- 

DROP TABLE IF EXISTS `form`;
CREATE TABLE IF NOT EXISTS `form` (
	`ID` int(11) NOT NULL auto_increment,
	`formname` varchar(50) NOT NULL,
	`pagetitle` varchar(255) default NULL,
	`metadescription` text,
	`email` varchar(100) default NULL,
	`sendemail` tinyint(4) NOT NULL default '0',
	`emailsubject` varchar(100) default NULL,
	`emailmessage` text,
	`confirmationpage` mediumtext,
	`header` mediumtext,
	`footer` mediumtext,
	`confirmationpageURL` varchar(255) default NULL,
	`ordernum` int(11) NOT NULL default '0',
	`blockwww` tinyint(4) NOT NULL default '0',
	`noindex` tinyint(4) NOT NULL default '0',
	`captcha` tinyint(4) NOT NULL default '0',
	`adduser` tinyint(4) NOT NULL default '0',
	`showlabels` tinyint(4) NOT NULL default '1',
	`showplaceholders` tinyint(4) NOT NULL default '0',
	`regionID` int(11) NOT NULL,	
	`groupID` int(11) default NULL,
	`accessrankID` tinyint(4) default '0',
	`loginsignup` tinyint(4) default '0',
	`createdbyID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	`text_submit` varchar(50) default 'Submit...',
	`text_enter_value` varchar(255) default 'A value is required in this form field',
	`text_select_value` varchar(255) default 'Please select a value',
	`text_check_value` varchar(255) default 'Please check box',
	`text_required` varchar(50) default 'Required item',
	PRIMARY KEY  (`ID`),
	KEY `regionID` (`regionID`),
	KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `form`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `formfield`
-- 

DROP TABLE IF EXISTS `formfield`;
CREATE TABLE IF NOT EXISTS `formfield` (
	`ID` int(11) NOT NULL auto_increment,
	`formID` int(11) NOT NULL,
	`formfieldname` text,
	`formfieldplaceholder` text default NULL,
	`formfieldtype` tinyint(4) NOT NULL,
	`formfieldspecialtype` tinyint(4) NOT NULL default '0',
	`required` tinyint(4) default '0',
	`showinlistview` tinyint(4) default '0',
	`halfwidth` tinyint(4) default '0',
	`addverifyfield` tinyint(4) default '0',
	`encryptfield` tinyint(4) default '0',	
	`validate` tinyint(4) default '0',
	`ordernum` int(11) NOT NULL default '0',		
	`createdbyID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`),
	KEY `formID` (`formID`),
	KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `formfield`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `formfieldchoice`
-- 

DROP TABLE IF EXISTS `formfieldchoice`;
CREATE TABLE IF NOT EXISTS `formfieldchoice` (
	`ID` int(11) NOT NULL auto_increment,
	`formfieldID` int(11) NOT NULL,
	`formfieldchoicename` varchar(50) NOT NULL,
	`ordernum` int(11) NOT NULL default '0',		
	`createdbyID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`),
	KEY `formfieldID` (`formfieldID`),
	KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `formfieldchoice`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `formresponse`
-- 

DROP TABLE IF EXISTS `formresponse`;
CREATE TABLE IF NOT EXISTS `formresponse` (
	`ID` int(11) NOT NULL auto_increment,		
	`formID` int(11) default NULL,
	`createdbyID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `formresponse`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `formfieldresponse`
--  BLOB intead of TEXT required to store AES ENCRYPTED DATA

DROP TABLE IF EXISTS `formfieldresponse`;
CREATE TABLE IF NOT EXISTS `formfieldresponse` (
	`ID` int(11) NOT NULL auto_increment,
	`formresponseID` int(11) NOT NULL,
	`formfieldID` int(11) NOT NULL,
	`formfieldchoiceID` int(11) default NULL,
	`formfieldtextanswer` blob,
	PRIMARY KEY  (`ID`),
	KEY `formresponseID` (`formresponseID`),
	KEY `formfieldID` (`formfieldID`),
	KEY `formfieldchoiceID` (`formfieldchoiceID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `formfieldresponse`
-- 

-- --------------------------------------------------------

--
-- Table structure for table `forumcomment`
--

DROP TABLE IF EXISTS `forumcomment`;
CREATE TABLE IF NOT EXISTS `forumcomment` (
  `ID` int(11) NOT NULL auto_increment,
  `topicID` int(11) NOT NULL default '0',
  `imageURL` varchar(100) default NULL,
  `emailme` tinyint(4) NOT NULL default '0',
  `postedbyID` int(11) NOT NULL default '1',
  `posteddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `statusID` tinyint(4) NOT NULL default '1',
  `message` text,
  `IPaddress` varchar(15) default NULL,
  `rating` tinyint(4) default NULL,
  `modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `topicID` (`topicID`),
  KEY `statusID` (`statusID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `forumsection`
--

DROP TABLE IF EXISTS `forumsection`;
CREATE TABLE IF NOT EXISTS `forumsection` (
  `ID` int(11) NOT NULL auto_increment,
  `sectionname` varchar(100) default NULL,
  `sectiondescription` text,
  `imageURL` varchar(100) default NULL,
  `accesslevel` tinyint(4) NOT NULL default '0',
  `rankwrite` tinyint(4) NOT NULL default '0',
  `groupread` tinyint(4) NOT NULL default '0',
  `groupwrite` tinyint(4) NOT NULL default '0',
  `regionID` int(11) NOT NULL default '1',
  `statusID` tinyint(4) NOT NULL default '1',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `moderatorID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `forumtopic`
--

DROP TABLE IF EXISTS `forumtopic`;
CREATE TABLE IF NOT EXISTS `forumtopic` (
  `ID` int(11) NOT NULL auto_increment,
  `topic` varchar(100) default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `accesslevel` tinyint(4) default NULL,
  `moderatorID` int(11) NOT NULL default '0',
  `regionID` tinyint(4) default '1',
  `newsID` int(11) default NULL,
  `articleID` int(11) default NULL,
  `productID` int(11) default NULL,
   `eventID` int(11) default NULL,
  `sectionID` tinyint(4) NOT NULL default '1',
  `viewcount` int(11) default '0',
  `editorpick` tinyint(4) default '0',
  PRIMARY KEY  (`ID`),
  KEY `productID` (`productID`),
   KEY `newsID` (`newsID`),
    KEY `eventID` (`eventID`),
	KEY `regionID` (`regionID`),
	KEY `articleID` (`articleID`),
	KEY `sectionID` (`sectionID`),
  KEY `statusID` (`statusID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `furniture`
--

DROP TABLE IF EXISTS `furniture`;
CREATE TABLE IF NOT EXISTS `furniture` (
  `ID` int(11) NOT NULL auto_increment,
  `furniturename` varchar(50) NOT NULL,
  `furnituretext` text,
  `furniturelink` varchar(100) default NULL,
 `appearsonURL` varchar(100) default NULL,
  `newwindow` tinyint(4) NOT NULL default '0',
  `imageURL` varchar(100) default NULL,
  `width_px` int(11) default NULL,
  `height_px` int(11) default NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `regionID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`),
  KEY `statusID` (`statusID`),
   KEY `regionID` (`regionID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `groupemail`
-- 

DROP TABLE IF EXISTS `groupemail`;
CREATE TABLE IF NOT EXISTS `groupemail` (
	`ID` int(11) NOT NULL auto_increment,
	`regionID` int(11) NOT NULL default '1',
	`startdatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`enddatetime` datetime default NULL,
	`usertypeID` tinyint(4) NOT NULL default '1',
	`usergroupID` int(11) NOT NULL default '0',
	`trackclicks` int(11) NOT NULL default '1',
	`readcount` int(11) NOT NULL default '0',
	`clickcount` int(11) NOT NULL default '0',
	`from` varchar(100)  default NULL,
	`fromname` varchar(50)  default NULL,
	`subject` varchar(100)  default NULL,
	`message` text,
	`head` text,
	`bodytag` varchar(255) default NULL,
	`defaultfirstname` varchar(50) default 'Reader',
	`html` mediumtext,
	`templateID` tinyint(4) NOT NULL default '0',
	`ignoreoptout` tinyint(4) NOT NULL default '0',
	`showunsubscribe` tinyint(4) NOT NULL default '1',
	`viewonline` tinyint(4) NOT NULL default '1',
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`modifiedbyID` int(11)  default NULL,
	`modifieddatetime` datetime  default NULL,
	`active` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`),
	KEY `startdatetime` (`startdatetime`),
	KEY `usertypeID` (`usertypeID`),
	KEY `usergroupID` (`usergroupID`),
	KEY `ignoreoptout` (`ignoreoptout`),
	KEY `regionID` (`regionID`),
	KEY `active` (`active`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `groupemailclick`
--

DROP TABLE IF EXISTS `groupemailclick`;
CREATE TABLE IF NOT EXISTS `groupemailclick` (
  `ID` int(11) NOT NULL auto_increment,
  `groupemailID` int(11) default NULL,
  `userID` int(11) default NULL,
  `url` text,
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `groupemailID` (`groupemailID`),
  KEY `userID` (`userID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

--
-- Dumping data for table `groupemailclick`
--


-- --------------------------------------------------------

-- 
-- Table structure for table `groupemaillist`
-- 

DROP TABLE IF EXISTS `groupemaillist`;
CREATE TABLE IF NOT EXISTS `groupemaillist` (
	`ID` int(11) NOT NULL auto_increment,
	`groupemailID` int(11) NOT NULL,
	`userID` int(11) NOT NULL,
	`sent` tinyint(4) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `groupemailID` (`groupemailID`),
	KEY `userID` (`userID`),
	KEY `sent` (`sent`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `groupemailoptoutlog`
-- 

DROP TABLE IF EXISTS `groupemailoptoutlog`;
CREATE TABLE IF NOT EXISTS `groupemailoptoutlog` (
  `ID` int(11) NOT NULL auto_increment,
  `email` varchar(100) NOT NULL default '',
  `createdbyID` int(11) default NULL,
  `createddatetime` datetime default NULL, 
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `groupemailoptoutlog`
-- 



-- --------------------------------------------------------

-- 
-- Table structure for table `groupemailtemplate`
-- 

DROP TABLE IF EXISTS `groupemailtemplate`;
CREATE TABLE IF NOT EXISTS `groupemailtemplate` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `regionID` int(11) NOT NULL default '1',
  `templateversion` tinyint(4) NOT NULL default '1',
  `templatename` varchar(50) NOT NULL,
  `templatesubject` varchar(50) default NULL,
  `templatemessage` text,
  `templatehead` text,
  `templatebodytag` varchar(255)  default NULL,
  `templateHTML` mediumtext,
  `templatedefaultfirstname` varchar(50) default 'Reader',
  `smsmessage` mediumtext,
  `viewonline` tinyint(4) NOT NULL default '1',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;




-- --------------------------------------------------------

-- 
-- Table structure for table `help`
-- 

DROP TABLE IF EXISTS `help`;
CREATE TABLE IF NOT EXISTS `help` (
  `ID` int(11) NOT NULL auto_increment,
  `sectionID` int(11) NOT NULL default '0',
  `subsection` float default NULL,
  `title` varchar(50) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `imageurl` varchar(100) default NULL,
  `pageurl` varchar(50) default NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `helpsection`
-- 

DROP TABLE IF EXISTS `helpsection`;
CREATE TABLE IF NOT EXISTS `helpsection` (
  `ID` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL default '',
  `accesslevel` tinyint(4) NOT NULL default '0',
  `statusID` tinyint(4) NOT NULL default '1',
    `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `helpsection`
-- 


-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
CREATE TABLE IF NOT EXISTS `invoice` (
  `ID` int(11) NOT NULL auto_increment,
  `invoicenumber` varchar(50) default NULL,
  `invoicedate` date NOT NULL,
  `invoiceamount` decimal(10,2)  DEFAULT '0',
  `paiddate` date default NULL,
  `paidamount` decimal(10,2)  DEFAULT '0',
  `recipientuserID` int(11)  default '0',
  `lastsent` date DEFAULT NULL,
  `numberreminders` tinyint(4) NOT NULL DEFAULT '0',
  `statusID` tinyint(4) NOT NULL DEFAULT '1',
  `createddatetime` datetime default '2000-01-01 00:00:00',
  `createdbyID` int(11)  default '0',
  `modifiedbyID` int(11) DEFAULT NULL,
  `modifieddatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM   AUTO_INCREMENT=1  ;

-- --------------------------------------------------------

--
-- Table structure for table `invoiceitem`
--

DROP TABLE IF EXISTS `invoiceitem`;
CREATE TABLE IF NOT EXISTS `invoiceitem` (
  `ID` int(11) NOT NULL auto_increment,
  `invoiceID` int(11) NOT NULL,
  `itemnumber` varchar(50) NOT NULL,
  `itemdescription` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `vatrate` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;




-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_admin_log`
-- 

DROP TABLE IF EXISTS `isearch_admin_log`;
CREATE TABLE IF NOT EXISTS `isearch_admin_log` (
  `id` int(11) NOT NULL auto_increment,
  `msg` text,
  `time` int(11) default NULL,
  `regionID` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `isearch_admin_log`
-- 



-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_alts`
-- 

DROP TABLE IF EXISTS `isearch_alts`;
CREATE TABLE IF NOT EXISTS `isearch_alts` (
  `id` int(11) NOT NULL auto_increment,
  `keyword` varchar(255) default NULL,
  `alternative` varchar(255) default NULL,
  `redirect` tinyint(4) default '0',
  `regionID` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `isearch_alts`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_info`
-- 

DROP TABLE IF EXISTS `isearch_info`;
CREATE TABLE IF NOT EXISTS `isearch_info` (
  `id` int(11) NOT NULL default '0',
  `admin_email` varchar(255) default NULL,
  `aggressive_link_search` tinyint(4) default '0',
  `allow_colons` tinyint(4) default '0',
  `allow_dashes` tinyint(4) default '0',
  `dir_redirect_handling` int(11) default '1',
  `directory_handling` int(11) default '2',
  `basic_authorization` varchar(255) default NULL,
  `char_set` varchar(255) default NULL,
  `char_set_8_bit` tinyint(4) default '1',
  `check_empty_search` tinyint(4) default '1',
  `description_style` tinyint(4) default '1',
  `display_strip_query` tinyint(4) default '0',
  `error_reporting` int(11) default '1',
  `extra_link_display` tinyint(4) default '1',
  `follow_frames` tinyint(4) default '1',
  `form_show_advanced` tinyint(4) default '1',
  `form_show_groups` tinyint(4) default '1',
  `form_show_partial` tinyint(4) default '1',
  `file_redirect_handling` int(11) default '1',
  `heading_rank` int(11) default '3',
  `hide_powered_by` tinyint(4) default '0',
  `hide_regexp` varchar(255) NOT NULL default '',
  `highlight_results` tinyint(4) default '1',
  `javascript_link_search` tinyint(4) default '0',
  `ignore_image_alt_tags` tinyint(4) default '0',
  `keep_cache` tinyint(4) default '0',
  `keyword_rank` int(11) default '10',
  `lang_name` varchar(255) default NULL,
  `log_echo_level` int(11) default '3',
  `log_level` int(11) default '3',
  `log_searches` tinyint(4) default '1',
  `match_score` int(11) default '2',
  `max_displayed_description_length` int(11) default '0',
  `max_displayed_title_length` int(11) default '0',
  `max_displayed_url_length` int(11) default '40',
  `max_execution_time` int(11) default '300',
  `max_file_size` int(11) default '65536',
  `max_pages` int(11) default '20',
  `msword_exec` varchar(255) default NULL,
  `msword_support` int(11) default '0',
  `notify_updates` tinyint(4) default '0',
  `online_id` varchar(255) default NULL,
  `pdf_exec` varchar(255) default NULL,
  `pdf_support` int(11) default '0',
  `prevnext_type` int(11) default '2',
  `prevnext_num` int(11) default '10',
  `proxy_enable` tinyint(4) default '0',
  `proxy_host` varchar(255) NOT NULL default '',
  `proxy_pass` varchar(255) NOT NULL default '',
  `proxy_port` int(11) default '8080',
  `proxy_user` varchar(255) NOT NULL default '',
  `reading_mechanism` tinyint(4) default '0',
  `replace_regexp` varchar(255) NOT NULL default '',
  `results_frame` varchar(255) default NULL,
  `results_per_page` int(11) default '10',
  `search_all` tinyint(4) default '0',
  `search_box_width` int(11) default '40',
  `search_help_link` tinyint(4) default '1',
  `search_internet` tinyint(4) default '1',
  `search_log_email_days` int(11) default '0',
  `search_partial` tinyint(4) default '0',
  `show_admin_tooltips` tinyint(4) default '1',
  `show_size` tinyint(4) default '1',
  `show_time` tinyint(4) default '1',
  `show_title` tinyint(4) default '1',
  `sitemap_type` tinyint(4) default '4',
  `spider_delay` int(11) default '0',
  `soundex` tinyint(4) default '2',
  `start_urls` longtext,
  `stop_words_length` tinyint(4) default '2',
  `style_name` varchar(255) default NULL,
  `suggestions` tinyint(4) default '1',
  `tmpdir` varchar(255) default NULL,
  `target_frame` varchar(255) default NULL,
  `test_mode` tinyint(4) default '0',
  `title_rank` int(11) default '10',
  `url_rank` int(11) default '0',
  `url_replace` varchar(255) default NULL,
  `url_search` varchar(255) default NULL,
  `word_rank` int(11) default '1',
  `www_option` tinyint(4) default '1',
  `search_log_last_emailed` int(11) default '0',
  `update_last_checked` int(11) default '0',
  `update_last_version` varchar(255) default NULL,
  `allowed_ext` text,
  `allowed_urls` longtext,
  `allowed_urls_beginning` longtext,
  `exclude_urls` longtext,
  `exclude_urls_beginning` longtext,
  `groups` longtext,
  `remove_get_vars` longtext,
  `stop_words` longtext,
  `strip_defaults` text,
  `top_searches` int(11) default '20',
  `total_searches` tinyint(4) default '1',
  `last_searches` int(11) default '20',
  `robots_domains` longtext,
  `robots_excludes` longtext,
  `last_update` int(11) default '0',
  
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `isearch_info`
-- 

INSERT INTO `isearch_info` VALUES (1, '', 0, 0, 0, 1, 2, NULL, 'utf-8', 1, 1, 2, 0, 1, 1, 1, 1, 1, 1, 1, 3, 1, '', 1, 0, 0, 0, 10, 'english', 3, 3, 1, 0, 0, 0, 40, 300, 65536, 20, NULL, 0, 0, NULL, NULL, 0, 2, 10, 0, '', '', 8080, '', 2, '', '_self', 10, 0, 40, 0, 1, 1, 0, 1, 0, 0, 1, 4, 0, 2, 'http://www.fullbhuna.com/', 2, 'default', 1, '/tmp', 'isearch', 0, 10, 0, NULL, NULL, 1, 1, 1162919313, 17798400, '2.15', 'php php3 php4 html htm shtml dhtml asp pl cgi', NULL, 'http://www.fullbhuna.com/', NULL, '', NULL, 'PHPSESSID', '', '', 20, 1, 20, 'www.fullbhuna.com', '', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_links`
-- 

DROP TABLE IF EXISTS `isearch_links`;
CREATE TABLE IF NOT EXISTS `isearch_links` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(255) default NULL,
  `keywords` text,
  `description` text,
  `title` text,
  `regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`),
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `isearch_links`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_links_words`
-- 

DROP TABLE IF EXISTS `isearch_links_words`;
CREATE TABLE IF NOT EXISTS `isearch_links_words` (
  `id` int(11) NOT NULL,
  `word` varchar(100) default NULL,
  `score` int(11) default NULL,
  `regionID` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `regionID` (`regionID`),
  KEY `word` (`word`)

) ENGINE=MyISAM ;

-- 
-- Dumping data for table `isearch_links_words`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_new`
-- 

DROP TABLE IF EXISTS `isearch_new`;
CREATE TABLE IF NOT EXISTS `isearch_new` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(150) default NULL,
  `description` longtext,
  `stripped_body` longtext,
  `words` longtext,
  `title` text,
  `state` varchar(255) default NULL,
  `temp_referrer_id` int(11) default NULL,
  `referrer_id` int(11) default '0',
  `cache` longtext,
  `size` int(11) default '0',
  `base` varchar(255) default NULL,
  `sig` varchar(255) default NULL,
  `priority` float default '-1',
  `lastmod` int(11) default NULL,
  `changefreq` varchar(255) default NULL,
  `regionID` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `regionID` (`regionID`),
  KEY `url` (`url`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `isearch_new`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_search_log`
-- 

DROP TABLE IF EXISTS `isearch_search_log`;
CREATE TABLE IF NOT EXISTS `isearch_search_log` (
  `id` int(11) NOT NULL auto_increment,
  `search_term` text,
  `time` int(11) default NULL,
  `matches` int(11) default NULL,
  `checked` int(11) default '0',
  `regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`),
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `isearch_search_log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_spider_log`
-- 

DROP TABLE IF EXISTS `isearch_spider_log`;
CREATE TABLE IF NOT EXISTS `isearch_spider_log` (
  `id` int(11) NOT NULL auto_increment,
  `msg` text,
  `regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`),
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `isearch_spider_log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_urls`
-- 

DROP TABLE IF EXISTS `isearch_urls`;
CREATE TABLE IF NOT EXISTS `isearch_urls` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(150) default NULL,
  `description` longtext,
  `stripped_body` longtext,
  `words` longtext,
  `title` text,
  `state` varchar(255) default NULL,
  `temp_referrer_id` int(11) default NULL,
  `referrer_id` int(11) default '0',
  `cache` longtext,
  `size` int(11) default '0',
  `base` varchar(255) default NULL,
  `sig` varchar(255) default NULL,
  `priority` float default '-1',
  `lastmod` int(11) default NULL,
  `changefreq` varchar(255) default NULL,
 `regionID` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `regionID` (`regionID`),
  KEY `state` (`state`),
  KEY `size` (`size`),
  KEY `sig` (`sig`),
  KEY `url` (`url`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `isearch_urls`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_words`
-- 

DROP TABLE IF EXISTS `isearch_words`;
CREATE TABLE IF NOT EXISTS `isearch_words` (
  `word` varchar(150) default NULL,
  `score` int(11) default NULL,
  `id` int(11) default NULL,
  `regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`),
  KEY `word` (`word`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `isearch_words`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `isearch_words_new`
-- 

DROP TABLE IF EXISTS `isearch_words_new`;
CREATE TABLE IF NOT EXISTS `isearch_words_new` (
  `word` varchar(150) default NULL,
  `score` int(11) default NULL,
  `id` int(11) default NULL,
  `regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`),
  KEY `word` (`word`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `isearch_words_new`
-- 

-- --------------------------------------------------------

--
-- Table structure for table `keywordlinks`
--

DROP TABLE IF EXISTS `keywordlinks`;
CREATE TABLE IF NOT EXISTS `keywordlinks` (
  `ID` int(11) NOT NULL auto_increment,
  `linkkeywords` varchar(255) binary NOT NULL,
  `linkURL` varchar(255) binary NOT NULL,
  `linktitle` varchar(255) binary default NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` date NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` date default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `regionID` int(11) NOT NULL default '1',
  PRIMARY KEY  (`ID`),
  KEY `statusID` (`statusID`),
  KEY `regionID` (`regionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `keywordlinks`
--


-- 
-- Table structure for table `likes`
-- 

DROP TABLE IF EXISTS `likes`;
CREATE TABLE IF NOT EXISTS `likes` (
  `ID` int(11) NOT NULL auto_increment,
  `newsID` int(11) NOT NULL default '0', 
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`),
   KEY `newsID` (`newsID`),
    KEY `createdbyID` (`createdbyID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
-- --------------------------------------------------------



-- 
-- Table structure for table `location`
-- 

DROP TABLE IF EXISTS `location`;
CREATE TABLE IF NOT EXISTS `location` (
  `ID` int(11) NOT NULL auto_increment,
  `locationcode` varchar(10) default NULL,
  `userID` int(11) default NULL,
  `categoryID` tinyint(4) NOT NULL default '0',
  `public` tinyint(4) NOT NULL default '0',
  `locationname` varchar(100) default NULL,
  `locationtype` tinyint(4) NOT NULL default '0',
  `description` text,
  `address1` varchar(60) default NULL,
  `address2` varchar(60) default NULL,
  `address3` varchar(60) default NULL,
  `address4` varchar(60) default NULL,
  `address5` varchar(60) default NULL,
  `postcode` varchar(10) default NULL,
  `countryID` int(11) default NULL,
  `regionID` int(11) NOT NULL default '1',
  `telephone1` varchar(50) default NULL,
  `telephone2` varchar(50) default NULL,
  `telephone3` varchar(50) default NULL,
  `fax` varchar(20) default NULL,
  `imageURL` varchar(100) default NULL,
  `mapURL` varchar(255) default NULL,
  `locationURL` varchar(100) default NULL,
    `locationemail` varchar(50) default NULL,
  `latitude` double default NULL,
  `longitude` double default NULL,
  `active` tinyint(4) NOT NULL default '1',
  `createdbyID` int(11) default NULL,
  `createddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
   KEY `userID` (`userID`),
   KEY `categoryID` (`userID`),
   KEY `public` (`public`),
   KEY `active` (`active`),
    KEY `postcode` (`postcode`),
    KEY `locationtype` (`locationtype`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `location`
-- 

-- --------------------------------------------------------

--
-- Table structure for table `locationcategory`
--

CREATE TABLE IF NOT EXISTS `locationcategory` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `categoryname` varchar(50) NOT NULL default '',
  `statusID` tinyint(4) NOT NULL default '1',
  `subcatofID` int(11) NOT NULL default '0',
  `regionID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;




-- --------------------------------------------------------

--
-- Table structure for table `locationprefs`
--

DROP TABLE IF EXISTS `locationprefs`;
CREATE TABLE IF NOT EXISTS `locationprefs` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `publicaccess` tinyint(4) NOT NULL default '1',
  `locationdescriptor` varchar(20) NOT NULL default 'location',
  `postcodecheckerkey` varchar(20) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

--
-- Dumping data for table `locationprefs`
--

INSERT INTO `locationprefs` (`ID`, `publicaccess`) VALUES
(1, 1);



-- --------------------------------------------------------

-- 
-- Table structure for table `locationuser`
-- 

DROP TABLE IF EXISTS `locationuser`;
CREATE TABLE IF NOT EXISTS `locationuser` (
  `ID` int(11) NOT NULL auto_increment,
  `locationID` int(11) NOT NULL,
  `relationshipID` int(11) default NULL,
  `userID` int(11) NOT NULL,
  `daysofweek` varchar(7) default NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `locationID` (`locationID`),
  KEY `relationshipID` (`relationshipID`),
  KEY `userID` (`userID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `locationuser`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `locationuserrelationship`
-- 

DROP TABLE IF EXISTS `locationuserrelationship`;
CREATE TABLE IF NOT EXISTS `locationuserrelationship` (
  `ID` int(11) NOT NULL auto_increment,
  `relationship` varchar(50) NOT NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `locationuser`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `mailaccount`
-- 

DROP TABLE IF EXISTS `mailaccount`;
CREATE TABLE IF NOT EXISTS `mailaccount` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `regionID` int(11) NOT NULL default '1',
  `accountname` varchar(50) NOT NULL,
  `mailserver` varchar(100) NOT NULL,
  `port` int(11) NOT NULL,
   `usessl` tinyint(4) NOT NULL default '0',
    `protocol` tinyint(4) NOT NULL default '1',
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `bounceaccount` tinyint(4) NOT NULL default '0',
   `deletemail` tinyint(4) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL,
  `createddatetime` date NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` date default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `mailaccount`
-- 





-- --------------------------------------------------------

-- 
-- Table structure for table `mailattachments`
-- 

DROP TABLE IF EXISTS `mailattachments`;
CREATE TABLE IF NOT EXISTS `mailattachments` (
  `ID` int(11) NOT NULL auto_increment,
  `correspondenceID` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `mimetype` varchar(50) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `mailfolders`
-- 

DROP TABLE IF EXISTS `mailfolders`;
CREATE TABLE IF NOT EXISTS `mailfolders` (
  `ID` int(11) NOT NULL auto_increment,
  `foldername` varchar(100) NOT NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `mailprefs`
-- 

DROP TABLE IF EXISTS `mailprefs`;
CREATE TABLE IF NOT EXISTS `mailprefs` (
	`ID` tinyint(4) NOT NULL default '1',
	`contactformactive` tinyint(4) NOT NULL default '1',
	`contactpageURL` varchar(100) default NULL,
	`formbuilderID` int(11) default NULL,
	`useCaptcha` tinyint(4) NOT NULL default '0',
	`captchatype` tinyint(4) NOT NULL default '1',
	`allowattachments` tinyint(4) NOT NULL default '0',
	`showemail` tinyint(4) NOT NULL default '0',
	`showcontact` tinyint(4) NOT NULL default '1',
	`inperson` tinyint(4) NOT NULL default '1',
	`enableGroupEmail` tinyint(4) NOT NULL default '0',
	`enableletters` tinyint(4) NOT NULL default '0',
	`noreplyemail` varchar(100) default NULL,
	`replytoemail` varchar(100) default NULL,	
	`envelopefrom` varchar(100) default NULL,
	`webdevelopersURL` varchar(50) default NULL,
	`confirmationURL` varchar(255) default NULL,
	`lastViewed` datetime default NULL,
	`livechat` tinyint(4) NOT NULL default '0',
	`askcompany` tinyint(4) NOT NULL default '1',
	`companyrequired` tinyint(4) NOT NULL default '0',
	`askname` tinyint(4) NOT NULL default '1',
	`namerequired` tinyint(4) NOT NULL default '1',
	`askdob` tinyint(4) NOT NULL default '0',
	`dobrequired` tinyint(4) NOT NULL default '0',
	`askemail` tinyint(4) NOT NULL default '1',
	`emailrequired` tinyint(4) NOT NULL default '0',
	`emailconfirm` tinyint(4) NOT NULL default '0',
	`asktelephone` tinyint(4) NOT NULL default '0',
	`telephonerequired` tinyint(4) NOT NULL default '0',
	`askaddress` tinyint(4) NOT NULL default '0',
	`addressrequired` tinyint(4) NOT NULL default '0',
	`askdiscovered` tinyint(4) NOT NULL default '0',
	`discoveredrequired` tinyint(4) NOT NULL default '0',
	`asksubject` tinyint(4) NOT NULL default '0',
	`askmessage` tinyint(4) NOT NULL default '1',
	`messagerequired` tinyint(4) NOT NULL default '0',
	`messagelabel` varchar(50) default 'Message',
	`askcustom` tinyint(4) NOT NULL default '0',
	`customrequired` tinyint(4) NOT NULL default '0',
	`text_custom` varchar(50) default 'Custom',
	`showmap` tinyint(4) NOT NULL default '1',
	`addtocontacts` tinyint(4) NOT NULL default '0',
	`contacttitle` varchar(255) default 'Contact Us',
	`contactheader` mediumtext,
	`contactfooter` mediumtext,
	`contactmetadescription` text,
	`responsesubject` varchar(255) default 'Thank you!',
	`responsemessage` text,
	`defaultsubject` varchar(50) default 'Web Contact Form',
	`askrespondby` tinyint(4) NOT NULL default '0',
	`html` tinyint(4) NOT NULL default '0',
	`autoreply` tinyint(4) NOT NULL default '0',
	`feedbackpost` tinyint(4) NOT NULL default '8',
	`feedbackread` tinyint(4) NOT NULL default '10',
	`feedbackemail` varchar(100) default 'hello@digdex.co.uk',
	`mailchimpapi` varchar(50) default NULL,
	`mailchimplistid` varchar(50) default NULL,
	`mailgunapi` varchar(50) default NULL,
	`mailgundomain` varchar(50) default NULL,
	`mailgunregion` tinyint(4) default 1,
	`sendcountday` date NOT NULL  default '1970-01-01',
	`sendcount` int(11) NOT NULL default '0',
	`text_callback_time` varchar(255) default NULL,
	`text_callback_option_1` varchar(30) default NULL,
	`text_callback_option_2` varchar(30) default NULL,
	`text_callback_option_3` varchar(30) default NULL,
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `mailprefs`
-- 


INSERT INTO `mailprefs` (ID) VALUES (1);

-- --------------------------------------------------------

-- 
-- Table structure for table `mailrecipient`
-- 

DROP TABLE IF EXISTS `mailrecipient`;
CREATE TABLE IF NOT EXISTS `mailrecipient` (
  `ID` int(11) NOT NULL auto_increment,
  `recipient` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `responsesubject` varchar(100) default NULL,
  `responsemessage` text,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `regionID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

-- 
-- Table structure for table `mediaprefs`
-- 

DROP TABLE IF EXISTS `mediaprefs`;
CREATE TABLE IF NOT EXISTS `mediaprefs` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `galleryhome` tinyint(4) NOT NULL default '0',
  `showcomments` tinyint(4) NOT NULL default '0',
  `showslideshow` tinyint(4) NOT NULL default '1',
  `uselightbox` tinyint(4) NOT NULL default '1',
   `allowlinks` tinyint(4) NOT NULL default '0',
  `uploadpermissioncheck` tinyint(4) NOT NULL default '0',
  `uploadrankID` tinyint(4) NOT NULL default '1',
  `uploadapprove` tinyint(4) NOT NULL default '0',
  `roundedpx` tinyint(4) NOT NULL default '0',
  `gallerytype` tinyint(4) NOT NULL default '1',
  `galleriesname` varchar(50) default 'Photo Galleries',
  `imagesize_gallery` varchar(50) default 'thumb',
  
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `mediaprefs`
-- 

INSERT INTO `mediaprefs` (ID) VALUES (1);



-- --------------------------------------------------------

-- 
-- Table structure for table `merge`
-- 

DROP TABLE IF EXISTS `merge`;
CREATE TABLE IF NOT EXISTS `merge` (
  `ID` int(11) NOT NULL auto_increment, 
  `mergename` varchar(50) NOT NULL,
  `mergetext` text,
  `mergeincludeURL` varchar(255) default NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `regionID` tinyint(4) NOT NULL default '0',  
  PRIMARY KEY  (`ID`),
  KEY `statusID` (`statusID`),
  KEY `regionID` (`regionID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;





-- --------------------------------------------------------

-- 
-- Table structure for table `news`
-- 

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
	`ID` int(11) NOT NULL auto_increment,
	`longID` varchar(100)  default NULL,
	`ordernum` int(11) default '0',
	`metadescription` text ,
	`metakeywords` text,
	`title` varchar(255) NOT NULL default '',
	`pagetitle` varchar(255) default NULL,
	`summary` mediumtext NOT NULL,
	`body` mediumtext,
	`displayfrom` datetime default NULL,
	`displayto` datetime default NULL,
	`eventdatetime` datetime default NULL,
	`status` tinyint(4) NOT NULL default '0',
	`headline` tinyint(4) NOT NULL default '0',
	`featured` tinyint(4) NOT NULL default '0',
	`alert` tinyint(4) NOT NULL default '0',
	`action` tinyint(4) NOT NULL default '0',
	`rss` tinyint(4) NOT NULL default '1',
	`postedbyID` int(11) default NULL,
	`imageURL` varchar(255) default NULL,
	`imagealt` varchar(255) default NULL,
	`imageURL2` varchar(255) default NULL,
	`attachment1` varchar(255) default NULL,
	`redirectURL` varchar(255) default NULL,
	`youtube` text,
	`posteddatetime` datetime default NULL,
	`regionID` int(11) default NULL,
	`sectionID` int(11) NOT NULL default '1',
	`photogalleryID` int(11) default NULL,
	`slideshow` tinyint(4) NOT NULL default '0',
	`groupemailID` int(11) default NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
  	PRIMARY KEY  (`ID`),
  	KEY `status` (`status`),
  	KEY `ordernum` (`ordernum`),
  	KEY `longID` (`longID`),
  	KEY `alert` (`alert`),
  	KEY `regionID` (`regionID`),
  	KEY `sectionID` (`sectionID`),
  	KEY `displayfrom` (`displayfrom`),
	KEY `displayto` (`displayto`),
	KEY `featured` (`featured`),
	KEY `headline` (`headline`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `news`
-- 


-- --------------------------------------------------------

--
-- Table structure for table `newsusergroup`
--

DROP TABLE IF EXISTS `newsusergroup`;
CREATE TABLE IF NOT EXISTS `newsusergroup` (
	`ID` int(11) NOT NULL auto_increment,
	`newsID` int(11)  NOT NULL default  '0',
	`usergroupID` int(11)  NOT NULL default  '0',
	`createdbyID` int(11) NOT NULL  default  '1',
	`createddatetime` datetime NOT NULL  default  '2008-01-01 00:00:00',
	PRIMARY KEY (  `ID` ),
	 KEY `newsID` (`newsID`),
	 KEY `usergroupID` (`usergroupID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `newssection`
--

DROP TABLE IF EXISTS `newssection`;
CREATE TABLE IF NOT EXISTS `newssection` (
	`ID` INT NOT NULL AUTO_INCREMENT ,
	`longID` varchar(100)  default NULL,
	`redirectURL` varchar(255)  default NULL,
	`customNewsURL` varchar(255)  default NULL,
	`metadescription` text,
	`metakeywords` text,
	`defaultsummary` text,
	`defaultbody` text,
	`sectioname` varchar(100) NOT NULL default '',
	`sectiontitle` varchar(255) default NULL,
	`description` mediumtext,
	`ordernum` int(11) NOT NULL default  '0',
	`accesslevel` tinyint(4) NOT NULL default  '0',
	`editaccess` tinyint(4) NOT NULL default  '8',
	`groupreadID` int(11) NOT NULL default  '0',
	`groupwriteID` int(11) NOT NULL default  '0',
	`requiresapproval` tinyint(4) NOT NULL default  '1',
	`allowcomments` tinyint(4) NOT NULL default  '0',
	`reportabuse` tinyint(4) NOT NULL default  '0',
	`allowbody` tinyint(4) NOT NULL default  '1',
	`allowphoto` tinyint(4) NOT NULL default  '1',
	`allowattachment` tinyint(4) NOT NULL default  '0',
	`showeventdatetime` tinyint(4) NOT NULL default  '0',
	`classes` varchar(50) NOT NULL default '',
	`statusID` tinyint(4) NOT NULL default  '1',
	`articleID` int(11)  default NULL,
	`sectionID` int(11)  default NULL,
	`regionID` int(11)  default '1',
	`noindex` int(11)  default '0',
	`showbody` tinyint(4)  default '1',
	`emailsenddefault` tinyint(4)  default '0',
	`rsssenddefault` tinyint(4)  default '1',
	`showpostedby` tinyint(4)  default '1',
	`orderby` tinyint(4)  default '1',
	`wysiwyg` tinyint(4)  default '0',
	`wysiwygsummary` tinyint(4)  default '0',
	`indexstyle` tinyint(4)  default '0',
	`parentsectionID` int(11)  default NULL,
	`emailtemplateID` int(11)  default NULL,
	`createdbyID` int(11) NOT NULL  default  '1',
	`createddatetime` datetime NOT NULL  default  '2008-01-01 00:00:00',
	`modifiedbyID` int(11) default NULL ,
	`modifieddatetime` datetime default NULL ,
	PRIMARY KEY (  `ID` ),
	 KEY `ordernum` (`ordernum`),
	 KEY `noindex` (`noindex`),
	 KEY `longID` (`longID`),
	  KEY `regionID` (`regionID`),
	   KEY `sectionID` (`sectionID`),
	   KEY `statusID` (`statusID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------


-- 
-- Table structure for table `newsprefs`
-- 

DROP TABLE IF EXISTS `newsprefs`;
CREATE TABLE IF NOT EXISTS `newsprefs` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `newstickerfeedtitle` varchar(50) default NULL,
  `newstickerfeed` varchar(255) default NULL,
  `newspagefeedtitle` varchar(50) default NULL,
  `newspagefeed` varchar(255) default NULL,
  `usedefaultimage` tinyint(4) NOT NULL default  '1',
  `defaultImageURL` varchar(100) default NULL,
  `uselightwindow` tinyint(4) NOT NULL default  '0',
  `sectionindextype` tinyint(4) NOT NULL default  '0',
  `initialsection` int(11) NOT NULL default  '1',
   `newsshare` tinyint(4) NOT NULL default  '1',
  
   `imagesize_index` varchar(50) default 'thumb',
   `imagesize_story` varchar(50) default 'medium',
   `item_class` varchar(50) default 'row',
   `image_class` varchar(50) default 'col-sm-4',
   `text_class` varchar(50) default 'col-sm-8',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `newsprefs`
-- 


INSERT INTO `newsprefs` (ID) VALUES (1);


-- 
-- Table structure for table `notification`
-- 

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `notification` varchar(50) default NULL,
  `userID` int(11) NOT NULL  default  '0',
  `rankID` int(11) NOT NULL  default  '0',
  `groupID` int(11) NOT NULL  default  '0',
  `regionID` int(11) NOT NULL default  '0',
  `statusID` tinyint(4) NOT NULL  default  '1',
 `createdbyID` int(11) NOT NULL  default  '0',
	`createddatetime` datetime NOT NULL  default  '2008-01-01 00:00:00',
	`modifiedbyID` int(11) default NULL ,
	`modifieddatetime` datetime default NULL ,
  PRIMARY KEY  (`ID`),
  KEY `userID` (`userID`),
  KEY `rankID` (`rankID`),
  KEY `groupID` (`groupID`), 
  KEY `statusID` (`statusID`),
   KEY `regionID` (`regionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `newsprefs`
-- 



# Table structure for table `paypal_cart_info` 	#

DROP TABLE IF EXISTS `paypal_cart_info`;
CREATE TABLE IF NOT EXISTS `paypal_cart_info` (
  `txnid` varchar(30) NOT NULL default '',
  `itemname` varchar(255) NOT NULL default '',
  `itemnumber` varchar(50) default NULL,
  `os0` varchar(20) default NULL,
  `on0` varchar(50) default NULL,
  `os1` varchar(20) default NULL,
  `on1` varchar(50) default NULL,
  `quantity` char(3) NOT NULL default '',
  `invoice` varchar(255) NOT NULL default '',
  `custom` varchar(255) NOT NULL default ''
) ENGINE=MyISAM;



# Table structure for table `paypal_subscription_info` #


DROP TABLE IF EXISTS `paypal_subscription_info`;
CREATE TABLE IF NOT EXISTS `paypal_subscription_info` (
  `subscr_id` varchar(255) NOT NULL default '',
  `sub_event` varchar(50) NOT NULL default '',
  `subscr_date` varchar(255) NOT NULL default '',
  `subscr_effective` varchar(255) NOT NULL default '',
  `period1` varchar(255) NOT NULL default '',
  `period2` varchar(255) NOT NULL default '',
  `period3` varchar(255) NOT NULL default '',
  `amount1` varchar(255) NOT NULL default '',
  `amount2` varchar(255) NOT NULL default '',
  `amount3` varchar(255) NOT NULL default '',
  `mc_amount1` varchar(255) NOT NULL default '',
  `mc_amount2` varchar(255) NOT NULL default '',
  `mc_amount3` varchar(255) NOT NULL default '',
  `recurring` varchar(255) NOT NULL default '',
  `reattempt` varchar(255) NOT NULL default '',
  `retry_at` varchar(255) NOT NULL default '',
  `recur_times` varchar(255) NOT NULL default '',
  `username` varchar(255) NOT NULL default '',
  `password` varchar(255) default NULL,
  `payment_txn_id` varchar(50) NOT NULL default '',
  `subscriber_emailaddress` varchar(255) NOT NULL default '',
  `datecreation` date NOT NULL default '2000-01-01'
) ENGINE=MyISAM;






# Table structure for table `paypal_payment_info` #


DROP TABLE IF EXISTS `paypal_payment_info`;
CREATE TABLE IF NOT EXISTS `paypal_payment_info` (
  `firstname` varchar(100) default NULL,
  `lastname` varchar(100) default NULL,
  `buyer_email` varchar(100) default NULL,
  `street` varchar(100) default NULL,
  `city` varchar(50) default NULL,
  `state` char(3)  default NULL,
  `zipcode` varchar(11) default NULL,
  `memo` varchar(255) default NULL,
  `itemname` varchar(255) default NULL,
  `itemnumber` varchar(50) default NULL,
  `os0` varchar(20) default NULL,
  `on0` varchar(50) default NULL,
  `os1` varchar(20) default NULL,
  `on1` varchar(50) default NULL,
  `quantity` char(3) default NULL,
  `paymentdate` varchar(50) default NULL,
  `paymenttype` varchar(10) default NULL,
  `txnid` varchar(30) default NULL,
  `mc_gross` varchar(6) default NULL,
  `mc_fee` varchar(5) default NULL,
  `paymentstatus` varchar(15) default NULL,
  `pendingreason` varchar(10) default NULL,
  `txntype` varchar(10) default NULL,
  `tax` varchar(10) default NULL,
  `mc_currency` varchar(5) default NULL,
  `reasoncode` varchar(20) default NULL,
  `custom` varchar(255) default NULL,
  `country` varchar(20) default NULL,
  `datecreation` date NOT NULL default '2000-01-01'
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `photos`
-- 

DROP TABLE IF EXISTS `photos`;
CREATE TABLE IF NOT EXISTS `photos` (
  `ID` int(11) NOT NULL auto_increment,
  `userID` int(11) NOT NULL default '0',
  `imageURL` varchar(255)  default NULL,
  `uploadID` int(11) default NULL,
  `width` int(11) default NULL,
  `height` int(11) default NULL,
  `title` varchar(100) NOT NULL default '',
  `description` text,
  `active` tinyint(4) NOT NULL default '1',
  `linkURL` varchar(255)  default NULL,
  `videoURL` varchar(255)  default NULL,
  `ordernum` int(11) NOT NULL default '0',
  `categoryID` int(11) default NULL,
  `latitude` double default NULL,
  `longitude` double default NULL,
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `userID` (`userID`),
  KEY `active` (`active`),
  KEY `categoryID` (`categoryID`),
  KEY `uploadID` (`uploadID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `photos`
-- 



-- --------------------------------------------------------

-- 
-- Table structure for table `photocategories`
-- 

DROP TABLE IF EXISTS `photocategories`;
CREATE TABLE IF NOT EXISTS `photocategories` (
	`ID` int(11) NOT NULL auto_increment,
	`categoryname` varchar(50)  default NULL,
	`soundtrackURL` varchar(255) default NULL,
	`categorydate` DATE default NULL,
	`coverphotoID` int(11) default NULL,
	`ordernum` int(11) default 1,
	`regionID` int(11) default 1,
	`description` text,
	`categoryofID` int(11) NOT NULL default '0',
	`accesslevel` tinyint(4) NOT NULL default '0',
	`groupID` tinyint(4) NOT NULL default '0',
	`addedbyID` int(11) default 1,
	`active` tinyint(4) NOT NULL default '1',
	`createddatetime` DATETIME NOT NULL  DEFAULT  '2008-01-01 00:00:00',
	`modifiedbyID` INT DEFAULT NULL ,
	`modifieddatetime` DATETIME DEFAULT NULL ,
	PRIMARY KEY  (`ID`),
	KEY `regionID` (`regionID`),
	KEY `coverphotoID` (`coverphotoID`),
	KEY `categoryofID` (`categoryofID`),
	KEY `active` (`active`),
	KEY `accesslevel` (`accesslevel`),
	KEY `ordernum` (`ordernum`),
	KEY `createddatetime` (`createddatetime`),
	KEY `addedbyID` (`addedbyID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `photocategories`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `photoincategory`
-- 

DROP TABLE IF EXISTS `photoincategory`;
CREATE TABLE IF NOT EXISTS `photoincategory` (
	`ID` int(11) NOT NULL auto_increment,
	`categoryID` int(11) NOT NULL ,
	`photoID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `categoryID` (`categoryID`),
	KEY `photoID` (`photoID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `photoincategory`
-- 



-- --------------------------------------------------------

-- 
-- Table structure for table `photocomments`
-- 

DROP TABLE IF EXISTS `photocomments`;
CREATE TABLE IF NOT EXISTS `photocomments` (
  `ID` int(11) NOT NULL auto_increment,
  `userID` int(11) NOT NULL default '0',
  `photoID` int(11) NOT NULL default '0',
  `comment` text NOT NULL,
  `active` tinyint(4) NOT NULL default '1',
  `datetimeposted` datetime NOT NULL default '2000-01-01 00:00:00',
  `IPaddress` varchar(15) default NULL,
  KEY `userID` (`userID`),
  KEY `photoID` (`photoID`),
  KEY `active` (`active`),
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `photocomments`
-- 




-- --------------------------------------------------------

-- 
-- Table structure for table `photoviews`
-- 

DROP TABLE IF EXISTS `photoviews`;
CREATE TABLE IF NOT EXISTS `photoviews` (
  `ID` int(11) NOT NULL auto_increment,
  `photoID` int(11) NOT NULL default '0',
  `userID` int(11) NOT NULL default '0',
  `IP_address` varchar(16) default NULL,
  `datetimeviewed` datetime default NULL,
  KEY `userID` (`userID`),
  KEY `photoID` (`photoID`),
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `photoviews`
-- 

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
	`ID` int(11) NOT NULL auto_increment,
	`longID` varchar(100) default NULL,
	`custompageURL` varchar(255) default NULL,
	`redirect301`  tinyint(4) NOT NULL default '0',
	`productcategoryID` int(11) default NULL,
	`popularity` int(11) NOT NULL default '0',
	`regionID` int(11) NOT NULL default '1',
	`metadescription` text,
	`metakeywords` text,
	`h2` text,
	`datetimecreated` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	`title` varchar(100) default NULL,
	`sku` varchar(150) default NULL,
	`isbn` varchar(50) default NULL,
	`mpn` varchar(50) default NULL,
	`upc` varchar(50) default NULL,
	`googleID` decimal(65,0) default NULL,
	`description` mediumtext,
	`price` decimal(12,2) NOT NULL default '0',
	`priceper` varchar(20) default NULL,
	`pricetype` tinyint(4) NOT NULL default '1',
	`listprice` decimal(12,2) default NULL,
	`saleprice` decimal(12,2)  default NULL,
	`costprice` decimal(12,2) default NULL,
	`startingbid` decimal(12,2)  default NULL,
	`addfrom` tinyint(4) NOT NULL default '0',
	`showareaprice` tinyint(4) NOT NULL default '1',
	`showrrp` tinyint(4) NOT NULL default '0',
	`shippingexempt` tinyint(4) NOT NULL default '0',
	`shippingrateID` tinyint(4) NOT NULL default '0',
	`finishID` int(11) default NULL,
	`versionID` int(11) default NULL, 
	`availabledate` date default NULL,
	`weight` float  default NULL,
	`box_length` float default NULL,
	`box_height` float default NULL,
	`box_width` float   default NULL,
	`max_per_box` float NOT NULL default '0',
	`int_length` float default NULL,
	`int_height` float default NULL,
	`int_width` float  default NULL,
	`capacity` float  default NULL,
	`area` float  default NULL,
	`hazardous` tinyint(4) NOT NULL default '0', 
	`noshipinternational` tinyint(4) NOT NULL default '0', 
	`manufacturerID` int(11)  default NULL,
	`supplierdirectoryID` int(11)  default NULL,
	`imageURL` varchar(100) default NULL,
	`altimage` tinyint(4) NOT NULL default '0',
	`deliveryperiod` int(11) default NULL,
	`mindeliverytime` int(11) default NULL,
	`maxdeliverytime` int(11) default NULL,
	`inputfield` varchar(255) default NULL,
	`inputfield2` varchar(255) default NULL,
	`inputfield3` varchar(255) default NULL,
	`instock` int(11) NOT NULL default '1',
	`seotitle` varchar(100) default NULL,
	`class` varchar(20) default NULL,
	`featured` tinyint(4) NOT NULL default '0',
	`saleitem` tinyint(4) NOT NULL default '0',
	`fileupload` tinyint(4) NOT NULL default '0',
	`vattype` tinyint(4) NOT NULL default '1',
	`nocommondetails` tinyint(4) NOT NULL default '0',
	`statusID` tinyint(4) NOT NULL default '1',
	`condition` tinyint(4) NOT NULL default '0',
	`auction` tinyint(4) NOT NULL default '0',
	`auctionenddatetime` datetime default NULL,
	`auctionsellafter` tinyint(4) NOT NULL default '0',
	`relatedall` tinyint(4) NOT NULL default '0',
	`ordernum` int(11) NOT NULL default '0',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `productcategoryID` (`productcategoryID`),
	KEY `manufacturerID` (`manufacturerID`),
	KEY `supplierdirectoryID` (`supplierdirectoryID`),
	KEY `ordernum` (`ordernum`),
	KEY `longID` (`longID`),
	KEY `relatedall` (`relatedall`),
	KEY `statusID` (`statusID`),
	KEY `regionID` (`regionID`),
	KEY `vattype` (`vattype`),
	KEY `price` (`price`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

-- 
-- Table structure for table `productaccount`
-- 

DROP TABLE IF EXISTS `productaccount`;
CREATE TABLE IF NOT EXISTS `productaccount` (
	`ID` int(11) NOT NULL auto_increment,
	`groupID` int(11)  NOT NULL default '0',
	`approverrankID`  tinyint(4)  NOT NULL default '9',
	`payaccount` tinyint(4)  NOT NULL default '1',
	`payother` tinyint(4)  NOT NULL default '0',
	`sharedaddresses` tinyint(4)  NOT NULL default '0',
	`orderforlist` tinyint(4)  NOT NULL default '0',
	`regionID` int(11)  NOT NULL default '1',
	`statusID` int(11)  NOT NULL default '1',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `regionID` (`regionID`),
	KEY `statusID` (`statusID`),
	KEY `groupID` (`groupID`),
	KEY `approverrankID` (`approverrankID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;




-- --------------------------------------------------------

-- 
-- Table structure for table `productbid`
-- 

DROP TABLE IF EXISTS `productbid`;
CREATE TABLE IF NOT EXISTS `productbid` (
	`ID` int(11) NOT NULL auto_increment,
	`productID` int(11) NOT NULL,
	`amount`  decimal(10,2) NOT NULL default '0.00',
	`winning` tinyint(4) NOT NULL default '0',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `createdbyID` (`createdbyID`),
	KEY `productID` (`productID`),
	KEY `winning` (`winning`),
	KEY `amount` (`amount`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;




-- --------------------------------------------------------

--
-- Table structure for table `productcategory`
--

DROP TABLE IF EXISTS `productcategory`;
CREATE TABLE IF NOT EXISTS `productcategory` (
	`ID` int(11) NOT NULL auto_increment,
	`longID` varchar(100) default NULL,
	`regionID` tinyint(4) NOT NULL default '1',
	`accesslevel` tinyint(4) NOT NULL default '0',
	`groupID` tinyint(4) NOT NULL default '0',
	`metatitle` varchar(50) default NULL,
	`metadescription` text,
	`metakeywords` text,
	`forcemaincategory` tinyint(4) NOT NULL default '0',
	`gbasecat` varchar(100) default NULL,
	`subcatofID` int(11) NOT NULL default '0',
	`usergroupID` int(11)  default NULL,
	`title` varchar(255) default NULL,
	`summary` mediumtext,
	`description` mediumtext,
	`appendProductDescription` text,
	`colour` varchar(25) default NULL,
	`freesamplesku` varchar(150) default NULL,
	`samplequote` tinyint(4) NOT NULL default '0',
	`nextproductsku` varchar(150) default NULL,
	`excludepromotions` tinyint(4) NOT NULL default '0',
	`featured` tinyint(4) NOT NULL default '0',
	`imageURL` varchar(255) default NULL,
	`imageURL2` varchar(255) default NULL,
	`imageURL3` varchar(255) default NULL,
	`redirectURL` varchar(255) default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	`showinmenu` tinyint(4) NOT NULL default '1',
	`ordernum` int(11) NOT NULL default '0',
	`vatdefault` tinyint(4) NOT NULL default '1',
	`vatincluded` tinyint(4) NOT NULL default '1',
	`vatprice` tinyint(4) NOT NULL default '0',
	`vattext` tinyint(4) NOT NULL default '0',
	`noindex` tinyint(4) NOT NULL default '0',
	`categorysale` tinyint(4) NOT NULL default '0',
	`seotitle` varchar(66) default NULL,
	`directoryID` int(11) default NULL, #Associate organisation
	`directoryadmin` tinyint(4) NOT NULL default '0',
	`directorynotify` tinyint(4) NOT NULL default '0',
	`nopricebuy` tinyint(4) default NULL,
	`nopricetext` varchar(255) default NULL,
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `longID` (`longID`),
	KEY `ordernum` (`ordernum`),
	KEY `subcatofID` (`subcatofID`),
	KEY `regionID` (`regionID`),
	KEY `statusID` (`statusID`),
	KEY `showinmenu` (`showinmenu`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `productdetails`
--

DROP TABLE IF EXISTS `productdetails`;
CREATE TABLE IF NOT EXISTS `productdetails` (
	`ID` int(11) NOT NULL auto_increment,
	`ordernum` int(11) NOT NULL,
	`productID` int(11) NOT NULL,
	`regionID` tinyint(4) NOT NULL default '1',
	`defaulttab` int(11)  default '0',
	`tabtitle` varchar(50) NOT NULL,
	`headHTML` mediumtext,
	`tabtext` mediumtext,
	`footHTML` mediumtext,
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`),
   KEY `ordernum` (`ordernum`),
    KEY `productID` (`productID`),
	 KEY `regionID` (`regionID`),
	  KEY `statusID` (`statusID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;




-- --------------------------------------------------------

--
-- Table structure for table `productemail`
--

DROP TABLE IF EXISTS `productemail`;
CREATE TABLE IF NOT EXISTS `productemail` (
	`ID` int(11) NOT NULL auto_increment,
	`templateID` int(11) NOT NULL,
	`viaemail` tinyint(4) NOT NULL default '1',
	`viasms` tinyint(4) NOT NULL default '0',
	`categoryID` int(11) NOT NULL default '0',
	`period` int(11)  default NULL,
	`purchasemade` tinyint(4)  default '1',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`ignoreoptout` tinyint(4) NOT NULL default '0',
	`statusID` tinyint(4) NOT NULL default '1',
	`regionID` int(11) NOT NULL default '1',
  PRIMARY KEY  (`ID`),
  KEY `templateID` (`templateID`),
  KEY `categoryID` (`categoryID`),
  KEY `statusID` (`statusID`),
  KEY `regionID` (`regionID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;




-- --------------------------------------------------------

--
-- Table structure for table `productfeaturepage`
--

DROP TABLE IF EXISTS `productfeaturepage`;
CREATE TABLE IF NOT EXISTS `productfeaturepage` (
	`ID` int(11) NOT NULL auto_increment,
	`regionID` int(11) NOT NULL default '1',
	`categoryID` int(11) default NULL,
	`galleryID` int(11) default NULL,
	`manufacturerID` int(11) default NULL,
	`searchterm` varchar(20) default NULL,
	`pagetitle` varchar(20) NOT NULL,
	`bodyHTML` mediumtext,
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `productfinish`
--

DROP TABLE IF EXISTS `productfinish`;
CREATE TABLE IF NOT EXISTS `productfinish` (
	`ID` int(11) NOT NULL auto_increment,
	`finishname` varchar(50) NOT NULL,
	`imageURL` varchar(100) default NULL,
	`ordernum` int(11) NOT NULL default '0',
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`),
	KEY `statusID` (`statusID`),
	KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `productgallery`
--

DROP TABLE IF EXISTS `productgallery`;
CREATE TABLE IF NOT EXISTS `productgallery` (
  `ID` int(11) NOT NULL auto_increment,
  `productID` int(11) NOT NULL,
  `galleryID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `createdbyID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `productID` (`productID`),
  KEY `galleryID` (`galleryID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- 
-- Table structure for table `productgoogle`
-- 

DROP TABLE IF EXISTS `productgoogle`;
CREATE TABLE IF NOT EXISTS `productgoogle` (
	`ID` int(11) NOT NULL auto_increment,
	`googleID` decimal(65,0) NOT NULL,
	`baseprice`  decimal(10,2) NOT NULL default '0.00',
	`totalprice`  decimal(10,2) NOT NULL default '0.00',
	`sellers` varchar(255) NULL default NULL,
	`details` varchar(255) NULL default NULL,
	`shoplink` varchar(255) NULL default NULL,
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `googleID` (`googleID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;





-- --------------------------------------------------------

-- 
-- Table structure for table `productincategory`
-- 

DROP TABLE IF EXISTS `productincategory`;
CREATE TABLE `productincategory` (
	`ID` int(11) NOT NULL auto_increment,
	`productID` int(11) NOT NULL,
	`categoryID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
  	PRIMARY KEY  (`ID`),
  	KEY `productID` (`productID`),
	KEY `categoryID` (`categoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `productincategory`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `productinregion`
-- 

DROP TABLE IF EXISTS `productinregion`;
CREATE TABLE `productinregion` (
	`ID` int(11) NOT NULL auto_increment,
	`productID` int(11) NOT NULL,
	`regionID` tinyint(4) NOT NULL,
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`createdbyID` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `productID` (`productID`),
	KEY `regionID` (`regionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `productinregion`
-- 

-- --------------------------------------------------------

--
-- Table structure for table `productmanufacturer`
--

DROP TABLE IF EXISTS `productmanufacturer`;
CREATE TABLE IF NOT EXISTS `productmanufacturer` (
  `ID` int(11) NOT NULL auto_increment,
  `longID` varchar(50) default NULL,
  `subsidiaryofID` int(11) default NULL,
   `exclpromos` tinyint(4) NOT NULL default '0',
  `manufacturername` varchar(100) NOT NULL,
  `manufacturershipping` varchar(100) default NULL,
  `manufactureremail` varchar(100) default NULL,
  `description` mediumtext,
  `imageURL` varchar(255) default NULL,
  `metadescription` text,
  `metakeywords` text,
  `createdbyID` int(11) NOT NULL default '0',
  `regionID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `ordernum` int(11) default '0',
  `manufacturersale` tinyint(4) NOT NULL default '0',
  `freesamplesku` varchar(150) default NULL,
  KEY `subsidiaryofID` (`subsidiaryofID`),
   KEY `longID` (`longID`),
    KEY `statusID` (`statusID`),
	 KEY `regionID` (`regionID`),
	 KEY `ordernum` (`ordernum`),
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;






-- --------------------------------------------------------



--
-- Table structure for table `productoptions`
--

DROP TABLE IF EXISTS `productoptions`;
CREATE TABLE IF NOT EXISTS `productoptions` (
  `ID` int(11) NOT NULL auto_increment,
  `optionname` varchar(30) NOT NULL,
  `productID` int(11) NOT NULL,
  `finishID` int(11) default NULL,
  `versionID` int(11) default NULL,
  `stockcode` varchar(50) default NULL,
	`upc` varchar(50) default NULL,
	`mpn` varchar(50) default NULL,
	`googleID` decimal(65,0) default NULL,
  `ordernum` tinyint(4) NOT NULL default '0',
  `price` decimal(12,2) default NULL,
  `costprice` decimal(12,2) default NULL,
  `size` varchar(50) default NULL,
  `weight` float default NULL,
  `quantity` float default NULL,
  `photoID` int(11) default NULL,
  `regionID` int(11) NOT NULL default '0',
  `instock` int(11) NOT NULL default '1',
  `availabledate` date default NULL,
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `createdbyID` int(11) NOT NULL default '0',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  KEY `statusID` (`statusID`),
  KEY `productID` (`productID`),
  KEY `finishID` (`finishID`),
  KEY `photoID` (`photoID`),
  KEY `versionID` (`versionID`),
  KEY `googleID` (`googleID`),
  KEY `ordernum` (`ordernum`),
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `productorders`
--
-- NOTE an ID has been added for compatibility with DELETE general statements 

DROP TABLE IF EXISTS `productorders`;
CREATE TABLE IF NOT EXISTS `productorders` (
	`ID` int(11) NOT NULL auto_increment,
	`VendorTxCode` varchar(50) NOT NULL default '',
	`userID` int(11) default NULL,
	`regionID` int(11) default NULL,
	`sessionID` varchar(32) default NULL,
	`confemailsent` tinyint(4) default 0,
	`TxType` varchar(32) NOT NULL default '',
	`AmountTax` decimal(10,2)  default NULL,
	`Amount` decimal(10,2) NOT NULL default '0.00',
	`AmountPaid` decimal(10,2) default NULL,
	`shipping` decimal(10,2) NOT NULL default '0.00',
	`promotions` decimal(10,2)  NULL default NULL,
	`Currency` varchar(3) NOT NULL default '',
	`purchaseorder` varchar(50) default NULL,
	`BillingFirstnames` varchar(20) default NULL,
	`BillingSurname` varchar(20) default NULL,
	`BillingCompany` varchar(100) default NULL,
	`BillingAddress1` varchar(100) default NULL,
	`BillingAddress2` varchar(100) default NULL,
	`BillingCity` varchar(40) default NULL,
	`BillingPostCode` varchar(10) default NULL,
	`BillingCountry` varchar(2) default NULL,
	`BillingState` varchar(2) default NULL,
	`BillingPhone` varchar(20) default NULL,
	`BillingMobile` varchar(20) default NULL,
	`deliverysame` tinyint(4) default '1',
	`optin` tinyint(4) default '1',
	`followup` tinyint(4) default NULL,
	`deliveryinstructions` mediumtext,
	`checkoutanswer1` text,
	`DeliveryFirstnames` varchar(20) default NULL,
	`DeliverySurname` varchar(20) default NULL,
	`DeliveryCompany` varchar(100) default NULL,
	`DeliveryAddress1` varchar(100) default NULL,
	`DeliveryAddress2` varchar(100) default NULL,
	`DeliveryCity` varchar(40) default NULL,
	`DeliveryPostCode` varchar(10) default NULL,
	`DeliveryCountry` varchar(2) default NULL,
	`DeliveryState` varchar(2) default NULL,
	`DeliveryPhone` varchar(20) default NULL,
	`CustomerEMail` varchar(100) default NULL,
	`VPSTxId` varchar(64) default NULL,
	`SecurityKey` varchar(10) default NULL,
	`TxAuthNo` bigint(20) NOT NULL default '0',
	`AVSCV2` varchar(50) default NULL,
	`AddressResult` varchar(20) default NULL,
	`PostCodeResult` varchar(20) default NULL,
	`CV2Result` varchar(20) default NULL,
	`GiftAid` tinyint(4) default NULL,
	`ThreeDSecureStatus` varchar(50) default NULL,
	`CAVV` varchar(40) default NULL,
	`RelatedVendorTxCode` varchar(50) default NULL,
	`Status` varchar(255) default NULL,
	`AddressStatus` varchar(20) default NULL,
	`PayerStatus` varchar(20) default NULL,
	`CardType` varchar(15) default NULL,
	`Last4Digits` varchar(4) default NULL,
	`LastUpdated` datetime default NULL,
	`archive` tinyint(4) default '0',
	`discovered` int(11) default NULL,
	`basket_json` text,
	`billingcountryID` int(11) default NULL,
	`deliverycountryID` int(11) default NULL,
	`deliverytime` varchar(255) default NULL,
	`approvalrequired` tinyint(4) default '0',
	`approvedbyID` int(11) default NULL,
	`approveddatetime` datetime default NULL,	
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime default NULL,
	`modifiedbyID` int(11)  default NULL,
	`VATnumber` varchar(50) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `VendorTxCode` (`VendorTxCode`),
	KEY `archive` (`archive`),
	KEY `LastUpdated` (`LastUpdated`),
	KEY `createddatetime` (`createddatetime`)
) ENGINE=MyISAM;


-- --------------------------------------------------------

--
-- Table structure for table `productorderproducts`
--

DROP TABLE IF EXISTS `productorderproducts`;
CREATE TABLE IF NOT EXISTS `productorderproducts` (
	`ID` int(11) NOT NULL auto_increment,
	`VendorTxCode` varchar(50) NOT NULL,
	`ProductId` bigint(20) unsigned NOT NULL,
	`Price` decimal(10,2) NOT NULL default '0.00',
	`Quantity` int(11) default '0',
	`predictedamount` int(11) default '0',
	`dispatched` tinyint(4) default '0',
	`couriertrackerID` varchar(255) default NULL,
	`optiontext` varchar(255) default NULL,
	`optionID` int(11) default NULL,
	`uploadID` int(11) default NULL,
	`sampleofID` int(11) default NULL,
	`productforuserID` int(11) default NULL,
	 `stockdecremented` tinyint(4) default '0',
	 `mindeliverydatetime` datetime default NULL,
	 `maxdeliverydatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `VendorTxCode` (`VendorTxCode`),
	KEY `ProductId` (`ProductId`)
) ENGINE=MyISAM   AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `productorderpromos`
-- 

DROP TABLE IF EXISTS `productorderpromos`;
CREATE TABLE IF NOT EXISTS `productorderpromos` (
	`ID` int(11) NOT NULL auto_increment,
	`VendorTxCode` varchar(50) NOT NULL,
	`promotionID` int(11) NOT NULL,
	`amount`  decimal(10,2) NOT NULL default '0.00',
	PRIMARY KEY  (`ID`),
	KEY `VendorTxCode` (`VendorTxCode`),
	KEY `promotionID` (`promotionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;




-- --------------------------------------------------------

--
-- Table structure for table `productprefs`
--

DROP TABLE IF EXISTS `productprefs`;
CREATE TABLE IF NOT EXISTS `productprefs` (
	`ID` tinyint(4) NOT NULL auto_increment,
	`shopTitle` varchar(50) default NULL,
	`producttitle` varchar(100) default '{title}',
	`successURL` varchar(255) default NULL,
	`failURL` varchar(255) default NULL,
	`showcondition` tinyint(4) NOT NULL default '0',
	`defaultcondition` tinyint(4) NOT NULL default '0',
	`paymentproviderID` tinyint(4) NOT NULL default '0',
	`paymentclientID` varchar(255) default NULL,
	`paymentclientcode` varchar(255) default NULL,
	`paymentclientpassword` varchar(255) default NULL,
	`paypalID` varchar(50) default NULL,
	`cpp_header_image` varchar(255) default NULL,
	`cashdelivery` tinyint(4) NOT NULL default '0',
	`cashcollection` tinyint(4) NOT NULL default '0',
	`invoice` tinyint(4) NOT NULL default '0',
	`invoiceURL` varchar(255) default NULL,
	`cheque` tinyint(4) NOT NULL default '0',
	`auctions` tinyint(4) NOT NULL default '0',
	`auctiondays` tinyint(4) NOT NULL default '7',
	`auctionhours` tinyint(4) NOT NULL default '0',
	`auctionminbid` decimal(10,2) NOT NULL default '1',
	`auctionminincrement` decimal(10,2) NOT NULL default '1',
	`minimumorder` decimal(10,2) NOT NULL default '0',
	`bacsdetails` text,
	`importsettings` text,
	`producttemplateHTML` mediumtext,
	`productpagetemplateID` int(11) NOT NULL default '0',
	`relatedproducts` tinyint(4) NOT NULL default '0',
	`relatedtext` varchar(50) default 'You may also be interested in',
	`relatedcategoryID` int(11) NOT NULL default '-1',
	`relatedmanufacturerID` int(11) NOT NULL default '0',
	`featuredproducts` tinyint(4) NOT NULL default '0',
	`featuredtext` varchar(50) default 'Featured products',
	`viewedproducts` tinyint(4) NOT NULL default '0',
	`viewedtext` varchar(50) default 'Previously viewed',
	`alsobought` tinyint(4) NOT NULL default '0',
	`alsoboughttext` varchar(50) default 'Customers also bought',
	`shopfrontURL` varchar(255) default NULL,
	`imagesize_index` varchar(20) default "thumb",
	`imagesize_product` varchar(20) default "medium",
	`imagesize_basket` varchar(20) default "thumb",
	`imagesize_related` varchar(20) default "thumb",
	`imagesize_viewed` varchar(20) default "thumb",
	`imagesize_enlarged` varchar(20) default "large",
	`imagesize_category` varchar(20) default "thumb",
	`imagesize_productthumbs` varchar(20) default "thumb",	
	`defaultImageURL` varchar(100) default NULL,
	`imageOverlayURL` varchar(100) default NULL,
	`shopfrontfeatured` tinyint(4) NOT NULL default '4',
	`shopfrontpopular` tinyint(4) NOT NULL default '4',
	`shopfronttext` mediumtext,
	`shopfrontimagewidth` int(11) default '690',
	`shopfrontimageheight` int(11) default '410',
	`shopfrontimageURL1` varchar(100) default NULL,
	`shopfrontimageURL2` varchar(100) default NULL,
	`shopfrontimageURL3` varchar(100) default NULL,
	`shopfrontimageURL4` varchar(100) default NULL,
	`shopfrontimageURL5` varchar(100) default NULL,
	`shopfrontimageURL6` varchar(100) default NULL,
	`shopfrontimageURL7` varchar(100) default NULL,
	`shopfrontlink1` varchar(255) default NULL,
	`shopfrontlink2` varchar(255) default NULL,
	`shopfrontlink3` varchar(255) default NULL,
	`shopfrontlink4` varchar(255) default NULL,
	`shopfrontlink5` varchar(255) default NULL,
	`shopfrontlink6` varchar(255) default NULL,
	`shopfrontlink7` varchar(255) default NULL,
	`shopfrontimagealt1` varchar(100) default NULL,
	`shopfrontimagealt2` varchar(100) default NULL,
	`shopfrontimagealt3` varchar(100) default NULL,
	`shopfrontimagealt4` varchar(100) default NULL,
	`shopfrontimagealt5` varchar(100) default NULL,
	`shopfrontimagealt6` varchar(100) default NULL,
	`shopfrontimagealt7` varchar(100) default NULL,
	`commondetails` tinyint(4) NOT NULL default '0',
	`commondetailstitle` varchar(50) default NULL,
	`commondetailstext` mediumtext,
	`maintabtext` varchar(50) default 'Overview',
	`gallerytype` tinyint(4) NOT NULL default '1',
	`optionsdisplay` tinyint(4) NOT NULL default '1',
	`indexoptionsdisplay` tinyint(4) NOT NULL default '0',
	`vatincluded` tinyint(4) NOT NULL default '1',
	`vatprice` tinyint(4) NOT NULL default '0',
	`vattext` tinyint(4) NOT NULL default '0',
	`askvatnumber` tinyint(4) NOT NULL default '0',
	`showareaprice` tinyint(4) NOT NULL default '0',
	`useareaquantity` tinyint(4) NOT NULL default '0',
	`shippingcalctype` tinyint(4) NOT NULL default '0',
	`shippingautocalc` tinyint(4) NOT NULL default '1',
	`shippingcalcbeforeaddress` tinyint(4) NOT NULL default '0',
	`allowcollection` tinyint(4) NOT NULL default '0',
	`collectionaddress` varchar(255) default NULL,
	`shopstatus` tinyint(4) NOT NULL default '1',
	`askbillingdetails` tinyint(4) NOT NULL default '1',
	`askcompany` tinyint(4) NOT NULL default '0',
	`saleemail` tinyint(4) NOT NULL default '1',
	`gbaseVAT` tinyint(4) NOT NULL default '1',
	`googlemerchantID` int(11) default NULL,
	`googlemerchantoptions` tinyint(4) default 0,
	`googlemerchantoutofstock` tinyint(4) default 0,
	`googlemerchantpreorder` tinyint(4) default 0,
	`confirmationemail` tinyint(4) NOT NULL default '0',
	`confemailcc` varchar(100) default NULL,
	`confemailsubject` varchar(50) default NULL,
	`confemailmessage` mediumtext,
	`confemailtemplateID` int(11) NOT NULL default '0',	
	`conffreeemailtemplateID` int(11) NOT NULL default '0',	
	`dispatchemailsubject` varchar(50) default NULL,
	`dispatchemailcc` varchar(100) default NULL,
	`dispatchemailmessage` mediumtext,
	`dispatchemailtemplateID` int(11) NOT NULL default '0',	
	`dispatchsms` tinyint(4) NOT NULL default '0',	
	`salesnotifications` tinyint(4) NOT NULL default '0',
	`shippingincluded` tinyint(4) NOT NULL default '1',
	`shopfront` tinyint(4) NOT NULL default '0',
	`allowsharing` tinyint(4) NOT NULL default '1',
	`allowcomments` tinyint(4) NOT NULL default '0',
	`reviewstab` tinyint(4) NOT NULL default '0',
	`reviewemailtemplateID` int(11)  default NULL,
	`commentscaptcha` tinyint(4) NOT NULL default '0',
	`commentsmemberonly` tinyint(4) NOT NULL default '0',
	`commentslocation` tinyint(4) NOT NULL default '0',
	`commentsemail` tinyint(4) NOT NULL default '0',
	`colourchooser` tinyint(4) NOT NULL default '1',
	`successpage` mediumtext,
	`returnspolicyURL` varchar(255) default NULL,
	`shippingnotes` mediumtext,
	`shippinginfoURL` varchar(255) default NULL,
	`shippingendofday` time default "23:59:59",
	`shippinginfonewwindow` tinyint(4) NOT NULL default '0',
	`deliverytimes1` varchar(50) default NULL,
	`deliverytimes2` varchar(50) default NULL,
	`deliverytimes3` varchar(50) default NULL,
	`samplemax` tinyint(4) NOT NULL default '0',
	`sampletext` varchar(255) default 'FREE SAMPLE',
	`basketpageURL` varchar(50) default NULL,
	`moreinfotext` varchar(20) default 'More info',
	`addtobasket` varchar(20) default 'Add to basket',
	`baskettext` varchar(20) default 'Basket',
	`updatebaskettext` varchar(20) default 'Update Quantities',
	`checkouttext` varchar(20) default 'Place Order',
	`continueshoppingtext` varchar(20) default 'Continue shopping',
	`promocodetext` varchar(150) default 'Do you have a promotional code? Enter it here:',
	`promobuttontext` varchar(20) default 'Enter code',
	`quantitytext` varchar(20) default 'Quantity',
	`itemtext` varchar(20) default 'Item',
	`pricetext` varchar(20) default 'Price',
	`paymenttext` varchar(20) default 'Secure Payment',
	`taxname` varchar(20) default 'VAT',
	`subtotaltext` varchar(20) default 'Sub Total',
	`grandtotaltext` varchar(50) default 'Grand Total',
	`nopricebuy` tinyint(4) NOT NULL default '0',
	`nopricetext` varchar(255) default "Please call for best price",
	`tagsearchtype` tinyint(4) NOT NULL default '1',
	`stockcontrol` tinyint(4) NOT NULL default '0',
	`stocklowamount` int(11) NOT NULL default '0',
	`categorytextposition` tinyint(4) NOT NULL default '1',
	`askpostcode` tinyint(4) NOT NULL default '1',
	`sitemap` tinyint(4) NOT NULL default '0',
	`saleends` datetime default NULL,
	`basketshowpricem2` tinyint(4) NOT NULL default '0',
	`basketshowupdatequantity` tinyint(4) NOT NULL default '1',
	`basketshowadjustablequantity` tinyint(4) NOT NULL default '0',
	`basketshowremove` tinyint(4) NOT NULL default '1',
	`basketshowweight` tinyint(4) NOT NULL default '0', 
	`buyposition` tinyint(4) NOT NULL default '2', 
	`producth1category` tinyint(4) NOT NULL default '1', 
	`askhowdiscovered` tinyint(4) NOT NULL default '0', 
	`versiontitle` varchar(20) default 'Version',
	`versionfilter` tinyint(4) NOT NULL default '0',
	`finishtitle` varchar(20) default 'Colour',
	`finishfilter` tinyint(4) NOT NULL default '0',
	`manufacturertitle` varchar(20) default 'Brand',
	`manufacturerfilter` tinyint(4) NOT NULL default '0',
	`manufacturershowsubs` tinyint(4) NOT NULL default '0',
	`defaultsort` varchar(20) NOT NULL default 'ordernum',
	`subcatsposition` tinyint(4) NOT NULL default '2',
	`searchsubcats` tinyint(4) NOT NULL default '1',
	`searchtype` tinyint(4) NOT NULL default '1',
	`searchresults` tinyint(4) NOT NULL default '1',
	`basketrelatedcategoryID` tinyint(4) NOT NULL default '0',
	`checkoutmandatorytelephone` tinyint(4) NOT NULL default '0',
	`checkouttermsagree` tinyint(4) NOT NULL default '0',
	`checkoutquestion1` varchar(255)  default NULL,
	`checkoutconfirmfooter` mediumtext,
	`paynowemail` text,
	`paynowtemplateID` int(11) default NULL,
	`defaultitemunit` varchar(20) default 'item',
	`text_custom_isbn_field` varchar(50) default 'ISBN',
	`text_yourorder` varchar(50) default 'Your Order',
	`text_usingpromo` varchar(100) default 'You are using promotion code:',
	`text_promonotexists` varchar(100) default 'We do not have a active promotion code that matches the one you entered.',
	`text_bagitems` varchar(50) default 'Items added to bag',
	`text_noitems` varchar(50) default 'You have no items in your bag',
	`text_shipping` varchar(50) default 'Shipping',
	`text_free` varchar(20) default 'FREE',
	`text_update` varchar(20) default 'Update',
	`text_remove` varchar(20) default 'Remove',
	`text_yourdetails` varchar(20) default 'Your Details',
	`text_firstname` varchar(20) default 'First name(s)',
	`text_surname` varchar(20) default 'Surname',
	`text_email` varchar(20) default 'email',
	`text_emailinfo` varchar(50) default '(to send purchase confirmation)',
	`text_address` varchar(20) default 'Address',
	`text_city` varchar(20) default 'City/State',
	`text_postcode` varchar(20) default 'Post/Zip Code',
	`text_country` varchar(20) default 'Country',
	`text_telephone` varchar(20) default 'Contact Phone',	
	`text_mobile` varchar(20) default 'Mobile',
	`text_mobileinfo` varchar(50) default '(to assist our couriers with delivery)',
	`text_howfound` varchar(50) default 'How did you hear about us?',
	`text_billingdetails` varchar(50) default 'Billing Details',
	`text_choosesaved` varchar(50) default 'Choose a saved address...',
	`text_deliverydetails` varchar(255) default 'Delivery Details',
	`text_deliveryaddress` varchar(50) default 'Delivery Address',
	`text_sameasbilling` varchar(50) default 'Same as Billing',
	`text_differentaddress` varchar(50) default 'Different Address',
	`text_willcollectfrom` varchar(50) default 'Will collect from',
	`text_addspecialinstructions` varchar(50) default 'Add special instructions',
	`text_deliveryinstructions` varchar(50) default 'Delivery instructions',
	`text_calcshipping` varchar(50) default 'Calculate Shipping',
	`text_payby` varchar(20) default 'Pay by',
	`text_creditcard` varchar(20) default 'Credit/Debit Card',
	`text_ordersummary` varchar(20) default 'Order Summary',
	`text_myaccount` varchar(20) default 'My Account',
	`text_addresserror` varchar(150) default 'Please enter the following information below:',
	`text_vatnumber` varchar(50) default 'VAT Number',
	`text_confirmremovebasket` varchar(255) default 'Are you sure you want to remove this item from your basket?',
	`text_returningmember` varchar(50) default 'Returning Member?',
	`text_returningmemberinfo` varchar(255) default 'If you are a returning member, we should already have your address on file, so log in:',
	`text_savedetails` varchar(150) default 'Would you like us to save your details for your next visit?',
	`text_yesregister` varchar(50) default 'Yes, register me',
	`text_registerinfo` text,
	`text_yourreceipt` varchar(50) default 'Your Receipt',
	`text_paymentreceived` varchar(50) default 'Payment received in full. Thank you.',
	`text_paymentpending` varchar(50) default 'Payment is pending.',
	`text_forreference` varchar(50) default 'For your reference',
	`text_transactionnumber` varchar(50) default 'Your transaction number:',
	`text_invoice` varchar(50) default 'Invoice',
	`text_viewinvoice` varchar(255) default 'View\/print invoice',
	`text_welcomeback` varchar(255) default 'Welcome back, {name}',
	`text_notyou` varchar(255) default '(If you are not {name}, please {logout})',
	`text_mypurchases` varchar(255) default 'My Purchases',
	`text_delivery_time` varchar(255) default 'Preferred delivery time',
	`text_filterby` varchar(50) default 'Filter by',
	
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1;


--
-- Dumping data for table `productprefs`
--

INSERT INTO `productprefs` (`confemailmessage`,  `successpage`) VALUES
('Dear [firstname],\r\n\r\nThank you for your order detailed below. It will be dispatched shortly.\r\n\r\n[order]\r\n\r\nIf you have any queries regarding this order please quote order code:\r\n\r\n[code]\r\n','<h1>Transaction Successful</h1><p>Your purchase has been processed. We will send a confirmation email once your goods are dispatched (always check your junk folder too).</p><p>You can <a href="javascript:window.print();">print this page</a> for your records.</p>');


-- --------------------------------------------------------

--
-- Table structure for table `productpromo`
--

DROP TABLE IF EXISTS `productpromo`;
CREATE TABLE IF NOT EXISTS `productpromo` (
	`ID` int(11) NOT NULL auto_increment,
	`promotitle` varchar(255) NOT NULL,
	`promocodetype` tinyint(4) default '0',
	`promocode` varchar(50) default NULL,
	`promodetails` text,
	`imageURL` varchar(100) default NULL,
	`linkURL` varchar(100) default NULL,
	`startdatetime` datetime default NULL,
	`enddatetime` datetime default NULL,
	`actiontypeID` tinyint(4) NOT NULL,
	`actionproductID` int(11) NOT NULL default '0',
	`actioncategoryID` int(11) NOT NULL default '0',
	`actionmanufacturerID` int(11) NOT NULL default '0',	
	`actionamount` decimal(10,2) NOT NULL,
	`resulttypeID` int(11) NOT NULL,
	`resultproduct` int(11) NOT NULL default '0',
	`resultcategoryID` int(11) NOT NULL default '0',
	`resultamount` decimal(10,2) NOT NULL,
	`regionID` tinyint(4) NOT NULL default '0',
	`display` tinyint(4) NOT NULL default '1',
	`addbasket` tinyint(4) NOT NULL default '0',
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL,
	`modifieddatetime` datetime default NULL,
	`modifiedbyID` int(11) default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	`standalone` tinyint(4) NOT NULL default '1',
	`progressivediscountgroup` int(11) NOT NULL default '0',
	`usergroupID` tinyint(4) NOT NULL default '0',
	`ordernum` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `statusID` (`statusID`),
	KEY `progressivediscountgroup` (`progressivediscountgroup`),
	KEY `usergroupID` (`usergroupID`),
	KEY `regionID` (`regionID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `productpromocode`
-- 

DROP TABLE IF EXISTS `productpromocode`;
CREATE TABLE IF NOT EXISTS `productpromocode` (
	`ID` int(11) NOT NULL auto_increment,
	`promocode` varchar(32) NOT NULL,
	`promoID` int(11) NOT NULL,
	`uploadID` int(11) default NULL,
	`validfrom` datetime default NULL,
	`validuntil` datetime default NULL,
	`createdbyID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`),
	KEY `promocode` (`promocode`),
	KEY `promoID` (`promoID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `productpromocode`
-- 


-- --------------------------------------------------------

--
-- Table structure for table `productrelated`
--

DROP TABLE IF EXISTS `productrelated`;
CREATE TABLE IF NOT EXISTS `productrelated` (
	`ID` int(11) NOT NULL auto_increment,
	`productID` int(11) NOT NULL,
	`relatedtoID` int(11) NOT NULL,
	`relationshiptypeID` tinyint(4) NOT NULL default '1',
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`),
	KEY `productID` (`productID`,`relatedtoID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Table structure for table `productsearch`
--

DROP TABLE IF EXISTS `productsearch`;
CREATE TABLE IF NOT EXISTS `productsearch` (
	`ID` int(11) NOT NULL auto_increment,
	`searchterm` varchar(255) NOT NULL,
	`sessionID` varchar(255) NOT NULL,
	`regionID` int(11) default NULL,
	`createddatetime` datetime NOT NULL,
	PRIMARY KEY  (`ID`),
	KEY `searchterm` (`searchterm`),
	KEY `sessionID` (`sessionID`),
	KEY `regionID` (`regionID`),
	KEY `createddatetime` (`createddatetime`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


--
-- Table structure for table `productshipping`
--

DROP TABLE IF EXISTS `productshipping`;
CREATE TABLE IF NOT EXISTS `productshipping` (
	`ID` int(11) NOT NULL auto_increment,
	`shippingname` varchar(50) NOT NULL,
	`shippingrate` decimal(10,2) NOT NULL,
	`ratemultiple` tinyint(4) NOT NULL default '0',
	`ratemultipleamount` int(11) NOT NULL default '1',
	`minweight` float default NULL,
	`maxweight` float default NULL,
	`shippingzoneID` int(11) NOT NULL default '0',
	`express` tinyint(4) default '0',
	`promotion` tinyint(4) default '0',
	`hazardous` tinyint(4) default '0',
	`regionID` tinyint(4) NOT NULL default '0',
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL,
	`modifiedbyID` int(11) NOT NULL,
	`modifieddatetime` datetime default NULL,
	`statusID` tinyint(4) default '1',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `productshippingzone`
--

DROP TABLE IF EXISTS `productshippingzone`;
CREATE TABLE IF NOT EXISTS `productshippingzone` (
	`ID` int(11) NOT NULL auto_increment,
	`zonename` varchar(100) NOT NULL,
	`type` tinyint(4) NOT NULL,
	`bypostcode` text,
	`createdbyID` int(11) NOT NULL,
	`createddatetime` datetime NOT NULL,
	`modifieddatetime` datetime default NULL,
	`modifiedbyID` int(11) default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `productshippingzone`

INSERT INTO `productshippingzone` (`ID`, `zonename`, `type`, `bypostcode`, `createdbyID`, `createddatetime`, `modifieddatetime`, `modifiedbyID`, `statusID`) VALUES
(1, 'All national addresses', 1, NULL, 1, NOW(), NULL, NULL, 1),
(2, 'All international addresses', 2, NULL, 1, NOW(), NULL, NULL, 1);

--


-- --------------------------------------------------------

--
-- Table structure for table `productsubscription`
--

DROP TABLE IF EXISTS `productsubscription`;
CREATE TABLE IF NOT EXISTS `productsubscription` (
  `ID` int(11) NOT NULL auto_increment,
  `followonID` int(11) default NULL,
  `userID` int(11) default NULL,
  `productID` int(11) default NULL,
  `orderID` int(11) default NULL,
  `directoryID` int(11) default NULL,
  `startdatetime` datetime NOT NULL, 
  `enddatetime` datetime NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifieddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `productsubscription`


-- --------------------------------------------------------

-- 
-- Table structure for table `producttag`
-- 

DROP TABLE IF EXISTS `producttag`;
CREATE TABLE IF NOT EXISTS `producttag` (
  `ID` int(11) NOT NULL auto_increment,
  `tagname` varchar(50) NOT NULL,
  `taggroupID` tinyint(4) default NULL,
  `ordernum` int(11) NOT NULL default '0',
  `regionID` int(11) NOT NULL default '1',
  `createdbyID` int(11) default NULL,
  `createddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `taggroupID` (`taggroupID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `producttagged`
-- 

DROP TABLE IF EXISTS `producttagged`;
CREATE TABLE IF NOT EXISTS `producttagged` (
  `ID` int(11) NOT NULL auto_increment,
  `productID` int(11) NOT NULL,
  `tagID` int(11) NOT NULL,
  `createdbyID` int(11) default NULL,
  `createddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `productID` (`productID`),
  KEY `tagID` (`tagID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `producttaggroup`
-- 

DROP TABLE IF EXISTS `producttaggroup`;
CREATE TABLE IF NOT EXISTS `producttaggroup` (
  `ID` int(11) NOT NULL auto_increment,
  `regionID` int(11) NOT NULL default '1',
  `taggroupname` varchar(30) NOT NULL,
  `ordernum` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;




-- --------------------------------------------------------

--
-- Table structure for table `productversion`
--

DROP TABLE IF EXISTS `productversion`;
CREATE TABLE IF NOT EXISTS `productversion` (
  `ID` int(11) NOT NULL auto_increment,
  `versionname` varchar(50) NOT NULL,
  `ordernum` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;







-- --------------------------------------------------------

--
-- Table structure for table `productnotify`
--

DROP TABLE IF EXISTS `productnotify`;
CREATE TABLE IF NOT EXISTS `productnotify` (
  `ID` int(11) NOT NULL auto_increment,
  `userID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `notifieddatetime` datetime default NULL,
  `notified` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `productwithfinish`
--

DROP TABLE IF EXISTS `productwithfinish`;
CREATE TABLE IF NOT EXISTS `productwithfinish` (
  `ID` int(11) NOT NULL auto_increment,
  `productID` int(11) NOT NULL,
  `finishID` int(11) default NULL,
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `createdbyID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `productID` (`productID`),
  KEY `finishID` (`finishID`)
) ENGINE=MyISAM   AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `productwithversion`
--

DROP TABLE IF EXISTS `productwithversion`;
CREATE TABLE IF NOT EXISTS `productwithversion` (
  `ID` int(11) NOT NULL auto_increment,
  `productID` int(11) NOT NULL,
  `versionID` int(11) default NULL,
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `createdbyID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `productID` (`productID`),
  KEY `versionID` (`versionID`)
) ENGINE=MyISAM   AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `productvatrate`
--

DROP TABLE IF EXISTS `productvatrate`;
CREATE TABLE IF NOT EXISTS `productvatrate` (
  `ID` int(11) NOT NULL auto_increment,
  `regionID` int(11) NOT NULL,
  `ratename` varchar(100)  default NULL,
  `ratepercent` int(11) default NULL,
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `createdbyID` int(11) NOT NULL default '0',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM   AUTO_INCREMENT=1 ;





-- Table structure for table `region`
-- 

DROP TABLE IF EXISTS `region`;
CREATE TABLE IF NOT EXISTS `region` (
  `ID` int(11) NOT NULL auto_increment,
  `showmenu` tinyint(4) NOT NULL default '0',
  `title` varchar(50) NOT NULL default '',
  `currencycode` varchar(5) default NULL,
  `languagecode` varchar(10) default NULL,
  `paymenttypeID` tinyint(4) NOT NULL default '0',
  `paymentserviceID` varchar(255) default NULL,
  `hostdomain` char(255) default NULL,
  `https` tinyint(4) NOT NULL default '0',
  `www` tinyint(4) NOT NULL default '1',
  `flag` varchar(2) default NULL,
  `address` text,
  `postcode` varchar(12) default NULL,
  `telephone` varchar(20) default NULL,
  `fax` varchar(20) default NULL,
  `email` varchar(255) default NULL,
  `skypeID` varchar(50) default NULL,
  `facebookID` varchar(255) default NULL,
  `twitterID` varchar(255) default NULL,
  `twittertab` tinyint(4) NOT NULL default '0',
  `blogURL` varchar(255) default NULL,
  `flickrURL` varchar(255) default NULL,
  `googleplusURL` varchar(255) default NULL,
  `youtubeURL` varchar(255) default NULL,
  `linkedinURL` varchar(255) default NULL,
  `instagramURL` varchar(255) default NULL,
  `instagram_access_user_ID` bigint(20) default NULL,
  `instagram_access_token` varchar(255) default NULL,
  `instagram_access_token_expiry` datetime default NULL,
  `pinterestURL` varchar(255) default NULL,
  `signupemailtext` text,
  `headhtml` text,
  `statusID` tinyint(4) NOT NULL default '1',
  `vatrate` float default NULL,
  `vatnumber` varchar(50) default NULL,
  `imageURL` varchar(255) default NULL,
  `adminheaderimageURL` varchar(255) default NULL,
  `adminheadercolor` varchar(25) default '#333333',
  `backgroundimageURL` varchar(255) default NULL,
  `themecolor1` varchar(25) default '#999999',
  `themecolor2` varchar(25) default '#cccccc',
  `h1color` varchar(25) default '#000000',
  `pcolor` varchar(25) default '#666666',
  `backgroundcolor` varchar(25) default '#ffffff',
  `faviconURL` varchar(50) default '/favicon.ico',
  `text_yes` varchar(20) default 'Yes',
  `text_no` varchar(20) default 'No',
  `text_choose` varchar(20) default 'Choose...',
  `text_save` varchar(20) default 'Save changes...',
  `text_continue` varchar(20) default 'Continue...',
  `text_previous` varchar(20) default 'Previous',
  `text_next` varchar(20) default 'Next',
  `text_contactus` varchar(20) default 'Contact Us',
  `text_emailerror` varchar(150) default 'Please enter a vald email address',
  `text_follow_us` varchar(20) default 'Follow us',
  `text_share` varchar(20) default 'Share',
  `text_back` varchar(20) default 'Back',
  `text_items` varchar(20) default 'Items',
  `text_to` varchar(20) default 'to',
  `text_of` varchar(20) default 'of',
  `text_show_all` varchar(20) default 'Show all',
  `text_read_more` varchar(20) default 'Read more',
  `text_other1` varchar(255) default 'Other 1',
  `text_other2` varchar(255) default 'Other 2',
  `text_other3` varchar(255) default 'Other 3',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- 
-- Dumping data for table `region`
-- 

INSERT INTO `region` (ID, title, currencycode) VALUES (1, 'Default Site', 'GBP');


-- --------------------------------------------------------

--
-- Table structure for table `reminder`
--

DROP TABLE IF EXISTS `reminder`;
CREATE TABLE IF NOT EXISTS `reminder` (
  `ID` int(11) NOT NULL auto_increment,
  `eventID` int(11) default NULL,
  `recipientID` int(11) NOT NULL,
  `cc` varchar(50) default NULL,
  `from` varchar(255) default NULL,
  `friendlyfrom` varchar(255) default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `viaemail` tinyint(4) NOT NULL default '1',
  `viasms` tinyint(4) NOT NULL default '0',
  `subject` varchar(50) NOT NULL default 'REMINDER',
  `message` text,
  `htmlhead` text,
  `htmlmessage` mediumtext,
  `smsmessage` text,
  `firstsend` datetime NOT NULL,
  `lastsent` datetime default NULL,
  `reminderrepeat` tinyint(4) NOT NULL default '1',
  `months` tinyint(4) NOT NULL default '0',
  `seconds` int(11) NOT NULL default '0',
  `ignoreoptout` tinyint(4) NOT NULL default '0',
  `regionID` int(11) NOT NULL default '1',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `statusID` (`statusID`),
  KEY `regionID` (`regionID`),
  KEY `eventID` (`eventID`),
  KEY `lastsent` (`lastsent`),
  KEY `firstsend` (`firstsend`),
  KEY `months` (`months`),
  KEY `seconds` (`seconds`),
  KEY `ignoreoptout` (`ignoreoptout`),
  KEY `reminderrepeat` (`reminderrepeat`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- 

-- 
-- Table structure for table `sessions`
-- 
-- aggregate data for track_session

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `ID` varchar(32) NOT NULL,
  `access` int(10) unsigned default NULL,
  `data` text,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM ;


-- 
-- Table structure for table `smsaccount`
-- 

DROP TABLE IF EXISTS `smsaccount`;
CREATE TABLE IF NOT EXISTS `smsaccount` (
	`ID` int(11) NOT NULL auto_increment,
	`accountname` varchar(100) default NULL,
	`apiID` varchar(100) default NULL,
	`username` varchar(100) default NULL,
	`password` varchar(100) default NULL,
	`senderID` varchar(100) default NULL,
	`statusID` tinyint(4) NOT NULL default '1',
	`regionID` int(11) NOT NULL default '1',
	`providerID` int(11) default NULL,
	`createdbyID` int(11) NOT NULL default '0',
	`createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`modifiedbyID` int(11) default NULL,
	`modifieddatetime` datetime default NULL,
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM ;



-- --------------------------------------------------------

-- 
-- Table structure for table `smsprovider`
-- 

DROP TABLE IF EXISTS `smsprovider`;
CREATE TABLE IF NOT EXISTS `smsprovider` (
   `ID` int(11) NOT NULL auto_increment,
  `providername` varchar(100) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM ;

--
-- Dumping data for table `smsprovider`

INSERT INTO `smsprovider` (`ID`, `providername`) VALUES
(1, 'Clickatell');

--

-- --------------------------------------------------------

-- 
-- Table structure for table `status`
-- 

DROP TABLE IF EXISTS `status`;
CREATE TABLE IF NOT EXISTS `status` (
  `ID` tinyint(4) NOT NULL default '0',
  `description` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `status`
-- 

INSERT INTO `status` VALUES (0, 'Draft/Pending Approval');
INSERT INTO `status` VALUES (1, 'Approved');
INSERT INTO `status` VALUES (2, 'REJECTED');
INSERT INTO `status` VALUES (3, 'Do not display');


-- --------------------------------------------------------

--
-- Table structure for table `survey`
--

CREATE TABLE IF NOT EXISTS `survey` (
  `ID` int(11) NOT NULL auto_increment,
  `surveyname` varchar(100) default NULL,
  `introduction` mediumtext,
  `accesslevel` tinyint(4) NOT NULL default '0',
  `statusID` tinyint(4) NOT NULL default '0',
  `canupdate` tinyint(4) NOT NULL default '0',
  `anonymous` tinyint(4) NOT NULL default '0',
  `requiredirectoryID` tinyint(4) NOT NULL default '0',
  `answerrequired` tinyint(4) NOT NULL default '0',
  `multiple` tinyint(4) NOT NULL default '0',
  `usesections` tinyint(4) NOT NULL default '0',
  `usescoring` tinyint(4) NOT NULL default '0',
  `showscores` tinyint(4) NOT NULL default '0',
  `useweighting` tinyint(4) NOT NULL default '0',
  `usecomments` tinyint(4) NOT NULL default '0',
  `useprogress` tinyint(4) NOT NULL default '0',
  `autocalc` tinyint(4) NOT NULL default '0',
  `maxscore` int(11) NOT NULL default '0',
  `passscore` int(11) NOT NULL default '0',
  `redirectURL` varchar(255) default NULL,
  `email` varchar(100) default NULL,
  `showsummary` tinyint(4) NOT NULL default '0',
  `summarystart` tinyint(4) NOT NULL default '0',
  `summaryend` tinyint(4) NOT NULL default '0',
  `confirmationemail` tinyint(4) NOT NULL default '0',
  `confirmationemailcontent` mediumtext,
  `ordernum` int(11) NOT NULL default '0',
  `startdatetime` datetime default NULL,
  `enddatetime` datetime default NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `statusID` (`statusID`),
  KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_answer`
--

CREATE TABLE IF NOT EXISTS `survey_answer` (
  `ID` int(11) NOT NULL auto_increment,
  `questionID` int(11) NOT NULL default '0',
  `answerscore` tinyint(4) default NULL,
  `answertext` text,
  `ordernum` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `questionID` (`questionID`),
  KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_comments`
--

CREATE TABLE IF NOT EXISTS `survey_comments` (
  `ID` int(11) NOT NULL auto_increment,
  `comments` text,
  `sessionID` varchar(32) default NULL,
  `questionID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  PRIMARY KEY  (`ID`),
  KEY `sessionID` (`sessionID`),
  KEY `questionID` (`questionID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `survey_question`
--

CREATE TABLE IF NOT EXISTS `survey_question` (
  `ID` int(11) NOT NULL auto_increment,
  `question_number` varchar(5) default NULL,
  `questionorder` int(11) NOT NULL default '0',
  `questionweight` tinyint(4) default NULL,
  `addscore` tinyint(4) NOT NULL default '1',
  `questiontext` text,
  `questionnotes` text,
  `questiontype` tinyint(4) NOT NULL default '0',
  `maxchoices` tinyint(4) default NULL,
  `passscore` tinyint(4) default NULL,
  `addcommentsbox` tinyint(4) NOT NULL default '1',
  `answerrequired` tinyint(4) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '1',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `surveyID` int(11) NOT NULL default '0',
  `surveysectionID` int(11) NOT NULL default '0',
  `imageURL` varchar(100) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `surveyID` (`surveyID`),
  KEY `active` (`active`),
    KEY `questionorder` (`questionorder`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_question_exception`
--

CREATE TABLE IF NOT EXISTS `survey_question_exception` (
  `ID` int(11) NOT NULL auto_increment,
  `questionID` int(11) NOT NULL,
  `answerID` int(11) NOT NULL,
  `isnot` tinyint(4) NOT NULL default '0',
  `equalto` tinyint(4) NOT NULL default '0',
  `setvalue` varchar(50) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_response_choice`
--

CREATE TABLE IF NOT EXISTS `survey_response_choice` (
  `ID` int(11) NOT NULL auto_increment,
  `answerID` int(11) NOT NULL default '0',
  `sessionID` varchar(32) default NULL,
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  PRIMARY KEY  (`ID`),
  KEY `answerID` (`answerID`),
  KEY `sessionID` (`sessionID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `survey_response_multitext`
-- 

CREATE TABLE IF NOT EXISTS `survey_response_multitext` (
  `ID` int(11) NOT NULL auto_increment,
  `answerID` int(11) NOT NULL,
  `response` varchar(30) NOT NULL,
  `sessionID` varchar(32) NOT NULL,
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `answerID` (`answerID`),
   KEY `sessionID` (`sessionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `survey_response_multitext`
-- 

-- --------------------------------------------------------

--
-- Table structure for table `survey_response_text`
--

CREATE TABLE IF NOT EXISTS `survey_response_text` (
  `ID` int(11) NOT NULL auto_increment,
  `questionID` int(11) NOT NULL default '0',
  `sessionID` varchar(32) default NULL,
  `response_text` text,
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  PRIMARY KEY  (`ID`),
  KEY `sessionID` (`sessionID`),
  KEY `questionID` (`questionID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_scores`
--

CREATE TABLE IF NOT EXISTS `survey_scores` (
  `ID` int(11) NOT NULL auto_increment,
  `score` varchar(5) default NULL,
  `finalscore` varchar(5) default NULL,
  `createddatetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `sessionID` varchar(32) default NULL,
  `questionID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `sessionID` (`sessionID`),
  KEY `questionID` (`questionID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_section`
--

CREATE TABLE IF NOT EXISTS `survey_section` (
  `ID` int(11) NOT NULL auto_increment,
  `surveyID` int(11) NOT NULL default '0',
  `sectionnumber` varchar(5) default NULL,
  `weight` tinyint(4) default NULL,
  `description` varchar(50) default NULL,
  `subsectionofID` int(11) NOT NULL default '0',
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_session`
--

CREATE TABLE IF NOT EXISTS `survey_session` (
  `ID` int(11) NOT NULL auto_increment,
  `sessionID` varchar(32) default NULL,
  `surveyID` int(11) NOT NULL default '0',
  `userID` int(11) default NULL,
  `directoryID` int(11) default NULL,
  `registrationID` int(11) default NULL,
  `startdatetime` datetime NOT NULL default '2008-01-01 00:00:00',
  `enddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `sessionID` (`sessionID`),
  KEY `userID` (`userID`),
  KEY `surveyID` (`surveyID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `surveyprefs`
--

DROP TABLE IF EXISTS `surveyprefs`;
CREATE TABLE IF NOT EXISTS `surveyprefs` (
  `ID` tinyint(4) NOT NULL,
  `surveyName` varchar(50) default 'Survey',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `surveyprefs`
-- 


INSERT INTO `surveyprefs` (ID) VALUES (1);


-- --------------------------------------------------------

-- 
-- Table structure for table `tag`
-- 

DROP TABLE IF EXISTS `tag`;
CREATE TABLE IF NOT EXISTS `tag` (
  `ID` int(11) NOT NULL auto_increment,
  `tagname` varchar(50) NOT NULL,
  `taggroupID` tinyint(4) default NULL,
  `ordernum` int(11) NOT NULL default '0',
  `regionID` int(11) NOT NULL default '1',
  `taggeddefault` tinyint(4) NOT NULL default '0',
  `createdbyID` int(11) default NULL,
  `createddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `taggroupID` (`taggroupID`),
  KEY `ordernum` (`ordernum`),
  KEY `regionID` (`regionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `tagged`
-- 

DROP TABLE IF EXISTS `tagged`;
CREATE TABLE IF NOT EXISTS `tagged` (
  `ID` int(11) NOT NULL auto_increment,
  `blogentryID` int(11) default NULL,
  `eventgroupID` int(11) default NULL,
  `newsID` int(11) default NULL,
  `tagID` int(11) NOT NULL,
  `createdbyID` int(11) default NULL,
  `createddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `blogentryID` (`blogentryID`),
  KEY `eventgroupID` (`eventgroupID`),
  KEY `tagID` (`tagID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `taggroup`
-- 

DROP TABLE IF EXISTS `taggroup`;
CREATE TABLE IF NOT EXISTS `taggroup` (
  `ID` int(11) NOT NULL auto_increment,
  `regionID` int(11) NOT NULL default '1',
  `taggroupname` varchar(30) NOT NULL,
  `ordernum` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`),
   KEY `regionID` (`regionID`),
    KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `track_page`
-- 

DROP TABLE IF EXISTS `track_page`;
CREATE TABLE IF NOT EXISTS `track_page` (
  `ID` int(11) NOT NULL auto_increment,
  `datetime` datetime NOT NULL default '2000-01-01 00:00:00',
  `sessionID` varchar(32) NOT NULL default '',
  `page` varchar(255) default NULL,
  `pageTitle` varchar(50) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `datetime` (`datetime`),
  KEY `pageTitle` (`pageTitle`),
  KEY `sessionID` (`sessionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `track_session`
-- 

DROP TABLE IF EXISTS `track_session`;
CREATE TABLE IF NOT EXISTS `track_session` (
	`ID` varchar(32) NOT NULL default '',
	`datetime` datetime NOT NULL default '2000-01-01 00:00:00',
	`entrypageID` int(11) default NULL,
	`remote_address` varchar(50) default NULL,
	`user_agent` varchar(255) default NULL,
	`referer` text,
	`username` varchar(255) default NULL,
	`prepay` tinyint(4) NOT NULL default '0',
	`postpay` tinyint(4) NOT NULL default '0',
	`screenwidth` int(11) default NULL,
	`screenheight` int(11) default NULL,
	`regionID` int(11) default 1,
	`adwords` int(11) default 0,
	`local` int(11) default 0,
	PRIMARY KEY  (`ID`),
	KEY `username` (`username`),
	KEY `local` (`local`),
	KEY `entrypageID` (`entrypageID`),
	KEY `regionID` (`regionID`),
	KEY `remote_address` (`remote_address`),
	KEY `datetime` (`datetime`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `track_month`
-- 

DROP TABLE IF EXISTS `track_month`;
CREATE TABLE IF NOT EXISTS `track_month` (
  `ID` int(11) NOT NULL auto_increment,
  `logmonth` date  default NULL,
   `hits` int(11) default NULL,
   `regionID` int(11) default 1,
  PRIMARY KEY  (`ID`),
   KEY `regionID` (`regionID`),
  KEY `logmonth` (`logmonth`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;



--
-- Table structure for table `uploads`
--

CREATE TABLE IF NOT EXISTS `uploads` (
  `ID` int(11) NOT NULL auto_increment,
  `filename` varchar(100) NOT NULL,
  `newfilename` varchar(255) NOT NULL,
  `mimetype` varchar(50) default NULL,
  `filesize` int(11) default NULL,
  `systemversion` tinyint(4) default NULL,
  `regionID` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `regionID` (`regionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `usergroup`
-- 

DROP TABLE IF EXISTS `usergroup`;
CREATE TABLE IF NOT EXISTS `usergroup` (
  `ID` int(11) NOT NULL auto_increment,
  `regionID` int(11) NOT NULL default '1',
  `groupsetID` int(11) default NULL,
  `groupname` varchar(50) NOT NULL,
  `groupdescription` text,
  `optin` tinyint(4) NOT NULL default '0',
  `notificationemail` varchar(50) default NULL,
  `renewalcost` double default 0,
  `grouptypeID` int(11)  NULL default NULL,
   `ordernum` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`),
   KEY `regionID` (`regionID`),
    KEY `groupsetID` (`groupsetID`),
	 KEY `optin` (`optin`),
	  KEY `grouptypeID` (`grouptypeID`),
	  KEY `ordernum` (`ordernum`),
	   KEY `statusID` (`statusID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `usergrouptype`
-- 

DROP TABLE IF EXISTS `usergrouptype`;
CREATE TABLE IF NOT EXISTS `usergrouptype` (
  `ID` tinyint(11) NOT NULL auto_increment,
  `grouptype` varchar(50) NOT NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `usergroupmember`
-- 

DROP TABLE IF EXISTS `usergroupmember`;
CREATE TABLE IF NOT EXISTS `usergroupmember` (
  `ID` int(11) NOT NULL auto_increment,
  `userID` int(11) NOT NULL,
  `groupID` int(11)  NOT NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  `ordernum` int(11) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11)  default NULL,
  `modifieddatetime` datetime default NULL,
  `expirydatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `userID` (`userID`),
  KEY `statusID` (`statusID`),
  KEY `ordernum` (`ordernum`),
  KEY `groupID` (`groupID`),
  KEY `expirydatetime` (`expirydatetime`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `usergroupset`
-- 

DROP TABLE IF EXISTS `usergroupset`;
CREATE TABLE IF NOT EXISTS `usergroupset` (
  `ID` int(11) NOT NULL auto_increment,
  `groupsetname` varchar(50) NOT NULL,
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `usergroupset`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `usergroupsetgroup`
-- 

DROP TABLE IF EXISTS `usergroupsetgroup`;
CREATE TABLE IF NOT EXISTS `usergroupsetgroup` (
  `ID` int(11) NOT NULL auto_increment,
  `groupsetID` int(11) NOT NULL,
  `groupID` int(11) NOT NULL,
  `ordernum` int(11) NOT NULL default '0',
  `relationship` tinyint(4) NOT NULL default '1',
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `groupsetID` (`groupsetID`),
  KEY `groupID` (`groupID`),
  KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `usergroupsetgroup`
-- 



-- --------------------------------------------------------

-- 
-- Table structure for table `useremail`
-- 

DROP TABLE IF EXISTS `useremail`;
CREATE TABLE IF NOT EXISTS `useremail` (
  `ID` int(11) NOT NULL auto_increment,
  `email` varchar(50) NOT NULL,
  `userID` int(11) NOT NULL,
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;





-- --------------------------------------------------------

--
-- Table structure for table `usercomments`
--

DROP TABLE IF EXISTS `usercomments`;
CREATE TABLE IF NOT EXISTS `usercomments` (
  `ID` int(11) NOT NULL auto_increment,
  `userID` int(11) NOT NULL,
  `comments` text NOT NULL,
  `createdbyID` int(11) NOT NULL default '1',
  `createddatetime` datetime NOT NULL,
  `modifieddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `userip`;
CREATE TABLE IF NOT EXISTS `userip` (
  `ID` int(11) NOT NULL auto_increment,
  `userID` int(11) NOT NULL,
  `ipv4` varchar(15) NOT NULL,
  `createddatetime` datetime NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `userID` (`userID`),
   KEY `ipv4` (`ipv4`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

-- 
-- Table structure for table `userrelationship`
-- 

DROP TABLE IF EXISTS `userrelationship`;
CREATE TABLE IF NOT EXISTS `userrelationship` (
  `ID` int(11) NOT NULL auto_increment,
  `userID` int(11) NOT NULL,
  `relatedtouserID` int(11) NOT NULL  default '0',
  `relationshiptypeID` tinyint(4) NOT NULL default '0',
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  PRIMARY KEY  (`ID`),
  KEY `userID` (`userID`),
   KEY `relatedtouserID` (`relatedtouserID`),
   KEY `relationshiptypeID` (`relationshiptypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `userrelationshiptype`
-- 

DROP TABLE IF EXISTS `userrelationshiptype`;
CREATE TABLE IF NOT EXISTS `userrelationshiptype` (
  `ID` int(11) NOT NULL auto_increment,
  `relationshiptype` varchar(100) default 'Related',
  `accessID` int(11) NOT NULL default '1',
  `createdbyID` int(11) NOT NULL,
  `createddatetime` datetime NOT NULL,
  `modifiedbyID` int(11) default NULL,
  `modifieddatetime` datetime default NULL,
  `regionID` int(11) NOT NULL default '1',
  `statusID` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
	`ID` int(11) NOT NULL auto_increment,
	`usertypeID` tinyint(4) default NULL,
	`salutation` varchar(6) default NULL,
	`firstname` varchar(50) default NULL,
	`middlename` varchar(50) default NULL,
	`surname` varchar(50) default NULL,
	`gender` tinyint(4) default NULL,
	`dob` date default NULL,
	`agerangeID` tinyint(4) default NULL,
	`ethnicityID` tinyint(4) default NULL,
	`disabilityID` tinyint(4) default NULL,
	`email` varchar(50) default NULL,
	`username` varchar(50) default NULL,
	`password` varchar(32) default NULL,
	`password_salt` varchar(32) default NULL,
	`plainpassword` varchar(20) default NULL,
	`crypttype` tinyint(4) default 1,
	`locationID` int(11) default NULL,
	`regionID` int(11) default NULL,
	`nationalityID` int(11) default NULL,
	`jobtitle` varchar(75) default NULL,
	`NI_number` varchar(16) default NULL,
	`telephone` varchar(20) default NULL,
	`mobile` varchar(20) default NULL,
	`aboutme` mediumtext,
	`defaultaddressID` int(11) default NULL,
	`imageURL` varchar(100) default NULL,
	`twitterID` varchar(50) default NULL,
	`facebookURL` varchar(100) default NULL,
	`youtubeURL` varchar(100) default NULL,
	`websiteURL` varchar(50) default NULL,
	`dateadded` datetime NOT NULL default '2000-01-01 00:00:00',
	`addedbyID` int(11) NOT NULL default '0',
	`termsagree` tinyint(4) NOT NULL default '1',
	`termsagreedate` datetime default NULL,
	`emailoptin` tinyint(4) NOT NULL default '0',
	`partneremailoptin` tinyint(4) NOT NULL default '0',
	`emailbounced` tinyint(4) NOT NULL default '0',
	`emailverified` tinyint(4) NOT NULL default '0',
	`identityverified` tinyint(4) NOT NULL default '0',
	`mobileverified` tinyint(4) NOT NULL default '0',
	`chatstatus` tinyint(4) NOT NULL default '1',   
	`contactbyphone` tinyint(4) NOT NULL default '0',
	`contactbypost` tinyint(4) NOT NULL default '0',
	`deceased` datetime default NULL,
	`showemail` tinyint(4) NOT NULL default '0',
	`changepassword` tinyint(4) NOT NULL default '0',
	`canchangepassword` tinyint(4) NOT NULL default '1',
	`updateprofile` tinyint(4) NOT NULL default '0',
	`discovered` tinyint(4) default NULL,
	`discoveredother` varchar(50) default NULL,
	`twoauth` tinyint(4) NOT NULL default '0',
	`twoauthcode` varchar(12) default NULL,
	`memberspageURL` tinyint(4) default NULL,
	`failedlogin` tinyint(4) NOT NULL default '0',
	`lastlogin` datetime default NULL,
	`membershipexpires` date default NULL,
	`gdpr_date` date default NULL,
	`warning` tinyint(4) default 0,
	`donotautodelete` tinyint(4) default 0,
	`usersettings` mediumtext,
	`modifieddatetime` datetime default NULL,
	`modifiedbyID` int(11) default NULL,
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `username` (`username`),
	KEY `usertypeID` (`usertypeID`),
	KEY `surname` (`surname`),
	KEY `firstname` (`firstname`),
	KEY `dateadded` (`dateadded`),	 
	KEY `email` (`email`),
	KEY `emailoptin` (`emailoptin`),
	KEY `emailbounced` (`emailbounced`),
	KEY `locationID` (`locationID`),
	KEY `disabilityID` (`disabilityID`),
	KEY `ethnicityID` (`ethnicityID`),
	KEY `gender` (`gender`),
	KEY `dob` (`dob`),
	KEY `discovered` (`discovered`),
	KEY `defaultaddressID` (`defaultaddressID`),
	KEY `regionID` (`regionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `users`
-- 





-- --------------------------------------------------------

-- 
-- Table structure for table `usertype`
-- 

DROP TABLE IF EXISTS `usertype`;
CREATE TABLE IF NOT EXISTS `usertype` (
  `ID` int(11) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `usertype`
-- 

INSERT INTO `usertype` VALUES (-2, 'Banned');
INSERT INTO `usertype` VALUES (-1, 'Non Member');
INSERT INTO `usertype` VALUES (0, 'Pending Member');
INSERT INTO `usertype` VALUES (1, 'Member');
INSERT INTO `usertype` VALUES (6, 'Agent');
INSERT INTO `usertype` VALUES (7, 'Staff Member');
INSERT INTO `usertype` VALUES (8, 'Editor');
INSERT INTO `usertype` VALUES (9, 'Manager');
INSERT INTO `usertype` VALUES (10, 'Systems Administrator');


