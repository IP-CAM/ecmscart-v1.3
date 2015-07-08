<form method="post" action="<?php echo $action; ?>">
	<input type="hidden" name="command" value="initiateTransaction">
	<input type="hidden" name="encRequest" value="<?php echo $encrypted_data; ?>">

	<input type="hidden" name="access_code" value="<?php echo $access_code; ?>">
	<div class="buttons">
		<div class="right">
			<input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
		</div>
	</div>
</form>