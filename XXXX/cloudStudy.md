# 名词

| 缩写               | 具体含义                                                     |
| ------------------ | ------------------------------------------------------------ |
| RTT                | 循环时间（Round-Trip Time）                                  |
| Network Throughput | 网络吞吐量                                                   |
| CDN                | Content Distribution Network  内容分发网络                   |
| DCDN               | Dynamic Content Delivery Network  动态内容分发网络<br />（全站加速） |
|                    |                                                              |









# 学习云原生

https://www.thebyte.com.cn

演进路线：   物理机 -> 虚拟机 -> 容器

## 容器出现历史：
### 1. **chroot** 阶段：隔离文件系统

chroot 被认为是最早的容器技术之一，它能将进程的根目录重定向到某个新目录，复现某些特定环境，同时也将进程的文件读写权限限制在该目录内。
通过 chroot 隔离出来的新环境有一个形象的命名“Jail”（监狱），这便是容器最重要的特性 —— 隔离



### 2. LXC 阶段：封装系统

2006 年，Google 推出 Process Container（进程容器），Process Container 目的非常直白，它希望能够像虚拟化技术那样给进程提供操作系统级别的资源限制、优先级控制、资源审计和进程控制能力。

带着这样的设计思路，Process Container 推出不久就进入了 Linux 内核主干，不过由于 container 这一命名在内核中具有许多不同的含义，为了避免代码命名的混乱，后来就将 Process Container 更名为了 Control Groups —— 简称 cgroups。


2008 年，Linux 内核版本 2.6.24 刚开始提供 cgroups，社区开发者就将 cgroups 资源管理能力和 Linux namespace 资源隔离能力组合在一起，形成了完整的容器技术 LXC（Linux Container，Linux 容器）。

LXC 是如今被广泛应用的容器技术的实现基础，通过 LXC 可以在同一主机上运行多个相互隔离的 Linux 容器，每个容器都有自己的完整的文件系统、网络、进程和资源隔离环境，容器内的进程如同拥有一个完整、独享的操作系统。

至 2013 年，Linux 虚拟化技术已基本成型，通过 cgroups、namespace 以及安全防护机制，大体上解决了容器核心技术“运行环境隔离”，但此时仍需等待另一项关键技术的出现，才能迎来容器技术的全面繁荣。



### 3. Docker 阶段：封装应用

Docker 的核心创新“容器镜像（container image）”：

- **容器镜像打包了整个容器运行依赖的环境，以避免依赖运行容器的服务器的操作系统，从而实现“build once，run anywhere”**。
- **容器镜像一但构建完成，就变成只读状态，成为不可变基础设施的一份子**。
- 与操作系统发行版无关，核心解决的是容器进程对操作系统包含的库、工具、配置的依赖（注意，容器镜像无法解决容器进程对内核特性的特殊依赖）。

至此，现阶段容器技术体系已经解决了**最核心的两个问题“如何运行软件和如何发布软件”** ，云计算开始进入容器阶段。



### 4.OCI 阶段：容器标准化

先是 CoreOS 推出了自己的容器引擎 rkt（Rocket 的缩写），Google 也推出了自己的容器引擎 lmctfy（Let Me Contain That For You 的缩写）试图与 Docker 分庭抗礼，相互竞争的结果就是大家坐下来谈容器接口标准，避免出现“碎片化”的容器技术。

2015 年 6 月，Linux 基金会联合 Docker 带头成立 OCI（Open Container Initiative，开放容器标准）项目，**OCI 组织着力解决容器的构建、分发和运行标准问题，其宗旨是制定并维护 OCI Specifications（容器镜像格式和容器运行时的标准规范）**。

OCI 的成立结束了容器技术标准之争，Docker 公司被迫放弃容器规范独家控制权。作为回报，Docker 的容器格式被 OCI 采纳为新标准的基础，并且由 Docker 起草 OCI 草案规范的初稿。

当然这个“标准起草者”也不是那么好当的，Docker 需要提交自己的容器引擎源码作为启动资源。首先是 Docker 最初使用的容器引擎 libcontainer，这是 Docker 在容器运行时方面的核心组件之一 ，用于实现容器的创建、管理和运行。Docker 将 libcontainer 捐赠给了 OCI，作为 OCI 容器运行时标准的参考实现。



OCI 有了三个主要的标准：

- **OCI Runtime Spec**（容器运行时标准）：定义了运行一个容器，如何管理容器的状态和生命周期，如何使用操作系统的底层特性（namespace、cgroups、pivot_root 等）。
- **OCI Image Spec**（容器镜像标准）：定义了镜像的格式，配置（包括应用程序的参数、依赖的元数据格式、环境信息等），简单来说就是对镜像文件格式的描述。
- **OCI Distribution Spec**（镜像分发标准）：定义了镜像上传和下载的网络交互过程的规范。

