FROM ubuntu:19.04

RUN sed -i "s@http://\(security.ubuntu.com\|archive.ubuntu.com\)@http://mirrors.aliyun.com@g" /etc/apt/sources.list \
    && apt update \
    && apt install -y python3-distutils wget

ARG  PIP_SOURCE
ARG  HA_VERSION=dev

RUN if [ $PIP_SOURCE ] ; then \
    mkdir -p ~/.pip/ \
    && echo "[global]" > ~/.pip/pip.conf \
    && echo "index-url = $PIP_SOURCE" >> ~/.pip/pip.conf \
    && echo "[install]" >> ~/.pip/pip.conf \
    && echo "trusted-host = $PIP_SOURCE" | sed -r "s@http://([^/]*)/.*@\1@g" >> ~/.pip/pip.conf \
    ; fi

RUN mkdir -p ~/tmp \
    && cd ~/tmp \
    && wget https://bootstrap.pypa.io/get-pip.py -O get-pip.py \
    && python3 get-pip.py

RUN cd ~/tmp \
    && wget https://github.com/home-assistant/home-assistant/archive/$HA_VERSION.tar.gz -O hass.tar.gz \
    && tar zxf hass.tar.gz \
    && cd home-assistant-$HA_VERSION \
    && mkdir -p /usr/src/homeassistant \
    && mv homeassistant /usr/src/homeassistant/ \
    && mv setup.py /usr/src/homeassistant/ \
    && mkdir -p /usr/src/homeassistant/config

COPY debug.py get_requirements.py /usr/src/homeassistant/
COPY .vscode /usr/src/homeassistant/.vscode
COPY docker-entrypoint /usr/local/bin/

RUN cd /usr/src/homeassistant/ \
    && python3 get_requirements.py \
    && pip install -r requirements.txt

RUN apt install -y openssh-server \
    && mkdir -p /var/run/sshd \
    && sed -i "s/#PermitRootLogin prohibit-password/PermitRootLogin yes/g" /etc/ssh/sshd_config

COPY id_rsa.pub /root/tmp/tmp.pub

RUN cd ~/tmp \
    && mkdir -p ~/.ssh \
    && chmod 700 ~/.ssh \
    && cat tmp.pub >> ~/.ssh/authorized_keys \
    && chmod 600 ~/.ssh/authorized_keys

RUN rm -rf ~/tmp \
    && apt clean

WORKDIR /usr/src/homeassistant/

ENTRYPOINT ["docker-entrypoint"]

# ENTRYPOINT ["/usr/sbin/sshd", "-D"]
# ENTRYPOINT ["/bin/sh", "-c", "while sleep 1000; do :; done"]