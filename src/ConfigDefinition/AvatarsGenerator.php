<?php

namespace BlueSpice\Avatars\ConfigDefinition;

class AvatarsGenerator extends \BlueSpice\ConfigDefinition\ArraySetting {

	public function getHtmlFormField() {
		return new \HTMLSelectField( $this->makeFormFieldParams() );
	}

	public function getLabelMessageKey() {
		return 'bs-avatars-pref-generator';
	}

	public function isStored() {
		return true;
	}
}
