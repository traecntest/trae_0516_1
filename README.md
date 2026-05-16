# CAD Collaborate

基于 WebAssembly 的在线 CAD 图纸协作平台

## 技术栈

- **后端**: PHP 8 + Slim Framework
- **前端**: Vue 3 + Vite
- **数据库**: MySQL
- **WebAssembly**: OpenJSCAD (jscad-modeling)
- **认证**: JWT

## 功能特性

- 用户注册与登录
- 图纸创建与管理
- WebAssembly 3D 建模
- 实时协作
- 版本控制
- 图纸分享
- STL/STEP 导出

## 安装指南

### 1. 配置数据库

```bash
mysql -u root -p < config/init.sql
```

### 2. 安装后端依赖

```bash
cd backend
composer install
```

### 3. 安装前端依赖

```bash
cd frontend
npm install
```

### 4. 配置 Nginx

将 `config/nginx.conf` 配置到 Nginx 服务器

### 5. 启动服务

```bash
# 启动 PHP 后端
cd backend
php -S localhost:8000

# 启动前端开发服务器
cd frontend
npm run dev
```

## 项目结构

```
├── backend/              # PHP 后端
│   ├── src/
│   │   ├── controllers/  # 控制器
│   │   ├── core/         # 核心模块
│   │   ├── middleware/   # 中间件
│   │   └── routes/       # 路由
│   └── index.php         # 入口文件
├── frontend/             # Vue 前端
│   ├── src/
│   │   ├── views/        # 页面组件
│   │   ├── router/       # 路由配置
│   │   ├── main.js       # 入口文件
│   │   └── style.css     # 全局样式
│   └── index.html
├── cad-core/             # WebAssembly CAD 核心
│   ├── cad-engine.js     # CAD 引擎封装
│   └── jscad-worker.js   # Web Worker
├── config/               # 配置文件
│   ├── database.php      # 数据库配置
│   ├── app.php           # 应用配置
│   ├── init.sql          # 数据库初始化
│   └── nginx.conf        # Nginx 配置
└── uploads/              # 上传文件目录
```

## API 接口

### 认证
- POST `/api/auth/login` - 登录
- POST `/api/auth/register` - 注册
- GET `/api/auth/me` - 获取用户信息

### 图纸
- GET `/api/drawings` - 获取图纸列表
- POST `/api/drawings` - 创建图纸
- GET `/api/drawings/{id}` - 获取图纸详情
- PUT `/api/drawings/{id}` - 更新图纸
- DELETE `/api/drawings/{id}` - 删除图纸

### 协作
- POST `/api/collaboration/join/{drawingId}` - 加入协作
- POST `/api/collaboration/leave/{drawingId}` - 离开协作
- GET `/api/collaboration/{drawingId}/users` - 获取协作用户

## 默认账户

用户名: admin
邮箱: admin@cadcollab.local
密码: password

## License

MIT
