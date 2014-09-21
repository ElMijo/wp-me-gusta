<?php
/*
	Plugin Name: MeGusta
	Plugin URI: https://github.com/ElMijo/wp-me-gusta
	Description: Este plugin permite marcar un megusta en los post 
	Author URI: https://github.com/ElMijo
	Version: 1.0
	License: MIT
	Text Domain: wp-me-gusta
*/
class MeGusta
{


	/**
	* Texto Dominio para las traducciones
	* @var string
	 */
	const TXT_DOMIN   = "wp-me-gusta";
	/**
	* Mensaje ajax en caso de error
	* @var string
	*/
	const ERROR_MSG   = "Error en el proceso, inténtelo más tarde";

	/**
	* Mensaje ajax en caso de que se guardaron los cambios
	* @var string
	*/
	const SUCCESS_MSG = "Proceso ejecutado Exitosamente!!";


	/**
	* Constructor
	*/
	function __construct(){

		add_action('wp_ajax_marcar-megusta', array($this, 'accionMeGusta') );
		add_action('wp_enqueue_scripts', array($this,"ajaxloadpost_enqueuescripts"));
		add_action( 'admin_menu', array($this,"adminMenu"));


	}

	/**
	 * Metodo marcar o desmarcar un post con un Me Gusta
	 * @return [type]
	 */
	public function accionMeGusta(){
		
		$respuesta = array("error" => TRUE,"msg" => __(self::ERROR_MSG,self::TXT_DOMIN));

		$accion    = null;

		$slug      = isset($_POST['slug'])?$_POST['slug']:FALSE;

		if(!!$slug){
			
			$post = get_page_by_path($slug,OBJECT,'post');

			if(!!$post){

				$megustaUsuarios = $this->obtenerUsuariosMegustaPost($post->ID);

				$usuario         = get_current_user_id();

				if(!!in_array($usuario, $megustaUsuarios)){

					$megustaUsuarios = array_diff($megustaUsuarios,array($usuario));

					$accion          = "remover";

				}else{

					array_push($megustaUsuarios, $usuario);

					$accion          = "agregar";

				}

				$totalMegusta    = count($megustaUsuarios);				

				update_post_meta($post->ID,'_megusta_usuarios',$megustaUsuarios);

				$respuesta = array("error" => FALSE,"msg" => __(self::SUCCESS_MSG,self::TXT_DOMIN),"total" => $totalMegusta, "accion" => $accion);

			}


		}

		wp_send_json($respuesta);

	}

	/**
	 * Permite obtener los ID de los usuaios que le dieron Me Gusta a un Post 
	 * @param  integer $postId     El ID del post
	 * @return integer
	 */
	private function obtenerUsuariosMegustaPost($postId)
	{

		$megustaUsuarios = get_post_meta($postId,'_megusta_usuarios');

		return  !!empty($megustaUsuarios)?$megustaUsuarios:$megustaUsuarios[0];
	}


	public function adminMenu()
	{
	
		add_menu_page( 'Me Gusta', 'Me Gusta', 'read', 'me-gusta-admin', array($this,'vistaAdminMenu'), 'dashicons-heart',6);
	
	}

	public function vistaAdminMenu()
	{
		echo '<div class="wrap">';
		echo '<h2>Me Gusta</h2>';
		echo '<p>'.__('Esta es una lista de los Post a los que le ha hecho un Me Gusta',self::TXT_DOMIN).'</p>';
		echo '</div>';
	}

	/**
	 * Permite declarar el ajax url como una variable global de javascript
	 * @return void
	 */
	public function ajaxloadpost_enqueuescripts()
	{

    	wp_localize_script( 'wp_me_gusta', 'MeGusta', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	}

	/**
	 * Permite saber si un usuario le dio un Me Gusta a un Post
	 * @param  integer $postId     El ID del post
	 * @return booleaan
	 */
	public function leGusta($postId)
	{

		$megustaUsuarios = $this->obtenerUsuariosMegustaPost($postId);

		$usuario         = get_current_user_id();

		return !!in_array($usuario, $megustaUsuarios);

	}

	/**
	 * Permite obtener el total de los Me Gusta dados a un Post
	 * @param  integer $postId     El ID del post
	 * @return integer
	 */
	public function totalMegusta($postId)
	{

		return count($this->obtenerUsuariosMegustaPost($postId));

	}
}

$MeGusta = new MeGusta();

/**
 * Funcion global paara saber si un usuario le dio un Me Gusta a un Post
 * @param  integer $postId     El ID del post
 * @return booleaan
 */
function mg_le_gusta($postId)
{

	global $MeGusta;

	return $MeGusta->leGusta($postId);

}

/**
 * Funcion global para obtener el total de Me Gusta de un Post
 * @param  integer $postId     El ID del post
 * @return integer
 */
function mg_total_me_gusta($postId)
{

	global $MeGusta;

	return $MeGusta->totalMegusta($postId);

}

?>