#! /bin/bash

export RUN_PATH=`dirname "$0" || echo .`
set -a
. ${RUN_PATH}/_config.sh
set +a

docker stop ${KUBERNETES_DEPLOYMENT}
docker rm ${KUBERNETES_DEPLOYMENT}
docker run -ti --name ${KUBERNETES_DEPLOYMENT} \
  -v ${PWD}/lib:/metadata-import/lib \
  -v ${PWD}/bin:/metadata-import/bin \
  -v ${PWD}/etc:/metadata-import/etc \
  -v ${PWD}/var:/metadata-import/var \
  --link cassameta:cassandra \
  --env-file ENV ${IMAGE} bash


#docker logs -f ${KUBERNETES_DEPLOYMENT}


# --link devenv_cassandra_1:cassandra \
# --net devenv_default \
