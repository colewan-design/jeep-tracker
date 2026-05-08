<?php

use Illuminate\Support\Facades\Broadcast;

// Public channel — any client can subscribe to track a specific jeep
Broadcast::channel('jeep.{jeepId}', function () {
    return true;
});
