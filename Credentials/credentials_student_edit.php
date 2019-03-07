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

if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_student_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $gibbonPersonID = isset($_GET['gibbonPersonID'])? $_GET['gibbonPersonID'] : '';
    $search = isset($_GET['search'])? $_GET['search'] : '';
    $allStudents = isset($_GET['allStudents'])? $_GET['allStudents'] : '';

    if ($gibbonPersonID == '') { echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            if ($allStudents != 'on') {
                $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'gibbonPersonID' => $gibbonPersonID);
                $sql = "SELECT * FROM gibbonPerson JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND gibbonPerson.gibbonPersonID=:gibbonPersonID";
            } else {
                $data = array('gibbonPersonID' => $gibbonPersonID);
                $sql = 'SELECT DISTINCT gibbonPerson.* FROM gibbonPerson LEFT JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) WHERE gibbonPerson.gibbonPersonID=:gibbonPersonID';
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $student = $result->fetch();

            //Proceed!
            $page->breadcrumbs->add(__('Manage Credentials'), 'credentials.php', [
                'search' => $search,
                'allStudents' => $allStudents,
            ]);
            $page->breadcrumbs->add(
                formatName('', $student['preferredName'], $student['surname'], 'Student'),
                'credentials_student.php',
                [
                    'gibbonPersonID' => $gibbonPersonID,
                    'search' => $search,
                    'allStudents' => $allStudents,
                ]
            );
            $page->breadcrumbs->add(__('Edit Credential'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            //Check if school year specified
            $credentialsCredentialID = $_GET['credentialsCredentialID'];
            if ($credentialsCredentialID == '') {
                echo "<div class='error'>";
                echo __('You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                try {
                    $data = array('credentialsCredentialID' => $credentialsCredentialID);
                    $sql = 'SELECT * FROM credentialsCredential WHERE credentialsCredentialID=:credentialsCredentialID';
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

                    if ($_GET['search'] != '') {
                        echo "<div class='linkTop'>";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Credentials/credentials_student.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents'>".__('Back').'</a>';
                        echo '</div>';
                    }

                    if ($values['password'] != '') {
                        //Defines
                        define('SAFETY_CIPHER', MCRYPT_RIJNDAEL_256);
                        define('SAFETY_MODE', MCRYPT_MODE_CFB);
                        define('APPLICATION_WIDE_PASSPHRASE', $guid);
                        define('ENCRYPTION_DIVIDER_TOKEN', '$$');

                        //Key, etc.
                        $key = substr(md5(APPLICATION_WIDE_PASSPHRASE), 0, mcrypt_get_key_size(SAFETY_CIPHER, SAFETY_MODE));

                        //Decrypt
                        $values['password'] = mcrypt_decrypt(SAFETY_CIPHER, $key, base64_decode(substr($values['password'], (strpos($values['password'], '$$') + 2))), SAFETY_MODE, base64_decode(substr($values['password'], 0, strpos($values['password'], '$$'))));
                    }

                    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/credentials_student_editProcess.php?gibbonPersonID='.$gibbonPersonID.'&search='.$search.'&allStudents='.$allStudents.'&credentialsCredentialID='.$credentialsCredentialID);

                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    $sql = "SELECT credentialsWebsiteID as value, title as name FROM credentialsWebsite WHERE active='Y' ORDER BY title";
                    $row = $form->addRow();
                        $row->addLabel('credentialsWebsiteID', __('Website'));
                        $row->addSelect('credentialsWebsiteID')->fromQuery($pdo, $sql)->required()->placeholder();

                    $row = $form->addRow();
                        $row->addLabel('username', __('Username'));
                        $row->addTextField('username')->maxLength(50);

                    $row = $form->addRow();
                        $row->addLabel('password', __('Password'))->description(__('Saved using encryption, but displayed in plain text in the system.'));
                        $row->addTextField('password')->maxLength(50);

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
    }
}
