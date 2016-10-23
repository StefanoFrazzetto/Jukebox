<?php

class Bluetooth
{
    private $config;

    /** The bluetooth devices array */
    protected $devices;

    /** Bluetooth scripts */
    protected $cmd_folder;

    /**
     * Bluetooth constructor.
     *
     * @param $act
     * @param $mac
     */
    public function __construct($act, $mac = "")
    {
        $this->config = include "../config.php";
        $this->cmd_folder = $this->config['assets_path'] . '/cmd/bluetooth/';

        switch ($act) {
            case 'turn_on':
                $this->power('on');
                break;

            case 'turn_off':
                $this->power('off');
                break;

            case 'scan':
                if ($this->scan()) {
                } else {
                    $this->output("Error");
                }
                break;

            case 'pair':
                $this->pair($mac);
                break;

            case 'unpair':
                $this->unpair();
                break;

            default:
                $this->output('Errore fatale. Muori.');
        }
    }

    private function pair($mac) {
        $output = shell_exec($this->cmd_folder . "bluez5-connect $mac");
        if (strpos($output, 'org.bluez.Error.Failed') !== false) {
            $this->output('failed');
        } else {
            $this->output("connected");
        }
    }

    private function unpair() {
        $cmd = shell_exec($this->cmd_folder . 'unpair-all.sh');
        if (strpos($cmd, 'Device has been removed') === false) {
            $this->output("Error unpairing the devices");
        } else {
            $this->output("Fuck yeah!");
        }
    }

    /**
     * Launch the scan.
     *
     * @return bool true if the scan is successful, false if the interface is off.
     */
    private function scan() {
        $cmd = shell_exec('hcitool scan');

        if(strpos($cmd, 'not available') === false) {
            str_replace('Scanning ...', '', $cmd);
            preg_match("/\\n\\t(.*)\\t(.*)\\n/", $cmd, $matches);
            unset($matches[0]);

            for ($i = 1; $i < count($matches); $i+=2) {
                $device['mac'] = $matches[$i];
                $device['device'] = $matches[$i+1];

                $this->devices[] = $device;
            }

            $this->output($this->devices);
            return true;
        }

        return false;
    }

    /**
     * Change the bluetooth status.
     *
     * @param string $mode on or off
     * @return string
     */
    private function power($mode) {
        $cmd = shell_exec($this->cmd_folder . "power.sh $mode");
        if(strpos($cmd, 'succeeded') !== false) {
            $this->output("Bluetooth is now $mode");
        } else {
            $this->output("Can't switch the bluetooth $mode");
        }
    }

    /**
     * AJAX output
     *
     * @param string $string
     */
    private function output($string) {
        echo json_encode($string);
    }

}


$action = $_POST['action'];
$mac = isset($_POST['mac']) ? $_POST['mac'] : '';
$bt = new Bluetooth($action, $mac);