<input id="systemGroupEnabled" type="hidden" value="<?php echo $_['systemGroupEnabled'];?>"/>
<?php if($_['systemGroupEnabled']){ ?>
<a id="systemGroupTab" href="#"><?php echo $l -> t('System Group'); ?></a>
<?php } ?>
<a id="groupTab" href="#"><?php echo $l -> t('Group'); ?></a>
<a id="contactTab" href="#"><?php echo $l -> t('Contact'); ?></a>
<?php if($_['systemGroupEnabled']){ ?>
<table id="systemGroupMainTable">
	<thead>
		<tr class="titleTr">
			<td>
				<input type="checkbox" id="selectAllSystemGroup">
				<?php echo $l -> t('select all'); ?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<div id="systemGroupListDiv">
					<img id="systemGroupListLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
					<table id="systemGroupListTable">
						<tr class="systemGroupTr">
							<td class="systemGroupTd">
								<input id="systemGroupId" type="hidden" value=""/>
								<span id="systemGroupName"></span>
								<span class="systemGroupContentBtn">&nbsp;&nbsp;▼&nbsp;&nbsp;</span>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<?php } ?>
<table id="groupMainTable">
	<thead>
		<tr class="titleTr">
			<td>
				<input type="checkbox" id="selectAllGroup">
				<?php echo $l -> t('select all'); ?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<div id="groupListDiv">
					<img id="groupListLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
					<table id="groupListTable">
						<tr class="groupTr">
							<td class="groupTd">
								<input id="groupId" type="hidden" value=""/>
								<span id="groupName"></span>
								<span class="groupContentBtn">&nbsp;&nbsp;▼&nbsp;&nbsp;</span>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<table id="contactMainTable">
	<thead>
		<tr class="titleTr">
			<td>
				<input type="checkbox" id="selectAllContact">
				<?php echo $l -> t('select all'); ?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<div id="contactListDiv">
					<img id="contactListLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
					<table id="contactListTable">
						<tr class="contactTr">
							<td class="contactTd">
								<input id="contactId" type="hidden" value=""/>
								<span id="nickname"></span>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
</table>
<table id="contactInGroupMainTable">
	<thead>
		<tr class="titleTr">
			<td>
				<div id="groupNameDiv">
					<span id="groupNameSpan"></span>
					<span id="contactInGroupCloseSpan" class="closeSpanClass">&nbsp;&nbsp;X&nbsp;&nbsp;</span>
				</div>
				<input type="checkbox" id="selectAllContactInGroup">
				<?php echo $l -> t('select all'); ?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
			<div id="contactInGroupListDiv">
				<img id="contactInGroupLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
				<table id="contactInGroupListTable">
					<tr class="contactInGroupTr">
						<td class="contactInGroupTd">
							<input id="contactId" type="hidden" value=""/>
							<span id="nickname"></span>
						</td>
					</tr>
				</table>
			</div></td>
		</tr>
	</tbody>
</table>