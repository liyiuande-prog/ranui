# RanUI Blog 🚀

RanUI 是一个基于 PHP 开发的高性能、简洁且功能强大的博客/内容管理系统。它具有轻量级的核心架构，支持插件扩展和主题定制，旨在为用户提供最纯粹的写作和阅读体验。

🌐 **官方网站**: [Geknet.com](https://Geknet.com)

---

## ✨ 项目特性

- **极简架构**: 采用原生的 PHP 开发，无沉重的第三方依赖，响应极快。
- **自定义主题**: 轻松切换和导出主题。
- **插件系统**: 支持动态加载功能模块。
- **响应式设计**: 完美适配手机、平板和桌面端。
- **SEO 友好**: 自动优化标题与 meta 标签。

---

## 🛠️ 安装方法

### 1. 环境要求
- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- Nginx 或 Apache

### 2. 克隆项目
```bash
git clone https://github.com/liyiuande-prog/ranui.git
cd ranui
```

### 3. 设置目录权限
为了确保插件上传和系统日志、缓存能够正常运行，请为以下目录开启写入权限：
```bash
chmod -R 777 plugins
chmod -R 777 storage
```

### 4. 部署与安装
将你的 Nginx 或 Apache 的根目录指向项目中的 `public` 文件夹。

随后在浏览器中直接访问您的域名，系统将自动进入安装引导程序（`install`），帮助您轻松完成数据库连接和站点初始化。

*例如 Nginx 配置：*
```nginx
root /path/to/ranui/public;
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```


### 5. 安装依赖 (App 扩展功能)
若需要使用视频剪辑和即时通讯功能，请在项目根目录运行：
```bash
composer require php-ffmpeg/php-ffmpeg  # 视频首帧裁剪
composer require workerman/workerman    # 即时通讯与通知
```

> **💡 提示**：为了使 `php-ffmpeg` 正常工作，您需要确保 PHP 环境开启了 `exec`、`shell_exec` 和 `proc_open` 函数。请检查您的 `php.ini` 文件，并将它们从 `disable_functions` 列表中移除（如果是使用宝塔面板，请在 PHP 设置的“禁用函数”中将其删除）。


---

## 📱 App 功能扩展

### 1. 即时通讯与通知
RanUI 支持通过 Workerman 实现即时聊天和系统通知：
- **开放端口**: 请在你的服务器控制面板（如阿里云/腾讯云安组）和宝塔面板安全设置中，放行 **8081** 端口。
- **启动服务**: 进入插件目录并运行以下命令以启动后台服务：
  ```bash
  cd plugins/Ran_App
  php bin/server.php start -d
  ```

### 2. 视频功能
- **视频预览**: 系统使用 `php-ffmpeg` 自动裁剪上传视频的第一帧作为封面图。

---

## 📖 使用说明

- **后台管理**: 访问 `域名/admin` 进入管理面板。
- **发布文章**: 在后台可以进行分类管理、文章撰写以及评论审核。
- **主题更换**: 编辑 `app/Config/config.php` 中的 `'theme'` 项或在后台进行切换。

---

## ☕ 赞赏支持

如果你觉得这个项目对你有帮助，欢迎请作者喝杯咖啡！

| 微信支付 | 支付宝 |
| :---: | :---: |
| <img src="https://www.geknet.com/file/view?p=uploads/images/2026/02/698492147dbb5.png" width="200"> | <img src="https://www.geknet.com/file/view?p=uploads/images/2026/02/698492334fa9d.png" width="200"> |

---

## 📜 开源协议
本项目采用 MIT 协议开源。

Copyright (c) 2026 [RanUI](https://Geknet.com)
