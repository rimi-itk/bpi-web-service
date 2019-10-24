IMAGE_TAG = bpi-web-service/standalone:latest
CONTAINER_NAME = bpi-web-service
CONTAINER_ID := $(shell docker ps --all --filter 'name=bpi-web-service' --format '{{.ID}}')

docker-build:
	docker build --tag=$(IMAGE_TAG) .

docker-run:
	@[ -z "$(CONTAINER_ID)" ] || (docker stop $(CONTAINER_ID) && docker rm $(CONTAINER_ID))
	docker run --interactive --tty --publish 8888:80 --name $(CONTAINER_NAME) $(IMAGE_TAG)

.PHONY: docker-build docker-run
