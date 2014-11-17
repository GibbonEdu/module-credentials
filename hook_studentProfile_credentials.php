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

@session_start() ;

//Module includes
include "./modules/Credentials/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Credentials/hook_studentProfile_credentials.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	try {
		$data=array("gibbonPersonID"=>$gibbonPersonID); 
		$sql="SELECT * FROM credentialsCredential WHERE gibbonPersonID=:gibbonPersonID ORDER BY title" ; 
		$result=$connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) { 
		print "<div class='error'>" . $e->getMessage() . "</div>" ; 
	}
	
	if ($result->rowCount()<1) {
		print "<div class='error'>" ;
		print _("There are no records to display.") ;
		print "</div>" ;
	}
	else {
		print "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>" ;
			print "<tr class='head'>" ;
				print "<th>" ;
					print _("Title") . "<br/>" ;
				print "</th>" ;
				print "<th>" ;
					print _("Username") ;
				print "</th>" ;
				print "<th>" ;
					print _("Password") . "<br/>" ;
				print "</th>" ;
			print "</tr>" ;
			
			//Decryption defines
			define('SAFETY_CIPHER', MCRYPT_RIJNDAEL_256);
			define('SAFETY_MODE', MCRYPT_MODE_CFB);
			define('APPLICATION_WIDE_PASSPHRASE', $guid);
			define('ENCRYPTION_DIVIDER_TOKEN', '$$');

			$count=0;
			$rowNum="odd" ;
			while ($row=$result->fetch()) {
				if ($count%2==0) {
					$rowNum="even" ;
				}
				else {
					$rowNum="odd" ;
				}
				$count++ ;
				
				//COLOR ROW BY STATUS!
				print "<tr class=$rowNum>" ;
					print "<td>" ;
						if ($row["url"]!="") {
							print "<a href='" .$row["url"] . "' target='_blank'>" . $row["title"] . "</a>" ;
						}
						else {
							print $row["title"] ;
						}
					print "</td>" ;
					print "<td>" ;
						print $row["username"] ;
					print "</td>" ;
					print "<td>" ;
						if ($row["password"]!="") {
							//Key, etc.
							$key=substr(md5(APPLICATION_WIDE_PASSPHRASE), 0, mcrypt_get_key_size(SAFETY_CIPHER, SAFETY_MODE));
							
							//Decrypt
							print mcrypt_decrypt(SAFETY_CIPHER, $key, base64_decode(substr($row["password"], (strpos($row["password"], "$$")+2))), SAFETY_MODE, base64_decode(substr($row["password"],0, strpos($row["password"], "$$")))) . '<br/>';
						}
					print "</td>" ;
				print "</tr>" ;
			}
		print "</table>" ;
	}
}
?>