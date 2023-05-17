curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::INSTALLING_CORE }}" > /dev/null 2>&1

yarn global add @arkecosystem/core@latest
