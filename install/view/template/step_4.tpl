<?php echo $header; ?>
<div class="container">
  <header>
      <div class="row">
        <div class="col-sm-6">
          <h1 class="pull-left">4<small>/4</small></h1>
          <h3><?php echo $heading_step_4; ?><br><small><?php echo $heading_step_4_small; ?></small></h3>
        </div>
        <div class="col-sm-6">
          <div id="logo" class="pull-right hidden-xs">
            <img src="view/image/logo.png" alt="eCmsCart" title="eCmsCart" />
          </div>
        </div>
      </div>
  </header>
  <?php if ($success) { ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="alert alert-danger"><?php echo $text_forget; ?></div>
  <div class="visit">
    <div class="row">
      <div class="col-sm-5 col-sm-offset-1 text-center">
        <img src="view/image/icon-store.png">
        <a class="btn btn-secondary" href="../"><?php echo $text_shop; ?></a>
      </div>
      <div class="col-sm-5 text-center">
        <img src="view/image/icon-admin.png">
        <a class="btn btn-secondary" href="../admin/"><?php echo $text_login; ?></a>
      </div>
    </div>
  </div>
  <div class="core-modules">
    <div class="row">
      <div class="col-sm-6 text-center">
        <img src="view/image/openbay_pro.gif">
        <p><?php echo $text_openbay; ?> <a href="http://www.openbaypro.com/?utm_source=eCmsCart_install&utm_medium=referral&utm_campaign=eCmsCart_install"><?php echo $text_more_info; ?></a></p>
        <a class="btn btn-primary" href="<?php echo $link_openbay; ?>"><?php echo $button_setup; ?></a>
      </div>
      <div class="col-sm-6 text-center">
        <img src="view/image/maxmind.gif">
        <p><?php echo $text_maxmind; ?> <a href="http://www.maxmind.com/?utm_source=eCmsCart_install&utm_medium=referral&utm_campaign=eCmsCart_install"><?php echo $text_more_info; ?></a></p>
        <a class="btn btn-primary" href="<?php echo $link_maxmind; ?>"><?php echo $button_setup; ?></a>
      </div>
    </div>
  </div>
  <div class="support text-center">
    <div class="row">
      <div class="col-sm-6">
        <a href="http://forum.ecmscart.com/" class="icon transition">
          <i class="fa fa-comments fa-4x"></i>
        </a>
        <h3><?php echo $text_forum; ?></h3>
        <p><?php echo $text_forum_info; ?></p>
        <a href="http://forum.ecmscart.com/"><?php echo $text_forum_link; ?></a>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>