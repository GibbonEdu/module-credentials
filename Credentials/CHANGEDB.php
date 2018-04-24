<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = array();
$count = 0;

//v1.0.00 - FIRST VERSION, SO NO CHANGES
$sql[$count][0] = '1.0.00';
$sql[$count][1] = '';

//v1.0.01
++$count;
$sql[$count][0] = '1.0.01';
$sql[$count][1] = '';

//v1.0.02
++$count;
$sql[$count][0] = '1.0.02';
$sql[$count][1] = '';

//v1.0.03
++$count;
$sql[$count][0] = '1.0.03';
$sql[$count][1] = '';

//v1.0.04
++$count;
$sql[$count][0] = '1.0.04';
$sql[$count][1] = '';

//v2.0.00
++$count;
$sql[$count][0] = '2.0.00';
$sql[$count][1] = "
ALTER TABLE `credentialsCredential` DROP `title`, DROP `url`;end
ALTER TABLE `credentialsCredential` ADD `credentialsWebsiteID` INT(4) UNSIGNED ZEROFILL NULL DEFAULT NULL AFTER `credentialsCredentialID`;end
ALTER TABLE `credentialsCredential` ADD `notes` TEXT NOT NULL AFTER `password`;end
CREATE TABLE `credentialsWebsite` (`credentialsWebsiteID` int(4) unsigned zerofill NOT NULL AUTO_INCREMENT,  `title` varchar(100) NOT NULL,  `active` enum('Y','N') NOT NULL DEFAULT 'Y', `url` varchar(255) NOT NULL,  `logo` varchar(255) NOT NULL,  `notes` text NOT NULL,  `gibbonPersonIDCreator` int(10) unsigned zerofill NOT NULL,  `timestampCreator` datetime NOT NULL, PRIMARY KEY (`credentialsWebsiteID`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `entrySidebar`, `menuShow`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Credentials'), 'Manage Websites', 0, 'Websites', 'Allows privileged users to manage website details.', 'websites.php, websites_add.php, websites_edit.php, websites_delete.php', 'websites.php', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N') ;end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Credentials' AND gibbonAction.name='Manage Websites'));end
UPDATE gibbonAction SET category='Credentials' WHERE (name='Manage Credentials' OR name='Import Credentials') AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Credentials');end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `entrySidebar`, `menuShow`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Credentials'), 'View Credentials in Parent Dashboard', 0, '', 'Allows parents to see login details, including passwords, for their children in their family.', 'hook_parentDashboard_credentials.php', '', 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N', 'Y', 'N') ;end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '4', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Credentials' AND gibbonAction.name='View Credentials in Parent Dashboard'));end
INSERT INTO `gibbonHook` (`gibbonHookID`, `name`, `type`, `options`, gibbonModuleID) VALUES (NULL, 'Credentials', 'Parental Dashboard', 'a:3:{s:16:\"sourceModuleName\";s:11:\"Credentials\";s:18:\"sourceModuleAction\";s:36:\"View Credentials in Parent Dashboard\";s:19:\"sourceModuleInclude\";s:36:\"hook_parentDashboard_credentials.php\";}', (SELECT gibbonModuleID FROM gibbonModule WHERE name='Credentials'));end
";

//v2.0.01
++$count;
$sql[$count][0] = '2.0.01';
$sql[$count][1] = '
ALTER TABLE `credentialsWebsite` ADD UNIQUE `title` (`title`);end
ALTER TABLE `credentialsCredential` ADD UNIQUE `personWebsite` (`credentialsWebsiteID`, `gibbonPersonID`);
';

//2.1.00
++$count;
$sql[$count][0] = '2.1.00';
$sql[$count][1] = "
UPDATE gibbonModule SET category='People' WHERE name='Credentials';end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `entrySidebar`, `menuShow`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Credentials'), 'View Credentials', 0, 'Credentials', 'Allows users to see login details, including passwords, for all students in school.', 'credentials_view.php, credentials_view_student.php', 'credentials_view.php', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N') ;end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Credentials' AND gibbonAction.name='View Credentials'));end
";

//2.1.01
++$count;
$sql[$count][0] = '2.1.01';
$sql[$count][1] = "";

//2.1.02
++$count;
$sql[$count][0] = '2.1.02';
$sql[$count][1] = "";

//2.1.03
++$count;
$sql[$count][0] = '2.1.03';
$sql[$count][1] = "";

//2.1.04
++$count;
$sql[$count][0] = '2.1.04';
$sql[$count][1] = "";

//2.2.00
++$count;
$sql[$count][0] = '2.2.00';
$sql[$count][1] = "";
