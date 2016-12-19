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

First setup Cassandra, then build and run.

```
bin/build.sh
bin/rundev.sh
```
