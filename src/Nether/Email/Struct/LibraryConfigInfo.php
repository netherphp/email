<?php

namespace Nether\Email\Struct;

use Nether\Common;

use Nether\Email\Library;
use Nether\Email\Outbound;

class LibraryConfigInfo {

	public ?string
	$DefaultKey;

	public ?string
	$DefaultName;

	public Common\Datastore
	$ConfigKeys;

	public Common\Datastore
	$Services;

	public function
	__Construct() {

		$this->DefaultKey = Library::Get(Library::ConfOutboundVia);
		$this->DefaultName = Outbound::GetViaName($this->DefaultKey);

		$this->ConfigKeys = new Common\Datastore([
			'Email\Library::ConfOutboundVia'     => 'string (Email\Outbound::Via[SMTP|Mailjet|SendGrid])',
			'Email\Library::ConfOutboundFrom'    => 'string (email address)',
			'Email\Library::ConfOutboundReplyTo' => 'string (email address)',
			'Email\Library::ConfOutboundName'    => 'string (email display name)',
			'Email\Library::ConfLogFile'         => 'string (file path)'

		]);

		$this->Services = new Common\Datastore([
			Outbound::ViaMailjet
			=> new ServiceConfigInfo([
				'Key'        => Outbound::ViaMailjet,
				'Name'       => Outbound::GetViaName(Outbound::ViaMailjet),
				'Ready'      => Library::IsConfiguredMailjet(),
				'IsDefault'  => ($this->DefaultKey === Outbound::ViaMailjet),
				'ConfigKeys' => new Common\Datastore([
					'Email\Library::ConfMailjetPublicKey'  => 'string',
					'Email\Library::ConfMailjetPrivateKey' => 'string'
				])
			]),

			Outbound::ViaSendGrid
			=> new ServiceConfigInfo([
				'Key'        => Outbound::ViaSendGrid,
				'Name'       => Outbound::GetViaName(Outbound::ViaSendGrid),
				'Ready'      => Library::IsConfiguredSendGrid(),
				'IsDefault'  => ($this->DefaultKey === Outbound::ViaSendGrid),
				'ConfigKeys' => new Common\Datastore([
					'Email\Library::ConfSendGridKey' => 'string'
				])
			]),

			Outbound::ViaSMTP
			=> new ServiceConfigInfo([
				'Key'        => Outbound::ViaSMTP,
				'Name'       => Outbound::GetViaName(Outbound::ViaSMTP),
				'Ready'      => Library::IsConfiguredSMTP(),
				'IsDefault'  => ($this->DefaultKey === Outbound::ViaSMTP),
				'ConfigKeys' => new Common\Datastore([
					'Email\Library::ConfServerHost'     => 'string',
					'Email\Library::ConfServerPort'     => 'int',
					'Email\Library::ConfServerUsername' => 'string',
					'Email\Library::ConfServerPassword' => 'string'
				])
			])
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetDefaultService():
	?ServiceConfigInfo {

		if(!$this->DefaultKey)
		return NULL;

		if(!$this->Services->HasKey($this->DefaultKey))
		return NULL;

		return $this->Services[$this->DefaultKey];
	}

	public function
	GetReadyServices():
	Common\Datastore {

		return $this->Services->Distill(
			fn(ServiceConfigInfo $SInfo)
			=> $SInfo->Ready === TRUE
		);
	}

	public function
	HasDefaultService():
	bool {

		return is_string($this->DefaultKey);
	}

	public function
	IsDefaultKey(string $Key):
	bool {

		return ($this->DefaultKey === $Key);
	}

}
