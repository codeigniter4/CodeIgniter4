<?php

echo number_to_currency(1234.56, 'USD', 'en_US', 2);  // Returns $1,234.56
echo number_to_currency(1234.56, 'EUR', 'de_DE', 2);  // Returns 1.234,56 €
echo number_to_currency(1234.56, 'GBP', 'en_GB', 2);  // Returns £1,234.56
echo number_to_currency(1234.56, 'YEN', 'ja_JP', 2);  // Returns YEN 1,234.56
