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

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __($guid, 'You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".__($guid, getModuleName($_GET['q']))."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/websites.php'>".__($guid, 'Manage Websites')."</a> > </div><div class='trailEnd'>".__($guid, 'Add Website').'</div>';
    echo '</div>';

    $returns = array();
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Credentials/websites_edit.php&credentialsWebsiteID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, $returns);
    }
    ?>
	<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/websites_addProcess.php" ?>" enctype="multipart/form-data">
		<table class='smallIntBorder' cellspacing='0' style="width: 100%">
			<tr>
				<td style='width: 275px'>
					<b><?php echo __($guid, 'Website Title') ?> *</b><br/>
					<span class="emphasis small"><?php echo __($guid, 'Must be unique.') ?></span>
				</td>
				<td class="right">
					<input name="title" id="title" maxlength=100 value="" type="text" style="width: 300px">
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
						<option value="Y"><?php echo __($guid, 'Yes') ?></option>
						<option value="N"><?php echo __($guid, 'No') ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<b><?php echo __($guid, 'URL') ?></b><br/>
				</td>
				<td class="right">
					<input name="url" id="url" maxlength=255 value="" type="text" style="width: 300px">
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
					<input type="file" name="file1" id="file1"><br/><br/>
					<script type="text/javascript">
						var file1=new LiveValidation('file1');
						file1.add( Validate.Inclusion, { within: ['gif','jpg','jpeg','png'], failureMessage: "Illegal file type!", partialMatch: true, caseSensitive: false } );
					</script>
				</td>
			</tr>
            <tr>
                <td colspan=2>
                    <b><?php echo __($guid, 'Notes') ?></b>
                    <textarea name='notes' id='notes' rows=5 class='standardWidth'></textarea>
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
?>
