<div class="panel panel-default">
  <div class="panel-heading">
     <h3 class="panel-title"><i class="fa fa-calendar"></i> <?php echo $heading_title; ?></h3>
  </div>
  <div id="myCarousel" class="carousel slide vertical">
<!-- Carousel items -->
    <div class="carousel-inner">
    	<?php if ($activities) { ?>
    		<?php foreach ($activities as $activity) { ?>    
    		<div class="item">
      			<?php echo $activity['comment']; ?><br />
      			<small class="text-muted"><i class="fa fa-clock-o"></i> <?php echo $activity['date_added']; ?></small>
             </div>		 
    		 <?php } ?>
    	<?php } else { ?>
   		<div class="active item"><?php echo $text_no_results; ?></div>
    	<?php } ?>
	  </div>
  <!-- Carousel items -->
   </div>
</div>
<script type="text/javascript">
$( document ).ready(function() {
$('.carousel').carousel({
  interval: 3000,
})
$('.item:first').addClass('active');
});
</script>