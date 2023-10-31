cat << EOF | sudo tee /etc/apt/apt.conf.d/50unattended-upgrades > /dev/null
Unattended-Upgrade::Allowed-Origins {
    "Ubuntu jammy-security";
};
Unattended-Upgrade::Package-Blacklist {
    //
};
EOF

cat << EOF | sudo tee /etc/apt/apt.conf.d/10periodic > /dev/null
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Download-Upgradeable-Packages "1";
APT::Periodic::AutocleanInterval "7";
APT::Periodic::Unattended-Upgrade "1";
EOF
