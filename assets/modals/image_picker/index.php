<div class="modalHeader">Image picker</div>
<div class="modalBody" data-mcs-theme="dark">

    <div id="fromTheWeb" style="width: 75%; float: left">
        <form id="coverPickerForm">
            <input type="hidden" name="coverFrom" id="coverFrom"/>
            <input type="hidden" name="uploadedCover" id="uploadedCover"/>
            <input type="hidden" name="coverURL" id="coverURL"/>
        </form>

        <div class="mCustomScrollbar center" style="height: 300px">
            <div class="center">
                <form id="searchImage">
                    <p>Search and select a cover from the internet</p>
                    <div class="row" id="cover_info">
                        <input type="text" id="artist" name="artist" placeholder="Artist" required/>
                        <input type="text" id="album" name="album" placeholder="Album title" required/>
                        <div class="box-btn" id="search">Search</div>
                        <input type="submit" class="invisible"/>
                        <!-- <img id="loading-img" src="#" style="display: none;" /> -->
                    </div>
                </form>
            </div>

            <div class="row" id="covers" style="margin-top: 20px;">
                <p class="hidden" style="margin-top: 50px">
                    <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
                </p>
            </div>
        </div>
    </div>

    <div style="width: 25%; float: right;">
        <div class="center">
            <p>Or upload a file</p>
            <p>(*.jpg, *.png, *.gif)</p>
        </div>
        <form action="/assets/php/image_upload.php" enctype="multipart/form-data" class="dropzone"
              id="image_picker_dropzone">
            <div class="fallback">
                <input name="name" type="file" multiple/>
            </div>
        </form>
    </div>

</div>
<div class="modalFooter">
    <button onclick="imageSelector.back();">Back</button>
    <button class="right disabled" id="imagePickerSubmit">Confirm</button>
</div>
<?php
include '../../../vendor/autoload.php';

use Lib\ICanHaz;

ICanHaz::js('scripts.js', false, true);

?>