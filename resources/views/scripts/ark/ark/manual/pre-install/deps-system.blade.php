heading "Installing system dependencies..."

echo 'libssl1.1 libraries/restart-without-asking boolean true' | sudo debconf-set-selections

apt_wait
sudo apt-get update

apt_wait
sudo apt-get install -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" -y --force-yes curl apt-transport-https update-notifier bc wget gnupg net-tools zip make build-essential

success "Installed system dependencies!"
