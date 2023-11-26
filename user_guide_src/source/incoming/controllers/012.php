<?php

/*
 * Folder and file structure:
 * \<NamespaceName>(\<SubNamespaceNames>)*\<ClassName>
 */

$routes->get('helloworld', '\App\Controllers\HelloWorld::index');
