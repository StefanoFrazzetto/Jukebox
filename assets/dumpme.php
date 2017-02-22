<pre>
<?php


function parse_shell_output($output, $keys = ['index', 'name', 'volume', 'muted', 'state'])
{
    $lines = explode('\n', $output);
    $spacing = "[\t\s]*";
    $match = "/^$spacing(?P<default>\*)?$spacing(?P<key>".implode('|', $keys).'): (?P<value>.*)$/';
    $devices = [];
    $default = 0;
    foreach ($lines as $nr => $line) {
        if (preg_match($match, $line, $matches)) {
            if ($matches['key'] == 'index') {
                $devices[] = [];
            }
            if ($matches['default'] && $matches['default'] == '*') {
                $default = count($devices) - 1;
            }

            if (is_array($devices[count($devices) - 1])) {
                $devices[count($devices) - 1][$matches['key']] = $matches['value'];
            }
        }
    }
    $devices['default'] = $default;

    return ['devices'=>$devices, 'default'=>$default];
}

echo shell_exec("pactl list cards | egrep 'Card #|device.bus = |device.string = |device.description = |Active Profile: |a2dp'");

$command2 = 'pacmd set-card-profile 1 a2dp';

$command3 = 'bash switch-sink a2dp';

//echo shell_exec("whoami");

//echo "magic command";

//var_dump(system($command3));

//echo "\n\n", shell_exec($command3);

echo system($command3);
