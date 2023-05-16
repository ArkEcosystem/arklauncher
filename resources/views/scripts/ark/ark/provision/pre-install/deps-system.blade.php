curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::INSTALLING_SYSTEM_DEPENDENCIES }}" > /dev/null 2>&1

heading "Installing system dependencies..."

echo 'libssl1.1 libraries/restart-without-asking boolean true' | sudo debconf-set-selections

sudo apt-get update
sudo apt-get install -y git curl apt-transport-https update-notifier

success "Installed system dependencies!"
