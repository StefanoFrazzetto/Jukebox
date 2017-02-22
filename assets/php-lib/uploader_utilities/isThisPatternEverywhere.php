<?php

function isThisPatternEverywhere($pattern, $titles)
{
    $occurencies = 0;
    foreach ($titles as $title) {
        if (preg_match($pattern, $title)) {
            $occurencies++;
        }
    }

    //echo '<pre>I found this pattern ', $pattern, " $occurencies time(s)!</pre>";
    if ($occurencies == count($titles) && count($titles) > 1) {
        return true;
    } else {
        return false;
    }
}
