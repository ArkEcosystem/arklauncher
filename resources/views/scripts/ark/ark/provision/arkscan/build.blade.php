curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::BUILDING_EXPLORER }}" > /dev/null 2>&1

cd "{{ $explorerPath }}/docker"

cat > docker-compose-build.yml << 'EOF'
version: '2'
services:
  arkscan:
    build:
      context: ../
      dockerfile: docker/Dockerfile
    image: ardenthq/arkscan
    container_name: ark-arkscan
    restart: always
    ports:
      - '{{ $explorerPort }}:8898'
    working_dir: /var/www/arkscan
    networks:
      - arkscan
    volumes:
      - arkscan:/var/www/arkscan
    tty: true
    extra_hosts:
      host.docker.internal: host-gateway
networks:
  arkscan:
volumes:
  arkscan:
    driver_opts:
      type: none
      device: $PWD/../
      o: bind
EOF

# Build and boot the Explorer image
docker-compose -f docker-compose-build.yml up -d

DOCKER_STATUS=$?

if [ $DOCKER_STATUS -ne 0 ]; then
    curl -X POST "{!! $deploymentStatus !!}" -d "status={{ ServerDeploymentStatus::FAILED_BUILDING_EXPLORER }}" > /dev/null 2>&1
    echo "Failed to build the ARKscan's Docker image"

    exit 1
fi
