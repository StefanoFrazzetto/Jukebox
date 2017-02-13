<?php
require_once "vendor/autoload.php";
use Lib\ICanHaz;

?>
<!DOCTYPE html>
<html>

<head>
    <title>&lt;JUKEBOX&gt;</title>
    <?php ICanHaz::css(['assets/css/main.css', 'assets/css/font-awesome.min.css', 'assets/css/jquery.mCustomScrollbar.min.css'], true); ?>
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
                    <div id="power" class="crcbtn"></div>
                    <div id="settings" class="crcbtn" onclick="modal.openPage('assets/modals/settings.php')"></div>
                    <div id="network_settings" class="crcbtn"
                         onclick="modal.openPage('assets/modals/bluetooth.php')"></div>
                    <div id="qr" class="crcbtn" onclick="modal.openPage('assets/modals/qrcode.php')"></div>
                    <div id="trash" class="crcbtn" onclick="storage.deleteAlbum(album_id);"></div>
                    <div id="add" class="crcbtn"></div>
                    <div id="shuffle" class="crcbtn"></div>
                    <div id="wifi" class="crcbtn" onclick="modal.openPage('assets/modals/network_settings')"></div>
                    <div id="ripper" class="crcbtn" onclick="modal.openPage('assets/modals/rip/rip_pre.php')"></div>
                    <div id="eq_button" class="crcbtn" onclick="modal.openPage('assets/modals/eq/index.php')"></div>
                    <div id="radio" class="crcbtn" onclick="modal.openPage('assets/modals/radio/index.php')"></div>
                    <div id="burn" class="crcbtn" onclick="modal.openPage('assets/modals/burner.php')"></div>
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

                <div id="repeat" class="crcbtn"></div>
                <div id="stop" class="crcbtn"></div>
                <div id="bwd" class="crcbtn"></div>
                <div id="play"></div>
                <div id="fwd" class="crcbtn"></div>
                <div id="menu-btn" class="crcbtn"></div>
                <div id="playlist" class="crcbtn" onclick="modal.openPage('assets/modals/playlist.php')"></div>

            </div>

            <div id="playlistOverlay" class="mCustomScrollbar" data-mcs-theme="dark">
                <table id="playlistTable"></table>
            </div>
        </div>
        <audio id="player" crossorigin="anonymous"></audio>

        <div id="buttonsBar" class="toolbar">

                <span class="searchbox-icon" id="home-btn">
                    <i class="fa fa-home"></i>
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
                    <i class="fa fa-search"></i>
                </span>

            <span class="searchbox-icon pull-right" id="sort-icon">
                   <i class="fa fa-sort"></i>
                </span>

            <div id="sorter" class="toolbar">
                <div class="by active" data-value="1">
                        <span class="searchbox-icon pull-right">
                            <i class="fa fa-user"></i>
                        </span>
                    <div>Artist</div>
                </div>
                <div class="by" data-value="2">
                        <span class="searchbox-icon pull-right">
                            <i class="fa fa-dot-circle-o"></i>
                        </span>
                    <div>Album</div>
                </div>
                <div class="by" data-value="3">
                        <span class="searchbox-icon pull-right">
                            <i class="fa fa-star"></i>
                        </span>
                    <div>Hits</div>
                </div>
                <div class="by" data-value="4">
                        <span class="searchbox-icon pull-right">
                            <i class="fa fa-clock-o"></i>
                        </span>
                    <div>Time</div>
                </div>
                <div class="by" data-value="5">
                        <span class="searchbox-icon pull-right">
                            <i class="fa fa-upload"></i>
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
                        <i class="fa fa-user"></i>
                    </span>
                    <span class="searchbox-icon" id="album-icon">
                        <i class="fa fa-dot-circle-o"></i>
                    </span>
                    <span class="searchbox-icon" id="song-icon">
                        <i class="fa fa-music"></i>
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
        <div class="ajaxloader">
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
    'assets/js/vars.js',
    'assets/js/alerts.js',
    'assets/js/jquery.min.js',
    'assets/js/jquery-ui.min.js',
    'assets/js/sliders.js',
    'assets/js/Player.js',
    'assets/js/player_init.js',
    'assets/js/searchbar.js',
    'assets/js/modals.js',
    'assets/js/jquery.mCustomScrollbar.concat.min.js',
    'assets/js/post-script.js'
];

if (isJukebox()) {   // Things that wil be done only by the local jukebox client
    $scripts[0] = 'assets/js/vars_jb.js';
    $scripts[] = 'assets/js/remote_listener.js';
}


ICanHaz::js($scripts, true);

if (isJukebox())
    include 'assets/modals/keyboard.php';
?>

</body>

</html>