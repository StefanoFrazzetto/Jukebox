#!/bin/bash


if [ $# == 0 ]; then
	echo "Not enough arguments, at least 2 ports needed i.e 22"
	exit 1
fi

if [ $# == 1 ]; then
	echo "Port $1" > /etc/ssh/sshd_config;			
fi


if [ $# == 2 ]; then
	echo "Port $1" > /etc/ssh/sshd_config;	
	echo "Port $2" >> /etc/ssh/sshd_config;				
fi


if [ $# -gt 2 ]; then
		echo "to many arguments, maximum of 2 ports please"
	exit 1
fi


echo "Protocol 2" >> /etc/ssh/sshd_config;
echo "HostKey /etc/ssh/ssh_host_rsa_key" >> /etc/ssh/sshd_config;
echo "HostKey /etc/ssh/ssh_host_dsa_key" >> /etc/ssh/sshd_config;
echo "HostKey /etc/ssh/ssh_host_ecdsa_key" >> /etc/ssh/sshd_config;
echo "HostKey /etc/ssh/ssh_host_ed25519_key" >> /etc/ssh/sshd_config;
echo "UsePrivilegeSeparation yes" >> /etc/ssh/sshd_config;
echo "KeyRegenerationInterval 3600" >> /etc/ssh/sshd_config;
echo "ServerKeyBits 1024" >> /etc/ssh/sshd_config;
echo "SyslogFacility AUTH" >> /etc/ssh/sshd_config;
echo "LogLevel INFO" >> /etc/ssh/sshd_config;
echo "LoginGraceTime 120" >> /etc/ssh/sshd_config;
echo "PermitRootLogin yes" >> /etc/ssh/sshd_config;
echo "StrictModes yes" >> /etc/ssh/sshd_config;
echo "RSAAuthentication yes" >> /etc/ssh/sshd_config;
echo "PubkeyAuthentication yes" >> /etc/ssh/sshd_config;
echo "IgnoreRhosts yes" >> /etc/ssh/sshd_config;
echo "RhostsRSAAuthentication no" >> /etc/ssh/sshd_config;
echo "HostbasedAuthentication no" >> /etc/ssh/sshd_config;
echo "PermitEmptyPasswords no" >> /etc/ssh/sshd_config;
echo "ChallengeResponseAuthentication no" >> /etc/ssh/sshd_config;
echo "X11Forwarding yes" >> /etc/ssh/sshd_config;
echo "X11DisplayOffset 10" >> /etc/ssh/sshd_config;
echo "PrintMotd no" >> /etc/ssh/sshd_config;
echo "PrintLastLog yes" >> /etc/ssh/sshd_config;
echo "TCPKeepAlive yes" >> /etc/ssh/sshd_config;
echo "AcceptEnv LANG LC_*" >> /etc/ssh/sshd_config;
echo "Subsystem sftp /usr/lib/openssh/sftp-server" >> /etc/ssh/sshd_config;
echo "UsePAM yes" >> /etc/ssh/sshd_config;




sudo service ssh restart
