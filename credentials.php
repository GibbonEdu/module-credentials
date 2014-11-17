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

@session_start() ;

if (isActionAccessible($guid, $connection2, "/modules/Credentials/credentials.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print _("You do not have access to this action.") ;
	print "</div>" ;
}
else {
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . _("Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . _(getModuleName($_GET["q"])) . "</a> > </div><div class='trailEnd'>" . _('Manage Credentials') . "</div>" ;
	print "</div>" ;
	
	print "<div class='warning'>" ;
	print "<b><u>WARNING</u></b>: This module uses two-way encryption to store and retreive passwords. This is secure, but far from infallible. Please use this module only for storing student credentials for sites which do not include sensitive personal data." ;
	print "</div>" ;
	
	print "<h2>" ;
	print _("Search") ;
	print "</h2>" ;
	
	$gibbonPersonID=NULL;
	if (isset($_GET["gibbonPersonID"])) {
		$gibbonPersonID=$_GET["gibbonPersonID"] ;
	}
	$search=NULL;
	if (isset($_GET["search"])) {
		$search=$_GET["search"] ;
	}
	$allStudents="" ;
	if (isset($_GET["allStudents"])) {
		$allStudents=$_GET["allStudents"] ;
	}
	
	?>
	<form method="get" action="<?php print $_SESSION[$guid]["absoluteURL"]?>/index.php">
		<table class='noIntBorder' cellspacing='0' style="width: 100%">	
			<tr><td style="width: 30%"></td><td></td></tr>
			<tr>
				<td> 
					<b><?php print _('Search For') ?></b><br/>
					<span style="font-size: 90%"><i><?php print _('Preferred, surname, username.') ?></i></span>
				</td>
				<td class="right">
					<input name="search" id="search" maxlength=20 value="<?php print $search ?>" type="text" style="width: 300px">
				</td>
			</tr>
			<tr>
				<td> 
					<b><?php print _('All Students') ?></b><br/>
					<span style="font-size: 90%"><i><?php print _('Include all students, regardless of status and current enrolment. Some data may not display.') ?></i></span>
				</td>
				<td class="right">
					<?php
					$checked="" ;
					if ($allStudents=="on") {
						$checked="checked" ;
					}
					print "<input $checked name=\"allStudents\" id=\"allStudents\" type=\"checkbox\">" ;
					?>
				</td>
			</tr>
			<tr>
				<td colspan=2 class="right">
					<input type="hidden" name="q" value="/modules/<?php print $_SESSION[$guid]["module"] ?>/credentials.php">
					<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
					<?php
					print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/credentials.php'>" . _('Clear Search') . "</a>" ;
					?>
					<input type="submit" value="<?php print _("Submit") ; ?>">
				</td>
			</tr>
		</table>
	</form>
	<?php
	
	print "<h2>" ;
	print _("Choose A Student") ;
	print "</h2>" ;
	
	//Set pagination variable
	$page=1 ; if (isset($_GET["page"])) { $page=$_GET["page"] ; }
	if ((!is_numeric($page)) OR $page<1) {
		$page=1 ;
	}
	
	try {
		if ($allStudents!="on") {
			$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"]); 
			$sql="SELECT gibbonPerson.gibbonPersonID, status, gibbonStudentEnrolmentID, surname, preferredName, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, (SELECT COUNT(gibbonPersonID) FROM credentialsCredential WHERE gibbonPersonID=gibbonPerson.gibbonPersonID) AS credentialCount FROM gibbonPerson, gibbonStudentEnrolment, gibbonYearGroup, gibbonRollGroup WHERE (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) AND (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) AND (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) AND gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND gibbonPerson.status='Full' ORDER BY surname, preferredName" ; 
			if ($search!="") {
				$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "search1"=>"%$search%", "search2"=>"%$search%", "search3"=>"%$search%"); 
				$sql="SELECT gibbonPerson.gibbonPersonID, status, gibbonStudentEnrolmentID, surname, preferredName, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, (SELECT COUNT(gibbonPersonID) FROM credentialsCredential WHERE gibbonPersonID=gibbonPerson.gibbonPersonID) AS credentialCount FROM gibbonPerson, gibbonStudentEnrolment, gibbonYearGroup, gibbonRollGroup WHERE (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) AND (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) AND (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) AND gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND (preferredName LIKE :search1 OR surname LIKE :search2 OR username LIKE :search3) AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND gibbonPerson.status='Full' ORDER BY surname, preferredName" ; 
			}
		}
		else {
			$data=array(); 
			$sql="SELECT DISTINCT gibbonPerson.gibbonPersonID, status, surname, preferredName, NULL AS yearGroup, NULL AS rollGroup, (SELECT COUNT(gibbonPersonID) FROM credentialsCredential WHERE gibbonPersonID=gibbonPerson.gibbonPersonID) AS credentialCount FROM gibbonPerson, gibbonStudentEnrolment WHERE (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) ORDER BY surname, preferredName" ; 
			if ($search!="") {
				$data=array("search1"=>"%$search%", "search2"=>"%$search%", "search3"=>"%$search%"); 
				$sql="SELECT DISTINCT gibbonPerson.gibbonPersonID, status, surname, preferredName, NULL AS yearGroup, NULL AS rollGroup, (SELECT COUNT(gibbonPersonID) FROM credentialsCredential WHERE gibbonPersonID=gibbonPerson.gibbonPersonID) AS credentialCount FROM gibbonPerson, gibbonStudentEnrolment WHERE (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) AND (preferredName LIKE :search1 OR surname LIKE :search2 OR username LIKE :search3) ORDER BY surname, preferredName" ; 
			}
		}
		$sqlPage=$sql . " LIMIT " . $_SESSION[$guid]["pagination"] . " OFFSET " . (($page-1)*$_SESSION[$guid]["pagination"]) ; 
		$result=$connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) { 
		print "<div class='error'>" . $e->getMessage() . "</div>" ; 
	}

	if ($result->rowcount()<1) {
		print "<div class='error'>" ;
		print _("There are no records to display.") ;
		print "</div>" ;
	}
	else {
		if ($result->rowcount()>$_SESSION[$guid]["pagination"]) {
			printPagination($guid, $result->rowcount(), $page, $_SESSION[$guid]["pagination"], "top", "&search=$search&allStudents=$allStudents") ;
		}
	
		print "<table cellspacing='0' style='width: 100%'>" ;
			print "<tr class='head'>" ;
				print "<th>" ;
					print _("Name") ;
				print "</th>" ;
				print "<th>" ;
					print _("Year Group") ;
				print "</th>" ;
				print "<th>" ;
					print _("Roll Group") ;
				print "</th>" ;
				print "<th>" ;
					print _("Credential Count") ;
				print "</th>" ;
				print "<th>" ;
					print _("Actions") ;
				print "</th>" ;
			print "</tr>" ;
			
			$count=0;
			$rowNum="odd" ;
			try {
				$resultPage=$connection2->prepare($sqlPage);
				$resultPage->execute($data);
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
			while ($row=$resultPage->fetch()) {
				if ($count%2==0) {
					$rowNum="even" ;
				}
				else {
					$rowNum="odd" ;
				}
				if ($row["status"]!="Full") {
					$rowNum="error" ;
				}
				$count++ ;
				
				//COLOR ROW BY STATUS!
				print "<tr class=$rowNum>" ;
					print "<td>" ;
						print formatName("", $row["preferredName"],$row["surname"], "Student", true) ;
					print "</td>" ;
					print "<td>" ;
						if ($row["yearGroup"]!="") {
							print _($row["yearGroup"]) ;
						}
					print "</td>" ;
					print "<td>" ;
						print $row["rollGroup"] ;
					print "</td>" ;
					print "<td>" ;
						print $row["credentialCount"] ;
					print "</td>" ;
					print "<td>" ;
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/credentials_student.php&gibbonPersonID=" . $row["gibbonPersonID"] . "&search=$search&allStudents=$allStudents'><img title='" . _('View Details') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/plus.png'/></a> " ;
					print "</td>" ;
				print "</tr>" ;
			}
		print "</table>" ;
		
		if ($result->rowcount()>$_SESSION[$guid]["pagination"]) {
			printPagination($guid, $result->rowcount(), $page, $_SESSION[$guid]["pagination"], "bottom", "search=$search") ;
		}
	}
}
?>