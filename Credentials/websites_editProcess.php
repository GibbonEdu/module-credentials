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

$credentialsWebsiteID = $_GET['credentialsWebsiteID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/websites_edit.php&credentialsWebsiteID=$credentialsWebsiteID";

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_edit.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if note specified
    if ($credentialsWebsiteID == '') {
        echo 'Fatal error loading this page!';
    } else {
        try {
            $data = array('credentialsWebsiteID' => $credentialsWebsiteID);
            $sql = 'SELECT * FROM credentialsWebsite WHERE credentialsWebsiteID=:credentialsWebsiteID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            //Fail2
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            //Fail 2
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            $row = $result->fetch();

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
                    $dataCheck = array('title' => $title, 'credentialsWebsiteID' => $credentialsWebsiteID);
                    $sqlCheck = 'SELECT * FROM credentialsWebsite WHERE title=:title AND NOT credentialsWebsiteID=:credentialsWebsiteID';
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($resultCheck->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Sort out logo
                    $imageFail = false;
                    $logo = $row['logo'];
                    if ($_FILES['file1']['tmp_name'] != '') {
                        $time = time();
                        //Check for folder in uploads based on today's date
                        $path = $_SESSION[$guid]['absolutePath'];
                        if (is_dir($path.'/uploads/'.date('Y', $time).'/'.date('m', $time)) == false) {
                            mkdir($path.'/uploads/'.date('Y', $time).'/'.date('m', $time), 0777, true);
                        }

                        $unique = false;
                        $count = 0;
                        while ($unique == false and $count < 100) {
                            $suffix = randomPassword(16);
                            if ($count == 0) {
                                $logo = 'uploads/'.date('Y', $time).'/'.date('m', $time).'/'.$title."_$suffix".strrchr($_FILES['file1']['name'], '.');
                            } else {
                                $logo = 'uploads/'.date('Y', $time).'/'.date('m', $time).'/'.$title."_$suffix"."_$count".strrchr($_FILES['file1']['name'], '.');
                            }

                            if (!(file_exists($path.'/'.$logo))) {
                                $unique = true;
                            }
                            ++$count;
                        }
                        if (!(move_uploaded_file($_FILES['file1']['tmp_name'], $path.'/'.$logo))) {
                            $logo = '';
                            $imageFail = true;
                        }
                    }

                    //Write to database
                    try {
                        $data = array('title' => $title, 'active' => $active, 'url' => $url, 'logo' => $logo, 'notes' => $notes, 'credentialsWebsiteID' => $credentialsWebsiteID);
                        $sql = 'UPDATE credentialsWebsite SET title=:title, active=:active, url=:url, logo=:logo, notes=:notes WHERE credentialsWebsiteID=:credentialsWebsiteID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo $e->getMessage();
                        exit();
                        //Fail 2
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Success 0
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
