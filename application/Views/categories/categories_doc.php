<!doctype html>
<html>
    <head>
        <style>
            .word-table {
                border:1px solid black !important; 
                border-collapse: collapse !important;
                width: 100%;
            }
            .word-table tr th, .word-table tr td{
                border:1px solid black !important; 
                padding: 5px 10px;
            }
        </style>
    </head>
    <body>
        <h2>Categories List</h2>
        <table class="word-table" style="margin-bottom: 10px">
            <tr>
                <th>No</th>
		<th>Name</th>
		<th>Date</th>
		
            </tr><?php
            foreach ($categories_data as $categories)
            {
                ?>
                <tr>
		      <td><?= ++$start ?></td>
		      <td><?= $categories->name ?></td>
		      <td><?= $categories->date ?></td>	
                </tr>
                <?php
            }
            ?>
        </table>
    </body>
</html>