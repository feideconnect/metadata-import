# metadata-import

This repository holds the job that fetches SAML 2.0 metadata from international federations. The only job of the repository is to fetch, parse & validate the metadata, and then store it to a Cassandra store. Other jobs that needs access to the metadata can then read it from that repository.

## Development / testing

This section goes through the steps for running this script in a development environment.

## Run cassandra

```
docker pull cassandra:3.0
docker run --name cassameta -d cassandra:3.0
# docker exec -ti cassameta sh -c 'exec cqlsh "$CASSANDRA_PORT_9042_TCP_ADDR"'
docker exec -ti cassameta sh -c 'exec cqlsh'
docker exec -i cassameta sh -c 'exec cqlsh' < etc/init.cql
```

## Run locally

First setup and run Cassandra (as described above), then build and run the script like this:

```
bin/build.sh
bin/rundev.sh
```

### To run the test.php script for development

The rundev.sh opens a bash shell in the container. Run:

```
clear; ./test.php
```

rundev.sh also mounts the lib directory and the scripts. It means you can develop locally and re-test immediately.


To speed up processing, fetch a local cached XML:

curl -o /metadata-import/temp.xml http://mds.edugain.org
