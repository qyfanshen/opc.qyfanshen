# API Reference

> OPC 一人公司生态社区 的接口文档。模块：submit, admin

## 通用约定

- 基础路径：`/api/`
- 请求/响应：JSON
- 鉴权：除登录接口外，所有接口需要带 `Authorization: Bearer <token>` 或 Cookie 会话
- 限流：默认每 IP 每分钟 60 次（可由 `api/rate_limit.php` 或中间件调整）
- 错误格式：
  ```json
  { "code": 400, "message": "Invalid parameter", "data": null }
  ```


## 申请提交

```
POST /api/submit.php
```

请求：
```json
{
  "name": "string",
  "phone": "string",
  "company": "string",
  "message": "string"
}
```
