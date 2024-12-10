<div align="center">

  <a href="https://github.com/fivefifteen/wordup">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="./assets/wordup-white.png">
      <img src="./assets/wordup.png" alt="WordUp">
    </picture>
  </a>

  # WordUp

  A WordPress [Deployer Recipe](https://deployer.org).

  [![packagist package version](https://img.shields.io/packagist/v/fivefifteen/wordup.svg?style=flat-square)](https://packagist.org/packages/fivefifteen/wordup)
  [![packagist package downloads](https://img.shields.io/packagist/dt/fivefifteen/wordup.svg?style=flat-square)](https://packagist.org/packages/fivefifteen/wordup)
  [![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/fivefifteen/wordup?style=flat-square)](https://github.com/fivefifteen/wordup)
  [![license](https://img.shields.io/github/license/fivefifteen/wordup.svg?style=flat-square)](https://github.com/fivefifteen/wordup/blob/main/license.md)

  <a href="https://fivefifteen.com" target="_blank"><img src="./assets/fivefifteen.png" /><br /><b>A Five Fifteen Project</b></a>

</div>


## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Setup](#setup)
- [Configuration](#configuration)
    - [Configuration Options](#configuration-options)
- [Tasks](#tasks)
    - [Database Tasks](#database-tasks)
    - [Templates Tasks](#templates-tasks)
    - [Uploads Tasks](#uploads-tasks)
    - [WordPress Tasks](#wordpress-tasks)
- [Usage](#usage)
    - [Usage Examples](#usage-examples)
- [License Information](#license-information)


## Requirements

- PHP 8.1 or above
- Composer


## Installation

```sh
composer require-dev fivefifteen/wordup
```


## Setup

To get started, copy [examples/deploy.php](examples/deploy.php) and [examples/deploy.yml](examples/deploy.yml) into the root of your project. You'll then want to update both of those files to meet your needs.


## Configuration

Configuration options can be defined/updated in either `deploy.yml` or by using the [add](https://deployer.org/docs/7.x/api#add)/[set](https://deployer.org/docs/7.x/api#set) functions in `deploy.php`.

WordUp uses [Deployer](https://deployer.org/docs/7.x/basics) and it's [Common Recipe](https://deployer.org/docs/7.x/recipe/common) at it's core so you'll want to familiarize yourself with both of those.


### Configuration Options

These are options that are custom to WordUp. See the source of [recipe/wordup.php](recipe/wordup.php) to view the default values (as well as any built-in Deployer options that WordUp modifies).

 - `db/credentials` - A list of database credentials
     - `host` - The database host
     - `name` - The database name
     - `user` - The database username
     - `pass` - The database password
     - `prefix` - The database tables prefix
     - `charset` - The database charset
     - `collate` - The database collate
 - `db/exports_dir` - The name of the directory to save database exports to
 - `db/exports_path` - The remote path to save database exports to
 - `db/keep_exports` - If falsy, database exports will not be deleted from the remote server after downloading
 - `db/keep_local_exports` - If truthy, database exports will be deleted from the local environment after importing
 - `templates/files` - A list of `.mustache` files to be rendered
 - `templates/temp_dir` - A temporary directory to store template files before uploading them to a remote server
 - `wp/config/constants` - An associative array of constants to write to `wp-config.php` during the `wp:config:create` task
 - `wp/config/extra_php` - PHP code that should be included in `wp-config.php`
 - `wp/config/require` - A list of files that should be required by `wp-config.php`
 - `wp/content_dir` - The name of WordPress's content directory
 - `wp/content_path` - The remote path to WordPress's content directory
 - `wp/home` - The home URL for your WordPress website
 - `wp/salts/temp_dir` - A temporary directory to store generated salts
 - `wp/siteurl` - The URL to the WordPress directory
 - `wp/uploads_dir` - The name of the WordPress uploads directory
 - `wp/uploads_path` - The remote path to the WordPress uploads directory


## Tasks

### Database Tasks

 - `db:export` - Exports the local database
 - `db:import` - Imports a database export into the local database
 - `db:export:remote` - Exports and downloads the remote database
 - `db:import:remote` - Uploads a local database export and imports it into the remote database
 - `db:pull` - Pulls remote database to localhost (invokes `db:export:remote` and `db:import`)
 - `db:push` - Pushes local database to remote host (invokes `db:export` and `db:import:remote`)

### Templates Tasks

 - `templates:render` - Renders mustache template files

### Uploads Tasks

 - `uploads:pull` - Pulls uploads from remote to local
 - `uploads:push` - Pushes uploads from local to remote
 - `uploads:sync` - Syncs uploads between local and remote

### WordPress Tasks

 - `wp:config:create` - Generates a wp-config.php file
 - `wp:salts:php` - Generates salts in PHP format and saves them to a file
 - `wp:salts:json` - Generates salts in JSON format and saves them to a file
 - `wp:salts:yml` - Generates salts in YML format and saves them to a file


## Usage

```sh
dep <task> <stage>
```

### Usage Examples

```sh
# Deploy to staging
dep deploy staging

# Export the database from production, download it, and import it into the local database
dep db:pull production

# Generate a wp-config.php file for my local environment
dep wp:config:create localhost
```

## License Information

MIT. See the [license.md file](license.md) for more info.