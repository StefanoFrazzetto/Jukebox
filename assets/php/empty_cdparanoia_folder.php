<?php

session_start();

$remove_from_cdparanoia_folder = '../modals/rip/scripts/remove_ripped.sh';
exec($remove_from_cdparanoia_folder);
