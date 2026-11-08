#!/usr/bin/env bash
# Build the WordPress.org release zips into dist/:
#   dist/arponsamanta-fitness-toolkit.zip   (plugin, without dev files listed in .distignore)
#   dist/fitbull.zip        (theme)
# Usage: bin/package.sh   (run from the repo root; builds the blocks first)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DIST="$ROOT/dist"
rm -rf "$DIST" && mkdir -p "$DIST/stage"

( cd "$ROOT/plugins/arponsamanta-fitness-toolkit" && npm ci --silent && npm run build --silent )

copy_clean () { # src dest ignore-file
	mkdir -p "$2"
	( cd "$1" && tar cf - --exclude-from="$3" . ) | ( cd "$2" && tar xf - )
}

copy_clean "$ROOT/plugins/arponsamanta-fitness-toolkit" "$DIST/stage/arponsamanta-fitness-toolkit" "$ROOT/plugins/arponsamanta-fitness-toolkit/.distignore"
copy_clean "$ROOT/themes/fitbull" "$DIST/stage/fitbull" "$ROOT/themes/fitbull/.distignore"

( cd "$DIST/stage" && zip -qr "$DIST/arponsamanta-fitness-toolkit.zip" arponsamanta-fitness-toolkit && zip -qr "$DIST/fitbull.zip" fitbull )
rm -rf "$DIST/stage"
ls -lh "$DIST"
