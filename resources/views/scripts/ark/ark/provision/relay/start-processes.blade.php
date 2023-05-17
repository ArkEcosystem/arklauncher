curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::STARTING_PROCESSES }}" > /dev/null 2>&1

ark relay:start --token="{{ $token }}" --network="{{ $network }}"
