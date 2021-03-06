#!/usr/bin/env bash

# Requires the following environment variables:
# $TRAVIS_BUILD_DIR = The directory of the project.
# $TRAVIS_COMMIT = The commit hash of the build.
# $REPO_URI = The URI of the Docker repo to tag the image with.
# $ENV_SECRET_ID = The ID of the .env file in AWS Secrets Manager (defaults to "env-api").
# $PUBLIC_KEY_SECRET_ID = The ID of the OAuth public key file in AWS Secrets Manager (default to "oauth-public-key").
# $PRIVATE_KEY_SECRET_ID = The ID of the OAuth private key file in AWS Secrets Manager (default to "oauth-private-key").
# $GCLOUD_SERVICE_KEY = The base64 encoded JSON file contents of the GCP service account.
# $BLACKFIRE_SERVER_ID = The Blackfire server ID.
# $BLACKFIRE_SERVER_TOKEN = The Blackfire server token.

# Bail out on first error.
set -e

# Package the app.
echo "Packaging the app..."
cd ${TRAVIS_BUILD_DIR}
# We can use `archive` which makes use of .gitattributes to `export-ignore`
# extraneous files.
git archive \--format=tar --worktree-attributes ${TRAVIS_COMMIT} \
  | tar -xf - -C ${TRAVIS_BUILD_DIR}/docker/app/packaged

# Production Build Steps.
echo "Installing composer dependencies..."
cd ${TRAVIS_BUILD_DIR}/docker/app/packaged
./develop composer install --no-dev --optimize-autoloader

echo "Installing NPM dependencies..."
./develop npm ci

echo "Compiling assets..."
./develop npm run prod
docker run --rm \
    -w /opt \
    -v ${TRAVIS_BUILD_DIR}/docker/app/packaged:/opt \
    ubuntu:16.04 bash -c "rm -rf node_modules"

# Get the .env file.
echo "Downloading .env file..."
gcloud secrets versions access latest \
  --secret=${ENV_SECRET_ID} > .env

# Get the OAuth keys.
echo "Downloading public OAuth key..."
gcloud secrets versions access latest \
  --secret=${PUBLIC_KEY_SECRET_ID} > storage/oauth-public.key

echo "Downloading private OAuth key..."
gcloud secrets versions access latest \
  --secret=${PRIVATE_KEY_SECRET_ID} > storage/oauth-private.key

# Save the GCP service-account.json file.
echo $GCLOUD_SERVICE_KEY | base64 --decode > service-account.json

# Build the Docker image with latest code.
echo "Building Docker images..."
cd ${TRAVIS_BUILD_DIR}/docker/app
docker build \
    --build-arg BLACKFIRE_SERVER_ID=$BLACKFIRE_SERVER_ID \
    --build-arg BLACKFIRE_SERVER_TOKEN=$BLACKFIRE_SERVER_TOKEN \
    -t ${REPO_URI}:latest \
    -t ${REPO_URI}:${TRAVIS_COMMIT} .

# Clean up packaged directory, but only if not in CI environment.
echo "Cleaning up..."
cd ${TRAVIS_BUILD_DIR}/docker/app
# The "vendor" directory (any any built assets!) will be owned as user "root" on
# the Linux file system. So we'll use Docker to delete them with a one-off
# container.
docker run --rm \
    -w /opt \
    -v ${TRAVIS_BUILD_DIR}/docker/app:/opt \
    ubuntu:16.04 bash -c "rm -rf ./packaged"

mkdir packaged
touch packaged/.gitkeep
