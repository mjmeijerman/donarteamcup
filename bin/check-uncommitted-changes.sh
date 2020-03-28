#!/bin/bash

if [ -z "$(git status --porcelain)" ]; then
  echo "Working directory clean"
else
  echo "Your working directory contains uncommitted changes. Please commit or stash these changes first"
  exit 1
fi
