curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::INSTALLING_PROGRAM_DEPENDENCIES }}" > /dev/null 2>&1

heading "Installing program dependencies..."

apt_wait
sudo apt-get install build-essential libcairo2-dev pkg-config libtool autoconf automake python libpq-dev jq zip unzip -y

{{-- We need to install jq-1.6 because it contains fixes that we need later on --}}
wget https://github.com/stedolan/jq/releases/download/jq-1.6/jq-linux64 -O jq
chmod +x jq
sudo mv jq /usr/bin

success "Installed program dependencies!"
