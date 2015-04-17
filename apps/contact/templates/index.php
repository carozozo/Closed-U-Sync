<table id="contactLayoutTable">
	<tr id="addTr">
		<td width="35%"><?php echo $l -> t('Add Group'); ?>:<br/>
		<input type="text" id="groupName" name="groupName" value="" maxlength="25"/>
		<input type="button" id="addContactGroupButton" value="<?php echo $l -> t('Add'); ?>" />
		</td>
		<td></td>
		<td></td>
		<td width="35%"><?php echo $_['newContactMessage']; ?>:<br/>
		<input type="text" id="addContactId" name="addContactId" value="<?php echo $l -> t('Insert Id or email'); ?>" alt="<?php echo $l -> t('Insert Id or email'); ?>"/>
		<!-- <input type="text" id="addContactNickname" name="addContactNickname" value="<?php echo $l -> t('Insert a nickname'); ?>" alt="<?php echo $l -> t('Insert a nickname'); ?>"/> -->
		<input type="button" id="addContactButton" value="<?php echo $l -> t('Add'); ?>" />
		</td>
	</tr>
	<tr>
		<td colspan="4">&nbsp;</td>
	</tr>
	<tr>
		<td>
		<div id="contactGroupTabDiv">
			<?php if($_['systemGroupEnabled']){ ?>
			<a id="contactSystemGroupTab" href="#"><?php echo $l -> t('System Group List'); ?></a>
			<?php } ?>
			<a id="contactGroupTab" href="#"><?php echo $l -> t('Group List'); ?></a>
		</div>
		<table id="contactGroupMainTable">
			<thead>
				<tr class="titleTr">
					<td><?php //echo $l -> t('Group List'); ?></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
					<input type="hidden" id="selectedGroup" value="">
					<div id="contactGroupListDiv">
						<img id="contactGroupListLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
						<table id="contactGroupListTable">
							<tr class="contactGroupTr">
								<td class="groupNameTd">
									<span class="groupNameSpan"></span>
									<input type="text" class="renameGroupText renameItem" value="" alt="" maxlength="25"/>
									<input type="hidden" class="groupId" value="">
								</td>
								<td class="groupButtonTd">
								<input type="button" class="renameContactGroupButton contactGroupButton" value="<?php echo $l -> t('rename'); ?>"/>
								<input type="button" class="deleteContactGroupButton contactGroupButton" value="<?php echo $l -> t('delete'); ?>"/>
								</td>
							</tr>
						</table>
					</div></td>
				</tr>
			</tbody>
		</table>
		<?php if($_['systemGroupEnabled']){ ?>
		<table id="contactSystemGroupMainTable">
			<thead>
				<tr class="titleTr">
					<td><?php //echo $l -> t('System Group List'); ?></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
					<div id="contactSystemGroupListDiv">
						<img id="contactSystemGroupListLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
						<table id="contactSystemGroupListTable">
							<tr class="contactSystemGroupTr">
								<td class="systemGroupNameTd">
									<span class="systemGroupNameSpan"></span>
									<input type="text" class="renameSystemGroupText renameItem" value="" alt="" maxlength="25"/>
									<input type="hidden" class="systemGroupId" value=""/>
								</td>
								<td class="groupButtonTd">
								<input type="button" class="renameContactSystemGroupButton contactSystemGroupButton" value="<?php echo $l -> t('rename'); ?>"/>
								<input type="button" class="deleteContactSystemGroupButton contactSystemGroupButton" value="<?php echo $l -> t('delete'); ?>"/>
								</td>
							</tr>
						</table>
					</div></td>
				</tr>
			</tbody>
		</table>
		<?php } ?>
		</td>
		<td>
		<div id="contactInGroupTabDiv">
			<a id="contactInGroupTab" href="#"><?php echo $l -> t('Contact in group'); ?></a>
		</div>
		<table id="contactInGroupMainTable">
			<thead>
				<tr class="titleTr">
					<td>
						<!-- <span id="contactInGroupTitle"></span> -->
						<span id="selectAllContactInGroupItem">
						<input type="checkbox" id="selectAllContactInGroup">
						全選</span>
					</td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
					<div id="contactInGroupDiv">
						<img id="contactInGroupLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
						<table id="contactInGroupTable">
							<tr class="contactInGroupTr">
								<td class="contactIngroupTd">
								<input id="contactInGroup" type="hidden" value=""/>
								<span id="nicknameInGroup"></span></td>
							</tr>
						</table>
					</div></td>
				</tr>
			</tbody>
		</table></td>
		<td id="contactButtonTd">
    		<input type="button" id="removeContactFromGroupButton" class="contactRelationButton" value="<?php echo $l -> t('Remove from group'); ?>=>"/>
    		<br/>
    		<br/>
    		<input type="button" id="addContactToGroupButton" class="contactRelationButton" value="<=<?php echo $l -> t('Insert to group'); ?>" />
    		<input type="button" id="addSystemGroupContactToContactButton" class="contactRelationButton2" value="<?php echo $l -> t('Add contact'); ?>=>"/>
		</td>
		<td>
		<div id="contactTabDiv">
			<a id="contactTab" href="#"><?php echo $l -> t('Contact List'); ?></a>
		</div>
		<table id="contactMainTable">
			<thead>
				<tr class="titleTr">
					<td>
					<input type="checkbox" id="selectAllContact">
					<?php echo $l -> t('select all'); ?></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
					<div id="contactListDiv">
						<img id="contactListLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
						<table id="contactListTable">
							<tr class="contactTr">
								<td class="contactIdTd">
								<input id="contactId" type="hidden" value=""/>
								<span class="nicknameSpan"></span>
								<span class="emailSpan"></span>
								<input type="text" class="renameNicknameText renameItem" value="" alt="" maxlength="25"/>
								</td>
								<td class="contactButtonTd">
									<input type="button" class="renameContactNicknameButton contactListButton" value="<?php echo $l -> t('rename'); ?>"/>
									<input type="button" class="deleteContactIdButton contactListButton" value="<?php echo $l -> t('delete'); ?>"/>
								</td>
							</tr>
						</table>
					</div></td>
				</tr>
			</tbody>
		</table></td>
	</tr>
</table>