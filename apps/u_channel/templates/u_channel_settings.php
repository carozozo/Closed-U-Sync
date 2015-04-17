<form id="uChannelSettingsForm" action="#" method="post">
  <fieldset class="personalblock">
    <legend>
      <strong>U Channel Settings</strong>
    </legend>
    <table>
      <?php	foreach ($_['configs'] as $key => $config) {
      ?>
      <tr>
        <td><?php echo $l -> t($config['configkey']); ?>:
        </td>
        <td>
        <input type="text" id="<?php echo $config['configkey']; ?>" name="<?php echo $config['configkey']; ?>" value="<?php echo $config['configvalue']; ?>" alt="<?php echo $config['configvalue']; ?>" />
        </td>
      </tr>
      <?php	} ?>
      <tr>
        <td>&nbsp;</td>
        <td>
        <input type="button" id="updateBtn" value="Update"/>
        <input type="button" id="revertBtn" value="Revert All"/>
        <span id="settingsMess"></span></td>
      </tr>
    </table>
  </fieldset>
</form>
