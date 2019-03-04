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

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__('Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".__(getModuleName($_GET['q']))."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/websites.php'>".__('Manage Websites')."</a> > </div><div class='trailEnd'>".__('Edit Website').'</div>';
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $credentialsWebsiteID = $_GET['credentialsWebsiteID'];
    if ($credentialsWebsiteID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('credentialsWebsiteID' => $credentialsWebsiteID);
            $sql = 'SELECT * FROM credentialsWebsite WHERE credentialsWebsiteID=:credentialsWebsiteID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();
            
            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/websites_editProcess.php?credentialsWebsiteID='.$credentialsWebsiteID);
                
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
