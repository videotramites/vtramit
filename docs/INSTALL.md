# Development setup

## Install from git

Please make sure you have installed the following dependencies: `make, npm, curl, composer, node-js`

If you want to run the latest development version from git source, you need to clone the repo to your apps folder and exeucte:

```
make composer
composer install
```
### Generate Swagger API file

To generate the file execute:

```
./vendor/bin/openapi --output vtramit-api.yml --pattern "*.php" ./lib
```

The api file is generated at the root of the project as vtramit-api.yml


## INSTALL IN NEXTCLOUD

This application has been added to apps.nextcloud.com, proceed to install as any other Nextcloud app.

You will need to create some folders from an account with administrative priviledges, for instance "MYWORKGROUP1", matching the name of one user group in your Nextcloud. Share that folder with "MYWORKGROUP1".

The app will require a few settings on Nextcloud configuration to work.
<pre><code>
  'vtramit.mode' => 'production',
  'vtramit.backoffice.entryPoint' => 'procedure',
  'vtramit.timezone'       => 'Europe/Berlin',
  'vtramit.dateFormat'     => 'd/m/Y',
  'vtramit.timeFormat'     => 'H:i',
  'vtramit.admin'          => 'adminusername',
  'vtramit.history.days'   => 4,
</pre></code>

The procedure types, enabled features, jitsi backend and email templates can be configured in VTramit's configuration settings section inside Nextcloud.

To get a complete environment you will also need:

- Jitsi Meet (ideally with JWT authentication) and an integration to upload recordings back into Nextcloud if necessary
- Webhook to report customers waiting to be attended (waiting to start videoconference)
- Mailer app that fetches mails queued in VTramit.
- Appointment synchronization/integration between your booking platform and VTramit
- Reporting: fetch logs from different endpoints and get KPI for the service
- Digital signatures: VTramit is integrated with CAProxy, our digital signatures broker that allows us to abstract from different signature providers. Currently it supports the 2 standard methods for Catalan public administrations plus Remote and Biometric signatures of ValidatedID (EU & US compliant).

For any of these integrations, refer to VTramit API ( Swagger 2.0 & OpenAPI 3.0 ), or request our services for an enterprise turnkey solution.

In any case, we will be glad to help you implement it and share it with the community.

If do you need further assistance, don't hesitate in contacting us at https://videoatencion.com


