<div class="modalHeader">Add new Radio Station</div>


<div class="modalBody">
    <center id='firstStep'>
        <label for="radiourl">Radio URL</label>
        <p/>
        <input type="text" class="larger" id="radiourl" placeholder="URL"/>
        <p/>
        <button onclick="modal.openPage('/assets/modals/radio/index.php')">Cancel</button>
        <button onclick="testRadio()">Test</button>
        <button onclick="secondRadioPage();">Next</button>
    </center>

    <center id='secondStep' style='display: none'>
        <div class="center col-left">
            <label for="radioname">Radio Name</label>
            <p/>
            <input type="text" class="large" id="radioname" placeholder="Name"/>
        </div>

        <div class="center col-right">
            <label>Cover</label>
            <p/>
            <img src="/assets/img/album-placeholder.png" id="addRadioCover" onclick="openChangeCover()" width="175"
                 class="cover-picture">
        </div>

        <div class="center col-row">
            <button onclick="firstRadioPage()">Back</button>
            <button onclick="addNewRadio()">Add</button>
        </div>
    </center>

    <center id='loading' style='display: none'>
        <h2>LOADING...</h2>
    </center>

    <script src="/assets/js/validateURL.js"></script>
    <script src="/assets/modals/radio/add_radio/scripts.js?2"></script>
    <script src="/assets/js/parseUri.js"></script>
</div>
