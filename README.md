# Codigo Wordpress Project for Skelly & Couch
A clean slate Wordpress application for Skelly & Couch.
Based in [sage](https://github.com/roots/sage?tab=readme-ov-file)


[![version](https://img.shields.io/badge/version-0.0.2-red.svg)](https://semver.org)


## Live version

[#](Not ready yet)

## Staging version

[https://beta.skelly-couch.tm-studio.co.uk/](beta.skelly-couch.tm-studio.co.uk)

 - User: tm-studio
 - Password: skellycouch


## Local version

[https://skelly-couch.localhost:8465/](https://skelly-couch.localhost:8465/)


---

## Layout

### Breakpoints
  
* `--breakpoint-sm: 600px;  /* Matches Gutenberg small */`
* `--breakpoint-md: 782px;  /*  Matches Gutenberg medium */`
* `--breakpoint-lg: 960px;  /*  Matches Gutenberg large */`
* `--breakpoint-xl: 1280px;`
* `--breakpoint-2xl: 1536px;`
* `--breakpoint-3xl: 1792px;`


### Sizes

- Normalised to 1920px viewport
- Formula: font-size: (target_px / 1920) * 100vw;
  - sm: 16px → 0.8333vw
  - base: 24px → 1.25vw
  - 30px → 1.5625vw
  - h1: 72px → 3.75vw
  - h2: 36px → 1.875vw
  - 50px → 2.6042vw
  - 7xl (large): 100px → 5.2083vw
  - 130px → 6.7708vw;
  - 200px → 10.4167vw;
  - 300px -> 15.625vw;
  - 1144px → 59.7917vw;


---

## Installation

### Docker Installation

[DOCKER.md](docs/DOCKER.md)


### Install Sage


The theme is based in Sage 11, which is a powerful WordPress starter theme that uses Laravel Blade templating and Vite for asset management.

[SAGE.md](docs/SAGE.md)


### PostCSS

[PostCSS.md](docs/PostCSS.md)


### Alpine

[ALPINE.md](docs/ALPINE.md)



---


## Deploying 


1. **Build theme assets:**

   ```bash
   npm run build
   ```

2. **Install Composer dependencies (without dev packages):**

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Upload your theme files:**

   Upload all files and folders in your theme directory **except** the `node_modules` folder to your host.


### Optimisation

Similar to deploying a Laravel app, Acorn supports an `optimize` command that caches your configuration and views. This command should be part of your deployment process:

```bash
wp acorn optimize
```

---

## Server Configuration


### Securing Blade Templates

By default, any file in the theme directory is publicly accessible in WordPress. This includes `*.blade.php` files, which — if accessed directly — can expose your view code as plain text. To avoid this, add a web server rule to block public access to `.blade.php` files.

#### Nginx

If you're using **Nginx**, add this to your site configuration **before the final `location` block**:

```nginx
location ~* \.(blade\.php)$ {
    deny all;
}
```

#### Apache

If you're using **Apache**, add this to your virtual host configuration or `.htaccess` file:

```apache
<FilesMatch ".+\.(blade\.php)$">
    # Apache 2.4
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>

    # Apache 2.2
    <IfModule !mod_authz_core.c>
        Order deny,allow
        Deny from all
    </IfModule>
</FilesMatch>
```

**NOTE:** You can find more information about deploying a SAGE theme here

[Deployment](https://roots.io/sage/docs/deployment/)


---


## Required Plugins

* [Advanced Custom Fields](https://www.advancedcustomfields.com/pro/)


---

## Required libraries

* [SAGE](https://github.com/roots/sage?tab=readme-ov-file)
* [Acorn](https://roots.io/acorn/).
* [ACF Composer](https://github.com/Log1x/acf-composer)
* [Sage Directives](https://log1x.github.io/sage-directives-docs/)

---

## Optional libraries

* [Nextly](https://github.com/web3templates/nextly-template)
* [Poet](https://github.com/Log1x/poet)


---

## Bugs & Fixes

* [PHP security vulnerabilities - October 2026](docs/PHP-Security-Update-2026-10.md)

## Copyright and License

Copyright 2025 Codigo Wordpress Theme released under the [MIT](https://github.com/pablorica/sparemytime/blob/main/LICENSE) license.

## Versioning

We use [SemVer](https://semver.org/) for versioning. For the versions available, [list of tags can be found in this page](https://github.com/pablorica/docker-starter-kit/tags).

### Changelog

[CHANGELOG.md](docs/CHANGELOG.md)

