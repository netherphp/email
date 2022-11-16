<?php

namespace Nether\Email\Routes;
use Nether;

use Nether\Atlantis\Routes\Web;
use Nether\Avenue\Meta\RouteHandler;

class OutboundWeb
extends Web {

	#[RouteHandler('/contact')]
	public function
	PageContact():
	void {

		($this->App->Surface)
		->Wrap('email/contact');

		return;
	}

}
