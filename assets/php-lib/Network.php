<?php

class Network
{

    var $interface = null; // eth0 - ra0
    var $dhcp = null; // Dynamic - Static

    // Static DHCP
    var $dhcp_address = null;
    var $ip = null;
    var $subnet_mask = null;
    var $gateway = null;
    var $dns = null;

    // Wifi
    var $encryption = null; // WPA - Open
    var $ssid = null;
    var $password = null;

    // Hotspot
    var $hotspot_ssid = null;
    var $hotspot_password = null;
    var $hotspot_channel = null;

    public function connect()
    {
        if ($this->interface === 'none') {
            $this->run_term('none');
        }

        if ($this->interface === 'ethernet') {
            $this->run_term('ethernet ' . $this->getDhcpParams());
        }

        if ($this->interface === 'wifi') {
            $this->run_term('wifi ' . $this->getDhcpParams() . ' ' . $this->getWifiParams());
        }

        if ($this->interface === 'hotspot') {
            $this->run_term('hotspot ' . $this->getHotspotParams());
        }
    }

    private function run_term($string)
    {
        return shell_exec("sudo ../cmd/network_setup.sh $string");
    }

    private function getDhcpParams()
    {
        if ($this->dhcp) {
            return "dhcp";
        } else {
            if (!$this->isStaticDhcpValid())
                throw new InvalidArgumentException();

            return "static $this->ip $this->subnet_mask $this->gateway $this->dns";
        }
    }

    public function isStaticDhcpValid()
    {
        return isset($this->subnet_mask, $this->dhcp_address, $this->ip, $this->gateway);
        // TODO check if all the other values are valid
    }

    private function getWifiParams()
    {
        global $wifiConfig;

        require_once __DIR__ . '/Wifi.php';

        $network = $wifiConfig[$this->ssid];

        $this->password = Wifi::decodePassword($network['password'], $network['salt']);

        if ($network['encryption'] == "open") {
            $this->encryption = "open";
        } elseif ($network['encryption_type'] == "WPA" || $network['encryption_type'] == "WPA2") {
            $this->encryption = "wpa";
        } elseif ($network['encryption_type'] == "WEP"){
            $this->encryption = "wep";
        }

        if (!$this->isWifiValid())
            throw new InvalidArgumentException();

        if ($this->encryption === 'open') {
            $this->password = '';
        }

        return "\"$this->ssid\" \"$this->password\" $this->encryption";
    }

    public function isWifiValid()
    {
        return isset($this->encryption, $this->ssid);
        // TODO check if all the other values are valid
    }

    private function getHotspotParams(){
        if (!$this->isHotspotValid())
            throw new InvalidArgumentException();

        return "\"$this->hotspot_ssid\" \"$this->hotspot_password\" $this->hotspot_channel";
    }

    public function isHotspotValid(){
        return isset($this->hotspot_ssid, $this->hotspot_password, $this->hotspot_channel);
        // TODO proper value check
    }

    public function load_network()
    {
        $json = file_get_contents(__DIR__ . '/../config/network_settings.json');

        $this->set_network_from_json($json);
    }

    public function set_network_from_json($json)
    {
        $settings = json_decode($json);

        foreach ($settings as $key => $setting) {
            if ($key === 'network_type') {
                if ($setting == '0')
                    $this->interface = 'none';
                elseif ($setting == '1')
                    $this->interface = 'ethernet';
                elseif ($setting == '2') {
                    $this->interface = 'wifi';
                }
                elseif ($setting == '3') {
                    $this->interface = 'hotspot';
                }
            } elseif ($key === 'dhcp') {
                if ($setting == 'on')
                    $this->dhcp = true;
                elseif ($setting == '2') {
                    $this->dhcp = false;
                }
            } elseif ($key === 'ipaddress') {
                $this->ip = $setting;
            } elseif ($key === 'gateway') {
                $this->gateway = $setting;
            } elseif ($key === 'subnetmask') {
                $this->subnet_mask = $setting;
            } elseif ($key === 'dns1') {
                $this->dns = $setting;
            } elseif ($key === 'ssid') {
                $this->ssid = $setting;
            } elseif($key === 'hotspot_ssid'){
                $this->hotspot_ssid = $setting;
            } elseif($key === 'hotspot_password'){
                $this->hotspot_password = $setting;
            } elseif($key === 'hotspot_channel'){
                $this->hotspot_channel = $setting;
            }

        }

        if (!isset($this->dhcp)) {
            $this->dhcp = false;
        }

        if (!isset($this->dhcp_address)) {
            $this->dhcp_address = $this->gateway;
        }
    }

}