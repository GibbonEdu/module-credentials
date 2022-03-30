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
use Gibbon\Module\Credentials\Domain\WebsiteGateway;

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __m('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__m('Manage Websites'), 'websites.php');
    $page->breadcrumbs->add(__m('Edit Website'));

    $returns = ['error3'   => __m('Website title should be unique')];
    $page->return->addReturns($returns);

    //Check if credentialsWebsiteID specified
    $credentialsWebsiteID = $_GET['credentialsWebsiteID'] ?? '';

    if ($credentialsWebsiteID == '') {
        echo "<div class='error'>";
        echo __m('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $websiteGateway = $container->get(WebsiteGateway::class);
        $values = $websiteGateway->getById($credentialsWebsiteID);

        $form = Form::create('action', $session->get('absoluteURL').'/modules/'.$session->get('module').'/websites_editProcess.php?credentialsWebsiteID='.$credentialsWebsiteID);

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
        $row->addFileUpload('file1')
                ->accepts('.jpg,.jpeg,.gif,.png')
                ->setMaxUpload(false)
                ->setAttachment('logo', $session->get('absoluteURL'), $values['logo']);

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
