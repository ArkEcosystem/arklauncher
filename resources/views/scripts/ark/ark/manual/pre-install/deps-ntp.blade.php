heading "Installing NTP..."

sudo timedatectl set-ntp off > /dev/null 2>&1 # disable the default systemd timesyncd service

apt_wait
sudo apt-get install ntp -yyq

sudo ntpd -gq

success "Installed NTP!"
