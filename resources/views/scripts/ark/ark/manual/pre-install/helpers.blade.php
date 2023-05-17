#!/usr/bin/env bash

readNonempty() {
    prompt=${1}
    answer=""
    while [ -z "${answer}" ] ; do
        read -p "${prompt}" answer
    done
    echo "${answer}"
}
