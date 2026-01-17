<?php

use src\Utils\RelogioTimeZone;

require_once __DIR__ . '/bootstrap.php';

echo "Current Timezone: " . RelogioTimeZone::obterTimeZone()->getName() . PHP_EOL;
echo "Current Date and Time: " . RelogioTimeZone::agora()->format('d-m-Y H:i:s') . PHP_EOL;