#!/bin/bash

[ $(git rev-parse HEAD) = $(git ls-remote origin master | cut -f1) ] && echo up to date || echo not up to date