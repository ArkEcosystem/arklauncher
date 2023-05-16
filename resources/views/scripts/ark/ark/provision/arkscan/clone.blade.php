curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::CLONING_EXPLORER }}" > /dev/null 2>&1

git clone https://github.com/ArdentHQ/arkscan.git -b master "{{ $explorerPath }}" || GIT_PULL_FAILED="Y"
cd "{{ $explorerPath }}"
git checkout develop

if [ "$GIT_PULL_FAILED" == "Y" ]; then
    echo "Failed to fetch ARKscan repo at 'https://github.com/ArdentHQ/arkscan.git'"
    curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::FAILED_CLONING_EXPLORER }}" > /dev/null 2>&1

    exit 1
fi
