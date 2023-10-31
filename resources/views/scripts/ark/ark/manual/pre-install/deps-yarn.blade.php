heading "Installing Yarn..."

curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | sudo tee /usr/share/keyrings/yarnkey.gpg >/dev/null
(echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list)

apt_wait
sudo apt-get update

apt_wait
sudo DEBIAN_FRONTEND=noninteractive NEEDRESTART_MODE=a apt-get install yarn -yq

success "Installed Yarn!"
