<?php

// Inside execute():

// Forward every unbound argument and option to the target command as-is.
$this->call('cache:clear', $this->getUnboundArguments(), $this->getUnboundOptions());
