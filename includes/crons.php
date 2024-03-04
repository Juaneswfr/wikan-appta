<?php
function APPTAread_opotunidades(){

    // Obtener la fecha anterior
    $fecha_anterior = date('Y-m-d H:i:s', strtotime('-1 day'));
    // Obtener la fecha siguiente
    $fecha_siguiente = date('Y-m-d H:i:s', strtotime('+1 day'));
    appta_custom_error_log('SE ACCIONO CRON | FECHA INICIO: '.$fecha_anterior.' | FECHA FIN: '.$fecha_siguiente);
    $params = array(
        'idTipoSolicitud' => '1',
        'fechaInicio' => $fecha_anterior,
        'fechaFin' => $fecha_siguiente
    );
    $query_string = http_build_query($params);
    $resume = appta_call_api( 'solicitudes/listadoVacantes?'.$query_string , '' , 'GET');
    // SI NOS DIO DATA ENTONCES A CREAR O ACTUALIZAR LOS POST
    if($resume){
        // Convertir el array a JSON
        if (is_object($resume)) {
            // La propiedad "mensaje" existe, puedes acceder a su valor
            appta_custom_error_log('Devolvio un mensaje la consulta: '.$resume->mensaje. "\n");
        }else{
            foreach ( $resume as $solicitud ) {
                try { 
                    // Buscar el post por meta key y meta value
                    $args = array(
                        'post_type' => 'post', // Tipo de post
                        'meta_query' => array(
                            array(
                                'key' => 'idSolicitud', // Nombre del campo meta
                                'value' => $solicitud->idSolicitud, // Valor específico a buscar
                                'compare' => '=' // Comparar valor exacto
                            )
                        )
                    );
                    $existing_posts = get_posts($args);
                    // Si se encuentra al menos un post
                    if ($existing_posts) {
                        foreach ($existing_posts as $post) {
                            $post_id = $post->ID;
                            $post_title = $solicitud->perfil.' N'.$solicitud->idSolicitud;
                            $requisitos = '';
                            $post_status = 'publish';
                            if($solicitud->desEstado != 'ACTIVA'){
                                $post_status = 'draft';
                            }
                            // Datos del post
                            $post_data = array(
                                'ID' => $post_id,
                                'post_title'    => $post_title,
                                'post_name' => sanitize_title($post_title),
                                'post_content'  => $solicitud->descripcion,
                                'post_status'   => 'publish',
                                'post_author'   => 1, // ID del autor del post
                                'post_category' => array(8),
                                'post_type'     => 'post' // Tipo de post (puede ser 'post', 'page', etc.)
                            );
                            
                            if($solicitud->requisitos !== null){
                                $requisitos .= '<ul>';
                                // Recorremos los requisitos
                                foreach ($solicitud->requisitos as $requisito) {
                                    $requisitos .= '<li>';
                                    $requisitos .= '<strong>' . $requisito->requisito . ':</strong> ' .  $requisito->observaciones;
                                    $requisitos .= '</li>';
                                }
                                $requisitos .= '</ul>';
                            }
                            // Actualizar el post
                            $updated_post_id = wp_update_post($post_data);
                            // Actualizar el valor del campo meta 'idSolicitud'
                            update_post_meta($post_id, 'idsolicitud', $solicitud->idSolicitud);
                            update_post_meta($post_id, 'salario', $solicitud->salario);
                            // Establecer la imagen destacada
                            update_post_meta($post_id, 'modalidad', $solicitud->modalidad);
                            update_post_meta($post_id, 'locacion', $solicitud->localidad);
                            update_post_meta($post_id, 'nivel_academico', $solicitud->nivel_academico);
                            update_post_meta($post_id, 'contrato', $solicitud->contrato);
                            update_post_meta($post_id, 'empresa', $solicitud->nombreEmpresa);
                            update_post_meta($post_id, 'experiencia', $solicitud->experiencia);
                            update_post_meta($post_id, 'horario', $solicitud->horario);
                            update_post_meta($post_id, 'area', $solicitud->area);
                            update_post_meta($post_id, 'fecha_de_creacion', $solicitud->fechaCreacion);
                            update_post_meta($post_id, 'fecha_de_cierre', $solicitud->fechaCierre);
                            update_post_meta($post_id, 'requisitos', $requisitos);
                            update_post_meta($post_id, '_thumbnail_id', '5502');

                            // Verificar si la actualización fue exitosa
                            if (is_wp_error($updated_post_id)) {
                                throw new Exception( '<br>Error al actualizar el post: ' . $updated_post_id->get_error_message() );
                            }
                        }
                    // Si no creamos el post
                    }else{
                        $post_title = $solicitud->perfil.' N'.$solicitud->idSolicitud;
                        // Datos del post
                        $post_data = array(
                            'post_title'    => $post_title,
                            'post_name' => sanitize_title($post_title),
                            'post_content'  => $solicitud->descripcion,
                            'post_status'   => 'publish',
                            'post_author'   => 1, // ID del autor del post
                            'post_category' => array(8),
                            'post_type'     => 'post' // Tipo de post (puede ser 'post', 'page', etc.)
                        );
                        // Insertar el nuevo post
                        $new_post_id = wp_insert_post($post_data);
                        $requisitos = '';
                        if($solicitud->requisitos !== null){
                            $requisitos .= '<ul>';
                            // Recorremos los requisitos
                            foreach ($solicitud->requisitos as $requisito) {
                                $requisitos .= '<li>';
                                $requisitos .= '<strong>' . $requisito['requisito'] . ':</strong> ' . $requisito['observaciones'];
                                $requisitos .= '</li>';
                            }
                            $requisitos .= '</ul>';
                        }
                        // Verificar si la inserción fue exitosa
                        if (is_wp_error($new_post_id)){                    
                            throw new Exception( '<br>Error al crear el post: ' . $new_post_id->get_error_message() );
                        }
                    }
                } catch (Exception $e) {
                    appta_custom_error_log($e->getMessage(). "\n");
                    // Captura y manejo del error
                }
            }
        }
    }
}
// Programar la ejecución de la función cada hora
add_action('APPTACron', 'APPTAread_opotunidades');
if (!wp_next_scheduled('APPTACron')) {
    wp_schedule_event(time(), 'hourly', 'APPTACron');
}


