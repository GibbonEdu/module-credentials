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

include '../../functions.php';
include '../../config.php';

//New PDO DB connection
try {
    $connection2 = new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}

@session_start();

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]['timezone']);

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/websites_add.php";

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_add.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $title = $_POST['title'];
    $active = $_POST['active'];
    $url = $_POST['url'];
    $notes = $_POST['notes'];

    if ($title == '' or $active == '' or $url == '') {
        //Fail 3
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('title' => $title);
            $sql = 'SELECT * FROM credentialsWebsite WHERE title=:title';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Deal with file upload
            $logo = '';
            $partialFail = false;

            //Move attached image  file, if there is one
            if (!empty($_FILES['file1']['tmp_name'])) {
                $fileUploader = new Gibbon\FileUploader($pdo, $gibbon->session);
                $fileUploader->getFileExtensions('Graphics/Design');

                $file = (isset($_FILES['file1']))? $_FILES['file1'] : null;

                // Upload the file, return the /uploads relative path
                $logo = $fileUploader->uploadFromPost($file, $title);

                if (empty($logo)) {
                    $partialFail = true;
                }
            }

            //Write to database
            try {
                $data = array('title' => $title, 'active' => $active, 'url' => $url, 'logo' => $logo, 'notes' => $notes, 'gibbonPersonIDCreator' => $_SESSION[$guid]['gibbonPersonID'], 'timestampCreator' => date('Y-m-d H:i:s', time()));
                $sql = 'INSERT INTO credentialsWebsite SET title=:title, active=:active, url=:url, logo=:logo, notes=:notes, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestampCreator=:timestampCreator';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail 2
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $AI = str_pad($connection2->lastInsertID(), 4, '0', STR_PAD_LEFT);

            //Success 0
            if ($partialFail == true) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                $URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
            }
        }
    }
}
