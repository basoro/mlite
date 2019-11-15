CREATE TABLE `sms_autolist` (
  `phoneNumber` varchar(15) NOT NULL default '',
  `id` int(11) NOT NULL default '0',
  `status` int(11) default NULL,
  PRIMARY KEY  (`phoneNumber`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_autoresponder` (
  `id` int(11) NOT NULL auto_increment,
  `msg` text,
  `interv` int(11) default NULL,
  `idgroup` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_group` (
  `idgroup` int(11) NOT NULL auto_increment,
  `group` varchar(50) default NULL,
  PRIMARY KEY  (`idgroup`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_inbox` (
  `id` int(11) NOT NULL auto_increment,
  `msg` text,
  `sender` varchar(20) default NULL,
  `time` datetime default NULL,
  `flagRead` int(11) default NULL,
  `flagReply` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_message` (
  `id` int(11) NOT NULL auto_increment,
  `message` text,
  `pubdate` DATETIME NULL DEFAULT NULL,
  `status` int(11) NOT NULL,
  `idgroup` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_phonebook` (
  `noTelp` varchar(50) NOT NULL default '',
  `nama` varchar(50) default NULL,
  `alamat` varchar(100) default NULL,
  `idgroup` int(11) default NULL,
  `dateJoin` date default NULL,
  PRIMARY KEY  (`noTelp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_sentmsg` (
  `id` int(11) NOT NULL auto_increment,
  `msg` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_data` (
  `keyword` varchar(20) NOT NULL default '',
  `key` varchar(100) NOT NULL default '',
  `field1` varchar(100) default NULL,
  `field2` varchar(100) default NULL,
  `field3` varchar(100) default NULL,
  `field4` varchar(100) default NULL,
  `field5` varchar(100) default NULL,
  PRIMARY KEY  (`keyword`,`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_keyword` (
  `keyword` varchar(100) NOT NULL default '',
  `template` varchar(500) default NULL,
  PRIMARY KEY  (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `gammu` (
  `Version` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `inbox` (
  `UpdatedInDB` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ReceivingDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Text` text NOT NULL,
  `SenderNumber` varchar(20) NOT NULL DEFAULT '',
  `Coding` enum('Default_No_Compression','Unicode_No_Compression','8bit','Default_Compression','Unicode_Compression') NOT NULL DEFAULT 'Default_No_Compression',
  `UDH` text NOT NULL,
  `SMSCNumber` varchar(20) NOT NULL DEFAULT '',
  `Class` int(11) NOT NULL DEFAULT '-1',
  `TextDecoded` text NOT NULL,
  `ID` int(10) unsigned NOT NULL,
  `RecipientID` text NOT NULL,
  `Processed` enum('false','true') NOT NULL DEFAULT 'false',
  `Status` int(11) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `outbox` (
  `UpdatedInDB` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `InsertIntoDB` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SendingDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SendBefore` time NOT NULL DEFAULT '23:59:59',
  `SendAfter` time NOT NULL DEFAULT '00:00:00',
  `Text` text,
  `DestinationNumber` varchar(20) NOT NULL DEFAULT '',
  `Coding` enum('Default_No_Compression','Unicode_No_Compression','8bit','Default_Compression','Unicode_Compression') NOT NULL DEFAULT 'Default_No_Compression',
  `UDH` text,
  `Class` int(11) DEFAULT '-1',
  `TextDecoded` text NOT NULL,
  `ID` int(10) unsigned NOT NULL,
  `MultiPart` enum('false','true') DEFAULT 'false',
  `RelativeValidity` int(11) DEFAULT '-1',
  `SenderID` varchar(255) DEFAULT NULL,
  `SendingTimeOut` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `DeliveryReport` enum('default','yes','no') DEFAULT 'default',
  `CreatorID` text NOT NULL,
  `Retries` int(3) DEFAULT '0',
  `Priority` int(11) DEFAULT '0',
  `Status` enum('SendingOK','SendingOKNoReport','SendingError','DeliveryOK','DeliveryFailed','DeliveryPending','DeliveryUnknown','Error','Reserved') NOT NULL DEFAULT 'Reserved',
  `StatusCode` int(11) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `outbox_multipart` (
  `Text` text,
  `Coding` enum('Default_No_Compression','Unicode_No_Compression','8bit','Default_Compression','Unicode_Compression') NOT NULL DEFAULT 'Default_No_Compression',
  `UDH` text,
  `Class` int(11) DEFAULT '-1',
  `TextDecoded` text,
  `ID` int(10) unsigned NOT NULL DEFAULT '0',
  `SequencePosition` int(11) NOT NULL DEFAULT '1',
  `Status` enum('SendingOK','SendingOKNoReport','SendingError','DeliveryOK','DeliveryFailed','DeliveryPending','DeliveryUnknown','Error','Reserved') NOT NULL DEFAULT 'Reserved',
  `StatusCode` int(11) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `phones` (
  `ID` text NOT NULL,
  `UpdatedInDB` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `InsertIntoDB` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TimeOut` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Send` enum('yes','no') NOT NULL DEFAULT 'no',
  `Receive` enum('yes','no') NOT NULL DEFAULT 'no',
  `IMEI` varchar(35) NOT NULL,
  `IMSI` varchar(35) NOT NULL,
  `NetCode` varchar(10) DEFAULT 'ERROR',
  `NetName` varchar(35) DEFAULT 'ERROR',
  `Client` text NOT NULL,
  `Battery` int(11) NOT NULL DEFAULT '-1',
  `Signal` int(11) NOT NULL DEFAULT '-1',
  `Sent` int(11) NOT NULL DEFAULT '0',
  `Received` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sentitems` (
  `UpdatedInDB` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `InsertIntoDB` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SendingDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DeliveryDateTime` timestamp NULL DEFAULT NULL,
  `Text` text NOT NULL,
  `DestinationNumber` varchar(20) NOT NULL DEFAULT '',
  `Coding` enum('Default_No_Compression','Unicode_No_Compression','8bit','Default_Compression','Unicode_Compression') NOT NULL DEFAULT 'Default_No_Compression',
  `UDH` text NOT NULL,
  `SMSCNumber` varchar(20) NOT NULL DEFAULT '',
  `Class` int(11) NOT NULL DEFAULT '-1',
  `TextDecoded` text NOT NULL,
  `ID` int(10) unsigned NOT NULL DEFAULT '0',
  `SenderID` varchar(255) NOT NULL,
  `SequencePosition` int(11) NOT NULL DEFAULT '1',
  `Status` enum('SendingOK','SendingOKNoReport','SendingError','DeliveryOK','DeliveryFailed','DeliveryPending','DeliveryUnknown','Error') NOT NULL DEFAULT 'SendingOK',
  `StatusError` int(11) NOT NULL DEFAULT '-1',
  `TPMR` int(11) NOT NULL DEFAULT '-1',
  `RelativeValidity` int(11) NOT NULL DEFAULT '-1',
  `CreatorID` text NOT NULL,
  `StatusCode` int(11) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

ALTER TABLE `gammu`
  ADD PRIMARY KEY (`Version`);

ALTER TABLE `inbox`
  ADD PRIMARY KEY (`ID`);

ALTER TABLE `outbox`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `outbox_date` (`SendingDateTime`,`SendingTimeOut`),
  ADD KEY `outbox_sender` (`SenderID`(250));

ALTER TABLE `outbox_multipart`
  ADD PRIMARY KEY (`ID`,`SequencePosition`);

ALTER TABLE `phones`
  ADD PRIMARY KEY (`IMEI`);

ALTER TABLE `sentitems`
  ADD PRIMARY KEY (`ID`,`SequencePosition`),
  ADD KEY `sentitems_date` (`DeliveryDateTime`),
  ADD KEY `sentitems_tpmr` (`TPMR`),
  ADD KEY `sentitems_dest` (`DestinationNumber`),
  ADD KEY `sentitems_sender` (`SenderID`(250));

ALTER TABLE `inbox`
  MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `outbox`
  MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT;

INSERT INTO `gammu` (`Version`) VALUES (16);

/*ALTER TABLE `sms`
  ADD `stts_baca` INT(1) NULL DEFAULT NULL  AFTER `tgl_sms`,
  ADD `stts_balas` INT(1) NULL DEFAULT NULL  AFTER `stts_baca`;
*/
