<?php

namespace Lib;

class Bluetooth
{
    private $config;

    /** The bluetooth devices array */
    private $devices;

    /**
     * Return the Bluetooth helper.
     */
    public function __construct()
    {
        $this->config = Config::getPath('scripts');
    }

    /**
     * Turns on bluetooth.
     */
    public function powerOn()
    {
        $command = 'expect -c "
        spawn \"bluetoothctl\"
        expect \"# \"
        send \"power on\r\"
        expect \"Changing power on succeeded\"
        send \"exit\r\"
        expect eof
        "';

        OS::execute($command);
    }

    /**
     * Turns off bluetooth.
     */
    public function powerOff()
    {
        $command = 'expect -c "
        spawn \"bluetoothctl\"
        expect \"# \"
        send \"power off\r\"
        expect \"Changing power off succeeded\"
        send \"exit\r\"
        expect eof
        "';

        OS::execute($command);
    }

    /**
     * Returns a 2D array containing bluetooth devices.
     *
     * The array will contain the device mac and name.
     *
     * @return array|bool an array containing devices in the following format:
     * array['index']['mac']
     * array['index']['name']
     *
     * If the bluetooth controller is off, false is returned.
     */
    public function scan()
    {
        $bluetooth_off = 'not available';

        $command = 'hcitool scan';
        $result = OS::execute($command);

        if (StringUtils::contains($result, $bluetooth_off)) {
            return false;
        }

        $dev_no = preg_match_all("/(([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2}))\s([^\s]+)/", $result, $devices_tmp);

        $devices = [];
        for ($i = 0; $i < $dev_no; $i++) {
            $devices[$i]["mac"] = $devices_tmp[1][$i];
            $devices[$i]["name"] = $devices_tmp[4][$i];
        }

        return $devices;
    }

    public function connect($mac)
    {

    }

    private function pair($mac)
    {
        $output = shell_exec($this->cmd_folder . "bluez5-connect $mac");
        if (strpos($output, 'org.bluez.Error.Failed') !== false) {
            $this->output('failed');
        } else {
            $this->output('connected');
        }
    }

    private function unpair()
    {
        $cmd = shell_exec($this->cmd_folder . 'unpair-all.sh');
        if (strpos($cmd, 'done') === false) {
            $this->output('Error unpairing the devices');
        } else {
            $this->output('All devices unpaired.');
        }
    }
}
