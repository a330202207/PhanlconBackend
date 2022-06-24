# 分支名即版本号


# 安装步骤
> * composer update
> * 创建`storage`目录，并设置755权限
> * 执行`sql`文件
> * 设置 `apps/Librarys/InvironMent.php` 中 `$hosts`的值(本机名称)
> * 后台(http://www.your-site-name.com/admin/index/index) 登录账号密码：admin 123

##  项目结构

###  树状图
```shell
 |----app
 |      |----Helpers                                    # 工具类目录
 |      |----Librarys                                   # 自定义类库目录
 |      |----Models                                     # 数据模型目录
 |      |----Modules                                    # 模块目录
 |      |      |----Frontend                            # 前台模块
 |      |      |      |----Controllers                  # 控制器层
 |      |      |      |      |----IndexController.php
 |      |      |      |----Services                     # 服务层
 |      |      |      |----Tasks                        # 任务层
 |      |      |      |      |----MainTask.php          # 主任务
 |      |      |      |----Views                        # 页面层
 |      |      |      |      |----base                  # 基础目录
 |      |      |      |      |      |----error404.phtml # 404错误页
 |      |      |      |      |      |----error500.phtml # 500错误页
 |      |      |      |      |----index
 |      |      |      |      |      |----index.phtml
 |      |      |      |----Module.php                   # 前台模块
 |      |----Providers                                  # 供应商
 |      |      |----ModuleProvider.php                  # 模块供应商
 |      |      |----ControllerProvider.php              # 控制器供应商
 |      |      |----TaskProvider.php                    # 任务供应商
 |      |----Bootstrap.php                              # 应用入口
 |      |----CliTask.php                                # cli任务应用
 |----bootstrap
 |      |----autoload.php                               # 项目自动加载
 |----config
 |      |----app.php                                    # 应用配置
 |      |----modules.php                                # 模块配置
 |----public
 |      |----.htaccess
 |      |----Frontend
 |      |     |----css
 |      |     |----files
 |      |     |----img
 |      |     |----js
 |      |     |----temp
 |      |----index.php                                  # 项目入口
 |----storage                                           # 缓存
 |      |----cache                                      # 缓存目录
 |      |----logs                                       # 日志目录
 |      |     |----app                                  # app日志目录
 |      |     |----cli                                  # cli日志目录
 |----vendor                                            # composer三方类库目录
 |----.htrouter.php
 |----.htaccess
```
