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

use Gibbon\Forms\Prefab\DeleteForm;
use Gibbon\Module\Credentials\Domain\CredentialsCredentialGateway;

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_student_delete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __m('You do not have access to this action.');
    echo '</div>';
} else {
    $gibbonPersonID = $_GET['gibbonPersonID'] ?? '';
    $search = $_GET['search'] ?? '';
    $allStudents = $_GET['allStudents'] ?? '';

    if ($gibbonPersonID == '') {
        echo "<div class='error'>";
        echo __m('You have not specified one or more required parameters.');
        echo '</div>';
    } else {

        $gibbonSchoolYearID = $session->get('gibbonSchoolYearID');

        $credentialsCredentialGateway = $container->get(CredentialsCredentialGateway::class);
        $searchColumns = $credentialsCredentialGateway->getSearchableColumns();

        $criteria = $credentialsCredentialGateway->newQueryCriteria()
                ->searchBy($searchColumns, $search)
                ->sortBy(['surname', 'preferredName'])
                ->filterBy('all', $allStudents)
                ->fromPOST();
        $students = $credentialsCredentialGateway->queryStudentBySchoolYear($criteria, $gibbonSchoolYearID, $gibbonPersonID);

        if ($students->getResultCount() == 1) {
            //Check if credentialsCredentialID was specified
            $credentialsCredentialID = $_GET['credentialsCredentialID'] ?? '';
            if ($credentialsCredentialID == '') {
                echo "<div class='error'>";
                echo __m('You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                $credentialsCredentialGateway = $container->get(CredentialsCredentialGateway::class);
                $credential = $credentialsCredentialGateway->getById($credentialsCredentialID);

                //Let's go!
                $form = DeleteForm::createForm($session->get('absoluteURL').'/modules/'.$session->get('module')."/credentials_student_deleteProcess.php?gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents&credentialsCredentialID=$credentialsCredentialID");
                echo $form->getOutput();
            }
        }
    }
}
