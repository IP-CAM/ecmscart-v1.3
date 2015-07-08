<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box" >
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /><?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
    </div>
    <div class="content">
		<div>
			<a href="https://www.bluezeal.in/" target="_blank">
			<img src="view/image/payment/logob.png" alt="Bluezeal Logo" />
			Ccavenue Payment Gateway developed by Bluezeal.in
			</a>
		</div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
         <tr> 
		  <td><?php echo $entry_status; ?></td>
          <td>
			  <select name="ccavenuepay_status"><?php if ($ccavenuepay_status) { ?>
				  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
				  <option value="0"><?php echo $text_disabled;?></option>
				  <?php } else { ?>
				  <option value="1"><?php echo $text_enabled; ?></option>
				  <option value="0" selected="selected"><?php echo $text_disabled; ?></option><?php } ?>
			  </select>
		  </td>
		  </tr>
		 <tr>
			  <td>
				<span class="required">*</span> <?php echo $entry_merchant_id; ?>
			  </td> 
			  <td>
				  <input type="text" name="ccavenuepay_merchant_id" value="<?php echo $ccavenuepay_merchant_id; ?>" /> 
				  <?php if ($error_merchant_id) { ?>
				  <span class="error"><?php echo $error_merchant_id; ?></span><?php } ?>
			  </td>        
		  </tr>
		   <tr>          
			  <td>
				<span class="required">*</span> <?php echo $entry_working_key; ?>
			  </td>          
			  <td>
				  <input type="text" name="ccavenuepay_working_key" value="<?php echo $ccavenuepay_working_key; ?>" />
				  <?php if ($error_working_key) { ?>
				  <span class="error"><?php echo $error_working_key; ?></span><?php } ?>
			  </td>        
		  </tr>	
		  <tr>          
			  <td>
				<?php echo $entry_payment_confirmation_mail; ?>
			  </td>          
			  <td >
				  <?php if ($ccavenuepay_payment_confirmation_mail) { ?>
				  <input type="radio" name="ccavenuepay_payment_confirmation_mail" value="1" checked="checked" />
				  <?php echo $text_yes; ?>            
				  <input type="radio" name="ccavenuepay_payment_confirmation_mail" value="0" />
				  <?php echo $text_no; ?>            
				  <?php } else { ?>            
				  <input type="radio" name="ccavenuepay_payment_confirmation_mail" value="1" />
				  <?php echo $text_yes; ?>            
				  <input type="radio" name="ccavenuepay_payment_confirmation_mail" value="0" checked="checked" />
				  <?php echo $text_no; ?><?php } ?>
			  </td>        
		  </tr>
		  <tr> 
			  <td><?php echo $entry_geo_zone; ?>
			  </td>          
			  <td>
				  <select name="ccavenuepay_geo_zone_id">
					  <option value="0"><?php echo $text_all_zones; ?></option> 
						  <?php foreach ($geo_zones as $geo_zone) { ?>              
						  <?php if ($geo_zone['geo_zone_id'] == $ccavenuepay_geo_zone_id) { ?>
						  <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?>
					  </option>              
					  <?php } else { ?>              
					  <option value="<?php echo $geo_zone['geo_zone_id']; ?>">
					  <?php echo $geo_zone['name']; ?></option> 
					  <?php } ?><?php } ?> 
				  </select>
			  </td>        
		</tr>              
		<tr>          
			<td><?php echo $entry_sort_order; ?></td>          
			<td><input type="text" name="ccavenuepay_sort_order" value="<?php echo $ccavenuepay_sort_order; ?>" size="1" /></td>        
		</tr>	
		<tr>            
		<td><?php echo $entry_total; ?></td>
		<td><input type="text" name="pp_standard_total" value="<?php echo $pp_standard_total; ?>" /></td>
		</tr> 
         <tr>
            <td><?php echo $entry_completed_status; ?></td>
            <td><select name="ccavenuepay_completed_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $ccavenuepay_completed_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_failed_status; ?></td>
            <td><select name="ccavenuepay_failed_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $ccavenuepay_failed_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_pending_status; ?></td>
            <td><select name="ccavenuepay_pending_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $ccavenuepay_pending_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?> 