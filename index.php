<?php
require_once 'vendor/autoload.php';

use Lib\ICanHaz;

if (isJukebox()) {
    session_start();
    include 'assets/php/startup_scripts.php';
    session_write_close();
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>&lt;JUKEBOX&gt;</title>
    <?php
    try {
        ICanHaz::css(['/assets/css/main.css', '/assets/css/font-awesome.min.css', '/assets/css/jquery.mCustomScrollbar.min.css'], true);
    } catch (Exception $e) {
        die('<h1>CSS FILES MISSING!</h1>');
    }
    ?>
    <link href="assets/img/icons/vinyl1.png" rel="icon" type="image/png">
    <meta name="theme-color" content="#2a2a2a">
    <meta charset="UTF-8">
</head>

<body>
<div id="container">
    <div id="controls">
        <div id="maskLower">
            <div id="albumOverlay">
                <img id="albumCover"/>
            </div>

            <div id="volumeOverlay">
                <div id="slider-vertical" class="progressBar thin"></div>
                <span class="tooltip"></span>
            </div>

            <div id="mask">
                <div id="dropdownModal">
                    <div id="power" class="crcbtn" title="Power settings">
                        <i class="material-icons">settings_power</i>
                    </div>
                    <div id="settings" class="crcbtn" title="Settings" onclick="modal.openSettings('settings.php')">
                        <i class="material-icons">settings</i>
                    </div>
                    <div id="network_settings" class="crcbtn" title="Bluetooth"
                         onclick="alert('Bluetooth is not available yet.');">
                        <i class="material-icons">bluetooth</i>
                    </div>
                    <div id="qr" class="crcbtn" title="Scan QR"
                         onclick="modal.openPage('assets/modals/qrcode.php')">
                        <i class="fa fa-qrcode"></i>
                    </div>
                    <div id="trash" class="crcbtn">
                        <i class="material-icons">warning</i>
                    </div>
                    <div id="add" class="crcbtn" title="Add a new album">
                        <i class="material-icons">library_add</i>
                    </div>
                    <div id="shuffle" class="crcbtn" title="Shuffle">
                        <i class="material-icons">shuffle</i>
                    </div>
                    <div id="wifi" class="crcbtn" title="Network settings"
                         onclick="modal.openPage('assets/modals/network_settings')">
                        <i class="material-icons">wifi</i>
                    </div>
                    <div id="ripper" class="crcbtn" title="Rip a CD"
                         onclick="modal.openPage('assets/modals/rip/rip_pre.php')">
                        <i class="material-icons">disc_full</i>
                    </div>
                    <div id="eq_button" class="crcbtn" title="Equaliser"
                         onclick="modal.openPage('assets/modals/eq/index.php')">
                        <i class="material-icons">equalizer</i>
                    </div>
                    <div id="radio" class="crcbtn" title="Radio"
                         onclick="modal.openPage('assets/modals/radio/index.php')">
                        <i class="material-icons">settings_input_antenna</i>
                    </div>
                    <div id="burn" class="crcbtn" title="missing">
                        <i class="material-icons">warning</i>
                    </div>
                </div>

                <div id="marquee">
                    <canvas width="354px" height="94px" id="eq_canvas"></canvas>
                    <div style="position: absolute; top: 0; right: 0; width: 100%">
                        <span id="songTitle"></span>
                        <br/>
                        <span id="albumTitle"></span>
                        <br/>
                        <span id="seek">00:00</span>
                    </div>
                </div>

                <div id="slider" class="progressBar thin"></div>

                <div id="repeat" class="crcbtn">
                    <i class="material-icons">repeat</i>
                </div>
                <div id="stop" class="crcbtn">
                    <i class="material-icons">stop</i>
                </div>
                <div id="bwd" class="crcbtn">
                    <i class="material-icons">skip_previous</i>
                </div>
                <div id="play" class="crcbtn bigger">
                    <i class="material-icons">play_arrow</i>
                </div>
                <div id="fwd" class="crcbtn">
                    <i class="material-icons">skip_next</i>
                </div>

                <div id="playlist" class="crcbtn" onclick="modal.openPage('assets/modals/playlist')">
                    <i class="material-icons">queue_music</i>
                </div>

                <div id="menu-btn" class="crcbtn">
                    <i class="material-icons">more_horiz</i>
                </div>

            </div>

            <div id="playlistOverlay" class="mCustomScrollbar" data-mcs-theme="dark">
                <table id="playlistTable"></table>
            </div>
        </div>
        <audio id="player" crossorigin="anonymous"></audio>

        <div id="buttonsBar" class="toolbar">

                <span class="searchbox-icon" id="home-btn">
                    <i class="material-icons">home</i>
                </span>

            <span id="alphabet">
                    <a href="#">0</a>
                    <a href="#">A</a>
                    <a href="#">B</a>
                    <a href="#">C</a>
                    <a href="#">D</a>
                    <a href="#">E</a>
                    <a href="#">F</a>
                    <a href="#">G</a>
                    <a href="#">H</a>
                    <a href="#">I</a>
                    <a href="#">J</a>
                    <a href="#">K</a>
                    <a href="#">L</a>
                    <a href="#">M</a>
                    <a href="#">N</a>
                    <a href="#">O</a>
                    <a href="#">P</a>
                    <a href="#">Q</a>
                    <a href="#">R</a>
                    <a href="#">S</a>
                    <a href="#">T</a>
                    <a href="#">U</a>
                    <a href="#">V</a>
                    <a href="#">W</a>
                    <a href="#">X</a>
                    <a href="#">Y</a>
                    <a href="#">Z</a>
                </span>

            <span class="searchbox-icon pull-right" id="searchbox-icon">
                    <i class="material-icons">search</i>
                </span>

            <span class="searchbox-icon pull-right" id="sort-icon">
                   <i class="material-icons">sort</i>
                </span>

            <div id="sorter" class="toolbar">
                <div class="by active" data-value="1">
                        <span class="searchbox-icon pull-right">
                            <i class="material-icons" title="Artist">face</i>
                        </span>
                    <div>Artist</div>
                </div>
                <div class="by" data-value="2">
                        <span class="searchbox-icon pull-right">
                            <i class="material-icons" title="Album">album</i>
                        </span>
                    <div>Album</div>
                </div>
                <div class="by" data-value="3">
                        <span class="searchbox-icon pull-right">
                            <i class="material-icons" title="Popular">favorite</i>
                        </span>
                    <div>Hits</div>
                </div>
                <div class="by" data-value="4">
                        <span class="searchbox-icon pull-right">
                            <i class="material-icons" title="Last Played">history</i>
                        </span>
                    <div>Time</div>
                </div>
                <div class="by" data-value="5">
                        <span class="searchbox-icon pull-right">
                            <i class="material-icons" title="Last uploaded">file_upload</i>
                        </span>
                    <div>Added</div>
                </div>
            </div>

        </div>

        <div id="searchBar">
            <div id="search-items">
                <form class="searchbox" id="searchbox">
                    <input type="search" placeholder="Search..." name="search" id="searchbox-input" class="toolbar"
                           required/>
                    <input type="submit" class="searchbox-submit" id="searchSubmit" value="GO"/>
                </form>
            </div>

            <span id="search-by" class="toolbar">
                    <span class="searchbox-icon active" id="artist-icon">
                        <i class="material-icons" title="Artist">face</i>
                    </span>
                    <span class="searchbox-icon" id="album-icon">
                        <i class="material-icons" title="Album">album</i>
                    </span>
                    <span class="searchbox-icon" id="song-icon">
                        <i class="material-icons" title="Song">music_note</i>
                    </span>
                </span>

            <span class="toolbar" id="last-icon-wrapper">
                    <span class="searchbox-icon pull-right">
                        <i class="fa fa-search"></i>
                    </span>
                </span>

        </div>
    </div>
    <div id="albums">
        <div id="mainContentAjax">
            <div id="previous" class="hidden"><img src="assets/img/icons/previous.png"></div>
            <div id="next" class="hidden"><img src="assets/img/icons/next.png"></div>
            <div id="mainContentAjaxLoader"></div>
        </div>
    </div>
    <div id="modal">
        <img src="assets/img/icons/close.png" class="closeModal" onclick="modal.close()"/>
        <div class="modalContainer">
            <i class="fa fa-spinner fa-spin fa-4x fa-fw"></i>
        </div>
        <div id="modalAjaxLoader"></div>
    </div>
</div>


<?php
function isJukebox()
    {
        return in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
    }

$scripts = [
    '$/vars.js',
    '$/Arrays.js',
    '$/alerts.js',
    '$/jquery.min.js',
    '$/jquery-ui.min.js',
    '$/sliders.js',
    '$/Player.js',
    '$/player_init.js',
    '$/searchbar.js',
    '$/modals.js',
    '$/jquery.mCustomScrollbar.concat.min.js',
    '$/ImageSelector.js',
    '$/Uploader.js',
    '$/dropzone.js',
    '$/post-script.js',
];

if (isJukebox()) {   // Things that wil be done only by the local jukebox client
    $scripts[0] = '$/vars_jb.js';
    $scripts[] = '$/remote_listener.js';
}

ICanHaz::js($scripts, true);

if (isJukebox()) {
    include 'assets/modals/keyboard.php';
}
?>

</body>

</html>