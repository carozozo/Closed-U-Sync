<div id='emptyMessage'><?php echo $l -> t('The files public link you shared list here. You can manage them easily.'); ?></div>
<div id='publicShareListLayoutDiv'>
	<img id='sharedListLoaginImg' src='<?php echo OC::$WEBROOT; ?>/core/img/loading.gif' height='32' />
	<table id='publicShareListTable'>
		<thead>
			<tr id='publicShareTitleTr'>
				<th id='pathSortTh' class='publicShareTitleTd sortClass' alt='asc'>
				    <?php echo $l -> t('File Path'); ?>
				    <span id='pathSort' class='sortClass'></span>：
				</th>
				<th class='publicShareTitleTd'>
				    <?php echo $l -> t('URL'); ?>：
				</th>
				<th id='timeSortTh' class='publicShareTitleTd sortClass' alt='desc'>
				    <?php echo $l -> t('Deadline'); ?>
				    <span id='timeSort' class='sortClass'>▲</span>：
				</th>
				<th class='publicShareTitleTd'>
                    <?php echo $l -> t('Password'); ?>：
                </th>
				<th><!-- <input type='button' id='refreshPublicShareButton' alt='' value='<?php echo $l -> t('Refresh'); ?>'/> --></th>
			</tr>
		</thead>
		<tbody>
			<tr class='publicShareTr'>
				<td id='sourcePath'></td>
				<td id='shortUrl'></td>
				<!-- 這邊不能設定id，否則 jquery 的 datapicker UI 會發生錯誤 -->
				<td id='deadline'></td>
				<td id='pwd'></td>
				<td>
		  		  <input type='button' id='updateBtn' value='<?php echo $l -> t('Update'); ?>'/>
                  <input type='button' id='cancelBtn' value='<?php echo $l -> t('Unshare'); ?>'/>
				</td>
			</tr>
		</tbody>
	</table>
</div>
