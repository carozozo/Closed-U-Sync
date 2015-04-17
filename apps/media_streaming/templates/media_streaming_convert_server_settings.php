<form id="mediaConvertServerSettingsForm" action="#" method="post">
  <fieldset class="personalblock">
    <legend>
      <strong>
      	Convert Server Settings
      	<input type="button" id="refreshBtn" value="Refresh"/>
      	<input type="button" id="getListBtn" value="ShowConvertList"/>
      </strong>
    </legend>
    <img id="loadingImg" src="<?php echo OC::$WEBROOT; ?>/core/img/loading.gif" height="32" />
    <table id="mediaConvertServerTable">
    	<!-- 此為範本tr -->
    	<tr class="convertServerTr">
    		<td>IP:<span class="serverIpSpan"></span></td>
    		<td>PID:<span class="pidSpan"></span></td>
    		<td>Start Time:<span class="startTimeSpan"></span></td>
    		<td>Status:<span class="statusSpan"></span></td>
    		<td>
        	<input type="button" class="defaultBtn" value="Set Default" alt=""/>
        	<input type="button" class="delBtn" value="Delete" alt=""/>
        </td>
      </tr>
      <tr>
      	<td colspan="5">
      		<input type="text" id="newInp" />
      		<input type="button" id="newBtn" value="New Convert Server"/>
      	</td>
      </tr>
    </table>
  </fieldset>
</form>
