# Woo Offers v3.0 - Git Branching Strategy

## Overview

This document outlines the branching strategy for the Woo Offers WordPress plugin development. We follow a modified GitFlow approach optimized for WordPress plugin development and distribution.

## Branch Structure

### Main Branches

#### `main` (Production Ready)
- **Purpose**: Contains production-ready code that has been tested and is ready for release
- **Protection**: Branch protection enabled, requires pull requests
- **Deployment**: Automatically deployed to WordPress.org repository (when configured)
- **Naming**: `main`

#### `develop` (Integration Branch)
- **Purpose**: Integration branch for ongoing development
- **Contains**: Latest development changes that will be included in the next release
- **Testing**: All features must pass tests before merging
- **Naming**: `develop`

### Supporting Branches

#### Feature Branches
- **Purpose**: Develop new features or enhancements
- **Created from**: `develop`
- **Merged back to**: `develop`
- **Naming convention**: `feature/<feature-name>`
- **Examples**: 
  - `feature/campaign-builder`
  - `feature/analytics-dashboard`
  - `feature/bulk-offer-management`

#### Release Branches
- **Purpose**: Prepare new releases, final testing, and version bumping
- **Created from**: `develop`
- **Merged to**: Both `main` and `develop`
- **Naming convention**: `release/<version>`
- **Examples**: 
  - `release/3.0.0`
  - `release/3.1.0`

#### Hotfix Branches
- **Purpose**: Quick fixes for critical issues in production
- **Created from**: `main`
- **Merged to**: Both `main` and `develop`
- **Naming convention**: `hotfix/<version>`
- **Examples**: 
  - `hotfix/3.0.1`
  - `hotfix/3.0.2`

## Workflow

### 1. Feature Development
```bash
# Start new feature
git checkout develop
git pull origin develop
git checkout -b feature/new-feature-name

# Work on feature
git add .
git commit -m "feat: implement new feature functionality"

# Push and create PR
git push origin feature/new-feature-name
```

### 2. Release Preparation
```bash
# Create release branch
git checkout develop
git pull origin develop
git checkout -b release/3.1.0

# Prepare release (version bumps, documentation updates)
git add .
git commit -m "chore: prepare release 3.1.0"
git push origin release/3.1.0
```

## Commit Message Conventions

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

### Format
```
<type>[optional scope]: <description>
```

### Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Build process, dependency updates

### Examples
```bash
feat(campaigns): add visual campaign builder interface
fix(offers): resolve discount calculation error for BOGO offers
docs(readme): update installation instructions
chore(deps): update composer dependencies
```

## Versioning Strategy

We follow [Semantic Versioning (SemVer)](https://semver.org/):

### Version Format: `MAJOR.MINOR.PATCH`

- **MAJOR**: Breaking changes, significant feature overhauls
- **MINOR**: New features, backwards-compatible changes
- **PATCH**: Bug fixes, security patches 