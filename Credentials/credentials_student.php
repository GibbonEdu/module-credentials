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


if (isActionAccessible($guid, $connection2, '/modules/Credentials/credentials_student.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {

    $gibbonPersonID = isset($_GET['gibbonPersonID'])? $_GET['gibbonPersonID'] : '';
    $search = isset($_GET['search'])? $_GET['search'] : '';
    $allStudents = isset($_GET['allStudents'])? $_GET['allStudents'] : '';

    if ($gibbonPersonID == false) { echo "<div class='error'>";
        echo __($guid, 'You have not specified one or more required parameters.');
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
            echo __($guid, 'The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $row = $result->fetch();

            echo "<div class='trail'>";
            echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".__($guid, getModuleName($_GET['q']))."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/credentials.php&search=$search&allStudents=$allStudents'>".__($guid, 'Manage Credentials')."</a> > </div><div class='trailEnd'>".formatName('', $row['preferredName'], $row['surname'], 'Student').'</div>';
            echo '</div>';

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            try {
                $data = array('gibbonPersonID' => $gibbonPersonID);
                $sql = 'SELECT *
                    FROM credentialsCredential
                        JOIN credentialsWebsite ON (credentialsCredential.credentialsWebsiteID=credentialsWebsite.credentialsWebsiteID)
                    WHERE gibbonPersonID=:gibbonPersonID
                        AND active=\'Y\'
                    ORDER BY title';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($search != '' or $allStudents != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Credentials/credentials.php&search=$search&allStudents=$allStudents'>".__($guid, 'Back to Search Results').'</a>';
                echo '</div>';
            }

            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/credentials_student_add.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents'>".__($guid, 'Add')."<img style='margin-left: 5px' title='".__($guid, 'Add')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_new.png'/></a>";
            echo '</div>';

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
                echo '<th>';
                echo __($guid, 'Actions');
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
                    echo '<td>';
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/credentials_student_edit.php&search='.$search.'&credentialsCredentialID='.$row['credentialsCredentialID']."&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents'><img title='".__($guid, 'Edit')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/config.png'/></a> ";
                        echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/credentials_student_delete.php&search='.$search.'&credentialsCredentialID='.$row['credentialsCredentialID']."&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents&width=650&height=135'><img title='".__($guid, 'Delete')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/garbage.png'/></a>";
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
    }
}
