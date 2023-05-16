curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::CREATING_BOOT_SCRIPT }}" > /dev/null 2>&1

PM2_OUTPUT=$(pm2 startup)

PM2_STARTUP=$(echo $PM2_OUTPUT | grep "command: " | sed "s/^.*command: //")

eval $PM2_STARTUP
