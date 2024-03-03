<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);




function APPTAread_post(){

    $params = array(
        'idTipoSolicitud' => '1',
        'fechaInicio' => '2023-01-01 00:00:00',
        'fechaFin' => '2024-01-01 00:00:00'          
    );
    $query_string = http_build_query($params);

    $resume = appta_call_api( 'solicitudes/listadoVacantes?'.$query_string , '' , 'GET');
    echo '<pre>';
    // SI NOS DIO DATA ENTONCES A CREAR O ACTUALIZAR LOS POST
    if($resume){
        foreach ( $resume as $solicitud ) {
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

                    // Actualizar el post con nueva data
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

                    $requisitos = '';
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
                    if (!is_wp_error($updated_post_id)) {
                        // Notificar que el post se ha actualizado correctamente
                        echo '<br>Post actualizado correctamente. ID: ' . $post_id;
                    } else {
                        // Notificar si hubo un error al actualizar el post
                        echo '<br>Error al actualizar el post: ' . $updated_post_id->get_error_message();
                    }
                }
            // Si no creamos el post
            } else {
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
                if (!is_wp_error($new_post_id)) {                    
                    update_post_meta($new_post_id, 'idsolicitud', $solicitud->idSolicitud);
                    update_post_meta($new_post_id, 'salario', $solicitud->salario);
                    update_post_meta($new_post_id, 'modalidad', $solicitud->modalidad);
                    update_post_meta($new_post_id, 'locacion', $solicitud->localidad);
                    update_post_meta($new_post_id, 'nivel_academico', $solicitud->nivel_academico);
                    update_post_meta($new_post_id, 'contrato', $solicitud->contrato);
                    update_post_meta($new_post_id, 'empresa', $solicitud->nombreEmpresa);
                    update_post_meta($new_post_id, 'experiencia', $solicitud->experiencia);
                    update_post_meta($new_post_id, 'horario', $solicitud->horario);
                    update_post_meta($new_post_id, 'area', $solicitud->area);
                    update_post_meta($new_post_id, 'fecha_de_creacion', $solicitud->fechaCreacion);
                    update_post_meta($new_post_id, 'fecha_de_cierre', $solicitud->fechaCierre);
                    update_post_meta($new_post_id, 'requisitos', $requisitos);
                    update_post_meta($new_post_id, '_thumbnail_id', '5502');
                    // Notificar que el post se ha creado correctamente
                    echo '<br> Post creado correctamente. ID: ' . $new_post_id;
                } else {
                    // Notificar si hubo un error al crear el post
                    echo '<br> Error al crear el post: ' . $new_post_id->get_error_message();
                }
            }
        }
    }
    echo '</pre>';
}

 
//add_action( 'APPTAupdate_orders', 'APPTAupdate_order_id' );


