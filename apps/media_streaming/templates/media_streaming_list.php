<table id="mediaStreamingListTable">
  <!-- 此為範本tr -->
	<?php foreach($_['streamingList'] as $index => $streamingItem){ ?>
  <tr class="streamingTr">
    <td>
	    <table class="mediaStreamingTable">
	    	<tr>
	        <td><?php echo $index;?>.</td>
	        <td>&nbsp;</td>
	      </tr>
	      <tr>
	        <td>Insert Time:<?php echo $streamingItem['insert_time']; ?></td>
	        <td>Device Type:<?php echo $streamingItem['device_type']; ?></td>
	      </tr>
	      <tr>
	        <td>User Id:<?php echo $streamingItem['user_id']; ?></td>
	        <td>Output Name:<?php echo $streamingItem['output_name']; ?></td>
	      </tr>
	      <tr>
	        <td colspan="2">Source Path:<?php echo $streamingItem['source_path']; ?></td>
	      </tr>
	      <tr>
	      </tr>
	        <td>ServerIp:<?php echo $streamingItem['server_ip']; ?></td>
	        <td>PID:<?php echo $streamingItem['pid']; ?></td>
	      <tr>
	        <td>Start Time:<?php echo $streamingItem['start_time']; ?></td>
	        <td>Status:<?php echo $streamingItem['status']; ?></td>
	      </tr>
	    </table>
    </td>
  </tr>
	<?php } ?>
</table>