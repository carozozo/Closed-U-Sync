<input type="hidden" id="title" value="<?php echo $_['title']; ?>" />
<input type="hidden" id="source" value="<?php echo $_['source']; ?>" />
<input type="hidden" id="mode" value="<?php echo $_['mode']; ?>" />
<div id="mediaPlayerDiv">
  <div id="loadingDiv">
    Loading the player...<img id="loadingImg" src="<?php echo OC_Helper::imagePath('core', 'loading.gif'); ?>"/>
  </div>
</div>