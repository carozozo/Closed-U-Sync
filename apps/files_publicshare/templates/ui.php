<div id='publicShareDiv'>
    <input type='hidden' id='nickname' value='<?php echo $_['nickname']; ?>'/>
    <input type='hidden' id='token' value='<?php echo $_['property'] -> token; ?>'/>
    <input type='hidden' id='shareLimitDays' value='<?php echo $_['property'] -> shareLimitDays; ?>'/>
    <table id='publicShareTable'>
      <tr id='publicShareTitleTr'>
        <td colspan="2">
          <?php echo $l -> t('Share File'); ?> ：
          <span id='sourcePathSpan'><?php echo $_['property'] -> sourcePath; ?></span>
        </td>
      </tr>
      <tr>
        <td>
            <?php echo $l -> t('Deadline'); ?>：
        </td>
        <td>
          <input type='text' id='deadlineInp' value='<?php echo $_['property'] -> deadlineLocal; ?>'/>
        </td>
      </tr>
      <tr>
        <td>
          <?php echo $l -> t('URL'); ?>：
        </td>
        <td>
            <span id='linkSpan'><?php echo $_['property'] -> shortUrl; ?></span>
        </td>
      </tr>
      <tr>
          <td>
            <a href='#' id='advanceLin'><?php echo $l -> t('Advanced options'); ?></a>
          </td>
      </tr>
      <tr id='pwdTr'>
        <td>
          <?php echo $l -> t('Set password'); ?>：
        </td>
        <td>
          <input type='text' id='pwdInp' value='<?php echo $_['property'] -> pwd; ?>' maxlength='12'/>
          <input type='button' id='clearPwdBtn' value='<?php echo $l -> t('Clear'); ?>'/>
          (<?php echo $l -> t('Optional. User need to keyin passowrd if you set it'); ?>)
        </td>
      </tr>
      <tr>
        <td> &nbsp; </td>
        <td>
          <input type='button' id='updateBtn' value='<?php echo $l -> t('Update'); ?>'/>
          <input type='button' id='emailBtn' value='<?php echo $l -> t('Share by email'); ?>'/>
          <span id='messSpan'></span>
        </td>
      </tr>
    </table>
</div>