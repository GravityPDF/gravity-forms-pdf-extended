#!/usr/bin/env bash

if [[ -z "$GITHUB_ACTION" ]]; then
	echo "Script is only to be run by Github Action" 1>&2
	exit 1
fi

if [[ -z "$WP_ORG_PASSWORD" ]]; then
	echo "WordPress.org password not set" 1>&2
	exit 1
fi

PROJECT_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"
PLUGIN_BUILDS_PATH="$PROJECT_ROOT/tmp/package"

# Ensure the current build directory exists
if [ ! -d "$PLUGIN_BUILDS_PATH/$SOURCE_TAG" ]; then
    echo "Built directory $PLUGIN_BUILDS_PATH/$SOURCE_TAG does not exist" 1>&2
    exit 1
fi

# Check if the tag exists for the version we are building
TAG=$(svn ls "https://plugins.svn.wordpress.org/$PLUGIN/tags/$SOURCE_TAG")
error=$?
if [ $error == 0 ]; then
    # Tag exists, don't deploy
    echo "Tag already exists for version $SOURCE_TAG, aborting deployment"
    exit 1
fi

# Create Tags
echo "Begin Tag Deployment"
svn --no-auth-cache --non-interactive --username "$WP_ORG_USERNAME" --password "$WP_ORG_PASSWORD" mkdir "https://plugins.svn.wordpress.org/$PLUGIN/tags/$SOURCE_TAG" -m "Create tag $SOURCE_TAG"

cd "$PLUGIN_BUILDS_PATH"

# Checkout the SVN tag
svn co -q  "https://plugins.svn.wordpress.org/$PLUGIN/tags/$SOURCE_TAG" svn

# Add new version tag
rsync -r -p $SOURCE_TAG/* svn

# Add new files to SVN
svn stat svn | grep '^?' | awk '{print $2}' | xargs -I x svn add x@
# Remove deleted files from SVN
svn stat svn | grep '^!' | awk '{print $2}' | xargs -I x svn rm --force x@
svn stat svn

# Commit to SVN
svn ci --no-auth-cache --non-interactive --username "$WP_ORG_USERNAME" --password "$WP_ORG_PASSWORD" svn -m "Deploy version $SOURCE_TAG"

# Remove SVN temp dir
rm -fR svn

# Create Trunk
echo "End Tag Deployment"
