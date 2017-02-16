<?php require_once "vendor/autoload.php";
use Lib\ICanHaz;

?>

<!DOCTYPE html>
<html>

<head>
    <title>&lt;JUKEBOX&gt;</title>
    <?php ICanHaz::css(['assets/css/main.css', 'assets/css/font-awesome.min.css', 'assets/css/jquery.mCustomScrollbar.min.css']) ?>
    <link rel="icon" type="image/png" href="assets/img/icons/vinyl1.png">
    <meta name="theme-color" content="#2a2a2a">
    <meta charset="UTF-8">
</head>

<body>
<div id="container">
    <div id="modal">
        <img src="assets/img/icons/close.png" class="closeModal" onclick="modal.close()"/>
        <div class="ajaxloader">
            <i class="fa fa-spinner fa-spin fa-4x fa-fw"></i>
        </div>
        <div id="modalAjaxLoader"></div>
    </div>
</div>

<?php
ICanHaz::js([
    'assets/js/vars.js',
    'assets/js/jquery.min.js',
    'assets/js/jquery-ui.min.js',
    'assets/js/draggable.js',
    'assets/js/sliders.js',
    'assets/js/modals.js',
    'assets/js/jquery.mCustomScrollbar.concat.min.js',
    'assets/js/alerts.js',
    'assets/js/Uploader.js'
]);
?>

<script type="text/javascript">
    Uploader.start();
</script>

</body>

</html>
