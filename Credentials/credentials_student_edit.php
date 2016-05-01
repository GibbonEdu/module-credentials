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

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_student_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    $gibbonPersonID = $_GET['gibbonPersonID'];
    $search = null;
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }
    $allStudents = '';
    if (isset($_GET['allStudents'])) {
        $allStudents = $_GET['allStudents'];
    }
    if ($gibbonPersonID == '') {
        echo "<div class='error'>";
        echo __($guid, 'You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            if ($allStudents != 'on') {
                $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'gibbonPersonID' => $gibbonPersonID);
                $sql = "SELECT * FROM gibbonPerson JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND gibbonPerson.gibbonPersonID=:gibbonPersonID";
            } else {
                $data = array('gibbonPersonID' => $gibbonPersonID);
                $sql = 'SELECT DISTINCT gibbonPerson.* FROM gibbonPerson LEFT JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) WHERE gibbonPerson.gibbonPersonID=:gibbonPersonID';
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __($guid, 'The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $row = $result->fetch();

            //Proceed!
            echo "<div class='trail'>";
            echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".__($guid, getModuleName($_GET['q']))."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/credentials.php'>".__($guid, 'Manage Credentials')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/credentials_student.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents'>".formatName('', $row['preferredName'], $row['surname'], 'Student')."</a> > </div><div class='trailEnd'>".__($guid, 'Edit Credential').'</div>';
            echo '</div>';

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            //Check if school year specified
            $credentialsCredentialID = $_GET['credentialsCredentialID'];
            if ($credentialsCredentialID == '') {
                echo "<div class='error'>";
                echo __($guid, 'You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                try {
                    $data = array('credentialsCredentialID' => $credentialsCredentialID);
                    $sql = 'SELECT * FROM credentialsCredential WHERE credentialsCredentialID=:credentialsCredentialID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='error'>";
                    echo __($guid, 'The specified record cannot be found.');
                    echo '</div>';
                } else {
                    //Let's go!
                    $row = $result->fetch();

                    if ($_GET['search'] != '') {
                        echo "<div class='linkTop'>";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Credentials/credentials_student.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents'>".__($guid, 'Back').'</a>';
                        echo '</div>';
                    }
                    ?>
					<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/credentials_student_editProcess.php?gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents&credentialsCredentialID=$credentialsCredentialID" ?>">
						<table class='smallIntBorder' cellspacing='0' style="width: 100%">
							<tr>
								<td style='width: 275px'>
									<b><?php echo __($guid, 'Site Title') ?> *</b><br/>
									<span style="font-size: 90%"><i></i></span>
								</td>
								<td class="right">
									<input name="title" id="title" maxlength=100 value="<?php echo $row['title'] ?>" type="text" style="width: 300px">
									<script type="text/javascript">
										var title=new LiveValidation('title');
										title.add(Validate.Presence);
									 </script>
								</td>
							</tr>
							<tr>
								<td>
									<b><?php echo __($guid, 'URL') ?></b><br/>
								</td>
								<td class="right">
									<input name="url" id="url" maxlength=255 value="<?php echo $row['url'] ?>" type="text" style="width: 300px">
									<script type="text/javascript">
										var url=new LiveValidation('url');
										url.add( Validate.Format, { pattern: /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/, failureMessage: "Must start with http:// or https://" } );
									</script>
								</td>
							</tr>
							<tr>
								<td>
									<b><?php echo __($guid, 'Username') ?> *</b><br/>
								</td>
								<td class="right">
									<input name="username" id="username" maxlength=50 value="<?php echo $row['username'] ?>" type="text" style="width: 300px">
								</td>
							</tr>
							<tr>
								<td>
									<b><?php echo __($guid, 'Password') ?></b><br/>
									<span style="font-size: 90%"><i><?php echo __($guid, 'Saved using encryption, but displayed in plain text in the system.') ?></i></span>
								</td>
								<td class="right">
									<?php
                                    $passwordPrint = '';
                    if ($row['password'] != '') {
                        //Defines
                                        define('SAFETY_CIPHER', MCRYPT_RIJNDAEL_256);
                        define('SAFETY_MODE', MCRYPT_MODE_CFB);
                        define('APPLICATION_WIDE_PASSPHRASE', $guid);
                        define('ENCRYPTION_DIVIDER_TOKEN', '$$');

                                        //Key, etc.
                                        $key = substr(md5(APPLICATION_WIDE_PASSPHRASE), 0, mcrypt_get_key_size(SAFETY_CIPHER, SAFETY_MODE));

                                        //Decrypt
                                        $passwordPrint = mcrypt_decrypt(SAFETY_CIPHER, $key, base64_decode(substr($row['password'], (strpos($row['password'], '$$') + 2))), SAFETY_MODE, base64_decode(substr($row['password'], 0, strpos($row['password'], '$$'))));
                    }
                    ?>
									<input name="password" id="password" maxlength=50 value="<?php echo $passwordPrint ?>" type="text" style="width: 300px">
								</td>
							</tr>
							<tr>
								<td>
									<span style="font-size: 90%"><i>* <?php echo __($guid, 'denotes a required field');
                    ?></i></span>
								</td>
								<td class="right">
									<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
									<input type="submit" value="<?php echo __($guid, 'Submit');
                    ?>">
								</td>
							</tr>
						</table>
					</form>
					<?php

                }
            }
        }
    }
}
?>
