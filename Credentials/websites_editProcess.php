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
use Gibbon\Module\Credentials\CredentialsWebsiteGateway;

include '../../gibbon.php';


$credentialsWebsiteID = $_GET['credentialsWebsiteID'] ?? '';
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/websites_edit.php&credentialsWebsiteID=$credentialsWebsiteID";

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_edit.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if note specified
    if ($credentialsWebsiteID == '') {
        echo __('Fatal error loading this page!');
    } else {
        try {
            $websiteGateway = $container->get(CredentialsWebsiteGateway::class);
            $website = $websiteGateway->querySelectCredentialsWebsiteById($credentialsWebsiteID)->fetchAll();
            if (!$website and !empty($website)) {
                 throw new Exception();
            }
        } catch (Exception $e) {
            //Fail2
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
        if (count($website) != 1) {
            //Fail 2
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            $title = $_POST['title'] ?? '';
            $active = $_POST['active'] ?? '';
            $url = $_POST['url'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (trim($title) == '' or trim($active) == '' or trim($url == '')) {
                //Fail 3
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $uniqueTitle = $websiteGateway->queryCheckUniqueTitleCredentialsWebsite($title, $credentialsWebsiteID)->fetchAll();
                    if (!$uniqueTitle and empty($website)) {
                         throw new Exception();
                    }
                } catch (Exception $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if (count($uniqueTitle) > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Sort out logo
                    $logo = isset($_POST['logo'])? $_POST['logo'] : '';
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
                        if ($logo != $website[0]['logo']) {
                            $fileLogo = $_SESSION[$guid]['absolutePath'] . '/' . $website[0]['logo'];
                            if (file_exists($fileLogo) and is_file($fileLogo)) {
                                unlink($fileLogo);
                            }
                        }
                        $data = array('title' => $title, 'active' => $active, 'url' => $url, 'logo' => $logo, 'notes' => $notes, 'credentialsWebsiteID' => $credentialsWebsiteID);
                        if (!$websiteGateway->updateCredentialsWebsite($data)) {
                            throw new Exception();
                        }
                    } catch (Exception $e) {
                        //Fail 2
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Success 0
                    if ($partialFail == true) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                    } else {
                        $URL .= "&return=success0";
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
