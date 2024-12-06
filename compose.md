# docker-compose命令
docker --version #查看版本

docker-compose -h    # 查看帮助

docker-compose up    # 启动所有docker-compose服务

docker-compose up -d    # 启动所有docker-compose服务并后台运行

docker-compose down     # 停止并删除容器、网络、卷、镜像。

docker-compose exec  yml里面的服务id   # 进入容器实例内部 docker-compose exec docker-compose.yml文件中写的服务id /bin/bash

docker-compose ps      # 展示当前docker-compose编排过的运行的所有容器

docker-compose top       # 展示当前docker-compose编排过的容器进程

docker-compose logs  yml里面的服务id   # 查看容器输出日志

docker-compose config     # 检查配置

docker-compose config -q  # 检查配置，有问题才有输出

docker-compose restart   # 重启服务

docker-compose start     # 启动服务

docker-compose stop      # 停止服务

# docker-compose 字段
version: '3' # docker-compose版本
services:
    mysql: #服务名
        container_name: kafka3 #容器名
        image: 
            mysql:5.5 #mysql镜像
        build: 
            ./user #这里为用户微服务文件夹，里面存放的是该服务代码jar包和Dockerfile文件
        ports: # 端口映射
            -"7000:7000"
        expose: # 暴露端口 供其他服务调用，只暴露，不映射
            -"7000"
        environment: #环境变量
            # INSIDE  
            MYSQL_ROOT_PASSWORD: 000000 #设置数据库密码
        volumes:
            - "$PWD/mysql/data:/var/lib/mysql" #数据卷挂载
        depends_on:
            - "redis" #依赖其他服务，先启动redis服务，再启动该服务