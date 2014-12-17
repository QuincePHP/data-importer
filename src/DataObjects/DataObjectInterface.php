<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Contracts\Support\Arrayable;

interface DataObjectInterface extends Arrayable {

	/**
	 * Renew object properties
	 *
	 * @return void
	 */
	public function renew();

}
