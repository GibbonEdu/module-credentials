<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//This file describes the module, including database tables

//Basica variables
$name = 'Credentials';
$description = 'Credentials is a module for storing student login details, and making them available via the Student profile. The app is aimed at helping teachers of young students retrieve forgotten usernames and passwords for the various systems they used. IT IS NOT DESIGNED FOR STORAGE OF IMPORTANT PASSWORDS PROTECTING SENSITIVE DATA.';
$entryURL = 'credentials.php';
$type = 'Additional';
$category = 'Admin';
$version = '2.1.04';
$author = 'Ross Parker';
$url = 'http://rossparker.org';

//Module tables
$moduleTables[0] = 'CREATE TABLE `credentialsCredential` (
`credentialsCredentialID` int(12) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `credentialsWebsiteID` int(4) unsigned zerofill DEFAULT NULL,
  `gibbonPersonID` int(10) unsigned zerofill NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NULL DEFAULT NULL,
  `notes` text NOT NULL,
  `gibbonPersonIDCreator` int(10) unsigned zerofill NOT NULL,
  `timestampCreator` datetime NOT NULL,
   PRIMARY KEY (`credentialsCredentialID`),
   UNIQUE `personWebsite` (`credentialsWebsiteID`, `gibbonPersonID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

$moduleTables[1] = 'CREATE TABLE `credentialsWebsite` (
`credentialsWebsiteID` int(4) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `active` enum(\'Y\',\'N\') NOT NULL DEFAULT \'Y\',
  `url` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `gibbonPersonIDCreator` int(10) unsigned zerofill NOT NULL,
  `timestampCreator` datetime NOT NULL,
   PRIMARY KEY (`credentialsWebsiteID`),
   UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

//Action rows
$actionRows[0]['name'] = 'Manage Credentials';
$actionRows[0]['precedence'] = '0';
$actionRows[0]['category'] = 'Credentials';
$actionRows[0]['description'] = 'Allows privileged users to view and manage credentials by student.';
$actionRows[0]['URLList'] = 'credentials.php, credentials_student.php, credentials_student_add.php, credentials_student_edit.php, credentials_student_delete.php';
$actionRows[0]['entryURL'] = 'credentials.php';
$actionRows[0]['defaultPermissionAdmin'] = 'Y';
$actionRows[0]['defaultPermissionTeacher'] = 'N';
$actionRows[0]['defaultPermissionStudent'] = 'N';
$actionRows[0]['defaultPermissionParent'] = 'N';
$actionRows[0]['defaultPermissionSupport'] = 'N';
$actionRows[0]['categoryPermissionStaff'] = 'Y';
$actionRows[0]['categoryPermissionStudent'] = 'N';
$actionRows[0]['categoryPermissionParent'] = 'N';
$actionRows[0]['categoryPermissionOther'] = 'N';

$actionRows[1]['name'] = 'View Credentials in Student Profile';
$actionRows[1]['precedence'] = '0';
$actionRows[1]['category'] = '';
$actionRows[1]['description'] = 'Allows users with access to view student profiles to see login details, including passwords.';
$actionRows[1]['URLList'] = 'hook_studentProfile_credentials.php';
$actionRows[1]['entryURL'] = '';
$actionRows[1]['defaultPermissionAdmin'] = 'Y';
$actionRows[1]['defaultPermissionTeacher'] = 'Y';
$actionRows[1]['defaultPermissionStudent'] = 'N';
$actionRows[1]['defaultPermissionParent'] = 'N';
$actionRows[1]['defaultPermissionSupport'] = 'N';
$actionRows[1]['categoryPermissionStaff'] = 'Y';
$actionRows[1]['categoryPermissionStudent'] = 'N';
$actionRows[1]['categoryPermissionParent'] = 'N';
$actionRows[1]['categoryPermissionOther'] = 'N';

$actionRows[2]['name'] = 'Import Credentials';
$actionRows[2]['precedence'] = '0';
$actionRows[2]['category'] = 'Credentials';
$actionRows[2]['description'] = 'Allows users with access to import credentials for one or more users.';
$actionRows[2]['URLList'] = 'import_credentials.php';
$actionRows[2]['entryURL'] = 'import_credentials.php';
$actionRows[2]['defaultPermissionAdmin'] = 'Y';
$actionRows[2]['defaultPermissionTeacher'] = 'N';
$actionRows[2]['defaultPermissionStudent'] = 'N';
$actionRows[2]['defaultPermissionParent'] = 'N';
$actionRows[2]['defaultPermissionSupport'] = 'N';
$actionRows[2]['categoryPermissionStaff'] = 'Y';
$actionRows[2]['categoryPermissionStudent'] = 'N';
$actionRows[2]['categoryPermissionParent'] = 'N';
$actionRows[2]['categoryPermissionOther'] = 'N';

$actionRows[3]['name'] = 'Manage Websites';
$actionRows[3]['precedence'] = '0';
$actionRows[3]['category'] = 'Websites';
$actionRows[3]['description'] = 'Allows privileged users to manage website details.';
$actionRows[3]['URLList'] = 'websites.php, websites_add.php, websites_edit.php, websites_delete.php';
$actionRows[3]['entryURL'] = 'websites.php';
$actionRows[3]['defaultPermissionAdmin'] = 'Y';
$actionRows[3]['defaultPermissionTeacher'] = 'N';
$actionRows[3]['defaultPermissionStudent'] = 'N';
$actionRows[3]['defaultPermissionParent'] = 'N';
$actionRows[3]['defaultPermissionSupport'] = 'N';
$actionRows[3]['categoryPermissionStaff'] = 'Y';
$actionRows[3]['categoryPermissionStudent'] = 'N';
$actionRows[3]['categoryPermissionParent'] = 'N';
$actionRows[3]['categoryPermissionOther'] = 'N';

$actionRows[4]['name'] = 'View Credentials in Parent Dashboard';
$actionRows[4]['precedence'] = '0';
$actionRows[4]['category'] = '';
$actionRows[4]['description'] = 'Allows parents to see login details, including passwords, for their children in their family.';
$actionRows[4]['URLList'] = 'hook_parentDashboard_credentials.php';
$actionRows[4]['entryURL'] = '';
$actionRows[4]['defaultPermissionAdmin'] = 'N';
$actionRows[4]['defaultPermissionTeacher'] = 'N';
$actionRows[4]['defaultPermissionStudent'] = 'N';
$actionRows[4]['defaultPermissionParent'] = 'Y';
$actionRows[4]['defaultPermissionSupport'] = 'N';
$actionRows[4]['categoryPermissionStaff'] = 'N';
$actionRows[4]['categoryPermissionStudent'] = 'N';
$actionRows[4]['categoryPermissionParent'] = 'Y';
$actionRows[4]['categoryPermissionOther'] = 'N';

$array = array();
$array['sourceModuleName'] = 'Credentials';
$array['sourceModuleAction'] = 'View Credentials in Student Profile';
$array['sourceModuleInclude'] = 'hook_studentProfile_credentials.php';
$hooks[0] = "INSERT INTO `gibbonHook` (`gibbonHookID`, `name`, `type`, `options`, gibbonModuleID) VALUES (NULL, 'Credentials', 'Student Profile', '".serialize($array)."', (SELECT gibbonModuleID FROM gibbonModule WHERE name='$name'));";
$array['sourceModuleAction'] = 'View Credentials in Parent Dashboard';
$array['sourceModuleInclude'] = 'hook_parentDashboard_credentials.php';
$hooks[1] = "INSERT INTO `gibbonHook` (`gibbonHookID`, `name`, `type`, `options`, gibbonModuleID) VALUES (NULL, 'Credentials', 'Parental Dashboard', '".serialize($array)."', (SELECT gibbonModuleID FROM gibbonModule WHERE name='$name'));";
