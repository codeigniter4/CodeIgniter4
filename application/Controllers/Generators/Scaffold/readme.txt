Before your new module can be successfully used, you must run the migration to get the database current.
You can do this with the following line:

    php sprint database migrate

If you have scaffolded this as part of a module, then pass the module name after that, like:

    php sprint database migrate {module_name}
