<?php

return [

	/*
	|--------------------------------------------------------------------------
	| CSV delimiter character
	|--------------------------------------------------------------------------
	|
	| The character is used to seperate each column in a row
	|
	*/
    'delimiter' => ',',

	/*
	|--------------------------------------------------------------------------
	| Enclosure character
	|--------------------------------------------------------------------------
	|
	| The character is used to specify the enclosure
	|
	*/
	'enclosure' => '"',

	/*
	|--------------------------------------------------------------------------
	| Escape character
	|--------------------------------------------------------------------------
	|
	| The character to escape invalid charcters
	|
	*/
	'escape' => '\\',

	/*
	|--------------------------------------------------------------------------
	| Chunk size
	|--------------------------------------------------------------------------
	|
	| The size of rows to be processed by closure you pass to import method
	|
	*/
	'chunk_size' => '100',

	/*
	|--------------------------------------------------------------------------
	| Chunk size
	|--------------------------------------------------------------------------
	|
	| The character to join relation name and column of relation table you want
	| to import.
	|
	*/
	'relation_joint' => '.',

];
