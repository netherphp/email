<?php

namespace Nether\Email\Routes;
use Nether;

use Nether\Atlantis\Routes\Api;
use Nether\Avenue\Meta\RouteHandler;

class OutboundAPI
extends Api {

	#[RouteHandler('/api/email/outbound')]
	public function
	HandleOutbound():
	void {

		$Email = new Nether\Email\Outbound;
		$Email->Render('outbound', [  ]);
		$Sent = $Email->Send();

		$this->SetPayload([
			'From'    => $Email->From,
			'Name'    => $Email->Name,
			'Subject' => $Email->Subject,
			'Content' => $Email->Content,
			'Sent'    => $Sent
		]);

		return;
	}

}
