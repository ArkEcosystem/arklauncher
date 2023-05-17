echo "------------------------------------"
echo "Passphrase Details"
echo "------------------------------------"
if [ -d "$HOME/.config/{{ $token }}-core/{{ $network }}" ]; then
    PASSPHRASE=$(sh -c "jq '.passphrase' $HOME/.config/{{ $token }}-core/{{ $network }}/genesis-wallet.json")
    ADDRESS=$(sh -c "jq '.address' $HOME/.config/{{ $token }}-core/{{ $network }}/genesis-wallet.json")

    echo "Your {{ $network }} Genesis Details are:"
    echo "  Passphrase: $PASSPHRASE"
    echo "  Address: $ADDRESS"
    echo ""
    echo "You can find the genesis wallet passphrase in '$HOME/.config/{{ $token }}-core/{{ $network }}/genesis-wallet.json'"
    echo "You can find the delegates.json passphrase file at '$HOME/.config/{{ $token }}-core/{{ $network }}/delegates.json'"
else
    echo "Could not find your {{ $network }} config"
fi
echo "------------------------------------"
