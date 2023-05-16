heading "Configuring Core"

mkdir -p $HOME/.config/{{ $token }}-core/{{ $network }}/crypto

{{-- Move the configuration files to their correct locations --}}
mv .env app.json peers.json -t $HOME/.config/{{ $token }}-core/{{ $network }}

mv crypto/* $HOME/.config/{{ $token }}-core/{{ $network }}/crypto

cd $HOME/.config/{{ $token }}-core/{{ $network }}

{{-- Creates an empty `delegates.json` that doesnt come with the fetched config --}}
cat > delegates.json << EOF
{
    "secrets": []
}
EOF

{{-- Back to home directory --}}
cd

success "Configured ARK Core!"
