#!/bin/bash

uuid=`/usr/bin/cat /proc/cpuinfo | /usr/bin/grep Serial | /usr/bin/cut -d ' ' -f 2`
host_name="bodycam-${uuid}"
echo $host_name | tee /etc/hostname
sed -i -E 's/^127.0.1.1.*/127.0.1.1\t'"$host_name"'/' /etc/hosts
/usr/bin/hostnamectl set-hostname $host_name
/usr/bin/systemctl restart avahi-daemon

/app/bodycam/common/modem_init.php
/app/bodycam/common/modem_checker.php &
