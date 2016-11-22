<pre>
<?php

if(isset($_GET['clear']) && $_GET['clear'] == 'y') {
	exec("bash ./clear_apache_log.sh");
}

echo shell_exec("tac /var/log/apache2/error.log");