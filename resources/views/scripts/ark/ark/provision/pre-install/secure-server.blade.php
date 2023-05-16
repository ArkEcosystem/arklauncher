curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::SECURING_NODE }}" > /dev/null 2>&1

heading "Securing Node..."

# apply security recommendations
# https://docs.ark.io/tutorials/node/secure.html#ssh-security
sudo sed -i.bak \
-e 's/^#PermitRootLogin yes/PermitRootLogin no/' \
-e 's/PermitRootLogin yes/PermitRootLogin no/' \
-e 's/^#PermitEmptyPasswords .*/PermitEmptyPasswords no/' \
-e 's/PermitEmptyPasswords .*/PermitEmptyPasswords no/' \
-e 's/^#LoginGraceTime .*/LoginGraceTime 60/' \
-e 's/LoginGraceTime .*/LoginGraceTime 60/' \
-e 's/^#X11Forwarding yes/X11Forwarding no/' \
-e 's/X11Forwarding yes/X11Forwarding no/' \
-e 's/^#MaxStartups .*/MaxStartups 2/' \
-e 's/MaxStartups .*/MaxStartups 2/' \
/etc/ssh/sshd_config

# Disable Password Authentication Over SSH

sed -i "/PasswordAuthentication yes/d" /etc/ssh/sshd_config
echo "" | sudo tee -a /etc/ssh/sshd_config
echo "" | sudo tee -a /etc/ssh/sshd_config
echo "PasswordAuthentication no" | sudo tee -a /etc/ssh/sshd_config

# Set custom SSH port if specified
if [[ ! -z $PROVISION_SSH_PORT ]]; then
    sudo sed -i.bak \
    -e "s/^#Port .*/Port $PROVISION_SSH_PORT/" \
    -e "s/Port .*/Port $PROVISION_SSH_PORT/" \
    /etc/ssh/sshd_config
    sudo ufw allow ${PROVISION_SSH_PORT}/tcp
else
    sudo ufw allow 22/tcp
fi

# Configure core ports
# webhooks and JSON RPC ports are disabled by default, users will have to manually enable the feature and open the port

@if(!empty($p2pPort))
sudo ufw allow {{ $p2pPort }}/tcp
@endif

@if(!empty($apiPort) && ! $server->isForger())
sudo ufw allow {{ $apiPort }}/tcp
@elseif(!empty($apiPort) && $server->isForger())
sudo ufw deny {{ $apiPort }}/tcp
@endif

@if(!empty($explorerPort) && ($server->isGenesis() || $server->isExplorer()))
sudo ufw allow {{ $explorerPort }}/tcp
@endif

sudo apt install -y ufw fail2ban
sudo systemctl restart sshd.service
sudo ufw --force enable

success "Server Secured!"
