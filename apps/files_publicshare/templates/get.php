<table id='getMainTable'>
  <!-- 如果要秀廣告頁 -->
  <?php if(OC_PublicShare_Config::adEnabled()){
  ?>
  <tr id='adTr'>
    <td id='adTd'><iframe id='adIframe' src="<?php echo $SERVERROOT; ?>/apps/files_publicshare/ad/index.html" scrolling="no"></iframe></td>
  </tr>
  <?php } ?>
  <tr>
    <td>
    <input type="hidden" id='token' value="<?php echo $_['token']; ?>" />
    <table id='fileListTable'>
      <tr id='pathTr'>
        <td id='pathTd' class='fileListTd'><?php echo $l -> t('Location'); ?>：
        <span id='locationSpan'></span></td>
        <td>&nbsp;</td>
        <td class='fileListTd'>
        <div class='btnDiv'>
          <input type='button' id='topBtn' value='<?php echo $l -> t('Top'); ?>'/>
        </div></td>
      </tr>
      <tr id='titleTr'>
          <td class='fileListTd'><?php echo $l -> t('File Name'); ?></td>
        <td class='fileListTd'><?php echo $l -> t('File Size'); ?></td>
        <td class='fileListTd'>&nbsp;</td>
      </tr>
      <tr class='fileListTr'>
        <td class='fileNameTd fileListTd'><span class='fileNameSpan'></span></td>
        <td class='fileSizeTd fileListTd'><span class='fileSizeSpan'></span></td>
        <td class='fileListTd'>
        <div class='btnDiv'>
          <input type='button' class='openBtn' value='<?php echo $l -> t('Open'); ?>'/>
          <input type='button' class='downloadBtn' value='<?php echo $l -> t('Download'); ?>'/>
        </div>
        <div class="downloadDiv">
          <iframe class="downloadFrame" src=""></iframe>
        </div></td>
      </tr>
    </table>
    <div id='messDiv'></div>
    <div id='pwdDiv'>
      <input type="text" id='pwdInp' />
      <input type="button" id='pwdBtn' value='OK'/>
    </div>
    </td>
  </tr>
</table>
