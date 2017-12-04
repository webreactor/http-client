
build: vendor

vendor:
	composer install

test: build
	# Starting test container
	if [ -n "$$(docker ps -a | grep http-test-container)" ]; then docker rm -f http-test-container; fi
	docker run -d --name http-test-container -p 9988:80 -v $(shell pwd):/test -v $(shell pwd)/_TESTS/fixes:/var/www webreactor/nginx-php:v0.0.2  > /dev/null
	# Waiting for nginx ...
	while [ "$$(curl -s -o /dev/null -I -w '%{http_code}' http://localhost:9988/)" -ne "200" ]; do true; done
	# Running tests
	docker run --rm --network host -v $(shell pwd):/workdir webreactor/nginx-php:v0.0.2 php /workdir/_TESTS/test.php
	# Removing container
	docker rm -f http-test-container > /dev/null

