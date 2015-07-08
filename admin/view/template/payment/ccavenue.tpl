<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
      <button type="submit" form="form-latest" data-toggle="tooltip" onclick="$('#form').submit();" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
      
      <a onclick="location = '<?php echo $cancel; ?>';" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
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
     <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
     </div>
    <div class="panel-body">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_Merchant_Id; ?></td>
            <td><input type="text" name="ccavenue_Merchant_Id" value="<?php echo $ccavenue_Merchant_Id; ?>" />
              <?php if ($error_Merchant_Id) { ?>
              <span class="error"><?php echo $error_Merchant_Id; ?></span>
              <?php } ?></td>
          </tr>
		  		  
		  <tr>
            <td><span class="required">*</span> <?php echo $entry_action; ?></td>
            <td>
				<select name="ccavenue_action">
					<option value="https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction" <?php print ($ccavenue_action=="https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction"?"selected":""); ?>>Indian Account</option>
					<option value="https://world.ccavenue.com/servlet/ccw.CCAvenueController" <?php print ($ccavenue_action=="https://world.ccavenue.com/servlet/ccw.CCAvenueController"?"selected":""); ?>>World Account</option>
				</select>
				<?php if ($error_action) { ?>
					<span class="error"><?php echo $error_action; ?></span>
				<?php } ?>
			</td>
          </tr> 
		  
		  <tr>
            <td><span class="required">*</span> <?php echo $entry_total; ?></td>
            <td><input type="text" name="ccavenue_total" value="<?php echo $ccavenue_total; ?>" /><?php if ($error_total) { ?>
              <span class="error"><?php echo $error_total; ?></span>
              <?php } ?></td>
          </tr>
		  <tr>
            <td><span class="required">*</span> <?php echo $entry_workingkey; ?></td>
            <td><input type="text" name="ccavenue_workingkey" value="<?php echo $ccavenue_workingkey; ?>" /><?php if ($error_workingkey) { ?>
              <span class="error"><?php echo $error_workingkey; ?></span>
              <?php } ?></td>
          </tr>

			 <tr>
            <td><span class="required">*</span> <?php echo $entry_access_code; ?></td>
            <td><input type="text" name="ccavenue_access_code" value="<?php echo $ccavenue_access_code; ?>" /><?php if ($error_access_code) { ?>
              <span class="error"><?php echo $error_access_code; ?></span>
              <?php } ?></td>
          </tr>  
          
          <tr>
            <td><?php echo $entry_completed_status; ?></td>
            <td><select name="ccavenue_completed_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $ccavenue_completed_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          
          <tr>
            <td><?php echo $entry_failed_status; ?></td>
            <td><select name="ccavenue_failed_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $ccavenue_failed_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
			</td>
          </tr>
          <tr>
            <td><?php echo $entry_pending_status; ?></td>
            <td><select name="ccavenue_pending_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $ccavenue_pending_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
			</td>
          </tr>
          
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="ccavenue_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $ccavenue_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
			</td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select name="ccavenue_status">
                <?php if ($ccavenue_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
			</td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="ccavenue_sort_order" value="<?php echo $ccavenue_sort_order; ?>" size="1" /></td>
          </tr>
		  <!--
		  <tr>
			<td colspan="2">Developed by: <a href="mailto:harpreet@truesol.net">Harpreet Singh</a></td>
		  </tr>
		  -->
        </table>
        </div>
      </form>
    </div>
  </div>
  </div>
</div>
<?php echo $footer; ?> 