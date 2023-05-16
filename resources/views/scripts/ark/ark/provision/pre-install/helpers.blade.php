#!/usr/bin/env bash

system_add_user()
{
    # system_add_user(username, password, shell=/bin/bash)
    USERNAME=$1
    PASSWORD=$2
    SHELL=$4

    if [ -z "$4" ]; then
        SHELL="/bin/bash"
    fi

    useradd --create-home --shell "$SHELL" --user-group "$USERNAME"
    echo "$USERNAME:$PASSWORD" | chpasswd
    {{-- #require password change upon first login
    chage -d 0 $USERNAME --}}
}

system_get_user_home()
{
    # system_get_user_home(username)
    cat /etc/passwd | grep "^$1:" | cut --delimiter=":" -f6
}

system_user_add_ssh_key()
{
    # system_user_add_ssh_key(username, ssh_key)
    USERNAME=$1
    USER_HOME=`system_get_user_home "$USERNAME"`
    sudo -u "$USERNAME" mkdir "$USER_HOME/.ssh"
    sudo -u "$USERNAME" touch "$USER_HOME/.ssh/authorized_keys"
    sudo -u "$USERNAME" echo "$2" >> "$USER_HOME/.ssh/authorized_keys"
    chmod 0600 "$USER_HOME/.ssh/authorized_keys"
}

system_sshd_edit()
{
    # system_sshd_edit (param_name, "Yes"|"No")
    VALUE=$1
    if [ "$VALUE" == "yes" ] || [ "$VALUE" == "no" ]; then
        sed -i "s/^#*\($1\).*/\1 $VALUE/" /etc/ssh/sshd_config
    fi
}

system_sshd_permitrootlogin()
{
    system_sshd_edit "PermitRootLogin" "$1"
}
