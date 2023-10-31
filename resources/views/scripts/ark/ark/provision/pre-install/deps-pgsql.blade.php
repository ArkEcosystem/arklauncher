curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::INSTALLING_POSTGRESQL }}" > /dev/null 2>&1

heading "Installing PostgreSQL..."

apt_wait
sudo apt-get update

apt_wait
sudo apt-get install postgresql postgresql-contrib -y

success "Installed PostgreSQL!"
