#!/bin/bash
set -e

BUMP=${1:-patch}

# Fetch tags so we always have the latest
git fetch --tags --quiet

# Determine current version (strip leading 'v'), default to 0.0.0
CURRENT=$(git tag --list 'v*' --sort=-version:refname | head -1)
CURRENT=${CURRENT:-v0.0.0}
CURRENT=${CURRENT#v}

IFS='.' read -r MAJOR MINOR PATCH <<< "$CURRENT"

case "$BUMP" in
    major)
        MAJOR=$((MAJOR + 1))
        MINOR=0
        PATCH=0
        ;;
    minor)
        MINOR=$((MINOR + 1))
        PATCH=0
        ;;
    patch)
        PATCH=$((PATCH + 1))
        ;;
    *)
        echo "Usage: $0 [major|minor|patch]"
        echo "  Current version: v$CURRENT"
        exit 1
        ;;
esac

VERSION="$MAJOR.$MINOR.$PATCH"

echo "Current version : v$CURRENT"
echo "New version     : v$VERSION  ($BUMP bump)"
echo ""
read -rp "Confirm release v$VERSION? [y/N] " CONFIRM
if [[ "$CONFIRM" != "y" && "$CONFIRM" != "Y" ]]; then
    echo "Aborted."
    exit 0
fi

git tag -a "v$VERSION" -m "Release version $VERSION"
git push origin "v$VERSION"
echo "Tagged and pushed v$VERSION"