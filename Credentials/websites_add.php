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

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Websites'), 'websites.php');
    $page->breadcrumbs->add(__('Add Website'));

    $returns = array();
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Credentials/websites_edit.php&credentialsWebsiteID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, $returns);
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/websites_addProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('title', __('Website Title'))->description(__('Must be unique.'));
        $row->addTextField('title')->isRequired()->maxLength(100);

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->isRequired();

    $row = $form->addRow();
        $row->addLabel('url', __('URL'));
        $row->addURL('url')->maxLength(255);

    $row = $form->addRow();
        $row->addLabel('file1', __('Logo'));
        $row->addFileUpload('file1')->accepts('.jpg,.jpeg,.gif,.png')->setMaxUpload(false);

    $row = $form->addRow();
        $row->addLabel('notes', __('Notes'));
        $row->addTextArea('notes')->setRows(5);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
