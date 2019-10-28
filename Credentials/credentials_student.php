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

use Gibbon\Tables\DataTable;
use Gibbon\Module\Credentials\CredentialsCredentialGateway;
use Gibbon\Services\Format;

//Module includes
include './modules/Credentials/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_student.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

    $gibbonPersonID = $_GET['gibbonPersonID'] ?? '';
    $search = $_GET['search'] ?? '';
    $allStudents = $_GET['allStudents'] ?? '';

    if ($gibbonPersonID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $gibbonSchoolYearID = $_SESSION[$guid]['gibbonSchoolYearID'];

            $studentGateway = $container->get(CredentialsCredentialGateway::class);
            $searchColumns = $studentGateway->getSearchableColumns();

            $criteria = $studentGateway->newQueryCriteria()
                            ->searchBy($searchColumns, $search)
                            ->sortBy(['surname', 'preferredName'])
                            ->filterBy('all', $allStudents ?? '')
                            ->fromPOST();
            $students = $studentGateway->queryStudentBySchoolYear($criteria, $gibbonSchoolYearID, $gibbonPersonID);
        } catch (Exception $e) {
            echo "<div class='error'>" . $e->getMessage() . '</div>';
        }

        if ($students->getResultCount() != 1) {
            echo "<div class='error'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $student = $students->getRow(0);

            $page->breadcrumbs->add(__('Manage Credentials'), 'credentials.php', [
                'search' => $search,
                'allStudents' => $allStudents,
            ]);
            $page->breadcrumbs->add(Format::name('', $student['preferredName'], $student['surname'], 'Student'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            if ($search != '' or $allStudents != '') {
                echo "<div class='linkTop'>";
                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Credentials/credentials.php&search=$search&allStudents=$allStudents'>" . __('Back to Search Results') . '</a>';
                echo '</div>';
            }

            echo "<div class='linkTop'>";
            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . "/credentials_student_add.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents'>" . __('Add') . "<img style='margin-left: 5px' title='" . __('Add') . "' src='./themes/" . $_SESSION[$guid]['gibbonThemeName'] . "/img/page_new.png'/></a>";
            echo '</div>';

            $studentGateway = $container->get(CredentialsCredentialGateway::class);
            $criteria = $studentGateway->newQueryCriteria();

            $students = $studentGateway->queryViewCredentialsByPerson($criteria, $gibbonPersonID);

            if (!$students) {
                echo "<div class='error'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                // DATA TABLE
                $table = DataTable::createPaginated('credentials_students', $criteria);

                // COLUMNS
                $table->addColumn('title', __('Title'))
                        ->format(function ($credential) {
                            return Format::link($credential['url'], $credential['title']);
                        });
                $table->addColumn('username', __('Username'));
                $table->addColumn('password', __('Password'))
                        ->format(function ($credentials)use ($guid) {
                            return getDecryptCredentialOpenssl($credentials['password']);
                        });

                $table->addActionColumn()
                        ->addParam('credentialsCredentialID')
                        ->addParam('search', $criteria->getSearchText(true))
                        ->addParam('gibbonPersonID', $gibbonPersonID)
                        ->format(function ($person, $actions) use ($guid) {
                            $actions->addAction('edit', __('Edit'))
                            ->setURL('/modules/Credentials/credentials_student_edit.php');

                            $actions->addAction('delete', __('Delete'))
                            ->setURL('/modules/Credentials/credentials_student_delete.php');
                        });
                echo $table->render($students);
            }
        }
    }
}
