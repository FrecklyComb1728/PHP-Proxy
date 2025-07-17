# PHP 代理脚本

一个简单的 PHP 脚本，充当代理，允许通过处理跨源资源共享（CORS）向任何 URL 发送请求，并适当管理内容类型。适用于需要绕过 CORS 限制的场景，例如测试或开发环境。<s>绝对不是因为需要反代jsd</s>

## 使用方法

通过在脚本的 URL 路径后附加目标 URL 来使用此脚本。例如：

- 如果脚本位于 http://your-server.com/proxy.php，访问 http://your-server.com/proxy.php/https://example.com 来代理对 https://example.com 的请求。

- 如果脚本是文档根目录中的 index.php，访问 http://your-server.com/https://example.com。

**重要说明：**

- 目标 URL 必须包含在路径中，并以协议开头（例如，https:// 或 http://）。

- 如果未指定协议，脚本会默认添加 https://。

- 如果访问脚本时未提供目标 URL（例如，http://your-server.com/），会返回错误。

## 设置

1. 将此脚本上传到 Web 服务器的文档根目录或子目录。

2. 确保服务器已安装 PHP 并正确配置，且启用了 cURL 扩展。

3. 如果希望脚本处理根路径请求（例如，http://your-server.com/\[目标URL]），请将脚本命名为 index.php 并放置在文档根目录。

## 特性

- **CORS 处理**：自动设置 Access-Control-Allow-Origin: \*，允许跨源请求。

- **URL 验证**：确保目标 URL 有效，并在未指定协议时添加 https://。

- **内容类型管理**：根据目标 URL 的文件扩展名自动设置正确的 Content-Type 头（例如，.html、json、.png 等）。

- **响应流式传输**：直接将目标 URL 的响应流式传输到客户端，适合处理大文件。

- **头信息转发**：转发目标 URL 的大多数响应头，排除 Transfer-Encoding、Content-Length 和 Content-Encoding 等头。

- **Favicon 处理**：将 /favicon.ico 请求重定向到指定的 CDN URL。

## 限制

- **无认证支持**：脚本不处理目标 URL 的 cookie、认证令牌或其他会话数据。

- **安全风险**：由于脚本允许代理到任何 URL，生产环境中可能存在安全风险，建议通过防火墙规则或访问控制进行限制。

- **内容类型回退**：如果无法从目标 URL 的扩展名确定 Content-Type，可能会导致内容类型设置不正确。

- **有限的头信息过滤**：某些头信息被过滤，可能会影响特定类型的请求或响应。

## 示例

要代理对 https://example.com 的请求，访问：

- 如果脚本位于 http://your-server.com/proxy.php，使用：http://your-server.com/proxy.php/https://example.com

- 如果脚本位于 http://your-server.com/（文档根目录中的 index.php），使用：http://your-server.com/https://example.com

## 其他说明

- **用途**：此脚本适用于教育或测试目的。在生产环境中使用时，建议实现额外的安全措施，例如限制允许的域名或添加认证机制。

- **依赖**：脚本使用 cURL 发起请求，请确保 PHP 安装已启用 cURL 扩展。

- **内容类型支持**：脚本支持多种文件扩展名对应的 MIME 类型，如下表所示：

| 扩展名 | MIME 类型 |

|----------|--------------------------|

| .html | text/html |

| .css | text/css |

| .js | application/javascript |

| .json | application/json |

| .png | image/png |

| .jpg | image/jpeg |

| .mp4 | video/mp4 |

| .pdf | application/pdf |

- 如果遇到特定内容类型或请求的问题，请检查服务器配置是否支持所需的 MIME 类型。
