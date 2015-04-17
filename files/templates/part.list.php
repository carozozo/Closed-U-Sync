<?php foreach($_['files'] as $file):
    # 簡單化的檔案大小
    $simpleFileSize = simple_file_size($file -> size);
    # the bigger the file, the darker the shade of grey; megabytes*2
    $simpleSizeColor = intval(200 - $file -> size / (1024 * 1024) * 2);
    if ($simpleSizeColor < 0)
        $simpleSizeColor = 0;
    # 取得最近的修改時間(和今天比較)
    $relativeModifiedDate = relative_modified_date($file -> mtime);
    # the older the file, the brighter the shade of grey; days*14
    $relativeDateColor = round((time() - ($file -> mtime)) / 60 / 60 / 24 * 14);
    if ($relativeDateColor > 200)
        $relativeDateColor = 200;
?>
<tr data-file="<?php echo $file -> encodeName; ?>" data-type="<?php echo $file -> type; ?>" data-mime="<?php echo $file -> mime; ?>" data-size='<?php echo $file -> size; ?>' data-date='<?php echo $file -> mtimeHuman; ?>'  data-read='<?php echo $file -> readable; ?>' data-write='<?php echo $file -> writeable; ?>'>
  <td class="checkboxClass">
  <input type="checkbox" />
  </td>
  <td class="filename svg">
      <a class="name" href="<?php echo $file -> fileUrl; ?>" title="">
          <img class='fileImg' src='<?php	echo $file -> imgSrc; ?>' width='32' />
          <span class="nametext">
            <?php if($file -> type == 'dir'): ?>
            <?php echo htmlspecialchars($file -> markName); ?>
            <?php else: ?>
            <?php echo htmlspecialchars($file -> filename); ?>
            <span class='extention'><?php echo $file -> extension; ?></span>
            <?php endif; ?>
          </span>
       </a>
    </td>
    <td class="filesize" title="<?php echo $file -> sizeHuman; ?>" style="color:rgb(<?php echo $simpleSizeColor.','.$simpleSizeColor.','.$simpleSizeColor ?>)">
        <?php echo $simpleFileSize; ?>
    </td>
    <td class="date">
        <span class="modified" title="<?php echo $file -> mtimeHuman; ?>" style="color:rgb(<?php echo $relativeDateColor.','.$relativeDateColor.','.$relativeDateColor ?>)">
            <?php echo $relativeModifiedDate; ?>
        </span>
    </td>
</tr>
<?php endforeach; ?>
