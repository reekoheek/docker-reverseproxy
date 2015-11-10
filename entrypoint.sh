#!/bin/bash
set -e

env

if [ "$USE_API" = "false" ]; then
  exec "$@"
else
  exec "/usr/bin/supervisord"
fi