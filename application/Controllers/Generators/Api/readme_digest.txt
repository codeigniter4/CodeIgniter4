Due to limitations in the database browser we are not able to add an index to the 'digest_key' column we just created
on the user table. For good performance, you should add an index to that field.

While a migration was created, you will need to run the migration with: php sprint migrate app
