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

$gibbonPersonID = $_GET['gibbonPersonID'] ?? '';
$search = $_GET['search'] ?? '';
$allStudents = $_GET['allStudents'] ?? '';

$credentialsCredentialID = $_GET['credentialsCredentialID'] ?? '';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address'])."/credentials_student_delete.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents&credentialsCredentialID=".$credentialsCredentialID;
$URLDelete = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address'])."/credentials_student.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents";

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_student_delete.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if (($credentialsCredentialID == '') or ( $gibbonPersonID == '')) {
        echo __m('Fatal error loading this page!');
    } else {
        $credentialsCredentialGateway = $container->get(CredentialsCredentialGateway::class);
        $credential = $credentialsCredentialGateway->getById($credentialsCredentialID);

        if (!$credential) {
            //Fail 2
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            $credentialsCredentialGateway->delete($credentialsCredentialID);

            //Success 0
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
