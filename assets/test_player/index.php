<?php
require_once '../php-lib/ICanHaz.php'
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Web Audio Api Equalizer</title>
    <link rel="icon" href="/assets/img/icons/vinyl1.png"/>
    <?php ICanHaz::css('css/style.css'); ?>
</head>

<body>
<h2>Web Audio Api Equalizer</h2>
<div class="eq" id="eq">

</div>
<h3>Vittorio Iocolano - <a href="http://vittorio-io.me">website</a></h3>
<audio id="player" controls crossorigin>
    <!--<source src="http://api.audiotool.com/track/volution/play.mp3" type="audio/mpeg">-->
    <!--<source src="http://api.audiotool.com/track/volution/play.ogg" type="audio/ogg">-->
    <source src="gershwin-rhapsodyinblue-bertoli.mp3" type="audio/mpeg">
    <!--<source src="01.kelly-llorenna-tell-it-to-my-heart.mp3" type="audio/mpeg">-->
    Your browser does not support the audio tag.
</audio>
<canvas id="canvas" width="500"></canvas>
<!--<canvas id="canvas" width="354" height="94"></canvas>-->
<?php ICanHaz::js(['../js/jquery.min.js', '../js/player.v2.0.js', '../js/storage.js']); ?>
<script>
    //region Instances
    var player = new Player();

    player.EQ.connect();

    player.EQ.drawEQ(document.getElementById('eq'));

    player.visualiser = new Visualiser(player.context, player.outputNode, document.getElementById("canvas"));

    player.visualiser.reflectEQ = true;

    load_storages();
    //endregion
</script>

</body>
</html>
