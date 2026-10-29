# VTramit

Nextcloud App for administrative proceedings with public administrations through Videoconferences

# Development setup
## Install

Please make sure you have installed the following dependencies: `make, npm, curl, composer, node-js`

If you want to run the latest development version from git source, you need to clone the repo to your apps folder and exeucte:

```
make composer
sudo npm ci
composer install
```

## Generate API yml file

The api file is generated at the root of vtramit project with vtramit-api.yml

### Install Swagger library
```
composer install
```

### Generate Swagger API file
To generate the files for v2 and v3, execute:
```
make open-api
```

### Generate JS files
Execute:
```
make build-js
```

## Swagger

The swagger-php library used is: [https://github.com/zircote/swagger-php](https://github.com/zircote/swagger-php)

See docs here:

* [Guide](https://zircote.github.io/swagger-php/guide/)
* [Elements Reference](https://zircote.github.io/swagger-php/reference/attributes.html)

## Test with CURL

Mark an appointment as "user is waiting to be attended":
```
curl -i -H "Accept: application/json" -H "Content-Type: application/json" -umy-admin:my-password -X GET https://www.mydomain.com/index.php/apps/vtramit/api/0.1/waitingforvc/{appointment identifier}
```

# Development Tips

## ResponseInfo

The `ResponseInfo` is an object thought to communicate from the services to the frontend. It is basically a JSON data with at least a `'result'` entry.

The elements you can find in the ResponseInfo object are:

| Element | Type | Values | Description |
| --- | --- | --- | --- |
| result | string | OK / ERROR / EXCEPTION | Indicates the result of the operation |
| message | string |  | A message to display |
| entity | JSON |  | The entity |
| list | array |  | List of entities retrieved |
| redirect | array |  | A URL where the frontend should redirect on receiving de message |

Following additional elements are used usually on requests about context:

| Element | Type | Values | Description |
| --- | --- | --- | --- |
| channels | array |  | List of channels |
| groups | array |  | List of groups |
| users | array |  | List of users |
| transitions | array |  | List of allowed status actions |
| isAdmin | bool | true / false | Indicates wether the user is admin or regular staff |

## Change to "Finished" status

When changing to "Finished" status it is required that use indicates which is the channel used. If only one channel available, then use automatically that channel. If more than one channel is available, the system shows the available channels for the procedure type so the staff have to indicates which one has been the channel used.

When using the "videoconference finished" method, the channel is automatically set to "videoconference".

## Procedure: Videoconference notification

The procedure notification consist in 3 steps:

* Jitsi sends a notification to vTramit when a stakeholder is waiting
* The vTramit client notifies the staff user
* Jitsi sends a notification to vTramit when the videoconference is finished

### [POST] /api/2.0/procedure/videoconference/{jitsiRoomCode}/waiting

This endpoint is used by Jitsi to notify to vTramit when a stakeholder is waiting.

The `{jitsiRoomCode}` is the room code of the Jitsi URL in usage by the stakeholder.

### [GET] /api/2.0/procedure/videoconference/op/waiting

This enpoint is used by the vTramit frontend to display who is waiting to be attended.

### [POST] /api/2.0/procedure/videoconference/{jitsiRoomCode}/finished

This endpoint is used by Jitsi to notify to vTramit when the videoconference is finished.

The `{jitsiRoomCode}` is the room code of the Jitsi URL in usage by the stakeholder.
