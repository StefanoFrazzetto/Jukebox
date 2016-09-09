<div class="modalHeader">Scan QR code</div>
<div class="modalBody" style="text-align: center;">

    <head>
        <script>
            function SwapQr(key) {
                document.getElementById("QrCodeIMG").src = key;
            }

        </script>
    </head>


    <?php
    $all_ip = shell_exec("sudo ifconfig | egrep 'inet addr:'|grep -v '127.0.0.1'|cut '-d ' -f12|cut -d ':' -f2 && wget http://ipinfo.io/ip -qO -");
    $all_ip_array = explode("\n", trim($all_ip));

    foreach($all_ip_array as $key => $ip) {

        $img . $key = shell_exec("qrencode --output=- -s 8 -m 1 -t PNG 'http://$ip'");
        $imgData . $key = "data:image/png;base64," . base64_encode($img . $key);
        echo "<button onclick='SwapQr(\"$key\")'>$ip</button>";
    }

?>
        <p><img class="emrQRCode" id="QrCodeIMG" src="<?php echo $imgData . $key ?>" alt="<?php
echo $ip ?>" style="border-radius: 5px; box-shadow: 0px 0px 6px 2px rgba(0, 0, 0, 0.40);" />
</div>
