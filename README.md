# data-importer

Tool for importing data from tabular data files into laravel models

## Installation

Add data-importer to your composer.json file.

for laravel 4:
```json
"require": {
  "opilo/data-importer": "~1.0"
}
```

and for laravel 4:
```json
"require": {
  "opilo/data-importer": "~2.0"
}
```

Use composer to install this package.

```
$ composer update
```

## Registering the Package

Register the service provider within the providers array found in `app/config/app.php`:

```php
'providers' => array(
    'Opilo\DataImporter\DataImporterServiceProvider'
)
```

## Configuration

Next step is to publish package configuration by running:

in laravel 4 run:

```bash
$ php artisan config:publish opilo/data-importer
```

and in laravel 5 run:
```bash
$ php artisan vendor:publish
```

Config file could be found in `app/config/packages/quince/data-importer/config.php` for laravel 4 and in `config/quince/data-importer.php` 

## Usage

You can use data-importer in two ways

### Using Laravel IoC Container

Make sure exporter service manager is registered in your app config.

```php
<?php

// Initialize required data
$filePath = '/absolute/path/to/file/to/import';
$bindingModel = '/Namespace/ToYour/Model'; // or YourModel::class

// get data-importer out of IoC
$importer = App::make('importer');

/** @var RowsCollection $data */
$importer->import($filePath, $bindingModel, function($data) {
	// whatever you want to do with data
	// $data is an instance of RowsCollection
	// for example
	
	/** @var RowData $row */
	foreach ($data as $row) {
	    YourModel::create($row->getBase());
	}
});
```

### Using Laravel Dependency Injection

You can also inject `Quince\DataImporterManager` in your class, and use it.

```php
<?php

use Quince\DataImporterManager as Importer;

class ExampleClass {

	protected $importer

	public function __construct(Importer $importer)
	{
		$this->importer = $importer;
	}

	public function exampleMethod($file)
	{
		$this->importer->import($file->getPath(), DesiredModel::class, function($data) {
			// whatever you want to do with data
		});
	}

}
```

## Advanced Usages

Each row of file is a presenter of a record in database, and each record may have relation records.
In your csv file, a column may be present these relation records.
Header of this column is a concatenation of relation name and field name of relation that is provide in that column
*(Concat with `relation_joint` is set in config file)*.
if a column have many relation records, values should be separated with `relation_value_delimiter` which is set in config file.

#### Example
The file below:

name			| username	    | email					| phones.number
---				|---			|---					|---
John Doe		| Johny			| J.Doe@mail.com 		| 0941120773
Jane Doe		| Jane6			| miss.J.Doe@gmail.com	| 0929339687\|0916740160

will be converted to something like this *(as json)*:

```JSON
[
	{
		"name": "John Doe",
		"username": "Johny",
		"email": "J.Doe@mail.com",
		"phones": [
			{
				"number": "0941120773"
			}
		]
	},
	{
		"name": "Jane Doe",
		"username": "Jane6",
		"email": "miss.J.Doe@gmail.com",
		"phones": [
			{
				"number": "0929339687"
			},
			{
				"number": "0916740160"
			}
		]
	}
]
```

### Additional Fields

When only some of the table fields are provided in given file, you can set those field manualy

> **NOTE:** *You should specify additional fields are for main record or relation records*

```php
<?php

// Initialize required data
$filePath = '/absolute/path/to/file/to/import';
$bindingModel = '/Namespace/ToYour/Model'; // or YourModel::class
$additionalFields = [
	'base' => [
		'column_name' => 'value'
	],
	'relation' => [
		'relation_name' => [
			'column_name' => 'value'
		]
		// another relations goes heare
	]
];

// get data-importer out of IoC
$importer = App::make('importer');

$importer->setAdditionalFields($additionalFields)
         ->import($filePath, $bindingModel, function($data) {
         	// whatever you want to do with data
         });
```

If fields that specified as `additional fields` exist in csv file, data will be loaded from csv file.
But if you pass `true` as second parameter for `setAdditionalFields` aditional fields will overwrite existing data in csv file.

```php
$importer->setAdditionalFields($additionalFields, true)
         ->import($filePath, $bindingModel, function($data) {
         	// whatever you want to do with data
         });
```

### Translating file headers

When column headers of your file are defferent from your table columns, you can map them to your database table columns.
Assume your file is like:

Real Name       | Nickname	    | Mail Address			| Phone Numbers
---				|---			|---					|---
John Doe		| Johny			| J.Doe@mail.com 		| 0941120773
Jane Doe		| Jane6			| miss.J.Doe@gmail.com	| 0929339687\|0916740160

you can pass a dictionary for your file custom header to translate them to their table-column names.

```php
<?php

// Initialize required data
$filePath = '/absolute/path/to/file/to/import';
$bindingModel = '/Namespace/ToYour/Model'; // or YourModel::class
$dictionary = [
	'Real Name'		=> 'name',
	'Nickname'		=> 'username',
	'Mail Address'	=> 'email',
	'Phone Numbers'	=> 'phones.number'
];

// get data-importer out of IoC
$importer = App::make('importer');

$importer->setDictionary($dictionary)
         ->import($filePath, $bindingModel, function($data) {
         	// whatever you want to do with data
         });
```

you can also map a single column of your file to a column in your database table.

```php
$importer->setColumnDictionary($columnName, $translation)
         ->import($filePath, $bindingModel, function($data) {
         	// whatever you want to do with data
         });
```

> For more information about advanced usage visit [wiki pages](https://github.com/QuincePHP/data-importer/wiki)

## Config file

field | description | default value
---|---|---
delimiter | The character is used to separate each column in a row. | `,`
enclosure | The character is used to specify the enclosure. | `"`
escape | The character to escape invalid characters. | `\`
chunk_size | The size of rows to be processed by closure you pass to import method. | 100
relation_joint | The character to join relation name and column of relation table you want to import. | `.`
relation_value_delimiter | The character to delimit relation value from each other. | `\|`
