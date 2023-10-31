heading "Installing node.js & npm..."

sudo rm -rf /usr/local/{lib/node{,/.npm,_modules},bin,share/man}/{npm*,node*,man1/node*}
sudo rm -rf ~/{.npm,.forever,.node*,.cache,.nvm}

(echo -e "Package: nodejs\nPin: origin deb.nodesource.com\nPin-Priority: 999" | sudo tee /etc/apt/preferences.d/nodesource)
curl -sL  https://deb.nodesource.com/gpgkey/nodesource.gpg.key | gpg --dearmor | sudo tee /usr/share/keyrings/nodesource.gpg >/dev/null
(echo "deb [signed-by=/usr/share/keyrings/nodesource.gpg] https://deb.nodesource.com/node_18.x ${DEB_ID} main" | sudo tee /etc/apt/sources.list.d/nodesource.list)

apt_wait
sudo apt-get update

apt_wait
sudo DEBIAN_FRONTEND=noninteractive NEEDRESTART_MODE=a apt-get install nodejs -yq

success "Installed node.js & npm!"
