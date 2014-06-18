#!/usr/bin/env php
<?php
chdir(__DIR__);

$returnStatus = null;
passthru('composer install --dev', $returnStatus);
if ($returnStatus !== 0) {
    exit(1);
}

require 'vendor/autoload.php';

passthru('./vendor/bin/phpcs --standard=' . __DIR__ . '/vendor/dominionenterprises/dws-coding-standard/DWS -n src tests *.php', $returnStatus);
if ($returnStatus !== 0) {
    exit(1);
}

$phpunitConfiguration = PHPUnit_Util_Configuration::getInstance(__DIR__ . '/phpunit.xml');
$phpunitArguments = array(
    'reportUselessTests' => true,
    'strictCoverage' => true,
    'disallowTestOutput' => true,
    'enforceTimeLimit' => true,
    'disallowTodoAnnotatedTests' => true,
    'coverageHtml' => 'coverage',
    'coverageClover' => 'clover.xml',
    'configuration' => $phpunitConfiguration,
);
$testRunner = new PHPUnit_TextUI_TestRunner();
$result = $testRunner->doRun($phpunitConfiguration->getTestSuiteConfiguration(), $phpunitArguments);
if (!$result->wasSuccessful()) {
    exit(1);
}

$xml = new SimpleXMLElement(file_get_contents('clover.xml'));
foreach ($xml->xpath('//file/metrics') as $metric) {
    if ((int)$metric['elements'] !== (int)$metric['coveredelements']) {
        file_put_contents('php://stderr', "Code coverage was NOT 100%\n");
        exit(1);
    }
}

echo "Code coverage was 100%\n";
