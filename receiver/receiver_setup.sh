#!/bin/sh

# turn off blanking and screen saver
DISPLAY=:0 /usr/bin/xset s noblank
DISPLAY=:0 /usr/bin/xset -dpms
DISPLAY=:0 /usr/bin/xset s off

# hide cursor
# apt install unclutter
DISPLAY=:0 /usr/bin/unclutter -idle 0 &

