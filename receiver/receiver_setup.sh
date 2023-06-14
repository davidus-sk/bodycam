#!/bin/sh

#apt-get install unclutter

# turn off blanking and screen saver
DISPLAY=:0 /usr/bin/xset s noblank
DISPLAY=:0 /usr/bin/xset -dpms
DISPLAY=:0 /usr/bin/xset s off

# hide cursor
DISPLAY=:0 /usr/bin/unclutter -idle 0 &

