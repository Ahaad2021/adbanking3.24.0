INSTALL_PREFIX = ${INSTALL_ROOT}/usr/share/adbanking
CONFIG_PREFIX = ${INSTALL_ROOT}

install:
	mkdir -p ${INSTALL_PREFIX}
	cp -af bin ${INSTALL_PREFIX}
	cp -af db ${INSTALL_PREFIX}
	cp -af web ${INSTALL_PREFIX}
	cp -af jasper ${INSTALL_PREFIX}
	cp -af conf/* ${CONFIG_PREFIX}
	mkdir -p ${CONFIG_PREFIX}/var/lib/adbanking
	mkdir -p ${CONFIG_PREFIX}/var/lib/adbanking/rapports/jasper
	mkdir -p ${CONFIG_PREFIX}/var/log/adbanking
clean:
	rm -rf ${INSTALL_PREFIX}/bin
	rm -rf ${INSTALL_PREFIX}/db
	rm -rf ${INSTALL_PREFIX}/web
	rm -rf ${INSTALL_PREFIX}/jasper
	rm -rf ${CONFIG_PREFIX}/var
