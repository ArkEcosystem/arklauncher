# Wait For Apt To Unlock

apt_wait () {
    while sudo fuser /var/lib/dpkg/lock >/dev/null 2>&1 ; do
        echo "Waiting for dpkg/lock to be unlocked for sudo..."

        sleep 5
    done

    while sudo fuser /var/lib/dpkg/lock-frontend >/dev/null 2>&1 ; do
        echo "Waiting: dpk for lock-frontend to be unlocked for sudo..."

        sleep 5
    done

    while sudo fuser /var/lib/apt/lists/lock >/dev/null 2>&1 ; do
        echo "Waiting for lists/lock to be unlocked for sudo..."

        sleep 5
    done

    if [ -f /var/log/unattended-upgrades/unattended-upgrades.log ]; then
        while sudo fuser /var/log/unattended-upgrades/unattended-upgrades.log >/dev/null 2>&1 ; do
            echo "Waiting for unattended-upgrades to be unlocked for sudo..."

            sleep 5
        done
    fi

    while fuser /var/lib/dpkg/lock >/dev/null 2>&1 ; do
        echo "Waiting for dpkg/lock to be unlocked for user..."
        sleep 5
    done

    while fuser /var/lib/dpkg/lock-frontend >/dev/null 2>&1 ; do
        echo "Waiting for dpkg/lock-frontend to be unlocked for user..."
        sleep 5
    done

    while fuser /var/lib/apt/lists/lock >/dev/null 2>&1 ; do
        echo "Waiting for lists/lock to be unlocked for user..."
        sleep 5
    done

    if [ -f /var/log/unattended-upgrades/unattended-upgrades.log ]; then
        while fuser /var/log/unattended-upgrades/unattended-upgrades.log >/dev/null 2>&1 ; do
            echo "Waiting for unattended-upgrades to be unlocked for user..."
            sleep 5
        done
    fi
}

heading "Waiting for locks..."
apt_wait

sudo sed -i "s/#precedence ::ffff:0:0\/96  100/precedence ::ffff:0:0\/96  100/" /etc/gai.conf
if [ -f /etc/needrestart/needrestart.conf ]; then
    # Ubuntu 22 has this set to (i)nteractive, but we want (a)utomatic.
    sudo sed -i "s/^#\$nrconf{restart} = 'i';/\$nrconf{restart} = 'a';/" /etc/needrestart/needrestart.conf
fi
