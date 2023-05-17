curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::INSTALLING_POSTGRESQL }}" > /dev/null 2>&1

heading "Installing PostgreSQL..."

sudo apt-get update
sudo apt-get install postgresql postgresql-contrib -y

success "Installed PostgreSQL!"
