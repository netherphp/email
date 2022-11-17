<?php

namespace Nether\Email\Routes;
use Nether;

use Nether\Atlantis\Routes\PublicWeb;
use Nether\Avenue\Meta\RouteHandler;

class OutboundWeb
extends PublicWeb {

	#[RouteHandler('/contact')]
	public function
	PageContact():
	void {

		($this->App->Surface)
		->Wrap('email/contact');

		return;
	}

}
