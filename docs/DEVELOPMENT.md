# Development setup

## Install NodeJS

You must choose the desired NodeJS version:

```
curl -sL https://deb.nodesource.com/setup_20.x | bash -
apt install nodejs
```

Or better, you can install nvm:

```
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.2/install.sh | bash
```

And then you can install any version easily with:

```
nvm install 20
```

And finally, use your desired version:
```
nvm use 20
```

## Install from git

Please make sure you have installed the following dependencies: `make, npm, curl, composer, node-js`

If you want to run the latest development version from git source, you need to clone the repo to your apps folder and execute:

```
make composer
sudo npm ci
composer install
```

## Generate Swagger API file

The api file is generated at the root of vtramit project. The files generated are:
- vtramit-api-v2.yml
- vtramit-api-v3.yml

These files correspond to versions 2 and 3 of Swagger.

To generate the files, you must run:
```
make open-api
```

## Build VUE projects to JS
There are 3 VUE projects:
- vue/admin : the administration panel for the application configuration
- vue/proceduresViews : the procedures panel for the company staff
- vue/appPublic : the public view for the customers

You can build all the projecte by executing:
```
make build-dev
```

That command will build all the VUE project in development version.

Next are explained all the available build options for the `make` command:

- build: builds all the VUE projects optimized for production
- build-dev: builds all the VUE projeces for a development environment
- build-dev-admin: builds the *admin* VUE project for a development environment
- build-dev-appPublic: builds the *appPublic* VUE project for a development environment
- build-dev-proceduresView: builds the *proceduresView* VUE project for a development environment
