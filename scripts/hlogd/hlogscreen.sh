#!/bin/sh
# 
# I mainly use this for debugging when running hlogd in the
# foreground
screen -S hlogd -m -d ./hlogd.pl
