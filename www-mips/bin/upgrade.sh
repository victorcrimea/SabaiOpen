#!/bin/ash
# Sabai Technology - Apache v2 licence
# Copyright 2015 Sabai Technology

echo "SABAI:> Simulate OS upgrade"
TMP_FILE='/tmp/upgrade/tmp.txt'

CURRENT_KERNEL=$(grub-editenv /mnt/grubenv list | grep boot_entry | awk -F "=" '{print $2}')
echo **Current kernel is $CURRENT_KERNEL > /dev/kmsg

#TODO transfer firmware archive to tmpfs
CHECK_DIR=`find /tmp -name upgrade`
if [ -d "$CHECK_DIR" ]; then
	echo Directory was allocated correct.
else
	echo ERROR 01 - Directory was not allocated.
	exit 1
fi

tar -C /tmp/upgrade -xf /tmp/upgrade/sabai-bundle-secured.tar
if [ -s "$CHECK_DIR/sabai-bundle.tar" ] && [ -e "$CHECK_DIR/signature" ]; then
	echo Firmware is ready for verification.
else
	echo ERROR 02 - Firmware is NOT ready for verification.
	exit 1
fi
openssl dgst -sha256 < /tmp/upgrade/sabai-bundle.tar > /tmp/upgrade/hash
openssl rsautl -verify -inkey /etc/sabai/keys/public.pem -keyform PEM -pubin -in /tmp/upgrade/signature > /tmp/upgrade/verified
cmp -l /tmp/upgrade/verified /tmp/upgrade/hash > "$TMP_FILE"
if [ -f "$TMP_FILE" ]; then
	OK=`cat "$TMP_FILE" | head -1`
        if [ "$OK" != "" ]; then
        	echo ERROR 03 - Verification failed. Go away bad guy!
        	exit 1
	else
                echo Verification finished with success!
        fi
else
        echo ERROR 04 - Error occured during verification.
fi

tar -C /tmp/upgrade -xf /tmp/upgrade/sabai-bundle.tar
gunzip /tmp/upgrade/rootfs-sabai-img.gz
mv /tmp/upgrade/rootfs-sabai-img /tmp/upgrade/rootfs-sabai.img
umount /dev/sda5
mount -t ext2 /dev/sda1 /mnt

if [ "$CURRENT_KERNEL" = "0" ]; then
	cp -f /tmp/upgrade/openwrt-x86-64-vmlinuz /mnt/boot/vmlinuz2
	dd if=/tmp/upgrade/rootfs-sabai.img of=/dev/sda3
else
	cp -f /tmp/upgrade/openwrt-x86-64-vmlinuz /mnt/boot/vmlinuz1
	dd if=/tmp/upgrade/rootfs-sabai.img of=/dev/sda2
fi
umount /dev/sda1
mount -t ext4 /dev/sda5 /mnt

grub-editenv /mnt/grubenv set prev_kernel=$CURRENT_KERNEL
if [ "$CURRENT_KERNEL" = "1" ]; then
        grub-editenv /mnt/grubenv set boot_entry=0
else
        grub-editenv /mnt/grubenv set boot_entry=1
fi
grub-editenv /mnt/grubenv set is_upgrade=1
umount /dev/sda5

rm /tmp/upgrade/*

echo "SABAI:> Booting new OS...."
reboot
