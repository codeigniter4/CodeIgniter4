<?php

$options = [
    'small'  => 'Small Shirt',
    'med'    => 'Medium Shirt',
    'large'  => 'Large Shirt',
    'xlarge' => 'Extra Large Shirt',
];

$shirts_on_sale = ['small', 'large'];
echo form_dropdown('shirts', $options, 'large');
/*
 * Would produce:
 * <select name="shirts">
 *     <option value="small">Small Shirt</option>
 *     <option value="med">Medium Shirt</option>
 *     <option value="large" selected="selected">Large Shirt</option>
 *     <option value="xlarge">Extra Large Shirt</option>
 * </select>
 */

echo form_dropdown('shirts', $options, $shirts_on_sale);
/*
 * Would produce:
 * <select name="shirts" multiple="multiple">
 *     <option value="small" selected="selected">Small Shirt</option>
 *     <option value="med">Medium Shirt</option>
 *     <option value="large" selected="selected">Large Shirt</option>
 *     <option value="xlarge">Extra Large Shirt</option>
 * </select>
 */
