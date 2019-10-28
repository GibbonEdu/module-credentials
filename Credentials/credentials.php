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
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Module\Credentials\CredentialsCredentialGateway;

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Manage Credentials'));

    echo "<div class='warning'>";
    echo __('<b><u>WARNING</u></b>: This module uses two-way encryption to store and retreive passwords. This is secure, but far from infallible. Please use this module only for storing student credentials for sites which do not include sensitive personal data.');
    echo '</div>';

    echo '<h2>';
    echo __('Search');
    echo '</h2>';

    $gibbonPersonID = $_GET['gibbonPersonID'] ?? '';
    $search = $_GET['search'] ?? '';
    $allStudents = $_GET['allStudents'] ?? '';

    $form = Form::create('search', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/credentials.php');

    $row = $form->addRow();
    $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
    $row->addTextField('search')->setValue($search);

    $row = $form->addRow();
    $row->addLabel('allStudents', __('All Students'))->description(__('Include all students, regardless of status and current enrolment. Some data may not display.'));
    $row->addCheckbox('allStudents')->setValue('on')->checked($allStudents);

    $row = $form->addRow();
    $row->addSearchSubmit($gibbon->session, __('Clear Search'));

    echo $form->getOutput();

    echo '<h2>';
    echo __('Choose A Student');
    echo '</h2>';

    $gibbonSchoolYearID = $_SESSION[$guid]['gibbonSchoolYearID'];

    $studentGateway = $container->get(CredentialsCredentialGateway::class);
    $searchColumns = $studentGateway->getSearchableColumns();

    $criteria = $studentGateway->newQueryCriteria()
            ->searchBy($searchColumns, $search)
            ->sortBy(['surname', 'preferredName'])
            ->filterBy('all', $allStudents ?? '')
            ->fromPOST();
    $students = $studentGateway->queryStudentBySchoolYear($criteria, $gibbonSchoolYearID);

    if (!$students) {
        echo "<div class='error'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        // DATA TABLE
        $table = DataTable::createPaginated('students', $criteria);

        $table->modifyRows($studentGateway->getSharedUserRowHighlighter());

        $table->addMetaData('filterOptions', [
            'all:on' => __('All Students')
        ]);

        if ($criteria->hasFilter('all')) {
            $table->addMetaData('filterOptions', [
                'status:full' => __('Status') . ': ' . __('Full'),
                'status:expected' => __('Status') . ': ' . __('Expected'),
                'date:starting' => __('Before Start Date'),
                'date:ended' => __('After End Date'),
            ]);
        }   

        // COLUMNS
        $table->addColumn('student', __('Student'))
                ->sortable(['surname', 'preferredName'])
                ->format(function ($person) {
                    return Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true) . '<br/><small><i>' . Format::userStatusInfo($person) . '</i></small>';
                });
        $table->addColumn('yearGroup', __('Year Group'));
        $table->addColumn('rollGroup', __('Roll Group'));
        $table->addColumn('credentialCount', __('Credential Count'));

        $table->addActionColumn()
                ->addParam('gibbonPersonID')
                ->addParam('search',$search)
                ->addParam('allStudents', $allStudents ?? '')
                ->format(function ($row, $actions) {
                    $actions->addAction('view', __('View Details'))
                    ->setURL('/modules/Credentials/credentials_student.php');
                });

        echo $table->render($students);
    }
}
?>