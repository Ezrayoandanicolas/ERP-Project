<?php

return [
    'type' => env('THERMAL_PRINTER_TYPE', 'cups'),
    'name' => env('THERMAL_PRINTER', 'thermal-my'),

    // optional
    'ip'   => env('THERMAL_PRINTER_IP'),
    'port' => 9100,
];
