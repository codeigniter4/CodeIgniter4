<?php

echo number_to_amount(123456); // Returns 123 thousand
echo number_to_amount(123456789); // Returns 123 million
echo number_to_amount(1234567890123, 2); // Returns 1.23 trillion
echo number_to_amount('123,456,789,012', 2); // Returns 123.46 billion
