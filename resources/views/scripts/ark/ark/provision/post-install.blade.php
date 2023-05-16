curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::PROVISIONED }}" > /dev/null 2>&1

PACKAGE_JSON_PATH="$(yarn global dir)/node_modules/@arkecosystem/core/package.json"

CORE_VERSION=$(cat $PACKAGE_JSON_PATH | jq -r .version)

curl -X POST "{!! $coreVersion !!}" -d "version=$CORE_VERSION" > /dev/null 2>&1
