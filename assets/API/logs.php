<pre>
<?php

require_once '../../vendor/autoload.php';


use Lib\Logger;

$action = isset($_GET['action']) ? $_GET['action'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
$logger = new Logger();
$output = '';

switch ($action) {
    case 'clear': // Clear the log file
        $output = Logger::clearLog($file) ? 'Log file cleared.' : 'Could not clear the log file.';
        break;

    case 'view': // Display the content of the log
        try {
            $output = Logger::getLog($file);
        } catch (Exception $e) {
            $output = $e->getMessage();
        }
        break;

    case 'download':
        try {
            $files = $logger->download();
            foreach ($files as $log) {
                $output .= "<a href='$log'>$log</a>\n";
            }
        } catch (RuntimeException $e) {
            $output = $e->getMessage();
        }
        break;

    case 'list': // Display all the log files in the directory /var/log/
    default:
        foreach ($logger->listLogs() as $file) {
            $output .= "<a href='?action=view&file=$file'>" . $file . "</a> \n";
        }
}

echo $output;
?>
</pre>