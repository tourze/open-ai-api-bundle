# OpenAI API Bundle

[English](README.md) | [ä¸­æ–‡](README.zh-CN.md)

## Introduction

OpenAI API Bundle is a Symfony Bundle that provides OpenAI API integration support for Symfony applications. This bundle offers convenient service configuration and dependency injection, making it easy to use OpenAI's various AI services in your Symfony projects.

## Installation

Install via Composer:

```bash
composer require tourze/open-ai-api-bundle
```

## Configuration

Register the bundle in your Symfony application:

```php
// config/bundles.php
return [
    // ...
    Tourze\OpenAiApiBundle\OpenAiApiBundle::class => ['all' => true],
];
```

## Features

- =€ Simplified OpenAI API integration
- =' Automated service configuration
- =‰ Dependency injection support
- =Ý Complete API documentation reference

## API Documentation Reference

This bundle includes comprehensive OpenAI API reference documentation covering the following modules:

### Core Features
- **Chat** - Chat completions API
- **Completions** - Text completion API
- **Embeddings** - Text embeddings API
- **Images** - Image generation and editing API
- **Audio** - Speech synthesis, transcription, and translation API
- **Moderations** - Content moderation API

### Advanced Features
- **Assistants** - AI assistant management
- **Fine-tuning** - Model fine-tuning
- **Batch** - Batch operations
- **Files** - File management
- **Vector Stores** - Vector store management
- **Realtime** - Realtime sessions API

### Management Features
- **Projects** - Project management
- **Users** - User management
- **API Keys** - API key management
- **Rate Limits** - Rate limit configuration
- **Audit Logs** - Audit logging

## Requirements

- PHP 8.1 or higher
- Symfony 7.3 or higher

## Development

### Running Tests

```bash
# Run unit tests
./vendor/bin/phpunit packages/open-ai-api-bundle/tests

# Run PHPStan static analysis
./vendor/bin/phpstan analyse packages/open-ai-api-bundle
```

### Quality Standards

- PHPStan Level 8
- PHPUnit test coverage

## License

MIT License - See [LICENSE](LICENSE) file for details