<?php
require_once 'rip_settings.php';

unset($_SESSION['ripping']);
unset($_SESSION['tracks']);
unset($_SESSION['CD']);

exec("$scripts/abort_ripping.sh > /dev/null 2>&1 &");
exec("$scripts/remove_ripped.sh > /dev/null 2>&1 &");
exec("$scripts/abort_encoding.sh > /dev/null 2>&1 &");
exec("$scripts/remove_encoded.sh > /dev/null 2>&1 &");

?>

<div class="modalHeader">Operation canceled</div>
<div class="modalBody">
	<div class="center">
		<div>The operation has been canceled.</div>
	</div>
</div>