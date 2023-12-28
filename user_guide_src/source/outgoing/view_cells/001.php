// In a View.

// Simple Cell
<?= view_cell('App\Cells\MyClass::myMethod', ['param1' => 'value1', 'param2' => 'value2']) ?>

// Controlled Cell
<?= view_cell('App\Cells\MyCell', ['param1' => 'value1', 'param2' => 'value2']) ?>
