<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
// $route['Manager/(:any)/(:any)'] = 'manager/$1/$2';
$route['Admin/uploader'] = 'Lecturas/upload';

$route['Uploader/demo'] = 'Lecturas/demo';



$route['Admin/dashboard'] = 'manager/dashboard';

$route['Admin/Consolidados'] = 'Consolidados/listados';
$route['Consolidados/list_dt/(:any)/(:any)/(:any)'] = 'Consolidados/list_dt/$1/$2/$3';



$route['Admin/Lecturas'] = 'Lecturas/listados';
//LOTE
$route['Admin/Lecturas/cerrarLote'] = 'Lotes/cerrarLote';

$route['Admin/Lotes/viewBatch/(:any)'] = 'Lotes/viewBatch/$1';
$route['Admin/Lotes/lotes_dt'] = 'Lotes/lotes_dt';
$route['Admin/Lotes/Upload'] = 'Lotes/upload';
$route['Admin/Lotes/checkFile'] = 'Lotes/checkFile';
$route['Admin/Lotes/getInfoPanel'] = 'Lotes/getInfoPanel';

$route['Admin/Lecturas/list_dt/(:any)'] = 'Lecturas/list_dt/$1';
$route['Admin/Lecturas/lotes_dt/(:any)'] = 'Lecturas/lotes_dt/$1';


$route['Admin/Lecturas/Views/(:any)'] = 'Lecturas/views/$1';

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
// $route['Admin/Indexaciones/agregar'] = 'Indexaciones/agregar';


$route['Admin/Proveedores'] = 'proveedores/listados';
$route['Admin/Proveedores/agregar'] = 'proveedores/agregar';

$route['Admin/Usuarios'] = 'Usuarios/listados';

$route['Usuarios/profiles/'] = 'Usuarios/profiles';
$route['Uploader/index'] = 'Uploader';
$route['Admin/usuarios/agregar/?(:num)?'] = 'usuarios/agregar/$1';
$route['Admin/usuarios/listados'] = 'usuarios/listados';


$route['Admin'] = 'admin';
$route['Login'] = 'auth/login';
$route['Logout'] = 'auth/logout';
$route['default_controller'] = 'auth/login';
$route['404_override'] = 'Admin/Consolidados';
$route['translate_uri_dashes'] = FALSE;
