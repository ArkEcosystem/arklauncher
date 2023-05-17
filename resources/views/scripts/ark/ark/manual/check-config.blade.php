heading "Checking for Configuration Files"

cd "$HOME"

if [[ ! -f "app.json" || ! -f ".env" || ! -f "peers.json" ]]; then
    echo "One of the configuration files is missing!"
    exit 1
fi

if [ ! -d "$HOME/crypto" ]; then
    echo "Crypto configuration directory could not be found!"
    exit 1
fi

success "Configuration Files Found!"