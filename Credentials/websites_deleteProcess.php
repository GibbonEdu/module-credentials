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
use Gibbon\Module\Credentials\CredentialsCredentialGateway;

include '../../gibbon.php';

$credentialsWebsiteID = $_GET['credentialsWebsiteID'] ?? '';
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . "/websites_delete.php&credentialsWebsiteID=" . $credentialsWebsiteID;
$URLDelete = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . "/websites.php";

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_delete.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if note specified
    if ($credentialsWebsiteID == '') {
        echo __('Fatal error loading this page!');
    } else {

        $credentialsCredentialGateway = $container->get(CredentialsCredentialGateway::class);
        $credentialsCredentialGateway->deleteCredentialsCredentialbyWebsite($credentialsWebsiteID);

        $credentials = $credentialsCredentialGateway->selectCredentialsCredentialByWebsite($credentialsWebsiteID);

        if ($credentials->isEmpty()) {
            //Write to database
            try {
                $credentialsWebsiteGateway = $container->get(CredentialsWebsiteGateway::class);
                $website = $credentialsWebsiteGateway->querySelectCredentialsWebsiteById($credentialsWebsiteID)->fetchAll();

                if (count($website) == 1) {
                    if ($website[0]['logo'] != '') {
                        $fileLogo = $_SESSION[$guid]['absolutePath'] . '/' . $website[0]['logo'];
                        if (file_exists($fileLogo) and is_file($fileLogo)) {
                            unlink($fileLogo);
                        }
                    }
                    if (!$credentialsWebsiteGateway->deleteCredentialsWebsite($credentialsWebsiteID)) {
                        throw new Exception();
                    }
                }
            } catch (Exception $e) {
                //Fail2
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }
            //Success 0
            $URLDelete = $URLDelete . '&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
