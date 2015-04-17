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

	<body id="<?php echo $_['bodyid']; ?>">
		<header>
			<div id="header">
				<?php
                if (file_exists(OC::$SERVERROOT . '/core/templates/layout.user_header.php')) {
                    require_once (OC::$SERVERROOT . '/core/templates/layout.user_header.php');
                }
				?>
				<!--
				<form class="searchbox" action="#" method="post">
					<input id="searchbox" class="svg" type="search" name="query" value="<?php
					if ( isset($_POST['query']) ) {echo $_POST['query'];
					};
				?>" autocomplete="off" />
				</form>
				-->
				<div id="header_right">
					<div id="userInfoDiv">
						<div id="userIdDiv">
							<input type="hidden" id="userId" value="<?php echo $_['userId']; ?>"/>
							<input type="hidden" id="userEmail" value="<?php echo $_['userEmail']; ?>"/>
							<input type="hidden" id="userNickname" value="<?php echo $_['userNickname']; ?>"/>
							[ Hi <?php echo $_['userEmail']; ?> ]
						</div>
					</div>
					<div id="logoutDiv">
						<a id="logout" href="<?php echo link_to('', 'index.php'); ?>?logout=true">
							<img class="svg" title="<?php echo $l -> t('Log out'); ?>" src="<?php echo image_path('', 'actions/logout.svg'); ?>" align="top"/><br/>
							<?php echo $l -> t('Log out'); ?>
						</a>
					</div>
				</div>
			</div>
		</header>
		<nav><div id="navigation">
			<ul id="apps" class="svg">
			<?php
			foreach($_['navigation'] as $inClass => $entries){
			    # 找出左邊選單分類的屬性
			    $entryClass = (isset($_['navigationClass'][$inClass])) ? $_['navigationClass'][$inClass] : null;
                # 有找到分類屬性的話，則設置分類標題
                if($entryClass){
            ?>
            <li id="navigationClass_<?php echo $entryClass['name']; ?>" class="navigationClassTitle" style="cursor: default"><?php echo $l -> t($entryClass['name']); ?></li>
            <?php
            }
                # 分類底下的每個選單項目
                foreach($entries as $index => $entry){
            ?>
                <li>
                    <a style="background-image:url(<?php echo $entry['icon']; ?>)" href="<?php echo $entry['href']; ?>" title="" <?php if( $entry['active'] ): ?> class="active"<?php endif; ?> target="<?php echo ($entry['target'])?$entry['target']:'_self';?>" >
                    <?php echo $entry['name']; ?>
                    </a>
                </li>
            <?php
                }
            }
			?>
			</ul>

			<ul id="settings" class="svg">
				<img id="expand" class="svg" alt="<?php echo $l -> t('Settings'); ?>" src="<?php echo image_path('', 'actions/settings.svg'); ?>" />
				<span style="display:none"><?php echo $l -> t('Settings'); ?></span>
				<div id="expanddiv">
				<?php foreach($_['settingsnavigation'] as $entry):?>
					<li>
					  <a style="background-image:url(<?php echo $entry['icon']; ?>)" href="<?php echo $entry['href']; ?>" title="" <?php if( $entry["active"] ): ?> class="active"<?php endif; ?> target="<?php echo ($entry['target'])?$entry['target']:'_self';?>" >
					    <?php echo $entry['name'] ?>
					  </a>
					</li>
				<?php endforeach; ?>
				</div>
			</ul>
		</div></nav>

		<div id="content">
			<?php echo $_['content']; ?>
		</div>
	</body>
</html>
