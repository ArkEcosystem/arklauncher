curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::INSTALLING_DOCKER }}" > /dev/null 2>&1

heading "Installing Docker..."

sudo apt-get install apt-transport-https ca-certificates curl software-properties-common
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -

sudo add-apt-repository  "deb [arch=amd64] https://download.docker.com/linux/ubuntu  $(lsb_release -cs) stable"
sudo apt-get update
sudo apt-get -y install docker-ce
sudo usermod -aG docker ${USER}
sudo chmod 666 /var/run/docker.sock
sudo systemctl restart docker

# Verify that Docker has been installed
docker --version

success "Installed Docker!"

heading "Installing Docker Compose..."

sudo curl -L https://github.com/docker/compose/releases/download/1.17.0/docker-compose-`uname -s`-`uname -m` -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify that Docker Compose has been installed
docker-compose --version

success "Installed Docker Compose!"

# Locate the paths to the PostgreSQL configuration files without having to know which version of PostgreSQL is installed
PG_HBA_FILE=$(sudo -u postgres psql -t -P format=unaligned -c 'show hba_file')
PG_CONFIG_FILE=$(sudo -u postgres psql -t -P format=unaligned -c 'show config_file')

# Allow Docker containers access to PostgreSQL database
sudo sed -i -e "s/#listen_addresses = 'localhost'/listen_addresses = '*'/" $PG_CONFIG_FILE
sudo sed -i -e "s/port = 5432/port = {!! $databasePort !!}/" $PG_CONFIG_FILE

# 172.16.0.0/12 will give the range of 172.16.0.0 to 172.31.255.255 which covers all IP addresses that Docker assigns a container
echo "host all all 172.16.0.0/12 md5" | sudo tee -a $PG_HBA_FILE
echo "host all all 192.168.0.0/16 md5" | sudo tee -a $PG_HBA_FILE

sudo systemctl restart postgresql.service

# Enable firewall rules to grant access to port {!! $databasePort !!} from outside world
sudo ufw allow from 10.0.0.0/8 proto tcp to any port {!! $databasePort !!}
sudo ufw allow from 172.16.0.0/12 proto tcp to any port {!! $databasePort !!}
sudo ufw allow from 192.168.0.0/16 proto tcp to any port {!! $databasePort !!}

success "Configured local network access!"
