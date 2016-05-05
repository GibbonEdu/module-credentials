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

@session_start();

if (isActionAccessible($guid, $connection2, '/modules/Credentials/import_credentials.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".__($guid, getModuleName($_GET['q']))."</a> > </div><div class='trailEnd'>".__($guid, 'Import Credentials').'</div>';
    echo '</div>';

    $step = null;
    if (isset($_GET['step'])) {
        $step = $_GET['step'];
    }
    if ($step == '') {
        $step = 1;
    } elseif (($step != 1) and ($step != 2)) {
        $step = 1;
    }

    //STEP 1, SELECT TERM
    if ($step == 1) {
        ?>
		<h2>
			<?php echo __($guid, 'Step 1 - Select CSV Files') ?>
		</h2>
		<p>
			<?php echo __($guid, 'This page allows you to import student credentials from a CSV file. The import will add credentials for sites a user user does not already have (based on name and URL), and update them otherwise. No credentials will be removed. Select the CSV file you wish to use for the import operation.') ?><br/>
		</p>
		<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/import_credentials.php&step=2' ?>" enctype="multipart/form-data">
			<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
				<tr>
					<td style='width: 275px'> 
						<b><?php echo __($guid, 'CSV File') ?> *</b><br/>
						<span style="font-size: 90%"><i><?php echo __($guid, 'See Notes below for specification.') ?></i></span>
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
						<b><?php echo __($guid, 'Field Delimiter') ?> *</b><br/>
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
						<b><?php echo __($guid, 'String Enclosure') ?> *</b><br/>
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
						<span style="font-size: 90%"><i>* <?php echo __($guid, 'denotes a required field'); ?></i></span>
					</td>
					<td class="right">
						<input name="gibbonSchoolYearID" id="gibbonSchoolYearID" value="<?php echo $gibbonSchoolYearID ?>" type="hidden">
						<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
						<input type="submit" value="<?php echo __($guid, 'Submit'); ?>">
					</td>
				</tr>
			</table>
		</form>
		
		
		
		<h4>
			<?php echo __($guid, 'Notes') ?>
		</h4>
		<ol>
			<li style='color: #c00; font-weight: bold'><?php echo __($guid, 'THE SYSTEM WILL NOT PROMPT YOU TO PROCEED, IT WILL JUST DO THE IMPORT. BACKUP YOUR DATA.') ?></li>
			<li><?php echo __($guid, 'You may only submit CSV files.') ?></li>
			<li><?php echo __($guid, 'Imports cannot be run concurrently (e.g. make sure you are the only person importing at any one time).') ?></li>
			<li><?php echo __($guid, 'Your import should only include all current students.') ?></li>
			<li><?php echo __($guid, 'The submitted file must have the following fields in the following order (* denotes required field):') ?></li> 
				<ol>
					<li><b><?php echo sprintf(__($guid, '%1$s Username'), $_SESSION[$guid]['systemName']) ?> *</b></li>
					<li><b><?php echo __($guid, 'Title') ?> *</b> - <?php echo __($guid, 'Name of site.') ?></li>
					<li><b><?php echo __($guid, 'URL') ?></b></li>
					<li><b><?php echo __($guid, 'Credential Username') ?></b></li>
					<li><b><?php echo __($guid, 'Password') ?></b> - <?php echo __($guid, 'Plain text. It will be encrypted before being saved in database.') ?></li>
				</ol>
			</li>
			<li><?php echo __($guid, 'Do not include a header row in the CSV files.') ?></li>
		</ol>
	<?php

    } elseif ($step == 2) {
        ?>
		<h2>
			<?php echo __($guid, 'Step 2 - Data Check & Confirm') ?>
		</h2>
		<?php

        //Check file type
        if (($_FILES['file']['type'] != 'text/csv') and ($_FILES['file']['type'] != 'text/comma-separated-values') and ($_FILES['file']['type'] != 'text/x-comma-separated-values') and ($_FILES['file']['type'] != 'application/vnd.ms-excel')) {
            ?>
			<div class='error'>
				<?php echo sprintf(__($guid, 'Import cannot proceed, as the submitted file has a MIME-TYPE of %1$s, and as such does not appear to be a CSV file.'), $_FILES['file']['type']) ?><br/>
			</div>
			<?php

        } elseif (($_POST['fieldDelimiter'] == '') or ($_POST['stringEnclosure'] == '')) {
            ?>
			<div class='error'>
				<?php echo __($guid, 'Import cannot proceed, as the "Field Delimiter" and/or "String Enclosure" fields have been left blank.') ?><br/>
			</div>
			<?php

        } else {
            $proceed = true;

            //PREPARE TABLES
            echo '<h4>';
            echo __($guid, 'Prepare Database Tables');
            echo '</h4>';
            //Lock tables
            $lockFail = false;
            try {
                $sql = 'LOCK TABLES credentialsCredential WRITE, gibbonPerson WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $lockFail = true;
                $proceed = false;
            }
            if ($lockFail == true) {
                echo "<div class='error'>";
                echo __($guid, 'The database could not be locked for use.');
                echo '</div>';
            } elseif ($lockFail == false) {
                echo "<div class='success'>";
                echo __($guid, 'The database was successfully locked.');
                echo '</div>';
            }

            if ($lockFail == false) {
                //READ IN DATA
                if ($proceed == true) {
                    echo '<h4>';
                    echo __($guid, 'File Import');
                    echo '</h4>';
                    $importFail = false;
                    $csvFile = $_FILES['file']['tmp_name'];
                    $handle = fopen($csvFile, 'r');
                    $users = array();
                    $userCount = 0;
                    $userSuccessCount = 0;
                    while (($data = fgetcsv($handle, 100000, stripslashes($_POST['fieldDelimiter']), stripslashes($_POST['stringEnclosure']))) !== false) {
                        if ($data[0] != '' and $data[1] != '') {
                            $users[$userSuccessCount]['username'] = $data[0];
                            $users[$userSuccessCount]['title'] = $data[1];
                            $users[$userSuccessCount]['url'] = $data[2];
                            $users[$userSuccessCount]['username2'] = $data[3];
                            $users[$userSuccessCount]['password'] = $data[4];
                            ++$userSuccessCount;
                        } else {
                            echo "<div class='error'>";
                            echo sprintf(__($guid, 'Student with username %1$s had some information malformations.'), $data[0]);
                            echo '</div>';
                        }
                        ++$userCount;
                    }
                    fclose($handle);
                    if ($userSuccessCount == 0) {
                        echo "<div class='error'>";
                        echo __($guid, 'No useful credentials were detected in the import file (perhaps they did not meet minimum requirements), so the import will be aborted.');
                        echo '</div>';
                        $proceed = false;
                    } elseif ($userSuccessCount < $userCount) {
                        echo "<div class='error'>";
                        echo __($guid, 'Some credentials could not be successfully read or used, so the import will be aborted.');
                        echo '</div>';
                        $proceed = false;
                    } elseif ($userSuccessCount == $userCount) {
                        echo "<div class='success'>";
                        echo __($guid, 'All credentials could be read and used, so the import will proceed.');
                        echo '</div>';
                    } else {
                        echo "<div class='error'>";
                        echo __($guid, 'An unknown error occured, so the import will be aborted.');
                        echo '</div>';
                        $proceed = false;
                    }
                }

                //Encryption defines
                define('SAFETY_CIPHER', MCRYPT_RIJNDAEL_256);
                define('SAFETY_MODE', MCRYPT_MODE_CFB);
                define('APPLICATION_WIDE_PASSPHRASE', $guid);
                define('ENCRYPTION_DIVIDER_TOKEN', '$$');

                if ($proceed == true) {
                    echo '<h4>';
                    echo __($guid, 'Enrol All Students');
                    echo '</h4>';
                    foreach ($users as $user) {
                        $checkFail = false;
                        //Check if credential exists for user is 
                        try {
                            $data = array('username' => $user['username'], 'title' => $user['title'], 'url' => $user['url']);
                            $sql = 'SELECT * FROM credentialsCredential JOIN gibbonPerson ON (credentialsCredential.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonPerson.username=:username AND credentialsCredential.title=:title AND credentialsCredential.url=:url';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $checkFail = true;
                        }

                        if ($checkFail == true) {
                            echo "<div class='error'>";
                            echo __($guid, 'There was an error with credential:').' '.$user['username'].', '.$user['title'].', '.$user['url'];
                            echo '</div>';
                        } else {
                            $passwordFinal = null;
                            if ($user['password'] != '') {
                                //Password, key, etc.
                                $password = $user['password'];
                                $key = substr(md5(APPLICATION_WIDE_PASSPHRASE), 0, mcrypt_get_key_size(SAFETY_CIPHER, SAFETY_MODE));
                                $initVector = mcrypt_create_iv(mcrypt_get_iv_size(SAFETY_CIPHER, SAFETY_MODE), MCRYPT_RAND);

                                //Encrypt & prepare
                                $encrypted = mcrypt_encrypt(SAFETY_CIPHER, $key, $password, SAFETY_MODE, $initVector);
                                $passwordFinal = base64_encode($initVector).ENCRYPTION_DIVIDER_TOKEN.base64_encode($encrypted);
                            }

                            if ($result->rowCount() < 1) { //INSERT
                                $credentialInsertFail = false;
                                try {
                                    $data = array('username' => $user['username'], 'title' => $user['title'], 'url' => $user['url'], 'username2' => $user['username2'], 'password' => $passwordFinal, 'gibbonPersonIDCreator' => $_SESSION[$guid]['gibbonPersonID'], 'timestampCreator' => date('Y-m-d H:i:s', time()));
                                    $sql = 'INSERT INTO credentialsCredential SET gibbonPersonID=(SELECT gibbonPersonID FROM gibbonPerson WHERE username=:username), title=:title, url=:url, username=:username2, password=:password, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestampCreator=:timestampCreator';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $credentialInsertFail = true;
                                }

                                //Spit out results
                                if ($credentialInsertFail == true) {
                                    echo "<div class='error'>";
                                    echo $e->getMessage();
                                    echo __($guid, 'There was an error with credential:').' '.$user['username'].', '.$user['title'].', '.$user['url'];
                                    echo '</div>';
                                } else {
                                    echo "<div class='success'>";
                                    echo __($guid, 'The following credential was successfully inserted:').' '.$user['username'].', '.$user['title'].', '.$user['url'];
                                    echo '</div>';
                                }
                            } else { //UPDATE
                                $credentialInsertFail = false;
                                try {
                                    $data = array('username' => $user['username'], 'title' => $user['title'], 'url' => $user['url'], 'username2' => $user['username2'], 'password' => $passwordFinal);
                                    $sql = 'UPDATE credentialsCredential SET username=:username2, password=:password WHERE gibbonPersonID=(SELECT gibbonPersonID FROM gibbonPerson WHERE username=:username) AND title=:title AND url=:url';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $credentialInsertFail = true;
                                }

                                //Spit out results
                                if ($credentialInsertFail == true) {
                                    echo "<div class='error'>";
                                    echo __($guid, 'There was an error with credential:').' '.$user['username'].', '.$user['title'].', '.$user['url'];
                                    echo '</div>';
                                } else {
                                    echo "<div class='success'>";
                                    echo __($guid, 'The following credential was successfully updated:').' '.$user['username'].', '.$user['title'].', '.$user['url'];
                                    echo '</div>';
                                }
                            }
                        }
                    }
                }

                //UNLOCK TABLES
                try {
                    $sql = 'UNLOCK TABLES';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                }
            }
        }
    }
}
?>