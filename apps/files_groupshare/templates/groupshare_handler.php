<?php $langContact = new OC_L10N('contact'); ?>
<input type="hidden" id="source" value="<?php echo (!empty($_['source'])) ? $_['source'] : ''; ?>"/>
<div id='header'><?php echo $l -> t('Shared Folder'); ?>:<?php echo (!empty($_['source'])) ? $_['source'] : ''; ?></div>
<table id="groupShareLayoutTable">
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td id="permissionMainTd">
			<?php require_once('groupshare_handler/permission.php');?>
			<?php require_once('groupshare_handler/contact_for_groupshare.php');?>
		</td>
	</tr>
	<tr>
		<td id="contactMainTd" width="40%">
			<?php require_once('groupshare_handler/contact.php');?>
		</td>
		<td id="shareBtnMainTd">
			<?php require_once('groupshare_handler/share_btn.php');?>
		</td>
		<td id="sharedMainTd" width="40%">
			<?php require_once('groupshare_handler/shared.php');?>
		</td>
	</tr>
</table>