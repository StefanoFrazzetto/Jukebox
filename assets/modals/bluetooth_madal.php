<div class="modalHeader">Bluetooth</div>
<div class="modalBody mCustomScrollbar" style="">

<?php

$mega_command = shell_exec("hcitool scan 2>&1");


if ($mega_command == "Device is not available: No such device"){
	shell_exec("sudo hciconfig hci0 reset");
	$mega_command = shell_exec("hcitool scan");
}
$blue_array = explode("\n", trim($mega_command));
array_shift($blue_array);
?>
<table class="mCustomScrollbar songsTable _mCS_54" style="width: 100%; padding-top: 0; margin-top: 0;">
    <tbody><tr class="th">
            <th>#</th>
            <th class="playlist_title">Device Name</th>
            <th>Mac Address</th>            
        </tr>
<?php

foreach($blue_array as $key => $network) {
	$this_mac = trim(str_split("$network", 18) [0]);
	$this_name = str_split("$network", 18) [1];
	echo "<tr onclick='BTConnect(\"$this_mac\")'  id='$this_mac'>";
	echo "<td class=''>" . $key . "</td>";
	echo "<td class='' >" . $this_name . "</td>";
	echo "<td class=''>" . $this_mac . "</td>";
	echo '</tr>';
}

?>
    </tbody>
</table>
    <img class="emrQRCode" src="<?php echo $imgData
?>"  alt="<?php
echo $url ?>" style="border-radius: 5px; box-shadow: 0px 0px 6px 2px rgba(0, 0, 0, 0.40);"/>
</div>