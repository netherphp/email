<?php

namespace Nether\Email\Struct;

use Nether\Common;

class ServiceConfigInfo
extends Common\Prototype {

	public string
	$Key;

	public string
	$Name;

	public bool
	$Ready;

	public bool
	$IsDefault;

	public Common\Datastore
	$ConfigKeys;

	public function
	IsReady():
	bool {

		return $this->Ready;
	}

}
