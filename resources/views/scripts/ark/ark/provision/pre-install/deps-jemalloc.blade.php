curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::INSTALLING_JEMALLOC }}" > /dev/null 2>&1

heading "Installing jemalloc..."

sudo apt-get install -y libjemalloc-dev

success "Installed jemalloc!"
