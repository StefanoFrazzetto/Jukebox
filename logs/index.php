<pre>
<?php

if (isset($_GET['clear']) && $_GET['clear'] == 'y') {
    exec("bash ./clear_apache_log.sh");
    header('location: /logs/');
}

$log = "/var/log/apache2/error.log";

if (isset($_GET['log'])) {
    $log = $_GET['log'] . '.log';
}

$iterator = new \GlobIterator(__DIR__ . '/*.log', FilesystemIterator::KEY_AS_FILENAME);
$array = iterator_to_array($iterator);

if (count($array) > 0) {
    echo "[<a href=\"/logs/\">main</a>]";

    foreach ($array as $key => $file) {
        $name = $file->getFileName();
        $name = str_replace('.log', '', $name);

        echo '[<a href="?log=', $name, '">', $name, '</a>]';
    }

}

?> -- @[<a href="?clear=y">clear</a>]<hr/>
<?php

echo shell_exec("tac $log");