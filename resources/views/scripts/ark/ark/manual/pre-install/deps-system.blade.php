heading "Installing system dependencies..."

echo 'libssl1.1 libraries/restart-without-asking boolean true' | sudo debconf-set-selections

sudo apt-get update
sudo apt-get install -y curl apt-transport-https update-notifier

success "Installed system dependencies!"
