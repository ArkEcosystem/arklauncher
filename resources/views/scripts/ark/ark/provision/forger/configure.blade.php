curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::CONFIGURING_FORGER }}" > /dev/null 2>&1

@if($passphrase ?? '')
    @if($passphraseMethod === 'bip39')
    ark config:forger --method={{ $passphraseMethod }} --bip39="{{ $passphrase }}" --token="{{ $token }}" --network="{{ $network }}"
    @else
    ark config:forger --method={{ $passphraseMethod }} --bip39="{{ $passphrase }}" --password="{{ $passphrasePassword }}" --token="{{ $token }}" --network="{{ $network }}"
    @endif
@endif
