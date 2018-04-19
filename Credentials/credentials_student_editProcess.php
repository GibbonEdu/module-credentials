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

include '../../gibbon.php';


$gibbonPersonID = $_GET['gibbonPersonID'];
$search = null;
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$allStudents = '';
if (isset($_GET['allStudents'])) {
    $allStudents = $_GET['allStudents'];
}
$credentialsCredentialID = $_GET['credentialsCredentialID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/credentials_student_edit.php&gibbonPersonID=$gibbonPersonID&search=".$_GET['search']."&subpage=Notes&credentialsCredentialID=$credentialsCredentialID";

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_student_edit.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if note specified
    if ($credentialsCredentialID == '' or $gibbonPersonID == '') {
        echo 'Fatal error loading this page!';
    } else {
        try {
            $data = array('credentialsCredentialID' => $credentialsCredentialID);
            $sql = 'SELECT * FROM credentialsCredential WHERE credentialsCredentialID=:credentialsCredentialID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            //Fail2
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            //Fail 2
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            $credentialsWebsiteID = $_POST['credentialsWebsiteID'];
            $username = $_POST['username'];
            $notes = $_POST['notes'];

            //Encrypt password
            $passwordFinal = null;
            if ($_POST['password'] != '') {
                //Encryption defines
                define('SAFETY_CIPHER', MCRYPT_RIJNDAEL_256);
                define('SAFETY_MODE', MCRYPT_MODE_CFB);
                define('APPLICATION_WIDE_PASSPHRASE', $guid);
                define('ENCRYPTION_DIVIDER_TOKEN', '$$');

                //Password, key, etc.
                $password = $_POST['password'];
                $key = substr(md5(APPLICATION_WIDE_PASSPHRASE), 0, mcrypt_get_key_size(SAFETY_CIPHER, SAFETY_MODE));
                $initVector = mcrypt_create_iv(mcrypt_get_iv_size(SAFETY_CIPHER, SAFETY_MODE), MCRYPT_RAND);

                //Encrypt & prepare
                $encrypted = mcrypt_encrypt(SAFETY_CIPHER, $key, $password, SAFETY_MODE, $initVector);
                $passwordFinal = base64_encode($initVector).ENCRYPTION_DIVIDER_TOKEN.base64_encode($encrypted);
            }

            if ($credentialsWebsiteID == '') {
                //Fail 3
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('gibbonPersonID' => $gibbonPersonID, 'credentialsWebsiteID' => $credentialsWebsiteID, 'username' => $username, 'password' => $passwordFinal, 'notes' => $notes, 'gibbonPersonID' => $gibbonPersonID, 'credentialsCredentialID' => $credentialsCredentialID);
                    $sql = 'UPDATE credentialsCredential SET credentialsWebsiteID=:credentialsWebsiteID, username=:username, password=:password, notes=:notes, gibbonPersonID=:gibbonPersonID WHERE credentialsCredentialID=:credentialsCredentialID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                    exit();
                    //Fail 2
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Success 0
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
