curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::STARTING_PROCESSES }}" > /dev/null 2>&1

ark relay:start --token="{{ $token }}" --network="{{ $network }}" --networkStart --ignoreMinimumNetworkReach --env=test &>> "$HOME/core.log"
ark forger:start --token="{{ $token }}" --network="{{ $network }}" --env=test &>> "$HOME/core.log"
