# OPC H5 宝塔部署说明

适用站点：`opc.qyfanshen.com`  
站点目录：`/www/wwwroot/opc.qyfanshen.com`  
PHP：8.2  
数据库：`opc`，用户名 `opc`

## 1. 上传文件

在宝塔「网站」中打开站点根目录，删除宝塔自动生成的默认 `index.html`，把本压缩包内的所有文件解压到：

`/www/wwwroot/opc.qyfanshen.com`

注意：要让 `index.html` 直接位于这个目录，不要再多套一层文件夹。

## 2. 导入数据表

进入宝塔「数据库」→ 找到 `opc` →「管理」或「导入」，选择：

`database/schema.sql`

导入成功后应看到 `applications` 表。

## 3. 填写数据库密码

编辑服务器上的 `config/app.php`，找到：

`'password' => 'CHANGE_ME',`

把 `CHANGE_ME` 换成宝塔数据库 `opc` 的真实密码。只改引号里的内容，不要删逗号。

## 4. 检查 PHP 与安全设置

站点 PHP 版本选择 PHP 8.2，并确认 PHP 扩展中已启用 `pdo_mysql` 和 `mbstring`。

在站点「配置文件」的 `server { ... }` 内加入以下规则，阻止敏感目录被访问，然后重载 Nginx：

```nginx
location ~ ^/(config|inc|database)/ {
    deny all;
    return 404;
}
```

在宝塔申请并启用 SSL，随后开启「强制 HTTPS」。

## 5. 访问与测试

- H5 首页：`https://opc.qyfanshen.com/`
- 报名后台：`https://opc.qyfanshen.com/admin/`

先从首页提交一条测试报名，再登录后台检查是否收到。后台支持修改跟进状态和导出 CSV。

## 修改后台密码

后台密码不会以明文保存在服务器。需要更换时，可在任意 SHA-256 生成工具中计算新密码摘要，并替换 `config/app.php` 的 `admin_password_sha256`。也可在服务器终端运行：

```bash
php -r "echo hash('sha256', '这里写新密码'), PHP_EOL;"
```

把输出的 64 位字符填入配置文件即可。
