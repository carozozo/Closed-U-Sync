<table id="mediaConvertListTable">
  <!-- 此為範本tr -->
	<?php foreach($_['convertList'] as $index => $convertItem){ ?>
  <tr class="convertTr">
    <td>
	    <table class="mediaConvertTable">
	    	<tr>
	        <td><?php echo $index;?>.</td>
	        <td>&nbsp;</td>
	      </tr>
	      <tr>
	        <td>Insert Time:<?php echo $convertItem['insert_time']; ?></td>
	        <td>Device Type:<?php echo $convertItem['device_type']; ?></td>
	      </tr>
	      <tr>
	        <td>User Id:<?php echo $convertItem['user_id']; ?></td>
	        <td>Source Path:<?php echo $convertItem['source_path']; ?></td>
	      </tr>
	      <tr>
	      	<td>Target User Id:<?php echo $convertItem['target_user_id']; ?></td>
	        <td>Target Output Name:<?php echo $convertItem['target_output_name']; ?></td>
	      </tr>
	      <tr>
	        <td colspan="2">Target Source Path:<?php echo $convertItem['target_source_path']; ?></td>
	      </tr>
	      <tr>
	      </tr>
	        <td>ServerIp:<?php echo $convertItem['server_ip']; ?></td>
	        <td>PID:<?php echo $convertItem['pid']; ?></td>
	      <tr>
	        <td>Start Time:<?php echo $convertItem['start_time']; ?></td>
	        <td>Status:<?php echo $convertItem['status']; ?></td>
	      </tr>
	    </table>
    </td>
  </tr>
	<?php } ?>
</table>