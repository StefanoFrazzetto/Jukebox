<?php

function areAllTrackNoPresent(&$array)
{
    foreach ($array as &$track) {
        if (isset($track['track_no'])) {
            if ($track['track_no'] != '') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

function mergesort(&$array, $cmp_function = 'strcmp')
{ //replacement of usort
    // Arrays of size < 2 require no action.
    if (count($array) < 2) {
        return;
    }
    // Split the array in half
    $halfway = count($array) / 2;
    $array1 = array_slice($array, 0, $halfway);
    $array2 = array_slice($array, $halfway);
    // Recurse to sort the two halves
    mergesort($array1, $cmp_function);
    mergesort($array2, $cmp_function);
    // If all of $array1 is <= all of $array2, just append them.
    if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
        $array = array_merge($array1, $array2);

        return;
    }
    // Merge the two sorted arrays into a single sorted array
    $array = [];
    $ptr1 = $ptr2 = 0;
    while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
        if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
            $array[] = $array1[$ptr1++];
        } else {
            $array[] = $array2[$ptr2++];
        }
    }
    // Merge the remainder
    while ($ptr1 < count($array1)) {
        $array[] = $array1[$ptr1++];
    }
    while ($ptr2 < count($array2)) {
        $array[] = $array2[$ptr2++];
    }
}

/* this function should sort tables given one colum o sort */

function sort_matrix(&$array, $fields)
{
    $fields_array = array_reverse(explode(',', $fields));

    foreach ($fields_array as $field) {

        //$array = array_reverse($array);
        //uasort($array, function ($i, $j, $field) use (&$field) /* sounds like magic */ {
        mergesort($array, function ($i, $j, $field) use (&$field) /* sounds like magic */ {      //me just playing but seems to work for most albums :)
            return strnatcmp($i[$field], $j[$field]);
        });
    }

    return $array;
}
