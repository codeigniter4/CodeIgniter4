<?php

use CodeIgniter\CLI\CLI;

class SampleCommand extends \BaseCommand
{
    // ...
    private function processOrder(array $orderData): void
    {
        // Critical section - no interruptions allowed
        $result = $this->withSignalsBlocked(function () use ($orderData) {
            CLI::write('Starting critical transaction - signals blocked', 'yellow');

            // Start database transaction
            $this->db->transStart();

            try {
                // Create order record
                $orderId = $this->createOrder($orderData);

                // Update inventory
                $this->updateInventory($orderData['items']);

                // Process payment
                $this->processPayment($orderId, $orderData['payment']);

                // Commit transaction
                $this->db->transCommit();

                CLI::write('Transaction completed successfully', 'green');

                return $orderId;
            } catch (\Exception $e) {
                // Rollback on error
                $this->db->transRollback();

                throw $e;
            }
        });

        CLI::write('Critical section complete - signals restored', 'cyan');
        CLI::write("Order {$result} processed successfully", 'green');
    }
}
