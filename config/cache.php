<?php

return [
	'default' => [
		'adapter' => \Bitrix\Main\Lib\Cache\Adapters\FilesystemAdapter::class,
		'options' => [
			'dir' => 'cache/managed'
		]
	]
];