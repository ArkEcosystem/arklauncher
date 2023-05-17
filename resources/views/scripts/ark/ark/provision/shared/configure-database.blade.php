curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::CONFIGURING_DATABASE }}" > /dev/null 2>&1

# Create User
OWNED_DATABASES=$(sudo -u postgres psql -c "\l" | fgrep " | $USER " | awk '{print $1}' | egrep "_(main|dev|test)net$") || true

for OWNED_DATABASE in $OWNED_DATABASES; do
    sudo -u postgres dropdb "$OWNED_DATABASE"
done

sudo -u postgres psql -c "DROP OWNED BY $USER; DROP USER IF EXISTS $USER" || true
sudo -u postgres psql -c "CREATE USER $USER WITH PASSWORD 'password';"
sudo -u postgres psql -c "ALTER USER $USER WITH SUPERUSER;"

# Create Databases
sudo -i -u postgres psql -c "CREATE DATABASE {{ $databaseName }}_{{ $network }} WITH OWNER $USER;"

# Configure Core
ark env:set --key=CORE_DB_USERNAME --value=$USER --token="{{ $token }}" --network="{{ $network }}"
ark env:set --key=CORE_DB_PASSWORD --value=password --token="{{ $token }}" --network="{{ $network }}"
ark env:set --key=CORE_DB_DATABASE --value="{{ $databaseName }}_{{ $network }}" --token="{{ $token }}" --network="{{ $network }}"
