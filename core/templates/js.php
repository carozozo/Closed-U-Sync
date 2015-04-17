<script type="text/javascript">
	var oc_webroot = '<?php echo OC::$WEBROOT; ?>';
	var oc_current_user = '<?php echo OC_User::getUser() ?>';
	var oc_language = '<?php echo OC_L10N::findLanguage(); ?>';
	var oc_forbiddenCharArray = new Array();
	<?php
	foreach (OC_Filesystem::$forbiddenCharArray as $key => $value) {
	?>
			oc_forbiddenCharArray.push('\<?php echo $value; ?>');
	<?php
	}
	?>

</script>