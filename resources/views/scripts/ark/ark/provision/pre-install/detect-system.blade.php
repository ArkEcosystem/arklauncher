# Detect pkg type
DEB=$(which apt-get)
RPM=$(which yum)

# Detect SystemV / SystemD
SYS=$([[ -L "/sbin/init" ]] && echo 'SystemD' || echo 'SystemV')

if [[ -n $DEB ]]; then
    success "Running install for Debian derivate"
else
    heading "Not supported system"
    exit 1;
fi
