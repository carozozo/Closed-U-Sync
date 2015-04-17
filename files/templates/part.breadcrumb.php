<?php
$count=count($_["breadcrumb"]);
foreach($_["breadcrumb"] as $index => $crumb){
?>
<div class="crumb <?php echo ($index == $count - 1) ? 'last':'';?> svg" data-dir='<?php echo $crumb -> path; ?>' style='background-image:url("<?php echo image_path('core', 'breadcrumb.png'); ?>")'>
    <a href="<?php echo $crumb -> fileUrl; ?>"><?php echo htmlspecialchars($crumb -> markName); ?></a>
</div>
<?php } ?>