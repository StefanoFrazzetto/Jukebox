<div class="modalHeader">Add new Radio Station</div>


<div class="modalBody">
    <center id='firstStep'>
        <input type="text" id="radiourl" placeholder="URL"/>
        <br/>

        <br/>
        <button onclick="openModalPage('/assets/modals/radio/index.php')">Cancel</button>
        <button onclick="testRadio()">Test</button>
        <button onclick="secondRadioPage();">Next</button>
    </center>

    <center id='secondStep' style='display: none'>
        <input type="text" id="radioname" placeholder="Name"/>
        <br/>

        <br/>
        <button onclick="firstRadioPage()">Back</button>
        <button onclick="addNewRadio()">Add</button>
    </center>

    <center id='loading' style='display: none'>
        <h2>LOADING...</h2>
    </center>

    <script src="/assets/modals/radio/scripts.js"></script>
    <script src="/assets/js/parseUri.js"></script>
</div>
