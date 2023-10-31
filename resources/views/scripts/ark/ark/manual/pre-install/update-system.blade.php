heading "Installing system updates..."

apt_wait
sudo apt-get update

apt_wait
sudo DEBIAN_FRONTEND=noninteractive apt-get upgrade -yqq

apt_wait
sudo apt-get dist-upgrade -yqq

apt_wait
sudo apt-get autoremove -yyq

apt_wait
sudo apt-get autoclean -yq

success "Installed system updates!"
