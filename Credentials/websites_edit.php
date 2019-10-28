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
use Gibbon\Module\Credentials\CredentialsWebsiteGateway;

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Websites'), 'websites.php');
    $page->breadcrumbs->add(__('Edit Website'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $credentialsWebsiteID = $_GET['credentialsWebsiteID'] ?? '';
    if ($credentialsWebsiteID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $websiteGateway = $container->get(CredentialsWebsiteGateway::class);
            $website = $websiteGateway->querySelectCredentialsWebsiteById($credentialsWebsiteID)->fetchAll();
            if (!$website) {
                 throw new Exception();
            }
        } catch (Exception $e) {
            echo "<div class='error'>" . $e->getMessage() . '</div>';
        }

        if (count($website) != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $website[0];

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/websites_editProcess.php?credentialsWebsiteID=' . $credentialsWebsiteID);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
            $row->addLabel('title', __('Website Title'))->description(__('Must be unique.'));
            $row->addTextField('title')->required()->maxLength(100);

            $row = $form->addRow();
            $row->addLabel('active', __('Active'));
            $row->addYesNo('active')->required();

            $row = $form->addRow();
            $row->addLabel('url', __('URL'));
            $row->addURL('url')->required()->maxLength(255);

            $row = $form->addRow();
            $row->addLabel('file1', __('Logo'));
            $row->addFileUpload('file1')
                    ->accepts('.jpg,.jpeg,.gif,.png')
                    ->setMaxUpload(false)
                    ->setAttachment('logo', $_SESSION[$guid]['absoluteURL'], $values['logo']);

            $row = $form->addRow();
            $row->addLabel('notes', __('Notes'));
            $row->addTextArea('notes')->setRows(5);

            $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
