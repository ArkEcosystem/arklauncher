# setup postgres username, password and database
read -p "Would you like to configure the database? [y/N]: " choice

if [[ "$choice" =~ ^(yes|y|Y) ]]; then
    choice=""
    while [[ ! "$choice" =~ ^(yes|y|Y) ]] ; do
        databaseUsername=$(readNonempty "Enter the database username: ")
        databasePassword=$(readNonempty "Enter the database password: ")
        databaseName=$(readNonempty "Enter the database name: ")

        echo "database username: ${databaseUsername}"
        echo "database password: ${databasePassword}"
        echo "database name: ${databaseName}"
        read -p "Proceed? [y/N]: " choice
    done

    ark env:set --key=CORE_DB_USERNAME --value="${databaseUsername}" --token="{{ $token }}" --network="{{ $network }}"
    ark env:set --key=CORE_DB_PASSWORD --value="${databasePassword}" --token="{{ $token }}" --network="{{ $network }}"
    ark env:set --key=CORE_DB_DATABASE --value="${databaseName}" --token="{{ $token }}" --network="{{ $network }}"

    userExists=$(sudo -i -u postgres psql -tAc "SELECT 1 FROM pg_user WHERE usename = '${databaseUsername}'")
    databaseExists=$(sudo -i -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname = '${databaseName}'")

    if [[ $userExists == 1 ]]; then
        read -p "The database user ${databaseUsername} already exists, do you want to recreate it? [y/N]: " choice

        if [[ "$choice" =~ ^(yes|y|Y) ]]; then
            if [[ $databaseExists == 1 ]]; then
                sudo -i -u postgres psql -c "ALTER DATABASE ${databaseName} OWNER TO postgres;"
            fi
            sudo -i -u postgres psql -c "DROP USER ${databaseUsername}"
            sudo -i -u postgres psql -c "CREATE USER ${databaseUsername} WITH PASSWORD '${databasePassword}' CREATEDB;"
        fi
    else
        sudo -i -u postgres psql -c "CREATE USER ${databaseUsername} WITH PASSWORD '${databasePassword}' CREATEDB;"
    fi

    if [[ $databaseExists == 1 ]]; then
        read -p "The database ${databaseName} already exists, do you want to overwrite it? [y/N]: " choice

        if [[ "$choice" =~ ^(yes|y|Y) ]]; then
            sudo -i -u postgres psql -c "DROP DATABASE ${databaseName};"
            sudo -i -u postgres psql -c "CREATE DATABASE ${databaseName} WITH OWNER ${databaseUsername};"
        elif [[ "$choice" =~ ^(no|n|N) ]]; then
            sudo -i -u postgres psql -c "ALTER DATABASE ${databaseName} OWNER TO ${databaseUsername};"
        fi
    else
        sudo -i -u postgres psql -c "CREATE DATABASE ${databaseName} WITH OWNER ${databaseUsername};"
    fi
fi
