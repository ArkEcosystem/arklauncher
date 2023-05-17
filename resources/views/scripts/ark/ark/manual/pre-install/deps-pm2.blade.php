heading "Installing PM2..."

sudo yarn global add pm2
pm2 install pm2-logrotate
pm2 set pm2-logrotate:max_size 500M
pm2 set pm2-logrotate:compress true
pm2 set pm2-logrotate:retain 7

success "Installed PM2!"
