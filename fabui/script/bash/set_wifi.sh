#!/bin/bash

SSID=${1}
PASSWORD=${2}

CONFIG="ctrl_interface=DIR=/run/wpa_supplicant GROUP=netdev\nupdate_config=1\n\nnetwork={\n\tssid=\"$SSID\"\n"

if [ -z "$PASSWORD" ] ; then
	CONFIG="$CONFIG\tkey_mgmt=NONE\n"
else
	CONFIG="$CONFIG\tpsk=\"$PASSWORD\"\n"
fi
CONFIG="$CONFIG\tscan_ssid=1\n}"

sudo chmod 666 /etc/wpa_supplicant.conf
echo -e $CONFIG > /etc/wpa_supplicant.conf
sudo chmod 644 /etc/wpa_supplicant.conf
#restart interface
sudo ifdown wlan0
sudo ifup wlan0
#
sudo bash /var/www/fabui/script/bash/cron.sh &
sudo bash /var/www/fabui/script/bash/internet.sh &