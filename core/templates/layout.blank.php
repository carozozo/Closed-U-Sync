<!DOCTYPE html>
<html>
	<head>
		<title><?php echo OC_Helper::siteTitle(); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="pragma" content="no-cache"><!-- IE可能不見得有效 -->
		<meta http-equiv="expries" content="0">
		<link rel="shortcut icon" href="<?php echo image_path('', 'favicon.png'); ?>" /><link rel="apple-touch-icon-precomposed" href="<?php echo image_path('', 'favicon-touch.png'); ?>" />
		<?php foreach($_['cssfiles'] as $cssfile): ?>
			<link rel="stylesheet" href="<?php echo $cssfile; ?>" type="text/css" media="screen" />
		<?php endforeach; ?>
		<?php
		require_once 'js.php';
		?>
		<?php foreach($_['jsfiles'] as $jsfile): ?>
			<script type="text/javascript" src="<?php echo $jsfile; ?>"></script>
		<?php endforeach; ?>
		<?php foreach($_['headers'] as $header): ?>
			<?php
			echo '<' . $header['tag'] . ' ';
			foreach ($header['attributes'] as $name => $value) {
				echo "$name='$value' ";
			};
			echo '/>';
			?>
		<?php endforeach; ?>
	</head>
	<body>
		<?php echo $_['content']; ?>
	</body>
</html>
