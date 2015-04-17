<div id='menuTitleDiv'>
  <?php echo $l -> t('Copy source'); ?>：
  <span id="filesSpan"> </span>
  <br/>
  <?php echo $l -> t('Please cholice the destiration folder'); ?>：
  <span id='destSpan'></span>
  <input type='button' id='copyBtn' alt='' value=' OK '/>
  <img src='<?php echo OC::$WEBROOT; ?>/core/img/loading.gif' id='loadingImg'/>
  <span id='msgSpan'></span>
</div>
<div id="menuDiv">
  <input id='dir' type='hidden' value="<?php echo $_['dir']; ?>" />
  <input id='files' type='hidden' value="<?php echo $_['files']; ?>" />
  <table class="dirListTable">
    <tr class="parentTr">
      <td>
      </td>
    </tr>
    <tr class="dirPathTr">
      <td class="dirPathTd">
        <!-- 資料夾遮罩名稱 -->
        <span class="markNameSpan"></span>
        <!-- 如果資料夾底下還有資料夾，則顯示[>] -->
        <span class="arrowSpan"></span>
      </td>
    </tr>
  </table>
</div>