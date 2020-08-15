FROM docker_wordpress-base:latest

ARG BRANCH

ENV DEFAULT_BRANCH master
ENV PLUGIN_PACKAGE_NAME yoti-wordpress-edge.zip

RUN if [ "$BRANCH" = "" ]; then \
  $BRANCH = $DEFAULT_BRANCH; \
fi

RUN set -ex; \
        git clone -b ${BRANCH} https://github.com/getyoti/yoti-wordpress.git --single-branch /usr/src/yoti-wordpress; \
        echo "Finished cloning ${BRANCH}"; \
        chown -R www-data:www-data /usr/src/yoti-wordpress; \
        cd /usr/src/yoti-wordpress; \
        ./bin/pack-plugin.sh; \
        echo "Finished packing the plugin"; \
        mv ./${PLUGIN_PACKAGE_NAME} /usr/src/wordpress/wp-content/plugins; \
        cd /usr/src/wordpress/wp-content/plugins; \
        unzip ${PLUGIN_PACKAGE_NAME}; \
        rm -f ${PLUGIN_PACKAGE_NAME}
