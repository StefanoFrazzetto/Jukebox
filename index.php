<!DOCTYPE html>
<html>

<head>
    <title>&lt;JUKEBOX&gt;</title>
    <link href="assets/css/main.css?v6" rel="stylesheet" type="text/css"/>
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css"/>
    <link rel="icon" type="image/png" href="assets/img/icons/vinyl1.png">
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
                <!-- Tooltip -->
            </div>

            <div id="mask">
                <div id="dropdownModal">
                    <div id="power" class="crcbtn"></div>
                    <div id="settings" class="crcbtn" onclick="openModalPage('assets/modals/settings.php')"></div>
                    <div id="network_settings" class="crcbtn"
                         onclick="openModalPage('assets/modals/bluetooth.php')"></div>
                    <div id="qr" class="crcbtn" onclick="openModalPage('assets/modals/qrcode.php')"></div>
                    <div id="trash" class="crcbtn" onclick="deleteAlbum(album_id);"></div>
                    <div id="add" class="crcbtn"></div>
                    <div id="shuffle" class="crcbtn"></div>
                    <div id="wifi" class="crcbtn" onclick="openModalPage('assets/modals/network_settings')"></div>
                    <div id="ripper" class="crcbtn" onclick="openModalPage('assets/modals/rip/rip_pre.php')"></div>
                    <div id="eq_button" class="crcbtn" onclick="openModalPage('assets/modals/eq/index.php')"></div>
                    <div id="radio" class="crcbtn" onclick="openModalPage('assets/modals/radio/index.php')"></div>
                    <div id="burn" class="crcbtn" onclick="openModalPage('assets/modals/burner.php')"></div>
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
                <div id="playlist" class="crcbtn" onclick="openModalPage('assets/modals/playlist.php')"></div>

            </div>

            <div id="playlistOverlay" class="mCustomScrollbar" data-mcs-theme="dark">
                <table id="playlistTable"></table>
            </div>
        </div>
        <audio id="player"></audio>
    </div>
    <div id="albums">
        <div id="buttonsBar" class="toolbar" style="position: relative;">

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

        <div id="mainContentAjax">
            <div id="previous"><img src="assets/img/icons/previous.png"></div>
            <table id="mainContentAjaxLoader"></table>
            <div id="next"><img src="assets/img/icons/next.png"></div>
        </div>

    </div>
    <div id="modal">
        <img src="assets/img/icons/close.png" class="closeModal" onclick="closeModal()"/>
        <div class="ajaxloader">
            <i class="fa fa-spinner fa-spin fa-4x fa-fw"></i>
        </div>
        <div id="modalAjaxLoader"></div>
    </div>
</div>

<script type="text/javascript" src="assets/js/vars.js"></script>
<script type="text/javascript" src="assets/js/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="assets/js/sliders.js"></script>
<script type="text/javascript" src="assets/js/player.js"></script>
<script type="text/javascript" src="assets/js/searchbar.js"></script>
<script type="text/javascript" src="assets/js/modals.js"></script>
<script type="text/javascript" src="assets/js/post-script.js"></script>
<script type="text/javascript" src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
<script type="text/javascript" src="assets/js/alerts.js"></script>
<?php
$whitelist = array(
    '127.0.0.1',
    '::1'
);


if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) { // Things that wil be done only by the local jukebox client
    include 'assets/modals/keyboard.php';
    ?>
    <script type="text/javascript" src="assets/js/remote_listener.js"></script>
    <script type="text/javascript" src="assets/js/vars_jb.js"></script>
<?php
} else { // Things that will be done only from external clients ?>
    <script src="assets/js/eq.js"></script>
<?php } ?>

</body>

</html>
