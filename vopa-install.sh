
#!/bin/bash

echo "┌─────────────────────────────────────────┐"
echo "| This script might take a while,         |"
echo "| so if you dont see much progress,       |"
echo "| wait till you see --all done-- message. |"
echo "└─────────────────────────────────────────┘"
read -rsp $'Press any key to continue...\n' -n1 key

echo "┌───────────────────┐"
echo "| Installing System |"
echo "└───────────────────┘"
curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
apt install nodejs nginx git hostapd dnsmasq -y

wget -q https://raw.githubusercontent.com/Ecloserie-Numerique/Vopa-System/master/default_nginx -O /etc/nginx/sites-enabled/default
wget -q https://raw.githubusercontent.com/Ecloserie-Numerique/Vopa-System/master/dhcpcd.conf -O /etc/dhcpcd.conf
wget -q https://raw.githubusercontent.com/Ecloserie-Numerique/Vopa-System/master/dnsmasq.conf -O /etc/dnsmasq.conf
wget -q https://raw.githubusercontent.com/Ecloserie-Numerique/Vopa-System/master/hostapd.conf -O /etc/hostapd/hostapd.conf
wget -q https://raw.githubusercontent.com/Ecloserie-Numerique/Vopa-System/master/server.service -O /lib/systemd/system/server.service

update-rc.d dnsmasq defaults

sed -i -- 's/#DAEMON_CONF=""/DAEMON_CONF="\/etc\/hostapd\/hostapd.conf"/g' /etc/default/hostapd

iptables -t nat -A PREROUTING -s 192.168.24.0/24 -p tcp --dport 80 -j DNAT --to-destination 192.168.24.1:80
iptables -t nat -A POSTROUTING -j MASQUERADE
echo iptables-persistent iptables-persistent/autosave_v4 boolean true | sudo debconf-set-selections
echo iptables-persistent iptables-persistent/autosave_v6 boolean true | sudo debconf-set-selections
apt install iptables-persistent -y

systemctl unmask hostapd.service
systemctl enable hostapd.service

git clone https://github.com/Ecloserie-Numerique/Vopa-Server.git /home/pi/vopa-server
npm install --prefix /home/pi/vopa-server/
chown -R pi:pi /home/pi/vopa-server

systemctl daemon-reload
systemctl enable server

echo "┌─────────────────────────────────┐"
echo "| Please reboot your pi and test. |"
echo "└─────────────────────────────────┘"
echo "         --all done--"
