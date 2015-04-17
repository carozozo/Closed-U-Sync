<!-- <fieldset> -->
<div id="emptyMessage"><?php echo $l -> t('The files you shared list here. You can manage them easily.'); ?></div>
<div id="groupShareListLayoutDiv">
	<img id="sharedListLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
	<table id="groupShareListTable">
		<thead>
			<tr id="groupShareTitleTr">
				<th id='pathSortTh' class="groupShareTitleTd sortClass" alt='desc'>
				    <?php echo $l -> t('Folder Path'); ?>
				    <span id='pathSort' class="sortClass">▲</span>：
				</th>
				<th class="groupShareTitleTd"><?php echo $l -> t('Shared With'); ?>：</th>
				<th class="groupShareTitleTd"><?php echo $l -> t('Can change shared content'); ?>：</th>
				<th><input type="button" id="refreshGroupShareButton" alt="" value="<?php echo $l -> t('Refresh'); ?>"/></th>
			</tr>
		</thead>
		<tbody>
			<tr class="groupShareTr">
				<td id="source"></td>
				<td id="uid_shared_with"></td>
				<td id="permission">
					<!-- <input type="checkbox" id="permissionCK"/> -->
					<input type="checkbox" id="permission1"/>
					<?php echo $l -> t('Allow Download/Copy'); ?>
					<input type="checkbox" id="permission2"/>
					<?php echo $l -> t('Allow Upload'); ?>
				</td>
				<td>
				<input type="button" id="updateGroupShareButton" alt="" value="<?php echo $l -> t('Modify'); ?>"/>
				<input type="button" id="removeGroupShareButton" alt="" value="<?php echo $l -> t('Delete'); ?>"/>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<!-- </fieldset> -->