<?php

namespace Nether\Email\Routes;
use Nether;

use Nether\Atlantis\PublicAPI;
use Nether\Avenue\Meta\RouteHandler;

class OutboundAPI
extends PublicAPI {

	#[RouteHandler('/api/outbound/send', Verb: 'POST')]
	public function
	HandleOutbound():
	void {
	/*//
	provide an endpoin for the 'contact us' form on the website. it takes
	an end user supplied message and sends it to the configured primary
	contact.
	//*/

		($this->Request->Data)
		->Email(Nether\Common\Datafilters::Email(...))
		->Message(Nether\Common\Datafilters::TrimmedTextNullable(...));

		$InputName = $this->Request->Data->Name;
		$InputEmail = $this->Request->Data->Email;
		$InputMessage = $this->Request->Data->Message;

		if(!$InputEmail)
		$this->Quit(1, 'Email is required');

		if(!$InputMessage)
		$this->Quit(2, 'Message is required');

		// this email we are about to send to ourselves needs to look like
		// it came from us for auth reasons, but when the agent clicks
		// reply it should start a new message to the person who sent this.

		$Email = new Nether\Email\Outbound;
		$Email->To->Push($Email->From);
		$Email->ReplyTo = $InputEmail;

		$Email->Render('email/outbound', [
			'From'    => $InputEmail,
			'Name'    => $InputName,
			'Subject' => $Email->Subject,
			'Message' => $InputMessage
		]);

		$Email->Send();

		$this->SetPayload([
			'Subject' => $Email->Subject,
			'From'    => $Email->From,
			'ReplyTo' => $Email->ReplyTo,
			'To'      => join(', ', $Email->To->GetData()) ?: NULL,
			'BCC'     => join(', ', $Email->BCC->GetData()) ?: NULL,
			'Content' => $Email->Content
		]);

		return;
	}

}
