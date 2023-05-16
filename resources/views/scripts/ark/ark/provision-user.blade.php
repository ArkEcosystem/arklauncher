curl -X POST "{!! $deploymentStatus !!}" -d "status=provisioning_user" > /dev/null 2>&1

shopt -s expand_aliases

# Ensure we are in a directory we can access

cd "$HOME"

# Create User

id -u "{{ $username }}" &>/dev/null || useradd --create-home --shell "/bin/bash" --user-group "{{ $username }}" --groups sudo

echo '{{ $username }}:{{ $user_password }}' | chpasswd
echo 'root:{{ $sudo_password }}' | chpasswd

# Remove Password Prompts

echo "{{ $username }} ALL=(ALL:ALL) NOPASSWD: ALL" | sudo tee -a /etc/sudoers

# Create SSH Keys

if [[ ! -e "/home/{{ $username }}/.ssh" ]]; then
    mkdir "/home/{{ $username }}/.ssh"
fi

touch "/home/{{ $username }}/.ssh/authorized_keys"

@foreach($authorizedKeys as $authorizedKey)
echo "{{ $authorizedKey }}" >> "/home/{{ $username }}/.ssh/authorized_keys"
@endforeach

touch /home/{{ $username }}/.ssh/id_rsa
touch /home/{{ $username }}/.ssh/id_rsa.pub

echo "{{ $privateKey }}" >> /home/{{ $username }}/.ssh/id_rsa
echo "{{ $publicKey }}" >> /home/{{ $username }}/.ssh/id_rsa.pub

# Ownership

chown -R {{ $username }}:{{ $username }} /home/{{ $username }}
chmod -R 755 /home/{{ $username }}

chmod 700 /home/{{ $username }}/.ssh
chmod 644 /home/{{ $username }}/.ssh/authorized_keys
chmod 644 /home/{{ $username }}/.ssh/id_rsa.pub
chmod 600 /home/{{ $username }}/.ssh/id_rsa
