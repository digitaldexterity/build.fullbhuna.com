

-- --------------------------------------------------------

--
-- Table structure for table `wf_template`
--

DROP TABLE IF EXISTS `wf_template`;
CREATE TABLE IF NOT EXISTS `wf_template` (
  `ID` int(11) NOT NULL auto_increment,
  `templatename` varchar(100) NOT NULL,
   `front_imageURL` varchar(255) default NULL,
   `back_imageURL` varchar(255) default NULL,
  `statusID` tinyint(4) NOT NULL default '1',
  
  `createdbyID` int(11) NOT NULL default '0',
  `createddatetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `modifieddatetime` datetime default NULL,
  `modifiedbyID` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `statusID` (`statusID`)
) ENGINE=myISAM  AUTO_INCREMENT=1 ;



