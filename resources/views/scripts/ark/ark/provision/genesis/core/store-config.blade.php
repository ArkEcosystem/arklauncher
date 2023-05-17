curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::STORE_CONFIG }}" > /dev/null 2>&1

cd $HOME/.config/{{ $token }}-core/{{ $network }}

{{-- Deactivate magistrate plugins before taking config snapshots --}}
jq 'del(.[].plugins[] | select(.package | contains("magistrate")))' ./app.json | tee ./app-edited.json && mv ./app-edited.json ./app.json

{{-- Download the installation script --}}
wget -O install.sh "{!! $installationScriptUrl !!}" --quiet

{{-- Compress and send config files --}}
zip -R config.zip 'install.sh' 'app.json' 'peers.json' 'crypto/*' '.env'

curl -X POST "{!! $storeConfigUrl !!}" \
    -F 'file=@./config.zip' > /dev/null 2>&1

{{-- Once stored we no longer needs these files --}}
rm config.zip
