<?php
if (!isset($_)) {//also provide standalone error page
	require_once '../../lib/base.php';
	$tmpl = new OC_Template('', '404', 'guest');
	$tmpl -> printPage();
	exit ;
}
?>
<!-- <meta http-equiv="refresh" content="5; URL=http://<?php echo $_SERVER['HTTP_HOST']; ?>" /> -->
<ul>
	<li class='error'>
		<?php echo OC_Helper::siteTitle() . $l -> t('error message') . '： ' . $l -> t('Cloud not found'); ?><br/>
		<p class='hint'><?php if(isset($_['file'])) echo $_['file']?></p>
	</li>
</ul>