// Ejecutar el cron manualmente
//do_action( 'APPTACron' );


function APPTAupdate_all_oportunidades(){

    echo '<h1>Pruebas APPTA</h1>';
    $APPTAToken = new APPTAtoken();
    $tokenA = $APPTAToken->get_token();
    echo '<h2> TOKEN '.$tokenA['token'] .'</h2> ';

    // Obtener la fecha anterior
    //$fecha_anterior = '2023-01-01 00:00:00';
    $fecha_anterior = date('Y-m-d H:i:s', strtotime('-1 day'));
    // Obtener la fecha siguiente
    $fecha_siguiente = date('Y-m-d H:i:s', strtotime('+1 day'));
    $params = array(
        'idTipoSolicitud' => '1',
        'fechaInicio' => $fecha_anterior,
        'fechaFin' => $fecha_siguiente
    );
    $query_string = http_build_query($params);
    $resume = appta_call_api( 'solicitudes/listadoVacantes?'.$query_string , '' , 'GET');
    echo '<pre>';
    // SI NOS DIO DATA ENTONCES A CREAR O ACTUALIZAR LOS POST
    if($resume){
        // Convertir el array a JSON
        if (is_object($resume)) {
            // La propiedad "mensaje" existe, puedes acceder a su valor
            appta_custom_error_log('Devolvio un mensaje la consulta: '.$resume->mensaje. "\n");
        }else{
            foreach ( $resume as $solicitud ) {
                try { 
                    // Buscar el post por meta key y meta value
                    $args = array(
                        'post_type' => 'post', // Tipo de post
                        'meta_query' => array(
                            array(
                                'key' => 'idSolicitud', // Nombre del campo meta
                                'value' => $solicitud->idSolicitud, // Valor específico a buscar
                                'compare' => '=' // Comparar valor exacto
                            )
                        )
                    );
                    $existing_posts = get_posts($args);
                    // Si se encuentra al menos un post
                    if ($existing_posts) {
                        foreach ($existing_posts as $post) {
                            $post_id = $post->ID;
                            $post_title = $solicitud->perfil.' N'.$solicitud->idSolicitud;
                            $requisitos = '';
                            $post_status = 'publish';
                            if($solicitud->desEstado != 'ACTIVA'){
                                $post_status = 'draft';
                            }
                            // Datos del post
                            $post_data = array(
                                'ID' => $post_id,
                                'post_title'    => $post_title,
                                'post_name' => sanitize_title($post_title),
                                'post_content'  => $solicitud->descripcion,
                                'post_status'   => 'publish',
                                'post_author'   => 1, // ID del autor del post
                                'post_category' => array(8),
                                'post_type'     => 'post' // Tipo de post (puede ser 'post', 'page', etc.)
                            );
                            
                            if($solicitud->requisitos !== null){
                                $requisitos .= '<ul>';
                                // Recorremos los requisitos
                                foreach ($solicitud->requisitos as $requisito) {
                                    $requisitos .= '<li>';
                                    $requisitos .= '<strong>' . $requisito->requisito . ':</strong> ' .  $requisito->observaciones;
                                    $requisitos .= '</li>';
                                }
                                $requisitos .= '</ul>';
                            }
                            // Actualizar el post
                            $updated_post_id = wp_update_post($post_data);
                            // Actualizar el valor del campo meta 'idSolicitud'
                            update_post_meta($post_id, 'idsolicitud', $solicitud->idSolicitud);
                            update_post_meta($post_id, 'salario', $solicitud->salario);
                            // Establecer la imagen destacada
                            update_post_meta($post_id, 'modalidad', $solicitud->modalidad);
                            update_post_meta($post_id, 'locacion', $solicitud->localidad);
                            update_post_meta($post_id, 'nivel_academico', $solicitud->nivel_academico);
                            update_post_meta($post_id, 'contrato', $solicitud->contrato);
                            update_post_meta($post_id, 'empresa', $solicitud->nombreEmpresa);
                            update_post_meta($post_id, 'experiencia', $solicitud->experiencia);
                            update_post_meta($post_id, 'horario', $solicitud->horario);
                            update_post_meta($post_id, 'area', $solicitud->area);
                            update_post_meta($post_id, 'fecha_de_creacion', $solicitud->fechaCreacion);
                            update_post_meta($post_id, 'fecha_de_cierre', $solicitud->fechaCierre);
                            update_post_meta($post_id, 'requisitos', $requisitos);
                            update_post_meta($post_id, '_thumbnail_id', '5502');

                            // Verificar si la actualización fue exitosa
                            if (is_wp_error($updated_post_id)) {
                                throw new Exception( '<br>Error al actualizar el post: ' . $updated_post_id->get_error_message() );
                            }
                        }
                    // Si no creamos el post
                    }else{
                        $post_title = $solicitud->perfil.' N'.$solicitud->idSolicitud;
                        // Datos del post
                        $post_data = array(
                            'post_title'    => $post_title,
                            'post_name' => sanitize_title($post_title),
                            'post_content'  => $solicitud->descripcion,
                            'post_status'   => 'publish',
                            'post_author'   => 1, // ID del autor del post
                            'post_category' => array(8),
                            'post_type'     => 'post' // Tipo de post (puede ser 'post', 'page', etc.)
                        );
                        // Insertar el nuevo post
                        $new_post_id = wp_insert_post($post_data);
                        $requisitos = '';
                        if($solicitud->requisitos !== null){
                            $requisitos .= '<ul>';
                            // Recorremos los requisitos
                            foreach ($solicitud->requisitos as $requisito) {
                                $requisitos .= '<li>';
                                $requisitos .= '<strong>' . $requisito['requisito'] . ':</strong> ' . $requisito['observaciones'];
                                $requisitos .= '</li>';
                            }
                            $requisitos .= '</ul>';
                        }
                        // Verificar si la inserción fue exitosa
                        if (is_wp_error($new_post_id)){                    
                            throw new Exception( '<br>Error al crear el post: ' . $new_post_id->get_error_message() );
                        }
                    }
                } catch (Exception $e) {
                    appta_custom_error_log($e->getMessage(). "\n");
                    // Captura y manejo del error
                }
            }
        }
    }
    echo '</pre>';
}