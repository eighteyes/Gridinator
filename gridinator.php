<?php
/*
Plugin Name: Gridinator
Plugin URI: http://github.com/eighteyes/trees
Description: Insert [buildgrid id=1,2,3,4] into Post/Page. Deactivate / Reactivate to add a new, blank id. 
Version: 0.2b
Author: Sean Canton
Author URI: http://8isc.com
License: None
*/


function tree_install () {
   global $wpdb;

   $table_name = $wpdb->prefix . "treedata"; 

	$sql = "CREATE TABLE `".$table_name."` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `data` blob,
	  `name` text,
	  `html` blob,
	  UNIQUE KEY (`id`)
	)";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	
	add_option('treedata', 0.1);

}

function makeScript(){	?>	

	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>

	
<?
}

function makeStubs(){ ?>
		
<div id="blackout">
	<div id="details">
	<span class="close_btn">close (x)</span>
		<div id="details_content"></div>
		<form id="details_input" class='details_input'>
		<img class="spinner" class="hide" src="<?= plugin_dir_url( __FILE__ ) ?>ajax-loader.gif"></form> 

		<?  if ( is_user_logged_in() ) { ?>
			<div id="action_buttons"><span class="edit_details">Edit</span><span class="save_details">Save</span></div>
			<? } ?>

	</div>	
	<!-- end details -->
</div>
<? }

class Node {
	public $name;
	public $id;
	public $parent;
	public $depth;
	public $children = array();
	public static $nodes = array();
	
	public $definition;
	public $explain_picurl;
	public $explain_caption;
	public $explain_credit;
	
	public $example_picurl;
	public $example_caption;
	public $example_credit;
	
//	public $links_href = array();
//	public $link_titles = array();
	public $references;
	
	public function Node ($name, $id, $depth, $parent = null){
		$this->name = $name;
		$this->id = $id;
		$this->depth = $depth;
		$this->parent = $parent;
		$this->definition = "Nothing Yet";
		$this->references = 'http://something.com';
		if ( $parent ){ $parent->children[] = $this; }
		self::$nodes[] = $this;
	}
	
	static public function getNodes(){
		return self::$nodes;
	}
	static public function setNodes($object){
		self::$nodes = $object;
	}
	
	public function setContent($definition){
		$this->definition = $definition;
	}
	
	public function getJSON(){
		// make an object to send to page
		$obj = array(
						'name' 				=> $this->name,
						'definition'		=> $this->definition, 
						'explain_picurl' 	=> $this->explain_picurl,
						'explain_caption'	=> $this->explain_caption,
						'explain_credit' 	=> $this->explain_credit,
						'example_picurl' 	=> $this->example_picurl,
						'example_caption'	=> $this->example_caption,
						'example_credit' 	=> $this->example_credit,
						'references' 		=> $this->references
						);
		$json = json_encode($obj);
		return $json;
	}
	
	public function makeNode($first = false){
		$str = "";
		
		$str .= "<span class='activateNode' data-id='$this->id'>";
		
		if ($first == false) {
			$str .= "<connect>-</connect>";
		} 
		$str .=  urldecode($this->name). "</span>";
		$str .= "<div class='details_data' data-json='". $this->getJSON() . "' style='display:hidden'>";
	
		$str .= "</div>";
		//end details_data -> used in #details_content
		return $str;
	}
	
	public function makeTree(){
		
		$root = "<div class='root'>";
		$first  = "<div class='first'>";
		$second = "<div class='second'>";
		$third  = "<div class='third'>";
		foreach (self::$nodes as $child){
			switch ($child->depth) {
				case 0 : 
					$root .= $child->makeNode(true);
					break;
				case 1 :
					$first .= $child->makeNode();
					break;
				case 2 : 
					$second .= $child->makeNode();
					break;
				case 3: 
					$third .= $child->makeNode();
					break;
			}
		}
		$root .= "</div>";
		$first .= "</div>";
		$second .= "</div>";
		$third .= "</div>";
		$output = $root . $first . $second . $third;
		return $output;
	}
}

