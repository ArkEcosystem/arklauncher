curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::FETCH_CONFIG }}" > /dev/null 2>&1

mkdir -p $HOME/.config/{{ $token }}-core/{{ $network }}

cd $HOME/.config/{{ $token }}-core/{{ $network }}

{{-- Fetch, decrypt and uncompress the config files from the genesis server --}}
curl -o config.zip "{!! $getConfigUrl !!}"
unzip -q config.zip

{{-- Remove install file that is part of the zip and zip itself --}}
rm install.sh
rm config.zip

{{-- Creates an empty `delegates.json` that doesnt come with the fetched config --}}
cat > delegates.json << EOF
{
    "secrets": []
}
EOF

{{-- Back to home directory as next --}}
cd
