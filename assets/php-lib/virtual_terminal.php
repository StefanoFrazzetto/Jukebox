<style>
    .terminal{
        box-sizing: border-box;
        background-color: black;
        width: 90%;
        margin-left: 5%;
        font-family: currier-new;

        font-family: monospace;

        text-decoration: none;
        color: green;
        font-size: 1.5em;
        padding: 5px;
        border: solid 2px gray;
    }
    .terminal i{
        text-decoration: blink;
    }
</style>
<?php

function terminator($cmd) {
    $output = system($cmd);
    echo "<pre class='terminal'>$cmd\n$output\n><i>_</i></pre>";
    return $output;
}
