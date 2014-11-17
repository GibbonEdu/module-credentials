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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

@session_start() ;

if (isActionAccessible($guid, $connection2, "/modules/Credentials/import_credentials.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print _("You do not have access to this action.") ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . _("Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . _(getModuleName($_GET["q"])) . "</a> > </div><div class='trailEnd'>" . _('Import Credentials') . "</div>" ;
	print "</div>" ;
	
	$step=NULL ;
	if (isset($_GET["step"])) {
		$step=$_GET["step"] ;
	}
	if ($step=="") {
		$step=1 ;
	}
	else if (($step!=1) AND ($step!=2)) {
		$step=1 ;
	}
	
	//STEP 1, SELECT TERM
	if ($step==1) {
		?>
		<h2>
			<?php print _('Step 1 - Select CSV Files') ?>
		</h2>
		<p>
			<?php print _('This page allows you to import student credentials from a CSV file. The import will add credentials for sites a user user does not already have (based on name and URL), and update them otherwise. No credentials will be removed. Select the CSV file you wish to use for the import operation.') ?><br/>
		</p>
		<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/import_credentials.php&step=2" ?>" enctype="multipart/form-data">
			<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
				<tr>
					<td style='width: 275px'> 
						<b><?php print _('CSV File') ?> *</b><br/>
						<span style="font-size: 90%"><i><?php print _('See Notes below for specification.') ?></i></span>
					</td>
					<td class="right">
						<input type="file" name="file" id="file" size="chars">
						<script type="text/javascript">
							var file=new LiveValidation('file');
							file.add(Validate.Presence);
						</script>
					</td>
				</tr>
				<tr>
					<td> 
						<b><?php print _('Field Delimiter') ?> *</b><br/>
					</td>
					<td class="right">
						<input type="text" style="width: 300px" name="fieldDelimiter" value="," maxlength=1>
						<script type="text/javascript">
							var fieldDelimiter=new LiveValidation('fieldDelimiter');
							fieldDelimiter.add(Validate.Presence);
						 </script>
					</td>
				</tr>
				<tr>
					<td> 
						<b><?php print _('String Enclosure') ?> *</b><br/>
						<span style="font-size: 90%"><i></i></span>
					</td>
					<td class="right">
						<input type="text" style="width: 300px" name="stringEnclosure" value='"' maxlength=1>
						<script type="text/javascript">
							var stringEnclosure=new LiveValidation('stringEnclosure');
							stringEnclosure.add(Validate.Presence);
						 </script>
					</td>
				</tr>
				<tr>
					<td>
						<span style="font-size: 90%"><i>* <?php print _("denotes a required field") ; ?></i></span>
					</td>
					<td class="right">
						<input name="gibbonSchoolYearID" id="gibbonSchoolYearID" value="<?php print $gibbonSchoolYearID ?>" type="hidden">
						<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
						<input type="submit" value="<?php print _("Submit") ; ?>">
					</td>
				</tr>
			</table>
		</form>
		
		
		
		<h4>
			<?php print _('Notes') ?>
		</h4>
		<ol>
			<li style='color: #c00; font-weight: bold'><?php print _('THE SYSTEM WILL NOT PROMPT YOU TO PROCEED, IT WILL JUST DO THE IMPORT. BACKUP YOUR DATA.') ?></li>
			<li><?php print _('You may only submit CSV files.') ?></li>
			<li><?php print _('Imports cannot be run concurrently (e.g. make sure you are the only person importing at any one time).') ?></li>
			<li><?php print _('Your import should only include all current students.') ?></li>
			<li><?php print _('The submitted file must have the following fields in the following order (* denotes required field):') ?></li> 
				<ol>
					<li><b><?php print sprintf(_('%1$s Username'), $_SESSION[$guid]["systemName"]) ?> *</b></li>
					<li><b><?php print _('Title') ?> *</b> - <?php print _('Name of site.') ?></li>
					<li><b><?php print _('URL') ?></b></li>
					<li><b><?php print _('Credential Username') ?></b></li>
					<li><b><?php print _('Password') ?></b> - <?php print _('Plain text. It will be encrypted before being saved in database.') ?></li>
				</ol>
			</li>
			<li><?php print _('Do not include a header row in the CSV files.') ?></li>
		</ol>
	<?php
	}
	else if ($step==2) {
		?>
		<h2>
			<?php print _('Step 2 - Data Check & Confirm') ?>
		</h2>
		<?php
		
		//Check file type
		if (($_FILES['file']['type']!="text/csv") AND ($_FILES['file']['type']!="text/comma-separated-values") AND ($_FILES['file']['type']!="text/x-comma-separated-values") AND ($_FILES['file']['type']!="application/vnd.ms-excel")) {
			?>
			<div class='error'>
				<?php print sprintf(_('Import cannot proceed, as the submitted file has a MIME-TYPE of %1$s, and as such does not appear to be a CSV file.'), $_FILES['file']['type']) ?><br/>
			</div>
			<?php
		}
		else if (($_POST["fieldDelimiter"]=="") OR ($_POST["stringEnclosure"]=="")) {
			?>
			<div class='error'>
				<?php print _('Import cannot proceed, as the "Field Delimiter" and/or "String Enclosure" fields have been left blank.') ?><br/>
			</div>
			<?php
		}
		else {
			$proceed=true ;
			
			//PREPARE TABLES
			print "<h4>" ;
				print _("Prepare Database Tables") ;
			print "</h4>" ;
			//Lock tables
			$lockFail=false ;
			try {
				$sql="LOCK TABLES credentialsCredential WRITE, gibbonPerson WRITE" ;
				$result=$connection2->query($sql);   
			}
			catch(PDOException $e) {
				$lockFail=true ; 
				$proceed=false ;
			}
			if ($lockFail==true) {
				print "<div class='error'>" ;
					print _("The database could not be locked for use.") ;
				print "</div>" ;	
			}
			else if ($lockFail==false) {
				print "<div class='success'>" ;
					print _("The database was successfully locked.") ;
				print "</div>" ;	
			}	
			
			if ($lockFail==FALSE) {	
				//READ IN DATA
				if ($proceed==true) {
					print "<h4>" ;
						print _("File Import") ;
					print "</h4>" ;
					$importFail=false ;
					$csvFile=$_FILES['file']['tmp_name'] ;
					$handle=fopen($csvFile, "r");
					$users=array() ;
					$userCount=0 ;
					$userSuccessCount=0 ;
					while (($data=fgetcsv($handle, 100000, stripslashes($_POST["fieldDelimiter"]), stripslashes($_POST["stringEnclosure"]))) !==FALSE) {
						if ($data[0]!="" AND $data[1]!="") {
							$users[$userSuccessCount]["username"]=$data[0] ;
							$users[$userSuccessCount]["title"]=$data[1] ;
							$users[$userSuccessCount]["url"]=$data[2] ;
							$users[$userSuccessCount]["username2"]=$data[3] ;
							$users[$userSuccessCount]["password"]=$data[4] ;
							$userSuccessCount++ ;
						}
						else {
							print "<div class='error'>" ;
								print sprintf(_('Student with username %1$s had some information malformations.'), $data[0]) ;
							print "</div>" ;
						}
						$userCount++ ;
					}
					fclose($handle);
					if ($userSuccessCount==0) {
						print "<div class='error'>" ;
							print _("No useful credentials were detected in the import file (perhaps they did not meet minimum requirements), so the import will be aborted.") ;
						print "</div>" ;
						$proceed=false ;
					}
					else if ($userSuccessCount<$userCount) {
						print "<div class='error'>" ;
							print _("Some credentials could not be successfully read or used, so the import will be aborted.") ;
						print "</div>" ;
						$proceed=false ;
					}
					else if ($userSuccessCount==$userCount) {
						print "<div class='success'>" ;
							print _("All credentials could be read and used, so the import will proceed.") ;
						print "</div>" ;
					}
					else {
						print "<div class='error'>" ;
							print _("An unknown error occured, so the import will be aborted.") ;
						print "</div>" ;
						$proceed=false ;
					}
				}
				
				//Encryption defines
				define('SAFETY_CIPHER', MCRYPT_RIJNDAEL_256);
				define('SAFETY_MODE', MCRYPT_MODE_CFB);
				define('APPLICATION_WIDE_PASSPHRASE', $guid);
				define('ENCRYPTION_DIVIDER_TOKEN', '$$');
				
				if ($proceed==TRUE) {
					print "<h4>" ;
						print _("Enrol All Students") ;
					print "</h4>" ;
					foreach ($users AS $user) {
						$checkFail=FALSE ;
						//Check if credential exists for user is 
						try {
							$data=array("username"=>$user["username"], "title"=>$user["title"], "url"=>$user["url"]); 
							$sql="SELECT * FROM credentialsCredential JOIN gibbonPerson ON (credentialsCredential.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonPerson.username=:username AND credentialsCredential.title=:title AND credentialsCredential.url=:url" ;
							$result=$connection2->prepare($sql);
							$result->execute($data);
						}
						catch(PDOException $e) { 
							$checkFail=TRUE ;
						}
						
						if ($checkFail==TRUE) {
							print "<div class='error'>" ;
								print _("There was an error with credential:") . " " . $user["username"] . ", " . $user["title"] . ", " . $user["url"] ;
							print "</div>" ;
						}
						else {
							$passwordFinal=NULL ;
							if ($user["password"]!="") {
								//Password, key, etc.
								$password=$user["password"] ;
								$key=substr(md5(APPLICATION_WIDE_PASSPHRASE), 0, mcrypt_get_key_size(SAFETY_CIPHER, SAFETY_MODE));
								$initVector=mcrypt_create_iv(mcrypt_get_iv_size(SAFETY_CIPHER, SAFETY_MODE), MCRYPT_RAND);

								//Encrypt & prepare
								$encrypted=mcrypt_encrypt(SAFETY_CIPHER, $key, $password, SAFETY_MODE, $initVector);
								$passwordFinal=base64_encode($initVector) . ENCRYPTION_DIVIDER_TOKEN . base64_encode($encrypted) ;
							}
							
							if ($result->rowCount()<1) { //INSERT
								$credentialInsertFail=FALSE ;
								try {
									$data=array("username"=>$user["username"], "title"=>$user["title"], "url"=>$user["url"], "username2"=>$user["username2"], "password"=>$passwordFinal, "gibbonPersonIDCreator"=>$_SESSION[$guid]["gibbonPersonID"], "timestampCreator"=>date('Y-m-d H:i:s', time())); 
									$sql="INSERT INTO credentialsCredential SET gibbonPersonID=(SELECT gibbonPersonID FROM gibbonPerson WHERE username=:username), title=:title, url=:url, username=:username2, password=:password, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestampCreator=:timestampCreator" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) { 
									$credentialInsertFail=TRUE ;
								}
					
								//Spit out results
								if ($credentialInsertFail==TRUE) {
									print "<div class='error'>" ;
										print $e->getMessage() ;
										print _("There was an error with credential:") . " " . $user["username"] . ", " . $user["title"] . ", " . $user["url"] ;
									print "</div>" ;
								}
								else {
									print "<div class='success'>" ;
										print _('The following credential was successfully inserted:') . " " . $user["username"] . ", " . $user["title"] . ", " . $user["url"] ;
									print "</div>" ;
								}
							}
							else { //UPDATE
								$credentialInsertFail=FALSE ;
								try {
									$data=array("username"=>$user["username"], "title"=>$user["title"], "url"=>$user["url"], "username2"=>$user["username2"], "password"=>$passwordFinal); 
									$sql="UPDATE credentialsCredential SET username=:username2, password=:password WHERE gibbonPersonID=(SELECT gibbonPersonID FROM gibbonPerson WHERE username=:username) AND title=:title AND url=:url" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) { 
									$credentialInsertFail=TRUE ;
								}
					
								//Spit out results
								if ($credentialInsertFail==TRUE) {
									print "<div class='error'>" ;
										print _("There was an error with credential:") . " " . $user["username"] . ", " . $user["title"] . ", " . $user["url"] ;
									print "</div>" ;
								}
								else {
									print "<div class='success'>" ;
										print _('The following credential was successfully updated:') . " " . $user["username"] . ", " . $user["title"] . ", " . $user["url"] ;
									print "</div>" ;
								}
							}
						}
					}
				}
				
				//UNLOCK TABLES
				try {
					$sql="UNLOCK TABLES" ;
					$result=$connection2->query($sql);   
				}
				catch(PDOException $e) { }	
			}			
		}
	}
}
?>