<a id="sharedTab" href="#"><?php echo $l -> t('Shared List'); ?></a>
<table id="sharedMainTable">
	<thead>
		<tr class="titleTr">
			<td>
				<input type="checkbox" id="selectAllShared">
				<?php echo $l -> t('select all'); ?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<div id="sharedListDiv">
					<img id="sharedListLoaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
					<table id="sharedListTable">
						<tr>
							<td>
								<table id="sharedGroupListTable">
									<tr class="titleClass">
										<td>
											<?php echo $l -> t('Group'); ?>:
										</td>
									</tr>
									<tr class="sharedGroupTr sharedTr">
										<td class="sharedGroupTd sharedTd">
											<input id="sharedGroupId" type="hidden" value=""/>
											<span id="sharedGroupName"></span>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<table id="sharedContactListTable">
									<tr class="titleClass">
										<td>
											<?php echo $l -> t('Contact'); ?>:
										</td>
									</tr>
									<tr class="sharedContactTr sharedTr">
										<td class="sharedContactTd sharedTd">
											<input id="sharedContactId" type="hidden" value=""/>
											<span id="sharedNickname"></span>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</tbody>
</table>