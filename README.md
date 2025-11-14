# OpenAI API Bundle

[English](README.md) | [中文](README.zh-CN.md)

## Introduction

OpenAI API Bundle 是一个为 Symfony 应用程序提供 OpenAI API 数据管理功能的 Symfony Bundle。该 Bundle 提供了完整的实体模型、Repository 模式、管理后台集成和数据修复功能，帮助您在 Symfony 项目中高效管理 OpenAI 相关数据。

## Installation

通过 Composer 安装：

```bash
composer require tourze/open-ai-api-bundle
```

## Configuration

在您的 Symfony 应用程序中注册 Bundle：

```php
// config/bundles.php
return [
    // ...
    Tourze\OpenAiApiBundle\OpenAiApiBundle::class => ['all' => true],
];
```

## Features

- ✅ 完整的 OpenAI 数据实体模型
- 🤖 集成 EasyAdmin 管理后台
- 🔧 依赖注入支持
- 📚 完整的 Repository 模式
- 🎯 状态枚举管理
- 🔍 数据修复工具
- 📊 聊天对话管理

## Core Entities

### 1. AI Model (AI模型)
- **路径**: `src/Entity/AIModel.php`
- **功能**: 管理 OpenAI 模型配置信息
- **主要字段**:
  - `modelId`: 模型唯一标识符
  - `name`: 模型显示名称
  - `owner`: 模型所有者
  - `status`: 模型状态（可用/不可用/测试中）
  - `contextWindow`: 上下文窗口大小
  - `inputPricePerToken` / `outputPricePerToken`: Token 价格
  - `capabilities`: 模型能力配置

### 2. Assistant (AI助手)
- **路径**: `src/Entity/Assistant.php`
- **功能**: 管理 AI 助手配置
- **主要字段**:
  - `name`: 助手名称
  - `description`: 助手描述
  - `modelId`: 使用的模型
  - `instructions`: 指令集
  - `tools`: 工具配置
  - `status`: 助手状态

### 3. Thread (对话线程)
- **路径**: `src/Entity/Thread.php`
- **功能**: 管理对话线程
- **主要字段**:
  - `assistantId`: 关联的助手
  - `title`: 线程标题
  - `status`: 线程状态
  - `metadata`: 元数据

### 4. Chat Conversation (聊天对话)
- **路径**: `src/Entity/ChatConversation.php`
- **功能**: 管理具体的聊天对话记录

### 5. Uploaded File (上传文件)
- **路径**: `src/Entity/UploadedFile.php`
- **功能**: 管理上传到 OpenAI 的文件

## Enums

Bundle 提供了完整的状态枚举：

- **ModelStatus**: 模型状态管理 (`Available`, `Unavailable`, `Testing`)
- **AssistantStatus**: 助手状态管理
- **ThreadStatus**: 线程状态管理
- **ConversationStatus**: 对话状态管理
- **FileStatus**: 文件状态管理

## Admin Controllers

集成 EasyAdmin Bundle 提供管理后台：

- `AIModelCrudController`: AI 模型管理
- `AssistantCrudController`: AI 助手管理
- `ThreadCrudController`: 对话线程管理
- `ChatConversationCrudController`: 聊天对话管理
- `UploadedFileCrudController`: 上传文件管理

## Data Fixtures

提供测试数据生成器：

- `AIModelFixtures`: 生成 AI 模型测试数据
- `AssistantFixtures`: 生成 AI 助手测试数据
- `ThreadFixtures`: 生成对话线程测试数据
- `ChatConversationFixtures`: 生成聊天对话测试数据
- `UploadedFileFixtures`: 生成上传文件测试数据

## Requirements

- PHP 8.1 或更高版本
- Symfony 7.3 或更高版本
- Doctrine ORM
- EasyAdmin Bundle
- 相关的 Tourze Bundle 依赖

## Development

### Running Tests

```bash
# 运行单元测试
./vendor/bin/phpunit packages/open-ai-api-bundle/tests

# 运行 PHPStan 静态分析
./vendor/bin/phpstan analyse packages/open-ai-api-bundle
```

### Database Schema

Bundle 提供完整的数据库表结构：

- `openai_ai_models`: OpenAI 模型配置表
- `openai_assistants`: AI 助手表
- `openai_threads`: 对话线程表
- `openai_chat_conversations`: 聊天对话表
- `openai_uploaded_files`: 上传文件表

### Quality Standards

- PHPStan Level 8 静态分析
- PHPUnit 完整测试覆盖
- 严格的类型声明
- 完整的输入验证

## License

MIT License - 详见 [LICENSE](LICENSE) 文件