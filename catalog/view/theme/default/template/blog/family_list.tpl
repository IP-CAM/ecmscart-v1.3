<?php echo $header; ?>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li> <a href="<?php echo $breadcrumb['href']; ?>"> <?php echo $breadcrumb['text']; ?> </a> </li>
    <?php } ?>
  </ul>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
      <h1><?php echo $heading_title; ?></h1>
	  <?php if ($families) { ?>
	  <div class="row">
	   	<?php foreach ($families as $family) { ?>
			<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            
				<?php if ($family['thumb']) { ?>
                 <div class="image">
					 <a href="<?php echo $family['href']; ?>"><img src="<?php echo $family['thumb']; ?>" alt="<?php echo $family['name']; ?>" title="<?php echo $family['name']; ?>" class="img-thumbnail" /></a>
                     </div>
				<?php } ?>
                <div>
                 <div class="caption">
					 <h4><a href="<?php echo $family['href']; ?>"><span><?php echo $family['name']; ?></span></a></h4>
                    </div></div>
			</div>
		<?php } ?>
	  </div>
      <?php } else { ?>
      <p><?php echo $text_empty; ?></p>
      <div class="buttons clearfix">
        <div class="pull-right"><a href="<?php echo $continue; ?>" class="btn btn-primary"><?php echo $button_continue; ?></a></div>
      </div>
      <?php } ?>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?>