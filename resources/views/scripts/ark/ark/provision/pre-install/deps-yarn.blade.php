curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::INSTALLING_YARN }}" > /dev/null 2>&1

heading "Installing Yarn..."

curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
(echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list)

sudo apt-get update
sudo apt-get install -y yarn

success "Installed Yarn!"