function buildHTML($atts){
	global $wpdb;
	
	$id = $atts['id'];
	$table_name = $wpdb->prefix . "treedata";
	$tree = $wpdb->get_row("SELECT * FROM " . $table_name . " WHERE id = $id", OBJECT);
	
	$ns = unserialize($tree->data);
	Node::setNodes($ns);
	
	$html = Node::makeTree();
	
	$wpdb->update($table_name, array('html' => $html), array('id' => $id) );	
	
}

function tree_addTest(){
	global $wpdb;
	
	$table_name = $wpdb->prefix . "treedata"; 
	$testName = "testRow";
	
	$t = new Node('test_root', 0, 0);
	$t1 = new Node('test t1', 1, 1, $t);
	$t2 = new Node('test t2', 2, 1, $t);
	$t1a = new Node('test_t1a', 3, 2, $t1);
	$t1b = new Node('test_t1b', 4, 2, $t1);
	$t2a = new Node('test_t2a', 5, 2, $t2);
	$t2b = new Node('test_t2b', 6, 2, $t2);
	$t1a1 = new Node('test_t1a1', 7, 3, $t1a);
	$t1a2 = new Node('test_t1a2', 8, 3, $t1a);
	$t1b1 = new Node('test_t1b1', 9, 3, $t1b);
	$t1b2 = new Node('test_t1b2', 10, 3, $t1b);
	$t2a1 = new Node('test_t2a1', 11, 3, $t2a);
	$t2a2 = new Node('test_t2a2', 12, 3, $t2a);
	$t2b1 = new Node('test_t2b1', 13, 3, $t2b);
	$t2b2 = new Node('test_t2b2', 14, 3, $t2b);
	
	
	$testData = serialize(Node::getNodes());
	$testName = "test";
		
	$html = Node::makeTree();
	$rows = $wpdb->insert($table_name, array('data' => $testData, 'name' => $testName, 'html' => $html));
	

}

//ajaxy
function edit_node_callback($data){
	global $wpdb;
		$wpdb->show_errors();
	error_log("ajaxy");
	
	$id = $_POST['id'];
	
	// omg stripslashes!
	$data = stripslashes($_POST['data']);
	$data = json_decode( $data, true );
	
	error_log('JSON ERROR:' . json_last_error (  ) );
	
	foreach ($data as $k => $v){
		( $v != "" ) ? error_log("AJAX:  $k | $v") : null;
	}
	
	$treeid = $_POST['treeid'];
	
	//pull data for existing node
	$table_name = $wpdb->prefix . "treedata";
	$tree = $wpdb->get_row("SELECT * FROM " . $table_name . " WHERE id = $treeid", OBJECT);
	
	//build object
	$ns = unserialize($tree->data);
	Node::setNodes($ns);
	
	//localize pointer to data
	$nodes = Node::$nodes;
	
	//change object
	foreach ($nodes as $node){
		//find the right node
		if ($node->id == $id){
			 $node->name	      	=	$data['name'];
			 $node->definition		=	$data['definition'];
			 $node->explain_picurl	=	$data['explain_picurl'];
			 $node->explain_caption	=	$data['explain_caption'];
			 $node->explain_credit	=	$data['explain_credit'];
			 $node->example_picurl	=	$data['example_picurl'];
			 $node->example_caption	=	$data['example_caption'];
			 $node->example_credit	=	$data['example_credit'];
			 $node->references      =	$data['references'];
			error_log("definition: ". $node->definition);
		}                                         
	}
	
	// remake db entry with new object
	$data = serialize($nodes);
	$html = Node::makeTree();
 
	if ($wpdb->update($table_name, array('data' => $data, 'html' => $html), array('id' => $treeid) )) {
	
	
	 print "Success"; } else {print $wpdb->last_query;
		 print "Fail"; }
}

