#!/usr/bin/env bash

@include('scripts.ark.ark.provision.pre-install')

@include('scripts.ark.ark.provision.genesis.core.install-core')

@include('scripts.ark.ark.provision.shared.fetch-config')

@include('scripts.ark.ark.provision.shared.configure-database')

@include('scripts.ark.ark.provision.shared.create-alias')

@include('scripts.ark.ark.provision.seed.start-processes')

@include('scripts.ark.ark.provision.shared.create-boot-script')

@include('scripts.ark.ark.provision.post-install')

info "Public API: http://$PUBLIC_IP:{{ $apiPort }}/"
