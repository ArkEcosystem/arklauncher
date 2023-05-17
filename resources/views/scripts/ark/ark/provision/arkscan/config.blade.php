curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::CONFIGURING_EXPLORER }}" > /dev/null 2>&1

# Generate .env file

cd "{{ $explorerPath }}"

cat > .env << EOF
# Application Details
APP_NAME="{{ strtoupper($name) }} {{ ucfirst($network) }} Explorer"
APP_NAVBAR_NAME="{{ strtoupper($name) }}"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL="http://{{ $ipAddress }}:{{ $explorerPort }}"

# Drivers for Web Application
DB_CONNECTION=sqlite
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# ArkScan - Database
ARKSCAN_NETWORK={{ App\Enums\NetworkTypeEnum::alias($network) }}
ARKSCAN_DB_HOST={{ ($databaseHost === '127.0.0.1' || $databaseHost === 'localhost') ? 'host.docker.internal' : $databaseHost }}
ARKSCAN_DB_PORT={{ $databasePort }}
ARKSCAN_DB_DATABASE={{ $databaseName }}_{{ $network }}
ARKSCAN_DB_USERNAME={{ $username }}
ARKSCAN_DB_PASSWORD=password

# Development
DEBUGBAR_ENABLED=false
RESPONSE_CACHE_ENABLED=false
TELESCOPE_ENABLED=false
DARK_MODE_ENABLED=true

# ArkScan - Network
ARKSCAN_NETWORK_NAME="{{ $name }}"
ARKSCAN_NETWORK_ALIAS={{ $network }}
ARKSCAN_NETWORK_API="http://{{ $ipAddress }}:{{ $apiPort }}/api"
ARKSCAN_NETWORK_CURRENCY={{ $token }}
ARKSCAN_NETWORK_CURRENCY_SYMBOL={{ $symbol }}
ARKSCAN_NETWORK_CONFIRMATIONS={{ $forgers }}
ARKSCAN_NETWORK_KNOWN_WALLETS=null
ARKSCAN_NETWORK_DELEGATE_COUNT={{ $forgers }}
ARKSCAN_NETWORK_BLOCK_TIME={{ $blocktime }}
ARKSCAN_NETWORK_BLOCK_REWARD={{ $rewardPerBlock }}
ARKSCAN_NETWORK_EPOCH="{{ $epoch }}"
ARKSCAN_NETWORK_BASE58_PREFIX={{ $addressPrefix }}
EOF
