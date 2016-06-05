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

@session_start();

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".__($guid, getModuleName($_GET['q']))."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/websites.php'>".__($guid, 'Manage Websites')."</a> > </div><div class='trailEnd'>".__($guid, 'Edit Website').'</div>';
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $credentialsWebsiteID = $_GET['credentialsWebsiteID'];
    if ($credentialsWebsiteID == '') {
        echo "<div class='error'>";
        echo __($guid, 'You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('credentialsWebsiteID' => $credentialsWebsiteID);
            $sql = 'SELECT * FROM credentialsWebsite WHERE credentialsWebsiteID=:credentialsWebsiteID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __($guid, 'The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $row = $result->fetch();
            ?>
			<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/websites_editProcess.php?credentialsWebsiteID=$credentialsWebsiteID" ?>" enctype="multipart/form-data">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">
					<tr>
						<td style='width: 275px'>
							<b><?php echo __($guid, 'Site Title') ?> *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<input name="title" id="title" maxlength=100 value="<?php echo $row['title'] ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								var title=new LiveValidation('title');
								title.add(Validate.Presence);
							 </script>
						</td>
					</tr>
    				<tr>
    					<td>
    						<b><?php echo __($guid, 'Active') ?> *</b><br/>
    					</td>
    					<td class="right">
    						<select name="active" id="active" class="standardWidth">
    							<option <?php if ($row['active'] == 'Y') { echo 'selected'; } ?> value="Y"><?php echo __($guid, 'Yes') ?></option>
    							<option <?php if ($row['active'] == 'N') { echo 'selected'; } ?> value="N"><?php echo __($guid, 'No') ?></option>
    						</select>
    					</td>
    				</tr>
					<tr>
						<td>
							<b><?php echo __($guid, 'URL') ?></b><br/>
						</td>
						<td class="right">
							<input name="url" id="url" maxlength=255 value="<?php echo $row['url'] ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								var url=new LiveValidation('url');
								url.add(Validate.Presence);
								url.add( Validate.Format, { pattern: /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/, failureMessage: "Must start with http:// or https://" } );
							</script>
						</td>
					</tr>
					<tr>
						<td>
							<b><?php echo __($guid, 'Logo') ?></b><br/>
						</td>
						<td class="right">
							<?php
                            if ($row['logo'] != '') {
                                echo __($guid, 'Current attachment:')." <a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/'.$row['logo']."'>".$row['logo']."</a> <a href='".$_SESSION[$guid]['absoluteURL']."/modules/Credentials/websites_edit_photoDeleteProcess.php?credentialsWebsiteID=$credentialsWebsiteID' onclick='return confirm(\"Are you sure you want to delete this record? Unsaved changes will be lost.\")'><img style='margin-bottom: -8px' title='".__($guid, 'Delete')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/garbage.png'/></a><br/><br/>";
                            }
            				?>
							<input type="file" name="file1" id="file1"><br/><br/>
							<input type="hidden" name="attachment1" value='<?php echo $row['image_240'] ?>'>
							<script type="text/javascript">
								var file1=new LiveValidation('file1');
								file1.add( Validate.Inclusion, { within: ['gif','jpg','jpeg','png'], failureMessage: "Illegal file type!", partialMatch: true, caseSensitive: false } );
							</script>
						</td>
					</tr>
                    <tr>
                        <td colspan=2>
                            <b><?php echo __($guid, 'Notes') ?></b>
                            <textarea name='notes' id='notes' rows=5 class='standardWidth'><?php echo htmlPrep($row['notes']) ?></textarea>
                        </td>
                    </tr>

					<tr>
						<td>
							<span style="font-size: 90%"><i>* <?php echo __($guid, 'denotes a required field'); ?></i></span>
						</td>
						<td class="right">
							<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
							<input type="submit" value="<?php echo __($guid, 'Submit'); ?>">
						</td>
					</tr>
				</table>
			</form>
			<?php

        }
    }
}
?>
