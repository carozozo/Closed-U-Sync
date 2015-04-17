<!--[if IE 8]><style>input[type="checkbox"]{padding:0;}table td{position:static !important;}</style><![endif]-->
<div id="controls">
	<?php echo($_['breadcrumb']); ?>
	<?php if ($_['is_writeable']):?>
		<div class="actions">
			<div id='new' class='button'>
				<a>
					<?php echo $l -> t('New'); ?>
				</a>
				<ul class="popup popupTop">
					<li data-type='text-file'><p><img src="<?php echo mimetype_icon('text/plain') ?>" width="32"/><?php echo $l -> t('Text file'); ?></p></li>
					<li data-type='folder'><p><img src="<?php echo mimetype_icon('dir') ?>" width="32"/><?php echo $l -> t('Folder'); ?></p></li>
					<!-- <li style="background-image:url('<?php echo mimetype_icon('dir') ?>')" data-type='web'><p><?php echo $l->t('From the web');?></p></li> -->
				</ul>
			</div>
			<!-- 整體上傳Div -->
			<div class="file_upload_wrapper svg">
				<form data-upload-id='1' class="file_upload_form" action="ajax/upload.php" method="post" enctype="multipart/form-data" target="file_upload_target_1">
					<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $_['uploadMaxFilesize'] ?>" id="max_upload">
					<input type="hidden" class="max_human_file_size" value="(max <?php echo $_['uploadMaxHumanFilesize']; ?>)">
					<input type="hidden" name="dir" value="<?php echo $_['dir'] ?>" id="dir">
					<!-- 顯示用的上傳按鈕 -->
					<button class="file_upload_filename"><a><?php echo $l -> t('Upload'); ?></a></button>
					<!-- 偵測上傳進度用的變數(name:APC_UPLOAD_PROGRESS 不可改) ,$uniqId為隨機碼(用來對應要讀取的上傳序列)-->
          <input type="hidden" name="APC_UPLOAD_PROGRESS" id="progress_key"  value="<?php //echo $uniqId?>"/>
          <!-- 真正的上傳按鈕(透明度設為0) -->
					<input class="file_upload_start" type="file" name='files[]'/>
					<!-- 顯示上限限制的提示 -->
					<a href="#" class="file_upload_button_wrapper" onclick="return false;" title="<?php echo $l->t('Upload'); echo  ' max. '.$_['uploadMaxHumanFilesize'] ?>"></a>
					<!-- 開始上傳時的執行頁面(這樣子就不會變成另開視窗)-->
					<iframe name="file_upload_target_1" class='file_upload_target' src=""></iframe>
				</form>
			</div>
		</div>
		<div id="file_action_panel"></div>
	<?php else: ?>
		<input type="hidden" name="dir" value="<?php echo $_['dir'] ?>" id="dir">
	<?php endif; ?>

</div>
<div id='notification'></div>
<div class='selectedActions'></div>
<?php if (isset($_['files']) && count($_['files'])==0 && $_['is_readable'] ){?>
	<div id="emptyfolder"><?php echo $l->t('Nothing in here. Upload something!')?></div>
<?php }else if (isset($_['files']) && count($_['files'])==0){ ?>
	<div id="emptyfolder"><?php echo $l->t('Nothing in here.')?></div>
<?php } ?>
<table id="fileListTable">
	<thead>
		<tr>
		  <th width="20">
		    <?php if($_['is_readable']) { ?><input type="checkbox" id="select_all" /><?php } ?>
		  </th>
			<th id='headerName'>
				<span class='name'><?php echo $l -> t('Name'); ?></span>
				<!-- <div class='selectedActions'></div> -->
			</th>
			<th id="headerSize"><?php echo $l -> t('Size'); ?></th>
			<th id="headerDate">
			  <span id="modified"><?php echo $l -> t('Modified'); ?></span>
			  <!-- <span class="selectedActions"></span> -->
			 </th>
		</tr>
	</thead>
	<tbody id="fileList" data-read="<?php echo !$_['is_readable']; ?>" data-write="<?php echo !$_['is_writeable']; ?>">
		<?php echo($_['fileList']); ?>
	</tbody>
</table>
<div id="editor"></div>
<div id="uploadsize-message" title="<?php echo $l -> t('Upload too large'); ?>">
	<p>
		<?php echo $l -> t('The files you are trying to upload exceed the maximum size for file uploads on this server.'); ?>
	</p>
</div>

<!-- config hints for javascript -->
<input type="hidden" name="allowZipDownload" id="allowZipDownload" value="<?php echo $_['allowZipDownload']; ?>" />

<!-- 上傳表單，但裡面並未包含檔案(則可中斷上一次的上傳) -->
<form class="file_cancel_form" action="ajax/upload.php" method="post" enctype="multipart/form-data" target="file_upload_target_1">
</form>