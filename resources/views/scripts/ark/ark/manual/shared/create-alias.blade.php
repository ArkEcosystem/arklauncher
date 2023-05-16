echo 'export LESS="-RS"' >> "$HOME/.bashrc"
echo '{{ $token }}() { $(yarn global dir)/node_modules/@arkecosystem/core/bin/run "$@" --token="{{ $token }}" --network="{{ $network }}"; }' >> "$HOME/.bashrc"

source "$HOME/.bashrc"
source "$HOME/.profile"
