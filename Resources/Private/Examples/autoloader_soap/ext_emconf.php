<?php
/**
 * $EM_CONF
 *
 * @author   Tim Lochmüller
 */

/** @var $_EXTKEY string */
$EM_CONF[$_EXTKEY] = [
    'title'       => 'Autoloader (Soap - Try the WSDL with ###YOUR-SERVER###/?eID=SoapServer&server=testService&wsdl=1)',
    'description' => '',
    'constraints' => [
        'depends' => [
            'autoloader' => '3.0.0',
        ],
    ],
];
