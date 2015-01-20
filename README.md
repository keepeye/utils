我的PHP工具包
===============

###安装

    composer require "keepeye/utils" --prefer-source

####Laravel下
添加服务提供器到配置文件 `providers`,

    'Keepeye\Utils\UtilsServiceProvider'

####通用
直接实例化类即可


###具体使用

本工具包中可能抛出自定义异常类`Keepeye\Utils\KeepeyeUtilException`，可以针对捕获处理。


- [无限分类树工具ListTree][1]


[1]:docs/ListTree.md
