<?php /**
 * Copyright (c) 2011, Robin Appelman <icewind1991@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
$levels = array('Debug', 'Info', 'Warning', 'Error', 'Fatal');
?>

<div id="controls">
  <input type="button" id="clearLogBtn" value="Clear"/>
  <input type="button" id="refreshLogBtn" value="Refresh"/>
</div>
<table class="nostyle logTable">
  <?php //foreach($_['entries'] as $entry): ?>
  <tr>
    <td class='level'><?php // echo $levels[$entry -> level]; ?></td>
    <td class='app'><?php // echo $entry -> app; ?></td>
    <td class='message'><?php // echo $entry -> message; ?></td>
    <td class='time'><?php // echo $l -> l('datetime', $entry -> time); ?></td>
  </tr>
  <?php //endforeach; ?>
</table>