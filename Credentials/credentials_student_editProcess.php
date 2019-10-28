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
use Gibbon\Module\Credentials\CredentialsCredentialGateway;
include '../../gibbon.php';

//Module includes
include './moduleFunctions.php';

$gibbonPersonID = $_GET['gibbonPersonID'] ?? '';
$search = $_GET['search'] ?? '';
$allStudents = $_GET['allStudents'] ?? '';

$credentialsCredentialID = $_GET['credentialsCredentialID'] ?? '';
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/credentials_student_edit.php&gibbonPersonID=$gibbonPersonID&search=".$_GET['search']."&credentialsCredentialID=$credentialsCredentialID";

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_student_edit.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if note specified
    if ($credentialsCredentialID == '' or $gibbonPersonID == '') {
        echo __('Fatal error loading this page!');
    } else {
        try {
            $credentialsCredentialGateway = $container->get(CredentialsCredentialGateway::class);
            $credential = $credentialsCredentialGateway->selectCredentialsCredentialById($credentialsCredentialID)->fetchAll();
            if (!$credential and !empty($credential)) {
                 throw new Exception();
            }
        } catch (Exception $e) {
            //Fail2
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if (count($credential) != 1) {
            //Fail 2
            //$URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            $credentialsWebsiteID = $_POST['credentialsWebsiteID'] ?? '';
            $username = $_POST['username'];
            $notes = $_POST['notes'];

            //Encrypt password
            $passwordFinal = null;
            if ($_POST['password'] != '') {
                //Encrypt
                $passwordFinal = getEncryptCredentialOpenssl($_POST['password']);
            }

            if ($credentialsWebsiteID == '') {
                //Fail 3
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('gibbonPersonID' => $gibbonPersonID, 'credentialsWebsiteID' => $credentialsWebsiteID, 'username' => $username, 'password' => $passwordFinal, 'notes' => $notes, 'gibbonPersonID' => $gibbonPersonID, 'credentialsCredentialID' => $credentialsCredentialID);
                    if (!$credentialsCredentialGateway->updateCredentialsCredential($data)) {
                        //throw new Exception();
                    }
                } catch (Exception $e) {
                    //Fail 2
                    //$URL .= '&return=error2';
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
