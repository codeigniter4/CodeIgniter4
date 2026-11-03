<?php

$agent = $this->request->getUserAgent();

if ($agent->isBrowser()) {
    $currentAgent = $agent->getBrowser() . ' ' . $agent->getVersion();
} elseif ($agent->isRobot()) {
    $currentAgent = $agent->getRobot();
} elseif ($agent->isMobile()) {
    $currentAgent = $agent->getMobile();
} else {
    $currentAgent = 'Unidentified User Agent';
}

echo $currentAgent;

echo $agent->getPlatform(); // Platform info (Windows, Linux, Mac, etc.)
