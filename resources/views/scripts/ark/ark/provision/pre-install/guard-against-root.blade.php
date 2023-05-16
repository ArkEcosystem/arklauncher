if [ "$EID" == "0" ]; then
    echo "ARKLauncher installation must not be run as root!"
    exit 1
fi

# Ensure that no unwanted prompts show up
export DEBIAN_FRONTEND=noninteractive
