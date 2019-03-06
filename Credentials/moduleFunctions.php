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
*/;

function getCredentialGrid($guid, $connection2, $gibbonPersonID, $mini = false)
{
    $return = null;

    try {
        $data = array('gibbonPersonID' => $gibbonPersonID);
        $sql = 'SELECT logo, title, url, username, password, credentialsCredential.notes AS credentialNotes, credentialsWebsite.notes AS websiteNotes
            FROM credentialsCredential
                JOIN credentialsWebsite ON (credentialsCredential.credentialsWebsiteID=credentialsWebsite.credentialsWebsiteID)
            WHERE gibbonPersonID=:gibbonPersonID
                AND credentialsWebsite.active=\'Y\'
            ORDER BY title';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) { $return .= "<div class='error'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) { $return .= "<div class='error'>";
        $return .= __('There are no records to display.');
        $return .= '</div>';
    } else {
        $return .= "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
        $return .= "<tr class='head'>";
        $return .= '<th>';
        $return .= __('Logo').'<br/>';
        $return .= '</th>';
        $return .= '<th>';
        $return .= __('Website').'<br/>';
        $return .= '</th>';
        $return .= '<th>';
        $return .= __('Username').'<br/>';
        $return .= '</th>';
        $return .= '<th>';
        $return .= __('Password').'<br/>';
        $return .= '</th>';
        $return .= '<th>';
        $return .= __('Action').'<br/>';
        $return .= '</th>';
        $return .= '</tr>';

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
            $return .= "<tr class=$rowNum>";
            $return .= '<td>';
            $size = '150px' ;
            if ($mini) {
                $size = '75px' ;
            }
            if ($row['logo'] != '') {
                $return .= "<img class='user' style='max-width: $size' src='".$_SESSION[$guid]['absoluteURL'].'/'.$row['logo']."'/>";
            } else {
                $return .= "<img class='user' style='max-width: $size' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['gibbonThemeName']."/img/anonymous_240_square.jpg'/>";
            }
            $return .= '</td>';
            $return .= '<td>';
            if ($row['url'] != '') {
                $return .= "<a href='".$row['url']."' target='_blank'>".$row['title'].'</a>';
            } else {
                $return .= $row['title'];
            }
            $return .= '</td>';
            $return .= '<td>';
            $return .= $row['username'];
            $return .= '</td>';
            $return .= '<td>';
            if ($row['password'] != '') {
                //Key, etc.
				$key = substr(md5(APPLICATION_WIDE_PASSPHRASE), 0, mcrypt_get_key_size(SAFETY_CIPHER, SAFETY_MODE));

				//Decrypt
				$return .= mcrypt_decrypt(SAFETY_CIPHER, $key, base64_decode(substr($row['password'], (strpos($row['password'], '$$') + 2))), SAFETY_MODE, base64_decode(substr($row['password'], 0, strpos($row['password'], '$$')))).'<br/>';
            }
            $return .= '</td>';
            $return .= '<td>';
            $return .= "<script type='text/javascript'>";
            $return .= '$(document).ready(function(){';
            $return .= "\$(\".comment-$count-$gibbonPersonID\").hide();";
            $return .= "\$(\".show_hide-$count-$gibbonPersonID\").fadeIn(1000);";
            $return .= "\$(\".show_hide-$count-$gibbonPersonID\").click(function(){";
            $return .= "\$(\".comment-$count-$gibbonPersonID\").fadeToggle(1000);";
            $return .= '});';
            $return .= '});';
            $return .= '</script>';
            if ($row['credentialNotes'] != '' or $row['websiteNotes'] != '') {
                $return.= "<a title='".__('View Notes')."' class='show_hide-$count-$gibbonPersonID' onclick='false' href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['gibbonThemeName']."/img/page_down.png' alt='".__('View Notes')."' onclick='return false;' /></a>";
            }
            echo '</td>';
            $return .= '</tr>';
            if ($row['credentialNotes'] != '' or $row['websiteNotes'] != '') {
                $return .= "<tr class='comment-$count-$gibbonPersonID' id='comment-$count-$gibbonPersonID'>";
                $return .= "<td colspan=5>";
                if ($row['credentialNotes'] != '') {
                    $return .= '<b>'.__('Student Notes').'</b><br/>';
                    $return .= nl2brr($row['credentialNotes']).'<br/><br/>';
                }
                if ($row['websiteNotes'] != '') {
                    $return .= '<b>'.__('Website Notes').'</b><br/>';
                    $return .= nl2brr($row['websiteNotes']).'<br/><br/>';
                }
                $return .= '</td>';
                $return .= '</tr>';
            }
        }
        $return .= '</table>';
    }

    return $return;
}
