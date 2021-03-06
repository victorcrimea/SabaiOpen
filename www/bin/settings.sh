#!/bin/ash
# Sabai Technology - Apache v2 licence
# Copyright 2015 Sabai Technology
UCI_PATH="-c /configs"
act=$1

_hostname(){
	name=$(uci get sabai.general.hostname)
	uci $UCI_PATH set system.@system[0].hostname="$(uci get sabai.general.hostname)";
	uci $UCI_PATH commit sabai
	echo $(uci get system.@system[0].hostname) > /proc/sys/kernel/hostname
}

_return(){
	echo "res={ sabai: $1, msg: '$2' };"
	exit 0
}

_reboot(){
	reboot
	_return 1 "Rebooting... Please wait about 60 seconds."
}

_halt(){
	halt
	_return 1 "Shut Down Complete"
}

_updatepass(){
	pass=$(cat /tmp/hold)
(
         echo $pass
         sleep 1
         echo $pass
)|passwd root
	rm /tmp/hold
	_return 1 "Password Changed"
}


case $act in
	hostname) _hostname ;;
	reboot)	_reboot	;;
	halt)	_halt	;;
	updatepass) _updatepass ;;
esac
