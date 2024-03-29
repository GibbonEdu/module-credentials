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


use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\Credentials\Domain\CredentialGateway;

//Module includes
include './modules/Credentials/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_view_student.php') == false) {
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

        $credentialGateway = $container->get(CredentialGateway::class);
        $searchColumns = $credentialGateway->getSearchableColumns();

        $criteria = $credentialGateway->newQueryCriteria()
                ->searchBy($searchColumns, $search)
                ->sortBy(['surname', 'preferredName'])
                ->filterBy('all', $allStudents)
                ->fromPOST();

        $students = $credentialGateway->queryStudentBySchoolYear($criteria, $gibbonSchoolYearID, $gibbonPersonID);

        if ($students->getResultCount() != 1) {
            echo "<div class='error'>";
            echo __m('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $student = $students->getRow(0);

            $page->breadcrumbs->add(__m('View Credentials'), 'credentials_view.php', [
                'search' => $search,
                'allStudents' => $allStudents,
            ]);
            $page->breadcrumbs->add(Format::name('', $student['preferredName'], $student['surname'], 'Student'));

            $criteria = $credentialGateway->newQueryCriteria();
            $credentials = $credentialGateway->queryViewCredentialsByPerson($criteria, $gibbonPersonID);

            // DATA TABLE
            $table = DataTable::createPaginated('credentials_students', $criteria);
            // COLUMNS
            $table->addExpandableColumn('notes')
                    ->format(function($credential) {
                        $output = '';
                        if (!empty($credential['websiteNotes'])) {
                            $output .= '<strong>'.__m('Website Notes').'</strong>:';
                            $output .= '<br />'.$credential['websiteNotes'].'<br /><br />';
                        }
                        if (!empty($credential['credentialNotes'])) {
                            $output .= '<strong>'.__m('Credential Notes').'</strong>:';
                            $output .= '<br />'.$credential['credentialNotes'];
                        }
                        return $output;
                    });
            $table->addColumn('logo', __m('Logo'))
                    ->format(function($credential) use ($guid, $session) {
                        if ($credential['logo'] != '') {
                            echo "<img class='user' style='max-width: 150px' src='".$session->get('absoluteURL').'/'.$credential['logo']."'/>";
                        } else {
                            echo "<img class='user' style='max-width: 150px' src='".$session->get('absoluteURL').'/themes/'.$session->get('gibbonThemeName')."/img/anonymous_240_square.jpg'/>";
                        }
                    });
            $table->addColumn('title', __m('Title'))
                    ->format(function ($credential) {
                        return Format::link($credential['url'], $credential['title']);
                    });
            $table->addColumn('username', __m('Username'));
            $table->addColumn('password', __m('Password'))
                    ->format(function ($credential)use ($guid) {
                        return getDecryptCredentialOpenssl($credential['password']);
                    });

            echo $table->render($credentials);
        }
    }
}
