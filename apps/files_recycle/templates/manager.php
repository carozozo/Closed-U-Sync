<div id="emptyMessage"><?php echo $l -> t('All files you deleted record here, you can revert them or remove completely.'); ?></div>
<div id="recycleListLayoutDiv">
	<img id="loaginImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
	<table id="recycleListTable">
	    <tr id="pathTr">
            <td id="pathTd" class="titleTd" colspan="4">
                <?php echo $l -> t('Location'); ?>：
                <span id="locationSpan"></span>
            </td>
            <td class='btnTd'>
                <input type="button" id="revSeltBtn" class="selectedBtn" value="<?php echo $l -> t('Revert Selected'); ?>"/>
                <input type="button" id="delSeltBtn" class="selectedBtn" value="<?php echo $l -> t('Delete Selected'); ?>"/>
                <input type="button" id="topBtn" value="<?php echo $l -> t('Top'); ?>"/>
                <input type="button" id="bakBtn" value="<?php echo $l -> t('Back'); ?>"/>
                <input type="button" id="cleanUpBtn" value="<?php echo $l -> t('Clean Up'); ?>"/>
             </td>
        </tr>
		<tr id="titleTr">
			<td class="titleTd">
				<input type="checkbox" id="selectAll" class="selectRecycle"/>
			</td>
			<td id='pathSortTh' class="titleTd sortClass" alt='asc'>
			    <?php echo $l -> t('File Path'); ?>
			    <span id='pathSort' class="sortClass"></span>：
			</td>
			<td id='timeSortTh' class="titleTd sortClass" alt='desc'>
			    <?php echo $l -> t('Recycle Time'); ?>
			    <span id='timeSort' class="sortClass">▲</span>：
			</td>
			<td id='sizeTh' class="titleTd">
                <?php echo $l -> t('File Size'); ?>：
            </td>
			<td>
			</td>
		</tr>
		<tr class="recycleTr">
			<td class="selectTd"><input type="checkbox" class="selectRecycle"/></td>
			<td class="filePathTd"></td>
			<td class="recycleTimeTd"></td>
			<td class="fileSizeTd"></td>
			<td class="btnTd">
			    <input type="button" class="openBtn" value="<?php echo $l -> t('Open'); ?>"/>
			    <input type="button" class="revertBtn" value="<?php echo $l -> t('Revert File'); ?>"/>
			    <input type="button" class="deleteBtn" value="<?php echo $l -> t('Delete'); ?>"/>
			</td>
		</tr>
	</table>
</div>