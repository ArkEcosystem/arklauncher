#!/usr/bin/env bash

@include('scripts.ark.ark.manual.pre-install.visuals')

@include('scripts.ark.ark.manual.check-config')

@include('scripts.ark.ark.manual.pre-install')

@include('scripts.ark.ark.manual.genesis.core.install-core')

@include('scripts.ark.ark.manual.genesis.core.config')

@include('scripts.ark.ark.manual.shared.configure-database')

@include('scripts.ark.ark.manual.shared.create-alias')
