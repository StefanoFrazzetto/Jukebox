<?php

$output = shell_exec('bash /var/www/html/assets/cmd/CdCheck.sh');

if (strpos($output, '<<No disc in drive>>') !== false) {
    echo 'No disc in drive';
}
if (strpos($output, '<<Cd not blank>>') !== false) {
    echo 'Cd not blank';
}
if (strpos($output, '<<blank CD>>') !== false) {
    echo 'blank CD';
}
if (strpos($output, '<<blank DVD>>') !== false) {
    echo 'blank DVD';
}
if (strpos($output, '<<DVD not blank>>') !== false) {
    echo 'DVD not blank';
}
if (strpos($output, '<<Error>>') !== false) {
    echo 'Error';
}
