<form action="<?php echo 'resetpassword.php?'.$_SERVER['QUERY_STRING']; ?>" method="post">
	<fieldset>
		<?php if($_['success']): ?>
			<h1><?php echo $l->t('密碼已重新設定'); ?></h1>
			<p><a href="<?php echo OC::$WEBROOT ?>/"><?php echo $l->t('至登入頁面'); ?></a></p>
		<?php else: ?>
			<p class="infield">
				<label for="password" class="infield"><?php echo $l->t( '新密碼' ); ?></label>
				<input type="password" name="password" id="password" value="" required />
			</p>
			<input type="submit" id="submit" value="<?php echo $l->t('確定'); ?>" />
		<?php endif; ?>
	</fieldset>
</form>
