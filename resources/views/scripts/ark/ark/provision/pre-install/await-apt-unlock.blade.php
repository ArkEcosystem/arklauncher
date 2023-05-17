# Wait For Apt To Unlock

while fuser /var/lib/dpkg/lock >/dev/null 2>&1 ; do
    echo "Waiting for other software managers to finish..."

    sleep 1
done
