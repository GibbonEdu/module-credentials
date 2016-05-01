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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

@session_start();

//Module includes
include './modules/Credentials/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Credentials/hook_studentProfile_credentials.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    try {
        $data = array('gibbonPersonID' => $gibbonPersonID);
        $sql = 'SELECT * FROM credentialsCredential WHERE gibbonPersonID=:gibbonPersonID ORDER BY title';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='error'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) {
        echo "<div class='error'>";
        echo __($guid, 'There are no records to display.');
        echo '</div>';
    } else {
        echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __($guid, 'Title').'<br/>';
        echo '</th>';
        echo '<th>';
        echo __($guid, 'Username');
        echo '</th>';
        echo '<th>';
        echo __($guid, 'Password').'<br/>';
        echo '</th>';
        echo '</tr>';

            //Decryption defines
            define('SAFETY_CIPHER', MCRYPT_RIJNDAEL_256);
        define('SAFETY_MODE', MCRYPT_MODE_CFB);
        define('APPLICATION_WIDE_PASSPHRASE', $guid);
        define('ENCRYPTION_DIVIDER_TOKEN', '$$');

        $count = 0;
        $rowNum = 'odd';
        while ($row = $result->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

                //COLOR ROW BY STATUS!
                echo "<tr class=$rowNum>";
            echo '<td>';
            if ($row['url'] != '') {
                echo "<a href='".$row['url']."' target='_blank'>".$row['title'].'</a>';
            } else {
                echo $row['title'];
            }
            echo '</td>';
            echo '<td>';
            echo $row['username'];
            echo '</td>';
            echo '<td>';
            if ($row['password'] != '') {
                //Key, etc.
                            $key = substr(md5(APPLICATION_WIDE_PASSPHRASE), 0, mcrypt_get_key_size(SAFETY_CIPHER, SAFETY_MODE));

                            //Decrypt
                            echo mcrypt_decrypt(SAFETY_CIPHER, $key, base64_decode(substr($row['password'], (strpos($row['password'], '$$') + 2))), SAFETY_MODE, base64_decode(substr($row['password'], 0, strpos($row['password'], '$$')))).'<br/>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