而前面的 libcontainer，经过改造、标准化之后，成为 OCI 规范标准的第一个轻量运行时实现“runc”。



> 什么是 runc
>
> runc 是非常小的运行核，其目的在于提供一个干净简单的运行环境，他就是负责隔离 CPU、内存、网络等形成一个运行环境，可以看作一个小的操作系统。runc 的使用者都是一些 CaaS（Container as a Service，容器即服务）服务商，所以个人开发者知晓的并不是太多。



Docker 把与内部负责管理容器执行、分发、监控、网络、构建、日志等功能的模块重构为 containerd 项目 。如图 7-13 所示，containerd 的架构主要分为三个部分：生态系统（Ecosystem）、平台（Platform）和客户端（Client），每个部分在整个系统中扮演着不同的角色，协同工作以提供全面的容器管理功能。



![容器架构](https://containerd.io/img/architecture.png)



![docker架构](https://www.thebyte.com.cn/assets/docker-arc-C0C1vpYJ.png)

根据拆分后的 Docker 架构图看 ，根据功能的不同，容器运行时被分成两类：

- 只关注如 namespace、cgroups、镜像拆包等基础的容器运行时实现被称为“低层运行时”（low-level container runtime）。目前，应用最广泛的低层运行时是 runc；
- 支持更多高级功能，如镜像管理、容器应用的管理等，被称为“高层运行时”（high-level container runtime）。目前，应用最广泛高层运行时是 containerd。

在 OCI 标准规范下，两类运行时履行各自的职责，协作完成整个容器生命周期的管理工作



### 5.容器编排阶段：封装集群

Kubernetes 的时代，

让复杂软件在云计算下获得韧性、弹性、可观测性的最佳路径，也是为厂商们推动云计算时代加速到来的关键引擎之一。

Kubernetes 围绕容器抽象了一系列的“资源”概念能描述整个分布式集群的运行，还有可扩展的 API 接口、服务发现、容器网络及容器资源调度等关键特性，非常符合理想的分布式调度系统。

随着 Kubernetes 资源模型越来越广泛的传播，现在已经能够用一组 Kubernetes 资源来描述一整个软件定义计算环境。**就像用 docker run 可以启动单个程序一样，现在用 kubectl apply -f 就能部署和运行一个分布式集群应用，而无需关心是在私有云还是公有云或者具体哪家云厂商上**。



### 6.云原生阶段：百花齐放

Cloud Native Computing Foundation（CNCF，云原生基金会）。

OCI 和 CNCF 这两个围绕容器的基金会对云原生生态的发展发挥了非常重要的作用，二者不是竞争而是相辅相成，共同制定了一系列行业事实标准。

其中与容器相关的最为重要的几个规范包括：CRI（Container Runtime Interface，容器运行时接口规范）、CNI（Container Network Interface，容器网络接口规范）、CSI（Container Storage Interface，容器存储接口规范）、OCI Distribution Spec、OCI Image Spec、OCI Runtime Spec

![容器规范](https://www.thebyte.com.cn/assets/container-2-C3SgUuP4.jpeg)





## 微服务出现

 从巨石应用转换到微服务应用出现的非功能性需求问题：

- 服务发现（Service Discovery）问题：解决“我想调用你，如何找到你”的问题。
- 服务熔断（Circuit Breaker）问题：缓解服务之间依赖的不可靠问题。
- 负载均衡（Load Balancing）问题：通过均匀分配流量，让请求处理更加及时。
- 安全通讯问题：包括协议加密（TLS）、身份认证（证书/签名）、访问鉴权（RBAC）等。



## 服务网格

>  服务网格的定义
>
> 服务网格（ServiceMesh）是一个**基础设施层**，用于处理服务间通信。云原生应用有着复杂的服务拓扑，服务网格保证**请求在这些拓扑中可靠地穿梭**。在实际应用当中，服务网格通常是由一系列轻量级的**网络代理**组成的，它们与应用程序部署在一起，但**对应用程序透明**。
>
> —— by William Morgan





# 构建足够快的网络服务

## 网络阻塞原理：

https://www.thebyte.com.cn/http/congestion-control.html

看的迷迷糊糊，总体而言就是因为我们的网络带宽加上距离会导致我们的数据存在延迟，让发起请求的用户过多带宽占用满了这个时候会出现丢包，阻塞。



## 对网络请求进行动态加速

通过DCDN来进行网络加速

操作流程大致如下：

1. “源站”（Origin）将域名 CNAME 到 CDN 服务商提供的域名。例如，将 www.thebyte.com.cn CNAME 到 thebyte.akamai.com。
2. 源站提供一个约 20KB 的文件资源，用于探测网络质量。
3. CDN 服务商在源站附近选择一批“转发节点”（Relay Nodes）。
4. 转发节点对测试资源执行下载测试，根据丢包率、RTT、路由的 hops 数等，选定“客户端”（End Users）到源站的最佳路径。





# 深入 Linux 内核网络技术

