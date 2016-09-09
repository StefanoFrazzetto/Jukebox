<?php

session_start();


if (!isset($_SESSION['CD'])) {
    $_SESSION['CD'] = 2;
} else {
    $_SESSION['CD'] ++;
}