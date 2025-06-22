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

use Gibbon\Module\Credentials\Domain\WebsiteGateway;

include '../../gibbon.php';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address'])."/websites_add.php";

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_add.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $title = $_POST['title'] ?? '';
    $active = $_POST['active'] ?? '';
    $url = $_POST['url'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (trim($title) == '' or trim($active) == '' or trim($url) == '') {
        //Fail 3
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        $websiteGateway = $container->get(WebsiteGateway::class);
        $data = array('title' => $title);
        $website = $websiteGateway->selectBy($data)->fetch();

        if (!empty($website)) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Deal with file upload
            $logo = '';
            $partialFail = false;

            //Move attached image  file, if there is one
            if (!empty($_FILES['file1']['tmp_name'])) {
                $fileUploader = new Gibbon\FileUploader($pdo, $session);
                $fileUploader->getFileExtensions('Graphics/Design');

                $file = (isset($_FILES['file1'])) ? $_FILES['file1'] : null;

                // Upload the file, return the /uploads relative path
                $logo = $fileUploader->uploadFromPost($file, $title);

                if (empty($logo)) {
                    $partialFail = true;
                }
            }

            //Write to database
            $data = array('title' => $title, 'active' => $active, 'url' => $url, 'logo' => $logo, 'notes' => $notes, 'gibbonPersonIDCreator' => $session->get('gibbonPersonID'), 'timestampCreator' => date('Y-m-d H:i:s', time()));
            $AI = $websiteGateway->insert($data);

            //Success 0
            if ($partialFail == true) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                $URL .= "&return=success0&editID=".str_pad($AI, 4, '0', STR_PAD_LEFT);
                header("Location: {$URL}");
            }
        }
    }
}
