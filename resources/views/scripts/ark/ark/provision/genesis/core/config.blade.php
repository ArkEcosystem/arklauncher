curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::GENERATING_NETWORK_CONFIGURATION }}" > /dev/null 2>&1

# Build Configurations

ark network:generate \
    --network="{{ $network }}" \
    --premine="{{ $totalPremine }}" \
    --delegates="{{ $forgers }}" \
    --blocktime="{{ $blocktime }}" \
    --maxTxPerBlock="{{ $transactionsPerBlock }}" \
    --maxBlockPayload="{{ $maxBlockPayload }}" \
    --rewardHeight="{{ $rewardHeightStart }}" \
    --rewardAmount="{{ $rewardPerBlock }}" \
    --pubKeyHash="{{ AddressPrefixes::get(${$network.'Prefix'}) }}" \
    --wif="{{ $wif }}" \
    --token="{{ $token }}" \
    --symbol="{{ $symbol }}" \
    --explorer="http://{{ $explorerIp }}:{{ $explorerPort }}" \
    --distribute="false" \
    --epoch="{{ $epoch }}" \
    --vendorFieldLength="{{ $vendorFieldLength }}" \
    --feeStaticTransfer="{{ $fees['static']['transfer'] }}" \
    --feeStaticSecondSignature="{{ $fees['static']['secondSignature'] }}" \
    --feeStaticDelegateRegistration="{{ $fees['static']['delegateRegistration'] }}" \
    --feeStaticVote="{{ $fees['static']['vote'] }}" \
    --feeStaticMultiSignature="{{ $fees['static']['multiSignature'] }}" \
    --feeStaticIpfs="{{ $fees['static']['ipfs'] }}" \
    --feeStaticMultiPayment="{{ $fees['static']['multiPayment'] }}" \
    --feeStaticDelegateResignation="{{ $fees['static']['delegateResignation'] }}" \
    --feeDynamicEnabled="{{ $fees['dynamic']['enabled'] ? 'true' : 'false' }}" \
    @if ($fees['dynamic']['enabled'])
    --feeDynamicMinFeePool="{{ $fees['dynamic']['minFeePool'] }}" \
    --feeDynamicMinFeeBroadcast="{{ $fees['dynamic']['minFeeBroadcast'] }}" \
    --feeDynamicBytesTransfer="{{ $fees['dynamic']['addonBytes']['transfer'] }}" \
    --feeDynamicBytesSecondSignature="{{ $fees['dynamic']['addonBytes']['secondSignature'] }}" \
    --feeDynamicBytesDelegateRegistration="{{ $fees['dynamic']['addonBytes']['delegateRegistration'] }}" \
    --feeDynamicBytesVote="{{ $fees['dynamic']['addonBytes']['vote'] }}" \
    --feeDynamicBytesMultiSignature="{{ $fees['dynamic']['addonBytes']['multiSignature'] }}" \
    --feeDynamicBytesIpfs="{{ $fees['dynamic']['addonBytes']['ipfs'] }}" \
    --feeDynamicBytesMultiPayment="{{ $fees['dynamic']['addonBytes']['multiPayment'] }}" \
    --feeDynamicBytesDelegateResignation="{{ $fees['dynamic']['addonBytes']['delegateResignation'] }}" \
    @endif
    --coreDBHost="{{ $databaseHost }}" \
    --coreDBPort="{{ $databasePort }}" \
    --coreDBUsername="core" \
    --coreDBPassword="password" \
    --coreDBDatabase="{{ $databaseName }}_{{ $network }}" \
    --coreP2PPort="{{ $p2pPort }}" \
    --coreAPIPort="{{ $apiPort }}" \
    --coreWebhooksPort="{{ $webhookPort }}" \
    --coreMonitorPort="{{ $monitorPort }}" \
    --peers="$PUBLIC_IP" \
    --overwriteConfig \
    --force

