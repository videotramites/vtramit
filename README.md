# VTramit

Nextcloud App for administrative proceedings with public administrations through Videoconferences

## Development setup
### Install from git

Please make sure you have installed the following dependencies: `make, npm, curl, composer, node-js`

If you want to run the latest development version from git source, you need to clone the repo to your apps folder and exeucte:

```
make composer
composer install
```
#### Generate Swagger API file

To generate the file execute:

```
./vendor/bin/openapi --output vtramit-api.yml --pattern "*.php" ./lib
```

The api file is generated at the root of the project as vtramit-api.yml