#!/bin/bash

GIT=$(which git)
WEB_ROOT=/var/www/html

cd "$WEB_ROOT"

$GIT fetch --all
$GIT reset --hard origin/master
