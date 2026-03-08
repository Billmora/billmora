# Contributing to Billmora

Thank you for your interest in contributing to Billmora. Every contribution,
whether it is a bug report, feature suggestion, or code improvement, helps
make this project better for everyone.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [How to Contribute](#how-to-contribute)
  - [Reporting Bugs](#reporting-bugs)
  - [Suggesting Features](#suggesting-features)
  - [Submitting a Pull Request](#submitting-a-pull-request)
- [Development Guidelines](#development-guidelines)
  - [Branch Naming](#branch-naming)
  - [Commit Messages](#commit-messages)
  - [Coding Style](#coding-style)
- [Important Notes](#important-notes)

---

## Code of Conduct

By participating in this project, you agree to maintain a respectful and
welcoming environment for everyone. Harassment, discrimination, or
destructive behavior of any kind will not be tolerated.

---

## Getting Started

Before contributing, please make sure you have read the
[documentation](https://docs.billmora.com) and are familiar with how
Billmora works. For environment setup, refer to the installation guide
in the documentation.

---

## How to Contribute

### Reporting Bugs

If you encounter a bug, please open an issue on the
[GitHub Issues](https://github.com/Billmora/billmora/issues) page with
the following information:

- A clear and concise description of the bug
- Steps to reproduce the behavior
- Expected behavior vs actual behavior
- Your environment details (PHP version, OS, Billmora version)
- Screenshots or logs, if applicable

> For security vulnerabilities, do **not** open a public issue.
> Please refer to our [Security Policy](SECURITY.md) instead.

### Suggesting Features

Feature suggestions are welcome. Before opening a request, please check
existing issues to avoid duplicates. When submitting, include:

- A clear description of the feature and its purpose
- The problem it solves or the value it adds
- Any relevant examples or references

Note that feature requests are reviewed based on community need and
the project's direction. Acceptance is not guaranteed.

### Submitting a Pull Request

1. Fork the repository to your own GitHub account
2. Create a new branch from `dev` using the naming convention below
3. Make your changes with clear, focused commits
4. Ensure your code follows the project's coding style
5. Test your changes thoroughly before submitting
6. Open a pull request against the `dev` branch, not `main`
7. Fill out the pull request description clearly, referencing any
   related issues

Pull requests that lack context, break existing functionality, or do
not follow the guidelines may be closed without review.

---

## Development Guidelines

### Branch Naming

Use the following prefixes for branch names:

| Type | Format | Example |
|---|---|---|
| Bug fix | `fix/short-description` | `fix/invoice-due-date` |
| New feature | `feat/short-description` | `feat/paypal-gateway` |
| Documentation | `docs/short-description` | `docs/installation-guide` |
| Refactor | `refactor/short-description` | `refactor/billing-service` |

### Commit Messages

Write clear and descriptive commit messages in the imperative form:

- `fix: resolve incorrect invoice total calculation`
- `feat: add Pterodactyl provisioning plugin`
- `docs: update installation guide for shared hosting`
- `refactor: simplify recurring automation logic`

Avoid vague messages such as `fix bug`, `update`, or `changes`.

### Coding Style

Billmora is built with **Laravel 12**. Please follow these guidelines:

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use meaningful variable and method names
- Keep methods small and focused on a single responsibility
- Remove unused imports, variables, and commented-out code before submitting

---

## Important Notes

- All contributions are made under the [MIT License](LICENSE)
- The core team reserves the right to reject any contribution that does
  not align with the project's goals or quality standards
- Billmora is community-driven — sponsors and contributors do not have
  special influence over the project's direction or roadmap

Thank you for helping keep Billmora free, open, and sustainable.
