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
use Gibbon\Module\Credentials\Domain\CredentialGateway;

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_view.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __m('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__m('View Credentials'));

    echo '<h2>';
    echo __m('Search');
    echo '</h2>';

    $gibbonPersonID = $_GET['gibbonPersonID'] ?? '';
    $search = $_GET['search'] ?? '';
    $allStudents = $_GET['allStudents'] ?? '';

    $form = Form::create('search', $session->get('absoluteURL').'/index.php', 'get');
    $form->setClass('noIntBorder w-full');

    $form->addHiddenValue('q', '/modules/'.$session->get('module').'/credentials_view.php');

    $row = $form->addRow();
    $row->addLabel('search', __m('Search For'))->description(__m('Preferred, surname, username.'));
    $row->addTextField('search')->setValue($search);

    $row = $form->addRow();
    $row->addLabel('allStudents', __m('All Students'))->description(__m('Include all students, regardless of status and current enrolment. Some data may not display.'));
    $row->addCheckbox('allStudents')->setValue('on')->checked($allStudents);

    $row = $form->addRow();
    $row->addSearchSubmit($session, __m('Clear Search'));

    echo $form->getOutput();

    echo '<h2>';
    echo __m('Choose A Student');
    echo '</h2>';

    $gibbonSchoolYearID = $session->get('gibbonSchoolYearID');

    $credentialGateway = $container->get(CredentialGateway::class);
    $searchColumns = $credentialGateway->getSearchableColumns();

    $criteria = $credentialGateway->newQueryCriteria()
            ->searchBy($searchColumns, $search)
            ->sortBy(['surname', 'preferredName'])
            ->filterBy('all', $allStudents)
            ->fromPOST();
    $students = $credentialGateway->queryCredentialsStudentBySchoolYear($criteria, $gibbonSchoolYearID);


    // DATA TABLE
    $table = DataTable::createPaginated('students', $criteria);

    $table->modifyRows($credentialGateway->getSharedUserRowHighlighter());

    $table->addMetaData('filterOptions', [
        'all:on' => __m('All Students')
    ]);

    if ($criteria->hasFilter('all')) {
        $table->addMetaData('filterOptions', [
            'status:full' => __m('Status').': '.__m('Full'),
            'status:expected' => __m('Status').': '.__m('Expected'),
            'date:starting' => __m('Before Start Date'),
            'date:ended' => __m('After End Date'),
        ]);
    }

    // COLUMNS
    $table->addColumn('student', __m('Student'))
            ->sortable(['surname', 'preferredName'])
            ->format(function ($person) {
                return Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true).'<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
            });
    $table->addColumn('yearGroup', __m('Year Group'));
    $table->addColumn('formGroup', __m('Form Group'));
    $table->addColumn('credentialCount', __m('Credential Count'));

    $table->addActionColumn()
            ->addParam('gibbonPersonID')
            ->addParam('search', $search)
            ->addParam('allStudents', $allStudents)
            ->format(function ($row, $actions) {
                $actions->addAction('view', __m('View Details'))
                ->setURL('/modules/Credentials/credentials_view_student.php');
            });

    echo $table->render($students);
}
?>
