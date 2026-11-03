# CodeIgniter Repositories

## codeigniter4 organization

The CodeIgniter 4 open source project has its own [GitHub organization](https://github.com/codeigniter4).

There are several development repositories of interest to potential contributors:

| Repository | Audience | Description |
|----|----|----|
| [CodeIgniter4](https://github.com/codeigniter4/CodeIgniter4) | contributors | Project codebase, including tests & user guide sources |
| [translations](https://github.com/codeigniter4/translations) | developers | System message translations |
| [coding-standard](https://github.com/CodeIgniter/coding-standard) | contributors | Coding style conventions & rules |
| [devkit](https://github.com/codeigniter4/devkit) | developers | Development toolkit for CodeIgniter libraries and projects |
| [settings](https://github.com/codeigniter4/settings) | developers | Settings Library for CodeIgniter 4 |
| [shield](https://codeigniter4.github.io/shield) | developers | Authentication and Authorization Library for CodeIgniter 4 |
| [tasks](https://github.com/codeigniter4/tasks) | developers | Task Scheduler for CodeIgniter 4 |
| [cache](https://github.com/codeigniter4/cache) | developers | PSR-6 and PSR-16 Cache Adapters for CodeIgniter 4 |
| [queue](https://github.com/codeigniter4/queue) | developers | Job Queue for CodeIgniter 4 |

There are also several deployment repositories, referenced in the installation directions. The deployment repositories are built automatically when a new version is released, and they are not directly contributed to.

| Repository | Audience | Description |
|----|----|----|
| [framework](https://github.com/codeigniter4/framework) | developers | Released versions of the framework |
| [appstarter](https://github.com/codeigniter4/appstarter) | developers | Starter project (app/public/writable). Dependent on "framework" |
| [userguide](https://github.com/codeigniter4/userguide) | anyone | Pre-built user guide |

In all the above, the latest version of a repository can be downloaded from the **Releases** section of its GitHub repository page. The current development version of each can be cloned or downloaded using the **Code** dropdown on the repository homepage.

## Composer Packages

We also maintain composer-installable packages on [packagist.org](https://packagist.org/search/?query=codeigniter4). These correspond to the repositories mentioned above:

- [codeigniter4/framework](https://packagist.org/packages/codeigniter4/framework)
- [codeigniter4/appstarter](https://packagist.org/packages/codeigniter4/appstarter)
- [codeigniter4/translations](https://packagist.org/packages/codeigniter4/translations)
- [codeigniter/coding-standard](https://packagist.org/packages/codeigniter/coding-standard)
- [codeigniter4/devkit](https://packagist.org/packages/codeigniter4/devkit)
- [codeigniter4/settings](https://packagist.org/packages/codeigniter4/settings)
- [codeigniter4/shield](https://packagist.org/packages/codeigniter4/shield)
- [codeigniter4/cache](https://packagist.org/packages/codeigniter4/cache)

See the [Installation](index.md) page for more information.

## CodeIgniter 4 Projects

We maintain a [codeigniter4projects](https://github.com/codeigniter4projects) organization on GitHub as well, with projects that are not part of the framework, but which showcase it or make it easier to work with!

| Repository | Audience | Description |
|----|----|----|
| [website](https://github.com/codeigniter4projects/website) | developers | The codeigniter.com website, written in CodeIgniter 4 |
| [playground](https://github.com/codeigniter4projects/playground) | developers | Basic code examples in project form. Still growing. |

These are not composer-installable repositories.