function upload_image_callback($data){
	
	foreach ($_POST as $k => $v) {
		error_log($k . ":" . $v);
	}
	
	$file = $_FILES['file-0']['tmp_name'];
	
	$tmp_name = $_FILES["file-0"]["tmp_name"];
    $name = $_FILES["file-0"]["name"];

	$uploads_dir = plugin_dir_url( __FILE__ ). 'uploads';
	$upload_dira = wp_upload_dir();
	
	//todo: add identifying index to name
	
	$upload_dir = $upload_dira['basedir']."/$name"; 
	$upload_url = $upload_dira['baseurl'];

	        
	        if (move_uploaded_file($tmp_name, $upload_dir)){
				print $upload_url . "/$name";
				error_log($upload_url);
				
				
				//love to http://net.tutsplus.com/tutorials/php/image-resizing-made-easy-with-php/

				$ext = strtolower(strrchr($upload_dir, '.'));
				$image = $upload_dir;

				list($width, $height) = getimagesize($image);

				switch($ext)
				{
					case '.jpg':
					case '.jpeg':
						$img = @imagecreatefromjpeg($image);
						break;
					case '.gif':
						$img = @imagecreatefromgif($image);
						break;
					case '.png':
						$img = @imagecreatefrompng($image);
						break;
					default:
						$img = false;
						error_log('bad image');
						break;
				}

				$imgResized = imagecreatetruecolor(425,250);
				imagecopyresampled($imgResized, $img,0,0,0,0,425,250,$width,$height);

				switch($ext){
					case '.jpg':  
					case '.jpeg':  
						if (imagetypes() & IMG_JPG) {  
							imagejpeg($imgResized, $upload_dir, 100);  
						}  
						break;  
					case '.gif':  
						if (imagetypes() & IMG_GIF) {  
							imagegif($imgResized, $upload_dir);  
						}  
						break;  
					case '.png':  
						if (imagetypes() & IMG_PNG) {  
							imagepng($imgResized, $upload_dir, 0);  
					}  
						break;  
					default:  
						break;
				}
			};
	
	
				

}

//called from page
function buildGrid($atts){
	global $wpdb;

	error_log("-----");
	
	makeScript();
	makeStubs();
	$html = "";
	
	$id = $atts['id'];
	$ids = explode(',', $id);
	
	$table_name = $wpdb->prefix . "treedata";
	
	$i = 0;
	foreach ($ids as $id){
		$i++;
		switch ($i){
			case 1 : 
				$title = "Global Citizens";break;
			case 2 : 
				$title = "The E-Network";break;
			case 3: 
				$title = "The Academy";break;
			case 4:
				$title = "Green Transformer Zone";break;
			default:
				break;
		}
		$data = $wpdb->get_row("SELECT * FROM " . $table_name . " WHERE id = $id", OBJECT);
		$html .= "<div tree-id='$id' class='grid'>" . "<h1>$title</h1>" . $data->html . "</div>";
	}

	echo "<div id='gridparent'>" . $html . "</div>";
	
	
}




wp_enqueue_style('tree-style', plugin_dir_url( __FILE__ ) . '/css/styles.css' );

wp_enqueue_script( 'tree-jquery', plugin_dir_url( __FILE__ ) . '/js/script.js', array( 'jquery' ) );
wp_enqueue_script( 'tree-sugar', plugin_dir_url( __FILE__ ) . '/js/sugar-1.1.1.min.js' );

wp_enqueue_script( 'tree-ajax', plugin_dir_url( __FILE__ ) . '/js/ajax.js', array( 'jquery' ) );

//adds javascript pointer to ajax script under obj TreeAjax
wp_localize_script( 'tree-ajax', 'TreeAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

add_shortcode('buildgrid', 'buildGrid');
add_shortcode('rebuild', 'buildHTML');

add_action('wp_ajax_edit_node', 'edit_node_callback');
add_action('wp_ajax_upload_image', 'upload_image_callback');

register_activation_hook(__FILE__,'tree_install');
register_activation_hook(__FILE__,'tree_addTest');
?>