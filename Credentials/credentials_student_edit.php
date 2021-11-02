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

use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Module\Credentials\CredentialsCredentialGateway;

//Module includes
include './modules/Credentials/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_student_edit.php') == false) {
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

        $studentGateway = $container->get(CredentialsCredentialGateway::class);
        $searchColumns = $studentGateway->getSearchableColumns();

        $criteria = $studentGateway->newQueryCriteria()
                ->searchBy($searchColumns, $search)
                ->sortBy(['surname', 'preferredName'])
                ->filterBy('all', $allStudents)
                ->fromPOST();
        $students = $studentGateway->queryStudentBySchoolYear($criteria, $gibbonSchoolYearID, $gibbonPersonID);

        if ($students->getResultCount() != 1) {
            echo "<div class='error'>";
            echo __m('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $student = $students->getRow(0);

            //Proceed!
            $page->breadcrumbs->add(__m('Manage Credentials'), 'credentials.php', [
                'search' => $search,
                'allStudents' => $allStudents,
            ]);
            $page->breadcrumbs->add(
                    Format::name('', $student['preferredName'], $student['surname'], 'Student'), 'credentials_student.php', [
                'gibbonPersonID' => $gibbonPersonID,
                'search' => $search,
                'allStudents' => $allStudents,
                    ]
            );
            $page->breadcrumbs->add(__m('Edit Credential'));

            //Check if credentialsCredentialID specified
            $credentialsCredentialID = $_GET['credentialsCredentialID'] ?? '';

            if ($credentialsCredentialID == '') {
                echo "<div class='error'>";
                echo __m('You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                //Let's go!
                $values = $studentGateway->getById($credentialsCredentialID);

                if ($search != '') {
                    echo "<div class='linkTop'>";
                    echo "<a href='".$session->get('absoluteURL')."/index.php?q=/modules/Credentials/credentials_student.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents'>".__m('Back').'</a>';
                    echo '</div>';
                }

                if ($values['password'] != '') {
                    //Decrypt
                    $values['password'] = getDecryptCredentialOpenssl($values['password']);
                }

                $form = Form::create('action', $session->get('absoluteURL').'/modules/'.$session->get('module').'/credentials_student_editProcess.php?gibbonPersonID='.$gibbonPersonID.'&search='.$search.'&allStudents='.$allStudents.'&credentialsCredentialID='.$credentialsCredentialID);

                $form->addHiddenValue('address', $session->get('address'));

                $sql = "SELECT credentialsWebsiteID as value, title as name FROM credentialsWebsite WHERE active='Y' ORDER BY title";
                $row = $form->addRow();
                $row->addLabel('credentialsWebsiteID', __m('Website'));
                $row->addSelect('credentialsWebsiteID')->fromQuery($pdo, $sql)->required()->placeholder();

                $row = $form->addRow();
                $row->addLabel('username', __m('Username'));
                $row->addTextField('username')->maxLength(50);

                $row = $form->addRow();
                $row->addLabel('password', __m('Password'))->description(__m('Saved using encryption, but displayed in plain text in the system.'));
                $row->addTextField('password')->maxLength(50);

                $row = $form->addRow();
                $row->addLabel('notes', __m('Notes'));
                $row->addTextArea('notes')->setRows(5);

                $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

                $form->loadAllValuesFrom($values);

                echo $form->getOutput();
            }
        }
    }
}
