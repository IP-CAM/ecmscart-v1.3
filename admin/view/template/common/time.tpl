<div id="stats">
 <div class="text-center">
 <i class="fa fa-clock-o"></i>
 </div>
  <ul>
    <li>
      <div id="timer"></div>
    </li>
  </ul>
</div>

<script type="text/javascript"><!--
$(document).ready(function() {	
  setInterval(function time() {
    $("#timer").load("index.php?route=common/column_right/getDateTime&token=<?php echo $token; ?>");
  return time;
}(), 10000);
});
//--></script>
