#! /bin/bash

export RUN_PATH=`dirname "$0" || echo .`
set -a
. ${RUN_PATH}/_config.sh
set +a

docker stop ${KUBERNETES_DEPLOYMENT}
docker rm ${KUBERNETES_DEPLOYMENT}
docker run -ti --name ${KUBERNETES_DEPLOYMENT} \
  -v ${PWD}/metadata-import/lib:/metadata-import/lib \
  -v ${PWD}/metadata-import/getmetadata.php:/metadata-import/getmetadata.php \
  -v ${PWD}/metadata-import/test.php:/metadata-import/test.php \
  -v ${PWD}/metadata-import/testlogo.php:/metadata-import/testlogo.php \
  -v ${PWD}/metadata-import/config.php:/metadata-import/config.php \
  -v ${PWD}/metadata-import/var:/metadata-import/var \
  --link cassameta:cassandra \
  --env-file ENV ${IMAGE} bash
#docker logs -f ${KUBERNETES_DEPLOYMENT}
