# Detect pkg type
DEB=$(which apt-get)
RPM=$(which yum)

# Detect SystemV / SystemD
SYS=$([[ -L "/sbin/init" ]] && echo 'SystemD' || echo 'SystemV')

DEB_ID=$( (grep DISTRIB_CODENAME /etc/upstream-release/lsb-release || grep DISTRIB_CODENAME /etc/lsb-release || grep VERSION_CODENAME /etc/os-release) 2>/dev/null | cut -d'=' -f2 )

if [[ -n $DEB ]]; then
    success "Running install for Debian derivate"
else
    heading "Not supported system"
    exit 1;
fi
