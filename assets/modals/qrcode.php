<div class="modalHeader">Scan QR code</div>
<div class="modalBody" style="text-align: center;">
    <?php

    $all_ip = shell_exec("sudo ifconfig | egrep 'inet addr:'|grep -v '127.0.0.1'|cut '-d ' -f12|cut -d ':' -f2 && wget http://ipinfo.io/ip -qO -");

    $all_ip_array = explode("\n", trim($all_ip));

    if (count($all_ip_array) > 0) {
        function getQR($ip)
        {
            $bin = shell_exec("qrencode --output=- -s 8 -m 1 -t PNG 'http://$ip'");

            return 'data:image/png;base64,'.base64_encode($bin);
        }

        foreach ($all_ip_array as $key => $ip) {
            $qr = getQR($ip);

            echo "<button onclick='SwapQr(\"$qr\")'>$ip</button>";
        } ?>
        <p>
            <img class="emrQRCode" id="QrCodeIMG"
                 src="<?php echo getQR($all_ip_array[0]) ?>"
                 alt="<?php echo $all_ip_array[0] ?>"
                 style="border-radius: 5px; box-shadow: 0px 0px 6px 2px rgba(0, 0, 0, 0.40);"
            />
        </p>
    <?php 
    } ?>
</div>

<script>
    function SwapQr(key) {
        document.getElementById("QrCodeIMG").src = key;
    }
</script>