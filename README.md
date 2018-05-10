## 部署

- **重写规则**
	a.如果是 apache 不用改，因为目录下有 .htaccess
	b.如果是 nginx 重写规则
	```
	location / {
	    try_files $uri $uri/ /index.php?$query_string;
	}
	```

- **关于数据库**
	在 /sql 下有数据库安装sql语句，在 mysql 运行，表名：Web, Report

- **vhost**
	指定域名访问到目录下

- **配置文件**
	/conf/config.inc.php 总的配置文件
	根据不同环境修改对应环境的配置文件
	/conf/conf.{$env}.inc.php 