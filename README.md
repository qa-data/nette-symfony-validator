# NETTE Symfony Validator

Integrates Symfony Validator into Nette Framework.

## Setup

NETTE Symfony Validator is available on composer:

```bash
composer require qa-data/nette-symfony-validator
```

At first register compiler extension.

```neon
extensions:
	symfonyValidator: QaData\NetteSymfonyValidator\DI\NetteSymfonyValidatorExtension
```

## Configuration

```neon
symfonyValidator:
	cache: # optional
		directory: %tempDir%/cache/validator
		lifetime: 0
		namespace: validator.cache

```
