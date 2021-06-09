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
use Gibbon\Domain\User\UserGateway;
use Gibbon\Module\Credentials\CredentialsCredentialGateway;
use Gibbon\Module\Credentials\CredentialsWebsiteGateway;

//Module includes
include './modules/Credentials/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Credentials/import_credentials.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __m('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__m('Import Credentials'));

    $step = $_GET['step'] ?? 1;

    if (($step != 1) and ( $step != 2)) {
        $step = 1;
    }

    //STEP 1, SELECT TERM
    if ($step == 1) {

        echo '<h2>';
        echo __m('Step 1 - Select CSV Files');
        echo '</h2>';
        echo '<p>';
        echo __m('This page allows you to import student credentials from a CSV file. The import will add credentials for sites a user user does not already have (based on name and URL), and update them otherwise. No credentials will be removed. Select the CSV file you wish to use for the import operation.');
        echo '</p>';

        $form = Form::create('importUserPhotos', $session->get('absoluteURL').'/index.php?q=/modules/'.$session->get('module').'/import_credentials.php&step=2');

        $form->addHiddenValue('address', $session->get('address'));

        $row = $form->addRow();
        $row->addLabel('file', __m('CSV File'))->description(__m('See Notes below for specification.'));
        $row->addFileUpload('file')->required();

        $row = $form->addRow();
        $row->addLabel('fieldDelimiter', __m('Field Delimiter'));
        $row->addTextField('fieldDelimiter')->required()->maxLength(1)->setValue(',');

        $row = $form->addRow();
        $row->addLabel('stringEnclosure', __m('String Enclosure'));
        $row->addTextField('stringEnclosure')->required()->maxLength(1)->setValue('"');

        $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();
        ?>

        <h4>
            <?php echo __m('Notes'); ?>
        </h4>
        <ol>
            <li style='color: #c00;'><b><?php echo __m('THE SYSTEM WILL NOT PROMPT YOU TO PROCEED, IT WILL JUST DO THE IMPORT. BACKUP YOUR DATA.'); ?></b></li>
            <li><?php echo __m('You may only submit CSV files.'); ?></li>
            <li><?php echo __m('Imports cannot be run concurrently (e.g. make sure you are the only person importing at any one time).'); ?></li>
            <li><?php echo __m('Your import should only include all current students.'); ?></li>
            <li><?php echo __m('The submitted file must have the following fields in the following order (* denotes required field):'); ?></li>
            <ol>
                <li><b><?php echo sprintf(__m('%1$s Username'), $session->get('systemName')); ?> *</b></li>
                <li><b><?php echo __m('Website'); ?> *</b> - <?php echo __m('Title/name of website. Must exist in Manage Websites section.'); ?></li>
                <li><b><?php echo __m('Credential Username'); ?></b></li>
                <li><b><?php echo __m('Password'); ?></b> - <?php echo __m('Plain text. It will be encrypted before being saved in database.') ?></li>
                <li><b><?php echo __m('Notes'); ?></b></li>
            </ol>
        </li>
        <li><?php echo __m('Do not include a header row in the CSV files.'); ?></li>
        </ol>
        <?php
    } elseif ($step == 2) {
        echo '<h2>';
        echo __m('Step 2 - Data Check & Confirm');
        echo '</h2>';

        $fileTypes = array("text/plain", "text/csv", "text/comma-separated-values", "text/x-comma-separated-values", "application/vnd.ms-excel");
        //Check file type
        if (!in_array($_FILES['file']['type'], $fileTypes)) {
            echo '<div class="error">';
            echo sprintf(__m('Import cannot proceed, as the submitted file has a MIME-TYPE of %1$s, and as such does not appear to be a CSV file.<br />'), $_FILES['file']['type']);
            echo '</div>';
        } elseif ((trim($_POST['fieldDelimiter']) == '') or ( trim($_POST['stringEnclosure']) == '')) {
            echo '<div class="error">';
            echo __m('Import cannot proceed, as the "Field Delimiter" and/or "String Enclosure" fields have been left blank.<br/>');
            echo '</div>';
        } else {
            $proceed = true;
            //PREPARE TABLES
            echo '<h4>';
            echo __m('Prepare Database Tables');
            echo '</h4>';
            //Lock tables
            $lockFail = false;
            try {
                $sql = 'LOCK TABLES credentialsCredential WRITE, credentialsWebsite WRITE, gibbonPerson WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $lockFail = true;
                $proceed = false;
            }
            if ($lockFail == true) {
                echo "<div class='error'>";
                echo __m('The database could not be locked for use.');
                echo '</div>';
            } elseif ($lockFail == false) {
                echo "<div class='success'>";
                echo __m('The database was successfully locked.');
                echo '</div>';
            }

            if ($lockFail == false) {
                //READ IN DATA
                if ($proceed == true) {
                    echo '<h4>';
                    echo __m('File Import');
                    echo '</h4>';
                    $importFail = false;
                    $csvFile = $_FILES['file']['tmp_name'];
                    $handle = fopen($csvFile, 'r');
                    $users = array();
                    $userCount = 0;
                    $userSuccessCount = 0;
                    while (($data = fgetcsv($handle, 100000, stripslashes($_POST['fieldDelimiter']), stripslashes($_POST['stringEnclosure']))) !== false) {
                        if (trim($data[0]) != '' && trim($data[1] != '')) {
                            $users[$userSuccessCount]['username'] = $data[0];
                            $users[$userSuccessCount]['title'] = $data[1];
                            $users[$userSuccessCount]['username2'] = $data[2] ?? '';
                            $users[$userSuccessCount]['password'] = $data[3] ?? '';
                            $users[$userSuccessCount]['notes'] = $data[4] ?? '';
                            ++$userSuccessCount;
                        } else {
                            echo "<div class='error'>";
                            echo sprintf(__m('Student with username %1$s had some information malformations.'), $data[0]);
                            echo '</div>';
                        }
                        ++$userCount;
                    }
                    fclose($handle);
                    if ($userSuccessCount == 0) {
                        echo "<div class='error'>";
                        echo __m('No useful credentials were detected in the import file (perhaps they did not meet minimum requirements), so the import will be aborted.');
                        echo '</div>';
                        $proceed = false;
                    } elseif ($userSuccessCount < $userCount) {
                        echo "<div class='error'>";
                        echo __m('Some credentials could not be successfully read or used, so the import will be aborted.');
                        echo '</div>';
                        $proceed = false;
                    } elseif ($userSuccessCount == $userCount) {
                        echo "<div class='success'>";
                        echo __m('All credentials could be read and used, so the import will proceed.');
                        echo '</div>';
                    } else {
                        echo "<div class='error'>";
                        echo __m('An unknown error occured, so the import will be aborted.');
                        echo '</div>';
                        $proceed = false;
                    }
                }

                if ($proceed == true) {
                    echo '<h4>';
                    echo __m('Import All Credentials');
                    echo '</h4>';
                    foreach ($users as $user) {

                        //Check if credential exists for user
                        $credentialsCredentialGateway = $container->get(CredentialsCredentialGateway::class);
                        $criteria = $credentialsCredentialGateway->newQueryCriteria();
                        $importCredential = $credentialsCredentialGateway->queryViewCredentialsByPerson($criteria, $user['username'], $user['title']);

                        $passwordFinal = null;
                        if ($user['password'] != '') {
                            //Encrypt & prepare
                            $passwordFinal = getEncryptCredentialOpenssl($user['password']);
                        }

                        if ($importCredential->getResultCount() < 1) {

                            //Check if website exists
                            $credentialsWebsiteGateway = $container->get(CredentialsWebsiteGateway::class);
                            $dataWebsite = array('title' => $user['title']);
                            $credentialsWebsite = $credentialsWebsiteGateway->selectBy($dataWebsite)->fetch();

                            //Check if user/person exists
                            $userGateway = $container->get(UserGateway::class);
                            $dataUser = array('username' => $user['username']);
                            $person = $userGateway->selectBy($dataUser)->fetch();

                            if (!empty($credentialsWebsite)&&(!empty($person))) {
                                //Insert credential
                                $data = array('gibbonPersonID' => $person['gibbonPersonID'], 'credentialsWebsiteID' => $credentialsWebsite['credentialsWebsiteID'], 'username' => $user['username2'], 'password' => $passwordFinal, 'notes' => $user['notes'], 'gibbonPersonIDCreator' => $session->get('gibbonPersonID'), 'timestampCreator' => date('Y-m-d H:i:s', time()));
                                if ($credentialsCredentialGateway->insert($data)) {
                                    echo "<div class='success'>";
                                    echo __m('The following credential was successfully inserted:').' '.$user['username'].', '.$user['title'];
                                    echo '</div>';
                                }
                            } else {
                                echo "<div class='error'>";
                                echo __m('There was an error with credential:').' '.$user['username'].', '.$user['title'];
                                echo '</div>';
                            }
                        } else {
                            //Update credential
                            $row = $importCredential->getRow(0);
                            $data = array('username' => $user['username2'], 'password' => $passwordFinal, 'notes' => $user['notes']);
                            //Print out results
                            if (!$credentialsCredentialGateway->update($row['credentialsCredentialID'], $data)) {
                                echo "<div class='error'>";
                                echo __m('There was an error with credential:').' '.$user['username'].', '.$user['title'];
                                echo '</div>';
                            } else {
                                echo "<div class='success'>";
                                echo __m('The following credential was successfully updated:').' '.$user['username'].', '.$user['title'];
                                echo '</div>';
                            }
                        }
                    }
                }

                //UNLOCK TABLES
                try {
                    $sql = 'UNLOCK TABLES';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {

                }
            }
        }
    }
}
?>
