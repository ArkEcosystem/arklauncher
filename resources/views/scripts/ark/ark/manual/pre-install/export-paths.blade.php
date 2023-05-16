heading "Export Paths..."

# Ensure that yarn is available for later use
export PATH="$(yarn global bin):$PATH"

if [ -f "$HOME/.bash_profile" ]; then
    echo 'export PATH="$HOME/bin:$HOME/.local/bin:$(yarn global bin):$PATH"' >> "$HOME/.bash_profile"
elif [ -f "$HOME/.bashrc" ]; then
    echo 'export PATH="$HOME/bin:$HOME/.local/bin:$(yarn global bin):$PATH"' >> "$HOME/.bashrc"
fi

if [ -f "$HOME/.profile" ]; then
    echo 'export PATH="$HOME/bin:$HOME/.local/bin:$(yarn global bin):$PATH"' >> "$HOME/.profile"
fi

success "Exported Paths!"
