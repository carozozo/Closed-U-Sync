<table id="uChanneMainTable">
	<tr>
		<td id="uChanneListLayoutTd">
		  <table id="uChannelListTable">
		    <?php foreach($_['channelList'] as $index => $channel){
		    ?>
		    <tr>
		      <td><span class="channelSpan" alt="<?php echo $channel['url']; ?>"><?php echo $channel['name']; ?></span></td>
		    </tr>
		    <?php } ?>
		  </table>
		</td>
		<td id="uChannePlayerLayoutTd">
			<table id="uChannelPlayerLayoutTable">
				<tr>
					<td>
						<div id="uChannelPlayerDiv"></div>		
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>