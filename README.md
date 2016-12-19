feide-metadata-import
=====================

This repository holds the job that fetches SAML 2.0 metadata from international federations.
The only job of the repository is to fetch, parse & validate the metadata, and then store it in a Git repository.
Other jobs that needs access to the metadata can then read it from that repository.

This job is deployed to chores.uninett.no: [https://chores.uninett.no/job/feide-metadata-import/](https://chores.uninett.no/job/feide-metadata-import/)


Development / testing
---------------------

This section goes through the steps for running this script in a development environment.

### Creating temporary repository

Testing of this script must be done with a temporary repository.
A simple way to test is by creating a [new repository on scm.uninett.no](https://scm.uninett.no/projects/new).


### Run feide-metadata-import

```
./run_dev.sh 'git@scm.uninett.no:user/metadata-import-test.git'
```


## Run locally


docker



## Run cassandra

```
docker run --name cassameta -d cassandra:3.0
# docker exec -ti cassameta sh -c 'exec cqlsh "$CASSANDRA_PORT_9042_TCP_ADDR"'
docker exec -ti cassameta sh -c 'exec cqlsh'
docker exec -ti cassameta sh -c 'exec cqlsh' < etc/init.cql
```



> -cassandra:cassandra --rm cassandra sh -c 'exec cqlsh "$CASSANDRA_PORT_9042_TCP_ADDR"'
> ... or (simplified to take advantage of the /etc/hosts entry Docker adds for linked containers):
> $ docker run -it --link some-cassandra:cassandra --rm cassandra cqlsh cassandra
