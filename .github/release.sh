#!/bin/bash
VERSION=$1
git tag -a "v$VERSION" -m "Release version $VERSION"
git push origin "v$VERSION"
echo "Tagged and pushed v$VERSION"