<?php

namespace Lib;

class Bluetooth
{
    private $scripts;

    /**
     * Return the Bluetooth helper.
     */
    public function __construct()
    {
        $this->scripts = Config::getPath('scripts').'bluetooth/';
        static::powerOn();
    }

    /**
     * Turns on bluetooth.
     */
    public static function powerOn()
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
    public static function powerOff()
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
     * @return array an array containing the bluetooth devices/
     */
    public function scan()
    {
        $bluetooth_off = 'not available';

        $command = 'hcitool scan';
        $result = OS::execute($command);

        if (StringUtils::contains($result, $bluetooth_off)) {
            return [];
        }

        $dev_no = preg_match_all("/(([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2}))\s([^\s]+)/", $result, $devices_tmp);

        $devices = [];
        for ($i = 0; $i < $dev_no; $i++) {
            $devices[$i]['mac'] = $devices_tmp[1][$i];
            $devices[$i]['name'] = $devices_tmp[4][$i];
        }

        return $devices;
    }

    public function pair($mac)
    {
        $command = 'expect -c "
        spawn \"bluetoothctl\"
        expect \"# \"
        
        send \"agent on\r\"
        expect \"Agent registered\"
        
        send \"default agent\r\"
        expect \"Default agent request successful\"
        
        send \"pair MAC_ADDRESS\r\"
        expect \"Pairing successful\"
        
        send \"trust MAC_ADDRESS\r\"
        expect \"Changing MAC_ADDRESS trust succeeded\"
        
        send \"connect MAC_ADDRESS\r\"
        expect \"Connection successful\"
        
        send \"exit\r\"
        expect eof
        "';

        // Replace the string with the mac address
        $command = str_replace('MAC_ADDRESS', $mac, $command);

        $output = OS::execute($command);

        return StringUtils::contains($output, 'Connection successful');
    }

    public function unpairAll()
    {
        $cmd = 'expect -c "
        spawn \"bluetoothctl\"
        expect \"# \"
        send \"remove *\r\"
        expect \"# \"
        send \"exit\r\"
        expect eof
        "';

        $res = OS::execute($cmd);

        return StringUtils::contains($res, 'done');
    }
}
