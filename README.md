# Stub to start a new [BEM](https://bem.info) project with [bh-php][]

Project-stub is a template project repository used for BEM projects creation. It contains the minimal configuration files and folders you will need for quick start from scratch.

There are two main BEM libraries are linked here by default:

* [bem-core](https://en.bem.info/libs/bem-core/)
* [bem-components](https://en.bem.info/libs/bem-components/)

And also templates for [bh-php][]:

* [bem-core-php](https://github.com/bem/bem-core-php/)
* [bem-components-php](https://github.com/bem/bem-components-php/)

## Installation requirements

* [Node.js 0.12+](http://nodejs.org) is a platform built on Chrome JavaScript runtime for easily building fast, scalable network applications.
* [Git Bash](http://msysgit.github.io/) if you use Windows OS.
* [PHP 5.4+](http://php.net) is a popular general-purpose scripting language that is especially suited to web development.
* [Composer](https://getcomposer.org/) is a Dependency Manager for PHP.

## Supported browsers

The list of supported browsers depends on the [bem-core](https://en.bem.info/libs/bem-core/current/#supported-browsers) and [bem-components](https://en.bem.info/libs/bem-components/current/#supported-browsers) library versions.

>**NB** Internet Explorer 8.0 is not supported by default. To support IE8 you must follow the [recomendations](https://en.bem.info/libs/bem-components/current/#support-for-internet-explorer-8) or use the alternative way — a [generator-bem-stub](https://en.bem.info/tools/bem/bem-stub/) that ensures an optimal config file for your project creation.

## Installation

So, how easy is it to get started with BEM? — *Super easy!*

It's as easy as...

```
git clone https://github.com/bem/project-stub.git --depth 1 --branch bem-core-php my-bem-project
cd my-bem-project
npm install # Do not use root privilege to install npm, bower and composer dependencies.
```

`bower` dependencies are installed in the `libs` directory by `npm postinstall`.
While `composer` dependencies in the `vendor` directory.

## Usage

You could use the following tools to build the project: [ENB](https://ru.bem.info/tools/bem/enb-bem-techs/)(only in Russian) or [bem-tools](https://bem.info/tools/bem/bem-tools/). The result files are the same in both cases as `bem-tools` just calls `ENB` under the hood.

You can run any `enb` commands via `node_modules/.bin/enb` and the `bem-tools` commands with `node_modules/bem/bin/bem`.

### Build the project

```bash
node_modules/.bin/enb make
```
or
```bash
node_modules/.bin/bem make
```

To be able to run commands without typing a full path to an executable file (`node_modules/.bin/enb`), use:

```
export PATH=./node_modules/.bin:$PATH
```

Now you can use `enb` or `bem` from any point of your project.

```
enb make
```

### The basic commands

>Execute the following commands in your terminal.

You could use help option to get information about the basic commands of `enb` and `bem-tools`:

```
enb -h
```
and

```
bem -h
```

**Start the dev server**

```bash
node_modules/.bin/enb server
```
or
```bash
node_modules/.bin/bem server
```

You could use the `npm start` command to start the `enb server` without specifying the full path to the `node_modules`.

```bash
npm start
```

The `bem server ` is running. To check it out, navigate to `http://localhost:8080/desktop.bundles/index/index.html`.

You may also specify different port if `8080` is already taken by some other service:
```bash
npm start -- --port=8181
```

**Stop the server**

Press `Ctrl` + `C` or `⌘` + `C` (for MAC devices) while the terminal is your active window to stop the server.

**Add a block**

If you want to use `bem-tools` to create new blocks, you should install additional dependencies:

```bash
npm i ym --save-dev
```

Now it's possible to create blocks with `bem create` command:

```bash
bem create -l desktop.blocks -b newBlock
```

**Add a page**

```bash
bem create -l desktop.bundles -b page
```

## Generator of BEM projects for Yeoman

`project-stub` is a multipurpose template project that covers the most common tasks of the BEM project. If you want to create the most suitable configuration to build your project, use the [generator-bem-stub](https://en.bem.info/tools/bem/bem-stub/).

This generator provides you the ability to get the base of BEM project in few minutes by answering the simple questions.
- [generator-bem-stub](https://en.bem.info/tools/bem/bem-stub/)

## Docs

- [Full stack quick start](https://en.bem.info/articles/start-with-project-stub/)
- [Static quick-start](https://en.bem.info/tutorials/quick-start-static/)
- [Tutorial for BEMJSON template-engine](https://en.bem.info/technology/bemjson/current/bemjson/)
- [Tutorial on BEMHTML](https://en.bem.info/libs/bem-core/2.0.0/bemhtml/reference/)
- [Tutorial on i-bem.js](https://en.bem.info/tutorials/bem-js-tutorial/)
- [JavaScript for BEM: main terms](https://en.bem.info/articles/bem-js-main-terms/)
- [Commands bem-tools](https://en.bem.info/tools/bem/bem-tools/commands/)

## Project-stub based projects

- [Creating BEM application on Leaflet and 2GIS API](https://en.bem.info/tutorials/firm-card-story/)
- [Creating a menu of geo objects collections with Yandex.Maps API and BEM](https://en.bem.info/tutorials/yamapsbem/)
- [SSSR (Social Services Search Robot)](https://github.com/bem/sssr) — study app with BEM full-stack

## Useful tools

- [borschik](https://en.bem.info/tools/optimizers/borschik/) — borschik is a simple but powerful builder for text-based file formats

## Videos

- [BEM - Building 'em modular](https://www.youtube.com/watch?v=huQp7gr3WPE)
- [BEM for JavaScript Talk on Camp JS](https://en.bem.info/talks/campjs-melbourne-2014/)

[bh-php]: https://github.com/bem/bh-php
