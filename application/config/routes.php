<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['Admin/uploader'] = 'Lecturas/upload';

$route['Uploader/demo'] = 'Lecturas/demo';


$route['Chart/Proveedores'] = 'Admin/Proveedores';
$route['Admin/dashboard'] = 'manager/dashboard';

$route['Admin/Consolidados'] = 'Consolidados/listados';
$route['Consolidados/list_dt/(:any)/(:any)/(:any)'] = 'Consolidados/list_dt/$1/$2/$3';

//LOTE

$route['Admin/Lotes/viewBatch/(:any)'] = 'Lotes/viewBatch/$1';
$route['Admin/Lotes/lotes_dt'] = 'Lotes/lotes_dt';
$route['Admin/Lotes/Upload'] = 'Lotes/upload';
$route['Admin/Lotes/checkFile'] = 'Lotes/checkFile';
$route['Admin/Lotes/getInfoPanel'] = 'Lotes/getInfoPanel';

//LECTURAS
$route['Admin/Lecturas'] = 'Lecturas/listados';
$route['Admin/Lecturas/cerrarLote'] = 'Lotes/cerrarLote';
$route['Admin/Lecturas/list_dt/(:any)'] = 'Lecturas/list_dt/$1';
$route['Admin/Lecturas/lotes_dt/(:any)'] = 'Lecturas/lotes_dt/$1';
$route['Admin/Lecturas/Views/(:any)'] = 'Lecturas/views/$1';
$route['Admin/Lecturas/Copy/(:any)'] = 'Lecturas/copy/$1';
$route['Admin/Lecturas/indexaciones_dt'] = 'Lecturas/indexaciones_dt';


$route['Admin/Secretarias'] = 'secretarias/listados';
$route['Admin/Secretarias/agregar'] = 'secretarias/agregar';
$route['Admin/Dependencias'] = 'Dependencias/listados';
$route['Admin/Dependencias/agregar'] = 'Dependencias/agregar';
$route['Admin/Dependencias/editar/(:any)'] = 'Dependencias/listados/$1';
$route['Admin/Dependencias/get_dependencias'] = 'Dependencias/get_dependencias';

$route['Admin/Programas'] = 'Programas/listados';
$route['Admin/Programas/agregar'] = 'Programas/agregar';
$route['Admin/Programas/get_programas'] = 'Programas/get_programas';

$route['Admin/Proyectos'] = 'Proyectos/listados';
$route['Admin/Proyectos/agregar'] = 'Proyectos/agregar';
$route['Admin/Proyectos/get_proyectos'] = 'Proyectos/get_proyectos';

$route['Admin/Obras'] = 'Obras/listados';
$route['Admin/Obras/editar/(:any)'] = 'Obras/listados/$1';

$route['Admin/Indexaciones/editar/(:any)'] = 'Indexaciones/listados/$1';
$route['Admin/Indexaciones'] = 'Indexaciones/listados';


$route['Admin/Proveedores'] = 'proveedores/listados';

$route['Admin/Usuarios'] = 'Usuarios/listados';

$route['Usuarios/profiles/'] = 'Usuarios/profiles';
$route['Uploader/index'] = 'Uploader';
$route['Admin/usuarios/agregar/?(:num)?'] = 'usuarios/agregar/$1';
$route['Admin/usuarios/listados'] = 'usuarios/listados';


// ELECTROMECANICA
$route['Electromecanica'] = 'electromecanicax/Electromecanica';
$route['Electromecanica/Lecturas/upload'] = 'electromecanicax/Electromecanica/Lecturas/upload';
$route['Electromecanica/Lecturas'] = 'electromecanicax/Lecturas';
$route['Electromecanica/Lecturas/lotes_dt'] = 'electromecanicax/Lecturas/lotes_dt';
$route['Electromecanica/Lecturas/upload'] = 'electromecanicax/Lecturas/upload';
$route['Electromecanica/Lecturas/checkFile'] = 'electromecanicax/Lecturas/checkfile';
$route['Electromecanica/Lecturas/delete_lote'] = 'electromecanicax/Lecturas/delete_lote';
$route['Electromecanica/Lecturas/indexaciones_dt'] = 'electromecanicax/Lecturas/indexaciones_dt';
$route['Electromecanica/Lecturas/indexaciones_cuenta'] = 'electromecanicax/Lecturas/indexaciones_cuenta';

$route['Electromecanica/Lecturas/viewBatch/?(:any)?'] = 'electromecanicax/Lecturas/viewBatch/$1';
$route['Electromecanica/Lecturas/Views/?(:any)?'] = 'electromecanicax/Lecturas/views/$1';
$route['Electromecanica/Lecturas/leerApi'] = 'electromecanicax/Lecturas/leerApi';

$route['Electromecanica/Indexaciones'] = 'electromecanicax/Indexaciones';
$route['Electromecanica/Indexaciones/list_dt'] = 'electromecanicax/Indexaciones/list_dt';
$route['Electromecanica/Indexaciones/edit'] = 'electromecanicax/Indexaciones/edit';

$route['Electromecanica/Dependencias'] = 'electromecanicax/Dependencias/index';
$route['Electromecanica/Dependencias/list_dt'] = 'electromecanicax/Dependencias/list_dt';
$route['Electromecanica/Dependencias/delete'] = 'electromecanicax/Electromecanica/delete';
$route['Electromecanica/Dependencias/get_dependencias'] = 'electromecanicax/Dependencias/get_dependencias';
$route['Electromecanica/Dependencias/editar/?(:num)?'] = 'electromecanicax/Dependencias/index/$1';

$route['Electromecanica/Proveedores'] = 'electromecanicax/Proveedores/index';
$route['Electromecanica/Proveedores/list_dt'] = 'electromecanicax/Proveedores/list_dt';
$route['Electromecanica/Proveedores/checkApiUrl'] = 'electromecanicax/Proveedores/checkApiUrl';
$route['Electromecanica/Proveedores/delete'] = 'electromecanicax/Electromecanica/delete';
$route['Electromecanica/Proveedores/edit'] = 'electromecanicax/Electromecanica/get_edit';

// ELECTROMECANICA

$route['Admin'] = 'admin';
$route['Login'] = 'auth/login';
$route['Logout'] = 'auth/logout';
$route['default_controller'] = 'auth/login';
$route['404_override'] = 'Error/error_404';
$route['translate_uri_dashes'] = FALSE;
