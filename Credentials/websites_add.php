<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

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

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __m('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__m('Manage Websites'), 'websites.php');
    $page->breadcrumbs->add(__m('Add Website'));

    $editLink = $_GET['editID'] ?? '';
    if ($editLink != '') {
        $editLink = $session->get('absoluteURL').'/index.php?q=/modules/Credentials/websites_edit.php&credentialsWebsiteID='.$_GET['editID'];
    }
    $page->return->setEditLink($editLink);

    $form = Form::create('action', $session->get('absoluteURL').'/modules/'.$session->get('module').'/websites_addProcess.php');

    $form->addHiddenValue('address', $session->get('address'));

    $row = $form->addRow();
        $row->addLabel('title', __m('Website Title'))->description(__m('Must be unique.'));
        $row->addTextField('title')->required()->maxLength(100);

    $row = $form->addRow();
        $row->addLabel('active', __m('Active'));
        $row->addYesNo('active')->required();

    $row = $form->addRow();
        $row->addLabel('url', __m('URL'));
        $row->addURL('url')->required()->maxLength(255);

    $row = $form->addRow();
        $row->addLabel('file1', __m('Logo'));
        $row->addFileUpload('file1')->accepts('.jpg,.jpeg,.gif,.png')->setMaxUpload(false);

    $row = $form->addRow();
        $row->addLabel('notes', __m('Notes'));
        $row->addTextArea('notes')->setRows(5);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
