<?php
    	if (isset($_GET['userGroupID'])) $userGroupID = $_GET['userGroupID']; else $userGroupID = '';
    	$userGroup = new UserGroup(new NamedArguments(array('primaryKey' => $userGroupID)));


		//get all users for output in drop down
		$allUserArray = array();
		$userObj = new User();
		$allUserArray = $userObj->allAsArray();

		//get users already set up for this user group in case it's an edit
		$ugUserArray = $userGroup->getUsers();
?>
		<div id='div_userGroupForm'>
		<form id='userGroupForm'>
		<input type='hidden' name='editUserGroupID' id='editUserGroupID' value='<?php echo $userGroupID; ?>'>

		<div class='formTitle' style='width:280px; margin-bottom:5px;position:relative;'><span class='headerText'><?php if ($userGroupID){ echo "Edit User Group"; } else { echo "Add User Group"; } ?></span></div>

		<span class='smallDarkRedText' id='span_errors'></span>

		<table class='noBorder' style='width:100%;'>
		<tr style='vertical-align:top;'>
		<td style='vertical-align:top;position:relative;'>


			<span class='surroundBoxTitle'>&nbsp;&nbsp;<label for='rule'><b>User Group</b></label>&nbsp;&nbsp;</span>

			<table class='surroundBox' style='width:275px;'>
			<tr>
			<td>

				<table class='noBorder' style='width:235px; margin:15px 20px 10px 20px;'>
				<tr>
				<td style='vertical-align:top;text-align:left;'><label for='groupName'><b>Group Name:</b></label></td>
				<td>
				<input type='text' id='groupName' name='groupName' value = '<?php echo $userGroup->groupName; ?>' style='width:110px' class='changeInput' /><span id='span_error_groupName' class='smallDarkRedText'></span>
				</td>
				</tr>

				<tr>
				<td style='vertical-align:top;text-align:left;'><label for='emailAddress'><b>Email Address:</b></label></td>
				<td>
				<input type='text' id='emailAddress' name='emailAddress' value = '<?php echo $userGroup->emailAddress; ?>' style='width:110px' class='changeInput' />
				</td>
				</tr>
				</table>
			</td>
			</tr>
			</table>

			<div style='height:10px;'>&nbsp;</div>

			</td>
			</tr>
			<tr style='vertical-align:top;'>
			<td>

			<span class='surroundBoxTitle'>&nbsp;&nbsp;<label for='loginID'><b>Assigned Users</b></label>&nbsp;&nbsp;</span>

			<table class='surroundBox' style='width:275px;'>
			<tr>
			<td>

				<table class='noBorder smallPadding newUserTable' style='width:205px; margin:15px 35px 0px 35px;'>

				<tr class='newUserTR'>
				<td>
				<select class='changeSelect loginID' style='width:145px;'>
				<option value=''></option>
				<?php

				foreach ($allUserArray as $ugUser){
					$userObj = new User(new NamedArguments(array('primaryKey' => $ugUser['loginID'])));
					$ddDisplayName = $userObj->getDDDisplayName;
					echo "<option value='" . $ugUser['loginID'] . "'>" . $ddDisplayName . "</option>\n";
				}
				?>
				</select>
				</td>

				<td style='vertical-align:top;text-align:left;width:40px;'>
				<a href='javascript:void();'><img src='images/add.gif' class='addUser' alt='add user' title='add user'></a>
				</td>
				</tr>
				</table>
				<div class='smallDarkRedText' id='div_errorUser' style='margin:0px 35px 7px 35px;'></div>

				<table class='noBorder smallPadding userTable' style='width:205px; margin:0px 35px 0px 35px;'>
				<tr>
				<td colspan='2'>
					<hr style='width:200px;' />
				</td>
				</tr>

				<?php
				if (count($ugUserArray) > 0){

					foreach ($ugUserArray as $ugUser){
					?>
						<tr class='newUser'>
						<td>
						<select class='changeSelect loginID' style='width:145px;'>
						<option value=''></option>
						<?php
						foreach ($allUserArray as $userGroupUser){

							$userObj = new User(new NamedArguments(array('primaryKey' => $userGroupUser['loginID'])));
							$ddDisplayName = $userObj->getDDDisplayName;

							if ($ugUser->loginID == $userGroupUser['loginID']){
								echo "<option value='" . $userGroupUser['loginID'] . "' selected>" . $ddDisplayName . "</option>\n";
							}else{
								echo "<option value='" . $userGroupUser['loginID'] . "'>" . $ddDisplayName . "</option>\n";
							}
						}
						?>
						</select>
						</td>

						<td style='vertical-align:top;text-align:left;width:40px;'>
							<a href='javascript:void();'><img src='images/cross.gif' alt='remove user from group' title='remove user from group' class='remove' /></a>
						</td>
						</tr>
					<?php
					}
				}

				?>

				</table>



			</td>
			</tr>
			</table>

		</td>
		</tr>
		</table>


		<hr style='width:283px;margin-top:15px; margin-bottom:10px;' />

		<table class='noBorderTable' style='width:125px;'>
			<tr>
				<td style='text-align:left'><input type='button' value='submit' name='submitUserGroupForm' id ='submitUserGroupForm'></td>
				<td style='text-align:right'><input type='button' value='cancel' onclick="kill(); tb_remove();"></td>
			</tr>
		</table>

		</form>
		</div>

		<script type="text/javascript" src="js/forms/userGroupForm.js?random=<?php echo rand(); ?>"></script>

