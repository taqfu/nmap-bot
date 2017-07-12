nmap-bot

nmap-bot is a way to automatically scan your network using nmap and send you a push notification using Pushover.

In order to use it, you must fill out the appropriate information in nmap-bot.cfg.example and copy it to nmap-bot.cfg then fill out the mac addresses for your network in approved-macs.cfg


The script will occassionally give a sudo password prompt. This is not actually necessary and is just a bug. 

Root is required for certain kinds of scans which is why the config file requests root password.
