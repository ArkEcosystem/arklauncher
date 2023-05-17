curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::UPDATING_SYSTEM }}" > /dev/null 2>&1

heading "Installing system updates..."

sudo apt-get update
sudo DEBIAN_FRONTEND=noninteractive apt-get upgrade -yqq
sudo apt-get dist-upgrade -yqq
sudo apt-get autoremove -yyq
sudo apt-get autoclean -yq

success "Installed system updates!"
