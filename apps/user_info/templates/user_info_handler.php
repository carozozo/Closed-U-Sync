<span id="userInfoTitleSpan"><?php echo $l -> t('Edit User Profile'); ?></span>
<table class='userInfoTable'>
  <tr>
    <td class="userInfoTitleTd">
    	<?php echo $l -> t('Change nickname'); ?>-
    </td>
  </tr>
  <tr>
    <td>
    	<input type='text' id='userInfoNicknameInp' value='<?php echo $_['nickname']; ?>' />
    	<input type='button' id='updateNicknameBtn' value='Ok' />
    </td>
  </tr>
</table>

<table class='userInfoTable'>
  <tr>
    <td class="userInfoTitleTd">
    	<?php echo $l -> t('Change password'); ?>-
    </td>
  </tr>
  <tr>
    <td>
    	<?php echo $l -> t('Old password'); ?>:
    </td>
    <td>
    	<input type='password' id='userInfoOldPasswordInp' class='userInfoPwdInp' value='' />
    </td>
  </tr>
  <tr>
    <td>
    	<?php echo $l -> t('New password'); ?>:
    </td>
    <td>
    	<input type='password' id='userInfoNewPasswordInp' class='userInfoPwdInp' value='' />
    </td>
  </tr>
  <tr>
    <td>
    	<?php echo $l -> t('Check new password'); ?>:
    </td>
    <td>
    	<input type='password' id='userInfoCheckNewPasswordInp' class='userInfoPwdInp' value='' />
    	<input type='button' id='updatePasswordBtn' value='Ok' />
    </td>
  </tr>
</table>

<table class='userInfoTable'>
  <tr>
    <td class="userInfoTitleTd">
    	<?php echo $l -> t('Change Email'); ?>-
    </td>
  </tr>
  <tr>
    <td>
    	<?php echo $l -> t('Email'); ?>:
    	<input type='text' id='userInfoEmailInp' value='<?php echo $_['email']; ?>' />
    	<input type='button' id='updateEmailBtn' value='Ok' />
    	<span id='vaildateMessSpan'><?php echo $_['vaildateMess']; ?></span>
    	<input type='hidden' id='vaildateMessColor' value='<?php echo $_['vaildateMessColor']; ?>'/>
    </td>
  </tr>
</table>
<table class='userInfoTable'>
	<tr>
		<td>
			&nbsp;<span id="userInfoMess">
				<?php echo $_['defaultMes']?>
			</span>			
		</td>
	</tr>
</table>