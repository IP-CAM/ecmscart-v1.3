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
      <h2><?php echo $heading_title; ?></h2>
  <div class="row">       
        <h3><?php echo $blog['title'];?></h3>
        <div class="col-sm-3"><img src="<?php echo $blog['image']; ?>" alt="<?php echo $blog['title']; ?>" title="<?php echo $blog['title']; ?>" class="img-thumbnail" /></div>        <div class="col-sm-9"><?php echo $blog['description']; ?>
        <a href="<?php echo $blog['author_href']; ?>"><?php echo $blog['name'];?></a>
        <div class="fright"><?php echo $blog['date_added'];?> </div>
        </div>       
        </div>
	   <?php if ($comment_status) { ?>
            <div id="commsent"><?php echo $text_comments; ?></div>
              <form class="form-horizontal">
                <div id="comment"></div>
                <h2><?php echo $text_write; ?></h2>
                <?php if ($comment_guest) { ?>
                <div class="form-group required">
                  <div class="col-sm-12">
                    <label class="control-label" for="input-commenter"><?php echo $entry_commenter; ?></label>
                    <input type="text" name="commenter" value="" id="input-commenter" class="form-control" />
                  </div>
                </div>
                <div class="form-group required">
                  <div class="col-sm-12">
                    <label class="control-label" for="input-comment"><?php echo $entry_comment; ?></label>
                    <textarea name="text" rows="5" id="input-comment" class="form-control"></textarea>
                    <div class="help-block"><?php echo $text_note; ?></div>
                  </div>
                </div>
                <div class="form-group required">
                  <div class="col-sm-12">
                    <label class="control-label" for="input-captcha"><?php echo $entry_captcha; ?></label>
                    <input type="text" name="captcha" value="" id="input-captcha" class="form-control" />
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"> <img src="index.php?route=tool/captcha" alt="" id="captcha" /> </div>
                </div>
                <div class="buttons">
                  <div class="pull-right">
                    <button type="button" id="button-comment" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary"><?php echo $button_submit; ?></button>
                  </div>
                </div>
                <?php } else { ?>
                <?php echo $text_login; ?>
                <?php } ?>
              </form>
            <?php } ?>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>

<script type="text/javascript"><!--
$('#comment').delegate('.pagination a', 'click', function(e) {
  e.preventDefault();

    $('#comment').fadeOut('slow');

    $('#comment').load(this.href);

    $('#comment').fadeIn('slow');
});

$('#comment').load('index.php?route=blog/blog/comment&blog_id=<?php echo $blog_id; ?>');

$('#button-comment').on('click', function() {
	$.ajax({
		url: 'index.php?route=blog/blog/write&blog_id=<?php echo $blog_id; ?>',
		type: 'post',
		dataType: 'json',
		data: 'commenter=' + encodeURIComponent($('input[name=\'commenter\']').val()) + '&text=' + encodeURIComponent($('textarea[name=\'text\']').val()) + '&captcha=' + encodeURIComponent($('input[name=\'captcha\']').val()),
		beforeSend: function() {
			$('#button-comment').button('loading');
		},
		complete: function() {
			$('#button-comment').button('reset');
			$('#captcha').attr('src', 'index.php?route=tool/captcha#'+new Date().getTime());
			$('input[name=\'captcha\']').val('');
		},
		success: function(json) {
			$('.alert-success, .alert-danger').remove();
			
			if (json['error']) {
				$('#comment').after('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '</div>');
			}
			
			if (json['success']) {
				$('#comment').after('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '</div>');
				
				$('input[name=\'commenter\']').val('');
				$('textarea[name=\'text\']').val('');
				$('input[name=\'captcha\']').val('');
			}
		}
	});
});
//--></script>
<?php echo $footer; ?>
