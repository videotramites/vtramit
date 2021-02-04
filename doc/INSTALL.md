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


This application hasn't yet been added into apps.nextcloud.com, we will do our best to have it added in the forthcomming weeks.

In order to install it, first build the application as stated on [README.md](../README.md), then copy the folder under your Nextcloud apps folder, with name "vtramit".

Then you'll be able to enable it from your Nextcloud Apps section.


You will need to create some folders from an account with administrative priviledges, for instance "MYWORKGROUP1", matching the name of one user group in your Nextcloud. Share that folder with "MYWORKGROUP1".

Then, add the following directives into your config.php in order to enable its usage with vtramit:

<pre><code>
	//	Admin user owning the shared folders
	  'vtramit.admin' => 'your-admin-user',
	  'vtramit.history.days' => 4,
	  'vtramit.mails.allowed' => true,
	//	callto:34623001200
	  'vtramit.phone.link' => 'callto',
	  'vtramit.phone.prefix' => '',
	//	Name of folders for users
	  'vtramit.folder.upload' => 'Input',
	  'vtramit.folder.download' => 'Output',
	//	Jitsi URLs
	  'vtramit.jitsi.citizen.url' => 'https://jitsi-url-for-citizens.org',
	  'vtramit.jitsi.staff.url' => 'https://jitsi-url-for-workers.org',
	//	Enabled Groups
	  'vtramit.groups' => 
	  array (
	    0 => 'MYWORKGROUP1',
	    1 => 'MYWORKGROUP2'
	  ),
	//	Shown on mails
	  'vtramit.group.settings' => 
	  array (
	    'MYWORKGROUP1' => 
	    array (
	      'fullname' => 'WorkGroup 1',
	      'address' => '12th Example Street',
	      'cp' => '08221',
	      'phone' => '34623001200',
	    ),
	    'MYWORKGROUP2' => 
	    array (
	      'fullname' => 'Workgroup 2',
	      'address' => '14th Example Street',
	      'cp' => '08005',
	      'phone' => '34623001200',
	    ),
	  ),
	  'vtramit.group.mailSettings' => 
	  array (
	    'MYWORKGROUP1' => 
	    array (
	      'subject' => 'Appointment for a video call with WORKGROUP 1',
	    ),
	    'MYWORKGROUP2' => 
	    array (
	      'subject' => 'Appointment for a video call with WORKGROUP 2',
	    ),
	    'default' => 
	    array (
	      'subject' => 'Appointment for a video call with Ajuntament de Barcelona',
	    ),
	  ),

</pre></code>

If do you need further assistance, don't hesitate in contacting us at floss.cat.

