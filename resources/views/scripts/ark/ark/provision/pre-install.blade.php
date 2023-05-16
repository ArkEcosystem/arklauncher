shopt -s expand_aliases

curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::PROVISIONING }}" > /dev/null 2>&1

cd "$HOME"

@include('scripts.ark.ark.provision.pre-install.visuals')

@include('scripts.ark.ark.provision.pre-install.helpers')

@include('scripts.ark.ark.provision.pre-install.guard-against-root')

@include('scripts.ark.ark.provision.pre-install.detect-system')

@include('scripts.ark.ark.provision.pre-install.configure-locale')

@include('scripts.ark.ark.provision.pre-install.await-apt-unlock')

@include('scripts.ark.ark.provision.pre-install.deps-system')

@include('scripts.ark.ark.provision.pre-install.deps-node')

@include('scripts.ark.ark.provision.pre-install.deps-yarn')

@include('scripts.ark.ark.provision.pre-install.export-paths')

@include('scripts.ark.ark.provision.pre-install.deps-pm2')

@include('scripts.ark.ark.provision.pre-install.deps-program')

@include('scripts.ark.ark.provision.pre-install.deps-pgsql')

@include('scripts.ark.ark.provision.pre-install.deps-ntp')

@include('scripts.ark.ark.provision.pre-install.deps-jemalloc')

@include('scripts.ark.ark.provision.pre-install.update-system')

@include('scripts.ark.ark.provision.pre-install.secure-server')

@include('scripts.ark.ark.provision.pre-install.unattended-security-upgrades')

@include('scripts.ark.ark.provision.pre-install.accept-github-remote')

PUBLIC_IP=$(sudo ifconfig | fgrep "inet " | fgrep -v "inet 127." | egrep -o "inet ([0-9]+\.?){4}" | awk '{print $2}' | head -n 1)
