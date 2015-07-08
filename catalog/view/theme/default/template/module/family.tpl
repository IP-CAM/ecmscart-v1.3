<div class="list-group">
  <?php foreach ($families as $family) { ?>
  <?php if ($family['family_id'] == $family_id) { ?>
  <a href="<?php echo $family['href']; ?>" class="list-group-item active"><?php echo $family['name']; ?></a>
  <?php if ($family['children']) { ?>
  <?php foreach ($family['children'] as $child) { ?> 
  <a href="<?php echo $child['href']; ?>" class="list-group-item">&nbsp;&nbsp;&nbsp;- <?php echo $child['name']; ?></a>  
  <?php } ?>
  <?php } ?>
  <?php } else { ?>
  <a href="<?php echo $family['href']; ?>" class="list-group-item"><?php echo $family['name']; ?></a>
  <?php } ?>
  <?php } ?>
</div>
