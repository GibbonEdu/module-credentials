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

//Module includes
include './modules/Credentials/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Credentials/import_credentials.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Import Credentials'));

    $step = $_GET['step'] ?? 1;

    if (($step != 1) and ( $step != 2)) {
        $step = 1;
    }

    //STEP 1, SELECT TERM
    if ($step == 1) {

        echo '<h2>';
        echo __('Step 1 - Select CSV Files');
        echo '</h2>';
        echo '<p>';
        echo __('This page allows you to import student credentials from a CSV file. The import will add credentials for sites a user user does not already have (based on name and URL), and update them otherwise. No credentials will be removed. Select the CSV file you wish to use for the import operation.');
        echo '</p>';

        $form = Form::create('importUserPhotos', $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/import_credentials.php&step=2');

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $row = $form->addRow();
        $row->addLabel('file', __('CSV File'))->description(__('See Notes below for specification.'));
        $row->addFileUpload('file')->required();

        $row = $form->addRow();
        $row->addLabel('fieldDelimiter', __('Field Delimiter'));
        $row->addTextField('fieldDelimiter')->required()->maxLength(1)->setValue(',');

        $row = $form->addRow();
        $row->addLabel('stringEnclosure', __('String Enclosure'));
        $row->addTextField('stringEnclosure')->required()->maxLength(1)->setValue('"');

        $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();
        ?>

        <h4>
            <?php echo __('Notes') ?>
        </h4>
        <ol>
            <li style='color: #c00; font-weight: bold'><?php echo __('THE SYSTEM WILL NOT PROMPT YOU TO PROCEED, IT WILL JUST DO THE IMPORT. BACKUP YOUR DATA.') ?></li>
            <li><?php echo __('You may only submit CSV files.') ?></li>
            <li><?php echo __('Imports cannot be run concurrently (e.g. make sure you are the only person importing at any one time).') ?></li>
            <li><?php echo __('Your import should only include all current students.') ?></li>
            <li><?php echo __('The submitted file must have the following fields in the following order (* denotes required field):') ?></li>
            <ol>
                <li><b><?php echo sprintf(__('%1$s Username'), $_SESSION[$guid]['systemName']) ?> *</b></li>
                <li><b><?php echo __('Website') ?> *</b> - <?php echo __('Title/name of website. Must exist in Manage Websites section.') ?></li>
                <li><b><?php echo __('Credential Username') ?></b></li>
                <li><b><?php echo __('Password') ?></b> - <?php echo __('Plain text. It will be encrypted before being saved in database.') ?></li>
                <li><b><?php echo __('Notes') ?></b></li>
            </ol>
        </li>
        <li><?php echo __('Do not include a header row in the CSV files.') ?></li>
        </ol>
        <?php
    } elseif ($step == 2) {
        echo '<h2>';
        echo __('Step 2 - Data Check & Confirm');
        echo '</h2>';

        //Check file type
        if (($_FILES['file']['type'] != 'text/plain') and ( $_FILES['file']['type'] != 'text/csv') and ( $_FILES['file']['type'] != 'text/comma-separated-values') and ( $_FILES['file']['type'] != 'text/x-comma-separated-values') and ( $_FILES['file']['type'] != 'application/vnd.ms-excel')) {
            echo '<div class="error">';
            echo sprintf(__('Import cannot proceed, as the submitted file has a MIME-TYPE of %1$s, and as such does not appear to be a CSV file.<br />'), $_FILES['file']['type']);
            echo '</div>';
        } elseif (($_POST['fieldDelimiter'] == '') or ( $_POST['stringEnclosure'] == '')) {
            echo '<div class="error">';
            echo __('Import cannot proceed, as the "Field Delimiter" and/or "String Enclosure" fields have been left blank.<br/>');
            echo '</div>';
        } else {
            $proceed = true;

            //PREPARE TABLES
            echo '<h4>';
            echo __('Prepare Database Tables');
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
                echo __('The database could not be locked for use.');
                echo '</div>';
            } elseif ($lockFail == false) {
                echo "<div class='success'>";
                echo __('The database was successfully locked.');
                echo '</div>';
            }

            if ($lockFail == false) {
                //READ IN DATA
                if ($proceed == true) {
                    echo '<h4>';
                    echo __('File Import');
                    echo '</h4>';
                    $importFail = false;
                    $csvFile = $_FILES['file']['tmp_name'];
                    $handle = fopen($csvFile, 'r');
                    $users = array();
                    $userCount = 0;
                    $userSuccessCount = 0;
                    while (($data = fgetcsv($handle, 100000, stripslashes($_POST['fieldDelimiter']), stripslashes($_POST['stringEnclosure']))) !== false) {
                        if ($data[0] != '' and $data[1] != '') {
                            $users[$userSuccessCount]['username'] = $data[0];
                            $users[$userSuccessCount]['title'] = $data[1];
                            $users[$userSuccessCount]['username2'] = $data[2] ?? '';
                            $users[$userSuccessCount]['password'] = $data[3] ?? '';
                            $users[$userSuccessCount]['notes'] = $data[4] ?? '';
                            ++$userSuccessCount;
                        } else {
                            echo "<div class='error'>";
                            echo sprintf(__('Student with username %1$s had some information malformations.'), $data[0]);
                            echo '</div>';
                        }
                        ++$userCount;
                    }
                    fclose($handle);
                    if ($userSuccessCount == 0) {
                        echo "<div class='error'>";
                        echo __('No useful credentials were detected in the import file (perhaps they did not meet minimum requirements), so the import will be aborted.');
                        echo '</div>';
                        $proceed = false;
                    } elseif ($userSuccessCount < $userCount) {
                        echo "<div class='error'>";
                        echo __('Some credentials could not be successfully read or used, so the import will be aborted.');
                        echo '</div>';
                        $proceed = false;
                    } elseif ($userSuccessCount == $userCount) {
                        echo "<div class='success'>";
                        echo __('All credentials could be read and used, so the import will proceed.');
                        echo '</div>';
                    } else {
                        echo "<div class='error'>";
                        echo __('An unknown error occured, so the import will be aborted.');
                        echo '</div>';
                        $proceed = false;
                    }
                }

                if ($proceed == true) {
                    echo '<h4>';
                    echo __('Enrol All Students');
                    echo '</h4>';
                    foreach ($users as $user) {
                        $checkFail = false;
                        //Check if credential exists for user
                        try {
                            $data = array('username' => $user['username'], 'title' => $user['title']);
                            $sql = 'SELECT *
                                FROM credentialsCredential
                                    JOIN credentialsWebsite ON (credentialsCredential.credentialsWebsiteID=credentialsWebsite.credentialsWebsiteID)
                                    JOIN gibbonPerson ON (credentialsCredential.gibbonPersonID=gibbonPerson.gibbonPersonID)
                                WHERE gibbonPerson.username=:username
                                    AND credentialsWebsite.title=:title';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $checkFail = true;
                        }

                        if ($checkFail == true) {
                            echo "<div class='error'>";
                            echo __('There was an error with credential:') . ' ' . $user['username'] . ', ' . $user['title'];
                            echo '</div>';
                        } else {
                            $passwordFinal = null;
                            if ($user['password'] != '') {
                                //Encrypt & prepare
                                $passwordFinal = getEncryptCredentialOpenssl($user['password']);
                            }

                            if ($result->rowCount() < 1) { //INSERT
                                $credentialInsertFail = false;
                                try {
                                    $data = array('username' => $user['username'], 'title' => $user['title'], 'username2' => $user['username2'], 'password' => $passwordFinal, 'notes' => $user['notes'], 'gibbonPersonIDCreator' => $_SESSION[$guid]['gibbonPersonID'], 'timestampCreator' => date('Y-m-d H:i:s', time()));
                                    $sql = 'INSERT INTO credentialsCredential SET gibbonPersonID=(SELECT gibbonPersonID FROM gibbonPerson WHERE username=:username), credentialsWebsiteID=(SELECT credentialsWebsiteID FROM credentialsWebsite WHERE title=:title), username=:username2, password=:password, notes=:notes, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestampCreator=:timestampCreator';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $credentialInsertFail = true;
                                }

                                //Spit out results
                                if ($credentialInsertFail == true) {
                                    echo "<div class='error'>";
                                    echo __('There was an error with credential:') . ' ' . $user['username'] . ', ' . $user['title'];
                                    echo '</div>';
                                } else {
                                    echo "<div class='success'>";
                                    echo __('The following credential was successfully inserted:') . ' ' . $user['username'] . ', ' . $user['title'];
                                    echo '</div>';
                                }
                            } else { //UPDATE
                                $credentialInsertFail = false;
                                $row = $result->fetch();
                                try {
                                    $data = array('credentialsCredentialID' => $row['credentialsCredentialID'], 'username2' => $user['username2'], 'password' => $passwordFinal, 'notes' => $user['notes']);
                                    $sql = 'UPDATE credentialsCredential SET username=:username2, password=:password, notes=:notes WHERE credentialsCredentialID=:credentialsCredentialID';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $credentialInsertFail = true;
                                }

                                //Spit out results
                                if ($credentialInsertFail == true) {
                                    echo "<div class='error'>";
                                    echo __('There was an error with credential:') . ' ' . $user['username'] . ', ' . $user['title'];
                                    echo '</div>';
                                } else {
                                    echo "<div class='success'>";
                                    echo __('The following credential was successfully updated:') . ' ' . $user['username'] . ', ' . $user['title'];
                                    echo '</div>';
                                }
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
