<?php echo $header; ?>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
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
     <?php if ($thumb || $description) { ?>
	   <div class="row"> 
		  <div class="col-sm-3">
			 <div class="transition">   
				 <?php if ($thumb) { ?>
						<div class="image"> <img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" title="<?php echo $heading_title; ?>" class="img-thumbnail" /></div>
				   <?php } ?>
			   <div>
			 </div>
		  </div>
	   </div>
      <div class="col-sm-9"> 
       <h2><?php echo $heading_title; ?></h2>     
        <?php if ($description) { ?>
        <p> <span> <?php echo $description; ?> </span></p>
        <?php } ?>
        </div>
        </div>
      <?php } ?>
      <?php if ($blogs) { ?>      
      <div class="row">
      <div class="col-md-4">
          <div class="btn-group hidden-xs">
            <button type="button" id="list-view" class="btn btn-default" data-toggle="tooltip" title="<?php echo $button_list; ?>"><i class="fa fa-th-list"></i></button>
            <button type="button" id="grid-view" class="btn btn-default" data-toggle="tooltip" title="<?php echo $button_grid; ?>"><i class="fa fa-th"></i></button>
          </div>
        </div>
        <div class="col-sm-2 text-right">
          <label class="control-label" for="input-sort"><?php echo $text_sort; ?></label>
        </div>
        <div class="col-sm-3 text-right">
          <select id="input-sort" class="form-control col-sm-3" onchange="location = this.value;">
            <?php foreach ($sorts as $sorts) { ?>
            <?php if ($sorts['value'] == $sort . '-' . $order) { ?>
            <option value="<?php echo $sorts['href']; ?>" selected="selected"><?php echo $sorts['text']; ?></option>
            <?php } else { ?>
            <option value="<?php echo $sorts['href']; ?>"><?php echo $sorts['text']; ?></option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
        <div class="col-sm-1 text-right">
          <label class="control-label" for="input-limit"><?php echo $text_limit; ?></label>
        </div>
        <div class="col-sm-2 text-right">
          <select id="input-limit" class="form-control" onchange="location = this.value;">
            <?php foreach ($limits as $limits) { ?>
            <?php if ($limits['value'] == $limit) { ?>
            <option value="<?php echo $limits['href']; ?>" selected="selected"><?php echo $limits['text']; ?></option>
            <?php } else { ?>
            <option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
      <br />  
	<div class="row">
		<?php foreach ($blogs as $blog) { ?>
			 <div class="product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12">
				 <div class="product-thumb">
					 <div class="image">
					   <a href="<?php echo $blog['href']; ?>"><img class="img-thumbnail" title="<?php echo $blog['title']; ?>" alt="<?php echo $blog['title']; ?>" src="<?php echo $blog['image']; ?>"></a>
					 </div>
					 <div>
						 <div class="caption">
						 <h4><a href="<?php echo $blog['href']; ?>"><?php echo $blog['title'];?></a></h4>
						 <p> <?php echo $blog['description'];?><a href="<?php echo $blog['href']; ?>"><?php echo $text_read;?></a></p>
						 <div class="alright"> <?php echo $blog['date_added'];?></div>
						 </div>
					 </div>
				 </div>
			 </div>    
		 <?php } ?>  
	</div>
	<div class="row">
        <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
        <div class="col-sm-6 text-right"><?php echo $results; ?></div>
      </div>
      <?php } else { ?>
      <p><?php echo $text_empty; ?></p>
      <div class="buttons">
        <div class="pull-right"><a href="<?php echo $continue; ?>" class="btn btn-primary"><?php echo $button_continue; ?></a></div>
      </div>
      <?php } ?>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?> 