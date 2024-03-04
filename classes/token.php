<?php

/**
 * This is the model class for table "medic_token".
 *
 * @property int $token_id
 * @property string $token
 * @property string $fecha
 * 
 * */


class APPTAtoken {

    // Properties
    public $token_id;
    public $token;
    public $fecha;

    function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'appta_token';
        return $this;
    }

    function get_empty()
    {
        $this->token_id = 0;
        $this->token = '';
        $this->date = '';
        return $this;
    }


    // FUNCION PARA AGREGAR UN MEMBER
    function add_token($token, $erp_id){

        global $wpdb;
        $error = null;

        $wpdb->insert($this->table, array(
            'token_id' => $token_id,
            'token'=> $token,
            'fecha'=> $fecha
        ));
        $token_id = $wpdb->insert_id;
    
        if($wpdb->last_error){
            return new WP_Error( 'SQL', __( $wpdb->last_error ) );
        }

        if(is_wp_error($error)){        
            return $error;
        }
        return $token_id;
    }
    // FUNCION PARA AGREGAR UN MEMBER
    function update_token($token_id, $token, $fecha ){

        global $wpdb;
        $error = null;       

        $wpdb->update($this->table,
        array(
            'token_id' => $token_id,
            'token'=> $token,
            'fecha' => $fecha
        ),
        array(
            'token_id' => $token_id
        ));

        if($wpdb->last_error){
            return new WP_Error( 'SQL', __( $wpdb->last_error ) );
        }

        if(is_wp_error($error)){        
            return $error;
        }
        return $token_id;
    }

    // OBTENER tokenS
    function get_token(){
        global $wpdb;
        $error = null;
        $dbobj = $wpdb->get_row( "SELECT * FROM ".$this->table, ARRAY_A );
        return $dbobj;	//didn't find it in the DB
    }
  
}