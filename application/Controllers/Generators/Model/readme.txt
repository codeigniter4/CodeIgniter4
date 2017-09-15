NAME
	model - creates a new Model based on CIDbModel

SYNOPSIS
	model [model_name] ...

DESCRIPTION
	Will create a model file based off of Myth\Models\CIDbModel. If no options are present, the user will be prompted for the option values. If an existing database table is specified, the system will do it's best job at determining field types and setting generic validation rules. You should always edit the file to match your needs after generating it.

OPTIONS
	-table_name     The name of the database table to use.

	-key            The name of the primary key column.

	-set_created    If present, will set dates for created_by_field.

	-set_modified   If present, will set dates for modified_by_field.

	-date_format    The format dates are store for created_on and modified_on fields. Can be 'datetime', 'date' or 'int'.

	-log_user       If present, will log user primary key for creation, modification and deletion activities.

	-soft_delete    If present, will turn on 'soft deletes'.
