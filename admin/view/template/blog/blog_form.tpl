<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-blog" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_form; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-blog" class="form-horizontal">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
            <li><a href="#tab-data" data-toggle="tab"><?php echo $tab_data; ?></a></li>
            <li><a href="#tab-links" data-toggle="tab"><?php echo $tab_links; ?></a></li>
            <li><a href="#tab-design" data-toggle="tab"><?php echo $tab_design; ?></a></li> 
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-general">
              <ul class="nav nav-tabs" id="language">
                <?php foreach ($languages as $language) { ?>
                <li><a href="#language<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane" id="language<?php echo $language['language_id']; ?>">
                  <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-title<?php echo $language['language_id']; ?>"><?php echo $entry_title; ?></label>
                    <div class="col-sm-10">
                      <input type="text" name="blog_description[<?php echo $language['language_id']; ?>][title]" value="<?php echo isset($blog_description[$language['language_id']]) ? $blog_description[$language['language_id']]['title'] : ''; ?>" placeholder="<?php echo $entry_title; ?>" id="input-title<?php echo $language['language_id']; ?>" class="form-control" />
                      <?php if (isset($error_title[$language['language_id']])) { ?>
                      <div class="text-danger"><?php echo $error_title[$language['language_id']]; ?></div>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-description"><?php echo $entry_description; ?></label>
                    <div class="col-sm-10">
                      <textarea name="blog_description[<?php echo $language['language_id']; ?>][description]" placeholder="<?php echo $entry_description; ?>" id="input-description<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset($blog_description[$language['language_id']]) ? $blog_description[$language['language_id']]['description'] : ''; ?></textarea>
                      <?php if (isset($error_description[$language['language_id']])) { ?>
                      <div class="text-danger"><?php echo $error_description[$language['language_id']]; ?></div>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-meta-title<?php echo $language['language_id']; ?>"><?php echo $entry_meta_title; ?></label>
                    <div class="col-sm-10">
                      <input type="text" name="blog_description[<?php echo $language['language_id']; ?>][meta_title]" value="<?php echo isset($blog_description[$language['language_id']]) ? $blog_description[$language['language_id']]['meta_title'] : ''; ?>" placeholder="<?php echo $entry_meta_title; ?>" id="input-meta-title<?php echo $language['language_id']; ?>" class="form-control" />
                      <?php if (isset($error_meta_title[$language['language_id']])) { ?>
                      <div class="text-danger"><?php echo $error_meta_title[$language['language_id']]; ?></div>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-meta-description<?php echo $language['language_id']; ?>"><?php echo $entry_meta_description; ?></label>
                    <div class="col-sm-10">
                      <textarea name="blog_description[<?php echo $language['language_id']; ?>][meta_description]" rows="5" placeholder="<?php echo $entry_meta_description; ?>" id="input-meta-description<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset($blog_description[$language['language_id']]) ? $blog_description[$language['language_id']]['meta_description'] : ''; ?></textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-meta-keyword<?php echo $language['language_id']; ?>"><?php echo $entry_meta_keyword; ?></label>
                    <div class="col-sm-10">
                      <textarea name="blog_description[<?php echo $language['language_id']; ?>][meta_keyword]" rows="5" placeholder="<?php echo $entry_meta_keyword; ?>" id="input-meta-keyword<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset($blog_description[$language['language_id']]) ? $blog_description[$language['language_id']]['meta_keyword'] : ''; ?></textarea>
                    </div>
                  </div>
				  <div class="form-group">
					<label class="col-sm-2 control-label" for="input-keyword"><span data-toggle="tooltip" title="<?php echo $help_keyword; ?>"><?php echo $entry_keyword; ?></span></label>
					<div class="col-sm-10">					
                      <input type="text" name="blog_keyword[<?php echo $language['language_id']; ?>][keyword]" value="<?php echo isset($blog_keyword[$language['language_id']]) ? $blog_keyword[$language['language_id']]['keyword'] : ''; ?>" placeholder="<?php echo $entry_keyword; ?>" id="input-keyword<?php echo $language['language_id']; ?>" class="form-control" />
                      <?php if (isset($error_keyword[$language['language_id']])) { ?>
                      <div class="text-danger"><?php echo $error_keyword[$language['language_id']]; ?></div>
                      <?php } ?>
					</div>
				  </div>				  
                </div>
                <?php } ?>
              </div>
            </div>
            <div class="tab-pane" id="tab-data">
			  <div class="form-group">
                <label class="col-sm-2 control-label" for="input-keyword"><?php echo $entry_date_modified; ?></label>
                <div class="col-sm-10">
                  <?php echo $date_modified; ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_store; ?></label>
                <div class="col-sm-10">
                  <div class="well well-sm" style="height: 150px; overflow: auto;">
                    <div class="checkbox">
                      <label>
                        <?php if (in_array(0, $blog_store)) { ?>
                        <input type="checkbox" name="blog_store[]" value="0" checked="checked" />
                        <?php echo $text_default; ?>
                        <?php } else { ?>
                        <input type="checkbox" name="blog_store[]" value="0" />
                        <?php echo $text_default; ?>
                        <?php } ?>
                      </label>
                    </div>
                    <?php foreach ($stores as $store) { ?>
                    <div class="checkbox">
                      <label>
                        <?php if (in_array($store['store_id'], $blog_store)) { ?>
                        <input type="checkbox" name="blog_store[]" value="<?php echo $store['store_id']; ?>" checked="checked" />
                        <?php echo $store['name']; ?>
                        <?php } else { ?>
                        <input type="checkbox" name="blog_store[]" value="<?php echo $store['store_id']; ?>" />
                        <?php echo $store['name']; ?>
                        <?php } ?>
                      </label>
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
			  <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_image; ?></label>
                <div class="col-sm-10"><a href="" id="thumb-image" data-toggle="image" class="img-thumbnail"><img src="<?php echo $thumb; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a>
                  <input type="hidden" name="image" value="<?php echo $image; ?>" id="input-image" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-comments"><span data-toggle="tooltip" title="<?php echo $help_comments; ?>"><?php echo $entry_comments; ?></span></label>
                <div class="col-sm-10">
                  <div class="checkbox">
                    <label>
                      <?php if ($comments) { ?>
                      <input type="checkbox" name="comments" value="1" checked="checked" id="input-comments" />
                      <?php } else { ?>
                      <input type="checkbox" name="comments" value="1" id="input-comments" />
                      <?php } ?>
                      &nbsp; </label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="status" id="input-status" class="form-control">
                    <?php if ($status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="sort_order" value="<?php echo $sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-links">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-family"><?php echo $entry_family; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="family" value="" placeholder="<?php echo $text_family; ?>" id="input-family" class="form-control" />
                  <div id="blog-family" class="well well-sm" style="height: 150px; overflow: auto;">
                    <?php foreach ($blog_families as $blog_family) { ?>
                    <div id="blog_family<?php echo $blog_family['family_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $blog_family['name']; ?>
                      <input type="hidden" name="blog_family[]" value="<?php echo $blog_family['family_id']; ?>" />
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
			  <div class="form-group">
                <label class="col-sm-2 control-label" for="input-related"><?php echo $entry_related; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="related" value="" placeholder="<?php echo $text_related; ?>" id="input-related" class="form-control" />
                  <div id="blog-related" class="well well-sm" style="height: 150px; overflow: auto;">
                    <?php foreach ($blog_relateds as $blog_related) { ?>
                    <div id="blog_related<?php echo $blog_related['blog_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $blog_related['title']; ?>
                      <input type="hidden" name="blog_related[]" value="<?php echo $blog_related['blog_id']; ?>" />
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
			  <div class="form-group">
                <label class="col-sm-2 control-label" for="input-author"><?php echo $entry_author; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="author" value="<?php echo $author ?>" placeholder="<?php echo $text_author; ?>" id="input-author" class="form-control" />
                  <input type="hidden" name="author_id" value="<?php echo $author_id; ?>" />
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-design">
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <td class="text-left"><?php echo $entry_store; ?></td>
                      <td class="text-left"><?php echo $entry_layout; ?></td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="text-left"><?php echo $text_default; ?></td>
                      <td class="text-left"><select name="blog_layout[0]" class="form-control">
                          <option value=""></option>
                          <?php foreach ($layouts as $layout) { ?>
                          <?php if (isset($blog_layout[0]) && $blog_layout[0] == $layout['layout_id']) { ?>
                          <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                          <?php } ?>
                          <?php } ?>
                        </select></td>
                    </tr>
                    <?php foreach ($stores as $store) { ?>
                    <tr>
                      <td class="text-left"><?php echo $store['name']; ?></td>
                      <td class="text-left"><select name="blog_layout[<?php echo $store['store_id']; ?>]" class="form-control">
                          <option value=""></option>
                          <?php foreach ($layouts as $layout) { ?>
                          <?php if (isset($blog_layout[$store['store_id']]) && $blog_layout[$store['store_id']] == $layout['layout_id']) { ?>
                          <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                          <?php } ?>
                          <?php } ?>
                        </select></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
tinymce.init({
    selector: "#input-description<?php echo $language['language_id']; ?>",
	autoresize_min_height: 300,
    plugins: [
        "advlist autolink lists link image charmap print preview anchor ",
        "searchreplace visualblocks code fullscreen ",
        "insertdatetime media table contextmenu paste responsivefilemanager "
    ],
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | preview fullscreen | table ",
	external_filemanager_path:"<?php echo HTTP_FILE_MANAGER; ?>",
   	filemanager_title:"File Manager" ,
   	external_plugins: { "filemanager" : "<?php echo HTTP_FILE_MANAGER; ?>plugin.min.js" },
	relative_urls : false,
	remove_script_host : false,
	document_base_url: "<?php echo HTTP_FILE_MANAGER_SERVER; ?>"

});
<?php } ?>
//--></script>
<script type="text/javascript"><!--
// Author
$('input[name=\'author\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=blog/author/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',			
			success: function(json) {
				
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['author_id']
					}
				}));
			}
		});
	},
    'select': function(item) {
		$('input[name=\'author\']').val(item['label']);
		$('input[name=\'author_id\']').val(item['value']);
	}	
});

// Related
$('input[name=\'related\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=blog/blog/autocomplete&token=<?php echo $token; ?>&blog_id=<?php echo $blog_id; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',			
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['title'],
						value: item['blog_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'related\']').val('');
		
		$('#blog-related' + item['value']).remove();
		
		$('#blog-related').append('<div id="blog-related' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="blog_related[]" value="' + item['value'] + '" /></div>');	
	}	
});

$('#blog-related').delegate('.fa-minus-circle', 'click', function() {
	$(this).parent().remove();
});

// family
$('input[name=\'family\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=blog/family/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',			
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['family_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'family\']').val('');
		
		$('#blog-family' + item['value']).remove();
		
		$('#blog-family').append('<div id="blog-family' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="blog_family[]" value="' + item['value'] + '" /></div>');	
	}	
});

$('#blog-family').delegate('.fa-minus-circle', 'click', function() {
	$(this).parent().remove();
});
//--></script>
  <script type="text/javascript"><!--
$('#language a:first').tab('show');
//--></script></div>
<?php echo $column_right; ?>
<?php echo $footer; ?>