<form action="index.php" method="post">
	<fieldset>
		<?php echo $l->t('帳號名稱'); ?>
		<?php if ($_['requested']): ?>
			<?php echo $l->t('系統已傳送密碼重設連結至您的信箱'); ?>
		<?php else: ?>
			<?php if ($_['error']): ?>
				<?php echo $l->t('錯誤!'); ?>
			<?php endif; ?>
			<p class="infield">
				<label for="user" class="infield"><?php echo $l->t( '帳號' ); ?></label>
				<input type="text" name="user" id="user" value="" autocomplete="off" required autofocus />
			</p>
			<input type="submit" id="submit" value="<?php echo $l->t('確定'); ?>" />
		<?php endif; ?>
	</fieldset>
</form>
