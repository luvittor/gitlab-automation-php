# GitLab Automation Scripts (PHP)

PHP scripts for automating GitLab branch operations and merge requests with GMUD/task workflow support.

## Features

- 🌀 **Branch Management**
  - Create new branches from existing branches
  - Create GMUD release and working branches
  - Create task branches
  - Delete branches
  - Automatic branch pattern generation
  - Automatic {DATE} placeholder replacement
- 🔀 **Merge Request Automation**
  - GMUD workflow MRs (release → gmud → master)
  - Task-to-GMUD/master MRs
- ⚙️ **Environment Configuration**
  - `.env` file support
  - Batch task file generation
  - Multi-repository support

## Prerequisites

- PHP 8.0+ (tested with 8.2)
- [Composer](https://getcomposer.org/)
- GitLab API access token with `api` scope

## Installation

```bash
git clone https://github.com/luvittor/gitlab-automation-php.git
cd gitlab-automation-php
composer install
cp .env.example .env
```

Edit `.env`.

## Workflow Automation

### 1. GMUD Preparation
```bash
# Generate branch creation tasks
php create_txt_for_gmud_branches.php 1234

# Generate MR tasks between branches
php create_txt_for_gmuds_mrs.php 1234

# Execute operations
php gitlab.php tasks/create_gmud_1234_branches.txt
php gitlab.php tasks/create_gmud_1234_mrs.txt
```

### 2. Task MR Generation
```bash
# Generate task MRs for specific repos
php create_txt_for_task_mrs_for_gmud.php glpi-654321 1234 b2b/api-b2b mob/backend/portal_b2b_api

# Execute MR creation
php gitlab.php tasks/create_task_glpi-654321_mrs_to_gmud_1234.txt
```

## Script Reference

| Script | Purpose | Output Example |
|--------|---------|----------------|
| `create_txt_for_gmud_branches.php` | Generate branch creation tasks | `create_gmud_1234_branches.txt` |
| `create_txt_for_gmuds_mrs.php` | Create GMUD workflow MRs | `create_gmud_1234_mrs.txt` |
| `create_txt_for_task_mrs_for_gmud.php` | Generate task-to-GMUD MRs | `create_task_glpi-654321_mrs_to_gmud_1234.txt` |

## File Patterns

| Type | Pattern | Example |
|------|---------|---------|
| GMUD Branches | `release/gmud-{NUM}`<br>`gmud/{NUM}` | `release/gmud-1234`<br>`gmud/1234` |
| Task Branches | `task/{TASK_ID}` | `task/glpi-654321` |
| MR Paths | release → gmud → master<br>task → master/release | `MR release/gmud-1234 → gmud/1234` |

## Security

- 🔒 **Protect sensitive data**
  - Keep `.env` in `.gitignore`
  - Use least-privilege tokens
  - Restrict file permissions
  - Audit generated task files

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Invalid GMUD number | Use numeric values only |
| Missing repos | Verify `.env` REPOS configuration |
| API errors | Check token permissions<br>Validate GitLab URLs |

**Note**  
 - Test with non-critical branches first.
 - Validate generated task files before execution.
 - Maintain backups before bulk operations.