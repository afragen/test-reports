=== Test Reports ===

Tags: test, report, bug, patch, reproduce
Contributors: afragen, costdev
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Requires at least: 5.9
Requires PHP: 7.0
Tested up to: 6.6
Stable Tag: 1.1.0

Get templates with useful information to help you submit reports to WordPress.

## Description

Filing reports to WordPress can be daunting. What information should you provide? Where? What format is used?

This plugin simplifies that process by providing you with a template so you can paste, fill out each section, and submit.

Environment information is included in the template, so you don't need to fill in your browser, theme, plugins, operating system, server, database, or any of their versions. It's already in the template.

This plugin started life as a feature in the WordPress Beta Tester plugin. In order to bring it to more users and devs, creating a standalone plugin seemed optimal.

### Usage

1. Choose a report type, such as Bug Report, Bug Reproduction, Patch Testing or Security Vulnerability.
2. Choose a reporting location, such as Trac or GitHub. Security vulnerabilities should always be reported to HackerOne.
3. Click "Copy to clipboard" and paste into a new report at the reporting location.
4. Fill out the sections and submit.

## Screenshots
1. Bug Report

## Changelog

#### 1.1.0 / 2024-02-09
* check for SQLite

#### 1.0.0 / 2023-11-01
* initial release to dot org

#### 0.4.0 / 2023-10-16
* update namespacing, etc for plugin review team

#### 0.3.1 / 2023-09-09
* `mysql_get_client_info()` no longer in PHP 8.2, switch to `mysqli_get_client_info()`

#### 0.3.0 / 2023-07-20
* initial pass
* rename to `test-reports`
