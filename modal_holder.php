<!DOCTYPE html>
<html>

<head>
    <title>&lt;JUKEBOX&gt;</title>
    <link href="assets/css/main.css?v5" rel="stylesheet" type="text/css"/>
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css"/>
    <link rel="icon" type="image/png" href="assets/img/icons/vinyl1.png">
    <meta name="theme-color" content="#2a2a2a">
    <meta charset="UTF-8">
</head>

<body>
<div id="container">
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
<script type="text/javascript" src="assets/js/draggable.js"></script>
<script type="text/javascript" src="assets/js/sliders.js"></script>
<script type="text/javascript" src="assets/js/modals.js"></script>
<script type="text/javascript" src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
<script type="text/javascript" src="assets/js/alerts.js"></script>
<script type="text/javascript">
    openModalPage('assets/modals/album_upload/1-Intro.php');
</script>

</body>

</html>
