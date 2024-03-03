<?php
/**
* Plugin Name: APPTA - API conexión
* Description: Este plugin esta construido para realizar conexiones API con APPTA
* Version:     1.0
* Plugin URI: https://github.com/Fullstack-belka/Belka-code
* Author:      Belkacompany
* Author URI:  https://belkacompany.com/
* License:     GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: sms
* Domain Path: /languages
*/

/**
 * Stores the path to TML.
 *
 * @since 6.4.4
 */
define( 'APPTA_PATH', plugin_dir_path( __FILE__ ) );
define( 'APPTA_URL_LOG', plugin_dir_path( __FILE__ ) . 'log.log' );
define( 'APPTA_URL',  plugin_dir_url( __FILE__  ) );
define( 'APPTA_TOKEN', '0100000065f275e971ca252fa6c53b4cf8821eda58d04f7fc557d288cbc275979a67cc834444');

/**
 * Require files.
*/
defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

function appta_custom_error_log( $message ) {
    // Formatea el mensaje con la fecha y hora
    $log_message = '[' . date( 'Y-m-d H:i:s' ) . '] ' . $message . "\n";

    // Añade el mensaje al archivo de registro
    error_log( $log_message, 3, APPTA_URL_LOG );
}



function appta_login_token_api(){
    $api_url = 'https://wikanapi.appta.com.co/login/auth';
    $headers = array( 
        'Content-Type: application/json',
        'usuario: 900665403',
        'clave: PJms9698000@',
        'Authorization: Token '.APPTA_TOKEN
    );
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL =>  $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
    ));
    //execute post
    $response = curl_exec($curl);
    $err = curl_error($curl);
    if($err){
        appta_custom_error_log('ERROR API ' . $err. "\n");
    }
    $data = json_decode($response);
    $token = 'false';
    if(property_exists($data, 'mensaje')){
        appta_custom_error_log('ERROR API ' . $data->mensaje . "\n");
    }else{
        $token = $data->token ;
    }
    return $token;
}

function appta_call_api($url, $postfields, $request){
    
    $api_url = 'https://wikanapi.appta.com.co/'.$url;
    $token = appta_login_token_api();
    $headers = array( 
        'Authorization: Bearer '.$token
    );

    //open connection
    $curl = curl_init();
    if($postfields){
        $parsePostfields = json_encode($postfields);
    }else{
        $parsePostfields = '';
    }

    curl_setopt_array($curl, array(
        CURLOPT_URL =>  $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $request,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $parsePostfields,
    ));

    //execute post
    $response = curl_exec($curl);
    $err = curl_error($curl);
    if($err){
        appta_custom_error_log('ERROR API ' . $err. "\n");
    }
	$data = json_decode($response);

    curl_close($curl);
    return $data;
}


// Función para imprimir la variable JavaScript en el pie de página
function agregar_id_solicitud_js() {

    // Verificar si el post actual pertenece a la categoría con ID 8
    if (is_single() && has_category(8)) {
        // Obtener el valor del campo personalizado 'idSolicitud'
        $id_solicitud = get_post_meta(get_the_ID(), 'idsolicitud', true);
        // Imprimir el valor como una variable JavaScript si existe
        if (empty($id_solicitud)) {
            $id_solicitud = get_the_title();
        }

        echo '<script> var id_oferta = "'.$id_solicitud.'";</script>';
    }
}
// Agregar la función al gancho 'wp_header'
add_action('wp_head', 'agregar_id_solicitud_js');


require APPTA_PATH . 'includes/crons.php';


function APPTAadmin_menu()
{
    global $supporthost_sample_page;
    add_menu_page('APPTATEST', 'APPTATEST', 'activate_plugins', 'APPTATEST', 'APPTAread_post', 'dashicons-database');
}

add_action('admin_menu', 'APPTAadmin_menu');