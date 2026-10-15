#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

pnpm install

# npm arborist throws "Cannot read properties of null (reading 'matches')"
# when --package-lock-only walks pnpm's symlinked node_modules.
backup="$(mktemp -d)"
restore() {
	rm -rf node_modules
	if [ -d "$backup/node_modules" ]; then
		mv "$backup/node_modules" node_modules
	fi
	rm -rf "$backup"
}
trap restore EXIT

if [ -d node_modules ]; then
	mv node_modules "$backup/node_modules"
fi

npm install --package-lock-only --legacy-peer-deps
