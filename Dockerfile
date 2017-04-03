# version 1.0


FROM centos:latest
MAINTAINER Milo <cutadra@gmail.com>

# ENV container docker

# RUN (cd /lib/systemd/system/sysinit.target.wants/; for i in *; do [ $i == \
# systemd-tmpfiles-setup.service ] || rm -f $i; done); \
# rm -f /lib/systemd/system/multi-user.target.wants/*;\
# rm -f /etc/systemd/system/*.wants/*;\
# rm -f /lib/systemd/system/local-fs.target.wants/*; \
# rm -f /lib/systemd/system/sockets.target.wants/*udev*; \
# rm -f /lib/systemd/system/sockets.target.wants/*initctl*; \
# rm -f /lib/systemd/system/basic.target.wants/*;\
# rm -f /lib/systemd/system/anaconda.target.wants/*;

WORKDIR /root/


RUN mv /etc/yum.repos.d/CentOS-Base.repo /etc/yum.repos.d/CentOS-Base.repo.backup
#COPY nginx-7.repo /etc/yum.repos.d/nginx.repo
COPY Centos-7.repo /etc/yum.repos.d/CentOS-Base.repo


RUN mkdir -p /data/www/localhost/logs
RUN mkdir -p /data/www/localhost/test
RUN mkdir -p /usr/lib/php/pear

COPY pear/pworks /usr/lib/php/pear/pworks
COPY demo /data/www/localhost/htdocs
COPY test /data/www/localhost/test
COPY nginx.htaccess /data/www/localhost/htdocs/.htaccess

RUN yum clean all
RUN yum -y makecache

RUN yum install -y epel-release; yum clean all
RUN yum -y makecache

RUN yum install -y nginx; yum clean all
RUN yum install -y php php-fpm; yum clean all

RUN yum install -y php-xml php-pecl-apcu php-phpunit-PHPUnit php-pdo php-mysqlnd php-mcrypt php-mbstring; yum clean all
RUN yum install -y php-pecl-gearman php-pecl-redis; yum clean all

RUN yum install -y vim; yum clean all

#RUN yum install -y openssh-server; yum clean all

ENV TERM linux
ENV LC_ALL en_US.UTF-8

EXPOSE 80 443

COPY nginx.conf /etc/nginx/nginx.conf
COPY nginx.localhost.conf /etc/nginx/conf.d/localhost.conf

# RUN sed -i 's/UsePAM yes/UsePAM no/g' /etc/ssh/sshd_config
# CMD ["/usr/sbin/init"]
