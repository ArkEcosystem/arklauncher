shopt -s expand_aliases

cd "$HOME"

@include('scripts.ark.ark.manual.pre-install.helpers')

@include('scripts.ark.ark.manual.pre-install.guard-against-root')

@include('scripts.ark.ark.manual.pre-install.detect-system')

@include('scripts.ark.ark.manual.pre-install.configure-locale')

@include('scripts.ark.ark.manual.pre-install.await-apt-unlock')

@include('scripts.ark.ark.manual.pre-install.deps-system')

@include('scripts.ark.ark.manual.pre-install.deps-node')

@include('scripts.ark.ark.manual.pre-install.deps-yarn')

@include('scripts.ark.ark.manual.pre-install.export-paths')

@include('scripts.ark.ark.manual.pre-install.deps-pm2')

@include('scripts.ark.ark.manual.pre-install.deps-program')

@include('scripts.ark.ark.manual.pre-install.deps-pgsql')

@include('scripts.ark.ark.manual.pre-install.deps-ntp')

@include('scripts.ark.ark.manual.pre-install.deps-jemalloc')

@include('scripts.ark.ark.manual.pre-install.update-system')

PUBLIC_IP=$(sudo ifconfig | fgrep "inet " | fgrep -v "inet 127." | egrep -o "inet ([0-9]+\.?){4}" | awk '{print $2}' | head -n 1)
