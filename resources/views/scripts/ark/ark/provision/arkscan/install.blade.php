# Ensure wallet is synced to the database
ark env:set --key=CORE_WALLET_SYNC_ENABLED --value=true --token="{{ $token }}" --network="{{ $network }}"

# Install and setup Docker
@include ('scripts.ark.ark.provision.pre-install.deps-docker')

# Clone the ARKscan from GitHub
@include ('scripts.ark.ark.provision.arkscan.clone')

# Configure the ARKscan's environment variables
@include ('scripts.ark.ark.provision.arkscan.config')

# Build the ARKscan's Docker image
@include ('scripts.ark.ark.provision.arkscan.build')
